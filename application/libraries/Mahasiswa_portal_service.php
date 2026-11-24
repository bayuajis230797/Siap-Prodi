<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Data portal mahasiswa untuk API mobile (baca saja, mirror logika web).
 */
class Mahasiswa_portal_service {

    /** @var CI_Controller */
    protected $CI;

    public function __construct() {
        $this->CI =& get_instance();
        $this->CI->load->model('Tahun_akademik_model');
        $this->CI->load->model('Mahasiswa_model');
        $this->CI->load->model('Jadwal_kuliah_model');
        $this->CI->load->model('Jadwal_uts_model');
        $this->CI->load->model('Jadwal_uas_model');
        $this->CI->load->model('Jadwal_pkl_model');
        $this->CI->load->model('Mahasiswa_pkl_model');
        $this->CI->load->model('Skripsi_model');
        $this->CI->load->model('Kegiatan_model');
        $this->CI->load->model('Manajemen_berkas_model');
        $this->CI->load->model('Permintaan_surat_model');
        $this->CI->load->model('Tagihan_berkas_model');
        $this->CI->load->model('Pkl_pengajuan_model');
        $this->CI->load->model('Pkl_tempat_model');
        $this->CI->load->model('Kurikulum_model');
        $this->CI->load->model('Mata_kuliah_aktif_model');
        $this->CI->load->model('Pkl_bimbingan_model');
        $this->CI->load->model('Pkl_nilai_rekap_model');
        $this->CI->load->model('Skripsi_pengajuan_model');
        $this->CI->load->model('Skripsi_pendaftaran_sidang_model');
        $this->CI->load->model('Jadwal_skripsi_model');
        $this->CI->load->model('Skripsi_klaim_nilai_model');
        $this->CI->load->model('Skripsi_bimbingan_model');
        $this->CI->load->model('Skripsi_bimbingan_log_model');
        $this->CI->load->model('Pkl_bimbingan_log_model');
        $this->CI->load->model('Skripsi_nilai_sidang_model');
        $this->CI->load->model('Pkl_penilaian_model');
        $this->CI->load->model('Pkl_pendaftaran_sidang_model');
    }

    /**
     * @param object|array|null $data
     * @return array|null
     */
    public function to_array($data) {
        if ($data === null) {
            return null;
        }
        $json = json_encode($data);
        if ($json === false) {
            return null;
        }
        $arr = json_decode($json, true);
        return is_array($arr) ? $arr : null;
    }

    /**
     * @param array $rows
     * @return array
     */
    public function rows_to_array($rows) {
        if (!is_array($rows) && !($rows instanceof Traversable)) {
            return array();
        }
        $out = array();
        foreach ($rows as $r) {
            $a = $this->to_array($r);
            if ($a !== null) {
                $out[] = $a;
            }
        }
        return $out;
    }

    /**
     * Label status proses dari log bimbingan skripsi terakhir.
     *
     * @param object $bimbingan_row
     * @return array<string, mixed>
     */
    private function enrich_skripsi_bimbingan_row($bimbingan_row) {
        $a = $this->to_array($bimbingan_row);
        if ($a === null) {
            return array();
        }
        $id = isset($bimbingan_row->id) ? (int) $bimbingan_row->id : 0;
        if ($id > 0) {
            $log = $this->CI->Skripsi_bimbingan_log_model->get_latest_by_bimbingan($id);
            if ($log && isset($log->status_revisi) && $log->status_revisi !== null && $log->status_revisi !== '') {
                $sr = strtolower((string) $log->status_revisi);
                if ($sr === 'acc') {
                    $a['status_proses'] = 'Disetujui (ACC)';
                } elseif ($sr === 'revisi') {
                    $a['status_proses'] = 'Perlu revisi';
                } else {
                    $a['status_proses'] = (string) $log->status_revisi;
                }
            } else {
                $a['status_proses'] = 'Menunggu tanggapan pembimbing';
            }
            if ($log && !empty($log->created_at)) {
                $a['log_terakhir_pada'] = $log->created_at;
            }
        }
        return $a;
    }

    /**
     * @param array $rows objects from Pkl_bimbingan_model->get_by_mahasiswa
     * @return array
     */
    private function enrich_pkl_bimbingan_rows($rows) {
        if (!is_array($rows) && !($rows instanceof Traversable)) {
            return array();
        }
        $out = array();
        foreach ($rows as $r) {
            $a = $this->to_array($r);
            if ($a === null) {
                continue;
            }
            $id = isset($r->id) ? (int) $r->id : 0;
            if ($id > 0) {
                $log = $this->CI->Pkl_bimbingan_log_model->get_latest_by_bimbingan($id);
                if ($log && isset($log->status_revisi) && $log->status_revisi !== null && $log->status_revisi !== '') {
                    $sr = strtolower((string) $log->status_revisi);
                    if ($sr === 'acc') {
                        $a['status_proses'] = 'Disetujui (ACC)';
                    } elseif ($sr === 'revisi') {
                        $a['status_proses'] = 'Perlu revisi';
                    } else {
                        $a['status_proses'] = (string) $log->status_revisi;
                    }
                } else {
                    $a['status_proses'] = 'Menunggu tanggapan pembimbing';
                }
                if ($log && !empty($log->created_at)) {
                    $a['log_terakhir_pada'] = $log->created_at;
                }
            }
            $out[] = $a;
        }
        return $out;
    }

    /**
     * @param array $rows objects from Skripsi_bimbingan_model->get_by_mahasiswa
     * @return array
     */
    private function enrich_skripsi_bimbingan_rows($rows) {
        if (!is_array($rows) && !($rows instanceof Traversable)) {
            return array();
        }
        $out = array();
        foreach ($rows as $r) {
            $enriched = $this->enrich_skripsi_bimbingan_row($r);
            if (!empty($enriched)) {
                $out[] = $enriched;
            }
        }
        return $out;
    }

    public function get_context_mahasiswa($id_mahasiswa) {
        $id_mahasiswa = (int) $id_mahasiswa;
        $tahun = $this->CI->Tahun_akademik_model->get_aktif();
        $mhs = $this->CI->Mahasiswa_model->get_by_id($id_mahasiswa);
        return array(
            'id_mahasiswa' => $id_mahasiswa,
            'mahasiswa' => $this->to_array($mhs),
            'tahun_aktif' => $this->to_array($tahun),
            'kelas' => $mhs ? $mhs->kelas : null,
            'id_tahun' => $tahun ? (int) $tahun->id : null,
        );
    }

