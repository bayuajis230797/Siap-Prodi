<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Proposal_nilai_bimbingan_detail_model extends CI_Model {

    public function __construct() {
        parent::__construct();
        $this->load->database();
    }

    public function create($data) {
        $this->db->insert('proposal_nilai_bimbingan_detail', $data);
        return $this->db->insert_id();
    }

    public function update($id, $data) {
        $this->db->where('id', $id);
        $this->db->update('proposal_nilai_bimbingan_detail', $data);
        return $this->db->affected_rows();
    }

    public function delete($id) {
        $this->db->where('id', $id);
        $this->db->delete('proposal_nilai_bimbingan_detail');
        return $this->db->affected_rows();
    }

    public function get_by_id($id) {
        $this->db->select('snbd.*, sip.pertanyaan, sip.bobot, sap.kode, sap.nama as nama_aspek');
        $this->db->from('proposal_nilai_bimbingan_detail snbd');
        $this->db->join('proposal_item_penilaian sip', 'sip.id = snbd.id_item_penilaian', 'left');
        $this->db->join('proposal_aspek_penilaian sap', 'sap.id = sip.id_aspek_penilaian', 'left');
        $this->db->where('snbd.id', $id);
        return $this->db->get()->row();
    }

    public function get_by_nilai_bimbingan($id_nilai_bimbingan) {
        $this->db->select('snbd.*, sip.pertanyaan, sip.bobot, sip.urutan, sap.kode, sap.nama as nama_aspek');
        $this->db->from('proposal_nilai_bimbingan_detail snbd');
        $this->db->join('proposal_item_penilaian sip', 'sip.id = snbd.id_item_penilaian', 'left');
        $this->db->join('proposal_aspek_penilaian sap', 'sap.id = sip.id_aspek_penilaian', 'left');
        $this->db->where('snbd.id_nilai_bimbingan', $id_nilai_bimbingan);
        $this->db->order_by('sip.urutan', 'ASC');
        return $this->db->get()->result();
    }

    public function delete_by_nilai_bimbingan($id_nilai_bimbingan) {
        $this->db->where('id_nilai_bimbingan', $id_nilai_bimbingan);
        return $this->db->delete('proposal_nilai_bimbingan_detail');
    }
}





