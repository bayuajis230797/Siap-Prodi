<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Mata_kuliah_model extends CI_Model {

    public function __construct() {
        parent::__construct();
        $this->load->database();
    }

    public function get_all($limit = null, $offset = null) {
        $this->db->order_by('kode_mk', 'ASC');
        if ($limit !== null) {
            $this->db->limit($limit, $offset);
        }
        $query = $this->db->get('mata_kuliah');
        return $query->result();
    }

    public function count_all() {
        return $this->db->count_all_results('mata_kuliah');
    }

    public function get_by_id($id) {
        $this->db->where('id', $id);
        $query = $this->db->get('mata_kuliah');
        return $query->row();
    }

    public function get_by_kode($kode_mk) {
        $this->db->where('kode_mk', $kode_mk);
        $query = $this->db->get('mata_kuliah');
        return $query->row();
    }

    public function create($data) {
        $this->db->insert('mata_kuliah', $data);
        return $this->db->insert_id();
    }

    public function update($id, $data) {
        $this->db->where('id', $id);
        $this->db->update('mata_kuliah', $data);
        return $this->db->affected_rows();
    }

    public function delete($id) {
        $this->db->where('id', $id);
        $this->db->delete('mata_kuliah');
        return $this->db->affected_rows();
    }
}

