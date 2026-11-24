<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Bimbingan_proposal Controller (Dosen pembimbing)
 * Review topik bimbingan Proposal TA: ACC / revisi, tutup dan buka topik.
 * Nilai bimbingan dan ACC layak sidang ada di Proposal_mahasiswa_dosen.
 */
class Bimbingan_proposal extends MY_Controller {

    public function __construct() {
        parent::__construct();
        $this->check_role('dosen');
        $this->load->model('Proposal_bimbingan_model');
        $this->load->model('Proposal_bimbingan_log_model');
        $this->load->model('Proposal_model');
        $this->load->model('Proposal_nilai_bimbingan_model');
        $this->load->model('Tahun_akademik_model');
        $this->load->library('form_validation');
        $this->load->helper(array('proposal', 'google_drive', 'notifikasi'));
    }

    public function index() {
        $id_dosen = $this->session->userdata('ref_id');
        $tahun_aktif = $this->Tahun_akademik_model->get_aktif();
        $status = $this->input->get('status');
        if (!in_array($status, array('open', 'closed'), true)) {
            $status = null;
        }

        $list = $this->Proposal_bimbingan_model->get_by_pembimbing($id_dosen, $status, $tahun_aktif ? $tahun_aktif->id : null);
        foreach ($list as $b) {
            $w = proposal_bimbingan_workflow($b, $this->Proposal_bimbingan_log_model->get_latest_by_bimbingan($b->id));
            $b->workflow_status_key = $w['key'];
            $b->workflow_status_label = $w['label'];
            $b->workflow_status_class = $w['class'];
        }

        $data = array(
            'title' => 'Bimbingan Proposal TA',
            'page_title' => 'Bimbingan Proposal TA',
            'breadcrumb' => array(
                array('label' => 'Proposal TA', 'url' => '#'),
                array('label' => 'Bimbingan Proposal', 'active' => true)
            ),
            'sidebar_menu' => generate_sidebar_menu('dosen'),
            'bimbingan_list' => $list,
            'status_filter' => $status
        );
        $data['content'] = $this->load->view('dosen/proposal/bimbingan_index', $data, true);
        $this->load->view('layouts/main', $data);
    }

    public function detail($id) {
        $bimbingan = $this->ambil_bimbingan_milik_dosen($id);
        $logs = $this->Proposal_bimbingan_log_model->get_by_bimbingan($bimbingan->id);
        $latest = !empty($logs) ? end($logs) : null;

        $tahun_aktif = $this->Tahun_akademik_model->get_aktif();
        $proposal = $tahun_aktif ? $this->Proposal_model->get_by_mahasiswa($bimbingan->id_mahasiswa, $tahun_aktif->id) : null;

        $data = array(
            'title' => 'Detail Bimbingan Proposal TA',
            'page_title' => 'Detail Bimbingan Proposal TA',
            'breadcrumb' => array(
                array('label' => 'Bimbingan Proposal', 'url' => site_url('Bimbingan_proposal')),
                array('label' => 'Detail', 'active' => true)
            ),
            'sidebar_menu' => generate_sidebar_menu('dosen'),
            'bimbingan' => $bimbingan,
            'logs' => $logs,
            'workflow' => proposal_bimbingan_workflow($bimbingan, $latest),
            'proposal' => $proposal,
            'nilai_bimbingan' => $proposal ? $this->Proposal_nilai_bimbingan_model->get_row_by_proposal($proposal->id) : null
        );
        $data['content'] = $this->load->view('dosen/proposal/bimbingan_detail', $data, true);
        $this->load->view('layouts/main', $data);
    }

