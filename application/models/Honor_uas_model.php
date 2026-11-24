<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Honor UAS Model
 * Mengelola perhitungan dan data honor UAS
 */
class Honor_uas_model extends CI_Model {

    public function __construct() {
        parent::__construct();
        $this->load->database();
        $this->load->model('Honor_setup_model');
        $this->load->model('Mahasiswa_model');
    }

    /**
     * Generate honor UAS untuk semua jadwal
     */
    public function generate_honor($id_tahun_akademik) {
        // Hapus honor lama untuk tahun akademik ini
        $this->db->where('id_tahun_akademik', $id_tahun_akademik);
        $this->db->delete('honor_uas');
        
        // Ambil semua jadwal UAS
        $this->db->select('jua.*, mdm.id_dosen, mdm.kelas, mk.kode_mk, mk.nama_mk');
        $this->db->from('jadwal_uas jua');
        $this->db->join('mapping_dosen_mk mdm', 'mdm.id = jua.id_mapping_dosen_mk', 'left');
        $this->db->join('mata_kuliah_aktif mka', 'mka.id = mdm.id_mata_kuliah_aktif', 'left');
        $this->db->join('kurikulum_detail kd', 'kd.id = mka.id_kurikulum_detail', 'left');
        $this->db->join('mata_kuliah mk', 'mk.id = kd.id_mata_kuliah', 'left');
        $this->db->where('jua.id_tahun_akademik', $id_tahun_akademik);
        
        $jadwal_list = $this->db->get()->result();
        
        $honor_generated = 0;
        $kelas_soal_generated = array(); // Track honor soal per kelas
        $kelas_koreksi_generated = array(); // Track honor koreksi per kelas
        
        foreach ($jadwal_list as $jadwal) {
            if (!$jadwal->id_dosen || !$jadwal->kelas) {
                continue;
            }
            
            // 1. Honor Pembuatan Soal (per dosen pengampu per kelas - UNIQUE)
            $key_soal = $jadwal->id_dosen . '_' . $jadwal->kelas . '_' . $jadwal->kode_mk;
            if (!isset($kelas_soal_generated[$key_soal])) {
                $this->generate_honor_pembuatan_soal($jadwal, $id_tahun_akademik);
                $kelas_soal_generated[$key_soal] = true;
            }
            
            // 2. Honor Koreksi (per jumlah mahasiswa yang hadir per kelas - UNIQUE)
            $key_koreksi = $jadwal->id_dosen . '_' . $jadwal->kelas . '_' . $jadwal->kode_mk;
            if (!isset($kelas_koreksi_generated[$key_koreksi])) {
                $this->generate_honor_koreksi($jadwal, $id_tahun_akademik);
                $kelas_koreksi_generated[$key_koreksi] = true;
            }
            
            // 3. Honor Pengawas (per pengawas yang sudah ada berita acara)
            $this->generate_honor_pengawas($jadwal, $id_tahun_akademik);
            
            $honor_generated++;
        }
        
        return $honor_generated;
    }

