<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Pkl_klaim_nilai Controller
 * Menangani klaim nilai PKL oleh mahasiswa
 */
class Pkl_klaim_nilai extends MY_Controller {

    public function __construct() {
        parent::__construct();
        $this->check_role('mahasiswa');
        $this->load->model('Pkl_klaim_nilai_model');
        $this->load->model('Pkl_berkas_klaim_upload_model');
        $this->load->model('Pkl_berkas_klaim_nilai_model');
        $this->load->model('Pkl_nilai_rekap_model');
        $this->load->model('Tahun_akademik_model');
        $this->load->library('form_validation');
        $this->load->helper('file');
        $this->load->helper('google_drive');
        $this->load->helper('notifikasi');
    }

    /**
     * Index - form klaim nilai PKL
     */
    public function index() {
        $id_mahasiswa = $this->session->userdata('ref_id');
        $tahun_aktif = $this->Tahun_akademik_model->get_aktif();
        
        if (!$tahun_aktif) {
            $this->session->set_flashdata('error', 'Tidak ada tahun akademik aktif.');
            redirect('Halaman_utama');
        }

        // Cek apakah sudah ada penilaian dari penguji
        $this->load->model('Pkl_penilaian_model');
        $penilaian_penguji = $this->Pkl_penilaian_model->get_by_mahasiswa_tipe($id_mahasiswa, $tahun_aktif->id, 'penguji');
        
        if (!$penilaian_penguji) {
            $this->session->set_flashdata('error', 'Anda belum memiliki penilaian dari penguji. Pastikan sidang sudah dilakukan dan penguji sudah memberikan penilaian.');
            redirect('Halaman_utama');
        }

        // Cek apakah sudah ada klaim
        $klaim = $this->Pkl_klaim_nilai_model->get_by_mahasiswa($id_mahasiswa, $tahun_aktif->id);
        
        // Ambil berkas yang sudah diupload
        $berkas_uploaded = array();
        if ($klaim) {
            $berkas_uploaded = $this->Pkl_berkas_klaim_upload_model->get_by_klaim($klaim->id);
        }

        // Ambil daftar berkas yang diperlukan
        $berkas_list = $this->Pkl_berkas_klaim_nilai_model->get_all(true);

        // Ambil nilai rekap jika sudah ada
        $nilai_rekap = $this->Pkl_nilai_rekap_model->get_by_mahasiswa($id_mahasiswa, $tahun_aktif->id);

        $this->load->library('Bimbingan_pasca_service');
        $pasca_status = $this->bimbingan_pasca_service->pkl_klaim_status($id_mahasiswa, $tahun_aktif->id);

        $data = array(
            'title' => 'Klaim Nilai PKL',
            'page_title' => 'Klaim Nilai PKL',
            'breadcrumb' => array(
                array('label' => 'PKL', 'url' => '#'),
                array('label' => 'Klaim Nilai', 'active' => true)
            ),
            'sidebar_menu' => generate_sidebar_menu('mahasiswa'),
            'klaim' => $klaim,
            'berkas_list' => $berkas_list,
            'berkas_uploaded' => $berkas_uploaded,
            'nilai_rekap' => $nilai_rekap,
            'tahun_aktif' => $tahun_aktif,
            'pasca_status' => $pasca_status,
            'boleh_klaim' => !empty($pasca_status['ok']),
        );

        $data['content'] = $this->load->view('mahasiswa/pkl_klaim_nilai/index', $data, true);
        $this->load->view('layouts/main', $data);
    }

