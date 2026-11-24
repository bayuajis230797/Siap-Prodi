<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Pkl_absensi Controller (Mahasiswa)
 * Controller untuk absensi harian PKL (berangkat & pulang)
 */
class Pkl_absensi extends MY_Controller {

    public function __construct() {
        parent::__construct();
        $this->check_role('mahasiswa');
        $this->load->model('Pkl_absensi_model');
        $this->load->model('Pkl_tempat_model');
        $this->load->model('Pkl_setting_radius_model');
        $this->load->model('Tahun_akademik_model');
        $this->load->model('Mahasiswa_pkl_model');
        $this->load->helper('pkl_gps');
        $this->load->helper('gps');
        $this->load->library('form_validation');
    }

    /**
     * Index - Halaman absensi harian
     */
    public function index() {
        $id_mahasiswa = $this->session->userdata('ref_id');
        $tahun_aktif = $this->Tahun_akademik_model->get_aktif();
        
        if (!$tahun_aktif) {
            $this->session->set_flashdata('error', 'Tidak ada tahun akademik aktif.');
            redirect('Halaman_utama');
        }

        // Cek apakah mahasiswa terdaftar PKL
        if (!$this->Mahasiswa_pkl_model->is_registered($id_mahasiswa, $tahun_aktif->id)) {
            $this->session->set_flashdata('error', 'Anda belum terdaftar sebagai peserta PKL.');
            redirect('Halaman_utama');
        }

        // Ambil data tempat PKL
        $tempat_pkl = $this->Pkl_tempat_model->get_by_mahasiswa($id_mahasiswa, $tahun_aktif->id);
        
        if (!$tempat_pkl) {
            $this->session->set_flashdata('error', 'Data tempat PKL belum diisi. Silakan isi data tempat PKL terlebih dahulu.');
            redirect('Pkl/input_tempat');
        }

        // Cek apakah koordinat tempat PKL sudah diisi
        if (!$tempat_pkl->latitude || !$tempat_pkl->longitude) {
            $this->session->set_flashdata('error', 'Koordinat tempat PKL belum diisi. Silakan lengkapi data tempat PKL dengan koordinat GPS.');
            redirect('Pkl/input_tempat');
        }

        // Ambil absensi hari ini
        $absensi_hari_ini = $this->Pkl_absensi_model->get_hari_ini($id_mahasiswa);
        $sudah_berangkat = $this->Pkl_absensi_model->sudah_absen_berangkat($id_mahasiswa);
        $sudah_pulang = $this->Pkl_absensi_model->sudah_absen_pulang($id_mahasiswa);

        // Ambil setting radius
        $radius_setting = $this->Pkl_setting_radius_model->get_radius($tahun_aktif->id);

        // Riwayat & statistik — selaras dengan cetak PDF (semua absensi PKL mahasiswa)
        $history_absensi = $this->Pkl_absensi_model->get_by_mahasiswa($id_mahasiswa);
        $statistik = $this->Pkl_absensi_model->get_statistik($id_mahasiswa);

        $data = array(
            'title' => 'Absensi Harian PKL',
            'page_title' => 'Absensi Harian PKL',
            'breadcrumb' => array(
                array('label' => 'PKL', 'url' => site_url('Pkl')),
                array('label' => 'Absensi Harian', 'active' => true)
            ),
            'sidebar_menu' => generate_sidebar_menu('mahasiswa'),
            'tempat_pkl' => $tempat_pkl,
            'absensi_hari_ini' => $absensi_hari_ini,
            'sudah_berangkat' => $sudah_berangkat,
            'sudah_pulang' => $sudah_pulang,
            'radius_setting' => $radius_setting,
            'history_absensi' => $history_absensi,
            'statistik' => $statistik
        );
        
        $data['content'] = $this->load->view('mahasiswa/pkl/absensi', $data, true);
        $this->load->view('layouts/main', $data);
    }

