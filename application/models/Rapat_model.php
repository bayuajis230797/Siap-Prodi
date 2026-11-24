<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Rapat_model extends CI_Model {

    public function __construct() {
        parent::__construct();
        $this->load->database();
    }

    // ==================== RAPAT ====================
    
    public function get_all($limit = null, $offset = null) {
        $this->db->order_by('tanggal_rapat', 'DESC');
        $this->db->order_by('jam_mulai', 'DESC');
        if ($limit !== null) {
            $this->db->limit($limit, $offset);
        }
        return $this->db->get('rapat')->result();
    }

    public function get_by_id($id) {
        $this->db->where('id', $id);
        return $this->db->get('rapat')->row();
    }

    public function count_all() {
        return $this->db->count_all_results('rapat');
    }

    public function get_by_user_id($user_id, $limit = null, $offset = null) {
        $this->db->select('r.*');
        $this->db->from('rapat r');
        $this->db->join('rapat_peserta rp', 'rp.id_rapat = r.id', 'inner');
        $this->db->where('rp.id_user', $user_id);
        $this->db->group_by('r.id');
        $this->db->order_by('r.tanggal_rapat', 'DESC');
        $this->db->order_by('r.jam_mulai', 'DESC');
        if ($limit !== null) {
            $this->db->limit($limit, $offset);
        }
        return $this->db->get()->result();
    }

    public function count_by_user_id($user_id) {
        $this->db->select('COUNT(DISTINCT r.id) as total');
        $this->db->from('rapat r');
        $this->db->join('rapat_peserta rp', 'rp.id_rapat = r.id', 'inner');
        $this->db->where('rp.id_user', $user_id);
        $result = $this->db->get()->row();
        return $result ? (int)$result->total : 0;
    }

    public function get_peserta_by_user_and_rapat($user_id, $id_rapat) {
        $this->db->where('id_rapat', $id_rapat);
        $this->db->where('id_user', $user_id);
        return $this->db->get('rapat_peserta')->row();
    }

    public function create($data) {
        $this->db->insert('rapat', $data);
        return $this->db->insert_id();
    }

    public function update($id, $data) {
        $this->db->where('id', $id);
        $this->db->update('rapat', $data);
        return $this->db->affected_rows();
    }

    public function delete($id) {
        // Hapus dokumentasi terlebih dahulu (akan terhapus otomatis karena CASCADE)
        // Hapus peserta terlebih dahulu (akan terhapus otomatis karena CASCADE)
        $this->db->where('id', $id);
        $this->db->delete('rapat');
        return $this->db->affected_rows();
    }

    // ==================== PESERTA RAPAT ====================

    public function get_peserta_by_rapat($id_rapat) {
        $this->db->where('id_rapat', $id_rapat);
        $this->db->order_by('urutan', 'ASC');
        $this->db->order_by('id', 'ASC'); // Fallback jika urutan sama atau null
        return $this->db->get('rapat_peserta')->result();
    }

    public function get_peserta_by_id($id) {
        $this->db->where('id', $id);
        return $this->db->get('rapat_peserta')->row();
    }

    public function add_peserta($data) {
        // Jika urutan tidak diset, ambil urutan terakhir + 1
        if (!isset($data['urutan']) || $data['urutan'] === null) {
            $this->db->select_max('urutan');
            $this->db->where('id_rapat', $data['id_rapat']);
            $result = $this->db->get('rapat_peserta')->row();
            $urutan_terakhir = $result && $result->urutan ? (int)$result->urutan : 0;
            $data['urutan'] = $urutan_terakhir + 1;
        }
        
        $this->db->insert('rapat_peserta', $data);
        return $this->db->insert_id();
    }

    public function update_peserta($id, $data) {
        $this->db->where('id', $id);
        $this->db->update('rapat_peserta', $data);
        return $this->db->affected_rows();
    }

    public function delete_peserta($id) {
        $this->db->where('id', $id);
        $this->db->delete('rapat_peserta');
        return $this->db->affected_rows();
    }

    public function update_status_kehadiran($id_peserta, $status, $keterangan = null) {
        $data = array(
            'status_kehadiran' => $status
        );
        if ($keterangan !== null) {
            $data['keterangan'] = $keterangan;
        }
        $this->db->where('id', $id_peserta);
        $this->db->update('rapat_peserta', $data);
        return $this->db->affected_rows();
    }

    public function update_label_role($id_peserta, $label_role) {
        $data = array(
            'label_role' => $label_role ? trim($label_role) : null
        );
        $this->db->where('id', $id_peserta);
        $this->db->update('rapat_peserta', $data);
        return $this->db->affected_rows();
    }

    public function update_urutan($id_peserta, $urutan) {
        $data = array(
            'urutan' => $urutan ? (int)$urutan : null
        );
        $this->db->where('id', $id_peserta);
        $this->db->update('rapat_peserta', $data);
        return $this->db->affected_rows();
    }

    public function update_urutan_bulk($id_rapat, $urutan_list) {
        // urutan_list adalah array: [id_peserta => urutan]
        foreach ($urutan_list as $id_peserta => $urutan) {
            $this->db->where('id', $id_peserta);
            $this->db->where('id_rapat', $id_rapat);
            $this->db->update('rapat_peserta', array('urutan' => (int)$urutan));
        }
        return true;
    }

    public function bulk_add_peserta($id_rapat, $peserta_list) {
        // Ambil urutan terakhir untuk rapat ini
        $this->db->select_max('urutan');
        $this->db->where('id_rapat', $id_rapat);
        $result = $this->db->get('rapat_peserta')->row();
        $urutan_terakhir = $result && $result->urutan ? (int)$result->urutan : 0;
        
        $data_batch = array();
        $urutan = $urutan_terakhir + 1; // Mulai dari urutan berikutnya
        
        foreach ($peserta_list as $peserta) {
            // Pastikan id_user adalah integer atau NULL
            $id_user = null;
            if (isset($peserta['id_user']) && $peserta['id_user'] !== null && $peserta['id_user'] !== '') {
                $id_user = (int)$peserta['id_user'];
                // Jika setelah casting menjadi 0, set ke null
                if ($id_user <= 0) {
                    $id_user = null;
                }
            }
            
            $data_batch[] = array(
                'id_rapat' => (int)$id_rapat,
                'id_user' => $id_user,
                'nama_peserta' => $peserta['nama_peserta'],
                'role_peserta' => isset($peserta['role_peserta']) ? $peserta['role_peserta'] : 'lainnya',
                'status_kehadiran' => 'tidak_hadir',
                'urutan' => $urutan++
            );
        }
        if (!empty($data_batch)) {
            $this->db->insert_batch('rapat_peserta', $data_batch);
            return true;
        }
        return false;
    }

    // ==================== DOKUMENTASI RAPAT ====================

    public function get_dokumentasi_by_rapat($id_rapat) {
        $this->db->where('id_rapat', $id_rapat);
        $this->db->order_by('created_at', 'ASC');
        return $this->db->get('rapat_dokumentasi')->result();
    }

    public function get_dokumentasi_by_id($id) {
        $this->db->where('id', $id);
        return $this->db->get('rapat_dokumentasi')->row();
    }

    public function add_dokumentasi($data) {
        $this->db->insert('rapat_dokumentasi', $data);
        return $this->db->insert_id();
    }

    public function delete_dokumentasi($id) {
        // Ambil data file terlebih dahulu untuk hapus file
        $dokumentasi = $this->get_dokumentasi_by_id($id);
        if ($dokumentasi) {
            $file_path = FCPATH . $dokumentasi->file_path;
            if (file_exists($file_path)) {
                unlink($file_path);
            }
        }
        $this->db->where('id', $id);
        $this->db->delete('rapat_dokumentasi');
        return $this->db->affected_rows();
    }

    // ==================== STATISTIK ====================

    public function get_statistik_kehadiran($id_rapat) {
        $this->db->select('status_kehadiran, COUNT(*) as jumlah');
        $this->db->where('id_rapat', $id_rapat);
        $this->db->group_by('status_kehadiran');
        $result = $this->db->get('rapat_peserta')->result();
        
        $statistik = array(
            'hadir' => 0,
            'tidak_hadir' => 0,
            'izin' => 0,
            'total' => 0
        );
        
        foreach ($result as $row) {
            $statistik[$row->status_kehadiran] = (int)$row->jumlah;
            $statistik['total'] += (int)$row->jumlah;
        }
        
        return $statistik;
    }

    // ==================== HELPER ====================

    public function get_rapat_with_detail($id) {
        $rapat = $this->get_by_id($id);
        if ($rapat) {
            $rapat->peserta = $this->get_peserta_by_rapat($id);
            $rapat->dokumentasi = $this->get_dokumentasi_by_rapat($id);
            $rapat->statistik = $this->get_statistik_kehadiran($id);
        }
        return $rapat;
    }
}

