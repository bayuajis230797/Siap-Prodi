<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Tahun_akademik_model extends CI_Model {

    public function __construct() {
        parent::__construct();
        $this->load->database();
    }

    public function get_all($limit = null, $offset = null) {
        $this->db->order_by('tahun_akademik', 'DESC');
        $this->db->order_by('semester', 'DESC');
        if ($limit !== null) {
            $this->db->limit($limit, $offset);
        }
        $query = $this->db->get('tahun_akademik');
        return $query->result();
    }

    public function count_all() {
        return $this->db->count_all_results('tahun_akademik');
    }

    public function get_by_id($id) {
        $this->db->where('id', $id);
        $query = $this->db->get('tahun_akademik');
        return $query->row();
    }

    /**
     * Tahun akademik RESMI (is_aktif = 1) - satu untuk seluruh sistem.
     * Dipakai untuk operasi resmi: aktivasi, kenaikan kelas, mahasiswa baru/import.
     */
    public function get_aktif_resmi() {
        $this->db->where('is_aktif', 1);
        $this->db->limit(1);
        $query = $this->db->get('tahun_akademik');
        return $query->row();
    }

    /**
     * Tahun akademik KONTEKS sesi ini: tahun akademik yang sedang dilihat (mode lihat) bila ada,
     * selain itu tahun akademik resmi. Seluruh modul memakai fungsi ini sehingga otomatis mengikuti mode lihat.
     */
    public function get_aktif() {
        $lihat = $this->get_tahun_lihat();
        return $lihat ? $lihat : $this->get_aktif_resmi();
    }

    /**
     * Tahun akademik yang sedang dilihat sesi ini (null bila tidak dalam mode lihat,
     * id tidak valid, atau sama dengan tahun akademik resmi).
     */
    public function get_tahun_lihat() {
        $id = $this->get_lihat_id();
        if (!$id) {
            return null;
        }
        $this->db->where('id', $id);
        $ta = $this->db->get('tahun_akademik')->row();
        if (!$ta || (int) $ta->is_aktif === 1) {
            return null;
        }
        return $ta;
    }

    /** True bila sesi ini sedang dalam mode lihat tahun akademik selain yang resmi. */
    public function sedang_mode_lihat() {
        return $this->get_tahun_lihat() !== null;
    }

    /** ID tahun akademik yang dipilih untuk mode lihat pada sesi ini (tanpa validasi), atau null. */
    public function get_lihat_id() {
        $CI =& get_instance();
        if (!isset($CI->session) || !is_object($CI->session)) {
            return null; // CLI / tanpa sesi: selalu tahun akademik resmi
        }
        $id = (int) $CI->session->userdata('ta_lihat_id');
        return $id > 0 ? $id : null;
    }

    /** Masuk mode lihat tahun akademik $id (hanya bila ada). @return bool */
    public function set_tahun_lihat($id) {
        $CI =& get_instance();
        $this->db->where('id', (int) $id);
        $ta = $this->db->get('tahun_akademik')->row();
        if (!$ta || !isset($CI->session) || !is_object($CI->session)) {
            return false;
        }
        if ((int) $ta->is_aktif === 1) {
            $CI->session->unset_userdata('ta_lihat_id'); // memilih yang resmi = keluar mode lihat
        } else {
            $CI->session->set_userdata('ta_lihat_id', (int) $ta->id);
        }
        return true;
    }

    /** Keluar dari mode lihat. */
    public function keluar_mode_lihat() {
        $CI =& get_instance();
        if (isset($CI->session) && is_object($CI->session)) {
            $CI->session->unset_userdata('ta_lihat_id');
        }
    }

    public function create($data) {
        $this->db->insert('tahun_akademik', $data);
        return $this->db->insert_id();
    }

    public function update($id, $data) {
        $this->db->where('id', $id);
        $this->db->update('tahun_akademik', $data);
        return $this->db->affected_rows();
    }

    public function deactivate_all() {
        $this->db->update('tahun_akademik', array('is_aktif' => 0));
        return $this->db->affected_rows();
    }

    public function activate($id) {
        // Nonaktifkan semua dulu
        $this->deactivate_all();
        
        // Aktifkan yang dipilih
        $this->db->where('id', $id);
        $this->db->update('tahun_akademik', array('is_aktif' => 1));
        return $this->db->affected_rows();
    }

    public function delete($id) {
        $this->db->where('id', $id);
        $this->db->delete('tahun_akademik');
        return $this->db->affected_rows();
    }
}

