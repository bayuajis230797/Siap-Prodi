<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Mapping MK Controller
 * Mengelola mapping dosen dengan mata kuliah aktif (untuk Sekprodi)
 */
class Mapping_mk extends MY_Controller {

    public function __construct() {
        parent::__construct();
        $this->check_role('sekprodi');
        $this->load->model('Mapping_dosen_mk_model');
        $this->load->model('Mata_kuliah_aktif_model');
        $this->load->model('Dosen_model');
        $this->load->model('Tahun_akademik_model');
    }

    /**
     * List semua mapping dengan pagination
     */
    public function index() {
        $this->load->library('pagination');
        
        // Ambil tahun akademik aktif
        $tahun_aktif = $this->Tahun_akademik_model->get_aktif();
        $id_tahun_akademik = $tahun_aktif ? $tahun_aktif->id : null;
        
        // Ambil parameter per_page dari GET, default 10
        $per_page = $this->input->get('per_page') ? (int)$this->input->get('per_page') : 10;
        $per_page = in_array($per_page, [10, 25, 50, 100]) ? $per_page : 10;
        
        // Pagination config
        $config['base_url'] = site_url('Mapping_mk/index');
        $config['total_rows'] = $this->Mapping_dosen_mk_model->count_all($id_tahun_akademik);
        $config['per_page'] = $per_page;
        $config['uri_segment'] = 3;
        
        // Tambahkan per_page ke query string
        if ($per_page != 10) {
            $config['suffix'] = '?per_page=' . $per_page;
            $config['first_url'] = site_url('Mapping_mk/index?per_page=' . $per_page);
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
        $config['cur_tag_open'] = '<li class="page-item active"><a class="page-link" href="#">';
        $config['cur_tag_close'] = '</a></li>';
        $config['num_tag_open'] = '<li class="page-item">';
        $config['num_tag_close'] = '</li>';
        $config['attributes'] = array('class' => 'page-link');
        
        $this->pagination->initialize($config);
        
        $page = ($this->uri->segment(3)) ? $this->uri->segment(3) : 1;
        $offset = ($page - 1) * $config['per_page'];
        
        $data['mapping'] = $this->Mapping_dosen_mk_model->get_all($id_tahun_akademik, $config['per_page'], $offset);
        $data['pagination'] = $this->pagination->create_links();
        $data['total_rows'] = $config['total_rows'];
        $data['tahun_aktif'] = $tahun_aktif;
        $data['per_page'] = $per_page;
        
        $data['title'] = 'Mapping MK';
        $data['page_title'] = 'Mapping Mata Kuliah - Dosen';
        $data['breadcrumb'] = array(
            array('label' => 'Mapping MK', 'active' => true)
        );
        $data['sidebar_menu'] = generate_sidebar_menu('sekprodi');
        $data['content'] = $this->load->view('mapping_mk/index', $data, true);
        $this->load->view('layouts/main', $data);
    }

    /**
     * Form tambah mapping
     */
    public function create() {
        // Ambil tahun akademik aktif
        $tahun_aktif = $this->Tahun_akademik_model->get_aktif();
        
        if (!$tahun_aktif) {
            $this->session->set_flashdata('error', 'Tidak ada tahun akademik aktif. Silakan aktifkan tahun akademik terlebih dahulu.');
            redirect('Mapping_mk');
        }

        if ($this->input->method() === 'post') {
            $this->form_validation->set_rules('id_mata_kuliah_aktif', 'Mata Kuliah', 'required|trim|numeric');
            $this->form_validation->set_rules('id_dosen', 'Dosen', 'required|trim|numeric');
            $this->form_validation->set_rules('kelas', 'Kelas', 'required|trim');

            if ($this->form_validation->run() == FALSE) {
                $data['mata_kuliah_aktif'] = $this->Mapping_dosen_mk_model->get_mata_kuliah_aktif_available($tahun_aktif->id);
                $data['dosen'] = $this->Dosen_model->get_all(1000, 0);
                $data['kelas_list'] = $this->Mapping_dosen_mk_model->get_all_kelas($tahun_aktif);
                if (empty($data['kelas_list'])) {
                    $data['kelas_list'] = $this->Mapping_dosen_mk_model->get_all_kelas(null);
                }
                $data['tahun_aktif'] = $tahun_aktif;
                $data['title'] = 'Tambah Mapping MK';
                $data['page_title'] = 'Tambah Mapping Mata Kuliah - Dosen';
                $data['breadcrumb'] = array(
                    array('label' => 'Mapping MK', 'url' => site_url('Mapping_mk')),
                    array('label' => 'Tambah', 'active' => true)
                );
                $data['sidebar_menu'] = generate_sidebar_menu('sekprodi');
                $data['content'] = $this->load->view('mapping_mk/create', $data, true);
                $this->load->view('layouts/main', $data);
            } else {
                $id_mata_kuliah_aktif = $this->input->post('id_mata_kuliah_aktif');
                $id_dosen = $this->input->post('id_dosen');
                $kelas = $this->input->post('kelas') ? $this->input->post('kelas') : null;

                // Cek apakah mapping sudah ada
                if ($this->Mapping_dosen_mk_model->mapping_exists($id_mata_kuliah_aktif, $id_dosen, $kelas)) {
                    $this->session->set_flashdata('error', 'Mapping dosen dengan mata kuliah dan kelas ini sudah ada.');
                    redirect('Mapping_mk/create');
                }

                // Ambil id_tahun_akademik dan id_kurikulum_detail dari mata_kuliah_aktif
                $this->load->model('Mata_kuliah_aktif_model');
                $mka = $this->Mata_kuliah_aktif_model->get_by_id($id_mata_kuliah_aktif);
                $id_tahun_akademik = $mka ? $mka->id_tahun_akademik : $tahun_aktif->id;
                $id_kurikulum_detail = $mka ? $mka->id_kurikulum_detail : null;

                $data_mapping = array(
                    'id_mata_kuliah_aktif' => $id_mata_kuliah_aktif,
                    'id_tahun_akademik' => $id_tahun_akademik,
                    'id_kurikulum_detail' => $id_kurikulum_detail,
                    'id_dosen' => $id_dosen,
                    'kelas' => $kelas
                );

                // Insert mapping
                $mapping_id = $this->Mapping_dosen_mk_model->create($data_mapping);
                
                if ($mapping_id) {
                    $this->session->set_flashdata('success', 'Mapping berhasil ditambahkan.');
                    redirect('Mapping_mk');
                } else {
                    $this->session->set_flashdata('error', 'Gagal menambahkan mapping.');
                    redirect('Mapping_mk/create');
                }
            }
        } else {
            // Untuk tab manual, tidak perlu load mata kuliah dulu (akan di-load via AJAX setelah kelas dipilih)
            $data['mata_kuliah_aktif'] = array(); // Kosongkan, akan di-load via AJAX
            $data['dosen'] = $this->Dosen_model->get_all(1000, 0);
            $data['kelas_list'] = $this->Mapping_dosen_mk_model->get_all_kelas($tahun_aktif);
            if (empty($data['kelas_list'])) {
                $data['kelas_list'] = $this->Mapping_dosen_mk_model->get_all_kelas(null);
            }
            $data['tahun_aktif'] = $tahun_aktif;
            $data['title'] = 'Tambah Mapping MK';
            $data['page_title'] = 'Tambah Mapping Mata Kuliah - Dosen';
            $data['breadcrumb'] = array(
                array('label' => 'Mapping MK', 'url' => site_url('Mapping_mk')),
                array('label' => 'Tambah', 'active' => true)
            );
            $data['sidebar_menu'] = generate_sidebar_menu('sekprodi');
            $data['content'] = $this->load->view('mapping_mk/create', $data, true);
            $this->load->view('layouts/main', $data);
        }
    }

    /**
     * Form edit mapping
     */
    public function edit($id) {
        $mapping = $this->Mapping_dosen_mk_model->get_by_id($id);
        
        if (!$mapping) {
            $this->session->set_flashdata('error', 'Mapping tidak ditemukan.');
            redirect('Mapping_mk');
        }

        // Ambil tahun akademik aktif
        $tahun_aktif = $this->Tahun_akademik_model->get_aktif();

        if ($this->input->method() === 'post') {
            $this->form_validation->set_rules('id_mata_kuliah_aktif', 'Mata Kuliah', 'required|trim|numeric');
            $this->form_validation->set_rules('id_dosen', 'Dosen', 'required|trim|numeric');
            $this->form_validation->set_rules('kelas', 'Kelas', 'trim');

            if ($this->form_validation->run() == FALSE) {
                $data['mapping'] = $mapping;
                $data['mata_kuliah_aktif'] = $this->Mapping_dosen_mk_model->get_all_mata_kuliah_aktif($tahun_aktif->id);
                $data['dosen'] = $this->Dosen_model->get_all(1000, 0);
                $data['kelas_list'] = $this->Mapping_dosen_mk_model->get_all_kelas($tahun_aktif);
                if (empty($data['kelas_list'])) {
                    $data['kelas_list'] = $this->Mapping_dosen_mk_model->get_all_kelas(null);
                }
                $data['tahun_aktif'] = $tahun_aktif;
                $data['title'] = 'Edit Mapping MK';
                $data['page_title'] = 'Edit Mapping Mata Kuliah - Dosen';
                $data['breadcrumb'] = array(
                    array('label' => 'Mapping MK', 'url' => site_url('Mapping_mk')),
                    array('label' => 'Edit', 'active' => true)
                );
                $data['sidebar_menu'] = generate_sidebar_menu('sekprodi');
                $data['content'] = $this->load->view('mapping_mk/edit', $data, true);
                $this->load->view('layouts/main', $data);
            } else {
                $id_mata_kuliah_aktif = $this->input->post('id_mata_kuliah_aktif');
                $id_dosen = $this->input->post('id_dosen');
                $kelas = $this->input->post('kelas') ? $this->input->post('kelas') : null;

                // Cek apakah mapping sudah ada (kecuali untuk mapping yang sedang diedit)
                if ($this->Mapping_dosen_mk_model->mapping_exists($id_mata_kuliah_aktif, $id_dosen, $kelas, $id)) {
                    $this->session->set_flashdata('error', 'Mapping dosen dengan mata kuliah dan kelas ini sudah ada.');
                    redirect('Mapping_mk/edit/' . $id);
                }

                // Ambil id_tahun_akademik dan id_kurikulum_detail dari mata_kuliah_aktif
                $this->load->model('Mata_kuliah_aktif_model');
                $mka = $this->Mata_kuliah_aktif_model->get_by_id($id_mata_kuliah_aktif);
                $id_tahun_akademik = $mka ? $mka->id_tahun_akademik : $tahun_aktif->id;
                $id_kurikulum_detail = $mka ? $mka->id_kurikulum_detail : null;

                $data_mapping = array(
                    'id_mata_kuliah_aktif' => $id_mata_kuliah_aktif,
                    'id_tahun_akademik' => $id_tahun_akademik,
                    'id_kurikulum_detail' => $id_kurikulum_detail,
                    'id_dosen' => $id_dosen,
                    'kelas' => $kelas
                );

                // Update mapping
                if ($this->Mapping_dosen_mk_model->update($id, $data_mapping)) {
                    $this->session->set_flashdata('success', 'Mapping berhasil diupdate.');
                    redirect('Mapping_mk');
                } else {
                    $this->session->set_flashdata('error', 'Gagal mengupdate mapping.');
                    redirect('Mapping_mk/edit/' . $id);
                }
            }
        } else {
            $data['mapping'] = $mapping;
            $data['mata_kuliah_aktif'] = $this->Mapping_dosen_mk_model->get_all_mata_kuliah_aktif($tahun_aktif->id);
            $data['dosen'] = $this->Dosen_model->get_all(1000, 0);
            $data['kelas_list'] = $this->Mapping_dosen_mk_model->get_all_kelas($tahun_aktif);
            if (empty($data['kelas_list'])) {
                $data['kelas_list'] = $this->Mapping_dosen_mk_model->get_all_kelas(null);
            }
            $data['tahun_aktif'] = $tahun_aktif;
            $data['title'] = 'Edit Mapping MK';
            $data['page_title'] = 'Edit Mapping Mata Kuliah - Dosen';
            $data['breadcrumb'] = array(
                array('label' => 'Mapping MK', 'url' => site_url('Mapping_mk')),
                array('label' => 'Edit', 'active' => true)
            );
            $data['sidebar_menu'] = generate_sidebar_menu('sekprodi');
            $data['content'] = $this->load->view('mapping_mk/edit', $data, true);
            $this->load->view('layouts/main', $data);
        }
    }

    /**
     * Hapus mapping
     */
    public function delete($id) {
        $mapping = $this->Mapping_dosen_mk_model->get_by_id($id);
        
        if (!$mapping) {
            $this->session->set_flashdata('error', 'Mapping tidak ditemukan.');
            redirect('Mapping_mk');
        }

        if ($this->Mapping_dosen_mk_model->delete($id)) {
            $this->session->set_flashdata('success', 'Mapping berhasil dihapus.');
        } else {
            $this->session->set_flashdata('error', 'Gagal menghapus mapping.');
        }
        
        redirect('Mapping_mk');
    }

    /**
     * AJAX: Get mata kuliah aktif berdasarkan kelas
     * Mendukung GET/POST (GET disarankan agar tidak bentrok CSRF regenerate).
     */
    public function get_mk_by_kelas() {
        // Clear any output buffer
        if (ob_get_level()) {
            ob_clean();
        }
        
        header('Content-Type: application/json; charset=utf-8');
        
        try {
            $kelas = $this->input->get_post('kelas');
            
            if (empty($kelas)) {
                echo json_encode(array('success' => false, 'message' => 'Kelas tidak boleh kosong'));
                exit;
            }

            // Ambil tahun akademik aktif
            $tahun_aktif = $this->Tahun_akademik_model->get_aktif();
            
            if (!$tahun_aktif) {
                echo json_encode(array('success' => false, 'message' => 'Tidak ada tahun akademik aktif'));
                exit;
            }

            // Ambil mata kuliah aktif untuk kelas tersebut
            $mata_kuliah = $this->Mapping_dosen_mk_model->get_mata_kuliah_aktif_by_kelas($kelas, $tahun_aktif->id);
        
            // Cek apakah ada mahasiswa di kelas tersebut
            $this->load->model('Mahasiswa_model');
            $mahasiswa_kelas = $this->Mahasiswa_model->get_by_kelas($kelas);
            
            if (empty($mahasiswa_kelas)) {
                echo json_encode(array(
                    'success' => false, 
                    'message' => 'Tidak ada mahasiswa aktif di kelas ' . $kelas . '.'
                ));
                exit;
            }
            
            // Cek apakah mahasiswa memiliki kurikulum
            $has_kurikulum = false;
            $kurikulum_list = array();
            foreach ($mahasiswa_kelas as $mhs) {
                if ($mhs->id_kurikulum) {
                    $has_kurikulum = true;
                    // Ambil nama kurikulum
                    $this->db->select('nama_kurikulum, tahun_berlaku');
                    $this->db->where('id', $mhs->id_kurikulum);
                    $kurikulum = $this->db->get('kurikulum')->row();
                    if ($kurikulum) {
                        $kurikulum_list[$mhs->id_kurikulum] = $kurikulum->nama_kurikulum . ' (Tahun ' . $kurikulum->tahun_berlaku . ')';
                    }
                }
            }
            
            if (!$has_kurikulum) {
                echo json_encode(array(
                    'success' => false, 
                    'message' => 'Mahasiswa di kelas ' . $kelas . ' belum memiliki kurikulum. Silakan set kurikulum untuk mahasiswa terlebih dahulu.'
                ));
                exit;
            }
            
            // Jika tidak ada mata kuliah aktif
            if (empty($mata_kuliah)) {
                $kurikulum_names = implode(', ', array_unique($kurikulum_list));
                $semester_kelas = $this->Mapping_dosen_mk_model->extract_semester_from_kelas($kelas);
                echo json_encode(array(
                    'success' => false, 
                    'message' => 'Tidak ada mata kuliah aktif untuk kelas ' . $kelas . ' (semester ' . $semester_kelas . '). Kurikulum yang digunakan: ' . $kurikulum_names . '. Pastikan: 1) Kurikulum sudah diaktifkan, 2) Mata kuliah semester ' . $semester_kelas . ' sudah di-set aktif di menu Kurikulum > Mapping MK (klik tombol "Aktifkan Semua Mata Kuliah").'
                ));
                exit;
            }
            
            // Cek mana yang sudah di-mapping dan ambil info dosennya
            // PENTING: Filter berdasarkan kelas yang dipilih agar mapping spesifik per kelas
            $mapped_ids = array();
            $mapped_dosen = array(); // Array dengan key id_mata_kuliah_aktif => id_dosen
            if (!empty($mata_kuliah)) {
                $mka_ids = array();
                foreach ($mata_kuliah as $mk) {
                    $mka_ids[] = $mk->id;
                }
                
                if (!empty($mka_ids)) {
                    $this->db->select('id_mata_kuliah_aktif, id_dosen');
                    $this->db->where_in('id_mata_kuliah_aktif', $mka_ids);
                    // Filter berdasarkan kelas yang dipilih - mapping spesifik per kelas
                    $this->db->where('kelas', $kelas);
                    $mapped = $this->db->get('mapping_dosen_mk')->result();
                    foreach ($mapped as $m) {
                        $mapped_ids[] = $m->id_mata_kuliah_aktif;
                        $mapped_dosen[$m->id_mata_kuliah_aktif] = $m->id_dosen;
                    }
                }
            }

            // Convert object to array untuk JSON
            $data_array = array();
            foreach ($mata_kuliah as $mk) {
                $data_array[] = array(
                    'id' => isset($mk->id) ? $mk->id : null,
                    'kode_mk' => isset($mk->kode_mk) ? $mk->kode_mk : '',
                    'nama_mk' => isset($mk->nama_mk) ? $mk->nama_mk : '',
                    'sks' => isset($mk->sks) ? $mk->sks : 0,
                    'semester' => isset($mk->semester) ? $mk->semester : (isset($mk->semester_aktif) ? $mk->semester_aktif : 0),
                    'semester_aktif' => isset($mk->semester_aktif) ? $mk->semester_aktif : 0,
                    'nama_kurikulum' => isset($mk->nama_kurikulum) ? $mk->nama_kurikulum : '',
                    'tahun_berlaku' => isset($mk->tahun_berlaku) ? $mk->tahun_berlaku : ''
                );
            }

            echo json_encode(array(
                'success' => true,
                'data' => $data_array,
                'mapped_ids' => $mapped_ids,
                'mapped_dosen' => $mapped_dosen
            ));
            exit;
            
        } catch (Exception $e) {
            echo json_encode(array(
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ));
            exit;
        }
    }

    /**
     * AJAX: Get mata kuliah yang belum di-mapping untuk kelas tertentu (untuk mapping manual)
     * Mendukung GET/POST (GET disarankan agar tidak bentrok CSRF regenerate).
     */
    public function get_mk_available_by_kelas() {
        // Clear any output buffer
        if (ob_get_level()) {
            ob_clean();
        }
        
        header('Content-Type: application/json; charset=utf-8');
        
        try {
            $kelas = $this->input->get_post('kelas');
            
            if (empty($kelas)) {
                echo json_encode(array('success' => false, 'message' => 'Kelas tidak boleh kosong'));
                exit;
            }

            // Ambil tahun akademik aktif
            $tahun_aktif = $this->Tahun_akademik_model->get_aktif();
            
            if (!$tahun_aktif) {
                echo json_encode(array('success' => false, 'message' => 'Tidak ada tahun akademik aktif'));
                exit;
            }

            // Ambil semua mata kuliah untuk kelas tersebut (menggunakan logika yang sama dengan menu Kelas)
            $mata_kuliah = $this->Mapping_dosen_mk_model->get_mata_kuliah_aktif_by_kelas($kelas, $tahun_aktif->id);
            
            if (empty($mata_kuliah)) {
                echo json_encode(array(
                    'success' => false, 
                    'message' => 'Tidak ada mata kuliah untuk kelas ' . $kelas . '. Pastikan mahasiswa di kelas ini sudah memiliki kurikulum dan mata kuliah sudah di-input di kurikulum.'
                ));
                exit;
            }
            
            // Ambil mapping yang sudah ada untuk kelas tersebut
            $mka_ids = array();
            foreach ($mata_kuliah as $mk) {
                $mka_ids[] = $mk->id;
            }
            
            $mapped_ids = array();
            if (!empty($mka_ids)) {
                $this->db->select('id_mata_kuliah_aktif');
                $this->db->where_in('id_mata_kuliah_aktif', $mka_ids);
                $this->db->where('kelas', $kelas); // Filter berdasarkan kelas
                $mapped = $this->db->get('mapping_dosen_mk')->result();
                foreach ($mapped as $m) {
                    $mapped_ids[] = $m->id_mata_kuliah_aktif;
                }
            }
            
            // Filter hanya yang belum di-mapping untuk kelas tersebut
            $available = array();
            foreach ($mata_kuliah as $mk) {
                if (!in_array($mk->id, $mapped_ids)) {
                    $available[] = $mk;
                }
            }
            
            // Convert object to array untuk JSON
            $data_array = array();
            foreach ($available as $mk) {
                $data_array[] = array(
                    'id' => isset($mk->id) ? $mk->id : null,
                    'kode_mk' => isset($mk->kode_mk) ? $mk->kode_mk : '',
                    'nama_mk' => isset($mk->nama_mk) ? $mk->nama_mk : '',
                    'sks' => isset($mk->sks) ? $mk->sks : 0,
                    'semester' => isset($mk->semester) ? $mk->semester : (isset($mk->semester_aktif) ? $mk->semester_aktif : 0),
                    'semester_aktif' => isset($mk->semester_aktif) ? $mk->semester_aktif : 0,
                    'nama_kurikulum' => isset($mk->nama_kurikulum) ? $mk->nama_kurikulum : '',
                    'tahun_berlaku' => isset($mk->tahun_berlaku) ? $mk->tahun_berlaku : ''
                );
            }

            echo json_encode(array(
                'success' => true,
                'data' => $data_array
            ));
            exit;
            
        } catch (Exception $e) {
            echo json_encode(array(
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ));
            exit;
        }
    }

    /**
     * AJAX: Get semua kelas
     * Mendukung GET/POST. Filter opsional sesuai semester aktif (ganjil=1,3,5,7 / genap=2,4,6,8).
     */
    public function get_kelas() {
        // Clear any output buffer
        if (ob_get_level()) {
            ob_clean();
        }
        
        header('Content-Type: application/json; charset=utf-8');
        
        try {
            $tahun_aktif = $this->Tahun_akademik_model->get_aktif();
            $filter_semester = $this->input->get_post('filter_semester');
            if ($filter_semester === null || $filter_semester === '') {
                $filter_semester = '1'; // default: filter sesuai tahun akademik aktif
            }

            $kelas_list = $this->Mapping_dosen_mk_model->get_all_kelas(
                ($filter_semester === '1' || $filter_semester === 1 || $filter_semester === true)
                    ? $tahun_aktif
                    : null
            );
            
            // Convert object to array untuk JSON
            $data_array = array();
            foreach ($kelas_list as $k) {
                if (isset($k->kelas) && !empty($k->kelas)) {
                    $data_array[] = array('kelas' => $k->kelas);
                }
            }

            // Fallback: jika filter ganjil/genap kosong, kirim semua kelas
            if (empty($data_array) && $tahun_aktif) {
                $kelas_list = $this->Mapping_dosen_mk_model->get_all_kelas(null);
                foreach ($kelas_list as $k) {
                    if (isset($k->kelas) && !empty($k->kelas)) {
                        $data_array[] = array('kelas' => $k->kelas);
                    }
                }
            }
            
            echo json_encode(array(
                'success' => true,
                'data' => $data_array,
                'tahun_akademik' => $tahun_aktif ? ($tahun_aktif->tahun_akademik . ' ' . $tahun_aktif->semester) : null
            ));
            exit;
        } catch (Exception $e) {
            echo json_encode(array(
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ));
            exit;
        }
    }

    /**
     * Batch create mapping (untuk multiple mata kuliah sekaligus)
     */
    public function batch_create() {
        if ($this->input->method() !== 'post') {
            redirect('Mapping_mk');
        }

        $mappings = $this->input->post('mappings');
        
        if (empty($mappings) || !is_array($mappings)) {
            $this->session->set_flashdata('error', 'Tidak ada data mapping yang dipilih.');
            redirect('Mapping_mk/create');
        }

        $success_count = 0;
        $error_count = 0;
        $errors = array();

        $kelas = $this->input->post('kelas') ? $this->input->post('kelas') : null;
        
        foreach ($mappings as $mapping) {
            $id_mata_kuliah_aktif = isset($mapping['id_mata_kuliah_aktif']) ? $mapping['id_mata_kuliah_aktif'] : null;
            $id_dosen = isset($mapping['id_dosen']) ? $mapping['id_dosen'] : null;

            if (empty($id_mata_kuliah_aktif) || empty($id_dosen)) {
                $error_count++;
                continue;
            }

            // Cek apakah mapping sudah ada
            if ($this->Mapping_dosen_mk_model->mapping_exists($id_mata_kuliah_aktif, $id_dosen, $kelas)) {
                $error_count++;
                continue;
            }

            // Ambil id_tahun_akademik dan id_kurikulum_detail dari mata_kuliah_aktif
            $this->load->model('Mata_kuliah_aktif_model');
            $mka = $this->Mata_kuliah_aktif_model->get_by_id($id_mata_kuliah_aktif);
            $id_tahun_akademik = $mka ? $mka->id_tahun_akademik : $tahun_aktif->id;
            $id_kurikulum_detail = $mka ? $mka->id_kurikulum_detail : null;

            $data_mapping = array(
                'id_mata_kuliah_aktif' => $id_mata_kuliah_aktif,
                'id_tahun_akademik' => $id_tahun_akademik,
                'id_kurikulum_detail' => $id_kurikulum_detail,
                'id_dosen' => $id_dosen,
                'kelas' => $kelas
            );

            if ($this->Mapping_dosen_mk_model->create($data_mapping)) {
                $success_count++;
            } else {
                $error_count++;
            }
        }

        if ($success_count > 0) {
            $this->session->set_flashdata('success', "Berhasil menambahkan {$success_count} mapping." . ($error_count > 0 ? " {$error_count} mapping gagal." : ""));
        } else {
            $this->session->set_flashdata('error', "Gagal menambahkan mapping. Pastikan semua data sudah diisi dengan benar.");
        }

        redirect('Mapping_mk');
    }

    /**
     * Import Excel untuk mapping MK
     */
    public function import_excel() {
        // Ambil tahun akademik aktif
        $tahun_aktif = $this->Tahun_akademik_model->get_aktif();
        
        if (!$tahun_aktif) {
            $this->session->set_flashdata('error', 'Tidak ada tahun akademik aktif. Silakan aktifkan tahun akademik terlebih dahulu.');
            redirect('Mapping_mk');
        }

        if ($this->input->method() === 'post') {
            // Upload file
            $config['upload_path'] = './uploads/temp/';
            $config['allowed_types'] = 'xlsx|xls|csv';
            $config['max_size'] = 5120; // 5MB
            $config['encrypt_name'] = TRUE;

            if (!is_dir($config['upload_path'])) {
                mkdir($config['upload_path'], 0755, true);
            }

            $this->load->library('upload', $config);

            if (!$this->upload->do_upload('file_excel')) {
                $this->session->set_flashdata('error', 'Gagal upload file: ' . $this->upload->display_errors());
                redirect('Mapping_mk/import_excel');
            }

            $upload_data = $this->upload->data();
            $file_path = $config['upload_path'] . $upload_data['file_name'];

            // Load library Excel untuk mapping dosen-MK
            $this->load->library('Excel_reader');
            $result = $this->excel_reader->read_excel_mapping_dosen($file_path, $tahun_aktif->id);

            // Hapus file temporary
            unlink($file_path);

            if ($result['success']) {
                $this->session->set_flashdata('success', $result['message']);
            } else {
                $this->session->set_flashdata('error', $result['message']);
            }

            redirect('Mapping_mk');
        } else {
            $data['tahun_aktif'] = $tahun_aktif;
            $data['title'] = 'Import Excel - Mapping MK';
            $data['page_title'] = 'Import Excel - Mapping Mata Kuliah - Dosen';
            $data['breadcrumb'] = array(
                array('label' => 'Mapping MK', 'url' => site_url('Mapping_mk')),
                array('label' => 'Import Excel', 'active' => true)
            );
            $data['sidebar_menu'] = generate_sidebar_menu('sekprodi');
            $data['content'] = $this->load->view('mapping_mk/import_excel', $data, true);
            $this->load->view('layouts/main', $data);
        }
    }

    /**
     * Download template CSV untuk import mapping
     */
    public function download_template() {
        // Ambil tahun akademik aktif
        $tahun_aktif = $this->Tahun_akademik_model->get_aktif();
        
        if (!$tahun_aktif) {
            $this->session->set_flashdata('error', 'Tidak ada tahun akademik aktif.');
            redirect('Mapping_mk/import_excel');
        }

        // Buat folder jika belum ada
        if (!is_dir(APPPATH . '../uploads/templates/')) {
            mkdir(APPPATH . '../uploads/templates/', 0755, true);
        }

        // Buat file CSV
        $filename = 'template_mapping_dosen_mk.csv';
        $csv_file = APPPATH . '../uploads/templates/' . $filename;
        
        // Gunakan BOM UTF-8 agar Excel membaca dengan benar
        $fp = fopen($csv_file, 'w');
        fprintf($fp, chr(0xEF).chr(0xBB).chr(0xBF)); // UTF-8 BOM
        
        // Header
        fputcsv($fp, array('kode_mk', 'nidn'));
        
        // Ambil beberapa contoh mata kuliah aktif dan dosen untuk contoh
        $this->load->model('Mapping_dosen_mk_model');
        $this->load->model('Dosen_model');
        
        $mata_kuliah_aktif = $this->Mapping_dosen_mk_model->get_all_mata_kuliah_aktif($tahun_aktif->id);
        $dosen_list = $this->Dosen_model->get_all(5, 0); // Ambil 5 dosen pertama sebagai contoh
        
        // Contoh data (ambil 3 mata kuliah pertama dan 2 dosen pertama)
        if (!empty($mata_kuliah_aktif) && !empty($dosen_list)) {
            $mk_count = min(3, count($mata_kuliah_aktif));
            $dosen_count = min(2, count($dosen_list));
            
            for ($i = 0; $i < $mk_count; $i++) {
                $mk = $mata_kuliah_aktif[$i];
                $dosen = $dosen_list[$i % $dosen_count];
                fputcsv($fp, array($mk->kode_mk, $dosen->nidn));
            }
        } else {
            // Contoh data default jika tidak ada data
            fputcsv($fp, array('SIF2001', '1234567890'));
            fputcsv($fp, array('SIF3002', '1234567890'));
            fputcsv($fp, array('SIF3003', '0987654321'));
        }
        
        fclose($fp);

        // Download file
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Content-Length: ' . filesize($csv_file));
        readfile($csv_file);
        
        // Hapus file setelah download (opsional)
        // unlink($csv_file);
        exit;
    }

    /**
     * Download daftar dosen beserta NID
     */
    public function download_daftar_dosen() {
        $this->load->model('Dosen_model');
        
        // Ambil semua dosen aktif
        $dosen_list = $this->Dosen_model->get_all(10000, 0);
        
        // Filter hanya yang aktif
        $dosen_aktif = array();
        foreach ($dosen_list as $d) {
            if ($d->status == 'aktif') {
                $dosen_aktif[] = $d;
            }
        }
        
        // Buat folder jika belum ada
        if (!is_dir(APPPATH . '../uploads/templates/')) {
            mkdir(APPPATH . '../uploads/templates/', 0755, true);
        }

        // Buat file CSV
        $filename = 'daftar_dosen_' . date('YmdHis') . '.csv';
        $csv_file = APPPATH . '../uploads/templates/' . $filename;
        
        // Gunakan BOM UTF-8 agar Excel membaca dengan benar
        $fp = fopen($csv_file, 'w');
        fprintf($fp, chr(0xEF).chr(0xBB).chr(0xBF)); // UTF-8 BOM
        
        // Header
        fputcsv($fp, array('NIDN', 'Nama Dosen', 'Status'));
        
        // Data dosen
        foreach ($dosen_aktif as $d) {
            fputcsv($fp, array(
                $d->nidn,
                $d->nama_dosen,
                ucfirst($d->status)
            ));
        }
        
        fclose($fp);

        // Download file
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Content-Length: ' . filesize($csv_file));
        readfile($csv_file);
        
        // Hapus file setelah download
        unlink($csv_file);
        exit;
    }

    /**
     * Download daftar kode mata kuliah aktif
     */
    public function download_daftar_mk_aktif() {
        // Ambil tahun akademik aktif
        $tahun_aktif = $this->Tahun_akademik_model->get_aktif();
        
        if (!$tahun_aktif) {
            $this->session->set_flashdata('error', 'Tidak ada tahun akademik aktif.');
            redirect('Mapping_mk/import_excel');
        }
        
        // Ambil semua mata kuliah aktif
        $mata_kuliah_aktif = $this->Mapping_dosen_mk_model->get_all_mata_kuliah_aktif($tahun_aktif->id);
        
        // Buat folder jika belum ada
        if (!is_dir(APPPATH . '../uploads/templates/')) {
            mkdir(APPPATH . '../uploads/templates/', 0755, true);
        }

        // Buat file CSV
        $filename = 'daftar_mata_kuliah_aktif_' . date('YmdHis') . '.csv';
        $csv_file = APPPATH . '../uploads/templates/' . $filename;
        
        // Gunakan BOM UTF-8 agar Excel membaca dengan benar
        $fp = fopen($csv_file, 'w');
        fprintf($fp, chr(0xEF).chr(0xBB).chr(0xBF)); // UTF-8 BOM
        
        // Header
        fputcsv($fp, array('Kode MK', 'Nama Mata Kuliah', 'SKS', 'Semester', 'Kurikulum', 'Tahun Berlaku'));
        
        // Data mata kuliah
        foreach ($mata_kuliah_aktif as $mk) {
            $semester_display = isset($mk->semester) ? $mk->semester : (isset($mk->semester_aktif) ? $mk->semester_aktif : '-');
            fputcsv($fp, array(
                $mk->kode_mk,
                $mk->nama_mk,
                $mk->sks,
                $semester_display,
                $mk->nama_kurikulum,
                $mk->tahun_berlaku
            ));
        }
        
        fclose($fp);

        // Download file
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Content-Length: ' . filesize($csv_file));
        readfile($csv_file);
        
        // Hapus file setelah download
        unlink($csv_file);
        exit;
    }

    /**
     * Export mapping ke Excel
     */
    public function export_excel() {
        // Ambil tahun akademik aktif
        $tahun_aktif = $this->Tahun_akademik_model->get_aktif();
        
        if (!$tahun_aktif) {
            $this->session->set_flashdata('error', 'Tidak ada tahun akademik aktif.');
            redirect('Mapping_mk');
        }
        
        // Ambil semua mapping untuk tahun akademik aktif
        $mapping = $this->Mapping_dosen_mk_model->get_all($tahun_aktif->id, 10000, 0);
        
        // Cek apakah PhpSpreadsheet tersedia
        if (file_exists(APPPATH . '../vendor/autoload.php')) {
            require_once APPPATH . '../vendor/autoload.php';
            
            $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet();
            
            // Set title
            $sheet->setCellValue('A1', 'EXPORT MAPPING MATA KULIAH - DOSEN');
            $sheet->setCellValue('A2', 'Tahun Akademik: ' . $tahun_aktif->tahun_akademik . ' ' . ucfirst($tahun_aktif->semester));
            $sheet->setCellValue('A3', 'Tanggal Export: ' . date('d/m/Y H:i:s'));
            
            // Merge cells untuk title
            $sheet->mergeCells('A1:D1');
            $sheet->mergeCells('A2:D2');
            $sheet->mergeCells('A3:D3');
            
            // Style title
            $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14);
            $sheet->getStyle('A2')->getFont()->setSize(11);
            $sheet->getStyle('A3')->getFont()->setSize(10);
            $sheet->getStyle('A3')->getFont()->setItalic(true);
            
            // Set header (mulai dari baris 5)
            $sheet->setCellValue('A5', 'No');
            $sheet->setCellValue('B5', 'Kode MK');
            $sheet->setCellValue('C5', 'Nama Mata Kuliah');
            $sheet->setCellValue('D5', 'SKS');
            $sheet->setCellValue('E5', 'Semester');
            $sheet->setCellValue('F5', 'Kelas');
            $sheet->setCellValue('G5', 'NIDN');
            $sheet->setCellValue('H5', 'Nama Dosen');
            $sheet->setCellValue('I5', 'Kurikulum');
            $sheet->setCellValue('J5', 'Tahun Berlaku');
            
            // Style header
            $sheet->getStyle('A5:J5')->getFont()->setBold(true);
            $sheet->getStyle('A5:J5')->getFill()
                ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                ->getStartColor()->setARGB('FFE0E0E0');
            $sheet->getStyle('A5:J5')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
            
            // Data mapping
            $row = 6;
            $no = 1;
            foreach ($mapping as $m) {
                $sheet->setCellValue('A' . $row, $no++);
                $sheet->setCellValue('B' . $row, $m->kode_mk);
                $sheet->setCellValue('C' . $row, $m->nama_mk);
                $sheet->setCellValue('D' . $row, $m->sks);
                $sheet->setCellValue('E' . $row, $m->semester_aktif);
                $sheet->setCellValue('F' . $row, isset($m->kelas) && !empty($m->kelas) ? $m->kelas : '-');
                $sheet->setCellValue('G' . $row, $m->nidn);
                $sheet->setCellValue('H' . $row, $m->nama_dosen);
                $sheet->setCellValue('I' . $row, $m->nama_kurikulum);
                $sheet->setCellValue('J' . $row, $m->tahun_berlaku);
                $row++;
            }
            
            // Auto size columns
            foreach (range('A', 'J') as $col) {
                $sheet->getColumnDimension($col)->setAutoSize(true);
            }
            
            // Set border untuk data
            $lastRow = $row - 1;
            if ($lastRow >= 6) {
                $sheet->getStyle('A5:J' . $lastRow)->applyFromArray([
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                        ],
                    ],
                ]);
            }
            
