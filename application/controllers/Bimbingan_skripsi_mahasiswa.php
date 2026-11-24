<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Bimbingan_skripsi_mahasiswa Controller
 * Menangani bimbingan skripsi oleh mahasiswa
 */
class Bimbingan_skripsi_mahasiswa extends MY_Controller {

    public function __construct() {
        parent::__construct();
        $this->check_role('mahasiswa');
        $this->load->model('Skripsi_bimbingan_model');
        $this->load->model('Skripsi_bimbingan_log_model');
        $this->load->model('Skripsi_model');
        $this->load->model('Skripsi_pengajuan_model');
        $this->load->model('Skripsi_nilai_bimbingan_model');
        $this->load->model('Tahun_akademik_model');
        $this->load->library('form_validation');
        $this->load->helper('file');
        $this->load->helper('notifikasi');
    }

    /**
     * Status proses bimbingan (sama konsep PKL / Bimbingan_skripsi dosen).
     */
    private function enrich_bimbingan_workflow_status($bimbingan_list) {
        if (empty($bimbingan_list) || !is_array($bimbingan_list)) {
            return $bimbingan_list;
        }
        foreach ($bimbingan_list as $bimbingan) {
            $workflow = array(
                'key' => 'waiting_review',
                'label' => 'Menunggu Review Dosen',
                'class' => 'info'
            );
            if (($bimbingan->status ?? '') === 'closed') {
                $workflow = array(
                    'key' => 'closed',
                    'label' => 'Bimbingan Ditutup',
                    'class' => 'secondary'
                );
            } else {
                $latest_log = $this->Skripsi_bimbingan_log_model->get_latest_by_bimbingan($bimbingan->id);
                if ($latest_log) {
                    if (($latest_log->role_pengguna ?? '') === 'dosen' && ($latest_log->status_revisi ?? '') === 'revisi') {
                        $workflow = array(
                            'key' => 'need_revision',
                            'label' => 'Perlu Revisi Mahasiswa',
                            'class' => 'warning'
                        );
                    } elseif (($latest_log->role_pengguna ?? '') === 'dosen' && ($latest_log->status_revisi ?? '') === 'acc') {
                        $workflow = array(
                            'key' => 'approved',
                            'label' => 'ACC Pembimbing',
                            'class' => 'success'
                        );
                    }
                }
            }
            $bimbingan->workflow_status_key = $workflow['key'];
            $bimbingan->workflow_status_label = $workflow['label'];
            $bimbingan->workflow_status_class = $workflow['class'];
        }
        return $bimbingan_list;
    }

    /**
     * Bimbingan Skripsi - Daftar Topik Bimbingan
     */
    public function bimbingan() {
        $id_mahasiswa = $this->session->userdata('ref_id');
        $tahun_aktif = $this->Tahun_akademik_model->get_aktif();
        
        if (!$tahun_aktif) {
            $this->session->set_flashdata('error', 'Tidak ada tahun akademik aktif.');
            redirect('Halaman_utama');
        }

        // Ambil pengajuan skripsi
        $pengajuan = $this->Skripsi_pengajuan_model->get_latest_by_mahasiswa($id_mahasiswa, $tahun_aktif->id);

        if (!$pengajuan) {
            $this->session->set_flashdata('error', 'Data pengajuan tidak ditemukan.');
            redirect('Halaman_utama');
        }

        // RULE BARU:
        // Menu bimbingan aktif jika judul sudah ACC oleh Pembimbing 1,
        // ATAU jika Pembimbing 1 meminta revisi dan mahasiswa sudah upload berkas persetujuan/perubahan
        // lalu disetujui Sekprodi (status_approve_revisi = disetujui).
        $status_p1 = $pengajuan->status_acc_judul_p1 ?? 'pending';
        $status_approve_revisi = $pengajuan->status_approve_revisi ?? 'pending';

        if ($status_p1 !== 'acc' && $status_approve_revisi !== 'disetujui') {
            $this->session->set_flashdata(
                'error',
                'Bimbingan belum aktif. Syarat: judul di-ACC oleh Pembimbing 1, atau perubahan judul disetujui Sekprodi.'
            );
            redirect('Halaman_utama');
        }

        // Filter pembimbing (1 atau 2) dan status topik open/closed
        $filter_pembimbing = $this->input->get('pembimbing'); // 1 atau 2
        $status_filter = $this->input->get('status');

        $bimbingan_list = $this->Skripsi_bimbingan_model->get_by_mahasiswa(
            $id_mahasiswa,
            $tahun_aktif->id,
            $status_filter ? $status_filter : null
        );
        
        if ($filter_pembimbing) {
            $bimbingan_list = array_values(array_filter($bimbingan_list, function ($b) use ($filter_pembimbing) {
                return $b->tipe_pembimbing == $filter_pembimbing;
            }));
        }

        $bimbingan_list = $this->enrich_bimbingan_workflow_status($bimbingan_list);

        // Ambil data pembimbing untuk label
        $this->load->model('Dosen_model');
        $pembimbing_1 = $pengajuan->id_pembimbing_1_final ? $this->Dosen_model->get_by_id($pengajuan->id_pembimbing_1_final) : null;
        $pembimbing_2 = $pengajuan->id_pembimbing_2_final ? $this->Dosen_model->get_by_id($pengajuan->id_pembimbing_2_final) : null;

        $data = array(
            'title' => 'Bimbingan Skripsi',
            'page_title' => 'Bimbingan Skripsi',
            'breadcrumb' => array(
                array('label' => 'Skripsi', 'url' => site_url('Skripsi_pengajuan')),
                array('label' => 'Bimbingan Skripsi', 'active' => true)
            ),
            'sidebar_menu' => generate_sidebar_menu('mahasiswa'),
            'bimbingan_list' => $bimbingan_list,
            'pengajuan' => $pengajuan,
            'tahun_aktif' => $tahun_aktif,
            'pembimbing_1' => $pembimbing_1,
            'pembimbing_2' => $pembimbing_2,
            'filter_pembimbing' => $filter_pembimbing,
            'status_filter' => $status_filter
        );

        $data['content'] = $this->load->view('mahasiswa/skripsi/bimbingan', $data, true);
        $this->load->view('layouts/main', $data);
    }

