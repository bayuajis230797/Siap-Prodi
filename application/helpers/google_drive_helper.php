<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Google Drive Helper
 * Helper functions untuk validasi dan format link Google Drive
 */

/**
 * Validasi apakah string adalah link Google Drive yang valid
 * 
 * @param string $url Link yang akan divalidasi
 * @return bool TRUE jika valid, FALSE jika tidak
 */
if (!function_exists('is_valid_google_drive_link')) {
    function is_valid_google_drive_link($url) {
        if (empty($url)) {
            return false;
        }
        
        // Pattern untuk berbagai format Google Drive link
        // Format 1: https://drive.google.com/file/d/FILE_ID/view?usp=sharing
        // Format 2: https://drive.google.com/open?id=FILE_ID
        // Format 3: https://docs.google.com/document/d/FILE_ID/edit
        // Format 4: https://drive.google.com/uc?export=download&id=FILE_ID
        
        $patterns = array(
            '/^https?:\/\/(drive|docs)\.google\.com\/(file|document|spreadsheets|presentation)\/d\/[a-zA-Z0-9_-]+/',
            '/^https?:\/\/drive\.google\.com\/open\?id=[a-zA-Z0-9_-]+/',
            '/^https?:\/\/drive\.google\.com\/uc\?export=download&id=[a-zA-Z0-9_-]+/',
            '/^https?:\/\/drive\.google\.com\/file\/d\/[a-zA-Z0-9_-]+\/view/',
            '/^https?:\/\/drive\.google\.com\/file\/d\/[a-zA-Z0-9_-]+\/preview/',
        );
        
        foreach ($patterns as $pattern) {
            if (preg_match($pattern, trim($url))) {
                return true;
            }
        }
        
        return false;
    }
}

/**
 * Normalisasi link Google Drive ke format view yang dapat diakses
 * 
 * @param string $url Link Google Drive
 * @return string Link yang sudah dinormalisasi
 */
if (!function_exists('normalize_google_drive_link')) {
    function normalize_google_drive_link($url) {
        if (empty($url)) {
            return '';
        }
        
        $url = trim($url);
        
        // Extract file ID dari berbagai format
        $file_id = null;
        
        // Format: /file/d/FILE_ID/view atau /file/d/FILE_ID/preview
        if (preg_match('/\/file\/d\/([a-zA-Z0-9_-]+)/', $url, $matches)) {
            $file_id = $matches[1];
        }
        // Format: /document/d/FILE_ID/edit
        elseif (preg_match('/\/document\/d\/([a-zA-Z0-9_-]+)/', $url, $matches)) {
            $file_id = $matches[1];
        }
        // Format: ?id=FILE_ID
        elseif (preg_match('/[?&]id=([a-zA-Z0-9_-]+)/', $url, $matches)) {
            $file_id = $matches[1];
        }
        
        if ($file_id) {
            // Return format view yang dapat diakses
            return 'https://drive.google.com/file/d/' . $file_id . '/view?usp=sharing';
        }
        
        // Jika tidak bisa diextract, return as is
        return $url;
    }
}

/**
 * Generate link untuk membuka Google Drive di tab baru
 * 
 * @param string $url Link Google Drive
 * @param string $text Teks untuk link (default: "Buka di Google Drive")
 * @return string HTML link
 */
if (!function_exists('google_drive_link_html')) {
    function google_drive_link_html($url, $text = 'Buka di Google Drive') {
        if (empty($url)) {
            return '<span class="text-muted">Link tidak tersedia</span>';
        }
        
        $normalized_url = normalize_google_drive_link($url);
        
        return '<a href="' . htmlspecialchars($normalized_url) . '" target="_blank" class="btn btn-sm btn-info" rel="noopener noreferrer">
                    <i class="fas fa-external-link-alt"></i> ' . htmlspecialchars($text) . '
                </a>';
    }
}

/**
 * Cek apakah user tertentu sudah konek Google Drive.
 */
if (!function_exists('is_google_drive_connected')) {
    function is_google_drive_connected($user_id = null) {
        $CI =& get_instance();
        if ($user_id === null) {
            $user_id = $CI->session->userdata('user_id');
        }
        if (!$user_id) {
            return false;
        }
        $CI->load->model('User_google_drive_model');
        return $CI->User_google_drive_model->is_connected($user_id);
    }
}

/**
 * Cek apakah Drive institusi (admin) sudah terhubung.
 */
if (!function_exists('is_admin_google_drive_connected')) {
    function is_admin_google_drive_connected() {
        $CI =& get_instance();
        $CI->load->library('Upload_to_drive');
        return (bool) $CI->upload_to_drive->find_connected_admin_user_id();
    }
}

/**
 * Blokir aksi upload jika Drive ADMIN belum terhubung.
 */
if (!function_exists('require_google_drive_or_redirect')) {
    function require_google_drive_or_redirect($user_id = null, $redirect_url = null) {
        if (is_admin_google_drive_connected()) {
            return true;
        }
        $CI =& get_instance();
        $role = $CI->session->userdata('role');
        if ($role === 'admin') {
            $CI->session->set_flashdata('error', 'Hubungkan Google Drive institusi di menu Profil sebelum mengunggah berkas. Jika kapasitas penuh, Putuskan lalu Hubungkan akun lain.');
            redirect($redirect_url ? $redirect_url : 'Profile');
        } else {
            $CI->session->set_flashdata('error', 'Penyimpanan Google Drive belum siap. Hubungi admin untuk menghubungkan akun Drive institusi.');
            redirect($redirect_url ? $redirect_url : 'Dashboard');
        }
        return false;
    }
}

