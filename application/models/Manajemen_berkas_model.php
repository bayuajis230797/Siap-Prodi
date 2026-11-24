<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Manajemen_berkas_model extends CI_Model {

    public function __construct() {
        parent::__construct();
        $this->load->database();
    }

    public function get_all($is_active = null, $kategori = null, $limit = null, $offset = null) {
        $this->db->select('mb.*, u.username, r.nama_role as role');
        $this->db->from('manajemen_berkas mb');
        $this->db->join('users u', 'u.id = mb.uploaded_by', 'left');
        $this->db->join('roles r', 'r.id = u.role_id', 'left');
        
        if ($is_active !== null) {
            $this->db->where('mb.is_active', $is_active);
        }
        
        if ($kategori) {
            $this->db->where('mb.kategori', $kategori);
        }
        
        $this->db->order_by('mb.created_at', 'DESC');
        
        if ($limit !== null) {
            $this->db->limit($limit, $offset);
        }
        
        return $this->db->get()->result();
    }

    public function count_all($is_active = null, $kategori = null) {
        $this->db->from('manajemen_berkas mb');
        
        if ($is_active !== null) {
            $this->db->where('mb.is_active', $is_active);
        }
        
        if ($kategori) {
            $this->db->where('mb.kategori', $kategori);
        }
        
        return $this->db->count_all_results();
    }

    public function get_by_id($id) {
        $this->db->select('mb.*, u.username, r.nama_role as role');
        $this->db->from('manajemen_berkas mb');
        $this->db->join('users u', 'u.id = mb.uploaded_by', 'left');
        $this->db->join('roles r', 'r.id = u.role_id', 'left');
        $this->db->where('mb.id', $id);
        return $this->db->get()->row();
    }

    public function get_active($kategori = null) {
        $this->db->select('mb.*');
        $this->db->from('manajemen_berkas mb');
        $this->db->where('mb.is_active', 1);
        
        if ($kategori) {
            $this->db->where('mb.kategori', $kategori);
        }
        
        $this->db->order_by('mb.created_at', 'DESC');
        return $this->db->get()->result();
    }

    public function create($data) {
        $this->db->insert('manajemen_berkas', $data);
        return $this->db->insert_id();
    }

    public function update($id, $data) {
        $this->db->where('id', $id);
        $this->db->update('manajemen_berkas', $data);
        return $this->db->affected_rows();
    }

    public function delete($id) {
        // Hapus file fisik hanya jika bukan link (untuk backward compatibility)
        $berkas = $this->get_by_id($id);
        if ($berkas && !empty($berkas->file_path)) {
            // Cek apakah file_path adalah link (dimulai dengan http)
            if (strpos($berkas->file_path, 'http') === false) {
                // Ini adalah file lokal, hapus file fisik
                $file_path = FCPATH . $berkas->file_path;
                if (file_exists($file_path)) {
                    @unlink($file_path);
                }
            }
            // Jika file_path adalah link, tidak perlu hapus file fisik
        }
        
        $this->db->where('id', $id);
        $this->db->delete('manajemen_berkas');
        return $this->db->affected_rows();
    }

    public function toggle_active($id) {
        $berkas = $this->get_by_id($id);
        if (!$berkas) {
            return false;
        }
        
        $new_status = $berkas->is_active == 1 ? 0 : 1;
        $this->db->where('id', $id);
        $this->db->update('manajemen_berkas', array('is_active' => $new_status));
        return $this->db->affected_rows();
    }

    public function get_kategori_list() {
        $this->db->select('DISTINCT kategori', FALSE);
        $this->db->from('manajemen_berkas');
        $this->db->where('kategori IS NOT NULL');
        $this->db->where('kategori !=', '');
        $this->db->order_by('kategori', 'ASC');
        $query = $this->db->get();
        return $query->result();
    }
}

