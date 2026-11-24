<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Setting Grade Controller
 * Mengelola setting grade dan bobot nilai akhir untuk PKL dan Skripsi
 */
class Setting_grade extends MY_Controller {

    public function __construct() {
        parent::__construct();
        $this->check_role('admin');
        $this->load->model('Grade_setting_model');
        $this->load->model('Bobot_nilai_setting_model');
        $this->load->library('form_validation');
    }

    /**
     * Index - Tampilkan semua setting grade dan bobot
     */
    public function index() {
        $tipe = $this->input->get('tipe'); // pkl atau skripsi
        
        $data = array(
            'title' => 'Setting Grade & Bobot Nilai',
            'page_title' => 'Setting Grade & Bobot Nilai Akhir',
            'breadcrumb' => array(
                array('label' => 'System', 'url' => '#'),
                array('label' => 'Setting Grade', 'active' => true)
            ),
            'sidebar_menu' => generate_sidebar_menu('admin'),
            'tipe' => $tipe ? $tipe : 'pkl',
            'grade_pkl' => $this->Grade_setting_model->get_by_tipe('pkl', true), // Include inactive
            'grade_skripsi' => $this->Grade_setting_model->get_by_tipe('skripsi', true), // Include inactive
            'bobot_pkl' => $this->Bobot_nilai_setting_model->get_by_tipe('pkl'),
            'bobot_skripsi' => $this->Bobot_nilai_setting_model->get_by_tipe('skripsi')
        );

        $data['content'] = $this->load->view('admin/setting_grade/index', $data, true);
        $this->load->view('layouts/main', $data);
    }

