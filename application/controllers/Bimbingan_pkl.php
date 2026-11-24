<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Bimbingan_pkl Controller
 * Menangani bimbingan PKL oleh dosen pembimbing
 */
class Bimbingan_pkl extends MY_Controller {

    public function __construct() {
        parent::__construct();
        $this->check_role('dosen');
        $this->load->model('Pkl_bimbingan_model');
        $this->load->model('Pkl_bimbingan_log_model');
        $this->load->model('Pkl_pengajuan_model');
        $this->load->model('Tahun_akademik_model');
        $this->load->library('form_validation');
        $this->load->helper('notifikasi');
    }

    /**
     * Index - daftar bimbingan PKL
     */
    public function index() {
        $id_dosen = $this->session->userdata('ref_id');
        if (!$id_dosen) {
            show_404();
        }

        $tahun_aktif = $this->Tahun_akademik_model->get_aktif();
        $status = $this->input->get('status');
        
        // Ambil semua bimbingan PKL untuk pembimbing ini
        $bimbingan_list = $this->Pkl_bimbingan_model->get_by_pembimbing($id_dosen, $status, $tahun_aktif ? $tahun_aktif->id : null);
        $bimbingan_list = $this->enrich_bimbingan_workflow_status($bimbingan_list);

        $data = array(
            'title' => 'Bimbingan PKL',
            'page_title' => 'Bimbingan PKL',
            'breadcrumb' => array(array('label' => 'Bimbingan PKL', 'active' => true)),
            'sidebar_menu' => generate_sidebar_menu('dosen'),
            'bimbingan_list' => $bimbingan_list,
            'status_filter' => $status
        );

        $data['content'] = $this->load->view('dosen/pkl_bimbingan_index', $data, true);
        $this->load->view('layouts/main', $data);
    }

    /**
     * Detail bimbingan PKL
     */
    public function detail($id) {
        $id_dosen = $this->session->userdata('ref_id');
        if (!$id_dosen) {
            show_404();
        }

        $bimbingan = $this->Pkl_bimbingan_model->get_by_id($id);
        if (!$bimbingan || $bimbingan->id_pembimbing != $id_dosen) {
            show_404();
        }

        $logs = $this->Pkl_bimbingan_log_model->get_by_bimbingan($id);
        $latest_log = $this->Pkl_bimbingan_log_model->get_latest_by_bimbingan($id);

        // Ambil data pengajuan PKL untuk cek layak sidang
        $tahun_aktif = $this->Tahun_akademik_model->get_aktif();
        $pengajuan = null;
        $can_acc_layak_sidang = false;
        $penilaian = null;

        if ($tahun_aktif && $bimbingan->id_mahasiswa) {
            $pengajuan = $this->Pkl_pengajuan_model->get_by_mahasiswa($bimbingan->id_mahasiswa, $tahun_aktif->id);
            
            // Cek apakah pengajuan ada dan dapat ACC layak sidang
            if ($pengajuan) {
                $can_acc_layak_sidang = ($pengajuan->is_layak_sidang != 1 && $bimbingan->status === 'open');
                
                // Ambil data penilaian jika sudah layak sidang
                if ($pengajuan->is_layak_sidang == 1) {
                    // TODO: Load penilaian model jika ada
                }
            }
        }

        $workflow_status = array(
            'key' => 'waiting_review',
            'label' => 'Menunggu Review Dosen',
            'class' => 'info'
        );
        if (($bimbingan->status ?? '') === 'closed') {
            $workflow_status = array(
                'key' => 'closed',
                'label' => 'Bimbingan Ditutup',
                'class' => 'secondary'
            );
        } elseif ($latest_log) {
            if (($latest_log->role_pengguna ?? '') === 'dosen' && ($latest_log->status_revisi ?? '') === 'revisi') {
                $workflow_status = array(
                    'key' => 'need_revision',
                    'label' => 'Perlu Revisi Mahasiswa',
                    'class' => 'warning'
                );
            } elseif (($latest_log->role_pengguna ?? '') === 'dosen' && ($latest_log->status_revisi ?? '') === 'acc') {
                $workflow_status = array(
                    'key' => 'approved',
                    'label' => 'ACC Pembimbing',
                    'class' => 'success'
                );
            }
        }

        $data = array(
            'title' => 'Detail Bimbingan PKL',
            'page_title' => 'Detail Bimbingan PKL',
            'breadcrumb' => array(
                array('label' => 'Bimbingan PKL', 'url' => site_url('Bimbingan_pkl')),
                array('label' => 'Detail', 'active' => true)
            ),
            'sidebar_menu' => generate_sidebar_menu('dosen'),
            'bimbingan' => $bimbingan,
            'logs' => $logs,
            'pengajuan' => $pengajuan,
            'can_acc_layak_sidang' => $can_acc_layak_sidang,
            'penilaian' => $penilaian,
            'workflow_status' => $workflow_status
        );

        $data['content'] = $this->load->view('dosen/pkl_bimbingan_detail', $data, true);
        $this->load->view('layouts/main', $data);
    }

