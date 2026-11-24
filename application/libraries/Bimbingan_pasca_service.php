<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Service helper: pastikan thread bimbingan pasca sidang ada + cek layak klaim.
 */
class Bimbingan_pasca_service {

    /** @var CI_Controller */
    protected $CI;

    public function __construct() {
        $this->CI =& get_instance();
        $this->CI->load->model('Skripsi_bimbingan_pasca_model');
        $this->CI->load->model('Skripsi_bimbingan_pasca_penguji_model');
        $this->CI->load->model('Pkl_bimbingan_pasca_model');
        $this->CI->load->model('Jadwal_skripsi_model');
        $this->CI->load->model('Jadwal_pkl_model');
    }

    /**
     * Pastikan thread skripsi pasca sidang ada untuk jadwal.
     * @return object|null bimbingan row
     */
    public function ensure_skripsi_by_jadwal($id_jadwal) {
        $jadwal = $this->CI->Jadwal_skripsi_model->get_by_id($id_jadwal);
        if (!$jadwal) {
            return null;
        }

        $existing = $this->CI->Skripsi_bimbingan_pasca_model->get_by_jadwal($jadwal->id);
        if ($existing) {
            $this->sync_skripsi_penguji($existing->id, $jadwal);
            return $this->CI->Skripsi_bimbingan_pasca_model->get_by_id($existing->id);
        }

        $id = $this->CI->Skripsi_bimbingan_pasca_model->create(array(
            'id_jadwal_skripsi' => (int) $jadwal->id,
            'id_mahasiswa_skripsi' => (int) $jadwal->id_mahasiswa_skripsi,
            'id_mahasiswa' => (int) $jadwal->id_mahasiswa,
            'id_tahun_akademik' => isset($jadwal->id_tahun_akademik) ? (int) $jadwal->id_tahun_akademik : null,
            'judul_topik' => 'Revisi Pasca Sidang Skripsi',
            'status' => 'open',
            'last_activity' => date('Y-m-d H:i:s'),
        ));
        if (!$id) {
            return null;
        }
        $this->sync_skripsi_penguji($id, $jadwal);
        return $this->CI->Skripsi_bimbingan_pasca_model->get_by_id($id);
    }

    protected function sync_skripsi_penguji($id_bimbingan, $jadwal) {
        $map = array(
            'ketua' => isset($jadwal->id_ketua_penguji) ? (int) $jadwal->id_ketua_penguji : 0,
            'anggota_1' => isset($jadwal->id_anggota_penguji_1) ? (int) $jadwal->id_anggota_penguji_1 : 0,
            'anggota_2' => isset($jadwal->id_anggota_penguji_2) ? (int) $jadwal->id_anggota_penguji_2 : 0,
        );
        foreach ($map as $peran => $id_dosen) {
            if ($id_dosen <= 0) {
                continue;
            }
            $row = $this->CI->Skripsi_bimbingan_pasca_penguji_model->get_by_bimbingan_dosen($id_bimbingan, $id_dosen);
            if (!$row) {
                $this->CI->Skripsi_bimbingan_pasca_penguji_model->create(array(
                    'id_bimbingan_pasca' => (int) $id_bimbingan,
                    'id_dosen' => $id_dosen,
                    'peran' => $peran,
                    'layak_klaim' => 0,
                ));
            }
        }
    }

