<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Kegiatan_model extends CI_Model {

    public function __construct() {
        parent::__construct();
        $this->load->database();
    }

    // ==================== KEGIATAN ====================

    public function get_all($limit = null, $offset = null) {
        $this->db->select('k.*, u.username as created_by_name');
        $this->db->from('kegiatan k');
        $this->db->join('users u', 'u.id = k.created_by', 'left');
        $this->db->order_by('k.tanggal_kegiatan', 'DESC');
        $this->db->order_by('k.jam_mulai', 'DESC');
        if ($limit !== null) {
            $this->db->limit($limit, $offset);
        }
        return $this->db->get()->result();
    }

    public function count_all() {
        return $this->db->count_all_results('kegiatan');
    }

    public function get_by_id($id) {
        $this->db->where('id', $id);
        return $this->db->get('kegiatan')->row();
    }

    public function get_by_token($token) {
        $this->db->where('qr_code_token', $token);
        // Tidak cek status di sini, biar controller yang handle
        return $this->db->get('kegiatan')->row();
    }

    public function create($data) {
        $this->db->insert('kegiatan', $data);
        return $this->db->insert_id();
    }

    public function update($id, $data) {
        $this->db->where('id', $id);
        $this->db->update('kegiatan', $data);
        return $this->db->affected_rows();
    }

    public function delete($id) {
        $this->db->where('id', $id);
        $this->db->delete('kegiatan');
        return $this->db->affected_rows();
    }

    // ==================== KEGIATAN KELAS ====================

    public function get_kelas_by_kegiatan($id_kegiatan) {
        $this->db->where('id_kegiatan', $id_kegiatan);
        return $this->db->get('kegiatan_kelas')->result();
    }

    public function add_kelas($id_kegiatan, $nama_kelas) {
        // Cek apakah sudah ada
        $this->db->where('id_kegiatan', $id_kegiatan);
        $this->db->where('nama_kelas', $nama_kelas);
        $exists = $this->db->get('kegiatan_kelas')->row();
        
        if (!$exists) {
            $this->db->insert('kegiatan_kelas', array(
                'id_kegiatan' => $id_kegiatan,
                'nama_kelas' => $nama_kelas
            ));
            return $this->db->insert_id();
        }
        return false;
    }

    public function delete_kelas($id_kegiatan, $nama_kelas = null) {
        $this->db->where('id_kegiatan', $id_kegiatan);
        if ($nama_kelas) {
            $this->db->where('nama_kelas', $nama_kelas);
        }
        $this->db->delete('kegiatan_kelas');
        return $this->db->affected_rows();
    }

    public function bulk_add_kelas($id_kegiatan, $kelas_list) {
        // Hapus semua kelas dulu
        $this->delete_kelas($id_kegiatan);
        
        // Tambah kelas baru
        if (!empty($kelas_list)) {
            $data_batch = array();
            foreach ($kelas_list as $nama_kelas) {
                $data_batch[] = array(
                    'id_kegiatan' => $id_kegiatan,
                    'nama_kelas' => $nama_kelas
                );
            }
            $this->db->insert_batch('kegiatan_kelas', $data_batch);
        }
        return true;
    }

    // ==================== KEGIATAN PESERTA ====================

    public function get_peserta_by_kegiatan($id_kegiatan) {
        $this->db->select('kp.*, m.nim, m.nama_mahasiswa, m.kelas');
        $this->db->from('kegiatan_peserta kp');
        $this->db->join('mahasiswa m', 'm.id = kp.id_mahasiswa', 'inner');
        $this->db->where('kp.id_kegiatan', $id_kegiatan);
        $this->db->order_by('m.kelas', 'ASC');
        $this->db->order_by('m.nim', 'ASC');
        return $this->db->get()->result();
    }

    public function get_mahasiswa_by_kelas($nama_kelas) {
        $this->db->where('kelas', $nama_kelas);
        $this->db->where('status', 'aktif');
        $this->db->order_by('nim', 'ASC');
        return $this->db->get('mahasiswa')->result();
    }

    public function add_peserta($id_kegiatan, $id_mahasiswa, $status_pendaftaran = 'wajib') {
        // Cek apakah sudah ada
        $this->db->where('id_kegiatan', $id_kegiatan);
        $this->db->where('id_mahasiswa', $id_mahasiswa);
        $exists = $this->db->get('kegiatan_peserta')->row();
        
        if (!$exists) {
            $this->db->insert('kegiatan_peserta', array(
                'id_kegiatan' => $id_kegiatan,
                'id_mahasiswa' => $id_mahasiswa,
                'status_pendaftaran' => $status_pendaftaran
            ));
            return $this->db->insert_id();
        }
        return false;
    }

    public function delete_peserta($id_kegiatan, $id_mahasiswa = null) {
        $this->db->where('id_kegiatan', $id_kegiatan);
        if ($id_mahasiswa) {
            $this->db->where('id_mahasiswa', $id_mahasiswa);
        }
        $this->db->delete('kegiatan_peserta');
        return $this->db->affected_rows();
    }

    public function bulk_add_peserta($id_kegiatan, $mahasiswa_list) {
        // Hapus semua peserta dulu
        $this->delete_peserta($id_kegiatan);
        
        // Tambah peserta baru
        if (!empty($mahasiswa_list)) {
            $data_batch = array();
            foreach ($mahasiswa_list as $id_mahasiswa) {
                $data_batch[] = array(
                    'id_kegiatan' => $id_kegiatan,
                    'id_mahasiswa' => (int)$id_mahasiswa,
                    'status_pendaftaran' => 'wajib'
                );
            }
            $this->db->insert_batch('kegiatan_peserta', $data_batch);
        }
        return true;
    }

    // ==================== STATISTIK ====================

    public function get_statistik_absensi($id_kegiatan) {
        $this->db->select('
            COUNT(DISTINCT kp.id_mahasiswa) as total_peserta,
            COUNT(DISTINCT CASE WHEN ak.status = "hadir" THEN ak.id_mahasiswa END) as hadir,
            COUNT(DISTINCT CASE WHEN ak.status = "terlambat" THEN ak.id_mahasiswa END) as terlambat,
            COUNT(DISTINCT CASE WHEN ak.status = "tidak_hadir" OR ak.id IS NULL THEN kp.id_mahasiswa END) as tidak_hadir
        ');
        $this->db->from('kegiatan_peserta kp');
        $this->db->join('absensi_kegiatan ak', 'ak.id_kegiatan = kp.id_kegiatan AND ak.id_mahasiswa = kp.id_mahasiswa', 'left');
        $this->db->where('kp.id_kegiatan', $id_kegiatan);
        return $this->db->get()->row();
    }

    // ==================== KEGIATAN UNTUK MAHASISWA ====================

    public function get_kegiatan_by_mahasiswa($id_mahasiswa, $limit = null, $offset = null) {
        $this->db->select('k.*, kp.status_pendaftaran, ak.status as status_absen, ak.waktu_absen');
        $this->db->from('kegiatan k');
        $this->db->join('kegiatan_peserta kp', 'kp.id_kegiatan = k.id', 'inner');
        $this->db->join('absensi_kegiatan ak', 'ak.id_kegiatan = k.id AND ak.id_mahasiswa = kp.id_mahasiswa', 'left');
        $this->db->where('kp.id_mahasiswa', $id_mahasiswa);
        $this->db->order_by('k.tanggal_kegiatan', 'DESC');
        $this->db->order_by('k.jam_mulai', 'DESC');
        if ($limit !== null) {
            $this->db->limit($limit, $offset);
        }
        return $this->db->get()->result();
    }

    public function count_kegiatan_by_mahasiswa($id_mahasiswa) {
        $this->db->select('COUNT(DISTINCT k.id) as total');
        $this->db->from('kegiatan k');
        $this->db->join('kegiatan_peserta kp', 'kp.id_kegiatan = k.id', 'inner');
        $this->db->where('kp.id_mahasiswa', $id_mahasiswa);
        $result = $this->db->get()->row();
        return $result ? (int)$result->total : 0;
    }

    // ==================== AUTO UPDATE STATUS ====================

    /**
     * Auto-update status kegiatan berdasarkan waktu
     * - Jika jam mulai sudah menunjukkan (>= jam mulai) dan status masih 'draft', ubah ke 'aktif'
     * - Jika jam selesai sudah terlewati dan status masih 'aktif', ubah ke 'selesai'
     * - Jika belum waktunya, status tetap 'draft'
     */
    public function auto_update_status($id_kegiatan = null) {
        // Pastikan timezone adalah Asia/Jakarta (WIB)
        if (function_exists('date_default_timezone_get') && date_default_timezone_get() != 'Asia/Jakarta') {
            date_default_timezone_set('Asia/Jakarta');
        }
        
        $now = date('Y-m-d H:i:s');
        $now_timestamp = strtotime($now);
        
        if ($id_kegiatan) {
            // Update satu kegiatan spesifik
            $kegiatan = $this->get_by_id($id_kegiatan);
            if (!$kegiatan) {
                return false;
            }
            
            // Handle jam_mulai (pastikan format H:i:s)
            $jam_mulai = trim($kegiatan->jam_mulai);
            // Handle format waktu yang mungkin ada detik atau tidak
            if (strlen($jam_mulai) == 5) { // Format H:i
                $jam_mulai = $jam_mulai . ':00';
            } elseif (strlen($jam_mulai) == 8) { // Format H:i:s, tetap gunakan
                // Sudah benar
            }
            
            $datetime_kegiatan = $kegiatan->tanggal_kegiatan . ' ' . $jam_mulai;
            $datetime_kegiatan_timestamp = strtotime($datetime_kegiatan);
            
            // Debug logging (bisa dihapus setelah fix)
            log_message('debug', "Kegiatan ID: {$id_kegiatan}, Status: {$kegiatan->status}, Waktu Sekarang: {$now}, Waktu Kegiatan: {$datetime_kegiatan}, Timestamp Sekarang: {$now_timestamp}, Timestamp Kegiatan: {$datetime_kegiatan_timestamp}");
            
            $datetime_selesai_timestamp = null;
            if ($kegiatan->jam_selesai) {
                $jam_selesai = $kegiatan->jam_selesai;
                if (strlen($jam_selesai) == 5) { // Format H:i
                    $jam_selesai = $jam_selesai . ':00';
                }
                $datetime_selesai = $kegiatan->tanggal_kegiatan . ' ' . $jam_selesai;
                $datetime_selesai_timestamp = strtotime($datetime_selesai);
            }
            
            $updated = false;
            
            // Urutan pengecekan status:
            // 1. Cek apakah sudah selesai (prioritas tertinggi)
            if ($datetime_selesai_timestamp && $now_timestamp > $datetime_selesai_timestamp) {
                if ($kegiatan->status != 'selesai' && $kegiatan->status != 'dibatalkan') {
                    $this->db->where('id', $id_kegiatan);
                    $this->db->update('kegiatan', array('status' => 'selesai'));
                    $updated = true;
                }
            }
            // 2. Cek apakah sudah waktunya mulai (>= jam mulai)
            else if ($now_timestamp >= $datetime_kegiatan_timestamp) {
                // Update ke aktif jika masih draft, atau tetap aktif jika sudah aktif
                if ($kegiatan->status == 'draft') {
                    $this->db->where('id', $id_kegiatan);
                    $result = $this->db->update('kegiatan', array('status' => 'aktif'));
                    if ($this->db->affected_rows() > 0) {
                        $updated = true;
                        log_message('debug', "Status updated dari draft ke aktif untuk kegiatan ID: {$id_kegiatan}");
                    }
                } else if ($kegiatan->status == 'selesai' && !$datetime_selesai_timestamp) {
                    // Jika tidak ada jam selesai dan status selesai, ubah ke aktif (kegiatan masih berlangsung)
                    $this->db->where('id', $id_kegiatan);
                    $this->db->update('kegiatan', array('status' => 'aktif'));
                    $updated = true;
                }
            }
            // 3. Jika belum waktunya, pastikan status 'draft'
            else if ($now_timestamp < $datetime_kegiatan_timestamp) {
                if ($kegiatan->status != 'draft' && $kegiatan->status != 'dibatalkan') {
                    $this->db->where('id', $id_kegiatan);
                    $this->db->update('kegiatan', array('status' => 'draft'));
                    $updated = true;
                }
            }
            
            return $updated;
        } else {
            // Update semua kegiatan yang perlu di-update
            // Kegiatan draft yang jam mulainya sudah menunjukkan
            $this->db->where('status', 'draft');
            $this->db->where("CONCAT(tanggal_kegiatan, ' ', IF(LENGTH(jam_mulai) = 5, CONCAT(jam_mulai, ':00'), jam_mulai)) <=", $now);
            $this->db->update('kegiatan', array('status' => 'aktif'));
            
            // Kegiatan aktif yang jam selesainya sudah terlewati
            $this->db->where('status', 'aktif');
            $this->db->where('jam_selesai IS NOT NULL');
            $this->db->where("CONCAT(tanggal_kegiatan, ' ', IF(LENGTH(jam_selesai) = 5, CONCAT(jam_selesai, ':00'), jam_selesai)) <", $now);
            $this->db->update('kegiatan', array('status' => 'selesai'));
            
            // Kegiatan yang jam mulainya belum tiba, ubah ke draft (kecuali dibatalkan)
            $this->db->where("status IN ('aktif', 'selesai')");
            $this->db->where("CONCAT(tanggal_kegiatan, ' ', IF(LENGTH(jam_mulai) = 5, CONCAT(jam_mulai, ':00'), jam_mulai)) >", $now);
            $this->db->update('kegiatan', array('status' => 'draft'));
            
            return true;
        }
    }

    /**
     * Cek dan update status kegiatan jika perlu
     */
    public function check_and_update_status($id_kegiatan) {
        $kegiatan = $this->get_by_id($id_kegiatan);
        if (!$kegiatan) {
            return false;
        }
        
        return $this->auto_update_status($id_kegiatan);
    }
}

