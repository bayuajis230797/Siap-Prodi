<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Skripsi_pengajuan Controller
 * Menangani pengajuan judul skripsi oleh mahasiswa dan review oleh sekprodi
 */
class Skripsi_pengajuan extends MY_Controller {

    public function __construct() {
        parent::__construct();
        $this->load->model('Skripsi_pengajuan_model');
        $this->load->model('Skripsi_model');
        $this->load->model('Skripsi_pembimbing_model');
        $this->load->model('Skripsi_bimbingan_model');
        $this->load->model('Skripsi_bimbingan_log_model');
        $this->load->model('Skripsi_perubahan_judul_model');
        $this->load->model('Dosen_model');
        $this->load->model('Tahun_akademik_model');
        $this->load->library('pagination');
        $this->load->library('form_validation');
        $this->load->helper('file');
    }

    /**
     * Index untuk mahasiswa - form pengajuan judul
     */
    public function index() {
        $this->check_role('mahasiswa');
        
        $id_mahasiswa = $this->session->userdata('ref_id');
        $mahasiswa = $this->db->get_where('mahasiswa', array('id' => $id_mahasiswa))->row();
        
        if (!$mahasiswa) {
            $this->session->set_flashdata('error', 'Data mahasiswa tidak ditemukan.');
            redirect('Dashboard');
        }

        $tahun_aktif = $this->Tahun_akademik_model->get_aktif();
        if (!$tahun_aktif) {
            $this->session->set_flashdata('error', 'Tidak ada tahun akademik aktif.');
            redirect('Dashboard');
        }

        // Cek apakah mahasiswa sudah terdaftar skripsi
        $skripsi = $this->Skripsi_model->get_by_mahasiswa($mahasiswa->id, $tahun_aktif->id);
        
        // Cek apakah sudah ada pengajuan
        $pengajuan = $this->Skripsi_pengajuan_model->get_latest_by_mahasiswa($mahasiswa->id, $tahun_aktif->id);
        
        // Jika belum terdaftar skripsi dan belum ada pengajuan yang disetujui, tampilkan error
        if (!$skripsi && (!$pengajuan || $pengajuan->status != 'disetujui')) {
            $this->session->set_flashdata('error', 'Anda belum terdaftar untuk skripsi. Silakan hubungi sekprodi.');
            redirect('Dashboard');
        }

        // Cek status untuk menentukan apakah bimbingan dan pendaftaran sidang aktif
        $bimbingan_aktif = false;
        $pendaftaran_sidang_aktif = false;
        $bimbingan_list = array();
        $pendaftaran = null;
        $berkas_list = array();
        $berkas_uploaded = array();
        
        if ($pengajuan) {
            $status_p1 = isset($pengajuan->status_acc_judul_p1) ? $pengajuan->status_acc_judul_p1 : 'pending';
            $status_p2 = isset($pengajuan->status_acc_judul_p2) ? $pengajuan->status_acc_judul_p2 : 'pending';
            $status_approve_revisi = isset($pengajuan->status_approve_revisi) ? $pengajuan->status_approve_revisi : 'pending';
            
            // Bimbingan aktif jika:
            // - Pembimbing 1 ACC (pembimbing 2 bisa menyusul belakangan), atau
            // - Sekprodi sudah menyetujui perubahan judul (upload berkas persetujuan/revisi).
            if ($status_p1 == 'acc' || $status_approve_revisi == 'disetujui') {
                $bimbingan_aktif = true;
                $this->load->model('Skripsi_bimbingan_model');
                $this->load->model('Skripsi_bimbingan_log_model');
                $this->load->model('Dosen_model');
                
                // Ambil semua bimbingan untuk validasi
                $all_bimbingan = $this->Skripsi_bimbingan_model->get_by_mahasiswa($mahasiswa->id, $tahun_aktif->id);
                
                // Hapus bimbingan yang dibuat otomatis (tidak punya log dari mahasiswa)
                foreach ($all_bimbingan as $bimbingan) {
                    // Cek apakah bimbingan ini punya log dari mahasiswa
                    $logs_mahasiswa = $this->db->select('id')
                        ->from('skripsi_bimbingan_log')
                        ->where('id_bimbingan', $bimbingan->id)
                        ->where('role_pengguna', 'mahasiswa')
                        ->get()
                        ->result();
                    
                    // Jika tidak punya log dari mahasiswa, berarti dibuat otomatis - hapus
                    if (empty($logs_mahasiswa)) {
                        $this->Skripsi_bimbingan_model->delete($bimbingan->id);
                    }
                }
                
                // Ambil semua topik dari log mahasiswa (setiap topik adalah log terpisah)
                $topics_list = $this->Skripsi_bimbingan_log_model->get_topics_by_mahasiswa($mahasiswa->id, $tahun_aktif->id);
                
                // Tambahkan status ACC/Revisi untuk setiap topik
                foreach ($topics_list as &$topic) {
                    // Cek apakah topik ini sudah di-ACC atau direvisi oleh dosen
                    $log_dosen = $this->db->select('sbl.id, sbl.status_revisi, sbl.keterangan_revisi')
                        ->from('skripsi_bimbingan_log sbl')
                        ->where('sbl.id_bimbingan', $topic->id_bimbingan)
                        ->where('sbl.role_pengguna', 'dosen')
                        ->where('sbl.status_revisi IS NOT NULL')
                        ->where('sbl.status_revisi !=', '')
                        ->where('sbl.created_at >=', $topic->created_at) // Log dosen harus setelah atau sama dengan log mahasiswa
                        ->order_by('sbl.created_at', 'DESC')
                        ->limit(1)
                        ->get()
                        ->row();
                    
                    $topic->status_acc_topik = ($log_dosen && $log_dosen->status_revisi === 'acc');
                    $topic->status_revisi_topik = ($log_dosen && $log_dosen->status_revisi === 'revisi');
                    $topic->latest_status_dosen = $log_dosen ? $log_dosen->status_revisi : null;
                    $topic->keterangan_revisi = $log_dosen ? $log_dosen->keterangan_revisi : null;
                }
                unset($topic);
                
                // Siapkan data pembimbing untuk dropdown form bimbingan
                $pembimbing_list = array();
                $has_pending_topic_status = array();
                if ($pengajuan->id_pembimbing_1_final) {
                    $pembimbing_1 = $this->Dosen_model->get_by_id($pengajuan->id_pembimbing_1_final);
                    if ($pembimbing_1) {
                        $has_pending = $this->Skripsi_bimbingan_model->has_pending_topic($mahasiswa->id, $pembimbing_1->id, $tahun_aktif->id);
                        $pembimbing_list[] = array(
                            'id' => $pembimbing_1->id,
                            'nama' => $pembimbing_1->nama_dosen,
                            'tipe' => 1,
                            'has_pending' => $has_pending
                        );
                        $has_pending_topic_status[$pembimbing_1->id] = $has_pending;
                    }
                }
                if ($pengajuan->id_pembimbing_2_final) {
                    $pembimbing_2 = $this->Dosen_model->get_by_id($pengajuan->id_pembimbing_2_final);
                    if ($pembimbing_2) {
                        $has_pending = $this->Skripsi_bimbingan_model->has_pending_topic($mahasiswa->id, $pembimbing_2->id, $tahun_aktif->id);
                        $pembimbing_list[] = array(
                            'id' => $pembimbing_2->id,
                            'nama' => $pembimbing_2->nama_dosen,
                            'tipe' => 2,
                            'has_pending' => $has_pending
                        );
                        $has_pending_topic_status[$pembimbing_2->id] = $has_pending;
                    }
                }
            } else {
                $pembimbing_list = array();
                $has_pending_topic_status = array();
            }
            
            // Pendaftaran sidang aktif jika: skripsi status = layak_sidang
            if ($skripsi && $skripsi->status == 'layak_sidang') {
                $pendaftaran_sidang_aktif = true;
                $this->load->model('Skripsi_pendaftaran_sidang_model');
                $this->load->model('Skripsi_berkas_sidang_model');
                $this->load->model('Skripsi_berkas_upload_model');
                
                $pendaftaran = $this->Skripsi_pendaftaran_sidang_model->get_by_mahasiswa_skripsi($skripsi->id);
                $berkas_list = $this->Skripsi_berkas_sidang_model->get_all(true);
                if ($pendaftaran) {
                    $berkas_uploaded = $this->Skripsi_berkas_upload_model->get_by_pendaftaran($pendaftaran->id);
                }
            }
        }

        $judul_diajukan = null;
        $judul_disetujui_terbaru = null;
        if ($pengajuan) {
            $judul_diajukan = $pengajuan->judul_pengajuan;
            $judul_disetujui_terbaru = !empty($pengajuan->judul_disetujui) ? $pengajuan->judul_disetujui : null;

            // Prioritas judul disetujui terbaru dari modul perubahan judul skripsi (jika ada).
            $latest_approved_change = $this->Skripsi_perubahan_judul_model->get_latest_by_pengajuan_status($pengajuan->id, 'acc');
            if ($latest_approved_change && !empty($latest_approved_change->judul_baru)) {
                $judul_disetujui_terbaru = $latest_approved_change->judul_baru;
            }

            // Judul diajukan = judul yang sedang menunggu approve sekprodi.
            $latest_pending_change = $this->Skripsi_perubahan_judul_model->get_latest_by_pengajuan_status($pengajuan->id, 'pending');
            if ($latest_pending_change && !empty($latest_pending_change->judul_baru)) {
                $judul_diajukan = $latest_pending_change->judul_baru;
            } elseif (!empty($pengajuan->judul_revisi) && ($pengajuan->status_approve_revisi ?? 'pending') === 'pending') {
                $judul_diajukan = $pengajuan->judul_revisi;
            }
        }

        $data = array(
            'title' => 'Pengajuan Judul Skripsi',
            'page_title' => 'Pengajuan Judul Skripsi',
            'breadcrumb' => array(
                array('label' => 'Skripsi', 'url' => site_url('Skripsi_pengajuan')),
                array('label' => 'Pengajuan Judul', 'active' => true)
            ),
            'sidebar_menu' => generate_sidebar_menu('mahasiswa'),
            'pengajuan' => $pengajuan,
            'skripsi' => $skripsi,
            'tahun_aktif' => $tahun_aktif,
            'judul_diajukan' => $judul_diajukan,
            'judul_disetujui_terbaru' => $judul_disetujui_terbaru,
            'bimbingan_aktif' => $bimbingan_aktif,
            'topics_list' => isset($topics_list) ? $topics_list : array(),
            'pembimbing_list' => isset($pembimbing_list) ? $pembimbing_list : array(),
            'has_pending_topic_status' => isset($has_pending_topic_status) ? $has_pending_topic_status : array(),
            'pendaftaran_sidang_aktif' => $pendaftaran_sidang_aktif,
            'pendaftaran' => $pendaftaran,
            'berkas_list' => $berkas_list,
            'berkas_uploaded' => $berkas_uploaded
        );

        $data['content'] = $this->load->view('skripsi_pengajuan/index', $data, true);
        $this->load->view('layouts/main', $data);
    }

