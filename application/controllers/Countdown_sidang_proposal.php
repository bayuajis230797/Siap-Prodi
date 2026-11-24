<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Countdown_sidang_proposal Controller (Sekprodi)
 * Hitung mundur sidang Proposal TA yang belum selesai, diurutkan dari yang terdekat.
 */
class Countdown_sidang_proposal extends MY_Controller {

    public function __construct() {
        parent::__construct();
        $this->check_role('sekprodi');
        $this->load->model('Jadwal_proposal_model');
        $this->load->model('Tahun_akademik_model');
        $this->load->helper('proposal');
    }

    public function index() {
        $tahun_aktif = $this->Tahun_akademik_model->get_aktif();
        $dari = $this->tanggal_valid($this->input->get('tanggal_mulai'));
        $sampai = $this->tanggal_valid($this->input->get('tanggal_selesai'));
        $tampil_selesai = (bool) $this->input->get('selesai');

        $jadwal_list = array();
        if ($tahun_aktif) {
            foreach ($this->Jadwal_proposal_model->get_all($tahun_aktif->id, null, 1000, 0) as $j) {
                if ($j->status === 'batal' || (!$tampil_selesai && $j->status === 'selesai')) { continue; }
                if (!$j->tanggal_ujian || !$j->jam_mulai) { continue; }
                if ($dari && $j->tanggal_ujian < $dari) { continue; }
                if ($sampai && $j->tanggal_ujian > $sampai) { continue; }
                $j->jadwal_timestamp = strtotime($j->tanggal_ujian . ' ' . $j->jam_mulai);
                $jadwal_list[] = $j;
            }
            usort($jadwal_list, function ($a, $b) { return $a->jadwal_timestamp - $b->jadwal_timestamp; });
        }

        $data = array(
            'title' => 'Countdown Sidang Proposal TA',
            'page_title' => 'Countdown Sidang Proposal TA',
            'breadcrumb' => array(
                array('label' => 'Proposal TA', 'url' => '#'),
                array('label' => 'Countdown Sidang', 'active' => true)
            ),
            'sidebar_menu' => generate_sidebar_menu('sekprodi'),
            'tahun_aktif' => $tahun_aktif,
            'jadwal_list' => $jadwal_list,
            'filter_tanggal_mulai' => $dari,
            'filter_tanggal_selesai' => $sampai,
            'tampil_selesai' => $tampil_selesai
        );
        $data['content'] = $this->load->view('sekprodi/countdown_sidang_proposal', $data, true);
        $this->load->view('layouts/main', $data);
    }

    private function tanggal_valid($v) {
        return (is_string($v) && preg_match('/^\d{4}-\d{2}-\d{2}$/', $v) && strtotime($v) !== false) ? $v : null;
    }
}
