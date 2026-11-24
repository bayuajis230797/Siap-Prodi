<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Rkat extends MY_Controller {

    public function __construct() {
        parent::__construct();
    }

    public function index() {
        $role = get_user_role();
        $this->check_role(array('admin', 'sekprodi', 'dosen', 'mahasiswa'));
        
        $data['title'] = 'RKAT';
        $data['page_title'] = 'RKAT';
        $data['breadcrumb'] = array(array('label' => 'RKAT', 'active' => true));
        $data['sidebar_menu'] = generate_sidebar_menu($role);
        $data['content'] = $this->load->view('rkat/index', array(), true);
        $this->load->view('layouts/main', $data);
    }

    public function realisasi() {
        $this->check_role(array('admin', 'sekprodi'));
        
        $data['title'] = 'Realisasi RKAT';
        $data['page_title'] = 'Realisasi RKAT';
        $data['breadcrumb'] = array(
            array('label' => 'RKAT', 'url' => site_url('Rkat')),
            array('label' => 'Realisasi', 'active' => true)
        );
        $data['sidebar_menu'] = generate_sidebar_menu(get_user_role());
        $data['content'] = $this->load->view('rkat/realisasi', array(), true);
        $this->load->view('layouts/main', $data);
    }
}
