    /**
     * Create topik bimbingan baru
     */
    public function create_topik() {
        $this->check_role('mahasiswa');
        if ($this->input->method() !== 'post') {
            show_404();
        }

        $id_mahasiswa = $this->session->userdata('ref_id');
        $mahasiswa = $this->db->get_where('mahasiswa', array('id' => $id_mahasiswa))->row();
        
        if (!$mahasiswa) {
            $this->session->set_flashdata('error', 'Data mahasiswa tidak ditemukan.');
            redirect('Skripsi_pengajuan');
        }

        $tahun_aktif = $this->Tahun_akademik_model->get_aktif();
        if (!$tahun_aktif) {
            $this->session->set_flashdata('error', 'Tidak ada tahun akademik aktif.');
            redirect('Skripsi_pengajuan');
        }

        // Cek apakah sudah ada pengajuan yang disetujui
        $pengajuan = $this->Skripsi_pengajuan_model->get_latest_by_mahasiswa($mahasiswa->id, $tahun_aktif->id);
        if (!$pengajuan || $pengajuan->status != 'disetujui') {
            $this->session->set_flashdata('error', 'Pengajuan judul belum disetujui.');
            redirect('Skripsi_pengajuan');
        }

        // Gate bimbingan: Pembimbing 1 ACC, atau revisi disetujui Sekprodi.
        $status_p1 = isset($pengajuan->status_acc_judul_p1) ? $pengajuan->status_acc_judul_p1 : 'pending';
        $status_p2 = isset($pengajuan->status_acc_judul_p2) ? $pengajuan->status_acc_judul_p2 : 'pending';
        $status_approve_revisi = isset($pengajuan->status_approve_revisi) ? $pengajuan->status_approve_revisi : 'pending';
        if ($status_p1 != 'acc' && $status_approve_revisi != 'disetujui') {
            $this->session->set_flashdata('error', 'Bimbingan belum aktif. Syarat: judul di-ACC Pembimbing 1 atau perubahan judul disetujui Sekprodi.');
            redirect('Skripsi_pengajuan');
        }

        $this->load->model('Skripsi_bimbingan_model');
        $this->load->model('Skripsi_bimbingan_log_model');
        $this->load->library('form_validation');

        $this->form_validation->set_rules('id_pembimbing', 'Pembimbing', 'required|integer');
        $this->form_validation->set_rules('judul_topik', 'Judul/Topik Bimbingan', 'required|trim|callback_valid_skripsi_topik_bimbingan');

        if ($this->form_validation->run() == FALSE) {
            $this->session->set_flashdata('error', validation_errors());
            redirect('Skripsi_pengajuan');
        }

        $id_pembimbing = $this->input->post('id_pembimbing');
        $judul_topik = $this->input->post('judul_topik');
        
        // Handle file upload (opsional)
        $file_path = NULL;
        $file_name = NULL;
        if (!empty($_FILES['file_bimbingan']['name'])) {
            $config['upload_path'] = './uploads/skripsi/bimbingan/';
            $config['allowed_types'] = 'pdf|doc|docx|jpg|jpeg|png';
            $config['max_size'] = 10240; // 10MB
            $config['encrypt_name'] = TRUE;

            if (!is_dir($config['upload_path'])) {
                mkdir($config['upload_path'], 0755, true);
            }

            $this->load->library('upload', $config);
            if ($this->upload->do_upload('file_bimbingan')) {
                $file_data = $this->upload->data();
                $file_path = 'uploads/skripsi/bimbingan/' . $file_data['file_name'];
                $file_name = $file_data['orig_name'];
            } else {
                $this->session->set_flashdata('error', 'Upload file gagal: ' . $this->upload->display_errors('', ''));
                redirect('Skripsi_pengajuan');
            }
        }

        // Validasi: cek apakah pembimbing adalah pembimbing 1 atau 2
        $is_pembimbing_1 = ($pengajuan->id_pembimbing_1_final == $id_pembimbing);
        $is_pembimbing_2 = ($pengajuan->id_pembimbing_2_final == $id_pembimbing);
        
        if (!$is_pembimbing_1 && !$is_pembimbing_2) {
            $this->session->set_flashdata('error', 'Pembimbing yang dipilih tidak valid.');
            redirect('Skripsi_pengajuan');
        }

        // Jika pembimbing 2 belum ACC judul, batasi topik hanya untuk pembimbing 1.
        if ($is_pembimbing_2 && $status_p2 != 'acc') {
            $this->session->set_flashdata('error', 'Pembimbing 2 belum ACC judul. Bimbingan sementara hanya bisa dengan Pembimbing 1.');
            redirect('Skripsi_pengajuan');
        }

        // Cek apakah ada topik pending pada pembimbing ini
        if ($this->Skripsi_bimbingan_model->has_pending_topic($mahasiswa->id, $id_pembimbing, $tahun_aktif->id)) {
            $this->session->set_flashdata('error', 'Anda masih memiliki topik bimbingan yang belum di-ACC oleh pembimbing ini.');
            redirect('Skripsi_pengajuan');
        }

        // Dapatkan atau buat bimbingan utama (satu bimbingan per pembimbing)
        $bimbingan = $this->Skripsi_bimbingan_model->get_by_mahasiswa_and_pembimbing($mahasiswa->id, $id_pembimbing, $tahun_aktif->id);
        if (!$bimbingan) {
            // Jika bimbingan belum ada, buat entri bimbingan baru
            $tipe_pembimbing = ($pengajuan->id_pembimbing_1_final == $id_pembimbing) ? 1 : 2;

            $bimbingan_data = array(
                'id_mahasiswa' => $mahasiswa->id,
                'id_pembimbing' => $id_pembimbing,
                'tipe_pembimbing' => $tipe_pembimbing,
                'id_tahun_akademik' => $tahun_aktif->id,
                'judul_topik' => $judul_topik, // Topik pertama
                'status' => 'open',
                'last_activity' => date('Y-m-d H:i:s')
            );
            $id_bimbingan = $this->Skripsi_bimbingan_model->create($bimbingan_data);
        } else {
            $id_bimbingan = $bimbingan->id;
            // Update last_activity saja, jangan update judul_topik (setiap topik adalah log terpisah)
            $this->Skripsi_bimbingan_model->update($id_bimbingan, array('last_activity' => date('Y-m-d H:i:s')));
        }

        // Buat log bimbingan untuk topik baru (setiap topik adalah log terpisah)
        $log_data = array(
            'id_bimbingan' => $id_bimbingan,
            'id_pengguna' => $this->session->userdata('user_id'),
            'role_pengguna' => 'mahasiswa',
            'pesan' => $judul_topik, // Simpan judul topik di pesan
            'status_revisi' => NULL, // Awalnya pending
            'keterangan_revisi' => NULL,
            'file_path' => $file_path,
            'file_name' => $file_name
        );

        if ($this->Skripsi_bimbingan_log_model->create($log_data)) {
            $this->session->set_flashdata('success', 'Topik bimbingan berhasil diajukan. Menunggu tanggapan pembimbing.');
        } else {
            $this->session->set_flashdata('error', 'Gagal mengajukan topik bimbingan.');
        }

        redirect('Skripsi_pengajuan');
    }

