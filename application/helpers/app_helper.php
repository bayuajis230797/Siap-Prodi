<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * App Helper
 * Helper untuk aplikasi (kop surat, logo, dll)
 */

if (!function_exists('get_app_settings')) {
    /**
     * Ambil setting aplikasi
     */
    function get_app_settings() {
        $CI =& get_instance();
        $CI->load->model('App_settings_model');
        return $CI->App_settings_model->get_settings();
    }
}

if (!function_exists('get_kop_header')) {
    /**
     * Generate kop header untuk PDF (menggunakan gambar kop surat)
     * @return string HTML kop header
     */
    function get_kop_header() {
        $settings = get_app_settings();
        
        // Jika ada kop surat (gambar), gunakan itu
        if (!empty($settings->kop_surat)) {
            $kop_path = str_replace('\\', '/', $settings->kop_surat);
            $full_path = FCPATH . str_replace('/', DIRECTORY_SEPARATOR, $kop_path);
            
            // Cek path alternatif
            if (!file_exists($full_path)) {
                $alt_path = FCPATH . 'uploads' . DIRECTORY_SEPARATOR . 'kop_surat' . DIRECTORY_SEPARATOR . 'kop_surat.jpg';
                if (file_exists($alt_path)) {
                    $full_path = $alt_path;
                    $kop_path = 'uploads/kop_surat/kop_surat.jpg';
                }
            }
            
            if (file_exists($full_path)) {
                // Untuk PDF, gunakan base64 encoded image agar lebih reliable
                $image_data = file_get_contents($full_path);
                $image_base64 = base64_encode($image_data);
                $image_info = getimagesize($full_path);
                $mime_type = $image_info['mime'];
                
                return '<div style="text-align: center; margin-bottom: 20px;"><img src="data:' . $mime_type . ';base64,' . $image_base64 . '" alt="Kop Surat" style="max-width: 100%; height: auto;"></div>';
            }
        }
        
        // Fallback: Default kop header jika tidak ada gambar
        $logo = '';
        if ($settings->logo_kampus && file_exists(FCPATH . $settings->logo_kampus)) {
            $logo = '<img src="' . base_url($settings->logo_kampus) . '" alt="Logo" style="max-height: 80px;">';
        }
        
        $kop = '<table width="100%" style="border-bottom: 2px solid #000; margin-bottom: 20px;">';
        $kop .= '<tr>';
        if ($logo) {
            $kop .= '<td width="20%" style="vertical-align: middle; padding: 10px;">' . $logo . '</td>';
        }
        $kop .= '<td width="' . ($logo ? '80%' : '100%') . '" style="vertical-align: middle; padding: 10px; text-align: center;">';
        $kop .= '<h3 style="margin: 0; font-weight: bold;">' . htmlspecialchars($settings->nama_kampus) . '</h3>';
        $kop .= '<h4 style="margin: 5px 0; font-weight: normal;">' . htmlspecialchars($settings->nama_prodi) . '</h4>';
        $kop .= '</td>';
        $kop .= '</tr>';
        $kop .= '</table>';
        
        return $kop;
    }
}

if (!function_exists('get_kop_surat')) {
    /**
     * Ambil path kop surat (gambar)
     */
    function get_kop_surat() {
        $settings = get_app_settings();
        if ($settings->kop_surat) {
            $kop_path = str_replace('\\', '/', $settings->kop_surat);
            $full_path = FCPATH . str_replace('/', DIRECTORY_SEPARATOR, $kop_path);
            if (file_exists($full_path)) {
                return base_url($kop_path);
            }
        }
        return null;
    }
}

if (!function_exists('get_nama_kampus')) {
    /**
     * Ambil nama kampus
     */
    function get_nama_kampus() {
        $settings = get_app_settings();
        return $settings->nama_kampus;
    }
}

if (!function_exists('get_nama_prodi')) {
    /**
     * Ambil nama program studi
     */
    function get_nama_prodi() {
        $settings = get_app_settings();
        return $settings->nama_prodi;
    }
}

