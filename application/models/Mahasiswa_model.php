<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Mahasiswa_model extends CI_Model {

    public function __construct() {
        parent::__construct();
        $this->load->database();
    }

    /**
     * @param bool $konteks true = kelas/semester sesuai tahun akademik konteks (tampilan);
     *                     false = data tersimpan apa adanya (WAJIB untuk edit/simpan data mahasiswa)
     */
    public function get_by_id($id, $konteks = true) {
        $this->db->where('id', $id);
        $query = $this->db->get('mahasiswa');
        $row = $query->row();
        return $konteks ? $this->terapkan_konteks_ta($row) : $row;
    }

    public function get_all($limit = null, $offset = null) {
        $this->db->select('m.*, k.nama_kurikulum, k.tahun_berlaku');
        $this->db->from('mahasiswa m');
        $this->db->join('kurikulum k', 'k.id = m.id_kurikulum', 'left');
        $this->db->order_by('m.angkatan', 'DESC');
        $this->db->order_by('m.nim', 'ASC');
        if ($limit !== null) {
            $this->db->limit($limit, $offset);
        }
        $query = $this->db->get();
        $rows = $query->result();
        $k = $this->konteks_hitung();
        if ($k) {
            foreach ($rows as $r) {
                $this->terapkan_konteks_ta($r, $k);
            }
        }
        return $rows;
    }

    public function count_all() {
        $this->db->from('mahasiswa');
        return $this->db->count_all_results();
    }

    /* ------------------------------------------------------------------ */
    /* Data mahasiswa menurut tahun akademik yang DILIHAT (mode lihat)      */
    /* ------------------------------------------------------------------ */

    protected $konteks_cache = false;
    protected $konteks_cache_key = null;

    /** Kosongkan cache konteks (dipakai setelah tahun akademik berpindah dalam satu proses, mis. uji). */
    public function reset_konteks() {
        $this->konteks_cache = false;
        $this->konteks_cache_key = null;
    }

    /**
     * Konteks hitung kelas/semester. Data di tabel mahasiswa adalah keadaan pada tahun akademik TERTINGGI yang pernah
     * diterapkan kenaikan kelasnya (HW). Bila tahun akademik konteks sesi ini (yang aktif atau yang sedang dilihat)
     * BERBEDA dari HW, kelas/semester dihitung dari angkatan + rombel pada tahun akademik konteks (data tersimpan
     * tidak diubah). Bila sama dengan HW, data tersimpan dipakai apa adanya.
     * @return array|null ['th' => int, 'sm' => 1|2, 'ta' => object] atau null (pakai data tersimpan)
     */
    protected function konteks_hitung() {
        $this->load->model('Tahun_akademik_model');
        $key = (string) $this->Tahun_akademik_model->get_lihat_id();
        $resmi = $this->Tahun_akademik_model->get_aktif_resmi();
        $key .= '|' . ($resmi ? $resmi->id : 0);
        if ($this->konteks_cache !== false && $this->konteks_cache_key === $key) {
            return $this->konteks_cache;
        }

        $hasil = null;
        $ta = $this->Tahun_akademik_model->get_aktif(); // konteks: yang dilihat, selain itu yang resmi
        if ($ta && $this->db->field_exists('rombel', 'mahasiswa')) {
            $this->load->library('Kenaikan_kelas');
            $hw = $this->kenaikan_kelas->tahun_tertinggi_diterapkan($resmi);
            if ($hw && Kenaikan_kelas::kunci_ta($ta) !== Kenaikan_kelas::kunci_ta($hw)) {
                $p = Kenaikan_kelas::parse_ta($ta);
                $hasil = array('th' => $p['th'], 'sm' => $p['sm'], 'ta' => $ta);
            }
        }
        $this->konteks_cache = $hasil;
        $this->konteks_cache_key = $key;
        return $hasil;
    }

    /** Tahun mulai tahun akademik konteks (dasar statistik per angkatan). */
    public function get_tahun_basis() {
        $this->load->model('Tahun_akademik_model');
        $ta = $this->Tahun_akademik_model->get_aktif();
        if (!$ta) {
            return (int) date('Y');
        }
        $this->load->library('Kenaikan_kelas');
        $p = Kenaikan_kelas::parse_ta($ta);
        return $p['th'];
    }
    /**
     * Kelas dan semester mahasiswa PADA tahun akademik yang dilihat (dihitung dari angkatan dan rombel).
     * Tidak berefek di luar mode lihat. Mahasiswa nonaktif/cuti/lulus manual tidak dihitung ulang.
     */
    public function terapkan_konteks_ta($m, $k = false) {
        if (!$m) {
            return $m;
        }
        if ($k === false) {
            $k = $this->konteks_hitung();
        }
        if (!$k || !($m->status === 'aktif' || (isset($m->lulus_otomatis) && (int) $m->lulus_otomatis === 1))) {
            return $m;
        }
        $h = Kenaikan_kelas::hitung($m, $k['th'], $k['sm']);
        $m->kelas = $h['kelas'];
        $m->semester_aktif = $h['semester_aktif'];
        if (property_exists($m, 'semester_riil')) {
            $m->semester_riil = $h['semester_riil'];
        }
        return $m;
    }

    /**
     * Mahasiswa yang "ada" pada tahun akademik konteks, lengkap dengan kelas/semester pada saat itu.
     *  - Tahun akademik resmi: mahasiswa berstatus aktif, memakai data tersimpan.
     *  - Mode lihat: mahasiswa aktif (dan lulus otomatis) yang sudah masuk (angkatan <= tahun itu) dan belum
     *    melewati 14 semester pada tahun akademik itu; kelas dihitung dari angkatan dan rombel.
     * @return array objek {id, nim, nama_mahasiswa, angkatan, kelas, semester_aktif, semester_riil, alumni}
     */
    public function get_snapshot() {
        $k = $this->konteks_hitung();
        $this->db->from('mahasiswa');
        if ($k) {
            $this->db->group_start()->where('status', 'aktif');
            if ($this->db->field_exists('lulus_otomatis', 'mahasiswa')) {
                $this->db->or_group_start()->where('status', 'lulus')->where('lulus_otomatis', 1)->group_end();
            }
            $this->db->group_end();
        } else {
            $this->db->where('status', 'aktif');
        }
        $rows = $this->db->get()->result();

        $out = array();
        foreach ($rows as $m) {
            if ($k) {
                $h = Kenaikan_kelas::hitung($m, $k['th'], $k['sm']);
                if ($h['mentah'] < 1 || $h['melewati_batas']) {
                    continue; // belum masuk / sudah lewat batas studi pada tahun akademik itu
                }
                $kelas = $h['kelas'];
                $sa = $h['semester_aktif'];
                $riil = $h['semester_riil'];
            } else {
                $kelas = $m->kelas;
                $sa = (int) $m->semester_aktif;
                $riil = (isset($m->semester_riil) && $m->semester_riil !== null) ? (int) $m->semester_riil : $sa;
            }
            $out[] = (object) array(
                'id' => (int) $m->id, 'nim' => $m->nim, 'nama_mahasiswa' => $m->nama_mahasiswa, 'angkatan' => (int) $m->angkatan,
                'kelas' => $kelas, 'semester_aktif' => $sa, 'semester_riil' => $riil, 'alumni' => ($riil > 8 ? 1 : 0)
            );
        }
        return $out;
    }

    /**
     * Mahasiswa aktif = semester riil 1-8; semester riil > 8 dihitung terpisah sebagai alumni (belum lulus).
     * Mengikuti tahun akademik yang dilihat.
     * @return array ['aktif' => int, 'alumni' => int]
     */
    public function get_ringkasan_aktif() {
        $aktif = 0;
        $alumni = 0;
        foreach ($this->get_snapshot() as $s) {
            if ($s->alumni) { $alumni++; } else { $aktif++; }
        }
        return array('aktif' => $aktif, 'alumni' => $alumni);
    }

    /**
     * Jumlah mahasiswa per kelas DAN angkatan (pembeda), mengikuti tahun akademik yang dilihat.
     * Mahasiswa semester riil > 8 diberi tanda "(Alumni)".
     * @return array objek {kelas (label), kelas_kode, angkatan, jumlah, alumni}
     */
    public function get_statistik_kelas_aktif($limit = null) {
        $grup = array();
        foreach ($this->get_snapshot() as $s) {
            if ($s->kelas === null || $s->kelas === '') {
                continue;
            }
            $key = $s->kelas . '|' . $s->angkatan . '|' . $s->alumni;
            if (!isset($grup[$key])) {
                $grup[$key] = (object) array('kelas_kode' => $s->kelas, 'angkatan' => $s->angkatan, 'alumni' => $s->alumni, 'jumlah' => 0,
                    'kelas' => $s->kelas . ' - Angk. ' . $s->angkatan . ($s->alumni ? ' (Alumni)' : ''));
            }
            $grup[$key]->jumlah++;
        }
        $rows = array_values($grup);
        usort($rows, function ($a, $b) {
            return $b->jumlah <=> $a->jumlah ?: strcmp($a->kelas, $b->kelas);
        });
        return $limit ? array_slice($rows, 0, (int) $limit) : $rows;
    }

    /** Jumlah mahasiswa aktif (bukan alumni) per semester 1-8 pada tahun akademik yang dilihat. */
    public function get_statistik_semester() {
        $out = array_fill(1, 8, 0);
        foreach ($this->get_snapshot() as $s) {
            if (!$s->alumni && $s->semester_aktif >= 1 && $s->semester_aktif <= 8) {
                $out[$s->semester_aktif]++;
            }
        }
        return $out;
    }

    /** Jumlah mahasiswa per angkatan (map angkatan => jumlah) pada tahun akademik yang dilihat. */
    public function get_jumlah_per_angkatan() {
        $out = array();
        foreach ($this->get_snapshot() as $s) {
            $out[$s->angkatan] = isset($out[$s->angkatan]) ? $out[$s->angkatan] + 1 : 1;
        }
        return $out;
    }

    public function get_by_nim($nim) {
        $this->db->where('nim', $nim);
        $query = $this->db->get('mahasiswa');
        return $query->row();
    }

    public function create($data) {
        $this->db->insert('mahasiswa', $data);
        return $this->db->insert_id();
    }

    public function update($id, $data) {
        $this->db->where('id', $id);
        $this->db->update('mahasiswa', $data);
        return $this->db->affected_rows();
    }

    public function delete($id) {
        $this->db->where('id', $id);
        $this->db->delete('mahasiswa');
        return $this->db->affected_rows();
    }

    public function update_semester($id, $semester_baru) {
        $this->db->where('id', $id);
        $this->db->update('mahasiswa', array('semester_aktif' => $semester_baru));
        return $this->db->affected_rows();
    }

    /**
     * Recalculate semester aktif untuk semua mahasiswa berdasarkan tahun akademik baru.
     * Kelas cohort berbeda tidak digabung: jika target kelas sudah dipakai cohort lain
     * (mis. 7A naik ke 8A padahal 8A sudah terisi angkatan sebelumnya), maka cohort
     * yang baru masuk mendapat huruf kelas berikutnya (8B, 8C, dst).
     */
    public function recalculate_semester_all($tahun_akademik) {
        $tahun_parts = explode('/', $tahun_akademik->tahun_akademik);
        $tahun_akademik_start = intval($tahun_parts[0]);
        $semester_value = ($tahun_akademik->semester == 'ganjil') ? 1 : 2;

        $this->db->where('status', 'aktif');
        $mahasiswa = $this->db->get('mahasiswa')->result();

        // Pass 1: hitung semester baru + huruf preferensi per mahasiswa
        $plans = array();
        foreach ($mahasiswa as $mhs) {
            $selisih_tahun = $tahun_akademik_start - (int) $mhs->angkatan;
            $semester_baru = ($selisih_tahun * 2) + $semester_value;
            if ($semester_baru > 8) {
                $semester_baru = 8;
            }
            if ($semester_baru < 1) {
                $semester_baru = 1;
            }

            $huruf = $this->extract_huruf_kelas($mhs->kelas);
            // Cohort: angkatan + huruf asal (supaya rombongan tetap terpisah)
            $cohort_key = (string) ((int) $mhs->angkatan) . '|' . $huruf;

            $plans[] = array(
                'id' => (int) $mhs->id,
                'old_kelas' => $mhs->kelas,
                'old_semester' => (int) $mhs->semester_aktif,
                'semester_baru' => $semester_baru,
                'huruf' => $huruf,
                'angkatan' => (int) $mhs->angkatan,
                'cohort_key' => $cohort_key,
                // Prioritas: yang sudah di semester target (biasanya 8) pertahankan huruf lama
                'priority' => ((int) $mhs->semester_aktif === $semester_baru) ? 0 : 1,
            );
        }

        // Pass 2: alokasikan kelas tanpa menggabungkan cohort berbeda
        $assigned_by_kelas = array(); // kelas => cohort_key
        $cohort_kelas_map = array();  // semester|cohort_key => kelas final

        // Urutkan: prioritas tinggi dulu, lalu angkatan lebih tua, lalu huruf
        usort($plans, function ($a, $b) {
            if ($a['priority'] !== $b['priority']) {
                return $a['priority'] - $b['priority'];
            }
            if ($a['angkatan'] !== $b['angkatan']) {
                return $a['angkatan'] - $b['angkatan'];
            }
            return strcmp($a['huruf'], $b['huruf']);
        });

        foreach ($plans as &$plan) {
            $map_key = $plan['semester_baru'] . '|' . $plan['cohort_key'];
            if (isset($cohort_kelas_map[$map_key])) {
                $plan['kelas_baru'] = $cohort_kelas_map[$map_key];
                continue;
            }

            $candidate = $plan['semester_baru'] . $plan['huruf'];
            // Jika kelas sudah dipakai cohort lain, cari huruf berikutnya
            if (isset($assigned_by_kelas[$candidate]) && $assigned_by_kelas[$candidate] !== $plan['cohort_key']) {
                $candidate = $this->find_available_kelas_code(
                    $plan['semester_baru'],
                    $plan['huruf'],
                    $assigned_by_kelas,
                    $plan['cohort_key']
                );
            }

            $assigned_by_kelas[$candidate] = $plan['cohort_key'];
            $cohort_kelas_map[$map_key] = $candidate;
            $plan['kelas_baru'] = $candidate;
        }
        unset($plan);

        // Pass 3: simpan perubahan
        $updated = 0;
        foreach ($plans as $plan) {
            $need_update = false;
            $data = array();

            if ((int) $plan['old_semester'] !== (int) $plan['semester_baru']) {
                $data['semester_aktif'] = $plan['semester_baru'];
                $need_update = true;
            }
            if ((string) $plan['old_kelas'] !== (string) $plan['kelas_baru']) {
                $data['kelas'] = $plan['kelas_baru'];
                $need_update = true;
            }

            if ($need_update) {
                $this->db->where('id', $plan['id']);
                $this->db->update('mahasiswa', $data);
                $updated++;
            }
        }

        return $updated;
    }

    /**
     * Ambil huruf kelas dari kode kelas (1A -> A, 8G -> G)
     */
    private function extract_huruf_kelas($kelas) {
        if (!is_string($kelas) || trim($kelas) === '') {
            return 'A';
        }
        if (preg_match('/([A-Za-z])\s*$/', $kelas, $m)) {
            return strtoupper($m[1]);
        }
        return 'A';
    }

    /**
     * Cari kode kelas yang belum dipakai cohort lain pada semester yang sama.
     * Contoh: 8A sudah dipakai -> coba 8B, 8C, ...
     */
    private function find_available_kelas_code($semester, $start_huruf, $assigned_by_kelas, $cohort_key) {
        $letters = range('A', 'Z');
        $start_idx = array_search(strtoupper($start_huruf), $letters, true);
        if ($start_idx === false) {
            $start_idx = 0;
        }

        // Mulai dari huruf berikutnya setelah preferensi awal
        for ($i = $start_idx + 1; $i < count($letters); $i++) {
            $code = $semester . $letters[$i];
            if (!isset($assigned_by_kelas[$code]) || $assigned_by_kelas[$code] === $cohort_key) {
                return $code;
            }
        }
        // Fallback: scan dari A lagi
        for ($i = 0; $i < count($letters); $i++) {
            $code = $semester . $letters[$i];
            if (!isset($assigned_by_kelas[$code]) || $assigned_by_kelas[$code] === $cohort_key) {
                return $code;
            }
        }

        // Sangat jarang: semua huruf terpakai
        return $semester . $start_huruf . 'X';
    }

    /**
     * Naikkan semester untuk semua mahasiswa aktif yang semester < 8
     * DEPRECATED: Gunakan recalculate_semester_all() untuk menghitung ulang berdasarkan tahun akademik
     */
    public function naik_semester_all() {
        // Naikkan semester untuk semua mahasiswa aktif yang semester < 8
        // Dan update kelas sesuai dengan semester baru
        $this->db->where('status', 'aktif');
        $this->db->where('semester_aktif <', 8);
        
        // Ambil semua mahasiswa yang akan dinaikkan
        $mahasiswa = $this->db->get('mahasiswa')->result();
        
        $updated = 0;
        foreach ($mahasiswa as $mhs) {
            $semester_baru = $mhs->semester_aktif + 1;
            
            // Update kelas: ambil huruf kelas dari kelas lama, gabungkan dengan semester baru
            // Contoh: 1A -> 2A, 1B -> 2B, 1G -> 2G
            $kelas_baru = null;
            if ($mhs->kelas) {
                // Ambil huruf terakhir dari kelas (A, B, C, G, dll)
                $huruf_kelas = substr($mhs->kelas, -1);
                $kelas_baru = $semester_baru . $huruf_kelas;
            } else {
                // Jika belum ada kelas, set default ke A
                $kelas_baru = $semester_baru . 'A';
            }
            
            // Update semester dan kelas
            $this->db->where('id', $mhs->id);
            $this->db->update('mahasiswa', array(
                'semester_aktif' => $semester_baru,
                'kelas' => $kelas_baru
            ));
            $updated++;
        }
        
        return $updated;
    }

    /**
     * Generate kelas otomatis berdasarkan semester dan urutan
     * Format: {semester}{huruf} seperti 1A, 1B, 1C, 1G (untuk karyawan)
     */
    public function generate_kelas($semester, $is_karyawan = false) {
        if ($is_karyawan) {
            return $semester . 'G'; // G untuk kelas karyawan
        }
        
        // Untuk kelas reguler, hitung berapa banyak mahasiswa per semester
        $this->db->where('semester_aktif', $semester);
        $this->db->where('status', 'aktif');
        $count = $this->db->count_all_results('mahasiswa');
        
        // Tentukan huruf kelas berdasarkan jumlah mahasiswa
        // Setiap 40 mahasiswa = 1 kelas (A, B, C, dll)
        $kelas_index = floor($count / 40);
        $huruf = chr(65 + $kelas_index); // 65 = 'A', 66 = 'B', dll
        
        return $semester . $huruf;
    }

    /**
     * Get mahasiswa by kelas
     */
    public function get_by_kelas($kelas, $status = 'aktif') {
        $k = $this->konteks_hitung();
        if ($k) {
            return $this->get_by_kelas_konteks($kelas, $status, $k);
        }
        $this->db->select('m.*, k.nama_kurikulum');
        $this->db->from('mahasiswa m');
        $this->db->join('kurikulum k', 'k.id = m.id_kurikulum', 'left');
        $this->db->where('m.kelas', $kelas);
        if ($status) {
            $this->db->where('m.status', $status);
        }
        $this->db->order_by('m.nim', 'ASC');
        
        $query = $this->db->get();
        return $query->result();
    }

    /**
     * Get statistik kelas per semester
     */
    public function get_statistik_kelas($semester = null) {
        if ($semester) {
            $this->db->where('semester_aktif', $semester);
        }
        $this->db->where('status', 'aktif');
        $this->db->select('kelas, COUNT(*) as jumlah');
        $this->db->group_by('kelas');
        $this->db->order_by('kelas', 'ASC');
        $query = $this->db->get('mahasiswa');
        return $query->result();
    }

    /**
     * Get daftar semua kelas yang ada
     */
    /** get_by_kelas untuk mode lihat: kelas dihitung pada tahun akademik yang dilihat. */
    protected function get_by_kelas_konteks($kelas, $status, $k) {
        $this->db->select('m.*, k.nama_kurikulum');
        $this->db->from('mahasiswa m');
        $this->db->join('kurikulum k', 'k.id = m.id_kurikulum', 'left');
        if ($status) {
            if ($status === 'aktif' && $this->db->field_exists('lulus_otomatis', 'mahasiswa')) {
                $this->db->group_start()->where('m.status', 'aktif')->or_group_start()->where('m.status', 'lulus')->where('m.lulus_otomatis', 1)->group_end()->group_end();
            } else {
                $this->db->where('m.status', $status);
            }
        }
        $this->db->order_by('m.nim', 'ASC');
        $out = array();
        foreach ($this->db->get()->result() as $m) {
            $h = Kenaikan_kelas::hitung($m, $k['th'], $k['sm']);
            if ($h['mentah'] < 1 || $h['melewati_batas']) {
                continue;
            }
            if ($h['kelas'] === $kelas) {
                $m->kelas = $h['kelas'];
                $m->semester_aktif = $h['semester_aktif'];
                $out[] = $m;
            }
        }
        return $out;
    }
    public function get_all_kelas($status = 'aktif') {
        if ($this->konteks_hitung()) {
            // Mode lihat: daftar kelas pada tahun akademik yang dilihat
            $hitung = array();
            foreach ($this->get_snapshot() as $s) {
                $hitung[$s->kelas] = isset($hitung[$s->kelas]) ? $hitung[$s->kelas] + 1 : 1;
            }
            ksort($hitung);
            $out = array();
            foreach ($hitung as $kls => $n) { $out[] = (object) array('kelas' => $kls, 'jumlah' => $n); }
            return $out;
        }
        $this->db->select('kelas, COUNT(*) as jumlah');
        $this->db->from('mahasiswa');
        $this->db->where('kelas IS NOT NULL');
        $this->db->where('kelas !=', '');
        if ($status) {
            $this->db->where('status', $status);
        }
        $this->db->group_by('kelas');
        $this->db->order_by('kelas', 'ASC');
        $query = $this->db->get();
        return $query->result();
    }
}