    /**
     * Tambahkan status proses bimbingan untuk daftar dosen.
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
                $latest_log = $this->Pkl_bimbingan_log_model->get_latest_by_bimbingan($bimbingan->id);
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
     * Kirim pesan/tanggapan bimbingan
     */
    public function kirim_pesan($id) {
        if ($this->input->method() !== 'post') {
            show_404();
        }

        $id_dosen = $this->session->userdata('ref_id');
        $user_id = $this->session->userdata('user_id');
        if (!$id_dosen || !$user_id) {
            show_404();
        }

        $bimbingan = $this->Pkl_bimbingan_model->get_by_id($id);
        if (!$bimbingan || $bimbingan->id_pembimbing != $id_dosen) {
            show_404();
        }

        $this->form_validation->set_rules('status_revisi', 'Status', 'required|in_list[acc,revisi]');
        $this->form_validation->set_rules('keterangan_revisi', 'Keterangan Revisi', 'trim');
        $this->form_validation->set_rules('pesan', 'Pesan', 'trim');

        $pesan = $this->input->post('pesan');
        $status_revisi = $this->input->post('status_revisi');
        $keterangan_revisi = $this->input->post('keterangan_revisi');
        
        // Jika status revisi, keterangan wajib
        if ($status_revisi === 'revisi' && empty(trim($keterangan_revisi))) {
            $this->form_validation->set_rules('keterangan_revisi', 'Keterangan Revisi', 'required|trim|min_length[5]');
        }

        if ($this->form_validation->run() === FALSE) {
            $this->session->set_flashdata('error', validation_errors());
            redirect('Bimbingan_pkl/detail/' . $id);
        }

        $this->load->helper('google_drive');

        $file_path = null;
        $file_name = null;
        if (!empty($_FILES['file_berkas']['name'])) {
            require_google_drive_or_redirect();
            $this->load->library('Upload_to_drive');
            $share_emails = array();
            $mhs_email = $this->upload_to_drive->get_drive_email_by_role_ref('mahasiswa', (int) $bimbingan->id_mahasiswa);
            if ($mhs_email) {
                $share_emails[] = $mhs_email;
            }
            $upload = $this->upload_to_drive->handle_upload('file_berkas', $this->upload_to_drive->default_options(
                array('SIAP', 'PKL', 'bimbingan', 'dosen'),
                $share_emails
            ));
            if (empty($upload['ok'])) {
                $this->session->set_flashdata('error', isset($upload['message']) ? $upload['message'] : 'Gagal upload berkas.');
                redirect('Bimbingan_pkl/detail/' . $id);
            }
            $file_path = $upload['webViewLink'];
            $file_name = !empty($upload['file_name']) ? $upload['file_name'] : 'File dari Pembimbing';
        }

        $urutan = $this->Pkl_bimbingan_log_model->get_next_urutan($id);
        
        $log_data = array(
            'id_bimbingan' => $id,
            'id_pengguna' => $user_id,
            'role_pengguna' => 'dosen',
            'pesan' => !empty($pesan) ? trim($pesan) : '',
            'status_revisi' => $status_revisi,
            'keterangan_revisi' => !empty($keterangan_revisi) ? trim($keterangan_revisi) : null,
            'file_path' => $file_path,
            'file_name' => $file_name,
            'urutan' => $urutan
        );

        if ($this->Pkl_bimbingan_log_model->create($log_data)) {
            // Update last_activity
            $this->Pkl_bimbingan_model->update($id, array(
                'last_activity' => date('Y-m-d H:i:s')
            ));

            $notif_title = ($status_revisi === 'acc') ? 'Bimbingan PKL Di-ACC' : 'Revisi Bimbingan PKL';
            $topik_label = !empty($bimbingan->judul_topik) ? $bimbingan->judul_topik : 'bimbingan PKL';
            $notif_message = ($status_revisi === 'acc')
                ? 'Dosen pembimbing telah memberikan ACC pada topik: ' . $topik_label
                : 'Dosen pembimbing meminta revisi pada topik: ' . $topik_label;
            send_in_app_notification_by_ref(
                'mahasiswa',
                (int) $bimbingan->id_mahasiswa,
                $notif_title,
                $notif_message,
                site_url('Pkl/bimbingan_detail/' . encode_id($id))
            );

            $this->session->set_flashdata('success', 'Review berhasil dikirim.');
        } else {
            $this->session->set_flashdata('error', 'Gagal mengirim review.');
        }

        redirect('Bimbingan_pkl/detail/' . $id);
    }