if (!function_exists('get_logo_kampus')) {
    /**
     * Ambil path logo kampus
     */
    function get_logo_kampus() {
        $settings = get_app_settings();
        if (!$settings || !$settings->logo_kampus) {
            return null;
        }
        
        // Normalisasi path dari database - ganti backslash dengan forward slash
        $logo_db_path = str_replace('\\', '/', trim($settings->logo_kampus));
        $logo_db_path = ltrim($logo_db_path, '/');
        
        // Array path yang akan dicoba (dalam urutan prioritas)
        $paths_to_check = array();
        
        // 1. Path langsung dari database
        if (!empty($logo_db_path)) {
            $paths_to_check[] = $logo_db_path;
        }
        
        // 2. Path dengan uploads/logo/ jika belum ada prefix uploads/
        if (strpos($logo_db_path, 'uploads/') !== 0 && strpos($logo_db_path, 'uploads\\') !== 0) {
            $filename = basename($logo_db_path);
            if (!empty($filename)) {
                $paths_to_check[] = 'uploads/logo/' . $filename;
            }
        }
        
        // 3. Cek berbagai variasi path
        $filename = basename($logo_db_path);
        if (!empty($filename)) {
            // Coba beberapa format
            $variations = array(
                'uploads/logo/' . $filename,
                str_replace('\\', '/', $logo_db_path)
            );
            foreach ($variations as $var) {
                if (!in_array($var, $paths_to_check)) {
                    $paths_to_check[] = $var;
                }
            }
        }
        
        // Coba setiap path
        foreach ($paths_to_check as $relative_path) {
            // Normalisasi path untuk URL (selalu forward slash)
            $relative_path = str_replace('\\', '/', $relative_path);
            $relative_path = str_replace('//', '/', $relative_path);
            $relative_path = ltrim($relative_path, '/');
            
            // Convert ke full path untuk cek file exists
            $full_path = FCPATH . str_replace('/', DIRECTORY_SEPARATOR, $relative_path);
            
            // Cek file exists dan readable
            if (file_exists($full_path) && is_file($full_path) && is_readable($full_path)) {
                // Return URL dengan path yang benar menggunakan base_url
                // base_url() sudah menambahkan trailing slash, jadi langsung tambahkan path
                return base_url($relative_path);
            }
        }
        
        return null;
    }
}

if (!function_exists('skripsi_topik_bimbingan_pilihan')) {
    /**
     * Daftar topik bimbingan skripsi (seragam untuk mahasiswa).
     *
     * @return array
     */
    function skripsi_topik_bimbingan_pilihan() {
        return array(
            'Proposal Skripsi',
            'Laporan Skripsi BAB 1 - 3',
            'Laporan Skripsi BAB 4 - 5',
            'Laporan Skripsi BAB 4 - 6',
            'Bimbingan Program',
            'Draf Final Laporan Skripsi',
        );
    }
}

if (!function_exists('valid_skripsi_topik_bimbingan_label')) {
    /**
     * @param string $label
     * @return bool
     */
    function valid_skripsi_topik_bimbingan_label($label) {
        $label = is_string($label) ? trim($label) : '';
        return $label !== '' && in_array($label, skripsi_topik_bimbingan_pilihan(), true);
    }
}

if (!function_exists('pkl_topik_bimbingan_pilihan')) {
    /**
     * Daftar topik bimbingan laporan PKL (seragam untuk mahasiswa).
     *
     * @return array
     */
    function pkl_topik_bimbingan_pilihan() {
        return array(
            'Bab 1 - 3',
            'Bab 4 - 5',
            'Draf Final',
        );
    }
}

if (!function_exists('valid_pkl_topik_bimbingan_label')) {
    /**
     * @param string $label
     * @return bool
     */
    function valid_pkl_topik_bimbingan_label($label) {
        $label = is_string($label) ? trim($label) : '';
        return $label !== '' && in_array($label, pkl_topik_bimbingan_pilihan(), true);
    }
}

if (!function_exists('hari_dari_tanggal')) {
    /**
     * Nama hari dalam Bahasa Indonesia dari string tanggal (Y-m-d atau format strtotime).
     *
     * @param string|null $tanggal
     * @return string
     */
    function hari_dari_tanggal($tanggal) {
        if ($tanggal === null || $tanggal === '' || $tanggal === '-') {
            return '-';
        }
        $ts = strtotime($tanggal);
        if ($ts === false) {
            return '-';
        }
        $hari = array(
            1 => 'Senin',
            2 => 'Selasa',
            3 => 'Rabu',
            4 => 'Kamis',
            5 => 'Jumat',
            6 => 'Sabtu',
            7 => 'Minggu',
        );
        $n = (int) date('N', $ts);
        return isset($hari[$n]) ? $hari[$n] : '-';
    }
}

if (!function_exists('jadwal_ujian_times_overlap')) {
    function jadwal_ujian_times_overlap($jam_mulai_a, $jam_selesai_a, $jam_mulai_b, $jam_selesai_b) {
        if (!$jam_mulai_a || !$jam_selesai_a || !$jam_mulai_b || !$jam_selesai_b) {
            return false;
        }
        $start_a = strtotime($jam_mulai_a);
        $end_a = strtotime($jam_selesai_a);
        $start_b = strtotime($jam_mulai_b);
        $end_b = strtotime($jam_selesai_b);
        if ($start_a === false || $end_a === false || $start_b === false || $end_b === false) {
            return false;
        }
        return ($start_a < $end_b) && ($end_a > $start_b);
    }
}