    /**
     * Index - daftar bimbingan mahasiswa (backward compatibility)
     */
    public function index() {
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

        // Ambil pengajuan terbaru
        $pengajuan = $this->Skripsi_pengajuan_model->get_latest_by_mahasiswa($mahasiswa->id, $tahun_aktif->id);
        if (!$pengajuan) {
            $this->session->set_flashdata('error', 'Data pengajuan tidak ditemukan.');
            redirect('Dashboard');
        }

        // RULE BARU: lihat penjelasan di method bimbingan()
        $status_p1 = $pengajuan->status_acc_judul_p1 ?? 'pending';
        $status_approve_revisi = $pengajuan->status_approve_revisi ?? 'pending';
        if ($status_p1 !== 'acc' && $status_approve_revisi !== 'disetujui') {
            $this->session->set_flashdata('error', 'Bimbingan belum aktif. Syarat: judul di-ACC Pembimbing 1 atau perubahan judul disetujui Sekprodi.');
            redirect('Dashboard');
        }

        // Update status skripsi menjadi sedang_skripsi jika belum
        $skripsi = $this->Skripsi_model->get_by_mahasiswa($mahasiswa->id, $tahun_aktif->id);
        if ($skripsi && $skripsi->status == 'terdaftar') {
            $this->Skripsi_model->update($skripsi->id, array('status' => 'sedang_skripsi'));
        }

        $status_filter = $this->input->get('status');

        // Ambil bimbingan yang sudah ada
        $bimbingan_list = $this->Skripsi_bimbingan_model->get_by_mahasiswa(
            $mahasiswa->id,
            $tahun_aktif->id,
            $status_filter ? $status_filter : null
        );
        
        // Tambahkan status ACC untuk setiap bimbingan
        foreach ($bimbingan_list as &$bimbingan) {
            $logs_dosen = $this->db->select('*')
                ->from('skripsi_bimbingan_log')
                ->where('id_bimbingan', $bimbingan->id)
                ->where('role_pengguna', 'dosen')
                ->where('status_revisi', 'acc')
                ->get()
                ->result();
            
            $bimbingan->status_acc = !empty($logs_dosen);
        }
        unset($bimbingan);

        $bimbingan_list = $this->enrich_bimbingan_workflow_status($bimbingan_list);

        // Siapkan data pembimbing untuk dropdown
        $pembimbing_list = array();
        if ($pengajuan->id_pembimbing_1_final) {
            $pembimbing_1 = $this->db->get_where('dosen', array('id' => $pengajuan->id_pembimbing_1_final))->row();
            if ($pembimbing_1) {
                $has_pending = $this->Skripsi_bimbingan_model->has_pending_topic(
                    $mahasiswa->id, 
                    $pembimbing_1->id, 
                    $tahun_aktif->id
                );
                $pembimbing_list[] = array(
                    'id' => $pembimbing_1->id,
                    'nama' => $pembimbing_1->nama_dosen,
                    'tipe' => 1,
                    'has_pending' => $has_pending
                );
            }
        }
        if ($pengajuan->id_pembimbing_2_final) {
            $pembimbing_2 = $this->db->get_where('dosen', array('id' => $pengajuan->id_pembimbing_2_final))->row();
            if ($pembimbing_2) {
                $has_pending = $this->Skripsi_bimbingan_model->has_pending_topic(
                    $mahasiswa->id, 
                    $pembimbing_2->id, 
                    $tahun_aktif->id
                );
                $pembimbing_list[] = array(
                    'id' => $pembimbing_2->id,
                    'nama' => $pembimbing_2->nama_dosen,
                    'tipe' => 2,
                    'has_pending' => $has_pending
                );
            }
        }

        $data = array(
            'title' => 'Bimbingan Skripsi',
            'page_title' => 'Bimbingan Skripsi',
            'breadcrumb' => array(
                array('label' => 'Skripsi', 'url' => site_url('Skripsi_pengajuan')),
                array('label' => 'Bimbingan', 'active' => true)
            ),
            'sidebar_menu' => generate_sidebar_menu('mahasiswa'),
            'bimbingan_list' => $bimbingan_list,
            'pembimbing_list' => $pembimbing_list,
            'tahun_aktif' => $tahun_aktif,
            'pengajuan' => $pengajuan,
            'status_filter' => $status_filter
        );

        $data['content'] = $this->load->view('bimbingan_skripsi_mahasiswa/index', $data, true);
        $this->load->view('layouts/main', $data);
    }

