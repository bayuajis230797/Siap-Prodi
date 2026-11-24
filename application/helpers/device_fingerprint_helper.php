<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Device Fingerprint Helper
 * Helper functions untuk device fingerprinting
 */

if (!function_exists('parse_device_info')) {
    /**
     * Parse device info dari JSON
     * @param string $device_info_json JSON string device info
     * @return array|null Parsed device info atau null jika gagal
     */
    function parse_device_info($device_info_json) {
        if (!$device_info_json) {
            return null;
        }
        
        $info = json_decode($device_info_json, true);
        if (!$info || !is_array($info)) {
            return null;
        }
        
        // Parse user agent untuk browser dan OS
        $user_agent = isset($info['userAgent']) ? $info['userAgent'] : '';
        $browser = 'Unknown';
        $os = 'Unknown';
        $device_type = 'Desktop';
        
        // Detect browser
        if (stripos($user_agent, 'Chrome') !== false && stripos($user_agent, 'Edg') === false) {
            $browser = 'Chrome';
        } elseif (stripos($user_agent, 'Firefox') !== false) {
            $browser = 'Firefox';
        } elseif (stripos($user_agent, 'Safari') !== false && stripos($user_agent, 'Chrome') === false) {
            $browser = 'Safari';
        } elseif (stripos($user_agent, 'Edg') !== false) {
            $browser = 'Edge';
        } elseif (stripos($user_agent, 'Opera') !== false || stripos($user_agent, 'OPR') !== false) {
            $browser = 'Opera';
        }
        
        // Detect OS dan Device Type
        if (stripos($user_agent, 'Android') !== false) {
            $os = 'Android';
            $device_type = 'Mobile';
        } elseif (stripos($user_agent, 'iPhone') !== false) {
            $os = 'iOS';
            $device_type = 'Mobile';
        } elseif (stripos($user_agent, 'iPad') !== false) {
            $os = 'iOS';
            $device_type = 'Tablet';
        } elseif (stripos($user_agent, 'Windows') !== false) {
            $os = 'Windows';
            $device_type = 'Desktop';
        } elseif (stripos($user_agent, 'Mac') !== false) {
            $os = 'macOS';
            $device_type = 'Desktop';
        } elseif (stripos($user_agent, 'Linux') !== false) {
            $os = 'Linux';
            $device_type = 'Desktop';
        }
        
        // Get screen resolution
        $screen = 'Unknown';
        if (isset($info['screenWidth']) && isset($info['screenHeight'])) {
            $screen = $info['screenWidth'] . 'x' . $info['screenHeight'];
        }
        
        return array(
            'browser' => $browser,
            'os' => $os,
            'device_type' => $device_type,
            'screen' => $screen,
            'timezone' => isset($info['timezone']) ? $info['timezone'] : 'Unknown',
            'platform' => isset($info['platform']) ? $info['platform'] : 'Unknown',
            'language' => isset($info['language']) ? $info['language'] : 'Unknown',
            'hardware_concurrency' => isset($info['hardwareConcurrency']) ? $info['hardwareConcurrency'] : 'Unknown',
            'device_memory' => isset($info['deviceMemory']) ? $info['deviceMemory'] : 'Unknown',
            'raw' => $info
        );
    }
}

if (!function_exists('format_device_info_display')) {
    /**
     * Format device info untuk ditampilkan
     * @param array $device_info Parsed device info
     * @return string Formatted string
     */
    function format_device_info_display($device_info) {
        if (!$device_info) {
            return 'Tidak tersedia';
        }
        
        $parts = array();
        if (isset($device_info['browser']) && $device_info['browser'] !== 'Unknown') {
            $parts[] = $device_info['browser'];
        }
        if (isset($device_info['os']) && $device_info['os'] !== 'Unknown') {
            $parts[] = $device_info['os'];
        }
        if (isset($device_info['device_type'])) {
            $parts[] = '(' . $device_info['device_type'] . ')';
        }
        
        return implode(' ', $parts);
    }
}

if (!function_exists('compare_device_fingerprints')) {
    /**
     * Bandingkan dua device fingerprint
     * @param string $fp1 Fingerprint 1
     * @param string $fp2 Fingerprint 2
     * @return bool True jika sama, false jika berbeda
     */
    function compare_device_fingerprints($fp1, $fp2) {
        if (!$fp1 || !$fp2) {
            return false;
        }
        
        return $fp1 === $fp2;
    }
}