    /**
     * Generate honor pembuatan soal (per kelas)
     */
    private function generate_honor_pembuatan_soal($jadwal, $id_tahun_akademik) {
        // Cek apakah sudah ada honor pembuatan soal untuk dosen + kelas + kode_mk ini
        $existing = $this->db->where('honor_uas.id_dosen', $jadwal->id_dosen)
                             ->where('honor_uas.jenis_honor', 'pembuatan_soal')
                             ->where('honor_uas.id_tahun_akademik', $id_tahun_akademik)
                             ->join('jadwal_uas jua', 'jua.id = honor_uas.id_jadwal_uas', 'left')
                             ->join('mapping_dosen_mk mdm', 'mdm.id = jua.id_mapping_dosen_mk', 'left')
                             ->join('mata_kuliah_aktif mka', 'mka.id = mdm.id_mata_kuliah_aktif', 'left')
                             ->join('kurikulum_detail kd', 'kd.id = mka.id_kurikulum_detail', 'left')
                             ->join('mata_kuliah mk', 'mk.id = kd.id_mata_kuliah', 'left')
                             ->where('mdm.kelas', $jadwal->kelas)
                             ->where('mk.kode_mk', $jadwal->kode_mk)
                             ->get('honor_uas')
                             ->row();
        
        if ($existing) {
            return; // Sudah ada, skip
        }
        
        $besaran = $this->Honor_setup_model->get_besaran('pembuatan_soal_uas', $id_tahun_akademik);
        
        if ($besaran > 0) {
            $data = array(
                'id_jadwal_uas' => $jadwal->id,
                'id_dosen' => $jadwal->id_dosen,
                'id_tendik' => null,
                'jenis_honor' => 'pembuatan_soal',
                'jumlah_item' => 1, // 1 kelas
                'besaran_per_item' => $besaran,
                'total_honor' => $besaran,
                'id_tahun_akademik' => $id_tahun_akademik,
                'keterangan' => 'Honor pembuatan soal UAS untuk kelas ' . $jadwal->kelas . ' - ' . $jadwal->kode_mk
            );
            
            $this->db->insert('honor_uas', $data);
        }
    }

    /**
     * Generate honor koreksi (per kelas)
     */
    private function generate_honor_koreksi($jadwal, $id_tahun_akademik) {
        // Cek apakah sudah ada honor koreksi untuk dosen + kelas + kode_mk ini
        $existing = $this->db->where('honor_uas.id_dosen', $jadwal->id_dosen)
                             ->where('honor_uas.jenis_honor', 'koreksi')
                             ->where('honor_uas.id_tahun_akademik', $id_tahun_akademik)
                             ->join('jadwal_uas jua', 'jua.id = honor_uas.id_jadwal_uas', 'left')
                             ->join('mapping_dosen_mk mdm', 'mdm.id = jua.id_mapping_dosen_mk', 'left')
                             ->join('mata_kuliah_aktif mka', 'mka.id = mdm.id_mata_kuliah_aktif', 'left')
                             ->join('kurikulum_detail kd', 'kd.id = mka.id_kurikulum_detail', 'left')
                             ->join('mata_kuliah mk', 'mk.id = kd.id_mata_kuliah', 'left')
                             ->where('mdm.kelas', $jadwal->kelas)
                             ->where('mk.kode_mk', $jadwal->kode_mk)
                             ->get('honor_uas')
                             ->row();
        
        if ($existing) {
            return; // Sudah ada, skip
        }
        
        // Ambil jumlah mahasiswa aktif di kelas tersebut (bukan yang hadir, tapi semua mahasiswa aktif di kelas)
        $mahasiswa = $this->Mahasiswa_model->get_by_kelas($jadwal->kelas, 'aktif');
        $jumlah_mahasiswa = count($mahasiswa);
        
        // Jumlah mahasiswa ini akan digunakan sebagai jumlah_item untuk perhitungan honor koreksi
        
        $besaran = $this->Honor_setup_model->get_besaran('koreksi_uas', $id_tahun_akademik);
        
        if ($besaran > 0 && $jumlah_mahasiswa > 0) {
            $total_honor = $besaran * $jumlah_mahasiswa;
            
            $data = array(
                'id_jadwal_uas' => $jadwal->id,
                'id_dosen' => $jadwal->id_dosen,
                'id_tendik' => null,
                'jenis_honor' => 'koreksi',
                'jumlah_item' => $jumlah_mahasiswa,
                'besaran_per_item' => $besaran,
                'total_honor' => $total_honor,
                'id_tahun_akademik' => $id_tahun_akademik,
                'keterangan' => 'Honor koreksi UAS untuk ' . $jumlah_mahasiswa . ' mahasiswa kelas ' . $jadwal->kelas . ' - ' . $jadwal->kode_mk
            );
            
            $this->db->insert('honor_uas', $data);
        }
    }

