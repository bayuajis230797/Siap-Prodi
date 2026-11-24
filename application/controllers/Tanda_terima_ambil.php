<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Scan tanda terima untuk distribusi campuran (mahasiswa atau dosen, sesuai login).
 */
class Tanda_terima_ambil extends MY_Controller {

    public function __construct() {
        parent::__construct();
        $this->load->library('Mobile_tanda_terima_service');
        $this->load->helper('menu');
    }

    public function scan($token = null) {
        $role = $this->session->userdata('role');
        if ($token) {
            if ($role === 'mahasiswa') {
                $out = $this->mobile_tanda_terima_service->scan_mahasiswa((int) $this->session->userdata('ref_id'), $token);
            } elseif ($role === 'dosen') {
                $out = $this->mobile_tanda_terima_service->scan_dosen((int) $this->session->userdata('ref_id'), $token);
            } else {
                $out = array('success' => false, 'message' => 'Login sebagai mahasiswa atau dosen untuk scan.');
            }
            $this->output->set_content_type('application/json')->set_output(json_encode($out));
            return;
        }

        if (!in_array($role, array('mahasiswa', 'dosen'), true)) {
            $this->session->set_flashdata('error', 'Login sebagai mahasiswa atau dosen.');
            redirect('Auth/login');
        }

        $menu_role = $role;
        $index_url = $role === 'dosen' ? 'Tanda_terima_dosen' : 'Tanda_terima_mahasiswa';
        $data = array(
            'title' => 'Scan Tanda Terima',
            'page_title' => 'Scan QR Tanda Terima',
            'breadcrumb' => array(
                array('label' => 'Tanda Terima', 'url' => site_url($index_url)),
                array('label' => 'Scan', 'active' => true),
            ),
            'sidebar_menu' => generate_sidebar_menu($menu_role),
            'process_url' => site_url('Tanda_terima_ambil/process_scan'),
            'scan_path_hint' => 'Tanda_terima_ambil',
        );
        $data['content'] = $this->load->view('mahasiswa/tanda_terima/scan', $data, true);
        $this->load->view('layouts/main', $data);
    }

    public function process_scan() {
        $role = $this->session->userdata('role');
        $token = $this->input->post('token');
        if ($role === 'mahasiswa') {
            $out = $this->mobile_tanda_terima_service->scan_mahasiswa((int) $this->session->userdata('ref_id'), $token);
        } elseif ($role === 'dosen') {
            $out = $this->mobile_tanda_terima_service->scan_dosen((int) $this->session->userdata('ref_id'), $token);
        } else {
            $out = array('success' => false, 'message' => 'Login sebagai mahasiswa atau dosen.');
        }
        $this->output->set_content_type('application/json')->set_output(json_encode($out));
    }
}
