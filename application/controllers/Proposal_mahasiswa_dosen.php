<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Proposal_mahasiswa_dosen Controller (Dosen pembimbing)
 * Daftar mahasiswa bimbingan, input nilai bimbingan, dan ACC layak sidang.
 *
 * Syarat ACC layak sidang (satu pembimbing):
 *  - judul sudah di-ACC pembimbing,
 *  - minimal satu topik bimbingan sudah di-ACC,
 *  - belum ada jadwal sidang (nilai terkunci setelah dijadwalkan),
 *  - item penilaian PB aktif dengan total bobot 100%.
 */
class Proposal_mahasiswa_dosen extends MY_Controller {

    public function __construct() {
        parent::__construct();
        $this->check_role('dosen');
        $this->load->model('Proposal_model');
        $this->load->model('Proposal_pengajuan_model');
        $this->load->model('Proposal_bimbingan_model');
        $this->load->model('Proposal_bimbingan_log_model');
        $this->load->model('Proposal_nilai_bimbingan_model');
        $this->load->model('Proposal_nilai_bimbingan_detail_model');
        $this->load->model('Proposal_aspek_penilaian_model');
        $this->load->model('Proposal_item_penilaian_model');
        $this->load->model('Jadwal_proposal_model');
        $this->load->model('Tahun_akademik_model');
        $this->load->library('Proposal_penilaian');
        $this->load->helper(array('proposal', 'notifikasi'));
    }

    /**
     * Daftar mahasiswa bimbingan.
     */
    public function index() {
        $id_dosen = $this->session->userdata('ref_id');
        $tahun_aktif = $this->Tahun_akademik_model->get_aktif();
        if (!$tahun_aktif) {
            $this->session->set_flashdata('error', 'Tidak ada tahun akademik aktif.');
            redirect('Dashboard');
        }

        $list = $this->Proposal_model->get_by_pembimbing($id_dosen, $tahun_aktif->id);
        foreach ($list as $p) {
            $p->judul = get_judul_proposal_terbaru($p->id_mahasiswa, $tahun_aktif->id);
            $p->ringkas = $this->ringkasan_bimbingan($p->id_mahasiswa, $tahun_aktif->id);
            $p->nilai = $this->Proposal_nilai_bimbingan_model->get_row_by_proposal($p->id);
        }

        $data = array(
            'title' => 'Daftar Mahasiswa Proposal TA',
            'page_title' => 'Daftar Mahasiswa Proposal TA',
            'breadcrumb' => array(
                array('label' => 'Proposal TA', 'url' => '#'),
                array('label' => 'Daftar Mahasiswa', 'active' => true)
            ),
            'sidebar_menu' => generate_sidebar_menu('dosen'),
            'mahasiswa_list' => $list,
            'tahun_aktif' => $tahun_aktif
        );
        $data['content'] = $this->load->view('dosen/proposal/mahasiswa_index', $data, true);
        $this->load->view('layouts/main', $data);
    }

