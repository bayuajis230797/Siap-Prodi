<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Proposal_verifikasi_berkas_sidang Controller (Sekprodi)
 * Verifikasi pendaftaran sidang Proposal TA (satu tahap): setujui dan tetapkan sekaligus
 * tanggal, jam, ruangan, Penguji 1 dan Penguji 2. Perubahan sesudahnya lewat Jadwal_proposal_sekprodi.
 */
class Proposal_verifikasi_berkas_sidang extends MY_Controller {

    public function __construct() {
        parent::__construct();
        $this->check_role('sekprodi');
        $this->load->model('Proposal_pendaftaran_sidang_model');
        $this->load->model('Proposal_berkas_upload_model');
        $this->load->model('Proposal_model');
        $this->load->model('Jadwal_proposal_model');
        $this->load->model('Dosen_model');
        $this->load->model('Tahun_akademik_model');
        $this->load->library('pagination');
        $this->load->library('form_validation');
        $this->load->library('Proposal_jadwal');
        $this->load->helper('proposal');
    }

    public function index() {
        $tahun_aktif = $this->Tahun_akademik_model->get_aktif();
        $status = $this->input->get('status');
        $kelas = $this->input->get('kelas');
        $per_page = (int) $this->input->get('per_page');
        $per_page = in_array($per_page, array(10, 25, 50, 100), true) ? $per_page : 10;

        $filters = array();
        if ($tahun_aktif) { $filters['id_tahun_akademik'] = $tahun_aktif->id; }
        if (in_array($status, array('menunggu', 'disetujui', 'ditolak', 'selesai'), true)) { $filters['status'] = $status; } else { $status = null; }
        if ($kelas) { $filters['kelas'] = $kelas; }

        $total = $this->Proposal_pendaftaran_sidang_model->count_all($filters);
        $this->pagination->initialize(proposal_pagination_config(
            site_url('Proposal_verifikasi_berkas_sidang/index'), $total, $per_page,
            array('per_page' => $per_page != 10 ? $per_page : null, 'status' => $status, 'kelas' => $kelas)
        ));
        $page = max(1, (int) $this->uri->segment(3));
        $list = $this->Proposal_pendaftaran_sidang_model->get_all($filters, $per_page, ($page - 1) * $per_page);
        foreach ($list as $p) {
            $p->judul = get_judul_proposal_terbaru($p->id_mahasiswa, $p->id_tahun_akademik);
        }

        $kelas_list = array();
        foreach ($this->Proposal_model->get_all($tahun_aktif ? $tahun_aktif->id : null) as $s) {
            if (!empty($s->kelas) && !in_array($s->kelas, $kelas_list)) { $kelas_list[] = $s->kelas; }
        }
        sort($kelas_list);

        $data = array(
            'title' => 'Verifikasi Berkas Sidang Proposal TA',
            'page_title' => 'Verifikasi Berkas Sidang Proposal TA',
            'breadcrumb' => array(
                array('label' => 'Proposal TA', 'url' => '#'),
                array('label' => 'Verifikasi Berkas Sidang', 'active' => true)
            ),
            'sidebar_menu' => generate_sidebar_menu('sekprodi'),
            'pendaftaran_list' => $list,
            'pagination' => $this->pagination->create_links(),
            'total_rows' => $total,
            'status_filter' => $status,
            'kelas_filter' => $kelas,
            'kelas_list' => $kelas_list,
            'tahun_aktif' => $tahun_aktif,
            'per_page' => $per_page
        );
        $data['content'] = $this->load->view('sekprodi/proposal_verifikasi_berkas_sidang/index', $data, true);
        $this->load->view('layouts/main', $data);
    }

    public function detail($id) {
        $pendaftaran = $this->ambil_pendaftaran($id);
        $proposal = $this->Proposal_model->get_by_id($pendaftaran->id_mahasiswa_proposal);
        $jadwal_list = $this->Jadwal_proposal_model->get_by_mahasiswa($pendaftaran->id_mahasiswa_proposal);

        $data = array(
            'title' => 'Detail Verifikasi Berkas Sidang Proposal TA',
            'page_title' => 'Detail Verifikasi Berkas Sidang Proposal TA',
            'breadcrumb' => array(
                array('label' => 'Verifikasi Berkas Sidang', 'url' => site_url('Proposal_verifikasi_berkas_sidang')),
                array('label' => 'Detail', 'active' => true)
            ),
            'sidebar_menu' => generate_sidebar_menu('sekprodi'),
            'pendaftaran' => $pendaftaran,
            'proposal' => $proposal,
            'judul' => get_judul_proposal_terbaru($pendaftaran->id_mahasiswa, $pendaftaran->id_tahun_akademik),
            'berkas_uploaded' => $this->Proposal_berkas_upload_model->get_by_pendaftaran($pendaftaran->id),
            'jadwal' => !empty($jadwal_list) ? $jadwal_list[0] : null,
            'dosen_list' => $this->Dosen_model->get_all_active()
        );
        $data['content'] = $this->load->view('sekprodi/proposal_verifikasi_berkas_sidang/detail', $data, true);
        $this->load->view('layouts/main', $data);
    }

