<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Honor PKL Controller
 * Mengelola honor PKL untuk pembimbing dan penguji
 */
class Honor_pkl extends MY_Controller {

    public function __construct() {
        parent::__construct();
        $this->check_role(array('admin', 'sekprodi', 'kaprodi'));
        $this->load->model('Honor_pkl_model');
        $this->load->model('Honor_setup_model');
        $this->load->model('Tahun_akademik_model');
        $this->load->model('Dosen_model');
    }

    /**
     * Index - Tampilkan rekap honor PKL
     */
    public function index() {
        $tahun_aktif = $this->Tahun_akademik_model->get_aktif();
        $id_tahun_akademik = $tahun_aktif ? $tahun_aktif->id : null;
        
        // Ambil setup honor
        $honor_pembimbing = $this->Honor_setup_model->get_besaran('pembimbing_pkl', $id_tahun_akademik);
        $honor_penguji = $this->Honor_setup_model->get_besaran('penguji_pkl', $id_tahun_akademik);
        
        // Ambil rekap honor otomatis (tanpa generate)
        $rekap_dosen = $this->Honor_pkl_model->get_rekap_by_dosen_auto($id_tahun_akademik);
        
        // Hitung total
        $total_pembimbing = 0;
        $total_penguji = 0;
        $total_all = 0;
        
        foreach ($rekap_dosen as $rekap) {
            $total_pembimbing += $rekap->total_honor_pembimbing;
            $total_penguji += $rekap->total_honor_penguji;
            $total_all += $rekap->total_honor_all;
        }
        
        $data['title'] = 'Honor PKL';
        $data['page_title'] = 'Honor PKL';
        $data['breadcrumb'] = array(array('label' => 'Honor PKL', 'active' => true));
        $data['sidebar_menu'] = generate_sidebar_menu(get_user_role());
        $data['tahun_aktif'] = $tahun_aktif;
        $data['honor_pembimbing'] = $honor_pembimbing;
        $data['honor_penguji'] = $honor_penguji;
        $data['rekap_dosen'] = $rekap_dosen;
        $data['total_pembimbing'] = $total_pembimbing;
        $data['total_penguji'] = $total_penguji;
        $data['total_all'] = $total_all;
        $data['content'] = $this->load->view('honor_pkl/index', $data, true);
        $this->load->view('layouts/main', $data);
    }

    /**
     * Generate honor PKL
     */
    public function generate() {
        $this->assert_not_kaprodi('Honor_pkl');
        $tahun_aktif = $this->Tahun_akademik_model->get_aktif();
        
        if (!$tahun_aktif) {
            $this->session->set_flashdata('error', 'Tidak ada tahun akademik yang aktif.');
            redirect('Honor_pkl');
        }
        
        $id_tahun_akademik = $tahun_aktif->id;
        $honor_generated = $this->Honor_pkl_model->generate_honor($id_tahun_akademik);
        
        if ($honor_generated > 0) {
            $this->session->set_flashdata('success', 'Honor PKL berhasil di-generate untuk ' . $honor_generated . ' dosen.');
        } else {
            $this->session->set_flashdata('warning', 'Tidak ada honor PKL yang di-generate. Pastikan sudah ada pembimbing dan penguji PKL.');
        }
        
        redirect('Honor_pkl');
    }

