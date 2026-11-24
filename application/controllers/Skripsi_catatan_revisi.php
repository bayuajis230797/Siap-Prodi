<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Skripsi_catatan_revisi Controller
 * Untuk mahasiswa melihat catatan revisi dari dosen penguji Skripsi
 */
class Skripsi_catatan_revisi extends MY_Controller {

    public function __construct() {
        parent::__construct();
        $this->check_role('mahasiswa');
        $this->load->model('Jadwal_skripsi_model');
        $this->load->model('Skripsi_nilai_sidang_model');
        $this->load->model('Skripsi_model');
        $this->load->model('Tahun_akademik_model');
        $this->load->helper('menu');
    }

    /**
     * Daftar jadwal sidang Skripsi dengan catatan revisi
     */
    public function index() {
        $id_mahasiswa = $this->session->userdata('ref_id');
        $tahun_aktif = $this->Tahun_akademik_model->get_aktif();
        
        if (!$tahun_aktif) {
            $this->session->set_flashdata('error', 'Tidak ada tahun akademik aktif.');
            redirect('Dashboard');
        }

        // Cek apakah mahasiswa terdaftar Skripsi
        $skripsi = $this->Skripsi_model->get_by_mahasiswa($id_mahasiswa, $tahun_aktif->id);
        if (!$skripsi) {
            $this->session->set_flashdata('error', 'Anda belum terdaftar untuk Skripsi.');
            redirect('Dashboard');
        }

        // Ambil jadwal sidang Skripsi mahasiswa
        $jadwal_list = $this->Jadwal_skripsi_model->get_by_mahasiswa($skripsi->id);
        
        // Untuk setiap jadwal, ambil catatan revisi dari penguji
        $catatan_revisi = array();
        foreach ($jadwal_list as $jadwal) {
            // Ambil semua penilaian dari penguji untuk jadwal ini
            $nilai_sidang_list = $this->Skripsi_nilai_sidang_model->get_by_jadwal($jadwal->id);
            
            // Gabungkan catatan revisi dari semua penguji
            $catatan_revisi[$jadwal->id] = array(
                'nilai_sidang_list' => $nilai_sidang_list
            );
        }

        $data = array(
            'title' => 'Catatan Revisi Skripsi',
            'page_title' => 'Catatan Revisi Skripsi',
            'breadcrumb' => array(
                array('label' => 'Skripsi', 'url' => '#'),
                array('label' => 'Catatan Revisi', 'active' => true)
            ),
            'sidebar_menu' => generate_sidebar_menu('mahasiswa'),
            'jadwal_list' => $jadwal_list,
            'catatan_revisi' => $catatan_revisi,
            'skripsi' => $skripsi,
            'tahun_aktif' => $tahun_aktif
        );

        $data['content'] = $this->load->view('mahasiswa/skripsi_catatan_revisi/index', $data, true);
        $this->load->view('layouts/main', $data);
    }

    /**
     * Detail jadwal sidang Skripsi dengan catatan revisi
     */
    public function detail($jadwal_id) {
        $id_mahasiswa = $this->session->userdata('ref_id');
        $tahun_aktif = $this->Tahun_akademik_model->get_aktif();
        
        if (!$tahun_aktif) {
            $this->session->set_flashdata('error', 'Tidak ada tahun akademik aktif.');
            redirect('Skripsi_catatan_revisi');
        }

        // Ambil jadwal
        $jadwal = $this->Jadwal_skripsi_model->get_by_id($jadwal_id);
        
        // Cek apakah jadwal milik mahasiswa yang login
        $skripsi = $this->Skripsi_model->get_by_id($jadwal->id_mahasiswa_skripsi);
        if (!$jadwal || !$skripsi || $skripsi->id_mahasiswa != $id_mahasiswa) {
            $this->session->set_flashdata('error', 'Jadwal sidang Skripsi tidak ditemukan.');
            redirect('Skripsi_catatan_revisi');
        }

        // Ambil penilaian dari penguji untuk jadwal ini
        $nilai_sidang_list = $this->Skripsi_nilai_sidang_model->get_by_jadwal($jadwal->id);

        $data = array(
            'title' => 'Detail Catatan Revisi Skripsi',
            'page_title' => 'Detail Catatan Revisi Skripsi',
            'breadcrumb' => array(
                array('label' => 'Skripsi', 'url' => '#'),
                array('label' => 'Catatan Revisi', 'url' => site_url('Skripsi_catatan_revisi')),
                array('label' => 'Detail', 'active' => true)
            ),
            'sidebar_menu' => generate_sidebar_menu('mahasiswa'),
            'jadwal' => $jadwal,
            'nilai_sidang_list' => $nilai_sidang_list,
            'skripsi' => $skripsi,
            'tahun_aktif' => $tahun_aktif
        );

        $data['content'] = $this->load->view('mahasiswa/skripsi_catatan_revisi/detail', $data, true);
        $this->load->view('layouts/main', $data);
    }
}





