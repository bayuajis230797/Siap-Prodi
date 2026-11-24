<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Pendaftaran Sidang Controller
 * Mengelola pengajuan pendaftaran sidang dari mahasiswa (untuk Sekprodi)
 */
class Pendaftaran_sidang extends MY_Controller {

    public function __construct() {
        parent::__construct();
        $this->check_role('sekprodi');
        $this->load->model('Skripsi_pendaftaran_sidang_model');
        $this->load->model('Skripsi_berkas_upload_model');
        $this->load->model('Skripsi_model');
        $this->load->model('Jadwal_skripsi_model');
        $this->load->model('Dosen_model');
        $this->load->model('Tahun_akademik_model');
        $this->load->library('pagination');
        $this->load->library('form_validation');
    }

    public function index() {
        $tahun_aktif = $this->Tahun_akademik_model->get_aktif();
        $status_filter = $this->input->get('status');
        $kelas_filter = $this->input->get('kelas');

        $filters = array();
        if ($tahun_aktif) {
            $filters['id_tahun_akademik'] = $tahun_aktif->id;
        }
        if (!empty($status_filter)) {
            $filters['status'] = $status_filter;
        }
        if (!empty($kelas_filter)) {
            $filters['kelas'] = $kelas_filter;
        }

        $config['base_url'] = site_url('Pendaftaran_sidang/index');
        $config['total_rows'] = $this->Skripsi_pendaftaran_sidang_model->count_all($filters);
        $config['per_page'] = 10;
        $config['uri_segment'] = 3;
        $config['reuse_query_string'] = TRUE;
        $config['full_tag_open'] = '<ul class="pagination pagination-sm m-0 float-right">';
        $config['full_tag_close'] = '</ul>';
        $config['attributes'] = array('class' => 'page-link');
        $config['first_tag_open'] = '<li class="page-item">';
        $config['first_tag_close'] = '</li>';
        $config['last_tag_open'] = '<li class="page-item">';
        $config['last_tag_close'] = '</li>';
        $config['next_tag_open'] = '<li class="page-item">';
        $config['next_tag_close'] = '</li>';
        $config['prev_tag_open'] = '<li class="page-item">';
        $config['prev_tag_close'] = '</li>';
        $config['cur_tag_open'] = '<li class="page-item active"><a class="page-link" href="#">';
        $config['cur_tag_close'] = '</a></li>';
        $config['num_tag_open'] = '<li class="page-item">';
        $config['num_tag_close'] = '</li>';

        $this->pagination->initialize($config);

        $page = ($this->uri->segment(3)) ? $this->uri->segment(3) : 1;
        $offset = ($page - 1) * $config['per_page'];

        $data['pendaftaran_list'] = $this->Skripsi_pendaftaran_sidang_model->get_all($filters, $config['per_page'], $offset);
        $data['pagination'] = $this->pagination->create_links();
        $data['total_rows'] = $config['total_rows'];
        $data['status_filter'] = $status_filter;
        $data['kelas_filter'] = $kelas_filter;
        $data['tahun_aktif'] = $tahun_aktif;
        $data['title'] = 'Pendaftaran Sidang';
        $data['page_title'] = 'Pengajuan Pendaftaran Sidang';
        $data['breadcrumb'] = array(
            array('label' => 'Pendaftaran Sidang', 'active' => true)
        );
        $data['sidebar_menu'] = generate_sidebar_menu('sekprodi');
        $data['content'] = $this->load->view('pendaftaran_sidang/index', $data, true);
        $this->load->view('layouts/main', $data);
    }

    public function detail($id) {
        $pendaftaran = $this->Skripsi_pendaftaran_sidang_model->get_by_id($id);
        if (!$pendaftaran) {
            $this->session->set_flashdata('error', 'Data pendaftaran tidak ditemukan.');
            redirect('Pendaftaran_sidang');
        }

        // Ambil berkas yang diupload
        $berkas_uploaded = $this->Skripsi_berkas_upload_model->get_by_pendaftaran($id);

        // Ambil data skripsi
        $skripsi = $this->Skripsi_model->get_by_id($pendaftaran->id_mahasiswa_skripsi);

        // Ambil jadwal jika sudah ada
        $jadwal_list = $this->Jadwal_skripsi_model->get_by_mahasiswa($skripsi->id_mahasiswa, $pendaftaran->id_tahun_akademik);
        $jadwal = !empty($jadwal_list) ? $jadwal_list[0] : null;

        // Ambil daftar dosen untuk penguji
        $dosen_list = $this->Dosen_model->get_all();

        $data = array(
            'title' => 'Detail Pendaftaran Sidang',
            'page_title' => 'Detail Pendaftaran Sidang',
            'breadcrumb' => array(
                array('label' => 'Pendaftaran Sidang', 'url' => site_url('Pendaftaran_sidang')),
                array('label' => 'Detail', 'active' => true)
            ),
            'sidebar_menu' => generate_sidebar_menu('sekprodi'),
            'pendaftaran' => $pendaftaran,
            'berkas_uploaded' => $berkas_uploaded,
            'skripsi' => $skripsi,
            'jadwal' => $jadwal,
            'dosen_list' => $dosen_list
        );

        $data['content'] = $this->load->view('pendaftaran_sidang/detail', $data, true);
        $this->load->view('layouts/main', $data);
    }

