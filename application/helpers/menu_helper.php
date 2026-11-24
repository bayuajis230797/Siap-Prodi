<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Menu Helper
 * Helper untuk generate sidebar menu berdasarkan role
 */

if (!function_exists('generate_sidebar_menu')) {
    /**
     * Generate sidebar menu berdasarkan role
     * @param string $role Role user (admin, sekprodi, kaprodi, dosen, mahasiswa, tendik)
     * @return string HTML menu sidebar
     */
    function generate_sidebar_menu($role) {
        $menu = '';
        
        switch ($role) {
            case 'admin':
                $menu = get_admin_menu();
                break;
            case 'sekprodi':
                $menu = get_sekprodi_menu();
                break;
            case 'kaprodi':
                $menu = get_kaprodi_menu();
                break;
            case 'dosen':
                $menu = get_dosen_menu();
                break;
            case 'mahasiswa':
                $menu = get_mahasiswa_menu();
                break;
            case 'tendik':
                $menu = get_tendik_menu();
                break;
            default:
                $menu = '';
        }
        
        return $menu;
    }
}

if (!function_exists('get_admin_menu')) {
    function get_admin_menu() {
        $current_url = current_url();
        $menu = '
        <nav class="mt-2">
            <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu" data-accordion="false">
                <li class="nav-item">
                    <a href="' . site_url('Halaman_utama') . '" class="nav-link ' . (strpos($current_url, 'Halaman_utama') !== false ? 'active' : '') . '">
                        <i class="nav-icon fas fa-home"></i>
                        <p>Halaman Utama</p>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="' . site_url('Aktivitas_user') . '" class="nav-link ' . (strpos($current_url, 'Aktivitas_user') !== false ? 'active' : '') . '">
                        <i class="nav-icon fas fa-history"></i>
                        <p>Aktivitas</p>
                    </a>
                </li>
                ' . chat_sidebar_menu_item() . '
                <li class="nav-item has-treeview">
                    <a href="#" class="nav-link ' . (strpos($current_url, 'Mahasiswa') !== false || strpos($current_url, 'Dosen') !== false || strpos($current_url, 'Tendik') !== false || strpos($current_url, 'Mata_kuliah') !== false || strpos($current_url, 'Kurikulum') !== false || strpos($current_url, 'Tahun_akademik') !== false || strpos($current_url, 'Ruang_kelas') !== false ? 'active' : '') . '">
                        <i class="nav-icon fas fa-database"></i>
                        <p>Master Data <i class="fas fa-angle-left right"></i></p>
                    </a>
                    <ul class="nav nav-treeview">
                        <li class="nav-item"><a href="' . site_url('Mahasiswa') . '" class="nav-link ' . (strpos($current_url, 'Mahasiswa') !== false ? 'active' : '') . '"><i class="far fa-circle nav-icon"></i><p>Mahasiswa</p></a></li>
                        <li class="nav-item"><a href="' . site_url('Dosen') . '" class="nav-link ' . (strpos($current_url, 'Dosen') !== false ? 'active' : '') . '"><i class="far fa-circle nav-icon"></i><p>Dosen</p></a></li>
                        <li class="nav-item"><a href="' . site_url('Tendik') . '" class="nav-link ' . (strpos($current_url, 'Tendik') !== false ? 'active' : '') . '"><i class="far fa-circle nav-icon"></i><p>Tendik</p></a></li>
                        <li class="nav-item"><a href="' . site_url('Mata_kuliah') . '" class="nav-link ' . (strpos($current_url, 'Mata_kuliah') !== false ? 'active' : '') . '"><i class="far fa-circle nav-icon"></i><p>Mata Kuliah</p></a></li>
                        <li class="nav-item"><a href="' . site_url('Kurikulum') . '" class="nav-link ' . (strpos($current_url, 'Kurikulum') !== false ? 'active' : '') . '"><i class="far fa-circle nav-icon"></i><p>Kurikulum</p></a></li>
                        <li class="nav-item"><a href="' . site_url('Tahun_akademik') . '" class="nav-link ' . (strpos($current_url, 'Tahun_akademik') !== false ? 'active' : '') . '"><i class="far fa-circle nav-icon"></i><p>Tahun Akademik</p></a></li>
                        <li class="nav-item"><a href="' . site_url('Ruang_kelas') . '" class="nav-link ' . (strpos($current_url, 'Ruang_kelas') !== false ? 'active' : '') . '"><i class="far fa-circle nav-icon"></i><p>Ruang Kelas</p></a></li>
                    </ul>
                </li>
                <li class="nav-item has-treeview">
                    <a href="#" class="nav-link ' . (strpos($current_url, 'Pkl_aspek_penilaian') !== false || strpos($current_url, 'Pkl_item_penilaian') !== false || strpos($current_url, 'Pkl_berkas_sidang_setting') !== false || strpos($current_url, 'Pkl_berkas_klaim_setting') !== false ? 'active' : '') . '">
                        <i class="nav-icon fas fa-briefcase"></i>
                        <p>PKL <i class="fas fa-angle-left right"></i></p>
                    </a>
                    <ul class="nav nav-treeview">
                        <li class="nav-item"><a href="' . site_url('Pkl_aspek_penilaian') . '" class="nav-link ' . (strpos($current_url, 'Pkl_aspek_penilaian') !== false ? 'active' : '') . '"><i class="far fa-circle nav-icon"></i><p>Aspek Penilaian PKL</p></a></li>
                        <li class="nav-item"><a href="' . site_url('Pkl_item_penilaian') . '" class="nav-link ' . (strpos($current_url, 'Pkl_item_penilaian') !== false ? 'active' : '') . '"><i class="far fa-circle nav-icon"></i><p>Item Penilaian PKL</p></a></li>
                        <li class="nav-item"><a href="' . site_url('Pkl_berkas_sidang_setting') . '" class="nav-link ' . (strpos($current_url, 'Pkl_berkas_sidang_setting') !== false ? 'active' : '') . '"><i class="far fa-circle nav-icon"></i><p>Berkas Sidang PKL</p></a></li>
                        <li class="nav-item"><a href="' . site_url('Pkl_berkas_klaim_setting') . '" class="nav-link ' . (strpos($current_url, 'Pkl_berkas_klaim_setting') !== false ? 'active' : '') . '"><i class="far fa-circle nav-icon"></i><p>Berkas Klaim Nilai PKL</p></a></li>
                    </ul>
                </li>
                <li class="nav-item has-treeview ' . (strpos($current_url, 'Proposal_aspek_penilaian') !== false || strpos($current_url, 'Proposal_item_penilaian') !== false || strpos($current_url, 'Proposal_berkas_sidang_setting') !== false || strpos($current_url, 'Proposal_berkas_klaim_setting') !== false || strpos($current_url, 'Proposal_pembimbing_setting') !== false ? 'menu-open' : '') . '">
                    <a href="#" class="nav-link ' . (strpos($current_url, 'Proposal_aspek_penilaian') !== false || strpos($current_url, 'Proposal_item_penilaian') !== false || strpos($current_url, 'Proposal_berkas_sidang_setting') !== false || strpos($current_url, 'Proposal_berkas_klaim_setting') !== false || strpos($current_url, 'Proposal_pembimbing_setting') !== false ? 'active' : '') . '">
                        <i class="nav-icon fas fa-file-signature"></i>
                        <p>Proposal TA <i class="fas fa-angle-left right"></i></p>
                    </a>
                    <ul class="nav nav-treeview">
                        <li class="nav-item"><a href="' . site_url('Proposal_aspek_penilaian') . '" class="nav-link ' . (strpos($current_url, 'Proposal_aspek_penilaian') !== false ? 'active' : '') . '"><i class="far fa-circle nav-icon"></i><p>Aspek Penilaian</p></a></li>
                        <li class="nav-item"><a href="' . site_url('Proposal_item_penilaian') . '" class="nav-link ' . (strpos($current_url, 'Proposal_item_penilaian') !== false ? 'active' : '') . '"><i class="far fa-circle nav-icon"></i><p>Item Penilaian</p></a></li>
                        <li class="nav-item"><a href="' . site_url('Proposal_berkas_sidang_setting') . '" class="nav-link ' . (strpos($current_url, 'Proposal_berkas_sidang_setting') !== false ? 'active' : '') . '"><i class="far fa-circle nav-icon"></i><p>Berkas Sidang</p></a></li>
                        <li class="nav-item"><a href="' . site_url('Proposal_berkas_klaim_setting') . '" class="nav-link ' . (strpos($current_url, 'Proposal_berkas_klaim_setting') !== false ? 'active' : '') . '"><i class="far fa-circle nav-icon"></i><p>Berkas Klaim</p></a></li>
                        <li class="nav-item"><a href="' . site_url('Proposal_pembimbing_setting') . '" class="nav-link ' . (strpos($current_url, 'Proposal_pembimbing_setting') !== false ? 'active' : '') . '"><i class="far fa-circle nav-icon"></i><p>Pembimbing</p></a></li>
                    </ul>
                </li>
                <li class="nav-item has-treeview">
                    <a href="#" class="nav-link ' . (strpos($current_url, 'Skripsi_aspek_penilaian') !== false || strpos($current_url, 'Skripsi_item_penilaian') !== false || strpos($current_url, 'Skripsi_berkas_sidang_setting') !== false || strpos($current_url, 'Skripsi_berkas_klaim_setting') !== false ? 'active' : '') . '">
                        <i class="nav-icon fas fa-graduation-cap"></i>
                        <p>Skripsi <i class="fas fa-angle-left right"></i></p>
                    </a>
                    <ul class="nav nav-treeview">
                        <li class="nav-item"><a href="' . site_url('Skripsi_aspek_penilaian') . '" class="nav-link ' . (strpos($current_url, 'Skripsi_aspek_penilaian') !== false ? 'active' : '') . '"><i class="far fa-circle nav-icon"></i><p>Aspek Penilaian Skripsi</p></a></li>
                        <li class="nav-item"><a href="' . site_url('Skripsi_item_penilaian') . '" class="nav-link ' . (strpos($current_url, 'Skripsi_item_penilaian') !== false ? 'active' : '') . '"><i class="far fa-circle nav-icon"></i><p>Item Penilaian Skripsi</p></a></li>
                        <li class="nav-item"><a href="' . site_url('Skripsi_berkas_sidang_setting') . '" class="nav-link ' . (strpos($current_url, 'Skripsi_berkas_sidang_setting') !== false ? 'active' : '') . '"><i class="far fa-circle nav-icon"></i><p>Setting Berkas Sidang</p></a></li>
                        <li class="nav-item"><a href="' . site_url('Skripsi_berkas_klaim_setting') . '" class="nav-link ' . (strpos($current_url, 'Skripsi_berkas_klaim_setting') !== false ? 'active' : '') . '"><i class="far fa-circle nav-icon"></i><p>Setting Berkas Klaim</p></a></li>
                    </ul>
                </li>
                <li class="nav-item has-treeview">
                    <a href="#" class="nav-link ' . (strpos($current_url, 'Management_user') !== false || strpos($current_url, 'App_settings') !== false || strpos($current_url, 'Google_oauth_settings') !== false || strpos($current_url, 'Chat_settings') !== false || strpos($current_url, 'Honor_setup') !== false || strpos($current_url, 'Setting_grade') !== false || strpos($current_url, 'Setting_bobot') !== false ? 'active' : '') . '">
                        <i class="nav-icon fas fa-cog"></i>
                        <p>System <i class="fas fa-angle-left right"></i></p>
                    </a>
                    <ul class="nav nav-treeview">
                        <li class="nav-item"><a href="' . site_url('Management_user') . '" class="nav-link ' . (strpos($current_url, 'Management_user') !== false ? 'active' : '') . '"><i class="far fa-circle nav-icon"></i><p>Management User</p></a></li>
                        <li class="nav-item"><a href="' . site_url('Honor_setup') . '" class="nav-link ' . (strpos($current_url, 'Honor_setup') !== false ? 'active' : '') . '"><i class="far fa-circle nav-icon"></i><p>Pengaturan Honor</p></a></li>
                        <li class="nav-item has-treeview">
                            <a href="#" class="nav-link ' . (strpos($current_url, 'Setting_grade_pkl') !== false || strpos($current_url, 'Setting_grade_skripsi') !== false || strpos($current_url, 'Setting_grade_proposal') !== false ? 'active' : '') . '">
                                <i class="far fa-circle nav-icon"></i>
                                <p>Setting Grade <i class="fas fa-angle-left right"></i></p>
                            </a>
                            <ul class="nav nav-treeview">
                                <li class="nav-item"><a href="' . site_url('Setting_grade_pkl') . '" class="nav-link ' . (strpos($current_url, 'Setting_grade_pkl') !== false ? 'active' : '') . '"><i class="far fa-dot-circle nav-icon"></i><p>PKL</p></a></li>
                                <li class="nav-item"><a href="' . site_url('Setting_grade_skripsi') . '" class="nav-link ' . (strpos($current_url, 'Setting_grade_skripsi') !== false ? 'active' : '') . '"><i class="far fa-dot-circle nav-icon"></i><p>Skripsi</p></a></li>
                                <li class="nav-item"><a href="' . site_url('Setting_grade_proposal') . '" class="nav-link ' . (strpos($current_url, 'Setting_grade_proposal') !== false ? 'active' : '') . '"><i class="far fa-dot-circle nav-icon"></i><p>Proposal TA</p></a></li>
                            </ul>
                        </li>
                        <li class="nav-item has-treeview">
                            <a href="#" class="nav-link ' . (strpos($current_url, 'Setting_bobot_pkl') !== false || strpos($current_url, 'Setting_bobot_skripsi') !== false || strpos($current_url, 'Setting_bobot_proposal') !== false ? 'active' : '') . '">
                                <i class="far fa-circle nav-icon"></i>
                                <p>Setting Bobot <i class="fas fa-angle-left right"></i></p>
                            </a>
                            <ul class="nav nav-treeview">
                                <li class="nav-item"><a href="' . site_url('Setting_bobot_pkl') . '" class="nav-link ' . (strpos($current_url, 'Setting_bobot_pkl') !== false ? 'active' : '') . '"><i class="far fa-dot-circle nav-icon"></i><p>PKL</p></a></li>
                                <li class="nav-item"><a href="' . site_url('Setting_bobot_skripsi') . '" class="nav-link ' . (strpos($current_url, 'Setting_bobot_skripsi') !== false ? 'active' : '') . '"><i class="far fa-dot-circle nav-icon"></i><p>Skripsi</p></a></li>
                                <li class="nav-item"><a href="' . site_url('Setting_bobot_proposal') . '" class="nav-link ' . (strpos($current_url, 'Setting_bobot_proposal') !== false ? 'active' : '') . '"><i class="far fa-dot-circle nav-icon"></i><p>Proposal TA</p></a></li>
                            </ul>
                        </li>
                        <li class="nav-item"><a href="' . site_url('App_settings') . '" class="nav-link ' . (strpos($current_url, 'App_settings') !== false ? 'active' : '') . '"><i class="far fa-circle nav-icon"></i><p>Pengaturan Aplikasi</p></a></li>
                        <li class="nav-item"><a href="' . site_url('Google_oauth_settings') . '" class="nav-link ' . (strpos($current_url, 'Google_oauth_settings') !== false ? 'active' : '') . '"><i class="far fa-circle nav-icon"></i><p>Google Drive OAuth</p></a></li>
                        <li class="nav-item"><a href="' . site_url('Chat_settings') . '" class="nav-link ' . (strpos($current_url, 'Chat_settings') !== false ? 'active' : '') . '"><i class="far fa-circle nav-icon"></i><p>Pengaturan Chat</p></a></li>
                    </ul>
                </li>
                <li class="nav-item"><a href="' . site_url('Profile') . '" class="nav-link ' . (strpos($current_url, 'Profile') !== false ? 'active' : '') . '"><i class="nav-icon fas fa-user-cog"></i><p>Profil</p></a></li>
                <li class="nav-item"><a href="' . site_url('Auth/logout') . '" class="nav-link"><i class="nav-icon fas fa-sign-out-alt"></i><p>Logout</p></a></li>
            </ul>
        </nav>';
        return $menu;
    }
}

