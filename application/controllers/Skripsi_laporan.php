<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Skripsi_laporan Controller
 * Laporan Skripsi untuk sekprodi berisi daftar nama pembimbing, penguji, jadwal sidang, dan Nilai Akhir
 */
class Skripsi_laporan extends MY_Controller {

    public function __construct() {
        parent::__construct();
        $this->check_role('sekprodi');
        $this->load->model('Skripsi_model');
        $this->load->model('Skripsi_pengajuan_model');
        $this->load->model('Skripsi_perubahan_judul_model');
        $this->load->model('Jadwal_skripsi_model');
        $this->load->model('Tahun_akademik_model');
        $this->load->model('Skripsi_nilai_bimbingan_model');
        $this->load->model('Skripsi_nilai_bimbingan_detail_model');
        $this->load->model('Skripsi_nilai_sidang_model');
        $this->load->model('Skripsi_nilai_sidang_detail_model');
        $this->load->model('Skripsi_aspek_penilaian_model');
        $this->load->model('Skripsi_item_penilaian_model');
        $this->load->library('Skripsi_penilaian');
        $this->load->database();
    }

    /**
     * Index - Daftar laporan Skripsi
     */
    public function index() {
        $tahun_aktif = $this->Tahun_akademik_model->get_aktif();
        if (!$tahun_aktif) {
            $this->session->set_flashdata('error', 'Tidak ada tahun akademik aktif.');
            redirect('Dashboard');
        }

        // Sync nilai yang belum tersinkronkan sebelum mengambil data laporan
        $this->sync_all_nilai($tahun_aktif->id);

        // Ambil semua data laporan Skripsi
        $laporan_list = $this->get_laporan_data($tahun_aktif->id);

        $data = array(
            'title' => 'Laporan Skripsi',
            'page_title' => 'Laporan Skripsi',
            'breadcrumb' => array(
                array('label' => 'Laporan', 'url' => site_url('Laporan')),
                array('label' => 'Laporan Skripsi', 'active' => true)
            ),
            'sidebar_menu' => generate_sidebar_menu('sekprodi'),
            'laporan_list' => $laporan_list,
            'tahun_aktif' => $tahun_aktif
        );

        $data['content'] = $this->load->view('sekprodi/skripsi_laporan_index', $data, true);
        $this->load->view('layouts/main', $data);
    }

