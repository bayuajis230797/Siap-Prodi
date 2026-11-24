<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Pkm_perubahan_judul extends MY_Controller {

    public function __construct() {
        parent::__construct();
        $this->check_role('mahasiswa');
        $this->load->model('Pkm_pengajuan_model');
        $this->load->model('Pkm_perubahan_judul_model');
        $this->load->helper(array('menu', 'pkm', 'google_drive', 'id_encode'));
        $this->load->library('form_validation');
    }

    public function ajukan($id_pengajuan) {
        $id_pengajuan = pkm_resolve_id($id_pengajuan);
        if ($id_pengajuan <= 0) {
            redirect('Pkm_mahasiswa');
        }
        $seg = $this->uri->segment(3);
        if ($seg !== null && $seg !== '' && ctype_digit((string) $seg)) {
            redirect('Pkm_perubahan_judul/ajukan/' . pkm_encoded_id($id_pengajuan));
        }
        $id_mahasiswa = (int) $this->session->userdata('ref_id');
        $pengajuan = $this->Pkm_pengajuan_model->get_by_id($id_pengajuan);
        if (!$pengajuan || (int) $pengajuan->id_ketua !== $id_mahasiswa || $pengajuan->status_acc_judul !== 'revisi') {
            $this->session->set_flashdata('error', 'Pengajuan perubahan judul tidak dapat dilakukan.');
            redirect('Pkm_mahasiswa');
        }
        if ($this->Pkm_perubahan_judul_model->has_pending($id_pengajuan)) {
            $this->session->set_flashdata('error', 'Masih ada pengajuan perubahan judul yang pending.');
            redirect('Pkm_mahasiswa/detail/' . pkm_encoded_id($id_pengajuan));
        }

        if ($this->input->method() === 'post') {
            $judul_baru = trim($this->input->post('judul_baru'));
            $alasan = trim($this->input->post('alasan'));
            if ($judul_baru === '') {
                $this->session->set_flashdata('error', 'Judul baru wajib diisi.');
                redirect('Pkm_perubahan_judul/ajukan/' . pkm_encoded_id($id_pengajuan));
            }
            $link_stored = null;
            if (!empty($_FILES['file_bukti']['name'])) {
                require_google_drive_or_redirect();
                $this->load->library('Upload_to_drive');
                $share_emails = array();
                if (!empty($pengajuan->id_pembimbing_final)) {
                    $email = $this->upload_to_drive->get_drive_email_by_role_ref('dosen', (int) $pengajuan->id_pembimbing_final);
                } elseif (!empty($pengajuan->id_pembimbing_request)) {
                    $email = $this->upload_to_drive->get_drive_email_by_role_ref('dosen', (int) $pengajuan->id_pembimbing_request);
                } else {
                    $email = null;
                }
                if ($email) {
                    $share_emails[] = $email;
                }
                $upload = $this->upload_to_drive->handle_optional_upload('file_bukti', $this->upload_to_drive->default_options(
                    array('SIAP', 'PKM', 'perubahan_judul'),
                    $share_emails
                ));
                if (empty($upload['ok'])) {
                    $this->session->set_flashdata('error', isset($upload['message']) ? $upload['message'] : 'Gagal upload bukti.');
                    redirect('Pkm_perubahan_judul/ajukan/' . pkm_encoded_id($id_pengajuan));
                }
                if (empty($upload['skipped'])) {
                    $link_stored = pkm_encrypt_link($upload['webViewLink']);
                }
            }
            $this->Pkm_perubahan_judul_model->create(array(
                'id_pkm_pengajuan' => (int) $id_pengajuan,
                'judul_baru' => $judul_baru,
                'alasan' => $alasan ?: null,
                'link_bukti' => $link_stored,
            ));
            $this->session->set_flashdata('success', 'Pengajuan perubahan judul dikirim ke dosen pembimbing.');
            redirect('Pkm_mahasiswa/detail/' . pkm_encoded_id($id_pengajuan));
        }

        $data = array(
            'title' => 'Perubahan Judul PKM',
            'page_title' => 'Ajukan Perubahan Judul',
            'breadcrumb' => array(
                array('label' => 'PKM', 'url' => site_url('Pkm_mahasiswa')),
                array('label' => 'Perubahan Judul', 'active' => true),
            ),
            'sidebar_menu' => generate_sidebar_menu('mahasiswa'),
            'pengajuan' => $pengajuan,
        );
        $data['content'] = $this->load->view('mahasiswa/pkm/perubahan_judul', $data, true);
        $this->load->view('layouts/main', $data);
    }
}