if (!function_exists('get_kaprodi_menu')) {
    /**
     * Menu untuk role Kaprodi (Kepala Prodi) — monitoring akademik (baca saja)
     */
    function get_kaprodi_menu() {
        $current_url = current_url();
        $is_akademik = strpos($current_url, 'Kurikulum') !== false || strpos($current_url, 'Mahasiswa_sekprodi') !== false || strpos($current_url, 'Dosen') !== false;
        $is_pkl = strpos($current_url, 'Pkl_aktivitas_penilaian') !== false || strpos($current_url, 'Pkl_tempat_sekprodi') !== false || strpos($current_url, 'Pkl_monitoring_sekprodi') !== false || strpos($current_url, 'Pkl_monitor_peta') !== false;
        $is_skripsi = strpos($current_url, 'Skripsi_aktivitas_penilaian') !== false || strpos($current_url, 'Registrasi_skripsi') !== false;
        $is_jadwal_block = strpos($current_url, 'Jadwal_kuliah') !== false || strpos($current_url, 'Jadwal_uts') !== false || strpos($current_url, 'Jadwal_uas') !== false || strpos($current_url, 'Jadwal_skripsi_sekprodi') !== false || strpos($current_url, 'Jadwal_pkl_sekprodi') !== false;
        $is_honor = strpos($current_url, 'Honor_uts') !== false || strpos($current_url, 'Honor_uas') !== false || strpos($current_url, 'Honor_pkl') !== false || strpos($current_url, 'Honor_skripsi') !== false || strpos($current_url, 'Honor_proposal') !== false;
        $is_arsip = strpos($current_url, 'Arsip') !== false;
        $menu = '
        <nav class="mt-2">
            <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu" data-accordion="false">
                <li class="nav-item">
                    <a href="' . site_url('Halaman_utama') . '" class="nav-link ' . (strpos($current_url, 'Halaman_utama') !== false ? 'active' : '') . '">
                        <i class="nav-icon fas fa-home"></i>
                        <p>Halaman Utama</p>
                    </a>
                </li>
                ' . chat_sidebar_menu_item() . '
                <li class="nav-item has-treeview ' . ($is_jadwal_block ? 'menu-open' : '') . '">
                    <a href="#" class="nav-link ' . ($is_jadwal_block ? 'active' : '') . '"><i class="nav-icon fas fa-calendar-alt"></i><p>Jadwal <i class="fas fa-angle-left right"></i></p></a>
                    <ul class="nav nav-treeview">
                        <li class="nav-item"><a href="' . site_url('Jadwal_kuliah') . '" class="nav-link ' . (strpos($current_url, 'Jadwal_kuliah') !== false ? 'active' : '') . '"><i class="far fa-circle nav-icon"></i><p>Jadwal Kuliah</p></a></li>
                        <li class="nav-item"><a href="' . site_url('Jadwal_uts') . '" class="nav-link ' . (strpos($current_url, 'Jadwal_uts') !== false ? 'active' : '') . '"><i class="far fa-circle nav-icon"></i><p>Jadwal UTS</p></a></li>
                        <li class="nav-item"><a href="' . site_url('Jadwal_uas') . '" class="nav-link ' . (strpos($current_url, 'Jadwal_uas') !== false ? 'active' : '') . '"><i class="far fa-circle nav-icon"></i><p>Jadwal UAS</p></a></li>
                        <li class="nav-item"><a href="' . site_url('Jadwal_skripsi_sekprodi') . '" class="nav-link ' . (strpos($current_url, 'Jadwal_skripsi_sekprodi') !== false ? 'active' : '') . '"><i class="far fa-circle nav-icon"></i><p>Jadwal Sidang Skripsi</p></a></li>
                        <li class="nav-item"><a href="' . site_url('Jadwal_pkl_sekprodi') . '" class="nav-link ' . (strpos($current_url, 'Jadwal_pkl_sekprodi') !== false ? 'active' : '') . '"><i class="far fa-circle nav-icon"></i><p>Jadwal Sidang PKL</p></a></li>
                    </ul>
                </li>
                <li class="nav-item has-treeview ' . ($is_akademik ? 'menu-open' : '') . '">
                    <a href="#" class="nav-link ' . ($is_akademik ? 'active' : '') . '"><i class="nav-icon fas fa-book"></i><p>Akademik <i class="fas fa-angle-left right"></i></p></a>
                    <ul class="nav nav-treeview">
                        <li class="nav-item"><a href="' . site_url('Kurikulum') . '" class="nav-link ' . (strpos($current_url, 'Kurikulum') !== false ? 'active' : '') . '"><i class="far fa-circle nav-icon"></i><p>Struktur MK / Kurikulum</p></a></li>
                        <li class="nav-item"><a href="' . site_url('Mahasiswa_sekprodi') . '" class="nav-link ' . (strpos($current_url, 'Mahasiswa_sekprodi') !== false ? 'active' : '') . '"><i class="far fa-circle nav-icon"></i><p>Daftar Mahasiswa</p></a></li>
                        <li class="nav-item"><a href="' . site_url('Dosen') . '" class="nav-link ' . (strpos($current_url, 'Dosen') !== false ? 'active' : '') . '"><i class="far fa-circle nav-icon"></i><p>Daftar Dosen</p></a></li>
                    </ul>
                </li>
                <li class="nav-item has-treeview ' . ($is_pkl ? 'menu-open' : '') . '">
                    <a href="#" class="nav-link ' . ($is_pkl ? 'active' : '') . '"><i class="nav-icon fas fa-briefcase"></i><p>PKL <i class="fas fa-angle-left right"></i></p></a>
                    <ul class="nav nav-treeview">
                        <li class="nav-item"><a href="' . site_url('Pkl_tempat_sekprodi') . '" class="nav-link ' . (strpos($current_url, 'Pkl_tempat_sekprodi') !== false ? 'active' : '') . '"><i class="far fa-circle nav-icon"></i><p>Daftar Mahasiswa PKL</p></a></li>
                        <li class="nav-item"><a href="' . site_url('Pkl_monitoring_sekprodi') . '" class="nav-link ' . (strpos($current_url, 'Pkl_monitoring_sekprodi') !== false ? 'active' : '') . '"><i class="far fa-circle nav-icon"></i><p>Logbook & Absensi</p></a></li>
                        <li class="nav-item"><a href="' . site_url('Pkl_monitor_peta') . '" class="nav-link ' . (strpos($current_url, 'Pkl_monitor_peta') !== false ? 'active' : '') . '"><i class="far fa-circle nav-icon"></i><p>Monitor PKL</p></a></li>
                        <li class="nav-item"><a href="' . site_url('Pkl_aktivitas_penilaian') . '" class="nav-link ' . (strpos($current_url, 'Pkl_aktivitas_penilaian') !== false ? 'active' : '') . '"><i class="far fa-circle nav-icon"></i><p>Aktivitas Penilaian PKL</p></a></li>
                    </ul>
                </li>
                ' . proposal_menu_group_html('kaprodi', $current_url) . '
                <li class="nav-item has-treeview ' . ($is_skripsi ? 'menu-open' : '') . '">
                    <a href="#" class="nav-link ' . ($is_skripsi ? 'active' : '') . '"><i class="nav-icon fas fa-graduation-cap"></i><p>Skripsi <i class="fas fa-angle-left right"></i></p></a>
                    <ul class="nav nav-treeview">
                        <li class="nav-item"><a href="' . site_url('Registrasi_skripsi') . '" class="nav-link ' . (strpos($current_url, 'Registrasi_skripsi') !== false ? 'active' : '') . '"><i class="far fa-circle nav-icon"></i><p>Daftar Mahasiswa Skripsi</p></a></li>
                        <li class="nav-item"><a href="' . site_url('Skripsi_aktivitas_penilaian') . '" class="nav-link ' . (strpos($current_url, 'Skripsi_aktivitas_penilaian') !== false ? 'active' : '') . '"><i class="far fa-circle nav-icon"></i><p>Aktivitas Penilaian Skripsi</p></a></li>
                    </ul>
                </li>
                <li class="nav-item has-treeview ' . ($is_honor ? 'menu-open' : '') . '">
                    <a href="#" class="nav-link ' . ($is_honor ? 'active' : '') . '"><i class="nav-icon fas fa-money-bill-wave"></i><p>Honor <i class="fas fa-angle-left right"></i></p></a>
                    <ul class="nav nav-treeview">
                        <li class="nav-item"><a href="' . site_url('Honor_uts') . '" class="nav-link ' . (strpos($current_url, 'Honor_uts') !== false ? 'active' : '') . '"><i class="far fa-circle nav-icon"></i><p>Honor UTS</p></a></li>
                        <li class="nav-item"><a href="' . site_url('Honor_uas') . '" class="nav-link ' . (strpos($current_url, 'Honor_uas') !== false ? 'active' : '') . '"><i class="far fa-circle nav-icon"></i><p>Honor UAS</p></a></li>
                        <li class="nav-item"><a href="' . site_url('Honor_pkl') . '" class="nav-link ' . (strpos($current_url, 'Honor_pkl') !== false ? 'active' : '') . '"><i class="far fa-circle nav-icon"></i><p>Honor PKL</p></a></li>
                        <li class="nav-item"><a href="' . site_url('Honor_skripsi') . '" class="nav-link ' . (strpos($current_url, 'Honor_skripsi') !== false ? 'active' : '') . '"><i class="far fa-circle nav-icon"></i><p>Honor Skripsi</p></a></li>
                        <li class="nav-item"><a href="' . site_url('Honor_proposal') . '" class="nav-link ' . (strpos($current_url, 'Honor_proposal') !== false ? 'active' : '') . '"><i class="far fa-circle nav-icon"></i><p>Honor Proposal TA</p></a></li>
                    </ul>
                </li>
                <li class="nav-item">
                    <a href="' . site_url('Arsip') . '" class="nav-link ' . ($is_arsip ? 'active' : '') . '">
                        <i class="nav-icon fas fa-archive"></i>
                        <p>Arsip</p>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="' . site_url('Profile') . '" class="nav-link ' . (strpos($current_url, 'Profile') !== false ? 'active' : '') . '">
                        <i class="nav-icon fas fa-user-cog"></i>
                        <p>Profil</p>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="' . site_url('Auth/logout') . '" class="nav-link">
                        <i class="nav-icon fas fa-sign-out-alt"></i>
                        <p>Logout</p>
                    </a>
                </li>
            </ul>
        </nav>';
        return $menu;
    }
}

