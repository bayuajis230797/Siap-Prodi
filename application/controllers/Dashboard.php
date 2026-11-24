<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Dashboard Controller
 * Menampilkan dashboard sesuai role user
 */
class Dashboard extends CI_Controller {

    public function __construct() {
        parent::__construct();
        
        // Cek apakah sudah login
        if (!$this->session->userdata('user_id')) {
            redirect('Auth/login');
        }
        
        // Load helper menu
        $this->load->helper('menu');
        
        $this->load->model('Mahasiswa_model');
        $this->load->model('Dosen_model');
        $this->load->model('Tendik_model');
        $this->load->model('Tahun_akademik_model');
        $this->load->model('Notifikasi_model');
    }

    /**
     * Tampilkan dashboard sesuai role
     */
    public function index() {
        $role = $this->session->userdata('role');
        
        if (!$role) {
            redirect('Auth/login');
        }
        
        $data['title'] = 'Dashboard';
        $data['page_title'] = 'Dashboard';
        $data['breadcrumb'] = array(
            array('label' => 'Dashboard', 'active' => true)
        );
        $data['sidebar_menu'] = generate_sidebar_menu($role);
        $user_id = (int) $this->session->userdata('user_id');
        $data['global_notifikasi_unread_count'] = $this->Notifikasi_model->count_unread($user_id, $role);
        $data['global_notifikasi_items'] = $this->Notifikasi_model->get_by_user($user_id, $role, 5, 0);
        
        // Load data sesuai role
        switch ($role) {
            case 'admin':
                $this->load_admin_dashboard($data);
                break;
            case 'sekprodi':
                $this->load_sekprodi_dashboard($data);
                break;
            case 'dosen':
                $this->load_dosen_dashboard($data);
                break;
            case 'mahasiswa':
                $this->load_mahasiswa_dashboard($data);
                break;
            case 'kaprodi':
                $this->load_kaprodi_dashboard($data);
                break;
            case 'tendik':
                $this->load_tendik_dashboard($data);
                break;
            default:
                $this->load_default_dashboard($data);
        }
        
        $this->load->view('layouts/main', $data);
    }
    
    /**
     * Load dashboard untuk admin
     */
    private function load_admin_dashboard($data) {
        $data['total_mahasiswa'] = $this->Mahasiswa_model->count_all();
        $data['total_dosen'] = $this->Dosen_model->count_all();
        $data['total_tendik'] = $this->Tendik_model->count_all();
        $data['tahun_aktif'] = $this->Tahun_akademik_model->get_aktif();
        
        // Statistik mahasiswa per semester
        $data['statistik_semester'] = array();
        $data['statistik_semester'] = $this->Mahasiswa_model->get_statistik_semester(); // mengikuti tahun akademik yang dilihat
        
        // Statistik mahasiswa per kelas (top 10; kelas 8 dipisah alumni / bukan)
        $data['statistik_kelas'] = $this->Mahasiswa_model->get_statistik_kelas_aktif(10);
        $ringkasan = $this->Mahasiswa_model->get_ringkasan_aktif();
        $data['total_mahasiswa_aktif'] = $ringkasan['aktif'];
        $data['total_alumni'] = $ringkasan['alumni'];
        
        // Statistik dosen
        $this->db->select('COUNT(*) as total');
        $this->db->where('status', 'aktif');
        $result = $this->db->get('dosen')->row();
        $data['dosen_aktif'] = $result ? $result->total : 0;
        
        $this->db->select('COUNT(*) as total');
        $this->db->where('status', 'aktif');
        $this->db->where('is_pembimbing_skripsi', 1);
        $result = $this->db->get('dosen')->row();
        $data['dosen_pembimbing_skripsi'] = $result ? $result->total : 0;
        
        $this->db->select('COUNT(*) as total');
        $this->db->where('status', 'aktif');
        $this->db->where('is_pembimbing_pkl', 1);
        $result = $this->db->get('dosen')->row();
        $data['dosen_pembimbing_pkl'] = $result ? $result->total : 0;
        
        // Statistik mahasiswa per angkatan (5 tahun terakhir)
        $current_year = $this->Mahasiswa_model->get_tahun_basis();
        $per_angkatan = $this->Mahasiswa_model->get_jumlah_per_angkatan();
        $data['statistik_angkatan'] = array();
        for ($i = 0; $i < 5; $i++) {
            $tahun = $current_year - $i;
            $data['statistik_angkatan'][$tahun] = isset($per_angkatan[$tahun]) ? $per_angkatan[$tahun] : 0;
        }
        
        // Statistik status mahasiswa
        $this->db->select('status, COUNT(*) as jumlah');
        $this->db->from('mahasiswa');
        $this->db->group_by('status');
        $data['statistik_status_mahasiswa'] = $this->db->get()->result();
        
        // Statistik status dosen
        $this->db->select('status, COUNT(*) as jumlah');
        $this->db->from('dosen');
        $this->db->group_by('status');
        $data['statistik_status_dosen'] = $this->db->get()->result();
        
        $data['content'] = $this->load->view('dashboard/admin', $data, true);
    }
    
