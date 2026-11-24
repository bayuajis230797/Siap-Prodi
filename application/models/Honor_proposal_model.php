<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Honor_proposal_model
 * Rekap jumlah tugas dosen pada Proposal TA untuk perhitungan honor:
 *  - pembimbing : jumlah mahasiswa bimbingan (status bukan batal)
 *  - penguji    : jumlah sidang sebagai Penguji 1 / Penguji 2 (jadwal bukan batal)
 */
class Honor_proposal_model extends CI_Model {

    public function __construct() {
        parent::__construct();
        $this->load->database();
    }

    /** @return array id_dosen => object(nama_dosen, nidn, jumlah_bimbingan) */
    public function get_jumlah_pembimbing($id_tahun_akademik = null) {
        $this->db->select('d.id as id_dosen, d.nama_dosen, d.nidn, COUNT(ms.id) as jumlah_bimbingan', false);
        $this->db->from('mahasiswa_proposal ms');
        $this->db->join('dosen d', 'd.id = ms.id_pembimbing');
        $this->db->where('ms.id_pembimbing IS NOT NULL', null, false);
        $this->db->where('ms.status !=', 'batal');
        $this->db->where('d.status', 'aktif');
        if ($id_tahun_akademik) {
            $this->db->where('ms.id_tahun_akademik', $id_tahun_akademik);
        }
        $this->db->group_by('d.id, d.nama_dosen, d.nidn');
        $out = array();
        foreach ($this->db->get()->result() as $r) {
            $out[$r->id_dosen] = $r;
        }
        return $out;
    }

    /**
     * @param string $kolom 'id_penguji_1' atau 'id_penguji_2'
     * @return array id_dosen => object(nama_dosen, nidn, jumlah)
     */
    public function get_jumlah_penguji($kolom, $id_tahun_akademik = null) {
        if (!in_array($kolom, array('id_penguji_1', 'id_penguji_2'), true)) {
            return array();
        }
        $this->db->select('d.id as id_dosen, d.nama_dosen, d.nidn, COUNT(js.id) as jumlah', false);
        $this->db->from('jadwal_proposal js');
        $this->db->join('dosen d', 'd.id = js.' . $kolom);
        $this->db->where('js.' . $kolom . ' IS NOT NULL', null, false);
        $this->db->where('js.status !=', 'batal');
        $this->db->where('d.status', 'aktif');
        if ($id_tahun_akademik) {
            $this->db->where('js.id_tahun_akademik', $id_tahun_akademik);
        }
        $this->db->group_by('d.id, d.nama_dosen, d.nidn');
        $out = array();
        foreach ($this->db->get()->result() as $r) {
            $out[$r->id_dosen] = $r;
        }
        return $out;
    }

    /** Mahasiswa yang dibimbing seorang dosen. */
    public function get_mahasiswa_bimbingan($id_dosen, $id_tahun_akademik = null) {
        $this->db->select('ms.id, ms.status, ms.judul_proposal, m.nim, m.nama_mahasiswa, m.kelas');
        $this->db->from('mahasiswa_proposal ms');
        $this->db->join('mahasiswa m', 'm.id = ms.id_mahasiswa', 'left');
        $this->db->where('ms.id_pembimbing', $id_dosen);
        $this->db->where('ms.status !=', 'batal');
        if ($id_tahun_akademik) {
            $this->db->where('ms.id_tahun_akademik', $id_tahun_akademik);
        }
        $this->db->order_by('m.nama_mahasiswa', 'ASC');
        return $this->db->get()->result();
    }

    /** Sidang yang diuji seorang dosen (dengan peran). */
    public function get_sidang_penguji($id_dosen, $id_tahun_akademik = null) {
        $this->db->select('js.id, js.tanggal_ujian, js.jam_mulai, js.ruangan, js.status, js.id_penguji_1, js.id_penguji_2, m.nim, m.nama_mahasiswa', false);
        $this->db->from('jadwal_proposal js');
        $this->db->join('mahasiswa_proposal ms', 'ms.id = js.id_mahasiswa_proposal', 'left');
        $this->db->join('mahasiswa m', 'm.id = ms.id_mahasiswa', 'left');
        $this->db->where('js.status !=', 'batal');
        $this->db->group_start();
        $this->db->where('js.id_penguji_1', $id_dosen);
        $this->db->or_where('js.id_penguji_2', $id_dosen);
        $this->db->group_end();
        if ($id_tahun_akademik) {
            $this->db->where('js.id_tahun_akademik', $id_tahun_akademik);
        }
        $this->db->order_by('js.tanggal_ujian', 'ASC');
        $rows = $this->db->get()->result();
        foreach ($rows as $r) {
            $r->peran = ((int) $r->id_penguji_1 === (int) $id_dosen) ? 'Penguji 1' : 'Penguji 2';
        }
        return $rows;
    }
}