    /**
     * Detail bimbingan (backward compatibility - redirect ke bimbingan_detail)
     * Menerima ID terenkripsi
     */
    public function detail($encoded_id) {
        $id = decode_id($encoded_id);
        if ($id === false) {
            show_404();
        }
        redirect('Bimbingan_skripsi_mahasiswa/bimbingan_detail/' . encode_id($id));
    }

    /**
     * Kirim pesan bimbingan (backward compatibility - redirect ke bimbingan_detail)
     * Menerima ID terenkripsi
     */
    public function kirim_pesan($encoded_id) {
        $id = decode_id($encoded_id);
        if ($id === false) {
            show_404();
        }
        redirect('Bimbingan_skripsi_mahasiswa/bimbingan_detail/' . encode_id($id));
    }

    /**
     * Buat topik bimbingan baru
     */
    public function create_topik() {
        if ($this->input->method() !== 'post') {
            show_404();
        }

        $id_mahasiswa = $this->session->userdata('ref_id');
        $mahasiswa = $this->db->get_where('mahasiswa', array('id' => $id_mahasiswa))->row();
        
        if (!$mahasiswa) {
            $this->session->set_flashdata('error', 'Data mahasiswa tidak ditemukan.');
            redirect('Bimbingan_skripsi_mahasiswa');
        }

        $tahun_aktif = $this->Tahun_akademik_model->get_aktif();
        if (!$tahun_aktif) {
            $this->session->set_flashdata('error', 'Tidak ada tahun akademik aktif.');
            redirect('Bimbingan_skripsi_mahasiswa');
        }

        // Cek apakah sudah ada pengajuan
        $pengajuan = $this->Skripsi_pengajuan_model->get_latest_by_mahasiswa($mahasiswa->id, $tahun_aktif->id);
        if (!$pengajuan) {
            $this->session->set_flashdata('error', 'Data pengajuan tidak ditemukan.');
            redirect('Bimbingan_skripsi_mahasiswa');
        }

        // Gate bimbingan: Pembimbing 1 ACC atau revisi sudah disetujui Sekprodi
        $status_p1 = $pengajuan->status_acc_judul_p1 ?? 'pending';
        $status_approve_revisi = $pengajuan->status_approve_revisi ?? 'pending';
        if ($status_p1 !== 'acc' && $status_approve_revisi !== 'disetujui') {
            $this->session->set_flashdata('error', 'Bimbingan belum aktif. Syarat: judul di-ACC Pembimbing 1 atau perubahan judul disetujui Sekprodi.');
            redirect('Bimbingan_skripsi_mahasiswa');
        }

        $this->form_validation->set_rules('id_pembimbing', 'Pembimbing', 'required|trim');
        $this->form_validation->set_rules('judul_topik', 'Judul/Topik Bimbingan', 'required|trim|callback_valid_skripsi_topik_bimbingan');
        $this->form_validation->set_rules('pesan_pembuka', 'Pesan Pembuka', 'required|trim|min_length[10]');

        if ($this->form_validation->run() == FALSE) {
            $this->session->set_flashdata('error', validation_errors());
            redirect('Bimbingan_skripsi_mahasiswa');
        }

        $id_pembimbing = $this->input->post('id_pembimbing');
        $judul_topik = $this->input->post('judul_topik');
        $pesan_pembuka = $this->input->post('pesan_pembuka');

        // Validasi: cek apakah pembimbing adalah pembimbing 1 atau 2
        $is_pembimbing_1 = ($pengajuan->id_pembimbing_1_final == $id_pembimbing);
        $is_pembimbing_2 = ($pengajuan->id_pembimbing_2_final == $id_pembimbing);
        
        if (!$is_pembimbing_1 && !$is_pembimbing_2) {
            $this->session->set_flashdata('error', 'Pembimbing yang dipilih tidak valid.');
            redirect('Bimbingan_skripsi_mahasiswa');
        }

        // Jika pembimbing 2 belum ACC judul, batasi topik hanya untuk pembimbing 1.
        $status_p2 = $pengajuan->status_acc_judul_p2 ?? 'pending';
        if ($is_pembimbing_2 && $status_p2 != 'acc') {
            $this->session->set_flashdata('error', 'Pembimbing 2 belum ACC judul. Bimbingan sementara hanya bisa dengan Pembimbing 1.');
            redirect('Bimbingan_skripsi_mahasiswa');
        }

        // Validasi: cek apakah ada topik pending pada pembimbing ini
        if ($this->Skripsi_bimbingan_model->has_pending_topic($mahasiswa->id, $id_pembimbing, $tahun_aktif->id)) {
            $this->session->set_flashdata('error', 'Anda tidak bisa menambah topik baru karena masih ada topik yang belum di-ACC oleh pembimbing ini.');
            redirect('Bimbingan_skripsi_mahasiswa');
        }

        // Tentukan tipe pembimbing
        $tipe_pembimbing = $is_pembimbing_1 ? 1 : 2;

        // Buat bimbingan baru
        $bimbingan_data = array(
            'id_mahasiswa' => $mahasiswa->id,
            'id_pembimbing' => $id_pembimbing,
            'tipe_pembimbing' => $tipe_pembimbing,
            'id_tahun_akademik' => $tahun_aktif->id,
            'judul_topik' => $judul_topik,
            'status' => 'open',
            'last_activity' => date('Y-m-d H:i:s')
        );

        $id_bimbingan = $this->Skripsi_bimbingan_model->create($bimbingan_data);

        if ($id_bimbingan) {
            // Buat log pertama (pesan pembuka dari mahasiswa)
            $id_pengguna = $this->session->userdata('user_id');
            $log_data = array(
                'id_bimbingan' => $id_bimbingan,
                'id_pengguna' => $id_pengguna,
                'role_pengguna' => 'mahasiswa',
                'pesan' => $pesan_pembuka,
                'status_revisi' => null,
                'keterangan_revisi' => null
            );

            $this->Skripsi_bimbingan_log_model->create($log_data);

            send_in_app_notification_by_ref(
                'dosen',
                (int) $id_pembimbing,
                'Topik Bimbingan Skripsi Baru',
                'Mahasiswa mengajukan topik bimbingan: ' . $judul_topik,
                site_url('Bimbingan_skripsi/detail/' . $id_bimbingan)
            );

            $this->session->set_flashdata('success', 'Topik bimbingan berhasil dibuat. Menunggu respons dari pembimbing.');
        } else {
            $this->session->set_flashdata('error', 'Gagal membuat topik bimbingan.');
        }

        redirect('Bimbingan_skripsi_mahasiswa');
    }

