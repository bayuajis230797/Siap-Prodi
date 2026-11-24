<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Skpi_peserta_model extends CI_Model {

    public function __construct() {
        parent::__construct();
        $this->load->database();
    }

    public function is_peserta($id_periode, $id_mahasiswa) {
        $this->db->where('id_skpi_periode', (int) $id_periode);
        $this->db->where('id_mahasiswa', (int) $id_mahasiswa);
        return $this->db->count_all_results('skpi_peserta') > 0;
    }

    public function get_ids_by_periode($id_periode) {
        $this->db->select('id_mahasiswa');
        $this->db->where('id_skpi_periode', (int) $id_periode);
        $rows = $this->db->get('skpi_peserta')->result();
        $ids = array();
        foreach ($rows as $r) {
            $ids[] = (int) $r->id_mahasiswa;
        }
        return $ids;
    }

    public function sync_peserta($id_periode, $mahasiswa_ids) {
        $id_periode = (int) $id_periode;
        $this->db->where('id_skpi_periode', $id_periode);
        $this->db->delete('skpi_peserta');

        if (empty($mahasiswa_ids)) {
            return true;
        }

        $batch = array();
        foreach ($mahasiswa_ids as $id) {
            $id = (int) $id;
            if ($id > 0) {
                $batch[] = array(
                    'id_skpi_periode' => $id_periode,
                    'id_mahasiswa' => $id,
                );
            }
        }
        if (!empty($batch)) {
            $this->db->insert_batch('skpi_peserta', $batch);
        }
        return true;
    }

    public function get_by_periode($id_periode, $kelas = null) {
        $this->db->select('pes.*, m.nim, m.nama_mahasiswa, m.kelas');
        $this->db->from('skpi_peserta pes');
        $this->db->join('mahasiswa m', 'm.id = pes.id_mahasiswa', 'inner');
        $this->db->where('pes.id_skpi_periode', (int) $id_periode);
        if ($kelas !== null && $kelas !== '') {
            $this->db->where('m.kelas', $kelas);
        }
        $this->db->order_by('m.kelas', 'ASC');
        $this->db->order_by('m.nim', 'ASC');
        return $this->db->get()->result();
    }
}
