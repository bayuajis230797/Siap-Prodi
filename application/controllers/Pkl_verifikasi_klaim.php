<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Pkl_verifikasi_klaim Controller
 * Mengelola verifikasi klaim nilai PKL (untuk Sekprodi)
 */
class Pkl_verifikasi_klaim extends MY_Controller {

    public function __construct() {
        parent::__construct();
        $this->check_role('sekprodi');
        $this->load->model('Pkl_klaim_nilai_model');
        $this->load->model('Pkl_berkas_klaim_upload_model');
        $this->load->model('Pkl_berkas_klaim_nilai_model');
        $this->load->model('Pkl_nilai_rekap_model');
        $this->load->model('Pkl_penilaian_model');
        $this->load->model('Tahun_akademik_model');
        $this->load->helper('pkl');
        $this->load->helper('google_drive');
        $this->load->helper('notifikasi');
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

        $config['base_url'] = site_url('Pkl_verifikasi_klaim/index');
        $config['total_rows'] = $this->Pkl_klaim_nilai_model->count_all($filters);
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

        $klaim_list = $this->Pkl_klaim_nilai_model->get_all($filters, $config['per_page'], $offset);
        $klaim_list = $this->enrich_verifikasi_flags($klaim_list);

        $summary_filters = array();
        if ($tahun_aktif) {
            $summary_filters['id_tahun_akademik'] = $tahun_aktif->id;
        }
        $klaim_all = $this->Pkl_klaim_nilai_model->get_all($summary_filters, null, null);
        $klaim_all = $this->enrich_verifikasi_flags($klaim_all);
        $verification_summary = $this->build_verification_summary($klaim_all);

        $data = array(
            'title' => 'Verifikasi Klaim Nilai PKL',
            'page_title' => 'Verifikasi Klaim Nilai PKL',
            'breadcrumb' => array(
                array('label' => 'PKL', 'url' => '#'),
                array('label' => 'Verifikasi Klaim Nilai', 'active' => true)
            ),
            'sidebar_menu' => generate_sidebar_menu('sekprodi'),
            'klaim_list' => $klaim_list,
            'pagination' => $this->pagination->create_links(),
            'status_filter' => $status_filter,
            'tahun_aktif' => $tahun_aktif,
            'verification_summary' => $verification_summary
        );

        $data['content'] = $this->load->view('sekprodi/pkl_verifikasi_klaim/index', $data, true);
        $this->load->view('layouts/main', $data);
    }

    public function detail($id) {
        $klaim = $this->Pkl_klaim_nilai_model->get_by_id($id);
        if (!$klaim) {
            $this->session->set_flashdata('error', 'Data klaim nilai tidak ditemukan.');
            redirect('Pkl_verifikasi_klaim');
        }

        // Ambil berkas yang diupload
        $berkas_uploaded = $this->Pkl_berkas_klaim_upload_model->get_by_klaim($id);

        // Ambil semua penilaian untuk mahasiswa ini
        $penilaian_list = $this->Pkl_penilaian_model->get_by_mahasiswa($klaim->id_mahasiswa, $klaim->id_tahun_akademik);

        // Ambil nilai rekap jika sudah ada
        $nilai_rekap = $this->Pkl_nilai_rekap_model->get_by_mahasiswa($klaim->id_mahasiswa, $klaim->id_tahun_akademik);

        $data = array(
            'title' => 'Detail Verifikasi Klaim Nilai PKL',
            'page_title' => 'Detail Verifikasi Klaim Nilai PKL',
            'breadcrumb' => array(
                array('label' => 'PKL', 'url' => '#'),
                array('label' => 'Verifikasi Klaim Nilai', 'url' => site_url('Pkl_verifikasi_klaim')),
                array('label' => 'Detail', 'active' => true)
            ),
            'sidebar_menu' => generate_sidebar_menu('sekprodi'),
            'klaim' => $klaim,
            'berkas_uploaded' => $berkas_uploaded,
            'penilaian_list' => $penilaian_list,
            'nilai_rekap' => $nilai_rekap
        );

        $data['content'] = $this->load->view('sekprodi/pkl_verifikasi_klaim/detail', $data, true);
        $this->load->view('layouts/main', $data);
    }

