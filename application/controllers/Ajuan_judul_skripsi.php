<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Ajuan_judul_skripsi Controller
 * Menangani ajuan judul Skripsi oleh dosen pembimbing
 */
class Ajuan_judul_skripsi extends MY_Controller {

    public function __construct() {
        parent::__construct();
        $this->check_role('dosen');
        $this->load->model('Skripsi_pengajuan_model');
        $this->load->model('Skripsi_model');
        $this->load->model('Skripsi_bimbingan_model');
        $this->load->model('Tahun_akademik_model');
        $this->load->library('form_validation');
        $this->load->library('pagination');
        $this->load->helper('notifikasi');
    }

    /**
     * Daftar ajuan judul Skripsi yang sudah diplot pembimbing
     */
    public function index() {
        $id_dosen = $this->session->userdata('ref_id');
        $dosen = $this->db->get_where('dosen', array('id' => $id_dosen))->row();
        
        if (!$dosen) {
            $this->session->set_flashdata('error', 'Data dosen tidak ditemukan.');
            redirect('Dashboard');
        }

        $tahun_aktif = $this->Tahun_akademik_model->get_aktif();
        if (!$tahun_aktif) {
            $this->session->set_flashdata('error', 'Tidak ada tahun akademik aktif.');
            redirect('Dashboard');
        }

        // Filter status
        $filter_status = $this->input->get('status');
        $per_page = $this->input->get('per_page') ? (int)$this->input->get('per_page') : 10;
        $per_page = in_array($per_page, [10, 25, 50, 100]) ? $per_page : 10;

        // Ambil semua pengajuan dimana dosen ini adalah pembimbing 1 atau pembimbing 2
        // (tidak hanya pending, agar data ACC/revisi tetap muncul)
        $all_pengajuan = $this->Skripsi_pengajuan_model->get_by_pembimbing_all($id_dosen, $tahun_aktif->id);
        
        // Filter berdasarkan status jika ada
        $pengajuan_list = array();
        foreach ($all_pengajuan as $pengajuan) {
            // Tentukan status berdasarkan tipe pembimbing (cek dari mahasiswa_skripsi atau skripsi_pengajuan)
            $tipe_pembimbing = null;
            $id_pembimbing_1 = isset($pengajuan->id_pembimbing_1) ? $pengajuan->id_pembimbing_1 : (isset($pengajuan->id_pembimbing_1_final) ? $pengajuan->id_pembimbing_1_final : null);
            $id_pembimbing_2 = isset($pengajuan->id_pembimbing_2) ? $pengajuan->id_pembimbing_2 : (isset($pengajuan->id_pembimbing_2_final) ? $pengajuan->id_pembimbing_2_final : null);
            
            if ($id_pembimbing_1 == $id_dosen) {
                $tipe_pembimbing = 'pembimbing_1';
                $status_acc = isset($pengajuan->status_acc_judul_p1) ? $pengajuan->status_acc_judul_p1 : 'pending';
            } elseif ($id_pembimbing_2 == $id_dosen) {
                $tipe_pembimbing = 'pembimbing_2';
                $status_acc = isset($pengajuan->status_acc_judul_p2) ? $pengajuan->status_acc_judul_p2 : 'pending';
            }
            
            if (!$tipe_pembimbing) {
                continue;
            }
            
            // Filter berdasarkan status jika ada
            if ($filter_status) {
                if ($status_acc == $filter_status) {
                    $pengajuan->tipe_pembimbing = $tipe_pembimbing;
                    $pengajuan->status_acc = $status_acc;
                    $pengajuan_list[] = $pengajuan;
                }
            } else {
                $pengajuan->tipe_pembimbing = $tipe_pembimbing;
                $pengajuan->status_acc = $status_acc;
                $pengajuan_list[] = $pengajuan;
            }
        }
        
        // Simpan total rows sebelum pagination
        $total_rows = count($pengajuan_list);

        // Pagination
        $config['base_url'] = site_url('Ajuan_judul_skripsi/index');
        $config['total_rows'] = $total_rows;
        $config['per_page'] = $per_page;
        $config['uri_segment'] = 3;
        
        $query_params = array_filter(array(
            'per_page' => ($per_page != 10 ? $per_page : null),
            'status' => $filter_status
        ));
        $query_string = http_build_query($query_params);
        if ($query_string) {
            $config['suffix'] = '?' . $query_string;
            $config['first_url'] = site_url('Ajuan_judul_skripsi/index?' . $query_string);
        }
        
        $config['use_page_numbers'] = TRUE;
        $config['first_link'] = '&laquo; Awal';
        $config['last_link'] = 'Akhir &raquo;';
        $config['next_link'] = '&gt;';
        $config['prev_link'] = '&lt;';
        $config['full_tag_open'] = '<ul class="pagination pagination-sm m-0 float-right">';
        $config['full_tag_close'] = '</ul>';
        $config['first_tag_open'] = '<li class="page-item">';
        $config['first_tag_close'] = '</li>';
        $config['last_tag_open'] = '<li class="page-item">';
        $config['last_tag_close'] = '</li>';
        $config['next_tag_open'] = '<li class="page-item">';
        $config['next_tag_close'] = '</li>';
        $config['prev_tag_open'] = '<li class="page-item">';
        $config['prev_tag_close'] = '</li>';
        $config['cur_tag_open'] = '<li class="page-item active"><span class="page-link">';
        $config['cur_tag_close'] = '</span></li>';
        $config['num_tag_open'] = '<li class="page-item">';
        $config['num_tag_close'] = '</li>';
        $config['attributes'] = array('class' => 'page-link');

        $this->pagination->initialize($config);
        $page = ($this->uri->segment(3)) ? $this->uri->segment(3) : 1;
        $offset = ($page - 1) * $per_page;

        $pengajuan_list_paginated = array_slice($pengajuan_list, $offset, $per_page);

        $data = array(
            'title' => 'Ajuan Judul Skripsi',
            'page_title' => 'Ajuan Judul Skripsi',
            'breadcrumb' => array(
                array('label' => 'Skripsi', 'url' => '#'),
                array('label' => 'Ajuan Judul Mahasiswa', 'active' => true)
            ),
            'sidebar_menu' => generate_sidebar_menu('dosen'),
            'pengajuan_list' => $pengajuan_list_paginated,
            'tahun_aktif' => $tahun_aktif,
            'dosen_id' => $dosen->id,
            'filter_status' => $filter_status,
            'pagination' => $this->pagination->create_links(),
            'per_page' => $per_page,
            'total_rows' => $total_rows
        );

        $data['content'] = $this->load->view('dosen/ajuan_judul_skripsi/index', $data, true);
        $this->load->view('layouts/main', $data);
    }

