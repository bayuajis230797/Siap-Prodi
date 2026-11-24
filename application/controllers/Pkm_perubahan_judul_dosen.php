<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Pkm_perubahan_judul_dosen extends MY_Controller {

    public function __construct() {
        parent::__construct();
        $this->check_role('dosen');
        $this->load->model('Pkm_perubahan_judul_model');
        $this->load->model('Pkm_pengajuan_model');
        $this->load->helper(array('menu', 'google_drive', 'pkm'));
    }

    public function index() {
        $id_dosen = (int) $this->session->userdata('ref_id');
        $list = $this->Pkm_perubahan_judul_model->get_pending_by_pembimbing($id_dosen);
        $data = array(
            'title' => 'Perubahan Judul PKM',
            'page_title' => 'Verifikasi Perubahan Judul PKM',
            'breadcrumb' => array(array('label' => 'Perubahan Judul PKM', 'active' => true)),
            'sidebar_menu' => generate_sidebar_menu('dosen'),
            'list' => $list,
        );
        $data['content'] = $this->load->view('dosen/pkm/perubahan_index', $data, true);
        $this->load->view('layouts/main', $data);
    }

    public function proses($id) {
        if ($this->input->method() !== 'post') {
            show_404();
        }
        $id_dosen = (int) $this->session->userdata('ref_id');
        $row = $this->Pkm_perubahan_judul_model->get_by_id($id);
        if (!$row || (int) $row->id_pembimbing_final !== $id_dosen || $row->status_acc_dosen !== 'pending') {
            show_404();
        }
        $action = $this->input->post('action');
        $catatan = trim($this->input->post('catatan_dosen'));

        if ($action === 'acc') {
            $this->Pkm_perubahan_judul_model->update($id, array(
                'status_acc_dosen' => 'acc',
                'catatan_dosen' => $catatan ?: null,
                'reviewed_by' => $this->session->userdata('user_id'),
                'reviewed_at' => date('Y-m-d H:i:s'),
            ));
            $this->Pkm_pengajuan_model->update($row->id_pkm_pengajuan, array(
                'judul_pkm' => $row->judul_baru,
                'judul_disetujui' => $row->judul_baru,
                'status_acc_judul' => 'acc',
                'catatan_pembimbing' => $catatan ?: null,
            ));
            $pengajuan = $this->Pkm_pengajuan_model->get_by_id($row->id_pkm_pengajuan);
            $this->session->set_flashdata('success', 'Perubahan judul disetujui. Mahasiswa dapat melanjutkan bimbingan PKM.');
        } else {
            $this->Pkm_perubahan_judul_model->update($id, array(
                'status_acc_dosen' => 'ditolak',
                'catatan_dosen' => $catatan ?: null,
                'reviewed_by' => $this->session->userdata('user_id'),
                'reviewed_at' => date('Y-m-d H:i:s'),
            ));
            $this->session->set_flashdata('success', 'Perubahan judul ditolak.');
        }
        redirect('Pkm_perubahan_judul_dosen');
    }
}
