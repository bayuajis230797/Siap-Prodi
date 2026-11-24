<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Arsip Berkas Model
 * Mengelola arsip berkas masuk dan keluar
 */
class Arsip_berkas_model extends CI_Model {

    public function __construct() {
        parent::__construct();
        $this->load->database();
    }

    /**
     * Get all arsip dengan filter
     */
    public function get_all($jenis = null, $limit = null, $offset = null, $tanggal_dari = null, $tanggal_sampai = null) {
        $this->db->select('a.*, u.username as created_by_username');
        $this->db->from('arsip_berkas a');
        $this->db->join('users u', 'u.id = a.created_by', 'left');
        
        if ($jenis) {
            $this->db->where('a.jenis', $jenis);
        }
        
        if ($tanggal_dari) {
            $this->db->where('a.tanggal_surat >=', $tanggal_dari);
        }
        
        if ($tanggal_sampai) {
            $this->db->where('a.tanggal_surat <=', $tanggal_sampai);
        }
        
        $this->db->order_by('a.tanggal_surat', 'DESC');
        $this->db->order_by('a.created_at', 'DESC');
        
        if ($limit !== null && $offset !== null) {
            $this->db->limit($limit, $offset);
        }
        
        $query = $this->db->get();
        return $query->result();
    }

    /**
     * Count all arsip dengan filter
     */
    public function count_all($jenis = null, $tanggal_dari = null, $tanggal_sampai = null) {
        $this->db->from('arsip_berkas');
        
        if ($jenis) {
            $this->db->where('jenis', $jenis);
        }
        
        if ($tanggal_dari) {
            $this->db->where('tanggal_surat >=', $tanggal_dari);
        }
        
        if ($tanggal_sampai) {
            $this->db->where('tanggal_surat <=', $tanggal_sampai);
        }
        
        return $this->db->count_all_results();
    }

    /**
     * Get arsip by ID
     */
    public function get_by_id($id) {
        $this->db->select('a.*, u.username as created_by_username');
        $this->db->from('arsip_berkas a');
        $this->db->join('users u', 'u.id = a.created_by', 'left');
        $this->db->where('a.id', $id);
        
        $query = $this->db->get();
        return $query->row();
    }

    /**
     * Create new arsip
     */
    public function create($data) {
        $this->db->insert('arsip_berkas', $data);
        return $this->db->insert_id();
    }

    /**
     * Update arsip
     */
    public function update($id, $data) {
        $this->db->where('id', $id);
        return $this->db->update('arsip_berkas', $data);
    }

    /**
     * Delete arsip
     */
    public function delete($id) {
        // Hapus file fisik hanya jika bukan link (untuk backward compatibility)
        $arsip = $this->get_by_id($id);
        if ($arsip && !empty($arsip->file_path)) {
            // Cek apakah file_path adalah link (dimulai dengan http)
            if (strpos($arsip->file_path, 'http') === false) {
                // Ini adalah file lokal, hapus file fisik
                if (file_exists($arsip->file_path)) {
                    unlink($arsip->file_path);
                }
            }
            // Jika file_path adalah link, tidak perlu hapus file fisik
        }
        
        $this->db->where('id', $id);
        return $this->db->delete('arsip_berkas');
    }
}

