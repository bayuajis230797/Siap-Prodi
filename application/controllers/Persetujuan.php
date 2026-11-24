<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Persetujuan extends MY_Controller {

    public function __construct() {
        parent::__construct();
        $this->check_role('kaprodi');
    }

    public function index() {
        $data['title'] = 'Persetujuan';
        $data['page_title'] = 'Persetujuan';
        $data['breadcrumb'] = array(array('label' => 'Persetujuan', 'active' => true));
        $data['sidebar_menu'] = generate_sidebar_menu('kaprodi');
        $data['content'] = $this->load->view('persetujuan/index', array(), true);
        $this->load->view('layouts/main', $data);
    }
}
















