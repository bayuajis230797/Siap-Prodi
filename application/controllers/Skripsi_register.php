<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Skripsi_register Controller
 * Mengelola registrasi mahasiswa Skripsi (untuk Sekprodi)
 */
class Skripsi_register extends MY_Controller {

    public function __construct() {
        parent::__construct();
        $this->check_role('sekprodi');
        $this->load->model('Skripsi_model');
        $this->load->model('Mahasiswa_model');
        $this->load->model('Tahun_akademik_model');
        $this->load->library('pagination');
        $this->load->library('form_validation');
    }

    public function index() {
        $tahun_aktif = $this->Tahun_akademik_model->get_aktif();
        $id_tahun_akademik = $tahun_aktif ? $tahun_aktif->id : null;

        // Ambil parameter per_page dari GET, default 10
        $per_page = $this->input->get('per_page') ? (int)$this->input->get('per_page') : 10;
        $per_page = in_array($per_page, [10, 25, 50, 100]) ? $per_page : 10;
        
        // Ambil filter kelas dari GET parameter
        $kelas_filter = $this->input->get('kelas');

        // Pagination config
        $config['base_url'] = site_url('Skripsi_register/index');
        $config['total_rows'] = $this->Skripsi_model->count_all($id_tahun_akademik, $kelas_filter);
        $config['per_page'] = $per_page;
        $config['uri_segment'] = 3;

        // Build query string untuk pagination
        $query_params = array_filter(array(
            'per_page' => ($per_page != 10 ? $per_page : null),
            'kelas' => $kelas_filter
        ));
        $query_string = http_build_query($query_params);
        if ($query_string) {
            $config['suffix'] = '?' . $query_string;
            $config['first_url'] = site_url('Skripsi_register/index?' . $query_string);
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
        $page = ($this->uri->segment(3)) ? $this->uri->segment(3) : 1;
        $offset = ($page - 1) * $per_page;

        $skripsi_list = $this->Skripsi_model->get_all($id_tahun_akademik, $per_page, $offset, $kelas_filter);
        
        // Ambil daftar kelas unik dari semua mahasiswa skripsi untuk dropdown filter
        $all_skripsi = $this->Skripsi_model->get_all($id_tahun_akademik);
        $kelas_list = array();
        foreach ($all_skripsi as $skripsi) {
            if (!empty($skripsi->kelas) && !in_array($skripsi->kelas, $kelas_list)) {
                $kelas_list[] = $skripsi->kelas;
            }
        }
        sort($kelas_list);

        $data = array(
            'title' => 'Daftar Mahasiswa Skripsi',
            'page_title' => 'Daftar Mahasiswa Skripsi',
            'breadcrumb' => array(array('label' => 'Daftar Mahasiswa Skripsi', 'active' => true)),
            'sidebar_menu' => generate_sidebar_menu('sekprodi'),
            'skripsi_list' => $skripsi_list,
            'tahun_aktif' => $tahun_aktif,
            'pagination' => $this->pagination->create_links(),
            'per_page' => $per_page,
            'kelas_list' => $kelas_list,
            'kelas_filter' => $kelas_filter,
            'total_rows' => $config['total_rows']
        );

        $data['content'] = $this->load->view('skripsi_register/index', $data, true);
        $this->load->view('layouts/main', $data);
    }

    public function create() {
        $tahun_aktif = $this->Tahun_akademik_model->get_aktif();
        if (!$tahun_aktif) {
            $this->session->set_flashdata('error', 'Tidak ada tahun akademik aktif.');
            redirect('Skripsi_register');
        }

        if ($this->input->method() === 'post') {
            $this->form_validation->set_rules('id_mahasiswa[]', 'Mahasiswa', 'required');

            if ($this->form_validation->run() == FALSE) {
                $data['mahasiswa_list'] = $this->Mahasiswa_model->get_all();
                $data['skripsi_list'] = $this->Skripsi_model->get_all($tahun_aktif->id);
                
                // Ambil daftar kelas unik dari semua mahasiswa untuk dropdown filter
                $kelas_list = array();
                foreach ($data['mahasiswa_list'] as $mhs) {
                    if (!empty($mhs->kelas) && !in_array($mhs->kelas, $kelas_list)) {
                        $kelas_list[] = $mhs->kelas;
                    }
                }
                sort($kelas_list);
                $data['kelas_list'] = $kelas_list;
                
                $data['tahun_aktif'] = $tahun_aktif;
                $data['title'] = 'Tambah Mahasiswa Skripsi';
                $data['page_title'] = 'Tambah Mahasiswa Skripsi';
                $data['breadcrumb'] = array(
                    array('label' => 'Daftar Mahasiswa Skripsi', 'url' => site_url('Skripsi_register')),
                    array('label' => 'Tambah', 'active' => true)
                );
                $data['sidebar_menu'] = generate_sidebar_menu('sekprodi');
                $data['content'] = $this->load->view('skripsi_register/create', $data, true);
                $this->load->view('layouts/main', $data);
            } else {
                $id_mahasiswa_list = $this->input->post('id_mahasiswa');
                $success_count = 0;
                $error_count = 0;

                foreach ($id_mahasiswa_list as $id_mahasiswa) {
                    // Cek apakah sudah terdaftar
                    if (!$this->Skripsi_model->is_registered($id_mahasiswa, $tahun_aktif->id)) {
                        $data_insert = array(
                            'id_mahasiswa' => $id_mahasiswa,
                            'id_tahun_akademik' => $tahun_aktif->id,
                            'status' => 'terdaftar'
                        );
                        if ($this->Skripsi_model->create($data_insert)) {
                            $success_count++;
                        } else {
                            $error_count++;
                        }
                    } else {
                        $error_count++;
                    }
                }

                if ($success_count > 0) {
                    $this->session->set_flashdata('success', "Berhasil menambahkan {$success_count} mahasiswa Skripsi.");
                }
                if ($error_count > 0) {
                    $this->session->set_flashdata('error', "{$error_count} mahasiswa sudah terdaftar atau gagal ditambahkan.");
                }
                redirect('Skripsi_register');
            }
        } else {
            $data['mahasiswa_list'] = $this->Mahasiswa_model->get_all();
            $data['skripsi_list'] = $this->Skripsi_model->get_all($tahun_aktif->id);
            
            // Ambil daftar kelas unik dari semua mahasiswa untuk dropdown filter
            $kelas_list = array();
            foreach ($data['mahasiswa_list'] as $mhs) {
                if (!empty($mhs->kelas) && !in_array($mhs->kelas, $kelas_list)) {
                    $kelas_list[] = $mhs->kelas;
                }
            }
            sort($kelas_list);
            $data['kelas_list'] = $kelas_list;
            
            $data['tahun_aktif'] = $tahun_aktif;
            $data['title'] = 'Tambah Mahasiswa Skripsi';
            $data['page_title'] = 'Tambah Mahasiswa Skripsi';
            $data['breadcrumb'] = array(
                array('label' => 'Daftar Mahasiswa Skripsi', 'url' => site_url('Skripsi_register')),
                array('label' => 'Tambah', 'active' => true)
            );
            $data['sidebar_menu'] = generate_sidebar_menu('sekprodi');
            $data['content'] = $this->load->view('skripsi_register/create', $data, true);
            $this->load->view('layouts/main', $data);
        }
    }
}



