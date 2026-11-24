<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Tahun Akademik Controller
 * Mengelola tahun akademik dan kenaikan semester otomatis
 */
class Tahun_akademik extends MY_Controller {

    public function __construct() {
        parent::__construct();
        $this->check_role('admin');
        $this->load->model('Tahun_akademik_model');
        $this->load->model('Mahasiswa_model');
        $this->load->model('Kurikulum_model');
        $this->load->model('Mata_kuliah_aktif_model');
        $this->load->model('Mapping_dosen_mk_model');
    }

    /**
     * List semua tahun akademik dengan pagination
     */
    public function index() {
        $this->load->library('pagination');
        
        // Ambil parameter per_page dari GET, default 10
        $per_page = $this->input->get('per_page') ? (int)$this->input->get('per_page') : 10;
        $per_page = in_array($per_page, [10, 25, 50, 100]) ? $per_page : 10;
        
        // Pagination config
        $config['base_url'] = site_url('Tahun_akademik/index');
        $config['total_rows'] = $this->Tahun_akademik_model->count_all();
        $config['per_page'] = $per_page;
        $config['uri_segment'] = 3;
        
        // Tambahkan per_page ke query string
        if ($per_page != 10) {
            $config['suffix'] = '?per_page=' . $per_page;
            $config['first_url'] = site_url('Tahun_akademik/index?per_page=' . $per_page);
        }
        $config['use_page_numbers'] = TRUE;
        $config['first_link'] = '&laquo; Awal';
        $config['last_link'] = 'Akhir &raquo;';
        $config['next_link'] = '&gt;';
        $config['prev_link'] = '&lt;';
        $config['full_tag_open'] = '<ul class="pagination pagination-sm m-0 float-right">';
        $config['full_tag_close'] = '</ul>';
        $config['first_tag_open'] = '<li class="page-item">';
        $config['first_tag_close'] = '</li>';
        $config['last_tag_open'] = '<li class="page-item">';
        $config['last_tag_close'] = '</li>';
        $config['next_tag_open'] = '<li class="page-item">';
        $config['next_tag_close'] = '</li>';
        $config['prev_tag_open'] = '<li class="page-item">';
        $config['prev_tag_close'] = '</li>';
        $config['cur_tag_open'] = '<li class="page-item active"><a class="page-link" href="#">';
        $config['cur_tag_close'] = '</a></li>';
        $config['num_tag_open'] = '<li class="page-item">';
        $config['num_tag_close'] = '</li>';
        $config['attributes'] = array('class' => 'page-link');
        
        $this->pagination->initialize($config);
        
        $page = ($this->uri->segment(3)) ? $this->uri->segment(3) : 1;
        $offset = ($page - 1) * $config['per_page'];
        
        $data['tahun_akademik'] = $this->Tahun_akademik_model->get_all($config['per_page'], $offset);
        $data['pagination'] = $this->pagination->create_links();
        $data['total_rows'] = $config['total_rows'];
        $data['per_page'] = $per_page;
        
        $data['title'] = 'Tahun Akademik';
        $data['page_title'] = 'Manajemen Tahun Akademik';
        $data['breadcrumb'] = array(
            array('label' => 'Tahun Akademik', 'active' => true)
        );
        $data['sidebar_menu'] = generate_sidebar_menu('admin');
        $data['content'] = $this->load->view('tahun_akademik/index', $data, true);
        $this->load->view('layouts/main', $data);
    }

