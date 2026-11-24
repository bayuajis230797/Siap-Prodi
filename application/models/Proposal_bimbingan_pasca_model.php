<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Proposal_bimbingan_pasca_model extends CI_Model {

    public function __construct() {
        parent::__construct();
        $this->load->database();
    }

    public function create($data) {
        $this->db->insert('proposal_bimbingan_pasca', $data);
        return $this->db->insert_id();
    }

    public function update($id, $data) {
        $this->db->where('id', (int) $id);
        $this->db->update('proposal_bimbingan_pasca', $data);
        return $this->db->affected_rows();
    }

    public function get_by_id($id) {
        $this->db->select('sbp.*, m.nim, m.nama_mahasiswa, m.kelas, js.tanggal_ujian, js.ruangan, js.status as status_jadwal, ms.judul_proposal');
        $this->db->from('proposal_bimbingan_pasca sbp');
        $this->db->join('mahasiswa m', 'm.id = sbp.id_mahasiswa', 'left');
        $this->db->join('jadwal_proposal js', 'js.id = sbp.id_jadwal_proposal', 'left');
        $this->db->join('mahasiswa_proposal ms', 'ms.id = sbp.id_mahasiswa_proposal', 'left');
        $this->db->where('sbp.id', (int) $id);
        return $this->db->get()->row();
    }

    public function get_by_jadwal($id_jadwal) {
        $this->db->where('id_jadwal_proposal', (int) $id_jadwal);
        return $this->db->get('proposal_bimbingan_pasca')->row();
    }

    public function get_by_mahasiswa($id_mahasiswa) {
        $this->db->select('sbp.*, js.tanggal_ujian, js.ruangan, js.status as status_jadwal');
        $this->db->from('proposal_bimbingan_pasca sbp');
        $this->db->join('jadwal_proposal js', 'js.id = sbp.id_jadwal_proposal', 'left');
        $this->db->where('sbp.id_mahasiswa', (int) $id_mahasiswa);
        $this->db->order_by('sbp.last_activity', 'DESC');
        return $this->db->get()->result();
    }

    public function get_by_penguji($id_dosen) {
        $this->db->select('sbp.*, m.nim, m.nama_mahasiswa, m.kelas, js.tanggal_ujian, sbpp.peran, sbpp.layak_klaim, sbpp.layak_klaim_at');
        $this->db->from('proposal_bimbingan_pasca sbp');
        $this->db->join('proposal_bimbingan_pasca_penguji sbpp', 'sbpp.id_bimbingan_pasca = sbp.id', 'inner');
        $this->db->join('mahasiswa m', 'm.id = sbp.id_mahasiswa', 'left');
        $this->db->join('jadwal_proposal js', 'js.id = sbp.id_jadwal_proposal', 'left');
        $this->db->where('sbpp.id_dosen', (int) $id_dosen);
        $this->db->order_by('sbp.last_activity', 'DESC');
        return $this->db->get()->result();
    }
}