    /**
     * Buat Topik Bimbingan Baru (dengan Google Drive)
     */
    public function bimbingan_create() {
        $id_mahasiswa = $this->session->userdata('ref_id');
        $tahun_aktif = $this->Tahun_akademik_model->get_aktif();
        
        if (!$tahun_aktif) {
            $this->session->set_flashdata('error', 'Tidak ada tahun akademik aktif.');
            redirect('Bimbingan_skripsi_mahasiswa/bimbingan');
        }

        // Ambil pengajuan skripsi
        $pengajuan = $this->Skripsi_pengajuan_model->get_latest_by_mahasiswa($id_mahasiswa, $tahun_aktif->id);
        
        if (!$pengajuan) {
            $this->session->set_flashdata('error', 'Data pengajuan tidak ditemukan.');
            redirect('Bimbingan_skripsi_mahasiswa/bimbingan');
        }

        // Gate bimbingan: Pembimbing 1 ACC atau revisi disetujui Sekprodi
        $status_p1 = $pengajuan->status_acc_judul_p1 ?? 'pending';
        $status_p2 = $pengajuan->status_acc_judul_p2 ?? 'pending';
        $status_approve_revisi = $pengajuan->status_approve_revisi ?? 'pending';
        if ($status_p1 !== 'acc' && $status_approve_revisi !== 'disetujui') {
            $this->session->set_flashdata('error', 'Bimbingan belum aktif. Syarat: judul di-ACC Pembimbing 1 atau perubahan judul disetujui Sekprodi.');
            redirect('Bimbingan_skripsi_mahasiswa/bimbingan');
        }

        if ($this->input->method() === 'post') {
            $this->form_validation->set_rules('id_pembimbing', 'Pembimbing', 'required|trim');
            $this->form_validation->set_rules('judul_topik', 'Judul Topik', 'required|trim|callback_valid_skripsi_topik_bimbingan');
            $this->form_validation->set_rules('pesan', 'Pesan', 'trim');

            if ($this->form_validation->run() == FALSE) {
                // Load form dengan error
            } else {
                $id_pembimbing = $this->input->post('id_pembimbing');
                
                // Validasi pembimbing
                $is_pembimbing_1 = ($pengajuan->id_pembimbing_1_final == $id_pembimbing);
                $is_pembimbing_2 = ($pengajuan->id_pembimbing_2_final == $id_pembimbing);
                
                if (!$is_pembimbing_1 && !$is_pembimbing_2) {
                    $this->session->set_flashdata('error', 'Pembimbing yang dipilih tidak valid.');
                } else {
                    // Jika pembimbing 2 belum ACC judul, batasi topik hanya untuk pembimbing 1.
                    if ($is_pembimbing_2 && ($status_p2 ?? 'pending') != 'acc') {
                        $this->session->set_flashdata('error', 'Pembimbing 2 belum ACC judul. Bimbingan sementara hanya bisa dengan Pembimbing 1.');
                        redirect('Bimbingan_skripsi_mahasiswa/bimbingan');
                    }
                    if (empty($_FILES['file_laporan']['name'])) {
                        $this->session->set_flashdata('error', 'Berkas laporan wajib diunggah.');
                    } else {
                        $this->load->helper('google_drive');
                        require_google_drive_or_redirect();
                        $this->load->library('Upload_to_drive');
                        $share_emails = array();
                        $email = $this->upload_to_drive->get_drive_email_by_role_ref('dosen', (int) $id_pembimbing);
                        if ($email) {
                            $share_emails[] = $email;
                        }
                        $upload = $this->upload_to_drive->handle_upload('file_laporan', $this->upload_to_drive->default_options(array('SIAP', 'Skripsi', 'bimbingan'), $share_emails));
                        if (empty($upload['ok'])) {
                            $this->session->set_flashdata('error', isset($upload['message']) ? $upload['message'] : 'Gagal upload berkas.');
                            redirect('Bimbingan_skripsi_mahasiswa/bimbingan_create');
                        }
                        
                        // Tentukan tipe pembimbing
                        $tipe_pembimbing = $is_pembimbing_1 ? 1 : 2;
                        $judul_topik = $this->input->post('judul_topik');
                        
                        // Buat topik bimbingan
                        $bimbingan_data = array(
                            'id_mahasiswa' => $id_mahasiswa,
                            'id_pembimbing' => $id_pembimbing,
                            'tipe_pembimbing' => $tipe_pembimbing,
                            'id_tahun_akademik' => $tahun_aktif->id,
                            'judul_topik' => $judul_topik,
                            'status' => 'open',
                            'last_activity' => date('Y-m-d H:i:s')
                        );
                        
                        $id_bimbingan = $this->Skripsi_bimbingan_model->create($bimbingan_data);
                        
                        if ($id_bimbingan) {
                            // Buat log pertama
                            $user_id = $this->session->userdata('user_id');
                            $urutan = $this->Skripsi_bimbingan_log_model->get_next_urutan($id_bimbingan);
                            
                            $log_data = array(
                                'id_bimbingan' => $id_bimbingan,
                                'id_pengguna' => $user_id,
                                'role_pengguna' => 'mahasiswa',
                                'pesan' => $this->input->post('pesan') ?: 'Topik bimbingan baru',
                                'status_revisi' => null,
                                'keterangan_revisi' => null,
                                'file_path' => $upload['webViewLink'],
                                'file_name' => !empty($upload['file_name']) ? $upload['file_name'] : ('Laporan Skripsi - ' . $judul_topik),
                                'urutan' => $urutan
                            );
                            
                            $this->Skripsi_bimbingan_log_model->create($log_data);

                            send_in_app_notification_by_ref(
                                'dosen',
                                (int) $id_pembimbing,
                                'Topik Bimbingan Skripsi Baru',
                                'Mahasiswa mengajukan topik bimbingan: ' . $this->input->post('judul_topik'),
                                site_url('Bimbingan_skripsi/detail/' . $id_bimbingan)
                            );
                            
                            $this->session->set_flashdata('success', 'Topik bimbingan berhasil dibuat. Menunggu review dari pembimbing.');
                            redirect('Bimbingan_skripsi_mahasiswa/bimbingan_detail/' . encode_id($id_bimbingan));
                        } else {
                            $this->session->set_flashdata('error', 'Gagal membuat topik bimbingan.');
                        }
                    }
                }
            }
        }

        // Ambil data pembimbing untuk dropdown
        $this->load->model('Dosen_model');
        $pembimbing_1 = $pengajuan->id_pembimbing_1_final ? $this->Dosen_model->get_by_id($pengajuan->id_pembimbing_1_final) : null;
        $pembimbing_2 = $pengajuan->id_pembimbing_2_final ? $this->Dosen_model->get_by_id($pengajuan->id_pembimbing_2_final) : null;

        $data = array(
            'title' => 'Buat Topik Bimbingan',
            'page_title' => 'Buat Topik Bimbingan Baru',
            'breadcrumb' => array(
                array('label' => 'Skripsi', 'url' => site_url('Skripsi_pengajuan')),
                array('label' => 'Bimbingan Skripsi', 'url' => site_url('Bimbingan_skripsi_mahasiswa/bimbingan')),
                array('label' => 'Buat Topik Baru', 'active' => true)
            ),
            'sidebar_menu' => generate_sidebar_menu('mahasiswa'),
            'pengajuan' => $pengajuan,
            'pembimbing_1' => $pembimbing_1,
            'pembimbing_2' => $pembimbing_2
        );

        $data['content'] = $this->load->view('mahasiswa/skripsi/bimbingan_create', $data, true);
        $this->load->view('layouts/main', $data);
    }

