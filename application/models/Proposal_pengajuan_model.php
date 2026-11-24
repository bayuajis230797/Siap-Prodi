<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Proposal_pengajuan_model
 * Tabel proposal_pengajuan - pengajuan judul Proposal TA (1 pembimbing).
 */
class Proposal_pengajuan_model extends CI_Model {

    public function __construct() {
        parent::__construct();
        $this->load->database();
    }

    public function create($data) {
        $this->db->insert('proposal_pengajuan', $data);
        return $this->db->insert_id();
    }

    public function update($id, $data) {
        $this->db->where('id', $id);
        $this->db->update('proposal_pengajuan', $data);
        return $this->db->affected_rows();
    }

    public function delete($id) {
        $this->db->where('id', $id);
        return $this->db->delete('proposal_pengajuan');
    }

    private function base_select() {
        $this->db->select('sp.*,
                           m.nim, m.nama_mahasiswa, m.kelas,
                           ta.tahun_akademik, ta.semester,
                           d1.nama_dosen as pembimbing_nama, d1.nidn as pembimbing_nidn');
        $this->db->from('proposal_pengajuan sp');
        $this->db->join('mahasiswa m', 'm.id = sp.id_mahasiswa', 'left');
        $this->db->join('tahun_akademik ta', 'ta.id = sp.id_tahun_akademik', 'left');
        $this->db->join('dosen d1', 'd1.id = sp.id_pembimbing_final', 'left');
    }

    public function get_by_id($id) {
        $this->base_select();
        $this->db->select('mp.id_pembimbing, mp.judul_proposal as judul_final');
        $this->db->join('mahasiswa_proposal mp', 'mp.id_mahasiswa = sp.id_mahasiswa AND mp.id_tahun_akademik = sp.id_tahun_akademik', 'left');
        $this->db->where('sp.id', $id);
        return $this->db->get()->row();
    }

    /**
     * Pengajuan terbaru mahasiswa (opsional per tahun akademik)
     */
    public function get_latest_by_mahasiswa($id_mahasiswa, $id_tahun_akademik = null) {
        $this->base_select();
        $this->db->where('sp.id_mahasiswa', $id_mahasiswa);
        if (!empty($id_tahun_akademik)) {
            $this->db->where('sp.id_tahun_akademik', $id_tahun_akademik);
        }
        $this->db->order_by('sp.created_at', 'DESC');
        $this->db->limit(1);
        return $this->db->get()->row();
    }

    public function get_all($filters = array(), $limit = null, $offset = null) {
        $this->base_select();
        $this->db->select('COALESCE(d1.nama_dosen, d1_mp.nama_dosen) as pembimbing_nama_efektif, mp.id_pembimbing', false);
        $this->db->join('mahasiswa_proposal mp', 'mp.id_mahasiswa = sp.id_mahasiswa AND mp.id_tahun_akademik = sp.id_tahun_akademik', 'left');
        $this->db->join('dosen d1_mp', 'd1_mp.id = mp.id_pembimbing', 'left');

        if (!empty($filters['id_tahun_akademik'])) {
            $this->db->where('sp.id_tahun_akademik', $filters['id_tahun_akademik']);
        }
        if (!empty($filters['id_pembimbing'])) {
            $this->db->where('mp.id_pembimbing', $filters['id_pembimbing']);
        }
        if (!empty($filters['status_acc_judul'])) {
            $this->db->where('sp.status_acc_judul', $filters['status_acc_judul']);
        }
        if (!empty($filters['status'])) {
            $this->db->where('sp.status', $filters['status']);
        }
        if (!empty($filters['status_not'])) {
            $this->db->where('sp.status !=', $filters['status_not']);
        }
        if (!empty($filters['kelas'])) {
            $this->db->where('m.kelas', $filters['kelas']);
        }

        $this->db->order_by('sp.created_at', 'DESC');
        if ($limit !== null) {
            $this->db->limit($limit, $offset);
        }
        return $this->db->get()->result();
    }

    public function count_all($filters = array()) {
        $this->db->from('proposal_pengajuan sp');
        $this->db->join('mahasiswa m', 'm.id = sp.id_mahasiswa', 'left');

        if (!empty($filters['id_tahun_akademik'])) {
            $this->db->where('sp.id_tahun_akademik', $filters['id_tahun_akademik']);
        }
        if (!empty($filters['status'])) {
            $this->db->where('sp.status', $filters['status']);
        }
        if (!empty($filters['status_not'])) {
            $this->db->where('sp.status !=', $filters['status_not']);
        }
        if (!empty($filters['kelas'])) {
            $this->db->where('m.kelas', $filters['kelas']);
        }
        return $this->db->count_all_results();
    }

    /**
     * Pengajuan yang menunggu review ACC judul dari pembimbing tertentu
     */
    public function get_by_pembimbing_pending($id_dosen, $id_tahun_akademik = null) {
        $this->base_select();
        $this->db->where('sp.id_pembimbing_final', $id_dosen);
        $this->db->where('sp.status', 'disetujui');
        $this->db->where('sp.status_acc_judul', 'pending');
        if ($id_tahun_akademik) {
            $this->db->where('sp.id_tahun_akademik', $id_tahun_akademik);
        }
        $this->db->order_by('sp.created_at', 'DESC');
        return $this->db->get()->result();
    }

    /**
     * Semua pengajuan untuk pembimbing tertentu (tidak hanya pending)
     */
    public function get_by_pembimbing_all($id_dosen, $id_tahun_akademik = null) {
        $this->base_select();
        $this->db->where('sp.id_pembimbing_final', $id_dosen);
        if ($id_tahun_akademik) {
            $this->db->where('sp.id_tahun_akademik', $id_tahun_akademik);
        }
        $this->db->order_by('sp.created_at', 'DESC');
        return $this->db->get()->result();
    }

    /**
     * Plot (tetapkan / ganti) pembimbing untuk sebuah pengajuan judul.
     * Satu-satunya jalur penulisan pembimbing: dipakai Sekprodi saat approve
     * maupun menu Plot Pembimbing.
     *
     * @param object $pengajuan baris proposal_pengajuan (hasil get_by_id)
     * @param int    $id_dosen
     * @return array ['ok' => bool, 'message' => string]
     */
    public function plot_pembimbing($pengajuan, $id_dosen) {
        $id_dosen = (int) $id_dosen;
        if ($id_dosen <= 0) {
            return array('ok' => false, 'message' => 'Pembimbing wajib dipilih.');
        }

        $this->load->model('Proposal_pembimbing_model');
        $this->load->model('Proposal_model');
        $this->load->model('Proposal_bimbingan_model');

        if (!$this->Proposal_pembimbing_model->is_allowed($id_dosen)) {
            return array('ok' => false, 'message' => 'Dosen tersebut belum diizinkan sebagai pembimbing Proposal TA (atur di menu Admin).');
        }

        $berubah = !empty($pengajuan->id_pembimbing_final) && (int) $pengajuan->id_pembimbing_final !== $id_dosen;
        if ($berubah) {
            $bimbingan = $this->Proposal_bimbingan_model->get_by_mahasiswa($pengajuan->id_mahasiswa, $pengajuan->id_tahun_akademik);
            if (!empty($bimbingan)) {
                return array('ok' => false, 'message' => 'Pembimbing tidak dapat diganti karena bimbingan sudah dimulai.');
            }
        }

        $proposal = $this->Proposal_model->get_by_mahasiswa($pengajuan->id_mahasiswa, $pengajuan->id_tahun_akademik);
        if ($proposal) {
            $upd = array('id_pembimbing' => $id_dosen);
            if ($berubah) {
                $upd['acc_pembimbing'] = 0;
            }
            $this->Proposal_model->update($proposal->id, $upd);
        } else {
            $this->Proposal_model->create(array(
                'id_mahasiswa' => $pengajuan->id_mahasiswa,
                'id_tahun_akademik' => $pengajuan->id_tahun_akademik,
                'id_pembimbing' => $id_dosen,
                'status' => 'terdaftar'
            ));
        }

        $upd_pengajuan = array('id_pembimbing_final' => $id_dosen);
        if ($berubah) {
            // Pembimbing baru belum pernah mereview judul
            $upd_pengajuan['status_acc_judul'] = 'pending';
            $upd_pengajuan['catatan_revisi'] = null;
        }
        $this->update($pengajuan->id, $upd_pengajuan);

        return array('ok' => true, 'message' => 'Pembimbing berhasil diplot.');
    }
}
