<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Bimbingan_proposal_mahasiswa Controller
 * Bimbingan Proposal TA oleh mahasiswa (satu pembimbing).
 * Satu topik = satu baris proposal_bimbingan + log percakapan.
 */
class Bimbingan_proposal_mahasiswa extends MY_Controller {

    public function __construct() {
        parent::__construct();
        $this->check_role('mahasiswa');
        $this->load->model('Proposal_bimbingan_model');
        $this->load->model('Proposal_bimbingan_log_model');
        $this->load->model('Proposal_model');
        $this->load->model('Proposal_pengajuan_model');
        $this->load->model('Tahun_akademik_model');
        $this->load->library('form_validation');
        $this->load->helper(array('proposal', 'google_drive', 'notifikasi'));
    }

    /**
     * Pastikan mahasiswa boleh bimbingan (judul di-ACC pembimbing). Redirect bila belum.
     * @return array [pengajuan, tahun_aktif, proposal]
     */
    private function gate_bimbingan($redirect_to = 'Halaman_utama') {
        $id_mahasiswa = $this->session->userdata('ref_id');
        $tahun_aktif = $this->Tahun_akademik_model->get_aktif();
        if (!$tahun_aktif) {
            $this->session->set_flashdata('error', 'Tidak ada tahun akademik aktif.');
            redirect($redirect_to);
        }

        $pengajuan = $this->Proposal_pengajuan_model->get_latest_by_mahasiswa($id_mahasiswa, $tahun_aktif->id);
        if (!$pengajuan || $pengajuan->status !== 'disetujui') {
            $this->session->set_flashdata('error', 'Pengajuan judul Proposal TA belum disetujui sekprodi.');
            redirect($redirect_to);
        }
        if (($pengajuan->status_acc_judul ?? 'pending') !== 'acc') {
            $this->session->set_flashdata('error', 'Bimbingan belum aktif. Syarat: judul di-ACC pembimbing.');
            redirect($redirect_to);
        }

        $proposal = $this->Proposal_model->get_by_mahasiswa($id_mahasiswa, $tahun_aktif->id);
        return array($pengajuan, $tahun_aktif, $proposal);
    }

    private function enrich($bimbingan_list) {
        foreach ($bimbingan_list as $b) {
            $w = proposal_bimbingan_workflow($b, $this->Proposal_bimbingan_log_model->get_latest_by_bimbingan($b->id));
            $b->workflow_status_key = $w['key'];
            $b->workflow_status_label = $w['label'];
            $b->workflow_status_class = $w['class'];
        }
        return $bimbingan_list;
    }

    public function index() {
        redirect('Bimbingan_proposal_mahasiswa/bimbingan');
    }

    /**
     * Daftar topik bimbingan.
     */
    public function bimbingan() {
        list($pengajuan, $tahun_aktif, $proposal) = $this->gate_bimbingan();
        $id_mahasiswa = $this->session->userdata('ref_id');
        $status_filter = $this->input->get('status');
        if (!in_array($status_filter, array('open', 'closed'), true)) {
            $status_filter = null;
        }

        $list = $this->enrich($this->Proposal_bimbingan_model->get_by_mahasiswa($id_mahasiswa, $tahun_aktif->id, $status_filter));

        $data = array(
            'title' => 'Bimbingan Proposal TA',
            'page_title' => 'Bimbingan Proposal TA',
            'breadcrumb' => array(
                array('label' => 'Proposal TA', 'url' => site_url('Proposal_pengajuan')),
                array('label' => 'Bimbingan', 'active' => true)
            ),
            'sidebar_menu' => generate_sidebar_menu('mahasiswa'),
            'bimbingan_list' => $list,
            'pengajuan' => $pengajuan,
            'proposal' => $proposal,
            'tahun_aktif' => $tahun_aktif,
            'status_filter' => $status_filter
        );
        $data['content'] = $this->load->view('mahasiswa/proposal/bimbingan', $data, true);
        $this->load->view('layouts/main', $data);
    }

