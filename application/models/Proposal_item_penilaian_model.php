<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Proposal_item_penilaian_model extends CI_Model {

    public function __construct() {
        parent::__construct();
        $this->load->database();
    }

    public function create($data) {
        $this->db->insert('proposal_item_penilaian', $data);
        return $this->db->insert_id();
    }

    public function update($id, $data) {
        $this->db->where('id', $id);
        $this->db->update('proposal_item_penilaian', $data);
        return $this->db->affected_rows();
    }

    public function delete($id) {
        $this->db->where('id', $id);
        $this->db->delete('proposal_item_penilaian');
        return $this->db->affected_rows();
    }

    public function get_by_id($id) {
        $this->db->select('sip.*, 
                           sap.kode as kode_aspek, 
                           sap.nama as nama_aspek, 
                           sap.tipe_penilai,
                           sip.aktif as is_aktif');
        $this->db->from('proposal_item_penilaian sip');
        $this->db->join('proposal_aspek_penilaian sap', 'sap.id = sip.id_aspek_penilaian', 'left');
        $this->db->where('sip.id', $id);
        return $this->db->get()->row();
    }

    public function get_by_aspek($id_aspek_penilaian, $active_only = false) {
        $this->db->from('proposal_item_penilaian');
        $this->db->where('id_aspek_penilaian', $id_aspek_penilaian);
        
        if ($active_only) {
            $this->db->where('aktif', 1);
        }
        
        $this->db->order_by('urutan', 'ASC');
        return $this->db->get()->result();
    }

    public function get_by_kode_aspek($kode_aspek, $active_only = false) {
        $this->db->select('sip.*, sap.kode, sap.nama as nama_aspek');
        $this->db->from('proposal_item_penilaian sip');
        $this->db->join('proposal_aspek_penilaian sap', 'sap.id = sip.id_aspek_penilaian', 'left');
        $this->db->where('sap.kode', $kode_aspek);
        
        if ($active_only) {
            $this->db->where('sip.aktif', 1);
        }
        
        $this->db->order_by('sip.urutan', 'ASC');
        return $this->db->get()->result();
    }

    public function get_all($active_only = false) {
        $this->db->select('sip.*, 
                           sap.kode as kode_aspek, 
                           sap.nama as nama_aspek, 
                           sap.tipe_penilai,
                           sip.aktif as is_aktif');
        $this->db->from('proposal_item_penilaian sip');
        $this->db->join('proposal_aspek_penilaian sap', 'sap.id = sip.id_aspek_penilaian', 'left');
        
        if ($active_only) {
            $this->db->where('sip.aktif', 1);
        }
        
        $this->db->order_by('sap.kode', 'ASC');
        $this->db->order_by('sip.urutan', 'ASC');
        
        return $this->db->get()->result();
    }

    public function count_all($active_only = false) {
        if ($active_only) {
            $this->db->where('aktif', 1);
        }
        return $this->db->count_all_results('proposal_item_penilaian');
    }
}