    /**
     * Load dashboard untuk sekprodi
     */
    private function load_sekprodi_dashboard($data) {
        try {
            $data['tahun_aktif'] = $this->Tahun_akademik_model->get_aktif();
            
            // Mahasiswa aktif = semester riil 1-8; semester riil > 8 dihitung terpisah sebagai alumni
            $ringkasan = $this->Mahasiswa_model->get_ringkasan_aktif();
            $data['total_mahasiswa_aktif'] = $ringkasan['aktif'];
            $data['total_alumni'] = $ringkasan['alumni'];
            
            // Statistik mahasiswa per kelas (kelas 8 dipisah alumni / bukan)
            $data['statistik_kelas'] = $this->Mahasiswa_model->get_statistik_kelas_aktif();
            
            // Statistik mahasiswa per angkatan (5 tahun terakhir)
            $current_year = $this->Mahasiswa_model->get_tahun_basis();
            $per_angkatan = $this->Mahasiswa_model->get_jumlah_per_angkatan();
            krsort($per_angkatan);
            $data['statistik_angkatan'] = array();
            foreach ($per_angkatan as $angk => $jml) {
                if ($angk >= $current_year - 4) {
                    $data['statistik_angkatan'][] = (object) array('angkatan' => $angk, 'jumlah' => $jml);
                }
            }
            
            // Pastikan variabel selalu ada
            if (!isset($data['total_mahasiswa_aktif'])) {
                $data['total_mahasiswa_aktif'] = 0;
            }
            if (!isset($data['statistik_kelas'])) {
                $data['statistik_kelas'] = array();
            }
            if (!isset($data['statistik_angkatan'])) {
                $data['statistik_angkatan'] = array();
            }
            
            // Load view dengan error handling
            if (file_exists(APPPATH . 'views/dashboard/sekprodi.php')) {
                $data['content'] = $this->load->view('dashboard/sekprodi', $data, true);
            } else {
                $data['content'] = '<div class="alert alert-warning">View dashboard sekprodi tidak ditemukan.</div>';
            }
        } catch (Exception $e) {
            $data['content'] = '<div class="alert alert-danger">Error loading dashboard: ' . $e->getMessage() . '</div>';
        }
    }
    
    /**
     * Load dashboard untuk dosen
     */
    private function load_dosen_dashboard($data) {
        $data['content'] = $this->load->view('dashboard/dosen', $data, true);
    }
    
    /**
     * Load dashboard untuk mahasiswa
     */
    private function load_mahasiswa_dashboard($data) {
        try {
            $id_mahasiswa = $this->session->userdata('ref_id');
            $tahun_aktif = $this->Tahun_akademik_model->get_aktif();
            
            // Load models
            $this->load->model('Mahasiswa_model');
            $this->load->model('Skripsi_model');
            $this->load->model('Skripsi_pengajuan_model');
            $this->load->model('Jadwal_skripsi_model');
            $this->load->model('Kurikulum_model');
            $this->load->model('Mata_kuliah_aktif_model');
            
            // Ambil data mahasiswa
            $mahasiswa = null;
            if ($id_mahasiswa) {
                $mahasiswa = $this->Mahasiswa_model->get_by_id($id_mahasiswa);
            }
            
            // Cek apakah terdaftar skripsi
            $skripsi = null;
            $pengajuan = null;
            $jadwal_skripsi = null;
            
            if ($id_mahasiswa && $tahun_aktif) {
                $skripsi = $this->Skripsi_model->get_by_mahasiswa($id_mahasiswa, $tahun_aktif->id);
                
                if ($skripsi) {
                    $pengajuan = $this->Skripsi_pengajuan_model->get_latest_by_mahasiswa($id_mahasiswa, $tahun_aktif->id);
                    $jadwal_list = $this->Jadwal_skripsi_model->get_by_mahasiswa($skripsi->id);
                    $jadwal_skripsi = !empty($jadwal_list) ? $jadwal_list[0] : null;
                }
            }
            
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
            
            $data['mahasiswa'] = $mahasiswa;
            $data['skripsi'] = $skripsi;
            $data['pengajuan'] = $pengajuan;
            $data['jadwal_skripsi'] = $jadwal_skripsi;
            $data['tahun_aktif'] = $tahun_aktif;
            $data['kurikulum_info'] = $kurikulum_info;
            $data['struktur_kurikulum'] = $struktur_kurikulum;
            
            // Load view mahasiswa
            $view_path = APPPATH . 'views/dashboard/mahasiswa.php';
            if (file_exists($view_path)) {
                $data['content'] = $this->load->view('dashboard/mahasiswa', $data, true);
            } else {
                // Fallback jika view tidak ada
                $data['content'] = '<div class="alert alert-warning">View dashboard mahasiswa tidak ditemukan. Path: ' . $view_path . '</div>';
            }
        } catch (Exception $e) {
            // Error handling
            $data['content'] = '<div class="alert alert-danger">Error loading dashboard: ' . $e->getMessage() . '</div>';
        }
    }
    
    /**
     * Load dashboard untuk kaprodi
     */
    private function load_kaprodi_dashboard($data) {
        $data['content'] = $this->load->view('dashboard/kaprodi', $data, true);
    }
    
    /**
     * Load dashboard untuk tendik
     */
    private function load_tendik_dashboard($data) {
        $data['content'] = $this->load->view('dashboard/tendik', $data, true);
    }
    
    /**
     * Load dashboard default
     */
    private function load_default_dashboard($data) {
        $data['content'] = '<div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Dashboard</h3>
                    </div>
                    <div class="card-body">
                        <p class="lead">Selamat datang di SIAP PRODI</p>
                        <p>Dashboard untuk role Anda sedang dalam pengembangan.</p>
                    </div>
                </div>
            </div>
        </div>';
    }
}

