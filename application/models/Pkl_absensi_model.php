<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Pkl_absensi_model
 * Model untuk absensi harian PKL (berangkat & pulang)
 */
class Pkl_absensi_model extends CI_Model {

    public function __construct() {
        parent::__construct();
        $this->load->database();
    }

    /**
     * Create absensi harian
     * @param array $data Data absensi
     * @return int ID absensi yang baru dibuat
     */
    public function create($data) {
        $this->db->insert('pkl_absensi_harian', $data);
        return $this->db->insert_id();
    }

    /**
     * Get absensi by ID
     * @param int $id ID absensi
     * @return object Data absensi
     */
    public function get_by_id($id) {
        $this->db->select('pa.*, m.nim, m.nama_mahasiswa, m.kelas, pt.nama_instansi, pt.alamat_instansi, pt.latitude as tempat_latitude, pt.longitude as tempat_longitude');
        $this->db->from('pkl_absensi_harian pa');
        $this->db->join('mahasiswa m', 'm.id = pa.id_mahasiswa', 'left');
        $this->db->join('pkl_tempat pt', 'pt.id = pa.id_pkl_tempat', 'left');
        $this->db->where('pa.id', $id);
        return $this->db->get()->row();
    }

    /**
     * Get absensi by mahasiswa
     * @param int $id_mahasiswa ID mahasiswa
     * @param string $tanggal Tanggal (Y-m-d) atau null untuk semua
     * @param string $tipe_absensi 'berangkat' atau 'pulang' atau null untuk semua
     * @return array List absensi
     */
    public function get_by_mahasiswa($id_mahasiswa, $tanggal = null, $tipe_absensi = null) {
        $this->db->select('pa.*, pt.nama_instansi');
        $this->db->from('pkl_absensi_harian pa');
        $this->db->join('pkl_tempat pt', 'pt.id = pa.id_pkl_tempat', 'left');
        $this->db->where('pa.id_mahasiswa', $id_mahasiswa);
        
        if ($tanggal) {
            $this->db->where('pa.tanggal', $tanggal);
        }
        
        if ($tipe_absensi) {
            $this->db->where('pa.tipe_absensi', $tipe_absensi);
        }
        
        $this->db->order_by('pa.tanggal', 'DESC');
        $this->db->order_by('pa.waktu_absen', 'DESC');
        return $this->db->get()->result();
    }

    /**
     * Riwayat absensi untuk API mobile (terbaru dulu, dibatasi).
     *
     * @param int $id_mahasiswa
     * @param int $limit
     * @return array
     */
    public function get_riwayat_for_api($id_mahasiswa, $limit = 120) {
        $lim = (int) $limit;
        if ($lim <= 0) {
            $lim = 120;
        }
        if ($lim > 500) {
            $lim = 500;
        }
        $this->db->select('pa.tanggal, pa.tipe_absensi, pa.waktu_absen, pa.status_radius, pa.jarak_meter, pt.nama_instansi');
        $this->db->from('pkl_absensi_harian pa');
        $this->db->join('pkl_tempat pt', 'pt.id = pa.id_pkl_tempat', 'left');
        $this->db->where('pa.id_mahasiswa', (int) $id_mahasiswa);
        $this->db->order_by('pa.waktu_absen', 'DESC');
        $this->db->limit($lim);
        return $this->db->get()->result();
    }

    /**
     * Get absensi hari ini
     * @param int $id_mahasiswa ID mahasiswa
     * @return object Data absensi hari ini (berangkat & pulang)
     */
    public function get_hari_ini($id_mahasiswa) {
        $tanggal_hari_ini = date('Y-m-d');
        $this->db->where('id_mahasiswa', $id_mahasiswa);
        $this->db->where('tanggal', $tanggal_hari_ini);
        $this->db->order_by('tipe_absensi', 'ASC');
        return $this->db->get('pkl_absensi_harian')->result();
    }

    /**
     * Cek apakah sudah absen berangkat hari ini
     * @param int $id_mahasiswa ID mahasiswa
     * @param string $tanggal Tanggal (Y-m-d), default hari ini
     * @return bool True jika sudah absen berangkat
     */
    public function sudah_absen_berangkat($id_mahasiswa, $tanggal = null) {
        if (!$tanggal) {
            $tanggal = date('Y-m-d');
        }
        
        $this->db->where('id_mahasiswa', $id_mahasiswa);
        $this->db->where('tanggal', $tanggal);
        $this->db->where('tipe_absensi', 'berangkat');
        return $this->db->get('pkl_absensi_harian')->num_rows() > 0;
    }

