<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Skripsi_pembimbing_model extends CI_Model {

    public function __construct() {
        parent::__construct();
        $this->load->database();
    }

    public function get_all() {
        $this->db->select('sp.*, d.nama_dosen, d.nidn, d.status');
        $this->db->from('skripsi_pembimbing sp');
        $this->db->join('dosen d', 'd.id = sp.id_dosen', 'left');
        $this->db->order_by('d.nama_dosen', 'ASC');
        return $this->db->get()->result();
    }

    public function get_map() {
        $result = $this->get_all();
        $map = array();
        foreach ($result as $row) {
            $map[$row->id_dosen] = $row;
        }
        return $map;
    }

    public function get_by_dosen($id_dosen) {
        $this->db->where('id_dosen', $id_dosen);
        $query = $this->db->get('skripsi_pembimbing');
        return $query->row();
    }

    public function upsert($id_dosen, $allowed_p1 = 0, $allowed_p2 = 0, $is_active = 1) {
        $data = array(
            'id_dosen' => $id_dosen,
            'allowed_pembimbing_1' => $allowed_p1 ? 1 : 0,
            'allowed_pembimbing_2' => $allowed_p2 ? 1 : 0,
            'is_active' => $is_active ? 1 : 0
        );

        $existing = $this->get_by_dosen($id_dosen);
        if ($existing) {
            $this->db->where('id', $existing->id);
            $this->db->update('skripsi_pembimbing', $data);
            return $existing->id;
        } else {
            $this->db->insert('skripsi_pembimbing', $data);
            return $this->db->insert_id();
        }
    }

    public function get_allowed_for_type($type = 1) {
        // Menggunakan field is_pembimbing_skripsi di tabel dosen
        // Mengembalikan id_dosen untuk kompatibilitas dengan kode yang sudah ada
        
        // Cek apakah kolom is_pembimbing_skripsi sudah ada
        $column_exists = false;
        try {
            $result = $this->db->query("SHOW COLUMNS FROM dosen LIKE 'is_pembimbing_skripsi'");
            $column_exists = $result->num_rows() > 0;
        } catch (Exception $e) {
            // Jika error, asumsikan kolom belum ada
            $column_exists = false;
        }
        
        if ($column_exists) {
            // Gunakan field is_pembimbing_skripsi
            $this->db->select('d.id as id_dosen, d.id, d.nama_dosen, d.nidn, d.status, d.is_pembimbing_skripsi');
            $this->db->from('dosen d');
            $this->db->where('d.is_pembimbing_skripsi', 1);
            $this->db->where('d.status', 'aktif');
        } else {
            // Fallback: gunakan tabel skripsi_pembimbing jika kolom belum ada
            $this->db->select('d.id as id_dosen, d.id, d.nama_dosen, d.nidn, d.status');
            $this->db->from('dosen d');
            $this->db->join('skripsi_pembimbing sp', 'sp.id_dosen = d.id', 'left');
            $column = ($type == 2) ? 'allowed_pembimbing_2' : 'allowed_pembimbing_1';
            $this->db->where('sp.' . $column, 1);
            $this->db->where('sp.is_active', 1);
            $this->db->where('d.status', 'aktif');
        }
        
        $this->db->order_by('d.nama_dosen', 'ASC');
        return $this->db->get()->result();
    }

    public function is_allowed($id_dosen, $type = 1) {
        // Cek apakah kolom is_pembimbing_skripsi sudah ada
        $column_exists = false;
        try {
            $result = $this->db->query("SHOW COLUMNS FROM dosen LIKE 'is_pembimbing_skripsi'");
            $column_exists = $result->num_rows() > 0;
        } catch (Exception $e) {
            $column_exists = false;
        }
        
        if ($column_exists) {
            // Gunakan field is_pembimbing_skripsi
            $this->db->where('id', $id_dosen);
            $this->db->where('is_pembimbing_skripsi', 1);
            $this->db->where('status', 'aktif');
            return $this->db->get('dosen')->row() ? true : false;
        } else {
            // Fallback: gunakan tabel skripsi_pembimbing
            $this->db->select('sp.*');
            $this->db->from('skripsi_pembimbing sp');
            $this->db->join('dosen d', 'd.id = sp.id_dosen', 'left');
            $this->db->where('sp.id_dosen', $id_dosen);
            $column = ($type == 2) ? 'allowed_pembimbing_2' : 'allowed_pembimbing_1';
            $this->db->where('sp.' . $column, 1);
            $this->db->where('sp.is_active', 1);
            $this->db->where('d.status', 'aktif');
            return $this->db->get()->row() ? true : false;
        }
    }
}



