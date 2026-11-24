<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Jadwal PKL Dosen Controller
 * Menampilkan jadwal ujian PKL dimana dosen menjadi penguji
 * Dapat input penilaian dan catatan revisi pasca sidang
 */
class Jadwal_pkl_dosen extends MY_Controller {

    public function __construct() {
        parent::__construct();
        $this->check_role('dosen');
        $this->load->model('Jadwal_pkl_model');
        $this->load->model('Pkl_penilaian_model');
        $this->load->model('Pkl_penilaian_detail_model');
        $this->load->model('Pkl_aspek_penilaian_model');
        $this->load->model('Pkl_item_penilaian_model');
        $this->load->model('Pkl_pendaftaran_sidang_model');
        $this->load->model('Pkl_berkas_sidang_upload_model');
        $this->load->model('Tahun_akademik_model');
        $this->load->helper('pkl');
        $this->load->library('form_validation');
    }

    /**
     * Index - Daftar jadwal PKL dimana dosen menjadi penguji
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

        // Ambil jadwal PKL dimana dosen ini menjadi penguji
        $jadwal_list = $this->Jadwal_pkl_model->get_by_penguji($id_dosen, $id_tahun_akademik);

        // Untuk setiap jadwal, cek apakah sudah ada penilaian
        foreach ($jadwal_list as &$jadwal) {
            $penilaian = $this->Pkl_penilaian_model->get_by_mahasiswa_penilai(
                $jadwal->id_mahasiswa,
                $id_tahun_akademik,
                $id_dosen,
                'penguji'
            );
            $jadwal->penilaian = $penilaian;
        }
        unset($jadwal);

        $data = array(
            'title' => 'Jadwal Sidang PKL',
            'page_title' => 'Jadwal Sidang PKL',
            'breadcrumb' => array(
                array('label' => 'Jadwal', 'url' => '#'),
                array('label' => 'Jadwal Sidang PKL', 'active' => true)
            ),
            'sidebar_menu' => generate_sidebar_menu('dosen'),
            'jadwal_list' => $jadwal_list,
            'tahun_aktif' => $tahun_aktif
        );

        $data['content'] = $this->load->view('dosen/jadwal_pkl', $data, true);
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
            redirect('Jadwal_pkl_dosen');
        }

        // Ambil jadwal PKL
        $jadwal = $this->Jadwal_pkl_model->get_by_id($id);
        if (!$jadwal || $jadwal->id_penguji != $id_dosen) {
            $this->session->set_flashdata('error', 'Data jadwal tidak ditemukan atau Anda bukan penguji untuk jadwal ini.');
            redirect('Jadwal_pkl_dosen');
        }

        // Ambil aspek penilaian untuk penguji
        $aspek_list = $this->Pkl_aspek_penilaian_model->get_by_ditunjukkan_untuk('penguji', true);
        
        if (empty($aspek_list)) {
            $this->session->set_flashdata('error', 'Tidak ada aspek penilaian yang ditugaskan untuk penguji. Silakan hubungi admin untuk setting aspek penilaian.');
            redirect('Jadwal_pkl_dosen');
        }
        
        // Untuk setiap aspek, ambil item penilaiannya
        foreach ($aspek_list as &$aspek) {
            $aspek->items = $this->Pkl_item_penilaian_model->get_by_aspek($aspek->id, true);
        }
        unset($aspek);

        // Cek apakah sudah ada penilaian
        $penilaian = $this->Pkl_penilaian_model->get_by_mahasiswa_penilai(
            $jadwal->id_mahasiswa,
            $tahun_aktif->id,
            $id_dosen,
            'penguji'
        );

        // Ambil detail penilaian jika sudah ada
        $penilaian_details = array();
        if ($penilaian) {
            $details = $this->Pkl_penilaian_detail_model->get_by_penilaian($penilaian->id);
            foreach ($details as $detail) {
                $penilaian_details[$detail->id_item_penilaian] = $detail->nilai;
            }
        }

        if ($this->input->method() === 'post') {
            // Validasi input nilai per item
            $errors = array();
            $nilai_items = array();
            
            foreach ($aspek_list as $aspek) {
                foreach ($aspek->items as $item) {
                    $field_name = 'nilai_item_' . $item->id;
                    $nilai = $this->input->post($field_name);
                    
                    if ($nilai === '' || $nilai === null) {
                        $errors[] = "Nilai untuk item '{$item->pertanyaan}' belum diisi";
                    } elseif (!is_numeric($nilai) || $nilai < 0 || $nilai > 100) {
                        $errors[] = "Nilai untuk item '{$item->pertanyaan}' harus antara 0-100";
                    } else {
                        $nilai_items[$item->id] = array(
                            'nilai' => floatval($nilai),
                            'bobot' => floatval($item->bobot),
                            'id_aspek' => $aspek->id
                        );
                    }
                }
            }

            // Validasi catatan revisi
            $catatan_revisi = $this->input->post('catatan_revisi');

            if (!empty($errors)) {
                $this->session->set_flashdata('error', implode('<br>', $errors));
            } else {
                // Hitung nilai P untuk setiap aspek
                $nilai_p = array();
                foreach ($aspek_list as $aspek) {
                    $items_aspek = array();
                    foreach ($aspek->items as $item) {
                        if (isset($nilai_items[$item->id])) {
                            $items_aspek[] = $nilai_items[$item->id];
                        }
                    }
                    $nilai_p[$aspek->kode_aspek] = hitung_nilai_p_dari_items($items_aspek);
                }

                // Set nilai P yang tidak diinput penguji menjadi 0
                $nilai_p1 = isset($nilai_p['P1']) ? $nilai_p['P1'] : 0;
                $nilai_p2 = isset($nilai_p['P2']) ? $nilai_p['P2'] : 0;
                $nilai_p3 = isset($nilai_p['P3']) ? $nilai_p['P3'] : 0;
                $nilai_p4 = isset($nilai_p['P4']) ? $nilai_p['P4'] : 0;
                $nilai_p5 = isset($nilai_p['P5']) ? $nilai_p['P5'] : 0;
                $nilai_p6 = isset($nilai_p['P6']) ? $nilai_p['P6'] : 0;

                // Simpan atau update penilaian
                $penilaian_data = array(
                    'id_mahasiswa' => $jadwal->id_mahasiswa,
                    'id_tahun_akademik' => $tahun_aktif->id,
                    'id_penilai' => $id_dosen,
                    'tipe_penilai' => 'penguji',
                    'nilai_p1' => $nilai_p1,
                    'nilai_p2' => $nilai_p2,
                    'nilai_p3' => $nilai_p3,
                    'nilai_p4' => $nilai_p4,
                    'nilai_p5' => $nilai_p5,
                    'nilai_p6' => $nilai_p6,
                    'catatan' => $catatan_revisi ? trim($catatan_revisi) : null,
                    'tanggal_penilaian' => date('Y-m-d'),
                    'id_jadwal_pkl' => $id
                );

                if ($penilaian) {
                    // Update penilaian
                    $penilaian_id = $penilaian->id;
                    $this->Pkl_penilaian_model->update($penilaian_id, $penilaian_data);
                    
                    // Hapus detail lama
                    $this->Pkl_penilaian_detail_model->delete_by_penilaian($penilaian_id);
                } else {
                    // Create penilaian
                    $penilaian_id = $this->Pkl_penilaian_model->create($penilaian_data);
                }

                // Simpan detail penilaian per item
                foreach ($nilai_items as $id_item => $data_item) {
                    $this->Pkl_penilaian_detail_model->create(array(
                        'id_penilaian' => $penilaian_id,
                        'id_item_penilaian' => $id_item,
                        'nilai' => $data_item['nilai']
                    ));
                }

                // Update status jadwal menjadi selesai jika sudah dinilai
                $this->Jadwal_pkl_model->update($id, array('status' => 'selesai'));

                // Aktifkan thread bimbingan pasca sidang
                $this->load->library('Bimbingan_pasca_service');
                $this->bimbingan_pasca_service->ensure_pkl_by_jadwal($id);

                $this->session->set_flashdata('success', 'Penilaian dan catatan revisi berhasil disimpan.');
                redirect('Jadwal_pkl_dosen');
            }
        }

        $data = array(
            'title' => 'Detail Jadwal & Penilaian PKL',
            'page_title' => 'Detail Jadwal & Penilaian PKL',
            'breadcrumb' => array(
                array('label' => 'Jadwal', 'url' => '#'),
                array('label' => 'Jadwal Sidang PKL', 'url' => site_url('Jadwal_pkl_dosen')),
                array('label' => 'Detail & Penilaian', 'active' => true)
            ),
            'sidebar_menu' => generate_sidebar_menu('dosen'),
            'jadwal' => $jadwal,
            'aspek_list' => $aspek_list,
            'penilaian' => $penilaian,
            'penilaian_details' => $penilaian_details,
            'tahun_aktif' => $tahun_aktif
        );

        $data['content'] = $this->load->view('dosen/jadwal_pkl_detail', $data, true);
        $this->load->view('layouts/main', $data);
    }

    /**
     * Tampilkan daftar berkas pendaftaran sidang PKL mahasiswa
     */
    public function berkas($id) {
        $id_dosen = $this->session->userdata('ref_id');
        if (!$id_dosen) {
            show_404();
        }

        $jadwal = $this->Jadwal_pkl_model->get_by_id($id);
        if (!$jadwal || (int) $jadwal->id_penguji !== (int) $id_dosen) {
            $this->session->set_flashdata('error', 'Data jadwal tidak ditemukan atau Anda bukan penguji untuk jadwal ini.');
            redirect('Jadwal_pkl_dosen');
        }

        $pendaftaran = $this->Pkl_pendaftaran_sidang_model->get_by_mahasiswa(
            $jadwal->id_mahasiswa,
            $jadwal->id_tahun_akademik
        );
        if (!$pendaftaran) {
            // Fallback tanpa filter tahun akademik
            $pendaftaran = $this->Pkl_pendaftaran_sidang_model->get_by_mahasiswa($jadwal->id_mahasiswa);
        }

        $berkas_list = array();
        if ($pendaftaran) {
            $berkas_list = $this->Pkl_berkas_sidang_upload_model->get_by_pendaftaran($pendaftaran->id);
        }

        $data = array(
            'title' => 'Berkas Pendaftaran Sidang PKL',
            'page_title' => 'Berkas Pendaftaran Sidang PKL',
            'breadcrumb' => array(
                array('label' => 'Jadwal', 'url' => '#'),
                array('label' => 'Jadwal Sidang PKL', 'url' => site_url('Jadwal_pkl_dosen')),
                array('label' => 'Berkas Pendaftaran', 'active' => true)
            ),
            'sidebar_menu' => generate_sidebar_menu('dosen'),
            'jadwal' => $jadwal,
            'pendaftaran' => $pendaftaran,
            'berkas_list' => $berkas_list,
        );

        $data['content'] = $this->load->view('dosen/jadwal_pkl_berkas', $data, true);
        $this->load->view('layouts/main', $data);
    }

