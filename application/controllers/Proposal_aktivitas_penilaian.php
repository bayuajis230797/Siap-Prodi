<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Proposal_aktivitas_penilaian Controller (Sekprodi)
 * Pemantauan posisi tiap mahasiswa dalam alur Proposal TA (judul, bimbingan, nilai bimbingan,
 * pendaftaran sidang, jadwal, nilai penguji, layak klaim, klaim) beserta rincian nilai per butir.
 */
class Proposal_aktivitas_penilaian extends MY_Controller {

    public function __construct() {
        parent::__construct();
        $this->check_role(array('sekprodi', 'kaprodi'));
        $this->load->model('Tahun_akademik_model');
        $this->load->model('Proposal_model');
        $this->load->model('Proposal_aspek_penilaian_model');
        $this->load->model('Proposal_item_penilaian_model');
        $this->load->helper(array('proposal', 'app'));
    }

    public function index() {
        $tahun = $this->Tahun_akademik_model->get_aktif();
        if (!$tahun) {
            $this->session->set_flashdata('error', 'Tidak ada tahun akademik aktif.');
            redirect('Dashboard');
        }
        $kelas = $this->input->get('kelas');
        $tahap = $this->input->get('tahap');
        $list = $this->data_aktivitas($tahun->id, $kelas, $tahap);

        $kelas_list = array();
        foreach ($this->data_aktivitas($tahun->id, null, null) as $r) {
            if (!empty($r->kelas) && !in_array($r->kelas, $kelas_list)) { $kelas_list[] = $r->kelas; }
        }
        sort($kelas_list);

        $data = array(
            'title' => 'Aktivitas Penilaian Proposal TA',
            'page_title' => 'Aktivitas Penilaian Proposal TA',
            'breadcrumb' => array(
                array('label' => 'Proposal TA', 'url' => '#'),
                array('label' => 'Aktivitas Penilaian', 'active' => true)
            ),
            'sidebar_menu' => generate_sidebar_menu(get_user_role()),
            'tahun_aktif' => $tahun,
            'list' => $list,
            'kelas_list' => $kelas_list,
            'kelas_filter' => $kelas,
            'tahap_filter' => $tahap,
            'tahap_opsi' => $this->daftar_tahap()
        );
        $data['content'] = $this->load->view('sekprodi/proposal_aktivitas_penilaian/index', $data, true);
        $this->load->view('layouts/main', $data);
    }

    public function detail($id) {
        $p = $this->Proposal_model->get_by_id($id);
        if (!$p) {
            $this->session->set_flashdata('error', 'Data mahasiswa Proposal TA tidak ditemukan.');
            redirect('Proposal_aktivitas_penilaian');
        }

        $rincian = array('bimbingan' => $this->rincian_bimbingan($p->id), 'penguji_1' => null, 'penguji_2' => null);
        $jd = $this->db->order_by('tanggal_ujian', 'DESC')->limit(1)->get_where('jadwal_proposal', array('id_mahasiswa_proposal' => $p->id))->row();
        if ($jd) {
            foreach (array('penguji_1', 'penguji_2') as $peran) {
                $rincian[$peran] = $this->rincian_sidang($jd->id, $peran);
            }
        }

        $data = array(
            'title' => 'Detail Aktivitas Penilaian Proposal TA',
            'page_title' => 'Detail Aktivitas Penilaian Proposal TA',
            'breadcrumb' => array(
                array('label' => 'Aktivitas Penilaian', 'url' => site_url('Proposal_aktivitas_penilaian')),
                array('label' => 'Detail', 'active' => true)
            ),
            'sidebar_menu' => generate_sidebar_menu(get_user_role()),
            'p' => $p,
            'judul' => get_judul_proposal_terbaru($p->id_mahasiswa, $p->id_tahun_akademik),
            'rincian' => $rincian,
            'progress' => calculate_proposal_progress($p->id_mahasiswa, (object) array('id' => $p->id_tahun_akademik)),
            'rekap' => $this->db->get_where('proposal_nilai_rekap', array('id_mahasiswa_proposal' => $p->id))->row()
        );
        $data['content'] = $this->load->view('sekprodi/proposal_aktivitas_penilaian/detail', $data, true);
        $this->load->view('layouts/main', $data);
    }

    public function export_csv() {
        $tahun = $this->Tahun_akademik_model->get_aktif();
        if (!$tahun) {
            $this->session->set_flashdata('error', 'Tidak ada tahun akademik aktif.');
            redirect('Proposal_aktivitas_penilaian');
        }
        $rows = $this->baris_export($tahun, $this->input->get('kelas'), $this->input->get('tahap'));
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment;filename="Aktivitas_Penilaian_Proposal_TA_' . date('YmdHis') . '.csv"');
        header('Cache-Control: max-age=0');
        $out = fopen('php://output', 'w');
        fprintf($out, chr(0xEF) . chr(0xBB) . chr(0xBF));
        foreach ($rows as $r) { fputcsv($out, $r); }
        fclose($out);
        exit;
    }