    /**
     * Cek apakah sudah absen pulang hari ini
     * @param int $id_mahasiswa ID mahasiswa
     * @param string $tanggal Tanggal (Y-m-d), default hari ini
     * @return bool True jika sudah absen pulang
     */
    public function sudah_absen_pulang($id_mahasiswa, $tanggal = null) {
        if (!$tanggal) {
            $tanggal = date('Y-m-d');
        }
        
        $this->db->where('id_mahasiswa', $id_mahasiswa);
        $this->db->where('tanggal', $tanggal);
        $this->db->where('tipe_absensi', 'pulang');
        return $this->db->get('pkl_absensi_harian')->num_rows() > 0;
    }

    /**
     * Get absensi by pembimbing (untuk monitoring dosen)
     * @param int $id_pembimbing ID dosen pembimbing
     * @param int $id_tahun_akademik ID tahun akademik (optional)
     * @param string $tanggal_mulai Tanggal mulai filter (optional)
     * @param string $tanggal_selesai Tanggal selesai filter (optional)
     * @return array List absensi mahasiswa bimbingan
     */
    public function get_by_pembimbing($id_pembimbing, $id_tahun_akademik = null, $tanggal_mulai = null, $tanggal_selesai = null) {
        $this->db->select('pa.*, m.nim, m.nama_mahasiswa, m.kelas, pt.nama_instansi');
        $this->db->from('pkl_absensi_harian pa');
        $this->db->join('mahasiswa m', 'm.id = pa.id_mahasiswa', 'left');
        $this->db->join('pkl_tempat pt', 'pt.id = pa.id_pkl_tempat', 'left');
        $this->db->join('pkl_bimbingan pb', 'pb.id_mahasiswa = pa.id_mahasiswa AND pb.id_pembimbing = ' . $this->db->escape($id_pembimbing), 'inner');
        
        if ($id_tahun_akademik) {
            $this->db->where('pa.id_tahun_akademik', $id_tahun_akademik);
        }
        
        if ($tanggal_mulai) {
            $this->db->where('pa.tanggal >=', $tanggal_mulai);
        }
        
        if ($tanggal_selesai) {
            $this->db->where('pa.tanggal <=', $tanggal_selesai);
        }
        
        $this->db->order_by('pa.tanggal', 'DESC');
        $this->db->order_by('pa.waktu_absen', 'DESC');
        return $this->db->get()->result();
    }

