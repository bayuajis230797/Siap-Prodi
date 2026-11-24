<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Honor UTS Model
 * Mengelola perhitungan dan data honor UTS
 */
class Honor_uts_model extends CI_Model {

    public function __construct() {
        parent::__construct();
        $this->load->database();
        $this->load->model('Honor_setup_model');
        $this->load->model('Mahasiswa_model');
    }

    /**
     * Generate honor UTS untuk semua jadwal
     */
    public function generate_honor($id_tahun_akademik) {
        // Hapus honor lama untuk tahun akademik ini
        $this->db->where('id_tahun_akademik', $id_tahun_akademik);
        $this->db->delete('honor_uts');
        
        // Ambil semua jadwal UTS
        $this->db->select('jut.*, mdm.id_dosen, mdm.kelas, mk.kode_mk, mk.nama_mk');
        $this->db->from('jadwal_uts jut');
        $this->db->join('mapping_dosen_mk mdm', 'mdm.id = jut.id_mapping_dosen_mk', 'left');
        $this->db->join('mata_kuliah_aktif mka', 'mka.id = mdm.id_mata_kuliah_aktif', 'left');
        $this->db->join('kurikulum_detail kd', 'kd.id = mka.id_kurikulum_detail', 'left');
        $this->db->join('mata_kuliah mk', 'mk.id = kd.id_mata_kuliah', 'left');
        $this->db->where('jut.id_tahun_akademik', $id_tahun_akademik);
        
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
        $existing = $this->db->where('honor_uts.id_dosen', $jadwal->id_dosen)
                             ->where('honor_uts.jenis_honor', 'pembuatan_soal')
                             ->where('honor_uts.id_tahun_akademik', $id_tahun_akademik)
                             ->join('jadwal_uts jut', 'jut.id = honor_uts.id_jadwal_uts', 'left')
                             ->join('mapping_dosen_mk mdm', 'mdm.id = jut.id_mapping_dosen_mk', 'left')
                             ->join('mata_kuliah_aktif mka', 'mka.id = mdm.id_mata_kuliah_aktif', 'left')
                             ->join('kurikulum_detail kd', 'kd.id = mka.id_kurikulum_detail', 'left')
                             ->join('mata_kuliah mk', 'mk.id = kd.id_mata_kuliah', 'left')
                             ->where('mdm.kelas', $jadwal->kelas)
                             ->where('mk.kode_mk', $jadwal->kode_mk)
                             ->get('honor_uts')
                             ->row();
        
        if ($existing) {
            return; // Sudah ada, skip
        }
        
        $besaran = $this->Honor_setup_model->get_besaran('pembuatan_soal_uts', $id_tahun_akademik);
        
        if ($besaran > 0) {
            $data = array(
                'id_jadwal_uts' => $jadwal->id,
                'id_dosen' => $jadwal->id_dosen,
                'id_tendik' => null,
                'jenis_honor' => 'pembuatan_soal',
                'jumlah_item' => 1, // 1 kelas
                'besaran_per_item' => $besaran,
                'total_honor' => $besaran,
                'id_tahun_akademik' => $id_tahun_akademik,
                'keterangan' => 'Honor pembuatan soal UTS untuk kelas ' . $jadwal->kelas . ' - ' . $jadwal->kode_mk
            );
            
            $this->db->insert('honor_uts', $data);
        }
    }

