<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Proposal_pendaftaran_sidang_model extends CI_Model {

    public function __construct() {
        parent::__construct();
        $this->load->database();
    }

    public function create($data) {
        $this->db->insert('proposal_pendaftaran_sidang', $data);
        return $this->db->insert_id();
    }

    public function update($id, $data) {
        $this->db->where('id', $id);
        $this->db->update('proposal_pendaftaran_sidang', $data);
        return $this->db->affected_rows();
    }

    public function get_by_id($id) {
        $this->db->select('sps.*, ms.id_mahasiswa, ms.judul_proposal, m.nim, m.nama_mahasiswa, m.kelas, ta.tahun_akademik, ta.semester');
        $this->db->from('proposal_pendaftaran_sidang sps');
        $this->db->join('mahasiswa_proposal ms', 'ms.id = sps.id_mahasiswa_proposal', 'left');
        $this->db->join('mahasiswa m', 'm.id = ms.id_mahasiswa', 'left');
        $this->db->join('tahun_akademik ta', 'ta.id = sps.id_tahun_akademik', 'left');
        $this->db->where('sps.id', $id);
        return $this->db->get()->row();
    }

    public function get_by_mahasiswa_proposal($id_mahasiswa_proposal) {
        $this->db->select('sps.*, ms.id_mahasiswa, ms.judul_proposal, m.nim, m.nama_mahasiswa, m.kelas');
        $this->db->from('proposal_pendaftaran_sidang sps');
        $this->db->join('mahasiswa_proposal ms', 'ms.id = sps.id_mahasiswa_proposal', 'left');
        $this->db->join('mahasiswa m', 'm.id = ms.id_mahasiswa', 'left');
        $this->db->where('sps.id_mahasiswa_proposal', $id_mahasiswa_proposal);
        return $this->db->get()->row();
    }

    private function terapkan_filter($filters) {
        if (!empty($filters['id_tahun_akademik'])) {
            $this->db->where('sps.id_tahun_akademik', $filters['id_tahun_akademik']);
        }
        if (!empty($filters['status'])) {
            $this->db->where('sps.status', $filters['status']);
        }
        if (!empty($filters['kelas'])) {
            $this->db->where('m.kelas', $filters['kelas']);
        }
    }

    public function get_all($filters = array(), $limit = null, $offset = null) {
        $this->db->select('sps.*, ms.id_mahasiswa, ms.judul_proposal, m.nim, m.nama_mahasiswa, m.kelas, ta.tahun_akademik, ta.semester');
        $this->db->from('proposal_pendaftaran_sidang sps');
        $this->db->join('mahasiswa_proposal ms', 'ms.id = sps.id_mahasiswa_proposal', 'left');
        $this->db->join('mahasiswa m', 'm.id = ms.id_mahasiswa', 'left');
        $this->db->join('tahun_akademik ta', 'ta.id = sps.id_tahun_akademik', 'left');
        $this->terapkan_filter($filters);
        $this->db->order_by('sps.created_at', 'DESC');
        if ($limit !== null) {
            $this->db->limit($limit, $offset);
        }
        return $this->db->get()->result();
    }

    public function count_all($filters = array()) {
        $this->db->from('proposal_pendaftaran_sidang sps');
        $this->db->join('mahasiswa_proposal ms', 'ms.id = sps.id_mahasiswa_proposal', 'left');
        $this->db->join('mahasiswa m', 'm.id = ms.id_mahasiswa', 'left');
        $this->terapkan_filter($filters);
        return $this->db->count_all_results();
    }
}