    /**
     * Detail pengajuan untuk review
     */
    public function detail($id) {
        $id_dosen = $this->session->userdata('ref_id');
        $dosen = $this->db->get_where('dosen', array('id' => $id_dosen))->row();
        
        if (!$dosen) {
            $this->session->set_flashdata('error', 'Data dosen tidak ditemukan.');
            redirect('Dashboard');
        }

        $pengajuan = $this->Skripsi_pengajuan_model->get_by_id($id);
        if (!$pengajuan) {
            $this->session->set_flashdata('error', 'Data pengajuan tidak ditemukan.');
            redirect('Ajuan_judul_skripsi');
        }

        // Cek apakah dosen ini adalah pembimbing 1 atau 2 (cek dari mahasiswa_skripsi atau skripsi_pengajuan)
        $tipe_pembimbing = null;
        $id_pembimbing_1 = isset($pengajuan->id_pembimbing_1) ? $pengajuan->id_pembimbing_1 : (isset($pengajuan->id_pembimbing_1_final) ? $pengajuan->id_pembimbing_1_final : null);
        $id_pembimbing_2 = isset($pengajuan->id_pembimbing_2) ? $pengajuan->id_pembimbing_2 : (isset($pengajuan->id_pembimbing_2_final) ? $pengajuan->id_pembimbing_2_final : null);
        
        if ($id_pembimbing_1 == $id_dosen) {
            $tipe_pembimbing = 'pembimbing_1';
            $status_acc = isset($pengajuan->status_acc_judul_p1) ? $pengajuan->status_acc_judul_p1 : 'pending';
        } elseif ($id_pembimbing_2 == $id_dosen) {
            $tipe_pembimbing = 'pembimbing_2';
            $status_acc = isset($pengajuan->status_acc_judul_p2) ? $pengajuan->status_acc_judul_p2 : 'pending';
        } else {
            $this->session->set_flashdata('error', 'Anda bukan pembimbing untuk pengajuan ini.');
            redirect('Ajuan_judul_skripsi');
        }

        if ($this->input->method() === 'post') {
            $action = $this->input->post('action');
            
            if ($action === 'acc') {
                // ACC judul
                $update_data = array();
                if ($tipe_pembimbing == 'pembimbing_1') {
                    $update_data['status_acc_judul_p1'] = 'acc';
                    $update_data['catatan_revisi_p1'] = $this->input->post('catatan');
                } else {
                    $update_data['status_acc_judul_p2'] = 'acc';
                    $update_data['catatan_revisi_p2'] = $this->input->post('catatan');
                }
                
                if ($this->Skripsi_pengajuan_model->update($id, $update_data)) {
                    // Update status pengajuan jika kedua pembimbing sudah ACC
                    $this->_check_and_update_status_pengajuan($id);
                    
                    $this->session->set_flashdata('success', 'Judul Skripsi disetujui.');
                } else {
                    $this->session->set_flashdata('error', 'Gagal menyetujui judul.');
                }
            } elseif ($action === 'revisi') {
                // Revisi judul
                $update_data = array();
                if ($tipe_pembimbing == 'pembimbing_1') {
                    $update_data['status_acc_judul_p1'] = 'revisi';
                    $update_data['catatan_revisi_p1'] = $this->input->post('catatan');
                } else {
                    $update_data['status_acc_judul_p2'] = 'revisi';
                    $update_data['catatan_revisi_p2'] = $this->input->post('catatan');
                }
                
                if ($this->Skripsi_pengajuan_model->update($id, $update_data)) {
                    $this->session->set_flashdata('success', 'Judul Skripsi perlu direvisi. Catatan sudah dikirim ke mahasiswa.');
                } else {
                    $this->session->set_flashdata('error', 'Gagal mengirim catatan revisi.');
                }
            }
            
            redirect('Ajuan_judul_skripsi');
        }

        $data = array(
            'title' => 'Detail Ajuan Judul Skripsi',
            'page_title' => 'Detail Ajuan Judul Skripsi',
            'breadcrumb' => array(
                array('label' => 'Skripsi', 'url' => '#'),
                array('label' => 'Ajuan Judul Mahasiswa', 'url' => site_url('Ajuan_judul_skripsi')),
                array('label' => 'Detail', 'active' => true)
            ),
            'sidebar_menu' => generate_sidebar_menu('dosen'),
            'pengajuan' => $pengajuan,
            'tipe_pembimbing' => $tipe_pembimbing,
            'status_acc' => $status_acc
        );

        $data['content'] = $this->load->view('dosen/ajuan_judul_skripsi/detail', $data, true);
        $this->load->view('layouts/main', $data);
    }

