<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Tanda_terima_api extends CI_Controller {

    public function __construct() {
        parent::__construct();
        $this->load->helper('api');
        api_handle_options();
        api_send_cors_headers();
        $this->load->library('Mobile_tanda_terima_service');
    }

    public function scan() {
        if ($this->input->method(TRUE) !== 'POST') {
            api_json_response(array('success' => false, 'message' => 'Method tidak diizinkan.'), 405);
        }
        $payload_jwt = api_require_mahasiswa_jwt();
        $id_mahasiswa = (int) $payload_jwt['ref_id'];

        $body = api_read_json_input();
        $token = isset($body['token']) ? trim((string) $body['token']) : '';
        if ($token === '') {
            api_json_response(array('success' => false, 'message' => 'Token wajib diisi.'), 400);
        }

        try {
            $out = $this->mobile_tanda_terima_service->scan_mahasiswa($id_mahasiswa, $token);
        } catch (Throwable $e) {
            log_message('error', 'Tanda_terima_api::scan: ' . $e->getMessage());
            api_json_response(array(
                'success' => false,
                'message' => 'Terjadi kesalahan server. Silakan coba lagi.',
            ), 500);
        }

        $code = !empty($out['success']) ? 200 : 400;
        api_json_response($out, $code);
    }
}
