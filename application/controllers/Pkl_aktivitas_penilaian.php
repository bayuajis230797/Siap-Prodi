<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Pkl_aktivitas_penilaian Controller
 * Menampilkan aktivitas penilaian PKL secara rinci untuk sekprodi
 */
class Pkl_aktivitas_penilaian extends MY_Controller {

    public function __construct() {
        parent::__construct();
        $this->check_role(array('sekprodi', 'kaprodi'));
        $this->load->model('Pkl_pengajuan_model');
        $this->load->model('Pkl_penilaian_model');
        $this->load->model('Pkl_penilaian_detail_model');
        $this->load->model('Pkl_nilai_rekap_model');
        $this->load->model('Pkl_aspek_penilaian_model');
        $this->load->model('Pkl_item_penilaian_model');
        $this->load->model('Pkl_tempat_model');
        $this->load->model('Jadwal_pkl_model');
        $this->load->model('Dosen_model');
        $this->load->model('Tahun_akademik_model');
        $this->load->helper('pkl');
        $this->load->library('pagination');
    }

    /**
     * Index - Daftar semua aktivitas penilaian PKL
     */
    public function index() {
        $tahun_aktif = $this->Tahun_akademik_model->get_aktif();
        if (!$tahun_aktif) {
            $this->session->set_flashdata('error', 'Tidak ada tahun akademik aktif.');
            redirect('Dashboard');
        }

        // Ambil semua mahasiswa PKL yang sudah memiliki penilaian
        $pengajuan_list = $this->Pkl_pengajuan_model->get_all($tahun_aktif->id, 'disetujui');
        
        $mahasiswa_penilaian = array();
        foreach ($pengajuan_list as $pengajuan) {
            if ($pengajuan->status_acc_judul === 'acc') {
                // Ambil semua penilaian untuk mahasiswa ini
                $penilaian_list = $this->Pkl_penilaian_model->get_by_mahasiswa($pengajuan->id_mahasiswa, $tahun_aktif->id);
                
                if (!empty($penilaian_list)) {
                    // Ambil nilai rekap
                    $nilai_rekap = $this->Pkl_nilai_rekap_model->get_by_mahasiswa($pengajuan->id_mahasiswa, $tahun_aktif->id);
                    
                    // Hitung nilai akhir dan grade jika belum ada di rekap
                    $nilai_akhir = 0;
                    $grade = null;
                    
                    if ($nilai_rekap && $nilai_rekap->nilai_akhir > 0) {
                        $nilai_akhir = $nilai_rekap->nilai_akhir;
                        $grade = $nilai_rekap->nilai_huruf;
                    } else {
                        // Hitung dari penilaian yang ada
                        $p1 = 0; $p2 = 0; $p3 = 0; $p4 = 0; $p5 = 0; $p6 = 0;
                        foreach ($penilaian_list as $pen) {
                            $p1 = max($p1, floatval($pen->nilai_p1));
                            $p2 = max($p2, floatval($pen->nilai_p2));
                            $p3 = max($p3, floatval($pen->nilai_p3));
                            $p4 = max($p4, floatval($pen->nilai_p4));
                            $p5 = max($p5, floatval($pen->nilai_p5));
                            $p6 = max($p6, floatval($pen->nilai_p6));
                        }
                        if ($p1 + $p2 + $p3 + $p4 + $p5 + $p6 > 0) {
                            $nilai_akhir = hitung_nilai_akhir_pkl($p1, $p2, $p3, $p4, $p5, $p6);
                            $grade = konversi_nilai_huruf_pkl($nilai_akhir);
                        }
                    }
                    
                    // Jika grade masih null tapi nilai_akhir ada, hitung grade
                    if (!$grade && $nilai_akhir > 0) {
                        $grade = konversi_nilai_huruf_pkl($nilai_akhir);
                    }
                    
                    $mahasiswa_penilaian[] = array(
                        'pengajuan' => $pengajuan,
                        'penilaian_list' => $penilaian_list,
                        'nilai_rekap' => $nilai_rekap,
                        'nilai_akhir' => $nilai_akhir,
                        'grade' => $grade
                    );
                }
            }
        }

        $data = array(
            'title' => 'Aktivitas Penilaian PKL',
            'page_title' => 'Aktivitas Penilaian PKL',
            'breadcrumb' => array(
                array('label' => 'PKL', 'url' => '#'),
                array('label' => 'Aktivitas Penilaian', 'active' => true)
            ),
            'sidebar_menu' => generate_sidebar_menu(get_user_role()),
            'mahasiswa_penilaian' => $mahasiswa_penilaian,
            'tahun_aktif' => $tahun_aktif
        );

        $data['content'] = $this->load->view('sekprodi/pkl_aktivitas_penilaian/index', $data, true);
        $this->load->view('layouts/main', $data);
    }

