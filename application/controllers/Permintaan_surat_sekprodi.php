<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Permintaan Surat Sekprodi Controller
 * Untuk sekprodi mengelola permintaan surat dari mahasiswa
 */
class Permintaan_surat_sekprodi extends MY_Controller {

    public function __construct() {
        parent::__construct();
        $this->check_role(array('admin', 'sekprodi'));
        $this->load->model('Permintaan_surat_model');
        $this->load->model('Mahasiswa_model');
    }

    /**
     * List semua permintaan surat dengan pagination
     */
    public function index() {
        $this->load->library('pagination');
        
        // Filter status
        $filter_status = $this->input->get('status');
        
        // Ambil parameter per_page dari GET, default 10
        $per_page = $this->input->get('per_page') ? (int)$this->input->get('per_page') : 10;
        $per_page = in_array($per_page, [10, 25, 50, 100]) ? $per_page : 10;
        
        // Pagination config
        $config['base_url'] = site_url('Permintaan_surat_sekprodi/index');
        $config['total_rows'] = $this->Permintaan_surat_model->count_all($filter_status);
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
        
        // Tambahkan filter status dan per_page ke query string
        $query_params = array_filter(array('status' => $filter_status, 'per_page' => ($per_page != 10 ? $per_page : null)));
        $query_string = http_build_query($query_params);
        if ($query_string) {
            $config['suffix'] = '?' . $query_string;
            $config['first_url'] = site_url('Permintaan_surat_sekprodi/index?' . $query_string);
        }
        
        $this->pagination->initialize($config);
        
        $page = ($this->uri->segment(3)) ? $this->uri->segment(3) : 1;
        $offset = ($page - 1) * $config['per_page'];
        
        $data['permintaan'] = $this->Permintaan_surat_model->get_all($config['per_page'], $offset, $filter_status);
        $data['pagination'] = $this->pagination->create_links();
        $data['total_rows'] = $config['total_rows'];
        $data['filter_status'] = $filter_status;
        $data['per_page'] = $per_page;
        
        $data['title'] = 'Permintaan Surat Mahasiswa';
        $data['page_title'] = 'Permintaan Surat Mahasiswa';
        $data['breadcrumb'] = array(
            array('label' => 'Permintaan Surat', 'active' => true)
        );
        $data['sidebar_menu'] = generate_sidebar_menu('sekprodi');
        $data['content'] = $this->load->view('sekprodi/permintaan_surat_index', $data, true);
        $this->load->view('layouts/main', $data);
    }

    /**
     * Detail permintaan surat
     */
    public function detail($id) {
        $permintaan = $this->Permintaan_surat_model->get_by_id($id);
        
        if (!$permintaan) {
            $this->session->set_flashdata('error', 'Permintaan surat tidak ditemukan.');
            redirect('Permintaan_surat_sekprodi');
        }
        
        $data['permintaan'] = $permintaan;
        $data['title'] = 'Detail Permintaan Surat';
        $data['page_title'] = 'Detail Permintaan Surat';
        $data['breadcrumb'] = array(
            array('label' => 'Permintaan Surat', 'url' => site_url('Permintaan_surat_sekprodi')),
            array('label' => 'Detail', 'active' => true)
        );
        $data['sidebar_menu'] = generate_sidebar_menu('sekprodi');
        $data['content'] = $this->load->view('sekprodi/permintaan_surat_detail', $data, true);
        $this->load->view('layouts/main', $data);
    }

