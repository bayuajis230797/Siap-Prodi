<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Proposal_catatan_revisi Controller (Mahasiswa)
 * Jadwal sidang beserta penguji dan catatan revisi dari kedua penguji.
 * Nilai tidak ditampilkan sebelum klaim nilai mahasiswa disetujui sekprodi.
 */
class Proposal_catatan_revisi extends MY_Controller {

    public function __construct() {
        parent::__construct();
        $this->check_role('mahasiswa');
        $this->load->model('Proposal_model');
        $this->load->model('Jadwal_proposal_model');
        $this->load->model('Proposal_nilai_sidang_model');
        $this->load->model('Proposal_nilai_rekap_model');
        $this->load->model('Tahun_akademik_model');
        $this->load->helper('proposal');
    }

    public function index() {
        $tahun_aktif = $this->Tahun_akademik_model->get_aktif();
        if (!$tahun_aktif) {
            $this->session->set_flashdata('error', 'Tidak ada tahun akademik aktif.');
            redirect('Dashboard');
        }
        $proposal = $this->Proposal_model->get_by_mahasiswa($this->session->userdata('ref_id'), $tahun_aktif->id);
        if (!$proposal) {
            $this->session->set_flashdata('error', 'Anda belum terdaftar untuk Proposal TA.');
            redirect('Dashboard');
        }

        $this->load->model('Proposal_klaim_nilai_model');
        $klaim_row = $this->Proposal_klaim_nilai_model->get_by_mahasiswa_proposal($proposal->id);
        $sudah_klaim = ($klaim_row && $klaim_row->status === 'disetujui');
        $jadwal_list = $this->Jadwal_proposal_model->get_by_mahasiswa($proposal->id);
        $blok = array();
        foreach ($jadwal_list as $j) {
            $blok[$j->id] = array(
                'nilai_list' => $this->Proposal_nilai_sidang_model->get_by_jadwal($j->id),
                'rekap' => $this->Proposal_nilai_rekap_model->get_by_jadwal($j->id)
            );
        }

        $data = array(
            'title' => 'Jadwal Sidang dan Catatan Revisi Proposal TA',
            'page_title' => 'Jadwal Sidang dan Catatan Revisi',
            'breadcrumb' => array(
                array('label' => 'Proposal TA', 'url' => '#'),
                array('label' => 'Jadwal Sidang dan Revisi', 'active' => true)
            ),
            'sidebar_menu' => generate_sidebar_menu('mahasiswa'),
            'proposal' => $proposal,
            'jadwal_list' => $jadwal_list,
            'blok' => $blok,
            'sudah_klaim' => $sudah_klaim,
            'tahun_aktif' => $tahun_aktif
        );
        $data['content'] = $this->load->view('mahasiswa/proposal/catatan_revisi', $data, true);
        $this->load->view('layouts/main', $data);
    }
}
