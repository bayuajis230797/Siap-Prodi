<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Ruang Kelas Controller
 * Mengelola data ruang kelas untuk jadwal kuliah, UTS, UAS, dan ujian
 */
class Ruang_kelas extends MY_Controller {

    public function __construct() {
        parent::__construct();
        $this->check_role('admin');
        $this->load->model('Ruang_kelas_model');
    }

    /**
     * List semua ruang kelas dengan pagination
     */
    public function index() {
        $this->load->library('pagination');
        
        // Ambil parameter per_page dari GET, default 10
        $per_page = $this->input->get('per_page') ? (int)$this->input->get('per_page') : 10;
        $per_page = in_array($per_page, [10, 25, 50, 100]) ? $per_page : 10;
        
        // Pagination config
        $config['base_url'] = site_url('Ruang_kelas/index');
        $config['total_rows'] = $this->Ruang_kelas_model->count_all();
        $config['per_page'] = $per_page;
        $config['uri_segment'] = 3;
        
        // Tambahkan per_page ke query string
        if ($per_page != 10) {
            $config['suffix'] = '?per_page=' . $per_page;
            $config['first_url'] = site_url('Ruang_kelas/index?per_page=' . $per_page);
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
        
        $data['ruang_kelas'] = $this->Ruang_kelas_model->get_all($config['per_page'], $offset);
        $data['pagination'] = $this->pagination->create_links();
        $data['total_rows'] = $config['total_rows'];
        
        $data['title'] = 'Ruang Kelas';
        $data['page_title'] = 'Master Data Ruang Kelas';
        $data['breadcrumb'] = array(
            array('label' => 'Ruang Kelas', 'active' => true)
        );
        $data['sidebar_menu'] = generate_sidebar_menu('admin');
        $data['content'] = $this->load->view('ruang_kelas/index', $data, true);
        $this->load->view('layouts/main', $data);
    }

    /**
     * Form tambah ruang kelas
     */
    public function create() {
        if ($this->input->method() === 'post') {
            $this->form_validation->set_rules('kode_ruang', 'Kode Ruang', 'required|trim|is_unique[ruang_kelas.kode_ruang]');
            $this->form_validation->set_rules('nama_ruang', 'Nama Ruang', 'required|trim');
            $this->form_validation->set_rules('kapasitas', 'Kapasitas', 'trim|numeric');
            $this->form_validation->set_rules('jenis_ruang', 'Jenis Ruang', 'required|trim');
            $this->form_validation->set_rules('status', 'Status', 'required|trim');

            if ($this->form_validation->run() == FALSE) {
                $data['title'] = 'Tambah Ruang Kelas';
                $data['page_title'] = 'Tambah Ruang Kelas';
                $data['breadcrumb'] = array(
                    array('label' => 'Ruang Kelas', 'url' => site_url('Ruang_kelas')),
                    array('label' => 'Tambah', 'active' => true)
                );
                $data['sidebar_menu'] = generate_sidebar_menu('admin');
                $data['content'] = $this->load->view('ruang_kelas/create', $data, true);
                $this->load->view('layouts/main', $data);
            } else {
                $data_ruang = array(
                    'kode_ruang' => $this->input->post('kode_ruang'),
                    'nama_ruang' => $this->input->post('nama_ruang'),
                    'kapasitas' => $this->input->post('kapasitas') ? $this->input->post('kapasitas') : 0,
                    'jenis_ruang' => $this->input->post('jenis_ruang'),
                    'lokasi' => $this->input->post('lokasi'),
                    'fasilitas' => $this->input->post('fasilitas'),
                    'status' => $this->input->post('status')
                );

                // Insert ruang kelas
                $ruang_id = $this->Ruang_kelas_model->create($data_ruang);
                
                if ($ruang_id) {
                    $this->session->set_flashdata('success', 'Ruang kelas berhasil ditambahkan.');
                    redirect('Ruang_kelas');
                } else {
                    $this->session->set_flashdata('error', 'Gagal menambahkan ruang kelas.');
                    redirect('Ruang_kelas/create');
                }
            }
        } else {
            $data['title'] = 'Tambah Ruang Kelas';
            $data['page_title'] = 'Tambah Ruang Kelas';
            $data['breadcrumb'] = array(
                array('label' => 'Ruang Kelas', 'url' => site_url('Ruang_kelas')),
                array('label' => 'Tambah', 'active' => true)
            );
            $data['sidebar_menu'] = generate_sidebar_menu('admin');
            $data['content'] = $this->load->view('ruang_kelas/create', $data, true);
            $this->load->view('layouts/main', $data);
        }
    }

    /**
     * Form edit ruang kelas
     */
    public function edit($id) {
        $ruang = $this->Ruang_kelas_model->get_by_id($id);
        
        if (!$ruang) {
            $this->session->set_flashdata('error', 'Ruang kelas tidak ditemukan.');
            redirect('Ruang_kelas');
        }

        if ($this->input->method() === 'post') {
            // Validasi kode ruang unik (kecuali untuk ruang yang sedang diedit)
            $kode_unique = '';
            if ($this->input->post('kode_ruang') != $ruang->kode_ruang) {
                $kode_unique = '|is_unique[ruang_kelas.kode_ruang]';
            }

            $this->form_validation->set_rules('kode_ruang', 'Kode Ruang', 'required|trim' . $kode_unique);
            $this->form_validation->set_rules('nama_ruang', 'Nama Ruang', 'required|trim');
            $this->form_validation->set_rules('kapasitas', 'Kapasitas', 'trim|numeric');
            $this->form_validation->set_rules('jenis_ruang', 'Jenis Ruang', 'required|trim');
            $this->form_validation->set_rules('status', 'Status', 'required|trim');

            if ($this->form_validation->run() == FALSE) {
                $data['ruang'] = $ruang;
                $data['title'] = 'Edit Ruang Kelas';
                $data['page_title'] = 'Edit Ruang Kelas';
                $data['breadcrumb'] = array(
                    array('label' => 'Ruang Kelas', 'url' => site_url('Ruang_kelas')),
                    array('label' => 'Edit', 'active' => true)
                );
                $data['sidebar_menu'] = generate_sidebar_menu('admin');
                $data['content'] = $this->load->view('ruang_kelas/edit', $data, true);
                $this->load->view('layouts/main', $data);
            } else {
                $data_ruang = array(
                    'kode_ruang' => $this->input->post('kode_ruang'),
                    'nama_ruang' => $this->input->post('nama_ruang'),
                    'kapasitas' => $this->input->post('kapasitas') ? $this->input->post('kapasitas') : 0,
                    'jenis_ruang' => $this->input->post('jenis_ruang'),
                    'lokasi' => $this->input->post('lokasi'),
                    'fasilitas' => $this->input->post('fasilitas'),
                    'status' => $this->input->post('status')
                );

                // Update ruang kelas
                if ($this->Ruang_kelas_model->update($id, $data_ruang)) {
                    $this->session->set_flashdata('success', 'Ruang kelas berhasil diupdate.');
                    redirect('Ruang_kelas');
                } else {
                    $this->session->set_flashdata('error', 'Gagal mengupdate ruang kelas.');
                    redirect('Ruang_kelas/edit/' . $id);
                }
            }
        } else {
            $data['ruang'] = $ruang;
            $data['title'] = 'Edit Ruang Kelas';
            $data['page_title'] = 'Edit Ruang Kelas';
            $data['breadcrumb'] = array(
                array('label' => 'Ruang Kelas', 'url' => site_url('Ruang_kelas')),
                array('label' => 'Edit', 'active' => true)
            );
            $data['sidebar_menu'] = generate_sidebar_menu('admin');
            $data['content'] = $this->load->view('ruang_kelas/edit', $data, true);
            $this->load->view('layouts/main', $data);
        }
    }

    /**
     * Hapus ruang kelas
     */
    public function delete($id) {
        $ruang = $this->Ruang_kelas_model->get_by_id($id);
        
        if (!$ruang) {
            $this->session->set_flashdata('error', 'Ruang kelas tidak ditemukan.');
            redirect('Ruang_kelas');
        }

        if ($this->Ruang_kelas_model->delete($id)) {
            $this->session->set_flashdata('success', 'Ruang kelas berhasil dihapus.');
        } else {
            $this->session->set_flashdata('error', 'Gagal menghapus ruang kelas.');
        }
        
        redirect('Ruang_kelas');
    }

    /**
     * Import ruang kelas dari Excel
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
                redirect('Ruang_kelas/import');
            }

            $upload_data = $this->upload->data();
            $file_path = $config['upload_path'] . $upload_data['file_name'];

            // Load library Excel_reader
            $this->load->library('Excel_reader');
            $result = $this->excel_reader->read_excel_ruang_kelas($file_path);

            // Hapus file temporary
            unlink($file_path);

            if ($result['success']) {
                $this->session->set_flashdata('success', $result['message']);
            } else {
                $this->session->set_flashdata('error', $result['message']);
            }

            redirect('Ruang_kelas');
        } else {
            $data['title'] = 'Import Ruang Kelas';
            $data['page_title'] = 'Import Ruang Kelas dari Excel';
            $data['breadcrumb'] = array(
                array('label' => 'Ruang Kelas', 'url' => site_url('Ruang_kelas')),
                array('label' => 'Import', 'active' => true)
            );
            $data['sidebar_menu'] = generate_sidebar_menu('admin');
            $data['content'] = $this->load->view('ruang_kelas/import', $data, true);
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
            $sheet->setCellValue('A1', 'kode_ruang');
            $sheet->setCellValue('B1', 'nama_ruang');
            $sheet->setCellValue('C1', 'kapasitas');
            $sheet->setCellValue('D1', 'jenis_ruang');
            $sheet->setCellValue('E1', 'lokasi');
            $sheet->setCellValue('F1', 'fasilitas');
            $sheet->setCellValue('G1', 'status');
            
            // Set contoh data
            $sheet->setCellValue('A2', 'R001');
            $sheet->setCellValue('B2', 'Ruang Kelas 1');
            $sheet->setCellValue('C2', '40');
            $sheet->setCellValue('D2', 'Kelas');
            $sheet->setCellValue('E2', 'Gedung A Lantai 1');
            $sheet->setCellValue('F2', 'AC, Proyektor, Papan Tulis');
            $sheet->setCellValue('G2', 'aktif');
            
            $sheet->setCellValue('A3', 'LAB001');
            $sheet->setCellValue('B3', 'Laboratorium Komputer 1');
            $sheet->setCellValue('C3', '30');
            $sheet->setCellValue('D3', 'Laboratorium');
            $sheet->setCellValue('E3', 'Gedung B Lantai 1');
            $sheet->setCellValue('F3', 'AC, Komputer, Proyektor');
            $sheet->setCellValue('G3', 'aktif');
            
            // Style header
            $sheet->getStyle('A1:G1')->getFont()->setBold(true);
            $sheet->getStyle('A1:G1')->getFill()
                ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                ->getStartColor()->setARGB('FFE0E0E0');
            
            // Auto size columns
            foreach (range('A', 'G') as $col) {
                $sheet->getColumnDimension($col)->setAutoSize(true);
            }
            
            // Set writer
            $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
            
            // Output
            header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
            header('Content-Disposition: attachment;filename="template_import_ruang_kelas.xlsx"');
            header('Cache-Control: max-age=0');
            
            $writer->save('php://output');
            exit;
        } else {
            // Fallback: CSV
            header('Content-Type: text/csv; charset=utf-8');
            header('Content-Disposition: attachment;filename="template_import_ruang_kelas.csv"');
            
            $output = fopen('php://output', 'w');
            
            // Header
            fputcsv($output, array('kode_ruang', 'nama_ruang', 'kapasitas', 'jenis_ruang', 'lokasi', 'fasilitas', 'status'));
            
            // Contoh data
            fputcsv($output, array('R001', 'Ruang Kelas 1', '40', 'Kelas', 'Gedung A Lantai 1', 'AC, Proyektor, Papan Tulis', 'aktif'));
            fputcsv($output, array('LAB001', 'Laboratorium Komputer 1', '30', 'Laboratorium', 'Gedung B Lantai 1', 'AC, Komputer, Proyektor', 'aktif'));
            
            fclose($output);
            exit;
        }
    }
}