    /**
     * Store - Simpan absensi (berangkat atau pulang)
     */
    public function store() {
        $id_mahasiswa = $this->session->userdata('ref_id');
        $tahun_aktif = $this->Tahun_akademik_model->get_aktif();
        
        if (!$tahun_aktif) {
            $this->output
                ->set_content_type('application/json')
                ->set_output(json_encode(array(
                    'success' => false,
                    'message' => 'Tidak ada tahun akademik aktif.'
                )));
            return;
        }

        // Validasi input
        $this->form_validation->set_rules('tipe_absensi', 'Tipe Absensi', 'required|in_list[berangkat,pulang]');
        $this->form_validation->set_rules('latitude', 'Latitude', 'required|numeric');
        $this->form_validation->set_rules('longitude', 'Longitude', 'required|numeric');
        $this->form_validation->set_rules('tanggal', 'Tanggal', 'required');

        if ($this->form_validation->run() == FALSE) {
            $this->output
                ->set_content_type('application/json')
                ->set_output(json_encode(array(
                    'success' => false,
                    'message' => validation_errors('', '')
                )));
            return;
        }

        $tipe_absensi = $this->input->post('tipe_absensi');
        $tanggal = $this->input->post('tanggal');
        $latitude = (float)$this->input->post('latitude');
        $longitude = (float)$this->input->post('longitude');
        $keterangan = $this->input->post('keterangan');

        // Validasi tanggal (tidak boleh lebih dari hari ini)
        if ($tanggal > date('Y-m-d')) {
            $this->output
                ->set_content_type('application/json')
                ->set_output(json_encode(array(
                    'success' => false,
                    'message' => 'Tanggal absensi tidak boleh lebih dari hari ini.'
                )));
            return;
        }

        // Validasi: tidak bisa absen pulang jika belum absen berangkat
        if ($tipe_absensi === 'pulang') {
            if (!$this->Pkl_absensi_model->sudah_absen_berangkat($id_mahasiswa, $tanggal)) {
                $this->output
                    ->set_content_type('application/json')
                    ->set_output(json_encode(array(
                        'success' => false,
                        'message' => 'Anda belum melakukan absensi berangkat untuk tanggal ini.'
                    )));
                return;
            }
        }

        // Cek apakah sudah absen untuk tipe ini di tanggal yang sama
        if ($tipe_absensi === 'berangkat' && $this->Pkl_absensi_model->sudah_absen_berangkat($id_mahasiswa, $tanggal)) {
            $this->output
                ->set_content_type('application/json')
                ->set_output(json_encode(array(
                    'success' => false,
                    'message' => 'Anda sudah melakukan absensi berangkat untuk tanggal ini.'
                )));
            return;
        }

        if ($tipe_absensi === 'pulang' && $this->Pkl_absensi_model->sudah_absen_pulang($id_mahasiswa, $tanggal)) {
            $this->output
                ->set_content_type('application/json')
                ->set_output(json_encode(array(
                    'success' => false,
                    'message' => 'Anda sudah melakukan absensi pulang untuk tanggal ini.'
                )));
            return;
        }

        // Ambil data tempat PKL
        $tempat_pkl = $this->Pkl_tempat_model->get_by_mahasiswa($id_mahasiswa, $tahun_aktif->id);
        
        if (!$tempat_pkl || !$tempat_pkl->latitude || !$tempat_pkl->longitude) {
            $this->output
                ->set_content_type('application/json')
                ->set_output(json_encode(array(
                    'success' => false,
                    'message' => 'Koordinat tempat PKL belum diisi.'
                )));
            return;
        }

        // Cek radius dan hitung jarak
        $radius_check = check_radius_pkl($latitude, $longitude, $tempat_pkl->id, $tahun_aktif->id);

        // Device Fingerprinting
        $device_fingerprint = $this->input->post('device_fingerprint');
        $device_info = $this->input->post('device_info');
        $is_device_changed = 0;

        // Cek apakah ini absensi berangkat pertama mahasiswa
        // Device referensi utama adalah device saat absen berangkat pertama kali
        $first_berangkat = $this->Pkl_absensi_model->get_first_absensi($id_mahasiswa);
        
        if ($first_berangkat) {
            // Bandingkan dengan fingerprint absensi berangkat pertama
            if ($device_fingerprint && $first_berangkat->device_fingerprint) {
                if ($device_fingerprint !== $first_berangkat->device_fingerprint) {
                    $is_device_changed = 1;
                }
            }
        } else {
            // Jika ini adalah absensi berangkat pertama, set sebagai device utama
            // Jika ini adalah absensi pulang sebelum ada absensi berangkat, tetap set sebagai device utama
            if ($tipe_absensi === 'berangkat') {
                $is_device_changed = 0; // Device utama untuk absensi berangkat pertama
            } else {
                // Absensi pulang sebelum ada absensi berangkat, gunakan sebagai referensi sementara
                $is_device_changed = 0;
            }
        }

        // Simpan absensi
        $data = array(
            'id_mahasiswa' => $id_mahasiswa,
            'id_pkl_tempat' => $tempat_pkl->id,
            'id_tahun_akademik' => $tahun_aktif->id,
            'tanggal' => $tanggal,
            'tipe_absensi' => $tipe_absensi,
            'waktu_absen' => date('Y-m-d H:i:s'),
            'latitude_absen' => $latitude,
            'longitude_absen' => $longitude,
            'jarak_meter' => $radius_check['jarak_meter'],
            'status_radius' => $radius_check['status_radius'],
            'ip_address' => $this->input->ip_address(),
            'user_agent' => $this->input->user_agent(),
            'keterangan' => $keterangan ? trim($keterangan) : null,
            'device_fingerprint' => $device_fingerprint ? $device_fingerprint : null,
            'device_info' => $device_info ? $device_info : null,
            'is_device_changed' => $is_device_changed
        );

        $id_absensi = $this->Pkl_absensi_model->create($data);

        if ($id_absensi) {
            $this->output
                ->set_content_type('application/json')
                ->set_output(json_encode(array(
                    'success' => true,
                    'message' => 'Absensi ' . $tipe_absensi . ' berhasil disimpan.',
                    'data' => array(
                        'id' => $id_absensi,
                        'status_radius' => $radius_check['status_radius'],
                        'jarak_meter' => $radius_check['jarak_meter'],
                        'radius_setting' => $radius_check['radius_setting']
                    )
                )));
        } else {
            $this->output
                ->set_content_type('application/json')
                ->set_output(json_encode(array(
                    'success' => false,
                    'message' => 'Gagal menyimpan absensi.'
                )));
        }
    }

