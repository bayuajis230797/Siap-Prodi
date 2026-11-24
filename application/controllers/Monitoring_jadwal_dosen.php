<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Monitoring Jadwal Dosen Controller
 * Menampilkan jadwal mengajar dosen dari jadwal kuliah, UTS, dan UAS
 */
class Monitoring_jadwal_dosen extends MY_Controller {

    public function __construct() {
        parent::__construct();
        $this->check_role('sekprodi');
        $this->load->model('Jadwal_kuliah_model');
        $this->load->model('Jadwal_uts_model');
        $this->load->model('Jadwal_uas_model');
        $this->load->model('Dosen_model');
        $this->load->model('Tahun_akademik_model');
    }

    /**
     * Halaman monitoring jadwal dosen
     */
    public function index() {
        $tahun_aktif = $this->Tahun_akademik_model->get_aktif();
        $id_tahun_akademik = $tahun_aktif ? $tahun_aktif->id : null;

        // Filter
        $filter_dosen = $this->input->get('dosen');
        $filter_jenis = $this->input->get('jenis'); // kuliah, uts, uas, atau all

        // Ambil semua dosen untuk dropdown
        $dosen_list = $this->Dosen_model->get_all_active();

        // Ambil data jadwal dosen
        $data_dosen = array();

        if ($id_tahun_akademik) {
            // Jadwal Kuliah
            if (!$filter_jenis || $filter_jenis == 'all' || $filter_jenis == 'kuliah') {
                $jadwal_kuliah = $this->get_jadwal_kuliah_by_dosen($id_tahun_akademik, $filter_dosen);
                foreach ($jadwal_kuliah as $jk) {
                    $data_dosen[] = array(
                        'jenis' => 'Kuliah',
                        'dosen' => $jk->nama_dosen,
                        'nidn' => $jk->nidn,
                        'id_dosen' => $jk->id_dosen,
                        'hari_tanggal' => ucfirst($jk->hari),
                        'tanggal' => null,
                        'jam_mulai' => $jk->jam_mulai,
                        'jam_selesai' => $jk->jam_selesai,
                        'kelas' => $jk->kelas,
                        'mata_kuliah' => $jk->kode_mk . ' - ' . $jk->nama_mk,
                        'ruangan' => $jk->kode_ruang . ' - ' . $jk->nama_ruang,
                        'sesi' => null
                    );
                }
            }

            // Jadwal UTS
            if (!$filter_jenis || $filter_jenis == 'all' || $filter_jenis == 'uts') {
                $jadwal_uts = $this->get_jadwal_uts_by_dosen($id_tahun_akademik, $filter_dosen);
                foreach ($jadwal_uts as $jut) {
                    $data_dosen[] = array(
                        'jenis' => 'UTS',
                        'dosen' => $jut->nama_dosen,
                        'nidn' => $jut->nidn,
                        'id_dosen' => $jut->id_dosen,
                        'hari_tanggal' => date('d/m/Y', strtotime($jut->tanggal)),
                        'tanggal' => $jut->tanggal,
                        'jam_mulai' => $jut->jam_mulai,
                        'jam_selesai' => $jut->jam_selesai,
                        'kelas' => $jut->kelas,
                        'mata_kuliah' => $jut->kode_mk . ' - ' . $jut->nama_mk,
                        'ruangan' => $jut->kode_ruang . ' - ' . $jut->nama_ruang,
                        'sesi' => $jut->sesi
                    );
                }
            }

            // Jadwal UAS
            if (!$filter_jenis || $filter_jenis == 'all' || $filter_jenis == 'uas') {
                $jadwal_uas = $this->get_jadwal_uas_by_dosen($id_tahun_akademik, $filter_dosen);
                foreach ($jadwal_uas as $jua) {
                    $data_dosen[] = array(
                        'jenis' => 'UAS',
                        'dosen' => $jua->nama_dosen,
                        'nidn' => $jua->nidn,
                        'id_dosen' => $jua->id_dosen,
                        'hari_tanggal' => date('d/m/Y', strtotime($jua->tanggal)),
                        'tanggal' => $jua->tanggal,
                        'jam_mulai' => $jua->jam_mulai,
                        'jam_selesai' => $jua->jam_selesai,
                        'kelas' => $jua->kelas,
                        'mata_kuliah' => $jua->kode_mk . ' - ' . $jua->nama_mk,
                        'ruangan' => $jua->kode_ruang . ' - ' . $jua->nama_ruang,
                        'sesi' => $jua->sesi
                    );
                }
            }

            // Detect bentrok dosen
            // Bentrok jika: dosen sama, hari/tanggal sama, dan waktu overlap
            for ($i = 0; $i < count($data_dosen); $i++) {
                $data_dosen[$i]['bentrok'] = false;
                for ($j = $i + 1; $j < count($data_dosen); $j++) {
                    // Cek apakah dosen sama
                    if ($data_dosen[$i]['id_dosen'] == $data_dosen[$j]['id_dosen'] && 
                        $data_dosen[$i]['id_dosen'] != null) {
                        
                        // Cek apakah hari/tanggal sama
                        $hari_tanggal_sama = false;
                        if ($data_dosen[$i]['tanggal'] && $data_dosen[$j]['tanggal']) {
                            // Keduanya UTS/UAS - bandingkan tanggal
                            $hari_tanggal_sama = ($data_dosen[$i]['tanggal'] == $data_dosen[$j]['tanggal']);
                        } elseif (!$data_dosen[$i]['tanggal'] && !$data_dosen[$j]['tanggal']) {
                            // Keduanya kuliah - bandingkan hari
                            $hari_tanggal_sama = ($data_dosen[$i]['hari_tanggal'] == $data_dosen[$j]['hari_tanggal']);
                        }
                        
                        // Jika hari/tanggal sama, cek overlap waktu
                        if ($hari_tanggal_sama) {
                            $jam_mulai_i = strtotime($data_dosen[$i]['jam_mulai']);
                            $jam_selesai_i = strtotime($data_dosen[$i]['jam_selesai']);
                            $jam_mulai_j = strtotime($data_dosen[$j]['jam_mulai']);
                            $jam_selesai_j = strtotime($data_dosen[$j]['jam_selesai']);
                            
                            // Overlap jika: (mulai_i < selesai_j) AND (selesai_i > mulai_j)
                            if ($jam_mulai_i < $jam_selesai_j && $jam_selesai_i > $jam_mulai_j) {
                                $data_dosen[$i]['bentrok'] = true;
                                $data_dosen[$j]['bentrok'] = true;
                            }
                        }
                    }
                }
            }

            // Sort by dosen, then by tanggal/hari, then by jam_mulai
            usort($data_dosen, function($a, $b) {
                // Sort by dosen first
                $dosen_cmp = strcmp($a['dosen'], $b['dosen']);
                if ($dosen_cmp !== 0) {
                    return $dosen_cmp;
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
            'title' => 'Monitoring Jadwal Dosen',
            'page_title' => 'Monitoring Jadwal Mengajar Dosen',
            'breadcrumb' => array(
                array('label' => 'Jadwal', 'url' => site_url('Halaman_utama')),
                array('label' => 'Monitoring Jadwal Dosen', 'active' => true)
            ),
            'sidebar_menu' => generate_sidebar_menu(get_user_role()),
            'tahun_aktif' => $tahun_aktif,
            'dosen_list' => $dosen_list,
            'data_dosen' => $data_dosen,
            'filter_dosen' => $filter_dosen,
            'filter_jenis' => $filter_jenis ? $filter_jenis : 'all'
        );

        $data['content'] = $this->load->view('monitoring_jadwal_dosen/index', $data, true);
        $this->load->view('layouts/main', $data);
    }

    /**
     * Ambil jadwal kuliah berdasarkan dosen
     */
    private function get_jadwal_kuliah_by_dosen($id_tahun_akademik, $filter_dosen = null) {
        $this->db->select('jk.*, 
                          mk.kode_mk, mk.nama_mk,
                          d.nama_dosen, d.nidn, d.id as id_dosen,
                          rk.kode_ruang, rk.nama_ruang');
        $this->db->from('jadwal_kuliah jk');
        $this->db->join('mapping_dosen_mk mdm', 'mdm.id = jk.id_mapping_dosen_mk', 'left');
        $this->db->join('mata_kuliah_aktif mka', 'mka.id = mdm.id_mata_kuliah_aktif', 'left');
        $this->db->join('kurikulum_detail kd', 'kd.id = mka.id_kurikulum_detail', 'left');
        $this->db->join('mata_kuliah mk', 'mk.id = kd.id_mata_kuliah', 'left');
        $this->db->join('dosen d', 'd.id = mdm.id_dosen', 'left');
        $this->db->join('ruang_kelas rk', 'rk.id = jk.id_ruang_kelas', 'left');
        $this->db->where('jk.id_tahun_akademik', $id_tahun_akademik);
        $this->db->where('mdm.id_dosen IS NOT NULL');
        
        if ($filter_dosen) {
            $this->db->where('mdm.id_dosen', $filter_dosen);
        }
        
        $this->db->order_by('d.nama_dosen', 'ASC');
        $this->db->order_by('jk.hari', 'ASC');
        $this->db->order_by('jk.jam_mulai', 'ASC');
        
        return $this->db->get()->result();
    }

    /**
     * Ambil jadwal UTS berdasarkan dosen
     */
    private function get_jadwal_uts_by_dosen($id_tahun_akademik, $filter_dosen = null) {
        $this->db->select('jut.*, 
                          mk.kode_mk, mk.nama_mk,
                          d.nama_dosen, d.nidn, mdm.id_dosen,
                          rk.kode_ruang, rk.nama_ruang');
        $this->db->from('jadwal_uts jut');
        $this->db->join('mapping_dosen_mk mdm', 'mdm.id = jut.id_mapping_dosen_mk', 'left');
        $this->db->join('mata_kuliah_aktif mka', 'mka.id = mdm.id_mata_kuliah_aktif', 'left');
        $this->db->join('kurikulum_detail kd', 'kd.id = mka.id_kurikulum_detail', 'left');
        $this->db->join('mata_kuliah mk', 'mk.id = kd.id_mata_kuliah', 'left');
        $this->db->join('dosen d', 'd.id = mdm.id_dosen', 'left');
        $this->db->join('ruang_kelas rk', 'rk.id = jut.id_ruang_kelas', 'left');
        $this->db->where('jut.id_tahun_akademik', $id_tahun_akademik);
        $this->db->where('mdm.id_dosen IS NOT NULL');
        
        if ($filter_dosen) {
            $this->db->where('mdm.id_dosen', $filter_dosen);
        }
        
        $this->db->order_by('d.nama_dosen', 'ASC');
        $this->db->order_by('jut.tanggal', 'ASC');
        $this->db->order_by('jut.jam_mulai', 'ASC');
        
        return $this->db->get()->result();
    }

    /**
     * Ambil jadwal UAS berdasarkan dosen
     */
    private function get_jadwal_uas_by_dosen($id_tahun_akademik, $filter_dosen = null) {
        $this->db->select('jua.*, 
                          mk.kode_mk, mk.nama_mk,
                          d.nama_dosen, d.nidn, mdm.id_dosen,
                          rk.kode_ruang, rk.nama_ruang');
        $this->db->from('jadwal_uas jua');
        $this->db->join('mapping_dosen_mk mdm', 'mdm.id = jua.id_mapping_dosen_mk', 'left');
        $this->db->join('mata_kuliah_aktif mka', 'mka.id = mdm.id_mata_kuliah_aktif', 'left');
        $this->db->join('kurikulum_detail kd', 'kd.id = mka.id_kurikulum_detail', 'left');
        $this->db->join('mata_kuliah mk', 'mk.id = kd.id_mata_kuliah', 'left');
        $this->db->join('dosen d', 'd.id = mdm.id_dosen', 'left');
        $this->db->join('ruang_kelas rk', 'rk.id = jua.id_ruang_kelas', 'left');
        $this->db->where('jua.id_tahun_akademik', $id_tahun_akademik);
        $this->db->where('mdm.id_dosen IS NOT NULL');
        
        if ($filter_dosen) {
            $this->db->where('mdm.id_dosen', $filter_dosen);
        }
        
        $this->db->order_by('d.nama_dosen', 'ASC');
        $this->db->order_by('jua.tanggal', 'ASC');
        $this->db->order_by('jua.jam_mulai', 'ASC');
        
        return $this->db->get()->result();
    }

    /**
     * Export monitoring jadwal dosen ke Excel
     */
    public function export_excel() {
        $tahun_aktif = $this->Tahun_akademik_model->get_aktif();
        if (!$tahun_aktif) {
            $this->session->set_flashdata('error', 'Tidak ada tahun akademik aktif.');
            redirect('Monitoring_jadwal_dosen');
        }

        // Filter
        $filter_dosen = $this->input->get('dosen');
        $filter_jenis = $this->input->get('jenis');

        // Ambil data monitoring (sama seperti di index)
        $id_tahun_akademik = $tahun_aktif->id;
        $data_dosen = array();

        // Jadwal Kuliah
        if (!$filter_jenis || $filter_jenis == 'all' || $filter_jenis == 'kuliah') {
            $jadwal_kuliah = $this->get_jadwal_kuliah_by_dosen($id_tahun_akademik, $filter_dosen);
            foreach ($jadwal_kuliah as $jk) {
                $data_dosen[] = array(
                    'jenis' => 'Kuliah',
                    'dosen' => $jk->nama_dosen,
                    'nidn' => $jk->nidn,
                    'id_dosen' => $jk->id_dosen,
                    'hari_tanggal' => ucfirst($jk->hari),
                    'tanggal' => null,
                    'jam_mulai' => $jk->jam_mulai,
                    'jam_selesai' => $jk->jam_selesai,
                    'kelas' => $jk->kelas,
                    'mata_kuliah' => $jk->kode_mk . ' - ' . $jk->nama_mk,
                    'ruangan' => $jk->kode_ruang . ' - ' . $jk->nama_ruang,
                    'sesi' => null
                );
            }
        }

        // Jadwal UTS
        if (!$filter_jenis || $filter_jenis == 'all' || $filter_jenis == 'uts') {
            $jadwal_uts = $this->get_jadwal_uts_by_dosen($id_tahun_akademik, $filter_dosen);
            foreach ($jadwal_uts as $jut) {
                $data_dosen[] = array(
                    'jenis' => 'UTS',
                    'dosen' => $jut->nama_dosen,
                    'nidn' => $jut->nidn,
                    'id_dosen' => $jut->id_dosen,
                    'hari_tanggal' => date('d/m/Y', strtotime($jut->tanggal)),
                    'tanggal' => $jut->tanggal,
                    'jam_mulai' => $jut->jam_mulai,
                    'jam_selesai' => $jut->jam_selesai,
                    'kelas' => $jut->kelas,
                    'mata_kuliah' => $jut->kode_mk . ' - ' . $jut->nama_mk,
                    'ruangan' => $jut->kode_ruang . ' - ' . $jut->nama_ruang,
                    'sesi' => $jut->sesi
                );
            }
        }

        // Jadwal UAS
        if (!$filter_jenis || $filter_jenis == 'all' || $filter_jenis == 'uas') {
            $jadwal_uas = $this->get_jadwal_uas_by_dosen($id_tahun_akademik, $filter_dosen);
            foreach ($jadwal_uas as $jua) {
                $data_dosen[] = array(
                    'jenis' => 'UAS',
                    'dosen' => $jua->nama_dosen,
                    'nidn' => $jua->nidn,
                    'id_dosen' => $jua->id_dosen,
                    'hari_tanggal' => date('d/m/Y', strtotime($jua->tanggal)),
                    'tanggal' => $jua->tanggal,
                    'jam_mulai' => $jua->jam_mulai,
                    'jam_selesai' => $jua->jam_selesai,
                    'kelas' => $jua->kelas,
                    'mata_kuliah' => $jua->kode_mk . ' - ' . $jua->nama_mk,
                    'ruangan' => $jua->kode_ruang . ' - ' . $jua->nama_ruang,
                    'sesi' => $jua->sesi
                );
            }
        }

        // Detect bentrok
        for ($i = 0; $i < count($data_dosen); $i++) {
            $data_dosen[$i]['bentrok'] = false;
            for ($j = $i + 1; $j < count($data_dosen); $j++) {
                if ($data_dosen[$i]['id_dosen'] == $data_dosen[$j]['id_dosen'] && 
                    $data_dosen[$i]['id_dosen'] != null) {
                    
                    $hari_tanggal_sama = false;
                    if ($data_dosen[$i]['tanggal'] && $data_dosen[$j]['tanggal']) {
                        $hari_tanggal_sama = ($data_dosen[$i]['tanggal'] == $data_dosen[$j]['tanggal']);
                    } elseif (!$data_dosen[$i]['tanggal'] && !$data_dosen[$j]['tanggal']) {
                        $hari_tanggal_sama = ($data_dosen[$i]['hari_tanggal'] == $data_dosen[$j]['hari_tanggal']);
                    }
                    
                    if ($hari_tanggal_sama) {
                        $jam_mulai_i = strtotime($data_dosen[$i]['jam_mulai']);
                        $jam_selesai_i = strtotime($data_dosen[$i]['jam_selesai']);
                        $jam_mulai_j = strtotime($data_dosen[$j]['jam_mulai']);
                        $jam_selesai_j = strtotime($data_dosen[$j]['jam_selesai']);
                        
                        if ($jam_mulai_i < $jam_selesai_j && $jam_selesai_i > $jam_mulai_j) {
                            $data_dosen[$i]['bentrok'] = true;
                            $data_dosen[$j]['bentrok'] = true;
                        }
                    }
                }
            }
        }

        if (empty($data_dosen)) {
            $this->session->set_flashdata('warning', 'Tidak ada data untuk di-export.');
            redirect('Monitoring_jadwal_dosen');
        }

        // Cek PhpSpreadsheet
        $use_phpspreadsheet = false;
        if (file_exists(APPPATH . '../vendor/autoload.php')) {
            require_once APPPATH . '../vendor/autoload.php';
            if (class_exists('PhpOffice\\PhpSpreadsheet\\Spreadsheet')) {
                $use_phpspreadsheet = true;
            }
        }

        $filename_base = 'Monitoring_Jadwal_Dosen_' . $tahun_aktif->tahun_akademik . '_' . ucfirst($tahun_aktif->semester) . '_' . date('YmdHis');

        if ($use_phpspreadsheet) {
            // PhpSpreadsheet export
            $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet();
            $sheet->setTitle('Monitoring Jadwal Dosen');
            
            // Header
            $sheet->setCellValue('A1', 'MONITORING JADWAL MENGAJAR DOSEN');
            $sheet->mergeCells('A1:J1');
            $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14);
            $sheet->getStyle('A1')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
            
            $sheet->setCellValue('A2', 'Tahun Akademik: ' . $tahun_aktif->tahun_akademik . ' ' . ucfirst($tahun_aktif->semester));
            $sheet->mergeCells('A2:J2');
            $sheet->getStyle('A2')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
            
            $sheet->setCellValue('A3', 'Tanggal Export: ' . date('d/m/Y H:i'));
            $sheet->mergeCells('A3:J3');
            $sheet->getStyle('A3')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
            
            // Header tabel
            $headers = array('No', 'Dosen', 'NIDN', 'Jenis Jadwal', 'Hari/Tanggal', 'Jam', 'Kelas', 'Mata Kuliah', 'Ruangan', 'Sesi');
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
            foreach ($data_dosen as $d) {
                $sheet->setCellValue('A' . $row, $no++);
                $sheet->setCellValue('B' . $row, $d['dosen'] . ($d['bentrok'] ? ' [BENTROK]' : ''));
                $sheet->setCellValue('C' . $row, $d['nidn']);
                $sheet->setCellValue('D' . $row, $d['jenis']);
                $sheet->setCellValue('E' . $row, $d['hari_tanggal']);
                $sheet->setCellValue('F' . $row, date('H:i', strtotime($d['jam_mulai'])) . ' - ' . date('H:i', strtotime($d['jam_selesai'])));
                $sheet->setCellValue('G' . $row, $d['kelas']);
                $sheet->setCellValue('H' . $row, $d['mata_kuliah']);
                $sheet->setCellValue('I' . $row, $d['ruangan']);
                $sheet->setCellValue('J' . $row, $d['sesi'] ? 'Sesi ' . $d['sesi'] : '-');
                
                // Highlight bentrok
                if ($d['bentrok']) {
                    $sheet->getStyle('A' . $row . ':J' . $row)->getFill()
                        ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                        ->getStartColor()->setARGB('FFFF9999');
                }
                
                $row++;
            }
            
            // Auto width
            foreach (range('A', 'J') as $columnID) {
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
            fputcsv($output, array('MONITORING JADWAL MENGAJAR DOSEN'));
            fputcsv($output, array('Tahun Akademik: ' . $tahun_aktif->tahun_akademik . ' ' . ucfirst($tahun_aktif->semester)));
            fputcsv($output, array('Tanggal Export: ' . date('d/m/Y H:i')));
            fputcsv($output, array('')); // Empty row
            
            // Headers
            fputcsv($output, array('No', 'Dosen', 'NIDN', 'Jenis Jadwal', 'Hari/Tanggal', 'Jam', 'Kelas', 'Mata Kuliah', 'Ruangan', 'Sesi'));
            
            // Data
            $no = 1;
            foreach ($data_dosen as $d) {
                fputcsv($output, array(
                    $no++,
                    $d['dosen'] . ($d['bentrok'] ? ' [BENTROK]' : ''),
                    $d['nidn'],
                    $d['jenis'],
                    $d['hari_tanggal'],
                    date('H:i', strtotime($d['jam_mulai'])) . ' - ' . date('H:i', strtotime($d['jam_selesai'])),
                    $d['kelas'],
                    $d['mata_kuliah'],
                    $d['ruangan'],
                    $d['sesi'] ? 'Sesi ' . $d['sesi'] : '-'
                ));
            }
            
            fclose($output);
        }
    }
}



