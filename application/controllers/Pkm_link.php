<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Akses aman link Google Drive PKM — URL asli tidak diekspos di HTML.
 */
class Pkm_link extends MY_Controller {

    public function __construct() {
        parent::__construct();
        $this->load->helper(array('pkm', 'google_drive', 'id_encode'));
    }

    /**
     * @param string $tipe proposal_awal|proposal_final|log|bukti_judul
     * @param string $encoded ID terenkode
     */
    public function buka($tipe = null, $encoded = null) {
        if (!$this->session->userdata('user_id')) {
            redirect('Auth/login');
        }
        $id = decode_id($encoded);
        if (!$id || !$tipe) {
            show_404();
        }

        $role = $this->session->userdata('role');
        $ref_id = (int) $this->session->userdata('ref_id');
        $stored = null;

        switch ($tipe) {
            case 'proposal_awal':
            case 'proposal_final':
                $this->load->model('Pkm_pengajuan_model');
                $pengajuan = $this->Pkm_pengajuan_model->get_by_id($id);
                if (!$pengajuan || !$this->_can_access_pengajuan($pengajuan, $role, $ref_id)) {
                    show_404();
                }
                $field = ($tipe === 'proposal_awal') ? 'link_proposal_awal' : 'link_proposal_final';
                $stored = $pengajuan->$field ?? null;
                break;

            case 'log':
                $this->load->model('Pkm_bimbingan_log_model');
                $this->load->model('Pkm_bimbingan_model');
                $log = $this->Pkm_bimbingan_log_model->get_by_id($id);
                if (!$log) {
                    show_404();
                }
                $bimbingan = $this->Pkm_bimbingan_model->get_by_id($log->id_bimbingan);
                if (!$bimbingan || !$this->_can_access_bimbingan($bimbingan, $role, $ref_id)) {
                    show_404();
                }
                $stored = $log->file_path ?? null;
                break;

            case 'bukti_judul':
                $this->load->model('Pkm_perubahan_judul_model');
                $row = $this->Pkm_perubahan_judul_model->get_by_id($id);
                if (!$row || !$this->_can_access_perubahan($row, $role, $ref_id)) {
                    show_404();
                }
                $stored = $row->link_bukti ?? null;
                break;

            default:
                show_404();
        }

        $url = pkm_decrypt_link($stored);
        if ($url === '' || !is_valid_google_drive_link($url)) {
            show_404();
        }

        redirect(normalize_google_drive_link($url), 'location', 302);
    }

    private function _can_access_pengajuan($pengajuan, $role, $ref_id) {
        if (in_array($role, array('sekprodi', 'admin'), true)) {
            return true;
        }
        if ($role === 'mahasiswa') {
            $this->load->model('Pkm_pengajuan_model');
            return $this->Pkm_pengajuan_model->mahasiswa_akses_pengajuan($ref_id, $pengajuan->id) !== null;
        }
        if ($role === 'dosen' && (int) $pengajuan->id_pembimbing_final === $ref_id) {
            return true;
        }
        return false;
    }

    private function _can_access_bimbingan($bimbingan, $role, $ref_id) {
        if (in_array($role, array('sekprodi', 'admin'), true)) {
            return true;
        }
        if ($role === 'mahasiswa') {
            $this->load->model('Pkm_pengajuan_model');
            return $this->Pkm_pengajuan_model->mahasiswa_akses_pengajuan($ref_id, $bimbingan->id_pkm_pengajuan) !== null;
        }
        if ($role === 'dosen' && (int) $bimbingan->id_pembimbing === $ref_id) {
            return true;
        }
        return false;
    }

    private function _can_access_perubahan($row, $role, $ref_id) {
        if (in_array($role, array('sekprodi', 'admin'), true)) {
            return true;
        }
        if ($role === 'mahasiswa' && (int) $row->id_ketua === $ref_id) {
            return true;
        }
        if ($role === 'dosen' && (int) $row->id_pembimbing_final === $ref_id) {
            return true;
        }
        return false;
    }
}
