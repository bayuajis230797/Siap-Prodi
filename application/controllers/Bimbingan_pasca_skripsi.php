<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Bimbingan Pasca Sidang Skripsi — role dosen penguji (thread bersama).
 */
class Bimbingan_pasca_skripsi extends MY_Controller {

    public function __construct() {
        parent::__construct();
        $this->check_role('dosen');
        $this->load->library('Bimbingan_pasca_service');
        $this->load->model('Skripsi_bimbingan_pasca_model');
        $this->load->model('Skripsi_bimbingan_pasca_penguji_model');
        $this->load->model('Skripsi_bimbingan_pasca_log_model');
        $this->load->model('Jadwal_skripsi_model');
        $this->load->helper(array('menu', 'notifikasi', 'id_encode', 'google_drive'));
        $this->load->library('form_validation');
    }

    public function index() {
        $id_dosen = (int) $this->session->userdata('ref_id');
        // Pastikan thread untuk semua jadwal di mana dosen jadi penguji
        $this->db->from('jadwal_skripsi');
        $this->db->group_start();
        $this->db->where('id_ketua_penguji', $id_dosen);
        $this->db->or_where('id_anggota_penguji_1', $id_dosen);
        $this->db->or_where('id_anggota_penguji_2', $id_dosen);
        $this->db->group_end();
        $jadwal_list = $this->db->get()->result();
        foreach ($jadwal_list as $j) {
            $this->bimbingan_pasca_service->ensure_skripsi_by_jadwal($j->id);
        }

        $list = $this->Skripsi_bimbingan_pasca_model->get_by_penguji($id_dosen);
        $data = array(
            'title' => 'Bimbingan Pasca Sidang Skripsi',
            'page_title' => 'Bimbingan Pasca Sidang Skripsi',
            'breadcrumb' => array(
                array('label' => 'Skripsi', 'url' => '#'),
                array('label' => 'Bimbingan Pasca Sidang', 'active' => true),
            ),
            'sidebar_menu' => generate_sidebar_menu('dosen'),
            'list' => $list,
        );
        $data['content'] = $this->load->view('dosen/bimbingan_pasca_skripsi/index', $data, true);
        $this->load->view('layouts/main', $data);
    }

    public function detail($id) {
        $id_dosen = (int) $this->session->userdata('ref_id');
        $bimbingan = $this->Skripsi_bimbingan_pasca_model->get_by_id($id);
        $penguji_me = $bimbingan
            ? $this->Skripsi_bimbingan_pasca_penguji_model->get_by_bimbingan_dosen($id, $id_dosen)
            : null;
        if (!$bimbingan || !$penguji_me) {
            $this->session->set_flashdata('error', 'Data tidak ditemukan atau Anda bukan penguji.');
            redirect('Bimbingan_pasca_skripsi');
        }

        $penguji_list = $this->Skripsi_bimbingan_pasca_penguji_model->get_by_bimbingan($id);
        $logs = $this->Skripsi_bimbingan_pasca_log_model->get_by_bimbingan($id);
        $all_layak = $this->Skripsi_bimbingan_pasca_penguji_model->is_all_layak($id);

        $data = array(
            'title' => 'Detail Bimbingan Pasca Sidang',
            'page_title' => 'Detail Bimbingan Pasca Sidang Skripsi',
            'breadcrumb' => array(
                array('label' => 'Bimbingan Pasca Sidang', 'url' => site_url('Bimbingan_pasca_skripsi')),
                array('label' => 'Detail', 'active' => true),
            ),
            'sidebar_menu' => generate_sidebar_menu('dosen'),
            'bimbingan' => $bimbingan,
            'penguji_me' => $penguji_me,
            'penguji_list' => $penguji_list,
            'logs' => $logs,
            'all_layak' => $all_layak,
        );
        $data['content'] = $this->load->view('dosen/bimbingan_pasca_skripsi/detail', $data, true);
        $this->load->view('layouts/main', $data);
    }