    public function dashboard($id_mahasiswa) {
        $ctx = $this->get_context_mahasiswa($id_mahasiswa);
        $id_tahun = $ctx['id_tahun'];
        $is_pkl = false;
        $is_skripsi = false;
        if ($id_tahun) {
            $is_pkl = $this->CI->Mahasiswa_pkl_model->is_registered($id_mahasiswa, $id_tahun);
            $is_skripsi = $this->CI->Skripsi_model->get_by_mahasiswa($id_mahasiswa, $id_tahun) !== null;
        }
        $tagihan_list = $this->CI->Tagihan_berkas_model->get_by_mahasiswa($id_mahasiswa, 1);
        $total_tagihan = count($tagihan_list);
        $belum = 0;
        $sudah = 0;
        $terlambat = 0;
        foreach ($tagihan_list as $tagihan) {
            $detail = $this->CI->Tagihan_berkas_model->get_detail_by_mahasiswa_tagihan($tagihan->id, $id_mahasiswa);
            if ($tagihan->deadline && strtotime($tagihan->deadline) < strtotime('today')) {
                $terlambat++;
            }
            if (isset($tagihan->is_hardcopy) && (int) $tagihan->is_hardcopy === 1) {
                if ($detail && $detail->status_hardcopy == 'sudah') {
                    $sudah++;
                } else {
                    $belum++;
                }
            } else {
                if ($detail && $detail->status == 'sudah') {
                    $sudah++;
                } else {
                    $belum++;
                }
            }
        }
        return array(
            'success' => true,
            'tahun_aktif' => $ctx['tahun_aktif'],
            'mahasiswa' => $ctx['mahasiswa'],
            'is_registered_pkl' => $is_pkl,
            'is_registered_skripsi' => $is_skripsi,
            'tagihan' => array(
                'total' => $total_tagihan,
                'belum' => $belum,
                'sudah' => $sudah,
                'terlambat' => $terlambat,
            ),
        );
    }

    public function jadwal_kuliah($id_mahasiswa) {
        $ctx = $this->get_context_mahasiswa($id_mahasiswa);
        $list = array();
        if ($ctx['id_tahun'] && $ctx['kelas']) {
            $list = $this->CI->Jadwal_kuliah_model->get_by_kelas($ctx['kelas'], $ctx['id_tahun']);
        }
        $hari_list = array('senin', 'selasa', 'rabu', 'kamis', 'jumat', 'sabtu', 'minggu');
        $by_hari = array();
        foreach ($hari_list as $h) {
            $by_hari[$h] = array();
        }
        foreach ($list as $jk) {
            $hari = strtolower($jk->hari);
            if (isset($by_hari[$hari])) {
                $by_hari[$hari][] = $this->to_array($jk);
            }
        }
        return array(
            'success' => true,
            'tahun_aktif' => $ctx['tahun_aktif'],
            'kelas' => $ctx['kelas'],
            'by_hari' => $by_hari,
            'items' => $this->rows_to_array($list),
        );
    }

    public function jadwal_uts($id_mahasiswa) {
        $ctx = $this->get_context_mahasiswa($id_mahasiswa);
        $rows = array();
        if ($ctx['id_tahun'] && $ctx['kelas']) {
            $rows = $this->get_jadwal_uts_by_mahasiswa($id_mahasiswa, $ctx['kelas'], $ctx['id_tahun']);
        }
        return array(
            'success' => true,
            'tahun_aktif' => $ctx['tahun_aktif'],
            'kelas' => $ctx['kelas'],
            'items' => $this->rows_to_array($rows),
        );
    }

    public function jadwal_uas($id_mahasiswa) {
        $ctx = $this->get_context_mahasiswa($id_mahasiswa);
        $rows = array();
        if ($ctx['id_tahun'] && $ctx['kelas']) {
            $rows = $this->get_jadwal_uas_by_mahasiswa($id_mahasiswa, $ctx['kelas'], $ctx['id_tahun']);
        }
        return array(
            'success' => true,
            'tahun_aktif' => $ctx['tahun_aktif'],
            'kelas' => $ctx['kelas'],
            'items' => $this->rows_to_array($rows),
        );
    }

    public function kegiatan_saya($id_mahasiswa) {
        $this->CI->Kegiatan_model->auto_update_status();
        $list = $this->CI->Kegiatan_model->get_kegiatan_by_mahasiswa($id_mahasiswa, 80, 0);
        return array(
            'success' => true,
            'items' => $this->rows_to_array($list),
        );
    }

    public function ujian_jadwal($id_mahasiswa) {
        $ctx = $this->get_context_mahasiswa($id_mahasiswa);
        $uts = array();
        $uas = array();
        if ($ctx['id_tahun'] && $ctx['kelas'] && $ctx['mahasiswa']) {
            $uts = $this->get_jadwal_uts_by_mahasiswa($id_mahasiswa, $ctx['kelas'], $ctx['id_tahun']);
            $uas = $this->get_jadwal_uas_by_mahasiswa($id_mahasiswa, $ctx['kelas'], $ctx['id_tahun']);
        }
        return array(
            'success' => true,
            'uts' => $this->rows_to_array($uts),
            'uas' => $this->rows_to_array($uas),
        );
    }

    public function manajemen_berkas() {
        $list = $this->CI->Manajemen_berkas_model->get_all(1, null, 200, 0);
        $items = array();
        foreach ($list as $row) {
            $a = $this->to_array($row);
            if ($a && !empty($row->file_path)) {
                if (strpos($row->file_path, 'http') === 0) {
                    $a['file_url'] = $row->file_path;
                } else {
                    $a['file_url'] = base_url($row->file_path);
                }
            }
            $items[] = $a;
        }
        return array('success' => true, 'items' => $items);
    }

    public function permintaan_surat($id_mahasiswa) {
        $list = $this->CI->Permintaan_surat_model->get_by_mahasiswa($id_mahasiswa, 100, 0);
        return array('success' => true, 'items' => $this->rows_to_array($list));
    }

