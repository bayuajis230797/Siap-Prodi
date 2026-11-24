<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Skripsi_perubahan_judul Controller
 * Menangani permintaan perubahan judul Skripsi oleh mahasiswa
 */
class Skripsi_perubahan_judul extends MY_Controller {

    public function __construct() {
        parent::__construct();
        $this->check_role('mahasiswa');
        $this->load->model('Skripsi_perubahan_judul_model');
        $this->load->model('Skripsi_pengajuan_model');
        $this->load->model('Skripsi_model');
        $this->load->model('Tahun_akademik_model');
        $this->load->library('form_validation');
        $this->load->helper('file');
    }

    /**
     * Form perubahan judul
     */
    public function index() {
        $id_mahasiswa = $this->session->userdata('ref_id');
        $tahun_aktif = $this->Tahun_akademik_model->get_aktif();
        
        if (!$tahun_aktif) {
            $this->session->set_flashdata('error', 'Tidak ada tahun akademik aktif.');
            redirect('Halaman_utama');
        }

        // Cek apakah mahasiswa terdaftar Skripsi
        $skripsi = $this->Skripsi_model->get_by_mahasiswa($id_mahasiswa, $tahun_aktif->id);
        if (!$skripsi) {
            $this->session->set_flashdata('error', 'Anda belum terdaftar sebagai peserta Skripsi.');
            redirect('Halaman_utama');
        }

        // Ambil pengajuan terakhir
        $pengajuan = $this->Skripsi_pengajuan_model->get_latest_by_mahasiswa($id_mahasiswa, $tahun_aktif->id);
        if (!$pengajuan) {
            $this->session->set_flashdata('error', 'Data pengajuan tidak ditemukan.');
            redirect('Halaman_utama');
        }

        // Cek apakah judul sudah direvisi oleh pembimbing
        $status_p1 = isset($pengajuan->status_acc_judul_p1) ? $pengajuan->status_acc_judul_p1 : 'pending';
        $status_p2 = isset($pengajuan->status_acc_judul_p2) ? $pengajuan->status_acc_judul_p2 : 'pending';
        
        // Hanya bisa submit perubahan jika salah satu atau kedua pembimbing sudah merevisi
        if ($status_p1 != 'revisi' && $status_p2 != 'revisi') {
            $this->session->set_flashdata('error', 'Judul belum direvisi oleh pembimbing. Tidak bisa melakukan perubahan judul.');
            redirect('Halaman_utama');
        }

        // Ambil semua perubahan judul yang sudah ada untuk mahasiswa ini
        $perubahan_list = $this->Skripsi_perubahan_judul_model->get_by_mahasiswa($id_mahasiswa, $tahun_aktif->id);

        // Pending = bisa diedit; Ditolak terakhir = dipakai sebagai referensi pengajuan ulang.
        $perubahan_pending = $this->Skripsi_perubahan_judul_model->get_latest_by_pengajuan_status($pengajuan->id, 'pending');
        $perubahan_ditolak = $this->Skripsi_perubahan_judul_model->get_latest_by_pengajuan_status($pengajuan->id, 'ditolak');
        $perubahan_existing = $perubahan_pending;
        $perubahan_form = $perubahan_pending ? $perubahan_pending : $perubahan_ditolak;

        if ($this->input->method() === 'post') {
            $this->form_validation->set_rules('judul_baru', 'Judul Baru', 'required|trim|min_length[10]');

            if ($this->form_validation->run() == FALSE) {
                // Load form dengan error
            } else {
                if (empty($_FILES['file_ttd_persetujuan']['name'])) {
                    $this->session->set_flashdata('error', 'Berkas TTD persetujuan wajib diunggah.');
                    redirect('Skripsi_perubahan_judul');
                }
                $this->load->helper('google_drive');
                require_google_drive_or_redirect();
                $this->load->library('Upload_to_drive');
                $share_emails = array();
                if (!empty($pengajuan->id_pembimbing_1_final)) {
                    $email = $this->upload_to_drive->get_drive_email_by_role_ref('dosen', (int) $pengajuan->id_pembimbing_1_final);
                    if ($email) {
                        $share_emails[] = $email;
                    }
                }
                if (!empty($pengajuan->id_pembimbing_2_final)) {
                    $email = $this->upload_to_drive->get_drive_email_by_role_ref('dosen', (int) $pengajuan->id_pembimbing_2_final);
                    if ($email && !in_array($email, $share_emails, true)) {
                        $share_emails[] = $email;
                    }
                }
                $sekprodi_owner = $this->upload_to_drive->find_connected_sekprodi_user_id();
                if ($sekprodi_owner) {
                    $email = $this->upload_to_drive->get_drive_email($sekprodi_owner);
                    if ($email && !in_array($email, $share_emails, true)) {
                        $share_emails[] = $email;
                    }
                }
                $upload = $this->upload_to_drive->handle_upload('file_ttd_persetujuan', $this->upload_to_drive->default_options(array('SIAP', 'Skripsi', 'perubahan_judul'), $share_emails));
                if (empty($upload['ok'])) {
                    $this->session->set_flashdata('error', isset($upload['message']) ? $upload['message'] : 'Gagal upload berkas.');
                    redirect('Skripsi_perubahan_judul');
                }
                $file_ttd = $upload['webViewLink'];

                // Update atau create perubahan judul
                $data_perubahan = array(
                    'id_pengajuan' => $pengajuan->id,
                    'judul_baru' => $this->input->post('judul_baru'),
                    'file_ttd_persetujuan' => $file_ttd,
                    'status_approve_sekprodi' => 'pending'
                );

                if ($perubahan_existing) {
                    // Update perubahan yang sudah ada
                    if ($this->Skripsi_perubahan_judul_model->update($perubahan_existing->id, $data_perubahan)) {
                        $this->load->helper(array('notifikasi', 'sekprodi_badge'));
                        notify_sekprodi(
                            'Perubahan Judul Skripsi',
                            'Ada permintaan perubahan judul skripsi yang menunggu validasi.',
                            site_url('Skripsi_perubahan_judul_sekprodi'),
                            'skripsi_perubahan'
                        );
                        $this->session->set_flashdata('success', 'Permintaan perubahan judul berhasil diperbarui. Menunggu validasi dari Sekprodi.');
                    } else {
                        $this->session->set_flashdata('error', 'Gagal memperbarui permintaan perubahan judul.');
                    }
                } else {
                    // Create baru
                    if ($this->Skripsi_perubahan_judul_model->create($data_perubahan)) {
                        $this->load->helper(array('notifikasi', 'sekprodi_badge'));
                        notify_sekprodi(
                            'Perubahan Judul Skripsi',
                            'Ada permintaan perubahan judul skripsi yang menunggu validasi.',
                            site_url('Skripsi_perubahan_judul_sekprodi'),
                            'skripsi_perubahan'
                        );
                        $this->session->set_flashdata('success', 'Permintaan perubahan judul berhasil dikirim. Menunggu validasi dari Sekprodi.');
                    } else {
                        $this->session->set_flashdata('error', 'Gagal mengirim permintaan perubahan judul.');
                    }
                }

                redirect('Skripsi_perubahan_judul');
            }
        }

        // Ambil judul terbaru dari pengajuan (jika sudah ada yang di-acc)
        $judul_terbaru = null;
        if (!empty($perubahan_list)) {
            // Cari perubahan yang sudah di-acc (yang paling terbaru)
            foreach ($perubahan_list as $p) {
                if ($p->status_approve_sekprodi == 'acc') {
                    // Jika sudah di-acc, judul terbaru adalah judul_baru dari perubahan yang di-acc
                    $judul_terbaru = $p->judul_baru;
                    break;
                }
            }
        }
        
        // Jika belum ada yang di-acc, ambil dari pengajuan
        if (empty($judul_terbaru) && isset($pengajuan)) {
            $judul_terbaru = isset($pengajuan->judul_disetujui) && !empty($pengajuan->judul_disetujui) 
                ? $pengajuan->judul_disetujui 
                : (isset($pengajuan->judul_pengajuan) ? $pengajuan->judul_pengajuan : null);
        }

        $data = array(
            'title' => 'Perubahan Judul Skripsi',
            'page_title' => 'Perubahan Judul Skripsi',
            'breadcrumb' => array(
                array('label' => 'Skripsi', 'url' => '#'),
                array('label' => 'Perubahan Judul', 'active' => true)
            ),
            'sidebar_menu' => generate_sidebar_menu('mahasiswa'),
            'pengajuan' => $pengajuan,
            'perubahan' => $perubahan_existing,
            'perubahan_form' => $perubahan_form,
            'perubahan_pending' => $perubahan_pending,
            'perubahan_ditolak' => $perubahan_ditolak,
            'perubahan_list' => $perubahan_list,
            'judul_terbaru' => $judul_terbaru,
            'tahun_aktif' => $tahun_aktif
        );

        $data['content'] = $this->load->view('mahasiswa/skripsi_perubahan_judul/index', $data, true);
        $this->load->view('layouts/main', $data);
    }

