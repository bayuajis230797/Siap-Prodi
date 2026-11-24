<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Pkm_anggota_model extends CI_Model {

    public function __construct() {
        parent::__construct();
        $this->load->database();
    }

    public function get_by_pengajuan($id_pengajuan) {
        $this->db->select('pa.*, m.nim, m.nama_mahasiswa, m.kelas');
        $this->db->from('pkm_anggota pa');
        $this->db->join('mahasiswa m', 'm.id = pa.id_mahasiswa', 'left');
        $this->db->where('pa.id_pkm_pengajuan', (int) $id_pengajuan);
        $this->db->order_by('pa.urutan', 'ASC');
        return $this->db->get()->result();
    }

    public function sync_anggota($id_pengajuan, $rows) {
        $id_pengajuan = (int) $id_pengajuan;
        $this->db->where('id_pkm_pengajuan', $id_pengajuan);
        $this->db->delete('pkm_anggota');
        if (empty($rows)) {
            return true;
        }
        foreach ($rows as $row) {
            $row['id_pkm_pengajuan'] = $id_pengajuan;
            $this->db->insert('pkm_anggota', $row);
        }
        return true;
    }

    public function count_ketua_per_periode($id_mahasiswa, $id_periode, $exclude_pengajuan_id = null) {
        $this->db->from('pkm_pengajuan');
        $this->db->where('id_ketua', (int) $id_mahasiswa);
        $this->db->where('id_pkm_periode', (int) $id_periode);
        $this->db->where('status_verifikasi_sekprodi !=', 'ditolak');
        if ($exclude_pengajuan_id) {
            $this->db->where('id !=', (int) $exclude_pengajuan_id);
        }
        return $this->db->count_all_results();
    }

    public function count_anggota_per_periode($id_mahasiswa, $id_periode, $exclude_pengajuan_id = null) {
        $this->db->from('pkm_anggota pa');
        $this->db->join('pkm_pengajuan pp', 'pp.id = pa.id_pkm_pengajuan', 'inner');
        $this->db->where('pa.id_mahasiswa', (int) $id_mahasiswa);
        $this->db->where('pa.tipe', 'internal');
        $this->db->where('pp.id_pkm_periode', (int) $id_periode);
        $this->db->where('pp.status_verifikasi_sekprodi !=', 'ditolak');
        if ($exclude_pengajuan_id) {
            $this->db->where('pp.id !=', (int) $exclude_pengajuan_id);
        }
        return $this->db->count_all_results();
    }

    public function is_mahasiswa_in_periode($id_mahasiswa, $id_periode, $exclude_pengajuan_id = null) {
        $id_mahasiswa = (int) $id_mahasiswa;
        $id_periode = (int) $id_periode;

        $this->db->from('pkm_pengajuan');
        $this->db->where('id_ketua', $id_mahasiswa);
        $this->db->where('id_pkm_periode', $id_periode);
        $this->db->where('status_verifikasi_sekprodi !=', 'ditolak');
        if ($exclude_pengajuan_id) {
            $this->db->where('id !=', (int) $exclude_pengajuan_id);
        }
        if ($this->db->count_all_results() > 0) {
            return true;
        }

        $this->db->from('pkm_anggota pa');
        $this->db->join('pkm_pengajuan pp', 'pp.id = pa.id_pkm_pengajuan', 'inner');
        $this->db->where('pa.id_mahasiswa', $id_mahasiswa);
        $this->db->where('pa.tipe', 'internal');
        $this->db->where('pp.id_pkm_periode', $id_periode);
        $this->db->where('pp.status_verifikasi_sekprodi !=', 'ditolak');
        if ($exclude_pengajuan_id) {
            $this->db->where('pp.id !=', (int) $exclude_pengajuan_id);
        }
        return $this->db->count_all_results() > 0;
    }
}
