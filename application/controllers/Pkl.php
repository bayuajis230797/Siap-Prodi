<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Pkl Controller (Mahasiswa)
 * Mengelola PKL untuk mahasiswa
 */
class Pkl extends MY_Controller {

    public function __construct() {
        parent::__construct();
        $this->check_role('mahasiswa');
        $this->load->model('Mahasiswa_pkl_model');
        $this->load->model('Pkl_tempat_model');
        $this->load->model('Pkl_pengajuan_model');
        $this->load->model('Pkl_bimbingan_model');
        $this->load->model('Pkl_bimbingan_log_model');
        $this->load->model('Pkl_logbook_model');
        $this->load->model('Pkl_pengajuan_model');
        $this->load->model('Tahun_akademik_model');
        $this->load->model('Mahasiswa_model');
        $this->load->library('form_validation');
        $this->load->helper('file');
        $this->load->helper('gps');
        $this->load->helper('pkl');
        $this->load->helper('notifikasi');
        $this->load->helper('google_drive');
    }

    /**
     * Registrasi PKL / Index
     * Menampilkan halaman utama PKL dengan data tempat PKL
     */
    public function registrasi() {
        $this->index();
    }

    /**
     * Index - Halaman utama PKL
     */
    public function index() {
        $id_mahasiswa = $this->session->userdata('ref_id');
        $tahun_aktif = $this->Tahun_akademik_model->get_aktif();
        
        if (!$tahun_aktif) {
            $this->session->set_flashdata('error', 'Tidak ada tahun akademik aktif.');
            redirect('Halaman_utama');
        }

        // Cek apakah mahasiswa terdaftar PKL
        if (!$this->Mahasiswa_pkl_model->is_registered($id_mahasiswa, $tahun_aktif->id)) {
            $this->session->set_flashdata('error', 'Anda belum terdaftar sebagai peserta PKL. Silakan hubungi Sekprodi.');
            redirect('Halaman_utama');
        }

        // Ambil data tempat PKL
        $tempat_pkl = $this->Pkl_tempat_model->get_by_mahasiswa($id_mahasiswa, $tahun_aktif->id);
        
        // Cek apakah pembimbing sudah ditetapkan
        $pembimbing_sudah_ditetapkan = false;
        $pengajuan = null;
        if ($tempat_pkl && $tempat_pkl->status_acc_tempat === 'disetujui') {
            $pengajuan = $this->Pkl_pengajuan_model->get_by_mahasiswa($id_mahasiswa, $tahun_aktif->id);
            if ($pengajuan && $pengajuan->id_pembimbing) {
                $pembimbing_sudah_ditetapkan = true;
            }
        }

        // Ambil data bimbingan jika ada
        $bimbingan = null;
        $bimbingan_enabled = false;
        $is_layak_sidang = false;
        if ($pembimbing_sudah_ditetapkan && $pengajuan) {
            $bimbingan = $this->Pkl_bimbingan_model->get_by_mahasiswa($id_mahasiswa, $tahun_aktif->id);
            if ($bimbingan) {
                $bimbingan_enabled = ($pengajuan->status_acc_judul === 'acc');
                $is_layak_sidang = ($pengajuan->is_layak_sidang == 1);
            }
        }

        // Cek apakah bisa submit pengajuan
        $can_submit = false;
        if ($tempat_pkl && $tempat_pkl->status === 'selesai' && 
            $tempat_pkl->status_acc_tempat === 'disetujui' && 
            $pembimbing_sudah_ditetapkan) {
            if (!$pengajuan || $pengajuan->status === 'ditolak') {
                $can_submit = true;
            }
        }

        $data = array(
            'title' => 'Registrasi PKL',
            'page_title' => 'Registrasi PKL',
            'breadcrumb' => array(
                array('label' => 'PKL', 'url' => '#'),
                array('label' => 'Registrasi PKL', 'active' => true)
            ),
            'sidebar_menu' => generate_sidebar_menu('mahasiswa'),
            'tahun_aktif' => $tahun_aktif,
            'tempat_pkl' => $tempat_pkl,
            'pembimbing_sudah_ditetapkan' => $pembimbing_sudah_ditetapkan,
            'pengajuan' => $pengajuan,
            'bimbingan' => $bimbingan,
            'bimbingan_enabled' => $bimbingan_enabled,
            'is_layak_sidang' => $is_layak_sidang,
            'can_submit' => $can_submit,
            'workflow_state' => resolve_pkl_workflow_state($id_mahasiswa, $tahun_aktif)
        );

        $data['content'] = $this->load->view('mahasiswa/pkl', $data, true);
        $this->load->view('layouts/main', $data);
    }

    /**
     * Input/Edit Data Tempat PKL
     */
    public function input_tempat() {
        $id_mahasiswa = $this->session->userdata('ref_id');
        $tahun_aktif = $this->Tahun_akademik_model->get_aktif();
        
        if (!$tahun_aktif) {
            $this->session->set_flashdata('error', 'Tidak ada tahun akademik aktif.');
            redirect('Pkl');
        }

        // Cek apakah mahasiswa terdaftar PKL
        if (!$this->Mahasiswa_pkl_model->is_registered($id_mahasiswa, $tahun_aktif->id)) {
            $this->session->set_flashdata('error', 'Anda belum terdaftar sebagai peserta PKL.');
            redirect('Halaman_utama');
        }

        // Ambil data tempat PKL jika sudah ada
        $tempat_pkl = $this->Pkl_tempat_model->get_by_mahasiswa($id_mahasiswa, $tahun_aktif->id);
        
        // Cek apakah pembimbing sudah ditetapkan
        $pembimbing_sudah_ditetapkan = false;
        $pengajuan = null;
        if ($tempat_pkl && $tempat_pkl->status_acc_tempat === 'disetujui') {
            $pengajuan = $this->Pkl_pengajuan_model->get_by_mahasiswa($id_mahasiswa, $tahun_aktif->id);
            if ($pengajuan && $pengajuan->id_pembimbing) {
                $pembimbing_sudah_ditetapkan = true;
            }
        }

        if ($this->input->method() === 'post') {
            $this->form_validation->set_rules('nama_instansi', 'Nama Instansi', 'required|trim');
            $this->form_validation->set_rules('alamat_instansi', 'Alamat Instansi', 'required|trim');
            $this->form_validation->set_rules('tanggal_mulai', 'Tanggal Mulai', 'required|trim');
            $this->form_validation->set_rules('tanggal_selesai', 'Tanggal Selesai', 'required|trim');
            $this->form_validation->set_rules('latitude', 'Latitude', 'required|numeric');
            $this->form_validation->set_rules('longitude', 'Longitude', 'required|numeric');

            if ($this->form_validation->run() == FALSE) {
                // Load form dengan error
            } else {
                $data_tempat = array(
                    'id_mahasiswa' => $id_mahasiswa,
                    'id_tahun_akademik' => $tahun_aktif->id,
                    'nama_instansi' => $this->input->post('nama_instansi'),
                    'alamat_instansi' => $this->input->post('alamat_instansi'),
                    'tanggal_mulai' => $this->input->post('tanggal_mulai'),
                    'tanggal_selesai' => $this->input->post('tanggal_selesai'),
                    'latitude' => $this->input->post('latitude') ? (float)$this->input->post('latitude') : null,
                    'longitude' => $this->input->post('longitude') ? (float)$this->input->post('longitude') : null,
                    'nama_pembimbing_lapangan' => $this->input->post('nama_pembimbing_lapangan') ?: null,
                    'jabatan_pembimbing_lapangan' => $this->input->post('jabatan_pembimbing_lapangan') ?: null,
                    'no_hp_pembimbing_lapangan' => $this->input->post('no_hp_pembimbing_lapangan') ?: null,
                    'email_pembimbing_lapangan' => $this->input->post('email_pembimbing_lapangan') ?: null,
                    'status' => 'draft',
                    'status_acc_tempat' => 'pending'
                );

                if ($tempat_pkl) {
                    // Update jika sudah ada
                    if ($this->Pkl_tempat_model->update($tempat_pkl->id, $data_tempat)) {
                        $this->session->set_flashdata('success', 'Data tempat PKL berhasil diperbarui.');
                    } else {
                        $this->session->set_flashdata('error', 'Gagal memperbarui data tempat PKL.');
                    }
                } else {
                    // Create baru
                    if ($this->Pkl_tempat_model->create($data_tempat)) {
                        $this->session->set_flashdata('success', 'Data tempat PKL berhasil disimpan.');
                    } else {
                        $this->session->set_flashdata('error', 'Gagal menyimpan data tempat PKL.');
                    }
                }
                redirect('Pkl');
            }
        }

        $data = array(
            'title' => ($tempat_pkl ? 'Edit' : 'Input') . ' Data Tempat PKL',
            'page_title' => ($tempat_pkl ? 'Edit' : 'Input') . ' Data Tempat PKL',
            'breadcrumb' => array(
                array('label' => 'PKL', 'url' => site_url('Pkl')),
                array('label' => 'Input Tempat PKL', 'active' => true)
            ),
            'sidebar_menu' => generate_sidebar_menu('mahasiswa'),
            'tahun_aktif' => $tahun_aktif,
            'tempat_pkl' => $tempat_pkl,
            'pembimbing_sudah_ditetapkan' => $pembimbing_sudah_ditetapkan,
            'pengajuan' => $pengajuan
        );

        $data['content'] = $this->load->view('mahasiswa/pkl_input_tempat', $data, true);
        $this->load->view('layouts/main', $data);
    }

