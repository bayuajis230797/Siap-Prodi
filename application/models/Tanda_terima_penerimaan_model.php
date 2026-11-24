<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Tanda_terima_penerimaan_model extends CI_Model {

    public function __construct() {
        parent::__construct();
        $this->load->database();
    }

    public function create($data) {
        $this->db->insert('tanda_terima_penerimaan', $data);
        return $this->db->insert_id();
    }

    public function sudah_terima($id_item, $id_mahasiswa = null, $id_dosen = null) {
        $this->db->where('id_tanda_terima_item', (int) $id_item);
        if ($id_mahasiswa) {
            $this->db->where('id_mahasiswa', (int) $id_mahasiswa);
        }
        if ($id_dosen) {
            $this->db->where('id_dosen', (int) $id_dosen);
        }
        return $this->db->count_all_results('tanda_terima_penerimaan') > 0;
    }

    /**
     * Hapus catatan penerimaan untuk item + penerima.
     */
    public function delete_by_penerima($id_item, $id_mahasiswa = null, $id_dosen = null) {
        $this->db->where('id_tanda_terima_item', (int) $id_item);
        if ($id_mahasiswa) {
            $this->db->where('id_mahasiswa', (int) $id_mahasiswa);
        } elseif ($id_dosen) {
            $this->db->where('id_dosen', (int) $id_dosen);
        } else {
            return 0;
        }
        $this->db->delete('tanda_terima_penerimaan');
        return $this->db->affected_rows();
    }

    public function get_by_item($id_item) {
        $this->db->select('tp.*, m.nim, m.nama_mahasiswa, m.kelas, d.nidn, d.nama_dosen');
        $this->db->from('tanda_terima_penerimaan tp');
        $this->db->join('mahasiswa m', 'm.id = tp.id_mahasiswa', 'left');
        $this->db->join('dosen d', 'd.id = tp.id_dosen', 'left');
        $this->db->where('tp.id_tanda_terima_item', (int) $id_item);
        $this->db->order_by('tp.waktu_terima', 'DESC');
        return $this->db->get()->result();
    }

    public function get_rekap_distribusi($id_tanda_terima, $jenis_penerima) {
        $items = $this->db->where('id_tanda_terima', (int) $id_tanda_terima)
            ->order_by('nama_barang', 'ASC')
            ->get('tanda_terima_item')->result();

        $peserta = array();
        if ($jenis_penerima === 'dosen' || $jenis_penerima === 'campuran') {
            $this->db->select('tp.id_dosen as ref_id, d.nidn as kode, d.nama_dosen as nama');
            $this->db->select("'dosen' as tipe_penerima", false);
            $this->db->from('tanda_terima_peserta tp');
            $this->db->join('dosen d', 'd.id = tp.id_dosen', 'inner');
            $this->db->where('tp.id_tanda_terima', (int) $id_tanda_terima);
            $this->db->where('tp.id_dosen IS NOT NULL', null, false);
            foreach ($this->db->get()->result() as $row) {
                $row->ref_key = 'd' . (int) $row->ref_id;
                $peserta[] = $row;
            }
        }
        if ($jenis_penerima === 'mahasiswa' || $jenis_penerima === 'campuran') {
            $this->db->select('tp.id_mahasiswa as ref_id, m.nim as kode, m.nama_mahasiswa as nama, m.kelas');
            $this->db->select("'mahasiswa' as tipe_penerima", false);
            $this->db->from('tanda_terima_peserta tp');
            $this->db->join('mahasiswa m', 'm.id = tp.id_mahasiswa', 'inner');
            $this->db->where('tp.id_tanda_terima', (int) $id_tanda_terima);
            $this->db->where('tp.id_mahasiswa IS NOT NULL', null, false);
            foreach ($this->db->get()->result() as $row) {
                $row->ref_key = 'm' . (int) $row->ref_id;
                $peserta[] = $row;
            }
        }

        $terima_map = array();
        if (!empty($items)) {
            $item_ids = array_map(function ($i) { return (int) $i->id; }, $items);
            $this->db->where_in('id_tanda_terima_item', $item_ids);
            $rows = $this->db->get('tanda_terima_penerimaan')->result();
            foreach ($rows as $r) {
                $suffix = $r->id_mahasiswa ? ('m' . (int) $r->id_mahasiswa) : ('d' . (int) $r->id_dosen);
                $key = (int) $r->id_tanda_terima_item . '_' . $suffix;
                $terima_map[$key] = $r;
            }
        }

        return array('items' => $items, 'peserta' => $peserta, 'terima_map' => $terima_map);
    }

    public function get_status_items_for_penerima($id_tanda_terima, $id_mahasiswa = null, $id_dosen = null) {
        $items = $this->db->where('id_tanda_terima', (int) $id_tanda_terima)
            ->where('status', 'aktif')
            ->order_by('nama_barang', 'ASC')
            ->get('tanda_terima_item')->result();
        foreach ($items as $it) {
            $this->db->where('id_tanda_terima_item', (int) $it->id);
            if ($id_mahasiswa) {
                $this->db->where('id_mahasiswa', (int) $id_mahasiswa);
            }
            if ($id_dosen) {
                $this->db->where('id_dosen', (int) $id_dosen);
            }
            $row = $this->db->get('tanda_terima_penerimaan')->row();
            $it->waktu_terima = $row ? $row->waktu_terima : null;
        }
        return $items;
    }
}
