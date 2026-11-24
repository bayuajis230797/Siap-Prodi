<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Mahasiswa Controller
 * Mengelola data mahasiswa dengan auto-create user account
 */
class Mahasiswa extends MY_Controller {

    public function __construct() {
        parent::__construct();
        $this->check_role('admin');
        $this->load->model('Mahasiswa_model');
        $this->load->model('Kurikulum_model');
        $this->load->model('Tahun_akademik_model');
        $this->load->model('User_model');
    }

    /**
     * List semua mahasiswa dengan pagination
     */
    public function index() {
        $this->load->library('pagination');
        
        // Ambil parameter per_page dari GET, default 10
        $per_page = $this->input->get('per_page') ? (int)$this->input->get('per_page') : 10;
        $per_page = in_array($per_page, [10, 25, 50, 100]) ? $per_page : 10;
        
        // Pagination config
        $config['base_url'] = site_url('Mahasiswa/index');
        $config['total_rows'] = $this->Mahasiswa_model->count_all();
        $config['per_page'] = $per_page;
        $config['uri_segment'] = 3;
        
        // Tambahkan per_page ke query string
        if ($per_page != 10) {
            $config['suffix'] = '?per_page=' . $per_page;
            $config['first_url'] = site_url('Mahasiswa/index?per_page=' . $per_page);
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
        
        $data['mahasiswa'] = $this->Mahasiswa_model->get_all($config['per_page'], $offset);
        $data['pagination'] = $this->pagination->create_links();
        $data['total_rows'] = $config['total_rows'];
        $data['per_page'] = $per_page;
        
        // Ambil daftar kelas untuk dropdown download
        $data['daftar_kelas'] = $this->Mahasiswa_model->get_all_kelas('aktif');
        
        $data['title'] = 'Mahasiswa';
        $data['page_title'] = 'Master Data Mahasiswa';
        $data['breadcrumb'] = array(
            array('label' => 'Mahasiswa', 'active' => true)
        );
        $data['sidebar_menu'] = generate_sidebar_menu('admin');
        $data['content'] = $this->load->view('mahasiswa/index', $data, true);
        $this->load->view('layouts/main', $data);
    }

    /**
     * Form tambah mahasiswa
     */
    public function create() {
        if ($this->input->method() === 'post') {
            $this->form_validation->set_rules('nim', 'NIM', 'required|trim|is_unique[mahasiswa.nim]');
            $this->form_validation->set_rules('nama_mahasiswa', 'Nama Mahasiswa', 'required|trim');
            $this->form_validation->set_rules('angkatan', 'Angkatan', 'required|trim|numeric');
            $this->form_validation->set_rules('id_kurikulum', 'Kurikulum', 'required|trim|numeric');
            $this->form_validation->set_rules('status', 'Status', 'required|trim');

            if ($this->form_validation->run() == FALSE) {
                $data['kurikulum'] = $this->Kurikulum_model->get_all_aktif();
                $data['title'] = 'Tambah Mahasiswa';
                $data['page_title'] = 'Tambah Mahasiswa';
                $data['breadcrumb'] = array(
                    array('label' => 'Mahasiswa', 'url' => site_url('Mahasiswa')),
                    array('label' => 'Tambah', 'active' => true)
                );
                $data['sidebar_menu'] = generate_sidebar_menu('admin');
                $data['content'] = $this->load->view('mahasiswa/create', $data, true);
                $this->load->view('layouts/main', $data);
            } else {
                // Hitung semester aktif berdasarkan angkatan dan tahun akademik aktif
                $this->load->library('Kenaikan_kelas');
                $attr = $this->kenaikan_kelas->atribut_baru($this->input->post('angkatan'), $this->input->post('kelas'));
                $semester_aktif = $attr['semester_aktif'];
                
                // Generate kelas otomatis atau ambil dari input
                $kelas = $this->input->post('kelas');
                if (empty($kelas)) {
                    // Generate kelas otomatis berdasarkan semester dan apakah karyawan
                    $is_karyawan = (strpos(strtolower($this->input->post('nama_mahasiswa')), 'karyawan') !== false || 
                                   strpos(strtolower($this->input->post('nim')), 'K') !== false);
                    $kelas = $this->Mahasiswa_model->generate_kelas($semester_aktif, $is_karyawan);
                }
                
                $data_mahasiswa = array(
                    'nim' => $this->input->post('nim'),
                    'nama_mahasiswa' => $this->input->post('nama_mahasiswa'),
                    'angkatan' => $this->input->post('angkatan'),
                    'semester_aktif' => $semester_aktif,
                    'kelas' => $kelas,
                    'id_kurikulum' => $this->input->post('id_kurikulum'),
                    'status' => $this->input->post('status')
                );
                if (isset($attr['semester_riil'])) {
                    $data_mahasiswa['semester_riil'] = $attr['semester_riil'];
                    $data_mahasiswa['rombel'] = Kenaikan_kelas::rombel_dari_kelas($kelas);
                }

                // Insert mahasiswa
                $mahasiswa_id = $this->Mahasiswa_model->create($data_mahasiswa);
                
                if ($mahasiswa_id) {
                    // Auto-create user account
                    $this->create_user_account($this->input->post('nim'), $mahasiswa_id);
                    
                    $this->session->set_flashdata('success', 'Mahasiswa berhasil ditambahkan. User account otomatis dibuat dengan username dan password: ' . $this->input->post('nim'));
                    redirect('Mahasiswa');
                } else {
                    $this->session->set_flashdata('error', 'Gagal menambahkan mahasiswa.');
                    redirect('Mahasiswa/create');
                }
            }
        } else {
            $data['kurikulum'] = $this->Kurikulum_model->get_all_aktif();
            $data['title'] = 'Tambah Mahasiswa';
            $data['page_title'] = 'Tambah Mahasiswa';
            $data['breadcrumb'] = array(
                array('label' => 'Mahasiswa', 'url' => site_url('Mahasiswa')),
                array('label' => 'Tambah', 'active' => true)
            );
            $data['sidebar_menu'] = generate_sidebar_menu('admin');
            $data['content'] = $this->load->view('mahasiswa/create', $data, true);
            $this->load->view('layouts/main', $data);
        }
    }

    /**
     * Form edit mahasiswa
     */
    public function edit($id) {
        $mahasiswa = $this->Mahasiswa_model->get_by_id($id, false); // data tersimpan (bukan hasil hitung tahun akademik konteks)
        
        if (!$mahasiswa) {
            $this->session->set_flashdata('error', 'Mahasiswa tidak ditemukan.');
            redirect('Mahasiswa');
        }

        if ($this->input->method() === 'post') {
            // Cek apakah NIM berubah
            $nim_baru = $this->input->post('nim');
            $is_unique = ($nim_baru == $mahasiswa->nim) ? '' : '|is_unique[mahasiswa.nim]';
            
            $this->form_validation->set_rules('nim', 'NIM', 'required|trim' . $is_unique);
            $this->form_validation->set_rules('nama_mahasiswa', 'Nama Mahasiswa', 'required|trim');
            $this->form_validation->set_rules('angkatan', 'Angkatan', 'required|trim|numeric');
            $this->form_validation->set_rules('id_kurikulum', 'Kurikulum', 'required|trim|numeric');
            $this->form_validation->set_rules('status', 'Status', 'required|trim');

            if ($this->form_validation->run() == FALSE) {
                $data['mahasiswa'] = $mahasiswa;
                $data['kurikulum'] = $this->Kurikulum_model->get_all_aktif();
                $data['title'] = 'Edit Mahasiswa';
                $data['page_title'] = 'Edit Mahasiswa';
                $data['breadcrumb'] = array(
                    array('label' => 'Mahasiswa', 'url' => site_url('Mahasiswa')),
                    array('label' => 'Edit', 'active' => true)
                );
                $data['sidebar_menu'] = generate_sidebar_menu('admin');
                $data['content'] = $this->load->view('mahasiswa/edit', $data, true);
                $this->load->view('layouts/main', $data);
            } else {
                // Hitung semester aktif jika angkatan berubah
                $this->load->library('Kenaikan_kelas');
                $ekstra = array();
                $semester_aktif = $mahasiswa->semester_aktif;
                if ($this->input->post('angkatan') != $mahasiswa->angkatan) {
                    $attr = $this->kenaikan_kelas->atribut_baru($this->input->post('angkatan'), $this->input->post('kelas'));
                    $semester_aktif = $attr['semester_aktif'];
                    if (isset($attr['semester_riil'])) {
                        $ekstra['semester_riil'] = $attr['semester_riil'];
                    }
                }
                
                // Update kelas jika semester berubah atau kelas diinput manual
                $kelas = $this->input->post('kelas');
                if (empty($kelas) && $semester_aktif != $mahasiswa->semester_aktif) {
                    // Jika semester berubah, update kelas sesuai semester baru
                    // Huruf kelas = rombel asli (bukan huruf kelas yang mungkin sudah bergeser)
                    $huruf_kelas = !empty($mahasiswa->rombel) ? $mahasiswa->rombel : Kenaikan_kelas::rombel_dari_kelas($mahasiswa->kelas);
                    $kelas = $semester_aktif . $huruf_kelas;
                } elseif (empty($kelas)) {
                    // Jika kelas kosong dan semester tidak berubah, gunakan kelas lama
                    $kelas = $mahasiswa->kelas;
                }
                
                $data = array(
                    'nim' => $this->input->post('nim'),
                    'nama_mahasiswa' => $this->input->post('nama_mahasiswa'),
                    'angkatan' => $this->input->post('angkatan'),
                    'semester_aktif' => $semester_aktif,
                    'kelas' => $kelas,
                    'id_kurikulum' => $this->input->post('id_kurikulum'),
                    'status' => $this->input->post('status')
                );
                if ($this->kenaikan_kelas->kolom_baru_ada()) {
                    $data = array_merge($data, $ekstra);
                    if (!empty($this->input->post('kelas'))) {
                        $data['rombel'] = Kenaikan_kelas::rombel_dari_kelas($this->input->post('kelas'));
                    }
                    // Perubahan status manual = bukan lulus otomatis lagi
                    if ($this->input->post('status') !== $mahasiswa->status) {
                        $data['lulus_otomatis'] = 0;
                    }
                }

                if ($this->Mahasiswa_model->update($id, $data)) {
                    // Update user account jika NIM berubah
                    if ($nim_baru != $mahasiswa->nim) {
                        $this->update_user_account($mahasiswa->nim, $nim_baru, $id);
                    }
                    
                    $this->session->set_flashdata('success', 'Mahasiswa berhasil diupdate.');
                    redirect('Mahasiswa');
                } else {
                    $this->session->set_flashdata('error', 'Gagal mengupdate mahasiswa.');
                    redirect('Mahasiswa/edit/' . $id);
                }
            }
        } else {
            $data['mahasiswa'] = $mahasiswa;
            $data['kurikulum'] = $this->Kurikulum_model->get_all_aktif();
            $data['title'] = 'Edit Mahasiswa';
            $data['page_title'] = 'Edit Mahasiswa';
            $data['breadcrumb'] = array(
                array('label' => 'Mahasiswa', 'url' => site_url('Mahasiswa')),
                array('label' => 'Edit', 'active' => true)
            );
            $data['sidebar_menu'] = generate_sidebar_menu('admin');
            $data['content'] = $this->load->view('mahasiswa/edit', $data, true);
            $this->load->view('layouts/main', $data);
        }
    }

    /**
     * Hapus mahasiswa
     */
    public function delete($id) {
        $mahasiswa = $this->Mahasiswa_model->get_by_id($id, false); // data tersimpan (bukan hasil hitung tahun akademik konteks)
        
        if (!$mahasiswa) {
            $this->session->set_flashdata('error', 'Mahasiswa tidak ditemukan.');
            redirect('Mahasiswa');
        }

        // Hapus user account terlebih dahulu
        $this->delete_user_account($mahasiswa->nim);

        if ($this->Mahasiswa_model->delete($id)) {
            $this->session->set_flashdata('success', 'Mahasiswa berhasil dihapus.');
        } else {
            $this->session->set_flashdata('error', 'Gagal menghapus mahasiswa.');
        }
        redirect('Mahasiswa');
    }

    /**
     * Import mahasiswa dari Excel
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
                redirect('Mahasiswa/import');
            }

            $upload_data = $this->upload->data();
            $file_path = $config['upload_path'] . $upload_data['file_name'];

            // Load library Excel_reader
            $this->load->library('Excel_reader');
            $result = $this->excel_reader->read_excel_mahasiswa($file_path);

            // Hapus file temporary
            unlink($file_path);

            if ($result['success']) {
                $this->session->set_flashdata('success', $result['message']);
            } else {
                $this->session->set_flashdata('error', $result['message']);
            }

            redirect('Mahasiswa');
        } else {
            // Ambil daftar kurikulum aktif untuk ditampilkan
            $data['kurikulum'] = $this->Kurikulum_model->get_all_aktif();
            $data['title'] = 'Import Mahasiswa';
            $data['page_title'] = 'Import Mahasiswa dari Excel';
            $data['breadcrumb'] = array(
                array('label' => 'Mahasiswa', 'url' => site_url('Mahasiswa')),
                array('label' => 'Import', 'active' => true)
            );
            $data['sidebar_menu'] = generate_sidebar_menu('admin');
            $data['content'] = $this->load->view('mahasiswa/import', $data, true);
            $this->load->view('layouts/main', $data);
        }
    }

    /**
     * Download template Excel untuk import
     */
    public function download_template() {
        $kurikulum_id = 1;
        try {
            $kurikulum_list = $this->Kurikulum_model->get_all_aktif();
            if (!empty($kurikulum_list) && isset($kurikulum_list[0]->id)) {
                $kurikulum_id = $kurikulum_list[0]->id;
            }
        } catch (Exception $e) {
            log_message('error', 'Mahasiswa download_template kurikulum: ' . $e->getMessage());
        }

        // Bersihkan buffer agar header download tidak gagal
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

                    $sheet->setCellValue('A1', 'nim');
                    $sheet->setCellValue('B1', 'nama_mahasiswa');
                    $sheet->setCellValue('C1', 'angkatan');
                    $sheet->setCellValue('D1', 'id_kurikulum');
                    $sheet->setCellValue('E1', 'kelas');
                    $sheet->setCellValue('F1', 'status');

                    $sheet->setCellValue('A2', '202001001');
                    $sheet->setCellValue('B2', 'Contoh Mahasiswa 1');
                    $sheet->setCellValue('C2', '2020');
                    $sheet->setCellValue('D2', $kurikulum_id);
                    $sheet->setCellValue('E2', '1A');
                    $sheet->setCellValue('F2', 'aktif');

                    $sheet->setCellValue('A3', '202001002');
                    $sheet->setCellValue('B3', 'Contoh Mahasiswa Karyawan');
                    $sheet->setCellValue('C3', '2020');
                    $sheet->setCellValue('D3', $kurikulum_id);
                    $sheet->setCellValue('E3', '1G');
                    $sheet->setCellValue('F3', 'aktif');

                    $sheet->getStyle('A1:F1')->getFont()->setBold(true);
                    $sheet->getStyle('A1:F1')->getFill()
                        ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                        ->getStartColor()->setARGB('FFE0E0E0');

                    foreach (range('A', 'F') as $col) {
                        $sheet->getColumnDimension($col)->setAutoSize(true);
                    }

                    $sheet->setCellValue('A5', 'Catatan:');
                    $sheet->setCellValue('A6', '- Kolom kelas (opsional): Format {semester}{huruf}, contoh: 1A, 1B, 1G (untuk karyawan)');
                    $sheet->setCellValue('A7', '- Jika kelas dikosongkan, akan otomatis di-generate berdasarkan semester');
                    $sheet->setCellValue('A8', '- id_kurikulum: Gunakan ID kurikulum yang aktif (lihat di menu Kurikulum)');
                    $sheet->getStyle('A5')->getFont()->setBold(true);
                    $sheet->getStyle('A6:A8')->getFont()->setItalic(true);

                    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
                    header('Content-Disposition: attachment;filename="template_import_mahasiswa.xlsx"');
                    header('Cache-Control: max-age=0');
                    header('Pragma: public');

                    $writer = \PhpOffice\PhpSpreadsheet\IOFactory::createWriter($spreadsheet, 'Xlsx');
                    $writer->save('php://output');
                    exit;
                }
            } catch (Throwable $e) {
                log_message('error', 'Mahasiswa download_template xlsx gagal, fallback CSV: ' . $e->getMessage());
            }
        }

        // Fallback CSV (selalu tersedia, tidak butuh Zip/PhpSpreadsheet)
        $this->download_template_csv($kurikulum_id);
    }

    /**
     * Fallback download template CSV
     */
    private function download_template_csv($kurikulum_id) {
        while (ob_get_level() > 0) {
            @ob_end_clean();
        }

        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment;filename="template_import_mahasiswa.csv"');
        header('Cache-Control: max-age=0');
        header('Pragma: public');

        $output = fopen('php://output', 'w');
        // BOM agar Excel Windows membaca UTF-8 dengan benar
        fprintf($output, chr(0xEF) . chr(0xBB) . chr(0xBF));
        fputcsv($output, array('nim', 'nama_mahasiswa', 'angkatan', 'id_kurikulum', 'kelas', 'status'));
        fputcsv($output, array('202001001', 'Contoh Mahasiswa 1', '2020', $kurikulum_id, '1A', 'aktif'));
        fputcsv($output, array('202001002', 'Contoh Mahasiswa Karyawan', '2020', $kurikulum_id, '1G', 'aktif'));
        fclose($output);
        exit;
    }

    /**
     * Hitung semester aktif berdasarkan angkatan dan tahun akademik aktif
     */
    private function calculate_semester_aktif($angkatan) {
        $this->load->library('Kenaikan_kelas');
        $a = $this->kenaikan_kelas->atribut_baru($angkatan);
        return $a['semester_aktif'];
    }

    /**
     * Buat user account untuk mahasiswa
     */
    private function create_user_account($nim, $mahasiswa_id) {
        // Ambil role_id untuk mahasiswa
        $this->db->where('nama_role', 'mahasiswa');
        $role = $this->db->get('roles')->row();
        
        if (!$role) {
            return false;
        }
        
        // Cek apakah user sudah ada
        $this->db->where('username', $nim);
        $existing = $this->db->get('users')->row();
        
        if ($existing) {
            // Update ref_id jika user sudah ada
            $this->db->where('id', $existing->id);
            $this->db->update('users', array('ref_id' => $mahasiswa_id));
            return true;
        }
        
        // Insert user baru
        $data = array(
            'username' => $nim,
            'password' => md5($nim), // Password default = MD5(NIM)
            'role_id' => $role->id,
            'ref_id' => $mahasiswa_id
        );
        
        $this->db->insert('users', $data);
        return $this->db->insert_id();
    }

    /**
     * Update user account jika NIM berubah
     */
    private function update_user_account($nim_lama, $nim_baru, $mahasiswa_id) {
        // Update username dan password
        $this->db->where('username', $nim_lama);
        $this->db->where('ref_id', $mahasiswa_id);
        $this->db->update('users', array(
            'username' => $nim_baru,
            'password' => md5($nim_baru)
        ));
    }

    /**
     * Hapus user account mahasiswa
     */
    private function delete_user_account($nim) {
        $this->db->where('username', $nim);
        $this->db->delete('users');
    }

    /**
     * Download daftar mahasiswa per kelas ke Excel
     */
    public function download_per_kelas() {
        $kelas = $this->input->get('kelas');
        
        if (empty($kelas)) {
            $this->session->set_flashdata('error', 'Kelas harus dipilih.');
            redirect('Mahasiswa');
        }
        
        // Ambil data mahasiswa per kelas
        $mahasiswa = $this->Mahasiswa_model->get_by_kelas($kelas, null);
        
        if (empty($mahasiswa)) {
            $this->session->set_flashdata('error', 'Tidak ada data mahasiswa untuk kelas ' . $kelas . '.');
            redirect('Mahasiswa');
        }
        
        // Cek apakah PhpSpreadsheet tersedia
        $use_phpspreadsheet = false;
        if (file_exists(APPPATH . '../vendor/autoload.php')) {
            require_once APPPATH . '../vendor/autoload.php';
            if (class_exists('PhpOffice\\PhpSpreadsheet\\Spreadsheet')) {
                $use_phpspreadsheet = true;
            }
        }
        
        $filename_base = 'Daftar_Mahasiswa_Kelas_' . $kelas . '_' . date('YmdHis');
        
        if ($use_phpspreadsheet) {
            $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet();
            
            $sheet->setCellValue('A1', 'DAFTAR MAHASISWA KELAS ' . strtoupper($kelas));
            $sheet->mergeCells('A1:H1');
            $sheet->getStyle('A1')->applyFromArray([
                'font' => ['bold' => true, 'size' => 16],
                'alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER]
            ]);
            
            $sheet->setCellValue('A2', 'Tanggal Export: ' . date('d F Y H:i:s'));
            $sheet->mergeCells('A2:H2');
            
            $headerRow = 4;
            $headers = ['A' => 'No', 'B' => 'NIM', 'C' => 'Nama Mahasiswa', 'D' => 'Angkatan', 'E' => 'Semester Aktif', 'F' => 'Kurikulum', 'G' => 'Tahun Berlaku', 'H' => 'Status'];
            
            foreach ($headers as $col => $label) {
                $sheet->setCellValue($col . $headerRow, $label);
            }
            
            $sheet->getStyle('A' . $headerRow . ':H' . $headerRow)->applyFromArray([
                'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                'fill' => ['fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID, 'startColor' => ['rgb' => '1F4E78']],
                'alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER]
            ]);
            
            $row = $headerRow + 1;
            $no = 1;
            foreach ($mahasiswa as $mhs) {
                $sheet->setCellValue('A' . $row, $no++);
                $sheet->setCellValue('B' . $row, $mhs->nim);
                $sheet->setCellValue('C' . $row, $mhs->nama_mahasiswa);
                $sheet->setCellValue('D' . $row, $mhs->angkatan);
                $sheet->setCellValue('E' . $row, 'Semester ' . $mhs->semester_aktif);
                $sheet->setCellValue('F' . $row, $mhs->nama_kurikulum ?? '-');
                $sheet->setCellValue('G' . $row, $mhs->tahun_berlaku ?? '-');
                $sheet->setCellValue('H' . $row, ucfirst($mhs->status));
                $row++;
            }
            
            $sheet->setCellValue('A' . $row, 'TOTAL');
            $sheet->mergeCells('A' . $row . ':C' . $row);
            $sheet->setCellValue('D' . $row, count($mahasiswa) . ' Mahasiswa');
            $sheet->mergeCells('D' . $row . ':H' . $row);
            
            foreach (['A' => 6, 'B' => 15, 'C' => 35, 'D' => 12, 'E' => 15, 'F' => 25, 'G' => 15, 'H' => 12] as $col => $width) {
                $sheet->getColumnDimension($col)->setWidth($width);
            }
            
            $filename = $filename_base . '.xlsx';
            header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
            header('Content-Disposition: attachment;filename="' . $filename . '"');
            header('Cache-Control: max-age=0');
            
            $writer = \PhpOffice\PhpSpreadsheet\IOFactory::createWriter($spreadsheet, 'Xlsx');
            $writer->save('php://output');
            exit;
        } else {
            $filename = $filename_base . '.csv';
            header('Content-Type: text/csv; charset=utf-8');
            header('Content-Disposition: attachment;filename="' . $filename . '"');
            
            $output = fopen('php://output', 'w');
            fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));
            
            fputcsv($output, ['DAFTAR MAHASISWA KELAS ' . strtoupper($kelas)]);
            fputcsv($output, ['Tanggal Export: ' . date('d F Y H:i:s')]);
            fputcsv($output, []);
            fputcsv($output, ['No', 'NIM', 'Nama Mahasiswa', 'Angkatan', 'Semester Aktif', 'Kurikulum', 'Tahun Berlaku', 'Status']);
            
            $no = 1;
            foreach ($mahasiswa as $mhs) {
                fputcsv($output, [$no++, $mhs->nim, $mhs->nama_mahasiswa, $mhs->angkatan, 'Semester ' . $mhs->semester_aktif, $mhs->nama_kurikulum ?? '-', $mhs->tahun_berlaku ?? '-', ucfirst($mhs->status)]);
            }
            
            fputcsv($output, []);
            fputcsv($output, ['TOTAL', count($mahasiswa) . ' Mahasiswa']);
            
            fclose($output);
            exit;
        }
    }
}