    /**
     * Get statistik absensi mahasiswa
     * @param int $id_mahasiswa ID mahasiswa
     * @param string $tanggal_mulai Tanggal mulai (optional)
     * @param string $tanggal_selesai Tanggal selesai (optional)
     * @return object Statistik (total_hari, hadir, tidak_hadir, dalam_radius, di_luar_radius)
     */
    public function get_statistik($id_mahasiswa, $tanggal_mulai = null, $tanggal_selesai = null) {
        $this->db->select('COUNT(DISTINCT tanggal) as total_hari,
                          SUM(CASE WHEN tipe_absensi = "berangkat" THEN 1 ELSE 0 END) as total_berangkat,
                          SUM(CASE WHEN tipe_absensi = "pulang" THEN 1 ELSE 0 END) as total_pulang,
                          SUM(CASE WHEN status_radius = "dalam_radius" THEN 1 ELSE 0 END) as dalam_radius,
                          SUM(CASE WHEN status_radius = "di_luar_radius" THEN 1 ELSE 0 END) as di_luar_radius');
        $this->db->from('pkl_absensi_harian');
        $this->db->where('id_mahasiswa', $id_mahasiswa);
        
        if ($tanggal_mulai) {
            $this->db->where('tanggal >=', $tanggal_mulai);
        }
        
        if ($tanggal_selesai) {
            $this->db->where('tanggal <=', $tanggal_selesai);
        }
        
        return $this->db->get()->row();
    }

    /**
     * Get statistik absensi mahasiswa pada tahun akademik tertentu.
     */
    public function get_statistik_by_tahun($id_mahasiswa, $id_tahun_akademik, $tanggal_mulai = null, $tanggal_selesai = null) {
        $this->db->select('COUNT(DISTINCT tanggal) as total_hari,
                          SUM(CASE WHEN tipe_absensi = "berangkat" THEN 1 ELSE 0 END) as total_berangkat,
                          SUM(CASE WHEN tipe_absensi = "pulang" THEN 1 ELSE 0 END) as total_pulang,
                          SUM(CASE WHEN status_radius = "dalam_radius" THEN 1 ELSE 0 END) as dalam_radius,
                          SUM(CASE WHEN status_radius = "di_luar_radius" THEN 1 ELSE 0 END) as di_luar_radius');
        $this->db->from('pkl_absensi_harian');
        $this->db->where('id_mahasiswa', $id_mahasiswa);
        $this->db->where('id_tahun_akademik', $id_tahun_akademik);

        if ($tanggal_mulai) {
            $this->db->where('tanggal >=', $tanggal_mulai);
        }

        if ($tanggal_selesai) {
            $this->db->where('tanggal <=', $tanggal_selesai);
        }

        return $this->db->get()->row();
    }

    /**
     * Hitung jumlah absensi sejak tanggal tertentu pada tahun akademik.
     */
    public function count_since_by_tahun($id_mahasiswa, $id_tahun_akademik, $tanggal_mulai) {
        $this->db->from('pkl_absensi_harian');
        $this->db->where('id_mahasiswa', $id_mahasiswa);
        $this->db->where('id_tahun_akademik', $id_tahun_akademik);
        $this->db->where('tanggal >=', $tanggal_mulai);
        return (int) $this->db->count_all_results();
    }

    /**
     * Get absensi dalam range tanggal (untuk kalender)
     * @param int $id_mahasiswa ID mahasiswa
     * @param string $tanggal_mulai Tanggal mulai
     * @param string $tanggal_selesai Tanggal selesai
     * @return array List absensi
     */
    public function get_range_tanggal($id_mahasiswa, $tanggal_mulai, $tanggal_selesai) {
        $this->db->where('id_mahasiswa', $id_mahasiswa);
        $this->db->where('tanggal >=', $tanggal_mulai);
        $this->db->where('tanggal <=', $tanggal_selesai);
        $this->db->order_by('tanggal', 'ASC');
        $this->db->order_by('tipe_absensi', 'ASC');
        return $this->db->get('pkl_absensi_harian')->result();
    }

    /**
     * Update absensi
     * @param int $id ID absensi
     * @param array $data Data yang akan diupdate
     * @return int Jumlah baris yang terpengaruh
     */
    public function update($id, $data) {
        $this->db->where('id', $id);
        $this->db->update('pkl_absensi_harian', $data);
        return $this->db->affected_rows();
    }

    /**
     * Delete absensi
     * @param int $id ID absensi
     * @return int Jumlah baris yang terpengaruh
     */
    public function delete($id) {
        $this->db->where('id', $id);
        $this->db->delete('pkl_absensi_harian');
        return $this->db->affected_rows();
    }

    /**
     * Get absensi berangkat pertama mahasiswa (untuk device fingerprinting)
     * Device referensi utama adalah device saat absen berangkat pertama kali
     * @param int $id_mahasiswa ID mahasiswa
     * @return object Data absensi berangkat pertama atau null
     */
    public function get_first_absensi($id_mahasiswa) {
        $this->db->where('id_mahasiswa', $id_mahasiswa);
        $this->db->where('tipe_absensi', 'berangkat');
        $this->db->where('device_fingerprint IS NOT NULL');
        $this->db->where('device_fingerprint !=', '');
        $this->db->order_by('waktu_absen', 'ASC');
        $this->db->limit(1);
        return $this->db->get('pkl_absensi_harian')->row();
    }

    /**
     * Get statistik device untuk mahasiswa
     * @param int $id_mahasiswa ID mahasiswa
     * @return object Statistik device (total_absensi, device_sama, device_berbeda)
     */
    public function get_device_statistics($id_mahasiswa) {
        $this->db->select('COUNT(*) as total_absensi,
                          SUM(CASE WHEN is_device_changed = 0 THEN 1 ELSE 0 END) as device_sama,
                          SUM(CASE WHEN is_device_changed = 1 THEN 1 ELSE 0 END) as device_berbeda');
        $this->db->from('pkl_absensi_harian');
        $this->db->where('id_mahasiswa', $id_mahasiswa);
        $this->db->where('device_fingerprint IS NOT NULL');
        return $this->db->get()->row();
    }

    /**
     * Get statistik device untuk mahasiswa pada tahun akademik tertentu.
     */
    public function get_device_statistics_by_tahun($id_mahasiswa, $id_tahun_akademik) {
        $this->db->select('COUNT(*) as total_absensi,
                          SUM(CASE WHEN is_device_changed = 0 THEN 1 ELSE 0 END) as device_sama,
                          SUM(CASE WHEN is_device_changed = 1 THEN 1 ELSE 0 END) as device_berbeda');
        $this->db->from('pkl_absensi_harian');
        $this->db->where('id_mahasiswa', $id_mahasiswa);
        $this->db->where('id_tahun_akademik', $id_tahun_akademik);
        $this->db->where('device_fingerprint IS NOT NULL');
        return $this->db->get()->row();
    }
}

