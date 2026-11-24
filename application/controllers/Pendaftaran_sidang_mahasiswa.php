<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Pendaftaran_sidang_mahasiswa Controller
 * Menangani pendaftaran sidang oleh mahasiswa
 */
class Pendaftaran_sidang_mahasiswa extends MY_Controller {

    public function __construct() {
        parent::__construct();
        $this->check_role('mahasiswa');
        $this->load->model('Skripsi_pendaftaran_sidang_model');
        $this->load->model('Skripsi_berkas_upload_model');
        $this->load->model('Skripsi_berkas_sidang_model');
        $this->load->model('Skripsi_model');
        $this->load->model('Tahun_akademik_model');
        $this->load->library('form_validation');
    }

    /**
     * Index - form pendaftaran sidang
     */
    public function index() {
        $id_mahasiswa = $this->session->userdata('ref_id');
        $mahasiswa = $this->db->get_where('mahasiswa', array('id' => $id_mahasiswa))->row();
        
        if (!$mahasiswa) {
            $this->session->set_flashdata('error', 'Data mahasiswa tidak ditemukan.');
            redirect('Dashboard');
        }

        $tahun_aktif = $this->Tahun_akademik_model->get_aktif();
        if (!$tahun_aktif) {
            $this->session->set_flashdata('error', 'Tidak ada tahun akademik aktif.');
            redirect('Dashboard');
        }

        // Cek apakah mahasiswa sudah terdaftar skripsi
        $skripsi = $this->Skripsi_model->get_by_mahasiswa($mahasiswa->id, $tahun_aktif->id);
        if (!$skripsi) {
            $this->session->set_flashdata('error', 'Anda belum terdaftar untuk skripsi.');
            redirect('Dashboard');
        }

        // Cek apakah sudah layak sidang (pembimbing sudah ACC)
        if ($skripsi->status != 'layak_sidang' || ($skripsi->acc_pembimbing_1 != 1 || (!empty($skripsi->id_pembimbing_2) && $skripsi->acc_pembimbing_2 != 1))) {
            $this->session->set_flashdata('error', 'Anda belum layak sidang. Pastikan pembimbing sudah memberikan ACC layak sidang.');
            redirect('Dashboard');
        }

        // Cek apakah sudah ada pendaftaran
        $pendaftaran = $this->Skripsi_pendaftaran_sidang_model->get_by_mahasiswa_skripsi($skripsi->id);
        
        // Ambil berkas yang sudah diupload
        $berkas_uploaded = array();
        if ($pendaftaran) {
            $berkas_uploaded = $this->Skripsi_berkas_upload_model->get_by_pendaftaran($pendaftaran->id);
        }

        // Ambil daftar berkas yang diperlukan
        $berkas_list = $this->Skripsi_berkas_sidang_model->get_all(true);

        $data = array(
            'title' => 'Pendaftaran Sidang Skripsi',
            'page_title' => 'Pendaftaran Sidang Skripsi',
            'breadcrumb' => array(
                array('label' => 'Skripsi', 'url' => site_url('Skripsi_pengajuan')),
                array('label' => 'Pendaftaran Sidang', 'active' => true)
            ),
            'sidebar_menu' => generate_sidebar_menu('mahasiswa'),
            'pendaftaran' => $pendaftaran,
            'berkas_list' => $berkas_list,
            'berkas_uploaded' => $berkas_uploaded,
            'skripsi' => $skripsi,
            'tahun_aktif' => $tahun_aktif
        );

        $data['content'] = $this->load->view('pendaftaran_sidang_mahasiswa/index', $data, true);
        $this->load->view('layouts/main', $data);
    }

    /**
     * Submit pendaftaran sidang
     */
    public function submit() {
        $id_mahasiswa = $this->session->userdata('ref_id');
        $mahasiswa = $this->db->get_where('mahasiswa', array('id' => $id_mahasiswa))->row();
        
        if (!$mahasiswa) {
            $this->session->set_flashdata('error', 'Data mahasiswa tidak ditemukan.');
            redirect('Dashboard');
        }

        $tahun_aktif = $this->Tahun_akademik_model->get_aktif();
        if (!$tahun_aktif) {
            $this->session->set_flashdata('error', 'Tidak ada tahun akademik aktif.');
            redirect('Dashboard');
        }

        $skripsi = $this->Skripsi_model->get_by_mahasiswa($mahasiswa->id, $tahun_aktif->id);
        if (!$skripsi) {
            $this->session->set_flashdata('error', 'Anda belum terdaftar untuk skripsi.');
            redirect('Dashboard');
        }

        // Cek apakah sudah ada pendaftaran
        $pendaftaran = $this->Skripsi_pendaftaran_sidang_model->get_by_mahasiswa_skripsi($skripsi->id);
        
        // Ambil daftar berkas wajib
        $berkas_list = $this->Skripsi_berkas_sidang_model->get_all(true);
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
                        $errors[] = "Berkas wajib belum diisi";
                        break;
                    }
                } else {
                    $errors[] = "Berkas wajib belum diisi";
                    break;
                }
            }
        }

        if (!empty($errors)) {
            $this->session->set_flashdata('error', implode(', ', $errors));
            redirect('Pendaftaran_sidang_mahasiswa');
        }

        // Buat atau update pendaftaran
        if (!$pendaftaran) {
            $pendaftaran_data = array(
                'id_mahasiswa_skripsi' => $skripsi->id,
                'id_tahun_akademik' => $tahun_aktif->id,
                'status' => 'menunggu'
            );
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
                redirect('Pendaftaran_sidang_mahasiswa');
            }
            $existing = $this->Skripsi_berkas_upload_model->get_by_pendaftaran_berkas($pendaftaran_id, $berkas->id);
            $row = array(
                'file_path' => $upload['webViewLink'],
                'file_name' => !empty($upload['file_name']) ? $upload['file_name'] : $berkas->nama_berkas,
                'status' => 'pending'
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
        redirect('Pendaftaran_sidang_mahasiswa');
    }
}

