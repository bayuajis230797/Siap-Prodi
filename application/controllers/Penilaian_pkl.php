<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Penilaian_pkl Controller
 * Admin menginput nilai P1-P6 untuk mahasiswa PKL
 */
class Penilaian_pkl extends MY_Controller {

    public function __construct() {
        parent::__construct();
        $this->check_role('admin');
        $this->load->model('Pkl_pengajuan_model');
        $this->load->model('Pkl_penilaian_model');
        $this->load->model('Pkl_aspek_penilaian_model');
        $this->load->model('Pkl_item_penilaian_model');
        $this->load->model('Tahun_akademik_model');
        $this->load->library('form_validation');
    }

    /**
     * Daftar penilaian PKL
     */
    public function index() {
        $tahun_aktif = $this->Tahun_akademik_model->get_aktif();
        if (!$tahun_aktif) {
            $this->session->set_flashdata('error', 'Tidak ada tahun akademik aktif.');
            redirect('Dashboard');
        }

        // Ambil semua pengajuan PKL yang judul sudah di-ACC
        $pengajuan_list = $this->Pkl_pengajuan_model->get_all($tahun_aktif->id, 'disetujui');
        
        // Filter hanya yang judul sudah di-ACC (bukan placeholder)
        $mahasiswa_list = array();
        foreach ($pengajuan_list as $pengajuan) {
            if ($pengajuan->judul_pkl && $pengajuan->judul_pkl !== $this->Pkl_pengajuan_model::PLACEHOLDER_JUDUL 
                && $pengajuan->status_acc_judul === 'acc') {
                // Ambil penilaian jika ada
                $penilaian = $this->Pkl_penilaian_model->get_by_mahasiswa($pengajuan->id_mahasiswa, $tahun_aktif->id);
                $pengajuan->penilaian = !empty($penilaian) ? $penilaian[0] : null;
                $mahasiswa_list[] = $pengajuan;
            }
        }

        $data = array(
            'title' => 'Penilaian PKL',
            'page_title' => 'Penilaian PKL',
            'breadcrumb' => array(
                array('label' => 'Penilaian PKL', 'active' => true)
            ),
            'sidebar_menu' => generate_sidebar_menu('admin'),
            'mahasiswa_list' => $mahasiswa_list,
            'tahun_aktif' => $tahun_aktif
        );

        $data['content'] = $this->load->view('admin/penilaian_pkl_index', $data, true);
        $this->load->view('layouts/main', $data);
    }