    public function approve($id) {
        $klaim = $this->Pkl_klaim_nilai_model->get_by_id($id);
        if (!$klaim) {
            $this->session->set_flashdata('error', 'Data klaim nilai tidak ditemukan.');
            redirect('Pkl_verifikasi_klaim');
        }
        if (($klaim->status ?? '') !== 'menunggu') {
            $this->session->set_flashdata('error', 'Klaim ini tidak dalam status menunggu verifikasi.');
            redirect('Pkl_verifikasi_klaim/detail/' . $id);
        }

        $doc_check = $this->validate_submission_documents($id);
        if (!empty($doc_check['error'])) {
            $this->session->set_flashdata('error', $doc_check['error']);
            redirect('Pkl_verifikasi_klaim/detail/' . $id);
        }

        // Ambil semua penilaian untuk mahasiswa ini
        $penilaian_list = $this->Pkl_penilaian_model->get_by_mahasiswa($klaim->id_mahasiswa, $klaim->id_tahun_akademik);

        // Hitung nilai akhir dari semua penilaian
        $p1 = 0; $p2 = 0; $p3 = 0; $p4 = 0; $p5 = 0; $p6 = 0;

        foreach ($penilaian_list as $penilaian) {
            $p1 = max($p1, floatval($penilaian->nilai_p1));
            $p2 = max($p2, floatval($penilaian->nilai_p2));
            $p3 = max($p3, floatval($penilaian->nilai_p3));
            $p4 = max($p4, floatval($penilaian->nilai_p4));
            $p5 = max($p5, floatval($penilaian->nilai_p5));
            $p6 = max($p6, floatval($penilaian->nilai_p6));
        }

        // Hitung nilai akhir
        $nilai_akhir = hitung_nilai_akhir_pkl($p1, $p2, $p3, $p4, $p5, $p6);
        $nilai_huruf = konversi_nilai_huruf_pkl($nilai_akhir);

        // Update atau create nilai rekap
        $nilai_rekap = $this->Pkl_nilai_rekap_model->get_by_mahasiswa($klaim->id_mahasiswa, $klaim->id_tahun_akademik);
        
        if ($nilai_rekap) {
            $this->Pkl_nilai_rekap_model->update($nilai_rekap->id, array(
                'nilai_akhir' => $nilai_akhir,
                'nilai_huruf' => $nilai_huruf
            ));
        } else {
            // Ambil nilai dari masing-masing penilai
            $penilaian_pembimbing_dosen = $this->Pkl_penilaian_model->get_by_mahasiswa_tipe($klaim->id_mahasiswa, $klaim->id_tahun_akademik, 'pembimbing_dosen');
            $penilaian_pembimbing_lapangan = $this->Pkl_penilaian_model->get_by_mahasiswa_tipe($klaim->id_mahasiswa, $klaim->id_tahun_akademik, 'pembimbing_lapangan');
            $penilaian_sekprodi = $this->Pkl_penilaian_model->get_by_mahasiswa_tipe($klaim->id_mahasiswa, $klaim->id_tahun_akademik, 'sekprodi');
            $penilaian_penguji = $this->Pkl_penilaian_model->get_by_mahasiswa_tipe($klaim->id_mahasiswa, $klaim->id_tahun_akademik, 'penguji');

            $this->Pkl_nilai_rekap_model->create(array(
                'id_mahasiswa' => $klaim->id_mahasiswa,
                'id_tahun_akademik' => $klaim->id_tahun_akademik,
                'nilai_pembimbing_dosen' => $penilaian_pembimbing_dosen ? hitung_nilai_akhir_pkl($penilaian_pembimbing_dosen->nilai_p1, $penilaian_pembimbing_dosen->nilai_p2, $penilaian_pembimbing_dosen->nilai_p3, $penilaian_pembimbing_dosen->nilai_p4, $penilaian_pembimbing_dosen->nilai_p5, $penilaian_pembimbing_dosen->nilai_p6) : 0,
                'nilai_pembimbing_lapangan' => $penilaian_pembimbing_lapangan ? hitung_nilai_akhir_pkl($penilaian_pembimbing_lapangan->nilai_p1, $penilaian_pembimbing_lapangan->nilai_p2, $penilaian_pembimbing_lapangan->nilai_p3, $penilaian_pembimbing_lapangan->nilai_p4, $penilaian_pembimbing_lapangan->nilai_p5, $penilaian_pembimbing_lapangan->nilai_p6) : 0,
                'nilai_sekprodi' => $penilaian_sekprodi ? hitung_nilai_akhir_pkl($penilaian_sekprodi->nilai_p1, $penilaian_sekprodi->nilai_p2, $penilaian_sekprodi->nilai_p3, $penilaian_sekprodi->nilai_p4, $penilaian_sekprodi->nilai_p5, $penilaian_sekprodi->nilai_p6) : 0,
                'nilai_penguji' => $penilaian_penguji ? hitung_nilai_akhir_pkl($penilaian_penguji->nilai_p1, $penilaian_penguji->nilai_p2, $penilaian_penguji->nilai_p3, $penilaian_penguji->nilai_p4, $penilaian_penguji->nilai_p5, $penilaian_penguji->nilai_p6) : 0,
                'nilai_akhir' => $nilai_akhir,
                'nilai_huruf' => $nilai_huruf
            ));
        }

        // Update status klaim
        $this->Pkl_klaim_nilai_model->update($id, array(
            'status' => 'disetujui',
            'catatan_sekprodi' => $this->input->post('catatan_sekprodi')
        ));

        send_in_app_notification_by_ref(
            'mahasiswa',
            (int) $klaim->id_mahasiswa,
            'Klaim Nilai PKL Disetujui',
            'Klaim nilai PKL Anda telah disetujui dan nilai akhir telah difinalisasi.',
            site_url('Pkl_klaim_nilai')
        );

        $this->session->set_flashdata('success', 'Klaim nilai disetujui dan nilai final berhasil dihitung.');
        redirect('Pkl_verifikasi_klaim');
    }

