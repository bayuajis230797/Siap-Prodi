<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Skripsi Helper
 * Helper untuk perhitungan nilai Skripsi
 */

/**
 * Hitung nilai akhir Skripsi
 * Menggunakan bobot dari database
 * @param float $nilai_bimbingan
 * @param float $nilai_sidang
 * @return float
 */
if (!function_exists('hitung_nilai_akhir_skripsi')) {
    function hitung_nilai_akhir_skripsi($nilai_bimbingan, $nilai_sidang) {
        $CI =& get_instance();
        $CI->load->model('Bobot_nilai_setting_model');
        
        // Gunakan bobot dari database
        return $CI->Bobot_nilai_setting_model->hitung_nilai_akhir_skripsi($nilai_bimbingan, $nilai_sidang);
    }
}

/**
 * Hitung nilai bimbingan rata-rata
 * Rumus: (Nilai Pembimbing 1 + Nilai Pembimbing 2) / 2
 * @param float $nilai_pembimbing_1
 * @param float $nilai_pembimbing_2
 * @return float
 */
if (!function_exists('hitung_nilai_bimbingan_rata')) {
    function hitung_nilai_bimbingan_rata($nilai_pembimbing_1, $nilai_pembimbing_2) {
        // Pembimbing 2 opsional: rata-rata hanya dari nilai yang ada (> 0),
        // konsisten dengan Skripsi_penilaian::sync_bimbingan().
        $values = array();
        foreach (array($nilai_pembimbing_1, $nilai_pembimbing_2) as $v) {
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
 * Hitung nilai sidang rata-rata
 * Rumus: (Nilai Ketua Penguji + Nilai Penguji 1 + Nilai Penguji 2) / 3
 * @param float $nilai_ketua
 * @param float $nilai_penguji_1
 * @param float $nilai_penguji_2
 * @return float
 */
if (!function_exists('hitung_nilai_sidang_rata')) {
    function hitung_nilai_sidang_rata($nilai_ketua, $nilai_penguji_1, $nilai_penguji_2) {
        $total = $nilai_ketua + $nilai_penguji_1 + $nilai_penguji_2;
        return round($total / 3, 2);
    }
}

/**
 * Konversi nilai angka ke nilai huruf (A, B, C, D, E)
 * Menggunakan setting dari database
 * @param float $nilai
 * @return string
 */
if (!function_exists('konversi_nilai_huruf_skripsi')) {
    function konversi_nilai_huruf_skripsi($nilai) {
        if ($nilai === null) {
            return null;
        }

        $CI =& get_instance();
        $CI->load->model('Grade_setting_model');
        
        // Gunakan setting dari database
        return $CI->Grade_setting_model->konversi_nilai_ke_grade($nilai, 'skripsi');
    }
}

/**
 * Hitung nilai P (P1 atau P2) dari item-item penilaian dengan bobot
 * Rumus: P = (Item1 x Weight1 + Item2 x Weight2 + ...) / 100
 * @param array $items Array of items dengan structure: [['nilai' => float, 'bobot' => float], ...]
 * @return float
 */
if (!function_exists('hitung_nilai_p_dari_items_skripsi')) {
    function hitung_nilai_p_dari_items_skripsi($items) {
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

/**
 * Format nilai untuk ditampilkan
 * @param float $nilai
 * @return string
 */
if (!function_exists('format_nilai_skripsi')) {
    function format_nilai_skripsi($nilai) {
        return number_format($nilai, 2, ',', '.');
    }
}

/**
 * Hitung progress Skripsi mahasiswa
 * @param int $id_mahasiswa
 * @param object $tahun_aktif
 * @return array
 */
if (!function_exists('calculate_skripsi_progress')) {
    function calculate_skripsi_progress($id_mahasiswa, $tahun_aktif) {
        if (!$id_mahasiswa || !$tahun_aktif) {
            return array('percentage' => 0, 'current_step' => 0, 'total_steps' => 10, 'steps' => array());
        }
        
        $CI =& get_instance();
        $CI->load->model('Skripsi_model');
        $CI->load->model('Skripsi_pengajuan_model');
        $CI->load->model('Skripsi_bimbingan_model');
        $CI->load->model('Skripsi_pendaftaran_sidang_model');
        $CI->load->model('Jadwal_skripsi_model');
        $CI->load->model('Skripsi_klaim_nilai_model');
        $CI->load->model('Skripsi_nilai_rekap_model');
        
        $steps = array();
        $current_step = 0;
        
        // Step 1: Terdaftar Skripsi
        $skripsi = $CI->Skripsi_model->get_by_mahasiswa($id_mahasiswa, $tahun_aktif->id);
        if ($skripsi) {
            $steps[] = array('name' => 'Terdaftar Skripsi', 'completed' => true);
            $current_step = 1;
            
            // Step 2: Pengajuan judul
            $pengajuan = $CI->Skripsi_pengajuan_model->get_latest_by_mahasiswa($id_mahasiswa, $tahun_aktif->id);
            if ($pengajuan) {
                $steps[] = array('name' => 'Pengajuan Judul', 'completed' => true);
                $current_step = 2;
                
                // Step 3: Judul ACC Pembimbing 1 (pembimbing 2 bisa menyusul belakangan),
                // atau revisi sudah disetujui sekprodi.
                $status_p1 = $pengajuan->status_acc_judul_p1 ?? 'pending';
                $status_approve_revisi = $pengajuan->status_approve_revisi ?? 'pending';
                if ($status_p1 === 'acc' || $status_approve_revisi === 'disetujui') {
                    $steps[] = array('name' => 'Judul ACC Pembimbing 1', 'completed' => true);
                    $current_step = 3;
                    
                    // Step 4: Bimbingan aktif
                    $bimbingan_list = $CI->Skripsi_bimbingan_model->get_by_mahasiswa($id_mahasiswa, $tahun_aktif->id);
                    if (!empty($bimbingan_list)) {
                        $steps[] = array('name' => 'Bimbingan Aktif', 'completed' => true);
                        $current_step = 4;
                        
                        // Step 5: Layak sidang
                        if (isset($skripsi->acc_pembimbing_1) && $skripsi->acc_pembimbing_1 == 1 &&
                            (empty($skripsi->id_pembimbing_2) || (isset($skripsi->acc_pembimbing_2) && $skripsi->acc_pembimbing_2 == 1))) {
                            $steps[] = array('name' => 'Layak Sidang', 'completed' => true);
                            $current_step = 5;
                            
                            // Step 6: Pendaftaran sidang
                            $pendaftaran = $CI->Skripsi_pendaftaran_sidang_model->get_by_mahasiswa_skripsi($skripsi->id);
                            if ($pendaftaran) {
                                $steps[] = array('name' => 'Pendaftaran Sidang', 'completed' => true);
                                $current_step = 6;
                                
                                // Step 7: Sidang selesai (jadwal ada)
                                $jadwal_list = $CI->Jadwal_skripsi_model->get_by_mahasiswa($skripsi->id);
                                if (!empty($jadwal_list)) {
                                    $steps[] = array('name' => 'Sidang Selesai', 'completed' => true);
                                    $current_step = 7;
                                    
                                    // Step 8: Klaim nilai
                                    $klaim = $CI->Skripsi_klaim_nilai_model->get_by_mahasiswa_skripsi($skripsi->id);
                                    if ($klaim) {
                                        $steps[] = array('name' => 'Klaim Nilai', 'completed' => true);
                                        $current_step = 8;
                                        
                                        // Step 9: Selesai (nilai rekap ada)
                                        $jadwal = $jadwal_list[0];
                                        $nilai_rekap = $CI->Skripsi_nilai_rekap_model->get_by_jadwal($jadwal->id);
                                        if ($nilai_rekap) {
                                            $steps[] = array('name' => 'Selesai', 'completed' => true);
                                            $current_step = 9;
                                        } else {
                                            $steps[] = array('name' => 'Selesai', 'completed' => false);
                                        }
                                    } else {
                                        $steps[] = array('name' => 'Klaim Nilai', 'completed' => false);
                                        $steps[] = array('name' => 'Selesai', 'completed' => false);
                                    }
                                } else {
                                    $steps[] = array('name' => 'Sidang Selesai', 'completed' => false);
                                    $steps[] = array('name' => 'Klaim Nilai', 'completed' => false);
                                    $steps[] = array('name' => 'Selesai', 'completed' => false);
                                }
                            } else {
                                $steps[] = array('name' => 'Pendaftaran Sidang', 'completed' => false);
                                $steps[] = array('name' => 'Sidang Selesai', 'completed' => false);
                                $steps[] = array('name' => 'Klaim Nilai', 'completed' => false);
                                $steps[] = array('name' => 'Selesai', 'completed' => false);
                            }
                        } else {
                            $steps[] = array('name' => 'Layak Sidang', 'completed' => false);
                            $steps[] = array('name' => 'Pendaftaran Sidang', 'completed' => false);
                            $steps[] = array('name' => 'Sidang Selesai', 'completed' => false);
                            $steps[] = array('name' => 'Klaim Nilai', 'completed' => false);
                            $steps[] = array('name' => 'Selesai', 'completed' => false);
                        }
                    } else {
                        $steps[] = array('name' => 'Bimbingan Aktif', 'completed' => false);
                        $steps[] = array('name' => 'Layak Sidang', 'completed' => false);
                        $steps[] = array('name' => 'Pendaftaran Sidang', 'completed' => false);
                        $steps[] = array('name' => 'Sidang Selesai', 'completed' => false);
                        $steps[] = array('name' => 'Klaim Nilai', 'completed' => false);
                        $steps[] = array('name' => 'Selesai', 'completed' => false);
                    }
                } else {
                    $steps[] = array('name' => 'Judul ACC Pembimbing', 'completed' => false);
                    $steps[] = array('name' => 'Bimbingan Aktif', 'completed' => false);
                    $steps[] = array('name' => 'Layak Sidang', 'completed' => false);
                    $steps[] = array('name' => 'Pendaftaran Sidang', 'completed' => false);
                    $steps[] = array('name' => 'Sidang Selesai', 'completed' => false);
                    $steps[] = array('name' => 'Klaim Nilai', 'completed' => false);
                    $steps[] = array('name' => 'Selesai', 'completed' => false);
                }
            } else {
                $steps[] = array('name' => 'Pengajuan Judul', 'completed' => false);
                $steps[] = array('name' => 'Judul ACC Pembimbing', 'completed' => false);
                $steps[] = array('name' => 'Bimbingan Aktif', 'completed' => false);
                $steps[] = array('name' => 'Layak Sidang', 'completed' => false);
                $steps[] = array('name' => 'Pendaftaran Sidang', 'completed' => false);
                $steps[] = array('name' => 'Sidang Selesai', 'completed' => false);
                $steps[] = array('name' => 'Klaim Nilai', 'completed' => false);
                $steps[] = array('name' => 'Selesai', 'completed' => false);
            }
        } else {
            $steps[] = array('name' => 'Terdaftar Skripsi', 'completed' => false);
            $steps[] = array('name' => 'Pengajuan Judul', 'completed' => false);
            $steps[] = array('name' => 'Judul ACC Pembimbing', 'completed' => false);
            $steps[] = array('name' => 'Bimbingan Aktif', 'completed' => false);
            $steps[] = array('name' => 'Layak Sidang', 'completed' => false);
            $steps[] = array('name' => 'Pendaftaran Sidang', 'completed' => false);
            $steps[] = array('name' => 'Sidang Selesai', 'completed' => false);
            $steps[] = array('name' => 'Klaim Nilai', 'completed' => false);
            $steps[] = array('name' => 'Selesai', 'completed' => false);
        }
        
        $total_steps = 9;
        $percentage = ($current_step / $total_steps) * 100;
        
        return array(
            'percentage' => round($percentage, 1),
            'current_step' => $current_step,
            'total_steps' => $total_steps,
            'steps' => $steps
        );
    }
}





