<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Pkl_setting_radius Controller (Sekprodi)
 * Controller untuk pengaturan radius absensi PKL
 */
class Pkl_setting_radius extends MY_Controller {

    public function __construct() {
        parent::__construct();
        $this->check_role('sekprodi');
        $this->load->model('Pkl_setting_radius_model');
        $this->load->model('Tahun_akademik_model');
        $this->load->library('form_validation');
    }

    /**
     * Index - Daftar setting radius
     */
    public function index() {
        $settings = $this->Pkl_setting_radius_model->get_all();
        $tahun_aktif = $this->Tahun_akademik_model->get_aktif();
        
        // Ambil setting untuk tahun aktif
        $setting_aktif = null;
        if ($tahun_aktif) {
            $setting_aktif = $this->Pkl_setting_radius_model->get_by_tahun_akademik($tahun_aktif->id);
        }

        $data = array(
            'title' => 'Pengaturan Radius Absensi PKL',
            'page_title' => 'Pengaturan Radius Absensi PKL',
            'breadcrumb' => array(
                array('label' => 'PKL', 'url' => '#'),
                array('label' => 'Setting Radius', 'active' => true)
            ),
            'sidebar_menu' => generate_sidebar_menu('sekprodi'),
            'settings' => $settings,
            'tahun_aktif' => $tahun_aktif,
            'setting_aktif' => $setting_aktif
        );
        
        $data['content'] = $this->load->view('sekprodi/pkl/setting_radius', $data, true);
        $this->load->view('layouts/main', $data);
    }

    /**
     * Create - Form buat setting radius baru
     */
    public function create() {
        $tahun_aktif = $this->Tahun_akademik_model->get_aktif();
        
        if (!$tahun_aktif) {
            $this->session->set_flashdata('error', 'Tidak ada tahun akademik aktif.');
            redirect('Pkl_setting_radius');
        }

        // Cek apakah sudah ada setting untuk tahun aktif
        $existing = $this->Pkl_setting_radius_model->get_by_tahun_akademik($tahun_aktif->id);
        if ($existing) {
            $this->session->set_flashdata('info', 'Setting radius untuk tahun akademik aktif sudah ada. Silakan edit setting yang ada.');
            redirect('Pkl_setting_radius/edit/' . $existing->id);
        }

        $data = array(
            'title' => 'Tambah Setting Radius',
            'page_title' => 'Tambah Setting Radius Absensi PKL',
            'breadcrumb' => array(
                array('label' => 'PKL', 'url' => '#'),
                array('label' => 'Setting Radius', 'url' => site_url('Pkl_setting_radius')),
                array('label' => 'Tambah Baru', 'active' => true)
            ),
            'sidebar_menu' => generate_sidebar_menu('sekprodi'),
            'tahun_aktif' => $tahun_aktif
        );
        
        $data['content'] = $this->load->view('sekprodi/pkl/setting_radius_form', $data, true);
        $this->load->view('layouts/main', $data);
    }

    /**
     * Store - Simpan setting radius baru
     */
    public function store() {
        $this->form_validation->set_rules('radius_meter', 'Radius (Meter)', 'required|integer|greater_than[0]|less_than_equal_to[1000]');
        $this->form_validation->set_rules('id_tahun_akademik', 'Tahun Akademik', 'required|integer');

        if ($this->form_validation->run() == FALSE) {
            $this->create();
        } else {
            $id_tahun_akademik = (int)$this->input->post('id_tahun_akademik');
            
            // Cek apakah sudah ada setting untuk tahun akademik ini
            $existing = $this->Pkl_setting_radius_model->get_by_tahun_akademik($id_tahun_akademik);
            if ($existing) {
                $this->session->set_flashdata('error', 'Setting radius untuk tahun akademik ini sudah ada. Silakan edit setting yang ada.');
                redirect('Pkl_setting_radius/edit/' . $existing->id);
            }

            $data = array(
                'radius_meter' => (int)$this->input->post('radius_meter'),
                'id_tahun_akademik' => $id_tahun_akademik,
                'created_by' => $this->session->userdata('user_id')
            );

            $id = $this->Pkl_setting_radius_model->create($data);

            if ($id) {
                $this->session->set_flashdata('success', 'Setting radius berhasil dibuat.');
                redirect('Pkl_setting_radius');
            } else {
                $this->session->set_flashdata('error', 'Gagal membuat setting radius.');
                redirect('Pkl_setting_radius/create');
            }
        }
    }

    /**
     * Edit - Form edit setting radius
     */
    public function edit($id) {
        $setting = $this->Pkl_setting_radius_model->get_by_id($id);
        
        if (!$setting) {
            $this->session->set_flashdata('error', 'Setting radius tidak ditemukan.');
            redirect('Pkl_setting_radius');
        }

        $data = array(
            'title' => 'Edit Setting Radius',
            'page_title' => 'Edit Setting Radius Absensi PKL',
            'breadcrumb' => array(
                array('label' => 'PKL', 'url' => '#'),
                array('label' => 'Setting Radius', 'url' => site_url('Pkl_setting_radius')),
                array('label' => 'Edit', 'active' => true)
            ),
            'sidebar_menu' => generate_sidebar_menu('sekprodi'),
            'setting' => $setting
        );
        
        $data['content'] = $this->load->view('sekprodi/pkl/setting_radius_form', $data, true);
        $this->load->view('layouts/main', $data);
    }

    /**
     * Update - Update setting radius
     */
    public function update($id) {
        $setting = $this->Pkl_setting_radius_model->get_by_id($id);
        
        if (!$setting) {
            $this->session->set_flashdata('error', 'Setting radius tidak ditemukan.');
            redirect('Pkl_setting_radius');
        }

        $this->form_validation->set_rules('radius_meter', 'Radius (Meter)', 'required|integer|greater_than[0]|less_than_equal_to[1000]');

        if ($this->form_validation->run() == FALSE) {
            $this->edit($id);
        } else {
            $data = array(
                'radius_meter' => (int)$this->input->post('radius_meter')
            );

            if ($this->Pkl_setting_radius_model->update($id, $data)) {
                $this->session->set_flashdata('success', 'Setting radius berhasil diupdate.');
                redirect('Pkl_setting_radius');
            } else {
                $this->session->set_flashdata('error', 'Gagal mengupdate setting radius.');
                redirect('Pkl_setting_radius/edit/' . $id);
            }
        }
    }

    /**
     * Delete - Hapus setting radius
     */
    public function delete($id) {
        $setting = $this->Pkl_setting_radius_model->get_by_id($id);
        
        if (!$setting) {
            $this->session->set_flashdata('error', 'Setting radius tidak ditemukan.');
            redirect('Pkl_setting_radius');
        }

        if ($this->Pkl_setting_radius_model->delete($id)) {
            $this->session->set_flashdata('success', 'Setting radius berhasil dihapus.');
        } else {
            $this->session->set_flashdata('error', 'Gagal menghapus setting radius.');
        }

        redirect('Pkl_setting_radius');
    }
}