    /**
     * Submit pengajuan judul oleh mahasiswa
     */
    public function submit() {
        $this->check_role('mahasiswa');
        
        $id_mahasiswa = $this->session->userdata('ref_id');
        $mahasiswa = $this->db->get_where('mahasiswa', array('id' => $id_mahasiswa))->row();
        
        if (!$mahasiswa) {
            $this->session->set_flashdata('error', 'Data mahasiswa tidak ditemukan.');
            redirect('Dashboard');
        }

        $tahun_aktif = $this->Tahun_akademik_model->get_aktif();
        if (!$tahun_aktif) {
            $this->session->set_flashdata('error', 'Tidak ada tahun akademik aktif.');
            redirect('Dashboard');
        }

        $this->form_validation->set_rules('judul_pengajuan', 'Judul Skripsi', 'required|trim');
        $this->form_validation->set_rules('deskripsi', 'Deskripsi', 'trim');
        $this->form_validation->set_rules('alasan', 'Alasan', 'trim');

        if ($this->form_validation->run() == FALSE) {
            redirect('Skripsi_pengajuan');
        }

        $file_proposal = null;
        if (!empty($_FILES['file_proposal']['name'])) {
            $this->load->helper('google_drive');
            require_google_drive_or_redirect();
            $this->load->library('Upload_to_drive');
            $share_emails = array();
            $sekprodi_owner = $this->upload_to_drive->find_connected_sekprodi_user_id();
            if ($sekprodi_owner) {
                $email = $this->upload_to_drive->get_drive_email($sekprodi_owner);
                if ($email) {
                    $share_emails[] = $email;
                }
            }
            $upload = $this->upload_to_drive->handle_upload('file_proposal', $this->upload_to_drive->default_options(array('SIAP', 'Skripsi', 'ajuan'), $share_emails));
            if (empty($upload['ok'])) {
                $this->session->set_flashdata('error', isset($upload['message']) ? $upload['message'] : 'Gagal upload berkas proposal.');
                redirect('Skripsi_pengajuan');
            }
            $file_proposal = $upload['webViewLink'];
        }

        $data = array(
            'id_mahasiswa' => $mahasiswa->id,
            'id_tahun_akademik' => $tahun_aktif->id,
            'judul_pengajuan' => $this->input->post('judul_pengajuan'),
            'deskripsi' => $this->input->post('deskripsi'),
            'alasan' => $this->input->post('alasan'),
            'file_proposal' => $file_proposal,
            'id_pembimbing_1_request' => null,
            'id_pembimbing_2_request' => null,
            'status' => 'pending'
        );

        if ($this->Skripsi_pengajuan_model->create($data)) {
            $this->load->helper(array('notifikasi', 'sekprodi_badge'));
            notify_sekprodi(
                'Ajuan Judul Skripsi Baru',
                'Ada pengajuan judul skripsi baru yang menunggu review.',
                site_url('Skripsi_plot_pembimbing'),
                'skripsi_judul'
            );
            $this->session->set_flashdata('success', 'Pengajuan judul skripsi berhasil dikirim. Menunggu review dari sekprodi.');
        } else {
            $this->session->set_flashdata('error', 'Gagal mengirim pengajuan judul skripsi.');
        }

        redirect('Skripsi_pengajuan');
    }

