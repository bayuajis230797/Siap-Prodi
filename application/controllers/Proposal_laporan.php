<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Proposal_laporan Controller (Sekprodi, Kaprodi)
 * Laporan Proposal TA per tahun akademik aktif: daftar, ekspor CSV, dan rekap butir nilai.
 * Hanya membaca nilai yang sudah tersimpan (tidak ada sinkronisasi/penulisan saat membuka halaman).
 * Nilai dianggap FINAL bila klaim sudah disetujui (ada rekap), selain itu SEMENTARA.
 */
class Proposal_laporan extends MY_Controller {

    public function __construct() {
        parent::__construct();
        $this->check_role(array('sekprodi', 'kaprodi'));
        $this->load->model('Tahun_akademik_model');
        $this->load->model('Proposal_aspek_penilaian_model');
        $this->load->model('Proposal_item_penilaian_model');
        $this->load->helper('app');
    }

    private function tahun_aktif_atau_pergi() {
        $t = $this->Tahun_akademik_model->get_aktif();
        if (!$t) {
            $this->session->set_flashdata('error', 'Tidak ada tahun akademik aktif.');
            redirect('Dashboard');
        }
        return $t;
    }

    public function index() {
        $tahun = $this->tahun_aktif_atau_pergi();
        $kelas = $this->input->get('kelas');
        $status = $this->input->get('status');
        $list = $this->data_laporan($tahun->id, $kelas, $status);

        $kelas_list = array();
        foreach ($this->data_laporan($tahun->id, null, null) as $r) {
            if (!empty($r->kelas) && !in_array($r->kelas, $kelas_list)) { $kelas_list[] = $r->kelas; }
        }
        sort($kelas_list);

        $ringkas = array('total' => count($list), 'selesai' => 0, 'layak' => 0, 'dijadwalkan' => 0, 'rata' => null, 'grade' => array());
        $sum = 0; $n = 0;
        foreach ($list as $r) {
            if ($r->status === 'selesai_proposal') { $ringkas['selesai']++; }
            if ($r->status === 'layak_sidang') { $ringkas['layak']++; }
            if ($r->tanggal_ujian) { $ringkas['dijadwalkan']++; }
            if ($r->status_nilai === 'Final' && $r->nilai_proposal_final !== null) {
                $sum += (float) $r->nilai_proposal_final; $n++;
                $g = $r->nilai_huruf ?: '-';
                $ringkas['grade'][$g] = isset($ringkas['grade'][$g]) ? $ringkas['grade'][$g] + 1 : 1;
            }
        }
        $ringkas['rata'] = $n > 0 ? round($sum / $n, 2) : null;
        ksort($ringkas['grade']);

        $data = array(
            'title' => 'Laporan Proposal TA',
            'page_title' => 'Laporan Proposal TA',
            'breadcrumb' => array(
                array('label' => 'Laporan', 'url' => '#'),
                array('label' => 'Laporan Proposal TA', 'active' => true)
            ),
            'sidebar_menu' => generate_sidebar_menu(get_user_role()),
            'laporan_list' => $list,
            'tahun_aktif' => $tahun,
            'kelas_list' => $kelas_list,
            'kelas_filter' => $kelas,
            'status_filter' => $status,
            'ringkas' => $ringkas
        );
        $data['content'] = $this->load->view('sekprodi/proposal_laporan_index', $data, true);
        $this->load->view('layouts/main', $data);
    }

    public function export_csv() {
        $tahun = $this->tahun_aktif_atau_pergi();
        $this->kirim_csv($this->baris_laporan($tahun, $this->input->get('kelas'), $this->input->get('status')), 'Laporan_Proposal_TA');
    }

    public function export_rekap_nilai() {
        $tahun = $this->tahun_aktif_atau_pergi();
        $this->kirim_csv($this->baris_rekap_nilai($tahun, $this->input->get('kelas')), 'Rekap_Nilai_Proposal_TA');
    }

    /* ------------------------------------------------------------------ */

    private function kirim_csv($rows, $nama) {
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment;filename="' . $nama . '_' . date('YmdHis') . '.csv"');
        header('Cache-Control: max-age=0');
        $out = fopen('php://output', 'w');
        fprintf($out, chr(0xEF) . chr(0xBB) . chr(0xBF));
        foreach ($rows as $r) { fputcsv($out, $r); }
        fclose($out);
        exit;
    }

