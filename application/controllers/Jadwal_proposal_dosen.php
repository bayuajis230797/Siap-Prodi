<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Jadwal_proposal_dosen Controller (Dosen penguji)
 * Daftar jadwal sidang Proposal TA yang diuji dosen, dan berkas ajuan mahasiswa.
 * Form penilaian sidang ditambahkan pada fase berikutnya.
 */
class Jadwal_proposal_dosen extends MY_Controller {

    public function __construct() {
        parent::__construct();
        $this->check_role('dosen');
        $this->load->model('Jadwal_proposal_model');
        $this->load->model('Proposal_pendaftaran_sidang_model');
        $this->load->model('Proposal_berkas_upload_model');
        $this->load->model('Proposal_nilai_sidang_model');
        $this->load->model('Proposal_nilai_sidang_detail_model');
        $this->load->model('Proposal_aspek_penilaian_model');
        $this->load->model('Proposal_item_penilaian_model');
        $this->load->model('Proposal_klaim_nilai_model');
        $this->load->model('Proposal_model');
        $this->load->model('Tahun_akademik_model');
        $this->load->library('Proposal_penilaian');
        $this->load->library('Bimbingan_pasca_service');
        $this->load->helper(array('proposal', 'app', 'notifikasi'));
    }

    /** 'penguji_1', 'penguji_2', atau null bila dosen bukan penguji jadwal ini. */
    private function peran($jadwal, $id_dosen) {
        if ((int) $jadwal->id_penguji_1 === (int) $id_dosen) { return 'penguji_1'; }
        if ((int) $jadwal->id_penguji_2 === (int) $id_dosen) { return 'penguji_2'; }
        return null;
    }

    public function index() {
        $id_dosen = $this->session->userdata('ref_id');
        $tahun_aktif = $this->Tahun_akademik_model->get_aktif();
        if (!$id_dosen || !$tahun_aktif) {
            $this->session->set_flashdata('error', !$id_dosen ? 'Data dosen tidak ditemukan.' : 'Tidak ada tahun akademik aktif.');
            redirect('Dashboard');
        }

        $list = $this->Jadwal_proposal_model->get_by_penguji($id_dosen, $tahun_aktif->id);
        foreach ($list as $j) {
            $j->peran = $this->peran($j, $id_dosen);
            $j->judul = get_judul_proposal_terbaru($j->id_mahasiswa, $tahun_aktif->id);
            $j->penilaian = $j->peran ? $this->Proposal_nilai_sidang_model->get_by_jadwal_penguji($j->id, $j->peran) : null;
        }

        $data = array(
            'title' => 'Jadwal Sidang Proposal TA',
            'page_title' => 'Jadwal Sidang Proposal TA',
            'breadcrumb' => array(
                array('label' => 'Proposal TA', 'url' => '#'),
                array('label' => 'Jadwal Sidang Proposal', 'active' => true)
            ),
            'sidebar_menu' => generate_sidebar_menu('dosen'),
            'jadwal_list' => $list,
            'tahun_aktif' => $tahun_aktif
        );
        $data['content'] = $this->load->view('dosen/proposal/jadwal_index', $data, true);
        $this->load->view('layouts/main', $data);
    }