    /**
     * Detail penilaian untuk mahasiswa tertentu
     */
    public function detail($pengajuan_id) {
        $tahun_aktif = $this->Tahun_akademik_model->get_aktif();
        if (!$tahun_aktif) {
            $this->session->set_flashdata('error', 'Tidak ada tahun akademik aktif.');
            redirect('Pkl_aktivitas_penilaian');
        }

        // Ambil pengajuan PKL
        $pengajuan = $this->Pkl_pengajuan_model->get_by_id($pengajuan_id);
        if (!$pengajuan) {
            $this->session->set_flashdata('error', 'Data pengajuan tidak ditemukan.');
            redirect('Pkl_aktivitas_penilaian');
        }

        // Ambil semua penilaian untuk mahasiswa ini
        $penilaian_list = $this->Pkl_penilaian_model->get_by_mahasiswa($pengajuan->id_mahasiswa, $tahun_aktif->id);
        
        // Untuk setiap penilaian, ambil detail per item
        foreach ($penilaian_list as &$penilaian) {
            $penilaian->details = $this->Pkl_penilaian_detail_model->get_by_penilaian($penilaian->id);
            
            // Ambil info penilai
            if ($penilaian->tipe_penilai === 'pembimbing_dosen' || $penilaian->tipe_penilai === 'penguji') {
                $this->load->model('Dosen_model');
                $dosen = $this->Dosen_model->get_by_id($penilaian->id_penilai);
                $penilaian->penilai_nama = $dosen ? $dosen->nama_dosen : '-';
            } else {
                $penilaian->penilai_nama = ucfirst(str_replace('_', ' ', $penilaian->tipe_penilai));
            }
        }
        unset($penilaian);

        // Ambil nilai rekap
        $nilai_rekap = $this->Pkl_nilai_rekap_model->get_by_mahasiswa($pengajuan->id_mahasiswa, $tahun_aktif->id);

        // Ambil semua aspek dan item untuk display
        $aspek_list = $this->Pkl_aspek_penilaian_model->get_all(true);
        foreach ($aspek_list as &$aspek) {
            $aspek->items = $this->Pkl_item_penilaian_model->get_by_aspek($aspek->id, true);
        }
        unset($aspek);

        $data = array(
            'title' => 'Detail Aktivitas Penilaian PKL',
            'page_title' => 'Detail Aktivitas Penilaian PKL',
            'breadcrumb' => array(
                array('label' => 'PKL', 'url' => '#'),
                array('label' => 'Aktivitas Penilaian', 'url' => site_url('Pkl_aktivitas_penilaian')),
                array('label' => 'Detail', 'active' => true)
            ),
            'sidebar_menu' => generate_sidebar_menu(get_user_role()),
            'pengajuan' => $pengajuan,
            'penilaian_list' => $penilaian_list,
            'nilai_rekap' => $nilai_rekap,
            'aspek_list' => $aspek_list,
            'tahun_aktif' => $tahun_aktif
        );

        $data['content'] = $this->load->view('sekprodi/pkl_aktivitas_penilaian/detail', $data, true);
        $this->load->view('layouts/main', $data);
    }

