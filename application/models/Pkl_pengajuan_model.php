<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Pkl_pengajuan_model extends CI_Model {

    const PLACEHOLDER_JUDUL = 'Menunggu Pengajuan Judul';

    public function __construct() {
        parent::__construct();
        $this->load->database();
    }

    public function create($data) {
        $this->db->insert('pkl_pengajuan', $data);
        return $this->db->insert_id();
    }

    public function update($id, $data) {
        $this->db->where('id', $id);
        $this->db->update('pkl_pengajuan', $data);
        return $this->db->affected_rows();
    }

    public function delete($id) {
        $pengajuan = $this->get_by_id($id);
        
        if ($pengajuan && !empty($pengajuan->file_proposal) && file_exists($pengajuan->file_proposal)) {
            unlink($pengajuan->file_proposal);
        }
        
        if ($pengajuan && !empty($pengajuan->file_approve_revisi) && file_exists($pengajuan->file_approve_revisi)) {
            unlink($pengajuan->file_approve_revisi);
        }
        
        $this->db->where('id', $id);
        return $this->db->delete('pkl_pengajuan');
    }

    public function get_by_id($id) {
        $this->db->select('pp.*, m.nim, m.nama_mahasiswa, m.kelas, ta.tahun_akademik, ta.semester,
                          d.nama_dosen as pembimbing_nama, d.nidn as pembimbing_nidn');
        $this->db->from('pkl_pengajuan pp');
        $this->db->join('mahasiswa m', 'm.id = pp.id_mahasiswa', 'left');
        $this->db->join('tahun_akademik ta', 'ta.id = pp.id_tahun_akademik', 'left');
        $this->db->join('dosen d', 'd.id = pp.id_pembimbing', 'left');
        $this->db->where('pp.id', $id);
        return $this->db->get()->row();
    }

    public function get_by_mahasiswa($id_mahasiswa, $id_tahun_akademik = null) {
        $this->db->select('pp.*, d.nama_dosen as pembimbing_nama, d.nidn as pembimbing_nidn');
        $this->db->from('pkl_pengajuan pp');
        $this->db->join('dosen d', 'd.id = pp.id_pembimbing', 'left');
        $this->db->where('pp.id_mahasiswa', $id_mahasiswa);
        if ($id_tahun_akademik) {
            $this->db->where('pp.id_tahun_akademik', $id_tahun_akademik);
        }
        $this->db->order_by('pp.created_at', 'DESC');
        return $this->db->get()->row();
    }

    public function get_all_by_mahasiswa($id_mahasiswa, $id_tahun_akademik = null) {
        $this->db->select('pp.*, d.nama_dosen as pembimbing_nama, d.nidn as pembimbing_nidn');
        $this->db->from('pkl_pengajuan pp');
        $this->db->join('dosen d', 'd.id = pp.id_pembimbing', 'left');
        $this->db->where('pp.id_mahasiswa', $id_mahasiswa);
        if ($id_tahun_akademik) {
            $this->db->where('pp.id_tahun_akademik', $id_tahun_akademik);
        }
        $this->db->order_by('pp.created_at', 'DESC');
        return $this->db->get()->result();
    }

    public function get_all($id_tahun_akademik = null, $status = null, $id_pembimbing = null, $limit = null, $offset = null) {
        $this->db->select('pp.*, m.nim, m.nama_mahasiswa, m.kelas, ta.tahun_akademik, ta.semester,
                          d.nama_dosen as pembimbing_nama, d.nidn as pembimbing_nidn');
        $this->db->from('pkl_pengajuan pp');
        $this->db->join('mahasiswa m', 'm.id = pp.id_mahasiswa', 'left');
        $this->db->join('tahun_akademik ta', 'ta.id = pp.id_tahun_akademik', 'left');
        $this->db->join('dosen d', 'd.id = pp.id_pembimbing', 'left');
        
        if ($id_tahun_akademik) {
            $this->db->where('pp.id_tahun_akademik', $id_tahun_akademik);
        }
        
        if ($status) {
            $this->db->where('pp.status', $status);
        }
        
        if ($id_pembimbing) {
            $this->db->where('pp.id_pembimbing', $id_pembimbing);
        }
        
        $this->db->order_by('pp.created_at', 'DESC');
        
        if ($limit !== null && $offset !== null) {
            $this->db->limit($limit, $offset);
        }
        
        return $this->db->get()->result();
    }

    public function count_all($id_tahun_akademik = null, $status = null, $id_pembimbing = null) {
        $this->db->from('pkl_pengajuan');
        
        if ($id_tahun_akademik) {
            $this->db->where('id_tahun_akademik', $id_tahun_akademik);
        }
        
        if ($status) {
            $this->db->where('status', $status);
        }
        
        if ($id_pembimbing) {
            $this->db->where('id_pembimbing', $id_pembimbing);
        }
        
        return $this->db->count_all_results();
    }

    public function get_layak_sidang($id_tahun_akademik = null) {
        $this->db->select('pp.*, m.nim, m.nama_mahasiswa, m.kelas, ta.tahun_akademik, ta.semester,
                          d.nama_dosen as pembimbing_nama, d.nidn as pembimbing_nidn');
        $this->db->from('pkl_pengajuan pp');
        $this->db->join('mahasiswa m', 'm.id = pp.id_mahasiswa', 'left');
        $this->db->join('tahun_akademik ta', 'ta.id = pp.id_tahun_akademik', 'left');
        $this->db->join('dosen d', 'd.id = pp.id_pembimbing', 'left');
        $this->db->where('pp.is_layak_sidang', 1);
        $this->db->where('pp.status', 'disetujui');
        
        if ($id_tahun_akademik) {
            $this->db->where('pp.id_tahun_akademik', $id_tahun_akademik);
        }
        
        $this->db->order_by('pp.tanggal_layak_sidang', 'DESC');
        return $this->db->get()->result();
    }

    public function is_placeholder($pengajuan) {
        if (!$pengajuan) return false;
        return ($pengajuan->judul_pkl === self::PLACEHOLDER_JUDUL && empty($pengajuan->file_proposal));
    }

    /**
     * Daftar pengajuan judul yang sudah dikirim mahasiswa (bukan placeholder, belum acc)
     */
    public function get_pengajuan_judul_resettable($id_tahun_akademik = null) {
        $this->db->select('pp.*, m.nim, m.nama_mahasiswa, m.kelas, ta.tahun_akademik, ta.semester,
                          d.nama_dosen as pembimbing_nama');
        $this->db->from('pkl_pengajuan pp');
        $this->db->join('mahasiswa m', 'm.id = pp.id_mahasiswa', 'left');
        $this->db->join('tahun_akademik ta', 'ta.id = pp.id_tahun_akademik', 'left');
        $this->db->join('dosen d', 'd.id = pp.id_pembimbing', 'left');
        $this->db->where('pp.judul_pkl !=', self::PLACEHOLDER_JUDUL);
        $this->db->where('pp.judul_pkl IS NOT NULL');
        $this->db->group_start();
        $this->db->where('pp.status_acc_judul !=', 'acc');
        $this->db->or_where('pp.status_acc_judul IS NULL');
        $this->db->group_end();

        if ($id_tahun_akademik) {
            $this->db->where('pp.id_tahun_akademik', $id_tahun_akademik);
        }

        $this->db->order_by('m.kelas', 'ASC');
        $this->db->order_by('m.nim', 'ASC');
        return $this->db->get()->result();
    }

    /**
     * Reset pengajuan judul ke kondisi awal (belum mengajukan judul)
     */
    public function reset_pengajuan_judul($id) {
        $pengajuan = $this->get_by_id($id);
        if (!$pengajuan) {
            return false;
        }

        if ($pengajuan->status_acc_judul === 'acc') {
            return false;
        }

        if ($pengajuan->judul_pkl === self::PLACEHOLDER_JUDUL) {
            return false;
        }

        $data = array(
            'judul_pkl' => self::PLACEHOLDER_JUDUL,
            'temuan_masalah' => null,
            'deskripsi_pengajuan' => null,
            'file_proposal' => null,
            'status_acc_judul' => 'pending',
            'catatan_revisi' => null,
            'judul_revisi' => null,
            'file_approve_revisi' => null,
            'status_approve_revisi' => null,
        );

        return $this->update($id, $data);
    }

    /**
     * Ambil semua permintaan perubahan judul PKL untuk sekprodi
     */
    public function get_all_perubahan_judul($id_tahun_akademik = null, $status_approve_revisi = null) {
        // Gunakan pp.* untuk memastikan semua field termasuk judul_revisi terambil
        $this->db->select('pp.*, m.nim, m.nama_mahasiswa, m.kelas, ta.tahun_akademik, ta.semester,
                          d.nama_dosen as pembimbing_nama, d.nidn as pembimbing_nidn');
        $this->db->from('pkl_pengajuan pp');
        $this->db->join('mahasiswa m', 'm.id = pp.id_mahasiswa', 'left');
        $this->db->join('tahun_akademik ta', 'ta.id = pp.id_tahun_akademik', 'left');
        $this->db->join('dosen d', 'd.id = pp.id_pembimbing', 'left');

        if ($id_tahun_akademik) {
            $this->db->where('pp.id_tahun_akademik', $id_tahun_akademik);
        }
        
        // Hanya ambil yang ada pengajuan perubahan judul
        // (ada judul_revisi ATAU ada file_approve_revisi ATAU status_approve_revisi sudah di-set)
        $this->db->group_start();
        $this->db->where('pp.judul_revisi IS NOT NULL');
        $this->db->or_where('pp.file_approve_revisi IS NOT NULL');
        $this->db->or_where('pp.status_approve_revisi IS NOT NULL');
        $this->db->or_where("pp.status_approve_revisi != ''", NULL, FALSE);
        $this->db->group_end();

        if ($status_approve_revisi) {
            $this->db->where('pp.status_approve_revisi', $status_approve_revisi);
        }

        $this->db->order_by('pp.created_at', 'DESC');
        return $this->db->get()->result();
    }
}







