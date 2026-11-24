<?php
defined('BASEPATH') OR exit('No direct script access allowed');

if (!function_exists('pkm_prodi_default')) {
    function pkm_prodi_default() {
        return 'S1 Sistem Informasi';
    }
}

if (!function_exists('pkm_status_verifikasi_badge')) {
    function pkm_status_verifikasi_badge($status) {
        $map = array(
            'pending' => 'warning',
            'disetujui' => 'success',
            'ditolak' => 'danger',
        );
        return $map[$status] ?? 'secondary';
    }
}

if (!function_exists('pkm_status_acc_badge')) {
    function pkm_status_acc_badge($status) {
        $map = array(
            'pending' => 'warning',
            'acc' => 'success',
            'revisi' => 'info',
            'ditolak' => 'danger',
        );
        return $map[$status] ?? 'secondary';
    }
}

if (!function_exists('pkm_topik_bimbingan_pilihan')) {
    function pkm_topik_bimbingan_pilihan() {
        return array(
            'PKM BAB 1 - 3',
            'PKM BAB 4 - 5',
            'Draf Final PKM',
        );
    }
}

if (!function_exists('valid_pkm_topik_bimbingan_label')) {
    function valid_pkm_topik_bimbingan_label($label) {
        return in_array(trim($label), pkm_topik_bimbingan_pilihan(), true);
    }
}

if (!function_exists('pkm_proposal_final_aktif')) {
    function pkm_proposal_final_aktif($pengajuan) {
        return $pengajuan && !empty($pengajuan->proposal_final_aktif);
    }
}

if (!function_exists('pkm_proposal_final_submit_allowed')) {
    function pkm_proposal_final_submit_allowed($pengajuan) {
        if (!$pengajuan) {
            return false;
        }
        if (empty($pengajuan->tanggal_selesai)) {
            return true;
        }
        return strtotime('today') <= strtotime($pengajuan->tanggal_selesai);
    }
}

if (!function_exists('pkm_proposal_final_deadline_iso')) {
    function pkm_proposal_final_deadline_iso($tanggal_selesai) {
        if (empty($tanggal_selesai)) {
            return '';
        }
        return date('Y-m-d', strtotime($tanggal_selesai)) . 'T23:59:59';
    }
}

if (!function_exists('pkm_bimbingan_aktif')) {
    function pkm_bimbingan_aktif($pengajuan) {
        if (!$pengajuan) {
            return false;
        }
        return $pengajuan->status_verifikasi_sekprodi === 'disetujui'
            && $pengajuan->status_acc_judul === 'acc';
    }
}

if (!function_exists('pkm_status_verifikasi_proposal_final_label')) {
    function pkm_status_verifikasi_proposal_final_label($status) {
        $map = array(
            'pending' => 'Menunggu Verifikasi Sekprodi',
            'acc' => 'Disetujui Sekprodi',
            'revisi' => 'Perlu Perbaikan',
        );
        return $map[$status] ?? ucfirst($status ?: 'belum dikirim');
    }
}

if (!function_exists('pkm_link_key')) {
    function pkm_link_key() {
        $CI =& get_instance();
        $key = $CI->config->item('encryption_key');
        if (empty($key)) {
            $key = 'siharkat_pkm_link_' . md5(BASEPATH);
        }
        return hash('sha256', $key, true);
    }
}

if (!function_exists('pkm_encrypt_link')) {
    /**
     * Enkripsi link Google Drive sebelum disimpan ke database.
     */
    function pkm_encrypt_link($url) {
        if ($url === null || $url === '') {
            return null;
        }
        $url = trim($url);
        if ($url === '') {
            return null;
        }
        if (strpos($url, 'enc:') === 0) {
            return $url;
        }
        $key = pkm_link_key();
        $iv = openssl_random_pseudo_bytes(16);
        $encrypted = openssl_encrypt($url, 'AES-256-CBC', $key, OPENSSL_RAW_DATA, $iv);
        if ($encrypted === false) {
            return $url;
        }
        return 'enc:' . base64_encode($iv . $encrypted);
    }
}

if (!function_exists('pkm_decrypt_link')) {
    /**
     * Dekripsi link dari database (mendukung data lama yang masih plain URL).
     */
    function pkm_decrypt_link($stored) {
        if ($stored === null || $stored === '') {
            return '';
        }
        if (strpos($stored, 'enc:') !== 0) {
            return $stored;
        }
        $raw = base64_decode(substr($stored, 4), true);
        if ($raw === false || strlen($raw) < 17) {
            return '';
        }
        $iv = substr($raw, 0, 16);
        $encrypted = substr($raw, 16);
        $decrypted = openssl_decrypt($encrypted, 'AES-256-CBC', pkm_link_key(), OPENSSL_RAW_DATA, $iv);
        return $decrypted !== false ? $decrypted : '';
    }
}

if (!function_exists('pkm_link_url')) {
    /**
     * URL aman untuk membuka link Drive (tanpa mengekspos ID file di HTML).
     */
    function pkm_link_url($tipe, $ref_id) {
        $CI =& get_instance();
        $CI->load->helper('id_encode');
        $encoded = encode_id((int) $ref_id);
        if ($encoded === '') {
            return '#';
        }
        return site_url('Pkm_link/buka/' . $tipe . '/' . $encoded);
    }
}

if (!function_exists('pkm_google_drive_link_html')) {
    /**
     * Tombol buka link — href hanya token aman, bukan URL Google Drive.
     *
     * @param string|null $stored Nilai terenkripsi di database
     * @param string $text Label tombol
     * @param string|null $secure_tipe proposal_awal|proposal_final|log|bukti_judul
     * @param int|null $ref_id ID record terkait
     */
    function pkm_google_drive_link_html($stored, $text = 'Buka Dokumen', $secure_tipe = null, $ref_id = null) {
        if ($stored === null || $stored === '') {
            return '<span class="text-muted">Link tidak tersedia</span>';
        }
        if ($secure_tipe && $ref_id) {
            $href = pkm_link_url($secure_tipe, $ref_id);
            return '<a href="' . htmlspecialchars($href) . '" target="_blank" class="btn btn-sm btn-info" rel="noopener noreferrer">'
                . '<i class="fas fa-lock"></i> ' . htmlspecialchars($text) . '</a>';
        }
        return '<span class="text-muted"><i class="fas fa-lock"></i> Link terlindungi</span>';
    }
}

if (!function_exists('pkm_anggota_tipe_label')) {
    function pkm_anggota_tipe_label($tipe) {
        return $tipe === 'external' ? 'Prodi Lain' : 'S1 Sistem Informasi';
    }
}

if (!function_exists('pkm_resolve_id')) {
    /** Decode ID dari URL terenkode atau integer biasa. */
    function pkm_resolve_id($id) {
        $CI =& get_instance();
        $CI->load->helper('id_encode');
        $decoded = decode_id($id);
        return $decoded !== false ? (int) $decoded : (int) $id;
    }
}

if (!function_exists('pkm_encoded_id')) {
    function pkm_encoded_id($id) {
        $CI =& get_instance();
        $CI->load->helper('id_encode');
        return encode_id((int) $id);
    }
}
