<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Jadwal Skripsi Controller
 * Mengelola jadwal ujian skripsi (untuk Sekprodi)
 */
class Jadwal_skripsi extends MY_Controller {

    public function __construct() {
        parent::__construct();
        $this->check_role('sekprodi');
        $this->load->model('Jadwal_skripsi_model');
        $this->load->model('Skripsi_model');
        $this->load->model('Dosen_model');
        $this->load->model('Tahun_akademik_model');
        $this->load->model('Skripsi_nilai_sidang_model');
        $this->load->library('Skripsi_penilaian');
        $this->load->library('pagination');
        $this->load->library('form_validation');
    }

    public function index() {
        $tahun_aktif = $this->Tahun_akademik_model->get_aktif();
        $id_tahun_akademik = $tahun_aktif ? $tahun_aktif->id : null;
        
        $status_filter = $this->input->get('status');
        
        // Ambil parameter per_page dari GET, default 10
        $per_page = $this->input->get('per_page') ? (int)$this->input->get('per_page') : 10;
        $per_page = in_array($per_page, [10, 25, 50, 100]) ? $per_page : 10;
        
        $config['base_url'] = site_url('Jadwal_skripsi/index');
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
        
        // Tambahkan status dan per_page ke query string
        $query_params = array_filter(array('status' => $status_filter, 'per_page' => ($per_page != 10 ? $per_page : null)));
        $query_string = http_build_query($query_params);
        if ($query_string) {
            $config['suffix'] = '?' . $query_string;
            $config['first_url'] = site_url('Jadwal_skripsi/index?' . $query_string);
        }
        
        $this->pagination->initialize($config);
        
        $page = ($this->uri->segment(3)) ? $this->uri->segment(3) : 1;
        $offset = ($page - 1) * $config['per_page'];
        
        $data['jadwal_list'] = $this->Jadwal_skripsi_model->get_all($id_tahun_akademik, $status_filter, $config['per_page'], $offset);
        $data['pagination'] = $this->pagination->create_links();
        $data['total_rows'] = $config['total_rows'];
        $data['tahun_aktif'] = $tahun_aktif;
        $data['status_filter'] = $status_filter;
        $data['per_page'] = $per_page;
        
        $data['title'] = 'Jadwal Ujian Skripsi';
        $data['page_title'] = 'Jadwal Ujian Skripsi';
        $data['breadcrumb'] = array(
            array('label' => 'Jadwal Skripsi', 'active' => true)
        );
        $data['sidebar_menu'] = generate_sidebar_menu('sekprodi');
        $data['content'] = $this->load->view('jadwal_skripsi/index', $data, true);
        $this->load->view('layouts/main', $data);
    }

