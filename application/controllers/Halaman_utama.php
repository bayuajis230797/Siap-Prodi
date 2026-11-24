<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Halaman Utama Controller
 * Menampilkan halaman utama untuk semua role
 */
class Halaman_utama extends MY_Controller {

    public function __construct() {
        parent::__construct();
        // Bisa diakses oleh semua role yang sudah login
        if (!$this->session->userdata('user_id')) {
            redirect('Auth/login');
        }
        
        $this->load->helper('menu');
        $this->load->model('Tahun_akademik_model');
        $this->load->model('Mahasiswa_model');
    }

    /**
     * Tampilkan halaman utama sesuai role
     */
    public function index() {
        try {
            $role = $this->session->userdata('role');
            
            $data['title'] = 'Halaman Utama';
            $data['page_title'] = 'Halaman Utama';
            $data['breadcrumb'] = array(
                array('label' => 'Halaman Utama', 'active' => true)
            );
            $data['sidebar_menu'] = generate_sidebar_menu($role);
            $data['tahun_aktif'] = $this->Tahun_akademik_model->get_aktif();
            
            // Load data sesuai role
            switch ($role) {
                case 'admin':
                    $this->load_admin_halaman_utama($data);
                    break;
                case 'sekprodi':
                    $this->load_sekprodi_halaman_utama($data);
                    break;
                case 'kaprodi':
                    $this->load_sekprodi_halaman_utama($data);
                    break;
                case 'dosen':
                    $this->load_dosen_halaman_utama($data);
                    break;
                case 'mahasiswa':
                    $this->load_mahasiswa_halaman_utama($data);
                    break;
                case 'tendik':
                    $this->load_tendik_halaman_utama($data);
                    break;
                default:
                    $this->load_default_halaman_utama($data);
            }
            
            $this->load->view('layouts/main', $data);
        } catch (Exception $e) {
            log_message('error', 'Error loading halaman utama: ' . $e->getMessage());
            $this->session->set_flashdata('error', 'Terjadi kesalahan saat memuat halaman utama.');
            redirect('Dashboard');
        }
    }
    
    /**
     * Load halaman utama untuk admin
     */
    private function load_admin_halaman_utama(&$data) {
        // Mahasiswa aktif = semester riil 1-8; semester riil > 8 dihitung terpisah sebagai alumni
        $ringkasan = $this->Mahasiswa_model->get_ringkasan_aktif();
        $data['total_mahasiswa_aktif'] = $ringkasan['aktif'];
        $data['total_alumni'] = $ringkasan['alumni'];
        
        // Total dosen aktif
        $this->db->where('status', 'aktif');
        $data['total_dosen_aktif'] = $this->db->count_all_results('dosen');
        
        // Total tendik aktif
        $this->db->where('status', 'aktif');
        $data['total_tendik_aktif'] = $this->db->count_all_results('tendik');
        
        // Statistik mahasiswa per kelas (kelas 8 dipisah alumni / bukan)
        $data['statistik_kelas'] = $this->Mahasiswa_model->get_statistik_kelas_aktif();

        // Visualisasi tambahan
        $data['chart_login_7hari'] = $this->get_login_chart_7hari();
        $data['total_percakapan'] = $this->db->table_exists('chat_conversations') ? (int) $this->db->count_all('chat_conversations') : 0;
        $data['total_pesan_chat'] = $this->db->table_exists('chat_messages') ? (int) $this->db->count_all('chat_messages') : 0;
        $data['login_hari_ini'] = 0;
        if ($this->db->table_exists('user_activity_log')) {
            $this->db->where('activity_type', 'login');
            $this->db->where('DATE(created_at) = ' . $this->db->escape(date('Y-m-d')), null, false);
            $data['login_hari_ini'] = (int) $this->db->count_all_results('user_activity_log');
        }
        
        // Pastikan variabel selalu ada
        if (!isset($data['total_mahasiswa_aktif'])) $data['total_mahasiswa_aktif'] = 0;
        if (!isset($data['total_dosen_aktif'])) $data['total_dosen_aktif'] = 0;
        if (!isset($data['total_tendik_aktif'])) $data['total_tendik_aktif'] = 0;
        if (!isset($data['statistik_kelas'])) $data['statistik_kelas'] = array();
        
        $data['content'] = $this->load->view('halaman_utama/index', $data, true);
    }

    private function get_login_chart_7hari() {
        $labels = array();
        $values = array();
        $map = array();
        for ($i = 6; $i >= 0; $i--) {
            $d = date('Y-m-d', strtotime('-' . $i . ' day'));
            $labels[] = date('d/m', strtotime($d));
            $map[$d] = 0;
        }
        if ($this->db->table_exists('user_activity_log')) {
            $this->load->model('User_activity_log_model');
            foreach ($this->User_activity_log_model->get_login_count_last_days(7) as $row) {
                if (isset($map[$row->tgl])) {
                    $map[$row->tgl] = (int) $row->jumlah;
                }
            }
        }
        foreach ($map as $v) {
            $values[] = $v;
        }
        return array('labels' => $labels, 'values' => $values);
    }
    
