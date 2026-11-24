<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Pkm_pengajuan_model extends CI_Model {

    public function __construct() {
        parent::__construct();
        $this->load->database();
    }

    private function _base_select() {
        $this->db->select('pp.*, pj.nama_jenis, pj.min_anggota, pj.max_anggota,
            pr.nama_periode, pr.deadline_pengajuan, pr.tanggal_selesai,
            mk.nim as ketua_nim, mk.nama_mahasiswa as ketua_nama, mk.kelas as ketua_kelas,
            dr.nama_dosen as pembimbing_request_nama,
            df.nama_dosen as pembimbing_final_nama');
        $this->db->from('pkm_pengajuan pp');
        $this->db->join('pkm_jenis_lomba pj', 'pj.id = pp.id_pkm_jenis', 'left');
        $this->db->join('pkm_periode pr', 'pr.id = pp.id_pkm_periode', 'left');
        $this->db->join('mahasiswa mk', 'mk.id = pp.id_ketua', 'left');
        $this->db->join('dosen dr', 'dr.id = pp.id_pembimbing_request', 'left');
        $this->db->join('dosen df', 'df.id = pp.id_pembimbing_final', 'left');
    }

    public function get_by_id($id) {
        $this->_base_select();
        $this->db->where('pp.id', (int) $id);
        return $this->db->get()->row();
    }

    public function get_by_ketua_periode($id_ketua, $id_periode) {
        $this->_base_select();
        $this->db->where('pp.id_ketua', (int) $id_ketua);
        $this->db->where('pp.id_pkm_periode', (int) $id_periode);
        $this->db->order_by('pp.created_at', 'DESC');
        return $this->db->get()->row();
    }

    public function get_by_pembimbing($id_pembimbing, $status_sekprodi = 'disetujui') {
        $this->_base_select();
        $this->db->where('pp.id_pembimbing_final', (int) $id_pembimbing);
        if ($status_sekprodi) {
            $this->db->where('pp.status_verifikasi_sekprodi', $status_sekprodi);
        }
        $this->db->order_by('pp.created_at', 'DESC');
        return $this->db->get()->result();
    }

    public function get_for_verifikasi($id_periode = null, $status = 'pending') {
        $this->_base_select();
        if ($id_periode) {
            $this->db->where('pp.id_pkm_periode', (int) $id_periode);
        }
        if ($status) {
            $this->db->where('pp.status_verifikasi_sekprodi', $status);
        }
        $this->db->order_by('pp.created_at', 'ASC');
        return $this->db->get()->result();
    }

    public function get_for_verifikasi_by_periode_ids(array $id_periode_list, $status = 'pending') {
        if (empty($id_periode_list)) {
            return array();
        }
        $this->_base_select();
        $this->db->where_in('pp.id_pkm_periode', array_map('intval', $id_periode_list));
        if ($status) {
            $this->db->where('pp.status_verifikasi_sekprodi', $status);
        }
        $this->db->order_by('pp.created_at', 'ASC');
        return $this->db->get()->result();
    }

    public function get_by_mahasiswa_in_active_periode($id_mahasiswa) {
        $id_mahasiswa = (int) $id_mahasiswa;
        $this->_base_select();
        $this->db->where('pr.status', 'aktif');
        $this->db->where('pp.status_verifikasi_sekprodi !=', 'ditolak');
        $this->db->group_start();
        $this->db->where('pp.id_ketua', $id_mahasiswa);
        $this->db->or_where(
            'EXISTS (SELECT 1 FROM pkm_anggota pa WHERE pa.id_pkm_pengajuan = pp.id AND pa.id_mahasiswa = '
            . $id_mahasiswa . " AND pa.tipe = 'internal')",
            null,
            false
        );
        $this->db->group_end();
        $this->db->order_by('pp.created_at', 'DESC');
        $this->db->limit(1);
        return $this->db->get()->row();
    }

    public function get_by_mahasiswa($id_mahasiswa) {
        $id_mahasiswa = (int) $id_mahasiswa;
        $this->_base_select();
        $this->db->where('pp.status_verifikasi_sekprodi !=', 'ditolak');
        $this->db->group_start();
        $this->db->where('pp.id_ketua', $id_mahasiswa);
        $this->db->or_where(
            'EXISTS (SELECT 1 FROM pkm_anggota pa WHERE pa.id_pkm_pengajuan = pp.id AND pa.id_mahasiswa = '
            . $id_mahasiswa . " AND pa.tipe = 'internal')",
            null,
            false
        );
        $this->db->group_end();
        $this->db->order_by('pp.created_at', 'DESC');
        $rows = $this->db->get()->result();
        foreach ($rows as $row) {
            $row->peran = ((int) $row->id_ketua === $id_mahasiswa) ? 'ketua' : 'anggota';
        }
        return $rows;
    }

    public function mahasiswa_akses_pengajuan($id_mahasiswa, $id_pengajuan) {
        $id_mahasiswa = (int) $id_mahasiswa;
        $pengajuan = $this->get_by_id($id_pengajuan);
        if (!$pengajuan) {
            return null;
        }
        if ((int) $pengajuan->id_ketua === $id_mahasiswa) {
            $pengajuan->peran = 'ketua';
            return $pengajuan;
        }
        $this->db->from('pkm_anggota');
        $this->db->where('id_pkm_pengajuan', (int) $id_pengajuan);
        $this->db->where('id_mahasiswa', $id_mahasiswa);
        $this->db->where('tipe', 'internal');
        if ($this->db->count_all_results() > 0) {
            $pengajuan->peran = 'anggota';
            return $pengajuan;
        }
        return null;
    }

    public function get_by_periode($id_periode) {
        $this->_base_select();
        $this->db->where('pp.id_pkm_periode', (int) $id_periode);
        $this->db->order_by('pp.created_at', 'DESC');
        return $this->db->get()->result();
    }

    public function create($data) {
        $this->db->insert('pkm_pengajuan', $data);
        return $this->db->insert_id();
    }

    public function update($id, $data) {
        $this->db->where('id', (int) $id);
        $this->db->update('pkm_pengajuan', $data);
        return $this->db->affected_rows();
    }

    public function get_for_verifikasi_proposal_final($status = 'pending') {
        $this->_base_select();
        $this->db->where('pp.proposal_final_aktif', 1);
        $this->db->where('pp.link_proposal_final IS NOT NULL', null, false);
        if ($status && $status !== 'all') {
            $this->db->where('pp.status_acc_proposal', $status);
        }
        $this->db->order_by('pp.updated_at', 'ASC');
        return $this->db->get()->result();
    }

    public function get_mahasiswa_terdaftar_export_rows(array $periode_ids) {
        if (empty($periode_ids)) {
            return array();
        }
        $ids = implode(',', array_map('intval', $periode_ids));
        $sql = "
            SELECT m.id AS id_mahasiswa, m.nim, m.nama_mahasiswa, m.kelas,
                'Ketua' AS peran, pr.nama_periode, pj.nama_jenis,
                COALESCE(pp.judul_disetujui, pp.judul_pkm) AS judul_pkm,
                mk.nama_mahasiswa AS ketua_nama, mk.nim AS ketua_nim,
                pp.status_verifikasi_sekprodi,
                COALESCE(df.nama_dosen, dr.nama_dosen) AS nama_pembimbing,
                pp.status_acc_proposal,
                CASE WHEN pp.link_proposal_final IS NOT NULL AND pp.link_proposal_final != '' THEN 'Sudah Submit' ELSE 'Belum Submit' END AS status_submit_proposal
            FROM pkm_pengajuan pp
            INNER JOIN mahasiswa m ON m.id = pp.id_ketua
            INNER JOIN pkm_periode pr ON pr.id = pp.id_pkm_periode
            INNER JOIN pkm_jenis_lomba pj ON pj.id = pp.id_pkm_jenis
            INNER JOIN mahasiswa mk ON mk.id = pp.id_ketua
            LEFT JOIN dosen dr ON dr.id = pp.id_pembimbing_request
            LEFT JOIN dosen df ON df.id = pp.id_pembimbing_final
            WHERE pp.id_pkm_periode IN ({$ids}) AND pp.status_verifikasi_sekprodi != 'ditolak'
            UNION ALL
            SELECT m.id AS id_mahasiswa, m.nim, m.nama_mahasiswa, m.kelas,
                'Anggota' AS peran, pr.nama_periode, pj.nama_jenis,
                COALESCE(pp.judul_disetujui, pp.judul_pkm) AS judul_pkm,
                mk.nama_mahasiswa AS ketua_nama, mk.nim AS ketua_nim,
                pp.status_verifikasi_sekprodi,
                COALESCE(df.nama_dosen, dr.nama_dosen) AS nama_pembimbing,
                pp.status_acc_proposal,
                CASE WHEN pp.link_proposal_final IS NOT NULL AND pp.link_proposal_final != '' THEN 'Sudah Submit' ELSE 'Belum Submit' END AS status_submit_proposal
            FROM pkm_anggota pa
            INNER JOIN pkm_pengajuan pp ON pp.id = pa.id_pkm_pengajuan
            INNER JOIN mahasiswa m ON m.id = pa.id_mahasiswa
            INNER JOIN pkm_periode pr ON pr.id = pp.id_pkm_periode
            INNER JOIN pkm_jenis_lomba pj ON pj.id = pp.id_pkm_jenis
            INNER JOIN mahasiswa mk ON mk.id = pp.id_ketua
            LEFT JOIN dosen dr ON dr.id = pp.id_pembimbing_request
            LEFT JOIN dosen df ON df.id = pp.id_pembimbing_final
            WHERE pa.tipe = 'internal'
                AND pp.id_pkm_periode IN ({$ids})
                AND pp.status_verifikasi_sekprodi != 'ditolak'
            ORDER BY nim ASC, peran ASC
        ";
        return $this->db->query($sql)->result();
    }

    public function get_mahasiswa_non_terdaftar_export_rows(array $periode_ids) {
        $registered = $this->get_mahasiswa_terdaftar_export_rows($periode_ids);
        $registered_ids = array();
        foreach ($registered as $row) {
            $registered_ids[(int) $row->id_mahasiswa] = true;
        }
        $this->db->select('id, nim, nama_mahasiswa, kelas');
        $this->db->from('mahasiswa');
        $this->db->where('status', 'aktif');
        if (!empty($registered_ids)) {
            $this->db->where_not_in('id', array_keys($registered_ids));
        }
        $this->db->order_by('nim', 'ASC');
        return $this->db->get()->result();
    }

    public function get_mahasiswa_submit_proposal_export_rows(array $periode_ids) {
        $rows = $this->get_mahasiswa_terdaftar_export_rows($periode_ids);
        return array_values(array_filter($rows, function ($row) {
            return ($row->status_submit_proposal ?? '') === 'Sudah Submit';
        }));
    }

    public function get_mahasiswa_belum_submit_proposal_export_rows(array $periode_ids) {
        $rows = $this->get_mahasiswa_terdaftar_export_rows($periode_ids);
        return array_values(array_filter($rows, function ($row) {
            return ($row->status_submit_proposal ?? '') !== 'Sudah Submit';
        }));
    }
}
