<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Tendik Controller
 * Mengelola data tenaga kependidikan dengan auto-create user account
 */
class Tendik extends MY_Controller {

    public function __construct() {
        parent::__construct();
        $this->check_role('admin');
        $this->load->model('Tendik_model');
    }

    /**
     * List semua tendik dengan pagination
     */
    public function index() {
        $this->load->library('pagination');
        
        // Ambil parameter per_page dari GET, default 10
        $per_page = $this->input->get('per_page') ? (int)$this->input->get('per_page') : 10;
        $per_page = in_array($per_page, [10, 25, 50, 100]) ? $per_page : 10;
        
        // Pagination config
        $config['base_url'] = site_url('Tendik/index');
        $config['total_rows'] = $this->Tendik_model->count_all();
        $config['per_page'] = $per_page;
        $config['uri_segment'] = 3;
        
        // Tambahkan per_page ke query string
        if ($per_page != 10) {
            $config['suffix'] = '?per_page=' . $per_page;
            $config['first_url'] = site_url('Tendik/index?per_page=' . $per_page);
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
        
        $data['tendik'] = $this->Tendik_model->get_all($config['per_page'], $offset);
        $data['pagination'] = $this->pagination->create_links();
        $data['total_rows'] = $config['total_rows'];
        $data['per_page'] = $per_page;
        
        $data['title'] = 'Tenaga Kependidikan';
        $data['page_title'] = 'Master Data Tenaga Kependidikan';
        $data['breadcrumb'] = array(
            array('label' => 'Tendik', 'active' => true)
        );
        $data['sidebar_menu'] = generate_sidebar_menu('admin');
        $data['content'] = $this->load->view('tendik/index', $data, true);
        $this->load->view('layouts/main', $data);
    }

    /**
     * Form tambah tendik
     */
    public function create() {
        if ($this->input->method() === 'post') {
            $this->form_validation->set_rules('nipy', 'NIPY', 'required|trim|is_unique[tendik.nipy]');
            $this->form_validation->set_rules('nama_tendik', 'Nama Tendik', 'required|trim');
            $this->form_validation->set_rules('email', 'Email', 'trim|valid_email');
            $this->form_validation->set_rules('jabatan', 'Jabatan', 'trim');
            $this->form_validation->set_rules('status', 'Status', 'required|trim');

            if ($this->form_validation->run() == FALSE) {
                $data['title'] = 'Tambah Tendik';
                $data['page_title'] = 'Tambah Tenaga Kependidikan';
                $data['breadcrumb'] = array(
                    array('label' => 'Tendik', 'url' => site_url('Tendik')),
                    array('label' => 'Tambah', 'active' => true)
                );
                $data['sidebar_menu'] = generate_sidebar_menu('admin');
                $data['content'] = $this->load->view('tendik/create', $data, true);
                $this->load->view('layouts/main', $data);
            } else {
                $data_tendik = array(
                    'nipy' => $this->input->post('nipy'),
                    'nama_tendik' => $this->input->post('nama_tendik'),
                    'email' => $this->input->post('email'),
                    'jabatan' => $this->input->post('jabatan'),
                    'status' => $this->input->post('status')
                );

                // Insert tendik
                $tendik_id = $this->Tendik_model->create($data_tendik);
                
                if ($tendik_id) {
                    // Auto-create user account
                    $this->create_user_account($this->input->post('nipy'), $tendik_id);
                    
                    $this->session->set_flashdata('success', 'Tenaga Kependidikan berhasil ditambahkan. User account otomatis dibuat dengan username dan password: ' . $this->input->post('nipy'));
                    redirect('Tendik');
                } else {
                    $this->session->set_flashdata('error', 'Gagal menambahkan tenaga kependidikan.');
                    redirect('Tendik/create');
                }
            }
        } else {
            $data['title'] = 'Tambah Tendik';
            $data['page_title'] = 'Tambah Tenaga Kependidikan';
            $data['breadcrumb'] = array(
                array('label' => 'Tendik', 'url' => site_url('Tendik')),
                array('label' => 'Tambah', 'active' => true)
            );
            $data['sidebar_menu'] = generate_sidebar_menu('admin');
            $data['content'] = $this->load->view('tendik/create', $data, true);
            $this->load->view('layouts/main', $data);
        }
    }

    /**
     * Form edit tendik
     */
    public function edit($id) {
        $tendik = $this->Tendik_model->get_by_id($id);
        
        if (!$tendik) {
            $this->session->set_flashdata('error', 'Tenaga Kependidikan tidak ditemukan.');
            redirect('Tendik');
        }

        if ($this->input->method() === 'post') {
            // Cek apakah NIPY berubah
            $nipy_baru = $this->input->post('nipy');
            $is_unique = ($nipy_baru == $tendik->nipy) ? '' : '|is_unique[tendik.nipy]';
            
            $this->form_validation->set_rules('nipy', 'NIPY', 'required|trim' . $is_unique);
            $this->form_validation->set_rules('nama_tendik', 'Nama Tendik', 'required|trim');
            $this->form_validation->set_rules('email', 'Email', 'trim|valid_email');
            $this->form_validation->set_rules('jabatan', 'Jabatan', 'trim');
            $this->form_validation->set_rules('status', 'Status', 'required|trim');

            if ($this->form_validation->run() == FALSE) {
                $data['tendik'] = $tendik;
                $data['title'] = 'Edit Tendik';
                $data['page_title'] = 'Edit Tenaga Kependidikan';
                $data['breadcrumb'] = array(
                    array('label' => 'Tendik', 'url' => site_url('Tendik')),
                    array('label' => 'Edit', 'active' => true)
                );
                $data['sidebar_menu'] = generate_sidebar_menu('admin');
                $data['content'] = $this->load->view('tendik/edit', $data, true);
                $this->load->view('layouts/main', $data);
            } else {
                $data = array(
                    'nipy' => $this->input->post('nipy'),
                    'nama_tendik' => $this->input->post('nama_tendik'),
                    'email' => $this->input->post('email'),
                    'jabatan' => $this->input->post('jabatan'),
                    'status' => $this->input->post('status')
                );

                if ($this->Tendik_model->update($id, $data)) {
                    // Update user account jika NIPY berubah
                    if ($nipy_baru != $tendik->nipy) {
                        $this->update_user_account($tendik->nipy, $nipy_baru, $id);
                    }
                    
                    $this->session->set_flashdata('success', 'Tenaga Kependidikan berhasil diupdate.');
                    redirect('Tendik');
                } else {
                    $this->session->set_flashdata('error', 'Gagal mengupdate tenaga kependidikan.');
                    redirect('Tendik/edit/' . $id);
                }
            }
        } else {
            $data['tendik'] = $tendik;
            $data['title'] = 'Edit Tendik';
            $data['page_title'] = 'Edit Tenaga Kependidikan';
            $data['breadcrumb'] = array(
                array('label' => 'Tendik', 'url' => site_url('Tendik')),
                array('label' => 'Edit', 'active' => true)
            );
            $data['sidebar_menu'] = generate_sidebar_menu('admin');
            $data['content'] = $this->load->view('tendik/edit', $data, true);
            $this->load->view('layouts/main', $data);
        }
    }

    /**
     * Hapus tendik
     */
    public function delete($id) {
        $tendik = $this->Tendik_model->get_by_id($id);
        
        if (!$tendik) {
            $this->session->set_flashdata('error', 'Tenaga Kependidikan tidak ditemukan.');
            redirect('Tendik');
        }

        // Hapus user account terlebih dahulu
        $this->delete_user_account($tendik->nipy);

        if ($this->Tendik_model->delete($id)) {
            $this->session->set_flashdata('success', 'Tenaga Kependidikan berhasil dihapus.');
        } else {
            $this->session->set_flashdata('error', 'Gagal menghapus tenaga kependidikan.');
        }
        redirect('Tendik');
    }

    /**
     * Import tendik dari Excel
     */
    public function import() {
        if ($this->input->method() === 'post') {
            // Upload file
            $config['upload_path'] = './uploads/temp/';
            $config['allowed_types'] = 'xlsx|xls|csv';
            $config['max_size'] = 5120; // 5MB
            $config['encrypt_name'] = TRUE;

            if (!is_dir($config['upload_path'])) {
                mkdir($config['upload_path'], 0755, true);
            }

            $this->load->library('upload', $config);

            if (!$this->upload->do_upload('file_excel')) {
                $this->session->set_flashdata('error', 'Gagal upload file: ' . $this->upload->display_errors());
                redirect('Tendik/import');
            }

            $upload_data = $this->upload->data();
            $file_path = $config['upload_path'] . $upload_data['file_name'];

            // Load library Excel_reader
            $this->load->library('Excel_reader');
            $result = $this->excel_reader->read_excel_tendik($file_path);

            // Hapus file temporary
            unlink($file_path);

            if ($result['success']) {
                $this->session->set_flashdata('success', $result['message']);
            } else {
                $this->session->set_flashdata('error', $result['message']);
            }

            redirect('Tendik');
        } else {
            $data['title'] = 'Import Tendik';
            $data['page_title'] = 'Import Tenaga Kependidikan dari Excel';
            $data['breadcrumb'] = array(
                array('label' => 'Tendik', 'url' => site_url('Tendik')),
                array('label' => 'Import', 'active' => true)
            );
            $data['sidebar_menu'] = generate_sidebar_menu('admin');
            $data['content'] = $this->load->view('tendik/import', $data, true);
            $this->load->view('layouts/main', $data);
        }
    }

    /**
     * Download template Excel untuk import
     */
    public function download_template() {
        // Cek apakah PhpSpreadsheet tersedia
        if (file_exists(APPPATH . '../vendor/autoload.php')) {
            require_once APPPATH . '../vendor/autoload.php';
            
            $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet();
            
            // Set header
            $sheet->setCellValue('A1', 'nipy');
            $sheet->setCellValue('B1', 'nama_tendik');
            $sheet->setCellValue('C1', 'email');
            $sheet->setCellValue('D1', 'jabatan');
            $sheet->setCellValue('E1', 'status');
            
            // Set contoh data
            $sheet->setCellValue('A2', 'T001');
            $sheet->setCellValue('B2', 'Contoh Tendik 1');
            $sheet->setCellValue('C2', 'tendik1@example.com');
            $sheet->setCellValue('D2', 'Administrator');
            $sheet->setCellValue('E2', 'aktif');
            
            // Style header
            $sheet->getStyle('A1:E1')->getFont()->setBold(true);
            $sheet->getStyle('A1:E1')->getFill()
                ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                ->getStartColor()->setARGB('FFE0E0E0');
            
            // Auto size columns
            foreach (range('A', 'E') as $col) {
                $sheet->getColumnDimension($col)->setAutoSize(true);
            }
            
            // Download
            header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
            header('Content-Disposition: attachment;filename="template_import_tendik.xlsx"');
            header('Cache-Control: max-age=0');
            
            $writer = \PhpOffice\PhpSpreadsheet\IOFactory::createWriter($spreadsheet, 'Xlsx');
            $writer->save('php://output');
            exit;
        } else {
            // Fallback: download CSV
            header('Content-Type: text/csv');
            header('Content-Disposition: attachment;filename="template_import_tendik.csv"');
            
            $output = fopen('php://output', 'w');
            fputcsv($output, array('nipy', 'nama_tendik', 'email', 'jabatan', 'status'));
            fputcsv($output, array('T001', 'Contoh Tendik 1', 'tendik1@example.com', 'Administrator', 'aktif'));
            fputcsv($output, array('T002', 'Contoh Tendik 2', 'tendik2@example.com', 'Staff', 'aktif'));
            fclose($output);
            exit;
        }
    }

    /**
     * Buat user account untuk tendik
     */
    private function create_user_account($nipy, $tendik_id) {
        // Ambil role_id untuk tendik
        $this->db->where('nama_role', 'tendik');
        $role = $this->db->get('roles')->row();
        
        // Jika tidak ada role tendik, buat role baru
        if (!$role) {
            $this->db->insert('roles', array('nama_role' => 'tendik'));
            $role_id = $this->db->insert_id();
            $role = (object) array('id' => $role_id);
        }
        
        if (!$role || !isset($role->id)) {
            return false;
        }
        
        // Cek apakah user sudah ada
        $this->db->where('username', $nipy);
        $existing = $this->db->get('users')->row();
        
        if ($existing) {
            // Update ref_id jika user sudah ada
            $this->db->where('id', $existing->id);
            $this->db->update('users', array('ref_id' => $tendik_id));
            return true;
        }
        
        // Insert user baru
        $data = array(
            'username' => $nipy,
            'password' => md5($nipy), // Password default = MD5(NIPY)
            'role_id' => $role->id,
            'ref_id' => $tendik_id
        );
        
        $this->db->insert('users', $data);
        return $this->db->insert_id();
    }

    /**
     * Update user account jika NIPY berubah
     */
    private function update_user_account($nipy_lama, $nipy_baru, $tendik_id) {
        // Ambil role_id untuk tendik
        $this->db->where('nama_role', 'tendik');
        $role = $this->db->get('roles')->row();
        
        // Jika tidak ada role tendik, buat role baru
        if (!$role) {
            $this->db->insert('roles', array('nama_role' => 'tendik'));
            $role_id = $this->db->insert_id();
        } else {
            $role_id = $role->id;
        }
        
        // Update username dan password
        $this->db->where('username', $nipy_lama);
        $this->db->where('ref_id', $tendik_id);
        $this->db->update('users', array(
            'username' => $nipy_baru,
            'password' => md5($nipy_baru), // Password = MD5(NIPY)
            'role_id' => $role_id // Pastikan menggunakan role tendik
        ));
    }

    /**
     * Hapus user account tendik
     */
    private function delete_user_account($nipy) {
        $this->db->where('username', $nipy);
        $this->db->delete('users');
    }
}







