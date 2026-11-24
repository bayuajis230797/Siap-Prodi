<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Bimbingan_pasca_skripsi_mahasiswa extends MY_Controller {

    public function __construct() {
        parent::__construct();
        $this->check_role('mahasiswa');
        $this->load->library('Bimbingan_pasca_service');
        $this->load->model('Skripsi_bimbingan_pasca_model');
        $this->load->model('Skripsi_bimbingan_pasca_penguji_model');
        $this->load->model('Skripsi_bimbingan_pasca_log_model');
        $this->load->model('Skripsi_model');
        $this->load->model('Jadwal_skripsi_model');
        $this->load->model('Tahun_akademik_model');
        $this->load->helper(array('menu', 'notifikasi', 'id_encode', 'google_drive'));
        $this->load->library('form_validation');
    }

    public function index() {
        $id_mahasiswa = (int) $this->session->userdata('ref_id');
        $tahun = $this->Tahun_akademik_model->get_aktif();
        $skripsi = $tahun ? $this->Skripsi_model->get_by_mahasiswa($id_mahasiswa, $tahun->id) : null;
        if ($skripsi) {
            $jadwal_list = $this->Jadwal_skripsi_model->get_by_mahasiswa($skripsi->id);
            foreach ($jadwal_list as $j) {
                $this->bimbingan_pasca_service->ensure_skripsi_by_jadwal($j->id);
            }
        }
        $list = $this->Skripsi_bimbingan_pasca_model->get_by_mahasiswa($id_mahasiswa);
        $data = array(
            'title' => 'Bimbingan Pasca Sidang Skripsi',
            'page_title' => 'Bimbingan Pasca Sidang Skripsi',
            'breadcrumb' => array(
                array('label' => 'Skripsi', 'url' => '#'),
                array('label' => 'Bimbingan Pasca Sidang', 'active' => true),
            ),
            'sidebar_menu' => generate_sidebar_menu('mahasiswa'),
            'list' => $list,
        );
        $data['content'] = $this->load->view('mahasiswa/bimbingan_pasca_skripsi/index', $data, true);
        $this->load->view('layouts/main', $data);
    }

    public function detail($encoded_id) {
        $id = (int) decode_id($encoded_id);
        $id_mahasiswa = (int) $this->session->userdata('ref_id');
        $bimbingan = $this->Skripsi_bimbingan_pasca_model->get_by_id($id);
        if (!$bimbingan || (int) $bimbingan->id_mahasiswa !== $id_mahasiswa) {
            show_404();
        }
        $penguji_list = $this->Skripsi_bimbingan_pasca_penguji_model->get_by_bimbingan($id);
        $logs = $this->Skripsi_bimbingan_pasca_log_model->get_by_bimbingan($id);
        $latest = $this->Skripsi_bimbingan_pasca_log_model->get_latest($id);
        $can_reply = $bimbingan->status === 'open' && (
            !$latest || $latest->role_pengguna === 'dosen' || empty($logs)
        );

        $data = array(
            'title' => 'Detail Bimbingan Pasca Sidang',
            'page_title' => 'Detail Bimbingan Pasca Sidang Skripsi',
            'breadcrumb' => array(
                array('label' => 'Bimbingan Pasca Sidang', 'url' => site_url('Bimbingan_pasca_skripsi_mahasiswa')),
                array('label' => 'Detail', 'active' => true),
            ),
            'sidebar_menu' => generate_sidebar_menu('mahasiswa'),
            'bimbingan' => $bimbingan,
            'penguji_list' => $penguji_list,
            'logs' => $logs,
            'can_reply' => $can_reply,
            'encoded_id' => $encoded_id,
            'all_layak' => $this->Skripsi_bimbingan_pasca_penguji_model->is_all_layak($id),
        );
        $data['content'] = $this->load->view('mahasiswa/bimbingan_pasca_skripsi/detail', $data, true);
        $this->load->view('layouts/main', $data);
    }

    public function kirim($encoded_id) {
        if ($this->input->method() !== 'post') {
            show_404();
        }
        $id = (int) decode_id($encoded_id);
        $id_mahasiswa = (int) $this->session->userdata('ref_id');
        $user_id = (int) $this->session->userdata('user_id');
        $bimbingan = $this->Skripsi_bimbingan_pasca_model->get_by_id($id);
        if (!$bimbingan || (int) $bimbingan->id_mahasiswa !== $id_mahasiswa || $bimbingan->status !== 'open') {
            show_404();
        }

        $pesan = trim((string) $this->input->post('pesan'));
        if ($pesan === '' && empty($_FILES['file_google_drive']['name'])) {
            $this->session->set_flashdata('error', 'Isi pesan atau unggah berkas.');
            redirect('Bimbingan_pasca_skripsi_mahasiswa/detail/' . $encoded_id);
        }

        $file_path = null;
        $file_name = null;
        if (!empty($_FILES['file_google_drive']['name'])) {
            $this->load->helper('google_drive');
            require_google_drive_or_redirect();
            $this->load->library('Upload_to_drive');
            $share_emails = array();
            foreach ($this->Skripsi_bimbingan_pasca_penguji_model->get_by_bimbingan($id) as $p) {
                $email = $this->upload_to_drive->get_drive_email_by_role_ref('dosen', (int) $p->id_dosen);
                if ($email && !in_array($email, $share_emails, true)) {
                    $share_emails[] = $email;
                }
            }
            $upload = $this->upload_to_drive->handle_upload('file_google_drive', $this->upload_to_drive->default_options(array('SIAP', 'Skripsi', 'pasca_sidang'), $share_emails));
            if (empty($upload['ok'])) {
                $this->session->set_flashdata('error', isset($upload['message']) ? $upload['message'] : 'Gagal upload berkas.');
                redirect('Bimbingan_pasca_skripsi_mahasiswa/detail/' . $encoded_id);
            }
            $file_path = $upload['webViewLink'];
            $file_name = !empty($upload['file_name']) ? $upload['file_name'] : 'File Revisi Mahasiswa';
        }

        $this->Skripsi_bimbingan_pasca_log_model->create(array(
            'id_bimbingan_pasca' => $id,
            'urutan' => $this->Skripsi_bimbingan_pasca_log_model->get_next_urutan($id),
            'id_pengguna' => $user_id,
            'role_pengguna' => 'mahasiswa',
            'id_dosen' => null,
            'pesan' => $pesan,
            'file_path' => $file_path,
            'file_name' => $file_name,
            'status_revisi' => null,
            'keterangan_revisi' => null,
        ));
        $this->Skripsi_bimbingan_pasca_model->update($id, array('last_activity' => date('Y-m-d H:i:s')));

        $penguji_list = $this->Skripsi_bimbingan_pasca_penguji_model->get_by_bimbingan($id);
        foreach ($penguji_list as $p) {
            send_in_app_notification_by_ref(
                'dosen',
                (int) $p->id_dosen,
                'Pesan Pasca Sidang Skripsi',
                'Mahasiswa mengirim pesan/revisi pada bimbingan pasca sidang.',
                site_url('Bimbingan_pasca_skripsi/detail/' . $id)
            );
        }

        $this->session->set_flashdata('success', 'Pesan berhasil dikirim.');
        redirect('Bimbingan_pasca_skripsi_mahasiswa/detail/' . $encoded_id);
    }
}