    /**
     * Review pengajuan untuk sekprodi
     */
    public function review() {
        $this->check_role('sekprodi');

        $tahun_aktif = $this->Tahun_akademik_model->get_aktif();
        $status_filter = $this->input->get('status');
        $kelas_filter = $this->input->get('kelas');

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

        $config['base_url'] = site_url('Skripsi_pengajuan/review');
        $config['total_rows'] = $this->Skripsi_pengajuan_model->count_all($filters);
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
        $config['cur_tag_open'] = '<li class="page-item active"><span class="page-link">';
        $config['cur_tag_close'] = '</span></li>';
        $config['num_tag_open'] = '<li class="page-item">';
        $config['num_tag_close'] = '</li>';

        $this->pagination->initialize($config);
        $page = ($this->uri->segment(3)) ? $this->uri->segment(3) : 1;
        $offset = ($page - 1) * $config['per_page'];

        $pengajuan_list = $this->Skripsi_pengajuan_model->get_all($filters, $config['per_page'], $offset);

        // Ambil daftar pembimbing
        $pembimbing_1_list = $this->Skripsi_pembimbing_model->get_allowed_for_type(1);
        $pembimbing_2_list = $this->Skripsi_pembimbing_model->get_allowed_for_type(2);

        $data = array(
            'title' => 'Review Pengajuan Judul Skripsi',
            'page_title' => 'Review Pengajuan Judul Skripsi',
            'breadcrumb' => array(
                array('label' => 'Skripsi', 'url' => site_url('Skripsi_register')),
                array('label' => 'Review Pengajuan', 'active' => true)
            ),
            'sidebar_menu' => generate_sidebar_menu('sekprodi'),
            'pengajuan_list' => $pengajuan_list,
            'tahun_aktif' => $tahun_aktif,
            'status_filter' => $status_filter,
            'kelas_filter' => $kelas_filter,
            'pagination' => $this->pagination->create_links(),
            'pembimbing_1_list' => $pembimbing_1_list,
            'pembimbing_2_list' => $pembimbing_2_list
        );

        $data['content'] = $this->load->view('skripsi_pengajuan/review', $data, true);
        $this->load->view('layouts/main', $data);
    }

    /**
     * Edit judul skripsi oleh sekprodi
     */
    public function edit_judul($id) {
        $this->check_role('sekprodi');

        if ($this->input->method() === 'post') {
            $pengajuan = $this->Skripsi_pengajuan_model->get_by_id($id);
            if (!$pengajuan) {
                $this->session->set_flashdata('error', 'Data pengajuan tidak ditemukan.');
                redirect('Skripsi_pengajuan/review');
            }

            $this->form_validation->set_rules('judul_disetujui', 'Judul Baru', 'required|trim|min_length[10]');

            if ($this->form_validation->run() == FALSE) {
                $this->session->set_flashdata('error', validation_errors());
                redirect('Skripsi_pengajuan/detail/' . $id);
            }

            $judul_baru = $this->input->post('judul_disetujui');
            $catatan_edit = $this->input->post('catatan_edit_judul');

            // Update judul_disetujui di skripsi_pengajuan
            $update_data = array(
                'judul_disetujui' => $judul_baru
            );
            
            if (!empty($catatan_edit)) {
                $update_data['catatan_sekprodi'] = ($pengajuan->catatan_sekprodi ? $pengajuan->catatan_sekprodi . "\n\n" : '') . 
                    'Catatan Edit Judul: ' . $catatan_edit;
            }

            if ($this->Skripsi_pengajuan_model->update($id, $update_data)) {
                // Update judul_skripsi di mahasiswa_skripsi
                $skripsi = $this->Skripsi_model->get_by_mahasiswa($pengajuan->id_mahasiswa, $pengajuan->id_tahun_akademik);
                if ($skripsi) {
                    $this->Skripsi_model->update($skripsi->id, array(
                        'judul_skripsi' => $judul_baru
                    ));

                    // Update judul_topik di semua bimbingan skripsi
                    $this->load->model('Skripsi_bimbingan_model');
                    $bimbingan_list = $this->Skripsi_bimbingan_model->get_by_mahasiswa($pengajuan->id_mahasiswa, $pengajuan->id_tahun_akademik);
                    foreach ($bimbingan_list as $bimbingan) {
                        $this->Skripsi_bimbingan_model->update($bimbingan->id, array(
                            'judul_topik' => $judul_baru
                        ));
                    }
                }

                $this->session->set_flashdata('success', 'Judul skripsi berhasil diubah. Perubahan telah diterapkan ke semua data terkait (bimbingan skripsi, dll).');
            } else {
                $this->session->set_flashdata('error', 'Gagal mengubah judul skripsi.');
            }
        }

        redirect('Skripsi_pengajuan/detail/' . $id);
    }

