<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Pkl_penilaian_sekprodi Controller
 * Penilaian PKL untuk sekprodi (untuk aspek yang ditugaskan)
 */
class Pkl_penilaian_sekprodi extends MY_Controller {

    public function __construct() {
        parent::__construct();
        $this->check_role('sekprodi');
        $this->load->model('Pkl_pengajuan_model');
        $this->load->model('Pkl_penilaian_model');
        $this->load->model('Pkl_penilaian_detail_model');
        $this->load->model('Pkl_aspek_penilaian_model');
        $this->load->model('Pkl_item_penilaian_model');
        $this->load->model('Pkl_logbook_model');
        $this->load->model('Pkl_nilai_rekap_model');
        $this->load->model('Tahun_akademik_model');
        $this->load->helper('pkl');
        $this->load->library('form_validation');
        $this->load->library('pagination');
    }

    /**
     * Daftar mahasiswa PKL yang sudah layak untuk dinilai oleh sekprodi
     */
    public function index() {
        $tahun_aktif = $this->Tahun_akademik_model->get_aktif();
        if (!$tahun_aktif) {
            $this->session->set_flashdata('error', 'Tidak ada tahun akademik aktif.');
            redirect('Dashboard');
        }

        // Ambil aspek penilaian yang ditugaskan untuk sekprodi
        $aspek_sekprodi = $this->Pkl_aspek_penilaian_model->get_by_ditunjukkan_untuk('sekprodi', true);
        
        // Ambil semua mahasiswa PKL yang sudah layak untuk dinilai
        // (judul sudah di-ACC atau sudah layak sidang)
        $pengajuan_list = $this->Pkl_pengajuan_model->get_all($tahun_aktif->id, 'disetujui');

        // Filter hanya yang judul sudah di-ACC dan hitung nilai/grade
        $mahasiswa_list = array();
        foreach ($pengajuan_list as $pengajuan) {
            if ($pengajuan->status_acc_judul === 'acc') {
                // Cek apakah sudah ada penilaian dari sekprodi
                $penilaian_sekprodi = $this->Pkl_penilaian_model->get_by_mahasiswa_tipe(
                    $pengajuan->id_mahasiswa, 
                    $tahun_aktif->id, 
                    'sekprodi'
                );
                
                $pengajuan->penilaian_sekprodi = $penilaian_sekprodi;
                
                // Ambil nilai rekap untuk menampilkan grade
                $nilai_rekap = $this->Pkl_nilai_rekap_model->get_by_mahasiswa($pengajuan->id_mahasiswa, $tahun_aktif->id);
                $nilai_akhir = 0;
                $grade = null;
                
                if ($nilai_rekap) {
                    $nilai_akhir = floatval($nilai_rekap->nilai_akhir);
                    $grade = isset($nilai_rekap->nilai_huruf) ? $nilai_rekap->nilai_huruf : null;
                    if (($grade === null || $grade === '') && $nilai_akhir > 0) {
                        $grade = konversi_nilai_huruf_pkl($nilai_akhir);
                    }
                } else {
                    // Hitung dari penilaian jika belum ada rekap
                    $penilaian_list = $this->Pkl_penilaian_model->get_by_mahasiswa($pengajuan->id_mahasiswa, $tahun_aktif->id);
                    if (!empty($penilaian_list)) {
                        $p1 = 0; $p2 = 0; $p3 = 0; $p4 = 0; $p5 = 0; $p6 = 0;
                        foreach ($penilaian_list as $pen) {
                            $p1 = max($p1, floatval($pen->nilai_p1));
                            $p2 = max($p2, floatval($pen->nilai_p2));
                            $p3 = max($p3, floatval($pen->nilai_p3));
                            $p4 = max($p4, floatval($pen->nilai_p4));
                            $p5 = max($p5, floatval($pen->nilai_p5));
                            $p6 = max($p6, floatval($pen->nilai_p6));
                        }
                        if ($p1 + $p2 + $p3 + $p4 + $p5 + $p6 > 0) {
                            $nilai_akhir = hitung_nilai_akhir_pkl($p1, $p2, $p3, $p4, $p5, $p6);
                            $grade = konversi_nilai_huruf_pkl($nilai_akhir);
                        }
                    }
                }
                
                // Tambahkan nilai dan grade ke object pengajuan
                $pengajuan->nilai_akhir = $nilai_akhir;
                $pengajuan->grade = $grade;
                
                $mahasiswa_list[] = $pengajuan;
            }
        }

        $data = array(
            'title' => 'Penilaian PKL',
            'page_title' => 'Penilaian PKL',
            'breadcrumb' => array(
                array('label' => 'PKL', 'url' => '#'),
                array('label' => 'Penilaian PKL', 'active' => true)
            ),
            'sidebar_menu' => generate_sidebar_menu('sekprodi'),
            'mahasiswa_list' => $mahasiswa_list,
            'aspek_sekprodi' => $aspek_sekprodi,
            'tahun_aktif' => $tahun_aktif
        );

        $data['content'] = $this->load->view('sekprodi/pkl_penilaian/index', $data, true);
        $this->load->view('layouts/main', $data);
    }

