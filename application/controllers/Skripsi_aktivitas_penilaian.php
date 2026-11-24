<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Skripsi_aktivitas_penilaian Controller
 * Menampilkan aktivitas penilaian Skripsi secara rinci untuk sekprodi
 */
class Skripsi_aktivitas_penilaian extends MY_Controller {

    public function __construct() {
        parent::__construct();
        $this->check_role(array('sekprodi', 'kaprodi'));
        $this->load->model('Skripsi_model');
        $this->load->model('Skripsi_nilai_bimbingan_model');
        $this->load->model('Skripsi_nilai_bimbingan_detail_model');
        $this->load->model('Skripsi_nilai_sidang_model');
        $this->load->model('Skripsi_nilai_sidang_detail_model');
        $this->load->model('Skripsi_nilai_rekap_model');
        $this->load->model('Skripsi_aspek_penilaian_model');
        $this->load->model('Skripsi_item_penilaian_model');
        $this->load->model('Tahun_akademik_model');
        $this->load->model('Dosen_model');
        $this->load->helper('skripsi');
        $this->load->library('pagination');
    }

    /**
     * Index - Daftar semua aktivitas penilaian Skripsi
     */
    public function index() {
        $tahun_aktif = $this->Tahun_akademik_model->get_aktif();
        if (!$tahun_aktif) {
            $this->session->set_flashdata('error', 'Tidak ada tahun akademik aktif.');
            redirect('Dashboard');
        }

        // Ambil semua mahasiswa Skripsi
        $skripsi_list = $this->Skripsi_model->get_all($tahun_aktif->id);
        
        // Load model untuk pengajuan dan perubahan judul
        $this->load->model('Skripsi_pengajuan_model');
        $this->load->model('Skripsi_perubahan_judul_model');
        $this->load->model('Jadwal_skripsi_model');
        
        $mahasiswa_penilaian = array();
        foreach ($skripsi_list as $skripsi) {
            // Tampilkan jika mahasiswa sudah layak sidang (status layak_sidang atau kedua pembimbing sudah ACC)
            $layak_sidang = false;
            if (isset($skripsi->status) && $skripsi->status === 'layak_sidang') {
                $layak_sidang = true;
            } elseif (isset($skripsi->acc_pembimbing_1) && $skripsi->acc_pembimbing_1 == 1 &&
                      (empty($skripsi->id_pembimbing_2) || (isset($skripsi->acc_pembimbing_2) && $skripsi->acc_pembimbing_2 == 1))) {
                $layak_sidang = true;
            }
            
            // Ambil nilai bimbingan
            $nilai_bimbingan_list = $this->Skripsi_nilai_bimbingan_model->get_by_skripsi($skripsi->id);
            
            // Ambil jadwal sidang untuk mendapatkan nilai sidang
            $jadwal_list = $this->Jadwal_skripsi_model->get_by_mahasiswa($skripsi->id);
            
            // Tampilkan jika layak sidang atau sudah ada nilai bimbingan atau sudah ada jadwal
            if ($layak_sidang || !empty($nilai_bimbingan_list) || !empty($jadwal_list)) {
                // Hitung nilai bimbingan final (rata-rata P1 dan P2)
                $nilai_bimbingan_final = null;
                if (!empty($nilai_bimbingan_list)) {
                    $total_nilai = 0;
                    $count = 0;
                    foreach ($nilai_bimbingan_list as $nb) {
                        if (isset($nb->nilai_rata) && $nb->nilai_rata > 0) {
                            $total_nilai += $nb->nilai_rata;
                            $count++;
                        }
                    }
                    if ($count > 0) {
                        $nilai_bimbingan_final = $total_nilai / $count;
                    }
                }
                
                // Ambil nilai sidang (rata-rata dari semua penguji)
                $nilai_sidang_final = null;
                if (!empty($jadwal_list)) {
                    $this->load->model('Skripsi_nilai_sidang_model');
                    $total_nilai_sidang = 0;
                    $count_sidang = 0;
                    foreach ($jadwal_list as $jadwal) {
                        $nilai_sidang_list = $this->Skripsi_nilai_sidang_model->get_by_jadwal($jadwal->id);
                        foreach ($nilai_sidang_list as $ns) {
                            if (isset($ns->nilai_total) && $ns->nilai_total > 0) {
                                $total_nilai_sidang += $ns->nilai_total;
                                $count_sidang++;
                            }
                        }
                    }
                    if ($count_sidang > 0) {
                        $nilai_sidang_final = $total_nilai_sidang / $count_sidang;
                    }
                }
                
                // Ambil nilai rekap jika ada
                $nilai_rekap = null;
                if (!empty($jadwal_list)) {
                    $nilai_rekap = $this->Skripsi_nilai_rekap_model->get_by_jadwal($jadwal_list[0]->id);
                }
                
                // Ambil judul skripsi terbaru
                $pengajuan = $this->Skripsi_pengajuan_model->get_latest_by_mahasiswa($skripsi->id_mahasiswa, $tahun_aktif->id);
                $judul_terbaru = null;
                if ($pengajuan) {
                    $perubahan_approved = $this->Skripsi_perubahan_judul_model->get_by_pengajuan($pengajuan->id);
                    if ($perubahan_approved && isset($perubahan_approved->status_approve_sekprodi) && $perubahan_approved->status_approve_sekprodi === 'acc') {
                        $judul_terbaru = $perubahan_approved->judul_baru;
                    } elseif (isset($pengajuan->judul_disetujui) && !empty($pengajuan->judul_disetujui)) {
                        $judul_terbaru = $pengajuan->judul_disetujui;
                    } elseif (isset($pengajuan->judul_pengajuan) && !empty($pengajuan->judul_pengajuan)) {
                        $judul_terbaru = $pengajuan->judul_pengajuan;
                    }
                }
                if (empty($judul_terbaru) && isset($skripsi->judul_skripsi) && !empty($skripsi->judul_skripsi)) {
                    $judul_terbaru = $skripsi->judul_skripsi;
                }
                
                // Hitung nilai akhir dan grade - selalu re-konversi dari nilai akhir menggunakan helper function
                $nilai_akhir = null;
                $nilai_huruf = null;
                if ($nilai_rekap && isset($nilai_rekap->nilai_akhir) && $nilai_rekap->nilai_akhir !== null) {
                    $nilai_akhir = $nilai_rekap->nilai_akhir;
                    // Re-konversi dari nilai akhir menggunakan helper function yang mengambil dari setting database
                    $nilai_huruf = konversi_nilai_huruf_skripsi($nilai_akhir);
                } else {
                    // Hitung dari penilaian yang ada jika belum ada rekap
                    if ($nilai_bimbingan_final !== null && $nilai_sidang_final !== null) {
                        $nilai_akhir = hitung_nilai_akhir_skripsi($nilai_bimbingan_final, $nilai_sidang_final);
                        // Konversi dari nilai akhir menggunakan helper function yang mengambil dari setting database
                        $nilai_huruf = konversi_nilai_huruf_skripsi($nilai_akhir);
                    }
                }
                
                $mahasiswa_penilaian[] = array(
                    'skripsi' => $skripsi,
                    'judul_terbaru' => $judul_terbaru,
                    'nilai_bimbingan_list' => $nilai_bimbingan_list,
                    'nilai_bimbingan_final' => $nilai_bimbingan_final,
                    'nilai_sidang_final' => $nilai_sidang_final,
                    'jadwal_list' => $jadwal_list,
                    'nilai_rekap' => $nilai_rekap,
                    'nilai_akhir' => $nilai_akhir,
                    'nilai_huruf' => $nilai_huruf
                );
            }
        }

        $data = array(
            'title' => 'Aktivitas Penilaian Skripsi',
            'page_title' => 'Aktivitas Penilaian Skripsi',
            'breadcrumb' => array(
                array('label' => 'Skripsi', 'url' => '#'),
                array('label' => 'Aktivitas Penilaian', 'active' => true)
            ),
            'sidebar_menu' => generate_sidebar_menu(get_user_role()),
            'mahasiswa_penilaian' => $mahasiswa_penilaian,
            'tahun_aktif' => $tahun_aktif
        );

        $data['content'] = $this->load->view('sekprodi/skripsi_aktivitas_penilaian/index', $data, true);
        $this->load->view('layouts/main', $data);
    }