    /**
     * Export laporan Skripsi ke CSV
     */
    public function export_excel() {
        $tahun_aktif = $this->Tahun_akademik_model->get_aktif();
        if (!$tahun_aktif) {
            $this->session->set_flashdata('error', 'Tidak ada tahun akademik aktif.');
            redirect('Skripsi_laporan');
        }

        // Ambil semua data laporan Skripsi
        $laporan_list = $this->get_laporan_data($tahun_aktif->id);

        if (empty($laporan_list)) {
            $this->session->set_flashdata('warning', 'Tidak ada data laporan Skripsi untuk di-export.');
            redirect('Skripsi_laporan');
        }

        $filename_base = 'Laporan_Skripsi_' . $tahun_aktif->tahun_akademik . '_' . ucfirst($tahun_aktif->semester) . '_' . date('YmdHis');
        $filename = $filename_base . '.csv';

        // Set headers untuk download CSV
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment;filename="' . $filename . '"');
        header('Cache-Control: max-age=0');

        $output = fopen('php://output', 'w');
        // UTF-8 BOM untuk Excel
        fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));

        // Header
        fputcsv($output, array('LAPORAN SKRIPSI'));
        fputcsv($output, array('Tahun Akademik: ' . $tahun_aktif->tahun_akademik . ' ' . ucfirst($tahun_aktif->semester)));
        fputcsv($output, array('Tanggal Export: ' . date('d/m/Y H:i:s')));
        fputcsv($output, array('')); // Empty row
        fputcsv($output, array('No', 'NIM', 'Nama Mahasiswa', 'Kelas', 'Judul Skripsi', 'Pembimbing 1', 'Pembimbing 2', 'Ketua Penguji', 'Anggota Penguji 1', 'Anggota Penguji 2', 'Tanggal Sidang', 'Jam', 'Ruangan', 'Nilai Bimbingan', 'Nilai Sidang', 'Nilai Akhir', 'Grade'));

        // Data
        $no = 1;
        foreach ($laporan_list as $laporan) {
            fputcsv($output, array(
                $no++,
                $laporan->nim ?: '-',
                $laporan->nama_mahasiswa ?: '-',
                $laporan->kelas ?: '-',
                $laporan->judul_skripsi ?: '-',
                $laporan->pembimbing_1_nama ?: '-',
                $laporan->pembimbing_2_nama ?: '-',
                $laporan->ketua_penguji ?: '-',
                $laporan->anggota_penguji_1 ?: '-',
                $laporan->anggota_penguji_2 ?: '-',
                $laporan->tanggal_sidang ? date('d/m/Y', strtotime($laporan->tanggal_sidang)) : '-',
                isset($laporan->jam_sidang) ? ($laporan->jam_sidang ?: '-') : '-',
                isset($laporan->ruangan) ? ($laporan->ruangan ?: '-') : '-',
                (isset($laporan->nilai_bimbingan_final) && $laporan->nilai_bimbingan_final !== null && $laporan->nilai_bimbingan_final !== '' && (is_numeric($laporan->nilai_bimbingan_final) || is_float($laporan->nilai_bimbingan_final))) ? number_format((float)$laporan->nilai_bimbingan_final, 2) : '-',
                (isset($laporan->nilai_sidang) && $laporan->nilai_sidang !== null && $laporan->nilai_sidang !== '' && (is_numeric($laporan->nilai_sidang) || is_float($laporan->nilai_sidang))) ? number_format((float)$laporan->nilai_sidang, 2) : '-',
                (isset($laporan->nilai_skripsi_final) && $laporan->nilai_skripsi_final !== null && $laporan->nilai_skripsi_final !== '' && (is_numeric($laporan->nilai_skripsi_final) || is_float($laporan->nilai_skripsi_final))) ? number_format((float)$laporan->nilai_skripsi_final, 2) : '-',
                (isset($laporan->nilai_huruf) && trim($laporan->nilai_huruf) !== '') ? trim($laporan->nilai_huruf) : '-'
            ));
        }

        fclose($output);
        exit;
    }

    /**
     * Sync semua nilai yang belum tersinkronkan
     */
    private function sync_all_nilai($id_tahun_akademik) {
        // Ambil semua mahasiswa Skripsi
        $this->db->select('ms.id');
        $this->db->from('mahasiswa_skripsi ms');
        $this->db->where('ms.id_tahun_akademik', $id_tahun_akademik);
        $mahasiswa_skripsi_list = $this->db->get()->result();
        
        // Sync nilai untuk setiap mahasiswa
        foreach ($mahasiswa_skripsi_list as $ms) {
            // Sync nilai bimbingan
            $this->skripsi_penilaian->sync_bimbingan($ms->id);
            // Sync nilai sidang (akan otomatis sync final juga)
            $this->skripsi_penilaian->sync_sidang($ms->id);
        }
    }

    /**
     * Ambil data laporan Skripsi lengkap
     */
    private function get_laporan_data($id_tahun_akademik) {
        // Ambil semua mahasiswa yang terdaftar Skripsi dengan semua field termasuk nilai
        // Pastikan kolom nilai diambil secara eksplisit
        $this->db->select('ms.id, ms.id_mahasiswa, ms.id_tahun_akademik, ms.status, 
                          ms.acc_pembimbing_1, ms.acc_pembimbing_2, 
                          ms.id_pembimbing_1, ms.id_pembimbing_2,
                          ms.nilai_bimbingan_p1, ms.nilai_bimbingan_p2, 
                          ms.nilai_bimbingan_final, ms.nilai_sidang, 
                          ms.nilai_skripsi_final, ms.nilai_huruf,
                          ms.judul_skripsi, ms.tanggal_mulai, ms.tanggal_selesai,
                          m.nim, m.nama_mahasiswa, m.kelas');
        $this->db->from('mahasiswa_skripsi ms');
        $this->db->join('mahasiswa m', 'm.id = ms.id_mahasiswa', 'left');
        $this->db->where('ms.id_tahun_akademik', $id_tahun_akademik);
        $this->db->order_by('m.kelas', 'ASC');
        $this->db->order_by('m.nim', 'ASC');
        $mahasiswa_skripsi_list = $this->db->get()->result();
        
        $laporan_list = array();
        $this->load->helper('skripsi');
        
        foreach ($mahasiswa_skripsi_list as $ms) {
            $item = new stdClass();
            $item->id_mahasiswa = $ms->id_mahasiswa;
            $item->nim = $ms->nim;
            $item->nama_mahasiswa = $ms->nama_mahasiswa;
            $item->kelas = $ms->kelas;
            
            // Ambil judul skripsi terbaru
            $pengajuan = $this->Skripsi_pengajuan_model->get_latest_by_mahasiswa($ms->id_mahasiswa, $id_tahun_akademik);
            
            $judul_skripsi = null;
            if ($pengajuan) {
                // Cek apakah ada perubahan judul yang sudah di-approve sekprodi
                $perubahan_approved = $this->Skripsi_perubahan_judul_model->get_by_pengajuan($pengajuan->id);
                if ($perubahan_approved && isset($perubahan_approved->status_approve_sekprodi) && $perubahan_approved->status_approve_sekprodi === 'acc') {
                    // Gunakan judul baru dari perubahan judul yang sudah di-approve
                    $judul_skripsi = $perubahan_approved->judul_baru;
                } elseif (isset($pengajuan->judul_disetujui) && !empty($pengajuan->judul_disetujui)) {
                    // Gunakan judul_disetujui dari pengajuan
                    $judul_skripsi = $pengajuan->judul_disetujui;
                } elseif (isset($pengajuan->judul_pengajuan) && !empty($pengajuan->judul_pengajuan)) {
                    // Fallback ke judul_pengajuan
                    $judul_skripsi = $pengajuan->judul_pengajuan;
                }
            }
            
            // Jika masih belum ada, gunakan dari mahasiswa_skripsi
            if (empty($judul_skripsi) && isset($ms->judul_skripsi) && !empty($ms->judul_skripsi)) {
                $judul_skripsi = $ms->judul_skripsi;
            }
            
            $item->judul_skripsi = $judul_skripsi;
            
            // Ambil data pembimbing
            $this->db->select('d1.nama_dosen as pembimbing_1_nama, d1.nidn as pembimbing_1_nidn, 
                             d2.nama_dosen as pembimbing_2_nama, d2.nidn as pembimbing_2_nidn');
            $this->db->from('mahasiswa_skripsi ms_pembimbing');
            $this->db->join('dosen d1', 'd1.id = ms_pembimbing.id_pembimbing_1', 'left');
            $this->db->join('dosen d2', 'd2.id = ms_pembimbing.id_pembimbing_2', 'left');
            $this->db->where('ms_pembimbing.id', $ms->id);
            $pembimbing_data = $this->db->get()->row();
            
            if ($pembimbing_data) {
                $item->pembimbing_1_nama = $pembimbing_data->pembimbing_1_nama;
                $item->pembimbing_1_nidn = $pembimbing_data->pembimbing_1_nidn;
                $item->pembimbing_2_nama = $pembimbing_data->pembimbing_2_nama;
                $item->pembimbing_2_nidn = $pembimbing_data->pembimbing_2_nidn;
            } else {
                $item->pembimbing_1_nama = null;
                $item->pembimbing_1_nidn = null;
                $item->pembimbing_2_nama = null;
                $item->pembimbing_2_nidn = null;
            }
            
            // Ambil jadwal sidang (terbaru)
            $jadwal_list = $this->Jadwal_skripsi_model->get_by_mahasiswa($ms->id);
            if (!empty($jadwal_list)) {
                $jadwal = $jadwal_list[0];
                $item->ketua_penguji = isset($jadwal->ketua_penguji) ? $jadwal->ketua_penguji : null;
                $item->anggota_penguji_1 = isset($jadwal->anggota_penguji_1) ? $jadwal->anggota_penguji_1 : null;
                $item->anggota_penguji_2 = isset($jadwal->anggota_penguji_2) ? $jadwal->anggota_penguji_2 : null;
                $item->tanggal_sidang = isset($jadwal->tanggal_ujian) ? $jadwal->tanggal_ujian : (isset($jadwal->tanggal) ? $jadwal->tanggal : null);
                
                // Ambil jam sidang
                if (isset($jadwal->jam_mulai) && isset($jadwal->jam_selesai)) {
                    $item->jam_sidang = date('H:i', strtotime($jadwal->jam_mulai)) . ' - ' . date('H:i', strtotime($jadwal->jam_selesai));
                } else {
                    $item->jam_sidang = null;
                }
                
                // Ambil ruangan
                $item->ruangan = isset($jadwal->ruangan) ? $jadwal->ruangan : null;
            } else {
                $item->ketua_penguji = null;
                $item->anggota_penguji_1 = null;
                $item->anggota_penguji_2 = null;
                $item->tanggal_sidang = null;
                $item->jam_sidang = null;
                $item->ruangan = null;
            }
            
            // Ambil nilai secara eksplisit dari object $ms
            $item->nilai_bimbingan_final = ($ms->nilai_bimbingan_final !== null && $ms->nilai_bimbingan_final !== '') 
                ? (float)$ms->nilai_bimbingan_final : null;
            $item->nilai_sidang = ($ms->nilai_sidang !== null && $ms->nilai_sidang !== '') 
                ? (float)$ms->nilai_sidang : null;
            $item->nilai_skripsi_final = ($ms->nilai_skripsi_final !== null && $ms->nilai_skripsi_final !== '') 
                ? (float)$ms->nilai_skripsi_final : null;
            // Nilai huruf: selalu dari konversi sesuai Setting Grade (admin)
            $item->nilai_huruf = $item->nilai_skripsi_final !== null ? konversi_nilai_huruf_skripsi($item->nilai_skripsi_final) : null;
            
            $laporan_list[] = $item;
        }
        
        // Sort by kelas, then NIM
        usort($laporan_list, function($a, $b) {
            if ($a->kelas != $b->kelas) {
                return strcmp($a->kelas, $b->kelas);
            }
            return strcmp($a->nim, $b->nim);
        });
        
        return $laporan_list;
    }

    /**
     * Export rekap nilai Skripsi ke CSV
     * Berisi rekap semua item butir penilaian dari 2 pembimbing dan 3 penguji
     */
    public function export_rekap_nilai() {
        $tahun_aktif = $this->Tahun_akademik_model->get_aktif();
        if (!$tahun_aktif) {
            $this->session->set_flashdata('error', 'Tidak ada tahun akademik aktif.');
            redirect('Skripsi_laporan');
        }

        // Sync nilai yang belum tersinkronkan sebelum mengambil data
        $this->sync_all_nilai($tahun_aktif->id);

        // Ambil semua mahasiswa yang terdaftar Skripsi
        $this->db->select('ms.id, ms.id_mahasiswa, m.nim, m.nama_mahasiswa');
        $this->db->from('mahasiswa_skripsi ms');
        $this->db->join('mahasiswa m', 'm.id = ms.id_mahasiswa', 'left');
        $this->db->where('ms.id_tahun_akademik', $tahun_aktif->id);
        $this->db->order_by('m.kelas', 'ASC');
        $this->db->order_by('m.nim', 'ASC');
        $mahasiswa_skripsi_list = $this->db->get()->result();
        
        if (empty($mahasiswa_skripsi_list)) {
            $this->session->set_flashdata('warning', 'Tidak ada data mahasiswa Skripsi untuk di-export.');
            redirect('Skripsi_laporan');
        }

        // Ambil semua item penilaian untuk pembimbing
        $aspek_bimbingan = $this->Skripsi_aspek_penilaian_model->get_by_tipe('pembimbing', true);
        $items_bimbingan = array();
        foreach ($aspek_bimbingan as $aspek) {
            $aspek_items = $this->Skripsi_item_penilaian_model->get_by_aspek($aspek->id, true);
            foreach ($aspek_items as $item) {
                $items_bimbingan[] = $item;
            }
        }

        // Ambil semua item penilaian untuk penguji
        $aspek_penguji = $this->Skripsi_aspek_penilaian_model->get_by_tipe('penguji', true);
        $items_penguji = array();
        foreach ($aspek_penguji as $aspek) {
            $aspek_items = $this->Skripsi_item_penilaian_model->get_by_aspek($aspek->id, true);
            foreach ($aspek_items as $item) {
                $items_penguji[] = $item;
            }
        }

        // Prepare filename
        $filename_base = 'Rekap_Nilai_Skripsi_' . $tahun_aktif->tahun_akademik . '_' . ucfirst($tahun_aktif->semester) . '_' . date('YmdHis');
        $filename = $filename_base . '.csv';

        // Set headers untuk download CSV
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment;filename="' . $filename . '"');
        header('Cache-Control: max-age=0');

        // Buka output stream
        $output = fopen('php://output', 'w');
        
        // UTF-8 BOM untuk Excel
        fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));

        // Header 1: Judul
        fputcsv($output, array('REKAP NILAI SKRIPSI'));

        // Header 2: Tahun Akademik
        fputcsv($output, array('Tahun Akademik: ' . $tahun_aktif->tahun_akademik . ' ' . ucfirst($tahun_aktif->semester)));

        // Baris kosong
        fputcsv($output, array(''));

        // Header kolom
        $header_row = array('No', 'Nama');
        
        // Tambahkan header untuk Pembimbing 1
        foreach ($items_bimbingan as $index => $item) {
            $header_row[] = 'Pembimbing 1 - Item ' . ($index + 1);
        }
        
        // Tambahkan header untuk Pembimbing 2
        foreach ($items_bimbingan as $index => $item) {
            $header_row[] = 'Pembimbing 2 - Item ' . ($index + 1);
        }
        
        // Tambahkan header untuk Ketua Penguji
        foreach ($items_penguji as $index => $item) {
            $header_row[] = 'Ketua Penguji - Item ' . ($index + 1);
        }
        
        // Tambahkan header untuk Anggota Penguji 1
        foreach ($items_penguji as $index => $item) {
            $header_row[] = 'Anggota Penguji 1 - Item ' . ($index + 1);
        }
        
        // Tambahkan header untuk Anggota Penguji 2
        foreach ($items_penguji as $index => $item) {
            $header_row[] = 'Anggota Penguji 2 - Item ' . ($index + 1);
        }
        
        // Tambahkan Nilai Akhir dan Grade
        $header_row[] = 'Nilai Akhir';
        $header_row[] = 'Grade';
        
        fputcsv($output, $header_row);

        // Data rows
        $no = 1;
        foreach ($mahasiswa_skripsi_list as $ms) {
            $data_row = array();
            
            // No
            $data_row[] = $no++;
            
            // Nama
            $data_row[] = $ms->nama_mahasiswa;

            // Ambil nilai bimbingan untuk Pembimbing 1 (tipe_pembimbing = 1)
            $nilai_bimbingan_p1 = $this->Skripsi_nilai_bimbingan_model->get_by_skripsi_tipe($ms->id, 1);
            $nilai_p1_by_item = array();
            if ($nilai_bimbingan_p1) {
                $details_p1 = $this->Skripsi_nilai_bimbingan_detail_model->get_by_nilai_bimbingan($nilai_bimbingan_p1->id);
                foreach ($details_p1 as $detail) {
                    $nilai_p1_by_item[$detail->id_item_penilaian] = $detail->nilai;
                }
            }
            
            // Nilai per item untuk Pembimbing 1
            foreach ($items_bimbingan as $item) {
                $nilai = isset($nilai_p1_by_item[$item->id]) ? $nilai_p1_by_item[$item->id] : '';
                $data_row[] = $nilai !== '' ? number_format($nilai, 2) : '-';
            }

            // Ambil nilai bimbingan untuk Pembimbing 2 (tipe_pembimbing = 2)
            $nilai_bimbingan_p2 = $this->Skripsi_nilai_bimbingan_model->get_by_skripsi_tipe($ms->id, 2);
            $nilai_p2_by_item = array();
            if ($nilai_bimbingan_p2) {
                $details_p2 = $this->Skripsi_nilai_bimbingan_detail_model->get_by_nilai_bimbingan($nilai_bimbingan_p2->id);
                foreach ($details_p2 as $detail) {
                    $nilai_p2_by_item[$detail->id_item_penilaian] = $detail->nilai;
                }
            }
            
            // Nilai per item untuk Pembimbing 2
            foreach ($items_bimbingan as $item) {
                $nilai = isset($nilai_p2_by_item[$item->id]) ? $nilai_p2_by_item[$item->id] : '';
                $data_row[] = $nilai !== '' ? number_format($nilai, 2) : '-';
            }

            // Ambil jadwal sidang untuk mendapatkan id_jadwal_skripsi
            $jadwal_list = $this->Jadwal_skripsi_model->get_by_mahasiswa($ms->id);
            $id_jadwal = null;
            if (!empty($jadwal_list)) {
                $id_jadwal = $jadwal_list[0]->id;
            }

            // Ambil nilai sidang untuk Ketua Penguji (peran = 'ketua')
            $nilai_sidang_ketua = null;
            $nilai_ketua_by_item = array();
            if ($id_jadwal) {
                $nilai_sidang_ketua = $this->Skripsi_nilai_sidang_model->get_by_jadwal_penguji($id_jadwal, 'ketua');
                if ($nilai_sidang_ketua) {
                    $details_ketua = $this->Skripsi_nilai_sidang_detail_model->get_by_nilai_sidang($nilai_sidang_ketua->id);
                    foreach ($details_ketua as $detail) {
                        $nilai_ketua_by_item[$detail->id_item_penilaian] = $detail->nilai;
                    }
                }
            }
            
            // Nilai per item untuk Ketua Penguji
            foreach ($items_penguji as $item) {
                $nilai = isset($nilai_ketua_by_item[$item->id]) ? $nilai_ketua_by_item[$item->id] : '';
                $data_row[] = $nilai !== '' ? number_format($nilai, 2) : '-';
            }

            // Ambil nilai sidang untuk Anggota Penguji 1 (peran = 'anggota_1')
            $nilai_sidang_anggota1 = null;
            $nilai_anggota1_by_item = array();
            if ($id_jadwal) {
                $nilai_sidang_anggota1 = $this->Skripsi_nilai_sidang_model->get_by_jadwal_penguji($id_jadwal, 'anggota_1');
                if ($nilai_sidang_anggota1) {
                    $details_anggota1 = $this->Skripsi_nilai_sidang_detail_model->get_by_nilai_sidang($nilai_sidang_anggota1->id);
                    foreach ($details_anggota1 as $detail) {
                        $nilai_anggota1_by_item[$detail->id_item_penilaian] = $detail->nilai;
                    }
                }
            }
            
            // Nilai per item untuk Anggota Penguji 1
            foreach ($items_penguji as $item) {
                $nilai = isset($nilai_anggota1_by_item[$item->id]) ? $nilai_anggota1_by_item[$item->id] : '';
                $data_row[] = $nilai !== '' ? number_format($nilai, 2) : '-';
            }

            // Ambil nilai sidang untuk Anggota Penguji 2 (peran = 'anggota_2')
            $nilai_sidang_anggota2 = null;
            $nilai_anggota2_by_item = array();
            if ($id_jadwal) {
                $nilai_sidang_anggota2 = $this->Skripsi_nilai_sidang_model->get_by_jadwal_penguji($id_jadwal, 'anggota_2');
                if ($nilai_sidang_anggota2) {
                    $details_anggota2 = $this->Skripsi_nilai_sidang_detail_model->get_by_nilai_sidang($nilai_sidang_anggota2->id);
                    foreach ($details_anggota2 as $detail) {
                        $nilai_anggota2_by_item[$detail->id_item_penilaian] = $detail->nilai;
                    }
                }
            }
            
            // Nilai per item untuk Anggota Penguji 2
            foreach ($items_penguji as $item) {
                $nilai = isset($nilai_anggota2_by_item[$item->id]) ? $nilai_anggota2_by_item[$item->id] : '';
                $data_row[] = $nilai !== '' ? number_format($nilai, 2) : '-';
            }

            // Nilai Akhir
            $skripsi = $this->Skripsi_model->get_by_id($ms->id);
            $nilai_akhir = $skripsi && isset($skripsi->nilai_skripsi_final) && $skripsi->nilai_skripsi_final !== null && $skripsi->nilai_skripsi_final !== '' 
                ? (float)$skripsi->nilai_skripsi_final : '';
            $data_row[] = $nilai_akhir !== '' ? number_format($nilai_akhir, 2) : '-';

            // Grade
            $grade = $skripsi && isset($skripsi->nilai_huruf) && trim($skripsi->nilai_huruf) !== '' 
                ? trim($skripsi->nilai_huruf) : '';
            $data_row[] = $grade ?: '-';

            fputcsv($output, $data_row);
        }

        fclose($output);
        exit;
    }
}