    public function tagihan_berkas($id_mahasiswa) {
        $list = $this->CI->Tagihan_berkas_model->get_by_mahasiswa($id_mahasiswa, 1);
        $items = array();
        foreach ($list as $tagihan) {
            $a = $this->to_array($tagihan);
            $detail = $this->CI->Tagihan_berkas_model->get_detail_by_mahasiswa_tagihan($tagihan->id, $id_mahasiswa);
            $a['detail'] = $this->to_array($detail);
            $items[] = $a;
        }
        return array('success' => true, 'items' => $items);
    }

    public function jadwal_sidang_pkl($id_mahasiswa) {
        $ctx = $this->get_context_mahasiswa($id_mahasiswa);
        $list = array();
        if ($ctx['id_tahun']) {
            $list = $this->CI->Jadwal_pkl_model->get_by_mahasiswa($id_mahasiswa, $ctx['id_tahun']);
        }
        $pengajuan = ($ctx['id_tahun'])
            ? $this->CI->Pkl_pengajuan_model->get_by_mahasiswa($id_mahasiswa, $ctx['id_tahun'])
            : null;
        $judul_pkl = ($pengajuan && !empty($pengajuan->judul_pkl)) ? $pengajuan->judul_pkl : '';
        $items = array();
        foreach ($list as $row) {
            $a = $this->to_array($row);
            if ($a !== null) {
                $a['judul_pkl'] = $judul_pkl;
                $pen_list = $this->CI->Pkl_penilaian_model->get_by_jadwal_pkl($row->id);
                $catatan_pkl = array();
                foreach ($pen_list as $pp) {
                    if (empty($pp->catatan) || trim((string) $pp->catatan) === '') {
                        continue;
                    }
                    $catatan_pkl[] = array(
                        'penilai_nama' => isset($pp->penilai_nama) ? $pp->penilai_nama : '',
                        'penilai_nidn' => isset($pp->penilai_nidn) ? $pp->penilai_nidn : '',
                        'catatan' => $pp->catatan,
                        'tanggal_penilaian' => isset($pp->tanggal_penilaian) ? $pp->tanggal_penilaian : null,
                        'created_at' => isset($pp->created_at) ? $pp->created_at : null,
                    );
                }
                $a['catatan_revisi_penguji'] = $catatan_pkl;
                $items[] = $a;
            }
        }
        $pendaftaran_sidang = null;
        if ($ctx['id_tahun']) {
            $pendaftaran_sidang = $this->CI->Pkl_pendaftaran_sidang_model->get_by_mahasiswa($id_mahasiswa, $ctx['id_tahun']);
        }
        return array(
            'success' => true,
            'pendaftaran_sidang' => $this->to_array($pendaftaran_sidang),
            'items' => $items,
        );
    }

    public function pkl_portal($id_mahasiswa) {
        $ctx = $this->get_context_mahasiswa($id_mahasiswa);
        $id_tahun = $ctx['id_tahun'];
        $mp = $id_tahun ? $this->CI->Mahasiswa_pkl_model->get_by_mahasiswa($id_mahasiswa, $id_tahun) : null;
        $pengajuan = $id_tahun ? $this->CI->Pkl_pengajuan_model->get_by_mahasiswa($id_mahasiswa, $id_tahun) : null;
        $tempat = $id_tahun ? $this->CI->Pkl_tempat_model->get_by_mahasiswa($id_mahasiswa, $id_tahun) : null;
        $jadwal_block = $this->jadwal_sidang_pkl($id_mahasiswa);
        $pengajuan_arr = $this->to_array($pengajuan);
        // Paritas web (pkl.php / pkl_detail_pengajuan): badge memakai status_acc_judul, bukan kolom status.
        if ($pengajuan_arr && $pengajuan) {
            $acc = isset($pengajuan->status_acc_judul) ? (string) $pengajuan->status_acc_judul : 'pending';
            if ($acc === 'acc') {
                $pengajuan_arr['status_judul_tampilan'] = 'Disetujui';
            } elseif ($acc === 'revisi') {
                $pengajuan_arr['status_judul_tampilan'] = 'Revisi';
            } else {
                $pengajuan_arr['status_judul_tampilan'] = 'Pending';
            }
        }
        return array(
            'success' => true,
            'mahasiswa_pkl' => $this->to_array($mp),
            'pengajuan' => $pengajuan_arr,
            'tempat_pkl' => $this->to_array($tempat),
            'pendaftaran_sidang' => isset($jadwal_block['pendaftaran_sidang']) ? $jadwal_block['pendaftaran_sidang'] : null,
            'jadwal_sidang' => isset($jadwal_block['items']) ? $jadwal_block['items'] : array(),
        );
    }

    public function skripsi_portal($id_mahasiswa) {
        $ctx = $this->get_context_mahasiswa($id_mahasiswa);
        $id_tahun = $ctx['id_tahun'];
        $sk = $id_tahun ? $this->CI->Skripsi_model->get_by_mahasiswa($id_mahasiswa, $id_tahun) : null;
        return array(
            'success' => true,
            'skripsi' => $this->to_array($sk),
        );
    }

