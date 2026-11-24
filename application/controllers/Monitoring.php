<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Monitoring extends MY_Controller {

    public function __construct() {
        parent::__construct();
        $this->check_role('kaprodi');
    }

    public function index() {
        $data['title'] = 'Monitoring Akademik';
        $data['page_title'] = 'Monitoring Akademik';
        $data['breadcrumb'] = array(array('label' => 'Monitoring Akademik', 'active' => true));
        $data['sidebar_menu'] = generate_sidebar_menu('kaprodi');
        $data['content'] = $this->load->view('monitoring/index', array(), true);
        $this->load->view('layouts/main', $data);
    }
}
















