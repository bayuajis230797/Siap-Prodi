<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Tagihan Berkas Sekprodi Controller
 * Mengelola tagihan berkas untuk mahasiswa
 */
class Tagihan_berkas_sekprodi extends MY_Controller {

    public function __construct() {
        parent::__construct();
        $this->check_role('sekprodi');
        
        $this->load->model('Tagihan_berkas_model');
        $this->load->model('Mahasiswa_model');
        $this->load->model('Kelas_model');
        $this->load->helper('menu');
        $this->load->library('form_validation');
        $this->load->helper('file');
    }

    /**
     * Index - Daftar tagihan
     */
    public function index() {
        $kelas_filter = $this->input->get('kelas');
        
        $data['title'] = 'Tagihan Berkas';
        $data['page_title'] = 'Tagihan Berkas';
        $data['breadcrumb'] = array(
            array('label' => 'Tagihan Berkas', 'active' => true)
        );
        $data['sidebar_menu'] = generate_sidebar_menu('sekprodi');
        
        // Get semua kelas untuk filter
        $data['kelas_list'] = $this->Kelas_model->get_all_kelas();
        $data['kelas_filter'] = $kelas_filter;
        
        // Get tagihan
        $data['tagihan_list'] = $this->Tagihan_berkas_model->get_all($kelas_filter, null);
        
        $data['content'] = $this->load->view('tagihan_berkas_sekprodi/index', $data, true);
        $this->load->view('layouts/main', $data);
    }

    /**
     * Create tagihan baru
     */
    public function create() {
        if ($this->input->method() === 'post') {
            $this->form_validation->set_rules('nama_tagihan', 'Nama Tagihan', 'required|trim');
            $this->form_validation->set_rules('deskripsi', 'Deskripsi', 'trim');
            $this->form_validation->set_rules('is_hardcopy', 'Jenis Berkas', 'required');
            $this->form_validation->set_rules('kelas_filter', 'Kelas', 'trim');
            $this->form_validation->set_rules('deadline', 'Deadline', 'trim');
            
            if ($this->form_validation->run() == FALSE) {
                $data['title'] = 'Buat Tagihan Berkas';
                $data['page_title'] = 'Buat Tagihan Berkas';
                $data['breadcrumb'] = array(
                    array('label' => 'Tagihan Berkas', 'url' => site_url('Tagihan_berkas_sekprodi')),
                    array('label' => 'Buat Baru', 'active' => true)
                );
                $data['sidebar_menu'] = generate_sidebar_menu('sekprodi');
                $data['kelas_list'] = $this->Kelas_model->get_all_kelas();
                
                $data['content'] = $this->load->view('tagihan_berkas_sekprodi/create', $data, true);
                $this->load->view('layouts/main', $data);
            } else {
                $data = array(
                    'nama_tagihan' => $this->input->post('nama_tagihan'),
                    'deskripsi' => $this->input->post('deskripsi'),
                    'is_hardcopy' => $this->input->post('is_hardcopy') == '1' ? 1 : 0,
                    'kelas_filter' => $this->input->post('kelas_filter') ? $this->input->post('kelas_filter') : null,
                    'deadline' => $this->input->post('deadline') ? $this->input->post('deadline') : null,
                    'is_aktif' => 1,
                    'created_by' => $this->session->userdata('user_id')
                );
                
                $id_tagihan = $this->Tagihan_berkas_model->create($data);
                
                // Generate detail untuk mahasiswa
                if ($data['kelas_filter']) {
                    $this->Tagihan_berkas_model->generate_detail_for_kelas($id_tagihan, $data['kelas_filter']);
                } else {
                    $this->Tagihan_berkas_model->generate_detail_for_all($id_tagihan);
                }
                
                $this->session->set_flashdata('success', 'Tagihan berkas berhasil dibuat.');
                redirect('Tagihan_berkas_sekprodi/detail/' . $id_tagihan);
            }
        } else {
            $data['title'] = 'Buat Tagihan Berkas';
            $data['page_title'] = 'Buat Tagihan Berkas';
            $data['breadcrumb'] = array(
                array('label' => 'Tagihan Berkas', 'url' => site_url('Tagihan_berkas_sekprodi')),
                array('label' => 'Buat Baru', 'active' => true)
            );
            $data['sidebar_menu'] = generate_sidebar_menu('sekprodi');
            $data['kelas_list'] = $this->Kelas_model->get_all_kelas();
            
            $data['content'] = $this->load->view('tagihan_berkas_sekprodi/create', $data, true);
            $this->load->view('layouts/main', $data);
        }
    }

