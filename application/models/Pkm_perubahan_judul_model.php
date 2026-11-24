<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Pkm_perubahan_judul_model extends CI_Model {

    public function __construct() {
        parent::__construct();
        $this->load->database();
    }

    public function create($data) {
        $this->db->insert('pkm_perubahan_judul', $data);
        return $this->db->insert_id();
    }

    public function update($id, $data) {
        $this->db->where('id', (int) $id);
        $this->db->update('pkm_perubahan_judul', $data);
        return $this->db->affected_rows();
    }

    public function get_by_id($id) {
        $this->db->select('ppj.*, pp.judul_pkm, pp.judul_disetujui, pp.id_pembimbing_final, pp.id_ketua,
            m.nim, m.nama_mahasiswa');
        $this->db->from('pkm_perubahan_judul ppj');
        $this->db->join('pkm_pengajuan pp', 'pp.id = ppj.id_pkm_pengajuan', 'left');
        $this->db->join('mahasiswa m', 'm.id = pp.id_ketua', 'left');
        $this->db->where('ppj.id', (int) $id);
        return $this->db->get()->row();
    }

    public function get_pending_by_pembimbing($id_pembimbing) {
        $this->db->select('ppj.*, pp.judul_pkm, pp.id_pembimbing_final, m.nim, m.nama_mahasiswa');
        $this->db->from('pkm_perubahan_judul ppj');
        $this->db->join('pkm_pengajuan pp', 'pp.id = ppj.id_pkm_pengajuan', 'inner');
        $this->db->join('mahasiswa m', 'm.id = pp.id_ketua', 'left');
        $this->db->where('pp.id_pembimbing_final', (int) $id_pembimbing);
        $this->db->where('ppj.status_acc_dosen', 'pending');
        $this->db->order_by('ppj.created_at', 'ASC');
        return $this->db->get()->result();
    }

    public function get_by_pengajuan($id_pengajuan) {
        $this->db->where('id_pkm_pengajuan', (int) $id_pengajuan);
        $this->db->order_by('created_at', 'DESC');
        return $this->db->get('pkm_perubahan_judul')->result();
    }

    public function has_pending($id_pengajuan) {
        $this->db->where('id_pkm_pengajuan', (int) $id_pengajuan);
        $this->db->where('status_acc_dosen', 'pending');
        return $this->db->count_all_results('pkm_perubahan_judul') > 0;
    }
}
