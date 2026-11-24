<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Tagihan Berkas Controller (untuk Mahasiswa)
 * Mahasiswa dapat melihat tagihan dan upload file
 */
class Tagihan_berkas extends MY_Controller {

    public function __construct() {
        parent::__construct();
        $this->check_role('mahasiswa');
        
        $this->load->model('Tagihan_berkas_model');
        $this->load->helper('menu');
        $this->load->library('form_validation');
        $this->load->helper('file');
    }

    /**
     * Index - Daftar tagihan mahasiswa
     */
    public function index() {
        $id_mahasiswa = $this->session->userdata('ref_id');
        
        if (!$id_mahasiswa) {
            $this->session->set_flashdata('error', 'Data mahasiswa tidak ditemukan.');
            redirect('Dashboard');
        }
        
        $data['title'] = 'Tagihan Berkas';
        $data['page_title'] = 'Tagihan Berkas';
        $data['breadcrumb'] = array(
            array('label' => 'Tagihan Berkas', 'active' => true)
        );
        $data['sidebar_menu'] = generate_sidebar_menu('mahasiswa');
        
        // Get tagihan untuk mahasiswa ini
        $data['tagihan_list'] = $this->Tagihan_berkas_model->get_by_mahasiswa($id_mahasiswa, 1);
        
        // Get detail untuk setiap tagihan
        foreach ($data['tagihan_list'] as $tagihan) {
            $tagihan->detail = $this->Tagihan_berkas_model->get_detail_by_mahasiswa_tagihan($tagihan->id, $id_mahasiswa);
        }
        
        $data['content'] = $this->load->view('tagihan_berkas/index', $data, true);
        $this->load->view('layouts/main', $data);
    }

    /**
     * Detail tagihan
     */
    public function detail($id) {
        $id_mahasiswa = $this->session->userdata('ref_id');
        
        if (!$id_mahasiswa) {
            $this->session->set_flashdata('error', 'Data mahasiswa tidak ditemukan.');
            redirect('Dashboard');
        }
        
        $tagihan = $this->Tagihan_berkas_model->get_by_id($id);
        
        if (!$tagihan) {
            $this->session->set_flashdata('error', 'Tagihan tidak ditemukan.');
            redirect('Tagihan_berkas');
        }
        
        // Cek apakah tagihan untuk mahasiswa ini
        $detail = $this->Tagihan_berkas_model->get_detail_by_mahasiswa_tagihan($id, $id_mahasiswa);
        
        if (!$detail) {
            $this->session->set_flashdata('error', 'Anda tidak memiliki akses ke tagihan ini.');
            redirect('Tagihan_berkas');
        }
        
        $data['title'] = 'Detail Tagihan Berkas';
        $data['page_title'] = 'Detail Tagihan Berkas';
        $data['breadcrumb'] = array(
            array('label' => 'Tagihan Berkas', 'url' => site_url('Tagihan_berkas')),
            array('label' => 'Detail', 'active' => true)
        );
        $data['sidebar_menu'] = generate_sidebar_menu('mahasiswa');
        
        $data['tagihan'] = $tagihan;
        $data['detail'] = $detail;
        
        $data['content'] = $this->load->view('tagihan_berkas/detail', $data, true);
        $this->load->view('layouts/main', $data);
    }

