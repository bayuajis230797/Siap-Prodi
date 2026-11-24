<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Pkl_aspek_penilaian Controller
 * CRUD Master Aspek Penilaian PKL (P1-P6)
 */
class Pkl_aspek_penilaian extends MY_Controller {

    public function __construct() {
        parent::__construct();
        $this->check_role('admin');
        $this->load->model('Pkl_aspek_penilaian_model');
        $this->load->model('Pkl_item_penilaian_model');
        $this->load->library('form_validation');
    }

    /**
     * List semua aspek penilaian dengan pagination
     */
    public function index() {
        // Ambil filter
        $is_aktif_filter = $this->input->get('is_aktif');
        $ditunjukkan_filter = $this->input->get('ditunjukkan_untuk');

        // Ambil semua aspek penilaian
        $aspek_list = $this->Pkl_aspek_penilaian_model->get_all(false);
        
        // Filter jika ada
        if ($is_aktif_filter !== null && $is_aktif_filter !== '') {
            $aspek_list = array_filter($aspek_list, function($item) use ($is_aktif_filter) {
                return $item->is_aktif == $is_aktif_filter;
            });
        }
        if ($ditunjukkan_filter) {
            $aspek_list = array_filter($aspek_list, function($item) use ($ditunjukkan_filter) {
                return $item->ditunjukkan_untuk == $ditunjukkan_filter;
            });
        }
        
        // Untuk setiap aspek, hitung jumlah item
        foreach ($aspek_list as &$aspek) {
            $aspek->jumlah_item = count($this->Pkl_item_penilaian_model->get_by_aspek($aspek->id, false));
        }
        unset($aspek);

        $data = array(
            'title' => 'Master Aspek Penilaian PKL',
            'page_title' => 'Master Aspek Penilaian PKL',
            'breadcrumb' => array(
                array('label' => 'PKL', 'url' => '#'),
                array('label' => 'Master Aspek Penilaian', 'active' => true)
            ),
            'sidebar_menu' => generate_sidebar_menu('admin'),
            'aspek_list' => array_values($aspek_list),
            'total_rows' => count($aspek_list),
            'is_aktif_filter' => $is_aktif_filter,
            'ditunjukkan_filter' => $ditunjukkan_filter
        );

        $data['content'] = $this->load->view('admin/pkl_aspek_penilaian/index', $data, true);
        $this->load->view('layouts/main', $data);
    }

    /**
     * Form tambah aspek penilaian
     */
    public function create() {
        if ($this->input->method() === 'post') {
            $this->form_validation->set_rules('kode_aspek', 'Kode Aspek', 'required|trim|is_unique[pkl_aspek_penilaian.kode_aspek]');
            $this->form_validation->set_rules('nama_aspek', 'Nama Aspek', 'required|trim');
            $this->form_validation->set_rules('ditunjukkan_untuk', 'Ditunjukkan Untuk', 'required|trim');
            $this->form_validation->set_rules('urutan', 'Urutan', 'required|trim|numeric');

            if ($this->form_validation->run() == FALSE) {
                $data['title'] = 'Tambah Aspek Penilaian PKL';
                $data['page_title'] = 'Tambah Aspek Penilaian PKL';
                $data['breadcrumb'] = array(
                    array('label' => 'PKL', 'url' => '#'),
                    array('label' => 'Master Aspek Penilaian', 'url' => site_url('Pkl_aspek_penilaian')),
                    array('label' => 'Tambah', 'active' => true)
                );
                $data['sidebar_menu'] = generate_sidebar_menu('admin');
                $data['content'] = $this->load->view('admin/pkl_aspek_penilaian/create', $data, true);
                $this->load->view('layouts/main', $data);
            } else {
                $data = array(
                    'kode_aspek' => strtoupper($this->input->post('kode_aspek')),
                    'nama_aspek' => $this->input->post('nama_aspek'),
                    'bobot' => floatval($this->input->post('bobot')),
                    'ditunjukkan_untuk' => $this->input->post('ditunjukkan_untuk'),
                    'is_aktif' => $this->input->post('is_aktif') ? 1 : 0,
                    'urutan' => intval($this->input->post('urutan'))
                );

                if ($this->Pkl_aspek_penilaian_model->create($data)) {
                    $this->session->set_flashdata('success', 'Aspek penilaian berhasil ditambahkan.');
                    redirect('Pkl_aspek_penilaian');
                } else {
                    $this->session->set_flashdata('error', 'Gagal menambahkan aspek penilaian.');
                    redirect('Pkl_aspek_penilaian/create');
                }
            }
        } else {
            $data['title'] = 'Tambah Aspek Penilaian PKL';
            $data['page_title'] = 'Tambah Aspek Penilaian PKL';
            $data['breadcrumb'] = array(
                array('label' => 'PKL', 'url' => '#'),
                array('label' => 'Master Aspek Penilaian', 'url' => site_url('Pkl_aspek_penilaian')),
                array('label' => 'Tambah', 'active' => true)
            );
            $data['sidebar_menu'] = generate_sidebar_menu('admin');
            $data['content'] = $this->load->view('admin/pkl_aspek_penilaian/create', $data, true);
            $this->load->view('layouts/main', $data);
        }
    }