    public function reject($id) {
        $klaim = $this->Pkl_klaim_nilai_model->get_by_id($id);
        if (!$klaim) {
            $this->session->set_flashdata('error', 'Data klaim nilai tidak ditemukan.');
            redirect('Pkl_verifikasi_klaim');
        }

        if ($this->input->method() === 'post') {
            $this->load->library('form_validation');
            $this->form_validation->set_rules('catatan_sekprodi', 'Catatan', 'required|trim');

            if ($this->form_validation->run() === FALSE) {
                $this->session->set_flashdata('error', validation_errors());
                redirect('Pkl_verifikasi_klaim/detail/' . $id);
            }

            $this->Pkl_klaim_nilai_model->update($id, array(
                'status' => 'ditolak',
                'catatan_sekprodi' => $this->input->post('catatan_sekprodi')
            ));

            send_in_app_notification_by_ref(
                'mahasiswa',
                (int) $klaim->id_mahasiswa,
                'Klaim Nilai PKL Ditolak',
                'Klaim nilai PKL Anda ditolak. Silakan perbaiki berkas dan ajukan ulang.',
                site_url('Pkl_klaim_nilai')
            );

            $this->session->set_flashdata('success', 'Klaim nilai ditolak.');
            redirect('Pkl_verifikasi_klaim');
        }

        redirect('Pkl_verifikasi_klaim/detail/' . $id);
    }

    public function download_berkas($id_upload) {
        $upload = $this->Pkl_berkas_klaim_upload_model->get_by_id($id_upload);
        if (!$upload) {
            show_404();
        }

        // Cek apakah file_path adalah link Google Drive
        if (strpos($upload->file_path, 'http') === 0) {
            // Redirect ke link Google Drive
            redirect($upload->file_path);
            return;
        }

        // Bersihkan path dari ./ jika ada dan normalisasi path
        $file_path = $upload->file_path;
        $file_path = ltrim($file_path, './');
        $file_path = str_replace('\\', '/', $file_path);
        
        $full_path = FCPATH . $file_path;
        
        if (!file_exists($full_path)) {
            $this->session->set_flashdata('error', 'File tidak ditemukan.');
            redirect('Pkl_verifikasi_klaim');
        }

        $this->load->helper('download');
        $file_name = $upload->file_name ?: basename($file_path);
        force_download($file_name, file_get_contents($full_path));
    }