    /**
     * Form tambah tahun akademik
     */
    public function create() {
        if ($this->input->method() === 'post') {
            $this->form_validation->set_rules('tahun_akademik', 'Tahun Akademik', 'required|trim');
            $this->form_validation->set_rules('semester', 'Semester', 'required|trim');
            $this->form_validation->set_rules('tgl_mulai', 'Tanggal Mulai', 'required|trim');
            $this->form_validation->set_rules('tgl_selesai', 'Tanggal Selesai', 'required|trim');

            if ($this->form_validation->run() == FALSE) {
                $data['title'] = 'Tambah Tahun Akademik';
                $data['page_title'] = 'Tambah Tahun Akademik';
                $data['breadcrumb'] = array(
                    array('label' => 'Tahun Akademik', 'url' => site_url('Tahun_akademik')),
                    array('label' => 'Tambah', 'active' => true)
                );
                $data['sidebar_menu'] = generate_sidebar_menu('admin');
                $data['content'] = $this->load->view('tahun_akademik/create', $data, true);
                $this->load->view('layouts/main', $data);
            } else {
                $data = array(
                    'tahun_akademik' => $this->input->post('tahun_akademik'),
                    'semester' => $this->input->post('semester'),
                    'tgl_mulai' => $this->input->post('tgl_mulai'),
                    'tgl_selesai' => $this->input->post('tgl_selesai'),
                    'is_aktif' => 0
                );

                if ($this->Tahun_akademik_model->create($data)) {
                    $this->session->set_flashdata('success', 'Tahun akademik berhasil ditambahkan.');
                    redirect('Tahun_akademik');
                } else {
                    $this->session->set_flashdata('error', 'Gagal menambahkan tahun akademik.');
                    redirect('Tahun_akademik/create');
                }
            }
        } else {
            $data['title'] = 'Tambah Tahun Akademik';
            $data['page_title'] = 'Tambah Tahun Akademik';
            $data['breadcrumb'] = array(
                array('label' => 'Tahun Akademik', 'url' => site_url('Tahun_akademik')),
                array('label' => 'Tambah', 'active' => true)
            );
            $data['sidebar_menu'] = generate_sidebar_menu('admin');
            $data['content'] = $this->load->view('tahun_akademik/create', $data, true);
            $this->load->view('layouts/main', $data);
        }
    }

    /**
     * Pratinjau aktivasi tahun akademik (HANYA MEMBACA, tidak mengubah data apa pun).
     * Menampilkan dampak kenaikan kelas sebelum admin mengonfirmasi.
     */
    public function pratinjau($id) {
        $ta = $this->Tahun_akademik_model->get_by_id($id);
        if (!$ta) {
            $this->session->set_flashdata('error', 'Tahun akademik tidak ditemukan.');
            redirect('Tahun_akademik');
        }
        $aktif = $this->Tahun_akademik_model->get_aktif_resmi();
        if ($aktif && (int) $aktif->id === (int) $ta->id) {
            $this->session->set_flashdata('error', 'Tahun akademik ini sudah aktif.');
            redirect('Tahun_akademik');
        }

        $this->load->library('Kenaikan_kelas');
        $migrasi_ok = $this->kenaikan_kelas->kolom_baru_ada();
        $mode = 'maju';
        $hw = null;
        if ($migrasi_ok) {
            $m = $this->kenaikan_kelas->mode_aktivasi($ta, $aktif);
            $mode = $m['mode'];
            $hw = $m['hw'];
        }
        // Hanya kenaikan kelas (maju melewati tahun akademik tertinggi yang pernah diterapkan) yang punya rencana perubahan mahasiswa
        $rencana = ($migrasi_ok && $mode === 'maju') ? $this->kenaikan_kelas->rencana($ta, $aktif) : null;

        $kelompok = array();
        $perhatian = array();
        if ($rencana) {
            foreach ($rencana['rows'] as $r) {
                if ($r['berubah']) {
                    $k = $r['angkatan'] . '|' . $r['lama']['kelas'] . '|' . $r['baru']['kelas'] . '|' . $r['baru']['semester_riil'] . '|' . $r['baru']['status'];
                    if (!isset($kelompok[$k])) {
                        $kelompok[$k] = array('angkatan' => $r['angkatan'], 'kelas_lama' => $r['lama']['kelas'], 'kelas_baru' => $r['baru']['kelas'],
                            'semester_riil' => $r['baru']['semester_riil'], 'status' => $r['baru']['status'], 'jumlah' => 0);
                    }
                    $kelompok[$k]['jumlah']++;
                }
                if ($r['keterangan'] !== null || $r['mentah'] < 1) {
                    $perhatian[] = $r;
                }
            }
            ksort($kelompok);
        }

        $data = array(
            'title' => 'Pratinjau Aktivasi Tahun Akademik',
            'page_title' => 'Pratinjau Aktivasi Tahun Akademik',
            'breadcrumb' => array(
                array('label' => 'Tahun Akademik', 'url' => site_url('Tahun_akademik')),
                array('label' => 'Pratinjau Aktivasi', 'active' => true)
            ),
            'sidebar_menu' => generate_sidebar_menu('admin'),
            'ta' => $ta,
            'aktif' => $aktif,
            'migrasi_ok' => $migrasi_ok,
            'mode' => $mode,
            'hw' => $hw,
            'rencana' => $rencana,
            'kelompok' => array_values($kelompok),
            'perhatian' => $perhatian
        );
        $data['content'] = $this->load->view('tahun_akademik/pratinjau', $data, true);
        $this->load->view('layouts/main', $data);
    }

