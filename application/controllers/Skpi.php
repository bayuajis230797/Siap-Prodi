<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Manajemen SKPI — role sekprodi
 */
class Skpi extends MY_Controller {

    public function __construct() {
        parent::__construct();
        $this->check_role('sekprodi');
        $this->load->model('Skpi_periode_model');
        $this->load->model('Skpi_peserta_model');
        $this->load->model('Skpi_jenis_berkas_model');
        $this->load->model('Tahun_akademik_model');
        $this->load->helper('menu');
        $this->load->library('form_validation');
    }

    public function index() {
        $status = $this->input->get('status');
        $list = $this->Skpi_periode_model->get_all($status ?: null);
        foreach ($list as $row) {
            $row->jumlah_peserta = $this->Skpi_periode_model->count_peserta($row->id);
            $row->jumlah_jenis = $this->Skpi_periode_model->count_jenis_berkas($row->id);
            $row->deadline_lewat = $this->Skpi_periode_model->is_deadline_lewat($row);
        }
        $data = array(
            'title' => 'SKPI',
            'page_title' => 'Kelola Periode SKPI',
            'breadcrumb' => array(array('label' => 'SKPI', 'active' => true)),
            'sidebar_menu' => generate_sidebar_menu('sekprodi'),
            'list' => $list,
            'filter_status' => $status,
        );
        $data['content'] = $this->load->view('sekprodi/skpi/index', $data, true);
        $this->load->view('layouts/main', $data);
    }

    public function create() {
        $data = array(
            'title' => 'Tambah Periode SKPI',
            'page_title' => 'Tambah Periode SKPI',
            'breadcrumb' => array(
                array('label' => 'SKPI', 'url' => site_url('Skpi')),
                array('label' => 'Tambah', 'active' => true),
            ),
            'sidebar_menu' => generate_sidebar_menu('sekprodi'),
            'tahun_aktif' => $this->Tahun_akademik_model->get_aktif(),
            'periode' => null,
        );
        $data['content'] = $this->load->view('sekprodi/skpi/form', $data, true);
        $this->load->view('layouts/main', $data);
    }

    public function store() {
        $this->_validate_periode_form();
        if ($this->form_validation->run() === FALSE) {
            $this->create();
            return;
        }
        $id = $this->Skpi_periode_model->create($this->_periode_post_data());
        if ($id) {
            $this->session->set_flashdata('success', 'Periode SKPI berhasil dibuat. Atur peserta dan jenis berkas di halaman detail.');
            redirect('Skpi/detail/' . $id);
        }
        $this->session->set_flashdata('error', 'Gagal menyimpan data.');
        redirect('Skpi/create');
    }

    public function edit($id) {
        $periode = $this->Skpi_periode_model->get_by_id($id);
        if (!$periode) {
            $this->session->set_flashdata('error', 'Data tidak ditemukan.');
            redirect('Skpi');
        }
        $data = array(
            'title' => 'Edit Periode SKPI',
            'page_title' => 'Edit Periode SKPI',
            'breadcrumb' => array(
                array('label' => 'SKPI', 'url' => site_url('Skpi')),
                array('label' => 'Edit', 'active' => true),
            ),
            'sidebar_menu' => generate_sidebar_menu('sekprodi'),
            'tahun_aktif' => $this->Tahun_akademik_model->get_aktif(),
            'periode' => $periode,
        );
        $data['content'] = $this->load->view('sekprodi/skpi/form', $data, true);
        $this->load->view('layouts/main', $data);
    }

    public function update($id) {
        $periode = $this->Skpi_periode_model->get_by_id($id);
        if (!$periode) {
            $this->session->set_flashdata('error', 'Data tidak ditemukan.');
            redirect('Skpi');
        }
        $this->_validate_periode_form();
        if ($this->form_validation->run() === FALSE) {
            $this->edit($id);
            return;
        }
        $data = $this->_periode_post_data();
        unset($data['created_by']);
        $this->Skpi_periode_model->update($id, $data);
        $this->session->set_flashdata('success', 'Periode SKPI berhasil diperbarui.');
        redirect('Skpi/detail/' . $id);
    }

