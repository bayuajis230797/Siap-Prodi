<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Jadwal UAS Model
 * Mengelola data jadwal UAS dengan validasi bentrok
 */
class Jadwal_uas_model extends CI_Model {

    public function __construct() {
        parent::__construct();
        $this->load->database();
    }

    /**
     * Get semua jadwal UAS dengan pagination
     */
    public function get_all($id_tahun_akademik = null, $limit = 10, $offset = 0, $filter_tanggal = null, $filter_kelas = null) {
        $this->db->select('jua.*, 
                          jua.kelas,
                          mk.kode_mk, mk.nama_mk, mk.sks,
                          d.nidn, d.nama_dosen, mdm.id_dosen as id_dosen_pengampu,
                          rk.kode_ruang, rk.nama_ruang,
                          ta.tahun_akademik, ta.semester,
                          t.nama_tendik, t.nipy as nipy_tendik');
        $this->db->from('jadwal_uas jua');
        $this->db->join('mapping_dosen_mk mdm', 'mdm.id = jua.id_mapping_dosen_mk', 'left');
        $this->db->join('mata_kuliah_aktif mka', 'mka.id = mdm.id_mata_kuliah_aktif', 'left');
        $this->db->join('kurikulum_detail kd', 'kd.id = mka.id_kurikulum_detail', 'left');
        $this->db->join('mata_kuliah mk', 'mk.id = kd.id_mata_kuliah', 'left');
        $this->db->join('dosen d', 'd.id = mdm.id_dosen', 'left');
        $this->db->join('ruang_kelas rk', 'rk.id = jua.id_ruang_kelas', 'left');
        $this->db->join('tahun_akademik ta', 'ta.id = jua.id_tahun_akademik', 'left');
        $this->db->join('tendik t', 't.id = jua.id_tendik', 'left');
        $this->db->join('jadwal_uas_berita_acara juba', 'juba.id_jadwal_uas = jua.id', 'left');
        $this->db->select('CASE WHEN juba.id IS NULL THEN 0 ELSE 1 END as has_berita_acara', false);
        
        if ($id_tahun_akademik) {
            $this->db->where('jua.id_tahun_akademik', $id_tahun_akademik);
        }
        
        if ($filter_tanggal) {
            $this->db->where('jua.tanggal', $filter_tanggal);
        }
        
        if ($filter_kelas) {
            $this->db->where('jua.kelas', $filter_kelas);
        }
        
        $this->db->order_by('jua.tanggal', 'ASC');
        $this->db->order_by('jua.jam_mulai', 'ASC');
        $this->db->order_by('jua.kelas', 'ASC');
        $this->db->limit($limit, $offset);
        
        $query = $this->db->get();
        $results = $query->result();
        
        // Ambil pengawas untuk setiap jadwal
        foreach ($results as $result) {
            $result->pengawas = $this->get_pengawas($result->id);
        }
        
        return $results;
    }

    /**
     * Count semua jadwal UAS
     */
    public function count_all($id_tahun_akademik = null, $filter_tanggal = null, $filter_kelas = null) {
        $this->db->from('jadwal_uas jua');
        
        if ($id_tahun_akademik) {
            $this->db->where('jua.id_tahun_akademik', $id_tahun_akademik);
        }
        
        if ($filter_tanggal) {
            $this->db->where('jua.tanggal', $filter_tanggal);
        }
        
        if ($filter_kelas) {
            $this->db->where('jua.kelas', $filter_kelas);
        }
        
        return $this->db->count_all_results();
    }

    /**
     * Get jadwal UAS by ID
     */
    public function get_by_id($id) {
        $this->db->select('jua.*, 
                          mk.kode_mk, mk.nama_mk, mk.sks,
                          d.nidn, d.nama_dosen, mdm.id_dosen as id_dosen_pengampu,
                          rk.kode_ruang, rk.nama_ruang, rk.id as id_ruang_kelas,
                          t.nama_tendik, t.nipy as nipy_tendik');
        $this->db->from('jadwal_uas jua');
        $this->db->join('mapping_dosen_mk mdm', 'mdm.id = jua.id_mapping_dosen_mk', 'left');
        $this->db->join('mata_kuliah_aktif mka', 'mka.id = mdm.id_mata_kuliah_aktif', 'left');
        $this->db->join('kurikulum_detail kd', 'kd.id = mka.id_kurikulum_detail', 'left');
        $this->db->join('mata_kuliah mk', 'mk.id = kd.id_mata_kuliah', 'left');
        $this->db->join('dosen d', 'd.id = mdm.id_dosen', 'left');
        $this->db->join('ruang_kelas rk', 'rk.id = jua.id_ruang_kelas', 'left');
        $this->db->join('tendik t', 't.id = jua.id_tendik', 'left');
        $this->db->where('jua.id', $id);
        
        $query = $this->db->get();
        $result = $query->row();
        
        if ($result) {
            $result->pengawas = $this->get_pengawas($id);
            // Jika sesi 2 dan ada tendik, tambahkan tendik ke pengawas untuk display
            if ($result->sesi == 2 && $result->nama_tendik) {
                $tendik_pengawas = new stdClass();
                $tendik_pengawas->id = 'tendik_' . $result->id_tendik;
                $tendik_pengawas->nidn = $result->nipy_tendik;
                $tendik_pengawas->nama_dosen = $result->nama_tendik . ' (Tendik)';
                $result->pengawas[] = $tendik_pengawas;
            }
        }
        
        return $result;
    }

    /**
     * Create jadwal UAS
     */
    public function create($data) {
        // Set default sesi jika tidak ada
        if (!isset($data['sesi'])) {
            $data['sesi'] = 1;
        }
        
        // Generate QR Code token dan file
        $this->load->helper('qrcode');
        $token = $this->generate_unique_token();
        $data['qr_code_token'] = $token;
        
        // Generate QR Code (token mentah — scanner membaca UTS_/UAS_...)
        $qr_data = qr_code_payload_for_ujian($token);
        $filename = 'uas_' . time() . '_' . uniqid();
        $qr_code_path = generate_qr_code($qr_data, $filename);
        $data['qr_code'] = $qr_code_path;
        
        // Cek error sebelum insert
        if ($this->db->insert('jadwal_uas', $data)) {
            return $this->db->insert_id();
        } else {
            // Log error jika ada
            $error = $this->db->error();
            log_message('error', 'Jadwal UAS Create Error: ' . $error['message']);
            return false;
        }
    }
    
    /**
     * Generate unique token untuk QR Code
     */
    private function generate_unique_token() {
        do {
            $token = 'UAS_' . bin2hex(random_bytes(16));
            $this->db->where('qr_code_token', $token);
            $exists = $this->db->get('jadwal_uas')->num_rows() > 0;
        } while ($exists);
        
        return $token;
    }
    
    /**
     * Get jadwal UAS by QR Code token
     */
    public function get_by_token($token) {
        $this->db->select('jua.*, 
                          mk.kode_mk, mk.nama_mk, mk.sks,
                          d.nidn, d.nama_dosen, mdm.id_dosen as id_dosen_pengampu,
                          rk.kode_ruang, rk.nama_ruang,
                          t.nama_tendik, t.nipy as nipy_tendik');
        $this->db->from('jadwal_uas jua');
        $this->db->join('mapping_dosen_mk mdm', 'mdm.id = jua.id_mapping_dosen_mk', 'left');
        $this->db->join('mata_kuliah_aktif mka', 'mka.id = mdm.id_mata_kuliah_aktif', 'left');
        $this->db->join('kurikulum_detail kd', 'kd.id = mka.id_kurikulum_detail', 'left');
        $this->db->join('mata_kuliah mk', 'mk.id = kd.id_mata_kuliah', 'left');
        $this->db->join('dosen d', 'd.id = mdm.id_dosen', 'left');
        $this->db->join('ruang_kelas rk', 'rk.id = jua.id_ruang_kelas', 'left');
        $this->db->join('tendik t', 't.id = jua.id_tendik', 'left');
        $this->db->where('jua.qr_code_token', $token);
        
        return $this->db->get()->row();
    }
    
    /**
     * Reset QR Code untuk jadwal UAS
     */
    public function reset_qr_code($id) {
        $this->load->helper('qrcode');
        
        // Hapus QR Code lama jika ada
        $jadwal = $this->get_by_id($id);
        if ($jadwal && $jadwal->qr_code) {
            delete_qr_code($jadwal->qr_code);
        }
        
        // Generate token baru
        $token = $this->generate_unique_token();
        
        // Generate QR Code baru (token mentah)
        $qr_data = qr_code_payload_for_ujian($token);
        $filename = 'uas_' . time() . '_' . uniqid();
        $qr_code_path = generate_qr_code($qr_data, $filename);
        
        // Update ke database
        $this->db->where('id', $id);
        return $this->db->update('jadwal_uas', array(
            'qr_code_token' => $token,
            'qr_code' => $qr_code_path
        ));
    }

    /**
     * Buat ulang file PNG QR tanpa mengganti token (untuk cetak / file hilang).
     */
    public function ensure_qr_code_file($id) {
        $this->load->helper('qrcode');
        $jadwal = $this->get_by_id($id);
        if (!$jadwal || empty($jadwal->qr_code_token)) {
            return false;
        }
        if (!empty($jadwal->qr_code) && qr_code_resolve_full_path($jadwal->qr_code)) {
            return true;
        }
        if (!empty($jadwal->qr_code)) {
            delete_qr_code($jadwal->qr_code);
        }
        $filename = 'uas_' . $id . '_' . substr(md5($jadwal->qr_code_token), 0, 12);
        $qr_code_path = generate_qr_code(qr_code_payload_for_ujian($jadwal->qr_code_token), $filename);
        $this->db->where('id', $id);
        return $this->db->update('jadwal_uas', array('qr_code' => $qr_code_path));
    }

    /**
     * Update jadwal UAS
     */
    public function update($id, $data) {
        $this->db->where('id', $id);
        return $this->db->update('jadwal_uas', $data);
    }

    /**
     * Delete jadwal UAS
     */
    public function delete($id) {
        $this->db->where('id', $id);
        return $this->db->delete('jadwal_uas');
    }

    /**
     * Hitung jam selesai berdasarkan jam mulai dan SKS
     * 1 SKS = 50 menit
     */
    public function calculate_jam_selesai($jam_mulai, $sks) {
        if (empty($jam_mulai) || empty($sks)) {
            return null;
        }
        
        $time = strtotime($jam_mulai);
        $menit_total = $sks * 50;
        $time = strtotime("+{$menit_total} minutes", $time);
        
        return date('H:i:s', $time);
    }

    /**
     * Get mapping dosen MK berdasarkan kelas (untuk dropdown)
     */
    public function get_mapping_by_kelas($kelas, $id_tahun_akademik) {
        $this->db->select('mdm.*, mk.kode_mk, mk.nama_mk, mk.sks, d.nidn, d.nama_dosen');
        $this->db->from('mapping_dosen_mk mdm');
        $this->db->join('mata_kuliah_aktif mka', 'mka.id = mdm.id_mata_kuliah_aktif', 'left');
        $this->db->join('kurikulum_detail kd', 'kd.id = mka.id_kurikulum_detail', 'left');
        $this->db->join('mata_kuliah mk', 'mk.id = kd.id_mata_kuliah', 'left');
        $this->db->join('dosen d', 'd.id = mdm.id_dosen', 'left');
        $this->db->where('mdm.kelas', $kelas);
        $this->db->where('mka.id_tahun_akademik', $id_tahun_akademik);
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
     * Cek bentrok ruang kelas
     * Tidak boleh ruang yang sama digunakan pada waktu yang sama untuk kelas berbeda
     */
    public function check_bentrok_ruang($id_ruang_kelas, $tanggal, $jam_mulai, $jam_selesai, $id_tahun_akademik, $exclude_id = null) {
        if (!$id_ruang_kelas) {
            return false;
        }
        
        $this->db->where('id_ruang_kelas', $id_ruang_kelas);
        $this->db->where('tanggal', $tanggal);
        $this->db->where('id_tahun_akademik', $id_tahun_akademik);
        $this->db->where("jam_mulai < '{$jam_selesai}'", NULL, FALSE);
        $this->db->where("jam_selesai > '{$jam_mulai}'", NULL, FALSE);
        
        if ($exclude_id) {
            $this->db->where('id !=', $exclude_id);
        }
        
        $query = $this->db->get('jadwal_uas');
        return $query->num_rows() > 0;
    }

    /**
     * Cek bentrok dosen
     * Tidak boleh dosen yang sama mengawasi ujian di waktu yang sama dengan kelas berbeda
     */
    public function check_bentrok_dosen($id_mapping_dosen_mk, $tanggal, $jam_mulai, $jam_selesai, $id_tahun_akademik, $exclude_id = null) {
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
        $this->db->select('jua.*');
        $this->db->from('jadwal_uas jua');
        $this->db->join('mapping_dosen_mk mdm', 'mdm.id = jua.id_mapping_dosen_mk', 'left');
        $this->db->where('mdm.id_dosen', $mapping->id_dosen);
        $this->db->where('jua.tanggal', $tanggal);
        $this->db->where('jua.id_tahun_akademik', $id_tahun_akademik);
        
        // Cek apakah waktu bentrok (overlap)
        $this->db->where("jua.jam_mulai < '{$jam_selesai}'", NULL, FALSE);
        $this->db->where("jua.jam_selesai > '{$jam_mulai}'", NULL, FALSE);
        
        if ($exclude_id) {
            $this->db->where('jua.id !=', $exclude_id);
        }
        
        $query = $this->db->get();
        return $query->num_rows() > 0;
    }

    /**
     * Cek bentrok kelas
     * Tidak boleh kelas yang sama ada jadwal UAS di waktu yang sama
     */
    public function check_bentrok_kelas($kelas, $tanggal, $jam_mulai, $jam_selesai, $id_tahun_akademik, $exclude_id = null) {
        if (!$kelas) {
            return false;
        }
        
        $this->db->where('kelas', $kelas);
        $this->db->where('tanggal', $tanggal);
        $this->db->where('id_tahun_akademik', $id_tahun_akademik);
        
        // Cek apakah waktu bentrok (overlap)
        $this->db->where("jam_mulai < '{$jam_selesai}'", NULL, FALSE);
        $this->db->where("jam_selesai > '{$jam_mulai}'", NULL, FALSE);
        
        if ($exclude_id) {
            $this->db->where('id !=', $exclude_id);
        }
        
        $query = $this->db->get('jadwal_uas');
        return $query->num_rows() > 0;
    }

    /**
     * Get pengawas untuk jadwal UAS
     */
    public function get_pengawas($id_jadwal_uas) {
        $this->db->select('jup.id_dosen, jup.id_tendik, d.id as id_dosen_full, d.nidn, d.nama_dosen, t.id as id_tendik_full, t.nipy, t.nama_tendik');
        $this->db->from('jadwal_uas_pengawas jup');
        $this->db->join('dosen d', 'd.id = jup.id_dosen', 'left');
        $this->db->join('tendik t', 't.id = jup.id_tendik', 'left');
        $this->db->where('jup.id_jadwal_uas', $id_jadwal_uas);
        $this->db->order_by('COALESCE(d.nama_dosen, t.nama_tendik) ASC', '', false);
        
        return $this->db->get()->result();
    }

    /**
     * Get jadwal UAS by kelas
     */
    public function get_by_kelas($kelas, $id_tahun_akademik) {
        $this->db->select('jua.*, 
                          mk.kode_mk, mk.nama_mk, mk.sks,
                          d.nidn, d.nama_dosen,
                          rk.kode_ruang, rk.nama_ruang,
                          t.nama_tendik, t.nipy as nipy_tendik');
        $this->db->from('jadwal_uas jua');
        $this->db->join('mapping_dosen_mk mdm', 'mdm.id = jua.id_mapping_dosen_mk', 'left');
        $this->db->join('mata_kuliah_aktif mka', 'mka.id = mdm.id_mata_kuliah_aktif', 'left');
        $this->db->join('kurikulum_detail kd', 'kd.id = mka.id_kurikulum_detail', 'left');
        $this->db->join('mata_kuliah mk', 'mk.id = kd.id_mata_kuliah', 'left');
        $this->db->join('dosen d', 'd.id = mdm.id_dosen', 'left');
        $this->db->join('ruang_kelas rk', 'rk.id = jua.id_ruang_kelas', 'left');
        $this->db->join('tendik t', 't.id = jua.id_tendik', 'left');
        $this->db->where('jua.kelas', $kelas);
        $this->db->where('jua.id_tahun_akademik', $id_tahun_akademik);
        $this->db->order_by('jua.tanggal', 'ASC');
        $this->db->order_by('jua.jam_mulai', 'ASC');
        $this->db->order_by('jua.sesi', 'ASC');
        
        $query = $this->db->get();
        $results = $query->result();
        
        // Ambil pengawas untuk setiap jadwal
        foreach ($results as $result) {
            $result->pengawas = $this->get_pengawas($result->id);
        }
        
        return $results;
    }

    /**
     * Set pengawas untuk jadwal UAS (replace existing)
     */
    public function set_pengawas($id_jadwal_uas, $id_dosen_array) {
        // Hapus pengawas lama
        $this->db->where('id_jadwal_uas', $id_jadwal_uas);
        $this->db->delete('jadwal_uas_pengawas');
        
        // Insert pengawas baru
        if (!empty($id_dosen_array) && is_array($id_dosen_array)) {
            foreach ($id_dosen_array as $id_dosen) {
                if (!empty($id_dosen)) {
                    $this->db->insert('jadwal_uas_pengawas', array(
                        'id_jadwal_uas' => $id_jadwal_uas,
                        'id_dosen' => $id_dosen,
                        'id_tendik' => null
                    ));
                }
            }
        }
        
        return true;
    }

    /**
     * Set pengawas tunggal untuk jadwal UAS (dosen atau tendik)
     */
    public function set_pengawas_single($id_jadwal_uas, $type, $id) {
        // Hapus pengawas lama
        $this->db->where('id_jadwal_uas', $id_jadwal_uas);
        $this->db->delete('jadwal_uas_pengawas');
        
        // Insert pengawas baru
        $data = array(
            'id_jadwal_uas' => $id_jadwal_uas
        );
        
        if ($type == 'dosen') {
            $data['id_dosen'] = $id;
            $data['id_tendik'] = null;
        } elseif ($type == 'tendik') {
            $data['id_dosen'] = null;
            $data['id_tendik'] = $id;
        }
        
        $this->db->insert('jadwal_uas_pengawas', $data);
        
        return true;
    }

    /**
     * Cek bentrok pengawas (dosen)
     * Tidak boleh pengawas yang sama mengawasi ujian di waktu yang sama
     */
    public function check_bentrok_pengawas($id_dosen_array, $tanggal, $jam_mulai, $jam_selesai, $id_tahun_akademik, $exclude_id = null) {
        if (empty($id_dosen_array) || !is_array($id_dosen_array)) {
            return false;
        }
        
        foreach ($id_dosen_array as $id_dosen) {
            if (empty($id_dosen)) {
                continue;
            }
            
            // Cek apakah dosen ini sudah menjadi pengawas di jadwal lain pada waktu yang sama
            $this->db->select('jua.*');
            $this->db->from('jadwal_uas jua');
            $this->db->join('jadwal_uas_pengawas jup', 'jup.id_jadwal_uas = jua.id', 'left');
            $this->db->where('jup.id_dosen', $id_dosen);
            $this->db->where('jua.tanggal', $tanggal);
            $this->db->where('jua.id_tahun_akademik', $id_tahun_akademik);
            
            // Cek apakah waktu bentrok (overlap)
            $this->db->where("jua.jam_mulai < '{$jam_selesai}'", NULL, FALSE);
            $this->db->where("jua.jam_selesai > '{$jam_mulai}'", NULL, FALSE);
            
            if ($exclude_id) {
                $this->db->where('jua.id !=', $exclude_id);
            }
            
            $query = $this->db->get();
            if ($query->num_rows() > 0) {
                return true; // Ada bentrok
            }
        }
        
        return false; // Tidak ada bentrok
    }

    /**
     * Cek bentrok tendik sebagai pengawas
     * Tidak boleh tendik yang sama mengawasi ujian di waktu yang sama
     */
    public function check_bentrok_tendik($id_tendik, $tanggal, $jam_mulai, $jam_selesai, $id_tahun_akademik, $exclude_id = null) {
        if (empty($id_tendik)) {
            return false;
        }
        
        // Cek apakah tendik ini sudah menjadi pengawas di jadwal lain pada waktu yang sama
        $this->db->select('jua.*');
        $this->db->from('jadwal_uas jua');
        $this->db->join('jadwal_uas_pengawas jup', 'jup.id_jadwal_uas = jua.id', 'left');
        $this->db->where('jup.id_tendik', $id_tendik);
        $this->db->where('jua.tanggal', $tanggal);
        $this->db->where('jua.id_tahun_akademik', $id_tahun_akademik);
        
        // Cek apakah waktu bentrok (overlap)
        $this->db->where("jua.jam_mulai < '{$jam_selesai}'", NULL, FALSE);
        $this->db->where("jua.jam_selesai > '{$jam_mulai}'", NULL, FALSE);
        
        if ($exclude_id) {
            $this->db->where('jua.id !=', $exclude_id);
        }
        
        $query = $this->db->get();
        return $query->num_rows() > 0; // true jika ada bentrok, false jika tidak
    }
}