            // Download
            $filename = 'export_mapping_dosen_mk_' . date('YmdHis') . '.xlsx';
            header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
            header('Content-Disposition: attachment;filename="' . $filename . '"');
            header('Cache-Control: max-age=0');
            
            $writer = \PhpOffice\PhpSpreadsheet\IOFactory::createWriter($spreadsheet, 'Xlsx');
            $writer->save('php://output');
            exit;
        } else {
            // Fallback: download CSV
            $filename = 'export_mapping_dosen_mk_' . date('YmdHis') . '.csv';
            header('Content-Type: text/csv; charset=utf-8');
            header('Content-Disposition: attachment;filename="' . $filename . '"');
            
            $output = fopen('php://output', 'w');
            // UTF-8 BOM untuk Excel
            fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));
            
            // Header
            fputcsv($output, array('No', 'Kode MK', 'Nama Mata Kuliah', 'SKS', 'Semester', 'Kelas', 'NIDN', 'Nama Dosen', 'Kurikulum', 'Tahun Berlaku'));
            
            // Data
            $no = 1;
            foreach ($mapping as $m) {
                fputcsv($output, array(
                    $no++,
                    $m->kode_mk,
                    $m->nama_mk,
                    $m->sks,
                    $m->semester_aktif,
                    isset($m->kelas) && !empty($m->kelas) ? $m->kelas : '-',
                    $m->nidn,
                    $m->nama_dosen,
                    $m->nama_kurikulum,
                    $m->tahun_berlaku
                ));
            }
            
            fclose($output);
            exit;
        }
    }
}

