<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Permintaan Surat Model
 * Mengelola data permintaan surat dari mahasiswa
 */
class Permintaan_surat_model extends CI_Model {

    public function __construct() {
        parent::__construct();
        $this->load->database();
    }

    /**
     * Get semua permintaan surat dengan pagination
     */
    public function get_all($limit = 10, $offset = 0, $filter_status = null) {
        $this->db->select('ps.*, m.nim, m.nama_mahasiswa, m.kelas');
        $this->db->from('permintaan_surat ps');
        $this->db->join('mahasiswa m', 'm.id = ps.id_mahasiswa', 'left');
        
        if ($filter_status) {
            $this->db->where('ps.status', $filter_status);
        }
        
        $this->db->order_by('ps.created_at', 'DESC');
        $this->db->limit($limit, $offset);
        
        $query = $this->db->get();
        return $query->result();
    }

    /**
     * Count semua permintaan surat
     */
    public function count_all($filter_status = null) {
        $this->db->from('permintaan_surat ps');
        
        if ($filter_status) {
            $this->db->where('ps.status', $filter_status);
        }
        
        return $this->db->count_all_results();
    }

    /**
     * Get permintaan surat by ID
     */
    public function get_by_id($id) {
        $this->db->select('ps.*, m.nim, m.nama_mahasiswa, m.kelas, m.angkatan');
        $this->db->from('permintaan_surat ps');
        $this->db->join('mahasiswa m', 'm.id = ps.id_mahasiswa', 'left');
        $this->db->where('ps.id', $id);
        
        $query = $this->db->get();
        return $query->row();
    }

    /**
     * Get permintaan surat by mahasiswa
     */
    public function get_by_mahasiswa($id_mahasiswa, $limit = 10, $offset = 0) {
        $this->db->select('ps.*');
        $this->db->from('permintaan_surat ps');
        $this->db->where('ps.id_mahasiswa', $id_mahasiswa);
        $this->db->order_by('ps.created_at', 'DESC');
        $this->db->limit($limit, $offset);
        
        $query = $this->db->get();
        return $query->result();
    }

    /**
     * Count permintaan surat by mahasiswa
     */
    public function count_by_mahasiswa($id_mahasiswa) {
        $this->db->from('permintaan_surat ps');
        $this->db->where('ps.id_mahasiswa', $id_mahasiswa);
        
        return $this->db->count_all_results();
    }

    /**
     * Create permintaan surat baru
     */
    public function create($data) {
        $this->db->insert('permintaan_surat', $data);
        return $this->db->insert_id();
    }

    /**
     * Update permintaan surat
     */
    public function update($id, $data) {
        $this->db->where('id', $id);
        return $this->db->update('permintaan_surat', $data);
    }

    /**
     * Delete permintaan surat
     */
    public function delete($id) {
        // Hapus file surat jika ada
        $permintaan = $this->get_by_id($id);
        if ($permintaan && $permintaan->file_surat) {
            $file_path = FCPATH . $permintaan->file_surat;
            if (file_exists($file_path)) {
                unlink($file_path);
            }
        }
        
        $this->db->where('id', $id);
        return $this->db->delete('permintaan_surat');
    }

    /**
     * Update status permintaan surat
     */
    public function update_status($id, $status, $catatan = null, $file_surat = null, $nama_file_surat = null) {
        $data = array('status' => $status);
        
        if ($catatan !== null) {
            $data['catatan_sekprodi'] = $catatan;
        }
        
        if ($file_surat !== null) {
            $data['file_surat'] = $file_surat;
        }
        
        if ($nama_file_surat !== null) {
            $data['nama_file_surat'] = $nama_file_surat;
        }
        
        $this->db->where('id', $id);
        return $this->db->update('permintaan_surat', $data);
    }
}










