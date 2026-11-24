<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Validasi absensi ujian (UTS/UAS).
 * Tidak ada batas window waktu — absensi diizinkan selama jadwal & barcode valid.
 *
 * @param object $jadwal object jadwal ujian
 * @return array ['ok' => bool, 'message' => string|null, 'start' => int, 'end' => int]
 */
if (!function_exists('absensi_ujian_window_check')) {
    function absensi_ujian_window_check($jadwal, $buffer_before = 30, $buffer_after = 120) {
        unset($buffer_before, $buffer_after);

        if (!$jadwal) {
            return array(
                'ok' => false,
                'message' => 'Data jadwal ujian tidak ditemukan.',
                'start' => 0,
                'end' => 0,
            );
        }

        if (empty($jadwal->qr_code_token)) {
            return array(
                'ok' => false,
                'message' => 'Barcode absensi untuk jadwal ini belum tersedia.',
                'start' => 0,
                'end' => 0,
            );
        }

        return array('ok' => true, 'message' => null, 'start' => 0, 'end' => 0);
    }
}

if (!function_exists('absensi_ujian_can_absen_now')) {
    function absensi_ujian_can_absen_now($jadwal) {
        if (!$jadwal || empty($jadwal->qr_code_token)) {
            return false;
        }
        return true;
    }
}
