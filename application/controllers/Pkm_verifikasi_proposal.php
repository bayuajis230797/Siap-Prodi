<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Pkm_verifikasi_proposal extends MY_Controller {

    public function __construct() {
        parent::__construct();
        $this->check_role('sekprodi');
        $this->load->model('Pkm_periode_model');
        $this->load->model('Pkm_pengajuan_model');
        $this->load->model('Pkm_anggota_model');
        $this->load->helper(array('menu', 'pkm', 'google_drive'));
    }

    public function index() {
        $status = $this->input->get('status') ?: 'pending';
        $list = $this->Pkm_pengajuan_model->get_for_verifikasi_proposal_final($status === 'all' ? null : $status);

        $data = array(
            'title' => 'Verifikasi Proposal Final PKM',
            'page_title' => 'Verifikasi Proposal Final PKM',
            'breadcrumb' => array(array('label' => 'Verifikasi Proposal Final', 'active' => true)),
            'sidebar_menu' => generate_sidebar_menu('sekprodi'),
            'pengajuan_list' => $list,
            'status_filter' => $status,
        );
        $data['content'] = $this->load->view('sekprodi/pkm_verifikasi_proposal/index', $data, true);
        $this->load->view('layouts/main', $data);
    }

    public function detail($id) {
        $pengajuan = $this->Pkm_pengajuan_model->get_by_id($id);
        if (!$pengajuan || empty($pengajuan->proposal_final_aktif) || empty($pengajuan->link_proposal_final)) {
            show_404();
        }
        $anggota = $this->Pkm_anggota_model->get_by_pengajuan($id);
        $data = array(
            'title' => 'Verifikasi Proposal Final',
            'page_title' => 'Verifikasi Proposal Final — ' . $pengajuan->ketua_nama,
            'breadcrumb' => array(
                array('label' => 'Verifikasi Proposal Final', 'url' => site_url('Pkm_verifikasi_proposal')),
                array('label' => 'Detail', 'active' => true),
            ),
            'sidebar_menu' => generate_sidebar_menu('sekprodi'),
            'pengajuan' => $pengajuan,
            'anggota' => $anggota,
        );
        $data['content'] = $this->load->view('sekprodi/pkm_verifikasi_proposal/detail', $data, true);
        $this->load->view('layouts/main', $data);
    }

    public function proses($id) {
        if ($this->input->method() !== 'post') {
            show_404();
        }
        $pengajuan = $this->Pkm_pengajuan_model->get_by_id($id);
        if (!$pengajuan) {
            redirect('Pkm_verifikasi_proposal');
        }
        $action = $this->input->post('action');
        $catatan = trim($this->input->post('catatan_verifikasi_proposal_final'));

        if ($action === 'setujui') {
            $this->Pkm_pengajuan_model->update($id, array(
                'status_acc_proposal' => 'acc',
                'catatan_verifikasi_proposal_final' => $catatan ?: null,
            ));
            $this->session->set_flashdata('success', 'Proposal final PKM disetujui.');
        } elseif ($action === 'tolak') {
            $this->Pkm_pengajuan_model->update($id, array(
                'status_acc_proposal' => 'revisi',
                'catatan_verifikasi_proposal_final' => $catatan ?: 'Perlu perbaikan proposal final.',
            ));
            $this->session->set_flashdata('success', 'Proposal final ditolak. Mahasiswa dapat mengirim ulang.');
        }
        redirect('Pkm_verifikasi_proposal');
    }

    public function export_mahasiswa_pkm() {
        $periode_ids = $this->_active_periode_ids();
        if (empty($periode_ids)) {
            $this->session->set_flashdata('error', 'Tidak ada periode PKM aktif.');
            redirect('Pkm_verifikasi_proposal');
        }
        $rows = $this->Pkm_pengajuan_model->get_mahasiswa_submit_proposal_export_rows($periode_ids);
        $this->_output_csv(
            'pkm_proposal_final_sudah_submit_' . date('Ymd_His') . '.csv',
            array('NIM', 'Nama Mahasiswa', 'Kelas', 'Peran', 'Periode', 'Jenis PKM', 'Judul PKM', 'NIM Ketua', 'Nama Ketua', 'Status Proposal Final', 'Dosen Pembimbing'),
            $rows,
            function ($r) {
                return array(
                    $r->nim,
                    $r->nama_mahasiswa,
                    $r->kelas,
                    $r->peran,
                    $r->nama_periode,
                    $r->nama_jenis,
                    $r->judul_pkm,
                    $r->ketua_nim,
                    $r->ketua_nama,
                    $r->status_acc_proposal,
                    $r->nama_pembimbing,
                );
            }
        );
    }

    public function export_mahasiswa_non_pkm() {
        $periode_ids = $this->_active_periode_ids();
        if (empty($periode_ids)) {
            $this->session->set_flashdata('error', 'Tidak ada periode PKM aktif.');
            redirect('Pkm_verifikasi_proposal');
        }
        $rows = $this->Pkm_pengajuan_model->get_mahasiswa_belum_submit_proposal_export_rows($periode_ids);
        $this->_output_csv(
            'pkm_proposal_final_belum_submit_' . date('Ymd_His') . '.csv',
            array('NIM', 'Nama Mahasiswa', 'Kelas', 'Peran', 'Periode', 'Jenis PKM', 'Judul PKM', 'NIM Ketua', 'Nama Ketua', 'Dosen Pembimbing'),
            $rows,
            function ($r) {
                return array(
                    $r->nim,
                    $r->nama_mahasiswa,
                    $r->kelas,
                    $r->peran,
                    $r->nama_periode,
                    $r->nama_jenis,
                    $r->judul_pkm,
                    $r->ketua_nim,
                    $r->ketua_nama,
                    $r->nama_pembimbing,
                );
            }
        );
    }

    private function _active_periode_ids() {
        $list = $this->Pkm_periode_model->get_active_periode();
        $ids = array();
        foreach ($list as $p) {
            $ids[] = (int) $p->id;
        }
        return $ids;
    }

    private function _output_csv($filename, array $header, $rows, callable $mapper) {
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename=' . $filename);
        $out = fopen('php://output', 'w');
        fprintf($out, chr(0xEF) . chr(0xBB) . chr(0xBF));
        fputcsv($out, $header);
        foreach ($rows as $row) {
            fputcsv($out, $mapper($row));
        }
        fclose($out);
        exit;
    }
}