    public function approve($id) {
        $pendaftaran = $this->Skripsi_pendaftaran_sidang_model->get_by_id($id);
        if (!$pendaftaran) {
            $this->session->set_flashdata('error', 'Data pendaftaran tidak ditemukan.');
            redirect('Pendaftaran_sidang');
        }

        if ($this->input->method() === 'post') {
            $this->form_validation->set_rules('id_ketua_penguji', 'Ketua Penguji', 'required|integer');
            $this->form_validation->set_rules('id_anggota_penguji_1', 'Anggota Penguji 1', 'required|integer');
            $this->form_validation->set_rules('tanggal_ujian', 'Tanggal Ujian', 'required');
            $this->form_validation->set_rules('jam_mulai', 'Jam Mulai', 'required');
            $this->form_validation->set_rules('jam_selesai', 'Jam Selesai', 'required');
            $this->form_validation->set_rules('ruangan', 'Ruangan', 'required|trim');

            if ($this->form_validation->run() === FALSE) {
                $this->session->set_flashdata('error', validation_errors());
                redirect('Pendaftaran_sidang/detail/' . $id);
            }

            $skripsi = $this->Skripsi_model->get_by_id($pendaftaran->id_mahasiswa_skripsi);
            
            $ketua_penguji = (int) $this->input->post('id_ketua_penguji');
            $anggota_penguji_1 = (int) $this->input->post('id_anggota_penguji_1');
            $anggota_penguji_2 = $skripsi->id_pembimbing_2; // Otomatis pembimbing 2

            // Validasi: Pembimbing 1 tidak boleh jadi ketua atau anggota 1
            if ($skripsi->id_pembimbing_1 == $ketua_penguji || $skripsi->id_pembimbing_1 == $anggota_penguji_1) {
                $this->session->set_flashdata('error', 'Pembimbing 1 tidak boleh menjadi Ketua Penguji atau Anggota Penguji 1.');
                redirect('Pendaftaran_sidang/detail/' . $id);
            }

            // Validasi: Tidak boleh ada duplikasi
            if ($ketua_penguji == $anggota_penguji_1 || $ketua_penguji == $anggota_penguji_2 || $anggota_penguji_1 == $anggota_penguji_2) {
                $this->session->set_flashdata('error', 'Penguji tidak boleh duplikat.');
                redirect('Pendaftaran_sidang/detail/' . $id);
            }

            // Update status pendaftaran
            $this->Skripsi_pendaftaran_sidang_model->update($id, array(
                'status' => 'disetujui',
                'catatan_sekprodi' => $this->input->post('catatan_sekprodi')
            ));

            // Buat jadwal sidang
            $tahun_aktif = $this->Tahun_akademik_model->get_aktif();
            $this->Jadwal_skripsi_model->create(array(
                'id_mahasiswa_skripsi' => $pendaftaran->id_mahasiswa_skripsi,
                'id_ketua_penguji' => $ketua_penguji,
                'id_anggota_penguji_1' => $anggota_penguji_1,
                'id_anggota_penguji_2' => $anggota_penguji_2,
                'tanggal_ujian' => $this->input->post('tanggal_ujian'),
                'jam_mulai' => $this->input->post('jam_mulai'),
                'jam_selesai' => $this->input->post('jam_selesai'),
                'ruangan' => $this->input->post('ruangan'),
                'id_tahun_akademik' => $tahun_aktif->id,
                'status' => 'terjadwal',
                'catatan' => $this->input->post('catatan_sekprodi')
            ));

            $this->session->set_flashdata('success', 'Pendaftaran sidang disetujui dan jadwal sidang berhasil dibuat.');
            redirect('Pendaftaran_sidang');
        }

        redirect('Pendaftaran_sidang/detail/' . $id);
    }

    public function reject($id) {
        $pendaftaran = $this->Skripsi_pendaftaran_sidang_model->get_by_id($id);
        if (!$pendaftaran) {
            $this->session->set_flashdata('error', 'Data pendaftaran tidak ditemukan.');
            redirect('Pendaftaran_sidang');
        }

        if ($this->input->method() === 'post') {
            $this->form_validation->set_rules('catatan_sekprodi', 'Catatan', 'required|trim|min_length[10]');

            if ($this->form_validation->run() === FALSE) {
                $this->session->set_flashdata('error', validation_errors());
                redirect('Pendaftaran_sidang/detail/' . $id);
            }

            $this->Skripsi_pendaftaran_sidang_model->update($id, array(
                'status' => 'ditolak',
                'catatan_sekprodi' => $this->input->post('catatan_sekprodi')
            ));

            $this->session->set_flashdata('success', 'Pendaftaran sidang ditolak.');
            redirect('Pendaftaran_sidang');
        }

        redirect('Pendaftaran_sidang/detail/' . $id);
    }

    public function download_berkas($id) {
        $berkas = $this->Skripsi_berkas_upload_model->get_by_id($id);
        if (!$berkas || !file_exists('./' . $berkas->file_path)) {
            $this->session->set_flashdata('error', 'File tidak ditemukan.');
            redirect('Pendaftaran_sidang');
        }

        $this->load->helper('download');
        force_download($berkas->file_name, file_get_contents('./' . $berkas->file_path));
    }
}

