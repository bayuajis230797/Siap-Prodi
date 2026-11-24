<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Jadwal_skripsi_sekprodi Controller
 * Mengelola jadwal sidang Skripsi (untuk Sekprodi)
 */
class Jadwal_skripsi_sekprodi extends MY_Controller {

    public function __construct() {
        parent::__construct();
        $this->check_role(array('sekprodi', 'kaprodi'));
        $this->load->model('Jadwal_skripsi_model');
        $this->load->model('Dosen_model');
        $this->load->model('Tahun_akademik_model');
        $this->load->library('pagination');
        $this->load->library('form_validation');
    }

    public function index() {
        $tahun_aktif = $this->Tahun_akademik_model->get_aktif();
        $id_tahun_akademik = $tahun_aktif ? $tahun_aktif->id : null;
        
        $status_filter = $this->input->get('status');
        
        $per_page = $this->input->get('per_page') ? (int)$this->input->get('per_page') : 10;
        $per_page = in_array($per_page, [10, 25, 50, 100]) ? $per_page : 10;
        
        $config['base_url'] = site_url('Jadwal_skripsi_sekprodi/index');
        $config['total_rows'] = $this->Jadwal_skripsi_model->count_all($id_tahun_akademik, $status_filter);
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
        
        $query_params = array_filter(array('status' => $status_filter, 'per_page' => ($per_page != 10 ? $per_page : null)));
        $query_string = http_build_query($query_params);
        if ($query_string) {
            $config['suffix'] = '?' . $query_string;
            $config['first_url'] = site_url('Jadwal_skripsi_sekprodi/index?' . $query_string);
        }
        
        $this->pagination->initialize($config);
        
        $page = ($this->uri->segment(3)) ? $this->uri->segment(3) : 1;
        $offset = ($page - 1) * $config['per_page'];
        
        $jadwal_list = $this->Jadwal_skripsi_model->get_all($id_tahun_akademik, $status_filter, $config['per_page'], $offset);
        
        $data = array(
            'title' => 'Jadwal Sidang Skripsi',
            'page_title' => 'Jadwal Sidang Skripsi',
            'breadcrumb' => array(
                array('label' => 'Skripsi', 'url' => '#'),
                array('label' => 'Jadwal Sidang', 'active' => true)
            ),
            'sidebar_menu' => generate_sidebar_menu(get_user_role()),
            'jadwal_list' => $jadwal_list,
            'pagination' => $this->pagination->create_links(),
            'total_rows' => $config['total_rows'],
            'tahun_aktif' => $tahun_aktif,
            'status_filter' => $status_filter,
            'per_page' => $per_page
        );

        $data['content'] = $this->load->view('sekprodi/jadwal_skripsi/index', $data, true);
        $this->load->view('layouts/main', $data);
    }

