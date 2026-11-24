<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Absensi Ujian Controller
 * Controller untuk mahasiswa melakukan absensi UTS/UAS via QR Code
 */
class Absensi_ujian extends MY_Controller {

    public function __construct() {
        parent::__construct();
        $this->load->model('Jadwal_uts_model');
        $this->load->model('Jadwal_uas_model');
        $this->load->model('Mahasiswa_model');
        $this->load->library('Mobile_absensi_service');
        $this->load->helper('absensi');
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
        
        // Token via GET: tampilkan halaman scan saja
        if ($token) {
            $data = array(
                'title' => 'Scan QR Code Absensi Ujian',
                'page_title' => 'Scan QR Code Absensi Ujian',
                'breadcrumb' => array(
                    array('label' => 'Jadwal Ujian', 'url' => site_url('Absensi_ujian/jadwal_saya')),
                    array('label' => 'Scan QR Code', 'active' => true)
                ),
                'sidebar_menu' => generate_sidebar_menu('mahasiswa'),
                'token' => $token
            );
            
            $data['content'] = $this->load->view('mahasiswa/absensi_ujian/scan', $data, true);
            $this->load->view('layouts/main', $data);
            return;
        }
        
        // Jika tidak ada token, tampilkan halaman scan
        $data = array(
            'title' => 'Scan QR Code Absensi Ujian',
            'page_title' => 'Scan QR Code Absensi Ujian',
            'breadcrumb' => array(
                array('label' => 'Jadwal Ujian', 'url' => site_url('Absensi_ujian/jadwal_saya')),
                array('label' => 'Scan QR Code', 'active' => true)
            ),
            'sidebar_menu' => generate_sidebar_menu('mahasiswa'),
            'token' => null
        );
        
        $data['content'] = $this->load->view('mahasiswa/absensi_ujian/scan', $data, true);
        $this->load->view('layouts/main', $data);
    }

    /**
     * Process Scan - Proses absensi dari scan QR Code
     */
    public function process_scan($token = null) {
        $is_ajax = $this->input->is_ajax_request();
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
            $token = $this->input->post('token');
        }
        $token = trim((string) $token);

        if ($token === '') {
            echo json_encode(array(
                'success' => false,
                'message' => 'Token QR Code tidak valid.',
            ));
            return;
        }

        $mode = 'ujian';
        if (stripos($token, 'UTS_') === 0) {
            $mode = 'uts';
        } elseif (stripos($token, 'UAS_') === 0) {
            $mode = 'uas';
        }

        try {
            $out = $this->mobile_absensi_service->scan_ujian($id_mahasiswa, $token, $mode);
        } catch (Throwable $e) {
            log_message('error', 'Absensi_ujian::process_scan: ' . $e->getMessage());
            echo json_encode(array(
                'success' => false,
                'message' => 'Terjadi kesalahan server saat memproses absensi. Silakan coba lagi.',
            ));
            return;
        }

        if (!empty($out['success'])) {
            $out['redirect_url'] = site_url('Jadwal/jadwal_' . strtolower($out['jenis_ujian'] ?? 'uts'));
        } elseif (!$is_ajax) {
            $this->session->set_flashdata('error', $out['message'] ?? 'Gagal absensi.');
            redirect('Absensi_ujian/jadwal_saya');
            return;
        }

