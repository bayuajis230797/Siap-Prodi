<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Proposal Model
 * Menangani data pada tabel mahasiswa_proposal (1 pembimbing)
 */
class Proposal_model extends CI_Model {

    public function __construct() {
        parent::__construct();
        $this->load->database();
    }

    /**
     * Ambil data proposal berdasarkan ID
     */
    public function get_by_id($id) {
        $this->db->select('ms.*, m.nim, m.nama_mahasiswa, m.kelas, m.semester_aktif, 
            d1.nama_dosen as nama_pembimbing');
        $this->db->from('mahasiswa_proposal ms');
        $this->db->join('mahasiswa m', 'm.id = ms.id_mahasiswa', 'left');
        $this->db->join('dosen d1', 'd1.id = ms.id_pembimbing', 'left');
        $this->db->where('ms.id', $id);
        return $this->db->get()->row();
    }

    /**
     * Ambil data proposal berdasarkan mahasiswa (opsional filter tahun akademik)
     */
    public function get_by_mahasiswa($id_mahasiswa, $id_tahun_akademik = null) {
        $this->db->select('ms.*, m.nim, m.nama_mahasiswa, m.kelas, 
            d1.nama_dosen as nama_pembimbing');
        $this->db->from('mahasiswa_proposal ms');
        $this->db->join('mahasiswa m', 'm.id = ms.id_mahasiswa', 'left');
        $this->db->join('dosen d1', 'd1.id = ms.id_pembimbing', 'left');
        $this->db->where('ms.id_mahasiswa', $id_mahasiswa);
        if (!empty($id_tahun_akademik)) {
            $this->db->where('ms.id_tahun_akademik', $id_tahun_akademik);
        }
        $this->db->order_by('ms.created_at', 'DESC');
        return $this->db->get()->row();
    }

    /**
     * Update data proposal
     */
    public function update($id, $data) {
        if (empty($id) || !is_array($data)) {
            return false;
        }
        $this->db->where('id', $id);
        $this->db->update('mahasiswa_proposal', $data);
        return $this->db->affected_rows() >= 0;
    }

    /**
     * Buat data proposal baru
     */
    public function create($data) {
        if (!is_array($data) || empty($data)) {
            return false;
        }
        $this->db->insert('mahasiswa_proposal', $data);
        return $this->db->insert_id();
    }

    /**
     * Cek apakah mahasiswa sudah terdaftar proposal
     */
    public function is_registered($id_mahasiswa, $id_tahun_akademik) {
        $this->db->where('id_mahasiswa', $id_mahasiswa);
        $this->db->where('id_tahun_akademik', $id_tahun_akademik);
        $this->db->from('mahasiswa_proposal');
        return $this->db->count_all_results() > 0;
    }

    /**
     * Ambil semua data proposal dengan filter
     */
    public function get_all($id_tahun_akademik = null, $limit = null, $offset = null, $kelas_filter = null) {
        $this->db->select('ms.*, ms.id_mahasiswa, m.nim, m.nama_mahasiswa, m.kelas, 
            d1.nama_dosen as pembimbing, d1.nidn as pembimbing_nidn');
        $this->db->from('mahasiswa_proposal ms');
        $this->db->join('mahasiswa m', 'm.id = ms.id_mahasiswa', 'left');
        $this->db->join('dosen d1', 'd1.id = ms.id_pembimbing', 'left');
        
        if ($id_tahun_akademik) {
            $this->db->where('ms.id_tahun_akademik', $id_tahun_akademik);
        }
        
        if ($kelas_filter) {
            $this->db->where('m.kelas', $kelas_filter);
        }
        
        $this->db->order_by('ms.created_at', 'DESC');
        
        if ($limit !== null) {
            $this->db->limit($limit, $offset);
        }
        
        return $this->db->get()->result();
    }

    /**
     * Hitung total data proposal
     */
    public function count_all($id_tahun_akademik = null, $kelas_filter = null) {
        $this->db->from('mahasiswa_proposal ms');
        $this->db->join('mahasiswa m', 'm.id = ms.id_mahasiswa', 'left');
        
        if ($id_tahun_akademik) {
            $this->db->where('ms.id_tahun_akademik', $id_tahun_akademik);
        }
        
        if ($kelas_filter) {
            $this->db->where('m.kelas', $kelas_filter);
        }
        
        return $this->db->count_all_results();
    }

    /**
     * Hapus data proposal
     */
    public function delete($id) {
        $this->db->where('id', $id);
        return $this->db->delete('mahasiswa_proposal');
    }

    /**
     * Mahasiswa bimbingan seorang dosen (pembimbing Proposal TA).
     */
    public function get_by_pembimbing($id_dosen, $id_tahun_akademik = null) {
        $this->db->select('ms.*, m.nim, m.nama_mahasiswa, m.kelas');
        $this->db->from('mahasiswa_proposal ms');
        $this->db->join('mahasiswa m', 'm.id = ms.id_mahasiswa', 'left');
        $this->db->where('ms.id_pembimbing', $id_dosen);
        if ($id_tahun_akademik) {
            $this->db->where('ms.id_tahun_akademik', $id_tahun_akademik);
        }
        $this->db->order_by('m.nama_mahasiswa', 'ASC');
        return $this->db->get()->result();
    }
}
