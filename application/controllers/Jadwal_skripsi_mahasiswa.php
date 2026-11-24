<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Jadwal_skripsi_mahasiswa Controller
 * Menangani view jadwal sidang untuk mahasiswa
 */
class Jadwal_skripsi_mahasiswa extends MY_Controller {

    public function __construct() {
        parent::__construct();
        $this->check_role('mahasiswa');
        $this->load->model('Jadwal_skripsi_model');
        $this->load->model('Skripsi_model');
        $this->load->model('Tahun_akademik_model');
    }

    /**
     * Index - lihat jadwal sidang mahasiswa
     */
    public function index() {
        $id_mahasiswa = $this->session->userdata('ref_id');
        $mahasiswa = $this->db->get_where('mahasiswa', array('id' => $id_mahasiswa))->row();
        
        if (!$mahasiswa) {
            $this->session->set_flashdata('error', 'Data mahasiswa tidak ditemukan.');
            redirect('Dashboard');
        }

        $tahun_aktif = $this->Tahun_akademik_model->get_aktif();
        if (!$tahun_aktif) {
            $this->session->set_flashdata('error', 'Tidak ada tahun akademik aktif.');
            redirect('Dashboard');
        }

        // Ambil data mahasiswa_skripsi terlebih dahulu untuk mendapatkan id_mahasiswa_skripsi
        $skripsi_data = $this->Skripsi_model->get_by_mahasiswa($mahasiswa->id, $tahun_aktif->id);
        
        // Ambil jadwal sidang mahasiswa
        $jadwal_list = array();
        if ($skripsi_data && $skripsi_data->id) {
            $jadwal_list = $this->Jadwal_skripsi_model->get_by_mahasiswa($skripsi_data->id);
        }

        // Ambil data skripsi untuk nilai (gunakan data yang sudah diambil di atas)
        $skripsi = $skripsi_data;

        $data = array(
            'title' => 'Jadwal Sidang Skripsi',
            'page_title' => 'Jadwal Sidang Skripsi',
            'breadcrumb' => array(
                array('label' => 'Skripsi', 'url' => site_url('Skripsi_pengajuan')),
                array('label' => 'Jadwal Sidang', 'active' => true)
            ),
            'sidebar_menu' => generate_sidebar_menu('mahasiswa'),
            'jadwal_list' => $jadwal_list,
            'skripsi' => $skripsi,
            'tahun_aktif' => $tahun_aktif
        );

        $data['content'] = $this->load->view('jadwal_skripsi_mahasiswa/index', $data, true);
        $this->load->view('layouts/main', $data);
    }

    /**
     * Detail jadwal sidang
     */
    public function detail($id) {
        $id_mahasiswa = $this->session->userdata('ref_id');
        $mahasiswa = $this->db->get_where('mahasiswa', array('id' => $id_mahasiswa))->row();
        
        if (!$mahasiswa) {
            $this->session->set_flashdata('error', 'Data mahasiswa tidak ditemukan.');
            redirect('Dashboard');
        }

        $jadwal = $this->Jadwal_skripsi_model->get_by_id($id);
        if (!$jadwal || $jadwal->id_mahasiswa != $mahasiswa->id) {
            $this->session->set_flashdata('error', 'Jadwal sidang tidak ditemukan.');
            redirect('Jadwal_skripsi_mahasiswa');
        }

        $data = array(
            'title' => 'Detail Jadwal Sidang',
            'page_title' => 'Detail Jadwal Sidang',
            'breadcrumb' => array(
                array('label' => 'Jadwal Sidang', 'url' => site_url('Jadwal_skripsi_mahasiswa')),
                array('label' => 'Detail', 'active' => true)
            ),
            'sidebar_menu' => generate_sidebar_menu('mahasiswa'),
            'jadwal' => $jadwal
        );

        $data['content'] = $this->load->view('jadwal_skripsi_mahasiswa/detail', $data, true);
        $this->load->view('layouts/main', $data);
    }
}