    /**
     * Load halaman utama untuk sekprodi
     */
    private function load_sekprodi_halaman_utama(&$data) {
        // Mahasiswa aktif = semester riil 1-8; semester riil > 8 dihitung terpisah sebagai alumni
        $ringkasan = $this->Mahasiswa_model->get_ringkasan_aktif();
        $data['total_mahasiswa_aktif'] = $ringkasan['aktif'];
        $data['total_alumni'] = $ringkasan['alumni'];
        
        // Statistik mahasiswa per kelas (kelas 8 dipisah alumni / bukan)
        $data['statistik_kelas'] = $this->Mahasiswa_model->get_statistik_kelas_aktif();

        $tahun = isset($data['tahun_aktif']) ? $data['tahun_aktif'] : null;
        $id_ta = $tahun ? (int) $tahun->id : 0;
        $data['total_skripsi_ta'] = 0;
        $data['total_pkl_ta'] = 0;
        $data['total_proposal_ta'] = 0;
        if ($id_ta > 0) {
            if ($this->db->table_exists('mahasiswa_skripsi')) {
                $this->db->where('id_tahun_akademik', $id_ta);
                $data['total_skripsi_ta'] = (int) $this->db->count_all_results('mahasiswa_skripsi');
            }
            if ($this->db->table_exists('pkl_pengajuan')) {
                $this->db->where('id_tahun_akademik', $id_ta);
                $data['total_pkl_ta'] = (int) $this->db->count_all_results('pkl_pengajuan');
            }
            $proposal_table = $this->db->table_exists('proposal_bimbingan') ? 'proposal_bimbingan' : ($this->db->table_exists('pta_bimbingan') ? 'pta_bimbingan' : null);
            if ($proposal_table) {
                $this->db->where('id_tahun_akademik', $id_ta);
                $data['total_proposal_ta'] = (int) $this->db->count_all_results($proposal_table);
            }
        }
        
        // Pastikan variabel selalu ada
        if (!isset($data['total_mahasiswa_aktif'])) $data['total_mahasiswa_aktif'] = 0;
        if (!isset($data['statistik_kelas'])) $data['statistik_kelas'] = array();
        
        $data['content'] = $this->load->view('halaman_utama/index', $data, true);
    }
    
