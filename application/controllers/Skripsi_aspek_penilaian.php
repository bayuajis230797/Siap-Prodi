<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Skripsi_aspek_penilaian Controller
 * CRUD Master Aspek Penilaian Skripsi (P1 dan P2)
 */
class Skripsi_aspek_penilaian extends MY_Controller {

    public function __construct() {
        parent::__construct();
        $this->check_role('admin');
        $this->load->model('Skripsi_aspek_penilaian_model');
        $this->load->model('Skripsi_item_penilaian_model');
        $this->load->library('form_validation');
    }

    /**
     * List semua aspek penilaian
     */
    public function index() {
        // Ambil filter
        $is_aktif_filter = $this->input->get('is_aktif');
        $ditunjukkan_filter = $this->input->get('ditunjukkan_untuk');

        // Ambil semua aspek penilaian
        $aspek_list = $this->Skripsi_aspek_penilaian_model->get_all(false);
        
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
        
        // Untuk setiap aspek, hitung jumlah item dan total bobot
        foreach ($aspek_list as &$aspek) {
            $items = $this->Skripsi_item_penilaian_model->get_by_aspek($aspek->id, false);
            $aspek->jumlah_item = count($items);
            $total_bobot = 0;
            foreach ($items as $item) {
                $total_bobot += floatval($item->bobot);
            }
            $aspek->total_bobot = $total_bobot;
        }
        unset($aspek);

        $data = array(
            'title' => 'Setting Aspek Penilaian Skripsi',
            'page_title' => 'Setting Aspek Penilaian Skripsi',
            'breadcrumb' => array(
                array('label' => 'Skripsi', 'url' => '#'),
                array('label' => 'Setting Aspek Penilaian', 'active' => true)
            ),
            'sidebar_menu' => generate_sidebar_menu('admin'),
            'aspek_list' => array_values($aspek_list),
            'total_rows' => count($aspek_list),
            'is_aktif_filter' => $is_aktif_filter,
            'ditunjukkan_filter' => $ditunjukkan_filter
        );

        $data['content'] = $this->load->view('admin/skripsi_aspek_penilaian/index', $data, true);
        $this->load->view('layouts/main', $data);
    }

    /**
     * Form tambah aspek penilaian
     */
    public function create() {
        if ($this->input->method() === 'post') {
            $this->form_validation->set_rules('kode_aspek', 'Kode Aspek', 'required|trim|is_unique[skripsi_aspek_penilaian.kode]');
            $this->form_validation->set_rules('nama_aspek', 'Nama Aspek', 'required|trim');
            $this->form_validation->set_rules('ditunjukkan_untuk', 'Ditunjukkan Untuk', 'required|trim');

            if ($this->form_validation->run() == FALSE) {
                $data['title'] = 'Tambah Aspek Penilaian Skripsi';
                $data['page_title'] = 'Tambah Aspek Penilaian Skripsi';
                $data['breadcrumb'] = array(
                    array('label' => 'Skripsi', 'url' => '#'),
                    array('label' => 'Setting Aspek Penilaian', 'url' => site_url('Skripsi_aspek_penilaian')),
                    array('label' => 'Tambah', 'active' => true)
                );
                $data['sidebar_menu'] = generate_sidebar_menu('admin');
                $data['content'] = $this->load->view('admin/skripsi_aspek_penilaian/create', $data, true);
                $this->load->view('layouts/main', $data);
            } else {
                $data = array(
                    'kode' => strtoupper($this->input->post('kode_aspek')),
                    'nama' => $this->input->post('nama_aspek'),
                    'tipe_penilai' => $this->input->post('ditunjukkan_untuk'),
                    'aktif' => $this->input->post('is_aktif') ? 1 : 0
                );

                if ($this->Skripsi_aspek_penilaian_model->create($data)) {
                    $this->session->set_flashdata('success', 'Aspek penilaian berhasil ditambahkan.');
                    redirect('Skripsi_aspek_penilaian');
                } else {
                    $this->session->set_flashdata('error', 'Gagal menambahkan aspek penilaian.');
                    redirect('Skripsi_aspek_penilaian/create');
                }
            }
        } else {
            $data['title'] = 'Tambah Aspek Penilaian Skripsi';
            $data['page_title'] = 'Tambah Aspek Penilaian Skripsi';
            $data['breadcrumb'] = array(
                array('label' => 'Skripsi', 'url' => '#'),
                array('label' => 'Setting Aspek Penilaian', 'url' => site_url('Skripsi_aspek_penilaian')),
                array('label' => 'Tambah', 'active' => true)
            );
            $data['sidebar_menu'] = generate_sidebar_menu('admin');
            $data['content'] = $this->load->view('admin/skripsi_aspek_penilaian/create', $data, true);
            $this->load->view('layouts/main', $data);
        }
    }

