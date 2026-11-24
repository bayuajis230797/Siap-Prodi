<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Honor Setup Model
 * Mengelola setup besaran honor
 */
class Honor_setup_model extends CI_Model {

    public function __construct() {
        parent::__construct();
        $this->load->database();
    }

    /**
     * Get semua setup honor
     */
    public function get_all($id_tahun_akademik = null) {
        $this->db->from('honor_setup');
        
        if ($id_tahun_akademik) {
            $this->db->where('id_tahun_akademik', $id_tahun_akademik);
        } else {
            $this->db->where('id_tahun_akademik IS NULL');
        }
        
        $this->db->order_by('item_honor', 'ASC');
        
        return $this->db->get()->result();
    }

    /**
     * Get setup honor by item
     */
    public function get_by_item($item_honor, $id_tahun_akademik = null) {
        $this->db->from('honor_setup');
        $this->db->where('item_honor', $item_honor);
        
        if ($id_tahun_akademik) {
            $this->db->where('id_tahun_akademik', $id_tahun_akademik);
        } else {
            $this->db->where('id_tahun_akademik IS NULL');
        }
        
        $this->db->order_by('id_tahun_akademik', 'DESC');
        $this->db->limit(1);
        
        return $this->db->get()->row();
    }

    /**
     * Get atau create setup honor
     */
    public function get_or_create($item_honor, $id_tahun_akademik = null) {
        $setup = $this->get_by_item($item_honor, $id_tahun_akademik);
        
        if (!$setup) {
            // Coba ambil dari setup global (tanpa tahun akademik)
            $setup = $this->get_by_item($item_honor, null);
            
            if (!$setup) {
                // Buat default jika belum ada
                $data = array(
                    'item_honor' => $item_honor,
                    'besaran' => 0,
                    'id_tahun_akademik' => $id_tahun_akademik
                );
                $this->db->insert('honor_setup', $data);
                $setup = $this->get_by_item($item_honor, $id_tahun_akademik);
            }
        }
        
        return $setup;
    }

    /**
     * Update atau create setup honor
     */
    public function save($item_honor, $besaran, $keterangan = null, $id_tahun_akademik = null) {
        // Cek apakah sudah ada dengan kombinasi item_honor + id_tahun_akademik
        $this->db->where('item_honor', $item_honor);
        if ($id_tahun_akademik) {
            $this->db->where('id_tahun_akademik', $id_tahun_akademik);
        } else {
            $this->db->where('id_tahun_akademik IS NULL');
        }
        $setup = $this->db->get('honor_setup')->row();
        
        $data = array(
            'item_honor' => $item_honor,
            'besaran' => $besaran,
            'keterangan' => $keterangan,
            'id_tahun_akademik' => $id_tahun_akademik
        );
        
        if ($setup) {
            // Update
            $this->db->where('id', $setup->id);
            $this->db->update('honor_setup', $data);
            return $setup->id;
        } else {
            // Insert
            $this->db->insert('honor_setup', $data);
            return $this->db->insert_id();
        }
    }

    /**
     * Get besaran honor
     */
    public function get_besaran($item_honor, $id_tahun_akademik = null) {
        $setup = $this->get_or_create($item_honor, $id_tahun_akademik);
        return $setup ? $setup->besaran : 0;
    }
}

