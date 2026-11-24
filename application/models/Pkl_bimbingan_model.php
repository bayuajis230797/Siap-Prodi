<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Pkl_bimbingan_model extends CI_Model {

    public function __construct() {
        parent::__construct();
        $this->load->database();
    }

    public function create($data) {
        $this->db->insert('pkl_bimbingan', $data);
        return $this->db->insert_id();
    }

    public function update($id, $data) {
        $this->db->where('id', $id);
        $this->db->update('pkl_bimbingan', $data);
        return $this->db->affected_rows();
    }

    public function delete($id) {
        $this->db->where('id_bimbingan', $id);
        $this->db->delete('pkl_bimbingan_log');

        $this->db->where('id', $id);
        return $this->db->delete('pkl_bimbingan');
    }

    public function get_by_id($id) {
        $this->db->select('pb.*, m.nim, m.nama_mahasiswa, m.kelas, d.nama_dosen, d.nidn');
        $this->db->from('pkl_bimbingan pb');
        $this->db->join('mahasiswa m', 'm.id = pb.id_mahasiswa', 'left');
        $this->db->join('dosen d', 'd.id = pb.id_pembimbing', 'left');
        $this->db->where('pb.id', $id);
        return $this->db->get()->row();
    }

    public function get_by_mahasiswa($id_mahasiswa, $id_tahun_akademik = null) {
        $this->db->select('pb.*, d.nama_dosen, d.nidn');
        $this->db->from('pkl_bimbingan pb');
        $this->db->join('dosen d', 'd.id = pb.id_pembimbing', 'left');
        $this->db->where('pb.id_mahasiswa', $id_mahasiswa);
        if ($id_tahun_akademik) {
            $this->db->where('pb.id_tahun_akademik', $id_tahun_akademik);
        }
        $this->db->order_by('pb.last_activity', 'DESC');
        return $this->db->get()->result();
    }

    public function get_by_pembimbing($id_pembimbing, $status = null, $id_tahun_akademik = null) {
        $this->db->select('pb.*, m.nim, m.nama_mahasiswa, m.kelas');
        $this->db->from('pkl_bimbingan pb');
        $this->db->join('mahasiswa m', 'm.id = pb.id_mahasiswa', 'left');
        $this->db->where('pb.id_pembimbing', $id_pembimbing);
        if ($status) {
            $this->db->where('pb.status', $status);
        }
        if ($id_tahun_akademik) {
            $this->db->where('pb.id_tahun_akademik', $id_tahun_akademik);
        }
        $this->db->order_by('pb.last_activity', 'DESC');
        return $this->db->get()->result();
    }

    public function get_by_mahasiswa_pembimbing($id_mahasiswa, $id_pembimbing) {
        $this->db->where('id_mahasiswa', $id_mahasiswa);
        $this->db->where('id_pembimbing', $id_pembimbing);
        return $this->db->get('pkl_bimbingan')->row();
    }
}