    /**
     * Upload file untuk tagihan softfile
     */
    public function upload($id) {
        $id_mahasiswa = $this->session->userdata('ref_id');
        
        if (!$id_mahasiswa) {
            $this->session->set_flashdata('error', 'Data mahasiswa tidak ditemukan.');
            redirect('Dashboard');
        }
        
        $tagihan = $this->Tagihan_berkas_model->get_by_id($id);
        
        if (!$tagihan) {
            $this->session->set_flashdata('error', 'Tagihan tidak ditemukan.');
            redirect('Tagihan_berkas');
        }
        
        // Cek apakah tagihan softfile
        if ($tagihan->is_hardcopy == 1) {
            $this->session->set_flashdata('error', 'Tagihan ini adalah hardcopy, tidak perlu upload file.');
            redirect('Tagihan_berkas/detail/' . $id);
        }
        
        // Cek apakah tagihan untuk mahasiswa ini
        $detail = $this->Tagihan_berkas_model->get_detail_by_mahasiswa_tagihan($id, $id_mahasiswa);
        
        if (!$detail) {
            $this->session->set_flashdata('error', 'Anda tidak memiliki akses ke tagihan ini.');
            redirect('Tagihan_berkas');
        }
        
        if ($this->input->method() === 'post') {
            $this->load->library('Upload_to_drive');
            $this->load->helper('google_drive');

            // Tagihan Berkas → Drive institusi (admin), folder khusus Tagihan_Berkas
            require_google_drive_or_redirect(null, 'Tagihan_berkas/detail/' . $id);

            $folder_name = !empty($tagihan->nama_tagihan) ? $tagihan->nama_tagihan : ('tagihan_' . $id);
            $nim = $this->session->userdata('username');

            $upload = $this->upload_to_drive->handle_upload('file_berkas', array(
                'folder_parts' => array('SIAP', 'Tagihan_Berkas', $folder_name, $nim ?: 'mahasiswa'),
                'share_anyone' => true,
                'allowed_types' => '*',
                'file_label' => $folder_name
            ));

            if (empty($upload['ok'])) {
                $this->session->set_flashdata('error', isset($upload['message']) ? $upload['message'] : 'Gagal upload ke Google Drive institusi.');
                redirect('Tagihan_berkas/detail/' . $id);
            }

            // Hapus file lokal lama jika ada (migrasi dari penyimpanan server)
            if (!empty($detail->file_path) && is_file($detail->file_path)) {
                @unlink($detail->file_path);
            }

            $update_data = array(
                'file_path' => $upload['webViewLink'],
                'file_name' => $upload['file_name'],
                'file_size' => isset($_FILES['file_berkas']['size']) ? round($_FILES['file_berkas']['size'] / 1024) : null,
                'status' => 'sudah',
                'uploaded_at' => date('Y-m-d H:i:s')
            );

            $this->Tagihan_berkas_model->update_detail($detail->id, $update_data);
            $this->session->set_flashdata('success', 'File berhasil diunggah ke Google Drive institusi.');
            redirect('Tagihan_berkas/detail/' . $id);
        } else {
            redirect('Tagihan_berkas/detail/' . $id);
        }
    }

    /**
     * Download file
     */
    public function download($id) {
        $id_mahasiswa = $this->session->userdata('ref_id');
        
        if (!$id_mahasiswa) {
            $this->session->set_flashdata('error', 'Data mahasiswa tidak ditemukan.');
            redirect('Dashboard');
        }
        
        $tagihan = $this->Tagihan_berkas_model->get_by_id($id);
        
        if (!$tagihan) {
            $this->session->set_flashdata('error', 'Tagihan tidak ditemukan.');
            redirect('Tagihan_berkas');
        }
        
        $detail = $this->Tagihan_berkas_model->get_detail_by_mahasiswa_tagihan($id, $id_mahasiswa);
        
        if (!$detail || empty($detail->file_path)) {
            $this->session->set_flashdata('error', 'File tidak ditemukan.');
            redirect('Tagihan_berkas/detail/' . $id);
        }

        // File baru = link Google Drive; file lama = path lokal
        $this->load->helper('google_drive');
        if (is_valid_google_drive_link($detail->file_path) || preg_match('#^https?://#i', $detail->file_path)) {
            redirect(normalize_google_drive_link($detail->file_path));
        }

        if (!file_exists($detail->file_path)) {
            $this->session->set_flashdata('error', 'File tidak ditemukan.');
            redirect('Tagihan_berkas/detail/' . $id);
        }
        
        $this->load->helper('download');
        force_download($detail->file_name, file_get_contents($detail->file_path));
    }

    /**
     * Delete file
     */
    public function delete_file($id) {
        $id_mahasiswa = $this->session->userdata('ref_id');
        
        if (!$id_mahasiswa) {
            $this->session->set_flashdata('error', 'Data mahasiswa tidak ditemukan.');
            redirect('Dashboard');
        }
        
        $tagihan = $this->Tagihan_berkas_model->get_by_id($id);
        
        if (!$tagihan) {
            $this->session->set_flashdata('error', 'Tagihan tidak ditemukan.');
            redirect('Tagihan_berkas');
        }
        
        $detail = $this->Tagihan_berkas_model->get_detail_by_mahasiswa_tagihan($id, $id_mahasiswa);
        
        if (!$detail) {
            $this->session->set_flashdata('error', 'Anda tidak memiliki akses ke tagihan ini.');
            redirect('Tagihan_berkas');
        }
        
        // Hapus file
        if ($detail->file_path && file_exists($detail->file_path)) {
            unlink($detail->file_path);
        }
        
        // Update detail
        $update_data = array(
            'file_path' => null,
            'file_name' => null,
            'file_size' => null,
            'status' => 'belum',
            'uploaded_at' => null
        );
        
        $this->Tagihan_berkas_model->update_detail($detail->id, $update_data);
        
        $this->session->set_flashdata('success', 'File berhasil dihapus.');
        redirect('Tagihan_berkas/detail/' . $id);
    }
}