    /**
     * Review topik: ACC atau revisi (keterangan wajib bila revisi).
     */
    public function kirim_pesan($id) {
        $bimbingan = $this->ambil_bimbingan_milik_dosen($id);
        $url_detail = 'Bimbingan_proposal/detail/' . $bimbingan->id;
        if ($this->input->method() !== 'post') {
            redirect($url_detail);
        }
        if ($bimbingan->status !== 'open') {
            $this->session->set_flashdata('error', 'Topik sudah ditutup. Buka kembali topik untuk memberi review.');
            redirect($url_detail);
        }

        $status_revisi = $this->input->post('status_revisi');
        $this->form_validation->set_rules('status_revisi', 'Status', 'required|in_list[acc,revisi]');
        $this->form_validation->set_rules('pesan', 'Pesan', 'trim');
        if ($status_revisi === 'revisi') {
            $this->form_validation->set_rules('keterangan_revisi', 'Keterangan Revisi', 'required|trim|min_length[5]');
        } else {
            $this->form_validation->set_rules('keterangan_revisi', 'Keterangan', 'trim');
        }
        if ($this->form_validation->run() === FALSE) {
            $this->session->set_flashdata('error', validation_errors());
            redirect($url_detail);
        }

        // Harus ada kiriman mahasiswa yang direview (log terakhir bukan dari dosen)
        $latest = $this->Proposal_bimbingan_log_model->get_latest_by_bimbingan($bimbingan->id);
        if (!$latest || $latest->role_pengguna !== 'mahasiswa') {
            $this->session->set_flashdata('error', 'Belum ada kiriman baru dari mahasiswa untuk direview.');
            redirect($url_detail);
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
            $upload = $this->upload_to_drive->handle_upload('file_google_drive', $this->upload_to_drive->default_options(array('SIAP', 'Proposal', 'bimbingan'), $share_emails));
            if (empty($upload['ok'])) {
                $this->session->set_flashdata('error', isset($upload['message']) ? $upload['message'] : 'Gagal upload berkas.');
                redirect($url_detail);
            }
            $file_path = $upload['webViewLink'];
            $file_name = !empty($upload['file_name']) ? $upload['file_name'] : 'File Bimbingan Dosen';
        }
        $this->Proposal_bimbingan_log_model->create(array(
            'id_bimbingan' => $bimbingan->id,
            'id_pengguna' => $this->session->userdata('user_id'),
            'role_pengguna' => 'dosen',
            'pesan' => trim((string) $this->input->post('pesan')),
            'status_revisi' => $status_revisi,
            'keterangan_revisi' => $status_revisi === 'revisi' ? $this->input->post('keterangan_revisi') : null,
            'file_path' => $file_path,
            'file_name' => $file_name,
            'urutan' => $this->Proposal_bimbingan_log_model->get_next_urutan($bimbingan->id)
        ));
        $this->Proposal_bimbingan_model->update($bimbingan->id, array('last_activity' => date('Y-m-d H:i:s')));

        proposal_notify('mahasiswa', $bimbingan->id_mahasiswa,
            $status_revisi === 'acc' ? 'Bimbingan Proposal TA Di-ACC' : 'Revisi Bimbingan Proposal TA',
            $status_revisi === 'acc' ? 'Pembimbing meng-ACC topik: ' . $bimbingan->judul_topik : 'Pembimbing meminta revisi pada topik: ' . $bimbingan->judul_topik,
            site_url('Bimbingan_proposal_mahasiswa/bimbingan_detail/' . encode_id($bimbingan->id)));

        $this->session->set_flashdata('success', 'Review berhasil dikirim.');
        redirect($url_detail);
    }

    public function tutup($id) {
        $bimbingan = $this->ambil_bimbingan_milik_dosen($id);
        if ($this->input->method() === 'post') {
            $this->Proposal_bimbingan_model->update($bimbingan->id, array('status' => 'closed', 'last_activity' => date('Y-m-d H:i:s')));
            proposal_notify('mahasiswa', $bimbingan->id_mahasiswa, 'Topik Bimbingan Proposal TA Ditutup',
                'Pembimbing menutup topik: ' . $bimbingan->judul_topik, site_url('Bimbingan_proposal_mahasiswa/bimbingan'));
            $this->session->set_flashdata('success', 'Bimbingan telah ditutup.');
        }
        redirect('Bimbingan_proposal/detail/' . $bimbingan->id);
    }

    public function buka($id) {
        $bimbingan = $this->ambil_bimbingan_milik_dosen($id);
        if ($this->input->method() === 'post') {
            $this->Proposal_bimbingan_model->update($bimbingan->id, array('status' => 'open', 'last_activity' => date('Y-m-d H:i:s')));
            proposal_notify('mahasiswa', $bimbingan->id_mahasiswa, 'Topik Bimbingan Proposal TA Dibuka Kembali',
                'Pembimbing membuka kembali topik: ' . $bimbingan->judul_topik,
                site_url('Bimbingan_proposal_mahasiswa/bimbingan_detail/' . encode_id($bimbingan->id)));
            $this->session->set_flashdata('success', 'Bimbingan dibuka kembali.');
        }
        redirect('Bimbingan_proposal/detail/' . $bimbingan->id);
    }

    public function valid_drive_link_opsional($link) {
        if ($link === '' || $link === null) {
            return true;
        }
        if (!is_valid_google_drive_link($link)) {
            $this->form_validation->set_message('valid_drive_link_opsional', 'Link Google Drive tidak valid.');
            return false;
        }
        return true;
    }

    private function ambil_bimbingan_milik_dosen($id) {
        $id_dosen = $this->session->userdata('ref_id');
        $bimbingan = $this->Proposal_bimbingan_model->get_by_id($id);
        if (!$id_dosen || !$bimbingan || (int) $bimbingan->id_pembimbing !== (int) $id_dosen) {
            show_404();
        }
        return $bimbingan;
    }
}
