<?php
defined('BASEPATH') OR exit('No direct script access allowed');

if (!function_exists('get_mahasiswa_menu_mobile')) {
    /**
     * Struktur menu mahasiswa untuk aplikasi mobile (paritas get_mahasiswa_menu).
     *
     * @param int $id_mahasiswa
     * @return array
     */
    function get_mahasiswa_menu_mobile($id_mahasiswa) {
        $ci =& get_instance();
        $ci->load->model('Mahasiswa_pkl_model');
        $ci->load->model('Skripsi_model');
        $ci->load->model('Tahun_akademik_model');

        $id_mahasiswa = (int) $id_mahasiswa;
        $tahun_aktif = $ci->Tahun_akademik_model->get_aktif();
        $is_registered_pkl = false;
        $is_registered_skripsi = false;
        if ($id_mahasiswa > 0 && $tahun_aktif) {
            $is_registered_pkl = $ci->Mahasiswa_pkl_model->is_registered($id_mahasiswa, $tahun_aktif->id);
            $skripsi = $ci->Skripsi_model->get_by_mahasiswa($id_mahasiswa, $tahun_aktif->id);
            $is_registered_skripsi = ($skripsi !== null);
        }

        $items = array();

        $items[] = array(
            'id' => 'halaman_utama',
            'label' => 'Halaman Utama',
            'icon' => 'home',
            'path' => 'Halaman_utama',
            'web_url' => site_url('Halaman_utama'),
        );

        if ($is_registered_pkl) {
            $items[] = array(
                'id' => 'pkl',
                'label' => 'PKL',
                'icon' => 'briefcase',
                'path' => null,
                'children' => array(
                    array('id' => 'pkl_tempat_app', 'label' => 'Tempat PKL', 'icon' => 'business', 'path' => 'Pkl/input_tempat', 'web_url' => site_url('Pkl/input_tempat')),
                    array('id' => 'pkl_pengajuan_judul_app', 'label' => 'Status Pengajuan Judul', 'icon' => 'document', 'path' => 'Pkl/pengajuan', 'web_url' => site_url('Pkl/pengajuan')),
                    array('id' => 'jadwal_sidang_pkl', 'label' => 'Jadwal Sidang PKL', 'icon' => 'calendar', 'path' => 'Pkl_revisi_mahasiswa', 'web_url' => site_url('Pkl_revisi_mahasiswa')),
                    array('id' => 'pkl_logbook', 'label' => 'Logbook PKL', 'icon' => 'journal', 'path' => 'Pkl/logbook', 'web_url' => site_url('Pkl/logbook')),
                    array('id' => 'pkl_absensi_harian', 'label' => 'Absensi Harian', 'icon' => 'calendar', 'path' => 'Pkl_absensi', 'web_url' => site_url('Pkl_absensi')),
                    array('id' => 'pkl_bimbingan_app', 'label' => 'Bimbingan PKL', 'icon' => 'people', 'path' => 'Pkl/bimbingan', 'web_url' => site_url('Pkl/bimbingan')),
                    array('id' => 'pkl_nilai_app', 'label' => 'Nilai PKL', 'icon' => 'ribbon', 'path' => 'Pkl/bimbingan', 'web_url' => site_url('Pkl/bimbingan')),
                ),
            );
        }

        if ($is_registered_skripsi) {
            $items[] = array(
                'id' => 'skripsi',
                'label' => 'Skripsi',
                'icon' => 'school',
                'path' => null,
                'children' => array(
                    array('id' => 'skripsi_jadwal_sidang_app', 'label' => 'Jadwal Sidang', 'icon' => 'time', 'path' => 'Skripsi_catatan_revisi', 'web_url' => site_url('Skripsi_catatan_revisi')),
                    array('id' => 'skripsi_pengajuan_app', 'label' => 'Pengajuan Judul', 'icon' => 'document', 'path' => 'Skripsi_pengajuan', 'web_url' => site_url('Skripsi_pengajuan')),
                    array('id' => 'skripsi_pendaftaran_sidang_app', 'label' => 'Pendaftaran Sidang', 'icon' => 'calendar', 'path' => 'Pendaftaran_jadwal_skripsi', 'web_url' => site_url('Pendaftaran_jadwal_skripsi')),
                    array('id' => 'skripsi_pendaftaran_penguji_app', 'label' => 'Pendaftaran Penguji', 'icon' => 'person', 'path' => 'Pendaftaran_sidang_skripsi', 'web_url' => site_url('Pendaftaran_sidang_skripsi')),
                    array('id' => 'skripsi_klaim_app', 'label' => 'Klaim Nilai', 'icon' => 'ribbon', 'path' => 'Skripsi_klaim_nilai', 'web_url' => site_url('Skripsi_klaim_nilai')),
                    array('id' => 'skripsi_bimbingan_app', 'label' => 'Bimbingan Skripsi', 'icon' => 'people', 'path' => 'Bimbingan_skripsi_mahasiswa/bimbingan', 'web_url' => site_url('Bimbingan_skripsi_mahasiswa/bimbingan')),
                ),
            );
        }

        $jadwal_children = array(
            array('id' => 'jadwal_kuliah', 'label' => 'Jadwal Kuliah', 'icon' => 'book', 'path' => 'Jadwal/jadwal_kuliah', 'web_url' => site_url('Jadwal/jadwal_kuliah')),
            array('id' => 'jadwal_uts', 'label' => 'Jadwal UTS', 'icon' => 'document-text', 'path' => 'Jadwal/jadwal_uts', 'web_url' => site_url('Jadwal/jadwal_uts')),
            array('id' => 'jadwal_uas', 'label' => 'Jadwal UAS', 'icon' => 'documents', 'path' => 'Jadwal/jadwal_uas', 'web_url' => site_url('Jadwal/jadwal_uas')),
        );

        $items[] = array(
            'id' => 'dokumen',
            'label' => 'Dokumen & Surat',
            'icon' => 'folder',
            'path' => null,
            'children' => array(
                array('id' => 'manajemen_berkas', 'label' => 'Manajemen Berkas', 'icon' => 'archive', 'path' => 'Manajemen_berkas', 'web_url' => site_url('Manajemen_berkas')),
                array('id' => 'permintaan_surat', 'label' => 'Permintaan Surat', 'icon' => 'mail', 'path' => 'Permintaan_surat', 'web_url' => site_url('Permintaan_surat')),
                array('id' => 'tagihan_berkas', 'label' => 'Tagihan Berkas', 'icon' => 'card', 'path' => 'Tagihan_berkas', 'web_url' => site_url('Tagihan_berkas')),
            ),
        );

        $items[] = array(
            'id' => 'jadwal',
            'label' => 'Jadwal',
            'icon' => 'calendar',
            'path' => null,
            'children' => $jadwal_children,
        );

        $items[] = array(
            'id' => 'absensi_kegiatan',
            'label' => 'Absensi Kegiatan',
            'icon' => 'qr-code',
            'path' => 'Absensi/kegiatan_saya',
            'web_url' => site_url('Absensi/kegiatan_saya'),
        );

        $items[] = array(
            'id' => 'absensi_ujian',
            'label' => 'Absensi Ujian (UTS/UAS)',
            'icon' => 'scan',
            'path' => 'Absensi_ujian/jadwal_saya',
            'web_url' => site_url('Absensi_ujian/jadwal_saya'),
        );

        $items[] = array(
            'id' => 'notifikasi_dan_password',
            'label' => 'Notifikasi & pengaturan akun',
            'icon' => 'notifications',
            'path' => null,
            'children' => array(
                array(
                    'id' => 'notifikasi_app',
                    'label' => 'Notifikasi',
                    'icon' => 'notifications',
                    'path' => 'Notifikasi',
                    'web_url' => site_url('Dashboard'),
                ),
                array(
                    'id' => 'ubah_password',
                    'label' => 'Profil',
                    'icon' => 'person',
                    'path' => 'Profile',
                    'web_url' => site_url('Profile'),
                ),
            ),
        );

        mahasiswa_menu_attach_native_routes($items);

        return $items;
    }
}

