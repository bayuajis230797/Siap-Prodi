<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Skripsi_bimbingan_pasca_log_model extends CI_Model {

    public function __construct() {
        parent::__construct();
        $this->load->database();
    }

    public function create($data) {
        $this->db->insert('skripsi_bimbingan_pasca_log', $data);
        return $this->db->insert_id();
    }

    public function get_by_bimbingan($id_bimbingan_pasca) {
        $this->db->select('l.*, u.username, d.nama_dosen');
        $this->db->from('skripsi_bimbingan_pasca_log l');
        $this->db->join('users u', 'u.id = l.id_pengguna', 'left');
        $this->db->join('dosen d', 'd.id = l.id_dosen', 'left');
        $this->db->where('l.id_bimbingan_pasca', (int) $id_bimbingan_pasca);
        $this->db->order_by('l.urutan', 'ASC');
        $this->db->order_by('l.created_at', 'ASC');
        return $this->db->get()->result();
    }

    public function get_next_urutan($id_bimbingan_pasca) {
        $this->db->select_max('urutan');
        $this->db->where('id_bimbingan_pasca', (int) $id_bimbingan_pasca);
        $row = $this->db->get('skripsi_bimbingan_pasca_log')->row();
        return $row && $row->urutan ? ((int) $row->urutan + 1) : 1;
    }

    public function get_latest($id_bimbingan_pasca) {
        $this->db->where('id_bimbingan_pasca', (int) $id_bimbingan_pasca);
        $this->db->order_by('created_at', 'DESC');
        $this->db->order_by('id', 'DESC');
        $this->db->limit(1);
        return $this->db->get('skripsi_bimbingan_pasca_log')->row();
    }
}
