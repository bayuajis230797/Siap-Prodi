<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Skripsi_pengajuan_model extends CI_Model {

    public function __construct() {
        parent::__construct();
        $this->load->database();
    }

    public function create($data) {
        $this->db->insert('skripsi_pengajuan', $data);
        return $this->db->insert_id();
    }

    public function update($id, $data) {
        $this->db->where('id', $id);
        $this->db->update('skripsi_pengajuan', $data);
        return $this->db->affected_rows();
    }

    public function delete($id) {
        $pengajuan = $this->get_by_id($id);
        
        if ($pengajuan && !empty($pengajuan->file_proposal) && file_exists($pengajuan->file_proposal)) {
            unlink($pengajuan->file_proposal);
        }
        
        $this->db->where('id', $id);
        return $this->db->delete('skripsi_pengajuan');
    }

    public function get_by_id($id) {
        $this->db->select('sp.*, 
                           m.nim, m.nama_mahasiswa, m.kelas,
                           ta.tahun_akademik, ta.semester,
                           d1.nama_dosen as pembimbing_1_nama, d1.nidn as pembimbing_1_nidn,
                           d2.nama_dosen as pembimbing_2_nama, d2.nidn as pembimbing_2_nidn,
                           ms.id_pembimbing_1, ms.id_pembimbing_2, ms.judul_skripsi as judul_final');
        $this->db->from('skripsi_pengajuan sp');
        $this->db->join('mahasiswa m', 'm.id = sp.id_mahasiswa', 'left');
        $this->db->join('tahun_akademik ta', 'ta.id = sp.id_tahun_akademik', 'left');
        $this->db->join('dosen d1', 'd1.id = sp.id_pembimbing_1_final', 'left');
        $this->db->join('dosen d2', 'd2.id = sp.id_pembimbing_2_final', 'left');
        // Join ke mahasiswa_skripsi untuk mendapatkan pembimbing yang sudah diplot (jika ada)
        $this->db->join('mahasiswa_skripsi ms', 'ms.id_mahasiswa = sp.id_mahasiswa AND ms.id_tahun_akademik = sp.id_tahun_akademik', 'left');
        $this->db->where('sp.id', $id);
        return $this->db->get()->row();
    }

    public function get_by_mahasiswa_skripsi($id_mahasiswa_skripsi) {
        $this->db->select('sp.*, ms.id_mahasiswa, ms.id_pembimbing_1, ms.id_pembimbing_2,
                           m.nim, m.nama_mahasiswa, m.kelas');
        $this->db->from('skripsi_pengajuan sp');
        $this->db->join('mahasiswa_skripsi ms', 'ms.id = sp.id_mahasiswa_skripsi', 'left');
        $this->db->join('mahasiswa m', 'm.id = ms.id_mahasiswa', 'left');
        $this->db->where('sp.id_mahasiswa_skripsi', $id_mahasiswa_skripsi);
        $this->db->order_by('sp.created_at', 'DESC');
        return $this->db->get()->result();
    }

    public function get_latest_by_mahasiswa_skripsi($id_mahasiswa_skripsi) {
        $this->db->select('sp.*');
        $this->db->from('skripsi_pengajuan sp');
        $this->db->where('sp.id_mahasiswa_skripsi', $id_mahasiswa_skripsi);
        $this->db->order_by('sp.created_at', 'DESC');
        $this->db->limit(1);
        return $this->db->get()->row();
    }

    /**
     * Get latest pengajuan by id_mahasiswa and id_tahun_akademik
     * Direct query from skripsi_pengajuan table (which has id_mahasiswa directly)
     */
    public function get_latest_by_mahasiswa($id_mahasiswa, $id_tahun_akademik = null) {
        $this->db->select('sp.*, 
                           m.nim, m.nama_mahasiswa, m.kelas,
                           ta.tahun_akademik, ta.semester,
                           d1.nama_dosen as pembimbing_1_nama, d1.nidn as pembimbing_1_nidn,
                           d2.nama_dosen as pembimbing_2_nama, d2.nidn as pembimbing_2_nidn');
        $this->db->from('skripsi_pengajuan sp');
        $this->db->join('mahasiswa m', 'm.id = sp.id_mahasiswa', 'left');
        $this->db->join('tahun_akademik ta', 'ta.id = sp.id_tahun_akademik', 'left');
        $this->db->join('dosen d1', 'd1.id = sp.id_pembimbing_1_final', 'left');
        $this->db->join('dosen d2', 'd2.id = sp.id_pembimbing_2_final', 'left');
        $this->db->where('sp.id_mahasiswa', $id_mahasiswa);
        if (!empty($id_tahun_akademik)) {
            $this->db->where('sp.id_tahun_akademik', $id_tahun_akademik);
        }
        $this->db->order_by('sp.created_at', 'DESC');
        $this->db->limit(1);
        return $this->db->get()->row();
    }

    public function get_all($filters = array(), $limit = null, $offset = null) {
        $this->db->select('sp.*, 
                           m.nim, m.nama_mahasiswa, m.kelas,
                           ta.tahun_akademik, ta.semester,
                           COALESCE(d1.nama_dosen, d1_ms.nama_dosen) as pembimbing_1_nama,
                           COALESCE(d2.nama_dosen, d2_ms.nama_dosen) as pembimbing_2_nama,
                           ms.id_pembimbing_1, ms.id_pembimbing_2');
        $this->db->from('skripsi_pengajuan sp');
        $this->db->join('mahasiswa m', 'm.id = sp.id_mahasiswa', 'left');
        $this->db->join('tahun_akademik ta', 'ta.id = sp.id_tahun_akademik', 'left');
        // Join dosen dari skripsi_pengajuan (prioritas)
        $this->db->join('dosen d1', 'd1.id = sp.id_pembimbing_1_final', 'left');
        $this->db->join('dosen d2', 'd2.id = sp.id_pembimbing_2_final', 'left');
        // Join ke mahasiswa_skripsi untuk mendapatkan pembimbing yang sudah diplot (jika ada)
        $this->db->join('mahasiswa_skripsi ms', 'ms.id_mahasiswa = sp.id_mahasiswa AND ms.id_tahun_akademik = sp.id_tahun_akademik', 'left');
        // Join dosen dari mahasiswa_skripsi sebagai fallback
        $this->db->join('dosen d1_ms', 'd1_ms.id = ms.id_pembimbing_1', 'left');
        $this->db->join('dosen d2_ms', 'd2_ms.id = ms.id_pembimbing_2', 'left');

        if (!empty($filters['id_tahun_akademik'])) {
            $this->db->where('sp.id_tahun_akademik', $filters['id_tahun_akademik']);
        }

        if (!empty($filters['id_pembimbing_1']) || !empty($filters['id_pembimbing_2'])) {
            $this->db->group_start();
            if (!empty($filters['id_pembimbing_1'])) {
                $this->db->where('ms.id_pembimbing_1', $filters['id_pembimbing_1']);
            }
            if (!empty($filters['id_pembimbing_2'])) {
                $this->db->or_where('ms.id_pembimbing_2', $filters['id_pembimbing_2']);
            }
            $this->db->group_end();
        }

        if (!empty($filters['status_pembimbing_1'])) {
            $this->db->where('sp.status_acc_judul_p1', $filters['status_pembimbing_1']);
        }

        if (!empty($filters['status_pembimbing_2'])) {
            $this->db->where('sp.status_acc_judul_p2', $filters['status_pembimbing_2']);
        }

        if (!empty($filters['kelas'])) {
            $this->db->where('m.kelas', $filters['kelas']);
        }

        $this->db->order_by('sp.created_at', 'DESC');

        if ($limit !== null) {
            $this->db->limit($limit, $offset);
        }

        return $this->db->get()->result();
    }

    public function count_all($filters = array()) {
        $this->db->from('skripsi_pengajuan sp');
        $this->db->join('mahasiswa m', 'm.id = sp.id_mahasiswa', 'left');

        if (!empty($filters['id_tahun_akademik'])) {
            $this->db->where('sp.id_tahun_akademik', $filters['id_tahun_akademik']);
        }

        if (!empty($filters['kelas'])) {
            $this->db->where('m.kelas', $filters['kelas']);
        }

        return $this->db->count_all_results();
    }

    /**
     * Get pengajuan yang perlu di-review oleh pembimbing tertentu
     */
    public function get_by_pembimbing_pending($id_dosen, $id_tahun_akademik = null) {
        $this->db->select('sp.*, 
                           m.nim, m.nama_mahasiswa, m.kelas,
                           ta.tahun_akademik, ta.semester,
                           ms.id_pembimbing_1, ms.id_pembimbing_2');
        $this->db->from('skripsi_pengajuan sp');
        $this->db->join('mahasiswa m', 'm.id = sp.id_mahasiswa', 'left');
        $this->db->join('tahun_akademik ta', 'ta.id = sp.id_tahun_akademik', 'left');
        // Join ke mahasiswa_skripsi untuk mendapatkan pembimbing yang sudah diplot
        $this->db->join('mahasiswa_skripsi ms', 'ms.id_mahasiswa = sp.id_mahasiswa AND ms.id_tahun_akademik = sp.id_tahun_akademik', 'left');
        
        // Filter: dosen ini adalah pembimbing 1 atau 2 dan masih pending
        $this->db->group_start();
        $this->db->group_start();
        $this->db->where('ms.id_pembimbing_1', $id_dosen);
        $this->db->where('sp.status_acc_judul_p1', 'pending');
        $this->db->group_end();
        $this->db->or_group_start();
        $this->db->where('ms.id_pembimbing_2', $id_dosen);
        $this->db->where('sp.status_acc_judul_p2', 'pending');
        $this->db->group_end();
        $this->db->group_end();
        
        if ($id_tahun_akademik) {
            $this->db->where('sp.id_tahun_akademik', $id_tahun_akademik);
        }
        
        $this->db->order_by('sp.created_at', 'DESC');
        
        return $this->db->get()->result();
    }

    /**
     * Get semua pengajuan untuk pembimbing tertentu (tidak hanya pending)
     */
    public function get_by_pembimbing_all($id_dosen, $id_tahun_akademik = null) {
        $this->db->select('sp.*, 
                           m.nim, m.nama_mahasiswa, m.kelas,
                           ta.tahun_akademik, ta.semester,
                           ms.id_pembimbing_1, ms.id_pembimbing_2');
        $this->db->from('skripsi_pengajuan sp');
        $this->db->join('mahasiswa m', 'm.id = sp.id_mahasiswa', 'left');
        $this->db->join('tahun_akademik ta', 'ta.id = sp.id_tahun_akademik', 'left');
        // Join ke mahasiswa_skripsi untuk mendapatkan pembimbing yang sudah diplot
        $this->db->join('mahasiswa_skripsi ms', 'ms.id_mahasiswa = sp.id_mahasiswa AND ms.id_tahun_akademik = sp.id_tahun_akademik', 'left');
        
        // Filter: dosen ini adalah pembimbing 1 atau 2 (cek dari mahasiswa_skripsi atau skripsi_pengajuan)
        $this->db->group_start();
        $this->db->group_start();
        $this->db->where('ms.id_pembimbing_1', $id_dosen);
        $this->db->or_where('ms.id_pembimbing_2', $id_dosen);
        $this->db->group_end();
        $this->db->or_group_start();
        $this->db->where('sp.id_pembimbing_1_final', $id_dosen);
        $this->db->or_where('sp.id_pembimbing_2_final', $id_dosen);
        $this->db->group_end();
        $this->db->group_end();
        
        if ($id_tahun_akademik) {
            $this->db->where('sp.id_tahun_akademik', $id_tahun_akademik);
        }
        
        $this->db->order_by('sp.created_at', 'DESC');
        
        return $this->db->get()->result();
    }
}
