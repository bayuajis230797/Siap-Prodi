<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class App_settings_model extends CI_Model {

    public function __construct() {
        parent::__construct();
        $this->load->database();
    }

    public function get_settings() {
        $this->db->where('is_aktif', 1);
        $this->db->order_by('id', 'DESC');
        $this->db->limit(1);
        $settings = $this->db->get('app_settings')->row();
        
        // Jika tidak ada, return default
        if (!$settings) {
            return (object) array(
                'id' => null,
                'nama_kampus' => 'Universitas',
                'nama_prodi' => 'Program Studi',
                'logo_kampus' => null,
                'kop_header' => null,
                'is_aktif' => 1
            );
        }
        
        return $settings;
    }

    public function get_by_id($id) {
        $this->db->where('id', $id);
        return $this->db->get('app_settings')->row();
    }

    public function update($id, $data) {
        $this->db->where('id', $id);
        $this->db->update('app_settings', $data);
        return $this->db->affected_rows();
    }

    public function create($data) {
        // Set semua setting lain menjadi tidak aktif
        $this->db->update('app_settings', array('is_aktif' => 0));
        
        // Buat setting baru
        $data['is_aktif'] = 1;
        $this->db->insert('app_settings', $data);
        return $this->db->insert_id();
    }

    public function upsert($data) {
        $settings = $this->get_settings();
        
        if ($settings && $settings->id) {
            // Update existing
            return $this->update($settings->id, $data);
        } else {
            // Create new
            return $this->create($data);
        }
    }
}





