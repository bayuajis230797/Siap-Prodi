<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Aplikasi_mobile Controller (SEMUA peran yang sudah login)
 * Halaman "Aplikasi Android": memasang SI-HARKAT sebagai aplikasi (PWA) di ponsel/komputer.
 */
class Aplikasi_mobile extends MY_Controller {

    public function index() {
        $role = (string) $this->session->userdata('role');
        $data = array(
            'title' => 'Aplikasi Android',
            'page_title' => 'Aplikasi Android',
            'breadcrumb' => array(array('label' => 'Aplikasi Android', 'active' => true)),
            'sidebar_menu' => generate_sidebar_menu($role)
        );
        $data['content'] = $this->load->view('aplikasi/index', $data, true);
        $this->load->view('layouts/main', $data);
    }
}
