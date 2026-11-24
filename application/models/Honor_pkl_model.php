<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Honor PKL Model
 * Mengelola perhitungan dan data honor PKL (pembimbing dan penguji)
 */
class Honor_pkl_model extends CI_Model {

    public function __construct() {
        parent::__construct();
        $this->load->database();
        $this->load->model('Honor_setup_model');
    }

    /**
     * Generate honor PKL untuk semua pembimbing dan penguji
     */
    public function generate_honor($id_tahun_akademik) {
        // Hapus honor lama untuk tahun akademik ini
        $this->db->where('id_tahun_akademik', $id_tahun_akademik);
        $this->db->delete('honor_pkl');
        
        $honor_generated = 0;
        
        // 1. Generate honor pembimbing PKL
        $honor_generated += $this->generate_honor_pembimbing($id_tahun_akademik);
        
        // 2. Generate honor penguji PKL
        $honor_generated += $this->generate_honor_penguji($id_tahun_akademik);
        
        return $honor_generated;
    }

    /**
     * Generate honor pembimbing PKL
     * Honor dihitung per jumlah mahasiswa yang dibimbing
     */
    private function generate_honor_pembimbing($id_tahun_akademik) {
        // Ambil semua dosen pembimbing PKL beserta jumlah mahasiswa yang dibimbing
        $this->db->select('pp.id_pembimbing, d.nama_dosen, d.nidn, COUNT(DISTINCT pp.id_mahasiswa) as jumlah_mahasiswa');
        $this->db->from('pkl_pengajuan pp');
        $this->db->join('dosen d', 'd.id = pp.id_pembimbing', 'inner');
        $this->db->where('pp.id_tahun_akademik', $id_tahun_akademik);
        $this->db->where('pp.id_pembimbing IS NOT NULL');
        $this->db->where('pp.status', 'disetujui'); // Hanya yang sudah disetujui sekprodi
        $this->db->group_by('pp.id_pembimbing');
        
        $pembimbing_list = $this->db->get()->result();
        
        $besaran_per_mahasiswa = $this->Honor_setup_model->get_besaran('pembimbing_pkl', $id_tahun_akademik);
        
        $count = 0;
        foreach ($pembimbing_list as $pembimbing) {
            if ($pembimbing->id_pembimbing && $pembimbing->jumlah_mahasiswa > 0 && $besaran_per_mahasiswa > 0) {
                $total_honor = $pembimbing->jumlah_mahasiswa * $besaran_per_mahasiswa;
                
                $data = array(
                    'id_dosen' => $pembimbing->id_pembimbing,
                    'id_tahun_akademik' => $id_tahun_akademik,
                    'jenis_honor' => 'pembimbing',
                    'jumlah_item' => $pembimbing->jumlah_mahasiswa,
                    'besaran_per_item' => $besaran_per_mahasiswa,
                    'total_honor' => $total_honor,
                    'keterangan' => 'Honor pembimbing PKL untuk ' . $pembimbing->jumlah_mahasiswa . ' mahasiswa'
                );
                
                $this->db->insert('honor_pkl', $data);
                $count++;
            }
        }
        
        return $count;
    }

