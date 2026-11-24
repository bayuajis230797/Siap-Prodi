<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Penilaian_sidang Controller
 * Menangani penilaian sidang oleh dosen (penguji)
 */
class Penilaian_sidang extends MY_Controller {

    public function __construct() {
        parent::__construct();
        $this->check_role('dosen');
        $this->load->model('Jadwal_skripsi_model');
        $this->load->model('Skripsi_nilai_sidang_model');
        $this->load->model('Tahun_akademik_model');
        $this->load->library('Skripsi_penilaian');
        $this->load->library('form_validation');
    }

    /**
     * Index - daftar jadwal sidang yang perlu dinilai
     */
    public function index() {
        $id_dosen = $this->session->userdata('ref_id');
        $dosen = $this->db->get_where('dosen', array('id' => $id_dosen))->row();
        
        if (!$dosen) {
            $this->session->set_flashdata('error', 'Data dosen tidak ditemukan.');
            redirect('Dashboard');
        }

        $tahun_aktif = $this->Tahun_akademik_model->get_aktif();
        if (!$tahun_aktif) {
            $this->session->set_flashdata('error', 'Tidak ada tahun akademik aktif.');
            redirect('Dashboard');
        }

        // Ambil jadwal sidang dimana dosen ini adalah penguji
        $jadwal_list = $this->Jadwal_skripsi_model->get_by_penguji($dosen->id, $tahun_aktif->id);

        $data = array(
            'title' => 'Penilaian Sidang Skripsi',
            'page_title' => 'Penilaian Sidang Skripsi',
            'breadcrumb' => array(
                array('label' => 'Skripsi', 'url' => site_url('Skripsi_review_judul')),
                array('label' => 'Penilaian Sidang', 'active' => true)
            ),
            'sidebar_menu' => generate_sidebar_menu('dosen'),
            'jadwal_list' => $jadwal_list,
            'tahun_aktif' => $tahun_aktif,
            'dosen_id' => $dosen->id
        );

        $data['content'] = $this->load->view('penilaian_sidang/index', $data, true);
        $this->load->view('layouts/main', $data);
    }

    /**
     * Form penilaian sidang
     */
    public function nilai($id) {
        $id_dosen = $this->session->userdata('ref_id');
        $dosen = $this->db->get_where('dosen', array('id' => $id_dosen))->row();
        
        if (!$dosen) {
            $this->session->set_flashdata('error', 'Data dosen tidak ditemukan.');
            redirect('Dashboard');
        }

        $jadwal = $this->Jadwal_skripsi_model->get_by_id($id);
        if (!$jadwal) {
            $this->session->set_flashdata('error', 'Jadwal sidang tidak ditemukan.');
            redirect('Penilaian_sidang');
        }

        // Cek apakah dosen ini adalah penguji
        $peran = null;
        if ($jadwal->id_ketua_penguji == $dosen->id) {
            $peran = 'ketua';
        } elseif ($jadwal->id_anggota_penguji_1 == $dosen->id) {
            $peran = 'anggota_1';
        } elseif ($jadwal->id_anggota_penguji_2 == $dosen->id) {
            $peran = 'anggota_2';
        } else {
            $this->session->set_flashdata('error', 'Anda bukan penguji untuk sidang ini.');
            redirect('Penilaian_sidang');
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
            foreach ($fields as $field => $label) {
                $this->form_validation->set_rules($field, $label, 'required|numeric|greater_than_equal_to[0]|less_than_equal_to[100]');
            }

            if ($this->form_validation->run() === FALSE) {
                $this->session->set_flashdata('error', validation_errors());
                redirect('Penilaian_sidang/nilai/' . $id);
            }

            $nilai_input = array();
            foreach (array_keys($fields) as $field) {
                $nilai_input[$field] = (float) $this->input->post($field);
            }

            // Hitung N1, N2, N3, N4 sesuai rumus
            $n1 = round(($nilai_input['nilai_sistematika'] + $nilai_input['nilai_bahasa'] + $nilai_input['nilai_format']) / 3, 2);
            $n2 = round(($nilai_input['nilai_penguasaan_isi'] + $nilai_input['nilai_penguasaan_tugas'] + $nilai_input['nilai_penguasaan_konsep']) / 3, 2);
            $n3 = round(($nilai_input['nilai_penampilan'] + $nilai_input['nilai_penggunaan_alat'] + $nilai_input['nilai_kesesuaian_waktu'] + $nilai_input['nilai_kejelasan_materi']) / 4, 2);
            $n4 = round(($nilai_input['nilai_penguasaan_produk'] + $nilai_input['nilai_perancangan_produk']) / 2, 2);
            $nilai_total = round(($n1 + $n2 + $n3 + $n4) / 4, 2);

            $save_data = array_merge($nilai_input, array(
                'id_jadwal_skripsi' => $jadwal->id,
                'id_mahasiswa_skripsi' => $jadwal->id_mahasiswa_skripsi,
                'id_penguji' => $dosen->id,
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
            redirect('Penilaian_sidang/nilai/' . $id);
        }

        // Ambil nilai yang sudah ada
        $nilai_existing = $this->Skripsi_nilai_sidang_model->get_by_jadwal_penguji($id, $dosen->id);

        $data = array(
            'title' => 'Penilaian Sidang Skripsi',
            'page_title' => 'Penilaian Sidang Skripsi',
            'breadcrumb' => array(
                array('label' => 'Penilaian Sidang', 'url' => site_url('Penilaian_sidang')),
                array('label' => 'Input Nilai', 'active' => true)
            ),
            'sidebar_menu' => generate_sidebar_menu('dosen'),
            'jadwal' => $jadwal,
            'nilai' => $nilai_existing,
            'fields' => $fields,
            'peran' => $peran
        );

        $data['content'] = $this->load->view('penilaian_sidang/form', $data, true);
        $this->load->view('layouts/main', $data);
    }
}

