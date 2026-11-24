<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Monitoring Ruangan Controller
 * Menampilkan penggunaan ruangan dari jadwal kuliah, UTS, dan UAS
 */
class Monitoring_ruangan extends MY_Controller {

    public function __construct() {
        parent::__construct();
        $this->check_role('sekprodi');
        $this->load->model('Jadwal_kuliah_model');
        $this->load->model('Jadwal_uts_model');
        $this->load->model('Jadwal_uas_model');
        $this->load->model('Ruang_kelas_model');
        $this->load->model('Tahun_akademik_model');
    }

    /**
     * Halaman monitoring ruangan
     */
    public function index() {
        $tahun_aktif = $this->Tahun_akademik_model->get_aktif();
        $id_tahun_akademik = $tahun_aktif ? $tahun_aktif->id : null;

        // Filter
        $filter_ruangan = $this->input->get('ruangan');
        $filter_jenis = $this->input->get('jenis'); // kuliah, uts, uas, atau all

        // Ambil semua ruangan untuk dropdown
        $ruang_list = $this->Ruang_kelas_model->get_all_active();

        // Ambil data penggunaan ruangan
        $data_ruangan = array();

        if ($id_tahun_akademik) {
            // Jadwal Kuliah
            if (!$filter_jenis || $filter_jenis == 'all' || $filter_jenis == 'kuliah') {
                $jadwal_kuliah = $this->get_jadwal_kuliah_by_ruangan($id_tahun_akademik, $filter_ruangan);
                foreach ($jadwal_kuliah as $jk) {
                    $data_ruangan[] = array(
                        'jenis' => 'Kuliah',
                        'ruangan' => $jk->kode_ruang . ' - ' . $jk->nama_ruang,
                        'id_ruang_kelas' => $jk->id_ruang_kelas,
                        'hari_tanggal' => ucfirst($jk->hari),
                        'tanggal' => null,
                        'jam_mulai' => $jk->jam_mulai,
                        'jam_selesai' => $jk->jam_selesai,
                        'kelas' => $jk->kelas,
                        'mata_kuliah' => $jk->kode_mk . ' - ' . $jk->nama_mk,
                        'dosen' => $jk->nama_dosen,
                        'sesi' => null
                    );
                }
            }

            // Jadwal UTS
            if (!$filter_jenis || $filter_jenis == 'all' || $filter_jenis == 'uts') {
                $jadwal_uts = $this->get_jadwal_uts_by_ruangan($id_tahun_akademik, $filter_ruangan);
                foreach ($jadwal_uts as $jut) {
                    $data_ruangan[] = array(
                        'jenis' => 'UTS',
                        'ruangan' => $jut->kode_ruang . ' - ' . $jut->nama_ruang,
                        'id_ruang_kelas' => $jut->id_ruang_kelas,
                        'hari_tanggal' => date('d/m/Y', strtotime($jut->tanggal)),
                        'tanggal' => $jut->tanggal,
                        'jam_mulai' => $jut->jam_mulai,
                        'jam_selesai' => $jut->jam_selesai,
                        'kelas' => $jut->kelas,
                        'mata_kuliah' => $jut->kode_mk . ' - ' . $jut->nama_mk,
                        'dosen' => $jut->nama_dosen,
                        'sesi' => $jut->sesi
                    );
                }
            }

            // Jadwal UAS
            if (!$filter_jenis || $filter_jenis == 'all' || $filter_jenis == 'uas') {
                $jadwal_uas = $this->get_jadwal_uas_by_ruangan($id_tahun_akademik, $filter_ruangan);
                foreach ($jadwal_uas as $jua) {
                    $data_ruangan[] = array(
                        'jenis' => 'UAS',
                        'ruangan' => $jua->kode_ruang . ' - ' . $jua->nama_ruang,
                        'id_ruang_kelas' => $jua->id_ruang_kelas,
                        'hari_tanggal' => date('d/m/Y', strtotime($jua->tanggal)),
                        'tanggal' => $jua->tanggal,
                        'jam_mulai' => $jua->jam_mulai,
                        'jam_selesai' => $jua->jam_selesai,
                        'kelas' => $jua->kelas,
                        'mata_kuliah' => $jua->kode_mk . ' - ' . $jua->nama_mk,
                        'dosen' => $jua->nama_dosen,
                        'sesi' => $jua->sesi
                    );
                }
            }

            // Detect bentrok ruangan
            // Bentrok jika: ruangan sama, hari/tanggal sama, dan waktu overlap
            for ($i = 0; $i < count($data_ruangan); $i++) {
                $data_ruangan[$i]['bentrok'] = false;
                for ($j = $i + 1; $j < count($data_ruangan); $j++) {
                    // Cek apakah ruangan sama
                    if ($data_ruangan[$i]['id_ruang_kelas'] == $data_ruangan[$j]['id_ruang_kelas'] && 
                        $data_ruangan[$i]['id_ruang_kelas'] != null) {
                        
                        // Cek apakah hari/tanggal sama
                        $hari_tanggal_sama = false;
                        if ($data_ruangan[$i]['tanggal'] && $data_ruangan[$j]['tanggal']) {
                            // Keduanya UTS/UAS - bandingkan tanggal
                            $hari_tanggal_sama = ($data_ruangan[$i]['tanggal'] == $data_ruangan[$j]['tanggal']);
                        } elseif (!$data_ruangan[$i]['tanggal'] && !$data_ruangan[$j]['tanggal']) {
                            // Keduanya kuliah - bandingkan hari
                            $hari_tanggal_sama = ($data_ruangan[$i]['hari_tanggal'] == $data_ruangan[$j]['hari_tanggal']);
                        }
                        
                        // Jika hari/tanggal sama, cek overlap waktu
                        if ($hari_tanggal_sama) {
                            $jam_mulai_i = strtotime($data_ruangan[$i]['jam_mulai']);
                            $jam_selesai_i = strtotime($data_ruangan[$i]['jam_selesai']);
                            $jam_mulai_j = strtotime($data_ruangan[$j]['jam_mulai']);
                            $jam_selesai_j = strtotime($data_ruangan[$j]['jam_selesai']);
                            
                            // Overlap jika: (mulai_i < selesai_j) AND (selesai_i > mulai_j)
                            if ($jam_mulai_i < $jam_selesai_j && $jam_selesai_i > $jam_mulai_j) {
                                $data_ruangan[$i]['bentrok'] = true;
                                $data_ruangan[$j]['bentrok'] = true;
                            }
                        }
                    }
                }
            }

            // Sort by ruangan, then by tanggal/hari, then by jam_mulai
            usort($data_ruangan, function($a, $b) {
                // Sort by ruangan first
                $ruang_cmp = strcmp($a['ruangan'], $b['ruangan']);
                if ($ruang_cmp !== 0) {
                    return $ruang_cmp;
                }
                
                // Then by tanggal (for UTS/UAS) or hari (for kuliah)
                if ($a['tanggal'] && $b['tanggal']) {
                    // Both have tanggal (UTS/UAS)
                    $date_cmp = strcmp($a['tanggal'], $b['tanggal']);
                    if ($date_cmp !== 0) {
                        return $date_cmp;
                    }
                } elseif (!$a['tanggal'] && !$b['tanggal']) {
                    // Both are kuliah (hari)
                    $hari_order = array('Senin' => 1, 'Selasa' => 2, 'Rabu' => 3, 'Kamis' => 4, 'Jumat' => 5, 'Sabtu' => 6, 'Minggu' => 7);
                    $a_hari = isset($hari_order[$a['hari_tanggal']]) ? $hari_order[$a['hari_tanggal']] : 999;
                    $b_hari = isset($hari_order[$b['hari_tanggal']]) ? $hari_order[$b['hari_tanggal']] : 999;
                    $hari_cmp = $a_hari - $b_hari;
                    if ($hari_cmp !== 0) {
                        return $hari_cmp;
                    }
                } else {
                    // One has tanggal, one doesn't - put kuliah (no tanggal) first
                    return $a['tanggal'] ? 1 : -1;
                }
                
                // Finally by jam_mulai
                return strcmp($a['jam_mulai'], $b['jam_mulai']);
            });
        }

        $data = array(
            'title' => 'Monitoring Ruangan',
            'page_title' => 'Monitoring Penggunaan Ruangan',
            'breadcrumb' => array(
                array('label' => 'Jadwal', 'url' => site_url('Halaman_utama')),
                array('label' => 'Monitoring Ruangan', 'active' => true)
            ),
            'sidebar_menu' => generate_sidebar_menu(get_user_role()),
            'tahun_aktif' => $tahun_aktif,
            'ruang_list' => $ruang_list,
            'data_ruangan' => $data_ruangan,
            'filter_ruangan' => $filter_ruangan,
            'filter_jenis' => $filter_jenis ? $filter_jenis : 'all'
        );

        $data['content'] = $this->load->view('monitoring_ruangan/index', $data, true);
        $this->load->view('layouts/main', $data);
    }

