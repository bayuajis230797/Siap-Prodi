<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Skripsi_nilai_sidang_detail_model extends CI_Model {

    public function __construct() {
        parent::__construct();
        $this->load->database();
    }

    public function create($data) {
        $this->db->insert('skripsi_nilai_sidang_detail', $data);
        return $this->db->insert_id();
    }

    public function update($id, $data) {
        $this->db->where('id', $id);
        $this->db->update('skripsi_nilai_sidang_detail', $data);
        return $this->db->affected_rows();
    }

    public function delete($id) {
        $this->db->where('id', $id);
        $this->db->delete('skripsi_nilai_sidang_detail');
        return $this->db->affected_rows();
    }

    public function get_by_id($id) {
        $this->db->select('snsd.*, sip.pertanyaan, sip.bobot, sap.kode, sap.nama as nama_aspek');
        $this->db->from('skripsi_nilai_sidang_detail snsd');
        $this->db->join('skripsi_item_penilaian sip', 'sip.id = snsd.id_item_penilaian', 'left');
        $this->db->join('skripsi_aspek_penilaian sap', 'sap.id = sip.id_aspek_penilaian', 'left');
        $this->db->where('snsd.id', $id);
        return $this->db->get()->row();
    }

    public function get_by_nilai_sidang($id_nilai_sidang) {
        $this->db->select('snsd.*, sip.pertanyaan, sip.bobot, sip.urutan, sap.kode, sap.nama as nama_aspek');
        $this->db->from('skripsi_nilai_sidang_detail snsd');
        $this->db->join('skripsi_item_penilaian sip', 'sip.id = snsd.id_item_penilaian', 'left');
        $this->db->join('skripsi_aspek_penilaian sap', 'sap.id = sip.id_aspek_penilaian', 'left');
        $this->db->where('snsd.id_nilai_sidang', $id_nilai_sidang);
        $this->db->order_by('sip.urutan', 'ASC');
        return $this->db->get()->result();
    }

    public function delete_by_nilai_sidang($id_nilai_sidang) {
        $this->db->where('id_nilai_sidang', $id_nilai_sidang);
        return $this->db->delete('skripsi_nilai_sidang_detail');
    }
}





