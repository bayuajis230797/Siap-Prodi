<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Jadwal Pengawas Controller
 * Untuk dosen dan tendik melihat jadwal UTS/UAS dimana mereka menjadi pengawas
 */
class Jadwal_pengawas extends MY_Controller {

    public function __construct() {
        parent::__construct();
        $this->check_role(array('dosen', 'tendik'));
        $this->load->model('Jadwal_uts_model');
        $this->load->model('Jadwal_uas_model');
        $this->load->model('Tahun_akademik_model');
        $this->load->model('Mahasiswa_model');
    }

    /**
     * List jadwal UTS dimana user menjadi pengawas
     */
    public function uts() {
        $role = $this->session->userdata('role');
        $ref_id = $this->session->userdata('ref_id');
        
        if (!$ref_id) {
            $this->session->set_flashdata('error', 'Data pengguna tidak ditemukan.');
            redirect('Dashboard');
        }

        // Ambil tahun akademik aktif
        $tahun_aktif = $this->Tahun_akademik_model->get_aktif();
        $id_tahun_akademik = $tahun_aktif ? $tahun_aktif->id : null;

        // Ambil filter dari GET
        $filter_tanggal = $this->input->get('tanggal');
        $filter_kelas = $this->input->get('kelas');

        // Ambil jadwal UTS dimana user menjadi pengawas
        $this->db->select('jut.*, 
                          mk.kode_mk, mk.nama_mk, mk.sks,
                          d.nidn, d.nama_dosen,
                          rk.kode_ruang, rk.nama_ruang,
                          ta.tahun_akademik, ta.semester,
                          t.nama_tendik, t.nipy as nipy_tendik');
        $this->db->from('jadwal_uts jut');
        $this->db->join('mapping_dosen_mk mdm', 'mdm.id = jut.id_mapping_dosen_mk', 'left');
        $this->db->join('mata_kuliah_aktif mka', 'mka.id = mdm.id_mata_kuliah_aktif', 'left');
        $this->db->join('kurikulum_detail kd', 'kd.id = mka.id_kurikulum_detail', 'left');
        $this->db->join('mata_kuliah mk', 'mk.id = kd.id_mata_kuliah', 'left');
        $this->db->join('dosen d', 'd.id = mdm.id_dosen', 'left');
        $this->db->join('ruang_kelas rk', 'rk.id = jut.id_ruang_kelas', 'left');
        $this->db->join('tahun_akademik ta', 'ta.id = jut.id_tahun_akademik', 'left');
        $this->db->join('tendik t', 't.id = jut.id_tendik', 'left');
        $this->db->join('jadwal_uts_pengawas jup', 'jup.id_jadwal_uts = jut.id', 'inner');
        
        if ($role == 'dosen') {
            $this->db->where('jup.id_dosen', $ref_id);
        } elseif ($role == 'tendik') {
            $this->db->where('jup.id_tendik', $ref_id);
        }

        if ($id_tahun_akademik) {
            $this->db->where('jut.id_tahun_akademik', $id_tahun_akademik);
        }

        if ($filter_tanggal) {
            $this->db->where('jut.tanggal', $filter_tanggal);
        }

        if ($filter_kelas) {
            $this->db->where('jut.kelas', $filter_kelas);
        }

        $this->db->order_by('jut.tanggal', 'ASC');
        $this->db->order_by('jut.jam_mulai', 'ASC');
        $this->db->order_by('jut.kelas', 'ASC');
        
        $jadwal_uts = $this->db->get()->result();
        

        // Ambil daftar kelas untuk filter
        $this->db->distinct();
        $this->db->select('jut.kelas');
        $this->db->from('jadwal_uts jut');
        $this->db->join('jadwal_uts_pengawas jup', 'jup.id_jadwal_uts = jut.id', 'inner');
        if ($role == 'dosen') {
            $this->db->where('jup.id_dosen', $ref_id);
        } elseif ($role == 'tendik') {
            $this->db->where('jup.id_tendik', $ref_id);
        }
        if ($id_tahun_akademik) {
            $this->db->where('jut.id_tahun_akademik', $id_tahun_akademik);
        }
        $this->db->where('jut.kelas IS NOT NULL');
        $this->db->where('jut.kelas !=', '');
        $this->db->order_by('jut.kelas', 'ASC');
        $kelas_list = $this->db->get()->result();
        
        $kelas_options = array();
        if (!empty($kelas_list)) {
            foreach ($kelas_list as $kls) {
                $kelas_options[$kls->kelas] = $kls->kelas;
            }
        }

        $data = array(
            'title' => 'Jadwal Pengawas UTS',
            'page_title' => 'Jadwal Pengawas UTS',
            'jadwal_uts' => $jadwal_uts,
            'tahun_aktif' => $tahun_aktif,
            'filter_tanggal' => $filter_tanggal,
            'filter_kelas' => $filter_kelas,
            'kelas_options' => $kelas_options,
            'role' => $role
        );

        $data['sidebar_menu'] = generate_sidebar_menu($role);
        $data['content'] = $this->load->view('dosen/jadwal_pengawas_uts', $data, true);
        $this->load->view('layouts/main', $data);
    }

    /**
     * List jadwal UAS dimana user menjadi pengawas
     */
    public function uas() {
        $role = $this->session->userdata('role');
        $ref_id = $this->session->userdata('ref_id');
        
        if (!$ref_id) {
            $this->session->set_flashdata('error', 'Data pengguna tidak ditemukan.');
            redirect('Dashboard');
        }

        // Ambil tahun akademik aktif
        $tahun_aktif = $this->Tahun_akademik_model->get_aktif();
        $id_tahun_akademik = $tahun_aktif ? $tahun_aktif->id : null;

        // Ambil filter dari GET
        $filter_tanggal = $this->input->get('tanggal');
        $filter_kelas = $this->input->get('kelas');

        // Ambil jadwal UAS dimana user menjadi pengawas
        $this->db->select('jua.*, 
                          mk.kode_mk, mk.nama_mk, mk.sks,
                          d.nidn, d.nama_dosen,
                          rk.kode_ruang, rk.nama_ruang,
                          ta.tahun_akademik, ta.semester,
                          t.nama_tendik, t.nipy as nipy_tendik');
        $this->db->from('jadwal_uas jua');
        $this->db->join('mapping_dosen_mk mdm', 'mdm.id = jua.id_mapping_dosen_mk', 'left');
        $this->db->join('mata_kuliah_aktif mka', 'mka.id = mdm.id_mata_kuliah_aktif', 'left');
        $this->db->join('kurikulum_detail kd', 'kd.id = mka.id_kurikulum_detail', 'left');
        $this->db->join('mata_kuliah mk', 'mk.id = kd.id_mata_kuliah', 'left');
        $this->db->join('dosen d', 'd.id = mdm.id_dosen', 'left');
        $this->db->join('ruang_kelas rk', 'rk.id = jua.id_ruang_kelas', 'left');
        $this->db->join('tahun_akademik ta', 'ta.id = jua.id_tahun_akademik', 'left');
        $this->db->join('tendik t', 't.id = jua.id_tendik', 'left');
        $this->db->join('jadwal_uas_pengawas jup', 'jup.id_jadwal_uas = jua.id', 'inner');
        
        if ($role == 'dosen') {
            $this->db->where('jup.id_dosen', $ref_id);
        } elseif ($role == 'tendik') {
            $this->db->where('jup.id_tendik', $ref_id);
        }

        if ($id_tahun_akademik) {
            $this->db->where('jua.id_tahun_akademik', $id_tahun_akademik);
        }

        if ($filter_tanggal) {
            $this->db->where('jua.tanggal', $filter_tanggal);
        }

        if ($filter_kelas) {
            $this->db->where('jua.kelas', $filter_kelas);
        }

        $this->db->order_by('jua.tanggal', 'ASC');
        $this->db->order_by('jua.jam_mulai', 'ASC');
        $this->db->order_by('jua.kelas', 'ASC');
        
        $jadwal_uas = $this->db->get()->result();
        

        // Ambil daftar kelas untuk filter
        $this->db->distinct();
        $this->db->select('jua.kelas');
        $this->db->from('jadwal_uas jua');
        $this->db->join('jadwal_uas_pengawas jup', 'jup.id_jadwal_uas = jua.id', 'inner');
        if ($role == 'dosen') {
            $this->db->where('jup.id_dosen', $ref_id);
        } elseif ($role == 'tendik') {
            $this->db->where('jup.id_tendik', $ref_id);
        }
        if ($id_tahun_akademik) {
            $this->db->where('jua.id_tahun_akademik', $id_tahun_akademik);
        }
        $this->db->where('jua.kelas IS NOT NULL');
        $this->db->where('jua.kelas !=', '');
        $this->db->order_by('jua.kelas', 'ASC');
        $kelas_list = $this->db->get()->result();
        
        $kelas_options = array();
        if (!empty($kelas_list)) {
            foreach ($kelas_list as $kls) {
                $kelas_options[$kls->kelas] = $kls->kelas;
            }
        }

        $data = array(
            'title' => 'Jadwal Pengawas UAS',
            'page_title' => 'Jadwal Pengawas UAS',
            'jadwal_uas' => $jadwal_uas,
            'tahun_aktif' => $tahun_aktif,
            'filter_tanggal' => $filter_tanggal,
            'filter_kelas' => $filter_kelas,
            'kelas_options' => $kelas_options,
            'role' => $role
        );

        $data['sidebar_menu'] = generate_sidebar_menu($role);
        $data['content'] = $this->load->view('dosen/jadwal_pengawas_uas', $data, true);
        $this->load->view('layouts/main', $data);
    }

    /**
     * View status absensi mahasiswa untuk jadwal UTS
     */
    public function view_absensi_uts($id_jadwal) {
        $role = $this->session->userdata('role');
        $ref_id = $this->session->userdata('ref_id');
        
        if (!$ref_id) {
            $this->session->set_flashdata('error', 'Data pengguna tidak ditemukan.');
            redirect('Dashboard');
        }

        // Verifikasi bahwa user adalah pengawas untuk jadwal ini
        $this->db->where('id_jadwal_uts', $id_jadwal);
        if ($role == 'dosen') {
            $this->db->where('id_dosen', $ref_id);
        } elseif ($role == 'tendik') {
            $this->db->where('id_tendik', $ref_id);
        }
        $is_pengawas = $this->db->get('jadwal_uts_pengawas')->num_rows() > 0;
        
        if (!$is_pengawas) {
            $this->session->set_flashdata('error', 'Anda tidak memiliki akses untuk melihat jadwal ini.');
            redirect('Jadwal_pengawas/uts');
        }

        // Ambil data jadwal
        $jadwal = $this->Jadwal_uts_model->get_by_id($id_jadwal);
        if (!$jadwal) {
            $this->session->set_flashdata('error', 'Jadwal tidak ditemukan.');
            redirect('Jadwal_pengawas/uts');
        }

        // Ambil semua mahasiswa yang terdaftar di jadwal ini (dari tabel sesi atau kelas)
        $this->db->select('m.id, m.nim, m.nama_mahasiswa,
                          COALESCE(juka.status_kehadiran, "tidak_hadir") as status_kehadiran,
                          juka.waktu_absen, juka.created_at');
        $this->db->from('jadwal_uts_mahasiswa_sesi jms');
        $this->db->join('mahasiswa m', 'm.id = jms.id_mahasiswa', 'inner');
        $this->db->join('jadwal_uts_kehadiran juka', 'juka.id_jadwal_uts = jms.id_jadwal_uts AND juka.id_mahasiswa = m.id', 'left');
        $this->db->where('jms.id_jadwal_uts', $id_jadwal);
        $this->db->order_by('m.nim', 'ASC');
        $mahasiswa_list = $this->db->get()->result();
        
        // Jika tidak ada di tabel sesi, ambil dari kelas (backward compatibility)
        if (empty($mahasiswa_list)) {
            $this->db->select('m.id, m.nim, m.nama_mahasiswa,
                              COALESCE(juka.status_kehadiran, "tidak_hadir") as status_kehadiran,
                              juka.waktu_absen, juka.created_at');
            $this->db->from('mahasiswa m');
            $this->db->join('jadwal_uts_kehadiran juka', 'juka.id_jadwal_uts = ' . $id_jadwal . ' AND juka.id_mahasiswa = m.id', 'left');
            $this->db->where('m.kelas', $jadwal->kelas);
            $this->db->where('m.status', 'aktif');
            $this->db->order_by('m.nim', 'ASC');
            $mahasiswa_list = $this->db->get()->result();
        }

        // Hitung statistik
        $total_mahasiswa = count($mahasiswa_list);
        $hadir = 0;
        $tidak_hadir = 0;
        foreach ($mahasiswa_list as $mhs) {
            if ($mhs->status_kehadiran == 'hadir') {
                $hadir++;
            } else {
                $tidak_hadir++;
            }
        }

        $data = array(
            'title' => 'Status Absensi Mahasiswa - UTS',
            'page_title' => 'Status Absensi Mahasiswa',
            'jadwal' => $jadwal,
            'mahasiswa_list' => $mahasiswa_list,
            'total_mahasiswa' => $total_mahasiswa,
            'hadir' => $hadir,
            'tidak_hadir' => $tidak_hadir,
            'jenis_ujian' => 'UTS',
            'role' => $role
        );

        $data['sidebar_menu'] = generate_sidebar_menu($role);
        $data['content'] = $this->load->view('dosen/view_absensi_ujian', $data, true);
        $this->load->view('layouts/main', $data);
    }

    /**
     * View status absensi mahasiswa untuk jadwal UAS
     */
    public function view_absensi_uas($id_jadwal) {
        $role = $this->session->userdata('role');
        $ref_id = $this->session->userdata('ref_id');
        
        if (!$ref_id) {
            $this->session->set_flashdata('error', 'Data pengguna tidak ditemukan.');
            redirect('Dashboard');
        }

        // Verifikasi bahwa user adalah pengawas untuk jadwal ini
        $this->db->where('id_jadwal_uas', $id_jadwal);
        if ($role == 'dosen') {
            $this->db->where('id_dosen', $ref_id);
        } elseif ($role == 'tendik') {
            $this->db->where('id_tendik', $ref_id);
        }
        $is_pengawas = $this->db->get('jadwal_uas_pengawas')->num_rows() > 0;
        
        if (!$is_pengawas) {
            $this->session->set_flashdata('error', 'Anda tidak memiliki akses untuk melihat jadwal ini.');
            redirect('Jadwal_pengawas/uas');
        }

        // Ambil data jadwal
        $jadwal = $this->Jadwal_uas_model->get_by_id($id_jadwal);
        if (!$jadwal) {
            $this->session->set_flashdata('error', 'Jadwal tidak ditemukan.');
            redirect('Jadwal_pengawas/uas');
        }

        // Ambil semua mahasiswa yang terdaftar di jadwal ini (dari tabel sesi atau kelas)
        $this->db->select('m.id, m.nim, m.nama_mahasiswa,
                          COALESCE(juaka.status_kehadiran, "tidak_hadir") as status_kehadiran,
                          juaka.waktu_absen, juaka.created_at');
        $this->db->from('jadwal_uas_mahasiswa_sesi jms');
        $this->db->join('mahasiswa m', 'm.id = jms.id_mahasiswa', 'inner');
        $this->db->join('jadwal_uas_kehadiran juaka', 'juaka.id_jadwal_uas = jms.id_jadwal_uas AND juaka.id_mahasiswa = m.id', 'left');
        $this->db->where('jms.id_jadwal_uas', $id_jadwal);
        $this->db->order_by('m.nim', 'ASC');
        $mahasiswa_list = $this->db->get()->result();
        
        // Jika tidak ada di tabel sesi, ambil dari kelas (backward compatibility)
        if (empty($mahasiswa_list)) {
            $this->db->select('m.id, m.nim, m.nama_mahasiswa,
                              COALESCE(juaka.status_kehadiran, "tidak_hadir") as status_kehadiran,
                              juaka.waktu_absen, juaka.created_at');
            $this->db->from('mahasiswa m');
            $this->db->join('jadwal_uas_kehadiran juaka', 'juaka.id_jadwal_uas = ' . $id_jadwal . ' AND juaka.id_mahasiswa = m.id', 'left');
            $this->db->where('m.kelas', $jadwal->kelas);
            $this->db->where('m.status', 'aktif');
            $this->db->order_by('m.nim', 'ASC');
            $mahasiswa_list = $this->db->get()->result();
        }

        // Hitung statistik
        $total_mahasiswa = count($mahasiswa_list);
        $hadir = 0;
        $tidak_hadir = 0;
        foreach ($mahasiswa_list as $mhs) {
            if ($mhs->status_kehadiran == 'hadir') {
                $hadir++;
            } else {
                $tidak_hadir++;
            }
        }

        $data = array(
            'title' => 'Status Absensi Mahasiswa - UAS',
            'page_title' => 'Status Absensi Mahasiswa',
            'jadwal' => $jadwal,
            'mahasiswa_list' => $mahasiswa_list,
            'total_mahasiswa' => $total_mahasiswa,
            'hadir' => $hadir,
            'tidak_hadir' => $tidak_hadir,
            'jenis_ujian' => 'UAS',
            'role' => $role
        );

        $data['sidebar_menu'] = generate_sidebar_menu($role);
        $data['content'] = $this->load->view('dosen/view_absensi_ujian', $data, true);
        $this->load->view('layouts/main', $data);
    }
}

