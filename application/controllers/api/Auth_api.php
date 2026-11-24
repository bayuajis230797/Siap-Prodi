<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Login mobile (mahasiswa) — JWT, tanpa MY_Controller.
 */
class Auth_api extends CI_Controller {

    public function __construct() {
        parent::__construct();
        $this->load->helper('api');
        $this->load->helper('jwt');
        api_handle_options();
        api_send_cors_headers();
        $this->load->model('User_model');
    }

    public function login() {
        if ($this->input->method(TRUE) !== 'POST') {
            api_json_response(array('success' => false, 'message' => 'Method tidak diizinkan.'), 405);
        }

        $this->config->load('api', true);
        $max_attempts = (int) $this->config->item('login_max_attempts', 'api');
        $lock_seconds = (int) $this->config->item('login_lock_seconds', 'api');
        if ($max_attempts <= 0) {
            $max_attempts = 5;
        }
        if ($lock_seconds <= 0) {
            $lock_seconds = 600;
        }

        $lock_until = (int) $this->session->userdata('login_lock_until');
        if ($lock_until > time()) {
            $remaining = (int) ceil(($lock_until - time()) / 60);
            api_json_response(array(
                'success' => false,
                'message' => 'Terlalu banyak percobaan gagal. Silakan coba lagi dalam ' . $remaining . ' menit.',
            ), 429);
        }

        $body = api_read_json_input();
        $username = isset($body['username']) ? trim((string) $body['username']) : '';
        $password = isset($body['password']) ? (string) $body['password'] : '';

        if ($username === '' || $password === '') {
            api_json_response(array('success' => false, 'message' => 'Username dan password wajib diisi.'), 400);
        }

        $user = $this->User_model->check_login($username, $password);
        if (!$user || ($user->nama_role ?? '') !== 'mahasiswa' || empty($user->ref_id)) {
            $attempts = (int) $this->session->userdata('login_attempts') + 1;
            $this->session->set_userdata('login_attempts', $attempts);
            if ($attempts >= $max_attempts) {
                $this->session->set_userdata('login_lock_until', time() + $lock_seconds);
            }
            api_json_response(array('success' => false, 'message' => 'Login gagal. Periksa username/password atau akun bukan mahasiswa.'), 401);
        }

        $this->session->unset_userdata('login_attempts');
        $this->session->unset_userdata('login_lock_until');

        $mahasiswa = $this->User_model->get_mahasiswa_by_ref($user->ref_id);
        if (!$mahasiswa) {
            api_json_response(array('success' => false, 'message' => 'Data mahasiswa tidak ditemukan.'), 403);
        }

        $device_fp = isset($body['device_fingerprint']) ? trim((string) $body['device_fingerprint']) : '';
        $this->load->model('Mobile_device_binding_model');
        $bind = $this->Mobile_device_binding_model->assert_binding_or_bind((int) $user->ref_id, $device_fp);
        if (empty($bind['ok'])) {
            api_json_response(array(
                'success' => false,
                'message' => isset($bind['message']) ? $bind['message'] : 'Perangkat tidak diizinkan.',
            ), 403);
        }

        $secret = $this->config->item('jwt_secret', 'api');
        $ttl = (int) $this->config->item('jwt_ttl_seconds', 'api');
        $kelas_jwt = '';
        if (isset($mahasiswa->kelas) && $mahasiswa->kelas !== null && $mahasiswa->kelas !== '') {
            $kelas_jwt = (string) $mahasiswa->kelas;
        }

        $token = api_jwt_encode(array(
            'sub' => (int) $user->id,
            'role' => 'mahasiswa',
            'ref_id' => (int) $user->ref_id,
            'nim' => $mahasiswa->nim,
            'nama' => $mahasiswa->nama_mahasiswa,
            'kelas' => $kelas_jwt,
        ), $secret, $ttl);

        if ($this->db->table_exists('user_activity_log')) {
            $this->load->model('User_activity_log_model');
            $this->User_activity_log_model->create(array(
                'user_id' => (int) $user->id,
                'activity_type' => 'login',
                'description' => 'Login mobile API | username=' . $user->username,
                'ip_address' => $this->input->ip_address(),
                'user_agent' => substr($this->input->user_agent(), 0, 500),
            ));
        }

        api_json_response(array(
            'success' => true,
            'message' => 'Login berhasil.',
            'token' => $token,
            'token_type' => 'Bearer',
            'expires_in' => $ttl,
            'user' => array(
                'id' => (int) $user->id,
                'username' => $user->username,
                'nama' => $mahasiswa->nama_mahasiswa,
                'nim' => $mahasiswa->nim,
                'kelas' => $kelas_jwt,
                'id_mahasiswa' => (int) $user->ref_id,
            ),
        ));
    }
}
