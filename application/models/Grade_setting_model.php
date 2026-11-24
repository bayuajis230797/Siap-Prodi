<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Grade Setting Model
 * Mengelola setting grade untuk PKL dan Skripsi
 */
class Grade_setting_model extends CI_Model {

    public function __construct() {
        parent::__construct();
        $this->load->database();
    }

    /**
     * Get semua grade setting berdasarkan tipe
     * @param string $tipe pkl atau skripsi
     * @return array
     */
    public function get_by_tipe($tipe, $include_inactive = false) {
        $this->db->where('tipe', $tipe);
        if (!$include_inactive) {
            $this->db->where('is_aktif', 1);
        }
        $this->db->order_by('nilai_min', 'DESC');
        $query = $this->db->get('grade_setting');
        return $query->result();
    }

    /**
     * Get semua grade setting
     * @param bool $include_inactive Include grade yang tidak aktif
     * @return array
     */
    public function get_all($include_inactive = false) {
        if (!$include_inactive) {
            $this->db->where('is_aktif', 1);
        }
        $this->db->order_by('tipe', 'ASC');
        $this->db->order_by('nilai_min', 'DESC');
        $query = $this->db->get('grade_setting');
        return $query->result();
    }

    /**
     * Get grade setting by ID
     * @param int $id
     * @return object|null
     */
    public function get_by_id($id) {
        $this->db->where('id', $id);
        $query = $this->db->get('grade_setting');
        return $query->row();
    }

    /**
     * Get grade setting by tipe dan grade
     * @param string $tipe
     * @param string $grade
     * @return object|null
     */
    public function get_by_tipe_grade($tipe, $grade, $include_inactive = false) {
        $this->db->where('tipe', $tipe);
        $this->db->where('grade', $grade);
        if (!$include_inactive) {
            $this->db->where('is_aktif', 1);
        }
        $query = $this->db->get('grade_setting');
        return $query->row();
    }

    /**
     * Konversi nilai angka ke grade berdasarkan tipe
     * @param float $nilai
     * @param string $tipe pkl atau skripsi
     * @return string|null
     */
    public function konversi_nilai_ke_grade($nilai, $tipe) {
        if ($nilai === null) {
            return null;
        }

        $grades = $this->get_by_tipe($tipe);
        
        if (empty($grades)) {
            // Fallback ke default jika tidak ada setting
            return $this->get_default_grade($nilai);
        }
        
        foreach ($grades as $grade) {
            if ($grade->nilai_max !== null) {
                if ($nilai >= $grade->nilai_min && $nilai <= $grade->nilai_max) {
                    return $grade->grade;
                }
            } else {
                // Untuk grade tertinggi, nilai_max bisa NULL
                if ($nilai >= $grade->nilai_min) {
                    return $grade->grade;
                }
            }
        }

        // Jika tidak ada yang cocok, ambil grade terendah dari setting
        if (!empty($grades)) {
            $lowest_grade = end($grades);
            return $lowest_grade->grade;
        }
        
        // Fallback ke default
        return $this->get_default_grade($nilai);
    }

    /**
     * Get default grade jika tidak ada setting (fallback)
     * @param float $nilai
     * @return string
     */
    private function get_default_grade($nilai) {
        if ($nilai >= 81) return 'A';
        if ($nilai >= 69) return 'B';
        if ($nilai >= 60) return 'C';
        if ($nilai >= 49) return 'D';
        return 'E';
    }

    /**
     * Create grade setting
     * @param array $data
     * @return int Insert ID
     */
    public function create($data) {
        $this->db->insert('grade_setting', $data);
        return $this->db->insert_id();
    }

    /**
     * Update grade setting
     * @param int $id
     * @param array $data
     * @return bool
     */
    public function update($id, $data) {
        $this->db->where('id', $id);
        return $this->db->update('grade_setting', $data);
    }

    /**
     * Delete grade setting
     * @param int $id
     * @return bool
     */
    public function delete($id) {
        $this->db->where('id', $id);
        return $this->db->delete('grade_setting');
    }

    /**
     * Update status aktif
     * @param int $id
     * @param int $is_aktif
     * @return bool
     */
    public function update_status($id, $is_aktif) {
        $this->db->where('id', $id);
        return $this->db->update('grade_setting', array('is_aktif' => $is_aktif));
    }
}

