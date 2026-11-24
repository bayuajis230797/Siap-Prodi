<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Skpi_verifikasi extends MY_Controller {

    public function __construct() {
        parent::__construct();
        $this->check_role('sekprodi');
        $this->load->model('Skpi_periode_model');
        $this->load->model('Skpi_peserta_model');
        $this->load->model('Skpi_upload_model');
        $this->load->helper(array('menu', 'google_drive', 'notifikasi'));
        $this->load->library('form_validation');
    }

    public function index() {
        $id_periode = (int) $this->input->get('id_periode');
        $kelas = $this->input->get('kelas');
        $periode_list = $this->Skpi_periode_model->get_all(null);
        $peserta_list = array();
        $periode = null;
        if ($id_periode > 0) {
            $periode = $this->Skpi_periode_model->get_by_id($id_periode);
            if ($periode) {
                $peserta_list = $this->Skpi_peserta_model->get_by_periode($id_periode, $kelas ?: null);
                foreach ($peserta_list as $p) {
                    $uploads = $this->Skpi_upload_model->get_by_mahasiswa_periode($id_periode, $p->id_mahasiswa);
                    $p->total_upload = count($uploads);
                    $p->pending = 0;
                    $p->disetujui = 0;
                    $p->ditolak = 0;
                    foreach ($uploads as $u) {
                        if ($u->status === 'pending') {
                            $p->pending++;
                        } elseif ($u->status === 'disetujui') {
                            $p->disetujui++;
                        } elseif (in_array($u->status, array('ditolak', 'ditolak_permanen'), true)) {
                            $p->ditolak++;
                        }
                    }
                }
            }
        }
        $data = array(
            'title' => 'Verifikasi SKPI',
            'page_title' => 'Verifikasi Pengajuan SKPI',
            'breadcrumb' => array(array('label' => 'Verifikasi SKPI', 'active' => true)),
            'sidebar_menu' => generate_sidebar_menu('sekprodi'),
            'periode_list' => $periode_list,
            'periode' => $periode,
            'peserta_list' => $peserta_list,
            'id_periode' => $id_periode,
            'kelas_filter' => $kelas,
        );
        $data['content'] = $this->load->view('sekprodi/skpi_verifikasi/index', $data, true);
        $this->load->view('layouts/main', $data);
    }

    public function detail_mahasiswa($id_periode, $id_mahasiswa) {
        $periode = $this->Skpi_periode_model->get_by_id($id_periode);
        if (!$periode) {
            $this->session->set_flashdata('error', 'Periode tidak ditemukan.');
            redirect('Skpi_verifikasi');
        }
        $peserta = $this->Skpi_peserta_model->get_by_periode($id_periode);
        $mhs = null;
        foreach ($peserta as $p) {
            if ((int) $p->id_mahasiswa === (int) $id_mahasiswa) {
                $mhs = $p;
                break;
            }
        }
        if (!$mhs) {
            $this->session->set_flashdata('error', 'Mahasiswa tidak terdaftar pada periode ini.');
            redirect('Skpi_verifikasi?id_periode=' . $id_periode);
        }
        $uploads = $this->Skpi_upload_model->get_for_verifikasi($id_periode, $id_mahasiswa);
        $grouped = array();
        foreach ($uploads as $u) {
            $key = $u->nama_berkas;
            if (!isset($grouped[$key])) {
                $grouped[$key] = array();
            }
            $grouped[$key][] = $u;
        }
        $data = array(
            'title' => 'Verifikasi SKPI',
            'page_title' => 'Verifikasi — ' . $mhs->nama_mahasiswa,
            'breadcrumb' => array(
                array('label' => 'Verifikasi SKPI', 'url' => site_url('Skpi_verifikasi?id_periode=' . $id_periode)),
                array('label' => 'Detail', 'active' => true),
            ),
            'sidebar_menu' => generate_sidebar_menu('sekprodi'),
            'periode' => $periode,
            'mhs' => $mhs,
            'grouped' => $grouped,
        );
        $data['content'] = $this->load->view('sekprodi/skpi_verifikasi/detail_mahasiswa', $data, true);
        $this->load->view('layouts/main', $data);
    }

    public function setujui($id_upload) {
        $this->_verifikasi($id_upload, 'disetujui', null);
    }

    public function tolak($id_upload) {
        if ($this->input->method() !== 'post') {
            redirect('Skpi_verifikasi');
        }
        $jenis = $this->input->post('jenis_tolak');
        $status = ($jenis === 'permanen') ? 'ditolak_permanen' : 'ditolak';
        $this->form_validation->set_rules('catatan_sekprodi', 'Catatan', 'required|trim');
        if ($this->form_validation->run() === FALSE) {
            $upload = $this->Skpi_upload_model->get_by_id($id_upload);
            if ($upload) {
                $this->session->set_flashdata('error', validation_errors());
                redirect('Skpi_verifikasi/detail_mahasiswa/' . $upload->id_skpi_periode . '/' . $upload->id_mahasiswa);
            }
            redirect('Skpi_verifikasi');
        }
        $this->_verifikasi($id_upload, $status, $this->input->post('catatan_sekprodi'));
    }

    public function export_csv() {
        $id_periode = (int) $this->input->get('id_periode');
        $kelas = $this->input->get('kelas');
        $status = $this->input->get('status');
        if ($id_periode < 1) {
            $this->session->set_flashdata('error', 'Pilih periode terlebih dahulu.');
            redirect('Skpi_verifikasi');
        }
        $rows = $this->Skpi_upload_model->get_export_rows($id_periode, $kelas ?: null, $status ?: null);
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename=skpi_export_' . $id_periode . '_' . date('Ymd_His') . '.csv');
        $out = fopen('php://output', 'w');
        fprintf($out, chr(0xEF) . chr(0xBB) . chr(0xBF));
        fputcsv($out, array('NIM', 'Nama', 'Kelas', 'Periode', 'Jenis Berkas', 'Nama Sertifikat', 'Deskripsi', 'Link Drive', 'Status', 'Catatan Sekprodi', 'Tanggal Upload', 'Tanggal Verifikasi'));
        foreach ($rows as $r) {
            fputcsv($out, array(
                $r->nim,
                $r->nama_mahasiswa,
                $r->kelas,
                $r->nama_periode,
                $r->nama_berkas,
                isset($r->nama_sertifikat) ? $r->nama_sertifikat : '',
                $r->deskripsi_berkas,
                $r->link_drive,
                $r->status,
                $r->catatan_sekprodi,
                $r->created_at,
                $r->reviewed_at,
            ));
        }
        fclose($out);
        exit;
    }

    private function _verifikasi($id_upload, $status, $catatan) {
        $upload = $this->Skpi_upload_model->get_by_id($id_upload);
        if (!$upload) {
            $this->session->set_flashdata('error', 'Data tidak ditemukan.');
            redirect('Skpi_verifikasi');
        }
        $this->Skpi_upload_model->update($id_upload, array(
            'status' => $status,
            'catatan_sekprodi' => $catatan,
            'reviewed_by' => $this->session->userdata('user_id'),
            'reviewed_at' => date('Y-m-d H:i:s'),
        ));
        $label = ($status === 'disetujui') ? 'disetujui' : (($status === 'ditolak_permanen') ? 'ditolak permanen' : 'ditolak (perlu perbaikan)');
        send_in_app_notification_by_ref(
            'mahasiswa',
            $upload->id_mahasiswa,
            'SKPI: Status Pengajuan',
            'Pengajuan "' . (!empty($upload->nama_sertifikat) ? $upload->nama_sertifikat : $upload->nama_berkas) . '" Anda ' . $label . ($catatan ? '. Catatan: ' . $catatan : ''),
            site_url('Skpi_mahasiswa/upload/' . $upload->id_skpi_periode)
        );
        $this->session->set_flashdata('success', 'Status pengajuan diperbarui.');
        redirect('Skpi_verifikasi/detail_mahasiswa/' . $upload->id_skpi_periode . '/' . $upload->id_mahasiswa);
    }
}
