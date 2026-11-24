<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Proposal_nilai_bimbingan_model extends CI_Model {

    public function __construct() {
        parent::__construct();
        $this->load->database();
    }

    public function create($data) {
        $this->db->insert('proposal_nilai_bimbingan', $data);
        return $this->db->insert_id();
    }

    public function update($id, $data) {
        $this->db->where('id', $id);
        $this->db->update('proposal_nilai_bimbingan', $data);
        return $this->db->affected_rows();
    }

    public function delete($id) {
        $this->db->where('id', $id);
        return $this->db->delete('proposal_nilai_bimbingan');
    }

    public function get_by_id($id) {
        $this->db->select('snb.*, ms.id_mahasiswa, ms.judul_proposal, m.nim, m.nama_mahasiswa, m.kelas,
                           d.nama_dosen as pembimbing_nama, d.nidn as pembimbing_nidn,
                           ta.tahun_akademik, ta.semester');
        $this->db->from('proposal_nilai_bimbingan snb');
        $this->db->join('mahasiswa_proposal ms', 'ms.id = snb.id_mahasiswa_proposal', 'left');
        $this->db->join('mahasiswa m', 'm.id = ms.id_mahasiswa', 'left');
        $this->db->join('dosen d', 'd.id = snb.id_dosen', 'left');
        $this->db->join('tahun_akademik ta', 'ta.id = snb.id_tahun_akademik', 'left');
        $this->db->where('snb.id', $id);
        return $this->db->get()->row();
    }

    public function get_by_proposal($id_mahasiswa_proposal) {
        $this->db->select('snb.*, d.nama_dosen as pembimbing_nama, d.nidn as pembimbing_nidn');
        $this->db->from('proposal_nilai_bimbingan snb');
        $this->db->join('dosen d', 'd.id = snb.id_dosen', 'left');
        $this->db->where('snb.id_mahasiswa_proposal', $id_mahasiswa_proposal);
        return $this->db->get()->result();
    }

    public function get_row_by_proposal($id_mahasiswa_proposal) {
        $this->db->where('id_mahasiswa_proposal', $id_mahasiswa_proposal);
        $this->db->order_by('id', 'DESC');
        $this->db->limit(1);
        return $this->db->get('proposal_nilai_bimbingan')->row();
    }

    public function get_by_pembimbing($id_dosen, $id_tahun_akademik = null) {
        $this->db->select('snb.*, ms.id_mahasiswa, ms.judul_proposal, m.nim, m.nama_mahasiswa, m.kelas');
        $this->db->from('proposal_nilai_bimbingan snb');
        $this->db->join('mahasiswa_proposal ms', 'ms.id = snb.id_mahasiswa_proposal', 'left');
        $this->db->join('mahasiswa m', 'm.id = ms.id_mahasiswa', 'left');
        $this->db->where('snb.id_dosen', $id_dosen);
        
        if ($id_tahun_akademik) {
            $this->db->where('snb.id_tahun_akademik', $id_tahun_akademik);
        }
        
        $this->db->order_by('snb.created_at', 'DESC');
        return $this->db->get()->result();
    }

    public function upsert($data) {
        $existing = $this->get_row_by_proposal($data['id_mahasiswa_proposal']);
        if ($existing) {
            $this->db->where('id', $existing->id);
            $this->db->update('proposal_nilai_bimbingan', $data);
            return $existing->id;
        }

        $this->db->insert('proposal_nilai_bimbingan', $data);
        return $this->db->insert_id();
    }
}