    /**
     * Form nilai bimbingan + ACC layak sidang.
     */
    public function siap_sidang($id_proposal) {
        $id_dosen = $this->session->userdata('ref_id');
        $proposal = $this->ambil_milik_dosen($id_proposal);
        $url_form = 'Proposal_mahasiswa_dosen/siap_sidang/' . $proposal->id;

        $alasan = $this->alasan_belum_boleh($proposal);
        if ($alasan !== null) {
            $this->session->set_flashdata('error', $alasan);
            redirect('Proposal_mahasiswa_dosen');
        }

        $aspek = $this->Proposal_aspek_penilaian_model->get_by_kode('PB');
        if (!$aspek || empty($aspek->aktif)) {
            $this->session->set_flashdata('error', 'Aspek penilaian PB (Nilai Bimbingan) belum aktif. Hubungi admin.');
            redirect('Proposal_mahasiswa_dosen');
        }
        $item_list = $this->Proposal_item_penilaian_model->get_by_aspek($aspek->id, true);
        $total_bobot = 0;
        foreach ($item_list as $it) {
            $total_bobot += (float) $it->bobot;
        }
        if (empty($item_list) || abs($total_bobot - 100) > 0.01) {
            $this->session->set_flashdata('error', 'Item penilaian bimbingan belum diatur dengan benar (total bobot harus 100%, saat ini ' . $total_bobot . '%). Hubungi admin.');
            redirect('Proposal_mahasiswa_dosen');
        }

        $nilai = $this->Proposal_nilai_bimbingan_model->get_row_by_proposal($proposal->id);
        $detail = array();
        if ($nilai) {
            foreach ($this->Proposal_nilai_bimbingan_detail_model->get_by_nilai_bimbingan($nilai->id) as $d) {
                $detail[$d->id_item_penilaian] = $d->nilai;
            }
        }

        if ($this->input->method() === 'post') {
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

            $nilai_pb = hitung_nilai_p_dari_items_proposal($items);
            $id_nilai = $this->Proposal_nilai_bimbingan_model->upsert(array(
                'id_mahasiswa_proposal' => $proposal->id,
                'id_dosen' => $id_dosen,
                'id_tahun_akademik' => $proposal->id_tahun_akademik,
                'nilai_rata' => $nilai_pb
            ));
            $this->Proposal_nilai_bimbingan_detail_model->delete_by_nilai_bimbingan($id_nilai);
            foreach ($items as $id_item => $it) {
                $this->Proposal_nilai_bimbingan_detail_model->create(array(
                    'id_nilai_bimbingan' => $id_nilai,
                    'id_item_penilaian' => $id_item,
                    'nilai' => $it['nilai']
                ));
            }

            $baru_layak = ($proposal->status !== 'layak_sidang');
            $this->Proposal_model->update($proposal->id, array('acc_pembimbing' => 1, 'status' => 'layak_sidang'));
            $this->proposal_penilaian->sync_bimbingan($proposal->id);

            if ($baru_layak) {
                proposal_notify('mahasiswa', $proposal->id_mahasiswa, 'Layak Sidang Proposal TA',
                    'Pembimbing menyatakan Anda layak sidang. Silakan lanjut ke pendaftaran sidang.', site_url('Proposal_pengajuan'));
            }
            $this->session->set_flashdata('success', 'Nilai bimbingan tersimpan (' . format_nilai_proposal($nilai_pb) . '). Mahasiswa dinyatakan layak sidang.');
            redirect('Proposal_mahasiswa_dosen');
        }

        $data = array(
            'title' => 'Nilai Bimbingan dan Layak Sidang',
            'page_title' => 'Nilai Bimbingan dan Layak Sidang Proposal TA',
            'breadcrumb' => array(
                array('label' => 'Proposal TA', 'url' => '#'),
                array('label' => 'Daftar Mahasiswa', 'url' => site_url('Proposal_mahasiswa_dosen')),
                array('label' => 'Nilai Bimbingan', 'active' => true)
            ),
            'sidebar_menu' => generate_sidebar_menu('dosen'),
            'proposal' => $proposal,
            'aspek' => $aspek,
            'item_list' => $item_list,
            'nilai_bimbingan' => $nilai,
            'penilaian_details' => $detail,
            'judul' => get_judul_proposal_terbaru($proposal->id_mahasiswa, $proposal->id_tahun_akademik)
        );
        $data['content'] = $this->load->view('dosen/proposal/siap_sidang_form', $data, true);
        $this->load->view('layouts/main', $data);
    }

