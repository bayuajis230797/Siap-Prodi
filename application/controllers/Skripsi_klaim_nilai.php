<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Skripsi_klaim_nilai Controller
 * Menangani klaim nilai Skripsi oleh mahasiswa
 */
class Skripsi_klaim_nilai extends MY_Controller {

    public function __construct() {
        parent::__construct();
        $this->check_role('mahasiswa');
        $this->load->model('Skripsi_klaim_nilai_model');
        $this->load->model('Skripsi_berkas_klaim_upload_model');
        $this->load->model('Skripsi_berkas_klaim_nilai_model');
        $this->load->model('Skripsi_nilai_rekap_model');
        $this->load->model('Jadwal_skripsi_model');
        $this->load->model('Tahun_akademik_model');
        $this->load->library('form_validation');
        $this->load->helper('file');
    }

    /**
     * Index - form klaim nilai Skripsi
     */
    public function index() {
        $id_mahasiswa = $this->session->userdata('ref_id');
        $tahun_aktif = $this->Tahun_akademik_model->get_aktif();
        
        if (!$tahun_aktif) {
            $this->session->set_flashdata('error', 'Tidak ada tahun akademik aktif.');
            redirect('Halaman_utama');
        }

        // Cek apakah sudah ada penilaian dari penguji
        $this->load->model('Skripsi_model');
        $skripsi = $this->Skripsi_model->get_by_mahasiswa($id_mahasiswa, $tahun_aktif->id);
        if (!$skripsi) {
            $this->session->set_flashdata('error', 'Anda belum terdaftar untuk Skripsi.');
            redirect('Halaman_utama');
        }

        // Ambil jadwal sidang
        $jadwal_list = $this->Jadwal_skripsi_model->get_by_mahasiswa($skripsi->id);
        if (empty($jadwal_list)) {
            $this->session->set_flashdata('error', 'Anda belum memiliki jadwal sidang. Pastikan sidang sudah dijadwalkan.');
            redirect('Halaman_utama');
        }

        $jadwal = $jadwal_list[0];

        // Cek apakah semua penguji sudah memberikan nilai
        $this->load->model('Skripsi_nilai_sidang_model');
        $nilai_sidang_list = $this->Skripsi_nilai_sidang_model->get_by_jadwal($jadwal->id);
        if (empty($nilai_sidang_list) || count($nilai_sidang_list) < 3) {
            $this->session->set_flashdata('error', 'Penilaian dari penguji belum lengkap. Pastikan semua penguji sudah memberikan penilaian.');
            redirect('Halaman_utama');
        }

        // Cek apakah sudah ada klaim
        $klaim = $this->Skripsi_klaim_nilai_model->get_by_mahasiswa_skripsi($skripsi->id);
        
        // Ambil berkas yang sudah diupload
        $berkas_uploaded = array();
        if ($klaim) {
            $berkas_uploaded = $this->Skripsi_berkas_klaim_upload_model->get_by_klaim_nilai($klaim->id);
        }

        // Ambil daftar berkas yang diperlukan
        $berkas_list = $this->Skripsi_berkas_klaim_nilai_model->get_all(true);

        // Ambil nilai rekap jika sudah ada
        $nilai_rekap = $this->Skripsi_nilai_rekap_model->get_by_jadwal($jadwal->id);
        
        // Ambil nilai bimbingan dan sidang untuk ditampilkan
        $this->load->model('Skripsi_nilai_bimbingan_model');
        $nilai_bimbingan_list = $this->Skripsi_nilai_bimbingan_model->get_by_skripsi($skripsi->id);
        
        // Hitung nilai bimbingan final (rata-rata)
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
        
        // Hitung nilai sidang final (rata-rata)
        $nilai_sidang_final = null;
        if (!empty($nilai_sidang_list)) {
            $total_nilai = 0;
            $count = 0;
            foreach ($nilai_sidang_list as $ns) {
                if (isset($ns->nilai_total) && $ns->nilai_total > 0) {
                    $total_nilai += $ns->nilai_total;
                    $count++;
                }
            }
            if ($count > 0) {
                $nilai_sidang_final = $total_nilai / $count;
            }
        }
        
        // Hitung nilai akhir skripsi
        $nilai_akhir_skripsi = null;
        if ($nilai_rekap && isset($nilai_rekap->nilai_akhir) && $nilai_rekap->nilai_akhir !== null) {
            $nilai_akhir_skripsi = $nilai_rekap->nilai_akhir;
        } elseif ($nilai_bimbingan_final !== null && $nilai_sidang_final !== null) {
            $this->load->helper('skripsi');
            $nilai_akhir_skripsi = hitung_nilai_akhir_skripsi($nilai_bimbingan_final, $nilai_sidang_final);
        }
        
        // Ambil bobot nilai dan grade settings untuk ditampilkan
        $this->load->model('Bobot_nilai_setting_model');
        $bobot_array = $this->Bobot_nilai_setting_model->get_bobot_perhitungan('skripsi');
        $bobot_bimbingan = isset($bobot_array['bimbingan']) ? $bobot_array['bimbingan'] : 40;
        $bobot_sidang = isset($bobot_array['sidang']) ? $bobot_array['sidang'] : 60;
        
        $this->load->model('Grade_setting_model');
        $grade_list = $this->Grade_setting_model->get_by_tipe('skripsi');

        $this->load->library('Bimbingan_pasca_service');
        $pasca_status = $this->bimbingan_pasca_service->skripsi_klaim_status($skripsi->id);

        $data = array(
            'title' => 'Klaim Nilai Skripsi',
            'page_title' => 'Klaim Nilai Skripsi',
            'breadcrumb' => array(
                array('label' => 'Skripsi', 'url' => '#'),
                array('label' => 'Klaim Nilai', 'active' => true)
            ),
            'sidebar_menu' => generate_sidebar_menu('mahasiswa'),
            'klaim' => $klaim,
            'berkas_list' => $berkas_list,
            'berkas_uploaded' => $berkas_uploaded,
            'nilai_rekap' => $nilai_rekap,
            'nilai_bimbingan_final' => $nilai_bimbingan_final,
            'nilai_sidang_final' => $nilai_sidang_final,
            'nilai_akhir_skripsi' => $nilai_akhir_skripsi,
            'bobot_bimbingan' => $bobot_bimbingan,
            'bobot_sidang' => $bobot_sidang,
            'grade_list' => $grade_list,
            'tahun_aktif' => $tahun_aktif,
            'pasca_status' => $pasca_status,
            'boleh_klaim' => !empty($pasca_status['ok']),
        );

        $data['content'] = $this->load->view('mahasiswa/skripsi_klaim_nilai/index', $data, true);
        $this->load->view('layouts/main', $data);
    }