    /**
     * Input/Edit nilai P1-P6
     */
    public function input_nilai($pengajuan_id) {
        $tahun_aktif = $this->Tahun_akademik_model->get_aktif();
        if (!$tahun_aktif) {
            $this->session->set_flashdata('error', 'Tidak ada tahun akademik aktif.');
            redirect('Penilaian_pkl');
        }

        // Ambil pengajuan PKL
        $pengajuan = $this->Pkl_pengajuan_model->get_by_id($pengajuan_id);
        if (!$pengajuan) {
            show_404();
        }

        // Cek apakah judul sudah di-ACC
        if ($pengajuan->status_acc_judul !== 'acc') {
            $this->session->set_flashdata('error', 'Judul PKL belum disetujui.');
            redirect('Penilaian_pkl');
        }

        // Ambil semua aspek penilaian (P1-P6)
        $aspek_list = $this->Pkl_aspek_penilaian_model->get_all(true);
        
        // Urutkan berdasarkan urutan
        usort($aspek_list, function($a, $b) {
            return $a->urutan <=> $b->urutan;
        });

        // Cek apakah sudah ada penilaian dari sekprodi
        $id_penilai = $this->session->userdata('user_id');
        $penilaian_admin = $this->Pkl_penilaian_model->get_by_mahasiswa_penilai(
            $pengajuan->id_mahasiswa, 
            $tahun_aktif->id, 
            $id_penilai, 
            'sekprodi'
        );

        if ($this->input->method() === 'post') {
            // Validasi input nilai
            $this->form_validation->set_rules('nilai_p1', 'Nilai P1', 'required|numeric|greater_than_equal_to[0]|less_than_equal_to[100]');
            $this->form_validation->set_rules('nilai_p2', 'Nilai P2', 'required|numeric|greater_than_equal_to[0]|less_than_equal_to[100]');
            $this->form_validation->set_rules('nilai_p3', 'Nilai P3', 'required|numeric|greater_than_equal_to[0]|less_than_equal_to[100]');
            $this->form_validation->set_rules('nilai_p4', 'Nilai P4', 'required|numeric|greater_than_equal_to[0]|less_than_equal_to[100]');
            $this->form_validation->set_rules('nilai_p5', 'Nilai P5', 'required|numeric|greater_than_equal_to[0]|less_than_equal_to[100]');
            $this->form_validation->set_rules('nilai_p6', 'Nilai P6', 'required|numeric|greater_than_equal_to[0]|less_than_equal_to[100]');

            if ($this->form_validation->run() == FALSE) {
                $this->session->set_flashdata('error', validation_errors());
            } else {
                // Hitung nilai akhir: (P1 + P2 + P3 + P4 + P5 + P6) / 6
                $nilai_p1 = (float)$this->input->post('nilai_p1');
                $nilai_p2 = (float)$this->input->post('nilai_p2');
                $nilai_p3 = (float)$this->input->post('nilai_p3');
                $nilai_p4 = (float)$this->input->post('nilai_p4');
                $nilai_p5 = (float)$this->input->post('nilai_p5');
                $nilai_p6 = (float)$this->input->post('nilai_p6');
                
                $nilai_akhir = round(($nilai_p1 + $nilai_p2 + $nilai_p3 + $nilai_p4 + $nilai_p5 + $nilai_p6) / 6, 2);
                
                // Konversi ke nilai huruf
                $nilai_huruf = $this->konversi_nilai_huruf($nilai_akhir);

                // Ambil user_id admin sebagai id_penilai
                $id_penilai = $this->session->userdata('user_id');

                // Simpan atau update penilaian
                $penilaian_data = array(
                    'id_mahasiswa' => $pengajuan->id_mahasiswa,
                    'id_tahun_akademik' => $tahun_aktif->id,
                    'id_penilai' => $id_penilai,
                    'tipe_penilai' => 'sekprodi',
                    'nilai_p1' => $nilai_p1,
                    'nilai_p2' => $nilai_p2,
                    'nilai_p3' => $nilai_p3,
                    'nilai_p4' => $nilai_p4,
                    'nilai_p5' => $nilai_p5,
                    'nilai_p6' => $nilai_p6,
                    'nilai_akhir' => $nilai_akhir,
                    'nilai_huruf' => $nilai_huruf,
                    'tanggal_penilaian' => date('Y-m-d')
                );

                if ($penilaian_admin) {
                    // Update
                    $this->Pkl_penilaian_model->update($penilaian_admin->id, $penilaian_data);
                    $this->session->set_flashdata('success', 'Nilai berhasil diperbarui.');
                } else {
                    // Create
                    $this->Pkl_penilaian_model->create($penilaian_data);
                    $this->session->set_flashdata('success', 'Nilai berhasil disimpan.');
                }

                redirect('Penilaian_pkl');
            }
        }

        $data = array(
            'title' => 'Input Nilai PKL',
            'page_title' => 'Input Nilai PKL',
            'breadcrumb' => array(
                array('label' => 'Penilaian PKL', 'url' => site_url('Penilaian_pkl')),
                array('label' => 'Input Nilai', 'active' => true)
            ),
            'sidebar_menu' => generate_sidebar_menu('admin'),
            'pengajuan' => $pengajuan,
            'aspek_list' => $aspek_list,
            'penilaian' => $penilaian_admin,
            'tahun_aktif' => $tahun_aktif
        );

        $data['content'] = $this->load->view('admin/penilaian_pkl_form', $data, true);
        $this->load->view('layouts/main', $data);
    }

    /**
     * Konversi nilai angka ke huruf
     * Menggunakan setting dari database
     */
    private function konversi_nilai_huruf($nilai) {
        $this->load->model('Grade_setting_model');
        return $this->Grade_setting_model->konversi_nilai_ke_grade($nilai, 'pkl');
    }
}