if (!function_exists('get_sekprodi_menu')) {
    function get_sekprodi_menu() {
        $current_url = current_url();
        $b_pkl = sekprodi_badge_sum_html(array('pkl_judul', 'pkl_perubahan', 'pkl_sidang', 'pkl_klaim'));
        $b_skripsi = sekprodi_badge_sum_html(array('skripsi_judul', 'skripsi_perubahan', 'skripsi_sidang', 'skripsi_klaim'));
        $b_dokumen = sekprodi_badge_html('permintaan_surat');
        $b_pkm = sekprodi_badge_sum_html(array('pkm_verifikasi', 'pkm_proposal_final'));
        $b_skpi = sekprodi_badge_html('skpi_verifikasi');
        $menu = '
        <nav class="mt-2">
            <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu" data-accordion="false">
                <li class="nav-item"><a href="' . site_url('Halaman_utama') . '" class="nav-link ' . (strpos($current_url, 'Halaman_utama') !== false ? 'active' : '') . '"><i class="nav-icon fas fa-home"></i><p>Halaman Utama</p></a></li>
                ' . chat_sidebar_menu_item() . '
                <li class="nav-item"><a href="' . site_url('Mahasiswa_sekprodi') . '" class="nav-link ' . (strpos($current_url, 'Mahasiswa_sekprodi') !== false ? 'active' : '') . '"><i class="nav-icon fas fa-user-graduate"></i><p>Daftar Mahasiswa</p></a></li>
                <li class="nav-item has-treeview ' . (strpos($current_url, 'Pkl') !== false || strpos($current_url, 'Monitoring_penguji_sidang_pkl') !== false ? 'menu-open' : '') . '">
                    <a href="#" class="nav-link ' . (strpos($current_url, 'Pkl') !== false || strpos($current_url, 'Monitoring_penguji_sidang_pkl') !== false ? 'active' : '') . '"><i class="nav-icon fas fa-briefcase"></i><p>PKL' . $b_pkl . ' <i class="fas fa-angle-left right"></i></p></a>
                    <ul class="nav nav-treeview">
                        <li class="nav-item"><a href="' . site_url('Pkl_register') . '" class="nav-link ' . (strpos($current_url, 'Pkl_register') !== false ? 'active' : '') . '"><i class="far fa-circle nav-icon"></i><p>Registrasi Mahasiswa</p></a></li>
                        <li class="nav-item"><a href="' . site_url('Pkl_tempat_sekprodi') . '" class="nav-link ' . (strpos($current_url, 'Pkl_tempat_sekprodi') !== false ? 'active' : '') . '"><i class="far fa-circle nav-icon"></i><p>Daftar Mahasiswa PKL' . sekprodi_badge_html('pkl_judul') . '</p></a></li>
                        <li class="nav-item"><a href="' . site_url('Pkl_setting_radius') . '" class="nav-link ' . (strpos($current_url, 'Pkl_setting_radius') !== false ? 'active' : '') . '"><i class="far fa-circle nav-icon"></i><p>Setting Radius Absensi</p></a></li>
                        <li class="nav-item"><a href="' . site_url('Pkl_monitoring_sekprodi') . '" class="nav-link ' . (strpos($current_url, 'Pkl_monitoring_sekprodi') !== false ? 'active' : '') . '"><i class="far fa-circle nav-icon"></i><p>Logbook & Absensi</p></a></li>
                        <li class="nav-item"><a href="' . site_url('Pkl_monitor_peta') . '" class="nav-link ' . (strpos($current_url, 'Pkl_monitor_peta') !== false ? 'active' : '') . '"><i class="far fa-circle nav-icon"></i><p>Monitor PKL</p></a></li>
                        <li class="nav-item"><a href="' . site_url('Pkl_perubahan_judul_sekprodi') . '" class="nav-link ' . (strpos($current_url, 'Pkl_perubahan_judul_sekprodi') !== false ? 'active' : '') . '"><i class="far fa-circle nav-icon"></i><p>Perubahan Judul' . sekprodi_badge_html('pkl_perubahan') . '</p></a></li>
                        <li class="nav-item"><a href="' . site_url('Pkl_reset_pengajuan_sekprodi') . '" class="nav-link ' . (strpos($current_url, 'Pkl_reset_pengajuan_sekprodi') !== false ? 'active' : '') . '"><i class="far fa-circle nav-icon"></i><p>Reset Pengajuan Judul</p></a></li>
                        <li class="nav-item"><a href="' . site_url('Pkl_penilaian_sekprodi') . '" class="nav-link ' . (strpos($current_url, 'Pkl_penilaian_sekprodi') !== false ? 'active' : '') . '"><i class="far fa-circle nav-icon"></i><p>Penilaian PKL</p></a></li>
                        <li class="nav-item"><a href="' . site_url('Pkl_verifikasi_berkas_sidang') . '" class="nav-link ' . (strpos($current_url, 'Pkl_verifikasi_berkas_sidang') !== false ? 'active' : '') . '"><i class="far fa-circle nav-icon"></i><p>Verifikasi Berkas Sidang' . sekprodi_badge_html('pkl_sidang') . '</p></a></li>
                        <li class="nav-item"><a href="' . site_url('Jadwal_pkl_sekprodi') . '" class="nav-link ' . (strpos($current_url, 'Jadwal_pkl_sekprodi') !== false ? 'active' : '') . '"><i class="far fa-circle nav-icon"></i><p>Jadwal Sidang PKL</p></a></li>
                        <li class="nav-item"><a href="' . site_url('Monitoring_penguji_sidang_pkl') . '" class="nav-link ' . (strpos($current_url, 'Monitoring_penguji_sidang_pkl') !== false ? 'active' : '') . '"><i class="far fa-circle nav-icon"></i><p>Monitoring Penguji</p></a></li>
                        <li class="nav-item"><a href="' . site_url('Pkl_verifikasi_klaim') . '" class="nav-link ' . (strpos($current_url, 'Pkl_verifikasi_klaim') !== false ? 'active' : '') . '"><i class="far fa-circle nav-icon"></i><p>Verifikasi Klaim Nilai' . sekprodi_badge_html('pkl_klaim') . '</p></a></li>
                        <li class="nav-item"><a href="' . site_url('Pkl_aktivitas_penilaian') . '" class="nav-link ' . (strpos($current_url, 'Pkl_aktivitas_penilaian') !== false ? 'active' : '') . '"><i class="far fa-circle nav-icon"></i><p>Aktivitas Penilaian</p></a></li>
                    </ul>
                </li>
                ' . proposal_menu_group_html('sekprodi', $current_url) . '
                <li class="nav-item has-treeview ' . (strpos($current_url, 'Registrasi_skripsi') !== false || strpos($current_url, 'Skripsi_plot_pembimbing') !== false || strpos($current_url, 'Skripsi_perubahan_judul_sekprodi') !== false || strpos($current_url, 'Skripsi_verifikasi_berkas_sidang') !== false || strpos($current_url, 'Jadwal_skripsi_sekprodi') !== false || strpos($current_url, 'Monitoring_penguji_sidang_skripsi') !== false || strpos($current_url, 'Skripsi_verifikasi_klaim') !== false || strpos($current_url, 'Skripsi_aktivitas_penilaian') !== false || strpos($current_url, 'Countdown_sidang_skripsi') !== false ? 'menu-open' : '') . '">
                    <a href="#" class="nav-link ' . (strpos($current_url, 'Registrasi_skripsi') !== false || strpos($current_url, 'Skripsi_plot_pembimbing') !== false || strpos($current_url, 'Skripsi_perubahan_judul_sekprodi') !== false || strpos($current_url, 'Skripsi_verifikasi_berkas_sidang') !== false || strpos($current_url, 'Jadwal_skripsi_sekprodi') !== false || strpos($current_url, 'Monitoring_penguji_sidang_skripsi') !== false || strpos($current_url, 'Skripsi_verifikasi_klaim') !== false || strpos($current_url, 'Skripsi_aktivitas_penilaian') !== false || strpos($current_url, 'Countdown_sidang_skripsi') !== false ? 'active' : '') . '"><i class="nav-icon fas fa-graduation-cap"></i><p>Skripsi' . $b_skripsi . ' <i class="fas fa-angle-left right"></i></p></a>
                    <ul class="nav nav-treeview">
                        <li class="nav-item"><a href="' . site_url('Registrasi_skripsi') . '" class="nav-link ' . (strpos($current_url, 'Registrasi_skripsi') !== false ? 'active' : '') . '"><i class="far fa-circle nav-icon"></i><p>Registrasi Mahasiswa</p></a></li>
                        <li class="nav-item"><a href="' . site_url('Skripsi_plot_pembimbing') . '" class="nav-link ' . (strpos($current_url, 'Skripsi_plot_pembimbing') !== false ? 'active' : '') . '"><i class="far fa-circle nav-icon"></i><p>Plot Pembimbing' . sekprodi_badge_html('skripsi_judul') . '</p></a></li>
                        <li class="nav-item"><a href="' . site_url('Skripsi_perubahan_judul_sekprodi') . '" class="nav-link ' . (strpos($current_url, 'Skripsi_perubahan_judul_sekprodi') !== false ? 'active' : '') . '"><i class="far fa-circle nav-icon"></i><p>Validasi Perubahan Judul' . sekprodi_badge_html('skripsi_perubahan') . '</p></a></li>
                        <li class="nav-item"><a href="' . site_url('Skripsi_verifikasi_berkas_sidang') . '" class="nav-link ' . (strpos($current_url, 'Skripsi_verifikasi_berkas_sidang') !== false ? 'active' : '') . '"><i class="far fa-circle nav-icon"></i><p>Verifikasi Berkas Sidang' . sekprodi_badge_html('skripsi_sidang') . '</p></a></li>
                        <li class="nav-item"><a href="' . site_url('Jadwal_skripsi_sekprodi') . '" class="nav-link ' . (strpos($current_url, 'Jadwal_skripsi_sekprodi') !== false ? 'active' : '') . '"><i class="far fa-circle nav-icon"></i><p>Jadwal Sidang</p></a></li>
                        <li class="nav-item"><a href="' . site_url('Countdown_sidang_skripsi') . '" class="nav-link ' . (strpos($current_url, 'Countdown_sidang_skripsi') !== false ? 'active' : '') . '"><i class="far fa-circle nav-icon"></i><p>Countdown Sidang</p></a></li>
                        <li class="nav-item"><a href="' . site_url('Monitoring_penguji_sidang_skripsi') . '" class="nav-link ' . (strpos($current_url, 'Monitoring_penguji_sidang_skripsi') !== false ? 'active' : '') . '"><i class="far fa-circle nav-icon"></i><p>Monitoring Penguji</p></a></li>
                        <li class="nav-item"><a href="' . site_url('Skripsi_verifikasi_klaim') . '" class="nav-link ' . (strpos($current_url, 'Skripsi_verifikasi_klaim') !== false ? 'active' : '') . '"><i class="far fa-circle nav-icon"></i><p>Verifikasi Klaim Nilai' . sekprodi_badge_html('skripsi_klaim') . '</p></a></li>
                        <li class="nav-item"><a href="' . site_url('Skripsi_aktivitas_penilaian') . '" class="nav-link ' . (strpos($current_url, 'Skripsi_aktivitas_penilaian') !== false ? 'active' : '') . '"><i class="far fa-circle nav-icon"></i><p>Aktivitas Penilaian</p></a></li>
                    </ul>
                </li>
                <li class="nav-item has-treeview ' . (strpos($current_url, 'Manajemen_berkas') !== false || strpos($current_url, 'Arsip') !== false || strpos($current_url, 'Permintaan_surat') !== false || strpos($current_url, 'Tagihan_berkas') !== false ? 'menu-open' : '') . '">
                    <a href="#" class="nav-link ' . (strpos($current_url, 'Manajemen_berkas') !== false || strpos($current_url, 'Arsip') !== false || strpos($current_url, 'Permintaan_surat') !== false || strpos($current_url, 'Tagihan_berkas') !== false ? 'active' : '') . '"><i class="nav-icon fas fa-file-alt"></i><p>Dokumen & Surat' . $b_dokumen . ' <i class="fas fa-angle-left right"></i></p></a>
                    <ul class="nav nav-treeview">
                        <li class="nav-item"><a href="' . site_url('Manajemen_berkas') . '" class="nav-link ' . (strpos($current_url, 'Manajemen_berkas') !== false ? 'active' : '') . '"><i class="far fa-circle nav-icon"></i><p>Manajemen Berkas</p></a></li>
                        <li class="nav-item"><a href="' . site_url('Arsip') . '" class="nav-link ' . (strpos($current_url, 'Arsip') !== false ? 'active' : '') . '"><i class="far fa-circle nav-icon"></i><p>Arsip</p></a></li>
                        <li class="nav-item"><a href="' . site_url('Permintaan_surat_sekprodi') . '" class="nav-link ' . (strpos($current_url, 'Permintaan_surat') !== false ? 'active' : '') . '"><i class="far fa-circle nav-icon"></i><p>Permintaan Surat' . sekprodi_badge_html('permintaan_surat') . '</p></a></li>
                        <li class="nav-item"><a href="' . site_url('Tagihan_berkas_sekprodi') . '" class="nav-link ' . (strpos($current_url, 'Tagihan_berkas') !== false ? 'active' : '') . '"><i class="far fa-circle nav-icon"></i><p>Tagihan Berkas</p></a></li>
                    </ul>
                </li>
                <li class="nav-item has-treeview ' . (strpos($current_url, 'Honor') !== false ? 'menu-open' : '') . '">
                    <a href="#" class="nav-link ' . (strpos($current_url, 'Honor') !== false ? 'active' : '') . '"><i class="nav-icon fas fa-money-bill-wave"></i><p>Honor <i class="fas fa-angle-left right"></i></p></a>
                    <ul class="nav nav-treeview">
                        <li class="nav-item"><a href="' . site_url('Honor_setup') . '" class="nav-link ' . (strpos($current_url, 'Honor_setup') !== false ? 'active' : '') . '"><i class="far fa-circle nav-icon"></i><p>Pengaturan Honor</p></a></li>
                        <li class="nav-item"><a href="' . site_url('Honor_uts') . '" class="nav-link ' . (strpos($current_url, 'Honor_uts') !== false ? 'active' : '') . '"><i class="far fa-circle nav-icon"></i><p>Honor UTS</p></a></li>
                        <li class="nav-item"><a href="' . site_url('Honor_uas') . '" class="nav-link ' . (strpos($current_url, 'Honor_uas') !== false ? 'active' : '') . '"><i class="far fa-circle nav-icon"></i><p>Honor UAS</p></a></li>
                        <li class="nav-item"><a href="' . site_url('Honor_pkl') . '" class="nav-link ' . (strpos($current_url, 'Honor_pkl') !== false ? 'active' : '') . '"><i class="far fa-circle nav-icon"></i><p>Honor PKL</p></a></li>
                        <li class="nav-item"><a href="' . site_url('Honor_skripsi') . '" class="nav-link ' . (strpos($current_url, 'Honor_skripsi') !== false ? 'active' : '') . '"><i class="far fa-circle nav-icon"></i><p>Honor Skripsi</p></a></li>
                        <li class="nav-item"><a href="' . site_url('Honor_proposal') . '" class="nav-link ' . (strpos($current_url, 'Honor_proposal') !== false ? 'active' : '') . '"><i class="far fa-circle nav-icon"></i><p>Honor Proposal TA</p></a></li>
                    </ul>
                </li>
                <li class="nav-item has-treeview ' . (strpos($current_url, 'Jadwal') !== false || strpos($current_url, 'Mapping_mk') !== false || strpos($current_url, 'Monitoring_ruangan') !== false || strpos($current_url, 'Monitoring_jadwal_dosen') !== false ? 'menu-open' : '') . '">
                    <a href="#" class="nav-link ' . (strpos($current_url, 'Jadwal') !== false || strpos($current_url, 'Mapping_mk') !== false || strpos($current_url, 'Monitoring_ruangan') !== false || strpos($current_url, 'Monitoring_jadwal_dosen') !== false ? 'active' : '') . '"><i class="nav-icon fas fa-calendar-alt"></i><p>Jadwal <i class="fas fa-angle-left right"></i></p></a>
                    <ul class="nav nav-treeview">
                        <li class="nav-item"><a href="' . site_url('Jadwal_kuliah') . '" class="nav-link ' . (strpos($current_url, 'Jadwal_kuliah') !== false ? 'active' : '') . '"><i class="far fa-circle nav-icon"></i><p>Jadwal Kuliah</p></a></li>
                        <li class="nav-item"><a href="' . site_url('Jadwal_uts') . '" class="nav-link ' . (strpos($current_url, 'Jadwal_uts') !== false ? 'active' : '') . '"><i class="far fa-circle nav-icon"></i><p>Jadwal UTS</p></a></li>
                        <li class="nav-item"><a href="' . site_url('Jadwal_uas') . '" class="nav-link ' . (strpos($current_url, 'Jadwal_uas') !== false ? 'active' : '') . '"><i class="far fa-circle nav-icon"></i><p>Jadwal UAS</p></a></li>
                        <li class="nav-item"><a href="' . site_url('Mapping_mk') . '" class="nav-link ' . (strpos($current_url, 'Mapping_mk') !== false ? 'active' : '') . '"><i class="far fa-circle nav-icon"></i><p>Mapping Mata Kuliah</p></a></li>
                        <li class="nav-item"><a href="' . site_url('Monitoring_ruangan') . '" class="nav-link ' . (strpos($current_url, 'Monitoring_ruangan') !== false ? 'active' : '') . '"><i class="far fa-circle nav-icon"></i><p>Monitoring Ruangan</p></a></li>
                        <li class="nav-item"><a href="' . site_url('Monitoring_jadwal_dosen') . '" class="nav-link ' . (strpos($current_url, 'Monitoring_jadwal_dosen') !== false ? 'active' : '') . '"><i class="far fa-circle nav-icon"></i><p>Monitoring Jadwal Dosen</p></a></li>
                    </ul>
                </li>
                <li class="nav-item has-treeview ' . (strpos($current_url, 'Laporan') !== false || strpos($current_url, 'Pkl_laporan_sekprodi') !== false || strpos($current_url, 'Skripsi_laporan') !== false ? 'menu-open' : '') . '">
                    <a href="#" class="nav-link ' . (strpos($current_url, 'Laporan') !== false || strpos($current_url, 'Pkl_laporan_sekprodi') !== false || strpos($current_url, 'Skripsi_laporan') !== false ? 'active' : '') . '"><i class="nav-icon fas fa-file-alt"></i><p>Laporan <i class="fas fa-angle-left right"></i></p></a>
                    <ul class="nav nav-treeview">
                        <li class="nav-item"><a href="' . site_url('Laporan/laporan_uts') . '" class="nav-link ' . (strpos($current_url, 'Laporan/laporan_uts') !== false ? 'active' : '') . '"><i class="far fa-circle nav-icon"></i><p>Laporan UTS</p></a></li>
                        <li class="nav-item"><a href="' . site_url('Laporan/laporan_uas') . '" class="nav-link ' . (strpos($current_url, 'Laporan/laporan_uas') !== false ? 'active' : '') . '"><i class="far fa-circle nav-icon"></i><p>Laporan UAS</p></a></li>
                        <li class="nav-item"><a href="' . site_url('Pkl_laporan_sekprodi') . '" class="nav-link ' . (strpos($current_url, 'Pkl_laporan_sekprodi') !== false ? 'active' : '') . '"><i class="far fa-circle nav-icon"></i><p>Laporan PKL</p></a></li>
                        <li class="nav-item"><a href="' . site_url('Skripsi_laporan') . '" class="nav-link ' . (strpos($current_url, 'Skripsi_laporan') !== false ? 'active' : '') . '"><i class="far fa-circle nav-icon"></i><p>Laporan Skripsi</p></a></li>
                        <li class="nav-item"><a href="' . site_url('Proposal_laporan') . '" class="nav-link ' . (strpos($current_url, 'Proposal_laporan') !== false ? 'active' : '') . '"><i class="far fa-circle nav-icon"></i><p>Laporan Proposal TA</p></a></li>
                    </ul>
                </li>
                <li class="nav-item"><a href="' . site_url('Rapat') . '" class="nav-link ' . (strpos($current_url, 'Rapat') !== false ? 'active' : '') . '"><i class="nav-icon fas fa-users"></i><p>Rapat</p></a></li>
                <li class="nav-item"><a href="' . site_url('Kegiatan') . '" class="nav-link ' . (strpos($current_url, 'Kegiatan') !== false ? 'active' : '') . '"><i class="nav-icon fas fa-calendar-check"></i><p>Kegiatan</p></a></li>
                <li class="nav-item has-treeview ' . (strpos($current_url, 'Skpi') !== false ? 'menu-open' : '') . '">
                    <a href="#" class="nav-link ' . (strpos($current_url, 'Skpi') !== false ? 'active' : '') . '"><i class="nav-icon fas fa-certificate"></i><p>SKPI' . $b_skpi . ' <i class="fas fa-angle-left right"></i></p></a>
                    <ul class="nav nav-treeview">
                        <li class="nav-item"><a href="' . site_url('Skpi') . '" class="nav-link ' . (strpos($current_url, 'Skpi') !== false && strpos($current_url, 'Skpi_verifikasi') === false && strpos($current_url, 'Skpi_mahasiswa') === false ? 'active' : '') . '"><i class="far fa-circle nav-icon"></i><p>Kelola Periode</p></a></li>
                        <li class="nav-item"><a href="' . site_url('Skpi_verifikasi') . '" class="nav-link ' . (strpos($current_url, 'Skpi_verifikasi') !== false ? 'active' : '') . '"><i class="far fa-circle nav-icon"></i><p>Verifikasi Pengajuan' . sekprodi_badge_html('skpi_verifikasi') . '</p></a></li>
                    </ul>
                </li>
                <li class="nav-item has-treeview ' . (strpos($current_url, 'Pkm') !== false ? 'menu-open' : '') . '">
                    <a href="#" class="nav-link ' . (strpos($current_url, 'Pkm') !== false ? 'active' : '') . '"><i class="nav-icon fas fa-lightbulb"></i><p>PKM' . $b_pkm . ' <i class="fas fa-angle-left right"></i></p></a>
                    <ul class="nav nav-treeview">
                        <li class="nav-item"><a href="' . site_url('Pkm') . '" class="nav-link ' . (strpos($current_url, 'Pkm') !== false && strpos($current_url, 'Pkm_verifikasi') === false && strpos($current_url, 'Pkm_mahasiswa') === false ? 'active' : '') . '"><i class="far fa-circle nav-icon"></i><p>Kelola Periode</p></a></li>
                        <li class="nav-item"><a href="' . site_url('Pkm_verifikasi') . '" class="nav-link ' . (strpos($current_url, 'Pkm_verifikasi') !== false && strpos($current_url, 'Pkm_verifikasi_proposal') === false ? 'active' : '') . '"><i class="far fa-circle nav-icon"></i><p>Verifikasi Pengajuan' . sekprodi_badge_html('pkm_verifikasi') . '</p></a></li>
                        <li class="nav-item"><a href="' . site_url('Pkm_verifikasi_proposal') . '" class="nav-link ' . (strpos($current_url, 'Pkm_verifikasi_proposal') !== false ? 'active' : '') . '"><i class="far fa-circle nav-icon"></i><p>Verifikasi Proposal Final' . sekprodi_badge_html('pkm_proposal_final') . '</p></a></li>
                    </ul>
                </li>
                <li class="nav-item"><a href="' . site_url('Tanda_terima') . '" class="nav-link ' . (strpos($current_url, 'Tanda_terima') !== false ? 'active' : '') . '"><i class="nav-icon fas fa-box-open"></i><p>Tanda Terima</p></a></li>
                <li class="nav-item"><a href="' . site_url('Profile') . '" class="nav-link ' . (strpos($current_url, 'Profile') !== false ? 'active' : '') . '"><i class="nav-icon fas fa-user-cog"></i><p>Profil</p></a></li>
                <li class="nav-item"><a href="' . site_url('Auth/logout') . '" class="nav-link"><i class="nav-icon fas fa-sign-out-alt"></i><p>Logout</p></a></li>
            </ul>
        </nav>';
        return $menu;
    }
}

