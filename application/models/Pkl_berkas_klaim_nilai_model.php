<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Pkl_berkas_klaim_nilai_model extends CI_Model {

    public function __construct() {
        parent::__construct();
        $this->load->database();
    }

    public function create($data) {
        $this->db->insert('pkl_berkas_klaim_nilai', $data);
        return $this->db->insert_id();
    }

    public function update($id, $data) {
        $this->db->where('id', $id);
        $this->db->update('pkl_berkas_klaim_nilai', $data);
        return $this->db->affected_rows();
    }

    public function delete($id) {
        $this->db->where('id', $id);
        $this->db->delete('pkl_berkas_klaim_nilai');
        return $this->db->affected_rows();
    }

    public function get_by_id($id) {
        $this->db->where('id', $id);
        return $this->db->get('pkl_berkas_klaim_nilai')->row();
    }

    public function get_all($aktif_only = false) {
        if ($aktif_only) {
            $this->db->where('aktif', 1);
        }
        $this->db->order_by('urutan', 'ASC');
        $this->db->order_by('id', 'ASC');
        return $this->db->get('pkl_berkas_klaim_nilai')->result();
    }

    public function get_wajib() {
        $this->db->where('aktif', 1);
        $this->db->where('wajib', 1);
        $this->db->order_by('urutan', 'ASC');
        return $this->db->get('pkl_berkas_klaim_nilai')->result();
    }
}





