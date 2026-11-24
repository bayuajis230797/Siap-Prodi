<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Pkl_laporan_sekprodi Controller
 * Laporan PKL untuk sekprodi berisi daftar nama pembimbing, penguji, tempat PKL, dan Nilai Akhir
 */
class Pkl_laporan_sekprodi extends MY_Controller {

    public function __construct() {
        parent::__construct();
        $this->check_role('sekprodi');
        $this->load->model('Mahasiswa_pkl_model');
        $this->load->model('Pkl_pengajuan_model');
        $this->load->model('Pkl_tempat_model');
        $this->load->model('Pkl_nilai_rekap_model');
        $this->load->model('Jadwal_pkl_model');
        $this->load->model('Tahun_akademik_model');
        $this->load->model('Pkl_penilaian_model');
        $this->load->model('Pkl_penilaian_detail_model');
        $this->load->model('Pkl_aspek_penilaian_model');
        $this->load->model('Pkl_item_penilaian_model');
        $this->load->database();
    }

    /**
     * Index - Daftar laporan PKL
     */
    public function index() {
        $tahun_aktif = $this->Tahun_akademik_model->get_aktif();
        if (!$tahun_aktif) {
            $this->session->set_flashdata('error', 'Tidak ada tahun akademik aktif.');
            redirect('Dashboard');
        }

        // Ambil semua data laporan PKL
        $laporan_list = $this->get_laporan_data($tahun_aktif->id);

        $data = array(
            'title' => 'Laporan PKL',
            'page_title' => 'Laporan PKL',
            'breadcrumb' => array(
                array('label' => 'PKL', 'url' => '#'),
                array('label' => 'Laporan PKL', 'active' => true)
            ),
            'sidebar_menu' => generate_sidebar_menu('sekprodi'),
            'laporan_list' => $laporan_list,
            'tahun_aktif' => $tahun_aktif
        );

        $data['content'] = $this->load->view('sekprodi/pkl_laporan_index', $data, true);
        $this->load->view('layouts/main', $data);
    }

    /**
     * Export laporan PKL ke Excel
     */
    public function export_excel() {
        $tahun_aktif = $this->Tahun_akademik_model->get_aktif();
        if (!$tahun_aktif) {
            $this->session->set_flashdata('error', 'Tidak ada tahun akademik aktif.');
            redirect('Pkl_laporan_sekprodi');
        }

        // Ambil semua data laporan PKL
        $laporan_list = $this->get_laporan_data($tahun_aktif->id);

        if (empty($laporan_list)) {
            $this->session->set_flashdata('warning', 'Tidak ada data laporan PKL untuk di-export.');
            redirect('Pkl_laporan_sekprodi');
        }

        // Cek apakah PhpSpreadsheet tersedia
        $use_phpspreadsheet = false;
        if (file_exists(APPPATH . '../vendor/autoload.php')) {
            require_once APPPATH . '../vendor/autoload.php';
            if (class_exists('PhpOffice\PhpSpreadsheet\Spreadsheet')) {
                $use_phpspreadsheet = true;
            }
        }

        $filename_base = 'Laporan_PKL_' . $tahun_aktif->tahun_akademik . '_' . ucfirst($tahun_aktif->semester) . '_' . date('YmdHis');

        if ($use_phpspreadsheet) {
            // Gunakan PhpSpreadsheet
            $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet();
            $sheet->setTitle('Laporan PKL');

            // Header
            $sheet->setCellValue('A1', 'LAPORAN PKL');
            $sheet->mergeCells('A1:L1');
            $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14);
            $sheet->getStyle('A1')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

            $sheet->setCellValue('A2', 'Tahun Akademik: ' . $tahun_aktif->tahun_akademik . ' ' . ucfirst($tahun_aktif->semester));
            $sheet->mergeCells('A2:L2');
            $sheet->getStyle('A2')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

            // Header tabel
            $headers = array('No', 'NIM', 'Nama Mahasiswa', 'Judul PKL', 'Tempat PKL', 'Dosen Pembimbing', 'Ruang Sidang', 'Penguji', 'NIDN Penguji', 'Jam Sidang', 'Nilai Akhir', 'Nilai Huruf');
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
            foreach ($laporan_list as $laporan) {
                $sheet->setCellValue('A' . $row, $no++);
                $sheet->setCellValue('B' . $row, $laporan->nim);
                $sheet->setCellValue('C' . $row, $laporan->nama_mahasiswa);
                $sheet->setCellValue('D' . $row, $laporan->judul_pkl ?: '-');
                $sheet->setCellValue('E' . $row, $laporan->tempat_pkl ?: '-');
                $sheet->setCellValue('F' . $row, $laporan->pembimbing_nama ?: '-');
                $sheet->setCellValue('G' . $row, isset($laporan->ruang_sidang) ? ($laporan->ruang_sidang ?: '-') : '-');
                $sheet->setCellValue('H' . $row, $laporan->penguji_nama ?: '-');
                $sheet->setCellValue('I' . $row, $laporan->penguji_nidn ?: '-');
                $sheet->setCellValue('J' . $row, isset($laporan->jam_sidang) ? ($laporan->jam_sidang ?: '-') : '-');
                $sheet->setCellValue('K' . $row, $laporan->nilai_akhir ? number_format($laporan->nilai_akhir, 2) : '-');
                $sheet->setCellValue('L' . $row, $laporan->nilai_huruf ?: '-');
                $row++;
            }

            // Auto width columns
            foreach (range('A', 'L') as $col) {
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
            $sheet->getStyle('A4:L' . ($row - 1))->applyFromArray($styleArray);

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
            // UTF-8 BOM untuk Excel
            fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));