    /**
     * Aktifkan tahun akademik (hanya POST dari halaman pratinjau), terapkan kenaikan kelas
     * (satu transaksi + snapshot), lalu set mata kuliah aktif.
     */
    public function activate($id) {
        $tahun_akademik = $this->Tahun_akademik_model->get_by_id($id);
        if (!$tahun_akademik) {
            $this->session->set_flashdata('error', 'Tahun akademik tidak ditemukan.');
            redirect('Tahun_akademik');
        }
        if ($this->input->method() !== 'post') {
            redirect('Tahun_akademik/pratinjau/' . $id);
        }

        $tahun_aktif_sebelumnya = $this->Tahun_akademik_model->get_aktif_resmi();
        $tahun_berubah = true;
        if ($tahun_aktif_sebelumnya
            && $tahun_aktif_sebelumnya->tahun_akademik == $tahun_akademik->tahun_akademik
            && $tahun_aktif_sebelumnya->semester == $tahun_akademik->semester) {
            $tahun_berubah = false;
        }

        $this->load->library('Kenaikan_kelas');
        $pakai_library = $this->kenaikan_kelas->kolom_baru_ada();
        $id_batch = null;
        $affected_mhs = 0;

        // Mode: 'maju' (lebih baru dari tahun akademik tertinggi yang pernah diterapkan) = kenaikan kelas;
        // 'ganti_saja' (sama/lebih lama) = hanya mengganti tahun akademik resmi, mahasiswa tidak disentuh.
        $mode = 'maju';
        if ($tahun_berubah && $pakai_library) {
            $m = $this->kenaikan_kelas->mode_aktivasi($tahun_akademik, $tahun_aktif_sebelumnya);
            $mode = $m['mode'];
        }

        // Ganti-saja tanpa riwayat batch: catat jangkar agar tahun akademik tertinggi (HW) tetap tahun akademik resmi sebelum berpindah
        if ($tahun_berubah && $pakai_library && $mode === 'ganti_saja') {
            $this->kenaikan_kelas->pastikan_jangkar($tahun_aktif_sebelumnya, $this->session->userdata('username'));
        }

        // 1) Terapkan kenaikan kelas lebih dulu (transaksi + snapshot). Gagal/diblokir = tahun akademik TIDAK berpindah.
        if ($tahun_berubah && $pakai_library && $mode === 'maju') {
            $hasil = $this->kenaikan_kelas->terapkan($tahun_akademik, $tahun_aktif_sebelumnya, $this->session->userdata('username'));
            if (!$hasil['ok']) {
                $this->session->set_flashdata('error', 'Aktivasi dibatalkan: ' . $hasil['pesan']);
                redirect('Tahun_akademik/pratinjau/' . $id);
            }
            $id_batch = $hasil['id_batch'];
            $affected_mhs = $hasil['berubah'];
        }

        // 2) Pindahkan tahun akademik aktif
        $this->Tahun_akademik_model->deactivate_all();
        if (!$this->Tahun_akademik_model->activate($id)) {
            // Gagal: kembalikan tahun akademik aktif semula dan batalkan kenaikan kelas
            if ($tahun_aktif_sebelumnya) {
                $this->Tahun_akademik_model->activate($tahun_aktif_sebelumnya->id);
            }
            if ($id_batch) {
                $this->kenaikan_kelas->pulihkan($id_batch, $this->session->userdata('username'));
            }
            $this->session->set_flashdata('error', 'Gagal mengaktifkan tahun akademik. Perubahan kelas dibatalkan.');
            redirect('Tahun_akademik');
        }

        // Instalasi lama (migrasi belum dijalankan): cara lama tanpa pratinjau/riwayat
        if ($tahun_berubah && !$pakai_library) {
            $affected_mhs = $this->Mahasiswa_model->recalculate_semester_all($tahun_akademik);
        }

        // Mata kuliah aktif: kenaikan kelas = selalu dibuat ulang (seperti sebelumnya); ganti-saja = hanya dibuat bila belum ada
        // (data mata kuliah aktif tahun akademik lama dan mapping dosen yang terhubung tidak ditimpa).
        if ($mode === 'ganti_saja') {
            $affected_mk = $this->Mata_kuliah_aktif_model->ada_untuk_tahun($tahun_akademik->id) ? 0 : $this->set_mata_kuliah_aktif($tahun_akademik);
        } else {
            $affected_mk = $this->set_mata_kuliah_aktif($tahun_akademik);
        }

        if ($mode === 'ganti_saja') {
            $this->session->set_flashdata('success', 'Tahun akademik berhasil dipindah ke ' . $tahun_akademik->tahun_akademik . ' ' . ucfirst($tahun_akademik->semester)
                . '. Tampilan kelas dan semester mahasiswa kini mengikuti tahun akademik ini (data tersimpan tidak diubah); jadwal dan mata kuliah aktif yang sudah ada dipertahankan.');
            redirect('Tahun_akademik');
        }

        $message = 'Tahun akademik berhasil diaktifkan. ';
        if ($tahun_berubah && $affected_mhs > 0) {
            $message .= 'Semester dan kelas ' . $affected_mhs . ' mahasiswa telah diperbarui'
                . ($id_batch ? ' (dapat dipulihkan lewat Riwayat Kenaikan Kelas). ' : '. ');
        }
        $message .= 'Mata kuliah aktif untuk ' . $affected_mk . ' kurikulum telah diupdate.';
        $this->session->set_flashdata('success', $message);
        redirect('Tahun_akademik');
    }

