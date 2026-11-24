<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Proposal Helper
 * Perhitungan nilai dan progres Proposal TA (1 pembimbing, 2 penguji).
 */

/**
 * Nilai akhir Proposal TA memakai bobot dari database (tipe 'proposal').
 */
if (!function_exists('hitung_nilai_akhir_proposal')) {
    function hitung_nilai_akhir_proposal($nilai_bimbingan, $nilai_sidang) {
        $CI =& get_instance();
        $CI->load->model('Bobot_nilai_setting_model');
        return $CI->Bobot_nilai_setting_model->hitung_nilai_akhir_proposal($nilai_bimbingan, $nilai_sidang);
    }
}

/**
 * Nilai sidang = rata-rata nilai Penguji 1 dan Penguji 2.
 * Hanya nilai yang ada (> 0) yang dihitung.
 */
if (!function_exists('hitung_nilai_sidang_rata_proposal')) {
    function hitung_nilai_sidang_rata_proposal($nilai_penguji_1, $nilai_penguji_2) {
        $values = array();
        foreach (array($nilai_penguji_1, $nilai_penguji_2) as $v) {
            if ($v !== null && (float) $v > 0) {
                $values[] = (float) $v;
            }
        }
        if (empty($values)) {
            return 0.0;
        }
        return round(array_sum($values) / count($values), 2);
    }
}

/**
 * Konversi nilai angka ke grade huruf (setting tipe 'proposal').
 */
if (!function_exists('konversi_nilai_huruf_proposal')) {
    function konversi_nilai_huruf_proposal($nilai) {
        if ($nilai === null) {
            return null;
        }
        $CI =& get_instance();
        $CI->load->model('Grade_setting_model');
        return $CI->Grade_setting_model->konversi_nilai_ke_grade($nilai, 'proposal');
    }
}

/**
 * Nilai satu aspek dari butir penilaian berbobot.
 * Rumus: P = (Item1 x Bobot1 + Item2 x Bobot2 + ...) / 100
 * @param array $items [['nilai' => float, 'bobot' => float], ...]
 */
if (!function_exists('hitung_nilai_p_dari_items_proposal')) {
    function hitung_nilai_p_dari_items_proposal($items) {
        if (empty($items)) {
            return 0;
        }
        $total = 0;
        foreach ($items as $item) {
            $nilai = isset($item['nilai']) ? floatval($item['nilai']) : 0;
            $bobot = isset($item['bobot']) ? floatval($item['bobot']) : 0;
            $total += ($nilai * $bobot / 100);
        }
        return round($total, 2);
    }
}

if (!function_exists('format_nilai_proposal')) {
    function format_nilai_proposal($nilai) {
        return number_format((float) $nilai, 2, ',', '.');
    }
}

/**
 * Syarat layak sidang Proposal TA: pembimbing sudah ACC dan sudah memberi nilai.
 * @param object $proposal baris mahasiswa_proposal
 * @param object|null $nilai_bimbingan baris proposal_nilai_bimbingan
 */
if (!function_exists('is_layak_sidang_proposal')) {
    function is_layak_sidang_proposal($proposal, $nilai_bimbingan) {
        if (!$proposal) {
            return false;
        }
        if (isset($proposal->status) && $proposal->status === 'layak_sidang') {
            return true;
        }
        return !empty($proposal->acc_pembimbing) && $nilai_bimbingan !== null && $nilai_bimbingan !== false;
    }
}

/**
 * Judul Proposal TA terbaru (satu-satunya tempat logika ini ditulis).
 * Prioritas: perubahan judul yang di-ACC sekprodi > judul_revisi yang disetujui
 *            > judul_disetujui > judul_pengajuan > mahasiswa_proposal.judul_proposal
 * @return string|null
 */
if (!function_exists('get_judul_proposal_terbaru')) {
    function get_judul_proposal_terbaru($id_mahasiswa, $id_tahun_akademik = null) {
        $CI =& get_instance();
        $CI->load->model('Proposal_pengajuan_model');
        $CI->load->model('Proposal_perubahan_judul_model');
        $CI->load->model('Proposal_model');

        $pengajuan = $CI->Proposal_pengajuan_model->get_latest_by_mahasiswa($id_mahasiswa, $id_tahun_akademik);
        if ($pengajuan) {
            $acc = $CI->Proposal_perubahan_judul_model->get_latest_by_pengajuan_status($pengajuan->id, 'acc');
            if ($acc && !empty($acc->judul_baru)) {
                return $acc->judul_baru;
            }
            if (($pengajuan->status_approve_revisi ?? 'pending') === 'disetujui' && !empty($pengajuan->judul_revisi)) {
                return $pengajuan->judul_revisi;
            }
            if (!empty($pengajuan->judul_disetujui)) {
                return $pengajuan->judul_disetujui;
            }
            if (!empty($pengajuan->judul_pengajuan)) {
                return $pengajuan->judul_pengajuan;
            }
        }

        $proposal = $CI->Proposal_model->get_by_mahasiswa($id_mahasiswa, $id_tahun_akademik);
        if ($proposal && !empty($proposal->judul_proposal)) {
            return $proposal->judul_proposal;
        }
        return null;
    }
}

