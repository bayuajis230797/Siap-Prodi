<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Skpi_upload_model extends CI_Model {

    public function __construct() {
        parent::__construct();
        $this->load->database();
    }

    public function get_by_id($id) {
        $this->db->select('u.*, j.nama_berkas, j.id_skpi_periode, m.nim, m.nama_mahasiswa, m.kelas');
        $this->db->from('skpi_upload u');
        $this->db->join('skpi_jenis_berkas j', 'j.id = u.id_skpi_jenis_berkas', 'left');
        $this->db->join('mahasiswa m', 'm.id = u.id_mahasiswa', 'left');
        $this->db->where('u.id', (int) $id);
        return $this->db->get()->row();
    }

    public function create($data) {
        $this->db->insert('skpi_upload', $data);
        return $this->db->insert_id();
    }

    public function update($id, $data) {
        $this->db->where('id', (int) $id);
        $this->db->update('skpi_upload', $data);
        return $this->db->affected_rows();
    }

    public function delete($id) {
        $this->db->where('id', (int) $id);
        $this->db->delete('skpi_upload');
        return $this->db->affected_rows();
    }

    public function get_by_mahasiswa_periode($id_periode, $id_mahasiswa) {
        $this->db->select('u.*, j.nama_berkas, j.urutan, j.petunjuk');
        $this->db->from('skpi_upload u');
        $this->db->join('skpi_jenis_berkas j', 'j.id = u.id_skpi_jenis_berkas', 'inner');
        $this->db->where('u.id_skpi_periode', (int) $id_periode);
        $this->db->where('u.id_mahasiswa', (int) $id_mahasiswa);
        $this->db->order_by('j.urutan', 'ASC');
        $this->db->order_by('u.created_at', 'ASC');
        return $this->db->get()->result();
    }

    public function get_by_mahasiswa_jenis($id_periode, $id_mahasiswa, $id_jenis) {
        $this->db->where('id_skpi_periode', (int) $id_periode);
        $this->db->where('id_mahasiswa', (int) $id_mahasiswa);
        $this->db->where('id_skpi_jenis_berkas', (int) $id_jenis);
        $this->db->order_by('created_at', 'ASC');
        return $this->db->get('skpi_upload')->result();
    }

    public function get_for_verifikasi($id_periode, $id_mahasiswa) {
        return $this->get_by_mahasiswa_periode($id_periode, $id_mahasiswa);
    }

    public function count_by_status_periode($id_periode, $status = null) {
        $this->db->where('id_skpi_periode', (int) $id_periode);
        if ($status !== null) {
            $this->db->where('status', $status);
        }
        return $this->db->count_all_results('skpi_upload');
    }

    public function get_export_rows($id_periode, $kelas = null, $status_filter = null) {
        $this->db->select('u.*, j.nama_berkas, m.nim, m.nama_mahasiswa, m.kelas, p.nama_periode');
        $this->db->from('skpi_upload u');
        $this->db->join('skpi_jenis_berkas j', 'j.id = u.id_skpi_jenis_berkas', 'inner');
        $this->db->join('mahasiswa m', 'm.id = u.id_mahasiswa', 'inner');
        $this->db->join('skpi_periode p', 'p.id = u.id_skpi_periode', 'inner');
        $this->db->where('u.id_skpi_periode', (int) $id_periode);
        if ($kelas !== null && $kelas !== '') {
            $this->db->where('m.kelas', $kelas);
        }
        if ($status_filter !== null && $status_filter !== '') {
            $this->db->where('u.status', $status_filter);
        }
        $this->db->order_by('m.kelas', 'ASC');
        $this->db->order_by('m.nim', 'ASC');
        $this->db->order_by('j.urutan', 'ASC');
        $this->db->order_by('u.created_at', 'ASC');
        return $this->db->get()->result();
    }
}