    /**
     * Detail tagihan
     */
    public function detail($id) {
        $tagihan = $this->Tagihan_berkas_model->get_by_id($id);
        
        if (!$tagihan) {
            $this->session->set_flashdata('error', 'Tagihan tidak ditemukan.');
            redirect('Tagihan_berkas_sekprodi');
        }
        
        $kelas_filter = $this->input->get('kelas');
        
        $data['title'] = 'Detail Tagihan Berkas';
        $data['page_title'] = 'Detail Tagihan Berkas';
        $data['breadcrumb'] = array(
            array('label' => 'Tagihan Berkas', 'url' => site_url('Tagihan_berkas_sekprodi')),
            array('label' => 'Detail', 'active' => true)
        );
        $data['sidebar_menu'] = generate_sidebar_menu('sekprodi');
        
        $data['tagihan'] = $tagihan;
        $data['kelas_filter'] = $kelas_filter;
        
        // Get detail tagihan per mahasiswa
        $data['detail_list'] = $this->Tagihan_berkas_model->get_detail_by_tagihan($id, $kelas_filter);
        
        // Get statistik
        $data['statistik'] = $this->Tagihan_berkas_model->get_statistik($id);
        
        // Get kelas list untuk filter
        $data['kelas_list'] = $this->Kelas_model->get_all_kelas();
        
        $data['content'] = $this->load->view('tagihan_berkas_sekprodi/detail', $data, true);
        $this->load->view('layouts/main', $data);
    }

    /**
     * Update status hardcopy (AJAX) - Single update (legacy, tetap ada untuk kompatibilitas)
     */
    public function update_status_hardcopy() {
        // Set content type untuk JSON response
        $this->output->set_content_type('application/json');
        
        if ($this->input->method() !== 'post') {
            $this->output->set_status_header(405);
            echo json_encode(array('success' => false, 'message' => 'Method not allowed'));
            return;
        }
        
        $id_detail = $this->input->post('id_detail');
        $status = $this->input->post('status'); // 'sudah' atau 'belum'
        
        if (!$id_detail || !$status) {
            $this->output->set_status_header(400);
            echo json_encode(array('success' => false, 'message' => 'Parameter tidak lengkap'));
            return;
        }
        
        // Validasi status
        if (!in_array($status, array('sudah', 'belum'))) {
            $this->output->set_status_header(400);
            echo json_encode(array('success' => false, 'message' => 'Status tidak valid. Harus "sudah" atau "belum"'));
            return;
        }
        
        // Cek apakah detail tagihan ada
        $this->load->model('Tagihan_berkas_model');
        $detail = $this->db->get_where('tagihan_berkas_detail', array('id' => $id_detail))->row();
        
        if (!$detail) {
            $this->output->set_status_header(404);
            echo json_encode(array('success' => false, 'message' => 'Data detail tagihan tidak ditemukan'));
            return;
        }
        
        // Update status hardcopy
        $data = array('status_hardcopy' => $status);
        $result = $this->Tagihan_berkas_model->update_detail($id_detail, $data);
        
        if ($result) {
            // Update status juga jika hardcopy sudah
            if ($status == 'sudah') {
                $this->Tagihan_berkas_model->update_detail($id_detail, array('status' => 'sudah'));
            }
            echo json_encode(array('success' => true, 'message' => 'Status berhasil diupdate'));
        } else {
            $this->output->set_status_header(500);
            echo json_encode(array('success' => false, 'message' => 'Gagal update status. Silakan coba lagi.'));
        }
    }

