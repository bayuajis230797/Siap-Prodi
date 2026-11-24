<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * PKL Helper
 * Helper untuk perhitungan nilai PKL
 */

/**
 * Hitung nilai akhir dari P1-P6
 * @param float $p1
 * @param float $p2
 * @param float $p3
 * @param float $p4
 * @param float $p5
 * @param float $p6
 * @return float
 */
if (!function_exists('hitung_nilai_akhir_pkl')) {
    function hitung_nilai_akhir_pkl($p1, $p2, $p3, $p4, $p5, $p6) {
        $total = $p1 + $p2 + $p3 + $p4 + $p5 + $p6;
        return round($total / 6, 2);
    }
}

/**
 * Konversi nilai angka ke nilai huruf (A, B, C, D, E)
 * Menggunakan setting dari database
 * @param float $nilai
 * @return string
 */
if (!function_exists('konversi_nilai_huruf_pkl')) {
    function konversi_nilai_huruf_pkl($nilai) {
        if ($nilai === null) {
            return null;
        }

        $CI =& get_instance();
        $CI->load->model('Grade_setting_model');
        
        // Gunakan setting dari database
        return $CI->Grade_setting_model->konversi_nilai_ke_grade($nilai, 'pkl');
    }
}

/**
 * Hitung nilai P dari item-item penilaian dengan bobot
 * @param array $items Array of items dengan structure: [['nilai' => float, 'bobot' => float], ...]
 * @return float
 */
if (!function_exists('hitung_nilai_p_dari_items')) {
    function hitung_nilai_p_dari_items($items) {
        if (empty($items)) {
            return 0;
        }
        
        $total = 0;
        $total_bobot = 0;
        
        foreach ($items as $item) {
            $nilai = isset($item['nilai']) ? floatval($item['nilai']) : 0;
            $bobot = isset($item['bobot']) ? floatval($item['bobot']) : 0;
            
            $total += ($nilai * $bobot / 100);
            $total_bobot += $bobot;
        }
        
        // Jika total bobot tidak 100, normalisasi
        if ($total_bobot > 0 && $total_bobot != 100) {
            $total = ($total / $total_bobot) * 100;
        }
        
        return round($total, 2);
    }
}

/**
 * Format nilai untuk ditampilkan
 * @param float $nilai
 * @return string
 */
if (!function_exists('format_nilai_pkl')) {
    function format_nilai_pkl($nilai) {
        return number_format($nilai, 2, ',', '.');
    }
}

/**
 * Hitung progress PKL mahasiswa
 * @param int $id_mahasiswa
 * @param object $tahun_aktif
 * @return array
 */
