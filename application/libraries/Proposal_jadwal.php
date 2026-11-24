<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Proposal_jadwal
 * Validasi tanggal/jam/ruangan, penguji, dan bentrok untuk jadwal sidang Proposal TA.
 * Dipakai bersama oleh verifikasi berkas (Sekprodi) dan edit jadwal (Sekprodi).
 */
class Proposal_jadwal {

    protected $CI;

    public function __construct() {
        $this->CI =& get_instance();
        $this->CI->load->model('Jadwal_proposal_model');
        $this->CI->load->model('Dosen_model');
    }

    /**
     * Validasi isian waktu dan ruangan.
     * @param bool $boleh_lampau izinkan tanggal sebelum hari ini (untuk koreksi data)
     * @return array ['errors' => string[], 'data' => array tanggal_ujian, jam_mulai, jam_selesai, ruangan]
     */
    public function validasi_waktu($post, $boleh_lampau = false) {
        $err = array();
        $tanggal = trim((string) ($post['tanggal_ujian'] ?? ''));
        $mulai = trim((string) ($post['jam_mulai'] ?? ''));
        $selesai = trim((string) ($post['jam_selesai'] ?? ''));
        $ruangan = trim((string) ($post['ruangan'] ?? ''));

        $ok_tgl = preg_match('/^(\d{4})-(\d{2})-(\d{2})$/', $tanggal, $m) && checkdate((int) $m[2], (int) $m[3], (int) $m[1]);
        if (!$ok_tgl) {
            $err[] = 'Tanggal ujian tidak valid.';
        } elseif (!$boleh_lampau && $tanggal < date('Y-m-d')) {
            $err[] = 'Tanggal ujian tidak boleh sebelum hari ini.';
        }

        $re_jam = '/^([01]\d|2[0-3]):([0-5]\d)(:[0-5]\d)?$/';
        $ok_mulai = (bool) preg_match($re_jam, $mulai);
        $ok_selesai = (bool) preg_match($re_jam, $selesai);
        if (!$ok_mulai) { $err[] = 'Jam mulai tidak valid.'; }
        if (!$ok_selesai) { $err[] = 'Jam selesai tidak valid.'; }
        if ($ok_mulai) { $mulai = substr($mulai . ':00', 0, 8); }
        if ($ok_selesai) { $selesai = substr($selesai . ':00', 0, 8); }
        if ($ok_mulai && $ok_selesai && $selesai <= $mulai) {
            $err[] = 'Jam selesai harus setelah jam mulai.';
        }

        if ($ruangan === '') {
            $err[] = 'Ruangan wajib diisi.';
        } elseif (mb_strlen($ruangan) > 50) {
            $err[] = 'Ruangan maksimal 50 karakter.';
        }

        return array('errors' => $err, 'data' => array(
            'tanggal_ujian' => $tanggal, 'jam_mulai' => $mulai, 'jam_selesai' => $selesai, 'ruangan' => $ruangan
        ));
    }

    /**
     * Validasi Penguji 1 dan Penguji 2.
     * Aturan: wajib keduanya, dosen aktif, berbeda. Penguji 1 bukan pembimbing; Penguji 2 secara bawaan adalah
     * pembimbing mahasiswa tetapi boleh diganti dosen lain.
     * @param int|null $id_pembimbing
     * @return array ['errors' => string[], 'ids' => [id1, id2]]
     */
    public function validasi_penguji($post, $id_pembimbing) {
        $err = array();
        $p1 = (int) ($post['id_penguji_1'] ?? 0);
        $p2 = (int) ($post['id_penguji_2'] ?? 0);

        if ($p1 <= 0 || $p2 <= 0) {
            $err[] = 'Penguji 1 dan Penguji 2 wajib dipilih.';
        } else {
            if ($p1 === $p2) {
                $err[] = 'Penguji 1 dan Penguji 2 tidak boleh orang yang sama.';
            }
            foreach (array('Penguji 1' => $p1, 'Penguji 2' => $p2) as $label => $id) {
                $d = $this->CI->Dosen_model->get_by_id($id);
                if (!$d || $d->status !== 'aktif') {
                    $err[] = $label . ' bukan dosen aktif.';
                }
                if ($label === 'Penguji 1' && $id_pembimbing && (int) $id_pembimbing === $id) {
                    $err[] = $label . ' tidak boleh pembimbing mahasiswa ini (pembimbing berperan sebagai Penguji 2).';
                }
            }
        }
        return array('errors' => $err, 'ids' => array($p1, $p2));
    }

    /**
     * Cek bentrok ruangan dan (bila diberikan) penguji. Null bila aman.
     * @param int|null $exclude_id jadwal yang sedang diedit
     * @param int[]|null $penguji_ids [id1, id2]
     */
    public function bentrok($data, $id_tahun, $exclude_id = null, $penguji_ids = null) {
        $b = $this->CI->Jadwal_proposal_model->check_bentrok(
            $data['tanggal_ujian'], $data['jam_mulai'], $data['jam_selesai'], $data['ruangan'],
            $id_tahun, $exclude_id, $penguji_ids
        );

        if (!empty($b['bentrok_ruang'])) {
            return "Ruangan '{$data['ruangan']}' pada " . date('d/m/Y', strtotime($data['tanggal_ujian']))
                . ' pukul ' . substr($data['jam_mulai'], 0, 5) . '-' . substr($data['jam_selesai'], 0, 5) . ' sudah digunakan.';
        }
        if (!empty($b['bentrok_penguji'])) {
            $nama = array();
            foreach ($b['detail_penguji'] as $d) {
                $dosen = $this->CI->Dosen_model->get_by_id($d['id_dosen']);
                $nama[] = $dosen ? $dosen->nama_dosen : ('Dosen #' . $d['id_dosen']);
            }
            return 'Penguji bentrok pada ' . date('d/m/Y', strtotime($data['tanggal_ujian'])) . ': ' . implode(', ', array_unique($nama)) . ' sudah memiliki jadwal pada jam tersebut.';
        }
        return null;
    }
}
