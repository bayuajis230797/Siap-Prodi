<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Pkl_bimbingan_pasca_model extends CI_Model {

    public function __construct() {
        parent::__construct();
        $this->load->database();
    }

    public function create($data) {
        $this->db->insert('pkl_bimbingan_pasca', $data);
        return $this->db->insert_id();
    }

    public function update($id, $data) {
        $this->db->where('id', (int) $id);
        $this->db->update('pkl_bimbingan_pasca', $data);
        return $this->db->affected_rows();
    }

    public function get_by_id($id) {
        $this->db->select('pbp.*, m.nim, m.nama_mahasiswa, m.kelas, jp.tanggal, jp.ruangan, jp.status as status_jadwal, d.nama_dosen as nama_penguji');
        $this->db->from('pkl_bimbingan_pasca pbp');
        $this->db->join('mahasiswa m', 'm.id = pbp.id_mahasiswa', 'left');
        $this->db->join('jadwal_pkl jp', 'jp.id = pbp.id_jadwal_pkl', 'left');
        $this->db->join('dosen d', 'd.id = pbp.id_penguji', 'left');
        $this->db->where('pbp.id', (int) $id);
        return $this->db->get()->row();
    }

    public function get_by_jadwal($id_jadwal) {
        $this->db->where('id_jadwal_pkl', (int) $id_jadwal);
        return $this->db->get('pkl_bimbingan_pasca')->row();
    }

    public function get_by_mahasiswa($id_mahasiswa, $id_tahun_akademik = null) {
        $this->db->select('pbp.*, jp.tanggal, jp.ruangan, d.nama_dosen as nama_penguji');
        $this->db->from('pkl_bimbingan_pasca pbp');
        $this->db->join('jadwal_pkl jp', 'jp.id = pbp.id_jadwal_pkl', 'left');
        $this->db->join('dosen d', 'd.id = pbp.id_penguji', 'left');
        $this->db->where('pbp.id_mahasiswa', (int) $id_mahasiswa);
        if ($id_tahun_akademik) {
            $this->db->where('pbp.id_tahun_akademik', (int) $id_tahun_akademik);
        }
        $this->db->order_by('pbp.last_activity', 'DESC');
        return $this->db->get()->result();
    }

    public function get_by_penguji($id_dosen, $id_tahun_akademik = null) {
        $this->db->select('pbp.*, m.nim, m.nama_mahasiswa, m.kelas, jp.tanggal, jp.ruangan');
        $this->db->from('pkl_bimbingan_pasca pbp');
        $this->db->join('mahasiswa m', 'm.id = pbp.id_mahasiswa', 'left');
        $this->db->join('jadwal_pkl jp', 'jp.id = pbp.id_jadwal_pkl', 'left');
        $this->db->where('pbp.id_penguji', (int) $id_dosen);
        if ($id_tahun_akademik) {
            $this->db->where('pbp.id_tahun_akademik', (int) $id_tahun_akademik);
        }
        $this->db->order_by('pbp.last_activity', 'DESC');
        return $this->db->get()->result();
    }

    public function set_layak_klaim($id) {
        $this->db->where('id', (int) $id);
        $this->db->update('pkl_bimbingan_pasca', array(
            'layak_klaim' => 1,
            'layak_klaim_at' => date('Y-m-d H:i:s'),
        ));
        return $this->db->affected_rows();
    }
}
