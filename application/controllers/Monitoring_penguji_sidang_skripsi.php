<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Monitoring Penguji Sidang Skripsi Controller
 * Menampilkan jadwal sidang Skripsi berdasarkan penguji
 */
class Monitoring_penguji_sidang_skripsi extends MY_Controller {

    public function __construct() {
        parent::__construct();
        $this->check_role('sekprodi');
        $this->load->model('Jadwal_skripsi_model');
        $this->load->model('Dosen_model');
        $this->load->model('Tahun_akademik_model');
    }

    /**
     * Halaman monitoring penguji sidang Skripsi
     */
    public function index() {
        $tahun_aktif = $this->Tahun_akademik_model->get_aktif();
        $id_tahun_akademik = $tahun_aktif ? $tahun_aktif->id : null;

        // Filter
        $filter_dosen = $this->input->get('dosen');
        $filter_tanggal_mulai = $this->input->get('tanggal_mulai');
        $filter_tanggal_selesai = $this->input->get('tanggal_selesai');

        // Ambil semua dosen untuk dropdown
        $dosen_list = $this->Dosen_model->get_all_active();

        // Ambil data jadwal sidang Skripsi berdasarkan penguji
        $data_penguji = array();

        if ($id_tahun_akademik) {
            $jadwal_list = $this->get_jadwal_skripsi_by_penguji($id_tahun_akademik, $filter_dosen, $filter_tanggal_mulai, $filter_tanggal_selesai);
            
            foreach ($jadwal_list as $jadwal) {
                // Tambahkan untuk setiap penguji (ketua, anggota 1, anggota 2)
                $penguji_list = array(
                    array(
                        'id' => $jadwal->id_ketua_penguji,
                        'nama' => $jadwal->ketua_penguji,
                        'nidn' => $jadwal->ketua_penguji_nidn,
                        'role' => 'Ketua Penguji'
                    ),
                    array(
                        'id' => $jadwal->id_anggota_penguji_1,
                        'nama' => $jadwal->anggota_penguji_1,
                        'nidn' => $jadwal->anggota_penguji_1_nidn,
                        'role' => 'Anggota Penguji 1'
                    ),
                    array(
                        'id' => $jadwal->id_anggota_penguji_2,
                        'nama' => $jadwal->anggota_penguji_2,
                        'nidn' => $jadwal->anggota_penguji_2_nidn,
                        'role' => 'Anggota Penguji 2'
                    )
                );

                foreach ($penguji_list as $penguji) {
                    if ($penguji['id']) {
                        $data_penguji[] = array(
                            'dosen' => $penguji['nama'],
                            'id_dosen' => $penguji['id'],
                            'role' => $penguji['role'],
                            'tanggal' => $jadwal->tanggal_ujian,
                            'hari_tanggal' => date('d/m/Y', strtotime($jadwal->tanggal_ujian)) . ' (' . $this->get_hari_indonesia(date('N', strtotime($jadwal->tanggal_ujian))) . ')',
                            'jam_mulai' => $jadwal->jam_mulai,
                            'jam_selesai' => $jadwal->jam_selesai,
                            'ruangan' => $jadwal->ruangan,
                            'mahasiswa' => $jadwal->nim . ' - ' . $jadwal->nama_mahasiswa,
                            'kelas' => $jadwal->kelas,
                            'pembimbing_1' => isset($jadwal->pembimbing_1_nama) && $jadwal->pembimbing_1_nama ? $jadwal->pembimbing_1_nama : '-',
                            'pembimbing_2' => isset($jadwal->pembimbing_2_nama) && $jadwal->pembimbing_2_nama ? $jadwal->pembimbing_2_nama : '-',
                            'status' => $jadwal->status
                        );
                    }
                }
            }

            // Detect bentrok penguji
            for ($i = 0; $i < count($data_penguji); $i++) {
                $data_penguji[$i]['bentrok'] = false;
                for ($j = $i + 1; $j < count($data_penguji); $j++) {
                    // Cek apakah penguji sama
                    if ($data_penguji[$i]['id_dosen'] == $data_penguji[$j]['id_dosen'] && 
                        $data_penguji[$i]['id_dosen'] != null) {
                        
                        // Cek apakah tanggal sama
                        if ($data_penguji[$i]['tanggal'] == $data_penguji[$j]['tanggal']) {
                            // Cek overlap waktu
                            $jam_mulai_i = strtotime($data_penguji[$i]['jam_mulai']);
                            $jam_selesai_i = strtotime($data_penguji[$i]['jam_selesai']);
                            $jam_mulai_j = strtotime($data_penguji[$j]['jam_mulai']);
                            $jam_selesai_j = strtotime($data_penguji[$j]['jam_selesai']);
                            
                            // Overlap jika: (mulai_i < selesai_j) AND (selesai_i > mulai_j)
                            if ($jam_mulai_i < $jam_selesai_j && $jam_selesai_i > $jam_mulai_j) {
                                $data_penguji[$i]['bentrok'] = true;
                                $data_penguji[$j]['bentrok'] = true;
                            }
                        }
                    }
                }
            }

            // Sort by dosen, then by tanggal, then by jam_mulai
            usort($data_penguji, function($a, $b) {
                // Sort by dosen first
                $dosen_cmp = strcmp($a['dosen'], $b['dosen']);
                if ($dosen_cmp !== 0) {
                    return $dosen_cmp;
                }
                
                // Then by tanggal
                $date_cmp = strcmp($a['tanggal'], $b['tanggal']);
                if ($date_cmp !== 0) {
                    return $date_cmp;
                }
                
                // Finally by jam_mulai
                return strcmp($a['jam_mulai'], $b['jam_mulai']);
            });
        }

        $data = array(
            'title' => 'Monitoring Penguji Sidang Skripsi',
            'page_title' => 'Monitoring Penguji Sidang Skripsi',
            'breadcrumb' => array(
                array('label' => 'Skripsi', 'url' => '#'),
                array('label' => 'Monitoring Penguji Sidang', 'active' => true)
            ),
            'sidebar_menu' => generate_sidebar_menu('sekprodi'),
            'tahun_aktif' => $tahun_aktif,
            'dosen_list' => $dosen_list,
            'data_penguji' => $data_penguji,
            'filter_dosen' => $filter_dosen,
            'filter_tanggal_mulai' => $filter_tanggal_mulai,
            'filter_tanggal_selesai' => $filter_tanggal_selesai
        );

        $data['content'] = $this->load->view('monitoring_penguji_sidang_skripsi/index', $data, true);
        $this->load->view('layouts/main', $data);
    }

