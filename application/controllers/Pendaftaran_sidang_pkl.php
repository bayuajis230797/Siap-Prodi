<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Pendaftaran_sidang_pkl Controller
 * Menangani pendaftaran sidang PKL oleh mahasiswa
 */
class Pendaftaran_sidang_pkl extends MY_Controller {

    public function __construct() {
        parent::__construct();
        $this->check_role('mahasiswa');
        $this->load->model('Pkl_pendaftaran_sidang_model');
        $this->load->model('Pkl_berkas_sidang_upload_model');
        $this->load->model('Pkl_berkas_sidang_model');
        $this->load->model('Pkl_pengajuan_model');
        $this->load->model('Mahasiswa_pkl_model');
        $this->load->model('Tahun_akademik_model');
        $this->load->library('form_validation');
        $this->load->helper('google_drive');
        $this->load->helper('notifikasi');
    }

    /**
     * Index - form pendaftaran sidang PKL
     */
    public function index() {
        $id_mahasiswa = $this->session->userdata('ref_id');
        $tahun_aktif = $this->Tahun_akademik_model->get_aktif();
        
        if (!$tahun_aktif) {
            $this->session->set_flashdata('error', 'Tidak ada tahun akademik aktif.');
            redirect('Halaman_utama');
        }

        if (!$this->Mahasiswa_pkl_model->is_registered($id_mahasiswa, $tahun_aktif->id)) {
            $this->session->set_flashdata('error', 'Anda belum terdaftar sebagai peserta PKL.');
            redirect('Halaman_utama');
        }

        $pengajuan = $this->Pkl_pengajuan_model->get_by_mahasiswa($id_mahasiswa, $tahun_aktif->id);
        if (!$pengajuan || !$pengajuan->is_layak_sidang || $pengajuan->status_acc_judul !== 'acc') {
            $this->session->set_flashdata('error', 'Anda belum layak sidang. Pastikan pembimbing sudah memberikan ACC layak sidang.');
            redirect('Halaman_utama');
        }

        $pendaftaran = $this->Pkl_pendaftaran_sidang_model->get_by_mahasiswa($id_mahasiswa, $tahun_aktif->id);
        
        $berkas_uploaded = array();
        if ($pendaftaran) {
            $berkas_uploaded = $this->Pkl_berkas_sidang_upload_model->get_by_pendaftaran($pendaftaran->id);
        }

        $berkas_list = $this->Pkl_berkas_sidang_model->get_all(true);

        $data = array(
            'title' => 'Pendaftaran Sidang PKL',
            'page_title' => 'Pendaftaran Sidang PKL',
            'breadcrumb' => array(
                array('label' => 'PKL', 'url' => '#'),
                array('label' => 'Pendaftaran Sidang', 'active' => true)
            ),
            'sidebar_menu' => generate_sidebar_menu('mahasiswa'),
            'pendaftaran' => $pendaftaran,
            'berkas_list' => $berkas_list,
            'berkas_uploaded' => $berkas_uploaded,
            'pengajuan' => $pengajuan,
            'tahun_aktif' => $tahun_aktif
        );

        $data['content'] = $this->load->view('mahasiswa/pendaftaran_sidang_pkl/index', $data, true);
        $this->load->view('layouts/main', $data);
    }