    /**
     * Selesai input tempat PKL (ubah status dari draft ke selesai)
     */
    public function selesai_tempat() {
        $id_mahasiswa = $this->session->userdata('ref_id');
        $tahun_aktif = $this->Tahun_akademik_model->get_aktif();
        
        if (!$tahun_aktif) {
            $this->session->set_flashdata('error', 'Tidak ada tahun akademik aktif.');
            redirect('Pkl');
        }

        $tempat_pkl = $this->Pkl_tempat_model->get_by_mahasiswa($id_mahasiswa, $tahun_aktif->id);
        
        if (!$tempat_pkl) {
            $this->session->set_flashdata('error', 'Data tempat PKL tidak ditemukan.');
            redirect('Pkl');
        }

        if ($tempat_pkl->id_mahasiswa != $id_mahasiswa) {
            $this->session->set_flashdata('error', 'Anda tidak memiliki akses ke data ini.');
            redirect('Pkl');
        }

        if ($tempat_pkl->status === 'selesai') {
            $this->session->set_flashdata('info', 'Data tempat PKL sudah dalam status selesai.');
            redirect('Pkl');
        }

        // Update status menjadi selesai (antrean sekprodi)
        if ($this->Pkl_tempat_model->update($tempat_pkl->id, array(
            'status' => 'selesai',
            'status_acc_tempat' => 'pending'
        ))) {
            $this->load->helper(array('notifikasi', 'sekprodi_badge'));
            notify_sekprodi(
                'Ajuan Tempat / Judul PKL Baru',
                'Ada data tempat PKL baru yang menunggu persetujuan sekprodi.',
                site_url('Pkl_tempat_sekprodi'),
                'pkl_judul'
            );
            $this->session->set_flashdata('success', 'Data tempat PKL telah dikirim ke Sekprodi untuk persetujuan.');
        } else {
            $this->session->set_flashdata('error', 'Gagal mengubah status data tempat PKL.');
        }

        redirect('Pkl');
    }

    /**
     * Pengajuan Judul PKL
     * TODO: Implementasi fitur pengajuan judul PKL
     */
    /**
     * Pengajuan Judul PKL - redirect ke submit
     */
    public function pengajuan() {
        redirect('Pkl/submit');
    }

    /**
     * Detail pengajuan judul PKL untuk mahasiswa
     */
    public function detail_pengajuan($id) {
        $id_mahasiswa = $this->session->userdata('ref_id');
        
        // Ambil pengajuan dengan join ke mahasiswa
        $pengajuan = $this->Pkl_pengajuan_model->get_by_id($id);
        
        if (!$pengajuan) {
            $this->session->set_flashdata('error', 'Data pengajuan tidak ditemukan.');
            redirect('Pkl');
        }
        
        // Cek apakah pengajuan ini milik mahasiswa yang login
        if ($pengajuan->id_mahasiswa != $id_mahasiswa) {
            $this->session->set_flashdata('error', 'Anda tidak memiliki akses untuk melihat pengajuan ini.');
            redirect('Pkl');
        }
        
        // Ambil data mahasiswa untuk menampilkan NIM, nama, kelas
        $this->load->model('Mahasiswa_model');
        $mahasiswa = $this->Mahasiswa_model->get_by_id($id_mahasiswa);
        
        // Ambil data tempat PKL
        $tempat_pkl = $this->Pkl_tempat_model->get_by_mahasiswa($id_mahasiswa, $pengajuan->id_tahun_akademik);
        
        $data = array(
            'title' => 'Detail Pengajuan Judul PKL',
            'page_title' => 'Detail Pengajuan Judul PKL',
            'breadcrumb' => array(
                array('label' => 'PKL', 'url' => site_url('Pkl')),
                array('label' => 'Detail Pengajuan', 'active' => true)
            ),
            'sidebar_menu' => generate_sidebar_menu('mahasiswa'),
            'pengajuan' => $pengajuan,
            'mahasiswa' => $mahasiswa,
            'tempat_pkl' => $tempat_pkl
        );
        
        $data['content'] = $this->load->view('mahasiswa/pkl_detail_pengajuan', $data, true);
        $this->load->view('layouts/main', $data);
    }


    /**
     * Kirim pesan bimbingan
     * Menerima ID terenkripsi
     */
    public function kirim_pesan($encoded_id) {
        if ($this->input->method() !== 'post') {
            show_404();
        }

        $id = decode_id($encoded_id);
        if ($id === false) {
            show_404();
        }

        $id_mahasiswa = $this->session->userdata('ref_id');
        $user_id = $this->session->userdata('user_id');
        
        if (!$user_id) {
            $this->session->set_flashdata('error', 'Session tidak valid.');
            redirect('Pkl/bimbingan');
        }

        $bimbingan = $this->Pkl_bimbingan_model->get_by_id($id);
        if (!$bimbingan || $bimbingan->id_mahasiswa != $id_mahasiswa) {
            $this->session->set_flashdata('error', 'Data bimbingan tidak ditemukan.');
            redirect('Pkl/bimbingan');
        }

        $this->form_validation->set_rules('pesan', 'Pesan', 'required|trim');

        if ($this->form_validation->run() == FALSE) {
            redirect('Pkl/bimbingan_detail/' . encode_id($id));
        }

        $file_path = null;
        $file_name = null;
        if (!empty($_FILES['file_berkas']['name'])) {
            require_google_drive_or_redirect();
            $this->load->library('Upload_to_drive');
            $share_emails = array();
            $email = $this->upload_to_drive->get_drive_email_by_role_ref('dosen', (int) $bimbingan->id_pembimbing);
            if ($email) {
                $share_emails[] = $email;
            }
            $upload = $this->upload_to_drive->handle_upload('file_berkas', $this->upload_to_drive->default_options(
                array('SIAP', 'PKL', 'bimbingan', 'pesan'),
                $share_emails
            ));
            if (empty($upload['ok'])) {
                $this->session->set_flashdata('error', isset($upload['message']) ? $upload['message'] : 'Gagal upload berkas.');
                redirect('Pkl/bimbingan_detail/' . encode_id($id));
            }
            $file_path = $upload['webViewLink'];
            $file_name = !empty($upload['file_name']) ? $upload['file_name'] : 'Lampiran Bimbingan PKL';
        }

        $log_data = array(
            'id_bimbingan' => $id,
            'id_pengguna' => $user_id,
            'role_pengguna' => 'mahasiswa',
            'pesan' => $this->input->post('pesan'),
            'status_revisi' => null,
            'keterangan_revisi' => null,
            'file_path' => $file_path,
            'file_name' => $file_name
        );

        if ($this->Pkl_bimbingan_log_model->create($log_data)) {
            // Update last_activity
            $this->Pkl_bimbingan_model->update($id, array('last_activity' => date('Y-m-d H:i:s')));
            send_in_app_notification_by_ref(
                'dosen',
                (int) $bimbingan->id_pembimbing,
                'Pesan Bimbingan PKL Baru',
                'Mahasiswa mengirim pesan pada topik: ' . $bimbingan->judul_topik,
                site_url('Bimbingan_pkl/detail/' . $id)
            );
            $this->session->set_flashdata('success', 'Pesan berhasil dikirim.');
        } else {
            $this->session->set_flashdata('error', 'Gagal mengirim pesan.');
        }

        redirect('Pkl/bimbingan_detail/' . encode_id($id));
    }

