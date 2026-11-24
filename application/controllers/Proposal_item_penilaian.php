<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Proposal_item_penilaian Controller
 * CRUD Item Penilaian per Aspek Proposal TA
 */
class Proposal_item_penilaian extends MY_Controller {

    public function __construct() {
        parent::__construct();
        $this->check_role('admin');
        $this->load->model('Proposal_item_penilaian_model');
        $this->load->model('Proposal_aspek_penilaian_model');
        $this->load->library('form_validation');
        $this->load->library('pagination');
    }

    /**
     * List semua item penilaian
     */
    public function index() {
        // Ambil parameter per_page dari GET, default 10
        $per_page = $this->input->get('per_page') ? (int)$this->input->get('per_page') : 10;
        $per_page = in_array($per_page, [10, 25, 50, 100]) ? $per_page : 10;
        
        // Ambil filter
        $id_aspek_filter = $this->input->get('id_aspek_penilaian');
        $is_aktif_filter = $this->input->get('is_aktif');

        // Ambil semua aspek penilaian untuk filter dropdown
        $aspek_list = $this->Proposal_aspek_penilaian_model->get_all(false);

        // Ambil semua item penilaian
        $item_list = $this->Proposal_item_penilaian_model->get_all(false);
        
        // Filter jika ada
        if ($id_aspek_filter) {
            $item_list = array_filter($item_list, function($item) use ($id_aspek_filter) {
                return $item->id_aspek_penilaian == $id_aspek_filter;
            });
        }
        if ($is_aktif_filter !== null && $is_aktif_filter !== '') {
            $item_list = array_filter($item_list, function($item) use ($is_aktif_filter) {
                return $item->is_aktif == $is_aktif_filter;
            });
        }

        // Manual pagination
        $total_rows = count($item_list);
        $page = ($this->uri->segment(3)) ? $this->uri->segment(3) : 1;
        $offset = ($page - 1) * $per_page;
        $item_list_paginated = array_slice($item_list, $offset, $per_page);

        // Pagination config
        $config['base_url'] = site_url('Proposal_item_penilaian/index');
        $config['total_rows'] = $total_rows;
        $config['per_page'] = $per_page;
        $config['uri_segment'] = 3;
        
        $query_params = array_filter(array(
            'per_page' => ($per_page != 10 ? $per_page : null),
            'id_aspek_penilaian' => $id_aspek_filter,
            'is_aktif' => $is_aktif_filter
        ));
        $query_string = http_build_query($query_params);
        if ($query_string) {
            $config['suffix'] = '?' . $query_string;
            $config['first_url'] = site_url('Proposal_item_penilaian/index?' . $query_string);
        }
        
        $config['use_page_numbers'] = TRUE;
        $config['first_link'] = '&laquo; Awal';
        $config['last_link'] = 'Akhir &raquo;';
        $config['next_link'] = '&gt;';
        $config['prev_link'] = '&lt;';
        $config['full_tag_open'] = '<ul class="pagination pagination-sm m-0 float-right">';
        $config['full_tag_close'] = '</ul>';
        $config['first_tag_open'] = '<li class="page-item">';
        $config['first_tag_close'] = '</li>';
        $config['last_tag_open'] = '<li class="page-item">';
        $config['last_tag_close'] = '</li>';
        $config['next_tag_open'] = '<li class="page-item">';
        $config['next_tag_close'] = '</li>';
        $config['prev_tag_open'] = '<li class="page-item">';
        $config['prev_tag_close'] = '</li>';
        $config['cur_tag_open'] = '<li class="page-item active"><span class="page-link">';
        $config['cur_tag_close'] = '</span></li>';
        $config['num_tag_open'] = '<li class="page-item">';
        $config['num_tag_close'] = '</li>';
        $config['attributes'] = array('class' => 'page-link');

        $this->pagination->initialize($config);

        $data = array(
            'title' => 'Setting Item Penilaian Proposal TA',
            'page_title' => 'Setting Item Penilaian Proposal TA',
            'breadcrumb' => array(
                array('label' => 'Proposal TA', 'url' => '#'),
                array('label' => 'Setting Item Penilaian', 'active' => true)
            ),
            'sidebar_menu' => generate_sidebar_menu('admin'),
            'item_list' => $item_list_paginated,
            'aspek_list' => $aspek_list,
            'id_aspek_filter' => $id_aspek_filter,
            'is_aktif_filter' => $is_aktif_filter,
            'pagination' => $this->pagination->create_links(),
            'per_page' => $per_page,
            'total_rows' => $total_rows
        );

        $data['content'] = $this->load->view('admin/proposal_item_penilaian/index', $data, true);
        $this->load->view('layouts/main', $data);
    }

