<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Kenaikan_kelas
 * Satu-satunya implementasi aturan kenaikan kelas / semester mahasiswa.
 *
 *   semester_mentah = (tahun mulai TA - angkatan) x 2 + (1 ganjil | 2 genap)
 *   semester_riil   = dibatasi 1..14            (semester sebenarnya)
 *   semester_aktif  = min(semester_riil, 8)     (dipakai seluruh modul lama)
 *   kelas           = semester_aktif . rombel   (rombel = huruf asli, permanen)
 *   semester_mentah > 14 -> status 'lulus' + lulus_otomatis = 1
 *                           (ditahan bila masih punya Proposal TA / Skripsi / PKL berjalan)
 *
 * Hanya mahasiswa 'aktif' dan mahasiswa 'lulus' bertanda lulus_otomatis = 1 yang diproses.
 * Lulus manual, cuti, dan nonaktif tidak pernah disentuh.
 * Setiap penerapan disimpan sebagai batch + detail (sebelum/sesudah) sehingga dapat dipulihkan.
 *
 * Membutuhkan migrasi database/migrations/2026_11_15_kenaikan_kelas.sql.
 */
class Kenaikan_kelas {

    const SEMESTER_KELAS_MAX = 8;
    const SEMESTER_STUDI_MAX = 14;

    protected $CI;
    protected $kolom_ada = null;

    public function __construct() {
        $this->CI =& get_instance();
        $this->CI->load->database();
    }

    /* ------------------------------------------------------------------ */
    /* Fungsi murni (tanpa database)                                       */
    /* ------------------------------------------------------------------ */

    /** @return array ['th' => int tahun mulai, 'sm' => 1 (ganjil) | 2 (genap)] */
    public static function parse_ta($ta) {
        $parts = explode('/', (string) $ta->tahun_akademik);
        return array('th' => (int) $parts[0], 'sm' => ($ta->semester === 'ganjil') ? 1 : 2);
    }

    public static function semester_mentah($angkatan, $th, $sm) {
        return (($th - (int) $angkatan) * 2) + $sm;
    }

    /** Huruf rombel dari kode kelas (1A -> A). 'A' bila kosong / tanpa huruf. */
    public static function rombel_dari_kelas($kelas) {
        if (is_string($kelas) && preg_match('/([A-Za-z])\s*$/', trim($kelas), $m)) {
            return strtoupper($m[1]);
        }
        return 'A';
    }

    /**
     * Hitung kondisi target seorang mahasiswa pada tahun akademik tertentu.
     * @param object $mhs baris mahasiswa (angkatan, kelas, rombel)
     * @return array mentah, semester_riil, semester_aktif, rombel, kelas, melewati_batas
     */
    public static function hitung($mhs, $th, $sm) {
        $mentah = self::semester_mentah($mhs->angkatan, $th, $sm);
        $riil = max(1, min($mentah, self::SEMESTER_STUDI_MAX));
        $aktif = min($riil, self::SEMESTER_KELAS_MAX);
        $rombel = !empty($mhs->rombel) ? strtoupper($mhs->rombel) : self::rombel_dari_kelas(isset($mhs->kelas) ? $mhs->kelas : null);
        return array(
            'mentah' => $mentah,
            'semester_riil' => $riil,
            'semester_aktif' => $aktif,
            'rombel' => $rombel,
            'kelas' => $aktif . $rombel,
            'melewati_batas' => ($mentah > self::SEMESTER_STUDI_MAX)
        );
    }

    /* ------------------------------------------------------------------ */
    /* Mahasiswa baru / edit (satu aturan untuk tambah, edit, import)      */
    /* ------------------------------------------------------------------ */

    /** Kunci urut tahun akademik (makin besar = makin baru). */
    public static function kunci_ta($ta) {
        $p = self::parse_ta($ta);
        return $p['th'] * 2 + $p['sm'];
    }

    /**
     * Tahun akademik TERTINGGI yang pernah diterapkan kenaikan kelasnya (dari batch terakhir berstatus diterapkan).
     * Bila belum ada batch (atau tabel belum ada), memakai tahun akademik resmi yang diberikan.
     * @param object|null $ta_resmi
     * @return object|null
     */
    public function tahun_tertinggi_diterapkan($ta_resmi = null) {
        $db = $this->CI->db;
        if ($db->table_exists('kenaikan_kelas_batch')) {
            $b = $db->where('status', 'diterapkan')->order_by('id', 'DESC')->limit(1)->get('kenaikan_kelas_batch')->row();
            if ($b) {
                $ta = $db->get_where('tahun_akademik', array('id' => (int) $b->id_tahun_akademik_ke))->row();
                if ($ta) {
                    return $ta;
                }
            }
        }
        return $ta_resmi;
    }