    /**
     * Ringkasan modul skripsi untuk aplikasi mobile (status & baca saja).
     */
    public function skripsi_ringkas($id_mahasiswa) {
        $id_mahasiswa = (int) $id_mahasiswa;
        $tahun = $this->CI->Tahun_akademik_model->get_aktif();
        if (!$tahun) {
            return array('success' => false, 'message' => 'Tidak ada tahun akademik aktif.');
        }
        $sk = $this->CI->Skripsi_model->get_by_mahasiswa($id_mahasiswa, $tahun->id);
        if (!$sk) {
            return array('success' => false, 'message' => 'Anda belum terdaftar sebagai peserta skripsi.');
        }

        $pengajuan = $this->CI->Skripsi_pengajuan_model->get_latest_by_mahasiswa($id_mahasiswa, $tahun->id);
        $jadwal_list = $this->CI->Jadwal_skripsi_model->get_by_mahasiswa($sk->id);

        $pendaftaran_jadwal = null;
        $pendaftaran_verifikasi = null;
        if ($this->CI->db->field_exists('tahap', 'skripsi_pendaftaran_sidang')) {
            $pendaftaran_jadwal = $this->CI->Skripsi_pendaftaran_sidang_model->get_by_mahasiswa_skripsi_tahap($sk->id, 'jadwal');
            $pendaftaran_verifikasi = $this->CI->Skripsi_pendaftaran_sidang_model->get_by_mahasiswa_skripsi_tahap($sk->id, 'verifikasi');
        } else {
            $pendaftaran_verifikasi = $this->CI->Skripsi_pendaftaran_sidang_model->get_by_mahasiswa_skripsi($sk->id);
        }

        $plot_penguji = false;
        foreach ($jadwal_list as $j) {
            if (!empty($j->id_ketua_penguji)) {
                $plot_penguji = true;
                break;
            }
        }

        $klaim = $this->CI->Skripsi_klaim_nilai_model->get_by_mahasiswa_skripsi($sk->id);
        $klaim_api = null;
        if ($klaim) {
            // Mobile: jangan kirim kunci FK/internal (id jadwal, tahun, mahasiswa skripsi).
            $klaim_api = array(
                'status' => isset($klaim->status) ? (string) $klaim->status : null,
                'catatan_sekprodi' => isset($klaim->catatan_sekprodi) ? $klaim->catatan_sekprodi : null,
                'nilai_akhir' => isset($klaim->nilai_akhir) ? $klaim->nilai_akhir : null,
                'grade' => isset($klaim->grade) ? (string) $klaim->grade : null,
                'created_at' => isset($klaim->created_at) ? $klaim->created_at : null,
                'updated_at' => isset($klaim->updated_at) ? $klaim->updated_at : null,
            );
        }
        $bimbingan_raw = $this->CI->Skripsi_bimbingan_model->get_by_mahasiswa($id_mahasiswa, $tahun->id);
        $bimbingan = $this->enrich_skripsi_bimbingan_rows($bimbingan_raw);

        $pengajuan_arr = $this->to_array($pengajuan);
        if ($pengajuan_arr && $pengajuan) {
            if (!empty($pengajuan->judul_pengajuan)) {
                $pengajuan_arr['judul_diajukan'] = $pengajuan->judul_pengajuan;
            }
            if (!empty($pengajuan->judul_disetujui)) {
                $pengajuan_arr['judul_disetujui_teks'] = $pengajuan->judul_disetujui;
            }
            if (isset($pengajuan->status) && $pengajuan->status === 'disetujui') {
                if (!empty($pengajuan->updated_at)) {
                    $pengajuan_arr['waktu_persetujuan'] = $pengajuan->updated_at;
                } elseif (!empty($pengajuan->created_at)) {
                    $pengajuan_arr['waktu_persetujuan'] = $pengajuan->created_at;
                }
            }
        }

        $jadwal_sidang_api = array();
        foreach ($jadwal_list as $jw) {
            $row = $this->to_array($jw);
            if ($row === null) {
                continue;
            }
            $nilai_sidang_list = $this->CI->Skripsi_nilai_sidang_model->get_by_jadwal($jw->id);
            $catatan_rows = array();
            foreach ($nilai_sidang_list as $ns) {
                if (empty($ns->catatan_revisi) || trim((string) $ns->catatan_revisi) === '') {
                    continue;
                }
                $peran = isset($ns->peran) ? (string) $ns->peran : '';
                if ($peran === 'ketua') {
                    $peran_label = 'Ketua Penguji';
                } elseif ($peran === 'anggota_1') {
                    $peran_label = 'Anggota Penguji 1';
                } elseif ($peran === 'anggota_2') {
                    $peran_label = 'Anggota Penguji 2';
                } else {
                    $peran_label = 'Penguji';
                }
                $catatan_rows[] = array(
                    'peran' => $peran,
                    'peran_label' => $peran_label,
                    'penguji_nama' => isset($ns->penguji_nama) ? $ns->penguji_nama : '',
                    'penguji_nidn' => isset($ns->penguji_nidn) ? $ns->penguji_nidn : '',
                    'catatan_revisi' => $ns->catatan_revisi,
                    'created_at' => isset($ns->created_at) ? $ns->created_at : null,
                );
            }
            $row['catatan_revisi_penguji'] = $catatan_rows;
            $jadwal_sidang_api[] = $row;
        }

        return array(
            'success' => true,
            'skripsi' => $this->to_array($sk),
            'pengajuan' => $pengajuan_arr,
            'jadwal_sidang' => $jadwal_sidang_api,
            'pendaftaran_jadwal' => $this->to_array($pendaftaran_jadwal),
            'pendaftaran_verifikasi' => $this->to_array($pendaftaran_verifikasi),
            'plot_penguji_terjadwal' => $plot_penguji,
            'klaim_nilai' => $klaim_api,
            'bimbingan' => $bimbingan,
        );
    }

    /**
     * Bangun URL absolut untuk file lampiran log bimbingan.
     */
    private function bimbingan_file_url($file_path) {
        if (empty($file_path)) {
            return null;
        }
        $fp = (string) $file_path;
        if (strpos($fp, 'http://') === 0 || strpos($fp, 'https://') === 0) {
            return $fp;
        }
        return base_url(ltrim($fp, '/'));
    }

    /**
     * Daftar log bimbingan skripsi (percakapan dosen ↔ mahasiswa) untuk satu id bimbingan.
     */
    public function skripsi_bimbingan_log($id_mahasiswa, $id_bimbingan) {
        $id_mahasiswa = (int) $id_mahasiswa;
        $id_bimbingan = (int) $id_bimbingan;
        $bimbingan = $this->CI->Skripsi_bimbingan_model->get_by_id($id_bimbingan);
        if (!$bimbingan || (int) $bimbingan->id_mahasiswa !== $id_mahasiswa) {
            return array('success' => false, 'message' => 'Bimbingan tidak ditemukan.');
        }
        $logs = $this->CI->Skripsi_bimbingan_log_model->get_by_bimbingan($id_bimbingan);
        $items = array();
        foreach ($logs as $log) {
            $a = $this->to_array($log);
            if ($a === null) {
                continue;
            }
            $a['file_url'] = $this->bimbingan_file_url(isset($log->file_path) ? $log->file_path : null);
            $items[] = $a;
        }
        return array(
            'success' => true,
            'bimbingan' => array(
                'id' => (int) $bimbingan->id,
                'judul_topik' => isset($bimbingan->judul_topik) ? $bimbingan->judul_topik : null,
                'nama_dosen' => isset($bimbingan->nama_dosen) ? $bimbingan->nama_dosen : null,
                'nidn' => isset($bimbingan->nidn) ? $bimbingan->nidn : null,
                'status' => isset($bimbingan->status) ? $bimbingan->status : null,
            ),
            'logs' => $items,
        );
    }