    /**
     * Download file bimbingan (untuk backward compatibility dengan file upload)
     * Jika file_path adalah link Google Drive, redirect ke link tersebut
     */
    public function download_file($log_id) {
        $id_mahasiswa = $this->session->userdata('ref_id');
        $log = $this->Pkl_bimbingan_log_model->get_by_id($log_id);
        
        if (!$log || !$log->file_path) {
            show_404();
        }

        $bimbingan = $this->Pkl_bimbingan_model->get_by_id($log->id_bimbingan);
        if (!$bimbingan || $bimbingan->id_mahasiswa != $id_mahasiswa) {
            show_404();
        }

        // Cek apakah file_path adalah link Google Drive
        $this->load->helper('google_drive');
        if (is_valid_google_drive_link($log->file_path)) {
            // Redirect ke Google Drive
            $normalized_url = normalize_google_drive_link($log->file_path);
            redirect($normalized_url, 'location', 302);
        } else {
            // File upload biasa (backward compatibility)
            $file_path = FCPATH . $log->file_path;
            if (!file_exists($file_path)) {
                $this->session->set_flashdata('error', 'File tidak ditemukan.');
                redirect('Pkl/bimbingan_detail/' . encode_id($log->id_bimbingan));
            }

            $this->load->helper('download');
            force_download($log->file_name ?: basename($file_path), file_get_contents($file_path));
        }
    }

    /**
     * Logbook PKL
     * Input dan daftar logbook harian PKL (tanpa approve dosen)
     */
    public function logbook() {
        $id_mahasiswa = $this->session->userdata('ref_id');
        $tahun_aktif = $this->Tahun_akademik_model->get_aktif();
        
        if (!$tahun_aktif) {
            $this->session->set_flashdata('error', 'Tidak ada tahun akademik aktif.');
            redirect('Pkl');
        }

        // Cek apakah mahasiswa terdaftar PKL
        if (!$this->Mahasiswa_pkl_model->is_registered($id_mahasiswa, $tahun_aktif->id)) {
            $this->session->set_flashdata('error', 'Anda belum terdaftar sebagai peserta PKL.');
            redirect('Halaman_utama');
        }

        // Ambil pengajuan PKL untuk mendapatkan id_pkl_pengajuan
        $pengajuan = $this->Pkl_pengajuan_model->get_by_mahasiswa($id_mahasiswa, $tahun_aktif->id);

        // Handle POST (simpan logbook)
        if ($this->input->method() === 'post') {
            $this->form_validation->set_rules('tanggal', 'Tanggal', 'required');
            $this->form_validation->set_rules('jam_mulai', 'Jam Mulai', 'required');
            $this->form_validation->set_rules('jam_selesai', 'Jam Selesai', 'required');
            $this->form_validation->set_rules('aktivitas', 'Aktivitas', 'required|trim|min_length[10]');
            $this->form_validation->set_rules('latitude', 'Latitude', 'numeric');
            $this->form_validation->set_rules('longitude', 'Longitude', 'numeric');

            if ($this->form_validation->run() == FALSE) {
                // Load form dengan error
            } else {
                $tanggal = $this->input->post('tanggal');
                
                // Validasi: tanggal tidak boleh lebih dari hari ini
                if ($tanggal > date('Y-m-d')) {
                    $this->session->set_flashdata('error', 'Tanggal logbook tidak boleh lebih dari hari ini.');
                } else {
                    // Cek apakah sudah ada logbook untuk tanggal ini
                    $existing = $this->Pkl_logbook_model->get_by_tanggal($id_mahasiswa, $tanggal);
                    
                    $logbook_data = array(
                        'id_mahasiswa' => $id_mahasiswa,
                        'id_pkl_pengajuan' => $pengajuan ? $pengajuan->id : null,
                        'tanggal' => $tanggal,
                        'jam_mulai' => $this->input->post('jam_mulai'),
                        'jam_selesai' => $this->input->post('jam_selesai'),
                        'aktivitas' => $this->input->post('aktivitas'),
                        'latitude_logbook' => $this->input->post('latitude') ? (float)$this->input->post('latitude') : null,
                        'longitude_logbook' => $this->input->post('longitude') ? (float)$this->input->post('longitude') : null
                    );

                    if ($existing) {
                        // Update logbook yang sudah ada
                        if ($this->Pkl_logbook_model->update($existing->id, $logbook_data)) {
                            $this->session->set_flashdata('success', 'Logbook berhasil diperbarui.');
                        } else {
                            $this->session->set_flashdata('error', 'Gagal memperbarui logbook.');
                        }
                    } else {
                        // Create logbook baru
                        if ($this->Pkl_logbook_model->create($logbook_data)) {
                            $this->session->set_flashdata('success', 'Logbook berhasil disimpan.');
                        } else {
                            $this->session->set_flashdata('error', 'Gagal menyimpan logbook.');
                        }
                    }
                }
                redirect('Pkl/logbook');
            }
        }

        // Ambil daftar logbook
        $logbook_list = $this->Pkl_logbook_model->get_by_mahasiswa($id_mahasiswa, $tahun_aktif->id);
        
        // Statistik logbook
        $statistik = $this->Pkl_logbook_model->get_statistik($id_mahasiswa, $tahun_aktif->id);
        
        // Ambil data tempat PKL untuk menghitung jarak
        $tempat_pkl = $this->Pkl_tempat_model->get_by_mahasiswa($id_mahasiswa, $tahun_aktif->id);
        
        // Load helper GPS untuk menghitung jarak
        $this->load->helper('gps');
        
        // Hitung jarak untuk setiap logbook yang memiliki koordinat
        if ($tempat_pkl && $tempat_pkl->latitude && $tempat_pkl->longitude) {
            foreach ($logbook_list as $logbook) {
                if ($logbook->latitude_logbook && $logbook->longitude_logbook) {
                    $logbook->jarak_dari_tempat = calculate_distance_gps(
                        $tempat_pkl->latitude,
                        $tempat_pkl->longitude,
                        $logbook->latitude_logbook,
                        $logbook->longitude_logbook
                    );
                } else {
                    $logbook->jarak_dari_tempat = null;
                }
            }
        }

        $data = array(
            'title' => 'Logbook PKL',
            'page_title' => 'Logbook PKL',
            'breadcrumb' => array(
                array('label' => 'PKL', 'url' => site_url('Pkl')),
                array('label' => 'Logbook PKL', 'active' => true)
            ),
            'sidebar_menu' => generate_sidebar_menu('mahasiswa'),
            'logbook_list' => $logbook_list,
            'statistik' => $statistik,
            'pengajuan' => $pengajuan,
            'tempat_pkl' => $tempat_pkl
        );

        $data['content'] = $this->load->view('mahasiswa/pkl/logbook', $data, true);
        $this->load->view('layouts/main', $data);
    }

    /**
     * Delete logbook
     */
    public function delete_logbook($id) {
        $id_mahasiswa = $this->session->userdata('ref_id');
        $logbook = $this->Pkl_logbook_model->get_by_id($id);
        
        if (!$logbook || $logbook->id_mahasiswa != $id_mahasiswa) {
            $this->session->set_flashdata('error', 'Logbook tidak ditemukan atau Anda tidak memiliki akses.');
            redirect('Pkl/logbook');
        }

        // Hanya bisa hapus logbook untuk hari ini atau hari sebelumnya
        $tanggal_logbook = strtotime($logbook->tanggal);
        $tanggal_hari_ini = strtotime(date('Y-m-d'));
        $selisih_hari = ($tanggal_hari_ini - $tanggal_logbook) / 86400;

        if ($selisih_hari > 1) {
            $this->session->set_flashdata('error', 'Logbook hanya bisa dihapus untuk hari ini atau hari sebelumnya.');
            redirect('Pkl/logbook');
        }

        if ($this->Pkl_logbook_model->delete($id)) {
            $this->session->set_flashdata('success', 'Logbook berhasil dihapus.');
        } else {
            $this->session->set_flashdata('error', 'Gagal menghapus logbook.');
        }

        redirect('Pkl/logbook');
    }

