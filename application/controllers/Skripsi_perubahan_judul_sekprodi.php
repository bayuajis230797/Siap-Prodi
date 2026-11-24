<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Skripsi_perubahan_judul_sekprodi Controller
 * Menangani validasi permintaan perubahan judul Skripsi oleh sekprodi
 */
class Skripsi_perubahan_judul_sekprodi extends MY_Controller {

    public function __construct() {
        parent::__construct();
        $this->check_role('sekprodi');
        $this->load->model('Skripsi_perubahan_judul_model');
        $this->load->model('Skripsi_pengajuan_model');
        $this->load->model('Skripsi_model');
        $this->load->model('Tahun_akademik_model');
        $this->load->library('form_validation');
    }

    /**
     * Daftar permintaan perubahan judul yang perlu divalidasi
     */
    public function index() {
        $tahun_aktif = $this->Tahun_akademik_model->get_aktif();
        if (!$tahun_aktif) {
            $this->session->set_flashdata('error', 'Tidak ada tahun akademik aktif.');
            redirect('Dashboard');
        }

        // Ambil semua permintaan perubahan judul (pending, acc, ditolak)
        $perubahan_list = $this->Skripsi_perubahan_judul_model->get_all(array('id_tahun_akademik' => $tahun_aktif->id)); // mengikuti tahun akademik aktif/dilihat

        $data = array(
            'title' => 'Validasi Perubahan Judul Skripsi',
            'page_title' => 'Validasi Perubahan Judul Skripsi',
            'breadcrumb' => array(
                array('label' => 'Skripsi', 'url' => '#'),
                array('label' => 'Validasi Perubahan Judul', 'active' => true)
            ),
            'sidebar_menu' => generate_sidebar_menu('sekprodi'),
            'perubahan_list' => $perubahan_list,
            'tahun_aktif' => $tahun_aktif
        );

        $data['content'] = $this->load->view('sekprodi/skripsi_perubahan_judul/index', $data, true);
        $this->load->view('layouts/main', $data);
    }

