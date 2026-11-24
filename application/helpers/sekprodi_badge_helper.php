<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Badge angka menu Sekprodi:
 * - Hanya menampilkan item yang BELUM DILIHAT (baru setelah kunjungan terakhir)
 * - Saat menu/halaman antrean dibuka → ack → angka hilang
 * - Baseline saat login: antrean lama tidak dihitung sebagai "belum dilihat"
 */

if (!function_exists('sekprodi_badge_keys')) {
    function sekprodi_badge_keys() {
        return array(
            'proposal_judul',
            'proposal_perubahan',
            'proposal_sidang',
            'proposal_klaim',
            'skripsi_judul',
            'skripsi_perubahan',
            'skripsi_sidang',
            'skripsi_klaim',
            'pkl_judul',
            'pkl_perubahan',
            'pkl_sidang',
            'pkl_klaim',
            'permintaan_surat',
            'pkm_verifikasi',
            'pkm_proposal_final',
            'skpi_verifikasi',
        );
    }
}

if (!function_exists('sekprodi_badge_table')) {
    function sekprodi_badge_table($candidates) {
        $CI =& get_instance();
        foreach ((array) $candidates as $t) {
            if ($t && $CI->db->table_exists($t)) {
                return $t;
            }
        }
        return null;
    }
}

if (!function_exists('sekprodi_badge_ack_all')) {
    function sekprodi_badge_ack_all() {
        $CI =& get_instance();
        $ack = $CI->session->userdata('sekprodi_badge_ack');
        return is_array($ack) ? $ack : array();
    }
}

if (!function_exists('sekprodi_badge_ack')) {
    /** Tandai menu sudah dilihat → badge hilang sampai ada item baru. */
    function sekprodi_badge_ack($key) {
        $CI =& get_instance();
        $ack = sekprodi_badge_ack_all();
        $ack[$key] = date('Y-m-d H:i:s');
        $CI->session->set_userdata('sekprodi_badge_ack', $ack);
    }
}

if (!function_exists('sekprodi_badge_seed_baseline')) {
    /**
     * Saat login / pertama kali: tandai semua menu "sudah dilihat sampai sekarang"
     * agar antrean lama tidak memunculkan angka.
     */
    function sekprodi_badge_seed_baseline($force = false) {
        $CI =& get_instance();
        $ack = sekprodi_badge_ack_all();
        $now = date('Y-m-d H:i:s');
        $changed = false;
        foreach (sekprodi_badge_keys() as $key) {
            if ($force || empty($ack[$key])) {
                $ack[$key] = $now;
                $changed = true;
            }
        }
        if ($changed) {
            $CI->session->set_userdata('sekprodi_badge_ack', $ack);
        }
        return $ack;
    }
}

if (!function_exists('sekprodi_badge_ack_from_controller')) {
    function sekprodi_badge_ack_from_controller($controller, $method = '') {
        $map = array(
            'Proposal_pengajuan' => 'proposal_judul',
            'Proposal_perubahan_judul_sekprodi' => 'proposal_perubahan',
            'Proposal_verifikasi_berkas_sidang' => 'proposal_sidang',
            'Proposal_verifikasi_klaim' => 'proposal_klaim',
            'Skripsi_plot_pembimbing' => 'skripsi_judul',
            'Skripsi_perubahan_judul_sekprodi' => 'skripsi_perubahan',
            'Skripsi_verifikasi_berkas_sidang' => 'skripsi_sidang',
            'Skripsi_verifikasi_klaim' => 'skripsi_klaim',
            'Pkl_tempat_sekprodi' => 'pkl_judul',
            'Pkl_perubahan_judul_sekprodi' => 'pkl_perubahan',
            'Pkl_verifikasi_berkas_sidang' => 'pkl_sidang',
            'Pkl_verifikasi_klaim' => 'pkl_klaim',
            'Permintaan_surat_sekprodi' => 'permintaan_surat',
            'Pkm_verifikasi' => 'pkm_verifikasi',
            'Pkm_verifikasi_proposal' => 'pkm_proposal_final',
            'Skpi_verifikasi' => 'skpi_verifikasi',
            'Chat' => 'chat',
        );

        // Sekprodi review judul skripsi lewat Skripsi_pengajuan/review
        if ($controller === 'Skripsi_pengajuan' && in_array($method, array('review', 'detail', 'approve', 'reject'), true)) {
            sekprodi_badge_ack('skripsi_judul');
            return;
        }
        // Proposal: hanya halaman antrean sekprodi (bukan form mahasiswa)
        if ($controller === 'Proposal_pengajuan' && !in_array($method, array('review', 'detail', 'index', 'approve', 'reject'), true) && $method !== '') {
            return;
        }
        if (isset($map[$controller])) {
            sekprodi_badge_ack($map[$controller]);
        }
    }
}