    /**
     * Load halaman utama untuk dosen
     */
    private function load_dosen_halaman_utama(&$data) {
        $id_dosen = $this->session->userdata('ref_id');
        $tahun_aktif = $data['tahun_aktif'];
        $id_tahun_akademik = $tahun_aktif ? $tahun_aktif->id : null;

        // Bimbingan Skripsi (ikut tahun akademik dipilih)
        $this->db->where('(id_pembimbing_1 = ' . $this->db->escape($id_dosen) . ' OR id_pembimbing_2 = ' . $this->db->escape($id_dosen) . ')', NULL, FALSE);
        if ($id_tahun_akademik) {
            $this->db->where('id_tahun_akademik', $id_tahun_akademik);
        }
        $data['total_bimbingan_skripsi'] = $this->db->count_all_results('mahasiswa_skripsi');

        // Bimbingan Proposal TA
        $proposal_table = $this->db->table_exists('proposal_bimbingan') ? 'proposal_bimbingan' : 'pta_bimbingan';
        if ($this->db->table_exists($proposal_table)) {
            $this->db->where('id_pembimbing', $id_dosen);
            if ($id_tahun_akademik) {
                $this->db->where('id_tahun_akademik', $id_tahun_akademik);
            }
            $data['total_bimbingan_proposal'] = $this->db->count_all_results($proposal_table);
        } else {
            $data['total_bimbingan_proposal'] = 0;
        }

        // Bimbingan PKL
        $this->db->where('id_pembimbing', $id_dosen);
        if ($id_tahun_akademik) {
            $this->db->where('id_tahun_akademik', $id_tahun_akademik);
        }
        $data['total_bimbingan_pkl'] = $this->db->count_all_results('pkl_pengajuan');

        // Bimbingan PKM (via periode → tahun akademik)
        $this->db->from('pkm_bimbingan pb');
        $this->db->join('pkm_pengajuan pp', 'pp.id = pb.id_pkm_pengajuan', 'left');
        $this->db->join('pkm_periode pe', 'pe.id = pp.id_pkm_periode', 'left');
        $this->db->where('pb.id_pembimbing', $id_dosen);
        if ($id_tahun_akademik) {
            $this->db->where('pe.id_tahun_akademik', $id_tahun_akademik);
        }
        $data['total_bimbingan_pkm'] = $this->db->count_all_results();

        // Penguji PKL
        $this->db->where('id_penguji', $id_dosen);
        if ($id_tahun_akademik) {
            $this->db->where('id_tahun_akademik', $id_tahun_akademik);
        }
        $data['total_penguji_pkl'] = $this->db->count_all_results('jadwal_pkl');

        // Penguji Skripsi
        $this->db->where('(id_ketua_penguji = ' . $this->db->escape($id_dosen) . ' OR id_anggota_penguji_1 = ' . $this->db->escape($id_dosen) . ' OR id_anggota_penguji_2 = ' . $this->db->escape($id_dosen) . ')', NULL, FALSE);
        if ($id_tahun_akademik) {
            $this->db->where('id_tahun_akademik', $id_tahun_akademik);
        }
        $data['total_penguji_skripsi'] = $this->db->count_all_results('jadwal_skripsi');

        // Pengawasan UTS
        $this->db->select('COUNT(DISTINCT jup.id_jadwal_uts) as total');
        $this->db->from('jadwal_uts_pengawas jup');
        $this->db->join('jadwal_uts ju', 'ju.id = jup.id_jadwal_uts', 'left');
        $this->db->where('jup.id_dosen', $id_dosen);
        if ($id_tahun_akademik) {
            $this->db->where('ju.id_tahun_akademik', $id_tahun_akademik);
        }
        $result_uts = $this->db->get()->row();
        $data['total_pengawasan_uts'] = $result_uts ? $result_uts->total : 0;

        // Pengawasan UAS
        $this->db->select('COUNT(DISTINCT juap.id_jadwal_uas) as total');
        $this->db->from('jadwal_uas_pengawas juap');
        $this->db->join('jadwal_uas jua', 'jua.id = juap.id_jadwal_uas', 'left');
        $this->db->where('juap.id_dosen', $id_dosen);
        if ($id_tahun_akademik) {
            $this->db->where('jua.id_tahun_akademik', $id_tahun_akademik);
        }
        $result_uas = $this->db->get()->row();
        $data['total_pengawasan_uas'] = $result_uas ? $result_uas->total : 0;

        // Total SKS
        $this->db->select('COALESCE(SUM(mk.sks), 0) as total_sks');
        $this->db->from('jadwal_kuliah jk');
        $this->db->join('mapping_dosen_mk mdm', 'mdm.id = jk.id_mapping_dosen_mk', 'left');
        $this->db->join('mata_kuliah_aktif mka', 'mka.id = mdm.id_mata_kuliah_aktif', 'left');
        $this->db->join('kurikulum_detail kd', 'kd.id = mka.id_kurikulum_detail', 'left');
        $this->db->join('mata_kuliah mk', 'mk.id = kd.id_mata_kuliah', 'left');
        $this->db->where('mdm.id_dosen', $id_dosen);
        if ($id_tahun_akademik) {
            $this->db->where('jk.id_tahun_akademik', $id_tahun_akademik);
        }
        $result_sks = $this->db->get()->row();
        $data['total_sks'] = $result_sks ? $result_sks->total_sks : 0;

        $defaults = array(
            'total_bimbingan_skripsi', 'total_bimbingan_proposal', 'total_bimbingan_pkl', 'total_bimbingan_pkm',
            'total_penguji_pkl', 'total_penguji_skripsi', 'total_pengawasan_uts', 'total_pengawasan_uas', 'total_sks'
        );
        foreach ($defaults as $k) {
            if (!isset($data[$k])) {
                $data[$k] = 0;
            }
        }

        $data['content'] = $this->load->view('halaman_utama/index', $data, true);
    }
    
