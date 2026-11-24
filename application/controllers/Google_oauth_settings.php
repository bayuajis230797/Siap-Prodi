<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Admin: konfigurasi OAuth Google Drive (Client ID/Secret/Redirect URI).
 */
class Google_oauth_settings extends MY_Controller {

    public function __construct() {
        parent::__construct();
        $this->check_role('admin');
        $this->load->model('Google_oauth_setting_model');
        $this->load->library('Google_drive_oauth');
        $this->load->library('form_validation');
    }

    public function index() {
        $settings = $this->Google_oauth_setting_model->get_settings();
        $has_secret = !empty($settings->client_secret_enc);

        $data = array(
            'title' => 'Pengaturan Google Drive OAuth',
            'page_title' => 'Pengaturan Google Drive OAuth',
            'breadcrumb' => array(
                array('label' => 'System', 'url' => site_url('App_settings')),
                array('label' => 'Google Drive OAuth', 'active' => true)
            ),
            'sidebar_menu' => generate_sidebar_menu('admin'),
            'settings' => $settings,
            'has_secret' => $has_secret,
            'is_configured' => $this->Google_oauth_setting_model->is_configured(),
            'suggested_redirect' => site_url('Profile/oauth_callback')
        );

        $data['content'] = $this->load->view('admin/google_oauth_settings', $data, true);
        $this->load->view('layouts/main', $data);
    }

    public function save() {
        if ($this->input->method() !== 'post') {
            show_404();
        }

        $this->form_validation->set_rules('client_id', 'Client ID', 'required|trim');
        $this->form_validation->set_rules('redirect_uri', 'Redirect URI', 'required|trim|valid_url');

        $secret_input = trim((string) $this->input->post('client_secret'));
        $settings = $this->Google_oauth_setting_model->get_settings();
        if ($secret_input === '' && empty($settings->client_secret_enc)) {
            $this->form_validation->set_rules('client_secret', 'Client Secret', 'required|trim');
        }

        if ($this->form_validation->run() === FALSE) {
            $this->session->set_flashdata('error', validation_errors());
            redirect('Google_oauth_settings');
        }

        $data = array(
            'client_id' => trim($this->input->post('client_id')),
            // Redirect mengikuti host saat ini; daftar keduanya di Google Console
            'redirect_uri' => site_url('Profile/oauth_callback'),
            'is_aktif' => 1
        );

        if ($secret_input !== '') {
            $data['client_secret_enc'] = $this->google_drive_oauth->encrypt($secret_input);
        }

        if ($this->Google_oauth_setting_model->save($data)) {
            $this->session->set_flashdata('success', 'Pengaturan Google OAuth berhasil disimpan.');
        } else {
            $this->session->set_flashdata('error', 'Gagal menyimpan pengaturan.');
        }
        redirect('Google_oauth_settings');
    }
}