    /**
     * Detail penilaian untuk mahasiswa tertentu
     */
    public function detail($skripsi_id) {
        $tahun_aktif = $this->Tahun_akademik_model->get_aktif();
        if (!$tahun_aktif) {
            $this->session->set_flashdata('error', 'Tidak ada tahun akademik aktif.');
            redirect('Skripsi_aktivitas_penilaian');
        }

        // Ambil data skripsi
        $skripsi = $this->Skripsi_model->get_by_id($skripsi_id);
        if (!$skripsi) {
            $this->session->set_flashdata('error', 'Data skripsi tidak ditemukan.');
            redirect('Skripsi_aktivitas_penilaian');
        }

        // Ambil nilai bimbingan
        $nilai_bimbingan_list = $this->Skripsi_nilai_bimbingan_model->get_by_skripsi($skripsi_id);
        
        // Buat array untuk menyimpan nilai bimbingan berdasarkan tipe pembimbing
        $nilai_bimbingan_by_tipe = array();
        foreach ($nilai_bimbingan_list as $nb) {
            if (isset($nb->tipe_pembimbing)) {
                $nilai_bimbingan_by_tipe[$nb->tipe_pembimbing] = $nb;
            }
        }
        
        // Untuk setiap nilai bimbingan, ambil detail per item
        foreach ($nilai_bimbingan_list as &$nb) {
            $nb->details = $this->Skripsi_nilai_bimbingan_detail_model->get_by_nilai_bimbingan($nb->id);
        }
        
        // Pastikan semua pembimbing muncul, walaupun belum menginput nilai
        $pembimbing_list_complete = array();
        
        // Pembimbing 1
        if (isset($skripsi->id_pembimbing_1) && $skripsi->id_pembimbing_1) {
            $pembimbing_1 = $this->Dosen_model->get_by_id($skripsi->id_pembimbing_1);
            $p1_data = isset($nilai_bimbingan_by_tipe[1]) ? $nilai_bimbingan_by_tipe[1] : null;
            $pembimbing_list_complete[] = (object) array(
                'tipe_pembimbing' => 1,
                'id_dosen' => $skripsi->id_pembimbing_1,
                'pembimbing_nama' => $pembimbing_1 ? $pembimbing_1->nama_dosen : 'Pembimbing 1',
                'pembimbing_nidn' => $pembimbing_1 ? $pembimbing_1->nidn : '',
                'nilai_rata' => $p1_data ? (isset($p1_data->nilai_rata) ? $p1_data->nilai_rata : null) : null,
                'created_at' => $p1_data ? (isset($p1_data->created_at) ? $p1_data->created_at : null) : null,
                'details' => $p1_data ? (isset($p1_data->details) ? $p1_data->details : array()) : array()
            );
        }
        
        // Pembimbing 2
        if (isset($skripsi->id_pembimbing_2) && $skripsi->id_pembimbing_2) {
            $pembimbing_2 = $this->Dosen_model->get_by_id($skripsi->id_pembimbing_2);
            $p2_data = isset($nilai_bimbingan_by_tipe[2]) ? $nilai_bimbingan_by_tipe[2] : null;
            $pembimbing_list_complete[] = (object) array(
                'tipe_pembimbing' => 2,
                'id_dosen' => $skripsi->id_pembimbing_2,
                'pembimbing_nama' => $pembimbing_2 ? $pembimbing_2->nama_dosen : 'Pembimbing 2',
                'pembimbing_nidn' => $pembimbing_2 ? $pembimbing_2->nidn : '',
                'nilai_rata' => $p2_data ? (isset($p2_data->nilai_rata) ? $p2_data->nilai_rata : null) : null,
                'created_at' => $p2_data ? (isset($p2_data->created_at) ? $p2_data->created_at : null) : null,
                'details' => $p2_data ? (isset($p2_data->details) ? $p2_data->details : array()) : array()
            );
        }
        
        // Gunakan list lengkap yang mencakup semua pembimbing
        $nilai_bimbingan_list = $pembimbing_list_complete;

        // Ambil jadwal sidang
        $this->load->model('Jadwal_skripsi_model');
        $jadwal_list = $this->Jadwal_skripsi_model->get_by_mahasiswa($skripsi_id);
        
        // Ambil nilai sidang untuk setiap jadwal dan pastikan semua penguji muncul
        $nilai_sidang_complete_list = array();
        foreach ($jadwal_list as &$jadwal) {
            $nilai_sidang_list = $this->Skripsi_nilai_sidang_model->get_by_jadwal($jadwal->id);
            
            // Buat array untuk menyimpan nilai sidang berdasarkan peran
            $nilai_sidang_by_peran = array();
            foreach ($nilai_sidang_list as $ns) {
                if (isset($ns->peran)) {
                    $nilai_sidang_by_peran[$ns->peran] = $ns;
                }
                // Ambil detail per item
                $ns->details = $this->Skripsi_nilai_sidang_detail_model->get_by_nilai_sidang($ns->id);
            }
            
            // Pastikan semua penguji muncul, walaupun belum menginput nilai
            $penguji_list_complete = array();
            
            // Ketua Penguji
            if (isset($jadwal->id_ketua_penguji) && $jadwal->id_ketua_penguji) {
                $ketua = $this->Dosen_model->get_by_id($jadwal->id_ketua_penguji);
                $ketua_data = isset($nilai_sidang_by_peran['ketua']) ? $nilai_sidang_by_peran['ketua'] : null;
                $penguji_list_complete[] = (object) array(
                    'peran' => 'ketua',
                    'id_penguji' => $jadwal->id_ketua_penguji,
                    'penguji_nama' => $ketua ? $ketua->nama_dosen : 'Ketua Penguji',
                    'penguji_nidn' => $ketua ? $ketua->nidn : '',
                    'nilai_total' => $ketua_data ? (isset($ketua_data->nilai_total) ? $ketua_data->nilai_total : null) : null,
                    'created_at' => $ketua_data ? (isset($ketua_data->created_at) ? $ketua_data->created_at : null) : null,
                    'catatan' => $ketua_data ? (isset($ketua_data->catatan) ? $ketua_data->catatan : null) : null,
                    'details' => $ketua_data ? (isset($ketua_data->details) ? $ketua_data->details : array()) : array()
                );
            }
            
            // Anggota Penguji 1
            if (isset($jadwal->id_anggota_penguji_1) && $jadwal->id_anggota_penguji_1) {
                $anggota_1 = $this->Dosen_model->get_by_id($jadwal->id_anggota_penguji_1);
                $anggota_1_data = isset($nilai_sidang_by_peran['anggota_1']) ? $nilai_sidang_by_peran['anggota_1'] : null;
                $penguji_list_complete[] = (object) array(
                    'peran' => 'anggota_1',
                    'id_penguji' => $jadwal->id_anggota_penguji_1,
                    'penguji_nama' => $anggota_1 ? $anggota_1->nama_dosen : 'Anggota Penguji 1',
                    'penguji_nidn' => $anggota_1 ? $anggota_1->nidn : '',
                    'nilai_total' => $anggota_1_data ? (isset($anggota_1_data->nilai_total) ? $anggota_1_data->nilai_total : null) : null,
                    'created_at' => $anggota_1_data ? (isset($anggota_1_data->created_at) ? $anggota_1_data->created_at : null) : null,
                    'catatan' => $anggota_1_data ? (isset($anggota_1_data->catatan) ? $anggota_1_data->catatan : null) : null,
                    'details' => $anggota_1_data ? (isset($anggota_1_data->details) ? $anggota_1_data->details : array()) : array()
                );
            }
            
            // Anggota Penguji 2
            if (isset($jadwal->id_anggota_penguji_2) && $jadwal->id_anggota_penguji_2) {
                $anggota_2 = $this->Dosen_model->get_by_id($jadwal->id_anggota_penguji_2);
                $anggota_2_data = isset($nilai_sidang_by_peran['anggota_2']) ? $nilai_sidang_by_peran['anggota_2'] : null;
                $penguji_list_complete[] = (object) array(
                    'peran' => 'anggota_2',
                    'id_penguji' => $jadwal->id_anggota_penguji_2,
                    'penguji_nama' => $anggota_2 ? $anggota_2->nama_dosen : 'Anggota Penguji 2',
                    'penguji_nidn' => $anggota_2 ? $anggota_2->nidn : '',
                    'nilai_total' => $anggota_2_data ? (isset($anggota_2_data->nilai_total) ? $anggota_2_data->nilai_total : null) : null,
                    'created_at' => $anggota_2_data ? (isset($anggota_2_data->created_at) ? $anggota_2_data->created_at : null) : null,
                    'catatan' => $anggota_2_data ? (isset($anggota_2_data->catatan) ? $anggota_2_data->catatan : null) : null,
                    'details' => $anggota_2_data ? (isset($anggota_2_data->details) ? $anggota_2_data->details : array()) : array()
                );
            }
            
            $jadwal->nilai_sidang_list = $penguji_list_complete;
            
            // Gabungkan ke list lengkap
            foreach ($penguji_list_complete as $ns) {
                $nilai_sidang_complete_list[] = $ns;
            }
        }

        // Ambil nilai rekap
        $nilai_rekap = null;
        if (!empty($jadwal_list)) {
            $nilai_rekap = $this->Skripsi_nilai_rekap_model->get_by_jadwal($jadwal_list[0]->id);
        }

        // Ambil judul skripsi terbaru
        $this->load->model('Skripsi_pengajuan_model');
        $this->load->model('Skripsi_perubahan_judul_model');
        $pengajuan = $this->Skripsi_pengajuan_model->get_latest_by_mahasiswa($skripsi->id_mahasiswa, $tahun_aktif->id);
        $judul_skripsi_terbaru = null;
        if ($pengajuan) {
            $perubahan_approved = $this->Skripsi_perubahan_judul_model->get_by_pengajuan($pengajuan->id);
            if ($perubahan_approved && isset($perubahan_approved->status_approve_sekprodi) && $perubahan_approved->status_approve_sekprodi === 'acc') {
                $judul_skripsi_terbaru = $perubahan_approved->judul_baru;
            } elseif (isset($pengajuan->judul_disetujui) && !empty($pengajuan->judul_disetujui)) {
                $judul_skripsi_terbaru = $pengajuan->judul_disetujui;
            } elseif (isset($pengajuan->judul_pengajuan) && !empty($pengajuan->judul_pengajuan)) {
                $judul_skripsi_terbaru = $pengajuan->judul_pengajuan;
            }
        }
        if (empty($judul_skripsi_terbaru) && isset($skripsi->judul_skripsi) && !empty($skripsi->judul_skripsi)) {
            $judul_skripsi_terbaru = $skripsi->judul_skripsi;
        }
        
        // Gabungkan semua nilai sidang dari semua jadwal menjadi satu list (sudah lengkap dengan semua penguji)
        $nilai_sidang_list = $nilai_sidang_complete_list;

        // Ambil item penilaian untuk P1 dan P2
        $aspek_p1 = $this->Skripsi_aspek_penilaian_model->get_by_kode('P1');
        $aspek_p2 = $this->Skripsi_aspek_penilaian_model->get_by_kode('P2');
        $item_p1 = $aspek_p1 ? $this->Skripsi_item_penilaian_model->get_by_aspek($aspek_p1->id, true) : array();
        $item_p2 = $aspek_p2 ? $this->Skripsi_item_penilaian_model->get_by_aspek($aspek_p2->id, true) : array();

        // Hitung rekap penilaian untuk ditampilkan dalam tabel
        $nilai_p1 = null;
        $nilai_p2 = null;
        $nilai_bimbingan_rata = null;
        $nilai_bimbingan_40 = null;
        
        // Ambil nilai pembimbing 1 dan 2 (hanya yang sudah diinput)
        foreach ($nilai_bimbingan_list as $nb) {
            if (isset($nb->tipe_pembimbing)) {
                if ($nb->tipe_pembimbing == 1 && isset($nb->nilai_rata) && $nb->nilai_rata !== null && $nb->nilai_rata > 0) {
                    $nilai_p1 = $nb->nilai_rata;
                } elseif ($nb->tipe_pembimbing == 2 && isset($nb->nilai_rata) && $nb->nilai_rata !== null && $nb->nilai_rata > 0) {
                    $nilai_p2 = $nb->nilai_rata;
                }
            }
        }
        
        // Hitung rata-rata bimbingan
        if ($nilai_p1 !== null && $nilai_p2 !== null) {
            $nilai_bimbingan_rata = ($nilai_p1 + $nilai_p2) / 2;
        } elseif ($nilai_p1 !== null) {
            $nilai_bimbingan_rata = $nilai_p1;
        } elseif ($nilai_p2 !== null) {
            $nilai_bimbingan_rata = $nilai_p2;
        }
        
        // Hitung 40% dari nilai bimbingan
        if ($nilai_bimbingan_rata !== null) {
            $nilai_bimbingan_40 = 0.4 * $nilai_bimbingan_rata;
        }
        
        // Hitung nilai sidang per penguji dan rata-rata
        $nilai_penguji_list = array();
        $nilai_sidang_rata = null;
        $nilai_sidang_60 = null;
        
        // Ambil semua penguji untuk ditampilkan di tabel rekap
        if (!empty($nilai_sidang_list)) {
            foreach ($nilai_sidang_list as $ns) {
                $nilai_penguji_list[] = array(
                    'nama' => isset($ns->penguji_nama) ? $ns->penguji_nama : '-',
                    'peran' => isset($ns->peran) ? $ns->peran : '',
                    'nilai' => (isset($ns->nilai_total) && $ns->nilai_total !== null && $ns->nilai_total > 0) ? $ns->nilai_total : null
                );
            }
            
            // Hitung rata-rata nilai sidang (hanya yang sudah diinput)
            if (!empty($nilai_penguji_list)) {
                $total_nilai_sidang = 0;
                $count_nilai = 0;
                foreach ($nilai_penguji_list as $np) {
                    if (isset($np['nilai']) && $np['nilai'] !== null && $np['nilai'] > 0) {
                        $total_nilai_sidang += $np['nilai'];
                        $count_nilai++;
                    }
                }
                if ($count_nilai > 0) {
                    $nilai_sidang_rata = $total_nilai_sidang / $count_nilai;
                }
            }
        }
        
        // Hitung 60% dari nilai sidang
        if ($nilai_sidang_rata !== null) {
            $nilai_sidang_60 = 0.6 * $nilai_sidang_rata;
        }
        
        // Hitung nilai akhir dan grade
        $nilai_akhir = null;
        $grade = null;
        if ($nilai_bimbingan_40 !== null && $nilai_sidang_60 !== null) {
            $nilai_akhir = $nilai_bimbingan_40 + $nilai_sidang_60;
            $grade = konversi_nilai_huruf_skripsi($nilai_akhir);
        }

        $data = array(
            'title' => 'Detail Aktivitas Penilaian Skripsi',
            'page_title' => 'Detail Aktivitas Penilaian Skripsi',
            'breadcrumb' => array(
                array('label' => 'Skripsi', 'url' => '#'),
                array('label' => 'Aktivitas Penilaian', 'url' => site_url('Skripsi_aktivitas_penilaian')),
                array('label' => 'Detail', 'active' => true)
            ),
            'sidebar_menu' => generate_sidebar_menu(get_user_role()),
            'skripsi' => $skripsi,
            'judul_skripsi_terbaru' => $judul_skripsi_terbaru,
            'nilai_bimbingan_list' => $nilai_bimbingan_list,
            'nilai_sidang_list' => $nilai_sidang_list,
            'jadwal_list' => $jadwal_list,
            'nilai_rekap' => $nilai_rekap,
            'item_p1' => $item_p1,
            'item_p2' => $item_p2,
            'tahun_aktif' => $tahun_aktif,
            // Data rekap untuk tabel
            'nilai_p1' => $nilai_p1,
            'nilai_p2' => $nilai_p2,
            'nilai_bimbingan_rata' => $nilai_bimbingan_rata,
            'nilai_bimbingan_40' => $nilai_bimbingan_40,
            'nilai_penguji_list' => $nilai_penguji_list,
            'nilai_sidang_rata' => $nilai_sidang_rata,
            'nilai_sidang_60' => $nilai_sidang_60,
            'nilai_akhir' => $nilai_akhir,
            'grade' => $grade
        );

        $data['content'] = $this->load->view('sekprodi/skripsi_aktivitas_penilaian/detail', $data, true);
        $this->load->view('layouts/main', $data);
    }