    /**
     * Validasi Google Drive link
     */
    public function validate_google_drive_link($link) {
        $this->load->helper('google_drive');
        
        if (empty($link)) {
            $this->form_validation->set_message('validate_google_drive_link', 'Link Google Drive wajib diisi.');
            return false;
        }
        
        if (!is_valid_google_drive_link($link)) {
            $this->form_validation->set_message('validate_google_drive_link', 'Link Google Drive tidak valid. Pastikan link adalah link Google Drive yang valid.');
            return false;
        }
        
        return true;
    }

    /**
     * Redirect ke Google Drive link TTD persetujuan perubahan judul
     */
    public function download_ttd($id) {
        $id_mahasiswa = $this->session->userdata('ref_id');
        
        // Ambil perubahan judul
        $perubahan = $this->Skripsi_perubahan_judul_model->get_by_id($id);
        if (!$perubahan || empty($perubahan->file_ttd_persetujuan)) {
            $this->session->set_flashdata('error', 'Link tidak ditemukan.');
            redirect('Skripsi_perubahan_judul');
        }

        // Ambil pengajuan untuk verifikasi kepemilikan
        $pengajuan = $this->Skripsi_pengajuan_model->get_by_id($perubahan->id_pengajuan);
        if (!$pengajuan || $pengajuan->id_mahasiswa != $id_mahasiswa) {
            $this->session->set_flashdata('error', 'Anda tidak memiliki akses untuk mengakses link ini.');
            redirect('Skripsi_perubahan_judul');
        }

        // Cek apakah ini link Google Drive
        $file_path = $perubahan->file_ttd_persetujuan;
        
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
            redirect('Skripsi_perubahan_judul');
        }

        $this->load->helper('download');
        $file_name = basename($file_path);
        force_download($file_name, file_get_contents($full_path));
    }
}