    /**
     * Daftar log bimbingan PKL (percakapan dosen ↔ mahasiswa) untuk satu id bimbingan.
     */
    public function pkl_bimbingan_log($id_mahasiswa, $id_bimbingan) {
        $id_mahasiswa = (int) $id_mahasiswa;
        $id_bimbingan = (int) $id_bimbingan;
        $this->CI->db->select('pb.*, d.nama_dosen, d.nidn');
        $this->CI->db->from('pkl_bimbingan pb');
        $this->CI->db->join('dosen d', 'd.id = pb.id_pembimbing', 'left');
        $this->CI->db->where('pb.id', $id_bimbingan);
        $bimbingan = $this->CI->db->get()->row();
        if (!$bimbingan || (int) $bimbingan->id_mahasiswa !== $id_mahasiswa) {
            return array('success' => false, 'message' => 'Bimbingan tidak ditemukan.');
        }
        $logs = $this->CI->Pkl_bimbingan_log_model->get_by_bimbingan($id_bimbingan);
        $items = array();
        foreach ($logs as $log) {
            $a = $this->to_array($log);
            if ($a === null) {
                continue;
            }
            $a['file_url'] = $this->bimbingan_file_url(isset($log->file_path) ? $log->file_path : null);
            $items[] = $a;
        }
        return array(
            'success' => true,
            'bimbingan' => array(
                'id' => (int) $bimbingan->id,
                'judul_topik' => isset($bimbingan->judul_topik) ? $bimbingan->judul_topik : null,
                'nama_dosen' => isset($bimbingan->nama_dosen) ? $bimbingan->nama_dosen : null,
                'nidn' => isset($bimbingan->nidn) ? $bimbingan->nidn : null,
                'status' => isset($bimbingan->status) ? $bimbingan->status : null,
            ),
            'logs' => $items,
        );
    }

    /**
     * Status bimbingan PKL + nilai rekap (baca saja).
     */
    public function pkl_bimbingan_nilai($id_mahasiswa) {
        $id_mahasiswa = (int) $id_mahasiswa;
        $tahun = $this->CI->Tahun_akademik_model->get_aktif();
        if (!$tahun) {
            return array('success' => false, 'message' => 'Tidak ada tahun akademik aktif.');
        }
        if (!$this->CI->Mahasiswa_pkl_model->is_registered($id_mahasiswa, $tahun->id)) {
            return array('success' => false, 'message' => 'Anda belum terdaftar PKL.');
        }
        $bimbingan_raw = $this->CI->Pkl_bimbingan_model->get_by_mahasiswa($id_mahasiswa, $tahun->id);
        $bimbingan = $this->enrich_pkl_bimbingan_rows($bimbingan_raw);
        $nilai = $this->CI->Pkl_nilai_rekap_model->get_by_mahasiswa($id_mahasiswa, $tahun->id);
        $this->CI->load->helper('pkl');
        $this->CI->load->model('Pkl_klaim_nilai_model');
        $nilai_rekap_api = null;
        if ($nilai) {
            $akhir = isset($nilai->nilai_akhir) ? floatval($nilai->nilai_akhir) : 0.0;
            $huruf = (isset($nilai->nilai_huruf) && $nilai->nilai_huruf !== null && (string) $nilai->nilai_huruf !== '')
                ? trim((string) $nilai->nilai_huruf)
                : null;
            if (($huruf === null || $huruf === '') && $akhir > 0) {
                $huruf = konversi_nilai_huruf_pkl($akhir);
            }
            $nilai_rekap_api = array(
                'nilai_akhir' => $akhir,
                'grade' => ($huruf !== null && $huruf !== '') ? $huruf : null,
            );
        }
        $klaim = $this->CI->Pkl_klaim_nilai_model->get_by_mahasiswa($id_mahasiswa, $tahun->id);
        $klaim_arr = null;
        if ($klaim) {
            $klaim_arr = array(
                'status' => isset($klaim->status) ? (string) $klaim->status : null,
                'catatan_sekprodi' => isset($klaim->catatan_sekprodi) ? $klaim->catatan_sekprodi : null,
                'nim' => isset($klaim->nim) ? (string) $klaim->nim : null,
                'nama_mahasiswa' => isset($klaim->nama_mahasiswa) ? (string) $klaim->nama_mahasiswa : null,
                'judul_pkl' => isset($klaim->judul_pkl) ? (string) $klaim->judul_pkl : null,
            );
        }
        return array(
            'success' => true,
            'bimbingan' => $bimbingan,
            'nilai_rekap' => $nilai_rekap_api,
            'klaim_nilai' => $klaim_arr,
        );
    }

