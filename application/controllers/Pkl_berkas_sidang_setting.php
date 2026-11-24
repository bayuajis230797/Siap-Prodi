<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Pkl_berkas_sidang_setting Controller
 * CRUD Master Berkas Sidang PKL
 */
class Pkl_berkas_sidang_setting extends MY_Controller {

    public function __construct() {
        parent::__construct();
        $this->check_role('admin');
        $this->load->model('Pkl_berkas_sidang_model');
        $this->load->library('form_validation');
    }

    /**
     * List semua berkas sidang
     */
    public function index() {
        // Ambil filter
        $aktif_filter = $this->input->get('aktif');
        
        // Ambil semua berkas
        $berkas_list = $this->Pkl_berkas_sidang_model->get_all(false);
        
        // Filter jika ada
        if ($aktif_filter !== null && $aktif_filter !== '') {
            $berkas_list = array_filter($berkas_list, function($item) use ($aktif_filter) {
                return $item->aktif == $aktif_filter;
            });
        }

        $data = array(
            'title' => 'Master Berkas Sidang PKL',
            'page_title' => 'Master Berkas Sidang PKL',
            'breadcrumb' => array(
                array('label' => 'PKL', 'url' => '#'),
                array('label' => 'Master Berkas Sidang', 'active' => true)
            ),
            'sidebar_menu' => generate_sidebar_menu('admin'),
            'berkas_list' => array_values($berkas_list),
            'aktif_filter' => $aktif_filter
        );

        $data['content'] = $this->load->view('admin/pkl_berkas_sidang_setting/index', $data, true);
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
                $data['title'] = 'Tambah Berkas Sidang PKL';
                $data['page_title'] = 'Tambah Berkas Sidang PKL';
                $data['breadcrumb'] = array(
                    array('label' => 'PKL', 'url' => '#'),
                    array('label' => 'Master Berkas Sidang', 'url' => site_url('Pkl_berkas_sidang_setting')),
                    array('label' => 'Tambah', 'active' => true)
                );
                $data['sidebar_menu'] = generate_sidebar_menu('admin');
                $data['content'] = $this->load->view('admin/pkl_berkas_sidang_setting/create', $data, true);
                $this->load->view('layouts/main', $data);
            } else {
                $data = array(
                    'nama_berkas' => $this->input->post('nama_berkas'),
                    'deskripsi' => $this->input->post('deskripsi') ?: null,
                    'wajib' => $this->input->post('wajib') ? 1 : 0,
                    'urutan' => intval($this->input->post('urutan')),
                    'aktif' => $this->input->post('aktif') ? 1 : 0
                );

                if ($this->Pkl_berkas_sidang_model->create($data)) {
                    $this->session->set_flashdata('success', 'Berkas sidang berhasil ditambahkan.');
                    redirect('Pkl_berkas_sidang_setting');
                } else {
                    $this->session->set_flashdata('error', 'Gagal menambahkan berkas sidang.');
                    redirect('Pkl_berkas_sidang_setting/create');
                }
            }
        } else {
            $data['title'] = 'Tambah Berkas Sidang PKL';
            $data['page_title'] = 'Tambah Berkas Sidang PKL';
            $data['breadcrumb'] = array(
                array('label' => 'PKL', 'url' => '#'),
                array('label' => 'Master Berkas Sidang', 'url' => site_url('Pkl_berkas_sidang_setting')),
                array('label' => 'Tambah', 'active' => true)
            );
            $data['sidebar_menu'] = generate_sidebar_menu('admin');
            $data['content'] = $this->load->view('admin/pkl_berkas_sidang_setting/create', $data, true);
            $this->load->view('layouts/main', $data);
        }
    }

    /**
     * Form edit berkas sidang
     */
    public function edit($id) {
        $berkas = $this->Pkl_berkas_sidang_model->get_by_id($id);
        if (!$berkas) {
            $this->session->set_flashdata('error', 'Data berkas sidang tidak ditemukan.');
            redirect('Pkl_berkas_sidang_setting');
        }

        if ($this->input->method() === 'post') {
            $this->form_validation->set_rules('nama_berkas', 'Nama Berkas', 'required|trim');
            $this->form_validation->set_rules('urutan', 'Urutan', 'required|trim|numeric');

            if ($this->form_validation->run() == FALSE) {
                $data['berkas'] = $berkas;
                $data['title'] = 'Edit Berkas Sidang PKL';
                $data['page_title'] = 'Edit Berkas Sidang PKL';
                $data['breadcrumb'] = array(
                    array('label' => 'PKL', 'url' => '#'),
                    array('label' => 'Master Berkas Sidang', 'url' => site_url('Pkl_berkas_sidang_setting')),
                    array('label' => 'Edit', 'active' => true)
                );
                $data['sidebar_menu'] = generate_sidebar_menu('admin');
                $data['content'] = $this->load->view('admin/pkl_berkas_sidang_setting/edit', $data, true);
                $this->load->view('layouts/main', $data);
            } else {
                $data = array(
                    'nama_berkas' => $this->input->post('nama_berkas'),
                    'deskripsi' => $this->input->post('deskripsi') ?: null,
                    'wajib' => $this->input->post('wajib') ? 1 : 0,
                    'urutan' => intval($this->input->post('urutan')),
                    'aktif' => $this->input->post('aktif') ? 1 : 0
                );

                if ($this->Pkl_berkas_sidang_model->update($id, $data)) {
                    $this->session->set_flashdata('success', 'Berkas sidang berhasil diperbarui.');
                    redirect('Pkl_berkas_sidang_setting');
                } else {
                    $this->session->set_flashdata('error', 'Gagal memperbarui berkas sidang.');
                    redirect('Pkl_berkas_sidang_setting/edit/' . $id);
                }
            }
        } else {
            $data['berkas'] = $berkas;
            $data['title'] = 'Edit Berkas Sidang PKL';
            $data['page_title'] = 'Edit Berkas Sidang PKL';
            $data['breadcrumb'] = array(
                array('label' => 'PKL', 'url' => '#'),
                array('label' => 'Master Berkas Sidang', 'url' => site_url('Pkl_berkas_sidang_setting')),
                array('label' => 'Edit', 'active' => true)
            );
            $data['sidebar_menu'] = generate_sidebar_menu('admin');
            $data['content'] = $this->load->view('admin/pkl_berkas_sidang_setting/edit', $data, true);
            $this->load->view('layouts/main', $data);
        }
    }

    /**
     * Hapus berkas sidang
     */
    public function delete($id) {
        $berkas = $this->Pkl_berkas_sidang_model->get_by_id($id);
        if (!$berkas) {
            $this->session->set_flashdata('error', 'Data berkas sidang tidak ditemukan.');
            redirect('Pkl_berkas_sidang_setting');
        }

        // Cek apakah sudah digunakan di upload
        $this->load->model('Pkl_berkas_sidang_upload_model');
        $uploads = $this->db->get_where('pkl_berkas_sidang_upload', array('id_berkas_sidang' => $id))->result();
        if (!empty($uploads)) {
            $this->session->set_flashdata('error', 'Tidak dapat menghapus berkas sidang karena sudah digunakan. Nonaktifkan berkas terlebih dahulu.');
            redirect('Pkl_berkas_sidang_setting');
        }

        if ($this->Pkl_berkas_sidang_model->delete($id)) {
            $this->session->set_flashdata('success', 'Berkas sidang berhasil dihapus.');
        } else {
            $this->session->set_flashdata('error', 'Gagal menghapus berkas sidang.');
        }
        redirect('Pkl_berkas_sidang_setting');
    }

    /**
     * Toggle aktif/non-aktif
     */
    public function toggle_aktif($id) {
        $berkas = $this->Pkl_berkas_sidang_model->get_by_id($id);
        if (!$berkas) {
            $this->session->set_flashdata('error', 'Data berkas sidang tidak ditemukan.');
            redirect('Pkl_berkas_sidang_setting');
        }

        $aktif = $berkas->aktif ? 0 : 1;
        $this->Pkl_berkas_sidang_model->update($id, array('aktif' => $aktif));
        
        $status = $aktif ? 'diaktifkan' : 'dinonaktifkan';
        $this->session->set_flashdata('success', "Berkas sidang berhasil {$status}.");
        redirect('Pkl_berkas_sidang_setting');
    }
}





