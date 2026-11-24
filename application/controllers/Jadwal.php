<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Jadwal extends MY_Controller {

    public function __construct() {
        parent::__construct();
        $this->check_role('mahasiswa');
        $this->load->model('Jadwal_kuliah_model');
        $this->load->model('Jadwal_uts_model');
        $this->load->model('Jadwal_uas_model');
        $this->load->model('Jadwal_skripsi_model');
        $this->load->model('Jadwal_pkl_model');
        $this->load->model('Skripsi_nilai_sidang_model');
        $this->load->model('Tahun_akademik_model');
        $this->load->model('Mahasiswa_model');
        $this->load->model('Skripsi_model');
        $this->load->model('Mahasiswa_pkl_model');
    }

    public function index() {
        $id_mahasiswa = $this->session->userdata('ref_id');
        $tahun_aktif = $this->Tahun_akademik_model->get_aktif();
        
        // Ambil data mahasiswa untuk mendapatkan kelas
        $mahasiswa = $this->Mahasiswa_model->get_by_id($id_mahasiswa);
        $kelas = $mahasiswa ? $mahasiswa->kelas : null;
        
        $jadwal_kuliah = array();
        $jadwal_uts = array();
        $jadwal_uas = array();
        $jadwal_skripsi = array();
        $jadwal_pkl = array();
        
        if ($tahun_aktif && $kelas) {
            $jadwal_kuliah = $this->Jadwal_kuliah_model->get_by_kelas($kelas, $tahun_aktif->id);
            // Ambil jadwal UTS hanya untuk sesi mahasiswa yang login
            $jadwal_uts = $this->get_jadwal_uts_by_mahasiswa($id_mahasiswa, $kelas, $tahun_aktif->id);
            // Ambil jadwal UAS hanya untuk sesi mahasiswa yang login
            $jadwal_uas = $this->get_jadwal_uas_by_mahasiswa($id_mahasiswa, $kelas, $tahun_aktif->id);
        }
        
        $catatan_revisi = array();
        if ($tahun_aktif && $id_mahasiswa) {
            $jadwal_skripsi = $this->Jadwal_skripsi_model->get_by_mahasiswa($id_mahasiswa, $tahun_aktif->id);
            // Ambil catatan revisi untuk setiap jadwal
            foreach ($jadwal_skripsi as $js) {
                $nilai_sidang = $this->Skripsi_nilai_sidang_model->get_by_jadwal($js->id);
                $catatan_revisi[$js->id] = $nilai_sidang;
            }
            
            // Ambil jadwal ujian PKL untuk mahasiswa
            $jadwal_pkl = $this->Jadwal_pkl_model->get_by_mahasiswa($id_mahasiswa, $tahun_aktif->id);
        }
        
        // Group jadwal kuliah by hari
        $jadwal_by_hari = array();
        $hari_list = array('senin', 'selasa', 'rabu', 'kamis', 'jumat', 'sabtu', 'minggu');
        foreach ($hari_list as $hari) {
            $jadwal_by_hari[$hari] = array();
        }
        
        foreach ($jadwal_kuliah as $jk) {
            $hari = strtolower($jk->hari);
            if (isset($jadwal_by_hari[$hari])) {
                $jadwal_by_hari[$hari][] = $jk;
            }
        }
        
        // Cek apakah mahasiswa terdaftar PKL atau skripsi
        $is_mahasiswa_pkl = false;
        $is_mahasiswa_skripsi = false;
        
        if ($id_mahasiswa && $tahun_aktif) {
            $pkl = $this->Mahasiswa_pkl_model->get_by_mahasiswa($id_mahasiswa, $tahun_aktif->id);
            $is_mahasiswa_pkl = !empty($pkl);
            
            $skripsi = $this->Skripsi_model->get_by_mahasiswa($id_mahasiswa, $tahun_aktif->id);
            $is_mahasiswa_skripsi = !empty($skripsi);
        }
        
        $data = array(
            'title' => 'Jadwal',
            'page_title' => 'Jadwal',
            'breadcrumb' => array(array('label' => 'Jadwal', 'active' => true)),
            'sidebar_menu' => generate_sidebar_menu('mahasiswa'),
            'tahun_aktif' => $tahun_aktif,
            'kelas' => $kelas,
            'mahasiswa' => $mahasiswa,
            'jadwal_kuliah' => $jadwal_kuliah,
            'jadwal_by_hari' => $jadwal_by_hari,
            'jadwal_uts' => $jadwal_uts,
            'jadwal_uas' => $jadwal_uas,
            'jadwal_skripsi' => $jadwal_skripsi,
            'jadwal_pkl' => $jadwal_pkl,
            'catatan_revisi' => $catatan_revisi,
            'is_mahasiswa_pkl' => $is_mahasiswa_pkl,
            'is_mahasiswa_skripsi' => $is_mahasiswa_skripsi
        );
        
        $data['content'] = $this->load->view('mahasiswa/jadwal', $data, true);
        $this->load->view('layouts/main', $data);
    }

    /**
     * Ambil jadwal UTS berdasarkan mahasiswa dan sesi
     */
    private function get_jadwal_uts_by_mahasiswa($id_mahasiswa, $kelas, $id_tahun_akademik) {
        // Ambil sesi mahasiswa dari tabel jadwal_uts_mahasiswa_sesi
        $this->db->select('jms.id_jadwal_uts, jms.sesi');
        $this->db->from('jadwal_uts_mahasiswa_sesi jms');
        $this->db->where('jms.id_mahasiswa', $id_mahasiswa);
        $mahasiswa_sesi = $this->db->get()->result();
        
        // Jika mahasiswa tidak ada di tabel sesi, ambil semua jadwal kelas (backward compatibility)
        if (empty($mahasiswa_sesi)) {
            $results = $this->Jadwal_uts_model->get_by_kelas($kelas, $id_tahun_akademik);
            // Tambahkan kehadiran untuk setiap jadwal (pengawas sudah diambil di get_by_kelas)
            foreach ($results as $result) {
                $this->db->where('id_jadwal_uts', $result->id);
                $this->db->where('id_mahasiswa', $id_mahasiswa);
                $result->kehadiran = $this->db->get('jadwal_uts_kehadiran')->row();
                
                // Jika belum ada record kehadiran, buat dengan status "tidak_hadir"
                if (!$result->kehadiran) {
                    $this->db->insert('jadwal_uts_kehadiran', array(
                        'id_jadwal_uts' => $result->id,
                        'id_mahasiswa' => $id_mahasiswa,
                        'status_kehadiran' => 'tidak_hadir',
                        'created_at' => date('Y-m-d H:i:s')
                    ));
                    // Reload kehadiran
                    $this->db->where('id_jadwal_uts', $result->id);
                    $this->db->where('id_mahasiswa', $id_mahasiswa);
                    $result->kehadiran = $this->db->get('jadwal_uts_kehadiran')->row();
                }
            }
            return $results;
        }
        
        // Ambil id_jadwal_uts dari hasil query
        $id_jadwal_uts_list = array();
        foreach ($mahasiswa_sesi as $ms) {
            $id_jadwal_uts_list[] = $ms->id_jadwal_uts;
        }
        
        if (empty($id_jadwal_uts_list)) {
            return array();
        }
        
        // Ambil jadwal UTS berdasarkan id_jadwal_uts dan sesi
        $this->db->select('jut.*, 
                          mk.kode_mk, mk.nama_mk, mk.sks,
                          d.nidn, d.nama_dosen,
                          rk.kode_ruang, rk.nama_ruang,
                          t.nama_tendik, t.nipy as nipy_tendik,
                          jut.qr_code_token');
        $this->db->from('jadwal_uts jut');
        $this->db->join('mapping_dosen_mk mdm', 'mdm.id = jut.id_mapping_dosen_mk', 'left');
        $this->db->join('mata_kuliah_aktif mka', 'mka.id = mdm.id_mata_kuliah_aktif', 'left');
        $this->db->join('kurikulum_detail kd', 'kd.id = mka.id_kurikulum_detail', 'left');
        $this->db->join('mata_kuliah mk', 'mk.id = kd.id_mata_kuliah', 'left');
        $this->db->join('dosen d', 'd.id = mdm.id_dosen', 'left');
        $this->db->join('ruang_kelas rk', 'rk.id = jut.id_ruang_kelas', 'left');
        $this->db->join('tendik t', 't.id = jut.id_tendik', 'left');
        $this->db->where('jut.kelas', $kelas);
        $this->db->where('jut.id_tahun_akademik', $id_tahun_akademik);
        $this->db->where_in('jut.id', $id_jadwal_uts_list);
        $this->db->order_by('jut.tanggal', 'ASC');
        $this->db->order_by('jut.jam_mulai', 'ASC');
        $this->db->order_by('jut.sesi', 'ASC');
        
        $query = $this->db->get();
        $results = $query->result();
        
        // Tambahkan sesi, pengawas, dan kehadiran untuk setiap jadwal
        foreach ($results as $result) {
            // Ambil sesi mahasiswa untuk jadwal ini
            $this->db->where('id_jadwal_uts', $result->id);
            $this->db->where('id_mahasiswa', $id_mahasiswa);
            $ms_data = $this->db->get('jadwal_uts_mahasiswa_sesi')->row();
            if ($ms_data) {
                $result->sesi = $ms_data->sesi;
            }
            
            // Ambil pengawas
            $result->pengawas = $this->Jadwal_uts_model->get_pengawas($result->id);
            
            // Ambil kehadiran, jika belum ada buat dengan status "tidak_hadir"
            $this->db->where('id_jadwal_uts', $result->id);
            $this->db->where('id_mahasiswa', $id_mahasiswa);
            $result->kehadiran = $this->db->get('jadwal_uts_kehadiran')->row();
            
            // Jika belum ada record kehadiran, buat dengan status "tidak_hadir"
            if (!$result->kehadiran) {
                $this->db->insert('jadwal_uts_kehadiran', array(
                    'id_jadwal_uts' => $result->id,
                    'id_mahasiswa' => $id_mahasiswa,
                    'status_kehadiran' => 'tidak_hadir',
                    'created_at' => date('Y-m-d H:i:s')
                ));
                // Reload kehadiran
                $this->db->where('id_jadwal_uts', $result->id);
                $this->db->where('id_mahasiswa', $id_mahasiswa);
                $result->kehadiran = $this->db->get('jadwal_uts_kehadiran')->row();
            }
        }
        
        return $results;
    }

    /**
     * Ambil jadwal UAS berdasarkan mahasiswa dan sesi
     */
    private function get_jadwal_uas_by_mahasiswa($id_mahasiswa, $kelas, $id_tahun_akademik) {
        // Ambil sesi mahasiswa dari tabel jadwal_uas_mahasiswa_sesi
        $this->db->select('jms.id_jadwal_uas, jms.sesi');
        $this->db->from('jadwal_uas_mahasiswa_sesi jms');
        $this->db->where('jms.id_mahasiswa', $id_mahasiswa);
        $mahasiswa_sesi = $this->db->get()->result();
        
        // Jika mahasiswa tidak ada di tabel sesi, ambil semua jadwal kelas (backward compatibility)
        if (empty($mahasiswa_sesi)) {
            $results = $this->Jadwal_uas_model->get_by_kelas($kelas, $id_tahun_akademik);
            // Tambahkan kehadiran untuk setiap jadwal (pengawas sudah diambil di get_by_kelas)
            foreach ($results as $result) {
                $this->db->where('id_jadwal_uas', $result->id);
                $this->db->where('id_mahasiswa', $id_mahasiswa);
                $result->kehadiran = $this->db->get('jadwal_uas_kehadiran')->row();
                
                // Jika belum ada record kehadiran, buat dengan status "tidak_hadir"
                if (!$result->kehadiran) {
                    $this->db->insert('jadwal_uas_kehadiran', array(
                        'id_jadwal_uas' => $result->id,
                        'id_mahasiswa' => $id_mahasiswa,
                        'status_kehadiran' => 'tidak_hadir',
                        'created_at' => date('Y-m-d H:i:s')
                    ));
                    // Reload kehadiran
                    $this->db->where('id_jadwal_uas', $result->id);
                    $this->db->where('id_mahasiswa', $id_mahasiswa);
                    $result->kehadiran = $this->db->get('jadwal_uas_kehadiran')->row();
                }
            }
            return $results;
        }
        
        // Ambil id_jadwal_uas dari hasil query
        $id_jadwal_uas_list = array();
        foreach ($mahasiswa_sesi as $ms) {
            $id_jadwal_uas_list[] = $ms->id_jadwal_uas;
        }
        
        if (empty($id_jadwal_uas_list)) {
            return array();
        }
        
        // Ambil jadwal UAS berdasarkan id_jadwal_uas dan sesi
        $this->db->select('jua.*, 
                          mk.kode_mk, mk.nama_mk, mk.sks,
                          d.nidn, d.nama_dosen,
                          rk.kode_ruang, rk.nama_ruang,
                          t.nama_tendik, t.nipy as nipy_tendik,
                          jua.qr_code_token');
        $this->db->from('jadwal_uas jua');
        $this->db->join('mapping_dosen_mk mdm', 'mdm.id = jua.id_mapping_dosen_mk', 'left');
        $this->db->join('mata_kuliah_aktif mka', 'mka.id = mdm.id_mata_kuliah_aktif', 'left');
        $this->db->join('kurikulum_detail kd', 'kd.id = mka.id_kurikulum_detail', 'left');
        $this->db->join('mata_kuliah mk', 'mk.id = kd.id_mata_kuliah', 'left');
        $this->db->join('dosen d', 'd.id = mdm.id_dosen', 'left');
        $this->db->join('ruang_kelas rk', 'rk.id = jua.id_ruang_kelas', 'left');
        $this->db->join('tendik t', 't.id = jua.id_tendik', 'left');
        $this->db->where('jua.kelas', $kelas);
        $this->db->where('jua.id_tahun_akademik', $id_tahun_akademik);
        $this->db->where_in('jua.id', $id_jadwal_uas_list);
        $this->db->order_by('jua.tanggal', 'ASC');
        $this->db->order_by('jua.jam_mulai', 'ASC');
        $this->db->order_by('jua.sesi', 'ASC');
        
        $query = $this->db->get();
        $results = $query->result();
        
        // Tambahkan sesi, pengawas, dan kehadiran untuk setiap jadwal
        foreach ($results as $result) {
            // Ambil sesi mahasiswa untuk jadwal ini
            $this->db->where('id_jadwal_uas', $result->id);
            $this->db->where('id_mahasiswa', $id_mahasiswa);
            $ms_data = $this->db->get('jadwal_uas_mahasiswa_sesi')->row();
            if ($ms_data) {
                $result->sesi = $ms_data->sesi;
            }
            
            // Ambil pengawas
            $result->pengawas = $this->Jadwal_uas_model->get_pengawas($result->id);
            
            // Ambil kehadiran, jika belum ada buat dengan status "tidak_hadir"
            $this->db->where('id_jadwal_uas', $result->id);
            $this->db->where('id_mahasiswa', $id_mahasiswa);
            $result->kehadiran = $this->db->get('jadwal_uas_kehadiran')->row();
            
            // Jika belum ada record kehadiran, buat dengan status "tidak_hadir"
            if (!$result->kehadiran) {
                $this->db->insert('jadwal_uas_kehadiran', array(
                    'id_jadwal_uas' => $result->id,
                    'id_mahasiswa' => $id_mahasiswa,
                    'status_kehadiran' => 'tidak_hadir',
                    'created_at' => date('Y-m-d H:i:s')
                ));
                // Reload kehadiran
                $this->db->where('id_jadwal_uas', $result->id);
                $this->db->where('id_mahasiswa', $id_mahasiswa);
                $result->kehadiran = $this->db->get('jadwal_uas_kehadiran')->row();
            }
        }
        
        return $results;
    }

    /**
     * Jadwal Kuliah
     */
    public function jadwal_kuliah() {
        $this->load_jadwal_by_type('kuliah');
    }

    /**
     * Jadwal UTS
     */
    public function jadwal_uts() {
        $this->load_jadwal_by_type('uts');
    }

    /**
     * Jadwal UAS
     */
    public function jadwal_uas() {
        $this->load_jadwal_by_type('uas');
    }

    /**
     * Load jadwal berdasarkan tipe
     */
    private function load_jadwal_by_type($type = 'kuliah') {
        $id_mahasiswa = $this->session->userdata('ref_id');
        $tahun_aktif = $this->Tahun_akademik_model->get_aktif();
        
        // Ambil data mahasiswa untuk mendapatkan kelas
        $mahasiswa = $this->Mahasiswa_model->get_by_id($id_mahasiswa);
        $kelas = $mahasiswa ? $mahasiswa->kelas : null;
        
        $jadwal_kuliah = array();
        $jadwal_uts = array();
        $jadwal_uas = array();
        $jadwal_skripsi = array();
        $jadwal_pkl = array();
        
        if ($tahun_aktif && $kelas) {
            if ($type == 'kuliah') {
                $jadwal_kuliah = $this->Jadwal_kuliah_model->get_by_kelas($kelas, $tahun_aktif->id);
            } elseif ($type == 'uts') {
                $jadwal_uts = $this->get_jadwal_uts_by_mahasiswa($id_mahasiswa, $kelas, $tahun_aktif->id);
            } elseif ($type == 'uas') {
                $jadwal_uas = $this->get_jadwal_uas_by_mahasiswa($id_mahasiswa, $kelas, $tahun_aktif->id);
            }
        }
        
        $catatan_revisi = array();
        
        // Group jadwal kuliah by hari
        $jadwal_by_hari = array();
        $hari_list = array('senin', 'selasa', 'rabu', 'kamis', 'jumat', 'sabtu', 'minggu');
        foreach ($hari_list as $hari) {
            $jadwal_by_hari[$hari] = array();
        }
        
        foreach ($jadwal_kuliah as $jk) {
            $hari = strtolower($jk->hari);
            if (isset($jadwal_by_hari[$hari])) {
                $jadwal_by_hari[$hari][] = $jk;
            }
        }
        
        // Cek apakah mahasiswa terdaftar PKL atau skripsi
        $is_mahasiswa_pkl = false;
        $is_mahasiswa_skripsi = false;
        
        if ($id_mahasiswa && $tahun_aktif) {
            $pkl = $this->Mahasiswa_pkl_model->get_by_mahasiswa($id_mahasiswa, $tahun_aktif->id);
            $is_mahasiswa_pkl = !empty($pkl);
            
            $skripsi = $this->Skripsi_model->get_by_mahasiswa($id_mahasiswa, $tahun_aktif->id);
            $is_mahasiswa_skripsi = !empty($skripsi);
        }
        
        $page_titles = array(
            'kuliah' => 'Jadwal Kuliah',
            'uts' => 'Jadwal UTS',
            'uas' => 'Jadwal UAS'
        );
        
        $data = array(
            'title' => $page_titles[$type] ?? 'Jadwal',
            'page_title' => $page_titles[$type] ?? 'Jadwal',
            'breadcrumb' => array(array('label' => $page_titles[$type] ?? 'Jadwal', 'active' => true)),
            'sidebar_menu' => generate_sidebar_menu('mahasiswa'),
            'tahun_aktif' => $tahun_aktif,
            'kelas' => $kelas,
            'mahasiswa' => $mahasiswa,
            'jadwal_kuliah' => $jadwal_kuliah,
            'jadwal_by_hari' => $jadwal_by_hari,
            'jadwal_uts' => $jadwal_uts,
            'jadwal_uas' => $jadwal_uas,
            'jadwal_skripsi' => $jadwal_skripsi,
            'jadwal_pkl' => $jadwal_pkl,
            'catatan_revisi' => $catatan_revisi,
            'is_mahasiswa_pkl' => $is_mahasiswa_pkl,
            'is_mahasiswa_skripsi' => $is_mahasiswa_skripsi,
            'active_tab' => $type // Parameter untuk menentukan tab aktif
        );
        
        $data['content'] = $this->load->view('mahasiswa/jadwal', $data, true);
        $this->load->view('layouts/main', $data);
    }
}




