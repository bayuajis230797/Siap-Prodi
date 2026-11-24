<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Reset pengajuan judul PKL oleh Sekprodi
 * Hanya untuk judul yang belum disetujui (pending / revisi), bukan yang sudah acc.
 */
class Pkl_reset_pengajuan_sekprodi extends MY_Controller {

    public function __construct() {
        parent::__construct();
        $this->check_role(array('admin', 'sekprodi', 'kaprodi'));
        $this->load->model('Pkl_pengajuan_model');
        $this->load->model('Tahun_akademik_model');
    }

    /**
     * Daftar pengajuan judul yang dapat di-reset
     */
    public function index() {
        $tahun_aktif = $this->Tahun_akademik_model->get_aktif();
        if (!$tahun_aktif) {
            $this->session->set_flashdata('error', 'Tidak ada tahun akademik aktif.');
            redirect('Dashboard');
        }

        $pengajuan_list = $this->Pkl_pengajuan_model->get_pengajuan_judul_resettable($tahun_aktif->id);

        $data = array(
            'title' => 'Reset Pengajuan Judul PKL',
            'page_title' => 'Reset Pengajuan Judul PKL',
            'breadcrumb' => array(
                array('label' => 'PKL', 'url' => '#'),
                array('label' => 'Reset Pengajuan Judul', 'active' => true)
            ),
            'sidebar_menu' => generate_sidebar_menu(get_user_role()),
            'pengajuan_list' => $pengajuan_list,
            'tahun_aktif' => $tahun_aktif
        );

        $data['content'] = $this->load->view('sekprodi/pkl_reset_pengajuan_index', $data, true);
        $this->load->view('layouts/main', $data);
    }

    /**
     * Reset satu pengajuan judul
     */
    public function reset($id) {
        $pengajuan = $this->Pkl_pengajuan_model->get_by_id($id);
        if (!$pengajuan) {
            $this->session->set_flashdata('error', 'Data pengajuan tidak ditemukan.');
            redirect('Pkl_reset_pengajuan_sekprodi');
        }

        if ($pengajuan->status_acc_judul === 'acc') {
            $this->session->set_flashdata('error', 'Pengajuan yang sudah disetujui pembimbing tidak dapat di-reset.');
            redirect('Pkl_reset_pengajuan_sekprodi');
        }

        if ($pengajuan->judul_pkl === Pkl_pengajuan_model::PLACEHOLDER_JUDUL) {
            $this->session->set_flashdata('error', 'Mahasiswa ini belum mengajukan judul.');
            redirect('Pkl_reset_pengajuan_sekprodi');
        }

        if ($this->Pkl_pengajuan_model->reset_pengajuan_judul($id)) {
            $this->session->set_flashdata('success', 'Pengajuan judul PKL berhasil di-reset. Mahasiswa dapat mengajukan judul ulang.');
        } else {
            $this->session->set_flashdata('error', 'Gagal mereset pengajuan judul PKL.');
        }

        redirect('Pkl_reset_pengajuan_sekprodi');
    }
}
