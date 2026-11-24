<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Skripsi_review_judul Controller
 * Menangani review judul skripsi oleh pembimbing 1 dan 2
 */
class Skripsi_review_judul extends MY_Controller {

    public function __construct() {
        parent::__construct();
        $this->check_role('dosen');
        $this->load->model('Skripsi_pengajuan_model');
        $this->load->model('Skripsi_model');
        $this->load->model('Tahun_akademik_model');
        $this->load->library('form_validation');
    }

    /**
     * Daftar pengajuan yang perlu direview oleh dosen
     */
    public function index() {
        $id_dosen = $this->session->userdata('ref_id');
        $dosen = $this->db->get_where('dosen', array('id' => $id_dosen))->row();
        
        if (!$dosen) {
            $this->session->set_flashdata('error', 'Data dosen tidak ditemukan.');
            redirect('Dashboard');
        }

        $tahun_aktif = $this->Tahun_akademik_model->get_aktif();
        if (!$tahun_aktif) {
            $this->session->set_flashdata('error', 'Tidak ada tahun akademik aktif.');
            redirect('Dashboard');
        }

        // Ambil pengajuan yang perlu direview oleh dosen ini
        $pengajuan_list = $this->Skripsi_pengajuan_model->get_by_pembimbing_pending($dosen->id, $tahun_aktif->id);

        $data = array(
            'title' => 'Review Judul Skripsi',
            'page_title' => 'Review Judul Skripsi',
            'breadcrumb' => array(
                array('label' => 'Skripsi', 'url' => site_url('Skripsi_review_judul')),
                array('label' => 'Review Judul', 'active' => true)
            ),
            'sidebar_menu' => generate_sidebar_menu('dosen'),
            'pengajuan_list' => $pengajuan_list,
            'tahun_aktif' => $tahun_aktif,
            'dosen_id' => $dosen->id
        );

        $data['content'] = $this->load->view('skripsi_review_judul/index', $data, true);
        $this->load->view('layouts/main', $data);
    }

    /**
     * Detail pengajuan untuk review
     */
    public function detail($id) {
        $id_dosen = $this->session->userdata('ref_id');
        $dosen = $this->db->get_where('dosen', array('id' => $id_dosen))->row();
        
        if (!$dosen) {
            $this->session->set_flashdata('error', 'Data dosen tidak ditemukan.');
            redirect('Dashboard');
        }

        $pengajuan = $this->Skripsi_pengajuan_model->get_by_id($id);
        if (!$pengajuan) {
            $this->session->set_flashdata('error', 'Data pengajuan tidak ditemukan.');
            redirect('Skripsi_review_judul');
        }

        // Cek apakah dosen ini adalah pembimbing 1 atau 2
        $is_pembimbing_1 = ($pengajuan->id_pembimbing_1_final == $dosen->id);
        $is_pembimbing_2 = ($pengajuan->id_pembimbing_2_final == $dosen->id);

        if (!$is_pembimbing_1 && !$is_pembimbing_2) {
            $this->session->set_flashdata('error', 'Anda bukan pembimbing untuk pengajuan ini.');
            redirect('Skripsi_review_judul');
        }

        // Tentukan tipe pembimbing
        $tipe_pembimbing = $is_pembimbing_1 ? 1 : 2;
        $status_field = $is_pembimbing_1 ? 'status_acc_judul_p1' : 'status_acc_judul_p2';
        $catatan_field = $is_pembimbing_1 ? 'catatan_revisi_p1' : 'catatan_revisi_p2';

        $data = array(
            'title' => 'Review Judul Skripsi',
            'page_title' => 'Review Judul Skripsi',
            'breadcrumb' => array(
                array('label' => 'Review Judul', 'url' => site_url('Skripsi_review_judul')),
                array('label' => 'Detail', 'active' => true)
            ),
            'sidebar_menu' => generate_sidebar_menu('dosen'),
            'pengajuan' => $pengajuan,
            'tipe_pembimbing' => $tipe_pembimbing,
            'status_field' => $status_field,
            'catatan_field' => $catatan_field
        );

        $data['content'] = $this->load->view('skripsi_review_judul/detail', $data, true);
        $this->load->view('layouts/main', $data);
    }

