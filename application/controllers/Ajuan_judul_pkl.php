<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Ajuan_judul_pkl Controller
 * Menangani ajuan judul PKL oleh dosen pembimbing
 */
class Ajuan_judul_pkl extends MY_Controller {

    public function __construct() {
        parent::__construct();
        $this->check_role('dosen');
        $this->load->model('Pkl_pengajuan_model');
        $this->load->model('Pkl_tempat_model');
        $this->load->model('Tahun_akademik_model');
        $this->load->library('form_validation');
        $this->load->library('pagination');
        $this->load->helper('notifikasi');
    }

    /**
     * Daftar ajuan judul PKL yang sudah disetujui sekprodi
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

        // Ambil semua pengajuan dimana dosen ini adalah pembimbingnya
        // Ambil dengan filter id_pembimbing saja, tidak filter status, agar semua pengajuan diambil
        $all_pengajuan = $this->Pkl_pengajuan_model->get_all($tahun_aktif->id, null, $dosen->id);
        
        // Filter berdasarkan status_acc_judul jika ada, dan pastikan judul bukan placeholder
        // Juga skip pengajuan yang status_approve_revisi = 'pending' (sudah masuk ke sekprodi untuk approval perubahan judul)
        $pengajuan_list = array();
        foreach ($all_pengajuan as $pengajuan) {
            // Skip jika judul masih placeholder (belum di-submit mahasiswa)
            if ($pengajuan->judul_pkl == 'Menunggu Pengajuan Judul' || empty($pengajuan->judul_pkl)) {
                continue;
            }
            
            // Skip jika status_approve_revisi = 'pending' (sudah masuk ke sekprodi untuk approval perubahan judul)
            // Tapi hanya skip jika memang ada pengajuan perubahan judul (ada judul_revisi atau file_approve_revisi)
            // Jangan skip pengajuan baru yang belum pernah direvisi
            if ($pengajuan->status_approve_revisi == 'pending' && 
                (!empty($pengajuan->judul_revisi) || !empty($pengajuan->file_approve_revisi))) {
                continue;
            }
            
            // Pastikan id_pembimbing ter-set (jika tidak, skip karena tidak valid)
            if (empty($pengajuan->id_pembimbing)) {
                continue;
            }
            
            // Filter berdasarkan status_acc_judul jika ada
            if ($filter_status) {
                $status_acc = $pengajuan->status_acc_judul ?? 'pending';
                if ($status_acc == $filter_status) {
                    $pengajuan_list[] = $pengajuan;
                }
            } else {
                $pengajuan_list[] = $pengajuan;
            }
        }
        
        // Hapus duplikat berdasarkan id (jika ada pengajuan yang muncul di kedua array)
        $unique_pengajuan = array();
        $seen_ids = array();
        foreach ($pengajuan_list as $pengajuan) {
            if (!in_array($pengajuan->id, $seen_ids)) {
                $unique_pengajuan[] = $pengajuan;
                $seen_ids[] = $pengajuan->id;
            }
        }
        $pengajuan_list = $unique_pengajuan;
        
        // Simpan total rows sebelum pagination
        $total_rows = count($pengajuan_list);

        // Pagination
        $config['base_url'] = site_url('Ajuan_judul_pkl/index');
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
            $config['first_url'] = site_url('Ajuan_judul_pkl/index?' . $query_string);
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
            'title' => 'Ajuan Judul PKL',
            'page_title' => 'Ajuan Judul PKL',
            'breadcrumb' => array(
                array('label' => 'PKL', 'url' => '#'),
                array('label' => 'Ajuan Judul Mahasiswa', 'active' => true)
            ),
            'sidebar_menu' => generate_sidebar_menu('dosen'),
            'pengajuan_list' => $pengajuan_list_paginated,
            'tahun_aktif' => $tahun_aktif,
            'dosen_id' => $dosen->id,
            'filter_status' => $filter_status,
            'pagination' => $this->pagination->create_links(),
            'per_page' => $per_page
        );

        $data['content'] = $this->load->view('dosen/ajuan_judul_pkl/index', $data, true);
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

        $pengajuan = $this->Pkl_pengajuan_model->get_by_id($id);
        if (!$pengajuan) {
            $this->session->set_flashdata('error', 'Data pengajuan tidak ditemukan.');
            redirect('Ajuan_judul_pkl');
        }

        // Cek apakah dosen ini adalah pembimbing
        if ($pengajuan->id_pembimbing != $dosen->id) {
            $this->session->set_flashdata('error', 'Anda tidak memiliki akses untuk review pengajuan ini.');
            redirect('Ajuan_judul_pkl');
        }

        // Cek apakah status sekprodi sudah disetujui atau pending (jika pembimbing sudah di-plot, pengajuan bisa langsung masuk)
        // Skip jika status ditolak
        if ($pengajuan->status == 'ditolak') {
            $this->session->set_flashdata('error', 'Pengajuan ini telah ditolak.');
            redirect('Ajuan_judul_pkl');
        }
        
        // Skip jika judul masih placeholder
        if ($pengajuan->judul_pkl == 'Menunggu Pengajuan Judul') {
            $this->session->set_flashdata('error', 'Pengajuan ini belum memiliki judul.');
            redirect('Ajuan_judul_pkl');
        }

        // Ambil data tempat PKL
        $tempat_pkl = $this->Pkl_tempat_model->get_by_mahasiswa($pengajuan->id_mahasiswa, $pengajuan->id_tahun_akademik);

        $data = array(
            'title' => 'Detail Ajuan Judul PKL',
            'page_title' => 'Detail Ajuan Judul PKL',
            'breadcrumb' => array(
                array('label' => 'PKL', 'url' => '#'),
                array('label' => 'Ajuan Judul Mahasiswa', 'url' => site_url('Ajuan_judul_pkl')),
                array('label' => 'Detail', 'active' => true)
            ),
            'sidebar_menu' => generate_sidebar_menu('dosen'),
            'pengajuan' => $pengajuan,
            'tempat_pkl' => $tempat_pkl
        );

        $data['content'] = $this->load->view('dosen/ajuan_judul_pkl/detail', $data, true);
        $this->load->view('layouts/main', $data);
    }

    /**
     * ACC judul oleh pembimbing
     */
    public function acc($id) {
        $id_dosen = $this->session->userdata('ref_id');
        $dosen = $this->db->get_where('dosen', array('id' => $id_dosen))->row();
        
        if (!$dosen) {
            $this->session->set_flashdata('error', 'Data dosen tidak ditemukan.');
            redirect('Dashboard');
        }

        $pengajuan = $this->Pkl_pengajuan_model->get_by_id($id);
        if (!$pengajuan) {
            $this->session->set_flashdata('error', 'Data pengajuan tidak ditemukan.');
            redirect('Ajuan_judul_pkl');
        }

        // Cek apakah dosen ini adalah pembimbing
        if ($pengajuan->id_pembimbing != $dosen->id) {
            $this->session->set_flashdata('error', 'Anda bukan pembimbing untuk pengajuan ini.');
            redirect('Ajuan_judul_pkl');
        }

        // Update status ACC
        $update_data = array(
            'status_acc_judul' => 'acc'
        );

        if ($this->Pkl_pengajuan_model->update($id, $update_data)) {
            $this->session->set_flashdata('success', 'Judul PKL telah disetujui.');
        } else {
            $this->session->set_flashdata('error', 'Gagal menyetujui judul PKL.');
        }

        redirect('Ajuan_judul_pkl/detail/' . $id);
    }

