<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Logika absensi untuk API mobile (mirror Absensi + Absensi_ujian).
 */
class Mobile_absensi_service {

    /** @var CI_Controller */
    protected $CI;

    public function __construct() {
        $this->CI =& get_instance();
        $this->CI->load->model('Kegiatan_model');
        $this->CI->load->model('Absensi_model');
        $this->CI->load->model('Mahasiswa_model');
        $this->CI->load->model('Jadwal_uts_model');
        $this->CI->load->model('Jadwal_uas_model');
        $this->CI->load->helper('gps');
    }

    /**
     * Samakan dengan isi QR: URL CI (?/controller/method/token), URL path, atau token mentah.
     *
     * @param string $raw
     * @return string
     */
    protected function normalize_scan_token($raw) {
        $t = trim((string) $raw);
        if ($t === '') {
            return '';
        }
        $t = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/', '', $t);
        $t = preg_replace('/^\xEF\xBB\xBF/', '', $t);
        if (preg_match('/(UTS_[A-Fa-f0-9]+)/', $t, $m)) {
            return $m[1];
        }
        if (preg_match('/(UAS_[A-Fa-f0-9]+)/', $t, $m)) {
            return $m[1];
        }
        if (preg_match('/(UTS_\d+)/', $t, $m)) {
            return $m[1];
        }
        if (preg_match('/(UAS_\d+)/', $t, $m)) {
            return $m[1];
        }
        if (preg_match('#https?://#i', $t)) {
            $p = parse_url($t);
            if (!empty($p['query'])) {
                $qraw = $p['query'];
                if ($qraw[0] === '/' || strncasecmp($qraw, '%2F', 3) === 0) {
                    $q = rawurldecode($qraw);
                    $parts = array_values(array_filter(explode('/', $q)));
                    $last = end($parts);
                    if ($last !== false && $last !== '') {
                        return rawurldecode($last);
                    }
                }
            }
            if (!empty($p['path'])) {
                $parts = array_values(array_filter(explode('/', $p['path'])));
                $last = end($parts);
                if ($last !== false && $last !== '') {
                    return rawurldecode($last);
                }
            }
        }
        if (preg_match('#Absensi_ujian/scan/([^/?&#\s]+)#', $t, $m)) {
            return rawurldecode($m[1]);
        }
        if (preg_match('#Absensi/scan/([^/?&#\s]+)#', $t, $m)) {
            return rawurldecode($m[1]);
        }
        if (strpos($t, '/') !== false && !preg_match('#^https?://#i', $t)) {
            $parts = array_values(array_filter(explode('/', $t)));
            $last = end($parts);
            if ($last !== false && $last !== '') {
                return $last;
            }
        }
        return $t;
    }