    /**
     * Submit / Pengajuan Judul PKL
     */
    public function submit() {
        $id_mahasiswa = $this->session->userdata('ref_id');
        $tahun_aktif = $this->Tahun_akademik_model->get_aktif();
        
        if (!$tahun_aktif) {
            $this->session->set_flashdata('error', 'Tidak ada tahun akademik aktif.');
            redirect('Pkl');
        }

        // Cek apakah mahasiswa terdaftar PKL
        if (!$this->Mahasiswa_pkl_model->is_registered($id_mahasiswa, $tahun_aktif->id)) {
            $this->session->set_flashdata('error', 'Anda belum terdaftar sebagai peserta PKL.');
            redirect('Halaman_utama');
        }

        // Cek apakah tempat PKL sudah disetujui dan pembimbing sudah ditentukan
        $tempat_pkl = $this->Pkl_tempat_model->get_by_mahasiswa($id_mahasiswa, $tahun_aktif->id);
        
        if (!$tempat_pkl || $tempat_pkl->status_acc_tempat !== 'disetujui') {
            $this->session->set_flashdata('error', 'Tempat PKL belum disetujui oleh Sekprodi. Silakan tunggu persetujuan tempat PKL terlebih dahulu.');
            redirect('Pkl');
        }

        // Pastikan tempat PKL sudah disetujui
        if (!$tempat_pkl || $tempat_pkl->status_acc_tempat !== 'disetujui') {
            $this->session->set_flashdata('error', 'Tempat PKL belum disetujui oleh Sekprodi. Silakan tunggu persetujuan tempat PKL terlebih dahulu.');
            redirect('Pkl');
        }
        
        // Ambil data pengajuan jika sudah ada (pengajuan dengan placeholder dibuat saat sekprodi approve tempat)
        $pengajuan = $this->Pkl_pengajuan_model->get_by_mahasiswa($id_mahasiswa, $tahun_aktif->id);
        
        // Pastikan pengajuan ada dan pembimbing sudah ditentukan
        if (!$pengajuan) {
            $this->session->set_flashdata('error', 'Data pengajuan PKL belum tersedia. Pembimbing PKL belum ditentukan oleh Sekprodi. Silakan tunggu penunjukkan pembimbing.');
            redirect('Pkl');
        }
        
        if (!$pengajuan->id_pembimbing) {
            $this->session->set_flashdata('error', 'Pembimbing PKL belum ditentukan oleh Sekprodi. Silakan tunggu penunjukkan pembimbing.');
            redirect('Pkl');
        }

        // Ambil semua pengajuan untuk ditampilkan dalam tabel (yang sudah di-submit, bukan placeholder)
        $pengajuan_list = array();
        $all_pengajuan = $this->Pkl_pengajuan_model->get_all_by_mahasiswa($id_mahasiswa, $tahun_aktif->id);
        foreach ($all_pengajuan as $p) {
            if ($p->judul_pkl != 'Menunggu Pengajuan Judul') {
                $pengajuan_list[] = $p;
            }
        }

        // Cek apakah bisa submit (hanya bisa jika belum ada judul atau judul ditolak/revisi)
        $can_submit = true;
        if (empty($pengajuan_list)) {
            $can_submit = true;
        } else {
            // Cek pengajuan terakhir
            $latest_pengajuan = $pengajuan_list[0]; // Sudah diurutkan DESC
            if ($latest_pengajuan->status === 'disetujui' && $latest_pengajuan->status_acc_judul === 'acc') {
                $can_submit = false;
            }
        }

        if ($this->input->method() === 'post') {
            $this->form_validation->set_rules('judul_pkl', 'Judul PKL', 'required|trim|min_length[10]');
            $this->form_validation->set_rules('temuan_masalah', 'Temuan Masalah di Tempat PKL', 'required|trim|min_length[20]');
            $this->form_validation->set_rules('deskripsi_pengajuan', 'Deskripsi (Latar Belakang)', 'required|trim|min_length[30]');
            if ($this->form_validation->run() == FALSE) {
                // Load form dengan error
            } else {
                $this->load->helper('google_drive');

                $file_proposal = ($pengajuan && $pengajuan->file_proposal) ? $pengajuan->file_proposal : null;
                if (!empty($_FILES['file_proposal']['name'])) {
                    require_google_drive_or_redirect();
                    $this->load->library('Upload_to_drive');
                    $share_emails = array();
                    if (!empty($pengajuan->id_pembimbing)) {
                        $email = $this->upload_to_drive->get_drive_email_by_role_ref('dosen', (int) $pengajuan->id_pembimbing);
                        if ($email) {
                            $share_emails[] = $email;
                        }
                    }
                    $upload = $this->upload_to_drive->handle_upload('file_proposal', $this->upload_to_drive->default_options(
                        array('SIAP', 'PKL', 'pengajuan_judul'),
                        $share_emails
                    ));
                    if (empty($upload['ok'])) {
                        $this->session->set_flashdata('error', isset($upload['message']) ? $upload['message'] : 'Gagal upload proposal.');
                        redirect('Pkl/submit');
                    }
                    $file_proposal = $upload['webViewLink'];
                }

                // Update atau create pengajuan
                // Langsung ke pembimbing untuk review (tidak ada status sekprodi)
                $data_pengajuan = array(
                    'judul_pkl' => $this->input->post('judul_pkl'),
                    'temuan_masalah' => $this->input->post('temuan_masalah'),
                    'deskripsi_pengajuan' => $this->input->post('deskripsi_pengajuan'),
                    'status' => 'disetujui', // Set sebagai disetujui agar pembimbing bisa review
                    'status_acc_judul' => 'pending', // Menunggu persetujuan dari pembimbing
                    'catatan_revisi' => null,
                );

                if ($file_proposal) {
                    $data_pengajuan['file_proposal'] = $file_proposal;
                }

                if ($pengajuan) {
                    // Update pengajuan yang sudah ada
                    // Pastikan id_pembimbing tetap ada dan status benar
                    // SELALU pastikan id_pembimbing ter-set, jangan sampai hilang saat update
                    if (!$pengajuan->id_pembimbing) {
                        $this->session->set_flashdata('error', 'Pembimbing belum ditentukan. Silakan hubungi Sekprodi.');
                        redirect('Pkl/submit');
                    }
                    
                    // Pastikan id_pembimbing selalu ter-set saat update
                    $data_pengajuan['id_pembimbing'] = $pengajuan->id_pembimbing;
                    
                    if ($this->Pkl_pengajuan_model->update($pengajuan->id, $data_pengajuan)) {
                        send_in_app_notification_by_ref(
                            'dosen',
                            (int) $pengajuan->id_pembimbing,
                            'Review Judul PKL Baru',
                            'Mahasiswa mengirim/ memperbarui judul PKL dan menunggu review Anda.',
                            site_url('Bimbingan_pkl')
                        );
                        $this->session->set_flashdata('success', 'Pengajuan judul PKL berhasil diperbarui. Menunggu persetujuan dari pembimbing.');
                    } else {
                        $this->session->set_flashdata('error', 'Gagal memperbarui pengajuan judul PKL.');
                    }
                } else {
                    // Create baru (seharusnya tidak terjadi karena pengajuan sudah dibuat sekprodi, tapi untuk jaga-jaga)
                    // Ambil id_pembimbing dari pengajuan yang sudah ada (seharusnya sudah di-check di atas)
                    $id_pembimbing = $pengajuan ? $pengajuan->id_pembimbing : null;
                    
                    if (!$id_pembimbing) {
                        $this->session->set_flashdata('error', 'Pembimbing belum ditentukan. Silakan hubungi Sekprodi.');
                        redirect('Pkl/submit');
                    }
                    
                    $data_pengajuan['id_mahasiswa'] = $id_mahasiswa;
                    $data_pengajuan['id_tahun_akademik'] = $tahun_aktif->id;
                    $data_pengajuan['id_pembimbing'] = $id_pembimbing;
                    
                    if ($this->Pkl_pengajuan_model->create($data_pengajuan)) {
                        send_in_app_notification_by_ref(
                            'dosen',
                            (int) $id_pembimbing,
                            'Review Judul PKL Baru',
                            'Mahasiswa mengirim pengajuan judul PKL baru dan menunggu review Anda.',
                            site_url('Bimbingan_pkl')
                        );
                        $this->session->set_flashdata('success', 'Pengajuan judul PKL berhasil dikirim. Menunggu persetujuan dari pembimbing.');
                    } else {
                        $this->session->set_flashdata('error', 'Gagal mengirim pengajuan judul PKL.');
                    }
                }

                redirect('Pkl/submit');
            }
        }

        $data = array(
            'title' => 'Pengajuan Judul PKL',
            'page_title' => 'Pengajuan Judul PKL',
            'breadcrumb' => array(
                array('label' => 'PKL', 'url' => site_url('Pkl')),
                array('label' => 'Pengajuan Judul PKL', 'active' => true)
            ),
            'sidebar_menu' => generate_sidebar_menu('mahasiswa'),
            'tahun_aktif' => $tahun_aktif,
            'pengajuan' => $pengajuan,
            'pengajuan_list' => $pengajuan_list,
            'can_submit' => $can_submit
        );

        // Tidak perlu redirect, biarkan user melihat tabel pengajuan

        $data['content'] = $this->load->view('mahasiswa/pkl_submit', $data, true);
        $this->load->view('layouts/main', $data);
    }

