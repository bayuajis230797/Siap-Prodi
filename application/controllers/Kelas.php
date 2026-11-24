<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Kelas Controller
 * Mengelola data kelas dan mata kuliah per kelas
 */
class Kelas extends MY_Controller {

    public function __construct() {
        parent::__construct();
        $this->check_role('admin');
        $this->load->model('Kelas_model');
        $this->load->model('Tahun_akademik_model');
    }

    /**
     * List semua kelas
     */
    public function index() {
        $data['kelas_list'] = $this->Kelas_model->get_all_kelas();
        $data['statistik'] = $this->Kelas_model->get_statistik_kelas();
        $data['tahun_aktif'] = $this->Tahun_akademik_model->get_aktif();
        
        $data['title'] = 'Kelas';
        $data['page_title'] = 'Master Data Kelas';
        $data['breadcrumb'] = array(
            array('label' => 'Kelas', 'active' => true)
        );
        $data['sidebar_menu'] = generate_sidebar_menu('admin');
        $data['content'] = $this->load->view('kelas/index', $data, true);
        $this->load->view('layouts/main', $data);
    }

    /**
     * Detail kelas: mahasiswa dan mata kuliah
     */
    public function detail($kelas) {
        // Decode kelas jika di-encode
        $kelas = urldecode($kelas);
        
        // Cek apakah kelas ada
        $kelas_list = $this->Kelas_model->get_all_kelas();
        $kelas_exists = false;
        foreach ($kelas_list as $k) {
            if ($k->kelas == $kelas) {
                $kelas_exists = true;
                break;
            }
        }
        
        if (!$kelas_exists) {
            $this->session->set_flashdata('error', 'Kelas tidak ditemukan.');
            redirect('Kelas');
        }

        // Ambil data kelas
        $data['kelas'] = $kelas;
        $data['mahasiswa'] = $this->Kelas_model->get_detail_kelas($kelas);
        $data['kurikulum'] = $this->Kelas_model->get_kurikulum_by_kelas($kelas);
        $data['tahun_aktif'] = $this->Tahun_akademik_model->get_aktif();
        
        // Ambil mata kuliah untuk kelas tersebut
        $id_tahun_akademik = $data['tahun_aktif'] ? $data['tahun_aktif']->id : null;
        $data['mata_kuliah'] = $this->Kelas_model->get_mata_kuliah_by_kelas($kelas, $id_tahun_akademik);
        
        // Statistik
        $data['jumlah_mahasiswa'] = count($data['mahasiswa']);
        $data['jumlah_mata_kuliah'] = count($data['mata_kuliah']);
        
        // Group mata kuliah by kurikulum
        $data['mata_kuliah_by_kurikulum'] = array();
        foreach ($data['mata_kuliah'] as $mk) {
            $key = $mk->nama_kurikulum . ' (Tahun ' . $mk->tahun_berlaku . ')';
            if (!isset($data['mata_kuliah_by_kurikulum'][$key])) {
                $data['mata_kuliah_by_kurikulum'][$key] = array();
            }
            $data['mata_kuliah_by_kurikulum'][$key][] = $mk;
        }
        
        $data['title'] = 'Detail Kelas';
        $data['page_title'] = 'Detail Kelas ' . $kelas;
        $data['breadcrumb'] = array(
            array('label' => 'Kelas', 'url' => site_url('Kelas')),
            array('label' => 'Detail ' . $kelas, 'active' => true)
        );
        $data['sidebar_menu'] = generate_sidebar_menu('admin');
        $data['content'] = $this->load->view('kelas/detail', $data, true);
        $this->load->view('layouts/main', $data);
    }
}














