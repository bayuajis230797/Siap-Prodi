<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Pkl_aspek_penilaian_model extends CI_Model {

    public function __construct() {
        parent::__construct();
        $this->load->database();
    }

    public function create($data) {
        $this->db->insert('pkl_aspek_penilaian', $data);
        return $this->db->insert_id();
    }

    public function update($id, $data) {
        $this->db->where('id', $id);
        $this->db->update('pkl_aspek_penilaian', $data);
        return $this->db->affected_rows();
    }

    public function delete($id) {
        $this->db->where('id', $id);
        $this->db->delete('pkl_aspek_penilaian');
        return $this->db->affected_rows();
    }

    public function get_by_id($id) {
        return $this->db->get_where('pkl_aspek_penilaian', array('id' => $id))->row();
    }

    public function get_by_tipe($tipe_penilai, $active_only = false) {
        $this->db->where('ditunjukkan_untuk', $tipe_penilai);
        if ($active_only) {
            $this->db->where('is_aktif', 1);
        }
        $this->db->order_by('urutan', 'ASC');
        return $this->db->get('pkl_aspek_penilaian')->result();
    }

    public function get_all($active_only = false) {
        if ($active_only) {
            $this->db->where('is_aktif', 1);
        }
        $this->db->order_by('urutan', 'ASC');
        return $this->db->get('pkl_aspek_penilaian')->result();
    }

    public function get_by_kode($kode_aspek) {
        return $this->db->get_where('pkl_aspek_penilaian', array('kode_aspek' => $kode_aspek))->row();
    }

    public function get_by_ditunjukkan_untuk($ditunjukkan_untuk, $active_only = false) {
        // ditunjukkan_untuk adalah ENUM: 'pembimbing_dosen', 'pembimbing_lapangan', 'sekprodi', atau 'penguji'
        $this->db->where('ditunjukkan_untuk', $ditunjukkan_untuk);
        
        if ($active_only) {
            $this->db->where('is_aktif', 1);
        }
        
        $this->db->order_by('urutan', 'ASC');
        return $this->db->get('pkl_aspek_penilaian')->result();
    }
}