    /**
     * Hapus data bimbingan PKL (beserta log)
     */
    public function hapus($id) {
        if ($this->input->method() !== 'post') {
            show_404();
        }

        $id_dosen = $this->session->userdata('ref_id');
        if (!$id_dosen) {
            show_404();
        }

        $bimbingan = $this->Pkl_bimbingan_model->get_by_id($id);
        if (!$bimbingan || (int) $bimbingan->id_pembimbing !== (int) $id_dosen) {
            $this->session->set_flashdata('error', 'Data bimbingan tidak ditemukan atau akses ditolak.');
            redirect('Bimbingan_pkl');
        }

        if ($this->Pkl_bimbingan_model->delete($id)) {
            $this->session->set_flashdata('success', 'Data bimbingan PKL berhasil dihapus.');
        } else {
            $this->session->set_flashdata('error', 'Gagal menghapus data bimbingan PKL.');
        }

        redirect('Bimbingan_pkl');
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

        $log = $this->Pkl_bimbingan_log_model->get_by_id($log_id);
        if (!$log || !$log->file_path) {
            show_404();
        }

        $bimbingan = $this->Pkl_bimbingan_model->get_by_id($log->id_bimbingan);
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
                redirect('Bimbingan_pkl/detail/' . $log->id_bimbingan);
            }