    /**
     * Revisi judul oleh pembimbing
     */
    public function revisi($id) {
        $id_dosen = $this->session->userdata('ref_id');
        $dosen = $this->db->get_where('dosen', array('id' => $id_dosen))->row();
        
        if (!$dosen) {
            $this->session->set_flashdata('error', 'Data dosen tidak ditemukan.');
            redirect('Dashboard');
        }

        $pengajuan = $this->Pkl_pengajuan_model->get_by_id($id);
        if (!$pengajuan) {
            $this->session->set_flashdata('error', 'Data pengajuan tidak ditemukan.');
            redirect('Ajuan_judul_pkl');
        }

        // Cek apakah dosen ini adalah pembimbing
        if ($pengajuan->id_pembimbing != $dosen->id) {
            $this->session->set_flashdata('error', 'Anda bukan pembimbing untuk pengajuan ini.');
            redirect('Ajuan_judul_pkl');
        }

        if ($this->input->method() === 'post') {
            $this->form_validation->set_rules('catatan_revisi', 'Catatan Revisi', 'required|trim');

            if ($this->form_validation->run() == FALSE) {
                redirect('Ajuan_judul_pkl/detail/' . $id);
            }

            // Update status revisi
            $update_data = array(
                'status_acc_judul' => 'revisi',
                'catatan_revisi' => $this->input->post('catatan_revisi')
            );

            if ($this->Pkl_pengajuan_model->update($id, $update_data)) {
                $this->session->set_flashdata('success', 'Judul PKL ditolak dan catatan revisi telah disimpan.');
            } else {
                $this->session->set_flashdata('error', 'Gagal menyimpan catatan revisi.');
            }

            redirect('Ajuan_judul_pkl/detail/' . $id);
        } else {
            redirect('Ajuan_judul_pkl/detail/' . $id);
        }
    }