if (!function_exists('calculate_pkl_progress')) {
    function calculate_pkl_progress($id_mahasiswa, $tahun_aktif) {
        if (!$id_mahasiswa || !$tahun_aktif) {
            return array('percentage' => 0, 'current_step' => 0, 'total_steps' => 10, 'steps' => array());
        }
        
        $CI =& get_instance();
        $CI->load->model('Pkl_tempat_model');
        $CI->load->model('Pkl_pengajuan_model');
        $CI->load->model('Pkl_pendaftaran_sidang_model');
        $CI->load->model('Pkl_klaim_nilai_model');
        $CI->load->model('Pkl_nilai_rekap_model');
        
        $steps = array();
        $current_step = 0;
        
        // Step 1: Terdaftar PKL
        $steps[] = array('name' => 'Terdaftar PKL', 'completed' => true);
        $current_step = 1;
        
        // Step 2: Input tempat PKL
        $tempat_pkl = $CI->Pkl_tempat_model->get_by_mahasiswa($id_mahasiswa, $tahun_aktif->id);
        if ($tempat_pkl) {
            $steps[] = array('name' => 'Input Tempat PKL', 'completed' => true);
            $current_step = 2;
            
            // Step 3: Tempat PKL disetujui
            if ($tempat_pkl->status_acc_tempat === 'disetujui') {
                $steps[] = array('name' => 'Tempat PKL Disetujui', 'completed' => true);
                $current_step = 3;
                
                // Step 4: Pembimbing ditentukan
                $pengajuan = $CI->Pkl_pengajuan_model->get_by_mahasiswa($id_mahasiswa, $tahun_aktif->id);
                if ($pengajuan && $pengajuan->id_pembimbing) {
                    $steps[] = array('name' => 'Pembimbing Ditentukan', 'completed' => true);
                    $current_step = 4;
                    
                    // Step 5: Pengajuan judul PKL
                    if ($pengajuan->judul_pkl && $pengajuan->judul_pkl !== 'Menunggu Pengajuan Judul') {
                        $steps[] = array('name' => 'Pengajuan Judul PKL', 'completed' => true);
                        $current_step = 5;
                        
                        // Step 6: Judul PKL disetujui
                        if ($pengajuan->status_acc_judul === 'acc') {
                            $steps[] = array('name' => 'Judul PKL Disetujui', 'completed' => true);
                            $current_step = 6;
                            
                            // Step 7: Layak sidang
                            if ($pengajuan->is_layak_sidang == 1) {
                                $steps[] = array('name' => 'Layak Sidang', 'completed' => true);
                                $current_step = 7;
                                
                                // Step 8: Pendaftaran sidang
                                $pendaftaran = $CI->Pkl_pendaftaran_sidang_model->get_by_mahasiswa($id_mahasiswa, $tahun_aktif->id);
                                if ($pendaftaran) {
                                    $steps[] = array('name' => 'Pendaftaran Sidang', 'completed' => true);
                                    $current_step = 8;
                                    
                                    // Step 9: Klaim nilai
                                    $klaim = $CI->Pkl_klaim_nilai_model->get_by_mahasiswa($id_mahasiswa, $tahun_aktif->id);
                                    if ($klaim) {
                                        $steps[] = array('name' => 'Klaim Nilai', 'completed' => true);
                                        $current_step = 9;
                                        
                                        // Step 10: Selesai (nilai rekap ada)
                                        $nilai_rekap = $CI->Pkl_nilai_rekap_model->get_by_mahasiswa($id_mahasiswa, $tahun_aktif->id);
                                        if ($nilai_rekap) {
                                            $steps[] = array('name' => 'Selesai', 'completed' => true);
                                            $current_step = 10;
                                        } else {
                                            $steps[] = array('name' => 'Selesai', 'completed' => false);
                                        }
                                    } else {
                                        $steps[] = array('name' => 'Klaim Nilai', 'completed' => false);
                                        $steps[] = array('name' => 'Selesai', 'completed' => false);
                                    }
                                } else {
                                    $steps[] = array('name' => 'Pendaftaran Sidang', 'completed' => false);
                                    $steps[] = array('name' => 'Klaim Nilai', 'completed' => false);
                                    $steps[] = array('name' => 'Selesai', 'completed' => false);
                                }
                            } else {
                                $steps[] = array('name' => 'Layak Sidang', 'completed' => false);
                                $steps[] = array('name' => 'Pendaftaran Sidang', 'completed' => false);
                                $steps[] = array('name' => 'Klaim Nilai', 'completed' => false);
                                $steps[] = array('name' => 'Selesai', 'completed' => false);
                            }
                        } else {
                            $steps[] = array('name' => 'Judul PKL Disetujui', 'completed' => false);
                            $steps[] = array('name' => 'Layak Sidang', 'completed' => false);
                            $steps[] = array('name' => 'Pendaftaran Sidang', 'completed' => false);
                            $steps[] = array('name' => 'Klaim Nilai', 'completed' => false);
                            $steps[] = array('name' => 'Selesai', 'completed' => false);
                        }
                    } else {
                        $steps[] = array('name' => 'Pengajuan Judul PKL', 'completed' => false);
                        $steps[] = array('name' => 'Judul PKL Disetujui', 'completed' => false);
                        $steps[] = array('name' => 'Layak Sidang', 'completed' => false);
                        $steps[] = array('name' => 'Pendaftaran Sidang', 'completed' => false);
                        $steps[] = array('name' => 'Klaim Nilai', 'completed' => false);
                        $steps[] = array('name' => 'Selesai', 'completed' => false);
                    }
                } else {
                    $steps[] = array('name' => 'Pembimbing Ditentukan', 'completed' => false);
                    $steps[] = array('name' => 'Pengajuan Judul PKL', 'completed' => false);
                    $steps[] = array('name' => 'Judul PKL Disetujui', 'completed' => false);
                    $steps[] = array('name' => 'Layak Sidang', 'completed' => false);
                    $steps[] = array('name' => 'Pendaftaran Sidang', 'completed' => false);
                    $steps[] = array('name' => 'Klaim Nilai', 'completed' => false);
                    $steps[] = array('name' => 'Selesai', 'completed' => false);
                }
            } else {
                $steps[] = array('name' => 'Tempat PKL Disetujui', 'completed' => false);
                $steps[] = array('name' => 'Pembimbing Ditentukan', 'completed' => false);
                $steps[] = array('name' => 'Pengajuan Judul PKL', 'completed' => false);
                $steps[] = array('name' => 'Judul PKL Disetujui', 'completed' => false);
                $steps[] = array('name' => 'Layak Sidang', 'completed' => false);
                $steps[] = array('name' => 'Pendaftaran Sidang', 'completed' => false);
                $steps[] = array('name' => 'Klaim Nilai', 'completed' => false);
                $steps[] = array('name' => 'Selesai', 'completed' => false);
            }
        } else {
            $steps[] = array('name' => 'Input Tempat PKL', 'completed' => false);
            $steps[] = array('name' => 'Tempat PKL Disetujui', 'completed' => false);
            $steps[] = array('name' => 'Pembimbing Ditentukan', 'completed' => false);
            $steps[] = array('name' => 'Pengajuan Judul PKL', 'completed' => false);
            $steps[] = array('name' => 'Judul PKL Disetujui', 'completed' => false);
            $steps[] = array('name' => 'Layak Sidang', 'completed' => false);
            $steps[] = array('name' => 'Pendaftaran Sidang', 'completed' => false);
            $steps[] = array('name' => 'Klaim Nilai', 'completed' => false);
            $steps[] = array('name' => 'Selesai', 'completed' => false);
        }
        
        $total_steps = 10;
        $percentage = ($current_step / $total_steps) * 100;
        
        return array(
            'percentage' => round($percentage, 1),
            'current_step' => $current_step,
            'total_steps' => $total_steps,
            'steps' => $steps
        );
    }
}

