<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Absensi_model extends CI_Model {

    public function __construct() {
        parent::__construct();
        $this->load->database();
    }

    /**
     * Hitung jarak antara dua koordinat GPS (Haversine formula)
     * @param float $lat1 Latitude titik 1
     * @param float $lon1 Longitude titik 1
     * @param float $lat2 Latitude titik 2
     * @param float $lon2 Longitude titik 2
     * @return float Jarak dalam meter
     */
    public function calculate_distance($lat1, $lon1, $lat2, $lon2) {
        $earth_radius = 6371000; // Radius bumi dalam meter
        
        $dLat = deg2rad($lat2 - $lat1);
        $dLon = deg2rad($lon2 - $lon1);
        
        $a = sin($dLat / 2) * sin($dLat / 2) +
             cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
             sin($dLon / 2) * sin($dLon / 2);
        
        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));
        
        return $earth_radius * $c;
    }

    /**
     * Validasi apakah mahasiswa berada dalam radius yang diizinkan
     */
    public function validate_location($kegiatan_lat, $kegiatan_lon, $absen_lat, $absen_lon, $radius_meter = 20) {
        if (!$kegiatan_lat || !$kegiatan_lon || !$absen_lat || !$absen_lon) {
            return false;
        }
        
        $jarak = $this->calculate_distance($kegiatan_lat, $kegiatan_lon, $absen_lat, $absen_lon);
        return $jarak <= $radius_meter;
    }

    /**
     * Cek apakah mahasiswa sudah absen
     */
    public function is_already_absen($id_kegiatan, $id_mahasiswa) {
        $this->db->where('id_kegiatan', $id_kegiatan);
        $this->db->where('id_mahasiswa', $id_mahasiswa);
        return $this->db->get('absensi_kegiatan')->row();
    }

    /**
     * Simpan absensi
     */
    public function save_absen($data) {
        // Cek apakah sudah ada
        $existing = $this->is_already_absen($data['id_kegiatan'], $data['id_mahasiswa']);
        
        if ($existing) {
            // Update absensi yang sudah ada
            $this->db->where('id', $existing->id);
            $this->db->update('absensi_kegiatan', $data);
            return $existing->id;
        } else {
            // Insert absensi baru
            $this->db->insert('absensi_kegiatan', $data);
            return $this->db->insert_id();
        }
    }

    /**
     * Get absensi by kegiatan
     */
    public function get_absensi_by_kegiatan($id_kegiatan) {
        $this->db->select('ak.*, m.nim, m.nama_mahasiswa, m.kelas');
        $this->db->from('absensi_kegiatan ak');
        $this->db->join('mahasiswa m', 'm.id = ak.id_mahasiswa', 'inner');
        $this->db->where('ak.id_kegiatan', $id_kegiatan);
        $this->db->order_by('ak.waktu_absen', 'DESC');
        return $this->db->get()->result();
    }

    /**
     * Get absensi by mahasiswa
     */
    public function get_absensi_by_mahasiswa($id_mahasiswa, $limit = null, $offset = null) {
        $this->db->select('ak.*, k.nama_kegiatan, k.tanggal_kegiatan, k.jam_mulai');
        $this->db->from('absensi_kegiatan ak');
        $this->db->join('kegiatan k', 'k.id = ak.id_kegiatan', 'inner');
        $this->db->where('ak.id_mahasiswa', $id_mahasiswa);
        $this->db->order_by('ak.waktu_absen', 'DESC');
        if ($limit !== null) {
            $this->db->limit($limit, $offset);
        }
        return $this->db->get()->result();
    }

    /**
     * Get absensi by mahasiswa dan kegiatan
     */
    public function get_absensi_by_mahasiswa_kegiatan($id_mahasiswa, $id_kegiatan) {
        $this->db->where('id_mahasiswa', $id_mahasiswa);
        $this->db->where('id_kegiatan', $id_kegiatan);
        return $this->db->get('absensi_kegiatan')->row();
    }

    /**
     * Tentukan status absensi (hadir/terlambat)
     * Keterlambatan dihitung jika absen lebih dari 15% dari alokasi waktu (jam_mulai - jam_selesai)
     */
    public function determine_status($waktu_absen, $tanggal_kegiatan, $jam_mulai, $jam_selesai = null) {
        // Handle format jam
        if (strlen($jam_mulai) == 5) {
            $jam_mulai = $jam_mulai . ':00';
        }
        
        $waktu_kegiatan = strtotime($tanggal_kegiatan . ' ' . $jam_mulai);
        $waktu_absen_timestamp = strtotime($waktu_absen);
        
        // Jika absen sebelum jam mulai, tetap dianggap hadir
        if ($waktu_absen_timestamp < $waktu_kegiatan) {
            return 'hadir';
        }
        
        // Jika tidak ada jam selesai, gunakan toleransi default 15 menit
        if (!$jam_selesai) {
            $toleransi_menit = 15;
            $selisih_detik = $waktu_absen_timestamp - $waktu_kegiatan;
            $selisih_menit = $selisih_detik / 60;
            
            if ($selisih_menit <= $toleransi_menit) {
                return 'hadir';
            } else {
                return 'terlambat';
            }
        }
        
        // Hitung alokasi waktu dalam menit
        if (strlen($jam_selesai) == 5) {
            $jam_selesai = $jam_selesai . ':00';
        }
        
        $waktu_selesai = strtotime($tanggal_kegiatan . ' ' . $jam_selesai);
        $alokasi_detik = $waktu_selesai - $waktu_kegiatan;
        $alokasi_menit = $alokasi_detik / 60;
        
        // Hitung 15% dari alokasi waktu
        $toleransi_menit = $alokasi_menit * 0.15;
        
        // Selisih waktu absen dari jam mulai
        $selisih_detik = $waktu_absen_timestamp - $waktu_kegiatan;
        $selisih_menit = $selisih_detik / 60;
        
        // Jika absen dalam 15% alokasi waktu, dianggap hadir
        if ($selisih_menit <= $toleransi_menit) {
            return 'hadir';
        } else {
            return 'terlambat';
        }
    }
}

