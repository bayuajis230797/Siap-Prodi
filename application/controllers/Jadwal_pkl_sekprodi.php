<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Jadwal_pkl_sekprodi Controller
 * Mengelola jadwal sidang PKL (untuk Sekprodi)
 */
class Jadwal_pkl_sekprodi extends MY_Controller {

    public function __construct() {
        parent::__construct();
        $this->check_role(array('sekprodi', 'kaprodi'));
        $this->load->model('Jadwal_pkl_model');
        $this->load->model('Pkl_pendaftaran_sidang_model');
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
        
        $config['base_url'] = site_url('Jadwal_pkl_sekprodi/index');
        $config['total_rows'] = $this->Jadwal_pkl_model->count_all($id_tahun_akademik, $status_filter);
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
            $config['first_url'] = site_url('Jadwal_pkl_sekprodi/index?' . $query_string);
        }
        
        $this->pagination->initialize($config);
        
        $page = ($this->uri->segment(3)) ? $this->uri->segment(3) : 1;
        $offset = ($page - 1) * $config['per_page'];
        
        $jadwal_list = $this->Jadwal_pkl_model->get_all($id_tahun_akademik, $status_filter, $config['per_page'], $offset);
        
        $data = array(
            'title' => 'Jadwal Sidang PKL',
            'page_title' => 'Jadwal Sidang PKL',
            'breadcrumb' => array(
                array('label' => 'PKL', 'url' => '#'),
                array('label' => 'Jadwal Sidang PKL', 'active' => true)
            ),
            'sidebar_menu' => generate_sidebar_menu(get_user_role()),
            'jadwal_list' => $jadwal_list,
            'pagination' => $this->pagination->create_links(),
            'total_rows' => $config['total_rows'],
            'tahun_aktif' => $tahun_aktif,
            'status_filter' => $status_filter,
            'per_page' => $per_page
        );