            // Header
            fputcsv($output, array('LAPORAN PKL'));
            fputcsv($output, array('Tahun Akademik: ' . $tahun_aktif->tahun_akademik . ' ' . ucfirst($tahun_aktif->semester)));
            fputcsv($output, array('')); // Empty row
            fputcsv($output, array('No', 'NIM', 'Nama Mahasiswa', 'Judul PKL', 'Tempat PKL', 'Dosen Pembimbing', 'Ruang Sidang', 'Penguji', 'NIDN Penguji', 'Jam Sidang', 'Nilai Akhir', 'Nilai Huruf'));

            // Data
            $no = 1;
            foreach ($laporan_list as $laporan) {
                fputcsv($output, array(
                    $no++,
                    $laporan->nim,
                    $laporan->nama_mahasiswa,
                    $laporan->judul_pkl ?: '-',
                    $laporan->tempat_pkl ?: '-',
                    $laporan->pembimbing_nama ?: '-',
                    isset($laporan->ruang_sidang) ? ($laporan->ruang_sidang ?: '-') : '-',
                    $laporan->penguji_nama ?: '-',
                    $laporan->penguji_nidn ?: '-',
                    isset($laporan->jam_sidang) ? ($laporan->jam_sidang ?: '-') : '-',
                    $laporan->nilai_akhir ? number_format($laporan->nilai_akhir, 2) : '-',
                    $laporan->nilai_huruf ?: '-'
                ));
            }