    /**
     * Proses permintaan surat (upload file atau update status)
     */
    public function proses($id) {
        $permintaan = $this->Permintaan_surat_model->get_by_id($id);
        
        if (!$permintaan) {
            $this->session->set_flashdata('error', 'Permintaan surat tidak ditemukan.');
            redirect('Permintaan_surat_sekprodi');
        }
        
        if ($this->input->method() === 'post') {
            $status = $this->input->post('status');
            $catatan = $this->input->post('catatan');
            
            $file_surat = null;
            $nama_file_surat = null;
            
            // Jika status selesai, upload file
            // Untuk soft_copy: wajib upload file (untuk didownload mahasiswa)
            // Untuk hard_copy: opsional upload file (hanya untuk arsip digital)
            if ($status == 'selesai') {
                if (!empty($_FILES['file_surat']['name'])) {
                    // Hapus file lama jika ada
                    if ($permintaan->file_surat) {
                        $old_file = FCPATH . $permintaan->file_surat;
                        if (file_exists($old_file)) {
                            unlink($old_file);
                        }
                    }
                    
                    $config['upload_path'] = './uploads/surat/';
                    $config['allowed_types'] = 'pdf|doc|docx';
                    $config['max_size'] = 5120; // 5MB
                    $config['encrypt_name'] = TRUE;
                    
                    if (!is_dir($config['upload_path'])) {
                        mkdir($config['upload_path'], 0755, true);
                    }
                    
                    $this->load->library('upload', $config);
                    
                    if (!$this->upload->do_upload('file_surat')) {
                        $this->session->set_flashdata('error', 'Gagal upload file: ' . $this->upload->display_errors());
                        redirect('Permintaan_surat_sekprodi/detail/' . $id);
                    }
                    
                    $upload_data = $this->upload->data();
                    $file_surat = 'uploads/surat/' . $upload_data['file_name'];
                    $nama_file_surat = $upload_data['orig_name'];
                } elseif (!$permintaan->file_surat) {
                    // Jika status selesai tapi belum ada file
                    // Untuk soft_copy: wajib upload file
                    // Untuk hard_copy: opsional
                    $tipe_surat = isset($permintaan->tipe_surat) ? $permintaan->tipe_surat : null;
                    if ($tipe_surat == 'soft_copy') {
                        $this->session->set_flashdata('error', 'File surat wajib diupload untuk surat Soft Copy. Mahasiswa akan mendownload file ini.');
                        redirect('Permintaan_surat_sekprodi/detail/' . $id);
                    } else {
                        // Hard copy: file opsional, biarkan kosong
                        $file_surat = null;
                        $nama_file_surat = null;
                    }
                } else {
                    // Jika sudah ada file, gunakan file yang ada
                    $file_surat = $permintaan->file_surat;
                    $nama_file_surat = $permintaan->nama_file_surat;
                }
            } else {
                // Jika status bukan selesai, hapus file jika ada
                if ($permintaan->file_surat) {
                    $old_file = FCPATH . $permintaan->file_surat;
                    if (file_exists($old_file)) {
                        unlink($old_file);
                    }
                }
                $file_surat = null;
                $nama_file_surat = null;
            }
            
            // Update status
            if ($this->Permintaan_surat_model->update_status($id, $status, $catatan, $file_surat, $nama_file_surat)) {
                $this->session->set_flashdata('success', 'Permintaan surat berhasil diproses.');
                redirect('Permintaan_surat_sekprodi/detail/' . $id);
            } else {
                $this->session->set_flashdata('error', 'Gagal memproses permintaan surat.');
                redirect('Permintaan_surat_sekprodi/detail/' . $id);
            }
        } else {
            redirect('Permintaan_surat_sekprodi/detail/' . $id);
        }
    }

    /**
     * Download file surat
     */
    public function download($id) {
        $permintaan = $this->Permintaan_surat_model->get_by_id($id);
        
        if (!$permintaan) {
            $this->session->set_flashdata('error', 'Permintaan surat tidak ditemukan.');
            redirect('Permintaan_surat_sekprodi');
        }
        
        if (!$permintaan->file_surat) {
            $this->session->set_flashdata('error', 'File surat belum tersedia.');
            redirect('Permintaan_surat_sekprodi/detail/' . $id);
        }
        
        $file_path = FCPATH . $permintaan->file_surat;
        
        if (!file_exists($file_path)) {
            $this->session->set_flashdata('error', 'File surat tidak ditemukan.');
            redirect('Permintaan_surat_sekprodi/detail/' . $id);
        }
        
        $this->load->helper('download');
        $file_name = $permintaan->nama_file_surat ?: basename($file_path);
        force_download($file_name, file_get_contents($file_path));
    }