if (!function_exists('get_dosen_menu')) {
    function get_dosen_menu() {
        $current_url = current_url();
        $menu = '
        <nav class="mt-2">
            <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu" data-accordion="false">
                <li class="nav-item"><a href="' . site_url('Halaman_utama') . '" class="nav-link ' . (strpos($current_url, 'Halaman_utama') !== false ? 'active' : '') . '"><i class="nav-icon fas fa-home"></i><p>Halaman Utama</p></a></li>
                ' . chat_sidebar_menu_item() . '
                <li class="nav-item has-treeview ' . (strpos($current_url, 'Jadwal_mengajar') !== false || strpos($current_url, 'Jadwal_pengawas') !== false ? 'menu-open' : '') . '">
                    <a href="#" class="nav-link ' . (strpos($current_url, 'Jadwal_mengajar') !== false || strpos($current_url, 'Jadwal_pengawas') !== false ? 'active' : '') . '"><i class="nav-icon fas fa-calendar-alt"></i><p>Jadwal <i class="fas fa-angle-left right"></i></p></a>
                    <ul class="nav nav-treeview">
                        <li class="nav-item"><a href="' . site_url('Jadwal_mengajar') . '" class="nav-link ' . (strpos($current_url, 'Jadwal_mengajar') !== false ? 'active' : '') . '"><i class="far fa-circle nav-icon"></i><p>Jadwal Mata Kuliah</p></a></li>
                        <li class="nav-item"><a href="' . site_url('Jadwal_pengawas/uts') . '" class="nav-link ' . (strpos($current_url, 'Jadwal_pengawas/uts') !== false || (strpos($current_url, 'Jadwal_pengawas') !== false && strpos($current_url, 'uas') === false) ? 'active' : '') . '"><i class="far fa-circle nav-icon"></i><p>Jadwal UTS</p></a></li>
                        <li class="nav-item"><a href="' . site_url('Jadwal_pengawas/uas') . '" class="nav-link ' . (strpos($current_url, 'Jadwal_pengawas/uas') !== false ? 'active' : '') . '"><i class="far fa-circle nav-icon"></i><p>Jadwal UAS</p></a></li>
                    </ul>
                </li>
                <li class="nav-item has-treeview ' . (strpos($current_url, 'Ajuan_judul_pkl') !== false || strpos($current_url, 'Bimbingan_pkl') !== false || strpos($current_url, 'Pkl_mahasiswa_dosen') !== false || strpos($current_url, 'Jadwal_pkl_dosen') !== false || strpos($current_url, 'Bimbingan_pasca_pkl') !== false ? 'menu-open' : '') . '">
                    <a href="#" class="nav-link ' . (strpos($current_url, 'Ajuan_judul_pkl') !== false || strpos($current_url, 'Bimbingan_pkl') !== false || strpos($current_url, 'Pkl_mahasiswa_dosen') !== false || strpos($current_url, 'Jadwal_pkl_dosen') !== false || strpos($current_url, 'Bimbingan_pasca_pkl') !== false ? 'active' : '') . '"><i class="nav-icon fas fa-briefcase"></i><p>PKL <i class="fas fa-angle-left right"></i></p></a>
                    <ul class="nav nav-treeview">
                        <li class="nav-item"><a href="' . site_url('Ajuan_judul_pkl') . '" class="nav-link ' . (strpos($current_url, 'Ajuan_judul_pkl') !== false ? 'active' : '') . '"><i class="far fa-circle nav-icon"></i><p>Ajuan Judul Mahasiswa</p></a></li>
                        <li class="nav-item"><a href="' . site_url('Bimbingan_pkl') . '" class="nav-link ' . (strpos($current_url, 'Bimbingan_pkl') !== false && strpos($current_url, 'Bimbingan_pasca_pkl') === false ? 'active' : '') . '"><i class="far fa-circle nav-icon"></i><p>Bimbingan PKL</p></a></li>
                        <li class="nav-item"><a href="' . site_url('Pkl_mahasiswa_dosen') . '" class="nav-link ' . (strpos($current_url, 'Pkl_mahasiswa_dosen') !== false ? 'active' : '') . '"><i class="far fa-circle nav-icon"></i><p>Daftar Mahasiswa</p></a></li>
                        <li class="nav-item"><a href="' . site_url('Pkl_mahasiswa_dosen/monitoring') . '" class="nav-link ' . (strpos($current_url, 'Pkl_mahasiswa_dosen/monitoring') !== false ? 'active' : '') . '"><i class="far fa-circle nav-icon"></i><p>Logbook & Absensi</p></a></li>
                        <li class="nav-item"><a href="' . site_url('Pkl_monitor_peta_dosen') . '" class="nav-link ' . (strpos($current_url, 'Pkl_monitor_peta_dosen') !== false ? 'active' : '') . '"><i class="far fa-circle nav-icon"></i><p>Monitor PKL</p></a></li>
                        <li class="nav-item"><a href="' . site_url('Jadwal_pkl_dosen') . '" class="nav-link ' . (strpos($current_url, 'Jadwal_pkl_dosen') !== false ? 'active' : '') . '"><i class="far fa-circle nav-icon"></i><p>Jadwal Sidang PKL</p></a></li>
                        <li class="nav-item"><a href="' . site_url('Bimbingan_pasca_pkl') . '" class="nav-link ' . (strpos($current_url, 'Bimbingan_pasca_pkl') !== false ? 'active' : '') . '"><i class="far fa-circle nav-icon"></i><p>Bimbingan Pasca Sidang</p></a></li>
                    </ul>
                </li>
                ' . proposal_menu_group_html('dosen', $current_url) . '
                <li class="nav-item has-treeview ' . (strpos($current_url, 'Ajuan_judul_skripsi') !== false || strpos($current_url, 'Bimbingan_skripsi') !== false || strpos($current_url, 'Skripsi_mahasiswa_dosen') !== false || strpos($current_url, 'Jadwal_skripsi_dosen') !== false || strpos($current_url, 'Bimbingan_pasca_skripsi') !== false ? 'menu-open' : '') . '">
                    <a href="#" class="nav-link ' . (strpos($current_url, 'Ajuan_judul_skripsi') !== false || strpos($current_url, 'Bimbingan_skripsi') !== false || strpos($current_url, 'Skripsi_mahasiswa_dosen') !== false || strpos($current_url, 'Jadwal_skripsi_dosen') !== false || strpos($current_url, 'Bimbingan_pasca_skripsi') !== false ? 'active' : '') . '"><i class="nav-icon fas fa-graduation-cap"></i><p>Skripsi <i class="fas fa-angle-left right"></i></p></a>
                    <ul class="nav nav-treeview">
                        <li class="nav-item"><a href="' . site_url('Ajuan_judul_skripsi') . '" class="nav-link ' . (strpos($current_url, 'Ajuan_judul_skripsi') !== false ? 'active' : '') . '"><i class="far fa-circle nav-icon"></i><p>Ajuan Judul Mahasiswa</p></a></li>
                        <li class="nav-item"><a href="' . site_url('Bimbingan_skripsi') . '" class="nav-link ' . (strpos($current_url, 'Bimbingan_skripsi') !== false && strpos($current_url, 'Bimbingan_pasca_skripsi') === false ? 'active' : '') . '"><i class="far fa-circle nav-icon"></i><p>Bimbingan Skripsi</p></a></li>
                        <li class="nav-item"><a href="' . site_url('Skripsi_mahasiswa_dosen') . '" class="nav-link ' . (strpos($current_url, 'Skripsi_mahasiswa_dosen') !== false ? 'active' : '') . '"><i class="far fa-circle nav-icon"></i><p>Daftar Mahasiswa</p></a></li>
                        <li class="nav-item"><a href="' . site_url('Jadwal_skripsi_dosen') . '" class="nav-link ' . (strpos($current_url, 'Jadwal_skripsi_dosen') !== false ? 'active' : '') . '"><i class="far fa-circle nav-icon"></i><p>Jadwal Sidang Skripsi</p></a></li>
                        <li class="nav-item"><a href="' . site_url('Bimbingan_pasca_skripsi') . '" class="nav-link ' . (strpos($current_url, 'Bimbingan_pasca_skripsi') !== false ? 'active' : '') . '"><i class="far fa-circle nav-icon"></i><p>Bimbingan Pasca Sidang</p></a></li>
                    </ul>
                </li>
                <li class="nav-item has-treeview ' . (strpos($current_url, 'Ajuan_judul_pkm') !== false || strpos($current_url, 'Bimbingan_pkm') !== false || strpos($current_url, 'Pkm_perubahan_judul_dosen') !== false ? 'menu-open' : '') . '">
                    <a href="#" class="nav-link ' . (strpos($current_url, 'Ajuan_judul_pkm') !== false || strpos($current_url, 'Bimbingan_pkm') !== false || strpos($current_url, 'Pkm_perubahan_judul_dosen') !== false ? 'active' : '') . '"><i class="nav-icon fas fa-lightbulb"></i><p>PKM <i class="fas fa-angle-left right"></i></p></a>
                    <ul class="nav nav-treeview">
                        <li class="nav-item"><a href="' . site_url('Ajuan_judul_pkm') . '" class="nav-link ' . (strpos($current_url, 'Ajuan_judul_pkm') !== false ? 'active' : '') . '"><i class="far fa-circle nav-icon"></i><p>Ajuan Judul PKM</p></a></li>
                        <li class="nav-item"><a href="' . site_url('Pkm_perubahan_judul_dosen') . '" class="nav-link ' . (strpos($current_url, 'Pkm_perubahan_judul_dosen') !== false ? 'active' : '') . '"><i class="far fa-circle nav-icon"></i><p>Perubahan Judul</p></a></li>
                        <li class="nav-item"><a href="' . site_url('Bimbingan_pkm') . '" class="nav-link ' . (strpos($current_url, 'Bimbingan_pkm') !== false ? 'active' : '') . '"><i class="far fa-circle nav-icon"></i><p>Bimbingan PKM</p></a></li>
                    </ul>
                </li>
                <li class="nav-item"><a href="' . site_url('Rapat/dosen') . '" class="nav-link ' . (strpos($current_url, 'Rapat') !== false ? 'active' : '') . '"><i class="nav-icon fas fa-users"></i><p>Rapat</p></a></li>
                <li class="nav-item"><a href="' . site_url('Tanda_terima_dosen') . '" class="nav-link ' . (strpos($current_url, 'Tanda_terima_dosen') !== false ? 'active' : '') . '"><i class="nav-icon fas fa-box-open"></i><p>Tanda Terima</p></a></li>
                <li class="nav-item"><a href="' . site_url('Profile') . '" class="nav-link ' . (strpos($current_url, 'Profile') !== false ? 'active' : '') . '"><i class="nav-icon fas fa-user-cog"></i><p>Profil</p></a></li>
                <li class="nav-item"><a href="' . site_url('Auth/logout') . '" class="nav-link"><i class="nav-icon fas fa-sign-out-alt"></i><p>Logout</p></a></li>
            </ul>
        </nav>';
        return $menu;
    }
}

