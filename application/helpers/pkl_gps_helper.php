<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * PKL GPS Helper
 * Helper khusus untuk perhitungan GPS dan validasi radius PKL
 */

if (!function_exists('calculate_distance_pkl')) {
    /**
     * Hitung jarak antara koordinat tempat PKL dengan koordinat absensi
     * Menggunakan Haversine formula
     * 
     * @param float $lat_tempat Latitude tempat PKL
     * @param float $lon_tempat Longitude tempat PKL
     * @param float $lat_absen Latitude saat absen
     * @param float $lon_absen Longitude saat absen
     * @return float Jarak dalam meter
     */
    function calculate_distance_pkl($lat_tempat, $lon_tempat, $lat_absen, $lon_absen) {
        // Load helper GPS yang sudah ada
        if (!function_exists('calculate_distance_gps')) {
            $CI =& get_instance();
            $CI->load->helper('gps');
        }
        
        return calculate_distance_gps($lat_tempat, $lon_tempat, $lat_absen, $lon_absen);
    }
}

if (!function_exists('check_radius_pkl')) {
    /**
     * Cek apakah koordinat absensi berada dalam radius tempat PKL
     * 
     * @param float $lat_absen Latitude saat absen
     * @param float $lon_absen Longitude saat absen
     * @param int $id_pkl_tempat ID tempat PKL
     * @param int $id_tahun_akademik ID tahun akademik (optional)
     * @return array Array dengan keys: 'dalam_radius' (bool), 'jarak_meter' (float), 'radius_setting' (int)
     */
    function check_radius_pkl($lat_absen, $lon_absen, $id_pkl_tempat, $id_tahun_akademik = null) {
        $CI =& get_instance();
        $CI->load->model('Pkl_tempat_model');
        $CI->load->model('Pkl_setting_radius_model');
        
        // Ambil data tempat PKL
        $tempat_pkl = $CI->Pkl_tempat_model->get_by_id($id_pkl_tempat);
        
        if (!$tempat_pkl || !$tempat_pkl->latitude || !$tempat_pkl->longitude) {
            return array(
                'dalam_radius' => false,
                'jarak_meter' => null,
                'radius_setting' => null,
                'error' => 'Koordinat tempat PKL belum diisi'
            );
        }
        
        // Ambil setting radius
        if (!$id_tahun_akademik) {
            $CI->load->model('Tahun_akademik_model');
            $tahun_aktif = $CI->Tahun_akademik_model->get_aktif();
            if ($tahun_aktif) {
                $id_tahun_akademik = $tahun_aktif->id;
            }
        }
        
        $radius_setting = $CI->Pkl_setting_radius_model->get_radius($id_tahun_akademik);
        
        // Hitung jarak
        $jarak_meter = calculate_distance_pkl(
            $tempat_pkl->latitude,
            $tempat_pkl->longitude,
            $lat_absen,
            $lon_absen
        );
        
        // Cek apakah dalam radius
        $dalam_radius = ($jarak_meter <= $radius_setting);
        
        return array(
            'dalam_radius' => $dalam_radius,
            'jarak_meter' => round($jarak_meter, 2),
            'radius_setting' => $radius_setting,
            'status_radius' => $dalam_radius ? 'dalam_radius' : 'di_luar_radius'
        );
    }
}

if (!function_exists('get_radius_setting')) {
    /**
     * Ambil setting radius untuk tahun akademik
     * 
     * @param int $id_tahun_akademik ID tahun akademik (optional)
     * @return int Radius dalam meter
     */
    function get_radius_setting($id_tahun_akademik = null) {
        $CI =& get_instance();
        $CI->load->model('Pkl_setting_radius_model');
        return $CI->Pkl_setting_radius_model->get_radius($id_tahun_akademik);
    }
}

if (!function_exists('format_jarak_pkl')) {
    /**
     * Format jarak untuk ditampilkan
     * 
     * @param float $jarak_meter Jarak dalam meter
     * @return string Jarak yang sudah diformat (contoh: "50.5 m" atau "1.2 km")
     */
    function format_jarak_pkl($jarak_meter) {
        if ($jarak_meter < 1000) {
            return number_format($jarak_meter, 1) . ' m';
        } else {
            return number_format($jarak_meter / 1000, 2) . ' km';
        }
    }
}

