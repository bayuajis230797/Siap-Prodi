<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Skripsi_verifikasi_berkas_sidang Controller
 * Mengelola verifikasi berkas pendaftaran sidang Skripsi (untuk Sekprodi)
 */
class Skripsi_verifikasi_berkas_sidang extends MY_Controller {

    public function __construct() {
        parent::__construct();
        $this->check_role('sekprodi');
        $this->load->model('Skripsi_pendaftaran_sidang_model');
        $this->load->model('Skripsi_berkas_upload_model');
        $this->load->model('Jadwal_skripsi_model');
        $this->load->model('Dosen_model');
        $this->load->model('Tahun_akademik_model');
        $this->load->library('pagination');
        $this->load->library('form_validation');
        $this->load->helper('file');
    }

    public function index() {
        $tahun_aktif = $this->Tahun_akademik_model->get_aktif();
        $status_filter = $this->input->get('status');
        $kelas_filter = $this->input->get('kelas');
        $tahap_filter = $this->input->get('tahap');

        $filters = array();
        if ($tahun_aktif) {
            $filters['id_tahun_akademik'] = $tahun_aktif->id;
        }
        if (!empty($status_filter)) {
            $filters['status'] = $status_filter;
        }
        if (!empty($kelas_filter)) {
            $filters['kelas'] = $kelas_filter;
        }
        if (!empty($tahap_filter) && $this->db->field_exists('tahap', 'skripsi_pendaftaran_sidang')) {
            $filters['tahap'] = $tahap_filter;
        }

        $per_page = (int) $this->input->get('per_page');
        $per_page = in_array($per_page, array(10, 25, 50, 100), true) ? $per_page : 10;

        $config['base_url'] = site_url('Skripsi_verifikasi_berkas_sidang/index');
        $config['total_rows'] = $this->Skripsi_pendaftaran_sidang_model->count_all($filters);
        $config['per_page'] = $per_page;
        $config['uri_segment'] = 3;
        $config['reuse_query_string'] = TRUE;
        $config['full_tag_open'] = '<ul class="pagination pagination-sm m-0 float-right">';
        $config['full_tag_close'] = '</ul>';
        $config['attributes'] = array('class' => 'page-link');
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

        $this->pagination->initialize($config);

        $page = ($this->uri->segment(3)) ? $this->uri->segment(3) : 1;
        $offset = ($page - 1) * $config['per_page'];

        $pendaftaran_list = $this->Skripsi_pendaftaran_sidang_model->get_all($filters, $config['per_page'], $offset);
        
        // Ambil judul skripsi terbaru untuk setiap pendaftaran
        $this->load->model('Skripsi_pengajuan_model');
        $this->load->model('Skripsi_perubahan_judul_model');
        foreach ($pendaftaran_list as &$pendaftaran) {
            if (isset($pendaftaran->id_mahasiswa) && $pendaftaran->id_mahasiswa) {
                // Ambil pengajuan terbaru
                $pengajuan = $this->Skripsi_pengajuan_model->get_latest_by_mahasiswa($pendaftaran->id_mahasiswa, $tahun_aktif ? $tahun_aktif->id : null);
                
                if ($pengajuan) {
                    // Cek apakah ada perubahan judul yang sudah di-approve sekprodi
                    $perubahan_approved = $this->Skripsi_perubahan_judul_model->get_by_pengajuan($pengajuan->id);
                    if ($perubahan_approved && isset($perubahan_approved->status_approve_sekprodi) && $perubahan_approved->status_approve_sekprodi === 'acc') {
                        // Gunakan judul baru dari perubahan judul yang sudah di-approve
                        $pendaftaran->judul_skripsi = $perubahan_approved->judul_baru;
                    } elseif (isset($pengajuan->judul_disetujui) && !empty($pengajuan->judul_disetujui)) {
                        // Gunakan judul_disetujui dari pengajuan
                        $pendaftaran->judul_skripsi = $pengajuan->judul_disetujui;
                    } elseif (isset($pengajuan->judul_pengajuan) && !empty($pengajuan->judul_pengajuan)) {
                        // Fallback ke judul_pengajuan
                        $pendaftaran->judul_skripsi = $pengajuan->judul_pengajuan;
                    }
                }
            }
        }
        unset($pendaftaran); // Hapus reference terakhir

        // Ambil kelas list untuk filter
        $this->load->model('Mahasiswa_model');
        $this->db->distinct();
        $this->db->select('kelas');
        $this->db->from('mahasiswa');
        $this->db->where('kelas IS NOT NULL');
        $this->db->where('kelas !=', '');
        $this->db->order_by('kelas', 'ASC');
        $kelas_list = $this->db->get()->result();

        $data = array(
            'title' => 'Verifikasi Berkas Sidang Skripsi',
            'page_title' => 'Verifikasi Berkas Sidang Skripsi',
            'breadcrumb' => array(
                array('label' => 'Skripsi', 'url' => '#'),
                array('label' => 'Verifikasi Berkas Sidang', 'active' => true)
            ),
            'sidebar_menu' => generate_sidebar_menu('sekprodi'),
            'pendaftaran_list' => $pendaftaran_list,
            'pagination' => $this->pagination->create_links(),
            'total_rows' => $config['total_rows'],
            'status_filter' => $status_filter,
            'kelas_filter' => $kelas_filter,
            'tahap_filter' => $tahap_filter,
            'kelas_list' => $kelas_list,
            'tahun_aktif' => $tahun_aktif,
            'per_page' => $per_page,
        );

        $data['content'] = $this->load->view('sekprodi/skripsi_verifikasi_berkas_sidang/index', $data, true);
        $this->load->view('layouts/main', $data);
    }

