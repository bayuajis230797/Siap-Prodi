<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Mata Kuliah Controller
 * Master data mata kuliah yang akan digunakan di kurikulum
 */
class Mata_kuliah extends MY_Controller {

    public function __construct() {
        parent::__construct();
        $this->check_role('admin');
        $this->load->model('Mata_kuliah_model');
        $this->load->model('Tahun_akademik_model');
        $this->load->model('Mata_kuliah_aktif_model');
    }

    /**
     * List mata kuliah aktif berdasarkan tahun akademik aktif
     */
    public function index() {
        // Ambil tahun akademik aktif
        $tahun_aktif = $this->Tahun_akademik_model->get_aktif();
        
        if (!$tahun_aktif) {
            // Jika tidak ada tahun akademik aktif, tampilkan pesan
            $data['mata_kuliah'] = array();
            $data['pagination'] = '';
            $data['total_rows'] = 0;
            $data['tahun_aktif'] = null;
            $data['title'] = 'Mata Kuliah Aktif';
            $data['page_title'] = 'Mata Kuliah Aktif';
            $data['breadcrumb'] = array(
                array('label' => 'Mata Kuliah Aktif', 'active' => true)
            );
            $data['sidebar_menu'] = generate_sidebar_menu('admin');
            $data['content'] = $this->load->view('mata_kuliah/index', $data, true);
            $this->load->view('layouts/main', $data);
            return;
        }
        
        $this->load->library('pagination');
        
        // Ambil parameter per_page dari GET, default 10
        $per_page = $this->input->get('per_page') ? (int)$this->input->get('per_page') : 10;
        $per_page = in_array($per_page, [10, 25, 50, 100]) ? $per_page : 10;
        
        // Pagination config
        $config['base_url'] = site_url('Mata_kuliah/index');
        $config['total_rows'] = $this->Mata_kuliah_aktif_model->count_all_aktif_by_tahun($tahun_aktif->id);
        $config['per_page'] = $per_page;
        $config['uri_segment'] = 3;
        
        // Tambahkan per_page ke query string
        if ($per_page != 10) {
            $config['suffix'] = '?per_page=' . $per_page;
            $config['first_url'] = site_url('Mata_kuliah/index?per_page=' . $per_page);
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
        $config['cur_tag_open'] = '<li class="page-item active"><a class="page-link" href="#">';
        $config['cur_tag_close'] = '</a></li>';
        $config['num_tag_open'] = '<li class="page-item">';
        $config['num_tag_close'] = '</li>';
        $config['attributes'] = array('class' => 'page-link');
        
        $this->pagination->initialize($config);
        
        $page = ($this->uri->segment(3)) ? $this->uri->segment(3) : 1;
        $offset = ($page - 1) * $config['per_page'];
        
        // Ambil mata kuliah aktif untuk tahun akademik aktif
        $data['mata_kuliah'] = $this->Mata_kuliah_aktif_model->get_all_aktif_by_tahun($tahun_aktif->id, $config['per_page'], $offset);
        $data['pagination'] = $this->pagination->create_links();
        $data['total_rows'] = $config['total_rows'];
        $data['tahun_aktif'] = $tahun_aktif;
        $data['per_page'] = $per_page;
        
        $data['title'] = 'Mata Kuliah Aktif';
        $data['page_title'] = 'Mata Kuliah Aktif';
        $data['breadcrumb'] = array(
            array('label' => 'Mata Kuliah Aktif', 'active' => true)
        );
        $data['sidebar_menu'] = generate_sidebar_menu('admin');
        $data['content'] = $this->load->view('mata_kuliah/index', $data, true);
        $this->load->view('layouts/main', $data);
    }

    /**
     * Form tambah mata kuliah
     */
    public function create() {
        if ($this->input->method() === 'post') {
            $this->form_validation->set_rules('kode_mk', 'Kode Mata Kuliah', 'required|trim|is_unique[mata_kuliah.kode_mk]');
            $this->form_validation->set_rules('nama_mk', 'Nama Mata Kuliah', 'required|trim');
            $this->form_validation->set_rules('sks', 'SKS', 'required|trim|numeric|greater_than[0]');

            if ($this->form_validation->run() == FALSE) {
                $data['title'] = 'Tambah Mata Kuliah';
                $data['page_title'] = 'Tambah Mata Kuliah';
                $data['breadcrumb'] = array(
                    array('label' => 'Mata Kuliah', 'url' => site_url('Mata_kuliah')),
                    array('label' => 'Tambah', 'active' => true)
                );
                $data['sidebar_menu'] = generate_sidebar_menu('admin');
                $data['content'] = $this->load->view('mata_kuliah/create', $data, true);
                $this->load->view('layouts/main', $data);
            } else {
                $data = array(
                    'kode_mk' => strtoupper($this->input->post('kode_mk')),
                    'nama_mk' => $this->input->post('nama_mk'),
                    'sks' => $this->input->post('sks')
                );

                if ($this->Mata_kuliah_model->create($data)) {
                    $this->session->set_flashdata('success', 'Mata kuliah berhasil ditambahkan.');
                    redirect('Mata_kuliah');
                } else {
                    $this->session->set_flashdata('error', 'Gagal menambahkan mata kuliah.');
                    redirect('Mata_kuliah/create');
                }
            }
        } else {
            $data['title'] = 'Tambah Mata Kuliah';
            $data['page_title'] = 'Tambah Mata Kuliah';
            $data['breadcrumb'] = array(
                array('label' => 'Mata Kuliah', 'url' => site_url('Mata_kuliah')),
                array('label' => 'Tambah', 'active' => true)
            );
            $data['sidebar_menu'] = generate_sidebar_menu('admin');
            $data['content'] = $this->load->view('mata_kuliah/create', $data, true);
            $this->load->view('layouts/main', $data);
        }
    }

    /**
     * Form edit mata kuliah
     */
    public function edit($id) {
        $mata_kuliah = $this->Mata_kuliah_model->get_by_id($id);
        
        if (!$mata_kuliah) {
            $this->session->set_flashdata('error', 'Mata kuliah tidak ditemukan.');
            redirect('Mata_kuliah');
        }

        if ($this->input->method() === 'post') {
            // Cek apakah kode_mk berubah
            $kode_mk_baru = strtoupper($this->input->post('kode_mk'));
            $is_unique = ($kode_mk_baru == $mata_kuliah->kode_mk) ? '' : '|is_unique[mata_kuliah.kode_mk]';
            
            $this->form_validation->set_rules('kode_mk', 'Kode Mata Kuliah', 'required|trim' . $is_unique);
            $this->form_validation->set_rules('nama_mk', 'Nama Mata Kuliah', 'required|trim');
            $this->form_validation->set_rules('sks', 'SKS', 'required|trim|numeric|greater_than[0]');

            if ($this->form_validation->run() == FALSE) {
                $data['mata_kuliah'] = $mata_kuliah;
                $data['title'] = 'Edit Mata Kuliah';
                $data['page_title'] = 'Edit Mata Kuliah';
                $data['breadcrumb'] = array(
                    array('label' => 'Mata Kuliah', 'url' => site_url('Mata_kuliah')),
                    array('label' => 'Edit', 'active' => true)
                );
                $data['sidebar_menu'] = generate_sidebar_menu('admin');
                $data['content'] = $this->load->view('mata_kuliah/edit', $data, true);
                $this->load->view('layouts/main', $data);
            } else {
                $data = array(
                    'kode_mk' => strtoupper($this->input->post('kode_mk')),
                    'nama_mk' => $this->input->post('nama_mk'),
                    'sks' => $this->input->post('sks')
                );

                if ($this->Mata_kuliah_model->update($id, $data)) {
                    $this->session->set_flashdata('success', 'Mata kuliah berhasil diupdate.');
                    redirect('Mata_kuliah');
                } else {
                    $this->session->set_flashdata('error', 'Gagal mengupdate mata kuliah.');
                    redirect('Mata_kuliah/edit/' . $id);
                }
            }
        } else {
            $data['mata_kuliah'] = $mata_kuliah;
            $data['title'] = 'Edit Mata Kuliah';
            $data['page_title'] = 'Edit Mata Kuliah';
            $data['breadcrumb'] = array(
                array('label' => 'Mata Kuliah', 'url' => site_url('Mata_kuliah')),
                array('label' => 'Edit', 'active' => true)
            );
            $data['sidebar_menu'] = generate_sidebar_menu('admin');
            $data['content'] = $this->load->view('mata_kuliah/edit', $data, true);
            $this->load->view('layouts/main', $data);
        }
    }

    /**
     * Hapus mata kuliah
     */
    public function delete($id) {
        $mata_kuliah = $this->Mata_kuliah_model->get_by_id($id);
        
        if (!$mata_kuliah) {
            $this->session->set_flashdata('error', 'Mata kuliah tidak ditemukan.');
            redirect('Mata_kuliah');
        }

        // Cek apakah mata kuliah digunakan di kurikulum
        $this->load->model('Kurikulum_model');
        $this->db->where('id_mata_kuliah', $id);
        $used = $this->db->count_all_results('kurikulum_detail');
        
        if ($used > 0) {
            $this->session->set_flashdata('error', 'Mata kuliah tidak dapat dihapus karena masih digunakan di ' . $used . ' kurikulum.');
            redirect('Mata_kuliah');
        }

        if ($this->Mata_kuliah_model->delete($id)) {
            $this->session->set_flashdata('success', 'Mata kuliah berhasil dihapus.');
        } else {
            $this->session->set_flashdata('error', 'Gagal menghapus mata kuliah.');
        }
        redirect('Mata_kuliah');
    }
}
