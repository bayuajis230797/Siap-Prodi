<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Pkl_monitoring_sekprodi Controller
 * Monitoring logbook dan absensi semua mahasiswa PKL untuk sekprodi/kaprodi
 */
class Pkl_monitoring_sekprodi extends MY_Controller {

    public function __construct() {
        parent::__construct();
        $this->check_role(array('sekprodi', 'kaprodi'));
        $this->load->model('Pkl_pengajuan_model');
        $this->load->model('Pkl_logbook_model');
        $this->load->model('Pkl_absensi_model');
        $this->load->model('Pkl_tempat_model');
        $this->load->model('Pkl_bimbingan_model');
        $this->load->model('Pkl_bimbingan_log_model');
        $this->load->model('Tahun_akademik_model');
        $this->load->library('pagination');
        $this->load->helper('pkl');
    }

    /**
     * Index - Daftar semua mahasiswa PKL dengan monitoring logbook & absensi
     */
    public function index() {
        $tahun_aktif = $this->Tahun_akademik_model->get_aktif();
        if (!$tahun_aktif) {
            $this->session->set_flashdata('error', 'Tidak ada tahun akademik aktif.');
            redirect('Dashboard');
        }

        // Filter
        $filter_kelas = $this->input->get('kelas');
        $filter_status = $this->input->get('status'); // status pengajuan
        $per_page = $this->input->get('per_page') ? (int)$this->input->get('per_page') : 25;
        $per_page = in_array($per_page, [10, 25, 50, 100]) ? $per_page : 25;

        // Ambil semua tempat PKL yang sudah di-approve sekprodi
        // Join dengan pkl_pengajuan untuk mendapatkan pembimbing
        // Gunakan INNER JOIN untuk memastikan hanya data yang memiliki pengajuan dengan pembimbing
        $this->db->select('pt.*, m.nim, m.nama_mahasiswa, m.kelas, ta.tahun_akademik, ta.semester,
                          pp.id as id_pengajuan, pp.id_pembimbing, pp.judul_pkl, pp.status as status_pengajuan, pp.status_acc_judul,
                          d.nama_dosen as pembimbing_nama, d.nidn as pembimbing_nidn');
        $this->db->from('pkl_tempat pt');
        $this->db->join('mahasiswa m', 'm.id = pt.id_mahasiswa', 'inner');
        $this->db->join('tahun_akademik ta', 'ta.id = pt.id_tahun_akademik', 'inner');
        $this->db->join('pkl_pengajuan pp', 'pp.id_mahasiswa = pt.id_mahasiswa AND pp.id_tahun_akademik = pt.id_tahun_akademik', 'inner');
        $this->db->join('dosen d', 'd.id = pp.id_pembimbing', 'inner');
        $this->db->where('pt.id_tahun_akademik', $tahun_aktif->id);
        $this->db->where('pt.status_acc_tempat', 'disetujui');
        $this->db->where('pp.id_pembimbing IS NOT NULL');
        
        // Filter kelas jika ada
        if ($filter_kelas) {
            $this->db->where('m.kelas', $filter_kelas);
        }
        
        $this->db->order_by('m.kelas', 'ASC');
        $this->db->order_by('m.nim', 'ASC');
        $tempat_list = $this->db->get()->result();
        
        // Konversi ke format yang sama dengan pengajuan untuk kompatibilitas
        $mahasiswa_list = array();
        foreach ($tempat_list as $tempat) {
            // Buat object pengajuan dari data tempat
            $pengajuan = new stdClass();
            $pengajuan->id = $tempat->id_pengajuan ? $tempat->id_pengajuan : null;
            $pengajuan->id_mahasiswa = $tempat->id_mahasiswa;
            $pengajuan->id_tahun_akademik = $tempat->id_tahun_akademik;
            $pengajuan->id_pembimbing = $tempat->id_pembimbing;
            $pengajuan->judul_pkl = $tempat->judul_pkl;
            $pengajuan->status = $tempat->status_pengajuan;
            $pengajuan->status_acc_judul = $tempat->status_acc_judul;
            $pengajuan->nim = $tempat->nim;
            $pengajuan->nama_mahasiswa = $tempat->nama_mahasiswa;
            $pengajuan->kelas = $tempat->kelas;
            $pengajuan->tahun_akademik = $tempat->tahun_akademik;
            $pengajuan->semester = $tempat->semester;
            $pengajuan->pembimbing_nama = $tempat->pembimbing_nama;
            $pengajuan->pembimbing_nidn = $tempat->pembimbing_nidn;
            
            // Ambil statistik logbook dan absensi
            $pengajuan->statistik_logbook = $this->Pkl_logbook_model->get_statistik($pengajuan->id_mahasiswa, $tahun_aktif->id);
            $pengajuan->statistik_absensi = $this->Pkl_absensi_model->get_statistik_by_tahun($pengajuan->id_mahasiswa, $tahun_aktif->id);

            // Indikator risiko monitoring (7 hari terakhir + kualitas absensi + progres bimbingan)
            $tanggal_7_hari = date('Y-m-d', strtotime('-7 days'));
            $logbook_7_hari = $this->Pkl_logbook_model->count_since($pengajuan->id_mahasiswa, $tanggal_7_hari, $tahun_aktif->id);
            $absensi_7_hari = $this->Pkl_absensi_model->count_since_by_tahun($pengajuan->id_mahasiswa, $tahun_aktif->id, $tanggal_7_hari);
            $device_stats = $this->Pkl_absensi_model->get_device_statistics_by_tahun($pengajuan->id_mahasiswa, $tahun_aktif->id);
            $bimbingan_list = $this->Pkl_bimbingan_model->get_by_mahasiswa($pengajuan->id_mahasiswa, $tahun_aktif->id);
            $last_bimbingan_activity = null;
            if (!empty($bimbingan_list)) {
                $last_bimbingan_activity = $bimbingan_list[0]->last_activity ?? null;
            }

            $risk = $this->build_risk_indicator(
                $pengajuan->statistik_logbook,
                $pengajuan->statistik_absensi,
                $device_stats,
                $logbook_7_hari,
                $absensi_7_hari,
                $last_bimbingan_activity
            );
            $pengajuan->risk_indicator = $risk;
            $pengajuan->workflow_state = resolve_pkl_workflow_state($pengajuan->id_mahasiswa, $tahun_aktif);
            
            $mahasiswa_list[] = $pengajuan;
        }

        // Ringkasan risiko sebelum pagination
        $risk_summary = array(
            'high' => 0,
            'medium' => 0,
            'low' => 0
        );
        foreach ($mahasiswa_list as $mhs) {
            $level = $mhs->risk_indicator['level'] ?? 'low';
            if (!isset($risk_summary[$level])) {
                $risk_summary[$level] = 0;
            }
            $risk_summary[$level]++;
        }

        // Pagination (manual karena data sudah di-filter)
        $total_rows = count($mahasiswa_list);
        $page = ($this->uri->segment(3)) ? $this->uri->segment(3) : 1;
        $offset = ($page - 1) * $per_page;
        $mahasiswa_list = array_slice($mahasiswa_list, $offset, $per_page);

        // Setup pagination
        $config['base_url'] = site_url('Pkl_monitoring_sekprodi/index');
        $config['total_rows'] = $total_rows;
        $config['per_page'] = $per_page;
        $config['uri_segment'] = 3;
        
        $query_params = array_filter(array(
            'per_page' => ($per_page != 25 ? $per_page : null),
            'kelas' => $filter_kelas,
            'status' => $filter_status
        ));
        $query_string = http_build_query($query_params);
        if ($query_string) {
            $config['suffix'] = '?' . $query_string;
            $config['first_url'] = site_url('Pkl_monitoring_sekprodi/index?' . $query_string);
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
        $config['cur_tag_open'] = '<li class="page-item active"><span class="page-link">';
        $config['cur_tag_close'] = '</span></li>';
        $config['num_tag_open'] = '<li class="page-item">';
        $config['num_tag_close'] = '</li>';
        $config['attributes'] = array('class' => 'page-link');

        $this->pagination->initialize($config);

        // Ambil daftar kelas untuk filter
        $this->load->model('Kelas_model');
        $kelas_list = $this->Kelas_model->get_all_kelas();

        $data = array(
            'title' => 'Logbook & Absensi PKL',
            'page_title' => 'Logbook & Absensi PKL',
            'breadcrumb' => array(
                array('label' => 'PKL', 'url' => '#'),
                array('label' => 'Logbook & Absensi', 'active' => true)
            ),
            'sidebar_menu' => generate_sidebar_menu(get_user_role()),
            'mahasiswa_list' => $mahasiswa_list,
            'tahun_aktif' => $tahun_aktif,
            'filter_kelas' => $filter_kelas,
            'filter_status' => $filter_status,
            'kelas_list' => $kelas_list,
            'pagination' => $this->pagination->create_links(),
            'per_page' => $per_page,
            'total_rows' => $total_rows,
            'risk_summary' => $risk_summary
        );

        $data['content'] = $this->load->view('sekprodi/pkl/monitoring', $data, true);
        $this->load->view('layouts/main', $data);
    }

    /**
     * Detail monitoring untuk mahasiswa tertentu
     * Parameter bisa id_pengajuan atau id_mahasiswa
     */
    public function detail($pengajuan_id) {
        $tahun_aktif = $this->Tahun_akademik_model->get_aktif();
        if (!$tahun_aktif) {
            $this->session->set_flashdata('error', 'Tidak ada tahun akademik aktif.');
            redirect('Pkl_monitoring_sekprodi');
        }

        // Coba ambil pengajuan berdasarkan id
        $selected_pengajuan = $this->Pkl_pengajuan_model->get_by_id($pengajuan_id);
        
        // Jika tidak ditemukan, mungkin parameter adalah id_mahasiswa
        if (!$selected_pengajuan) {
            // Coba ambil berdasarkan id_mahasiswa
            $selected_pengajuan = $this->Pkl_pengajuan_model->get_by_mahasiswa($pengajuan_id, $tahun_aktif->id);
        }
        
        // Jika masih tidak ditemukan, coba ambil dari tempat PKL
        if (!$selected_pengajuan) {
            // Ambil tempat PKL berdasarkan id_mahasiswa
            $tempat_pkl = $this->Pkl_tempat_model->get_by_mahasiswa($pengajuan_id, $tahun_aktif->id);
            if ($tempat_pkl && $tempat_pkl->status_acc_tempat === 'disetujui') {
                // Ambil pengajuan berdasarkan id_mahasiswa
                $selected_pengajuan = $this->Pkl_pengajuan_model->get_by_mahasiswa($tempat_pkl->id_mahasiswa, $tahun_aktif->id);
                
                // Jika pengajuan tidak ada, buat object dummy dari tempat
                if (!$selected_pengajuan) {
                    $this->load->model('Mahasiswa_model');
                    $mahasiswa = $this->Mahasiswa_model->get_by_id($tempat_pkl->id_mahasiswa);
                    $selected_pengajuan = new stdClass();
                    $selected_pengajuan->id = null;
                    $selected_pengajuan->id_mahasiswa = $tempat_pkl->id_mahasiswa;
                    $selected_pengajuan->id_tahun_akademik = $tahun_aktif->id;
                    $selected_pengajuan->id_pembimbing = null;
                    $selected_pengajuan->judul_pkl = 'Menunggu Pengajuan Judul';
                    $selected_pengajuan->status = 'pending';
                    $selected_pengajuan->status_acc_judul = 'pending';
                    $selected_pengajuan->nim = $mahasiswa ? $mahasiswa->nim : '';
                    $selected_pengajuan->nama_mahasiswa = $mahasiswa ? $mahasiswa->nama_mahasiswa : '';
                    $selected_pengajuan->kelas = $mahasiswa ? $mahasiswa->kelas : '';
                }
            }
        }
        
        if (!$selected_pengajuan) {
            $this->session->set_flashdata('error', 'Data mahasiswa PKL tidak ditemukan.');
            redirect('Pkl_monitoring_sekprodi');
        }

        // Ambil data tempat PKL
        $tempat_pkl = $this->Pkl_tempat_model->get_by_mahasiswa($selected_pengajuan->id_mahasiswa, $tahun_aktif->id);
        
        // Cek apakah tempat sudah di-approve
        if (!$tempat_pkl || $tempat_pkl->status_acc_tempat !== 'disetujui') {
            $this->session->set_flashdata('error', 'Tempat PKL belum disetujui.');
            redirect('Pkl_monitoring_sekprodi');
        }
        
        // Ambil logbook mahasiswa
        $logbook_list = $this->Pkl_logbook_model->get_by_mahasiswa($selected_pengajuan->id_mahasiswa, $tahun_aktif->id);
        $statistik_logbook = $this->Pkl_logbook_model->get_statistik($selected_pengajuan->id_mahasiswa, $tahun_aktif->id);
        
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
        
        // Ambil absensi mahasiswa
        $absensi_list = $this->Pkl_absensi_model->get_by_mahasiswa($selected_pengajuan->id_mahasiswa);
        $statistik_absensi = $this->Pkl_absensi_model->get_statistik_by_tahun($selected_pengajuan->id_mahasiswa, $tahun_aktif->id);

        // Ambil informasi dosen pembimbing akademik jika ada
        $dosen_pembimbing = null;
        if ($selected_pengajuan->id_pembimbing) {
            $this->load->model('Dosen_model');
            $dosen_pembimbing = $this->Dosen_model->get_by_id($selected_pengajuan->id_pembimbing);
        }

        // Ambil semua mahasiswa untuk sidebar
        // Filter: tempat PKL sudah di-approve sekprodi & pembimbing sudah ditentukan
        // Gunakan INNER JOIN untuk memastikan hanya data yang memiliki pengajuan dengan pembimbing
        $this->db->select('pt.*, m.nim, m.nama_mahasiswa, m.kelas,
                          pp.id as id_pengajuan, pp.id_pembimbing, pp.judul_pkl, pp.status as status_pengajuan, pp.status_acc_judul,
                          d.nama_dosen as pembimbing_nama');
        $this->db->from('pkl_tempat pt');
        $this->db->join('mahasiswa m', 'm.id = pt.id_mahasiswa', 'inner');
        $this->db->join('pkl_pengajuan pp', 'pp.id_mahasiswa = pt.id_mahasiswa AND pp.id_tahun_akademik = pt.id_tahun_akademik', 'inner');
        $this->db->join('dosen d', 'd.id = pp.id_pembimbing', 'inner');
        $this->db->where('pt.id_tahun_akademik', $tahun_aktif->id);
        $this->db->where('pt.status_acc_tempat', 'disetujui');
        $this->db->where('pp.id_pembimbing IS NOT NULL');
        $this->db->order_by('m.kelas', 'ASC');
        $this->db->order_by('m.nim', 'ASC');
        $tempat_list = $this->db->get()->result();
        
        // Konversi ke format pengajuan untuk kompatibilitas
        $mahasiswa_list = array();
        foreach ($tempat_list as $tempat) {
            $pengajuan = new stdClass();
            $pengajuan->id = $tempat->id_pengajuan ? $tempat->id_pengajuan : null;
            $pengajuan->id_mahasiswa = $tempat->id_mahasiswa;
            $pengajuan->id_tahun_akademik = $tempat->id_tahun_akademik;
            $pengajuan->id_pembimbing = $tempat->id_pembimbing;
            $pengajuan->judul_pkl = $tempat->judul_pkl;
            $pengajuan->status = $tempat->status_pengajuan;
            $pengajuan->status_acc_judul = $tempat->status_acc_judul;
            $pengajuan->nim = $tempat->nim;
            $pengajuan->nama_mahasiswa = $tempat->nama_mahasiswa;
            $pengajuan->kelas = $tempat->kelas;
            $pengajuan->pembimbing_nama = $tempat->pembimbing_nama;
            $mahasiswa_list[] = $pengajuan;
        }

        $data = array(
            'title' => 'Detail Logbook & Absensi - ' . $selected_pengajuan->nama_mahasiswa,
            'page_title' => 'Detail Logbook & Absensi',
            'breadcrumb' => array(
                array('label' => 'PKL', 'url' => '#'),
                array('label' => 'Logbook & Absensi', 'url' => site_url('Pkl_monitoring_sekprodi')),
                array('label' => 'Detail', 'active' => true)
            ),
            'sidebar_menu' => generate_sidebar_menu(get_user_role()),
            'mahasiswa_list' => $mahasiswa_list,
            'selected_pengajuan' => $selected_pengajuan,
            'tempat_pkl' => $tempat_pkl,
            'dosen_pembimbing' => $dosen_pembimbing,
            'logbook_list' => $logbook_list,
            'absensi_list' => $absensi_list,
            'statistik_logbook' => $statistik_logbook,
            'statistik_absensi' => $statistik_absensi,
            'timeline_bimbingan' => $this->build_bimbingan_timeline($selected_pengajuan->id_mahasiswa, $tahun_aktif->id),
            'tahun_aktif' => $tahun_aktif
        );

        $data['content'] = $this->load->view('sekprodi/pkl/monitoring_detail', $data, true);
        $this->load->view('layouts/main', $data);
    }

    /**
     * Bangun indikator risiko monitoring per mahasiswa.
     */
    private function build_risk_indicator($statistik_logbook, $statistik_absensi, $device_stats, $logbook_7_hari, $absensi_7_hari, $last_bimbingan_activity) {
        $score = 0;
        $issues = array();

        if ((int) $logbook_7_hari === 0) {
            $score += 2;
            $issues[] = 'Tidak ada logbook 7 hari terakhir';
        }

        if ((int) $absensi_7_hari === 0) {
            $score += 2;
            $issues[] = 'Tidak ada absensi 7 hari terakhir';
        }

        $di_luar_radius = (int) ($statistik_absensi->di_luar_radius ?? 0);
        if ($di_luar_radius > 0) {
            $score += 2;
            $issues[] = $di_luar_radius . ' absensi di luar radius';
        }

        $device_berbeda = (int) ($device_stats->device_berbeda ?? 0);
        if ($device_berbeda > 0) {
            $score += 2;
            $issues[] = $device_berbeda . ' absensi dengan device berbeda';
        }

        if (!empty($last_bimbingan_activity)) {
            $hari_tanpa_bimbingan = (int) floor((time() - strtotime($last_bimbingan_activity)) / 86400);
            if ($hari_tanpa_bimbingan >= 7) {
                $score += 1;
                $issues[] = 'Aktivitas bimbingan stagnan ' . $hari_tanpa_bimbingan . ' hari';
            }
        }

        $level = 'low';
        $label = 'Rendah';
        $class = 'success';
        if ($score >= 5) {
            $level = 'high';
            $label = 'Tinggi';
            $class = 'danger';
        } elseif ($score >= 3) {
            $level = 'medium';
            $label = 'Sedang';
            $class = 'warning';
        }

        return array(
            'score' => $score,
            'issues' => $issues,
            'level' => $level,
            'label' => $label,
            'class' => $class
        );
    }

    /**
     * Ringkasan timeline bimbingan PKL (read-only) untuk monitoring manajemen.
     */
    private function build_bimbingan_timeline($id_mahasiswa, $id_tahun_akademik) {
        $result = array(
            'statistik' => (object) array(
                'total_topik' => 0,
                'total_log' => 0,
                'total_acc' => 0,
                'total_revisi' => 0,
                'last_activity' => null
            ),
            'items' => array()
        );

        $bimbingan_list = $this->Pkl_bimbingan_model->get_by_mahasiswa($id_mahasiswa, $id_tahun_akademik);
        if (empty($bimbingan_list)) {
            return $result;
        }

        $result['statistik']->total_topik = count($bimbingan_list);
        $timeline = array();

        foreach ($bimbingan_list as $bimbingan) {
            if (!empty($bimbingan->last_activity)) {
                if (empty($result['statistik']->last_activity) || strtotime($bimbingan->last_activity) > strtotime($result['statistik']->last_activity)) {
                    $result['statistik']->last_activity = $bimbingan->last_activity;
                }
            }

            $logs = $this->Pkl_bimbingan_log_model->get_by_bimbingan($bimbingan->id);
            foreach ($logs as $log) {
                $result['statistik']->total_log++;
                if (($log->status_revisi ?? '') === 'acc') {
                    $result['statistik']->total_acc++;
                } elseif (($log->status_revisi ?? '') === 'revisi') {
                    $result['statistik']->total_revisi++;
                }

                $timeline[] = array(
                    'tanggal' => $log->created_at,
                    'topik' => $bimbingan->judul_topik,
                    'pengirim' => ($log->role_pengguna === 'dosen') ? 'Dosen Pembimbing' : 'Mahasiswa',
                    'status' => $log->status_revisi,
                    'pesan' => $log->keterangan_revisi ?: $log->pesan ?: '-'
                );
            }
        }

        usort($timeline, function($a, $b) {
            return strtotime($b['tanggal']) - strtotime($a['tanggal']);
        });

        $result['items'] = array_slice($timeline, 0, 15);
        return $result;
    }
}