    /**
     * Detail Topik Bimbingan
     * Menerima ID terenkripsi di URL
     */
    public function bimbingan_detail($encoded_id) {
        $id = decode_id($encoded_id);
        if ($id === false) {
            show_404();
        }
        $id_mahasiswa = $this->session->userdata('ref_id');
        $bimbingan = $this->Skripsi_bimbingan_model->get_by_id($id);
        
        if (!$bimbingan || $bimbingan->id_mahasiswa != $id_mahasiswa) {
            show_404();
        }

        // Ambil semua log bimbingan
        $logs = $this->Skripsi_bimbingan_log_model->get_by_bimbingan($id);
        
        // Ambil statistik
        $statistik = $this->Skripsi_bimbingan_log_model->get_statistik_by_bimbingan($id);
        
        // Cek status terakhir
        $latest_log = !empty($logs) ? end($logs) : null;
        $can_send_revisi = false;
        if ($latest_log && $latest_log->role_pengguna === 'dosen' && $latest_log->status_revisi === 'revisi') {
            $can_send_revisi = true;
        }

        $data = array(
            'title' => 'Detail Bimbingan Skripsi',
            'page_title' => 'Detail Bimbingan: ' . $bimbingan->judul_topik,
            'breadcrumb' => array(
                array('label' => 'Skripsi', 'url' => site_url('Skripsi_pengajuan')),
                array('label' => 'Bimbingan Skripsi', 'url' => site_url('Bimbingan_skripsi_mahasiswa/bimbingan')),
                array('label' => 'Detail', 'active' => true)
            ),
            'sidebar_menu' => generate_sidebar_menu('mahasiswa'),
            'bimbingan' => $bimbingan,
            'logs' => $logs,
            'statistik' => $statistik,
            'can_send_revisi' => $can_send_revisi
        );

        $data['content'] = $this->load->view('mahasiswa/skripsi/bimbingan_detail', $data, true);
        $this->load->view('layouts/main', $data);
    }

