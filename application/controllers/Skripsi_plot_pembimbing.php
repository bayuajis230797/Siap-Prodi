<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Skripsi_plot_pembimbing Controller
 * Mengelola plot pembimbing 1 dan 2 untuk pengajuan judul skripsi (untuk Sekprodi)
 */
class Skripsi_plot_pembimbing extends MY_Controller {

    public function __construct() {
        parent::__construct();
        $this->check_role('sekprodi');
        $this->load->model('Skripsi_pengajuan_model');
        $this->load->model('Skripsi_model');
        $this->load->model('Dosen_model');
        $this->load->model('Tahun_akademik_model');
        $this->load->library('pagination');
        $this->load->library('form_validation');
    }

    /**
     * Daftar pengajuan judul yang belum diplot pembimbing
     */
    public function index() {
        $tahun_aktif = $this->Tahun_akademik_model->get_aktif();
        $id_tahun_akademik = $tahun_aktif ? $tahun_aktif->id : null;

        $per_page = $this->input->get('per_page') ? (int)$this->input->get('per_page') : 10;
        $per_page = in_array($per_page, [10, 25, 50, 100]) ? $per_page : 10;
        
        $kelas_filter = $this->input->get('kelas');

        // Ambil semua pengajuan yang belum diplot pembimbing (id_pembimbing_1 atau id_pembimbing_2 masih null)
        $filters = array('id_tahun_akademik' => $id_tahun_akademik);
        if ($kelas_filter) {
            $filters['kelas'] = $kelas_filter;
        }
        
        $all_pengajuan = $this->Skripsi_pengajuan_model->get_all($filters);
        
        // Tampilkan semua pengajuan, tidak hanya yang belum diplot
        // Data tetap ada di tabel meskipun sudah diplot pembimbing
        $pengajuan_list_all = array();
        
        foreach ($all_pengajuan as $p) {
            // Tambahkan semua pengajuan ke dalam list
            $pengajuan_list_all[] = $p;
        }
        
        // Manual pagination
        $total_rows = count($pengajuan_list_all);
        $page = ($this->uri->segment(3)) ? $this->uri->segment(3) : 1;
        $offset = ($page - 1) * $per_page;
        $pengajuan_list = array_slice($pengajuan_list_all, $offset, $per_page);

        // Pagination config
        $config['base_url'] = site_url('Skripsi_plot_pembimbing/index');
        $config['total_rows'] = $total_rows;
        $config['per_page'] = $per_page;
        $config['uri_segment'] = 3;
        
        $query_params = array_filter(array(
            'per_page' => ($per_page != 10 ? $per_page : null),
            'kelas' => $kelas_filter
        ));
        $query_string = http_build_query($query_params);
        if ($query_string) {
            $config['suffix'] = '?' . $query_string;
            $config['first_url'] = site_url('Skripsi_plot_pembimbing/index?' . $query_string);
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

        // Ambil daftar kelas untuk filter
        $all_skripsi = $this->Skripsi_model->get_all($id_tahun_akademik);
        $kelas_list = array();
        foreach ($all_skripsi as $s) {
            if (!empty($s->kelas) && !in_array($s->kelas, $kelas_list)) {
                $kelas_list[] = $s->kelas;
            }
        }
        sort($kelas_list);

        $data = array(
            'title' => 'Plot Pembimbing Skripsi',
            'page_title' => 'Plot Pembimbing Skripsi',
            'breadcrumb' => array(
                array('label' => 'Skripsi', 'url' => '#'),
                array('label' => 'Plot Pembimbing', 'active' => true)
            ),
            'sidebar_menu' => generate_sidebar_menu('sekprodi'),
            'pengajuan_list' => $pengajuan_list,
            'tahun_aktif' => $tahun_aktif,
            'pagination' => $this->pagination->create_links(),
            'per_page' => $per_page,
            'kelas_list' => $kelas_list,
            'kelas_filter' => $kelas_filter,
            'total_rows' => $total_rows
        );

        $data['content'] = $this->load->view('sekprodi/skripsi_plot_pembimbing/index', $data, true);
        $this->load->view('layouts/main', $data);
    }

    /**
     * Detail pengajuan untuk plot pembimbing
     */
    public function detail($id) {
        $pengajuan = $this->Skripsi_pengajuan_model->get_by_id($id);
        
        if (!$pengajuan) {
            $this->session->set_flashdata('error', 'Data pengajuan judul tidak ditemukan.');
            redirect('Skripsi_plot_pembimbing');
        }

        // Ambil daftar dosen aktif
        $dosen_list = $this->Dosen_model->get_all_active();

        $data = array(
            'title' => 'Plot Pembimbing',
            'page_title' => 'Plot Pembimbing',
            'breadcrumb' => array(
                array('label' => 'Skripsi', 'url' => '#'),
                array('label' => 'Plot Pembimbing', 'url' => site_url('Skripsi_plot_pembimbing')),
                array('label' => 'Detail', 'active' => true)
            ),
            'sidebar_menu' => generate_sidebar_menu('sekprodi'),
            'pengajuan' => $pengajuan,
            'dosen_list' => $dosen_list
        );

        $data['content'] = $this->load->view('sekprodi/skripsi_plot_pembimbing/detail', $data, true);
        $this->load->view('layouts/main', $data);
    }

    /**
     * Proses plot pembimbing
     */
    public function proses($id) {
        $pengajuan = $this->Skripsi_pengajuan_model->get_by_id($id);
        
        if (!$pengajuan) {
            $this->session->set_flashdata('error', 'Data pengajuan judul tidak ditemukan.');
            redirect('Skripsi_plot_pembimbing');
        }

        if ($this->input->method() === 'post') {
            $this->form_validation->set_rules('id_pembimbing_1', 'Pembimbing 1', 'required|integer|greater_than[0]');
            $this->form_validation->set_rules('id_pembimbing_2', 'Pembimbing 2', 'trim');

            if ($this->form_validation->run() == FALSE) {
                redirect('Skripsi_plot_pembimbing/detail/' . $id);
            }

            $id_pembimbing_1 = (int) $this->input->post('id_pembimbing_1');
            $id_pembimbing_2_raw = $this->input->post('id_pembimbing_2');
            $id_pembimbing_2 = ($id_pembimbing_2_raw && (int)$id_pembimbing_2_raw > 0) ? (int) $id_pembimbing_2_raw : null;

            // Validasi pembimbing tidak boleh sama (jika keduanya diisi)
            if ($id_pembimbing_2 !== null && $id_pembimbing_1 == $id_pembimbing_2) {
                $this->session->set_flashdata('error', 'Pembimbing 1 dan Pembimbing 2 tidak boleh sama.');
                redirect('Skripsi_plot_pembimbing/detail/' . $id);
            }

            // Update pembimbing di mahasiswa_skripsi dan skripsi_pengajuan
            $tahun_aktif = $this->Tahun_akademik_model->get_aktif();
            
            // Dapatkan atau buat record mahasiswa_skripsi
            $skripsi = $this->Skripsi_model->get_by_mahasiswa($pengajuan->id_mahasiswa, $pengajuan->id_tahun_akademik);
            
            if (!$skripsi) {
                // Jika belum ada, buat record baru
                $data_skripsi_new = array(
                    'id_mahasiswa' => $pengajuan->id_mahasiswa,
                    'id_tahun_akademik' => $pengajuan->id_tahun_akademik,
                    'id_pembimbing_1' => $id_pembimbing_1,
                    'id_pembimbing_2' => $id_pembimbing_2,
                    'status' => 'bimbingan'
                );
                $skripsi_id = $this->Skripsi_model->create($data_skripsi_new);
                if ($skripsi_id) {
                    $skripsi = $this->Skripsi_model->get_by_id($skripsi_id);
                }
            }
            
            if ($skripsi) {
                // Update pembimbing di mahasiswa_skripsi (Pembimbing 2 opsional)
                $data_skripsi = array(
                    'id_pembimbing_1' => $id_pembimbing_1,
                    'id_pembimbing_2' => $id_pembimbing_2
                );
                
                // Update pembimbing di skripsi_pengajuan juga
                $data_pengajuan = array(
                    'id_pembimbing_1_final' => $id_pembimbing_1,
                    'id_pembimbing_2_final' => $id_pembimbing_2
                );
                
                $update_skripsi = $this->Skripsi_model->update($skripsi->id, $data_skripsi);
                $update_pengajuan = $this->Skripsi_pengajuan_model->update($id, $data_pengajuan);
                
                if ($update_skripsi !== false && $update_pengajuan) {
                    $this->session->set_flashdata('success', 'Pembimbing berhasil diplot.');
                } else {
                    $this->session->set_flashdata('error', 'Gagal memplot pembimbing.');
                }
            } else {
                $this->session->set_flashdata('error', 'Gagal membuat atau mendapatkan data skripsi mahasiswa.');
            }
        }

        redirect('Skripsi_plot_pembimbing');
    }
}