    public function detail($id) {
        $pendaftaran = $this->Skripsi_pendaftaran_sidang_model->get_by_id($id);
        if (!$pendaftaran) {
            $this->session->set_flashdata('error', 'Data pendaftaran tidak ditemukan.');
            redirect('Skripsi_verifikasi_berkas_sidang');
        }

        // Ambil berkas yang diupload
        $berkas_uploaded = $this->Skripsi_berkas_upload_model->get_by_pendaftaran($id);

        // Ambil data skripsi untuk mendapatkan pembimbing
        $this->load->model('Skripsi_model');
        $this->load->model('Skripsi_pengajuan_model');
        $this->load->model('Skripsi_perubahan_judul_model');
        $skripsi = $this->Skripsi_model->get_by_id($pendaftaran->id_mahasiswa_skripsi);
        
        // Ambil judul skripsi terbaru
        $judul_skripsi_terbaru = null;
        if ($skripsi) {
            // Ambil pengajuan terbaru
            $tahun_aktif = $this->Tahun_akademik_model->get_aktif();
            $pengajuan = $this->Skripsi_pengajuan_model->get_latest_by_mahasiswa($skripsi->id_mahasiswa, $tahun_aktif ? $tahun_aktif->id : null);
            
            if ($pengajuan) {
                // Cek apakah ada perubahan judul yang sudah di-approve sekprodi
                $perubahan_approved = $this->Skripsi_perubahan_judul_model->get_by_pengajuan($pengajuan->id);
                if ($perubahan_approved && isset($perubahan_approved->status_approve_sekprodi) && $perubahan_approved->status_approve_sekprodi === 'acc') {
                    // Gunakan judul baru dari perubahan judul yang sudah di-approve
                    $judul_skripsi_terbaru = $perubahan_approved->judul_baru;
                } elseif (isset($pengajuan->judul_disetujui) && !empty($pengajuan->judul_disetujui)) {
                    // Gunakan judul_disetujui dari pengajuan
                    $judul_skripsi_terbaru = $pengajuan->judul_disetujui;
                } elseif (isset($pengajuan->judul_pengajuan) && !empty($pengajuan->judul_pengajuan)) {
                    // Fallback ke judul_pengajuan
                    $judul_skripsi_terbaru = $pengajuan->judul_pengajuan;
                }
            }
            
            // Jika masih belum ada, gunakan dari mahasiswa_skripsi
            if (empty($judul_skripsi_terbaru) && isset($skripsi->judul_skripsi) && !empty($skripsi->judul_skripsi)) {
                $judul_skripsi_terbaru = $skripsi->judul_skripsi;
            }
        }

        // Ambil jadwal jika sudah ada - gunakan get_by_id jika ada jadwal dari pendaftaran
        $jadwal = null;
        if (isset($pendaftaran->id_jadwal_skripsi) && $pendaftaran->id_jadwal_skripsi) {
            $jadwal = $this->Jadwal_skripsi_model->get_by_id($pendaftaran->id_jadwal_skripsi);
        } else {
            // Jika tidak ada id_jadwal_skripsi di pendaftaran, cari dari mahasiswa_skripsi
            $jadwal_list = $this->Jadwal_skripsi_model->get_by_mahasiswa($pendaftaran->id_mahasiswa_skripsi);
            $jadwal = !empty($jadwal_list) ? $jadwal_list[0] : null;
        }

        // Ambil daftar dosen untuk penguji
        $dosen_list = $this->Dosen_model->get_all_active();
        
        // Rekomendasi penguji: Pembimbing 2 otomatis jadi anggota penguji 2
        $rekomendasi_penguji_2 = null;
        if ($skripsi && isset($skripsi->id_pembimbing_2) && $skripsi->id_pembimbing_2) {
            $rekomendasi_penguji_2 = $skripsi->id_pembimbing_2;
        }

        // Deteksi tahap pendaftaran (untuk form approve yang berbeda)
        $pendaftaran_tahap = isset($pendaftaran->tahap) ? $pendaftaran->tahap : 'verifikasi';
        $is_tahap_jadwal = ($pendaftaran_tahap === 'jadwal');

        // Cek apakah bisa edit jadwal (nonaktifkan jika status selesai dan semua penguji sudah input nilai)
        $can_edit_jadwal = true;
        if ($jadwal && isset($jadwal->status) && $jadwal->status === 'selesai') {
            $this->load->model('Skripsi_nilai_sidang_model');
            $nilai_sidang_list = $this->Skripsi_nilai_sidang_model->get_by_jadwal($jadwal->id);
            
            // Cek apakah semua penguji (ketua, anggota 1, anggota 2) sudah memberikan nilai
            $penguji_yang_sudah_nilai = array();
            foreach ($nilai_sidang_list as $nilai) {
                if (isset($nilai->peran) && isset($nilai->nilai_total) && $nilai->nilai_total > 0) {
                    $penguji_yang_sudah_nilai[] = $nilai->peran;
                }
            }
            
            // Jika semua 3 penguji sudah memberikan nilai, nonaktifkan edit
            if (in_array('ketua', $penguji_yang_sudah_nilai) && 
                in_array('anggota_1', $penguji_yang_sudah_nilai) && 
                in_array('anggota_2', $penguji_yang_sudah_nilai)) {
                $can_edit_jadwal = false;
            }
        }

        $data = array(
            'title' => 'Detail Verifikasi Berkas Sidang Skripsi',
            'page_title' => 'Detail Verifikasi Berkas Sidang Skripsi',
            'breadcrumb' => array(
                array('label' => 'Skripsi', 'url' => '#'),
                array('label' => 'Verifikasi Berkas Sidang', 'url' => site_url('Skripsi_verifikasi_berkas_sidang')),
                array('label' => 'Detail', 'active' => true)
            ),
            'sidebar_menu' => generate_sidebar_menu('sekprodi'),
            'pendaftaran' => $pendaftaran,
            'berkas_uploaded' => $berkas_uploaded,
            'skripsi' => $skripsi,
            'judul_skripsi_terbaru' => $judul_skripsi_terbaru,
            'jadwal' => $jadwal,
            'dosen_list' => $dosen_list,
            'rekomendasi_penguji_2' => $rekomendasi_penguji_2,
            'can_edit_jadwal' => $can_edit_jadwal,
            'pendaftaran_tahap' => $pendaftaran_tahap,
            'is_tahap_jadwal' => $is_tahap_jadwal
        );

        $data['content'] = $this->load->view('sekprodi/skripsi_verifikasi_berkas_sidang/detail', $data, true);
        $this->load->view('layouts/main', $data);
    }

