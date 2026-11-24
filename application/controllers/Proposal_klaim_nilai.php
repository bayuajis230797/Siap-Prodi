<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Proposal_klaim_nilai Controller (Mahasiswa)
 * Pengajuan klaim nilai Proposal TA.
 * Syarat: jadwal sidang selesai (kedua penguji menilai) dan kedua penguji menyatakan layak klaim.
 */
class Proposal_klaim_nilai extends MY_Controller {

    public function __construct() {
        parent::__construct();
        $this->check_role('mahasiswa');
        $this->load->model('Proposal_model');
        $this->load->model('Jadwal_proposal_model');
        $this->load->model('Proposal_klaim_nilai_model');
        $this->load->model('Proposal_berkas_klaim_nilai_model');
        $this->load->model('Proposal_berkas_klaim_upload_model');
        $this->load->model('Proposal_nilai_sidang_model');
        $this->load->model('Proposal_nilai_rekap_model');
        $this->load->model('Tahun_akademik_model');
        $this->load->model('Bobot_nilai_setting_model');
        $this->load->model('Grade_setting_model');
        $this->load->library('Bimbingan_pasca_service');
        $this->load->helper(array('proposal', 'google_drive'));
    }

    /** @return array [proposal, jadwal, pasca_status, tahun_aktif]; redirect bila belum memenuhi syarat dasar */
    private function konteks() {
        $tahun_aktif = $this->Tahun_akademik_model->get_aktif();
        if (!$tahun_aktif) {
            $this->session->set_flashdata('error', 'Tidak ada tahun akademik aktif.');
            redirect('Halaman_utama');
        }
        $proposal = $this->Proposal_model->get_by_mahasiswa($this->session->userdata('ref_id'), $tahun_aktif->id);
        if (!$proposal) {
            $this->session->set_flashdata('error', 'Anda belum terdaftar untuk Proposal TA.');
            redirect('Halaman_utama');
        }
        $jadwal_list = $this->Jadwal_proposal_model->get_by_mahasiswa($proposal->id);
        if (empty($jadwal_list)) {
            $this->session->set_flashdata('error', 'Anda belum memiliki jadwal sidang.');
            redirect('Halaman_utama');
        }
        $jadwal = $jadwal_list[0];
        $pasca = $this->bimbingan_pasca_service->proposal_klaim_status($proposal->id);
        return array($proposal, $jadwal, $pasca, $tahun_aktif);
    }

    /** Null bila boleh klaim; selain itu alasannya. */
    private function alasan_belum_boleh($jadwal, $pasca) {
        if ($jadwal->status !== 'selesai') {
            return 'Penilaian sidang belum lengkap. Klaim dapat diajukan setelah kedua penguji menilai.';
        }
        if (empty($pasca['ok'])) {
            return $pasca['message'];
        }
        return null;
    }

    public function index() {
        list($proposal, $jadwal, $pasca, $tahun_aktif) = $this->konteks();
        $klaim = $this->Proposal_klaim_nilai_model->get_by_mahasiswa_proposal($proposal->id);
        $rekap = $this->Proposal_nilai_rekap_model->get_by_jadwal($jadwal->id);

        $bobot = $this->Bobot_nilai_setting_model->get_bobot_perhitungan('proposal');
        $nilai_akhir = null;
        $grade = null;
        if ($rekap && $rekap->nilai_akhir !== null) {
            $nilai_akhir = (float) $rekap->nilai_akhir;
            $grade = $rekap->grade;
        } elseif ($proposal->nilai_bimbingan !== null && $proposal->nilai_sidang !== null) {
            $nilai_akhir = $proposal->nilai_proposal_final !== null ? (float) $proposal->nilai_proposal_final
                : hitung_nilai_akhir_proposal($proposal->nilai_bimbingan, $proposal->nilai_sidang);
            $grade = $proposal->nilai_huruf ?: konversi_nilai_huruf_proposal($nilai_akhir);
        }

        // Nilai hanya ditampilkan setelah klaim mahasiswa disetujui sekprodi
        $nilai_tampil = ($klaim && $klaim->status === 'disetujui');
        if (!$nilai_tampil) {
            $nilai_akhir = null;
            $grade = null;
        }

        $data = array(
            'title' => 'Klaim Nilai Proposal TA',
            'page_title' => 'Klaim Nilai Proposal TA',
            'breadcrumb' => array(
                array('label' => 'Proposal TA', 'url' => '#'),
                array('label' => 'Klaim Nilai', 'active' => true)
            ),
            'sidebar_menu' => generate_sidebar_menu('mahasiswa'),
            'proposal' => $proposal,
            'jadwal' => $jadwal,
            'tahun_aktif' => $tahun_aktif,
            'klaim' => $klaim,
            'rekap' => $rekap,
            'berkas_list' => $this->Proposal_berkas_klaim_nilai_model->get_all(true),
            'berkas_uploaded' => $klaim ? $this->Proposal_berkas_klaim_upload_model->get_by_klaim_nilai($klaim->id) : array(),
            'pasca_status' => $pasca,
            'syarat_pesan' => $this->alasan_belum_boleh($jadwal, $pasca),
            'nilai_tampil' => $nilai_tampil,
            'nilai_bimbingan' => $nilai_tampil ? $proposal->nilai_bimbingan : null,
            'nilai_sidang' => $nilai_tampil ? $proposal->nilai_sidang : null,
            'bobot_bimbingan' => isset($bobot['bimbingan']) ? $bobot['bimbingan'] : 40,
            'bobot_sidang' => isset($bobot['sidang']) ? $bobot['sidang'] : 60,
            'nilai_akhir' => $nilai_akhir,
            'grade' => $grade,
            'grade_list' => $this->Grade_setting_model->get_by_tipe('proposal')
        );
        $data['content'] = $this->load->view('mahasiswa/proposal/klaim_nilai', $data, true);
        $this->load->view('layouts/main', $data);
    }

