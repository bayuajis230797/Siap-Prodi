<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Manajemen Tanda Terima — role sekprodi
 */
class Tanda_terima extends MY_Controller {

    public function __construct() {
        parent::__construct();
        $this->check_role('sekprodi');
        $this->load->model('Tanda_terima_model');
        $this->load->model('Tanda_terima_item_model');
        $this->load->model('Tanda_terima_penerimaan_model');
        $this->load->model('Tahun_akademik_model');
        $this->load->model('Kelas_model');
        $this->load->model('Mahasiswa_model');
        $this->load->model('Dosen_model');
        $this->load->helper(array('qrcode', 'menu'));
    }

    public function index() {
        $jenis = $this->input->get('jenis');
        if (!in_array($jenis, array('mahasiswa', 'dosen'), true)) {
            $jenis = null;
        }
        $list = $this->Tanda_terima_model->get_all($jenis, 200, 0);
        foreach ($list as $row) {
            $row->jumlah_peserta = $this->Tanda_terima_model->count_peserta($row->id);
            $row->jumlah_barang = $this->Tanda_terima_item_model->count_by_distribusi($row->id);
        }
        $data = array(
            'title' => 'Tanda Terima',
            'page_title' => 'Tanda Terima Barang',
            'breadcrumb' => array(array('label' => 'Tanda Terima', 'active' => true)),
            'sidebar_menu' => generate_sidebar_menu('sekprodi'),
            'list' => $list,
            'filter_jenis' => $jenis,
            'tahun_aktif' => $this->Tahun_akademik_model->get_aktif(),
        );
        $data['content'] = $this->load->view('sekprodi/tanda_terima/index', $data, true);
        $this->load->view('layouts/main', $data);
    }

    public function create() {
        $data = array(
            'title' => 'Tambah Tanda Terima',
            'page_title' => 'Tambah Distribusi Barang',
            'breadcrumb' => array(
                array('label' => 'Tanda Terima', 'url' => site_url('Tanda_terima')),
                array('label' => 'Tambah', 'active' => true),
            ),
            'sidebar_menu' => generate_sidebar_menu('sekprodi'),
            'tahun_aktif' => $this->Tahun_akademik_model->get_aktif(),
        );
        $data['content'] = $this->load->view('sekprodi/tanda_terima/create', $data, true);
        $this->load->view('layouts/main', $data);
    }

    public function store() {
        $this->form_validation->set_rules('nama_distribusi', 'Nama Distribusi', 'required|trim');
        $this->form_validation->set_rules('jenis_penerima', 'Jenis Penerima', 'required|in_list[mahasiswa,dosen,campuran]');
        if ($this->form_validation->run() === FALSE) {
            $this->create();
            return;
        }
        $tahun = $this->Tahun_akademik_model->get_aktif();
        $id = $this->Tanda_terima_model->create(array(
            'nama_distribusi' => $this->input->post('nama_distribusi'),
            'deskripsi' => $this->input->post('deskripsi'),
            'jenis_penerima' => $this->input->post('jenis_penerima'),
            'tanggal_mulai' => $this->input->post('tanggal_mulai') ?: null,
            'tanggal_selesai' => $this->input->post('tanggal_selesai') ?: null,
            'id_tahun_akademik' => $tahun ? $tahun->id : null,
            'status' => 'draft',
            'created_by' => $this->session->userdata('user_id'),
        ));
        if ($id) {
            $this->session->set_flashdata('success', 'Distribusi tanda terima berhasil dibuat. Tambahkan barang dan daftar penerima.');
            redirect('Tanda_terima/detail/' . $id);
        }
        $this->session->set_flashdata('error', 'Gagal menyimpan data.');
        redirect('Tanda_terima/create');
    }

