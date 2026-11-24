<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Mata Kuliah Aktif Model
 * Mengelola mata kuliah aktif per tahun akademik
 */
class Mata_kuliah_aktif_model extends CI_Model {

    public function __construct() {
        parent::__construct();
        $this->load->database();
    }

    /**
     * Get mata kuliah aktif by ID
     */
    public function get_by_id($id) {
        $this->db->where('id', $id);
        $query = $this->db->get('mata_kuliah_aktif');
        return $query->row();
    }

    /**
     * Hapus semua mata kuliah aktif untuk tahun akademik tertentu
     */
    public function clear_by_tahun_akademik($id_tahun_akademik) {
        $this->db->where('id_tahun_akademik', $id_tahun_akademik);
        $this->db->delete('mata_kuliah_aktif');
        return $this->db->affected_rows();
    }

    /**
     * Set mata kuliah aktif untuk kurikulum dan tahun akademik
     */
    public function set_aktif($id_kurikulum, $id_kurikulum_detail, $id_tahun_akademik, $semester_aktif) {
        // Cek apakah sudah ada
        $this->db->where('id_kurikulum_detail', $id_kurikulum_detail);
        $this->db->where('id_tahun_akademik', $id_tahun_akademik);
        $existing = $this->db->get('mata_kuliah_aktif')->row();
        
        if ($existing) {
            // Update
            $this->db->where('id', $existing->id);
            $this->db->update('mata_kuliah_aktif', array(
                'semester_aktif' => $semester_aktif
            ));
            return $existing->id;
        } else {
            // Insert
            $data = array(
                'id_kurikulum' => $id_kurikulum,
                'id_kurikulum_detail' => $id_kurikulum_detail,
                'id_tahun_akademik' => $id_tahun_akademik,
                'semester_aktif' => $semester_aktif
            );
            $this->db->insert('mata_kuliah_aktif', $data);
            return $this->db->insert_id();
        }
    }

    /**
     * Get mata kuliah aktif untuk kurikulum dan tahun akademik
     * Hanya menampilkan jika kurikulum statusnya aktif
     */
    /** True bila tahun akademik ini sudah punya data mata kuliah aktif (riwayat tidak boleh ditimpa). */
    public function ada_untuk_tahun($id_tahun_akademik) {
        $this->db->where('id_tahun_akademik', (int) $id_tahun_akademik);
        return $this->db->count_all_results('mata_kuliah_aktif') > 0;
    }

    public function get_aktif_by_kurikulum_tahun($id_kurikulum, $id_tahun_akademik) {
        $this->db->select('mka.*, kd.semester, mk.kode_mk, mk.nama_mk, mk.sks, k.is_aktif');
        $this->db->from('mata_kuliah_aktif mka');
        $this->db->join('kurikulum_detail kd', 'kd.id = mka.id_kurikulum_detail', 'left');
        $this->db->join('mata_kuliah mk', 'mk.id = kd.id_mata_kuliah', 'left');
        $this->db->join('kurikulum k', 'k.id = mka.id_kurikulum', 'left');
        $this->db->where('mka.id_kurikulum', $id_kurikulum);
        $this->db->where('mka.id_tahun_akademik', $id_tahun_akademik);
        $this->db->where('k.is_aktif', 1); // Hanya kurikulum yang aktif
        $this->db->order_by('kd.semester', 'ASC');
        $this->db->order_by('mk.kode_mk', 'ASC');
        $query = $this->db->get();
        return $query->result();
    }

    /**
     * Cek apakah mata kuliah aktif untuk tahun akademik tertentu
     */
    public function is_aktif($id_kurikulum_detail, $id_tahun_akademik) {
        $this->db->where('id_kurikulum_detail', $id_kurikulum_detail);
        $this->db->where('id_tahun_akademik', $id_tahun_akademik);
        $query = $this->db->get('mata_kuliah_aktif');
        return $query->num_rows() > 0;
    }

    /**
     * Get semua mata kuliah aktif untuk tahun akademik tertentu (semua kurikulum)
     * Hanya menampilkan mata kuliah yang statusnya aktif (ada di mata_kuliah_aktif)
     * DAN dari kurikulum yang statusnya aktif (is_aktif = 1)
     */
    public function get_all_aktif_by_tahun($id_tahun_akademik, $limit = null, $offset = null) {
        $this->db->select('mk.id, mk.kode_mk, mk.nama_mk, mk.sks, kd.semester, k.nama_kurikulum, k.tahun_berlaku');
        $this->db->from('mata_kuliah_aktif mka');
        $this->db->join('kurikulum_detail kd', 'kd.id = mka.id_kurikulum_detail', 'left');
        $this->db->join('mata_kuliah mk', 'mk.id = kd.id_mata_kuliah', 'left');
        $this->db->join('kurikulum k', 'k.id = mka.id_kurikulum', 'left');
        $this->db->where('mka.id_tahun_akademik', $id_tahun_akademik);
        $this->db->where('k.is_aktif', 1); // Hanya kurikulum yang aktif
        $this->db->order_by('kd.semester', 'ASC'); // Urutkan semester terendah dulu
        $this->db->order_by('mk.kode_mk', 'ASC'); // Kemudian urutkan kode MK
        if ($limit !== null) {
            $this->db->limit($limit, $offset);
        }
        $query = $this->db->get();
        return $query->result();
    }

    /**
     * Count semua mata kuliah aktif untuk tahun akademik tertentu
     * Hanya menghitung mata kuliah yang statusnya aktif (ada di mata_kuliah_aktif)
     * DAN dari kurikulum yang statusnya aktif (is_aktif = 1)
     */
    public function count_all_aktif_by_tahun($id_tahun_akademik) {
        $this->db->from('mata_kuliah_aktif mka');
        $this->db->join('kurikulum_detail kd', 'kd.id = mka.id_kurikulum_detail', 'left');
        $this->db->join('mata_kuliah mk', 'mk.id = kd.id_mata_kuliah', 'left');
        $this->db->join('kurikulum k', 'k.id = mka.id_kurikulum', 'left');
        $this->db->where('mka.id_tahun_akademik', $id_tahun_akademik);
        $this->db->where('k.is_aktif', 1); // Hanya kurikulum yang aktif
        return $this->db->count_all_results();
    }

    /**
     * Get mata kuliah aktif by kurikulum_detail dan tahun akademik
     */
    public function get_by_kurikulum_detail_tahun($id_kurikulum_detail, $id_tahun_akademik) {
        $this->db->where('id_kurikulum_detail', $id_kurikulum_detail);
        $this->db->where('id_tahun_akademik', $id_tahun_akademik);
        $query = $this->db->get('mata_kuliah_aktif');
        return $query->row();
    }
}