    /**
     * Riwayat kenaikan kelas (batch terbaru di atas).
     */
    public function riwayat() {
        $this->load->library('Kenaikan_kelas');
        $batch = array();
        if ($this->db->table_exists('kenaikan_kelas_batch')) {
            $this->db->select('b.*, ta1.tahun_akademik AS dari_ta, ta1.semester AS dari_sem, ta2.tahun_akademik AS ke_ta, ta2.semester AS ke_sem');
            $this->db->from('kenaikan_kelas_batch b');
            $this->db->join('tahun_akademik ta1', 'ta1.id = b.id_tahun_akademik_dari', 'left');
            $this->db->join('tahun_akademik ta2', 'ta2.id = b.id_tahun_akademik_ke', 'left');
            $this->db->order_by('b.id', 'DESC');
            $this->db->limit(100);
            $batch = $this->db->get()->result();
        }
        $terakhir = null;
        foreach ($batch as $b) {
            if ($b->status === 'diterapkan') { $terakhir = (int) $b->id; break; }
        }

        $data = array(
            'title' => 'Riwayat Kenaikan Kelas',
            'page_title' => 'Riwayat Kenaikan Kelas',
            'breadcrumb' => array(
                array('label' => 'Tahun Akademik', 'url' => site_url('Tahun_akademik')),
                array('label' => 'Riwayat Kenaikan Kelas', 'active' => true)
            ),
            'sidebar_menu' => generate_sidebar_menu('admin'),
            'batch_list' => $batch,
            'batch_terakhir' => $terakhir
        );
        $data['content'] = $this->load->view('tahun_akademik/riwayat', $data, true);
        $this->load->view('layouts/main', $data);
    }

