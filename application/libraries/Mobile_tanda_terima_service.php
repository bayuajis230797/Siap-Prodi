<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Logika scan tanda terima barang (mahasiswa / dosen).
 */
class Mobile_tanda_terima_service {

    /** @var CI_Controller */
    protected $CI;

    public function __construct() {
        $this->CI =& get_instance();
        $this->CI->load->model('Tanda_terima_model');
        $this->CI->load->model('Tanda_terima_item_model');
        $this->CI->load->model('Tanda_terima_penerimaan_model');
        $this->CI->load->model('Mahasiswa_model');
        $this->CI->load->model('Dosen_model');
    }

    /**
     * @param string $raw
     * @return string
     */
    public function normalize_scan_token($raw) {
        $t = trim((string) $raw);
        if ($t === '') {
            return '';
        }
        if (preg_match('#Tanda_terima_mahasiswa/scan/([^/?&#\s]+)#i', $t, $m)) {
            return rawurldecode($m[1]);
        }
        if (preg_match('#Tanda_terima_dosen/scan/([^/?&#\s]+)#i', $t, $m)) {
            return rawurldecode($m[1]);
        }
        if (preg_match('#Tanda_terima_ambil/scan/([^/?&#\s]+)#i', $t, $m)) {
            return rawurldecode($m[1]);
        }
        if (preg_match('/(TT_[A-Fa-f0-9]+)/', $t, $m)) {
            return $m[1];
        }
        if (preg_match('#https?://#i', $t)) {
            $p = parse_url($t);
            if (!empty($p['path'])) {
                $parts = array_values(array_filter(explode('/', $p['path'])));
                $last = end($parts);
                if ($last !== false && $last !== '') {
                    return rawurldecode($last);
                }
            }
        }
        if (strpos($t, '/') !== false) {
            $parts = array_values(array_filter(explode('/', $t)));
            $last = end($parts);
            if ($last !== false && $last !== '') {
                return $last;
            }
        }
        return $t;
    }

    /**
     * @param int $id_mahasiswa
     * @param string $token
     * @return array
     */
    public function scan_mahasiswa($id_mahasiswa, $token) {
        $id_mahasiswa = (int) $id_mahasiswa;
        if ($id_mahasiswa <= 0) {
            return array('success' => false, 'message' => 'Data mahasiswa tidak valid.');
        }
        $token = $this->normalize_scan_token($token);
        if ($token === '') {
            return array('success' => false, 'message' => 'Token QR Code tidak valid.');
        }
        return $this->process_scan('mahasiswa', $id_mahasiswa, null, $token);
    }

    /**
     * @param int $id_dosen
     * @param string $token
     * @return array
     */
    public function scan_dosen($id_dosen, $token) {
        $id_dosen = (int) $id_dosen;
        if ($id_dosen <= 0) {
            return array('success' => false, 'message' => 'Data dosen tidak valid.');
        }
        $token = $this->normalize_scan_token($token);
        if ($token === '') {
            return array('success' => false, 'message' => 'Token QR Code tidak valid.');
        }
        return $this->process_scan('dosen', null, $id_dosen, $token);
    }

    /**
     * @param string $jenis_penerima mahasiswa|dosen
     * @param int|null $id_mahasiswa
     * @param int|null $id_dosen
     * @param string $token
     * @return array
     */
    protected function process_scan($jenis_penerima, $id_mahasiswa, $id_dosen, $token) {
        $item = $this->CI->Tanda_terima_item_model->get_with_distribusi_by_token($token);
        if (!$item) {
            return array('success' => false, 'message' => 'QR Code tidak valid atau barang tidak ditemukan.');
        }
        if ($item->jenis_penerima === 'mahasiswa' && $jenis_penerima !== 'mahasiswa') {
            return array('success' => false, 'message' => 'QR Code ini hanya untuk mahasiswa terdaftar.');
        }
        if ($item->jenis_penerima === 'dosen' && $jenis_penerima !== 'dosen') {
            return array('success' => false, 'message' => 'QR Code ini hanya untuk dosen terdaftar.');
        }
        if ($item->status !== 'aktif') {
            return array('success' => false, 'message' => 'Barang "' . $item->nama_barang . '" tidak aktif untuk pengambilan.');
        }
        if ($item->status_distribusi !== 'aktif') {
            $msg = 'Distribusi belum aktif atau sudah ditutup.';
            if ($item->status_distribusi === 'draft') {
                $msg = 'Distribusi belum dibuka. Status: Draft.';
            } elseif ($item->status_distribusi === 'selesai') {
                $msg = 'Distribusi sudah selesai.';
            } elseif ($item->status_distribusi === 'dibatalkan') {
                $msg = 'Distribusi dibatalkan.';
            }
            return array('success' => false, 'message' => $msg);
        }
        $today = date('Y-m-d');
        if (!empty($item->tanggal_mulai) && $today < $item->tanggal_mulai) {
            return array('success' => false, 'message' => 'Pengambilan belum dibuka. Mulai: ' . date('d/m/Y', strtotime($item->tanggal_mulai)));
        }
        if (!empty($item->tanggal_selesai) && $today > $item->tanggal_selesai) {
            return array('success' => false, 'message' => 'Periode pengambilan sudah berakhir (' . date('d/m/Y', strtotime($item->tanggal_selesai)) . ').');
        }
        if (!$this->CI->Tanda_terima_model->is_peserta($item->id_tanda_terima, $id_mahasiswa, $id_dosen)) {
            return array('success' => false, 'message' => 'Anda tidak terdaftar sebagai penerima distribusi ini.');
        }
        if ($this->CI->Tanda_terima_penerimaan_model->sudah_terima($item->id, $id_mahasiswa, $id_dosen)) {
            return array('success' => false, 'message' => 'Anda sudah menerima barang "' . $item->nama_barang . '" sebelumnya.');
        }

        $waktu = date('Y-m-d H:i:s');
        $this->CI->Tanda_terima_penerimaan_model->create(array(
            'id_tanda_terima_item' => (int) $item->id,
            'id_tanda_terima' => (int) $item->id_tanda_terima,
            'id_mahasiswa' => $id_mahasiswa ? (int) $id_mahasiswa : null,
            'id_dosen' => $id_dosen ? (int) $id_dosen : null,
            'waktu_terima' => $waktu,
            'ip_address' => $this->CI->input->ip_address(),
            'user_agent' => substr((string) $this->CI->input->user_agent(), 0, 500),
        ));

        return array(
            'success' => true,
            'message' => 'Tanda terima berhasil. Anda telah menerima: ' . $item->nama_barang . ' (' . date('d/m/Y H:i', strtotime($waktu)) . ').',
            'nama_barang' => $item->nama_barang,
            'nama_distribusi' => $item->nama_distribusi,
            'waktu_terima' => $waktu,
        );
    }
}