if (!function_exists('proposal_menu_group_html')) {
    /**
     * Grup menu "Proposal TA" per peran (sekprodi, kaprodi, dosen, mahasiswa).
     * Tambahkan item baru di array $items sesuai fase pengembangan.
     */
    function proposal_menu_group_html($role, $current_url) {
        $items = array(
            'sekprodi' => array(
                array('Registrasi_proposal', 'Registrasi Mahasiswa'),
                array('Proposal_pengajuan/review', 'Review Pengajuan Judul'),
                array('Proposal_plot_pembimbing', 'Plot Pembimbing'),
                array('Proposal_perubahan_judul_sekprodi', 'Validasi Perubahan Judul'),
                array('Proposal_verifikasi_berkas_sidang', 'Verifikasi Berkas Sidang'),
                array('Jadwal_proposal_sekprodi', 'Jadwal Sidang'),
                array('Proposal_verifikasi_klaim', 'Verifikasi Klaim Nilai'),
                array('Monitoring_penguji_sidang_proposal', 'Monitoring Penguji'),
                array('Proposal_aktivitas_penilaian', 'Aktivitas Penilaian'),
            ),
            'kaprodi' => array(
                array('Registrasi_proposal', 'Daftar Mahasiswa Proposal TA'),
                array('Jadwal_proposal_sekprodi', 'Jadwal Sidang'),
                array('Proposal_aktivitas_penilaian', 'Aktivitas Penilaian'),
            ),
            'dosen' => array(
                array('Ajuan_judul_proposal', 'Ajuan Judul Mahasiswa'),
                array('Bimbingan_proposal', 'Bimbingan Proposal'),
                array('Proposal_mahasiswa_dosen', 'Daftar Mahasiswa'),
                array('Jadwal_proposal_dosen', 'Jadwal Sidang Proposal'),
                array('Bimbingan_pasca_proposal', 'Bimbingan Pasca Sidang'),
            ),
            'mahasiswa' => array(
                array('Proposal_pengajuan', 'Pengajuan Judul'),
                array('Proposal_perubahan_judul', 'Perubahan Judul'),
                array('Bimbingan_proposal_mahasiswa', 'Bimbingan Proposal'),
                array('Pendaftaran_sidang_proposal', 'Pendaftaran Sidang'),
                array('Proposal_catatan_revisi', 'Jadwal Sidang dan Revisi'),
                array('Bimbingan_pasca_proposal_mahasiswa', 'Bimbingan Pasca Sidang'),
                array('Proposal_klaim_nilai', 'Klaim Nilai'),
            ),
        );
        if (empty($items[$role])) {
            return '';
        }

        $semua_aktif = false;
        $li = '';
        $badge_map = array(
            'Proposal_pengajuan/review' => 'proposal_judul',
            'Proposal_perubahan_judul_sekprodi' => 'proposal_perubahan',
            'Proposal_verifikasi_berkas_sidang' => 'proposal_sidang',
            'Proposal_verifikasi_klaim' => 'proposal_klaim',
        );
        foreach ($items[$role] as $it) {
            $key = $it[0];
            // "Proposal_pengajuan" juga cocok dengan "Proposal_pengajuan/review": bedakan untuk sekprodi
            $aktif = strpos($current_url, $key) !== false;
            if ($role === 'sekprodi' && $key === 'Proposal_pengajuan/review') {
                $aktif = strpos($current_url, 'Proposal_pengajuan') !== false;
            }
            if ($role === 'mahasiswa' && $key === 'Proposal_pengajuan') {
                $aktif = strpos($current_url, 'Proposal_pengajuan') !== false && strpos($current_url, 'Proposal_perubahan') === false;
            }
            $semua_aktif = $semua_aktif || $aktif;
            $badge = '';
            if ($role === 'sekprodi' && isset($badge_map[$key]) && function_exists('sekprodi_badge_html')) {
                $badge = sekprodi_badge_html($badge_map[$key]);
            }
            $li .= '<li class="nav-item"><a href="' . site_url($key) . '" class="nav-link ' . ($aktif ? 'active' : '')
                . '"><i class="far fa-circle nav-icon"></i><p>' . $it[1] . $badge . '</p></a></li>';
        }

        $group_badge = '';
        if ($role === 'sekprodi' && function_exists('sekprodi_badge_sum_html')) {
            $group_badge = sekprodi_badge_sum_html(array('proposal_judul', 'proposal_perubahan', 'proposal_sidang', 'proposal_klaim'));
        }

        return '<li class="nav-item has-treeview ' . ($semua_aktif ? 'menu-open' : '') . '">'
            . '<a href="#" class="nav-link ' . ($semua_aktif ? 'active' : '') . '"><i class="nav-icon fas fa-file-signature"></i>'
            . '<p>Proposal TA' . $group_badge . ' <i class="fas fa-angle-left right"></i></p></a>'
            . '<ul class="nav nav-treeview">' . $li . '</ul></li>';
    }
}

