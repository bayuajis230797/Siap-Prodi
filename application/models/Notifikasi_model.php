<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Notifikasi_model extends CI_Model {

    public function __construct() {
        parent::__construct();
        $this->load->database();
    }

    public function create($data) {
        $this->db->insert('notifikasi', $data);
        return $this->db->insert_id();
    }

    public function get_by_user($id_user, $role, $limit = null, $offset = null) {
        $this->db->where('id_user', $id_user);
        $this->db->where('role', $role);
        $this->db->order_by('created_at', 'DESC');
        $this->db->order_by('is_read', 'ASC');
        
        if ($limit !== null) {
            $this->db->limit($limit, $offset !== null ? (int) $offset : 0);
        }
        
        return $this->db->get('notifikasi')->result();
    }

    public function count_unread($id_user, $role) {
        $this->db->where('id_user', $id_user);
        $this->db->where('role', $role);
        $this->db->where('is_read', 0);
        return $this->db->count_all_results('notifikasi');
    }

    public function mark_as_read($id) {
        $this->db->where('id', $id);
        $this->db->update('notifikasi', array('is_read' => 1));
        return $this->db->affected_rows();
    }

    public function mark_all_as_read($id_user, $role) {
        $this->db->where('id_user', $id_user);
        $this->db->where('role', $role);
        $this->db->where('is_read', 0);
        $this->db->update('notifikasi', array('is_read' => 1));
        return $this->db->affected_rows();
    }

    public function delete($id) {
        $this->db->where('id', $id);
        $this->db->delete('notifikasi');
        return $this->db->affected_rows();
    }

    public function get_by_id($id) {
        $this->db->where('id', $id);
        return $this->db->get('notifikasi')->row();
    }

    /**
     * Notifikasi belum dibaca dengan id > $after_id (untuk polling popup).
     */
    public function get_unread_after($id_user, $role, $after_id = 0, $limit = 10) {
        $this->db->where('id_user', $id_user);
        $this->db->where('role', $role);
        $this->db->where('is_read', 0);
        if ((int) $after_id > 0) {
            $this->db->where('id >', (int) $after_id);
        }
        $this->db->order_by('id', 'ASC');
        $this->db->limit((int) $limit);
        return $this->db->get('notifikasi')->result();
    }

    public function get_max_id($id_user, $role) {
        $this->db->select_max('id');
        $this->db->where('id_user', $id_user);
        $this->db->where('role', $role);
        $row = $this->db->get('notifikasi')->row();
        return $row && $row->id ? (int) $row->id : 0;
    }
}



