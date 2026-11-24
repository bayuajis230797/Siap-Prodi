<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Tanda_terima_model extends CI_Model {

    public function __construct() {
        parent::__construct();
        $this->load->database();
    }

    public function get_all($jenis_penerima = null, $limit = null, $offset = null) {
        $this->db->from('tanda_terima tt');
        if ($jenis_penerima) {
            $this->db->where('tt.jenis_penerima', $jenis_penerima);
        }
        $this->db->order_by('tt.created_at', 'DESC');
        if ($limit !== null) {
            $this->db->limit((int) $limit, (int) $offset);
        }
        return $this->db->get()->result();
    }

    public function count_all($jenis_penerima = null) {
        if ($jenis_penerima) {
            $this->db->where('jenis_penerima', $jenis_penerima);
        }
        return $this->db->count_all_results('tanda_terima');
    }

    public function get_by_id($id) {
        $this->db->where('id', (int) $id);
        return $this->db->get('tanda_terima')->row();
    }

    public function create($data) {
        $this->db->insert('tanda_terima', $data);
        return $this->db->insert_id();
    }

    public function update($id, $data) {
        $this->db->where('id', (int) $id);
        $this->db->update('tanda_terima', $data);
        return $this->db->affected_rows();
    }

    public function delete($id) {
        $this->db->where('id', (int) $id);
        $this->db->delete('tanda_terima');
        return $this->db->affected_rows();
    }

    public function get_peserta_by_distribusi($id_tanda_terima) {
        $tt = $this->get_by_id($id_tanda_terima);
        if (!$tt) {
            return array();
        }
        if ($tt->jenis_penerima === 'dosen') {
            $this->db->select('tp.*, d.nidn, d.nama_dosen');
            $this->db->from('tanda_terima_peserta tp');
            $this->db->join('dosen d', 'd.id = tp.id_dosen', 'inner');
            $this->db->where('tp.id_tanda_terima', (int) $id_tanda_terima);
            $this->db->order_by('d.nama_dosen', 'ASC');
            return $this->db->get()->result();
        }

        if ($tt->jenis_penerima === 'campuran') {
            $out = array();
            $this->db->select('tp.*, m.nim, m.nama_mahasiswa, m.kelas');
            $this->db->from('tanda_terima_peserta tp');
            $this->db->join('mahasiswa m', 'm.id = tp.id_mahasiswa', 'inner');
            $this->db->where('tp.id_tanda_terima', (int) $id_tanda_terima);
            $this->db->order_by('m.kelas', 'ASC');
            $this->db->order_by('m.nim', 'ASC');
            foreach ($this->db->get()->result() as $row) {
                $row->tipe_penerima = 'mahasiswa';
                $out[] = $row;
            }
            $this->db->select('tp.*, d.nidn, d.nama_dosen');
            $this->db->from('tanda_terima_peserta tp');
            $this->db->join('dosen d', 'd.id = tp.id_dosen', 'inner');
            $this->db->where('tp.id_tanda_terima', (int) $id_tanda_terima);
            $this->db->order_by('d.nama_dosen', 'ASC');
            foreach ($this->db->get()->result() as $row) {
                $row->tipe_penerima = 'dosen';
                $out[] = $row;
            }
            return $out;
        }

        $this->db->select('tp.*, m.nim, m.nama_mahasiswa, m.kelas');
        $this->db->from('tanda_terima_peserta tp');
        $this->db->join('mahasiswa m', 'm.id = tp.id_mahasiswa', 'inner');
        $this->db->where('tp.id_tanda_terima', (int) $id_tanda_terima);
        $this->db->order_by('m.kelas', 'ASC');
        $this->db->order_by('m.nim', 'ASC');
        return $this->db->get()->result();
    }

    /**
     * Mahasiswa aktif dikelompokkan per kelas (untuk form pilih penerima).
     *
     * @return array<string, array>
     */
    public function get_mahasiswa_aktif_grouped_by_kelas() {
        $this->db->where('status', 'aktif');
        $this->db->order_by('kelas', 'ASC');
        $this->db->order_by('nim', 'ASC');
        $rows = $this->db->get('mahasiswa')->result();
        $grouped = array();
        foreach ($rows as $m) {
            $kelas = $m->kelas ? $m->kelas : 'Tanpa Kelas';
            if (!isset($grouped[$kelas])) {
                $grouped[$kelas] = array();
            }
            $grouped[$kelas][] = $m;
        }
        return $grouped;
    }

    /**
     * Simpan penerima campuran (mahasiswa + dosen terpilih).
     */
    public function save_peserta_campuran($id_tanda_terima, $mahasiswa_ids, $dosen_ids) {
        $this->delete_peserta($id_tanda_terima);
        $batch = array();
        foreach ($mahasiswa_ids as $id) {
            $id = (int) $id;
            if ($id > 0) {
                $batch[] = array(
                    'id_tanda_terima' => (int) $id_tanda_terima,
                    'id_mahasiswa' => $id,
                    'id_dosen' => null,
                );
            }
        }
        foreach ($dosen_ids as $id) {
            $id = (int) $id;
            if ($id > 0) {
                $batch[] = array(
                    'id_tanda_terima' => (int) $id_tanda_terima,
                    'id_mahasiswa' => null,
                    'id_dosen' => $id,
                );
            }
        }
        if (!empty($batch)) {
            $this->db->insert_batch('tanda_terima_peserta', $batch);
        }
        return true;
    }

    public function is_peserta($id_tanda_terima, $id_mahasiswa = null, $id_dosen = null) {
        $this->db->where('id_tanda_terima', (int) $id_tanda_terima);
        if ($id_mahasiswa) {
            $this->db->where('id_mahasiswa', (int) $id_mahasiswa);
        }
        if ($id_dosen) {
            $this->db->where('id_dosen', (int) $id_dosen);
        }
        return $this->db->count_all_results('tanda_terima_peserta') > 0;
    }

    public function delete_peserta($id_tanda_terima, $id_mahasiswa = null, $id_dosen = null) {
        $this->db->where('id_tanda_terima', (int) $id_tanda_terima);
        if ($id_mahasiswa) {
            $this->db->where('id_mahasiswa', (int) $id_mahasiswa);
        }
        if ($id_dosen) {
            $this->db->where('id_dosen', (int) $id_dosen);
        }
        if (!$id_mahasiswa && !$id_dosen) {
            $this->db->where('id_tanda_terima', (int) $id_tanda_terima);
        }
        $this->db->delete('tanda_terima_peserta');
        return $this->db->affected_rows();
    }

    public function bulk_add_peserta_mahasiswa($id_tanda_terima, $ids) {
        $this->delete_peserta($id_tanda_terima);
        if (empty($ids)) {
            return true;
        }
        $batch = array();
        foreach ($ids as $id) {
            $batch[] = array(
                'id_tanda_terima' => (int) $id_tanda_terima,
                'id_mahasiswa' => (int) $id,
                'id_dosen' => null,
            );
        }
        $this->db->insert_batch('tanda_terima_peserta', $batch);
        return true;
    }

    public function bulk_add_peserta_dosen($id_tanda_terima, $ids) {
        $this->delete_peserta($id_tanda_terima);
        if (empty($ids)) {
            return true;
        }
        $batch = array();
        foreach ($ids as $id) {
            $batch[] = array(
                'id_tanda_terima' => (int) $id_tanda_terima,
                'id_mahasiswa' => null,
                'id_dosen' => (int) $id,
            );
        }
        $this->db->insert_batch('tanda_terima_peserta', $batch);
        return true;
    }

    public function add_peserta_from_kelas($id_tanda_terima, $kelas_list) {
        if (empty($kelas_list)) {
            return 0;
        }
        $this->db->select('id');
        $this->db->from('mahasiswa');
        $this->db->where('status', 'aktif');
        $this->db->where_in('kelas', $kelas_list);
        $rows = $this->db->get()->result();
        $added = 0;
        foreach ($rows as $r) {
            $this->db->where('id_tanda_terima', (int) $id_tanda_terima);
            $this->db->where('id_mahasiswa', (int) $r->id);
            if ($this->db->count_all_results('tanda_terima_peserta') > 0) {
                continue;
            }
            $this->db->insert('tanda_terima_peserta', array(
                'id_tanda_terima' => (int) $id_tanda_terima,
                'id_mahasiswa' => (int) $r->id,
                'id_dosen' => null,
            ));
            $added++;
        }
        return $added;
    }

    public function count_peserta($id_tanda_terima) {
        $this->db->where('id_tanda_terima', (int) $id_tanda_terima);
        return $this->db->count_all_results('tanda_terima_peserta');
    }

    /**
     * Daftar distribusi untuk penerima (mahasiswa/dosen) beserta status per item.
     */
    public function get_for_penerima($jenis_penerima, $id_mahasiswa = null, $id_dosen = null) {
        $this->db->select('tt.*');
        $this->db->from('tanda_terima tt');
        $this->db->join('tanda_terima_peserta tp', 'tp.id_tanda_terima = tt.id', 'inner');
        if ($jenis_penerima === 'mahasiswa') {
            $this->db->group_start();
            $this->db->where_in('tt.jenis_penerima', array('mahasiswa', 'campuran'));
            $this->db->group_end();
            if ($id_mahasiswa) {
                $this->db->where('tp.id_mahasiswa', (int) $id_mahasiswa);
            }
        } elseif ($jenis_penerima === 'dosen') {
            $this->db->group_start();
            $this->db->where_in('tt.jenis_penerima', array('dosen', 'campuran'));
            $this->db->group_end();
            if ($id_dosen) {
                $this->db->where('tp.id_dosen', (int) $id_dosen);
            }
        } else {
            $this->db->where('tt.jenis_penerima', $jenis_penerima);
        }
        $this->db->order_by('tt.tanggal_mulai', 'DESC');
        $this->db->order_by('tt.id', 'DESC');
        return $this->db->get()->result();
    }
}
