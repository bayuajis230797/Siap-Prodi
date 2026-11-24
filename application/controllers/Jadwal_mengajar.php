<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Jadwal Mengajar Controller
 * Menampilkan jadwal mengajar dosen
 */
class Jadwal_mengajar extends MY_Controller {

    public function __construct() {
        parent::__construct();
        $this->check_role('dosen');
        $this->load->model('Jadwal_kuliah_model');
        $this->load->model('Tahun_akademik_model');
    }

    public function index() {
        $id_dosen = $this->session->userdata('ref_id');
        
        if (!$id_dosen) {
            $this->session->set_flashdata('error', 'Data dosen tidak ditemukan.');
            redirect('Dashboard');
        }

        // Ambil tahun akademik aktif
        $tahun_aktif = $this->Tahun_akademik_model->get_aktif();
        $id_tahun_akademik = $tahun_aktif ? $tahun_aktif->id : null;
        
        // Filter hari (jika ada)
        $hari_filter = $this->input->get('hari');
        
        // Ambil jadwal mengajar dosen
        $jadwal_list = $this->Jadwal_kuliah_model->get_by_dosen($id_dosen, $id_tahun_akademik, $hari_filter);
        
        // Group jadwal by hari untuk tampilan yang lebih rapi
        $jadwal_by_hari = array();
        $hari_list = array('Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu', 'Minggu');
        
        foreach ($hari_list as $hari) {
            $jadwal_by_hari[$hari] = array();
        }
        
        foreach ($jadwal_list as $jadwal) {
            if (isset($jadwal_by_hari[$jadwal->hari])) {
                $jadwal_by_hari[$jadwal->hari][] = $jadwal;
            }
        }
        
        $data['jadwal_list'] = $jadwal_list;
        $data['jadwal_by_hari'] = $jadwal_by_hari;
        $data['hari_list'] = $hari_list;
        $data['hari_filter'] = $hari_filter;
        $data['tahun_aktif'] = $tahun_aktif;
        $data['title'] = 'Jadwal Mengajar';
        $data['page_title'] = 'Jadwal Mengajar';
        $data['breadcrumb'] = array(array('label' => 'Jadwal Mengajar', 'active' => true));
        $data['sidebar_menu'] = generate_sidebar_menu('dosen');
        $data['content'] = $this->load->view('dosen/jadwal_mengajar', $data, true);
        $this->load->view('layouts/main', $data);
    }
}






