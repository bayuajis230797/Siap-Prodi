<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Permintaan Surat Controller (Mahasiswa)
 * Untuk mahasiswa mengajukan permintaan surat
 */
class Permintaan_surat extends MY_Controller {

    public function __construct() {
        parent::__construct();
        $this->check_role('mahasiswa');
        $this->load->model('Permintaan_surat_model');
        $this->load->model('Mahasiswa_model');
    }

    /**
     * List permintaan surat mahasiswa
     */
    public function index() {
        $this->load->library('pagination');
        
        $id_mahasiswa = $this->session->userdata('ref_id');
        
        // Pagination config
        $config['base_url'] = site_url('Permintaan_surat/index');
        $config['total_rows'] = $this->Permintaan_surat_model->count_by_mahasiswa($id_mahasiswa);
        $config['per_page'] = 10;
        $config['uri_segment'] = 3;
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
        
        $data['permintaan'] = $this->Permintaan_surat_model->get_by_mahasiswa($id_mahasiswa, $config['per_page'], $offset);
        $data['pagination'] = $this->pagination->create_links();
        $data['total_rows'] = $config['total_rows'];
        
        $data['title'] = 'Permintaan Surat';
        $data['page_title'] = 'Permintaan Surat';
        $data['breadcrumb'] = array(
            array('label' => 'Permintaan Surat', 'active' => true)
        );
        $data['sidebar_menu'] = generate_sidebar_menu('mahasiswa');
        $data['content'] = $this->load->view('mahasiswa/permintaan_surat_index', $data, true);
        $this->load->view('layouts/main', $data);
    }

    /**
     * Form tambah permintaan surat
     */
    public function create() {
        if ($this->input->method() === 'post') {
            $this->form_validation->set_rules('jenis_surat', 'Jenis Surat', 'required|trim');
            $this->form_validation->set_rules('untuk_siapa', 'Untuk Siapa', 'required|trim');
            $this->form_validation->set_rules('keperluan', 'Keperluan', 'required|trim|min_length[10]');
            $this->form_validation->set_rules('tipe_surat', 'Tipe Surat', 'required|trim|in_list[soft_copy,hard_copy]');
            
            // Validasi untuk Surat Permintaan Data Skripsi
            $jenis_surat = $this->input->post('jenis_surat');
            if ($jenis_surat == 'Surat Permintaan Data Skripsi') {
                $this->form_validation->set_rules('judul_skripsi', 'Judul Skripsi', 'required|trim');
                $this->form_validation->set_rules('lokasi_penelitian', 'Lokasi Penelitian', 'required|trim');
                
                // Validasi minimal satu metode pengumpulan data harus dipilih
                $pengambilan_data = $this->input->post('pengambilan_data');
                $observasi = $this->input->post('observasi');
                $wawancara = $this->input->post('wawancara');
                
                if (empty($pengambilan_data) && empty($observasi) && empty($wawancara)) {
                    $this->form_validation->set_rules('pengambilan_data', 'Metode Pengumpulan Data', 'required', array('required' => 'Pilih minimal satu metode pengumpulan data.'));
                }
            }
            
            // Validasi untuk Surat Izin Tempat PKL
            if ($jenis_surat == 'Surat Izin Tempat PKL') {
                $this->form_validation->set_rules('nama_tempat_pkl', 'Nama Tempat PKL', 'required|trim');
                $this->form_validation->set_rules('alamat_pkl', 'Alamat PKL', 'required|trim');
            }

            if ($this->form_validation->run() == FALSE) {
                $data['title'] = 'Ajukan Permintaan Surat';
                $data['page_title'] = 'Ajukan Permintaan Surat';
                $data['breadcrumb'] = array(
                    array('label' => 'Permintaan Surat', 'url' => site_url('Permintaan_surat')),
                    array('label' => 'Ajukan', 'active' => true)
                );
                $data['sidebar_menu'] = generate_sidebar_menu('mahasiswa');
                $data['content'] = $this->load->view('mahasiswa/permintaan_surat_create', $data, true);
                $this->load->view('layouts/main', $data);
            } else {
                $id_mahasiswa = $this->session->userdata('ref_id');
                
                // Handle upload template (opsional)
                $file_template = null;
                $nama_file_template = null;
                
                if (!empty($_FILES['file_template']['name'])) {
                    $config['upload_path'] = './uploads/surat/template/';
                    $config['allowed_types'] = 'pdf|doc|docx|jpg|jpeg|png';
                    $config['max_size'] = 5120; // 5MB
                    $config['encrypt_name'] = TRUE;
                    
                    if (!is_dir($config['upload_path'])) {
                        mkdir($config['upload_path'], 0755, true);
                    }
                    
                    $this->load->library('upload', $config);
                    
                    if ($this->upload->do_upload('file_template')) {
                        $upload_data = $this->upload->data();
                        $file_template = 'uploads/surat/template/' . $upload_data['file_name'];
                        $nama_file_template = $upload_data['orig_name'];
                    } else {
                        $this->session->set_flashdata('error', 'Gagal upload template: ' . $this->upload->display_errors());
                        redirect('Permintaan_surat/create');
                        return;
                    }
                }
                
                $data = array(
                    'id_mahasiswa' => $id_mahasiswa,
                    'jenis_surat' => $this->input->post('jenis_surat'),
                    'untuk_siapa' => $this->input->post('untuk_siapa'),
                    'keperluan' => $this->input->post('keperluan'),
                    'tipe_surat' => $this->input->post('tipe_surat'),
                    'status' => 'pending'
                );
                
                // Tambahkan field template jika ada
                if ($file_template) {
                    $data['file_template'] = $file_template;
                    $data['nama_file_template'] = $nama_file_template;
                }
                
                // Tambahkan field untuk Surat Permintaan Data Skripsi
                if ($jenis_surat == 'Surat Permintaan Data Skripsi') {
                    $data['judul_skripsi'] = $this->input->post('judul_skripsi');
                    $data['lokasi_penelitian'] = $this->input->post('lokasi_penelitian');
                    $data['pengambilan_data'] = $this->input->post('pengambilan_data') ? 1 : 0;
                    $data['observasi'] = $this->input->post('observasi') ? 1 : 0;
                    $data['wawancara'] = $this->input->post('wawancara') ? 1 : 0;
                }
                
                // Tambahkan field untuk Surat Izin Tempat PKL
                if ($jenis_surat == 'Surat Izin Tempat PKL') {
                    $data['nama_tempat_pkl'] = $this->input->post('nama_tempat_pkl');
                    $data['alamat_pkl'] = $this->input->post('alamat_pkl');
                }

                if ($this->Permintaan_surat_model->create($data)) {
                    $this->load->helper(array('notifikasi', 'sekprodi_badge'));
                    notify_sekprodi(
                        'Permintaan Surat Baru',
                        'Ada permintaan surat baru yang menunggu proses sekprodi.',
                        site_url('Permintaan_surat_sekprodi'),
                        'permintaan_surat'
                    );
                    $this->session->set_flashdata('success', 'Permintaan surat berhasil diajukan. Silakan tunggu proses dari sekprodi.');
                    redirect('Permintaan_surat');
                } else {
                    $this->session->set_flashdata('error', 'Gagal mengajukan permintaan surat.');
                    redirect('Permintaan_surat/create');
                }
            }
        } else {
            $data['title'] = 'Ajukan Permintaan Surat';
            $data['page_title'] = 'Ajukan Permintaan Surat';
            $data['breadcrumb'] = array(
                array('label' => 'Permintaan Surat', 'url' => site_url('Permintaan_surat')),
                array('label' => 'Ajukan', 'active' => true)
            );
            $data['sidebar_menu'] = generate_sidebar_menu('mahasiswa');
            $data['content'] = $this->load->view('mahasiswa/permintaan_surat_create', $data, true);
            $this->load->view('layouts/main', $data);
        }
    }

