<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Kelas_model extends CI_Model {

    public function __construct() {
        parent::__construct();
        $this->load->database();
    }

    /**
     * Get semua kelas yang ada dari data mahasiswa
     */
    public function get_all_kelas() {
        $this->db->select('kelas, COUNT(*) as jumlah_mahasiswa');
        $this->db->from('mahasiswa');
        $this->db->where('status', 'aktif');
        $this->db->where('kelas IS NOT NULL');
        $this->db->where('kelas !=', '');
        $this->db->group_by('kelas');
        $this->db->order_by('kelas', 'ASC');
        $query = $this->db->get();
        return $query->result();
    }

    /**
     * Get detail kelas: mahasiswa dan kurikulum yang digunakan
     */
    public function get_detail_kelas($kelas) {
        $this->db->select('m.*, k.nama_kurikulum, k.tahun_berlaku, k.id as id_kurikulum');
        $this->db->from('mahasiswa m');
        $this->db->join('kurikulum k', 'k.id = m.id_kurikulum', 'left');
        $this->db->where('m.kelas', $kelas);
        $this->db->where('m.status', 'aktif');
        $this->db->order_by('m.nim', 'ASC');
        $query = $this->db->get();
        return $query->result();
    }

    /**
     * Get kurikulum yang digunakan di kelas tersebut
     */
    public function get_kurikulum_by_kelas($kelas) {
        $this->db->select('k.id, k.nama_kurikulum, k.tahun_berlaku');
        $this->db->distinct();
        $this->db->from('mahasiswa m');
        $this->db->join('kurikulum k', 'k.id = m.id_kurikulum', 'left');
        $this->db->where('m.kelas', $kelas);
        $this->db->where('m.status', 'aktif');
        $this->db->where('k.id IS NOT NULL');
        $this->db->order_by('k.tahun_berlaku', 'DESC');
        $query = $this->db->get();
        return $query->result();
    }

    /**
     * Get mata kuliah untuk kelas berdasarkan kurikulum dan semester kelas
     */
    public function get_mata_kuliah_by_kelas($kelas, $id_tahun_akademik = null) {
        // Ambil semester dari kelas (misalnya 3A = semester 3)
        $semester_kelas = intval(substr($kelas, 0, 1));
        
        if ($semester_kelas < 1 || $semester_kelas > 8) {
            return array();
        }

        // Ambil kurikulum yang digunakan di kelas tersebut
        $kurikulum_list = $this->get_kurikulum_by_kelas($kelas);
        
        if (empty($kurikulum_list)) {
            return array();
        }

        $kurikulum_ids = array();
        foreach ($kurikulum_list as $k) {
            $kurikulum_ids[] = $k->id;
        }

        // Ambil mata kuliah dari kurikulum_detail untuk semester tersebut
        $this->db->select('kd.id, mk.kode_mk, mk.nama_mk, mk.sks, kd.semester, k.nama_kurikulum, k.tahun_berlaku, kd.jenis');
        $this->db->from('kurikulum_detail kd');
        $this->db->join('mata_kuliah mk', 'mk.id = kd.id_mata_kuliah', 'left');
        $this->db->join('kurikulum k', 'k.id = kd.id_kurikulum', 'left');
        $this->db->where_in('kd.id_kurikulum', $kurikulum_ids);
        $this->db->where('kd.semester', $semester_kelas);
        $this->db->where('k.is_aktif', 1); // Hanya kurikulum yang aktif
        $this->db->order_by('kd.semester', 'ASC');
        $this->db->order_by('mk.kode_mk', 'ASC');
        
        $mata_kuliah = $this->db->get()->result();

        // Jika ada tahun akademik, cek apakah mata kuliah sudah di-set aktif
        if ($id_tahun_akademik) {
            $this->load->model('Mata_kuliah_aktif_model');
            foreach ($mata_kuliah as $mk) {
                // $mk->id adalah id dari kurikulum_detail
                $mk->is_aktif = $this->Mata_kuliah_aktif_model->is_aktif($mk->id, $id_tahun_akademik);
            }
        }

        return $mata_kuliah;
    }

    /**
     * Get jumlah mahasiswa per kelas
     */
    public function count_mahasiswa_by_kelas($kelas) {
        $this->db->where('kelas', $kelas);
        $this->db->where('status', 'aktif');
        return $this->db->count_all_results('mahasiswa');
    }

    /**
     * Get statistik kelas
     */
    public function get_statistik_kelas() {
        $this->db->select('kelas, COUNT(*) as jumlah_mahasiswa, 
                          MIN(semester_aktif) as semester_min, 
                          MAX(semester_aktif) as semester_max');
        $this->db->from('mahasiswa');
        $this->db->where('status', 'aktif');
        $this->db->where('kelas IS NOT NULL');
        $this->db->where('kelas !=', '');
        $this->db->group_by('kelas');
        $this->db->order_by('kelas', 'ASC');
        $query = $this->db->get();
        return $query->result();
    }
}

