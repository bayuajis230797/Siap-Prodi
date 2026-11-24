<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Pkl_verifikasi_berkas_sidang Controller
 * Mengelola verifikasi berkas pendaftaran sidang PKL (untuk Sekprodi)
 */
class Pkl_verifikasi_berkas_sidang extends MY_Controller {

    public function __construct() {
        parent::__construct();
        $this->check_role('sekprodi');
        $this->load->model('Pkl_pendaftaran_sidang_model');
        $this->load->model('Pkl_berkas_sidang_upload_model');
        $this->load->model('Pkl_berkas_sidang_model');
        $this->load->model('Jadwal_pkl_model');
        $this->load->model('Dosen_model');
        $this->load->model('Tahun_akademik_model');
        $this->load->library('pagination');
        $this->load->library('form_validation');
        $this->load->helper('file');
        $this->load->helper('google_drive');
        $this->load->helper('notifikasi');
    }

    public function index() {
        $tahun_aktif = $this->Tahun_akademik_model->get_aktif();
        $status_filter = $this->input->get('status');
        $kelas_filter = $this->input->get('kelas');
        $q_filter = trim((string) $this->input->get('q'));
        $per_page = (int) $this->input->get('per_page');
        $per_page = in_array($per_page, array(10, 25, 50, 100), true) ? $per_page : 10;

        $filters = array();
        if ($tahun_aktif) {
            $filters['id_tahun_akademik'] = $tahun_aktif->id;
        }
        if (!empty($status_filter)) {
            $filters['status'] = $status_filter;
        }
        if (!empty($kelas_filter)) {
            $filters['kelas'] = $kelas_filter;
        }
        if ($q_filter !== '') {
            $filters['q'] = $q_filter;
        }

        $config['base_url'] = site_url('Pkl_verifikasi_berkas_sidang/index');
        $config['total_rows'] = $this->Pkl_pendaftaran_sidang_model->count_all($filters);
        $config['per_page'] = $per_page;
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

        $pendaftaran_list = $this->Pkl_pendaftaran_sidang_model->get_all($filters, $config['per_page'], $offset);
        $pendaftaran_list = $this->enrich_verifikasi_flags($pendaftaran_list);

        $summary_filters = array();
        if ($tahun_aktif) {
            $summary_filters['id_tahun_akademik'] = $tahun_aktif->id;
        }
        $pendaftaran_all = $this->Pkl_pendaftaran_sidang_model->get_all($summary_filters, null, null);
        $pendaftaran_all = $this->enrich_verifikasi_flags($pendaftaran_all);
        $verification_summary = $this->build_verification_summary($pendaftaran_all);

        // Ambil kelas list untuk filter
        $this->load->model('Mahasiswa_model');
        $this->db->distinct();
        $this->db->select('kelas');
        $this->db->from('mahasiswa');
        $this->db->where('kelas IS NOT NULL');
        $this->db->where('kelas !=', '');
        $this->db->order_by('kelas', 'ASC');
        $kelas_list = $this->db->get()->result();

        $data = array(
            'title' => 'Verifikasi Berkas Sidang PKL',
            'page_title' => 'Verifikasi Berkas Sidang PKL',
            'breadcrumb' => array(
                array('label' => 'PKL', 'url' => '#'),
                array('label' => 'Verifikasi Berkas Sidang', 'active' => true)
            ),
            'sidebar_menu' => generate_sidebar_menu('sekprodi'),
            'pendaftaran_list' => $pendaftaran_list,
            'pagination' => $this->pagination->create_links(),
            'total_rows' => $config['total_rows'],
            'status_filter' => $status_filter,
            'kelas_filter' => $kelas_filter,
            'q_filter' => $q_filter,
            'per_page' => $per_page,
            'kelas_list' => $kelas_list,
            'tahun_aktif' => $tahun_aktif,
            'verification_summary' => $verification_summary
        );

        $data['content'] = $this->load->view('sekprodi/pkl_verifikasi_berkas_sidang/index', $data, true);
        $this->load->view('layouts/main', $data);
    }

