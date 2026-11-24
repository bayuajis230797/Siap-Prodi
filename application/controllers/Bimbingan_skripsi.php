<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Bimbingan_skripsi extends MY_Controller {

    public function __construct() {
        parent::__construct();
        $this->check_role('dosen');
        $this->load->model('Skripsi_bimbingan_model');
        $this->load->model('Skripsi_bimbingan_log_model');
        $this->load->model('Skripsi_model');
        $this->load->model('Skripsi_nilai_bimbingan_model');
        $this->load->model('Skripsi_pengajuan_model');
        $this->load->model('Tahun_akademik_model');
        $this->load->library('form_validation');
        $this->load->library('Skripsi_penilaian');
        $this->load->helper('notifikasi');
    }

    /**
     * Status proses bimbingan (sama konsep PKL).
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

    public function index() {
        $id_dosen = $this->session->userdata('ref_id');
        if (!$id_dosen) {
            show_404();
        }

        $tahun_aktif = $this->Tahun_akademik_model->get_aktif();
        $status = $this->input->get('status');
        
        // Ambil semua bimbingan untuk pembimbing ini (seperti PKL, dari tabel bimbingan bukan log)
        $bimbingan_list = $this->Skripsi_bimbingan_model->get_by_pembimbing(
            $id_dosen, 
            $status, 
            $tahun_aktif ? $tahun_aktif->id : null
        );
        $bimbingan_list = $this->enrich_bimbingan_workflow_status($bimbingan_list);

        $data = array(
            'title' => 'Bimbingan Skripsi',
            'page_title' => 'Bimbingan Skripsi',
            'breadcrumb' => array(array('label' => 'Bimbingan Skripsi', 'active' => true)),
            'sidebar_menu' => generate_sidebar_menu('dosen'),
            'bimbingan_list' => $bimbingan_list,
            'status_filter' => $status
        );

        $data['content'] = $this->load->view('dosen/skripsi_bimbingan_index', $data, true);
        $this->load->view('layouts/main', $data);
    }

    public function detail($id) {
        $id_dosen = $this->session->userdata('ref_id');
        if (!$id_dosen) {
            show_404();
        }

        $bimbingan = $this->Skripsi_bimbingan_model->get_by_id($id);
        if (!$bimbingan || $bimbingan->id_pembimbing != $id_dosen) {
            show_404();
        }

        $logs = $this->Skripsi_bimbingan_log_model->get_by_bimbingan($id);

        // Ambil topik terbaru yang diajukan mahasiswa
        $ajuan_topik = $this->Skripsi_bimbingan_log_model->get_latest_mahasiswa_topic_pending_dosen($id, $id_dosen);
        
        // Jika tidak ada topik pending, ambil topik terbaru dari mahasiswa
        if (!$ajuan_topik) {
            $ajuan_topik = $this->db->select('sbl.*')
                ->from('skripsi_bimbingan_log sbl')
                ->join('skripsi_bimbingan sb', 'sb.id = sbl.id_bimbingan')
                ->where('sbl.id_bimbingan', $id)
                ->where('sbl.role_pengguna', 'mahasiswa')
                ->where('sb.id_pembimbing', $id_dosen)
                ->order_by('sbl.created_at', 'DESC')
                ->limit(1)
                ->get()
                ->row();
        }
        
        // Cek apakah topik sudah di-ACC atau direvisi
        $topik_sudah_acc = false;
        if ($ajuan_topik) {
            // Cek apakah ada log ACC dari dosen untuk topik ini
            $log_dosen = $this->db->select('sbl.id, sbl.status_revisi')
                ->from('skripsi_bimbingan_log sbl')
                ->where('sbl.id_bimbingan', $id)
                ->where('sbl.role_pengguna', 'dosen')
                ->where('sbl.status_revisi IS NOT NULL')
                ->where('sbl.status_revisi !=', '')
                ->where('sbl.created_at >=', $ajuan_topik->created_at) // Log dosen harus setelah atau sama dengan log mahasiswa
                ->order_by('sbl.created_at', 'DESC')
                ->limit(1)
                ->get()
                ->row();
            
            $topik_sudah_acc = ($log_dosen && $log_dosen->status_revisi === 'acc');
        }

        $tahun_aktif = $this->Tahun_akademik_model->get_aktif();
        $skripsi = null;
        $can_acc_layak_sidang = false;
        $nilai_bimbingan = null;

        if ($tahun_aktif) {
            $skripsi = $this->Skripsi_model->get_by_mahasiswa($bimbingan->id_mahasiswa, $tahun_aktif->id);
            if ($skripsi) {
                $nilai_bimbingan = $this->Skripsi_nilai_bimbingan_model->get_by_skripsi_tipe($skripsi->id, $bimbingan->tipe_pembimbing);
                if (in_array($skripsi->status, array('sedang_skripsi', 'layak_sidang', 'selesai_skripsi'))) {
                    if ($bimbingan->tipe_pembimbing == 1) {
                        $can_acc_layak_sidang = ($skripsi->acc_pembimbing_1 == 0);
                    } else {
                        $can_acc_layak_sidang = ($skripsi->acc_pembimbing_2 == 0);
                    }
                }
            }
        }

        $data = array(
            'title' => 'Detail Bimbingan Skripsi',
            'page_title' => 'Detail Bimbingan Skripsi',
            'breadcrumb' => array(
                array('label' => 'Bimbingan Skripsi', 'url' => site_url('Bimbingan_skripsi')),
                array('label' => 'Detail', 'active' => true)
            ),
            'sidebar_menu' => generate_sidebar_menu('dosen'),
            'bimbingan' => $bimbingan,
            'logs' => $logs,
            'ajuan_topik' => $ajuan_topik,
            'topik_sudah_acc' => $topik_sudah_acc,
            'skripsi' => $skripsi,
            'tahun_aktif' => $tahun_aktif,
            'can_acc_layak_sidang' => $can_acc_layak_sidang,
            'nilai_bimbingan' => $nilai_bimbingan
        );

        $data['content'] = $this->load->view('dosen/skripsi_bimbingan_detail', $data, true);
        $this->load->view('layouts/main', $data);
    }

    public function kirim_pesan($id) {
        if ($this->input->method() !== 'post') {
            show_404();
        }

        $id_dosen = $this->session->userdata('ref_id');
        $user_id = $this->session->userdata('user_id');
        if (!$id_dosen || !$user_id) {
            show_404();
        }

        $bimbingan = $this->Skripsi_bimbingan_model->get_by_id($id);
        if (!$bimbingan || $bimbingan->id_pembimbing != $id_dosen) {
            show_404();
        }

        // Status revisi wajib
        $this->form_validation->set_rules('status_revisi', 'Status', 'required|in_list[acc,revisi]');
        
        $status_revisi = $this->input->post('status_revisi');
        $keterangan_revisi = $this->input->post('keterangan_revisi');
        
        // Jika status revisi, keterangan wajib
        if ($status_revisi === 'revisi') {
            $this->form_validation->set_rules('keterangan_revisi', 'Keterangan Revisi', 'required|trim|min_length[5]');
        }
        
        if ($this->form_validation->run() === FALSE) {
            $this->session->set_flashdata('error', validation_errors());
            redirect('Bimbingan_skripsi/detail/' . $id);
        }
        
        $file_path = null;
        $file_name = null;
        if (!empty($_FILES['file_google_drive']['name'])) {
            $this->load->helper('google_drive');
            require_google_drive_or_redirect();
            $this->load->library('Upload_to_drive');
            $share_emails = array();
            $email = $this->upload_to_drive->get_drive_email_by_role_ref('mahasiswa', (int) $bimbingan->id_mahasiswa);
            if ($email) {
                $share_emails[] = $email;
            }
            $upload = $this->upload_to_drive->handle_upload('file_google_drive', $this->upload_to_drive->default_options(array('SIAP', 'Skripsi', 'bimbingan'), $share_emails));
            if (empty($upload['ok'])) {
                $this->session->set_flashdata('error', isset($upload['message']) ? $upload['message'] : 'Gagal upload berkas.');
                redirect('Bimbingan_skripsi/detail/' . $id);
            }
            $file_path = $upload['webViewLink'];
            $file_name = !empty($upload['file_name']) ? $upload['file_name'] : 'File Bimbingan Dosen';
        }

        // Pesan tambahan (opsional)
        $pesan_final = $this->input->post('pesan') ? trim($this->input->post('pesan')) : '';

        $urutan = $this->Skripsi_bimbingan_log_model->get_next_urutan($id);

        $this->Skripsi_bimbingan_log_model->create(array(
            'id_bimbingan' => $id,
            'id_pengguna' => $user_id,
            'role_pengguna' => 'dosen',
            'pesan' => $pesan_final,
            'status_revisi' => $status_revisi ? $status_revisi : null,
            'keterangan_revisi' => $keterangan_revisi ? $keterangan_revisi : null,
            'file_path' => $file_path,
            'file_name' => $file_name,
            'urutan' => $urutan
        ));

        $this->Skripsi_bimbingan_model->update($id, array(
            'last_activity' => date('Y-m-d H:i:s')
        ));

        $notif_title = ($status_revisi === 'acc') ? 'Bimbingan Skripsi Di-ACC' : 'Revisi Bimbingan Skripsi';
        $notif_message = ($status_revisi === 'acc')
            ? 'Dosen pembimbing telah memberikan ACC pada topik bimbingan skripsi Anda.'
            : 'Dosen pembimbing meminta revisi pada topik bimbingan skripsi Anda.';
        send_in_app_notification_by_ref(
            'mahasiswa',
            (int) $bimbingan->id_mahasiswa,
            $notif_title,
            $notif_message,
            site_url('Bimbingan_skripsi_mahasiswa/bimbingan_detail/' . encode_id($id))
        );

        $this->session->set_flashdata('success', 'Pesan berhasil dikirim.');
        redirect('Bimbingan_skripsi/detail/' . $id);
    }

    /**
     * Download file bimbingan (untuk backward compatibility dengan file upload)
     * Jika file_path adalah link Google Drive, redirect ke link tersebut
     */
    public function download_file($log_id) {
        $id_dosen = $this->session->userdata('ref_id');
        if (!$id_dosen) {
            show_404();
        }

        $log = $this->Skripsi_bimbingan_log_model->get_by_id($log_id);
        if (!$log || !$log->file_path) {
            show_404();
        }

        $bimbingan = $this->Skripsi_bimbingan_model->get_by_id($log->id_bimbingan);
        if (!$bimbingan || $bimbingan->id_pembimbing != $id_dosen) {
            show_404();
        }

        // Cek apakah file_path adalah link Google Drive
        $this->load->helper('google_drive');
        if (is_valid_google_drive_link($log->file_path)) {
            // Redirect ke Google Drive
            $normalized_url = normalize_google_drive_link($log->file_path);
            redirect($normalized_url, 'location', 302);
        } else {
            // File upload biasa (backward compatibility)
            $file_path = FCPATH . $log->file_path;
            if (!file_exists($file_path)) {
                $this->session->set_flashdata('error', 'File tidak ditemukan.');
                redirect('Bimbingan_skripsi/detail/' . $log->id_bimbingan);
            }

            $this->load->helper('download');
            force_download($log->file_name ?: basename($file_path), file_get_contents($file_path));
        }
    }

    public function acc_layak_sidang($id) {
        $id_dosen = $this->session->userdata('ref_id');
        if (!$id_dosen) {
            show_404();
        }

        $bimbingan = $this->Skripsi_bimbingan_model->get_by_id($id);
        if (!$bimbingan || $bimbingan->id_pembimbing != $id_dosen) {
            show_404();
        }

        $tahun_aktif = $this->Tahun_akademik_model->get_aktif();
        if (!$tahun_aktif) {
            $this->session->set_flashdata('error', 'Tidak ada tahun akademik aktif.');
            redirect('Bimbingan_skripsi/detail/' . $id);
        }

        $skripsi = $this->Skripsi_model->get_by_mahasiswa($bimbingan->id_mahasiswa, $tahun_aktif->id);
        if (!$skripsi) {
            $this->session->set_flashdata('error', 'Data skripsi tidak ditemukan.');
            redirect('Bimbingan_skripsi/detail/' . $id);
        }

        if (!in_array($skripsi->status, array('sedang_skripsi', 'layak_sidang'))) {
            $this->session->set_flashdata('error', 'Penilaian bimbingan hanya tersedia ketika status skripsi sedang atau layak sidang.');
            redirect('Bimbingan_skripsi/detail/' . $id);
        }

        // Form 4-aspek lama tidak punya view. Penilaian bimbingan + ACC layak sidang
        // dilakukan lewat form item penilaian P1 yang dikelola Admin.
        redirect('Skripsi_mahasiswa_dosen/siap_sidang/' . $skripsi->id);

        $nilai_existing = $this->Skripsi_nilai_bimbingan_model->get_by_skripsi_tipe($skripsi->id, $bimbingan->tipe_pembimbing);

        if ($this->input->method() === 'post') {
            $this->form_validation->set_rules('kedisiplinan', 'Kedisiplinan', 'required|numeric|greater_than_equal_to[0]|less_than_equal_to[100]');
            $this->form_validation->set_rules('kreativitas', 'Kreativitas', 'required|numeric|greater_than_equal_to[0]|less_than_equal_to[100]');
            $this->form_validation->set_rules('penguasaan_materi', 'Penguasaan Materi', 'required|numeric|greater_than_equal_to[0]|less_than_equal_to[100]');
            $this->form_validation->set_rules('kelengkapan_referensi', 'Kelengkapan & Referensi', 'required|numeric|greater_than_equal_to[0]|less_than_equal_to[100]');

            if ($this->form_validation->run() === FALSE) {
                $this->session->set_flashdata('error', validation_errors());
                redirect('Bimbingan_skripsi/acc_layak_sidang/' . $id);
            }

            $kedisiplinan = (float)$this->input->post('kedisiplinan');
            $kreativitas = (float)$this->input->post('kreativitas');
            $penguasaan_materi = (float)$this->input->post('penguasaan_materi');
            $kelengkapan = (float)$this->input->post('kelengkapan_referensi');
            $catatan = $this->input->post('catatan');

            $nilai_rata = round(($kedisiplinan + $kreativitas + $penguasaan_materi + $kelengkapan) / 4, 2);

            $save_data = array(
                'id_mahasiswa_skripsi' => $skripsi->id,
                'id_dosen' => $id_dosen,
                'tipe_pembimbing' => $bimbingan->tipe_pembimbing,
                'id_tahun_akademik' => $tahun_aktif->id,
                'kedisiplinan' => $kedisiplinan,
                'kreativitas' => $kreativitas,
                'penguasaan_materi' => $penguasaan_materi,
                'kelengkapan_referensi' => $kelengkapan,
                'nilai_rata' => $nilai_rata,
                'catatan' => !empty($catatan) ? $catatan : null
            );

            $this->Skripsi_nilai_bimbingan_model->upsert($save_data);

            $update_data = array();
            if ($bimbingan->tipe_pembimbing == 1) {
                $update_data['acc_pembimbing_1'] = 1;
            } else {
                $update_data['acc_pembimbing_2'] = 1;
            }
            $this->Skripsi_model->update($skripsi->id, $update_data);

            $skripsi_updated = $this->Skripsi_model->get_by_id($skripsi->id);
            if ($skripsi_updated->acc_pembimbing_1 == 1 && $skripsi_updated->acc_pembimbing_2 == 1 && $skripsi_updated->status !== 'layak_sidang') {
                $this->Skripsi_model->update($skripsi->id, array('status' => 'layak_sidang'));
            }

            $this->skripsi_penilaian->sync_bimbingan($skripsi->id);

            $this->session->set_flashdata('success', 'Nilai bimbingan tersimpan dan ACC Layak Sidang berhasil direkam.');
            redirect('Bimbingan_skripsi/detail/' . $id);
        }

        $data = array(
            'title' => 'Nilai Bimbingan & ACC Layak Sidang',
            'page_title' => 'Nilai Bimbingan & ACC Layak Sidang',
            'breadcrumb' => array(
                array('label' => 'Bimbingan Skripsi', 'url' => site_url('Bimbingan_skripsi')),
                array('label' => 'Nilai Bimbingan', 'active' => true)
            ),
            'sidebar_menu' => generate_sidebar_menu('dosen'),
            'bimbingan' => $bimbingan,
            'skripsi' => $skripsi,
            'nilai' => $nilai_existing,
            'tahun_aktif' => $tahun_aktif
        );

        $data['content'] = $this->load->view('dosen/skripsi_nilai_bimbingan_form', $data, true);
        $this->load->view('layouts/main', $data);
    }

    public function tutup($id) {
        $id_dosen = $this->session->userdata('ref_id');
        if (!$id_dosen) {
            show_404();
        }

        $bimbingan = $this->Skripsi_bimbingan_model->get_by_id($id);
        if (!$bimbingan || $bimbingan->id_pembimbing != $id_dosen) {
            show_404();
        }

        $this->Skripsi_bimbingan_model->update($id, array(
            'status' => 'closed',
            'last_activity' => date('Y-m-d H:i:s')
        ));

        send_in_app_notification_by_ref(
            'mahasiswa',
            (int) $bimbingan->id_mahasiswa,
            'Topik Bimbingan Skripsi Ditutup',
            'Pembimbing menutup topik bimbingan: ' . $bimbingan->judul_topik,
            site_url('Bimbingan_skripsi_mahasiswa/bimbingan')
        );

        $this->session->set_flashdata('success', 'Bimbingan telah ditutup.');
        redirect('Bimbingan_skripsi/detail/' . $id);
    }

    public function buka($id) {
        $id_dosen = $this->session->userdata('ref_id');
        if (!$id_dosen) {
            show_404();
        }

        $bimbingan = $this->Skripsi_bimbingan_model->get_by_id($id);
        if (!$bimbingan || $bimbingan->id_pembimbing != $id_dosen) {
            show_404();
        }

        $this->Skripsi_bimbingan_model->update($id, array(
            'status' => 'open',
            'last_activity' => date('Y-m-d H:i:s')
        ));

        send_in_app_notification_by_ref(
            'mahasiswa',
            (int) $bimbingan->id_mahasiswa,
            'Topik Bimbingan Skripsi Dibuka Kembali',
            'Pembimbing membuka kembali topik: ' . $bimbingan->judul_topik,
            site_url('Bimbingan_skripsi_mahasiswa/bimbingan_detail/' . encode_id($id))
        );

        $this->session->set_flashdata('success', 'Bimbingan dibuka kembali.');
        redirect('Bimbingan_skripsi/detail/' . $id);
    }

    /**
     * List pengajuan judul skripsi yang perlu di-ACC/revisi
     */
    public function ajuan_judul() {
        $id_dosen = $this->session->userdata('ref_id');
        if (!$id_dosen) {
            show_404();
        }

        $tahun_aktif = $this->Tahun_akademik_model->get_aktif();
        
        // Ambil pengajuan dimana dosen ini adalah pembimbing 1 atau 2 dan status ACC judul masih pending
        $pengajuan_list = $this->Skripsi_pengajuan_model->get_by_pembimbing_pending($id_dosen, $tahun_aktif ? $tahun_aktif->id : null);

        $data = array(
            'title' => 'Ajuan Judul Skripsi',
            'page_title' => 'Ajuan Judul Skripsi',
            'breadcrumb' => array(array('label' => 'Ajuan Judul Skripsi', 'active' => true)),
            'sidebar_menu' => generate_sidebar_menu('dosen'),
            'pengajuan_list' => $pengajuan_list,
            'tahun_aktif' => $tahun_aktif
        );

        $data['content'] = $this->load->view('dosen/ajuan_judul', $data, true);
        $this->load->view('layouts/main', $data);
    }

    /**
     * Review dan ACC/Revisi judul skripsi
     */
    public function review_judul($id) {
        $id_dosen = $this->session->userdata('ref_id');
        if (!$id_dosen) {
            show_404();
        }

        $pengajuan = $this->Skripsi_pengajuan_model->get_by_id($id);
        if (!$pengajuan) {
            $this->session->set_flashdata('error', 'Data pengajuan tidak ditemukan.');
            redirect('Bimbingan_skripsi/ajuan_judul');
        }

        // Cek apakah dosen ini adalah pembimbing 1 atau 2
        $is_pembimbing_1 = ($pengajuan->id_pembimbing_1_final == $id_dosen);
        $is_pembimbing_2 = ($pengajuan->id_pembimbing_2_final == $id_dosen);

        if (!$is_pembimbing_1 && !$is_pembimbing_2) {
            $this->session->set_flashdata('error', 'Anda tidak memiliki akses untuk review pengajuan ini.');
            redirect('Bimbingan_skripsi/ajuan_judul');
        }

        $tipe_pembimbing = $is_pembimbing_1 ? 1 : 2;
        $status_field = $is_pembimbing_1 ? 'status_acc_judul_p1' : 'status_acc_judul_p2';
        $catatan_field = $is_pembimbing_1 ? 'catatan_revisi_p1' : 'catatan_revisi_p2';

        if ($this->input->method() === 'post') {
            $action = $this->input->post('action');

            if ($action === 'acc') {
                // ACC judul (bisa judul awal atau judul revisi)
                $update_data = array(
                    $status_field => 'acc'
                );
                
                if ($this->Skripsi_pengajuan_model->update($id, $update_data)) {
                    // Update status pengajuan jika kedua pembimbing sudah ACC
                    $this->_check_and_update_status_pengajuan($id);
                    
                    // Jika ada judul revisi dan status sebelumnya revisi, ACC berarti setujui judul revisi
                    if (!empty($pengajuan->judul_revisi) && $current_status === 'revisi') {
                        // Judul revisi sudah di-ACC oleh pembimbing ini
                        $this->session->set_flashdata('success', 'Judul revisi telah di-ACC. Menunggu persetujuan dari sekprodi.');
                    } else {
                        $this->session->set_flashdata('success', 'Judul skripsi telah di-ACC.');
                    }
                } else {
                    $this->session->set_flashdata('error', 'Gagal menyetujui judul skripsi.');
                }
                
                redirect('Bimbingan_skripsi/ajuan_judul');
            } elseif ($action === 'revisi') {
                // Revisi judul
                $catatan_revisi = $this->input->post('catatan_revisi');
                
                if (empty($catatan_revisi) || trim($catatan_revisi) === '') {
                    $this->session->set_flashdata('error', 'Catatan revisi wajib diisi.');
                    redirect('Bimbingan_skripsi/review_judul/' . $id);
                }

                if (strlen(trim($catatan_revisi)) < 10) {
                    $this->session->set_flashdata('error', 'Catatan revisi minimal 10 karakter.');
                    redirect('Bimbingan_skripsi/review_judul/' . $id);
                }

                $update_data = array(
                    $status_field => 'revisi',
                    $catatan_field => trim($catatan_revisi)
                );
                
                $result = $this->Skripsi_pengajuan_model->update($id, $update_data);
                
                if ($result > 0) {
                    $this->session->set_flashdata('success', 'Judul skripsi telah direvisi. Mahasiswa akan diminta untuk upload lembar persetujuan.');
                } else {
                    $this->session->set_flashdata('error', 'Gagal menyimpan catatan revisi. Silakan coba lagi.');
                }
                redirect('Bimbingan_skripsi/ajuan_judul');
            }
        }

        $data = array(
            'title' => 'Review Judul Skripsi',
            'page_title' => 'Review Judul Skripsi',
            'breadcrumb' => array(
                array('label' => 'Ajuan Judul Skripsi', 'url' => site_url('Bimbingan_skripsi/ajuan_judul')),
                array('label' => 'Review', 'active' => true)
            ),
            'sidebar_menu' => generate_sidebar_menu('dosen'),
            'pengajuan' => $pengajuan,
            'tipe_pembimbing' => $tipe_pembimbing,
            'is_pembimbing_1' => $is_pembimbing_1,
            'judul_aktif' => !empty($pengajuan->judul_disetujui) ? $pengajuan->judul_disetujui : $pengajuan->judul_pengajuan,
            'status_field' => $status_field,
            'current_status' => $is_pembimbing_1 ? $pengajuan->status_acc_judul_p1 : $pengajuan->status_acc_judul_p2,
            'current_catatan' => $is_pembimbing_1 ? $pengajuan->catatan_revisi_p1 : $pengajuan->catatan_revisi_p2
        );

        $data['content'] = $this->load->view('dosen/review_judul', $data, true);
        $this->load->view('layouts/main', $data);
    }

    /**
     * Ubah judul skripsi mahasiswa (hanya Pembimbing 1)
     */
    public function ubah_judul_mahasiswa($id) {
        if ($this->input->method() !== 'post') {
            show_404();
        }

        $id_dosen = $this->session->userdata('ref_id');
        if (!$id_dosen) {
            show_404();
        }

        $pengajuan = $this->Skripsi_pengajuan_model->get_by_id($id);
        if (!$pengajuan) {
            $this->session->set_flashdata('error', 'Data pengajuan tidak ditemukan.');
            redirect('Bimbingan_skripsi/ajuan_judul');
        }

        if ((int) $pengajuan->id_pembimbing_1_final !== (int) $id_dosen) {
            $this->session->set_flashdata('error', 'Hanya Pembimbing 1 yang dapat mengubah judul skripsi mahasiswa.');
            redirect('Bimbingan_skripsi/ajuan_judul');
        }

        $this->form_validation->set_rules('judul_disetujui', 'Judul Skripsi', 'required|trim|min_length[10]');
        if ($this->form_validation->run() === false) {
            $this->session->set_flashdata('error', validation_errors());
            redirect('Bimbingan_skripsi/review_judul/' . $id);
        }

        $judul_baru = trim($this->input->post('judul_disetujui'));
        if ($this->_apply_skripsi_judul_update($pengajuan, $judul_baru)) {
            send_in_app_notification_by_ref(
                'mahasiswa',
                (int) $pengajuan->id_mahasiswa,
                'Judul Skripsi Diperbarui',
                'Pembimbing 1 memperbarui judul skripsi Anda menjadi: ' . $judul_baru,
                site_url('Skripsi_pengajuan')
            );
            $this->session->set_flashdata('success', 'Judul skripsi mahasiswa berhasil diperbarui.');
        } else {
            $this->session->set_flashdata('error', 'Gagal memperbarui judul skripsi.');
        }

        redirect('Bimbingan_skripsi/review_judul/' . $id);
    }

    /**
     * Download file proposal
     */
    public function download_proposal($id_pengajuan) {
        $id_dosen = $this->session->userdata('ref_id');
        if (!$id_dosen) {
            show_404();
        }

        $pengajuan = $this->Skripsi_pengajuan_model->get_by_id($id_pengajuan);
        if (!$pengajuan) {
            $this->session->set_flashdata('error', 'File tidak ditemukan.');
            redirect('Bimbingan_skripsi/ajuan_judul');
        }

        // Cek apakah dosen ini adalah pembimbing 1 atau 2
        $is_pembimbing_1 = ($pengajuan->id_pembimbing_1_final == $id_dosen);
        $is_pembimbing_2 = ($pengajuan->id_pembimbing_2_final == $id_dosen);

        if (!$is_pembimbing_1 && !$is_pembimbing_2) {
            $this->session->set_flashdata('error', 'Anda tidak memiliki akses untuk mengunduh file ini.');
            redirect('Bimbingan_skripsi/ajuan_judul');
        }

        if (empty($pengajuan->file_proposal)) {
            $this->session->set_flashdata('error', 'File proposal tidak ditemukan.');
            redirect('Bimbingan_skripsi/ajuan_judul');
        }

        $file_path = FCPATH . 'uploads/skripsi/proposal/' . $pengajuan->file_proposal;
        if (!file_exists($file_path)) {
            $this->session->set_flashdata('error', 'File tidak ditemukan di server.');
            redirect('Bimbingan_skripsi/ajuan_judul');
        }

        $this->load->helper('download');
        force_download($file_path, NULL);
    }

    /**
     * Terapkan perubahan judul skripsi ke pengajuan, mahasiswa_skripsi, dan topik bimbingan.
     */
    private function _apply_skripsi_judul_update($pengajuan, $judul_baru) {
        $update_data = array(
            'judul_disetujui' => $judul_baru,
            'judul_pengajuan' => $judul_baru,
        );

        if (!$this->Skripsi_pengajuan_model->update($pengajuan->id, $update_data)) {
            return false;
        }

        $skripsi = $this->Skripsi_model->get_by_mahasiswa($pengajuan->id_mahasiswa, $pengajuan->id_tahun_akademik);
        if ($skripsi) {
            $this->Skripsi_model->update($skripsi->id, array(
                'judul_skripsi' => $judul_baru,
            ));

            $bimbingan_list = $this->Skripsi_bimbingan_model->get_by_mahasiswa($pengajuan->id_mahasiswa, $pengajuan->id_tahun_akademik);
            foreach ($bimbingan_list as $bimbingan) {
                $this->Skripsi_bimbingan_model->update($bimbingan->id, array(
                    'judul_topik' => $judul_baru,
                ));
            }
        }

        return true;
    }

    /**
     * Helper method untuk cek dan update status pengajuan jika kedua pembimbing sudah ACC
     */
    private function _check_and_update_status_pengajuan($id) {
        // Ambil data pengajuan terbaru
        $pengajuan = $this->Skripsi_pengajuan_model->get_by_id($id);
        
        if (!$pengajuan) {
            return false;
        }
        
        // Cek apakah ada pembimbing 1 dan pembimbing 2 (prioritas dari id_pembimbing_1_final/id_pembimbing_2_final)
        $has_pembimbing_1 = !empty($pengajuan->id_pembimbing_1_final);
        $has_pembimbing_2 = !empty($pengajuan->id_pembimbing_2_final);
        
        // Jika tidak ada pembimbing final, cek dari mahasiswa_skripsi
        if (!$has_pembimbing_1 && !empty($pengajuan->id_pembimbing_1)) {
            $has_pembimbing_1 = true;
        }
        if (!$has_pembimbing_2 && !empty($pengajuan->id_pembimbing_2)) {
            $has_pembimbing_2 = true;
        }
        
        // Cek status ACC kedua pembimbing
        $status_p1 = isset($pengajuan->status_acc_judul_p1) ? $pengajuan->status_acc_judul_p1 : 'pending';
        $status_p2 = isset($pengajuan->status_acc_judul_p2) ? $pengajuan->status_acc_judul_p2 : 'pending';
        
        // Jika kedua pembimbing sudah diplot dan sudah ACC, update status pengajuan menjadi 'disetujui'
        if ($has_pembimbing_1 && $has_pembimbing_2 && 
            $status_p1 == 'acc' && $status_p2 == 'acc' && 
            $pengajuan->status != 'disetujui') {
            $update_status = array('status' => 'disetujui');
            return $this->Skripsi_pengajuan_model->update($id, $update_status);
        }
        
        return false;
    }
}

