<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Proposal_pengajuan Controller
 * Pengajuan judul Proposal TA oleh mahasiswa dan review oleh Sekprodi.
 * Satu pembimbing. Perbaikan judul setelah revisi pembimbing dilakukan lewat
 * menu Perubahan Judul (Proposal_perubahan_judul), bukan di halaman ini.
 */
class Proposal_pengajuan extends MY_Controller {

    public function __construct() {
        parent::__construct();
        $this->load->model('Proposal_pengajuan_model');
        $this->load->model('Proposal_model');
        $this->load->model('Proposal_pembimbing_model');
        $this->load->model('Proposal_bimbingan_model');
        $this->load->model('Proposal_perubahan_judul_model');
        $this->load->model('Tahun_akademik_model');
        $this->load->library('pagination');
        $this->load->library('form_validation');
        $this->load->helper('proposal');
    }

    /* ==================================================================
     * MAHASISWA
     * ================================================================== */

    /**
     * Halaman pengajuan judul: status pengajuan atau form pengajuan baru.
     */
    public function index() {
        $this->check_role('mahasiswa');

        $id_mahasiswa = $this->session->userdata('ref_id');
        $mahasiswa = $this->db->get_where('mahasiswa', array('id' => $id_mahasiswa))->row();
        if (!$mahasiswa) {
            $this->session->set_flashdata('error', 'Data mahasiswa tidak ditemukan.');
            redirect('Dashboard');
        }

        $tahun_aktif = $this->Tahun_akademik_model->get_aktif();
        if (!$tahun_aktif) {
            $this->session->set_flashdata('error', 'Tidak ada tahun akademik aktif.');
            redirect('Dashboard');
        }

        $proposal = $this->Proposal_model->get_by_mahasiswa($mahasiswa->id, $tahun_aktif->id);
        if (!$proposal) {
            $this->session->set_flashdata('error', 'Anda belum terdaftar untuk Proposal TA. Silakan hubungi sekprodi.');
            redirect('Dashboard');
        }

        $pengajuan = $this->Proposal_pengajuan_model->get_latest_by_mahasiswa($mahasiswa->id, $tahun_aktif->id);

        $judul_terbaru = $pengajuan ? get_judul_proposal_terbaru($mahasiswa->id, $tahun_aktif->id) : null;
        $perubahan_pending = $pengajuan
            ? $this->Proposal_perubahan_judul_model->get_latest_by_pengajuan_status($pengajuan->id, 'pending')
            : null;

        $data = array(
            'title' => 'Pengajuan Judul Proposal TA',
            'page_title' => 'Pengajuan Judul Proposal TA',
            'breadcrumb' => array(
                array('label' => 'Proposal TA', 'url' => '#'),
                array('label' => 'Pengajuan Judul', 'active' => true)
            ),
            'sidebar_menu' => generate_sidebar_menu('mahasiswa'),
            'pengajuan' => $pengajuan,
            'proposal' => $proposal,
            'tahun_aktif' => $tahun_aktif,
            'judul_terbaru' => $judul_terbaru,
            'perubahan_pending' => $perubahan_pending,
            'bimbingan_aktif' => ($pengajuan && ($pengajuan->status_acc_judul ?? 'pending') === 'acc')
        );

        $data['content'] = $this->load->view('proposal_pengajuan/index', $data, true);
        $this->load->view('layouts/main', $data);
    }

