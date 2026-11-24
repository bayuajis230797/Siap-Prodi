<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Proposal_plot_pembimbing Controller (Sekprodi)
 * Menetapkan atau mengganti SATU pembimbing Proposal TA untuk pengajuan judul.
 * Penulisan data pembimbing hanya lewat Proposal_pengajuan_model::plot_pembimbing().
 */
class Proposal_plot_pembimbing extends MY_Controller {

    public function __construct() {
        parent::__construct();
        $this->check_role('sekprodi');
        $this->load->model('Proposal_pengajuan_model');
        $this->load->model('Proposal_model');
        $this->load->model('Proposal_pembimbing_model');
        $this->load->model('Tahun_akademik_model');
        $this->load->library('pagination');
        $this->load->library('form_validation');
        $this->load->helper('proposal');
    }

    /**
     * Daftar pengajuan (selain yang ditolak) beserta pembimbingnya.
     */
    public function index() {
        $tahun_aktif = $this->Tahun_akademik_model->get_aktif();
        $id_tahun = $tahun_aktif ? $tahun_aktif->id : null;

        $per_page = (int) $this->input->get('per_page');
        $per_page = in_array($per_page, array(10, 25, 50, 100), true) ? $per_page : 10;
        $kelas_filter = $this->input->get('kelas');

        $filters = array('id_tahun_akademik' => $id_tahun, 'status_not' => 'ditolak');
        if ($kelas_filter) {
            $filters['kelas'] = $kelas_filter;
        }

        $total_rows = $this->Proposal_pengajuan_model->count_all($filters);
        $this->pagination->initialize(proposal_pagination_config(
            site_url('Proposal_plot_pembimbing/index'), $total_rows, $per_page,
            array('per_page' => $per_page != 10 ? $per_page : null, 'kelas' => $kelas_filter)
        ));
        $page = max(1, (int) $this->uri->segment(3));
        $pengajuan_list = $this->Proposal_pengajuan_model->get_all($filters, $per_page, ($page - 1) * $per_page);

        $kelas_list = array();
        foreach ($this->Proposal_model->get_all($id_tahun) as $s) {
            if (!empty($s->kelas) && !in_array($s->kelas, $kelas_list)) {
                $kelas_list[] = $s->kelas;
            }
        }
        sort($kelas_list);

        $data = array(
            'title' => 'Plot Pembimbing Proposal TA',
            'page_title' => 'Plot Pembimbing Proposal TA',
            'breadcrumb' => array(
                array('label' => 'Proposal TA', 'url' => '#'),
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

        $data['content'] = $this->load->view('sekprodi/proposal_plot_pembimbing/index', $data, true);
        $this->load->view('layouts/main', $data);
    }

    public function detail($id) {
        $pengajuan = $this->Proposal_pengajuan_model->get_by_id($id);
        if (!$pengajuan) {
            $this->session->set_flashdata('error', 'Data pengajuan judul tidak ditemukan.');
            redirect('Proposal_plot_pembimbing');
        }

        $data = array(
            'title' => 'Plot Pembimbing Proposal TA',
            'page_title' => 'Plot Pembimbing Proposal TA',
            'breadcrumb' => array(
                array('label' => 'Proposal TA', 'url' => '#'),
                array('label' => 'Plot Pembimbing', 'url' => site_url('Proposal_plot_pembimbing')),
                array('label' => 'Detail', 'active' => true)
            ),
            'sidebar_menu' => generate_sidebar_menu('sekprodi'),
            'pengajuan' => $pengajuan,
            'pembimbing_list' => $this->Proposal_pembimbing_model->get_allowed()
        );

        $data['content'] = $this->load->view('sekprodi/proposal_plot_pembimbing/detail', $data, true);
        $this->load->view('layouts/main', $data);
    }

    public function proses($id) {
        if ($this->input->method() !== 'post') {
            redirect('Proposal_plot_pembimbing/detail/' . $id);
        }

        $pengajuan = $this->Proposal_pengajuan_model->get_by_id($id);
        if (!$pengajuan) {
            $this->session->set_flashdata('error', 'Data pengajuan judul tidak ditemukan.');
            redirect('Proposal_plot_pembimbing');
        }

        $this->form_validation->set_rules('id_pembimbing', 'Pembimbing', 'required|integer|greater_than[0]');
        if ($this->form_validation->run() == FALSE) {
            $this->session->set_flashdata('error', validation_errors());
            redirect('Proposal_plot_pembimbing/detail/' . $id);
        }

        $hasil = $this->Proposal_pengajuan_model->plot_pembimbing($pengajuan, $this->input->post('id_pembimbing'));
        if ($hasil['ok']) {
            proposal_notify('dosen', $this->input->post('id_pembimbing'), 'Pembimbing Proposal TA',
                'Anda ditetapkan sebagai pembimbing Proposal TA untuk ' . $pengajuan->nama_mahasiswa . '.', site_url('Ajuan_judul_proposal'));
            $this->session->set_flashdata('success', $hasil['message']);
            redirect('Proposal_plot_pembimbing');
        }

        $this->session->set_flashdata('error', $hasil['message']);
        redirect('Proposal_plot_pembimbing/detail/' . $id);
    }
}