    /**
     * Generate honor penguji PKL
     * Honor dihitung per jumlah ujian yang diikuti
     */
    private function generate_honor_penguji($id_tahun_akademik) {
        // Ambil semua penguji PKL dengan menghitung jumlah DISTINCT jadwal
        // Menggunakan UNION untuk menggabungkan dari pkl_penguji dan jadwal_pkl.id_penguji
        // kemudian menghitung DISTINCT jadwal per dosen
        $this->db->select('pp.id_dosen, d.nama_dosen, d.nidn, pp.id_jadwal_pkl');
        $this->db->from('pkl_penguji pp');
        $this->db->join('jadwal_pkl jp', 'jp.id = pp.id_jadwal_pkl', 'inner');
        $this->db->join('dosen d', 'd.id = pp.id_dosen', 'inner');
        $this->db->where('jp.id_tahun_akademik', $id_tahun_akademik);
        
        $penguji_from_table = $this->db->get()->result();
        
        // Ambil penguji dari jadwal_pkl.id_penguji (jika ada dan tidak duplikat dengan pkl_penguji)
        $this->db->select('jp.id_penguji as id_dosen, d.nama_dosen, d.nidn, jp.id as id_jadwal_pkl');
        $this->db->from('jadwal_pkl jp');
        $this->db->join('dosen d', 'd.id = jp.id_penguji', 'inner');
        $this->db->where('jp.id_tahun_akademik', $id_tahun_akademik);
        $this->db->where('jp.id_penguji IS NOT NULL');
        // Exclude yang sudah ada di pkl_penguji
        $this->db->where('NOT EXISTS (
            SELECT 1 FROM pkl_penguji pp2 
            WHERE pp2.id_jadwal_pkl = jp.id AND pp2.id_dosen = jp.id_penguji
        )', NULL, FALSE);
        
        $penguji_from_jadwal = $this->db->get()->result();
        
        // Gabungkan semua penguji
        $all_penguji = array_merge($penguji_from_table, $penguji_from_jadwal);
        
        // Hitung jumlah DISTINCT jadwal per dosen
        $penguji_map = array();
        foreach ($all_penguji as $penguji) {
            if ($penguji->id_dosen) {
                if (!isset($penguji_map[$penguji->id_dosen])) {
                    $penguji_map[$penguji->id_dosen] = array(
                        'id_dosen' => $penguji->id_dosen,
                        'nama_dosen' => $penguji->nama_dosen,
                        'nidn' => $penguji->nidn,
                        'jadwal_ids' => array()
                    );
                }
                if (!in_array($penguji->id_jadwal_pkl, $penguji_map[$penguji->id_dosen]['jadwal_ids'])) {
                    $penguji_map[$penguji->id_dosen]['jadwal_ids'][] = $penguji->id_jadwal_pkl;
                }
            }
        }
        
        $besaran_per_ujian = $this->Honor_setup_model->get_besaran('penguji_pkl', $id_tahun_akademik);
        
        $count = 0;
        foreach ($penguji_map as $penguji) {
            $jumlah_ujian = count($penguji['jadwal_ids']);
            
            if ($penguji['id_dosen'] && $jumlah_ujian > 0 && $besaran_per_ujian > 0) {
                $total_honor = $jumlah_ujian * $besaran_per_ujian;
                
                $data = array(
                    'id_dosen' => $penguji['id_dosen'],
                    'id_tahun_akademik' => $id_tahun_akademik,
                    'jenis_honor' => 'penguji',
                    'jumlah_item' => $jumlah_ujian,
                    'besaran_per_item' => $besaran_per_ujian,
                    'total_honor' => $total_honor,
                    'keterangan' => 'Honor penguji PKL untuk ' . $jumlah_ujian . ' ujian'
                );
                
                $this->db->insert('honor_pkl', $data);
                $count++;
            }
        }
        
        return $count;
    }

    /**
     * Get semua honor PKL
     */
    public function get_all($id_tahun_akademik = null, $jenis_honor = null) {
        $this->db->select('hp.*, d.nama_dosen, d.nidn');
        $this->db->from('honor_pkl hp');
        $this->db->join('dosen d', 'd.id = hp.id_dosen', 'left');
        
        if ($id_tahun_akademik) {
            $this->db->where('hp.id_tahun_akademik', $id_tahun_akademik);
        }
        
        if ($jenis_honor) {
            $this->db->where('hp.jenis_honor', $jenis_honor);
        }
        
        $this->db->order_by('d.nama_dosen', 'ASC');
        $this->db->order_by('hp.jenis_honor', 'ASC');
        
        return $this->db->get()->result();
    }

