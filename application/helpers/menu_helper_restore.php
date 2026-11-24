<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Menu Helper - RESTORED VERSION
 * Helper untuk generate sidebar menu berdasarkan role
 * 
 * File ini adalah versi restored dari menu_helper.php yang rusak
 * Mohon rename file ini menjadi menu_helper.php untuk menggunakannya
 */

if (!function_exists('generate_sidebar_menu')) {
    function generate_sidebar_menu($role) {
        $menu = '';
        switch ($role) {
            case 'admin':
                $menu = get_admin_menu();
                break;
            case 'sekprodi':
                $menu = get_sekprodi_menu();
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

// File ini hanya sebagai placeholder - file lengkap akan dibuat setelah ini




