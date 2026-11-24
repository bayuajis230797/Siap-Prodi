<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Pkl_penilaian_model extends CI_Model {

    public function __construct() {
        parent::__construct();
        $this->load->database();
    }

    public function create($data) {
        $this->db->insert('pkl_penilaian', $data);
        return $this->db->insert_id();
    }

    public function update($id, $data) {
        $this->db->where('id', $id);
        $this->db->update('pkl_penilaian', $data);
        return $this->db->affected_rows();
    }

    public function get_by_id($id) {
        $this->db->select('pp.*, m.nim, m.nama_mahasiswa, m.kelas');
        $this->db->from('pkl_penilaian pp');
        $this->db->join('mahasiswa m', 'm.id = pp.id_mahasiswa', 'left');
        $this->db->where('pp.id', $id);
        return $this->db->get()->row();
    }

    public function get_by_mahasiswa_penilai($id_mahasiswa, $id_tahun_akademik, $id_penilai, $tipe_penilai) {
        $this->db->where('id_mahasiswa', $id_mahasiswa);
        $this->db->where('id_tahun_akademik', $id_tahun_akademik);
        $this->db->where('id_penilai', $id_penilai);
        $this->db->where('tipe_penilai', $tipe_penilai);
        return $this->db->get('pkl_penilaian')->row();
    }

    public function get_by_mahasiswa($id_mahasiswa, $id_tahun_akademik = null) {
        $this->db->select('pp.*, m.nim, m.nama_mahasiswa, m.kelas');
        $this->db->from('pkl_penilaian pp');
        $this->db->join('mahasiswa m', 'm.id = pp.id_mahasiswa', 'left');
        $this->db->where('pp.id_mahasiswa', $id_mahasiswa);
        if ($id_tahun_akademik) {
            $this->db->where('pp.id_tahun_akademik', $id_tahun_akademik);
        }
        return $this->db->get()->result();
    }

    public function get_by_mahasiswa_tipe($id_mahasiswa, $id_tahun_akademik, $tipe_penilai) {
        $this->db->select('pp.*, m.nim, m.nama_mahasiswa, m.kelas');
        $this->db->from('pkl_penilaian pp');
        $this->db->join('mahasiswa m', 'm.id = pp.id_mahasiswa', 'left');
        $this->db->where('pp.id_mahasiswa', $id_mahasiswa);
        $this->db->where('pp.id_tahun_akademik', $id_tahun_akademik);
        $this->db->where('pp.tipe_penilai', $tipe_penilai);
        return $this->db->get()->row();
    }

    public function get_all_by_tipe($tipe_penilai, $id_tahun_akademik = null) {
        $this->db->select('pp.*, m.nim, m.nama_mahasiswa, m.kelas');
        $this->db->from('pkl_penilaian pp');
        $this->db->join('mahasiswa m', 'm.id = pp.id_mahasiswa', 'left');
        $this->db->where('pp.tipe_penilai', $tipe_penilai);
        if ($id_tahun_akademik) {
            $this->db->where('pp.id_tahun_akademik', $id_tahun_akademik);
        }
        $this->db->order_by('pp.created_at', 'DESC');
        return $this->db->get()->result();
    }

    /**
     * Get penilaian by jadwal PKL untuk penguji
     * @param int $id_jadwal_pkl ID jadwal PKL
     * @return array Array of penilaian objects
     */
    public function get_by_jadwal_pkl($id_jadwal_pkl) {
        $this->db->select('pp.*, d.nama_dosen as penilai_nama, d.nidn as penilai_nidn');
        $this->db->from('pkl_penilaian pp');
        $this->db->join('dosen d', 'd.id = pp.id_penilai', 'left');
        $this->db->where('pp.id_jadwal_pkl', $id_jadwal_pkl);
        $this->db->where('pp.tipe_penilai', 'penguji');
        $this->db->order_by('pp.created_at', 'DESC');
        return $this->db->get()->result();
    }
}

