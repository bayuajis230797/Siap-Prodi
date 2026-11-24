<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Pendaftaran_sidang_skripsi Controller
 * Menangani pendaftaran sidang Skripsi oleh mahasiswa
 */
class Pendaftaran_sidang_skripsi extends MY_Controller {

    public function __construct() {
        parent::__construct();
        $this->check_role('mahasiswa');
        $this->load->model('Skripsi_pendaftaran_sidang_model');
        $this->load->model('Skripsi_berkas_upload_model');
        $this->load->model('Skripsi_berkas_sidang_model');
        $this->load->model('Skripsi_model');
        $this->load->model('Skripsi_nilai_bimbingan_model');
        $this->load->model('Tahun_akademik_model');
        $this->load->library('form_validation');
        $this->load->helper('file');
    }

    /**
     * Index - form pendaftaran sidang Skripsi
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

        // Ambil nilai bimbingan dari kedua pembimbing
        $nilai_bimbingan_list = $this->Skripsi_nilai_bimbingan_model->get_by_skripsi($skripsi->id);
        $nilai_p1 = null;
        $nilai_p2 = null;
        $pembimbing_1_sudah_nilai = false;
        $pembimbing_2_sudah_nilai = false;
        
        foreach ($nilai_bimbingan_list as $nb) {
            if ($nb->tipe_pembimbing == 1) {
                $nilai_p1 = $nb;
                $pembimbing_1_sudah_nilai = true;
            } elseif ($nb->tipe_pembimbing == 2) {
                $nilai_p2 = $nb;
                $pembimbing_2_sudah_nilai = true;
            }
        }
        
        // Cek status ACC dari pembimbing
        $pembimbing_1_acc = (isset($skripsi->acc_pembimbing_1) && $skripsi->acc_pembimbing_1 == 1);
        $pembimbing_2_acc = (isset($skripsi->acc_pembimbing_2) && $skripsi->acc_pembimbing_2 == 1);
        
        // Cek apakah sudah layak sidang
        // Prioritas: gunakan status dari tabel jika sudah 'layak_sidang', jika belum cek kondisi manual
        $layak_sidang = false;
        if (isset($skripsi->status) && $skripsi->status === 'layak_sidang') {
            // Status sudah di-update menjadi 'layak_sidang' (kedua pembimbing sudah ACC dan input nilai)
            $layak_sidang = true;
        } else {
            // Cek manual: kedua pembimbing sudah ACC dan sudah input nilai
            // Pembimbing 2 opsional: bila tidak diplot, cukup Pembimbing 1.
            $p2_terpenuhi = empty($skripsi->id_pembimbing_2) ? true : ($pembimbing_2_acc && $pembimbing_2_sudah_nilai);
            $layak_sidang = ($pembimbing_1_acc && $pembimbing_1_sudah_nilai && $p2_terpenuhi);
        }

        // Ambil judul skripsi
        // Prioritas: judul dari perubahan judul yang di-approve sekprodi > judul_disetujui > judul_pengajuan
        $this->load->model('Skripsi_pengajuan_model');
        $this->load->model('Skripsi_perubahan_judul_model');
        
        $judul_skripsi = 'Belum ada judul';
        
        // Ambil pengajuan terakhir
        $pengajuan = $this->Skripsi_pengajuan_model->get_latest_by_mahasiswa($id_mahasiswa, $tahun_aktif->id);
        if ($pengajuan) {
            // Cek apakah ada perubahan judul yang sudah di-approve sekprodi
            $perubahan_approved = $this->Skripsi_perubahan_judul_model->get_by_pengajuan($pengajuan->id);
            
            if ($perubahan_approved && isset($perubahan_approved->status_approve_sekprodi) && $perubahan_approved->status_approve_sekprodi === 'acc') {
                // Jika ada perubahan judul yang di-approve sekprodi, pakai judul terbaru
                $judul_skripsi = $perubahan_approved->judul_baru;
            } elseif (isset($pengajuan->judul_disetujui) && !empty($pengajuan->judul_disetujui)) {
                // Jika judul langsung di-ACC kedua pembimbing tanpa perubahan, pakai judul_disetujui
                $judul_skripsi = $pengajuan->judul_disetujui;
            } elseif (isset($pengajuan->judul_pengajuan) && !empty($pengajuan->judul_pengajuan)) {
                // Fallback ke judul_pengajuan
                $judul_skripsi = $pengajuan->judul_pengajuan;
            }
        }
        
        // Fallback ke judul dari mahasiswa_skripsi jika belum ada
        if ($judul_skripsi == 'Belum ada judul' && isset($skripsi->judul_skripsi) && !empty($skripsi->judul_skripsi)) {
            $judul_skripsi = $skripsi->judul_skripsi;
        }

        // Cek apakah sudah ada pendaftaran tahap verifikasi
        $pendaftaran = $this->Skripsi_pendaftaran_sidang_model->get_by_mahasiswa_skripsi_tahap($skripsi->id, 'verifikasi');
        
        // Ambil berkas yang sudah diupload
        $berkas_uploaded = array();
        if ($pendaftaran) {
            $berkas_uploaded = $this->Skripsi_berkas_upload_model->get_by_pendaftaran($pendaftaran->id);
        }

        // Ambil daftar berkas tahap verifikasi
        $berkas_list = $this->Skripsi_berkas_sidang_model->get_by_tahap('verifikasi', true);
        if (empty($berkas_list) && $this->db->field_exists('tahap', 'skripsi_berkas_sidang')) {
            $berkas_list = array();
        } elseif (empty($berkas_list)) {
            $berkas_list = $this->Skripsi_berkas_sidang_model->get_all(true);
        }

        $data = array(
            'title' => 'Pendaftaran Sidang Skripsi',
            'page_title' => 'Pendaftaran Sidang Skripsi',
            'breadcrumb' => array(
                array('label' => 'Skripsi', 'url' => '#'),
                array('label' => 'Pendaftaran Sidang', 'active' => true)
            ),
            'sidebar_menu' => generate_sidebar_menu('mahasiswa'),
            'pendaftaran' => $pendaftaran,
            'berkas_list' => $berkas_list,
            'berkas_uploaded' => $berkas_uploaded,
            'skripsi' => $skripsi,
            'tahun_aktif' => $tahun_aktif,
            'judul_skripsi' => $judul_skripsi,
            'pembimbing_1_sudah_nilai' => $pembimbing_1_sudah_nilai,
            'pembimbing_2_sudah_nilai' => $pembimbing_2_sudah_nilai,
            'pembimbing_1_acc' => $pembimbing_1_acc,
            'pembimbing_2_acc' => $pembimbing_2_acc,
            'layak_sidang' => $layak_sidang,
            'nilai_p1' => $nilai_p1,
            'nilai_p2' => $nilai_p2
        );

        $data['content'] = $this->load->view('mahasiswa/pendaftaran_sidang_skripsi/index', $data, true);
        $this->load->view('layouts/main', $data);
    }

    /**
     * Submit pendaftaran sidang Skripsi
     */
    public function submit() {
        $id_mahasiswa = $this->session->userdata('ref_id');
        $tahun_aktif = $this->Tahun_akademik_model->get_aktif();
        
        if (!$tahun_aktif) {
            $this->session->set_flashdata('error', 'Tidak ada tahun akademik aktif.');
            redirect('Halaman_utama');
        }

        // Cek apakah sudah layak sidang
        $skripsi = $this->Skripsi_model->get_by_mahasiswa($id_mahasiswa, $tahun_aktif->id);
        if (!$skripsi) {
            $this->session->set_flashdata('error', 'Anda belum terdaftar sebagai peserta Skripsi.');
            redirect('Halaman_utama');
        }
        
        // Cek nilai bimbingan dari kedua pembimbing
        $nilai_bimbingan_list = $this->Skripsi_nilai_bimbingan_model->get_by_skripsi($skripsi->id);
        $pembimbing_1_sudah_nilai = false;
        $pembimbing_2_sudah_nilai = false;
        
        foreach ($nilai_bimbingan_list as $nb) {
            if ($nb->tipe_pembimbing == 1) {
                $pembimbing_1_sudah_nilai = true;
            } elseif ($nb->tipe_pembimbing == 2) {
                $pembimbing_2_sudah_nilai = true;
            }
        }
        
        // Cek status ACC dari pembimbing
        $pembimbing_1_acc = (isset($skripsi->acc_pembimbing_1) && $skripsi->acc_pembimbing_1 == 1);
        $pembimbing_2_acc = (isset($skripsi->acc_pembimbing_2) && $skripsi->acc_pembimbing_2 == 1);
        
        // Cek apakah sudah layak sidang
        // Prioritas: gunakan status dari tabel jika sudah 'layak_sidang', jika belum cek kondisi manual
        $layak_sidang = false;
        if (isset($skripsi->status) && $skripsi->status === 'layak_sidang') {
            // Status sudah di-update menjadi 'layak_sidang' (kedua pembimbing sudah ACC dan input nilai)
            $layak_sidang = true;
        } else {
            // Cek manual: kedua pembimbing sudah ACC dan sudah input nilai
            $p2_terpenuhi = empty($skripsi->id_pembimbing_2) ? true : ($pembimbing_2_acc && $pembimbing_2_sudah_nilai);
            $layak_sidang = ($pembimbing_1_acc && $pembimbing_1_sudah_nilai && $p2_terpenuhi);
        }

        if (!$layak_sidang) {
            $this->session->set_flashdata('error', 'Anda belum layak sidang. Pastikan kedua pembimbing sudah memberikan nilai bimbingan dan menyatakan Anda layak sidang.');
            redirect('Pendaftaran_sidang_skripsi');
        }

        // Cek apakah sudah ada pendaftaran tahap verifikasi
        $pendaftaran = $this->Skripsi_pendaftaran_sidang_model->get_by_mahasiswa_skripsi_tahap($skripsi->id, 'verifikasi');
        
        // Ambil daftar berkas tahap verifikasi
        $berkas_list = $this->Skripsi_berkas_sidang_model->get_by_tahap('verifikasi', true);
        if (empty($berkas_list) && $this->db->field_exists('tahap', 'skripsi_berkas_sidang')) {
            $berkas_list = array();
        } elseif (empty($berkas_list)) {
            $berkas_list = $this->Skripsi_berkas_sidang_model->get_all(true);
        }
        $berkas_wajib = array();
        foreach ($berkas_list as $berkas) {
            if ($berkas->wajib) {
                $berkas_wajib[] = $berkas->id;
            }
        }

        // Validasi link wajib
        $errors = array();
        foreach ($berkas_wajib as $id_berkas) {
            $field_name = 'file_berkas_' . $id_berkas;
            if (empty($_FILES[$field_name]['name'])) {
                // Cek apakah sudah pernah diupload
                if ($pendaftaran) {
                    $existing = $this->Skripsi_berkas_upload_model->get_by_pendaftaran_berkas($pendaftaran->id, $id_berkas);
                    if (!$existing) {
                        $errors[] = "Berkas wajib belum diupload";
                        break;
                    }
                } else {
                    $errors[] = "Berkas wajib belum diupload";
                    break;
                }
            }
        }

        if (!empty($errors)) {
            $this->session->set_flashdata('error', implode(', ', $errors));
            redirect('Pendaftaran_sidang_skripsi');
        }

        // Buat atau update pendaftaran tahap verifikasi
        $pendaftaran_data = array(
            'id_mahasiswa_skripsi' => $skripsi->id,
            'id_tahun_akademik' => $tahun_aktif->id,
            'status' => 'menunggu'
        );
        if ($this->db->field_exists('tahap', 'skripsi_pendaftaran_sidang')) {
            $pendaftaran_data['tahap'] = 'verifikasi';
        }
        if (!$pendaftaran) {
            $pendaftaran_id = $this->Skripsi_pendaftaran_sidang_model->create($pendaftaran_data);
        } else {
            $pendaftaran_id = $pendaftaran->id;
            // Update status jika ditolak sebelumnya
            if ($pendaftaran->status == 'ditolak') {
                $this->Skripsi_pendaftaran_sidang_model->update($pendaftaran_id, array('status' => 'menunggu'));
            }
        }

        $needs_upload = false;
        foreach ($berkas_list as $berkas) {
            if (!empty($_FILES['file_berkas_' . $berkas->id]['name'])) {
                $needs_upload = true;
                break;
            }
        }
        if ($needs_upload) {
            $this->load->helper('google_drive');
            require_google_drive_or_redirect();
        }
        $this->load->library('Upload_to_drive');
        $share_emails = array();
        $sekprodi_owner = $this->upload_to_drive->find_connected_sekprodi_user_id();
        if ($sekprodi_owner) {
            $email = $this->upload_to_drive->get_drive_email($sekprodi_owner);
            if ($email) {
                $share_emails[] = $email;
            }
        }
        $upload_opts = $this->upload_to_drive->default_options(array('SIAP', 'Skripsi', 'sidang'), $share_emails);

        foreach ($berkas_list as $berkas) {
            $field_name = 'file_berkas_' . $berkas->id;
            if (empty($_FILES[$field_name]['name'])) {
                continue;
            }
            $upload = $this->upload_to_drive->handle_upload($field_name, $upload_opts);
            if (empty($upload['ok'])) {
                $this->session->set_flashdata('error', (isset($upload['message']) ? $upload['message'] : 'Gagal upload.') . ' (' . $berkas->nama_berkas . ')');
                redirect('Pendaftaran_sidang_skripsi');
            }
            $existing = $this->Skripsi_berkas_upload_model->get_by_pendaftaran_berkas($pendaftaran_id, $berkas->id);
            $row = array(
                'file_path' => $upload['webViewLink'],
                'file_name' => !empty($upload['file_name']) ? $upload['file_name'] : $berkas->nama_berkas,
                'file_size' => null
            );
            if ($existing) {
                $this->Skripsi_berkas_upload_model->update($existing->id, $row);
            } else {
                $this->Skripsi_berkas_upload_model->create($row + array(
                    'id_pendaftaran_sidang' => $pendaftaran_id,
                    'id_berkas_sidang' => $berkas->id
                ));
            }
        }

        $this->load->helper(array('notifikasi', 'sekprodi_badge'));
        notify_sekprodi(
            'Pendaftaran Sidang Skripsi Baru',
            'Ada pendaftaran sidang skripsi baru yang menunggu verifikasi berkas.',
            site_url('Skripsi_verifikasi_berkas_sidang'),
            'skripsi_sidang'
        );
        $this->session->set_flashdata('success', 'Pendaftaran sidang berhasil dikirim. Menunggu persetujuan dari sekprodi.');
        redirect('Pendaftaran_sidang_skripsi');
    }
}


