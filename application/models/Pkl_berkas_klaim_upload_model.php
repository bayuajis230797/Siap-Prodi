<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Pkl_berkas_klaim_upload_model extends CI_Model {

    public function __construct() {
        parent::__construct();
        $this->load->database();
    }

    public function create($data) {
        $this->db->insert('pkl_berkas_klaim_upload', $data);
        return $this->db->insert_id();
    }

    public function update($id, $data) {
        $this->db->where('id', $id);
        $this->db->update('pkl_berkas_klaim_upload', $data);
        return $this->db->affected_rows();
    }

    public function delete($id) {
        $upload = $this->get_by_id($id);
        if ($upload && !empty($upload->file_path) && file_exists($upload->file_path)) {
            unlink($upload->file_path);
        }
        $this->db->where('id', $id);
        $this->db->delete('pkl_berkas_klaim_upload');
        return $this->db->affected_rows();
    }

    public function get_by_id($id) {
        $this->db->select('pbku.*, pbkn.nama_berkas, pbkn.deskripsi, pbkn.wajib');
        $this->db->from('pkl_berkas_klaim_upload pbku');
        $this->db->join('pkl_berkas_klaim_nilai pbkn', 'pbkn.id = pbku.id_berkas_klaim_nilai', 'left');
        $this->db->where('pbku.id', $id);
        return $this->db->get()->row();
    }

    public function get_by_klaim($id_klaim_nilai) {
        $this->db->select('pbku.*, pbkn.nama_berkas, pbkn.deskripsi, pbkn.wajib, pbkn.urutan');
        $this->db->from('pkl_berkas_klaim_upload pbku');
        $this->db->join('pkl_berkas_klaim_nilai pbkn', 'pbkn.id = pbku.id_berkas_klaim_nilai', 'left');
        $this->db->where('pbku.id_klaim_nilai', $id_klaim_nilai);
        $this->db->order_by('pbkn.urutan', 'ASC');
        return $this->db->get()->result();
    }

    public function get_by_klaim_berkas($id_klaim_nilai, $id_berkas_klaim_nilai) {
        $this->db->where('id_klaim_nilai', $id_klaim_nilai);
        $this->db->where('id_berkas_klaim_nilai', $id_berkas_klaim_nilai);
        return $this->db->get('pkl_berkas_klaim_upload')->row();
    }
}





