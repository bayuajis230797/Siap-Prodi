<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Honor Skripsi Model
 * Mengelola perhitungan honor skripsi untuk pembimbing dan penguji
 */
class Honor_skripsi_model extends CI_Model {

    public function __construct() {
        parent::__construct();
        $this->load->database();
    }

    /**
     * Get honor pembimbing per dosen
     * Menghitung jumlah mahasiswa yang dibimbing per dosen
     */
    public function get_honor_pembimbing($id_tahun_akademik = null) {
        // Query untuk pembimbing 1
        $this->db->select('ms.id_pembimbing_1 as id_dosen, 
                          COUNT(DISTINCT ms.id) as jumlah_pembimbing_1,
                          0 as jumlah_pembimbing_2,
                          d.nidn, d.nama_dosen');
        $this->db->from('mahasiswa_skripsi ms');
        $this->db->join('dosen d', 'd.id = ms.id_pembimbing_1', 'left');
        $this->db->where('d.status', 'aktif');
        $this->db->where('ms.id_pembimbing_1 IS NOT NULL');
        
        if ($id_tahun_akademik) {
            $this->db->where('ms.id_tahun_akademik', $id_tahun_akademik);
        }
        
        $this->db->group_by('d.id');
        $query_p1 = $this->db->get_compiled_select();
        
        // Query untuk pembimbing 2
        $this->db->select('ms.id_pembimbing_2 as id_dosen, 
                          0 as jumlah_pembimbing_1,
                          COUNT(DISTINCT ms.id) as jumlah_pembimbing_2,
                          d.nidn, d.nama_dosen');
        $this->db->from('mahasiswa_skripsi ms');
        $this->db->join('dosen d', 'd.id = ms.id_pembimbing_2', 'left');
        $this->db->where('d.status', 'aktif');
        $this->db->where('ms.id_pembimbing_2 IS NOT NULL');
        
        if ($id_tahun_akademik) {
            $this->db->where('ms.id_tahun_akademik', $id_tahun_akademik);
        }
        
        $this->db->group_by('d.id');
        $query_p2 = $this->db->get_compiled_select();
        
        // Gabungkan kedua query dengan UNION
        $union_query = "($query_p1) UNION ALL ($query_p2)";
        
        // Query final untuk menggabungkan hasil
        $final_query = "
            SELECT 
                id_dosen,
                SUM(jumlah_pembimbing_1) as jumlah_pembimbing_1,
                SUM(jumlah_pembimbing_2) as jumlah_pembimbing_2,
                MAX(nidn) as nidn,
                MAX(nama_dosen) as nama_dosen
            FROM ($union_query) as combined
            GROUP BY id_dosen
            HAVING jumlah_pembimbing_1 > 0 OR jumlah_pembimbing_2 > 0
            ORDER BY nama_dosen ASC
        ";
        
        return $this->db->query($final_query)->result();
    }

    /**
     * Get honor penguji per dosen
     * Menghitung jumlah sidang sebagai penguji per dosen
     */
    public function get_honor_penguji($id_tahun_akademik = null) {
        // Query untuk ketua penguji
        $this->db->select('js.id_ketua_penguji as id_dosen,
                          COUNT(DISTINCT js.id) as jumlah_ketua,
                          0 as jumlah_anggota_1,
                          0 as jumlah_anggota_2,
                          d.nidn, d.nama_dosen');
        $this->db->from('jadwal_skripsi js');
        $this->db->join('dosen d', 'd.id = js.id_ketua_penguji', 'left');
        $this->db->where('d.status', 'aktif');
        $this->db->where('js.id_ketua_penguji IS NOT NULL');
        
        if ($id_tahun_akademik) {
            $this->db->where('js.id_tahun_akademik', $id_tahun_akademik);
        }
        
        $this->db->group_by('d.id');
        $query_ketua = $this->db->get_compiled_select();
        
        // Query untuk anggota penguji 1
        $this->db->select('js.id_anggota_penguji_1 as id_dosen,
                          0 as jumlah_ketua,
                          COUNT(DISTINCT js.id) as jumlah_anggota_1,
                          0 as jumlah_anggota_2,
                          d.nidn, d.nama_dosen');
        $this->db->from('jadwal_skripsi js');
        $this->db->join('dosen d', 'd.id = js.id_anggota_penguji_1', 'left');
        $this->db->where('d.status', 'aktif');
        $this->db->where('js.id_anggota_penguji_1 IS NOT NULL');
        
        if ($id_tahun_akademik) {
            $this->db->where('js.id_tahun_akademik', $id_tahun_akademik);
        }
        
        $this->db->group_by('d.id');
        $query_anggota1 = $this->db->get_compiled_select();
        
        // Query untuk anggota penguji 2
        $this->db->select('js.id_anggota_penguji_2 as id_dosen,
                          0 as jumlah_ketua,
                          0 as jumlah_anggota_1,
                          COUNT(DISTINCT js.id) as jumlah_anggota_2,
                          d.nidn, d.nama_dosen');
        $this->db->from('jadwal_skripsi js');
        $this->db->join('dosen d', 'd.id = js.id_anggota_penguji_2', 'left');
        $this->db->where('d.status', 'aktif');
        $this->db->where('js.id_anggota_penguji_2 IS NOT NULL');
        
        if ($id_tahun_akademik) {
            $this->db->where('js.id_tahun_akademik', $id_tahun_akademik);
        }
        
        $this->db->group_by('d.id');
        $query_anggota2 = $this->db->get_compiled_select();
        
        // Gabungkan semua query dengan UNION ALL
        $union_query = "($query_ketua) UNION ALL ($query_anggota1) UNION ALL ($query_anggota2)";
        
        // Query final untuk menggabungkan hasil
        $final_query = "
            SELECT 
                id_dosen,
                SUM(jumlah_ketua) as jumlah_ketua,
                SUM(jumlah_anggota_1) as jumlah_anggota_1,
                SUM(jumlah_anggota_2) as jumlah_anggota_2,
                MAX(nidn) as nidn,
                MAX(nama_dosen) as nama_dosen
            FROM ($union_query) as combined
            GROUP BY id_dosen
            HAVING jumlah_ketua > 0 OR jumlah_anggota_1 > 0 OR jumlah_anggota_2 > 0
            ORDER BY nama_dosen ASC
        ";
        
        return $this->db->query($final_query)->result();
    }

    /**
     * Get detail honor pembimbing untuk dosen tertentu
     */
    public function get_detail_pembimbing($id_dosen, $id_tahun_akademik = null) {
        $this->db->select('ms.*, m.nim, m.nama_mahasiswa, m.kelas,
                          CASE 
                              WHEN ms.id_pembimbing_1 = ' . $this->db->escape($id_dosen) . ' THEN 1
                              WHEN ms.id_pembimbing_2 = ' . $this->db->escape($id_dosen) . ' THEN 2
                          END as tipe_pembimbing');
        $this->db->from('mahasiswa_skripsi ms');
        $this->db->join('mahasiswa m', 'm.id = ms.id_mahasiswa', 'left');
        $this->db->where('(ms.id_pembimbing_1 = ' . $this->db->escape($id_dosen) . ' OR ms.id_pembimbing_2 = ' . $this->db->escape($id_dosen) . ')', NULL, FALSE);
        
        if ($id_tahun_akademik) {
            $this->db->where('ms.id_tahun_akademik', $id_tahun_akademik);
        }
        
        $this->db->order_by('m.nim', 'ASC');
        return $this->db->get()->result();
    }

    /**
     * Get detail honor penguji untuk dosen tertentu
     */
    public function get_detail_penguji($id_dosen, $id_tahun_akademik = null) {
        $this->db->select('js.*, 
                          ms.id_mahasiswa, m.nim, m.nama_mahasiswa, m.kelas,
                          CASE 
                              WHEN js.id_ketua_penguji = ' . $this->db->escape($id_dosen) . ' THEN "ketua"
                              WHEN js.id_anggota_penguji_1 = ' . $this->db->escape($id_dosen) . ' THEN "anggota_1"
                              WHEN js.id_anggota_penguji_2 = ' . $this->db->escape($id_dosen) . ' THEN "anggota_2"
                          END as peran_penguji');
        $this->db->from('jadwal_skripsi js');
        $this->db->join('mahasiswa_skripsi ms', 'ms.id = js.id_mahasiswa_skripsi', 'left');
        $this->db->join('mahasiswa m', 'm.id = ms.id_mahasiswa', 'left');
        $this->db->where('(js.id_ketua_penguji = ' . $this->db->escape($id_dosen) . ' 
                          OR js.id_anggota_penguji_1 = ' . $this->db->escape($id_dosen) . ' 
                          OR js.id_anggota_penguji_2 = ' . $this->db->escape($id_dosen) . ')', NULL, FALSE);
        
        if ($id_tahun_akademik) {
            $this->db->where('js.id_tahun_akademik', $id_tahun_akademik);
        }
        
        $this->db->order_by('js.tanggal_ujian', 'ASC');
        return $this->db->get()->result();
    }
}

