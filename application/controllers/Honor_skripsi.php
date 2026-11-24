<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Honor_skripsi extends MY_Controller {

    public function __construct() {
        parent::__construct();
        $this->check_role(array('admin', 'sekprodi', 'kaprodi'));
        $this->load->model('Honor_skripsi_model');
        $this->load->model('Honor_setup_model');
        $this->load->model('Tahun_akademik_model');
        $this->load->model('Skripsi_model');
    }

    public function index() {
        $tahun_aktif = $this->Tahun_akademik_model->get_aktif();
        $id_tahun_akademik = $tahun_aktif ? $tahun_aktif->id : null;
        
        // Ambil setup honor
        $honor_p1 = $this->Honor_setup_model->get_besaran('pembimbing_1_skripsi', $id_tahun_akademik);
        $honor_p2 = $this->Honor_setup_model->get_besaran('pembimbing_2_skripsi', $id_tahun_akademik);
        $honor_ketua = $this->Honor_setup_model->get_besaran('ketua_penguji_skripsi', $id_tahun_akademik);
        $honor_anggota_1 = $this->Honor_setup_model->get_besaran('anggota_penguji_1_skripsi', $id_tahun_akademik);
        $honor_anggota_2 = $this->Honor_setup_model->get_besaran('anggota_penguji_2_skripsi', $id_tahun_akademik);
        
        // Ambil data honor pembimbing
        $honor_pembimbing = $this->Honor_skripsi_model->get_honor_pembimbing($id_tahun_akademik);
        
        // Hitung total honor per dosen pembimbing
        foreach ($honor_pembimbing as $item) {
            $item->honor_p1 = $item->jumlah_pembimbing_1 * $honor_p1;
            $item->honor_p2 = $item->jumlah_pembimbing_2 * $honor_p2;
            $item->total_honor_pembimbing = $item->honor_p1 + $item->honor_p2;
        }
        
        // Ambil data honor penguji
        $honor_penguji = $this->Honor_skripsi_model->get_honor_penguji($id_tahun_akademik);
        
        // Hitung total honor per dosen penguji
        foreach ($honor_penguji as $item) {
            $item->honor_ketua = $item->jumlah_ketua * $honor_ketua;
            $item->honor_anggota_1 = $item->jumlah_anggota_1 * $honor_anggota_1;
            $item->honor_anggota_2 = $item->jumlah_anggota_2 * $honor_anggota_2;
            $item->total_honor_penguji = $item->honor_ketua + $item->honor_anggota_1 + $item->honor_anggota_2;
        }
        
        // Gabungkan data pembimbing dan penguji per dosen
        $honor_all = array();
        $honor_map = array();
        
        // Masukkan honor pembimbing
        foreach ($honor_pembimbing as $item) {
            $honor_map[$item->id_dosen] = array(
                'id_dosen' => $item->id_dosen,
                'nidn' => $item->nidn,
                'nama_dosen' => $item->nama_dosen,
                'jumlah_pembimbing_1' => $item->jumlah_pembimbing_1,
                'jumlah_pembimbing_2' => $item->jumlah_pembimbing_2,
                'honor_p1' => $item->honor_p1,
                'honor_p2' => $item->honor_p2,
                'total_honor_pembimbing' => $item->total_honor_pembimbing,
                'jumlah_ketua' => 0,
                'jumlah_anggota_1' => 0,
                'jumlah_anggota_2' => 0,
                'honor_ketua' => 0,
                'honor_anggota_1' => 0,
                'honor_anggota_2' => 0,
                'total_honor_penguji' => 0
            );
        }
        
        // Masukkan honor penguji
        foreach ($honor_penguji as $item) {
            if (isset($honor_map[$item->id_dosen])) {
                $honor_map[$item->id_dosen]['jumlah_ketua'] = $item->jumlah_ketua;
                $honor_map[$item->id_dosen]['jumlah_anggota_1'] = $item->jumlah_anggota_1;
                $honor_map[$item->id_dosen]['jumlah_anggota_2'] = $item->jumlah_anggota_2;
                $honor_map[$item->id_dosen]['honor_ketua'] = $item->honor_ketua;
                $honor_map[$item->id_dosen]['honor_anggota_1'] = $item->honor_anggota_1;
                $honor_map[$item->id_dosen]['honor_anggota_2'] = $item->honor_anggota_2;
                $honor_map[$item->id_dosen]['total_honor_penguji'] = $item->total_honor_penguji;
            } else {
                $honor_map[$item->id_dosen] = array(
                    'id_dosen' => $item->id_dosen,
                    'nidn' => $item->nidn,
                    'nama_dosen' => $item->nama_dosen,
                    'jumlah_pembimbing_1' => 0,
                    'jumlah_pembimbing_2' => 0,
                    'honor_p1' => 0,
                    'honor_p2' => 0,
                    'total_honor_pembimbing' => 0,
                    'jumlah_ketua' => $item->jumlah_ketua,
                    'jumlah_anggota_1' => $item->jumlah_anggota_1,
                    'jumlah_anggota_2' => $item->jumlah_anggota_2,
                    'honor_ketua' => $item->honor_ketua,
                    'honor_anggota_1' => $item->honor_anggota_1,
                    'honor_anggota_2' => $item->honor_anggota_2,
                    'total_honor_penguji' => $item->total_honor_penguji
                );
            }
        }
        
        // Hitung total honor keseluruhan per dosen
        foreach ($honor_map as $key => $item) {
            $item['total_honor'] = $item['total_honor_pembimbing'] + $item['total_honor_penguji'];
            $honor_all[] = $item;
        }
        
        // Urutkan berdasarkan nama dosen
        usort($honor_all, function($a, $b) {
            $nama_a = is_array($a) ? $a['nama_dosen'] : $a->nama_dosen;
            $nama_b = is_array($b) ? $b['nama_dosen'] : $b->nama_dosen;
            return strcmp($nama_a, $nama_b);
        });
        
        $data = array(
            'title' => 'Honor Skripsi',
            'page_title' => 'Honor Skripsi',
            'breadcrumb' => array(
                array('label' => 'Honor', 'url' => site_url('Honor')),
                array('label' => 'Skripsi', 'active' => true)
            ),
            'sidebar_menu' => generate_sidebar_menu(get_user_role()),
            'tahun_aktif' => $tahun_aktif,
            'honor_all' => $honor_all,
            'honor_p1' => $honor_p1,
            'honor_p2' => $honor_p2,
            'honor_ketua' => $honor_ketua,
            'honor_anggota_1' => $honor_anggota_1,
            'honor_anggota_2' => $honor_anggota_2
        );
        
        $data['content'] = $this->load->view('honor/skripsi', $data, true);
        $this->load->view('layouts/main', $data);
    }

    /**
     * Export Honor Skripsi ke Excel
     */
    public function export_excel() {
        $tahun_aktif = $this->Tahun_akademik_model->get_aktif();
        if (!$tahun_aktif) {
            $this->session->set_flashdata('error', 'Tahun akademik aktif tidak ditemukan.');
            redirect('Honor_skripsi');
        }

        // Cek apakah PhpSpreadsheet tersedia
        $use_phpspreadsheet = false;
        if (file_exists(APPPATH . '../vendor/autoload.php')) {
            require_once APPPATH . '../vendor/autoload.php';
            if (class_exists('PhpOffice\PhpSpreadsheet\Spreadsheet')) {
                $use_phpspreadsheet = true;
            }
        }

        if (!$use_phpspreadsheet) {
            // Fallback: Export CSV
            $this->export_csv();
            return;
        }

        $id_tahun_akademik = $tahun_aktif->id;
        
        // Ambil setup honor
        $honor_p1 = $this->Honor_setup_model->get_besaran('pembimbing_1_skripsi', $id_tahun_akademik);
        $honor_p2 = $this->Honor_setup_model->get_besaran('pembimbing_2_skripsi', $id_tahun_akademik);
        $honor_ketua = $this->Honor_setup_model->get_besaran('ketua_penguji_skripsi', $id_tahun_akademik);
        $honor_anggota_1 = $this->Honor_setup_model->get_besaran('anggota_penguji_1_skripsi', $id_tahun_akademik);
        $honor_anggota_2 = $this->Honor_setup_model->get_besaran('anggota_penguji_2_skripsi', $id_tahun_akademik);
        
        // Ambil data honor pembimbing
        $honor_pembimbing = $this->Honor_skripsi_model->get_honor_pembimbing($id_tahun_akademik);
        
        // Hitung total honor per dosen pembimbing
        foreach ($honor_pembimbing as $item) {
            $item->honor_p1 = $item->jumlah_pembimbing_1 * $honor_p1;
            $item->honor_p2 = $item->jumlah_pembimbing_2 * $honor_p2;
            $item->total_honor_pembimbing = $item->honor_p1 + $item->honor_p2;
        }
        
        // Ambil data honor penguji
        $honor_penguji = $this->Honor_skripsi_model->get_honor_penguji($id_tahun_akademik);
        
        // Hitung total honor per dosen penguji
        foreach ($honor_penguji as $item) {
            $item->honor_ketua = $item->jumlah_ketua * $honor_ketua;
            $item->honor_anggota_1 = $item->jumlah_anggota_1 * $honor_anggota_1;
            $item->honor_anggota_2 = $item->jumlah_anggota_2 * $honor_anggota_2;
            $item->total_honor_penguji = $item->honor_ketua + $item->honor_anggota_1 + $item->honor_anggota_2;
        }
        
        // Gabungkan data pembimbing dan penguji per dosen
        $honor_all = array();
        $honor_map = array();
        
        // Masukkan honor pembimbing
        foreach ($honor_pembimbing as $item) {
            $honor_map[$item->id_dosen] = array(
                'id_dosen' => $item->id_dosen,
                'nidn' => $item->nidn,
                'nama_dosen' => $item->nama_dosen,
                'jumlah_pembimbing_1' => $item->jumlah_pembimbing_1,
                'jumlah_pembimbing_2' => $item->jumlah_pembimbing_2,
                'honor_p1' => $item->honor_p1,
                'honor_p2' => $item->honor_p2,
                'total_honor_pembimbing' => $item->total_honor_pembimbing,
                'jumlah_ketua' => 0,
                'jumlah_anggota_1' => 0,
                'jumlah_anggota_2' => 0,
                'honor_ketua' => 0,
                'honor_anggota_1' => 0,
                'honor_anggota_2' => 0,
                'total_honor_penguji' => 0
            );
        }
        
        // Masukkan honor penguji
        foreach ($honor_penguji as $item) {
            if (isset($honor_map[$item->id_dosen])) {
                $honor_map[$item->id_dosen]['jumlah_ketua'] = $item->jumlah_ketua;
                $honor_map[$item->id_dosen]['jumlah_anggota_1'] = $item->jumlah_anggota_1;
                $honor_map[$item->id_dosen]['jumlah_anggota_2'] = $item->jumlah_anggota_2;
                $honor_map[$item->id_dosen]['honor_ketua'] = $item->honor_ketua;
                $honor_map[$item->id_dosen]['honor_anggota_1'] = $item->honor_anggota_1;
                $honor_map[$item->id_dosen]['honor_anggota_2'] = $item->honor_anggota_2;
                $honor_map[$item->id_dosen]['total_honor_penguji'] = $item->total_honor_penguji;
            } else {
                $honor_map[$item->id_dosen] = array(
                    'id_dosen' => $item->id_dosen,
                    'nidn' => $item->nidn,
                    'nama_dosen' => $item->nama_dosen,
                    'jumlah_pembimbing_1' => 0,
                    'jumlah_pembimbing_2' => 0,
                    'honor_p1' => 0,
                    'honor_p2' => 0,
                    'total_honor_pembimbing' => 0,
                    'jumlah_ketua' => $item->jumlah_ketua,
                    'jumlah_anggota_1' => $item->jumlah_anggota_1,
                    'jumlah_anggota_2' => $item->jumlah_anggota_2,
                    'honor_ketua' => $item->honor_ketua,
                    'honor_anggota_1' => $item->honor_anggota_1,
                    'honor_anggota_2' => $item->honor_anggota_2,
                    'total_honor_penguji' => $item->total_honor_penguji
                );
            }
        }
        
        // Hitung total honor keseluruhan per dosen
        foreach ($honor_map as $key => $item) {
            $item['total_honor'] = $item['total_honor_pembimbing'] + $item['total_honor_penguji'];
            $honor_all[] = $item;
        }
        
        // Urutkan berdasarkan nama dosen
        usort($honor_all, function($a, $b) {
            return strcmp($a['nama_dosen'], $b['nama_dosen']);
        });

        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Honor Skripsi');

        // Kop tabel
        $sheet->mergeCells('A1:L1');
        $sheet->setCellValue('A1', 'LAPORAN HONOR SKRIPSI');
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14)->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color('FFFFFFFF'));
        $sheet->getStyle('A1')->getFill()
            ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
            ->getStartColor()->setARGB('FF800000');
        $sheet->getStyle('A1')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER)
            ->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER);
        $sheet->getRowDimension('1')->setRowHeight(25);

        $sheet->setCellValue('A2', 'Tahun Akademik: ' . $tahun_aktif->tahun_akademik . ' ' . ucfirst($tahun_aktif->semester));
        $sheet->setCellValue('A3', 'Tanggal Export: ' . date('d/m/Y H:i:s'));

        // Header tabel
        $sheet->mergeCells('A5:A6');
        $sheet->setCellValue('A5', 'No');
        $sheet->mergeCells('B5:B6');
        $sheet->setCellValue('B5', 'NIDN');
        $sheet->mergeCells('C5:C6');
        $sheet->setCellValue('C5', 'Nama Dosen');
        $sheet->mergeCells('D5:F5');
        $sheet->setCellValue('D5', 'Pembimbing');
        $sheet->setCellValue('D6', 'P1');
        $sheet->setCellValue('E6', 'P2');
        $sheet->setCellValue('F6', 'Subtotal');
        $sheet->mergeCells('G5:J5');
        $sheet->setCellValue('G5', 'Penguji');
        $sheet->setCellValue('G6', 'Ketua');
        $sheet->setCellValue('H6', 'Anggota 1');
        $sheet->setCellValue('I6', 'Anggota 2');
        $sheet->setCellValue('J6', 'Subtotal');
        $sheet->mergeCells('K5:K6');
        $sheet->setCellValue('K5', 'Total Honor');
        
        // Style header tabel
        $sheet->getStyle('A5:K6')->getFont()->setBold(true);
        $sheet->getStyle('A5:K6')->getFill()
            ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
            ->getStartColor()->setARGB('FF800000');
        $sheet->getStyle('A5:K6')->getFont()->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color('FFFFFFFF'));
        $sheet->getStyle('A5:K6')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER)
            ->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER);
        $sheet->getRowDimension('5')->setRowHeight(20);
        $sheet->getRowDimension('6')->setRowHeight(20);

        $row = 7;
        $no = 1;
        
        foreach ($honor_all as $item) {
            $sheet->setCellValue('A' . $row, $no++);
            $sheet->setCellValue('B' . $row, $item['nidn']);
            $sheet->setCellValue('C' . $row, $item['nama_dosen']);
            $sheet->setCellValue('D' . $row, $item['jumlah_pembimbing_1'] . "\n(Rp " . number_format($item['honor_p1'], 0, ',', '.') . ')');
            $sheet->setCellValue('E' . $row, $item['jumlah_pembimbing_2'] . "\n(Rp " . number_format($item['honor_p2'], 0, ',', '.') . ')');
            $sheet->setCellValue('F' . $row, 'Rp ' . number_format($item['total_honor_pembimbing'], 0, ',', '.'));
            $sheet->setCellValue('G' . $row, $item['jumlah_ketua'] . "\n(Rp " . number_format($item['honor_ketua'], 0, ',', '.') . ')');
            $sheet->setCellValue('H' . $row, $item['jumlah_anggota_1'] . "\n(Rp " . number_format($item['honor_anggota_1'], 0, ',', '.') . ')');
            $sheet->setCellValue('I' . $row, $item['jumlah_anggota_2'] . "\n(Rp " . number_format($item['honor_anggota_2'], 0, ',', '.') . ')');
            $sheet->setCellValue('J' . $row, 'Rp ' . number_format($item['total_honor_penguji'], 0, ',', '.'));
            $sheet->setCellValue('K' . $row, 'Rp ' . number_format($item['total_honor'], 0, ',', '.'));
            
            // Format wrap text
            $sheet->getStyle('D' . $row . ':I' . $row)->getAlignment()->setWrapText(true);
            $sheet->getStyle('D' . $row . ':I' . $row)->getAlignment()->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER);
            
            $row++;
        }
        
        // Total row
        if (!empty($honor_all)) {
            $total_p1 = 0;
            $total_p2 = 0;
            $total_honor_pembimbing = 0;
            $total_ketua = 0;
            $total_anggota_1 = 0;
            $total_anggota_2 = 0;
            $total_honor_penguji = 0;
            $grand_total = 0;
            foreach ($honor_all as $item) {
                $total_p1 += $item['jumlah_pembimbing_1'];
                $total_p2 += $item['jumlah_pembimbing_2'];
                $total_honor_pembimbing += $item['total_honor_pembimbing'];
                $total_ketua += $item['jumlah_ketua'];
                $total_anggota_1 += $item['jumlah_anggota_1'];
                $total_anggota_2 += $item['jumlah_anggota_2'];
                $total_honor_penguji += $item['total_honor_penguji'];
                $grand_total += $item['total_honor'];
            }
            
            $sheet->setCellValue('A' . $row, 'TOTAL');
            $sheet->mergeCells('A' . $row . ':C' . $row);
            $sheet->setCellValue('D' . $row, $total_p1);
            $sheet->setCellValue('E' . $row, $total_p2);
            $sheet->setCellValue('F' . $row, 'Rp ' . number_format($total_honor_pembimbing, 0, ',', '.'));
            $sheet->setCellValue('G' . $row, $total_ketua);
            $sheet->setCellValue('H' . $row, $total_anggota_1);
            $sheet->setCellValue('I' . $row, $total_anggota_2);
            $sheet->setCellValue('J' . $row, 'Rp ' . number_format($total_honor_penguji, 0, ',', '.'));
            $sheet->setCellValue('K' . $row, 'Rp ' . number_format($grand_total, 0, ',', '.'));
            
            $sheet->getStyle('A' . $row . ':K' . $row)->getFont()->setBold(true);
            $sheet->getStyle('A' . $row . ':K' . $row)->getFill()
                ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                ->getStartColor()->setARGB('FFE0E0E0');
        }

        // Auto size columns
        foreach (range('A', 'K') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }
        
        // Set width
        $sheet->getColumnDimension('C')->setWidth(30);
        $sheet->getColumnDimension('D')->setWidth(15);
        $sheet->getColumnDimension('E')->setWidth(15);
        $sheet->getColumnDimension('F')->setWidth(18);
        $sheet->getColumnDimension('G')->setWidth(15);
        $sheet->getColumnDimension('H')->setWidth(15);
        $sheet->getColumnDimension('I')->setWidth(15);
        $sheet->getColumnDimension('J')->setWidth(18);
        $sheet->getColumnDimension('K')->setWidth(18);

        // Border
        $last_row = !empty($honor_all) ? $row : $row - 1;
        $sheet->getStyle('A5:K' . $last_row)->applyFromArray(array(
            'borders' => array(
                'allBorders' => array(
                    'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                    'color' => array('rgb' => '000000'),
                ),
            ),
        ));
        
        // Alignment
        $sheet->getStyle('F7:F' . ($row - 1))->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT);
        $sheet->getStyle('J7:J' . ($row - 1))->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT);
        $sheet->getStyle('K7:K' . ($row - 1))->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT);
        if (!empty($honor_all)) {
            $sheet->getStyle('F' . $row . ':K' . $row)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT);
        }

        // Download
        $filename = 'Rekap_Honor_Skripsi_' . date('YmdHis') . '.xlsx';
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $filename . '"');
        header('Cache-Control: max-age=0');

        $writer = \PhpOffice\PhpSpreadsheet\IOFactory::createWriter($spreadsheet, 'Xlsx');
        $writer->save('php://output');
        exit;
    }

    /**
     * Export Honor Skripsi ke CSV (Fallback)
     */
    private function export_csv() {
        $tahun_aktif = $this->Tahun_akademik_model->get_aktif();
        if (!$tahun_aktif) {
            $this->session->set_flashdata('error', 'Tahun akademik aktif tidak ditemukan.');
            redirect('Honor_skripsi');
        }

        $id_tahun_akademik = $tahun_aktif->id;
        
        // Ambil setup honor
        $honor_p1 = $this->Honor_setup_model->get_besaran('pembimbing_1_skripsi', $id_tahun_akademik);
        $honor_p2 = $this->Honor_setup_model->get_besaran('pembimbing_2_skripsi', $id_tahun_akademik);
        $honor_ketua = $this->Honor_setup_model->get_besaran('ketua_penguji_skripsi', $id_tahun_akademik);
        $honor_anggota_1 = $this->Honor_setup_model->get_besaran('anggota_penguji_1_skripsi', $id_tahun_akademik);
        $honor_anggota_2 = $this->Honor_setup_model->get_besaran('anggota_penguji_2_skripsi', $id_tahun_akademik);
        
        // Ambil data honor pembimbing
        $honor_pembimbing = $this->Honor_skripsi_model->get_honor_pembimbing($id_tahun_akademik);
        
        // Hitung total honor per dosen pembimbing
        foreach ($honor_pembimbing as $item) {
            $item->honor_p1 = $item->jumlah_pembimbing_1 * $honor_p1;
            $item->honor_p2 = $item->jumlah_pembimbing_2 * $honor_p2;
            $item->total_honor_pembimbing = $item->honor_p1 + $item->honor_p2;
        }
        
        // Ambil data honor penguji
        $honor_penguji = $this->Honor_skripsi_model->get_honor_penguji($id_tahun_akademik);
        
        // Hitung total honor per dosen penguji
        foreach ($honor_penguji as $item) {
            $item->honor_ketua = $item->jumlah_ketua * $honor_ketua;
            $item->honor_anggota_1 = $item->jumlah_anggota_1 * $honor_anggota_1;
            $item->honor_anggota_2 = $item->jumlah_anggota_2 * $honor_anggota_2;
            $item->total_honor_penguji = $item->honor_ketua + $item->honor_anggota_1 + $item->honor_anggota_2;
        }
        
        // Gabungkan data pembimbing dan penguji per dosen
        $honor_all = array();
        $honor_map = array();
        
        // Masukkan honor pembimbing
        foreach ($honor_pembimbing as $item) {
            $honor_map[$item->id_dosen] = array(
                'id_dosen' => $item->id_dosen,
                'nidn' => $item->nidn,
                'nama_dosen' => $item->nama_dosen,
                'jumlah_pembimbing_1' => $item->jumlah_pembimbing_1,
                'jumlah_pembimbing_2' => $item->jumlah_pembimbing_2,
                'honor_p1' => $item->honor_p1,
                'honor_p2' => $item->honor_p2,
                'total_honor_pembimbing' => $item->total_honor_pembimbing,
                'jumlah_ketua' => 0,
                'jumlah_anggota_1' => 0,
                'jumlah_anggota_2' => 0,
                'honor_ketua' => 0,
                'honor_anggota_1' => 0,
                'honor_anggota_2' => 0,
                'total_honor_penguji' => 0
            );
        }
        
        // Masukkan honor penguji
        foreach ($honor_penguji as $item) {
            if (isset($honor_map[$item->id_dosen])) {
                $honor_map[$item->id_dosen]['jumlah_ketua'] = $item->jumlah_ketua;
                $honor_map[$item->id_dosen]['jumlah_anggota_1'] = $item->jumlah_anggota_1;
                $honor_map[$item->id_dosen]['jumlah_anggota_2'] = $item->jumlah_anggota_2;
                $honor_map[$item->id_dosen]['honor_ketua'] = $item->honor_ketua;
                $honor_map[$item->id_dosen]['honor_anggota_1'] = $item->honor_anggota_1;
                $honor_map[$item->id_dosen]['honor_anggota_2'] = $item->honor_anggota_2;
                $honor_map[$item->id_dosen]['total_honor_penguji'] = $item->total_honor_penguji;
            } else {
                $honor_map[$item->id_dosen] = array(
                    'id_dosen' => $item->id_dosen,
                    'nidn' => $item->nidn,
                    'nama_dosen' => $item->nama_dosen,
                    'jumlah_pembimbing_1' => 0,
                    'jumlah_pembimbing_2' => 0,
                    'honor_p1' => 0,
                    'honor_p2' => 0,
                    'total_honor_pembimbing' => 0,
                    'jumlah_ketua' => $item->jumlah_ketua,
                    'jumlah_anggota_1' => $item->jumlah_anggota_1,
                    'jumlah_anggota_2' => $item->jumlah_anggota_2,
                    'honor_ketua' => $item->honor_ketua,
                    'honor_anggota_1' => $item->honor_anggota_1,
                    'honor_anggota_2' => $item->honor_anggota_2,
                    'total_honor_penguji' => $item->total_honor_penguji
                );
            }
        }
        
        // Hitung total honor keseluruhan per dosen
        foreach ($honor_map as $key => $item) {
            $item['total_honor'] = $item['total_honor_pembimbing'] + $item['total_honor_penguji'];
            $honor_all[] = $item;
        }
        
        // Urutkan berdasarkan nama dosen
        usort($honor_all, function($a, $b) {
            return strcmp($a['nama_dosen'], $b['nama_dosen']);
        });

        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment;filename="Rekap_Honor_Skripsi_' . date('YmdHis') . '.csv"');
        header('Cache-Control: max-age=0');
        
        echo "\xEF\xBB\xBF";
        
        $output = fopen('php://output', 'w');
        fputcsv($output, array('LAPORAN HONOR SKRIPSI'));
        fputcsv($output, array('Tahun Akademik: ' . $tahun_aktif->tahun_akademik . ' ' . ucfirst($tahun_aktif->semester)));
        fputcsv($output, array('Tanggal Export: ' . date('d/m/Y H:i:s')));
        fputcsv($output, array());
        fputcsv($output, array('No', 'NIDN', 'Nama Dosen', 'P1 (Jumlah)', 'P1 (Honor)', 'P2 (Jumlah)', 'P2 (Honor)', 'Subtotal Pembimbing', 'Ketua (Jumlah)', 'Ketua (Honor)', 'Anggota 1 (Jumlah)', 'Anggota 1 (Honor)', 'Anggota 2 (Jumlah)', 'Anggota 2 (Honor)', 'Subtotal Penguji', 'Total Honor'));
        
        $no = 1;
        foreach ($honor_all as $item) {
            fputcsv($output, array(
                $no++,
                $item['nidn'],
                $item['nama_dosen'],
                $item['jumlah_pembimbing_1'],
                'Rp ' . number_format($item['honor_p1'], 0, ',', '.'),
                $item['jumlah_pembimbing_2'],
                'Rp ' . number_format($item['honor_p2'], 0, ',', '.'),
                'Rp ' . number_format($item['total_honor_pembimbing'], 0, ',', '.'),
                $item['jumlah_ketua'],
                'Rp ' . number_format($item['honor_ketua'], 0, ',', '.'),
                $item['jumlah_anggota_1'],
                'Rp ' . number_format($item['honor_anggota_1'], 0, ',', '.'),
                $item['jumlah_anggota_2'],
                'Rp ' . number_format($item['honor_anggota_2'], 0, ',', '.'),
                'Rp ' . number_format($item['total_honor_penguji'], 0, ',', '.'),
                'Rp ' . number_format($item['total_honor'], 0, ',', '.')
            ));
        }
        
        fclose($output);
        exit;
    }

    /**
     * Detail honor untuk dosen tertentu
     */
    public function detail($id_dosen) {
        $tahun_aktif = $this->Tahun_akademik_model->get_aktif();
        $id_tahun_akademik = $tahun_aktif ? $tahun_aktif->id : null;
        
        // Ambil data dosen
        $this->load->model('Dosen_model');
        $dosen = $this->Dosen_model->get_by_id($id_dosen);
        if (!$dosen) {
            $this->session->set_flashdata('error', 'Data dosen tidak ditemukan.');
            redirect('Honor_skripsi');
        }
        
        // Ambil setup honor
        $honor_p1 = $this->Honor_setup_model->get_besaran('pembimbing_1_skripsi', $id_tahun_akademik);
        $honor_p2 = $this->Honor_setup_model->get_besaran('pembimbing_2_skripsi', $id_tahun_akademik);
        $honor_ketua = $this->Honor_setup_model->get_besaran('ketua_penguji_skripsi', $id_tahun_akademik);
        $honor_anggota_1 = $this->Honor_setup_model->get_besaran('anggota_penguji_1_skripsi', $id_tahun_akademik);
        $honor_anggota_2 = $this->Honor_setup_model->get_besaran('anggota_penguji_2_skripsi', $id_tahun_akademik);
        
        // Ambil detail pembimbing
        $detail_pembimbing = $this->Honor_skripsi_model->get_detail_pembimbing($id_dosen, $id_tahun_akademik);
        
        // Ambil detail penguji
        $detail_penguji = $this->Honor_skripsi_model->get_detail_penguji($id_dosen, $id_tahun_akademik);
        
        $data = array(
            'title' => 'Detail Honor Skripsi',
            'page_title' => 'Detail Honor Skripsi - ' . $dosen->nama_dosen,
            'breadcrumb' => array(
                array('label' => 'Honor', 'url' => site_url('Honor')),
                array('label' => 'Skripsi', 'url' => site_url('Honor_skripsi')),
                array('label' => 'Detail', 'active' => true)
            ),
            'sidebar_menu' => generate_sidebar_menu(get_user_role()),
            'tahun_aktif' => $tahun_aktif,
            'dosen' => $dosen,
            'detail_pembimbing' => $detail_pembimbing,
            'detail_penguji' => $detail_penguji,
            'honor_p1' => $honor_p1,
            'honor_p2' => $honor_p2,
            'honor_ketua' => $honor_ketua,
            'honor_anggota_1' => $honor_anggota_1,
            'honor_anggota_2' => $honor_anggota_2
        );
        
        $data['content'] = $this->load->view('honor/skripsi_detail', $data, true);
        $this->load->view('layouts/main', $data);
    }
}

