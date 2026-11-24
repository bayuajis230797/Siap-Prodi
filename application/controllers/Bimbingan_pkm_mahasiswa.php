<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Bimbingan_pkm_mahasiswa extends MY_Controller {

    public function __construct() {
        parent::__construct();
        $this->check_role('mahasiswa');
        $this->load->model('Pkm_pengajuan_model');
        $this->load->model('Pkm_bimbingan_model');
        $this->load->model('Pkm_bimbingan_log_model');
        $this->load->helper(array('menu', 'pkm', 'google_drive', 'id_encode'));
        $this->load->library('form_validation');
    }

    public function index() {
        redirect('Bimbingan_pkm_mahasiswa/bimbingan');
    }

    public function valid_pkm_topik_bimbingan($str) {
        if (!valid_pkm_topik_bimbingan_label($str)) {
            $this->form_validation->set_message('valid_pkm_topik_bimbingan', 'Topik bimbingan tidak valid.');
            return false;
        }
        return true;
    }

    public function bimbingan() {
        $id_mahasiswa = (int) $this->session->userdata('ref_id');
        $pengajuan = $this->_get_pengajuan_bimbingan($id_mahasiswa);
        if (!$pengajuan) {
            $this->session->set_flashdata('error', 'Bimbingan PKM belum aktif. Judul harus di-ACC dosen pembimbing.');
            redirect('Pkm_mahasiswa');
        }

        $is_ketua = ($pengajuan->peran === 'ketua');
        $status_filter = $this->input->get('status');
        $bimbingan_list = $this->Pkm_bimbingan_model->get_list_by_pengajuan(
            $pengajuan->id,
            $status_filter ?: null
        );
        $bimbingan_list = $this->enrich_bimbingan_workflow_status($bimbingan_list);

        $has_pending = false;
        if ($is_ketua && (int) $pengajuan->id_pembimbing_final > 0) {
            $has_pending = $this->Pkm_bimbingan_model->has_pending_topic(
                (int) $pengajuan->id_ketua,
                (int) $pengajuan->id_pembimbing_final,
                $pengajuan->id
            );
        }

        $data = array(
            'title' => 'Bimbingan PKM',
            'page_title' => 'Bimbingan PKM',
            'breadcrumb' => array(
                array('label' => 'PKM', 'url' => site_url('Pkm_mahasiswa')),
                array('label' => 'Bimbingan PKM', 'active' => true),
            ),
            'sidebar_menu' => generate_sidebar_menu('mahasiswa'),
            'bimbingan_list' => $bimbingan_list,
            'pengajuan' => $pengajuan,
            'status_filter' => $status_filter,
            'has_pending_topic' => $has_pending,
            'is_ketua' => $is_ketua,
            'proposal_final_submit_allowed' => pkm_proposal_final_submit_allowed($pengajuan),
        );
        $data['content'] = $this->load->view('mahasiswa/pkm/bimbingan_index', $data, true);
        $this->load->view('layouts/main', $data);
    }

    public function bimbingan_create() {
        $id_mahasiswa = (int) $this->session->userdata('ref_id');
        $pengajuan = $this->_get_pengajuan_bimbingan($id_mahasiswa);
        if (!$pengajuan) {
            redirect('Pkm_mahasiswa');
        }
        if ($pengajuan->peran !== 'ketua') {
            $this->session->set_flashdata('error', 'Hanya ketua tim yang dapat menambah topik bimbingan.');
            redirect('Bimbingan_pkm_mahasiswa/bimbingan');
        }

        $id_pembimbing = (int) $pengajuan->id_pembimbing_final;
        if ($id_pembimbing <= 0) {
            $this->session->set_flashdata('error', 'Dosen pembimbing belum ditetapkan.');
            redirect('Bimbingan_pkm_mahasiswa/bimbingan');
        }

        if ($this->Pkm_bimbingan_model->has_pending_topic($id_mahasiswa, $id_pembimbing, $pengajuan->id)) {
            $this->session->set_flashdata('error', 'Selesaikan topik bimbingan yang masih menunggu review dosen sebelum membuat topik baru.');
            redirect('Bimbingan_pkm_mahasiswa/bimbingan');
        }

        if ($this->input->method() === 'post') {
            $this->form_validation->set_rules('judul_topik', 'Topik Bimbingan', 'required|trim|callback_valid_pkm_topik_bimbingan');
            $this->form_validation->set_rules('pesan', 'Pesan', 'trim');

            if ($this->form_validation->run()) {
                $this->load->helper(array('google_drive', 'pkm'));
                require_google_drive_or_redirect();
                $this->load->library('Upload_to_drive');
                $share_emails = array();
                $email = $this->upload_to_drive->get_drive_email_by_role_ref('dosen', $id_pembimbing);
                if ($email) {
                    $share_emails[] = $email;
                }
                $upload = $this->upload_to_drive->handle_upload('file_laporan', $this->upload_to_drive->default_options(
                    array('SIAP', 'PKM', 'bimbingan'),
                    $share_emails
                ));
                if (empty($upload['ok'])) {
                    $this->session->set_flashdata('error', isset($upload['message']) ? $upload['message'] : 'Gagal upload dokumen.');
                } else {
                    $id_bimbingan = $this->Pkm_bimbingan_model->create(array(
                        'id_pkm_pengajuan' => $pengajuan->id,
                        'id_mahasiswa' => $id_mahasiswa,
                        'id_pembimbing' => $id_pembimbing,
                        'judul_topik' => $this->input->post('judul_topik'),
                        'status' => 'open',
                        'last_activity' => date('Y-m-d H:i:s'),
                    ));
                    if ($id_bimbingan) {
                        $this->Pkm_bimbingan_log_model->create(array(
                            'id_bimbingan' => $id_bimbingan,
                            'id_pengguna' => (int) $this->session->userdata('user_id'),
                            'role_pengguna' => 'mahasiswa',
                            'pesan' => trim($this->input->post('pesan')) ?: 'Topik bimbingan baru',
                            'file_path' => pkm_encrypt_link($upload['webViewLink']),
                            'file_name' => !empty($upload['file_name']) ? $upload['file_name'] : ('Dokumen PKM - ' . $this->input->post('judul_topik')),
                            'urutan' => 1,
                        ));
                        $this->session->set_flashdata('success', 'Topik bimbingan berhasil dibuat. Menunggu review dosen.');
                        redirect('Bimbingan_pkm_mahasiswa/bimbingan_detail/' . encode_id($id_bimbingan));
                    }
                    $this->session->set_flashdata('error', 'Gagal membuat topik bimbingan.');
                }
            }
        }

        $data = array(
            'title' => 'Buat Topik Bimbingan PKM',
            'page_title' => 'Buat Topik Bimbingan',
            'breadcrumb' => array(
                array('label' => 'Bimbingan PKM', 'url' => site_url('Bimbingan_pkm_mahasiswa/bimbingan')),
                array('label' => 'Buat Topik', 'active' => true),
            ),
            'sidebar_menu' => generate_sidebar_menu('mahasiswa'),
            'pengajuan' => $pengajuan,
        );
        $data['content'] = $this->load->view('mahasiswa/pkm/bimbingan_create', $data, true);
        $this->load->view('layouts/main', $data);
    }

    public function bimbingan_detail($encoded_id) {
        $id = decode_id($encoded_id);
        if ($id === false) {
            show_404();
        }
        $id_mahasiswa = (int) $this->session->userdata('ref_id');
        $bimbingan = $this->Pkm_bimbingan_model->get_by_id($id);
        if (!$bimbingan) {
            show_404();
        }

        $pengajuan = $this->Pkm_pengajuan_model->mahasiswa_akses_pengajuan($id_mahasiswa, $bimbingan->id_pkm_pengajuan);
        if (!$pengajuan || !pkm_bimbingan_aktif($pengajuan)) {
            show_404();
        }
        if ((int) $bimbingan->id_pkm_pengajuan !== (int) $pengajuan->id) {
            show_404();
        }

        $is_ketua = ($pengajuan->peran === 'ketua');
        $logs = $this->Pkm_bimbingan_log_model->get_by_bimbingan($id);
        $statistik = $this->Pkm_bimbingan_log_model->get_statistik_by_bimbingan($id);
        $latest_log = !empty($logs) ? end($logs) : null;
        $can_send_revisi = $is_ketua
            && ($latest_log && $latest_log->role_pengguna === 'dosen' && $latest_log->status_revisi === 'revisi');

        $data = array(
            'title' => 'Detail Bimbingan PKM',
            'page_title' => 'Detail: ' . $bimbingan->judul_topik,
            'breadcrumb' => array(
                array('label' => 'Bimbingan PKM', 'url' => site_url('Bimbingan_pkm_mahasiswa/bimbingan')),
                array('label' => 'Detail', 'active' => true),
            ),
            'sidebar_menu' => generate_sidebar_menu('mahasiswa'),
            'pengajuan' => $pengajuan,
            'bimbingan' => $bimbingan,
            'logs' => $logs,
            'statistik' => $statistik,
            'can_send_revisi' => $can_send_revisi,
            'is_ketua' => $is_ketua,
        );
        $data['content'] = $this->load->view('mahasiswa/pkm/bimbingan_detail', $data, true);
        $this->load->view('layouts/main', $data);
    }

    public function bimbingan_kirim($encoded_id) {
        if ($this->input->method() !== 'post') {
            show_404();
        }
        $id = decode_id($encoded_id);
        if ($id === false) {
            show_404();
        }
        $id_mahasiswa = (int) $this->session->userdata('ref_id');
        $user_id = (int) $this->session->userdata('user_id');
        $bimbingan = $this->Pkm_bimbingan_model->get_by_id($id);
        if (!$bimbingan || $bimbingan->status !== 'open') {
            show_404();
        }
        $pengajuan = $this->Pkm_pengajuan_model->mahasiswa_akses_pengajuan($id_mahasiswa, $bimbingan->id_pkm_pengajuan);
        if (!$pengajuan || $pengajuan->peran !== 'ketua' || (int) $bimbingan->id_mahasiswa !== (int) $pengajuan->id_ketua) {
            show_404();
        }

        $logs = $this->Pkm_bimbingan_log_model->get_by_bimbingan($id);
        $latest_log = !empty($logs) ? end($logs) : null;
        if (!$latest_log || $latest_log->role_pengguna !== 'dosen' || $latest_log->status_revisi !== 'revisi') {
            $this->session->set_flashdata('error', 'Anda tidak dapat mengirim revisi saat ini.');
            redirect('Bimbingan_pkm_mahasiswa/bimbingan_detail/' . encode_id($id));
        }

        $this->load->helper(array('google_drive', 'pkm'));
        require_google_drive_or_redirect();
        $this->load->library('Upload_to_drive');
        $share_emails = array();
        $email = $this->upload_to_drive->get_drive_email_by_role_ref('dosen', (int) $bimbingan->id_pembimbing);
        if ($email) {
            $share_emails[] = $email;
        }
        $upload = $this->upload_to_drive->handle_upload('file_laporan', $this->upload_to_drive->default_options(
            array('SIAP', 'PKM', 'bimbingan'),
            $share_emails
        ));
        if (empty($upload['ok'])) {
            $this->session->set_flashdata('error', isset($upload['message']) ? $upload['message'] : 'Gagal upload revisi.');
            redirect('Bimbingan_pkm_mahasiswa/bimbingan_detail/' . encode_id($id));
        }

        $this->Pkm_bimbingan_log_model->create(array(
            'id_bimbingan' => $id,
            'id_pengguna' => $user_id,
            'role_pengguna' => 'mahasiswa',
            'pesan' => trim($this->input->post('pesan')),
            'file_path' => pkm_encrypt_link($upload['webViewLink']),
            'file_name' => !empty($upload['file_name']) ? $upload['file_name'] : ('Revisi - ' . $bimbingan->judul_topik),
            'urutan' => $this->Pkm_bimbingan_log_model->get_next_urutan($id),
        ));
        $this->Pkm_bimbingan_model->update($id, array('last_activity' => date('Y-m-d H:i:s')));
        $this->session->set_flashdata('success', 'Revisi bimbingan terkirim.');
        redirect('Bimbingan_pkm_mahasiswa/bimbingan_detail/' . encode_id($id));
    }

    public function kirim_proposal_final() {
        if ($this->input->method() !== 'post') {
            show_404();
        }
        $id_mahasiswa = (int) $this->session->userdata('ref_id');
        $pengajuan = $this->_get_pengajuan_bimbingan($id_mahasiswa);
        if (!$pengajuan || $pengajuan->peran !== 'ketua') {
            $this->session->set_flashdata('error', 'Hanya ketua tim yang dapat mengirim proposal final.');
            redirect('Bimbingan_pkm_mahasiswa/bimbingan');
        }
        if (!$pengajuan || !pkm_proposal_final_aktif($pengajuan)) {
            $this->session->set_flashdata('error', 'Pengiriman proposal final belum aktif. Menunggu finalisasi dari dosen pembimbing.');
            redirect('Bimbingan_pkm_mahasiswa/bimbingan');
        }
        if (!pkm_proposal_final_submit_allowed($pengajuan)) {
            $this->session->set_flashdata('error', 'Batas waktu submit Proposal Final PKM telah berakhir.');
            redirect('Bimbingan_pkm_mahasiswa/bimbingan');
        }
        if ($pengajuan->status_acc_proposal === 'acc') {
            $this->session->set_flashdata('error', 'Proposal final sudah disetujui sekprodi.');
            redirect('Bimbingan_pkm_mahasiswa/bimbingan');
        }

        $this->load->helper(array('google_drive', 'pkm'));
        require_google_drive_or_redirect();
        $this->load->library('Upload_to_drive');
        $share_emails = array();
        if (!empty($pengajuan->id_pembimbing_final)) {
            $email = $this->upload_to_drive->get_drive_email_by_role_ref('dosen', (int) $pengajuan->id_pembimbing_final);
            if ($email) {
                $share_emails[] = $email;
            }
        }
        $upload = $this->upload_to_drive->handle_upload('file_proposal_final', $this->upload_to_drive->default_options(
            array('SIAP', 'PKM', 'proposal_final'),
            $share_emails
        ));
        if (empty($upload['ok'])) {
            $this->session->set_flashdata('error', isset($upload['message']) ? $upload['message'] : 'Gagal upload proposal final.');
            redirect('Bimbingan_pkm_mahasiswa/bimbingan');
        }

        $this->Pkm_pengajuan_model->update($pengajuan->id, array(
            'link_proposal_final' => pkm_encrypt_link($upload['webViewLink']),
            'status_acc_proposal' => 'pending',
            'catatan_verifikasi_proposal_final' => null,
        ));
        $this->load->helper(array('notifikasi', 'sekprodi_badge'));
        notify_sekprodi(
            'Proposal Final PKM Baru',
            'Ada proposal final PKM baru yang menunggu verifikasi.',
            site_url('Pkm_verifikasi_proposal'),
            'pkm_proposal_final'
        );
        $this->session->set_flashdata('success', 'Proposal final PKM dikirim ke sekprodi untuk verifikasi.');
        redirect('Bimbingan_pkm_mahasiswa/bimbingan');
    }

    private function _get_pengajuan_bimbingan($id_mahasiswa) {
        $pengajuan = $this->Pkm_pengajuan_model->get_by_mahasiswa_in_active_periode($id_mahasiswa);
        if (!$pengajuan || !pkm_bimbingan_aktif($pengajuan)) {
            return null;
        }
        $pengajuan->peran = ((int) $pengajuan->id_ketua === (int) $id_mahasiswa) ? 'ketua' : 'anggota';
        return $pengajuan;
    }

    private function enrich_bimbingan_workflow_status($bimbingan_list) {
        if (empty($bimbingan_list) || !is_array($bimbingan_list)) {
            return $bimbingan_list;
        }
        foreach ($bimbingan_list as $bimbingan) {
            $latest_log = $this->Pkm_bimbingan_log_model->get_latest_by_bimbingan($bimbingan->id);
            if (($bimbingan->status ?? '') === 'closed') {
                $workflow = array('label' => 'Bimbingan Ditutup', 'class' => 'secondary');
            } elseif (!$latest_log) {
                $workflow = array('label' => 'Menunggu Review Dosen', 'class' => 'info');
            } elseif (($latest_log->role_pengguna ?? '') === 'dosen' && ($latest_log->status_revisi ?? '') === 'revisi') {
                $workflow = array('label' => 'Perlu Revisi', 'class' => 'warning');
            } elseif (($latest_log->role_pengguna ?? '') === 'dosen' && ($latest_log->status_revisi ?? '') === 'acc') {
                $workflow = array('label' => 'ACC Pembimbing', 'class' => 'success');
            } else {
                $workflow = array('label' => 'Menunggu Review Dosen', 'class' => 'info');
            }
            $bimbingan->workflow_status_label = $workflow['label'];
            $bimbingan->workflow_status_class = $workflow['class'];
        }
        return $bimbingan_list;
    }
}
