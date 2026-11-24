<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Arsip Controller
 * Mengelola arsip berkas masuk dan keluar (Sekprodi; Kaprodi baca saja)
 */
class Arsip extends MY_Controller {

    public function __construct() {
        parent::__construct();
        $this->check_role(array('sekprodi', 'kaprodi'));
        $this->load->model('Arsip_berkas_model');
        $this->load->library('form_validation');
        $this->load->library('pagination');
    }

    /**
     * List semua arsip dengan pagination
     */
    public function index() {
        // Filter jenis (masuk/keluar)
        $jenis_filter = $this->input->get('jenis');
        $tanggal_dari = $this->input->get('tanggal_dari');
        $tanggal_sampai = $this->input->get('tanggal_sampai');
        
        // Ambil parameter per_page dari GET, default 10
        $per_page = $this->input->get('per_page') ? (int)$this->input->get('per_page') : 10;
        $per_page = in_array($per_page, [10, 25, 50, 100]) ? $per_page : 10;
        
        // Pagination config
        $config['base_url'] = site_url('Arsip/index');
        $config['total_rows'] = $this->Arsip_berkas_model->count_all($jenis_filter, $tanggal_dari, $tanggal_sampai);
        $config['per_page'] = $per_page;
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
        
        // Tambahkan filter dan per_page ke query string
        $query_params = array_filter(array(
            'jenis' => $jenis_filter, 
            'tanggal_dari' => $tanggal_dari,
            'tanggal_sampai' => $tanggal_sampai,
            'per_page' => ($per_page != 10 ? $per_page : null)
        ));
        $query_string = http_build_query($query_params);
        if ($query_string) {
            $config['suffix'] = '?' . $query_string;
            $config['first_url'] = site_url('Arsip/index?' . $query_string);
        }
        
        $this->pagination->initialize($config);
        
        $page = ($this->uri->segment(3)) ? $this->uri->segment(3) : 1;
        $offset = ($page - 1) * $config['per_page'];
        
        $data['arsip_list'] = $this->Arsip_berkas_model->get_all($jenis_filter, $config['per_page'], $offset, $tanggal_dari, $tanggal_sampai);
        $data['pagination'] = $this->pagination->create_links();
        $data['total_rows'] = $config['total_rows'];
        $data['jenis_filter'] = $jenis_filter;
        $data['tanggal_dari'] = $tanggal_dari;
        $data['tanggal_sampai'] = $tanggal_sampai;
        $data['per_page'] = $per_page;
        
        $data['title'] = 'Arsip Berkas';
        $data['page_title'] = 'Arsip Berkas Masuk & Keluar';
        $data['breadcrumb'] = array(
            array('label' => 'Arsip Berkas', 'active' => true)
        );
        $data['sidebar_menu'] = generate_sidebar_menu($this->session->userdata('role'));
        $data['content'] = $this->load->view('sekprodi/arsip_index', $data, true);
        $this->load->view('layouts/main', $data);
    }

