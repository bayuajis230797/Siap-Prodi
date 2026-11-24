<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Bimbingan_pasca_proposal_mahasiswa Controller (Mahasiswa)
 * Diskusi dan pengiriman revisi pasca sidang kepada kedua penguji.
 */
class Bimbingan_pasca_proposal_mahasiswa extends MY_Controller {

    public function __construct() {
        parent::__construct();
        $this->check_role('mahasiswa');
        $this->load->model('Proposal_bimbingan_pasca_model');
        $this->load->model('Proposal_bimbingan_pasca_penguji_model');
        $this->load->model('Proposal_bimbingan_pasca_log_model');
        $this->load->library('form_validation');
        $this->load->helper(array('proposal', 'google_drive', 'notifikasi', 'id_encode'));
    }

    public function index() {
        $data = array(
            'title' => 'Bimbingan Pasca Sidang Proposal TA',
            'page_title' => 'Bimbingan Pasca Sidang Proposal TA',
            'breadcrumb' => array(
                array('label' => 'Proposal TA', 'url' => '#'),
                array('label' => 'Bimbingan Pasca Sidang', 'active' => true)
            ),
            'sidebar_menu' => generate_sidebar_menu('mahasiswa'),
            'list' => $this->Proposal_bimbingan_pasca_model->get_by_mahasiswa((int) $this->session->userdata('ref_id'))
        );
        $data['content'] = $this->load->view('mahasiswa/proposal/pasca_index', $data, true);
        $this->load->view('layouts/main', $data);
    }

    public function detail($encoded_id) {
        $bimbingan = $this->ambil_milik_saya($encoded_id);
        $data = array(
            'title' => 'Detail Bimbingan Pasca Sidang',
            'page_title' => 'Detail Bimbingan Pasca Sidang Proposal TA',
            'breadcrumb' => array(
                array('label' => 'Bimbingan Pasca Sidang', 'url' => site_url('Bimbingan_pasca_proposal_mahasiswa')),
                array('label' => 'Detail', 'active' => true)
            ),
            'sidebar_menu' => generate_sidebar_menu('mahasiswa'),
            'bimbingan' => $bimbingan,
            'penguji_list' => $this->Proposal_bimbingan_pasca_penguji_model->get_by_bimbingan($bimbingan->id),
            'logs' => $this->Proposal_bimbingan_pasca_log_model->get_by_bimbingan($bimbingan->id),
            'all_layak' => $this->Proposal_bimbingan_pasca_penguji_model->is_all_layak($bimbingan->id),
            'encoded_id' => $encoded_id
        );
        $data['content'] = $this->load->view('mahasiswa/proposal/pasca_detail', $data, true);
        $this->load->view('layouts/main', $data);
    }

    public function kirim($encoded_id) {
        $bimbingan = $this->ambil_milik_saya($encoded_id);
        $url = 'Bimbingan_pasca_proposal_mahasiswa/detail/' . $encoded_id;
        if ($this->input->method() !== 'post') {
            redirect($url);
        }
        if ($bimbingan->status !== 'open') {
            $this->session->set_flashdata('error', 'Bimbingan pasca sidang sudah ditutup.');
            redirect($url);
        }

        $pesan = trim((string) $this->input->post('pesan'));
        if ($pesan === '' && empty($_FILES['file_google_drive']['name'])) {
            $this->session->set_flashdata('error', 'Isi pesan atau unggah berkas.');
            redirect($url);
        }

        $file_path = null;
        $file_name = null;
        if (!empty($_FILES['file_google_drive']['name'])) {
            require_google_drive_or_redirect();
            $this->load->library('Upload_to_drive');
            $share_emails = array();
            foreach ($this->Proposal_bimbingan_pasca_penguji_model->get_by_bimbingan($bimbingan->id) as $p) {
                $email = $this->upload_to_drive->get_drive_email_by_role_ref('dosen', (int) $p->id_dosen);
                if ($email && !in_array($email, $share_emails, true)) {
                    $share_emails[] = $email;
                }
            }
            $upload = $this->upload_to_drive->handle_upload('file_google_drive', $this->upload_to_drive->default_options(array('SIAP', 'Proposal', 'pasca_sidang'), $share_emails));
            if (empty($upload['ok'])) {
                $this->session->set_flashdata('error', isset($upload['message']) ? $upload['message'] : 'Gagal upload berkas.');
                redirect($url);
            }
            $file_path = $upload['webViewLink'];
            $file_name = !empty($upload['file_name']) ? $upload['file_name'] : 'File Revisi Mahasiswa';
        }

        $this->Proposal_bimbingan_pasca_log_model->create(array(
            'id_bimbingan_pasca' => $bimbingan->id,
            'urutan' => $this->Proposal_bimbingan_pasca_log_model->get_next_urutan($bimbingan->id),
            'id_pengguna' => (int) $this->session->userdata('user_id'),
            'role_pengguna' => 'mahasiswa',
            'id_dosen' => null,
            'pesan' => $pesan,
            'file_path' => $file_path,
            'file_name' => $file_name,
            'status_revisi' => null,
            'keterangan_revisi' => null
        ));
        $this->Proposal_bimbingan_pasca_model->update($bimbingan->id, array('last_activity' => date('Y-m-d H:i:s')));

        foreach ($this->Proposal_bimbingan_pasca_penguji_model->get_by_bimbingan($bimbingan->id) as $p) {
            proposal_notify('dosen', $p->id_dosen, 'Pesan Pasca Sidang Proposal TA',
                'Mahasiswa mengirim pesan/revisi pada bimbingan pasca sidang.', site_url('Bimbingan_pasca_proposal/detail/' . $bimbingan->id));
        }

        $this->session->set_flashdata('success', 'Pesan berhasil dikirim.');
        redirect($url);
    }

    private function ambil_milik_saya($encoded_id) {
        $id = decode_id($encoded_id);
        if ($id === false) {
            show_404();
        }
        $b = $this->Proposal_bimbingan_pasca_model->get_by_id((int) $id);
        if (!$b || (int) $b->id_mahasiswa !== (int) $this->session->userdata('ref_id')) {
            show_404();
        }
        return $b;
    }
}
