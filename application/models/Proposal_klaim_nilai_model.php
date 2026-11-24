<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Proposal_klaim_nilai_model extends CI_Model {

    public function __construct() {
        parent::__construct();
        $this->load->database();
    }

    public function create($data) {
        $this->db->insert('proposal_klaim_nilai', $data);
        return $this->db->insert_id();
    }

    public function update($id, $data) {
        $this->db->where('id', $id);
        $this->db->update('proposal_klaim_nilai', $data);
        return $this->db->affected_rows();
    }

    public function delete($id) {
        $this->db->where('id', $id);
        $this->db->delete('proposal_klaim_nilai');
        return $this->db->affected_rows();
    }

    public function get_by_id($id) {
        $this->db->select('skn.*, ms.id_mahasiswa, ms.judul_proposal, m.nim, m.nama_mahasiswa, m.kelas, ta.tahun_akademik, ta.semester');
        $this->db->from('proposal_klaim_nilai skn');
        $this->db->join('mahasiswa_proposal ms', 'ms.id = skn.id_mahasiswa_proposal', 'left');
        $this->db->join('mahasiswa m', 'm.id = ms.id_mahasiswa', 'left');
        $this->db->join('tahun_akademik ta', 'ta.id = skn.id_tahun_akademik', 'left');
        $this->db->where('skn.id', $id);
        return $this->db->get()->row();
    }

    public function get_by_mahasiswa_proposal($id_mahasiswa_proposal) {
        $this->db->where('id_mahasiswa_proposal', $id_mahasiswa_proposal);
        $this->db->order_by('created_at', 'DESC');
        return $this->db->get('proposal_klaim_nilai')->row();
    }

    public function get_all($filters = array(), $limit = null, $offset = null) {
        $this->db->select('skn.*, ms.id_mahasiswa, ms.judul_proposal, m.nim, m.nama_mahasiswa, m.kelas, ta.tahun_akademik, ta.semester');
        $this->db->from('proposal_klaim_nilai skn');
        $this->db->join('mahasiswa_proposal ms', 'ms.id = skn.id_mahasiswa_proposal', 'left');
        $this->db->join('mahasiswa m', 'm.id = ms.id_mahasiswa', 'left');
        $this->db->join('tahun_akademik ta', 'ta.id = skn.id_tahun_akademik', 'left');
        
        if (!empty($filters['status'])) {
            $this->db->where('skn.status', $filters['status']);
        }
        
        if (!empty($filters['id_tahun_akademik'])) {
            $this->db->where('skn.id_tahun_akademik', $filters['id_tahun_akademik']);
        }
        
        $this->db->order_by('skn.created_at', 'DESC');
        
        if ($limit !== null) {
            $this->db->limit($limit, $offset);
        }
        
        return $this->db->get()->result();
    }
}





