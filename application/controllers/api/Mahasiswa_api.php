<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Mahasiswa_api extends CI_Controller {

    public function __construct() {
        parent::__construct();
        // WIB: absensi PKL, tanggal_server, logbook, dll. memakai Asia/Jakarta (API tidak lewat MY_Controller).
        date_default_timezone_set('Asia/Jakarta');
        $this->load->helper('api');
        $this->load->helper('jwt');
        api_handle_options();
        api_send_cors_headers();
    }

    public function me() {
        if ($this->input->method(TRUE) !== 'GET') {
            api_json_response(array('success' => false, 'message' => 'Method tidak diizinkan.'), 405);
        }
        $payload = api_require_mahasiswa_jwt();
        $this->load->model('Mahasiswa_model');
        $this->db->select('m.*, k.nama_kurikulum, k.tahun_berlaku');
        $this->db->from('mahasiswa m');
        $this->db->join('kurikulum k', 'k.id = m.id_kurikulum', 'left');
        $this->db->where('m.id', (int) $payload['ref_id']);
        $m = $this->db->get()->row();
        if (!$m) {
            api_json_response(array('success' => false, 'message' => 'Data mahasiswa tidak ditemukan.'), 404);
        }
        $arr = json_decode(json_encode($m), true);
        if (!is_array($arr)) {
            $arr = array();
        }
        unset($arr['password']);
        if (!isset($arr['nama']) && isset($arr['nama_mahasiswa'])) {
            $arr['nama'] = $arr['nama_mahasiswa'];
        }
        api_json_response(array(
            'success' => true,
            'mahasiswa' => $arr,
        ));
    }

    /**
     * Scan QR absensi kegiatan / UTS / UAS (JWT).
     * Logika sama Absensi_api; URL di bawah mahasiswa agar konsisten dengan endpoint API lain (hindari 404 routing).
     */
    public function scan_absensi() {
        if ($this->input->method(TRUE) !== 'POST') {
            api_json_response(array('success' => false, 'message' => 'Method tidak diizinkan.'), 405);
        }
        $payload_jwt = api_require_mahasiswa_jwt();
        $id_mahasiswa = (int) $payload_jwt['ref_id'];

        $body = api_read_json_input();
        $mode = isset($body['mode']) ? strtolower(trim((string) $body['mode'])) : '';
        $token = isset($body['token']) ? trim((string) $body['token']) : '';
        $latitude = isset($body['latitude']) ? $body['latitude'] : null;
        $longitude = isset($body['longitude']) ? $body['longitude'] : null;

        if ($token === '') {
            api_json_response(array('success' => false, 'message' => 'Token wajib diisi.'), 400);
        }

        $allowed = array('kegiatan', 'uts', 'uas', 'ujian');
        if (!in_array($mode, $allowed, true)) {
            api_json_response(array(
                'success' => false,
                'message' => 'Mode tidak valid. Gunakan: kegiatan, uts, uas, atau ujian.',
            ), 400);
        }

        $this->load->library('Mobile_absensi_service');
        try {
            if ($mode === 'kegiatan') {
                $out = $this->mobile_absensi_service->scan_kegiatan($id_mahasiswa, $token, $latitude, $longitude);
            } else {
                $out = $this->mobile_absensi_service->scan_ujian($id_mahasiswa, $token, $mode);
            }
        } catch (Throwable $e) {
            log_message('error', 'Mahasiswa_api::scan_absensi: ' . $e->getMessage());
            api_json_response(array(
                'success' => false,
                'message' => 'Terjadi kesalahan server saat memproses absensi. Silakan coba lagi.',
            ), 500);
        }

        $code = !empty($out['success']) ? 200 : 400;
        api_json_response($out, $code);
    }

    public function menu() {
        if ($this->input->method(TRUE) !== 'GET') {
            api_json_response(array('success' => false, 'message' => 'Method tidak diizinkan.'), 405);
        }
        $payload = api_require_mahasiswa_jwt();
        $this->load->helper('mobile_menu');
        $items = get_mahasiswa_menu_mobile((int) $payload['ref_id']);
        api_json_response(array(
            'success' => true,
            'menu' => $items,
        ));
    }

    public function dashboard() {
        if ($this->input->method(TRUE) !== 'GET') {
            api_json_response(array('success' => false, 'message' => 'Method tidak diizinkan.'), 405);
        }
        $payload = api_require_mahasiswa_jwt();
        $this->load->library('mahasiswa_portal_service');
        api_json_response($this->mahasiswa_portal_service->dashboard((int) $payload['ref_id']));
    }

    /**
     * Beranda: struktur kurikulum + progress PKL/Skripsi (paritas Halaman_utama mahasiswa).
     */
    public function beranda() {
        if ($this->input->method(TRUE) !== 'GET') {
            api_json_response(array('success' => false, 'message' => 'Method tidak diizinkan.'), 405);
        }
        $payload = api_require_mahasiswa_jwt();
        $this->load->library('mahasiswa_portal_service');
        api_json_response($this->mahasiswa_portal_service->beranda((int) $payload['ref_id']));
    }

    public function jadwal_kuliah() {
        if ($this->input->method(TRUE) !== 'GET') {
            api_json_response(array('success' => false, 'message' => 'Method tidak diizinkan.'), 405);
        }
        $payload = api_require_mahasiswa_jwt();
        $this->load->library('mahasiswa_portal_service');
        api_json_response($this->mahasiswa_portal_service->jadwal_kuliah((int) $payload['ref_id']));
    }

    public function jadwal_uts() {
        if ($this->input->method(TRUE) !== 'GET') {
            api_json_response(array('success' => false, 'message' => 'Method tidak diizinkan.'), 405);
        }
        $payload = api_require_mahasiswa_jwt();
        $this->load->library('mahasiswa_portal_service');
        api_json_response($this->mahasiswa_portal_service->jadwal_uts((int) $payload['ref_id']));
    }

    public function jadwal_uas() {
        if ($this->input->method(TRUE) !== 'GET') {
            api_json_response(array('success' => false, 'message' => 'Method tidak diizinkan.'), 405);
        }
        $payload = api_require_mahasiswa_jwt();
        $this->load->library('mahasiswa_portal_service');
        api_json_response($this->mahasiswa_portal_service->jadwal_uas((int) $payload['ref_id']));
    }

    public function kegiatan() {
        if ($this->input->method(TRUE) !== 'GET') {
            api_json_response(array('success' => false, 'message' => 'Method tidak diizinkan.'), 405);
        }
        $payload = api_require_mahasiswa_jwt();
        $this->load->library('mahasiswa_portal_service');
        api_json_response($this->mahasiswa_portal_service->kegiatan_saya((int) $payload['ref_id']));
    }

    public function ujian_jadwal() {
        if ($this->input->method(TRUE) !== 'GET') {
            api_json_response(array('success' => false, 'message' => 'Method tidak diizinkan.'), 405);
        }
        $payload = api_require_mahasiswa_jwt();
        $this->load->library('mahasiswa_portal_service');
        api_json_response($this->mahasiswa_portal_service->ujian_jadwal((int) $payload['ref_id']));
    }

    public function berkas() {
        if ($this->input->method(TRUE) !== 'GET') {
            api_json_response(array('success' => false, 'message' => 'Method tidak diizinkan.'), 405);
        }
        api_require_mahasiswa_jwt();
        $this->load->library('mahasiswa_portal_service');
        api_json_response($this->mahasiswa_portal_service->manajemen_berkas());
    }

    public function permintaan_surat() {
        if ($this->input->method(TRUE) !== 'GET') {
            api_json_response(array('success' => false, 'message' => 'Method tidak diizinkan.'), 405);
        }
        $payload = api_require_mahasiswa_jwt();
        $this->load->library('mahasiswa_portal_service');
        api_json_response($this->mahasiswa_portal_service->permintaan_surat((int) $payload['ref_id']));
    }

    public function tagihan() {
        if ($this->input->method(TRUE) !== 'GET') {
            api_json_response(array('success' => false, 'message' => 'Method tidak diizinkan.'), 405);
        }
        $payload = api_require_mahasiswa_jwt();
        $this->load->library('mahasiswa_portal_service');
        api_json_response($this->mahasiswa_portal_service->tagihan_berkas((int) $payload['ref_id']));
    }

    public function jadwal_sidang_pkl() {
        if ($this->input->method(TRUE) !== 'GET') {
            api_json_response(array('success' => false, 'message' => 'Method tidak diizinkan.'), 405);
        }
        $payload = api_require_mahasiswa_jwt();
        $this->load->library('mahasiswa_portal_service');
        api_json_response($this->mahasiswa_portal_service->jadwal_sidang_pkl((int) $payload['ref_id']));
    }

    public function pkl_portal() {
        if ($this->input->method(TRUE) !== 'GET') {
            api_json_response(array('success' => false, 'message' => 'Method tidak diizinkan.'), 405);
        }
        $payload = api_require_mahasiswa_jwt();
        $this->load->library('mahasiswa_portal_service');
        api_json_response($this->mahasiswa_portal_service->pkl_portal((int) $payload['ref_id']));
    }

    public function skripsi_portal() {
        if ($this->input->method(TRUE) !== 'GET') {
            api_json_response(array('success' => false, 'message' => 'Method tidak diizinkan.'), 405);
        }
        $payload = api_require_mahasiswa_jwt();
        $this->load->library('mahasiswa_portal_service');
        api_json_response($this->mahasiswa_portal_service->skripsi_portal((int) $payload['ref_id']));
    }

    public function skripsi_ringkas() {
        if ($this->input->method(TRUE) !== 'GET') {
            api_json_response(array('success' => false, 'message' => 'Method tidak diizinkan.'), 405);
        }
        $payload = api_require_mahasiswa_jwt();
        $this->load->library('mahasiswa_portal_service');
        api_json_response($this->mahasiswa_portal_service->skripsi_ringkas((int) $payload['ref_id']));
    }

    public function pkl_bimbingan_nilai() {
        if ($this->input->method(TRUE) !== 'GET') {
            api_json_response(array('success' => false, 'message' => 'Method tidak diizinkan.'), 405);
        }
        $payload = api_require_mahasiswa_jwt();
        $this->load->library('mahasiswa_portal_service');
        api_json_response($this->mahasiswa_portal_service->pkl_bimbingan_nilai((int) $payload['ref_id']));
    }

    /**
     * Histori percakapan revisi bimbingan PKL (per topik bimbingan).
     */
    public function pkl_bimbingan_log($id_bimbingan = 0) {
        if ($this->input->method(TRUE) !== 'GET') {
            api_json_response(array('success' => false, 'message' => 'Method tidak diizinkan.'), 405);
        }
        $payload = api_require_mahasiswa_jwt();
        $id = (int) $id_bimbingan;
        if ($id <= 0) {
            api_json_response(array('success' => false, 'message' => 'ID bimbingan tidak valid.'), 400);
        }
        $this->load->library('mahasiswa_portal_service');
        $resp = $this->mahasiswa_portal_service->pkl_bimbingan_log((int) $payload['ref_id'], $id);
        api_json_response($resp, !empty($resp['success']) ? 200 : 404);
    }

    /**
     * Histori percakapan revisi bimbingan Skripsi (per topik bimbingan).
     */
    public function skripsi_bimbingan_log($id_bimbingan = 0) {
        if ($this->input->method(TRUE) !== 'GET') {
            api_json_response(array('success' => false, 'message' => 'Method tidak diizinkan.'), 405);
        }
        $payload = api_require_mahasiswa_jwt();
        $id = (int) $id_bimbingan;
        if ($id <= 0) {
            api_json_response(array('success' => false, 'message' => 'ID bimbingan tidak valid.'), 400);
        }
        $this->load->library('mahasiswa_portal_service');
        $resp = $this->mahasiswa_portal_service->skripsi_bimbingan_log((int) $payload['ref_id'], $id);
        api_json_response($resp, !empty($resp['success']) ? 200 : 404);
    }

    /**
     * Ubah password (mahasiswa)
     */
    public function change_password() {
        if ($this->input->method(TRUE) !== 'POST') {
            api_json_response(array('success' => false, 'message' => 'Method tidak diizinkan.'), 405);
        }
        $payload = api_require_mahasiswa_jwt();
        $body = api_read_json_input();
        $old = isset($body['old_password']) ? (string) $body['old_password'] : '';
        $new = isset($body['new_password']) ? (string) $body['new_password'] : '';
        if ($old === '' || $new === '') {
            api_json_response(array('success' => false, 'message' => 'Password lama dan baru wajib diisi.'), 400);
        }
        if (strlen($new) < 6) {
            api_json_response(array('success' => false, 'message' => 'Password baru minimal 6 karakter.'), 400);
        }
        $this->load->model('User_model');
        $user = $this->User_model->get_user_by_id((int) $payload['sub']);
        if (!$user) {
            api_json_response(array('success' => false, 'message' => 'User tidak ditemukan.'), 404);
        }
        $ok = $this->User_model->check_login($user->username, $old);
        if (!$ok) {
            api_json_response(array('success' => false, 'message' => 'Password lama salah.'), 401);
        }
        $this->User_model->update((int) $user->id, array('password' => $new));
        api_json_response(array('success' => true, 'message' => 'Password berhasil diubah.'));
    }

    /**
     * Logbook PKL — GET: daftar + statistik; POST: simpan/perbarui (aturan sama Pkl::logbook).
     */
    public function pkl_logbook() {
        $payload = api_require_mahasiswa_jwt();
        $id_mahasiswa = (int) $payload['ref_id'];
        $method = $this->input->method(TRUE);

        $this->load->model('Tahun_akademik_model');
        $this->load->model('Mahasiswa_pkl_model');
        $this->load->model('Pkl_pengajuan_model');
        $this->load->model('Pkl_logbook_model');

        $tahun_aktif = $this->Tahun_akademik_model->get_aktif();
        if (!$tahun_aktif) {
            api_json_response(array('success' => false, 'message' => 'Tidak ada tahun akademik aktif.'), 400);
        }
        if (!$this->Mahasiswa_pkl_model->is_registered($id_mahasiswa, $tahun_aktif->id)) {
            api_json_response(array('success' => false, 'message' => 'Anda belum terdaftar sebagai peserta PKL.'), 403);
        }

        if ($method === 'GET') {
            $list = $this->Pkl_logbook_model->get_by_mahasiswa($id_mahasiswa, $tahun_aktif->id);
            $statistik = $this->Pkl_logbook_model->get_statistik($id_mahasiswa, $tahun_aktif->id);
            $items = array();
            foreach ($list as $row) {
                $items[] = json_decode(json_encode($row), true);
            }
            $stat_arr = $statistik ? json_decode(json_encode($statistik), true) : null;
            api_json_response(array(
                'success' => true,
                'items' => $items,
                'statistik' => $stat_arr,
            ));
        }

        if ($method === 'POST') {
            $body = api_read_json_input();
            $tanggal = isset($body['tanggal']) ? trim((string) $body['tanggal']) : '';
            $jam_mulai = isset($body['jam_mulai']) ? trim((string) $body['jam_mulai']) : '';
            $jam_selesai = isset($body['jam_selesai']) ? trim((string) $body['jam_selesai']) : '';
            $aktivitas = isset($body['aktivitas']) ? trim((string) $body['aktivitas']) : '';
            $lat = isset($body['latitude']) ? $body['latitude'] : null;
            $lng = isset($body['longitude']) ? $body['longitude'] : null;

            if ($tanggal === '' || $jam_mulai === '' || $jam_selesai === '') {
                api_json_response(array('success' => false, 'message' => 'Tanggal, jam mulai, dan jam selesai wajib diisi.'), 400);
            }
            if (strlen($aktivitas) < 10) {
                api_json_response(array('success' => false, 'message' => 'Aktivitas minimal 10 karakter.'), 400);
            }
            if ($lat !== null && $lat !== '' && !is_numeric($lat)) {
                api_json_response(array('success' => false, 'message' => 'Latitude tidak valid.'), 400);
            }
            if ($lng !== null && $lng !== '' && !is_numeric($lng)) {
                api_json_response(array('success' => false, 'message' => 'Longitude tidak valid.'), 400);
            }
            if ($tanggal > date('Y-m-d')) {
                api_json_response(array('success' => false, 'message' => 'Tanggal logbook tidak boleh lebih dari hari ini.'), 400);
            }

            $pengajuan = $this->Pkl_pengajuan_model->get_by_mahasiswa($id_mahasiswa, $tahun_aktif->id);
            $logbook_data = array(
                'id_mahasiswa' => $id_mahasiswa,
                'id_pkl_pengajuan' => $pengajuan ? $pengajuan->id : null,
                'tanggal' => $tanggal,
                'jam_mulai' => $jam_mulai,
                'jam_selesai' => $jam_selesai,
                'aktivitas' => $aktivitas,
                'latitude_logbook' => ($lat !== null && $lat !== '') ? (float) $lat : null,
                'longitude_logbook' => ($lng !== null && $lng !== '') ? (float) $lng : null,
            );

            $existing = $this->Pkl_logbook_model->get_by_tanggal($id_mahasiswa, $tanggal);
            if ($existing) {
                $ok = $this->Pkl_logbook_model->update($existing->id, $logbook_data);
                api_json_response(array(
                    'success' => (bool) $ok,
                    'message' => $ok ? 'Logbook berhasil diperbarui.' : 'Gagal memperbarui logbook.',
                ), $ok ? 200 : 500);
            }
            $id_new = $this->Pkl_logbook_model->create($logbook_data);
            api_json_response(array(
                'success' => (bool) $id_new,
                'message' => $id_new ? 'Logbook berhasil disimpan.' : 'Gagal menyimpan logbook.',
                'id' => $id_new ? (int) $id_new : null,
            ), $id_new ? 200 : 500);
        }

        api_json_response(array('success' => false, 'message' => 'Method tidak diizinkan.'), 405);
    }

    /**
     * Absensi PKL — GET: status hari ini + tempat + radius; POST: simpan (aturan sama Pkl_absensi::store).
     */
    public function pkl_absensi() {
        $payload = api_require_mahasiswa_jwt();
        $id_mahasiswa = (int) $payload['ref_id'];
        $method = $this->input->method(TRUE);

        $this->load->model('Tahun_akademik_model');
        $this->load->model('Mahasiswa_pkl_model');
        $this->load->model('Pkl_tempat_model');
        $this->load->model('Pkl_absensi_model');
        $this->load->model('Pkl_setting_radius_model');
        $this->load->helper('pkl_gps');

        $tahun_aktif = $this->Tahun_akademik_model->get_aktif();
        if (!$tahun_aktif) {
            api_json_response(array('success' => false, 'message' => 'Tidak ada tahun akademik aktif.'), 400);
        }
        if (!$this->Mahasiswa_pkl_model->is_registered($id_mahasiswa, $tahun_aktif->id)) {
            api_json_response(array('success' => false, 'message' => 'Anda belum terdaftar sebagai peserta PKL.'), 403);
        }

        if ($method === 'GET') {
            $tempat_pkl = $this->Pkl_tempat_model->get_by_mahasiswa($id_mahasiswa, $tahun_aktif->id);
            $radius_setting = $this->Pkl_setting_radius_model->get_radius($tahun_aktif->id);
            $absensi_hari_ini = $this->Pkl_absensi_model->get_hari_ini($id_mahasiswa);
            $sudah_berangkat = $this->Pkl_absensi_model->sudah_absen_berangkat($id_mahasiswa);
            $sudah_pulang = $this->Pkl_absensi_model->sudah_absen_pulang($id_mahasiswa);

            $tempat_arr = $tempat_pkl ? json_decode(json_encode($tempat_pkl), true) : null;
            $hari_ini = array();
            $waktu_berangkat = null;
            $waktu_pulang = null;
            foreach ($absensi_hari_ini as $a) {
                $row = json_decode(json_encode($a), true);
                $hari_ini[] = $row;
                if (isset($a->tipe_absensi) && $a->tipe_absensi === 'berangkat' && !empty($a->waktu_absen)) {
                    $waktu_berangkat = (string) $a->waktu_absen;
                }
                if (isset($a->tipe_absensi) && $a->tipe_absensi === 'pulang' && !empty($a->waktu_absen)) {
                    $waktu_pulang = (string) $a->waktu_absen;
                }
            }

            $riwayat_rows = $this->Pkl_absensi_model->get_riwayat_for_api($id_mahasiswa, 150);
            $merged_by_tanggal = array();
            foreach ($riwayat_rows as $r) {
                $tg = isset($r->tanggal) ? (string) $r->tanggal : '';
                if ($tg === '') {
                    continue;
                }
                if (!isset($merged_by_tanggal[$tg])) {
                    $merged_by_tanggal[$tg] = array(
                        'tanggal' => $tg,
                        'waktu_berangkat' => null,
                        'waktu_pulang' => null,
                        'nama_instansi' => isset($r->nama_instansi) ? $r->nama_instansi : null,
                    );
                }
                $tipe = isset($r->tipe_absensi) ? (string) $r->tipe_absensi : '';
                $waktu = isset($r->waktu_absen) ? (string) $r->waktu_absen : null;
                if ($tipe === 'berangkat' && $waktu) {
                    $merged_by_tanggal[$tg]['waktu_berangkat'] = $waktu;
                } elseif ($tipe === 'pulang' && $waktu) {
                    $merged_by_tanggal[$tg]['waktu_pulang'] = $waktu;
                }
            }
            krsort($merged_by_tanggal);
            $riwayat_per_tanggal = array_values($merged_by_tanggal);

            $ready = $tempat_pkl && $tempat_pkl->latitude && $tempat_pkl->longitude;
            api_json_response(array(
                'success' => true,
                'timezone_absensi' => 'Asia/Jakarta',
                'tanggal_server' => date('Y-m-d'),
                'tempat_pkl' => $tempat_arr,
                'radius_meter' => (int) $radius_setting,
                'absensi_hari_ini' => $hari_ini,
                'waktu_berangkat' => $waktu_berangkat,
                'waktu_pulang' => $waktu_pulang,
                'riwayat_per_tanggal' => $riwayat_per_tanggal,
                'sudah_berangkat' => (bool) $sudah_berangkat,
                'sudah_pulang' => (bool) $sudah_pulang,
                'bisa_absen' => (bool) $ready,
                'pesan_tempat' => $ready ? null : 'Lengkapi data tempat PKL beserta koordinat GPS di web jika belum.',
            ));
        }

        if ($method === 'POST') {
            $body = api_read_json_input();
            $tipe_absensi = isset($body['tipe_absensi']) ? (string) $body['tipe_absensi'] : '';
            $tanggal = isset($body['tanggal']) ? trim((string) $body['tanggal']) : date('Y-m-d');
            $latitude = isset($body['latitude']) ? (float) $body['latitude'] : null;
            $longitude = isset($body['longitude']) ? (float) $body['longitude'] : null;
            $keterangan = isset($body['keterangan']) ? trim((string) $body['keterangan']) : '';
            $device_fingerprint = isset($body['device_fingerprint']) ? (string) $body['device_fingerprint'] : '';
            $device_info = isset($body['device_info']) ? (string) $body['device_info'] : '';

            if (!in_array($tipe_absensi, array('berangkat', 'pulang'), true)) {
                api_json_response(array('success' => false, 'message' => 'Tipe absensi harus berangkat atau pulang.'), 400);
            }
            if ($latitude === null || $longitude === null) {
                api_json_response(array('success' => false, 'message' => 'Koordinat GPS wajib diisi.'), 400);
            }
            if ($tanggal > date('Y-m-d')) {
                api_json_response(array('success' => false, 'message' => 'Tanggal absensi tidak boleh lebih dari hari ini.'), 400);
            }
            if ($tipe_absensi === 'pulang') {
                if (!$this->Pkl_absensi_model->sudah_absen_berangkat($id_mahasiswa, $tanggal)) {
                    api_json_response(array('success' => false, 'message' => 'Anda belum melakukan absensi berangkat untuk tanggal ini.'), 400);
                }
            }
            if ($tipe_absensi === 'berangkat' && $this->Pkl_absensi_model->sudah_absen_berangkat($id_mahasiswa, $tanggal)) {
                api_json_response(array('success' => false, 'message' => 'Anda sudah melakukan absensi berangkat untuk tanggal ini.'), 400);
            }
            if ($tipe_absensi === 'pulang' && $this->Pkl_absensi_model->sudah_absen_pulang($id_mahasiswa, $tanggal)) {
                api_json_response(array('success' => false, 'message' => 'Anda sudah melakukan absensi pulang untuk tanggal ini.'), 400);
            }

            $tempat_pkl = $this->Pkl_tempat_model->get_by_mahasiswa($id_mahasiswa, $tahun_aktif->id);
            if (!$tempat_pkl || !$tempat_pkl->latitude || !$tempat_pkl->longitude) {
                api_json_response(array('success' => false, 'message' => 'Koordinat tempat PKL belum diisi.'), 400);
            }

            $radius_check = check_radius_pkl($latitude, $longitude, $tempat_pkl->id, $tahun_aktif->id);

            $first_berangkat = $this->Pkl_absensi_model->get_first_absensi($id_mahasiswa);
            $is_device_changed = 0;
            if ($first_berangkat) {
                if ($device_fingerprint && $first_berangkat->device_fingerprint) {
                    if ($device_fingerprint !== $first_berangkat->device_fingerprint) {
                        $is_device_changed = 1;
                    }
                }
            }

            $data = array(
                'id_mahasiswa' => $id_mahasiswa,
                'id_pkl_tempat' => $tempat_pkl->id,
                'id_tahun_akademik' => $tahun_aktif->id,
                'tanggal' => $tanggal,
                'tipe_absensi' => $tipe_absensi,
                'waktu_absen' => date('Y-m-d H:i:s'),
                'latitude_absen' => $latitude,
                'longitude_absen' => $longitude,
                'jarak_meter' => $radius_check['jarak_meter'],
                'status_radius' => $radius_check['status_radius'],
                'ip_address' => $this->input->ip_address(),
                'user_agent' => $this->input->user_agent(),
                'keterangan' => $keterangan !== '' ? $keterangan : null,
                'device_fingerprint' => $device_fingerprint !== '' ? $device_fingerprint : null,
                'device_info' => $device_info !== '' ? $device_info : null,
                'is_device_changed' => $is_device_changed,
            );

            $id_absensi = $this->Pkl_absensi_model->create($data);
            if ($id_absensi) {
                api_json_response(array(
                    'success' => true,
                    'message' => 'Absensi ' . $tipe_absensi . ' berhasil disimpan.',
                    'data' => array(
                        'id' => (int) $id_absensi,
                        'status_radius' => $radius_check['status_radius'],
                        'jarak_meter' => $radius_check['jarak_meter'],
                        'radius_setting' => $radius_check['radius_setting'],
                    ),
                ));
            }
            api_json_response(array('success' => false, 'message' => 'Gagal menyimpan absensi.'), 500);
        }

        api_json_response(array('success' => false, 'message' => 'Method tidak diizinkan.'), 405);
    }

    /**
     * Notifikasi mahasiswa (paritas dropdown navbar web).
     */
    public function notifikasi() {
        if ($this->input->method(TRUE) !== 'GET') {
            api_json_response(array('success' => false, 'message' => 'Method tidak diizinkan.'), 405);
        }
        $payload = api_require_mahasiswa_jwt();
        $id_user = (int) $payload['sub'];
        $limit = (int) $this->input->get('limit');
        if ($limit <= 0 || $limit > 100) {
            $limit = 50;
        }
        $this->load->model('Notifikasi_model');
        $rows = $this->Notifikasi_model->get_by_user($id_user, 'mahasiswa', $limit, 0);
        $items = array();
        foreach ($rows as $r) {
            $items[] = array(
                'id' => (int) $r->id,
                'judul' => $r->judul,
                'pesan' => $r->pesan,
                'link' => $r->link,
                'is_read' => (int) $r->is_read,
                'created_at' => $r->created_at,
                'jenis' => isset($r->jenis) ? $r->jenis : '',
            );
        }
        $unread = $this->Notifikasi_model->count_unread($id_user, 'mahasiswa');
        api_json_response(array(
            'success' => true,
            'items' => $items,
            'unread_count' => (int) $unread,
        ));
    }

    public function notifikasi_read() {
        if ($this->input->method(TRUE) !== 'POST') {
            api_json_response(array('success' => false, 'message' => 'Method tidak diizinkan.'), 405);
        }
        $payload = api_require_mahasiswa_jwt();
        $id_user = (int) $payload['sub'];
        $body = api_read_json_input();
        $id = isset($body['id']) ? (int) $body['id'] : 0;
        if ($id <= 0) {
            api_json_response(array('success' => false, 'message' => 'ID tidak valid.'), 400);
        }
        $this->load->model('Notifikasi_model');
        $row = $this->Notifikasi_model->get_by_id($id);
        if (!$row || (int) $row->id_user !== $id_user || $row->role !== 'mahasiswa') {
            api_json_response(array('success' => false, 'message' => 'Notifikasi tidak ditemukan.'), 404);
        }
        $this->Notifikasi_model->mark_as_read($id);
        api_json_response(array('success' => true, 'message' => 'Ditandai dibaca.'));
    }

    public function notifikasi_read_all() {
        if ($this->input->method(TRUE) !== 'POST') {
            api_json_response(array('success' => false, 'message' => 'Method tidak diizinkan.'), 405);
        }
        $payload = api_require_mahasiswa_jwt();
        $id_user = (int) $payload['sub'];
        $this->load->model('Notifikasi_model');
        $this->Notifikasi_model->mark_all_as_read($id_user, 'mahasiswa');
        api_json_response(array('success' => true, 'message' => 'Semua notifikasi ditandai dibaca.'));
    }
}