    public function create() {
        $tahun_aktif = $this->Tahun_akademik_model->get_aktif();
        if (!$tahun_aktif) {
            $this->session->set_flashdata('error', 'Tidak ada tahun akademik aktif.');
            redirect('Jadwal_skripsi');
        }

        if ($this->input->method() === 'post') {
            $this->form_validation->set_rules('id_mahasiswa_skripsi', 'Mahasiswa', 'required|integer');
            $this->form_validation->set_rules('id_ketua_penguji', 'Ketua Penguji', 'required|integer');
            $this->form_validation->set_rules('id_anggota_penguji_1', 'Anggota Penguji 1', 'required|integer');
            $this->form_validation->set_rules('tanggal_ujian', 'Tanggal Ujian', 'required');
            $this->form_validation->set_rules('jam_mulai', 'Jam Mulai', 'required');
            $this->form_validation->set_rules('jam_selesai', 'Jam Selesai', 'required');

            if ($this->form_validation->run() === FALSE) {
                $this->session->set_flashdata('error', validation_errors());
                redirect('Jadwal_skripsi/create');
            }

            $id_mahasiswa_skripsi = (int) $this->input->post('id_mahasiswa_skripsi');
            $id_ketua_penguji = (int) $this->input->post('id_ketua_penguji');
            $id_anggota_penguji_1 = (int) $this->input->post('id_anggota_penguji_1');

            // Ambil data skripsi untuk mendapatkan pembimbing 2
            $skripsi = $this->Skripsi_model->get_by_id($id_mahasiswa_skripsi);
            if (!$skripsi || $skripsi->status !== 'layak_sidang') {
                $this->session->set_flashdata('error', 'Mahasiswa belum layak sidang atau data tidak ditemukan.');
                redirect('Jadwal_skripsi/create');
            }

            // Validasi: Pembimbing 1 tidak boleh menjadi Ketua Penguji atau Anggota Penguji 1
            if ($skripsi->id_pembimbing_1 == $id_ketua_penguji || $skripsi->id_pembimbing_1 == $id_anggota_penguji_1) {
                $this->session->set_flashdata('error', 'Pembimbing 1 tidak boleh menjadi Ketua Penguji atau Anggota Penguji 1.');
                redirect('Jadwal_skripsi/create');
            }

            // Pembimbing 2 otomatis menjadi Anggota Penguji 2
            $id_anggota_penguji_2 = $skripsi->id_pembimbing_2;
            if (!$id_anggota_penguji_2) {
                $this->session->set_flashdata('error', 'Pembimbing 2 belum ditetapkan.');
                redirect('Jadwal_skripsi/create');
            }

            // Validasi: Ketua Penguji dan Anggota Penguji 1 tidak boleh sama
            if ($id_ketua_penguji == $id_anggota_penguji_1) {
                $this->session->set_flashdata('error', 'Ketua Penguji dan Anggota Penguji 1 tidak boleh sama.');
                redirect('Jadwal_skripsi/create');
            }

            // Validasi: Ketua Penguji dan Anggota Penguji 2 tidak boleh sama
            if ($id_ketua_penguji == $id_anggota_penguji_2) {
                $this->session->set_flashdata('error', 'Ketua Penguji dan Anggota Penguji 2 tidak boleh sama.');
                redirect('Jadwal_skripsi/create');
            }

            // Validasi: Anggota Penguji 1 dan Anggota Penguji 2 tidak boleh sama
            if ($id_anggota_penguji_1 == $id_anggota_penguji_2) {
                $this->session->set_flashdata('error', 'Anggota Penguji 1 dan Anggota Penguji 2 tidak boleh sama.');
                redirect('Jadwal_skripsi/create');
            }

            // Validasi bentrok jadwal
            $tanggal_ujian = $this->input->post('tanggal_ujian');
            $jam_mulai = $this->input->post('jam_mulai');
            $jam_selesai = $this->input->post('jam_selesai');
            $ruangan = $this->input->post('ruangan');
            
            $bentrok = $this->Jadwal_skripsi_model->check_bentrok(
                $tanggal_ujian,
                $jam_mulai,
                $jam_selesai,
                $ruangan,
                $tahun_aktif->id,
                null,
                array($id_ketua_penguji, $id_anggota_penguji_1, $id_anggota_penguji_2)
            );
            
            if ($bentrok['bentrok_ruang']) {
                $this->load->model('Mahasiswa_model');
                $mhs = $this->Skripsi_model->get_by_id($id_mahasiswa_skripsi);
                $this->session->set_flashdata('error', "Ruangan '{$ruangan}' pada tanggal " . date('d/m/Y', strtotime($tanggal_ujian)) . " jam " . date('H:i', strtotime($jam_mulai)) . "-" . date('H:i', strtotime($jam_selesai)) . " sudah digunakan. Silakan pilih ruangan, tanggal, atau waktu lain.");
                redirect('Jadwal_skripsi/create');
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
                $error_msg = rtrim($error_msg, ', ') . " sudah memiliki jadwal pada waktu tersebut. Silakan pilih waktu lain.";
                $this->session->set_flashdata('error', $error_msg);
                redirect('Jadwal_skripsi/create');
            }

            $data = array(
                'id_mahasiswa_skripsi' => $id_mahasiswa_skripsi,
                'id_ketua_penguji' => $id_ketua_penguji,
                'id_anggota_penguji_1' => $id_anggota_penguji_1,
                'id_anggota_penguji_2' => $id_anggota_penguji_2,
                'tanggal_ujian' => $tanggal_ujian,
                'jam_mulai' => $jam_mulai,
                'jam_selesai' => $jam_selesai,
                'ruangan' => $ruangan,
                'id_tahun_akademik' => $tahun_aktif->id,
                'status' => 'terjadwal',
                'catatan' => $this->input->post('catatan')
            );

            $this->Jadwal_skripsi_model->create($data);
            $this->session->set_flashdata('success', 'Jadwal ujian skripsi berhasil dibuat.');
            redirect('Jadwal_skripsi');
        }

        $data['layak_sidang'] = $this->Jadwal_skripsi_model->get_layak_sidang($tahun_aktif->id);
        $data['dosen_list'] = $this->Dosen_model->get_all(1000, 0);
        $data['tahun_aktif'] = $tahun_aktif;
        
        $data['title'] = 'Tambah Jadwal Ujian Skripsi';
        $data['page_title'] = 'Tambah Jadwal Ujian Skripsi';
        $data['breadcrumb'] = array(
            array('label' => 'Jadwal Skripsi', 'url' => site_url('Jadwal_skripsi')),
            array('label' => 'Tambah', 'active' => true)
        );
        $data['sidebar_menu'] = generate_sidebar_menu('sekprodi');
        $data['content'] = $this->load->view('jadwal_skripsi/create', $data, true);
        $this->load->view('layouts/main', $data);
    }

