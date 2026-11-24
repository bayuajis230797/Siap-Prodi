<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Kegiatan extends MY_Controller {

    public function __construct() {
        parent::__construct();
        $this->check_role('sekprodi');
        $this->load->model('Kegiatan_model');
        $this->load->model('Kelas_model');
        $this->load->model('Mahasiswa_model');
        $this->load->helper('form');
        $this->load->model('Absensi_model');
        $this->load->helper('qrcode');
        $this->load->helper('gps');
    }

    /**
     * Index - Daftar kegiatan
     */
    public function index() {
        // Auto-update status semua kegiatan sebelum tampilkan
        // Update per kegiatan untuk memastikan logic yang benar
        $all_kegiatan = $this->Kegiatan_model->get_all();
        foreach ($all_kegiatan as $kegiatan) {
            $this->Kegiatan_model->auto_update_status($kegiatan->id);
        }
        
        // Pagination
        $this->load->library('pagination');
        $config['base_url'] = site_url('Kegiatan');
        $config['total_rows'] = $this->Kegiatan_model->count_all();
        $config['per_page'] = 10;
        $config['uri_segment'] = 2;
        
        $this->pagination->initialize($config);
        
        $page = $this->uri->segment(2) ? (int)$this->uri->segment(2) : 0;
        $kegiatan_list = $this->Kegiatan_model->get_all($config['per_page'], $page);
        
        // Ambil statistik untuk setiap kegiatan
        foreach ($kegiatan_list as $kegiatan) {
            $statistik = $this->Kegiatan_model->get_statistik_absensi($kegiatan->id);
            $kegiatan->statistik = $statistik;
        }
        
        $data = array(
            'title' => 'Manajemen Kegiatan',
            'page_title' => 'Manajemen Kegiatan',
            'breadcrumb' => array(array('label' => 'Kegiatan', 'active' => true)),
            'sidebar_menu' => generate_sidebar_menu('sekprodi'),
            'kegiatan_list' => $kegiatan_list,
            'pagination' => $this->pagination->create_links()
        );
        
        $data['content'] = $this->load->view('sekprodi/kegiatan/index', $data, true);
        $this->load->view('layouts/main', $data);
    }

    /**
     * Create - Form buat kegiatan baru
     */
    public function create() {
        $data = array(
            'title' => 'Tambah Kegiatan Baru',
            'page_title' => 'Tambah Kegiatan Baru',
            'breadcrumb' => array(
                array('label' => 'Kegiatan', 'url' => site_url('Kegiatan')),
                array('label' => 'Tambah Baru', 'active' => true)
            ),
            'sidebar_menu' => generate_sidebar_menu('sekprodi'),
            'kelas_list' => $this->Kelas_model->get_all_kelas()
        );
        
        $data['content'] = $this->load->view('sekprodi/kegiatan/create', $data, true);
        $this->load->view('layouts/main', $data);
    }

    /**
     * Store - Simpan kegiatan baru
     */
    public function store() {
        $this->form_validation->set_rules('nama_kegiatan', 'Nama Kegiatan', 'required|trim');
        $this->form_validation->set_rules('tanggal_kegiatan', 'Tanggal Kegiatan', 'required');
        $this->form_validation->set_rules('jam_mulai', 'Jam Mulai', 'required');
        $this->form_validation->set_rules('latitude', 'Latitude', 'trim');
        $this->form_validation->set_rules('longitude', 'Longitude', 'trim');
        
        if ($this->form_validation->run() == FALSE) {
            $this->create();
        } else {
            $latitude = trim((string) $this->input->post('latitude'));
            $longitude = trim((string) $this->input->post('longitude'));
            if ($latitude !== '' && !is_numeric($latitude)) {
                $this->session->set_flashdata('error', 'Latitude harus berupa angka.');
                redirect('Kegiatan/create');
            }
            if ($longitude !== '' && !is_numeric($longitude)) {
                $this->session->set_flashdata('error', 'Longitude harus berupa angka.');
                redirect('Kegiatan/create');
            }
            // Generate QR Code token
            $qr_token = bin2hex(random_bytes(32));
            
            // Generate QR Code
            $qr_data = site_url('Absensi/scan/' . $qr_token);
            $qr_filename = 'kegiatan_' . time() . '_' . uniqid();
            $qr_path = generate_qr_code($qr_data, $qr_filename);
            
            $data = array(
                'nama_kegiatan' => $this->input->post('nama_kegiatan'),
                'deskripsi' => $this->input->post('deskripsi'),
                'tanggal_kegiatan' => $this->input->post('tanggal_kegiatan'),
                'jam_mulai' => $this->input->post('jam_mulai'),
                'jam_selesai' => $this->input->post('jam_selesai') ? $this->input->post('jam_selesai') : null,
                'lokasi' => $this->input->post('lokasi'),
                'latitude' => $latitude !== '' ? $latitude : null,
                'longitude' => $longitude !== '' ? $longitude : null,
                'radius_meter' => $this->input->post('radius_meter') ? (int)$this->input->post('radius_meter') : 20,
                'kuota_peserta' => $this->input->post('kuota_peserta') ? (int)$this->input->post('kuota_peserta') : null,
                'jenis_kegiatan' => $this->input->post('jenis_kegiatan'),
                'qr_code' => $qr_path,
                'qr_code_token' => $qr_token,
                'status' => 'draft',
                'created_by' => $this->session->userdata('user_id')
            );
            
            $id_kegiatan = $this->Kegiatan_model->create($data);
            
            if ($id_kegiatan) {
                // Tambah kelas jika dipilih
                $kelas_selected = $this->input->post('kelas');
                if (!empty($kelas_selected) && is_array($kelas_selected)) {
                    $this->Kegiatan_model->bulk_add_kelas($id_kegiatan, $kelas_selected);
                }
                
                // Tambah peserta jika dipilih
                $peserta_selected = $this->input->post('peserta');
                if (!empty($peserta_selected) && is_array($peserta_selected)) {
                    $this->Kegiatan_model->bulk_add_peserta($id_kegiatan, $peserta_selected);
                }
                
                $this->session->set_flashdata('success', 'Kegiatan berhasil dibuat.');
                redirect('Kegiatan/detail/' . $id_kegiatan);
            } else {
                $this->session->set_flashdata('error', 'Gagal membuat kegiatan.');
                redirect('Kegiatan/create');
            }
        }
    }

    /**
     * Detail - Detail kegiatan
     */
    public function detail($id) {
        // Auto-update status kegiatan terlebih dahulu
        $this->Kegiatan_model->auto_update_status($id);
        
        // Ambil data kegiatan setelah update
        $kegiatan = $this->Kegiatan_model->get_by_id($id);
        
        if (!$kegiatan) {
            $this->session->set_flashdata('error', 'Data kegiatan tidak ditemukan.');
            redirect('Kegiatan');
        }
        
        // Ambil kelas dan peserta
        $kegiatan->kelas = $this->Kegiatan_model->get_kelas_by_kegiatan($id);
        $kegiatan->peserta = $this->Kegiatan_model->get_peserta_by_kegiatan($id);
        $kegiatan->statistik = $this->Kegiatan_model->get_statistik_absensi($id);
        
        // Ambil absensi
        $kegiatan->absensi = $this->Absensi_model->get_absensi_by_kegiatan($id);
        
        $data = array(
            'title' => 'Detail Kegiatan',
            'page_title' => 'Detail Kegiatan',
            'breadcrumb' => array(
                array('label' => 'Kegiatan', 'url' => site_url('Kegiatan')),
                array('label' => 'Detail', 'active' => true)
            ),
            'sidebar_menu' => generate_sidebar_menu('sekprodi'),
            'kegiatan' => $kegiatan
        );
        
        $data['content'] = $this->load->view('sekprodi/kegiatan/detail', $data, true);
        $this->load->view('layouts/main', $data);
    }

    /**
     * Edit - Form edit kegiatan
     */
    public function edit($id) {
        $kegiatan = $this->Kegiatan_model->get_by_id($id);
        
        if (!$kegiatan) {
            $this->session->set_flashdata('error', 'Data kegiatan tidak ditemukan.');
            redirect('Kegiatan');
        }
        
        // Ambil kelas dan peserta
        $kegiatan->kelas = $this->Kegiatan_model->get_kelas_by_kegiatan($id);
        $kegiatan->peserta = $this->Kegiatan_model->get_peserta_by_kegiatan($id);
        
        $data = array(
            'title' => 'Edit Kegiatan',
            'page_title' => 'Edit Kegiatan',
            'breadcrumb' => array(
                array('label' => 'Kegiatan', 'url' => site_url('Kegiatan')),
                array('label' => 'Edit', 'active' => true)
            ),
            'sidebar_menu' => generate_sidebar_menu('sekprodi'),
            'kegiatan' => $kegiatan,
            'kelas_list' => $this->Kelas_model->get_all_kelas()
        );
        
        $data['content'] = $this->load->view('sekprodi/kegiatan/edit', $data, true);
        $this->load->view('layouts/main', $data);
    }

    /**
     * Update - Update kegiatan
     */
    public function update($id) {
        $this->form_validation->set_rules('nama_kegiatan', 'Nama Kegiatan', 'required|trim');
        $this->form_validation->set_rules('tanggal_kegiatan', 'Tanggal Kegiatan', 'required');
        $this->form_validation->set_rules('jam_mulai', 'Jam Mulai', 'required');
        $this->form_validation->set_rules('latitude', 'Latitude', 'trim');
        $this->form_validation->set_rules('longitude', 'Longitude', 'trim');
        
        if ($this->form_validation->run() == FALSE) {
            $this->edit($id);
        } else {
            $latitude = trim((string) $this->input->post('latitude'));
            $longitude = trim((string) $this->input->post('longitude'));
            if ($latitude !== '' && !is_numeric($latitude)) {
                $this->session->set_flashdata('error', 'Latitude harus berupa angka.');
                redirect('Kegiatan/edit/' . $id);
            }
            if ($longitude !== '' && !is_numeric($longitude)) {
                $this->session->set_flashdata('error', 'Longitude harus berupa angka.');
                redirect('Kegiatan/edit/' . $id);
            }
            $data = array(
                'nama_kegiatan' => $this->input->post('nama_kegiatan'),
                'deskripsi' => $this->input->post('deskripsi'),
                'tanggal_kegiatan' => $this->input->post('tanggal_kegiatan'),
                'jam_mulai' => $this->input->post('jam_mulai'),
                'jam_selesai' => $this->input->post('jam_selesai') ? $this->input->post('jam_selesai') : null,
                'lokasi' => $this->input->post('lokasi'),
                'latitude' => $latitude !== '' ? $latitude : null,
                'longitude' => $longitude !== '' ? $longitude : null,
                'radius_meter' => $this->input->post('radius_meter') ? (int)$this->input->post('radius_meter') : 20,
                'kuota_peserta' => $this->input->post('kuota_peserta') ? (int)$this->input->post('kuota_peserta') : null,
                'jenis_kegiatan' => $this->input->post('jenis_kegiatan'),
                'status' => $this->input->post('status')
            );
            
            $result = $this->Kegiatan_model->update($id, $data);
            
            if ($result) {
                // Update kelas
                $kelas_selected = $this->input->post('kelas');
                if (!empty($kelas_selected) && is_array($kelas_selected)) {
                    $this->Kegiatan_model->bulk_add_kelas($id, $kelas_selected);
                } else {
                    $this->Kegiatan_model->delete_kelas($id);
                }
                
                // Update peserta
                $peserta_selected = $this->input->post('peserta');
                if (!empty($peserta_selected) && is_array($peserta_selected)) {
                    $this->Kegiatan_model->bulk_add_peserta($id, $peserta_selected);
                } else {
                    $this->Kegiatan_model->delete_peserta($id);
                }
                
                $this->session->set_flashdata('success', 'Kegiatan berhasil diupdate.');
                redirect('Kegiatan/detail/' . $id);
            } else {
                $this->session->set_flashdata('error', 'Gagal update kegiatan.');
                redirect('Kegiatan/edit/' . $id);
            }
        }
    }

    /**
     * Delete - Hapus kegiatan
     */
    public function delete($id) {
        $kegiatan = $this->Kegiatan_model->get_by_id($id);
        
        if ($kegiatan && $kegiatan->qr_code) {
            delete_qr_code($kegiatan->qr_code);
        }
        
        $result = $this->Kegiatan_model->delete($id);
        
        if ($result) {
            $this->session->set_flashdata('success', 'Kegiatan berhasil dihapus.');
        } else {
            $this->session->set_flashdata('error', 'Gagal menghapus kegiatan.');
        }
        
        redirect('Kegiatan');
    }

    /**
     * Ubah Status - Quick change status
     */
    public function ubah_status() {
        $id_kegiatan = $this->input->post('id_kegiatan');
        $status_baru = $this->input->post('status');
        
        if (!$id_kegiatan || !$status_baru) {
            echo json_encode(array(
                'success' => false,
                'message' => 'Parameter tidak lengkap.'
            ));
            return;
        }
        
        // Validasi status
        $allowed_status = array('draft', 'aktif', 'selesai', 'dibatalkan');
        if (!in_array($status_baru, $allowed_status)) {
            echo json_encode(array(
                'success' => false,
                'message' => 'Status tidak valid.'
            ));
            return;
        }
        
        // Cek apakah kegiatan ada
        $kegiatan = $this->Kegiatan_model->get_by_id($id_kegiatan);
        if (!$kegiatan) {
            echo json_encode(array(
                'success' => false,
                'message' => 'Kegiatan tidak ditemukan.'
            ));
            return;
        }
        
        // Update status
        $result = $this->Kegiatan_model->update($id_kegiatan, array('status' => $status_baru));
        
        if ($result) {
            $status_labels = array(
                'draft' => 'Draft',
                'aktif' => 'Aktif',
                'selesai' => 'Selesai',
                'dibatalkan' => 'Dibatalkan'
            );
            echo json_encode(array(
                'success' => true,
                'message' => 'Status berhasil diubah menjadi "' . $status_labels[$status_baru] . '".'
            ));
        } else {
            echo json_encode(array(
                'success' => false,
                'message' => 'Gagal mengubah status.'
            ));
        }
    }

    /**
     * Reset QR Code - Generate QR Code baru
     */
    public function reset_qrcode($id) {
        $kegiatan = $this->Kegiatan_model->get_by_id($id);
        
        if (!$kegiatan) {
            echo json_encode(array('success' => false, 'message' => 'Kegiatan tidak ditemukan.'));
            return;
        }
        
        // Hapus QR Code lama
        if ($kegiatan->qr_code) {
            delete_qr_code($kegiatan->qr_code);
        }
        
        // Generate QR Code baru
        $qr_token = bin2hex(random_bytes(32));
        $qr_data = site_url('Absensi/scan/' . $qr_token);
        $qr_filename = 'kegiatan_' . $id . '_' . time() . '_' . uniqid();
        $qr_path = generate_qr_code($qr_data, $qr_filename);
        
        // Update database
        $this->Kegiatan_model->update($id, array(
            'qr_code' => $qr_path,
            'qr_code_token' => $qr_token
        ));
        
        echo json_encode(array(
            'success' => true,
            'qr_path' => base_url($qr_path),
            'message' => 'QR Code berhasil direset.'
        ));
    }

    /**
     * Get Mahasiswa by Kelas - AJAX (GET/POST)
     */
    public function get_mahasiswa_by_kelas() {
        $kelas = trim((string) $this->input->get('kelas'));
        if ($kelas === '') {
            $kelas = trim((string) $this->input->post('kelas'));
        }

        if ($kelas === '') {
            $this->output
                ->set_content_type('application/json')
                ->set_output(json_encode(array('success' => false, 'message' => 'Kelas tidak dipilih.')));
            return;
        }

        $mahasiswa_list = $this->Mahasiswa_model->get_by_kelas($kelas, 'aktif');

        $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode(array(
                'success' => true,
                'mahasiswa' => $mahasiswa_list,
            )));
    }

    /**
     * Laporan - Laporan absensi kegiatan
     */
    public function laporan($id) {
        $kegiatan = $this->Kegiatan_model->get_by_id($id);
        
        if (!$kegiatan) {
            $this->session->set_flashdata('error', 'Data kegiatan tidak ditemukan.');
            redirect('Kegiatan');
        }
        
        // Ambil semua peserta dan absensi
        $kegiatan->peserta = $this->Kegiatan_model->get_peserta_by_kegiatan($id);
        $kegiatan->absensi = $this->Absensi_model->get_absensi_by_kegiatan($id);
        $kegiatan->statistik = $this->Kegiatan_model->get_statistik_absensi($id);
        
        // Mapping absensi by mahasiswa
        $absensi_map = array();
        foreach ($kegiatan->absensi as $absen) {
            $absensi_map[$absen->id_mahasiswa] = $absen;
        }
        
        // Set status absensi untuk setiap peserta
        foreach ($kegiatan->peserta as $peserta) {
            if (isset($absensi_map[$peserta->id_mahasiswa])) {
                $peserta->absensi = $absensi_map[$peserta->id_mahasiswa];
                $peserta->status_absen = $absensi_map[$peserta->id_mahasiswa]->status;
            } else {
                $peserta->absensi = null;
                $peserta->status_absen = 'tidak_hadir';
            }
        }
        
        $data = array(
            'title' => 'Laporan Absensi Kegiatan',
            'page_title' => 'Laporan Absensi Kegiatan',
            'breadcrumb' => array(
                array('label' => 'Kegiatan', 'url' => site_url('Kegiatan')),
                array('label' => 'Laporan', 'active' => true)
            ),
            'sidebar_menu' => generate_sidebar_menu('sekprodi'),
            'kegiatan' => $kegiatan
        );
        
        $data['content'] = $this->load->view('sekprodi/kegiatan/laporan', $data, true);
        $this->load->view('layouts/main', $data);
    }

    /**
     * Cetak Kehadiran - Cetak laporan kehadiran kegiatan
     */
    public function cetak_kehadiran($id) {
        $kegiatan = $this->Kegiatan_model->get_by_id($id);
        
        if (!$kegiatan) {
            $this->session->set_flashdata('error', 'Data kegiatan tidak ditemukan.');
            redirect('Kegiatan');
        }
        
        // Ambil semua peserta dan absensi
        $kegiatan->peserta = $this->Kegiatan_model->get_peserta_by_kegiatan($id);
        $kegiatan->absensi = $this->Absensi_model->get_absensi_by_kegiatan($id);
        $kegiatan->statistik = $this->Kegiatan_model->get_statistik_absensi($id);
        
        // Mapping absensi by mahasiswa
        $absensi_map = array();
        foreach ($kegiatan->absensi as $absen) {
            $absensi_map[$absen->id_mahasiswa] = $absen;
        }
        
        // Set status absensi untuk setiap peserta
        foreach ($kegiatan->peserta as $peserta) {
            if (isset($absensi_map[$peserta->id_mahasiswa])) {
                $peserta->absensi = $absensi_map[$peserta->id_mahasiswa];
                $peserta->status_absen = $absensi_map[$peserta->id_mahasiswa]->status;
            } else {
                $peserta->absensi = null;
                $peserta->status_absen = 'tidak_hadir';
            }
        }
        
        // Load helper untuk kop surat
        $this->load->helper('app');
        
        $data = array(
            'kegiatan' => $kegiatan
        );
        
        $this->load->view('sekprodi/kegiatan/cetak_kehadiran', $data);
    }

    /**
     * Export Excel - Export laporan absensi kegiatan ke Excel
     */
    public function export_excel($id) {
        $kegiatan = $this->Kegiatan_model->get_by_id($id);
        
        if (!$kegiatan) {
            $this->session->set_flashdata('error', 'Data kegiatan tidak ditemukan.');
            redirect('Kegiatan');
        }
        
        // Ambil semua peserta dan absensi
        $kegiatan->peserta = $this->Kegiatan_model->get_peserta_by_kegiatan($id);
        $kegiatan->absensi = $this->Absensi_model->get_absensi_by_kegiatan($id);
        $kegiatan->statistik = $this->Kegiatan_model->get_statistik_absensi($id);
        
        // Mapping absensi by mahasiswa
        $absensi_map = array();
        foreach ($kegiatan->absensi as $absen) {
            $absensi_map[$absen->id_mahasiswa] = $absen;
        }
        
        // Set status absensi untuk setiap peserta
        foreach ($kegiatan->peserta as $peserta) {
            if (isset($absensi_map[$peserta->id_mahasiswa])) {
                $peserta->absensi = $absensi_map[$peserta->id_mahasiswa];
                $peserta->status_absen = $absensi_map[$peserta->id_mahasiswa]->status;
            } else {
                $peserta->absensi = null;
                $peserta->status_absen = 'tidak_hadir';
            }
        }
        
        // Cek apakah PhpSpreadsheet tersedia
        $use_phpspreadsheet = false;
        if (file_exists(APPPATH . '../vendor/autoload.php')) {
            require_once APPPATH . '../vendor/autoload.php';
            if (class_exists('PhpOffice\PhpSpreadsheet\Spreadsheet')) {
                $use_phpspreadsheet = true;
            }
        }
        
        $filename_base = 'Laporan_Absensi_Kegiatan_' . preg_replace('/[^a-zA-Z0-9_]/', '_', $kegiatan->nama_kegiatan) . '_' . date('YmdHis');
        
        if ($use_phpspreadsheet) {
            // Gunakan PhpSpreadsheet
            $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet();
            
            // Set timezone
            date_default_timezone_set('Asia/Jakarta');
            
            // Header informasi kegiatan
            $sheet->setCellValue('A1', 'LAPORAN ABSENSI KEGIATAN');
            $sheet->mergeCells('A1:H1');
            $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14);
            $sheet->getStyle('A1')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
            
            $row = 3;
            $sheet->setCellValue('A' . $row, 'Nama Kegiatan:');
            $sheet->setCellValue('B' . $row, $kegiatan->nama_kegiatan);
            $sheet->getStyle('A' . $row)->getFont()->setBold(true);
            
            $row++;
            $sheet->setCellValue('A' . $row, 'Tanggal:');
            $sheet->setCellValue('B' . $row, date('d F Y', strtotime($kegiatan->tanggal_kegiatan)));
            $sheet->getStyle('A' . $row)->getFont()->setBold(true);
            
            $row++;
            $sheet->setCellValue('A' . $row, 'Waktu:');
            $waktu = date('H:i', strtotime($kegiatan->jam_mulai)) . ' WIB';
            if ($kegiatan->jam_selesai) {
                $waktu .= ' - ' . date('H:i', strtotime($kegiatan->jam_selesai)) . ' WIB';
            }
            $sheet->setCellValue('B' . $row, $waktu);
            $sheet->getStyle('A' . $row)->getFont()->setBold(true);
            
            if ($kegiatan->lokasi) {
                $row++;
                $sheet->setCellValue('A' . $row, 'Lokasi:');
                $sheet->setCellValue('B' . $row, $kegiatan->lokasi);
                $sheet->getStyle('A' . $row)->getFont()->setBold(true);
            }
            
            // Statistik
            if (isset($kegiatan->statistik)) {
                $row += 2;
                $sheet->setCellValue('A' . $row, 'STATISTIK');
                $sheet->mergeCells('A' . $row . ':H' . $row);
                $sheet->getStyle('A' . $row)->getFont()->setBold(true)->setSize(12);
                
                $row++;
                $sheet->setCellValue('A' . $row, 'Total Peserta:');
                $sheet->setCellValue('B' . $row, $kegiatan->statistik->total_peserta);
                $sheet->setCellValue('D' . $row, 'Hadir:');
                $sheet->setCellValue('E' . $row, $kegiatan->statistik->hadir);
                $sheet->setCellValue('G' . $row, 'Terlambat:');
                $sheet->setCellValue('H' . $row, $kegiatan->statistik->terlambat);
                
                $row++;
                $sheet->setCellValue('A' . $row, 'Tidak Hadir:');
                $sheet->setCellValue('B' . $row, $kegiatan->statistik->tidak_hadir);
            }
            
            // Header tabel
            $row += 2;
            $header_row = $row;
            $sheet->setCellValue('A' . $row, 'No');
            $sheet->setCellValue('B' . $row, 'NIM');
            $sheet->setCellValue('C' . $row, 'Nama Mahasiswa');
            $sheet->setCellValue('D' . $row, 'Kelas');
            $sheet->setCellValue('E' . $row, 'Waktu Absen');
            $sheet->setCellValue('F' . $row, 'Jarak (m)');
            $sheet->setCellValue('G' . $row, 'Status');
            $sheet->setCellValue('H' . $row, 'Keterangan');
            
            // Style header
            $header_style = array(
                'font' => array('bold' => true, 'color' => array('rgb' => 'FFFFFF')),
                'fill' => array(
                    'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                    'startColor' => array('rgb' => '4472C4')
                ),
                'alignment' => array('horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER),
                'borders' => array(
                    'allBorders' => array(
                        'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN
                    )
                )
            );
            $sheet->getStyle('A' . $header_row . ':H' . $header_row)->applyFromArray($header_style);
            
            // Data peserta
            $row++;
            $no = 1;
            foreach ($kegiatan->peserta as $peserta) {
                $sheet->setCellValue('A' . $row, $no++);
                $sheet->setCellValue('B' . $row, $peserta->nim);
                $sheet->setCellValue('C' . $row, $peserta->nama_mahasiswa);
                $sheet->setCellValue('D' . $row, $peserta->kelas);
                
                // Waktu absen
                if (isset($peserta->absensi) && $peserta->absensi) {
                    $sheet->setCellValue('E' . $row, date('d/m/Y H:i', strtotime($peserta->absensi->waktu_absen)));
                    
                    // Jarak
                    if ($peserta->absensi->jarak_meter) {
                        $sheet->setCellValue('F' . $row, number_format($peserta->absensi->jarak_meter, 2));
                    } else {
                        $sheet->setCellValue('F' . $row, '-');
                    }
                    
                    // Status
                    $status_labels = array(
                        'hadir' => 'Hadir',
                        'terlambat' => 'Terlambat',
                        'tidak_hadir' => 'Tidak Hadir'
                    );
                    $status = $status_labels[$peserta->status_absen] ?? 'Tidak Hadir';
                    $sheet->setCellValue('G' . $row, $status);
                    
                    // Keterangan
                    $keterangan = $peserta->absensi->keterangan ? $peserta->absensi->keterangan : '-';
                    $sheet->setCellValue('H' . $row, $keterangan);
                } else {
                    $sheet->setCellValue('E' . $row, '-');
                    $sheet->setCellValue('F' . $row, '-');
                    $sheet->setCellValue('G' . $row, 'Tidak Hadir');
                    $sheet->setCellValue('H' . $row, '-');
                }
                
                // Border untuk setiap baris
                $sheet->getStyle('A' . $row . ':H' . $row)->applyFromArray(array(
                    'borders' => array(
                        'allBorders' => array(
                            'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN
                        )
                    )
                ));
                
                $row++;
            }
            
            // Auto size columns
            foreach (range('A', 'H') as $col) {
                $sheet->getColumnDimension($col)->setAutoSize(true);
            }
            
            // Set width untuk kolom tertentu
            $sheet->getColumnDimension('C')->setWidth(30); // Nama Mahasiswa
            $sheet->getColumnDimension('E')->setWidth(18); // Waktu Absen
            $sheet->getColumnDimension('H')->setWidth(25); // Keterangan
            
            // Export
            $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
            $filename = $filename_base . '.xlsx';
            
            header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
            header('Content-Disposition: attachment;filename="' . $filename . '"');
            header('Cache-Control: max-age=0');
            
            $writer->save('php://output');
            exit;
        } else {
            // Fallback: Export CSV
            $filename = $filename_base . '.csv';
            
            header('Content-Type: text/csv; charset=utf-8');
            header('Content-Disposition: attachment;filename="' . $filename . '"');
            header('Cache-Control: max-age=0');
            
            $output = fopen('php://output', 'w');
            
            // BOM untuk UTF-8
            fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));
            
            // Header informasi
            fputcsv($output, array('LAPORAN ABSENSI KEGIATAN'));
            fputcsv($output, array(''));
            fputcsv($output, array('Nama Kegiatan:', $kegiatan->nama_kegiatan));
            fputcsv($output, array('Tanggal:', date('d F Y', strtotime($kegiatan->tanggal_kegiatan))));
            $waktu = date('H:i', strtotime($kegiatan->jam_mulai)) . ' WIB';
            if ($kegiatan->jam_selesai) {
                $waktu .= ' - ' . date('H:i', strtotime($kegiatan->jam_selesai)) . ' WIB';
            }
            fputcsv($output, array('Waktu:', $waktu));
            if ($kegiatan->lokasi) {
                fputcsv($output, array('Lokasi:', $kegiatan->lokasi));
            }
            
            // Statistik
            if (isset($kegiatan->statistik)) {
                fputcsv($output, array(''));
                fputcsv($output, array('STATISTIK'));
                fputcsv($output, array('Total Peserta:', $kegiatan->statistik->total_peserta));
                fputcsv($output, array('Hadir:', $kegiatan->statistik->hadir));
                fputcsv($output, array('Terlambat:', $kegiatan->statistik->terlambat));
                fputcsv($output, array('Tidak Hadir:', $kegiatan->statistik->tidak_hadir));
            }
            
            // Header tabel
            fputcsv($output, array(''));
            fputcsv($output, array('No', 'NIM', 'Nama Mahasiswa', 'Kelas', 'Waktu Absen', 'Jarak (m)', 'Status', 'Keterangan'));
            
            // Data peserta
            $no = 1;
            foreach ($kegiatan->peserta as $peserta) {
                $waktu_absen = '-';
                $jarak = '-';
                $status = 'Tidak Hadir';
                $keterangan = '-';
                
                if (isset($peserta->absensi) && $peserta->absensi) {
                    $waktu_absen = date('d/m/Y H:i', strtotime($peserta->absensi->waktu_absen));
                    if ($peserta->absensi->jarak_meter) {
                        $jarak = number_format($peserta->absensi->jarak_meter, 2);
                    }
                    
                    $status_labels = array(
                        'hadir' => 'Hadir',
                        'terlambat' => 'Terlambat',
                        'tidak_hadir' => 'Tidak Hadir'
                    );
                    $status = $status_labels[$peserta->status_absen] ?? 'Tidak Hadir';
                    $keterangan = $peserta->absensi->keterangan ? $peserta->absensi->keterangan : '-';
                }
                
                fputcsv($output, array(
                    $no++,
                    $peserta->nim,
                    $peserta->nama_mahasiswa,
                    $peserta->kelas,
                    $waktu_absen,
                    $jarak,
                    $status,
                    $keterangan
                ));
            }
            
            fclose($output);
            exit;
        }
    }
}