    /**
     * Pastikan thread proposal pasca sidang ada untuk jadwal (2 penguji).
     * @return object|null bimbingan row
     */
    public function ensure_proposal_by_jadwal($id_jadwal) {
        $this->CI->load->model('Proposal_bimbingan_pasca_model');
        $this->CI->load->model('Proposal_bimbingan_pasca_penguji_model');
        $this->CI->load->model('Jadwal_proposal_model');

        $jadwal = $this->CI->Jadwal_proposal_model->get_by_id($id_jadwal);
        if (!$jadwal) {
            return null;
        }

        $existing = $this->CI->Proposal_bimbingan_pasca_model->get_by_jadwal($jadwal->id);
        if ($existing) {
            $this->sync_proposal_penguji($existing->id, $jadwal);
            return $this->CI->Proposal_bimbingan_pasca_model->get_by_id($existing->id);
        }

        $id = $this->CI->Proposal_bimbingan_pasca_model->create(array(
            'id_jadwal_proposal' => (int) $jadwal->id,
            'id_mahasiswa_proposal' => (int) $jadwal->id_mahasiswa_proposal,
            'id_mahasiswa' => (int) $jadwal->id_mahasiswa,
            'id_tahun_akademik' => isset($jadwal->id_tahun_akademik) ? (int) $jadwal->id_tahun_akademik : null,
            'judul_topik' => 'Revisi Pasca Sidang Proposal TA',
            'status' => 'open',
            'last_activity' => date('Y-m-d H:i:s'),
        ));
        if (!$id) {
            return null;
        }
        $this->sync_proposal_penguji($id, $jadwal);
        return $this->CI->Proposal_bimbingan_pasca_model->get_by_id($id);
    }

    protected function sync_proposal_penguji($id_bimbingan, $jadwal) {
        $map = array(
            'penguji_1' => isset($jadwal->id_penguji_1) ? (int) $jadwal->id_penguji_1 : 0,
            'penguji_2' => isset($jadwal->id_penguji_2) ? (int) $jadwal->id_penguji_2 : 0,
        );
        foreach ($map as $peran => $id_dosen) {
            if ($id_dosen <= 0) {
                continue;
            }
            $row = $this->CI->Proposal_bimbingan_pasca_penguji_model->get_by_bimbingan_dosen($id_bimbingan, $id_dosen);
            if (!$row) {
                $this->CI->Proposal_bimbingan_pasca_penguji_model->create(array(
                    'id_bimbingan_pasca' => (int) $id_bimbingan,
                    'id_dosen' => $id_dosen,
                    'peran' => $peran,
                    'layak_klaim' => 0,
                ));
            }
        }
    }

    /**
     * Status klaim nilai Proposal TA: OK bila semua penguji menyatakan layak klaim.
     * @return array{ok:bool,message:string,bimbingan:?object,layak:int,total:int}
     */
    public function proposal_klaim_status($id_mahasiswa_proposal) {
        $this->CI->load->model('Jadwal_proposal_model');
        $this->CI->load->model('Proposal_bimbingan_pasca_penguji_model');

        $jadwal_list = $this->CI->Jadwal_proposal_model->get_by_mahasiswa($id_mahasiswa_proposal);
        if (empty($jadwal_list)) {
            return array('ok' => false, 'message' => 'Jadwal sidang belum tersedia.', 'bimbingan' => null, 'layak' => 0, 'total' => 0);
        }
        $bimbingan = $this->ensure_proposal_by_jadwal($jadwal_list[0]->id);
        if (!$bimbingan) {
            return array('ok' => false, 'message' => 'Bimbingan pasca sidang belum tersedia.', 'bimbingan' => null, 'layak' => 0, 'total' => 0);
        }
        $total = $this->CI->Proposal_bimbingan_pasca_penguji_model->count_penguji($bimbingan->id);
        $layak = $this->CI->Proposal_bimbingan_pasca_penguji_model->count_layak($bimbingan->id);
        $ok = $total > 0 && $layak >= $total;
        return array(
            'ok' => $ok,
            'message' => $ok
                ? 'Semua penguji telah menyatakan layak klaim nilai.'
                : 'Menunggu penguji (' . $layak . '/' . $total . ') menyatakan layak klaim nilai.',
            'bimbingan' => $bimbingan,
            'layak' => $layak,
            'total' => $total,
        );
    }