    /**
     * @param int $id_mahasiswa
     * @param string $token
     * @param string|null $latitude
     * @param string|null $longitude
     * @return array
     */
    public function scan_kegiatan($id_mahasiswa, $token, $latitude = null, $longitude = null) {
        $id_mahasiswa = (int) $id_mahasiswa;
        if ($id_mahasiswa <= 0) {
            return array('success' => false, 'message' => 'Data mahasiswa tidak valid.');
        }
        if ($token === '' || $token === null) {
            return array('success' => false, 'message' => 'Token QR Code tidak valid.');
        }
        $token = $this->normalize_scan_token($token);

        $kegiatan = $this->CI->Kegiatan_model->get_by_token($token);
        if (!$kegiatan) {
            return array(
                'success' => false,
                'message' => 'QR Code tidak valid atau kegiatan tidak ditemukan.',
            );
        }

        $this->CI->Kegiatan_model->auto_update_status($kegiatan->id);
        $kegiatan = $this->CI->Kegiatan_model->get_by_id($kegiatan->id);

        if ($kegiatan->status !== 'aktif') {
            if ($kegiatan->status == 'draft') {
                $datetime_kegiatan = $kegiatan->tanggal_kegiatan . ' ' . $kegiatan->jam_mulai;
                return array(
                    'success' => false,
                    'message' => 'Kegiatan belum dimulai. Kegiatan akan dimulai pada: ' . date('d/m/Y H:i', strtotime($datetime_kegiatan)),
                );
            }
            return array(
                'success' => false,
                'message' => 'Kegiatan sudah selesai atau dibatalkan. Status: ' . ucfirst($kegiatan->status),
            );
        }

        $peserta = $this->CI->Kegiatan_model->get_peserta_by_kegiatan($kegiatan->id);
        $is_peserta = false;
        foreach ($peserta as $p) {
            if ((int) $p->id_mahasiswa === $id_mahasiswa) {
                $is_peserta = true;
                break;
            }
        }
        if (!$is_peserta) {
            return array('success' => false, 'message' => 'Anda tidak terdaftar sebagai peserta kegiatan ini.');
        }

        $mahasiswa = $this->CI->Mahasiswa_model->get_by_id($id_mahasiswa);
        if (!$mahasiswa) {
            return array('success' => false, 'message' => 'Data mahasiswa tidak ditemukan.');
        }

        $jarak_meter = null;
        $valid_location = true;
        if ($kegiatan->latitude && $kegiatan->longitude && $latitude && $longitude) {
            $jarak_meter = $this->CI->Absensi_model->calculate_distance(
                $kegiatan->latitude,
                $kegiatan->longitude,
                $latitude,
                $longitude
            );
            $valid_location = $this->CI->Absensi_model->validate_location(
                $kegiatan->latitude,
                $kegiatan->longitude,
                $latitude,
                $longitude,
                $kegiatan->radius_meter
            );
            if (!$valid_location) {
                return array(
                    'success' => false,
                    'message' => 'Anda berada di luar radius yang diizinkan. Jarak: ' . number_format($jarak_meter, 2) . ' meter (maksimal: ' . $kegiatan->radius_meter . ' meter).',
                );
            }
        }

        $existing_absen = $this->CI->Absensi_model->is_already_absen($kegiatan->id, $id_mahasiswa);
        if ($existing_absen) {
            return array(
                'success' => false,
                'message' => 'Anda sudah melakukan absensi pada ' . date('d/m/Y H:i', strtotime($existing_absen->waktu_absen)) . '.',
            );
        }

        $waktu_absen = date('Y-m-d H:i:s');
        $status = $this->CI->Absensi_model->determine_status(
            $waktu_absen,
            $kegiatan->tanggal_kegiatan,
            $kegiatan->jam_mulai,
            $kegiatan->jam_selesai
        );

        $data_absen = array(
            'id_kegiatan' => $kegiatan->id,
            'id_mahasiswa' => $id_mahasiswa,
            'nim' => $mahasiswa->nim,
            'waktu_absen' => $waktu_absen,
            'latitude_absen' => $latitude ? $latitude : null,
            'longitude_absen' => $longitude ? $longitude : null,
            'jarak_meter' => $jarak_meter,
            'ip_address' => $this->CI->input->ip_address(),
            'user_agent' => $this->CI->input->user_agent(),
            'status' => $status,
        );

        $result = $this->CI->Absensi_model->save_absen($data_absen);
        if ($result) {
            return array(
                'success' => true,
                'message' => 'Absensi berhasil. Kehadiran pada kegiatan tercatat: ' . ($status == 'hadir' ? 'Hadir' : 'Terlambat') . '.',
                'status' => $status,
                'waktu_absen' => date('d/m/Y H:i', strtotime($waktu_absen)),
                'tipe' => 'kegiatan',
            );
        }
        return array('success' => false, 'message' => 'Gagal menyimpan absensi.');
    }