    /**
     * Review perubahan judul untuk sekprodi
     */
    public function review_perubahan_judul() {
        $this->check_role('sekprodi');

        $tahun_aktif = $this->Tahun_akademik_model->get_aktif();
        
        // Ambil pengajuan yang ada judul revisi atau file persetujuan dan status approve revisi masih pending
        $pengajuan_list = $this->Skripsi_pengajuan_model->get_pending_revisi($tahun_aktif ? $tahun_aktif->id : null);

        $data = array(
            'title' => 'Review Perubahan Judul',
            'page_title' => 'Review Perubahan Judul',
            'breadcrumb' => array(
                array('label' => 'Skripsi', 'url' => site_url('Skripsi_pengajuan/review')),
                array('label' => 'Review Perubahan Judul', 'active' => true)
            ),
            'sidebar_menu' => generate_sidebar_menu('sekprodi'),
            'pengajuan_list' => $pengajuan_list,
            'tahun_aktif' => $tahun_aktif
        );

        $data['content'] = $this->load->view('skripsi_pengajuan/review_perubahan_judul', $data, true);
        $this->load->view('layouts/main', $data);
    }

    /**
     * Detail pengajuan untuk review
     */
    public function detail($id) {
        $this->check_role('sekprodi');

        $pengajuan = $this->Skripsi_pengajuan_model->get_by_id($id);
        if (!$pengajuan) {
            $this->session->set_flashdata('error', 'Data pengajuan tidak ditemukan.');
            redirect('Skripsi_pengajuan/review');
        }

        // Ambil daftar pembimbing
        $pembimbing_1_list = $this->Skripsi_pembimbing_model->get_allowed_for_type(1);
        $pembimbing_2_list = $this->Skripsi_pembimbing_model->get_allowed_for_type(2);

        $data = array(
            'title' => 'Detail Pengajuan Judul',
            'page_title' => 'Detail Pengajuan Judul',
            'breadcrumb' => array(
                array('label' => 'Review Pengajuan', 'url' => site_url('Skripsi_pengajuan/review')),
                array('label' => 'Detail', 'active' => true)
            ),
            'sidebar_menu' => generate_sidebar_menu('sekprodi'),
            'pengajuan' => $pengajuan,
            'pembimbing_1_list' => $pembimbing_1_list,
            'pembimbing_2_list' => $pembimbing_2_list
        );

        $data['content'] = $this->load->view('skripsi_pengajuan/detail', $data, true);
        $this->load->view('layouts/main', $data);
    }

    /**
     * Approve pengajuan oleh sekprodi
     */
    public function approve($id) {
        $this->check_role('sekprodi');

        if ($this->input->method() === 'post') {
            $this->form_validation->set_rules('judul_disetujui', 'Judul Disetujui', 'required|trim');
            $this->form_validation->set_rules('id_pembimbing_1_final', 'Pembimbing 1', 'required|integer');
            // Pembimbing 2 opsional (konsisten dengan Plot Pembimbing)
            $this->form_validation->set_rules('id_pembimbing_2_final', 'Pembimbing 2', 'trim|integer');

            if ($this->form_validation->run() == FALSE) {
                redirect('Skripsi_pengajuan/detail/' . $id);
            }

            $p2_raw = $this->input->post('id_pembimbing_2_final');
            $p2_final = ($p2_raw && (int) $p2_raw > 0) ? (int) $p2_raw : null;
            if ($p2_final !== null && $p2_final == (int) $this->input->post('id_pembimbing_1_final')) {
                $this->session->set_flashdata('error', 'Pembimbing 1 dan Pembimbing 2 tidak boleh sama.');
                redirect('Skripsi_pengajuan/detail/' . $id);
            }

            $data = array(
                'status' => 'disetujui',
                'judul_disetujui' => $this->input->post('judul_disetujui'),
                'id_pembimbing_1_final' => $this->input->post('id_pembimbing_1_final'),
                'id_pembimbing_2_final' => $p2_final,
                'catatan_sekprodi' => $this->input->post('catatan_sekprodi')
            );

            if ($this->Skripsi_pengajuan_model->update($id, $data)) {
                // Update atau create mahasiswa_skripsi dengan pembimbing
                $pengajuan = $this->Skripsi_pengajuan_model->get_by_id($id);
                $skripsi = $this->Skripsi_model->get_by_mahasiswa($pengajuan->id_mahasiswa, $pengajuan->id_tahun_akademik);
                
                $skripsi_data = array(
                    'id_pembimbing_1' => $data['id_pembimbing_1_final'],
                    'id_pembimbing_2' => $data['id_pembimbing_2_final'],
                    'judul_skripsi' => $data['judul_disetujui'],
                    'status' => 'sedang_skripsi'
                );
                
                if ($skripsi) {
                    // Update jika sudah ada
                    $this->Skripsi_model->update($skripsi->id, $skripsi_data);
                } else {
                    // Create jika belum ada
                    $skripsi_data['id_mahasiswa'] = $pengajuan->id_mahasiswa;
                    $skripsi_data['id_tahun_akademik'] = $pengajuan->id_tahun_akademik;
                    $this->Skripsi_model->create($skripsi_data);
                }

                $this->session->set_flashdata('success', 'Pengajuan judul disetujui.');
            } else {
                $this->session->set_flashdata('error', 'Gagal menyetujui pengajuan.');
            }
        }

        redirect('Skripsi_pengajuan/review');
    }

    /**
     * Reject pengajuan oleh sekprodi
     */
    public function reject($id) {
        $this->check_role('sekprodi');

        if ($this->input->method() === 'post') {
            $this->form_validation->set_rules('catatan_sekprodi', 'Alasan Penolakan', 'required|trim');

            if ($this->form_validation->run() == FALSE) {
                redirect('Skripsi_pengajuan/detail/' . $id);
            }

            $data = array(
                'status' => 'ditolak',
                'catatan_sekprodi' => $this->input->post('catatan_sekprodi')
            );

            if ($this->Skripsi_pengajuan_model->update($id, $data)) {
                $this->session->set_flashdata('success', 'Pengajuan judul ditolak.');
            } else {
                $this->session->set_flashdata('error', 'Gagal menolak pengajuan.');
            }
        }

        redirect('Skripsi_pengajuan/review');
    }

    /**
     * Hapus pengajuan oleh sekprodi
     */
    public function delete($id) {
        $this->check_role('sekprodi');

        $pengajuan = $this->Skripsi_pengajuan_model->get_by_id($id);
        
        if (!$pengajuan) {
            $this->session->set_flashdata('error', 'Data pengajuan tidak ditemukan.');
            redirect('Skripsi_pengajuan/review');
        }

        // Hapus pengajuan beserta file-file terkait
        if ($this->Skripsi_pengajuan_model->delete($id)) {
            $this->session->set_flashdata('success', 'Pengajuan judul berhasil dihapus.');
        } else {
            $this->session->set_flashdata('error', 'Gagal menghapus pengajuan judul.');
        }

        redirect('Skripsi_pengajuan/review');
    }

