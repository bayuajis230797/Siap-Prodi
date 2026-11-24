<?php
defined('BASEPATH') OR exit('No direct script access allowed');

if (!function_exists('api_handle_options')) {
    function api_handle_options() {
        $ci =& get_instance();
        if (strtoupper($ci->input->server('REQUEST_METHOD')) === 'OPTIONS') {
            api_send_cors_headers();
            http_response_code(204);
            exit;
        }
    }
}

if (!function_exists('api_send_cors_headers')) {
    function api_send_cors_headers() {
        header('Access-Control-Allow-Origin: *');
        header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
        header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');
    }
}

if (!function_exists('api_json_response')) {
    function api_json_response($data, $http_code = 200) {
        while (ob_get_level()) {
            ob_end_clean();
        }
        api_send_cors_headers();
        http_response_code((int) $http_code);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($data);
        exit;
    }
}

if (!function_exists('api_read_json_input')) {
    /**
     * @return array
     */
    function api_read_json_input() {
        $raw = file_get_contents('php://input');
        if ($raw === false || $raw === '') {
            return array();
        }
        $decoded = json_decode($raw, true);
        return is_array($decoded) ? $decoded : array();
    }
}

if (!function_exists('api_bearer_token')) {
    /**
     * @return string|null
     */
    function api_bearer_token() {
        $ci =& get_instance();
        $auth = $ci->input->get_request_header('Authorization', true);
        if (!$auth || stripos($auth, 'Bearer ') !== 0) {
            return null;
        }
        return trim(substr($auth, 7));
    }
}

if (!function_exists('api_require_mahasiswa_jwt')) {
    /**
     * @return array JWT payload (sub, ref_id, role, ...)
     */
    function api_require_mahasiswa_jwt() {
        $ci =& get_instance();
        $ci->config->load('api', true);
        $secret = $ci->config->item('jwt_secret', 'api');
        $token = api_bearer_token();
        if (!$token) {
            api_json_response(array('success' => false, 'message' => 'Token tidak ada.'), 401);
        }
        $payload = api_jwt_decode($token, $secret);
        if (!$payload || empty($payload['sub']) || ($payload['role'] ?? '') !== 'mahasiswa' || empty($payload['ref_id'])) {
            api_json_response(array('success' => false, 'message' => 'Token tidak valid atau kedaluwarsa.'), 401);
        }
        return $payload;
    }
}