    /**
     * Catat "jangkar" bila belum ada batch berstatus diterapkan: menandai tahun akademik $ta (resmi saat ini) sebagai
     * keadaan yang tersimpan pada data mahasiswa, sebelum tahun akademik resmi dipindah ke yang lebih lama.
     * Tanpa jangkar, tahun akademik tertinggi (HW) akan ikut bergeser ke tahun akademik lama dan kelas tidak lagi dihitung ulang.
     * Tidak mengubah data mahasiswa (jumlah_berubah = 0).
     */
    public function pastikan_jangkar($ta, $oleh = null) {
        $db = $this->CI->db;
        if (!$ta || !$db->table_exists('kenaikan_kelas_batch')) {
            return;
        }
        $ada = $db->where('status', 'diterapkan')->count_all_results('kenaikan_kelas_batch');
        if ($ada > 0) {
            return;
        }
        $db->insert('kenaikan_kelas_batch', array(
            'id_tahun_akademik_dari' => null, 'id_tahun_akademik_ke' => $ta->id, 'arah' => 'sama',
            'dibuat_oleh' => ($oleh ? $oleh . ' ' : '') . '(jangkar)', 'jumlah_berubah' => 0, 'status' => 'diterapkan'
        ));
    }
    /**
     * Mode aktivasi untuk tahun akademik tujuan:
     *  'maju'       = tujuan lebih baru dari tahun akademik tertinggi yang pernah diterapkan -> kenaikan kelas
     *  'ganti_saja' = tujuan sama / lebih lama -> hanya mengganti tahun akademik resmi (mahasiswa tidak disentuh)
     * @return array ['mode' => string, 'hw' => object|null]
     */
    public function mode_aktivasi($ta_ke, $ta_resmi) {
        $hw = $this->tahun_tertinggi_diterapkan($ta_resmi);
        if (!$ta_resmi || !$hw) {
            return array('mode' => 'maju', 'hw' => $hw);
        }
        return array('mode' => (self::kunci_ta($ta_ke) > self::kunci_ta($hw)) ? 'maju' : 'ganti_saja', 'hw' => $hw);
    }
    /** True bila migrasi kolom baru sudah dijalankan. */
    public function kolom_baru_ada() {
        if ($this->kolom_ada === null) {
            $db = $this->CI->db;
            $this->kolom_ada = $db->field_exists('rombel', 'mahasiswa')
                && $db->field_exists('semester_riil', 'mahasiswa')
                && $db->field_exists('lulus_otomatis', 'mahasiswa');
        }
        return $this->kolom_ada;
    }

    /**
     * Atribut semester untuk mahasiswa baru / angkatan berubah, berdasarkan TA aktif.
     * Kolom baru hanya disertakan bila migrasi sudah dijalankan (kompatibel mundur).
     * @param string|null $kelas kelas yang diinput manual (menentukan rombel)
     * @return array semester_aktif [, semester_riil, rombel]
     */
    public function atribut_baru($angkatan, $kelas = null, $ta = null) {
        $this->CI->load->model('Tahun_akademik_model');
        // Data tersimpan mahasiswa adalah keadaan pada tahun akademik tertinggi yang pernah diterapkan (HW); mahasiswa baru
        // dihitung terhadap itu (bukan tahun akademik yang sedang dilihat / dimundurkan) agar konsisten dengan data lain.
        $ta = $ta ?: $this->tahun_tertinggi_diterapkan($this->CI->Tahun_akademik_model->get_aktif_resmi());

        if (!$ta) {
            $aktif = 1;
            $riil = 1;
        } else {
            $p = self::parse_ta($ta);
            $h = self::hitung((object) array('angkatan' => $angkatan, 'kelas' => $kelas, 'rombel' => null), $p['th'], $p['sm']);
            $aktif = $h['semester_aktif'];
            $riil = $h['semester_riil'];
        }

        $out = array('semester_aktif' => $aktif);
        if ($this->kolom_baru_ada()) {
            $out['semester_riil'] = $riil;
            $out['rombel'] = ($kelas !== null && trim((string) $kelas) !== '') ? self::rombel_dari_kelas($kelas) : null;
        }
        return $out;
    }

