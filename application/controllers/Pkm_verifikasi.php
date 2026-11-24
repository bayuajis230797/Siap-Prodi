<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Pkm_verifikasi extends MY_Controller {

    public function __construct() {
        parent::__construct();
        $this->check_role('sekprodi');
        $this->load->model('Pkm_periode_model');
        $this->load->model('Pkm_pengajuan_model');
        $this->load->model('Pkm_anggota_model');
        $this->load->model('Dosen_model');
        $this->load->helper(array('menu', 'pkm', 'google_drive'));
        $this->load->library('form_validation');
    }

    public function index() {
        $status = $this->input->get('status') ?: 'pending';
        $periode_aktif_list = $this->Pkm_periode_model->get_active_periode();
        $periode_ids = array();
        foreach ($periode_aktif_list as $p) {
            $periode_ids[] = (int) $p->id;
        }

        $pengajuan_list = array();
        if (!empty($periode_ids)) {
            $pengajuan_list = $this->Pkm_pengajuan_model->get_for_verifikasi_by_periode_ids(
                $periode_ids,
                $status === 'all' ? null : $status
            );
            foreach ($pengajuan_list as $p) {
                $p->anggota = $this->Pkm_anggota_model->get_by_pengajuan($p->id);
                $p->jumlah_anggota = count($p->anggota);
            }
        }

        $data = array(
            'title' => 'Verifikasi PKM',
            'page_title' => 'Verifikasi Pengajuan PKM',
            'breadcrumb' => array(array('label' => 'Verifikasi PKM', 'active' => true)),
            'sidebar_menu' => generate_sidebar_menu('sekprodi'),
            'periode_aktif_list' => $periode_aktif_list,
            'pengajuan_list' => $pengajuan_list,
            'status_filter' => $status,
            'dosen_list' => $this->Dosen_model->get_all(),
        );
        $data['content'] = $this->load->view('sekprodi/pkm_verifikasi/index', $data, true);
        $this->load->view('layouts/main', $data);
    }

    public function detail($id) {
        $pengajuan = $this->Pkm_pengajuan_model->get_by_id($id);
        if (!$pengajuan) {
            $this->session->set_flashdata('error', 'Pengajuan tidak ditemukan.');
            redirect('Pkm_verifikasi');
        }
        $anggota = $this->Pkm_anggota_model->get_by_pengajuan($id);
        $data = array(
            'title' => 'Verifikasi PKM',
            'page_title' => 'Verifikasi — ' . $pengajuan->ketua_nama,
            'breadcrumb' => array(
                array('label' => 'Verifikasi PKM', 'url' => site_url('Pkm_verifikasi')),
                array('label' => 'Detail', 'active' => true),
            ),
            'sidebar_menu' => generate_sidebar_menu('sekprodi'),
            'pengajuan' => $pengajuan,
            'anggota' => $anggota,
            'dosen_list' => $this->Dosen_model->get_all(),
        );
        $data['content'] = $this->load->view('sekprodi/pkm_verifikasi/detail', $data, true);
        $this->load->view('layouts/main', $data);
    }

    public function proses($id) {
        if ($this->input->method() !== 'post') {
            show_404();
        }
        $pengajuan = $this->Pkm_pengajuan_model->get_by_id($id);
        if (!$pengajuan) {
            $this->session->set_flashdata('error', 'Pengajuan tidak ditemukan.');
            redirect('Pkm_verifikasi');
        }
        $action = $this->input->post('action');
        $catatan = trim($this->input->post('catatan_sekprodi'));
        $id_pembimbing = (int) $this->input->post('id_pembimbing_final');

        if ($action === 'tolak') {
            $this->Pkm_pengajuan_model->update($id, array(
                'status_verifikasi_sekprodi' => 'ditolak',
                'catatan_sekprodi' => $catatan ?: null,
                'reviewed_sekprodi_by' => $this->session->userdata('user_id'),
                'reviewed_sekprodi_at' => date('Y-m-d H:i:s'),
            ));
            $this->session->set_flashdata('success', 'Pengajuan PKM ditolak.');
            redirect('Pkm_verifikasi');
        }

        if ($action === 'setujui') {
            if ($id_pembimbing <= 0) {
                $this->session->set_flashdata('error', 'Pilih dosen pembimbing final.');
                redirect('Pkm_verifikasi/detail/' . $id);
            }
            $anggota = $this->Pkm_anggota_model->get_by_pengajuan($id);
            $jumlah = count($anggota);
            if ($jumlah < (int) $pengajuan->min_anggota || $jumlah > (int) $pengajuan->max_anggota) {
                $this->session->set_flashdata('error', 'Jumlah anggota (' . $jumlah . ') tidak sesuai ketentuan jenis lomba (' . $pengajuan->min_anggota . '–' . $pengajuan->max_anggota . ').');
                redirect('Pkm_verifikasi/detail/' . $id);
            }

            $this->Pkm_pengajuan_model->update($id, array(
                'status_verifikasi_sekprodi' => 'disetujui',
                'id_pembimbing_final' => $id_pembimbing,
                'catatan_sekprodi' => $catatan ?: null,
                'reviewed_sekprodi_by' => $this->session->userdata('user_id'),
                'reviewed_sekprodi_at' => date('Y-m-d H:i:s'),
            ));
            $this->session->set_flashdata('success', 'Pengajuan disetujui. Dosen pembimbing dapat memverifikasi judul.');
            redirect('Pkm_verifikasi');
        }

        redirect('Pkm_verifikasi/detail/' . $id);
    }

    public function export_mahasiswa_pkm() {
        $periode_ids = $this->_active_periode_ids();
        if (empty($periode_ids)) {
            $this->session->set_flashdata('error', 'Tidak ada periode PKM aktif.');
            redirect('Pkm_verifikasi');
        }
        $rows = $this->Pkm_pengajuan_model->get_mahasiswa_terdaftar_export_rows($periode_ids);
        $this->_output_csv(
            'pkm_mahasiswa_terdaftar_' . date('Ymd_His') . '.csv',
            array('NIM', 'Nama Mahasiswa', 'Kelas', 'Peran', 'Periode', 'Jenis PKM', 'Judul PKM', 'NIM Ketua', 'Nama Ketua', 'Status Verifikasi Sekprodi', 'Dosen Pembimbing'),
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
                    $r->status_verifikasi_sekprodi,
                    $r->nama_pembimbing,
                );
            }
        );
    }

    public function export_mahasiswa_non_pkm() {
        $periode_ids = $this->_active_periode_ids();
        if (empty($periode_ids)) {
            $this->session->set_flashdata('error', 'Tidak ada periode PKM aktif.');
            redirect('Pkm_verifikasi');
        }
        $rows = $this->Pkm_pengajuan_model->get_mahasiswa_non_terdaftar_export_rows($periode_ids);
        $this->_output_csv(
            'pkm_mahasiswa_non_terdaftar_' . date('Ymd_His') . '.csv',
            array('NIM', 'Nama Mahasiswa', 'Kelas'),
            $rows,
            function ($r) {
                return array($r->nim, $r->nama_mahasiswa, $r->kelas);
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