    /**
     * Export permintaan surat ke Excel
     */
    public function export_excel() {
        $filter_status = $this->input->get('status');
        
        // Ambil semua data permintaan surat
        $permintaan_list = $this->Permintaan_surat_model->get_all(10000, 0, $filter_status);
        
        if (empty($permintaan_list)) {
            $this->session->set_flashdata('warning', 'Tidak ada data permintaan surat untuk di-export.');
            redirect('Permintaan_surat_sekprodi');
        }
        
        // Cek apakah PhpSpreadsheet tersedia
        $use_phpspreadsheet = false;
        if (file_exists(APPPATH . '../vendor/autoload.php')) {
            require_once APPPATH . '../vendor/autoload.php';
            if (class_exists('PhpOffice\PhpSpreadsheet\Spreadsheet')) {
                $use_phpspreadsheet = true;
            }
        }
        
        $filename_base = 'Permintaan_Surat_' . date('YmdHis');
        if ($filter_status) {
            $filename_base .= '_' . ucfirst($filter_status);
        }
        
        if ($use_phpspreadsheet) {
            // Gunakan PhpSpreadsheet
            $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet();
            $sheet->setTitle('Permintaan Surat');
            
            // Header
            $sheet->setCellValue('A1', 'LAPORAN PERMINTAAN SURAT');
            $sheet->mergeCells('A1:J1');
            $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14);
            $sheet->getStyle('A1')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
            
            $sheet->setCellValue('A2', 'Tanggal Export: ' . date('d/m/Y H:i'));
            $sheet->mergeCells('A2:J2');
            $sheet->getStyle('A2')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
            
            // Header tabel
            $headers = array('No', 'NIM', 'Nama Mahasiswa', 'Kelas', 'Jenis Surat', 'Untuk Siapa', 'Tipe Surat', 'Status', 'Tanggal Pengajuan', 'Keperluan');
            $col = 'A';
            $row = 4;
            
            foreach ($headers as $header) {
                $sheet->setCellValue($col . $row, $header);
                $sheet->getStyle($col . $row)->getFont()->setBold(true);
                $sheet->getStyle($col . $row)->getFill()
                    ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                    ->getStartColor()->setARGB('FFE0E0E0');
                $col++;
            }
            
            // Data
            $row = 5;
            $no = 1;
            foreach ($permintaan_list as $p) {
                $sheet->setCellValue('A' . $row, $no++);
                $sheet->setCellValue('B' . $row, $p->nim);
                $sheet->setCellValue('C' . $row, $p->nama_mahasiswa);
                $sheet->setCellValue('D' . $row, $p->kelas ?: '-');
                $sheet->setCellValue('E' . $row, $p->jenis_surat);
                $sheet->setCellValue('F' . $row, isset($p->untuk_siapa) && $p->untuk_siapa ? $p->untuk_siapa : '-');
                
                $tipe_label = '-';
                if (isset($p->tipe_surat)) {
                    $tipe_label = $p->tipe_surat == 'soft_copy' ? 'Soft Copy' : 'Hard Copy';
                }
                $sheet->setCellValue('G' . $row, $tipe_label);
                
                $sheet->setCellValue('H' . $row, ucfirst($p->status));
                $sheet->setCellValue('I' . $row, date('d/m/Y H:i', strtotime($p->created_at)));
                $sheet->setCellValue('J' . $row, $p->keperluan);
                
                // Tambahkan informasi tambahan jika ada
                if ($p->jenis_surat == 'Surat Permintaan Data Skripsi') {
                    if (isset($p->judul_skripsi) && $p->judul_skripsi) {
                        $sheet->setCellValue('K' . $row, 'Judul: ' . $p->judul_skripsi);
                    }
                    if (isset($p->lokasi_penelitian) && $p->lokasi_penelitian) {
                        $sheet->setCellValue('L' . $row, 'Lokasi: ' . $p->lokasi_penelitian);
                    }
                    // Metode pengumpulan data
                    $metode_list = array();
                    if (isset($p->pengambilan_data) && $p->pengambilan_data == 1) {
                        $metode_list[] = 'Pengambilan Data';
                    }
                    if (isset($p->observasi) && $p->observasi == 1) {
                        $metode_list[] = 'Observasi';
                    }
                    if (isset($p->wawancara) && $p->wawancara == 1) {
                        $metode_list[] = 'Wawancara';
                    }
                    if (!empty($metode_list)) {
                        $sheet->setCellValue('M' . $row, 'Metode: ' . implode(', ', $metode_list));
                    }
                } elseif ($p->jenis_surat == 'Surat Izin Tempat PKL') {
                    if (isset($p->nama_tempat_pkl) && $p->nama_tempat_pkl) {
                        $sheet->setCellValue('K' . $row, 'Tempat: ' . $p->nama_tempat_pkl);
                    }
                    if (isset($p->alamat_pkl) && $p->alamat_pkl) {
                        $sheet->setCellValue('L' . $row, 'Alamat: ' . $p->alamat_pkl);
                    }
                }
                
                $row++;
            }
            
            // Auto width columns
            foreach (range('A', 'J') as $col) {
                $sheet->getColumnDimension($col)->setAutoSize(true);
            }
            
            // Set borders
            $styleArray = array(
                'borders' => array(
                    'allBorders' => array(
                        'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                    ),
                ),
            );
            $sheet->getStyle('A4:J' . ($row - 1))->applyFromArray($styleArray);
            
            // Download
            header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
            header('Content-Disposition: attachment;filename="' . $filename_base . '.xlsx"');
            header('Cache-Control: max-age=0');
            
            $writer = \PhpOffice\PhpSpreadsheet\IOFactory::createWriter($spreadsheet, 'Xlsx');
            $writer->save('php://output');
            exit;
        } else {
            // Fallback: Export CSV
            $filename = $filename_base . '.csv';
            $csv_file = sys_get_temp_dir() . '/' . $filename;
            $fp = fopen($csv_file, 'w');
            
            // BOM untuk UTF-8
            fprintf($fp, chr(0xEF).chr(0xBB).chr(0xBF));
            
            // Header CSV
            fputcsv($fp, array('No', 'NIM', 'Nama Mahasiswa', 'Kelas', 'Jenis Surat', 'Untuk Siapa', 'Tipe Surat', 'Status', 'Tanggal Pengajuan', 'Keperluan'));
            
            // Data CSV
            $no = 1;
            foreach ($permintaan_list as $p) {
                $tipe_label = '-';
                if (isset($p->tipe_surat)) {
                    $tipe_label = $p->tipe_surat == 'soft_copy' ? 'Soft Copy' : 'Hard Copy';
                }
                
                fputcsv($fp, array(
                    $no++,
                    $p->nim,
                    $p->nama_mahasiswa,
                    $p->kelas ?: '-',
                    $p->jenis_surat,
                    isset($p->untuk_siapa) && $p->untuk_siapa ? $p->untuk_siapa : '-',
                    $tipe_label,
                    ucfirst($p->status),
                    date('d/m/Y H:i', strtotime($p->created_at)),
                    $p->keperluan
                ));
            }
            
            fclose($fp);
            
            // Download file
            header('Content-Type: text/csv; charset=utf-8');
            header('Content-Disposition: attachment; filename="' . $filename . '"');
            header('Content-Length: ' . filesize($csv_file));
            readfile($csv_file);
            
            // Hapus file setelah download
            unlink($csv_file);
            exit;
        }
    }
}

