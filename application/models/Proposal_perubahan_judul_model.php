<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Proposal_perubahan_judul_model extends CI_Model {

    public function __construct() {
        parent::__construct();
        $this->load->database();
    }

    public function create($data) {
        $this->db->insert('proposal_perubahan_judul', $data);
        return $this->db->insert_id();
    }

    public function update($id, $data) {
        $this->db->where('id', $id);
        $this->db->update('proposal_perubahan_judul', $data);
        return $this->db->affected_rows();
    }

    public function delete($id) {
        $perubahan = $this->get_by_id($id);
        
        if ($perubahan && !empty($perubahan->file_ttd_persetujuan) && file_exists($perubahan->file_ttd_persetujuan)) {
            unlink($perubahan->file_ttd_persetujuan);
        }
        
        $this->db->where('id', $id);
        return $this->db->delete('proposal_perubahan_judul');
    }

    public function get_by_id($id) {
        $this->db->select("spj.*,
                          COALESCE(
                              (
                                  SELECT prev.judul_baru
                                  FROM proposal_perubahan_judul prev
                                  WHERE prev.id_pengajuan = spj.id_pengajuan
                                    AND prev.status_approve_sekprodi = 'acc'
                                    AND (
                                        prev.created_at < spj.created_at
                                        OR (prev.created_at = spj.created_at AND prev.id < spj.id)
                                    )
                                  ORDER BY prev.created_at DESC, prev.id DESC
                                  LIMIT 1
                              ),
                              sp.judul_pengajuan
                          ) as judul_lama,
                          m.nim, m.nama_mahasiswa, m.kelas", FALSE);
        $this->db->from('proposal_perubahan_judul spj');
        $this->db->join('proposal_pengajuan sp', 'sp.id = spj.id_pengajuan', 'left');
        $this->db->join('mahasiswa m', 'm.id = sp.id_mahasiswa', 'left');
        $this->db->where('spj.id', $id);
        return $this->db->get()->row();
    }

    public function get_by_pengajuan($id_pengajuan) {
        $this->db->where('id_pengajuan', $id_pengajuan);
        $this->db->order_by('created_at', 'DESC');
        return $this->db->get('proposal_perubahan_judul')->row(); // Return single row, not array
    }

    /**
     * Ambil perubahan judul terbaru berdasarkan status tertentu.
     */
    public function get_latest_by_pengajuan_status($id_pengajuan, $status) {
        $this->db->where('id_pengajuan', $id_pengajuan);
        $this->db->where('status_approve_sekprodi', $status);
        $this->db->order_by('created_at', 'DESC');
        $this->db->order_by('id', 'DESC');
        return $this->db->get('proposal_perubahan_judul')->row();
    }

    public function get_pending() {
        $this->db->select("spj.*,
                          COALESCE(
                              (
                                  SELECT prev.judul_baru
                                  FROM proposal_perubahan_judul prev
                                  WHERE prev.id_pengajuan = spj.id_pengajuan
                                    AND prev.status_approve_sekprodi = 'acc'
                                    AND (
                                        prev.created_at < spj.created_at
                                        OR (prev.created_at = spj.created_at AND prev.id < spj.id)
                                    )
                                  ORDER BY prev.created_at DESC, prev.id DESC
                                  LIMIT 1
                              ),
                              sp.judul_pengajuan
                          ) as judul_lama,
                          m.nim, m.nama_mahasiswa, m.kelas", FALSE);
        $this->db->from('proposal_perubahan_judul spj');
        $this->db->join('proposal_pengajuan sp', 'sp.id = spj.id_pengajuan', 'left');
        $this->db->join('mahasiswa m', 'm.id = sp.id_mahasiswa', 'left');
        $this->db->where('spj.status_approve_sekprodi', 'pending');
        $this->db->order_by('spj.created_at', 'DESC');
        return $this->db->get()->result();
    }

    public function get_all($filters = array()) {
        $this->db->select("spj.*,
                          COALESCE(
                              (
                                  SELECT prev.judul_baru
                                  FROM proposal_perubahan_judul prev
                                  WHERE prev.id_pengajuan = spj.id_pengajuan
                                    AND prev.status_approve_sekprodi = 'acc'
                                    AND (
                                        prev.created_at < spj.created_at
                                        OR (prev.created_at = spj.created_at AND prev.id < spj.id)
                                    )
                                  ORDER BY prev.created_at DESC, prev.id DESC
                                  LIMIT 1
                              ),
                              sp.judul_pengajuan
                          ) as judul_lama,
                          m.nim, m.nama_mahasiswa, m.kelas", FALSE);
        $this->db->from('proposal_perubahan_judul spj');
        $this->db->join('proposal_pengajuan sp', 'sp.id = spj.id_pengajuan', 'left');
        $this->db->join('mahasiswa m', 'm.id = sp.id_mahasiswa', 'left');
        
        if (!empty($filters['status'])) {
            $this->db->where('spj.status_approve_sekprodi', $filters['status']);
        }
        if (!empty($filters['id_tahun_akademik'])) {
            $this->db->where('sp.id_tahun_akademik', $filters['id_tahun_akademik']);
        }
        
        $this->db->order_by('spj.created_at', 'DESC');
        return $this->db->get()->result();
    }

    /**
     * Get all perubahan judul by mahasiswa id
     */
    public function get_by_mahasiswa($id_mahasiswa, $id_tahun_akademik = null) {
        $this->db->select("spj.*,
                          COALESCE(
                              (
                                  SELECT prev.judul_baru
                                  FROM proposal_perubahan_judul prev
                                  WHERE prev.id_pengajuan = spj.id_pengajuan
                                    AND prev.status_approve_sekprodi = 'acc'
                                    AND (
                                        prev.created_at < spj.created_at
                                        OR (prev.created_at = spj.created_at AND prev.id < spj.id)
                                    )
                                  ORDER BY prev.created_at DESC, prev.id DESC
                                  LIMIT 1
                              ),
                              sp.judul_pengajuan
                          ) as judul_lama,
                          sp.judul_disetujui as judul_terbaru,
                          m.nim, m.nama_mahasiswa, m.kelas", FALSE);
        $this->db->from('proposal_perubahan_judul spj');
        $this->db->join('proposal_pengajuan sp', 'sp.id = spj.id_pengajuan', 'left');
        $this->db->join('mahasiswa m', 'm.id = sp.id_mahasiswa', 'left');
        $this->db->where('sp.id_mahasiswa', $id_mahasiswa);
        
        if ($id_tahun_akademik) {
            $this->db->where('sp.id_tahun_akademik', $id_tahun_akademik);
        }
        
        $this->db->order_by('spj.created_at', 'DESC');
        return $this->db->get()->result();
    }
}