    /**
     * Ambil jadwal Skripsi berdasarkan penguji
     */
    private function get_jadwal_skripsi_by_penguji($id_tahun_akademik, $filter_dosen = null, $filter_tanggal_mulai = null, $filter_tanggal_selesai = null) {
        $this->db->select('js.*, 
                          ms.id_mahasiswa, ms.judul_skripsi, ms.id_pembimbing_1, ms.id_pembimbing_2,
                          m.nim, m.nama_mahasiswa, m.kelas,
                          d1.nama_dosen as ketua_penguji, d1.nidn as ketua_penguji_nidn,
                          d2.nama_dosen as anggota_penguji_1, d2.nidn as anggota_penguji_1_nidn,
                          d3.nama_dosen as anggota_penguji_2, d3.nidn as anggota_penguji_2_nidn,
                          d4.nama_dosen as pembimbing_1_nama,
                          d5.nama_dosen as pembimbing_2_nama');
        $this->db->from('jadwal_skripsi js');
        $this->db->join('mahasiswa_skripsi ms', 'ms.id = js.id_mahasiswa_skripsi', 'left');
        $this->db->join('mahasiswa m', 'm.id = ms.id_mahasiswa', 'left');
        $this->db->join('dosen d1', 'd1.id = js.id_ketua_penguji', 'left');
        $this->db->join('dosen d2', 'd2.id = js.id_anggota_penguji_1', 'left');
        $this->db->join('dosen d3', 'd3.id = js.id_anggota_penguji_2', 'left');
        $this->db->join('dosen d4', 'd4.id = ms.id_pembimbing_1', 'left');
        $this->db->join('dosen d5', 'd5.id = ms.id_pembimbing_2', 'left');
        $this->db->where('js.id_tahun_akademik', $id_tahun_akademik);
        $this->db->where('js.status !=', 'batal');
        
        if ($filter_dosen) {
            $this->db->group_start();
            $this->db->where('js.id_ketua_penguji', $filter_dosen);
            $this->db->or_where('js.id_anggota_penguji_1', $filter_dosen);
            $this->db->or_where('js.id_anggota_penguji_2', $filter_dosen);
            $this->db->group_end();
        }
        if ($filter_tanggal_mulai) {
            $this->db->where('js.tanggal_ujian >=', $filter_tanggal_mulai);
        }
        if ($filter_tanggal_selesai) {
            $this->db->where('js.tanggal_ujian <=', $filter_tanggal_selesai);
        }
        
        $this->db->order_by('js.tanggal_ujian', 'ASC');
        $this->db->order_by('js.jam_mulai', 'ASC');
        
        return $this->db->get()->result();
    }

    /**
     * Get hari dalam bahasa Indonesia
     */
    private function get_hari_indonesia($day_number) {
        $hari = array(
            1 => 'Senin',
            2 => 'Selasa',
            3 => 'Rabu',
            4 => 'Kamis',
            5 => 'Jumat',
            6 => 'Sabtu',
            7 => 'Minggu'
        );
        return isset($hari[$day_number]) ? $hari[$day_number] : '';
    }
}