    /* ------------------------------------------------------------------ */
    /* Rencana (pratinjau) - TIDAK menulis apa pun                          */
    /* ------------------------------------------------------------------ */

    /**
     * @param object      $ta_ke   tahun akademik yang akan diaktifkan
     * @param object|null $ta_dari tahun akademik aktif sekarang
     * @return array ['arah','blokir','rows','ringkasan']
     */
    public function rencana($ta_ke, $ta_dari = null) {
        $hasil = array('arah' => 'maju', 'blokir' => null, 'rows' => array(), 'ringkasan' => array());

        if (!$this->kolom_baru_ada()) {
            $hasil['blokir'] = 'Migrasi database kenaikan kelas (2026_11_15_kenaikan_kelas.sql) belum dijalankan.';
            return $hasil;
        }

        $ke = self::parse_ta($ta_ke);
        if ($ta_dari) {
            $dari = self::parse_ta($ta_dari);
            $k1 = $dari['th'] * 2 + $dari['sm'];
            $k2 = $ke['th'] * 2 + $ke['sm'];
            $hasil['arah'] = $k2 > $k1 ? 'maju' : ($k2 < $k1 ? 'mundur' : 'sama');
        }

        $db = $this->CI->db;
        $db->where('status', 'aktif');
        $db->or_group_start()->where('status', 'lulus')->where('lulus_otomatis', 1)->group_end();
        $db->order_by('angkatan', 'ASC')->order_by('nim', 'ASC');
        $semua = $db->get('mahasiswa')->result();

        $proses = $this->proses_berjalan();

        $rs = array('total' => count($semua), 'berubah' => 0, 'naik' => 0, 'turun' => 0, 'riil_di_atas_8' => 0,
            'lulus_otomatis' => 0, 'kembali_aktif' => 0, 'ditahan' => 0, 'belum_masuk' => 0);

        foreach ($semua as $m) {
            $h = self::hitung($m, $ke['th'], $ke['sm']);
            $status_baru = $m->status;
            $lulus_baru = (int) $m->lulus_otomatis;
            $ket = null;

            if ($m->status === 'aktif') {
                if ($h['mentah'] < 1) {
                    $rs['belum_masuk']++;
                }
                if ($h['melewati_batas']) {
                    if (isset($proses[(int) $m->id])) {
                        $ket = 'ditahan: ' . implode(', ', $proses[(int) $m->id]) . ' masih berjalan';
                        $rs['ditahan']++;
                    } else {
                        $status_baru = 'lulus';
                        $lulus_baru = 1;
                        $ket = 'lulus otomatis (melewati ' . self::SEMESTER_STUDI_MAX . ' semester)';
                        $rs['lulus_otomatis']++;
                    }
                }
            } else { // lulus + lulus_otomatis = 1
                if (!$h['melewati_batas']) {
                    $status_baru = 'aktif';
                    $lulus_baru = 0;
                    $ket = 'kembali aktif';
                    $rs['kembali_aktif']++;
                }
            }

            $baris = array(
                'id' => (int) $m->id, 'nim' => $m->nim, 'nama' => $m->nama_mahasiswa, 'angkatan' => (int) $m->angkatan,
                'lama' => array('kelas' => $m->kelas, 'semester_aktif' => (int) $m->semester_aktif,
                    'semester_riil' => $m->semester_riil === null ? null : (int) $m->semester_riil,
                    'status' => $m->status, 'rombel' => $m->rombel, 'lulus_otomatis' => (int) $m->lulus_otomatis),
                'baru' => array('kelas' => $h['kelas'], 'semester_aktif' => $h['semester_aktif'],
                    'semester_riil' => $h['semester_riil'], 'status' => $status_baru, 'rombel' => $h['rombel'], 'lulus_otomatis' => $lulus_baru),
                'mentah' => $h['mentah'], 'keterangan' => $ket
            );
            $baris['berubah'] = ($baris['lama'] !== $baris['baru']);

            if ($baris['berubah']) {
                $rs['berubah']++;
                $sa = $baris['baru']['semester_riil'] <=> (int) $baris['lama']['semester_riil'];
                if ($sa > 0) { $rs['naik']++; } elseif ($sa < 0) { $rs['turun']++; }
            }
            if ($h['semester_riil'] > self::SEMESTER_KELAS_MAX) {
                $rs['riil_di_atas_8']++;
            }
            $hasil['rows'][] = $baris;
        }

        if ($hasil['arah'] === 'mundur' && $rs['belum_masuk'] > 0) {
            $hasil['blokir'] = $rs['belum_masuk'] . ' mahasiswa aktif belum masuk pada tahun akademik ' . $ta_ke->tahun_akademik . ' '
                . $ta_ke->semester . ' (angkatan lebih baru dari tahun akademik tujuan). Tahun akademik tidak dapat dimundurkan sejauh itu.';
        }

        $hasil['ringkasan'] = $rs;
        return $hasil;
    }

