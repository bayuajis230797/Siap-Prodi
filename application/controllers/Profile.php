<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Profile Controller
 * Semua role: ubah password.
 * Admin saja: Hubungkan / Putuskan Google Drive institusi (penyimpanan seluruh sistem).
 */
class Profile extends MY_Controller {

    public function __construct() {
        parent::__construct();
        if (!$this->session->userdata('user_id')) {
            redirect('Auth');
        }
        $this->load->model('User_model');
        $this->load->model('User_google_drive_model');
        $this->load->model('Google_oauth_setting_model');
        $this->load->library('Google_drive_oauth');
        $this->load->library('form_validation');
    }

    public function index() {
        $user_id = (int) $this->session->userdata('user_id');
        $user = $this->User_model->get_user_by_id($user_id);

        if (!$user) {
            $this->session->set_flashdata('error', 'Data user tidak ditemukan.');
            redirect('Dashboard');
        }

        if ($this->input->method() === 'post') {
            $this->form_validation->set_rules('password_lama', 'Password Lama', 'required|trim');
            $this->form_validation->set_rules('password_baru', 'Password Baru', 'required|trim|min_length[5]');
            $this->form_validation->set_rules('password_baru_confirm', 'Konfirmasi Password Baru', 'required|trim|matches[password_baru]');

            if ($this->form_validation->run() == FALSE) {
                $this->render_profile($user);
                return;
            }

            $password_lama = $this->input->post('password_lama');
            $password_baru = $this->input->post('password_baru');

            $current_user = $this->User_model->check_login($user->username, $password_lama);
            if (!$current_user) {
                $this->session->set_flashdata('error', 'Password lama tidak benar.');
                redirect('Profile');
            }

            if ($this->User_model->update($user_id, array('password' => trim($password_baru)))) {
                $this->session->set_flashdata('success', 'Password berhasil diubah.');
            } else {
                $this->session->set_flashdata('error', 'Gagal mengubah password.');
            }
            redirect('Profile');
        }

        $this->render_profile($user);
    }

    /**
     * Mulai OAuth Google Drive — admin only.
     */
    public function connect_drive() {
        $this->require_admin_drive_access();

        if (!$this->google_drive_oauth->is_configured()) {
            $this->session->set_flashdata('error', 'Isi Client ID/Secret di menu System → Google Drive OAuth terlebih dahulu.');
            redirect('Profile');
        }

        $url = $this->google_drive_oauth->build_auth_url((int) $this->session->userdata('user_id'));
        if (!$url) {
            $this->session->set_flashdata('error', 'Gagal membuat URL otorisasi Google.');
            redirect('Profile');
        }
        redirect($url);
    }

    /**
     * Callback OAuth dari Google — admin only.
     */
    public function oauth_callback() {
        // Callback bisa datang setelah login admin; pastikan role admin
        if ($this->session->userdata('role') !== 'admin') {
            $this->session->set_flashdata('error', 'Hanya admin yang dapat menghubungkan Google Drive institusi.');
            redirect('Profile');
        }

        $error = $this->input->get('error');
        if ($error) {
            $this->session->set_flashdata('error', 'Otorisasi Google dibatalkan: ' . htmlspecialchars($error));
            redirect('Profile');
        }

        $code = $this->input->get('code');
        $state = $this->input->get('state');
        if (!$code || !$state) {
            $this->session->set_flashdata('error', 'Callback OAuth tidak lengkap.');
            redirect('Profile');
        }

        $result = $this->google_drive_oauth->handle_callback($code, $state);
        if (!empty($result['ok'])) {
            $this->session->set_flashdata('success', 'Google Drive institusi terhubung. Semua unggahan sistem akan masuk ke akun ini.');
        } else {
            $this->session->set_flashdata('error', isset($result['message']) ? $result['message'] : 'Gagal menghubungkan Google Drive.');
        }
        redirect('Profile');
    }

    /**
     * Putuskan koneksi Google Drive — admin only (ganti akun jika penuh).
     */
    public function disconnect_drive() {
        $this->require_admin_drive_access();
        if ($this->input->method() !== 'post') {
            show_404();
        }
        $user_id = (int) $this->session->userdata('user_id');
        $this->google_drive_oauth->disconnect($user_id);
        $this->session->set_flashdata('success', 'Koneksi Google Drive diputus. Hubungkan akun lain jika ingin mengganti penyimpanan.');
        redirect('Profile');
    }

    protected function require_admin_drive_access() {
        if ($this->session->userdata('role') !== 'admin') {
            $this->session->set_flashdata('error', 'Hanya admin yang mengelola koneksi Google Drive institusi.');
            redirect('Profile');
        }
    }

    protected function render_profile($user) {
        $role = $this->session->userdata('role');
        $is_admin = ($role === 'admin');
        $user_id = (int) $user->id;

        $drive = null;
        $connected = false;
        if ($is_admin) {
            $drive = $this->User_google_drive_model->get_by_user($user_id);
            $connected = $this->User_google_drive_model->is_connected($user_id);
        }

        $data = array(
            'user' => $user,
            'title' => 'Profil',
            'page_title' => 'Profil',
            'breadcrumb' => array(
                array('label' => 'Profil', 'active' => true)
            ),
            'sidebar_menu' => generate_sidebar_menu($role),
            'is_admin' => $is_admin,
            'drive' => $drive,
            'drive_connected' => $connected,
            'oauth_configured' => $this->Google_oauth_setting_model->is_configured()
        );
        $data['content'] = $this->load->view('profile/index', $data, true);
        $this->load->view('layouts/main', $data);
    }
}
