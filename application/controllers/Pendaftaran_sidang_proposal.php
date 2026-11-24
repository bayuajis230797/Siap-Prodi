<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Pendaftaran_sidang_proposal Controller (Mahasiswa)
 * Pendaftaran sidang (satu tahap) setelah pembimbing menyatakan layak sidang.
 * Sekprodi lalu menetapkan jadwal dan Penguji 1 dan 2 sekaligus.
 */
class Pendaftaran_sidang_proposal extends MY_Controller {

    public function __construct() {
        parent::__construct();
        $this->check_role('mahasiswa');
        $this->load->model('Proposal_model');
        $this->load->model('Proposal_pendaftaran_sidang_model');
        $this->load->model('Proposal_berkas_upload_model');
        $this->load->model('Jadwal_proposal_model');
        $this->load->model('Tahun_akademik_model');
        $this->load->library('Proposal_pendaftaran');
        $this->load->helper('proposal');
    }

    private function ambil_proposal() {
        $tahun_aktif = $this->Tahun_akademik_model->get_aktif();
        if (!$tahun_aktif) {
            $this->session->set_flashdata('error', 'Tidak ada tahun akademik aktif.');
            redirect('Halaman_utama');
        }
        $proposal = $this->Proposal_model->get_by_mahasiswa($this->session->userdata('ref_id'), $tahun_aktif->id);
        if (!$proposal) {
            $this->session->set_flashdata('error', 'Anda belum terdaftar sebagai peserta Proposal TA.');
            redirect('Halaman_utama');
        }
        return array($proposal, $tahun_aktif);
    }

    public function index() {
        list($proposal, $tahun_aktif) = $this->ambil_proposal();
        $pendaftaran = $this->Proposal_pendaftaran_sidang_model->get_by_mahasiswa_proposal($proposal->id);

        $data = array(
            'title' => 'Pendaftaran Sidang Proposal TA',
            'page_title' => 'Pendaftaran Sidang Proposal TA',
            'breadcrumb' => array(
                array('label' => 'Proposal TA', 'url' => '#'),
                array('label' => 'Pendaftaran Sidang', 'active' => true)
            ),
            'sidebar_menu' => generate_sidebar_menu('mahasiswa'),
            'form_action' => 'Pendaftaran_sidang_proposal/submit',
            'keterangan' => 'Setelah dinyatakan layak sidang oleh pembimbing, kirim berkas sidang untuk diverifikasi sekprodi. Setelah disetujui, jadwal sidang serta Penguji 1 dan Penguji 2 ditetapkan.',
            'proposal' => $proposal,
            'tahun_aktif' => $tahun_aktif,
            'judul' => get_judul_proposal_terbaru($proposal->id_mahasiswa, $tahun_aktif->id),
            'syarat_pesan' => $this->proposal_pendaftaran->syarat($proposal),
            'pendaftaran' => $pendaftaran,
            'berkas_list' => $this->proposal_pendaftaran->berkas_list(),
            'berkas_uploaded' => $pendaftaran ? $this->Proposal_berkas_upload_model->get_by_pendaftaran($pendaftaran->id) : array(),
            'jadwal_list' => $this->Jadwal_proposal_model->get_by_mahasiswa($proposal->id)
        );
        $data['content'] = $this->load->view('mahasiswa/proposal/pendaftaran_form', $data, true);
        $this->load->view('layouts/main', $data);
    }

    public function submit() {
        list($proposal) = $this->ambil_proposal();
        if ($this->input->method() !== 'post') {
            redirect('Pendaftaran_sidang_proposal');
        }

        $hasil = $this->proposal_pendaftaran->simpan($proposal, $_POST);
        $this->session->set_flashdata($hasil['ok'] ? 'success' : 'error', $hasil['message']);
        redirect('Pendaftaran_sidang_proposal');
    }
}
