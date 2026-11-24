<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Skpi_periode_model extends CI_Model {

    public function __construct() {
        parent::__construct();
        $this->load->database();
    }

    public function get_all($status = null, $limit = 200, $offset = 0) {
        if ($status !== null && $status !== '') {
            $this->db->where('status', $status);
        }
        $this->db->order_by('created_at', 'DESC');
        if ($limit !== null) {
            $this->db->limit((int) $limit, (int) $offset);
        }
        return $this->db->get('skpi_periode')->result();
    }

    public function get_by_id($id) {
        $this->db->where('id', (int) $id);
        return $this->db->get('skpi_periode')->row();
    }

    public function create($data) {
        $this->db->insert('skpi_periode', $data);
        return $this->db->insert_id();
    }

    public function update($id, $data) {
        $this->db->where('id', (int) $id);
        $this->db->update('skpi_periode', $data);
        return $this->db->affected_rows();
    }

    public function delete($id) {
        $this->db->where('id', (int) $id);
        $this->db->delete('skpi_periode');
        return $this->db->affected_rows();
    }

    public function is_deadline_lewat($periode) {
        if (!$periode || empty($periode->deadline_pengumpulan)) {
            return false;
        }
        return strtotime('today') > strtotime($periode->deadline_pengumpulan);
    }

    public function is_upload_allowed($periode) {
        if (!$periode || $periode->status !== 'aktif') {
            return false;
        }
        return !$this->is_deadline_lewat($periode);
    }

    public function get_periode_for_mahasiswa($id_mahasiswa, $status = 'aktif') {
        $id_mahasiswa = (int) $id_mahasiswa;
        $this->db->select('sp.*');
        $this->db->from('skpi_periode sp');
        $this->db->join('skpi_peserta pes', 'pes.id_skpi_periode = sp.id', 'inner');
        $this->db->where('pes.id_mahasiswa', $id_mahasiswa);
        if ($status !== null && $status !== '') {
            $this->db->where('sp.status', $status);
        }
        $this->db->order_by('sp.deadline_pengumpulan', 'ASC');
        return $this->db->get()->result();
    }

    public function has_any_periode($id_mahasiswa) {
        $this->db->from('skpi_peserta');
        $this->db->where('id_mahasiswa', (int) $id_mahasiswa);
        return $this->db->count_all_results() > 0;
    }

    public function count_peserta($id_periode) {
        $this->db->where('id_skpi_periode', (int) $id_periode);
        return $this->db->count_all_results('skpi_peserta');
    }

    public function count_jenis_berkas($id_periode) {
        $this->db->where('id_skpi_periode', (int) $id_periode);
        $this->db->where('aktif', 1);
        return $this->db->count_all_results('skpi_jenis_berkas');
    }

    public function get_mahasiswa_aktif_grouped_by_kelas() {
        $this->db->where('status', 'aktif');
        $this->db->order_by('kelas', 'ASC');
        $this->db->order_by('nim', 'ASC');
        $rows = $this->db->get('mahasiswa')->result();
        $grouped = array();
        foreach ($rows as $m) {
            $kelas = $m->kelas ? $m->kelas : 'Tanpa Kelas';
            if (!isset($grouped[$kelas])) {
                $grouped[$kelas] = array();
            }
            $grouped[$kelas][] = $m;
        }
        return $grouped;
    }
}
