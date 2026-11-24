<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Pkl_penilaian_detail_model extends CI_Model {

    public function __construct() {
        parent::__construct();
        $this->load->database();
    }

    public function create($data) {
        $this->db->insert('pkl_penilaian_detail', $data);
        return $this->db->insert_id();
    }

    public function update($id, $data) {
        $this->db->where('id', $id);
        $this->db->update('pkl_penilaian_detail', $data);
        return $this->db->affected_rows();
    }

    public function get_by_penilaian($id_penilaian) {
        $this->db->select('ppd.*, pip.pertanyaan, pap.kode_aspek, pap.nama_aspek');
        $this->db->from('pkl_penilaian_detail ppd');
        $this->db->join('pkl_item_penilaian pip', 'pip.id = ppd.id_item_penilaian', 'left');
        $this->db->join('pkl_aspek_penilaian pap', 'pap.id = pip.id_aspek_penilaian', 'left');
        $this->db->where('ppd.id_penilaian', $id_penilaian);
        $this->db->order_by('pap.urutan', 'ASC');
        $this->db->order_by('pip.urutan', 'ASC');
        return $this->db->get()->result();
    }

    public function delete_by_penilaian($id_penilaian) {
        $this->db->where('id_penilaian', $id_penilaian);
        $this->db->delete('pkl_penilaian_detail');
        return $this->db->affected_rows();
    }

    public function get_by_item($id_item_penilaian) {
        $this->db->where('id_item_penilaian', $id_item_penilaian);
        return $this->db->get('pkl_penilaian_detail')->result();
    }
}







