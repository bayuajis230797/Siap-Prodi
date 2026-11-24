<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Pendaftaran_jadwal_skripsi Controller
 * Pendaftaran Sidang Tahap 1 (Jadwal) - Upload berkas untuk menentukan tanggal, jam, ruangan sidang.
 * Syarat: Sekprodi sudah plot Pembimbing 1 (Pembimbing 2 opsional).
 */
class Pendaftaran_jadwal_skripsi extends MY_Controller {

    const TAHAP = 'jadwal';

    public function __construct() {
        parent::__construct();
        $this->check_role('mahasiswa');
        $this->load->model('Skripsi_pendaftaran_sidang_model');
        $this->load->model('Skripsi_berkas_upload_model');
        $this->load->model('Skripsi_berkas_sidang_model');
        $this->load->model('Skripsi_model');
        $this->load->model('Tahun_akademik_model');
        $this->load->library('form_validation');
        $this->load->helper('file');
    }

    /**
     * Index - form pendaftaran jadwal sidang (tahap 1)
     */
    public function index() {
        $id_mahasiswa = $this->session->userdata('ref_id');
        $tahun_aktif = $this->Tahun_akademik_model->get_aktif();
        
        if (!$tahun_aktif) {
            $this->session->set_flashdata('error', 'Tidak ada tahun akademik aktif.');
            redirect('Halaman_utama');
        }

        $skripsi = $this->Skripsi_model->get_by_mahasiswa($id_mahasiswa, $tahun_aktif->id);
        if (!$skripsi) {
            $this->session->set_flashdata('error', 'Anda belum terdaftar sebagai peserta Skripsi.');
            redirect('Halaman_utama');
        }

        // Syarat tahap 1: Pembimbing 1 sudah diplot
        $pembimbing_1_sudah_diplot = !empty($skripsi->id_pembimbing_1);

        if (!$pembimbing_1_sudah_diplot) {
            $this->session->set_flashdata('error', 'Anda belum dapat mendaftar jadwal sidang. Sekprodi belum menetapkan Pembimbing 1 untuk Anda.');
            redirect('Halaman_utama');
        }

        // Ambil judul skripsi
        $this->load->model('Skripsi_pengajuan_model');
        $this->load->model('Skripsi_perubahan_judul_model');
        $judul_skripsi = 'Belum ada judul';
        $pengajuan = $this->Skripsi_pengajuan_model->get_latest_by_mahasiswa($id_mahasiswa, $tahun_aktif->id);
        if ($pengajuan) {
            $perubahan_approved = $this->Skripsi_perubahan_judul_model->get_by_pengajuan($pengajuan->id);
            if ($perubahan_approved && isset($perubahan_approved->status_approve_sekprodi) && $perubahan_approved->status_approve_sekprodi === 'acc') {
                $judul_skripsi = $perubahan_approved->judul_baru;
            } elseif (isset($pengajuan->judul_disetujui) && !empty($pengajuan->judul_disetujui)) {
                $judul_skripsi = $pengajuan->judul_disetujui;
            } elseif (isset($pengajuan->judul_pengajuan) && !empty($pengajuan->judul_pengajuan)) {
                $judul_skripsi = $pengajuan->judul_pengajuan;
            }
        }
        if ($judul_skripsi == 'Belum ada judul' && isset($skripsi->judul_skripsi) && !empty($skripsi->judul_skripsi)) {
            $judul_skripsi = $skripsi->judul_skripsi;
        }

        // Cek pendaftaran tahap jadwal
        $pendaftaran = $this->Skripsi_pendaftaran_sidang_model->get_by_mahasiswa_skripsi_tahap($skripsi->id, self::TAHAP);
        $berkas_uploaded = array();
        if ($pendaftaran) {
            $berkas_uploaded = $this->Skripsi_berkas_upload_model->get_by_pendaftaran($pendaftaran->id);
        }

        // Berkas tahap jadwal
        $berkas_list = $this->Skripsi_berkas_sidang_model->get_by_tahap(self::TAHAP, true);
        if (empty($berkas_list) && $this->db->field_exists('tahap', 'skripsi_berkas_sidang')) {
            $berkas_list = array();
        } elseif (empty($berkas_list)) {
            $berkas_list = $this->Skripsi_berkas_sidang_model->get_all(true);
        }

        $data = array(
            'title' => 'Pendaftaran Jadwal Sidang',
            'page_title' => 'Pendaftaran Jadwal Sidang (Tahap 1)',
            'breadcrumb' => array(
                array('label' => 'Skripsi', 'url' => '#'),
                array('label' => 'Pendaftaran Jadwal Sidang', 'active' => true)
            ),
            'sidebar_menu' => generate_sidebar_menu('mahasiswa'),
            'pendaftaran' => $pendaftaran,
            'berkas_list' => $berkas_list,
            'berkas_uploaded' => $berkas_uploaded,
            'skripsi' => $skripsi,
            'tahun_aktif' => $tahun_aktif,
            'judul_skripsi' => $judul_skripsi
        );

        $data['content'] = $this->load->view('mahasiswa/pendaftaran_jadwal_skripsi/index', $data, true);
        $this->load->view('layouts/main', $data);
    }

    /**
     * Submit pendaftaran jadwal sidang
     */
    public function submit() {
        $id_mahasiswa = $this->session->userdata('ref_id');
        $tahun_aktif = $this->Tahun_akademik_model->get_aktif();
        
        if (!$tahun_aktif) {
            $this->session->set_flashdata('error', 'Tidak ada tahun akademik aktif.');
            redirect('Halaman_utama');
        }

        $skripsi = $this->Skripsi_model->get_by_mahasiswa($id_mahasiswa, $tahun_aktif->id);
        if (!$skripsi) {
            $this->session->set_flashdata('error', 'Anda belum terdaftar sebagai peserta Skripsi.');
            redirect('Halaman_utama');
        }

        if (empty($skripsi->id_pembimbing_1)) {
            $this->session->set_flashdata('error', 'Pembimbing 1 belum ditetapkan.');
            redirect('Pendaftaran_jadwal_skripsi');
        }

        $pendaftaran = $this->Skripsi_pendaftaran_sidang_model->get_by_mahasiswa_skripsi_tahap($skripsi->id, self::TAHAP);

        $berkas_list = $this->Skripsi_berkas_sidang_model->get_by_tahap(self::TAHAP, true);
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

        $errors = array();
        foreach ($berkas_wajib as $id_berkas) {
            $field_name = 'file_berkas_' . $id_berkas;
            if (empty($_FILES[$field_name]['name'])) {
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
            redirect('Pendaftaran_jadwal_skripsi');
        }

        $pendaftaran_data = array(
            'id_mahasiswa_skripsi' => $skripsi->id,
            'id_tahun_akademik' => $tahun_aktif->id,
            'status' => 'menunggu'
        );
        if ($this->db->field_exists('tahap', 'skripsi_pendaftaran_sidang')) {
            $pendaftaran_data['tahap'] = self::TAHAP;
        }

        if (!$pendaftaran) {
            $pendaftaran_id = $this->Skripsi_pendaftaran_sidang_model->create($pendaftaran_data);
        } else {
            $pendaftaran_id = $pendaftaran->id;
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
                redirect('Pendaftaran_jadwal_skripsi');
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

        $this->session->set_flashdata('success', 'Pendaftaran jadwal sidang berhasil dikirim. Menunggu verifikasi dari sekprodi.');
        redirect('Pendaftaran_jadwal_skripsi');
    }
}
