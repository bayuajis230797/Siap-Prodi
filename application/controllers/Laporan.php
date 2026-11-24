<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Laporan Controller
 * Mengelola laporan dan export data untuk role sekprodi
 */
class Laporan extends MY_Controller {

    public function __construct() {
        parent::__construct();
        $this->check_role('sekprodi');
        $this->load->model('Mapping_dosen_mk_model');
        $this->load->model('Jadwal_kuliah_model');
        $this->load->model('Jadwal_uts_model');
        $this->load->model('Jadwal_uas_model');
        $this->load->model('Honor_uts_model');
        $this->load->model('Honor_uas_model');
        $this->load->model('Tahun_akademik_model');
        $this->load->model('Mahasiswa_model');
        $this->load->model('Dosen_model');
        $this->load->model('Tendik_model');
    }

    /**
     * Halaman utama laporan
     */
    public function index() {
        $tahun_aktif = $this->Tahun_akademik_model->get_aktif();
        
        $data['tahun_aktif'] = $tahun_aktif;
        $data['title'] = 'Laporan';
        $data['page_title'] = 'Laporan';
        $data['breadcrumb'] = array(array('label' => 'Laporan', 'active' => true));
        $data['sidebar_menu'] = generate_sidebar_menu(get_user_role());
        $data['content'] = $this->load->view('laporan/index', $data, true);
        $this->load->view('layouts/main', $data);
    }