    /**
     * Update status hardcopy batch (AJAX) - Update multiple sekaligus
     */
    public function update_status_hardcopy_batch() {
        // Set content type untuk JSON response
        $this->output->set_content_type('application/json');
        
        if ($this->input->method() !== 'post') {
            $this->output->set_status_header(405);
            echo json_encode(array('success' => false, 'message' => 'Method not allowed'));
            return;
        }
        
        // Ambil data POST - CodeIgniter otomatis membaca array dengan format checked_ids[0], checked_ids[1], dll
        $checked_ids = $this->input->post('checked_ids'); // Array ID yang dicentang (status = 'sudah')
        $unchecked_ids = $this->input->post('unchecked_ids'); // Array ID yang tidak dicentang (status = 'belum')
        
        // Jika null atau bukan array, coba ambil dari $_POST langsung
        if (!is_array($checked_ids) || empty($checked_ids)) {
            $checked_ids = array();
            // Cek apakah ada data di $_POST dengan format array
            if (isset($_POST['checked_ids']) && is_array($_POST['checked_ids'])) {
                $checked_ids = $_POST['checked_ids'];
            } else {
                // Cek format checked_ids[0], checked_ids[1], dll
                foreach ($_POST as $key => $value) {
                    if (preg_match('/^checked_ids\[(\d+)\]$/', $key, $matches)) {
                        $checked_ids[] = (int) $value;
                    }
                }
            }
        }
        
        if (!is_array($unchecked_ids) || empty($unchecked_ids)) {
            $unchecked_ids = array();
            // Cek apakah ada data di $_POST dengan format array
            if (isset($_POST['unchecked_ids']) && is_array($_POST['unchecked_ids'])) {
                $unchecked_ids = $_POST['unchecked_ids'];
            } else {
                // Cek format unchecked_ids[0], unchecked_ids[1], dll
                foreach ($_POST as $key => $value) {
                    if (preg_match('/^unchecked_ids\[(\d+)\]$/', $key, $matches)) {
                        $unchecked_ids[] = (int) $value;
                    }
                }
            }
        }
        
        // Pastikan adalah array dan filter hanya integer
        $checked_ids = array_filter(array_map('intval', $checked_ids), function($id) {
            return $id > 0;
        });
        $unchecked_ids = array_filter(array_map('intval', $unchecked_ids), function($id) {
            return $id > 0;
        });
        
        // Log untuk debugging
        log_message('debug', 'Update batch - Checked IDs: ' . json_encode($checked_ids));
        log_message('debug', 'Update batch - Unchecked IDs: ' . json_encode($unchecked_ids));
        
        if (empty($checked_ids) && empty($unchecked_ids)) {
            $this->output->set_status_header(400);
            echo json_encode(array('success' => false, 'message' => 'Tidak ada data yang akan diupdate'));
            return;
        }
        
        $this->load->model('Tagihan_berkas_model');
        $updated = 0;
        $errors = array();
        
        // Update yang dicentang (status = 'sudah')
        foreach ($checked_ids as $id_detail) {
            $id_detail = (int) $id_detail;
            if ($id_detail > 0) {
                // Cek apakah detail tagihan ada
                $detail = $this->db->get_where('tagihan_berkas_detail', array('id' => $id_detail))->row();
                if ($detail) {
                    $data = array(
                        'status_hardcopy' => 'sudah',
                        'status' => 'sudah' // Update status juga
                    );
                    if ($this->Tagihan_berkas_model->update_detail($id_detail, $data)) {
                        $updated++;
                    } else {
                        $errors[] = 'Gagal update ID: ' . $id_detail;
                    }
                }
            }
        }
        
        // Update yang tidak dicentang (status = 'belum')
        foreach ($unchecked_ids as $id_detail) {
            $id_detail = (int) $id_detail;
            if ($id_detail > 0) {
                // Cek apakah detail tagihan ada
                $detail = $this->db->get_where('tagihan_berkas_detail', array('id' => $id_detail))->row();
                if ($detail) {
                    $data = array('status_hardcopy' => 'belum');
                    if ($this->Tagihan_berkas_model->update_detail($id_detail, $data)) {
                        $updated++;
                    } else {
                        $errors[] = 'Gagal update ID: ' . $id_detail;
                    }
                }
            }
        }
        
        if ($updated > 0) {
            $message = 'Status berhasil diupdate untuk ' . $updated . ' mahasiswa.';
            if (!empty($errors)) {
                $message .= ' Beberapa data gagal: ' . implode(', ', $errors);
            }
            echo json_encode(array('success' => true, 'message' => $message, 'updated' => $updated));
        } else {
            $this->output->set_status_header(500);
            $message = 'Gagal update status.';
            if (!empty($errors)) {
                $message .= ' ' . implode(', ', $errors);
            }
            echo json_encode(array('success' => false, 'message' => $message));
        }
    }