    /**
     * ACC judul oleh pembimbing
     */
    public function acc($id) {
        $id_dosen = $this->session->userdata('ref_id');
        $dosen = $this->db->get_where('dosen', array('id' => $id_dosen))->row();
        
        if (!$dosen) {
            $this->session->set_flashdata('error', 'Data dosen tidak ditemukan.');
            redirect('Dashboard');
        }

        $pengajuan = $this->Skripsi_pengajuan_model->get_by_id($id);
        if (!$pengajuan) {
            $this->session->set_flashdata('error', 'Data pengajuan tidak ditemukan.');
            redirect('Skripsi_review_judul');
        }

        // Cek apakah dosen ini adalah pembimbing 1 atau 2
        $is_pembimbing_1 = ($pengajuan->id_pembimbing_1_final == $dosen->id);
        $is_pembimbing_2 = ($pengajuan->id_pembimbing_2_final == $dosen->id);

        if (!$is_pembimbing_1 && !$is_pembimbing_2) {
            $this->session->set_flashdata('error', 'Anda bukan pembimbing untuk pengajuan ini.');
            redirect('Skripsi_review_judul');
        }

        // Update status ACC
        $update_data = array();
        if ($is_pembimbing_1) {
            $update_data['status_acc_judul_p1'] = 'acc';
        } else {
            $update_data['status_acc_judul_p2'] = 'acc';
        }

        if ($this->Skripsi_pengajuan_model->update($id, $update_data)) {
            // Update status pengajuan jika kedua pembimbing sudah ACC
            $this->_check_and_update_status_pengajuan($id);
            
            $this->session->set_flashdata('success', 'Judul skripsi telah disetujui.');
        } else {
            $this->session->set_flashdata('error', 'Gagal menyetujui judul skripsi.');
        }

        redirect('Skripsi_review_judul');
    }

    /**
     * Revisi judul oleh pembimbing
     */
    public function revisi($id) {
        $id_dosen = $this->session->userdata('ref_id');
        $dosen = $this->db->get_where('dosen', array('id' => $id_dosen))->row();
        
        if (!$dosen) {
            $this->session->set_flashdata('error', 'Data dosen tidak ditemukan.');
            redirect('Dashboard');
        }

        if ($this->input->method() === 'post') {
            $this->form_validation->set_rules('catatan_revisi', 'Catatan Revisi', 'required|trim');

            if ($this->form_validation->run() == FALSE) {
                redirect('Skripsi_review_judul/detail/' . $id);
            }

            $pengajuan = $this->Skripsi_pengajuan_model->get_by_id($id);
            if (!$pengajuan) {
                $this->session->set_flashdata('error', 'Data pengajuan tidak ditemukan.');
                redirect('Skripsi_review_judul');
            }

            // Cek apakah dosen ini adalah pembimbing 1 atau 2
            $is_pembimbing_1 = ($pengajuan->id_pembimbing_1_final == $dosen->id);
            $is_pembimbing_2 = ($pengajuan->id_pembimbing_2_final == $dosen->id);

            if (!$is_pembimbing_1 && !$is_pembimbing_2) {
                $this->session->set_flashdata('error', 'Anda bukan pembimbing untuk pengajuan ini.');
                redirect('Skripsi_review_judul');
            }

            // Update status revisi
            $update_data = array();
            if ($is_pembimbing_1) {
                $update_data['status_acc_judul_p1'] = 'revisi';
                $update_data['catatan_revisi_p1'] = $this->input->post('catatan_revisi');
            } else {
                $update_data['status_acc_judul_p2'] = 'revisi';
                $update_data['catatan_revisi_p2'] = $this->input->post('catatan_revisi');
            }

            if ($this->Skripsi_pengajuan_model->update($id, $update_data)) {
                $this->session->set_flashdata('success', 'Catatan revisi telah dikirim.');
            } else {
                $this->session->set_flashdata('error', 'Gagal mengirim catatan revisi.');
            }
        }

        redirect('Skripsi_review_judul');
    }

    /**
     * Helper method untuk cek dan update status pengajuan jika kedua pembimbing sudah ACC
     */
    private function _check_and_update_status_pengajuan($id) {
        // Ambil data pengajuan terbaru
        $pengajuan = $this->Skripsi_pengajuan_model->get_by_id($id);
        
        if (!$pengajuan) {
            return false;
        }
        
        // Cek apakah ada pembimbing 1 dan pembimbing 2 (prioritas dari id_pembimbing_1_final/id_pembimbing_2_final)
        $has_pembimbing_1 = !empty($pengajuan->id_pembimbing_1_final);
        $has_pembimbing_2 = !empty($pengajuan->id_pembimbing_2_final);
        
        // Jika tidak ada pembimbing final, cek dari mahasiswa_skripsi
        if (!$has_pembimbing_1 && !empty($pengajuan->id_pembimbing_1)) {
            $has_pembimbing_1 = true;
        }
        if (!$has_pembimbing_2 && !empty($pengajuan->id_pembimbing_2)) {
            $has_pembimbing_2 = true;
        }
        
        // Cek status ACC kedua pembimbing
        $status_p1 = isset($pengajuan->status_acc_judul_p1) ? $pengajuan->status_acc_judul_p1 : 'pending';
        $status_p2 = isset($pengajuan->status_acc_judul_p2) ? $pengajuan->status_acc_judul_p2 : 'pending';
        
        // Jika kedua pembimbing sudah diplot dan sudah ACC, update status pengajuan menjadi 'disetujui'
        if ($has_pembimbing_1 && $has_pembimbing_2 && 
            $status_p1 == 'acc' && $status_p2 == 'acc' && 
            $pengajuan->status != 'disetujui') {
            $update_status = array('status' => 'disetujui');
            return $this->Skripsi_pengajuan_model->update($id, $update_status);
        }
        
        return false;
    }
}