    /**
     * Cetak Berita Acara Sidang PKL (PDF)
     */
    public function cetak_berita_acara($pengajuan_id) {
        $tahun_aktif = $this->Tahun_akademik_model->get_aktif();
        if (!$tahun_aktif) {
            $this->session->set_flashdata('error', 'Tidak ada tahun akademik aktif.');
            redirect('Pkl_aktivitas_penilaian');
        }

        // Ambil pengajuan PKL
        $pengajuan = $this->Pkl_pengajuan_model->get_by_id($pengajuan_id);
        if (!$pengajuan) {
            $this->session->set_flashdata('error', 'Data pengajuan tidak ditemukan.');
            redirect('Pkl_aktivitas_penilaian');
        }

        // Ambil data mahasiswa
        $this->load->model('Mahasiswa_model');
        $mahasiswa = $this->Mahasiswa_model->get_by_id($pengajuan->id_mahasiswa);

        // Ambil jadwal sidang PKL
        $this->load->model('Jadwal_pkl_model');
        $jadwal_list = $this->Jadwal_pkl_model->get_by_mahasiswa($pengajuan->id_mahasiswa, $tahun_aktif->id);
        $jadwal_sidang = !empty($jadwal_list) ? $jadwal_list[0] : null;

        // Ambil semua penilaian untuk mahasiswa ini
        $penilaian_list = $this->Pkl_penilaian_model->get_by_mahasiswa($pengajuan->id_mahasiswa, $tahun_aktif->id);
        
        // Kelompokkan penilaian berdasarkan tipe penilai
        $penilaian_by_tipe = array(
            'pembimbing_dosen' => null,
            'pembimbing_lapangan' => null,
            'sekprodi' => null,
            'penguji' => null
        );

        $this->load->model('Dosen_model');
        foreach ($penilaian_list as $penilaian) {
            // Ambil detail penilaian per item
            $penilaian->details = $this->Pkl_penilaian_detail_model->get_by_penilaian($penilaian->id);
            
            // Ambil info penilai
            if ($penilaian->tipe_penilai === 'pembimbing_dosen' || $penilaian->tipe_penilai === 'penguji') {
                $dosen = $this->Dosen_model->get_by_id($penilaian->id_penilai);
                $penilaian->penilai_nama = $dosen ? $dosen->nama_dosen : '-';
                $penilaian->penilai_nidn = $dosen ? $dosen->nidn : '-';
            } else {
                $penilaian->penilai_nama = ucfirst(str_replace('_', ' ', $penilaian->tipe_penilai));
                $penilaian->penilai_nidn = '-';
            }

            // Simpan berdasarkan tipe
            if (isset($penilaian_by_tipe[$penilaian->tipe_penilai])) {
                $penilaian_by_tipe[$penilaian->tipe_penilai] = $penilaian;
            }
        }

        // Ambil nilai rekap
        $nilai_rekap = $this->Pkl_nilai_rekap_model->get_by_mahasiswa($pengajuan->id_mahasiswa, $tahun_aktif->id);

        // Ambil aspek penilaian yang ditugaskan untuk penguji saja (untuk berita acara)
        $aspek_list = $this->Pkl_aspek_penilaian_model->get_by_ditunjukkan_untuk('penguji', true);
        foreach ($aspek_list as &$aspek) {
            $aspek->items = $this->Pkl_item_penilaian_model->get_by_aspek($aspek->id, true);
        }
        unset($aspek);

        // Ambil data dosen pembimbing
        $dosen_pembimbing = null;
        if ($pengajuan->id_pembimbing) {
            $dosen_pembimbing = $this->Dosen_model->get_by_id($pengajuan->id_pembimbing);
        }

        // Ambil data dosen penguji dari jadwal atau penilaian
        $dosen_penguji = null;
        if ($jadwal_sidang && $jadwal_sidang->id_penguji) {
            $dosen_penguji = $this->Dosen_model->get_by_id($jadwal_sidang->id_penguji);
        } elseif ($penilaian_by_tipe['penguji'] && $penilaian_by_tipe['penguji']->id_penilai) {
            $dosen_penguji = $this->Dosen_model->get_by_id($penilaian_by_tipe['penguji']->id_penilai);
        }

        // Ambil catatan revisi dari penguji
        $catatan_revisi = null;
        if ($penilaian_by_tipe['penguji']) {
            $penilaian_penguji = $penilaian_by_tipe['penguji'];
            // Cek catatan dengan lebih teliti - bisa berupa string kosong atau null
            if (isset($penilaian_penguji->catatan)) {
                $catatan_text = trim($penilaian_penguji->catatan);
                if (!empty($catatan_text) && $catatan_text !== '' && $catatan_text !== null) {
                    $catatan_revisi = $penilaian_penguji;
                }
            }
        }

        // Load library PDF dan helper
        $this->load->library('Pdf');
        $this->load->helper('pkl');
        $this->load->helper('app');
        
        $data = array(
            'mahasiswa' => $mahasiswa,
            'pengajuan' => $pengajuan,
            'jadwal_sidang' => $jadwal_sidang,
            'penilaian_list' => $penilaian_list, // Tambahkan untuk perhitungan nilai akhir
            'penilaian_by_tipe' => $penilaian_by_tipe,
            'nilai_rekap' => $nilai_rekap,
            'aspek_list' => $aspek_list,
            'dosen_pembimbing' => $dosen_pembimbing,
            'dosen_penguji' => $dosen_penguji,
            'catatan_revisi' => $catatan_revisi,
            'tahun_aktif' => $tahun_aktif
        );
        
        // Generate HTML untuk PDF
        $html = $this->load->view('sekprodi/pkl_aktivitas_penilaian/cetak_berita_acara', $data, true);
        
        // Load HTML ke PDF library
        $this->pdf->load_html($html);
        $this->pdf->set_paper('A4', 'portrait');
        
        // Generate filename
        $filename = 'Berita_Acara_Sidang_PKL_' . $mahasiswa->nim . '_' . date('Ymd') . '.pdf';
        
        // Stream PDF
        $this->pdf->stream($filename);
    }