    /**
     * Ubah judul PKL oleh pembimbing
     */
    public function ubah_judul($id) {
        if ($this->input->method() !== 'post') {
            show_404();
        }

        $id_dosen = $this->session->userdata('ref_id');
        $dosen = $this->db->get_where('dosen', array('id' => $id_dosen))->row();
        if (!$dosen) {
            show_404();
        }

        $pengajuan = $this->Pkl_pengajuan_model->get_by_id($id);
        if (!$pengajuan || (int) $pengajuan->id_pembimbing !== (int) $dosen->id) {
            $this->session->set_flashdata('error', 'Anda tidak memiliki akses untuk mengubah judul ini.');
            redirect('Ajuan_judul_pkl');
        }

        if ($pengajuan->status == 'ditolak') {
            $this->session->set_flashdata('error', 'Pengajuan telah ditolak.');
            redirect('Ajuan_judul_pkl/detail/' . $id);
        }

        $this->form_validation->set_rules('judul_pkl', 'Judul PKL', 'required|trim|min_length[10]');
        if ($this->form_validation->run() === false) {
            $this->session->set_flashdata('error', validation_errors());
            redirect('Ajuan_judul_pkl/detail/' . $id);
        }

        $judul_baru = trim($this->input->post('judul_pkl'));
        if ($judul_baru === Pkl_pengajuan_model::PLACEHOLDER_JUDUL) {
            $this->session->set_flashdata('error', 'Judul PKL tidak valid.');
            redirect('Ajuan_judul_pkl/detail/' . $id);
        }

        if ($this->Pkl_pengajuan_model->update($id, array('judul_pkl' => $judul_baru))) {
            send_in_app_notification_by_ref(
                'mahasiswa',
                (int) $pengajuan->id_mahasiswa,
                'Judul PKL Diperbarui',
                'Dosen pembimbing memperbarui judul PKL Anda menjadi: ' . $judul_baru,
                site_url('Pkl/detail_pengajuan/' . $id)
            );
            $this->session->set_flashdata('success', 'Judul PKL berhasil diperbarui.');
        } else {
            $this->session->set_flashdata('error', 'Gagal memperbarui judul PKL.');
        }

        redirect('Ajuan_judul_pkl/detail/' . $id);
    }

    /**
     * Download file proposal PKL
     */
    public function download_proposal($id) {
        $id_dosen = $this->session->userdata('ref_id');
        $pengajuan = $this->Pkl_pengajuan_model->get_by_id($id);
        
        if (!$pengajuan) {
            $this->session->set_flashdata('error', 'Data pengajuan tidak ditemukan.');
            redirect('Ajuan_judul_pkl');
        }
        
        // Cek apakah dosen ini adalah pembimbing
        if ($pengajuan->id_pembimbing != $id_dosen) {
            $this->session->set_flashdata('error', 'Anda tidak memiliki akses untuk mengunduh file ini.');
            redirect('Ajuan_judul_pkl');
        }
        
        // Cek apakah file proposal ada
        if (!$pengajuan->file_proposal) {
            $this->session->set_flashdata('error', 'File proposal tidak ditemukan.');
            redirect('Ajuan_judul_pkl/detail/' . $id);
        }
        
        // Bersihkan path dari ./ jika ada dan normalisasi path
        $file_path = $pengajuan->file_proposal;
        $file_path = ltrim($file_path, './');
        $file_path = str_replace('\\', '/', $file_path);
        
        $full_path = FCPATH . $file_path;
        
        if (!file_exists($full_path)) {
            $this->session->set_flashdata('error', 'File tidak ditemukan di server.');
            redirect('Ajuan_judul_pkl/detail/' . $id);
        }
        
        $this->load->helper('download');
        $file_name = basename($file_path);
        force_download($file_name, file_get_contents($full_path));
    }
}