    /**
     * Form edit aspek penilaian
     */
    public function edit($id) {
        $aspek = $this->Skripsi_aspek_penilaian_model->get_by_id($id);
        if (!$aspek) {
            $this->session->set_flashdata('error', 'Data aspek penilaian tidak ditemukan.');
            redirect('Skripsi_aspek_penilaian');
        }

        if ($this->input->method() === 'post') {
            // Cek unique kode_aspek (kecuali untuk record yang sedang diedit)
            $existing = $this->Skripsi_aspek_penilaian_model->get_by_kode($this->input->post('kode_aspek'));
            if ($existing && $existing->id != $id) {
                $this->form_validation->set_rules('kode_aspek', 'Kode Aspek', 'required|trim|is_unique[skripsi_aspek_penilaian.kode]');
            } else {
                $this->form_validation->set_rules('kode_aspek', 'Kode Aspek', 'required|trim');
            }
            $this->form_validation->set_rules('nama_aspek', 'Nama Aspek', 'required|trim');
            $this->form_validation->set_rules('ditunjukkan_untuk', 'Ditunjukkan Untuk', 'required|trim');
            $this->form_validation->set_rules('urutan', 'Urutan', 'required|trim|numeric');

            if ($this->form_validation->run() == FALSE) {
                $data['aspek'] = $aspek;
                $data['title'] = 'Edit Aspek Penilaian Skripsi';
                $data['page_title'] = 'Edit Aspek Penilaian Skripsi';
                $data['breadcrumb'] = array(
                    array('label' => 'Skripsi', 'url' => '#'),
                    array('label' => 'Setting Aspek Penilaian', 'url' => site_url('Skripsi_aspek_penilaian')),
                    array('label' => 'Edit', 'active' => true)
                );
                $data['sidebar_menu'] = generate_sidebar_menu('admin');
                $data['content'] = $this->load->view('admin/skripsi_aspek_penilaian/edit', $data, true);
                $this->load->view('layouts/main', $data);
            } else {
                $data = array(
                    'kode' => strtoupper($this->input->post('kode_aspek')),
                    'nama' => $this->input->post('nama_aspek'),
                    'tipe_penilai' => $this->input->post('ditunjukkan_untuk'),
                    'aktif' => $this->input->post('is_aktif') ? 1 : 0
                );

                if ($this->Skripsi_aspek_penilaian_model->update($id, $data)) {
                    $this->session->set_flashdata('success', 'Aspek penilaian berhasil diperbarui.');
                    redirect('Skripsi_aspek_penilaian');
                } else {
                    $this->session->set_flashdata('error', 'Gagal memperbarui aspek penilaian.');
                    redirect('Skripsi_aspek_penilaian/edit/' . $id);
                }
            }
        } else {
            $data['aspek'] = $aspek;
            $data['title'] = 'Edit Aspek Penilaian Skripsi';
            $data['page_title'] = 'Edit Aspek Penilaian Skripsi';
            $data['breadcrumb'] = array(
                array('label' => 'Skripsi', 'url' => '#'),
                array('label' => 'Setting Aspek Penilaian', 'url' => site_url('Skripsi_aspek_penilaian')),
                array('label' => 'Edit', 'active' => true)
            );
            $data['sidebar_menu'] = generate_sidebar_menu('admin');
            $data['content'] = $this->load->view('admin/skripsi_aspek_penilaian/edit', $data, true);
            $this->load->view('layouts/main', $data);
        }
    }

    /**
     * Hapus aspek penilaian
     */
    public function delete($id) {
        if ($this->Skripsi_aspek_penilaian_model->delete($id)) {
            $this->session->set_flashdata('success', 'Aspek penilaian berhasil dihapus.');
        } else {
            $this->session->set_flashdata('error', 'Gagal menghapus aspek penilaian.');
        }
        redirect('Skripsi_aspek_penilaian');
    }
}