    public function approve($id) {
        $pendaftaran = $this->ambil_pendaftaran($id);
        $url_detail = 'Proposal_verifikasi_berkas_sidang/detail/' . $id;
        if ($this->input->method() !== 'post') {
            redirect($url_detail);
        }
        if ($pendaftaran->status !== 'menunggu') {
            $this->session->set_flashdata('error', 'Pendaftaran ini sudah diproses.');
            redirect($url_detail);
        }

        $proposal = $this->Proposal_model->get_by_id($pendaftaran->id_mahasiswa_proposal);
        if (!$proposal) {
            $this->session->set_flashdata('error', 'Data Proposal TA mahasiswa tidak ditemukan.');
            redirect($url_detail);
        }
        $catatan = trim((string) $this->input->post('catatan_sekprodi'));

        if (count($this->Jadwal_proposal_model->get_by_mahasiswa($proposal->id)) > 0) {
            $this->session->set_flashdata('error', 'Jadwal sidang mahasiswa ini sudah ada. Ubah lewat menu Jadwal Sidang.');
            redirect($url_detail);
        }

        $w = $this->proposal_jadwal->validasi_waktu($_POST);
        $p = $this->proposal_jadwal->validasi_penguji($_POST, $proposal->id_pembimbing);
        $errors = array_merge(!empty($w['errors']) ? $w['errors'] : array(), !empty($p['errors']) ? $p['errors'] : array());
        if (!empty($errors)) {
            $this->session->set_flashdata('error', implode('<br>', $errors));
            redirect($url_detail);
        }
        $bentrok = $this->proposal_jadwal->bentrok($w['data'], $proposal->id_tahun_akademik, null, $p['ids']);
        if ($bentrok !== null) {
            $this->session->set_flashdata('error', $bentrok);
            redirect($url_detail);
        }

        $this->db->trans_start();
        $this->Jadwal_proposal_model->create($w['data'] + array(
            'id_mahasiswa_proposal' => $proposal->id,
            'id_mahasiswa' => $proposal->id_mahasiswa,
            'id_dosen_pembimbing' => $proposal->id_pembimbing,
            'id_tahun_akademik' => $proposal->id_tahun_akademik,
            'status' => 'terjadwal',
            'id_penguji_1' => $p['ids'][0],
            'id_penguji_2' => $p['ids'][1]
        ));
        $this->Proposal_pendaftaran_sidang_model->update($id, array('status' => 'disetujui', 'catatan_sekprodi' => $catatan !== '' ? $catatan : null));
        $this->db->trans_complete();
        if (!$this->db->trans_status()) {
            $this->session->set_flashdata('error', 'Gagal menyimpan jadwal dan penguji.');
            redirect($url_detail);
        }

        $tgl = date('d/m/Y', strtotime($w['data']['tanggal_ujian'])) . ' ' . substr($w['data']['jam_mulai'], 0, 5) . ' di ' . $w['data']['ruangan'];
        proposal_notify('mahasiswa', $proposal->id_mahasiswa, 'Jadwal Sidang Proposal TA', 'Sidang Anda dijadwalkan: ' . $tgl . '. Tim penguji sudah ditetapkan.', site_url('Proposal_catatan_revisi'));
        proposal_notify('dosen', $proposal->id_pembimbing, 'Jadwal Sidang Proposal TA', 'Mahasiswa bimbingan Anda dijadwalkan sidang: ' . $tgl, site_url('Proposal_mahasiswa_dosen'));
        foreach ($p['ids'] as $i => $id_dosen) {
            proposal_notify('dosen', $id_dosen, 'Penguji Sidang Proposal TA',
                'Anda ditetapkan sebagai Penguji ' . ($i + 1) . ' sidang Proposal TA pada ' . $tgl . '.', site_url('Jadwal_proposal_dosen'));
        }
        $this->session->set_flashdata('success', 'Pendaftaran disetujui. Jadwal sidang serta Penguji 1 dan 2 ditetapkan.');
        redirect($url_detail);
    }
    public function reject($id) {
        $pendaftaran = $this->ambil_pendaftaran($id);
        $url_detail = 'Proposal_verifikasi_berkas_sidang/detail/' . $id;
        if ($this->input->method() !== 'post') {
            redirect($url_detail);
        }
        if ($pendaftaran->status !== 'menunggu') {
            $this->session->set_flashdata('error', 'Pendaftaran ini sudah diproses.');
            redirect($url_detail);
        }

        $this->form_validation->set_rules('catatan_sekprodi', 'Catatan', 'required|trim');
        if ($this->form_validation->run() === FALSE) {
            $this->session->set_flashdata('error', validation_errors());
            redirect($url_detail);
        }

        $this->Proposal_pendaftaran_sidang_model->update($id, array('status' => 'ditolak', 'catatan_sekprodi' => $this->input->post('catatan_sekprodi')));
        proposal_notify('mahasiswa', $pendaftaran->id_mahasiswa, 'Pendaftaran Sidang Proposal TA Ditolak',
            'Pendaftaran ditolak. Lihat catatan sekprodi dan kirim ulang.',
            site_url('Pendaftaran_sidang_proposal'));
        $this->session->set_flashdata('success', 'Pendaftaran sidang ditolak.');
        redirect('Proposal_verifikasi_berkas_sidang');
    }

    private function ambil_pendaftaran($id) {
        $p = $this->Proposal_pendaftaran_sidang_model->get_by_id($id);
        if (!$p) {
            $this->session->set_flashdata('error', 'Data pendaftaran tidak ditemukan.');
            redirect('Proposal_verifikasi_berkas_sidang');
        }
        return $p;
    }
}
