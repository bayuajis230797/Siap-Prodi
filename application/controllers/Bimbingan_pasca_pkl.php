<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Bimbingan_pasca_pkl extends MY_Controller {

    public function __construct() {
        parent::__construct();
        $this->check_role('dosen');
        $this->load->library('Bimbingan_pasca_service');
        $this->load->model('Pkl_bimbingan_pasca_model');
        $this->load->model('Pkl_bimbingan_pasca_log_model');
        $this->load->model('Jadwal_pkl_model');
        $this->load->model('Tahun_akademik_model');
        $this->load->helper(array('menu', 'notifikasi', 'id_encode', 'google_drive'));
        $this->load->library('form_validation');
    }

    public function index() {
        $id_dosen = (int) $this->session->userdata('ref_id');
        $tahun = $this->Tahun_akademik_model->get_aktif();
        $jadwal_list = $this->Jadwal_pkl_model->get_by_penguji($id_dosen, $tahun ? $tahun->id : null);
        foreach ($jadwal_list as $j) {
            $this->bimbingan_pasca_service->ensure_pkl_by_jadwal($j->id);
        }
        $list = $this->Pkl_bimbingan_pasca_model->get_by_penguji($id_dosen, $tahun ? $tahun->id : null);
        $data = array(
            'title' => 'Bimbingan Pasca Sidang PKL',
            'page_title' => 'Bimbingan Pasca Sidang PKL',
            'breadcrumb' => array(
                array('label' => 'PKL', 'url' => '#'),
                array('label' => 'Bimbingan Pasca Sidang', 'active' => true),
            ),
            'sidebar_menu' => generate_sidebar_menu('dosen'),
            'list' => $list,
        );
        $data['content'] = $this->load->view('dosen/bimbingan_pasca_pkl/index', $data, true);
        $this->load->view('layouts/main', $data);
    }

    public function detail($id) {
        $id_dosen = (int) $this->session->userdata('ref_id');
        $bimbingan = $this->Pkl_bimbingan_pasca_model->get_by_id($id);
        if (!$bimbingan || (int) $bimbingan->id_penguji !== $id_dosen) {
            $this->session->set_flashdata('error', 'Data tidak ditemukan atau Anda bukan penguji.');
            redirect('Bimbingan_pasca_pkl');
        }
        $logs = $this->Pkl_bimbingan_pasca_log_model->get_by_bimbingan($id);
        $data = array(
            'title' => 'Detail Bimbingan Pasca Sidang PKL',
            'page_title' => 'Detail Bimbingan Pasca Sidang PKL',
            'breadcrumb' => array(
                array('label' => 'Bimbingan Pasca Sidang', 'url' => site_url('Bimbingan_pasca_pkl')),
                array('label' => 'Detail', 'active' => true),
            ),
            'sidebar_menu' => generate_sidebar_menu('dosen'),
            'bimbingan' => $bimbingan,
            'logs' => $logs,
        );
        $data['content'] = $this->load->view('dosen/bimbingan_pasca_pkl/detail', $data, true);
        $this->load->view('layouts/main', $data);
    }

    public function kirim_pesan($id) {
        if ($this->input->method() !== 'post') {
            show_404();
        }
        $id_dosen = (int) $this->session->userdata('ref_id');
        $user_id = (int) $this->session->userdata('user_id');
        $bimbingan = $this->Pkl_bimbingan_pasca_model->get_by_id($id);
        if (!$bimbingan || (int) $bimbingan->id_penguji !== $id_dosen || $bimbingan->status !== 'open') {
            $this->session->set_flashdata('error', 'Tidak dapat mengirim pesan.');
            redirect('Bimbingan_pasca_pkl');
        }

        $this->form_validation->set_rules('status_revisi', 'Status', 'required|in_list[acc,revisi]');
        $status_revisi = $this->input->post('status_revisi');
        if ($status_revisi === 'revisi') {
            $this->form_validation->set_rules('keterangan_revisi', 'Keterangan Revisi', 'required|trim|min_length[5]');
        }
        if ($this->form_validation->run() === FALSE) {
            $this->session->set_flashdata('error', validation_errors());
            redirect('Bimbingan_pasca_pkl/detail/' . $id);
        }

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
                array('SIAP', 'PKL', 'pasca_sidang', 'dosen'),
                $share_emails
            ));
            if (empty($upload['ok'])) {
                $this->session->set_flashdata('error', isset($upload['message']) ? $upload['message'] : 'Gagal upload berkas.');
                redirect('Bimbingan_pasca_pkl/detail/' . $id);
            }
            $file_path = $upload['webViewLink'];
            $file_name = !empty($upload['file_name']) ? $upload['file_name'] : 'File Penguji';
        }

        $this->Pkl_bimbingan_pasca_log_model->create(array(
            'id_bimbingan_pasca' => (int) $id,
            'urutan' => $this->Pkl_bimbingan_pasca_log_model->get_next_urutan($id),
            'id_pengguna' => $user_id,
            'role_pengguna' => 'dosen',
            'pesan' => trim((string) $this->input->post('pesan')),
            'file_path' => $file_path,
            'file_name' => $file_name,
            'status_revisi' => $status_revisi,
            'keterangan_revisi' => $this->input->post('keterangan_revisi'),
        ));
        $this->Pkl_bimbingan_pasca_model->update($id, array('last_activity' => date('Y-m-d H:i:s')));

        send_in_app_notification_by_ref(
            'mahasiswa',
            (int) $bimbingan->id_mahasiswa,
            $status_revisi === 'acc' ? 'Pasca Sidang PKL Di-ACC' : 'Revisi Pasca Sidang PKL',
            $status_revisi === 'acc'
                ? 'Penguji memberikan ACC pada revisi pasca sidang PKL Anda.'
                : 'Penguji meminta revisi pada bimbingan pasca sidang PKL.',
            site_url('Bimbingan_pasca_pkl_mahasiswa/detail/' . encode_id($id))
        );

        $this->session->set_flashdata('success', 'Pesan berhasil dikirim.');
        redirect('Bimbingan_pasca_pkl/detail/' . $id);
    }

    public function layak_klaim($id) {
        if ($this->input->method() !== 'post') {
            show_404();
        }
        $id_dosen = (int) $this->session->userdata('ref_id');
        $bimbingan = $this->Pkl_bimbingan_pasca_model->get_by_id($id);
        if (!$bimbingan || (int) $bimbingan->id_penguji !== $id_dosen) {
            $this->session->set_flashdata('error', 'Akses ditolak.');
            redirect('Bimbingan_pasca_pkl');
        }
        if (!empty($bimbingan->layak_klaim)) {
            $this->session->set_flashdata('success', 'Sudah dinyatakan layak klaim nilai.');
            redirect('Bimbingan_pasca_pkl/detail/' . $id);
        }

        $this->Pkl_bimbingan_pasca_model->set_layak_klaim($id);
        send_in_app_notification_by_ref(
            'mahasiswa',
            (int) $bimbingan->id_mahasiswa,
            'Penguji Menyatakan Layak Klaim PKL',
            'Penguji telah menyatakan layak klaim nilai. Anda dapat mengajukan klaim nilai PKL.',
            site_url('Pkl_klaim_nilai')
        );
        $this->session->set_flashdata('success', 'Mahasiswa sekarang dapat klaim nilai.');
        redirect('Bimbingan_pasca_pkl/detail/' . $id);
    }
}
