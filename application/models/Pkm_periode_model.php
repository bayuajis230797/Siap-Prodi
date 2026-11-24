<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Pkm_periode_model extends CI_Model {

    public function __construct() {
        parent::__construct();
        $this->load->database();
    }

    public function get_all($status = null, $limit = 200, $offset = 0) {
        if ($status !== null && $status !== '') {
            $this->db->where('status', $status);
        }
        $this->db->order_by('created_at', 'DESC');
        if ($limit !== null) {
            $this->db->limit((int) $limit, (int) $offset);
        }
        return $this->db->get('pkm_periode')->result();
    }

    public function get_by_id($id) {
        $this->db->where('id', (int) $id);
        return $this->db->get('pkm_periode')->row();
    }

    public function create($data) {
        $this->db->insert('pkm_periode', $data);
        return $this->db->insert_id();
    }

    public function update($id, $data) {
        $this->db->where('id', (int) $id);
        $this->db->update('pkm_periode', $data);
        return $this->db->affected_rows();
    }

    public function delete($id) {
        $this->db->where('id', (int) $id);
        $this->db->delete('pkm_periode');
        return $this->db->affected_rows();
    }

    public function is_deadline_lewat($periode) {
        if (!$periode || empty($periode->deadline_pengajuan)) {
            return false;
        }
        return strtotime('today') > strtotime($periode->deadline_pengajuan);
    }

    public function is_pengajuan_allowed($periode) {
        if (!$periode || $periode->status !== 'aktif') {
            return false;
        }
        return !$this->is_deadline_lewat($periode);
    }

    public function is_proposal_final_deadline_lewat($periode) {
        if (!$periode || empty($periode->tanggal_selesai)) {
            return false;
        }
        return strtotime('today') > strtotime($periode->tanggal_selesai);
    }

    public function is_proposal_final_submit_allowed($periode) {
        if (!$periode || empty($periode->tanggal_selesai)) {
            return true;
        }
        return !$this->is_proposal_final_deadline_lewat($periode);
    }

    public function has_active_periode() {
        $this->db->where('status', 'aktif');
        return $this->db->count_all_results('pkm_periode') > 0;
    }

    public function get_active_periode() {
        $this->db->where('status', 'aktif');
        $this->db->order_by('deadline_pengajuan', 'ASC');
        return $this->db->get('pkm_periode')->result();
    }

    public function count_jenis_lomba($id_periode) {
        $this->db->where('id_pkm_periode', (int) $id_periode);
        $this->db->where('aktif', 1);
        return $this->db->count_all_results('pkm_jenis_lomba');
    }

    public function count_pengajuan($id_periode) {
        $this->db->where('id_pkm_periode', (int) $id_periode);
        return $this->db->count_all_results('pkm_pengajuan');
    }
}
