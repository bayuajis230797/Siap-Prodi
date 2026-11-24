<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Pkm extends MY_Controller {

    public function __construct() {
        parent::__construct();
        $this->check_role('sekprodi');
        $this->load->model('Pkm_periode_model');
        $this->load->model('Pkm_jenis_lomba_model');
        $this->load->model('Tahun_akademik_model');
        $this->load->helper('menu');
        $this->load->library('form_validation');
    }

    public function index() {
        $status = $this->input->get('status');
        $list = $this->Pkm_periode_model->get_all($status ?: null);
        foreach ($list as $row) {
            $row->jumlah_jenis = $this->Pkm_periode_model->count_jenis_lomba($row->id);
            $row->jumlah_pengajuan = $this->Pkm_periode_model->count_pengajuan($row->id);
            $row->deadline_lewat = $this->Pkm_periode_model->is_deadline_lewat($row);
        }
        $data = array(
            'title' => 'PKM',
            'page_title' => 'Kelola Periode PKM',
            'breadcrumb' => array(array('label' => 'PKM', 'active' => true)),
            'sidebar_menu' => generate_sidebar_menu('sekprodi'),
            'list' => $list,
            'filter_status' => $status,
        );
        $data['content'] = $this->load->view('sekprodi/pkm/index', $data, true);
        $this->load->view('layouts/main', $data);
    }

    public function create() {
        $data = array(
            'title' => 'Tambah Periode PKM',
            'page_title' => 'Tambah Periode PKM',
            'breadcrumb' => array(
                array('label' => 'PKM', 'url' => site_url('Pkm')),
                array('label' => 'Tambah', 'active' => true),
            ),
            'sidebar_menu' => generate_sidebar_menu('sekprodi'),
            'tahun_aktif' => $this->Tahun_akademik_model->get_aktif(),
            'periode' => null,
        );
        $data['content'] = $this->load->view('sekprodi/pkm/form', $data, true);
        $this->load->view('layouts/main', $data);
    }

    public function store() {
        $this->_validate_periode_form();
        if ($this->form_validation->run() === FALSE) {
            $this->create();
            return;
        }
        $id = $this->Pkm_periode_model->create($this->_periode_post_data());
        if ($id) {
            $this->session->set_flashdata('success', 'Periode PKM berhasil dibuat. Atur jenis lomba di halaman detail.');
            redirect('Pkm/detail/' . $id);
        }
        $this->session->set_flashdata('error', 'Gagal menyimpan data.');
        redirect('Pkm/create');
    }

    public function edit($id) {
        $periode = $this->Pkm_periode_model->get_by_id($id);
        if (!$periode) {
            $this->session->set_flashdata('error', 'Data tidak ditemukan.');
            redirect('Pkm');
        }
        $data = array(
            'title' => 'Edit Periode PKM',
            'page_title' => 'Edit Periode PKM',
            'breadcrumb' => array(
                array('label' => 'PKM', 'url' => site_url('Pkm')),
                array('label' => 'Edit', 'active' => true),
            ),
            'sidebar_menu' => generate_sidebar_menu('sekprodi'),
            'tahun_aktif' => $this->Tahun_akademik_model->get_aktif(),
            'periode' => $periode,
        );
        $data['content'] = $this->load->view('sekprodi/pkm/form', $data, true);
        $this->load->view('layouts/main', $data);
    }

    public function update($id) {
        $periode = $this->Pkm_periode_model->get_by_id($id);
        if (!$periode) {
            $this->session->set_flashdata('error', 'Data tidak ditemukan.');
            redirect('Pkm');
        }
        $this->_validate_periode_form();
        if ($this->form_validation->run() === FALSE) {
            $this->edit($id);
            return;
        }
        $data = $this->_periode_post_data();
        unset($data['created_by']);
        $this->Pkm_periode_model->update($id, $data);
        $this->session->set_flashdata('success', 'Periode PKM berhasil diperbarui.');
        redirect('Pkm/detail/' . $id);
    }

    public function detail($id) {
        $periode = $this->Pkm_periode_model->get_by_id($id);
        if (!$periode) {
            $this->session->set_flashdata('error', 'Data tidak ditemukan.');
            redirect('Pkm');
        }
        $jenis_list = $this->Pkm_jenis_lomba_model->get_by_periode($id, false);
        $data = array(
            'title' => 'Detail PKM',
            'page_title' => $periode->nama_periode,
            'breadcrumb' => array(
                array('label' => 'PKM', 'url' => site_url('Pkm')),
                array('label' => 'Detail', 'active' => true),
            ),
            'sidebar_menu' => generate_sidebar_menu('sekprodi'),
            'periode' => $periode,
            'jenis_list' => $jenis_list,
            'deadline_lewat' => $this->Pkm_periode_model->is_deadline_lewat($periode),
        );
        $data['content'] = $this->load->view('sekprodi/pkm/detail', $data, true);
        $this->load->view('layouts/main', $data);
    }

    public function tambah_jenis($id) {
        $periode = $this->Pkm_periode_model->get_by_id($id);
        if (!$periode) {
            $this->session->set_flashdata('error', 'Data tidak ditemukan.');
            redirect('Pkm');
        }
        $nama = trim($this->input->post('nama_jenis'));
        if ($nama === '') {
            $this->session->set_flashdata('error', 'Nama jenis lomba wajib diisi.');
            redirect('Pkm/detail/' . $id);
        }
        $min = max(0, (int) $this->input->post('min_anggota'));
        $max = max($min, (int) $this->input->post('max_anggota'));
        $this->Pkm_jenis_lomba_model->create(array(
            'id_pkm_periode' => (int) $id,
            'nama_jenis' => $nama,
            'deskripsi' => $this->input->post('deskripsi'),
            'min_anggota' => $min,
            'max_anggota' => $max,
            'urutan' => (int) $this->input->post('urutan'),
            'aktif' => 1,
        ));
        $this->session->set_flashdata('success', 'Jenis lomba berhasil ditambahkan.');
        redirect('Pkm/detail/' . $id);
    }

    public function hapus_jenis($id_jenis) {
        $jenis = $this->Pkm_jenis_lomba_model->get_by_id($id_jenis);
        if ($jenis) {
            $this->Pkm_jenis_lomba_model->delete($id_jenis);
            $this->session->set_flashdata('success', 'Jenis lomba dihapus.');
            redirect('Pkm/detail/' . $jenis->id_pkm_periode);
        }
        redirect('Pkm');
    }

    public function update_status($id) {
        $periode = $this->Pkm_periode_model->get_by_id($id);
        if (!$periode) {
            echo json_encode(array('success' => false, 'message' => 'Data tidak ditemukan.'));
            return;
        }
        $status = $this->input->post('status');
        if (!in_array($status, array('draft', 'aktif', 'selesai', 'dibatalkan'), true)) {
            echo json_encode(array('success' => false, 'message' => 'Status tidak valid.'));
            return;
        }
        if ($status === 'aktif') {
            if ($this->Pkm_periode_model->count_jenis_lomba($id) < 1) {
                echo json_encode(array('success' => false, 'message' => 'Tambahkan minimal satu jenis lomba sebelum mengaktifkan.'));
                return;
            }
        }
        $this->Pkm_periode_model->update($id, array('status' => $status));
        echo json_encode(array('success' => true, 'message' => 'Status diperbarui.'));
    }

    private function _validate_periode_form() {
        $this->form_validation->set_rules('nama_periode', 'Nama Periode', 'required|trim');
        $this->form_validation->set_rules('deadline_pengajuan', 'Deadline Pengajuan', 'required|trim');
        $this->form_validation->set_rules('tanggal_selesai', 'Tanggal Selesai', 'required|trim');
    }

    private function _periode_post_data() {
        $tahun = $this->Tahun_akademik_model->get_aktif();
        return array(
            'nama_periode' => $this->input->post('nama_periode'),
            'deskripsi' => $this->input->post('deskripsi') ?: null,
            'tanggal_mulai' => $this->input->post('tanggal_mulai') ?: null,
            'tanggal_selesai' => $this->input->post('tanggal_selesai') ?: null,
            'deadline_pengajuan' => $this->input->post('deadline_pengajuan'),
            'id_tahun_akademik' => $tahun ? $tahun->id : null,
            'created_by' => $this->session->userdata('user_id'),
        );
    }
}