    public function detail($id) {
        $tt = $this->Tanda_terima_model->get_by_id($id);
        if (!$tt) {
            $this->session->set_flashdata('error', 'Data tidak ditemukan.');
            redirect('Tanda_terima');
        }
        $items = $this->Tanda_terima_item_model->get_by_distribusi($id);
        $peserta = $this->Tanda_terima_model->get_peserta_by_distribusi($id);
        $rekap = $this->Tanda_terima_penerimaan_model->get_rekap_distribusi($id, $tt->jenis_penerima);
        $peserta_mhs_ids = array();
        $peserta_dosen_ids = array();
        foreach ($peserta as $p) {
            if (!empty($p->id_mahasiswa)) {
                $peserta_mhs_ids[] = (int) $p->id_mahasiswa;
            }
            if (!empty($p->id_dosen)) {
                $peserta_dosen_ids[] = (int) $p->id_dosen;
            }
        }
        $show_mhs = in_array($tt->jenis_penerima, array('mahasiswa', 'campuran'), true);
        $show_dosen = in_array($tt->jenis_penerima, array('dosen', 'campuran'), true);
        $data = array(
            'title' => 'Detail Tanda Terima',
            'page_title' => $tt->nama_distribusi,
            'breadcrumb' => array(
                array('label' => 'Tanda Terima', 'url' => site_url('Tanda_terima')),
                array('label' => 'Detail', 'active' => true),
            ),
            'sidebar_menu' => generate_sidebar_menu('sekprodi'),
            'tt' => $tt,
            'items' => $items,
            'peserta' => $peserta,
            'rekap' => $rekap,
            'peserta_mhs_ids' => $peserta_mhs_ids,
            'peserta_dosen_ids' => $peserta_dosen_ids,
            'show_mhs' => $show_mhs,
            'show_dosen' => $show_dosen,
            'mahasiswa_by_kelas' => $show_mhs ? $this->Tanda_terima_model->get_mahasiswa_aktif_grouped_by_kelas() : array(),
            'dosen_list' => $show_dosen ? $this->Dosen_model->get_all_active() : array(),
            'kelas_list' => $show_mhs ? $this->Kelas_model->get_all_kelas() : array(),
        );
        $data['content'] = $this->load->view('sekprodi/tanda_terima/detail', $data, true);
        $this->load->view('layouts/main', $data);
    }

    /**
     * Catat pengambilan manual (sekprodi) — jika penerima lupa scan QR.
     * POST: id_item, tipe (mahasiswa|dosen), id_ref, waktu_terima (opsional Y-m-d H:i atau Y-m-d\TH:i)
     */
    public function catat_manual($id) {
        $tt = $this->Tanda_terima_model->get_by_id($id);
        if (!$tt) {
            $this->output->set_content_type('application/json')
                ->set_output(json_encode(array('success' => false, 'message' => 'Distribusi tidak ditemukan.')));
            return;
        }

        $id_item = (int) $this->input->post('id_item');
        $tipe = $this->input->post('tipe');
        $id_ref = (int) $this->input->post('id_ref');
        $waktu_raw = trim((string) $this->input->post('waktu_terima'));

        if ($id_item <= 0 || $id_ref <= 0 || !in_array($tipe, array('mahasiswa', 'dosen'), true)) {
            $this->output->set_content_type('application/json')
                ->set_output(json_encode(array('success' => false, 'message' => 'Parameter tidak lengkap.')));
            return;
        }

        $item = $this->Tanda_terima_item_model->get_by_id($id_item);
        if (!$item || (int) $item->id_tanda_terima !== (int) $id) {
            $this->output->set_content_type('application/json')
                ->set_output(json_encode(array('success' => false, 'message' => 'Barang tidak valid untuk distribusi ini.')));
            return;
        }

        if ($tt->jenis_penerima === 'mahasiswa' && $tipe !== 'mahasiswa') {
            $this->output->set_content_type('application/json')
                ->set_output(json_encode(array('success' => false, 'message' => 'Distribusi ini hanya untuk mahasiswa.')));
            return;
        }
        if ($tt->jenis_penerima === 'dosen' && $tipe !== 'dosen') {
            $this->output->set_content_type('application/json')
                ->set_output(json_encode(array('success' => false, 'message' => 'Distribusi ini hanya untuk dosen.')));
            return;
        }

        $id_mahasiswa = $tipe === 'mahasiswa' ? $id_ref : null;
        $id_dosen = $tipe === 'dosen' ? $id_ref : null;

        if (!$this->Tanda_terima_model->is_peserta($id, $id_mahasiswa, $id_dosen)) {
            $this->output->set_content_type('application/json')
                ->set_output(json_encode(array('success' => false, 'message' => 'Penerima belum terdaftar di daftar peserta distribusi.')));
            return;
        }

        if ($this->Tanda_terima_penerimaan_model->sudah_terima($id_item, $id_mahasiswa, $id_dosen)) {
            $this->output->set_content_type('application/json')
                ->set_output(json_encode(array('success' => false, 'message' => 'Penerima sudah tercatat mengambil barang ini.')));
            return;
        }

        $waktu = date('Y-m-d H:i:s');
        if ($waktu_raw !== '') {
            $waktu_raw = str_replace('T', ' ', $waktu_raw);
            if (preg_match('/^\d{4}-\d{2}-\d{2} \d{2}:\d{2}$/', $waktu_raw)) {
                $waktu_raw .= ':00';
            }
            $ts = strtotime($waktu_raw);
            if ($ts === false) {
                $this->output->set_content_type('application/json')
                    ->set_output(json_encode(array('success' => false, 'message' => 'Format waktu tidak valid.')));
                return;
            }
            $waktu = date('Y-m-d H:i:s', $ts);
        }

        $this->Tanda_terima_penerimaan_model->create(array(
            'id_tanda_terima_item' => $id_item,
            'id_tanda_terima' => (int) $id,
            'id_mahasiswa' => $id_mahasiswa,
            'id_dosen' => $id_dosen,
            'waktu_terima' => $waktu,
            'ip_address' => $this->input->ip_address(),
            'user_agent' => 'manual-sekprodi:' . substr((string) $this->input->user_agent(), 0, 480),
        ));

        $this->output->set_content_type('application/json')
            ->set_output(json_encode(array(
                'success' => true,
                'message' => 'Pengambilan dicatat manual: ' . $item->nama_barang . ' (' . date('d/m/Y H:i', strtotime($waktu)) . ').',
                'waktu_terima' => $waktu,
            )));
    }

