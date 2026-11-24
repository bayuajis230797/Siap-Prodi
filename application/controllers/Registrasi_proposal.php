<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Registrasi_proposal Controller
 * Mengelola registrasi mahasiswa Proposal TA (untuk Sekprodi)
 */
class Registrasi_proposal extends MY_Controller {

    public function __construct() {
        parent::__construct();
        $this->check_role(array('sekprodi', 'kaprodi'));
        $this->load->model('Proposal_model');
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
        $config['base_url'] = site_url('Registrasi_proposal/index');
        $config['total_rows'] = $this->Proposal_model->count_all($id_tahun_akademik, $kelas_filter);
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
            $config['first_url'] = site_url('Registrasi_proposal/index?' . $query_string);
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

        $proposal_list = $this->Proposal_model->get_all($id_tahun_akademik, $per_page, $offset, $kelas_filter);
        
        // Ambil daftar kelas unik dari semua mahasiswa proposal untuk dropdown filter
        $all_proposal = $this->Proposal_model->get_all($id_tahun_akademik);
        $kelas_list = array();
        foreach ($all_proposal as $proposal) {
            if (!empty($proposal->kelas) && !in_array($proposal->kelas, $kelas_list)) {
                $kelas_list[] = $proposal->kelas;
            }
        }
        sort($kelas_list);

        $data = array(
            'title' => 'Registrasi Mahasiswa Proposal TA',
            'page_title' => 'Registrasi Mahasiswa Proposal TA',
            'breadcrumb' => array(array('label' => 'Registrasi Mahasiswa Proposal TA', 'active' => true)),
            'sidebar_menu' => generate_sidebar_menu(get_user_role()),
            'proposal_list' => $proposal_list,
            'tahun_aktif' => $tahun_aktif,
            'pagination' => $this->pagination->create_links(),
            'per_page' => $per_page,
            'kelas_list' => $kelas_list,
            'kelas_filter' => $kelas_filter,
            'total_rows' => $config['total_rows']
        );

        $data['content'] = $this->load->view('sekprodi/registrasi_proposal/index', $data, true);
        $this->load->view('layouts/main', $data);
    }

    public function create() {
        $this->assert_not_kaprodi('Registrasi_proposal');
        $tahun_aktif = $this->Tahun_akademik_model->get_aktif();
        if (!$tahun_aktif) {
            $this->session->set_flashdata('error', 'Tidak ada tahun akademik aktif. Silakan aktifkan tahun akademik terlebih dahulu.');
            redirect('Registrasi_proposal');
        }

        if ($this->input->method() === 'post') {
            $this->form_validation->set_rules('id_mahasiswa[]', 'Mahasiswa', 'required');

            if ($this->form_validation->run() == FALSE) {
                $data['mahasiswa_list'] = $this->Mahasiswa_model->get_all();
                $data['proposal_list'] = $this->Proposal_model->get_all($tahun_aktif->id);
                
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
                $data['title'] = 'Tambah Mahasiswa Proposal TA';
                $data['page_title'] = 'Tambah Mahasiswa Proposal TA';
                $data['breadcrumb'] = array(
                    array('label' => 'Registrasi Mahasiswa Proposal TA', 'url' => site_url('Registrasi_proposal')),
                    array('label' => 'Tambah', 'active' => true)
                );
                $data['sidebar_menu'] = generate_sidebar_menu(get_user_role());
                $data['content'] = $this->load->view('sekprodi/registrasi_proposal/create', $data, true);
                $this->load->view('layouts/main', $data);
            } else {
                $id_mahasiswa_list = $this->input->post('id_mahasiswa');
                $success_count = 0;
                $error_count = 0;

                foreach ($id_mahasiswa_list as $id_mahasiswa) {
                    // Cek apakah sudah terdaftar
                    if (!$this->Proposal_model->is_registered($id_mahasiswa, $tahun_aktif->id)) {
                        $data_insert = array(
                            'id_mahasiswa' => $id_mahasiswa,
                            'id_tahun_akademik' => $tahun_aktif->id
                        );
                        if ($this->Proposal_model->create($data_insert)) {
                            $success_count++;
                        } else {
                            $error_count++;
                        }
                    } else {
                        $error_count++;
                    }
                }

                if ($success_count > 0) {
                    $this->session->set_flashdata('success', "Berhasil mendaftarkan {$success_count} mahasiswa untuk proposal.");
                }
                if ($error_count > 0) {
                    $this->session->set_flashdata('error', "{$error_count} mahasiswa gagal didaftarkan (mungkin sudah terdaftar).");
                }

                redirect('Registrasi_proposal');
            }
        } else {
            $data['mahasiswa_list'] = $this->Mahasiswa_model->get_all();
            $data['proposal_list'] = $this->Proposal_model->get_all($tahun_aktif->id);
            
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
            $data['title'] = 'Tambah Mahasiswa Proposal TA';
            $data['page_title'] = 'Tambah Mahasiswa Proposal TA';
            $data['breadcrumb'] = array(
                array('label' => 'Registrasi Mahasiswa Proposal TA', 'url' => site_url('Registrasi_proposal')),
                array('label' => 'Tambah', 'active' => true)
            );
            $data['sidebar_menu'] = generate_sidebar_menu(get_user_role());
            $data['content'] = $this->load->view('sekprodi/registrasi_proposal/create', $data, true);
            $this->load->view('layouts/main', $data);
        }
    }

    public function view_progress($id) {
        $proposal = $this->Proposal_model->get_by_id($id);
        
        if (!$proposal) {
            $this->session->set_flashdata('error', 'Data mahasiswa proposal tidak ditemukan.');
            redirect('Registrasi_proposal');
        }
        
        // Hitung progress Proposal TA mahasiswa
        $this->load->helper('proposal');
        $tahun_aktif = $this->Tahun_akademik_model->get_by_id($proposal->id_tahun_akademik);
        $proposal_progress = calculate_proposal_progress($proposal->id_mahasiswa, $tahun_aktif);
        
        // Tambahkan data tahun akademik ke object proposal untuk ditampilkan di view
        if ($tahun_aktif) {
            $proposal->tahun_akademik = $tahun_aktif->tahun_akademik;
            $proposal->semester = $tahun_aktif->semester;
        } else {
            $proposal->tahun_akademik = '-';
            $proposal->semester = '-';
        }
        
        $data = array(
            'title' => 'Progress Proposal TA Mahasiswa',
            'page_title' => 'Progress Proposal TA Mahasiswa',
            'breadcrumb' => array(
                array('label' => 'Registrasi Mahasiswa Proposal TA', 'url' => site_url('Registrasi_proposal')),
                array('label' => 'Progress', 'active' => true)
            ),
            'sidebar_menu' => generate_sidebar_menu(get_user_role()),
            'proposal' => $proposal,
            'proposal_progress' => $proposal_progress,
            'tahun_aktif' => $tahun_aktif
        );
        
        $data['content'] = $this->load->view('sekprodi/registrasi_proposal/view_progress', $data, true);
        $this->load->view('layouts/main', $data);
    }

    public function delete($id) {
        $this->assert_not_kaprodi('Registrasi_proposal');
        if ($this->Proposal_model->delete($id)) {
            $this->session->set_flashdata('success', 'Mahasiswa berhasil dihapus dari registrasi proposal.');
        } else {
            $this->session->set_flashdata('error', 'Gagal menghapus mahasiswa dari registrasi proposal.');
        }
        redirect('Registrasi_proposal');
    }
}