if (!function_exists('sekprodi_badge_apply_since')) {
    function sekprodi_badge_apply_since($table, $col, $since) {
        if (!$since || !$table || !$col) {
            return;
        }
        $CI =& get_instance();
        if (!$CI->db->field_exists($col, $table)) {
            return;
        }
        $CI->db->where($col . ' >', $since);
    }
}

if (!function_exists('sekprodi_badge_count_raw')) {
    /**
     * Hitung pending yang belum dilihat ($since = waktu terakhir menu dibuka).
     */
    function sekprodi_badge_count_raw($key, $since = null) {
        $CI =& get_instance();
        $n = 0;

        switch ($key) {
            case 'proposal_judul':
                $t = sekprodi_badge_table(array('proposal_pengajuan', 'pta_pengajuan'));
                if ($t) {
                    $CI->db->from($t);
                    $CI->db->where('status', 'pending');
                    sekprodi_badge_apply_since($t, 'created_at', $since);
                    $n = (int) $CI->db->count_all_results();
                }
                break;

            case 'proposal_perubahan':
                $t = sekprodi_badge_table(array('proposal_perubahan_judul', 'pta_perubahan_judul'));
                if ($t) {
                    $CI->db->from($t);
                    $CI->db->where('status_approve_sekprodi', 'pending');
                    sekprodi_badge_apply_since($t, 'created_at', $since);
                    $n = (int) $CI->db->count_all_results();
                }
                break;

            case 'proposal_sidang':
                $t = sekprodi_badge_table(array('proposal_pendaftaran_sidang', 'pta_pendaftaran_sidang'));
                if ($t) {
                    $CI->db->from($t);
                    $CI->db->where_in('status', array('menunggu', 'pending'));
                    sekprodi_badge_apply_since($t, 'created_at', $since);
                    $n = (int) $CI->db->count_all_results();
                }
                break;

            case 'proposal_klaim':
                $t = sekprodi_badge_table(array('proposal_klaim_nilai', 'pta_klaim_nilai'));
                if ($t) {
                    $CI->db->from($t);
                    $CI->db->where_in('status', array('menunggu', 'pending'));
                    sekprodi_badge_apply_since($t, 'created_at', $since);
                    $n = (int) $CI->db->count_all_results();
                }
                break;

            case 'skripsi_perubahan':
                if ($CI->db->table_exists('skripsi_perubahan_judul')) {
                    $CI->db->from('skripsi_perubahan_judul');
                    $CI->db->where('status_approve_sekprodi', 'pending');
                    sekprodi_badge_apply_since('skripsi_perubahan_judul', 'created_at', $since);
                    $n = (int) $CI->db->count_all_results();
                }
                break;

            case 'skripsi_sidang':
                if ($CI->db->table_exists('skripsi_pendaftaran_sidang')) {
                    $CI->db->from('skripsi_pendaftaran_sidang');
                    $CI->db->where_in('status', array('menunggu', 'pending'));
                    sekprodi_badge_apply_since('skripsi_pendaftaran_sidang', 'created_at', $since);
                    $n = (int) $CI->db->count_all_results();
                }
                break;

            case 'skripsi_klaim':
                if ($CI->db->table_exists('skripsi_klaim_nilai')) {
                    $CI->db->from('skripsi_klaim_nilai');
                    $CI->db->where_in('status', array('menunggu', 'pending'));
                    sekprodi_badge_apply_since('skripsi_klaim_nilai', 'created_at', $since);
                    $n = (int) $CI->db->count_all_results();
                }
                break;

            case 'skripsi_judul':
                if ($CI->db->table_exists('skripsi_pengajuan')) {
                    $CI->db->from('skripsi_pengajuan');
                    $CI->db->where('status', 'pending');
                    sekprodi_badge_apply_since('skripsi_pengajuan', 'created_at', $since);
                    $n = (int) $CI->db->count_all_results();
                }
                break;

            case 'pkl_perubahan':
                if ($CI->db->table_exists('pkl_pengajuan') && $CI->db->field_exists('status_approve_revisi', 'pkl_pengajuan')) {
                    $CI->db->from('pkl_pengajuan');
                    $CI->db->where('status_approve_revisi', 'pending');
                    $col = $CI->db->field_exists('updated_at', 'pkl_pengajuan') ? 'updated_at' : 'created_at';
                    sekprodi_badge_apply_since('pkl_pengajuan', $col, $since);
                    $n = (int) $CI->db->count_all_results();
                }
                break;

            case 'pkl_sidang':
                if ($CI->db->table_exists('pkl_pendaftaran_sidang')) {
                    $CI->db->from('pkl_pendaftaran_sidang');
                    $CI->db->where_in('status', array('menunggu', 'pending'));
                    sekprodi_badge_apply_since('pkl_pendaftaran_sidang', 'created_at', $since);
                    $n = (int) $CI->db->count_all_results();
                }
                break;

            case 'pkl_klaim':
                if ($CI->db->table_exists('pkl_klaim_nilai')) {
                    $CI->db->from('pkl_klaim_nilai');
                    $CI->db->where_in('status', array('menunggu', 'pending'));
                    sekprodi_badge_apply_since('pkl_klaim_nilai', 'created_at', $since);
                    $n = (int) $CI->db->count_all_results();
                }
                break;

            case 'pkl_judul':
                if ($CI->db->table_exists('pkl_tempat')) {
                    $CI->db->from('pkl_tempat');
                    $CI->db->where('status', 'selesai');
                    $CI->db->where('status_acc_tempat', 'pending');
                    $col = $CI->db->field_exists('updated_at', 'pkl_tempat') ? 'updated_at' : 'created_at';
                    sekprodi_badge_apply_since('pkl_tempat', $col, $since);
                    $n = (int) $CI->db->count_all_results();
                }
                break;

            case 'permintaan_surat':
                if ($CI->db->table_exists('permintaan_surat')) {
                    $CI->db->from('permintaan_surat');
                    // Hanya yang belum dibuka/diproses
                    $CI->db->where('status', 'pending');
                    sekprodi_badge_apply_since('permintaan_surat', 'created_at', $since);
                    $n = (int) $CI->db->count_all_results();
                }
                break;

            case 'pkm_verifikasi':
                if ($CI->db->table_exists('pkm_pengajuan')) {
                    $CI->db->from('pkm_pengajuan');
                    $CI->db->where('status_verifikasi_sekprodi', 'pending');
                    sekprodi_badge_apply_since('pkm_pengajuan', 'created_at', $since);
                    $n = (int) $CI->db->count_all_results();
                }
                break;

            case 'pkm_proposal_final':
                if ($CI->db->table_exists('pkm_pengajuan') && $CI->db->field_exists('proposal_final_aktif', 'pkm_pengajuan')) {
                    $CI->db->from('pkm_pengajuan');
                    $CI->db->where('proposal_final_aktif', 1);
                    $CI->db->where('status_acc_proposal', 'pending');
                    $col = $CI->db->field_exists('updated_at', 'pkm_pengajuan') ? 'updated_at' : 'created_at';
                    sekprodi_badge_apply_since('pkm_pengajuan', $col, $since);
                    $n = (int) $CI->db->count_all_results();
                }
                break;

            case 'skpi_verifikasi':
                $t_skpi = sekprodi_badge_table(array('skpi_upload', 'skpi_pengajuan'));
                if ($t_skpi) {
                    $CI->db->from($t_skpi);
                    if ($CI->db->field_exists('status', $t_skpi)) {
                        $CI->db->where_in('status', array('pending', 'menunggu', 'diajukan'));
                    }
                    sekprodi_badge_apply_since($t_skpi, 'created_at', $since);
                    $n = (int) $CI->db->count_all_results();
                }
                break;

            case 'chat':
                if ($CI->session->userdata('user_id') && $CI->db->table_exists('chat_messages')) {
                    $CI->load->model('Chat_model');
                    $n = (int) $CI->Chat_model->count_unread((int) $CI->session->userdata('user_id'));
                }
                break;
        }

        return $n;
    }
}