    public function detail($id) {
        $periode = $this->Skpi_periode_model->get_by_id($id);
        if (!$periode) {
            $this->session->set_flashdata('error', 'Data tidak ditemukan.');
            redirect('Skpi');
        }
        $peserta_ids = $this->Skpi_peserta_model->get_ids_by_periode($id);
        $jenis_list = $this->Skpi_jenis_berkas_model->get_by_periode($id, false);
        $data = array(
            'title' => 'Detail SKPI',
            'page_title' => $periode->nama_periode,
            'breadcrumb' => array(
                array('label' => 'SKPI', 'url' => site_url('Skpi')),
                array('label' => 'Detail', 'active' => true),
            ),
            'sidebar_menu' => generate_sidebar_menu('sekprodi'),
            'periode' => $periode,
            'peserta_ids' => $peserta_ids,
            'jenis_list' => $jenis_list,
            'deadline_lewat' => $this->Skpi_periode_model->is_deadline_lewat($periode),
            'mahasiswa_by_kelas' => $this->Skpi_periode_model->get_mahasiswa_aktif_grouped_by_kelas(),
        );
        $data['content'] = $this->load->view('sekprodi/skpi/detail', $data, true);
        $this->load->view('layouts/main', $data);
    }

    public function simpan_peserta($id) {
        $periode = $this->Skpi_periode_model->get_by_id($id);
        if (!$periode) {
            $this->session->set_flashdata('error', 'Data tidak ditemukan.');
            redirect('Skpi');
        }
        $mhs_ids = $this->input->post('id_mahasiswa');
        $mhs_ids = is_array($mhs_ids) ? array_map('intval', $mhs_ids) : array();
        $this->Skpi_peserta_model->sync_peserta($id, $mhs_ids);
        $this->session->set_flashdata('success', 'Daftar mahasiswa SKPI berhasil disimpan (' . count($mhs_ids) . ' orang).');
        redirect('Skpi/detail/' . $id);
    }

    public function tambah_jenis($id) {
        $periode = $this->Skpi_periode_model->get_by_id($id);
        if (!$periode) {
            $this->session->set_flashdata('error', 'Data tidak ditemukan.');
            redirect('Skpi');
        }
        $nama = trim($this->input->post('nama_berkas'));
        if ($nama === '') {
            $this->session->set_flashdata('error', 'Nama jenis berkas wajib diisi.');
            redirect('Skpi/detail/' . $id);
        }
        $this->Skpi_jenis_berkas_model->create(array(
            'id_skpi_periode' => (int) $id,
            'nama_berkas' => $nama,
            'petunjuk' => $this->input->post('petunjuk'),
            'urutan' => (int) $this->input->post('urutan'),
            'aktif' => 1,
        ));
        $this->session->set_flashdata('success', 'Jenis berkas berhasil ditambahkan.');
        redirect('Skpi/detail/' . $id);
    }

    public function hapus_jenis($id_jenis) {
        $jenis = $this->Skpi_jenis_berkas_model->get_by_id($id_jenis);
        if ($jenis) {
            $this->Skpi_jenis_berkas_model->delete($id_jenis);
            $this->session->set_flashdata('success', 'Jenis berkas dihapus.');
            redirect('Skpi/detail/' . $jenis->id_skpi_periode);
        }
        redirect('Skpi');
    }

    public function update_status($id) {
        $periode = $this->Skpi_periode_model->get_by_id($id);
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
            $jumlah = $this->Skpi_periode_model->count_jenis_berkas($id);
            if ($jumlah < 1) {
                echo json_encode(array('success' => false, 'message' => 'Tambahkan minimal satu jenis berkas sebelum mengaktifkan.'));
                return;
            }
            if ($this->Skpi_periode_model->count_peserta($id) < 1) {
                echo json_encode(array('success' => false, 'message' => 'Pilih minimal satu mahasiswa peserta SKPI.'));
                return;
            }
        }
        $this->Skpi_periode_model->update($id, array('status' => $status));
        echo json_encode(array('success' => true, 'message' => 'Status diperbarui.'));
    }

    private function _validate_periode_form() {
        $this->form_validation->set_rules('nama_periode', 'Nama Periode', 'required|trim');
        $this->form_validation->set_rules('deadline_pengumpulan', 'Deadline Pengumpulan', 'required|trim');
    }

    private function _periode_post_data() {
        $tahun = $this->Tahun_akademik_model->get_aktif();
        return array(
            'nama_periode' => $this->input->post('nama_periode'),
            'deskripsi' => $this->input->post('deskripsi') ?: null,
            'tanggal_mulai' => $this->input->post('tanggal_mulai') ?: null,
            'tanggal_selesai' => $this->input->post('tanggal_selesai') ?: null,
            'deadline_pengumpulan' => $this->input->post('deadline_pengumpulan'),
            'id_tahun_akademik' => $tahun ? $tahun->id : null,
            'created_by' => $this->session->userdata('user_id'),
        );
    }
}