    /**
     * Jadwal PKL
     */
    public function jadwal() {
        $id_mahasiswa = $this->session->userdata('ref_id');
        $tahun_aktif = $this->Tahun_akademik_model->get_aktif();
        
        if (!$tahun_aktif) {
            $this->session->set_flashdata('error', 'Tidak ada tahun akademik aktif.');
            redirect('Pkl');
        }

        $this->load->model('Jadwal_pkl_model');
        $jadwal = $this->Jadwal_pkl_model->get_by_mahasiswa($id_mahasiswa, $tahun_aktif->id);

        $data = array(
            'title' => 'Jadwal PKL',
            'page_title' => 'Jadwal PKL',
            'breadcrumb' => array(
                array('label' => 'PKL', 'url' => site_url('Pkl')),
                array('label' => 'Jadwal PKL', 'active' => true)
            ),
            'sidebar_menu' => generate_sidebar_menu('mahasiswa'),
            'jadwal' => $jadwal,
            'tahun_aktif' => $tahun_aktif
        );

        // Buat view sederhana untuk jadwal
        $content = '<div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Jadwal PKL</h3>
                    </div>
                    <div class="card-body">';
        
        if ($jadwal) {
            $content .= '<table class="table table-bordered">
                <tr>
                    <th width="20%">Tanggal</th>
                    <td>' . date('d/m/Y', strtotime($jadwal->tanggal)) . '</td>
                </tr>
                <tr>
                    <th>Jam</th>
                    <td>' . date('H:i', strtotime($jadwal->jam_mulai)) . ' - ' . date('H:i', strtotime($jadwal->jam_selesai)) . '</td>
                </tr>
                <tr>
                    <th>Ruangan</th>
                    <td>' . htmlspecialchars($jadwal->ruangan) . '</td>
                </tr>
            </table>';
        } else {
            $content .= '<div class="alert alert-info">
                <i class="fas fa-info-circle"></i> Jadwal PKL belum ditetapkan. Silakan tunggu informasi lebih lanjut dari Sekprodi.
            </div>';
        }
        
        $content .= '</div></div></div></div>';
        
        $data['content'] = $content;
        $this->load->view('layouts/main', $data);
    }

    /**
     * Form perubahan judul PKL untuk mahasiswa (ketika ditolak pembimbing)
     */
    public function perubahan_judul() {
        $id_mahasiswa = $this->session->userdata('ref_id');
        $tahun_aktif = $this->Tahun_akademik_model->get_aktif();
        
        if (!$tahun_aktif) {
            $this->session->set_flashdata('error', 'Tidak ada tahun akademik aktif.');
            redirect('Pkl/submit');
        }

        $pengajuan = $this->Pkl_pengajuan_model->get_by_mahasiswa($id_mahasiswa, $tahun_aktif->id);
        
        if (!$pengajuan || $pengajuan->status_acc_judul !== 'revisi') {
            $this->session->set_flashdata('error', 'Anda tidak dapat mengajukan perubahan judul. Judul harus dalam status revisi terlebih dahulu.');
            redirect('Pkl/submit');
        }

        if ($this->input->method() === 'post') {
            $this->form_validation->set_rules('judul_revisi', 'Judul Revisi', 'required|trim|min_length[10]');

            if ($this->form_validation->run() == FALSE) {
                // Load form with errors
            } else {
                require_google_drive_or_redirect();
                $this->load->library('Upload_to_drive');
                $share_emails = array();
                if (!empty($pengajuan->id_pembimbing)) {
                    $email = $this->upload_to_drive->get_drive_email_by_role_ref('dosen', (int) $pengajuan->id_pembimbing);
                    if ($email) {
                        $share_emails[] = $email;
                    }
                }
                $upload = $this->upload_to_drive->handle_upload('file_approve_revisi', array_merge(
                    $this->upload_to_drive->default_options(array('SIAP', 'PKL', 'perubahan_judul'), $share_emails),
                    array('allowed_types' => 'pdf', 'max_size_kb' => 5120)
                ));
                if (empty($upload['ok'])) {
                    $this->session->set_flashdata('error', isset($upload['message']) ? $upload['message'] : 'Gagal upload bukti perubahan judul.');
                    redirect('Pkl/perubahan_judul');
                }
                $file_approve_revisi = $upload['webViewLink'];

                $judul_revisi = $this->input->post('judul_revisi');

                $update_data = array(
                    'judul_revisi' => $judul_revisi,
                    'file_approve_revisi' => $file_approve_revisi,
                    'status_approve_revisi' => 'pending'
                );

                if ($this->Pkl_pengajuan_model->update($pengajuan->id, $update_data)) {
                    $this->load->helper(array('notifikasi', 'sekprodi_badge'));
                    notify_sekprodi(
                        'Perubahan Judul PKL',
                        'Ada permintaan perubahan judul PKL yang menunggu validasi.',
                        site_url('Pkl_perubahan_judul_sekprodi'),
                        'pkl_perubahan'
                    );
                    $this->session->set_flashdata('success', 'Permintaan perubahan judul berhasil dikirim. Menunggu validasi dari Sekprodi.');
                    redirect('Pkl/submit');
                } else {
                    $this->session->set_flashdata('error', 'Gagal mengirim permintaan perubahan judul.');
                }
            }
        }

        $judul_revisi = $pengajuan->judul_revisi ?: '';

        $data = array(
            'title' => 'Perubahan Judul PKL',
            'page_title' => 'Perubahan Judul PKL',
            'breadcrumb' => array(
                array('label' => 'PKL', 'url' => site_url('Pkl')),
                array('label' => 'Pengajuan Judul PKL', 'url' => site_url('Pkl/submit')),
                array('label' => 'Perubahan Judul', 'active' => true)
            ),
            'sidebar_menu' => generate_sidebar_menu('mahasiswa'),
            'pengajuan' => $pengajuan,
            'judul_revisi' => $judul_revisi
        );

        $data['content'] = $this->load->view('mahasiswa/pkl_perubahan_judul', $data, true);
        $this->load->view('layouts/main', $data);
    }

    /**
     * Download file approve revisi PKL
     */
    public function download_approve_revisi($id) {
        $id_mahasiswa = $this->session->userdata('ref_id');
        $pengajuan = $this->Pkl_pengajuan_model->get_by_id($id);
        
        if (!$pengajuan) {
            $this->session->set_flashdata('error', 'Data pengajuan tidak ditemukan.');
            redirect('Pkl/submit');
        }
        
        // Cek apakah file approve revisi ada
        if (!$pengajuan->file_approve_revisi) {
            $this->session->set_flashdata('error', 'File tidak ditemukan.');
            redirect('Pkl/submit');
        }
        
        if ($pengajuan->id_mahasiswa != $id_mahasiswa) {
            $this->session->set_flashdata('error', 'Anda tidak memiliki akses untuk mengunduh file ini.');
            redirect('Pkl/submit');
        }

        if (strpos((string) $pengajuan->file_approve_revisi, 'http') === 0) {
            redirect($pengajuan->file_approve_revisi);
        }

        $file_path = $pengajuan->file_approve_revisi;
        $file_path = ltrim($file_path, './');
        $file_path = str_replace('\\', '/', $file_path);
        
        $full_path = FCPATH . $file_path;
        
        if (!file_exists($full_path)) {
            $this->session->set_flashdata('error', 'File tidak ditemukan di server.');
            redirect('Pkl/submit');
        }
        
        $this->load->helper('download');
        $file_name = basename($file_path);
        force_download($file_name, file_get_contents($full_path));
    }