    /**
     * Kirim Pesan/Revisi Bimbingan (dengan Google Drive)
     * Menerima ID terenkripsi
     */
    public function bimbingan_kirim($encoded_id) {
        $id = decode_id($encoded_id);
        if ($id === false) {
            show_404();
        }
        $id_mahasiswa = $this->session->userdata('ref_id');
        $bimbingan = $this->Skripsi_bimbingan_model->get_by_id($id);
        
        if (!$bimbingan || $bimbingan->id_mahasiswa != $id_mahasiswa) {
            show_404();
        }

        if ($this->input->method() !== 'post') {
            show_404();
        }

        // Cek apakah bisa kirim revisi
        $logs = $this->Skripsi_bimbingan_log_model->get_by_bimbingan($id);
        $latest_log = !empty($logs) ? end($logs) : null;
        
        if (!$latest_log || $latest_log->role_pengguna !== 'dosen' || $latest_log->status_revisi !== 'revisi') {
            $this->session->set_flashdata('error', 'Anda tidak dapat mengirim revisi saat ini.');
            redirect('Bimbingan_skripsi_mahasiswa/bimbingan_detail/' . encode_id($id));
        }

        $this->form_validation->set_rules('pesan', 'Pesan', 'trim');

        if ($this->form_validation->run() == FALSE) {
            $this->session->set_flashdata('error', validation_errors());
            redirect('Bimbingan_skripsi_mahasiswa/bimbingan_detail/' . encode_id($id));
        }

        if (empty($_FILES['file_laporan']['name'])) {
            $this->session->set_flashdata('error', 'Berkas laporan revisi wajib diunggah.');
            redirect('Bimbingan_skripsi_mahasiswa/bimbingan_detail/' . encode_id($id));
        }

        $this->load->helper('google_drive');
        require_google_drive_or_redirect();
        $this->load->library('Upload_to_drive');
        $share_emails = array();
        $email = $this->upload_to_drive->get_drive_email_by_role_ref('dosen', (int) $bimbingan->id_pembimbing);
        if ($email) {
            $share_emails[] = $email;
        }
        $upload = $this->upload_to_drive->handle_upload('file_laporan', $this->upload_to_drive->default_options(array('SIAP', 'Skripsi', 'bimbingan'), $share_emails));
        if (empty($upload['ok'])) {
            $this->session->set_flashdata('error', isset($upload['message']) ? $upload['message'] : 'Gagal upload berkas.');
            redirect('Bimbingan_skripsi_mahasiswa/bimbingan_detail/' . encode_id($id));
        }
        
        $user_id = $this->session->userdata('user_id');
        $urutan = $this->Skripsi_bimbingan_log_model->get_next_urutan($id);
        
        $log_data = array(
            'id_bimbingan' => $id,
            'id_pengguna' => $user_id,
            'role_pengguna' => 'mahasiswa',
            'pesan' => $this->input->post('pesan') ?: 'Revisi laporan',
            'status_revisi' => null,
            'keterangan_revisi' => null,
            'file_path' => $upload['webViewLink'],
            'file_name' => !empty($upload['file_name']) ? $upload['file_name'] : ('Revisi Laporan Skripsi - ' . $bimbingan->judul_topik),
            'urutan' => $urutan
        );
        
        if ($this->Skripsi_bimbingan_log_model->create($log_data)) {
            // Update last_activity
            $this->Skripsi_bimbingan_model->update($id, array('last_activity' => date('Y-m-d H:i:s')));
            send_in_app_notification_by_ref(
                'dosen',
                (int) $bimbingan->id_pembimbing,
                'Revisi Bimbingan Skripsi',
                'Mahasiswa mengirim revisi pada topik: ' . $bimbingan->judul_topik,
                site_url('Bimbingan_skripsi/detail/' . $id)
            );
            $this->session->set_flashdata('success', 'Revisi berhasil dikirim. Menunggu review dari pembimbing.');
        } else {
            $this->session->set_flashdata('error', 'Gagal mengirim revisi.');
        }

        redirect('Bimbingan_skripsi_mahasiswa/bimbingan_detail/' . encode_id($id));
    }