/**
 * Progres Proposal TA mahasiswa (9 langkah, sama dengan Skripsi).
 * @return array ['percentage','current_step','total_steps','steps'=>[['name','completed'],...]]
 */
if (!function_exists('calculate_proposal_progress')) {
    function calculate_proposal_progress($id_mahasiswa, $tahun_aktif) {
        $nama = array(
            'Terdaftar Proposal TA', 'Pengajuan Judul', 'Judul ACC Pembimbing', 'Bimbingan Aktif',
            'Layak Sidang', 'Pendaftaran Sidang', 'Sidang Selesai', 'Klaim Nilai', 'Selesai'
        );
        $total = count($nama);

        if (!$id_mahasiswa || !$tahun_aktif) {
            return array('percentage' => 0, 'current_step' => 0, 'total_steps' => $total, 'steps' => array());
        }

        $CI =& get_instance();
        $CI->load->model('Proposal_model');
        $CI->load->model('Proposal_pengajuan_model');
        $CI->load->model('Proposal_bimbingan_model');
        $CI->load->model('Proposal_pendaftaran_sidang_model');
        $CI->load->model('Jadwal_proposal_model');
        $CI->load->model('Proposal_klaim_nilai_model');
        $CI->load->model('Proposal_nilai_rekap_model');

        $done = array_fill(0, $total, false);

        $proposal = $CI->Proposal_model->get_by_mahasiswa($id_mahasiswa, $tahun_aktif->id);
        $done[0] = ($proposal !== null);

        $pengajuan = $done[0] ? $CI->Proposal_pengajuan_model->get_latest_by_mahasiswa($id_mahasiswa, $tahun_aktif->id) : null;
        $done[1] = ($pengajuan !== null);

        if ($done[1]) {
            $acc = (($pengajuan->status_acc_judul ?? 'pending') === 'acc')
                || (($pengajuan->status_approve_revisi ?? 'pending') === 'disetujui');
            $done[2] = $acc;
        }
        if ($done[2]) {
            $bimbingan = $CI->Proposal_bimbingan_model->get_by_mahasiswa($id_mahasiswa, $tahun_aktif->id);
            $done[3] = !empty($bimbingan);
        }
        if ($done[3]) {
            $done[4] = !empty($proposal->acc_pembimbing);
        }
        if ($done[4]) {
            $done[5] = ($CI->Proposal_pendaftaran_sidang_model->get_by_mahasiswa_proposal($proposal->id) !== null);
        }
        $jadwal_list = array();
        if ($done[5]) {
            $jadwal_list = $CI->Jadwal_proposal_model->get_by_mahasiswa($proposal->id);
            $done[6] = !empty($jadwal_list);
        }
        if ($done[6]) {
            $done[7] = ($CI->Proposal_klaim_nilai_model->get_by_mahasiswa_proposal($proposal->id) !== null);
        }
        if ($done[7]) {
            $done[8] = ($CI->Proposal_nilai_rekap_model->get_by_jadwal($jadwal_list[0]->id) !== null);
        }

        $steps = array();
        $current = 0;
        foreach ($nama as $i => $n) {
            $steps[] = array('name' => $n, 'completed' => $done[$i]);
            if ($done[$i]) {
                $current = $i + 1;
            }
        }

        return array(
            'percentage' => round(($current / $total) * 100, 1),
            'current_step' => $current,
            'total_steps' => $total,
            'steps' => $steps
        );
    }
}

/**
 * Konfigurasi pagination Bootstrap 4 (AdminLTE) untuk daftar Proposal TA.
 * @param string $base_url  mis. site_url('Proposal_pengajuan/review')
 * @param int    $total_rows
 * @param int    $per_page
 * @param array  $query     parameter GET yang dipertahankan (nilai kosong dibuang)
 * @return array config untuk $this->pagination->initialize()
 */