    /**
     * Form input penilaian untuk mahasiswa tertentu
     */
    public function form($pengajuan_id) {
        $tahun_aktif = $this->Tahun_akademik_model->get_aktif();
        if (!$tahun_aktif) {
            $this->session->set_flashdata('error', 'Tidak ada tahun akademik aktif.');
            redirect('Pkl_penilaian_sekprodi');
        }

        // Untuk sekprodi, kita perlu ID dosen yang valid karena constraint foreign key
        // Karena sekprodi tidak memiliki ref_id (NULL), kita perlu menggunakan user_id
        // untuk mencari dosen terkait atau menggunakan nilai default
        $user_id = $this->session->userdata('user_id');
        $id_sekprodi = null;
        
        // Cek apakah user sekprodi juga memiliki record di tabel dosen (via ref_id)
        $ref_id = $this->session->userdata('ref_id');
        if ($ref_id) {
            // Jika ref_id ada, gunakan sebagai id_penilai
            $id_sekprodi = $ref_id;
        } else {
            // Jika tidak ada ref_id, cari dosen berdasarkan user_id
            $dosen_by_user = $this->db->select('d.id')
                                      ->from('dosen d')
                                      ->join('users u', 'u.ref_id = d.id', 'inner')
                                      ->where('u.id', $user_id)
                                      ->where('d.status', 'aktif')
                                      ->get()
                                      ->row();
            
            if ($dosen_by_user) {
                $id_sekprodi = $dosen_by_user->id;
            }
        }
        
        // Jika masih null, berarti sekprodi tidak memiliki record dosen
        // Karena constraint foreign key memerlukan ID dosen yang valid,
        // kita perlu menggunakan workaround: gunakan ID dosen pertama sebagai placeholder
        // TODO: Idealnya, sekprodi yang melakukan penilaian juga harus memiliki record di tabel dosen
        if (!$id_sekprodi) {
            $first_dosen = $this->db->select('id')
                                   ->from('dosen')
                                   ->where('status', 'aktif')
                                   ->limit(1)
                                   ->order_by('id', 'ASC')
                                   ->get()
                                   ->row();
            
            if (!$first_dosen) {
                $this->session->set_flashdata('error', 'Sistem tidak dapat menemukan ID dosen untuk menyimpan penilaian. Silakan hubungi administrator.');
                redirect('Pkl_penilaian_sekprodi');
            }
            
            $id_sekprodi = $first_dosen->id;
        }

        // Ambil pengajuan PKL
        $pengajuan = $this->Pkl_pengajuan_model->get_by_id($pengajuan_id);
        if (!$pengajuan || $pengajuan->status_acc_judul !== 'acc') {
            $this->session->set_flashdata('error', 'Data pengajuan tidak ditemukan atau judul belum disetujui.');
            redirect('Pkl_penilaian_sekprodi');
        }

        // Ambil aspek penilaian untuk sekprodi
        $aspek_list = $this->Pkl_aspek_penilaian_model->get_by_ditunjukkan_untuk('sekprodi', true);
        
        if (empty($aspek_list)) {
            $this->session->set_flashdata('error', 'Tidak ada aspek penilaian yang ditugaskan untuk sekprodi. Silakan hubungi admin untuk setting aspek penilaian.');
            redirect('Pkl_penilaian_sekprodi');
        }
        
        // Untuk setiap aspek, ambil item penilaiannya
        foreach ($aspek_list as &$aspek) {
            $aspek->items = $this->Pkl_item_penilaian_model->get_by_aspek($aspek->id, true);
        }
        unset($aspek);

        // Cek apakah sudah ada penilaian
        $penilaian = $this->Pkl_penilaian_model->get_by_mahasiswa_tipe(
            $pengajuan->id_mahasiswa, 
            $tahun_aktif->id, 
            'sekprodi'
        );

        // Ambil detail penilaian jika sudah ada
        $penilaian_details = array();
        if ($penilaian) {
            $details = $this->Pkl_penilaian_detail_model->get_by_penilaian($penilaian->id);
            foreach ($details as $detail) {
                $penilaian_details[$detail->id_item_penilaian] = $detail->nilai;
            }
        }

        // Ambil logbook untuk P1 (kehadiran)
        $logbook_list = $this->Pkl_logbook_model->get_by_mahasiswa($pengajuan->id_mahasiswa, $tahun_aktif->id);

        if ($this->input->method() === 'post') {
            // Validasi input nilai per item
            $errors = array();
            $nilai_items = array();
            
            foreach ($aspek_list as $aspek) {
                foreach ($aspek->items as $item) {
                    $field_name = 'nilai_item_' . $item->id;
                    $nilai = $this->input->post($field_name);
                    
                    if ($nilai === '' || $nilai === null) {
                        $errors[] = "Nilai untuk item '{$item->pertanyaan}' belum diisi";
                    } elseif (!is_numeric($nilai) || $nilai < 0 || $nilai > 100) {
                        $errors[] = "Nilai untuk item '{$item->pertanyaan}' harus antara 0-100";
                    } else {
                        $nilai_items[$item->id] = array(
                            'nilai' => floatval($nilai),
                            'bobot' => floatval($item->bobot),
                            'id_aspek' => $aspek->id
                        );
                    }
                }
            }

            if (!empty($errors)) {
                $this->session->set_flashdata('error', implode('<br>', $errors));
            } else {
                // Hitung nilai P untuk setiap aspek
                $nilai_p = array();
                foreach ($aspek_list as $aspek) {
                    $items_aspek = array();
                    foreach ($aspek->items as $item) {
                        if (isset($nilai_items[$item->id])) {
                            $items_aspek[] = $nilai_items[$item->id];
                        }
                    }
                    $nilai_p[$aspek->kode_aspek] = hitung_nilai_p_dari_items($items_aspek);
                }

                // Set nilai P yang tidak diinput sekprodi menjadi 0
                $nilai_p1 = isset($nilai_p['P1']) ? $nilai_p['P1'] : 0;
                $nilai_p2 = isset($nilai_p['P2']) ? $nilai_p['P2'] : 0;
                $nilai_p3 = isset($nilai_p['P3']) ? $nilai_p['P3'] : 0;
                $nilai_p4 = isset($nilai_p['P4']) ? $nilai_p['P4'] : 0;
                $nilai_p5 = isset($nilai_p['P5']) ? $nilai_p['P5'] : 0;
                $nilai_p6 = isset($nilai_p['P6']) ? $nilai_p['P6'] : 0;

                // Simpan atau update penilaian
                $penilaian_data = array(
                    'id_mahasiswa' => $pengajuan->id_mahasiswa,
                    'id_tahun_akademik' => $tahun_aktif->id,
                    'id_penilai' => $id_sekprodi,
                    'tipe_penilai' => 'sekprodi',
                    'nilai_p1' => $nilai_p1,
                    'nilai_p2' => $nilai_p2,
                    'nilai_p3' => $nilai_p3,
                    'nilai_p4' => $nilai_p4,
                    'nilai_p5' => $nilai_p5,
                    'nilai_p6' => $nilai_p6,
                    'tanggal_penilaian' => date('Y-m-d')
                );

                if ($penilaian) {
                    // Update penilaian
                    $penilaian_id = $penilaian->id;
                    $this->Pkl_penilaian_model->update($penilaian_id, $penilaian_data);
                    
                    // Hapus detail lama
                    $this->db->where('id_penilaian', $penilaian_id);
                    $this->db->delete('pkl_penilaian_detail');
                } else {
                    // Create penilaian
                    $penilaian_id = $this->Pkl_penilaian_model->create($penilaian_data);
                }

                // Simpan detail penilaian per item
                foreach ($nilai_items as $id_item => $data_item) {
                    $this->Pkl_penilaian_detail_model->create(array(
                        'id_penilaian' => $penilaian_id,
                        'id_item_penilaian' => $id_item,
                        'nilai' => $data_item['nilai']
                    ));
                }

                $this->session->set_flashdata('success', 'Penilaian berhasil disimpan.');
                redirect('Pkl_penilaian_sekprodi');
            }
        }

        $data = array(
            'title' => 'Input Penilaian PKL',
            'page_title' => 'Input Penilaian PKL',
            'breadcrumb' => array(
                array('label' => 'PKL', 'url' => '#'),
                array('label' => 'Penilaian PKL', 'url' => site_url('Pkl_penilaian_sekprodi')),
                array('label' => 'Input Penilaian', 'active' => true)
            ),
            'sidebar_menu' => generate_sidebar_menu('sekprodi'),
            'pengajuan' => $pengajuan,
            'aspek_list' => $aspek_list,
            'penilaian' => $penilaian,
            'penilaian_details' => $penilaian_details,
            'logbook_list' => $logbook_list,
            'tahun_aktif' => $tahun_aktif
        );

        $data['content'] = $this->load->view('sekprodi/pkl_penilaian/form', $data, true);
        $this->load->view('layouts/main', $data);
    }
}

