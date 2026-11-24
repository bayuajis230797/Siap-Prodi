<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Honor UAS Controller
 * Mengelola honor UAS untuk dosen dan tendik
 */
class Honor_uas extends MY_Controller {

    public function __construct() {
        parent::__construct();
        $this->check_role(array('admin', 'sekprodi', 'kaprodi'));
        $this->load->model('Honor_uas_model');
        $this->load->model('Honor_setup_model');
        $this->load->model('Tahun_akademik_model');
        $this->load->model('Dosen_model');
        $this->load->model('Tendik_model');
    }

    /**
     * Index - Tampilkan rekap honor UAS
     */
    public function index() {
        $tahun_aktif = $this->Tahun_akademik_model->get_aktif();
        $id_tahun_akademik = $tahun_aktif ? $tahun_aktif->id : null;
        
        // Ambil setup honor
        $honor_soal = $this->Honor_setup_model->get_besaran('pembuatan_soal_uas', $id_tahun_akademik);
        $honor_koreksi = $this->Honor_setup_model->get_besaran('koreksi_uas', $id_tahun_akademik);
        $honor_pengawas = $this->Honor_setup_model->get_besaran('pengawas_uas', $id_tahun_akademik);
        
        // Ambil semua honor
        $all_honor = $this->Honor_uas_model->get_all($id_tahun_akademik);
        
        // Prepare summary
        $summary = $this->prepare_honor_summary($all_honor);
        $honor_all = $summary['data'];
        
        $data['title'] = 'Honor UAS';
        $data['page_title'] = 'Honor UAS';
        $data['breadcrumb'] = array(array('label' => 'Honor UAS', 'active' => true));
        $data['sidebar_menu'] = generate_sidebar_menu(get_user_role());
        $data['tahun_aktif'] = $tahun_aktif;
        $data['honor_soal'] = $honor_soal;
        $data['honor_koreksi'] = $honor_koreksi;
        $data['honor_pengawas'] = $honor_pengawas;
        $data['honor_all'] = $honor_all;
        $data['content'] = $this->load->view('honor_uas/index', $data, true);
        $this->load->view('layouts/main', $data);
    }

    /**
     * Generate honor UAS
     */
    public function generate() {
        $this->assert_not_kaprodi('Honor_uas');
        $tahun_aktif = $this->Tahun_akademik_model->get_aktif();
        
        if (!$tahun_aktif) {
            $this->session->set_flashdata('error', 'Tidak ada tahun akademik yang aktif.');
            redirect('Honor_uas');
        }
        
        $id_tahun_akademik = $tahun_aktif->id;
        $honor_generated = $this->Honor_uas_model->generate_honor($id_tahun_akademik);
        
        if ($honor_generated > 0) {
            $this->session->set_flashdata('success', 'Honor UAS berhasil di-generate untuk ' . $honor_generated . ' jadwal.');
        } else {
            $this->session->set_flashdata('warning', 'Tidak ada honor UAS yang di-generate. Pastikan sudah ada jadwal UAS dan berita acara.');
        }
        
        redirect('Honor_uas');
    }

    /**
     * Detail honor per dosen
     */
    public function detail_dosen($id_dosen) {
        $dosen = $this->Dosen_model->get_by_id($id_dosen);
        
        if (!$dosen) {
            $this->session->set_flashdata('error', 'Dosen tidak ditemukan.');
            redirect('Honor_uas');
        }
        
        $tahun_aktif = $this->Tahun_akademik_model->get_aktif();
        $id_tahun_akademik = $tahun_aktif ? $tahun_aktif->id : null;
        
        $detail = $this->Honor_uas_model->get_detail_per_dosen($id_dosen, $id_tahun_akademik);
        
        // Hitung total
        $total_pembuatan_soal = 0;
        $total_koreksi = 0;
        $total_pengawas = 0;
        
        foreach ($detail['pembuatan_soal'] as $item) {
            $total_pembuatan_soal += $item->total_honor;
        }
        
        foreach ($detail['koreksi'] as $item) {
            $total_koreksi += isset($item->total_honor_koreksi) ? $item->total_honor_koreksi : $item->total_honor;
        }
        
        foreach ($detail['pengawas'] as $item) {
            $total_pengawas += $item->total_honor;
        }
        
        $data['title'] = 'Detail Honor UAS - ' . $dosen->nama_dosen;
        $data['page_title'] = 'Detail Honor UAS';
        $data['breadcrumb'] = array(
            array('label' => 'Honor UAS', 'url' => site_url('Honor_uas')),
            array('label' => 'Detail', 'active' => true)
        );
        $data['sidebar_menu'] = generate_sidebar_menu(get_user_role());
        $data['dosen'] = $dosen;
        $data['tahun_aktif'] = $tahun_aktif;
        $data['detail'] = $detail;
        $data['total_pembuatan_soal'] = $total_pembuatan_soal;
        $data['total_koreksi'] = $total_koreksi;
        $data['total_pengawas'] = $total_pengawas;
        $data['content'] = $this->load->view('honor_uas/detail_dosen', $data, true);
        $this->load->view('layouts/main', $data);
    }