    public function edit($id) {
        $jadwal = $this->Jadwal_skripsi_model->get_by_id($id);
        if (!$jadwal) {
            $this->session->set_flashdata('error', 'Jadwal tidak ditemukan.');
            redirect('Jadwal_skripsi');
        }

        if ($this->input->method() === 'post') {
            $this->form_validation->set_rules('id_ketua_penguji', 'Ketua Penguji', 'required|integer');
            $this->form_validation->set_rules('id_anggota_penguji_1', 'Anggota Penguji 1', 'required|integer');
            $this->form_validation->set_rules('tanggal_ujian', 'Tanggal Ujian', 'required');
            $this->form_validation->set_rules('jam_mulai', 'Jam Mulai', 'required');
            $this->form_validation->set_rules('jam_selesai', 'Jam Selesai', 'required');

            if ($this->form_validation->run() === FALSE) {
                $this->session->set_flashdata('error', validation_errors());
                redirect('Jadwal_skripsi/edit/' . $id);
            }

            $id_ketua_penguji = (int) $this->input->post('id_ketua_penguji');
            $id_anggota_penguji_1 = (int) $this->input->post('id_anggota_penguji_1');

            // Ambil data skripsi
            $skripsi = $this->Skripsi_model->get_by_id($jadwal->id_mahasiswa_skripsi);
            
            // Validasi: Pembimbing 1 tidak boleh menjadi Ketua Penguji atau Anggota Penguji 1
            if ($skripsi->id_pembimbing_1 == $id_ketua_penguji || $skripsi->id_pembimbing_1 == $id_anggota_penguji_1) {
                $this->session->set_flashdata('error', 'Pembimbing 1 tidak boleh menjadi Ketua Penguji atau Anggota Penguji 1.');
                redirect('Jadwal_skripsi/edit/' . $id);
            }

            // Validasi: Ketua Penguji dan Anggota Penguji 1 tidak boleh sama
            if ($id_ketua_penguji == $id_anggota_penguji_1) {
                $this->session->set_flashdata('error', 'Ketua Penguji dan Anggota Penguji 1 tidak boleh sama.');
                redirect('Jadwal_skripsi/edit/' . $id);
            }

            // Validasi: Ketua Penguji dan Anggota Penguji 2 tidak boleh sama
            if ($id_ketua_penguji == $jadwal->id_anggota_penguji_2) {
                $this->session->set_flashdata('error', 'Ketua Penguji dan Anggota Penguji 2 tidak boleh sama.');
                redirect('Jadwal_skripsi/edit/' . $id);
            }

            // Validasi: Anggota Penguji 1 dan Anggota Penguji 2 tidak boleh sama
            if ($id_anggota_penguji_1 == $jadwal->id_anggota_penguji_2) {
                $this->session->set_flashdata('error', 'Anggota Penguji 1 dan Anggota Penguji 2 tidak boleh sama.');
                redirect('Jadwal_skripsi/edit/' . $id);
            }

            // Validasi bentrok jadwal
            $tanggal_ujian = $this->input->post('tanggal_ujian');
            $jam_mulai = $this->input->post('jam_mulai');
            $jam_selesai = $this->input->post('jam_selesai');
            $ruangan = $this->input->post('ruangan');
            $tahun_aktif = $this->Tahun_akademik_model->get_aktif();
            
            $bentrok = $this->Jadwal_skripsi_model->check_bentrok(
                $tanggal_ujian,
                $jam_mulai,
                $jam_selesai,
                $ruangan,
                $tahun_aktif->id,
                $id, // Exclude jadwal yang sedang diedit
                array($id_ketua_penguji, $id_anggota_penguji_1, $jadwal->id_anggota_penguji_2)
            );
            
            if ($bentrok['bentrok_ruang']) {
                $this->session->set_flashdata('error', "Ruangan '{$ruangan}' pada tanggal " . date('d/m/Y', strtotime($tanggal_ujian)) . " jam " . date('H:i', strtotime($jam_mulai)) . "-" . date('H:i', strtotime($jam_selesai)) . " sudah digunakan. Silakan pilih ruangan, tanggal, atau waktu lain.");
                redirect('Jadwal_skripsi/edit/' . $id);
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
                $error_msg = rtrim($error_msg, ', ') . " sudah memiliki jadwal pada waktu tersebut. Silakan pilih waktu lain.";
                $this->session->set_flashdata('error', $error_msg);
                redirect('Jadwal_skripsi/edit/' . $id);
            }

            $data = array(
                'id_ketua_penguji' => $id_ketua_penguji,
                'id_anggota_penguji_1' => $id_anggota_penguji_1,
                'tanggal_ujian' => $tanggal_ujian,
                'jam_mulai' => $jam_mulai,
                'jam_selesai' => $jam_selesai,
                'ruangan' => $ruangan,
                'status' => $this->input->post('status'),
                'catatan' => $this->input->post('catatan')
            );

            $this->Jadwal_skripsi_model->update($id, $data);
            $this->session->set_flashdata('success', 'Jadwal ujian skripsi berhasil diperbarui.');
            redirect('Jadwal_skripsi');
        }

        $data['jadwal'] = $jadwal;
        $data['dosen_list'] = $this->Dosen_model->get_all(1000, 0);
        $data['tahun_aktif'] = $this->Tahun_akademik_model->get_aktif();
        
        $data['title'] = 'Edit Jadwal Ujian Skripsi';
        $data['page_title'] = 'Edit Jadwal Ujian Skripsi';
        $data['breadcrumb'] = array(
            array('label' => 'Jadwal Skripsi', 'url' => site_url('Jadwal_skripsi')),
            array('label' => 'Edit', 'active' => true)
        );
        $data['sidebar_menu'] = generate_sidebar_menu('sekprodi');
        $data['content'] = $this->load->view('jadwal_skripsi/edit', $data, true);
        $this->load->view('layouts/main', $data);
    }

