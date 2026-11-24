<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Mahasiswa Sekprodi Controller
 * Mengelola daftar mahasiswa untuk role sekprodi dengan fitur download per kelas
 */
class Mahasiswa_sekprodi extends MY_Controller {

    public function __construct() {
        parent::__construct();
        $this->check_role(array('sekprodi', 'kaprodi'));
        $this->load->model('Mahasiswa_model');
        $this->load->model('Kurikulum_model');
        $this->load->model('Tahun_akademik_model');
    }

    /**
     * List semua mahasiswa dengan pagination dan filter
     */
    public function index() {
        $this->load->library('pagination');
        
        // Ambil parameter filter
        $filter_kelas = $this->input->get('kelas');
        $filter_status = $this->input->get('status') ? $this->input->get('status') : 'aktif';
        $filter_angkatan = $this->input->get('angkatan');
        $per_page = $this->input->get('per_page') ? (int)$this->input->get('per_page') : 25;
        $per_page = in_array($per_page, [10, 25, 50, 100]) ? $per_page : 25;
        
        // Query dengan filter
        $this->db->select('m.*, k.nama_kurikulum, k.tahun_berlaku');
        $this->db->from('mahasiswa m');
        $this->db->join('kurikulum k', 'k.id = m.id_kurikulum', 'left');
        
        if ($filter_kelas) {
            $this->db->where('m.kelas', $filter_kelas);
        }
        if ($filter_status) {
            $this->db->where('m.status', $filter_status);
        }
        if ($filter_angkatan) {
            $this->db->where('m.angkatan', $filter_angkatan);
        }
        
        $this->db->order_by('m.angkatan', 'DESC');
        $this->db->order_by('m.kelas', 'ASC');
        $this->db->order_by('m.nim', 'ASC');
        
        // Hitung total rows untuk pagination
        $total_rows = $this->db->count_all_results('', false);
        
        // Reset query builder setelah count
        $this->db->reset_query();
        
        // Pagination config
        $config['base_url'] = site_url('Mahasiswa_sekprodi/index');
        $config['total_rows'] = $total_rows;
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
        
        // Tambahkan query string ke pagination
        $query_string = http_build_query(array_filter([
            'kelas' => $filter_kelas,
            'status' => $filter_status,
            'angkatan' => $filter_angkatan,
            'per_page' => $per_page != 25 ? $per_page : null
        ]));
        if ($query_string) {
            $config['suffix'] = '?' . $query_string;
            $config['first_url'] = site_url('Mahasiswa_sekprodi/index?' . $query_string);
        }
        
        $this->pagination->initialize($config);
        
        $page = ($this->uri->segment(3)) ? $this->uri->segment(3) : 1;
        $offset = ($page - 1) * $config['per_page'];
        
        // Query lagi dengan limit dan offset (query builder sudah di-reset)
        $this->db->select('m.*, k.nama_kurikulum, k.tahun_berlaku');
        $this->db->from('mahasiswa m');
        $this->db->join('kurikulum k', 'k.id = m.id_kurikulum', 'left');
        
        if ($filter_kelas) {
            $this->db->where('m.kelas', $filter_kelas);
        }
        if ($filter_status) {
            $this->db->where('m.status', $filter_status);
        }
        if ($filter_angkatan) {
            $this->db->where('m.angkatan', $filter_angkatan);
        }
        
        $this->db->order_by('m.angkatan', 'DESC');
        $this->db->order_by('m.kelas', 'ASC');
        $this->db->order_by('m.nim', 'ASC');
        $this->db->limit($config['per_page'], $offset);
        
        $mahasiswa = $this->db->get()->result();
        
        // Reset query builder lagi sebelum query berikutnya
        $this->db->reset_query();
        
        // Ambil daftar kelas untuk dropdown filter dan download
        $this->db->distinct();
        $this->db->select('kelas');
        $this->db->from('mahasiswa');
        if ($filter_status) {
            $this->db->where('status', $filter_status);
        } else {
            $this->db->where('status', 'aktif');
        }
        $this->db->where('kelas IS NOT NULL');
        $this->db->where('kelas !=', '');
        $this->db->order_by('kelas', 'ASC');
        $daftar_kelas = $this->db->get()->result();
        
        // Reset query builder sebelum query angkatan
        $this->db->reset_query();
        
        // Ambil daftar angkatan
        $this->db->distinct();
        $this->db->select('angkatan');
        $this->db->from('mahasiswa');
        if ($filter_status) {
            $this->db->where('status', $filter_status);
        } else {
            $this->db->where('status', 'aktif');
        }
        $this->db->order_by('angkatan', 'DESC');
        $daftar_angkatan = $this->db->get()->result();
        
        $data = array(
            'title' => 'Daftar Mahasiswa',
            'page_title' => 'Daftar Mahasiswa',
            'breadcrumb' => array(
                array('label' => 'Daftar Mahasiswa', 'active' => true)
            ),
            'sidebar_menu' => generate_sidebar_menu(get_user_role()),
            'mahasiswa' => $mahasiswa,
            'pagination' => $this->pagination->create_links(),
            'total_rows' => $total_rows,
            'per_page' => $per_page,
            'daftar_kelas' => $daftar_kelas,
            'daftar_angkatan' => $daftar_angkatan,
            'filter_kelas' => $filter_kelas,
            'filter_status' => $filter_status,
            'filter_angkatan' => $filter_angkatan
        );
        
        $data['content'] = $this->load->view('mahasiswa_sekprodi/index', $data, true);
        $this->load->view('layouts/main', $data);
    }

    /**
     * Download daftar mahasiswa per kelas ke Excel
     */
    public function download_per_kelas() {
        $kelas = $this->input->get('kelas');
        
        if (empty($kelas)) {
            $this->session->set_flashdata('error', 'Kelas harus dipilih.');
            redirect('Mahasiswa_sekprodi');
        }
        
        // Ambil data mahasiswa per kelas
        $mahasiswa = $this->Mahasiswa_model->get_by_kelas($kelas, null);
        
        if (empty($mahasiswa)) {
            $this->session->set_flashdata('error', 'Tidak ada data mahasiswa untuk kelas ' . $kelas . '.');
            redirect('Mahasiswa_sekprodi');
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

