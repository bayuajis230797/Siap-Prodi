<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Setting Bobot Skripsi Controller
 * Mengelola setting bobot nilai akhir untuk Skripsi
 */
class Setting_bobot_skripsi extends MY_Controller {

    public function __construct() {
        parent::__construct();
        $this->check_role('admin');
        $this->load->model('Bobot_nilai_setting_model');
        $this->load->library('form_validation');
    }

    /**
     * Index - Tampilkan setting bobot Skripsi
     */
    public function index() {
        $data = array(
            'title' => 'Setting Bobot Nilai Skripsi',
            'page_title' => 'Setting Bobot Nilai Akhir Skripsi',
            'breadcrumb' => array(
                array('label' => 'System', 'url' => '#'),
                array('label' => 'Setting Bobot Skripsi', 'active' => true)
            ),
            'sidebar_menu' => generate_sidebar_menu('admin'),
            'bobot_skripsi' => $this->Bobot_nilai_setting_model->get_by_tipe('skripsi')
        );

        $data['content'] = $this->load->view('admin/setting_bobot_skripsi/index', $data, true);
        $this->load->view('layouts/main', $data);
    }

    /**
     * Edit bobot nilai setting
     */
    public function edit_bobot($id) {
        $bobot = $this->Bobot_nilai_setting_model->get_by_id($id);
        
        if (!$bobot || $bobot->tipe !== 'skripsi') {
            $this->session->set_flashdata('error', 'Bobot setting tidak ditemukan.');
            redirect('Setting_bobot_skripsi');
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
                        array('label' => 'Setting Bobot Skripsi', 'url' => site_url('Setting_bobot_skripsi')),
                        array('label' => 'Edit Bobot', 'active' => true)
                    ),
                    'sidebar_menu' => generate_sidebar_menu('admin'),
                    'bobot' => $bobot
                );
                $data['content'] = $this->load->view('admin/setting_grade/edit_bobot', $data, true);
                $this->load->view('layouts/main', $data);
            } else {
                $data = array(
                    'bobot' => floatval($this->input->post('bobot')),
                    'keterangan' => $this->input->post('keterangan'),
                    'is_aktif' => $this->input->post('is_aktif') ? 1 : 0
                );

                if ($this->Bobot_nilai_setting_model->update($id, $data)) {
                    $this->session->set_flashdata('success', 'Bobot nilai berhasil diperbarui.');
                    redirect('Setting_bobot_skripsi');
                } else {
                    $this->session->set_flashdata('error', 'Gagal memperbarui bobot nilai.');
                    redirect('Setting_bobot_skripsi/edit_bobot/' . $id);
                }
            }
        } else {
            $data = array(
                'title' => 'Edit Bobot Nilai',
                'page_title' => 'Edit Bobot Nilai',
                'breadcrumb' => array(
                    array('label' => 'System', 'url' => '#'),
                    array('label' => 'Setting Bobot Skripsi', 'url' => site_url('Setting_bobot_skripsi')),
                    array('label' => 'Edit Bobot', 'active' => true)
                ),
                'sidebar_menu' => generate_sidebar_menu('admin'),
                'bobot' => $bobot
            );
            $data['content'] = $this->load->view('admin/setting_grade/edit_bobot', $data, true);
            $this->load->view('layouts/main', $data);
        }
    }
}