if (!function_exists('sekprodi_badge_count')) {
    /**
     * Jumlah badge: hanya item baru setelah terakhir dilihat.
     * Jika belum ada baseline ack → seed dulu (antrean lama = sudah dianggap dilihat).
     */
    function sekprodi_badge_count($key) {
        if ($key === 'chat') {
            return sekprodi_badge_count_raw('chat');
        }

        $ack = sekprodi_badge_ack_all();
        if (empty($ack[$key])) {
            // Baseline: jangan tampilkan backlog lama sebagai "belum dilihat"
            sekprodi_badge_ack($key);
            return 0;
        }

        return sekprodi_badge_count_raw($key, $ack[$key]);
    }
}

if (!function_exists('sekprodi_badge_html')) {
    function sekprodi_badge_html($key) {
        $n = (int) sekprodi_badge_count($key);
        if ($n <= 0) {
            return '';
        }
        if ($n > 99) {
            $n = '99+';
        }
        return ' <span class="badge badge-danger right siap-menu-badge">' . $n . '</span>';
    }
}

if (!function_exists('sekprodi_badge_sum_html')) {
    function sekprodi_badge_sum_html($keys) {
        $total = 0;
        foreach ((array) $keys as $k) {
            $total += (int) sekprodi_badge_count($k);
        }
        if ($total <= 0) {
            return '';
        }
        if ($total > 99) {
            $total = '99+';
        }
        return ' <span class="badge badge-danger right siap-menu-badge">' . $total . '</span>';
    }
}

if (!function_exists('notify_sekprodi')) {
    /**
     * Kirim notifikasi + trigger popup ke semua user role sekprodi.
     */
    function notify_sekprodi($judul, $pesan, $link = null, $jenis = 'sekprodi') {
        if (!function_exists('send_in_app_notification_by_role')) {
            $CI =& get_instance();
            $CI->load->helper('notifikasi');
        }
        return send_in_app_notification_by_role('sekprodi', $judul, $pesan, $link, array(), $jenis);
    }
}