    public function edit($id) {
        $this->assert_not_kaprodi('Jadwal_skripsi_sekprodi');
        $jadwal = $this->Jadwal_skripsi_model->get_by_id($id);
        if (!$jadwal) {
            $this->session->set_flashdata('error', 'Data jadwal tidak ditemukan.');
            redirect('Jadwal_skripsi_sekprodi');
        }

        if ($this->input->method() === 'post') {
            $this->form_validation->set_rules('id_ketua_penguji', 'Ketua Penguji', 'required|integer');
            $this->form_validation->set_rules('id_penguji_1', 'Penguji 1', 'required|integer');
            $this->form_validation->set_rules('id_penguji_2', 'Penguji 2', 'required|integer');
            $this->form_validation->set_rules('tanggal_ujian', 'Tanggal Ujian', 'required');
            $this->form_validation->set_rules('jam_mulai', 'Jam Mulai', 'required');
            $this->form_validation->set_rules('jam_selesai', 'Jam Selesai', 'required');
            $this->form_validation->set_rules('ruangan', 'Ruangan', 'required|trim');

            if ($this->form_validation->run() === FALSE) {
                $dosen_list = $this->Dosen_model->get_all_active();
                $data['jadwal'] = $jadwal;
                $data['dosen_list'] = $dosen_list;
                $data['title'] = 'Edit Jadwal Sidang Skripsi';
                $data['page_title'] = 'Edit Jadwal Sidang Skripsi';
                $data['breadcrumb'] = array(
                    array('label' => 'Skripsi', 'url' => '#'),
                    array('label' => 'Jadwal Sidang', 'url' => site_url('Jadwal_skripsi_sekprodi')),
                    array('label' => 'Edit', 'active' => true)
                );
                $data['sidebar_menu'] = generate_sidebar_menu(get_user_role());
                $data['content'] = $this->load->view('sekprodi/jadwal_skripsi/edit', $data, true);
                $this->load->view('layouts/main', $data);
            } else {
                $id_ketua = $this->input->post('id_ketua_penguji');
                $id_penguji_1 = $this->input->post('id_penguji_1');
                $id_penguji_2 = $this->input->post('id_penguji_2');
                
                // Validasi penguji tidak boleh sama
                if ($id_ketua == $id_penguji_1 || $id_ketua == $id_penguji_2 || $id_penguji_1 == $id_penguji_2) {
                    $this->session->set_flashdata('error', 'Penguji tidak boleh sama.');
                    redirect('Jadwal_skripsi_sekprodi/edit/' . $id);
                }

                // Validasi anti bentrok
                $tanggal_ujian = $this->input->post('tanggal_ujian');
                $jam_mulai = $this->input->post('jam_mulai');
                $jam_selesai = $this->input->post('jam_selesai');
                $ruangan = $this->input->post('ruangan');
                
                // Validasi jam selesai harus setelah jam mulai
                if (strtotime($jam_selesai) <= strtotime($jam_mulai)) {
                    $this->session->set_flashdata('error', 'Jam selesai harus setelah jam mulai.');
                    redirect('Jadwal_skripsi_sekprodi/edit/' . $id);
                }
                
                $tahun_aktif = $this->Tahun_akademik_model->get_aktif();
                $bentrok = $this->Jadwal_skripsi_model->check_bentrok(
                    $tanggal_ujian,
                    $jam_mulai,
                    $jam_selesai,
                    $ruangan,
                    $tahun_aktif->id,
                    $id,
                    array($id_ketua, $id_penguji_1, $id_penguji_2)
                );
                
                if ($bentrok['bentrok_ruang']) {
                    $this->load->model('Dosen_model');
                    $dosen_penguji = $this->Dosen_model->get_by_id($bentrok['detail_ruang']->id_ketua_penguji);
                    $nama_penguji = $dosen_penguji ? $dosen_penguji->nama_dosen : 'Tidak diketahui';
                    $this->session->set_flashdata('error', 'Ruangan ' . $ruangan . ' sudah digunakan pada tanggal ' . date('d/m/Y', strtotime($tanggal_ujian)) . ' jam ' . date('H:i', strtotime($bentrok['detail_ruang']->jam_mulai)) . '-' . date('H:i', strtotime($bentrok['detail_ruang']->jam_selesai)) . ' oleh ' . $nama_penguji . '. Silakan pilih ruangan atau waktu lain.');
                    redirect('Jadwal_skripsi_sekprodi/edit/' . $id);
                }
                
                if ($bentrok['bentrok_penguji'] && !empty($bentrok['detail_penguji'])) {
                    $this->load->model('Dosen_model');
                    $error_msg = "Penguji bentrok pada tanggal " . date('d/m/Y', strtotime($tanggal_ujian)) . " jam " . date('H:i', strtotime($jam_mulai)) . "-" . date('H:i', strtotime($jam_selesai)) . ": ";
                    foreach ($bentrok['detail_penguji'] as $detail) {
                        $dosen = $this->Dosen_model->get_by_id($detail['id_dosen']);
                        if ($dosen) {
                            $error_msg .= $dosen->nama_dosen . ", ";
                        }
                    }
                    $error_msg = rtrim($error_msg, ', ') . " sudah memiliki jadwal pada waktu tersebut. Silakan pilih waktu atau penguji lain.";
                    $this->session->set_flashdata('error', $error_msg);
                    redirect('Jadwal_skripsi_sekprodi/edit/' . $id);
                }

                $data_update = array(
                    'tanggal_ujian' => $tanggal_ujian,
                    'jam_mulai' => $jam_mulai,
                    'jam_selesai' => $jam_selesai,
                    'ruangan' => $ruangan,
                    'id_ketua_penguji' => $id_ketua,
                    'id_anggota_penguji_1' => $id_penguji_1,
                    'id_anggota_penguji_2' => $id_penguji_2,
                    'status' => $this->input->post('status'),
                    'catatan' => $this->input->post('catatan')
                );

                if ($this->Jadwal_skripsi_model->update($id, $data_update)) {
                    $this->session->set_flashdata('success', 'Jadwal sidang berhasil diperbarui.');
                } else {
                    $this->session->set_flashdata('error', 'Gagal memperbarui jadwal sidang.');
                }
                redirect('Jadwal_skripsi_sekprodi');
            }
        } else {
            $dosen_list = $this->Dosen_model->get_all_active();
            $data['jadwal'] = $jadwal;
            $data['dosen_list'] = $dosen_list;
            $data['title'] = 'Edit Jadwal Sidang Skripsi';
            $data['page_title'] = 'Edit Jadwal Sidang Skripsi';
            $data['breadcrumb'] = array(
                array('label' => 'Skripsi', 'url' => '#'),
                array('label' => 'Jadwal Sidang', 'url' => site_url('Jadwal_skripsi_sekprodi')),
                array('label' => 'Edit', 'active' => true)
            );
            $data['sidebar_menu'] = generate_sidebar_menu(get_user_role());
            $data['content'] = $this->load->view('sekprodi/jadwal_skripsi/edit', $data, true);
            $this->load->view('layouts/main', $data);
        }
    }

