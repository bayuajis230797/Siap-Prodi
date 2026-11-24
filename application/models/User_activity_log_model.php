<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * User Activity Log Model
 * Model untuk log aktivitas user (login, logout, dll)
 */
class User_activity_log_model extends CI_Model {

    public function __construct() {
        parent::__construct();
        $this->load->database();
    }

    /**
     * Tambah log aktivitas
     */
    public function create($data) {
        $this->db->insert('user_activity_log', $data);
        return $this->db->insert_id();
    }

    /**
     * Ambil log aktivitas dengan filter
     * @param array $filters ['user_id', 'activity_type', 'date_from', 'date_to', 'role']
     * @param int $limit
     * @param int $offset
     */
    public function get_all($filters = array(), $limit = 50, $offset = 0) {
        $this->db->select("
            ual.*,
            u.username,
            u.ref_id,
            r.nama_role,
            (
                SELECT COUNT(*)
                FROM user_activity_detail uad
                WHERE uad.login_log_id = ual.id
            ) AS detail_count,
            CASE
                WHEN r.nama_role = 'dosen' THEN COALESCE(d.nama_dosen, u.username)
                WHEN r.nama_role = 'mahasiswa' THEN COALESCE(m.nama_mahasiswa, u.username)
                WHEN r.nama_role = 'tendik' THEN COALESCE(t.nama_tendik, u.username)
                ELSE u.username
            END AS nama_user
        ");
        $this->db->from('user_activity_log ual');
        $this->db->join('users u', 'u.id = ual.user_id', 'left');
        $this->db->join('roles r', 'r.id = u.role_id', 'left');
        $this->db->join('dosen d', "d.id = u.ref_id AND r.nama_role = 'dosen'", 'left', false);
        $this->db->join('mahasiswa m', "m.id = u.ref_id AND r.nama_role = 'mahasiswa'", 'left', false);
        $this->db->join('tendik t', "t.id = u.ref_id AND r.nama_role = 'tendik'", 'left', false);

        if (!empty($filters['user_id'])) {
            $this->db->where('ual.user_id', $filters['user_id']);
        }
        if (!empty($filters['activity_type'])) {
            $this->db->where('ual.activity_type', $filters['activity_type']);
        }
        if (!empty($filters['date_from'])) {
            $this->db->where('DATE(ual.created_at) >=', $filters['date_from']);
        }
        if (!empty($filters['date_to'])) {
            $this->db->where('DATE(ual.created_at) <=', $filters['date_to']);
        }
        if (!empty($filters['role'])) {
            $this->db->where('r.nama_role', $filters['role']);
        }
        if (!empty($filters['search'])) {
            $this->db->group_start();
            $this->db->like('u.username', $filters['search']);
            $this->db->or_like('d.nama_dosen', $filters['search']);
            $this->db->or_like('m.nama_mahasiswa', $filters['search']);
            $this->db->or_like('t.nama_tendik', $filters['search']);
            $this->db->or_like('ual.description', $filters['search']);
            $this->db->group_end();
        }

        $this->db->order_by('ual.created_at', 'DESC');
        $this->db->limit($limit, $offset);
        return $this->db->get()->result();
    }

    public function get_by_id($id) {
        $this->db->select("
            ual.*,
            u.username,
            u.ref_id,
            r.nama_role,
            CASE
                WHEN r.nama_role = 'dosen' THEN COALESCE(d.nama_dosen, u.username)
                WHEN r.nama_role = 'mahasiswa' THEN COALESCE(m.nama_mahasiswa, u.username)
                WHEN r.nama_role = 'tendik' THEN COALESCE(t.nama_tendik, u.username)
                ELSE u.username
            END AS nama_user
        ");
        $this->db->from('user_activity_log ual');
        $this->db->join('users u', 'u.id = ual.user_id', 'left');
        $this->db->join('roles r', 'r.id = u.role_id', 'left');
        $this->db->join('dosen d', "d.id = u.ref_id AND r.nama_role = 'dosen'", 'left', false);
        $this->db->join('mahasiswa m', "m.id = u.ref_id AND r.nama_role = 'mahasiswa'", 'left', false);
        $this->db->join('tendik t', "t.id = u.ref_id AND r.nama_role = 'tendik'", 'left', false);
        $this->db->where('ual.id', $id);
        return $this->db->get()->row();
    }

    /**
     * Hitung total log dengan filter
     */
    public function count_all($filters = array()) {
        $this->db->from('user_activity_log ual');
        $this->db->join('users u', 'u.id = ual.user_id', 'left');
        $this->db->join('roles r', 'r.id = u.role_id', 'left');
        $this->db->join('dosen d', "d.id = u.ref_id AND r.nama_role = 'dosen'", 'left', false);
        $this->db->join('mahasiswa m', "m.id = u.ref_id AND r.nama_role = 'mahasiswa'", 'left', false);
        $this->db->join('tendik t', "t.id = u.ref_id AND r.nama_role = 'tendik'", 'left', false);

        if (!empty($filters['user_id'])) {
            $this->db->where('ual.user_id', $filters['user_id']);
        }
        if (!empty($filters['activity_type'])) {
            $this->db->where('ual.activity_type', $filters['activity_type']);
        }
        if (!empty($filters['date_from'])) {
            $this->db->where('DATE(ual.created_at) >=', $filters['date_from']);
        }
        if (!empty($filters['date_to'])) {
            $this->db->where('DATE(ual.created_at) <=', $filters['date_to']);
        }
        if (!empty($filters['role'])) {
            $this->db->where('r.nama_role', $filters['role']);
        }
        if (!empty($filters['search'])) {
            $this->db->group_start();
            $this->db->like('u.username', $filters['search']);
            $this->db->or_like('d.nama_dosen', $filters['search']);
            $this->db->or_like('m.nama_mahasiswa', $filters['search']);
            $this->db->or_like('t.nama_tendik', $filters['search']);
            $this->db->or_like('ual.description', $filters['search']);
            $this->db->group_end();
        }

        return $this->db->count_all_results();
    }

    /**
     * Ambil daftar role untuk filter (dari tabel roles)
     */
    public function get_roles_for_filter() {
        $this->db->select('nama_role');
        $this->db->from('roles');
        $this->db->order_by('nama_role', 'ASC');
        $result = $this->db->get()->result();
        $roles = array();
        foreach ($result as $r) {
            $roles[] = $r->nama_role;
        }
        return $roles;
    }

    /**
     * Hapus log aktivitas (format) berdasarkan filter opsional.
     * @param array $filters date_to (hapus s/d tanggal), date_from, activity_type, user_id
     * @return int jumlah baris log yang dihapus
     */
    public function format_clear($filters = array()) {
        $ids = array();
        $this->db->select('ual.id');
        $this->db->from('user_activity_log ual');
        if (!empty($filters['user_id'])) {
            $this->db->where('ual.user_id', (int) $filters['user_id']);
        }
        if (!empty($filters['activity_type'])) {
            $this->db->where('ual.activity_type', $filters['activity_type']);
        }
        if (!empty($filters['date_from'])) {
            $this->db->where('DATE(ual.created_at) >=', $filters['date_from']);
        }
        if (!empty($filters['date_to'])) {
            $this->db->where('DATE(ual.created_at) <=', $filters['date_to']);
        }
        $rows = $this->db->get()->result();
        foreach ($rows as $r) {
            $ids[] = (int) $r->id;
        }
        if (empty($ids)) {
            return 0;
        }

        // Hapus detail dulu (batch)
        foreach (array_chunk($ids, 500) as $chunk) {
            $this->db->where_in('login_log_id', $chunk);
            $this->db->delete('user_activity_detail');
            $this->db->where_in('id', $chunk);
            $this->db->delete('user_activity_log');
        }
        return count($ids);
    }

    /** Ringkasan login 7 hari terakhir untuk dashboard */
    public function get_login_count_last_days($days = 7) {
        $days = max(1, (int) $days);
        $sql = "SELECT DATE(created_at) AS tgl, COUNT(*) AS jumlah
            FROM user_activity_log
            WHERE activity_type = 'login'
              AND created_at >= DATE_SUB(CURDATE(), INTERVAL ? DAY)
            GROUP BY DATE(created_at)
            ORDER BY tgl ASC";
        return $this->db->query($sql, array($days - 1))->result();
    }
}
