<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Setting Grade Skripsi Controller
 * Mengelola setting grade untuk Skripsi
 */
class Setting_grade_skripsi extends MY_Controller {

    public function __construct() {
        parent::__construct();
        $this->check_role('admin');
        $this->load->model('Grade_setting_model');
        $this->load->library('form_validation');
    }

    /**
     * Index - Tampilkan setting grade Skripsi
     */
    public function index() {
        $data = array(
            'title' => 'Setting Grade Skripsi',
            'page_title' => 'Setting Grade Skripsi',
            'breadcrumb' => array(
                array('label' => 'System', 'url' => '#'),
                array('label' => 'Setting Grade Skripsi', 'active' => true)
            ),
            'sidebar_menu' => generate_sidebar_menu('admin'),
            'grade_skripsi' => $this->Grade_setting_model->get_by_tipe('skripsi', true) // Include inactive
        );

        $data['content'] = $this->load->view('admin/setting_grade_skripsi/index', $data, true);
        $this->load->view('layouts/main', $data);
    }

    /**
     * Edit grade setting
     */
    public function edit_grade($id) {
        $grade = $this->Grade_setting_model->get_by_id($id);
        
        if (!$grade || $grade->tipe !== 'skripsi') {
            $this->session->set_flashdata('error', 'Grade setting tidak ditemukan.');
            redirect('Setting_grade_skripsi');
        }

        if ($this->input->method() === 'post') {
            $this->form_validation->set_rules('grade', 'Grade', 'required|trim|max_length[10]');
            $this->form_validation->set_rules('nilai_min', 'Nilai Minimum', 'required|trim|numeric');
            $this->form_validation->set_rules('nilai_max', 'Nilai Maksimum', 'trim|numeric');
            $this->form_validation->set_rules('keterangan', 'Keterangan', 'trim');
            
            // Validasi unique grade manual
            $grade_input = strtoupper(trim($this->input->post('grade')));
            $existing = $this->Grade_setting_model->get_by_tipe_grade('skripsi', $grade_input, true);
            
            if ($existing && $existing->id != $id) {
                $this->session->set_flashdata('error', 'Grade "' . $grade_input . '" untuk Skripsi sudah ada.');
                $data = array(
                    'title' => 'Edit Grade Setting',
                    'page_title' => 'Edit Grade Setting',
                    'breadcrumb' => array(
                        array('label' => 'System', 'url' => '#'),
                        array('label' => 'Setting Grade Skripsi', 'url' => site_url('Setting_grade_skripsi')),
                        array('label' => 'Edit Grade', 'active' => true)
                    ),
                    'sidebar_menu' => generate_sidebar_menu('admin'),
                    'grade' => $grade
                );
                $data['content'] = $this->load->view('admin/setting_grade/edit_grade', $data, true);
                $this->load->view('layouts/main', $data);
                return;
            }

            if ($this->form_validation->run() == FALSE) {
                $data = array(
                    'title' => 'Edit Grade Setting',
                    'page_title' => 'Edit Grade Setting',
                    'breadcrumb' => array(
                        array('label' => 'System', 'url' => '#'),
                        array('label' => 'Setting Grade Skripsi', 'url' => site_url('Setting_grade_skripsi')),
                        array('label' => 'Edit Grade', 'active' => true)
                    ),
                    'sidebar_menu' => generate_sidebar_menu('admin'),
                    'grade' => $grade
                );
                $data['content'] = $this->load->view('admin/setting_grade/edit_grade', $data, true);
                $this->load->view('layouts/main', $data);
            } else {
                $data = array(
                    'grade' => strtoupper(trim($this->input->post('grade'))),
                    'nilai_min' => floatval($this->input->post('nilai_min')),
                    'nilai_max' => $this->input->post('nilai_max') ? floatval($this->input->post('nilai_max')) : null,
                    'keterangan' => $this->input->post('keterangan'),
                    'is_aktif' => $this->input->post('is_aktif') ? 1 : 0
                );

                if ($this->Grade_setting_model->update($id, $data)) {
                    $this->session->set_flashdata('success', 'Grade setting berhasil diperbarui.');
                    redirect('Setting_grade_skripsi');
                } else {
                    $this->session->set_flashdata('error', 'Gagal memperbarui grade setting.');
                    redirect('Setting_grade_skripsi/edit_grade/' . $id);
                }
            }
        } else {
            $data = array(
                'title' => 'Edit Grade Setting',
                'page_title' => 'Edit Grade Setting',
                'breadcrumb' => array(
                    array('label' => 'System', 'url' => '#'),
                    array('label' => 'Setting Grade Skripsi', 'url' => site_url('Setting_grade_skripsi')),
                    array('label' => 'Edit Grade', 'active' => true)
                ),
                'sidebar_menu' => generate_sidebar_menu('admin'),
                'grade' => $grade
            );
            $data['content'] = $this->load->view('admin/setting_grade/edit_grade', $data, true);
            $this->load->view('layouts/main', $data);
        }
    }