    public function detail($id) {
        $pendaftaran = $this->Pkl_pendaftaran_sidang_model->get_by_id($id);
        if (!$pendaftaran) {
            $this->session->set_flashdata('error', 'Data pendaftaran tidak ditemukan.');
            redirect('Pkl_verifikasi_berkas_sidang');
        }

        // Ambil berkas yang diupload
        $berkas_uploaded = $this->Pkl_berkas_sidang_upload_model->get_by_pendaftaran($id);

        // Ambil data pengajuan PKL
        $this->load->model('Pkl_pengajuan_model');
        $pengajuan = $this->Pkl_pengajuan_model->get_by_mahasiswa($pendaftaran->id_mahasiswa, $pendaftaran->id_tahun_akademik);

        // Ambil data dosen pembimbing
        $dosen_pembimbing = null;
        if ($pengajuan && $pengajuan->id_pembimbing) {
            $dosen_pembimbing = $this->Dosen_model->get_by_id($pengajuan->id_pembimbing);
        }

        // Ambil jadwal jika sudah ada (ambil elemen pertama karena get_by_mahasiswa mengembalikan array)
        $jadwal_list = $this->Jadwal_pkl_model->get_by_mahasiswa($pendaftaran->id_mahasiswa, $pendaftaran->id_tahun_akademik);
        $jadwal = !empty($jadwal_list) ? $jadwal_list[0] : null;

        // Ambil daftar dosen untuk penguji
        $dosen_list = $this->Dosen_model->get_all();

        $data = array(
            'title' => 'Detail Verifikasi Berkas Sidang PKL',
            'page_title' => 'Detail Verifikasi Berkas Sidang PKL',
            'breadcrumb' => array(
                array('label' => 'PKL', 'url' => '#'),
                array('label' => 'Verifikasi Berkas Sidang', 'url' => site_url('Pkl_verifikasi_berkas_sidang')),
                array('label' => 'Detail', 'active' => true)
            ),
            'sidebar_menu' => generate_sidebar_menu('sekprodi'),
            'pendaftaran' => $pendaftaran,
            'berkas_uploaded' => $berkas_uploaded,
            'pengajuan' => $pengajuan,
            'dosen_pembimbing' => $dosen_pembimbing,
            'jadwal' => $jadwal,
            'dosen_list' => $dosen_list
        );

        $data['content'] = $this->load->view('sekprodi/pkl_verifikasi_berkas_sidang/detail', $data, true);
        $this->load->view('layouts/main', $data);
    }

