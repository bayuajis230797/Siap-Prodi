<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Pkl_berkas_klaim_setting Controller
 * CRUD Master Berkas Klaim Nilai PKL
 */
class Pkl_berkas_klaim_setting extends MY_Controller {

    public function __construct() {
        parent::__construct();
        $this->check_role('admin');
        $this->load->model('Pkl_berkas_klaim_nilai_model');
        $this->load->library('form_validation');
    }

    /**
     * List semua berkas klaim nilai
     */
    public function index() {
        // Ambil filter
        $aktif_filter = $this->input->get('aktif');
        
        // Ambil semua berkas
        $berkas_list = $this->Pkl_berkas_klaim_nilai_model->get_all(false);
        
        // Filter jika ada
        if ($aktif_filter !== null && $aktif_filter !== '') {
            $berkas_list = array_filter($berkas_list, function($item) use ($aktif_filter) {
                return $item->aktif == $aktif_filter;
            });
        }

        $data = array(
            'title' => 'Master Berkas Klaim Nilai PKL',
            'page_title' => 'Master Berkas Klaim Nilai PKL',
            'breadcrumb' => array(
                array('label' => 'PKL', 'url' => '#'),
                array('label' => 'Master Berkas Klaim Nilai', 'active' => true)
            ),
            'sidebar_menu' => generate_sidebar_menu('admin'),
            'berkas_list' => array_values($berkas_list),
            'aktif_filter' => $aktif_filter
        );

        $data['content'] = $this->load->view('admin/pkl_berkas_klaim_setting/index', $data, true);
        $this->load->view('layouts/main', $data);
    }

    /**
     * Form tambah berkas klaim nilai
     */
    public function create() {
        if ($this->input->method() === 'post') {
            $this->form_validation->set_rules('nama_berkas', 'Nama Berkas', 'required|trim');
            $this->form_validation->set_rules('urutan', 'Urutan', 'required|trim|numeric');

            if ($this->form_validation->run() == FALSE) {
                $data['title'] = 'Tambah Berkas Klaim Nilai PKL';
                $data['page_title'] = 'Tambah Berkas Klaim Nilai PKL';
                $data['breadcrumb'] = array(
                    array('label' => 'PKL', 'url' => '#'),
                    array('label' => 'Master Berkas Klaim Nilai', 'url' => site_url('Pkl_berkas_klaim_setting')),
                    array('label' => 'Tambah', 'active' => true)
                );
                $data['sidebar_menu'] = generate_sidebar_menu('admin');
                $data['content'] = $this->load->view('admin/pkl_berkas_klaim_setting/create', $data, true);
                $this->load->view('layouts/main', $data);
            } else {
                $data = array(
                    'nama_berkas' => $this->input->post('nama_berkas'),
                    'deskripsi' => $this->input->post('deskripsi') ?: null,
                    'wajib' => $this->input->post('wajib') ? 1 : 0,
                    'urutan' => intval($this->input->post('urutan')),
                    'aktif' => $this->input->post('aktif') ? 1 : 0
                );

                if ($this->Pkl_berkas_klaim_nilai_model->create($data)) {
                    $this->session->set_flashdata('success', 'Berkas klaim nilai berhasil ditambahkan.');
                    redirect('Pkl_berkas_klaim_setting');
                } else {
                    $this->session->set_flashdata('error', 'Gagal menambahkan berkas klaim nilai.');
                    redirect('Pkl_berkas_klaim_setting/create');
                }
            }
        } else {
            $data['title'] = 'Tambah Berkas Klaim Nilai PKL';
            $data['page_title'] = 'Tambah Berkas Klaim Nilai PKL';
            $data['breadcrumb'] = array(
                array('label' => 'PKL', 'url' => '#'),
                array('label' => 'Master Berkas Klaim Nilai', 'url' => site_url('Pkl_berkas_klaim_setting')),
                array('label' => 'Tambah', 'active' => true)
            );
            $data['sidebar_menu'] = generate_sidebar_menu('admin');
            $data['content'] = $this->load->view('admin/pkl_berkas_klaim_setting/create', $data, true);
            $this->load->view('layouts/main', $data);
        }
    }

