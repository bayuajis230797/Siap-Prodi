<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Pkl_berkas_sidang_upload_model extends CI_Model {

    public function __construct() {
        parent::__construct();
        $this->load->database();
    }

    public function create($data) {
        $this->db->insert('pkl_berkas_sidang_upload', $data);
        return $this->db->insert_id();
    }

    public function update($id, $data) {
        $this->db->where('id', $id);
        $this->db->update('pkl_berkas_sidang_upload', $data);
        return $this->db->affected_rows();
    }

    public function delete($id) {
        $upload = $this->get_by_id($id);
        if ($upload && !empty($upload->file_path) && file_exists($upload->file_path)) {
            unlink($upload->file_path);
        }
        $this->db->where('id', $id);
        $this->db->delete('pkl_berkas_sidang_upload');
        return $this->db->affected_rows();
    }

    public function get_by_id($id) {
        $this->db->select('pbsu.*, pbs.nama_berkas, pbs.deskripsi, pbs.wajib');
        $this->db->from('pkl_berkas_sidang_upload pbsu');
        $this->db->join('pkl_berkas_sidang pbs', 'pbs.id = pbsu.id_berkas_sidang', 'left');
        $this->db->where('pbsu.id', $id);
        return $this->db->get()->row();
    }

    public function get_by_pendaftaran($id_pendaftaran_sidang) {
        $this->db->select('pbsu.*, pbs.nama_berkas, pbs.deskripsi, pbs.wajib, pbs.urutan');
        $this->db->from('pkl_berkas_sidang_upload pbsu');
        $this->db->join('pkl_berkas_sidang pbs', 'pbs.id = pbsu.id_berkas_sidang', 'left');
        $this->db->where('pbsu.id_pendaftaran_sidang', $id_pendaftaran_sidang);
        $this->db->order_by('pbs.urutan', 'ASC');
        return $this->db->get()->result();
    }

    public function get_by_pendaftaran_berkas($id_pendaftaran_sidang, $id_berkas_sidang) {
        $this->db->where('id_pendaftaran_sidang', $id_pendaftaran_sidang);
        $this->db->where('id_berkas_sidang', $id_berkas_sidang);
        return $this->db->get('pkl_berkas_sidang_upload')->row();
    }
}





