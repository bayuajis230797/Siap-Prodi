<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Pkl_perubahan_judul_sekprodi Controller
 * Menangani review permintaan perubahan judul PKL oleh sekprodi
 */
class Pkl_perubahan_judul_sekprodi extends MY_Controller {

    public function __construct() {
        parent::__construct();
        $this->check_role('sekprodi');
        $this->load->model('Pkl_pengajuan_model');
        $this->load->model('Pkl_tempat_model');
        $this->load->model('Tahun_akademik_model');
        $this->load->library('form_validation');
    }

    /**
     * Daftar permintaan perubahan judul PKL yang perlu direview oleh sekprodi
     */
    public function index() {
        $tahun_aktif = $this->Tahun_akademik_model->get_aktif();
        if (!$tahun_aktif) {
            $this->session->set_flashdata('error', 'Tidak ada tahun akademik aktif.');
            redirect('Dashboard');
        }

        // Ambil semua permintaan perubahan judul (termasuk yang sudah disetujui/ditolak) agar ada riwayat di menu ini
        $all_pengajuan = $this->Pkl_pengajuan_model->get_all_perubahan_judul($tahun_aktif->id);

        $pengajuan_list = array();
        foreach ($all_pengajuan as $pengajuan) {
            if ($pengajuan->judul_pkl == Pkl_pengajuan_model::PLACEHOLDER_JUDUL) {
                continue;
            }
            // Tampilkan semua yang pernah masuk alur perubahan judul (pending / disetujui / ditolak)
            $st = isset($pengajuan->status_approve_revisi) ? (string) $pengajuan->status_approve_revisi : '';
            if ($st === 'pending' || $st === 'disetujui' || $st === 'ditolak' || $st === '') {
                $pengajuan_list[] = $pengajuan;
            }
        }

        $data = array(
            'title' => 'Perubahan Judul PKL',
            'page_title' => 'Perubahan Judul PKL',
            'breadcrumb' => array(
                array('label' => 'PKL', 'url' => '#'),
                array('label' => 'Perubahan Judul', 'active' => true)
            ),
            'sidebar_menu' => generate_sidebar_menu('sekprodi'),
            'pengajuan_list' => $pengajuan_list,
            'tahun_aktif' => $tahun_aktif
        );

        $data['content'] = $this->load->view('sekprodi/pkl_perubahan_judul_index', $data, true);
        $this->load->view('layouts/main', $data);
    }

    /**
     * Detail permintaan perubahan judul
     */
    public function detail($id) {
        $pengajuan = $this->Pkl_pengajuan_model->get_by_id($id);
        if (!$pengajuan) {
            $this->session->set_flashdata('error', 'Data pengajuan tidak ditemukan.');
            redirect('Pkl_perubahan_judul_sekprodi');
        }

        // Cek apakah ada permintaan perubahan judul (atau riwayat yang sudah diproses — tetap boleh dibuka untuk historis)
        $st = isset($pengajuan->status_approve_revisi) ? (string) $pengajuan->status_approve_revisi : '';
        $ada_riwayat = !empty($pengajuan->file_approve_revisi) || trim((string) ($pengajuan->judul_revisi ?? '')) !== ''
            || $st === 'disetujui' || $st === 'ditolak';
        if (!$ada_riwayat) {
            $this->session->set_flashdata('error', 'Tidak ada permintaan perubahan judul untuk pengajuan ini.');
            redirect('Pkl_perubahan_judul_sekprodi');
        }

        // Ambil data tempat PKL
        $tempat_pkl = $this->Pkl_tempat_model->get_by_mahasiswa($pengajuan->id_mahasiswa, $pengajuan->id_tahun_akademik);

        if ($this->input->method() === 'post') {
            $action = $this->input->post('action');
            
            if ($action === 'approve') {
                // Approve perubahan judul
                $judul_baru = $pengajuan->judul_revisi ?: $pengajuan->judul_pkl;
                
                $update_data = array(
                    'judul_pkl' => $judul_baru,
                    'judul_revisi' => null, // Reset setelah digunakan
                    'status_approve_revisi' => 'disetujui',
                    'status_acc_judul' => 'acc' // Otomatis acc setelah sekprodi approve
                );

                if ($this->Pkl_pengajuan_model->update($id, $update_data)) {
                    $this->session->set_flashdata('success', 'Permintaan perubahan judul disetujui. Judul PKL telah diperbarui dan status menjadi disetujui.');
                } else {
                    $this->session->set_flashdata('error', 'Gagal menyetujui permintaan perubahan judul.');
                }
            } elseif ($action === 'reject') {
                // Reject perubahan judul
                $update_data = array(
                    'status_approve_revisi' => 'ditolak'
                );
                
                if ($this->Pkl_pengajuan_model->update($id, $update_data)) {
                    $this->session->set_flashdata('success', 'Permintaan perubahan judul ditolak.');
                } else {
                    $this->session->set_flashdata('error', 'Gagal menolak permintaan perubahan judul.');
                }
            }
            
            redirect('Pkl_perubahan_judul_sekprodi');
        }

        $data = array(
            'title' => 'Detail Perubahan Judul PKL',
            'page_title' => 'Detail Perubahan Judul PKL',
            'breadcrumb' => array(
                array('label' => 'PKL', 'url' => '#'),
                array('label' => 'Perubahan Judul', 'url' => site_url('Pkl_perubahan_judul_sekprodi')),
                array('label' => 'Detail', 'active' => true)
            ),
            'sidebar_menu' => generate_sidebar_menu('sekprodi'),
            'pengajuan' => $pengajuan,
            'tempat_pkl' => $tempat_pkl
        );

        $data['content'] = $this->load->view('sekprodi/pkl_perubahan_judul_detail', $data, true);
        $this->load->view('layouts/main', $data);
    }

    /**
     * Download file approve revisi PKL
     */
    public function download_approve_revisi($id) {
        $pengajuan = $this->Pkl_pengajuan_model->get_by_id($id);
        
        if (!$pengajuan) {
            $this->session->set_flashdata('error', 'Data pengajuan tidak ditemukan.');
            redirect('Pkl_perubahan_judul_sekprodi');
        }
        
        // Cek apakah file approve revisi ada
        if (!$pengajuan->file_approve_revisi) {
            $this->session->set_flashdata('error', 'File tidak ditemukan.');
            redirect('Pkl_perubahan_judul_sekprodi/detail/' . $id);
        }
        
        // Bersihkan path dari ./ jika ada dan normalisasi path
        $file_path = $pengajuan->file_approve_revisi;
        $file_path = ltrim($file_path, './');
        $file_path = str_replace('\\', '/', $file_path);
        
        $full_path = FCPATH . $file_path;
        
        if (!file_exists($full_path)) {
            $this->session->set_flashdata('error', 'File tidak ditemukan di server.');
            redirect('Pkl_perubahan_judul_sekprodi/detail/' . $id);
        }
        
        $this->load->helper('download');
        $file_name = basename($file_path);
        force_download($file_name, file_get_contents($full_path));
    }
}