    /**
     * Ambil jadwal kuliah berdasarkan ruangan
     */
    private function get_jadwal_kuliah_by_ruangan($id_tahun_akademik, $filter_ruangan = null) {
        $this->db->select('jk.*, 
                          mk.kode_mk, mk.nama_mk,
                          d.nama_dosen,
                          rk.kode_ruang, rk.nama_ruang, rk.id as id_ruang_kelas');
        $this->db->from('jadwal_kuliah jk');
        $this->db->join('mapping_dosen_mk mdm', 'mdm.id = jk.id_mapping_dosen_mk', 'left');
        $this->db->join('mata_kuliah_aktif mka', 'mka.id = mdm.id_mata_kuliah_aktif', 'left');
        $this->db->join('kurikulum_detail kd', 'kd.id = mka.id_kurikulum_detail', 'left');
        $this->db->join('mata_kuliah mk', 'mk.id = kd.id_mata_kuliah', 'left');
        $this->db->join('dosen d', 'd.id = mdm.id_dosen', 'left');
        $this->db->join('ruang_kelas rk', 'rk.id = jk.id_ruang_kelas', 'left');
        $this->db->where('jk.id_tahun_akademik', $id_tahun_akademik);
        $this->db->where('jk.id_ruang_kelas IS NOT NULL');
        
        if ($filter_ruangan) {
            $this->db->where('jk.id_ruang_kelas', $filter_ruangan);
        }
        
        $this->db->order_by('rk.kode_ruang', 'ASC');
        $this->db->order_by('jk.hari', 'ASC');
        $this->db->order_by('jk.jam_mulai', 'ASC');
        
        return $this->db->get()->result();
    }

    /**
     * Ambil jadwal UTS berdasarkan ruangan
     */
    private function get_jadwal_uts_by_ruangan($id_tahun_akademik, $filter_ruangan = null) {
        $this->db->select('jut.*, 
                          mk.kode_mk, mk.nama_mk,
                          d.nama_dosen,
                          rk.kode_ruang, rk.nama_ruang, rk.id as id_ruang_kelas');
        $this->db->from('jadwal_uts jut');
        $this->db->join('mapping_dosen_mk mdm', 'mdm.id = jut.id_mapping_dosen_mk', 'left');
        $this->db->join('mata_kuliah_aktif mka', 'mka.id = mdm.id_mata_kuliah_aktif', 'left');
        $this->db->join('kurikulum_detail kd', 'kd.id = mka.id_kurikulum_detail', 'left');
        $this->db->join('mata_kuliah mk', 'mk.id = kd.id_mata_kuliah', 'left');
        $this->db->join('dosen d', 'd.id = mdm.id_dosen', 'left');
        $this->db->join('ruang_kelas rk', 'rk.id = jut.id_ruang_kelas', 'left');
        $this->db->where('jut.id_tahun_akademik', $id_tahun_akademik);
        $this->db->where('jut.id_ruang_kelas IS NOT NULL');
        
        if ($filter_ruangan) {
            $this->db->where('jut.id_ruang_kelas', $filter_ruangan);
        }
        
        $this->db->order_by('rk.kode_ruang', 'ASC');
        $this->db->order_by('jut.tanggal', 'ASC');
        $this->db->order_by('jut.jam_mulai', 'ASC');
        
        return $this->db->get()->result();
    }

    /**
     * Ambil jadwal UAS berdasarkan ruangan
     */
    private function get_jadwal_uas_by_ruangan($id_tahun_akademik, $filter_ruangan = null) {
        $this->db->select('jua.*, 
                          mk.kode_mk, mk.nama_mk,
                          d.nama_dosen,
                          rk.kode_ruang, rk.nama_ruang, rk.id as id_ruang_kelas');
        $this->db->from('jadwal_uas jua');
        $this->db->join('mapping_dosen_mk mdm', 'mdm.id = jua.id_mapping_dosen_mk', 'left');
        $this->db->join('mata_kuliah_aktif mka', 'mka.id = mdm.id_mata_kuliah_aktif', 'left');
        $this->db->join('kurikulum_detail kd', 'kd.id = mka.id_kurikulum_detail', 'left');
        $this->db->join('mata_kuliah mk', 'mk.id = kd.id_mata_kuliah', 'left');
        $this->db->join('dosen d', 'd.id = mdm.id_dosen', 'left');
        $this->db->join('ruang_kelas rk', 'rk.id = jua.id_ruang_kelas', 'left');
        $this->db->where('jua.id_tahun_akademik', $id_tahun_akademik);
        $this->db->where('jua.id_ruang_kelas IS NOT NULL');
        
        if ($filter_ruangan) {
            $this->db->where('jua.id_ruang_kelas', $filter_ruangan);
        }
        
        $this->db->order_by('rk.kode_ruang', 'ASC');
        $this->db->order_by('jua.tanggal', 'ASC');
        $this->db->order_by('jua.jam_mulai', 'ASC');
        
        return $this->db->get()->result();
    }

    /**
     * Export monitoring ruangan ke Excel
     */
    public function export_excel() {
        $tahun_aktif = $this->Tahun_akademik_model->get_aktif();
        if (!$tahun_aktif) {
            $this->session->set_flashdata('error', 'Tidak ada tahun akademik aktif.');
            redirect('Monitoring_ruangan');
        }

        // Filter
        $filter_ruangan = $this->input->get('ruangan');
        $filter_jenis = $this->input->get('jenis');

        // Ambil data monitoring (sama seperti di index)
        $id_tahun_akademik = $tahun_aktif->id;
        $data_ruangan = array();

        // Jadwal Kuliah
        if (!$filter_jenis || $filter_jenis == 'all' || $filter_jenis == 'kuliah') {
            $jadwal_kuliah = $this->get_jadwal_kuliah_by_ruangan($id_tahun_akademik, $filter_ruangan);
            foreach ($jadwal_kuliah as $jk) {
                $data_ruangan[] = array(
                    'jenis' => 'Kuliah',
                    'ruangan' => $jk->kode_ruang . ' - ' . $jk->nama_ruang,
                    'id_ruang_kelas' => $jk->id_ruang_kelas,
                    'hari_tanggal' => ucfirst($jk->hari),
                    'tanggal' => null,
                    'jam_mulai' => $jk->jam_mulai,
                    'jam_selesai' => $jk->jam_selesai,
                    'kelas' => $jk->kelas,
                    'mata_kuliah' => $jk->kode_mk . ' - ' . $jk->nama_mk,
                    'dosen' => $jk->nama_dosen,
                    'sesi' => null
                );
            }
        }

        // Jadwal UTS
        if (!$filter_jenis || $filter_jenis == 'all' || $filter_jenis == 'uts') {
            $jadwal_uts = $this->get_jadwal_uts_by_ruangan($id_tahun_akademik, $filter_ruangan);
            foreach ($jadwal_uts as $jut) {
                $data_ruangan[] = array(
                    'jenis' => 'UTS',
                    'ruangan' => $jut->kode_ruang . ' - ' . $jut->nama_ruang,
                    'id_ruang_kelas' => $jut->id_ruang_kelas,
                    'hari_tanggal' => date('d/m/Y', strtotime($jut->tanggal)),
                    'tanggal' => $jut->tanggal,
                    'jam_mulai' => $jut->jam_mulai,
                    'jam_selesai' => $jut->jam_selesai,
                    'kelas' => $jut->kelas,
                    'mata_kuliah' => $jut->kode_mk . ' - ' . $jut->nama_mk,
                    'dosen' => $jut->nama_dosen,
                    'sesi' => $jut->sesi
                );
            }
        }

        // Jadwal UAS
        if (!$filter_jenis || $filter_jenis == 'all' || $filter_jenis == 'uas') {
            $jadwal_uas = $this->get_jadwal_uas_by_ruangan($id_tahun_akademik, $filter_ruangan);
            foreach ($jadwal_uas as $jua) {
                $data_ruangan[] = array(
                    'jenis' => 'UAS',
                    'ruangan' => $jua->kode_ruang . ' - ' . $jua->nama_ruang,
                    'id_ruang_kelas' => $jua->id_ruang_kelas,
                    'hari_tanggal' => date('d/m/Y', strtotime($jua->tanggal)),
                    'tanggal' => $jua->tanggal,
                    'jam_mulai' => $jua->jam_mulai,
                    'jam_selesai' => $jua->jam_selesai,
                    'kelas' => $jua->kelas,
                    'mata_kuliah' => $jua->kode_mk . ' - ' . $jua->nama_mk,
                    'dosen' => $jua->nama_dosen,
                    'sesi' => $jua->sesi
                );
            }
        }

        // Detect bentrok
        for ($i = 0; $i < count($data_ruangan); $i++) {
            $data_ruangan[$i]['bentrok'] = false;
            for ($j = $i + 1; $j < count($data_ruangan); $j++) {
                if ($data_ruangan[$i]['id_ruang_kelas'] == $data_ruangan[$j]['id_ruang_kelas'] && 
                    $data_ruangan[$i]['id_ruang_kelas'] != null) {
                    
                    $hari_tanggal_sama = false;
                    if ($data_ruangan[$i]['tanggal'] && $data_ruangan[$j]['tanggal']) {
                        $hari_tanggal_sama = ($data_ruangan[$i]['tanggal'] == $data_ruangan[$j]['tanggal']);
                    } elseif (!$data_ruangan[$i]['tanggal'] && !$data_ruangan[$j]['tanggal']) {
                        $hari_tanggal_sama = ($data_ruangan[$i]['hari_tanggal'] == $data_ruangan[$j]['hari_tanggal']);
                    }
                    
                    if ($hari_tanggal_sama) {
                        $jam_mulai_i = strtotime($data_ruangan[$i]['jam_mulai']);
                        $jam_selesai_i = strtotime($data_ruangan[$i]['jam_selesai']);
                        $jam_mulai_j = strtotime($data_ruangan[$j]['jam_mulai']);
                        $jam_selesai_j = strtotime($data_ruangan[$j]['jam_selesai']);
                        
                        if ($jam_mulai_i < $jam_selesai_j && $jam_selesai_i > $jam_mulai_j) {
                            $data_ruangan[$i]['bentrok'] = true;
                            $data_ruangan[$j]['bentrok'] = true;
                        }
                    }
                }
            }
        }

        if (empty($data_ruangan)) {
            $this->session->set_flashdata('warning', 'Tidak ada data untuk di-export.');
            redirect('Monitoring_ruangan');
        }

        // Cek PhpSpreadsheet
        $use_phpspreadsheet = false;
        if (file_exists(APPPATH . '../vendor/autoload.php')) {
            require_once APPPATH . '../vendor/autoload.php';
            if (class_exists('PhpOffice\\PhpSpreadsheet\\Spreadsheet')) {
                $use_phpspreadsheet = true;
            }
        }

        $filename_base = 'Monitoring_Ruangan_' . $tahun_aktif->tahun_akademik . '_' . ucfirst($tahun_aktif->semester) . '_' . date('YmdHis');

        if ($use_phpspreadsheet) {
            // PhpSpreadsheet export
            $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet();
            $sheet->setTitle('Monitoring Ruangan');
            
            // Header
            $sheet->setCellValue('A1', 'MONITORING PENGGUNAAN RUANGAN');
            $sheet->mergeCells('A1:I1');
            $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14);
            $sheet->getStyle('A1')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
            
            $sheet->setCellValue('A2', 'Tahun Akademik: ' . $tahun_aktif->tahun_akademik . ' ' . ucfirst($tahun_aktif->semester));
            $sheet->mergeCells('A2:I2');
            $sheet->getStyle('A2')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
            
            $sheet->setCellValue('A3', 'Tanggal Export: ' . date('d/m/Y H:i'));
            $sheet->mergeCells('A3:I3');
            $sheet->getStyle('A3')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
            
            // Header tabel
            $headers = array('No', 'Ruangan', 'Jenis Jadwal', 'Hari/Tanggal', 'Jam', 'Kelas', 'Mata Kuliah', 'Dosen', 'Sesi');
            $col = 'A';
            $row = 5;
            
            foreach ($headers as $header) {
                $sheet->setCellValue($col . $row, $header);
                $sheet->getStyle($col . $row)->getFont()->setBold(true);
                $sheet->getStyle($col . $row)->getFill()
                    ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                    ->getStartColor()->setARGB('FFE0E0E0');
                $col++;
            }
            
            // Data
            $row = 6;
            $no = 1;
            foreach ($data_ruangan as $d) {
                $sheet->setCellValue('A' . $row, $no++);
                $sheet->setCellValue('B' . $row, $d['ruangan'] . ($d['bentrok'] ? ' [BENTROK]' : ''));
                $sheet->setCellValue('C' . $row, $d['jenis']);
                $sheet->setCellValue('D' . $row, $d['hari_tanggal']);
                $sheet->setCellValue('E' . $row, date('H:i', strtotime($d['jam_mulai'])) . ' - ' . date('H:i', strtotime($d['jam_selesai'])));
                $sheet->setCellValue('F' . $row, $d['kelas']);
                $sheet->setCellValue('G' . $row, $d['mata_kuliah']);
                $sheet->setCellValue('H' . $row, $d['dosen']);
                $sheet->setCellValue('I' . $row, $d['sesi'] ? 'Sesi ' . $d['sesi'] : '-');
                
                // Highlight bentrok
                if ($d['bentrok']) {
                    $sheet->getStyle('A' . $row . ':I' . $row)->getFill()
                        ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                        ->getStartColor()->setARGB('FFFF9999');
                }
                
                $row++;
            }
            
            // Auto width
            foreach (range('A', 'I') as $columnID) {
                $sheet->getColumnDimension($columnID)->setAutoSize(true);
            }
            
            // Download
            $filename = $filename_base . '.xlsx';
            header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
            header('Content-Disposition: attachment;filename="' . $filename . '"');
            header('Cache-Control: max-age=0');
            
            $writer = \PhpOffice\PhpSpreadsheet\IOFactory::createWriter($spreadsheet, 'Xlsx');
            $writer->save('php://output');
        } else {
            // CSV fallback
            $filename = $filename_base . '.csv';
            header('Content-Type: text/csv; charset=utf-8');
            header('Content-Disposition: attachment;filename="' . $filename . '"');
            header('Cache-Control: max-age=0');
            
            $output = fopen('php://output', 'w');
            fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF)); // UTF-8 BOM
            
            // Header CSV
            fputcsv($output, array('MONITORING PENGGUNAAN RUANGAN'));
            fputcsv($output, array('Tahun Akademik: ' . $tahun_aktif->tahun_akademik . ' ' . ucfirst($tahun_aktif->semester)));
            fputcsv($output, array('Tanggal Export: ' . date('d/m/Y H:i')));
            fputcsv($output, array('')); // Empty row
            
            // Headers
            fputcsv($output, array('No', 'Ruangan', 'Jenis Jadwal', 'Hari/Tanggal', 'Jam', 'Kelas', 'Mata Kuliah', 'Dosen', 'Sesi'));
            
            // Data
            $no = 1;
            foreach ($data_ruangan as $d) {
                fputcsv($output, array(
                    $no++,
                    $d['ruangan'] . ($d['bentrok'] ? ' [BENTROK]' : ''),
                    $d['jenis'],
                    $d['hari_tanggal'],
                    date('H:i', strtotime($d['jam_mulai'])) . ' - ' . date('H:i', strtotime($d['jam_selesai'])),
                    $d['kelas'],
                    $d['mata_kuliah'],
                    $d['dosen'],
                    $d['sesi'] ? 'Sesi ' . $d['sesi'] : '-'
                ));
            }
            
            fclose($output);
        }
    }
}

