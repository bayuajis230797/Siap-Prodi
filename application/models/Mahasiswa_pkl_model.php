<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Mahasiswa_pkl_model extends CI_Model {

    public function __construct() {
        parent::__construct();
        $this->load->database();
    }

    public function create($data) {
        $this->db->insert('mahasiswa_pkl', $data);
        return $this->db->insert_id();
    }

    public function update($id, $data) {
        $this->db->where('id', $id);
        $this->db->update('mahasiswa_pkl', $data);
        return $this->db->affected_rows();
    }

    public function delete($id) {
        $this->db->where('id', $id);
        $this->db->delete('mahasiswa_pkl');
        return $this->db->affected_rows();
    }

    public function get_by_id($id) {
        $this->db->select('mp.*, m.nim, m.nama_mahasiswa, m.kelas, ta.tahun_akademik, ta.semester');
        $this->db->from('mahasiswa_pkl mp');
        $this->db->join('mahasiswa m', 'm.id = mp.id_mahasiswa', 'left');
        $this->db->join('tahun_akademik ta', 'ta.id = mp.id_tahun_akademik', 'left');
        $this->db->where('mp.id', $id);
        return $this->db->get()->row();
    }

    public function get_all($id_tahun_akademik = null, $limit = null, $offset = null, $kelas_filter = null) {
        $this->db->select('mp.*, m.nim, m.nama_mahasiswa, m.kelas, ta.tahun_akademik, ta.semester');
        $this->db->from('mahasiswa_pkl mp');
        $this->db->join('mahasiswa m', 'm.id = mp.id_mahasiswa', 'left');
        $this->db->join('tahun_akademik ta', 'ta.id = mp.id_tahun_akademik', 'left');
        
        if ($id_tahun_akademik) {
            $this->db->where('mp.id_tahun_akademik', $id_tahun_akademik);
        }
        
        if ($kelas_filter && $kelas_filter !== '') {
            $this->db->where('m.kelas', $kelas_filter);
        }
        
        $this->db->order_by('m.kelas', 'ASC');
        $this->db->order_by('m.nim', 'ASC');
        
        if ($limit !== null && $offset !== null) {
            $this->db->limit($limit, $offset);
        }
        
        return $this->db->get()->result();
    }

    public function count_all($id_tahun_akademik = null, $kelas_filter = null) {
        $this->db->from('mahasiswa_pkl mp');
        $this->db->join('mahasiswa m', 'm.id = mp.id_mahasiswa', 'left');
        
        if ($id_tahun_akademik) {
            $this->db->where('mp.id_tahun_akademik', $id_tahun_akademik);
        }
        
        if ($kelas_filter && $kelas_filter !== '') {
            $this->db->where('m.kelas', $kelas_filter);
        }
        
        return $this->db->count_all_results();
    }

    public function get_by_mahasiswa($id_mahasiswa, $id_tahun_akademik = null) {
        $this->db->where('id_mahasiswa', $id_mahasiswa);
        if ($id_tahun_akademik) {
            $this->db->where('id_tahun_akademik', $id_tahun_akademik);
        }
        $this->db->order_by('id', 'DESC');
        return $this->db->get('mahasiswa_pkl')->row();
    }

    public function is_registered($id_mahasiswa, $id_tahun_akademik) {
        $this->db->where('id_mahasiswa', $id_mahasiswa);
        $this->db->where('id_tahun_akademik', $id_tahun_akademik);
        return $this->db->get('mahasiswa_pkl')->num_rows() > 0;
    }
}