    /**
     * Kirim pengajuan judul baru (hanya bila belum ada pengajuan aktif).
     */
    public function submit() {
        $this->check_role('mahasiswa');
        if ($this->input->method() !== 'post') {
            redirect('Proposal_pengajuan');
        }

        $id_mahasiswa = $this->session->userdata('ref_id');
        $tahun_aktif = $this->Tahun_akademik_model->get_aktif();
        if (!$id_mahasiswa || !$tahun_aktif) {
            $this->session->set_flashdata('error', 'Data mahasiswa atau tahun akademik aktif tidak ditemukan.');
            redirect('Dashboard');
        }

        $proposal = $this->Proposal_model->get_by_mahasiswa($id_mahasiswa, $tahun_aktif->id);
        if (!$proposal) {
            $this->session->set_flashdata('error', 'Anda belum terdaftar untuk Proposal TA.');
            redirect('Dashboard');
        }

        // Tidak boleh mengajukan lagi bila pengajuan terakhir belum ditolak
        $terakhir = $this->Proposal_pengajuan_model->get_latest_by_mahasiswa($id_mahasiswa, $tahun_aktif->id);
        if ($terakhir && $terakhir->status !== 'ditolak') {
            $this->session->set_flashdata('error', 'Anda sudah memiliki pengajuan judul yang sedang diproses atau disetujui.');
            redirect('Proposal_pengajuan');
        }

        $this->form_validation->set_rules('judul_pengajuan', 'Judul Proposal', 'required|trim|min_length[10]|max_length[255]');
        $this->form_validation->set_rules('deskripsi', 'Deskripsi', 'trim');
        $this->form_validation->set_rules('alasan', 'Alasan', 'trim');

        if ($this->form_validation->run() == FALSE) {
            $this->session->set_flashdata('error', validation_errors());
            redirect('Proposal_pengajuan');
        }

        $file_proposal = null;
        if (!empty($_FILES['file_proposal']['name'])) {
            $this->load->helper('google_drive');
            require_google_drive_or_redirect();

            $this->load->library('Upload_to_drive');
            $share_emails = array();
            $sekprodi_owner = $this->upload_to_drive->find_connected_sekprodi_user_id();
            if ($sekprodi_owner) {
                $email = $this->upload_to_drive->get_drive_email($sekprodi_owner);
                if ($email) {
                    $share_emails[] = $email;
                }
            }

            $upload = $this->upload_to_drive->handle_upload('file_proposal', array(
                'owner_user_id' => (int) $this->session->userdata('user_id'),
                'folder_parts' => array('SIAP', 'Proposal', 'ajuan'),
                'share_emails' => $share_emails,
                'share_anyone' => true,
                'allowed_types' => 'pdf|doc|docx|zip|rar',
                'max_size_kb' => 15360
            ));

            if (empty($upload['ok'])) {
                $this->session->set_flashdata('error', isset($upload['message']) ? $upload['message'] : 'Gagal upload berkas.');
                redirect('Proposal_pengajuan');
            }
            $file_proposal = $upload['webViewLink'];
        }

        $id = $this->Proposal_pengajuan_model->create(array(
            'id_mahasiswa' => $id_mahasiswa,
            'id_tahun_akademik' => $tahun_aktif->id,
            'judul_pengajuan' => $this->input->post('judul_pengajuan'),
            'deskripsi' => $this->input->post('deskripsi'),
            'alasan' => $this->input->post('alasan'),
            'file_proposal' => $file_proposal,
            'status' => 'pending'
        ));

        if ($id) {
            $this->load->helper(array('notifikasi', 'sekprodi_badge'));
            notify_sekprodi(
                'Ajuan Judul Proposal TA',
                'Ada pengajuan judul Proposal TA baru yang menunggu review.',
                site_url('Proposal_pengajuan/review'),
                'proposal_judul'
            );
            $this->session->set_flashdata('success', 'Pengajuan judul Proposal TA berhasil dikirim. Menunggu review sekprodi.');
        } else {
            $this->session->set_flashdata('error', 'Gagal mengirim pengajuan judul.');
        }
        redirect('Proposal_pengajuan');
    }

    /**
     * Callback validasi link Google Drive (kosong dianggap valid).
     */
    public function validate_google_drive_link($link) {
        if (empty($link)) {
            return true;
        }
        $this->load->helper('google_drive');
        if (!is_valid_google_drive_link($link)) {
            $this->form_validation->set_message('validate_google_drive_link', 'Link Google Drive tidak valid. Pastikan link adalah link Google Drive yang valid.');
            return false;
        }
        return true;
    }

    /* ==================================================================
     * SEKPRODI
     * ================================================================== */

    /**
     * Daftar pengajuan untuk direview.
     */
    public function review() {
        $this->check_role('sekprodi');

        $tahun_aktif = $this->Tahun_akademik_model->get_aktif();
        $status_filter = $this->input->get('status');
        $kelas_filter = $this->input->get('kelas');
        $per_page = (int) $this->input->get('per_page');
        $per_page = in_array($per_page, array(10, 25, 50, 100), true) ? $per_page : 10;

        $filters = array();
        if ($tahun_aktif) {
            $filters['id_tahun_akademik'] = $tahun_aktif->id;
        }
        if ($status_filter) {
            $filters['status'] = $status_filter;
        }
        if ($kelas_filter) {
            $filters['kelas'] = $kelas_filter;
        }

        $total_rows = $this->Proposal_pengajuan_model->count_all($filters);
        $this->pagination->initialize(proposal_pagination_config(
            site_url('Proposal_pengajuan/review'), $total_rows, $per_page,
            array('per_page' => $per_page != 10 ? $per_page : null, 'status' => $status_filter, 'kelas' => $kelas_filter)
        ));
        $page = max(1, (int) $this->uri->segment(3));
        $pengajuan_list = $this->Proposal_pengajuan_model->get_all($filters, $per_page, ($page - 1) * $per_page);

        $kelas_list = array();
        foreach ($this->Proposal_model->get_all($tahun_aktif ? $tahun_aktif->id : null) as $p) {
            if (!empty($p->kelas) && !in_array($p->kelas, $kelas_list)) {
                $kelas_list[] = $p->kelas;
            }
        }
        sort($kelas_list);

        $data = array(
            'title' => 'Review Pengajuan Judul Proposal TA',
            'page_title' => 'Review Pengajuan Judul Proposal TA',
            'breadcrumb' => array(
                array('label' => 'Proposal TA', 'url' => '#'),
                array('label' => 'Review Pengajuan', 'active' => true)
            ),
            'sidebar_menu' => generate_sidebar_menu('sekprodi'),
            'pengajuan_list' => $pengajuan_list,
            'tahun_aktif' => $tahun_aktif,
            'status_filter' => $status_filter,
            'kelas_filter' => $kelas_filter,
            'kelas_list' => $kelas_list,
            'pagination' => $this->pagination->create_links(),
            'per_page' => $per_page,
            'total_rows' => $total_rows
        );

        $data['content'] = $this->load->view('proposal_pengajuan/review', $data, true);
        $this->load->view('layouts/main', $data);
    }