    /**
     * Buat topik bimbingan baru.
     */
    public function bimbingan_create() {
        list($pengajuan, $tahun_aktif, $proposal) = $this->gate_bimbingan('Bimbingan_proposal_mahasiswa/bimbingan');
        $id_mahasiswa = $this->session->userdata('ref_id');

        if ($proposal && !empty($proposal->status) && in_array($proposal->status, array('layak_sidang', 'selesai_proposal'), true)) {
            $this->session->set_flashdata('error', 'Anda sudah dinyatakan layak sidang. Topik bimbingan baru tidak dapat dibuat.');
            redirect('Bimbingan_proposal_mahasiswa/bimbingan');
        }

        // Hanya satu topik yang boleh berjalan: topik baru setelah topik sebelumnya di-ACC atau ditutup
        foreach ($this->enrich($this->Proposal_bimbingan_model->get_by_mahasiswa($id_mahasiswa, $tahun_aktif->id, 'open')) as $b) {
            if ($b->workflow_status_key !== 'approved') {
                $this->session->set_flashdata('error', 'Masih ada topik "' . $b->judul_topik . '" yang belum di-ACC pembimbing. Selesaikan topik tersebut terlebih dahulu.');
                redirect('Bimbingan_proposal_mahasiswa/bimbingan');
            }
        }

        if ($this->input->method() === 'post') {
            $this->form_validation->set_rules('judul_topik', 'Judul Topik', 'required|trim|callback_valid_proposal_topik_bimbingan');
            $this->form_validation->set_rules('pesan', 'Pesan', 'trim');

            if ($this->form_validation->run() !== FALSE) {
                require_google_drive_or_redirect();
                $this->load->library('Upload_to_drive');

                $share_emails = array();
                $dosen_user = $this->db->select('u.id')
                    ->from('users u')
                    ->join('roles r', 'r.id = u.role_id')
                    ->where('r.nama_role', 'dosen')
                    ->where('u.ref_id', (int) $pengajuan->id_pembimbing_final)
                    ->get()->row();
                if ($dosen_user) {
                    $email = $this->upload_to_drive->get_drive_email($dosen_user->id);
                    if ($email) {
                        $share_emails[] = $email;
                    }
                }

                $upload = $this->upload_to_drive->handle_upload('file_laporan', array(
                    'owner_user_id' => (int) $this->session->userdata('user_id'),
                    'folder_parts' => array('SIAP', 'Proposal', 'bimbingan'),
                    'share_emails' => $share_emails,
                    'share_anyone' => true,
                    'allowed_types' => 'pdf|doc|docx|zip|rar',
                    'max_size_kb' => 15360
                ));
                if (empty($upload['ok'])) {
                    $this->session->set_flashdata('error', isset($upload['message']) ? $upload['message'] : 'Gagal upload berkas.');
                    redirect('Bimbingan_proposal_mahasiswa/bimbingan_create');
                }

                $link = $upload['webViewLink'];
                $judul_topik = $this->input->post('judul_topik');

                $id_bimbingan = $this->Proposal_bimbingan_model->create(array(
                    'id_mahasiswa' => $id_mahasiswa,
                    'id_pembimbing' => $pengajuan->id_pembimbing_final,
                    'id_tahun_akademik' => $tahun_aktif->id,
                    'judul_topik' => $judul_topik,
                    'status' => 'open',
                    'last_activity' => date('Y-m-d H:i:s')
                ));

                if ($id_bimbingan) {
                    $this->Proposal_bimbingan_log_model->create(array(
                        'id_bimbingan' => $id_bimbingan,
                        'id_pengguna' => $this->session->userdata('user_id'),
                        'role_pengguna' => 'mahasiswa',
                        'pesan' => $this->input->post('pesan') ?: 'Topik bimbingan baru',
                        'status_revisi' => null,
                        'keterangan_revisi' => null,
                        'file_path' => $link,
                        'file_name' => !empty($upload['file_name']) ? $upload['file_name'] : ('Proposal TA - ' . $judul_topik),
                        'urutan' => $this->Proposal_bimbingan_log_model->get_next_urutan($id_bimbingan)
                    ));

                    proposal_notify('dosen', $pengajuan->id_pembimbing_final, 'Topik Bimbingan Proposal TA Baru',
                        'Mahasiswa mengajukan topik bimbingan: ' . $judul_topik, site_url('Bimbingan_proposal/detail/' . $id_bimbingan));

                    $this->session->set_flashdata('success', 'Topik bimbingan berhasil dibuat. Menunggu review pembimbing.');
                    redirect('Bimbingan_proposal_mahasiswa/bimbingan_detail/' . encode_id($id_bimbingan));
                }
                $this->session->set_flashdata('error', 'Gagal membuat topik bimbingan.');
            }
        }

        $this->load->model('Dosen_model');
        $data = array(
            'title' => 'Buat Topik Bimbingan Proposal TA',
            'page_title' => 'Buat Topik Bimbingan Baru',
            'breadcrumb' => array(
                array('label' => 'Proposal TA', 'url' => site_url('Proposal_pengajuan')),
                array('label' => 'Bimbingan', 'url' => site_url('Bimbingan_proposal_mahasiswa/bimbingan')),
                array('label' => 'Topik Baru', 'active' => true)
            ),
            'sidebar_menu' => generate_sidebar_menu('mahasiswa'),
            'pengajuan' => $pengajuan,
            'pembimbing' => $this->Dosen_model->get_by_id($pengajuan->id_pembimbing_final)
        );
        $data['content'] = $this->load->view('mahasiswa/proposal/bimbingan_create', $data, true);
        $this->load->view('layouts/main', $data);
    }

