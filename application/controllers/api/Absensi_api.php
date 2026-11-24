<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Absensi_api extends CI_Controller {

    public function __construct() {
        parent::__construct();
        $this->load->helper('api');
        api_handle_options();
        api_send_cors_headers();
        $this->load->library('Mobile_absensi_service');
    }

    public function scan() {
        if ($this->input->method(TRUE) !== 'POST') {
            api_json_response(array('success' => false, 'message' => 'Method tidak diizinkan.'), 405);
        }
        $payload_jwt = api_require_mahasiswa_jwt();
        $id_mahasiswa = (int) $payload_jwt['ref_id'];

        $body = api_read_json_input();
        $mode = isset($body['mode']) ? strtolower(trim((string) $body['mode'])) : '';
        $token = isset($body['token']) ? trim((string) $body['token']) : '';
        $latitude = isset($body['latitude']) ? $body['latitude'] : null;
        $longitude = isset($body['longitude']) ? $body['longitude'] : null;

        if ($token === '') {
            api_json_response(array('success' => false, 'message' => 'Token wajib diisi.'), 400);
        }

        $allowed = array('kegiatan', 'uts', 'uas', 'ujian');
        if (!in_array($mode, $allowed, true)) {
            api_json_response(array(
                'success' => false,
                'message' => 'Mode tidak valid. Gunakan: kegiatan, uts, uas, atau ujian.',
            ), 400);
        }

        try {
            if ($mode === 'kegiatan') {
                $out = $this->mobile_absensi_service->scan_kegiatan($id_mahasiswa, $token, $latitude, $longitude);
            } else {
                $out = $this->mobile_absensi_service->scan_ujian($id_mahasiswa, $token, $mode);
            }
        } catch (Throwable $e) {
            log_message('error', 'Absensi_api::scan: ' . $e->getMessage());
            api_json_response(array(
                'success' => false,
                'message' => 'Terjadi kesalahan server saat memproses absensi. Silakan coba lagi.',
            ), 500);
        }

        $code = !empty($out['success']) ? 200 : 400;
        api_json_response($out, $code);
    }
}
