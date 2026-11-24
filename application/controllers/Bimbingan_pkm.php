<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Bimbingan_pkm extends MY_Controller {

    public function __construct() {
        parent::__construct();
        $this->check_role('dosen');
        $this->load->model('Pkm_bimbingan_model');
        $this->load->model('Pkm_bimbingan_log_model');
        $this->load->model('Pkm_pengajuan_model');
        $this->load->helper(array('menu', 'pkm', 'google_drive'));
        $this->load->library('form_validation');
    }

    public function index() {
        $id_dosen = (int) $this->session->userdata('ref_id');
        $status = $this->input->get('status');
        $list = $this->Pkm_bimbingan_model->get_by_pembimbing($id_dosen, $status ?: null);
        $list = $this->enrich_bimbingan_workflow_status($list);

        $data = array(
            'title' => 'Bimbingan PKM',
            'page_title' => 'Bimbingan PKM',
            'breadcrumb' => array(array('label' => 'Bimbingan PKM', 'active' => true)),
            'sidebar_menu' => generate_sidebar_menu('dosen'),
            'bimbingan_list' => $list,
            'status_filter' => $status,
        );
        $data['content'] = $this->load->view('dosen/pkm/bimbingan_index', $data, true);
        $this->load->view('layouts/main', $data);
    }

    public function detail($id) {
        $id_dosen = (int) $this->session->userdata('ref_id');
        $bimbingan = $this->Pkm_bimbingan_model->get_by_id($id);
        if (!$bimbingan || (int) $bimbingan->id_pembimbing !== $id_dosen) {
            show_404();
        }

        $pengajuan = $this->Pkm_pengajuan_model->get_by_id($bimbingan->id_pkm_pengajuan);
        $logs = $this->Pkm_bimbingan_log_model->get_by_bimbingan($id);
        $latest_log = $this->Pkm_bimbingan_log_model->get_latest_by_bimbingan($id);
        $workflow_status = $this->resolve_workflow_status($bimbingan, $latest_log);

        $data = array(
            'title' => 'Detail Bimbingan PKM',
            'page_title' => 'Detail Bimbingan PKM',
            'breadcrumb' => array(
                array('label' => 'Bimbingan PKM', 'url' => site_url('Bimbingan_pkm')),
                array('label' => 'Detail', 'active' => true),
            ),
            'sidebar_menu' => generate_sidebar_menu('dosen'),
            'bimbingan' => $bimbingan,
            'pengajuan' => $pengajuan,
            'logs' => $logs,
            'workflow_status' => $workflow_status,
        );
        $data['content'] = $this->load->view('dosen/pkm/bimbingan_detail', $data, true);
        $this->load->view('layouts/main', $data);
    }

    public function kirim_pesan($id) {
        if ($this->input->method() !== 'post') {
            show_404();
        }
        $id_dosen = (int) $this->session->userdata('ref_id');
        $user_id = (int) $this->session->userdata('user_id');
        $bimbingan = $this->Pkm_bimbingan_model->get_by_id($id);
        if (!$bimbingan || (int) $bimbingan->id_pembimbing !== $id_dosen || $bimbingan->status !== 'open') {
            show_404();
        }

        $status_revisi = $this->input->post('status_revisi');
        $keterangan = trim($this->input->post('keterangan_revisi'));
        $pesan = trim($this->input->post('pesan'));

        if (!in_array($status_revisi, array('acc', 'revisi'), true)) {
            $this->session->set_flashdata('error', 'Status revisi tidak valid.');
            redirect('Bimbingan_pkm/detail/' . $id);
        }
        if ($status_revisi === 'revisi' && $keterangan === '') {
            $this->session->set_flashdata('error', 'Keterangan revisi wajib diisi.');
            redirect('Bimbingan_pkm/detail/' . $id);
        }

        $this->load->helper(array('google_drive', 'pkm'));
        $file_path = null;
        $file_name = null;
        if (!empty($_FILES['file_berkas']['name'])) {
            require_google_drive_or_redirect();
            $this->load->library('Upload_to_drive');
            $share_emails = array();
            $email = $this->upload_to_drive->get_drive_email_by_role_ref('mahasiswa', (int) $bimbingan->id_mahasiswa);
            if ($email) {
                $share_emails[] = $email;
            }
            $upload = $this->upload_to_drive->handle_upload('file_berkas', $this->upload_to_drive->default_options(
                array('SIAP', 'PKM', 'bimbingan', 'dosen'),
                $share_emails
            ));
            if (empty($upload['ok'])) {
                $this->session->set_flashdata('error', isset($upload['message']) ? $upload['message'] : 'Gagal upload berkas.');
                redirect('Bimbingan_pkm/detail/' . $id);
            }
            $file_path = pkm_encrypt_link($upload['webViewLink']);
            $file_name = !empty($upload['file_name']) ? $upload['file_name'] : 'File Pembimbing';
        }

        $this->Pkm_bimbingan_log_model->create(array(
            'id_bimbingan' => $id,
            'id_pengguna' => $user_id,
            'role_pengguna' => 'dosen',
            'pesan' => $pesan,
            'status_revisi' => $status_revisi,
            'keterangan_revisi' => $keterangan ?: null,
            'file_path' => $file_path,
            'file_name' => $file_name,
            'urutan' => $this->Pkm_bimbingan_log_model->get_next_urutan($id),
        ));
        $this->Pkm_bimbingan_model->update($id, array('last_activity' => date('Y-m-d H:i:s')));
        $this->session->set_flashdata('success', 'Review bimbingan terkirim.');
        redirect('Bimbingan_pkm/detail/' . $id);
    }

    public function finalisasi_proposal($id_pengajuan) {
        if ($this->input->method() !== 'post') {
            show_404();
        }
        $id_dosen = (int) $this->session->userdata('ref_id');
        $pengajuan = $this->Pkm_pengajuan_model->get_by_id($id_pengajuan);
        if (!$pengajuan || (int) $pengajuan->id_pembimbing_final !== $id_dosen) {
            show_404();
        }
        if (!empty($pengajuan->proposal_final_aktif)) {
            $this->session->set_flashdata('error', 'Proposal final sudah difinalisasi sebelumnya.');
            redirect('Bimbingan_pkm/detail/' . (int) $this->input->post('id_bimbingan'));
        }

        $this->Pkm_pengajuan_model->update($id_pengajuan, array(
            'proposal_final_aktif' => 1,
            'proposal_final_aktif_at' => date('Y-m-d H:i:s'),
            'proposal_final_aktif_by' => $id_dosen,
        ));
        $this->session->set_flashdata('success', 'Proposal PKM difinalisasi. Mahasiswa dapat mengirim proposal final ke sekprodi.');
        redirect('Bimbingan_pkm/detail/' . (int) $this->input->post('id_bimbingan'));
    }

    public function tutup($id) {
        $id_dosen = (int) $this->session->userdata('ref_id');
        $bimbingan = $this->Pkm_bimbingan_model->get_by_id($id);
        if (!$bimbingan || (int) $bimbingan->id_pembimbing !== $id_dosen) {
            show_404();
        }
        $this->Pkm_bimbingan_model->update($id, array(
            'status' => 'closed',
            'last_activity' => date('Y-m-d H:i:s'),
        ));
        $this->session->set_flashdata('success', 'Topik bimbingan ditutup.');
        redirect('Bimbingan_pkm/detail/' . $id);
    }

    public function buka($id) {
        $id_dosen = (int) $this->session->userdata('ref_id');
        $bimbingan = $this->Pkm_bimbingan_model->get_by_id($id);
        if (!$bimbingan || (int) $bimbingan->id_pembimbing !== $id_dosen) {
            show_404();
        }
        $this->Pkm_bimbingan_model->update($id, array(
            'status' => 'open',
            'last_activity' => date('Y-m-d H:i:s'),
        ));
        $this->session->set_flashdata('success', 'Topik bimbingan dibuka kembali.');
        redirect('Bimbingan_pkm/detail/' . $id);
    }

    private function enrich_bimbingan_workflow_status($bimbingan_list) {
        if (empty($bimbingan_list) || !is_array($bimbingan_list)) {
            return $bimbingan_list;
        }
        foreach ($bimbingan_list as $bimbingan) {
            $latest_log = $this->Pkm_bimbingan_log_model->get_latest_by_bimbingan($bimbingan->id);
            $workflow = $this->resolve_workflow_status($bimbingan, $latest_log);
            $bimbingan->workflow_status_key = $workflow['key'];
            $bimbingan->workflow_status_label = $workflow['label'];
            $bimbingan->workflow_status_class = $workflow['class'];
        }
        return $bimbingan_list;
    }

    private function resolve_workflow_status($bimbingan, $latest_log) {
        if (($bimbingan->status ?? '') === 'closed') {
            return array('key' => 'closed', 'label' => 'Bimbingan Ditutup', 'class' => 'secondary');
        }
        if (!$latest_log) {
            return array('key' => 'waiting_review', 'label' => 'Menunggu Review Dosen', 'class' => 'info');
        }
        if (($latest_log->role_pengguna ?? '') === 'dosen' && ($latest_log->status_revisi ?? '') === 'revisi') {
            return array('key' => 'need_revision', 'label' => 'Perlu Revisi Mahasiswa', 'class' => 'warning');
        }
        if (($latest_log->role_pengguna ?? '') === 'dosen' && ($latest_log->status_revisi ?? '') === 'acc') {
            return array('key' => 'approved', 'label' => 'ACC Pembimbing', 'class' => 'success');
        }
        return array('key' => 'waiting_review', 'label' => 'Menunggu Review Dosen', 'class' => 'info');
    }
}
