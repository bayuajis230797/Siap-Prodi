<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * GPS Helper
 * Helper untuk perhitungan jarak GPS menggunakan Haversine formula
 */

if (!function_exists('calculate_distance_gps')) {
    /**
     * Hitung jarak antara dua koordinat GPS (Haversine formula)
     * @param float $lat1 Latitude titik 1
     * @param float $lon1 Longitude titik 1
     * @param float $lat2 Latitude titik 2
     * @param float $lon2 Longitude titik 2
     * @return float Jarak dalam meter
     */
    function calculate_distance_gps($lat1, $lon1, $lat2, $lon2) {
        $earth_radius = 6371000; // Radius bumi dalam meter
        
        $dLat = deg2rad($lat2 - $lat1);
        $dLon = deg2rad($lon2 - $lon1);
        
        $a = sin($dLat / 2) * sin($dLat / 2) +
             cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
             sin($dLon / 2) * sin($dLon / 2);
        
        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));
        
        return $earth_radius * $c;
    }
}

if (!function_exists('validate_gps_location')) {
    /**
     * Validasi apakah koordinat berada dalam radius yang diizinkan
     * @param float $target_lat Latitude target
     * @param float $target_lon Longitude target
     * @param float $current_lat Latitude saat ini
     * @param float $current_lon Longitude saat ini
     * @param int $radius_meter Radius maksimal dalam meter
     * @return bool True jika dalam radius, False jika di luar
     */
    function validate_gps_location($target_lat, $target_lon, $current_lat, $current_lon, $radius_meter = 20) {
        if (!$target_lat || !$target_lon || !$current_lat || !$current_lon) {
            return false;
        }
        
        $jarak = calculate_distance_gps($target_lat, $target_lon, $current_lat, $current_lon);
        return $jarak <= $radius_meter;
    }
}