    /**
     * Detail topik (ID terenkripsi di URL).
     */
    public function bimbingan_detail($encoded_id) {
        $bimbingan = $this->ambil_bimbingan_milik_saya($encoded_id);
        $logs = $this->Proposal_bimbingan_log_model->get_by_bimbingan($bimbingan->id);
        $latest = !empty($logs) ? end($logs) : null;
        $workflow = proposal_bimbingan_workflow($bimbingan, $latest);

        $data = array(
            'title' => 'Detail Bimbingan Proposal TA',
            'page_title' => 'Detail Bimbingan: ' . $bimbingan->judul_topik,
            'breadcrumb' => array(
                array('label' => 'Proposal TA', 'url' => site_url('Proposal_pengajuan')),
                array('label' => 'Bimbingan', 'url' => site_url('Bimbingan_proposal_mahasiswa/bimbingan')),
                array('label' => 'Detail', 'active' => true)
            ),
            'sidebar_menu' => generate_sidebar_menu('mahasiswa'),
            'bimbingan' => $bimbingan,
            'logs' => $logs,
            'statistik' => $this->Proposal_bimbingan_log_model->get_statistik_by_bimbingan($bimbingan->id),
            'workflow' => $workflow,
            'can_send_revisi' => ($bimbingan->status === 'open' && $workflow['key'] === 'need_revision')
        );
        $data['content'] = $this->load->view('mahasiswa/proposal/bimbingan_detail', $data, true);
        $this->load->view('layouts/main', $data);
    }

    /**
     * Kirim laporan revisi (hanya setelah dosen meminta revisi).
     */
    public function bimbingan_kirim($encoded_id) {
        $bimbingan = $this->ambil_bimbingan_milik_saya($encoded_id);
        $url_detail = 'Bimbingan_proposal_mahasiswa/bimbingan_detail/' . encode_id($bimbingan->id);
        if ($this->input->method() !== 'post') {
            redirect($url_detail);
        }

        $latest = $this->Proposal_bimbingan_log_model->get_latest_by_bimbingan($bimbingan->id);
        $workflow = proposal_bimbingan_workflow($bimbingan, $latest);
        if ($bimbingan->status !== 'open' || $workflow['key'] !== 'need_revision') {
            $this->session->set_flashdata('error', 'Anda tidak dapat mengirim revisi saat ini.');
            redirect($url_detail);
        }

        $this->form_validation->set_rules('pesan', 'Pesan', 'trim');
        if ($this->form_validation->run() == FALSE) {
            $this->session->set_flashdata('error', validation_errors());
            redirect($url_detail);
        }

        require_google_drive_or_redirect();
        $this->load->library('Upload_to_drive');
        $share_emails = array();
        $dosen_user = $this->db->select('u.id')
            ->from('users u')
            ->join('roles r', 'r.id = u.role_id')
            ->where('r.nama_role', 'dosen')
            ->where('u.ref_id', (int) $bimbingan->id_pembimbing)
            ->get()->row();
        if ($dosen_user) {
            $email = $this->upload_to_drive->get_drive_email($dosen_user->id);
            if ($email) {
                $share_emails[] = $email;
            }
        }
        $upload = $this->upload_to_drive->handle_upload('file_laporan', array(
            'owner_user_id' => (int) $this->session->userdata('user_id'),
            'folder_parts' => array('SIAP', 'Proposal', 'bimbingan'),
            'share_emails' => $share_emails,
            'share_anyone' => true,
            'allowed_types' => 'pdf|doc|docx|zip|rar',
            'max_size_kb' => 15360
        ));
        if (empty($upload['ok'])) {
            $this->session->set_flashdata('error', isset($upload['message']) ? $upload['message'] : 'Gagal upload berkas.');
            redirect($url_detail);
        }

        $this->Proposal_bimbingan_log_model->create(array(
            'id_bimbingan' => $bimbingan->id,
            'id_pengguna' => $this->session->userdata('user_id'),
            'role_pengguna' => 'mahasiswa',
            'pesan' => $this->input->post('pesan') ?: 'Revisi laporan',
            'status_revisi' => null,
            'keterangan_revisi' => null,
            'file_path' => $upload['webViewLink'],
            'file_name' => !empty($upload['file_name']) ? $upload['file_name'] : ('Revisi Proposal TA - ' . $bimbingan->judul_topik),
            'urutan' => $this->Proposal_bimbingan_log_model->get_next_urutan($bimbingan->id)
        ));
        $this->Proposal_bimbingan_model->update($bimbingan->id, array('last_activity' => date('Y-m-d H:i:s')));

        proposal_notify('dosen', $bimbingan->id_pembimbing, 'Revisi Bimbingan Proposal TA',
            'Mahasiswa mengirim revisi pada topik: ' . $bimbingan->judul_topik, site_url('Bimbingan_proposal/detail/' . $bimbingan->id));

        $this->session->set_flashdata('success', 'Revisi berhasil dikirim. Menunggu review pembimbing.');
        redirect($url_detail);
    }