    /**
     * Generate honor pengawas
     */
    private function generate_honor_pengawas($jadwal, $id_tahun_akademik) {
        // Ambil pengawas langsung dari jadwal (tidak perlu cek berita acara)
        $this->db->select('jup.id_dosen, jup.id_tendik');
        $this->db->from('jadwal_uas_pengawas jup');
        $this->db->where('jup.id_jadwal_uas', $jadwal->id);
        
        $pengawas_list = $this->db->get()->result();
        
        if (empty($pengawas_list)) {
            return; // Tidak ada pengawas
        }
        
        $besaran = $this->Honor_setup_model->get_besaran('pengawas_uas', $id_tahun_akademik);
        
        foreach ($pengawas_list as $pengawas) {
            // Cek apakah sudah ada honor pengawas untuk pengawas ini di jadwal ini
            $existing = $this->db->where('honor_uas.id_jadwal_uas', $jadwal->id)
                                 ->where('honor_uas.jenis_honor', 'pengawas')
                                 ->where('honor_uas.id_dosen', $pengawas->id_dosen)
                                 ->where('honor_uas.id_tendik', $pengawas->id_tendik)
                                 ->get('honor_uas')
                                 ->row();
            
            if ($existing) {
                continue; // Sudah ada, skip
            }
            
            if ($besaran > 0) {
                $data = array(
                    'id_jadwal_uas' => $jadwal->id,
                    'id_dosen' => $pengawas->id_dosen,
                    'id_tendik' => $pengawas->id_tendik,
                    'jenis_honor' => 'pengawas',
                    'jumlah_item' => 1,
                    'besaran_per_item' => $besaran,
                    'total_honor' => $besaran,
                    'id_tahun_akademik' => $id_tahun_akademik,
                    'keterangan' => 'Honor pengawas UAS untuk jadwal ' . $jadwal->kode_mk . ' kelas ' . $jadwal->kelas
                );
                
                $this->db->insert('honor_uas', $data);
            }
        }
    }