    /**
     * Export Excel - Data Mahasiswa PKL dengan rincian lengkap
     */
    public function export_excel() {
        $tahun_aktif = $this->Tahun_akademik_model->get_aktif();
        if (!$tahun_aktif) {
            $this->session->set_flashdata('error', 'Tidak ada tahun akademik aktif.');
            redirect('Pkl_aktivitas_penilaian');
        }

        // Ambil semua mahasiswa PKL yang sudah memiliki penilaian
        $pengajuan_list = $this->Pkl_pengajuan_model->get_all($tahun_aktif->id, 'disetujui');
        
        $data_export = array();
        foreach ($pengajuan_list as $pengajuan) {
            if ($pengajuan->status_acc_judul === 'acc') {
                // Ambil semua penilaian untuk mahasiswa ini
                $penilaian_list = $this->Pkl_penilaian_model->get_by_mahasiswa($pengajuan->id_mahasiswa, $tahun_aktif->id);
                
                if (!empty($penilaian_list)) {
                    // Ambil nilai rekap
                    $nilai_rekap = $this->Pkl_nilai_rekap_model->get_by_mahasiswa($pengajuan->id_mahasiswa, $tahun_aktif->id);
                    
                    // Hitung nilai akhir dan grade
                    $nilai_akhir = 0;
                    $grade = null;
                    
                    if ($nilai_rekap && $nilai_rekap->nilai_akhir > 0) {
                        $nilai_akhir = $nilai_rekap->nilai_akhir;
                        $grade = $nilai_rekap->nilai_huruf;
                    } else {
                        // Hitung dari penilaian yang ada
                        $p1 = 0; $p2 = 0; $p3 = 0; $p4 = 0; $p5 = 0; $p6 = 0;
                        foreach ($penilaian_list as $pen) {
                            $p1 = max($p1, floatval($pen->nilai_p1));
                            $p2 = max($p2, floatval($pen->nilai_p2));
                            $p3 = max($p3, floatval($pen->nilai_p3));
                            $p4 = max($p4, floatval($pen->nilai_p4));
                            $p5 = max($p5, floatval($pen->nilai_p5));
                            $p6 = max($p6, floatval($pen->nilai_p6));
                        }
                        if ($p1 + $p2 + $p3 + $p4 + $p5 + $p6 > 0) {
                            $nilai_akhir = hitung_nilai_akhir_pkl($p1, $p2, $p3, $p4, $p5, $p6);
                            $grade = konversi_nilai_huruf_pkl($nilai_akhir);
                        }
                    }
                    
                    // Jika grade masih null tapi nilai_akhir ada, hitung grade
                    if (!$grade && $nilai_akhir > 0) {
                        $grade = konversi_nilai_huruf_pkl($nilai_akhir);
                    }
                    
                    // Ambil tempat PKL
                    $tempat_pkl = $this->Pkl_tempat_model->get_by_mahasiswa($pengajuan->id_mahasiswa, $tahun_aktif->id);
                    
                    // Ambil dosen pembimbing
                    $dosen_pembimbing = null;
                    if ($pengajuan->id_pembimbing) {
                        $dosen_pembimbing = $this->Dosen_model->get_by_id($pengajuan->id_pembimbing);
                    }
                    
                    // Ambil jadwal sidang
                    $jadwal_list = $this->Jadwal_pkl_model->get_by_mahasiswa($pengajuan->id_mahasiswa, $tahun_aktif->id);
                    $jadwal = !empty($jadwal_list) ? $jadwal_list[0] : null;
                    
                    // Format jadwal sidang
                    $jadwal_sidang = '-';
                    if ($jadwal) {
                        $tanggal = $jadwal->tanggal ? date('d/m/Y', strtotime($jadwal->tanggal)) : '-';
                        $jam = ($jadwal->jam_mulai && $jadwal->jam_selesai) ? date('H:i', strtotime($jadwal->jam_mulai)) . ' - ' . date('H:i', strtotime($jadwal->jam_selesai)) : '-';
                        $ruangan = $jadwal->ruangan ?: '-';
                        $jadwal_sidang = $tanggal . ' ' . $jam . ' (' . $ruangan . ')';
                    }
                    
                    $data_export[] = array(
                        'nim' => $pengajuan->nim,
                        'nama_mahasiswa' => $pengajuan->nama_mahasiswa,
                        'tempat_pkl' => $tempat_pkl ? $tempat_pkl->nama_instansi : '-',
                        'pembimbing_pkl' => $tempat_pkl ? ($tempat_pkl->nama_pembimbing_lapangan ?: '-') : '-',
                        'no_pembimbing_pkl' => $tempat_pkl ? ($tempat_pkl->no_hp_pembimbing_lapangan ?: '-') : '-',
                        'dosen_pembimbing' => $dosen_pembimbing ? $dosen_pembimbing->nama_dosen : '-',
                        'jadwal_sidang' => $jadwal_sidang,
                        'nilai_akhir' => $nilai_akhir > 0 ? number_format($nilai_akhir, 2) : '-',
                        'nilai_huruf' => $grade ?: '-'
                    );
                }
            }
        }

        // Cek apakah PhpSpreadsheet tersedia
        if (class_exists('PhpOffice\PhpSpreadsheet\Spreadsheet')) {
            require_once APPPATH . 'vendor/autoload.php';
            
            $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet();
            
            // Set title
            $sheet->setTitle('Data Mahasiswa PKL');
            
            // Header
            $sheet->setCellValue('A1', 'No');
            $sheet->setCellValue('B1', 'NIM');
            $sheet->setCellValue('C1', 'Nama Mahasiswa');
            $sheet->setCellValue('D1', 'Tempat PKL');
            $sheet->setCellValue('E1', 'Pembimbing PKL');
            $sheet->setCellValue('F1', 'No Pembimbing PKL');
            $sheet->setCellValue('G1', 'Dosen Pembimbing');
            $sheet->setCellValue('H1', 'Jadwal Sidang');
            $sheet->setCellValue('I1', 'Nilai Akhir');
            $sheet->setCellValue('J1', 'Nilai Huruf');
            
            // Style header
            $sheet->getStyle('A1:J1')->getFont()->setBold(true);
            $sheet->getStyle('A1:J1')->getFill()
                ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                ->getStartColor()->setARGB('FF4472C4');
            $sheet->getStyle('A1:J1')->getFont()->getColor()->setARGB('FFFFFFFF');
            $sheet->getStyle('A1:J1')->getAlignment()
                ->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER)
                ->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER);
            
