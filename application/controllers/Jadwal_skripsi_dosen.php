<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Jadwal Skripsi Dosen Controller
 * Menampilkan jadwal ujian Skripsi dimana dosen menjadi penguji
 * Dapat input penilaian dan catatan revisi pasca sidang
 */
class Jadwal_skripsi_dosen extends MY_Controller {

    public function __construct() {
        parent::__construct();
        $this->check_role('dosen');
        $this->load->model('Jadwal_skripsi_model');
        $this->load->model('Skripsi_nilai_sidang_model');
        $this->load->model('Skripsi_nilai_sidang_detail_model');
        $this->load->model('Skripsi_aspek_penilaian_model');
        $this->load->model('Skripsi_item_penilaian_model');
        $this->load->model('Tahun_akademik_model');
        $this->load->model('Skripsi_berkas_upload_model');
        $this->load->model('Skripsi_pendaftaran_sidang_model');
        $this->load->helper('skripsi');
        $this->load->library('form_validation');
        $this->load->library('Skripsi_penilaian');
    }

    /**
     * Index - Daftar jadwal Skripsi dimana dosen menjadi penguji
     */
    public function index() {
        $id_dosen = $this->session->userdata('ref_id');
        
        if (!$id_dosen) {
            $this->session->set_flashdata('error', 'Data dosen tidak ditemukan.');
            redirect('Dashboard');
        }

        // Ambil tahun akademik aktif
        $tahun_aktif = $this->Tahun_akademik_model->get_aktif();
        $id_tahun_akademik = $tahun_aktif ? $tahun_aktif->id : null;

        // Ambil jadwal Skripsi dimana dosen ini menjadi penguji
        $jadwal_list = $this->Jadwal_skripsi_model->get_by_penguji($id_dosen, $id_tahun_akademik);

        // Untuk setiap jadwal, cek apakah sudah ada penilaian
        foreach ($jadwal_list as &$jadwal) {
            // Tentukan peran penguji
            $peran = null;
            if ($jadwal->id_ketua_penguji == $id_dosen) {
                $peran = 'ketua';
            } elseif ($jadwal->id_anggota_penguji_1 == $id_dosen) {
                $peran = 'anggota_1';
            } elseif ($jadwal->id_anggota_penguji_2 == $id_dosen) {
                $peran = 'anggota_2';
            }
            
            $jadwal->tipe_penguji = $peran; // Untuk kompatibilitas dengan view
            $jadwal->peran = $peran;
            
            if ($peran) {
                $nilai_sidang = $this->Skripsi_nilai_sidang_model->get_by_jadwal_penguji($jadwal->id, $peran);
                $jadwal->penilaian = $nilai_sidang;
            }
        }
        unset($jadwal);

        $data = array(
            'title' => 'Jadwal Sidang Skripsi',
            'page_title' => 'Jadwal Sidang Skripsi',
            'breadcrumb' => array(
                array('label' => 'Skripsi', 'url' => '#'),
                array('label' => 'Jadwal Sidang Skripsi', 'active' => true)
            ),
            'sidebar_menu' => generate_sidebar_menu('dosen'),
            'jadwal_list' => $jadwal_list,
            'tahun_aktif' => $tahun_aktif
        );

        $data['content'] = $this->load->view('dosen/jadwal_skripsi', $data, true);
        $this->load->view('layouts/main', $data);
    }