if (!function_exists('get_mahasiswa_menu')) {
    function get_mahasiswa_menu() {
        $current_url = current_url();
        $ci =& get_instance();
        $ci->load->model('Mahasiswa_pkl_model');
        $ci->load->model('Skripsi_model');
        $ci->load->model('Tahun_akademik_model');
        $ci->load->model('Skpi_periode_model');
        $ci->load->model('Pkm_periode_model');
        $id_mahasiswa = $ci->session->userdata('ref_id');
        $tahun_aktif = $ci->Tahun_akademik_model->get_aktif();
        $is_registered_pkl = false;
        $is_registered_skripsi = false;
        $is_registered_proposal = false;
        $has_skpi = false;
        $has_pkm = false;
        if ($id_mahasiswa && $tahun_aktif) {
            $is_registered_pkl = $ci->Mahasiswa_pkl_model->is_registered($id_mahasiswa, $tahun_aktif->id);
            $skripsi = $ci->Skripsi_model->get_by_mahasiswa($id_mahasiswa, $tahun_aktif->id);
            $is_registered_skripsi = ($skripsi !== null);
            $ci->load->model('Proposal_model');
            $is_registered_proposal = ($ci->Proposal_model->get_by_mahasiswa($id_mahasiswa, $tahun_aktif->id) !== null);
        }
        if ($id_mahasiswa) {
            $has_skpi = $ci->Skpi_periode_model->has_any_periode($id_mahasiswa);
            $has_pkm = $ci->Pkm_periode_model->has_active_periode();
        }
        $menu = '
        <nav class="mt-2">
            <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu" data-accordion="false">
                <li class="nav-item"><a href="' . site_url('Halaman_utama') . '" class="nav-link ' . (strpos($current_url, 'Halaman_utama') !== false ? 'active' : '') . '"><i class="nav-icon fas fa-home"></i><p>Halaman Utama</p></a></li>';
        $menu .= chat_sidebar_menu_item();
        if ($is_registered_pkl) {
            $menu .= '
                <li class="nav-item has-treeview ' . (strpos($current_url, 'Pkl') !== false || strpos($current_url, 'pkl') !== false || strpos($current_url, 'Pendaftaran_sidang_pkl') !== false || strpos($current_url, 'Pkl_klaim_nilai') !== false || strpos($current_url, 'Bimbingan_pasca_pkl_mahasiswa') !== false ? 'menu-open' : '') . '">
                    <a href="#" class="nav-link ' . (strpos($current_url, 'Pkl') !== false || strpos($current_url, 'pkl') !== false || strpos($current_url, 'Pendaftaran_sidang_pkl') !== false || strpos($current_url, 'Pkl_klaim_nilai') !== false || strpos($current_url, 'Bimbingan_pasca_pkl_mahasiswa') !== false ? 'active' : '') . '"><i class="nav-icon fas fa-briefcase"></i><p>PKL <i class="fas fa-angle-left right"></i></p></a>
                    <ul class="nav nav-treeview">
                        <li class="nav-item"><a href="' . site_url('Pkl/registrasi') . '" class="nav-link ' . (strpos($current_url, 'Pkl/registrasi') !== false ? 'active' : '') . '"><i class="far fa-circle nav-icon"></i><p>Pengajuan Tempat PKL</p></a></li>
                        <li class="nav-item"><a href="' . site_url('Pkl/submit') . '" class="nav-link ' . (strpos($current_url, 'Pkl/submit') !== false || strpos($current_url, 'Pkl/pengajuan') !== false ? 'active' : '') . '"><i class="far fa-circle nav-icon"></i><p>Pengajuan Judul PKL</p></a></li>
                        <li class="nav-item"><a href="' . site_url('Pkl/bimbingan') . '" class="nav-link ' . (strpos($current_url, 'Pkl/bimbingan') !== false ? 'active' : '') . '"><i class="far fa-circle nav-icon"></i><p>Bimbingan PKL</p></a></li>
                        <li class="nav-item"><a href="' . site_url('Pkl/logbook') . '" class="nav-link ' . (strpos($current_url, 'Pkl/logbook') !== false ? 'active' : '') . '"><i class="far fa-circle nav-icon"></i><p>Logbook PKL</p></a></li>
                        <li class="nav-item"><a href="' . site_url('Pkl_absensi') . '" class="nav-link ' . (strpos($current_url, 'Pkl_absensi') !== false ? 'active' : '') . '"><i class="far fa-circle nav-icon"></i><p>Absensi Harian</p></a></li>
                        <li class="nav-item"><a href="' . site_url('Pendaftaran_sidang_pkl') . '" class="nav-link ' . (strpos($current_url, 'Pendaftaran_sidang_pkl') !== false ? 'active' : '') . '"><i class="far fa-circle nav-icon"></i><p>Pendaftaran Sidang</p></a></li>
                        <li class="nav-item"><a href="' . site_url('Bimbingan_pasca_pkl_mahasiswa') . '" class="nav-link ' . (strpos($current_url, 'Bimbingan_pasca_pkl_mahasiswa') !== false ? 'active' : '') . '"><i class="far fa-circle nav-icon"></i><p>Bimbingan Pasca Sidang</p></a></li>
                        <li class="nav-item"><a href="' . site_url('Pkl_klaim_nilai') . '" class="nav-link ' . (strpos($current_url, 'Pkl_klaim_nilai') !== false ? 'active' : '') . '"><i class="far fa-circle nav-icon"></i><p>Klaim Nilai</p></a></li>
                    </ul>
                </li>';
        }
        if ($is_registered_proposal) {
            $menu .= proposal_menu_group_html('mahasiswa', $current_url);
        }
        if ($is_registered_skripsi) {
            $menu .= '
                <li class="nav-item has-treeview ' . (strpos($current_url, 'Skripsi') !== false || strpos($current_url, 'skripsi') !== false || strpos($current_url, 'Bimbingan_skripsi_mahasiswa') !== false || strpos($current_url, 'Pendaftaran_sidang_skripsi') !== false || strpos($current_url, 'Pendaftaran_jadwal_skripsi') !== false || strpos($current_url, 'Skripsi_klaim_nilai') !== false || strpos($current_url, 'Skripsi_perubahan_judul') !== false || strpos($current_url, 'Skripsi_catatan_revisi') !== false || strpos($current_url, 'Bimbingan_pasca_skripsi_mahasiswa') !== false ? 'menu-open' : '') . '">
                    <a href="#" class="nav-link ' . (strpos($current_url, 'Skripsi') !== false || strpos($current_url, 'skripsi') !== false || strpos($current_url, 'Bimbingan_skripsi_mahasiswa') !== false || strpos($current_url, 'Pendaftaran_sidang_skripsi') !== false || strpos($current_url, 'Pendaftaran_jadwal_skripsi') !== false || strpos($current_url, 'Skripsi_klaim_nilai') !== false || strpos($current_url, 'Skripsi_perubahan_judul') !== false || strpos($current_url, 'Skripsi_catatan_revisi') !== false || strpos($current_url, 'Bimbingan_pasca_skripsi_mahasiswa') !== false ? 'active' : '') . '"><i class="nav-icon fas fa-graduation-cap"></i><p>Skripsi <i class="fas fa-angle-left right"></i></p></a>
                    <ul class="nav nav-treeview">
                        <li class="nav-item"><a href="' . site_url('Skripsi_pengajuan') . '" class="nav-link ' . (strpos($current_url, 'Skripsi_pengajuan') !== false ? 'active' : '') . '"><i class="far fa-circle nav-icon"></i><p>Pengajuan Judul Skripsi</p></a></li>
                        <li class="nav-item"><a href="' . site_url('Bimbingan_skripsi_mahasiswa/bimbingan') . '" class="nav-link ' . (strpos($current_url, 'Bimbingan_skripsi_mahasiswa') !== false ? 'active' : '') . '"><i class="far fa-circle nav-icon"></i><p>Bimbingan Skripsi</p></a></li>
                        <li class="nav-item"><a href="' . site_url('Skripsi_perubahan_judul') . '" class="nav-link ' . (strpos($current_url, 'Skripsi_perubahan_judul') !== false ? 'active' : '') . '"><i class="far fa-circle nav-icon"></i><p>Perubahan Judul</p></a></li>
                        <li class="nav-item"><a href="' . site_url('Pendaftaran_jadwal_skripsi') . '" class="nav-link ' . (strpos($current_url, 'Pendaftaran_jadwal_skripsi') !== false ? 'active' : '') . '"><i class="far fa-circle nav-icon"></i><p>Pendaftaran Sidang</p></a></li>
                        <li class="nav-item"><a href="' . site_url('Pendaftaran_sidang_skripsi') . '" class="nav-link ' . (strpos($current_url, 'Pendaftaran_sidang_skripsi') !== false ? 'active' : '') . '"><i class="far fa-circle nav-icon"></i><p>Pendaftaran Penguji</p></a></li>
                        <li class="nav-item"><a href="' . site_url('Skripsi_catatan_revisi') . '" class="nav-link ' . (strpos($current_url, 'Skripsi_catatan_revisi') !== false ? 'active' : '') . '"><i class="far fa-circle nav-icon"></i><p>Jadwal Sidang</p></a></li>
                        <li class="nav-item"><a href="' . site_url('Bimbingan_pasca_skripsi_mahasiswa') . '" class="nav-link ' . (strpos($current_url, 'Bimbingan_pasca_skripsi_mahasiswa') !== false ? 'active' : '') . '"><i class="far fa-circle nav-icon"></i><p>Bimbingan Pasca Sidang</p></a></li>
                        <li class="nav-item"><a href="' . site_url('Skripsi_klaim_nilai') . '" class="nav-link ' . (strpos($current_url, 'Skripsi_klaim_nilai') !== false ? 'active' : '') . '"><i class="far fa-circle nav-icon"></i><p>Klaim Nilai</p></a></li>
                    </ul>
                </li>';
        }
        $menu .= '
                <li class="nav-item has-treeview ' . (strpos($current_url, 'Manajemen_berkas') !== false || strpos($current_url, 'Permintaan_surat') !== false || strpos($current_url, 'Tagihan_berkas') !== false ? 'menu-open' : '') . '">
                    <a href="#" class="nav-link ' . (strpos($current_url, 'Manajemen_berkas') !== false || strpos($current_url, 'Permintaan_surat') !== false || strpos($current_url, 'Tagihan_berkas') !== false ? 'active' : '') . '"><i class="nav-icon fas fa-file-alt"></i><p>Dokumen & Surat <i class="fas fa-angle-left right"></i></p></a>
                    <ul class="nav nav-treeview">
                        <li class="nav-item"><a href="' . site_url('Manajemen_berkas') . '" class="nav-link ' . (strpos($current_url, 'Manajemen_berkas') !== false ? 'active' : '') . '"><i class="far fa-circle nav-icon"></i><p>Manajemen Berkas</p></a></li>
                        <li class="nav-item"><a href="' . site_url('Permintaan_surat') . '" class="nav-link ' . (strpos($current_url, 'Permintaan_surat') !== false ? 'active' : '') . '"><i class="far fa-circle nav-icon"></i><p>Permintaan Surat</p></a></li>
                        <li class="nav-item"><a href="' . site_url('Tagihan_berkas') . '" class="nav-link ' . (strpos($current_url, 'Tagihan_berkas') !== false ? 'active' : '') . '"><i class="far fa-circle nav-icon"></i><p>Tagihan Berkas</p></a></li>
                    </ul>
                        </li>
                <li class="nav-item has-treeview ' . (strpos($current_url, 'Jadwal') !== false || strpos($current_url, 'Pkl_revisi_mahasiswa') !== false ? 'menu-open' : '') . '">
                    <a href="#" class="nav-link ' . (strpos($current_url, 'Jadwal') !== false || strpos($current_url, 'Pkl_revisi_mahasiswa') !== false ? 'active' : '') . '"><i class="nav-icon fas fa-calendar-alt"></i><p>Jadwal <i class="fas fa-angle-left right"></i></p></a>
                    <ul class="nav nav-treeview">
                        <li class="nav-item"><a href="' . site_url('Jadwal/jadwal_kuliah') . '" class="nav-link ' . (strpos($current_url, 'jadwal_kuliah') !== false ? 'active' : '') . '"><i class="far fa-circle nav-icon"></i><p>Jadwal Kuliah</p></a></li>
                        <li class="nav-item"><a href="' . site_url('Jadwal/jadwal_uts') . '" class="nav-link ' . (strpos($current_url, 'jadwal_uts') !== false ? 'active' : '') . '"><i class="far fa-circle nav-icon"></i><p>Jadwal UTS</p></a></li>
                        <li class="nav-item"><a href="' . site_url('Jadwal/jadwal_uas') . '" class="nav-link ' . (strpos($current_url, 'jadwal_uas') !== false ? 'active' : '') . '"><i class="far fa-circle nav-icon"></i><p>Jadwal UAS</p></a></li>';
        if ($is_registered_pkl) {
            $menu .= '<li class="nav-item"><a href="' . site_url('Pkl_revisi_mahasiswa') . '" class="nav-link ' . (strpos($current_url, 'Pkl_revisi_mahasiswa') !== false ? 'active' : '') . '"><i class="far fa-circle nav-icon"></i><p>Jadwal Sidang PKL</p></a></li>';
        }
        $menu .= '
                    </ul>
                </li>
                <li class="nav-item"><a href="' . site_url('Absensi/kegiatan_saya') . '" class="nav-link ' . (strpos($current_url, 'Absensi') !== false && strpos($current_url, 'Tanda_terima') === false ? 'active' : '') . '"><i class="nav-icon fas fa-qrcode"></i><p>Absensi Kegiatan</p></a></li>
                <li class="nav-item"><a href="' . site_url('Tanda_terima_mahasiswa') . '" class="nav-link ' . (strpos($current_url, 'Tanda_terima_mahasiswa') !== false ? 'active' : '') . '"><i class="nav-icon fas fa-box-open"></i><p>Tanda Terima</p></a></li>';
        if ($has_skpi) {
            $menu .= '
                <li class="nav-item"><a href="' . site_url('Skpi_mahasiswa') . '" class="nav-link ' . (strpos($current_url, 'Skpi_mahasiswa') !== false ? 'active' : '') . '"><i class="nav-icon fas fa-certificate"></i><p>SKPI</p></a></li>';
        }
        if ($has_pkm) {
            $menu .= '
                <li class="nav-item has-treeview ' . (strpos($current_url, 'Pkm_mahasiswa') !== false || strpos($current_url, 'Bimbingan_pkm_mahasiswa') !== false || strpos($current_url, 'Pkm_perubahan_judul') !== false ? 'menu-open' : '') . '">
                    <a href="#" class="nav-link ' . (strpos($current_url, 'Pkm_mahasiswa') !== false || strpos($current_url, 'Bimbingan_pkm_mahasiswa') !== false || strpos($current_url, 'Pkm_perubahan_judul') !== false ? 'active' : '') . '"><i class="nav-icon fas fa-lightbulb"></i><p>PKM <i class="fas fa-angle-left right"></i></p></a>
                    <ul class="nav nav-treeview">
                        <li class="nav-item"><a href="' . site_url('Pkm_mahasiswa') . '" class="nav-link ' . (strpos($current_url, 'Pkm_mahasiswa') !== false ? 'active' : '') . '"><i class="far fa-circle nav-icon"></i><p>Pengajuan PKM</p></a></li>
                        <li class="nav-item"><a href="' . site_url('Bimbingan_pkm_mahasiswa') . '" class="nav-link ' . (strpos($current_url, 'Bimbingan_pkm_mahasiswa') !== false ? 'active' : '') . '"><i class="far fa-circle nav-icon"></i><p>Bimbingan PKM</p></a></li>
                    </ul>
                </li>';
        }
        $menu .= '
                <li class="nav-item"><a href="' . site_url('Profile') . '" class="nav-link ' . (strpos($current_url, 'Profile') !== false ? 'active' : '') . '"><i class="nav-icon fas fa-user-cog"></i><p>Profil</p></a></li>
                <li class="nav-item"><a href="' . site_url('Auth/logout') . '" class="nav-link"><i class="nav-icon fas fa-sign-out-alt"></i><p>Logout</p></a></li>
            </ul>
        </nav>';
        return $menu;
    }
}

