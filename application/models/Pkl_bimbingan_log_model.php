<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Pkl_bimbingan_log_model extends CI_Model {

    public function __construct() {
        parent::__construct();
        $this->load->database();
    }

    public function create($data) {
        $this->db->insert('pkl_bimbingan_log', $data);
        return $this->db->insert_id();
    }

    public function get_by_id($id) {
        $this->db->select('pbl.*, u.username');
        $this->db->from('pkl_bimbingan_log pbl');
        $this->db->join('users u', 'u.id = pbl.id_pengguna', 'left');
        $this->db->where('pbl.id', $id);
        return $this->db->get()->row();
    }

    public function get_by_bimbingan($id_bimbingan) {
        $this->db->select('pbl.*, u.username, u.role_id, pbl.status_revisi, pbl.keterangan_revisi, pbl.file_path, pbl.file_name');
        $this->db->from('pkl_bimbingan_log pbl');
        $this->db->join('users u', 'u.id = pbl.id_pengguna', 'left');
        $this->db->where('pbl.id_bimbingan', $id_bimbingan);
        // Gunakan raw SQL untuk ORDER BY dengan COALESCE - parameter ketiga false untuk tidak escape
        $this->db->order_by('COALESCE(pbl.urutan, pbl.id) ASC, pbl.created_at ASC', '', false);
        return $this->db->get()->result();
    }

    public function get_latest_by_bimbingan($id_bimbingan) {
        $this->db->select('pbl.*, u.username, u.role_id');
        $this->db->from('pkl_bimbingan_log pbl');
        $this->db->join('users u', 'u.id = pbl.id_pengguna', 'left');
        $this->db->where('pbl.id_bimbingan', $id_bimbingan);
        $this->db->order_by('COALESCE(pbl.urutan, pbl.id) DESC, pbl.created_at DESC', '', false);
        $this->db->limit(1);
        return $this->db->get()->row();
    }
    
    public function get_next_urutan($id_bimbingan) {
        $this->db->select_max('urutan');
        $this->db->where('id_bimbingan', $id_bimbingan);
        $result = $this->db->get('pkl_bimbingan_log')->row();
        return ($result && $result->urutan) ? $result->urutan + 1 : 1;
    }
    
    public function get_statistik_by_bimbingan($id_bimbingan) {
        $this->db->select('
            COUNT(*) as total_log,
            SUM(CASE WHEN status_revisi = "acc" THEN 1 ELSE 0 END) as total_acc,
            SUM(CASE WHEN status_revisi = "revisi" THEN 1 ELSE 0 END) as total_revisi,
            SUM(CASE WHEN role_pengguna = "mahasiswa" THEN 1 ELSE 0 END) as total_mahasiswa,
            SUM(CASE WHEN role_pengguna = "dosen" THEN 1 ELSE 0 END) as total_dosen
        ');
        $this->db->where('id_bimbingan', $id_bimbingan);
        return $this->db->get('pkl_bimbingan_log')->row();
    }

    public function get_latest_revisi_by_bimbingan($id_bimbingan) {
        $this->db->select('pbl.*, u.username, u.role_id');
        $this->db->from('pkl_bimbingan_log pbl');
        $this->db->join('users u', 'u.id = pbl.id_pengguna', 'left');
        $this->db->where('pbl.id_bimbingan', $id_bimbingan);
        $this->db->where('pbl.role_pengguna', 'dosen');
        $this->db->where('pbl.status_revisi IS NOT NULL');
        $this->db->where('pbl.status_revisi !=', '');
        $this->db->order_by('pbl.created_at', 'DESC');
        $this->db->limit(1);
        return $this->db->get()->row();
    }
}