    public function riwayat_detail($id_batch) {
        $this->db->select('b.*, ta1.tahun_akademik AS dari_ta, ta1.semester AS dari_sem, ta2.tahun_akademik AS ke_ta, ta2.semester AS ke_sem');
        $this->db->from('kenaikan_kelas_batch b');
        $this->db->join('tahun_akademik ta1', 'ta1.id = b.id_tahun_akademik_dari', 'left');
        $this->db->join('tahun_akademik ta2', 'ta2.id = b.id_tahun_akademik_ke', 'left');
        $this->db->where('b.id', (int) $id_batch);
        $batch = $this->db->get()->row();
        if (!$batch) {
            $this->session->set_flashdata('error', 'Batch tidak ditemukan.');
            redirect('Tahun_akademik/riwayat');
        }
        $this->db->select('d.*, m.nim, m.nama_mahasiswa, m.angkatan');
        $this->db->from('kenaikan_kelas_detail d');
        $this->db->join('mahasiswa m', 'm.id = d.id_mahasiswa', 'left');
        $this->db->where('d.id_batch', (int) $id_batch);
        $this->db->order_by('m.angkatan', 'ASC')->order_by('m.nim', 'ASC');
        $detail = $this->db->get()->result();

        $data = array(
            'title' => 'Detail Kenaikan Kelas',
            'page_title' => 'Detail Kenaikan Kelas #' . (int) $id_batch,
            'breadcrumb' => array(
                array('label' => 'Tahun Akademik', 'url' => site_url('Tahun_akademik')),
                array('label' => 'Riwayat Kenaikan Kelas', 'url' => site_url('Tahun_akademik/riwayat')),
                array('label' => 'Detail', 'active' => true)
            ),
            'sidebar_menu' => generate_sidebar_menu('admin'),
            'batch' => $batch,
            'detail' => $detail
        );
        $data['content'] = $this->load->view('tahun_akademik/riwayat_detail', $data, true);
        $this->load->view('layouts/main', $data);
    }

    /**
     * Pulihkan batch kenaikan kelas terakhir (hanya POST). Tahun akademik aktif ikut dikembalikan
     * ke tahun akademik sebelum batch bila batch ini yang memindahkannya.
     */
    public function pulihkan($id_batch) {
        if ($this->input->method() !== 'post') {
            redirect('Tahun_akademik/riwayat');
        }
        $this->load->library('Kenaikan_kelas');
        $batch = $this->db->get_where('kenaikan_kelas_batch', array('id' => (int) $id_batch))->row();
        if (!$batch) {
            $this->session->set_flashdata('error', 'Batch tidak ditemukan.');
            redirect('Tahun_akademik/riwayat');
        }

        $hasil = $this->kenaikan_kelas->pulihkan($id_batch, $this->session->userdata('username'));
        if (!$hasil['ok']) {
            $this->session->set_flashdata('error', $hasil['pesan']);
            redirect('Tahun_akademik/riwayat');
        }

        $pesan = $hasil['pesan'];
        $aktif = $this->Tahun_akademik_model->get_aktif_resmi();
        if ($batch->id_tahun_akademik_dari && $aktif && (int) $aktif->id === (int) $batch->id_tahun_akademik_ke) {
            $dari = $this->Tahun_akademik_model->get_by_id($batch->id_tahun_akademik_dari);
            if ($dari) {
                $this->Tahun_akademik_model->activate($dari->id);
                if (!$this->Mata_kuliah_aktif_model->ada_untuk_tahun($dari->id)) {
                    $this->set_mata_kuliah_aktif($dari);
                }
                $pesan .= ' Tahun akademik aktif dikembalikan ke ' . $dari->tahun_akademik . ' ' . ucfirst($dari->semester) . '.';
            }
        }
        $this->session->set_flashdata('success', $pesan);
        redirect('Tahun_akademik/riwayat');
    }