    public function berkas($id) {
        $id_dosen = $this->session->userdata('ref_id');
        $jadwal = $this->Jadwal_proposal_model->get_by_id($id);
        if (!$jadwal) {
            $this->session->set_flashdata('error', 'Data jadwal tidak ditemukan.');
            redirect('Jadwal_proposal_dosen');
        }
        $peran = $this->peran($jadwal, $id_dosen);
        if ($peran === null) {
            $this->session->set_flashdata('error', 'Anda bukan penguji untuk jadwal ini.');
            redirect('Jadwal_proposal_dosen');
        }

        $pendaftaran = $this->Proposal_pendaftaran_sidang_model->get_by_mahasiswa_proposal($jadwal->id_mahasiswa_proposal);

        $data = array(
            'title' => 'Berkas Ajuan Sidang Proposal TA',
            'page_title' => 'Berkas Ajuan Sidang Proposal TA',
            'breadcrumb' => array(
                array('label' => 'Jadwal Sidang Proposal', 'url' => site_url('Jadwal_proposal_dosen')),
                array('label' => 'Berkas Ajuan', 'active' => true)
            ),
            'sidebar_menu' => generate_sidebar_menu('dosen'),
            'jadwal' => $jadwal,
            'peran' => $peran,
            'judul' => get_judul_proposal_terbaru($jadwal->id_mahasiswa, $jadwal->id_tahun_akademik),
            'berkas_uploaded' => $pendaftaran ? $this->Proposal_berkas_upload_model->get_by_pendaftaran($pendaftaran->id) : array()
        );
        $data['content'] = $this->load->view('dosen/proposal/jadwal_berkas', $data, true);
        $this->load->view('layouts/main', $data);
    }