    public function approve($id) {
        $pendaftaran = $this->Skripsi_pendaftaran_sidang_model->get_by_id($id);
        if (!$pendaftaran) {
            $this->session->set_flashdata('error', 'Data pendaftaran tidak ditemukan.');
            redirect('Skripsi_verifikasi_berkas_sidang');
        }

        $pendaftaran_tahap = isset($pendaftaran->tahap) ? $pendaftaran->tahap : 'verifikasi';
        $is_tahap_jadwal = ($pendaftaran_tahap === 'jadwal');

        if ($this->input->method() === 'post') {
            $this->load->model('Skripsi_model');
            $skripsi = $this->Skripsi_model->get_by_id($pendaftaran->id_mahasiswa_skripsi);
            $tahun_aktif = $this->Tahun_akademik_model->get_aktif();
            $jadwal_list = $this->Jadwal_skripsi_model->get_by_mahasiswa($pendaftaran->id_mahasiswa_skripsi);
            $jadwal_existing = !empty($jadwal_list) ? $jadwal_list[0] : null;

            if ($is_tahap_jadwal) {
                $this->form_validation->set_rules('tanggal_ujian', 'Tanggal Ujian', 'required');
                $this->form_validation->set_rules('jam_mulai', 'Jam Mulai', 'required');
                $this->form_validation->set_rules('jam_selesai', 'Jam Selesai', 'required');
                $this->form_validation->set_rules('ruangan', 'Ruangan', 'required|trim');
                $tanggal_ujian = $this->input->post('tanggal_ujian');
                $jam_mulai = $this->input->post('jam_mulai');
                $jam_selesai = $this->input->post('jam_selesai');
                $ruangan = trim($this->input->post('ruangan'));
                $penguji_ids = null;
            } else {
                $this->form_validation->set_rules('id_ketua_penguji', 'Ketua Penguji', 'required|integer');
                $this->form_validation->set_rules('id_anggota_penguji_1', 'Anggota Penguji 1', 'required|integer');
                $this->form_validation->set_rules('id_anggota_penguji_2', 'Anggota Penguji 2', 'required|integer');
                if (!$jadwal_existing) {
                    $this->session->set_flashdata('error', 'Jadwal sidang belum ditetapkan. Silakan approve pendaftaran tahap jadwal terlebih dahulu.');
                    redirect('Skripsi_verifikasi_berkas_sidang/detail/' . $id);
                }
                $tanggal_ujian = $jadwal_existing->tanggal_ujian ?? (isset($jadwal_existing->tanggal) ? $jadwal_existing->tanggal : null);
                $jam_mulai = $jadwal_existing->jam_mulai ?? '';
                $jam_selesai = $jadwal_existing->jam_selesai ?? '';
                $ruangan = $jadwal_existing->ruangan ?? '';
                $id_ketua = (int) $this->input->post('id_ketua_penguji');
                $id_anggota_1 = (int) $this->input->post('id_anggota_penguji_1');
                $id_anggota_2 = (int) $this->input->post('id_anggota_penguji_2');
                $penguji_ids = array($id_ketua, $id_anggota_1, $id_anggota_2);
                if ($skripsi && $skripsi->id_pembimbing_1) {
                    if ($id_ketua == $skripsi->id_pembimbing_1 || $id_anggota_1 == $skripsi->id_pembimbing_1 || $id_anggota_2 == $skripsi->id_pembimbing_1) {
                        $this->session->set_flashdata('error', 'Pembimbing 1 tidak boleh menjadi penguji.');
                        redirect('Skripsi_verifikasi_berkas_sidang/detail/' . $id);
                    }
                }
                if ($id_ketua == $id_anggota_1 || $id_ketua == $id_anggota_2 || $id_anggota_1 == $id_anggota_2) {
                    $this->session->set_flashdata('error', 'Penguji tidak boleh sama.');
                    redirect('Skripsi_verifikasi_berkas_sidang/detail/' . $id);
                }
            }

            if ($this->form_validation->run() === FALSE) {
                $this->session->set_flashdata('error', validation_errors());
                redirect('Skripsi_verifikasi_berkas_sidang/detail/' . $id);
            }

            $bentrok = $this->Jadwal_skripsi_model->check_bentrok(
                $tanggal_ujian,
                $jam_mulai,
                $jam_selesai,
                $ruangan,
                $tahun_aktif->id,
                null,
                $penguji_ids
            );

            if ($is_tahap_jadwal && $bentrok['bentrok_ruang']) {
                $this->session->set_flashdata('error', "Ruangan '{$ruangan}' pada tanggal " . date('d/m/Y', strtotime($tanggal_ujian)) . " jam " . date('H:i', strtotime($jam_mulai)) . "-" . date('H:i', strtotime($jam_selesai)) . " sudah digunakan.");
                redirect('Skripsi_verifikasi_berkas_sidang/detail/' . $id);
            }
            if (!$is_tahap_jadwal && $bentrok['bentrok_penguji'] && !empty($bentrok['detail_penguji'])) {
                $this->load->model('Dosen_model');
                $error_msg = "Penguji bentrok pada tanggal " . date('d/m/Y', strtotime($tanggal_ujian)) . ": ";
                foreach ($bentrok['detail_penguji'] as $detail) {
                    $dosen = $this->Dosen_model->get_by_id($detail['id_dosen']);
                    if ($dosen) $error_msg .= $dosen->nama_dosen . ", ";
                }
                $this->session->set_flashdata('error', rtrim($error_msg, ', ') . " sudah memiliki jadwal.");
                redirect('Skripsi_verifikasi_berkas_sidang/detail/' . $id);
            }

            $this->Skripsi_pendaftaran_sidang_model->update($id, array(
                'status' => 'disetujui',
                'catatan_sekprodi' => $this->input->post('catatan_sekprodi')
            ));

            if ($is_tahap_jadwal) {
                $jadwal_data = array(
                    'id_mahasiswa_skripsi' => $pendaftaran->id_mahasiswa_skripsi,
                    'id_tahun_akademik' => $tahun_aktif->id,
                    'tanggal_ujian' => $tanggal_ujian,
                    'jam_mulai' => $jam_mulai,
                    'jam_selesai' => $jam_selesai,
                    'ruangan' => $ruangan,
                    'status' => 'terjadwal',
                    'catatan' => $this->input->post('catatan'),
                    'id_ketua_penguji' => null,
                    'id_anggota_penguji_1' => null,
                    'id_anggota_penguji_2' => null
                );
                if ($jadwal_existing) {
                    $this->Jadwal_skripsi_model->update($jadwal_existing->id, $jadwal_data);
                } else {
                    $this->Jadwal_skripsi_model->create($jadwal_data);
                }
                $this->session->set_flashdata('success', 'Berkas tahap jadwal disetujui. Jadwal sidang (tanggal, jam, ruangan) berhasil ditetapkan.');
            } else {
                $this->Jadwal_skripsi_model->update($jadwal_existing->id, array(
                    'id_ketua_penguji' => $id_ketua,
                    'id_anggota_penguji_1' => $id_anggota_1,
                    'id_anggota_penguji_2' => $id_anggota_2,
                    'catatan' => $this->input->post('catatan')
                ));
                $this->session->set_flashdata('success', 'Berkas sidang disetujui dan tim penguji berhasil ditetapkan.');
            }
            redirect('Skripsi_verifikasi_berkas_sidang/detail/' . $id);
        }

        redirect('Skripsi_verifikasi_berkas_sidang/detail/' . $id);
    }

