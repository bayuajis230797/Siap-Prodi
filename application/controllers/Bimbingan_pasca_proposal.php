<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Bimbingan_pasca_proposal Controller (Dosen penguji)
 * Revisi pasca sidang Proposal TA: thread bersama kedua penguji.
 * Penguji menyatakan "layak klaim" setelah menilai dan revisi mahasiswa memadai.
 */
class Bimbingan_pasca_proposal extends MY_Controller {

    public function __construct() {
        parent::__construct();
        $this->check_role('dosen');
        $this->load->model('Proposal_bimbingan_pasca_model');
        $this->load->model('Proposal_bimbingan_pasca_penguji_model');
        $this->load->model('Proposal_bimbingan_pasca_log_model');
        $this->load->model('Proposal_nilai_sidang_model');
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
            'sidebar_menu' => generate_sidebar_menu('dosen'),
            'list' => $this->Proposal_bimbingan_pasca_model->get_by_penguji((int) $this->session->userdata('ref_id'))
        );
        $data['content'] = $this->load->view('dosen/proposal/pasca_index', $data, true);
        $this->load->view('layouts/main', $data);
    }

    public function detail($id) {
        list($bimbingan, $penguji_me) = $this->ambil($id);

        $data = array(
            'title' => 'Detail Bimbingan Pasca Sidang',
            'page_title' => 'Detail Bimbingan Pasca Sidang Proposal TA',
            'breadcrumb' => array(
                array('label' => 'Bimbingan Pasca Sidang', 'url' => site_url('Bimbingan_pasca_proposal')),
                array('label' => 'Detail', 'active' => true)
            ),
            'sidebar_menu' => generate_sidebar_menu('dosen'),
            'bimbingan' => $bimbingan,
            'penguji_me' => $penguji_me,
            'penguji_list' => $this->Proposal_bimbingan_pasca_penguji_model->get_by_bimbingan($bimbingan->id),
            'logs' => $this->Proposal_bimbingan_pasca_log_model->get_by_bimbingan($bimbingan->id),
            'all_layak' => $this->Proposal_bimbingan_pasca_penguji_model->is_all_layak($bimbingan->id),
            'sudah_menilai' => (bool) $this->Proposal_nilai_sidang_model->get_by_jadwal_penguji($bimbingan->id_jadwal_proposal, $penguji_me->peran)
        );
        $data['content'] = $this->load->view('dosen/proposal/pasca_detail', $data, true);
        $this->load->view('layouts/main', $data);
    }

    public function kirim_pesan($id) {
        list($bimbingan, $penguji_me) = $this->ambil($id);
        $url = 'Bimbingan_pasca_proposal/detail/' . $bimbingan->id;
        if ($this->input->method() !== 'post') {
            redirect($url);
        }
        if ($bimbingan->status !== 'open') {
            $this->session->set_flashdata('error', 'Bimbingan pasca sidang sudah ditutup.');
            redirect($url);
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
            redirect($url);
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
            $upload = $this->upload_to_drive->handle_upload('file_google_drive', $this->upload_to_drive->default_options(array('SIAP', 'Proposal', 'pasca_sidang'), $share_emails));
            if (empty($upload['ok'])) {
                $this->session->set_flashdata('error', isset($upload['message']) ? $upload['message'] : 'Gagal upload berkas.');
                redirect($url);
            }
            $file_path = $upload['webViewLink'];
            $file_name = !empty($upload['file_name']) ? $upload['file_name'] : 'File Penguji';
        }
        $this->Proposal_bimbingan_pasca_log_model->create(array(
            'id_bimbingan_pasca' => $bimbingan->id,
            'urutan' => $this->Proposal_bimbingan_pasca_log_model->get_next_urutan($bimbingan->id),
            'id_pengguna' => (int) $this->session->userdata('user_id'),
            'role_pengguna' => 'dosen',
            'id_dosen' => (int) $this->session->userdata('ref_id'),
            'pesan' => trim((string) $this->input->post('pesan')),
            'file_path' => $file_path,
            'file_name' => $file_name,
            'status_revisi' => $status_revisi,
            'keterangan_revisi' => $status_revisi === 'revisi' ? $this->input->post('keterangan_revisi') : null
        ));
        $this->Proposal_bimbingan_pasca_model->update($bimbingan->id, array('last_activity' => date('Y-m-d H:i:s')));

        proposal_notify('mahasiswa', $bimbingan->id_mahasiswa,
            $status_revisi === 'acc' ? 'Pasca Sidang Proposal TA Di-ACC' : 'Revisi Pasca Sidang Proposal TA',
            $status_revisi === 'acc' ? 'Penguji meng-ACC revisi pasca sidang Anda.' : 'Penguji meminta revisi pada bimbingan pasca sidang.',
            site_url('Bimbingan_pasca_proposal_mahasiswa/detail/' . encode_id($bimbingan->id)));

        $this->session->set_flashdata('success', 'Pesan berhasil dikirim.');
        redirect($url);
    }

    /**
     * Penguji menyatakan layak klaim nilai (sekali; hanya setelah penguji menilai sidang).
     */
    public function layak_klaim($id) {
        list($bimbingan, $penguji_me) = $this->ambil($id);
        $url = 'Bimbingan_pasca_proposal/detail/' . $bimbingan->id;
        if ($this->input->method() !== 'post') {
            redirect($url);
        }
        if ($bimbingan->status !== 'open') {
            $this->session->set_flashdata('error', 'Bimbingan pasca sidang sudah ditutup.');
            redirect($url);
        }
        if (!empty($penguji_me->layak_klaim)) {
            $this->session->set_flashdata('success', 'Anda sudah menyatakan layak klaim nilai.');
            redirect($url);
        }
        if (!$this->Proposal_nilai_sidang_model->get_by_jadwal_penguji($bimbingan->id_jadwal_proposal, $penguji_me->peran)) {
            $this->session->set_flashdata('error', 'Isi penilaian sidang Anda terlebih dahulu sebelum menyatakan layak klaim.');
            redirect($url);
        }

        $this->Proposal_bimbingan_pasca_penguji_model->set_layak_klaim($bimbingan->id, (int) $this->session->userdata('ref_id'));
        $semua = $this->Proposal_bimbingan_pasca_penguji_model->is_all_layak($bimbingan->id);

        proposal_notify('mahasiswa', $bimbingan->id_mahasiswa, 'Penguji Menyatakan Layak Klaim',
            $semua ? 'Kedua penguji telah menyatakan layak klaim nilai. Anda dapat mengajukan klaim nilai.' : 'Satu penguji menyatakan layak klaim nilai. Menunggu penguji lainnya.',
            site_url('Proposal_klaim_nilai'));

        $this->session->set_flashdata('success', $semua ? 'Layak klaim disimpan. Kedua penguji sudah menyatakan layak.' : 'Layak klaim disimpan. Menunggu penguji lain.');
        redirect($url);
    }

    public function valid_drive_opsional($link) {
        if ($link === '' || $link === null) {
            return true;
        }
        if (!is_valid_google_drive_link($link)) {
            $this->form_validation->set_message('valid_drive_opsional', 'Link Google Drive tidak valid.');
            return false;
        }
        return true;
    }

    /** @return array [bimbingan, penguji_me]; redirect bila bukan penguji thread ini */
    private function ambil($id) {
        $id_dosen = (int) $this->session->userdata('ref_id');
        $bimbingan = $this->Proposal_bimbingan_pasca_model->get_by_id($id);
        $penguji_me = $bimbingan ? $this->Proposal_bimbingan_pasca_penguji_model->get_by_bimbingan_dosen($bimbingan->id, $id_dosen) : null;
        if (!$bimbingan || !$penguji_me) {
            $this->session->set_flashdata('error', 'Data tidak ditemukan atau Anda bukan penguji.');
            redirect('Bimbingan_pasca_proposal');
        }
        return array($bimbingan, $penguji_me);
    }
}
