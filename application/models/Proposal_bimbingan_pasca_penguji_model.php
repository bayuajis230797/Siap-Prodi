<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Proposal_bimbingan_pasca_penguji_model extends CI_Model {

    public function __construct() {
        parent::__construct();
        $this->load->database();
    }

    public function create($data) {
        $this->db->insert('proposal_bimbingan_pasca_penguji', $data);
        return $this->db->insert_id();
    }

    public function get_by_bimbingan($id_bimbingan_pasca) {
        $this->db->select('sbpp.*, d.nama_dosen, d.nidn');
        $this->db->from('proposal_bimbingan_pasca_penguji sbpp');
        $this->db->join('dosen d', 'd.id = sbpp.id_dosen', 'left');
        $this->db->where('sbpp.id_bimbingan_pasca', (int) $id_bimbingan_pasca);
        $this->db->order_by('sbpp.peran', 'ASC');
        return $this->db->get()->result();
    }

    public function get_by_bimbingan_dosen($id_bimbingan_pasca, $id_dosen) {
        $this->db->where('id_bimbingan_pasca', (int) $id_bimbingan_pasca);
        $this->db->where('id_dosen', (int) $id_dosen);
        return $this->db->get('proposal_bimbingan_pasca_penguji')->row();
    }

    public function set_layak_klaim($id_bimbingan_pasca, $id_dosen) {
        $this->db->where('id_bimbingan_pasca', (int) $id_bimbingan_pasca);
        $this->db->where('id_dosen', (int) $id_dosen);
        $this->db->update('proposal_bimbingan_pasca_penguji', array(
            'layak_klaim' => 1,
            'layak_klaim_at' => date('Y-m-d H:i:s'),
        ));
        return $this->db->affected_rows();
    }

    public function is_all_layak($id_bimbingan_pasca) {
        $rows = $this->get_by_bimbingan($id_bimbingan_pasca);
        if (empty($rows)) {
            return false;
        }
        foreach ($rows as $r) {
            if (empty($r->layak_klaim)) {
                return false;
            }
        }
        return true;
    }

    public function count_layak($id_bimbingan_pasca) {
        $this->db->where('id_bimbingan_pasca', (int) $id_bimbingan_pasca);
        $this->db->where('layak_klaim', 1);
        return $this->db->count_all_results('proposal_bimbingan_pasca_penguji');
    }

    public function count_penguji($id_bimbingan_pasca) {
        $this->db->where('id_bimbingan_pasca', (int) $id_bimbingan_pasca);
        return $this->db->count_all_results('proposal_bimbingan_pasca_penguji');
    }
}