    /**
     * Edit grade setting
     */
    public function edit_grade($id) {
        $grade = $this->Grade_setting_model->get_by_id($id);
        
        if (!$grade) {
            $this->session->set_flashdata('error', 'Grade setting tidak ditemukan.');
            redirect('Setting_grade');
        }

        if ($this->input->method() === 'post') {
            $this->form_validation->set_rules('grade', 'Grade', 'required|trim|max_length[10]');
            $this->form_validation->set_rules('nilai_min', 'Nilai Minimum', 'required|trim|numeric');
            $this->form_validation->set_rules('nilai_max', 'Nilai Maksimum', 'trim|numeric');
            $this->form_validation->set_rules('keterangan', 'Keterangan', 'trim');
            
            // Validasi unique grade manual - cek sebelum form_validation->run()
            $grade_input = strtoupper(trim($this->input->post('grade')));
            $existing = $this->Grade_setting_model->get_by_tipe_grade($grade->tipe, $grade_input, true);
            
            if ($existing && $existing->id != $id) {
                $this->session->set_flashdata('error', 'Grade "' . $grade_input . '" untuk tipe ' . strtoupper($grade->tipe) . ' sudah ada.');
                $data = array(
                    'title' => 'Edit Grade Setting',
                    'page_title' => 'Edit Grade Setting',
                    'breadcrumb' => array(
                        array('label' => 'System', 'url' => '#'),
                        array('label' => 'Setting Grade & Bobot', 'url' => site_url('Setting_grade')),
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
                        array('label' => 'Setting Grade & Bobot', 'url' => site_url('Setting_grade')),
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
                    redirect('Setting_grade?tipe=' . $grade->tipe);
                } else {
                    $this->session->set_flashdata('error', 'Gagal memperbarui grade setting.');
                    redirect('Setting_grade/edit_grade/' . $id);
                }
            }
        } else {
            $data = array(
                'title' => 'Edit Grade Setting',
                'page_title' => 'Edit Grade Setting',
                'breadcrumb' => array(
                    array('label' => 'System', 'url' => '#'),
                    array('label' => 'Setting Grade & Bobot', 'url' => site_url('Setting_grade')),
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
        $tipe = $this->input->get('tipe') ? $this->input->get('tipe') : 'pkl';

        if ($this->input->method() === 'post') {
            $this->form_validation->set_rules('tipe', 'Tipe', 'required|trim|in_list[pkl,skripsi]');
            $this->form_validation->set_rules('grade', 'Grade', 'required|trim|max_length[10]');
            $this->form_validation->set_rules('nilai_min', 'Nilai Minimum', 'required|trim|numeric');
            $this->form_validation->set_rules('nilai_max', 'Nilai Maksimum', 'trim|numeric');
            $this->form_validation->set_rules('keterangan', 'Keterangan', 'trim');
            
            // Validasi unique grade manual
            $tipe_input = $this->input->post('tipe');
            $grade_input = strtoupper(trim($this->input->post('grade')));
            $existing = $this->Grade_setting_model->get_by_tipe_grade($tipe_input, $grade_input, true);
            
            if ($existing) {
                $this->session->set_flashdata('error', 'Grade "' . $grade_input . '" untuk tipe ' . strtoupper($tipe_input) . ' sudah ada.');
                $data = array(
                    'title' => 'Tambah Grade Setting',
                    'page_title' => 'Tambah Grade Setting',
                    'breadcrumb' => array(
                        array('label' => 'System', 'url' => '#'),
                        array('label' => 'Setting Grade & Bobot', 'url' => site_url('Setting_grade')),
                        array('label' => 'Tambah Grade', 'active' => true)
                    ),
                    'sidebar_menu' => generate_sidebar_menu('admin'),
                    'tipe' => $tipe
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
                        array('label' => 'Setting Grade & Bobot', 'url' => site_url('Setting_grade')),
                        array('label' => 'Tambah Grade', 'active' => true)
                    ),
                    'sidebar_menu' => generate_sidebar_menu('admin'),
                    'tipe' => $tipe
                );
                $data['content'] = $this->load->view('admin/setting_grade/create_grade', $data, true);
                $this->load->view('layouts/main', $data);
            } else {
                $data = array(
                    'tipe' => $this->input->post('tipe'),
                    'grade' => strtoupper(trim($this->input->post('grade'))),
                    'nilai_min' => floatval($this->input->post('nilai_min')),
                    'nilai_max' => $this->input->post('nilai_max') ? floatval($this->input->post('nilai_max')) : null,
                    'keterangan' => $this->input->post('keterangan'),
                    'is_aktif' => $this->input->post('is_aktif') ? 1 : 0
                );

                if ($this->Grade_setting_model->create($data)) {
                    $this->session->set_flashdata('success', 'Grade setting berhasil ditambahkan.');
                    redirect('Setting_grade?tipe=' . $data['tipe']);
                } else {
                    $this->session->set_flashdata('error', 'Gagal menambahkan grade setting.');
                    redirect('Setting_grade/create_grade?tipe=' . $tipe);
                }
            }
        } else {
            $data = array(
                'title' => 'Tambah Grade Setting',
                'page_title' => 'Tambah Grade Setting',
                'breadcrumb' => array(
                    array('label' => 'System', 'url' => '#'),
                    array('label' => 'Setting Grade & Bobot', 'url' => site_url('Setting_grade')),
                    array('label' => 'Tambah Grade', 'active' => true)
                ),
                'sidebar_menu' => generate_sidebar_menu('admin'),
                'tipe' => $tipe
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
        
        if (!$grade) {
            $this->session->set_flashdata('error', 'Grade setting tidak ditemukan.');
            redirect('Setting_grade');
        }

        if ($this->Grade_setting_model->delete($id)) {
            $this->session->set_flashdata('success', 'Grade setting berhasil dihapus.');
        } else {
            $this->session->set_flashdata('error', 'Gagal menghapus grade setting.');
        }
        
        redirect('Setting_grade?tipe=' . $grade->tipe);
    }

    /**
     * Edit bobot nilai setting
     */
    public function edit_bobot($id) {
        $bobot = $this->Bobot_nilai_setting_model->get_by_id($id);
        
        if (!$bobot) {
            $this->session->set_flashdata('error', 'Bobot setting tidak ditemukan.');
            redirect('Setting_grade');
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
                        array('label' => 'Setting Grade', 'url' => site_url('Setting_grade')),
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
                    redirect('Setting_grade?tipe=' . $bobot->tipe);
                } else {
                    $this->session->set_flashdata('error', 'Gagal memperbarui bobot nilai.');
                    redirect('Setting_grade/edit_bobot/' . $id);
                }
            }
        } else {
            $data = array(
                'title' => 'Edit Bobot Nilai',
                'page_title' => 'Edit Bobot Nilai',
                'breadcrumb' => array(
                    array('label' => 'System', 'url' => '#'),
                    array('label' => 'Setting Grade', 'url' => site_url('Setting_grade')),
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

