<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Absensi extends MY_Controller {

    public function __construct() {
        parent::__construct();
        $this->load->model('Kegiatan_model');
        $this->load->model('Absensi_model');
        $this->load->model('Mahasiswa_model');
        $this->load->helper('gps');
        $this->load->library('Mobile_absensi_service');
    }

    /**
     * Scan QR Code - Halaman scan untuk mahasiswa
     */
    public function scan($token = null) {
        // Cek apakah user sudah login
        $role = $this->session->userdata('role');
        $id_mahasiswa = $this->session->userdata('ref_id');
        
        if ($role !== 'mahasiswa' || !$id_mahasiswa) {
            $this->session->set_flashdata('error', 'Anda harus login sebagai mahasiswa untuk melakukan absensi.');
            redirect('Auth/login');
        }
        
        // Token di URL: tampilkan halaman scan (tidak proses absensi via GET)
        if ($token) {
            $data = array(
                'title' => 'Scan QR Code Absensi',
                'page_title' => 'Scan QR Code Absensi',
                'breadcrumb' => array(
                    array('label' => 'Absensi Kegiatan', 'url' => site_url('Absensi/kegiatan_saya')),
                    array('label' => 'Scan QR Code', 'active' => true)
                ),
                'sidebar_menu' => generate_sidebar_menu('mahasiswa'),
                'token' => $token,
            );
            $data['content'] = $this->load->view('mahasiswa/absensi/scan', $data, true);
            $this->load->view('layouts/main', $data);
            return;
        }
        
        // Tanpa token: halaman scan kosong
        $data = array(
            'title' => 'Scan QR Code Absensi',
            'page_title' => 'Scan QR Code Absensi',
            'breadcrumb' => array(
                array('label' => 'Absensi Kegiatan', 'url' => site_url('Absensi/kegiatan_saya')),
                array('label' => 'Scan QR Code', 'active' => true)
            ),
            'sidebar_menu' => generate_sidebar_menu('mahasiswa'),
            'token' => null,
        );
        
        $data['content'] = $this->load->view('mahasiswa/absensi/scan', $data, true);
        $this->load->view('layouts/main', $data);
    }

    /**
     * Process Scan - Proses absensi dari scan QR Code
     */
    public function process_scan($token) {
        $this->output->set_content_type('application/json');

        if ($this->input->method(TRUE) !== 'POST') {
            $this->output->set_status_header(405);
            echo json_encode(array(
                'success' => false,
                'message' => 'Method tidak diizinkan. Gunakan POST.',
            ));
            return;
        }

        $role = $this->session->userdata('role');
        $id_mahasiswa = (int) $this->session->userdata('ref_id');

        if ($role !== 'mahasiswa' || !$id_mahasiswa) {
            echo json_encode(array(
                'success' => false,
                'message' => 'Anda harus login sebagai mahasiswa.',
            ));
            return;
        }

        if (empty($token)) {
            echo json_encode(array(
                'success' => false,
                'message' => 'Token QR Code tidak valid.',
            ));
            return;
        }

        $out = $this->mobile_absensi_service->scan_kegiatan(
            $id_mahasiswa,
            $token,
            $this->input->post('latitude'),
            $this->input->post('longitude')
        );
        echo json_encode($out);
    }

    /**
     * Kegiatan Saya - Daftar kegiatan untuk mahasiswa
     */
    public function kegiatan_saya() {
        $this->check_role('mahasiswa');
        
        // Auto-update status semua kegiatan sebelum tampilkan
        $this->Kegiatan_model->auto_update_status();
        
        $id_mahasiswa = $this->session->userdata('ref_id');
        
        // Pagination
        $this->load->library('pagination');
        $config['base_url'] = site_url('Absensi/kegiatan_saya');
        $config['total_rows'] = $this->Kegiatan_model->count_kegiatan_by_mahasiswa($id_mahasiswa);
        $config['per_page'] = 10;
        $config['uri_segment'] = 3;
        
        $this->pagination->initialize($config);
        
        $page = $this->uri->segment(3) ? (int)$this->uri->segment(3) : 0;
        $kegiatan_list = $this->Kegiatan_model->get_kegiatan_by_mahasiswa($id_mahasiswa, $config['per_page'], $page);
        
        $data = array(
            'title' => 'Kegiatan Saya',
            'page_title' => 'Kegiatan Saya',
            'breadcrumb' => array(array('label' => 'Absensi Kegiatan', 'active' => true)),
            'sidebar_menu' => generate_sidebar_menu('mahasiswa'),
            'kegiatan_list' => $kegiatan_list,
            'pagination' => $this->pagination->create_links()
        );
        
        $data['content'] = $this->load->view('mahasiswa/absensi/kegiatan_saya', $data, true);
        $this->load->view('layouts/main', $data);
    }

    /**
     * Detail - Detail kegiatan untuk mahasiswa
     * Parameter bisa berupa ID kegiatan (numeric) atau token QR Code (string).
     * Untuk keamanan URL, link dari aplikasi akan menggunakan token.
     */
    public function detail($key) {
        $this->check_role('mahasiswa');
        
        $id_mahasiswa = $this->session->userdata('ref_id');
        
        // Tentukan apakah parameter adalah ID numerik atau token
        if (ctype_digit((string) $key)) {
            // Backward compatible: jika masih ada URL lama pakai ID
            $kegiatan = $this->Kegiatan_model->get_by_id((int) $key);
        } else {
            // URL baru: gunakan token QR Code
            $kegiatan = $this->Kegiatan_model->get_by_token($key);
        }
        
        if (!$kegiatan) {
            $this->session->set_flashdata('error', 'Data kegiatan tidak ditemukan.');
            redirect('Absensi/kegiatan_saya');
        }
        
        // Auto-update status kegiatan terlebih dahulu (pakai ID asli dari record)
        $this->Kegiatan_model->auto_update_status($kegiatan->id);
        
        // Ambil data kegiatan setelah update
        $kegiatan = $this->Kegiatan_model->get_by_id($kegiatan->id);
        
        if (!$kegiatan) {
            $this->session->set_flashdata('error', 'Data kegiatan tidak ditemukan.');
            redirect('Absensi/kegiatan_saya');
        }
        
        // Cek apakah mahasiswa adalah peserta
        $peserta = $this->Kegiatan_model->get_peserta_by_kegiatan($kegiatan->id);
        $is_peserta = false;
        foreach ($peserta as $p) {
            if ($p->id_mahasiswa == $id_mahasiswa) {
                $is_peserta = true;
                break;
            }
        }
        
        if (!$is_peserta) {
            $this->session->set_flashdata('error', 'Anda tidak terdaftar sebagai peserta kegiatan ini.');
            redirect('Absensi/kegiatan_saya');
        }
        
        // Ambil absensi mahasiswa
        $absensi = $this->Absensi_model->get_absensi_by_mahasiswa_kegiatan($id_mahasiswa, $kegiatan->id);
        
        $data = array(
            'title' => 'Detail Kegiatan',
            'page_title' => 'Detail Kegiatan',
            'breadcrumb' => array(
                array('label' => 'Absensi Kegiatan', 'url' => site_url('Absensi/kegiatan_saya')),
                array('label' => 'Detail', 'active' => true)
            ),
            'sidebar_menu' => generate_sidebar_menu('mahasiswa'),
            'kegiatan' => $kegiatan,
            'absensi' => $absensi
        );
        
        $data['content'] = $this->load->view('mahasiswa/absensi/detail', $data, true);
        $this->load->view('layouts/main', $data);
    }

    /**
     * Cetak - Cetak laporan absensi untuk mahasiswa
     */
    public function cetak() {
        $this->check_role('mahasiswa');
        
        $id_mahasiswa = $this->session->userdata('ref_id');
        $mahasiswa = $this->Mahasiswa_model->get_by_id($id_mahasiswa);
        
        // Ambil semua absensi mahasiswa
        $absensi_list = $this->Absensi_model->get_absensi_by_mahasiswa($id_mahasiswa);
        
        // Load library PDF
        $this->load->library('Pdf');
        $this->load->helper('app');
        
        $data = array(
            'mahasiswa' => $mahasiswa,
            'absensi_list' => $absensi_list
        );
        
        // Generate HTML untuk PDF
        $html = $this->load->view('mahasiswa/absensi/cetak', $data, true);
        
        // Load HTML ke PDF library
        $this->pdf->load_html($html);
        $this->pdf->set_paper('A4', 'portrait');
        
        // Generate filename
        $filename = 'Laporan_Absensi_' . $mahasiswa->nim . '_' . date('Ymd') . '.pdf';
        
        // Stream PDF
        $this->pdf->stream($filename);
    }
}