    /**
     * Generate honor koreksi (per kelas)
     */
    private function generate_honor_koreksi($jadwal, $id_tahun_akademik) {
        // Cek apakah sudah ada honor koreksi untuk dosen + kelas + kode_mk ini
        $existing = $this->db->where('honor_uts.id_dosen', $jadwal->id_dosen)
                             ->where('honor_uts.jenis_honor', 'koreksi')
                             ->where('honor_uts.id_tahun_akademik', $id_tahun_akademik)
                             ->join('jadwal_uts jut', 'jut.id = honor_uts.id_jadwal_uts', 'left')
                             ->join('mapping_dosen_mk mdm', 'mdm.id = jut.id_mapping_dosen_mk', 'left')
                             ->join('mata_kuliah_aktif mka', 'mka.id = mdm.id_mata_kuliah_aktif', 'left')
                             ->join('kurikulum_detail kd', 'kd.id = mka.id_kurikulum_detail', 'left')
                             ->join('mata_kuliah mk', 'mk.id = kd.id_mata_kuliah', 'left')
                             ->where('mdm.kelas', $jadwal->kelas)
                             ->where('mk.kode_mk', $jadwal->kode_mk)
                             ->get('honor_uts')
                             ->row();
        
        if ($existing) {
            return; // Sudah ada, skip
        }
        
        // Ambil jumlah mahasiswa aktif di kelas tersebut (bukan yang hadir, tapi semua mahasiswa aktif di kelas)
        $mahasiswa = $this->Mahasiswa_model->get_by_kelas($jadwal->kelas, 'aktif');
        $jumlah_mahasiswa = count($mahasiswa);
        
        // Jumlah mahasiswa ini akan digunakan sebagai jumlah_item untuk perhitungan honor koreksi
        
        $besaran = $this->Honor_setup_model->get_besaran('koreksi_uts', $id_tahun_akademik);
        
        if ($besaran > 0 && $jumlah_mahasiswa > 0) {
            $total_honor = $besaran * $jumlah_mahasiswa;
            
            $data = array(
                'id_jadwal_uts' => $jadwal->id,
                'id_dosen' => $jadwal->id_dosen,
                'id_tendik' => null,
                'jenis_honor' => 'koreksi',
                'jumlah_item' => $jumlah_mahasiswa,
                'besaran_per_item' => $besaran,
                'total_honor' => $total_honor,
                'id_tahun_akademik' => $id_tahun_akademik,
                'keterangan' => 'Honor koreksi UTS untuk ' . $jumlah_mahasiswa . ' mahasiswa kelas ' . $jadwal->kelas . ' - ' . $jadwal->kode_mk
            );
            
            $this->db->insert('honor_uts', $data);
        }
    }

    /**
     * Generate honor pengawas
     */
    private function generate_honor_pengawas($jadwal, $id_tahun_akademik) {
        // Ambil pengawas langsung dari jadwal (tidak perlu cek berita acara)
        $this->db->select('jup.id_dosen, jup.id_tendik');
        $this->db->from('jadwal_uts_pengawas jup');
        $this->db->where('jup.id_jadwal_uts', $jadwal->id);
        
        $pengawas_list = $this->db->get()->result();
        
        if (empty($pengawas_list)) {
            return; // Tidak ada pengawas
        }
        
        $besaran = $this->Honor_setup_model->get_besaran('pengawas_uts', $id_tahun_akademik);
        
        foreach ($pengawas_list as $pengawas) {
            // Cek apakah sudah ada honor pengawas untuk pengawas ini di jadwal ini
            $existing = $this->db->where('honor_uts.id_jadwal_uts', $jadwal->id)
                                 ->where('honor_uts.jenis_honor', 'pengawas')
                                 ->where('honor_uts.id_dosen', $pengawas->id_dosen)
                                 ->where('honor_uts.id_tendik', $pengawas->id_tendik)
                                 ->get('honor_uts')
                                 ->row();
            
            if ($existing) {
                continue; // Sudah ada, skip
            }
            
            if ($besaran > 0) {
                $data = array(
                    'id_jadwal_uts' => $jadwal->id,
                    'id_dosen' => $pengawas->id_dosen,
                    'id_tendik' => $pengawas->id_tendik,
                    'jenis_honor' => 'pengawas',
                    'jumlah_item' => 1,
                    'besaran_per_item' => $besaran,
                    'total_honor' => $besaran,
                    'id_tahun_akademik' => $id_tahun_akademik,
                    'keterangan' => 'Honor pengawas UTS untuk jadwal ' . $jadwal->kode_mk . ' kelas ' . $jadwal->kelas
                );
                
                $this->db->insert('honor_uts', $data);
            }
        }
    }

