<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Skripsi_bimbingan_log_model extends CI_Model {

    public function __construct() {
        parent::__construct();
        $this->load->database();
    }

    public function create($data) {
        $this->db->insert('skripsi_bimbingan_log', $data);
        return $this->db->insert_id();
    }

    public function get_by_id($id) {
        $this->db->select('sbl.*, u.username, u.role_id, sbl.file_path, sbl.file_name');
        $this->db->from('skripsi_bimbingan_log sbl');
        $this->db->join('users u', 'u.id = sbl.id_pengguna', 'left');
        $this->db->where('sbl.id', $id);
        return $this->db->get()->row();
    }

    public function get_by_bimbingan($id_bimbingan) {
        $this->db->select('sbl.*, u.username, u.role_id, sbl.status_revisi, sbl.keterangan_revisi, sbl.file_path, sbl.file_name');
        $this->db->from('skripsi_bimbingan_log sbl');
        $this->db->join('users u', 'u.id = sbl.id_pengguna', 'left');
        $this->db->where('sbl.id_bimbingan', $id_bimbingan);
        // Gunakan raw SQL untuk ORDER BY dengan COALESCE - parameter ketiga false untuk tidak escape
        $this->db->order_by('COALESCE(sbl.urutan, sbl.id) ASC, sbl.created_at ASC', '', false);
        return $this->db->get()->result();
    }
    
    /**
     * Log terakhir per topik (urutan sama seperti PKL).
     */
    public function get_latest_by_bimbingan($id_bimbingan) {
        $this->db->select('sbl.*, u.username, u.role_id');
        $this->db->from('skripsi_bimbingan_log sbl');
        $this->db->join('users u', 'u.id = sbl.id_pengguna', 'left');
        $this->db->where('sbl.id_bimbingan', $id_bimbingan);
        $this->db->order_by('COALESCE(sbl.urutan, sbl.id) DESC, sbl.created_at DESC', '', false);
        $this->db->limit(1);
        return $this->db->get()->row();
    }

    public function get_next_urutan($id_bimbingan) {
        $this->db->select_max('urutan');
        $this->db->where('id_bimbingan', $id_bimbingan);
        $result = $this->db->get('skripsi_bimbingan_log')->row();
        return ($result && $result->urutan) ? $result->urutan + 1 : 1;
    }
    
    public function get_statistik_by_bimbingan($id_bimbingan) {
        $this->db->select('
            COUNT(*) as total_log,
            SUM(CASE WHEN status_revisi = "acc" THEN 1 ELSE 0 END) as total_acc,
            SUM(CASE WHEN status_revisi = "revisi" THEN 1 ELSE 0 END) as total_revisi,
            SUM(CASE WHEN role_pengguna = "mahasiswa" THEN 1 ELSE 0 END) as total_mahasiswa,
            SUM(CASE WHEN role_pengguna = "dosen" THEN 1 ELSE 0 END) as total_dosen
        ');
        $this->db->where('id_bimbingan', $id_bimbingan);
        return $this->db->get('skripsi_bimbingan_log')->row();
    }

    public function get_latest_revisi_by_bimbingan($id_bimbingan) {
        $this->db->select('sbl.*, u.username, u.role_id');
        $this->db->from('skripsi_bimbingan_log sbl');
        $this->db->join('users u', 'u.id = sbl.id_pengguna', 'left');
        $this->db->where('sbl.id_bimbingan', $id_bimbingan);
        $this->db->where('sbl.role_pengguna', 'dosen');
        $this->db->where('sbl.status_revisi IS NOT NULL');
        $this->db->where('sbl.status_revisi !=', '');
        $this->db->order_by('sbl.created_at', 'DESC');
        $this->db->limit(1);
        return $this->db->get()->row();
    }

    public function get_latest_revisi_by_bimbingan_and_dosen($id_bimbingan, $id_dosen) {
        $this->db->select('sbl.*');
        $this->db->from('skripsi_bimbingan_log sbl');
        $this->db->join('skripsi_bimbingan sb', 'sb.id = sbl.id_bimbingan');
        $this->db->where('sbl.id_bimbingan', $id_bimbingan);
        $this->db->where('sbl.role_pengguna', 'dosen');
        $this->db->where('sb.id_pembimbing', $id_dosen); // Pastikan ini adalah log untuk dosen yang sedang login
        $this->db->where('sbl.status_revisi IS NOT NULL');
        $this->db->where('sbl.status_revisi !=', '');
        $this->db->order_by('sbl.created_at', 'DESC');
        $this->db->limit(1);
        return $this->db->get()->row();
    }

    public function get_latest_mahasiswa_topic_pending_dosen($id_bimbingan, $id_dosen) {
        $this->db->select('sbl.*');
        $this->db->from('skripsi_bimbingan_log sbl');
        $this->db->join('skripsi_bimbingan sb', 'sb.id = sbl.id_bimbingan');
        $this->db->where('sbl.id_bimbingan', $id_bimbingan);
        $this->db->where('sbl.role_pengguna', 'mahasiswa');
        $this->db->where('sb.id_pembimbing', $id_dosen); // Pastikan ini adalah log untuk dosen yang sedang login
        $this->db->where('sbl.status_revisi IS NULL'); // Belum di-ACC atau direvisi
        $this->db->order_by('sbl.created_at', 'DESC');
        $this->db->limit(1);
        return $this->db->get()->row();
    }

    /**
     * Get semua topik yang diajukan mahasiswa untuk ditampilkan di tabel
     * Setiap topik adalah log mahasiswa yang terpisah
     */
    public function get_topics_by_mahasiswa($id_mahasiswa, $id_tahun_akademik = null) {
        $this->db->select('sbl.*, sb.id_pembimbing, sb.tipe_pembimbing, sb.status as status_bimbingan, d.nama_dosen');
        $this->db->from('skripsi_bimbingan_log sbl');
        $this->db->join('skripsi_bimbingan sb', 'sb.id = sbl.id_bimbingan');
        $this->db->join('dosen d', 'd.id = sb.id_pembimbing', 'left');
        $this->db->where('sb.id_mahasiswa', $id_mahasiswa);
        $this->db->where('sbl.role_pengguna', 'mahasiswa'); // Hanya log dari mahasiswa
        if ($id_tahun_akademik) {
            $this->db->where('sb.id_tahun_akademik', $id_tahun_akademik);
        }
        $this->db->order_by('sbl.created_at', 'DESC');
        return $this->db->get()->result();
    }

    /**
     * Get semua topik yang diajukan mahasiswa untuk pembimbing tertentu (dosen)
     * Setiap topik adalah log mahasiswa yang terpisah
     */
    public function get_topics_by_pembimbing($id_pembimbing, $id_tahun_akademik = null, $status_bimbingan = null) {
        $this->db->select('sbl.*, sb.id_mahasiswa, sb.tipe_pembimbing, sb.status as status_bimbingan, 
                          sb.last_activity, m.nim, m.nama_mahasiswa, m.kelas');
        $this->db->from('skripsi_bimbingan_log sbl');
        $this->db->join('skripsi_bimbingan sb', 'sb.id = sbl.id_bimbingan');
        $this->db->join('mahasiswa m', 'm.id = sb.id_mahasiswa', 'left');
        $this->db->where('sb.id_pembimbing', $id_pembimbing);
        $this->db->where('sbl.role_pengguna', 'mahasiswa'); // Hanya log dari mahasiswa
        if ($id_tahun_akademik) {
            $this->db->where('sb.id_tahun_akademik', $id_tahun_akademik);
        }
        if ($status_bimbingan) {
            $this->db->where('sb.status', $status_bimbingan);
        }
        $this->db->order_by('sbl.created_at', 'DESC');
        return $this->db->get()->result();
    }
}