    public function reject($id) {
        $pendaftaran = $this->Skripsi_pendaftaran_sidang_model->get_by_id($id);
        if (!$pendaftaran) {
            $this->session->set_flashdata('error', 'Data pendaftaran tidak ditemukan.');
            redirect('Skripsi_verifikasi_berkas_sidang');
        }

        if ($this->input->method() === 'post') {
            $this->form_validation->set_rules('catatan_sekprodi', 'Catatan', 'required|trim');

            if ($this->form_validation->run() === FALSE) {
                $this->session->set_flashdata('error', validation_errors());
                redirect('Skripsi_verifikasi_berkas_sidang/detail/' . $id);
            }

            $this->Skripsi_pendaftaran_sidang_model->update($id, array(
                'status' => 'ditolak',
                'catatan_sekprodi' => $this->input->post('catatan_sekprodi')
            ));

            $this->session->set_flashdata('success', 'Pendaftaran sidang ditolak.');
            redirect('Skripsi_verifikasi_berkas_sidang');
        }

        redirect('Skripsi_verifikasi_berkas_sidang/detail/' . $id);
    }

    public function download_berkas($id_upload) {
        $upload = $this->Skripsi_berkas_upload_model->get_by_id($id_upload);
        if (!$upload) {
            show_404();
        }

        $file_path = $upload->file_path;
        
        // Cek apakah ini link Google Drive
        if (strpos($file_path, 'http') === 0) {
            // Ini adalah link Google Drive, redirect ke link tersebut
            $this->load->helper('google_drive');
            $normalized_link = normalize_google_drive_link($file_path);
            redirect($normalized_link);
        }
        
        // Jika bukan link, coba download file
        $file_path = ltrim($file_path, './');
        $file_path = str_replace('\\', '/', $file_path);
        
        $full_path = FCPATH . $file_path;
        
        if (!file_exists($full_path)) {
            $this->session->set_flashdata('error', 'File tidak ditemukan.');
            redirect('Skripsi_verifikasi_berkas_sidang');
        }

        $this->load->helper('download');
        $file_name = $upload->file_name ?: basename($file_path);
        force_download($file_name, file_get_contents($full_path));
    }