    /**
     * Detail honor per tendik
     */
    public function detail_tendik($id_tendik) {
        $tendik = $this->Tendik_model->get_by_id($id_tendik);
        
        if (!$tendik) {
            $this->session->set_flashdata('error', 'Tendik tidak ditemukan.');
            redirect('Honor_uas');
        }
        
        $tahun_aktif = $this->Tahun_akademik_model->get_aktif();
        $id_tahun_akademik = $tahun_aktif ? $tahun_aktif->id : null;
        
        $detail = $this->Honor_uas_model->get_detail_per_tendik($id_tendik, $id_tahun_akademik);
        
        // Hitung total
        $total_pengawas = 0;
        foreach ($detail['pengawas'] as $item) {
            $total_pengawas += $item->total_honor;
        }
        
        $data['title'] = 'Detail Honor UAS - ' . $tendik->nama_tendik;
        $data['page_title'] = 'Detail Honor UAS';
        $data['breadcrumb'] = array(
            array('label' => 'Honor UAS', 'url' => site_url('Honor_uas')),
            array('label' => 'Detail', 'active' => true)
        );
        $data['sidebar_menu'] = generate_sidebar_menu(get_user_role());
        $data['tendik'] = $tendik;
        $data['tahun_aktif'] = $tahun_aktif;
        $data['detail'] = $detail;
        $data['total_pengawas'] = $total_pengawas;
        $data['content'] = $this->load->view('honor_uas/detail_tendik', $data, true);
        $this->load->view('layouts/main', $data);
    }