    /**
     * Create grade setting baru
     */
    public function create_grade() {
        if ($this->input->method() === 'post') {
            $this->form_validation->set_rules('grade', 'Grade', 'required|trim|max_length[10]');
            $this->form_validation->set_rules('nilai_min', 'Nilai Minimum', 'required|trim|numeric');
            $this->form_validation->set_rules('nilai_max', 'Nilai Maksimum', 'trim|numeric');
            $this->form_validation->set_rules('keterangan', 'Keterangan', 'trim');
            
            // Validasi unique grade manual
            $grade_input = strtoupper(trim($this->input->post('grade')));
            $existing = $this->Grade_setting_model->get_by_tipe_grade('skripsi', $grade_input, true);
            
            if ($existing) {
                $this->session->set_flashdata('error', 'Grade "' . $grade_input . '" untuk Skripsi sudah ada.');
                $data = array(
                    'title' => 'Tambah Grade Setting',
                    'page_title' => 'Tambah Grade Setting',
                    'breadcrumb' => array(
                        array('label' => 'System', 'url' => '#'),
                        array('label' => 'Setting Grade Skripsi', 'url' => site_url('Setting_grade_skripsi')),
                        array('label' => 'Tambah Grade', 'active' => true)
                    ),
                    'sidebar_menu' => generate_sidebar_menu('admin'),
                    'tipe' => 'skripsi'
                );
                $data['content'] = $this->load->view('admin/setting_grade/create_grade', $data, true);
                $this->load->view('layouts/main', $data);
                return;
            }

            if ($this->form_validation->run() == FALSE) {
                $data = array(
                    'title' => 'Tambah Grade Setting',
                    'page_title' => 'Tambah Grade Setting',
                    'breadcrumb' => array(
                        array('label' => 'System', 'url' => '#'),
                        array('label' => 'Setting Grade Skripsi', 'url' => site_url('Setting_grade_skripsi')),
                        array('label' => 'Tambah Grade', 'active' => true)
                    ),
                    'sidebar_menu' => generate_sidebar_menu('admin'),
                    'tipe' => 'skripsi'
                );
                $data['content'] = $this->load->view('admin/setting_grade/create_grade', $data, true);
                $this->load->view('layouts/main', $data);
            } else {
                $data = array(
                    'tipe' => 'skripsi',
                    'grade' => strtoupper(trim($this->input->post('grade'))),
                    'nilai_min' => floatval($this->input->post('nilai_min')),
                    'nilai_max' => $this->input->post('nilai_max') ? floatval($this->input->post('nilai_max')) : null,
                    'keterangan' => $this->input->post('keterangan'),
                    'is_aktif' => $this->input->post('is_aktif') ? 1 : 0
                );

                if ($this->Grade_setting_model->create($data)) {
                    $this->session->set_flashdata('success', 'Grade setting berhasil ditambahkan.');
                    redirect('Setting_grade_skripsi');
                } else {
                    $this->session->set_flashdata('error', 'Gagal menambahkan grade setting.');
                    redirect('Setting_grade_skripsi/create_grade');
                }
            }
        } else {
            $data = array(
                'title' => 'Tambah Grade Setting',
                'page_title' => 'Tambah Grade Setting',
                'breadcrumb' => array(
                    array('label' => 'System', 'url' => '#'),
                    array('label' => 'Setting Grade Skripsi', 'url' => site_url('Setting_grade_skripsi')),
                    array('label' => 'Tambah Grade', 'active' => true)
                ),
                'sidebar_menu' => generate_sidebar_menu('admin'),
                'tipe' => 'skripsi'
            );
            $data['content'] = $this->load->view('admin/setting_grade/create_grade', $data, true);
            $this->load->view('layouts/main', $data);
        }
    }

    /**
     * Delete grade setting
     */
    public function delete_grade($id) {
        $grade = $this->Grade_setting_model->get_by_id($id);
        
        if (!$grade || $grade->tipe !== 'skripsi') {
            $this->session->set_flashdata('error', 'Grade setting tidak ditemukan.');
            redirect('Setting_grade_skripsi');
        }

        if ($this->Grade_setting_model->delete($id)) {
            $this->session->set_flashdata('success', 'Grade setting berhasil dihapus.');
        } else {
            $this->session->set_flashdata('error', 'Gagal menghapus grade setting.');
        }
        
        redirect('Setting_grade_skripsi');
    }
}