    public function ensure_pkl_by_jadwal($id_jadwal) {
        $jadwal = $this->CI->Jadwal_pkl_model->get_by_id($id_jadwal);
        if (!$jadwal || empty($jadwal->id_penguji)) {
            return null;
        }

        $existing = $this->CI->Pkl_bimbingan_pasca_model->get_by_jadwal($jadwal->id);
        if ($existing) {
            if ((int) $existing->id_penguji !== (int) $jadwal->id_penguji) {
                $this->CI->Pkl_bimbingan_pasca_model->update($existing->id, array(
                    'id_penguji' => (int) $jadwal->id_penguji,
                ));
            }
            return $this->CI->Pkl_bimbingan_pasca_model->get_by_id($existing->id);
        }

        $id = $this->CI->Pkl_bimbingan_pasca_model->create(array(
            'id_jadwal_pkl' => (int) $jadwal->id,
            'id_mahasiswa' => (int) $jadwal->id_mahasiswa,
            'id_penguji' => (int) $jadwal->id_penguji,
            'id_tahun_akademik' => isset($jadwal->id_tahun_akademik) ? (int) $jadwal->id_tahun_akademik : null,
            'judul_topik' => 'Revisi Pasca Sidang PKL',
            'status' => 'open',
            'layak_klaim' => 0,
            'last_activity' => date('Y-m-d H:i:s'),
        ));
        return $id ? $this->CI->Pkl_bimbingan_pasca_model->get_by_id($id) : null;
    }

    /**
     * @return array{ok:bool,message:string,bimbingan:?object,layak:int,total:int}
     */
    public function skripsi_klaim_status($id_mahasiswa_skripsi) {
        $jadwal_list = $this->CI->Jadwal_skripsi_model->get_by_mahasiswa($id_mahasiswa_skripsi);
        if (empty($jadwal_list)) {
            return array('ok' => false, 'message' => 'Jadwal sidang belum tersedia.', 'bimbingan' => null, 'layak' => 0, 'total' => 0);
        }
        $jadwal = $jadwal_list[0];
        $bimbingan = $this->ensure_skripsi_by_jadwal($jadwal->id);
        if (!$bimbingan) {
            return array('ok' => false, 'message' => 'Bimbingan pasca sidang belum tersedia.', 'bimbingan' => null, 'layak' => 0, 'total' => 0);
        }
        $total = $this->CI->Skripsi_bimbingan_pasca_penguji_model->count_penguji($bimbingan->id);
        $layak = $this->CI->Skripsi_bimbingan_pasca_penguji_model->count_layak($bimbingan->id);
        $ok = $total > 0 && $layak >= $total;
        return array(
            'ok' => $ok,
            'message' => $ok
                ? 'Semua penguji telah menyatakan layak klaim nilai.'
                : 'Menunggu penguji (' . $layak . '/' . $total . ') menyatakan layak klaim nilai.',
            'bimbingan' => $bimbingan,
            'layak' => $layak,
            'total' => $total,
        );
    }

    /**
     * @return array{ok:bool,message:string,bimbingan:?object}
     */
    public function pkl_klaim_status($id_mahasiswa, $id_tahun_akademik = null) {
        $jadwal_list = $this->CI->Jadwal_pkl_model->get_by_mahasiswa($id_mahasiswa, $id_tahun_akademik);
        if (empty($jadwal_list)) {
            return array('ok' => false, 'message' => 'Jadwal sidang PKL belum tersedia.', 'bimbingan' => null);
        }
        $jadwal = $jadwal_list[0];
        $bimbingan = $this->ensure_pkl_by_jadwal($jadwal->id);
        if (!$bimbingan) {
            return array('ok' => false, 'message' => 'Bimbingan pasca sidang belum tersedia.', 'bimbingan' => null);
        }
        $ok = !empty($bimbingan->layak_klaim);
        return array(
            'ok' => $ok,
            'message' => $ok
                ? 'Penguji telah menyatakan layak klaim nilai.'
                : 'Menunggu penguji menyatakan layak klaim nilai.',
            'bimbingan' => $bimbingan,
        );
    }
}
