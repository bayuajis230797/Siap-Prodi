<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Setting Grade Proposal TA Controller
 * Mengelola setting grade untuk Proposal TA
 */
class Setting_grade_proposal extends MY_Controller {

    public function __construct() {
        parent::__construct();
        $this->check_role('admin');
        $this->load->model('Grade_setting_model');
        $this->load->library('form_validation');
    }

    /**
     * Index - Tampilkan setting grade Proposal TA
     */
    public function index() {
        $data = array(
            'title' => 'Setting Grade Proposal TA',
            'page_title' => 'Setting Grade Proposal TA',
            'breadcrumb' => array(
                array('label' => 'System', 'url' => '#'),
                array('label' => 'Setting Grade Proposal TA', 'active' => true)
            ),
            'sidebar_menu' => generate_sidebar_menu('admin'),
            'grade_proposal' => $this->Grade_setting_model->get_by_tipe('proposal', true) // Include inactive
        );

        $data['content'] = $this->load->view('admin/setting_grade_proposal/index', $data, true);
        $this->load->view('layouts/main', $data);
    }

    /**
     * Edit grade setting
     */
    public function edit_grade($id) {
        $grade = $this->Grade_setting_model->get_by_id($id);
        
        if (!$grade || $grade->tipe !== 'proposal') {
            $this->session->set_flashdata('error', 'Grade setting tidak ditemukan.');
            redirect('Setting_grade_proposal');
        }

        if ($this->input->method() === 'post') {
            $this->form_validation->set_rules('grade', 'Grade', 'required|trim|max_length[10]');
            $this->form_validation->set_rules('nilai_min', 'Nilai Minimum', 'required|trim|numeric');
            $this->form_validation->set_rules('nilai_max', 'Nilai Maksimum', 'trim|numeric');
            $this->form_validation->set_rules('keterangan', 'Keterangan', 'trim');
            
            // Validasi unique grade manual
            $grade_input = strtoupper(trim($this->input->post('grade')));
            $existing = $this->Grade_setting_model->get_by_tipe_grade('proposal', $grade_input, true);
            
            if ($existing && $existing->id != $id) {
                $this->session->set_flashdata('error', 'Grade "' . $grade_input . '" untuk Proposal TA sudah ada.');
                $data = array(
                    'title' => 'Edit Grade Setting',
                    'page_title' => 'Edit Grade Setting',
                    'breadcrumb' => array(
                        array('label' => 'System', 'url' => '#'),
                        array('label' => 'Setting Grade Proposal TA', 'url' => site_url('Setting_grade_proposal')),
                        array('label' => 'Edit Grade', 'active' => true)
                    ),
                    'sidebar_menu' => generate_sidebar_menu('admin'),
                    'grade' => $grade
                );
                $data['content'] = $this->load->view('admin/setting_grade_proposal/edit', $data, true);
                $this->load->view('layouts/main', $data);
                return;
            }

            if ($this->form_validation->run() == FALSE) {
                $data = array(
                    'title' => 'Edit Grade Setting',
                    'page_title' => 'Edit Grade Setting',
                    'breadcrumb' => array(
                        array('label' => 'System', 'url' => '#'),
                        array('label' => 'Setting Grade Proposal TA', 'url' => site_url('Setting_grade_proposal')),
                        array('label' => 'Edit Grade', 'active' => true)
                    ),
                    'sidebar_menu' => generate_sidebar_menu('admin'),
                    'grade' => $grade
                );
                $data['content'] = $this->load->view('admin/setting_grade_proposal/edit', $data, true);
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
                    redirect('Setting_grade_proposal');
                } else {
                    $this->session->set_flashdata('error', 'Gagal memperbarui grade setting.');
                    redirect('Setting_grade_proposal/edit_grade/' . $id);
                }
            }
        } else {
            $data = array(
                'title' => 'Edit Grade Setting',
                'page_title' => 'Edit Grade Setting',
                'breadcrumb' => array(
                    array('label' => 'System', 'url' => '#'),
                    array('label' => 'Setting Grade Proposal TA', 'url' => site_url('Setting_grade_proposal')),
                    array('label' => 'Edit Grade', 'active' => true)
                ),
                'sidebar_menu' => generate_sidebar_menu('admin'),
                'grade' => $grade
            );
            $data['content'] = $this->load->view('admin/setting_grade_proposal/edit', $data, true);
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
            $existing = $this->Grade_setting_model->get_by_tipe_grade('proposal', $grade_input, true);
            
            if ($existing) {
                $this->session->set_flashdata('error', 'Grade "' . $grade_input . '" untuk Proposal TA sudah ada.');
                $data = array(
                    'title' => 'Tambah Grade Setting',
                    'page_title' => 'Tambah Grade Setting',
                    'breadcrumb' => array(
                        array('label' => 'System', 'url' => '#'),
                        array('label' => 'Setting Grade Proposal TA', 'url' => site_url('Setting_grade_proposal')),
                        array('label' => 'Tambah Grade', 'active' => true)
                    ),
                    'sidebar_menu' => generate_sidebar_menu('admin'),
                    'tipe' => 'proposal'
                );
                $data['content'] = $this->load->view('admin/setting_grade_proposal/create', $data, true);
                $this->load->view('layouts/main', $data);
                return;
            }

            if ($this->form_validation->run() == FALSE) {
                $data = array(
                    'title' => 'Tambah Grade Setting',
                    'page_title' => 'Tambah Grade Setting',
                    'breadcrumb' => array(
                        array('label' => 'System', 'url' => '#'),
                        array('label' => 'Setting Grade Proposal TA', 'url' => site_url('Setting_grade_proposal')),
                        array('label' => 'Tambah Grade', 'active' => true)
                    ),
                    'sidebar_menu' => generate_sidebar_menu('admin'),
                    'tipe' => 'proposal'
                );
                $data['content'] = $this->load->view('admin/setting_grade_proposal/create', $data, true);
                $this->load->view('layouts/main', $data);
            } else {
                $data = array(
                    'tipe' => 'proposal',
                    'grade' => strtoupper(trim($this->input->post('grade'))),
                    'nilai_min' => floatval($this->input->post('nilai_min')),
                    'nilai_max' => $this->input->post('nilai_max') ? floatval($this->input->post('nilai_max')) : null,
                    'keterangan' => $this->input->post('keterangan'),
                    'is_aktif' => $this->input->post('is_aktif') ? 1 : 0
                );

                if ($this->Grade_setting_model->create($data)) {
                    $this->session->set_flashdata('success', 'Grade setting berhasil ditambahkan.');
                    redirect('Setting_grade_proposal');
                } else {
                    $this->session->set_flashdata('error', 'Gagal menambahkan grade setting.');
                    redirect('Setting_grade_proposal/create_grade');
                }
            }
        } else {
            $data = array(
                'title' => 'Tambah Grade Setting',
                'page_title' => 'Tambah Grade Setting',
                'breadcrumb' => array(
                    array('label' => 'System', 'url' => '#'),
                    array('label' => 'Setting Grade Proposal TA', 'url' => site_url('Setting_grade_proposal')),
                    array('label' => 'Tambah Grade', 'active' => true)
                ),
                'sidebar_menu' => generate_sidebar_menu('admin'),
                'tipe' => 'proposal'
            );
            $data['content'] = $this->load->view('admin/setting_grade_proposal/create', $data, true);
            $this->load->view('layouts/main', $data);
        }
    }

    /**
     * Delete grade setting
     */
    public function delete_grade($id) {
        if ($this->input->method() !== 'post') {
            redirect('Setting_grade_proposal');
        }
        $grade = $this->Grade_setting_model->get_by_id($id);
        
        if (!$grade || $grade->tipe !== 'proposal') {
            $this->session->set_flashdata('error', 'Grade setting tidak ditemukan.');
            redirect('Setting_grade_proposal');
        }

        if ($this->Grade_setting_model->delete($id)) {
            $this->session->set_flashdata('success', 'Grade setting berhasil dihapus.');
        } else {
            $this->session->set_flashdata('error', 'Gagal menghapus grade setting.');
        }
        
        redirect('Setting_grade_proposal');
    }
}