    /**
     * Form tambah arsip dengan link Google Drive
     */
    public function create() {
        $this->assert_not_kaprodi('Arsip');
        if ($this->input->method() === 'post') {
            $this->form_validation->set_rules('jenis', 'Jenis', 'required|in_list[masuk,keluar]');
            $this->form_validation->set_rules('nomor_surat', 'Nomor Surat', 'trim');
            $this->form_validation->set_rules('tanggal_surat', 'Tanggal Surat', 'required');
            $this->form_validation->set_rules('perihal', 'Perihal', 'required|trim|min_length[5]');
            $this->form_validation->set_rules('keterangan', 'Keterangan', 'trim');
            $this->form_validation->set_rules('nama_file', 'Nama File', 'trim|max_length[255]');
            
            $jenis = $this->input->post('jenis');
            if ($jenis === 'masuk') {
                $this->form_validation->set_rules('pengirim', 'Pengirim', 'required|trim');
            } else {
                $this->form_validation->set_rules('penerima', 'Penerima', 'required|trim');
            }
            
            if ($this->form_validation->run() == FALSE) {
                $data['title'] = 'Tambah Arsip Berkas';
                $data['page_title'] = 'Tambah Arsip Berkas';
                $data['breadcrumb'] = array(
                    array('label' => 'Arsip Berkas', 'url' => site_url('Arsip')),
                    array('label' => 'Tambah', 'active' => true)
                );
                $data['sidebar_menu'] = generate_sidebar_menu($this->session->userdata('role'));
                $data['content'] = $this->load->view('sekprodi/arsip_create', $data, true);
                $this->load->view('layouts/main', $data);
                return;
            }
            
            $this->load->helper('google_drive');
            require_google_drive_or_redirect();
            $this->load->library('Upload_to_drive');
            $upload = $this->upload_to_drive->handle_upload('file_berkas', $this->upload_to_drive->default_options(
                array('SIAP', 'Arsip'),
                array()
            ));
            if (empty($upload['ok'])) {
                $this->session->set_flashdata('error', isset($upload['message']) ? $upload['message'] : 'Gagal upload berkas.');
                redirect('Arsip/create');
            }
            $nama_file = trim($this->input->post('nama_file')) ?: (!empty($upload['file_name']) ? $upload['file_name'] : 'Berkas Arsip');
            
            $data = array(
                'jenis' => $jenis,
                'nomor_surat' => $this->input->post('nomor_surat') ? $this->input->post('nomor_surat') : null,
                'tanggal_surat' => $this->input->post('tanggal_surat'),
                'pengirim' => $jenis === 'masuk' ? $this->input->post('pengirim') : null,
                'penerima' => $jenis === 'keluar' ? $this->input->post('penerima') : null,
                'perihal' => $this->input->post('perihal'),
                'file_path' => $upload['webViewLink'],
                'file_name' => $nama_file,
                'file_type' => isset($upload['mimeType']) ? $upload['mimeType'] : null,
                'file_size' => isset($_FILES['file_berkas']['size']) ? round($_FILES['file_berkas']['size'] / 1024) : null,
                'keterangan' => $this->input->post('keterangan') ? $this->input->post('keterangan') : null,
                'created_by' => $this->session->userdata('user_id')
            );
            
            if ($this->Arsip_berkas_model->create($data)) {
                $this->session->set_flashdata('success', 'Arsip berkas berhasil ditambahkan.');
                redirect('Arsip');
            } else {
                $this->session->set_flashdata('error', 'Gagal menambahkan arsip berkas.');
                redirect('Arsip/create');
            }
        } else {
            $data['title'] = 'Tambah Arsip Berkas';
            $data['page_title'] = 'Tambah Arsip Berkas';
            $data['breadcrumb'] = array(
                array('label' => 'Arsip Berkas', 'url' => site_url('Arsip')),
                array('label' => 'Tambah', 'active' => true)
            );
            $data['sidebar_menu'] = generate_sidebar_menu($this->session->userdata('role'));
            $data['content'] = $this->load->view('sekprodi/arsip_create', $data, true);
            $this->load->view('layouts/main', $data);
        }
    }

    /**
     * Form edit arsip dengan link Google Drive
     */
    public function edit($id) {
        $this->assert_not_kaprodi('Arsip');
        $arsip = $this->Arsip_berkas_model->get_by_id($id);
        
        if (!$arsip) {
            $this->session->set_flashdata('error', 'Arsip berkas tidak ditemukan.');
            redirect('Arsip');
        }
        
        if ($this->input->method() === 'post') {
            $this->form_validation->set_rules('jenis', 'Jenis', 'required|in_list[masuk,keluar]');
            $this->form_validation->set_rules('nomor_surat', 'Nomor Surat', 'trim');
            $this->form_validation->set_rules('tanggal_surat', 'Tanggal Surat', 'required');
            $this->form_validation->set_rules('perihal', 'Perihal', 'required|trim|min_length[5]');
            $this->form_validation->set_rules('keterangan', 'Keterangan', 'trim');
            $this->form_validation->set_rules('nama_file', 'Nama File', 'trim|max_length[255]');
            
            $jenis = $this->input->post('jenis');
            if ($jenis === 'masuk') {
                $this->form_validation->set_rules('pengirim', 'Pengirim', 'required|trim');
            } else {
                $this->form_validation->set_rules('penerima', 'Penerima', 'required|trim');
            }
            
            if ($this->form_validation->run() == FALSE) {
                $data['arsip'] = $arsip;
                $data['title'] = 'Edit Arsip Berkas';
                $data['page_title'] = 'Edit Arsip Berkas';
                $data['breadcrumb'] = array(
                    array('label' => 'Arsip Berkas', 'url' => site_url('Arsip')),
                    array('label' => 'Edit', 'active' => true)
                );
                $data['sidebar_menu'] = generate_sidebar_menu($this->session->userdata('role'));
                $data['content'] = $this->load->view('sekprodi/arsip_edit', $data, true);
                $this->load->view('layouts/main', $data);
                return;
            }
            
            $file_path = $arsip->file_path;
            $file_name = trim($this->input->post('nama_file')) ?: $arsip->file_name ?: 'Berkas Arsip';
            $file_type = $arsip->file_type;
            $file_size = $arsip->file_size;
            if (!empty($_FILES['file_berkas']['name'])) {
                $this->load->helper('google_drive');
                require_google_drive_or_redirect();
                $this->load->library('Upload_to_drive');
                $upload = $this->upload_to_drive->handle_upload('file_berkas', $this->upload_to_drive->default_options(
                    array('SIAP', 'Arsip'),
                    array()
                ));
                if (empty($upload['ok'])) {
                    $this->session->set_flashdata('error', isset($upload['message']) ? $upload['message'] : 'Gagal upload berkas.');
                    redirect('Arsip/edit/' . $id);
                }
                $file_path = $upload['webViewLink'];
                if (trim($this->input->post('nama_file')) === '') {
                    $file_name = !empty($upload['file_name']) ? $upload['file_name'] : $file_name;
                }
                $file_type = isset($upload['mimeType']) ? $upload['mimeType'] : null;
                $file_size = isset($_FILES['file_berkas']['size']) ? round($_FILES['file_berkas']['size'] / 1024) : null;
            }
            
            $data = array(
                'jenis' => $jenis,
                'nomor_surat' => $this->input->post('nomor_surat') ? $this->input->post('nomor_surat') : null,
                'tanggal_surat' => $this->input->post('tanggal_surat'),
                'pengirim' => $jenis === 'masuk' ? $this->input->post('pengirim') : null,
                'penerima' => $jenis === 'keluar' ? $this->input->post('penerima') : null,
                'perihal' => $this->input->post('perihal'),
                'file_path' => $file_path,
                'file_name' => $file_name,
                'file_type' => $file_type,
                'file_size' => $file_size,
                'keterangan' => $this->input->post('keterangan') ? $this->input->post('keterangan') : null
            );
            
            if ($this->Arsip_berkas_model->update($id, $data)) {
                $this->session->set_flashdata('success', 'Arsip berkas berhasil diperbarui.');
                redirect('Arsip');
            } else {
                $this->session->set_flashdata('error', 'Gagal memperbarui arsip berkas.');
                redirect('Arsip/edit/' . $id);
            }
        } else {
            $data['arsip'] = $arsip;
            $data['title'] = 'Edit Arsip Berkas';
            $data['page_title'] = 'Edit Arsip Berkas';
            $data['breadcrumb'] = array(
                array('label' => 'Arsip Berkas', 'url' => site_url('Arsip')),
                array('label' => 'Edit', 'active' => true)
            );
            $data['sidebar_menu'] = generate_sidebar_menu($this->session->userdata('role'));
            $data['content'] = $this->load->view('sekprodi/arsip_edit', $data, true);
            $this->load->view('layouts/main', $data);
        }
    }