    /**
     * Set mata kuliah aktif untuk semua kurikulum berdasarkan tahun akademik
     * Logika: Hitung semester aktif per kurikulum berdasarkan selisih tahun
     * 
     * Contoh:
     * - Tahun Akademik: 2025 ganjil, Kurikulum: 2022
     *   Semester aktif: (2025 - 2022) * 2 + 1 (ganjil) = 7
     * - Tahun Akademik: 2025 genap, Kurikulum: 2022
     *   Semester aktif: (2025 - 2022) * 2 + 2 (genap) = 8
     */
    private function set_mata_kuliah_aktif($tahun_akademik) {
        // Ambil tahun dari tahun_akademik (format: 2024/2025)
        $tahun_parts = explode('/', $tahun_akademik->tahun_akademik);
        $tahun_akademik_start = intval($tahun_parts[0]);
        
        // Tentukan nilai semester (ganjil = 1, genap = 2)
        $semester_value = ($tahun_akademik->semester == 'ganjil') ? 1 : 2;
        
        // Ambil semua kurikulum aktif
        $kurikulum_list = $this->Kurikulum_model->get_all_aktif();
        
        // Hapus mata kuliah aktif lama untuk tahun akademik ini
        $this->Mata_kuliah_aktif_model->clear_by_tahun_akademik($tahun_akademik->id);
        
        $kurikulum_count = 0;
        
        foreach ($kurikulum_list as $kurikulum) {
            // Hitung semester aktif untuk kurikulum ini
            $selisih_tahun = $tahun_akademik_start - $kurikulum->tahun_berlaku;
            $semester_aktif = ($selisih_tahun * 2) + $semester_value;
            
            // Batasi maksimal semester 8
            if ($semester_aktif > 8) {
                $semester_aktif = 8;
            }
            
            // Ambil semua kurikulum_detail untuk kurikulum ini
            $this->db->where('id_kurikulum', $kurikulum->id);
            $kurikulum_details = $this->db->get('kurikulum_detail')->result();
            
            // Set mata kuliah aktif untuk semester yang sesuai
            foreach ($kurikulum_details as $kd) {
                if ($kd->semester == $semester_aktif) {
                    $id_mata_kuliah_aktif = $this->Mata_kuliah_aktif_model->set_aktif(
                        $kurikulum->id,
                        $kd->id,
                        $tahun_akademik->id,
                        $semester_aktif
                    );
                    
                    // Re-link mapping yang id_mata_kuliah_aktif-nya NULL untuk tahun akademik ini
                    // Mapping yang id_mata_kuliah_aktif-nya NULL adalah mapping yang terputus saat mata_kuliah_aktif dihapus
                    $this->db->where('id_tahun_akademik', $tahun_akademik->id);
                    $this->db->where('id_mata_kuliah_aktif IS NULL', NULL, FALSE);
                    $this->db->where('kelas IS NOT NULL', NULL, FALSE);
                    $mapping_terputus = $this->db->get('mapping_dosen_mk')->result();
                    
                    // Cari mapping yang sesuai berdasarkan kurikulum_detail
                    // Mapping yang terputus harus memiliki informasi yang sama dengan mata_kuliah_aktif yang baru
                    foreach ($mapping_terputus as $mapping) {
                        // Cek apakah mapping ini untuk kurikulum_detail yang sama
                        // Kita perlu cek berdasarkan informasi yang tersimpan di mapping
                        // Karena id_mata_kuliah_aktif NULL, kita perlu cara lain untuk match
                        // Solusi: ambil dari mapping yang sudah ada id_mata_kuliah_aktif untuk tahun akademik yang sama
                        // dan cek apakah kurikulum_detail-nya sama
                        
                        // Ambil mapping lain yang masih terhubung untuk referensi
                        // Atau kita bisa simpan id_kurikulum_detail di mapping (tapi itu perubahan besar)
                        // Alternatif: re-link berdasarkan pola yang sama (kelas, dosen, dll)
                        // Untuk sementara, kita skip re-link otomatis dan biarkan user re-link manual
                        // Tapi kita bisa tambahkan method untuk re-link manual nanti
                    }
                }
            }
            
            $kurikulum_count++;
        }
        
        // Re-link mapping yang terputus setelah semua mata_kuliah_aktif di-regenerate
        $this->relink_mapping_terputus($tahun_akademik->id);
        
        return $kurikulum_count;
    }

