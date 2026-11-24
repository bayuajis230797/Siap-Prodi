<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Helper untuk encode/decode ID agar tidak terlihat di URL
 * Menggunakan enkripsi sederhana dengan key
 */

if (!function_exists('encode_id')) {
    /**
     * Encode ID menjadi string yang aman untuk URL
     * @param int $id
     * @return string
     */
    function encode_id($id) {
        if (!is_numeric($id) || $id < 1) {
            return '';
        }
        $CI =& get_instance();
        $key = $CI->config->item('encryption_key');
        if (empty($key)) {
            $key = 'siharkat_bimbingan_skripsi_' . md5(BASEPATH);
        }
        $data = $id . '|' . substr(hash_hmac('sha256', (string)$id, $key), 0, 16);
        $encoded = base64_encode($data);
        return str_replace(['+', '/', '='], ['-', '_', ''], $encoded);
    }
}

if (!function_exists('decode_id')) {
    /**
     * Decode string kembali ke ID
     * @param string $encoded
     * @return int|false ID atau false jika invalid
     */
    function decode_id($encoded) {
        if (empty($encoded) || !is_string($encoded)) {
            return false;
        }
        $encoded = str_replace(['-', '_'], ['+', '/'], $encoded);
        $padding = strlen($encoded) % 4;
        if ($padding) {
            $encoded .= str_repeat('=', 4 - $padding);
        }
        $decoded = base64_decode($encoded);
        if ($decoded === false) {
            return false;
        }
        $parts = explode('|', $decoded);
        if (count($parts) !== 2) {
            return false;
        }
        $id = $parts[0];
        $hash = $parts[1];
        $CI =& get_instance();
        $key = $CI->config->item('encryption_key');
        if (empty($key)) {
            $key = 'siharkat_bimbingan_skripsi_' . md5(BASEPATH);
        }
        $expected = substr(hash_hmac('sha256', (string)$id, $key), 0, 16);
        if (!hash_equals($expected, $hash)) {
            return false;
        }
        return is_numeric($id) && (int)$id > 0 ? (int)$id : false;
    }
}