    /**
     * Detail arsip
     */
    public function detail($id) {
        $arsip = $this->Arsip_berkas_model->get_by_id($id);
        
        if (!$arsip) {
            $this->session->set_flashdata('error', 'Arsip berkas tidak ditemukan.');
            redirect('Arsip');
        }
        
        $data['arsip'] = $arsip;
        $data['title'] = 'Detail Arsip Berkas';
        $data['page_title'] = 'Detail Arsip Berkas';
        $data['breadcrumb'] = array(
            array('label' => 'Arsip Berkas', 'url' => site_url('Arsip')),
            array('label' => 'Detail', 'active' => true)
        );
        $data['sidebar_menu'] = generate_sidebar_menu($this->session->userdata('role'));
        $data['content'] = $this->load->view('sekprodi/arsip_detail', $data, true);
        $this->load->view('layouts/main', $data);
    }

    /**
     * Download file arsip - redirect ke link Google Drive
     */
    public function download($id) {
        $arsip = $this->Arsip_berkas_model->get_by_id($id);
        
        if (!$arsip) {
            show_404();
        }
        
        // Redirect ke link Google Drive
        if (!empty($arsip->file_path)) {
            if (strpos($arsip->file_path, 'http') === 0) {
                // Ini adalah link
                redirect($arsip->file_path);
            } else {
                // Backward compatibility: file lokal
                $file_path = $arsip->file_path;
                if (file_exists($file_path)) {
                    $this->load->helper('download');
                    force_download($arsip->file_name, file_get_contents($file_path));
                } else {
                    $this->session->set_flashdata('error', 'File tidak ditemukan.');
                    redirect('Arsip');
                }
            }
        } else {
            $this->session->set_flashdata('error', 'Link berkas tidak tersedia.');
            redirect('Arsip');
        }
    }

    /**
     * Delete arsip
     */
    public function delete($id) {
        $this->assert_not_kaprodi('Arsip');
        $arsip = $this->Arsip_berkas_model->get_by_id($id);
        
        if (!$arsip) {
            $this->session->set_flashdata('error', 'Arsip berkas tidak ditemukan.');
            redirect('Arsip');
        }
        
        if ($this->Arsip_berkas_model->delete($id)) {
            $this->session->set_flashdata('success', 'Arsip berkas berhasil dihapus.');
        } else {
            $this->session->set_flashdata('error', 'Gagal menghapus arsip berkas.');
        }
        
        redirect('Arsip');
    }
}

