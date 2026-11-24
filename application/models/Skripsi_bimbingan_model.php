<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Skripsi_bimbingan_model extends CI_Model {

    public function __construct() {
        parent::__construct();
        $this->load->database();
    }

    public function create($data) {
        $this->db->insert('skripsi_bimbingan', $data);
        return $this->db->insert_id();
    }

    public function update($id, $data) {
        $this->db->where('id', $id);
        $this->db->update('skripsi_bimbingan', $data);
        return $this->db->affected_rows();
    }

    public function delete($id) {
        // Hapus log bimbingan terlebih dahulu
        $this->db->where('id_bimbingan', $id);
        $this->db->delete('skripsi_bimbingan_log');
        
        // Hapus bimbingan
        $this->db->where('id', $id);
        return $this->db->delete('skripsi_bimbingan');
    }

    public function get_by_id($id) {
        $this->db->select('sb.*, m.nim, m.nama_mahasiswa, m.kelas, d.nama_dosen, d.nidn');
        $this->db->from('skripsi_bimbingan sb');
        $this->db->join('mahasiswa m', 'm.id = sb.id_mahasiswa', 'left');
        $this->db->join('dosen d', 'd.id = sb.id_pembimbing', 'left');
        $this->db->where('sb.id', $id);
        return $this->db->get()->row();
    }

    public function get_by_mahasiswa($id_mahasiswa, $id_tahun_akademik = null, $status = null) {
        $this->db->select('sb.*, d.nama_dosen, d.nidn');
        $this->db->from('skripsi_bimbingan sb');
        $this->db->join('dosen d', 'd.id = sb.id_pembimbing', 'left');
        $this->db->where('sb.id_mahasiswa', $id_mahasiswa);
        if ($id_tahun_akademik) {
            $this->db->where('sb.id_tahun_akademik', $id_tahun_akademik);
        }
        if ($status) {
            $this->db->where('sb.status', $status);
        }
        $this->db->order_by('sb.last_activity', 'DESC');
        return $this->db->get()->result();
    }

    public function get_by_pembimbing($id_pembimbing, $status = null, $id_tahun_akademik = null) {
        $this->db->select('sb.*, m.nim, m.nama_mahasiswa, m.kelas');
        $this->db->from('skripsi_bimbingan sb');
        $this->db->join('mahasiswa m', 'm.id = sb.id_mahasiswa', 'left');
        $this->db->where('sb.id_pembimbing', $id_pembimbing);
        if ($status) {
            $this->db->where('sb.status', $status);
        }
        if ($id_tahun_akademik) {
            $this->db->where('sb.id_tahun_akademik', $id_tahun_akademik);
        }
        $this->db->order_by('sb.last_activity', 'DESC');
        return $this->db->get()->result();
    }

    public function get_by_mahasiswa_and_pembimbing($id_mahasiswa, $id_pembimbing, $id_tahun_akademik = null) {
        $this->db->select('sb.*');
        $this->db->from('skripsi_bimbingan sb');
        $this->db->where('sb.id_mahasiswa', $id_mahasiswa);
        $this->db->where('sb.id_pembimbing', $id_pembimbing);
        if ($id_tahun_akademik) {
            $this->db->where('sb.id_tahun_akademik', $id_tahun_akademik);
        }
        $this->db->order_by('sb.last_activity', 'DESC');
        $this->db->limit(1);
        return $this->db->get()->row();
    }

    /**
     * Cek apakah ada topik bimbingan yang belum di-ACC oleh pembimbing tertentu
     * Topik dianggap belum di-ACC jika ada log dari mahasiswa yang belum di-ACC oleh dosen
     */
    public function has_pending_topic($id_mahasiswa, $id_pembimbing, $id_tahun_akademik = null) {
        // Cari semua bimbingan untuk mahasiswa dan pembimbing ini
        $this->db->select('sb.id as id_bimbingan');
        $this->db->from('skripsi_bimbingan sb');
        $this->db->where('sb.id_mahasiswa', $id_mahasiswa);
        $this->db->where('sb.id_pembimbing', $id_pembimbing);
        if ($id_tahun_akademik) {
            $this->db->where('sb.id_tahun_akademik', $id_tahun_akademik);
        }
        $this->db->where('sb.status', 'open');
        $bimbingan_list = $this->db->get()->result();
        
        if (empty($bimbingan_list)) {
            return false;
        }
        
        // Untuk setiap bimbingan, cek apakah ada log mahasiswa yang belum di-ACC
        foreach ($bimbingan_list as $bimbingan) {
            // Cari log dari mahasiswa untuk bimbingan ini
            $log_mahasiswa = $this->db->select('sbl.id, sbl.created_at')
                ->from('skripsi_bimbingan_log sbl')
                ->where('sbl.id_bimbingan', $bimbingan->id_bimbingan)
                ->where('sbl.role_pengguna', 'mahasiswa')
                ->where('sbl.status_revisi IS NULL')
                ->order_by('sbl.created_at', 'DESC')
                ->limit(1)
                ->get()
                ->row();
            
            if ($log_mahasiswa) {
                // Cek apakah sudah ada log ACC dari dosen untuk log mahasiswa ini atau setelahnya
                $log_acc_dosen = $this->db->select('sbl.id')
                    ->from('skripsi_bimbingan_log sbl')
                    ->where('sbl.id_bimbingan', $bimbingan->id_bimbingan)
                    ->where('sbl.role_pengguna', 'dosen')
                    ->where('sbl.status_revisi', 'acc')
                    ->where('sbl.created_at >=', $log_mahasiswa->created_at) // Log dosen harus setelah atau sama dengan log mahasiswa
                    ->limit(1)
                    ->get()
                    ->row();
                
                // Jika belum ada log ACC dari dosen, cek apakah ada log revisi
                if (!$log_acc_dosen) {
                    // Cek apakah ada log revisi dari dosen
                    $log_revisi_dosen = $this->db->select('sbl.id, sbl.created_at')
                        ->from('skripsi_bimbingan_log sbl')
                        ->where('sbl.id_bimbingan', $bimbingan->id_bimbingan)
                        ->where('sbl.role_pengguna', 'dosen')
                        ->where('sbl.status_revisi', 'revisi')
                        ->where('sbl.created_at >=', $log_mahasiswa->created_at)
                        ->order_by('sbl.created_at', 'DESC')
                        ->limit(1)
                        ->get()
                        ->row();
                    
                    if ($log_revisi_dosen && isset($log_revisi_dosen->created_at)) {
                        // Jika ada revisi, cek apakah sudah ada perbaikan dari mahasiswa setelah revisi
                        $log_perbaikan_mahasiswa = $this->db->select('sbl.id, sbl.created_at')
                            ->from('skripsi_bimbingan_log sbl')
                            ->where('sbl.id_bimbingan', $bimbingan->id_bimbingan)
                            ->where('sbl.role_pengguna', 'mahasiswa')
                            ->where('sbl.created_at >', $log_revisi_dosen->created_at)
                            ->order_by('sbl.created_at', 'ASC')
                            ->limit(1)
                            ->get()
                            ->row();
                        
                        // Jika belum ada perbaikan dari mahasiswa setelah revisi, berarti masih pending
                        if (!$log_perbaikan_mahasiswa) {
                            return true;
                        }
                        
                        // Jika sudah ada perbaikan, cek apakah perbaikan sudah di-ACC
                        if (isset($log_perbaikan_mahasiswa->created_at)) {
                            $log_acc_perbaikan = $this->db->select('sbl.id')
                                ->from('skripsi_bimbingan_log sbl')
                                ->where('sbl.id_bimbingan', $bimbingan->id_bimbingan)
                                ->where('sbl.role_pengguna', 'dosen')
                                ->where('sbl.status_revisi', 'acc')
                                ->where('sbl.created_at >=', $log_perbaikan_mahasiswa->created_at)
                                ->limit(1)
                                ->get()
                                ->row();
                            
                            // Jika perbaikan belum di-ACC, berarti masih pending
                            if (!$log_acc_perbaikan) {
                                return true;
                            }
                        } else {
                            // Jika tidak ada created_at, berarti masih pending
                            return true;
                        }
                        
                    } else {
                        // Jika tidak ada revisi dan tidak ada ACC, berarti masih pending
                        return true;
                    }
                }
            }
        }
        
        return false;
    }
}

