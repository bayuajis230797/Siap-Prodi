<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Pkm_bimbingan_log_model extends CI_Model {

    public function __construct() {
        parent::__construct();
        $this->load->database();
    }

    public function create($data) {
        $this->db->insert('pkm_bimbingan_log', $data);
        return $this->db->insert_id();
    }

    public function get_by_id($id) {
        $this->db->where('id', (int) $id);
        return $this->db->get('pkm_bimbingan_log')->row();
    }

    public function get_by_bimbingan($id_bimbingan) {
        $this->db->where('id_bimbingan', (int) $id_bimbingan);
        $this->db->order_by('COALESCE(urutan, id) ASC, created_at ASC', '', false);
        return $this->db->get('pkm_bimbingan_log')->result();
    }

    public function get_latest_by_bimbingan($id_bimbingan) {
        $this->db->where('id_bimbingan', (int) $id_bimbingan);
        $this->db->order_by('COALESCE(urutan, id) DESC, created_at DESC', '', false);
        $this->db->limit(1);
        return $this->db->get('pkm_bimbingan_log')->row();
    }

    public function get_next_urutan($id_bimbingan) {
        $this->db->select_max('urutan');
        $this->db->where('id_bimbingan', (int) $id_bimbingan);
        $result = $this->db->get('pkm_bimbingan_log')->row();
        return ($result && $result->urutan) ? $result->urutan + 1 : 1;
    }

    public function get_statistik_by_bimbingan($id_bimbingan) {
        $this->db->select('
            COUNT(*) as total_log,
            SUM(CASE WHEN status_revisi = "acc" THEN 1 ELSE 0 END) as total_acc,
            SUM(CASE WHEN status_revisi = "revisi" THEN 1 ELSE 0 END) as total_revisi
        ');
        $this->db->where('id_bimbingan', (int) $id_bimbingan);
        return $this->db->get('pkm_bimbingan_log')->row();
    }
}