    /**
     * Download file proposal PKL
     */
    public function download_proposal($id) {
        $id_mahasiswa = $this->session->userdata('ref_id');
        $pengajuan = $this->Pkl_pengajuan_model->get_by_id($id);
        
        if (!$pengajuan) {
            $this->session->set_flashdata('error', 'Data pengajuan tidak ditemukan.');
            redirect('Pkl/submit');
        }
        
        // Cek apakah file proposal ada
        if (!$pengajuan->file_proposal) {
            $this->session->set_flashdata('error', 'File proposal tidak ditemukan.');
            redirect('Pkl/submit');
        }
        
        // Cek akses: mahasiswa hanya bisa download file mereka sendiri
        if ($pengajuan->id_mahasiswa != $id_mahasiswa) {
            $this->session->set_flashdata('error', 'Anda tidak memiliki akses untuk mengunduh file ini.');
            redirect('Pkl/submit');
        }
        
        // Bersihkan path dari ./ jika ada dan normalisasi path
        $file_path = $pengajuan->file_proposal;
        $file_path = ltrim($file_path, './');
        $file_path = str_replace('\\', '/', $file_path);
        
        $full_path = FCPATH . $file_path;
        
        if (!file_exists($full_path)) {
            $this->session->set_flashdata('error', 'File tidak ditemukan di server.');
            redirect('Pkl/submit');
        }
        
        $this->load->helper('download');
        $file_name = basename($file_path);
        force_download($file_name, file_get_contents($full_path));
    }

    /**
     * Cetak Logbook PKL (PDF)
     */
    public function cetak_logbook() {
        $id_mahasiswa = $this->session->userdata('ref_id');
        $tahun_aktif = $this->Tahun_akademik_model->get_aktif();
        
        if (!$tahun_aktif) {
            $this->session->set_flashdata('error', 'Tidak ada tahun akademik aktif.');
            redirect('Pkl/logbook');
        }

        // Ambil data mahasiswa
        $mahasiswa = $this->Mahasiswa_model->get_by_id($id_mahasiswa);
        
        // Ambil data logbook
        $logbook_list = $this->Pkl_logbook_model->get_by_mahasiswa($id_mahasiswa, $tahun_aktif->id);
        
        // Ambil data tempat PKL
        $tempat_pkl = $this->Pkl_tempat_model->get_by_mahasiswa($id_mahasiswa, $tahun_aktif->id);
        
        // Ambil pengajuan PKL
        $pengajuan = $this->Pkl_pengajuan_model->get_by_mahasiswa($id_mahasiswa, $tahun_aktif->id);

        // Load library PDF dan helper
        $this->load->library('Pdf');
        $this->load->helper('app');
        
        $data = array(
            'mahasiswa' => $mahasiswa,
            'logbook_list' => $logbook_list,
            'tempat_pkl' => $tempat_pkl,
            'pengajuan' => $pengajuan,
            'tahun_aktif' => $tahun_aktif
        );
        
        // Generate HTML untuk PDF
        $html = $this->load->view('mahasiswa/pkl/cetak_logbook', $data, true);
        
        // Load HTML ke PDF library
        $this->pdf->load_html($html);
        $this->pdf->set_paper('A4', 'portrait');
        
        // Generate filename
        $filename = 'Logbook_PKL_' . $mahasiswa->nim . '_' . date('Ymd') . '.pdf';
        
        // Stream PDF
        $this->pdf->stream($filename);
    }

    /**
     * Bimbingan PKL - Daftar Topik Bimbingan
     */
    public function bimbingan() {
        $id_mahasiswa = $this->session->userdata('ref_id');
        $tahun_aktif = $this->Tahun_akademik_model->get_aktif();
        
        if (!$tahun_aktif) {
            $this->session->set_flashdata('error', 'Tidak ada tahun akademik aktif.');
            redirect('Pkl');
        }

        // Cek apakah mahasiswa terdaftar PKL
        if (!$this->Mahasiswa_pkl_model->is_registered($id_mahasiswa, $tahun_aktif->id)) {
            $this->session->set_flashdata('error', 'Anda belum terdaftar sebagai peserta PKL.');
            redirect('Halaman_utama');
        }

        // Ambil pengajuan PKL
        $pengajuan = $this->Pkl_pengajuan_model->get_by_mahasiswa($id_mahasiswa, $tahun_aktif->id);
        
        if (!$pengajuan || $pengajuan->status_acc_judul !== 'acc') {
            $this->session->set_flashdata('error', 'Judul PKL belum disetujui. Silakan tunggu persetujuan dari pembimbing.');
            redirect('Pkl');
        }

        // Ambil semua topik bimbingan
        $bimbingan_list = $this->Pkl_bimbingan_model->get_by_mahasiswa($id_mahasiswa, $tahun_aktif->id);
        $bimbingan_list = $this->enrich_bimbingan_workflow_status($bimbingan_list);

        $data = array(
            'title' => 'Bimbingan PKL',
            'page_title' => 'Bimbingan PKL',
            'breadcrumb' => array(
                array('label' => 'PKL', 'url' => site_url('Pkl')),
                array('label' => 'Bimbingan PKL', 'active' => true)
            ),
            'sidebar_menu' => generate_sidebar_menu('mahasiswa'),
            'bimbingan_list' => $bimbingan_list,
            'pengajuan' => $pengajuan,
            'tahun_aktif' => $tahun_aktif
        );

        $data['content'] = $this->load->view('mahasiswa/pkl/bimbingan', $data, true);
        $this->load->view('layouts/main', $data);
    }

    /**
     * Tambahkan status proses bimbingan agar monitoring progres lebih jelas.
     */
    private function enrich_bimbingan_workflow_status($bimbingan_list) {
        if (empty($bimbingan_list) || !is_array($bimbingan_list)) {
            return $bimbingan_list;
        }

        foreach ($bimbingan_list as $bimbingan) {
            $workflow = array(
                'key' => 'waiting_review',
                'label' => 'Menunggu Review Dosen',
                'class' => 'info'
            );

            if (($bimbingan->status ?? '') === 'closed') {
                $workflow = array(
                    'key' => 'closed',
                    'label' => 'Bimbingan Ditutup',
                    'class' => 'secondary'
                );
            } else {
                $latest_log = $this->Pkl_bimbingan_log_model->get_latest_by_bimbingan($bimbingan->id);
                if ($latest_log) {
                    if (($latest_log->role_pengguna ?? '') === 'dosen' && ($latest_log->status_revisi ?? '') === 'revisi') {
                        $workflow = array(
                            'key' => 'need_revision',
                            'label' => 'Perlu Revisi Mahasiswa',
                            'class' => 'warning'
                        );
                    } elseif (($latest_log->role_pengguna ?? '') === 'dosen' && ($latest_log->status_revisi ?? '') === 'acc') {
                        $workflow = array(
                            'key' => 'approved',
                            'label' => 'ACC Pembimbing',
                            'class' => 'success'
                        );
                    }
                }
            }

            $bimbingan->workflow_status_key = $workflow['key'];
            $bimbingan->workflow_status_label = $workflow['label'];
            $bimbingan->workflow_status_class = $workflow['class'];
        }

        return $bimbingan_list;
    }