    /**
     * Load halaman utama untuk mahasiswa
     */
    private function load_mahasiswa_halaman_utama(&$data) {
        $id_mahasiswa = $this->session->userdata('ref_id');
        $tahun_aktif = $data['tahun_aktif'];
        
        // Cek status PKL
        $this->db->where('id_mahasiswa', $id_mahasiswa);
        if ($tahun_aktif) {
            $this->db->where('id_tahun_akademik', $tahun_aktif->id);
        }
        $data['is_registered_pkl'] = $this->db->count_all_results('mahasiswa_pkl') > 0;
        
        // Cek status Skripsi
        $this->db->where('id_mahasiswa', $id_mahasiswa);
        if ($tahun_aktif) {
            $this->db->where('id_tahun_akademik', $tahun_aktif->id);
        }
        $data['is_registered_skripsi'] = $this->db->count_all_results('mahasiswa_skripsi') > 0;
        
        // Load model tagihan berkas
        $this->load->model('Tagihan_berkas_model');
        $this->load->model('Mahasiswa_model');
        $this->load->model('Kurikulum_model');
        $this->load->model('Mata_kuliah_aktif_model');
        
        // Ambil data mahasiswa untuk kurikulum
        $mahasiswa = $this->Mahasiswa_model->get_by_id($id_mahasiswa);
        
        // Ambil struktur kurikulum mahasiswa
        $struktur_kurikulum = array();
        $kurikulum_info = null;
        if ($mahasiswa && $mahasiswa->id_kurikulum) {
            $kurikulum_info = $this->Kurikulum_model->get_by_id($mahasiswa->id_kurikulum);
            if ($kurikulum_info) {
                // Ambil semua mata kuliah dalam kurikulum, dikelompokkan per semester
                $all_mk = $this->Kurikulum_model->get_all_mata_kuliah_by_kurikulum($mahasiswa->id_kurikulum);
                
                // Kelompokkan per semester
                $semester_aktif_mhs = $mahasiswa->semester_aktif ? intval($mahasiswa->semester_aktif) : 1;
                
                foreach ($all_mk as $mk) {
                    $semester = intval($mk->semester);
                    if (!isset($struktur_kurikulum[$semester])) {
                        $struktur_kurikulum[$semester] = array();
                    }
                    
                    // Tentukan status mata kuliah
                    $mk->status_mk = 'akan_diambil'; // default
                    if ($semester < $semester_aktif_mhs) {
                        $mk->status_mk = 'sudah_diambil';
                    } elseif ($semester == $semester_aktif_mhs) {
                        $mk->status_mk = 'sedang_diambil';
                    }
                    
                    // Cek apakah mata kuliah aktif untuk tahun akademik saat ini
                    if ($tahun_aktif) {
                        $mk->is_aktif_ta = $this->Mata_kuliah_aktif_model->is_aktif($mk->id, $tahun_aktif->id);
                    } else {
                        $mk->is_aktif_ta = false;
                    }
                    
                    $struktur_kurikulum[$semester][] = $mk;
                }
            }
        }
        
        // Get tagihan aktif untuk mahasiswa
        $tagihan_list = $this->Tagihan_berkas_model->get_by_mahasiswa($id_mahasiswa, 1);
        
        // Hitung statistik tagihan
        $data['total_tagihan'] = count($tagihan_list);
        $data['tagihan_belum'] = 0;
        $data['tagihan_sudah'] = 0;
        $data['tagihan_terlambat'] = 0;
        $data['tagihan_list'] = array();
        $data['kurikulum_info'] = $kurikulum_info;
        $data['struktur_kurikulum'] = $struktur_kurikulum;
        $data['mahasiswa'] = $mahasiswa;
        
        foreach ($tagihan_list as $tagihan) {
            $detail = $this->Tagihan_berkas_model->get_detail_by_mahasiswa_tagihan($tagihan->id, $id_mahasiswa);
            
            $tagihan->detail = $detail;
            $tagihan->is_terlambat = false;
            
            if ($tagihan->deadline) {
                $deadline = strtotime($tagihan->deadline);
                $today = strtotime('today');
                if ($deadline < $today) {
                    $tagihan->is_terlambat = true;
                    $data['tagihan_terlambat']++;
                }
            }
            
            if ($tagihan->is_hardcopy == 1) {
                if ($detail && $detail->status_hardcopy == 'sudah') {
                    $data['tagihan_sudah']++;
                } else {
                    $data['tagihan_belum']++;
                }
            } else {
                if ($detail && $detail->status == 'sudah') {
                    $data['tagihan_sudah']++;
                } else {
                    $data['tagihan_belum']++;
                }
            }
            
            $data['tagihan_list'][] = $tagihan;
        }
        
        // Limit tagihan list untuk ditampilkan (5 terbaru)
        $data['tagihan_list'] = array_slice($data['tagihan_list'], 0, 5);
        
        // Hitung progress PKL jika terdaftar
        $data['pkl_progress'] = $this->calculate_pkl_progress($id_mahasiswa, $tahun_aktif);
        
        // Hitung progress Skripsi jika terdaftar
        $data['skripsi_progress'] = $this->calculate_skripsi_progress($id_mahasiswa, $tahun_aktif);

        // Data PKM periode aktif (ketua atau anggota internal)
        $this->load->model('Pkm_pengajuan_model');
        $this->load->model('Pkm_anggota_model');
        $data['pkm_pengajuan'] = $this->Pkm_pengajuan_model->get_by_mahasiswa_in_active_periode($id_mahasiswa);
        $data['is_registered_pkm'] = !empty($data['pkm_pengajuan']);
        $data['pkm_peran'] = null;
        $data['pkm_anggota_list'] = array();
        if ($data['pkm_pengajuan']) {
            $data['pkm_peran'] = ((int) $data['pkm_pengajuan']->id_ketua === (int) $id_mahasiswa) ? 'ketua' : 'anggota';
            $data['pkm_anggota_list'] = $this->Pkm_anggota_model->get_by_pengajuan($data['pkm_pengajuan']->id);
        }
        
        // Jadwal sidang untuk countdown (mahasiswa skripsi yang punya jadwal)
        $data['jadwal_sidang'] = null;
        $data['jadwal_sidang_timestamp'] = null;
        if ($data['is_registered_skripsi']) {
            $this->load->model('Skripsi_model');
            $this->load->model('Jadwal_skripsi_model');
            $skripsi = $this->Skripsi_model->get_by_mahasiswa($id_mahasiswa, $tahun_aktif ? $tahun_aktif->id : null);
            if ($skripsi) {
                $jadwal_list = $this->Jadwal_skripsi_model->get_by_mahasiswa($skripsi->id);
                $upcoming = null;
                $upcoming_ts = null;
                foreach ($jadwal_list as $j) {
                    $tanggal = isset($j->tanggal_ujian) ? $j->tanggal_ujian : (isset($j->tanggal) ? $j->tanggal : null);
                    if ($tanggal && isset($j->jam_mulai)) {
                        $ts = strtotime($tanggal . ' ' . $j->jam_mulai);
                        if ($ts >= time() && ($upcoming_ts === null || $ts < $upcoming_ts)) {
                            $upcoming = $j;
                            $upcoming_ts = $ts;
                        }
                    }
                }
                if ($upcoming) {
                    $data['jadwal_sidang'] = $upcoming;
                    $data['jadwal_sidang_timestamp'] = $upcoming_ts;
                } elseif (!empty($jadwal_list)) {
                    $j = $jadwal_list[0];
                    $tanggal = isset($j->tanggal_ujian) ? $j->tanggal_ujian : (isset($j->tanggal) ? $j->tanggal : null);
                    if ($tanggal && isset($j->jam_mulai)) {
                        $data['jadwal_sidang'] = $j;
                        $data['jadwal_sidang_timestamp'] = strtotime($tanggal . ' ' . $j->jam_mulai);
                    }
                }
            }
        }
        
        // Kartu Proposal TA (progres + countdown sidang). Gagal tidak boleh merusak halaman utama.
        $data['proposal_card'] = null;
        try {
            if ($tahun_aktif) {
                $this->load->helper('proposal');
                $this->load->model('Proposal_model');
                $this->load->model('Jadwal_proposal_model');
                $proposal_mhs = $this->Proposal_model->get_by_mahasiswa($id_mahasiswa, $tahun_aktif->id);
                if ($proposal_mhs) {
                    $jadwal_terdekat = null;
                    $ts_terdekat = null;
                    foreach ($this->Jadwal_proposal_model->get_by_mahasiswa($proposal_mhs->id) as $jp) {
                        if ($jp->status === 'batal' || !$jp->tanggal_ujian || !$jp->jam_mulai) { continue; }
                        $ts = strtotime($jp->tanggal_ujian . ' ' . $jp->jam_mulai);
                        if ($ts >= time() && ($ts_terdekat === null || $ts < $ts_terdekat)) {
                            $jadwal_terdekat = $jp;
                            $ts_terdekat = $ts;
                        }
                    }
                    $data['proposal_card'] = array(
                        'progress' => calculate_proposal_progress($id_mahasiswa, $tahun_aktif),
                        'judul' => get_judul_proposal_terbaru($id_mahasiswa, $tahun_aktif->id),
                        'jadwal' => $jadwal_terdekat,
                        'jadwal_timestamp' => $ts_terdekat
                    );
                }
            }
        } catch (Throwable $e) {
            log_message('error', 'Kartu Proposal TA gagal: ' . $e->getMessage());
            $data['proposal_card'] = null;
        }
        // Pastikan variabel selalu ada
        if (!isset($data['is_registered_pkl'])) $data['is_registered_pkl'] = false;
        if (!isset($data['is_registered_skripsi'])) $data['is_registered_skripsi'] = false;
        if (!isset($data['is_registered_pkm'])) $data['is_registered_pkm'] = false;
        if (!isset($data['pkm_pengajuan'])) $data['pkm_pengajuan'] = null;
        if (!isset($data['pkm_peran'])) $data['pkm_peran'] = null;
        if (!isset($data['pkm_anggota_list'])) $data['pkm_anggota_list'] = array();
        if (!isset($data['pkl_progress'])) $data['pkl_progress'] = array('percentage' => 0, 'current_step' => 0, 'total_steps' => 10, 'steps' => array());
        if (!isset($data['skripsi_progress'])) $data['skripsi_progress'] = array('percentage' => 0, 'current_step' => 0, 'total_steps' => 10, 'steps' => array());
        if (!isset($data['total_tagihan'])) $data['total_tagihan'] = 0;
        if (!isset($data['tagihan_belum'])) $data['tagihan_belum'] = 0;
        if (!isset($data['tagihan_sudah'])) $data['tagihan_sudah'] = 0;
        if (!isset($data['tagihan_terlambat'])) $data['tagihan_terlambat'] = 0;
        if (!isset($data['tagihan_list'])) $data['tagihan_list'] = array();
        
        $data['content'] = $this->load->view('halaman_utama/index', $data, true);
    }
    