    /**
     * Detail permintaan perubahan judul
     */
    public function detail($id) {
        $perubahan = $this->Skripsi_perubahan_judul_model->get_by_id($id);
        if (!$perubahan) {
            $this->session->set_flashdata('error', 'Data permintaan perubahan judul tidak ditemukan.');
            redirect('Skripsi_perubahan_judul_sekprodi');
        }

        // Ambil data pengajuan
        $pengajuan = $this->Skripsi_pengajuan_model->get_by_id($perubahan->id_pengajuan);
        if (!$pengajuan) {
            $this->session->set_flashdata('error', 'Data pengajuan tidak ditemukan.');
            redirect('Skripsi_perubahan_judul_sekprodi');
        }

        if ($this->input->method() === 'post') {
            $action = $this->input->post('action');
            
            if ($action === 'approve') {
                // Approve perubahan judul
                $update_perubahan = array(
                    'status_approve_sekprodi' => 'acc',
                    'catatan_sekprodi' => $this->input->post('catatan_sekprodi')
                );
                
                if ($this->Skripsi_perubahan_judul_model->update($id, $update_perubahan)) {
                    // Update judul_disetujui di pengajuan dengan judul baru
                    $update_pengajuan = array(
                        'judul_disetujui' => $perubahan->judul_baru,
                        'status' => 'disetujui' // Update status pengajuan menjadi disetujui ketika perubahan judul di-ACC sekprodi
                    );
                    
                    // Update status ACC pembimbing menjadi 'acc' karena perubahan judul sudah di-approve sekprodi
                    $status_p1 = isset($pengajuan->status_acc_judul_p1) ? $pengajuan->status_acc_judul_p1 : 'pending';
                    $status_p2 = isset($pengajuan->status_acc_judul_p2) ? $pengajuan->status_acc_judul_p2 : 'pending';
                    
                    if ($status_p1 == 'revisi') {
                        $update_pengajuan['status_acc_judul_p1'] = 'acc';
                    }
                    if ($status_p2 == 'revisi') {
                        $update_pengajuan['status_acc_judul_p2'] = 'acc';
                    }
                    
                    $this->Skripsi_pengajuan_model->update($perubahan->id_pengajuan, $update_pengajuan);
                    
                    // Update judul di mahasiswa_skripsi
                    $tahun_aktif = $this->Tahun_akademik_model->get_aktif();
                    if ($tahun_aktif && isset($pengajuan->id_mahasiswa)) {
                        $skripsi = $this->Skripsi_model->get_by_mahasiswa($pengajuan->id_mahasiswa, $tahun_aktif->id);
                        if ($skripsi) {
                            $this->Skripsi_model->update($skripsi->id, array(
                                'judul_skripsi' => $perubahan->judul_baru
                            ));
                        }
                    }
                    
                    $this->session->set_flashdata('success', 'Permintaan perubahan judul disetujui. Judul Skripsi telah diperbarui.');
                } else {
                    $this->session->set_flashdata('error', 'Gagal menyetujui permintaan perubahan judul.');
                }
            } elseif ($action === 'reject') {
                // Reject perubahan judul
                $update_perubahan = array(
                    'status_approve_sekprodi' => 'ditolak',
                    'catatan_sekprodi' => $this->input->post('catatan_sekprodi')
                );
                
                if ($this->Skripsi_perubahan_judul_model->update($id, $update_perubahan)) {
                    $this->session->set_flashdata('success', 'Permintaan perubahan judul ditolak.');
                } else {
                    $this->session->set_flashdata('error', 'Gagal menolak permintaan perubahan judul.');
                }
            }
            
            redirect('Skripsi_perubahan_judul_sekprodi');
        }

        $data = array(
            'title' => 'Detail Validasi Perubahan Judul',
            'page_title' => 'Detail Validasi Perubahan Judul',
            'breadcrumb' => array(
                array('label' => 'Skripsi', 'url' => '#'),
                array('label' => 'Validasi Perubahan Judul', 'url' => site_url('Skripsi_perubahan_judul_sekprodi')),
                array('label' => 'Detail', 'active' => true)
            ),
            'sidebar_menu' => generate_sidebar_menu('sekprodi'),
            'perubahan' => $perubahan,
            'pengajuan' => $pengajuan
        );

        $data['content'] = $this->load->view('sekprodi/skripsi_perubahan_judul/detail', $data, true);
        $this->load->view('layouts/main', $data);
    }

    /**
     * Redirect ke Google Drive link TTD persetujuan perubahan judul
     */
    public function download_ttd($id) {
        $perubahan = $this->Skripsi_perubahan_judul_model->get_by_id($id);
        if (!$perubahan || empty($perubahan->file_ttd_persetujuan)) {
            $this->session->set_flashdata('error', 'Link tidak ditemukan.');
            redirect('Skripsi_perubahan_judul_sekprodi');
        }

        $file_path = $perubahan->file_ttd_persetujuan;
        
        // Cek apakah ini link Google Drive
        if (strpos($file_path, 'http') === 0) {
            // Ini adalah link Google Drive, redirect ke link tersebut
            $this->load->helper('google_drive');
            $normalized_link = normalize_google_drive_link($file_path);
            redirect($normalized_link);
        }
        
        // Jika bukan link, coba download file (untuk kompatibilitas dengan data lama)
        $file_path = ltrim($file_path, './');
        $file_path = str_replace('\\', '/', $file_path);
        
        $full_path = FCPATH . $file_path;
        
        if (!file_exists($full_path)) {
            $this->session->set_flashdata('error', 'File tidak ditemukan di server.');
            redirect('Skripsi_perubahan_judul_sekprodi');
        }

        $this->load->helper('download');
        $file_name = basename($file_path);
        force_download($file_name, file_get_contents($full_path));
    }
}