    /**
     * Batalkan catatan penerimaan (sekprodi) — koreksi salah catat.
     * POST: id_item, tipe, id_ref
     */
    public function batal_manual($id) {
        $tt = $this->Tanda_terima_model->get_by_id($id);
        if (!$tt) {
            $this->output->set_content_type('application/json')
                ->set_output(json_encode(array('success' => false, 'message' => 'Distribusi tidak ditemukan.')));
            return;
        }

        $id_item = (int) $this->input->post('id_item');
        $tipe = $this->input->post('tipe');
        $id_ref = (int) $this->input->post('id_ref');

        if ($id_item <= 0 || $id_ref <= 0 || !in_array($tipe, array('mahasiswa', 'dosen'), true)) {
            $this->output->set_content_type('application/json')
                ->set_output(json_encode(array('success' => false, 'message' => 'Parameter tidak lengkap.')));
            return;
        }

        $item = $this->Tanda_terima_item_model->get_by_id($id_item);
        if (!$item || (int) $item->id_tanda_terima !== (int) $id) {
            $this->output->set_content_type('application/json')
                ->set_output(json_encode(array('success' => false, 'message' => 'Barang tidak valid.')));
            return;
        }

        $id_mahasiswa = $tipe === 'mahasiswa' ? $id_ref : null;
        $id_dosen = $tipe === 'dosen' ? $id_ref : null;

        if (!$this->Tanda_terima_penerimaan_model->sudah_terima($id_item, $id_mahasiswa, $id_dosen)) {
            $this->output->set_content_type('application/json')
                ->set_output(json_encode(array('success' => false, 'message' => 'Belum ada catatan penerimaan.')));
            return;
        }

        $this->Tanda_terima_penerimaan_model->delete_by_penerima($id_item, $id_mahasiswa, $id_dosen);
        $this->output->set_content_type('application/json')
            ->set_output(json_encode(array(
                'success' => true,
                'message' => 'Catatan pengambilan dibatalkan: ' . $item->nama_barang . '.',
            )));
    }

    public function update_status($id) {
        $tt = $this->Tanda_terima_model->get_by_id($id);
        if (!$tt) {
            $this->output->set_content_type('application/json')
                ->set_output(json_encode(array('success' => false, 'message' => 'Data tidak ditemukan.')));
            return;
        }
        $status = $this->input->post('status');
        if (!in_array($status, array('draft', 'aktif', 'selesai', 'dibatalkan'), true)) {
            $this->output->set_content_type('application/json')
                ->set_output(json_encode(array('success' => false, 'message' => 'Status tidak valid.')));
            return;
        }
        if ($tt->status === $status) {
            $this->output->set_content_type('application/json')
                ->set_output(json_encode(array('success' => true, 'message' => 'Status sudah ' . $status . '.')));
            return;
        }
        $this->Tanda_terima_model->update($id, array('status' => $status));
        $this->output->set_content_type('application/json')
            ->set_output(json_encode(array(
                'success' => true,
                'message' => 'Status diperbarui menjadi ' . $status . '.',
            )));
    }