    /**
     * Form edit aspek penilaian
     */
    public function edit($id) {
        $aspek = $this->Pkl_aspek_penilaian_model->get_by_id($id);
        if (!$aspek) {
            $this->session->set_flashdata('error', 'Data aspek penilaian tidak ditemukan.');
            redirect('Pkl_aspek_penilaian');
        }

        if ($this->input->method() === 'post') {
            // Cek unique kode_aspek (kecuali untuk record yang sedang diedit)
            $existing = $this->Pkl_aspek_penilaian_model->get_by_kode($this->input->post('kode_aspek'));
            if ($existing && $existing->id != $id) {
                $this->form_validation->set_rules('kode_aspek', 'Kode Aspek', 'required|trim|is_unique[pkl_aspek_penilaian.kode_aspek]');
            } else {
                $this->form_validation->set_rules('kode_aspek', 'Kode Aspek', 'required|trim');
            }
            $this->form_validation->set_rules('nama_aspek', 'Nama Aspek', 'required|trim');
            $this->form_validation->set_rules('ditunjukkan_untuk', 'Ditunjukkan Untuk', 'required|trim');
            $this->form_validation->set_rules('urutan', 'Urutan', 'required|trim|numeric');

            if ($this->form_validation->run() == FALSE) {
                $data['aspek'] = $aspek;
                $data['title'] = 'Edit Aspek Penilaian PKL';
                $data['page_title'] = 'Edit Aspek Penilaian PKL';
                $data['breadcrumb'] = array(
                    array('label' => 'PKL', 'url' => '#'),
                    array('label' => 'Master Aspek Penilaian', 'url' => site_url('Pkl_aspek_penilaian')),
                    array('label' => 'Edit', 'active' => true)
                );
                $data['sidebar_menu'] = generate_sidebar_menu('admin');
                $data['content'] = $this->load->view('admin/pkl_aspek_penilaian/edit', $data, true);
                $this->load->view('layouts/main', $data);
            } else {
                $data = array(
                    'kode_aspek' => strtoupper($this->input->post('kode_aspek')),
                    'nama_aspek' => $this->input->post('nama_aspek'),
                    'bobot' => floatval($this->input->post('bobot')),
                    'ditunjukkan_untuk' => $this->input->post('ditunjukkan_untuk'),
                    'is_aktif' => $this->input->post('is_aktif') ? 1 : 0,
                    'urutan' => intval($this->input->post('urutan'))
                );

                if ($this->Pkl_aspek_penilaian_model->update($id, $data)) {
                    $this->session->set_flashdata('success', 'Aspek penilaian berhasil diperbarui.');
                    redirect('Pkl_aspek_penilaian');
                } else {
                    $this->session->set_flashdata('error', 'Gagal memperbarui aspek penilaian.');
                    redirect('Pkl_aspek_penilaian/edit/' . $id);
                }
            }
        } else {
            $data['aspek'] = $aspek;
            $data['title'] = 'Edit Aspek Penilaian PKL';
            $data['page_title'] = 'Edit Aspek Penilaian PKL';
            $data['breadcrumb'] = array(
                array('label' => 'PKL', 'url' => '#'),
                array('label' => 'Master Aspek Penilaian', 'url' => site_url('Pkl_aspek_penilaian')),
                array('label' => 'Edit', 'active' => true)
            );
            $data['sidebar_menu'] = generate_sidebar_menu('admin');
            $data['content'] = $this->load->view('admin/pkl_aspek_penilaian/edit', $data, true);
            $this->load->view('layouts/main', $data);
        }
    }

    /**
     * Hapus aspek penilaian
     */
    public function delete($id) {
        $aspek = $this->Pkl_aspek_penilaian_model->get_by_id($id);
        if (!$aspek) {
            $this->session->set_flashdata('error', 'Data aspek penilaian tidak ditemukan.');
            redirect('Pkl_aspek_penilaian');
        }

        // Cek apakah ada item penilaian yang terkait
        $items = $this->Pkl_item_penilaian_model->get_by_aspek($id, false);
        if (!empty($items)) {
            $this->session->set_flashdata('error', 'Tidak dapat menghapus aspek penilaian karena masih memiliki item penilaian. Hapus item penilaian terlebih dahulu.');
            redirect('Pkl_aspek_penilaian');
        }

        if ($this->Pkl_aspek_penilaian_model->delete($id)) {
            $this->session->set_flashdata('success', 'Aspek penilaian berhasil dihapus.');
        } else {
            $this->session->set_flashdata('error', 'Gagal menghapus aspek penilaian.');
        }
        redirect('Pkl_aspek_penilaian');
    }

    /**
     * Toggle aktif/non-aktif
     */
    public function toggle_aktif($id) {
        $aspek = $this->Pkl_aspek_penilaian_model->get_by_id($id);
        if (!$aspek) {
            $this->session->set_flashdata('error', 'Data aspek penilaian tidak ditemukan.');
            redirect('Pkl_aspek_penilaian');
        }

        $is_aktif = $aspek->is_aktif ? 0 : 1;
        $this->Pkl_aspek_penilaian_model->update($id, array('is_aktif' => $is_aktif));
        
        $status = $is_aktif ? 'diaktifkan' : 'dinonaktifkan';
        $this->session->set_flashdata('success', "Aspek penilaian berhasil {$status}.");
        redirect('Pkl_aspek_penilaian');
    }
}

