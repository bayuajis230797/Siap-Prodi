<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Pkm_jenis_lomba_model extends CI_Model {

    public function __construct() {
        parent::__construct();
        $this->load->database();
    }

    public function get_by_periode($id_periode, $only_aktif = true) {
        $this->db->where('id_pkm_periode', (int) $id_periode);
        if ($only_aktif) {
            $this->db->where('aktif', 1);
        }
        $this->db->order_by('urutan', 'ASC');
        $this->db->order_by('nama_jenis', 'ASC');
        return $this->db->get('pkm_jenis_lomba')->result();
    }

    public function get_by_id($id) {
        $this->db->where('id', (int) $id);
        return $this->db->get('pkm_jenis_lomba')->row();
    }

    public function create($data) {
        $this->db->insert('pkm_jenis_lomba', $data);
        return $this->db->insert_id();
    }

    public function update($id, $data) {
        $this->db->where('id', (int) $id);
        $this->db->update('pkm_jenis_lomba', $data);
        return $this->db->affected_rows();
    }

    public function delete($id) {
        $this->db->where('id', (int) $id);
        $this->db->delete('pkm_jenis_lomba');
        return $this->db->affected_rows();
    }
}
