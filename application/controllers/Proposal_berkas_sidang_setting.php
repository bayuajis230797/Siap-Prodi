<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Proposal_berkas_sidang_setting Controller
 * CRUD Master Berkas Sidang Proposal TA (untuk Admin)
 */
class Proposal_berkas_sidang_setting extends MY_Controller {

    public function __construct() {
        parent::__construct();
        $this->check_role('admin');
        $this->load->model('Proposal_berkas_sidang_model');
        $this->load->library('form_validation');
    }

    /**
     * List semua berkas sidang
     */
    public function index() {
        $aktif_filter = $this->input->get('aktif');
        
        $berkas_list = $this->Proposal_berkas_sidang_model->get_all(false);
        
        if ($aktif_filter !== null && $aktif_filter !== '') {
            $berkas_list = array_filter($berkas_list, function($item) use ($aktif_filter) {
                return $item->aktif == $aktif_filter;
            });
        }

        $data = array(
            'title' => 'Setting Berkas Sidang Proposal TA',
            'page_title' => 'Setting Berkas Sidang Proposal TA',
            'breadcrumb' => array(
                array('label' => 'Proposal TA', 'url' => '#'),
                array('label' => 'Setting Berkas Sidang', 'active' => true)
            ),
            'sidebar_menu' => generate_sidebar_menu('admin'),
            'berkas_list' => array_values($berkas_list),
            'aktif_filter' => $aktif_filter
        );

        $data['content'] = $this->load->view('admin/proposal_berkas_sidang_setting/index', $data, true);
        $this->load->view('layouts/main', $data);
    }

    /**
     * Form tambah berkas sidang
     */
    public function create() {
        if ($this->input->method() === 'post') {
            $this->form_validation->set_rules('nama_berkas', 'Nama Berkas', 'required|trim');
            $this->form_validation->set_rules('urutan', 'Urutan', 'required|trim|numeric');

            if ($this->form_validation->run() == FALSE) {
                $data['title'] = 'Tambah Berkas Sidang Proposal TA';
                $data['page_title'] = 'Tambah Berkas Sidang Proposal TA';
                $data['breadcrumb'] = array(
                    array('label' => 'Proposal TA', 'url' => '#'),
                    array('label' => 'Setting Berkas Sidang', 'url' => site_url('Proposal_berkas_sidang_setting')),
                    array('label' => 'Tambah', 'active' => true)
                );
                $data['sidebar_menu'] = generate_sidebar_menu('admin');
                $data['content'] = $this->load->view('admin/proposal_berkas_sidang_setting/create', $data, true);
                $this->load->view('layouts/main', $data);
            } else {
                $data = array(
                    'nama_berkas' => $this->input->post('nama_berkas'),
                    'deskripsi' => $this->input->post('deskripsi') ?: null,
                    'wajib' => $this->input->post('wajib') ? 1 : 0,
                    'urutan' => intval($this->input->post('urutan')),
                    'aktif' => $this->input->post('aktif') ? 1 : 0
                );

                if ($this->Proposal_berkas_sidang_model->create($data)) {
                    $this->session->set_flashdata('success', 'Berkas sidang berhasil ditambahkan.');
                    redirect('Proposal_berkas_sidang_setting');
                } else {
                    $this->session->set_flashdata('error', 'Gagal menambahkan berkas sidang.');
                    redirect('Proposal_berkas_sidang_setting/create');
                }
            }
        } else {
            $data['title'] = 'Tambah Berkas Sidang Proposal TA';
            $data['page_title'] = 'Tambah Berkas Sidang Proposal TA';
            $data['breadcrumb'] = array(
                array('label' => 'Proposal TA', 'url' => '#'),
                array('label' => 'Setting Berkas Sidang', 'url' => site_url('Proposal_berkas_sidang_setting')),
                array('label' => 'Tambah', 'active' => true)
            );
            $data['sidebar_menu'] = generate_sidebar_menu('admin');
            $data['content'] = $this->load->view('admin/proposal_berkas_sidang_setting/create', $data, true);
            $this->load->view('layouts/main', $data);
        }
    }

    /**
     * Form edit berkas sidang
     */
    public function edit($id) {
        $berkas = $this->Proposal_berkas_sidang_model->get_by_id($id);
        if (!$berkas) {
            $this->session->set_flashdata('error', 'Data berkas tidak ditemukan.');
            redirect('Proposal_berkas_sidang_setting');
        }

        if ($this->input->method() === 'post') {
            $this->form_validation->set_rules('nama_berkas', 'Nama Berkas', 'required|trim');
            $this->form_validation->set_rules('urutan', 'Urutan', 'required|trim|numeric');

            if ($this->form_validation->run() == FALSE) {
                $data['berkas'] = $berkas;
                $data['title'] = 'Edit Berkas Sidang Proposal TA';
                $data['page_title'] = 'Edit Berkas Sidang Proposal TA';
                $data['breadcrumb'] = array(
                    array('label' => 'Proposal TA', 'url' => '#'),
                    array('label' => 'Setting Berkas Sidang', 'url' => site_url('Proposal_berkas_sidang_setting')),
                    array('label' => 'Edit', 'active' => true)
                );
                $data['sidebar_menu'] = generate_sidebar_menu('admin');
                $data['content'] = $this->load->view('admin/proposal_berkas_sidang_setting/edit', $data, true);
                $this->load->view('layouts/main', $data);
            } else {
                $data = array(
                    'nama_berkas' => $this->input->post('nama_berkas'),
                    'deskripsi' => $this->input->post('deskripsi') ?: null,
                    'wajib' => $this->input->post('wajib') ? 1 : 0,
                    'urutan' => intval($this->input->post('urutan')),
                    'aktif' => $this->input->post('aktif') ? 1 : 0
                );

                if ($this->Proposal_berkas_sidang_model->update($id, $data)) {
                    $this->session->set_flashdata('success', 'Berkas sidang berhasil diperbarui.');
                    redirect('Proposal_berkas_sidang_setting');
                } else {
                    $this->session->set_flashdata('error', 'Gagal memperbarui berkas sidang.');
                    redirect('Proposal_berkas_sidang_setting/edit/' . $id);
                }
            }
        } else {
            $data['berkas'] = $berkas;
            $data['title'] = 'Edit Berkas Sidang Proposal TA';
            $data['page_title'] = 'Edit Berkas Sidang Proposal TA';
            $data['breadcrumb'] = array(
                array('label' => 'Proposal TA', 'url' => '#'),
                array('label' => 'Setting Berkas Sidang', 'url' => site_url('Proposal_berkas_sidang_setting')),
                array('label' => 'Edit', 'active' => true)
            );
            $data['sidebar_menu'] = generate_sidebar_menu('admin');
            $data['content'] = $this->load->view('admin/proposal_berkas_sidang_setting/edit', $data, true);
            $this->load->view('layouts/main', $data);
        }
    }

    /**
     * Hapus berkas sidang
     */
    public function delete($id) {
        if ($this->input->method() !== 'post') {
            redirect('Proposal_berkas_sidang_setting');
        }
        if ($this->Proposal_berkas_sidang_model->delete($id)) {
            $this->session->set_flashdata('success', 'Berkas sidang berhasil dihapus.');
        } else {
            $this->session->set_flashdata('error', 'Gagal menghapus berkas sidang.');
        }
        redirect('Proposal_berkas_sidang_setting');
    }
}


