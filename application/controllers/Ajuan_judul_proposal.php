<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Ajuan_judul_proposal Controller (Dosen)
 * Pembimbing mereview judul Proposal TA: ACC atau minta revisi.
 * Satu-satunya jalur review judul (tidak ada duplikat seperti pada Skripsi).
 */
class Ajuan_judul_proposal extends MY_Controller {

    public function __construct() {
        parent::__construct();
        $this->check_role('dosen');
        $this->load->model('Proposal_pengajuan_model');
        $this->load->model('Proposal_model');
        $this->load->model('Tahun_akademik_model');
        $this->load->library('pagination');
        $this->load->library('form_validation');
        $this->load->helper('proposal');
    }

    public function index() {
        $id_dosen = $this->session->userdata('ref_id');
        $tahun_aktif = $this->Tahun_akademik_model->get_aktif();
        if (!$id_dosen || !$tahun_aktif) {
            $this->session->set_flashdata('error', !$id_dosen ? 'Data dosen tidak ditemukan.' : 'Tidak ada tahun akademik aktif.');
            redirect('Dashboard');
        }

        $filter_status = $this->input->get('status');
        $per_page = (int) $this->input->get('per_page');
        $per_page = in_array($per_page, array(10, 25, 50, 100), true) ? $per_page : 10;

        // Hanya pengajuan yang sudah disetujui sekprodi dan memilih dosen ini sebagai pembimbing
        $semua = $this->Proposal_pengajuan_model->get_by_pembimbing_all($id_dosen, $tahun_aktif->id);
        $list = array();
        foreach ($semua as $p) {
            if ($p->status !== 'disetujui') {
                continue;
            }
            $p->status_acc = $p->status_acc_judul ?? 'pending';
            if ($filter_status && $p->status_acc !== $filter_status) {
                continue;
            }
            $list[] = $p;
        }

        $total_rows = count($list);
        $this->pagination->initialize(proposal_pagination_config(
            site_url('Ajuan_judul_proposal/index'), $total_rows, $per_page,
            array('per_page' => $per_page != 10 ? $per_page : null, 'status' => $filter_status)
        ));
        $page = max(1, (int) $this->uri->segment(3));

        $data = array(
            'title' => 'Ajuan Judul Proposal TA',
            'page_title' => 'Ajuan Judul Proposal TA',
            'breadcrumb' => array(
                array('label' => 'Proposal TA', 'url' => '#'),
                array('label' => 'Ajuan Judul Mahasiswa', 'active' => true)
            ),
            'sidebar_menu' => generate_sidebar_menu('dosen'),
            'pengajuan_list' => array_slice($list, ($page - 1) * $per_page, $per_page),
            'tahun_aktif' => $tahun_aktif,
            'filter_status' => $filter_status,
            'pagination' => $this->pagination->create_links(),
            'per_page' => $per_page,
            'total_rows' => $total_rows
        );

        $data['content'] = $this->load->view('dosen/ajuan_judul_proposal/index', $data, true);
        $this->load->view('layouts/main', $data);
    }

    public function detail($id) {
        $pengajuan = $this->ambil_pengajuan_milik_dosen($id);

        $data = array(
            'title' => 'Detail Ajuan Judul Proposal TA',
            'page_title' => 'Detail Ajuan Judul Proposal TA',
            'breadcrumb' => array(
                array('label' => 'Ajuan Judul Mahasiswa', 'url' => site_url('Ajuan_judul_proposal')),
                array('label' => 'Detail', 'active' => true)
            ),
            'sidebar_menu' => generate_sidebar_menu('dosen'),
            'pengajuan' => $pengajuan,
            'status_acc' => $pengajuan->status_acc_judul ?? 'pending'
        );

        $data['content'] = $this->load->view('dosen/ajuan_judul_proposal/detail', $data, true);
        $this->load->view('layouts/main', $data);
    }

