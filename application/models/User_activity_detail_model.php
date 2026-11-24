<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * User Activity Detail Model
 * Menyimpan aktivitas user selama sesi login berjalan
 */
class User_activity_detail_model extends CI_Model {

    public function __construct() {
        parent::__construct();
        $this->load->database();
    }

    public function create($data) {
        $this->db->insert('user_activity_detail', $data);
        return $this->db->insert_id();
    }

    public function get_by_login_log_id($login_log_id) {
        $this->db->select('uad.*, u.username, r.nama_role');
        $this->db->from('user_activity_detail uad');
        $this->db->join('users u', 'u.id = uad.user_id', 'left');
        $this->db->join('roles r', 'r.id = u.role_id', 'left');
        $this->db->where('uad.login_log_id', $login_log_id);
        $this->db->order_by('uad.created_at', 'ASC');
        return $this->db->get()->result();
    }

    public function count_by_login_log_id($login_log_id) {
        $this->db->where('login_log_id', $login_log_id);
        return $this->db->count_all_results('user_activity_detail');
    }
}