    /**
     * Load halaman utama untuk tendik
     */
    private function load_tendik_halaman_utama(&$data) {
        $id_tendik = $this->session->userdata('ref_id');
        $tahun_aktif = $data['tahun_aktif'];
        $id_tahun_akademik = $tahun_aktif ? $tahun_aktif->id : null;
        
        // Total pengawasan UTS
        $this->db->select('COUNT(DISTINCT jup.id_jadwal_uts) as total');
        $this->db->from('jadwal_uts_pengawas jup');
        $this->db->join('jadwal_uts ju', 'ju.id = jup.id_jadwal_uts', 'left');
        $this->db->where('jup.id_tendik', $id_tendik);
        if ($id_tahun_akademik) {
            $this->db->where('ju.id_tahun_akademik', $id_tahun_akademik);
        }
        $result_uts = $this->db->get()->row();
        $data['total_pengawasan_uts'] = $result_uts ? $result_uts->total : 0;
        
        // Total pengawasan UAS
        $this->db->select('COUNT(DISTINCT juap.id_jadwal_uas) as total');
        $this->db->from('jadwal_uas_pengawas juap');
        $this->db->join('jadwal_uas jua', 'jua.id = juap.id_jadwal_uas', 'left');
        $this->db->where('juap.id_tendik', $id_tendik);
        if ($id_tahun_akademik) {
            $this->db->where('jua.id_tahun_akademik', $id_tahun_akademik);
        }
        $result_uas = $this->db->get()->row();
        $data['total_pengawasan_uas'] = $result_uas ? $result_uas->total : 0;
        
        // Total pengawasan (UTS + UAS)
        $data['total_pengawasan'] = $data['total_pengawasan_uts'] + $data['total_pengawasan_uas'];
        
        // Ambil data user tendik
        $this->db->where('id', $id_tendik);
        $tendik = $this->db->get('tendik')->row();
        $data['user_data'] = array(
            'nama' => $tendik ? $tendik->nama_tendik : 'Tendik'
        );
        
        // Pastikan variabel selalu ada
        if (!isset($data['total_pengawasan_uts'])) $data['total_pengawasan_uts'] = 0;
        if (!isset($data['total_pengawasan_uas'])) $data['total_pengawasan_uas'] = 0;
        if (!isset($data['total_pengawasan'])) $data['total_pengawasan'] = 0;
        if (!isset($data['user_data'])) $data['user_data'] = array('nama' => 'Tendik');
        
        $data['content'] = $this->load->view('dashboard/tendik', $data, true);
    }
    