    /**
     * Detail jadwal dan form input penilaian
     */
    public function detail($id) {
        $id_dosen = $this->session->userdata('ref_id');
        
        if (!$id_dosen) {
            show_404();
        }

        $tahun_aktif = $this->Tahun_akademik_model->get_aktif();
        if (!$tahun_aktif) {
            $this->session->set_flashdata('error', 'Tidak ada tahun akademik aktif.');
            redirect('Jadwal_skripsi_dosen');
        }

        // Ambil jadwal Skripsi
        $jadwal = $this->Jadwal_skripsi_model->get_by_id($id);
        if (!$jadwal) {
            $this->session->set_flashdata('error', 'Data jadwal tidak ditemukan.');
            redirect('Jadwal_skripsi_dosen');
        }

        // Tentukan peran penguji
        $peran = null;
        if ($jadwal->id_ketua_penguji == $id_dosen) {
            $peran = 'ketua';
        } elseif ($jadwal->id_anggota_penguji_1 == $id_dosen) {
            $peran = 'anggota_1';
        } elseif ($jadwal->id_anggota_penguji_2 == $id_dosen) {
            $peran = 'anggota_2';
        } else {
            $this->session->set_flashdata('error', 'Anda bukan penguji untuk jadwal ini.');
            redirect('Jadwal_skripsi_dosen');
        }

        // Ambil aspek penilaian P2 (Nilai Sidang) untuk penguji
        $aspek_p2 = $this->Skripsi_aspek_penilaian_model->get_by_kode('P2');
        if (!$aspek_p2) {
            $this->session->set_flashdata('error', 'Aspek penilaian P2 tidak ditemukan. Silakan hubungi admin untuk setting aspek penilaian.');
            redirect('Jadwal_skripsi_dosen');
        }
        
        // Ambil item penilaian P2
        $item_list = $this->Skripsi_item_penilaian_model->get_by_aspek($aspek_p2->id, true);
        if (empty($item_list)) {
            $this->session->set_flashdata('error', 'Item penilaian P2 tidak ditemukan. Silakan hubungi admin untuk setting item penilaian.');
            redirect('Jadwal_skripsi_dosen');
        }

        // Cek apakah sudah ada penilaian
        $nilai_sidang = $this->Skripsi_nilai_sidang_model->get_by_jadwal_penguji($id, $peran);

        // Ambil detail penilaian jika sudah ada
        $penilaian_details = array();
        if ($nilai_sidang) {
            $details = $this->Skripsi_nilai_sidang_detail_model->get_by_nilai_sidang($nilai_sidang->id);
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

            // Validasi catatan revisi
            $catatan_revisi = $this->input->post('catatan_revisi');

            if (!empty($errors)) {
                $this->session->set_flashdata('error', implode('<br>', $errors));
            } else {
                // Hitung nilai P2 dari item-item
                $nilai_p2 = hitung_nilai_p_dari_items_skripsi($nilai_items);

                // Ambil data mahasiswa skripsi
                $this->load->model('Skripsi_model');
                $skripsi = $this->Skripsi_model->get_by_id($jadwal->id_mahasiswa_skripsi);

                // Hitung N1, N2, N3, N4 dari nilai items (jika diperlukan)
                // Untuk saat ini, kita hanya menyimpan nilai_total
                $nilai_total = $nilai_p2;
                
                // Simpan atau update penilaian
                // Catatan: Kolom nilai_sistematika, nilai_bahasa, dll akan diisi dengan 0 
                // karena detail nilai sudah disimpan di skripsi_nilai_sidang_detail
                $nilai_sidang_data = array(
                    'id_jadwal_skripsi' => $id,
                    'id_mahasiswa_skripsi' => $jadwal->id_mahasiswa_skripsi,
                    'id_penguji' => $id_dosen,
                    'peran' => $peran,
                    'nilai_sistematika' => 0,
                    'nilai_bahasa' => 0,
                    'nilai_format' => 0,
                    'nilai_penguasaan_isi' => 0,
                    'nilai_penguasaan_tugas' => 0,
                    'nilai_penguasaan_konsep' => 0,
                    'nilai_penampilan' => 0,
                    'nilai_penggunaan_alat' => 0,
                    'nilai_kesesuaian_waktu' => 0,
                    'nilai_kejelasan_materi' => 0,
                    'nilai_penguasaan_produk' => 0,
                    'nilai_perancangan_produk' => 0,
                    'n1' => 0,
                    'n2' => 0,
                    'n3' => 0,
                    'n4' => 0,
                    'nilai_total' => $nilai_total,
                    'catatan' => null,
                    'catatan_revisi' => $catatan_revisi ? trim($catatan_revisi) : null
                );

                if ($nilai_sidang) {
                    // Update penilaian
                    $nilai_sidang_id = $nilai_sidang->id;
                    $this->Skripsi_nilai_sidang_model->update($nilai_sidang_id, $nilai_sidang_data);
                    
                    // Hapus detail lama
                    $this->Skripsi_nilai_sidang_detail_model->delete_by_nilai_sidang($nilai_sidang_id);
                } else {
                    // Create penilaian
                    $nilai_sidang_id = $this->Skripsi_nilai_sidang_model->create($nilai_sidang_data);
                }

                // Simpan detail penilaian per item
                foreach ($nilai_items as $id_item => $data_item) {
                    $this->Skripsi_nilai_sidang_detail_model->create(array(
                        'id_nilai_sidang' => $nilai_sidang_id,
                        'id_item_penilaian' => $id_item,
                        'nilai' => $data_item['nilai']
                    ));
                }

                // Update status jadwal menjadi selesai jika sudah dinilai
                $this->Jadwal_skripsi_model->update($id, array('status' => 'selesai'));

                // Aktifkan thread bimbingan pasca sidang (shared penguji)
                $this->load->library('Bimbingan_pasca_service');
                $this->bimbingan_pasca_service->ensure_skripsi_by_jadwal($id);

                // Sinkronkan nilai sidang ke tabel mahasiswa_skripsi
                $this->skripsi_penilaian->sync_sidang($jadwal->id_mahasiswa_skripsi);

                $this->session->set_flashdata('success', 'Penilaian dan catatan revisi berhasil disimpan.');
                redirect('Jadwal_skripsi_dosen');
            }
        }

        // Ambil judul skripsi terbaru
        $judul_skripsi_terbaru = $this->get_judul_skripsi_terbaru($jadwal->id_mahasiswa_skripsi);

        $data = array(
            'title' => 'Input Nilai Sidang Skripsi',
            'page_title' => 'Input Nilai Sidang Skripsi',
            'breadcrumb' => array(
                array('label' => 'Skripsi', 'url' => '#'),
                array('label' => 'Jadwal Sidang Skripsi', 'url' => site_url('Jadwal_skripsi_dosen')),
                array('label' => 'Input Nilai', 'active' => true)
            ),
            'sidebar_menu' => generate_sidebar_menu('dosen'),
            'jadwal' => $jadwal,
            'tipe_penguji' => $peran, // Untuk kompatibilitas dengan view
            'peran' => $peran,
            'item_list' => $item_list,
            'nilai_sidang' => $nilai_sidang,
            'penilaian_details' => $penilaian_details,
            'tahun_aktif' => $tahun_aktif,
            'judul_skripsi_terbaru' => $judul_skripsi_terbaru
        );

        $data['content'] = $this->load->view('dosen/jadwal_skripsi_detail', $data, true);
        $this->load->view('layouts/main', $data);
    }

