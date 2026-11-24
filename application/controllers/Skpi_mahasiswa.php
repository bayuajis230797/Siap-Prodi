<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Skpi_mahasiswa extends MY_Controller {

    public function __construct() {
        parent::__construct();
        $this->load->model('Skpi_periode_model');
        $this->load->model('Skpi_peserta_model');
        $this->load->model('Skpi_jenis_berkas_model');
        $this->load->model('Skpi_upload_model');
        $this->load->helper(array('menu', 'google_drive'));
        $this->load->library('form_validation');
    }

    public function index() {
        $this->check_role('mahasiswa');
        $id_mahasiswa = (int) $this->session->userdata('ref_id');
        $periode_list = $this->Skpi_periode_model->get_periode_for_mahasiswa($id_mahasiswa, null);
        foreach ($periode_list as $p) {
            $p->upload_allowed = $this->Skpi_periode_model->is_upload_allowed($p);
            $p->deadline_lewat = $this->Skpi_periode_model->is_deadline_lewat($p);
        }
        $data = array(
            'title' => 'SKPI',
            'page_title' => 'Pengajuan SKPI',
            'breadcrumb' => array(array('label' => 'SKPI', 'active' => true)),
            'sidebar_menu' => generate_sidebar_menu('mahasiswa'),
            'periode_list' => $periode_list,
        );
        $data['content'] = $this->load->view('mahasiswa/skpi/index', $data, true);
        $this->load->view('layouts/main', $data);
    }

    public function upload($id_periode) {
        $this->check_role('mahasiswa');
        $id_mahasiswa = (int) $this->session->userdata('ref_id');
        $periode = $this->_get_periode_akses($id_periode, $id_mahasiswa);
        if (!$periode) {
            return;
        }
        $jenis_list = $this->Skpi_jenis_berkas_model->get_by_periode($id_periode, true);
        $uploads = $this->Skpi_upload_model->get_by_mahasiswa_periode($id_periode, $id_mahasiswa);
        $uploads_by_jenis = array();
        foreach ($uploads as $u) {
            if (!isset($uploads_by_jenis[$u->id_skpi_jenis_berkas])) {
                $uploads_by_jenis[$u->id_skpi_jenis_berkas] = array();
            }
            $uploads_by_jenis[$u->id_skpi_jenis_berkas][] = $u;
        }
        $data = array(
            'title' => 'Upload SKPI',
            'page_title' => $periode->nama_periode,
            'breadcrumb' => array(
                array('label' => 'SKPI', 'url' => site_url('Skpi_mahasiswa')),
                array('label' => 'Upload', 'active' => true),
            ),
            'sidebar_menu' => generate_sidebar_menu('mahasiswa'),
            'periode' => $periode,
            'jenis_list' => $jenis_list,
            'uploads_by_jenis' => $uploads_by_jenis,
            'upload_allowed' => $this->Skpi_periode_model->is_upload_allowed($periode),
            'deadline_lewat' => $this->Skpi_periode_model->is_deadline_lewat($periode),
        );
        $data['content'] = $this->load->view('mahasiswa/skpi/upload', $data, true);
        $this->load->view('layouts/main', $data);
    }

    public function simpan_entri($id_periode) {
        $this->check_role('mahasiswa');
        $id_mahasiswa = (int) $this->session->userdata('ref_id');
        $periode = $this->_get_periode_akses($id_periode, $id_mahasiswa, true);
        if (!$periode) {
            return;
        }
        if (!$this->Skpi_periode_model->is_upload_allowed($periode)) {
            $this->session->set_flashdata('error', 'Batas pengumpulan telah berakhir. Pengajuan baru tidak dapat dilakukan.');
            redirect('Skpi_mahasiswa/upload/' . $id_periode);
        }
        $this->form_validation->set_rules('id_skpi_jenis_berkas', 'Jenis Berkas', 'required|integer');
        $this->form_validation->set_rules('nama_sertifikat', 'Nama Sertifikat', 'required|trim|max_length[255]');
        $this->form_validation->set_rules('deskripsi_berkas', 'Deskripsi Berkas', 'required|trim');
        if ($this->form_validation->run() === FALSE) {
            $this->session->set_flashdata('error', validation_errors());
            redirect('Skpi_mahasiswa/upload/' . $id_periode);
        }
        $id_jenis = (int) $this->input->post('id_skpi_jenis_berkas');
        $jenis = $this->Skpi_jenis_berkas_model->get_by_id($id_jenis);
        if (!$jenis || (int) $jenis->id_skpi_periode !== (int) $id_periode) {
            $this->session->set_flashdata('error', 'Jenis berkas tidak valid.');
            redirect('Skpi_mahasiswa/upload/' . $id_periode);
        }
        $this->load->helper('google_drive');
        require_google_drive_or_redirect();
        $this->load->library('Upload_to_drive');
        $upload = $this->upload_to_drive->handle_upload('file_berkas', $this->upload_to_drive->default_options(
            array('SIAP', 'SKPI', 'berkas'),
            array()
        ));
        if (empty($upload['ok'])) {
            $this->session->set_flashdata('error', isset($upload['message']) ? $upload['message'] : 'Gagal upload berkas.');
            redirect('Skpi_mahasiswa/upload/' . $id_periode);
        }
        $this->Skpi_upload_model->create(array(
            'id_skpi_periode' => (int) $id_periode,
            'id_skpi_jenis_berkas' => $id_jenis,
            'id_mahasiswa' => $id_mahasiswa,
            'nama_sertifikat' => $this->input->post('nama_sertifikat'),
            'link_drive' => $upload['webViewLink'],
            'deskripsi_berkas' => $this->input->post('deskripsi_berkas'),
            'status' => 'pending',
        ));
        $this->load->helper(array('notifikasi', 'sekprodi_badge'));
        notify_sekprodi(
            'Ajuan SKPI Baru',
            'Ada berkas SKPI baru yang menunggu verifikasi.',
            site_url('Skpi_verifikasi'),
            'skpi_verifikasi'
        );
        $this->session->set_flashdata('success', 'Berkas berhasil diajukan. Menunggu verifikasi sekprodi.');
        redirect('Skpi_mahasiswa/upload/' . $id_periode);
    }

    public function update_entri($id_upload) {
        $this->check_role('mahasiswa');
        $id_mahasiswa = (int) $this->session->userdata('ref_id');
        $upload = $this->Skpi_upload_model->get_by_id($id_upload);
        if (!$upload || (int) $upload->id_mahasiswa !== $id_mahasiswa) {
            $this->session->set_flashdata('error', 'Data tidak ditemukan.');
            redirect('Skpi_mahasiswa');
        }
        $periode = $this->Skpi_periode_model->get_by_id($upload->id_skpi_periode);
        if (!$periode || !$this->Skpi_peserta_model->is_peserta($periode->id, $id_mahasiswa)) {
            $this->session->set_flashdata('error', 'Akses ditolak.');
            redirect('Skpi_mahasiswa');
        }
        if (!$this->Skpi_periode_model->is_upload_allowed($periode)) {
            $this->session->set_flashdata('error', 'Batas pengumpulan telah berakhir. Perbaikan tidak dapat dilakukan.');
            redirect('Skpi_mahasiswa/upload/' . $periode->id);
        }
        if ($upload->status !== 'ditolak') {
            $this->session->set_flashdata('error', 'Hanya berkas ditolak yang dapat diperbaiki.');
            redirect('Skpi_mahasiswa/upload/' . $periode->id);
        }
        $nama = trim($this->input->post('nama_sertifikat'));
        $deskripsi = trim($this->input->post('deskripsi_berkas'));
        if ($nama === '' || $deskripsi === '') {
            $this->session->set_flashdata('error', 'Nama sertifikat dan deskripsi wajib diisi.');
            redirect('Skpi_mahasiswa/upload/' . $periode->id);
        }
        $link = $upload->link_drive;
        if (!empty($_FILES['file_berkas']['name'])) {
            $this->load->helper('google_drive');
            require_google_drive_or_redirect();
            $this->load->library('Upload_to_drive');
            $up = $this->upload_to_drive->handle_upload('file_berkas', $this->upload_to_drive->default_options(
                array('SIAP', 'SKPI', 'berkas'),
                array()
            ));
            if (empty($up['ok'])) {
                $this->session->set_flashdata('error', isset($up['message']) ? $up['message'] : 'Gagal upload berkas.');
                redirect('Skpi_mahasiswa/upload/' . $periode->id);
            }
            $link = $up['webViewLink'];
        }
        $this->Skpi_upload_model->update($id_upload, array(
            'nama_sertifikat' => $nama,
            'link_drive' => $link,
            'deskripsi_berkas' => $deskripsi,
            'status' => 'pending',
            'catatan_sekprodi' => null,
            'reviewed_by' => null,
            'reviewed_at' => null,
        ));
        $this->session->set_flashdata('success', 'Berkas berhasil diperbaiki dan dikirim ulang.');
        redirect('Skpi_mahasiswa/upload/' . $periode->id);
    }

    public function hapus_entri($id_upload) {
        $this->check_role('mahasiswa');
        $id_mahasiswa = (int) $this->session->userdata('ref_id');
        $upload = $this->Skpi_upload_model->get_by_id($id_upload);
        if (!$upload || (int) $upload->id_mahasiswa !== $id_mahasiswa) {
            $this->session->set_flashdata('error', 'Data tidak ditemukan.');
            redirect('Skpi_mahasiswa');
        }
        $periode = $this->Skpi_periode_model->get_by_id($upload->id_skpi_periode);
        if (!$this->Skpi_periode_model->is_upload_allowed($periode)) {
            $this->session->set_flashdata('error', 'Batas pengumpulan telah berakhir.');
            redirect('Skpi_mahasiswa/upload/' . $upload->id_skpi_periode);
        }
        if (!in_array($upload->status, array('pending', 'ditolak'), true)) {
            $this->session->set_flashdata('error', 'Berkas ini tidak dapat dihapus.');
            redirect('Skpi_mahasiswa/upload/' . $upload->id_skpi_periode);
        }
        $this->Skpi_upload_model->delete($id_upload);
        $this->session->set_flashdata('success', 'Entri berkas dihapus.');
        redirect('Skpi_mahasiswa/upload/' . $upload->id_skpi_periode);
    }

    private function _get_periode_akses($id_periode, $id_mahasiswa, $require_upload_allowed = false) {
        $periode = $this->Skpi_periode_model->get_by_id($id_periode);
        if (!$periode || !$this->Skpi_peserta_model->is_peserta($id_periode, $id_mahasiswa)) {
            $this->session->set_flashdata('error', 'Anda tidak terdaftar pada periode SKPI ini.');
            redirect('Skpi_mahasiswa');
            return null;
        }
        if ($require_upload_allowed && !$this->Skpi_periode_model->is_upload_allowed($periode)) {
            return $periode;
        }
        return $periode;
    }
}
