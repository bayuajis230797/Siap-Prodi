<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Jadwal_pkl_model extends CI_Model {

    public function __construct() {
        parent::__construct();
        $this->load->database();
    }

    public function create($data) {
        $this->db->insert('jadwal_pkl', $data);
        return $this->db->insert_id();
    }

    public function update($id, $data) {
        $this->db->where('id', $id);
        $this->db->update('jadwal_pkl', $data);
        return $this->db->affected_rows();
    }

    public function delete($id) {
        $this->db->where('id', $id);
        $this->db->delete('jadwal_pkl');
        return $this->db->affected_rows();
    }

    public function get_by_id($id) {
        $this->db->select('jp.*, m.nim, m.nama_mahasiswa, m.kelas, 
                          d_pembimbing.nama_dosen as pembimbing_nama, d_pembimbing.nidn as pembimbing_nidn,
                          d_penguji.nama_dosen as penguji_nama, d_penguji.nidn as penguji_nidn,
                          ta.tahun_akademik, ta.semester');
        $this->db->from('jadwal_pkl jp');
        $this->db->join('mahasiswa m', 'm.id = jp.id_mahasiswa', 'left');
        $this->db->join('dosen d_pembimbing', 'd_pembimbing.id = jp.id_pembimbing', 'left');
        $this->db->join('dosen d_penguji', 'd_penguji.id = jp.id_penguji', 'left');
        $this->db->join('tahun_akademik ta', 'ta.id = jp.id_tahun_akademik', 'left');
        $this->db->where('jp.id', $id);
        return $this->db->get()->row();
    }

    public function get_by_mahasiswa($id_mahasiswa, $id_tahun_akademik = null) {
        $this->db->select('jp.*, d_pembimbing.nama_dosen as pembimbing_nama, d_pembimbing.nidn as pembimbing_nidn, d_penguji.nama_dosen as penguji_nama, d_penguji.nidn as penguji_nidn');
        $this->db->from('jadwal_pkl jp');
        $this->db->join('dosen d_pembimbing', 'd_pembimbing.id = jp.id_pembimbing', 'left');
        $this->db->join('dosen d_penguji', 'd_penguji.id = jp.id_penguji', 'left');
        $this->db->where('jp.id_mahasiswa', $id_mahasiswa);
        if ($id_tahun_akademik) {
            $this->db->where('jp.id_tahun_akademik', $id_tahun_akademik);
        }
        $this->db->order_by('jp.tanggal', 'DESC');
        return $this->db->get()->result();
    }

    public function get_by_penguji($id_penguji, $id_tahun_akademik = null) {
        $this->db->select('jp.*, m.nim, m.nama_mahasiswa, m.kelas, d_pembimbing.nama_dosen as pembimbing_nama');
        $this->db->from('jadwal_pkl jp');
        $this->db->join('mahasiswa m', 'm.id = jp.id_mahasiswa', 'left');
        $this->db->join('dosen d_pembimbing', 'd_pembimbing.id = jp.id_pembimbing', 'left');
        $this->db->where('jp.id_penguji', $id_penguji);
        if ($id_tahun_akademik) {
            $this->db->where('jp.id_tahun_akademik', $id_tahun_akademik);
        }
        $this->db->order_by('jp.tanggal', 'DESC');
        return $this->db->get()->result();
    }

    public function get_all($id_tahun_akademik = null, $status = null, $limit = null, $offset = null) {
        $this->db->select('jp.*, m.nim, m.nama_mahasiswa, m.kelas,
                          d_pembimbing.nama_dosen as pembimbing_nama, d_penguji.nama_dosen as penguji_nama,
                          ta.tahun_akademik, ta.semester');
        $this->db->from('jadwal_pkl jp');
        $this->db->join('mahasiswa m', 'm.id = jp.id_mahasiswa', 'left');
        $this->db->join('dosen d_pembimbing', 'd_pembimbing.id = jp.id_pembimbing', 'left');
        $this->db->join('dosen d_penguji', 'd_penguji.id = jp.id_penguji', 'left');
        $this->db->join('tahun_akademik ta', 'ta.id = jp.id_tahun_akademik', 'left');
        
        if ($id_tahun_akademik) {
            $this->db->where('jp.id_tahun_akademik', $id_tahun_akademik);
        }
        
        if ($status) {
            $this->db->where('jp.status', $status);
        }
        
        $this->db->order_by('jp.tanggal', 'DESC');
        
        if ($limit !== null && $offset !== null) {
            $this->db->limit($limit, $offset);
        }
        
        return $this->db->get()->result();
    }

    public function count_all($id_tahun_akademik = null, $status = null) {
        $this->db->from('jadwal_pkl');
        
        if ($id_tahun_akademik) {
            $this->db->where('id_tahun_akademik', $id_tahun_akademik);
        }
        
        if ($status) {
            $this->db->where('status', $status);
        }
        
        return $this->db->count_all_results();
    }

    /**
     * Cek bentrok jadwal (ruangan, waktu, atau penguji)
     * @param string $tanggal
     * @param string $jam_mulai
     * @param string $jam_selesai
     * @param string $ruangan
     * @param int $id_tahun_akademik
     * @param int|null $exclude_id ID jadwal yang sedang diedit (untuk exclude dari pengecekan)
     * @param int|null $id_penguji ID penguji untuk cek bentrok
     * @return array Array dengan key 'bentrok_ruang', 'bentrok_penguji', dan detailnya
     */
    public function check_bentrok($tanggal, $jam_mulai, $jam_selesai, $ruangan, $id_tahun_akademik, $exclude_id = null, $id_penguji = null) {
        $result = array(
            'bentrok_ruang' => false,
            'bentrok_penguji' => false,
            'detail_ruang' => null,
            'detail_penguji' => null
        );
        
        // Cek bentrok ruangan
        if (!empty($ruangan)) {
            $this->db->where('tanggal', $tanggal);
            $this->db->where('ruangan', $ruangan);
            $this->db->where('id_tahun_akademik', $id_tahun_akademik);
            $this->db->where("jam_mulai < '{$jam_selesai}'", NULL, FALSE);
            $this->db->where("jam_selesai > '{$jam_mulai}'", NULL, FALSE);
            $this->db->where('status !=', 'batal'); // Exclude jadwal yang dibatalkan
            
            if ($exclude_id) {
                $this->db->where('id !=', $exclude_id);
            }
            
            $query = $this->db->get('jadwal_pkl');
            if ($query->num_rows() > 0) {
                $result['bentrok_ruang'] = true;
                $result['detail_ruang'] = $query->row();
            }
        }
        
        // Cek bentrok penguji
        if (!empty($id_penguji)) {
            $this->db->where('tanggal', $tanggal);
            $this->db->where('id_tahun_akademik', $id_tahun_akademik);
            $this->db->where("jam_mulai < '{$jam_selesai}'", NULL, FALSE);
            $this->db->where("jam_selesai > '{$jam_mulai}'", NULL, FALSE);
            $this->db->where('id_penguji', $id_penguji);
            $this->db->where('status !=', 'batal');
            
            if ($exclude_id) {
                $this->db->where('id !=', $exclude_id);
            }
            
            $query = $this->db->get('jadwal_pkl');
            if ($query->num_rows() > 0) {
                $result['bentrok_penguji'] = true;
                $result['detail_penguji'] = $query->row();
            }
        }
        
        return $result;
    }
}



