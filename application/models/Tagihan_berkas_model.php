<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Tagihan_berkas_model extends CI_Model {

    public function __construct() {
        parent::__construct();
        $this->load->database();
    }

    /**
     * Get semua tagihan
     */
    public function get_all($kelas_filter = null, $is_aktif = null) {
        $this->db->select('tb.*, u.username as created_by_name');
        $this->db->from('tagihan_berkas tb');
        $this->db->join('users u', 'u.id = tb.created_by', 'left');
        
        if ($kelas_filter && $kelas_filter !== '') {
            $this->db->where('tb.kelas_filter', $kelas_filter);
        }
        
        if ($is_aktif !== null) {
            $this->db->where('tb.is_aktif', $is_aktif);
        }
        
        $this->db->order_by('tb.created_at', 'DESC');
        return $this->db->get()->result();
    }

    /**
     * Get tagihan by ID
     */
    public function get_by_id($id) {
        $this->db->select('tb.*, u.username as created_by_name');
        $this->db->from('tagihan_berkas tb');
        $this->db->join('users u', 'u.id = tb.created_by', 'left');
        $this->db->where('tb.id', $id);
        return $this->db->get()->row();
    }

    /**
     * Create tagihan baru
     */
    public function create($data) {
        $this->db->insert('tagihan_berkas', $data);
        return $this->db->insert_id();
    }

    /**
     * Update tagihan
     */
    public function update($id, $data) {
        $this->db->where('id', $id);
        $this->db->update('tagihan_berkas', $data);
        return $this->db->affected_rows();
    }

    /**
     * Delete tagihan
     */
    public function delete($id) {
        $this->db->where('id', $id);
        $this->db->delete('tagihan_berkas');
        return $this->db->affected_rows();
    }

    /**
     * Get detail tagihan per mahasiswa
     */
    public function get_detail_by_tagihan($id_tagihan, $kelas_filter = null) {
        $this->db->select('tbd.*, m.nim, m.nama_mahasiswa, m.kelas');
        $this->db->from('tagihan_berkas_detail tbd');
        $this->db->join('mahasiswa m', 'm.id = tbd.id_mahasiswa', 'left');
        $this->db->where('tbd.id_tagihan_berkas', $id_tagihan);
        
        if ($kelas_filter && $kelas_filter !== '') {
            $this->db->where('m.kelas', $kelas_filter);
        }
        
        $this->db->order_by('m.kelas', 'ASC');
        $this->db->order_by('m.nim', 'ASC');
        return $this->db->get()->result();
    }

    /**
     * Get detail by mahasiswa dan tagihan
     */
    public function get_detail_by_mahasiswa_tagihan($id_tagihan, $id_mahasiswa) {
        $this->db->where('id_tagihan_berkas', $id_tagihan);
        $this->db->where('id_mahasiswa', $id_mahasiswa);
        return $this->db->get('tagihan_berkas_detail')->row();
    }

    /**
     * Create detail tagihan untuk mahasiswa
     */
    public function create_detail($data) {
        $this->db->insert('tagihan_berkas_detail', $data);
        return $this->db->insert_id();
    }

    /**
     * Update detail tagihan
     */
    public function update_detail($id, $data) {
        $this->db->where('id', $id);
        $this->db->update('tagihan_berkas_detail', $data);
        return $this->db->affected_rows();
    }

    /**
     * Update detail by tagihan dan mahasiswa
     */
    public function update_detail_by_tagihan_mahasiswa($id_tagihan, $id_mahasiswa, $data) {
        $this->db->where('id_tagihan_berkas', $id_tagihan);
        $this->db->where('id_mahasiswa', $id_mahasiswa);
        $this->db->update('tagihan_berkas_detail', $data);
        return $this->db->affected_rows();
    }

    /**
     * Get tagihan untuk mahasiswa tertentu
     */
    public function get_by_mahasiswa($id_mahasiswa, $is_aktif = 1) {
        // Get kelas mahasiswa
        $this->db->select('kelas');
        $this->db->where('id', $id_mahasiswa);
        $mahasiswa = $this->db->get('mahasiswa')->row();
        
        if (!$mahasiswa) {
            return array();
        }
        
        // Get tagihan yang sesuai dengan kelas mahasiswa atau semua kelas
        $this->db->select('tb.*');
        $this->db->from('tagihan_berkas tb');
        $this->db->where('tb.is_aktif', $is_aktif);
        $this->db->group_start();
        $this->db->where('tb.kelas_filter', $mahasiswa->kelas);
        $this->db->or_where('tb.kelas_filter IS NULL');
        $this->db->or_where('tb.kelas_filter', '');
        $this->db->group_end();
        $this->db->order_by('tb.created_at', 'DESC');
        
        return $this->db->get()->result();
    }

    /**
     * Generate detail tagihan untuk semua mahasiswa di kelas tertentu
     */
    public function generate_detail_for_kelas($id_tagihan, $kelas_filter) {
        // Get semua mahasiswa di kelas
        $this->db->where('kelas', $kelas_filter);
        $this->db->where('status', 'aktif');
        $mahasiswa_list = $this->db->get('mahasiswa')->result();
        
        $inserted = 0;
        foreach ($mahasiswa_list as $mhs) {
            // Cek apakah sudah ada
            $existing = $this->get_detail_by_mahasiswa_tagihan($id_tagihan, $mhs->id);
            if (!$existing) {
                $this->create_detail(array(
                    'id_tagihan_berkas' => $id_tagihan,
                    'id_mahasiswa' => $mhs->id,
                    'status' => 'belum',
                    'status_hardcopy' => 'belum'
                ));
                $inserted++;
            }
        }
        
        return $inserted;
    }

    /**
     * Generate detail tagihan untuk semua mahasiswa aktif (jika kelas_filter NULL)
     */
    public function generate_detail_for_all($id_tagihan) {
        $this->db->where('status', 'aktif');
        $mahasiswa_list = $this->db->get('mahasiswa')->result();
        
        $inserted = 0;
        foreach ($mahasiswa_list as $mhs) {
            // Cek apakah sudah ada
            $existing = $this->get_detail_by_mahasiswa_tagihan($id_tagihan, $mhs->id);
            if (!$existing) {
                $this->create_detail(array(
                    'id_tagihan_berkas' => $id_tagihan,
                    'id_mahasiswa' => $mhs->id,
                    'status' => 'belum',
                    'status_hardcopy' => 'belum'
                ));
                $inserted++;
            }
        }
        
        return $inserted;
    }

    /**
     * Count statistik tagihan
     */
    public function get_statistik($id_tagihan) {
        $this->db->select('
            COUNT(*) as total,
            SUM(CASE WHEN status = "sudah" THEN 1 ELSE 0 END) as sudah,
            SUM(CASE WHEN status = "belum" THEN 1 ELSE 0 END) as belum,
            SUM(CASE WHEN status_hardcopy = "sudah" THEN 1 ELSE 0 END) as hardcopy_sudah,
            SUM(CASE WHEN status_hardcopy = "belum" THEN 1 ELSE 0 END) as hardcopy_belum
        ');
        $this->db->where('id_tagihan_berkas', $id_tagihan);
        return $this->db->get('tagihan_berkas_detail')->row();
    }
}

