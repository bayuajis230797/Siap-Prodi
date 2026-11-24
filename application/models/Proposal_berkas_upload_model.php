<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Proposal_berkas_upload_model extends CI_Model {

    public function __construct() {
        parent::__construct();
        $this->load->database();
    }

    public function create($data) {
        $this->db->insert('proposal_berkas_sidang_upload', $data);
        return $this->db->insert_id();
    }

    public function update($id, $data) {
        $this->db->where('id', $id);
        $this->db->update('proposal_berkas_sidang_upload', $data);
        return $this->db->affected_rows();
    }

    public function delete($id) {
        $upload = $this->get_by_id($id);
        
        if ($upload && !empty($upload->file_path) && file_exists($upload->file_path)) {
            unlink($upload->file_path);
        }
        
        $this->db->where('id', $id);
        return $this->db->delete('proposal_berkas_sidang_upload');
    }

    public function get_by_id($id) {
        $this->db->select('sbsu.*, sbs.nama_berkas, sbs.wajib');
        $this->db->from('proposal_berkas_sidang_upload sbsu');
        $this->db->join('proposal_berkas_sidang sbs', 'sbs.id = sbsu.id_berkas_sidang', 'left');
        $this->db->where('sbsu.id', $id);
        return $this->db->get()->row();
    }

    public function get_by_pendaftaran($id_pendaftaran_sidang) {
        $this->db->select('sbsu.*, sbs.nama_berkas, sbs.deskripsi, sbs.wajib, sbs.urutan');
        $this->db->from('proposal_berkas_sidang_upload sbsu');
        $this->db->join('proposal_berkas_sidang sbs', 'sbs.id = sbsu.id_berkas_sidang', 'left');
        $this->db->where('sbsu.id_pendaftaran_sidang', $id_pendaftaran_sidang);
        $this->db->order_by('sbs.urutan', 'ASC');
        return $this->db->get()->result();
    }

    public function get_by_pendaftaran_berkas($id_pendaftaran_sidang, $id_berkas_sidang) {
        $this->db->where('id_pendaftaran_sidang', $id_pendaftaran_sidang);
        $this->db->where('id_berkas_sidang', $id_berkas_sidang);
        return $this->db->get('proposal_berkas_sidang_upload')->row();
    }

    public function delete_by_pendaftaran($id_pendaftaran_sidang) {
        $uploads = $this->get_by_pendaftaran($id_pendaftaran_sidang);
        
        foreach ($uploads as $upload) {
            if (!empty($upload->file_path) && file_exists($upload->file_path)) {
                unlink($upload->file_path);
            }
        }
        
        $this->db->where('id_pendaftaran_sidang', $id_pendaftaran_sidang);
        return $this->db->delete('proposal_berkas_sidang_upload');
    }
}