if (!function_exists('proposal_pagination_config')) {
    function proposal_pagination_config($base_url, $total_rows, $per_page, $query = array()) {
        $config = array(
            'base_url' => $base_url,
            'total_rows' => $total_rows,
            'per_page' => $per_page,
            'uri_segment' => 3,
            'use_page_numbers' => TRUE,
            'first_link' => '&laquo; Awal',
            'last_link' => 'Akhir &raquo;',
            'next_link' => '&gt;',
            'prev_link' => '&lt;',
            'full_tag_open' => '<ul class="pagination pagination-sm m-0 float-right">',
            'full_tag_close' => '</ul>',
            'first_tag_open' => '<li class="page-item">', 'first_tag_close' => '</li>',
            'last_tag_open' => '<li class="page-item">', 'last_tag_close' => '</li>',
            'next_tag_open' => '<li class="page-item">', 'next_tag_close' => '</li>',
            'prev_tag_open' => '<li class="page-item">', 'prev_tag_close' => '</li>',
            'cur_tag_open' => '<li class="page-item active"><span class="page-link">', 'cur_tag_close' => '</span></li>',
            'num_tag_open' => '<li class="page-item">', 'num_tag_close' => '</li>',
            'attributes' => array('class' => 'page-link'),
        );
        $query = array_filter($query, function ($v) { return $v !== null && $v !== '' && $v !== false; });
        $qs = http_build_query($query);
        if ($qs !== '') {
            $config['suffix'] = '?' . $qs;
            $config['first_url'] = $base_url . '?' . $qs;
        }
        return $config;
    }
}

/**
 * Badge status standar (pengajuan, ACC judul, pendaftaran, klaim, dst).
 */
if (!function_exists('proposal_status_badge')) {
    function proposal_status_badge($status) {
        $map = array(
            'pending'    => array('warning',   'Menunggu'),
            'menunggu'   => array('warning',   'Menunggu'),
            'disetujui'  => array('success',   'Disetujui'),
            'acc'        => array('success',   'ACC'),
            'revisi'     => array('info',      'Perlu Revisi'),
            'ditolak'    => array('danger',    'Ditolak'),
            'selesai'    => array('secondary', 'Selesai'),
        );
        $s = strtolower((string) $status);
        $cls = isset($map[$s]) ? $map[$s][0] : 'secondary';
        $txt = isset($map[$s]) ? $map[$s][1] : ($s === '' ? '-' : ucfirst($s));
        return '<span class="badge badge-' . $cls . '">' . htmlspecialchars($txt) . '</span>';
    }
}

/**
 * Kirim notifikasi in-app bila fungsi notifikasi tersedia (tidak fatal bila gagal).
 */
if (!function_exists('proposal_notify')) {
    function proposal_notify($role, $ref_id, $title, $message, $url) {
        if (!function_exists('send_in_app_notification_by_ref') || empty($ref_id)) {
            return;
        }
        try {
            send_in_app_notification_by_ref($role, (int) $ref_id, $title, $message, $url);
        } catch (Throwable $e) {
            log_message('error', 'proposal_notify gagal: ' . $e->getMessage());
        }
    }
}

/**
 * Topik bimbingan Proposal TA (pilihan seragam untuk mahasiswa).
 * @return string[]
 */
if (!function_exists('proposal_topik_bimbingan_pilihan')) {
    function proposal_topik_bimbingan_pilihan() {
        return array(
            'Bab 1 - Pendahuluan',
            'Bab 2 - Tinjauan Pustaka',
            'Bab 3 - Metodologi',
            'Draf Final Proposal',
        );
    }
}

if (!function_exists('valid_proposal_topik_bimbingan_label')) {
    function valid_proposal_topik_bimbingan_label($label) {
        $label = is_string($label) ? trim($label) : '';
        return $label !== '' && in_array($label, proposal_topik_bimbingan_pilihan(), true);
    }
}

/**
 * Status alur satu topik bimbingan berdasarkan log terakhir.
 * @param object $bimbingan baris proposal_bimbingan
 * @param object|null $latest_log log terakhir (role_pengguna, status_revisi)
 * @return array ['key','label','class']
 */
if (!function_exists('proposal_bimbingan_workflow')) {
    function proposal_bimbingan_workflow($bimbingan, $latest_log) {
        if (($bimbingan->status ?? '') === 'closed') {
            return array('key' => 'closed', 'label' => 'Bimbingan Ditutup', 'class' => 'secondary');
        }
        if ($latest_log && ($latest_log->role_pengguna ?? '') === 'dosen') {
            if (($latest_log->status_revisi ?? '') === 'revisi') {
                return array('key' => 'need_revision', 'label' => 'Perlu Revisi Mahasiswa', 'class' => 'warning');
            }
            if (($latest_log->status_revisi ?? '') === 'acc') {
                return array('key' => 'approved', 'label' => 'ACC Pembimbing', 'class' => 'success');
            }
        }
        return array('key' => 'waiting_review', 'label' => 'Menunggu Review Dosen', 'class' => 'info');
    }
}