    public function tambah_barang($id) {
        $tt = $this->Tanda_terima_model->get_by_id($id);
        if (!$tt) {
            $this->session->set_flashdata('error', 'Data tidak ditemukan.');
            redirect('Tanda_terima');
        }
        $nama = trim($this->input->post('nama_barang'));
        if ($nama === '') {
            $this->session->set_flashdata('error', 'Nama barang wajib diisi.');
            redirect('Tanda_terima/detail/' . $id);
        }
        $token = $this->Tanda_terima_item_model->generate_unique_token();
        if ($tt->jenis_penerima === 'dosen') {
            $scan_path = site_url('Tanda_terima_dosen/scan/' . $token);
        } elseif ($tt->jenis_penerima === 'campuran') {
            $scan_path = site_url('Tanda_terima_ambil/scan/' . $token);
        } else {
            $scan_path = site_url('Tanda_terima_mahasiswa/scan/' . $token);
        }
        $qr_path = generate_qr_code($scan_path, 'tt_' . $id . '_' . time());
        $this->Tanda_terima_item_model->create(array(
            'id_tanda_terima' => (int) $id,
            'nama_barang' => $nama,
            'keterangan' => $this->input->post('keterangan'),
            'qr_code_token' => $token,
            'qr_code' => $qr_path,
            'status' => 'aktif',
        ));
        $this->session->set_flashdata('success', 'Barang dan QR Code berhasil ditambahkan.');
        redirect('Tanda_terima/detail/' . $id);
    }

    public function hapus_barang($id_item) {
        $item = $this->Tanda_terima_item_model->get_by_id($id_item);
        if ($item) {
            $this->Tanda_terima_item_model->delete($id_item);
            $this->session->set_flashdata('success', 'Barang dihapus.');
            redirect('Tanda_terima/detail/' . $item->id_tanda_terima);
        }
        redirect('Tanda_terima');
    }

    public function simpan_peserta($id) {
        $tt = $this->Tanda_terima_model->get_by_id($id);
        if (!$tt) {
            $this->session->set_flashdata('error', 'Data tidak ditemukan.');
            redirect('Tanda_terima');
        }

        $mhs_ids = $this->input->post('id_mahasiswa');
        $dosen_ids = $this->input->post('id_dosen');
        $mhs_ids = is_array($mhs_ids) ? array_map('intval', $mhs_ids) : array();
        $dosen_ids = is_array($dosen_ids) ? array_map('intval', $dosen_ids) : array();

        if ($tt->jenis_penerima === 'mahasiswa') {
            if (empty($mhs_ids)) {
                $this->session->set_flashdata('error', 'Pilih minimal satu mahasiswa penerima.');
                redirect('Tanda_terima/detail/' . $id);
            }
            $this->Tanda_terima_model->bulk_add_peserta_mahasiswa($id, $mhs_ids);
        } elseif ($tt->jenis_penerima === 'dosen') {
            if (empty($dosen_ids)) {
                $this->session->set_flashdata('error', 'Pilih minimal satu dosen penerima.');
                redirect('Tanda_terima/detail/' . $id);
            }
            $this->Tanda_terima_model->bulk_add_peserta_dosen($id, $dosen_ids);
        } else {
            if (empty($mhs_ids) && empty($dosen_ids)) {
                $this->session->set_flashdata('error', 'Pilih minimal satu mahasiswa atau dosen penerima.');
                redirect('Tanda_terima/detail/' . $id);
            }
            $this->Tanda_terima_model->save_peserta_campuran($id, $mhs_ids, $dosen_ids);
        }

        $this->session->set_flashdata('success', 'Daftar penerima berhasil disimpan (' . (count($mhs_ids) + count($dosen_ids)) . ' orang).');
        redirect('Tanda_terima/detail/' . $id);
    }

