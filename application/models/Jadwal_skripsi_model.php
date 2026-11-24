<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Jadwal_skripsi_model extends CI_Model {

    public function __construct() {
        parent::__construct();
        $this->load->database();
    }

    public function create($data) {
        $this->db->insert('jadwal_skripsi', $data);
        return $this->db->insert_id();
    }

    public function update($id, $data) {
        $this->db->where('id', $id);
        $this->db->update('jadwal_skripsi', $data);
        return $this->db->affected_rows();
    }

    public function delete($id) {
        $this->db->where('id', $id);
        return $this->db->delete('jadwal_skripsi');
    }

    public function get_by_id($id) {
        $this->db->select('js.*, 
                          ms.id_mahasiswa, ms.judul_skripsi, ms.nilai_bimbingan_final, ms.nilai_sidang, ms.nilai_skripsi_final, ms.nilai_huruf,
                          m.nim, m.nama_mahasiswa, m.kelas,
                          d1.nama_dosen as ketua_penguji, d1.nidn as ketua_penguji_nidn,
                          d2.nama_dosen as anggota_penguji_1, d2.nidn as anggota_penguji_1_nidn,
                          d3.nama_dosen as anggota_penguji_2, d3.nidn as anggota_penguji_2_nidn,
                          ta.tahun_akademik, ta.semester');
        $this->db->from('jadwal_skripsi js');
        $this->db->join('mahasiswa_skripsi ms', 'ms.id = js.id_mahasiswa_skripsi', 'left');
        $this->db->join('mahasiswa m', 'm.id = ms.id_mahasiswa', 'left');
        $this->db->join('dosen d1', 'd1.id = js.id_ketua_penguji', 'left');
        $this->db->join('dosen d2', 'd2.id = js.id_anggota_penguji_1', 'left');
        $this->db->join('dosen d3', 'd3.id = js.id_anggota_penguji_2', 'left');
        $this->db->join('tahun_akademik ta', 'ta.id = js.id_tahun_akademik', 'left');
        $this->db->where('js.id', $id);
        return $this->db->get()->row();
    }

    public function get_by_mahasiswa($id_mahasiswa_skripsi) {
        $this->db->select('js.*, 
                          ms.id_mahasiswa, ms.judul_skripsi,
                          m.nim, m.nama_mahasiswa, m.kelas,
                          d1.nama_dosen as ketua_penguji, d1.nidn as ketua_penguji_nidn,
                          d2.nama_dosen as anggota_penguji_1, d2.nidn as anggota_penguji_1_nidn,
                          d3.nama_dosen as anggota_penguji_2, d3.nidn as anggota_penguji_2_nidn');
        $this->db->from('jadwal_skripsi js');
        $this->db->join('mahasiswa_skripsi ms', 'ms.id = js.id_mahasiswa_skripsi', 'left');
        $this->db->join('mahasiswa m', 'm.id = ms.id_mahasiswa', 'left');
        $this->db->join('dosen d1', 'd1.id = js.id_ketua_penguji', 'left');
        $this->db->join('dosen d2', 'd2.id = js.id_anggota_penguji_1', 'left');
        $this->db->join('dosen d3', 'd3.id = js.id_anggota_penguji_2', 'left');
        $this->db->where('js.id_mahasiswa_skripsi', $id_mahasiswa_skripsi);
        $this->db->order_by('js.tanggal_ujian', 'DESC');
        $this->db->order_by('js.tanggal', 'DESC');
        $this->db->order_by('js.jam_mulai', 'ASC');
        return $this->db->get()->result();
    }

    public function get_by_penguji($id_penguji, $id_tahun_akademik = null) {
        $this->db->select('js.*, 
                          ms.id_mahasiswa, ms.judul_skripsi,
                          m.nim, m.nama_mahasiswa, m.kelas');
        $this->db->from('jadwal_skripsi js');
        $this->db->join('mahasiswa_skripsi ms', 'ms.id = js.id_mahasiswa_skripsi', 'left');
        $this->db->join('mahasiswa m', 'm.id = ms.id_mahasiswa', 'left');
        $this->db->group_start();
        $this->db->where('js.id_ketua_penguji', $id_penguji);
        $this->db->or_where('js.id_anggota_penguji_1', $id_penguji);
        $this->db->or_where('js.id_anggota_penguji_2', $id_penguji);
        $this->db->group_end();
        
        if ($id_tahun_akademik) {
            $this->db->where('js.id_tahun_akademik', $id_tahun_akademik);
        }
        
        $this->db->order_by('js.tanggal_ujian', 'ASC');
        $this->db->order_by('js.tanggal', 'ASC');
        $this->db->order_by('js.jam_mulai', 'ASC');
        return $this->db->get()->result();
    }

    public function get_all($id_tahun_akademik = null, $status = null, $limit = null, $offset = null) {
        $this->db->reset_query();
        $this->db->select('js.*, 
                          ms.id_mahasiswa, ms.judul_skripsi, ms.nilai_bimbingan_final, ms.nilai_sidang, ms.nilai_skripsi_final, ms.nilai_huruf,
                          m.nim, m.nama_mahasiswa, m.kelas,
                          d1.nama_dosen as ketua_penguji, d1.nidn as ketua_penguji_nidn,
                          d2.nama_dosen as anggota_penguji_1, d2.nidn as anggota_penguji_1_nidn,
                          d3.nama_dosen as anggota_penguji_2, d3.nidn as anggota_penguji_2_nidn,
                          ta.tahun_akademik, ta.semester');
        $this->db->from('jadwal_skripsi js');
        $this->db->join('mahasiswa_skripsi ms', 'ms.id = js.id_mahasiswa_skripsi', 'left');
        $this->db->join('mahasiswa m', 'm.id = ms.id_mahasiswa', 'left');
        $this->db->join('dosen d1', 'd1.id = js.id_ketua_penguji', 'left');
        $this->db->join('dosen d2', 'd2.id = js.id_anggota_penguji_1', 'left');
        $this->db->join('dosen d3', 'd3.id = js.id_anggota_penguji_2', 'left');
        $this->db->join('tahun_akademik ta', 'ta.id = js.id_tahun_akademik', 'left');
        
        if ($id_tahun_akademik && is_numeric($id_tahun_akademik)) {
            $this->db->where('js.id_tahun_akademik', $id_tahun_akademik);
        }
        
        if ($status && is_string($status)) {
            $this->db->where('js.status', $status);
        }
        
        $this->db->order_by('js.tanggal_ujian', 'ASC');
        $this->db->order_by('js.tanggal', 'ASC');
        $this->db->order_by('js.jam_mulai', 'ASC');
        
        if ($limit !== null && is_numeric($limit)) {
            $this->db->limit($limit, $offset);
        }
        
        return $this->db->get()->result();
    }

    public function count_all($id_tahun_akademik = null, $status = null) {
        $this->db->reset_query();
        $this->db->from('jadwal_skripsi js');
        $this->db->join('mahasiswa_skripsi ms', 'ms.id = js.id_mahasiswa_skripsi', 'left');
        $this->db->join('mahasiswa m', 'm.id = ms.id_mahasiswa', 'left');
        
        if ($id_tahun_akademik && is_numeric($id_tahun_akademik)) {
            $this->db->where('js.id_tahun_akademik', $id_tahun_akademik);
        }
        
        if ($status && is_string($status)) {
            $this->db->where('js.status', $status);
        }
        
        return $this->db->count_all_results();
    }

    /**
     * Cek bentrok jadwal (ruangan, waktu, atau penguji)
     * @param string $tanggal_ujian
     * @param string $jam_mulai
     * @param string $jam_selesai
     * @param string $ruangan
     * @param int $id_tahun_akademik
     * @param int|null $exclude_id ID jadwal yang sedang diedit (untuk exclude dari pengecekan)
     * @param array|null $penguji_ids Array berisi [id_ketua, id_anggota_1, id_anggota_2]
     * @return array Array dengan key 'bentrok_ruang', 'bentrok_penguji', dan detailnya
     */
    public function check_bentrok($tanggal_ujian, $jam_mulai, $jam_selesai, $ruangan, $id_tahun_akademik, $exclude_id = null, $penguji_ids = null) {
        $result = array(
            'bentrok_ruang' => false,
            'bentrok_penguji' => false,
            'detail_ruang' => null,
            'detail_penguji' => array()
        );
        
        // Cek bentrok ruangan
        if (!empty($ruangan)) {
            $this->db->where('tanggal_ujian', $tanggal_ujian);
            $this->db->where('ruangan', $ruangan);
            $this->db->where('id_tahun_akademik', $id_tahun_akademik);
            $this->db->where("jam_mulai < '{$jam_selesai}'", NULL, FALSE);
            $this->db->where("jam_selesai > '{$jam_mulai}'", NULL, FALSE);
            $this->db->where('status !=', 'batal'); // Exclude jadwal yang dibatalkan
            
            if ($exclude_id) {
                $this->db->where('id !=', $exclude_id);
            }
            
            $query = $this->db->get('jadwal_skripsi');
            if ($query->num_rows() > 0) {
                $result['bentrok_ruang'] = true;
                $result['detail_ruang'] = $query->row();
            }
        }
        
        // Cek bentrok penguji
        if (!empty($penguji_ids) && is_array($penguji_ids)) {
            list($id_ketua, $id_anggota_1, $id_anggota_2) = $penguji_ids;
            
            $penguji_list = array_filter(array($id_ketua, $id_anggota_1, $id_anggota_2));
            
            foreach ($penguji_list as $penguji_id) {
                if (!$penguji_id) continue;
                
                $this->db->where('tanggal_ujian', $tanggal_ujian);
                $this->db->where('id_tahun_akademik', $id_tahun_akademik);
                $this->db->where("jam_mulai < '{$jam_selesai}'", NULL, FALSE);
                $this->db->where("jam_selesai > '{$jam_mulai}'", NULL, FALSE);
                $this->db->where('status !=', 'batal');
                $this->db->group_start();
                $this->db->where('id_ketua_penguji', $penguji_id);
                $this->db->or_where('id_anggota_penguji_1', $penguji_id);
                $this->db->or_where('id_anggota_penguji_2', $penguji_id);
                $this->db->group_end();
                
                if ($exclude_id) {
                    $this->db->where('id !=', $exclude_id);
                }
                
                $query = $this->db->get('jadwal_skripsi');
                if ($query->num_rows() > 0) {
                    $result['bentrok_penguji'] = true;
                    $result['detail_penguji'][] = array(
                        'id_dosen' => $penguji_id,
                        'jadwal' => $query->row()
                    );
                }
            }
        }
        
        return $result;
    }
}
