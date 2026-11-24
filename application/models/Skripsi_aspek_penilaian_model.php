<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Skripsi_aspek_penilaian_model extends CI_Model {

    public function __construct() {
        parent::__construct();
        $this->load->database();
    }

    public function create($data) {
        $this->db->insert('skripsi_aspek_penilaian', $data);
        return $this->db->insert_id();
    }

    public function update($id, $data) {
        $this->db->where('id', $id);
        $this->db->update('skripsi_aspek_penilaian', $data);
        return $this->db->affected_rows();
    }

    public function delete($id) {
        $this->db->where('id', $id);
        $this->db->delete('skripsi_aspek_penilaian');
        return $this->db->affected_rows();
    }

    public function get_by_id($id) {
        $this->db->select('*, 
                           kode as kode_aspek,
                           nama as nama_aspek,
                           tipe_penilai as ditunjukkan_untuk,
                           aktif as is_aktif');
        return $this->db->get_where('skripsi_aspek_penilaian', array('id' => $id))->row();
    }

    public function get_by_kode($kode) {
        $this->db->select('*, 
                           kode as kode_aspek,
                           nama as nama_aspek,
                           tipe_penilai as ditunjukkan_untuk,
                           aktif as is_aktif');
        return $this->db->get_where('skripsi_aspek_penilaian', array('kode' => $kode))->row();
    }

    public function get_by_tipe($tipe_penilai, $active_only = false) {
        $this->db->select('*, 
                           kode as kode_aspek,
                           nama as nama_aspek,
                           tipe_penilai as ditunjukkan_untuk,
                           aktif as is_aktif');
        $this->db->where('tipe_penilai', $tipe_penilai);
        if ($active_only) {
            $this->db->where('aktif', 1);
        }
        $this->db->order_by('kode', 'ASC');
        return $this->db->get('skripsi_aspek_penilaian')->result();
    }

    public function get_all($active_only = false) {
        $this->db->select('*, 
                           kode as kode_aspek,
                           nama as nama_aspek,
                           tipe_penilai as ditunjukkan_untuk,
                           aktif as is_aktif,
                           0 as bobot,
                           0 as urutan');
        if ($active_only) {
            $this->db->where('aktif', 1);
        }
        $this->db->order_by('kode', 'ASC');
        return $this->db->get('skripsi_aspek_penilaian')->result();
    }

    public function count_all($active_only = false) {
        if ($active_only) {
            $this->db->where('aktif', 1);
        }
        return $this->db->count_all_results('skripsi_aspek_penilaian');
    }
}

