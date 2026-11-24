<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Proposal_pembimbing_setting Controller
 * Admin menentukan dosen yang boleh menjadi pembimbing Proposal TA.
 * (Flag dosen.is_pembimbing_proposal)
 */
class Proposal_pembimbing_setting extends MY_Controller {

    public function __construct() {
        parent::__construct();
        $this->check_role('admin');
        $this->load->model('Proposal_pembimbing_model');
    }

    public function index() {
        $dosen_list = $this->Proposal_pembimbing_model->get_all_with_flag();

        $total_dipilih = 0;
        foreach ($dosen_list as $d) {
            if (!empty($d->is_pembimbing_proposal)) {
                $total_dipilih++;
            }
        }

        $data = array(
            'title' => 'Dosen Pembimbing Proposal TA',
            'page_title' => 'Dosen Pembimbing Proposal TA',
            'breadcrumb' => array(
                array('label' => 'Proposal TA', 'url' => '#'),
                array('label' => 'Dosen Pembimbing', 'active' => true)
            ),
            'sidebar_menu' => generate_sidebar_menu('admin'),
            'dosen_list' => $dosen_list,
            'total_dipilih' => $total_dipilih
        );

        $data['content'] = $this->load->view('admin/proposal_pembimbing_setting/index', $data, true);
        $this->load->view('layouts/main', $data);
    }

    public function save() {
        if ($this->input->method() !== 'post') {
            redirect('Proposal_pembimbing_setting');
        }

        $ids = $this->input->post('pembimbing');
        if (!is_array($ids)) {
            $ids = array();
        }

        if ($this->Proposal_pembimbing_model->save_allowed_ids($ids)) {
            $this->session->set_flashdata('success', 'Daftar dosen pembimbing Proposal TA berhasil disimpan.');
        } else {
            $this->session->set_flashdata('error', 'Gagal menyimpan daftar dosen pembimbing.');
        }
        redirect('Proposal_pembimbing_setting');
    }
}
