<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Skripsi_mahasiswa_dosen Controller
 * Daftar mahasiswa bimbingan Skripsi untuk dosen pembimbing
 */
class Skripsi_mahasiswa_dosen extends MY_Controller {

    public function __construct() {
        parent::__construct();
        $this->check_role('dosen');
        $this->load->model('Skripsi_model');
        $this->load->model('Skripsi_pengajuan_model');
        $this->load->model('Skripsi_nilai_bimbingan_model');
        $this->load->model('Skripsi_nilai_bimbingan_detail_model');
        $this->load->model('Skripsi_aspek_penilaian_model');
        $this->load->model('Skripsi_item_penilaian_model');
        $this->load->model('Tahun_akademik_model');
        $this->load->helper('skripsi');
        $this->load->library('form_validation');
        $this->load->library('Skripsi_penilaian');
    }

    /**
     * Daftar mahasiswa bimbingan Skripsi
     */
    public function index() {
        $id_dosen = $this->session->userdata('ref_id');
        if (!$id_dosen) {
            show_404();
        }

        $tahun_aktif = $this->Tahun_akademik_model->get_aktif();
        if (!$tahun_aktif) {
            $this->session->set_flashdata('error', 'Tidak ada tahun akademik aktif.');
            redirect('Dashboard');
        }

        // Ambil semua skripsi dimana dosen ini adalah pembimbing 1 atau pembimbing 2
        // dan judul sudah di-ACC oleh kedua pembimbing atau perubahan judul sudah di-approve sekprodi
        $this->load->model('Skripsi_perubahan_judul_model');
        $all_skripsi = $this->Skripsi_model->get_all($tahun_aktif->id);
        
        $mahasiswa_list = array();
        foreach ($all_skripsi as $skripsi) {
            // Cek apakah dosen ini adalah pembimbing 1 atau 2
            if ($skripsi->id_pembimbing_1 == $id_dosen || $skripsi->id_pembimbing_2 == $id_dosen) {
                // Ambil pengajuan untuk cek status ACC judul
                $pengajuan = $this->Skripsi_pengajuan_model->get_latest_by_mahasiswa($skripsi->id_mahasiswa, $tahun_aktif->id);
                
                if ($pengajuan) {
                    $status_p1 = isset($pengajuan->status_acc_judul_p1) ? $pengajuan->status_acc_judul_p1 : 'pending';
                    $status_p2 = isset($pengajuan->status_acc_judul_p2) ? $pengajuan->status_acc_judul_p2 : 'pending';
                    
                    // Cek apakah ada perubahan judul yang sudah di-approve sekprodi
                    $perubahan_approved = $this->Skripsi_perubahan_judul_model->get_by_pengajuan($pengajuan->id);
                    $has_perubahan_approved = false;
                    $judul_terbaru = null;
                    
                    // get_by_pengajuan mengembalikan perubahan judul terbaru
                    // Cek apakah statusnya sudah di-approve
                    if ($perubahan_approved && isset($perubahan_approved->status_approve_sekprodi) && $perubahan_approved->status_approve_sekprodi === 'acc') {
                        $has_perubahan_approved = true;
                        $judul_terbaru = $perubahan_approved->judul_baru;
                    }
                    
                    // Tampilkan jika:
                    // 1. Judul sudah di-ACC oleh kedua pembimbing, ATAU
                    // 2. Perubahan judul sudah di-approve sekprodi, ATAU
                    // 3. Status approve revisi = disetujui (untuk sistem lama)
                    if (($status_p1 === 'acc' && $status_p2 === 'acc') || 
                        $has_perubahan_approved ||
                        (isset($pengajuan->status_approve_revisi) && $pengajuan->status_approve_revisi === 'disetujui')) {
                        
                        // Set judul terbaru di pengajuan untuk ditampilkan di view
                        if ($judul_terbaru) {
                            $pengajuan->judul_terbaru = $judul_terbaru;
                        } else {
                            // Gunakan judul_disetujui jika ada, atau judul_pengajuan
                            $pengajuan->judul_terbaru = isset($pengajuan->judul_disetujui) && !empty($pengajuan->judul_disetujui) 
                                ? $pengajuan->judul_disetujui 
                                : (isset($pengajuan->judul_pengajuan) ? $pengajuan->judul_pengajuan : '');
                        }
                        
                        $skripsi->pengajuan = $pengajuan;
                        
                        // Ambil status layak sidang dari kedua pembimbing untuk ditampilkan
                        // Ambil nilai bimbingan dan ACC dari pembimbing 1
                        $nilai_bimbingan_p1 = $this->Skripsi_nilai_bimbingan_model->get_by_skripsi_tipe($skripsi->id, 1);
                        $acc_p1 = isset($skripsi->acc_pembimbing_1) && $skripsi->acc_pembimbing_1 == 1;
                        $layak_p1 = ($nilai_bimbingan_p1 !== null && $acc_p1);
                        
                        // Ambil nilai bimbingan dan ACC dari pembimbing 2
                        $nilai_bimbingan_p2 = $this->Skripsi_nilai_bimbingan_model->get_by_skripsi_tipe($skripsi->id, 2);
                        $acc_p2 = isset($skripsi->acc_pembimbing_2) && $skripsi->acc_pembimbing_2 == 1;
                        $layak_p2 = ($nilai_bimbingan_p2 !== null && $acc_p2);
                        
                        // Simpan status layak sidang untuk kedua pembimbing
                        $skripsi->layak_pembimbing_1 = $layak_p1;
                        $skripsi->layak_pembimbing_2 = $layak_p2;
                        
                        $mahasiswa_list[] = $skripsi;
                    }
                }
            }
        }

        $data = array(
            'title' => 'Daftar Mahasiswa Skripsi',
            'page_title' => 'Daftar Mahasiswa Skripsi',
            'breadcrumb' => array(
                array('label' => 'Skripsi', 'url' => '#'),
                array('label' => 'Daftar Mahasiswa', 'active' => true)
            ),
            'sidebar_menu' => generate_sidebar_menu('dosen'),
            'mahasiswa_list' => $mahasiswa_list,
            'tahun_aktif' => $tahun_aktif,
            'dosen_id' => $id_dosen
        );

        $data['content'] = $this->load->view('dosen/skripsi_mahasiswa_index', $data, true);
        $this->load->view('layouts/main', $data);
    }