    /**
     * Halaman beranda mahasiswa: struktur kurikulum (seperti web) + ringkasan tagihan + progress PKL/Skripsi.
     */
    public function beranda($id_mahasiswa) {
        $id_mahasiswa = (int) $id_mahasiswa;
        $tahun_aktif = $this->CI->Tahun_akademik_model->get_aktif();
        $mahasiswa = $this->CI->Mahasiswa_model->get_by_id($id_mahasiswa);

        $is_pkl = false;
        $is_skripsi = false;
        if ($tahun_aktif) {
            $is_pkl = $this->CI->Mahasiswa_pkl_model->is_registered($id_mahasiswa, $tahun_aktif->id);
            $is_skripsi = $this->CI->Skripsi_model->get_by_mahasiswa($id_mahasiswa, $tahun_aktif->id) !== null;
        }

        $struktur_kurikulum = array();
        $kurikulum_info = null;
        if ($mahasiswa && $mahasiswa->id_kurikulum) {
            $kurikulum_info = $this->CI->Kurikulum_model->get_by_id($mahasiswa->id_kurikulum);
            if ($kurikulum_info) {
                $all_mk = $this->CI->Kurikulum_model->get_all_mata_kuliah_by_kurikulum($mahasiswa->id_kurikulum);
                $semester_aktif_mhs = $mahasiswa->semester_aktif ? intval($mahasiswa->semester_aktif) : 1;
                foreach ($all_mk as $mk) {
                    $semester = intval($mk->semester);
                    if (!isset($struktur_kurikulum[$semester])) {
                        $struktur_kurikulum[$semester] = array();
                    }
                    $status_mk = 'akan_diambil';
                    if ($semester < $semester_aktif_mhs) {
                        $status_mk = 'sudah_diambil';
                    } elseif ($semester == $semester_aktif_mhs) {
                        $status_mk = 'sedang_diambil';
                    }
                    $is_aktif_ta = false;
                    if ($tahun_aktif) {
                        $is_aktif_ta = $this->CI->Mata_kuliah_aktif_model->is_aktif($mk->id, $tahun_aktif->id);
                    }
                    $row = $this->to_array($mk);
                    if ($row !== null) {
                        $row['status_mk'] = $status_mk;
                        $row['is_aktif_ta'] = $is_aktif_ta;
                        $struktur_kurikulum[$semester][] = $row;
                    }
                }
                ksort($struktur_kurikulum);
            }
        }

        $this->CI->load->helper('pkl');
        $pkl_progress = null;
        if ($is_pkl && $tahun_aktif) {
            $pkl_progress = calculate_pkl_progress($id_mahasiswa, $tahun_aktif);
        }
        $skripsi_progress = null;
        if ($is_skripsi && $tahun_aktif) {
            $skripsi_progress = $this->calculate_skripsi_progress_for_api($id_mahasiswa, $tahun_aktif);
        }

        $dash = $this->dashboard($id_mahasiswa);

        // Countdown sidang skripsi (sama logika dengan Halaman_utama mahasiswa)
        $jadwal_sidang_cd = null;
        $jadwal_sidang_timestamp = null;
        if ($is_skripsi && $tahun_aktif) {
            $sk_cd = $this->CI->Skripsi_model->get_by_mahasiswa($id_mahasiswa, $tahun_aktif->id);
            if ($sk_cd) {
                $jadwal_list_cd = $this->CI->Jadwal_skripsi_model->get_by_mahasiswa($sk_cd->id);
                $upcoming = null;
                $upcoming_ts = null;
                foreach ($jadwal_list_cd as $j) {
                    $tanggal = isset($j->tanggal_ujian) ? $j->tanggal_ujian : (isset($j->tanggal) ? $j->tanggal : null);
                    if ($tanggal && isset($j->jam_mulai)) {
                        $ts = strtotime($tanggal . ' ' . $j->jam_mulai);
                        if ($ts >= time() && ($upcoming_ts === null || $ts < $upcoming_ts)) {
                            $upcoming = $j;
                            $upcoming_ts = $ts;
                        }
                    }
                }
                if ($upcoming) {
                    $jadwal_sidang_cd = $upcoming;
                    $jadwal_sidang_timestamp = $upcoming_ts;
                } elseif (!empty($jadwal_list_cd)) {
                    $j0 = $jadwal_list_cd[0];
                    $tanggal = isset($j0->tanggal_ujian) ? $j0->tanggal_ujian : (isset($j0->tanggal) ? $j0->tanggal : null);
                    if ($tanggal && isset($j0->jam_mulai)) {
                        $jadwal_sidang_cd = $j0;
                        $jadwal_sidang_timestamp = strtotime($tanggal . ' ' . $j0->jam_mulai);
                    }
                }
            }
        }

        return array(
            'success' => true,
            'tahun_aktif' => $this->to_array($tahun_aktif),
            'mahasiswa' => $this->to_array($mahasiswa),
            'is_registered_pkl' => $is_pkl,
            'is_registered_skripsi' => $is_skripsi,
            'semester_aktif' => $mahasiswa && $mahasiswa->semester_aktif ? (int) $mahasiswa->semester_aktif : 1,
            'kurikulum_info' => $this->to_array($kurikulum_info),
            'struktur_kurikulum' => $struktur_kurikulum,
            'pkl_progress' => $pkl_progress,
            'skripsi_progress' => $skripsi_progress,
            'tagihan' => isset($dash['tagihan']) ? $dash['tagihan'] : array(),
            'jadwal_sidang' => $this->to_array($jadwal_sidang_cd),
            'jadwal_sidang_timestamp' => $jadwal_sidang_timestamp,
        );
    }