    /**
     * Get honor PKL by dosen (rekap per dosen)
     */
    public function get_rekap_by_dosen($id_tahun_akademik = null) {
        $this->db->select('hp.id_dosen, d.nama_dosen, d.nidn,
                          SUM(CASE WHEN hp.jenis_honor = "pembimbing" THEN hp.total_honor ELSE 0 END) as total_honor_pembimbing,
                          SUM(CASE WHEN hp.jenis_honor = "pembimbing" THEN hp.jumlah_item ELSE 0 END) as jumlah_mahasiswa_bimbingan,
                          SUM(CASE WHEN hp.jenis_honor = "penguji" THEN hp.total_honor ELSE 0 END) as total_honor_penguji,
                          SUM(CASE WHEN hp.jenis_honor = "penguji" THEN hp.jumlah_item ELSE 0 END) as jumlah_ujian,
                          SUM(hp.total_honor) as total_honor_all');
        $this->db->from('honor_pkl hp');
        $this->db->join('dosen d', 'd.id = hp.id_dosen', 'inner');
        
        if ($id_tahun_akademik) {
            $this->db->where('hp.id_tahun_akademik', $id_tahun_akademik);
        }
        
        $this->db->group_by('hp.id_dosen');
        $this->db->order_by('d.nama_dosen', 'ASC');
        
        return $this->db->get()->result();
    }

    /**
     * Rekap honor PKL otomatis (tanpa generate/simpan ke tabel honor_pkl).
     * Sumber:
     * - Pembimbing: pkl_pengajuan.id_pembimbing
     * - Penguji: pkl_penguji + fallback jadwal_pkl.id_penguji
     */
    public function get_rekap_by_dosen_auto($id_tahun_akademik = null) {
        if (!$id_tahun_akademik) {
            return array();
        }

        $besaran_pembimbing = (float) $this->Honor_setup_model->get_besaran('pembimbing_pkl', $id_tahun_akademik);
        $besaran_penguji = (float) $this->Honor_setup_model->get_besaran('penguji_pkl', $id_tahun_akademik);

        // 1) Hitung jumlah mahasiswa bimbingan per dosen
        $this->db->select('pp.id_pembimbing as id_dosen, COUNT(DISTINCT pp.id_mahasiswa) as jumlah_mahasiswa_bimbingan');
        $this->db->from('pkl_pengajuan pp');
        $this->db->where('pp.id_tahun_akademik', $id_tahun_akademik);
        $this->db->where('pp.id_pembimbing IS NOT NULL');
        $this->db->group_by('pp.id_pembimbing');
        $pembimbing_rows = $this->db->get()->result();

        $rekap_map = array();
        foreach ($pembimbing_rows as $row) {
            $id_dosen = (int) $row->id_dosen;
            if (!isset($rekap_map[$id_dosen])) {
                $rekap_map[$id_dosen] = array(
                    'id_dosen' => $id_dosen,
                    'jumlah_mahasiswa_bimbingan' => 0,
                    'jumlah_ujian' => 0
                );
            }
            $rekap_map[$id_dosen]['jumlah_mahasiswa_bimbingan'] = (int) $row->jumlah_mahasiswa_bimbingan;
        }

        // 2) Hitung jumlah ujian penguji per dosen (distinct per jadwal)
        $this->db->select('pp.id_dosen, pp.id_jadwal_pkl');
        $this->db->from('pkl_penguji pp');
        $this->db->join('jadwal_pkl jp', 'jp.id = pp.id_jadwal_pkl', 'inner');
        $this->db->where('jp.id_tahun_akademik', $id_tahun_akademik);
        $penguji_rows = $this->db->get()->result();

        $this->db->select('jp.id_penguji as id_dosen, jp.id as id_jadwal_pkl');
        $this->db->from('jadwal_pkl jp');
        $this->db->where('jp.id_tahun_akademik', $id_tahun_akademik);
        $this->db->where('jp.id_penguji IS NOT NULL');
        $this->db->where('NOT EXISTS (
            SELECT 1 FROM pkl_penguji pp2
            WHERE pp2.id_jadwal_pkl = jp.id AND pp2.id_dosen = jp.id_penguji
        )', NULL, FALSE);
        $penguji_fallback_rows = $this->db->get()->result();

        $penguji_map = array();
        foreach (array_merge($penguji_rows, $penguji_fallback_rows) as $row) {
            $id_dosen = (int) $row->id_dosen;
            $id_jadwal = (int) $row->id_jadwal_pkl;
            if ($id_dosen <= 0 || $id_jadwal <= 0) {
                continue;
            }
            if (!isset($penguji_map[$id_dosen])) {
                $penguji_map[$id_dosen] = array();
            }
            $penguji_map[$id_dosen][$id_jadwal] = true;
        }

        foreach ($penguji_map as $id_dosen => $jadwal_set) {
            if (!isset($rekap_map[$id_dosen])) {
                $rekap_map[$id_dosen] = array(
                    'id_dosen' => (int) $id_dosen,
                    'jumlah_mahasiswa_bimbingan' => 0,
                    'jumlah_ujian' => 0
                );
            }
            $rekap_map[$id_dosen]['jumlah_ujian'] = count($jadwal_set);
        }

        if (empty($rekap_map)) {
            return array();
        }

        $dosen_ids = array_keys($rekap_map);
        $this->db->select('id, nidn, nama_dosen');
        $this->db->from('dosen');
        $this->db->where_in('id', $dosen_ids);
        $dosen_rows = $this->db->get()->result();

        $dosen_map = array();
        foreach ($dosen_rows as $dosen) {
            $dosen_map[(int) $dosen->id] = $dosen;
        }

        $result = array();
        foreach ($rekap_map as $id_dosen => $item) {
            if (!isset($dosen_map[$id_dosen])) {
                continue;
            }
            $jumlah_bimbingan = (int) $item['jumlah_mahasiswa_bimbingan'];
            $jumlah_ujian = (int) $item['jumlah_ujian'];
            $total_honor_pembimbing = $jumlah_bimbingan * $besaran_pembimbing;
            $total_honor_penguji = $jumlah_ujian * $besaran_penguji;

            $row = new stdClass();
            $row->id_dosen = $id_dosen;
            $row->nidn = $dosen_map[$id_dosen]->nidn;
            $row->nama_dosen = $dosen_map[$id_dosen]->nama_dosen;
            $row->jumlah_mahasiswa_bimbingan = $jumlah_bimbingan;
            $row->total_honor_pembimbing = $total_honor_pembimbing;
            $row->jumlah_ujian = $jumlah_ujian;
            $row->total_honor_penguji = $total_honor_penguji;
            $row->total_honor_all = $total_honor_pembimbing + $total_honor_penguji;
            $result[] = $row;
        }

        usort($result, function($a, $b) {
            return strcasecmp($a->nama_dosen, $b->nama_dosen);
        });

        return $result;
    }

    /**
     * Count semua honor PKL
     */
    public function count_all($id_tahun_akademik = null, $jenis_honor = null) {
        $this->db->from('honor_pkl');
        
        if ($id_tahun_akademik) {
            $this->db->where('id_tahun_akademik', $id_tahun_akademik);
        }
        
        if ($jenis_honor) {
            $this->db->where('jenis_honor', $jenis_honor);
        }
        
        return $this->db->count_all_results();
    }
}