    /**
     * Export Mapping MK ke Excel
     */
    public function export_mapping_mk() {
        $tahun_aktif = $this->Tahun_akademik_model->get_aktif();
        if (!$tahun_aktif) {
            $this->session->set_flashdata('error', 'Tahun akademik aktif tidak ditemukan.');
            redirect('Laporan');
        }

        // Cek apakah PhpSpreadsheet tersedia
        if (!$this->check_phpspreadsheet()) {
            // Fallback: Export CSV
            $this->export_mapping_mk_csv($tahun_aktif);
            return;
        }

        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Mapping MK');

        // Kop tabel dengan warna merah maroon
        $sheet->mergeCells('A1:I1');
        $sheet->setCellValue('A1', 'LAPORAN MAPPING MATA KULIAH - DOSEN');
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14)->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color('FFFFFFFF'));
        $sheet->getStyle('A1')->getFill()
            ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
            ->getStartColor()->setARGB('FF800000'); // Merah maroon
        $sheet->getStyle('A1')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER)
            ->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER);
        $sheet->getRowDimension('1')->setRowHeight(25);

        $sheet->setCellValue('A2', 'Tahun Akademik: ' . $tahun_aktif->tahun_akademik . ' ' . ucfirst($tahun_aktif->semester));
        $sheet->setCellValue('A3', 'Tanggal Export: ' . date('d/m/Y H:i:s'));

        // Header tabel
        $sheet->setCellValue('A5', 'No');
        $sheet->setCellValue('B5', 'Kode MK');
        $sheet->setCellValue('C5', 'Nama Mata Kuliah');
        $sheet->setCellValue('D5', 'SKS');
        $sheet->setCellValue('E5', 'Semester');
        $sheet->setCellValue('F5', 'Kelas');
        $sheet->setCellValue('G5', 'NIDN');
        $sheet->setCellValue('H5', 'Nama Dosen');
        $sheet->setCellValue('I5', 'Tahun Akademik');

        // Style header tabel
        $sheet->getStyle('A5:I5')->getFont()->setBold(true);
        $sheet->getStyle('A5:I5')->getFill()
            ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
            ->getStartColor()->setARGB('FF800000'); // Merah maroon
        $sheet->getStyle('A5:I5')->getFont()->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color('FFFFFFFF')); // Putih
        $sheet->getStyle('A5:I5')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
        $sheet->getRowDimension('5')->setRowHeight(20);

        // Data
        $mapping_list = $this->Mapping_dosen_mk_model->get_all($tahun_aktif->id, 10000, 0);
        $row = 6;
        $no = 1;

        foreach ($mapping_list as $mapping) {
            $sheet->setCellValue('A' . $row, $no);
            $sheet->setCellValue('B' . $row, $mapping->kode_mk ?? '');
            $sheet->setCellValue('C' . $row, $mapping->nama_mk ?? '');
            $sheet->setCellValue('D' . $row, $mapping->sks ?? '');
            $sheet->setCellValue('E' . $row, $mapping->semester ?? '');
            $sheet->setCellValue('F' . $row, $mapping->kelas ?? '');
            $sheet->setCellValue('G' . $row, $mapping->nidn ?? '');
            $sheet->setCellValue('H' . $row, $mapping->nama_dosen ?? '');
            $sheet->setCellValue('I' . $row, $tahun_aktif->tahun_akademik . ' ' . ucfirst($tahun_aktif->semester));
            $row++;
            $no++;
        }

        // Auto size columns
        foreach (range('A', 'I') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        // Border
        $this->set_border($sheet, 'A5:I' . ($row - 1));

        // Download
        $filename = 'Laporan_Mapping_MK_' . date('YmdHis') . '.xlsx';
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $filename . '"');
        header('Cache-Control: max-age=0');

        $writer = \PhpOffice\PhpSpreadsheet\IOFactory::createWriter($spreadsheet, 'Xlsx');
        $writer->save('php://output');
        exit;
    }

    /**
     * Export Mapping MK ke CSV (Fallback)
     */
    private function export_mapping_mk_csv($tahun_aktif) {
        $mapping_list = $this->Mapping_dosen_mk_model->get_all($tahun_aktif->id, 10000, 0);
        
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment;filename="Laporan_Mapping_MK_' . date('YmdHis') . '.csv"');
        header('Cache-Control: max-age=0');
        
        echo "\xEF\xBB\xBF";
        
        $output = fopen('php://output', 'w');
        fputcsv($output, array('LAPORAN MAPPING MATA KULIAH - DOSEN'));
        fputcsv($output, array('Tahun Akademik: ' . $tahun_aktif->tahun_akademik . ' ' . ucfirst($tahun_aktif->semester)));
        fputcsv($output, array('Tanggal Export: ' . date('d/m/Y H:i:s')));
        fputcsv($output, array());
        fputcsv($output, array('No', 'Kode MK', 'Nama Mata Kuliah', 'SKS', 'Semester', 'Kelas', 'NIDN', 'Nama Dosen', 'Tahun Akademik'));
        
        $no = 1;
        foreach ($mapping_list as $mapping) {
            fputcsv($output, array(
                $no++,
                $mapping->kode_mk ?? '',
                $mapping->nama_mk ?? '',
                $mapping->sks ?? '',
                $mapping->semester ?? '',
                $mapping->kelas ?? '',
                $mapping->nidn ?? '',
                $mapping->nama_dosen ?? '',
                $tahun_aktif->tahun_akademik . ' ' . ucfirst($tahun_aktif->semester)
            ));
        }
        
        fclose($output);
        exit;
    }

    /**
     * Export Jadwal Kuliah ke CSV (Fallback)
     */
    private function export_jadwal_kuliah_csv($tahun_aktif) {
        $jadwal_list = $this->Jadwal_kuliah_model->get_all($tahun_aktif->id, 10000, 0);
        
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment;filename="Laporan_Jadwal_Kuliah_' . date('YmdHis') . '.csv"');
        header('Cache-Control: max-age=0');
        
        echo "\xEF\xBB\xBF";
        
        $output = fopen('php://output', 'w');
        fputcsv($output, array('LAPORAN JADWAL KULIAH'));
        fputcsv($output, array('Tahun Akademik: ' . $tahun_aktif->tahun_akademik . ' ' . ucfirst($tahun_aktif->semester)));
        fputcsv($output, array('Tanggal Export: ' . date('d/m/Y H:i:s')));
        fputcsv($output, array());
        fputcsv($output, array('No', 'Kode MK', 'Nama Mata Kuliah', 'SKS', 'Kelas', 'Hari', 'Jam Mulai', 'Jam Selesai', 'Ruang Kelas', 'NIDN', 'Nama Dosen'));
        
        $no = 1;
        foreach ($jadwal_list as $jadwal) {
            fputcsv($output, array(
                $no++,
                $jadwal->kode_mk ?? '',
                $jadwal->nama_mk ?? '',
                $jadwal->sks ?? '',
                $jadwal->kelas ?? '',
                ucfirst($jadwal->hari ?? ''),
                $jadwal->jam_mulai ?? '',
                $jadwal->jam_selesai ?? '',
                ($jadwal->kode_ruang ?? '') . ' - ' . ($jadwal->nama_ruang ?? ''),
                $jadwal->nidn ?? '',
                $jadwal->nama_dosen ?? ''
            ));
        }
        
        fclose($output);
        exit;
    }

    /**
     * Export Jadwal UTS ke CSV (Fallback)
     */
    private function export_jadwal_uts_csv($tahun_aktif) {
        $jadwal_list = $this->Jadwal_uts_model->get_all($tahun_aktif->id, 10000, 0);
        
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment;filename="Laporan_Jadwal_UTS_' . date('YmdHis') . '.csv"');
        header('Cache-Control: max-age=0');
        
        echo "\xEF\xBB\xBF";
        
        $output = fopen('php://output', 'w');
        fputcsv($output, array('LAPORAN JADWAL UTS'));
        fputcsv($output, array('Tahun Akademik: ' . $tahun_aktif->tahun_akademik . ' ' . ucfirst($tahun_aktif->semester)));
        fputcsv($output, array('Tanggal Export: ' . date('d/m/Y H:i:s')));
        fputcsv($output, array());
        fputcsv($output, array('No', 'Kode MK', 'Nama Mata Kuliah', 'Kelas', 'Tanggal', 'Hari', 'Waktu', 'Ruang', 'Sesi', 'Pengawas'));
        
        $no = 1;
        foreach ($jadwal_list as $jadwal) {
            fputcsv($output, array(
                $no++,
                $jadwal->kode_mk ?? '',
                $jadwal->nama_mk ?? '',
                $jadwal->kelas ?? '',
                $jadwal->tanggal ? date('d/m/Y', strtotime($jadwal->tanggal)) : '',
                hari_dari_tanggal($jadwal->tanggal ?? null),
                jadwal_ujian_waktu_label($jadwal->jam_mulai ?? null, $jadwal->jam_selesai ?? null),
                $jadwal->nama_ruang ?? '',
                $jadwal->sesi ?? '',
                jadwal_ujian_pengawas_export_str($jadwal->pengawas ?? array())
            ));
        }
        
        fclose($output);
        exit;
    }

    /**
     * Export Jadwal UAS ke CSV (Fallback)
     */
    private function export_jadwal_uas_csv($tahun_aktif) {
        $jadwal_list = $this->Jadwal_uas_model->get_all($tahun_aktif->id, 10000, 0);
        
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment;filename="Laporan_Jadwal_UAS_' . date('YmdHis') . '.csv"');
        header('Cache-Control: max-age=0');
        
        echo "\xEF\xBB\xBF";
        
        $output = fopen('php://output', 'w');
        fputcsv($output, array('LAPORAN JADWAL UAS'));
        fputcsv($output, array('Tahun Akademik: ' . $tahun_aktif->tahun_akademik . ' ' . ucfirst($tahun_aktif->semester)));
        fputcsv($output, array('Tanggal Export: ' . date('d/m/Y H:i:s')));
        fputcsv($output, array());
        fputcsv($output, array('No', 'Kode MK', 'Nama Mata Kuliah', 'Kelas', 'Tanggal', 'Hari', 'Waktu', 'Ruang', 'Sesi', 'Pengawas'));
        
        $no = 1;
        foreach ($jadwal_list as $jadwal) {
            fputcsv($output, array(
                $no++,
                $jadwal->kode_mk ?? '',
                $jadwal->nama_mk ?? '',
                $jadwal->kelas ?? '',
                $jadwal->tanggal ? date('d/m/Y', strtotime($jadwal->tanggal)) : '',
                hari_dari_tanggal($jadwal->tanggal ?? null),
                jadwal_ujian_waktu_label($jadwal->jam_mulai ?? null, $jadwal->jam_selesai ?? null),
                $jadwal->nama_ruang ?? '',
                $jadwal->sesi ?? '',
                jadwal_ujian_pengawas_export_str($jadwal->pengawas ?? array())
            ));
        }
        
        fclose($output);
        exit;
    }

    /**
     * Export Honor UTS ke CSV (Fallback)
     */
    private function export_honor_uts_csv($tahun_aktif) {
        $this->db->select('hu.*, 
                          jut.kelas,
                          mk.kode_mk, mk.nama_mk,
                          d.nidn, d.nama_dosen,
                          t.nipy, t.nama_tendik');
        $this->db->from('honor_uts hu');
        $this->db->join('jadwal_uts jut', 'jut.id = hu.id_jadwal_uts', 'left');
        $this->db->join('mapping_dosen_mk mdm', 'mdm.id = jut.id_mapping_dosen_mk', 'left');
        $this->db->join('mata_kuliah_aktif mka', 'mka.id = mdm.id_mata_kuliah_aktif', 'left');
        $this->db->join('kurikulum_detail kd', 'kd.id = mka.id_kurikulum_detail', 'left');
        $this->db->join('mata_kuliah mk', 'mk.id = kd.id_mata_kuliah', 'left');
        $this->db->join('dosen d', 'd.id = hu.id_dosen', 'left');
        $this->db->join('tendik t', 't.id = hu.id_tendik', 'left');
        $this->db->where('hu.id_tahun_akademik', $tahun_aktif->id);
        $this->db->order_by('hu.jenis_honor', 'ASC');
        $this->db->order_by('d.nama_dosen', 'ASC');
        $this->db->order_by('t.nama_tendik', 'ASC');
        $honor_list = $this->db->get()->result();
        
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment;filename="Laporan_Honor_UTS_' . date('YmdHis') . '.csv"');
        header('Cache-Control: max-age=0');
        
        echo "\xEF\xBB\xBF";
        
        $output = fopen('php://output', 'w');
        fputcsv($output, array('LAPORAN HONOR UTS'));
        fputcsv($output, array('Tahun Akademik: ' . $tahun_aktif->tahun_akademik . ' ' . ucfirst($tahun_aktif->semester)));
        fputcsv($output, array('Tanggal Export: ' . date('d/m/Y H:i:s')));
        fputcsv($output, array());
        fputcsv($output, array('No', 'NIDN/NIPY', 'Nama', 'Jenis Honor', 'Kode MK', 'Nama Mata Kuliah', 'Kelas', 'Jumlah Item', 'Besaran per Item', 'Total Honor', 'Keterangan'));
        
        $no = 1;
        foreach ($honor_list as $honor) {
            $nama = '';
            $nidn_nipy = '';
            if ($honor->id_dosen) {
                $nama = $honor->nama_dosen ?? '';
                $nidn_nipy = $honor->nidn ?? '';
            } elseif ($honor->id_tendik) {
                $nama = $honor->nama_tendik ?? '';
                $nidn_nipy = $honor->nipy ?? '';
            }
            
            $jenis_honor = '';
            switch ($honor->jenis_honor) {
                case 'pembuatan_soal':
                    $jenis_honor = 'Pembuatan Soal';
                    break;
                case 'koreksi':
                    $jenis_honor = 'Koreksi';
                    break;
                case 'pengawas':
                    $jenis_honor = 'Pengawas';
                    break;
            }
            
            fputcsv($output, array(
                $no++,
                $nidn_nipy,
                $nama,
                $jenis_honor,
                $honor->kode_mk ?? '',
                $honor->nama_mk ?? '',
                $honor->kelas ?? '',
                $honor->jumlah_item ?? 0,
                $honor->besaran_per_item ?? 0,
                $honor->total_honor ?? 0,
                $honor->keterangan ?? ''
            ));
        }
        
        fclose($output);
        exit;
    }

    /**
     * Export Honor UAS ke CSV (Fallback)
     */
    private function export_honor_uas_csv($tahun_aktif) {
        $this->db->select('hua.*, 
                          jua.kelas,
                          mk.kode_mk, mk.nama_mk,
                          d.nidn, d.nama_dosen,
                          t.nipy, t.nama_tendik');
        $this->db->from('honor_uas hua');
        $this->db->join('jadwal_uas jua', 'jua.id = hua.id_jadwal_uas', 'left');
        $this->db->join('mapping_dosen_mk mdm', 'mdm.id = jua.id_mapping_dosen_mk', 'left');
        $this->db->join('mata_kuliah_aktif mka', 'mka.id = mdm.id_mata_kuliah_aktif', 'left');
        $this->db->join('kurikulum_detail kd', 'kd.id = mka.id_kurikulum_detail', 'left');
        $this->db->join('mata_kuliah mk', 'mk.id = kd.id_mata_kuliah', 'left');
        $this->db->join('dosen d', 'd.id = hua.id_dosen', 'left');
        $this->db->join('tendik t', 't.id = hua.id_tendik', 'left');
        $this->db->where('hua.id_tahun_akademik', $tahun_aktif->id);
        $this->db->order_by('hua.jenis_honor', 'ASC');
        $this->db->order_by('d.nama_dosen', 'ASC');
        $this->db->order_by('t.nama_tendik', 'ASC');
        $honor_list = $this->db->get()->result();
        
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment;filename="Laporan_Honor_UAS_' . date('YmdHis') . '.csv"');
        header('Cache-Control: max-age=0');
        
        echo "\xEF\xBB\xBF";
        
        $output = fopen('php://output', 'w');
        fputcsv($output, array('LAPORAN HONOR UAS'));
        fputcsv($output, array('Tahun Akademik: ' . $tahun_aktif->tahun_akademik . ' ' . ucfirst($tahun_aktif->semester)));
        fputcsv($output, array('Tanggal Export: ' . date('d/m/Y H:i:s')));
        fputcsv($output, array());
        fputcsv($output, array('No', 'NIDN/NIPY', 'Nama', 'Jenis Honor', 'Kode MK', 'Nama Mata Kuliah', 'Kelas', 'Jumlah Item', 'Besaran per Item', 'Total Honor', 'Keterangan'));
        
        $no = 1;
        foreach ($honor_list as $honor) {
            $nama = '';
            $nidn_nipy = '';
            if ($honor->id_dosen) {
                $nama = $honor->nama_dosen ?? '';
                $nidn_nipy = $honor->nidn ?? '';
            } elseif ($honor->id_tendik) {
                $nama = $honor->nama_tendik ?? '';
                $nidn_nipy = $honor->nipy ?? '';
            }
            
            $jenis_honor = '';
            switch ($honor->jenis_honor) {
                case 'pembuatan_soal':
                    $jenis_honor = 'Pembuatan Soal';
                    break;
                case 'koreksi':
                    $jenis_honor = 'Koreksi';
                    break;
                case 'pengawas':
                    $jenis_honor = 'Pengawas';
                    break;
            }
            
            fputcsv($output, array(
                $no++,
                $nidn_nipy,
                $nama,
                $jenis_honor,
                $honor->kode_mk ?? '',
                $honor->nama_mk ?? '',
                $honor->kelas ?? '',
                $honor->jumlah_item ?? 0,
                $honor->besaran_per_item ?? 0,
                $honor->total_honor ?? 0,
                $honor->keterangan ?? ''
            ));
        }
        
        fclose($output);
        exit;
    }

    /**
     * Export Rekap Berita Acara ke CSV (Fallback)
     */
    private function export_rekap_berita_acara_csv($tahun_aktif) {
        // Data UTS
        $this->db->select('juka.*, 
                          jut.tanggal, jut.kelas,
                          mk.kode_mk, mk.nama_mk,
                          m.nim, m.nama_mahasiswa');
        $this->db->from('jadwal_uts_kehadiran juka');
        $this->db->join('jadwal_uts jut', 'jut.id = juka.id_jadwal_uts', 'left');
        $this->db->join('mapping_dosen_mk mdm', 'mdm.id = jut.id_mapping_dosen_mk', 'left');
        $this->db->join('mata_kuliah_aktif mka', 'mka.id = mdm.id_mata_kuliah_aktif', 'left');
        $this->db->join('kurikulum_detail kd', 'kd.id = mka.id_kurikulum_detail', 'left');
        $this->db->join('mata_kuliah mk', 'mk.id = kd.id_mata_kuliah', 'left');
        $this->db->join('mahasiswa m', 'm.id = juka.id_mahasiswa', 'left');
        $this->db->where('jut.id_tahun_akademik', $tahun_aktif->id);
        $this->db->where_in('juka.status_kehadiran', array('tidak_hadir', 'izin', 'sakit'));
        $this->db->order_by('jut.tanggal', 'ASC');
        $this->db->order_by('mk.kode_mk', 'ASC');
        $this->db->order_by('m.nim', 'ASC');
        $kehadiran_uts = $this->db->get()->result();

        // Data UAS
        $this->db->select('juaka.*, 
                          jua.tanggal, jua.kelas,
                          mk.kode_mk, mk.nama_mk,
                          m.nim, m.nama_mahasiswa');
        $this->db->from('jadwal_uas_kehadiran juaka');
        $this->db->join('jadwal_uas jua', 'jua.id = juaka.id_jadwal_uas', 'left');
        $this->db->join('mapping_dosen_mk mdm', 'mdm.id = jua.id_mapping_dosen_mk', 'left');
        $this->db->join('mata_kuliah_aktif mka', 'mka.id = mdm.id_mata_kuliah_aktif', 'left');
        $this->db->join('kurikulum_detail kd', 'kd.id = mka.id_kurikulum_detail', 'left');
        $this->db->join('mata_kuliah mk', 'mk.id = kd.id_mata_kuliah', 'left');
        $this->db->join('mahasiswa m', 'm.id = juaka.id_mahasiswa', 'left');
        $this->db->where('jua.id_tahun_akademik', $tahun_aktif->id);
        $this->db->where_in('juaka.status_kehadiran', array('tidak_hadir', 'izin', 'sakit'));
        $this->db->order_by('jua.tanggal', 'ASC');
        $this->db->order_by('mk.kode_mk', 'ASC');
        $this->db->order_by('m.nim', 'ASC');
        $kehadiran_uas = $this->db->get()->result();

        $all_kehadiran = array_merge($kehadiran_uts, $kehadiran_uas);
        
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment;filename="Laporan_Rekap_Berita_Acara_' . date('YmdHis') . '.csv"');
        header('Cache-Control: max-age=0');
        
        echo "\xEF\xBB\xBF";
        
        $output = fopen('php://output', 'w');
        fputcsv($output, array('REKAP BERITA ACARA - DAFTAR MAHASISWA TIDAK HADIR'));
        fputcsv($output, array('Tahun Akademik: ' . $tahun_aktif->tahun_akademik . ' ' . ucfirst($tahun_aktif->semester)));
        fputcsv($output, array('Tanggal Export: ' . date('d/m/Y H:i:s')));
        fputcsv($output, array());
        fputcsv($output, array('No', 'Tanggal', 'Kode MK', 'Nama Mata Kuliah', 'Kelas', 'NIM', 'Nama Mahasiswa', 'Status Kehadiran', 'Keterangan'));
        
        $no = 1;
        foreach ($all_kehadiran as $kehadiran) {
            $status = '';
            switch ($kehadiran->status_kehadiran) {
                case 'tidak_hadir':
                    $status = 'Tidak Hadir';
                    break;
                case 'izin':
                    $status = 'Izin';
                    break;
                case 'sakit':
                    $status = 'Sakit';
                    break;
            }
            
            fputcsv($output, array(
                $no++,
                $kehadiran->tanggal ? date('d/m/Y', strtotime($kehadiran->tanggal)) : '',
                $kehadiran->kode_mk ?? '',
                $kehadiran->nama_mk ?? '',
                $kehadiran->kelas ?? '',
                $kehadiran->nim ?? '',
                $kehadiran->nama_mahasiswa ?? '',
                $status,
                $kehadiran->keterangan ?? ''
            ));
        }
        
        fclose($output);
        exit;
    }

    /**
     * Export Mahasiswa Per Kelas ke CSV (Fallback)
     */
    private function export_mahasiswa_per_kelas_csv($tahun_aktif) {
        $this->db->select('kelas');
        $this->db->from('mahasiswa');
        $this->db->where('status', 'aktif');
        $this->db->where('kelas IS NOT NULL');
        $this->db->where('kelas !=', '');
        $this->db->distinct();
        $this->db->order_by('kelas', 'ASC');
        $kelas_list = $this->db->get()->result();
        
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment;filename="Laporan_Mahasiswa_Per_Kelas_' . date('YmdHis') . '.csv"');
        header('Cache-Control: max-age=0');
        
        echo "\xEF\xBB\xBF";
        
        $output = fopen('php://output', 'w');
        fputcsv($output, array('LAPORAN MAHASISWA PER KELAS'));
        fputcsv($output, array('Tahun Akademik: ' . $tahun_aktif->tahun_akademik . ' ' . ucfirst($tahun_aktif->semester)));
        fputcsv($output, array('Tanggal Export: ' . date('d/m/Y H:i:s')));
        fputcsv($output, array());
        
        foreach ($kelas_list as $kelas_item) {
            $kelas = $kelas_item->kelas;
            fputcsv($output, array('KELAS: ' . $kelas));
            fputcsv($output, array('No', 'NIM', 'Nama Mahasiswa', 'Angkatan', 'Semester', 'Status'));
            
            $this->db->select('m.*');
            $this->db->from('mahasiswa m');
            $this->db->where('m.kelas', $kelas);
            $this->db->where('m.status', 'aktif');
            $this->db->order_by('m.nim', 'ASC');
            $mahasiswa_list = $this->db->get()->result();
            
            $no = 1;
            foreach ($mahasiswa_list as $mhs) {
                fputcsv($output, array(
                    $no++,
                    $mhs->nim ?? '',
                    $mhs->nama_mahasiswa ?? '',
                    $mhs->angkatan ?? '',
                    $mhs->semester_aktif ?? '',
                    ucfirst($mhs->status ?? '')
                ));
            }
            
            fputcsv($output, array()); // Baris kosong antar kelas
        }
        
        fclose($output);
        exit;
    }

    /**
     * Helper: Check apakah PhpSpreadsheet tersedia
     */
    private function check_phpspreadsheet() {
        $use_phpspreadsheet = false;
        if (file_exists(APPPATH . '../vendor/autoload.php')) {
            require_once APPPATH . '../vendor/autoload.php';
            if (class_exists('PhpOffice\PhpSpreadsheet\Spreadsheet')) {
                $use_phpspreadsheet = true;
            }
        }
        return $use_phpspreadsheet;
    }

    /**
     * Helper: Set kop tabel dengan warna merah maroon
     */
    private function set_kop_tabel($sheet, $title, $col_range, $row = 1) {
        $sheet->mergeCells($col_range . $row);
        $sheet->setCellValue('A' . $row, $title);
        $sheet->getStyle('A' . $row)->getFont()->setBold(true)->setSize(14)->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color('FFFFFFFF'));
        $sheet->getStyle('A' . $row)->getFill()
            ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
            ->getStartColor()->setARGB('FF800000'); // Merah maroon
        $sheet->getStyle('A' . $row)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER)
            ->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER);
        $sheet->getRowDimension($row)->setRowHeight(25);
    }

    /**
     * Helper: Set header tabel dengan warna merah maroon
     */
    private function set_header_tabel($sheet, $headers, $start_col = 'A', $row = 5) {
        $col = $start_col;
        foreach ($headers as $header) {
            $sheet->setCellValue($col . $row, $header);
            $col++;
        }
        
        // Style header
        $end_col = chr(ord($start_col) + count($headers) - 1);
        $sheet->getStyle($start_col . $row . ':' . $end_col . $row)->getFont()->setBold(true);
        $sheet->getStyle($start_col . $row . ':' . $end_col . $row)->getFill()
            ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
            ->getStartColor()->setARGB('FF800000'); // Merah maroon
        $sheet->getStyle($start_col . $row . ':' . $end_col . $row)->getFont()->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color('FFFFFFFF')); // Putih
        $sheet->getStyle($start_col . $row . ':' . $end_col . $row)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
        $sheet->getRowDimension($row)->setRowHeight(20);
    }

    /**
     * Helper: Set border untuk range data
     */
    private function set_border($sheet, $range) {
        $sheet->getStyle($range)->applyFromArray(array(
            'borders' => array(
                'allBorders' => array(
                    'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                    'color' => array('rgb' => '000000'),
                ),
            ),
        ));
    }

    /**
     * Export Jadwal Kuliah ke Excel
     */
    public function export_jadwal_kuliah() {
        $tahun_aktif = $this->Tahun_akademik_model->get_aktif();
        if (!$tahun_aktif) {
            $this->session->set_flashdata('error', 'Tahun akademik aktif tidak ditemukan.');
            redirect('Laporan');
        }

        if (!$this->check_phpspreadsheet()) {
            // Fallback: Export CSV
            $this->export_jadwal_kuliah_csv($tahun_aktif);
            return;
        }

        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Jadwal Kuliah');

        // Kop tabel
        $this->set_kop_tabel($sheet, 'LAPORAN JADWAL KULIAH', 'A1:K1', 1);
        $sheet->setCellValue('A2', 'Tahun Akademik: ' . $tahun_aktif->tahun_akademik . ' ' . ucfirst($tahun_aktif->semester));
        $sheet->setCellValue('A3', 'Tanggal Export: ' . date('d/m/Y H:i:s'));

        // Header tabel
        $this->set_header_tabel($sheet, array('No', 'Kode MK', 'Nama Mata Kuliah', 'SKS', 'Kelas', 'Hari', 'Jam Mulai', 'Jam Selesai', 'Ruang Kelas', 'NIDN', 'Nama Dosen'), 'A', 5);

        // Data - urutkan berdasarkan kelas
        $jadwal_list = $this->Jadwal_kuliah_model->get_all($tahun_aktif->id, 10000, 0);
        // Sort berdasarkan kelas
        usort($jadwal_list, function($a, $b) {
            $kelas_a = $a->kelas ?? '';
            $kelas_b = $b->kelas ?? '';
            return strcmp($kelas_a, $kelas_b);
        });
        
        $row = 6;
        $no = 1;

        foreach ($jadwal_list as $jadwal) {
            $sheet->setCellValue('A' . $row, $no);
            $sheet->setCellValue('B' . $row, $jadwal->kode_mk ?? '');
            $sheet->setCellValue('C' . $row, $jadwal->nama_mk ?? '');
            $sheet->setCellValue('D' . $row, $jadwal->sks ?? '');
            $sheet->setCellValue('E' . $row, $jadwal->kelas ?? '');
            $sheet->setCellValue('F' . $row, ucfirst($jadwal->hari ?? ''));
            $sheet->setCellValue('G' . $row, $jadwal->jam_mulai ?? '');
            $sheet->setCellValue('H' . $row, $jadwal->jam_selesai ?? '');
            $sheet->setCellValue('I' . $row, ($jadwal->kode_ruang ?? '') . ' - ' . ($jadwal->nama_ruang ?? ''));
            $sheet->setCellValue('J' . $row, $jadwal->nidn ?? '');
            $sheet->setCellValue('K' . $row, $jadwal->nama_dosen ?? '');
            $row++;
            $no++;
        }

        // Auto size columns
        foreach (range('A', 'K') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        // Border
        $this->set_border($sheet, 'A5:K' . ($row - 1));
        
        // Center alignment untuk kolom tertentu
        $sheet->getStyle('A5:A' . ($row - 1))->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('D5:D' . ($row - 1))->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('E5:E' . ($row - 1))->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

        // Download
        $filename = 'Laporan_Jadwal_Kuliah_' . date('YmdHis') . '.xlsx';
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $filename . '"');
        header('Cache-Control: max-age=0');

        $writer = \PhpOffice\PhpSpreadsheet\IOFactory::createWriter($spreadsheet, 'Xlsx');
        $writer->save('php://output');
        exit;
    }

    /**
     * Export Jadwal UTS ke Excel
     */
    public function export_jadwal_uts() {
        $tahun_aktif = $this->Tahun_akademik_model->get_aktif();
        if (!$tahun_aktif) {
            $this->session->set_flashdata('error', 'Tahun akademik aktif tidak ditemukan.');
            redirect('Laporan');
        }

        if (!$this->check_phpspreadsheet()) {
            // Fallback: Export CSV
            $this->export_jadwal_uts_csv($tahun_aktif);
            return;
        }

        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Jadwal UTS');

        // Kop tabel
        $this->set_kop_tabel($sheet, 'LAPORAN JADWAL UTS', 'A1:J1', 1);
        $sheet->setCellValue('A2', 'Tahun Akademik: ' . $tahun_aktif->tahun_akademik . ' ' . ucfirst($tahun_aktif->semester));
        $sheet->setCellValue('A3', 'Tanggal Export: ' . date('d/m/Y H:i:s'));

        // Header tabel
        $this->set_header_tabel($sheet, array('No', 'Kode MK', 'Nama Mata Kuliah', 'Kelas', 'Tanggal', 'Hari', 'Waktu', 'Ruang', 'Sesi', 'Pengawas'), 'A', 5);

        // Data - urutkan berdasarkan kelas
        $jadwal_list = $this->Jadwal_uts_model->get_all($tahun_aktif->id, 10000, 0);
        // Sort berdasarkan kelas
        usort($jadwal_list, function($a, $b) {
            $kelas_a = $a->kelas ?? '';
            $kelas_b = $b->kelas ?? '';
            return strcmp($kelas_a, $kelas_b);
        });
        
        $row = 6;
        $no = 1;

        foreach ($jadwal_list as $jadwal) {
            $sheet->setCellValue('A' . $row, $no);
            $sheet->setCellValue('B' . $row, $jadwal->kode_mk ?? '');
            $sheet->setCellValue('C' . $row, $jadwal->nama_mk ?? '');
            $sheet->setCellValue('D' . $row, $jadwal->kelas ?? '');
            $sheet->setCellValue('E' . $row, $jadwal->tanggal ? date('d/m/Y', strtotime($jadwal->tanggal)) : '');
            $sheet->setCellValue('F' . $row, hari_dari_tanggal($jadwal->tanggal ?? null));
            $sheet->setCellValue('G' . $row, jadwal_ujian_waktu_label($jadwal->jam_mulai ?? null, $jadwal->jam_selesai ?? null));
            $sheet->setCellValue('H' . $row, $jadwal->nama_ruang ?? '');
            $sheet->setCellValue('I' . $row, $jadwal->sesi ?? '');
            $sheet->setCellValue('J' . $row, jadwal_ujian_pengawas_export_str($jadwal->pengawas ?? array()));
            $row++;
            $no++;
        }

        // Auto size columns
        foreach (range('A', 'J') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        // Border
        $this->set_border($sheet, 'A5:J' . ($row - 1));
        
        // Center alignment untuk kolom tertentu
        $sheet->getStyle('A5:A' . ($row - 1))->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('D5:D' . ($row - 1))->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('F5:F' . ($row - 1))->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('I5:I' . ($row - 1))->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

        // Download
        $filename = 'Laporan_Jadwal_UTS_' . date('YmdHis') . '.xlsx';
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $filename . '"');
        header('Cache-Control: max-age=0');

        $writer = \PhpOffice\PhpSpreadsheet\IOFactory::createWriter($spreadsheet, 'Xlsx');
        $writer->save('php://output');
        exit;
    }

    /**
     * Export Jadwal UAS ke Excel
     */
    public function export_jadwal_uas() {
        $tahun_aktif = $this->Tahun_akademik_model->get_aktif();
        if (!$tahun_aktif) {
            $this->session->set_flashdata('error', 'Tahun akademik aktif tidak ditemukan.');
            redirect('Laporan');
        }

        if (!$this->check_phpspreadsheet()) {
            // Fallback: Export CSV
            $this->export_jadwal_uas_csv($tahun_aktif);
            return;
        }

        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Jadwal UAS');

        // Kop tabel
        $this->set_kop_tabel($sheet, 'LAPORAN JADWAL UAS', 'A1:J1', 1);
        $sheet->setCellValue('A2', 'Tahun Akademik: ' . $tahun_aktif->tahun_akademik . ' ' . ucfirst($tahun_aktif->semester));
        $sheet->setCellValue('A3', 'Tanggal Export: ' . date('d/m/Y H:i:s'));

        // Header tabel
        $this->set_header_tabel($sheet, array('No', 'Kode MK', 'Nama Mata Kuliah', 'Kelas', 'Tanggal', 'Hari', 'Waktu', 'Ruang', 'Sesi', 'Pengawas'), 'A', 5);

        // Data - urutkan berdasarkan kelas
        $jadwal_list = $this->Jadwal_uas_model->get_all($tahun_aktif->id, 10000, 0);
        // Sort berdasarkan kelas
        usort($jadwal_list, function($a, $b) {
            $kelas_a = $a->kelas ?? '';
            $kelas_b = $b->kelas ?? '';
            return strcmp($kelas_a, $kelas_b);
        });
        
        $row = 6;
        $no = 1;

        foreach ($jadwal_list as $jadwal) {
            $sheet->setCellValue('A' . $row, $no);
            $sheet->setCellValue('B' . $row, $jadwal->kode_mk ?? '');
            $sheet->setCellValue('C' . $row, $jadwal->nama_mk ?? '');
            $sheet->setCellValue('D' . $row, $jadwal->kelas ?? '');
            $sheet->setCellValue('E' . $row, $jadwal->tanggal ? date('d/m/Y', strtotime($jadwal->tanggal)) : '');
            $sheet->setCellValue('F' . $row, hari_dari_tanggal($jadwal->tanggal ?? null));
            $sheet->setCellValue('G' . $row, jadwal_ujian_waktu_label($jadwal->jam_mulai ?? null, $jadwal->jam_selesai ?? null));
            $sheet->setCellValue('H' . $row, $jadwal->nama_ruang ?? '');
            $sheet->setCellValue('I' . $row, $jadwal->sesi ?? '');
            $sheet->setCellValue('J' . $row, jadwal_ujian_pengawas_export_str($jadwal->pengawas ?? array()));
            $row++;
            $no++;
        }

        // Auto size columns
        foreach (range('A', 'J') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        // Border
        $this->set_border($sheet, 'A5:J' . ($row - 1));
        
        // Center alignment untuk kolom tertentu
        $sheet->getStyle('A5:A' . ($row - 1))->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('D5:D' . ($row - 1))->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('F5:F' . ($row - 1))->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('I5:I' . ($row - 1))->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

        // Download
        $filename = 'Laporan_Jadwal_UAS_' . date('YmdHis') . '.xlsx';
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $filename . '"');
        header('Cache-Control: max-age=0');

        $writer = \PhpOffice\PhpSpreadsheet\IOFactory::createWriter($spreadsheet, 'Xlsx');
        $writer->save('php://output');
        exit;
    }

    /**
     * Export Honor UTS ke Excel (Format Per Dosen dengan Rincian)
     */
    public function export_honor_uts() {
        $tahun_aktif = $this->Tahun_akademik_model->get_aktif();
        if (!$tahun_aktif) {
            $this->session->set_flashdata('error', 'Tahun akademik aktif tidak ditemukan.');
            redirect('Laporan');
        }

        if (!$this->check_phpspreadsheet()) {
            // Fallback: Export CSV
            $this->export_honor_uts_csv($tahun_aktif);
            return;
        }

        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Honor UTS');

        // Kop tabel
        $this->set_kop_tabel($sheet, 'LAPORAN HONOR UTS', 'A1:H1', 1);
        $sheet->setCellValue('A2', 'Tahun Akademik: ' . $tahun_aktif->tahun_akademik . ' ' . ucfirst($tahun_aktif->semester));
        $sheet->setCellValue('A3', 'Tanggal Export: ' . date('d/m/Y H:i:s'));

        // Header tabel
        $this->set_header_tabel($sheet, array('Dosen', 'Kelas', 'Honor Soal', 'Mahasiswa', 'Honor Koreksi', 'Pengawasan', 'Honor Pengawas', 'Total Honor'), 'A', 5);

        // Ambil semua honor UTS untuk dosen
        $this->db->select('hu.*, 
                          jut.kelas, jut.tanggal, jut.jam_mulai, jut.jam_selesai, jut.sesi,
                          mk.kode_mk, mk.nama_mk,
                          d.nidn, d.nama_dosen');
        $this->db->from('honor_uts hu');
        $this->db->join('jadwal_uts jut', 'jut.id = hu.id_jadwal_uts', 'left');
        $this->db->join('mapping_dosen_mk mdm', 'mdm.id = jut.id_mapping_dosen_mk', 'left');
        $this->db->join('mata_kuliah_aktif mka', 'mka.id = mdm.id_mata_kuliah_aktif', 'left');
        $this->db->join('kurikulum_detail kd', 'kd.id = mka.id_kurikulum_detail', 'left');
        $this->db->join('mata_kuliah mk', 'mk.id = kd.id_mata_kuliah', 'left');
        $this->db->join('dosen d', 'd.id = hu.id_dosen', 'left');
        $this->db->where('hu.id_tahun_akademik', $tahun_aktif->id);
        $this->db->where('hu.id_dosen IS NOT NULL');
        $this->db->order_by('d.nama_dosen', 'ASC');
        $this->db->order_by('jut.kelas', 'ASC');
        
        $honor_list = $this->db->get()->result();
        
        // Group by dosen
        $honor_by_dosen = array();
        foreach ($honor_list as $honor) {
            $id_dosen = $honor->id_dosen;
            if (!isset($honor_by_dosen[$id_dosen])) {
                $honor_by_dosen[$id_dosen] = array(
                    'dosen' => $honor->nama_dosen ?? '',
                    'nidn' => $honor->nidn ?? '',
                    'kelas_list' => array(),
                    'honor_soal_total' => 0,
                    'honor_koreksi_total' => 0,
                    'honor_pengawas_total' => 0,
                    'pengawasan_list' => array()
                );
            }
            
            // Honor Soal
            if ($honor->jenis_honor == 'pembuatan_soal') {
                $kelas_key = ($honor->kelas ?? '') . ' - ' . ($honor->kode_mk ?? '') . ' - ' . ($honor->nama_mk ?? '');
                if (!isset($honor_by_dosen[$id_dosen]['kelas_list'][$kelas_key])) {
                    $honor_by_dosen[$id_dosen]['kelas_list'][$kelas_key] = array(
                        'kelas' => $honor->kelas ?? '',
                        'kode_mk' => $honor->kode_mk ?? '',
                        'nama_mk' => $honor->nama_mk ?? '',
                        'honor_soal' => 0,
                        'jumlah_mhs' => 0
                    );
                }
                $honor_by_dosen[$id_dosen]['kelas_list'][$kelas_key]['honor_soal'] += $honor->total_honor ?? 0;
                $honor_by_dosen[$id_dosen]['honor_soal_total'] += $honor->total_honor ?? 0;
            }
            
            // Honor Koreksi
            if ($honor->jenis_honor == 'koreksi') {
                $honor_by_dosen[$id_dosen]['honor_koreksi_total'] += $honor->total_honor ?? 0;
                // Ambil jumlah mahasiswa dari jumlah_item
                $kelas_key = ($honor->kelas ?? '') . ' - ' . ($honor->kode_mk ?? '') . ' - ' . ($honor->nama_mk ?? '');
                if (isset($honor_by_dosen[$id_dosen]['kelas_list'][$kelas_key])) {
                    $honor_by_dosen[$id_dosen]['kelas_list'][$kelas_key]['jumlah_mhs'] = $honor->jumlah_item ?? 0;
                }
            }
            
            // Honor Pengawas
            if ($honor->jenis_honor == 'pengawas') {
                $honor_by_dosen[$id_dosen]['honor_pengawas_total'] += $honor->total_honor ?? 0;
                // Ambil detail pengawasan
                $pengawasan_detail = ($honor->kode_mk ?? '') . ' - ' . ($honor->nama_mk ?? '') . ' | ' . 
                                     ($honor->kelas ?? '') . ' | ' . 
                                     ($honor->tanggal ? date('d/m/Y', strtotime($honor->tanggal)) : '') . ' | ' .
                                     'Sesi ' . ($honor->sesi ?? '') . ' | ' .
                                     ($honor->jam_mulai ?? '') . ' - ' . ($honor->jam_selesai ?? '');
                $honor_by_dosen[$id_dosen]['pengawasan_list'][] = $pengawasan_detail;
            }
        }
        
        // Ambil jumlah mahasiswa per kelas dari data mapping
        foreach ($honor_by_dosen as $id_dosen => &$data) {
            foreach ($data['kelas_list'] as $kelas_key => &$kelas_data) {
                if ($kelas_data['jumlah_mhs'] == 0) {
                    // Ambil dari mahasiswa
                    $this->db->select('COUNT(*) as jumlah');
                    $this->db->from('mahasiswa');
                    $this->db->where('kelas', $kelas_data['kelas']);
                    $this->db->where('status', 'aktif');
                    $jumlah = $this->db->get()->row();
                    $kelas_data['jumlah_mhs'] = $jumlah->jumlah ?? 0;
                }
            }
        }
        
        $row = 6;
        $no = 1;
        
        foreach ($honor_by_dosen as $id_dosen => $data) {
            $kelas_str = '';
            $honor_soal_str = '';
            $mhs_str = '';
            $honor_koreksi_str = '';
            
            foreach ($data['kelas_list'] as $kelas_data) {
                if ($kelas_str != '') $kelas_str .= "\n";
                $kelas_str .= $kelas_data['kelas'] . ' - ' . $kelas_data['kode_mk'] . ' - ' . $kelas_data['nama_mk'];
                
                if ($honor_soal_str != '') $honor_soal_str .= "\n";
                $honor_soal_str .= 'Rp ' . number_format($kelas_data['honor_soal'], 0, ',', '.');
                
                if ($mhs_str != '') $mhs_str .= "\n";
                $mhs_str .= $kelas_data['kelas'] . ' - ' . $kelas_data['kode_mk'] . ' - ' . $kelas_data['nama_mk'] . ' (' . $kelas_data['jumlah_mhs'] . ' mhs)';
            }
            
            $honor_koreksi_str = 'Rp ' . number_format($data['honor_koreksi_total'], 0, ',', '.');
            
            $pengawasan_str = implode("\n", $data['pengawasan_list']);
            if (empty($pengawasan_str)) {
                $pengawasan_str = '-';
            }
            
            $honor_pengawas_str = 'Rp ' . number_format($data['honor_pengawas_total'], 0, ',', '.');
            if ($data['honor_pengawas_total'] == 0) {
                $honor_pengawas_str = '-';
            }
            
            $total_honor = $data['honor_soal_total'] + $data['honor_koreksi_total'] + $data['honor_pengawas_total'];
            
            $sheet->setCellValue('A' . $row, $data['dosen']);
            $sheet->setCellValue('B' . $row, $kelas_str);
            $sheet->setCellValue('C' . $row, $honor_soal_str);
            $sheet->setCellValue('D' . $row, $mhs_str);
            $sheet->setCellValue('E' . $row, $honor_koreksi_str);
            $sheet->setCellValue('F' . $row, $pengawasan_str);
            $sheet->setCellValue('G' . $row, $honor_pengawas_str);
            $sheet->setCellValue('H' . $row, 'Rp ' . number_format($total_honor, 0, ',', '.'));
            
            // Format wrap text untuk kolom yang panjang
            $sheet->getStyle('B' . $row . ':F' . $row)->getAlignment()->setWrapText(true);
            $sheet->getStyle('B' . $row . ':F' . $row)->getAlignment()->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_TOP);
            
            // Format angka
            $sheet->getStyle('C' . $row . ':H' . $row)->getNumberFormat()->setFormatCode('#,##0');
            
            $row++;
            $no++;
        }

        // Auto size columns
        foreach (range('A', 'H') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }
        
        // Set width untuk kolom tertentu
        $sheet->getColumnDimension('B')->setWidth(40);
        $sheet->getColumnDimension('F')->setWidth(50);
        $sheet->getColumnDimension('A')->setWidth(30);

        // Border
        $this->set_border($sheet, 'A5:H' . ($row - 1));
        
        // Center alignment untuk kolom tertentu
        $sheet->getStyle('C5:C' . ($row - 1))->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT);
        $sheet->getStyle('E5:E' . ($row - 1))->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT);
        $sheet->getStyle('G5:G' . ($row - 1))->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT);
        $sheet->getStyle('H5:H' . ($row - 1))->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT);

        // Download
        $filename = 'Laporan_Honor_UTS_' . date('YmdHis') . '.xlsx';
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $filename . '"');
        header('Cache-Control: max-age=0');

        $writer = \PhpOffice\PhpSpreadsheet\IOFactory::createWriter($spreadsheet, 'Xlsx');
        $writer->save('php://output');
        exit;
    }

    /**
     * Export Honor UAS ke Excel (Format Per Dosen dengan Rincian)
     */
    public function export_honor_uas() {
        $tahun_aktif = $this->Tahun_akademik_model->get_aktif();
        if (!$tahun_aktif) {
            $this->session->set_flashdata('error', 'Tahun akademik aktif tidak ditemukan.');
            redirect('Laporan');
        }

        if (!$this->check_phpspreadsheet()) {
            // Fallback: Export CSV
            $this->export_honor_uas_csv($tahun_aktif);
            return;
        }

        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Honor UAS');

        // Kop tabel
        $this->set_kop_tabel($sheet, 'LAPORAN HONOR UAS', 'A1:H1', 1);
        $sheet->setCellValue('A2', 'Tahun Akademik: ' . $tahun_aktif->tahun_akademik . ' ' . ucfirst($tahun_aktif->semester));
        $sheet->setCellValue('A3', 'Tanggal Export: ' . date('d/m/Y H:i:s'));

        // Header tabel
        $this->set_header_tabel($sheet, array('Dosen', 'Kelas', 'Honor Soal', 'Mahasiswa', 'Honor Koreksi', 'Pengawasan', 'Honor Pengawas', 'Total Honor'), 'A', 5);

        // Ambil semua honor UAS untuk dosen
        $this->db->select('hua.*, 
                          jua.kelas, jua.tanggal, jua.jam_mulai, jua.jam_selesai, jua.sesi,
                          mk.kode_mk, mk.nama_mk,
                          d.nidn, d.nama_dosen');
        $this->db->from('honor_uas hua');
        $this->db->join('jadwal_uas jua', 'jua.id = hua.id_jadwal_uas', 'left');
        $this->db->join('mapping_dosen_mk mdm', 'mdm.id = jua.id_mapping_dosen_mk', 'left');
        $this->db->join('mata_kuliah_aktif mka', 'mka.id = mdm.id_mata_kuliah_aktif', 'left');
        $this->db->join('kurikulum_detail kd', 'kd.id = mka.id_kurikulum_detail', 'left');
        $this->db->join('mata_kuliah mk', 'mk.id = kd.id_mata_kuliah', 'left');
        $this->db->join('dosen d', 'd.id = hua.id_dosen', 'left');
        $this->db->where('hua.id_tahun_akademik', $tahun_aktif->id);
        $this->db->where('hua.id_dosen IS NOT NULL');
        $this->db->order_by('d.nama_dosen', 'ASC');
        $this->db->order_by('jua.kelas', 'ASC');
        
        $honor_list = $this->db->get()->result();
        
        // Group by dosen (sama seperti UTS)
        $honor_by_dosen = array();
        foreach ($honor_list as $honor) {
            $id_dosen = $honor->id_dosen;
            if (!isset($honor_by_dosen[$id_dosen])) {
                $honor_by_dosen[$id_dosen] = array(
                    'dosen' => $honor->nama_dosen ?? '',
                    'nidn' => $honor->nidn ?? '',
                    'kelas_list' => array(),
                    'honor_soal_total' => 0,
                    'honor_koreksi_total' => 0,
                    'honor_pengawas_total' => 0,
                    'pengawasan_list' => array()
                );
            }
            
            // Honor Soal
            if ($honor->jenis_honor == 'pembuatan_soal') {
                $kelas_key = ($honor->kelas ?? '') . ' - ' . ($honor->kode_mk ?? '') . ' - ' . ($honor->nama_mk ?? '');
                if (!isset($honor_by_dosen[$id_dosen]['kelas_list'][$kelas_key])) {
                    $honor_by_dosen[$id_dosen]['kelas_list'][$kelas_key] = array(
                        'kelas' => $honor->kelas ?? '',
                        'kode_mk' => $honor->kode_mk ?? '',
                        'nama_mk' => $honor->nama_mk ?? '',
                        'honor_soal' => 0,
                        'jumlah_mhs' => 0
                    );
                }
                $honor_by_dosen[$id_dosen]['kelas_list'][$kelas_key]['honor_soal'] += $honor->total_honor ?? 0;
                $honor_by_dosen[$id_dosen]['honor_soal_total'] += $honor->total_honor ?? 0;
            }
            
            // Honor Koreksi
            if ($honor->jenis_honor == 'koreksi') {
                $honor_by_dosen[$id_dosen]['honor_koreksi_total'] += $honor->total_honor ?? 0;
                $kelas_key = ($honor->kelas ?? '') . ' - ' . ($honor->kode_mk ?? '') . ' - ' . ($honor->nama_mk ?? '');
                if (isset($honor_by_dosen[$id_dosen]['kelas_list'][$kelas_key])) {
                    $honor_by_dosen[$id_dosen]['kelas_list'][$kelas_key]['jumlah_mhs'] = $honor->jumlah_item ?? 0;
                }
            }
            
            // Honor Pengawas
            if ($honor->jenis_honor == 'pengawas') {
                $honor_by_dosen[$id_dosen]['honor_pengawas_total'] += $honor->total_honor ?? 0;
                $pengawasan_detail = ($honor->kode_mk ?? '') . ' - ' . ($honor->nama_mk ?? '') . ' | ' . 
                                     ($honor->kelas ?? '') . ' | ' . 
                                     ($honor->tanggal ? date('d/m/Y', strtotime($honor->tanggal)) : '') . ' | ' .
                                     'Sesi ' . ($honor->sesi ?? '') . ' | ' .
                                     ($honor->jam_mulai ?? '') . ' - ' . ($honor->jam_selesai ?? '');
                $honor_by_dosen[$id_dosen]['pengawasan_list'][] = $pengawasan_detail;
            }
        }
        
        // Ambil jumlah mahasiswa per kelas
        foreach ($honor_by_dosen as $id_dosen => &$data) {
            foreach ($data['kelas_list'] as $kelas_key => &$kelas_data) {
                if ($kelas_data['jumlah_mhs'] == 0) {
                    $this->db->select('COUNT(*) as jumlah');
                    $this->db->from('mahasiswa');
                    $this->db->where('kelas', $kelas_data['kelas']);
                    $this->db->where('status', 'aktif');
                    $jumlah = $this->db->get()->row();
                    $kelas_data['jumlah_mhs'] = $jumlah->jumlah ?? 0;
                }
            }
        }
        
        $row = 6;
        $no = 1;
        
        foreach ($honor_by_dosen as $id_dosen => $data) {
            $kelas_str = '';
            $honor_soal_str = '';
            $mhs_str = '';
            $honor_koreksi_str = '';
            
            foreach ($data['kelas_list'] as $kelas_data) {
                if ($kelas_str != '') $kelas_str .= "\n";
                $kelas_str .= $kelas_data['kelas'] . ' - ' . $kelas_data['kode_mk'] . ' - ' . $kelas_data['nama_mk'];
                
                if ($honor_soal_str != '') $honor_soal_str .= "\n";
                $honor_soal_str .= 'Rp ' . number_format($kelas_data['honor_soal'], 0, ',', '.');
                
                if ($mhs_str != '') $mhs_str .= "\n";
                $mhs_str .= $kelas_data['kelas'] . ' - ' . $kelas_data['kode_mk'] . ' - ' . $kelas_data['nama_mk'] . ' (' . $kelas_data['jumlah_mhs'] . ' mhs)';
            }
            
            $honor_koreksi_str = 'Rp ' . number_format($data['honor_koreksi_total'], 0, ',', '.');
            
            $pengawasan_str = implode("\n", $data['pengawasan_list']);
            if (empty($pengawasan_str)) {
                $pengawasan_str = '-';
            }
            
            $honor_pengawas_str = 'Rp ' . number_format($data['honor_pengawas_total'], 0, ',', '.');
            if ($data['honor_pengawas_total'] == 0) {
                $honor_pengawas_str = '-';
            }
            
            $total_honor = $data['honor_soal_total'] + $data['honor_koreksi_total'] + $data['honor_pengawas_total'];
            
            $sheet->setCellValue('A' . $row, $data['dosen']);
            $sheet->setCellValue('B' . $row, $kelas_str);
            $sheet->setCellValue('C' . $row, $honor_soal_str);
            $sheet->setCellValue('D' . $row, $mhs_str);
            $sheet->setCellValue('E' . $row, $honor_koreksi_str);
            $sheet->setCellValue('F' . $row, $pengawasan_str);
            $sheet->setCellValue('G' . $row, $honor_pengawas_str);
            $sheet->setCellValue('H' . $row, 'Rp ' . number_format($total_honor, 0, ',', '.'));
            
            // Format wrap text
            $sheet->getStyle('B' . $row . ':F' . $row)->getAlignment()->setWrapText(true);
            $sheet->getStyle('B' . $row . ':F' . $row)->getAlignment()->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_TOP);
            
            // Format angka
            $sheet->getStyle('C' . $row . ':H' . $row)->getNumberFormat()->setFormatCode('#,##0');
            
            $row++;
            $no++;
        }

        // Auto size columns
        foreach (range('A', 'H') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }
        
        // Set width
        $sheet->getColumnDimension('B')->setWidth(40);
        $sheet->getColumnDimension('F')->setWidth(50);
        $sheet->getColumnDimension('A')->setWidth(30);

        // Border
        $this->set_border($sheet, 'A5:H' . ($row - 1));
        
        // Center alignment
        $sheet->getStyle('C5:C' . ($row - 1))->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT);
        $sheet->getStyle('E5:E' . ($row - 1))->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT);
        $sheet->getStyle('G5:G' . ($row - 1))->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT);
        $sheet->getStyle('H5:H' . ($row - 1))->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT);

        // Download
        $filename = 'Laporan_Honor_UAS_' . date('YmdHis') . '.xlsx';
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $filename . '"');
        header('Cache-Control: max-age=0');

        $writer = \PhpOffice\PhpSpreadsheet\IOFactory::createWriter($spreadsheet, 'Xlsx');
        $writer->save('php://output');
        exit;
    }

    /**
     * Export Rekap Berita Acara (daftar mahasiswa tidak hadir) ke Excel
     */
    public function export_rekap_berita_acara() {
        $tahun_aktif = $this->Tahun_akademik_model->get_aktif();
        if (!$tahun_aktif) {
            $this->session->set_flashdata('error', 'Tahun akademik aktif tidak ditemukan.');
            redirect('Laporan');
        }

        if (!$this->check_phpspreadsheet()) {
            // Fallback: Export CSV
            $this->export_rekap_berita_acara_csv($tahun_aktif);
            return;
        }

        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Rekap Berita Acara');

        // Kop tabel
        $this->set_kop_tabel($sheet, 'REKAP BERITA ACARA - DAFTAR MAHASISWA TIDAK HADIR', 'A1:I1', 1);
        $sheet->setCellValue('A2', 'Tahun Akademik: ' . $tahun_aktif->tahun_akademik . ' ' . ucfirst($tahun_aktif->semester));
        $sheet->setCellValue('A3', 'Tanggal Export: ' . date('d/m/Y H:i:s'));

        // Header tabel
        $this->set_header_tabel($sheet, array('No', 'Tanggal', 'Kode MK', 'Nama Mata Kuliah', 'Kelas', 'NIM', 'Nama Mahasiswa', 'Status Kehadiran', 'Keterangan'), 'A', 5);

        // Data UTS
        $this->db->select('juka.*, 
                          jut.tanggal, jut.kelas,
                          mk.kode_mk, mk.nama_mk,
                          m.nim, m.nama_mahasiswa');
        $this->db->from('jadwal_uts_kehadiran juka');
        $this->db->join('jadwal_uts jut', 'jut.id = juka.id_jadwal_uts', 'left');
        $this->db->join('mapping_dosen_mk mdm', 'mdm.id = jut.id_mapping_dosen_mk', 'left');
        $this->db->join('mata_kuliah_aktif mka', 'mka.id = mdm.id_mata_kuliah_aktif', 'left');
        $this->db->join('kurikulum_detail kd', 'kd.id = mka.id_kurikulum_detail', 'left');
        $this->db->join('mata_kuliah mk', 'mk.id = kd.id_mata_kuliah', 'left');
        $this->db->join('mahasiswa m', 'm.id = juka.id_mahasiswa', 'left');
        $this->db->where('jut.id_tahun_akademik', $tahun_aktif->id);
        $this->db->where_in('juka.status_kehadiran', array('tidak_hadir', 'izin', 'sakit'));
        $this->db->order_by('jut.tanggal', 'ASC');
        $this->db->order_by('mk.kode_mk', 'ASC');
        $this->db->order_by('m.nim', 'ASC');
        
        $kehadiran_uts = $this->db->get()->result();

        // Data UAS
        $this->db->select('juaka.*, 
                          jua.tanggal, jua.kelas,
                          mk.kode_mk, mk.nama_mk,
                          m.nim, m.nama_mahasiswa');
        $this->db->from('jadwal_uas_kehadiran juaka');
        $this->db->join('jadwal_uas jua', 'jua.id = juaka.id_jadwal_uas', 'left');
        $this->db->join('mapping_dosen_mk mdm', 'mdm.id = jua.id_mapping_dosen_mk', 'left');
        $this->db->join('mata_kuliah_aktif mka', 'mka.id = mdm.id_mata_kuliah_aktif', 'left');
        $this->db->join('kurikulum_detail kd', 'kd.id = mka.id_kurikulum_detail', 'left');
        $this->db->join('mata_kuliah mk', 'mk.id = kd.id_mata_kuliah', 'left');
        $this->db->join('mahasiswa m', 'm.id = juaka.id_mahasiswa', 'left');
        $this->db->where('jua.id_tahun_akademik', $tahun_aktif->id);
        $this->db->where_in('juaka.status_kehadiran', array('tidak_hadir', 'izin', 'sakit'));
        $this->db->order_by('jua.tanggal', 'ASC');
        $this->db->order_by('mk.kode_mk', 'ASC');
        $this->db->order_by('m.nim', 'ASC');
        
        $kehadiran_uas = $this->db->get()->result();

        $row = 6;
        $no = 1;

        // Gabungkan data UTS dan UAS
        $all_kehadiran = array_merge($kehadiran_uts, $kehadiran_uas);

        foreach ($all_kehadiran as $kehadiran) {
            $status = '';
            switch ($kehadiran->status_kehadiran) {
                case 'tidak_hadir':
                    $status = 'Tidak Hadir';
                    break;
                case 'izin':
                    $status = 'Izin';
                    break;
                case 'sakit':
                    $status = 'Sakit';
                    break;
            }

            $sheet->setCellValue('A' . $row, $no);
            $sheet->setCellValue('B' . $row, $kehadiran->tanggal ? date('d/m/Y', strtotime($kehadiran->tanggal)) : '');
            $sheet->setCellValue('C' . $row, $kehadiran->kode_mk ?? '');
            $sheet->setCellValue('D' . $row, $kehadiran->nama_mk ?? '');
            $sheet->setCellValue('E' . $row, $kehadiran->kelas ?? '');
            $sheet->setCellValue('F' . $row, $kehadiran->nim ?? '');
            $sheet->setCellValue('G' . $row, $kehadiran->nama_mahasiswa ?? '');
            $sheet->setCellValue('H' . $row, $status);
            $sheet->setCellValue('I' . $row, $kehadiran->keterangan ?? '');
            $row++;
            $no++;
        }

        // Auto size columns
        foreach (range('A', 'I') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        // Border
        $this->set_border($sheet, 'A5:I' . ($row - 1));
        
        // Center alignment untuk kolom tertentu
        $sheet->getStyle('A5:A' . ($row - 1))->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('E5:E' . ($row - 1))->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

        // Download
        $filename = 'Laporan_Rekap_Berita_Acara_' . date('YmdHis') . '.xlsx';
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $filename . '"');
        header('Cache-Control: max-age=0');

        $writer = \PhpOffice\PhpSpreadsheet\IOFactory::createWriter($spreadsheet, 'Xlsx');
        $writer->save('php://output');
        exit;
    }

    /**
     * Export Daftar Mahasiswa Per Kelas ke Excel
     */
    public function export_mahasiswa_per_kelas() {
        $tahun_aktif = $this->Tahun_akademik_model->get_aktif();
        if (!$tahun_aktif) {
            $this->session->set_flashdata('error', 'Tahun akademik aktif tidak ditemukan.');
            redirect('Laporan');
        }

        if (!$this->check_phpspreadsheet()) {
            // Fallback: Export CSV
            $this->export_mahasiswa_per_kelas_csv($tahun_aktif);
            return;
        }

        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Mahasiswa Per Kelas');

        // Kop tabel
        $this->set_kop_tabel($sheet, 'LAPORAN MAHASISWA PER KELAS', 'A1:F1', 1);
        $sheet->setCellValue('A2', 'Tahun Akademik: ' . $tahun_aktif->tahun_akademik . ' ' . ucfirst($tahun_aktif->semester));
        $sheet->setCellValue('A3', 'Tanggal Export: ' . date('d/m/Y H:i:s'));

        // Ambil semua kelas unik
        $this->db->select('kelas');
        $this->db->from('mahasiswa');
        $this->db->where('status', 'aktif');
        $this->db->where('kelas IS NOT NULL');
        $this->db->where('kelas !=', '');
        $this->db->distinct();
        $this->db->order_by('kelas', 'ASC');
        $kelas_list = $this->db->get()->result();

        $row = 5;

        foreach ($kelas_list as $kelas_item) {
            $kelas = $kelas_item->kelas;

            // Header untuk setiap kelas
            $sheet->setCellValue('A' . $row, 'KELAS: ' . $kelas);
            $sheet->getStyle('A' . $row)->getFont()->setBold(true)->setSize(12);
            $sheet->mergeCells('A' . $row . ':F' . $row);
            $row++;

            // Header tabel
            $this->set_header_tabel($sheet, array('No', 'NIM', 'Nama Mahasiswa', 'Angkatan', 'Semester', 'Status'), 'A', $row);
            $row++;

            // Data mahasiswa per kelas
            $this->db->select('m.*');
            $this->db->from('mahasiswa m');
            $this->db->where('m.kelas', $kelas);
            $this->db->where('m.status', 'aktif');
            $this->db->order_by('m.nim', 'ASC');
            $mahasiswa_list = $this->db->get()->result();

            $no = 1;
            foreach ($mahasiswa_list as $mhs) {
                $sheet->setCellValue('A' . $row, $no);
                $sheet->setCellValue('B' . $row, $mhs->nim ?? '');
                $sheet->setCellValue('C' . $row, $mhs->nama_mahasiswa ?? '');
                $sheet->setCellValue('D' . $row, $mhs->angkatan ?? '');
                $sheet->setCellValue('E' . $row, $mhs->semester_aktif ?? '');
                $sheet->setCellValue('F' . $row, ucfirst($mhs->status ?? ''));
                $row++;
                $no++;
            }

            // Border untuk setiap kelas
            $start_row = $row - count($mahasiswa_list) - 1;
            $this->set_border($sheet, 'A' . $start_row . ':F' . ($row - 1));
            
            // Center alignment untuk kolom tertentu
            $sheet->getStyle('A' . $start_row . ':A' . ($row - 1))->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle('D' . $start_row . ':D' . ($row - 1))->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle('E' . $start_row . ':E' . ($row - 1))->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle('F' . $start_row . ':F' . ($row - 1))->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

            // Spasi antar kelas
            $row += 2;
        }

        // Auto size columns
        foreach (range('A', 'F') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        // Download
        $filename = 'Laporan_Mahasiswa_Per_Kelas_' . date('YmdHis') . '.xlsx';
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $filename . '"');
        header('Cache-Control: max-age=0');

        $writer = \PhpOffice\PhpSpreadsheet\IOFactory::createWriter($spreadsheet, 'Xlsx');
        $writer->save('php://output');
        exit;
    }

    /**
     * Export Daftar Mahasiswa Tidak Hadir Per Kelas UTS ke Excel
     */
    public function export_mahasiswa_tidak_hadir_uts() {
        $tahun_aktif = $this->Tahun_akademik_model->get_aktif();
        if (!$tahun_aktif) {
            $this->session->set_flashdata('error', 'Tahun akademik aktif tidak ditemukan.');
            redirect('Laporan');
        }

        if (!$this->check_phpspreadsheet()) {
            // Fallback: Export CSV
            $this->export_mahasiswa_tidak_hadir_uts_csv($tahun_aktif);
            return;
        }

        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Mahasiswa Tidak Hadir UTS');

        // Kop tabel
        $this->set_kop_tabel($sheet, 'DAFTAR MAHASISWA TIDAK HADIR UTS PER KELAS', 'A1:G1', 1);
        $sheet->setCellValue('A2', 'Tahun Akademik: ' . $tahun_aktif->tahun_akademik . ' ' . ucfirst($tahun_aktif->semester));
        $sheet->setCellValue('A3', 'Tanggal Export: ' . date('d/m/Y H:i:s'));

        // Query data mahasiswa tidak hadir UTS, di-group by kelas
        // Ambil dari tabel sesi, kemudian join dengan kehadiran untuk mendapatkan status
        $this->db->select('COALESCE(juka.status_kehadiran, "tidak_hadir") as status_kehadiran,
                          jut.tanggal, jut.kelas,
                          mk.kode_mk, mk.nama_mk,
                          m.nim, m.nama_mahasiswa');
        $this->db->from('jadwal_uts_mahasiswa_sesi jms');
        $this->db->join('mahasiswa m', 'm.id = jms.id_mahasiswa', 'inner');
        $this->db->join('jadwal_uts jut', 'jut.id = jms.id_jadwal_uts', 'inner');
        $this->db->join('jadwal_uts_kehadiran juka', 'juka.id_jadwal_uts = jms.id_jadwal_uts AND juka.id_mahasiswa = m.id', 'left');
        $this->db->join('mapping_dosen_mk mdm', 'mdm.id = jut.id_mapping_dosen_mk', 'left');
        $this->db->join('mata_kuliah_aktif mka', 'mka.id = mdm.id_mata_kuliah_aktif', 'left');
        $this->db->join('kurikulum_detail kd', 'kd.id = mka.id_kurikulum_detail', 'left');
        $this->db->join('mata_kuliah mk', 'mk.id = kd.id_mata_kuliah', 'left');
        $this->db->where('jut.id_tahun_akademik', $tahun_aktif->id);
        $this->db->where('(COALESCE(juka.status_kehadiran, "tidak_hadir") = "tidak_hadir" OR juka.status_kehadiran = "izin" OR juka.status_kehadiran = "sakit")');
        $this->db->where('jut.kelas IS NOT NULL');
        $this->db->where('jut.kelas !=', '');
        $this->db->order_by('jut.kelas', 'ASC');
        $this->db->order_by('jut.tanggal', 'ASC');
        $this->db->order_by('mk.kode_mk', 'ASC');
        $this->db->order_by('m.nim', 'ASC');
        
        $kehadiran_list = $this->db->get()->result();
        
        // Jika tidak ada data dari tabel sesi, ambil dari kelas (backward compatibility)
        if (empty($kehadiran_list)) {
            $this->db->select('COALESCE(juka.status_kehadiran, "tidak_hadir") as status_kehadiran,
                              jut.tanggal, jut.kelas,
                              mk.kode_mk, mk.nama_mk,
                              m.nim, m.nama_mahasiswa');
            $this->db->from('mahasiswa m');
            $this->db->join('jadwal_uts jut', 'jut.kelas = m.kelas', 'inner');
            $this->db->join('jadwal_uts_kehadiran juka', 'juka.id_jadwal_uts = jut.id AND juka.id_mahasiswa = m.id', 'left');
            $this->db->join('mapping_dosen_mk mdm', 'mdm.id = jut.id_mapping_dosen_mk', 'left');
            $this->db->join('mata_kuliah_aktif mka', 'mka.id = mdm.id_mata_kuliah_aktif', 'left');
            $this->db->join('kurikulum_detail kd', 'kd.id = mka.id_kurikulum_detail', 'left');
            $this->db->join('mata_kuliah mk', 'mk.id = kd.id_mata_kuliah', 'left');
            $this->db->where('jut.id_tahun_akademik', $tahun_aktif->id);
            $this->db->where('m.status', 'aktif');
            $this->db->where('(COALESCE(juka.status_kehadiran, "tidak_hadir") = "tidak_hadir" OR juka.status_kehadiran = "izin" OR juka.status_kehadiran = "sakit")');
            $this->db->where('jut.kelas IS NOT NULL');
            $this->db->where('jut.kelas !=', '');
            $this->db->order_by('jut.kelas', 'ASC');
            $this->db->order_by('jut.tanggal', 'ASC');
            $this->db->order_by('mk.kode_mk', 'ASC');
            $this->db->order_by('m.nim', 'ASC');
            $kehadiran_list = $this->db->get()->result();
        }

        // Group by kelas
        $data_by_kelas = array();
        foreach ($kehadiran_list as $k) {
            $kelas = $k->kelas ?? 'Tidak Diketahui';
            if (!isset($data_by_kelas[$kelas])) {
                $data_by_kelas[$kelas] = array();
            }
            $data_by_kelas[$kelas][] = $k;
        }

        $row = 5;

        // Data per kelas
        foreach ($data_by_kelas as $kelas => $data_kelas) {
            // Header kelas
            $sheet->setCellValue('A' . $row, 'KELAS: ' . $kelas);
            $sheet->getStyle('A' . $row)->getFont()->setBold(true)->setSize(12);
            $sheet->mergeCells('A' . $row . ':G' . $row);
            $sheet->getStyle('A' . $row)->getFill()
                ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                ->getStartColor()->setARGB('FFE0E0E0');
            $row++;

            // Header tabel untuk setiap kelas
            $this->set_header_tabel($sheet, array('No', 'Tanggal', 'Kode MK', 'Nama Mata Kuliah', 'NIM', 'Nama Mahasiswa', 'Status Kehadiran'), 'A', $row);
            $row++;

            $no = 1;
            foreach ($data_kelas as $kehadiran) {
                $status = '';
                switch ($kehadiran->status_kehadiran) {
                    case 'tidak_hadir':
                        $status = 'Tidak Hadir';
                        break;
                    case 'izin':
                        $status = 'Izin';
                        break;
                    case 'sakit':
                        $status = 'Sakit';
                        break;
                }

                $sheet->setCellValue('A' . $row, $no++);
                $sheet->setCellValue('B' . $row, $kehadiran->tanggal ? date('d/m/Y', strtotime($kehadiran->tanggal)) : '');
                $sheet->setCellValue('C' . $row, $kehadiran->kode_mk ?? '');
                $sheet->setCellValue('D' . $row, $kehadiran->nama_mk ?? '');
                $sheet->setCellValue('E' . $row, $kehadiran->nim ?? '');
                $sheet->setCellValue('F' . $row, $kehadiran->nama_mahasiswa ?? '');
                $sheet->setCellValue('G' . $row, $status);
                $row++;
            }

            // Border untuk setiap kelas
            $start_row = $row - count($data_kelas) - 1;
            $this->set_border($sheet, 'A' . $start_row . ':G' . ($row - 1));
            
            // Center alignment untuk kolom tertentu per kelas
            $sheet->getStyle('A' . ($start_row + 1) . ':A' . ($row - 1))->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle('B' . ($start_row + 1) . ':B' . ($row - 1))->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle('E' . ($start_row + 1) . ':E' . ($row - 1))->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
            
            // Spasi antar kelas
            $row += 2;
        }

        // Auto size columns
        foreach (range('A', 'G') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        // Download
        $filename = 'Daftar_Mahasiswa_Tidak_Hadir_UTS_Per_Kelas_' . date('YmdHis') . '.xlsx';
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $filename . '"');
        header('Cache-Control: max-age=0');

        $writer = \PhpOffice\PhpSpreadsheet\IOFactory::createWriter($spreadsheet, 'Xlsx');
        $writer->save('php://output');
        exit;
    }

    /**
     * Export Daftar Mahasiswa Tidak Hadir Per Kelas UTS ke CSV (Fallback)
     */
    private function export_mahasiswa_tidak_hadir_uts_csv($tahun_aktif) {
        // Query data mahasiswa tidak hadir UTS, di-group by kelas
        // Ambil dari tabel sesi, kemudian join dengan kehadiran untuk mendapatkan status
        $this->db->select('COALESCE(juka.status_kehadiran, "tidak_hadir") as status_kehadiran,
                          jut.tanggal, jut.kelas,
                          mk.kode_mk, mk.nama_mk,
                          m.nim, m.nama_mahasiswa');
        $this->db->from('jadwal_uts_mahasiswa_sesi jms');
        $this->db->join('mahasiswa m', 'm.id = jms.id_mahasiswa', 'inner');
        $this->db->join('jadwal_uts jut', 'jut.id = jms.id_jadwal_uts', 'inner');
        $this->db->join('jadwal_uts_kehadiran juka', 'juka.id_jadwal_uts = jms.id_jadwal_uts AND juka.id_mahasiswa = m.id', 'left');
        $this->db->join('mapping_dosen_mk mdm', 'mdm.id = jut.id_mapping_dosen_mk', 'left');
        $this->db->join('mata_kuliah_aktif mka', 'mka.id = mdm.id_mata_kuliah_aktif', 'left');
        $this->db->join('kurikulum_detail kd', 'kd.id = mka.id_kurikulum_detail', 'left');
        $this->db->join('mata_kuliah mk', 'mk.id = kd.id_mata_kuliah', 'left');
        $this->db->where('jut.id_tahun_akademik', $tahun_aktif->id);
        $this->db->where('(COALESCE(juka.status_kehadiran, "tidak_hadir") = "tidak_hadir" OR juka.status_kehadiran = "izin" OR juka.status_kehadiran = "sakit")');
        $this->db->where('jut.kelas IS NOT NULL');
        $this->db->where('jut.kelas !=', '');
        $this->db->order_by('jut.kelas', 'ASC');
        $this->db->order_by('jut.tanggal', 'ASC');
        $this->db->order_by('mk.kode_mk', 'ASC');
        $this->db->order_by('m.nim', 'ASC');
        
        $kehadiran_list = $this->db->get()->result();
        
        // Jika tidak ada data dari tabel sesi, ambil dari kelas (backward compatibility)
        if (empty($kehadiran_list)) {
            $this->db->select('COALESCE(juka.status_kehadiran, "tidak_hadir") as status_kehadiran,
                              jut.tanggal, jut.kelas,
                              mk.kode_mk, mk.nama_mk,
                              m.nim, m.nama_mahasiswa');
            $this->db->from('mahasiswa m');
            $this->db->join('jadwal_uts jut', 'jut.kelas = m.kelas', 'inner');
            $this->db->join('jadwal_uts_kehadiran juka', 'juka.id_jadwal_uts = jut.id AND juka.id_mahasiswa = m.id', 'left');
            $this->db->join('mapping_dosen_mk mdm', 'mdm.id = jut.id_mapping_dosen_mk', 'left');
            $this->db->join('mata_kuliah_aktif mka', 'mka.id = mdm.id_mata_kuliah_aktif', 'left');
            $this->db->join('kurikulum_detail kd', 'kd.id = mka.id_kurikulum_detail', 'left');
            $this->db->join('mata_kuliah mk', 'mk.id = kd.id_mata_kuliah', 'left');
            $this->db->where('jut.id_tahun_akademik', $tahun_aktif->id);
            $this->db->where('m.status', 'aktif');
            $this->db->where('(COALESCE(juka.status_kehadiran, "tidak_hadir") = "tidak_hadir" OR juka.status_kehadiran = "izin" OR juka.status_kehadiran = "sakit")');
            $this->db->where('jut.kelas IS NOT NULL');
            $this->db->where('jut.kelas !=', '');
            $this->db->order_by('jut.kelas', 'ASC');
            $this->db->order_by('jut.tanggal', 'ASC');
            $this->db->order_by('mk.kode_mk', 'ASC');
            $this->db->order_by('m.nim', 'ASC');
            $kehadiran_list = $this->db->get()->result();
        }

        // Group by kelas
        $data_by_kelas = array();
        foreach ($kehadiran_list as $k) {
            $kelas = $k->kelas ?? 'Tidak Diketahui';
            if (!isset($data_by_kelas[$kelas])) {
                $data_by_kelas[$kelas] = array();
            }
            $data_by_kelas[$kelas][] = $k;
        }

        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment;filename="Daftar_Mahasiswa_Tidak_Hadir_UTS_Per_Kelas_' . date('YmdHis') . '.csv"');
        header('Cache-Control: max-age=0');
        
        echo "\xEF\xBB\xBF";
        
        $output = fopen('php://output', 'w');
        fputcsv($output, array('DAFTAR MAHASISWA TIDAK HADIR UTS PER KELAS'));
        fputcsv($output, array('Tahun Akademik: ' . $tahun_aktif->tahun_akademik . ' ' . ucfirst($tahun_aktif->semester)));
        fputcsv($output, array('Tanggal Export: ' . date('d/m/Y H:i:s')));
        fputcsv($output, array());
        fputcsv($output, array('No', 'Tanggal', 'Kode MK', 'Nama Mata Kuliah', 'NIM', 'Nama Mahasiswa', 'Status Kehadiran'));
        
        foreach ($data_by_kelas as $kelas => $data_kelas) {
            fputcsv($output, array('KELAS: ' . $kelas));
            $no = 1;
            foreach ($data_kelas as $kehadiran) {
                $status = '';
                switch ($kehadiran->status_kehadiran) {
                    case 'tidak_hadir':
                        $status = 'Tidak Hadir';
                        break;
                    case 'izin':
                        $status = 'Izin';
                        break;
                    case 'sakit':
                        $status = 'Sakit';
                        break;
                }
                fputcsv($output, array(
                    $no++,
                    $kehadiran->tanggal ? date('d/m/Y', strtotime($kehadiran->tanggal)) : '',
                    $kehadiran->kode_mk ?? '',
                    $kehadiran->nama_mk ?? '',
                    $kehadiran->nim ?? '',
                    $kehadiran->nama_mahasiswa ?? '',
                    $status
                ));
            }
            fputcsv($output, array()); // Baris kosong antar kelas
        }
        
        fclose($output);
        exit;
    }

    /**
     * Export Daftar Mahasiswa Tidak Hadir Per Kelas UAS ke Excel
     */
    public function export_mahasiswa_tidak_hadir_uas() {
        $tahun_aktif = $this->Tahun_akademik_model->get_aktif();
        if (!$tahun_aktif) {
            $this->session->set_flashdata('error', 'Tahun akademik aktif tidak ditemukan.');
            redirect('Laporan');
        }

        if (!$this->check_phpspreadsheet()) {
            // Fallback: Export CSV
            $this->export_mahasiswa_tidak_hadir_uas_csv($tahun_aktif);
            return;
        }

        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Mahasiswa Tidak Hadir UAS');

        // Kop tabel
        $this->set_kop_tabel($sheet, 'DAFTAR MAHASISWA TIDAK HADIR UAS PER KELAS', 'A1:G1', 1);
        $sheet->setCellValue('A2', 'Tahun Akademik: ' . $tahun_aktif->tahun_akademik . ' ' . ucfirst($tahun_aktif->semester));
        $sheet->setCellValue('A3', 'Tanggal Export: ' . date('d/m/Y H:i:s'));

        // Query data mahasiswa tidak hadir UAS, di-group by kelas
        // Ambil dari tabel sesi, kemudian join dengan kehadiran untuk mendapatkan status
        $this->db->select('COALESCE(juaka.status_kehadiran, "tidak_hadir") as status_kehadiran,
                          jua.tanggal, jua.kelas,
                          mk.kode_mk, mk.nama_mk,
                          m.nim, m.nama_mahasiswa');
        $this->db->from('jadwal_uas_mahasiswa_sesi jms');
        $this->db->join('mahasiswa m', 'm.id = jms.id_mahasiswa', 'inner');
        $this->db->join('jadwal_uas jua', 'jua.id = jms.id_jadwal_uas', 'inner');
        $this->db->join('jadwal_uas_kehadiran juaka', 'juaka.id_jadwal_uas = jms.id_jadwal_uas AND juaka.id_mahasiswa = m.id', 'left');
        $this->db->join('mapping_dosen_mk mdm', 'mdm.id = jua.id_mapping_dosen_mk', 'left');
        $this->db->join('mata_kuliah_aktif mka', 'mka.id = mdm.id_mata_kuliah_aktif', 'left');
        $this->db->join('kurikulum_detail kd', 'kd.id = mka.id_kurikulum_detail', 'left');
        $this->db->join('mata_kuliah mk', 'mk.id = kd.id_mata_kuliah', 'left');
        $this->db->where('jua.id_tahun_akademik', $tahun_aktif->id);
        $this->db->where('(COALESCE(juaka.status_kehadiran, "tidak_hadir") = "tidak_hadir" OR juaka.status_kehadiran = "izin" OR juaka.status_kehadiran = "sakit")');
        $this->db->where('jua.kelas IS NOT NULL');
        $this->db->where('jua.kelas !=', '');
        $this->db->order_by('jua.kelas', 'ASC');
        $this->db->order_by('jua.tanggal', 'ASC');
        $this->db->order_by('mk.kode_mk', 'ASC');
        $this->db->order_by('m.nim', 'ASC');
        
        $kehadiran_list = $this->db->get()->result();
        
        // Jika tidak ada data dari tabel sesi, ambil dari kelas (backward compatibility)
        if (empty($kehadiran_list)) {
            $this->db->select('COALESCE(juaka.status_kehadiran, "tidak_hadir") as status_kehadiran,
                              jua.tanggal, jua.kelas,
                              mk.kode_mk, mk.nama_mk,
                              m.nim, m.nama_mahasiswa');
            $this->db->from('mahasiswa m');
            $this->db->join('jadwal_uas jua', 'jua.kelas = m.kelas', 'inner');
            $this->db->join('jadwal_uas_kehadiran juaka', 'juaka.id_jadwal_uas = jua.id AND juaka.id_mahasiswa = m.id', 'left');
            $this->db->join('mapping_dosen_mk mdm', 'mdm.id = jua.id_mapping_dosen_mk', 'left');
            $this->db->join('mata_kuliah_aktif mka', 'mka.id = mdm.id_mata_kuliah_aktif', 'left');
            $this->db->join('kurikulum_detail kd', 'kd.id = mka.id_kurikulum_detail', 'left');
            $this->db->join('mata_kuliah mk', 'mk.id = kd.id_mata_kuliah', 'left');
            $this->db->where('jua.id_tahun_akademik', $tahun_aktif->id);
            $this->db->where('m.status', 'aktif');
            $this->db->where('(COALESCE(juaka.status_kehadiran, "tidak_hadir") = "tidak_hadir" OR juaka.status_kehadiran = "izin" OR juaka.status_kehadiran = "sakit")');
            $this->db->where('jua.kelas IS NOT NULL');
            $this->db->where('jua.kelas !=', '');
            $this->db->order_by('jua.kelas', 'ASC');
            $this->db->order_by('jua.tanggal', 'ASC');
            $this->db->order_by('mk.kode_mk', 'ASC');
            $this->db->order_by('m.nim', 'ASC');
            $kehadiran_list = $this->db->get()->result();
        }

        // Group by kelas
        $data_by_kelas = array();
        foreach ($kehadiran_list as $k) {
            $kelas = $k->kelas ?? 'Tidak Diketahui';
            if (!isset($data_by_kelas[$kelas])) {
                $data_by_kelas[$kelas] = array();
            }
            $data_by_kelas[$kelas][] = $k;
        }

        $row = 5;

        // Data per kelas
        foreach ($data_by_kelas as $kelas => $data_kelas) {
            // Header kelas
            $sheet->setCellValue('A' . $row, 'KELAS: ' . $kelas);
            $sheet->getStyle('A' . $row)->getFont()->setBold(true)->setSize(12);
            $sheet->mergeCells('A' . $row . ':G' . $row);
            $sheet->getStyle('A' . $row)->getFill()
                ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                ->getStartColor()->setARGB('FFE0E0E0');
            $row++;

            // Header tabel untuk setiap kelas
            $this->set_header_tabel($sheet, array('No', 'Tanggal', 'Kode MK', 'Nama Mata Kuliah', 'NIM', 'Nama Mahasiswa', 'Status Kehadiran'), 'A', $row);
            $row++;

            $no = 1;
            foreach ($data_kelas as $kehadiran) {
                $status = '';
                switch ($kehadiran->status_kehadiran) {
                    case 'tidak_hadir':
                        $status = 'Tidak Hadir';
                        break;
                    case 'izin':
                        $status = 'Izin';
                        break;
                    case 'sakit':
                        $status = 'Sakit';
                        break;
                }

                $sheet->setCellValue('A' . $row, $no++);
                $sheet->setCellValue('B' . $row, $kehadiran->tanggal ? date('d/m/Y', strtotime($kehadiran->tanggal)) : '');
                $sheet->setCellValue('C' . $row, $kehadiran->kode_mk ?? '');
                $sheet->setCellValue('D' . $row, $kehadiran->nama_mk ?? '');
                $sheet->setCellValue('E' . $row, $kehadiran->nim ?? '');
                $sheet->setCellValue('F' . $row, $kehadiran->nama_mahasiswa ?? '');
                $sheet->setCellValue('G' . $row, $status);
                $row++;
            }

            // Border untuk setiap kelas
            $start_row = $row - count($data_kelas) - 1;
            $this->set_border($sheet, 'A' . $start_row . ':G' . ($row - 1));
            
            // Center alignment untuk kolom tertentu per kelas
            $sheet->getStyle('A' . ($start_row + 1) . ':A' . ($row - 1))->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle('B' . ($start_row + 1) . ':B' . ($row - 1))->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle('E' . ($start_row + 1) . ':E' . ($row - 1))->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
            
            // Spasi antar kelas
            $row += 2;
        }

        // Auto size columns
        foreach (range('A', 'G') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        // Download
        $filename = 'Daftar_Mahasiswa_Tidak_Hadir_UAS_Per_Kelas_' . date('YmdHis') . '.xlsx';
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $filename . '"');
        header('Cache-Control: max-age=0');

        $writer = \PhpOffice\PhpSpreadsheet\IOFactory::createWriter($spreadsheet, 'Xlsx');
        $writer->save('php://output');
        exit;
    }

    /**
     * Export Daftar Mahasiswa Tidak Hadir Per Kelas UAS ke CSV (Fallback)
     */
    private function export_mahasiswa_tidak_hadir_uas_csv($tahun_aktif) {
        // Query data mahasiswa tidak hadir UAS, di-group by kelas
        // Ambil dari tabel sesi, kemudian join dengan kehadiran untuk mendapatkan status
        $this->db->select('COALESCE(juaka.status_kehadiran, "tidak_hadir") as status_kehadiran,
                          jua.tanggal, jua.kelas,
                          mk.kode_mk, mk.nama_mk,
                          m.nim, m.nama_mahasiswa');
        $this->db->from('jadwal_uas_mahasiswa_sesi jms');
        $this->db->join('mahasiswa m', 'm.id = jms.id_mahasiswa', 'inner');
        $this->db->join('jadwal_uas jua', 'jua.id = jms.id_jadwal_uas', 'inner');
        $this->db->join('jadwal_uas_kehadiran juaka', 'juaka.id_jadwal_uas = jms.id_jadwal_uas AND juaka.id_mahasiswa = m.id', 'left');
        $this->db->join('mapping_dosen_mk mdm', 'mdm.id = jua.id_mapping_dosen_mk', 'left');
        $this->db->join('mata_kuliah_aktif mka', 'mka.id = mdm.id_mata_kuliah_aktif', 'left');
        $this->db->join('kurikulum_detail kd', 'kd.id = mka.id_kurikulum_detail', 'left');
        $this->db->join('mata_kuliah mk', 'mk.id = kd.id_mata_kuliah', 'left');
        $this->db->where('jua.id_tahun_akademik', $tahun_aktif->id);
        $this->db->where('(COALESCE(juaka.status_kehadiran, "tidak_hadir") = "tidak_hadir" OR juaka.status_kehadiran = "izin" OR juaka.status_kehadiran = "sakit")');
        $this->db->where('jua.kelas IS NOT NULL');
        $this->db->where('jua.kelas !=', '');
        $this->db->order_by('jua.kelas', 'ASC');
        $this->db->order_by('jua.tanggal', 'ASC');
        $this->db->order_by('mk.kode_mk', 'ASC');
        $this->db->order_by('m.nim', 'ASC');
        
        $kehadiran_list = $this->db->get()->result();
        
        // Jika tidak ada data dari tabel sesi, ambil dari kelas (backward compatibility)
        if (empty($kehadiran_list)) {
            $this->db->select('COALESCE(juaka.status_kehadiran, "tidak_hadir") as status_kehadiran,
                              jua.tanggal, jua.kelas,
                              mk.kode_mk, mk.nama_mk,
                              m.nim, m.nama_mahasiswa');
            $this->db->from('mahasiswa m');
            $this->db->join('jadwal_uas jua', 'jua.kelas = m.kelas', 'inner');
            $this->db->join('jadwal_uas_kehadiran juaka', 'juaka.id_jadwal_uas = jua.id AND juaka.id_mahasiswa = m.id', 'left');
            $this->db->join('mapping_dosen_mk mdm', 'mdm.id = jua.id_mapping_dosen_mk', 'left');
            $this->db->join('mata_kuliah_aktif mka', 'mka.id = mdm.id_mata_kuliah_aktif', 'left');
            $this->db->join('kurikulum_detail kd', 'kd.id = mka.id_kurikulum_detail', 'left');
            $this->db->join('mata_kuliah mk', 'mk.id = kd.id_mata_kuliah', 'left');
            $this->db->where('jua.id_tahun_akademik', $tahun_aktif->id);
            $this->db->where('m.status', 'aktif');
            $this->db->where('(COALESCE(juaka.status_kehadiran, "tidak_hadir") = "tidak_hadir" OR juaka.status_kehadiran = "izin" OR juaka.status_kehadiran = "sakit")');
            $this->db->where('jua.kelas IS NOT NULL');
            $this->db->where('jua.kelas !=', '');
            $this->db->order_by('jua.kelas', 'ASC');
            $this->db->order_by('jua.tanggal', 'ASC');
            $this->db->order_by('mk.kode_mk', 'ASC');
            $this->db->order_by('m.nim', 'ASC');
            $kehadiran_list = $this->db->get()->result();
        }

        // Group by kelas
        $data_by_kelas = array();
        foreach ($kehadiran_list as $k) {
            $kelas = $k->kelas ?? 'Tidak Diketahui';
            if (!isset($data_by_kelas[$kelas])) {
                $data_by_kelas[$kelas] = array();
            }
            $data_by_kelas[$kelas][] = $k;
        }

        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment;filename="Daftar_Mahasiswa_Tidak_Hadir_UAS_Per_Kelas_' . date('YmdHis') . '.csv"');
        header('Cache-Control: max-age=0');
        
        echo "\xEF\xBB\xBF";
        
        $output = fopen('php://output', 'w');
        fputcsv($output, array('DAFTAR MAHASISWA TIDAK HADIR UAS PER KELAS'));
        fputcsv($output, array('Tahun Akademik: ' . $tahun_aktif->tahun_akademik . ' ' . ucfirst($tahun_aktif->semester)));
        fputcsv($output, array('Tanggal Export: ' . date('d/m/Y H:i:s')));
        fputcsv($output, array());
        fputcsv($output, array('No', 'Tanggal', 'Kode MK', 'Nama Mata Kuliah', 'NIM', 'Nama Mahasiswa', 'Status Kehadiran'));
        
        foreach ($data_by_kelas as $kelas => $data_kelas) {
            fputcsv($output, array('KELAS: ' . $kelas));
            $no = 1;
            foreach ($data_kelas as $kehadiran) {
                $status = '';
                switch ($kehadiran->status_kehadiran) {
                    case 'tidak_hadir':
                        $status = 'Tidak Hadir';
                        break;
                    case 'izin':
                        $status = 'Izin';
                        break;
                    case 'sakit':
                        $status = 'Sakit';
                        break;
                }
                fputcsv($output, array(
                    $no++,
                    $kehadiran->tanggal ? date('d/m/Y', strtotime($kehadiran->tanggal)) : '',
                    $kehadiran->kode_mk ?? '',
                    $kehadiran->nama_mk ?? '',
                    $kehadiran->nim ?? '',
                    $kehadiran->nama_mahasiswa ?? '',
                    $status
                ));
            }
            fputcsv($output, array()); // Baris kosong antar kelas
        }
        
        fclose($output);
        exit;
    }

    /**
     * Laporan lengkap UTS
     */
    public function laporan_uts() {
        $tahun_aktif = $this->Tahun_akademik_model->get_aktif();
        if (!$tahun_aktif) {
            $this->session->set_flashdata('error', 'Tahun akademik aktif tidak ditemukan.');
            redirect('Dashboard');
        }

        // Ambil filter dari GET
        $filter_tanggal = $this->input->get('tanggal');
        $filter_kelas = $this->input->get('kelas');
        $filter_kode_mk = $this->input->get('kode_mk');

        // Query jadwal UTS dengan semua relasi
        $this->db->select('jut.*, 
                          mk.kode_mk, mk.nama_mk, mk.sks,
                          d.nidn, d.nama_dosen,
                          rk.kode_ruang, rk.nama_ruang,
                          ta.tahun_akademik, ta.semester');
        $this->db->from('jadwal_uts jut');
        $this->db->join('mapping_dosen_mk mdm', 'mdm.id = jut.id_mapping_dosen_mk', 'left');
        $this->db->join('mata_kuliah_aktif mka', 'mka.id = mdm.id_mata_kuliah_aktif', 'left');
        $this->db->join('kurikulum_detail kd', 'kd.id = mka.id_kurikulum_detail', 'left');
        $this->db->join('mata_kuliah mk', 'mk.id = kd.id_mata_kuliah', 'left');
        $this->db->join('dosen d', 'd.id = mdm.id_dosen', 'left');
        $this->db->join('ruang_kelas rk', 'rk.id = jut.id_ruang_kelas', 'left');
        $this->db->join('tahun_akademik ta', 'ta.id = jut.id_tahun_akademik', 'left');
        $this->db->where('jut.id_tahun_akademik', $tahun_aktif->id);

        if ($filter_tanggal) {
            $this->db->where('jut.tanggal', $filter_tanggal);
        }
        if ($filter_kelas) {
            $this->db->where('jut.kelas', $filter_kelas);
        }
        if ($filter_kode_mk) {
            $this->db->like('mk.kode_mk', $filter_kode_mk);
        }

        $this->db->order_by('jut.tanggal', 'ASC');
        $this->db->order_by('jut.jam_mulai', 'ASC');
        $this->db->order_by('mk.kode_mk', 'ASC');
        
        $jadwal_list = $this->db->get()->result();

        // Ambil data tambahan untuk setiap jadwal
        foreach ($jadwal_list as $jadwal) {
            // Ambil pengawas
            $jadwal->pengawas = $this->Jadwal_uts_model->get_pengawas($jadwal->id);
            
            // Ambil semua mahasiswa yang terdaftar di jadwal ini (dari tabel sesi atau kelas)
            // Kemudian join dengan tabel kehadiran untuk mendapatkan status
            $this->db->select('m.id, m.nim, m.nama_mahasiswa, 
                              COALESCE(juka.status_kehadiran, "tidak_hadir") as status_kehadiran,
                              juka.keterangan, juka.created_at as waktu_absen');
            $this->db->from('jadwal_uts_mahasiswa_sesi jms');
            $this->db->join('mahasiswa m', 'm.id = jms.id_mahasiswa', 'inner');
            $this->db->join('jadwal_uts_kehadiran juka', 'juka.id_jadwal_uts = jms.id_jadwal_uts AND juka.id_mahasiswa = m.id', 'left');
            $this->db->where('jms.id_jadwal_uts', $jadwal->id);
            $this->db->order_by('m.nim', 'ASC');
            $kehadiran_from_sesi = $this->db->get()->result();
            
            // Jika tidak ada di tabel sesi, ambil dari kelas (backward compatibility)
            if (empty($kehadiran_from_sesi)) {
                $this->db->select('m.id, m.nim, m.nama_mahasiswa,
                                  COALESCE(juka.status_kehadiran, "tidak_hadir") as status_kehadiran,
                                  juka.keterangan, juka.created_at as waktu_absen');
                $this->db->from('mahasiswa m');
                $this->db->join('jadwal_uts_kehadiran juka', 'juka.id_jadwal_uts = ' . $jadwal->id . ' AND juka.id_mahasiswa = m.id', 'left');
                $this->db->where('m.kelas', $jadwal->kelas);
                $this->db->where('m.status', 'aktif');
                $this->db->order_by('m.nim', 'ASC');
                $kehadiran_from_sesi = $this->db->get()->result();
            }
            
            $jadwal->kehadiran = $kehadiran_from_sesi;
            
            
            // Hitung statistik kehadiran
            $jadwal->total_mahasiswa = count($jadwal->kehadiran);
            $jadwal->hadir = 0;
            $jadwal->tidak_hadir = 0;
            $jadwal->izin = 0;
            $jadwal->sakit = 0;
            
            foreach ($jadwal->kehadiran as $k) {
                switch ($k->status_kehadiran) {
                    case 'hadir':
                        $jadwal->hadir++;
                        break;
                    case 'tidak_hadir':
                        $jadwal->tidak_hadir++;
                        break;
                    case 'izin':
                        $jadwal->izin++;
                        break;
                    case 'sakit':
                        $jadwal->sakit++;
                        break;
                }
            }
        }

        // Ambil daftar kelas untuk filter
        $this->db->distinct();
        $this->db->select('kelas');
        $this->db->from('jadwal_uts');
        $this->db->where('id_tahun_akademik', $tahun_aktif->id);
        $this->db->order_by('kelas', 'ASC');
        $kelas_list = $this->db->get()->result();

        $data = array(
            'title' => 'Laporan UTS',
            'page_title' => 'Laporan Lengkap UTS',
            'breadcrumb' => array(
                array('label' => 'Laporan', 'url' => site_url('Laporan')),
                array('label' => 'Laporan UTS', 'active' => true)
            ),
            'sidebar_menu' => generate_sidebar_menu(get_user_role()),
            'tahun_aktif' => $tahun_aktif,
            'jadwal_list' => $jadwal_list,
            'kelas_list' => $kelas_list,
            'filter_tanggal' => $filter_tanggal,
            'filter_kelas' => $filter_kelas,
            'filter_kode_mk' => $filter_kode_mk
        );

        $data['content'] = $this->load->view('laporan/laporan_uts', $data, true);
        $this->load->view('layouts/main', $data);
    }

    /**
     * Laporan lengkap UAS
     */
    public function laporan_uas() {
        $tahun_aktif = $this->Tahun_akademik_model->get_aktif();
        if (!$tahun_aktif) {
            $this->session->set_flashdata('error', 'Tahun akademik aktif tidak ditemukan.');
            redirect('Dashboard');
        }

        // Ambil filter dari GET
        $filter_tanggal = $this->input->get('tanggal');
        $filter_kelas = $this->input->get('kelas');
        $filter_kode_mk = $this->input->get('kode_mk');

        // Query jadwal UAS dengan semua relasi
        $this->db->select('jua.*, 
                          mk.kode_mk, mk.nama_mk, mk.sks,
                          d.nidn, d.nama_dosen,
                          rk.kode_ruang, rk.nama_ruang,
                          ta.tahun_akademik, ta.semester');
        $this->db->from('jadwal_uas jua');
        $this->db->join('mapping_dosen_mk mdm', 'mdm.id = jua.id_mapping_dosen_mk', 'left');
        $this->db->join('mata_kuliah_aktif mka', 'mka.id = mdm.id_mata_kuliah_aktif', 'left');
        $this->db->join('kurikulum_detail kd', 'kd.id = mka.id_kurikulum_detail', 'left');
        $this->db->join('mata_kuliah mk', 'mk.id = kd.id_mata_kuliah', 'left');
        $this->db->join('dosen d', 'd.id = mdm.id_dosen', 'left');
        $this->db->join('ruang_kelas rk', 'rk.id = jua.id_ruang_kelas', 'left');
        $this->db->join('tahun_akademik ta', 'ta.id = jua.id_tahun_akademik', 'left');
        $this->db->where('jua.id_tahun_akademik', $tahun_aktif->id);

        if ($filter_tanggal) {
            $this->db->where('jua.tanggal', $filter_tanggal);
        }
        if ($filter_kelas) {
            $this->db->where('jua.kelas', $filter_kelas);
        }
        if ($filter_kode_mk) {
            $this->db->like('mk.kode_mk', $filter_kode_mk);
        }

        $this->db->order_by('jua.tanggal', 'ASC');
        $this->db->order_by('jua.jam_mulai', 'ASC');
        $this->db->order_by('mk.kode_mk', 'ASC');
        
        $jadwal_list = $this->db->get()->result();

        // Ambil data tambahan untuk setiap jadwal
        foreach ($jadwal_list as $jadwal) {
            // Ambil pengawas
            $jadwal->pengawas = $this->Jadwal_uas_model->get_pengawas($jadwal->id);
            
            // Ambil semua mahasiswa yang terdaftar di jadwal ini (dari tabel sesi atau kelas)
            // Kemudian join dengan tabel kehadiran untuk mendapatkan status
            $this->db->select('m.id, m.nim, m.nama_mahasiswa,
                              COALESCE(juaka.status_kehadiran, "tidak_hadir") as status_kehadiran,
                              juaka.keterangan, juaka.created_at as waktu_absen');
            $this->db->from('jadwal_uas_mahasiswa_sesi jms');
            $this->db->join('mahasiswa m', 'm.id = jms.id_mahasiswa', 'inner');
            $this->db->join('jadwal_uas_kehadiran juaka', 'juaka.id_jadwal_uas = jms.id_jadwal_uas AND juaka.id_mahasiswa = m.id', 'left');
            $this->db->where('jms.id_jadwal_uas', $jadwal->id);
            $this->db->order_by('m.nim', 'ASC');
            $kehadiran_from_sesi = $this->db->get()->result();
            
            // Jika tidak ada di tabel sesi, ambil dari kelas (backward compatibility)
            if (empty($kehadiran_from_sesi)) {
                $this->db->select('m.id, m.nim, m.nama_mahasiswa,
                                  COALESCE(juaka.status_kehadiran, "tidak_hadir") as status_kehadiran,
                                  juaka.keterangan, juaka.created_at as waktu_absen');
                $this->db->from('mahasiswa m');
                $this->db->join('jadwal_uas_kehadiran juaka', 'juaka.id_jadwal_uas = ' . $jadwal->id . ' AND juaka.id_mahasiswa = m.id', 'left');
                $this->db->where('m.kelas', $jadwal->kelas);
                $this->db->where('m.status', 'aktif');
                $this->db->order_by('m.nim', 'ASC');
                $kehadiran_from_sesi = $this->db->get()->result();
            }
            
            $jadwal->kehadiran = $kehadiran_from_sesi;
            
            
            // Hitung statistik kehadiran
            $jadwal->total_mahasiswa = count($jadwal->kehadiran);
            $jadwal->hadir = 0;
            $jadwal->tidak_hadir = 0;
            $jadwal->izin = 0;
            $jadwal->sakit = 0;
            
            foreach ($jadwal->kehadiran as $k) {
                switch ($k->status_kehadiran) {
                    case 'hadir':
                        $jadwal->hadir++;
                        break;
                    case 'tidak_hadir':
                        $jadwal->tidak_hadir++;
                        break;
                    case 'izin':
                        $jadwal->izin++;
                        break;
                    case 'sakit':
                        $jadwal->sakit++;
                        break;
                }
            }
        }

        // Ambil daftar kelas untuk filter
        $this->db->distinct();
        $this->db->select('kelas');
        $this->db->from('jadwal_uas');
        $this->db->where('id_tahun_akademik', $tahun_aktif->id);
        $this->db->order_by('kelas', 'ASC');
        $kelas_list = $this->db->get()->result();

        $data = array(
            'title' => 'Laporan UAS',
            'page_title' => 'Laporan Lengkap UAS',
            'breadcrumb' => array(
                array('label' => 'Laporan', 'url' => site_url('Laporan')),
                array('label' => 'Laporan UAS', 'active' => true)
            ),
            'sidebar_menu' => generate_sidebar_menu(get_user_role()),
            'tahun_aktif' => $tahun_aktif,
            'jadwal_list' => $jadwal_list,
            'kelas_list' => $kelas_list,
            'filter_tanggal' => $filter_tanggal,
            'filter_kelas' => $filter_kelas,
            'filter_kode_mk' => $filter_kode_mk
        );

        $data['content'] = $this->load->view('laporan/laporan_uas', $data, true);
        $this->load->view('layouts/main', $data);
    }
}
