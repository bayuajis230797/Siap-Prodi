<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Ruang_kelas_model extends CI_Model {

    public function __construct() {
        parent::__construct();
        $this->load->database();
    }

    public function get_by_id($id) {
        $this->db->where('id', $id);
        $query = $this->db->get('ruang_kelas');
        return $query->row();
    }

    public function get_all($limit = 10, $offset = 0) {
        $this->db->order_by('kode_ruang', 'ASC');
        $this->db->limit($limit, $offset);
        $query = $this->db->get('ruang_kelas');
        return $query->result();
    }

    public function count_all() {
        return $this->db->count_all('ruang_kelas');
    }

    public function get_by_kode($kode_ruang) {
        $this->db->where('kode_ruang', $kode_ruang);
        $query = $this->db->get('ruang_kelas');
        return $query->row();
    }

    public function get_all_active() {
        $this->db->where('status', 'aktif');
        $this->db->order_by('kode_ruang', 'ASC');
        $query = $this->db->get('ruang_kelas');
        return $query->result();
    }

    public function create($data) {
        $this->db->insert('ruang_kelas', $data);
        return $this->db->insert_id();
    }

    public function update($id, $data) {
        $this->db->where('id', $id);
        return $this->db->update('ruang_kelas', $data);
    }

    public function delete($id) {
        $this->db->where('id', $id);
        return $this->db->delete('ruang_kelas');
    }
}
