if (!function_exists('mahasiswa_menu_attach_native_routes')) {
    /**
     * Tambah native_route untuk aplikasi Android/iOS (tanpa buka browser).
     *
     * @param array $items (by reference)
     */
    function mahasiswa_menu_attach_native_routes(array &$items) {
        $route_by_id = array(
            'halaman_utama' => 'halaman_utama',
            'pkl_tempat_app' => 'pkl_tempat',
            'pkl_pengajuan_judul_app' => 'pkl_pengajuan_judul',
            'pkl_logbook' => 'pkl_logbook',
            'pkl_absensi_harian' => 'pkl_absensi',
            'pkl_bimbingan_app' => 'pkl_bimbingan_nilai',
            'pkl_nilai_app' => 'pkl_nilai_ringkas',
            'skripsi_jadwal_sidang_app' => 'skripsi_jadwal_sidang',
            'skripsi_pengajuan_app' => 'skripsi_pengajuan_ringkas',
            'skripsi_pendaftaran_sidang_app' => 'skripsi_pendaftaran_sidang_ringkas',
            'skripsi_pendaftaran_penguji_app' => 'skripsi_pendaftaran_penguji_ringkas',
            'skripsi_klaim_app' => 'skripsi_klaim_ringkas',
            'skripsi_bimbingan_app' => 'skripsi_bimbingan_ringkas',
            'manajemen_berkas' => 'berkas',
            'permintaan_surat' => 'permintaan_surat',
            'tagihan_berkas' => 'tagihan',
            'jadwal_kuliah' => 'jadwal_kuliah',
            'jadwal_uts' => 'jadwal_uts',
            'jadwal_uas' => 'jadwal_uas',
            'jadwal_sidang_pkl' => 'jadwal_sidang_pkl',
            'absensi_kegiatan' => 'kegiatan',
            'absensi_ujian' => 'ujian_jadwal',
            'ubah_password' => 'ubah_password',
            'notifikasi_app' => 'notifikasi',
        );

        foreach ($items as &$row) {
            $id = isset($row['id']) ? $row['id'] : '';
            $row['native_route'] = isset($route_by_id[$id]) ? $route_by_id[$id] : null;
            unset($row['web_url']);
            if (!empty($row['children']) && is_array($row['children'])) {
                mahasiswa_menu_attach_native_routes($row['children']);
            }
        }
        unset($row);
    }
}
