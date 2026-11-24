<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Countdown Sidang Skripsi Controller
 * Menampilkan countdown sidang skripsi mahasiswa secara realtime (role sekprodi)
 */
class Countdown_sidang_skripsi extends MY_Controller {

    public function __construct() {
        parent::__construct();
        $this->check_role('sekprodi');
        $this->load->model('Jadwal_skripsi_model');
        $this->load->model('Tahun_akademik_model');
    }

    /**
     * Halaman countdown sidang skripsi
     */
    public function index() {
        $tahun_aktif = $this->Tahun_akademik_model->get_aktif();
        $id_tahun_akademik = $tahun_aktif ? $tahun_aktif->id : null;

        // Filter tanggal (opsional)
        $filter_tanggal_mulai = $this->input->get('tanggal_mulai');
        $filter_tanggal_selesai = $this->input->get('tanggal_selesai');

        // Ambil semua jadwal sidang skripsi yang punya tanggal & jam
        $jadwal_list = array();
        if ($id_tahun_akademik) {
            $all_jadwal = $this->Jadwal_skripsi_model->get_all($id_tahun_akademik, null, 500, 0);
            foreach ($all_jadwal as $j) {
                $tanggal = isset($j->tanggal_ujian) ? $j->tanggal_ujian : (isset($j->tanggal) ? $j->tanggal : null);
                if (!$tanggal || !isset($j->jam_mulai)) continue;

                // Filter tanggal jika ada
                if ($filter_tanggal_mulai && strtotime($tanggal) < strtotime($filter_tanggal_mulai)) continue;
                if ($filter_tanggal_selesai && strtotime($tanggal) > strtotime($filter_tanggal_selesai)) continue;

                $ts = strtotime($tanggal . ' ' . $j->jam_mulai);
                $j->jadwal_timestamp = $ts;
                $jadwal_list[] = $j;
            }
            // Urutkan berdasarkan waktu terdekat
            usort($jadwal_list, function($a, $b) {
                return $a->jadwal_timestamp - $b->jadwal_timestamp;
            });
        }

        $data = array(
            'page_title' => 'Countdown Sidang Skripsi',
            'breadcrumb' => array(
                array('label' => 'Halaman Utama', 'url' => site_url('Halaman_utama')),
                array('label' => 'Countdown Sidang Skripsi', 'active' => true)
            ),
            'sidebar_menu' => generate_sidebar_menu('sekprodi'),
            'tahun_aktif' => $tahun_aktif,
            'jadwal_list' => $jadwal_list,
            'filter_tanggal_mulai' => $filter_tanggal_mulai,
            'filter_tanggal_selesai' => $filter_tanggal_selesai
        );

        $data['content'] = $this->load->view('sekprodi/countdown_sidang_skripsi/index', $data, true);
        $this->load->view('layouts/main', $data);
    }
}