        echo json_encode($out);
    }

    /**
     * Kegiatan Saya - Daftar jadwal UTS/UAS untuk mahasiswa
     */
    public function jadwal_saya() {
        $this->check_role('mahasiswa');
        
        $id_mahasiswa = $this->session->userdata('ref_id');
        $mahasiswa = $this->Mahasiswa_model->get_by_id($id_mahasiswa);
        
        if (!$mahasiswa) {
            $this->session->set_flashdata('error', 'Data mahasiswa tidak ditemukan.');
            redirect('Dashboard');
        }
        
        // Ambil tahun akademik aktif
        $this->load->model('Tahun_akademik_model');
        $tahun_aktif = $this->Tahun_akademik_model->get_aktif();
        $id_tahun_akademik = $tahun_aktif ? $tahun_aktif->id : null;
        
        // Ambil jadwal UTS dan UAS untuk kelas mahasiswa
        $jadwal_uts = array();
        $jadwal_uas = array();
        
        if ($id_tahun_akademik && $mahasiswa->kelas) {
            $jadwal_uts = $this->Jadwal_uts_model->get_by_kelas($mahasiswa->kelas, $id_tahun_akademik);
            $jadwal_uas = $this->Jadwal_uas_model->get_by_kelas($mahasiswa->kelas, $id_tahun_akademik);
            
            // Ambil status kehadiran untuk setiap jadwal
            foreach ($jadwal_uts as $j) {
                $this->db->where('id_jadwal_uts', $j->id);
                $this->db->where('id_mahasiswa', $id_mahasiswa);
                $kehadiran = $this->db->get('jadwal_uts_kehadiran')->row();
                $j->status_absen = $kehadiran ? $kehadiran->status_kehadiran : null;
                $j->waktu_absen = $kehadiran ? $kehadiran->created_at : null;
                $j->can_absen = absensi_ujian_can_absen_now($j);
            }
            
            foreach ($jadwal_uas as $j) {
                $this->db->where('id_jadwal_uas', $j->id);
                $this->db->where('id_mahasiswa', $id_mahasiswa);
                $kehadiran = $this->db->get('jadwal_uas_kehadiran')->row();
                $j->status_absen = $kehadiran ? $kehadiran->status_kehadiran : null;
                $j->waktu_absen = $kehadiran ? $kehadiran->created_at : null;
                $j->can_absen = absensi_ujian_can_absen_now($j);
            }
        }
        
        $data = array(
            'title' => 'Jadwal Ujian Saya',
            'page_title' => 'Jadwal Ujian Saya',
            'breadcrumb' => array(array('label' => 'Jadwal Ujian', 'active' => true)),
            'sidebar_menu' => generate_sidebar_menu('mahasiswa'),
            'jadwal_uts' => $jadwal_uts,
            'jadwal_uas' => $jadwal_uas,
            'mahasiswa' => $mahasiswa
        );
        
        $data['content'] = $this->load->view('mahasiswa/absensi_ujian/jadwal_saya', $data, true);
        $this->load->view('layouts/main', $data);
    }

    /**
     * Detail Jadwal Ujian
     */
    public function detail($token = null) {
        $this->check_role('mahasiswa');
        $id_mahasiswa = $this->session->userdata('ref_id');
        
        if (empty($token)) {
            $this->session->set_flashdata('error', 'Token tidak valid.');
            redirect('Absensi_ujian/jadwal_saya');
        }
        
        // Cari jadwal berdasarkan token
        $jadwal = null;
        $jenis_ujian = null;
        $table_kehadiran = null;
        $id_jadwal_field = null;
        
        $jadwal_uts = $this->Jadwal_uts_model->get_by_token($token);
        if ($jadwal_uts) {
            $jadwal = $jadwal_uts;
            $jenis_ujian = 'UTS';
            $table_kehadiran = 'jadwal_uts_kehadiran';
            $id_jadwal_field = 'id_jadwal_uts';
        } else {
            $jadwal_uas = $this->Jadwal_uas_model->get_by_token($token);
            if ($jadwal_uas) {
                $jadwal = $jadwal_uas;
                $jenis_ujian = 'UAS';
                $table_kehadiran = 'jadwal_uas_kehadiran';
                $id_jadwal_field = 'id_jadwal_uas';
            }
        }
        
        if (!$jadwal) {
            $this->session->set_flashdata('error', 'Jadwal tidak ditemukan.');
            redirect('Absensi_ujian/jadwal_saya');
        }
        
        // Ambil data kehadiran
        $this->db->where($id_jadwal_field, $jadwal->id);
        $this->db->where('id_mahasiswa', $id_mahasiswa);
        $kehadiran = $this->db->get($table_kehadiran)->row();
        
        // Ambil pengawas
        if ($jenis_ujian == 'UTS') {
            $pengawas = $this->Jadwal_uts_model->get_pengawas($jadwal->id);
        } else {
            $pengawas = $this->Jadwal_uas_model->get_pengawas($jadwal->id);
        }
        
        $data = array(
            'title' => 'Detail Jadwal ' . $jenis_ujian,
            'page_title' => 'Detail Jadwal ' . $jenis_ujian,
            'breadcrumb' => array(
                array('label' => 'Absensi Ujian', 'url' => site_url('Absensi_ujian/jadwal_saya')),
                array('label' => 'Detail', 'active' => true)
            ),
            'sidebar_menu' => generate_sidebar_menu('mahasiswa'),
            'jadwal' => $jadwal,
            'jenis_ujian' => $jenis_ujian,
            'kehadiran' => $kehadiran,
            'pengawas' => $pengawas,
            'can_absen' => absensi_ujian_can_absen_now($jadwal),
        );
        
        $data['content'] = $this->load->view('mahasiswa/absensi_ujian/detail', $data, true);
        $this->load->view('layouts/main', $data);
    }
}

