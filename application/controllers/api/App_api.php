<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class App_api extends CI_Controller {

    public function __construct() {
        parent::__construct();
        $this->load->helper('api');
        api_handle_options();
        api_send_cors_headers();
        $this->load->model('App_settings_model');
    }

    public function branding() {
        if ($this->input->method(TRUE) !== 'GET') {
            api_json_response(array('success' => false, 'message' => 'Method tidak diizinkan.'), 405);
        }

        $settings = $this->App_settings_model->get_settings();
        $this->load->helper('app');

        $logo_url = null;
        if ($settings && !empty($settings->logo_kampus)) {
            $logo_url = get_logo_kampus();
        }

        api_json_response(array(
            'success' => true,
            'app_name' => 'SIAP PRODI',
            'nama_kampus' => $settings->nama_kampus ?? '',
            'nama_prodi' => $settings->nama_prodi ?? '',
            'logo_url' => $logo_url,
            'base_url' => rtrim((string) $this->config->item('base_url'), '/') . '/',
        ));
    }
}