    /**
     * Rincian nilai bimbingan (baca saja).
     */
    public function detail_penilaian($id_proposal) {
        $proposal = $this->ambil_milik_dosen($id_proposal);
        $nilai = $this->Proposal_nilai_bimbingan_model->get_row_by_proposal($proposal->id);
        if (!$nilai) {
            $this->session->set_flashdata('error', 'Nilai bimbingan belum diinput.');
            redirect('Proposal_mahasiswa_dosen');
        }

        $rincian = array();
        foreach ($this->Proposal_nilai_bimbingan_detail_model->get_by_nilai_bimbingan($nilai->id) as $d) {
            $item = $this->Proposal_item_penilaian_model->get_by_id($d->id_item_penilaian);
            $rincian[] = (object) array(
                'pertanyaan' => $item ? $item->pertanyaan : ('Item #' . $d->id_item_penilaian),
                'bobot' => $item ? $item->bobot : null,
                'nilai' => $d->nilai
            );
        }

        $data = array(
            'title' => 'Detail Nilai Bimbingan',
            'page_title' => 'Detail Nilai Bimbingan Proposal TA',
            'breadcrumb' => array(
                array('label' => 'Daftar Mahasiswa', 'url' => site_url('Proposal_mahasiswa_dosen')),
                array('label' => 'Detail Nilai', 'active' => true)
            ),
            'sidebar_menu' => generate_sidebar_menu('dosen'),
            'proposal' => $proposal,
            'nilai_bimbingan' => $nilai,
            'rincian' => $rincian
        );
        $data['content'] = $this->load->view('dosen/proposal/detail_penilaian', $data, true);
        $this->load->view('layouts/main', $data);
    }

    /* ----- util ----- */

    /** Jumlah topik: total, di-ACC, masih berjalan. */
    private function ringkasan_bimbingan($id_mahasiswa, $id_tahun) {
        $r = array('total' => 0, 'acc' => 0, 'berjalan' => 0);
        foreach ($this->Proposal_bimbingan_model->get_by_mahasiswa($id_mahasiswa, $id_tahun) as $b) {
            $r['total']++;
            $w = proposal_bimbingan_workflow($b, $this->Proposal_bimbingan_log_model->get_latest_by_bimbingan($b->id));
            if ($w['key'] === 'approved') {
                $r['acc']++;
            } elseif ($w['key'] !== 'closed') {
                $r['berjalan']++;
            }
        }
        return $r;
    }

    /** Null bila boleh dinilai; selain itu pesan alasannya. */
    private function alasan_belum_boleh($proposal) {
        if (!in_array($proposal->status, array('sedang_proposal', 'layak_sidang'), true)) {
            return 'Penilaian bimbingan hanya tersedia ketika status Proposal TA sedang berjalan atau layak sidang.';
        }
        $pengajuan = $this->Proposal_pengajuan_model->get_latest_by_mahasiswa($proposal->id_mahasiswa, $proposal->id_tahun_akademik);
        if (!$pengajuan || ($pengajuan->status_acc_judul ?? 'pending') !== 'acc') {
            return 'Judul mahasiswa belum di-ACC pembimbing.';
        }
        $ringkas = $this->ringkasan_bimbingan($proposal->id_mahasiswa, $proposal->id_tahun_akademik);
        if ($ringkas['acc'] < 1) {
            return 'Belum ada topik bimbingan yang di-ACC. ACC minimal satu topik sebelum menyatakan layak sidang.';
        }
        if (count($this->Jadwal_proposal_model->get_by_mahasiswa($proposal->id)) > 0) {
            return 'Sidang sudah dijadwalkan. Nilai bimbingan tidak dapat diubah lagi.';
        }
        return null;
    }

    private function ambil_milik_dosen($id_proposal) {
        $id_dosen = $this->session->userdata('ref_id');
        $proposal = $this->Proposal_model->get_by_id($id_proposal);
        if (!$id_dosen || !$proposal || (int) $proposal->id_pembimbing !== (int) $id_dosen) {
            $this->session->set_flashdata('error', 'Anda bukan pembimbing mahasiswa ini.');
            redirect('Proposal_mahasiswa_dosen');
        }
        return $proposal;
    }
}
