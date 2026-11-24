<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Skripsi_berkas_klaim_nilai_model extends CI_Model {

    public function __construct() {
        parent::__construct();
        $this->load->database();
    }

    public function create($data) {
        $this->db->insert('skripsi_berkas_klaim_nilai', $data);
        return $this->db->insert_id();
    }

    public function update($id, $data) {
        $this->db->where('id', $id);
        $this->db->update('skripsi_berkas_klaim_nilai', $data);
        return $this->db->affected_rows();
    }

    public function delete($id) {
        $this->db->where('id', $id);
        $this->db->delete('skripsi_berkas_klaim_nilai');
        return $this->db->affected_rows();
    }

    public function get_by_id($id) {
        return $this->db->get_where('skripsi_berkas_klaim_nilai', array('id' => $id))->row();
    }

    public function get_all($active_only = false) {
        if ($active_only) {
            $this->db->where('aktif', 1);
        }
        $this->db->order_by('urutan', 'ASC');
        return $this->db->get('skripsi_berkas_klaim_nilai')->result();
    }

    public function count_all($active_only = false) {
        if ($active_only) {
            $this->db->where('aktif', 1);
        }
        return $this->db->count_all_results('skripsi_berkas_klaim_nilai');
    }
}