    /**
     * Detail permintaan surat
     */
    public function detail($id) {
        $id_mahasiswa = $this->session->userdata('ref_id');
        $permintaan = $this->Permintaan_surat_model->get_by_id($id);
        
        if (!$permintaan || $permintaan->id_mahasiswa != $id_mahasiswa) {
            $this->session->set_flashdata('error', 'Permintaan surat tidak ditemukan.');
            redirect('Permintaan_surat');
        }
        
        $data['permintaan'] = $permintaan;
        $data['title'] = 'Detail Permintaan Surat';
        $data['page_title'] = 'Detail Permintaan Surat';
        $data['breadcrumb'] = array(
            array('label' => 'Permintaan Surat', 'url' => site_url('Permintaan_surat')),
            array('label' => 'Detail', 'active' => true)
        );
        $data['sidebar_menu'] = generate_sidebar_menu('mahasiswa');
        $data['content'] = $this->load->view('mahasiswa/permintaan_surat_detail', $data, true);
        $this->load->view('layouts/main', $data);
    }

    /**
     * Download file surat
     */
    public function download($id) {
        $id_mahasiswa = $this->session->userdata('ref_id');
        $permintaan = $this->Permintaan_surat_model->get_by_id($id);
        
        if (!$permintaan || $permintaan->id_mahasiswa != $id_mahasiswa) {
            $this->session->set_flashdata('error', 'Permintaan surat tidak ditemukan.');
            redirect('Permintaan_surat');
        }
        
        if (!$permintaan->file_surat || $permintaan->status != 'selesai') {
            $this->session->set_flashdata('error', 'File surat belum tersedia.');
            redirect('Permintaan_surat/detail/' . $id);
        }
        
        $file_path = FCPATH . $permintaan->file_surat;
        
        if (!file_exists($file_path)) {
            $this->session->set_flashdata('error', 'File surat tidak ditemukan.');
            redirect('Permintaan_surat/detail/' . $id);
        }
        
        $this->load->helper('download');
        $file_name = $permintaan->nama_file_surat ?: basename($file_path);
        force_download($file_name, file_get_contents($file_path));
    }
}