    /**
     * Mahasiswa dengan Proposal TA / Skripsi / PKL yang masih berjalan.
     * @return array id_mahasiswa => ['Proposal TA'|'Skripsi'|'PKL', ...]
     */
    public function proses_berjalan() {
        $db = $this->CI->db;
        $out = array();

        $cek = array(
            'Proposal TA' => array('mahasiswa_proposal', "SELECT DISTINCT id_mahasiswa FROM mahasiswa_proposal WHERE status IN ('terdaftar','sedang_proposal','layak_sidang')"),
            'Skripsi' => array('mahasiswa_skripsi', "SELECT DISTINCT id_mahasiswa FROM mahasiswa_skripsi WHERE status IN ('terdaftar','sedang_skripsi','layak_sidang')")
        );
        foreach ($cek as $label => $c) {
            if ($db->table_exists($c[0])) {
                foreach ($db->query($c[1])->result() as $r) {
                    $out[(int) $r->id_mahasiswa][] = $label;
                }
            }
        }
        if ($db->table_exists('mahasiswa_pkl') && $db->table_exists('pkl_klaim_nilai')) {
            $sql = "SELECT DISTINCT p.id_mahasiswa FROM mahasiswa_pkl p WHERE NOT EXISTS
                    (SELECT 1 FROM pkl_klaim_nilai k WHERE k.id_mahasiswa = p.id_mahasiswa AND k.status IN ('disetujui','selesai'))";
            foreach ($db->query($sql)->result() as $r) {
                $out[(int) $r->id_mahasiswa][] = 'PKL';
            }
        }
        return $out;
    }

    /* ------------------------------------------------------------------ */
    /* Penerapan (transaksi + snapshot) dan pemulihan                       */
    /* ------------------------------------------------------------------ */

    /**
     * Terapkan rencana. Gagal di tengah = seluruhnya dibatalkan.
     * @return array ['ok','pesan','id_batch','berubah','ringkasan']
     */
    public function terapkan($ta_ke, $ta_dari, $oleh = null) {
        $rencana = $this->rencana($ta_ke, $ta_dari);
        if ($rencana['blokir'] !== null) {
            return array('ok' => false, 'pesan' => $rencana['blokir'], 'id_batch' => null, 'berubah' => 0, 'ringkasan' => $rencana['ringkasan']);
        }

        $ubah = array_values(array_filter($rencana['rows'], function ($r) { return $r['berubah']; }));
        if (empty($ubah)) {
            return array('ok' => true, 'pesan' => 'Tidak ada perubahan kelas/semester.', 'id_batch' => null, 'berubah' => 0, 'ringkasan' => $rencana['ringkasan']);
        }

        $db = $this->CI->db;
        $db->trans_start();
        $db->insert('kenaikan_kelas_batch', array(
            'id_tahun_akademik_dari' => $ta_dari ? $ta_dari->id : null,
            'id_tahun_akademik_ke' => $ta_ke->id,
            'arah' => $rencana['arah'],
            'dibuat_oleh' => $oleh,
            'jumlah_berubah' => count($ubah),
            'status' => 'diterapkan'
        ));
        $id_batch = $db->insert_id();

        $detail = array();
        foreach ($ubah as $r) {
            $detail[] = array(
                'id_batch' => $id_batch, 'id_mahasiswa' => $r['id'],
                'kelas_lama' => $r['lama']['kelas'], 'kelas_baru' => $r['baru']['kelas'],
                'semester_aktif_lama' => $r['lama']['semester_aktif'], 'semester_aktif_baru' => $r['baru']['semester_aktif'],
                'semester_riil_lama' => $r['lama']['semester_riil'], 'semester_riil_baru' => $r['baru']['semester_riil'],
                'status_lama' => $r['lama']['status'], 'status_baru' => $r['baru']['status'],
                'rombel_lama' => $r['lama']['rombel'], 'rombel_baru' => $r['baru']['rombel'],
                'lulus_otomatis_lama' => $r['lama']['lulus_otomatis'], 'lulus_otomatis_baru' => $r['baru']['lulus_otomatis'],
                'keterangan' => $r['keterangan']
            );
            $db->where('id', $r['id'])->update('mahasiswa', $r['baru']);
        }
        $db->insert_batch('kenaikan_kelas_detail', $detail);
        $db->trans_complete();

        if (!$db->trans_status()) {
            return array('ok' => false, 'pesan' => 'Gagal menerapkan kenaikan kelas; tidak ada data yang diubah.', 'id_batch' => null, 'berubah' => 0, 'ringkasan' => $rencana['ringkasan']);
        }
        return array('ok' => true, 'pesan' => count($ubah) . ' mahasiswa diperbarui.', 'id_batch' => $id_batch, 'berubah' => count($ubah), 'ringkasan' => $rencana['ringkasan']);
    }