    /**
     * Detail pengajuan + form approve / tolak.
     */
    public function detail($id) {
        $this->check_role('sekprodi');

        $pengajuan = $this->Proposal_pengajuan_model->get_by_id($id);
        if (!$pengajuan) {
            $this->session->set_flashdata('error', 'Data pengajuan tidak ditemukan.');
            redirect('Proposal_pengajuan/review');
        }

        $data = array(
            'title' => 'Detail Pengajuan Judul Proposal TA',
            'page_title' => 'Detail Pengajuan Judul Proposal TA',
            'breadcrumb' => array(
                array('label' => 'Review Pengajuan', 'url' => site_url('Proposal_pengajuan/review')),
                array('label' => 'Detail', 'active' => true)
            ),
            'sidebar_menu' => generate_sidebar_menu('sekprodi'),
            'pengajuan' => $pengajuan,
            'pembimbing_list' => $this->Proposal_pembimbing_model->get_allowed()
        );

        $data['content'] = $this->load->view('proposal_pengajuan/detail', $data, true);
        $this->load->view('layouts/main', $data);
    }

    /**
     * Setujui pengajuan: tetapkan judul final dan pembimbing.
     */
    public function approve($id) {
        $this->check_role('sekprodi');
        if ($this->input->method() !== 'post') {
            redirect('Proposal_pengajuan/detail/' . $id);
        }

        $pengajuan = $this->Proposal_pengajuan_model->get_by_id($id);
        if (!$pengajuan) {
            $this->session->set_flashdata('error', 'Data pengajuan tidak ditemukan.');
            redirect('Proposal_pengajuan/review');
        }
        if ($pengajuan->status !== 'pending') {
            $this->session->set_flashdata('error', 'Pengajuan ini sudah diproses.');
            redirect('Proposal_pengajuan/detail/' . $id);
        }

        $this->form_validation->set_rules('judul_disetujui', 'Judul Disetujui', 'required|trim|max_length[255]');
        $this->form_validation->set_rules('id_pembimbing', 'Pembimbing', 'required|integer|greater_than[0]');
        $this->form_validation->set_rules('catatan_sekprodi', 'Catatan', 'trim');
        if ($this->form_validation->run() == FALSE) {
            $this->session->set_flashdata('error', validation_errors());
            redirect('Proposal_pengajuan/detail/' . $id);
        }

        $plot = $this->Proposal_pengajuan_model->plot_pembimbing($pengajuan, $this->input->post('id_pembimbing'));
        if (!$plot['ok']) {
            $this->session->set_flashdata('error', $plot['message']);
            redirect('Proposal_pengajuan/detail/' . $id);
        }

        $judul = $this->input->post('judul_disetujui');
        $this->Proposal_pengajuan_model->update($id, array(
            'status' => 'disetujui',
            'judul_disetujui' => $judul,
            'catatan_sekprodi' => $this->input->post('catatan_sekprodi')
        ));

        $proposal = $this->Proposal_model->get_by_mahasiswa($pengajuan->id_mahasiswa, $pengajuan->id_tahun_akademik);
        if ($proposal) {
            $this->Proposal_model->update($proposal->id, array(
                'judul_proposal' => $judul,
                'status' => 'sedang_proposal'
            ));
        }

        proposal_notify('mahasiswa', $pengajuan->id_mahasiswa, 'Pengajuan Judul Proposal TA Disetujui',
            'Judul Anda disetujui sekprodi. Menunggu ACC judul dari pembimbing.', site_url('Proposal_pengajuan'));

        $this->session->set_flashdata('success', 'Pengajuan judul disetujui dan pembimbing ditetapkan.');
        redirect('Proposal_pengajuan/review');
    }