    /**
     * Form tambah item penilaian
     */
    public function create() {
        if ($this->input->method() === 'post') {
            $this->form_validation->set_rules('id_aspek_penilaian', 'Aspek Penilaian', 'required|integer');
            $this->form_validation->set_rules('pertanyaan', 'Pertanyaan', 'required|trim');
            $this->form_validation->set_rules('bobot', 'Bobot', 'required|trim|numeric|greater_than_equal_to[0]|less_than_equal_to[100]');
            $this->form_validation->set_rules('urutan', 'Urutan', 'required|trim|numeric');

            if ($this->form_validation->run() == FALSE) {
                $data['aspek_list'] = $this->Proposal_aspek_penilaian_model->get_all(false);
                $data['title'] = 'Tambah Item Penilaian Proposal TA';
                $data['page_title'] = 'Tambah Item Penilaian Proposal TA';
                $data['breadcrumb'] = array(
                    array('label' => 'Proposal TA', 'url' => '#'),
                    array('label' => 'Setting Item Penilaian', 'url' => site_url('Proposal_item_penilaian')),
                    array('label' => 'Tambah', 'active' => true)
                );
                $data['sidebar_menu'] = generate_sidebar_menu('admin');
                $data['content'] = $this->load->view('admin/proposal_item_penilaian/create', $data, true);
                $this->load->view('layouts/main', $data);
            } else {
                $data = array(
                    'id_aspek_penilaian' => intval($this->input->post('id_aspek_penilaian')),
                    'pertanyaan' => $this->input->post('pertanyaan'),
                    'bobot' => floatval($this->input->post('bobot')),
                    'aktif' => $this->input->post('is_aktif') ? 1 : 0,
                    'urutan' => intval($this->input->post('urutan'))
                );

                if ($this->Proposal_item_penilaian_model->create($data)) {
                    $this->session->set_flashdata('success', 'Item penilaian berhasil ditambahkan.');
                    redirect('Proposal_item_penilaian');
                } else {
                    $this->session->set_flashdata('error', 'Gagal menambahkan item penilaian.');
                    redirect('Proposal_item_penilaian/create');
                }
            }
        } else {
            $data['aspek_list'] = $this->Proposal_aspek_penilaian_model->get_all(false);
            $data['title'] = 'Tambah Item Penilaian Proposal TA';
            $data['page_title'] = 'Tambah Item Penilaian Proposal TA';
            $data['breadcrumb'] = array(
                array('label' => 'Proposal TA', 'url' => '#'),
                array('label' => 'Setting Item Penilaian', 'url' => site_url('Proposal_item_penilaian')),
                array('label' => 'Tambah', 'active' => true)
            );
            $data['sidebar_menu'] = generate_sidebar_menu('admin');
            $data['content'] = $this->load->view('admin/proposal_item_penilaian/create', $data, true);
            $this->load->view('layouts/main', $data);
        }
    }

    /**
     * Form edit item penilaian
     */
    public function edit($id) {
        $item = $this->Proposal_item_penilaian_model->get_by_id($id);
        if (!$item) {
            $this->session->set_flashdata('error', 'Data item penilaian tidak ditemukan.');
            redirect('Proposal_item_penilaian');
        }

        if ($this->input->method() === 'post') {
            $this->form_validation->set_rules('id_aspek_penilaian', 'Aspek Penilaian', 'required|integer');
            $this->form_validation->set_rules('pertanyaan', 'Pertanyaan', 'required|trim');
            $this->form_validation->set_rules('bobot', 'Bobot', 'required|trim|numeric|greater_than_equal_to[0]|less_than_equal_to[100]');
            $this->form_validation->set_rules('urutan', 'Urutan', 'required|trim|numeric');

            if ($this->form_validation->run() == FALSE) {
                $data['item'] = $item;
                $data['aspek_list'] = $this->Proposal_aspek_penilaian_model->get_all(false);
                $data['title'] = 'Edit Item Penilaian Proposal TA';
                $data['page_title'] = 'Edit Item Penilaian Proposal TA';
                $data['breadcrumb'] = array(
                    array('label' => 'Proposal TA', 'url' => '#'),
                    array('label' => 'Setting Item Penilaian', 'url' => site_url('Proposal_item_penilaian')),
                    array('label' => 'Edit', 'active' => true)
                );
                $data['sidebar_menu'] = generate_sidebar_menu('admin');
                $data['content'] = $this->load->view('admin/proposal_item_penilaian/edit', $data, true);
                $this->load->view('layouts/main', $data);
            } else {
                $data = array(
                    'id_aspek_penilaian' => intval($this->input->post('id_aspek_penilaian')),
                    'pertanyaan' => $this->input->post('pertanyaan'),
                    'bobot' => floatval($this->input->post('bobot')),
                    'aktif' => $this->input->post('is_aktif') ? 1 : 0,
                    'urutan' => intval($this->input->post('urutan'))
                );

                if ($this->Proposal_item_penilaian_model->update($id, $data)) {
                    $this->session->set_flashdata('success', 'Item penilaian berhasil diperbarui.');
                    redirect('Proposal_item_penilaian');
                } else {
                    $this->session->set_flashdata('error', 'Gagal memperbarui item penilaian.');
                    redirect('Proposal_item_penilaian/edit/' . $id);
                }
            }
        } else {
            $data['item'] = $item;
            $data['aspek_list'] = $this->Proposal_aspek_penilaian_model->get_all(false);
            $data['title'] = 'Edit Item Penilaian Proposal TA';
            $data['page_title'] = 'Edit Item Penilaian Proposal TA';
            $data['breadcrumb'] = array(
                array('label' => 'Proposal TA', 'url' => '#'),
                array('label' => 'Setting Item Penilaian', 'url' => site_url('Proposal_item_penilaian')),
                array('label' => 'Edit', 'active' => true)
            );
            $data['sidebar_menu'] = generate_sidebar_menu('admin');
            $data['content'] = $this->load->view('admin/proposal_item_penilaian/edit', $data, true);
            $this->load->view('layouts/main', $data);
        }
    }

    /**
     * Hapus item penilaian
     */
    public function delete($id) {
        if ($this->input->method() !== 'post') {
            redirect('Proposal_item_penilaian');
        }
        if ($this->Proposal_item_penilaian_model->delete($id)) {
            $this->session->set_flashdata('success', 'Item penilaian berhasil dihapus.');
        } else {
            $this->session->set_flashdata('error', 'Gagal menghapus item penilaian.');
        }
        redirect('Proposal_item_penilaian');
    }
}