    public function kirim_pesan($id) {
        if ($this->input->method() !== 'post') {
            show_404();
        }
        $id_dosen = (int) $this->session->userdata('ref_id');
        $user_id = (int) $this->session->userdata('user_id');
        $bimbingan = $this->Skripsi_bimbingan_pasca_model->get_by_id($id);
        $penguji_me = $bimbingan
            ? $this->Skripsi_bimbingan_pasca_penguji_model->get_by_bimbingan_dosen($id, $id_dosen)
            : null;
        if (!$bimbingan || !$penguji_me || $bimbingan->status !== 'open') {
            $this->session->set_flashdata('error', 'Tidak dapat mengirim pesan.');
            redirect('Bimbingan_pasca_skripsi');
        }

        $this->form_validation->set_rules('status_revisi', 'Status', 'required|in_list[acc,revisi]');
        $status_revisi = $this->input->post('status_revisi');
        if ($status_revisi === 'revisi') {
            $this->form_validation->set_rules('keterangan_revisi', 'Keterangan Revisi', 'required|trim|min_length[5]');
        }
        if ($this->form_validation->run() === FALSE) {
            $this->session->set_flashdata('error', validation_errors());
            redirect('Bimbingan_pasca_skripsi/detail/' . $id);
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
            $upload = $this->upload_to_drive->handle_upload('file_google_drive', $this->upload_to_drive->default_options(array('SIAP', 'Skripsi', 'pasca_sidang'), $share_emails));
            if (empty($upload['ok'])) {
                $this->session->set_flashdata('error', isset($upload['message']) ? $upload['message'] : 'Gagal upload berkas.');
                redirect('Bimbingan_pasca_skripsi/detail/' . $id);
            }
            $file_path = $upload['webViewLink'];
            $file_name = !empty($upload['file_name']) ? $upload['file_name'] : 'File Penguji';
        }

        $this->Skripsi_bimbingan_pasca_log_model->create(array(
            'id_bimbingan_pasca' => (int) $id,
            'urutan' => $this->Skripsi_bimbingan_pasca_log_model->get_next_urutan($id),
            'id_pengguna' => $user_id,
            'role_pengguna' => 'dosen',
            'id_dosen' => $id_dosen,
            'pesan' => trim((string) $this->input->post('pesan')),
            'file_path' => $file_path,
            'file_name' => $file_name,
            'status_revisi' => $status_revisi,
            'keterangan_revisi' => $this->input->post('keterangan_revisi'),
        ));
        $this->Skripsi_bimbingan_pasca_model->update($id, array('last_activity' => date('Y-m-d H:i:s')));

        send_in_app_notification_by_ref(
            'mahasiswa',
            (int) $bimbingan->id_mahasiswa,
            $status_revisi === 'acc' ? 'Pasca Sidang Di-ACC' : 'Revisi Pasca Sidang Skripsi',
            $status_revisi === 'acc'
                ? 'Penguji memberikan ACC pada revisi pasca sidang skripsi Anda.'
                : 'Penguji meminta revisi pada bimbingan pasca sidang skripsi.',
            site_url('Bimbingan_pasca_skripsi_mahasiswa/detail/' . encode_id($id))
        );

        $this->session->set_flashdata('success', 'Pesan berhasil dikirim.');
        redirect('Bimbingan_pasca_skripsi/detail/' . $id);
    }

    public function layak_klaim($id) {
        if ($this->input->method() !== 'post') {
            show_404();
        }
        $id_dosen = (int) $this->session->userdata('ref_id');
        $bimbingan = $this->Skripsi_bimbingan_pasca_model->get_by_id($id);
        $penguji_me = $bimbingan
            ? $this->Skripsi_bimbingan_pasca_penguji_model->get_by_bimbingan_dosen($id, $id_dosen)
            : null;
        if (!$bimbingan || !$penguji_me) {
            $this->session->set_flashdata('error', 'Akses ditolak.');
            redirect('Bimbingan_pasca_skripsi');
        }
        if (!empty($penguji_me->layak_klaim)) {
            $this->session->set_flashdata('success', 'Anda sudah menyatakan layak klaim nilai.');
            redirect('Bimbingan_pasca_skripsi/detail/' . $id);
        }

        $this->Skripsi_bimbingan_pasca_penguji_model->set_layak_klaim($id, $id_dosen);
        $all = $this->Skripsi_bimbingan_pasca_penguji_model->is_all_layak($id);

        send_in_app_notification_by_ref(
            'mahasiswa',
            (int) $bimbingan->id_mahasiswa,
            'Penguji Menyatakan Layak Klaim',
            $all
                ? 'Semua penguji telah menyatakan layak klaim nilai. Anda dapat mengajukan klaim nilai skripsi.'
                : 'Salah satu penguji menyatakan layak klaim nilai. Menunggu penguji lainnya.',
            site_url('Skripsi_klaim_nilai')
        );

        $this->session->set_flashdata('success', $all
            ? 'Layak klaim disimpan. Semua penguji sudah menyatakan layak.'
            : 'Layak klaim disimpan. Menunggu penguji lain.');
        redirect('Bimbingan_pasca_skripsi/detail/' . $id);
    }
}