    /**
     * Tolak pengajuan (alasan wajib).
     */
    public function reject($id) {
        $this->check_role('sekprodi');
        if ($this->input->method() !== 'post') {
            redirect('Proposal_pengajuan/detail/' . $id);
        }

        $pengajuan = $this->Proposal_pengajuan_model->get_by_id($id);
        if (!$pengajuan) {
            $this->session->set_flashdata('error', 'Data pengajuan tidak ditemukan.');
            redirect('Proposal_pengajuan/review');
        }
        if ($pengajuan->status !== 'pending') {
            $this->session->set_flashdata('error', 'Pengajuan ini sudah diproses.');
            redirect('Proposal_pengajuan/detail/' . $id);
        }

        $this->form_validation->set_rules('catatan_sekprodi', 'Alasan Penolakan', 'required|trim');
        if ($this->form_validation->run() == FALSE) {
            $this->session->set_flashdata('error', validation_errors());
            redirect('Proposal_pengajuan/detail/' . $id);
        }

        $this->Proposal_pengajuan_model->update($id, array(
            'status' => 'ditolak',
            'catatan_sekprodi' => $this->input->post('catatan_sekprodi')
        ));

        proposal_notify('mahasiswa', $pengajuan->id_mahasiswa, 'Pengajuan Judul Proposal TA Ditolak',
            'Pengajuan judul ditolak. Lihat catatan sekprodi dan ajukan kembali.', site_url('Proposal_pengajuan'));

        $this->session->set_flashdata('success', 'Pengajuan judul ditolak.');
        redirect('Proposal_pengajuan/review');
    }

    /**
     * Hapus pengajuan (tidak boleh bila bimbingan sudah berjalan).
     */
    public function delete($id) {
        $this->check_role('sekprodi');
        if ($this->input->method() !== 'post') {
            redirect('Proposal_pengajuan/review');
        }

        $pengajuan = $this->Proposal_pengajuan_model->get_by_id($id);
        if (!$pengajuan) {
            $this->session->set_flashdata('error', 'Data pengajuan tidak ditemukan.');
            redirect('Proposal_pengajuan/review');
        }

        $bimbingan = $this->Proposal_bimbingan_model->get_by_mahasiswa($pengajuan->id_mahasiswa, $pengajuan->id_tahun_akademik);
        if (!empty($bimbingan)) {
            $this->session->set_flashdata('error', 'Pengajuan tidak dapat dihapus karena bimbingan sudah dimulai.');
            redirect('Proposal_pengajuan/review');
        }

        $this->db->trans_start();
        $this->db->where('id_pengajuan', $id)->delete('proposal_perubahan_judul');
        $this->Proposal_pengajuan_model->delete($id);
        $this->db->trans_complete();

        if ($this->db->trans_status()) {
            $this->session->set_flashdata('success', 'Pengajuan judul berhasil dihapus.');
        } else {
            $this->session->set_flashdata('error', 'Gagal menghapus pengajuan judul.');
        }
        redirect('Proposal_pengajuan/review');
    }

    /**
     * Koreksi langsung judul yang sudah disetujui (mis. salah ketik). Hanya Sekprodi.
     */
    public function edit_judul($id) {
        $this->check_role('sekprodi');
        if ($this->input->method() !== 'post') {
            redirect('Proposal_pengajuan/review');
        }

        $pengajuan = $this->Proposal_pengajuan_model->get_by_id($id);
        if (!$pengajuan || $pengajuan->status !== 'disetujui') {
            $this->session->set_flashdata('error', 'Hanya pengajuan yang sudah disetujui yang dapat diubah judulnya.');
            redirect('Proposal_pengajuan/review');
        }

        $this->form_validation->set_rules('judul_disetujui', 'Judul Baru', 'required|trim|min_length[10]|max_length[255]');
        $this->form_validation->set_rules('catatan_edit_judul', 'Catatan', 'trim');
        if ($this->form_validation->run() == FALSE) {
            $this->session->set_flashdata('error', validation_errors());
            redirect('Proposal_pengajuan/review');
        }

        $judul = $this->input->post('judul_disetujui');
        $this->Proposal_pengajuan_model->update($id, array('judul_disetujui' => $judul));

        $proposal = $this->Proposal_model->get_by_mahasiswa($pengajuan->id_mahasiswa, $pengajuan->id_tahun_akademik);
        if ($proposal) {
            $this->Proposal_model->update($proposal->id, array('judul_proposal' => $judul));
        }

        proposal_notify('mahasiswa', $pengajuan->id_mahasiswa, 'Judul Proposal TA Diperbarui',
            'Sekprodi memperbarui judul Proposal TA Anda.', site_url('Proposal_pengajuan'));

        $this->session->set_flashdata('success', 'Judul berhasil diperbarui.');
        redirect('Proposal_pengajuan/review');
    }
}
