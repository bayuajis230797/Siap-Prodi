<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Rapat extends MY_Controller {

    public function __construct() {
        parent::__construct();
        // Check role: sekprodi atau dosen
        $role = $this->session->userdata('role');
        if (!in_array($role, array('sekprodi', 'dosen'))) {
            $this->session->set_flashdata('error', 'Anda tidak memiliki akses ke halaman ini.');
            redirect('Profile');
        }
        $this->load->model('Rapat_model');
        $this->load->model('Dosen_model');
        $this->load->model('Tendik_model');
        $this->load->model('Mahasiswa_model');
        $this->load->model('User_model');
        $this->load->library('form_validation');
        $this->load->library('upload');
    }

    /**
     * Index - Daftar semua rapat
     */
    public function index() {
        // Pagination
        $this->load->library('pagination');
        $config['base_url'] = site_url('Rapat');
        $config['total_rows'] = $this->Rapat_model->count_all();
        $config['per_page'] = 10;
        $config['uri_segment'] = 2;
        $config['query_string_segment'] = 'page';
        $config['page_query_string'] = TRUE;
        
        $this->pagination->initialize($config);
        
        $page = $this->input->get('page') ? (int)$this->input->get('page') : 0;
        $rapat_list = $this->Rapat_model->get_all($config['per_page'], $page);
        
        // Ambil statistik untuk setiap rapat
        foreach ($rapat_list as $rapat) {
            $rapat->statistik = $this->Rapat_model->get_statistik_kehadiran($rapat->id);
        }
        
        $data = array(
            'title' => 'Manajemen Rapat',
            'page_title' => 'Manajemen Rapat',
            'breadcrumb' => array(array('label' => 'Rapat', 'active' => true)),
            'sidebar_menu' => generate_sidebar_menu('sekprodi'),
            'rapat_list' => $rapat_list,
            'pagination' => $this->pagination->create_links()
        );
        
        $data['content'] = $this->load->view('sekprodi/rapat/index', $data, true);
        $this->load->view('layouts/main', $data);
    }

    /**
     * Create - Form tambah rapat baru
     */
    public function create() {
        // Ambil data dosen dan tendik untuk pilihan peserta
        $dosen_list = $this->Dosen_model->get_all_active();
        $tendik_list = $this->Tendik_model->get_all();
        $kelas_list = $this->Mahasiswa_model->get_all_kelas('aktif');
        
        $data = array(
            'title' => 'Tambah Rapat Baru',
            'page_title' => 'Tambah Rapat Baru',
            'breadcrumb' => array(
                array('label' => 'Rapat', 'url' => site_url('Rapat')),
                array('label' => 'Tambah Baru', 'active' => true)
            ),
            'sidebar_menu' => generate_sidebar_menu('sekprodi'),
            'dosen_list' => $dosen_list,
            'tendik_list' => $tendik_list,
            'kelas_list' => $kelas_list,
            'rapat' => null
        );
        
        $data['content'] = $this->load->view('sekprodi/rapat/create', $data, true);
        $this->load->view('layouts/main', $data);
    }

    /**
     * Store - Simpan rapat baru
     */
    public function store() {
        $this->form_validation->set_rules('judul_rapat', 'Judul Rapat', 'required|trim');
        $this->form_validation->set_rules('tanggal_rapat', 'Tanggal Rapat', 'required');
        $this->form_validation->set_rules('jam_mulai', 'Jam Mulai', 'required');
        
        if ($this->form_validation->run() == FALSE) {
            $this->create();
        } else {
            $data = array(
                'judul_rapat' => $this->input->post('judul_rapat'),
                'tanggal_rapat' => $this->input->post('tanggal_rapat'),
                'jam_mulai' => $this->input->post('jam_mulai'),
                'jam_selesai' => $this->input->post('jam_selesai') ? $this->input->post('jam_selesai') : null,
                'tempat' => $this->input->post('tempat'),
                'notulen' => $this->input->post('notulen'),
                'created_by' => $this->session->userdata('user_id')
            );
            
            $id_rapat = $this->Rapat_model->create($data);
            
            // Tambah peserta jika ada
            $peserta_dosen = $this->input->post('peserta_dosen');
            $peserta_tendik = $this->input->post('peserta_tendik');
            $peserta_eksternal = $this->input->post('peserta_eksternal');
            
            $peserta_list = array();
            
            // Tambah dosen
            if ($peserta_dosen && is_array($peserta_dosen)) {
                foreach ($peserta_dosen as $id_dosen) {
                    $dosen = $this->Dosen_model->get_by_id($id_dosen);
                    if ($dosen) {
                        // Cari user_id dari dosen
                        $user = $this->User_model->get_by_ref_id($id_dosen, 'dosen');
                        $id_user = null;
                        if ($user && isset($user->id) && $user->id > 0) {
                            $id_user = (int)$user->id;
                        }
                        $peserta_list[] = array(
                            'id_user' => $id_user,
                            'nama_peserta' => $dosen->nama_dosen,
                            'role_peserta' => 'dosen'
                        );
                    }
                }
            }
            
            // Tambah tendik
            if ($peserta_tendik && is_array($peserta_tendik)) {
                foreach ($peserta_tendik as $id_tendik) {
                    $tendik = $this->Tendik_model->get_by_id($id_tendik);
                    if ($tendik) {
                        // Cari user_id dari tendik
                        $user = $this->User_model->get_by_ref_id($id_tendik, 'tendik');
                        $id_user = null;
                        if ($user && isset($user->id) && $user->id > 0) {
                            $id_user = (int)$user->id;
                        }
                        $peserta_list[] = array(
                            'id_user' => $id_user,
                            'nama_peserta' => $tendik->nama_tendik,
                            'role_peserta' => 'tendik'
                        );
                    }
                }
            }
            
            // Tambah peserta eksternal
            if ($peserta_eksternal && is_array($peserta_eksternal)) {
                foreach ($peserta_eksternal as $nama) {
                    if (!empty(trim($nama))) {
                        $peserta_list[] = array(
                            'id_user' => null,
                            'nama_peserta' => trim($nama),
                            'role_peserta' => 'eksternal'
                        );
                    }
                }
            }
            
            // Tambah mahasiswa
            $peserta_mahasiswa = $this->input->post('peserta_mahasiswa');
            if ($peserta_mahasiswa && is_array($peserta_mahasiswa)) {
                foreach ($peserta_mahasiswa as $id_mahasiswa) {
                    $mahasiswa = $this->Mahasiswa_model->get_by_id($id_mahasiswa);
                    if ($mahasiswa) {
                        // Cari user_id dari mahasiswa
                        $user = $this->User_model->get_by_ref_id($id_mahasiswa, 'mahasiswa');
                        $id_user = null;
                        if ($user && isset($user->id) && $user->id > 0) {
                            $id_user = (int)$user->id;
                        }
                        $peserta_list[] = array(
                            'id_user' => $id_user,
                            'nama_peserta' => $mahasiswa->nama_mahasiswa . ' (' . $mahasiswa->nim . ')',
                            'role_peserta' => 'mahasiswa'
                        );
                    }
                }
            }
            
            if (!empty($peserta_list)) {
                $this->Rapat_model->bulk_add_peserta($id_rapat, $peserta_list);
            }
            
            $this->session->set_flashdata('success', 'Rapat berhasil ditambahkan.');
            redirect('Rapat/detail/' . $id_rapat);
        }
    }

    /**
     * Detail - Detail rapat dengan peserta dan dokumentasi
     */
    public function detail($id) {
        $rapat = $this->Rapat_model->get_rapat_with_detail($id);
        
        if (!$rapat) {
            $this->session->set_flashdata('error', 'Data rapat tidak ditemukan.');
            redirect('Rapat');
        }
        
        // Ambil data dosen dan tendik untuk tambah peserta
        $dosen_list = $this->Dosen_model->get_all_active();
        $tendik_list = $this->Tendik_model->get_all();
        $kelas_list = $this->Mahasiswa_model->get_all_kelas('aktif');
        
        $data = array(
            'title' => 'Detail Rapat',
            'page_title' => 'Detail Rapat',
            'breadcrumb' => array(
                array('label' => 'Rapat', 'url' => site_url('Rapat')),
                array('label' => 'Detail', 'active' => true)
            ),
            'sidebar_menu' => generate_sidebar_menu('sekprodi'),
            'rapat' => $rapat,
            'dosen_list' => $dosen_list,
            'tendik_list' => $tendik_list,
            'kelas_list' => $kelas_list
        );
        
        $data['content'] = $this->load->view('sekprodi/rapat/detail', $data, true);
        $this->load->view('layouts/main', $data);
    }

    /**
     * Edit - Form edit rapat
     */
    public function edit($id) {
        $rapat = $this->Rapat_model->get_by_id($id);
        
        if (!$rapat) {
            $this->session->set_flashdata('error', 'Data rapat tidak ditemukan.');
            redirect('Rapat');
        }
        
        $data = array(
            'title' => 'Edit Rapat',
            'page_title' => 'Edit Rapat',
            'breadcrumb' => array(
                array('label' => 'Rapat', 'url' => site_url('Rapat')),
                array('label' => 'Edit', 'active' => true)
            ),
            'sidebar_menu' => generate_sidebar_menu('sekprodi'),
            'rapat' => $rapat
        );
        
        $data['content'] = $this->load->view('sekprodi/rapat/edit', $data, true);
        $this->load->view('layouts/main', $data);
    }

    /**
     * Update - Update data rapat
     */
    public function update($id) {
        $this->form_validation->set_rules('judul_rapat', 'Judul Rapat', 'required|trim');
        $this->form_validation->set_rules('tanggal_rapat', 'Tanggal Rapat', 'required');
        $this->form_validation->set_rules('jam_mulai', 'Jam Mulai', 'required');
        
        if ($this->form_validation->run() == FALSE) {
            $this->edit($id);
        } else {
            $data = array(
                'judul_rapat' => $this->input->post('judul_rapat'),
                'tanggal_rapat' => $this->input->post('tanggal_rapat'),
                'jam_mulai' => $this->input->post('jam_mulai'),
                'jam_selesai' => $this->input->post('jam_selesai') ? $this->input->post('jam_selesai') : null,
                'tempat' => $this->input->post('tempat'),
                'notulen' => $this->input->post('notulen')
            );
            
            $this->Rapat_model->update($id, $data);
            $this->session->set_flashdata('success', 'Data rapat berhasil diupdate.');
            redirect('Rapat/detail/' . $id);
        }
    }

    /**
     * Delete - Hapus rapat
     */
    public function delete($id) {
        $rapat = $this->Rapat_model->get_by_id($id);
        
        if (!$rapat) {
            $this->session->set_flashdata('error', 'Data rapat tidak ditemukan.');
            redirect('Rapat');
        }
        
        // Hapus dokumentasi (file akan terhapus otomatis di model)
        $dokumentasi_list = $this->Rapat_model->get_dokumentasi_by_rapat($id);
        foreach ($dokumentasi_list as $dok) {
            $this->Rapat_model->delete_dokumentasi($dok->id);
        }
        
        $this->Rapat_model->delete($id);
        $this->session->set_flashdata('success', 'Rapat berhasil dihapus.');
        redirect('Rapat');
    }

    /**
     * Tambah Peserta - AJAX
     */
    public function tambah_peserta() {
        $id_rapat = $this->input->post('id_rapat');
        $nama_peserta = $this->input->post('nama_peserta');
        $role_peserta = $this->input->post('role_peserta') ? $this->input->post('role_peserta') : 'lainnya';
        
        if (empty($nama_peserta)) {
            echo json_encode(array('success' => false, 'message' => 'Nama peserta harus diisi.'));
            return;
        }
        
        $data = array(
            'id_rapat' => $id_rapat,
            'nama_peserta' => $nama_peserta,
            'role_peserta' => $role_peserta,
            'status_kehadiran' => 'tidak_hadir'
        );
        
        $id = $this->Rapat_model->add_peserta($data);
        
        if ($id) {
            $peserta = $this->Rapat_model->get_peserta_by_id($id);
            echo json_encode(array('success' => true, 'peserta' => $peserta));
        } else {
            echo json_encode(array('success' => false, 'message' => 'Gagal menambah peserta.'));
        }
    }

    /**
     * Update Status Kehadiran - AJAX
     */
    public function update_kehadiran() {
        $id_peserta = $this->input->post('id_peserta');
        $status = $this->input->post('status');
        $keterangan = $this->input->post('keterangan');
        
        if (!in_array($status, array('hadir', 'tidak_hadir', 'izin'))) {
            echo json_encode(array('success' => false, 'message' => 'Status tidak valid.'));
            return;
        }
        
        $result = $this->Rapat_model->update_status_kehadiran($id_peserta, $status, $keterangan);
        
        if ($result) {
            $peserta = $this->Rapat_model->get_peserta_by_id($id_peserta);
            $statistik = $this->Rapat_model->get_statistik_kehadiran($peserta->id_rapat);
            echo json_encode(array('success' => true, 'statistik' => $statistik));
        } else {
            echo json_encode(array('success' => false, 'message' => 'Gagal update status kehadiran.'));
        }
    }

    /**
     * Update Label Role untuk Cetak - AJAX
     */
    public function update_label_role() {
        $id_peserta = $this->input->post('id_peserta');
        $label_role = $this->input->post('label_role');
        
        $result = $this->Rapat_model->update_label_role($id_peserta, $label_role);
        
        if ($result !== false) {
            echo json_encode(array('success' => true));
        } else {
            echo json_encode(array('success' => false, 'message' => 'Gagal update label role.'));
        }
    }

    /**
     * Update Urutan Peserta - AJAX
     */
    public function update_urutan() {
        $id_peserta = $this->input->post('id_peserta');
        $urutan = $this->input->post('urutan');
        
        if (!is_numeric($urutan) || $urutan < 1) {
            echo json_encode(array('success' => false, 'message' => 'Urutan harus berupa angka positif.'));
            return;
        }
        
        $result = $this->Rapat_model->update_urutan($id_peserta, $urutan);
        
        if ($result !== false) {
            echo json_encode(array('success' => true));
        } else {
            echo json_encode(array('success' => false, 'message' => 'Gagal update urutan.'));
        }
    }

    /**
     * Hapus Peserta - AJAX
     */
    public function hapus_peserta() {
        $id_peserta = $this->input->post('id_peserta');
        
        $result = $this->Rapat_model->delete_peserta($id_peserta);
        
        if ($result) {
            echo json_encode(array('success' => true));
        } else {
            echo json_encode(array('success' => false, 'message' => 'Gagal menghapus peserta.'));
        }
    }

    /**
     * Upload Dokumentasi
     */
    public function upload_dokumentasi() {
        $id_rapat = $this->input->post('id_rapat');
        
        if (!$id_rapat) {
            echo json_encode(array('success' => false, 'message' => 'ID rapat tidak valid.'));
            return;
        }
        
        // Konfigurasi upload
        $upload_path = './uploads/rapat/';
        if (!is_dir($upload_path)) {
            mkdir($upload_path, 0755, true);
        }
        
        $config['upload_path'] = $upload_path;
        $config['allowed_types'] = 'jpg|jpeg|png|gif|pdf';
        $config['max_size'] = 5120; // 5MB
        $config['encrypt_name'] = TRUE;
        
        $this->upload->initialize($config);
        
        if (!$this->upload->do_upload('file')) {
            echo json_encode(array('success' => false, 'message' => $this->upload->display_errors('', '')));
            return;
        }
        
        $upload_data = $this->upload->data();
        
        $data = array(
            'id_rapat' => $id_rapat,
            'file_name' => $upload_data['orig_name'],
            'file_path' => 'uploads/rapat/' . $upload_data['file_name'],
            'file_size' => $upload_data['file_size'],
            'file_type' => $upload_data['file_type'],
            'keterangan' => $this->input->post('keterangan'),
            'uploaded_by' => $this->session->userdata('user_id')
        );
        
        $id = $this->Rapat_model->add_dokumentasi($data);
        
        if ($id) {
            $dokumentasi = $this->Rapat_model->get_dokumentasi_by_id($id);
            echo json_encode(array('success' => true, 'dokumentasi' => $dokumentasi));
        } else {
            // Hapus file jika gagal insert
            unlink($upload_path . $upload_data['file_name']);
            echo json_encode(array('success' => false, 'message' => 'Gagal menyimpan data dokumentasi.'));
        }
    }

    /**
     * Hapus Dokumentasi
     */
    public function hapus_dokumentasi() {
        $id = $this->input->post('id');
        
        $result = $this->Rapat_model->delete_dokumentasi($id);
        
        if ($result) {
            echo json_encode(array('success' => true));
        } else {
            echo json_encode(array('success' => false, 'message' => 'Gagal menghapus dokumentasi.'));
        }
    }

    /**
     * Get Mahasiswa by Kelas - AJAX
     */
    public function get_mahasiswa_by_kelas() {
        $kelas = $this->input->get('kelas');
        
        if (empty($kelas)) {
            echo json_encode(array('success' => false, 'message' => 'Kelas harus dipilih.'));
            return;
        }
        
        $mahasiswa_list = $this->Mahasiswa_model->get_by_kelas($kelas, 'aktif');
        
        echo json_encode(array('success' => true, 'mahasiswa' => $mahasiswa_list));
    }


    // ==================== METHOD UNTUK DOSEN ====================

    /**
     * Index Dosen - Daftar rapat yang diikuti dosen
     */
    public function dosen() {
        $role = $this->session->userdata('role');
        if ($role !== 'dosen') {
            show_404();
        }

        $user_id = $this->session->userdata('user_id');
        
        // Pagination
        $this->load->library('pagination');
        $config['base_url'] = site_url('Rapat/dosen');
        $config['total_rows'] = $this->Rapat_model->count_by_user_id($user_id);
        $config['per_page'] = 10;
        $config['uri_segment'] = 3;
        $config['query_string_segment'] = 'page';
        $config['page_query_string'] = TRUE;
        
        $this->pagination->initialize($config);
        
        $page = $this->input->get('page') ? (int)$this->input->get('page') : 0;
        $rapat_list = $this->Rapat_model->get_by_user_id($user_id, $config['per_page'], $page);
        
        // Ambil status kehadiran untuk setiap rapat
        foreach ($rapat_list as $rapat) {
            $peserta = $this->Rapat_model->get_peserta_by_user_and_rapat($user_id, $rapat->id);
            $rapat->status_kehadiran_saya = $peserta ? $peserta->status_kehadiran : null;
        }
        
        $data = array(
            'title' => 'Daftar Rapat',
            'page_title' => 'Daftar Rapat',
            'breadcrumb' => array(array('label' => 'Rapat', 'active' => true)),
            'sidebar_menu' => generate_sidebar_menu('dosen'),
            'rapat_list' => $rapat_list,
            'pagination' => $this->pagination->create_links()
        );
        
        $data['content'] = $this->load->view('dosen/rapat/index', $data, true);
        $this->load->view('layouts/main', $data);
    }

    /**
     * Detail Rapat untuk Dosen
     */
    public function dosen_detail($id) {
        $role = $this->session->userdata('role');
        if ($role !== 'dosen') {
            show_404();
        }

        $user_id = $this->session->userdata('user_id');
        
        // Cek apakah dosen ini adalah peserta rapat
        $peserta = $this->Rapat_model->get_peserta_by_user_and_rapat($user_id, $id);
        if (!$peserta) {
            $this->session->set_flashdata('error', 'Anda tidak terdaftar sebagai peserta rapat ini.');
            redirect('Rapat/dosen');
        }
        
        $rapat = $this->Rapat_model->get_rapat_with_detail($id);
        
        if (!$rapat) {
            $this->session->set_flashdata('error', 'Data rapat tidak ditemukan.');
            redirect('Rapat/dosen');
        }
        
        // Set status kehadiran dosen
        $rapat->status_kehadiran_saya = $peserta->status_kehadiran;
        $rapat->keterangan_saya = $peserta->keterangan;
        
        $data = array(
            'title' => 'Detail Rapat',
            'page_title' => 'Detail Rapat',
            'breadcrumb' => array(
                array('label' => 'Rapat', 'url' => site_url('Rapat/dosen')),
                array('label' => 'Detail', 'active' => true)
            ),
            'sidebar_menu' => generate_sidebar_menu('dosen'),
            'rapat' => $rapat
        );
        
        $data['content'] = $this->load->view('dosen/rapat/detail', $data, true);
        $this->load->view('layouts/main', $data);
    }

    /**
     * Cetak Daftar Hadir Rapat (untuk Sekprodi)
     */
    public function cetak_daftar_hadir($id) {
        $role = $this->session->userdata('role');
        if ($role !== 'sekprodi') {
            show_404();
        }

        $rapat = $this->Rapat_model->get_rapat_with_detail($id);
        
        if (!$rapat) {
            $this->session->set_flashdata('error', 'Data rapat tidak ditemukan.');
            redirect('Rapat');
        }

        // Load library PDF
        $this->load->library('Pdf');
        
        // Ambil data peserta (dosen, tendik, mahasiswa, dan eksternal)
        $peserta_list = array();
        foreach ($rapat->peserta as $peserta) {
            // Tampilkan dosen, tendik, mahasiswa, dan eksternal
            if (in_array($peserta->role_peserta, array('dosen', 'tendik', 'mahasiswa', 'eksternal'))) {
                $peserta_list[] = $peserta;
            }
        }
        
        $data = array(
            'rapat' => $rapat,
            'peserta_list' => $peserta_list
        );
        
        // Generate HTML untuk PDF
        $html = $this->load->view('sekprodi/rapat/cetak_daftar_hadir', $data, true);
        
        // Load HTML ke PDF library
        $this->pdf->load_html($html);
        $this->pdf->set_paper('A4', 'portrait');
        
        // Generate filename
        $filename = 'Daftar_Hadir_Rapat_' . date('Ymd_His', strtotime($rapat->tanggal_rapat)) . '.pdf';
        
        // Stream PDF
        $this->pdf->stream($filename);
    }

    /**
     * Cetak Berita Acara Rapat (untuk Sekprodi)
     */
    public function cetak_berita_acara($id) {
        $role = $this->session->userdata('role');
        if ($role !== 'sekprodi') {
            show_404();
        }

        $rapat = $this->Rapat_model->get_rapat_with_detail($id);
        
        if (!$rapat) {
            $this->session->set_flashdata('error', 'Data rapat tidak ditemukan.');
            redirect('Rapat');
        }

        // Load library PDF
        $this->load->library('Pdf');
        
        // Ambil semua data peserta (dosen, tendik, mahasiswa, dan eksternal)
        $peserta_list = array();
        foreach ($rapat->peserta as $peserta) {
            // Tampilkan dosen, tendik, mahasiswa, dan eksternal
            if (in_array($peserta->role_peserta, array('dosen', 'tendik', 'mahasiswa', 'eksternal'))) {
                $peserta_list[] = $peserta;
            }
        }
        
        // Ambil semua foto dokumentasi (hanya file gambar)
        $foto_dokumentasi = array();
        if (isset($rapat->dokumentasi) && !empty($rapat->dokumentasi)) {
            $image_types = array('image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/bmp', 'image/webp');
            foreach ($rapat->dokumentasi as $dok) {
                $is_image = false;
                // Cek berdasarkan file_type
                if (isset($dok->file_type) && in_array(strtolower($dok->file_type), $image_types)) {
                    $is_image = true;
                }
                // Cek berdasarkan extension di file_path
                elseif (isset($dok->file_path) && preg_match('/\.(jpg|jpeg|png|gif|bmp|webp)$/i', $dok->file_path)) {
                    $is_image = true;
                }
                // Cek berdasarkan extension di file_name
                elseif (isset($dok->file_name) && preg_match('/\.(jpg|jpeg|png|gif|bmp|webp)$/i', $dok->file_name)) {
                    $is_image = true;
                }
                
                if ($is_image) {
                    $foto_dokumentasi[] = $dok;
                }
            }
        }
        
        // Ambil data Kepala Prodi dan Sekretaris Prodi dari parameter URL (opsional)
        $id_kepala_prodi = $this->input->get('kepala_prodi');
        $id_sekretaris_prodi = $this->input->get('sekretaris_prodi');
        
        $kepala_prodi = null;
        $sekretaris_prodi = null;
        
        // Jika ada parameter, ambil dari database
        if ($id_kepala_prodi) {
            $kepala_prodi = $this->Dosen_model->get_by_id($id_kepala_prodi);
        }
        if ($id_sekretaris_prodi) {
            $sekretaris_prodi = $this->Dosen_model->get_by_id($id_sekretaris_prodi);
        }
        
        // Jika tidak ada parameter, tetap null (akan ditampilkan sebagai "Belum dipilih" di view)
        
        $data = array(
            'rapat' => $rapat,
            'peserta_list' => $peserta_list,
            'foto_dokumentasi' => $foto_dokumentasi,
            'kepala_prodi' => $kepala_prodi,
            'sekretaris_prodi' => $sekretaris_prodi
        );
        
        // Generate HTML untuk PDF
        $html = $this->load->view('sekprodi/rapat/cetak_berita_acara', $data, true);
        
        // Load HTML ke PDF library
        $this->pdf->load_html($html);
        $this->pdf->set_paper('A4', 'portrait');
        
        // Generate filename
        $filename = 'Berita_Acara_Rapat_' . date('Ymd_His', strtotime($rapat->tanggal_rapat)) . '.pdf';
        
        // Stream PDF
        $this->pdf->stream($filename);
    }
}

