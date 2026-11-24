<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Setting Bobot PKL Controller
 * Mengelola setting bobot nilai akhir untuk PKL
 */
class Setting_bobot_pkl extends MY_Controller {

    public function __construct() {
        parent::__construct();
        $this->check_role('admin');
        $this->load->model('Bobot_nilai_setting_model');
        $this->load->library('form_validation');
    }

    /**
     * Index - Tampilkan setting bobot PKL
     */
    public function index() {
        $data = array(
            'title' => 'Setting Bobot Nilai PKL',
            'page_title' => 'Setting Bobot Nilai Akhir PKL',
            'breadcrumb' => array(
                array('label' => 'System', 'url' => '#'),
                array('label' => 'Setting Bobot PKL', 'active' => true)
            ),
            'sidebar_menu' => generate_sidebar_menu('admin'),
            'bobot_pkl' => $this->Bobot_nilai_setting_model->get_by_tipe('pkl')
        );

        $data['content'] = $this->load->view('admin/setting_bobot_pkl/index', $data, true);
        $this->load->view('layouts/main', $data);
    }

    /**
     * Edit bobot nilai setting
     */
    public function edit_bobot($id) {
        $bobot = $this->Bobot_nilai_setting_model->get_by_id($id);
        
        if (!$bobot || $bobot->tipe !== 'pkl') {
            $this->session->set_flashdata('error', 'Bobot setting tidak ditemukan.');
            redirect('Setting_bobot_pkl');
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
                        array('label' => 'Setting Bobot PKL', 'url' => site_url('Setting_bobot_pkl')),
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
                    redirect('Setting_bobot_pkl');
                } else {
                    $this->session->set_flashdata('error', 'Gagal memperbarui bobot nilai.');
                    redirect('Setting_bobot_pkl/edit_bobot/' . $id);
                }
            }
        } else {
            $data = array(
                'title' => 'Edit Bobot Nilai',
                'page_title' => 'Edit Bobot Nilai',
                'breadcrumb' => array(
                    array('label' => 'System', 'url' => '#'),
                    array('label' => 'Setting Bobot PKL', 'url' => site_url('Setting_bobot_pkl')),
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