    /**
     * Tampilkan daftar berkas ajuan mahasiswa untuk jadwal sidang
     */
    public function berkas($id) {
        $id_dosen = $this->session->userdata('ref_id');
        
        if (!$id_dosen) {
            show_404();
        }

        // Ambil jadwal Skripsi
        $jadwal = $this->Jadwal_skripsi_model->get_by_id($id);
        if (!$jadwal) {
            $this->session->set_flashdata('error', 'Data jadwal tidak ditemukan.');
            redirect('Jadwal_skripsi_dosen');
        }

        // Tentukan peran penguji
        $peran = null;
        if ($jadwal->id_ketua_penguji == $id_dosen) {
            $peran = 'ketua';
        } elseif ($jadwal->id_anggota_penguji_1 == $id_dosen) {
            $peran = 'anggota_1';
        } elseif ($jadwal->id_anggota_penguji_2 == $id_dosen) {
            $peran = 'anggota_2';
        } else {
            $this->session->set_flashdata('error', 'Anda bukan penguji untuk jadwal ini.');
            redirect('Jadwal_skripsi_dosen');
        }

        // Ambil berkas tahap 1 (jadwal) dan tahap 2 (verifikasi)
        $pendaftaran_tahap1 = $this->Skripsi_pendaftaran_sidang_model->get_by_mahasiswa_skripsi_tahap(
            $jadwal->id_mahasiswa_skripsi,
            'jadwal'
        );
        $pendaftaran_tahap2 = $this->Skripsi_pendaftaran_sidang_model->get_by_mahasiswa_skripsi_tahap(
            $jadwal->id_mahasiswa_skripsi,
            'verifikasi'
        );

        $berkas_tahap1 = array();
        $berkas_tahap2 = array();
        if ($pendaftaran_tahap1) {
            $berkas_tahap1 = $this->Skripsi_berkas_upload_model->get_by_pendaftaran($pendaftaran_tahap1->id);
        }
        if ($pendaftaran_tahap2) {
            $berkas_tahap2 = $this->Skripsi_berkas_upload_model->get_by_pendaftaran($pendaftaran_tahap2->id);
        }

        // Fallback lama: jika kolom tahap belum dipakai / data tanpa tahap
        if (empty($berkas_tahap1) && empty($berkas_tahap2)) {
            $pendaftaran_legacy = $this->Skripsi_pendaftaran_sidang_model->get_by_mahasiswa_skripsi($jadwal->id_mahasiswa_skripsi);
            if ($pendaftaran_legacy) {
                $berkas_tahap2 = $this->Skripsi_berkas_upload_model->get_by_pendaftaran($pendaftaran_legacy->id);
                $pendaftaran_tahap2 = $pendaftaran_legacy;
            }
        }

        // Ambil judul skripsi terbaru
        $judul_skripsi_terbaru = $this->get_judul_skripsi_terbaru($jadwal->id_mahasiswa_skripsi);

        $data = array(
            'title' => 'Berkas Ajuan Sidang Skripsi',
            'page_title' => 'Berkas Ajuan Sidang Skripsi',
            'breadcrumb' => array(
                array('label' => 'Skripsi', 'url' => '#'),
                array('label' => 'Jadwal Sidang Skripsi', 'url' => site_url('Jadwal_skripsi_dosen')),
                array('label' => 'Berkas Ajuan', 'active' => true)
            ),
            'sidebar_menu' => generate_sidebar_menu('dosen'),
            'jadwal' => $jadwal,
            'pendaftaran_tahap1' => $pendaftaran_tahap1,
            'pendaftaran_tahap2' => $pendaftaran_tahap2,
            'berkas_tahap1' => $berkas_tahap1,
            'berkas_tahap2' => $berkas_tahap2,
            'peran' => $peran,
            'judul_skripsi_terbaru' => $judul_skripsi_terbaru
        );

        $data['content'] = $this->load->view('dosen/jadwal_skripsi_berkas', $data, true);
        $this->load->view('layouts/main', $data);
    }

