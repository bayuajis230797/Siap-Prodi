<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Jadwal_proposal_sekprodi Controller (Sekprodi, Kaprodi hanya melihat)
 * Daftar, lihat, ubah jadwal sidang Proposal TA, dan ekspor CSV.
 * Penjadwalan awal dilakukan lewat Proposal_verifikasi_berkas_sidang.
 */
class Jadwal_proposal_sekprodi extends MY_Controller {

    public function __construct() {
        parent::__construct();
        $this->check_role(array('sekprodi', 'kaprodi'));
        $this->load->model('Jadwal_proposal_model');
        $this->load->model('Proposal_model');
        $this->load->model('Proposal_nilai_sidang_model');
        $this->load->model('Dosen_model');
        $this->load->model('Tahun_akademik_model');
        $this->load->library('pagination');
        $this->load->library('Proposal_jadwal');
        $this->load->helper(array('proposal', 'app'));
    }

    public function index() {
        $tahun_aktif = $this->Tahun_akademik_model->get_aktif();
        $id_tahun = $tahun_aktif ? $tahun_aktif->id : null;
        $status = $this->input->get('status');
        if (!in_array($status, array('terjadwal', 'selesai', 'batal'), true)) {
            $status = null;
        }
        $per_page = (int) $this->input->get('per_page');
        $per_page = in_array($per_page, array(10, 25, 50, 100), true) ? $per_page : 10;

        $total = $this->Jadwal_proposal_model->count_all($id_tahun, $status);
        $this->pagination->initialize(proposal_pagination_config(
            site_url('Jadwal_proposal_sekprodi/index'), $total, $per_page,
            array('status' => $status, 'per_page' => $per_page != 10 ? $per_page : null)
        ));
        $page = max(1, (int) $this->uri->segment(3));
        $list = $this->Jadwal_proposal_model->get_all($id_tahun, $status, $per_page, ($page - 1) * $per_page);

        $data = array(
            'title' => 'Jadwal Sidang Proposal TA',
            'page_title' => 'Jadwal Sidang Proposal TA',
            'breadcrumb' => array(
                array('label' => 'Proposal TA', 'url' => '#'),
                array('label' => 'Jadwal Sidang', 'active' => true)
            ),
            'sidebar_menu' => generate_sidebar_menu(get_user_role()),
            'jadwal_list' => $list,
            'pagination' => $this->pagination->create_links(),
            'total_rows' => $total,
            'tahun_aktif' => $tahun_aktif,
            'status_filter' => $status,
            'per_page' => $per_page,
            'bisa_ubah' => get_user_role() === 'sekprodi'
        );
        $data['content'] = $this->load->view('sekprodi/proposal_jadwal/index', $data, true);
        $this->load->view('layouts/main', $data);
    }

    public function view($id) {
        $jadwal = $this->ambil_jadwal($id);
        $data = array(
            'title' => 'Detail Jadwal Sidang Proposal TA',
            'page_title' => 'Detail Jadwal Sidang Proposal TA',
            'breadcrumb' => array(
                array('label' => 'Jadwal Sidang', 'url' => site_url('Jadwal_proposal_sekprodi')),
                array('label' => 'Detail', 'active' => true)
            ),
            'sidebar_menu' => generate_sidebar_menu(get_user_role()),
            'jadwal' => $jadwal,
            'judul' => get_judul_proposal_terbaru($jadwal->id_mahasiswa, $jadwal->id_tahun_akademik),
            'nilai_list' => $this->Proposal_nilai_sidang_model->get_by_jadwal($jadwal->id),
            'bisa_ubah' => get_user_role() === 'sekprodi'
        );
        $data['content'] = $this->load->view('sekprodi/proposal_jadwal/view', $data, true);
        $this->load->view('layouts/main', $data);
    }