            // Data
            $row = 2;
            $no = 1;
            foreach ($data_export as $data) {
                $sheet->setCellValue('A' . $row, $no++);
                $sheet->setCellValue('B' . $row, $data['nim']);
                $sheet->setCellValue('C' . $row, $data['nama_mahasiswa']);
                $sheet->setCellValue('D' . $row, $data['tempat_pkl']);
                $sheet->setCellValue('E' . $row, $data['pembimbing_pkl']);
                $sheet->setCellValue('F' . $row, $data['no_pembimbing_pkl']);
                $sheet->setCellValue('G' . $row, $data['dosen_pembimbing']);
                $sheet->setCellValue('H' . $row, $data['jadwal_sidang']);
                $sheet->setCellValue('I' . $row, $data['nilai_akhir']);
                $sheet->setCellValue('J' . $row, $data['nilai_huruf']);
                $row++;
            }
            
            // Auto size columns
            foreach (range('A', 'J') as $col) {
                $sheet->getColumnDimension($col)->setAutoSize(true);
            }
            
            // Set borders
            $styleArray = [
                'borders' => [
                    'allBorders' => [
                        'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                    ],
                ],
            ];
            $sheet->getStyle('A1:J' . ($row - 1))->applyFromArray($styleArray);
            
            // Set filename
            $filename = 'Data_Mahasiswa_PKL_' . date('Ymd_His') . '.xlsx';
            
            // Download
            header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
            header('Content-Disposition: attachment;filename="' . $filename . '"');
            header('Cache-Control: max-age=0');
            
            $writer = \PhpOffice\PhpSpreadsheet\IOFactory::createWriter($spreadsheet, 'Xlsx');
            $writer->save('php://output');
            exit;
        } else {
            // Fallback: download CSV
            $filename = 'Data_Mahasiswa_PKL_' . date('Ymd_His') . '.csv';
            header('Content-Type: text/csv; charset=utf-8');
            header('Content-Disposition: attachment;filename="' . $filename . '"');
            header('Cache-Control: max-age=0');
            
            $output = fopen('php://output', 'w');
            
            // BOM untuk UTF-8 (agar Excel membaca dengan benar)
            fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));
            
            // Header
            fputcsv($output, array('No', 'NIM', 'Nama Mahasiswa', 'Tempat PKL', 'Pembimbing PKL', 'No Pembimbing PKL', 'Dosen Pembimbing', 'Jadwal Sidang', 'Nilai Akhir', 'Nilai Huruf'));
            
            // Data
            $no = 1;
            foreach ($data_export as $data) {
                fputcsv($output, array(
                    $no++,
                    $data['nim'],
                    $data['nama_mahasiswa'],
                    $data['tempat_pkl'],
                    $data['pembimbing_pkl'],
                    $data['no_pembimbing_pkl'],
                    $data['dosen_pembimbing'],
                    $data['jadwal_sidang'],
                    $data['nilai_akhir'],
                    $data['nilai_huruf']
                ));
            }
            
            fclose($output);
            exit;
        }
    }
}