    public function submit() {
        list($proposal, $jadwal, $pasca) = $this->konteks();
        if ($this->input->method() !== 'post') {
            redirect('Proposal_klaim_nilai');
        }

        $alasan = $this->alasan_belum_boleh($jadwal, $pasca);
        if ($alasan !== null) {
            $this->session->set_flashdata('error', $alasan);
            redirect('Proposal_klaim_nilai');
        }

        $klaim = $this->Proposal_klaim_nilai_model->get_by_mahasiswa_proposal($proposal->id);
        if ($klaim && $klaim->status !== 'ditolak') {
            $this->session->set_flashdata('error', 'Klaim nilai Anda ' . ($klaim->status === 'disetujui' ? 'sudah disetujui' : 'sedang diverifikasi') . ' dan tidak dapat diubah.');
            redirect('Proposal_klaim_nilai');
        }

        $berkas_list = $this->Proposal_berkas_klaim_nilai_model->get_all(true);
        if (empty($berkas_list)) {
            $this->session->set_flashdata('error', 'Belum ada berkas klaim yang ditentukan. Hubungi Sekprodi atau Admin.');
            redirect('Proposal_klaim_nilai');
        }

        foreach ($berkas_list as $b) {
            $field = 'file_berkas_' . $b->id;
            $ada = ($klaim && $this->Proposal_berkas_klaim_upload_model->get_by_klaim_berkas($klaim->id, $b->id));
            if (empty($_FILES[$field]['name'])) {
                if (!empty($b->wajib) && !$ada) {
                    $this->session->set_flashdata('error', "Berkas wajib '{$b->nama_berkas}' belum diunggah.");
                    redirect('Proposal_klaim_nilai');
                }
            }
        }

        $needs_upload = false;
        foreach ($berkas_list as $b) {
            if (!empty($_FILES['file_berkas_' . $b->id]['name'])) {
                $needs_upload = true;
                break;
            }
        }
        if ($needs_upload) {
            $this->load->helper('google_drive');
            require_google_drive_or_redirect();
        }
        $this->load->library('Upload_to_drive');
        $share_emails = array();
        $sekprodi_owner = $this->upload_to_drive->find_connected_sekprodi_user_id();
        if ($sekprodi_owner) {
            $email = $this->upload_to_drive->get_drive_email($sekprodi_owner);
            if ($email) {
                $share_emails[] = $email;
            }
        }
        $upload_opts = $this->upload_to_drive->default_options(array('SIAP', 'Proposal', 'klaim_nilai'), $share_emails);
        $link = array();
        foreach ($berkas_list as $b) {
            $field = 'file_berkas_' . $b->id;
            if (empty($_FILES[$field]['name'])) {
                continue;
            }
            $upload = $this->upload_to_drive->handle_upload($field, $upload_opts);
            if (empty($upload['ok'])) {
                $this->session->set_flashdata('error', (isset($upload['message']) ? $upload['message'] : 'Gagal upload.') . ' (' . $b->nama_berkas . ')');
                redirect('Proposal_klaim_nilai');
            }
            $link[$b->id] = array(
                'path' => $upload['webViewLink'],
                'name' => !empty($upload['file_name']) ? $upload['file_name'] : $b->nama_berkas
            );
        }

        $this->db->trans_start();
        if (!$klaim) {
            $id_klaim = $this->Proposal_klaim_nilai_model->create(array(
                'id_mahasiswa_proposal' => $proposal->id,
                'id_jadwal_proposal' => $jadwal->id,
                'id_tahun_akademik' => $proposal->id_tahun_akademik,
                'status' => 'menunggu'
            ));
        } else {
            $id_klaim = $klaim->id;
            $this->Proposal_klaim_nilai_model->update($id_klaim, array('status' => 'menunggu'));
        }
        foreach ($berkas_list as $b) {
            if (!isset($link[$b->id])) {
                continue;
            }
            $ex = $this->Proposal_berkas_klaim_upload_model->get_by_klaim_berkas($id_klaim, $b->id);
            $row = array('file_path' => $link[$b->id]['path'], 'file_name' => $link[$b->id]['name'], 'file_size' => null);
            if ($ex) {
                $this->Proposal_berkas_klaim_upload_model->update($ex->id, $row);
            } else {
                $this->Proposal_berkas_klaim_upload_model->create($row + array('id_klaim_nilai' => $id_klaim, 'id_berkas_klaim_nilai' => $b->id));
            }
        }
        $this->db->trans_complete();

        if ($this->db->trans_status()) {
            $this->load->helper(array('notifikasi', 'sekprodi_badge'));
            notify_sekprodi(
                'Klaim Nilai Proposal TA Baru',
                'Ada klaim nilai Proposal TA baru yang menunggu verifikasi.',
                site_url('Proposal_verifikasi_klaim'),
                'proposal_klaim'
            );
            $this->session->set_flashdata('success', 'Klaim nilai berhasil dikirim. Menunggu verifikasi sekprodi.');
        } else {
            $this->session->set_flashdata('error', 'Gagal menyimpan klaim nilai.');
        }
        redirect('Proposal_klaim_nilai');
    }
}
