<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Judul Skripsi Controller
 * Mengelola ajuan judul skripsi dan setting pembimbing untuk Sekprodi
 */
class Judul_skripsi extends MY_Controller {

    public function __construct() {
        parent::__construct();
        $this->check_role('sekprodi');
        $this->load->model('Skripsi_pengajuan_model');
        $this->load->model('Skripsi_model');
        $this->load->model('Skripsi_pembimbing_model');
        $this->load->model('Dosen_model');
        $this->load->model('Tahun_akademik_model');
        $this->load->library('pagination');
        $this->load->library('form_validation');
    }

    /**
     * Daftar ajuan judul skripsi
     */
    public function index() {
        $tahun_aktif = $this->Tahun_akademik_model->get_aktif();
        $id_tahun_akademik = $tahun_aktif ? $tahun_aktif->id : null;

        // Ambil parameter filter
        $filter_status = $this->input->get('status');
        $filter_kelas = $this->input->get('kelas');
        $per_page = $this->input->get('per_page') ? (int)$this->input->get('per_page') : 10;
        $per_page = in_array($per_page, [10, 25, 50, 100]) ? $per_page : 10;

        // Build filters
        $filters = array();
        if ($id_tahun_akademik) {
            $filters['id_tahun_akademik'] = $id_tahun_akademik;
        }
        if ($filter_status) {
            $filters['status'] = $filter_status;
        }
        if ($filter_kelas) {
            $filters['kelas'] = $filter_kelas;
        }

        // Pagination config
        $config['base_url'] = site_url('Judul_skripsi/index');
        $config['total_rows'] = $this->Skripsi_pengajuan_model->count_all($filters);
        $config['per_page'] = $per_page;
        $config['uri_segment'] = 3;

        // Build query string untuk pagination
        $query_params = array_filter(array(
            'per_page' => ($per_page != 10 ? $per_page : null),
            'status' => $filter_status,
            'kelas' => $filter_kelas
        ));
        $query_string = http_build_query($query_params);
        if ($query_string) {
            $config['suffix'] = '?' . $query_string;
            $config['first_url'] = site_url('Judul_skripsi/index?' . $query_string);
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

        $pengajuan_list = $this->Skripsi_pengajuan_model->get_all($filters, $per_page, $offset);
        
        // Ambil daftar kelas unik untuk filter
        $all_pengajuan = $this->Skripsi_pengajuan_model->get_all($filters);
        $kelas_list = array();
        foreach ($all_pengajuan as $p) {
            if (!empty($p->kelas) && !in_array($p->kelas, $kelas_list)) {
                $kelas_list[] = $p->kelas;
            }
        }
        sort($kelas_list);

        $data = array(
            'title' => 'Judul Skripsi',
            'page_title' => 'Judul Skripsi',
            'breadcrumb' => array(array('label' => 'Judul Skripsi', 'active' => true)),
            'sidebar_menu' => generate_sidebar_menu('sekprodi'),
            'pengajuan_list' => $pengajuan_list,
            'tahun_aktif' => $tahun_aktif,
            'pagination' => $this->pagination->create_links(),
            'per_page' => $per_page,
            'kelas_list' => $kelas_list,
            'kelas_filter' => $filter_kelas,
            'status_filter' => $filter_status,
            'total_rows' => $config['total_rows']
        );

        $data['content'] = $this->load->view('judul_skripsi/index', $data, true);
        $this->load->view('layouts/main', $data);
    }

    /**
     * Detail dan setting pembimbing untuk ajuan judul
     */
    public function detail($id) {
        $pengajuan = $this->Skripsi_pengajuan_model->get_by_id($id);
        if (!$pengajuan) {
            $this->session->set_flashdata('error', 'Pengajuan tidak ditemukan.');
            redirect('Judul_skripsi');
        }

        if ($this->input->method() === 'post') {
            $this->form_validation->set_rules('status', 'Status', 'required|in_list[pending,disetujui,ditolak]');
            
            if ($this->form_validation->run() == FALSE) {
                $this->session->set_flashdata('error', validation_errors());
                redirect('Judul_skripsi/detail/' . $id);
            }

            $status = $this->input->post('status');
            $judul_disetujui = $this->input->post('judul_disetujui');
            $id_pembimbing_1_final = $this->input->post('id_pembimbing_1_final');
            $id_pembimbing_2_final = $this->input->post('id_pembimbing_2_final');
            $catatan_sekprodi = $this->input->post('catatan_sekprodi');

            $data_update = array(
                'status' => $status,
                'catatan_sekprodi' => $catatan_sekprodi,
                'approved_by' => get_user_id(),
                'approved_at' => date('Y-m-d H:i:s')
            );

            // Jika disetujui, update judul dan pembimbing
            if ($status == 'disetujui') {
                if (!empty($judul_disetujui)) {
                    $data_update['judul_disetujui'] = $judul_disetujui;
                } else {
                    $data_update['judul_disetujui'] = $pengajuan->judul_pengajuan;
                }

                // Tentukan pembimbing final
                $pembimbing_1_final = null;
                $pembimbing_2_final = null;
                
                if (!empty($id_pembimbing_1_final)) {
                    $pembimbing_1_final = $id_pembimbing_1_final;
                } elseif (!empty($pengajuan->id_pembimbing_1_request)) {
                    $pembimbing_1_final = $pengajuan->id_pembimbing_1_request;
                }
                
                if (!empty($id_pembimbing_2_final)) {
                    $pembimbing_2_final = $id_pembimbing_2_final;
                } elseif (!empty($pengajuan->id_pembimbing_2_request)) {
                    $pembimbing_2_final = $pengajuan->id_pembimbing_2_request;
                }

                $data_update['id_pembimbing_1_final'] = $pembimbing_1_final;
                $data_update['id_pembimbing_2_final'] = $pembimbing_2_final;

                // Update juga di tabel mahasiswa_skripsi
                $skripsi = $this->Skripsi_model->get_by_mahasiswa($pengajuan->id_mahasiswa, $pengajuan->id_tahun_akademik);
                if ($skripsi) {
                    $data_skripsi = array(
                        'judul_skripsi' => $data_update['judul_disetujui'],
                        'status' => 'sedang_skripsi'
                    );
                    
                    // Update pembimbing jika sudah ditetapkan
                    if ($pembimbing_1_final) {
                        $data_skripsi['id_pembimbing_1'] = $pembimbing_1_final;
                    }
                    if ($pembimbing_2_final) {
                        $data_skripsi['id_pembimbing_2'] = $pembimbing_2_final;
                    }
                    
                    $this->Skripsi_model->update($skripsi->id, $data_skripsi);
                }
            }

            if ($this->Skripsi_pengajuan_model->update($id, $data_update)) {
                $this->session->set_flashdata('success', 'Pengajuan berhasil diperbarui.');
            } else {
                $this->session->set_flashdata('error', 'Gagal memperbarui pengajuan.');
            }
            redirect('Judul_skripsi');
        }

        // Ambil daftar dosen yang boleh menjadi pembimbing
        $dosen_pembimbing_1 = $this->Skripsi_pembimbing_model->get_allowed_for_type(1);
        $dosen_pembimbing_2 = $this->Skripsi_pembimbing_model->get_allowed_for_type(2);

        $data = array(
            'title' => 'Detail Judul Skripsi',
            'page_title' => 'Detail Judul Skripsi',
            'breadcrumb' => array(
                array('label' => 'Judul Skripsi', 'url' => site_url('Judul_skripsi')),
                array('label' => 'Detail', 'active' => true)
            ),
            'sidebar_menu' => generate_sidebar_menu('sekprodi'),
            'pengajuan' => $pengajuan,
            'dosen_pembimbing_1' => $dosen_pembimbing_1,
            'dosen_pembimbing_2' => $dosen_pembimbing_2
        );

        $data['content'] = $this->load->view('judul_skripsi/detail', $data, true);
        $this->load->view('layouts/main', $data);
    }

    /**
     * Hapus pengajuan judul skripsi
     */
    public function delete($id) {
        $pengajuan = $this->Skripsi_pengajuan_model->get_by_id($id);
        if (!$pengajuan) {
            $this->session->set_flashdata('error', 'Pengajuan tidak ditemukan.');
            redirect('Judul_skripsi');
        }

        if ($this->Skripsi_pengajuan_model->delete($id)) {
            $this->session->set_flashdata('success', 'Pengajuan judul skripsi berhasil dihapus.');
        } else {
            $this->session->set_flashdata('error', 'Gagal menghapus pengajuan judul skripsi.');
        }
        redirect('Judul_skripsi');
    }
}