    /* ------------------------------------------------------------------ */

    protected function daftar_tahap() {
        return array(
            'belum_judul' => 'Belum mengajukan judul', 'review_sekprodi' => 'Menunggu review sekprodi', 'acc_judul' => 'Menunggu ACC judul',
            'bimbingan' => 'Bimbingan', 'layak_sidang' => 'Layak sidang / mendaftar sidang', 'terjadwal' => 'Terjadwal',
            'penilaian' => 'Penilaian sidang', 'pasca_sidang' => 'Pasca sidang / layak klaim', 'klaim' => 'Klaim nilai', 'selesai' => 'Selesai'
        );
    }

    /** Tentukan tahap saat ini dari data satu baris. */
    protected function tahap_saat_ini($r) {
        if ($r->status === 'selesai_proposal' || $r->status_klaim === 'disetujui') { return 'selesai'; }
        if ($r->status_klaim) { return 'klaim'; }
        if ($r->jumlah_nilai_penguji >= 2) { return 'pasca_sidang'; }
        if ($r->status_jadwal && $r->status_jadwal !== 'batal') { return $r->jumlah_nilai_penguji > 0 ? 'penilaian' : 'terjadwal'; }
        if ($r->status === 'layak_sidang') { return 'layak_sidang'; }
        if (!$r->status_pengajuan || $r->status_pengajuan === 'ditolak') { return 'belum_judul'; }
        if ($r->status_pengajuan === 'pending') { return 'review_sekprodi'; }
        if ($r->status_acc_judul !== 'acc') { return 'acc_judul'; }
        return 'bimbingan';
    }