    public function cetak_qr($id_item) {
        $item = $this->Tanda_terima_item_model->get_by_id($id_item);
        if (!$item) {
            show_404();
        }
        $tt = $this->Tanda_terima_model->get_by_id($item->id_tanda_terima);
        if ($tt->jenis_penerima === 'dosen') {
            $scan_url = site_url('Tanda_terima_dosen/scan/' . $item->qr_code_token);
        } elseif ($tt->jenis_penerima === 'campuran') {
            $scan_url = site_url('Tanda_terima_ambil/scan/' . $item->qr_code_token);
        } else {
            $scan_url = site_url('Tanda_terima_mahasiswa/scan/' . $item->qr_code_token);
        }
        $data = array('item' => $item, 'tt' => $tt, 'scan_url' => $scan_url);
        $this->load->view('sekprodi/tanda_terima/cetak_qr', $data);
    }

    public function export_csv($id) {
        $tt = $this->Tanda_terima_model->get_by_id($id);
        if (!$tt) {
            redirect('Tanda_terima');
        }
        $rekap = $this->Tanda_terima_penerimaan_model->get_rekap_distribusi($id, $tt->jenis_penerima);
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment;filename="Tanda_Terima_' . $id . '_' . date('YmdHis') . '.csv"');
        echo "\xEF\xBB\xBF";
        $out = fopen('php://output', 'w');
        fputcsv($out, array('Rekap Tanda Terima: ' . $tt->nama_distribusi));
        fputcsv($out, array('Jenis Penerima', ucfirst($tt->jenis_penerima)));
        fputcsv($out, array());
        $header = array('No');
        if ($tt->jenis_penerima === 'campuran') {
            $header[] = 'Jenis';
        }
        $header[] = $tt->jenis_penerima === 'dosen' ? 'NIDN' : ($tt->jenis_penerima === 'campuran' ? 'NIM/NIDN' : 'NIM');
        $header[] = 'Nama';
        if ($tt->jenis_penerima !== 'dosen') {
            $header[] = 'Kelas';
        }
        foreach ($rekap['items'] as $it) {
            $nama = $it->nama_barang;
            $header[] = $nama . ' - Status';
            $header[] = $nama . ' - Tanggal';
            $header[] = $nama . ' - Jam';
        }
        fputcsv($out, $header);
        $no = 1;
        foreach ($rekap['peserta'] as $p) {
            $row = array($no++);
            if ($tt->jenis_penerima === 'campuran') {
                $row[] = isset($p->tipe_penerima) ? ucfirst($p->tipe_penerima) : '-';
            }
            $row[] = $p->kode;
            $row[] = $p->nama;
            if ($tt->jenis_penerima !== 'dosen') {
                $row[] = isset($p->kelas) ? $p->kelas : '';
            }
            foreach ($rekap['items'] as $it) {
                $ref_key = isset($p->ref_key) ? $p->ref_key : ((isset($p->tipe_penerima) && $p->tipe_penerima === 'dosen') ? 'd' : 'm') . (int) $p->ref_id;
                $key = (int) $it->id . '_' . $ref_key;
                $terima = isset($rekap['terima_map'][$key]) ? $rekap['terima_map'][$key] : null;
                if ($terima && !empty($terima->waktu_terima)) {
                    $row[] = 'Sudah';
                    $row[] = date('d/m/Y', strtotime($terima->waktu_terima));
                    $row[] = date('H:i', strtotime($terima->waktu_terima));
                } else {
                    $row[] = 'Belum';
                    $row[] = '';
                    $row[] = '';
                }
            }
            fputcsv($out, $row);
        }
        fclose($out);
        exit;
    }

    public function cetak_rekap($id) {
        $tt = $this->Tanda_terima_model->get_by_id($id);
        if (!$tt) {
            $this->session->set_flashdata('error', 'Data tidak ditemukan.');
            redirect('Tanda_terima');
        }
        $rekap = $this->Tanda_terima_penerimaan_model->get_rekap_distribusi($id, $tt->jenis_penerima);
        $this->load->library('Pdf');
        $this->load->helper('app');
        $data = array(
            'tt' => $tt,
            'rekap' => $rekap,
        );
        $html = $this->load->view('sekprodi/tanda_terima/cetak_rekap', $data, true);
        $this->pdf->load_html($html);
        $orientation = count($rekap['items']) > 2 ? 'landscape' : 'portrait';
        $this->pdf->set_paper('A4', $orientation);
        $filename = 'Rekap_Tanda_Terima_' . $id . '_' . date('YmdHis') . '.pdf';
        $this->pdf->stream($filename);
    }
}