    /**
     * Data laporan: satu baris per mahasiswa terdaftar, beserta pembimbing, jadwal terbaru, dan nilai tersimpan.
     */
    protected function data_laporan($id_tahun, $kelas = null, $status = null) {
        $this->db->select('ms.id, ms.id_mahasiswa, ms.status, ms.judul_proposal, ms.nilai_bimbingan, ms.nilai_sidang,
                           ms.nilai_proposal_final, ms.nilai_huruf, m.nim, m.nama_mahasiswa, m.kelas,
                           d.nama_dosen as pembimbing, d.nidn as pembimbing_nidn,
                           (SELECT COUNT(*) FROM proposal_nilai_rekap r WHERE r.id_mahasiswa_proposal = ms.id) as jumlah_rekap', false);
        $this->db->from('mahasiswa_proposal ms');
        $this->db->join('mahasiswa m', 'm.id = ms.id_mahasiswa', 'left');
        $this->db->join('dosen d', 'd.id = ms.id_pembimbing', 'left');
        $this->db->where('ms.id_tahun_akademik', $id_tahun);
        if ($kelas) { $this->db->where('m.kelas', $kelas); }
        if ($status && in_array($status, array('terdaftar', 'sedang_proposal', 'layak_sidang', 'selesai_proposal', 'batal'), true)) {
            $this->db->where('ms.status', $status);
        }
        $this->db->order_by('m.kelas', 'ASC');
        $this->db->order_by('m.nim', 'ASC');
        $rows = $this->db->get()->result();

        // Jadwal terbaru per mahasiswa_proposal (satu query)
        $this->db->select('js.id_mahasiswa_proposal, js.tanggal_ujian, js.jam_mulai, js.jam_selesai, js.ruangan, js.status as status_jadwal,
                           d1.nama_dosen as penguji_1, d2.nama_dosen as penguji_2');
        $this->db->from('jadwal_proposal js');
        $this->db->join('dosen d1', 'd1.id = js.id_penguji_1', 'left');
        $this->db->join('dosen d2', 'd2.id = js.id_penguji_2', 'left');
        $this->db->where('js.id_tahun_akademik', $id_tahun);
        $this->db->order_by('js.tanggal_ujian', 'ASC');
        $jadwal = array();
        foreach ($this->db->get()->result() as $j) {
            $jadwal[$j->id_mahasiswa_proposal] = $j; // yang terakhir (tanggal terbesar) menimpa
        }

        foreach ($rows as $r) {
            $j = isset($jadwal[$r->id]) ? $jadwal[$r->id] : null;
            $r->tanggal_ujian = $j ? $j->tanggal_ujian : null;
            $r->jam = $j && $j->jam_mulai ? substr($j->jam_mulai, 0, 5) . ' - ' . substr($j->jam_selesai, 0, 5) : null;
            $r->ruangan = $j ? $j->ruangan : null;
            $r->penguji_1 = $j ? $j->penguji_1 : null;
            $r->penguji_2 = $j ? $j->penguji_2 : null;
            $r->status_jadwal = $j ? $j->status_jadwal : null;
            $r->status_nilai = ((int) $r->jumlah_rekap > 0) ? 'Final' : ($r->nilai_proposal_final !== null ? 'Sementara' : '-');
        }
        return $rows;
    }

    protected function baris_laporan($tahun, $kelas, $status) {
        $rows = array(
            array('LAPORAN PROPOSAL TA'),
            array('Tahun Akademik: ' . $tahun->tahun_akademik . ' ' . ucfirst($tahun->semester)),
            array('Tanggal Export: ' . date('d F Y H:i:s')),
            array(),
            array('No', 'NIM', 'Nama Mahasiswa', 'Kelas', 'Judul Proposal', 'Pembimbing', 'NIDN Pembimbing', 'Status', 'Tanggal Sidang', 'Hari', 'Jam', 'Ruangan',
                'Penguji 1', 'Penguji 2', 'Nilai Bimbingan', 'Nilai Sidang', 'Nilai Akhir', 'Grade', 'Status Nilai')
        );
        $no = 1;
        $f = function ($v) { return ($v === null || $v === '') ? '-' : number_format((float) $v, 2, ',', ''); };
        foreach ($this->data_laporan($tahun->id, $kelas, $status) as $r) {
            $rows[] = array($no++, $r->nim, $r->nama_mahasiswa, $r->kelas, $r->judul_proposal ?: '-', $r->pembimbing ?: '-', $r->pembimbing_nidn ?: '-',
                str_replace('_', ' ', $r->status), $r->tanggal_ujian ? date('d/m/Y', strtotime($r->tanggal_ujian)) : '-', hari_dari_tanggal($r->tanggal_ujian),
                $r->jam ?: '-', $r->ruangan ?: '-', $r->penguji_1 ?: '-', $r->penguji_2 ?: '-',
                $f($r->nilai_bimbingan), $f($r->nilai_sidang), $f($r->nilai_proposal_final), $r->nilai_huruf ?: '-', $r->status_nilai);
        }
        $rows[] = array();
        $rows[] = array('TOTAL', ($no - 1) . ' Mahasiswa');
        return $rows;
    }

    /** Rekap nilai per butir: pembimbing (PB) dan masing-masing penguji (PS). */
    protected function baris_rekap_nilai($tahun, $kelas) {
        $items = array('PB' => array(), 'PS' => array());
        foreach (array('PB', 'PS') as $kode) {
            $a = $this->Proposal_aspek_penilaian_model->get_by_kode($kode);
            if ($a) { $items[$kode] = $this->Proposal_item_penilaian_model->get_by_aspek($a->id, false); }
        }
        $head = array('No', 'NIM', 'Nama', 'Kelas');
        foreach ($items['PB'] as $i) { $head[] = 'Pembimbing: ' . $i->pertanyaan . ' (' . (float) $i->bobot . '%)'; }
        $head[] = 'Nilai Bimbingan';
        foreach (array('Penguji 1', 'Penguji 2') as $p) {
            foreach ($items['PS'] as $i) { $head[] = $p . ': ' . $i->pertanyaan . ' (' . (float) $i->bobot . '%)'; }
            $head[] = 'Nilai ' . $p;
        }
        array_push($head, 'Nilai Sidang', 'Nilai Akhir', 'Grade', 'Status Nilai');

        $rows = array(
            array('REKAP NILAI PROPOSAL TA'),
            array('Tahun Akademik: ' . $tahun->tahun_akademik . ' ' . ucfirst($tahun->semester)),
            array(),
            $head
        );
        $f = function ($v) { return ($v === null || $v === '') ? '-' : number_format((float) $v, 2, ',', ''); };

        $no = 1;
        foreach ($this->data_laporan($tahun->id, $kelas, null) as $r) {
            $line = array($no++, $r->nim, $r->nama_mahasiswa, $r->kelas);

            // Rincian bimbingan
            $nb = $this->db->get_where('proposal_nilai_bimbingan', array('id_mahasiswa_proposal' => $r->id))->row();
            $det = array();
            if ($nb) {
                foreach ($this->db->get_where('proposal_nilai_bimbingan_detail', array('id_nilai_bimbingan' => $nb->id))->result() as $d) { $det[$d->id_item_penilaian] = $d->nilai; }
            }
            foreach ($items['PB'] as $i) { $line[] = isset($det[$i->id]) ? $f($det[$i->id]) : '-'; }
            $line[] = $nb ? $f($nb->nilai_rata) : '-';

            // Rincian penguji
            $jd = $this->db->order_by('tanggal_ujian', 'DESC')->limit(1)->get_where('jadwal_proposal', array('id_mahasiswa_proposal' => $r->id))->row();
            foreach (array('penguji_1', 'penguji_2') as $peran) {
                $ns = $jd ? $this->db->get_where('proposal_nilai_sidang', array('id_jadwal_proposal' => $jd->id, 'peran' => $peran))->row() : null;
                $det = array();
                if ($ns) {
                    foreach ($this->db->get_where('proposal_nilai_sidang_detail', array('id_nilai_sidang' => $ns->id))->result() as $d) { $det[$d->id_item_penilaian] = $d->nilai; }
                }
                foreach ($items['PS'] as $i) { $line[] = isset($det[$i->id]) ? $f($det[$i->id]) : '-'; }
                $line[] = $ns ? $f($ns->nilai_total) : '-';
            }
            array_push($line, $f($r->nilai_sidang), $f($r->nilai_proposal_final), $r->nilai_huruf ?: '-', $r->status_nilai);
            $rows[] = $line;
        }
        return $rows;
    }
}