    public function approve($id) {
        $pendaftaran = $this->Pkl_pendaftaran_sidang_model->get_by_id($id);
        if (!$pendaftaran) {
            $this->session->set_flashdata('error', 'Data pendaftaran tidak ditemukan.');
            redirect('Pkl_verifikasi_berkas_sidang');
        }
        if (($pendaftaran->status ?? '') !== 'menunggu') {
            $this->session->set_flashdata('error', 'Pendaftaran ini tidak dalam status menunggu verifikasi.');
            redirect('Pkl_verifikasi_berkas_sidang/detail/' . $id);
        }

        $doc_check = $this->validate_submission_documents($pendaftaran->id);
        if (!empty($doc_check['error'])) {
            $this->session->set_flashdata('error', $doc_check['error']);
            redirect('Pkl_verifikasi_berkas_sidang/detail/' . $id);
        }

        if ($this->input->method() === 'post') {
            $this->form_validation->set_rules('id_penguji', 'Dosen Penguji', 'required|integer');
            $this->form_validation->set_rules('tanggal', 'Tanggal Ujian', 'required');
            $this->form_validation->set_rules('jam_mulai', 'Jam Mulai', 'required');
            $this->form_validation->set_rules('jam_selesai', 'Jam Selesai', 'required');
            $this->form_validation->set_rules('ruangan', 'Ruangan', 'required|trim');

            if ($this->form_validation->run() === FALSE) {
                $this->session->set_flashdata('error', validation_errors());
                redirect('Pkl_verifikasi_berkas_sidang/detail/' . $id);
            }

            $tanggal = $this->input->post('tanggal');
            $jam_mulai = $this->input->post('jam_mulai');
            $jam_selesai = $this->input->post('jam_selesai');
            $ruangan = trim((string) $this->input->post('ruangan'));
            $id_penguji = (int) $this->input->post('id_penguji');

            if (strtotime($jam_selesai) <= strtotime($jam_mulai)) {
                $this->session->set_flashdata('error', 'Jam selesai harus setelah jam mulai.');
                redirect('Pkl_verifikasi_berkas_sidang/detail/' . $id);
            }

            $jadwal_existing = $this->Jadwal_pkl_model->get_by_mahasiswa($pendaftaran->id_mahasiswa, $pendaftaran->id_tahun_akademik);
            if (!empty($jadwal_existing)) {
                $this->session->set_flashdata('error', 'Mahasiswa ini sudah memiliki jadwal sidang PKL.');
                redirect('Pkl_verifikasi_berkas_sidang/detail/' . $id);
            }

            $this->load->model('Pkl_pengajuan_model');
            $pengajuan = $this->Pkl_pengajuan_model->get_by_mahasiswa($pendaftaran->id_mahasiswa, $pendaftaran->id_tahun_akademik);
            if ($pengajuan && !empty($pengajuan->id_pembimbing) && (int) $pengajuan->id_pembimbing === $id_penguji) {
                $this->session->set_flashdata('error', 'Dosen pembimbing tidak boleh menjadi penguji sidang PKL.');
                redirect('Pkl_verifikasi_berkas_sidang/detail/' . $id);
            }

            $bentrok = $this->Jadwal_pkl_model->check_bentrok(
                $tanggal,
                $jam_mulai,
                $jam_selesai,
                $ruangan,
                $pendaftaran->id_tahun_akademik,
                null,
                $id_penguji
            );

            if ($bentrok['bentrok_ruang']) {
                $dosen_penguji = $this->Dosen_model->get_by_id($bentrok['detail_ruang']->id_penguji);
                $nama_penguji = $dosen_penguji ? $dosen_penguji->nama_dosen : 'Tidak diketahui';
                $this->session->set_flashdata('error', 'Ruangan ' . $ruangan . ' bentrok pada tanggal ' . date('d/m/Y', strtotime($tanggal)) . ' jam ' . date('H:i', strtotime($bentrok['detail_ruang']->jam_mulai)) . '-' . date('H:i', strtotime($bentrok['detail_ruang']->jam_selesai)) . ' oleh ' . $nama_penguji . '.');
                redirect('Pkl_verifikasi_berkas_sidang/detail/' . $id);
            }

            if ($bentrok['bentrok_penguji']) {
                $dosen = $this->Dosen_model->get_by_id($id_penguji);
                $nama_dosen = $dosen ? $dosen->nama_dosen : 'Tidak diketahui';
                $this->session->set_flashdata('error', 'Dosen penguji ' . $nama_dosen . ' bentrok pada tanggal ' . date('d/m/Y', strtotime($tanggal)) . ' jam ' . date('H:i', strtotime($bentrok['detail_penguji']->jam_mulai)) . '-' . date('H:i', strtotime($bentrok['detail_penguji']->jam_selesai)) . ' di ruangan ' . $bentrok['detail_penguji']->ruangan . '.');
                redirect('Pkl_verifikasi_berkas_sidang/detail/' . $id);
            }

            $this->db->trans_start();

            // Update status pendaftaran setelah lolos quality gate
            $this->Pkl_pendaftaran_sidang_model->update($id, array(
                'status' => 'disetujui',
                'catatan_sekprodi' => $this->input->post('catatan_sekprodi')
            ));

            // Buat jadwal PKL
            $jadwal_data = array(
                'id_mahasiswa' => $pendaftaran->id_mahasiswa,
                'id_pembimbing' => $pengajuan ? $pengajuan->id_pembimbing : null,
                'id_penguji' => $id_penguji,
                'tanggal' => $tanggal,
                'jam_mulai' => $jam_mulai,
                'jam_selesai' => $jam_selesai,
                'ruangan' => $ruangan,
                'id_tahun_akademik' => $pendaftaran->id_tahun_akademik,
                'status' => 'terjadwal'
            );

            $this->Jadwal_pkl_model->create($jadwal_data);
            $this->db->trans_complete();

            if ($this->db->trans_status() === FALSE) {
                $this->session->set_flashdata('error', 'Gagal menyimpan persetujuan berkas sidang. Silakan coba lagi.');
                redirect('Pkl_verifikasi_berkas_sidang/detail/' . $id);
            }

            send_in_app_notification_by_ref(
                'mahasiswa',
                (int) $pendaftaran->id_mahasiswa,
                'Berkas Sidang PKL Disetujui',
                'Berkas sidang PKL Anda disetujui. Jadwal sidang telah ditetapkan.',
                site_url('Pkl/jadwal')
            );
            send_in_app_notification_by_ref(
                'dosen',
                $id_penguji,
                'Penugasan Penguji Sidang PKL',
                'Anda ditugaskan sebagai penguji sidang PKL mahasiswa.',
                site_url('Jadwal_pkl_dosen')
            );

            $this->session->set_flashdata('success', 'Berkas sidang disetujui dan jadwal sidang berhasil dibuat.');
            redirect('Pkl_verifikasi_berkas_sidang');
        }

        redirect('Pkl_verifikasi_berkas_sidang/detail/' . $id);
    }

