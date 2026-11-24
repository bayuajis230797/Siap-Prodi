<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Pkl_setting_radius_model
 * Model untuk setting radius absensi PKL
 */
class Pkl_setting_radius_model extends CI_Model {

    public function __construct() {
        parent::__construct();
        $this->load->database();
    }

    /**
     * Create atau update setting radius
     * @param array $data Data setting radius
     * @return int ID setting yang dibuat/diupdate
     */
    public function create($data) {
        // Cek apakah sudah ada setting untuk tahun akademik ini
        if (isset($data['id_tahun_akademik']) && $data['id_tahun_akademik']) {
            $existing = $this->get_by_tahun_akademik($data['id_tahun_akademik']);
            if ($existing) {
                // Update jika sudah ada
                return $this->update($existing->id, $data);
            }
        }
        
        $this->db->insert('pkl_setting_radius', $data);
        return $this->db->insert_id();
    }

    /**
     * Update setting radius
     * @param int $id ID setting
     * @param array $data Data yang akan diupdate
     * @return int Jumlah baris yang terpengaruh
     */
    public function update($id, $data) {
        $this->db->where('id', $id);
        $this->db->update('pkl_setting_radius', $data);
        return $this->db->affected_rows();
    }

    /**
     * Get setting by ID
     * @param int $id ID setting
     * @return object Data setting
     */
    public function get_by_id($id) {
        $this->db->select('psr.*, ta.tahun_akademik, ta.semester, u.username as created_by_username');
        $this->db->from('pkl_setting_radius psr');
        $this->db->join('tahun_akademik ta', 'ta.id = psr.id_tahun_akademik', 'left');
        $this->db->join('users u', 'u.id = psr.created_by', 'left');
        $this->db->where('psr.id', $id);
        return $this->db->get()->row();
    }

    /**
     * Get setting by tahun akademik
     * @param int $id_tahun_akademik ID tahun akademik
     * @return object Data setting atau null
     */
    public function get_by_tahun_akademik($id_tahun_akademik) {
        $this->db->where('id_tahun_akademik', $id_tahun_akademik);
        return $this->db->get('pkl_setting_radius')->row();
    }

    /**
     * Get setting untuk tahun akademik aktif
     * @return object Data setting atau null
     */
    public function get_aktif() {
        $this->load->model('Tahun_akademik_model');
        $tahun_aktif = $this->Tahun_akademik_model->get_aktif();
        
        if (!$tahun_aktif) {
            return null;
        }
        
        return $this->get_by_tahun_akademik($tahun_aktif->id);
    }

    /**
     * Get radius untuk tahun akademik (dengan fallback ke default)
     * @param int $id_tahun_akademik ID tahun akademik (optional, jika null ambil tahun aktif)
     * @return int Radius dalam meter (default: 50)
     */
    public function get_radius($id_tahun_akademik = null) {
        if (!$id_tahun_akademik) {
            $this->load->model('Tahun_akademik_model');
            $tahun_aktif = $this->Tahun_akademik_model->get_aktif();
            if ($tahun_aktif) {
                $id_tahun_akademik = $tahun_aktif->id;
            }
        }
        
        if ($id_tahun_akademik) {
            $setting = $this->get_by_tahun_akademik($id_tahun_akademik);
            if ($setting) {
                return (int)$setting->radius_meter;
            }
        }
        
        // Fallback ke default
        return 50;
    }

    /**
     * Get all settings
     * @return array List semua setting
     */
    public function get_all() {
        $this->db->select('psr.*, ta.tahun_akademik, ta.semester, u.username as created_by_username');
        $this->db->from('pkl_setting_radius psr');
        $this->db->join('tahun_akademik ta', 'ta.id = psr.id_tahun_akademik', 'left');
        $this->db->join('users u', 'u.id = psr.created_by', 'left');
        $this->db->order_by('psr.id_tahun_akademik', 'DESC');
        return $this->db->get()->result();
    }

    /**
     * Delete setting
     * @param int $id ID setting
     * @return int Jumlah baris yang terpengaruh
     */
    public function delete($id) {
        $this->db->where('id', $id);
        $this->db->delete('pkl_setting_radius');
        return $this->db->affected_rows();
    }
}