if (!function_exists('get_tendik_menu')) {
    function get_tendik_menu() {
        $current_url = current_url();
        $menu = '
        <nav class="mt-2">
            <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu" data-accordion="false">
                <li class="nav-item">
                    <a href="' . site_url('Halaman_utama') . '" class="nav-link ' . (strpos($current_url, 'Halaman_utama') !== false ? 'active' : '') . '">
                        <i class="nav-icon fas fa-home"></i>
                        <p>Halaman Utama</p>
                    </a>
                </li>
                <li class="nav-item has-treeview ' . (strpos($current_url, 'Jadwal_pengawas') !== false ? 'menu-open' : '') . '">
                    <a href="#" class="nav-link ' . (strpos($current_url, 'Jadwal_pengawas') !== false ? 'active' : '') . '">
                        <i class="nav-icon fas fa-calendar-alt"></i>
                        <p>
                            Jadwal Pengawas
                            <i class="fas fa-angle-left right"></i>
                        </p>
                    </a>
                    <ul class="nav nav-treeview">
                        <li class="nav-item">
                            <a href="' . site_url('Jadwal_pengawas/uts') . '" class="nav-link ' . (strpos($current_url, 'Jadwal_pengawas/uts') !== false || (strpos($current_url, 'Jadwal_pengawas') !== false && strpos($current_url, 'uas') === false) ? 'active' : '') . '">
                                <i class="far fa-circle nav-icon"></i>
                                <p>Jadwal Pengawas UTS</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="' . site_url('Jadwal_pengawas/uas') . '" class="nav-link ' . (strpos($current_url, 'Jadwal_pengawas/uas') !== false ? 'active' : '') . '">
                                <i class="far fa-circle nav-icon"></i>
                                <p>Jadwal Pengawas UAS</p>
                            </a>
                        </li>
                    </ul>
                </li>
                <li class="nav-item">
                    <a href="' . site_url('Profile') . '" class="nav-link ' . (strpos($current_url, 'Profile') !== false ? 'active' : '') . '">
                        <i class="nav-icon fas fa-user-cog"></i>
                        <p>Profil</p>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="' . site_url('Auth/logout') . '" class="nav-link">
                        <i class="nav-icon fas fa-sign-out-alt"></i>
                        <p>Logout</p>
                    </a>
                </li>
            </ul>
        </nav>';
        return $menu;
    }
}