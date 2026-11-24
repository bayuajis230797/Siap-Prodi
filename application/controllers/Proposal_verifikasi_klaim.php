<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Proposal_verifikasi_klaim Controller (Sekprodi)
 * Verifikasi klaim nilai dan penetapan nilai akhir Proposal TA.
 * Rumus (dari setting Admin): nilai akhir = bimbingan x bobot + sidang x bobot,
 * dengan nilai sidang = rata-rata Penguji 1 dan Penguji 2. Perhitungan tunggal ada
 * di Proposal_penilaian; rekap hanya menyalin hasilnya.
 */
class Proposal_verifikasi_klaim extends MY_Controller {

    public function __construct() {
        parent::__construct();
        $this->check_role('sekprodi');
        $this->load->model('Proposal_klaim_nilai_model');
        $this->load->model('Proposal_berkas_klaim_upload_model');
        $this->load->model('Proposal_model');
        $this->load->model('Proposal_nilai_bimbingan_model');
        $this->load->model('Proposal_nilai_sidang_model');
        $this->load->model('Proposal_nilai_rekap_model');
        $this->load->model('Proposal_bimbingan_pasca_model');
        $this->load->model('Tahun_akademik_model');
        $this->load->library('pagination');
        $this->load->library('form_validation');
        $this->load->library('Proposal_penilaian');
        $this->load->library('Bimbingan_pasca_service');
        $this->load->helper('proposal');
    }

    public function index() {
        $tahun_aktif = $this->Tahun_akademik_model->get_aktif();
        $status = $this->input->get('status');
        if (!in_array($status, array('menunggu', 'disetujui', 'ditolak'), true)) {
            $status = null;
        }
        $per_page = (int) $this->input->get('per_page');
        $per_page = in_array($per_page, array(10, 25, 50, 100), true) ? $per_page : 10;

        $filters = array();
        if ($tahun_aktif) { $filters['id_tahun_akademik'] = $tahun_aktif->id; }
        if ($status) { $filters['status'] = $status; }

        $total = count($this->Proposal_klaim_nilai_model->get_all($filters));
        $this->pagination->initialize(proposal_pagination_config(
            site_url('Proposal_verifikasi_klaim/index'), $total, $per_page,
            array('status' => $status, 'per_page' => $per_page != 10 ? $per_page : null)
        ));
        $page = max(1, (int) $this->uri->segment(3));

        $data = array(
            'title' => 'Verifikasi Klaim Nilai Proposal TA',
            'page_title' => 'Verifikasi Klaim Nilai Proposal TA',
            'breadcrumb' => array(
                array('label' => 'Proposal TA', 'url' => '#'),
                array('label' => 'Verifikasi Klaim Nilai', 'active' => true)
            ),
            'sidebar_menu' => generate_sidebar_menu('sekprodi'),
            'klaim_list' => $this->Proposal_klaim_nilai_model->get_all($filters, $per_page, ($page - 1) * $per_page),
            'pagination' => $this->pagination->create_links(),
            'total_rows' => $total,
            'status_filter' => $status,
            'per_page' => $per_page,
            'tahun_aktif' => $tahun_aktif
        );
        $data['content'] = $this->load->view('sekprodi/proposal_verifikasi_klaim/index', $data, true);
        $this->load->view('layouts/main', $data);
    }

    public function detail($id) {
        $klaim = $this->ambil_klaim($id);
        $proposal = $this->Proposal_model->get_by_id($klaim->id_mahasiswa_proposal);
        $pasca = $this->bimbingan_pasca_service->proposal_klaim_status($klaim->id_mahasiswa_proposal);

        $data = array(
            'title' => 'Detail Klaim Nilai Proposal TA',
            'page_title' => 'Detail Klaim Nilai Proposal TA',
            'breadcrumb' => array(
                array('label' => 'Verifikasi Klaim Nilai', 'url' => site_url('Proposal_verifikasi_klaim')),
                array('label' => 'Detail', 'active' => true)
            ),
            'sidebar_menu' => generate_sidebar_menu('sekprodi'),
            'klaim' => $klaim,
            'proposal' => $proposal,
            'judul' => get_judul_proposal_terbaru($klaim->id_mahasiswa, $klaim->id_tahun_akademik),
            'berkas_uploaded' => $this->Proposal_berkas_klaim_upload_model->get_by_klaim_nilai($klaim->id),
            'nilai_bimbingan' => $this->Proposal_nilai_bimbingan_model->get_row_by_proposal($klaim->id_mahasiswa_proposal),
            'nilai_sidang' => $this->Proposal_nilai_sidang_model->get_by_jadwal($klaim->id_jadwal_proposal),
            'rekap' => $this->Proposal_nilai_rekap_model->get_by_jadwal($klaim->id_jadwal_proposal),
            'pasca_status' => $pasca
        );
        $data['content'] = $this->load->view('sekprodi/proposal_verifikasi_klaim/detail', $data, true);
        $this->load->view('layouts/main', $data);
    }

