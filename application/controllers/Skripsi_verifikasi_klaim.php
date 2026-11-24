<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Skripsi_verifikasi_klaim Controller
 * Mengelola verifikasi klaim nilai Skripsi (untuk Sekprodi)
 */
class Skripsi_verifikasi_klaim extends MY_Controller {

    public function __construct() {
        parent::__construct();
        $this->check_role('sekprodi');
        $this->load->model('Skripsi_klaim_nilai_model');
        $this->load->model('Skripsi_berkas_klaim_upload_model');
        $this->load->model('Skripsi_nilai_rekap_model');
        $this->load->model('Skripsi_nilai_bimbingan_model');
        $this->load->model('Skripsi_nilai_sidang_model');
        $this->load->model('Tahun_akademik_model');
        $this->load->helper('skripsi');
        $this->load->library('pagination');
    }

    public function index() {
        $tahun_aktif = $this->Tahun_akademik_model->get_aktif();
        $status_filter = $this->input->get('status');

        $filters = array();
        if ($tahun_aktif) {
            $filters['id_tahun_akademik'] = $tahun_aktif->id;
        }
        if (!empty($status_filter)) {
            $filters['status'] = $status_filter;
        }

        $config['base_url'] = site_url('Skripsi_verifikasi_klaim/index');
        $config['total_rows'] = count($this->Skripsi_klaim_nilai_model->get_all($filters));
        $config['per_page'] = 10;
        $config['uri_segment'] = 3;
        $config['reuse_query_string'] = TRUE;
        $config['full_tag_open'] = '<ul class="pagination pagination-sm m-0 float-right">';
        $config['full_tag_close'] = '</ul>';
        $config['attributes'] = array('class' => 'page-link');
        $config['first_tag_open'] = '<li class="page-item">';
        $config['first_tag_close'] = '</li>';
        $config['last_tag_open'] = '<li class="page-item">';
        $config['last_tag_close'] = '</li>';
        $config['next_tag_open'] = '<li class="page-item">';
        $config['next_tag_close'] = '</li>';
        $config['prev_tag_open'] = '<li class="page-item">';
        $config['prev_tag_close'] = '</li>';
        $config['cur_tag_open'] = '<li class="page-item active"><a class="page-link" href="#">';
        $config['cur_tag_close'] = '</a></li>';
        $config['num_tag_open'] = '<li class="page-item">';
        $config['num_tag_close'] = '</li>';

        $this->pagination->initialize($config);

        $page = ($this->uri->segment(3)) ? $this->uri->segment(3) : 1;
        $offset = ($page - 1) * $config['per_page'];

        $klaim_list = $this->Skripsi_klaim_nilai_model->get_all($filters, $config['per_page'], $offset);

        $data = array(
            'title' => 'Verifikasi Klaim Nilai Skripsi',
            'page_title' => 'Verifikasi Klaim Nilai Skripsi',
            'breadcrumb' => array(
                array('label' => 'Skripsi', 'url' => '#'),
                array('label' => 'Verifikasi Klaim Nilai', 'active' => true)
            ),
            'sidebar_menu' => generate_sidebar_menu('sekprodi'),
            'klaim_list' => $klaim_list,
            'pagination' => $this->pagination->create_links(),
            'status_filter' => $status_filter,
            'tahun_aktif' => $tahun_aktif
        );

        $data['content'] = $this->load->view('sekprodi/skripsi_verifikasi_klaim/index', $data, true);
        $this->load->view('layouts/main', $data);
    }

