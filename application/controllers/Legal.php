<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Halaman publik untuk OAuth consent (privacy / terms).
 * Tidak perlu login.
 */
class Legal extends CI_Controller {

    public function privacy() {
        $this->load->view('legal/privacy');
    }

    public function terms() {
        $this->load->view('legal/terms');
    }
}