    /**
     * Form penilaian sidang oleh penguji (Penguji 1 / Penguji 2).
     * - Dapat diisi kapan saja (tidak menunggu tanggal ujian), selama bukan jadwal batal.
     * - Dapat diubah sampai mahasiswa mengajukan klaim nilai.
     * - Jadwal menjadi 'selesai' hanya setelah KEDUA penguji menilai.
     */
    public function detail($id) {
        $id_dosen = $this->session->userdata('ref_id');
        $jadwal = $this->Jadwal_proposal_model->get_by_id($id);
        if (!$jadwal) {
            $this->session->set_flashdata('error', 'Data jadwal tidak ditemukan.');
            redirect('Jadwal_proposal_dosen');
        }
        $peran = $this->peran($jadwal, $id_dosen);
        if ($peran === null) {
            $this->session->set_flashdata('error', 'Anda bukan penguji untuk jadwal ini.');
            redirect('Jadwal_proposal_dosen');
        }
        $url_form = 'Jadwal_proposal_dosen/detail/' . $jadwal->id;

        $aspek = $this->Proposal_aspek_penilaian_model->get_by_kode('PS');
        $item_list = ($aspek && !empty($aspek->aktif)) ? $this->Proposal_item_penilaian_model->get_by_aspek($aspek->id, true) : array();
        $total_bobot = 0;
        foreach ($item_list as $it) {
            $total_bobot += (float) $it->bobot;
        }
        $setting_ok = !empty($item_list) && abs($total_bobot - 100) <= 0.01;

        // Alasan penilaian tidak dapat dilakukan/diubah (null = boleh)
        $terkunci = null;
        $klaim = $this->Proposal_klaim_nilai_model->get_by_mahasiswa_proposal($jadwal->id_mahasiswa_proposal);
        if ($jadwal->status === 'batal') {
            $terkunci = 'Jadwal sidang ini dibatalkan.';
        } elseif ($klaim && $klaim->status !== 'ditolak') {
            $terkunci = 'Mahasiswa sudah mengajukan klaim nilai, penilaian tidak dapat diubah lagi.';
        } elseif (!$setting_ok) {
            $terkunci = 'Item penilaian sidang (PS) belum diatur dengan benar (total bobot harus 100%, saat ini ' . $total_bobot . '%). Hubungi admin.';
        }

        $nilai = $this->Proposal_nilai_sidang_model->get_by_jadwal_penguji($jadwal->id, $peran);
        $detail = array();
        if ($nilai) {
            foreach ($this->Proposal_nilai_sidang_detail_model->get_by_nilai_sidang($nilai->id) as $d) {
                $detail[$d->id_item_penilaian] = $d->nilai;
            }
        }

        if ($this->input->method() === 'post') {
            if ($terkunci !== null) {
                $this->session->set_flashdata('error', $terkunci);
                redirect($url_form);
            }

            $errors = array();
            $items = array();
            foreach ($item_list as $it) {
                $v = $this->input->post('nilai_item_' . $it->id);
                if ($v === '' || $v === null) {
                    $errors[] = "Nilai untuk item '{$it->pertanyaan}' belum diisi";
                } elseif (!is_numeric($v) || $v < 0 || $v > 100) {
                    $errors[] = "Nilai untuk item '{$it->pertanyaan}' harus antara 0-100";
                } else {
                    $items[$it->id] = array('nilai' => (float) $v, 'bobot' => (float) $it->bobot);
                }
            }
            if (!empty($errors)) {
                $this->session->set_flashdata('error', implode('<br>', $errors));
                redirect($url_form);
            }

            $total = hitung_nilai_p_dari_items_proposal($items);
            $catatan_revisi = trim((string) $this->input->post('catatan_revisi'));

            $this->db->trans_start();
            $id_nilai = $this->Proposal_nilai_sidang_model->upsert(array(
                'id_jadwal_proposal' => $jadwal->id,
                'id_mahasiswa_proposal' => $jadwal->id_mahasiswa_proposal,
                'id_penguji' => $id_dosen,
                'peran' => $peran,
                'nilai_total' => $total,
                'catatan_revisi' => $catatan_revisi !== '' ? $catatan_revisi : null
            ));
            $this->Proposal_nilai_sidang_detail_model->delete_by_nilai_sidang($id_nilai);
            foreach ($items as $id_item => $it) {
                $this->Proposal_nilai_sidang_detail_model->create(array(
                    'id_nilai_sidang' => $id_nilai, 'id_item_penilaian' => $id_item, 'nilai' => $it['nilai']
                ));
            }
            // Jadwal selesai hanya bila kedua penguji sudah menilai
            $sudah = count($this->Proposal_nilai_sidang_model->get_by_jadwal($jadwal->id));
            if ($sudah >= 2 && $jadwal->status === 'terjadwal') {
                $this->Jadwal_proposal_model->update($jadwal->id, array('status' => 'selesai'));
            }
            $this->db->trans_complete();

            if (!$this->db->trans_status()) {
                $this->session->set_flashdata('error', 'Gagal menyimpan penilaian.');
                redirect($url_form);
            }

            $this->bimbingan_pasca_service->ensure_proposal_by_jadwal($jadwal->id);
            $this->proposal_penilaian->sync_sidang($jadwal->id_mahasiswa_proposal);

            if ($sudah >= 2) {
                proposal_notify('mahasiswa', $jadwal->id_mahasiswa, 'Penilaian Sidang Proposal TA Lengkap',
                    'Kedua penguji telah menilai. Lakukan revisi pasca sidang bila diminta.', site_url('Proposal_catatan_revisi'));
            }
            $this->session->set_flashdata('success', 'Penilaian sidang tersimpan (' . format_nilai_proposal($total) . ')'
                . ($sudah >= 2 ? '. Kedua penguji sudah menilai, jadwal selesai.' : '. Menunggu penguji lain.'));
            redirect('Jadwal_proposal_dosen');
        }

        $data = array(
            'title' => 'Input Nilai Sidang Proposal TA',
            'page_title' => 'Input Nilai Sidang Proposal TA',
            'breadcrumb' => array(
                array('label' => 'Jadwal Sidang Proposal', 'url' => site_url('Jadwal_proposal_dosen')),
                array('label' => 'Input Nilai', 'active' => true)
            ),
            'sidebar_menu' => generate_sidebar_menu('dosen'),
            'jadwal' => $jadwal,
            'peran' => $peran,
            'aspek' => $aspek,
            'item_list' => $item_list,
            'nilai_sidang' => $nilai,
            'penilaian_details' => $detail,
            'terkunci' => $terkunci,
            'judul' => get_judul_proposal_terbaru($jadwal->id_mahasiswa, $jadwal->id_tahun_akademik),
            'jumlah_penilai' => count($this->Proposal_nilai_sidang_model->get_by_jadwal($jadwal->id))
        );
        $data['content'] = $this->load->view('dosen/proposal/jadwal_detail', $data, true);
        $this->load->view('layouts/main', $data);
    }
}
