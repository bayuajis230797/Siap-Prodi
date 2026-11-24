<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Proposal_nilai_rekap_model extends CI_Model {

    public function __construct() {
        parent::__construct();
        $this->load->database();
    }

    public function create($data) {
        $this->db->insert('proposal_nilai_rekap', $data);
        return $this->db->insert_id();
    }

    public function update($id, $data) {
        $this->db->where('id', $id);
        $this->db->update('proposal_nilai_rekap', $data);
        return $this->db->affected_rows();
    }

    public function delete($id) {
        $this->db->where('id', $id);
        $this->db->delete('proposal_nilai_rekap');
        return $this->db->affected_rows();
    }

    public function get_by_id($id) {
        $this->db->select('snr.*, ms.id_mahasiswa, ms.judul_proposal, m.nim, m.nama_mahasiswa, m.kelas, ta.tahun_akademik, ta.semester');
        $this->db->from('proposal_nilai_rekap snr');
        $this->db->join('mahasiswa_proposal ms', 'ms.id = snr.id_mahasiswa_proposal', 'left');
        $this->db->join('mahasiswa m', 'm.id = ms.id_mahasiswa', 'left');
        $this->db->join('tahun_akademik ta', 'ta.id = snr.id_tahun_akademik', 'left');
        $this->db->where('snr.id', $id);
        return $this->db->get()->row();
    }

    public function get_by_mahasiswa_proposal($id_mahasiswa_proposal) {
        $this->db->where('id_mahasiswa_proposal', $id_mahasiswa_proposal);
        $this->db->order_by('created_at', 'DESC');
        return $this->db->get('proposal_nilai_rekap')->row();
    }

    public function get_by_jadwal($id_jadwal_proposal) {
        $this->db->where('id_jadwal_proposal', $id_jadwal_proposal);
        return $this->db->get('proposal_nilai_rekap')->row();
    }

    public function get_all($filters = array(), $limit = null, $offset = null) {
        $this->db->select('snr.*, ms.id_mahasiswa, ms.judul_proposal, m.nim, m.nama_mahasiswa, m.kelas, ta.tahun_akademik, ta.semester');
        $this->db->from('proposal_nilai_rekap snr');
        $this->db->join('mahasiswa_proposal ms', 'ms.id = snr.id_mahasiswa_proposal', 'left');
        $this->db->join('mahasiswa m', 'm.id = ms.id_mahasiswa', 'left');
        $this->db->join('tahun_akademik ta', 'ta.id = snr.id_tahun_akademik', 'left');
        
        if (!empty($filters['id_tahun_akademik'])) {
            $this->db->where('snr.id_tahun_akademik', $filters['id_tahun_akademik']);
        }
        
        if (!empty($filters['kelas'])) {
            $this->db->where('m.kelas', $filters['kelas']);
        }
        
        $this->db->order_by('snr.created_at', 'DESC');
        
        if ($limit !== null) {
            $this->db->limit($limit, $offset);
        }
        
        return $this->db->get()->result();
    }

    public function count_all($filters = array()) {
        $this->db->from('proposal_nilai_rekap snr');
        $this->db->join('mahasiswa_proposal ms', 'ms.id = snr.id_mahasiswa_proposal', 'left');
        $this->db->join('mahasiswa m', 'm.id = ms.id_mahasiswa', 'left');
        
        if (!empty($filters['id_tahun_akademik'])) {
            $this->db->where('snr.id_tahun_akademik', $filters['id_tahun_akademik']);
        }
        
        if (!empty($filters['kelas'])) {
            $this->db->where('m.kelas', $filters['kelas']);
        }
        
        return $this->db->count_all_results();
    }
}





