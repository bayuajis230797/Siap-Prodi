<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Auth Helper
 * Helper untuk pengecekan autentikasi dan role
 */

/**
 * Cek apakah user sudah login
 * @return bool
 */
if (!function_exists('is_logged_in')) {
    function is_logged_in() {
        $CI =& get_instance();
        return $CI->session->userdata('user_id') !== null;
    }
}

/**
 * Cek apakah user memiliki role tertentu
 * @param string|array $roles Role atau array of roles
 * @return bool
 */
if (!function_exists('has_role')) {
    function has_role($roles) {
        $CI =& get_instance();
        $user_role = $CI->session->userdata('role');
        
        if (is_array($roles)) {
            return in_array($user_role, $roles);
        }
        
        return $user_role === $roles;
    }
}

/**
 * Redirect ke login jika belum login
 */
if (!function_exists('require_login')) {
    function require_login() {
        if (!is_logged_in()) {
            redirect('Auth/login');
        }
    }
}

/**
 * Redirect jika tidak memiliki role yang diizinkan
 * @param string|array $allowed_roles
 * @param string $redirect_url Default ke dashboard
 */
if (!function_exists('require_role')) {
    function require_role($allowed_roles, $redirect_url = 'Dashboard') {
        require_login();
        
        if (!has_role($allowed_roles)) {
            $CI =& get_instance();
            $CI->session->set_flashdata('error', 'Anda tidak memiliki akses ke halaman ini.');
            redirect($redirect_url);
        }
    }
}

/**
 * Ambil user ID dari session
 * @return int|null
 */
if (!function_exists('get_user_id')) {
    function get_user_id() {
        $CI =& get_instance();
        return $CI->session->userdata('user_id');
    }
}

/**
 * Ambil role dari session
 * @return string|null
 */
if (!function_exists('get_user_role')) {
    function get_user_role() {
        $CI =& get_instance();
        return $CI->session->userdata('role');
    }
}
















