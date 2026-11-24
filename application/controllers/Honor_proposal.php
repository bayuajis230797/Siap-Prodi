<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Honor_proposal Controller (Admin, Sekprodi, Kaprodi)
 * Rekap honor pembimbing dan penguji Proposal TA per dosen.
 * Besaran diatur di Honor_setup (item pembimbing_proposal, penguji_1_proposal, penguji_2_proposal).
 * Honor pembimbing = jumlah mahasiswa bimbingan x besaran; penguji = jumlah sidang x besaran per peran.
 */
class Honor_proposal extends MY_Controller {

    public function __construct() {
        parent::__construct();
        $this->check_role(array('admin', 'sekprodi', 'kaprodi'));
        $this->load->model('Honor_proposal_model');
        $this->load->model('Honor_setup_model');
        $this->load->model('Tahun_akademik_model');
        $this->load->model('Dosen_model');
    }

    /** @return array [besaran, rekap[], total] */
    private function hitung($id_tahun) {
        $b = array(
            'pembimbing' => (float) $this->Honor_setup_model->get_besaran('pembimbing_proposal', $id_tahun),
            'penguji_1' => (float) $this->Honor_setup_model->get_besaran('penguji_1_proposal', $id_tahun),
            'penguji_2' => (float) $this->Honor_setup_model->get_besaran('penguji_2_proposal', $id_tahun),
        );
        $pemb = $this->Honor_proposal_model->get_jumlah_pembimbing($id_tahun);
        $p1 = $this->Honor_proposal_model->get_jumlah_penguji('id_penguji_1', $id_tahun);
        $p2 = $this->Honor_proposal_model->get_jumlah_penguji('id_penguji_2', $id_tahun);

        $ids = array_unique(array_merge(array_keys($pemb), array_keys($p1), array_keys($p2)));
        $rekap = array();
        $total = 0;
        foreach ($ids as $id) {
            $src = isset($pemb[$id]) ? $pemb[$id] : (isset($p1[$id]) ? $p1[$id] : $p2[$id]);
            $r = (object) array(
                'id_dosen' => $id,
                'nama_dosen' => $src->nama_dosen,
                'nidn' => $src->nidn,
                'jumlah_bimbingan' => isset($pemb[$id]) ? (int) $pemb[$id]->jumlah_bimbingan : 0,
                'jumlah_penguji_1' => isset($p1[$id]) ? (int) $p1[$id]->jumlah : 0,
                'jumlah_penguji_2' => isset($p2[$id]) ? (int) $p2[$id]->jumlah : 0,
            );
            $r->honor_pembimbing = $r->jumlah_bimbingan * $b['pembimbing'];
            $r->honor_penguji_1 = $r->jumlah_penguji_1 * $b['penguji_1'];
            $r->honor_penguji_2 = $r->jumlah_penguji_2 * $b['penguji_2'];
            $r->total_honor = $r->honor_pembimbing + $r->honor_penguji_1 + $r->honor_penguji_2;
            $total += $r->total_honor;
            $rekap[] = $r;
        }
        usort($rekap, function ($x, $y) { return strcmp($x->nama_dosen, $y->nama_dosen); });
        return array($b, $rekap, $total);
    }

    public function index() {
        $tahun_aktif = $this->Tahun_akademik_model->get_aktif();
        list($besaran, $rekap, $total) = $this->hitung($tahun_aktif ? $tahun_aktif->id : null);

        $data = array(
            'title' => 'Honor Proposal TA',
            'page_title' => 'Honor Proposal TA',
            'breadcrumb' => array(
                array('label' => 'Honor', 'url' => '#'),
                array('label' => 'Proposal TA', 'active' => true)
            ),
            'sidebar_menu' => generate_sidebar_menu(get_user_role()),
            'tahun_aktif' => $tahun_aktif,
            'besaran' => $besaran,
            'rekap' => $rekap,
            'total' => $total
        );
        $data['content'] = $this->load->view('honor/proposal', $data, true);
        $this->load->view('layouts/main', $data);
    }

    public function detail($id_dosen) {
        $dosen = $this->Dosen_model->get_by_id($id_dosen);
        if (!$dosen) {
            $this->session->set_flashdata('error', 'Data dosen tidak ditemukan.');
            redirect('Honor_proposal');
        }
        $tahun_aktif = $this->Tahun_akademik_model->get_aktif();
        $id_tahun = $tahun_aktif ? $tahun_aktif->id : null;

        $data = array(
            'title' => 'Detail Honor Proposal TA',
            'page_title' => 'Detail Honor Proposal TA',
            'breadcrumb' => array(
                array('label' => 'Honor Proposal TA', 'url' => site_url('Honor_proposal')),
                array('label' => 'Detail', 'active' => true)
            ),
            'sidebar_menu' => generate_sidebar_menu(get_user_role()),
            'tahun_aktif' => $tahun_aktif,
            'dosen' => $dosen,
            'bimbingan' => $this->Honor_proposal_model->get_mahasiswa_bimbingan($id_dosen, $id_tahun),
            'sidang' => $this->Honor_proposal_model->get_sidang_penguji($id_dosen, $id_tahun)
        );
        $data['content'] = $this->load->view('honor/proposal_detail', $data, true);
        $this->load->view('layouts/main', $data);
    }

    public function export_csv() {
        $tahun_aktif = $this->Tahun_akademik_model->get_aktif();
        if (!$tahun_aktif) {
            $this->session->set_flashdata('error', 'Tahun akademik aktif tidak ditemukan.');
            redirect('Honor_proposal');
        }
        $rows = $this->baris_export($tahun_aktif);

        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment;filename="Honor_Proposal_TA_' . date('YmdHis') . '.csv"');
        header('Cache-Control: max-age=0');
        $out = fopen('php://output', 'w');
        fprintf($out, chr(0xEF) . chr(0xBB) . chr(0xBF));
        foreach ($rows as $r) { fputcsv($out, $r); }
        fclose($out);
        exit;
    }

    /** Baris CSV (dipisah agar dapat diuji). */
    protected function baris_export($tahun_aktif) {
        list($b, $rekap, $total) = $this->hitung($tahun_aktif->id);
        $rows = array(
            array('HONOR PROPOSAL TA'),
            array('Tahun Akademik: ' . $tahun_aktif->tahun_akademik . ' ' . ucfirst($tahun_aktif->semester)),
            array('Besaran: Pembimbing ' . $b['pembimbing'] . ' | Penguji 1 ' . $b['penguji_1'] . ' | Penguji 2 ' . $b['penguji_2']),
            array(),
            array('No', 'NIDN', 'Nama Dosen', 'Jml Bimbingan', 'Honor Pembimbing', 'Jml Penguji 1', 'Honor Penguji 1', 'Jml Penguji 2', 'Honor Penguji 2', 'Total Honor')
        );
        $no = 1;
        foreach ($rekap as $r) {
            $rows[] = array($no++, $r->nidn, $r->nama_dosen, $r->jumlah_bimbingan, $r->honor_pembimbing,
                $r->jumlah_penguji_1, $r->honor_penguji_1, $r->jumlah_penguji_2, $r->honor_penguji_2, $r->total_honor);
        }
        $rows[] = array();
        $rows[] = array('TOTAL', '', '', '', '', '', '', '', '', $total);
        return $rows;
    }
}