    /**
     * Export daftar mahasiswa skripsi dengan nilai akhir ke Excel
     */
    public function export_excel() {
        $tahun_aktif = $this->Tahun_akademik_model->get_aktif();
        if (!$tahun_aktif) {
            $this->session->set_flashdata('error', 'Tidak ada tahun akademik aktif.');
            redirect('Skripsi_aktivitas_penilaian');
        }

        // Cek apakah PhpSpreadsheet tersedia
        $use_phpspreadsheet = false;
        if (file_exists(APPPATH . '../vendor/autoload.php')) {
            require_once APPPATH . '../vendor/autoload.php';
            if (class_exists('PhpOffice\\PhpSpreadsheet\\Spreadsheet')) {
                $use_phpspreadsheet = true;
            }
        }

        // Jika PhpSpreadsheet tidak tersedia, gunakan CSV sebagai fallback
        $filename_base = 'Daftar_Mahasiswa_Skripsi_Nilai_Akhir_' . $tahun_aktif->tahun_akademik . '_' . ucfirst($tahun_aktif->semester) . '_' . date('YmdHis');

        // Ambil semua mahasiswa skripsi (sama seperti di index)
        $skripsi_list = $this->Skripsi_model->get_all($tahun_aktif->id);
        
        // Load model untuk pengajuan dan perubahan judul
        $this->load->model('Skripsi_pengajuan_model');
        $this->load->model('Skripsi_perubahan_judul_model');
        $this->load->model('Jadwal_skripsi_model');
        
        $data_export = array();
        
        foreach ($skripsi_list as $skripsi) {
            // Tampilkan jika mahasiswa sudah layak sidang
            $layak_sidang = false;
            if (isset($skripsi->status) && $skripsi->status === 'layak_sidang') {
                $layak_sidang = true;
            } elseif (isset($skripsi->acc_pembimbing_1) && $skripsi->acc_pembimbing_1 == 1 &&
                      (empty($skripsi->id_pembimbing_2) || (isset($skripsi->acc_pembimbing_2) && $skripsi->acc_pembimbing_2 == 1))) {
                $layak_sidang = true;
            }
            
            // Ambil nilai bimbingan
            $nilai_bimbingan_list = $this->Skripsi_nilai_bimbingan_model->get_by_skripsi($skripsi->id);
            
            // Ambil jadwal sidang untuk mendapatkan nilai sidang
            $jadwal_list = $this->Jadwal_skripsi_model->get_by_mahasiswa($skripsi->id);
            
            // Tampilkan jika layak sidang atau sudah ada nilai bimbingan atau sudah ada jadwal
            if ($layak_sidang || !empty($nilai_bimbingan_list) || !empty($jadwal_list)) {
                // Hitung nilai bimbingan final
                $nilai_bimbingan_final = null;
                if (!empty($nilai_bimbingan_list)) {
                    $total_nilai = 0;
                    $count = 0;
                    foreach ($nilai_bimbingan_list as $nb) {
                        if (isset($nb->nilai_rata) && $nb->nilai_rata > 0) {
                            $total_nilai += $nb->nilai_rata;
                            $count++;
                        }
                    }
                    if ($count > 0) {
                        $nilai_bimbingan_final = $total_nilai / $count;
                    }
                }
                
                // Ambil nilai sidang
                $nilai_sidang_final = null;
                if (!empty($jadwal_list)) {
                    $total_nilai_sidang = 0;
                    $count_sidang = 0;
                    foreach ($jadwal_list as $jadwal) {
                        $nilai_sidang_list = $this->Skripsi_nilai_sidang_model->get_by_jadwal($jadwal->id);
                        foreach ($nilai_sidang_list as $ns) {
                            if (isset($ns->nilai_total) && $ns->nilai_total > 0) {
                                $total_nilai_sidang += $ns->nilai_total;
                                $count_sidang++;
                            }
                        }
                    }
                    if ($count_sidang > 0) {
                        $nilai_sidang_final = $total_nilai_sidang / $count_sidang;
                    }
                }
                
                // Ambil nilai rekap jika ada
                $nilai_rekap = null;
                if (!empty($jadwal_list)) {
                    $nilai_rekap = $this->Skripsi_nilai_rekap_model->get_by_jadwal($jadwal_list[0]->id);
                }
                
                // Ambil judul skripsi terbaru
                $pengajuan = $this->Skripsi_pengajuan_model->get_latest_by_mahasiswa($skripsi->id_mahasiswa, $tahun_aktif->id);
                $judul_terbaru = null;
                if ($pengajuan) {
                    $perubahan_approved = $this->Skripsi_perubahan_judul_model->get_by_pengajuan($pengajuan->id);
                    if ($perubahan_approved && isset($perubahan_approved->status_approve_sekprodi) && $perubahan_approved->status_approve_sekprodi === 'acc') {
                        $judul_terbaru = $perubahan_approved->judul_baru;
                    } elseif (isset($pengajuan->judul_disetujui) && !empty($pengajuan->judul_disetujui)) {
                        $judul_terbaru = $pengajuan->judul_disetujui;
                    } elseif (isset($pengajuan->judul_pengajuan) && !empty($pengajuan->judul_pengajuan)) {
                        $judul_terbaru = $pengajuan->judul_pengajuan;
                    }
                }
                if (empty($judul_terbaru) && isset($skripsi->judul_skripsi) && !empty($skripsi->judul_skripsi)) {
                    $judul_terbaru = $skripsi->judul_skripsi;
                }
                
                // Hitung nilai akhir dan grade - selalu re-konversi dari nilai akhir menggunakan helper function
                $nilai_akhir = null;
                $nilai_huruf = null;
                if ($nilai_rekap && isset($nilai_rekap->nilai_akhir) && $nilai_rekap->nilai_akhir !== null) {
                    $nilai_akhir = $nilai_rekap->nilai_akhir;
                    // Re-konversi dari nilai akhir menggunakan helper function yang mengambil dari setting database
                    $nilai_huruf = konversi_nilai_huruf_skripsi($nilai_akhir);
                } else {
                    // Hitung dari penilaian yang ada jika belum ada rekap
                    if ($nilai_bimbingan_final !== null && $nilai_sidang_final !== null) {
                        $nilai_akhir = hitung_nilai_akhir_skripsi($nilai_bimbingan_final, $nilai_sidang_final);
                        // Konversi dari nilai akhir menggunakan helper function yang mengambil dari setting database
                        $nilai_huruf = konversi_nilai_huruf_skripsi($nilai_akhir);
                    }
                }
                
                $data_export[] = array(
                    'nim' => isset($skripsi->nim) ? $skripsi->nim : '-',
                    'nama_mahasiswa' => isset($skripsi->nama_mahasiswa) ? $skripsi->nama_mahasiswa : '-',
                    'kelas' => isset($skripsi->kelas) ? $skripsi->kelas : '-',
                    'judul_skripsi' => $judul_terbaru ?: '-',
                    'nilai_bimbingan_final' => $nilai_bimbingan_final,
                    'nilai_sidang_final' => $nilai_sidang_final,
                    'nilai_akhir' => $nilai_akhir,
                    'nilai_huruf' => $nilai_huruf
                );
            }
        }

        // Buat Excel atau CSV
        if ($use_phpspreadsheet) {
            // Buat Excel menggunakan PhpSpreadsheet
            $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet();
            
            // Judul
            $sheet->setCellValue('A1', 'DAFTAR MAHASISWA SKRIPSI DENGAN NILAI AKHIR');
            $sheet->mergeCells('A1:I1');
            $sheet->getStyle('A1')->applyFromArray([
                'font' => ['bold' => true, 'size' => 16],
                'alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER]
            ]);
            
            $sheet->setCellValue('A2', 'Tahun Akademik: ' . $tahun_aktif->tahun_akademik . ' ' . ucfirst($tahun_aktif->semester));
            $sheet->mergeCells('A2:I2');
            
            $sheet->setCellValue('A3', 'Tanggal Export: ' . date('d F Y H:i:s'));
            $sheet->mergeCells('A3:I3');
            
            // Header
            $headerRow = 5;
            $headers = [
                'A' => 'No',
                'B' => 'NIM',
                'C' => 'Nama Mahasiswa',
                'D' => 'Kelas',
                'E' => 'Judul Skripsi',
                'F' => 'Nilai Bimbingan',
                'G' => 'Nilai Sidang',
                'H' => 'Nilai Akhir',
                'I' => 'Grade'
            ];
            
            foreach ($headers as $col => $label) {
                $sheet->setCellValue($col . $headerRow, $label);
            }
            
            $sheet->getStyle('A' . $headerRow . ':I' . $headerRow)->applyFromArray([
                'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                'fill' => ['fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID, 'startColor' => ['rgb' => '1F4E78']],
                'alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER]
            ]);
            
            // Data
            $row = $headerRow + 1;
            $no = 1;
            foreach ($data_export as $item) {
                $sheet->setCellValue('A' . $row, $no++);
                $sheet->setCellValue('B' . $row, $item['nim']);
                $sheet->setCellValue('C' . $row, $item['nama_mahasiswa']);
                $sheet->setCellValue('D' . $row, $item['kelas']);
                $sheet->setCellValue('E' . $row, $item['judul_skripsi']);
                $sheet->setCellValue('F' . $row, $item['nilai_bimbingan_final'] !== null ? number_format($item['nilai_bimbingan_final'], 2) : '-');
                $sheet->setCellValue('G' . $row, $item['nilai_sidang_final'] !== null ? number_format($item['nilai_sidang_final'], 2) : '-');
                $sheet->setCellValue('H' . $row, $item['nilai_akhir'] !== null ? number_format($item['nilai_akhir'], 2) : '-');
                $sheet->setCellValue('I' . $row, $item['nilai_huruf'] !== null ? $item['nilai_huruf'] : '-');
                $row++;
            }
            
            // Set column width
            $sheet->getColumnDimension('A')->setWidth(6);
            $sheet->getColumnDimension('B')->setWidth(15);
            $sheet->getColumnDimension('C')->setWidth(35);
            $sheet->getColumnDimension('D')->setWidth(12);
            $sheet->getColumnDimension('E')->setWidth(50);
            $sheet->getColumnDimension('F')->setWidth(15);
            $sheet->getColumnDimension('G')->setWidth(15);
            $sheet->getColumnDimension('H')->setWidth(15);
            $sheet->getColumnDimension('I')->setWidth(10);
            
            // Alignment
            $sheet->getStyle('A' . ($headerRow + 1) . ':A' . ($row - 1))->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle('D' . ($headerRow + 1) . ':D' . ($row - 1))->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle('F' . ($headerRow + 1) . ':I' . ($row - 1))->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
            
            // Border
            $sheet->getStyle('A' . $headerRow . ':I' . ($row - 1))->applyFromArray([
                'borders' => [
                    'allBorders' => [
                        'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN
                    ]
                ]
            ]);
            
            // Total
            $sheet->setCellValue('A' . $row, 'TOTAL');
            $sheet->mergeCells('A' . $row . ':E' . $row);
            $sheet->setCellValue('F' . $row, count($data_export) . ' Mahasiswa');
            $sheet->mergeCells('F' . $row . ':I' . $row);
            $sheet->getStyle('A' . $row . ':I' . $row)->getFont()->setBold(true);
            
            // Filename
            $filename = $filename_base . '.xlsx';
            
            header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
            header('Content-Disposition: attachment;filename="' . $filename . '"');
            header('Cache-Control: max-age=0');
            
            $writer = \PhpOffice\PhpSpreadsheet\IOFactory::createWriter($spreadsheet, 'Xlsx');
            $writer->save('php://output');
            exit;
        } else {
            // Fallback: Export CSV
            $filename = $filename_base . '.csv';
            header('Content-Type: text/csv; charset=utf-8');
            header('Content-Disposition: attachment;filename="' . $filename . '"');
            header('Cache-Control: max-age=0');
            
            $output = fopen('php://output', 'w');
            
            // BOM untuk Excel UTF-8
            fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));
            
            // Header
            fputcsv($output, array('DAFTAR MAHASISWA SKRIPSI DENGAN NILAI AKHIR'));
            fputcsv($output, array('Tahun Akademik: ' . $tahun_aktif->tahun_akademik . ' ' . ucfirst($tahun_aktif->semester)));
            fputcsv($output, array('Tanggal Export: ' . date('d F Y H:i:s')));
            fputcsv($output, array()); // Baris kosong
            fputcsv($output, array('No', 'NIM', 'Nama Mahasiswa', 'Kelas', 'Judul Skripsi', 'Nilai Bimbingan', 'Nilai Sidang', 'Nilai Akhir', 'Grade'));
            
            // Data
            $no = 1;
            foreach ($data_export as $item) {
                fputcsv($output, array(
                    $no++,
                    $item['nim'],
                    $item['nama_mahasiswa'],
                    $item['kelas'],
                    $item['judul_skripsi'],
                    $item['nilai_bimbingan_final'] !== null ? number_format($item['nilai_bimbingan_final'], 2, ',', '') : '-',
                    $item['nilai_sidang_final'] !== null ? number_format($item['nilai_sidang_final'], 2, ',', '') : '-',
                    $item['nilai_akhir'] !== null ? number_format($item['nilai_akhir'], 2, ',', '') : '-',
                    $item['nilai_huruf'] !== null ? $item['nilai_huruf'] : '-'
                ));
            }
            
            fputcsv($output, array()); // Baris kosong
            fputcsv($output, array('TOTAL', '', '', '', '', count($data_export) . ' Mahasiswa', '', '', ''));
            
            fclose($output);
            exit;
        }
    }
}


