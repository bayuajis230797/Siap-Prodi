<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Skripsi_berkas_sidang_setting Controller
 * CRUD Master Berkas Sidang Skripsi (untuk Admin)
 */
class Skripsi_berkas_sidang_setting extends MY_Controller {

    public function __construct() {
        parent::__construct();
        $this->check_role('admin');
        $this->load->model('Skripsi_berkas_sidang_model');
        $this->load->library('form_validation');
    }

    /**
     * List semua berkas sidang
     */
    public function index() {
        $aktif_filter = $this->input->get('aktif');
        $tahap_filter = $this->input->get('tahap');
        
        $berkas_list = $this->Skripsi_berkas_sidang_model->get_all(false, $tahap_filter);
        
        if ($aktif_filter !== null && $aktif_filter !== '') {
            $berkas_list = array_filter($berkas_list, function($item) use ($aktif_filter) {
                return $item->aktif == $aktif_filter;
            });
        }

        $data = array(
            'title' => 'Setting Berkas Sidang Skripsi',
            'page_title' => 'Setting Berkas Sidang Skripsi',
            'breadcrumb' => array(
                array('label' => 'Skripsi', 'url' => '#'),
                array('label' => 'Setting Berkas Sidang', 'active' => true)
            ),
            'sidebar_menu' => generate_sidebar_menu('admin'),
            'berkas_list' => array_values($berkas_list),
            'aktif_filter' => $aktif_filter,
            'tahap_filter' => $tahap_filter
        );

        $data['content'] = $this->load->view('admin/skripsi_berkas_sidang_setting/index', $data, true);
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
                $data['title'] = 'Tambah Berkas Sidang Skripsi';
                $data['page_title'] = 'Tambah Berkas Sidang Skripsi';
                $data['breadcrumb'] = array(
                    array('label' => 'Skripsi', 'url' => '#'),
                    array('label' => 'Setting Berkas Sidang', 'url' => site_url('Skripsi_berkas_sidang_setting')),
                    array('label' => 'Tambah', 'active' => true)
                );
                $data['sidebar_menu'] = generate_sidebar_menu('admin');
                $data['content'] = $this->load->view('admin/skripsi_berkas_sidang_setting/create', $data, true);
                $this->load->view('layouts/main', $data);
            } else {
                $data = array(
                    'nama_berkas' => $this->input->post('nama_berkas'),
                    'deskripsi' => $this->input->post('deskripsi') ?: null,
                    'wajib' => $this->input->post('wajib') ? 1 : 0,
                    'urutan' => intval($this->input->post('urutan')),
                    'aktif' => $this->input->post('aktif') ? 1 : 0
                );
                if ($this->db->field_exists('tahap', 'skripsi_berkas_sidang')) {
                    $data['tahap'] = $this->input->post('tahap') ?: 'verifikasi';
                }

                if ($this->Skripsi_berkas_sidang_model->create($data)) {
                    $this->session->set_flashdata('success', 'Berkas sidang berhasil ditambahkan.');
                    redirect('Skripsi_berkas_sidang_setting');
                } else {
                    $this->session->set_flashdata('error', 'Gagal menambahkan berkas sidang.');
                    redirect('Skripsi_berkas_sidang_setting/create');
                }
            }
        } else {
            $data['title'] = 'Tambah Berkas Sidang Skripsi';
            $data['page_title'] = 'Tambah Berkas Sidang Skripsi';
            $data['breadcrumb'] = array(
                array('label' => 'Skripsi', 'url' => '#'),
                array('label' => 'Setting Berkas Sidang', 'url' => site_url('Skripsi_berkas_sidang_setting')),
                array('label' => 'Tambah', 'active' => true)
            );
            $data['sidebar_menu'] = generate_sidebar_menu('admin');
            $data['content'] = $this->load->view('admin/skripsi_berkas_sidang_setting/create', $data, true);
            $this->load->view('layouts/main', $data);
        }
    }

    /**
     * Form edit berkas sidang
     */
    public function edit($id) {
        $berkas = $this->Skripsi_berkas_sidang_model->get_by_id($id);
        if (!$berkas) {
            $this->session->set_flashdata('error', 'Data berkas tidak ditemukan.');
            redirect('Skripsi_berkas_sidang_setting');
        }

        if ($this->input->method() === 'post') {
            $this->form_validation->set_rules('nama_berkas', 'Nama Berkas', 'required|trim');
            $this->form_validation->set_rules('urutan', 'Urutan', 'required|trim|numeric');

            if ($this->form_validation->run() == FALSE) {
                $data['berkas'] = $berkas;
                $data['title'] = 'Edit Berkas Sidang Skripsi';
                $data['page_title'] = 'Edit Berkas Sidang Skripsi';
                $data['breadcrumb'] = array(
                    array('label' => 'Skripsi', 'url' => '#'),
                    array('label' => 'Setting Berkas Sidang', 'url' => site_url('Skripsi_berkas_sidang_setting')),
                    array('label' => 'Edit', 'active' => true)
                );
                $data['sidebar_menu'] = generate_sidebar_menu('admin');
                $data['content'] = $this->load->view('admin/skripsi_berkas_sidang_setting/edit', $data, true);
                $this->load->view('layouts/main', $data);
            } else {
                $data = array(
                    'nama_berkas' => $this->input->post('nama_berkas'),
                    'deskripsi' => $this->input->post('deskripsi') ?: null,
                    'wajib' => $this->input->post('wajib') ? 1 : 0,
                    'urutan' => intval($this->input->post('urutan')),
                    'aktif' => $this->input->post('aktif') ? 1 : 0
                );
                if ($this->db->field_exists('tahap', 'skripsi_berkas_sidang')) {
                    $data['tahap'] = $this->input->post('tahap') ?: 'verifikasi';
                }

                if ($this->Skripsi_berkas_sidang_model->update($id, $data)) {
                    $this->session->set_flashdata('success', 'Berkas sidang berhasil diperbarui.');
                    redirect('Skripsi_berkas_sidang_setting');
                } else {
                    $this->session->set_flashdata('error', 'Gagal memperbarui berkas sidang.');
                    redirect('Skripsi_berkas_sidang_setting/edit/' . $id);
                }
            }
        } else {
            $data['berkas'] = $berkas;
            $data['title'] = 'Edit Berkas Sidang Skripsi';
            $data['page_title'] = 'Edit Berkas Sidang Skripsi';
            $data['breadcrumb'] = array(
                array('label' => 'Skripsi', 'url' => '#'),
                array('label' => 'Setting Berkas Sidang', 'url' => site_url('Skripsi_berkas_sidang_setting')),
                array('label' => 'Edit', 'active' => true)
            );
            $data['sidebar_menu'] = generate_sidebar_menu('admin');
            $data['content'] = $this->load->view('admin/skripsi_berkas_sidang_setting/edit', $data, true);
            $this->load->view('layouts/main', $data);
        }
    }

    /**
     * Hapus berkas sidang
     */
    public function delete($id) {
        if ($this->Skripsi_berkas_sidang_model->delete($id)) {
            $this->session->set_flashdata('success', 'Berkas sidang berhasil dihapus.');
        } else {
            $this->session->set_flashdata('error', 'Gagal menghapus berkas sidang.');
        }
        redirect('Skripsi_berkas_sidang_setting');
    }
}