    /**
     * Update status softfile (AJAX)
     */
    public function update_status_softfile() {
        if ($this->input->method() !== 'post') {
            $this->output->set_status_header(405);
            echo json_encode(array('success' => false, 'message' => 'Method not allowed'));
            return;
        }
        
        $id_detail = $this->input->post('id_detail');
        $status = $this->input->post('status'); // 'sudah' atau 'belum'
        
        if (!$id_detail || !$status) {
            $this->output->set_status_header(400);
            echo json_encode(array('success' => false, 'message' => 'Parameter tidak lengkap'));
            return;
        }
        
        $data = array('status' => $status);
        $result = $this->Tagihan_berkas_model->update_detail($id_detail, $data);
        
        if ($result) {
            echo json_encode(array('success' => true, 'message' => 'Status berhasil diupdate'));
        } else {
            $this->output->set_status_header(500);
            echo json_encode(array('success' => false, 'message' => 'Gagal update status'));
        }
    }

    /**
     * Edit tagihan
     */
    public function edit($id) {
        $tagihan = $this->Tagihan_berkas_model->get_by_id($id);
        
        if (!$tagihan) {
            $this->session->set_flashdata('error', 'Tagihan tidak ditemukan.');
            redirect('Tagihan_berkas_sekprodi');
        }
        
        if ($this->input->method() === 'post') {
            $this->form_validation->set_rules('nama_tagihan', 'Nama Tagihan', 'required|trim');
            $this->form_validation->set_rules('deskripsi', 'Deskripsi', 'trim');
            $this->form_validation->set_rules('is_hardcopy', 'Jenis Berkas', 'required');
            $this->form_validation->set_rules('kelas_filter', 'Kelas', 'trim');
            $this->form_validation->set_rules('deadline', 'Deadline', 'trim');
            $this->form_validation->set_rules('is_aktif', 'Status Aktif', 'required');
            
            if ($this->form_validation->run() == FALSE) {
                $data['title'] = 'Edit Tagihan Berkas';
                $data['page_title'] = 'Edit Tagihan Berkas';
                $data['breadcrumb'] = array(
                    array('label' => 'Tagihan Berkas', 'url' => site_url('Tagihan_berkas_sekprodi')),
                    array('label' => 'Edit', 'active' => true)
                );
                $data['sidebar_menu'] = generate_sidebar_menu('sekprodi');
                $data['tagihan'] = $tagihan;
                $data['kelas_list'] = $this->Kelas_model->get_all_kelas();
                
                $data['content'] = $this->load->view('tagihan_berkas_sekprodi/edit', $data, true);
                $this->load->view('layouts/main', $data);
            } else {
                $data = array(
                    'nama_tagihan' => $this->input->post('nama_tagihan'),
                    'deskripsi' => $this->input->post('deskripsi'),
                    'is_hardcopy' => $this->input->post('is_hardcopy') == '1' ? 1 : 0,
                    'kelas_filter' => $this->input->post('kelas_filter') ? $this->input->post('kelas_filter') : null,
                    'deadline' => $this->input->post('deadline') ? $this->input->post('deadline') : null,
                    'is_aktif' => $this->input->post('is_aktif') == '1' ? 1 : 0
                );
                
                $this->Tagihan_berkas_model->update($id, $data);
                
                $this->session->set_flashdata('success', 'Tagihan berkas berhasil diupdate.');
                redirect('Tagihan_berkas_sekprodi/detail/' . $id);
            }
        } else {
            $data['title'] = 'Edit Tagihan Berkas';
            $data['page_title'] = 'Edit Tagihan Berkas';
            $data['breadcrumb'] = array(
                array('label' => 'Tagihan Berkas', 'url' => site_url('Tagihan_berkas_sekprodi')),
                array('label' => 'Edit', 'active' => true)
            );
            $data['sidebar_menu'] = generate_sidebar_menu('sekprodi');
            $data['tagihan'] = $tagihan;
            $data['kelas_list'] = $this->Kelas_model->get_all_kelas();
            
            $data['content'] = $this->load->view('tagihan_berkas_sekprodi/edit', $data, true);
            $this->load->view('layouts/main', $data);
        }
    }

    /**
     * Delete tagihan
     */
    public function delete($id) {
        $tagihan = $this->Tagihan_berkas_model->get_by_id($id);
        
        if (!$tagihan) {
            $this->session->set_flashdata('error', 'Tagihan tidak ditemukan.');
            redirect('Tagihan_berkas_sekprodi');
        }
        
        $this->Tagihan_berkas_model->delete($id);
        $this->session->set_flashdata('success', 'Tagihan berkas berhasil dihapus.');
        redirect('Tagihan_berkas_sekprodi');
    }
}

