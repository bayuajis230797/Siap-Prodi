<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Tanda_terima_mahasiswa extends MY_Controller {

    public function __construct() {
        parent::__construct();
        $this->load->model('Tanda_terima_model');
        $this->load->model('Tanda_terima_penerimaan_model');
        $this->load->library('Mobile_tanda_terima_service');
        $this->load->helper('menu');
    }

    public function index() {
        $this->check_role('mahasiswa');
        $id_mahasiswa = (int) $this->session->userdata('ref_id');
        $distribusi = $this->Tanda_terima_model->get_for_penerima('mahasiswa', $id_mahasiswa);
        foreach ($distribusi as $d) {
            $d->items = $this->Tanda_terima_penerimaan_model->get_status_items_for_penerima(
                $d->id,
                $id_mahasiswa,
                null
            );
        }
        $data = array(
            'title' => 'Tanda Terima',
            'page_title' => 'Tanda Terima Barang',
            'breadcrumb' => array(array('label' => 'Tanda Terima', 'active' => true)),
            'sidebar_menu' => generate_sidebar_menu('mahasiswa'),
            'distribusi' => $distribusi,
        );
        $data['content'] = $this->load->view('mahasiswa/tanda_terima/index', $data, true);
        $this->load->view('layouts/main', $data);
    }

    public function scan($token = null) {
        $this->check_role('mahasiswa');
        $id_mahasiswa = (int) $this->session->userdata('ref_id');
        if ($token) {
            $out = $this->mobile_tanda_terima_service->scan_mahasiswa($id_mahasiswa, $token);
            $this->output->set_content_type('application/json')->set_output(json_encode($out));
            return;
        }
        $data = array(
            'title' => 'Scan Tanda Terima',
            'page_title' => 'Scan QR Tanda Terima',
            'breadcrumb' => array(
                array('label' => 'Tanda Terima', 'url' => site_url('Tanda_terima_mahasiswa')),
                array('label' => 'Scan', 'active' => true),
            ),
            'sidebar_menu' => generate_sidebar_menu('mahasiswa'),
            'process_url' => site_url('Tanda_terima_mahasiswa/process_scan'),
        );
        $data['content'] = $this->load->view('mahasiswa/tanda_terima/scan', $data, true);
        $this->load->view('layouts/main', $data);
    }

    public function process_scan() {
        $this->check_role('mahasiswa');
        $id_mahasiswa = (int) $this->session->userdata('ref_id');
        $token = $this->input->post('token');
        $out = $this->mobile_tanda_terima_service->scan_mahasiswa($id_mahasiswa, $token);
        $this->output->set_content_type('application/json')->set_output(json_encode($out));
    }
}
