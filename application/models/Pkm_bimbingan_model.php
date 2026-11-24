<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Pkm_bimbingan_model extends CI_Model {

    public function __construct() {
        parent::__construct();
        $this->load->database();
    }

    public function create($data) {
        $this->db->insert('pkm_bimbingan', $data);
        return $this->db->insert_id();
    }

    public function update($id, $data) {
        $this->db->where('id', (int) $id);
        $this->db->update('pkm_bimbingan', $data);
        return $this->db->affected_rows();
    }

    public function get_by_id($id) {
        $this->db->select('pb.*, m.nim, m.nama_mahasiswa, m.kelas, d.nama_dosen, d.nidn, pp.judul_pkm, pp.judul_disetujui, pp.proposal_final_aktif, pp.status_acc_proposal');
        $this->db->from('pkm_bimbingan pb');
        $this->db->join('mahasiswa m', 'm.id = pb.id_mahasiswa', 'left');
        $this->db->join('dosen d', 'd.id = pb.id_pembimbing', 'left');
        $this->db->join('pkm_pengajuan pp', 'pp.id = pb.id_pkm_pengajuan', 'left');
        $this->db->where('pb.id', (int) $id);
        return $this->db->get()->row();
    }

    public function get_list_by_pengajuan($id_pengajuan, $status = null) {
        $this->db->select('pb.*, d.nama_dosen, pp.judul_pkm, pp.judul_disetujui');
        $this->db->from('pkm_bimbingan pb');
        $this->db->join('dosen d', 'd.id = pb.id_pembimbing', 'left');
        $this->db->join('pkm_pengajuan pp', 'pp.id = pb.id_pkm_pengajuan', 'left');
        $this->db->where('pb.id_pkm_pengajuan', (int) $id_pengajuan);
        if ($status) {
            $this->db->where('pb.status', $status);
        }
        $this->db->order_by('pb.last_activity', 'DESC');
        return $this->db->get()->result();
    }

    public function get_by_mahasiswa_pengajuan($id_mahasiswa, $id_pengajuan, $status = null) {
        $this->db->select('pb.*, d.nama_dosen');
        $this->db->from('pkm_bimbingan pb');
        $this->db->join('dosen d', 'd.id = pb.id_pembimbing', 'left');
        $this->db->where('pb.id_mahasiswa', (int) $id_mahasiswa);
        $this->db->where('pb.id_pkm_pengajuan', (int) $id_pengajuan);
        if ($status) {
            $this->db->where('pb.status', $status);
        }
        $this->db->order_by('pb.last_activity', 'DESC');
        return $this->db->get()->result();
    }

    public function get_by_pembimbing($id_pembimbing, $status = null) {
        $this->db->select('pb.*, m.nim, m.nama_mahasiswa, m.kelas, pp.judul_pkm, pp.judul_disetujui, pp.status_acc_proposal, pp.proposal_final_aktif');
        $this->db->from('pkm_bimbingan pb');
        $this->db->join('mahasiswa m', 'm.id = pb.id_mahasiswa', 'left');
        $this->db->join('pkm_pengajuan pp', 'pp.id = pb.id_pkm_pengajuan', 'left');
        $this->db->where('pb.id_pembimbing', (int) $id_pembimbing);
        if ($status) {
            $this->db->where('pb.status', $status);
        }
        $this->db->order_by('pb.last_activity', 'DESC');
        return $this->db->get()->result();
    }

    public function has_pending_topic($id_mahasiswa, $id_pembimbing, $id_pengajuan) {
        $this->db->select('pb.id as id_bimbingan');
        $this->db->from('pkm_bimbingan pb');
        $this->db->where('pb.id_mahasiswa', (int) $id_mahasiswa);
        $this->db->where('pb.id_pembimbing', (int) $id_pembimbing);
        $this->db->where('pb.id_pkm_pengajuan', (int) $id_pengajuan);
        $this->db->where('pb.status', 'open');
        $bimbingan_list = $this->db->get()->result();

        if (empty($bimbingan_list)) {
            return false;
        }

        foreach ($bimbingan_list as $bimbingan) {
            $log_mahasiswa = $this->db->select('pbl.id, pbl.created_at')
                ->from('pkm_bimbingan_log pbl')
                ->where('pbl.id_bimbingan', $bimbingan->id_bimbingan)
                ->where('pbl.role_pengguna', 'mahasiswa')
                ->where('pbl.status_revisi IS NULL', null, false)
                ->order_by('pbl.created_at', 'DESC')
                ->limit(1)
                ->get()->row();

            if (!$log_mahasiswa) {
                continue;
            }

            $log_acc_dosen = $this->db->select('pbl.id')
                ->from('pkm_bimbingan_log pbl')
                ->where('pbl.id_bimbingan', $bimbingan->id_bimbingan)
                ->where('pbl.role_pengguna', 'dosen')
                ->where('pbl.status_revisi', 'acc')
                ->where('pbl.created_at >=', $log_mahasiswa->created_at)
                ->limit(1)
                ->get()->row();

            if (!$log_acc_dosen) {
                $log_revisi_dosen = $this->db->select('pbl.id, pbl.created_at')
                    ->from('pkm_bimbingan_log pbl')
                    ->where('pbl.id_bimbingan', $bimbingan->id_bimbingan)
                    ->where('pbl.role_pengguna', 'dosen')
                    ->where('pbl.status_revisi', 'revisi')
                    ->where('pbl.created_at >=', $log_mahasiswa->created_at)
                    ->order_by('pbl.created_at', 'DESC')
                    ->limit(1)
                    ->get()->row();

                if (!$log_revisi_dosen) {
                    return true;
                }

                $log_perbaikan = $this->db->select('pbl.id, pbl.created_at')
                    ->from('pkm_bimbingan_log pbl')
                    ->where('pbl.id_bimbingan', $bimbingan->id_bimbingan)
                    ->where('pbl.role_pengguna', 'mahasiswa')
                    ->where('pbl.created_at >', $log_revisi_dosen->created_at)
                    ->order_by('pbl.created_at', 'ASC')
                    ->limit(1)
                    ->get()->row();

                if (!$log_perbaikan) {
                    return true;
                }

                $log_acc_perbaikan = $this->db->select('pbl.id')
                    ->from('pkm_bimbingan_log pbl')
                    ->where('pbl.id_bimbingan', $bimbingan->id_bimbingan)
                    ->where('pbl.role_pengguna', 'dosen')
                    ->where('pbl.status_revisi', 'acc')
                    ->where('pbl.created_at >=', $log_perbaikan->created_at)
                    ->limit(1)
                    ->get()->row();

                if (!$log_acc_perbaikan) {
                    return true;
                }
            }
        }

        return false;
    }
}
