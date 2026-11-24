<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Jadwal Kuliah Controller
 * Mengelola jadwal kuliah dengan validasi bentrok
 */
class Jadwal_kuliah extends MY_Controller {

    public function __construct() {
        parent::__construct();
        $this->check_role(array('admin', 'sekprodi', 'kaprodi'));
        $this->load->model('Jadwal_kuliah_model');
        $this->load->model('Mapping_dosen_mk_model');
        $this->load->model('Ruang_kelas_model');
        $this->load->model('Tahun_akademik_model');
    }

    /**
     * List semua jadwal dengan pagination
     */
    public function index() {
        $this->load->library('pagination');
        
        // Ambil tahun akademik aktif
        $tahun_aktif = $this->Tahun_akademik_model->get_aktif();
        $id_tahun_akademik = $tahun_aktif ? $tahun_aktif->id : null;
        
        // Ambil parameter filter
        $filter_kelas = $this->input->get('kelas');
        $per_page = $this->input->get('per_page') ? (int)$this->input->get('per_page') : 20;
        $per_page = in_array($per_page, [10, 25, 50, 100]) ? $per_page : 20;
        
        // Query dengan filter kelas jika ada
        if ($filter_kelas && $id_tahun_akademik) {
            $total_rows = $this->db->from('jadwal_kuliah')
                ->where('kelas', $filter_kelas)
                ->where('id_tahun_akademik', $id_tahun_akademik)
                ->count_all_results();
        } else {
            $total_rows = $this->Jadwal_kuliah_model->count_all($id_tahun_akademik);
        }
        
        // Pagination config
        $config['base_url'] = site_url('Jadwal_kuliah/index');
        $config['total_rows'] = $total_rows;
        $config['per_page'] = $per_page;
        $config['uri_segment'] = 3;
        
        // Tambahkan query string ke pagination
        $query_string = http_build_query(array_filter([
            'kelas' => $filter_kelas,
            'per_page' => $per_page != 20 ? $per_page : null
        ]));
        if ($query_string) {
            $config['suffix'] = '?' . $query_string;
            $config['first_url'] = site_url('Jadwal_kuliah/index?' . $query_string);
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
        
        // Ambil jadwal dengan filter kelas jika ada
        if ($filter_kelas && $id_tahun_akademik) {
            // Query langsung dengan limit dan offset untuk filter kelas
            $this->db->select('jk.*, 
                              mdm.kelas as kelas_mapping,
                              mk.kode_mk, mk.nama_mk, mk.sks,
                              d.nidn, d.nama_dosen,
                              rk.kode_ruang, rk.nama_ruang,
                              ta.tahun_akademik, ta.semester');
            $this->db->from('jadwal_kuliah jk');
            $this->db->join('mapping_dosen_mk mdm', 'mdm.id = jk.id_mapping_dosen_mk', 'left');
            $this->db->join('mata_kuliah_aktif mka', 'mka.id = mdm.id_mata_kuliah_aktif', 'left');
            $this->db->join('kurikulum_detail kd', 'kd.id = mka.id_kurikulum_detail', 'left');
            $this->db->join('mata_kuliah mk', 'mk.id = kd.id_mata_kuliah', 'left');
            $this->db->join('dosen d', 'd.id = mdm.id_dosen', 'left');
            $this->db->join('ruang_kelas rk', 'rk.id = jk.id_ruang_kelas', 'left');
            $this->db->join('tahun_akademik ta', 'ta.id = jk.id_tahun_akademik', 'left');
            $this->db->where('jk.kelas', $filter_kelas);
            $this->db->where('jk.id_tahun_akademik', $id_tahun_akademik);
            $this->db->order_by('jk.hari', 'ASC');
            $this->db->order_by('jk.jam_mulai', 'ASC');
            $this->db->limit($config['per_page'], $offset);
            $data['jadwal'] = $this->db->get()->result();
        } else {
            $data['jadwal'] = $this->Jadwal_kuliah_model->get_all($id_tahun_akademik, $config['per_page'], $offset);
        }
        
        $data['pagination'] = $this->pagination->create_links();
        $data['total_rows'] = $config['total_rows'];
        $data['tahun_aktif'] = $tahun_aktif;
        $data['per_page'] = $per_page;
        $data['filter_kelas'] = $filter_kelas;
        
        // Ambil daftar kelas untuk dropdown filter dan download (sekprodi & kaprodi)
        if (in_array(get_user_role(), array('sekprodi', 'kaprodi'), true) && $id_tahun_akademik) {
            $data['kelas_list'] = $this->Jadwal_kuliah_model->get_kelas_with_mapping($id_tahun_akademik);
        } else {
            $data['kelas_list'] = array();
        }
        
        $data['title'] = 'Jadwal Kuliah';
        $data['page_title'] = 'Jadwal Kuliah';
        $data['breadcrumb'] = array(
            array('label' => 'Jadwal Kuliah', 'active' => true)
        );
        $data['sidebar_menu'] = generate_sidebar_menu(get_user_role());
        $data['content'] = $this->load->view('jadwal_kuliah/index', $data, true);
        $this->load->view('layouts/main', $data);
    }

    /**
     * Form tambah jadwal
     */
    public function create() {
        $this->assert_not_kaprodi('Jadwal_kuliah');
        // Ambil tahun akademik aktif
        $tahun_aktif = $this->Tahun_akademik_model->get_aktif();
        
        if (!$tahun_aktif) {
            $this->session->set_flashdata('error', 'Tidak ada tahun akademik aktif. Silakan aktifkan tahun akademik terlebih dahulu.');
            redirect('Jadwal_kuliah');
        }

        if ($this->input->method() === 'post') {
            $this->form_validation->set_rules('id_mapping_dosen_mk', 'Mapping MK', 'required|trim|numeric');
            $this->form_validation->set_rules('kelas', 'Kelas', 'required|trim');
            $this->form_validation->set_rules('hari', 'Hari', 'required|trim');
            $this->form_validation->set_rules('jam_mulai', 'Jam Mulai', 'required|trim');
            $this->form_validation->set_rules('id_ruang_kelas', 'Ruang Kelas', 'required|trim|numeric');

            if ($this->form_validation->run() == FALSE) {
                $data['mapping_list'] = array();
                $data['ruang_list'] = $this->Ruang_kelas_model->get_all_active();
                $data['kelas_list'] = $this->Jadwal_kuliah_model->get_kelas_with_mapping($tahun_aktif->id);
                $data['tahun_aktif'] = $tahun_aktif;
                $data['title'] = 'Tambah Jadwal Kuliah';
                $data['page_title'] = 'Tambah Jadwal Kuliah';
                $data['breadcrumb'] = array(
                    array('label' => 'Jadwal Kuliah', 'url' => site_url('Jadwal_kuliah')),
                    array('label' => 'Tambah', 'active' => true)
                );
                $data['sidebar_menu'] = generate_sidebar_menu(get_user_role());
                $data['content'] = $this->load->view('jadwal_kuliah/create', $data, true);
                $this->load->view('layouts/main', $data);
            } else {
                $id_mapping_dosen_mk = $this->input->post('id_mapping_dosen_mk');
                $kelas = $this->input->post('kelas');
                $hari = $this->input->post('hari');
                $jam_mulai = $this->input->post('jam_mulai');
                $jam_selesai = $this->input->post('jam_selesai');
                $id_ruang_kelas = $this->input->post('id_ruang_kelas');
                $keterangan = $this->input->post('keterangan');
                
                // Ambil SKS dari mapping untuk validasi
                $this->load->model('Mapping_dosen_mk_model');
                $mapping = $this->Mapping_dosen_mk_model->get_by_id($id_mapping_dosen_mk);
                
                if (!$mapping) {
                    $this->session->set_flashdata('error', 'Mapping tidak ditemukan.');
                    redirect('Jadwal_kuliah/create');
                }
                
                // Jika jam_selesai tidak diisi, hitung otomatis berdasarkan SKS (rekomendasi)
                if (empty($jam_selesai)) {
                    $sks = isset($mapping->sks) ? $mapping->sks : 0;
                    $jam_selesai = $this->Jadwal_kuliah_model->calculate_jam_selesai($jam_mulai, $sks);
                }
                
                // Validasi bentrok
                $errors = array();
                
                // 1. Cek bentrok ruang kelas
                if ($this->Jadwal_kuliah_model->check_bentrok_ruang($id_ruang_kelas, $hari, $jam_mulai, $jam_selesai, $tahun_aktif->id)) {
                    $ruang = $this->Ruang_kelas_model->get_by_id($id_ruang_kelas);
                    $errors[] = "Ruang kelas " . ($ruang ? $ruang->nama_ruang : '') . " sudah digunakan pada hari dan waktu yang sama oleh kelas lain.";
                }
                
                // 2. Cek bentrok dosen
                if ($this->Jadwal_kuliah_model->check_bentrok_dosen($id_mapping_dosen_mk, $hari, $jam_mulai, $jam_selesai, $tahun_aktif->id)) {
                    $errors[] = "Dosen sudah mengajar di waktu yang sama dengan kelas lain.";
                }
                
                // 3. Cek bentrok kelas
                if ($this->Jadwal_kuliah_model->check_bentrok_kelas($kelas, $hari, $jam_mulai, $jam_selesai, $tahun_aktif->id)) {
                    $errors[] = "Kelas " . $kelas . " sudah memiliki jadwal di hari dan waktu yang sama.";
                }
                
                if (!empty($errors)) {
                    $this->session->set_flashdata('error', implode('<br>', $errors));
                    redirect('Jadwal_kuliah/create');
                }
                
                // Generate kode jadwal
                $kode_jadwal = 'JDW-' . date('Ymd') . '-' . strtoupper($hari) . '-' . str_replace(':', '', $jam_mulai);
                
                $data_jadwal = array(
                    'kode_jadwal' => $kode_jadwal,
                    'id_mapping_dosen_mk' => $id_mapping_dosen_mk,
                    'kelas' => $kelas,
                    'hari' => $hari,
                    'jam_mulai' => $jam_mulai,
                    'jam_selesai' => $jam_selesai,
                    'id_ruang_kelas' => $id_ruang_kelas,
                    'id_tahun_akademik' => $tahun_aktif->id,
                    'keterangan' => $keterangan
                );

                // Insert jadwal
                $jadwal_id = $this->Jadwal_kuliah_model->create($data_jadwal);
                
                if ($jadwal_id) {
                    $this->session->set_flashdata('success', 'Jadwal kuliah berhasil ditambahkan.');
                    redirect('Jadwal_kuliah');
                } else {
                    $this->session->set_flashdata('error', 'Gagal menambahkan jadwal kuliah.');
                    redirect('Jadwal_kuliah/create');
                }
            }
        } else {
            $data['mapping_list'] = array();
            $data['ruang_list'] = $this->Ruang_kelas_model->get_all_active();
            $data['kelas_list'] = $this->Jadwal_kuliah_model->get_kelas_with_mapping($tahun_aktif->id);
            $data['tahun_aktif'] = $tahun_aktif;
            $data['title'] = 'Tambah Jadwal Kuliah';
            $data['page_title'] = 'Tambah Jadwal Kuliah';
            $data['breadcrumb'] = array(
                array('label' => 'Jadwal Kuliah', 'url' => site_url('Jadwal_kuliah')),
                array('label' => 'Tambah', 'active' => true)
            );
            $data['sidebar_menu'] = generate_sidebar_menu(get_user_role());
            $data['content'] = $this->load->view('jadwal_kuliah/create', $data, true);
            $this->load->view('layouts/main', $data);
        }
    }

    /**
     * Form edit jadwal
     */
    public function edit($id) {
        $this->assert_not_kaprodi('Jadwal_kuliah');
        $jadwal = $this->Jadwal_kuliah_model->get_by_id($id);
        
        if (!$jadwal) {
            $this->session->set_flashdata('error', 'Jadwal tidak ditemukan.');
            redirect('Jadwal_kuliah');
        }

        // Ambil tahun akademik aktif
        $tahun_aktif = $this->Tahun_akademik_model->get_aktif();

        if ($this->input->method() === 'post') {
            $this->form_validation->set_rules('id_mapping_dosen_mk', 'Mapping MK', 'required|trim|numeric');
            $this->form_validation->set_rules('kelas', 'Kelas', 'required|trim');
            $this->form_validation->set_rules('hari', 'Hari', 'required|trim');
            $this->form_validation->set_rules('jam_mulai', 'Jam Mulai', 'required|trim');
            $this->form_validation->set_rules('id_ruang_kelas', 'Ruang Kelas', 'required|trim|numeric');

            if ($this->form_validation->run() == FALSE) {
                $data['jadwal'] = $jadwal;
                $data['mapping_list'] = $this->Jadwal_kuliah_model->get_mapping_by_kelas($jadwal->kelas, $tahun_aktif->id);
                $data['ruang_list'] = $this->Ruang_kelas_model->get_all_active();
                $data['kelas_list'] = $this->Jadwal_kuliah_model->get_kelas_with_mapping($tahun_aktif->id);
                $data['tahun_aktif'] = $tahun_aktif;
                $data['title'] = 'Edit Jadwal Kuliah';
                $data['page_title'] = 'Edit Jadwal Kuliah';
                $data['breadcrumb'] = array(
                    array('label' => 'Jadwal Kuliah', 'url' => site_url('Jadwal_kuliah')),
                    array('label' => 'Edit', 'active' => true)
                );
                $data['sidebar_menu'] = generate_sidebar_menu(get_user_role());
                $data['content'] = $this->load->view('jadwal_kuliah/edit', $data, true);
                $this->load->view('layouts/main', $data);
            } else {
                $id_mapping_dosen_mk = $this->input->post('id_mapping_dosen_mk');
                $kelas = $this->input->post('kelas');
                $hari = $this->input->post('hari');
                $jam_mulai = $this->input->post('jam_mulai');
                $jam_selesai = $this->input->post('jam_selesai');
                $id_ruang_kelas = $this->input->post('id_ruang_kelas');
                $keterangan = $this->input->post('keterangan');
                
                // Ambil SKS dari mapping untuk validasi
                $this->load->model('Mapping_dosen_mk_model');
                $mapping = $this->Mapping_dosen_mk_model->get_by_id($id_mapping_dosen_mk);
                
                if (!$mapping) {
                    $this->session->set_flashdata('error', 'Mapping tidak ditemukan.');
                    redirect('Jadwal_kuliah/edit/' . $id);
                }
                
                // Jika jam_selesai tidak diisi, hitung otomatis berdasarkan SKS (rekomendasi)
                if (empty($jam_selesai)) {
                    $sks = isset($mapping->sks) ? $mapping->sks : 0;
                    $jam_selesai = $this->Jadwal_kuliah_model->calculate_jam_selesai($jam_mulai, $sks);
                }
                
                // Validasi bentrok (exclude jadwal yang sedang diedit)
                $errors = array();
                
                // 1. Cek bentrok ruang kelas
                if ($this->Jadwal_kuliah_model->check_bentrok_ruang($id_ruang_kelas, $hari, $jam_mulai, $jam_selesai, $tahun_aktif->id, $id)) {
                    $ruang = $this->Ruang_kelas_model->get_by_id($id_ruang_kelas);
                    $errors[] = "Ruang kelas " . ($ruang ? $ruang->nama_ruang : '') . " sudah digunakan pada hari dan waktu yang sama oleh kelas lain.";
                }
                
                // 2. Cek bentrok dosen
                if ($this->Jadwal_kuliah_model->check_bentrok_dosen($id_mapping_dosen_mk, $hari, $jam_mulai, $jam_selesai, $tahun_aktif->id, $id)) {
                    $errors[] = "Dosen sudah mengajar di waktu yang sama dengan kelas lain.";
                }
                
                // 3. Cek bentrok kelas
                if ($this->Jadwal_kuliah_model->check_bentrok_kelas($kelas, $hari, $jam_mulai, $jam_selesai, $tahun_aktif->id, $id)) {
                    $errors[] = "Kelas " . $kelas . " sudah memiliki jadwal di hari dan waktu yang sama.";
                }
                
                if (!empty($errors)) {
                    $this->session->set_flashdata('error', implode('<br>', $errors));
                    redirect('Jadwal_kuliah/edit/' . $id);
                }
                
                $data_jadwal = array(
                    'id_mapping_dosen_mk' => $id_mapping_dosen_mk,
                    'kelas' => $kelas,
                    'hari' => $hari,
                    'jam_mulai' => $jam_mulai,
                    'jam_selesai' => $jam_selesai,
                    'id_ruang_kelas' => $id_ruang_kelas,
                    'keterangan' => $keterangan
                );

                // Update jadwal
                if ($this->Jadwal_kuliah_model->update($id, $data_jadwal)) {
                    $this->session->set_flashdata('success', 'Jadwal kuliah berhasil diupdate.');
                    redirect('Jadwal_kuliah');
                } else {
                    $this->session->set_flashdata('error', 'Gagal mengupdate jadwal kuliah.');
                    redirect('Jadwal_kuliah/edit/' . $id);
                }
            }
        } else {
            $data['jadwal'] = $jadwal;
            // Untuk edit, hanya ambil mapping yang sedang diedit saja
            $this->db->select('mdm.id, mdm.kelas, mk.kode_mk, mk.nama_mk, mk.sks, d.nidn, d.nama_dosen');
            $this->db->from('mapping_dosen_mk mdm');
            $this->db->join('mata_kuliah_aktif mka', 'mka.id = mdm.id_mata_kuliah_aktif', 'left');
            $this->db->join('kurikulum_detail kd', 'kd.id = mka.id_kurikulum_detail', 'left');
            $this->db->join('mata_kuliah mk', 'mk.id = kd.id_mata_kuliah', 'left');
            $this->db->join('dosen d', 'd.id = mdm.id_dosen', 'left');
            $this->db->where('mdm.id', $jadwal->id_mapping_dosen_mk);
            $mapping_detail = $this->db->get()->row();
            
            $mapping_list = array();
            if ($mapping_detail) {
                $mapping_list[] = $mapping_detail;
            }
            
            $data['mapping_list'] = $mapping_list;
            $data['ruang_list'] = $this->Ruang_kelas_model->get_all_active();
            $data['kelas_list'] = $this->Jadwal_kuliah_model->get_kelas_with_mapping($tahun_aktif->id);
            $data['tahun_aktif'] = $tahun_aktif;
            $data['title'] = 'Edit Jadwal Kuliah';
            $data['page_title'] = 'Edit Jadwal Kuliah';
            $data['breadcrumb'] = array(
                array('label' => 'Jadwal Kuliah', 'url' => site_url('Jadwal_kuliah')),
                array('label' => 'Edit', 'active' => true)
            );
            $data['sidebar_menu'] = generate_sidebar_menu(get_user_role());
            $data['content'] = $this->load->view('jadwal_kuliah/edit', $data, true);
            $this->load->view('layouts/main', $data);
        }
    }

    /**
     * Hapus jadwal
     */
    public function delete($id) {
        $this->assert_not_kaprodi('Jadwal_kuliah');
        $jadwal = $this->Jadwal_kuliah_model->get_by_id($id);
        
        if (!$jadwal) {
            $this->session->set_flashdata('error', 'Jadwal tidak ditemukan.');
            redirect('Jadwal_kuliah');
        }

        if ($this->Jadwal_kuliah_model->delete($id)) {
            $this->session->set_flashdata('success', 'Jadwal kuliah berhasil dihapus.');
        } else {
            $this->session->set_flashdata('error', 'Gagal menghapus jadwal kuliah.');
        }
        
        redirect('Jadwal_kuliah');
    }

    /**
     * AJAX: Get mapping berdasarkan kelas (semua mapping, dengan status sudah dijadwalkan atau belum)
     */
    public function get_mapping_by_kelas() {
        $this->json_deny_kaprodi();
        // Clear all output buffers
        while (ob_get_level()) {
            ob_end_clean();
        }
        
        // Set JSON header
        header('Content-Type: application/json; charset=utf-8');
        
        $response = array('success' => false, 'message' => '', 'data' => array());
        
        try {
            $kelas = $this->input->get_post('kelas');
            
            if (empty($kelas)) {
                $response['message'] = 'Kelas tidak boleh kosong';
                echo json_encode($response, JSON_UNESCAPED_UNICODE);
                exit;
            }
            
            $tahun_aktif = $this->Tahun_akademik_model->get_aktif();
            
            if (!$tahun_aktif) {
                $response['message'] = 'Tidak ada tahun akademik aktif';
                echo json_encode($response, JSON_UNESCAPED_UNICODE);
                exit;
            }
            
            // Ambil semua mapping untuk kelas tersebut
            $mapping_list = $this->Jadwal_kuliah_model->get_mapping_by_kelas($kelas, $tahun_aktif->id);
            
            if (!$mapping_list) {
                $mapping_list = array();
            }
            
            // Ambil mapping yang sudah dijadwalkan
            $this->db->select('id_mapping_dosen_mk');
            $this->db->from('jadwal_kuliah');
            $this->db->where('kelas', $kelas);
            $this->db->where('id_tahun_akademik', $tahun_aktif->id);
            $query_scheduled = $this->db->get();
            
            $scheduled_ids = array();
            if ($query_scheduled) {
                $scheduled = $query_scheduled->result();
                foreach ($scheduled as $s) {
                    if (isset($s->id_mapping_dosen_mk) && $s->id_mapping_dosen_mk) {
                        $scheduled_ids[] = $s->id_mapping_dosen_mk;
                    }
                }
            }
            
            // Ambil detail jadwal untuk yang sudah dijadwalkan
            $this->db->select('jk.id_mapping_dosen_mk, jk.hari, jk.jam_mulai, jk.jam_selesai, jk.id_ruang_kelas, rk.kode_ruang, rk.nama_ruang, jk.id as jadwal_id');
            $this->db->from('jadwal_kuliah jk');
            $this->db->join('ruang_kelas rk', 'rk.id = jk.id_ruang_kelas', 'left');
            $this->db->where('jk.kelas', $kelas);
            $this->db->where('jk.id_tahun_akademik', $tahun_aktif->id);
            $this->db->order_by('jk.id_mapping_dosen_mk', 'ASC');
            $this->db->order_by('jk.hari', 'ASC');
            $this->db->order_by('jk.jam_mulai', 'ASC');
            $query_jadwal = $this->db->get();
            
            $jadwal_detail = array();
            if ($query_jadwal) {
                $jadwal_detail = $query_jadwal->result();
            }
            
            // Group jadwal by mapping_id
            $jadwal_map = array();
            foreach ($jadwal_detail as $jd) {
                if (isset($jd->id_mapping_dosen_mk) && $jd->id_mapping_dosen_mk) {
                    if (!isset($jadwal_map[$jd->id_mapping_dosen_mk])) {
                        $jadwal_map[$jd->id_mapping_dosen_mk] = array();
                    }
                    $jadwal_map[$jd->id_mapping_dosen_mk][] = $jd;
                }
            }
            
            $data_array = array();
            foreach ($mapping_list as $m) {
                if (!isset($m->id) || !$m->id) {
                    continue;
                }
                
                $is_scheduled = in_array($m->id, $scheduled_ids);
                $jadwal_list = isset($jadwal_map[$m->id]) ? $jadwal_map[$m->id] : array();
                
                // Convert jadwal list ke format array
                $jadwal_array = array();
                foreach ($jadwal_list as $jd) {
                    $jadwal_array[] = array(
                        'hari' => isset($jd->hari) ? $jd->hari : '',
                        'jam_mulai' => isset($jd->jam_mulai) ? $jd->jam_mulai : '',
                        'jam_selesai' => isset($jd->jam_selesai) ? $jd->jam_selesai : '',
                        'ruang' => (isset($jd->kode_ruang) && $jd->kode_ruang ? $jd->kode_ruang . ' - ' : '') . (isset($jd->nama_ruang) && $jd->nama_ruang ? $jd->nama_ruang : ''),
                        'id_ruang_kelas' => isset($jd->id_ruang_kelas) ? $jd->id_ruang_kelas : null
                    );
                }
                
                $data_array[] = array(
                    'id' => isset($m->id) ? $m->id : '',
                    'kode_mk' => isset($m->kode_mk) ? $m->kode_mk : '',
                    'nama_mk' => isset($m->nama_mk) ? $m->nama_mk : '',
                    'sks' => isset($m->sks) ? (int)$m->sks : 0,
                    'nidn' => isset($m->nidn) ? $m->nidn : '',
                    'nama_dosen' => isset($m->nama_dosen) ? $m->nama_dosen : '',
                    'is_scheduled' => $is_scheduled,
                    'jumlah_pertemuan' => count($jadwal_array),
                    'jadwal' => $jadwal_array
                );
            }
            
            $response['success'] = true;
            $response['data'] = $data_array;
            
        } catch (Exception $e) {
            log_message('error', 'Error in get_mapping_by_kelas: ' . $e->getMessage() . ' | Trace: ' . $e->getTraceAsString());
            $response['message'] = 'Terjadi kesalahan: ' . $e->getMessage();
        } catch (Error $e) {
            log_message('error', 'Fatal error in get_mapping_by_kelas: ' . $e->getMessage() . ' | Trace: ' . $e->getTraceAsString());
            $response['message'] = 'Terjadi kesalahan sistem';
        }
        
        echo json_encode($response, JSON_UNESCAPED_UNICODE);
        exit;
    }

    /**
     * AJAX: Calculate jam selesai berdasarkan jam mulai dan SKS
     */
    public function calculate_jam_selesai() {
        $this->json_deny_kaprodi();
        if (ob_get_level()) {
            ob_clean();
        }
        
        header('Content-Type: application/json; charset=utf-8');
        
        try {
            $jam_mulai = $this->input->post('jam_mulai');
            $sks = $this->input->post('sks');
            
            if (empty($jam_mulai) || empty($sks)) {
                echo json_encode(array('success' => false, 'message' => 'Jam mulai dan SKS harus diisi'));
                exit;
            }
            
            $jam_selesai = $this->Jadwal_kuliah_model->calculate_jam_selesai($jam_mulai, $sks);
            
            if ($jam_selesai) {
                echo json_encode(array('success' => true, 'jam_selesai' => $jam_selesai));
            } else {
                echo json_encode(array('success' => false, 'message' => 'Gagal menghitung jam selesai'));
            }
            exit;
            
        } catch (Exception $e) {
            echo json_encode(array('success' => false, 'message' => 'Terjadi kesalahan: ' . $e->getMessage()));
            exit;
        }
    }

    /**
     * Batch create jadwal per kelas
     */
    public function batch_create() {
        $this->assert_not_kaprodi('Jadwal_kuliah');
        // Ambil tahun akademik aktif
        $tahun_aktif = $this->Tahun_akademik_model->get_aktif();
        
        if (!$tahun_aktif) {
            $this->session->set_flashdata('error', 'Tidak ada tahun akademik aktif. Silakan aktifkan tahun akademik terlebih dahulu.');
            redirect('Jadwal_kuliah');
        }

        $kelas = $this->input->post('kelas');
        $jadwal_data = $this->input->post('jadwal');

        if (empty($kelas) || empty($jadwal_data)) {
            $this->session->set_flashdata('error', 'Data jadwal tidak lengkap.');
            redirect('Jadwal_kuliah/create');
        }

        $success_count = 0;
        $error_count = 0;
        $errors = array();

        foreach ($jadwal_data as $mapping_id => $jadwal) {
            // Skip jika sudah dijadwalkan (hanya ada hidden field, tidak ada input form)
            // Cek apakah ada id_ruang_kelas yang diisi (jika kosong berarti sudah dijadwalkan)
            if (empty($jadwal['id_ruang_kelas'])) {
                continue; // Skip yang sudah dijadwalkan
            }
            
            // Validasi data
            if (empty($jadwal['hari']) || empty($jadwal['jam_mulai'])) {
                $error_count++;
                continue;
            }

            $jam_mulai = $jadwal['jam_mulai'];
            $jam_selesai = !empty($jadwal['jam_selesai']) ? $jadwal['jam_selesai'] : null;
            $hari = $jadwal['hari'];
            $id_ruang_kelas = $jadwal['id_ruang_kelas'];
            $keterangan = isset($jadwal['keterangan']) ? $jadwal['keterangan'] : '';

            // Ambil SKS dari mapping
            $this->load->model('Mapping_dosen_mk_model');
            $mapping = $this->Mapping_dosen_mk_model->get_by_id($mapping_id);
            
            if (!$mapping) {
                $error_count++;
                continue;
            }

            // Jika jam_selesai tidak diisi, hitung otomatis
            if (empty($jam_selesai)) {
                $sks = isset($mapping->sks) ? $mapping->sks : 0;
                $jam_selesai = $this->Jadwal_kuliah_model->calculate_jam_selesai($jam_mulai, $sks);
            }

            // Validasi bentrok
            $has_error = false;
            
            // 1. Cek bentrok ruang kelas
            if ($this->Jadwal_kuliah_model->check_bentrok_ruang($id_ruang_kelas, $hari, $jam_mulai, $jam_selesai, $tahun_aktif->id)) {
                $ruang = $this->Ruang_kelas_model->get_by_id($id_ruang_kelas);
                $errors[] = "Ruang kelas " . ($ruang ? $ruang->nama_ruang : '') . " bentrok untuk " . ($mapping->kode_mk ?? 'MK') . " pada hari dan waktu yang sama.";
                $has_error = true;
            }
            
            // 2. Cek bentrok dosen
            if ($this->Jadwal_kuliah_model->check_bentrok_dosen($mapping_id, $hari, $jam_mulai, $jam_selesai, $tahun_aktif->id)) {
                $errors[] = "Dosen bentrok untuk " . ($mapping->kode_mk ?? 'MK') . " pada hari dan waktu yang sama.";
                $has_error = true;
            }
            
            // 3. Cek bentrok kelas
            if ($this->Jadwal_kuliah_model->check_bentrok_kelas($kelas, $hari, $jam_mulai, $jam_selesai, $tahun_aktif->id)) {
                $errors[] = "Kelas " . $kelas . " bentrok untuk " . ($mapping->kode_mk ?? 'MK') . " pada hari dan waktu yang sama.";
                $has_error = true;
            }

            if ($has_error) {
                $error_count++;
                continue;
            }

            // Generate kode jadwal
            $kode_jadwal = 'JDW-' . date('Ymd') . '-' . strtoupper($hari) . '-' . str_replace(':', '', $jam_mulai) . '-' . $mapping_id;
            
            $data_jadwal = array(
                'kode_jadwal' => $kode_jadwal,
                'id_mapping_dosen_mk' => $mapping_id,
                'kelas' => $kelas,
                'hari' => $hari,
                'jam_mulai' => $jam_mulai,
                'jam_selesai' => $jam_selesai,
                'id_ruang_kelas' => $id_ruang_kelas,
                'id_tahun_akademik' => $tahun_aktif->id,
                'keterangan' => $keterangan
            );

            // Insert jadwal
            if ($this->Jadwal_kuliah_model->create($data_jadwal)) {
                $success_count++;
            } else {
                $error_count++;
            }
        }

        if ($success_count > 0) {
            $message = "Berhasil menyimpan {$success_count} jadwal.";
            if ($error_count > 0) {
                $message .= " Gagal menyimpan {$error_count} jadwal.";
            }
            if (!empty($errors)) {
                $message .= "<br>" . implode("<br>", array_slice($errors, 0, 5));
                if (count($errors) > 5) {
                    $message .= "<br>... dan " . (count($errors) - 5) . " error lainnya.";
                }
            }
            $this->session->set_flashdata('success', $message);
        } else {
            $message = "Gagal menyimpan semua jadwal.";
            if (!empty($errors)) {
                $message .= "<br>" . implode("<br>", array_slice($errors, 0, 10));
            }
            $this->session->set_flashdata('error', $message);
        }

        redirect('Jadwal_kuliah');
    }

    /**
     * Form tambah jadwal pemadatan
     * Setiap mata kuliah harus dijadwalkan 2x di hari berbeda
     */
    public function create_pemadatan() {
        $this->assert_not_kaprodi('Jadwal_kuliah');
        // Ambil tahun akademik aktif
        $tahun_aktif = $this->Tahun_akademik_model->get_aktif();
        
        if (!$tahun_aktif) {
            $this->session->set_flashdata('error', 'Tidak ada tahun akademik aktif. Silakan aktifkan tahun akademik terlebih dahulu.');
            redirect('Jadwal_kuliah');
        }

        $data['ruang_list'] = $this->Ruang_kelas_model->get_all_active();
        $data['kelas_list'] = $this->Jadwal_kuliah_model->get_kelas_with_mapping($tahun_aktif->id);
        $data['tahun_aktif'] = $tahun_aktif;
        $data['title'] = 'Tambah Jadwal Pemadatan';
        $data['page_title'] = 'Tambah Jadwal Pemadatan';
        $data['breadcrumb'] = array(
            array('label' => 'Jadwal Kuliah', 'url' => site_url('Jadwal_kuliah')),
            array('label' => 'Tambah Jadwal Pemadatan', 'active' => true)
        );
        $data['sidebar_menu'] = generate_sidebar_menu(get_user_role());
        $data['content'] = $this->load->view('jadwal_kuliah/create_pemadatan', $data, true);
        $this->load->view('layouts/main', $data);
    }

    /**
     * Process tambah jadwal pemadatan
     * Setiap mata kuliah harus dijadwalkan 2x di hari berbeda
     */
    public function process_create_pemadatan() {
        $this->assert_not_kaprodi('Jadwal_kuliah');
        // Ambil tahun akademik aktif
        $tahun_aktif = $this->Tahun_akademik_model->get_aktif();
        
        if (!$tahun_aktif) {
            $this->session->set_flashdata('error', 'Tidak ada tahun akademik aktif. Silakan aktifkan tahun akademik terlebih dahulu.');
            redirect('Jadwal_kuliah');
        }

        $kelas = $this->input->post('kelas');
        $jadwal_data = $this->input->post('jadwal');

        if (empty($kelas) || empty($jadwal_data)) {
            $this->session->set_flashdata('error', 'Data jadwal tidak lengkap.');
            redirect('Jadwal_kuliah/create_pemadatan');
        }

        // Ambil jadwal yang sudah ada untuk setiap mapping
        $this->db->select('jk.id_mapping_dosen_mk, jk.hari, jk.jam_mulai, jk.jam_selesai, jk.id_ruang_kelas');
        $this->db->from('jadwal_kuliah jk');
        $this->db->where('jk.kelas', $kelas);
        $this->db->where('jk.id_tahun_akademik', $tahun_aktif->id);
        $this->db->order_by('jk.id_mapping_dosen_mk', 'ASC');
        $this->db->order_by('jk.hari', 'ASC');
        $existing_jadwal = $this->db->get()->result();
        
        // Group existing jadwal by mapping_id
        $existing_jadwal_map = array();
        foreach ($existing_jadwal as $ej) {
            if (!isset($existing_jadwal_map[$ej->id_mapping_dosen_mk])) {
                $existing_jadwal_map[$ej->id_mapping_dosen_mk] = array();
            }
            $existing_jadwal_map[$ej->id_mapping_dosen_mk][] = $ej;
        }
        
        // Convert format pertemuan_1 dan pertemuan_2 menjadi format flat
        $jadwal_flat = array();
        $errors = array();
        
        foreach ($jadwal_data as $mapping_id => $jadwal) {
            $pertemuan1 = isset($jadwal['pertemuan_1']) ? $jadwal['pertemuan_1'] : null;
            $pertemuan2 = isset($jadwal['pertemuan_2']) ? $jadwal['pertemuan_2'] : null;
            
            // Cek jadwal yang sudah ada untuk mapping ini
            $existing_count = isset($existing_jadwal_map[$mapping_id]) ? count($existing_jadwal_map[$mapping_id]) : 0;
            
            // Jika sudah ada 2 pertemuan, skip
            if ($existing_count >= 2) {
                continue;
            }
            
            // Validasi: harus ada pertemuan_2 jika belum ada 2 pertemuan
            if (!$pertemuan2 || empty($pertemuan2['hari']) || empty($pertemuan2['jam_mulai']) || empty($pertemuan2['id_ruang_kelas'])) {
                $this->load->model('Mapping_dosen_mk_model');
                $mapping = $this->Mapping_dosen_mk_model->get_by_id($mapping_id);
                $mk_name = $mapping ? ($mapping->kode_mk . ' - ' . $mapping->nama_mk) : 'Mata Kuliah';
                if ($existing_count == 0) {
                    $errors[] = $mk_name . " harus memiliki 2 pertemuan. Pertemuan 2 belum diisi.";
                } else {
                    $errors[] = $mk_name . " perlu 1 pertemuan lagi. Pertemuan 2 belum diisi.";
                }
                continue;
            }
            
            // Jika sudah ada 1 pertemuan, ambil hari dari jadwal yang sudah ada
            $hari_pertemuan1 = null;
            if ($existing_count == 1 && isset($existing_jadwal_map[$mapping_id][0])) {
                $hari_pertemuan1 = $existing_jadwal_map[$mapping_id][0]->hari;
            } elseif ($pertemuan1 && !empty($pertemuan1['hari'])) {
                $hari_pertemuan1 = $pertemuan1['hari'];
            }
            
            // Validasi: 2 pertemuan tidak boleh di hari yang sama
            if ($hari_pertemuan1 && !empty($pertemuan2['hari']) && $hari_pertemuan1 == $pertemuan2['hari']) {
                $this->load->model('Mapping_dosen_mk_model');
                $mapping = $this->Mapping_dosen_mk_model->get_by_id($mapping_id);
                $mk_name = $mapping ? ($mapping->kode_mk . ' - ' . $mapping->nama_mk) : 'Mata Kuliah';
                $errors[] = $mk_name . " harus dijadwalkan 2x pertemuan di hari berbeda. Saat ini kedua pertemuan di hari " . ucfirst($hari_pertemuan1) . ".";
                continue;
            }
            
            // Tambahkan pertemuan 1 hanya jika belum ada
            if ($existing_count == 0) {
                if ($pertemuan1 && !empty($pertemuan1['hari']) && !empty($pertemuan1['jam_mulai']) && !empty($pertemuan1['id_ruang_kelas'])) {
                    $jadwal_flat[] = array(
                        'mapping_id' => $mapping_id,
                        'hari' => $pertemuan1['hari'],
                        'jam_mulai' => $pertemuan1['jam_mulai'],
                        'jam_selesai' => !empty($pertemuan1['jam_selesai']) ? $pertemuan1['jam_selesai'] : null,
                        'id_ruang_kelas' => $pertemuan1['id_ruang_kelas'],
                        'pertemuan' => 1
                    );
                }
            }
            
            // Tambahkan pertemuan 2 (selalu insert karena belum ada 2 pertemuan)
            if (!empty($pertemuan2['hari']) && !empty($pertemuan2['jam_mulai']) && !empty($pertemuan2['id_ruang_kelas'])) {
                $jadwal_flat[] = array(
                    'mapping_id' => $mapping_id,
                    'hari' => $pertemuan2['hari'],
                    'jam_mulai' => $pertemuan2['jam_mulai'],
                    'jam_selesai' => !empty($pertemuan2['jam_selesai']) ? $pertemuan2['jam_selesai'] : null,
                    'id_ruang_kelas' => $pertemuan2['id_ruang_kelas'],
                    'pertemuan' => 2
                );
            }
        }
        
        if (!empty($errors)) {
            $this->session->set_flashdata('error', implode('<br>', $errors));
            redirect('Jadwal_kuliah/create_pemadatan');
        }
        
        if (empty($jadwal_flat)) {
            $this->session->set_flashdata('error', 'Tidak ada jadwal yang valid untuk disimpan.');
            redirect('Jadwal_kuliah/create_pemadatan');
        }

        $success_count = 0;
        $error_count = 0;
        $bentrok_errors = array();

        foreach ($jadwal_flat as $jadwal) {
            $mapping_id = $jadwal['mapping_id'];
            $jam_mulai = $jadwal['jam_mulai'];
            $jam_selesai = $jadwal['jam_selesai'];
            $hari = $jadwal['hari'];
            $id_ruang_kelas = $jadwal['id_ruang_kelas'];
            $pertemuan = $jadwal['pertemuan'];

            // Ambil SKS dari mapping
            $this->load->model('Mapping_dosen_mk_model');
            $mapping = $this->Mapping_dosen_mk_model->get_by_id($mapping_id);
            
            if (!$mapping) {
                $error_count++;
                continue;
            }

            // Jika jam_selesai tidak diisi, hitung otomatis
            if (empty($jam_selesai)) {
                $sks = isset($mapping->sks) ? $mapping->sks : 0;
                $jam_selesai = $this->Jadwal_kuliah_model->calculate_jam_selesai($jam_mulai, $sks);
            }

            // Validasi bentrok
            $has_error = false;
            
            // 1. Cek bentrok ruang kelas
            if ($this->Jadwal_kuliah_model->check_bentrok_ruang($id_ruang_kelas, $hari, $jam_mulai, $jam_selesai, $tahun_aktif->id)) {
                $ruang = $this->Ruang_kelas_model->get_by_id($id_ruang_kelas);
                $bentrok_errors[] = "Ruang kelas " . ($ruang ? $ruang->nama_ruang : '') . " bentrok untuk " . ($mapping->kode_mk ?? 'MK') . " pertemuan " . $pertemuan . " pada hari dan waktu yang sama.";
                $has_error = true;
            }
            
            // 2. Cek bentrok dosen
            if ($this->Jadwal_kuliah_model->check_bentrok_dosen($mapping_id, $hari, $jam_mulai, $jam_selesai, $tahun_aktif->id)) {
                $bentrok_errors[] = "Dosen bentrok untuk " . ($mapping->kode_mk ?? 'MK') . " pertemuan " . $pertemuan . " pada hari dan waktu yang sama.";
                $has_error = true;
            }
            
            // 3. Cek bentrok kelas
            if ($this->Jadwal_kuliah_model->check_bentrok_kelas($kelas, $hari, $jam_mulai, $jam_selesai, $tahun_aktif->id)) {
                $bentrok_errors[] = "Kelas " . $kelas . " bentrok untuk " . ($mapping->kode_mk ?? 'MK') . " pertemuan " . $pertemuan . " pada hari dan waktu yang sama.";
                $has_error = true;
            }

            if ($has_error) {
                $error_count++;
                continue;
            }

            // Generate kode jadwal
            $kode_jadwal = 'JDW-PEM-' . date('Ymd') . '-' . strtoupper($hari) . '-' . str_replace(':', '', $jam_mulai) . '-' . $mapping_id . '-P' . $pertemuan;
            
            $data_jadwal = array(
                'kode_jadwal' => $kode_jadwal,
                'id_mapping_dosen_mk' => $mapping_id,
                'kelas' => $kelas,
                'hari' => $hari,
                'jam_mulai' => $jam_mulai,
                'jam_selesai' => $jam_selesai,
                'id_ruang_kelas' => $id_ruang_kelas,
                'id_tahun_akademik' => $tahun_aktif->id,
                'keterangan' => 'Jadwal Pemadatan - Pertemuan ' . $pertemuan
            );

            // Insert jadwal
            if ($this->Jadwal_kuliah_model->create($data_jadwal)) {
                $success_count++;
            } else {
                $error_count++;
            }
        }

        if ($success_count > 0) {
            $message = "Berhasil menyimpan {$success_count} jadwal pemadatan.";
            if ($error_count > 0) {
                $message .= " Gagal menyimpan {$error_count} jadwal.";
            }
            if (!empty($bentrok_errors)) {
                $message .= "<br>" . implode("<br>", array_slice($bentrok_errors, 0, 5));
                if (count($bentrok_errors) > 5) {
                    $message .= "<br>... dan " . (count($bentrok_errors) - 5) . " error lainnya.";
                }
            }
            $this->session->set_flashdata('success', $message);
        } else {
            $message = "Gagal menyimpan semua jadwal pemadatan.";
            if (!empty($bentrok_errors)) {
                $message .= "<br>" . implode("<br>", array_slice($bentrok_errors, 0, 10));
            }
            $this->session->set_flashdata('error', $message);
        }

        redirect('Jadwal_kuliah');
    }

    /**
     * Form generate jadwal otomatis
     */
    public function generate_otomatis() {
        $this->assert_not_kaprodi('Jadwal_kuliah');
        // Ambil tahun akademik aktif
        $tahun_aktif = $this->Tahun_akademik_model->get_aktif();
        
        if (!$tahun_aktif) {
            $this->session->set_flashdata('error', 'Tidak ada tahun akademik aktif. Silakan aktifkan tahun akademik terlebih dahulu.');
            redirect('Jadwal_kuliah');
        }

        $data['kelas_list'] = $this->Jadwal_kuliah_model->get_kelas_with_mapping($tahun_aktif->id);
        $data['tahun_aktif'] = $tahun_aktif;
        $data['title'] = 'Generate Jadwal Otomatis';
        $data['page_title'] = 'Generate Jadwal Otomatis';
        $data['breadcrumb'] = array(
            array('label' => 'Jadwal Kuliah', 'url' => site_url('Jadwal_kuliah')),
            array('label' => 'Generate Otomatis', 'active' => true)
        );
        $data['sidebar_menu'] = generate_sidebar_menu(get_user_role());
        $data['content'] = $this->load->view('jadwal_kuliah/generate_otomatis', $data, true);
        $this->load->view('layouts/main', $data);
    }

    /**
     * AJAX: Preview generate jadwal
     */
    public function preview_generate() {
        $this->json_deny_kaprodi();
        if (ob_get_level()) {
            ob_clean();
        }
        
        header('Content-Type: application/json; charset=utf-8');
        
        try {
            $kelas = $this->input->get_post('kelas');
            $tahun_aktif = $this->Tahun_akademik_model->get_aktif();
            
            if (empty($kelas) || !$tahun_aktif) {
                echo json_encode(array('success' => false, 'message' => 'Kelas atau tahun akademik tidak valid'));
                exit;
            }

            $max_mk_per_hari = $this->input->get_post('max_mk_per_hari') ?: 2;
            
            // Generate preview (tanpa save)
            $result = $this->generate_jadwal_logic($kelas, $tahun_aktif->id, $max_mk_per_hari, false);

            if (empty($result['success'])) {
                echo json_encode(array(
                    'success' => false,
                    'message' => isset($result['message']) ? $result['message'] : 'Gagal membuat preview jadwal.',
                    'data' => array()
                ));
                exit;
            }
            
            echo json_encode(array(
                'success' => true,
                'data' => isset($result['preview']) && is_array($result['preview']) ? $result['preview'] : array(),
                'message' => isset($result['message']) ? $result['message'] : null
            ));
            exit;
            
        } catch (Exception $e) {
            echo json_encode(array('success' => false, 'message' => 'Terjadi kesalahan: ' . $e->getMessage()));
            exit;
        } catch (Throwable $e) {
            echo json_encode(array('success' => false, 'message' => 'Terjadi kesalahan: ' . $e->getMessage()));
            exit;
        }
    }

    /**
     * Process generate jadwal otomatis
     */
    public function process_generate() {
        $this->assert_not_kaprodi('Jadwal_kuliah');
        // Ambil tahun akademik aktif
        $tahun_aktif = $this->Tahun_akademik_model->get_aktif();
        
        if (!$tahun_aktif) {
            $this->session->set_flashdata('error', 'Tidak ada tahun akademik aktif. Silakan aktifkan tahun akademik terlebih dahulu.');
            redirect('Jadwal_kuliah');
        }

        $kelas = $this->input->post('kelas');
        $max_mk_per_hari = $this->input->post('max_mk_per_hari') ?: 2;
        $overwrite = $this->input->post('overwrite') == '1';

        if (empty($kelas)) {
            $this->session->set_flashdata('error', 'Kelas harus dipilih.');
            redirect('Jadwal_kuliah/generate_otomatis');
        }

        // Jika overwrite, hapus jadwal yang sudah ada untuk kelas ini
        if ($overwrite) {
            $this->db->where('kelas', $kelas);
            $this->db->where('id_tahun_akademik', $tahun_aktif->id);
            $this->db->delete('jadwal_kuliah');
        }

        // Generate jadwal
        $result = $this->generate_jadwal_logic($kelas, $tahun_aktif->id, $max_mk_per_hari, true);

        if ($result['success']) {
            $message = "Berhasil generate {$result['success_count']} jadwal untuk kelas {$kelas}.";
            if ($result['error_count'] > 0) {
                $message .= " Gagal generate {$result['error_count']} jadwal.";
            }
            $this->session->set_flashdata('success', $message);
        } else {
            $this->session->set_flashdata('error', $result['message']);
        }

        redirect('Jadwal_kuliah');
    }

    /**
     * Logic generate jadwal otomatis
     */
    private function generate_jadwal_logic($kelas, $id_tahun_akademik, $max_mk_per_hari = 2, $save = true) {
        // Ambil semua mapping yang belum dijadwalkan untuk kelas tersebut
        $mapping_list = $this->Jadwal_kuliah_model->get_mapping_by_kelas_available($kelas, $id_tahun_akademik);
        
        if (empty($mapping_list)) {
            return array(
                'success' => false,
                'message' => 'Tidak ada mata kuliah yang perlu dijadwalkan untuk kelas ' . $kelas . '.'
            );
        }

        // Ambil semua ruang aktif
        $ruang_list = $this->Ruang_kelas_model->get_all_active();
        if (empty($ruang_list)) {
            return array(
                'success' => false,
                'message' => 'Tidak ada ruang kelas yang aktif. Silakan tambahkan ruang kelas terlebih dahulu.'
            );
        }

        // Hari yang tersedia (Senin - Jumat)
        $hari_list = array('senin', 'selasa', 'rabu', 'kamis', 'jumat');
        
        // Jam mulai default
        $jam_mulai_default = '08:00:00';
        $jam_mulai_siang = '13:00:00';
        $jam_selesai_max = '17:30:00';

        // Distribusi mata kuliah ke hari
        $jadwal_per_hari = array();
        foreach ($hari_list as $hari) {
            $jadwal_per_hari[$hari] = array();
        }

        // Shuffle mapping untuk distribusi acak
        shuffle($mapping_list);

        // Distribusikan mata kuliah ke hari
        $hari_index = 0;
        foreach ($mapping_list as $mapping) {
            $hari = $hari_list[$hari_index % count($hari_list)];
            
            // Cek apakah hari ini sudah mencapai maksimal
            if (count($jadwal_per_hari[$hari]) >= $max_mk_per_hari) {
                // Cari hari lain yang belum penuh
                $found = false;
                foreach ($hari_list as $h) {
                    if (count($jadwal_per_hari[$h]) < $max_mk_per_hari) {
                        $hari = $h;
                        $found = true;
                        break;
                    }
                }
                if (!$found) {
                    // Semua hari penuh, tetap masukkan ke hari ini
                    $hari = $hari_list[$hari_index % count($hari_list)];
                }
            }
            
            $jadwal_per_hari[$hari][] = $mapping;
            $hari_index++;
        }

        // Generate jadwal untuk setiap hari
        $success_count = 0;
        $error_count = 0;
        $preview = array();
        
        // Track semua jadwal yang sudah dibuat dalam proses generate ini (untuk cek bentrok)
        // IMPORTANT: Track ini digunakan untuk cek bentrok ruang dan dosen untuk SEMUA kelas
        // Format: ['hari' => [['jam_mulai' => ..., 'jam_selesai' => ..., 'id_ruang' => ..., 'id_dosen' => ..., 'kelas' => ...], ...]]
        $jadwal_generated = array();

        foreach ($hari_list as $hari) {
            $mata_kuliah_hari = $jadwal_per_hari[$hari];
            
            if (empty($mata_kuliah_hari)) {
                continue;
            }

            // Sort berdasarkan SKS (prioritaskan yang SKS lebih besar untuk jam awal)
            usort($mata_kuliah_hari, function($a, $b) {
                return ($b->sks ?? 0) - ($a->sks ?? 0);
            });

            // Generate jadwal untuk hari ini
            $ruang_used_today = array(); // Track ruang yang sudah digunakan hari ini

            // Track jadwal yang sudah dibuat hari ini untuk perhitungan jam
            $jadwal_hari_ini = array();
            
            foreach ($mata_kuliah_hari as $index => $mk) {
                $sks = $mk->sks ?? 0;
                $durasi_menit = $sks * 50;
                
                // Tentukan jam mulai berdasarkan prioritas
                if ($index == 0) {
                    // Mata kuliah pertama: 08:00
                    $jam_mulai = $jam_mulai_default;
                } elseif ($index == 1 && count($mata_kuliah_hari) == 2) {
                    // Mata kuliah kedua jika hanya 2 MK: 13:00
                    $jam_mulai = $jam_mulai_siang;
                } elseif ($index == 1 && count($mata_kuliah_hari) >= 3) {
                    // Mata kuliah kedua jika 3 MK: 30 menit setelah MK pertama selesai
                    $mk_pertama = $mata_kuliah_hari[0];
                    $sks_pertama = $mk_pertama->sks ?? 0;
                    $durasi_pertama = $sks_pertama * 50;
                    $jam_mulai_pertama = strtotime($jam_mulai_default);
                    $jam_selesai_pertama = strtotime("+{$durasi_pertama} minutes", $jam_mulai_pertama);
                    $jam_mulai = date('H:i:s', strtotime('+30 minutes', $jam_selesai_pertama));
                } else {
                    // Mata kuliah ketiga atau lebih: 30 menit setelah MK sebelumnya selesai
                    $mk_sebelumnya = $mata_kuliah_hari[$index - 1];
                    $sks_sebelumnya = $mk_sebelumnya->sks ?? 0;
                    $durasi_sebelumnya = $sks_sebelumnya * 50;
                    
                    // Ambil jam mulai dan selesai dari jadwal sebelumnya yang sudah dihitung
                    if (isset($jadwal_hari_ini[$index - 1])) {
                        $jam_selesai_sebelumnya = strtotime($jadwal_hari_ini[$index - 1]['jam_selesai']);
                    } else {
                        // Fallback: hitung dari default
                        $jam_mulai_sebelumnya = strtotime($jam_mulai_default);
                        $jam_selesai_sebelumnya = strtotime("+{$durasi_sebelumnya} minutes", $jam_mulai_sebelumnya);
                    }
                    
                    $jam_mulai = date('H:i:s', strtotime('+30 minutes', $jam_selesai_sebelumnya));
                }

                // Hitung jam selesai
                $jam_mulai_timestamp = strtotime($jam_mulai);
                $jam_selesai_timestamp = strtotime("+{$durasi_menit} minutes", $jam_mulai_timestamp);
                $jam_selesai = date('H:i:s', $jam_selesai_timestamp);

                // Cek apakah melebihi jam maksimal
                if (strtotime($jam_selesai) > strtotime($jam_selesai_max)) {
                    $error_count++;
                    continue;
                }

                // Pilih ruang secara random (hindari yang sudah digunakan hari ini, dan yang tidak bentrok)
                // IMPORTANT: Cek bentrok ruang mengecek SEMUA kelas, bukan hanya kelas yang sama
                // Ruang tidak boleh digunakan oleh kelas berbeda pada waktu yang sama
                $jadwal_model = $this->Jadwal_kuliah_model;
                $available_ruang = array_filter($ruang_list, function($r) use ($hari, $jam_mulai, $jam_selesai, $id_tahun_akademik, $jadwal_model, $jadwal_generated) {
                    // 1. Cek bentrok dengan jadwal yang sudah ada di database (untuk SEMUA kelas)
                    // Method check_bentrok_ruang sudah mengecek semua kelas di database
                    if ($jadwal_model->check_bentrok_ruang($r->id, $hari, $jam_mulai, $jam_selesai, $id_tahun_akademik)) {
                        return false;
                    }
                    // 2. Cek bentrok dengan jadwal yang sudah dibuat dalam proses generate ini (untuk SEMUA kelas)
                    // Track jadwal_generated mencatat semua jadwal yang dibuat dalam proses generate
                    // Ini penting untuk mencegah bentrok saat generate multiple kelas sekaligus
                    if (isset($jadwal_generated[$hari])) {
                        foreach ($jadwal_generated[$hari] as $j) {
                            if ($j['id_ruang'] == $r->id) {
                                // Cek overlap waktu (untuk SEMUA kelas - tidak peduli kelas apa)
                                if (strtotime($jam_mulai) < strtotime($j['jam_selesai']) && 
                                    strtotime($jam_selesai) > strtotime($j['jam_mulai'])) {
                                    return false; // Bentrok - ruang sudah digunakan oleh kelas lain
                                }
                            }
                        }
                    }
                    return true;
                });

                // Jika tidak ada ruang yang available, skip mata kuliah ini
                if (empty($available_ruang)) {
                    $error_count++;
                    continue;
                }

                // Pilih ruang secara random dari yang available
                $ruang = $available_ruang[array_rand($available_ruang)];
                $ruang_used_today[] = $ruang->id;

                // Ambil id_dosen dari mapping untuk cek bentrok
                $this->load->model('Mapping_dosen_mk_model');
                $mapping_detail = $this->Mapping_dosen_mk_model->get_by_id($mk->id);
                $id_dosen = $mapping_detail ? $mapping_detail->id_dosen : null;

                // Validasi bentrok (ruang sudah dicek saat pemilihan)
                $has_error = false;
                
                // 1. Cek bentrok dosen dengan jadwal yang sudah ada di database
                if ($this->Jadwal_kuliah_model->check_bentrok_dosen($mk->id, $hari, $jam_mulai, $jam_selesai, $id_tahun_akademik)) {
                    $has_error = true;
                }
                
                // 2. Cek bentrok dosen dengan jadwal yang sudah dibuat dalam proses generate ini
                // IMPORTANT: Cek untuk SEMUA kelas, bukan hanya kelas yang sama
                // Dosen tidak boleh mengajar di kelas berbeda pada waktu yang sama
                if (!$has_error && $id_dosen && isset($jadwal_generated[$hari])) {
                    foreach ($jadwal_generated[$hari] as $j) {
                        if ($j['id_dosen'] == $id_dosen) {
                            // Cek overlap waktu (untuk SEMUA kelas)
                            if (strtotime($jam_mulai) < strtotime($j['jam_selesai']) && 
                                strtotime($jam_selesai) > strtotime($j['jam_mulai'])) {
                                $has_error = true;
                                break;
                            }
                        }
                    }
                }
                
                // 3. Cek bentrok kelas dengan jadwal yang sudah ada di database
                if (!$has_error && $this->Jadwal_kuliah_model->check_bentrok_kelas($kelas, $hari, $jam_mulai, $jam_selesai, $id_tahun_akademik)) {
                    $has_error = true;
                }
                
                // 4. Cek bentrok kelas dengan jadwal yang sudah dibuat dalam proses generate ini
                if (!$has_error && isset($jadwal_generated[$hari])) {
                    foreach ($jadwal_generated[$hari] as $j) {
                        if ($j['kelas'] == $kelas) {
                            // Cek overlap waktu
                            if (strtotime($jam_mulai) < strtotime($j['jam_selesai']) && 
                                strtotime($jam_selesai) > strtotime($j['jam_mulai'])) {
                                $has_error = true;
                                break;
                            }
                        }
                    }
                }

                if ($has_error) {
                    $error_count++;
                    continue;
                }

                // Simpan atau preview
                if ($save) {
                    $kode_jadwal = 'JDW-AUTO-' . date('Ymd') . '-' . strtoupper($hari) . '-' . str_replace(':', '', $jam_mulai) . '-' . $mk->id;
                    
                    $data_jadwal = array(
                        'kode_jadwal' => $kode_jadwal,
                        'id_mapping_dosen_mk' => $mk->id,
                        'kelas' => $kelas,
                        'hari' => $hari,
                        'jam_mulai' => $jam_mulai,
                        'jam_selesai' => $jam_selesai,
                        'id_ruang_kelas' => $ruang->id,
                        'id_tahun_akademik' => $id_tahun_akademik,
                        'keterangan' => 'Generated automatically'
                    );

                    if ($this->Jadwal_kuliah_model->create($data_jadwal)) {
                        $success_count++;
                    } else {
                        $error_count++;
                    }
                }

                // Simpan ke track jadwal hari ini
                $jadwal_hari_ini[$index] = array(
                    'jam_mulai' => $jam_mulai,
                    'jam_selesai' => $jam_selesai
                );

                // Track jadwal yang sudah dibuat untuk cek bentrok jadwal berikutnya
                if (!isset($jadwal_generated[$hari])) {
                    $jadwal_generated[$hari] = array();
                }
                $jadwal_generated[$hari][] = array(
                    'jam_mulai' => $jam_mulai,
                    'jam_selesai' => $jam_selesai,
                    'id_ruang' => $ruang->id,
                    'id_dosen' => $id_dosen,
                    'kelas' => $kelas
                );

                // Untuk preview
                $preview[] = array(
                    'hari' => $hari,
                    'kode_mk' => $mk->kode_mk,
                    'nama_mk' => $mk->nama_mk,
                    'sks' => $sks,
                    'nama_dosen' => $mk->nama_dosen,
                    'jam_mulai' => date('H:i', strtotime($jam_mulai)),
                    'jam_selesai' => date('H:i', strtotime($jam_selesai)),
                    'ruang' => $ruang->kode_ruang
                );
            }
        }

        return array(
            'success' => $success_count > 0 || !$save,
            'success_count' => $success_count,
            'error_count' => $error_count,
            'preview' => $preview,
            'message' => $success_count > 0 ? "Berhasil generate {$success_count} jadwal." : "Gagal generate jadwal."
        );
    }

    /**
     * Form generate jadwal pemadatan
     */
    public function generate_pemadatan() {
        $this->assert_not_kaprodi('Jadwal_kuliah');
        // Ambil tahun akademik aktif
        $tahun_aktif = $this->Tahun_akademik_model->get_aktif();
        
        if (!$tahun_aktif) {
            $this->session->set_flashdata('error', 'Tidak ada tahun akademik aktif. Silakan aktifkan tahun akademik terlebih dahulu.');
            redirect('Jadwal_kuliah');
        }

        $data['kelas_list'] = $this->Jadwal_kuliah_model->get_kelas_with_mapping($tahun_aktif->id);
        $data['tahun_aktif'] = $tahun_aktif;
        $data['title'] = 'Generate Jadwal Pemadatan';
        $data['page_title'] = 'Generate Jadwal Pemadatan';
        $data['breadcrumb'] = array(
            array('label' => 'Jadwal Kuliah', 'url' => site_url('Jadwal_kuliah')),
            array('label' => 'Generate Pemadatan', 'active' => true)
        );
        $data['sidebar_menu'] = generate_sidebar_menu(get_user_role());
        $data['content'] = $this->load->view('jadwal_kuliah/generate_pemadatan', $data, true);
        $this->load->view('layouts/main', $data);
    }

    /**
     * AJAX: Preview generate jadwal pemadatan
     */
    public function preview_pemadatan() {
        $this->json_deny_kaprodi();
        if (ob_get_level()) {
            ob_clean();
        }
        
        header('Content-Type: application/json; charset=utf-8');
        
        try {
            $kelas = $this->input->get_post('kelas');
            $max_mk_per_hari = $this->input->get_post('max_mk_per_hari') ?: 3;
            $tahun_aktif = $this->Tahun_akademik_model->get_aktif();
            
            if (empty($kelas) || !$tahun_aktif) {
                echo json_encode(array('success' => false, 'message' => 'Kelas atau tahun akademik tidak valid'));
                exit;
            }

            // Generate preview (tanpa save)
            $result = $this->generate_jadwal_pemadatan_logic($kelas, $tahun_aktif->id, $max_mk_per_hari, false);

            if (empty($result['success'])) {
                echo json_encode(array(
                    'success' => false,
                    'message' => isset($result['message']) ? $result['message'] : 'Gagal membuat preview jadwal pemadatan.',
                    'data' => array()
                ));
                exit;
            }
            
            echo json_encode(array(
                'success' => true,
                'data' => isset($result['preview']) && is_array($result['preview']) ? $result['preview'] : array(),
                'message' => isset($result['message']) ? $result['message'] : null
            ));
            exit;
            
        } catch (Exception $e) {
            echo json_encode(array('success' => false, 'message' => 'Terjadi kesalahan: ' . $e->getMessage()));
            exit;
        } catch (Throwable $e) {
            echo json_encode(array('success' => false, 'message' => 'Terjadi kesalahan: ' . $e->getMessage()));
            exit;
        }
    }

    /**
     * Process generate jadwal pemadatan
     */
    public function process_pemadatan() {
        $this->assert_not_kaprodi('Jadwal_kuliah');
        // Ambil tahun akademik aktif
        $tahun_aktif = $this->Tahun_akademik_model->get_aktif();
        
        if (!$tahun_aktif) {
            $this->session->set_flashdata('error', 'Tidak ada tahun akademik aktif. Silakan aktifkan tahun akademik terlebih dahulu.');
            redirect('Jadwal_kuliah');
        }

        $kelas = $this->input->post('kelas');
        $max_mk_per_hari = $this->input->post('max_mk_per_hari') ?: 3;
        $overwrite = $this->input->post('overwrite') == '1';

        if (empty($kelas)) {
            $this->session->set_flashdata('error', 'Kelas harus dipilih.');
            redirect('Jadwal_kuliah/generate_pemadatan');
        }

        // Jika overwrite, hapus jadwal yang sudah ada untuk kelas ini
        if ($overwrite) {
            $this->db->where('kelas', $kelas);
            $this->db->where('id_tahun_akademik', $tahun_aktif->id);
            $this->db->delete('jadwal_kuliah');
        }

        // Generate jadwal pemadatan
        $result = $this->generate_jadwal_pemadatan_logic($kelas, $tahun_aktif->id, $max_mk_per_hari, true);

        if ($result['success']) {
            $message = "Berhasil generate {$result['success_count']} jadwal pemadatan untuk kelas {$kelas}.";
            if ($result['error_count'] > 0) {
                $message .= " Gagal generate {$result['error_count']} jadwal.";
            }
            $this->session->set_flashdata('success', $message);
        } else {
            $this->session->set_flashdata('error', $result['message']);
        }

        redirect('Jadwal_kuliah');
    }

    /**
     * Logic generate jadwal pemadatan
     * Setiap mata kuliah akan bertemu 2x per minggu di hari berbeda
     */
    private function generate_jadwal_pemadatan_logic($kelas, $id_tahun_akademik, $max_mk_per_hari = 3, $save = true) {
        // Ambil semua mapping yang belum dijadwalkan untuk kelas tersebut
        $mapping_list = $this->Jadwal_kuliah_model->get_mapping_by_kelas_available($kelas, $id_tahun_akademik);
        
        if (empty($mapping_list)) {
            return array(
                'success' => false,
                'message' => 'Tidak ada mata kuliah yang perlu dijadwalkan untuk kelas ' . $kelas . '.'
            );
        }

        // Ambil semua ruang aktif
        $ruang_list = $this->Ruang_kelas_model->get_all_active();
        if (empty($ruang_list)) {
            return array(
                'success' => false,
                'message' => 'Tidak ada ruang kelas yang aktif. Silakan tambahkan ruang kelas terlebih dahulu.'
            );
        }

        // Hari yang tersedia (Senin - Jumat)
        $hari_list = array('senin', 'selasa', 'rabu', 'kamis', 'jumat');
        
        // Jam mulai default
        $jam_mulai_default = '08:00:00';
        $jam_selesai_max = '17:30:00';

        // Buat list mata kuliah yang akan dijadwalkan 2x
        $mk_double_list = array();
        foreach ($mapping_list as $mk) {
            // Setiap mata kuliah akan dijadwalkan 2x
            $mk_double_list[] = array('mapping' => $mk, 'pertemuan' => 1);
            $mk_double_list[] = array('mapping' => $mk, 'pertemuan' => 2);
        }

        // Shuffle untuk distribusi acak
        shuffle($mk_double_list);

        // Distribusikan ke hari (setiap mata kuliah harus di hari berbeda untuk 2 pertemuannya)
        $jadwal_per_hari = array();
        foreach ($hari_list as $hari) {
            $jadwal_per_hari[$hari] = array();
        }

        // Track mata kuliah yang sudah dijadwalkan untuk memastikan 2 pertemuan di hari berbeda
        $mk_scheduled = array(); // Format: ['mapping_id' => ['hari1' => ..., 'hari2' => ...]]

        foreach ($mk_double_list as $mk_item) {
            $mk = $mk_item['mapping'];
            $pertemuan = $mk_item['pertemuan'];
            $mk_id = $mk->id;

            // Jika ini pertemuan kedua, pastikan tidak di hari yang sama dengan pertemuan pertama
            $exclude_hari = array();
            if ($pertemuan == 2 && isset($mk_scheduled[$mk_id])) {
                $exclude_hari = $mk_scheduled[$mk_id];
            }

            // Cari hari yang cocok
            $hari_dipilih = null;
            $hari_index = 0;
            $max_attempts = count($hari_list) * 2; // Maksimal 2x putaran
            $attempt = 0;

            while ($hari_dipilih === null && $attempt < $max_attempts) {
                $hari = $hari_list[$hari_index % count($hari_list)];
                
                // Skip jika hari ini sudah digunakan untuk pertemuan pertama mata kuliah ini
                if (in_array($hari, $exclude_hari)) {
                    $hari_index++;
                    $attempt++;
                    continue;
                }

                // Cek apakah hari ini sudah mencapai maksimal
                if (count($jadwal_per_hari[$hari]) >= $max_mk_per_hari) {
                    $hari_index++;
                    $attempt++;
                    continue;
                }

                // Hari cocok
                $hari_dipilih = $hari;
                break;
            }

            // Jika tidak ada hari yang cocok, pilih hari yang belum penuh (kecuali exclude)
            if ($hari_dipilih === null) {
                foreach ($hari_list as $h) {
                    if (!in_array($h, $exclude_hari) && count($jadwal_per_hari[$h]) < $max_mk_per_hari) {
                        $hari_dipilih = $h;
                        break;
                    }
                }
            }

            // Jika masih null, pilih hari yang paling sedikit (kecuali exclude)
            if ($hari_dipilih === null) {
                $hari_counts = array();
                foreach ($hari_list as $h) {
                    if (!in_array($h, $exclude_hari)) {
                        $hari_counts[$h] = count($jadwal_per_hari[$h]);
                    }
                }
                if (!empty($hari_counts)) {
                    asort($hari_counts);
                    $hari_dipilih = array_key_first($hari_counts);
                }
            }

            // Jika masih null, pilih hari pertama yang bukan exclude
            if ($hari_dipilih === null) {
                foreach ($hari_list as $h) {
                    if (!in_array($h, $exclude_hari)) {
                        $hari_dipilih = $h;
                        break;
                    }
                }
            }

            if ($hari_dipilih) {
                $jadwal_per_hari[$hari_dipilih][] = array(
                    'mapping' => $mk,
                    'pertemuan' => $pertemuan
                );

                // Track hari yang sudah digunakan untuk mata kuliah ini
                if (!isset($mk_scheduled[$mk_id])) {
                    $mk_scheduled[$mk_id] = array();
                }
                $mk_scheduled[$mk_id][] = $hari_dipilih;
            }
        }

        // Generate jadwal untuk setiap hari
        $success_count = 0;
        $error_count = 0;
        $preview = array();
        
        // Track semua jadwal yang sudah dibuat dalam proses generate ini
        $jadwal_generated = array();

        foreach ($hari_list as $hari) {
            $mata_kuliah_hari = $jadwal_per_hari[$hari];
            
            if (empty($mata_kuliah_hari)) {
                continue;
            }

            // Sort berdasarkan SKS (prioritaskan yang SKS lebih besar untuk jam awal)
            usort($mata_kuliah_hari, function($a, $b) {
                $sks_a = $a['mapping']->sks ?? 0;
                $sks_b = $b['mapping']->sks ?? 0;
                return $sks_b - $sks_a;
            });

            // Generate jadwal untuk hari ini
            $ruang_used_today = array();
            $jadwal_hari_ini = array();
            $jam_mulai_current = $jam_mulai_default;

            foreach ($mata_kuliah_hari as $index => $mk_item) {
                $mk = $mk_item['mapping'];
                $pertemuan = $mk_item['pertemuan'];
                $sks = $mk->sks ?? 0;
                $durasi_menit = $sks * 50;
                
                // Tentukan jam mulai
                if ($index == 0) {
                    $jam_mulai = $jam_mulai_default;
                } else {
                    // 30 menit setelah mata kuliah sebelumnya selesai
                    if (isset($jadwal_hari_ini[$index - 1])) {
                        $jam_selesai_sebelumnya = strtotime($jadwal_hari_ini[$index - 1]['jam_selesai']);
                        $jam_mulai = date('H:i:s', strtotime('+30 minutes', $jam_selesai_sebelumnya));
                    } else {
                        $jam_mulai = $jam_mulai_current;
                    }
                }

                // Hitung jam selesai
                $jam_mulai_timestamp = strtotime($jam_mulai);
                $jam_selesai_timestamp = strtotime("+{$durasi_menit} minutes", $jam_mulai_timestamp);
                $jam_selesai = date('H:i:s', $jam_selesai_timestamp);

                // Cek apakah melebihi jam maksimal
                if (strtotime($jam_selesai) > strtotime($jam_selesai_max)) {
                    $error_count++;
                    continue;
                }

                // Pilih ruang secara random (hindari yang sudah digunakan hari ini, dan yang tidak bentrok)
                $jadwal_model = $this->Jadwal_kuliah_model;
                $available_ruang = array_filter($ruang_list, function($r) use ($ruang_used_today, $hari, $jam_mulai, $jam_selesai, $id_tahun_akademik, $jadwal_model, $jadwal_generated) {
                    if (in_array($r->id, $ruang_used_today)) {
                        return false;
                    }
                    if ($jadwal_model->check_bentrok_ruang($r->id, $hari, $jam_mulai, $jam_selesai, $id_tahun_akademik)) {
                        return false;
                    }
                    if (isset($jadwal_generated[$hari])) {
                        foreach ($jadwal_generated[$hari] as $j) {
                            if ($j['id_ruang'] == $r->id) {
                                if (strtotime($jam_mulai) < strtotime($j['jam_selesai']) && 
                                    strtotime($jam_selesai) > strtotime($j['jam_mulai'])) {
                                    return false;
                                }
                            }
                        }
                    }
                    return true;
                });

                if (empty($available_ruang)) {
                    $available_ruang = array_filter($ruang_list, function($r) use ($ruang_used_today) {
                        return !in_array($r->id, $ruang_used_today);
                    });
                    if (empty($available_ruang)) {
                        $available_ruang = $ruang_list;
                        $ruang_used_today = array();
                    }
                }

                if (empty($available_ruang)) {
                    $error_count++;
                    continue;
                }

                $ruang = $available_ruang[array_rand($available_ruang)];
                $ruang_used_today[] = $ruang->id;

                // Ambil id_dosen dari mapping
                $this->load->model('Mapping_dosen_mk_model');
                $mapping_detail = $this->Mapping_dosen_mk_model->get_by_id($mk->id);
                $id_dosen = $mapping_detail ? $mapping_detail->id_dosen : null;

                // Validasi bentrok
                $has_error = false;
                
                // Cek bentrok dosen dengan database
                if ($this->Jadwal_kuliah_model->check_bentrok_dosen($mk->id, $hari, $jam_mulai, $jam_selesai, $id_tahun_akademik)) {
                    $has_error = true;
                }
                
                // Cek bentrok dosen dengan jadwal yang sudah dibuat dalam proses generate
                if (!$has_error && $id_dosen && isset($jadwal_generated[$hari])) {
                    foreach ($jadwal_generated[$hari] as $j) {
                        if ($j['id_dosen'] == $id_dosen) {
                            if (strtotime($jam_mulai) < strtotime($j['jam_selesai']) && 
                                strtotime($jam_selesai) > strtotime($j['jam_mulai'])) {
                                $has_error = true;
                                break;
                            }
                        }
                    }
                }
                
                // Cek bentrok kelas dengan database
                if (!$has_error && $this->Jadwal_kuliah_model->check_bentrok_kelas($kelas, $hari, $jam_mulai, $jam_selesai, $id_tahun_akademik)) {
                    $has_error = true;
                }
                
                // Cek bentrok kelas dengan jadwal yang sudah dibuat dalam proses generate
                if (!$has_error && isset($jadwal_generated[$hari])) {
                    foreach ($jadwal_generated[$hari] as $j) {
                        if ($j['kelas'] == $kelas) {
                            if (strtotime($jam_mulai) < strtotime($j['jam_selesai']) && 
                                strtotime($jam_selesai) > strtotime($j['jam_mulai'])) {
                                $has_error = true;
                                break;
                            }
                        }
                    }
                }

                if ($has_error) {
                    $error_count++;
                    continue;
                }

                // Simpan atau preview
                if ($save) {
                    $kode_jadwal = 'JDW-PEMADATAN-' . date('Ymd') . '-' . strtoupper($hari) . '-' . str_replace(':', '', $jam_mulai) . '-' . $mk->id . '-P' . $pertemuan;
                    
                    $data_jadwal = array(
                        'kode_jadwal' => $kode_jadwal,
                        'id_mapping_dosen_mk' => $mk->id,
                        'kelas' => $kelas,
                        'hari' => $hari,
                        'jam_mulai' => $jam_mulai,
                        'jam_selesai' => $jam_selesai,
                        'id_ruang_kelas' => $ruang->id,
                        'id_tahun_akademik' => $id_tahun_akademik,
                        'keterangan' => 'Generated automatically - Pemadatan - Pertemuan ' . $pertemuan
                    );

                    if ($this->Jadwal_kuliah_model->create($data_jadwal)) {
                        $success_count++;
                    } else {
                        $error_count++;
                    }
                }

                // Track jadwal hari ini
                $jadwal_hari_ini[$index] = array(
                    'jam_mulai' => $jam_mulai,
                    'jam_selesai' => $jam_selesai
                );

                // Track jadwal yang sudah dibuat
                if (!isset($jadwal_generated[$hari])) {
                    $jadwal_generated[$hari] = array();
                }
                $jadwal_generated[$hari][] = array(
                    'jam_mulai' => $jam_mulai,
                    'jam_selesai' => $jam_selesai,
                    'id_ruang' => $ruang->id,
                    'id_dosen' => $id_dosen,
                    'kelas' => $kelas
                );

                // Untuk preview
                $preview[] = array(
                    'hari' => $hari,
                    'kode_mk' => $mk->kode_mk,
                    'nama_mk' => $mk->nama_mk,
                    'sks' => $sks,
                    'nama_dosen' => $mk->nama_dosen,
                    'jam_mulai' => date('H:i', strtotime($jam_mulai)),
                    'jam_selesai' => date('H:i', strtotime($jam_selesai)),
                    'ruang' => $ruang->kode_ruang,
                    'pertemuan' => 'Pertemuan ' . $pertemuan
                );
            }
        }

        return array(
            'success' => $success_count > 0 || !$save,
            'success_count' => $success_count,
            'error_count' => $error_count,
            'preview' => $preview,
            'message' => $success_count > 0 ? "Berhasil generate {$success_count} jadwal pemadatan." : "Gagal generate jadwal pemadatan."
        );
    }

    /**
     * Download/Export Jadwal Kuliah per Kelas
     * Hanya untuk role sekprodi
     */
    public function download_per_kelas() {
        $this->check_role(array('sekprodi', 'kaprodi'));
        
        // Ambil tahun akademik aktif
        $tahun_aktif = $this->Tahun_akademik_model->get_aktif();
        if (!$tahun_aktif) {
            $this->session->set_flashdata('error', 'Tidak ada tahun akademik aktif.');
            redirect('Jadwal_kuliah');
        }

        // Ambil parameter kelas
        $kelas = $this->input->get('kelas');
        if (empty($kelas)) {
            $this->session->set_flashdata('error', 'Kelas harus dipilih.');
            redirect('Jadwal_kuliah');
        }

        // Ambil data jadwal per kelas
        $jadwal_list = $this->Jadwal_kuliah_model->get_by_kelas($kelas, $tahun_aktif->id);
        
        if (empty($jadwal_list)) {
            $this->session->set_flashdata('error', 'Tidak ada jadwal untuk kelas ' . $kelas . '.');
            redirect('Jadwal_kuliah');
        }

        // Cek apakah PhpSpreadsheet tersedia
        $use_phpspreadsheet = false;
        if (file_exists(APPPATH . '../vendor/autoload.php')) {
            require_once APPPATH . '../vendor/autoload.php';
            if (class_exists('PhpOffice\\PhpSpreadsheet\\Spreadsheet')) {
                $use_phpspreadsheet = true;
            }
        }

        $filename_base = 'Jadwal_Kuliah_Kelas_' . $kelas . '_' . $tahun_aktif->tahun_akademik . '_' . ucfirst($tahun_aktif->semester) . '_' . date('YmdHis');

        if ($use_phpspreadsheet) {
            // Gunakan PhpSpreadsheet
            $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet();
            
            // Judul
            $sheet->setCellValue('A1', 'JADWAL KULIAH');
            $sheet->mergeCells('A1:H1');
            $sheet->getStyle('A1')->applyFromArray([
                'font' => ['bold' => true, 'size' => 16],
                'alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER]
            ]);
            
            $sheet->setCellValue('A2', 'Kelas: ' . $kelas);
            $sheet->mergeCells('A2:H2');
            $sheet->getStyle('A2')->applyFromArray([
                'font' => ['bold' => true, 'size' => 12],
                'alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER]
            ]);
            
            $sheet->setCellValue('A3', 'Tahun Akademik: ' . $tahun_aktif->tahun_akademik . ' ' . ucfirst($tahun_aktif->semester));
            $sheet->mergeCells('A3:H3');
            
            $sheet->setCellValue('A4', 'Tanggal Export: ' . date('d F Y H:i:s'));
            $sheet->mergeCells('A4:H4');
            
            // Header
            $headerRow = 6;
            $headers = [
                'A' => 'No',
                'B' => 'Hari',
                'C' => 'Jam',
                'D' => 'Kode MK',
                'E' => 'Mata Kuliah',
                'F' => 'SKS',
                'G' => 'Dosen',
                'H' => 'Ruang'
            ];
            
            foreach ($headers as $col => $label) {
                $sheet->setCellValue($col . $headerRow, $label);
            }
            
            // Style header
            $sheet->getStyle('A' . $headerRow . ':H' . $headerRow)->applyFromArray([
                'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                'fill' => [
                    'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                    'startColor' => ['rgb' => '4472C4']
                ],
                'alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER],
                'borders' => [
                    'allBorders' => [
                        'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN
                    ]
                ]
            ]);
            
            // Data
            $row = $headerRow + 1;
            $no = 1;
            
            // Urutkan berdasarkan hari dan jam
            $hari_order = ['senin' => 1, 'selasa' => 2, 'rabu' => 3, 'kamis' => 4, 'jumat' => 5];
            usort($jadwal_list, function($a, $b) use ($hari_order) {
                $hari_a = isset($hari_order[strtolower($a->hari)]) ? $hari_order[strtolower($a->hari)] : 99;
                $hari_b = isset($hari_order[strtolower($b->hari)]) ? $hari_order[strtolower($b->hari)] : 99;
                
                if ($hari_a != $hari_b) {
                    return $hari_a - $hari_b;
                }
                
                return strtotime($a->jam_mulai) - strtotime($b->jam_mulai);
            });
            
            foreach ($jadwal_list as $jadwal) {
                $sheet->setCellValue('A' . $row, $no++);
                $sheet->setCellValue('B' . $row, ucfirst($jadwal->hari));
                $sheet->setCellValue('C' . $row, date('H:i', strtotime($jadwal->jam_mulai)) . ' - ' . date('H:i', strtotime($jadwal->jam_selesai)));
                $sheet->setCellValue('D' . $row, $jadwal->kode_mk ?? '-');
                $sheet->setCellValue('E' . $row, $jadwal->nama_mk ?? '-');
                $sheet->setCellValue('F' . $row, $jadwal->sks ?? '-');
                $sheet->setCellValue('G' . $row, $jadwal->nama_dosen ?? '-');
                $sheet->setCellValue('H' . $row, ($jadwal->kode_ruang ?? '') . ($jadwal->nama_ruang ? ' - ' . $jadwal->nama_ruang : ''));
                
                // Style baris data
                $sheet->getStyle('A' . $row . ':H' . $row)->applyFromArray([
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN
                        ]
                    ]
                ]);
                
                $row++;
            }
            
            // Auto width columns
            foreach (range('A', 'H') as $col) {
                $sheet->getColumnDimension($col)->setAutoSize(true);
            }
            
            // Set width untuk kolom tertentu
            $sheet->getColumnDimension('A')->setWidth(5);
            $sheet->getColumnDimension('B')->setWidth(12);
            $sheet->getColumnDimension('C')->setWidth(15);
            $sheet->getColumnDimension('D')->setWidth(12);
            $sheet->getColumnDimension('E')->setWidth(40);
            $sheet->getColumnDimension('F')->setWidth(8);
            $sheet->getColumnDimension('G')->setWidth(30);
            $sheet->getColumnDimension('H')->setWidth(20);
            
            // Alignment
            $sheet->getStyle('A' . ($headerRow + 1) . ':A' . ($row - 1))->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle('B' . ($headerRow + 1) . ':B' . ($row - 1))->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle('C' . ($headerRow + 1) . ':C' . ($row - 1))->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle('F' . ($headerRow + 1) . ':F' . ($row - 1))->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
            
            // Set filename
            $filename = $filename_base . '.xlsx';
            
            // Set headers
            header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
            header('Content-Disposition: attachment;filename="' . $filename . '"');
            header('Cache-Control: max-age=0');
            
            // Write file
            $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
            $writer->save('php://output');
            exit;
        } else {
            // Fallback: Export CSV
            $filename = $filename_base . '.csv';
            header('Content-Type: text/csv; charset=utf-8');
            header('Content-Disposition: attachment;filename="' . $filename . '"');
            header('Cache-Control: max-age=0');
            
            $output = fopen('php://output', 'w');
            
            // BOM untuk Excel UTF-8
            fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));
            
            // Header
            fputcsv($output, array('JADWAL KULIAH'));
            fputcsv($output, array('Kelas: ' . $kelas));
            fputcsv($output, array('Tahun Akademik: ' . $tahun_aktif->tahun_akademik . ' ' . ucfirst($tahun_aktif->semester)));
            fputcsv($output, array('Tanggal Export: ' . date('d F Y H:i:s')));
            fputcsv($output, array()); // Baris kosong
            fputcsv($output, array('No', 'Hari', 'Jam', 'Kode MK', 'Mata Kuliah', 'SKS', 'Dosen', 'Ruang'));
            
            // Data
            $no = 1;
            
            // Urutkan berdasarkan hari dan jam
            $hari_order = ['senin' => 1, 'selasa' => 2, 'rabu' => 3, 'kamis' => 4, 'jumat' => 5];
            usort($jadwal_list, function($a, $b) use ($hari_order) {
                $hari_a = isset($hari_order[strtolower($a->hari)]) ? $hari_order[strtolower($a->hari)] : 99;
                $hari_b = isset($hari_order[strtolower($b->hari)]) ? $hari_order[strtolower($b->hari)] : 99;
                
                if ($hari_a != $hari_b) {
                    return $hari_a - $hari_b;
                }
                
                return strtotime($a->jam_mulai) - strtotime($b->jam_mulai);
            });
            
            foreach ($jadwal_list as $jadwal) {
                fputcsv($output, array(
                    $no++,
                    ucfirst($jadwal->hari),
                    date('H:i', strtotime($jadwal->jam_mulai)) . ' - ' . date('H:i', strtotime($jadwal->jam_selesai)),
                    $jadwal->kode_mk ?? '-',
                    $jadwal->nama_mk ?? '-',
                    $jadwal->sks ?? '-',
                    $jadwal->nama_dosen ?? '-',
                    ($jadwal->kode_ruang ?? '') . ($jadwal->nama_ruang ? ' - ' . $jadwal->nama_ruang : '')
                ));
            }
            
            fclose($output);
            exit;
        }
    }
}