    /**
     * Submit klaim nilai PKL
     */
    public function submit() {
        $id_mahasiswa = $this->session->userdata('ref_id');
        $tahun_aktif = $this->Tahun_akademik_model->get_aktif();
        
        if (!$tahun_aktif) {
            $this->session->set_flashdata('error', 'Tidak ada tahun akademik aktif.');
            redirect('Halaman_utama');
        }

        // Cek apakah sudah ada penilaian dari penguji
        $this->load->model('Pkl_penilaian_model');
        $penilaian_penguji = $this->Pkl_penilaian_model->get_by_mahasiswa_tipe($id_mahasiswa, $tahun_aktif->id, 'penguji');
        
        if (!$penilaian_penguji) {
            $this->session->set_flashdata('error', 'Anda belum memiliki penilaian dari penguji.');
            redirect('Halaman_utama');
        }

        $this->load->library('Bimbingan_pasca_service');
        $pasca_status = $this->bimbingan_pasca_service->pkl_klaim_status($id_mahasiswa, $tahun_aktif->id);
        if (empty($pasca_status['ok'])) {
            $this->session->set_flashdata('error', isset($pasca_status['message']) ? $pasca_status['message'] : 'Menunggu penguji menyatakan layak klaim nilai.');
            redirect('Pkl_klaim_nilai');
        }

        // Cek apakah sudah ada klaim
        $klaim = $this->Pkl_klaim_nilai_model->get_by_mahasiswa($id_mahasiswa, $tahun_aktif->id);
        
        $berkas_list = $this->Pkl_berkas_klaim_nilai_model->get_all(true);

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
            if ($klaim) {
                $existing = $this->Pkl_berkas_klaim_upload_model->get_by_klaim_berkas($klaim->id, $berkas->id);
            }
            if (!$existing || !is_valid_google_drive_link((string) $existing->file_path)) {
                $errors[] = 'Berkas wajib "' . $berkas->nama_berkas . '" belum diunggah.';
            }
        }

        if (!empty($errors)) {
            $this->session->set_flashdata('error', implode(' ', $errors));
            redirect('Pkl_klaim_nilai');
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

        // Buat atau update klaim
        if (!$klaim) {
            $klaim_data = array(
                'id_mahasiswa' => $id_mahasiswa,
                'id_tahun_akademik' => $tahun_aktif->id,
                'status' => 'menunggu'
            );
            $klaim_id = $this->Pkl_klaim_nilai_model->create($klaim_data);
        } else {
            $klaim_id = $klaim->id;
            // Update status jika ditolak sebelumnya
            if ($klaim->status == 'ditolak') {
                $this->Pkl_klaim_nilai_model->update($klaim_id, array('status' => 'menunggu'));
            }
        }

        $upload_opts = $needs_upload
            ? $this->upload_to_drive->default_options(array('SIAP', 'PKL', 'klaim_nilai'), array())
            : array();

        foreach ($berkas_list as $berkas) {
            $field_name = 'file_berkas_' . $berkas->id;
            if (empty($_FILES[$field_name]['name'])) {
                continue;
            }

            $upload = $this->upload_to_drive->handle_upload($field_name, $upload_opts);
            if (empty($upload['ok'])) {
                $this->session->set_flashdata('error', isset($upload['message']) ? $upload['message'] : 'Gagal upload berkas.');
                redirect('Pkl_klaim_nilai');
            }

            $existing = $this->Pkl_berkas_klaim_upload_model->get_by_klaim_berkas($klaim_id, $berkas->id);
            $row = array(
                'file_path' => $upload['webViewLink'],
                'file_name' => !empty($upload['file_name']) ? $upload['file_name'] : $berkas->nama_berkas,
                'file_size' => isset($_FILES[$field_name]['size']) ? round($_FILES[$field_name]['size'] / 1024) : null
            );
            if ($existing) {
                $this->Pkl_berkas_klaim_upload_model->update($existing->id, $row);
            } else {
                $row['id_klaim_nilai'] = $klaim_id;
                $row['id_berkas_klaim_nilai'] = $berkas->id;
                $this->Pkl_berkas_klaim_upload_model->create($row);
            }
        }

        $this->load->helper(array('notifikasi', 'sekprodi_badge'));
        notify_sekprodi(
            'Klaim Nilai PKL Baru',
            'Ada klaim nilai PKL baru yang menunggu verifikasi.',
            site_url('Pkl_verifikasi_klaim'),
            'pkl_klaim'
        );

        $this->session->set_flashdata('success', 'Klaim nilai berhasil dikirim. Menunggu persetujuan dari sekprodi.');
        redirect('Pkl_klaim_nilai');
    }
}