    /**
     * Upload berkas persetujuan judul oleh mahasiswa (setelah revisi)
     */
    public function upload_persetujuan() {
        $this->check_role('mahasiswa');
        
        $id_mahasiswa = $this->session->userdata('ref_id');
        $mahasiswa = $this->db->get_where('mahasiswa', array('id' => $id_mahasiswa))->row();
        
        if (!$mahasiswa) {
            $this->session->set_flashdata('error', 'Data mahasiswa tidak ditemukan.');
            redirect('Skripsi_pengajuan');
        }

        $tahun_aktif = $this->Tahun_akademik_model->get_aktif();
        if (!$tahun_aktif) {
            $this->session->set_flashdata('error', 'Tidak ada tahun akademik aktif.');
            redirect('Skripsi_pengajuan');
        }

        $pengajuan = $this->Skripsi_pengajuan_model->get_latest_by_mahasiswa($mahasiswa->id, $tahun_aktif->id);
        if (!$pengajuan) {
            $this->session->set_flashdata('error', 'Data pengajuan tidak ditemukan.');
            redirect('Skripsi_pengajuan');
        }

        // Cek apakah ada revisi dari pembimbing
        $status_p1 = isset($pengajuan->status_acc_judul_p1) ? $pengajuan->status_acc_judul_p1 : 'pending';
        $status_p2 = isset($pengajuan->status_acc_judul_p2) ? $pengajuan->status_acc_judul_p2 : 'pending';
        
        if ($status_p1 != 'revisi' && $status_p2 != 'revisi') {
            $this->session->set_flashdata('error', 'Tidak ada revisi dari pembimbing.');
            redirect('Skripsi_pengajuan');
        }

        if ($this->input->method() === 'post') {
            if (empty($_FILES['file_persetujuan_judul']['name'])) {
                $this->session->set_flashdata('error', 'Berkas persetujuan judul wajib diunggah.');
                redirect('Skripsi_pengajuan');
            }

            $this->load->helper('google_drive');
            require_google_drive_or_redirect();
            $this->load->library('Upload_to_drive');
            $share_emails = array();
            $sekprodi_owner = $this->upload_to_drive->find_connected_sekprodi_user_id();
            if ($sekprodi_owner) {
                $email = $this->upload_to_drive->get_drive_email($sekprodi_owner);
                if ($email) {
                    $share_emails[] = $email;
                }
            }
            $upload = $this->upload_to_drive->handle_upload('file_persetujuan_judul', $this->upload_to_drive->default_options(array('SIAP', 'Skripsi', 'persetujuan_judul'), $share_emails));
            if (empty($upload['ok'])) {
                $this->session->set_flashdata('error', isset($upload['message']) ? $upload['message'] : 'Gagal upload berkas.');
                redirect('Skripsi_pengajuan');
            }
            $file_path = $upload['webViewLink'];
            
            $update_data = array(
                'file_persetujuan_judul' => $file_path,
                'status_approve_revisi' => 'pending'
            );
            
            if ($this->Skripsi_pengajuan_model->update($pengajuan->id, $update_data)) {
                $this->session->set_flashdata('success', 'Berkas persetujuan judul berhasil diupload. Menunggu validasi dari sekprodi.');
            } else {
                $this->session->set_flashdata('error', 'Gagal menyimpan data.');
            }
        }

        redirect('Skripsi_pengajuan');
    }

    /**
     * Submit judul revisi dan file bukti ACC oleh mahasiswa (gabungan)
     */
    public function submit_judul_revisi_dan_file() {
        $this->check_role('mahasiswa');
        
        $id_mahasiswa = $this->session->userdata('ref_id');
        $mahasiswa = $this->db->get_where('mahasiswa', array('id' => $id_mahasiswa))->row();
        
        if (!$mahasiswa) {
            $this->session->set_flashdata('error', 'Data mahasiswa tidak ditemukan.');
            redirect('Skripsi_pengajuan');
        }

        $tahun_aktif = $this->Tahun_akademik_model->get_aktif();
        if (!$tahun_aktif) {
            $this->session->set_flashdata('error', 'Tidak ada tahun akademik aktif.');
            redirect('Skripsi_pengajuan');
        }

        $pengajuan = $this->Skripsi_pengajuan_model->get_latest_by_mahasiswa($mahasiswa->id, $tahun_aktif->id);
        if (!$pengajuan) {
            $this->session->set_flashdata('error', 'Data pengajuan tidak ditemukan.');
            redirect('Skripsi_pengajuan');
        }

        // Cek apakah ada revisi dari pembimbing
        $status_p1 = isset($pengajuan->status_acc_judul_p1) ? $pengajuan->status_acc_judul_p1 : 'pending';
        $status_p2 = isset($pengajuan->status_acc_judul_p2) ? $pengajuan->status_acc_judul_p2 : 'pending';
        
        if ($status_p1 != 'revisi' && $status_p2 != 'revisi') {
            $this->session->set_flashdata('error', 'Tidak ada revisi dari pembimbing.');
            redirect('Skripsi_pengajuan');
        }

        $this->form_validation->set_rules('judul_revisi', 'Judul Revisi', 'required|trim|min_length[10]');

        if ($this->form_validation->run() == FALSE) {
            $this->session->set_flashdata('error', validation_errors());
            redirect('Skripsi_pengajuan');
        }

        if (empty($_FILES['file_persetujuan_judul']['name'])) {
            $this->session->set_flashdata('error', 'Berkas persetujuan judul wajib diunggah.');
            redirect('Skripsi_pengajuan');
        }

        $this->load->helper('google_drive');
        require_google_drive_or_redirect();
        $this->load->library('Upload_to_drive');
        $share_emails = array();
        $sekprodi_owner = $this->upload_to_drive->find_connected_sekprodi_user_id();
        if ($sekprodi_owner) {
            $email = $this->upload_to_drive->get_drive_email($sekprodi_owner);
            if ($email) {
                $share_emails[] = $email;
            }
        }
        $upload = $this->upload_to_drive->handle_upload('file_persetujuan_judul', $this->upload_to_drive->default_options(array('SIAP', 'Skripsi', 'persetujuan_judul'), $share_emails));
        if (empty($upload['ok'])) {
            $this->session->set_flashdata('error', isset($upload['message']) ? $upload['message'] : 'Gagal upload berkas.');
            redirect('Skripsi_pengajuan');
        }
        $file_path = $upload['webViewLink'];

        $judul_revisi = $this->input->post('judul_revisi');
        
        $update_data = array(
            'judul_revisi' => $judul_revisi,
            'file_persetujuan_judul' => $file_path,
            'status_approve_revisi' => 'pending'
        );
        
        if ($this->Skripsi_pengajuan_model->update($pengajuan->id, $update_data)) {
            $this->session->set_flashdata('success', 'Pengajuan perubahan judul dan file bukti ACC berhasil dikirim. Menunggu validasi dari sekprodi.');
        } else {
            $this->session->set_flashdata('error', 'Gagal mengirim pengajuan perubahan judul.');
        }

        redirect('Skripsi_pengajuan');
    }