            fclose($output);
            exit;
        }
    }

    /**
     * Ambil data laporan PKL lengkap
     */
    private function get_laporan_data($id_tahun_akademik) {
        $this->load->helper('pkl');
        // Ambil semua mahasiswa yang terdaftar PKL
        $mahasiswa_pkl_list = $this->Mahasiswa_pkl_model->get_all($id_tahun_akademik);
        
        $laporan_list = array();
        
        foreach ($mahasiswa_pkl_list as $mp) {
            $item = new stdClass();
            $item->id_mahasiswa = $mp->id_mahasiswa;
            $item->nim = $mp->nim;
            $item->nama_mahasiswa = $mp->nama_mahasiswa;
            $item->kelas = $mp->kelas;
            
            // Ambil pengajuan PKL (untuk judul dan pembimbing)
            $pengajuan = $this->Pkl_pengajuan_model->get_by_mahasiswa($mp->id_mahasiswa, $id_tahun_akademik);
            if ($pengajuan) {
                $item->judul_pkl = $pengajuan->judul_pkl;
                $item->pembimbing_nama = $pengajuan->pembimbing_nama;
                $item->pembimbing_nidn = $pengajuan->pembimbing_nidn;
            } else {
                $item->judul_pkl = null;
                $item->pembimbing_nama = null;
                $item->pembimbing_nidn = null;
            }
            
            // Ambil tempat PKL
            $tempat = $this->Pkl_tempat_model->get_by_mahasiswa($mp->id_mahasiswa, $id_tahun_akademik);
            if ($tempat) {
                $tempat_text = isset($tempat->nama_instansi) ? $tempat->nama_instansi : '';
                if (isset($tempat->alamat_instansi) && !empty($tempat->alamat_instansi)) {
                    $tempat_text .= ($tempat_text ? ' - ' : '') . $tempat->alamat_instansi;
                }
                $item->tempat_pkl = $tempat_text ?: null;
            } else {
                $item->tempat_pkl = null;
            }
            
            // Ambil jadwal PKL (untuk penguji, ruang sidang, dan jam sidang)
            $jadwal_list = $this->Jadwal_pkl_model->get_by_mahasiswa($mp->id_mahasiswa, $id_tahun_akademik);
            if (!empty($jadwal_list)) {
                // Ambil semua penguji dari tabel pkl_penguji
                $jadwal = $jadwal_list[0];
                
                // Ambil ruang sidang dan jam sidang
                $item->ruang_sidang = $jadwal->ruangan ?: null;
                if ($jadwal->jam_mulai && $jadwal->jam_selesai) {
                    $item->jam_sidang = date('H:i', strtotime($jadwal->jam_mulai)) . ' - ' . date('H:i', strtotime($jadwal->jam_selesai));
                } else {
                    $item->jam_sidang = null;
                }
                
                // Cek apakah ada penguji di tabel pkl_penguji
                $this->db->select('d.nama_dosen, d.nidn');
                $this->db->from('pkl_penguji pp');
                $this->db->join('dosen d', 'd.id = pp.id_dosen', 'left');
                $this->db->where('pp.id_jadwal_pkl', $jadwal->id);
                $penguji_list = $this->db->get()->result();
                
                if (!empty($penguji_list)) {
                    // Jika ada multiple penguji, gabungkan dengan koma
                    $penguji_nama = array();
                    $penguji_nidn = array();
                    foreach ($penguji_list as $penguji) {
                        if ($penguji->nama_dosen) {
                            $penguji_nama[] = $penguji->nama_dosen;
                            $penguji_nidn[] = $penguji->nidn;
                        }
                    }
                    $item->penguji_nama = !empty($penguji_nama) ? implode(', ', $penguji_nama) : ($jadwal->penguji_nama ?: null);
                    $item->penguji_nidn = !empty($penguji_nidn) ? implode(', ', $penguji_nidn) : ($jadwal->penguji_nidn ?: null);
                } else {
                    // Fallback ke penguji dari jadwal_pkl jika tidak ada di pkl_penguji
                    $item->penguji_nama = $jadwal->penguji_nama;
                    $item->penguji_nidn = $jadwal->penguji_nidn;
                }
            } else {
                $item->penguji_nama = null;
                $item->penguji_nidn = null;
                $item->ruang_sidang = null;
                $item->jam_sidang = null;
            }
            
            // Ambil nilai rekap
            $nilai_rekap = $this->Pkl_nilai_rekap_model->get_by_mahasiswa($mp->id_mahasiswa, $id_tahun_akademik);
            if ($nilai_rekap) {
                $item->nilai_akhir = $nilai_rekap->nilai_akhir;
                $item->nilai_huruf = null;
            } else {
                $item->nilai_akhir = null;
                $item->nilai_huruf = null;
            }
            // Nilai huruf selalu dari konversi sesuai Setting Grade (admin) agar tabel konsisten dengan setting
            if ($item->nilai_akhir !== null && $item->nilai_akhir !== '') {
                $item->nilai_huruf = konversi_nilai_huruf_pkl((float)$item->nilai_akhir);
            }
            
            $laporan_list[] = $item;
        }
        
        // Sort by kelas, then NIM
        usort($laporan_list, function($a, $b) {
            if ($a->kelas != $b->kelas) {
                return strcmp($a->kelas, $b->kelas);
            }
            return strcmp($a->nim, $b->nim);
        });
        
        return $laporan_list;
    }

    /**
     * Export rekap nilai PKL ke CSV
     * Berisi rekap semua item butir penilaian dari semua role
     */
    public function export_rekap_nilai() {
        $this->load->helper('pkl');
        $tahun_aktif = $this->Tahun_akademik_model->get_aktif();
        if (!$tahun_aktif) {
            $this->session->set_flashdata('error', 'Tidak ada tahun akademik aktif.');
            redirect('Pkl_laporan_sekprodi');
        }

        // Ambil semua mahasiswa yang terdaftar PKL
        $mahasiswa_pkl_list = $this->Mahasiswa_pkl_model->get_all($tahun_aktif->id);
        
        if (empty($mahasiswa_pkl_list)) {
            $this->session->set_flashdata('warning', 'Tidak ada data mahasiswa PKL untuk di-export.');
            redirect('Pkl_laporan_sekprodi');
        }

        // Ambil semua item penilaian untuk setiap role
        $roles = array('sekprodi', 'pembimbing_dosen', 'penguji');
        $items_by_role = array();
        $role_labels = array(
            'sekprodi' => 'Sekprodi',
            'pembimbing_dosen' => 'Dosen Pembimbing',
            'penguji' => 'Dosen Penguji'
        );

        foreach ($roles as $role) {
            // Ambil aspek penilaian untuk role ini
            $aspek_list = $this->Pkl_aspek_penilaian_model->get_by_ditunjukkan_untuk($role, true);
            $items = array();
            
            foreach ($aspek_list as $aspek) {
                $aspek_items = $this->Pkl_item_penilaian_model->get_by_aspek($aspek->id, true);
                foreach ($aspek_items as $item) {
                    $items[] = $item;
                }
            }
            
            $items_by_role[$role] = $items;
        }

        // Prepare filename
        $filename_base = 'Rekap_Nilai_PKL_' . $tahun_aktif->tahun_akademik . '_' . ucfirst($tahun_aktif->semester) . '_' . date('YmdHis');
        $filename = $filename_base . '.csv';

        // Set headers untuk download CSV
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment;filename="' . $filename . '"');
        header('Cache-Control: max-age=0');

        // Buka output stream
        $output = fopen('php://output', 'w');
        
        // UTF-8 BOM untuk Excel
        fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));

        // Header 1: Judul
        fputcsv($output, array('REKAP NILAI PKL'));

        // Header 2: Tahun Akademik
        fputcsv($output, array('Tahun Akademik: ' . $tahun_aktif->tahun_akademik . ' ' . ucfirst($tahun_aktif->semester)));

        // Baris kosong
        fputcsv($output, array(''));

        // Header kolom
        $header_row = array('No', 'Nama');
        
        // Tambahkan header untuk setiap role dengan item-itemnya
        foreach ($roles as $role) {
            $role_label = isset($role_labels[$role]) ? $role_labels[$role] : ucwords(str_replace('_', ' ', $role));
            $items = $items_by_role[$role];
            foreach ($items as $index => $item) {
                $header_row[] = $role_label . ' - Item ' . ($index + 1);
            }
        }
        
        // Tambahkan Nilai Akhir dan Grade
        $header_row[] = 'Nilai Akhir';
        $header_row[] = 'Grade';
        
        fputcsv($output, $header_row);

        // Data rows
        $no = 1;
        foreach ($mahasiswa_pkl_list as $mp) {
            $data_row = array();
            
            // No
            $data_row[] = $no++;
            
            // Nama
            $data_row[] = $mp->nama_mahasiswa;

            // Ambil penilaian untuk mahasiswa ini
            $penilaian_by_role = array();
            foreach ($roles as $role) {
                $penilaian = $this->Pkl_penilaian_model->get_by_mahasiswa_tipe($mp->id_mahasiswa, $tahun_aktif->id, $role);
                if ($penilaian) {
                    $penilaian->details = $this->Pkl_penilaian_detail_model->get_by_penilaian($penilaian->id);
                    // Buat array index by item id
                    $penilaian_by_role[$role] = array();
                    foreach ($penilaian->details as $detail) {
                        $penilaian_by_role[$role][$detail->id_item_penilaian] = $detail->nilai;
                    }
                } else {
                    $penilaian_by_role[$role] = array();
                }
            }

            // Nilai per item untuk setiap role
            foreach ($roles as $role) {
                $items = $items_by_role[$role];
                foreach ($items as $item) {
                    $nilai = isset($penilaian_by_role[$role][$item->id]) ? $penilaian_by_role[$role][$item->id] : '';
                    $data_row[] = $nilai !== '' ? number_format($nilai, 2) : '-';
                }
            }

            // Nilai Akhir
            $nilai_rekap = $this->Pkl_nilai_rekap_model->get_by_mahasiswa($mp->id_mahasiswa, $tahun_aktif->id);
            $nilai_akhir = $nilai_rekap ? $nilai_rekap->nilai_akhir : null;
            $data_row[] = ($nilai_akhir !== null && $nilai_akhir !== '') ? number_format((float)$nilai_akhir, 2) : '-';

            // Grade: selalu dari konversi sesuai Setting Grade (admin)
            $grade = ($nilai_akhir !== null && $nilai_akhir !== '') ? konversi_nilai_huruf_pkl((float)$nilai_akhir) : '';
            $data_row[] = $grade ?: '-';

            fputcsv($output, $data_row);
        }

        fclose($output);
        exit;
    }

    /**
     * Helper function untuk mendapatkan kolom berikutnya (A -> B, Z -> AA, dll)
     */
    private function getNextColumn($col) {
        $colNum = $this->columnToNumber($col);
        return $this->numberToColumn($colNum + 1);
    }

    /**
     * Helper function untuk mendapatkan kolom sebelumnya
     */
    private function getPreviousColumn($col) {
        $colNum = $this->columnToNumber($col);
        if ($colNum <= 1) return 'A';
        return $this->numberToColumn($colNum - 1);
    }

    /**
     * Helper function untuk convert kolom letter ke number (A=1, B=2, ..., Z=26, AA=27, dll)
     */
    private function columnToNumber($col) {
        $col = strtoupper($col);
        $len = strlen($col);
        $num = 0;
        for ($i = 0; $i < $len; $i++) {
            $num = $num * 26 + (ord($col[$i]) - ord('A') + 1);
        }
        return $num;
    }

    /**
     * Helper function untuk convert number ke kolom letter (1=A, 2=B, ..., 26=Z, 27=AA, dll)
     */
    private function numberToColumn($num) {
        $col = '';
        while ($num > 0) {
            $num--;
            $col = chr(65 + ($num % 26)) . $col;
            $num = intval($num / 26);
        }
        return $col;
    }
}