    /**
     * Buat Topik Bimbingan Baru
     */
    public function bimbingan_create() {
        $id_mahasiswa = $this->session->userdata('ref_id');
        $tahun_aktif = $this->Tahun_akademik_model->get_aktif();
        
        if (!$tahun_aktif) {
            $this->session->set_flashdata('error', 'Tidak ada tahun akademik aktif.');
            redirect('Pkl/bimbingan');
        }

        // Cek apakah mahasiswa terdaftar PKL
        if (!$this->Mahasiswa_pkl_model->is_registered($id_mahasiswa, $tahun_aktif->id)) {
            $this->session->set_flashdata('error', 'Anda belum terdaftar sebagai peserta PKL.');
            redirect('Halaman_utama');
        }

        // Ambil pengajuan PKL
        $pengajuan = $this->Pkl_pengajuan_model->get_by_mahasiswa($id_mahasiswa, $tahun_aktif->id);
        
        if (!$pengajuan || !$pengajuan->id_pembimbing || $pengajuan->status_acc_judul !== 'acc') {
            $this->session->set_flashdata('error', 'Judul PKL belum disetujui atau pembimbing belum ditetapkan.');
            redirect('Pkl/bimbingan');
        }

        if ($this->input->method() === 'post') {
            $this->load->helper('app');
            $this->form_validation->set_rules('judul_topik', 'Judul Topik Bimbingan', 'required|trim|callback_valid_pkl_topik_bimbingan');
            $this->form_validation->set_rules('pesan', 'Pesan', 'trim');

            if ($this->form_validation->run() == FALSE) {
                // Load form dengan error
            } else {
                $this->load->helper('google_drive');
                require_google_drive_or_redirect();
                $this->load->library('Upload_to_drive');
                $share_emails = array();
                $email = $this->upload_to_drive->get_drive_email_by_role_ref('dosen', (int) $pengajuan->id_pembimbing);
                if ($email) {
                    $share_emails[] = $email;
                }
                $upload = $this->upload_to_drive->handle_upload('file_laporan', $this->upload_to_drive->default_options(
                    array('SIAP', 'PKL', 'bimbingan'),
                    $share_emails
                ));
                if (empty($upload['ok'])) {
                    $this->session->set_flashdata('error', isset($upload['message']) ? $upload['message'] : 'Gagal upload laporan.');
                } else {
                    $link_normalized = $upload['webViewLink'];
                    $upload_file_name = !empty($upload['file_name']) ? $upload['file_name'] : ('Laporan PKL - ' . $this->input->post('judul_topik'));

                    // Buat topik bimbingan
                    $bimbingan_data = array(
                        'id_mahasiswa' => $id_mahasiswa,
                        'id_pembimbing' => $pengajuan->id_pembimbing,
                        'id_tahun_akademik' => $tahun_aktif->id,
                        'judul_topik' => $this->input->post('judul_topik'),
                        'status' => 'open',
                        'last_activity' => date('Y-m-d H:i:s')
                    );
                    
                    $id_bimbingan = $this->Pkl_bimbingan_model->create($bimbingan_data);
                    
                    if ($id_bimbingan) {
                        // Buat log pertama
                        $user_id = $this->session->userdata('user_id');
                        $urutan = $this->Pkl_bimbingan_log_model->get_next_urutan($id_bimbingan);
                        
                        $log_data = array(
                            'id_bimbingan' => $id_bimbingan,
                            'id_pengguna' => $user_id,
                            'role_pengguna' => 'mahasiswa',
                            'pesan' => $this->input->post('pesan') ?: 'Topik bimbingan baru',
                            'status_revisi' => null,
                            'keterangan_revisi' => null,
                            'file_path' => $link_normalized,
                            'file_name' => $upload_file_name,
                            'urutan' => $urutan
                        );
                        
                        $this->Pkl_bimbingan_log_model->create($log_data);

                        send_in_app_notification_by_ref(
                            'dosen',
                            (int) $pengajuan->id_pembimbing,
                            'Topik Bimbingan PKL Baru',
                            'Mahasiswa mengajukan topik bimbingan: ' . $this->input->post('judul_topik'),
                            site_url('Bimbingan_pkl/detail/' . $id_bimbingan)
                        );

                        $this->session->set_flashdata('success', 'Topik bimbingan berhasil dibuat. Menunggu review dari pembimbing.');
                        redirect('Pkl/bimbingan_detail/' . encode_id($id_bimbingan));
                    } else {
                        $this->session->set_flashdata('error', 'Gagal membuat topik bimbingan.');
                    }
                }
            }
        }

        $data = array(
            'title' => 'Buat Topik Bimbingan',
            'page_title' => 'Buat Topik Bimbingan Baru',
            'breadcrumb' => array(
                array('label' => 'PKL', 'url' => site_url('Pkl')),
                array('label' => 'Bimbingan PKL', 'url' => site_url('Pkl/bimbingan')),
                array('label' => 'Buat Topik Baru', 'active' => true)
            ),
            'sidebar_menu' => generate_sidebar_menu('mahasiswa'),
            'pengajuan' => $pengajuan
        );

        $data['content'] = $this->load->view('mahasiswa/pkl/bimbingan_create', $data, true);
        $this->load->view('layouts/main', $data);
    }

    /**
     * Validasi topik bimbingan PKL (whitelist).
     */
    public function valid_pkl_topik_bimbingan($str) {
        $this->load->helper('app');
        if (valid_pkl_topik_bimbingan_label($str)) {
            return true;
        }
        $this->form_validation->set_message('valid_pkl_topik_bimbingan', 'Pilih topik bimbingan dari daftar yang disediakan.');
        return false;
    }

    /**
     * Detail Topik Bimbingan
     * Menerima ID terenkripsi di URL
     */
    public function bimbingan_detail($encoded_id) {
        $id = decode_id($encoded_id);
        if ($id === false) {
            show_404();
        }
        $id_mahasiswa = $this->session->userdata('ref_id');
        $bimbingan = $this->Pkl_bimbingan_model->get_by_id($id);
        
        if (!$bimbingan || $bimbingan->id_mahasiswa != $id_mahasiswa) {
            show_404();
        }

        // Ambil semua log bimbingan
        $logs = $this->Pkl_bimbingan_log_model->get_by_bimbingan($id);
        
        // Ambil statistik
        $statistik = $this->Pkl_bimbingan_log_model->get_statistik_by_bimbingan($id);
        
        // Cek status terakhir
        $latest_log = !empty($logs) ? end($logs) : null;
        $can_send_revisi = false;
        if ($latest_log && $latest_log->role_pengguna === 'dosen' && $latest_log->status_revisi === 'revisi') {
            $can_send_revisi = true;
        }

        $data = array(
            'title' => 'Detail Bimbingan PKL',
            'page_title' => 'Detail Bimbingan: ' . $bimbingan->judul_topik,
            'breadcrumb' => array(
                array('label' => 'PKL', 'url' => site_url('Pkl')),
                array('label' => 'Bimbingan PKL', 'url' => site_url('Pkl/bimbingan')),
                array('label' => 'Detail', 'active' => true)
            ),
            'sidebar_menu' => generate_sidebar_menu('mahasiswa'),
            'bimbingan' => $bimbingan,
            'logs' => $logs,
            'statistik' => $statistik,
            'can_send_revisi' => $can_send_revisi
        );

        $data['content'] = $this->load->view('mahasiswa/pkl/bimbingan_detail', $data, true);
        $this->load->view('layouts/main', $data);
    }

    /**
     * Kirim Pesan/Revisi Bimbingan
     * Menerima ID terenkripsi
     */
    public function bimbingan_kirim($encoded_id) {
        $id = decode_id($encoded_id);
        if ($id === false) {
            show_404();
        }
        $id_mahasiswa = $this->session->userdata('ref_id');
        $bimbingan = $this->Pkl_bimbingan_model->get_by_id($id);
        
        if (!$bimbingan || $bimbingan->id_mahasiswa != $id_mahasiswa) {
            show_404();
        }

        if ($this->input->method() !== 'post') {
            show_404();
        }

        // Cek apakah bisa kirim revisi
        $logs = $this->Pkl_bimbingan_log_model->get_by_bimbingan($id);
        $latest_log = !empty($logs) ? end($logs) : null;
        
        if (!$latest_log || $latest_log->role_pengguna !== 'dosen' || $latest_log->status_revisi !== 'revisi') {
            $this->session->set_flashdata('error', 'Anda tidak dapat mengirim revisi saat ini.');
            redirect('Pkl/bimbingan_detail/' . encode_id($id));
        }

        $this->form_validation->set_rules('pesan', 'Pesan', 'trim');

        if ($this->form_validation->run() == FALSE) {
            $this->session->set_flashdata('error', validation_errors());
            redirect('Pkl/bimbingan_detail/' . encode_id($id));
        }

        $this->load->helper('google_drive');
        require_google_drive_or_redirect();
        $this->load->library('Upload_to_drive');
        $share_emails = array();
        $email = $this->upload_to_drive->get_drive_email_by_role_ref('dosen', (int) $bimbingan->id_pembimbing);
        if ($email) {
            $share_emails[] = $email;
        }
        $upload = $this->upload_to_drive->handle_upload('file_laporan', $this->upload_to_drive->default_options(
            array('SIAP', 'PKL', 'bimbingan'),
            $share_emails
        ));
        if (empty($upload['ok'])) {
            $this->session->set_flashdata('error', isset($upload['message']) ? $upload['message'] : 'Gagal upload revisi.');
            redirect('Pkl/bimbingan_detail/' . encode_id($id));
        }

        $user_id = $this->session->userdata('user_id');
        $urutan = $this->Pkl_bimbingan_log_model->get_next_urutan($id);
        
        $log_data = array(
            'id_bimbingan' => $id,
            'id_pengguna' => $user_id,
            'role_pengguna' => 'mahasiswa',
            'pesan' => $this->input->post('pesan') ?: 'Revisi laporan',
            'status_revisi' => null,
            'keterangan_revisi' => null,
            'file_path' => $upload['webViewLink'],
            'file_name' => !empty($upload['file_name']) ? $upload['file_name'] : ('Revisi Laporan PKL - ' . $bimbingan->judul_topik),
            'urutan' => $urutan
        );
        
        if ($this->Pkl_bimbingan_log_model->create($log_data)) {
            // Update last_activity
            $this->Pkl_bimbingan_model->update($id, array('last_activity' => date('Y-m-d H:i:s')));
            send_in_app_notification_by_ref(
                'dosen',
                (int) $bimbingan->id_pembimbing,
                'Revisi Bimbingan PKL',
                'Mahasiswa mengirim revisi pada topik: ' . $bimbingan->judul_topik,
                site_url('Bimbingan_pkl/detail/' . $id)
            );
            $this->session->set_flashdata('success', 'Revisi berhasil dikirim. Menunggu review dari pembimbing.');
        } else {
            $this->session->set_flashdata('error', 'Gagal mengirim revisi.');
        }

        redirect('Pkl/bimbingan_detail/' . encode_id($id));
    }