    /**
     * Mirror private calculate_skripsi_progress di Halaman_utama (untuk konsistensi web vs app).
     */
    private function calculate_skripsi_progress_for_api($id_mahasiswa, $tahun_aktif) {
        if (!$id_mahasiswa || !$tahun_aktif) {
            return array('percentage' => 0, 'current_step' => 0, 'total_steps' => 10, 'steps' => array());
        }

        $this->CI->load->model('Skripsi_pengajuan_model');
        $this->CI->load->model('Skripsi_bimbingan_model');
        $this->CI->load->model('Skripsi_pendaftaran_sidang_model');
        $this->CI->load->model('Jadwal_skripsi_model');
        $this->CI->load->model('Skripsi_klaim_nilai_model');
        $this->CI->load->model('Skripsi_nilai_rekap_model');

        $steps = array();

        $skripsi = $this->CI->Skripsi_model->get_by_mahasiswa($id_mahasiswa, $tahun_aktif->id);
        $pengajuan = $this->CI->Skripsi_pengajuan_model->get_latest_by_mahasiswa($id_mahasiswa, $tahun_aktif->id);
        $bimbingan_list = $this->CI->Skripsi_bimbingan_model->get_by_mahasiswa($id_mahasiswa, $tahun_aktif->id);

        $pendaftaran = null;
        $jadwal_list = array();
        $klaim = null;
        $nilai_rekap = null;

        if ($skripsi) {
            $pendaftaran = $this->CI->Skripsi_pendaftaran_sidang_model->get_by_mahasiswa_skripsi_tahap($skripsi->id, 'verifikasi');
            if (!$pendaftaran && $this->CI->db->field_exists('tahap', 'skripsi_pendaftaran_sidang')) {
                $pendaftaran = $this->CI->Skripsi_pendaftaran_sidang_model->get_by_mahasiswa_skripsi($skripsi->id);
            }
            $jadwal_list = $this->CI->Jadwal_skripsi_model->get_by_mahasiswa($skripsi->id);
            $klaim = $this->CI->Skripsi_klaim_nilai_model->get_by_mahasiswa_skripsi($skripsi->id);
            if (!empty($jadwal_list)) {
                $jadwal = $jadwal_list[0];
                $nilai_rekap = $this->CI->Skripsi_nilai_rekap_model->get_by_jadwal($jadwal->id);
            }
        }

        $status_p1 = $pengajuan ? ($pengajuan->status_acc_judul_p1 ?? 'pending') : 'pending';
        $status_approve_revisi = $pengajuan ? ($pengajuan->status_approve_revisi ?? 'pending') : 'pending';
        $judul_siap_bimbingan = ($status_p1 === 'acc' || $status_approve_revisi === 'disetujui');

        $acc_layak_p1 = ($skripsi && isset($skripsi->acc_pembimbing_1) && (int) $skripsi->acc_pembimbing_1 === 1);
        $acc_layak_p2 = ($skripsi && isset($skripsi->acc_pembimbing_2) && (int) $skripsi->acc_pembimbing_2 === 1);
        $butuh_p2 = ($pengajuan && !empty($pengajuan->id_pembimbing_2_final));
        $acc_layak_p2_effective = $butuh_p2 ? $acc_layak_p2 : true;

        $s1 = (bool) $skripsi;
        $s2 = $s1 && (bool) $pengajuan;
        $s3 = $s2 && $judul_siap_bimbingan;
        $s4 = $s3 && !empty($bimbingan_list);
        $s5 = $s4 && $acc_layak_p1;
        $s6 = $s5 && $acc_layak_p2_effective;
        $s7 = $s6 && (bool) $pendaftaran;
        $s8 = $s7 && !empty($jadwal_list);
        $s9 = $s8 && (bool) $klaim;
        $s10 = $s9 && (bool) $nilai_rekap;

        $steps[] = array('name' => 'Terdaftar Skripsi', 'completed' => $s1);
        $steps[] = array('name' => 'Pengajuan Judul', 'completed' => $s2);
        $steps[] = array('name' => 'Judul Siap Bimbingan (ACC P1 / Revisi Disetujui)', 'completed' => $s3);
        $steps[] = array('name' => 'Bimbingan Aktif', 'completed' => $s4);
        $steps[] = array('name' => 'ACC Kelayakan Pembimbing 1', 'completed' => $s5);
        $steps[] = array('name' => 'ACC Kelayakan Pembimbing 2', 'completed' => $s6);
        $steps[] = array('name' => 'Pendaftaran Sidang', 'completed' => $s7);
        $steps[] = array('name' => 'Jadwal Sidang Terbit', 'completed' => $s8);
        $steps[] = array('name' => 'Klaim Nilai', 'completed' => $s9);
        $steps[] = array('name' => 'Nilai Final Terbit', 'completed' => $s10);

        $current_step = 0;
        foreach ($steps as $step) {
            if (!empty($step['completed'])) {
                $current_step++;
            } else {
                break;
            }
        }

        $total_steps = count($steps);
        $percentage = $total_steps > 0 ? ($current_step / $total_steps) * 100 : 0;

        return array(
            'percentage' => round($percentage, 1),
            'current_step' => $current_step,
            'total_steps' => $total_steps,
            'steps' => $steps,
        );
    }

    /* ----- mirror Jadwal.php (UTS / UAS + kehadiran) ----- */