  /**
     * Submit pendaftaran sidang PKL
     */
    public function submit() {
        $id_mahasiswa = $this->session->userdata('ref_id');
        $tahun_aktif = $this->Tahun_akademik_model->get_aktif();
        
        if (!$tahun_aktif) {
            $this->session->set_flashdata('error', 'Tidak ada tahun akademik aktif.');
            redirect('Halaman_utama');
        }

        $pengajuan = $this->Pkl_pengajuan_model->get_by_mahasiswa($id_mahasiswa, $tahun_aktif->id);
        if (!$pengajuan || !$pengajuan->is_layak_sidang || $pengajuan->status_acc_judul !== 'acc') {
            $this->session->set_flashdata('error', 'Anda belum layak sidang.');
            redirect('Halaman_utama');
        }

        $pendaftaran = $this->Pkl_pendaftaran_sidang_model->get_by_mahasiswa($id_mahasiswa, $tahun_aktif->id);
        $berkas_list = $this->Pkl_berkas_sidang_model->get_all(true);

        $errors = array();
        foreach ($berkas_list as $berkas) {
            if (!$berkas->wajib) {
                continue;
            }
            $field = 'file_berkas_' . $berkas->id;
            if (!empty($_FILES[$field]['name'])) {
                continue;
            }
            $existing = null;
            if ($pendaftaran) {
                $existing = $this->Pkl_berkas_sidang_upload_model->get_by_pendaftaran_berkas($pendaftaran->id, $berkas->id);
            }
            if (!$existing || !is_valid_google_drive_link((string) $existing->file_path)) {
                $errors[] = 'Berkas wajib "' . $berkas->nama_berkas . '" belum diunggah.';
            }
        }

        if (!empty($errors)) {
            $this->session->set_flashdata('error', implode(' ', $errors));
            redirect('Pendaftaran_sidang_pkl');
        }

        $needs_upload = false;
        foreach ($berkas_list as $berkas) {
            if (!empty($_FILES['file_berkas_' . $berkas->id]['name'])) {
                $needs_upload = true;
                break;
            }
        }

        if ($needs_upload) {
            require_google_drive_or_redirect();
            $this->load->library('Upload_to_drive');
        }

        if (!$pendaftaran) {
            $pendaftaran_id = $this->Pkl_pendaftaran_sidang_model->create(array(
                'id_mahasiswa' => $id_mahasiswa,
                'id_tahun_akademik' => $tahun_aktif->id,
                'status' => 'menunggu'
            ));
        } else {
            $pendaftaran_id = $pendaftaran->id;
            if ($pendaftaran->status == 'ditolak') {
                $this->Pkl_pendaftaran_sidang_model->update($pendaftaran_id, array('status' => 'menunggu'));
            }
        }

        $share_emails = array();
        if ($needs_upload) {
            if (!empty($pengajuan->id_pembimbing)) {
                $email = $this->upload_to_drive->get_drive_email_by_role_ref('dosen', (int) $pengajuan->id_pembimbing);
                if ($email) {
                    $share_emails[] = $email;
                }
            }
        }
        $upload_opts = $needs_upload
            ? $this->upload_to_drive->default_options(array('SIAP', 'PKL', 'sidang'), $share_emails)
            : array();

        foreach ($berkas_list as $berkas) {
            $field_name = 'file_berkas_' . $berkas->id;
            if (empty($_FILES[$field_name]['name'])) {
                continue;
            }

            $upload = $this->upload_to_drive->handle_upload($field_name, $upload_opts);
            if (empty($upload['ok'])) {
                $this->session->set_flashdata('error', isset($upload['message']) ? $upload['message'] : 'Gagal upload berkas.');
                redirect('Pendaftaran_sidang_pkl');
            }

            $existing = $this->Pkl_berkas_sidang_upload_model->get_by_pendaftaran_berkas($pendaftaran_id, $berkas->id);
            $row = array(
                'file_path' => $upload['webViewLink'],
                'file_name' => !empty($upload['file_name']) ? $upload['file_name'] : $berkas->nama_berkas,
                'file_size' => isset($_FILES[$field_name]['size']) ? round($_FILES[$field_name]['size'] / 1024) : null
            );
            if ($existing) {
                $this->Pkl_berkas_sidang_upload_model->update($existing->id, $row);
            } else {
                $row['id_pendaftaran_sidang'] = $pendaftaran_id;
                $row['id_berkas_sidang'] = $berkas->id;
                $this->Pkl_berkas_sidang_upload_model->create($row);
            }
        }

        $this->load->helper(array('notifikasi', 'sekprodi_badge'));
        notify_sekprodi(
            'Pendaftaran Sidang PKL Baru',
            'Ada pendaftaran sidang PKL baru yang menunggu verifikasi berkas.',
            site_url('Pkl_verifikasi_berkas_sidang'),
            'pkl_sidang'
        );

        $this->session->set_flashdata('success', 'Pendaftaran sidang berhasil dikirim. Menunggu persetujuan dari sekprodi.');
        redirect('Pendaftaran_sidang_pkl');
    }
}