    /**
     * Get semua honor UAS
     */
    public function get_all($id_tahun_akademik = null, $limit = null, $offset = null) {
        $this->db->select('hua.*, 
                          d.nidn, d.nama_dosen,
                          t.nipy, t.nama_tendik,
                          jua.tanggal, jua.kelas,
                          mk.kode_mk, mk.nama_mk,
                          ta.tahun_akademik, ta.semester');
        $this->db->from('honor_uas hua');
        $this->db->join('jadwal_uas jua', 'jua.id = hua.id_jadwal_uas', 'left');
        $this->db->join('mapping_dosen_mk mdm', 'mdm.id = jua.id_mapping_dosen_mk', 'left');
        $this->db->join('mata_kuliah_aktif mka', 'mka.id = mdm.id_mata_kuliah_aktif', 'left');
        $this->db->join('kurikulum_detail kd', 'kd.id = mka.id_kurikulum_detail', 'left');
        $this->db->join('mata_kuliah mk', 'mk.id = kd.id_mata_kuliah', 'left');
        $this->db->join('dosen d', 'd.id = hua.id_dosen', 'left');
        $this->db->join('tendik t', 't.id = hua.id_tendik', 'left');
        $this->db->join('tahun_akademik ta', 'ta.id = hua.id_tahun_akademik', 'left');
        
        if ($id_tahun_akademik) {
            $this->db->where('hua.id_tahun_akademik', $id_tahun_akademik);
        }
        
        $this->db->order_by('jua.tanggal', 'DESC');
        $this->db->order_by('hua.jenis_honor', 'ASC');
        
        if ($limit) {
            $this->db->limit($limit, $offset);
        }
        
        return $this->db->get()->result();
    }

    /**
     * Count semua honor UAS
     */
    public function count_all($id_tahun_akademik = null) {
        $this->db->from('honor_uas');
        
        if ($id_tahun_akademik) {
            $this->db->where('id_tahun_akademik', $id_tahun_akademik);
        }
        
        return $this->db->count_all_results();
    }

    /**
     * Get total honor per dosen
     */
    public function get_total_per_dosen($id_dosen, $id_tahun_akademik = null) {
        $this->db->select_sum('total_honor');
        $this->db->from('honor_uas');
        $this->db->where('honor_uas.id_dosen', $id_dosen);
        
        if ($id_tahun_akademik) {
            $this->db->where('honor_uas.id_tahun_akademik', $id_tahun_akademik);
        }
        
        $result = $this->db->get()->row();
        return $result ? $result->total_honor : 0;
    }

    /**
     * Get total honor per tendik
     */
    public function get_total_per_tendik($id_tendik, $id_tahun_akademik = null) {
        $this->db->select_sum('total_honor');
        $this->db->from('honor_uas');
        $this->db->where('honor_uas.id_tendik', $id_tendik);
        
        if ($id_tahun_akademik) {
            $this->db->where('honor_uas.id_tahun_akademik', $id_tahun_akademik);
        }
        
        $result = $this->db->get()->row();
        return $result ? $result->total_honor : 0;
    }

    /**
     * Get detail honor per dosen
     */
    public function get_detail_per_dosen($id_dosen, $id_tahun_akademik = null) {
        $detail = array();
        
        // Ambil honor pembuatan soal (GROUP BY kelas + kode_mk untuk menghindari duplikasi)
        $this->db->select('hua.*, jua.tanggal, mdm.kelas, rk.nama_ruang as ruangan, mk.kode_mk, mk.nama_mk');
        $this->db->from('honor_uas hua');
        $this->db->join('jadwal_uas jua', 'jua.id = hua.id_jadwal_uas', 'left');
        $this->db->join('ruang_kelas rk', 'rk.id = jua.id_ruang_kelas', 'left');
        $this->db->join('mapping_dosen_mk mdm', 'mdm.id = jua.id_mapping_dosen_mk', 'left');
        $this->db->join('mata_kuliah_aktif mka', 'mka.id = mdm.id_mata_kuliah_aktif', 'left');
        $this->db->join('kurikulum_detail kd', 'kd.id = mka.id_kurikulum_detail', 'left');
        $this->db->join('mata_kuliah mk', 'mk.id = kd.id_mata_kuliah', 'left');
        $this->db->where('hua.id_dosen', $id_dosen);
        $this->db->where('hua.jenis_honor', 'pembuatan_soal');
        if ($id_tahun_akademik) {
            $this->db->where('hua.id_tahun_akademik', $id_tahun_akademik);
        }
        $this->db->group_by('hua.id_dosen, mdm.kelas, mk.kode_mk');
        $this->db->order_by('jua.tanggal', 'ASC');
        $this->db->order_by('mk.kode_mk', 'ASC');
        $detail['pembuatan_soal'] = $this->db->get()->result();
        
        // Ambil honor koreksi (GROUP BY kelas + kode_mk untuk menghindari duplikasi)
        $this->db->select('hua.*, jua.tanggal, mdm.kelas, rk.nama_ruang as ruangan, mk.kode_mk, mk.nama_mk, SUM(hua.jumlah_item) as total_mahasiswa, SUM(hua.total_honor) as total_honor_koreksi');
        $this->db->from('honor_uas hua');
        $this->db->join('jadwal_uas jua', 'jua.id = hua.id_jadwal_uas', 'left');
        $this->db->join('ruang_kelas rk', 'rk.id = jua.id_ruang_kelas', 'left');
        $this->db->join('mapping_dosen_mk mdm', 'mdm.id = jua.id_mapping_dosen_mk', 'left');
        $this->db->join('mata_kuliah_aktif mka', 'mka.id = mdm.id_mata_kuliah_aktif', 'left');
        $this->db->join('kurikulum_detail kd', 'kd.id = mka.id_kurikulum_detail', 'left');
        $this->db->join('mata_kuliah mk', 'mk.id = kd.id_mata_kuliah', 'left');
        $this->db->where('hua.id_dosen', $id_dosen);
        $this->db->where('hua.jenis_honor', 'koreksi');
        if ($id_tahun_akademik) {
            $this->db->where('hua.id_tahun_akademik', $id_tahun_akademik);
        }
        $this->db->group_by('hua.id_dosen, mdm.kelas, mk.kode_mk');
        $this->db->order_by('jua.tanggal', 'ASC');
        $this->db->order_by('mk.kode_mk', 'ASC');
        $detail['koreksi'] = $this->db->get()->result();
        
        // Ambil honor pengawas
        $this->db->select('hua.*, jua.tanggal, jua.kelas, rk.nama_ruang as ruangan, mk.kode_mk, mk.nama_mk, jua.sesi, jua.jam_mulai, jua.jam_selesai');
        $this->db->from('honor_uas hua');
        $this->db->join('jadwal_uas jua', 'jua.id = hua.id_jadwal_uas', 'left');
        $this->db->join('ruang_kelas rk', 'rk.id = jua.id_ruang_kelas', 'left');
        $this->db->join('mapping_dosen_mk mdm', 'mdm.id = jua.id_mapping_dosen_mk', 'left');
        $this->db->join('mata_kuliah_aktif mka', 'mka.id = mdm.id_mata_kuliah_aktif', 'left');
        $this->db->join('kurikulum_detail kd', 'kd.id = mka.id_kurikulum_detail', 'left');
        $this->db->join('mata_kuliah mk', 'mk.id = kd.id_mata_kuliah', 'left');
        $this->db->where('hua.id_dosen', $id_dosen);
        $this->db->where('hua.jenis_honor', 'pengawas');
        if ($id_tahun_akademik) {
            $this->db->where('hua.id_tahun_akademik', $id_tahun_akademik);
        }
        $this->db->order_by('jua.tanggal', 'ASC');
        $this->db->order_by('jua.jam_mulai', 'ASC');
        $this->db->order_by('mk.kode_mk', 'ASC');
        $detail['pengawas'] = $this->db->get()->result();
        
        return $detail;
    }

    /**
     * Get detail honor per tendik
     */
    public function get_detail_per_tendik($id_tendik, $id_tahun_akademik = null) {
        $detail = array();
        
        // Ambil honor pengawas
        $this->db->select('hua.*, jua.tanggal, jua.kelas, rk.nama_ruang as ruangan, mk.kode_mk, mk.nama_mk, jua.sesi, jua.jam_mulai, jua.jam_selesai');
        $this->db->from('honor_uas hua');
        $this->db->join('jadwal_uas jua', 'jua.id = hua.id_jadwal_uas', 'left');
        $this->db->join('ruang_kelas rk', 'rk.id = jua.id_ruang_kelas', 'left');
        $this->db->join('mapping_dosen_mk mdm', 'mdm.id = jua.id_mapping_dosen_mk', 'left');
        $this->db->join('mata_kuliah_aktif mka', 'mka.id = mdm.id_mata_kuliah_aktif', 'left');
        $this->db->join('kurikulum_detail kd', 'kd.id = mka.id_kurikulum_detail', 'left');
        $this->db->join('mata_kuliah mk', 'mk.id = kd.id_mata_kuliah', 'left');
        $this->db->where('hua.id_tendik', $id_tendik);
        $this->db->where('hua.jenis_honor', 'pengawas');
        if ($id_tahun_akademik) {
            $this->db->where('hua.id_tahun_akademik', $id_tahun_akademik);
        }
        $this->db->order_by('jua.tanggal', 'ASC');
        $this->db->order_by('jua.jam_mulai', 'ASC');
        $this->db->order_by('mk.kode_mk', 'ASC');
        $detail['pengawas'] = $this->db->get()->result();
        
        return $detail;
    }
}