    /**
     * ACC judul.
     */
    public function acc($id) {
        $pengajuan = $this->ambil_pengajuan_milik_dosen($id);
        if ($this->input->method() !== 'post') {
            redirect('Ajuan_judul_proposal/detail/' . $id);
        }

        $this->Proposal_pengajuan_model->update($id, array(
            'status_acc_judul' => 'acc',
            'catatan_revisi' => null
        ));

        proposal_notify('mahasiswa', $pengajuan->id_mahasiswa, 'Judul Proposal TA di-ACC',
            'Pembimbing menyetujui judul Anda. Bimbingan Proposal TA sudah dapat dimulai.', site_url('Proposal_pengajuan'));

        $this->session->set_flashdata('success', 'Judul proposal telah disetujui. Mahasiswa dapat memulai bimbingan.');
        redirect('Ajuan_judul_proposal');
    }

    /**
     * Minta revisi judul (catatan wajib).
     */
    public function revisi($id) {
        $pengajuan = $this->ambil_pengajuan_milik_dosen($id);
        if ($this->input->method() !== 'post') {
            redirect('Ajuan_judul_proposal/detail/' . $id);
        }

        $this->form_validation->set_rules('catatan_revisi', 'Catatan Revisi', 'required|trim');
        if ($this->form_validation->run() == FALSE) {
            $this->session->set_flashdata('error', validation_errors());
            redirect('Ajuan_judul_proposal/detail/' . $id);
        }

        $this->Proposal_pengajuan_model->update($id, array(
            'status_acc_judul' => 'revisi',
            'catatan_revisi' => $this->input->post('catatan_revisi')
        ));

        proposal_notify('mahasiswa', $pengajuan->id_mahasiswa, 'Revisi Judul Proposal TA',
            'Pembimbing meminta revisi judul. Ajukan perbaikan lewat menu Perubahan Judul.', site_url('Proposal_perubahan_judul'));

        $this->session->set_flashdata('success', 'Catatan revisi telah dikirim ke mahasiswa.');
        redirect('Ajuan_judul_proposal');
    }

    /**
     * Ambil pengajuan dan pastikan: ada, sudah disetujui sekprodi, dan dosen ini pembimbingnya.
     */
    /**
     * Pembimbing mengoreksi judul mahasiswa bimbingannya (mis. salah ketik atau penajaman judul).
     * Hanya POST, hanya pembimbing pengajuan ini, dan tidak setelah Proposal TA selesai.
     */
    public function edit_judul($id) {
        $pengajuan = $this->ambil_pengajuan_milik_dosen($id);
        $url = 'Ajuan_judul_proposal/detail/' . $id;
        if ($this->input->method() !== 'post') {
            redirect($url);
        }

        $proposal = $this->Proposal_model->get_by_mahasiswa($pengajuan->id_mahasiswa, $pengajuan->id_tahun_akademik);
        if ($proposal && $proposal->status === 'selesai_proposal') {
            $this->session->set_flashdata('error', 'Proposal TA mahasiswa sudah selesai, judul tidak dapat diubah lagi.');
            redirect($url);
        }

        $this->form_validation->set_rules('judul_disetujui', 'Judul Baru', 'required|trim|min_length[10]|max_length[255]');
        if ($this->form_validation->run() == FALSE) {
            $this->session->set_flashdata('error', validation_errors());
            redirect($url);
        }

        $judul = $this->input->post('judul_disetujui');
        $this->Proposal_pengajuan_model->update($id, array('judul_disetujui' => $judul));
        if ($proposal) {
            $this->Proposal_model->update($proposal->id, array('judul_proposal' => $judul));
        }

        proposal_notify('mahasiswa', $pengajuan->id_mahasiswa, 'Judul Proposal TA Diperbarui',
            'Pembimbing memperbarui judul Proposal TA Anda.', site_url('Proposal_pengajuan'));

        $this->session->set_flashdata('success', 'Judul berhasil diperbarui.');
        redirect($url);
    }

    private function ambil_pengajuan_milik_dosen($id) {
        $id_dosen = $this->session->userdata('ref_id');
        $pengajuan = $this->Proposal_pengajuan_model->get_by_id($id);

        if (!$pengajuan || $pengajuan->status !== 'disetujui') {
            $this->session->set_flashdata('error', 'Data pengajuan tidak ditemukan.');
            redirect('Ajuan_judul_proposal');
        }
        if (!$id_dosen || (int) $pengajuan->id_pembimbing_final !== (int) $id_dosen) {
            $this->session->set_flashdata('error', 'Anda bukan pembimbing untuk pengajuan ini.');
            redirect('Ajuan_judul_proposal');
        }
        return $pengajuan;
    }
}
