<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Ajuan_perubahan_judul Controller
 * Menangani ajuan perubahan judul skripsi oleh sekprodi
 */
class Ajuan_perubahan_judul extends MY_Controller {

    public function __construct() {
        parent::__construct();
        $this->check_role('sekprodi');
        $this->load->model('Skripsi_pengajuan_model');
        $this->load->model('Skripsi_model');
        $this->load->model('Tahun_akademik_model');
        $this->load->library('form_validation');
    }

    /**
     * Daftar ajuan perubahan judul yang perlu direview oleh sekprodi
     */
    public function index() {
        $tahun_aktif = $this->Tahun_akademik_model->get_aktif();
        if (!$tahun_aktif) {
            $this->session->set_flashdata('error', 'Tidak ada tahun akademik aktif.');
            redirect('Dashboard');
        }

        // Ambil semua ajuan perubahan judul (termasuk yang sudah di ACC)
        $pengajuan_list = $this->Skripsi_pengajuan_model->get_all_ajuan_perubahan_judul($tahun_aktif->id);

        $data = array(
            'title' => 'Ajuan Perubahan Judul',
            'page_title' => 'Ajuan Perubahan Judul',
            'breadcrumb' => array(
                array('label' => 'Skripsi', 'url' => site_url('Judul_skripsi')),
                array('label' => 'Ajuan Perubahan Judul', 'active' => true)
            ),
            'sidebar_menu' => generate_sidebar_menu('sekprodi'),
            'pengajuan_list' => $pengajuan_list,
            'tahun_aktif' => $tahun_aktif
        );

        $data['content'] = $this->load->view('ajuan_perubahan_judul/index', $data, true);
        $this->load->view('layouts/main', $data);
    }

    /**
     * Detail ajuan perubahan judul
     */
    public function detail($id) {
        $pengajuan = $this->Skripsi_pengajuan_model->get_by_id($id);
        if (!$pengajuan) {
            $this->session->set_flashdata('error', 'Data pengajuan tidak ditemukan.');
            redirect('Ajuan_perubahan_judul');
        }

        // Cek apakah ada revisi dan file persetujuan
        $status_p1 = isset($pengajuan->status_acc_judul_p1) ? $pengajuan->status_acc_judul_p1 : 'pending';
        $status_p2 = isset($pengajuan->status_acc_judul_p2) ? $pengajuan->status_acc_judul_p2 : 'pending';
        
        if ($status_p1 != 'revisi' && $status_p2 != 'revisi') {
            $this->session->set_flashdata('error', 'Pengajuan ini tidak memiliki revisi.');
            redirect('Ajuan_perubahan_judul');
        }

        if (empty($pengajuan->file_persetujuan_judul) && empty($pengajuan->judul_revisi)) {
            $this->session->set_flashdata('error', 'Mahasiswa belum mengupload bukti perubahan judul.');
            redirect('Ajuan_perubahan_judul');
        }

        $data = array(
            'title' => 'Detail Ajuan Perubahan Judul',
            'page_title' => 'Detail Ajuan Perubahan Judul',
            'breadcrumb' => array(
                array('label' => 'Ajuan Perubahan Judul', 'url' => site_url('Ajuan_perubahan_judul')),
                array('label' => 'Detail', 'active' => true)
            ),
            'sidebar_menu' => generate_sidebar_menu('sekprodi'),
            'pengajuan' => $pengajuan
        );

        $data['content'] = $this->load->view('ajuan_perubahan_judul/detail', $data, true);
        $this->load->view('layouts/main', $data);
    }

