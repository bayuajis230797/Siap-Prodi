<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Pkl_pendaftaran_sidang_model extends CI_Model {

    public function __construct() {
        parent::__construct();
        $this->load->database();
    }

    public function create($data) {
        $this->db->insert('pkl_pendaftaran_sidang', $data);
        return $this->db->insert_id();
    }

    public function update($id, $data) {
        $this->db->where('id', $id);
        $this->db->update('pkl_pendaftaran_sidang', $data);
        return $this->db->affected_rows();
    }

    public function get_by_id($id) {
        $this->db->select('pps.*, m.nim, m.nama_mahasiswa, m.kelas, ta.tahun_akademik, ta.semester, pp.judul_pkl');
        $this->db->from('pkl_pendaftaran_sidang pps');
        $this->db->join('mahasiswa m', 'm.id = pps.id_mahasiswa', 'left');
        $this->db->join('tahun_akademik ta', 'ta.id = pps.id_tahun_akademik', 'left');
        $this->db->join('pkl_pengajuan pp', 'pp.id_mahasiswa = pps.id_mahasiswa AND pp.id_tahun_akademik = pps.id_tahun_akademik', 'left');
        $this->db->where('pps.id', $id);
        return $this->db->get()->row();
    }

    public function get_by_mahasiswa($id_mahasiswa, $id_tahun_akademik = null) {
        $this->db->select('pps.*, m.nim, m.nama_mahasiswa, m.kelas, pp.judul_pkl');
        $this->db->from('pkl_pendaftaran_sidang pps');
        $this->db->join('mahasiswa m', 'm.id = pps.id_mahasiswa', 'left');
        $this->db->join('pkl_pengajuan pp', 'pp.id_mahasiswa = pps.id_mahasiswa AND pp.id_tahun_akademik = pps.id_tahun_akademik', 'left');
        $this->db->where('pps.id_mahasiswa', $id_mahasiswa);
        if ($id_tahun_akademik) {
            $this->db->where('pps.id_tahun_akademik', $id_tahun_akademik);
        }
        return $this->db->get()->row();
    }

    public function get_all($filters = array(), $limit = null, $offset = null) {
        $this->db->select('pps.*, m.nim, m.nama_mahasiswa, m.kelas, ta.tahun_akademik, ta.semester, pp.judul_pkl');
        $this->db->from('pkl_pendaftaran_sidang pps');
        $this->db->join('mahasiswa m', 'm.id = pps.id_mahasiswa', 'left');
        $this->db->join('tahun_akademik ta', 'ta.id = pps.id_tahun_akademik', 'left');
        $this->db->join('pkl_pengajuan pp', 'pp.id_mahasiswa = pps.id_mahasiswa AND pp.id_tahun_akademik = pps.id_tahun_akademik', 'left');

        if (!empty($filters['id_tahun_akademik'])) {
            $this->db->where('pps.id_tahun_akademik', $filters['id_tahun_akademik']);
        }
        if (!empty($filters['status'])) {
            $this->db->where('pps.status', $filters['status']);
        }
        if (!empty($filters['kelas'])) {
            $this->db->where('m.kelas', $filters['kelas']);
        }
        if (!empty($filters['q'])) {
            $q = trim($filters['q']);
            $this->db->group_start();
            $this->db->like('m.nim', $q);
            $this->db->or_like('m.nama_mahasiswa', $q);
            $this->db->or_like('pp.judul_pkl', $q);
            $this->db->group_end();
        }

        $this->db->order_by('pps.created_at', 'DESC');

        if ($limit !== null) {
            $this->db->limit($limit, $offset);
        }

        return $this->db->get()->result();
    }

    public function count_all($filters = array()) {
        $this->db->from('pkl_pendaftaran_sidang pps');
        $this->db->join('mahasiswa m', 'm.id = pps.id_mahasiswa', 'left');
        if (!empty($filters['q'])) {
            $this->db->join('pkl_pengajuan pp', 'pp.id_mahasiswa = pps.id_mahasiswa AND pp.id_tahun_akademik = pps.id_tahun_akademik', 'left');
        }

        if (!empty($filters['id_tahun_akademik'])) {
            $this->db->where('pps.id_tahun_akademik', $filters['id_tahun_akademik']);
        }
        if (!empty($filters['status'])) {
            $this->db->where('pps.status', $filters['status']);
        }
        if (!empty($filters['kelas'])) {
            $this->db->where('m.kelas', $filters['kelas']);
        }
        if (!empty($filters['q'])) {
            $q = trim($filters['q']);
            $this->db->group_start();
            $this->db->like('m.nim', $q);
            $this->db->or_like('m.nama_mahasiswa', $q);
            $this->db->or_like('pp.judul_pkl', $q);
            $this->db->group_end();
        }

        return $this->db->count_all_results();
    }
}





