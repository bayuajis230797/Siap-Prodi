<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Ajuan_judul_pkm extends MY_Controller {

    public function __construct() {
        parent::__construct();
        $this->check_role('dosen');
        $this->load->model('Pkm_pengajuan_model');
        $this->load->model('Pkm_anggota_model');
        $this->load->helper(array('menu', 'pkm', 'google_drive'));
        $this->load->library('form_validation');
    }

    public function index() {
        $id_dosen = (int) $this->session->userdata('ref_id');
        $filter_status = $this->input->get('status');
        $all = $this->Pkm_pengajuan_model->get_by_pembimbing($id_dosen, 'disetujui');
        $list = array();
        foreach ($all as $p) {
            if ($filter_status && $p->status_acc_judul !== $filter_status) {
                continue;
            }
            $list[] = $p;
        }
        $data = array(
            'title' => 'Ajuan Judul PKM',
            'page_title' => 'Ajuan Judul PKM',
            'breadcrumb' => array(array('label' => 'Ajuan Judul PKM', 'active' => true)),
            'sidebar_menu' => generate_sidebar_menu('dosen'),
            'pengajuan_list' => $list,
            'filter_status' => $filter_status,
        );
        $data['content'] = $this->load->view('dosen/pkm/ajuan_index', $data, true);
        $this->load->view('layouts/main', $data);
    }

    public function detail($id) {
        $id_dosen = (int) $this->session->userdata('ref_id');
        $pengajuan = $this->Pkm_pengajuan_model->get_by_id($id);
        if (!$pengajuan || (int) $pengajuan->id_pembimbing_final !== $id_dosen) {
            show_404();
        }
        $anggota = $this->Pkm_anggota_model->get_by_pengajuan($id);
        $data = array(
            'title' => 'Detail Ajuan PKM',
            'page_title' => 'Detail Ajuan Judul PKM',
            'breadcrumb' => array(
                array('label' => 'Ajuan Judul PKM', 'url' => site_url('Ajuan_judul_pkm')),
                array('label' => 'Detail', 'active' => true),
            ),
            'sidebar_menu' => generate_sidebar_menu('dosen'),
            'pengajuan' => $pengajuan,
            'anggota' => $anggota,
        );
        $data['content'] = $this->load->view('dosen/pkm/ajuan_detail', $data, true);
        $this->load->view('layouts/main', $data);
    }

    public function proses($id) {
        if ($this->input->method() !== 'post') {
            show_404();
        }
        $id_dosen = (int) $this->session->userdata('ref_id');
        $pengajuan = $this->Pkm_pengajuan_model->get_by_id($id);
        if (!$pengajuan || (int) $pengajuan->id_pembimbing_final !== $id_dosen) {
            show_404();
        }
        $action = $this->input->post('action');
        $catatan = trim($this->input->post('catatan_pembimbing'));

        if ($action === 'acc') {
            $this->Pkm_pengajuan_model->update($id, array(
                'status_acc_judul' => 'acc',
                'judul_disetujui' => $pengajuan->judul_pkm,
                'catatan_pembimbing' => $catatan ?: null,
            ));
            $this->session->set_flashdata('success', 'Judul PKM disetujui. Mahasiswa dapat memulai bimbingan per topik.');
        } elseif ($action === 'revisi') {
            if ($catatan === '') {
                $this->session->set_flashdata('error', 'Catatan revisi wajib diisi.');
                redirect('Ajuan_judul_pkm/detail/' . $id);
            }
            $this->Pkm_pengajuan_model->update($id, array(
                'status_acc_judul' => 'revisi',
                'catatan_pembimbing' => $catatan,
            ));
            $this->session->set_flashdata('success', 'Permintaan revisi judul dikirim ke mahasiswa.');
        }
        redirect('Ajuan_judul_pkm/detail/' . $id);
    }
}