    public function reject($id) {
        $pendaftaran = $this->Pkl_pendaftaran_sidang_model->get_by_id($id);
        if (!$pendaftaran) {
            $this->session->set_flashdata('error', 'Data pendaftaran tidak ditemukan.');
            redirect('Pkl_verifikasi_berkas_sidang');
        }

        if ($this->input->method() === 'post') {
            $this->form_validation->set_rules('catatan_sekprodi', 'Catatan', 'required|trim');

            if ($this->form_validation->run() === FALSE) {
                $this->session->set_flashdata('error', validation_errors());
                redirect('Pkl_verifikasi_berkas_sidang/detail/' . $id);
            }

            $this->Pkl_pendaftaran_sidang_model->update($id, array(
                'status' => 'ditolak',
                'catatan_sekprodi' => $this->input->post('catatan_sekprodi')
            ));

            send_in_app_notification_by_ref(
                'mahasiswa',
                (int) $pendaftaran->id_mahasiswa,
                'Berkas Sidang PKL Ditolak',
                'Berkas sidang PKL Anda ditolak. Silakan perbaiki berkas sesuai catatan Sekprodi.',
                site_url('Pendaftaran_sidang_pkl')
            );

            $this->session->set_flashdata('success', 'Pendaftaran sidang ditolak.');
            redirect('Pkl_verifikasi_berkas_sidang');
        }

        redirect('Pkl_verifikasi_berkas_sidang/detail/' . $id);
    }

    public function download_berkas($id_upload) {
        $upload = $this->Pkl_berkas_sidang_upload_model->get_by_id($id_upload);
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
            redirect('Pkl_verifikasi_berkas_sidang');
        }

        $this->load->helper('download');
        $file_name = $upload->file_name ?: basename($file_path);
        force_download($file_name, file_get_contents($full_path));
    }

    private function enrich_verifikasi_flags($pendaftaran_list) {
        if (empty($pendaftaran_list)) {
            return $pendaftaran_list;
        }

        $berkas_wajib = $this->Pkl_berkas_sidang_model->get_wajib();
        $wajib_ids = array();
        foreach ($berkas_wajib as $item) {
            $wajib_ids[] = (int) $item->id;
        }

        $today_ts = strtotime(date('Y-m-d'));
        foreach ($pendaftaran_list as $item) {
            $uploads = $this->Pkl_berkas_sidang_upload_model->get_by_pendaftaran($item->id);
            $uploaded_by_berkas = array();
            $invalid_link_count = 0;

            foreach ($uploads as $upload) {
                $uploaded_by_berkas[(int) $upload->id_berkas_sidang] = true;
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

        return $pendaftaran_list;
    }

    private function build_verification_summary($pendaftaran_list) {
        $summary = array(
            'pending_total' => 0,
            'pending_overdue' => 0,
            'invalid_link_total' => 0,
            'missing_required_total' => 0
        );

        if (empty($pendaftaran_list)) {
            return $summary;
        }

        foreach ($pendaftaran_list as $item) {
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

    private function validate_submission_documents($id_pendaftaran) {
        $result = array('error' => null);

        $uploads = $this->Pkl_berkas_sidang_upload_model->get_by_pendaftaran($id_pendaftaran);
        $uploaded_map = array();
        foreach ($uploads as $upload) {
            $uploaded_map[(int) $upload->id_berkas_sidang] = $upload;
        }

        $berkas_wajib = $this->Pkl_berkas_sidang_model->get_wajib();
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

