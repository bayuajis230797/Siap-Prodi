<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Kurikulum Controller
 * Mengelola kurikulum dan mata kuliah per semester
 */
class Kurikulum extends MY_Controller {

    public function __construct() {
        parent::__construct();
        $this->check_role(array('admin', 'kaprodi'));
        $this->load->model('Kurikulum_model');
        $this->load->model('Mata_kuliah_model');
    }

    /**
     * List semua kurikulum dengan pagination
     */
    public function index() {
        $this->load->library('pagination');
        
        // Ambil parameter per_page dari GET, default 10
        $per_page = $this->input->get('per_page') ? (int)$this->input->get('per_page') : 10;
        $per_page = in_array($per_page, [10, 25, 50, 100]) ? $per_page : 10;
        
        // Pagination config
        $config['base_url'] = site_url('Kurikulum/index');
        $config['total_rows'] = $this->Kurikulum_model->count_all();
        $config['per_page'] = $per_page;
        $config['uri_segment'] = 3;
        
        // Tambahkan per_page ke query string
        if ($per_page != 10) {
            $config['suffix'] = '?per_page=' . $per_page;
            $config['first_url'] = site_url('Kurikulum/index?per_page=' . $per_page);
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
        
        $data['kurikulum'] = $this->Kurikulum_model->get_all($config['per_page'], $offset);
        $data['pagination'] = $this->pagination->create_links();
        $data['total_rows'] = $config['total_rows'];
        $data['per_page'] = $per_page;
        
        $data['title'] = 'Kurikulum';
        $data['page_title'] = 'Manajemen Kurikulum';
        $data['breadcrumb'] = array(
            array('label' => 'Kurikulum', 'active' => true)
        );
        $data['sidebar_menu'] = generate_sidebar_menu(get_user_role());
        $data['content'] = $this->load->view('kurikulum/index', $data, true);
        $this->load->view('layouts/main', $data);
    }

    /**
     * Form tambah kurikulum
     */
    public function create() {
        $this->assert_not_kaprodi('Kurikulum');
        if ($this->input->method() === 'post') {
            $this->form_validation->set_rules('nama_kurikulum', 'Nama Kurikulum', 'required|trim');
            $this->form_validation->set_rules('tahun_berlaku', 'Tahun Berlaku', 'required|trim|numeric');

            if ($this->form_validation->run() == FALSE) {
                $data['title'] = 'Tambah Kurikulum';
                $data['page_title'] = 'Tambah Kurikulum';
                $data['breadcrumb'] = array(
                    array('label' => 'Kurikulum', 'url' => site_url('Kurikulum')),
                    array('label' => 'Tambah', 'active' => true)
                );
                $data['sidebar_menu'] = generate_sidebar_menu(get_user_role());
                $data['content'] = $this->load->view('kurikulum/create', $data, true);
                $this->load->view('layouts/main', $data);
            } else {
                $data = array(
                    'nama_kurikulum' => $this->input->post('nama_kurikulum'),
                    'tahun_berlaku' => $this->input->post('tahun_berlaku'),
                    'is_aktif' => 0
                );

                if ($this->Kurikulum_model->create($data)) {
                    $this->session->set_flashdata('success', 'Kurikulum berhasil ditambahkan.');
                    redirect('Kurikulum');
                } else {
                    $this->session->set_flashdata('error', 'Gagal menambahkan kurikulum.');
                    redirect('Kurikulum/create');
                }
            }
        } else {
            $data['title'] = 'Tambah Kurikulum';
            $data['page_title'] = 'Tambah Kurikulum';
            $data['breadcrumb'] = array(
                array('label' => 'Kurikulum', 'url' => site_url('Kurikulum')),
                array('label' => 'Tambah', 'active' => true)
            );
            $data['sidebar_menu'] = generate_sidebar_menu(get_user_role());
            $data['content'] = $this->load->view('kurikulum/create', $data, true);
            $this->load->view('layouts/main', $data);
        }
    }

    /**
     * Mapping MK - Input langsung mata kuliah untuk kurikulum
     */
    public function mapping_mk($id) {
        $kurikulum = $this->Kurikulum_model->get_by_id($id);
        
        if (!$kurikulum) {
            $this->session->set_flashdata('error', 'Kurikulum tidak ditemukan.');
            redirect('Kurikulum');
        }

        // Pagination untuk mapping MK
        $this->load->library('pagination');
        
        $config['base_url'] = site_url('Kurikulum/mapping_mk/' . $id);
        $config['total_rows'] = $this->Kurikulum_model->count_mapping_mk($id);
        $config['per_page'] = 10;
        $config['uri_segment'] = 4;
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
        
        $page = ($this->uri->segment(4)) ? $this->uri->segment(4) : 1;
        $offset = ($page - 1) * $config['per_page'];
        
        // Ambil semua mapping MK untuk kurikulum ini
        $data['kurikulum'] = $kurikulum;
        $data['mapping_mk'] = $this->Kurikulum_model->get_all_mapping_mk($id, $config['per_page'], $offset);
        $data['pagination'] = $this->pagination->create_links();
        $data['total_rows'] = $config['total_rows'];
        
        // Ambil tahun akademik aktif
        $this->load->model('Tahun_akademik_model');
        $this->load->model('Mata_kuliah_aktif_model');
        $tahun_aktif = $this->Tahun_akademik_model->get_aktif();
        $data['tahun_aktif'] = $tahun_aktif;
        
        // Jika ada tahun akademik aktif, ambil mata kuliah aktif untuk kurikulum ini
        if ($tahun_aktif) {
            $data['mata_kuliah_aktif'] = $this->Mata_kuliah_aktif_model->get_aktif_by_kurikulum_tahun($id, $tahun_aktif->id);
            
            // Hitung semester aktif untuk kurikulum ini
            $tahun_parts = explode('/', $tahun_aktif->tahun_akademik);
            $tahun_akademik_start = intval($tahun_parts[0]);
            $semester_value = ($tahun_aktif->semester == 'ganjil') ? 1 : 2;
            $selisih_tahun = $tahun_akademik_start - $kurikulum->tahun_berlaku;
            $data['semester_aktif'] = min(($selisih_tahun * 2) + $semester_value, 8);
        } else {
            $data['mata_kuliah_aktif'] = array();
            $data['semester_aktif'] = null;
        }

        $data['title'] = 'Mapping Mata Kuliah';
        $data['page_title'] = 'Mapping Mata Kuliah: ' . $kurikulum->nama_kurikulum;
        $data['breadcrumb'] = array(
            array('label' => 'Kurikulum', 'url' => site_url('Kurikulum')),
            array('label' => 'Mapping MK', 'active' => true)
        );
        $data['sidebar_menu'] = generate_sidebar_menu(get_user_role());
        $data['content'] = $this->load->view('kurikulum/mapping_mk', $data, true);
        $this->load->view('layouts/main', $data);
    }

    /**
     * Tambah mapping MK (input langsung)
     */
    public function add_mapping_mk() {
        $this->assert_not_kaprodi('Kurikulum');
        if ($this->input->method() === 'post') {
            $id_kurikulum = $this->input->post('id_kurikulum');
            $kode_mk = strtoupper(trim($this->input->post('kode_mk')));
            $nama_mk = trim($this->input->post('nama_mk'));
            $sks = $this->input->post('sks');
            $semester = $this->input->post('semester');
            $is_wajib = $this->input->post('is_wajib') ? 1 : 0;

            // Validasi
            if (empty($kode_mk) || empty($nama_mk) || empty($sks) || empty($semester)) {
                $this->session->set_flashdata('error', 'Semua field harus diisi.');
                redirect('Kurikulum/mapping_mk/' . $id_kurikulum);
            }

            // Cek atau buat mata kuliah di master
            $mk = $this->Mata_kuliah_model->get_by_kode($kode_mk);
            if (!$mk) {
                // Buat mata kuliah baru di master
                $mk_data = array(
                    'kode_mk' => $kode_mk,
                    'nama_mk' => $nama_mk,
                    'sks' => $sks
                );
                $id_mata_kuliah = $this->Mata_kuliah_model->create($mk_data);
            } else {
                $id_mata_kuliah = $mk->id;
                // Update nama dan SKS jika berbeda
                if ($mk->nama_mk != $nama_mk || $mk->sks != $sks) {
                    $this->Mata_kuliah_model->update($mk->id, array(
                        'nama_mk' => $nama_mk,
                        'sks' => $sks
                    ));
                }
            }

            // Tambah ke kurikulum_detail
            if ($this->Kurikulum_model->add_mapping_mk($id_kurikulum, $id_mata_kuliah, $semester, $is_wajib)) {
                $this->session->set_flashdata('success', 'Mata kuliah berhasil ditambahkan ke mapping.');
            } else {
                $this->session->set_flashdata('error', 'Gagal menambahkan. Mungkin sudah ada di semester tersebut.');
            }
            redirect('Kurikulum/mapping_mk/' . $id_kurikulum);
        }
    }

    /**
     * Update mapping MK
     */
    public function update_mapping_mk() {
        $this->assert_not_kaprodi('Kurikulum');
        if ($this->input->method() === 'post') {
            $id_kurikulum_detail = $this->input->post('id_kurikulum_detail');
            $id_kurikulum = $this->input->post('id_kurikulum');
            $kode_mk = strtoupper(trim($this->input->post('kode_mk')));
            $nama_mk = trim($this->input->post('nama_mk'));
            $sks = $this->input->post('sks');
            $semester = $this->input->post('semester');
            $is_wajib = $this->input->post('is_wajib') ? 1 : 0;

            if ($this->Kurikulum_model->update_mapping_mk($id_kurikulum_detail, $id_kurikulum, $kode_mk, $nama_mk, $sks, $semester, $is_wajib)) {
                $this->session->set_flashdata('success', 'Mapping mata kuliah berhasil diupdate.');
            } else {
                $this->session->set_flashdata('error', 'Gagal mengupdate mapping.');
            }
            redirect('Kurikulum/mapping_mk/' . $id_kurikulum);
        }
    }

    /**
     * Hapus mapping MK
     */
    public function delete_mapping_mk($id_kurikulum_detail, $id_kurikulum) {
        $this->assert_not_kaprodi('Kurikulum');
        if ($this->Kurikulum_model->remove_mata_kuliah($id_kurikulum_detail)) {
            $this->session->set_flashdata('success', 'Mapping mata kuliah berhasil dihapus.');
        } else {
            $this->session->set_flashdata('error', 'Gagal menghapus mapping.');
        }
        redirect('Kurikulum/mapping_mk/' . $id_kurikulum);
    }

    /**
     * Import Excel untuk mapping MK
     */
    public function import_excel($id) {
        $this->assert_not_kaprodi('Kurikulum');
        $kurikulum = $this->Kurikulum_model->get_by_id($id);
        
        if (!$kurikulum) {
            $this->session->set_flashdata('error', 'Kurikulum tidak ditemukan.');
            redirect('Kurikulum');
        }

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
                redirect('Kurikulum/mapping_mk/' . $id);
            }

            $upload_data = $this->upload->data();
            $file_path = $config['upload_path'] . $upload_data['file_name'];

            // Load library Excel
            $this->load->library('Excel_reader');
            $result = $this->excel_reader->read_excel($file_path, $id);

            // Hapus file temporary
            unlink($file_path);

            if ($result['success']) {
                $this->session->set_flashdata('success', $result['message']);
            } else {
                $this->session->set_flashdata('error', $result['message']);
            }

            redirect('Kurikulum/mapping_mk/' . $id);
        } else {
            $data['kurikulum'] = $kurikulum;
            $data['title'] = 'Import Excel - Mapping MK';
            $data['page_title'] = 'Import Excel: ' . $kurikulum->nama_kurikulum;
            $data['breadcrumb'] = array(
                array('label' => 'Kurikulum', 'url' => site_url('Kurikulum')),
                array('label' => 'Mapping MK', 'url' => site_url('Kurikulum/mapping_mk/' . $id)),
                array('label' => 'Import Excel', 'active' => true)
            );
            $data['sidebar_menu'] = generate_sidebar_menu(get_user_role());
            $data['content'] = $this->load->view('kurikulum/import_excel', $data, true);
            $this->load->view('layouts/main', $data);
        }
    }

    /**
     * Toggle aktif/nonaktif kurikulum (bisa banyak yang aktif)
     */
    public function toggle_aktif($id) {
        $this->assert_not_kaprodi('Kurikulum');
        $kurikulum = $this->Kurikulum_model->get_by_id($id);
        if (!$kurikulum) {
            $this->session->set_flashdata('error', 'Kurikulum tidak ditemukan.');
            redirect('Kurikulum');
        }

        if ($this->Kurikulum_model->toggle_aktif($id)) {
            $status = $kurikulum->is_aktif == 1 ? 'dinonaktifkan' : 'diaktifkan';
            $this->session->set_flashdata('success', 'Kurikulum berhasil ' . $status . '.');
        } else {
            $this->session->set_flashdata('error', 'Gagal mengubah status kurikulum.');
        }
        redirect('Kurikulum');
    }

    /**
     * Set aktif semua mata kuliah untuk kurikulum berdasarkan semester aktif
     */
    public function set_aktif_mata_kuliah($id) {
        $this->assert_not_kaprodi('Kurikulum');
        $kurikulum = $this->Kurikulum_model->get_by_id($id);
        if (!$kurikulum) {
            $this->session->set_flashdata('error', 'Kurikulum tidak ditemukan.');
            redirect('Kurikulum');
        }

        // Cek apakah kurikulum aktif
        if ($kurikulum->is_aktif != 1) {
            $this->session->set_flashdata('error', 'Kurikulum harus diaktifkan terlebih dahulu.');
            redirect('Kurikulum/mapping_mk/' . $id);
        }

        // Ambil tahun akademik aktif
        $this->load->model('Tahun_akademik_model');
        $this->load->model('Mata_kuliah_aktif_model');
        $tahun_aktif = $this->Tahun_akademik_model->get_aktif();
        
        if (!$tahun_aktif) {
            $this->session->set_flashdata('error', 'Tidak ada tahun akademik yang aktif.');
            redirect('Kurikulum/mapping_mk/' . $id);
        }

        // Hitung semester aktif untuk kurikulum ini
        $tahun_parts = explode('/', $tahun_aktif->tahun_akademik);
        $tahun_akademik_start = intval($tahun_parts[0]);
        $semester_value = ($tahun_aktif->semester == 'ganjil') ? 1 : 2;
        $selisih_tahun = $tahun_akademik_start - $kurikulum->tahun_berlaku;
        $semester_aktif = min(($selisih_tahun * 2) + $semester_value, 8);

        // Ambil semua mapping MK untuk kurikulum ini
        $mapping_mk = $this->Kurikulum_model->get_all_mapping_mk($id, 1000, 0);
        
        $count = 0;
        foreach ($mapping_mk as $mk) {
            // Set aktif semua mata kuliah dengan semester sesuai semester aktif
            // Gunakan semester dari mapping MK sebagai semester_aktif
            $this->Mata_kuliah_aktif_model->set_aktif(
                $id, 
                $mk->id, // id_kurikulum_detail
                $tahun_aktif->id, 
                $mk->semester // Gunakan semester dari mapping MK
            );
            $count++;
        }

        if ($count > 0) {
            $this->session->set_flashdata('success', 'Berhasil mengaktifkan ' . $count . ' mata kuliah untuk kurikulum ini. Mata kuliah ini akan muncul di menu Mata Kuliah Aktif.');
        } else {
            $this->session->set_flashdata('info', 'Tidak ada mata kuliah yang perlu diaktifkan.');
        }
        
        redirect('Kurikulum/mapping_mk/' . $id);
    }


    /**
     * Download kurikulum (daftar mapping mata kuliah) ke Excel/CSV
     */
    public function download($id) {
        $kurikulum = $this->Kurikulum_model->get_by_id($id);
        if (!$kurikulum) {
            $this->session->set_flashdata('error', 'Kurikulum tidak ditemukan.');
            redirect('Kurikulum');
        }

        $mapping = $this->Kurikulum_model->get_all_mapping_mk($id);
        if (empty($mapping)) {
            $this->session->set_flashdata('warning', 'Kurikulum belum memiliki mapping mata kuliah untuk di-download.');
            redirect('Kurikulum/mapping_mk/' . $id);
        }

        $safe_name = preg_replace('/[^A-Za-z0-9_\-]+/', '_', $kurikulum->nama_kurikulum);
        $filename_base = 'Kurikulum_' . $kurikulum->tahun_berlaku . '_' . $safe_name . '_' . date('YmdHis');

        $rows = array();
        foreach ($mapping as $mk) {
            $is_wajib = (isset($mk->jenis) && strtolower((string)$mk->jenis) === 'wajib') ? 1 : 0;
            $rows[] = array(
                'kode_mk' => $mk->kode_mk ?: '',
                'nama_mk' => $mk->nama_mk ?: '',
                'sks' => isset($mk->sks) ? $mk->sks : '',
                'semester' => isset($mk->semester) ? $mk->semester : '',
                'is_wajib' => $is_wajib,
                'jenis' => isset($mk->jenis) ? $mk->jenis : ($is_wajib ? 'wajib' : 'pilihan')
            );
        }

        while (ob_get_level() > 0) {
            @ob_end_clean();
        }

        $autoload = FCPATH . 'vendor/autoload.php';
        $can_xlsx = file_exists($autoload) && extension_loaded('zip') && class_exists('ZipArchive', false);

        if ($can_xlsx) {
            try {
                require_once $autoload;
                if (class_exists('\\PhpOffice\\PhpSpreadsheet\\Spreadsheet')) {
                    $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
                    $sheet = $spreadsheet->getActiveSheet();
                    $sheet->setTitle('Kurikulum');

                    $sheet->setCellValue('A1', 'DOWNLOAD KURIKULUM');
                    $sheet->mergeCells('A1:F1');
                    $sheet->setCellValue('A2', 'Nama Kurikulum: ' . $kurikulum->nama_kurikulum);
                    $sheet->mergeCells('A2:F2');
                    $sheet->setCellValue('A3', 'Tahun Berlaku: ' . $kurikulum->tahun_berlaku);
                    $sheet->mergeCells('A3:F3');
                    $sheet->setCellValue('A4', 'Status: ' . ($kurikulum->is_aktif ? 'Aktif' : 'Tidak Aktif'));
                    $sheet->mergeCells('A4:F4');
                    $sheet->setCellValue('A5', 'Tanggal Download: ' . date('d/m/Y H:i:s'));
                    $sheet->mergeCells('A5:F5');

                    $headers = array('No', 'kode_mk', 'nama_mk', 'sks', 'semester', 'is_wajib');
                    $col = 'A';
                    foreach ($headers as $header) {
                        $sheet->setCellValue($col . '7', $header);
                        $col++;
                    }
                    $sheet->getStyle('A7:F7')->getFont()->setBold(true);
                    $sheet->getStyle('A7:F7')->getFill()
                        ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                        ->getStartColor()->setARGB('FF4472C4');
                    $sheet->getStyle('A7:F7')->getFont()->getColor()->setARGB('FFFFFFFF');

                    $excel_row = 8;
                    $no = 1;
                    foreach ($rows as $row) {
                        $sheet->setCellValue('A' . $excel_row, $no++);
                        $sheet->setCellValueExplicit('B' . $excel_row, (string)$row['kode_mk'], \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
                        $sheet->setCellValue('C' . $excel_row, $row['nama_mk']);
                        $sheet->setCellValue('D' . $excel_row, $row['sks']);
                        $sheet->setCellValue('E' . $excel_row, $row['semester']);
                        $sheet->setCellValue('F' . $excel_row, $row['is_wajib']);
                        $excel_row++;
                    }

                    foreach (range('A', 'F') as $c) {
                        $sheet->getColumnDimension($c)->setAutoSize(true);
                    }

                    $styleArray = array(
                        'borders' => array(
                            'allBorders' => array(
                                'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                            ),
                        ),
                    );
                    $sheet->getStyle('A7:F' . ($excel_row - 1))->applyFromArray($styleArray);

                    $filename = $filename_base . '.xlsx';
                    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
                    header('Content-Disposition: attachment;filename="' . $filename . '"');
                    header('Cache-Control: max-age=0');
                    header('Pragma: public');

                    $writer = \PhpOffice\PhpSpreadsheet\IOFactory::createWriter($spreadsheet, 'Xlsx');
                    $writer->save('php://output');
                    exit;
                }
            } catch (Throwable $e) {
                log_message('error', 'Kurikulum download xlsx gagal, fallback CSV: ' . $e->getMessage());
            }
        }

        // Fallback CSV (kompatibel dengan template import)
        $filename = $filename_base . '.csv';
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment;filename="' . $filename . '"');
        header('Cache-Control: max-age=0');
        header('Pragma: public');

        $output = fopen('php://output', 'w');
        fprintf($output, chr(0xEF) . chr(0xBB) . chr(0xBF));
        fputcsv($output, array('DOWNLOAD KURIKULUM'));
        fputcsv($output, array('Nama Kurikulum: ' . $kurikulum->nama_kurikulum));
        fputcsv($output, array('Tahun Berlaku: ' . $kurikulum->tahun_berlaku));
        fputcsv($output, array('Status: ' . ($kurikulum->is_aktif ? 'Aktif' : 'Tidak Aktif')));
        fputcsv($output, array('Tanggal Download: ' . date('d/m/Y H:i:s')));
        fputcsv($output, array(''));
        fputcsv($output, array('No', 'kode_mk', 'nama_mk', 'sks', 'semester', 'is_wajib'));

        $no = 1;
        foreach ($rows as $row) {
            fputcsv($output, array(
                $no++,
                $row['kode_mk'],
                $row['nama_mk'],
                $row['sks'],
                $row['semester'],
                $row['is_wajib']
            ));
        }

        fclose($output);
        exit;
    }

    /**
     * Download template Excel/CSV
     */
    public function download_template() {
        $this->assert_not_kaprodi('Kurikulum');
        // Buat folder jika belum ada
        if (!is_dir(APPPATH . '../uploads/templates/')) {
            mkdir(APPPATH . '../uploads/templates/', 0755, true);
        }

        // Buat file CSV (lebih sederhana, bisa dibuka di Excel)
        $filename = 'template_mapping_mk.csv';
        $csv_file = APPPATH . '../uploads/templates/' . $filename;
        
        // Gunakan BOM UTF-8 agar Excel membaca dengan benar
        $fp = fopen($csv_file, 'w');
        fprintf($fp, chr(0xEF).chr(0xBB).chr(0xBF)); // UTF-8 BOM
        
        // Header
        fputcsv($fp, array('kode_mk', 'nama_mk', 'sks', 'semester', 'is_wajib'));
        
        // Contoh data
        fputcsv($fp, array('MK001', 'Pemrograman Dasar', '3', '1', '1'));
        fputcsv($fp, array('MK002', 'Struktur Data', '3', '2', '1'));
        fputcsv($fp, array('MK003', 'Basis Data', '3', '2', '1'));
        fputcsv($fp, array('MK004', 'Algoritma dan Pemrograman', '3', '1', '1'));
        fputcsv($fp, array('MK005', 'Pemrograman Web', '3', '3', '1'));
        
        fclose($fp);

        // Download file
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Content-Length: ' . filesize($csv_file));
        readfile($csv_file);
        
        // Hapus file setelah download (opsional)
        // unlink($csv_file);
        exit;
    }

    /**
     * Hapus kurikulum
     */
    public function delete($id) {
        $this->assert_not_kaprodi('Kurikulum');
        if ($this->Kurikulum_model->delete($id)) {
            $this->session->set_flashdata('success', 'Kurikulum berhasil dihapus.');
        } else {
            $this->session->set_flashdata('error', 'Gagal menghapus kurikulum.');
        }
        redirect('Kurikulum');
    }
}