            $this->load->helper('download');
            force_download($log->file_name ?: basename($file_path), file_get_contents($file_path));
        }
    }

    /**
     * Set layak sidang
     */
    public function layak_sidang($id) {
        $id_dosen = $this->session->userdata('ref_id');
        if (!$id_dosen) {
            show_404();
        }

        $bimbingan = $this->Pkl_bimbingan_model->get_by_id($id);
        if (!$bimbingan || $bimbingan->id_pembimbing != $id_dosen) {
            show_404();
        }

        $tahun_aktif = $this->Tahun_akademik_model->get_aktif();
        if (!$tahun_aktif) {
            $this->session->set_flashdata('error', 'Tidak ada tahun akademik aktif.');
            redirect('Bimbingan_pkl/detail/' . $id);
        }

        // Ambil pengajuan PKL untuk mahasiswa ini
        $pengajuan = $this->Pkl_pengajuan_model->get_by_mahasiswa($bimbingan->id_mahasiswa, $tahun_aktif->id);
        if (!$pengajuan) {
            $this->session->set_flashdata('error', 'Data pengajuan PKL tidak ditemukan.');
            redirect('Bimbingan_pkl/detail/' . $id);
        }

        if ($pengajuan->is_layak_sidang == 1) {
            $this->session->set_flashdata('error', 'Mahasiswa sudah dinyatakan layak sidang.');
            redirect('Bimbingan_pkl/detail/' . $id);
        }

        if ($this->input->method() === 'post') {
            // Update pengajuan PKL
            $this->Pkl_pengajuan_model->update($pengajuan->id, array(
                'is_layak_sidang' => 1
            ));

            // Tutup bimbingan
            $this->Pkl_bimbingan_model->update($id, array(
                'status' => 'closed',
                'last_activity' => date('Y-m-d H:i:s')
            ));

            send_in_app_notification_by_ref(
                'mahasiswa',
                (int) $bimbingan->id_mahasiswa,
                'Dinyatakan Layak Sidang PKL',
                'Anda dinyatakan layak sidang oleh dosen pembimbing. Silakan lanjut pendaftaran sidang PKL.',
                site_url('Pendaftaran_sidang_pkl')
            );
            send_in_app_notification_by_role(
                'sekprodi',
                'Mahasiswa Layak Sidang PKL',
                'Terdapat mahasiswa yang sudah dinyatakan layak sidang oleh dosen pembimbing.',
                site_url('Pkl_verifikasi_berkas_sidang')
            );

            $this->session->set_flashdata('success', 'Mahasiswa dinyatakan layak sidang. Bimbingan telah ditutup.');
            redirect('Bimbingan_pkl/detail/' . $id);
        }

        // Tampilkan form konfirmasi
        $data = array(
            'title' => 'Layak Sidang',
            'page_title' => 'Layak Sidang',
            'breadcrumb' => array(
                array('label' => 'Bimbingan PKL', 'url' => site_url('Bimbingan_pkl')),
                array('label' => 'Layak Sidang', 'active' => true)
            ),
            'sidebar_menu' => generate_sidebar_menu('dosen'),
            'bimbingan' => $bimbingan,
            'pengajuan' => $pengajuan
        );

        $data['content'] = $this->load->view('dosen/pkl_layak_sidang', $data, true);
        $this->load->view('layouts/main', $data);
    }

    /**
     * Tutup bimbingan
     */
    public function tutup($id) {
        $id_dosen = $this->session->userdata('ref_id');
        if (!$id_dosen) {
            show_404();
        }

        $bimbingan = $this->Pkl_bimbingan_model->get_by_id($id);
        if (!$bimbingan || $bimbingan->id_pembimbing != $id_dosen) {
            show_404();
        }

        $this->Pkl_bimbingan_model->update($id, array(
            'status' => 'closed',
            'last_activity' => date('Y-m-d H:i:s')
        ));

        $this->session->set_flashdata('success', 'Bimbingan telah ditutup.');
        redirect('Bimbingan_pkl/detail/' . $id);
    }

    /**
     * Buka kembali bimbingan
     */
    public function buka($id) {
        $id_dosen = $this->session->userdata('ref_id');
        if (!$id_dosen) {
            show_404();
        }

        $bimbingan = $this->Pkl_bimbingan_model->get_by_id($id);
        if (!$bimbingan || $bimbingan->id_pembimbing != $id_dosen) {
            show_404();
        }

        $this->Pkl_bimbingan_model->update($id, array(
            'status' => 'open',
            'last_activity' => date('Y-m-d H:i:s')
        ));

        $this->session->set_flashdata('success', 'Bimbingan dibuka kembali.');
        redirect('Bimbingan_pkl/detail/' . $id);
    }
}

