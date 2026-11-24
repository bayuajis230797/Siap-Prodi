<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Pkl_nilai_rekap_model extends CI_Model {

    public function __construct() {
        parent::__construct();
        $this->load->database();
    }

    public function create($data) {
        $this->db->insert('pkl_nilai_rekap', $data);
        return $this->db->insert_id();
    }

    public function update($id, $data) {
        $this->db->where('id', $id);
        $this->db->update('pkl_nilai_rekap', $data);
        return $this->db->affected_rows();
    }

    public function get_by_id($id) {
        $this->db->select('pnr.*, m.nim, m.nama_mahasiswa, m.kelas, ta.tahun_akademik, ta.semester');
        $this->db->from('pkl_nilai_rekap pnr');
        $this->db->join('mahasiswa m', 'm.id = pnr.id_mahasiswa', 'left');
        $this->db->join('tahun_akademik ta', 'ta.id = pnr.id_tahun_akademik', 'left');
        $this->db->where('pnr.id', $id);
        return $this->db->get()->row();
    }

    public function get_by_mahasiswa($id_mahasiswa, $id_tahun_akademik = null) {
        $this->db->select('pnr.*, m.nim, m.nama_mahasiswa, m.kelas');
        $this->db->from('pkl_nilai_rekap pnr');
        $this->db->join('mahasiswa m', 'm.id = pnr.id_mahasiswa', 'left');
        $this->db->where('pnr.id_mahasiswa', $id_mahasiswa);
        if ($id_tahun_akademik) {
            $this->db->where('pnr.id_tahun_akademik', $id_tahun_akademik);
        }
        return $this->db->get()->row();
    }

    public function get_all($id_tahun_akademik = null, $limit = null, $offset = null) {
        $this->db->select('pnr.*, m.nim, m.nama_mahasiswa, m.kelas, ta.tahun_akademik, ta.semester');
        $this->db->from('pkl_nilai_rekap pnr');
        $this->db->join('mahasiswa m', 'm.id = pnr.id_mahasiswa', 'left');
        $this->db->join('tahun_akademik ta', 'ta.id = pnr.id_tahun_akademik', 'left');
        
        if ($id_tahun_akademik) {
            $this->db->where('pnr.id_tahun_akademik', $id_tahun_akademik);
        }
        
        $this->db->order_by('pnr.nilai_akhir', 'DESC');
        $this->db->order_by('m.nim', 'ASC');
        
        if ($limit !== null && $offset !== null) {
            $this->db->limit($limit, $offset);
        }
        
        return $this->db->get()->result();
    }

    public function count_all($id_tahun_akademik = null) {
        $this->db->from('pkl_nilai_rekap');
        if ($id_tahun_akademik) {
            $this->db->where('id_tahun_akademik', $id_tahun_akademik);
        }
        return $this->db->count_all_results();
    }
}