    /**
     * Update jadwal sidang dan penguji
     */
    public function update_jadwal($jadwal_id) {
        $jadwal = $this->Jadwal_skripsi_model->get_by_id($jadwal_id);
        if (!$jadwal) {
            $this->session->set_flashdata('error', 'Data jadwal tidak ditemukan.');
            redirect('Skripsi_verifikasi_berkas_sidang');
        }

        $pendaftaran_id = $this->input->post('pendaftaran_id') ?: $this->input->get('pendaftaran_id');
        $pendaftaran = $pendaftaran_id ? $this->Skripsi_pendaftaran_sidang_model->get_by_id($pendaftaran_id) : null;
        if (!$pendaftaran) {
            $pendaftaran = $this->Skripsi_pendaftaran_sidang_model->get_by_mahasiswa_skripsi($jadwal->id_mahasiswa_skripsi);
        }
        $edit_tahap = $this->input->post('edit_tahap') ?: (isset($pendaftaran->tahap) ? $pendaftaran->tahap : 'verifikasi');
        $is_tahap_jadwal = ($edit_tahap === 'jadwal');

        if ($this->input->method() === 'post') {
            if ($is_tahap_jadwal) {
                $this->form_validation->set_rules('tanggal_ujian', 'Tanggal Ujian', 'required');
                $this->form_validation->set_rules('jam_mulai', 'Jam Mulai', 'required');
                $this->form_validation->set_rules('jam_selesai', 'Jam Selesai', 'required');
                $this->form_validation->set_rules('ruangan', 'Ruangan', 'required|trim');
            } else {
                $this->form_validation->set_rules('id_ketua_penguji', 'Ketua Penguji', 'required|integer');
                $this->form_validation->set_rules('id_anggota_penguji_1', 'Anggota Penguji 1', 'required|integer');
                $this->form_validation->set_rules('id_anggota_penguji_2', 'Anggota Penguji 2', 'required|integer');
            }

            if ($this->form_validation->run() === FALSE) {
                $this->session->set_flashdata('error', validation_errors());
                if ($pendaftaran) {
                    redirect('Skripsi_verifikasi_berkas_sidang/detail/' . $pendaftaran->id);
                } else {
                    redirect('Skripsi_verifikasi_berkas_sidang');
                }
            }

            if ($is_tahap_jadwal) {
                $jadwal_data = array(
                    'tanggal_ujian' => $this->input->post('tanggal_ujian'),
                    'jam_mulai' => $this->input->post('jam_mulai'),
                    'jam_selesai' => $this->input->post('jam_selesai'),
                    'ruangan' => trim($this->input->post('ruangan')),
                    'catatan' => $this->input->post('catatan')
                );
                $this->Jadwal_skripsi_model->update($jadwal_id, $jadwal_data);
                $this->session->set_flashdata('success', 'Jadwal sidang (ruangan, tanggal, jam) berhasil diupdate.');
            } else {
                $this->load->model('Skripsi_model');
                $skripsi = $this->Skripsi_model->get_by_id($jadwal->id_mahasiswa_skripsi);
                $id_ketua = (int) $this->input->post('id_ketua_penguji');
                $id_anggota_1 = (int) $this->input->post('id_anggota_penguji_1');
                $id_anggota_2 = (int) $this->input->post('id_anggota_penguji_2');
                if ($skripsi && $skripsi->id_pembimbing_1) {
                    if ($id_ketua == $skripsi->id_pembimbing_1 || $id_anggota_1 == $skripsi->id_pembimbing_1 || $id_anggota_2 == $skripsi->id_pembimbing_1) {
                        $this->session->set_flashdata('error', 'Pembimbing 1 tidak boleh menjadi penguji.');
                        redirect('Skripsi_verifikasi_berkas_sidang/detail/' . $pendaftaran->id);
                    }
                }
                if ($id_ketua == $id_anggota_1 || $id_ketua == $id_anggota_2 || $id_anggota_1 == $id_anggota_2) {
                    $this->session->set_flashdata('error', 'Penguji tidak boleh sama.');
                    redirect('Skripsi_verifikasi_berkas_sidang/detail/' . $pendaftaran->id);
                }
                $jadwal_data = array(
                    'id_ketua_penguji' => $id_ketua,
                    'id_anggota_penguji_1' => $id_anggota_1,
                    'id_anggota_penguji_2' => $id_anggota_2,
                    'catatan' => $this->input->post('catatan')
                );
                $this->Jadwal_skripsi_model->update($jadwal_id, $jadwal_data);
                $this->session->set_flashdata('success', 'Tim penguji berhasil diupdate.');
            }
            if ($pendaftaran) {
                redirect('Skripsi_verifikasi_berkas_sidang/detail/' . $pendaftaran->id);
            } else {
                redirect('Skripsi_verifikasi_berkas_sidang');
            }
        }

        if ($pendaftaran) {
            redirect('Skripsi_verifikasi_berkas_sidang/detail/' . $pendaftaran->id);
        } else {
            redirect('Skripsi_verifikasi_berkas_sidang');
        }
    }
}


