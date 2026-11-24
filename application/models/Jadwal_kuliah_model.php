<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Jadwal Kuliah Model
 * Mengelola jadwal kuliah dengan validasi bentrok
 */
class Jadwal_kuliah_model extends CI_Model {

    public function __construct() {
        parent::__construct();
        $this->load->database();
    }

    /**
     * Get semua jadwal dengan detail lengkap
     */
    public function get_all($id_tahun_akademik = null, $limit = 10, $offset = 0) {
        $this->db->select('jk.*, 
                          mdm.kelas as kelas_mapping,
                          mk.kode_mk, mk.nama_mk, mk.sks,
                          d.nidn, d.nama_dosen,
                          rk.kode_ruang, rk.nama_ruang,
                          ta.tahun_akademik, ta.semester');
        $this->db->from('jadwal_kuliah jk');
        $this->db->join('mapping_dosen_mk mdm', 'mdm.id = jk.id_mapping_dosen_mk', 'left');
        $this->db->join('mata_kuliah_aktif mka', 'mka.id = mdm.id_mata_kuliah_aktif', 'left');
        $this->db->join('kurikulum_detail kd', 'kd.id = mka.id_kurikulum_detail', 'left');
        $this->db->join('mata_kuliah mk', 'mk.id = kd.id_mata_kuliah', 'left');
        $this->db->join('dosen d', 'd.id = mdm.id_dosen', 'left');
        $this->db->join('ruang_kelas rk', 'rk.id = jk.id_ruang_kelas', 'left');
        $this->db->join('tahun_akademik ta', 'ta.id = jk.id_tahun_akademik', 'left');
        
        if ($id_tahun_akademik) {
            $this->db->where('jk.id_tahun_akademik', $id_tahun_akademik);
        }
        
        $this->db->order_by('jk.hari', 'ASC');
        $this->db->order_by('jk.jam_mulai', 'ASC');
        $this->db->order_by('jk.kelas', 'ASC');
        $this->db->limit($limit, $offset);
        
        $query = $this->db->get();
        return $query->result();
    }

    /**
     * Count semua jadwal
     */
    public function count_all($id_tahun_akademik = null) {
        $this->db->from('jadwal_kuliah jk');
        
        if ($id_tahun_akademik) {
            $this->db->where('jk.id_tahun_akademik', $id_tahun_akademik);
        }
        
        return $this->db->count_all_results();
    }

    /**
     * Get jadwal by ID
     */
    public function get_by_id($id) {
        $this->db->select('jk.*, 
                          mdm.kelas as kelas_mapping,
                          mk.kode_mk, mk.nama_mk, mk.sks,
                          d.nidn, d.nama_dosen,
                          rk.kode_ruang, rk.nama_ruang, rk.id as id_ruang_kelas');
        $this->db->from('jadwal_kuliah jk');
        $this->db->join('mapping_dosen_mk mdm', 'mdm.id = jk.id_mapping_dosen_mk', 'left');
        $this->db->join('mata_kuliah_aktif mka', 'mka.id = mdm.id_mata_kuliah_aktif', 'left');
        $this->db->join('kurikulum_detail kd', 'kd.id = mka.id_kurikulum_detail', 'left');
        $this->db->join('mata_kuliah mk', 'mk.id = kd.id_mata_kuliah', 'left');
        $this->db->join('dosen d', 'd.id = mdm.id_dosen', 'left');
        $this->db->join('ruang_kelas rk', 'rk.id = jk.id_ruang_kelas', 'left');
        $this->db->where('jk.id', $id);
        
        $query = $this->db->get();
        return $query->row();
    }

    /**
     * Create jadwal
     */
    public function create($data) {
        $this->db->insert('jadwal_kuliah', $data);
        return $this->db->insert_id();
    }

    /**
     * Update jadwal
     */
    public function update($id, $data) {
        $this->db->where('id', $id);
        return $this->db->update('jadwal_kuliah', $data);
    }

    /**
     * Delete jadwal
     */
    public function delete($id) {
        $this->db->where('id', $id);
        return $this->db->delete('jadwal_kuliah');
    }

    /**
     * Cek bentrok ruang kelas
     * Tidak boleh ruang yang sama digunakan pada waktu yang sama untuk kelas berbeda
     */
    public function check_bentrok_ruang($id_ruang_kelas, $hari, $jam_mulai, $jam_selesai, $id_tahun_akademik, $exclude_id = null) {
        if (!$id_ruang_kelas) {
            return false; // Jika tidak ada ruang, tidak ada bentrok
        }
        
        // Cek apakah waktu bentrok (overlap)
        // Bentrok jika: (jam_mulai < existing_jam_selesai) AND (jam_selesai > existing_jam_mulai)
        $this->db->where('id_ruang_kelas', $id_ruang_kelas);
        $this->db->where('hari', $hari);
        $this->db->where('id_tahun_akademik', $id_tahun_akademik);
        $this->db->where("jam_mulai < '{$jam_selesai}'", NULL, FALSE);
        $this->db->where("jam_selesai > '{$jam_mulai}'", NULL, FALSE);
        
        if ($exclude_id) {
            $this->db->where('id !=', $exclude_id);
        }
        
        $query = $this->db->get('jadwal_kuliah');
        return $query->num_rows() > 0;
    }

    /**
     * Cek bentrok dosen
     * Tidak boleh dosen yang sama mengajar di waktu yang sama dengan kelas berbeda
     */
    public function check_bentrok_dosen($id_mapping_dosen_mk, $hari, $jam_mulai, $jam_selesai, $id_tahun_akademik, $exclude_id = null) {
        if (!$id_mapping_dosen_mk) {
            return false;
        }
        
        // Ambil id_dosen dari mapping
        $this->db->select('id_dosen');
        $this->db->where('id', $id_mapping_dosen_mk);
        $mapping = $this->db->get('mapping_dosen_mk')->row();
        
        if (!$mapping) {
            return false;
        }
        
        // Cek jadwal lain dengan dosen yang sama
        $this->db->select('jk.*');
        $this->db->from('jadwal_kuliah jk');
        $this->db->join('mapping_dosen_mk mdm', 'mdm.id = jk.id_mapping_dosen_mk', 'left');
        $this->db->where('mdm.id_dosen', $mapping->id_dosen);
        $this->db->where('jk.hari', $hari);
        $this->db->where('jk.id_tahun_akademik', $id_tahun_akademik);
        
        // Cek apakah waktu bentrok (overlap)
        $this->db->where("jk.jam_mulai < '{$jam_selesai}'", NULL, FALSE);
        $this->db->where("jk.jam_selesai > '{$jam_mulai}'", NULL, FALSE);
        
        if ($exclude_id) {
            $this->db->where('jk.id !=', $exclude_id);
        }
        
        $query = $this->db->get();
        return $query->num_rows() > 0;
    }

    /**
     * Cek bentrok kelas
     * Tidak boleh kelas yang sama ada jadwal di waktu dan hari yang sama
     */
    public function check_bentrok_kelas($kelas, $hari, $jam_mulai, $jam_selesai, $id_tahun_akademik, $exclude_id = null) {
        if (!$kelas) {
            return false;
        }
        
        $this->db->where('kelas', $kelas);
        $this->db->where('hari', $hari);
        $this->db->where('id_tahun_akademik', $id_tahun_akademik);
        
        // Cek apakah waktu bentrok (overlap)
        $this->db->where("jam_mulai < '{$jam_selesai}'", NULL, FALSE);
        $this->db->where("jam_selesai > '{$jam_mulai}'", NULL, FALSE);
        
        if ($exclude_id) {
            $this->db->where('id !=', $exclude_id);
        }
        
        $query = $this->db->get('jadwal_kuliah');
        return $query->num_rows() > 0;
    }

    /**
     * Get mapping dosen MK berdasarkan kelas
     */
    public function get_mapping_by_kelas($kelas, $id_tahun_akademik) {
        try {
            $this->db->select('mdm.id, mdm.kelas, mk.kode_mk, mk.nama_mk, mk.sks, d.nidn, d.nama_dosen');
            $this->db->from('mapping_dosen_mk mdm');
            $this->db->join('mata_kuliah_aktif mka', 'mka.id = mdm.id_mata_kuliah_aktif', 'left');
            $this->db->join('kurikulum_detail kd', 'kd.id = mka.id_kurikulum_detail', 'left');
            $this->db->join('mata_kuliah mk', 'mk.id = kd.id_mata_kuliah', 'left');
            $this->db->join('dosen d', 'd.id = mdm.id_dosen', 'left');
            $this->db->where('mdm.kelas', $kelas);
            $this->db->where('mka.id_tahun_akademik', $id_tahun_akademik);
            $this->db->order_by('mk.kode_mk', 'ASC');
            
            $query = $this->db->get();
            if ($query) {
                return $query->result();
            }
            return array();
        } catch (Exception $e) {
            log_message('error', 'Error in get_mapping_by_kelas model: ' . $e->getMessage());
            return array();
        }
    }

    /**
     * Get mapping dosen MK berdasarkan kelas yang BELUM dijadwalkan
     * Exclude mapping yang sudah ada jadwalnya untuk kelas tersebut
     * @param string $kelas
     * @param int $id_tahun_akademik
     * @param int|null $exclude_jadwal_id ID jadwal yang sedang diedit (untuk exclude dari filter)
     */
    public function get_mapping_by_kelas_available($kelas, $id_tahun_akademik, $exclude_jadwal_id = null) {
        // Ambil mapping yang sudah ada jadwalnya untuk kelas tersebut
        $this->db->select('jk.id_mapping_dosen_mk');
        $this->db->from('jadwal_kuliah jk');
        $this->db->where('jk.kelas', $kelas);
        $this->db->where('jk.id_tahun_akademik', $id_tahun_akademik);
        
        // Exclude jadwal yang sedang diedit (jika ada)
        if ($exclude_jadwal_id) {
            $this->db->where('jk.id !=', $exclude_jadwal_id);
        }
        
        $scheduled_mapping = $this->db->get()->result();
        
        // Buat array ID mapping yang sudah dijadwalkan
        $scheduled_ids = array();
        foreach ($scheduled_mapping as $sm) {
            $scheduled_ids[] = $sm->id_mapping_dosen_mk;
        }
        
        // Query mapping yang belum dijadwalkan
        $this->db->select('mdm.*, mk.kode_mk, mk.nama_mk, mk.sks, d.nidn, d.nama_dosen');
        $this->db->from('mapping_dosen_mk mdm');
        $this->db->join('mata_kuliah_aktif mka', 'mka.id = mdm.id_mata_kuliah_aktif', 'left');
        $this->db->join('kurikulum_detail kd', 'kd.id = mka.id_kurikulum_detail', 'left');
        $this->db->join('mata_kuliah mk', 'mk.id = kd.id_mata_kuliah', 'left');
        $this->db->join('dosen d', 'd.id = mdm.id_dosen', 'left');
        $this->db->where('mdm.kelas', $kelas);
        $this->db->where('mka.id_tahun_akademik', $id_tahun_akademik);
        
        // Exclude mapping yang sudah dijadwalkan
        if (!empty($scheduled_ids)) {
            $this->db->where_not_in('mdm.id', $scheduled_ids);
        }
        
        $this->db->order_by('mk.kode_mk', 'ASC');
        
        return $this->db->get()->result();
    }

    /**
     * Get semua kelas yang sudah ada mapping
     */
    public function get_kelas_with_mapping($id_tahun_akademik) {
        $this->db->distinct();
        $this->db->select('mdm.kelas');
        $this->db->from('mapping_dosen_mk mdm');
        $this->db->join('mata_kuliah_aktif mka', 'mka.id = mdm.id_mata_kuliah_aktif', 'left');
        $this->db->where('mka.id_tahun_akademik', $id_tahun_akademik);
        $this->db->where('mdm.kelas IS NOT NULL');
        $this->db->where('mdm.kelas !=', '');
        $this->db->order_by('mdm.kelas', 'ASC');
        
        return $this->db->get()->result();
    }

    /**
     * Hitung jam selesai berdasarkan jam mulai dan SKS
     * 1 SKS = 50 menit
     */
    public function calculate_jam_selesai($jam_mulai, $sks) {
        if (!$jam_mulai || !$sks) {
            return null;
        }
        
        // Parse jam mulai
        $time = strtotime($jam_mulai);
        $menit_total = $sks * 50; // 1 SKS = 50 menit
        
        // Tambahkan menit
        $jam_selesai = date('H:i:s', strtotime("+{$menit_total} minutes", $time));
        
        return $jam_selesai;
    }

    /**
     * Get jadwal kuliah by kelas dan tahun akademik
     */
    public function get_by_kelas($kelas, $id_tahun_akademik) {
        $this->db->select('jk.*, 
                          mdm.kelas as kelas_mapping,
                          mdm.id as id_mapping_dosen_mk,
                          mk.kode_mk, mk.nama_mk, mk.sks,
                          d.nidn, d.nama_dosen, d.id as id_dosen,
                          rk.kode_ruang, rk.nama_ruang, rk.id as id_ruang_kelas');
        $this->db->from('jadwal_kuliah jk');
        $this->db->join('mapping_dosen_mk mdm', 'mdm.id = jk.id_mapping_dosen_mk', 'left');
        $this->db->join('mata_kuliah_aktif mka', 'mka.id = mdm.id_mata_kuliah_aktif', 'left');
        $this->db->join('kurikulum_detail kd', 'kd.id = mka.id_kurikulum_detail', 'left');
        $this->db->join('mata_kuliah mk', 'mk.id = kd.id_mata_kuliah', 'left');
        $this->db->join('dosen d', 'd.id = mdm.id_dosen', 'left');
        $this->db->join('ruang_kelas rk', 'rk.id = jk.id_ruang_kelas', 'left');
        $this->db->where('jk.kelas', $kelas);
        $this->db->where('jk.id_tahun_akademik', $id_tahun_akademik);
        $this->db->order_by('jk.hari', 'ASC');
        $this->db->order_by('jk.jam_mulai', 'ASC');
        
        $query = $this->db->get();
        return $query->result();
    }

    /**
     * Get jadwal kuliah by dosen dan tahun akademik
     */
    public function get_by_dosen($id_dosen, $id_tahun_akademik = null, $hari = null) {
        $this->db->select('jk.*, 
                          mdm.kelas as kelas_mapping,
                          mdm.id as id_mapping_dosen_mk,
                          mk.kode_mk, mk.nama_mk, mk.sks,
                          d.nidn, d.nama_dosen, d.id as id_dosen,
                          rk.kode_ruang, rk.nama_ruang, rk.id as id_ruang_kelas,
                          ta.tahun_akademik, ta.semester');
        $this->db->from('jadwal_kuliah jk');
        $this->db->join('mapping_dosen_mk mdm', 'mdm.id = jk.id_mapping_dosen_mk', 'left');
        $this->db->join('mata_kuliah_aktif mka', 'mka.id = mdm.id_mata_kuliah_aktif', 'left');
        $this->db->join('kurikulum_detail kd', 'kd.id = mka.id_kurikulum_detail', 'left');
        $this->db->join('mata_kuliah mk', 'mk.id = kd.id_mata_kuliah', 'left');
        $this->db->join('dosen d', 'd.id = mdm.id_dosen', 'left');
        $this->db->join('ruang_kelas rk', 'rk.id = jk.id_ruang_kelas', 'left');
        $this->db->join('tahun_akademik ta', 'ta.id = jk.id_tahun_akademik', 'left');
        $this->db->where('mdm.id_dosen', $id_dosen);
        
        if ($id_tahun_akademik) {
            $this->db->where('jk.id_tahun_akademik', $id_tahun_akademik);
        }
        
        if ($hari) {
            $this->db->where('jk.hari', $hari);
        }
        
        $this->db->order_by('jk.hari', 'ASC');
        $this->db->order_by('jk.jam_mulai', 'ASC');
        
        $query = $this->db->get();
        return $query->result();
    }
}

