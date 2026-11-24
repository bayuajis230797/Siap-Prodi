<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Kurikulum_model extends CI_Model {

    public function __construct() {
        parent::__construct();
        $this->load->database();
    }

    public function get_all($limit = null, $offset = null) {
        $this->db->order_by('tahun_berlaku', 'DESC');
        if ($limit !== null) {
            $this->db->limit($limit, $offset);
        }
        $query = $this->db->get('kurikulum');
        return $query->result();
    }

    public function count_all() {
        return $this->db->count_all_results('kurikulum');
    }

    public function get_by_id($id) {
        $this->db->where('id', $id);
        $query = $this->db->get('kurikulum');
        return $query->row();
    }

    public function get_aktif() {
        $this->db->where('is_aktif', 1);
        $this->db->limit(1);
        $query = $this->db->get('kurikulum');
        return $query->row();
    }

    public function create($data) {
        $this->db->insert('kurikulum', $data);
        return $this->db->insert_id();
    }

    public function update($id, $data) {
        $this->db->where('id', $id);
        $this->db->update('kurikulum', $data);
        return $this->db->affected_rows();
    }

    public function toggle_aktif($id) {
        // Ambil status saat ini
        $kurikulum = $this->get_by_id($id);
        if (!$kurikulum) {
            return false;
        }
        
        // Toggle status (aktif <-> nonaktif)
        $new_status = $kurikulum->is_aktif == 1 ? 0 : 1;
        
        $this->db->where('id', $id);
        $this->db->update('kurikulum', array('is_aktif' => $new_status));
        return $this->db->affected_rows();
    }

    public function get_all_aktif() {
        $this->db->where('is_aktif', 1);
        $this->db->order_by('tahun_berlaku', 'DESC');
        $query = $this->db->get('kurikulum');
        return $query->result();
    }

    public function delete($id) {
        $this->db->where('id', $id);
        $this->db->delete('kurikulum');
        return $this->db->affected_rows();
    }

    public function get_mata_kuliah_by_semester($id_kurikulum, $semester) {
        $this->db->select('kd.*, mk.kode_mk, mk.nama_mk, mk.sks');
        $this->db->from('kurikulum_detail kd');
        $this->db->join('mata_kuliah mk', 'mk.id = kd.id_mata_kuliah', 'left');
        $this->db->where('kd.id_kurikulum', $id_kurikulum);
        $this->db->where('kd.semester', $semester);
        $this->db->order_by('mk.kode_mk', 'ASC');
        $query = $this->db->get();
        return $query->result();
    }

    public function get_all_mata_kuliah_by_kurikulum($id_kurikulum) {
        $this->db->select('kd.*, mk.kode_mk, mk.nama_mk, mk.sks');
        $this->db->from('kurikulum_detail kd');
        $this->db->join('mata_kuliah mk', 'mk.id = kd.id_mata_kuliah', 'left');
        $this->db->where('kd.id_kurikulum', $id_kurikulum);
        $this->db->order_by('kd.semester', 'ASC');
        $this->db->order_by('mk.kode_mk', 'ASC');
        $query = $this->db->get();
        return $query->result();
    }

    public function add_mata_kuliah($id_kurikulum, $id_mata_kuliah, $semester, $jenis = null) {
        $data = array(
            'id_kurikulum' => $id_kurikulum,
            'id_mata_kuliah' => $id_mata_kuliah,
            'semester' => $semester,
            'jenis' => $jenis
        );
        $this->db->insert('kurikulum_detail', $data);
        return $this->db->insert_id();
    }

    public function remove_mata_kuliah($id_kurikulum_detail) {
        $this->db->where('id', $id_kurikulum_detail);
        $this->db->delete('kurikulum_detail');
        return $this->db->affected_rows();
    }

    /**
     * Get all mapping MK untuk kurikulum (dengan info lengkap)
     */
    public function get_all_mapping_mk($id_kurikulum, $limit = null, $offset = null) {
        $this->db->select('kd.*, mk.kode_mk, mk.nama_mk, mk.sks');
        $this->db->from('kurikulum_detail kd');
        $this->db->join('mata_kuliah mk', 'mk.id = kd.id_mata_kuliah', 'left');
        $this->db->where('kd.id_kurikulum', $id_kurikulum);
        $this->db->order_by('kd.semester', 'ASC');
        $this->db->order_by('mk.kode_mk', 'ASC');
        if ($limit !== null) {
            $this->db->limit($limit, $offset);
        }
        $query = $this->db->get();
        return $query->result();
    }

    public function count_mapping_mk($id_kurikulum) {
        $this->db->where('id_kurikulum', $id_kurikulum);
        return $this->db->count_all_results('kurikulum_detail');
    }

    /**
     * Tambah mapping MK (dengan is_wajib)
     */
    public function add_mapping_mk($id_kurikulum, $id_mata_kuliah, $semester, $is_wajib = 0) {
        // Cek apakah sudah ada
        $this->db->where('id_kurikulum', $id_kurikulum);
        $this->db->where('id_mata_kuliah', $id_mata_kuliah);
        $this->db->where('semester', $semester);
        $existing = $this->db->get('kurikulum_detail')->row();
        
        if ($existing) {
            return false; // Sudah ada
        }

        $data = array(
            'id_kurikulum' => $id_kurikulum,
            'id_mata_kuliah' => $id_mata_kuliah,
            'semester' => $semester,
            'jenis' => $is_wajib == 1 ? 'wajib' : 'pilihan'
        );
        $this->db->insert('kurikulum_detail', $data);
        return $this->db->insert_id();
    }

    /**
     * Update mapping MK
     */
    public function update_mapping_mk($id_kurikulum_detail, $id_kurikulum, $kode_mk, $nama_mk, $sks, $semester, $is_wajib) {
        $this->load->model('Mata_kuliah_model');
        
        // Ambil data mapping saat ini
        $this->db->where('id', $id_kurikulum_detail);
        $mapping = $this->db->get('kurikulum_detail')->row();
        
        if (!$mapping) {
            return false;
        }

        // Update atau buat mata kuliah
        $mk = $this->Mata_kuliah_model->get_by_kode($kode_mk);
        if (!$mk) {
            $mk_data = array(
                'kode_mk' => $kode_mk,
                'nama_mk' => $nama_mk,
                'sks' => $sks
            );
            $id_mata_kuliah = $this->Mata_kuliah_model->create($mk_data);
        } else {
            $id_mata_kuliah = $mk->id;
            $this->Mata_kuliah_model->update($mk->id, array(
                'nama_mk' => $nama_mk,
                'sks' => $sks
            ));
        }

        // Update mapping
        $data = array(
            'id_mata_kuliah' => $id_mata_kuliah,
            'semester' => $semester,
            'jenis' => $is_wajib == 1 ? 'wajib' : 'pilihan'
        );
        $this->db->where('id', $id_kurikulum_detail);
        $this->db->update('kurikulum_detail', $data);
        return $this->db->affected_rows();
    }
}

