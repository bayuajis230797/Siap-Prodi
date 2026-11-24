<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Pkl_revisi_mahasiswa Controller
 * Untuk mahasiswa melihat catatan revisi dari dosen penguji PKL
 */
class Pkl_revisi_mahasiswa extends MY_Controller {

    public function __construct() {
        parent::__construct();
        $this->check_role('mahasiswa');
        $this->load->model('Jadwal_pkl_model');
        $this->load->model('Pkl_penilaian_model');
        $this->load->model('Pkl_pengajuan_model');
        $this->load->model('Tahun_akademik_model');
        $this->load->model('Mahasiswa_pkl_model');
        $this->load->helper('menu');
    }

    /**
     * Daftar jadwal sidang PKL dengan catatan revisi
     */
    public function index() {
        $id_mahasiswa = $this->session->userdata('ref_id');
        $tahun_aktif = $this->Tahun_akademik_model->get_aktif();
        
        if (!$tahun_aktif) {
            $this->session->set_flashdata('error', 'Tidak ada tahun akademik aktif.');
            redirect('Dashboard');
        }

        // Cek apakah mahasiswa terdaftar PKL
        $is_registered = $this->Mahasiswa_pkl_model->is_registered($id_mahasiswa, $tahun_aktif->id);
        if (!$is_registered) {
            $this->session->set_flashdata('error', 'Anda belum terdaftar untuk PKL.');
            redirect('Dashboard');
        }

        // Ambil jadwal sidang PKL mahasiswa
        $jadwal_list = $this->Jadwal_pkl_model->get_by_mahasiswa($id_mahasiswa, $tahun_aktif->id);
        
        // Untuk setiap jadwal, ambil catatan revisi dari penguji
        $catatan_revisi = array();
        foreach ($jadwal_list as $jadwal) {
            // Ambil semua penilaian dari penguji untuk jadwal ini
            $penilaian_list = $this->Pkl_penilaian_model->get_by_jadwal_pkl($jadwal->id);
            
            // Gabungkan catatan revisi dari semua penguji
            $catatan_revisi[$jadwal->id] = array(
                'penilaian_list' => $penilaian_list
            );
        }

        $data = array(
            'title' => 'Jadwal Sidang PKL',
            'page_title' => 'Jadwal Sidang PKL',
            'breadcrumb' => array(
                array('label' => 'Jadwal', 'url' => '#'),
                array('label' => 'Jadwal Sidang PKL', 'active' => true)
            ),
            'sidebar_menu' => generate_sidebar_menu('mahasiswa'),
            'jadwal_list' => $jadwal_list,
            'catatan_revisi' => $catatan_revisi,
            'tahun_aktif' => $tahun_aktif
        );

        $data['content'] = $this->load->view('mahasiswa/pkl_revisi_index', $data, true);
        $this->load->view('layouts/main', $data);
    }

    /**
     * Detail jadwal sidang PKL dengan catatan revisi
     */
    public function detail($jadwal_id) {
        $id_mahasiswa = $this->session->userdata('ref_id');
        $tahun_aktif = $this->Tahun_akademik_model->get_aktif();
        
        if (!$tahun_aktif) {
            $this->session->set_flashdata('error', 'Tidak ada tahun akademik aktif.');
            redirect('Pkl_revisi_mahasiswa');
        }

        // Ambil jadwal
        $jadwal = $this->Jadwal_pkl_model->get_by_id($jadwal_id);
        
        if (!$jadwal || $jadwal->id_mahasiswa != $id_mahasiswa) {
            $this->session->set_flashdata('error', 'Jadwal sidang PKL tidak ditemukan.');
            redirect('Pkl_revisi_mahasiswa');
        }

        // Ambil penilaian dari penguji untuk jadwal ini
        $penilaian_list = $this->Pkl_penilaian_model->get_by_jadwal_pkl($jadwal->id);
        
        // Ambil pengajuan PKL untuk mendapatkan judul
        $pengajuan = $this->Pkl_pengajuan_model->get_by_mahasiswa($id_mahasiswa, $tahun_aktif->id);

        $data = array(
            'title' => 'Detail Jadwal Sidang PKL',
            'page_title' => 'Detail Jadwal Sidang PKL',
            'breadcrumb' => array(
                array('label' => 'Jadwal', 'url' => '#'),
                array('label' => 'Jadwal Sidang PKL', 'url' => site_url('Pkl_revisi_mahasiswa')),
                array('label' => 'Detail', 'active' => true)
            ),
            'sidebar_menu' => generate_sidebar_menu('mahasiswa'),
            'jadwal' => $jadwal,
            'penilaian_list' => $penilaian_list,
            'pengajuan' => $pengajuan,
            'tahun_aktif' => $tahun_aktif
        );

        $data['content'] = $this->load->view('mahasiswa/pkl_revisi_detail', $data, true);
        $this->load->view('layouts/main', $data);
    }
}