if (!function_exists('jadwal_ujian_extract_pengawas_keys')) {
    function jadwal_ujian_extract_pengawas_keys($jadwal) {
        $keys = array();
        if (!empty($jadwal->pengawas) && is_array($jadwal->pengawas)) {
            foreach ($jadwal->pengawas as $p) {
                if (!empty($p->id_dosen)) {
                    $keys[] = 'dosen:' . (int) $p->id_dosen;
                } elseif (!empty($p->id_dosen_full)) {
                    $keys[] = 'dosen:' . (int) $p->id_dosen_full;
                }
                if (!empty($p->id_tendik)) {
                    $keys[] = 'tendik:' . (int) $p->id_tendik;
                } elseif (!empty($p->id_tendik_full)) {
                    $keys[] = 'tendik:' . (int) $p->id_tendik_full;
                }
            }
        }
        if (!empty($jadwal->id_tendik)) {
            $keys[] = 'tendik:' . (int) $jadwal->id_tendik;
        }
        return array_values(array_unique($keys));
    }
}

if (!function_exists('jadwal_ujian_build_pengawas_bentrok_map')) {
    function jadwal_ujian_build_pengawas_bentrok_map($jadwal_list) {
        $map = array();
        if (empty($jadwal_list) || !is_array($jadwal_list)) {
            return $map;
        }

        foreach ($jadwal_list as $jadwal) {
            $map[(int) $jadwal->id] = array(
                'bentrok' => false,
                'keterangan' => array(),
                'keterangan_str' => '',
            );
        }

        $count = count($jadwal_list);
        for ($i = 0; $i < $count; $i++) {
            for ($j = $i + 1; $j < $count; $j++) {
                $a = $jadwal_list[$i];
                $b = $jadwal_list[$j];
                if (empty($a->tanggal) || $a->tanggal !== $b->tanggal) {
                    continue;
                }
                if (!jadwal_ujian_times_overlap($a->jam_mulai, $a->jam_selesai, $b->jam_mulai, $b->jam_selesai)) {
                    continue;
                }
                $shared = array_intersect(
                    jadwal_ujian_extract_pengawas_keys($a),
                    jadwal_ujian_extract_pengawas_keys($b)
                );
                if (empty($shared)) {
                    continue;
                }

                $map[(int) $a->id]['bentrok'] = true;
                $map[(int) $b->id]['bentrok'] = true;
                $map[(int) $a->id]['keterangan'][] = 'Pengawas bentrok dengan kelas ' . ($b->kelas ?? '-') . ' (' . ($b->kode_mk ?? '-') . ')';
                $map[(int) $b->id]['keterangan'][] = 'Pengawas bentrok dengan kelas ' . ($a->kelas ?? '-') . ' (' . ($a->kode_mk ?? '-') . ')';
            }
        }

        foreach ($map as $id => $item) {
            $map[$id]['keterangan'] = array_values(array_unique($item['keterangan']));
            $map[$id]['keterangan_str'] = implode('; ', $map[$id]['keterangan']);
        }

        return $map;
    }
}

if (!function_exists('jadwal_ujian_waktu_label')) {
    function jadwal_ujian_waktu_label($jam_mulai, $jam_selesai) {
        if (!$jam_mulai || !$jam_selesai) {
            return '';
        }
        return date('H.i', strtotime($jam_mulai)) . ' - ' . date('H.i', strtotime($jam_selesai)) . ' WIB';
    }
}

if (!function_exists('jadwal_ujian_pengawas_display_name')) {
    function jadwal_ujian_pengawas_display_name($pengawas) {
        if (!empty($pengawas->nama_tendik)) {
            return $pengawas->nama_tendik;
        }
        if (!empty($pengawas->nama_dosen)) {
            return trim(preg_replace('/\s*\((Dosen|Tendik)\)\s*$/i', '', $pengawas->nama_dosen));
        }
        return '';
    }
}

if (!function_exists('jadwal_ujian_pengawas_export_str')) {
    function jadwal_ujian_pengawas_export_str($pengawas_list) {
        if (empty($pengawas_list) || !is_array($pengawas_list)) {
            return '';
        }
        $names = array();
        foreach ($pengawas_list as $p) {
            $name = jadwal_ujian_pengawas_display_name($p);
            if ($name !== '') {
                $names[] = $name;
            }
        }
        return implode(', ', array_unique($names));
    }
}