    /**
     * Download berkas ajuan mahasiswa
     */
    public function download_berkas($id_berkas_upload) {
        $id_dosen = $this->session->userdata('ref_id');
        
        if (!$id_dosen) {
            show_404();
        }

        // Ambil data berkas upload
        $berkas_upload = $this->Skripsi_berkas_upload_model->get_by_id($id_berkas_upload);
        if (!$berkas_upload) {
            $this->session->set_flashdata('error', 'Berkas tidak ditemukan.');
            redirect('Jadwal_skripsi_dosen');
        }

        // Ambil pendaftaran untuk validasi akses
        $pendaftaran = $this->Skripsi_pendaftaran_sidang_model->get_by_id($berkas_upload->id_pendaftaran_sidang);
        if (!$pendaftaran) {
            $this->session->set_flashdata('error', 'Data pendaftaran tidak ditemukan.');
            redirect('Jadwal_skripsi_dosen');
        }

        // Cek apakah dosen ini adalah penguji untuk jadwal yang terkait dengan pendaftaran ini
        // Cari jadwal berdasarkan id_mahasiswa_skripsi dari pendaftaran
        $this->db->select('js.*');
        $this->db->from('jadwal_skripsi js');
        $this->db->where('js.id_mahasiswa_skripsi', $pendaftaran->id_mahasiswa_skripsi);
        $this->db->group_start();
        $this->db->where('js.id_ketua_penguji', $id_dosen);
        $this->db->or_where('js.id_anggota_penguji_1', $id_dosen);
        $this->db->or_where('js.id_anggota_penguji_2', $id_dosen);
        $this->db->group_end();
        $jadwal = $this->db->get()->row();

        if (!$jadwal) {
            $this->session->set_flashdata('error', 'Anda tidak memiliki akses untuk mengunduh berkas ini.');
            redirect('Jadwal_skripsi_dosen');
        }

        // Cek apakah ini link Google Drive
        $file_path = $berkas_upload->file_path;
        
        if (strpos($file_path, 'http') === 0) {
            // Ini adalah link Google Drive, redirect ke link tersebut
            $this->load->helper('google_drive');
            $normalized_link = normalize_google_drive_link($file_path);
            redirect($normalized_link);
        }
        
        // Jika bukan link, coba download file
        $file_path = ltrim($file_path, './');
        $file_path = str_replace('\\', '/', $file_path);
        
        $full_path = FCPATH . $file_path;
        
        if (!file_exists($full_path)) {
            $this->session->set_flashdata('error', 'File tidak ditemukan di server.');
            redirect('Jadwal_skripsi_dosen/berkas/' . $jadwal->id);
        }

        // Download file
        $this->load->helper('download');
        $file_name = $berkas_upload->file_name ?: basename($file_path);
        force_download($file_name, file_get_contents($full_path));
    }