    protected function data_aktivitas($id_tahun, $kelas = null, $tahap = null) {
        $sql_latest_log = "(SELECT CONCAT(l.role_pengguna, ':', COALESCE(l.status_revisi, '')) FROM proposal_bimbingan_log l WHERE l.id_bimbingan = b.id ORDER BY l.created_at DESC, l.id DESC LIMIT 1)";
        $this->db->select("ms.id, ms.id_mahasiswa, ms.id_tahun_akademik, ms.status, ms.judul_proposal, ms.nilai_bimbingan, ms.nilai_sidang, ms.nilai_proposal_final, ms.nilai_huruf,
            m.nim, m.nama_mahasiswa, m.kelas, d.nama_dosen as pembimbing,
            (SELECT p.status FROM proposal_pengajuan p WHERE p.id_mahasiswa = ms.id_mahasiswa AND p.id_tahun_akademik = ms.id_tahun_akademik ORDER BY p.created_at DESC, p.id DESC LIMIT 1) as status_pengajuan,
            (SELECT p.status_acc_judul FROM proposal_pengajuan p WHERE p.id_mahasiswa = ms.id_mahasiswa AND p.id_tahun_akademik = ms.id_tahun_akademik ORDER BY p.created_at DESC, p.id DESC LIMIT 1) as status_acc_judul,
            (SELECT COUNT(*) FROM proposal_bimbingan b WHERE b.id_mahasiswa = ms.id_mahasiswa AND b.id_tahun_akademik = ms.id_tahun_akademik) as topik_total,
            (SELECT COUNT(*) FROM proposal_bimbingan b WHERE b.id_mahasiswa = ms.id_mahasiswa AND b.id_tahun_akademik = ms.id_tahun_akademik AND $sql_latest_log = 'dosen:acc') as topik_acc,
            (SELECT s.status FROM proposal_pendaftaran_sidang s WHERE s.id_mahasiswa_proposal = ms.id LIMIT 1) as daftar_sidang,
            (SELECT j.status FROM jadwal_proposal j WHERE j.id_mahasiswa_proposal = ms.id ORDER BY j.tanggal_ujian DESC LIMIT 1) as status_jadwal,
            (SELECT j.tanggal_ujian FROM jadwal_proposal j WHERE j.id_mahasiswa_proposal = ms.id ORDER BY j.tanggal_ujian DESC LIMIT 1) as tanggal_ujian,
            (SELECT COUNT(*) FROM proposal_nilai_sidang n WHERE n.id_mahasiswa_proposal = ms.id) as jumlah_nilai_penguji,
            (SELECT COUNT(*) FROM proposal_bimbingan_pasca_penguji pp JOIN proposal_bimbingan_pasca bp ON bp.id = pp.id_bimbingan_pasca WHERE bp.id_mahasiswa_proposal = ms.id AND pp.layak_klaim = 1) as jumlah_layak_klaim,
            (SELECT k.status FROM proposal_klaim_nilai k WHERE k.id_mahasiswa_proposal = ms.id LIMIT 1) as status_klaim", false);
        $this->db->from('mahasiswa_proposal ms');
        $this->db->join('mahasiswa m', 'm.id = ms.id_mahasiswa', 'left');
        $this->db->join('dosen d', 'd.id = ms.id_pembimbing', 'left');
        $this->db->where('ms.id_tahun_akademik', $id_tahun);
        $this->db->where('ms.status !=', 'batal');
        if ($kelas) { $this->db->where('m.kelas', $kelas); }
        $this->db->order_by('m.kelas', 'ASC');
        $this->db->order_by('m.nim', 'ASC');
        $rows = $this->db->get()->result();

        $opsi = $this->daftar_tahap();
        $hasil = array();
        foreach ($rows as $r) {
            $r->jumlah_nilai_penguji = (int) $r->jumlah_nilai_penguji;
            $r->tahap_kode = $this->tahap_saat_ini($r);
            $r->tahap_label = $opsi[$r->tahap_kode];
            if ($tahap && isset($opsi[$tahap]) && $r->tahap_kode !== $tahap) { continue; }
            $hasil[] = $r;
        }
        return $hasil;
    }

    protected function rincian_bimbingan($id_proposal) {
        $nb = $this->db->get_where('proposal_nilai_bimbingan', array('id_mahasiswa_proposal' => $id_proposal))->row();
        if (!$nb) { return null; }
        $a = $this->Proposal_aspek_penilaian_model->get_by_kode('PB');
        $det = array();
        foreach ($this->db->get_where('proposal_nilai_bimbingan_detail', array('id_nilai_bimbingan' => $nb->id))->result() as $d) { $det[$d->id_item_penilaian] = $d->nilai; }
        $baris = array();
        foreach ($a ? $this->Proposal_item_penilaian_model->get_by_aspek($a->id, false) : array() as $i) {
            $baris[] = (object) array('pertanyaan' => $i->pertanyaan, 'bobot' => $i->bobot, 'nilai' => isset($det[$i->id]) ? $det[$i->id] : null);
        }
        return (object) array('total' => $nb->nilai_rata, 'baris' => $baris);
    }

    protected function rincian_sidang($id_jadwal, $peran) {
        $ns = $this->db->select('n.*, d.nama_dosen')->from('proposal_nilai_sidang n')->join('dosen d', 'd.id = n.id_penguji', 'left')
            ->where('n.id_jadwal_proposal', $id_jadwal)->where('n.peran', $peran)->get()->row();
        if (!$ns) { return null; }
        $a = $this->Proposal_aspek_penilaian_model->get_by_kode('PS');
        $det = array();
        foreach ($this->db->get_where('proposal_nilai_sidang_detail', array('id_nilai_sidang' => $ns->id))->result() as $d) { $det[$d->id_item_penilaian] = $d->nilai; }
        $baris = array();
        foreach ($a ? $this->Proposal_item_penilaian_model->get_by_aspek($a->id, false) : array() as $i) {
            $baris[] = (object) array('pertanyaan' => $i->pertanyaan, 'bobot' => $i->bobot, 'nilai' => isset($det[$i->id]) ? $det[$i->id] : null);
        }
        return (object) array('penguji' => $ns->nama_dosen, 'total' => $ns->nilai_total, 'catatan_revisi' => $ns->catatan_revisi, 'baris' => $baris);
    }

    protected function baris_export($tahun, $kelas, $tahap) {
        $rows = array(
            array('AKTIVITAS PENILAIAN PROPOSAL TA'),
            array('Tahun Akademik: ' . $tahun->tahun_akademik . ' ' . ucfirst($tahun->semester)),
            array(),
            array('No', 'NIM', 'Nama', 'Kelas', 'Pembimbing', 'Tahap Saat Ini', 'Pengajuan Judul', 'ACC Judul', 'Topik ACC/Total', 'Nilai Bimbingan',
                'Pendaftaran Sidang', 'Tanggal Sidang', 'Penilai Sudah Menilai', 'Layak Klaim', 'Klaim', 'Nilai Akhir', 'Grade')
        );
        $f = function ($v) { return ($v === null || $v === '') ? '-' : number_format((float) $v, 2, ',', ''); };
        $no = 1;
        foreach ($this->data_aktivitas($tahun->id, $kelas, $tahap) as $r) {
            $rows[] = array($no++, $r->nim, $r->nama_mahasiswa, $r->kelas, $r->pembimbing ?: '-', $r->tahap_label, $r->status_pengajuan ?: '-', $r->status_acc_judul ?: '-',
                (int) $r->topik_acc . '/' . (int) $r->topik_total, $f($r->nilai_bimbingan), $r->daftar_sidang ?: '-',
                $r->tanggal_ujian ? date('d/m/Y', strtotime($r->tanggal_ujian)) : '-', (int) $r->jumlah_nilai_penguji . '/2', (int) $r->jumlah_layak_klaim . '/2',
                $r->status_klaim ?: '-', $f($r->nilai_proposal_final), $r->nilai_huruf ?: '-');
        }
        return $rows;
    }
}