    public function detail($id) {
        $klaim = $this->Skripsi_klaim_nilai_model->get_by_id($id);
        if (!$klaim) {
            $this->session->set_flashdata('error', 'Data klaim nilai tidak ditemukan.');
            redirect('Skripsi_verifikasi_klaim');
        }

        // Ambil berkas yang diupload
        $berkas_uploaded = $this->Skripsi_berkas_klaim_upload_model->get_by_klaim_nilai($id);

        // Ambil nilai bimbingan
        $nilai_bimbingan_list = $this->Skripsi_nilai_bimbingan_model->get_by_skripsi($klaim->id_mahasiswa_skripsi);
        $nilai_bimbingan_p1 = null;
        $nilai_bimbingan_p2 = null;
        foreach ($nilai_bimbingan_list as $nb) {
            if (isset($nb->tipe_pembimbing) && $nb->tipe_pembimbing == 1) {
                $nilai_bimbingan_p1 = isset($nb->nilai_rata) ? $nb->nilai_rata : null;
            } elseif (isset($nb->tipe_pembimbing) && $nb->tipe_pembimbing == 2) {
                $nilai_bimbingan_p2 = isset($nb->nilai_rata) ? $nb->nilai_rata : null;
            }
        }

        // Ambil nilai sidang
        $nilai_sidang_list = $this->Skripsi_nilai_sidang_model->get_by_jadwal($klaim->id_jadwal_skripsi);
        $nilai_sidang_ketua = null;
        $nilai_sidang_penguji_1 = null;
        $nilai_sidang_penguji_2 = null;
        foreach ($nilai_sidang_list as $ns) {
            if (isset($ns->peran)) {
                if ($ns->peran == 'ketua') {
                    $nilai_sidang_ketua = isset($ns->nilai_total) ? $ns->nilai_total : null;
                } elseif ($ns->peran == 'anggota_1') {
                    $nilai_sidang_penguji_1 = isset($ns->nilai_total) ? $ns->nilai_total : null;
                } elseif ($ns->peran == 'anggota_2') {
                    $nilai_sidang_penguji_2 = isset($ns->nilai_total) ? $ns->nilai_total : null;
                }
            }
        }

        // Ambil nilai rekap jika sudah ada
        $nilai_rekap = $this->Skripsi_nilai_rekap_model->get_by_jadwal($klaim->id_jadwal_skripsi);

        $data = array(
            'title' => 'Detail Verifikasi Klaim Nilai Skripsi',
            'page_title' => 'Detail Verifikasi Klaim Nilai Skripsi',
            'breadcrumb' => array(
                array('label' => 'Skripsi', 'url' => '#'),
                array('label' => 'Verifikasi Klaim Nilai', 'url' => site_url('Skripsi_verifikasi_klaim')),
                array('label' => 'Detail', 'active' => true)
            ),
            'sidebar_menu' => generate_sidebar_menu('sekprodi'),
            'klaim' => $klaim,
            'berkas_uploaded' => $berkas_uploaded,
            'nilai_bimbingan_list' => $nilai_bimbingan_list,
            'nilai_bimbingan_p1' => $nilai_bimbingan_p1,
            'nilai_bimbingan_p2' => $nilai_bimbingan_p2,
            'nilai_sidang_list' => $nilai_sidang_list,
            'nilai_sidang_ketua' => $nilai_sidang_ketua,
            'nilai_sidang_penguji_1' => $nilai_sidang_penguji_1,
            'nilai_sidang_penguji_2' => $nilai_sidang_penguji_2,
            'nilai_rekap' => $nilai_rekap
        );

        $data['content'] = $this->load->view('sekprodi/skripsi_verifikasi_klaim/detail', $data, true);
        $this->load->view('layouts/main', $data);
    }

