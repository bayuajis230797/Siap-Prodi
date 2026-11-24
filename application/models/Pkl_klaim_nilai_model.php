<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Pkl_klaim_nilai_model extends CI_Model {

    public function __construct() {
        parent::__construct();
        $this->load->database();
    }

    public function create($data) {
        $this->db->insert('pkl_klaim_nilai', $data);
        return $this->db->insert_id();
    }

    public function update($id, $data) {
        $this->db->where('id', $id);
        $this->db->update('pkl_klaim_nilai', $data);
        return $this->db->affected_rows();
    }

    public function get_by_id($id) {
        $this->db->select('pkn.*, m.nim, m.nama_mahasiswa, m.kelas, ta.tahun_akademik, ta.semester, pp.judul_pkl');
        $this->db->from('pkl_klaim_nilai pkn');
        $this->db->join('mahasiswa m', 'm.id = pkn.id_mahasiswa', 'left');
        $this->db->join('tahun_akademik ta', 'ta.id = pkn.id_tahun_akademik', 'left');
        $this->db->join('pkl_pengajuan pp', 'pp.id_mahasiswa = pkn.id_mahasiswa AND pp.id_tahun_akademik = pkn.id_tahun_akademik', 'left');
        $this->db->where('pkn.id', $id);
        return $this->db->get()->row();
    }

    public function get_by_mahasiswa($id_mahasiswa, $id_tahun_akademik = null) {
        $this->db->select('pkn.*, m.nim, m.nama_mahasiswa, m.kelas, pp.judul_pkl');
        $this->db->from('pkl_klaim_nilai pkn');
        $this->db->join('mahasiswa m', 'm.id = pkn.id_mahasiswa', 'left');
        $this->db->join('pkl_pengajuan pp', 'pp.id_mahasiswa = pkn.id_mahasiswa AND pp.id_tahun_akademik = pkn.id_tahun_akademik', 'left');
        $this->db->where('pkn.id_mahasiswa', $id_mahasiswa);
        if ($id_tahun_akademik) {
            $this->db->where('pkn.id_tahun_akademik', $id_tahun_akademik);
        }
        return $this->db->get()->row();
    }

    public function get_all($filters = array(), $limit = null, $offset = null) {
        $this->db->select('pkn.*, m.nim, m.nama_mahasiswa, m.kelas, ta.tahun_akademik, ta.semester, pp.judul_pkl');
        $this->db->from('pkl_klaim_nilai pkn');
        $this->db->join('mahasiswa m', 'm.id = pkn.id_mahasiswa', 'left');
        $this->db->join('tahun_akademik ta', 'ta.id = pkn.id_tahun_akademik', 'left');
        $this->db->join('pkl_pengajuan pp', 'pp.id_mahasiswa = pkn.id_mahasiswa AND pp.id_tahun_akademik = pkn.id_tahun_akademik', 'left');

        if (!empty($filters['id_tahun_akademik'])) {
            $this->db->where('pkn.id_tahun_akademik', $filters['id_tahun_akademik']);
        }
        if (!empty($filters['status'])) {
            $this->db->where('pkn.status', $filters['status']);
        }
        if (!empty($filters['kelas'])) {
            $this->db->where('m.kelas', $filters['kelas']);
        }

        $this->db->order_by('pkn.created_at', 'DESC');

        if ($limit !== null) {
            $this->db->limit($limit, $offset);
        }

        return $this->db->get()->result();
    }

    public function count_all($filters = array()) {
        $this->db->from('pkl_klaim_nilai pkn');
        $this->db->join('mahasiswa m', 'm.id = pkn.id_mahasiswa', 'left');

        if (!empty($filters['id_tahun_akademik'])) {
            $this->db->where('pkn.id_tahun_akademik', $filters['id_tahun_akademik']);
        }
        if (!empty($filters['status'])) {
            $this->db->where('pkn.status', $filters['status']);
        }
        if (!empty($filters['kelas'])) {
            $this->db->where('m.kelas', $filters['kelas']);
        }

        return $this->db->count_all_results();
    }
}