    /**
     * Load halaman utama default
     */
    private function load_default_halaman_utama(&$data) {
        $data['content'] = '<div class="alert alert-info">Selamat datang di Halaman Utama.</div>';
    }
    
    /**
     * Hitung progress PKL
     * @param int $id_mahasiswa
     * @param object $tahun_aktif
     * @return array
     */
    private function calculate_pkl_progress($id_mahasiswa, $tahun_aktif) {
        if (!$id_mahasiswa || !$tahun_aktif) {
            return array('percentage' => 0, 'current_step' => 0, 'total_steps' => 10, 'steps' => array());
        }
        
        $this->load->model('Pkl_tempat_model');
        $this->load->model('Pkl_pengajuan_model');
        $this->load->model('Pkl_pendaftaran_sidang_model');
        $this->load->model('Pkl_klaim_nilai_model');
        $this->load->model('Pkl_nilai_rekap_model');
        
        $steps = array();
        $current_step = 0;
        
        // Step 1: Terdaftar PKL
        $steps[] = array('name' => 'Terdaftar PKL', 'completed' => true);
        $current_step = 1;
        
        // Step 2: Input tempat PKL
        $tempat_pkl = $this->Pkl_tempat_model->get_by_mahasiswa($id_mahasiswa, $tahun_aktif->id);
        if ($tempat_pkl) {
            $steps[] = array('name' => 'Input Tempat PKL', 'completed' => true);
            $current_step = 2;
            
            // Step 3: Tempat PKL disetujui
            if ($tempat_pkl->status_acc_tempat === 'disetujui') {
                $steps[] = array('name' => 'Tempat PKL Disetujui', 'completed' => true);
                $current_step = 3;
                
                // Step 4: Pembimbing ditentukan
                $pengajuan = $this->Pkl_pengajuan_model->get_by_mahasiswa($id_mahasiswa, $tahun_aktif->id);
                if ($pengajuan && $pengajuan->id_pembimbing) {
                    $steps[] = array('name' => 'Pembimbing Ditentukan', 'completed' => true);
                    $current_step = 4;
                    
                    // Step 5: Pengajuan judul PKL
                    if ($pengajuan->judul_pkl && $pengajuan->judul_pkl !== 'Menunggu Pengajuan Judul') {
                        $steps[] = array('name' => 'Pengajuan Judul PKL', 'completed' => true);
                        $current_step = 5;
                        
                        // Step 6: Judul PKL disetujui
                        if ($pengajuan->status_acc_judul === 'acc') {
                            $steps[] = array('name' => 'Judul PKL Disetujui', 'completed' => true);
                            $current_step = 6;
                            
                            // Step 7: Layak sidang
                            if ($pengajuan->is_layak_sidang == 1) {
                                $steps[] = array('name' => 'Layak Sidang', 'completed' => true);
                                $current_step = 7;
                                
                                // Step 8: Pendaftaran sidang
                                $pendaftaran = $this->Pkl_pendaftaran_sidang_model->get_by_mahasiswa($id_mahasiswa, $tahun_aktif->id);
                                if ($pendaftaran) {
                                    $steps[] = array('name' => 'Pendaftaran Sidang', 'completed' => true);
                                    $current_step = 8;
                                    
                                    // Step 9: Klaim nilai
                                    $klaim = $this->Pkl_klaim_nilai_model->get_by_mahasiswa($id_mahasiswa, $tahun_aktif->id);
                                    if ($klaim) {
                                        $steps[] = array('name' => 'Klaim Nilai', 'completed' => true);
                                        $current_step = 9;
                                        
                                        // Step 10: Selesai (nilai rekap ada)
                                        $nilai_rekap = $this->Pkl_nilai_rekap_model->get_by_mahasiswa($id_mahasiswa, $tahun_aktif->id);
                                        if ($nilai_rekap) {
                                            $steps[] = array('name' => 'Selesai', 'completed' => true);
                                            $current_step = 10;
                                        } else {
                                            $steps[] = array('name' => 'Selesai', 'completed' => false);
                                        }
                                    } else {
                                        $steps[] = array('name' => 'Klaim Nilai', 'completed' => false);
                                        $steps[] = array('name' => 'Selesai', 'completed' => false);
                                    }
                                } else {
                                    $steps[] = array('name' => 'Pendaftaran Sidang', 'completed' => false);
                                    $steps[] = array('name' => 'Klaim Nilai', 'completed' => false);
                                    $steps[] = array('name' => 'Selesai', 'completed' => false);
                                }
                            } else {
                                $steps[] = array('name' => 'Layak Sidang', 'completed' => false);
                                $steps[] = array('name' => 'Pendaftaran Sidang', 'completed' => false);
                                $steps[] = array('name' => 'Klaim Nilai', 'completed' => false);
                                $steps[] = array('name' => 'Selesai', 'completed' => false);
                            }
                        } else {
                            $steps[] = array('name' => 'Judul PKL Disetujui', 'completed' => false);
                            $steps[] = array('name' => 'Layak Sidang', 'completed' => false);
                            $steps[] = array('name' => 'Pendaftaran Sidang', 'completed' => false);
                            $steps[] = array('name' => 'Klaim Nilai', 'completed' => false);
                            $steps[] = array('name' => 'Selesai', 'completed' => false);
                        }
                    } else {
                        $steps[] = array('name' => 'Pengajuan Judul PKL', 'completed' => false);
                        $steps[] = array('name' => 'Judul PKL Disetujui', 'completed' => false);
                        $steps[] = array('name' => 'Layak Sidang', 'completed' => false);
                        $steps[] = array('name' => 'Pendaftaran Sidang', 'completed' => false);
                        $steps[] = array('name' => 'Klaim Nilai', 'completed' => false);
                        $steps[] = array('name' => 'Selesai', 'completed' => false);
                    }
                } else {
                    $steps[] = array('name' => 'Pembimbing Ditentukan', 'completed' => false);
                    $steps[] = array('name' => 'Pengajuan Judul PKL', 'completed' => false);
                    $steps[] = array('name' => 'Judul PKL Disetujui', 'completed' => false);
                    $steps[] = array('name' => 'Layak Sidang', 'completed' => false);
                    $steps[] = array('name' => 'Pendaftaran Sidang', 'completed' => false);
                    $steps[] = array('name' => 'Klaim Nilai', 'completed' => false);
                    $steps[] = array('name' => 'Selesai', 'completed' => false);
                }
            } else {
                $steps[] = array('name' => 'Tempat PKL Disetujui', 'completed' => false);
                $steps[] = array('name' => 'Pembimbing Ditentukan', 'completed' => false);
                $steps[] = array('name' => 'Pengajuan Judul PKL', 'completed' => false);
                $steps[] = array('name' => 'Judul PKL Disetujui', 'completed' => false);
                $steps[] = array('name' => 'Layak Sidang', 'completed' => false);
                $steps[] = array('name' => 'Pendaftaran Sidang', 'completed' => false);
                $steps[] = array('name' => 'Klaim Nilai', 'completed' => false);
                $steps[] = array('name' => 'Selesai', 'completed' => false);
            }
        } else {
            $steps[] = array('name' => 'Input Tempat PKL', 'completed' => false);
            $steps[] = array('name' => 'Tempat PKL Disetujui', 'completed' => false);
            $steps[] = array('name' => 'Pembimbing Ditentukan', 'completed' => false);
            $steps[] = array('name' => 'Pengajuan Judul PKL', 'completed' => false);
            $steps[] = array('name' => 'Judul PKL Disetujui', 'completed' => false);
            $steps[] = array('name' => 'Layak Sidang', 'completed' => false);
            $steps[] = array('name' => 'Pendaftaran Sidang', 'completed' => false);
            $steps[] = array('name' => 'Klaim Nilai', 'completed' => false);
            $steps[] = array('name' => 'Selesai', 'completed' => false);
        }
        
        $total_steps = 10;
        $percentage = ($current_step / $total_steps) * 100;
        
        return array(
            'percentage' => round($percentage, 1),
            'current_step' => $current_step,
            'total_steps' => $total_steps,
            'steps' => $steps
        );
    }
    