    public function approve($id) {
        $klaim = $this->Skripsi_klaim_nilai_model->get_by_id($id);
        if (!$klaim) {
            $this->session->set_flashdata('error', 'Data klaim nilai tidak ditemukan.');
            redirect('Skripsi_verifikasi_klaim');
        }

        // Hanya via POST dan hanya untuk klaim yang masih menunggu
        if ($this->input->method() !== 'post') {
            redirect('Skripsi_verifikasi_klaim/detail/' . $id);
        }
        if (isset($klaim->status) && $klaim->status !== 'menunggu') {
            $this->session->set_flashdata('error', 'Klaim nilai ini sudah diproses.');
            redirect('Skripsi_verifikasi_klaim/detail/' . $id);
        }

        // Ambil nilai bimbingan
        $nilai_bimbingan_list = $this->Skripsi_nilai_bimbingan_model->get_by_skripsi($klaim->id_mahasiswa_skripsi);
        $nilai_bimbingan_p1 = 0;
        $nilai_bimbingan_p2 = 0;
        foreach ($nilai_bimbingan_list as $nb) {
            if (isset($nb->tipe_pembimbing) && $nb->tipe_pembimbing == 1 && isset($nb->nilai_rata) && $nb->nilai_rata) {
                $nilai_bimbingan_p1 = floatval($nb->nilai_rata);
            } elseif (isset($nb->tipe_pembimbing) && $nb->tipe_pembimbing == 2 && isset($nb->nilai_rata) && $nb->nilai_rata) {
                $nilai_bimbingan_p2 = floatval($nb->nilai_rata);
            }
        }

        // Ambil nilai sidang
        $nilai_sidang_list = $this->Skripsi_nilai_sidang_model->get_by_jadwal($klaim->id_jadwal_skripsi);
        $nilai_sidang_ketua = 0;
        $nilai_sidang_penguji_1 = 0;
        $nilai_sidang_penguji_2 = 0;
        foreach ($nilai_sidang_list as $ns) {
            if (isset($ns->peran)) {
                if ($ns->peran == 'ketua' && isset($ns->nilai_total) && $ns->nilai_total) {
                    $nilai_sidang_ketua = floatval($ns->nilai_total);
                } elseif ($ns->peran == 'anggota_1' && isset($ns->nilai_total) && $ns->nilai_total) {
                    $nilai_sidang_penguji_1 = floatval($ns->nilai_total);
                } elseif ($ns->peran == 'anggota_2' && isset($ns->nilai_total) && $ns->nilai_total) {
                    $nilai_sidang_penguji_2 = floatval($ns->nilai_total);
                }
            }
        }

        // Hitung nilai bimbingan rata-rata
        $nilai_bimbingan_rata = hitung_nilai_bimbingan_rata($nilai_bimbingan_p1, $nilai_bimbingan_p2);

        // Hitung nilai sidang rata-rata
        $nilai_sidang_rata = hitung_nilai_sidang_rata($nilai_sidang_ketua, $nilai_sidang_penguji_1, $nilai_sidang_penguji_2);

        // Hitung nilai akhir
        $nilai_akhir = hitung_nilai_akhir_skripsi($nilai_bimbingan_rata, $nilai_sidang_rata);
        $nilai_huruf = konversi_nilai_huruf_skripsi($nilai_akhir);

        // Update atau create nilai rekap
        $nilai_rekap = $this->Skripsi_nilai_rekap_model->get_by_jadwal($klaim->id_jadwal_skripsi);
        
        if ($nilai_rekap) {
            $this->Skripsi_nilai_rekap_model->update($nilai_rekap->id, array(
                'nilai_bimbingan_pembimbing_1' => $nilai_bimbingan_p1,
                'nilai_bimbingan_pembimbing_2' => $nilai_bimbingan_p2,
                'nilai_bimbingan_rata' => $nilai_bimbingan_rata,
                'nilai_sidang_ketua' => $nilai_sidang_ketua,
                'nilai_sidang_penguji_1' => $nilai_sidang_penguji_1,
                'nilai_sidang_penguji_2' => $nilai_sidang_penguji_2,
                'nilai_sidang_rata' => $nilai_sidang_rata,
                'nilai_akhir' => $nilai_akhir,
                'grade' => $nilai_huruf
            ));
        } else {
            $this->Skripsi_nilai_rekap_model->create(array(
                'id_mahasiswa_skripsi' => $klaim->id_mahasiswa_skripsi,
                'id_jadwal_skripsi' => $klaim->id_jadwal_skripsi,
                'id_tahun_akademik' => $klaim->id_tahun_akademik,
                'nilai_bimbingan_pembimbing_1' => $nilai_bimbingan_p1,
                'nilai_bimbingan_pembimbing_2' => $nilai_bimbingan_p2,
                'nilai_bimbingan_rata' => $nilai_bimbingan_rata,
                'nilai_sidang_ketua' => $nilai_sidang_ketua,
                'nilai_sidang_penguji_1' => $nilai_sidang_penguji_1,
                'nilai_sidang_penguji_2' => $nilai_sidang_penguji_2,
                'nilai_sidang_rata' => $nilai_sidang_rata,
                'nilai_akhir' => $nilai_akhir,
                'grade' => $nilai_huruf
            ));
        }

        // Update status klaim
        $this->Skripsi_klaim_nilai_model->update($id, array(
            'status' => 'disetujui',
            'catatan_sekprodi' => $this->input->post('catatan_sekprodi'),
            'nilai_akhir' => $nilai_akhir,
            'grade' => $nilai_huruf
        ));

        $this->session->set_flashdata('success', 'Klaim nilai disetujui dan nilai final berhasil dihitung.');
        redirect('Skripsi_verifikasi_klaim');
    }

    public function reject($id) {
        $klaim = $this->Skripsi_klaim_nilai_model->get_by_id($id);
        if (!$klaim) {
            $this->session->set_flashdata('error', 'Data klaim nilai tidak ditemukan.');
            redirect('Skripsi_verifikasi_klaim');
        }

        if ($this->input->method() === 'post') {
            $this->load->library('form_validation');
            $this->form_validation->set_rules('catatan_sekprodi', 'Catatan', 'required|trim');

            if ($this->form_validation->run() === FALSE) {
                $this->session->set_flashdata('error', validation_errors());
                redirect('Skripsi_verifikasi_klaim/detail/' . $id);
            }

            $this->Skripsi_klaim_nilai_model->update($id, array(
                'status' => 'ditolak',
                'catatan_sekprodi' => $this->input->post('catatan_sekprodi')
            ));

            $this->session->set_flashdata('success', 'Klaim nilai ditolak.');
            redirect('Skripsi_verifikasi_klaim');
        }

        redirect('Skripsi_verifikasi_klaim/detail/' . $id);
    }

    public function download_berkas($id_upload) {
        $upload = $this->Skripsi_berkas_klaim_upload_model->get_by_id($id_upload);
        if (!$upload) {
            show_404();
        }

        $file_path = $upload->file_path;
        
        // Cek apakah ini link Google Drive
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
            $this->session->set_flashdata('error', 'File tidak ditemukan.');
            redirect('Skripsi_verifikasi_klaim');
        }

        $this->load->helper('download');
        $file_name = $upload->file_name ?: basename($file_path);
        force_download($file_name, file_get_contents($full_path));
    }
}