    /**
     * Cetak PDF histori bimbingan (semua topik, atau satu topik bila ID diberikan).
     */
    public function cetak_histori_bimbingan($encoded_id = null) {
        $id_mahasiswa = $this->session->userdata('ref_id');
        $tahun_aktif = $this->Tahun_akademik_model->get_aktif();
        if (!$tahun_aktif) {
            $this->session->set_flashdata('error', 'Tidak ada tahun akademik aktif.');
            redirect('Bimbingan_proposal_mahasiswa/bimbingan');
        }

        $this->load->model('Mahasiswa_model');
        $this->load->model('Dosen_model');
        $mahasiswa = $this->Mahasiswa_model->get_by_id($id_mahasiswa);
        $pengajuan = $this->Proposal_pengajuan_model->get_latest_by_mahasiswa($id_mahasiswa, $tahun_aktif->id);
        if (!$pengajuan || !$mahasiswa) {
            $this->session->set_flashdata('error', 'Data pengajuan tidak ditemukan.');
            redirect('Bimbingan_proposal_mahasiswa/bimbingan');
        }

        if ($encoded_id) {
            $bimbingan_list = array($this->ambil_bimbingan_milik_saya($encoded_id));
        } else {
            $bimbingan_list = $this->Proposal_bimbingan_model->get_by_mahasiswa($id_mahasiswa, $tahun_aktif->id);
        }

        $proposal = $this->Proposal_model->get_by_mahasiswa($id_mahasiswa, $tahun_aktif->id);
        $all_logs = array();
        $stat = (object) array('total_topik' => count($bimbingan_list), 'total_log' => 0, 'total_acc' => 0, 'total_revisi' => 0);
        foreach ($bimbingan_list as $b) {
            $logs = $this->Proposal_bimbingan_log_model->get_by_bimbingan($b->id);
            $all_logs[$b->id] = array('bimbingan' => $b, 'logs' => $logs);
            $stat->total_log += count($logs);
            foreach ($logs as $l) {
                if ($l->status_revisi === 'acc') { $stat->total_acc++; }
                if ($l->status_revisi === 'revisi') { $stat->total_revisi++; }
            }
        }

        $data = array(
            'mahasiswa' => $mahasiswa,
            'pembimbing' => $pengajuan->id_pembimbing_final ? $this->Dosen_model->get_by_id($pengajuan->id_pembimbing_final) : null,
            'pengajuan' => $pengajuan,
            'proposal' => $proposal,
            'judul' => get_judul_proposal_terbaru($id_mahasiswa, $tahun_aktif->id),
            'all_logs' => $all_logs,
            'tahun_aktif' => $tahun_aktif,
            'statistik' => $stat
        );
        $html = $this->load->view('mahasiswa/proposal/cetak_histori_bimbingan', $data, true);

        $this->load->library('Pdf');
        $this->pdf->load_html($html);
        $this->pdf->set_paper('A4', 'portrait');
        $this->pdf->stream('Histori_Bimbingan_Proposal_TA_' . $mahasiswa->nim . '_' . date('Ymd') . '.pdf');
    }

    /* ----- validasi & util ----- */

    public function valid_proposal_topik_bimbingan($str) {
        if (!valid_proposal_topik_bimbingan_label($str)) {
            $this->form_validation->set_message('valid_proposal_topik_bimbingan', 'Pilih salah satu topik bimbingan yang telah ditentukan.');
            return false;
        }
        return true;
    }

    public function valid_drive_link($link) {
        if (!is_valid_google_drive_link($link)) {
            $this->form_validation->set_message('valid_drive_link', 'Link Google Drive tidak valid.');
            return false;
        }
        return true;
    }

    private function ambil_bimbingan_milik_saya($encoded_id) {
        $id = decode_id($encoded_id);
        if ($id === false) {
            show_404();
        }
        $bimbingan = $this->Proposal_bimbingan_model->get_by_id($id);
        if (!$bimbingan || (int) $bimbingan->id_mahasiswa !== (int) $this->session->userdata('ref_id')) {
            show_404();
        }
        return $bimbingan;
    }
}
