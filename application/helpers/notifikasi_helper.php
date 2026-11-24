<?php
defined('BASEPATH') OR exit('No direct script access allowed');

if (!function_exists('send_in_app_notification_by_user')) {
    function send_in_app_notification_by_user($user_id, $role, $judul, $pesan, $link = null, $jenis = 'umum') {
        $user_id = (int) $user_id;
        $role = trim((string) $role);
        $judul = trim((string) $judul);
        $pesan = trim((string) $pesan);
        $link = trim((string) $link);
        $jenis = trim((string) $jenis);
        if ($jenis === '') {
            $jenis = 'umum';
        }

        if ($user_id <= 0 || $role === '' || $judul === '' || $pesan === '') {
            return false;
        }

        $CI =& get_instance();
        $CI->load->model('Notifikasi_model');

        $data = array(
            'id_user' => $user_id,
            'role' => $role,
            'jenis' => $jenis,
            'judul' => $judul,
            'pesan' => $pesan,
            'link' => ($link !== '' ? $link : null),
            'is_read' => 0
        );

        return (bool) $CI->Notifikasi_model->create($data);
    }
}

if (!function_exists('send_in_app_notification_by_ref')) {
    function send_in_app_notification_by_ref($role, $ref_id, $judul, $pesan, $link = null, $jenis = 'umum') {
        $ref_id = (int) $ref_id;
        if ($ref_id <= 0) {
            return false;
        }

        $CI =& get_instance();
        $CI->load->model('User_model');
        $user = $CI->User_model->get_by_ref_id($ref_id, $role);
        if (!$user) {
            return false;
        }

        return send_in_app_notification_by_user($user->id, $role, $judul, $pesan, $link, $jenis);
    }
}

if (!function_exists('send_in_app_notification_by_role')) {
    function send_in_app_notification_by_role($role, $judul, $pesan, $link = null, $exclude_user_ids = array(), $jenis = 'umum') {
        $role = trim((string) $role);
        if ($role === '') {
            return 0;
        }

        $exclude_map = array();
        if (is_array($exclude_user_ids)) {
            foreach ($exclude_user_ids as $id) {
                $id = (int) $id;
                if ($id > 0) {
                    $exclude_map[$id] = true;
                }
            }
        }

        $CI =& get_instance();
        $CI->db->select('u.id');
        $CI->db->from('users u');
        $CI->db->join('roles r', 'r.id = u.role_id', 'inner');
        $CI->db->where('LOWER(r.nama_role)', strtolower($role));
        $users = $CI->db->get()->result();

        $created = 0;
        foreach ($users as $user) {
            $user_id = (int) ($user->id ?? 0);
            if ($user_id <= 0 || isset($exclude_map[$user_id])) {
                continue;
            }
            if (send_in_app_notification_by_user($user_id, $role, $judul, $pesan, $link, $jenis)) {
                $created++;
            }
        }

        return $created;
    }
}