    /**
     * Cetak PDF Histori Bimbingan Skripsi
     * Menerima ID terenkripsi (opsional)
     */
    public function cetak_histori_bimbingan($encoded_id = null) {
        $id = null;
        if ($encoded_id) {
            $id = decode_id($encoded_id);
            if ($id === false) {
                show_404();
            }
        }
        $id_mahasiswa = $this->session->userdata('ref_id');
        $tahun_aktif = $this->Tahun_akademik_model->get_aktif();
        
        if (!$tahun_aktif) {
            $this->session->set_flashdata('error', 'Tidak ada tahun akademik aktif.');
            redirect('Bimbingan_skripsi_mahasiswa/bimbingan');
        }

        // Ambil data mahasiswa
        $this->load->model('Mahasiswa_model');
        $mahasiswa = $this->Mahasiswa_model->get_by_id($id_mahasiswa);
        
        // Ambil pengajuan skripsi
        $pengajuan = $this->Skripsi_pengajuan_model->get_latest_by_mahasiswa($id_mahasiswa, $tahun_aktif->id);
        
        if (!$pengajuan) {
            $this->session->set_flashdata('error', 'Data pengajuan tidak ditemukan.');
            redirect('Bimbingan_skripsi_mahasiswa/bimbingan');
        }

        // Ambil data pembimbing
        $this->load->model('Dosen_model');
        $pembimbing_1 = $pengajuan->id_pembimbing_1_final ? $this->Dosen_model->get_by_id($pengajuan->id_pembimbing_1_final) : null;
        $pembimbing_2 = $pengajuan->id_pembimbing_2_final ? $this->Dosen_model->get_by_id($pengajuan->id_pembimbing_2_final) : null;

        // Jika ada ID topik spesifik, hanya ambil topik tersebut
        if ($id) {
            $bimbingan_specific = $this->Skripsi_bimbingan_model->get_by_id($id);
            if (!$bimbingan_specific || $bimbingan_specific->id_mahasiswa != $id_mahasiswa) {
                $this->session->set_flashdata('error', 'Data bimbingan tidak ditemukan.');
                redirect('Bimbingan_skripsi_mahasiswa/bimbingan');
            }
            $bimbingan_list = array($bimbingan_specific);
        } else {
            // Ambil semua topik bimbingan
            $bimbingan_list = $this->Skripsi_bimbingan_model->get_by_mahasiswa($id_mahasiswa, $tahun_aktif->id);
        }
        
        // Ambil data skripsi untuk cek layak sidang
        $skripsi = $this->Skripsi_model->get_by_mahasiswa($id_mahasiswa, $tahun_aktif->id);
        
        // Siapkan histori pengajuan judul dan ACC judul
        $histori_pengajuan = array();
        
        // 1. Pengajuan judul
        if ($pengajuan && $pengajuan->created_at) {
            $histori_pengajuan[] = array(
                'tanggal' => $pengajuan->created_at,
                'topik' => 'Pengajuan Judul Skripsi',
                'pengirim' => $mahasiswa->nama_mahasiswa,
                'status' => 'pending',
                'keterangan' => $pengajuan->judul_pengajuan
            );
        }
        
        // 2. ACC judul pembimbing 1
        if ($pengajuan && $pengajuan->status_acc_judul_p1 === 'acc' && $pembimbing_1) {
            // Cari updated_at dari pengajuan (perkiraan waktu ACC)
            $histori_pengajuan[] = array(
                'tanggal' => $pengajuan->updated_at ?: $pengajuan->created_at,
                'topik' => 'ACC Judul Skripsi',
                'pengirim' => $pembimbing_1->nama_dosen,
                'status' => 'acc',
                'keterangan' => 'Pembimbing 1 menyetujui judul: ' . ($pengajuan->judul_disetujui ?: $pengajuan->judul_pengajuan)
            );
        }
        
        // 3. ACC judul pembimbing 2
        if ($pengajuan && $pengajuan->status_acc_judul_p2 === 'acc' && $pembimbing_2) {
            $histori_pengajuan[] = array(
                'tanggal' => $pengajuan->updated_at ?: $pengajuan->created_at,
                'topik' => 'ACC Judul Skripsi',
                'pengirim' => $pembimbing_2->nama_dosen,
                'status' => 'acc',
                'keterangan' => 'Pembimbing 2 menyetujui judul: ' . ($pengajuan->judul_disetujui ?: $pengajuan->judul_pengajuan)
            );
        }
        
        // 4. ACC layak sidang pembimbing 1
        if ($skripsi && $skripsi->acc_pembimbing_1 == 1 && $pembimbing_1) {
            // Cari waktu ACC dari nilai bimbingan jika ada
            $nilai_p1 = $this->Skripsi_nilai_bimbingan_model->get_by_skripsi_tipe($skripsi->id, 1);
            $tanggal_acc_p1 = $nilai_p1 && isset($nilai_p1->created_at) ? $nilai_p1->created_at : ($skripsi->updated_at ?: date('Y-m-d H:i:s'));
            
            $histori_pengajuan[] = array(
                'tanggal' => $tanggal_acc_p1,
                'topik' => 'Layak Sidang',
                'pengirim' => $pembimbing_1->nama_dosen,
                'status' => 'acc',
                'keterangan' => 'Pembimbing 1 menyatakan layak sidang'
            );
        }
        
        // 5. ACC layak sidang pembimbing 2
        if ($skripsi && $skripsi->acc_pembimbing_2 == 1 && $pembimbing_2) {
            // Cari waktu ACC dari nilai bimbingan jika ada
            $nilai_p2 = $this->Skripsi_nilai_bimbingan_model->get_by_skripsi_tipe($skripsi->id, 2);
            $tanggal_acc_p2 = $nilai_p2 && isset($nilai_p2->created_at) ? $nilai_p2->created_at : ($skripsi->updated_at ?: date('Y-m-d H:i:s'));
            
            $histori_pengajuan[] = array(
                'tanggal' => $tanggal_acc_p2,
                'topik' => 'Layak Sidang',
                'pengirim' => $pembimbing_2->nama_dosen,
                'status' => 'acc',
                'keterangan' => 'Pembimbing 2 menyatakan layak sidang'
            );
        }
        
        // Ambil semua log bimbingan
        $all_logs = array();
        $total_topik = count($bimbingan_list);
        $total_log = 0;
        $total_acc = 0;
        $total_revisi = 0;
        
        foreach ($bimbingan_list as $bimbingan) {
            $logs = $this->Skripsi_bimbingan_log_model->get_by_bimbingan($bimbingan->id);
            $all_logs[$bimbingan->id] = array(
                'bimbingan' => $bimbingan,
                'logs' => $logs
            );
            $total_log += count($logs);
            foreach ($logs as $log) {
                if ($log->status_revisi === 'acc') $total_acc++;
                if ($log->status_revisi === 'revisi') $total_revisi++;
            }
        }
        

        // Load library PDF dan helper
        $this->load->library('Pdf');
        $this->load->helper('app');
        
        $data = array(
            'mahasiswa' => $mahasiswa,
            'pembimbing_1' => $pembimbing_1,
            'pembimbing_2' => $pembimbing_2,
            'pengajuan' => $pengajuan,
            'skripsi' => $skripsi,
            'bimbingan_list' => $bimbingan_list,
            'all_logs' => $all_logs,
            'histori_pengajuan' => $histori_pengajuan,
            'tahun_aktif' => $tahun_aktif,
            'statistik' => (object)array(
                'total_topik' => $total_topik,
                'total_log' => $total_log,
                'total_acc' => $total_acc,
                'total_revisi' => $total_revisi
            )
        );
        
        // Generate HTML untuk PDF
        $html = $this->load->view('mahasiswa/skripsi/cetak_histori_bimbingan', $data, true);
        
        // Load HTML ke PDF library
        $this->pdf->load_html($html);
        $this->pdf->set_paper('A4', 'portrait');
        
        // Generate filename
        $filename = 'Histori_Bimbingan_Skripsi_' . $mahasiswa->nim . '_' . date('Ymd') . '.pdf';
        
        // Stream PDF
        $this->pdf->stream($filename);
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