    /**
     * Get semua honor UTS
     */
    public function get_all($id_tahun_akademik = null, $limit = null, $offset = null) {
        $this->db->select('hut.*, 
                          d.nidn, d.nama_dosen,
                          t.nipy, t.nama_tendik,
                          jut.tanggal, jut.kelas,
                          mk.kode_mk, mk.nama_mk,
                          ta.tahun_akademik, ta.semester');
        $this->db->from('honor_uts hut');
        $this->db->join('jadwal_uts jut', 'jut.id = hut.id_jadwal_uts', 'left');
        $this->db->join('mapping_dosen_mk mdm', 'mdm.id = jut.id_mapping_dosen_mk', 'left');
        $this->db->join('mata_kuliah_aktif mka', 'mka.id = mdm.id_mata_kuliah_aktif', 'left');
        $this->db->join('kurikulum_detail kd', 'kd.id = mka.id_kurikulum_detail', 'left');
        $this->db->join('mata_kuliah mk', 'mk.id = kd.id_mata_kuliah', 'left');
        $this->db->join('dosen d', 'd.id = hut.id_dosen', 'left');
        $this->db->join('tendik t', 't.id = hut.id_tendik', 'left');
        $this->db->join('tahun_akademik ta', 'ta.id = hut.id_tahun_akademik', 'left');
        
        if ($id_tahun_akademik) {
            $this->db->where('hut.id_tahun_akademik', $id_tahun_akademik);
        }
        
        $this->db->order_by('jut.tanggal', 'DESC');
        $this->db->order_by('hut.jenis_honor', 'ASC');
        
        if ($limit) {
            $this->db->limit($limit, $offset);
        }
        
        return $this->db->get()->result();
    }

    /**
     * Count semua honor UTS
     */
    public function count_all($id_tahun_akademik = null) {
        $this->db->from('honor_uts');
        
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
        $this->db->from('honor_uts');
        $this->db->where('honor_uts.id_dosen', $id_dosen);
        
        if ($id_tahun_akademik) {
            $this->db->where('honor_uts.id_tahun_akademik', $id_tahun_akademik);
        }
        
        $result = $this->db->get()->row();
        return $result ? $result->total_honor : 0;
    }

    /**
     * Get total honor per tendik
     */
    public function get_total_per_tendik($id_tendik, $id_tahun_akademik = null) {
        $this->db->select_sum('total_honor');
        $this->db->from('honor_uts');
        $this->db->where('honor_uts.id_tendik', $id_tendik);
        
        if ($id_tahun_akademik) {
            $this->db->where('honor_uts.id_tahun_akademik', $id_tahun_akademik);
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
        $this->db->select('hut.*, jut.tanggal, mdm.kelas, rk.nama_ruang as ruangan, mk.kode_mk, mk.nama_mk');
        $this->db->from('honor_uts hut');
        $this->db->join('jadwal_uts jut', 'jut.id = hut.id_jadwal_uts', 'left');
        $this->db->join('ruang_kelas rk', 'rk.id = jut.id_ruang_kelas', 'left');
        $this->db->join('mapping_dosen_mk mdm', 'mdm.id = jut.id_mapping_dosen_mk', 'left');
        $this->db->join('mata_kuliah_aktif mka', 'mka.id = mdm.id_mata_kuliah_aktif', 'left');
        $this->db->join('kurikulum_detail kd', 'kd.id = mka.id_kurikulum_detail', 'left');
        $this->db->join('mata_kuliah mk', 'mk.id = kd.id_mata_kuliah', 'left');
        $this->db->where('hut.id_dosen', $id_dosen);
        $this->db->where('hut.jenis_honor', 'pembuatan_soal');
        if ($id_tahun_akademik) {
            $this->db->where('hut.id_tahun_akademik', $id_tahun_akademik);
        }
        $this->db->group_by('hut.id_dosen, mdm.kelas, mk.kode_mk');
        $this->db->order_by('jut.tanggal', 'ASC');
        $this->db->order_by('mk.kode_mk', 'ASC');
        $detail['pembuatan_soal'] = $this->db->get()->result();
        
        // Ambil honor koreksi (GROUP BY kelas + kode_mk untuk menghindari duplikasi)
        $this->db->select('hut.*, jut.tanggal, mdm.kelas, rk.nama_ruang as ruangan, mk.kode_mk, mk.nama_mk, SUM(hut.jumlah_item) as total_mahasiswa, SUM(hut.total_honor) as total_honor_koreksi');
        $this->db->from('honor_uts hut');
        $this->db->join('jadwal_uts jut', 'jut.id = hut.id_jadwal_uts', 'left');
        $this->db->join('ruang_kelas rk', 'rk.id = jut.id_ruang_kelas', 'left');
        $this->db->join('mapping_dosen_mk mdm', 'mdm.id = jut.id_mapping_dosen_mk', 'left');
        $this->db->join('mata_kuliah_aktif mka', 'mka.id = mdm.id_mata_kuliah_aktif', 'left');
        $this->db->join('kurikulum_detail kd', 'kd.id = mka.id_kurikulum_detail', 'left');
        $this->db->join('mata_kuliah mk', 'mk.id = kd.id_mata_kuliah', 'left');
        $this->db->where('hut.id_dosen', $id_dosen);
        $this->db->where('hut.jenis_honor', 'koreksi');
        if ($id_tahun_akademik) {
            $this->db->where('hut.id_tahun_akademik', $id_tahun_akademik);
        }
        $this->db->group_by('hut.id_dosen, mdm.kelas, mk.kode_mk');
        $this->db->order_by('jut.tanggal', 'ASC');
        $this->db->order_by('mk.kode_mk', 'ASC');
        $detail['koreksi'] = $this->db->get()->result();
        
        // Ambil honor pengawas
        $this->db->select('hut.*, jut.tanggal, jut.kelas, rk.nama_ruang as ruangan, mk.kode_mk, mk.nama_mk, jut.sesi, jut.jam_mulai, jut.jam_selesai');
        $this->db->from('honor_uts hut');
        $this->db->join('jadwal_uts jut', 'jut.id = hut.id_jadwal_uts', 'left');
        $this->db->join('ruang_kelas rk', 'rk.id = jut.id_ruang_kelas', 'left');
        $this->db->join('mapping_dosen_mk mdm', 'mdm.id = jut.id_mapping_dosen_mk', 'left');
        $this->db->join('mata_kuliah_aktif mka', 'mka.id = mdm.id_mata_kuliah_aktif', 'left');
        $this->db->join('kurikulum_detail kd', 'kd.id = mka.id_kurikulum_detail', 'left');
        $this->db->join('mata_kuliah mk', 'mk.id = kd.id_mata_kuliah', 'left');
        $this->db->where('hut.id_dosen', $id_dosen);
        $this->db->where('hut.jenis_honor', 'pengawas');
        if ($id_tahun_akademik) {
            $this->db->where('hut.id_tahun_akademik', $id_tahun_akademik);
        }
        $this->db->order_by('jut.tanggal', 'ASC');
        $this->db->order_by('jut.jam_mulai', 'ASC');
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
        $this->db->select('hut.*, jut.tanggal, jut.kelas, rk.nama_ruang as ruangan, mk.kode_mk, mk.nama_mk, jut.sesi, jut.jam_mulai, jut.jam_selesai');
        $this->db->from('honor_uts hut');
        $this->db->join('jadwal_uts jut', 'jut.id = hut.id_jadwal_uts', 'left');
        $this->db->join('ruang_kelas rk', 'rk.id = jut.id_ruang_kelas', 'left');
        $this->db->join('mapping_dosen_mk mdm', 'mdm.id = jut.id_mapping_dosen_mk', 'left');
        $this->db->join('mata_kuliah_aktif mka', 'mka.id = mdm.id_mata_kuliah_aktif', 'left');
        $this->db->join('kurikulum_detail kd', 'kd.id = mka.id_kurikulum_detail', 'left');
        $this->db->join('mata_kuliah mk', 'mk.id = kd.id_mata_kuliah', 'left');
        $this->db->where('hut.id_tendik', $id_tendik);
        $this->db->where('hut.jenis_honor', 'pengawas');
        if ($id_tahun_akademik) {
            $this->db->where('hut.id_tahun_akademik', $id_tahun_akademik);
        }
        $this->db->order_by('jut.tanggal', 'ASC');
        $this->db->order_by('jut.jam_mulai', 'ASC');
        $this->db->order_by('mk.kode_mk', 'ASC');
        $detail['pengawas'] = $this->db->get()->result();
        
        return $detail;
    }
}