    /**
     * @param int $id_mahasiswa
     * @param string $token
     * @param string $mode uts|uas|ujian (ujian = coba UTS lalu UAS)
     * @return array
     */
    public function scan_ujian($id_mahasiswa, $token, $mode = 'ujian') {
        $id_mahasiswa = (int) $id_mahasiswa;
        $mode = strtolower((string) $mode);
        if ($id_mahasiswa <= 0) {
            return array('success' => false, 'message' => 'Data mahasiswa tidak valid.');
        }
        if ($token === '' || $token === null) {
            return array('success' => false, 'message' => 'Token QR Code tidak valid.');
        }
        $token = $this->normalize_scan_token($token);

        $candidates = array();
        if ($mode === 'uts') {
            $candidates[] = 'UTS';
        } elseif ($mode === 'uas') {
            $candidates[] = 'UAS';
        } else {
            $candidates = array('UTS', 'UAS');
        }

        $resolved = null;
        foreach ($candidates as $jenis_try) {
            $r = $this->resolve_jadwal_ujian($token, $jenis_try);
            if ($r) {
                $resolved = $r;
                break;
            }
        }

        if (!$resolved) {
            return array('success' => false, 'message' => 'QR Code tidak valid atau jadwal tidak ditemukan.');
        }

        $jadwal = $resolved['jadwal'];
        $jenis_ujian = $resolved['jenis_ujian'];
        $table_kehadiran = $resolved['table_kehadiran'];
        $id_jadwal_field = $resolved['id_jadwal_field'];

        $this->CI->load->helper('absensi');
        $window = absensi_ujian_window_check($jadwal);
        if (empty($window['ok'])) {
            return array('success' => false, 'message' => $window['message']);
        }

        $mahasiswa = $this->CI->Mahasiswa_model->get_by_id($id_mahasiswa);
        if (!$mahasiswa) {
            return array('success' => false, 'message' => 'Data mahasiswa tidak ditemukan.');
        }

        if ($mahasiswa->kelas != $jadwal->kelas) {
            return array(
                'success' => false,
                'message' => 'QR Code tidak sesuai dengan kelas Anda. Kelas Anda: ' . $mahasiswa->kelas . ', Kelas Jadwal: ' . $jadwal->kelas,
            );
        }

        $table_sesi = ($jenis_ujian == 'UTS') ? 'jadwal_uts_mahasiswa_sesi' : 'jadwal_uas_mahasiswa_sesi';
        $this->CI->db->where($id_jadwal_field, $jadwal->id);
        $this->CI->db->where('id_mahasiswa', $id_mahasiswa);
        $mahasiswa_sesi = $this->CI->db->get($table_sesi)->row();

        if (!$mahasiswa_sesi) {
            $this->CI->db->where($id_jadwal_field, $jadwal->id);
            $total_peserta = $this->CI->db->get($table_sesi)->num_rows();
            if ($total_peserta > 0) {
                return array(
                    'success' => false,
                    'message' => 'QR Code tidak sesuai dengan jadwal Anda. Pastikan Anda scan barcode dari daftar hadir yang sesuai dengan kelas, mata kuliah, dan sesi Anda.',
                );
            }
        }

        $this->CI->db->where($id_jadwal_field, $jadwal->id);
        $this->CI->db->where('id_mahasiswa', $id_mahasiswa);
        $existing_absen = $this->CI->db->get($table_kehadiran)->row();

        $waktu_absen = date('Y-m-d H:i:s');

        if ($existing_absen) {
            if ($existing_absen->status_kehadiran == 'hadir') {
                $ts = !empty($existing_absen->created_at)
                    ? $existing_absen->created_at
                    : (isset($existing_absen->waktu_absen) ? $existing_absen->waktu_absen : null);
                return array(
                    'success' => false,
                    'message' => 'Anda sudah melakukan absensi pada ' . date('d/m/Y H:i', strtotime($ts)) . '. Status: Hadir',
                );
            }
            $data_update = array(
                'status_kehadiran' => 'hadir',
                'created_at' => $waktu_absen,
            );
            $this->CI->db->where($id_jadwal_field, $jadwal->id);
            $this->CI->db->where('id_mahasiswa', $id_mahasiswa);
            $result = $this->CI->db->update($table_kehadiran, $data_update);
        } else {
            $data_absen = array(
                $id_jadwal_field => $jadwal->id,
                'id_mahasiswa' => $id_mahasiswa,
                'status_kehadiran' => 'hadir',
                'created_at' => $waktu_absen,
            );
            $result = $this->CI->db->insert($table_kehadiran, $data_absen);
        }

        if ($result) {
            $nama_mk = isset($jadwal->nama_mk) ? $jadwal->nama_mk : '';
            return array(
                'success' => true,
                'message' => 'Absensi berhasil. Status kehadiran di daftar hadir diperbarui menjadi Hadir.',
                'jenis_ujian' => $jenis_ujian,
                'mata_kuliah' => $nama_mk,
                'waktu_absen' => date('d/m/Y H:i', strtotime($waktu_absen)),
                'tipe' => 'ujian',
                'status_kehadiran' => 'hadir',
            );
        }
        return array('success' => false, 'message' => 'Gagal menyimpan absensi.');
    }

    /**
     * @param string $token
     * @param string $jenis_try UTS|UAS
     * @return array|null
     */
    protected function resolve_jadwal_ujian($token, $jenis_try) {
        if ($jenis_try === 'UTS') {
            $jadwal_uts = $this->CI->Jadwal_uts_model->get_by_token($token);
            if ($jadwal_uts) {
                return array(
                    'jadwal' => $jadwal_uts,
                    'jenis_ujian' => 'UTS',
                    'table_kehadiran' => 'jadwal_uts_kehadiran',
                    'id_jadwal_field' => 'id_jadwal_uts',
                );
            }
            return null;
        }

        $jadwal_uas = $this->CI->Jadwal_uas_model->get_by_token($token);
        if ($jadwal_uas) {
            return array(
                'jadwal' => $jadwal_uas,
                'jenis_ujian' => 'UAS',
                'table_kehadiran' => 'jadwal_uas_kehadiran',
                'id_jadwal_field' => 'id_jadwal_uas',
            );
        }
        return null;
    }
}
