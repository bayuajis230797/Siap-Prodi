<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Proposal_berkas_sidang_model extends CI_Model {

    public function __construct() {
        parent::__construct();
        $this->load->database();
    }

    public function create($data) {
        $this->db->insert('proposal_berkas_sidang', $data);
        return $this->db->insert_id();
    }

    public function update($id, $data) {
        $this->db->where('id', $id);
        $this->db->update('proposal_berkas_sidang', $data);
        return $this->db->affected_rows();
    }

    public function delete($id) {
        $this->db->where('id', $id);
        $this->db->delete('proposal_berkas_sidang');
        return $this->db->affected_rows();
    }

    public function get_by_id($id) {
        $this->db->where('id', $id);
        return $this->db->get('proposal_berkas_sidang')->row();
    }

    public function get_all($aktif_only = false) {
        if ($aktif_only) {
            $this->db->where('aktif', 1);
        }
        $this->db->order_by('urutan', 'ASC');
        $this->db->order_by('id', 'ASC');
        return $this->db->get('proposal_berkas_sidang')->result();
    }

    public function get_wajib() {
        $this->db->where('aktif', 1);
        $this->db->where('wajib', 1);
        $this->db->order_by('urutan', 'ASC');
        return $this->db->get('proposal_berkas_sidang')->result();
    }

    public function count_all($aktif_only = false) {
        if ($aktif_only) {
            $this->db->where('aktif', 1);
        }
        return $this->db->count_all_results('proposal_berkas_sidang');
    }
}












