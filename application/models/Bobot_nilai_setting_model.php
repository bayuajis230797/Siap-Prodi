<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Bobot Nilai Setting Model
 * Mengelola setting bobot nilai akhir untuk PKL dan Skripsi
 */
class Bobot_nilai_setting_model extends CI_Model {

    public function __construct() {
        parent::__construct();
        $this->load->database();
    }

    /**
     * Get semua bobot setting berdasarkan tipe
     * @param string $tipe pkl atau skripsi
     * @return array
     */
    public function get_by_tipe($tipe) {
        $this->db->where('tipe', $tipe);
        $this->db->where('is_aktif', 1);
        $this->db->order_by('komponen', 'ASC');
        $query = $this->db->get('bobot_nilai_setting');
        return $query->result();
    }

    /**
     * Get semua bobot setting
     * @return array
     */
    public function get_all() {
        $this->db->order_by('tipe', 'ASC');
        $this->db->order_by('komponen', 'ASC');
        $query = $this->db->get('bobot_nilai_setting');
        return $query->result();
    }

    /**
     * Get bobot setting by ID
     * @param int $id
     * @return object|null
     */
    public function get_by_id($id) {
        $this->db->where('id', $id);
        $query = $this->db->get('bobot_nilai_setting');
        return $query->row();
    }

    /**
     * Get bobot setting by tipe dan komponen
     * @param string $tipe
     * @param string $komponen
     * @return object|null
     */
    public function get_by_tipe_komponen($tipe, $komponen) {
        $this->db->where('tipe', $tipe);
        $this->db->where('komponen', $komponen);
        $this->db->where('is_aktif', 1);
        $query = $this->db->get('bobot_nilai_setting');
        return $query->row();
    }

    /**
     * Get bobot untuk perhitungan nilai akhir
     * @param string $tipe pkl atau skripsi
     * @return array Array dengan key komponen dan value bobot
     */
    public function get_bobot_perhitungan($tipe) {
        $settings = $this->get_by_tipe($tipe);
        $bobot = array();
        
        foreach ($settings as $setting) {
            $bobot[$setting->komponen] = (float)$setting->bobot;
        }
        
        return $bobot;
    }

    /**
     * Hitung nilai akhir PKL berdasarkan bobot setting
     * @param float $p1
     * @param float $p2
     * @param float $p3
     * @param float $p4
     * @param float $p5
     * @param float $p6
     * @return float
     */
    public function hitung_nilai_akhir_pkl($p1, $p2, $p3, $p4, $p5, $p6) {
        // Default: rata-rata P1-P6
        $total = $p1 + $p2 + $p3 + $p4 + $p5 + $p6;
        return round($total / 6, 2);
    }

    /**
     * Hitung nilai akhir Skripsi berdasarkan bobot setting
     * @param float $nilai_bimbingan
     * @param float $nilai_sidang
     * @return float
     */
    public function hitung_nilai_akhir_skripsi($nilai_bimbingan, $nilai_sidang) {
        $bobot = $this->get_bobot_perhitungan('skripsi');
        
        $bobot_bimbingan = isset($bobot['bimbingan']) ? $bobot['bimbingan'] : 40.0;
        $bobot_sidang = isset($bobot['sidang']) ? $bobot['sidang'] : 60.0;
        
        $nilai_akhir = (($bobot_bimbingan / 100) * $nilai_bimbingan) + (($bobot_sidang / 100) * $nilai_sidang);
        return round($nilai_akhir, 2);
    }

    /**
     * Hitung nilai akhir Proposal TA berdasarkan bobot setting (tipe 'proposal')
     * @param float $nilai_bimbingan Nilai dari pembimbing
     * @param float $nilai_sidang Rata-rata nilai Penguji 1 dan 2
     * @return float
     */
    public function hitung_nilai_akhir_proposal($nilai_bimbingan, $nilai_sidang) {
        $bobot = $this->get_bobot_perhitungan('proposal');

        $bobot_bimbingan = isset($bobot['bimbingan']) ? $bobot['bimbingan'] : 40.0;
        $bobot_sidang = isset($bobot['sidang']) ? $bobot['sidang'] : 60.0;

        $nilai_akhir = (($bobot_bimbingan / 100) * $nilai_bimbingan) + (($bobot_sidang / 100) * $nilai_sidang);
        return round($nilai_akhir, 2);
    }

    /**
     * Create bobot setting
     * @param array $data
     * @return int Insert ID
     */
    public function create($data) {
        $this->db->insert('bobot_nilai_setting', $data);
        return $this->db->insert_id();
    }

    /**
     * Update bobot setting
     * @param int $id
     * @param array $data
     * @return bool
     */
    public function update($id, $data) {
        $this->db->where('id', $id);
        return $this->db->update('bobot_nilai_setting', $data);
    }

    /**
     * Delete bobot setting
     * @param int $id
     * @return bool
     */
    public function delete($id) {
        $this->db->where('id', $id);
        return $this->db->delete('bobot_nilai_setting');
    }

    /**
     * Update status aktif
     * @param int $id
     * @param int $is_aktif
     * @return bool
     */
    public function update_status($id, $is_aktif) {
        $this->db->where('id', $id);
        return $this->db->update('bobot_nilai_setting', array('is_aktif' => $is_aktif));
    }
}