    private function get_jadwal_uts_by_mahasiswa($id_mahasiswa, $kelas, $id_tahun_akademik) {
        $this->CI->db->select('jms.id_jadwal_uts, jms.sesi');
        $this->CI->db->from('jadwal_uts_mahasiswa_sesi jms');
        $this->CI->db->where('jms.id_mahasiswa', $id_mahasiswa);
        $mahasiswa_sesi = $this->CI->db->get()->result();

        if (empty($mahasiswa_sesi)) {
            $results = $this->CI->Jadwal_uts_model->get_by_kelas($kelas, $id_tahun_akademik);
            foreach ($results as $result) {
                $this->CI->db->where('id_jadwal_uts', $result->id);
                $this->CI->db->where('id_mahasiswa', $id_mahasiswa);
                $result->kehadiran = $this->CI->db->get('jadwal_uts_kehadiran')->row();
                if (!$result->kehadiran) {
                    $this->CI->db->insert('jadwal_uts_kehadiran', array(
                        'id_jadwal_uts' => $result->id,
                        'id_mahasiswa' => $id_mahasiswa,
                        'status_kehadiran' => 'tidak_hadir',
                        'created_at' => date('Y-m-d H:i:s'),
                    ));
                    $this->CI->db->where('id_jadwal_uts', $result->id);
                    $this->CI->db->where('id_mahasiswa', $id_mahasiswa);
                    $result->kehadiran = $this->CI->db->get('jadwal_uts_kehadiran')->row();
                }
            }
            return $results;
        }

        $id_jadwal_uts_list = array();
        foreach ($mahasiswa_sesi as $ms) {
            $id_jadwal_uts_list[] = $ms->id_jadwal_uts;
        }
        if (empty($id_jadwal_uts_list)) {
            return array();
        }

        $this->CI->db->select('jut.*, 
                          mk.kode_mk, mk.nama_mk, mk.sks,
                          d.nidn, d.nama_dosen,
                          rk.kode_ruang, rk.nama_ruang,
                          t.nama_tendik, t.nipy as nipy_tendik,
                          jut.qr_code_token');
        $this->CI->db->from('jadwal_uts jut');
        $this->CI->db->join('mapping_dosen_mk mdm', 'mdm.id = jut.id_mapping_dosen_mk', 'left');
        $this->CI->db->join('mata_kuliah_aktif mka', 'mka.id = mdm.id_mata_kuliah_aktif', 'left');
        $this->CI->db->join('kurikulum_detail kd', 'kd.id = mka.id_kurikulum_detail', 'left');
        $this->CI->db->join('mata_kuliah mk', 'mk.id = kd.id_mata_kuliah', 'left');
        $this->CI->db->join('dosen d', 'd.id = mdm.id_dosen', 'left');
        $this->CI->db->join('ruang_kelas rk', 'rk.id = jut.id_ruang_kelas', 'left');
        $this->CI->db->join('tendik t', 't.id = jut.id_tendik', 'left');
        $this->CI->db->where('jut.kelas', $kelas);
        $this->CI->db->where('jut.id_tahun_akademik', $id_tahun_akademik);
        $this->CI->db->where_in('jut.id', $id_jadwal_uts_list);
        $this->CI->db->order_by('jut.tanggal', 'ASC');
        $this->CI->db->order_by('jut.jam_mulai', 'ASC');
        $this->CI->db->order_by('jut.sesi', 'ASC');
        $results = $this->CI->db->get()->result();

        foreach ($results as $result) {
            $this->CI->db->where('id_jadwal_uts', $result->id);
            $this->CI->db->where('id_mahasiswa', $id_mahasiswa);
            $ms_data = $this->CI->db->get('jadwal_uts_mahasiswa_sesi')->row();
            if ($ms_data) {
                $result->sesi = $ms_data->sesi;
            }
            $result->pengawas = $this->CI->Jadwal_uts_model->get_pengawas($result->id);
            $this->CI->db->where('id_jadwal_uts', $result->id);
            $this->CI->db->where('id_mahasiswa', $id_mahasiswa);
            $result->kehadiran = $this->CI->db->get('jadwal_uts_kehadiran')->row();
            if (!$result->kehadiran) {
                $this->CI->db->insert('jadwal_uts_kehadiran', array(
                    'id_jadwal_uts' => $result->id,
                    'id_mahasiswa' => $id_mahasiswa,
                    'status_kehadiran' => 'tidak_hadir',
                    'created_at' => date('Y-m-d H:i:s'),
                ));
                $this->CI->db->where('id_jadwal_uts', $result->id);
                $this->CI->db->where('id_mahasiswa', $id_mahasiswa);
                $result->kehadiran = $this->CI->db->get('jadwal_uts_kehadiran')->row();
            }
        }
        return $results;
    }

    private function get_jadwal_uas_by_mahasiswa($id_mahasiswa, $kelas, $id_tahun_akademik) {
        $this->CI->db->select('jms.id_jadwal_uas, jms.sesi');
        $this->CI->db->from('jadwal_uas_mahasiswa_sesi jms');
        $this->CI->db->where('jms.id_mahasiswa', $id_mahasiswa);
        $mahasiswa_sesi = $this->CI->db->get()->result();

        if (empty($mahasiswa_sesi)) {
            $results = $this->CI->Jadwal_uas_model->get_by_kelas($kelas, $id_tahun_akademik);
            foreach ($results as $result) {
                $this->CI->db->where('id_jadwal_uas', $result->id);
                $this->CI->db->where('id_mahasiswa', $id_mahasiswa);
                $result->kehadiran = $this->CI->db->get('jadwal_uas_kehadiran')->row();
                if (!$result->kehadiran) {
                    $this->CI->db->insert('jadwal_uas_kehadiran', array(
                        'id_jadwal_uas' => $result->id,
                        'id_mahasiswa' => $id_mahasiswa,
                        'status_kehadiran' => 'tidak_hadir',
                        'created_at' => date('Y-m-d H:i:s'),
                    ));
                    $this->CI->db->where('id_jadwal_uas', $result->id);
                    $this->CI->db->where('id_mahasiswa', $id_mahasiswa);
                    $result->kehadiran = $this->CI->db->get('jadwal_uas_kehadiran')->row();
                }
            }
            return $results;
        }

        $id_jadwal_uas_list = array();
        foreach ($mahasiswa_sesi as $ms) {
            $id_jadwal_uas_list[] = $ms->id_jadwal_uas;
        }
        if (empty($id_jadwal_uas_list)) {
            return array();
        }

        $this->CI->db->select('jua.*, 
                          mk.kode_mk, mk.nama_mk, mk.sks,
                          d.nidn, d.nama_dosen,
                          rk.kode_ruang, rk.nama_ruang,
                          t.nama_tendik, t.nipy as nipy_tendik,
                          jua.qr_code_token');
        $this->CI->db->from('jadwal_uas jua');
        $this->CI->db->join('mapping_dosen_mk mdm', 'mdm.id = jua.id_mapping_dosen_mk', 'left');
        $this->CI->db->join('mata_kuliah_aktif mka', 'mka.id = mdm.id_mata_kuliah_aktif', 'left');
        $this->CI->db->join('kurikulum_detail kd', 'kd.id = mka.id_kurikulum_detail', 'left');
        $this->CI->db->join('mata_kuliah mk', 'mk.id = kd.id_mata_kuliah', 'left');
        $this->CI->db->join('dosen d', 'd.id = mdm.id_dosen', 'left');
        $this->CI->db->join('ruang_kelas rk', 'rk.id = jua.id_ruang_kelas', 'left');
        $this->CI->db->join('tendik t', 't.id = jua.id_tendik', 'left');
        $this->CI->db->where('jua.kelas', $kelas);
        $this->CI->db->where('jua.id_tahun_akademik', $id_tahun_akademik);
        $this->CI->db->where_in('jua.id', $id_jadwal_uas_list);
        $this->CI->db->order_by('jua.tanggal', 'ASC');
        $this->CI->db->order_by('jua.jam_mulai', 'ASC');
        $this->CI->db->order_by('jua.sesi', 'ASC');
        $results = $this->CI->db->get()->result();

        foreach ($results as $result) {
            $this->CI->db->where('id_jadwal_uas', $result->id);
            $this->CI->db->where('id_mahasiswa', $id_mahasiswa);
            $ms_data = $this->CI->db->get('jadwal_uas_mahasiswa_sesi')->row();
            if ($ms_data) {
                $result->sesi = $ms_data->sesi;
            }
            $result->pengawas = $this->CI->Jadwal_uas_model->get_pengawas($result->id);
            $this->CI->db->where('id_jadwal_uas', $result->id);
            $this->CI->db->where('id_mahasiswa', $id_mahasiswa);
            $result->kehadiran = $this->CI->db->get('jadwal_uas_kehadiran')->row();
            if (!$result->kehadiran) {
                $this->CI->db->insert('jadwal_uas_kehadiran', array(
                    'id_jadwal_uas' => $result->id,
                    'id_mahasiswa' => $id_mahasiswa,
                    'status_kehadiran' => 'tidak_hadir',
                    'created_at' => date('Y-m-d H:i:s'),
                ));
                $this->CI->db->where('id_jadwal_uas', $result->id);
                $this->CI->db->where('id_mahasiswa', $id_mahasiswa);
                $result->kehadiran = $this->CI->db->get('jadwal_uas_kehadiran')->row();
            }
        }
        return $results;
    }
}
