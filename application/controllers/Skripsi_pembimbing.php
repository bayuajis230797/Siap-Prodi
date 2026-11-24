<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Skripsi Pembimbing Controller
 * Mengatur daftar dosen yang dapat menjadi pembimbing skripsi
 */
class Skripsi_pembimbing extends MY_Controller {

    public function __construct() {
        parent::__construct();
        $this->check_role('sekprodi');
        $this->load->model('Skripsi_pembimbing_model');
        $this->load->model('Dosen_model');
    }

    public function index() {
        if ($this->input->method() === 'post') {
            $pembimbing1 = $this->input->post('pembimbing_1') ?? array();
            $pembimbing2 = $this->input->post('pembimbing_2') ?? array();
            $aktif = $this->input->post('is_active') ?? array();

            $dosen_list = $this->Dosen_model->get_all();
            foreach ($dosen_list as $dosen) {
                $id = $dosen->id;
                $allow1 = in_array($id, $pembimbing1) ? 1 : 0;
                $allow2 = in_array($id, $pembimbing2) ? 1 : 0;
                $is_active = in_array($id, $aktif) ? 1 : 0;

                // Jika tidak aktif, otomatis set tidak diperbolehkan
                if (!$is_active) {
                    $allow1 = 0;
                    $allow2 = 0;
                }

                $this->Skripsi_pembimbing_model->upsert($id, $allow1, $allow2, $is_active);
            }

            $this->session->set_flashdata('success', 'Pengaturan pembimbing skripsi berhasil disimpan.');
            redirect('Skripsi_pembimbing');
        }

        $dosen_list = $this->Dosen_model->get_all();
        $permissions = $this->Skripsi_pembimbing_model->get_map();

        $data['title'] = 'Pembimbing Skripsi';
        $data['page_title'] = 'Pengaturan Pembimbing Skripsi';
        $data['breadcrumb'] = array(
            array('label' => 'Skripsi', 'url' => site_url('Skripsi')),
            array('label' => 'Pembimbing Skripsi', 'active' => true)
        );
        $data['dosen_list'] = $dosen_list;
        $data['permissions'] = $permissions;
        $data['sidebar_menu'] = generate_sidebar_menu('sekprodi');
        $data['content'] = $this->load->view('skripsi/pembimbing', $data, true);
        $this->load->view('layouts/main', $data);
    }
}