    public function view($id) {
        $jadwal = $this->Jadwal_skripsi_model->get_by_id($id);
        if (!$jadwal) {
            $this->session->set_flashdata('error', 'Data jadwal tidak ditemukan.');
            redirect('Jadwal_skripsi_sekprodi');
        }

        $data = array(
            'title' => 'Detail Jadwal Sidang Skripsi',
            'page_title' => 'Detail Jadwal Sidang Skripsi',
            'breadcrumb' => array(
                array('label' => 'Skripsi', 'url' => '#'),
                array('label' => 'Jadwal Sidang', 'url' => site_url('Jadwal_skripsi_sekprodi')),
                array('label' => 'Detail', 'active' => true)
            ),
            'sidebar_menu' => generate_sidebar_menu(get_user_role()),
            'jadwal' => $jadwal
        );

        $data['content'] = $this->load->view('sekprodi/jadwal_skripsi/view', $data, true);
        $this->load->view('layouts/main', $data);
    }

    /**
     * Export jadwal sidang ke Excel
     */
    public function export_excel() {
        $tahun_aktif = $this->Tahun_akademik_model->get_aktif();
        if (!$tahun_aktif) {
            $this->session->set_flashdata('error', 'Tidak ada tahun akademik aktif.');
            redirect('Jadwal_skripsi_sekprodi');
        }

        $status_filter = $this->input->get('status');
        
        // Ambil semua jadwal
        $jadwal_list = $this->Jadwal_skripsi_model->get_all($tahun_aktif->id, $status_filter, 10000, 0);

        // Cek apakah PhpSpreadsheet tersedia
        $use_phpspreadsheet = false;
        if (file_exists(APPPATH . '../vendor/autoload.php')) {
            require_once APPPATH . '../vendor/autoload.php';
            if (class_exists('PhpOffice\\PhpSpreadsheet\\Spreadsheet')) {
                $use_phpspreadsheet = true;
            }
        }

        $filename_base = 'Jadwal_Sidang_Skripsi_' . $tahun_aktif->tahun_akademik . '_' . ucfirst($tahun_aktif->semester) . '_' . date('YmdHis');

        if ($use_phpspreadsheet) {
            // Buat Excel menggunakan PhpSpreadsheet
            $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet();
            
            // Judul
            $sheet->setCellValue('A1', 'JADWAL SIDANG SKRIPSI');
            $sheet->mergeCells('A1:U1');
            $sheet->getStyle('A1')->applyFromArray([
                'font' => ['bold' => true, 'size' => 16],
                'alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER]
            ]);
            
            $sheet->setCellValue('A2', 'Tahun Akademik: ' . $tahun_aktif->tahun_akademik . ' ' . ucfirst($tahun_aktif->semester));
            $sheet->mergeCells('A2:U2');
            
            $sheet->setCellValue('A3', 'Tanggal Export: ' . date('d F Y H:i:s'));
            $sheet->mergeCells('A3:U3');
            
            // Header
            $headerRow = 5;
            $headers = [
                'A' => 'No',
                'B' => 'NIM',
                'C' => 'Nama Mahasiswa',
                'D' => 'Kelas',
                'E' => 'Judul Skripsi',
                'F' => 'Tanggal Ujian',
                'G' => 'Hari',
                'H' => 'Jam Mulai',
                'I' => 'Jam Selesai',
                'J' => 'Ruangan',
                'K' => 'Ketua Penguji',
                'L' => 'NIDN Ketua',
                'M' => 'Anggota Penguji 1',
                'N' => 'NIDN Anggota 1',
                'O' => 'Anggota Penguji 2',
                'P' => 'NIDN Anggota 2',
                'Q' => 'Status',
                'R' => 'Nilai Bimbingan',
                'S' => 'Nilai Sidang',
                'T' => 'Nilai Akhir',
                'U' => 'Grade'
            ];
            
            foreach ($headers as $col => $label) {
                $sheet->setCellValue($col . $headerRow, $label);
            }
            
            $sheet->getStyle('A' . $headerRow . ':U' . $headerRow)->applyFromArray([
                'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                'fill' => ['fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID, 'startColor' => ['rgb' => '1F4E78']],
                'alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER]
            ]);
            
            // Data
            $row = $headerRow + 1;
            $no = 1;
            foreach ($jadwal_list as $jadwal) {
                $sheet->setCellValue('A' . $row, $no++);
                $sheet->setCellValue('B' . $row, isset($jadwal->nim) ? $jadwal->nim : '-');
                $sheet->setCellValue('C' . $row, isset($jadwal->nama_mahasiswa) ? $jadwal->nama_mahasiswa : '-');
                $sheet->setCellValue('D' . $row, isset($jadwal->kelas) ? $jadwal->kelas : '-');
                $sheet->setCellValue('E' . $row, isset($jadwal->judul_skripsi) ? $jadwal->judul_skripsi : '-');
                $sheet->setCellValue('F' . $row, isset($jadwal->tanggal_ujian) && $jadwal->tanggal_ujian ? date('d/m/Y', strtotime($jadwal->tanggal_ujian)) : '-');
                $sheet->setCellValue('G' . $row, hari_dari_tanggal(isset($jadwal->tanggal_ujian) ? $jadwal->tanggal_ujian : null));
                $sheet->setCellValue('H' . $row, isset($jadwal->jam_mulai) && $jadwal->jam_mulai ? date('H:i', strtotime($jadwal->jam_mulai)) : '-');
                $sheet->setCellValue('I' . $row, isset($jadwal->jam_selesai) && $jadwal->jam_selesai ? date('H:i', strtotime($jadwal->jam_selesai)) : '-');
                $sheet->setCellValue('J' . $row, isset($jadwal->ruangan) ? $jadwal->ruangan : '-');
                $sheet->setCellValue('K' . $row, isset($jadwal->ketua_penguji) ? $jadwal->ketua_penguji : '-');
                $sheet->setCellValue('L' . $row, isset($jadwal->ketua_penguji_nidn) ? $jadwal->ketua_penguji_nidn : '-');
                $sheet->setCellValue('M' . $row, isset($jadwal->anggota_penguji_1) ? $jadwal->anggota_penguji_1 : '-');
                $sheet->setCellValue('N' . $row, isset($jadwal->anggota_penguji_1_nidn) ? $jadwal->anggota_penguji_1_nidn : '-');
                $sheet->setCellValue('O' . $row, isset($jadwal->anggota_penguji_2) ? $jadwal->anggota_penguji_2 : '-');
                $sheet->setCellValue('P' . $row, isset($jadwal->anggota_penguji_2_nidn) ? $jadwal->anggota_penguji_2_nidn : '-');
                $sheet->setCellValue('Q' . $row, isset($jadwal->status) ? ucfirst($jadwal->status) : '-');
                $sheet->setCellValue('R' . $row, isset($jadwal->nilai_bimbingan_final) && $jadwal->nilai_bimbingan_final !== null ? number_format($jadwal->nilai_bimbingan_final, 2) : '-');
                $sheet->setCellValue('S' . $row, isset($jadwal->nilai_sidang) && $jadwal->nilai_sidang !== null ? number_format($jadwal->nilai_sidang, 2) : '-');
                $sheet->setCellValue('T' . $row, isset($jadwal->nilai_skripsi_final) && $jadwal->nilai_skripsi_final !== null ? number_format($jadwal->nilai_skripsi_final, 2) : '-');
                $sheet->setCellValue('U' . $row, isset($jadwal->nilai_huruf) && $jadwal->nilai_huruf ? $jadwal->nilai_huruf : '-');
                $row++;
            }
            
            // Set column width
            foreach (['A' => 6, 'B' => 15, 'C' => 35, 'D' => 12, 'E' => 50, 'F' => 15, 'G' => 12, 'H' => 12, 'I' => 12, 'J' => 15,
                     'K' => 25, 'L' => 15, 'M' => 25, 'N' => 15, 'O' => 25, 'P' => 15, 'Q' => 12, 'R' => 15, 'S' => 15, 'T' => 15, 'U' => 10] as $col => $width) {
                $sheet->getColumnDimension($col)->setWidth($width);
            }
            
            // Alignment
            $sheet->getStyle('A' . ($headerRow + 1) . ':A' . ($row - 1))->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle('D' . ($headerRow + 1) . ':D' . ($row - 1))->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle('F' . ($headerRow + 1) . ':U' . ($row - 1))->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
            
            // Border
            $sheet->getStyle('A' . $headerRow . ':U' . ($row - 1))->applyFromArray([
                'borders' => [
                    'allBorders' => [
                        'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN
                    ]
                ]
            ]);
            
            // Total
            $sheet->setCellValue('A' . $row, 'TOTAL');
            $sheet->mergeCells('A' . $row . ':E' . $row);
            $sheet->setCellValue('F' . $row, count($jadwal_list) . ' Jadwal');
            $sheet->mergeCells('F' . $row . ':U' . $row);
            $sheet->getStyle('A' . $row . ':U' . $row)->getFont()->setBold(true);
            
            // Filename
            $filename = $filename_base . '.xlsx';
            
            header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
            header('Content-Disposition: attachment;filename="' . $filename . '"');
            header('Cache-Control: max-age=0');
            
            $writer = \PhpOffice\PhpSpreadsheet\IOFactory::createWriter($spreadsheet, 'Xlsx');
            $writer->save('php://output');
            exit;
        } else {
            // Fallback: Export CSV
            $filename = $filename_base . '.csv';
            header('Content-Type: text/csv; charset=utf-8');
            header('Content-Disposition: attachment;filename="' . $filename . '"');
            header('Cache-Control: max-age=0');
            
            $output = fopen('php://output', 'w');
            
            // BOM untuk Excel UTF-8
            fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));
            
            // Header
            fputcsv($output, array('JADWAL SIDANG SKRIPSI'));
            fputcsv($output, array('Tahun Akademik: ' . $tahun_aktif->tahun_akademik . ' ' . ucfirst($tahun_aktif->semester)));
            fputcsv($output, array('Tanggal Export: ' . date('d F Y H:i:s')));
            fputcsv($output, array()); // Baris kosong
            fputcsv($output, array('No', 'NIM', 'Nama Mahasiswa', 'Kelas', 'Judul Skripsi', 'Tanggal Ujian', 'Hari', 'Jam Mulai', 'Jam Selesai', 'Ruangan', 'Ketua Penguji', 'NIDN Ketua', 'Anggota Penguji 1', 'NIDN Anggota 1', 'Anggota Penguji 2', 'NIDN Anggota 2', 'Status', 'Nilai Bimbingan', 'Nilai Sidang', 'Nilai Akhir', 'Grade'));
            
            // Data
            $no = 1;
            foreach ($jadwal_list as $jadwal) {
                fputcsv($output, array(
                    $no++,
                    isset($jadwal->nim) ? $jadwal->nim : '-',
                    isset($jadwal->nama_mahasiswa) ? $jadwal->nama_mahasiswa : '-',
                    isset($jadwal->kelas) ? $jadwal->kelas : '-',
                    isset($jadwal->judul_skripsi) ? $jadwal->judul_skripsi : '-',
                    isset($jadwal->tanggal_ujian) && $jadwal->tanggal_ujian ? date('d/m/Y', strtotime($jadwal->tanggal_ujian)) : '-',
                    hari_dari_tanggal(isset($jadwal->tanggal_ujian) ? $jadwal->tanggal_ujian : null),
                    isset($jadwal->jam_mulai) && $jadwal->jam_mulai ? date('H:i', strtotime($jadwal->jam_mulai)) : '-',
                    isset($jadwal->jam_selesai) && $jadwal->jam_selesai ? date('H:i', strtotime($jadwal->jam_selesai)) : '-',
                    isset($jadwal->ruangan) ? $jadwal->ruangan : '-',
                    isset($jadwal->ketua_penguji) ? $jadwal->ketua_penguji : '-',
                    isset($jadwal->ketua_penguji_nidn) ? $jadwal->ketua_penguji_nidn : '-',
                    isset($jadwal->anggota_penguji_1) ? $jadwal->anggota_penguji_1 : '-',
                    isset($jadwal->anggota_penguji_1_nidn) ? $jadwal->anggota_penguji_1_nidn : '-',
                    isset($jadwal->anggota_penguji_2) ? $jadwal->anggota_penguji_2 : '-',
                    isset($jadwal->anggota_penguji_2_nidn) ? $jadwal->anggota_penguji_2_nidn : '-',
                    isset($jadwal->status) ? ucfirst($jadwal->status) : '-',
                    isset($jadwal->nilai_bimbingan_final) && $jadwal->nilai_bimbingan_final !== null ? number_format($jadwal->nilai_bimbingan_final, 2, ',', '') : '-',
                    isset($jadwal->nilai_sidang) && $jadwal->nilai_sidang !== null ? number_format($jadwal->nilai_sidang, 2, ',', '') : '-',
                    isset($jadwal->nilai_skripsi_final) && $jadwal->nilai_skripsi_final !== null ? number_format($jadwal->nilai_skripsi_final, 2, ',', '') : '-',
                    isset($jadwal->nilai_huruf) && $jadwal->nilai_huruf ? $jadwal->nilai_huruf : '-'
                ));
            }
            
            fputcsv($output, array()); // Baris kosong
            fputcsv($output, array('TOTAL', '', '', '', '', count($jadwal_list) . ' Jadwal', '', '', '', '', '', '', '', '', '', '', '', '', '', '', ''));
            
            fclose($output);
            exit;
        }
    }
}