    /**
     * Form siap sidang dan input nilai bimbingan
     */
    public function siap_sidang($skripsi_id) {
        $id_dosen = $this->session->userdata('ref_id');
        if (!$id_dosen) {
            show_404();
        }

        $tahun_aktif = $this->Tahun_akademik_model->get_aktif();
        if (!$tahun_aktif) {
            $this->session->set_flashdata('error', 'Tidak ada tahun akademik aktif.');
            redirect('Skripsi_mahasiswa_dosen');
        }

        // Ambil data skripsi
        $skripsi = $this->Skripsi_model->get_by_id($skripsi_id);
        if (!$skripsi) {
            $this->session->set_flashdata('error', 'Data skripsi tidak ditemukan.');
            redirect('Skripsi_mahasiswa_dosen');
        }

        // Cek apakah dosen ini adalah pembimbing 1 atau 2
        $tipe_pembimbing = null;
        if ($skripsi->id_pembimbing_1 == $id_dosen) {
            $tipe_pembimbing = 'pembimbing_1';
        } elseif ($skripsi->id_pembimbing_2 == $id_dosen) {
            $tipe_pembimbing = 'pembimbing_2';
        } else {
            $this->session->set_flashdata('error', 'Anda bukan pembimbing untuk mahasiswa ini.');
            redirect('Skripsi_mahasiswa_dosen');
        }

        // Ambil aspek penilaian P1 (Nilai Bimbingan) untuk pembimbing
        $aspek_p1 = $this->Skripsi_aspek_penilaian_model->get_by_kode('P1');
        if (!$aspek_p1) {
            $this->session->set_flashdata('error', 'Aspek penilaian P1 tidak ditemukan. Silakan hubungi admin untuk setting aspek penilaian.');
            redirect('Skripsi_mahasiswa_dosen');
        }

        // Ambil item penilaian P1
        $item_list = $this->Skripsi_item_penilaian_model->get_by_aspek($aspek_p1->id, true);
        if (empty($item_list)) {
            $this->session->set_flashdata('error', 'Item penilaian P1 tidak ditemukan. Silakan hubungi admin untuk setting item penilaian.');
            redirect('Skripsi_mahasiswa_dosen');
        }

        // Cek apakah sudah ada nilai bimbingan
        // Konversi tipe_pembimbing ke integer (1 = Pembimbing 1, 2 = Pembimbing 2)
        $tipe_pembimbing_int = ($tipe_pembimbing == 'pembimbing_1') ? 1 : 2;
        $nilai_bimbingan = $this->Skripsi_nilai_bimbingan_model->get_by_skripsi_tipe($skripsi_id, $tipe_pembimbing_int);

        // Ambil detail penilaian jika sudah ada
        $penilaian_details = array();
        if ($nilai_bimbingan) {
            $details = $this->Skripsi_nilai_bimbingan_detail_model->get_by_nilai_bimbingan($nilai_bimbingan->id);
            foreach ($details as $detail) {
                $penilaian_details[$detail->id_item_penilaian] = $detail->nilai;
            }
        }

        if ($this->input->method() === 'post') {
            // Validasi input nilai per item
            $errors = array();
            $nilai_items = array();
            
            foreach ($item_list as $item) {
                $field_name = 'nilai_item_' . $item->id;
                $nilai = $this->input->post($field_name);
                
                if ($nilai === '' || $nilai === null) {
                    $errors[] = "Nilai untuk item '{$item->pertanyaan}' belum diisi";
                } elseif (!is_numeric($nilai) || $nilai < 0 || $nilai > 100) {
                    $errors[] = "Nilai untuk item '{$item->pertanyaan}' harus antara 0-100";
                } else {
                    $nilai_items[$item->id] = array(
                        'nilai' => floatval($nilai),
                        'bobot' => floatval($item->bobot)
                    );
                }
            }

            if (!empty($errors)) {
                $this->session->set_flashdata('error', implode('<br>', $errors));
            } else {
                // Hitung nilai P1 dari item-item
                $nilai_p1 = hitung_nilai_p_dari_items_skripsi($nilai_items);

                // Simpan atau update nilai bimbingan
                // $tipe_pembimbing_int sudah didefinisikan di atas
                $nilai_bimbingan_data = array(
                    'id_mahasiswa_skripsi' => $skripsi_id,
                    'id_dosen' => $id_dosen,
                    'tipe_pembimbing' => $tipe_pembimbing_int,
                    'id_tahun_akademik' => $tahun_aktif->id,
                    'nilai_rata' => $nilai_p1,
                    'kedisiplinan' => 0,  // Default value, tidak digunakan di sistem item penilaian
                    'kreativitas' => 0,  // Default value, tidak digunakan di sistem item penilaian
                    'penguasaan_materi' => 0,  // Default value, tidak digunakan di sistem item penilaian
                    'kelengkapan_referensi' => 0  // Default value, tidak digunakan di sistem item penilaian
                );

                if ($nilai_bimbingan) {
                    // Update nilai bimbingan
                    $nilai_bimbingan_id = $nilai_bimbingan->id;
                    $this->Skripsi_nilai_bimbingan_model->update($nilai_bimbingan_id, $nilai_bimbingan_data);
                    
                    // Hapus detail lama
                    $this->Skripsi_nilai_bimbingan_detail_model->delete_by_nilai_bimbingan($nilai_bimbingan_id);
                } else {
                    // Create nilai bimbingan
                    $nilai_bimbingan_id = $this->Skripsi_nilai_bimbingan_model->create($nilai_bimbingan_data);
                }

                // Simpan detail penilaian per item
                foreach ($nilai_items as $id_item => $data_item) {
                    $this->Skripsi_nilai_bimbingan_detail_model->create(array(
                        'id_nilai_bimbingan' => $nilai_bimbingan_id,
                        'id_item_penilaian' => $id_item,
                        'nilai' => $data_item['nilai']
                    ));
                }

                // Update ACC pembimbing (menyatakan layak sidang)
                $update_acc = array();
                if ($tipe_pembimbing_int == 1) {
                    $update_acc['acc_pembimbing_1'] = 1;
                } else {
                    $update_acc['acc_pembimbing_2'] = 1;
                }
                $this->Skripsi_model->update($skripsi_id, $update_acc);

                // Cek apakah kedua pembimbing sudah input nilai dan sudah ACC
                $nilai_bimbingan_p1 = $this->Skripsi_nilai_bimbingan_model->get_by_skripsi_tipe($skripsi_id, 1);
                $nilai_bimbingan_p2 = $this->Skripsi_nilai_bimbingan_model->get_by_skripsi_tipe($skripsi_id, 2);
                
                // Ambil data skripsi terbaru untuk cek ACC
                $skripsi_updated = $this->Skripsi_model->get_by_id($skripsi_id);
                $acc_p1 = (isset($skripsi_updated->acc_pembimbing_1) && $skripsi_updated->acc_pembimbing_1 == 1);
                $acc_p2 = (isset($skripsi_updated->acc_pembimbing_2) && $skripsi_updated->acc_pembimbing_2 == 1);

                // Pembimbing 2 opsional: bila tidak diplot, cukup Pembimbing 1.
                $p2_diplot = !empty($skripsi_updated->id_pembimbing_2);
                $p2_terpenuhi = $p2_diplot ? ($nilai_bimbingan_p2 && $acc_p2) : true;

                // Jika pembimbing yang diplot sudah input nilai dan sudah ACC, update status menjadi 'layak_sidang'
                if ($nilai_bimbingan_p1 && $acc_p1 && $p2_terpenuhi) {
                    $this->Skripsi_model->update($skripsi_id, array(
                        'status' => 'layak_sidang'
                    ));
                }

                // Sinkronkan nilai bimbingan ke tabel mahasiswa_skripsi
                $this->skripsi_penilaian->sync_bimbingan($skripsi_id);

                $this->session->set_flashdata('success', 'Nilai bimbingan berhasil disimpan.');
                redirect('Skripsi_mahasiswa_dosen');
            }
        }

        $data = array(
            'title' => 'Siap Sidang & Input Nilai Bimbingan',
            'page_title' => 'Siap Sidang & Input Nilai Bimbingan',
            'breadcrumb' => array(
                array('label' => 'Skripsi', 'url' => '#'),
                array('label' => 'Daftar Mahasiswa', 'url' => site_url('Skripsi_mahasiswa_dosen')),
                array('label' => 'Siap Sidang', 'active' => true)
            ),
            'sidebar_menu' => generate_sidebar_menu('dosen'),
            'skripsi' => $skripsi,
            'tipe_pembimbing' => $tipe_pembimbing,
            'item_list' => $item_list,
            'nilai_bimbingan' => $nilai_bimbingan,
            'penilaian_details' => $penilaian_details,
            'tahun_aktif' => $tahun_aktif
        );

        $data['content'] = $this->load->view('dosen/skripsi_siap_sidang_form', $data, true);
        $this->load->view('layouts/main', $data);
    }

