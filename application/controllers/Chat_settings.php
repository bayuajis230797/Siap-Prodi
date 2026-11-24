<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Chat_settings extends MY_Controller {

    public function __construct() {
        parent::__construct();
        $this->check_role('admin');
        $this->load->model('Chat_settings_model');
        $this->load->library('form_validation');
    }

    public function index() {
        $data = array(
            'title' => 'Pengaturan Chat',
            'page_title' => 'Pengaturan Chat',
            'breadcrumb' => array(
                array('label' => 'Pengaturan Chat', 'active' => true)
            ),
            'sidebar_menu' => generate_sidebar_menu('admin'),
            'settings' => $this->Chat_settings_model->get_settings(),
        );
        $data['content'] = $this->load->view('admin/chat_settings', $data, true);
        $this->load->view('layouts/main', $data);
    }

    public function update() {
        if ($this->input->method() !== 'post') {
            show_404();
        }

        $data = array(
            'enable_dosen_to_mahasiswa' => $this->input->post('enable_dosen_to_mahasiswa') ? 1 : 0,
            'enable_sekprodi_to_mahasiswa' => $this->input->post('enable_sekprodi_to_mahasiswa') ? 1 : 0,
            'enable_admin_to_mahasiswa' => $this->input->post('enable_admin_to_mahasiswa') ? 1 : 0,
            'enable_mahasiswa_to_dosen' => $this->input->post('enable_mahasiswa_to_dosen') ? 1 : 0,
            'enable_mahasiswa_to_mahasiswa' => $this->input->post('enable_mahasiswa_to_mahasiswa') ? 1 : 0,
            'mhs_dosen_rule' => $this->input->post('mhs_dosen_rule') === 'any_dosen' ? 'any_dosen' : 'pembimbing_only',
            'mhs_mhs_rule' => $this->input->post('mhs_mhs_rule') === 'any_active' ? 'any_active' : 'same_class',
        );

        if ($this->Chat_settings_model->upsert($data)) {
            $this->session->set_flashdata('success', 'Pengaturan chat berhasil disimpan.');
        } else {
            $this->session->set_flashdata('error', 'Gagal menyimpan pengaturan chat.');
        }

        redirect('Chat_settings');
    }
}