    /**
     * Approve/reject berkas persetujuan judul oleh sekprodi
     */
    public function approve_persetujuan($id) {
        $this->check_role('sekprodi');

        if ($this->input->method() === 'post') {
            $action = $this->input->post('action');
            
            $pengajuan = $this->Skripsi_pengajuan_model->get_by_id($id);
            if (!$pengajuan) {
                $this->session->set_flashdata('error', 'Data pengajuan tidak ditemukan.');
                redirect('Skripsi_pengajuan/review');
            }

            $id_sekprodi = $this->session->userdata('user_id');
            
            if ($action === 'approve') {
                // Approve judul revisi atau berkas persetujuan
                $update_data = array(
                    'status_approve_revisi' => 'disetujui',
                    'approved_by' => $id_sekprodi,
                    'approved_at' => date('Y-m-d H:i:s')
                );
                
                $judul_final = null;
                
                // Jika ada judul revisi, otomatis update judul_disetujui dengan judul revisi
                if (!empty($pengajuan->judul_revisi)) {
                    // Update judul_disetujui dengan judul revisi (otomatis tanpa perlu pembimbing ACC lagi)
                    $update_data['judul_disetujui'] = $pengajuan->judul_revisi;
                    $judul_final = $pengajuan->judul_revisi;
                    
                    // Update status ACC pembimbing menjadi 'acc' jika masih 'revisi'
                    if ($pengajuan->status_acc_judul_p1 == 'revisi') {
                        $update_data['status_acc_judul_p1'] = 'acc';
                    }
                    if ($pengajuan->status_acc_judul_p2 == 'revisi') {
                        $update_data['status_acc_judul_p2'] = 'acc';
                    }
                } else {
                    // Jika tidak ada judul revisi, berarti approve file persetujuan saja
                    // Update status ACC pembimbing menjadi 'acc' jika sudah ada file persetujuan
                    if ($pengajuan->status_acc_judul_p1 == 'revisi') {
                        $update_data['status_acc_judul_p1'] = 'acc';
                    }
                    if ($pengajuan->status_acc_judul_p2 == 'revisi') {
                        $update_data['status_acc_judul_p2'] = 'acc';
                    }
                    $judul_final = $pengajuan->judul_disetujui ?: $pengajuan->judul_pengajuan;
                }
                
                if ($this->Skripsi_pengajuan_model->update($id, $update_data)) {
                    // Update mahasiswa_skripsi dengan judul terbaru dan aktifkan bimbingan
                    $skripsi = $this->Skripsi_model->get_by_mahasiswa($pengajuan->id_mahasiswa, $pengajuan->id_tahun_akademik);
                    if ($skripsi) {
                        $update_skripsi = array('status' => 'sedang_skripsi');
                        
                        // Update judul_skripsi dengan judul terbaru
                        if ($judul_final) {
                            $update_skripsi['judul_skripsi'] = $judul_final;
                        }
                        
                        $this->Skripsi_model->update($skripsi->id, $update_skripsi);
                        
                        // Update judul_topik di semua bimbingan skripsi untuk mahasiswa ini
                        if ($judul_final) {
                            $this->load->model('Skripsi_bimbingan_model');
                            $bimbingan_list = $this->Skripsi_bimbingan_model->get_by_mahasiswa($pengajuan->id_mahasiswa, $pengajuan->id_tahun_akademik);
                            foreach ($bimbingan_list as $bimbingan) {
                                $this->Skripsi_bimbingan_model->update($bimbingan->id, array(
                                    'judul_topik' => $judul_final
                                ));
                            }
                        }
                    }
                    
                    if (!empty($pengajuan->judul_revisi)) {
                        $this->session->set_flashdata('success', 'Judul revisi disetujui. Judul yang berlaku sekarang otomatis berubah menjadi: <strong>' . htmlspecialchars($pengajuan->judul_revisi) . '</strong>. Bimbingan skripsi sekarang aktif.');
                    } else {
                        $this->session->set_flashdata('success', 'Berkas persetujuan judul disetujui. Bimbingan skripsi sekarang aktif.');
                    }
                } else {
                    $this->session->set_flashdata('error', 'Gagal menyetujui.');
                }
            } elseif ($action === 'reject') {
                $catatan = $this->input->post('catatan');
                $update_data = array(
                    'status_approve_revisi' => 'ditolak',
                    'catatan_sekprodi' => $catatan
                );
                
                if ($this->Skripsi_pengajuan_model->update($id, $update_data)) {
                    $this->session->set_flashdata('success', 'Berkas persetujuan judul ditolak.');
                } else {
                    $this->session->set_flashdata('error', 'Gagal menolak berkas persetujuan.');
                }
            }
        }

        redirect('Skripsi_pengajuan/review');
    }

    /**
     * Validasi Google Drive link
     */
    public function validate_google_drive_link($link) {
        $this->load->helper('google_drive');
        
        if (empty($link)) {
            return true; // Opsional, tidak perlu validasi jika kosong
        }
        
        if (!is_valid_google_drive_link($link)) {
            $this->form_validation->set_message('validate_google_drive_link', 'Link Google Drive tidak valid. Pastikan link adalah link Google Drive yang valid.');
            return false;
        }
        
        return true;
    }

    /**
     * Redirect ke Google Drive link (untuk kompatibilitas dengan link lama)
     */
    public function download_persetujuan($id) {
        $pengajuan = $this->Skripsi_pengajuan_model->get_by_id($id);
        
        if (!$pengajuan || empty($pengajuan->file_persetujuan_judul)) {
            $this->session->set_flashdata('error', 'Link tidak ditemukan.');
            redirect('Skripsi_pengajuan');
        }

        // Cek akses berdasarkan role
        $role = $this->session->userdata('role');
        $id_user = $this->session->userdata('ref_id');
        
        if ($role === 'mahasiswa') {
            // Mahasiswa hanya bisa akses file mereka sendiri
            if ($pengajuan->id_mahasiswa != $id_user) {
                $this->session->set_flashdata('error', 'Anda tidak memiliki akses untuk mengakses link ini.');
                redirect('Skripsi_pengajuan');
            }
        } elseif ($role === 'sekprodi') {
            // Sekprodi bisa akses semua
            // No additional check needed
        } elseif ($role === 'dosen') {
            // Dosen hanya bisa akses jika mereka adalah pembimbing
            $is_pembimbing_1 = ($pengajuan->id_pembimbing_1_final == $id_user);
            $is_pembimbing_2 = ($pengajuan->id_pembimbing_2_final == $id_user);
            
            if (!$is_pembimbing_1 && !$is_pembimbing_2) {
                $this->session->set_flashdata('error', 'Anda tidak memiliki akses untuk mengakses link ini.');
                redirect('Dashboard');
            }
        } else {
            show_404();
        }

        // Redirect ke Google Drive link
        $this->load->helper('google_drive');
        $link = normalize_google_drive_link($pengajuan->file_persetujuan_judul);
        redirect($link);
    }

