<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Proposal_pendaftaran
 * Aturan pendaftaran sidang Proposal TA (satu tahap): setelah pembimbing menyatakan layak sidang,
 * mahasiswa mengirim berkas; sekprodi memverifikasi lalu menetapkan jadwal dan Penguji 1 dan 2 sekaligus.
 */
class Proposal_pendaftaran {

    protected $CI;

    public function __construct() {
        $this->CI =& get_instance();
        $this->CI->load->model('Proposal_pendaftaran_sidang_model');
        $this->CI->load->model('Proposal_berkas_sidang_model');
        $this->CI->load->model('Proposal_berkas_upload_model');
        $this->CI->load->model('Jadwal_proposal_model');
        $this->CI->load->helper('google_drive');
    }

    public function berkas_list() {
        return $this->CI->Proposal_berkas_sidang_model->get_all(true);
    }

    /**
     * Null bila mahasiswa memenuhi syarat mendaftar sidang; selain itu pesan alasannya.
     * @param object $proposal baris mahasiswa_proposal
     */
    public function syarat($proposal) {
        if (empty($proposal->id_pembimbing)) {
            return 'Sekprodi belum menetapkan pembimbing untuk Anda.';
        }
        if ($proposal->status !== 'layak_sidang') {
            return 'Anda belum dinyatakan layak sidang oleh pembimbing (nilai bimbingan dan ACC layak sidang).';
        }
        return null;
    }

    /**
     * Simpan pendaftaran dan link berkas.
     * Hanya boleh bila belum ada pendaftaran atau pendaftaran sebelumnya ditolak.
     * @return array ['ok' => bool, 'message' => string]
     */
    public function simpan($proposal, $post) {
        $alasan = $this->syarat($proposal);
        if ($alasan !== null) {
            return array('ok' => false, 'message' => $alasan);
        }

        $pendaftaran = $this->CI->Proposal_pendaftaran_sidang_model->get_by_mahasiswa_proposal($proposal->id);
        if ($pendaftaran && $pendaftaran->status !== 'ditolak') {
            $teks = array('menunggu' => 'sedang menunggu verifikasi sekprodi', 'disetujui' => 'sudah disetujui', 'selesai' => 'sudah selesai');
            return array('ok' => false, 'message' => 'Pendaftaran ' . (isset($teks[$pendaftaran->status]) ? $teks[$pendaftaran->status] : 'tidak dapat diubah') . ' dan tidak dapat diubah.');
        }

        $berkas_list = $this->berkas_list();
        if (empty($berkas_list)) {
            return array('ok' => false, 'message' => 'Belum ada berkas sidang yang ditentukan. Hubungi Sekprodi atau Admin.');
        }

        foreach ($berkas_list as $b) {
            $field = 'file_berkas_' . $b->id;
            $ada = ($pendaftaran && $this->CI->Proposal_berkas_upload_model->get_by_pendaftaran_berkas($pendaftaran->id, $b->id));
            if (empty($_FILES[$field]['name'])) {
                if (!empty($b->wajib) && !$ada) {
                    return array('ok' => false, 'message' => "Berkas wajib '{$b->nama_berkas}' belum diunggah.");
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
            require_google_drive_or_redirect();
        }
        $this->CI->load->library('Upload_to_drive');
        $share_emails = array();
        $sekprodi_owner = $this->CI->upload_to_drive->find_connected_sekprodi_user_id();
        if ($sekprodi_owner) {
            $email = $this->CI->upload_to_drive->get_drive_email($sekprodi_owner);
            if ($email) {
                $share_emails[] = $email;
            }
        }
        $upload_opts = $this->CI->upload_to_drive->default_options(array('SIAP', 'Proposal', 'sidang'), $share_emails);
        $link = array();
        foreach ($berkas_list as $b) {
            $field = 'file_berkas_' . $b->id;
            if (empty($_FILES[$field]['name'])) {
                continue;
            }
            $upload = $this->CI->upload_to_drive->handle_upload($field, $upload_opts);
            if (empty($upload['ok'])) {
                return array('ok' => false, 'message' => (isset($upload['message']) ? $upload['message'] : 'Gagal upload.') . ' (' . $b->nama_berkas . ')');
            }
            $link[$b->id] = array(
                'path' => $upload['webViewLink'],
                'name' => !empty($upload['file_name']) ? $upload['file_name'] : $b->nama_berkas
            );
        }

        $this->CI->db->trans_start();
        if (!$pendaftaran) {
            $id = $this->CI->Proposal_pendaftaran_sidang_model->create(array(
                'id_mahasiswa_proposal' => $proposal->id,
                'id_tahun_akademik' => $proposal->id_tahun_akademik,
                'status' => 'menunggu'
            ));
        } else {
            $id = $pendaftaran->id;
            $this->CI->Proposal_pendaftaran_sidang_model->update($id, array('status' => 'menunggu'));
        }

        foreach ($berkas_list as $b) {
            if (!isset($link[$b->id])) {
                continue;
            }
            $existing = $this->CI->Proposal_berkas_upload_model->get_by_pendaftaran_berkas($id, $b->id);
            $row = array('file_path' => $link[$b->id]['path'], 'file_name' => $link[$b->id]['name'], 'file_size' => null);
            if ($existing) {
                $this->CI->Proposal_berkas_upload_model->update($existing->id, $row);
            } else {
                $this->CI->Proposal_berkas_upload_model->create($row + array('id_pendaftaran_sidang' => $id, 'id_berkas_sidang' => $b->id));
            }
        }
        $this->CI->db->trans_complete();

        if (!$this->CI->db->trans_status()) {
            return array('ok' => false, 'message' => 'Gagal menyimpan pendaftaran.');
        }

        $this->CI->load->helper(array('notifikasi', 'sekprodi_badge'));
        if (function_exists('notify_sekprodi')) {
            notify_sekprodi(
                'Pendaftaran Sidang Proposal TA Baru',
                'Ada pendaftaran sidang Proposal TA baru yang menunggu verifikasi berkas.',
                site_url('Proposal_verifikasi_berkas_sidang'),
                'proposal_sidang'
            );
        }

        return array('ok' => true, 'message' => 'Pendaftaran berhasil dikirim. Menunggu verifikasi sekprodi.', 'id' => $id);
    }
}
