<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Dosen_model extends CI_Model {

    public function __construct() {
        parent::__construct();
        $this->load->database();
    }

    public function get_by_id($id) {
        $this->db->where('id', $id);
        $query = $this->db->get('dosen');
        return $query->row();
    }

    public function get_all($limit = null, $offset = null) {
        $this->db->order_by('nama_dosen', 'ASC');
        if ($limit !== null) {
            $this->db->limit($limit, $offset);
        }
        $query = $this->db->get('dosen');
        return $query->result();
    }

    public function get_all_active($limit = null, $offset = null) {
        $this->db->where('status', 'aktif');
        $this->db->order_by('nama_dosen', 'ASC');
        if ($limit !== null) {
            $this->db->limit($limit, $offset);
        }
        $query = $this->db->get('dosen');
        return $query->result();
    }

    public function count_all() {
        $this->db->from('dosen');
        return $this->db->count_all_results();
    }

    public function get_by_nidn($nidn) {
        $this->db->where('nidn', $nidn);
        $query = $this->db->get('dosen');
        return $query->row();
    }

    public function create($data) {
        $this->db->insert('dosen', $data);
        return $this->db->insert_id();
    }

    public function update($id, $data) {
        $this->db->where('id', $id);
        $this->db->update('dosen', $data);
        return $this->db->affected_rows();
    }

    public function delete($id) {
        $this->db->where('id', $id);
        $this->db->delete('dosen');
        return $this->db->affected_rows();
    }
}