    private function enrich_verifikasi_flags($klaim_list) {
        if (empty($klaim_list)) {
            return $klaim_list;
        }

        $berkas_wajib = $this->Pkl_berkas_klaim_nilai_model->get_wajib();
        $wajib_ids = array();
        foreach ($berkas_wajib as $item) {
            $wajib_ids[] = (int) $item->id;
        }

        $today_ts = strtotime(date('Y-m-d'));
        foreach ($klaim_list as $item) {
            $uploads = $this->Pkl_berkas_klaim_upload_model->get_by_klaim($item->id);
            $uploaded_by_berkas = array();
            $invalid_link_count = 0;

            foreach ($uploads as $upload) {
                $uploaded_by_berkas[(int) $upload->id_berkas_klaim_nilai] = true;
                if (!$this->is_verified_upload_link($upload->file_path)) {
                    $invalid_link_count++;
                }
            }

            $missing_required_count = 0;
            foreach ($wajib_ids as $wajib_id) {
                if (!isset($uploaded_by_berkas[$wajib_id])) {
                    $missing_required_count++;
                }
            }

            $wait_days = 0;
            if (!empty($item->created_at)) {
                $created_ts = strtotime(substr((string) $item->created_at, 0, 10));
                if ($created_ts !== false) {
                    $wait_days = (int) floor(($today_ts - $created_ts) / 86400);
                    if ($wait_days < 0) {
                        $wait_days = 0;
                    }
                }
            }

            $is_pending = (($item->status ?? '') === 'menunggu');
            $is_overdue = $is_pending && $wait_days >= 3;

            $priority_key = 'low';
            $priority_label = 'Rendah';
            $priority_class = 'success';
            if ($is_pending && ($is_overdue || $invalid_link_count > 0 || $missing_required_count > 0)) {
                $priority_key = 'high';
                $priority_label = 'Tinggi';
                $priority_class = 'danger';
            } elseif ($is_pending) {
                $priority_key = 'medium';
                $priority_label = 'Sedang';
                $priority_class = 'warning';
            }

            $item->verifikasi_flags = array(
                'is_pending' => $is_pending,
                'wait_days' => $wait_days,
                'is_overdue' => $is_overdue,
                'invalid_link_count' => $invalid_link_count,
                'missing_required_count' => $missing_required_count,
                'priority_key' => $priority_key,
                'priority_label' => $priority_label,
                'priority_class' => $priority_class
            );
        }

        return $klaim_list;
    }

    private function build_verification_summary($klaim_list) {
        $summary = array(
            'pending_total' => 0,
            'pending_overdue' => 0,
            'invalid_link_total' => 0,
            'missing_required_total' => 0
        );

        if (empty($klaim_list)) {
            return $summary;
        }

        foreach ($klaim_list as $item) {
            $flags = isset($item->verifikasi_flags) ? $item->verifikasi_flags : array();
            $is_pending = !empty($flags['is_pending']);
            if ($is_pending) {
                $summary['pending_total']++;
            }
            if (!empty($flags['is_overdue'])) {
                $summary['pending_overdue']++;
            }
            if (!empty($flags['invalid_link_count'])) {
                $summary['invalid_link_total'] += (int) $flags['invalid_link_count'];
            }
            if (!empty($flags['missing_required_count'])) {
                $summary['missing_required_total'] += (int) $flags['missing_required_count'];
            }
        }

        return $summary;
    }

    private function is_verified_upload_link($file_path) {
        $file_path = trim((string) $file_path);
        if ($file_path === '') {
            return false;
        }
        if (strpos($file_path, 'http://') === 0 || strpos($file_path, 'https://') === 0) {
            return is_valid_google_drive_link($file_path);
        }
        // Dukung data lama yang masih menyimpan path lokal.
        return true;
    }

    private function validate_submission_documents($id_klaim) {
        $result = array('error' => null);

        $uploads = $this->Pkl_berkas_klaim_upload_model->get_by_klaim($id_klaim);
        $uploaded_map = array();
        foreach ($uploads as $upload) {
            $uploaded_map[(int) $upload->id_berkas_klaim_nilai] = $upload;
        }

        $berkas_wajib = $this->Pkl_berkas_klaim_nilai_model->get_wajib();
        foreach ($berkas_wajib as $wajib) {
            $id_wajib = (int) $wajib->id;
            if (!isset($uploaded_map[$id_wajib])) {
                $result['error'] = 'Tidak bisa disetujui: berkas wajib "' . $wajib->nama_berkas . '" belum diupload.';
                return $result;
            }
            $upload = $uploaded_map[$id_wajib];
            if (!$this->is_verified_upload_link($upload->file_path)) {
                $result['error'] = 'Tidak bisa disetujui: link berkas "' . $wajib->nama_berkas . '" tidak valid.';
                return $result;
            }
        }

        return $result;
    }
}