    /**
     * Detail penilaian untuk mahasiswa bimbingan tertentu
     */
    public function detail_penilaian($skripsi_id) {
        $id_dosen = $this->session->userdata('ref_id');
        if (!$id_dosen) {
            show_404();
        }

        $tahun_aktif = $this->Tahun_akademik_model->get_aktif();
        if (!$tahun_aktif) {
            $this->session->set_flashdata('error', 'Tidak ada tahun akademik aktif.');
            redirect('Skripsi_mahasiswa_dosen');
        }

        // Ambil data skripsi
        $skripsi = $this->Skripsi_model->get_by_id($skripsi_id);
        if (!$skripsi) {
            $this->session->set_flashdata('error', 'Data skripsi tidak ditemukan.');
            redirect('Skripsi_mahasiswa_dosen');
        }

        // Pastikan dosen ini adalah pembimbing 1 atau 2 untuk mahasiswa ini
        if ($skripsi->id_pembimbing_1 != $id_dosen && $skripsi->id_pembimbing_2 != $id_dosen) {
            $this->session->set_flashdata('error', 'Anda tidak memiliki akses untuk melihat penilaian mahasiswa ini.');
            redirect('Skripsi_mahasiswa_dosen');
        }

        // Load model yang diperlukan
        $this->load->model('Skripsi_nilai_sidang_model');
        $this->load->model('Skripsi_nilai_sidang_detail_model');
        $this->load->model('Skripsi_nilai_rekap_model');
        $this->load->model('Jadwal_skripsi_model');
        $this->load->model('Dosen_model');
        $this->load->model('Skripsi_perubahan_judul_model');

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
                array('label' => 'Daftar Mahasiswa', 'url' => site_url('Skripsi_mahasiswa_dosen')),
                array('label' => 'Detail Penilaian', 'active' => true)
            ),
            'sidebar_menu' => generate_sidebar_menu('dosen'),
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

        $data['content'] = $this->load->view('dosen/skripsi_aktivitas_penilaian_detail', $data, true);
        $this->load->view('layouts/main', $data);
    }
}


