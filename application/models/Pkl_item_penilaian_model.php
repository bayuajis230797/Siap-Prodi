<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Pkl_item_penilaian_model extends CI_Model {

    public function __construct() {
        parent::__construct();
        $this->load->database();
    }

    public function create($data) {
        $this->db->insert('pkl_item_penilaian', $data);
        return $this->db->insert_id();
    }

    public function update($id, $data) {
        $this->db->where('id', $id);
        $this->db->update('pkl_item_penilaian', $data);
        return $this->db->affected_rows();
    }

    public function delete($id) {
        $this->db->where('id', $id);
        $this->db->delete('pkl_item_penilaian');
        return $this->db->affected_rows();
    }

    public function get_by_id($id) {
        $this->db->select('pip.*, pap.kode_aspek, pap.nama_aspek, pap.ditunjukkan_untuk');
        $this->db->from('pkl_item_penilaian pip');
        $this->db->join('pkl_aspek_penilaian pap', 'pap.id = pip.id_aspek_penilaian', 'left');
        $this->db->where('pip.id', $id);
        return $this->db->get()->row();
    }

    public function get_by_aspek($id_aspek_penilaian, $active_only = false) {
        $this->db->from('pkl_item_penilaian');
        $this->db->where('id_aspek_penilaian', $id_aspek_penilaian);
        
        if ($active_only) {
            $this->db->where('is_aktif', 1);
        }
        
        $this->db->order_by('urutan', 'ASC');
        return $this->db->get()->result();
    }

    public function get_all($active_only = false) {
        $this->db->select('pip.*, pap.kode_aspek, pap.nama_aspek, pap.ditunjukkan_untuk');
        $this->db->from('pkl_item_penilaian pip');
        $this->db->join('pkl_aspek_penilaian pap', 'pap.id = pip.id_aspek_penilaian', 'left');
        
        if ($active_only) {
            $this->db->where('pip.is_aktif', 1);
        }
        
        $this->db->order_by('pap.urutan', 'ASC');
        $this->db->order_by('pip.urutan', 'ASC');
        
        return $this->db->get()->result();
    }
}