    public function approve($id) {
        $klaim = $this->ambil_klaim($id);
        $url = 'Proposal_verifikasi_klaim/detail/' . $id;
        if ($this->input->method() !== 'post') {
            redirect($url);
        }
        if ($klaim->status !== 'menunggu') {
            $this->session->set_flashdata('error', 'Klaim nilai ini sudah diproses.');
            redirect($url);
        }

        // Prasyarat data nilai
        $nilai_bimbingan = $this->Proposal_nilai_bimbingan_model->get_row_by_proposal($klaim->id_mahasiswa_proposal);
        $nilai_sidang = $this->Proposal_nilai_sidang_model->get_by_jadwal($klaim->id_jadwal_proposal);
        $peran_ada = array();
        foreach ($nilai_sidang as $n) { $peran_ada[$n->peran] = $n; }
        if (!$nilai_bimbingan) {
            $this->session->set_flashdata('error', 'Nilai bimbingan pembimbing belum ada.');
            redirect($url);
        }
        if (!isset($peran_ada['penguji_1']) || !isset($peran_ada['penguji_2'])) {
            $this->session->set_flashdata('error', 'Nilai sidang dari kedua penguji belum lengkap.');
            redirect($url);
        }
        $pasca = $this->bimbingan_pasca_service->proposal_klaim_status($klaim->id_mahasiswa_proposal);
        if (empty($pasca['ok'])) {
            $this->session->set_flashdata('error', $pasca['message']);
            redirect($url);
        }

        $catatan = trim((string) $this->input->post('catatan_sekprodi'));

        $this->db->trans_start();
        // Hitung ulang dari data sumber (satu rumus di Proposal_penilaian)
        $this->proposal_penilaian->sync_bimbingan($klaim->id_mahasiswa_proposal);
        $this->proposal_penilaian->sync_sidang($klaim->id_mahasiswa_proposal);
        $p = $this->Proposal_model->get_by_id($klaim->id_mahasiswa_proposal);

        $rekap_data = array(
            'id_mahasiswa_proposal' => $klaim->id_mahasiswa_proposal,
            'id_jadwal_proposal' => $klaim->id_jadwal_proposal,
            'id_tahun_akademik' => $klaim->id_tahun_akademik,
            'nilai_bimbingan' => $p->nilai_bimbingan,
            'nilai_sidang_penguji_1' => $peran_ada['penguji_1']->nilai_total,
            'nilai_sidang_penguji_2' => $peran_ada['penguji_2']->nilai_total,
            'nilai_sidang_rata' => $p->nilai_sidang,
            'nilai_akhir' => $p->nilai_proposal_final,
            'grade' => $p->nilai_huruf
        );
        $rekap = $this->Proposal_nilai_rekap_model->get_by_jadwal($klaim->id_jadwal_proposal);
        if ($rekap) {
            $this->Proposal_nilai_rekap_model->update($rekap->id, $rekap_data);
        } else {
            $this->Proposal_nilai_rekap_model->create($rekap_data);
        }

        $this->Proposal_klaim_nilai_model->update($id, array(
            'status' => 'disetujui',
            'catatan_sekprodi' => $catatan !== '' ? $catatan : null,
            'nilai_akhir' => $p->nilai_proposal_final,
            'grade' => $p->nilai_huruf
        ));
        $this->Proposal_model->update($p->id, array('status' => 'selesai_proposal', 'tanggal_selesai' => date('Y-m-d')));

        $pasca_row = $this->Proposal_bimbingan_pasca_model->get_by_jadwal($klaim->id_jadwal_proposal);
        if ($pasca_row) {
            $this->Proposal_bimbingan_pasca_model->update($pasca_row->id, array('status' => 'closed'));
        }
        $this->db->trans_complete();

        if (!$this->db->trans_status()) {
            $this->session->set_flashdata('error', 'Gagal menetapkan nilai akhir.');
            redirect($url);
        }

        proposal_notify('mahasiswa', $klaim->id_mahasiswa, 'Klaim Nilai Proposal TA Disetujui',
            'Nilai akhir Proposal TA Anda: ' . format_nilai_proposal($p->nilai_proposal_final) . ' (' . $p->nilai_huruf . ').', site_url('Proposal_klaim_nilai'));

        $this->session->set_flashdata('success', 'Klaim nilai disetujui. Nilai akhir ' . format_nilai_proposal($p->nilai_proposal_final) . ' (' . $p->nilai_huruf . ') ditetapkan.');
        redirect('Proposal_verifikasi_klaim');
    }

    public function reject($id) {
        $klaim = $this->ambil_klaim($id);
        $url = 'Proposal_verifikasi_klaim/detail/' . $id;
        if ($this->input->method() !== 'post') {
            redirect($url);
        }
        if ($klaim->status !== 'menunggu') {
            $this->session->set_flashdata('error', 'Klaim nilai ini sudah diproses.');
            redirect($url);
        }

        $this->form_validation->set_rules('catatan_sekprodi', 'Catatan', 'required|trim');
        if ($this->form_validation->run() === FALSE) {
            $this->session->set_flashdata('error', validation_errors());
            redirect($url);
        }

        $this->Proposal_klaim_nilai_model->update($id, array('status' => 'ditolak', 'catatan_sekprodi' => $this->input->post('catatan_sekprodi')));
        proposal_notify('mahasiswa', $klaim->id_mahasiswa, 'Klaim Nilai Proposal TA Ditolak',
            'Klaim nilai ditolak. Lihat catatan sekprodi dan kirim ulang.', site_url('Proposal_klaim_nilai'));
        $this->session->set_flashdata('success', 'Klaim nilai ditolak.');
        redirect('Proposal_verifikasi_klaim');
    }

    private function ambil_klaim($id) {
        $k = $this->Proposal_klaim_nilai_model->get_by_id($id);
        if (!$k) {
            $this->session->set_flashdata('error', 'Data klaim nilai tidak ditemukan.');
            redirect('Proposal_verifikasi_klaim');
        }
        return $k;
    }
}
