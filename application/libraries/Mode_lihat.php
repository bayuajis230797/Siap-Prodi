<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Mode_lihat
 * Aturan HANYA-BACA saat pengguna sedang melihat tahun akademik selain yang resmi.
 * Murni aturan (tanpa database); dipanggil oleh MY_Controller.
 *
 *  - Semua permintaan POST ditolak, kecuali controller bebas (login, memilih tahun akademik,
 *    chat, notifikasi, profil).
 *  - Permintaan GET ditolak bila nama method-nya mengubah data (delete, hapus, activate, approve, ...).
 *
 * Batasan: penjagaan GET berbasis nama method adalah lapisan tambahan, bukan jaminan mutlak.
 * API mobile tidak memakai sesi web ini dan tidak tercakup.
 */
class Mode_lihat {

    /** Controller yang tetap boleh POST saat mode lihat (bukan data akademik). */
    public static $controller_bebas = array('auth', 'welcome', 'tahun_akademik_lihat', 'chat', 'notifikasi', 'profile');

    /** Awalan nama method GET yang dianggap mengubah data. */
    const POLA_GET_UBAH = '/^(delete|hapus|remove|activate|deactivate|aktifkan|nonaktifkan|approve|reject|tolak|setujui|acc|batal|cancel|pulihkan|restore|toggle|reset|buka|tutup|update_status|ubah_status|set_aktif|selesai|finalisasi)/i';

    /**
     * @return string|null 'post' | 'get' bila harus ditolak; null bila boleh.
     */
    public static function evaluasi($http_method, $controller, $method) {
        $controller = strtolower((string) $controller);
        if (in_array($controller, self::$controller_bebas, true)) {
            return null;
        }
        $http_method = strtolower((string) $http_method);
        if ($http_method === 'post' || $http_method === 'put' || $http_method === 'delete' || $http_method === 'patch') {
            return 'post';
        }
        if (preg_match(self::POLA_GET_UBAH, (string) $method)) {
            return 'get';
        }
        return null;
    }

    public static function pesan($ta_lihat) {
        return 'Mode lihat: Anda sedang melihat ' . $ta_lihat->tahun_akademik . ' ' . ucfirst($ta_lihat->semester)
            . ' (hanya baca). Kembali ke tahun akademik berjalan untuk menambah atau mengubah data.';
    }
}
