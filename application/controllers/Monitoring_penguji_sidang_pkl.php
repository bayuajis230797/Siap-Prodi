<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Monitoring Penguji Sidang PKL Controller
 * Menampilkan jadwal sidang PKL berdasarkan penguji
 */
class Monitoring_penguji_sidang_pkl extends MY_Controller {

    public function __construct() {
        parent::__construct();
        $this->check_role('sekprodi');
        $this->load->model('Jadwal_pkl_model');
        $this->load->model('Dosen_model');
        $this->load->model('Tahun_akademik_model');
    }

    /**
     * Halaman monitoring penguji sidang PKL
     */
    public function index() {
        $tahun_aktif = $this->Tahun_akademik_model->get_aktif();
        $id_tahun_akademik = $tahun_aktif ? $tahun_aktif->id : null;

        // Filter
        $filter_dosen = $this->input->get('dosen');

        // Ambil semua dosen untuk dropdown
        $dosen_list = $this->Dosen_model->get_all_active();

        // Ambil data jadwal sidang PKL berdasarkan penguji
        $data_penguji = array();

        if ($id_tahun_akademik) {
            $jadwal_list = $this->get_jadwal_pkl_by_penguji($id_tahun_akademik, $filter_dosen);
            
            foreach ($jadwal_list as $jadwal) {
                $data_penguji[] = array(
                    'dosen' => $jadwal->penguji_nama,
                    'nidn' => $jadwal->penguji_nidn,
                    'id_dosen' => $jadwal->id_penguji,
                    'tanggal' => $jadwal->tanggal,
                    'hari_tanggal' => date('d/m/Y', strtotime($jadwal->tanggal)) . ' (' . $this->get_hari_indonesia(date('N', strtotime($jadwal->tanggal))) . ')',
                    'jam_mulai' => $jadwal->jam_mulai,
                    'jam_selesai' => $jadwal->jam_selesai,
                    'ruangan' => $jadwal->ruangan,
                    'mahasiswa' => $jadwal->nim . ' - ' . $jadwal->nama_mahasiswa,
                    'kelas' => $jadwal->kelas,
                    'judul' => isset($jadwal->judul_pkl) ? $jadwal->judul_pkl : '-',
                    'status' => $jadwal->status
                );
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
            'title' => 'Monitoring Penguji Sidang PKL',
            'page_title' => 'Monitoring Penguji Sidang PKL',
            'breadcrumb' => array(
                array('label' => 'PKL', 'url' => '#'),
                array('label' => 'Monitoring Penguji Sidang', 'active' => true)
            ),
            'sidebar_menu' => generate_sidebar_menu('sekprodi'),
            'tahun_aktif' => $tahun_aktif,
            'dosen_list' => $dosen_list,
            'data_penguji' => $data_penguji,
            'filter_dosen' => $filter_dosen
        );

        $data['content'] = $this->load->view('monitoring_penguji_sidang_pkl/index', $data, true);
        $this->load->view('layouts/main', $data);
    }

    /**
     * Ambil jadwal PKL berdasarkan penguji
     */
    private function get_jadwal_pkl_by_penguji($id_tahun_akademik, $filter_dosen = null) {
        $this->db->select('jp.*, 
                          m.nim, m.nama_mahasiswa, m.kelas,
                          d_penguji.nama_dosen as penguji_nama, d_penguji.nidn as penguji_nidn,
                          pp.judul_pkl');
        $this->db->from('jadwal_pkl jp');
        $this->db->join('mahasiswa m', 'm.id = jp.id_mahasiswa', 'left');
        $this->db->join('dosen d_penguji', 'd_penguji.id = jp.id_penguji', 'left');
        $this->db->join('pkl_pengajuan pp', 'pp.id_mahasiswa = jp.id_mahasiswa AND pp.id_tahun_akademik = jp.id_tahun_akademik', 'left');
        $this->db->where('jp.id_tahun_akademik', $id_tahun_akademik);
        $this->db->where('jp.status !=', 'batal');
        $this->db->where('jp.id_penguji IS NOT NULL');
        
        if ($filter_dosen) {
            $this->db->where('jp.id_penguji', $filter_dosen);
        }
        
        $this->db->order_by('d_penguji.nama_dosen', 'ASC');
        $this->db->order_by('jp.tanggal', 'ASC');
        $this->db->order_by('jp.jam_mulai', 'ASC');
        
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

