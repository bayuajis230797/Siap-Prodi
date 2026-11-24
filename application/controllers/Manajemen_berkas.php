<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Manajemen_berkas extends MY_Controller {

    public function __construct() {
        parent::__construct();
        // Controller ini digunakan untuk kedua role: sekprodi dan mahasiswa
        // Validasi role akan dilakukan di masing-masing method
        $this->load->model('Manajemen_berkas_model');
        $this->load->library('form_validation');
    }

    /**
     * Index - untuk sekprodi (manage berkas) dan mahasiswa (view berkas)
     */
    public function index() {
        $role = $this->session->userdata('role');
        
        if ($role === 'sekprodi') {
            $this->index_sekprodi();
        } elseif ($role === 'mahasiswa') {
            $this->index_mahasiswa();
        } else {
            show_404();
        }
    }

    /**
     * Index untuk sekprodi - manage berkas
     */
    private function index_sekprodi() {
        $this->check_role('sekprodi');
        
        $kategori = $this->input->get('kategori');
        $is_active = $this->input->get('is_active');
        
        // Ambil parameter per_page dari GET, default 10
        $per_page = $this->input->get('per_page') ? (int)$this->input->get('per_page') : 10;
        $per_page = in_array($per_page, [10, 25, 50, 100]) ? $per_page : 10;
        
        // Pagination
        $this->load->library('pagination');
        $config['base_url'] = site_url('Manajemen_berkas');
        $config['total_rows'] = $this->Manajemen_berkas_model->count_all($is_active !== null ? (int)$is_active : null, $kategori);
        $config['per_page'] = $per_page;
        $config['uri_segment'] = 2;
        $config['query_string_segment'] = 'page';
        $config['page_query_string'] = TRUE;
        $config['reuse_query_string'] = TRUE;
        
        $this->pagination->initialize($config);
        
        $page = $this->input->get('page') ? (int)$this->input->get('page') : 0;
        $berkas_list = $this->Manajemen_berkas_model->get_all(
            $is_active !== null ? (int)$is_active : null,
            $kategori,
            $config['per_page'],
            $page
        );
        
        $kategori_list = $this->Manajemen_berkas_model->get_kategori_list();
        
        $data = array(
            'title' => 'Manajemen Berkas',
            'page_title' => 'Manajemen Berkas',
            'breadcrumb' => array(array('label' => 'Manajemen Berkas', 'active' => true)),
            'sidebar_menu' => generate_sidebar_menu('sekprodi'),
            'berkas_list' => $berkas_list,
            'kategori_list' => $kategori_list,
            'kategori_filter' => $kategori,
            'is_active_filter' => $is_active,
            'pagination' => $this->pagination->create_links(),
            'per_page' => $per_page
        );
        
        $data['content'] = $this->load->view('sekprodi/manajemen_berkas', $data, true);
        $this->load->view('layouts/main', $data);
    }

    /**
     * Index untuk mahasiswa - view berkas aktif
     */
    private function index_mahasiswa() {
        $this->check_role('mahasiswa');
        
        $kategori = $this->input->get('kategori');
        
        // Pagination
        $this->load->library('pagination');
        $config['base_url'] = site_url('Manajemen_berkas');
        $config['total_rows'] = $this->Manajemen_berkas_model->count_all(1, $kategori); // Hanya aktif
        $config['per_page'] = 10;
        $config['uri_segment'] = 2;
        $config['query_string_segment'] = 'page';
        $config['page_query_string'] = TRUE;
        $config['reuse_query_string'] = TRUE;
        
        $this->pagination->initialize($config);
        
        $page = $this->input->get('page') ? (int)$this->input->get('page') : 0;
        $berkas_list = $this->Manajemen_berkas_model->get_all(
            1, // Hanya aktif
            $kategori,
            $config['per_page'],
            $page
        );
        
        $kategori_list = $this->Manajemen_berkas_model->get_kategori_list();
        
        $data = array(
            'title' => 'Manajemen Berkas',
            'page_title' => 'Manajemen Berkas',
            'breadcrumb' => array(array('label' => 'Manajemen Berkas', 'active' => true)),
            'sidebar_menu' => generate_sidebar_menu('mahasiswa'),
            'berkas_list' => $berkas_list,
            'kategori_list' => $kategori_list,
            'kategori_filter' => $kategori,
            'pagination' => $this->pagination->create_links()
        );
        
        $data['content'] = $this->load->view('mahasiswa/manajemen_berkas', $data, true);
        $this->load->view('layouts/main', $data);
    }

    /**
     * Create - form tambah berkas dengan link Google Drive (sekprodi only)
     */
    public function create() {
        $this->check_role('sekprodi');
        
        if ($this->input->method() === 'post') {
            $this->form_validation->set_rules('judul', 'Judul', 'required|trim|min_length[3]|max_length[255]');
            $this->form_validation->set_rules('kategori', 'Kategori', 'trim|max_length[100]');
            $this->form_validation->set_rules('deskripsi', 'Deskripsi', 'trim');
            $this->form_validation->set_rules('nama_file', 'Nama File', 'trim|max_length[255]');
            
            if ($this->form_validation->run() === FALSE) {
                $this->session->set_flashdata('error', validation_errors());
                redirect('Manajemen_berkas/create');
            }
            
            $this->load->helper('google_drive');
            require_google_drive_or_redirect();
            $this->load->library('Upload_to_drive');
            $upload = $this->upload_to_drive->handle_upload('file_berkas', $this->upload_to_drive->default_options(
                array('SIAP', 'Manajemen_Berkas'),
                array()
            ));
            if (empty($upload['ok'])) {
                $this->session->set_flashdata('error', isset($upload['message']) ? $upload['message'] : 'Gagal upload berkas.');
                redirect('Manajemen_berkas/create');
            }
            $nama_file = trim($this->input->post('nama_file')) ?: (!empty($upload['file_name']) ? $upload['file_name'] : 'Berkas');
            
            $user_id = $this->session->userdata('user_id');
            
            $data = array(
                'judul' => $this->input->post('judul'),
                'deskripsi' => $this->input->post('deskripsi') ?: null,
                'kategori' => $this->input->post('kategori') ?: null,
                'file_name' => $nama_file,
                'file_path' => $upload['webViewLink'],
                'file_size' => isset($_FILES['file_berkas']['size']) ? round($_FILES['file_berkas']['size'] / 1024) : null,
                'file_type' => isset($upload['mimeType']) ? $upload['mimeType'] : null,
                'is_active' => 1,
                'uploaded_by' => $user_id
            );
            
            if ($this->Manajemen_berkas_model->create($data)) {
                $this->session->set_flashdata('success', 'Berkas berhasil disimpan.');
                redirect('Manajemen_berkas');
            } else {
                $this->session->set_flashdata('error', 'Gagal menyimpan data berkas.');
                redirect('Manajemen_berkas/create');
            }
        } else {
            $data = array(
                'title' => 'Tambah Berkas',
                'page_title' => 'Tambah Berkas',
                'breadcrumb' => array(
                    array('label' => 'Manajemen Berkas', 'url' => site_url('Manajemen_berkas')),
                    array('label' => 'Tambah', 'active' => true)
                ),
                'sidebar_menu' => generate_sidebar_menu('sekprodi')
            );
            
            $data['content'] = $this->load->view('sekprodi/manajemen_berkas_create', $data, true);
            $this->load->view('layouts/main', $data);
        }
    }

    /**
     * Edit - edit berkas dengan link Google Drive (sekprodi only)
     */
    public function edit($id) {
        $this->check_role('sekprodi');
        
        $berkas = $this->Manajemen_berkas_model->get_by_id($id);
        if (!$berkas) {
            $this->session->set_flashdata('error', 'Berkas tidak ditemukan.');
            redirect('Manajemen_berkas');
        }
        
        if ($this->input->method() === 'post') {
            $this->form_validation->set_rules('judul', 'Judul', 'required|trim|min_length[3]|max_length[255]');
            $this->form_validation->set_rules('kategori', 'Kategori', 'trim|max_length[100]');
            $this->form_validation->set_rules('deskripsi', 'Deskripsi', 'trim');
            $this->form_validation->set_rules('nama_file', 'Nama File', 'trim|max_length[255]');
            
            if ($this->form_validation->run() === FALSE) {
                $this->session->set_flashdata('error', validation_errors());
                redirect('Manajemen_berkas/edit/' . $id);
            }
            
            $file_path = $berkas->file_path;
            $nama_file = trim($this->input->post('nama_file')) ?: $berkas->file_name ?: 'Berkas';
            $file_size = $berkas->file_size;
            $file_type = $berkas->file_type;
            if (!empty($_FILES['file_berkas']['name'])) {
                $this->load->helper('google_drive');
                require_google_drive_or_redirect();
                $this->load->library('Upload_to_drive');
                $upload = $this->upload_to_drive->handle_upload('file_berkas', $this->upload_to_drive->default_options(
                    array('SIAP', 'Manajemen_Berkas'),
                    array()
                ));
                if (empty($upload['ok'])) {
                    $this->session->set_flashdata('error', isset($upload['message']) ? $upload['message'] : 'Gagal upload berkas.');
                    redirect('Manajemen_berkas/edit/' . $id);
                }
                $file_path = $upload['webViewLink'];
                if (trim($this->input->post('nama_file')) === '') {
                    $nama_file = !empty($upload['file_name']) ? $upload['file_name'] : $nama_file;
                }
                $file_size = isset($_FILES['file_berkas']['size']) ? round($_FILES['file_berkas']['size'] / 1024) : null;
                $file_type = isset($upload['mimeType']) ? $upload['mimeType'] : null;
            }
            
            $data = array(
                'judul' => $this->input->post('judul'),
                'deskripsi' => $this->input->post('deskripsi') ?: null,
                'kategori' => $this->input->post('kategori') ?: null,
                'file_name' => $nama_file,
                'file_path' => $file_path,
                'file_size' => $file_size,
                'file_type' => $file_type,
                'is_active' => $this->input->post('is_active') ? 1 : 0
            );
            
            if ($this->Manajemen_berkas_model->update($id, $data)) {
                $this->session->set_flashdata('success', 'Berkas berhasil diupdate.');
                redirect('Manajemen_berkas');
            } else {
                $this->session->set_flashdata('error', 'Gagal mengupdate data berkas.');
                redirect('Manajemen_berkas/edit/' . $id);
            }
        } else {
            $data = array(
                'title' => 'Edit Berkas',
                'page_title' => 'Edit Berkas',
                'breadcrumb' => array(
                    array('label' => 'Manajemen Berkas', 'url' => site_url('Manajemen_berkas')),
                    array('label' => 'Edit', 'active' => true)
                ),
                'sidebar_menu' => generate_sidebar_menu('sekprodi'),
                'berkas' => $berkas
            );
            
            $data['content'] = $this->load->view('sekprodi/manajemen_berkas_edit', $data, true);
            $this->load->view('layouts/main', $data);
        }
    }

    /**
     * Delete - hapus berkas (sekprodi only)
     */
    public function delete($id) {
        $this->check_role('sekprodi');
        
        // Hapus dari database (tidak perlu hapus file karena menggunakan link)
        $berkas = $this->Manajemen_berkas_model->get_by_id($id);
        if ($berkas) {
            // Hanya hapus file fisik jika masih ada (untuk backward compatibility)
            if (!empty($berkas->file_path) && strpos($berkas->file_path, 'http') === false) {
                $file_path = FCPATH . $berkas->file_path;
                if (file_exists($file_path)) {
                    @unlink($file_path);
                }
            }
        }
        
        if ($this->Manajemen_berkas_model->delete($id)) {
            $this->session->set_flashdata('success', 'Berkas berhasil dihapus.');
        } else {
            $this->session->set_flashdata('error', 'Gagal menghapus berkas.');
        }
        
        redirect('Manajemen_berkas');
    }

    /**
     * Toggle active - aktifkan/nonaktifkan berkas (sekprodi only)
     */
    public function toggle_active($id) {
        $this->check_role('sekprodi');
        
        if ($this->Manajemen_berkas_model->toggle_active($id)) {
            $this->session->set_flashdata('success', 'Status berkas berhasil diubah.');
        } else {
            $this->session->set_flashdata('error', 'Gagal mengubah status berkas.');
        }
        
        redirect('Manajemen_berkas');
    }

    /**
     * Download - redirect ke link Google Drive (untuk semua role yang punya akses)
     */
    public function download($id) {
        $berkas = $this->Manajemen_berkas_model->get_by_id($id);
        if (!$berkas) {
            $this->session->set_flashdata('error', 'Berkas tidak ditemukan.');
            redirect('Manajemen_berkas');
        }
        
        // Cek akses: sekprodi bisa download semua, mahasiswa hanya yang aktif
        $role = $this->session->userdata('role');
        if ($role === 'mahasiswa' && $berkas->is_active != 1) {
            $this->session->set_flashdata('error', 'Berkas tidak tersedia.');
            redirect('Manajemen_berkas');
        }
        
        // Redirect ke link Google Drive
        if (!empty($berkas->file_path)) {
            redirect($berkas->file_path);
        } else {
            $this->session->set_flashdata('error', 'Link berkas tidak tersedia.');
            redirect('Manajemen_berkas');
        }
    }

    /**
     * View - redirect ke link Google Drive (untuk mahasiswa)
     */
    public function view($id) {
        $this->check_role('mahasiswa');
        
        $berkas = $this->Manajemen_berkas_model->get_by_id($id);
        if (!$berkas || $berkas->is_active != 1) {
            $this->session->set_flashdata('error', 'Berkas tidak ditemukan atau tidak tersedia.');
            redirect('Manajemen_berkas');
        }
        
        // Redirect ke link Google Drive
        if (!empty($berkas->file_path)) {
            redirect($berkas->file_path);
        } else {
            $this->session->set_flashdata('error', 'Link berkas tidak tersedia.');
            redirect('Manajemen_berkas');
        }
    }
}