    /**
     * Ubah judul skripsi mahasiswa (khusus Pembimbing 1) — tetap boleh meski status ACC.
     */
    public function ubah_judul_mahasiswa($id) {
        if ($this->input->method() !== 'post') {
            show_404();
        }

        $id_dosen = $this->session->userdata('ref_id');
        if (!$id_dosen) {
            show_404();
        }

        $pengajuan = $this->Skripsi_pengajuan_model->get_by_id($id);
        if (!$pengajuan) {
            $this->session->set_flashdata('error', 'Data pengajuan tidak ditemukan.');
            redirect('Ajuan_judul_skripsi');
        }

        $id_pembimbing_1 = isset($pengajuan->id_pembimbing_1) ? $pengajuan->id_pembimbing_1 : (isset($pengajuan->id_pembimbing_1_final) ? $pengajuan->id_pembimbing_1_final : null);
        if ((int) $id_pembimbing_1 !== (int) $id_dosen) {
            $this->session->set_flashdata('error', 'Hanya Pembimbing 1 yang dapat mengubah judul skripsi mahasiswa.');
            redirect('Ajuan_judul_skripsi/detail/' . $id);
        }

        $this->form_validation->set_rules('judul_disetujui', 'Judul Skripsi', 'required|trim|min_length[10]');
        if ($this->form_validation->run() === false) {
            $this->session->set_flashdata('error', validation_errors());
            redirect('Ajuan_judul_skripsi/detail/' . $id);
        }

        $judul_baru = trim((string) $this->input->post('judul_disetujui'));
        if ($this->_apply_skripsi_judul_update($pengajuan, $judul_baru)) {
            send_in_app_notification_by_ref(
                'mahasiswa',
                (int) $pengajuan->id_mahasiswa,
                'Judul Skripsi Diperbarui',
                'Pembimbing 1 memperbarui judul skripsi Anda menjadi: ' . $judul_baru,
                site_url('Skripsi_pengajuan')
            );
            $this->session->set_flashdata('success', 'Judul skripsi mahasiswa berhasil diperbarui.');
        } else {
            $this->session->set_flashdata('error', 'Gagal memperbarui judul skripsi.');
        }

        redirect('Ajuan_judul_skripsi/detail/' . $id);
    }

