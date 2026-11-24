<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Pkl_item_penilaian Controller
 * CRUD Item Penilaian per Aspek PKL
 */
class Pkl_item_penilaian extends MY_Controller {

    public function __construct() {
        parent::__construct();
        $this->check_role('admin');
        $this->load->model('Pkl_item_penilaian_model');
        $this->load->model('Pkl_aspek_penilaian_model');
        $this->load->library('form_validation');
    }

    /**
     * List semua item penilaian dengan pagination
     */
    public function index() {
        // Ambil parameter per_page dari GET, default 10
        $per_page = $this->input->get('per_page') ? (int)$this->input->get('per_page') : 10;
        $per_page = in_array($per_page, [10, 25, 50, 100]) ? $per_page : 10;
        
        // Ambil filter
        $id_aspek_filter = $this->input->get('id_aspek_penilaian');
        $is_aktif_filter = $this->input->get('is_aktif');

        // Ambil semua aspek penilaian untuk filter dropdown
        $aspek_list = $this->Pkl_aspek_penilaian_model->get_all(false);

        // Ambil semua item penilaian
        $item_list = $this->Pkl_item_penilaian_model->get_all(false);
        
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

        $data = array(
            'title' => 'Master Item Penilaian PKL',
            'page_title' => 'Master Item Penilaian PKL',
            'breadcrumb' => array(
                array('label' => 'PKL', 'url' => '#'),
                array('label' => 'Master Item Penilaian', 'active' => true)
            ),
            'sidebar_menu' => generate_sidebar_menu('admin'),
            'item_list' => array_values($item_list),
            'aspek_list' => $aspek_list,
            'id_aspek_filter' => $id_aspek_filter,
            'is_aktif_filter' => $is_aktif_filter
        );

        $data['content'] = $this->load->view('admin/pkl_item_penilaian/index', $data, true);
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
                $data['aspek_list'] = $this->Pkl_aspek_penilaian_model->get_all(false);
                $data['title'] = 'Tambah Item Penilaian PKL';
                $data['page_title'] = 'Tambah Item Penilaian PKL';
                $data['breadcrumb'] = array(
                    array('label' => 'PKL', 'url' => '#'),
                    array('label' => 'Master Item Penilaian', 'url' => site_url('Pkl_item_penilaian')),
                    array('label' => 'Tambah', 'active' => true)
                );
                $data['sidebar_menu'] = generate_sidebar_menu('admin');
                $data['content'] = $this->load->view('admin/pkl_item_penilaian/create', $data, true);
                $this->load->view('layouts/main', $data);
            } else {
                $data = array(
                    'id_aspek_penilaian' => intval($this->input->post('id_aspek_penilaian')),
                    'pertanyaan' => $this->input->post('pertanyaan'),
                    'bobot' => floatval($this->input->post('bobot')),
                    'is_aktif' => $this->input->post('is_aktif') ? 1 : 0,
                    'urutan' => intval($this->input->post('urutan'))
                );

                if ($this->Pkl_item_penilaian_model->create($data)) {
                    $this->session->set_flashdata('success', 'Item penilaian berhasil ditambahkan.');
                    redirect('Pkl_item_penilaian');
                } else {
                    $this->session->set_flashdata('error', 'Gagal menambahkan item penilaian.');
                    redirect('Pkl_item_penilaian/create');
                }
            }
        } else {
            $data['aspek_list'] = $this->Pkl_aspek_penilaian_model->get_all(false);
            $data['title'] = 'Tambah Item Penilaian PKL';
            $data['page_title'] = 'Tambah Item Penilaian PKL';
            $data['breadcrumb'] = array(
                array('label' => 'PKL', 'url' => '#'),
                array('label' => 'Master Item Penilaian', 'url' => site_url('Pkl_item_penilaian')),
                array('label' => 'Tambah', 'active' => true)
            );
            $data['sidebar_menu'] = generate_sidebar_menu('admin');
            $data['content'] = $this->load->view('admin/pkl_item_penilaian/create', $data, true);
            $this->load->view('layouts/main', $data);
        }
    }

    /**
     * Form edit item penilaian
     */
    public function edit($id) {
        $item = $this->Pkl_item_penilaian_model->get_by_id($id);
        if (!$item) {
            $this->session->set_flashdata('error', 'Data item penilaian tidak ditemukan.');
            redirect('Pkl_item_penilaian');
        }

        if ($this->input->method() === 'post') {
            $this->form_validation->set_rules('id_aspek_penilaian', 'Aspek Penilaian', 'required|integer');
            $this->form_validation->set_rules('pertanyaan', 'Pertanyaan', 'required|trim');
            $this->form_validation->set_rules('bobot', 'Bobot', 'required|trim|numeric|greater_than_equal_to[0]|less_than_equal_to[100]');
            $this->form_validation->set_rules('urutan', 'Urutan', 'required|trim|numeric');

            if ($this->form_validation->run() == FALSE) {
                $data['item'] = $item;
                $data['aspek_list'] = $this->Pkl_aspek_penilaian_model->get_all(false);
                $data['title'] = 'Edit Item Penilaian PKL';
                $data['page_title'] = 'Edit Item Penilaian PKL';
                $data['breadcrumb'] = array(
                    array('label' => 'PKL', 'url' => '#'),
                    array('label' => 'Master Item Penilaian', 'url' => site_url('Pkl_item_penilaian')),
                    array('label' => 'Edit', 'active' => true)
                );
                $data['sidebar_menu'] = generate_sidebar_menu('admin');
                $data['content'] = $this->load->view('admin/pkl_item_penilaian/edit', $data, true);
                $this->load->view('layouts/main', $data);
            } else {
                $data = array(
                    'id_aspek_penilaian' => intval($this->input->post('id_aspek_penilaian')),
                    'pertanyaan' => $this->input->post('pertanyaan'),
                    'bobot' => floatval($this->input->post('bobot')),
                    'is_aktif' => $this->input->post('is_aktif') ? 1 : 0,
                    'urutan' => intval($this->input->post('urutan'))
                );

                if ($this->Pkl_item_penilaian_model->update($id, $data)) {
                    $this->session->set_flashdata('success', 'Item penilaian berhasil diperbarui.');
                    redirect('Pkl_item_penilaian');
                } else {
                    $this->session->set_flashdata('error', 'Gagal memperbarui item penilaian.');
                    redirect('Pkl_item_penilaian/edit/' . $id);
                }
            }
        } else {
            $data['item'] = $item;
            $data['aspek_list'] = $this->Pkl_aspek_penilaian_model->get_all(false);
            $data['title'] = 'Edit Item Penilaian PKL';
            $data['page_title'] = 'Edit Item Penilaian PKL';
            $data['breadcrumb'] = array(
                array('label' => 'PKL', 'url' => '#'),
                array('label' => 'Master Item Penilaian', 'url' => site_url('Pkl_item_penilaian')),
                array('label' => 'Edit', 'active' => true)
            );
            $data['sidebar_menu'] = generate_sidebar_menu('admin');
            $data['content'] = $this->load->view('admin/pkl_item_penilaian/edit', $data, true);
            $this->load->view('layouts/main', $data);
        }
    }

    /**
     * Hapus item penilaian
     */
    public function delete($id) {
        $item = $this->Pkl_item_penilaian_model->get_by_id($id);
        if (!$item) {
            $this->session->set_flashdata('error', 'Data item penilaian tidak ditemukan.');
            redirect('Pkl_item_penilaian');
        }

        // Cek apakah sudah digunakan di penilaian detail
        $this->load->model('Pkl_penilaian_detail_model');
        $details = $this->Pkl_penilaian_detail_model->get_by_item($id);
        if (!empty($details)) {
            $this->session->set_flashdata('error', 'Tidak dapat menghapus item penilaian karena sudah digunakan dalam penilaian. Hapus penilaian terlebih dahulu.');
            redirect('Pkl_item_penilaian');
        }

        if ($this->Pkl_item_penilaian_model->delete($id)) {
            $this->session->set_flashdata('success', 'Item penilaian berhasil dihapus.');
        } else {
            $this->session->set_flashdata('error', 'Gagal menghapus item penilaian.');
        }
        redirect('Pkl_item_penilaian');
    }

    /**
     * Toggle aktif/non-aktif
     */
    public function toggle_aktif($id) {
        $item = $this->Pkl_item_penilaian_model->get_by_id($id);
        if (!$item) {
            $this->session->set_flashdata('error', 'Data item penilaian tidak ditemukan.');
            redirect('Pkl_item_penilaian');
        }

        $is_aktif = $item->is_aktif ? 0 : 1;
        $this->Pkl_item_penilaian_model->update($id, array('is_aktif' => $is_aktif));
        
        $status = $is_aktif ? 'diaktifkan' : 'dinonaktifkan';
        $this->session->set_flashdata('success', "Item penilaian berhasil {$status}.");
        redirect('Pkl_item_penilaian');
    }
}