    public function penilaian($id) {
        $jadwal = $this->Jadwal_skripsi_model->get_by_id($id);
        if (!$jadwal) {
            $this->session->set_flashdata('error', 'Jadwal tidak ditemukan.');
            redirect('Jadwal_skripsi');
        }

        $fields = array(
            'nilai_sistematika' => 'Ketepatan Sistematika dan Konsistensi',
            'nilai_bahasa' => 'Ketepatan Bahasa yang digunakan',
            'nilai_format' => 'Ketepatan Format Penulisan',
            'nilai_penguasaan_isi' => 'Penguasaan Isi Materi Tugas Akhir',
            'nilai_penguasaan_tugas' => 'Penguasaan Isi Tugas Akhir',
            'nilai_penguasaan_konsep' => 'Penguasaan Konsep Perancangan',
            'nilai_penampilan' => 'Penampilan',
            'nilai_penggunaan_alat' => 'Penguasaan Penggunaan Alat',
            'nilai_kesesuaian_waktu' => 'Kesesuaian Penggunaan Waktu',
            'nilai_kejelasan_materi' => 'Kejelasan Mengungkapkan Materi',
            'nilai_penguasaan_produk' => 'Penguasaan Produk Tugas Akhir',
            'nilai_perancangan_produk' => 'Hasil Perancangan Produk'
        );

        if ($this->input->method() === 'post') {
            $peran = $this->input->post('peran');
            $peran_map = array(
                'ketua' => $jadwal->id_ketua_penguji,
                'anggota_1' => $jadwal->id_anggota_penguji_1,
                'anggota_2' => $jadwal->id_anggota_penguji_2
            );

            if (!array_key_exists($peran, $peran_map) || empty($peran_map[$peran])) {
                $this->session->set_flashdata('error', 'Peran penguji tidak valid.');
                redirect('Jadwal_skripsi/penilaian/' . $id);
            }

            foreach ($fields as $field => $label) {
                $this->form_validation->set_rules($field, $label, 'required|numeric|greater_than_equal_to[0]|less_than_equal_to[100]');
            }

            if ($this->form_validation->run() === FALSE) {
                $this->session->set_flashdata('error', validation_errors());
                redirect('Jadwal_skripsi/penilaian/' . $id);
            }

            $nilai_input = array();
            foreach (array_keys($fields) as $field) {
                $nilai_input[$field] = (float) $this->input->post($field);
            }

            $n1 = round(($nilai_input['nilai_sistematika'] + $nilai_input['nilai_bahasa'] + $nilai_input['nilai_format']) / 3, 2);
            $n2 = round(($nilai_input['nilai_penguasaan_isi'] + $nilai_input['nilai_penguasaan_tugas'] + $nilai_input['nilai_penguasaan_konsep']) / 3, 2);
            $n3 = round(($nilai_input['nilai_penampilan'] + $nilai_input['nilai_penggunaan_alat'] + $nilai_input['nilai_kesesuaian_waktu'] + $nilai_input['nilai_kejelasan_materi']) / 4, 2);
            $n4 = round(($nilai_input['nilai_penguasaan_produk'] + $nilai_input['nilai_perancangan_produk']) / 2, 2);
            $nilai_total = round(($n1 + $n2 + $n3 + $n4) / 4, 2);

            $save_data = array_merge($nilai_input, array(
                'id_jadwal_skripsi' => $jadwal->id,
                'id_mahasiswa_skripsi' => $jadwal->id_mahasiswa_skripsi,
                'id_penguji' => $peran_map[$peran],
                'peran' => $peran,
                'n1' => $n1,
                'n2' => $n2,
                'n3' => $n3,
                'n4' => $n4,
                'nilai_total' => $nilai_total,
                'catatan' => $this->input->post('catatan') ? $this->input->post('catatan') : null
            ));

            $this->Skripsi_nilai_sidang_model->upsert($save_data);
            $this->skripsi_penilaian->sync_sidang($jadwal->id_mahasiswa_skripsi);

            $label_peran = ucwords(str_replace('_', ' ', $peran));
            $this->session->set_flashdata('success', 'Nilai ' . $label_peran . ' berhasil disimpan.');
            redirect('Jadwal_skripsi/penilaian/' . $id);
        }

        $nilai = $this->Skripsi_nilai_sidang_model->get_by_jadwal($id);

        $data = array(
            'title' => 'Penilaian Sidang Skripsi',
            'page_title' => 'Penilaian Sidang Skripsi',
            'breadcrumb' => array(
                array('label' => 'Jadwal Skripsi', 'url' => site_url('Jadwal_skripsi')),
                array('label' => 'Penilaian', 'active' => true)
            ),
            'sidebar_menu' => generate_sidebar_menu('sekprodi'),
            'jadwal' => $jadwal,
            'nilai' => $nilai,
            'fields' => $fields
        );

        $data['content'] = $this->load->view('jadwal_skripsi/penilaian', $data, true);
        $this->load->view('layouts/main', $data);
    }