    /**
     * Export rekap honor PKL ke Excel / CSV
     */
    public function export_excel() {
        $tahun_aktif = $this->Tahun_akademik_model->get_aktif();
        
        if (!$tahun_aktif) {
            $this->session->set_flashdata('error', 'Tidak ada tahun akademik yang aktif.');
            redirect('Honor_pkl');
        }
        
        $id_tahun_akademik = $tahun_aktif->id;
        $rekap_dosen = $this->Honor_pkl_model->get_rekap_by_dosen_auto($id_tahun_akademik);
        
        if (empty($rekap_dosen)) {
            $this->session->set_flashdata('warning', 'Tidak ada data honor PKL untuk di-export.');
            redirect('Honor_pkl');
        }
        
        // Hitung total
        $total_pembimbing = 0;
        $total_penguji = 0;
        $total_all = 0;
        
        foreach ($rekap_dosen as $rekap) {
            $total_pembimbing += $rekap->total_honor_pembimbing;
            $total_penguji += $rekap->total_honor_penguji;
            $total_all += $rekap->total_honor_all;
        }
        
        $use_phpspreadsheet = false;
        if (file_exists(APPPATH . '../vendor/autoload.php')) {
            require_once APPPATH . '../vendor/autoload.php';
            if (class_exists('PhpOffice\\PhpSpreadsheet\\Spreadsheet')) {
                $use_phpspreadsheet = true;
            }
        }
        
        $filename_base = 'Rekap_Honor_PKL_' . $tahun_aktif->tahun_akademik . '_' . ucfirst($tahun_aktif->semester) . '_' . date('YmdHis');
        
        if ($use_phpspreadsheet) {
            $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet();
            
            $sheet->setCellValue('A1', 'REKAP HONOR PKL');
            $sheet->mergeCells('A1:F1');
            $sheet->getStyle('A1')->applyFromArray([
                'font' => ['bold' => true, 'size' => 16],
                'alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER]
            ]);
            
            $sheet->setCellValue('A2', 'Tahun Akademik: ' . $tahun_aktif->tahun_akademik . ' ' . ucfirst($tahun_aktif->semester));
            $sheet->mergeCells('A2:F2');
            $sheet->getStyle('A2')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
            
            // Header
            $headers = array('No', 'NIDN', 'Nama Dosen', 'Jumlah Bimbingan', 'Honor Pembimbing', 'Jumlah Ujian', 'Honor Penguji', 'Total Honor');
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
            foreach ($rekap_dosen as $rekap) {
                $sheet->setCellValue('A' . $row, $no++);
                $sheet->setCellValue('B' . $row, $rekap->nidn);
                $sheet->setCellValue('C' . $row, $rekap->nama_dosen);
                $sheet->setCellValue('D' . $row, $rekap->jumlah_mahasiswa_bimbingan ?: 0);
                $sheet->setCellValue('E' . $row, $rekap->total_honor_pembimbing ? number_format($rekap->total_honor_pembimbing, 2) : 0);
                $sheet->setCellValue('F' . $row, $rekap->jumlah_ujian ?: 0);
                $sheet->setCellValue('G' . $row, $rekap->total_honor_penguji ? number_format($rekap->total_honor_penguji, 2) : 0);
                $sheet->setCellValue('H' . $row, $rekap->total_honor_all ? number_format($rekap->total_honor_all, 2) : 0);
                $row++;
            }
            
            // Total
            $sheet->setCellValue('C' . $row, 'TOTAL');
            $sheet->getStyle('C' . $row)->getFont()->setBold(true);
            $sheet->setCellValue('E' . $row, number_format($total_pembimbing, 2));
            $sheet->setCellValue('G' . $row, number_format($total_penguji, 2));
            $sheet->setCellValue('H' . $row, number_format($total_all, 2));
            $sheet->getStyle('E' . $row . ':H' . $row)->getFont()->setBold(true);
            
            // Auto width
            foreach (range('A', 'H') as $col) {
                $sheet->getColumnDimension($col)->setAutoSize(true);
            }
            
            // Borders
            $styleArray = array(
                'borders' => array(
                    'allBorders' => array(
                        'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                    ),
                ),
            );
            $sheet->getStyle('A4:H' . ($row))->applyFromArray($styleArray);
            
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
            header('Content-Type: text/csv; charset=utf-8');
            header('Content-Disposition: attachment;filename="' . $filename . '"');
            
            $output = fopen('php://output', 'w');
            fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF)); // UTF-8 BOM
            
            // Header
            fputcsv($output, array('REKAP HONOR PKL'));
            fputcsv($output, array('Tahun Akademik: ' . $tahun_aktif->tahun_akademik . ' ' . ucfirst($tahun_aktif->semester)));
            fputcsv($output, array(''));
            fputcsv($output, array('No', 'NIDN', 'Nama Dosen', 'Jumlah Bimbingan', 'Honor Pembimbing', 'Jumlah Ujian', 'Honor Penguji', 'Total Honor'));
            
            // Data
            $no = 1;
            foreach ($rekap_dosen as $rekap) {
                fputcsv($output, array(
                    $no++,
                    $rekap->nidn,
                    $rekap->nama_dosen,
                    $rekap->jumlah_mahasiswa_bimbingan ?: 0,
                    $rekap->total_honor_pembimbing ? number_format($rekap->total_honor_pembimbing, 2) : 0,
                    $rekap->jumlah_ujian ?: 0,
                    $rekap->total_honor_penguji ? number_format($rekap->total_honor_penguji, 2) : 0,
                    $rekap->total_honor_all ? number_format($rekap->total_honor_all, 2) : 0
                ));
            }
            
            // Total
            fputcsv($output, array('', '', 'TOTAL', '', number_format($total_pembimbing, 2), '', number_format($total_penguji, 2), number_format($total_all, 2)));
            
            fclose($output);
            exit;
        }
    }
}