    /**
     * Cetak PDF Histori Bimbingan PKL
     * Menerima ID terenkripsi (opsional)
     */
    public function cetak_histori_bimbingan($encoded_id = null) {
        $id = null;
        if ($encoded_id) {
            $id = decode_id($encoded_id);
            if ($id === false) {
                show_404();
            }
        }
        $id_mahasiswa = $this->session->userdata('ref_id');
        $tahun_aktif = $this->Tahun_akademik_model->get_aktif();
        
        if (!$tahun_aktif) {
            $this->session->set_flashdata('error', 'Tidak ada tahun akademik aktif.');
            redirect('Pkl/bimbingan');
        }

        // Ambil data mahasiswa
        $mahasiswa = $this->Mahasiswa_model->get_by_id($id_mahasiswa);
        
        // Ambil pengajuan PKL
        $pengajuan = $this->Pkl_pengajuan_model->get_by_mahasiswa($id_mahasiswa, $tahun_aktif->id);
        
        if (!$pengajuan || !$pengajuan->id_pembimbing) {
            $this->session->set_flashdata('error', 'Data pembimbing tidak ditemukan.');
            redirect('Pkl/bimbingan');
        }

        // Ambil data pembimbing
        $this->load->model('Dosen_model');
        $pembimbing = $this->Dosen_model->get_by_id($pengajuan->id_pembimbing);

        // Jika ada ID topik spesifik, hanya ambil topik tersebut
        if ($id) {
            $bimbingan_specific = $this->Pkl_bimbingan_model->get_by_id($id);
            if (!$bimbingan_specific || $bimbingan_specific->id_mahasiswa != $id_mahasiswa) {
                $this->session->set_flashdata('error', 'Data bimbingan tidak ditemukan.');
                redirect('Pkl/bimbingan');
            }
            $bimbingan_list = array($bimbingan_specific);
        } else {
            // Ambil semua topik bimbingan
            $bimbingan_list = $this->Pkl_bimbingan_model->get_by_mahasiswa($id_mahasiswa, $tahun_aktif->id);
        }
        
        // Ambil semua log untuk statistik
        $all_logs = array();
        $total_topik = count($bimbingan_list);
        $total_log = 0;
        $total_acc = 0;
        $total_revisi = 0;
        
        // Load model untuk mendapatkan nama pengirim
        $this->load->model('User_model');
        
        foreach ($bimbingan_list as $bimbingan) {
            $logs = $this->Pkl_bimbingan_log_model->get_by_bimbingan($bimbingan->id);
            
            // Ambil nama pengirim untuk setiap log
            foreach ($logs as $log) {
                $nama_pengirim = '';
                if ($log->role_pengguna === 'mahasiswa') {
                    $nama_pengirim = $mahasiswa->nama_mahasiswa;
                } elseif ($log->role_pengguna === 'dosen') {
                    $nama_pengirim = $pembimbing ? $pembimbing->nama_dosen : 'Dosen';
                } else {
                    // Ambil dari user jika ada
                    $user = $this->User_model->get_by_id($log->id_pengguna);
                    if ($user) {
                        if ($user->role === 'mahasiswa') {
                            $mhs = $this->Mahasiswa_model->get_by_id($user->ref_id);
                            $nama_pengirim = $mhs ? $mhs->nama_mahasiswa : 'Mahasiswa';
                        } elseif ($user->role === 'dosen') {
                            $dosen = $this->Dosen_model->get_by_id($user->ref_id);
                            $nama_pengirim = $dosen ? $dosen->nama_dosen : 'Dosen';
                        } else {
                            $nama_pengirim = $user->username;
                        }
                    } else {
                        $nama_pengirim = ucfirst($log->role_pengguna);
                    }
                }
                $log->nama_pengirim = $nama_pengirim;
            }
            
            $all_logs[$bimbingan->id] = array(
                'bimbingan' => $bimbingan,
                'logs' => $logs
            );
            $total_log += count($logs);
            foreach ($logs as $log) {
                if ($log->status_revisi === 'acc') $total_acc++;
                if ($log->status_revisi === 'revisi') $total_revisi++;
            }
        }

        // Histori pengajuan judul untuk PDF (sama pola Skripsi: label di kolom Topik, teks judul di Keterangan)
        $ph_judul = Pkl_pengajuan_model::PLACEHOLDER_JUDUL;
        $histori_pengajuan = array();
        if ($pengajuan && !empty($pengajuan->judul_pkl) && $pengajuan->judul_pkl !== $ph_judul && !empty($pengajuan->created_at)) {
            $histori_pengajuan[] = array(
                'tanggal' => $pengajuan->created_at,
                'topik' => 'Pengajuan Judul PKL',
                'pengirim' => $mahasiswa->nama_mahasiswa,
                'status' => 'pending',
                'keterangan' => $pengajuan->judul_pkl,
            );
        }
        if ($pengajuan && isset($pengajuan->status_acc_judul) && $pengajuan->status_acc_judul === 'acc' && $pembimbing) {
            $histori_pengajuan[] = array(
                'tanggal' => $pengajuan->updated_at ?: $pengajuan->created_at,
                'topik' => 'ACC Judul PKL',
                'pengirim' => $pembimbing->nama_dosen,
                'status' => 'acc',
                'keterangan' => 'Judul PKL disetujui: ' . $pengajuan->judul_pkl,
            );
        }
        if ($pengajuan && isset($pengajuan->status_acc_judul) && $pengajuan->status_acc_judul === 'revisi' && $pembimbing) {
            $ket_rev = 'Judul PKL memerlukan revisi';
            if (!empty($pengajuan->catatan_revisi)) {
                $ket_rev .= ': ' . $pengajuan->catatan_revisi;
            }
            $histori_pengajuan[] = array(
                'tanggal' => $pengajuan->updated_at ?: $pengajuan->created_at,
                'topik' => 'Revisi Judul PKL',
                'pengirim' => $pembimbing->nama_dosen,
                'status' => 'revisi',
                'keterangan' => $ket_rev,
            );
        }

        // Load library PDF dan helper
        $this->load->library('Pdf');
        $this->load->helper('app');
        
        $data = array(
            'mahasiswa' => $mahasiswa,
            'pembimbing' => $pembimbing,
            'pengajuan' => $pengajuan,
            'bimbingan_list' => $bimbingan_list,
            'all_logs' => $all_logs,
            'histori_pengajuan' => $histori_pengajuan,
            'tahun_aktif' => $tahun_aktif,
            'statistik' => (object)array(
                'total_topik' => $total_topik,
                'total_log' => $total_log,
                'total_acc' => $total_acc,
                'total_revisi' => $total_revisi
            )
        );
        
        // Generate HTML untuk PDF
        $html = $this->load->view('mahasiswa/pkl/cetak_histori_bimbingan', $data, true);
        
        // Load HTML ke PDF library
        $this->pdf->load_html($html);
        $this->pdf->set_paper('A4', 'portrait');
        
        // Generate filename
        $filename = 'Histori_Bimbingan_PKL_' . $mahasiswa->nim . '_' . date('Ymd') . '.pdf';
        
        // Stream PDF
        $this->pdf->stream($filename);
    }
}