    /**
     * Submit klaim nilai Skripsi
     */
    public function submit() {
        $id_mahasiswa = $this->session->userdata('ref_id');
        $tahun_aktif = $this->Tahun_akademik_model->get_aktif();
        
        if (!$tahun_aktif) {
            $this->session->set_flashdata('error', 'Tidak ada tahun akademik aktif.');
            redirect('Halaman_utama');
        }

        // Cek apakah sudah ada penilaian dari penguji
        $this->load->model('Skripsi_model');
        $skripsi = $this->Skripsi_model->get_by_mahasiswa($id_mahasiswa, $tahun_aktif->id);
        if (!$skripsi) {
            $this->session->set_flashdata('error', 'Anda belum terdaftar untuk Skripsi.');
            redirect('Halaman_utama');
        }

        // Ambil jadwal sidang
        $jadwal_list = $this->Jadwal_skripsi_model->get_by_mahasiswa($skripsi->id);
        if (empty($jadwal_list)) {
            $this->session->set_flashdata('error', 'Anda belum memiliki jadwal sidang.');
            redirect('Halaman_utama');
        }

        $jadwal = $jadwal_list[0];

        $this->load->library('Bimbingan_pasca_service');
        $pasca_status = $this->bimbingan_pasca_service->skripsi_klaim_status($skripsi->id);
        if (empty($pasca_status['ok'])) {
            $this->session->set_flashdata('error', isset($pasca_status['message']) ? $pasca_status['message'] : 'Menunggu penguji menyatakan layak klaim nilai.');
            redirect('Skripsi_klaim_nilai');
        }

        // Cek apakah sudah ada klaim
        $klaim = $this->Skripsi_klaim_nilai_model->get_by_mahasiswa_skripsi($skripsi->id);
        
        // Ambil daftar berkas wajib
        $berkas_list = $this->Skripsi_berkas_klaim_nilai_model->get_all(true);
        $berkas_wajib = array();
        foreach ($berkas_list as $berkas) {
            if ($berkas->wajib) {
                $berkas_wajib[] = $berkas->id;
            }
        }

        // Validasi link wajib
        $errors = array();
        foreach ($berkas_wajib as $id_berkas) {
            $field_name = 'file_berkas_' . $id_berkas;
            if (empty($_FILES[$field_name]['name'])) {
                // Cek apakah sudah pernah diupload
                if ($klaim) {
                    $existing = $this->Skripsi_berkas_klaim_upload_model->get_by_klaim_berkas($klaim->id, $id_berkas);
                    if (!$existing) {
                        $errors[] = "Berkas wajib belum diupload";
                        break;
                    }
                } else {
                    $errors[] = "Berkas wajib belum diupload";
                    break;
                }
            }
        }

        if (!empty($errors)) {
            $this->session->set_flashdata('error', implode(', ', $errors));
            redirect('Skripsi_klaim_nilai');
        }

        // Buat atau update klaim
        if (!$klaim) {
            $klaim_data = array(
                'id_mahasiswa_skripsi' => $skripsi->id,
                'id_jadwal_skripsi' => $jadwal->id,
                'id_tahun_akademik' => $tahun_aktif->id,
                'status' => 'menunggu'
            );
            $klaim_id = $this->Skripsi_klaim_nilai_model->create($klaim_data);
        } else {
            $klaim_id = $klaim->id;
            // Update status jika ditolak sebelumnya
            if ($klaim->status == 'ditolak') {
                $this->Skripsi_klaim_nilai_model->update($klaim_id, array('status' => 'menunggu'));
            }
        }

        $needs_upload = false;
        foreach ($berkas_list as $berkas) {
            $field_name = 'file_berkas_' . $berkas->id;
            if (!empty($_FILES[$field_name]['name'])) {
                $needs_upload = true;
                break;
            }
        }
        if ($needs_upload) {
            $this->load->helper('google_drive');
            require_google_drive_or_redirect();
        }
        $this->load->library('Upload_to_drive');
        $share_emails = array();
        $sekprodi_owner = $this->upload_to_drive->find_connected_sekprodi_user_id();
        if ($sekprodi_owner) {
            $email = $this->upload_to_drive->get_drive_email($sekprodi_owner);
            if ($email) {
                $share_emails[] = $email;
            }
        }
        $upload_opts = $this->upload_to_drive->default_options(array('SIAP', 'Skripsi', 'klaim_nilai'), $share_emails);

        foreach ($berkas_list as $berkas) {
            $field_name = 'file_berkas_' . $berkas->id;
            if (empty($_FILES[$field_name]['name'])) {
                continue;
            }
            $upload = $this->upload_to_drive->handle_upload($field_name, $upload_opts);
            if (empty($upload['ok'])) {
                $this->session->set_flashdata('error', (isset($upload['message']) ? $upload['message'] : 'Gagal upload berkas.') . ' (' . $berkas->nama_berkas . ')');
                redirect('Skripsi_klaim_nilai');
            }

            $existing = $this->Skripsi_berkas_klaim_upload_model->get_by_klaim_berkas($klaim_id, $berkas->id);
            $row = array(
                'file_path' => $upload['webViewLink'],
                'file_name' => !empty($upload['file_name']) ? $upload['file_name'] : $berkas->nama_berkas,
                'file_size' => null
            );
            if ($existing) {
                $this->Skripsi_berkas_klaim_upload_model->update($existing->id, $row);
            } else {
                $this->Skripsi_berkas_klaim_upload_model->create($row + array(
                    'id_klaim_nilai' => $klaim_id,
                    'id_berkas_klaim_nilai' => $berkas->id
                ));
            }
        }

        $this->load->helper(array('notifikasi', 'sekprodi_badge'));
        notify_sekprodi(
            'Klaim Nilai Skripsi Baru',
            'Ada klaim nilai skripsi baru yang menunggu verifikasi.',
            site_url('Skripsi_verifikasi_klaim'),
            'skripsi_klaim'
        );
        $this->session->set_flashdata('success', 'Klaim nilai berhasil dikirim. Menunggu persetujuan dari sekprodi.');
        redirect('Skripsi_klaim_nilai');
    }
}