    /**
     * Export rekap honor UAS ke Excel / CSV
     */
    public function export_excel() {
        $tahun_aktif = $this->Tahun_akademik_model->get_aktif();
        
        if (!$tahun_aktif) {
            $this->session->set_flashdata('error', 'Tidak ada tahun akademik yang aktif.');
            redirect('Honor_uas');
        }
        
        $id_tahun_akademik = $tahun_aktif->id;
        $all_honor = $this->Honor_uas_model->get_all($id_tahun_akademik);
        $summary = $this->prepare_honor_summary($all_honor);
        $honor_all = $summary['data'];
        $totals = $summary['totals'];
        
        if (empty($honor_all)) {
            $this->session->set_flashdata('warning', 'Tidak ada data honor UAS untuk di-export.');
            redirect('Honor_uas');
        }
        
        $use_phpspreadsheet = false;
        if (file_exists(APPPATH . '../vendor/autoload.php')) {
            require_once APPPATH . '../vendor/autoload.php';
            if (class_exists('PhpOffice\\PhpSpreadsheet\\Spreadsheet')) {
                $use_phpspreadsheet = true;
            }
        }
        
        $filename_base = 'Rekap_Honor_UAS_' . $tahun_aktif->tahun_akademik . '_' . ucfirst($tahun_aktif->semester) . '_' . date('YmdHis');
        
        if ($use_phpspreadsheet) {
            $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet();
            
            $sheet->setCellValue('A1', 'REKAP HONOR UAS');
            $sheet->mergeCells('A1:K1');
            $sheet->getStyle('A1')->applyFromArray([
                'font' => [
                    'bold' => true,
                    'size' => 16
                ],
                'alignment' => [
                    'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                    'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER
                ]
            ]);
            
            $sheet->setCellValue('A2', 'Tahun Akademik: ' . $tahun_aktif->tahun_akademik . ' ' . ucfirst($tahun_aktif->semester));
            $sheet->mergeCells('A2:K2');
            $sheet->getStyle('A2')->applyFromArray([
                'font' => [
                    'italic' => true,
                    'size' => 12
                ],
                'alignment' => [
                    'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER
                ]
            ]);
            
            $sheet->setCellValue('A3', 'Tanggal Export: ' . date('d F Y H:i:s'));
            $sheet->mergeCells('A3:K3');
            $sheet->getStyle('A3')->applyFromArray([
                'alignment' => [
                    'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER
                ]
            ]);
            
            $sheet->setCellValue('A4', 'Catatan: Pembuatan Soal dan Koreksi dihitung per kelas (bukan per sesi/jadwal)');
            $sheet->mergeCells('A4:K4');
            $sheet->getStyle('A4')->applyFromArray([
                'font' => [
                    'italic' => true,
                    'size' => 10,
                    'color' => ['rgb' => '666666']
                ],
                'alignment' => [
                    'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER
                ]
            ]);
            
            $headerRow = 5;
            $headers = [
                'A' => 'No',
                'B' => 'NIDN / NIPY',
                'C' => 'Nama',
                'D' => 'Tipe',
                'E' => 'Jml Pembuatan Soal (Kelas)',
                'F' => 'Subtotal Pembuatan Soal',
                'G' => 'Jml Koreksi (Kelas)',
                'H' => 'Subtotal Koreksi',
                'I' => 'Jml Pengawas (Sesi)',
                'J' => 'Subtotal Pengawas',
                'K' => 'Total Honor'
            ];
            
            foreach ($headers as $col => $label) {
                $sheet->setCellValue($col . $headerRow, $label);
            }
            
            $sheet->getStyle('A' . $headerRow . ':K' . $headerRow)->applyFromArray([
                'font' => [
                    'bold' => true,
                    'color' => ['rgb' => 'FFFFFF']
                ],
                'alignment' => [
                    'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                    'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
                    'wrapText' => true
                ],
                'fill' => [
                    'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                    'startColor' => ['rgb' => '1F4E78']
                ],
                'borders' => [
                    'allBorders' => [
                        'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN
                    ]
                ]
            ]);
            
            $row = $headerRow + 1;
            $no = 1;
            foreach ($honor_all as $item) {
                $honor_soal_display = 'Rp. ' . number_format($item['honor_soal_total'], 0, ',', '.');
                $honor_koreksi_display = 'Rp. ' . number_format($item['honor_koreksi_total'], 0, ',', '.');
                $honor_pengawas_display = 'Rp. ' . number_format($item['honor_pengawas_total'], 0, ',', '.');
                $honor_total_display = 'Rp. ' . number_format($item['total_honor'], 0, ',', '.');

                $sheet->setCellValue('A' . $row, $no++);
                $sheet->setCellValue('B' . $row, $item['tipe'] == 'dosen' ? ($item['nidn'] ?? '-') : ($item['nipy'] ?? '-'));
                $sheet->setCellValue('C' . $row, $item['tipe'] == 'dosen' ? ($item['nama_dosen'] ?? '') : ($item['nama_tendik'] ?? ''));
                $sheet->setCellValue('D' . $row, strtoupper($item['tipe']));
                $sheet->setCellValue('E' . $row, $item['jumlah_soal']);
                $sheet->setCellValueExplicit('F' . $row, $honor_soal_display, \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
                $sheet->setCellValue('G' . $row, $item['jumlah_koreksi']);
                $sheet->setCellValueExplicit('H' . $row, $honor_koreksi_display, \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
                $sheet->setCellValue('I' . $row, $item['jumlah_pengawas']);
                $sheet->setCellValueExplicit('J' . $row, $honor_pengawas_display, \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
                $sheet->setCellValueExplicit('K' . $row, $honor_total_display, \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
                
                $sheet->getStyle('A' . $row . ':K' . $row)->applyFromArray([
                    'alignment' => [
                        'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                        'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER
                    ],
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                            'color' => ['rgb' => 'DDDDDD']
                        ]
                    ]
                ]);
                
                $sheet->getStyle('C' . $row)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT);
                
                if ($row % 2 == 0) {
                    $sheet->getStyle('A' . $row . ':K' . $row)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setRGB('F9F9F9');
                }
                
                $row++;
            }
            
            $sheet->setCellValue('A' . $row, 'TOTAL');
            $sheet->mergeCells('A' . $row . ':D' . $row);
            $sheet->setCellValue('E' . $row, $totals['jumlah_soal']);
            $sheet->setCellValueExplicit('F' . $row, 'Rp. ' . number_format($totals['honor_soal_total'], 0, ',', '.'), \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
            $sheet->setCellValue('G' . $row, $totals['jumlah_koreksi']);
            $sheet->setCellValueExplicit('H' . $row, 'Rp. ' . number_format($totals['honor_koreksi_total'], 0, ',', '.'), \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
            $sheet->setCellValue('I' . $row, $totals['jumlah_pengawas']);
            $sheet->setCellValueExplicit('J' . $row, 'Rp. ' . number_format($totals['honor_pengawas_total'], 0, ',', '.'), \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
            $sheet->setCellValueExplicit('K' . $row, 'Rp. ' . number_format($totals['grand_total'], 0, ',', '.'), \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
            
            $sheet->getStyle('A' . $row . ':K' . $row)->applyFromArray([
                'font' => ['bold' => true],
                'fill' => [
                    'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                    'startColor' => ['rgb' => 'D9E1F2']
                ],
                'alignment' => [
                    'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                    'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER
                ],
                'borders' => [
                    'allBorders' => [
                        'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN
                    ]
                ]
            ]);
            $sheet->getStyle('C' . ($headerRow + 1) . ':C' . $row)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT);
            
            $columnsWidth = [
                'A' => 6,
                'B' => 18,
                'C' => 35,
                'D' => 12,
                'E' => 20,
                'F' => 25,
                'G' => 18,
                'H' => 22,
                'I' => 18,
                'J' => 25,
                'K' => 22
            ];
            foreach ($columnsWidth as $col => $width) {
                $sheet->getColumnDimension($col)->setWidth($width);
            }
            
            for ($i = $headerRow; $i <= $row; $i++) {
                $sheet->getRowDimension($i)->setRowHeight(22);
            }
            
            $sheet->freezePane('A' . ($headerRow + 1));
            $sheet->getPageSetup()->setOrientation(\PhpOffice\PhpSpreadsheet\Worksheet\PageSetup::ORIENTATION_LANDSCAPE);
            $sheet->getPageSetup()->setHorizontalCentered(true);
            $sheet->getPageMargins()->setTop(0.5);
            $sheet->getPageMargins()->setBottom(0.5);
            $sheet->getPageMargins()->setLeft(0.4);
            $sheet->getPageMargins()->setRight(0.4);
            
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
            header('Cache-Control: max-age=0');
            
            $output = fopen('php://output', 'w');
            
            // BOM untuk Excel UTF-8
            fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));
            
            // Header
            fputcsv($output, array('No', 'NIDN/NIPY', 'Nama', 'Tipe', 'Jml Pembuatan Soal (Kelas)', 'Subtotal Pembuatan Soal', 'Jml Koreksi (Kelas)', 'Subtotal Koreksi', 'Jml Pengawas (Sesi)', 'Subtotal Pengawas', 'Total Honor'));
            
            // Data
            $no = 1;
            foreach ($honor_all as $item) {
                fputcsv($output, array(
                    $no++,
                    $item['tipe'] == 'dosen' ? ($item['nidn'] ?? '-') : ($item['nipy'] ?? '-'),
                    $item['tipe'] == 'dosen' ? ($item['nama_dosen'] ?? '') : ($item['nama_tendik'] ?? ''),
                    strtoupper($item['tipe']),
                    $item['jumlah_soal'],
                    'Rp. ' . number_format($item['honor_soal_total'], 0, ',', '.'),
                    $item['jumlah_koreksi'],
                    'Rp. ' . number_format($item['honor_koreksi_total'], 0, ',', '.'),
                    $item['jumlah_pengawas'],
                    'Rp. ' . number_format($item['honor_pengawas_total'], 0, ',', '.'),
                    'Rp. ' . number_format($item['total_honor'], 0, ',', '.')
                ));
            }
            
            fclose($output);
            exit;
        }
    }

    /**
     * Prepare honor summary dari raw data
     */
    private function prepare_honor_summary($all_honor) {
        $honor_map = array();
        $soal_tracked = array(); // Track honor soal per kelas
        $koreksi_tracked = array(); // Track honor koreksi per kelas
        
        foreach ($all_honor as $honor) {
            $key = '';
            $tipe = '';
            
            if ($honor->id_dosen) {
                $key = 'dosen_' . $honor->id_dosen;
                $tipe = 'dosen';
                $nidn = $honor->nidn ?? '-';
                $nama = $honor->nama_dosen ?? '';
            } else if ($honor->id_tendik) {
                $key = 'tendik_' . $honor->id_tendik;
                $tipe = 'tendik';
                $nipy = $honor->nipy ?? '-';
                $nama = $honor->nama_tendik ?? '';
            } else {
                continue;
            }
            
            if (!isset($honor_map[$key])) {
                $honor_map[$key] = array(
                    'tipe' => $tipe,
                    'id_dosen' => $honor->id_dosen,
                    'id_tendik' => $honor->id_tendik,
                    'nidn' => $tipe == 'dosen' ? $nidn : null,
                    'nipy' => $tipe == 'tendik' ? $nipy : null,
                    'nama_dosen' => $tipe == 'dosen' ? $nama : null,
                    'nama_tendik' => $tipe == 'tendik' ? $nama : null,
                    'jumlah_soal' => 0,
                    'honor_soal_total' => 0,
                    'jumlah_koreksi' => 0,
                    'honor_koreksi_total' => 0,
                    'jumlah_pengawas' => 0,
                    'honor_pengawas_total' => 0,
                    'total_honor' => 0
                );
            }
            
            if ($honor->jenis_honor == 'pembuatan_soal') {
                // Track per kelas + kode_mk untuk menghindari duplikasi
                $track_key = $honor->id_dosen . '_' . $honor->kelas . '_' . $honor->kode_mk;
                if (!isset($soal_tracked[$track_key])) {
                    $honor_map[$key]['jumlah_soal']++;
                    $soal_tracked[$track_key] = true;
                }
                $honor_map[$key]['honor_soal_total'] += $honor->total_honor;
            } else if ($honor->jenis_honor == 'koreksi') {
                // Jumlah koreksi adalah total jumlah mahasiswa (jumlah_item), bukan jumlah kelas
                // Track per kelas + kode_mk untuk menghindari duplikasi jumlah_item
                $track_key = $honor->id_dosen . '_' . $honor->kelas . '_' . $honor->kode_mk;
                if (!isset($koreksi_tracked[$track_key])) {
                    // Tambahkan jumlah mahasiswa dari jumlah_item, bukan increment 1
                    $honor_map[$key]['jumlah_koreksi'] += ($honor->jumlah_item ?? 0);
                    $koreksi_tracked[$track_key] = true;
                }
                $honor_map[$key]['honor_koreksi_total'] += $honor->total_honor;
            } else if ($honor->jenis_honor == 'pengawas') {
                $honor_map[$key]['jumlah_pengawas']++;
                $honor_map[$key]['honor_pengawas_total'] += $honor->total_honor;
            }
            
            $honor_map[$key]['total_honor'] += $honor->total_honor;
        }
        
        // Convert to array and sort
        $honor_all = array_values($honor_map);
        usort($honor_all, function($a, $b) {
            return strcmp($a['tipe'] == 'dosen' ? $a['nama_dosen'] : $a['nama_tendik'], 
                         $b['tipe'] == 'dosen' ? $b['nama_dosen'] : $b['nama_tendik']);
        });
        
        // Calculate totals
        $totals = array(
            'jumlah_soal' => 0,
            'honor_soal_total' => 0,
            'jumlah_koreksi' => 0,
            'honor_koreksi_total' => 0,
            'jumlah_pengawas' => 0,
            'honor_pengawas_total' => 0,
            'grand_total' => 0
        );
        
        foreach ($honor_all as $item) {
            $totals['jumlah_soal'] += $item['jumlah_soal'];
            $totals['honor_soal_total'] += $item['honor_soal_total'];
            $totals['jumlah_koreksi'] += $item['jumlah_koreksi'];
            $totals['honor_koreksi_total'] += $item['honor_koreksi_total'];
            $totals['jumlah_pengawas'] += $item['jumlah_pengawas'];
            $totals['honor_pengawas_total'] += $item['honor_pengawas_total'];
            $totals['grand_total'] += $item['total_honor'];
        }
        
        return array(
            'data' => $honor_all,
            'totals' => $totals
        );
    }
}



