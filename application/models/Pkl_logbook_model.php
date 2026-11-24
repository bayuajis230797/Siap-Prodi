<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Pkl_logbook_model extends CI_Model {

    public function __construct() {
        parent::__construct();
        $this->load->database();
    }

    public function create($data) {
        $this->db->insert('pkl_logbook', $data);
        return $this->db->insert_id();
    }

    public function get_by_id($id) {
        $this->db->select('pl.*, m.nim, m.nama_mahasiswa, pp.judul_pkl');
        $this->db->from('pkl_logbook pl');
        $this->db->join('mahasiswa m', 'm.id = pl.id_mahasiswa', 'left');
        $this->db->join('pkl_pengajuan pp', 'pp.id = pl.id_pkl_pengajuan', 'left');
        $this->db->where('pl.id', $id);
        return $this->db->get()->row();
    }

    public function get_by_mahasiswa($id_mahasiswa, $id_tahun_akademik = null) {
        $this->db->select('pl.*, m.nim, m.nama_mahasiswa');
        $this->db->from('pkl_logbook pl');
        $this->db->join('mahasiswa m', 'm.id = pl.id_mahasiswa', 'left');
        $this->db->where('pl.id_mahasiswa', $id_mahasiswa);
        
        // Filter by tahun akademik jika ada pengajuan
        if ($id_tahun_akademik) {
            $this->db->join('pkl_pengajuan pp', 'pp.id = pl.id_pkl_pengajuan', 'left');
            $this->db->where('(pp.id_tahun_akademik = ' . $this->db->escape($id_tahun_akademik) . ' OR pl.id_pkl_pengajuan IS NULL)', null, false);
        }
        
        $this->db->order_by('pl.tanggal', 'DESC');
        $this->db->order_by('pl.created_at', 'DESC');
        return $this->db->get()->result();
    }

    public function get_by_tanggal($id_mahasiswa, $tanggal) {
        $this->db->where('id_mahasiswa', $id_mahasiswa);
        $this->db->where('tanggal', $tanggal);
        return $this->db->get('pkl_logbook')->row();
    }

    public function update($id, $data) {
        $this->db->where('id', $id);
        $this->db->update('pkl_logbook', $data);
        return $this->db->affected_rows();
    }

    public function delete($id) {
        $this->db->where('id', $id);
        $this->db->delete('pkl_logbook');
        return $this->db->affected_rows();
    }

    public function count_by_mahasiswa($id_mahasiswa) {
        $this->db->where('id_mahasiswa', $id_mahasiswa);
        return $this->db->count_all_results('pkl_logbook');
    }

    /**
     * Get logbook by pembimbing (untuk monitoring dosen)
     * @param int $id_pembimbing ID dosen pembimbing
     * @param int $id_tahun_akademik ID tahun akademik (optional)
     * @return array List logbook mahasiswa bimbingan
     */
    public function get_by_pembimbing($id_pembimbing, $id_tahun_akademik = null) {
        $this->db->select('pl.*, m.nim, m.nama_mahasiswa, pp.judul_pkl');
        $this->db->from('pkl_logbook pl');
        $this->db->join('mahasiswa m', 'm.id = pl.id_mahasiswa', 'left');
        $this->db->join('pkl_pengajuan pp', 'pp.id = pl.id_pkl_pengajuan', 'left');
        $this->db->join('pkl_bimbingan pb', 'pb.id_mahasiswa = pl.id_mahasiswa AND pb.id_pembimbing = ' . $this->db->escape($id_pembimbing), 'inner');
        
        if ($id_tahun_akademik) {
            $this->db->where('pp.id_tahun_akademik', $id_tahun_akademik);
        }
        
        $this->db->order_by('pl.tanggal', 'DESC');
        $this->db->order_by('pl.created_at', 'DESC');
        return $this->db->get()->result();
    }

    /**
     * Get statistik logbook mahasiswa
     * @param int $id_mahasiswa ID mahasiswa
     * @param int $id_tahun_akademik ID tahun akademik (optional)
     * @return object Statistik (total, dengan_koordinat, tanpa_koordinat)
     */
    public function get_statistik($id_mahasiswa, $id_tahun_akademik = null) {
        $this->db->select('COUNT(*) as total, 
                          SUM(CASE WHEN pl.latitude_logbook IS NOT NULL AND pl.longitude_logbook IS NOT NULL THEN 1 ELSE 0 END) as dengan_koordinat,
                          SUM(CASE WHEN pl.latitude_logbook IS NULL OR pl.longitude_logbook IS NULL THEN 1 ELSE 0 END) as tanpa_koordinat');
        $this->db->from('pkl_logbook pl');
        $this->db->where('pl.id_mahasiswa', $id_mahasiswa);
        
        if ($id_tahun_akademik) {
            $this->db->join('pkl_pengajuan pp', 'pp.id = pl.id_pkl_pengajuan', 'left');
            $this->db->where('pp.id_tahun_akademik', $id_tahun_akademik);
        }
        
        return $this->db->get()->row();
    }

    /**
     * Hitung jumlah logbook sejak tanggal tertentu.
     */
    public function count_since($id_mahasiswa, $tanggal_mulai, $id_tahun_akademik = null) {
        $this->db->from('pkl_logbook pl');
        $this->db->where('pl.id_mahasiswa', $id_mahasiswa);
        $this->db->where('pl.tanggal >=', $tanggal_mulai);

        if ($id_tahun_akademik) {
            $this->db->join('pkl_pengajuan pp', 'pp.id = pl.id_pkl_pengajuan', 'left');
            $this->db->where('pp.id_tahun_akademik', $id_tahun_akademik);
        }

        return (int) $this->db->count_all_results();
    }
}

