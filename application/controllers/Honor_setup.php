<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Honor Setup Controller
 * Mengelola besaran honor (UTS, UAS, Skripsi)
 */
class Honor_setup extends MY_Controller {

    public function __construct() {
        parent::__construct();
        $this->check_role(array('admin', 'sekprodi'));
        $this->load->model('Honor_setup_model');
        $this->load->model('Tahun_akademik_model');
        $this->load->library('form_validation');
    }

    /**
     * Halaman utama pengaturan honor
     */
    public function index() {
        $tahun_aktif = $this->Tahun_akademik_model->get_aktif();
        $id_tahun_akademik = $tahun_aktif ? $tahun_aktif->id : null;

        $items_map = $this->get_honor_items();

        if ($this->input->method() === 'post') {
            $this->validate_form($items_map);

            if ($this->form_validation->run() === FALSE) {
                $this->session->set_flashdata('error', validation_errors());
                redirect('Honor_setup');
            }

            $input = $this->input->post('honor');
            if (is_array($input)) {
                foreach ($items_map as $category) {
                    foreach ($category['items'] as $key => $label) {
                        if (!array_key_exists($key, $input)) {
                            continue;
                        }
                        // Ambil nilai input dan bersihkan
                        $raw_value = trim($input[$key]);
                        
                        // Hapus semua karakter non-numeric kecuali titik dan koma
                        $raw_value = preg_replace('/[^0-9.,]/', '', $raw_value);
                        
                        // Jika kosong, set ke 0
                        if (empty($raw_value)) {
                            $besaran = 0;
                        } else {
                            // Ganti koma dengan titik untuk format desimal
                            $raw_value = str_replace(',', '.', $raw_value);
                            
                            // Hapus semua titik (pemisah ribuan), karena kita tidak menggunakan pemisah ribuan
                            // Jika ada titik, cek apakah itu desimal (hanya 1-2 digit setelah titik)
                            $parts = explode('.', $raw_value);
                            if (count($parts) == 2 && strlen($parts[1]) <= 2) {
                                // Ada desimal dengan maksimal 2 digit
                                $raw_value = $parts[0] . '.' . $parts[1];
                            } else {
                                // Hapus semua titik (pemisah ribuan)
                                $raw_value = str_replace('.', '', $raw_value);
                            }
                            
                            // Konversi ke float
                            $besaran = is_numeric($raw_value) ? (float)$raw_value : 0;
                            
                            // Pastikan nilai tidak negatif
                            if ($besaran < 0) {
                                $besaran = 0;
                            }
                        }
                        
                        $this->Honor_setup_model->save($key, $besaran, null, $id_tahun_akademik);
                    }
                }
            }

            $this->session->set_flashdata('success', 'Besaran honor berhasil diperbarui.');
            redirect('Honor_setup');
        }

        // Ambil nilai saat ini
        foreach ($items_map as $category_key => $category) {
            foreach ($category['items'] as $item_key => $label) {
                $items_map[$category_key]['items'][$item_key] = array(
                    'label' => $label,
                    'value' => $this->Honor_setup_model->get_besaran($item_key, $id_tahun_akademik)
                );
            }
        }

        $data = array(
            'title' => 'Pengaturan Honor',
            'page_title' => 'Pengaturan Besaran Honor',
            'breadcrumb' => array(
                array('label' => 'Honor', 'active' => true),
                array('label' => 'Pengaturan Besaran', 'active' => true)
            ),
            'sidebar_menu' => generate_sidebar_menu(get_user_role()),
            'tahun_aktif' => $tahun_aktif,
            'items_map' => $items_map
        );

        $data['content'] = $this->load->view('honor_setup/index', $data, true);
        $this->load->view('layouts/main', $data);
    }

    /**
     * Aturan validasi form
     */
    private function validate_form($items_map) {
        foreach ($items_map as $category) {
            foreach ($category['items'] as $key => $label) {
                $this->form_validation->set_rules(
                    "honor[{$key}]",
                    $label,
                    'required|greater_than_equal_to[0]',
                    array(
                        'required' => '%s wajib diisi.',
                        'greater_than_equal_to' => '%s tidak boleh negatif.'
                    )
                );
            }
        }
    }

    /**
     * Daftar item honor
     */
    private function get_honor_items() {
        return array(
            'uts' => array(
                'title' => 'Honor UTS',
                'items' => array(
                    'pembuatan_soal_uts' => 'Honor Pembuatan Soal',
                    'koreksi_uts' => 'Honor Koreksi',
                    'pengawas_uts' => 'Honor Pengawas'
                )
            ),
            'uas' => array(
                'title' => 'Honor UAS',
                'items' => array(
                    'pembuatan_soal_uas' => 'Honor Pembuatan Soal',
                    'koreksi_uas' => 'Honor Koreksi',
                    'pengawas_uas' => 'Honor Pengawas'
                )
            ),
            'skripsi' => array(
                'title' => 'Honor Skripsi',
                'items' => array(
                    'pembimbing_1_skripsi' => 'Pembimbing 1',
                    'pembimbing_2_skripsi' => 'Pembimbing 2',
                    'ketua_penguji_skripsi' => 'Ketua Penguji',
                    'anggota_penguji_1_skripsi' => 'Anggota Penguji 1',
                    'anggota_penguji_2_skripsi' => 'Anggota Penguji 2'
                )
            ),
            'proposal' => array(
                'title' => 'Honor Proposal TA',
                'items' => array(
                    'pembimbing_proposal' => 'Pembimbing (per mahasiswa)',
                    'penguji_1_proposal' => 'Penguji 1 (per sidang)',
                    'penguji_2_proposal' => 'Penguji 2 (per sidang)'
                )
            ),
            'pkl' => array(
                'title' => 'Honor PKL',
                'items' => array(
                    'pembimbing_pkl' => 'Honor Pembimbing PKL',
                    'penguji_pkl' => 'Honor Penguji PKL'
                )
            )
        );
    }
}