    public function delete($id) {
        $jadwal = $this->Jadwal_skripsi_model->get_by_id($id);
        if (!$jadwal) {
            $this->session->set_flashdata('error', 'Jadwal tidak ditemukan.');
            redirect('Jadwal_skripsi');
        }

        $this->Jadwal_skripsi_model->delete($id);
        $this->session->set_flashdata('success', 'Jadwal ujian skripsi berhasil dihapus.');
        redirect('Jadwal_skripsi');
    }

    /**
     * Export jadwal sidang ke Excel
     */
    public function export_excel() {
        $tahun_aktif = $this->Tahun_akademik_model->get_aktif();
        if (!$tahun_aktif) {
            $this->session->set_flashdata('error', 'Tidak ada tahun akademik aktif.');
            redirect('Jadwal_skripsi');
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
            $sheet->mergeCells('A1:O1');
            $sheet->getStyle('A1')->applyFromArray([
                'font' => ['bold' => true, 'size' => 16],
                'alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER]
            ]);
            
            $sheet->setCellValue('A2', 'Tahun Akademik: ' . $tahun_aktif->tahun_akademik . ' ' . ucfirst($tahun_aktif->semester));
            $sheet->mergeCells('A2:O2');
            
            $sheet->setCellValue('A3', 'Tanggal Export: ' . date('d F Y H:i:s'));
            $sheet->mergeCells('A3:O3');
            
            // Header
            $headerRow = 5;
            $headers = [
                'A' => 'No',
                'B' => 'NIM',
                'C' => 'Nama Mahasiswa',
                'D' => 'Kelas',
                'E' => 'Judul Skripsi',
                'F' => 'Tanggal Ujian',
                'G' => 'Jam Mulai',
                'H' => 'Jam Selesai',
                'I' => 'Ruangan',
                'J' => 'Ketua Penguji',
                'K' => 'NIDN Ketua',
                'L' => 'Anggota Penguji 1',
                'M' => 'NIDN Anggota 1',
                'N' => 'Anggota Penguji 2',
                'O' => 'NIDN Anggota 2',
                'P' => 'Status',
                'Q' => 'Nilai Bimbingan',
                'R' => 'Nilai Sidang',
                'S' => 'Nilai Akhir',
                'T' => 'Grade'
            ];
            
            foreach ($headers as $col => $label) {
                $sheet->setCellValue($col . $headerRow, $label);
            }
            
            $sheet->getStyle('A' . $headerRow . ':T' . $headerRow)->applyFromArray([
                'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                'fill' => ['fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID, 'startColor' => ['rgb' => '1F4E78']],
                'alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER]
            ]);
            
            // Data
            $row = $headerRow + 1;
            $no = 1;
            foreach ($jadwal_list as $jadwal) {
                $sheet->setCellValue('A' . $row, $no++);
                $sheet->setCellValue('B' . $row, $jadwal->nim);
                $sheet->setCellValue('C' . $row, $jadwal->nama_mahasiswa);
                $sheet->setCellValue('D' . $row, $jadwal->kelas);
                $sheet->setCellValue('E' . $row, $jadwal->judul_skripsi);
                $sheet->setCellValue('F' . $row, $jadwal->tanggal_ujian ? date('d/m/Y', strtotime($jadwal->tanggal_ujian)) : '-');
                $sheet->setCellValue('G' . $row, $jadwal->jam_mulai ? date('H:i', strtotime($jadwal->jam_mulai)) : '-');
                $sheet->setCellValue('H' . $row, $jadwal->jam_selesai ? date('H:i', strtotime($jadwal->jam_selesai)) : '-');
                $sheet->setCellValue('I' . $row, $jadwal->ruangan ?: '-');
                $sheet->setCellValue('J' . $row, $jadwal->ketua_penguji ?: '-');
                $sheet->setCellValue('K' . $row, isset($jadwal->ketua_penguji_nidn) ? $jadwal->ketua_penguji_nidn : '-');
                $sheet->setCellValue('L' . $row, $jadwal->anggota_penguji_1 ?: '-');
                $sheet->setCellValue('M' . $row, isset($jadwal->anggota_penguji_1_nidn) ? $jadwal->anggota_penguji_1_nidn : '-');
                $sheet->setCellValue('N' . $row, $jadwal->anggota_penguji_2 ?: '-');
                $sheet->setCellValue('O' . $row, isset($jadwal->anggota_penguji_2_nidn) ? $jadwal->anggota_penguji_2_nidn : '-');
                $sheet->setCellValue('P' . $row, ucfirst($jadwal->status));
                $sheet->setCellValue('Q' . $row, isset($jadwal->nilai_bimbingan_final) && $jadwal->nilai_bimbingan_final !== null ? number_format($jadwal->nilai_bimbingan_final, 2) : '-');
                $sheet->setCellValue('R' . $row, isset($jadwal->nilai_sidang) && $jadwal->nilai_sidang !== null ? number_format($jadwal->nilai_sidang, 2) : '-');
                $sheet->setCellValue('S' . $row, isset($jadwal->nilai_skripsi_final) && $jadwal->nilai_skripsi_final !== null ? number_format($jadwal->nilai_skripsi_final, 2) : '-');
                $sheet->setCellValue('T' . $row, isset($jadwal->nilai_huruf) && $jadwal->nilai_huruf ? $jadwal->nilai_huruf : '-');
                $row++;
            }
            
            // Set column width
            foreach (['A' => 6, 'B' => 15, 'C' => 35, 'D' => 12, 'E' => 50, 'F' => 15, 'G' => 12, 'H' => 12, 'I' => 15, 
                     'J' => 25, 'K' => 15, 'L' => 25, 'M' => 15, 'N' => 25, 'O' => 15, 'P' => 12, 'Q' => 15, 'R' => 15, 'S' => 15, 'T' => 10] as $col => $width) {
                $sheet->getColumnDimension($col)->setWidth($width);
            }
            
            // Alignment
            $sheet->getStyle('A' . ($headerRow + 1) . ':A' . ($row - 1))->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle('D' . ($headerRow + 1) . ':D' . ($row - 1))->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle('F' . ($headerRow + 1) . ':T' . ($row - 1))->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
            
            // Border
            $sheet->getStyle('A' . $headerRow . ':T' . ($row - 1))->applyFromArray([
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
            $sheet->mergeCells('F' . $row . ':T' . $row);
            $sheet->getStyle('A' . $row . ':T' . $row)->getFont()->setBold(true);
            
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
            fputcsv($output, array('No', 'NIM', 'Nama Mahasiswa', 'Kelas', 'Judul Skripsi', 'Tanggal Ujian', 'Jam Mulai', 'Jam Selesai', 'Ruangan', 'Ketua Penguji', 'NIDN Ketua', 'Anggota Penguji 1', 'NIDN Anggota 1', 'Anggota Penguji 2', 'NIDN Anggota 2', 'Status', 'Nilai Bimbingan', 'Nilai Sidang', 'Nilai Akhir', 'Grade'));
            
            // Data
            $no = 1;
            foreach ($jadwal_list as $jadwal) {
                fputcsv($output, array(
                    $no++,
                    $jadwal->nim,
                    $jadwal->nama_mahasiswa,
                    $jadwal->kelas,
                    $jadwal->judul_skripsi,
                    $jadwal->tanggal_ujian ? date('d/m/Y', strtotime($jadwal->tanggal_ujian)) : '-',
                    $jadwal->jam_mulai ? date('H:i', strtotime($jadwal->jam_mulai)) : '-',
                    $jadwal->jam_selesai ? date('H:i', strtotime($jadwal->jam_selesai)) : '-',
                    $jadwal->ruangan ?: '-',
                    $jadwal->ketua_penguji ?: '-',
                    isset($jadwal->ketua_penguji_nidn) ? $jadwal->ketua_penguji_nidn : '-',
                    $jadwal->anggota_penguji_1 ?: '-',
                    isset($jadwal->anggota_penguji_1_nidn) ? $jadwal->anggota_penguji_1_nidn : '-',
                    $jadwal->anggota_penguji_2 ?: '-',
                    isset($jadwal->anggota_penguji_2_nidn) ? $jadwal->anggota_penguji_2_nidn : '-',
                    ucfirst($jadwal->status),
                    isset($jadwal->nilai_bimbingan_final) && $jadwal->nilai_bimbingan_final !== null ? number_format($jadwal->nilai_bimbingan_final, 2, ',', '') : '-',
                    isset($jadwal->nilai_sidang) && $jadwal->nilai_sidang !== null ? number_format($jadwal->nilai_sidang, 2, ',', '') : '-',
                    isset($jadwal->nilai_skripsi_final) && $jadwal->nilai_skripsi_final !== null ? number_format($jadwal->nilai_skripsi_final, 2, ',', '') : '-',
                    isset($jadwal->nilai_huruf) && $jadwal->nilai_huruf ? $jadwal->nilai_huruf : '-'
                ));
            }
            
            fputcsv($output, array()); // Baris kosong
            fputcsv($output, array('TOTAL', '', '', '', '', count($jadwal_list) . ' Jadwal', '', '', '', '', '', '', '', '', '', '', '', '', '', ''));
            
            fclose($output);
            exit;
        }
    }
}
