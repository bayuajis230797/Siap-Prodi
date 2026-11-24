<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Mapping Dosen MK Model
 * Mengelola mapping dosen dengan mata kuliah aktif
 */
class Mapping_dosen_mk_model extends CI_Model {

    public function __construct() {
        parent::__construct();
        $this->load->database();
    }

    /**
     * Get semua mapping dengan detail
     */
    public function get_all($id_tahun_akademik = null, $limit = 10, $offset = 0) {
        $this->db->select('mdm.*, mka.semester_aktif, mk.kode_mk, mk.nama_mk, mk.sks, 
                          k.nama_kurikulum, k.tahun_berlaku, d.nidn, d.nama_dosen, mdm.id_tahun_akademik');
        $this->db->from('mapping_dosen_mk mdm');
        $this->db->join('mata_kuliah_aktif mka', 'mka.id = mdm.id_mata_kuliah_aktif', 'left');
        $this->db->join('kurikulum_detail kd', 'kd.id = mka.id_kurikulum_detail', 'left');
        $this->db->join('mata_kuliah mk', 'mk.id = kd.id_mata_kuliah', 'left');
        $this->db->join('kurikulum k', 'k.id = mka.id_kurikulum', 'left');
        $this->db->join('dosen d', 'd.id = mdm.id_dosen', 'left');
        
        if ($id_tahun_akademik) {
            // Filter berdasarkan id_tahun_akademik di mapping (lebih reliable)
            $this->db->where('mdm.id_tahun_akademik', $id_tahun_akademik);
        }
        
        $this->db->order_by('mk.kode_mk', 'ASC');
        $this->db->order_by('mdm.kelas', 'ASC');
        $this->db->order_by('d.nama_dosen', 'ASC');
        $this->db->limit($limit, $offset);
        
        $query = $this->db->get();
        return $query->result();
    }

    /**
     * Count semua mapping
     */
    public function count_all($id_tahun_akademik = null) {
        $this->db->from('mapping_dosen_mk mdm');
        
        if ($id_tahun_akademik) {
            // Filter berdasarkan id_tahun_akademik di mapping (lebih reliable)
            $this->db->where('mdm.id_tahun_akademik', $id_tahun_akademik);
        }
        
        return $this->db->count_all_results();
    }

    /**
     * Get mapping by ID
     */
    public function get_by_id($id) {
        $this->db->select('mdm.*, mka.semester_aktif, mk.kode_mk, mk.nama_mk, mk.sks, 
                          k.nama_kurikulum, d.nidn, d.nama_dosen, mka.id_tahun_akademik');
        $this->db->from('mapping_dosen_mk mdm');
        $this->db->join('mata_kuliah_aktif mka', 'mka.id = mdm.id_mata_kuliah_aktif', 'left');
        $this->db->join('kurikulum_detail kd', 'kd.id = mka.id_kurikulum_detail', 'left');
        $this->db->join('mata_kuliah mk', 'mk.id = kd.id_mata_kuliah', 'left');
        $this->db->join('kurikulum k', 'k.id = mka.id_kurikulum', 'left');
        $this->db->join('dosen d', 'd.id = mdm.id_dosen', 'left');
        $this->db->where('mdm.id', $id);
        
        $query = $this->db->get();
        return $query->row();
    }

    /**
     * Get mapping by mata kuliah aktif
     */
    public function get_by_mata_kuliah_aktif($id_mata_kuliah_aktif) {
        $this->db->select('mdm.*, d.nidn, d.nama_dosen');
        $this->db->from('mapping_dosen_mk mdm');
        $this->db->join('dosen d', 'd.id = mdm.id_dosen', 'left');
        $this->db->where('mdm.id_mata_kuliah_aktif', $id_mata_kuliah_aktif);
        
        $query = $this->db->get();
        return $query->result();
    }

    /**
     * Create mapping
     */
    public function create($data) {
        $this->db->insert('mapping_dosen_mk', $data);
        return $this->db->insert_id();
    }

    /**
     * Update mapping
     */
    public function update($id, $data) {
        $this->db->where('id', $id);
        return $this->db->update('mapping_dosen_mk', $data);
    }

    /**
     * Delete mapping
     */
    public function delete($id) {
        $this->db->where('id', $id);
        return $this->db->delete('mapping_dosen_mk');
    }

    /**
     * Cek apakah mapping sudah ada
     */
    public function mapping_exists($id_mata_kuliah_aktif, $id_dosen, $kelas = null, $exclude_id = null) {
        $this->db->where('id_mata_kuliah_aktif', $id_mata_kuliah_aktif);
        $this->db->where('id_dosen', $id_dosen);
        if ($kelas !== null) {
            $this->db->where('kelas', $kelas);
        }
        if ($exclude_id) {
            $this->db->where('id !=', $exclude_id);
        }
        $query = $this->db->get('mapping_dosen_mk');
        return $query->num_rows() > 0;
    }

    /**
     * Get mata kuliah aktif yang belum di-mapping (untuk dropdown)
     */
    public function get_mata_kuliah_aktif_available($id_tahun_akademik) {
        // Ambil semua mata kuliah aktif untuk tahun akademik
        $this->db->select('mka.id, mk.kode_mk, mk.nama_mk, mk.sks, kd.semester, k.nama_kurikulum, k.tahun_berlaku');
        $this->db->from('mata_kuliah_aktif mka');
        $this->db->join('kurikulum_detail kd', 'kd.id = mka.id_kurikulum_detail', 'left');
        $this->db->join('mata_kuliah mk', 'mk.id = kd.id_mata_kuliah', 'left');
        $this->db->join('kurikulum k', 'k.id = mka.id_kurikulum', 'left');
        $this->db->where('mka.id_tahun_akademik', $id_tahun_akademik);
        $this->db->order_by('mk.kode_mk', 'ASC');
        
        $all_mk = $this->db->get()->result();
        
        // Ambil yang sudah di-mapping
        $this->db->select('id_mata_kuliah_aktif');
        $this->db->from('mapping_dosen_mk');
        $mapped_ids = array();
        foreach ($this->db->get()->result() as $row) {
            $mapped_ids[] = $row->id_mata_kuliah_aktif;
        }
        
        // Filter yang belum di-mapping
        $available = array();
        foreach ($all_mk as $mk) {
            if (!in_array($mk->id, $mapped_ids)) {
                $available[] = $mk;
            }
        }
        
        return $available;
    }

    /**
     * Get semua mata kuliah aktif untuk dropdown (termasuk yang sudah di-mapping)
     */
    public function get_all_mata_kuliah_aktif($id_tahun_akademik) {
        $this->db->select('mka.id, mk.kode_mk, mk.nama_mk, mk.sks, kd.semester, k.nama_kurikulum, k.tahun_berlaku');
        $this->db->from('mata_kuliah_aktif mka');
        $this->db->join('kurikulum_detail kd', 'kd.id = mka.id_kurikulum_detail', 'left');
        $this->db->join('mata_kuliah mk', 'mk.id = kd.id_mata_kuliah', 'left');
        $this->db->join('kurikulum k', 'k.id = mka.id_kurikulum', 'left');
        $this->db->where('mka.id_tahun_akademik', $id_tahun_akademik);
        $this->db->order_by('kd.semester', 'ASC'); // Urutkan semester terendah dulu
        $this->db->order_by('mk.kode_mk', 'ASC'); // Kemudian urutkan kode MK
        
        return $this->db->get()->result();
    }

    /**
     * Get mata kuliah aktif berdasarkan kelas
     * Menggunakan logika yang sama dengan menu Kelas admin
     * Mengambil dari kurikulum_detail, lalu cari id_mata_kuliah_aktif yang sesuai
     */
    public function get_mata_kuliah_aktif_by_kelas($kelas, $id_tahun_akademik) {
        // Ambil semester dari kelas (misalnya 3A = semester 3, mendukung multi-digit)
        $semester_kelas = $this->extract_semester_from_kelas($kelas);
        
        if ($semester_kelas < 1 || $semester_kelas > 8) {
            return array();
        }

        // Ambil kurikulum yang digunakan di kelas tersebut (sama dengan Kelas_model)
        $this->load->model('Kelas_model');
        $kurikulum_list = $this->Kelas_model->get_kurikulum_by_kelas($kelas);
        
        if (empty($kurikulum_list)) {
            return array();
        }

        $kurikulum_ids = array();
        foreach ($kurikulum_list as $k) {
            $kurikulum_ids[] = $k->id;
        }

        // Ambil mata kuliah dari kurikulum_detail untuk semester tersebut (sama dengan Kelas_model)
        // Ini mengambil data yang sama persis dengan menu Kelas admin
        $this->db->select('kd.id as id_kurikulum_detail, mk.kode_mk, mk.nama_mk, mk.sks, kd.semester, k.nama_kurikulum, k.tahun_berlaku, k.id as id_kurikulum, kd.jenis');
        $this->db->from('kurikulum_detail kd');
        $this->db->join('mata_kuliah mk', 'mk.id = kd.id_mata_kuliah', 'left');
        $this->db->join('kurikulum k', 'k.id = kd.id_kurikulum', 'left');
        $this->db->where_in('kd.id_kurikulum', $kurikulum_ids);
        $this->db->where('kd.semester', $semester_kelas);
        $this->db->where('k.is_aktif', 1); // Hanya kurikulum yang aktif
        $this->db->order_by('kd.semester', 'ASC');
        $this->db->order_by('mk.kode_mk', 'ASC');
        
        $mata_kuliah = $this->db->get()->result();

        // Load model untuk cek mata kuliah aktif
        $this->load->model('Mata_kuliah_aktif_model');
        
        // Untuk setiap mata kuliah, cari atau buat id_mata_kuliah_aktif
        $result = array();
        foreach ($mata_kuliah as $mk) {
            // Cek apakah sudah ada di mata_kuliah_aktif
            $mka = $this->db->select('mka.id, mka.semester_aktif')
                           ->from('mata_kuliah_aktif mka')
                           ->where('mka.id_kurikulum_detail', $mk->id_kurikulum_detail)
                           ->where('mka.id_tahun_akademik', $id_tahun_akademik)
                           ->get()
                           ->row();
            
            if ($mka) {
                // Jika sudah ada, gunakan id dari mata_kuliah_aktif
                $mk->id = $mka->id; // id untuk mapping (id_mata_kuliah_aktif)
                $mk->semester_aktif = $mka->semester_aktif;
            } else {
                // Jika belum ada, buat baru di mata_kuliah_aktif
                $data_mka = array(
                    'id_kurikulum' => $mk->id_kurikulum,
                    'id_kurikulum_detail' => $mk->id_kurikulum_detail,
                    'id_tahun_akademik' => $id_tahun_akademik,
                    'semester_aktif' => $mk->semester
                );
                $mka_id = $this->Mata_kuliah_aktif_model->set_aktif(
                    $mk->id_kurikulum, 
                    $mk->id_kurikulum_detail, 
                    $id_tahun_akademik, 
                    $mk->semester
                );
                $mk->id = $mka_id; // id untuk mapping (id_mata_kuliah_aktif)
                $mk->semester_aktif = $mk->semester;
            }
            
            $result[] = $mk;
        }

        return $result;
    }

    /**
     * Get semua kelas yang ada
     * Jika $tahun_aktif diberikan, filter kelas sesuai ganjil/genap:
     * - ganjil => semester kelas 1,3,5,7
     * - genap  => semester kelas 2,4,6,8
     */
    public function get_all_kelas($tahun_aktif = null) {
        $this->db->select('kelas');
        $this->db->distinct();
        $this->db->from('mahasiswa');
        $this->db->where('status', 'aktif');
        $this->db->where('kelas IS NOT NULL', null, false);
        $this->db->where('kelas !=', '');
        $this->db->order_by('kelas', 'ASC');
        $query = $this->db->get();
        $rows = $query->result();

        if (!$tahun_aktif || empty($tahun_aktif->semester)) {
            return $rows;
        }

        $want_odd = (strtolower($tahun_aktif->semester) === 'ganjil');
        $filtered = array();
        foreach ($rows as $row) {
            $semester = $this->extract_semester_from_kelas($row->kelas);
            if ($semester < 1 || $semester > 8) {
                continue;
            }
            $is_odd = ($semester % 2 === 1);
            if (($want_odd && $is_odd) || (!$want_odd && !$is_odd)) {
                $filtered[] = $row;
            }
        }

        return $filtered;
    }

    /**
     * Ambil angka semester dari kode kelas (mendukung 1A..8G, termasuk 2 digit jika ada)
     */
    public function extract_semester_from_kelas($kelas) {
        if (!is_string($kelas) || $kelas === '') {
            return 0;
        }
        if (preg_match('/^(\d{1,2})/', trim($kelas), $m)) {
            return (int) $m[1];
        }
        return 0;
    }
}