        $data['content'] = $this->load->view('sekprodi/jadwal_pkl/index', $data, true);
        $this->load->view('layouts/main', $data);
    }

    public function create() {
        $this->assert_not_kaprodi('Jadwal_pkl_sekprodi');
        $tahun_aktif = $this->Tahun_akademik_model->get_aktif();
        if (!$tahun_aktif) {
            $this->session->set_flashdata('error', 'Tidak ada tahun akademik aktif.');
            redirect('Jadwal_pkl_sekprodi');
        }

        if ($this->input->method() === 'post') {
            $this->form_validation->set_rules('id_mahasiswa', 'Mahasiswa', 'required|integer');
            $this->form_validation->set_rules('id_penguji', 'Dosen Penguji', 'required|integer');
            $this->form_validation->set_rules('tanggal', 'Tanggal Ujian', 'required');
            $this->form_validation->set_rules('jam_mulai', 'Jam Mulai', 'required');
            $this->form_validation->set_rules('jam_selesai', 'Jam Selesai', 'required');
            $this->form_validation->set_rules('ruangan', 'Ruangan', 'required|trim');

            if ($this->form_validation->run() === FALSE) {
                redirect('Jadwal_pkl_sekprodi/create');
            }

            // Cek apakah mahasiswa sudah mendaftar sidang dan berkas sudah disetujui
            $this->load->model('Pkl_pendaftaran_sidang_model');
            $pendaftaran = $this->Pkl_pendaftaran_sidang_model->get_by_mahasiswa($this->input->post('id_mahasiswa'), $tahun_aktif->id);
            
            if (!$pendaftaran || $pendaftaran->status !== 'disetujui') {
                $this->session->set_flashdata('error', 'Mahasiswa belum mendaftar sidang atau berkas belum disetujui.');
                redirect('Jadwal_pkl_sekprodi/create');
            }

            // Cek apakah sudah ada jadwal
            $jadwal_existing = $this->Jadwal_pkl_model->get_by_mahasiswa($this->input->post('id_mahasiswa'), $tahun_aktif->id);
            if ($jadwal_existing) {
                $this->session->set_flashdata('error', 'Mahasiswa ini sudah memiliki jadwal sidang.');
                redirect('Jadwal_pkl_sekprodi/create');
            }

            // Validasi anti bentrok
            $tanggal = $this->input->post('tanggal');
            $jam_mulai = $this->input->post('jam_mulai');
            $jam_selesai = $this->input->post('jam_selesai');
            $ruangan = $this->input->post('ruangan');
            $id_penguji = $this->input->post('id_penguji');
            
            // Validasi jam selesai harus setelah jam mulai
            if (strtotime($jam_selesai) <= strtotime($jam_mulai)) {
                $this->session->set_flashdata('error', 'Jam selesai harus setelah jam mulai.');
                redirect('Jadwal_pkl_sekprodi/create');
            }
            
            $bentrok = $this->Jadwal_pkl_model->check_bentrok($tanggal, $jam_mulai, $jam_selesai, $ruangan, $tahun_aktif->id, null, $id_penguji);
            
            if ($bentrok['bentrok_ruang']) {
                $this->load->model('Dosen_model');
                $dosen_penguji = $this->Dosen_model->get_by_id($bentrok['detail_ruang']->id_penguji);
                $nama_penguji = $dosen_penguji ? $dosen_penguji->nama_dosen : 'Tidak diketahui';
                $this->session->set_flashdata('error', 'Ruangan ' . $ruangan . ' sudah digunakan pada tanggal ' . date('d/m/Y', strtotime($tanggal)) . ' jam ' . date('H:i', strtotime($bentrok['detail_ruang']->jam_mulai)) . '-' . date('H:i', strtotime($bentrok['detail_ruang']->jam_selesai)) . ' oleh ' . $nama_penguji . '. Silakan pilih ruangan atau waktu lain.');
                redirect('Jadwal_pkl_sekprodi/create');
            }
            
            if ($bentrok['bentrok_penguji']) {
                $this->load->model('Dosen_model');
                $dosen = $this->Dosen_model->get_by_id($id_penguji);
                $nama_dosen = $dosen ? $dosen->nama_dosen : 'Tidak diketahui';
                $this->session->set_flashdata('error', 'Dosen penguji ' . $nama_dosen . ' sudah memiliki jadwal pada tanggal ' . date('d/m/Y', strtotime($tanggal)) . ' jam ' . date('H:i', strtotime($bentrok['detail_penguji']->jam_mulai)) . '-' . date('H:i', strtotime($bentrok['detail_penguji']->jam_selesai)) . ' di ruangan ' . $bentrok['detail_penguji']->ruangan . '. Silakan pilih waktu atau penguji lain.');
                redirect('Jadwal_pkl_sekprodi/create');
            }

            // Ambil id_pembimbing dari pengajuan
            $this->load->model('Pkl_pengajuan_model');
            $pengajuan = $this->Pkl_pengajuan_model->get_by_mahasiswa($this->input->post('id_mahasiswa'), $tahun_aktif->id);
            
            $jadwal_data = array(
                'id_mahasiswa' => $this->input->post('id_mahasiswa'),
                'id_pembimbing' => $pengajuan ? $pengajuan->id_pembimbing : null,
                'id_penguji' => $this->input->post('id_penguji'),
                'tanggal' => $this->input->post('tanggal'),
                'jam_mulai' => $this->input->post('jam_mulai'),
                'jam_selesai' => $this->input->post('jam_selesai'),
                'ruangan' => $this->input->post('ruangan'),
                'id_tahun_akademik' => $tahun_aktif->id,
                'status' => 'terjadwal'
            );

            $this->Jadwal_pkl_model->create($jadwal_data);
            $this->session->set_flashdata('success', 'Jadwal sidang PKL berhasil dibuat.');
            redirect('Jadwal_pkl_sekprodi');
        }

        // Ambil mahasiswa yang sudah disetujui pendaftaran sidangnya
        $this->load->model('Pkl_pendaftaran_sidang_model');
        $pendaftaran_list = $this->Pkl_pendaftaran_sidang_model->get_all(array(
            'id_tahun_akademik' => $tahun_aktif->id,
            'status' => 'disetujui'
        ));

        // Filter hanya yang belum punya jadwal
        $mahasiswa_siap_jadwal = array();
        foreach ($pendaftaran_list as $pendaftaran) {
            $jadwal_existing = $this->Jadwal_pkl_model->get_by_mahasiswa($pendaftaran->id_mahasiswa, $tahun_aktif->id);
            if (!$jadwal_existing) {
                $mahasiswa_siap_jadwal[] = $pendaftaran;
            }
        }

        $dosen_list = $this->Dosen_model->get_all();
        
        $data = array(
            'title' => 'Tambah Jadwal Sidang PKL',
            'page_title' => 'Tambah Jadwal Sidang PKL',
            'breadcrumb' => array(
                array('label' => 'PKL', 'url' => '#'),
                array('label' => 'Jadwal Sidang PKL', 'url' => site_url('Jadwal_pkl_sekprodi')),
                array('label' => 'Tambah', 'active' => true)
            ),
            'sidebar_menu' => generate_sidebar_menu(get_user_role()),
            'mahasiswa_siap_jadwal' => $mahasiswa_siap_jadwal,
            'dosen_list' => $dosen_list,
            'tahun_aktif' => $tahun_aktif
        );

        $data['content'] = $this->load->view('sekprodi/jadwal_pkl/create', $data, true);
        $this->load->view('layouts/main', $data);
    }

    public function edit($id) {
        $this->assert_not_kaprodi('Jadwal_pkl_sekprodi');
        $jadwal = $this->Jadwal_pkl_model->get_by_id($id);
        if (!$jadwal) {
            $this->session->set_flashdata('error', 'Data jadwal tidak ditemukan.');
            redirect('Jadwal_pkl_sekprodi');
        }

        if ($this->input->method() === 'post') {
            $this->form_validation->set_rules('id_penguji', 'Dosen Penguji', 'required|integer');
            $this->form_validation->set_rules('tanggal', 'Tanggal Ujian', 'required');
            $this->form_validation->set_rules('jam_mulai', 'Jam Mulai', 'required');
            $this->form_validation->set_rules('jam_selesai', 'Jam Selesai', 'required');
            $this->form_validation->set_rules('ruangan', 'Ruangan', 'required|trim');

            if ($this->form_validation->run() === FALSE) {
                redirect('Jadwal_pkl_sekprodi/edit/' . $id);
            }

            // Validasi anti bentrok
            $tanggal = $this->input->post('tanggal');
            $jam_mulai = $this->input->post('jam_mulai');
            $jam_selesai = $this->input->post('jam_selesai');
            $ruangan = $this->input->post('ruangan');
            $id_penguji = $this->input->post('id_penguji');
            
            // Validasi jam selesai harus setelah jam mulai
            if (strtotime($jam_selesai) <= strtotime($jam_mulai)) {
                $this->session->set_flashdata('error', 'Jam selesai harus setelah jam mulai.');
                redirect('Jadwal_pkl_sekprodi/edit/' . $id);
            }
            
            $tahun_aktif = $this->Tahun_akademik_model->get_aktif();
            $bentrok = $this->Jadwal_pkl_model->check_bentrok($tanggal, $jam_mulai, $jam_selesai, $ruangan, $tahun_aktif->id, $id, $id_penguji);
            
            if ($bentrok['bentrok_ruang']) {
                $this->load->model('Dosen_model');
                $dosen_penguji = $this->Dosen_model->get_by_id($bentrok['detail_ruang']->id_penguji);
                $nama_penguji = $dosen_penguji ? $dosen_penguji->nama_dosen : 'Tidak diketahui';
                $this->session->set_flashdata('error', 'Ruangan ' . $ruangan . ' sudah digunakan pada tanggal ' . date('d/m/Y', strtotime($tanggal)) . ' jam ' . date('H:i', strtotime($bentrok['detail_ruang']->jam_mulai)) . '-' . date('H:i', strtotime($bentrok['detail_ruang']->jam_selesai)) . ' oleh ' . $nama_penguji . '. Silakan pilih ruangan atau waktu lain.');
                redirect('Jadwal_pkl_sekprodi/edit/' . $id);
            }
            
            if ($bentrok['bentrok_penguji']) {
                $this->load->model('Dosen_model');
                $dosen = $this->Dosen_model->get_by_id($id_penguji);
                $nama_dosen = $dosen ? $dosen->nama_dosen : 'Tidak diketahui';
                $this->session->set_flashdata('error', 'Dosen penguji ' . $nama_dosen . ' sudah memiliki jadwal pada tanggal ' . date('d/m/Y', strtotime($tanggal)) . ' jam ' . date('H:i', strtotime($bentrok['detail_penguji']->jam_mulai)) . '-' . date('H:i', strtotime($bentrok['detail_penguji']->jam_selesai)) . ' di ruangan ' . $bentrok['detail_penguji']->ruangan . '. Silakan pilih waktu atau penguji lain.');
                redirect('Jadwal_pkl_sekprodi/edit/' . $id);
            }

            $jadwal_data = array(
                'id_penguji' => $this->input->post('id_penguji'),
                'tanggal' => $this->input->post('tanggal'),
                'jam_mulai' => $this->input->post('jam_mulai'),
                'jam_selesai' => $this->input->post('jam_selesai'),
                'ruangan' => $this->input->post('ruangan'),
                'status' => $this->input->post('status')
            );

            $this->Jadwal_pkl_model->update($id, $jadwal_data);
            $this->session->set_flashdata('success', 'Jadwal sidang PKL berhasil diperbarui.');
            redirect('Jadwal_pkl_sekprodi');
        }

        $dosen_list = $this->Dosen_model->get_all();
        
        $data = array(
            'title' => 'Edit Jadwal Sidang PKL',
            'page_title' => 'Edit Jadwal Sidang PKL',
            'breadcrumb' => array(
                array('label' => 'PKL', 'url' => '#'),
                array('label' => 'Jadwal Sidang PKL', 'url' => site_url('Jadwal_pkl_sekprodi')),
                array('label' => 'Edit', 'active' => true)
            ),
            'sidebar_menu' => generate_sidebar_menu(get_user_role()),
            'jadwal' => $jadwal,
            'dosen_list' => $dosen_list
        );

        $data['content'] = $this->load->view('sekprodi/jadwal_pkl/edit', $data, true);
        $this->load->view('layouts/main', $data);
    }

    /**
     * Export Excel - Data Jadwal Sidang PKL
     */
    public function export_excel() {
        $tahun_aktif = $this->Tahun_akademik_model->get_aktif();
        $id_tahun_akademik = $tahun_aktif ? $tahun_aktif->id : null;
        
        $status_filter = $this->input->get('status');
        
        // Ambil semua data tanpa pagination
        $jadwal_list = $this->Jadwal_pkl_model->get_all($id_tahun_akademik, $status_filter, null, null);
        
        // Cek apakah PhpSpreadsheet tersedia
        if (class_exists('PhpOffice\PhpSpreadsheet\Spreadsheet')) {
            require_once APPPATH . 'vendor/autoload.php';
            
            $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet();
            
            // Set title
            $sheet->setTitle('Jadwal Sidang PKL');
            
            // Header
            $sheet->setCellValue('A1', 'No');
            $sheet->setCellValue('B1', 'NIM');
            $sheet->setCellValue('C1', 'Nama Mahasiswa');
            $sheet->setCellValue('D1', 'Kelas');
            $sheet->setCellValue('E1', 'Pembimbing');
            $sheet->setCellValue('F1', 'Penguji');
            $sheet->setCellValue('G1', 'Tanggal');
            $sheet->setCellValue('H1', 'Hari');
            $sheet->setCellValue('I1', 'Jam Mulai');
            $sheet->setCellValue('J1', 'Jam Selesai');
            $sheet->setCellValue('K1', 'Ruangan');
            $sheet->setCellValue('L1', 'Status');
            
            // Style header
            $sheet->getStyle('A1:L1')->getFont()->setBold(true);
            $sheet->getStyle('A1:L1')->getFill()
                ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                ->getStartColor()->setARGB('FF4472C4');
            $sheet->getStyle('A1:L1')->getFont()->getColor()->setARGB('FFFFFFFF');
            $sheet->getStyle('A1:L1')->getAlignment()
                ->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER)
                ->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER);
            
            // Data
            $row = 2;
            $no = 1;
            foreach ($jadwal_list as $jadwal) {
                $sheet->setCellValue('A' . $row, $no++);
                $sheet->setCellValue('B' . $row, $jadwal->nim);
                $sheet->setCellValue('C' . $row, $jadwal->nama_mahasiswa);
                $sheet->setCellValue('D' . $row, $jadwal->kelas);
                $sheet->setCellValue('E' . $row, $jadwal->pembimbing_nama ?: '-');
                $sheet->setCellValue('F' . $row, $jadwal->penguji_nama ?: '-');
                $sheet->setCellValue('G' . $row, $jadwal->tanggal ? date('d/m/Y', strtotime($jadwal->tanggal)) : '-');
                $sheet->setCellValue('H' . $row, hari_dari_tanggal($jadwal->tanggal));
                $sheet->setCellValue('I' . $row, $jadwal->jam_mulai ? date('H:i', strtotime($jadwal->jam_mulai)) : '-');
                $sheet->setCellValue('J' . $row, $jadwal->jam_selesai ? date('H:i', strtotime($jadwal->jam_selesai)) : '-');
                $sheet->setCellValue('K' . $row, $jadwal->ruangan ?: '-');
                $sheet->setCellValue('L' . $row, ucfirst($jadwal->status));
                $row++;
            }
            
            // Auto size columns
            foreach (range('A', 'L') as $col) {
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
            $sheet->getStyle('A1:L' . ($row - 1))->applyFromArray($styleArray);
            
            // Set filename
            $filename = 'Jadwal_Sidang_PKL_' . date('Ymd_His') . '.xlsx';
            
            // Download
            header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
            header('Content-Disposition: attachment;filename="' . $filename . '"');
            header('Cache-Control: max-age=0');
            
            $writer = \PhpOffice\PhpSpreadsheet\IOFactory::createWriter($spreadsheet, 'Xlsx');
            $writer->save('php://output');
            exit;
        } else {
            // Fallback: download CSV
            $filename = 'Jadwal_Sidang_PKL_' . date('Ymd_His') . '.csv';
            header('Content-Type: text/csv; charset=utf-8');
            header('Content-Disposition: attachment;filename="' . $filename . '"');
            header('Cache-Control: max-age=0');
            
            $output = fopen('php://output', 'w');
            
            // BOM untuk UTF-8 (agar Excel membaca dengan benar)
            fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));
            
            // Header
            fputcsv($output, array('No', 'NIM', 'Nama Mahasiswa', 'Kelas', 'Pembimbing', 'Penguji', 'Tanggal', 'Hari', 'Jam Mulai', 'Jam Selesai', 'Ruangan', 'Status'));
            
            // Data
            $no = 1;
            foreach ($jadwal_list as $jadwal) {
                fputcsv($output, array(
                    $no++,
                    $jadwal->nim,
                    $jadwal->nama_mahasiswa,
                    $jadwal->kelas,
                    $jadwal->pembimbing_nama ?: '-',
                    $jadwal->penguji_nama ?: '-',
                    $jadwal->tanggal ? date('d/m/Y', strtotime($jadwal->tanggal)) : '-',
                    hari_dari_tanggal($jadwal->tanggal),
                    $jadwal->jam_mulai ? date('H:i', strtotime($jadwal->jam_mulai)) : '-',
                    $jadwal->jam_selesai ? date('H:i', strtotime($jadwal->jam_selesai)) : '-',
                    $jadwal->ruangan ?: '-',
                    ucfirst($jadwal->status)
                ));
            }
            
            fclose($output);
            exit;
        }
    }
}





