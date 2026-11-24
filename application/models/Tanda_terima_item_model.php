<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Tanda_terima_item_model extends CI_Model {

    public function __construct() {
        parent::__construct();
        $this->load->database();
    }

    public function get_by_distribusi($id_tanda_terima) {
        $this->db->where('id_tanda_terima', (int) $id_tanda_terima);
        $this->db->order_by('nama_barang', 'ASC');
        return $this->db->get('tanda_terima_item')->result();
    }

    public function get_by_id($id) {
        $this->db->where('id', (int) $id);
        return $this->db->get('tanda_terima_item')->row();
    }

    public function get_by_token($token) {
        $this->db->where('qr_code_token', $token);
        return $this->db->get('tanda_terima_item')->row();
    }

    public function get_with_distribusi_by_token($token) {
        $this->db->select('ti.*, tt.nama_distribusi, tt.jenis_penerima, tt.status as status_distribusi, tt.tanggal_mulai, tt.tanggal_selesai');
        $this->db->from('tanda_terima_item ti');
        $this->db->join('tanda_terima tt', 'tt.id = ti.id_tanda_terima', 'inner');
        $this->db->where('ti.qr_code_token', $token);
        return $this->db->get()->row();
    }

    public function generate_unique_token() {
        do {
            $token = 'TT_' . bin2hex(random_bytes(16));
            $this->db->where('qr_code_token', $token);
            $exists = $this->db->count_all_results('tanda_terima_item');
        } while ($exists > 0);
        return $token;
    }

    public function create($data) {
        $this->db->insert('tanda_terima_item', $data);
        return $this->db->insert_id();
    }

    public function update($id, $data) {
        $this->db->where('id', (int) $id);
        $this->db->update('tanda_terima_item', $data);
        return $this->db->affected_rows();
    }

    public function delete($id) {
        $this->db->where('id', (int) $id);
        $this->db->delete('tanda_terima_item');
        return $this->db->affected_rows();
    }

    public function count_by_distribusi($id_tanda_terima) {
        $this->db->where('id_tanda_terima', (int) $id_tanda_terima);
        return $this->db->count_all_results('tanda_terima_item');
    }
}