    public function edit($id) {
        $this->assert_not_kaprodi('Jadwal_proposal_sekprodi');
        $jadwal = $this->ambil_jadwal($id);
        $url_edit = 'Jadwal_proposal_sekprodi/edit/' . $id;

        $nilai_ada = count($this->Proposal_nilai_sidang_model->get_by_jadwal($jadwal->id)) > 0;
        $terkunci = ($jadwal->status === 'selesai');

        if ($this->input->method() === 'post') {
            if ($terkunci) {
                $this->session->set_flashdata('error', 'Jadwal sudah selesai (semua penguji telah menilai) dan tidak dapat diubah.');
                redirect($url_edit);
            }

            $boleh_lampau = (trim((string) $this->input->post('tanggal_ujian')) === (string) $jadwal->tanggal_ujian);
            $w = $this->proposal_jadwal->validasi_waktu($_POST, $boleh_lampau);
            $errors = $w['errors'];

            $status = $this->input->post('status');
            if (!in_array($status, array('terjadwal', 'batal'), true)) {
                $errors[] = 'Status jadwal tidak valid.';
            }

            // Penguji: boleh kosong hanya bila belum pernah ditetapkan
            $ids = array(null, null);
            $ada_penguji_lama = !empty($jadwal->id_penguji_1) || !empty($jadwal->id_penguji_2);
            $isi = (int) $this->input->post('id_penguji_1') > 0 || (int) $this->input->post('id_penguji_2') > 0;
            if ($isi || $ada_penguji_lama) {
                $mhs = $this->Proposal_model->get_by_id($jadwal->id_mahasiswa_proposal);
                $p = $this->proposal_jadwal->validasi_penguji($_POST, $mhs ? $mhs->id_pembimbing : null);
                $errors = array_merge($errors, $p['errors']);
                $ids = $p['ids'];
                // Penguji tidak boleh diganti bila sudah ada nilai sidang
                if ($nilai_ada && empty($p['errors']) && ((int) $ids[0] !== (int) $jadwal->id_penguji_1 || (int) $ids[1] !== (int) $jadwal->id_penguji_2)) {
                    $errors[] = 'Penguji tidak dapat diganti karena sudah ada penilaian sidang.';
                }
            }

            if (empty($errors) && $status === 'terjadwal') {
                $bentrok = $this->proposal_jadwal->bentrok($w['data'], $jadwal->id_tahun_akademik, $jadwal->id,
                    ($ids[0] && $ids[1]) ? $ids : null);
                if ($bentrok !== null) {
                    $errors[] = $bentrok;
                }
            }

            if (!empty($errors)) {
                $this->session->set_flashdata('error', implode('<br>', $errors));
                redirect($url_edit);
            }

            $update = $w['data'] + array(
                'status' => $status,
                'catatan' => trim((string) $this->input->post('catatan')) ?: null,
                'id_penguji_1' => $ids[0],
                'id_penguji_2' => $ids[1]
            );
            $this->Jadwal_proposal_model->update($jadwal->id, $update);

            $tgl = date('d/m/Y', strtotime($w['data']['tanggal_ujian'])) . ' ' . substr($w['data']['jam_mulai'], 0, 5) . ' di ' . $w['data']['ruangan'];
            proposal_notify('mahasiswa', $jadwal->id_mahasiswa, 'Jadwal Sidang Proposal TA Diperbarui',
                $status === 'batal' ? 'Jadwal sidang Anda dibatalkan.' : 'Jadwal sidang Anda: ' . $tgl, site_url('Pendaftaran_sidang_proposal'));
            foreach ($ids as $id_dosen) {
                if ($id_dosen) {
                    proposal_notify('dosen', $id_dosen, 'Jadwal Sidang Proposal TA Diperbarui', 'Jadwal sidang yang Anda uji: ' . $tgl, site_url('Jadwal_proposal_dosen'));
                }
            }

            $this->session->set_flashdata('success', 'Jadwal sidang berhasil diperbarui.');
            redirect('Jadwal_proposal_sekprodi');
        }

        $mhs = $this->Proposal_model->get_by_id($jadwal->id_mahasiswa_proposal);
        $data = array(
            'title' => 'Ubah Jadwal Sidang Proposal TA',
            'page_title' => 'Ubah Jadwal Sidang Proposal TA',
            'breadcrumb' => array(
                array('label' => 'Jadwal Sidang', 'url' => site_url('Jadwal_proposal_sekprodi')),
                array('label' => 'Ubah', 'active' => true)
            ),
            'sidebar_menu' => generate_sidebar_menu('sekprodi'),
            'jadwal' => $jadwal,
            'id_pembimbing' => $mhs ? $mhs->id_pembimbing : null,
            'dosen_list' => $this->Dosen_model->get_all_active(),
            'terkunci' => $terkunci,
            'nilai_ada' => $nilai_ada
        );
        $data['content'] = $this->load->view('sekprodi/proposal_jadwal/edit', $data, true);
        $this->load->view('layouts/main', $data);
    }

