<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Skpi_jenis_berkas_model extends CI_Model {

    public function __construct() {
        parent::__construct();
        $this->load->database();
    }

    public function get_by_periode($id_periode, $aktif_only = false) {
        $this->db->where('id_skpi_periode', (int) $id_periode);
        if ($aktif_only) {
            $this->db->where('aktif', 1);
        }
        $this->db->order_by('urutan', 'ASC');
        $this->db->order_by('id', 'ASC');
        return $this->db->get('skpi_jenis_berkas')->result();
    }

    public function get_by_id($id) {
        $this->db->where('id', (int) $id);
        return $this->db->get('skpi_jenis_berkas')->row();
    }

    public function create($data) {
        $this->db->insert('skpi_jenis_berkas', $data);
        return $this->db->insert_id();
    }

    public function update($id, $data) {
        $this->db->where('id', (int) $id);
        $this->db->update('skpi_jenis_berkas', $data);
        return $this->db->affected_rows();
    }

    public function delete($id) {
        $this->db->where('id', (int) $id);
        $this->db->delete('skpi_jenis_berkas');
        return $this->db->affected_rows();
    }
}