    /**
     * Pulihkan satu batch (hanya batch terakhir yang masih berstatus diterapkan).
     * Baris yang sudah diubah manual sesudah batch dilewati (tidak ditimpa).
     */
    public function pulihkan($id_batch, $oleh = null) {
        $db = $this->CI->db;
        $batch = $db->get_where('kenaikan_kelas_batch', array('id' => (int) $id_batch))->row();
        if (!$batch) {
            return array('ok' => false, 'pesan' => 'Batch tidak ditemukan.');
        }
        if ($batch->status !== 'diterapkan') {
            return array('ok' => false, 'pesan' => 'Batch ini sudah dipulihkan.');
        }
        $terakhir = $db->select_max('id')->where('status', 'diterapkan')->get('kenaikan_kelas_batch')->row();
        if ((int) $terakhir->id !== (int) $batch->id) {
            return array('ok' => false, 'pesan' => 'Hanya batch terakhir yang dapat dipulihkan. Pulihkan batch yang lebih baru lebih dulu.');
        }

        $detail = $db->get_where('kenaikan_kelas_detail', array('id_batch' => (int) $id_batch))->result();
        $pulih = 0;
        $lewat = 0;
        $db->trans_start();
        foreach ($detail as $d) {
            $m = $db->get_where('mahasiswa', array('id' => $d->id_mahasiswa))->row();
            $sama = $m && (string) $m->kelas === (string) $d->kelas_baru
                && (int) $m->semester_aktif === (int) $d->semester_aktif_baru
                && ($m->semester_riil === null ? null : (int) $m->semester_riil) === ($d->semester_riil_baru === null ? null : (int) $d->semester_riil_baru)
                && $m->status === $d->status_baru
                && (string) $m->rombel === (string) $d->rombel_baru
                && (int) $m->lulus_otomatis === (int) $d->lulus_otomatis_baru;
            if (!$sama) {
                $lewat++;
                continue;
            }
            $db->where('id', $d->id_mahasiswa)->update('mahasiswa', array(
                'kelas' => $d->kelas_lama, 'semester_aktif' => $d->semester_aktif_lama, 'semester_riil' => $d->semester_riil_lama,
                'status' => $d->status_lama, 'rombel' => $d->rombel_lama, 'lulus_otomatis' => $d->lulus_otomatis_lama
            ));
            $pulih++;
        }
        $db->where('id', $batch->id)->update('kenaikan_kelas_batch', array('status' => 'dipulihkan', 'dipulihkan_pada' => date('Y-m-d H:i:s')));
        $db->trans_complete();

        if (!$db->trans_status()) {
            return array('ok' => false, 'pesan' => 'Gagal memulihkan; tidak ada data yang diubah.');
        }
        return array('ok' => true, 'pesan' => $pulih . ' mahasiswa dipulihkan' . ($lewat ? ', ' . $lewat . ' dilewati karena sudah diubah manual.' : '.'),
            'dipulihkan' => $pulih, 'dilewati' => $lewat);
    }
}