    /**
     * Ekspor CSV jadwal sidang tahun akademik aktif.
     */
    public function export_csv() {
        $tahun_aktif = $this->Tahun_akademik_model->get_aktif();
        if (!$tahun_aktif) {
            $this->session->set_flashdata('error', 'Tidak ada tahun akademik aktif.');
            redirect('Jadwal_proposal_sekprodi');
        }
        $status = $this->input->get('status');
        $rows = $this->baris_export($tahun_aktif, in_array($status, array('terjadwal', 'selesai', 'batal'), true) ? $status : null);

        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment;filename="Jadwal_Sidang_Proposal_TA_' . date('YmdHis') . '.csv"');
        header('Cache-Control: max-age=0');
        $out = fopen('php://output', 'w');
        fprintf($out, chr(0xEF) . chr(0xBB) . chr(0xBF));
        foreach ($rows as $r) {
            fputcsv($out, $r);
        }
        fclose($out);
        exit;
    }

    /** Susun baris CSV (judul, header, data). Dipisah agar dapat diuji. */
    protected function baris_export($tahun_aktif, $status) {
        $rows = array(
            array('JADWAL SIDANG PROPOSAL TA'),
            array('Tahun Akademik: ' . $tahun_aktif->tahun_akademik . ' ' . ucfirst($tahun_aktif->semester)),
            array('Tanggal Export: ' . date('d F Y H:i:s')),
            array(),
            array('No', 'NIM', 'Nama Mahasiswa', 'Kelas', 'Judul Proposal', 'Tanggal Ujian', 'Hari', 'Jam Mulai', 'Jam Selesai', 'Ruangan',
                'Penguji 1', 'NIDN Penguji 1', 'Penguji 2', 'NIDN Penguji 2', 'Status', 'Nilai Bimbingan', 'Nilai Sidang', 'Nilai Akhir', 'Grade')
        );
        $no = 1;
        foreach ($this->Jadwal_proposal_model->get_all($tahun_aktif->id, $status, 10000, 0) as $j) {
            $f = function ($v) { return ($v === null || $v === '') ? '-' : number_format((float) $v, 2, ',', ''); };
            $rows[] = array(
                $no++, $j->nim ?? '-', $j->nama_mahasiswa ?? '-', $j->kelas ?? '-', $j->judul_proposal ?? '-',
                $j->tanggal_ujian ? date('d/m/Y', strtotime($j->tanggal_ujian)) : '-', hari_dari_tanggal($j->tanggal_ujian),
                $j->jam_mulai ? date('H:i', strtotime($j->jam_mulai)) : '-', $j->jam_selesai ? date('H:i', strtotime($j->jam_selesai)) : '-',
                $j->ruangan ?? '-', $j->penguji_1 ?? '-', $j->penguji_1_nidn ?? '-', $j->penguji_2 ?? '-', $j->penguji_2_nidn ?? '-',
                ucfirst($j->status), $f($j->nilai_bimbingan), $f($j->nilai_sidang), $f($j->nilai_proposal_final), $j->nilai_huruf ?: '-'
            );
        }
        $rows[] = array();
        $rows[] = array('TOTAL', count($rows) > 0 ? ($no - 1) . ' Jadwal' : '0 Jadwal');
        return $rows;
    }

    private function ambil_jadwal($id) {
        $jadwal = $this->Jadwal_proposal_model->get_by_id($id);
        if (!$jadwal) {
            $this->session->set_flashdata('error', 'Data jadwal tidak ditemukan.');
            redirect('Jadwal_proposal_sekprodi');
        }
        return $jadwal;
    }
}
