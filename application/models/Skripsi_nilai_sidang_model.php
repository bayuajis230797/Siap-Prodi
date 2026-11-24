<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Skripsi_nilai_sidang_model extends CI_Model {

    public function __construct() {
        parent::__construct();
        $this->load->database();
    }

    public function create($data) {
        $this->db->insert('skripsi_nilai_sidang', $data);
        return $this->db->insert_id();
    }

    public function update($id, $data) {
        $this->db->where('id', $id);
        $this->db->update('skripsi_nilai_sidang', $data);
        return $this->db->affected_rows();
    }

    public function delete($id) {
        $this->db->where('id', $id);
        return $this->db->delete('skripsi_nilai_sidang');
    }

    public function get_by_id($id) {
        $this->db->select('sns.*, ms.id_mahasiswa, ms.judul_skripsi, m.nim, m.nama_mahasiswa,
                           d.nama_dosen as penguji_nama, d.nidn as penguji_nidn');
        $this->db->from('skripsi_nilai_sidang sns');
        $this->db->join('mahasiswa_skripsi ms', 'ms.id = sns.id_mahasiswa_skripsi', 'left');
        $this->db->join('mahasiswa m', 'm.id = ms.id_mahasiswa', 'left');
        $this->db->join('dosen d', 'd.id = sns.id_penguji', 'left');
        $this->db->where('sns.id', $id);
        return $this->db->get()->row();
    }

    public function get_by_jadwal($id_jadwal_skripsi) {
        $this->db->select('sns.*, d.nama_dosen as penguji_nama, d.nidn as penguji_nidn');
        $this->db->from('skripsi_nilai_sidang sns');
        $this->db->join('dosen d', 'd.id = sns.id_penguji', 'left');
        $this->db->where('sns.id_jadwal_skripsi', $id_jadwal_skripsi);
        $this->db->order_by('sns.peran', 'ASC');
        return $this->db->get()->result();
    }

    public function get_by_jadwal_grouped($id_jadwal_skripsi) {
        $result = $this->get_by_jadwal($id_jadwal_skripsi);
        
        $grouped = array(
            'ketua_penguji' => null,
            'penguji_1' => null,
            'penguji_2' => null
        );
        
        foreach ($result as $row) {
            if ($row->peran == 'ketua') {
                $grouped['ketua_penguji'] = $row;
            } elseif ($row->peran == 'anggota_1') {
                $grouped['penguji_1'] = $row;
            } elseif ($row->peran == 'anggota_2') {
                $grouped['penguji_2'] = $row;
            }
        }
        
        return $grouped;
    }

    public function get_by_jadwal_penguji($id_jadwal_skripsi, $peran) {
        $this->db->where('id_jadwal_skripsi', $id_jadwal_skripsi);
        $this->db->where('peran', $peran);
        return $this->db->get('skripsi_nilai_sidang')->row();
    }

    public function get_by_jadwal_penguji_id($id_jadwal_skripsi, $id_penguji) {
        $this->db->where('id_jadwal_skripsi', $id_jadwal_skripsi);
        $this->db->where('id_penguji', $id_penguji);
        return $this->db->get('skripsi_nilai_sidang')->row();
    }

    public function upsert($data) {
        $existing = $this->get_by_jadwal_penguji($data['id_jadwal_skripsi'], $data['peran']);
        if ($existing) {
            $this->db->where('id', $existing->id);
            $this->db->update('skripsi_nilai_sidang', $data);
            return $existing->id;
        }

        $this->db->insert('skripsi_nilai_sidang', $data);
        return $this->db->insert_id();
    }

    public function get_average_by_skripsi($id_mahasiswa_skripsi) {
        $this->db->select('AVG(nilai_total) as nilai_sidang, COUNT(*) as jumlah_penilai');
        $this->db->from('skripsi_nilai_sidang sns');
        $this->db->where('sns.id_mahasiswa_skripsi', $id_mahasiswa_skripsi);
        $result = $this->db->get()->row();
        if ($result && $result->jumlah_penilai > 0) {
            return (float) $result->nilai_sidang;
        }
        return null;
    }
}
