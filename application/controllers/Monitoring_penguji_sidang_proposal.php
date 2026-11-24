<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Monitoring_penguji_sidang_proposal Controller (Sekprodi)
 * Beban penguji (Penguji 1 / Penguji 2) per dosen dan deteksi bentrok jadwal penguji.
 */
class Monitoring_penguji_sidang_proposal extends MY_Controller {

    public function __construct() {
        parent::__construct();
        $this->check_role('sekprodi');
        $this->load->model('Jadwal_proposal_model');
        $this->load->model('Dosen_model');
        $this->load->model('Tahun_akademik_model');
        $this->load->helper('app');
    }

    public function index() {
        $tahun_aktif = $this->Tahun_akademik_model->get_aktif();
        $id_dosen = (int) $this->input->get('dosen');
        $dari = $this->tanggal_valid($this->input->get('tanggal_mulai'));
        $sampai = $this->tanggal_valid($this->input->get('tanggal_selesai'));

        $baris = array();
        if ($tahun_aktif) {
            $baris = $this->susun_baris($tahun_aktif->id, $id_dosen ?: null, $dari, $sampai);
        }

        // Ringkasan per dosen
        $ringkas = array();
        foreach ($baris as $b) {
            if (!isset($ringkas[$b['id_dosen']])) {
                $ringkas[$b['id_dosen']] = array('nama' => $b['dosen'], 'nidn' => $b['nidn'], 'penguji_1' => 0, 'penguji_2' => 0, 'total' => 0, 'bentrok' => 0);
            }
            $ringkas[$b['id_dosen']][$b['peran'] === 'Penguji 1' ? 'penguji_1' : 'penguji_2']++;
            $ringkas[$b['id_dosen']]['total']++;
            if ($b['bentrok']) { $ringkas[$b['id_dosen']]['bentrok']++; }
        }
        uasort($ringkas, function ($a, $b) { return strcmp($a['nama'], $b['nama']); });

        $data = array(
            'title' => 'Monitoring Penguji Sidang Proposal TA',
            'page_title' => 'Monitoring Penguji Sidang Proposal TA',
            'breadcrumb' => array(
                array('label' => 'Proposal TA', 'url' => '#'),
                array('label' => 'Monitoring Penguji', 'active' => true)
            ),
            'sidebar_menu' => generate_sidebar_menu('sekprodi'),
            'tahun_aktif' => $tahun_aktif,
            'dosen_list' => $this->Dosen_model->get_all_active(),
            'filter_dosen' => $id_dosen ?: null,
            'filter_tanggal_mulai' => $dari,
            'filter_tanggal_selesai' => $sampai,
            'baris' => $baris,
            'ringkas' => $ringkas,
            'jumlah_bentrok' => count(array_filter($baris, function ($b) { return $b['bentrok']; }))
        );
        $data['content'] = $this->load->view('sekprodi/monitoring_penguji_proposal', $data, true);
        $this->load->view('layouts/main', $data);
    }

    /**
     * Satu baris per (dosen, sidang). 'bentrok' = dosen yang sama punya sidang lain
     * pada tanggal sama dengan jam bertumpang tindih.
     */
    protected function susun_baris($id_tahun, $id_dosen, $dari, $sampai) {
        $baris = array();
        foreach ($this->Jadwal_proposal_model->get_all($id_tahun, null, 5000, 0) as $j) {
            if ($j->status === 'batal' || !$j->tanggal_ujian) { continue; }
            if ($dari && $j->tanggal_ujian < $dari) { continue; }
            if ($sampai && $j->tanggal_ujian > $sampai) { continue; }
            foreach (array(array('id_penguji_1', 'penguji_1', 'penguji_1_nidn', 'Penguji 1'), array('id_penguji_2', 'penguji_2', 'penguji_2_nidn', 'Penguji 2')) as $p) {
                $idd = (int) $j->{$p[0]};
                if ($idd <= 0 || ($id_dosen && $idd !== (int) $id_dosen)) { continue; }
                $baris[] = array(
                    'id_jadwal' => $j->id, 'id_dosen' => $idd, 'dosen' => $j->{$p[1]}, 'nidn' => $j->{$p[2]}, 'peran' => $p[3],
                    'tanggal' => $j->tanggal_ujian,
                    'hari_tanggal' => hari_dari_tanggal($j->tanggal_ujian) . ', ' . date('d/m/Y', strtotime($j->tanggal_ujian)),
                    'jam_mulai' => $j->jam_mulai, 'jam_selesai' => $j->jam_selesai, 'ruangan' => $j->ruangan,
                    'mahasiswa' => ($j->nim ?? '-') . ' - ' . ($j->nama_mahasiswa ?? '-'), 'status' => $j->status, 'bentrok' => false
                );
            }
        }

        for ($a = 0; $a < count($baris); $a++) {
            for ($b = $a + 1; $b < count($baris); $b++) {
                $x = $baris[$a]; $y = $baris[$b];
                if ($x['id_dosen'] === $y['id_dosen'] && $x['id_jadwal'] !== $y['id_jadwal'] && $x['tanggal'] === $y['tanggal']
                    && $x['jam_mulai'] < $y['jam_selesai'] && $x['jam_selesai'] > $y['jam_mulai']) {
                    $baris[$a]['bentrok'] = true;
                    $baris[$b]['bentrok'] = true;
                }
            }
        }

        usort($baris, function ($x, $y) {
            return strcmp($x['dosen'], $y['dosen']) ?: strcmp($x['tanggal'] . $x['jam_mulai'], $y['tanggal'] . $y['jam_mulai']);
        });
        return $baris;
    }

    private function tanggal_valid($v) {
        return (is_string($v) && preg_match('/^\d{4}-\d{2}-\d{2}$/', $v) && strtotime($v) !== false) ? $v : null;
    }
}
