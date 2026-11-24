<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Proposal_pembimbing_model
 * Daftar dosen yang boleh menjadi pembimbing Proposal TA (kolom dosen.is_pembimbing_proposal).
 */
class Proposal_pembimbing_model extends CI_Model {

    public function __construct() {
        parent::__construct();
        $this->load->database();
    }

    /**
     * Dosen aktif yang boleh menjadi pembimbing Proposal TA
     */
    public function get_allowed() {
        $this->db->select('d.id as id_dosen, d.id, d.nama_dosen, d.nidn, d.status, d.is_pembimbing_proposal');
        $this->db->from('dosen d');
        $this->db->where('d.is_pembimbing_proposal', 1);
        $this->db->where('d.status', 'aktif');
        $this->db->order_by('d.nama_dosen', 'ASC');
        return $this->db->get()->result();
    }

    public function is_allowed($id_dosen) {
        $this->db->where('id', $id_dosen);
        $this->db->where('is_pembimbing_proposal', 1);
        $this->db->where('status', 'aktif');
        return $this->db->get('dosen')->row() ? true : false;
    }

    /**
     * Semua dosen aktif beserta flag pembimbing proposal (untuk layar pengaturan)
     */
    public function get_all_with_flag() {
        $this->db->select('d.id, d.nama_dosen, d.nidn, d.status, d.is_pembimbing_proposal');
        $this->db->from('dosen d');
        $this->db->where('d.status', 'aktif');
        $this->db->order_by('d.nama_dosen', 'ASC');
        return $this->db->get()->result();
    }

    public function set_flag($id_dosen, $is_pembimbing) {
        $this->db->where('id', $id_dosen);
        $this->db->update('dosen', array('is_pembimbing_proposal' => $is_pembimbing ? 1 : 0));
        return $this->db->affected_rows() >= 0;
    }

    /**
     * Simpan daftar dosen pembimbing Proposal TA sekaligus.
     * Dosen aktif yang id-nya ada di $ids diberi flag 1, lainnya 0.
     * @param int[] $ids
     */
    public function save_allowed_ids($ids) {
        $ids = array_values(array_unique(array_filter(array_map('intval', (array) $ids))));

        $this->db->trans_start();
        $this->db->where('status', 'aktif');
        $this->db->update('dosen', array('is_pembimbing_proposal' => 0));
        if (!empty($ids)) {
            $this->db->where_in('id', $ids);
            $this->db->where('status', 'aktif');
            $this->db->update('dosen', array('is_pembimbing_proposal' => 1));
        }
        $this->db->trans_complete();
        return $this->db->trans_status();
    }
}
