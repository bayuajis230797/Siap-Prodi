<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Serve file sensitif (QR code dll.) — tidak via folder uploads publik.
 */
class File_asset extends MY_Controller {

    public function __construct() {
        parent::__construct();
        $this->load->helper('qrcode');
    }

    public function jadwal_uts_qr($id) {
        $this->_require_jadwal_qr_access();
        $this->load->model('Jadwal_uts_model');
        $jadwal = $this->Jadwal_uts_model->get_by_id((int) $id);
        if (!$jadwal || empty($jadwal->qr_code)) {
            show_404();
        }
        $this->_output_png($jadwal->qr_code);
    }

    public function jadwal_uas_qr($id) {
        $this->_require_jadwal_qr_access();
        $this->load->model('Jadwal_uas_model');
        $jadwal = $this->Jadwal_uas_model->get_by_id((int) $id);
        if (!$jadwal || empty($jadwal->qr_code)) {
            show_404();
        }
        $this->_output_png($jadwal->qr_code);
    }

    public function kegiatan_qr($id) {
        $role = $this->session->userdata('role');
        if (!in_array($role, array('sekprodi', 'admin', 'dosen'), true)) {
            show_error('Akses ditolak.', 403);
        }
        $this->load->model('Kegiatan_model');
        $kegiatan = $this->Kegiatan_model->get_by_id((int) $id);
        if (!$kegiatan || empty($kegiatan->qr_code)) {
            show_404();
        }
        $this->_output_png($kegiatan->qr_code);
    }

    public function tanda_terima_qr($id_item) {
        $role = $this->session->userdata('role');
        if (!in_array($role, array('sekprodi', 'admin', 'mahasiswa', 'dosen'), true)) {
            show_error('Akses ditolak.', 403);
        }
        $this->load->model('Tanda_terima_item_model');
        $item = $this->Tanda_terima_item_model->get_by_id((int) $id_item);
        if (!$item || empty($item->qr_code)) {
            show_404();
        }
        $this->_output_png($item->qr_code);
    }

    private function _require_jadwal_qr_access() {
        $role = $this->session->userdata('role');
        if (!in_array($role, array('sekprodi', 'admin', 'dosen', 'tendik', 'kaprodi'), true)) {
            show_error('Akses ditolak.', 403);
        }
    }

    private function _require_staff_or_dosen() {
        $role = $this->session->userdata('role');
        if (!in_array($role, array('sekprodi', 'admin', 'dosen'), true)) {
            show_error('Akses ditolak.', 403);
        }
    }

    private function _output_png($relative_path) {
        $full = qr_code_resolve_full_path($relative_path);
        if (!$full) {
            show_404();
        }
        $this->output
            ->set_content_type('image/png')
            ->set_header('Cache-Control: private, no-store, max-age=0')
            ->set_header('X-Content-Type-Options: nosniff')
            ->set_output(file_get_contents($full));
    }
}