/**
 * Workflow state PKL eksplisit end-to-end.
 * Dipakai lintas halaman agar status proses konsisten.
 */
if (!function_exists('resolve_pkl_workflow_state')) {
    function resolve_pkl_workflow_state($id_mahasiswa, $tahun_aktif) {
        $fallback = array(
            'key' => 'unknown',
            'label' => 'Belum Ada Data PKL',
            'class' => 'secondary',
            'description' => 'Data PKL belum tersedia untuk mahasiswa ini.',
            'progress' => array('current_step' => 0, 'total_steps' => 10, 'percentage' => 0)
        );

        if (!$id_mahasiswa || !$tahun_aktif) {
            return $fallback;
        }

        $CI =& get_instance();
        $CI->load->model('Pkl_tempat_model');
        $CI->load->model('Pkl_pengajuan_model');
        $CI->load->model('Pkl_pendaftaran_sidang_model');
        $CI->load->model('Pkl_klaim_nilai_model');
        $CI->load->model('Pkl_nilai_rekap_model');
        $CI->load->model('Jadwal_pkl_model');

        $progress = calculate_pkl_progress($id_mahasiswa, $tahun_aktif);
        $state = $fallback;
        $state['progress'] = array(
            'current_step' => (int) ($progress['current_step'] ?? 0),
            'total_steps' => (int) ($progress['total_steps'] ?? 10),
            'percentage' => (float) ($progress['percentage'] ?? 0)
        );

        $tempat = $CI->Pkl_tempat_model->get_by_mahasiswa($id_mahasiswa, $tahun_aktif->id);
        if (!$tempat) {
            $state['key'] = 'input_tempat';
            $state['label'] = 'Perlu Input Tempat PKL';
            $state['class'] = 'warning';
            $state['description'] = 'Mahasiswa belum mengisi data tempat PKL.';
            return $state;
        }

        if (($tempat->status ?? '') === 'draft') {
            $state['key'] = 'draft_tempat';
            $state['label'] = 'Draft Tempat PKL';
            $state['class'] = 'secondary';
            $state['description'] = 'Data tempat PKL masih draft dan belum dikirim ke Sekprodi.';
            return $state;
        }

        if (($tempat->status_acc_tempat ?? '') === 'pending') {
            $state['key'] = 'verifikasi_tempat';
            $state['label'] = 'Verifikasi Tempat PKL';
            $state['class'] = 'info';
            $state['description'] = 'Menunggu persetujuan tempat PKL dari Sekprodi.';
            return $state;
        }

        if (($tempat->status_acc_tempat ?? '') === 'ditolak') {
            $state['key'] = 'revisi_tempat';
            $state['label'] = 'Perbaiki Tempat PKL';
            $state['class'] = 'danger';
            $state['description'] = 'Pengajuan tempat PKL ditolak dan perlu diperbaiki.';
            return $state;
        }

        $pengajuan = $CI->Pkl_pengajuan_model->get_by_mahasiswa($id_mahasiswa, $tahun_aktif->id);
        if (!$pengajuan || empty($pengajuan->id_pembimbing)) {
            $state['key'] = 'set_pembimbing';
            $state['label'] = 'Menunggu Penunjukan Pembimbing';
            $state['class'] = 'warning';
            $state['description'] = 'Tempat disetujui, tetapi pembimbing PKL belum ditetapkan.';
            return $state;
        }

        $judul = trim((string) ($pengajuan->judul_pkl ?? ''));
        if ($judul === '' || $judul === Pkl_pengajuan_model::PLACEHOLDER_JUDUL) {
            $state['key'] = 'submit_judul';
            $state['label'] = 'Pengajuan Judul PKL';
            $state['class'] = 'warning';
            $state['description'] = 'Mahasiswa perlu mengajukan judul PKL ke pembimbing.';
            return $state;
        }

        $status_judul = (string) ($pengajuan->status_acc_judul ?? 'pending');
        if ($status_judul === 'pending') {
            $state['key'] = 'review_judul';
            $state['label'] = 'Review Judul oleh Pembimbing';
            $state['class'] = 'info';
            $state['description'] = 'Judul sudah diajukan dan sedang direview pembimbing.';
            return $state;
        }

        if ($status_judul === 'revisi') {
            $state['key'] = 'revisi_judul';
            $state['label'] = 'Revisi Judul PKL';
            $state['class'] = 'danger';
            $state['description'] = 'Judul perlu revisi sesuai arahan pembimbing.';
            return $state;
        }

        if ((int) ($pengajuan->is_layak_sidang ?? 0) !== 1) {
            $state['key'] = 'bimbingan';
            $state['label'] = 'Bimbingan Laporan PKL';
            $state['class'] = 'primary';
            $state['description'] = 'Judul disetujui, proses bimbingan laporan sedang berjalan.';
            return $state;
        }

        $pendaftaran = $CI->Pkl_pendaftaran_sidang_model->get_by_mahasiswa($id_mahasiswa, $tahun_aktif->id);
        if (!$pendaftaran) {
            $state['key'] = 'daftar_sidang';
            $state['label'] = 'Siap Daftar Sidang';
            $state['class'] = 'success';
            $state['description'] = 'Mahasiswa sudah layak sidang dan dapat mendaftar sidang PKL.';
            return $state;
        }

        $status_sidang = (string) ($pendaftaran->status ?? 'menunggu');
        if ($status_sidang === 'menunggu') {
            $state['key'] = 'verifikasi_berkas_sidang';
            $state['label'] = 'Verifikasi Berkas Sidang';
            $state['class'] = 'info';
            $state['description'] = 'Pendaftaran sidang menunggu verifikasi berkas Sekprodi.';
            return $state;
        }

        if ($status_sidang === 'ditolak') {
            $state['key'] = 'revisi_berkas_sidang';
            $state['label'] = 'Perbaiki Berkas Sidang';
            $state['class'] = 'danger';
            $state['description'] = 'Berkas sidang ditolak dan perlu diperbaiki.';
            return $state;
        }

        $jadwal = $CI->Jadwal_pkl_model->get_by_mahasiswa($id_mahasiswa, $tahun_aktif->id);
        if (empty($jadwal)) {
            $state['key'] = 'penjadwalan_sidang';
            $state['label'] = 'Menunggu Jadwal Sidang';
            $state['class'] = 'warning';
            $state['description'] = 'Berkas sidang disetujui, menunggu penjadwalan sidang.';
            return $state;
        }

        $klaim = $CI->Pkl_klaim_nilai_model->get_by_mahasiswa($id_mahasiswa, $tahun_aktif->id);
        if (!$klaim) {
            $state['key'] = 'klaim_nilai';
            $state['label'] = 'Siap Klaim Nilai';
            $state['class'] = 'primary';
            $state['description'] = 'Sidang terjadwal/selesai, mahasiswa dapat mengajukan klaim nilai.';
            return $state;
        }

        $status_klaim = (string) ($klaim->status ?? 'menunggu');
        if ($status_klaim === 'menunggu') {
            $state['key'] = 'verifikasi_klaim';
            $state['label'] = 'Verifikasi Klaim Nilai';
            $state['class'] = 'info';
            $state['description'] = 'Klaim nilai menunggu verifikasi Sekprodi.';
            return $state;
        }

        if ($status_klaim === 'ditolak') {
            $state['key'] = 'revisi_klaim';
            $state['label'] = 'Perbaiki Klaim Nilai';
            $state['class'] = 'danger';
            $state['description'] = 'Klaim nilai ditolak dan perlu perbaikan berkas.';
            return $state;
        }

        $nilai_rekap = $CI->Pkl_nilai_rekap_model->get_by_mahasiswa($id_mahasiswa, $tahun_aktif->id);
        if ($nilai_rekap) {
            $state['key'] = 'selesai';
            $state['label'] = 'PKL Selesai';
            $state['class'] = 'success';
            $state['description'] = 'Semua proses PKL selesai dan nilai final telah terbit.';
            return $state;
        }

        $state['key'] = 'finalisasi_nilai';
        $state['label'] = 'Finalisasi Nilai';
        $state['class'] = 'warning';
        $state['description'] = 'Klaim disetujui, menunggu finalisasi nilai akhir.';
        return $state;
    }
}