    /**
     * Ambil judul skripsi terbaru untuk mahasiswa
     */
    private function get_judul_skripsi_terbaru($id_mahasiswa_skripsi) {
        $this->load->model('Skripsi_model');
        $this->load->model('Skripsi_pengajuan_model');
        $this->load->model('Skripsi_perubahan_judul_model');
        
        $skripsi = $this->Skripsi_model->get_by_id($id_mahasiswa_skripsi);
        if (!$skripsi) {
            return 'Belum ada judul';
        }

        $tahun_aktif = $this->Tahun_akademik_model->get_aktif();
        if (!$tahun_aktif) {
            return isset($skripsi->judul_skripsi) && !empty($skripsi->judul_skripsi) ? $skripsi->judul_skripsi : 'Belum ada judul';
        }

        // Ambil pengajuan terakhir
        $pengajuan = $this->Skripsi_pengajuan_model->get_latest_by_mahasiswa($skripsi->id_mahasiswa, $tahun_aktif->id);
        
        $judul_skripsi = 'Belum ada judul';
        
        if ($pengajuan) {
            // Cek apakah ada perubahan judul yang sudah di-approve sekprodi
            $perubahan_approved = $this->Skripsi_perubahan_judul_model->get_by_pengajuan($pengajuan->id);
            
            if ($perubahan_approved && isset($perubahan_approved->status_approve_sekprodi) && $perubahan_approved->status_approve_sekprodi === 'acc') {
                // Jika ada perubahan judul yang di-approve sekprodi, pakai judul terbaru
                $judul_skripsi = $perubahan_approved->judul_baru;
            } elseif (isset($pengajuan->judul_disetujui) && !empty($pengajuan->judul_disetujui)) {
                // Jika judul langsung di-ACC kedua pembimbing tanpa perubahan, pakai judul_disetujui
                $judul_skripsi = $pengajuan->judul_disetujui;
            } elseif (isset($pengajuan->judul_pengajuan) && !empty($pengajuan->judul_pengajuan)) {
                // Fallback ke judul_pengajuan
                $judul_skripsi = $pengajuan->judul_pengajuan;
            }
        }
        
        // Fallback ke judul dari mahasiswa_skripsi jika belum ada
        if ($judul_skripsi == 'Belum ada judul' && isset($skripsi->judul_skripsi) && !empty($skripsi->judul_skripsi)) {
            $judul_skripsi = $skripsi->judul_skripsi;
        }

        return $judul_skripsi;
    }
}
