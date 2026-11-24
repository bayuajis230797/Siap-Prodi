<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Dosen Controller
 * Mengelola data dosen dengan auto-create user account
 */
class Dosen extends MY_Controller {

    public function __construct() {
        parent::__construct();
        $this->check_role(array('admin', 'kaprodi'));
        $this->load->model('Dosen_model');
    }

    /**
     * List semua dosen dengan pagination
     */
    public function index() {
        $this->load->library('pagination');
        
        // Ambil parameter per_page dari GET, default 10
        $per_page = $this->input->get('per_page') ? (int)$this->input->get('per_page') : 10;
        $per_page = in_array($per_page, [10, 25, 50, 100]) ? $per_page : 10;
        
        // Pagination config
        $config['base_url'] = site_url('Dosen/index');
        $config['total_rows'] = $this->Dosen_model->count_all();
        $config['per_page'] = $per_page;
        $config['uri_segment'] = 3;
        
        // Tambahkan per_page ke query string
        if ($per_page != 10) {
            $config['suffix'] = '?per_page=' . $per_page;
            $config['first_url'] = site_url('Dosen/index?per_page=' . $per_page);
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
        
        $data['dosen'] = $this->Dosen_model->get_all($config['per_page'], $offset);
        $data['pagination'] = $this->pagination->create_links();
        $data['total_rows'] = $config['total_rows'];
        $data['per_page'] = $per_page;
        
        $data['title'] = 'Dosen';
        $data['page_title'] = 'Master Data Dosen';
        $data['breadcrumb'] = array(
            array('label' => 'Dosen', 'active' => true)
        );
        $data['sidebar_menu'] = generate_sidebar_menu(get_user_role());
        $data['content'] = $this->load->view('dosen/index', $data, true);
        $this->load->view('layouts/main', $data);
    }

    /**
     * Form tambah dosen
     */
    public function create() {
        $this->assert_not_kaprodi('Dosen');
        if ($this->input->method() === 'post') {
            $this->form_validation->set_rules('nidn', 'NIDN', 'required|trim|is_unique[dosen.nidn]');
            $this->form_validation->set_rules('nama_dosen', 'Nama Dosen', 'required|trim');
            $this->form_validation->set_rules('email', 'Email', 'trim|valid_email');
            $this->form_validation->set_rules('jabatan_fungsional', 'Jabatan Fungsional', 'required|trim');
            $this->form_validation->set_rules('status', 'Status', 'required|trim');

            if ($this->form_validation->run() == FALSE) {
                $data['title'] = 'Tambah Dosen';
                $data['page_title'] = 'Tambah Dosen';
                $data['breadcrumb'] = array(
                    array('label' => 'Dosen', 'url' => site_url('Dosen')),
                    array('label' => 'Tambah', 'active' => true)
                );
                $data['sidebar_menu'] = generate_sidebar_menu(get_user_role());
                $data['content'] = $this->load->view('dosen/create', $data, true);
                $this->load->view('layouts/main', $data);
            } else {
                $data_dosen = array(
                    'nidn' => $this->input->post('nidn'),
                    'nama_dosen' => $this->input->post('nama_dosen'),
                    'email' => $this->input->post('email'),
                    'jabatan_fungsional' => $this->input->post('jabatan_fungsional'),
                    'status' => $this->input->post('status'),
                    'is_pembimbing_skripsi' => $this->input->post('is_pembimbing_skripsi') ? 1 : 0
                );

                // Insert dosen
                $dosen_id = $this->Dosen_model->create($data_dosen);
                
                if ($dosen_id) {
                    // Auto-create user account
                    $this->create_user_account($this->input->post('nidn'), $dosen_id);
                    
                    $this->session->set_flashdata('success', 'Dosen berhasil ditambahkan. User account otomatis dibuat dengan username dan password: ' . $this->input->post('nidn'));
                    redirect('Dosen');
                } else {
                    $this->session->set_flashdata('error', 'Gagal menambahkan dosen.');
                    redirect('Dosen/create');
                }
            }
        } else {
            $data['title'] = 'Tambah Dosen';
            $data['page_title'] = 'Tambah Dosen';
            $data['breadcrumb'] = array(
                array('label' => 'Dosen', 'url' => site_url('Dosen')),
                array('label' => 'Tambah', 'active' => true)
            );
            $data['sidebar_menu'] = generate_sidebar_menu(get_user_role());
            $data['content'] = $this->load->view('dosen/create', $data, true);
            $this->load->view('layouts/main', $data);
        }
    }

    /**
     * Form edit dosen
     */
    public function edit($id) {
        $this->assert_not_kaprodi('Dosen');
        $dosen = $this->Dosen_model->get_by_id($id);
        
        if (!$dosen) {
            $this->session->set_flashdata('error', 'Dosen tidak ditemukan.');
            redirect('Dosen');
        }

        if ($this->input->method() === 'post') {
            // Cek apakah NIDN berubah
            $nidn_baru = $this->input->post('nidn');
            $is_unique = ($nidn_baru == $dosen->nidn) ? '' : '|is_unique[dosen.nidn]';
            
            $this->form_validation->set_rules('nidn', 'NIDN', 'required|trim' . $is_unique);
            $this->form_validation->set_rules('nama_dosen', 'Nama Dosen', 'required|trim');
            $this->form_validation->set_rules('email', 'Email', 'trim|valid_email');
            $this->form_validation->set_rules('jabatan_fungsional', 'Jabatan Fungsional', 'required|trim');
            $this->form_validation->set_rules('status', 'Status', 'required|trim');

            if ($this->form_validation->run() == FALSE) {
                $data['dosen'] = $dosen;
                $data['title'] = 'Edit Dosen';
                $data['page_title'] = 'Edit Dosen';
                $data['breadcrumb'] = array(
                    array('label' => 'Dosen', 'url' => site_url('Dosen')),
                    array('label' => 'Edit', 'active' => true)
                );
                $data['sidebar_menu'] = generate_sidebar_menu(get_user_role());
                $data['content'] = $this->load->view('dosen/edit', $data, true);
                $this->load->view('layouts/main', $data);
            } else {
                $data = array(
                    'nidn' => $this->input->post('nidn'),
                    'nama_dosen' => $this->input->post('nama_dosen'),
                    'email' => $this->input->post('email'),
                    'jabatan_fungsional' => $this->input->post('jabatan_fungsional'),
                    'status' => $this->input->post('status'),
                    'is_pembimbing_skripsi' => $this->input->post('is_pembimbing_skripsi') ? 1 : 0
                );

                if ($this->Dosen_model->update($id, $data)) {
                    // Update user account jika NIDN berubah
                    if ($nidn_baru != $dosen->nidn) {
                        $this->update_user_account($dosen->nidn, $nidn_baru, $id);
                    }
                    
                    $this->session->set_flashdata('success', 'Dosen berhasil diupdate.');
                    redirect('Dosen');
                } else {
                    $this->session->set_flashdata('error', 'Gagal mengupdate dosen.');
                    redirect('Dosen/edit/' . $id);
                }
            }
        } else {
            $data['dosen'] = $dosen;
            $data['title'] = 'Edit Dosen';
            $data['page_title'] = 'Edit Dosen';
            $data['breadcrumb'] = array(
                array('label' => 'Dosen', 'url' => site_url('Dosen')),
                array('label' => 'Edit', 'active' => true)
            );
            $data['sidebar_menu'] = generate_sidebar_menu(get_user_role());
            $data['content'] = $this->load->view('dosen/edit', $data, true);
            $this->load->view('layouts/main', $data);
        }
    }

    /**
     * Hapus dosen
     */
    public function delete($id) {
        $this->assert_not_kaprodi('Dosen');
        $dosen = $this->Dosen_model->get_by_id($id);
        
        if (!$dosen) {
            $this->session->set_flashdata('error', 'Dosen tidak ditemukan.');
            redirect('Dosen');
        }

        // Hapus user account terlebih dahulu
        $this->delete_user_account($dosen->nidn);

        if ($this->Dosen_model->delete($id)) {
            $this->session->set_flashdata('success', 'Dosen berhasil dihapus.');
        } else {
            $this->session->set_flashdata('error', 'Gagal menghapus dosen.');
        }
        redirect('Dosen');
    }

    /**
     * Import dosen dari Excel
     */
    public function import() {
        $this->assert_not_kaprodi('Dosen');
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
                redirect('Dosen/import');
            }

            $upload_data = $this->upload->data();
            $file_path = $config['upload_path'] . $upload_data['file_name'];

            // Load library Excel_reader
            $this->load->library('Excel_reader');
            $result = $this->excel_reader->read_excel_dosen($file_path);

            // Hapus file temporary
            unlink($file_path);

            if ($result['success']) {
                $this->session->set_flashdata('success', $result['message']);
            } else {
                $this->session->set_flashdata('error', $result['message']);
            }

            redirect('Dosen');
        } else {
            $data['title'] = 'Import Dosen';
            $data['page_title'] = 'Import Dosen dari Excel';
            $data['breadcrumb'] = array(
                array('label' => 'Dosen', 'url' => site_url('Dosen')),
                array('label' => 'Import', 'active' => true)
            );
            $data['sidebar_menu'] = generate_sidebar_menu(get_user_role());
            $data['content'] = $this->load->view('dosen/import', $data, true);
            $this->load->view('layouts/main', $data);
        }
    }

    /**
     * Download template Excel untuk import
     */
    public function download_template() {
        $this->assert_not_kaprodi('Dosen');
        // Cek apakah PhpSpreadsheet tersedia
        if (file_exists(APPPATH . '../vendor/autoload.php')) {
            require_once APPPATH . '../vendor/autoload.php';
            
            $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet();
            
            // Set header
            $sheet->setCellValue('A1', 'nidn');
            $sheet->setCellValue('B1', 'nama_dosen');
            $sheet->setCellValue('C1', 'email');
            $sheet->setCellValue('D1', 'jabatan_fungsional');
            $sheet->setCellValue('E1', 'status');
            
            // Set contoh data
            $sheet->setCellValue('A2', '0123456789');
            $sheet->setCellValue('B2', 'Contoh Dosen 1');
            $sheet->setCellValue('C2', 'dosen1@example.com');
            $sheet->setCellValue('D2', 'Lektor');
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
            header('Content-Disposition: attachment;filename="template_import_dosen.xlsx"');
            header('Cache-Control: max-age=0');
            
            $writer = \PhpOffice\PhpSpreadsheet\IOFactory::createWriter($spreadsheet, 'Xlsx');
            $writer->save('php://output');
            exit;
        } else {
            // Fallback: download CSV
            header('Content-Type: text/csv');
            header('Content-Disposition: attachment;filename="template_import_dosen.csv"');
            
            $output = fopen('php://output', 'w');
            fputcsv($output, array('nidn', 'nama_dosen', 'email', 'jabatan_fungsional', 'status'));
            fputcsv($output, array('0123456789', 'Contoh Dosen 1', 'dosen1@example.com', 'Lektor', 'aktif'));
            fputcsv($output, array('0987654321', 'Contoh Dosen 2', 'dosen2@example.com', 'Asisten Ahli', 'aktif'));
            fclose($output);
            exit;
        }
    }

    /**
     * Buat user account untuk dosen
     */
    private function create_user_account($nidn, $dosen_id) {
        // Ambil role_id untuk dosen
        $this->db->where('nama_role', 'dosen');
        $role = $this->db->get('roles')->row();
        
        if (!$role) {
            return false;
        }
        
        // Cek apakah user sudah ada
        $this->db->where('username', $nidn);
        $existing = $this->db->get('users')->row();
        
        if ($existing) {
            // Update ref_id jika user sudah ada
            $this->db->where('id', $existing->id);
            $this->db->update('users', array('ref_id' => $dosen_id));
            return true;
        }
        
        // Insert user baru
        $data = array(
            'username' => $nidn,
            'password' => md5($nidn), // Password default = MD5(NIDN)
            'role_id' => $role->id,
            'ref_id' => $dosen_id
        );
        
        $this->db->insert('users', $data);
        return $this->db->insert_id();
    }

    /**
     * Update user account jika NIDN berubah
     */
    private function update_user_account($nidn_lama, $nidn_baru, $dosen_id) {
        // Update username dan password
        $this->db->where('username', $nidn_lama);
        $this->db->where('ref_id', $dosen_id);
        $this->db->update('users', array(
            'username' => $nidn_baru,
            'password' => md5($nidn_baru)
        ));
    }

    /**
     * Hapus user account dosen
     */
    private function delete_user_account($nidn) {
        $this->db->where('username', $nidn);
        $this->db->delete('users');
    }
}