    /**
     * Form edit berkas klaim nilai
     */
    public function edit($id) {
        $berkas = $this->Pkl_berkas_klaim_nilai_model->get_by_id($id);
        if (!$berkas) {
            $this->session->set_flashdata('error', 'Data berkas klaim nilai tidak ditemukan.');
            redirect('Pkl_berkas_klaim_setting');
        }

        if ($this->input->method() === 'post') {
            $this->form_validation->set_rules('nama_berkas', 'Nama Berkas', 'required|trim');
            $this->form_validation->set_rules('urutan', 'Urutan', 'required|trim|numeric');

            if ($this->form_validation->run() == FALSE) {
                $data['berkas'] = $berkas;
                $data['title'] = 'Edit Berkas Klaim Nilai PKL';
                $data['page_title'] = 'Edit Berkas Klaim Nilai PKL';
                $data['breadcrumb'] = array(
                    array('label' => 'PKL', 'url' => '#'),
                    array('label' => 'Master Berkas Klaim Nilai', 'url' => site_url('Pkl_berkas_klaim_setting')),
                    array('label' => 'Edit', 'active' => true)
                );
                $data['sidebar_menu'] = generate_sidebar_menu('admin');
                $data['content'] = $this->load->view('admin/pkl_berkas_klaim_setting/edit', $data, true);
                $this->load->view('layouts/main', $data);
            } else {
                $data = array(
                    'nama_berkas' => $this->input->post('nama_berkas'),
                    'deskripsi' => $this->input->post('deskripsi') ?: null,
                    'wajib' => $this->input->post('wajib') ? 1 : 0,
                    'urutan' => intval($this->input->post('urutan')),
                    'aktif' => $this->input->post('aktif') ? 1 : 0
                );

                if ($this->Pkl_berkas_klaim_nilai_model->update($id, $data)) {
                    $this->session->set_flashdata('success', 'Berkas klaim nilai berhasil diperbarui.');
                    redirect('Pkl_berkas_klaim_setting');
                } else {
                    $this->session->set_flashdata('error', 'Gagal memperbarui berkas klaim nilai.');
                    redirect('Pkl_berkas_klaim_setting/edit/' . $id);
                }
            }
        } else {
            $data['berkas'] = $berkas;
            $data['title'] = 'Edit Berkas Klaim Nilai PKL';
            $data['page_title'] = 'Edit Berkas Klaim Nilai PKL';
            $data['breadcrumb'] = array(
                array('label' => 'PKL', 'url' => '#'),
                array('label' => 'Master Berkas Klaim Nilai', 'url' => site_url('Pkl_berkas_klaim_setting')),
                array('label' => 'Edit', 'active' => true)
            );
            $data['sidebar_menu'] = generate_sidebar_menu('admin');
            $data['content'] = $this->load->view('admin/pkl_berkas_klaim_setting/edit', $data, true);
            $this->load->view('layouts/main', $data);
        }
    }

    /**
     * Hapus berkas klaim nilai
     */
    public function delete($id) {
        $berkas = $this->Pkl_berkas_klaim_nilai_model->get_by_id($id);
        if (!$berkas) {
            $this->session->set_flashdata('error', 'Data berkas klaim nilai tidak ditemukan.');
            redirect('Pkl_berkas_klaim_setting');
        }

        // Cek apakah sudah digunakan di upload
        $uploads = $this->db->get_where('pkl_berkas_klaim_upload', array('id_berkas_klaim_nilai' => $id))->result();
        if (!empty($uploads)) {
            $this->session->set_flashdata('error', 'Tidak dapat menghapus berkas klaim nilai karena sudah digunakan. Nonaktifkan berkas terlebih dahulu.');
            redirect('Pkl_berkas_klaim_setting');
        }

        if ($this->Pkl_berkas_klaim_nilai_model->delete($id)) {
            $this->session->set_flashdata('success', 'Berkas klaim nilai berhasil dihapus.');
        } else {
            $this->session->set_flashdata('error', 'Gagal menghapus berkas klaim nilai.');
        }
        redirect('Pkl_berkas_klaim_setting');
    }

    /**
     * Toggle aktif/non-aktif
     */
    public function toggle_aktif($id) {
        $berkas = $this->Pkl_berkas_klaim_nilai_model->get_by_id($id);
        if (!$berkas) {
            $this->session->set_flashdata('error', 'Data berkas klaim nilai tidak ditemukan.');
            redirect('Pkl_berkas_klaim_setting');
        }

        $aktif = $berkas->aktif ? 0 : 1;
        $this->Pkl_berkas_klaim_nilai_model->update($id, array('aktif' => $aktif));
        
        $status = $aktif ? 'diaktifkan' : 'dinonaktifkan';
        $this->session->set_flashdata('success', "Berkas klaim nilai berhasil {$status}.");
        redirect('Pkl_berkas_klaim_setting');
    }
}