    /**
     * ACC perubahan judul oleh sekprodi
     */
    public function acc($id) {
        $pengajuan = $this->Skripsi_pengajuan_model->get_by_id($id);
        if (!$pengajuan) {
            $this->session->set_flashdata('error', 'Data pengajuan tidak ditemukan.');
            redirect('Ajuan_perubahan_judul');
        }

        // Cek apakah ada revisi dan file persetujuan
        $status_p1 = isset($pengajuan->status_acc_judul_p1) ? $pengajuan->status_acc_judul_p1 : 'pending';
        $status_p2 = isset($pengajuan->status_acc_judul_p2) ? $pengajuan->status_acc_judul_p2 : 'pending';
        
        if ($status_p1 != 'revisi' && $status_p2 != 'revisi') {
            $this->session->set_flashdata('error', 'Pengajuan ini tidak memiliki revisi.');
            redirect('Ajuan_perubahan_judul');
        }

        if (empty($pengajuan->file_persetujuan_judul) && empty($pengajuan->judul_revisi)) {
            $this->session->set_flashdata('error', 'Mahasiswa belum mengupload bukti perubahan judul.');
            redirect('Ajuan_perubahan_judul');
        }

        // Simpan judul lama sebelum update (ini adalah judul yang sudah disetujui sebelumnya)
        $judul_lama = $pengajuan->judul_disetujui ?: $pengajuan->judul_pengajuan;
        
        // Update judul yang disetujui dengan judul revisi
        $judul_baru = !empty($pengajuan->judul_revisi) ? $pengajuan->judul_revisi : $pengajuan->judul_disetujui;
        
        // Simpan judul lama di catatan_sekprodi dengan format khusus untuk tracking
        // Format: "JUDUL_LAMA_SEBELUM_REVISI: [judul lama]"
        $catatan_judul_lama = "JUDUL_LAMA_SEBELUM_REVISI: " . $judul_lama;
        $catatan_sekarang = $pengajuan->catatan_sekprodi;
        if (!empty($catatan_sekarang) && strpos($catatan_sekarang, 'JUDUL_LAMA_SEBELUM_REVISI:') === false) {
            $catatan_sekarang = $catatan_judul_lama . "\n" . $catatan_sekarang;
        } else if (empty($catatan_sekarang)) {
            $catatan_sekarang = $catatan_judul_lama;
        }
        
        $update_data = array(
            'judul_disetujui' => $judul_baru,
            'status_approve_revisi' => 'disetujui',
            'catatan_sekprodi' => $catatan_sekarang,
            'approved_by' => $this->session->userdata('user_id'),
            'approved_at' => date('Y-m-d H:i:s')
        );

        if ($this->Skripsi_pengajuan_model->update($id, $update_data)) {
            // Update status ACC pembimbing menjadi 'acc' karena sudah disetujui sekprodi
            $update_pembimbing = array();
            if ($status_p1 == 'revisi') {
                $update_pembimbing['status_acc_judul_p1'] = 'acc';
            }
            if ($status_p2 == 'revisi') {
                $update_pembimbing['status_acc_judul_p2'] = 'acc';
            }
            
            if (!empty($update_pembimbing)) {
                $this->Skripsi_pengajuan_model->update($id, $update_pembimbing);
            }

            // Update mahasiswa_skripsi
            $tahun_aktif = $this->Tahun_akademik_model->get_aktif();
            if ($tahun_aktif) {
                $skripsi = $this->Skripsi_model->get_by_mahasiswa($pengajuan->id_mahasiswa, $tahun_aktif->id);
                if ($skripsi) {
                    $data_skripsi = array(
                        'judul_skripsi' => $judul_baru,
                        'status' => 'sedang_skripsi'
                    );
                    $this->Skripsi_model->update($skripsi->id, $data_skripsi);
                }
            }

            $this->session->set_flashdata('success', 'Perubahan judul telah disetujui. Mahasiswa dan pembimbing akan otomatis masuk ke menu Bimbingan Skripsi.');
        } else {
            $this->session->set_flashdata('error', 'Gagal menyetujui perubahan judul.');
        }

        redirect('Ajuan_perubahan_judul');
    }

    /**
     * Tolak perubahan judul oleh sekprodi
     */
    public function tolak($id) {
        if ($this->input->method() === 'post') {
            $this->form_validation->set_rules('alasan_penolakan', 'Alasan Penolakan', 'required|trim');

            if ($this->form_validation->run() == FALSE) {
                redirect('Ajuan_perubahan_judul/detail/' . $id);
            }

            $pengajuan = $this->Skripsi_pengajuan_model->get_by_id($id);
            if (!$pengajuan) {
                $this->session->set_flashdata('error', 'Data pengajuan tidak ditemukan.');
                redirect('Ajuan_perubahan_judul');
            }

            $update_data = array(
                'status_approve_revisi' => 'ditolak',
                'catatan_sekprodi' => $this->input->post('alasan_penolakan')
            );

            if ($this->Skripsi_pengajuan_model->update($id, $update_data)) {
                $this->session->set_flashdata('success', 'Perubahan judul telah ditolak.');
            } else {
                $this->session->set_flashdata('error', 'Gagal menolak perubahan judul.');
            }
        }

        redirect('Ajuan_perubahan_judul');
    }

    /**
     * Download file bukti perubahan judul
     */
    public function download_file($id) {
        $pengajuan = $this->Skripsi_pengajuan_model->get_by_id($id);
        if (!$pengajuan) {
            $this->session->set_flashdata('error', 'Data pengajuan tidak ditemukan.');
            redirect('Ajuan_perubahan_judul');
        }

        if (empty($pengajuan->file_persetujuan_judul)) {
            $this->session->set_flashdata('error', 'File tidak ditemukan.');
            redirect('Ajuan_perubahan_judul/detail/' . $id);
        }

        $file_path = FCPATH . $pengajuan->file_persetujuan_judul;
        
        if (!file_exists($file_path)) {
            // Coba dengan path alternatif jika file tidak ditemukan
            $alt_path = FCPATH . ltrim(str_replace('uploads/', './uploads/', $pengajuan->file_persetujuan_judul), './');
            if (file_exists($alt_path)) {
                $file_path = $alt_path;
            } else {
                $this->session->set_flashdata('error', 'File tidak ditemukan di server.');
                redirect('Ajuan_perubahan_judul/detail/' . $id);
            }
        }

        $this->load->helper('download');
        $file_name = basename($file_path);
        
        // Cek apakah file bisa dibaca
        if (!is_readable($file_path)) {
            $this->session->set_flashdata('error', 'File tidak dapat dibaca. Silakan hubungi administrator.');
            redirect('Ajuan_perubahan_judul/detail/' . $id);
        }
        
        force_download($file_name, file_get_contents($file_path));
    }
}

