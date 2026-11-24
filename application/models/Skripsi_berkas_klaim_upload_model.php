<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Skripsi_berkas_klaim_upload_model extends CI_Model {

    public function __construct() {
        parent::__construct();
        $this->load->database();
    }

    public function create($data) {
        $this->db->insert('skripsi_berkas_klaim_upload', $data);
        return $this->db->insert_id();
    }

    public function update($id, $data) {
        $this->db->where('id', $id);
        $this->db->update('skripsi_berkas_klaim_upload', $data);
        return $this->db->affected_rows();
    }

    public function delete($id) {
        $upload = $this->get_by_id($id);
        
        if ($upload && !empty($upload->file_path) && file_exists($upload->file_path)) {
            unlink($upload->file_path);
        }
        
        $this->db->where('id', $id);
        return $this->db->delete('skripsi_berkas_klaim_upload');
    }

    public function get_by_id($id) {
        $this->db->select('sbku.*, sbkn.nama_berkas, sbkn.wajib');
        $this->db->from('skripsi_berkas_klaim_upload sbku');
        $this->db->join('skripsi_berkas_klaim_nilai sbkn', 'sbkn.id = sbku.id_berkas_klaim_nilai', 'left');
        $this->db->where('sbku.id', $id);
        return $this->db->get()->row();
    }

    public function get_by_klaim_nilai($id_klaim_nilai) {
        $this->db->select('sbku.*, sbkn.nama_berkas, sbkn.deskripsi, sbkn.wajib, sbkn.urutan');
        $this->db->from('skripsi_berkas_klaim_upload sbku');
        $this->db->join('skripsi_berkas_klaim_nilai sbkn', 'sbkn.id = sbku.id_berkas_klaim_nilai', 'left');
        $this->db->where('sbku.id_klaim_nilai', $id_klaim_nilai);
        $this->db->order_by('sbkn.urutan', 'ASC');
        return $this->db->get()->result();
    }

    public function get_by_klaim_berkas($id_klaim_nilai, $id_berkas_klaim_nilai) {
        $this->db->where('id_klaim_nilai', $id_klaim_nilai);
        $this->db->where('id_berkas_klaim_nilai', $id_berkas_klaim_nilai);
        return $this->db->get('skripsi_berkas_klaim_upload')->row();
    }

    public function delete_by_klaim_nilai($id_klaim_nilai) {
        $uploads = $this->get_by_klaim_nilai($id_klaim_nilai);
        
        foreach ($uploads as $upload) {
            if (!empty($upload->file_path) && file_exists($upload->file_path)) {
                unlink($upload->file_path);
            }
        }
        
        $this->db->where('id_klaim_nilai', $id_klaim_nilai);
        return $this->db->delete('skripsi_berkas_klaim_upload');
    }
}