    /**
     * Buka/unduh berkas pendaftaran sidang PKL
     */
    public function download_berkas($id_upload) {
        $id_dosen = $this->session->userdata('ref_id');
        if (!$id_dosen) {
            show_404();
        }

        $upload = $this->Pkl_berkas_sidang_upload_model->get_by_id($id_upload);
        if (!$upload) {
            $this->session->set_flashdata('error', 'Berkas tidak ditemukan.');
            redirect('Jadwal_pkl_dosen');
        }

        $pendaftaran = $this->Pkl_pendaftaran_sidang_model->get_by_id($upload->id_pendaftaran_sidang);
        if (!$pendaftaran) {
            $this->session->set_flashdata('error', 'Data pendaftaran tidak ditemukan.');
            redirect('Jadwal_pkl_dosen');
        }

        $this->db->from('jadwal_pkl');
        $this->db->where('id_mahasiswa', $pendaftaran->id_mahasiswa);
        $this->db->where('id_penguji', $id_dosen);
        if (!empty($pendaftaran->id_tahun_akademik)) {
            $this->db->where('id_tahun_akademik', $pendaftaran->id_tahun_akademik);
        }
        $jadwal = $this->db->get()->row();
        if (!$jadwal) {
            $this->session->set_flashdata('error', 'Anda tidak memiliki akses untuk membuka berkas ini.');
            redirect('Jadwal_pkl_dosen');
        }

        $file_path = $upload->file_path;
        if (strpos($file_path, 'http') === 0) {
            $this->load->helper('google_drive');
            redirect(normalize_google_drive_link($file_path));
            return;
        }

        $file_path = ltrim(str_replace('\\', '/', $file_path), './');
        $full_path = FCPATH . $file_path;
        if (!file_exists($full_path)) {
            $this->session->set_flashdata('error', 'File tidak ditemukan di server.');
            redirect('Jadwal_pkl_dosen/berkas/' . $jadwal->id);
        }

        $this->load->helper('download');
        force_download($upload->file_name ?: basename($file_path), file_get_contents($full_path));
    }
}