    /**
     * Get history absensi (AJAX)
     */
    public function history() {
        $id_mahasiswa = $this->session->userdata('ref_id');
        $tanggal_mulai = $this->input->get('tanggal_mulai');
        $tanggal_selesai = $this->input->get('tanggal_selesai');

        if ($tanggal_mulai && $tanggal_selesai) {
            $history = $this->Pkl_absensi_model->get_range_tanggal($id_mahasiswa, $tanggal_mulai, $tanggal_selesai);
        } else {
            $history = $this->Pkl_absensi_model->get_by_mahasiswa($id_mahasiswa);
        }

        $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode(array(
                'success' => true,
                'data' => $history
            )));
    }

    /**
     * Cetak Absensi PKL (PDF)
     */
    public function cetak() {
        $id_mahasiswa = $this->session->userdata('ref_id');
        $tahun_aktif = $this->Tahun_akademik_model->get_aktif();
        
        if (!$tahun_aktif) {
            $this->session->set_flashdata('error', 'Tidak ada tahun akademik aktif.');
            redirect('Pkl_absensi');
        }

        // Ambil data mahasiswa
        $this->load->model('Mahasiswa_model');
        $mahasiswa = $this->Mahasiswa_model->get_by_id($id_mahasiswa);
        
        // Ambil semua absensi mahasiswa (sumber data sama dengan tampilan halaman index)
        $absensi_list = $this->Pkl_absensi_model->get_by_mahasiswa($id_mahasiswa);
        
        // Ambil data tempat PKL
        $tempat_pkl = $this->Pkl_tempat_model->get_by_mahasiswa($id_mahasiswa, $tahun_aktif->id);
        
        // Ambil pengajuan PKL
        $this->load->model('Pkl_pengajuan_model');
        $pengajuan = $this->Pkl_pengajuan_model->get_by_mahasiswa($id_mahasiswa, $tahun_aktif->id);
        
        // Statistik absensi (selaras dengan halaman index)
        $statistik = $this->Pkl_absensi_model->get_statistik($id_mahasiswa);

        // Load library PDF dan helper
        $this->load->library('Pdf');
        $this->load->helper('app');
        
        $data = array(
            'mahasiswa' => $mahasiswa,
            'absensi_list' => $absensi_list,
            'tempat_pkl' => $tempat_pkl,
            'pengajuan' => $pengajuan,
            'statistik' => $statistik,
            'tahun_aktif' => $tahun_aktif
        );
        
        // Generate HTML untuk PDF
        $html = $this->load->view('mahasiswa/pkl/cetak_absensi', $data, true);
        
        // Load HTML ke PDF library
        $this->pdf->load_html($html);
        $this->pdf->set_paper('A4', 'portrait');
        
        // Generate filename
        $filename = 'Absensi_PKL_' . $mahasiswa->nim . '_' . date('Ymd') . '.pdf';
        
        // Stream PDF
        $this->pdf->stream($filename);
    }
}

