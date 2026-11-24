<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Tendik_model extends CI_Model {

    public function __construct() {
        parent::__construct();
        $this->load->database();
    }

    public function get_by_id($id) {
        $this->db->where('id', $id);
        $query = $this->db->get('tendik');
        return $query->row();
    }

    public function get_all($limit = null, $offset = null) {
        $this->db->order_by('nama_tendik', 'ASC');
        if ($limit !== null) {
            $this->db->limit($limit, $offset);
        }
        $query = $this->db->get('tendik');
        return $query->result();
    }

    public function count_all() {
        $this->db->from('tendik');
        return $this->db->count_all_results();
    }

    public function get_by_nipy($nipy) {
        $this->db->where('nipy', $nipy);
        $query = $this->db->get('tendik');
        return $query->row();
    }

    public function create($data) {
        $this->db->insert('tendik', $data);
        return $this->db->insert_id();
    }

    public function update($id, $data) {
        $this->db->where('id', $id);
        $this->db->update('tendik', $data);
        return $this->db->affected_rows();
    }

    public function delete($id) {
        $this->db->where('id', $id);
        $this->db->delete('tendik');
        return $this->db->affected_rows();
    }

    /**
     * Get tendik aktif
     */
    public function get_all_active($limit = null, $offset = null) {
        $this->db->where('status', 'aktif');
        $this->db->order_by('nama_tendik', 'ASC');
        if ($limit !== null) {
            $this->db->limit($limit, $offset);
        }
        $query = $this->db->get('tendik');
        return $query->result();
    }
}