    /**
     * Re-link mapping yang terputus (id_mata_kuliah_aktif = NULL) dengan mata_kuliah_aktif yang baru
     */
    private function relink_mapping_terputus($id_tahun_akademik) {
        // Ambil semua mapping yang terputus untuk tahun akademik ini
        $this->db->where('id_tahun_akademik', $id_tahun_akademik);
        $this->db->where('id_mata_kuliah_aktif IS NULL', NULL, FALSE);
        $this->db->where('id_kurikulum_detail IS NOT NULL', NULL, FALSE); // Hanya yang punya id_kurikulum_detail
        $mapping_terputus = $this->db->get('mapping_dosen_mk')->result();
        
        if (empty($mapping_terputus)) {
            return;
        }
        
        // Untuk setiap mapping terputus, cari mata_kuliah_aktif yang sesuai berdasarkan id_kurikulum_detail
        foreach ($mapping_terputus as $mapping) {
            // Cari mata_kuliah_aktif yang baru dengan kurikulum_detail yang sama
            $mka_baru = $this->Mata_kuliah_aktif_model->get_by_kurikulum_detail_tahun($mapping->id_kurikulum_detail, $id_tahun_akademik);
            
            if ($mka_baru) {
                // Re-link mapping
                $this->db->where('id', $mapping->id);
                $this->db->update('mapping_dosen_mk', array(
                    'id_mata_kuliah_aktif' => $mka_baru->id
                ));
            }
        }
    }

    /**
     * Detail tahun akademik dengan statistik mahasiswa per semester
     */
    public function detail($id) {
        $tahun_akademik = $this->Tahun_akademik_model->get_by_id($id);
        
        if (!$tahun_akademik) {
            $this->session->set_flashdata('error', 'Tahun akademik tidak ditemukan.');
            redirect('Tahun_akademik');
        }

        // Ambil statistik mahasiswa per semester
        $data['statistik_semester'] = array();
        for ($sem = 1; $sem <= 8; $sem++) {
            $this->db->where('status', 'aktif');
            $this->db->where('semester_aktif', $sem);
            $data['statistik_semester'][$sem] = $this->db->count_all_results('mahasiswa');
        }

        // Ambil info mata kuliah aktif per kurikulum (jika tahun akademik aktif)
        $data['kurikulum_mk_aktif'] = array();
        if ($tahun_akademik->is_aktif) {
            $kurikulum_list = $this->Kurikulum_model->get_all_aktif();
            $tahun_parts = explode('/', $tahun_akademik->tahun_akademik);
            $tahun_akademik_start = intval($tahun_parts[0]);
            $semester_value = ($tahun_akademik->semester == 'ganjil') ? 1 : 2;
            
            foreach ($kurikulum_list as $k) {
                $selisih_tahun = $tahun_akademik_start - $k->tahun_berlaku;
                $semester_aktif_kur = min(($selisih_tahun * 2) + $semester_value, 8);
                $mk_aktif = $this->Mata_kuliah_aktif_model->get_aktif_by_kurikulum_tahun($k->id, $tahun_akademik->id);
                
                $data['kurikulum_mk_aktif'][] = array(
                    'kurikulum' => $k,
                    'semester_aktif' => $semester_aktif_kur,
                    'jumlah_mk' => count($mk_aktif)
                );
            }
        }

        $data['tahun_akademik'] = $tahun_akademik;
        
        $data['title'] = 'Detail Tahun Akademik';
        $data['page_title'] = 'Detail Tahun Akademik: ' . $tahun_akademik->tahun_akademik . ' ' . ucfirst($tahun_akademik->semester);
        $data['breadcrumb'] = array(
            array('label' => 'Tahun Akademik', 'url' => site_url('Tahun_akademik')),
            array('label' => 'Detail', 'active' => true)
        );
        $data['sidebar_menu'] = generate_sidebar_menu('admin');
        $data['content'] = $this->load->view('tahun_akademik/detail', $data, true);
        $this->load->view('layouts/main', $data);
    }

    /**
     * Hapus tahun akademik
     */
    public function delete($id) {
        $tahun_akademik = $this->Tahun_akademik_model->get_by_id($id);
        
        if ($tahun_akademik && $tahun_akademik->is_aktif) {
            $this->session->set_flashdata('error', 'Tidak dapat menghapus tahun akademik yang sedang aktif.');
            redirect('Tahun_akademik');
        }

        if ($this->Tahun_akademik_model->delete($id)) {
            $this->session->set_flashdata('success', 'Tahun akademik berhasil dihapus.');
        } else {
            $this->session->set_flashdata('error', 'Gagal menghapus tahun akademik.');
        }
        redirect('Tahun_akademik');
    }
}