    /**
     * Perbaiki topik yang direvisi
     */
    public function perbaiki_topik($log_id) {
        $this->check_role('mahasiswa');
        
        $id_mahasiswa = $this->session->userdata('ref_id');
        $mahasiswa = $this->db->get_where('mahasiswa', array('id' => $id_mahasiswa))->row();
        
        if (!$mahasiswa) {
            $this->session->set_flashdata('error', 'Data mahasiswa tidak ditemukan.');
            redirect('Skripsi_pengajuan');
        }

        // Ambil log topik yang akan diperbaiki
        $log_topik = $this->Skripsi_bimbingan_log_model->get_by_id($log_id);
        if (!$log_topik || $log_topik->role_pengguna !== 'mahasiswa') {
            $this->session->set_flashdata('error', 'Data topik tidak ditemukan.');
            redirect('Skripsi_pengajuan');
        }

        // Ambil bimbingan
        $bimbingan = $this->Skripsi_bimbingan_model->get_by_id($log_topik->id_bimbingan);
        if (!$bimbingan || $bimbingan->id_mahasiswa != $mahasiswa->id) {
            $this->session->set_flashdata('error', 'Data bimbingan tidak ditemukan.');
            redirect('Skripsi_pengajuan');
        }

        // Cek apakah topik ini benar-benar direvisi
        $log_revisi_dosen = $this->db->select('sbl.*')
            ->from('skripsi_bimbingan_log sbl')
            ->where('sbl.id_bimbingan', $log_topik->id_bimbingan)
            ->where('sbl.role_pengguna', 'dosen')
            ->where('sbl.status_revisi', 'revisi')
            ->where('sbl.created_at >=', $log_topik->created_at)
            ->order_by('sbl.created_at', 'DESC')
            ->limit(1)
            ->get()
            ->row();

        if (!$log_revisi_dosen) {
            $this->session->set_flashdata('error', 'Topik ini tidak dalam status revisi.');
            redirect('Skripsi_pengajuan');
        }

        if ($this->input->method() === 'post') {
            $this->form_validation->set_rules('judul_topik', 'Judul/Topik Bimbingan', 'required|trim|callback_valid_skripsi_topik_bimbingan');

            if ($this->form_validation->run() == FALSE) {
                $this->session->set_flashdata('error', validation_errors());
                redirect('Skripsi_pengajuan/perbaiki_topik/' . $log_id);
            }

            $judul_topik = $this->input->post('judul_topik');
            $file_path = null;
            $file_name = null;

            // Upload file jika ada
            if (!empty($_FILES['file_bimbingan']['name'])) {
                $config['upload_path'] = './uploads/skripsi/bimbingan/';
                $config['allowed_types'] = 'pdf|doc|docx|jpg|jpeg|png';
                $config['max_size'] = 10240; // 10MB
                $config['encrypt_name'] = TRUE;

                if (!is_dir($config['upload_path'])) {
                    mkdir($config['upload_path'], 0755, true);
                }

                $this->load->library('upload', $config);
                if ($this->upload->do_upload('file_bimbingan')) {
                    $file_data = $this->upload->data();
                    $file_path = 'uploads/skripsi/bimbingan/' . $file_data['file_name'];
                    $file_name = $file_data['orig_name'];
                } else {
                    $this->session->set_flashdata('error', 'Upload file gagal: ' . $this->upload->display_errors('', ''));
                    redirect('Skripsi_pengajuan/perbaiki_topik/' . $log_id);
                }
            }

            // Buat log baru untuk perbaikan topik (masih pada bimbingan yang sama)
            $log_data = array(
                'id_bimbingan' => $log_topik->id_bimbingan,
                'id_pengguna' => $this->session->userdata('user_id'),
                'role_pengguna' => 'mahasiswa',
                'pesan' => $judul_topik, // Topik yang diperbaiki
                'status_revisi' => NULL, // Awalnya pending lagi
                'keterangan_revisi' => NULL,
                'file_path' => $file_path,
                'file_name' => $file_name
            );

            if ($this->Skripsi_bimbingan_log_model->create($log_data)) {
                // Update last_activity
                $this->Skripsi_bimbingan_model->update($log_topik->id_bimbingan, array('last_activity' => date('Y-m-d H:i:s')));
                $this->session->set_flashdata('success', 'Perbaikan topik berhasil dikirim. Menunggu tanggapan pembimbing.');
            } else {
                $this->session->set_flashdata('error', 'Gagal mengirim perbaikan topik.');
            }

            redirect('Skripsi_pengajuan');
        }

        // Tampilkan form perbaikan
        $tahun_aktif = $this->Tahun_akademik_model->get_aktif();
        $pengajuan = $this->Skripsi_pengajuan_model->get_latest_by_mahasiswa($mahasiswa->id, $tahun_aktif ? $tahun_aktif->id : null);

        $data = array(
            'title' => 'Perbaiki Topik Bimbingan',
            'page_title' => 'Perbaiki Topik Bimbingan',
            'breadcrumb' => array(
                array('label' => 'Pengajuan Judul Skripsi', 'url' => site_url('Skripsi_pengajuan')),
                array('label' => 'Perbaiki Topik', 'active' => true)
            ),
            'sidebar_menu' => generate_sidebar_menu('mahasiswa'),
            'log_topik' => $log_topik,
            'log_revisi_dosen' => $log_revisi_dosen,
            'bimbingan' => $bimbingan,
            'pengajuan' => $pengajuan
        );

        $data['content'] = $this->load->view('skripsi_pengajuan/perbaiki_topik', $data, true);
        $this->load->view('layouts/main', $data);
    }

    /**
     * Validasi topik bimbingan skripsi (hanya pilihan terbatas).
     */
    public function valid_skripsi_topik_bimbingan($str) {
        $this->load->helper('app');
        if (!valid_skripsi_topik_bimbingan_label($str)) {
            $this->form_validation->set_message('valid_skripsi_topik_bimbingan', 'Pilih salah satu topik bimbingan yang telah ditentukan.');
            return false;
        }
        return true;
    }
}