    /**
     * Hitung progress Skripsi
     * @param int $id_mahasiswa
     * @param object $tahun_aktif
     * @return array
     */
    private function calculate_skripsi_progress($id_mahasiswa, $tahun_aktif) {
        if (!$id_mahasiswa || !$tahun_aktif) {
            return array('percentage' => 0, 'current_step' => 0, 'total_steps' => 10, 'steps' => array());
        }
        
        $this->load->model('Skripsi_model');
        $this->load->model('Skripsi_pengajuan_model');
        $this->load->model('Skripsi_bimbingan_model');
        $this->load->model('Skripsi_pendaftaran_sidang_model');
        $this->load->model('Jadwal_skripsi_model');
        $this->load->model('Skripsi_klaim_nilai_model');
        $this->load->model('Skripsi_nilai_rekap_model');
        
        $steps = array();

        $skripsi = $this->Skripsi_model->get_by_mahasiswa($id_mahasiswa, $tahun_aktif->id);
        $pengajuan = $this->Skripsi_pengajuan_model->get_latest_by_mahasiswa($id_mahasiswa, $tahun_aktif->id);
        $bimbingan_list = $this->Skripsi_bimbingan_model->get_by_mahasiswa($id_mahasiswa, $tahun_aktif->id);

        $pendaftaran = null;
        $jadwal_list = array();
        $klaim = null;
        $nilai_rekap = null;

        if ($skripsi) {
            $pendaftaran = $this->Skripsi_pendaftaran_sidang_model->get_by_mahasiswa_skripsi_tahap($skripsi->id, 'verifikasi');
            if (!$pendaftaran && $this->db->field_exists('tahap', 'skripsi_pendaftaran_sidang')) {
                $pendaftaran = $this->Skripsi_pendaftaran_sidang_model->get_by_mahasiswa_skripsi($skripsi->id);
            }
            $jadwal_list = $this->Jadwal_skripsi_model->get_by_mahasiswa($skripsi->id);
            $klaim = $this->Skripsi_klaim_nilai_model->get_by_mahasiswa_skripsi($skripsi->id);
            if (!empty($jadwal_list)) {
                $jadwal = $jadwal_list[0];
                $nilai_rekap = $this->Skripsi_nilai_rekap_model->get_by_jadwal($jadwal->id);
            }
        }

        $status_p1 = $pengajuan->status_acc_judul_p1 ?? 'pending';
        $status_approve_revisi = $pengajuan->status_approve_revisi ?? 'pending';
        $judul_siap_bimbingan = ($status_p1 === 'acc' || $status_approve_revisi === 'disetujui');

        $acc_layak_p1 = ($skripsi && isset($skripsi->acc_pembimbing_1) && (int) $skripsi->acc_pembimbing_1 === 1);
        $acc_layak_p2 = ($skripsi && isset($skripsi->acc_pembimbing_2) && (int) $skripsi->acc_pembimbing_2 === 1);
        $butuh_p2 = ($pengajuan && !empty($pengajuan->id_pembimbing_2_final));
        $acc_layak_p2_effective = $butuh_p2 ? $acc_layak_p2 : true;

        // Setiap step harus berurutan, agar checkbox tidak loncat.
        $s1 = (bool) $skripsi;
        $s2 = $s1 && (bool) $pengajuan;
        $s3 = $s2 && $judul_siap_bimbingan;
        $s4 = $s3 && !empty($bimbingan_list);
        $s5 = $s4 && $acc_layak_p1;
        $s6 = $s5 && $acc_layak_p2_effective;
        $s7 = $s6 && (bool) $pendaftaran;
        $s8 = $s7 && !empty($jadwal_list);
        $s9 = $s8 && (bool) $klaim;
        $s10 = $s9 && (bool) $nilai_rekap;

        $steps[] = array('name' => 'Terdaftar Skripsi', 'completed' => $s1);
        $steps[] = array('name' => 'Pengajuan Judul', 'completed' => $s2);
        $steps[] = array('name' => 'Judul Siap Bimbingan (ACC P1 / Revisi Disetujui)', 'completed' => $s3);
        $steps[] = array('name' => 'Bimbingan Aktif', 'completed' => $s4);
        $steps[] = array('name' => 'ACC Kelayakan Pembimbing 1', 'completed' => $s5);
        $steps[] = array('name' => 'ACC Kelayakan Pembimbing 2', 'completed' => $s6);
        $steps[] = array('name' => 'Pendaftaran Sidang', 'completed' => $s7);
        $steps[] = array('name' => 'Jadwal Sidang Terbit', 'completed' => $s8);
        $steps[] = array('name' => 'Klaim Nilai', 'completed' => $s9);
        $steps[] = array('name' => 'Nilai Final Terbit', 'completed' => $s10);

        $current_step = 0;
        foreach ($steps as $step) {
            if (!empty($step['completed'])) {
                $current_step++;
            } else {
                break;
            }
        }

        $total_steps = count($steps);
        $percentage = $total_steps > 0 ? ($current_step / $total_steps) * 100 : 0;
        
        return array(
            'percentage' => round($percentage, 1),
            'current_step' => $current_step,
            'total_steps' => $total_steps,
            'steps' => $steps
        );
    }
}





