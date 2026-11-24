<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Setting Bobot Proposal TA Controller
 * Mengelola setting bobot nilai akhir untuk Proposal TA
 */
class Setting_bobot_proposal extends MY_Controller {

    public function __construct() {
        parent::__construct();
        $this->check_role('admin');
        $this->load->model('Bobot_nilai_setting_model');
        $this->load->library('form_validation');
    }

    /**
     * Index - Tampilkan setting bobot Proposal TA
     */
    public function index() {
        $data = array(
            'title' => 'Setting Bobot Nilai Proposal TA',
            'page_title' => 'Setting Bobot Nilai Akhir Proposal TA',
            'breadcrumb' => array(
                array('label' => 'System', 'url' => '#'),
                array('label' => 'Setting Bobot Proposal TA', 'active' => true)
            ),
            'sidebar_menu' => generate_sidebar_menu('admin'),
            'bobot_proposal' => $this->Bobot_nilai_setting_model->get_by_tipe('proposal')
        );

        $data['content'] = $this->load->view('admin/setting_bobot_proposal/index', $data, true);
        $this->load->view('layouts/main', $data);
    }

    /**
     * Edit bobot nilai setting
     */
    public function edit_bobot($id) {
        $bobot = $this->Bobot_nilai_setting_model->get_by_id($id);
        
        if (!$bobot || $bobot->tipe !== 'proposal') {
            $this->session->set_flashdata('error', 'Bobot setting tidak ditemukan.');
            redirect('Setting_bobot_proposal');
        }

        if ($this->input->method() === 'post') {
            $this->form_validation->set_rules('bobot', 'Bobot', 'required|trim|numeric|greater_than[0]|less_than_equal_to[100]');
            $this->form_validation->set_rules('keterangan', 'Keterangan', 'trim');

            if ($this->form_validation->run() == FALSE) {
                $data = array(
                    'title' => 'Edit Bobot Nilai',
                    'page_title' => 'Edit Bobot Nilai',
                    'breadcrumb' => array(
                        array('label' => 'System', 'url' => '#'),
                        array('label' => 'Setting Bobot Proposal TA', 'url' => site_url('Setting_bobot_proposal')),
                        array('label' => 'Edit Bobot', 'active' => true)
                    ),
                    'sidebar_menu' => generate_sidebar_menu('admin'),
                    'bobot' => $bobot
                );
                $data['content'] = $this->load->view('admin/setting_bobot_proposal/edit', $data, true);
                $this->load->view('layouts/main', $data);
            } else {
                $data = array(
                    'bobot' => floatval($this->input->post('bobot')),
                    'keterangan' => $this->input->post('keterangan'),
                    'is_aktif' => $this->input->post('is_aktif') ? 1 : 0
                );

                if ($this->Bobot_nilai_setting_model->update($id, $data)) {
                    $total = 0;
                    foreach ($this->Bobot_nilai_setting_model->get_by_tipe('proposal') as $b) {
                        $total += (float) $b->bobot;
                    }
                    if (abs($total - 100) > 0.001) {
                        $this->session->set_flashdata('error', 'Bobot tersimpan, tetapi total bobot Proposal TA sekarang ' . $total . '% (harus 100%). Perhitungan nilai akhir tidak akan akurat sampai total = 100%.');
                    } else {
                        $this->session->set_flashdata('success', 'Bobot nilai berhasil diperbarui.');
                    }
                    redirect('Setting_bobot_proposal');
                } else {
                    $this->session->set_flashdata('error', 'Gagal memperbarui bobot nilai.');
                    redirect('Setting_bobot_proposal/edit_bobot/' . $id);
                }
            }
        } else {
            $data = array(
                'title' => 'Edit Bobot Nilai',
                'page_title' => 'Edit Bobot Nilai',
                'breadcrumb' => array(
                    array('label' => 'System', 'url' => '#'),
                    array('label' => 'Setting Bobot Proposal TA', 'url' => site_url('Setting_bobot_proposal')),
                    array('label' => 'Edit Bobot', 'active' => true)
                ),
                'sidebar_menu' => generate_sidebar_menu('admin'),
                'bobot' => $bobot
            );
            $data['content'] = $this->load->view('admin/setting_bobot_proposal/edit', $data, true);
            $this->load->view('layouts/main', $data);
        }
    }
}