    /**
     * Download file proposal
     */
    public function download_proposal($id) {
        $pengajuan = $this->Skripsi_pengajuan_model->get_by_id($id);
        if (!$pengajuan || empty($pengajuan->file_proposal)) {
            $this->session->set_flashdata('error', 'Link tidak ditemukan.');
            redirect('Ajuan_judul_skripsi');
        }

        // Cek akses berdasarkan role
        $id_dosen = $this->session->userdata('ref_id');
        $id_pembimbing_1 = isset($pengajuan->id_pembimbing_1) ? $pengajuan->id_pembimbing_1 : (isset($pengajuan->id_pembimbing_1_final) ? $pengajuan->id_pembimbing_1_final : null);
        $id_pembimbing_2 = isset($pengajuan->id_pembimbing_2) ? $pengajuan->id_pembimbing_2 : (isset($pengajuan->id_pembimbing_2_final) ? $pengajuan->id_pembimbing_2_final : null);
        
        if ($id_dosen != $id_pembimbing_1 && $id_dosen != $id_pembimbing_2) {
            $this->session->set_flashdata('error', 'Anda tidak memiliki akses untuk mengakses link ini.');
            redirect('Ajuan_judul_skripsi');
        }

        // Redirect ke Google Drive link
        $this->load->helper('google_drive');
        $link = normalize_google_drive_link($pengajuan->file_proposal);
        redirect($link);
    }

    /**
     * Terapkan perubahan judul skripsi ke pengajuan, mahasiswa_skripsi, dan topik bimbingan.
     */
    private function _apply_skripsi_judul_update($pengajuan, $judul_baru) {
        $update_data = array(
            'judul_disetujui' => $judul_baru,
            'judul_pengajuan' => $judul_baru,
        );

        if (!$this->Skripsi_pengajuan_model->update($pengajuan->id, $update_data)) {
            return false;
        }

        $skripsi = $this->Skripsi_model->get_by_mahasiswa($pengajuan->id_mahasiswa, $pengajuan->id_tahun_akademik);
        if ($skripsi) {
            $this->Skripsi_model->update($skripsi->id, array(
                'judul_skripsi' => $judul_baru,
            ));

            $bimbingan_list = $this->Skripsi_bimbingan_model->get_by_mahasiswa($pengajuan->id_mahasiswa, $pengajuan->id_tahun_akademik);
            foreach ($bimbingan_list as $bimbingan) {
                $this->Skripsi_bimbingan_model->update($bimbingan->id, array(
                    'judul_topik' => $judul_baru,
                ));
            }
        }

        return true;
    }

    /**
     * Helper method untuk cek dan update status pengajuan jika kedua pembimbing sudah ACC
     */
    private function _check_and_update_status_pengajuan($id) {
        // Ambil data pengajuan terbaru
        $pengajuan = $this->Skripsi_pengajuan_model->get_by_id($id);
        
        if (!$pengajuan) {
            return false;
        }
        
        // Cek apakah ada pembimbing 1 dan pembimbing 2 (prioritas dari id_pembimbing_1_final/id_pembimbing_2_final)
        $has_pembimbing_1 = !empty($pengajuan->id_pembimbing_1_final);
        $has_pembimbing_2 = !empty($pengajuan->id_pembimbing_2_final);
        
        // Jika tidak ada pembimbing final, cek dari mahasiswa_skripsi
        if (!$has_pembimbing_1 && !empty($pengajuan->id_pembimbing_1)) {
            $has_pembimbing_1 = true;
        }
        if (!$has_pembimbing_2 && !empty($pengajuan->id_pembimbing_2)) {
            $has_pembimbing_2 = true;
        }
        
        // Cek status ACC kedua pembimbing
        $status_p1 = isset($pengajuan->status_acc_judul_p1) ? $pengajuan->status_acc_judul_p1 : 'pending';
        $status_p2 = isset($pengajuan->status_acc_judul_p2) ? $pengajuan->status_acc_judul_p2 : 'pending';
        
        // Jika kedua pembimbing sudah diplot dan sudah ACC, update status pengajuan menjadi 'disetujui'
        if ($has_pembimbing_1 && $has_pembimbing_2 && 
            $status_p1 == 'acc' && $status_p2 == 'acc' && 
            $pengajuan->status != 'disetujui') {
            $update_status = array('status' => 'disetujui');
            return $this->Skripsi_pengajuan_model->update($id, $update_status);
        }
        
        return false;
    }
}
