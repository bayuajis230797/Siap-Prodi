<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Tahun_akademik_lihat Controller (semua peran yang sudah login)
 * Memilih tahun akademik yang DILIHAT pada sesi ini (mode lihat, hanya baca).
 * Tidak mengubah tahun akademik resmi, kelas mahasiswa, maupun sesi pengguna lain.
 */
class Tahun_akademik_lihat extends MY_Controller {

    public function __construct() {
        parent::__construct();
        $this->load->model('Tahun_akademik_model');
    }

    /** Pilih tahun akademik (POST id_tahun_akademik). Memilih tahun akademik resmi = keluar mode lihat. */
    public function pilih() {
        if ($this->input->method() !== 'post') {
            redirect('Halaman_utama');
        }
        $id = (int) $this->input->post('id_tahun_akademik');
        if (!$this->Tahun_akademik_model->set_tahun_lihat($id)) {
            $this->session->set_flashdata('error', 'Tahun akademik tidak ditemukan.');
            $this->kembali();
        }
        if ($this->Tahun_akademik_model->sedang_mode_lihat()) {
            $ta = $this->Tahun_akademik_model->get_tahun_lihat();
            $this->session->set_flashdata('success', 'Mode lihat: ' . $ta->tahun_akademik . ' ' . ucfirst($ta->semester) . ' (hanya baca).');
        } else {
            $this->session->set_flashdata('success', 'Kembali ke tahun akademik berjalan.');
        }
        $this->kembali();
    }

    /** Keluar dari mode lihat (POST). */
    public function keluar() {
        if ($this->input->method() !== 'post') {
            redirect('Halaman_utama');
        }
        $this->Tahun_akademik_model->keluar_mode_lihat();
        $this->session->set_flashdata('success', 'Kembali ke tahun akademik berjalan.');
        $this->kembali();
    }

    /** Kembali ke halaman sebelumnya (hanya bila berasal dari situs ini), selain itu Halaman Utama. */
    private function kembali() {
        $ref = (string) $this->input->server('HTTP_REFERER');
        if ($ref !== '' && strpos($ref, base_url()) === 0 && strpos($ref, 'Tahun_akademik_lihat') === false) {
            redirect($ref);
        }
        redirect('Halaman_utama');
    }
}
