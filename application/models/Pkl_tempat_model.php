<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Pkl_tempat_model extends CI_Model {

    public function __construct() {
        parent::__construct();
        $this->load->database();
    }

    public function create($data) {
        $this->db->insert('pkl_tempat', $data);
        return $this->db->insert_id();
    }

    public function update($id, $data) {
        $this->db->where('id', $id);
        $this->db->update('pkl_tempat', $data);
        return $this->db->affected_rows();
    }

    public function get_by_id($id) {
        $this->db->select('pt.*, m.nim, m.nama_mahasiswa, m.kelas, ta.tahun_akademik, ta.semester');
        $this->db->from('pkl_tempat pt');
        $this->db->join('mahasiswa m', 'm.id = pt.id_mahasiswa', 'left');
        $this->db->join('tahun_akademik ta', 'ta.id = pt.id_tahun_akademik', 'left');
        $this->db->where('pt.id', $id);
        return $this->db->get()->row();
    }

    public function get_by_mahasiswa($id_mahasiswa, $id_tahun_akademik = null) {
        $this->db->select('pt.*, m.nim, m.nama_mahasiswa, m.kelas, ta.tahun_akademik, ta.semester');
        $this->db->from('pkl_tempat pt');
        $this->db->join('mahasiswa m', 'm.id = pt.id_mahasiswa', 'left');
        $this->db->join('tahun_akademik ta', 'ta.id = pt.id_tahun_akademik', 'left');
        $this->db->where('pt.id_mahasiswa', $id_mahasiswa);
        if ($id_tahun_akademik) {
            $this->db->where('pt.id_tahun_akademik', $id_tahun_akademik);
        }
        $this->db->order_by('pt.id', 'DESC');
        return $this->db->get()->row();
    }

    public function get_all($id_tahun_akademik = null, $status = null, $status_acc = null, $limit = null, $offset = null) {
        $this->db->select('pt.*, m.nim, m.nama_mahasiswa, m.kelas, ta.tahun_akademik, ta.semester');
        $this->db->from('pkl_tempat pt');
        $this->db->join('mahasiswa m', 'm.id = pt.id_mahasiswa', 'left');
        $this->db->join('tahun_akademik ta', 'ta.id = pt.id_tahun_akademik', 'left');
        
        if ($id_tahun_akademik) {
            $this->db->where('pt.id_tahun_akademik', $id_tahun_akademik);
        }
        
        if ($status) {
            $this->db->where('pt.status', $status);
        }
        
        if ($status_acc) {
            $this->db->where('pt.status_acc_tempat', $status_acc);
        }
        
        $this->db->order_by('pt.created_at', 'DESC');
        
        if ($limit !== null && $offset !== null) {
            $this->db->limit($limit, $offset);
        }
        
        return $this->db->get()->result();
    }

    public function count_all($id_tahun_akademik = null, $status = null, $status_acc = null) {
        $this->db->from('pkl_tempat');
        
        if ($id_tahun_akademik) {
            $this->db->where('id_tahun_akademik', $id_tahun_akademik);
        }
        
        if ($status) {
            $this->db->where('status', $status);
        }
        
        if ($status_acc) {
            $this->db->where('status_acc_tempat', $status_acc);
        }
        
        return $this->db->count_all_results();
    }

    public function is_acc_by_sekprodi($id_mahasiswa, $id_tahun_akademik) {
        $this->db->where('id_mahasiswa', $id_mahasiswa);
        $this->db->where('id_tahun_akademik', $id_tahun_akademik);
        $this->db->where('status_acc_tempat', 'disetujui');
        return $this->db->get('pkl_tempat')->num_rows() > 0;
    }
}







