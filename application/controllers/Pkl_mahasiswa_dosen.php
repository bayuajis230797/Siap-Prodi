<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Pkl_mahasiswa_dosen Controller
 * Daftar mahasiswa bimbingan PKL untuk dosen
 */
class Pkl_mahasiswa_dosen extends MY_Controller {

    public function __construct() {
        parent::__construct();
        $this->check_role('dosen');
        $this->load->model('Pkl_pengajuan_model');
        $this->load->model('Pkl_penilaian_model');
        $this->load->model('Pkl_penilaian_detail_model');
        $this->load->model('Pkl_aspek_penilaian_model');
        $this->load->model('Pkl_item_penilaian_model');
        $this->load->model('Pkl_logbook_model');
        $this->load->model('Pkl_absensi_model');
        $this->load->model('Pkl_tempat_model');
        $this->load->model('Pkl_nilai_rekap_model');
        $this->load->model('Tahun_akademik_model');
        $this->load->helper('pkl');
        $this->load->helper('notifikasi');
        $this->load->library('form_validation');
    }

    /**
     * Daftar mahasiswa bimbingan PKL
     */
    public function index() {
        $id_dosen = $this->session->userdata('ref_id');
        if (!$id_dosen) {
            show_404();
        }

        $tahun_aktif = $this->Tahun_akademik_model->get_aktif();
        if (!$tahun_aktif) {
            $this->session->set_flashdata('error', 'Tidak ada tahun akademik aktif.');
            redirect('Dashboard');
        }

        // Ambil semua pengajuan PKL dimana dosen ini adalah pembimbing dan judul sudah di-ACC
        $pengajuan_list = $this->Pkl_pengajuan_model->get_all($tahun_aktif->id, 'disetujui', $id_dosen);
        
        // Filter hanya yang judul sudah di-ACC (bukan placeholder) dan hitung nilai/grade
        $mahasiswa_list = array();
        foreach ($pengajuan_list as $pengajuan) {
            if ($pengajuan->judul_pkl && $pengajuan->judul_pkl !== $this->Pkl_pengajuan_model::PLACEHOLDER_JUDUL 
                && $pengajuan->status_acc_judul === 'acc') {
                
                // Ambil nilai rekap untuk menampilkan grade
                $nilai_rekap = $this->Pkl_nilai_rekap_model->get_by_mahasiswa($pengajuan->id_mahasiswa, $tahun_aktif->id);
                $nilai_akhir = 0;
                $grade = null;
                
                if ($nilai_rekap) {
                    $nilai_akhir = $nilai_rekap->nilai_akhir;
                    $grade = $nilai_rekap->nilai_huruf;
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
            'title' => 'Daftar Mahasiswa PKL',
            'page_title' => 'Daftar Mahasiswa PKL',
            'breadcrumb' => array(
                array('label' => 'PKL', 'url' => '#'),
                array('label' => 'Daftar Mahasiswa', 'active' => true)
            ),
            'sidebar_menu' => generate_sidebar_menu('dosen'),
            'mahasiswa_list' => $mahasiswa_list,
            'tahun_aktif' => $tahun_aktif
        );

        $data['content'] = $this->load->view('dosen/pkl_mahasiswa_index', $data, true);
        $this->load->view('layouts/main', $data);
    }

    /**
     * Form layak sidang dan input nilai
     */
    public function layak_sidang($pengajuan_id) {
        $id_dosen = $this->session->userdata('ref_id');
        if (!$id_dosen) {
            show_404();
        }

        $tahun_aktif = $this->Tahun_akademik_model->get_aktif();
        if (!$tahun_aktif) {
            $this->session->set_flashdata('error', 'Tidak ada tahun akademik aktif.');
            redirect('Pkl_mahasiswa_dosen');
        }

        // Ambil pengajuan PKL
        $pengajuan = $this->Pkl_pengajuan_model->get_by_id($pengajuan_id);
        if (!$pengajuan || $pengajuan->id_pembimbing != $id_dosen) {
            show_404();
        }

        // Cek apakah judul sudah di-ACC
        if ($pengajuan->status_acc_judul !== 'acc') {
            $this->session->set_flashdata('error', 'Judul PKL belum disetujui.');
            redirect('Pkl_mahasiswa_dosen');
        }

        // Ambil aspek penilaian untuk pembimbing dosen
        $aspek_list = $this->Pkl_aspek_penilaian_model->get_by_ditunjukkan_untuk('pembimbing_dosen', true);
        
        if (empty($aspek_list)) {
            $this->session->set_flashdata('error', 'Tidak ada aspek penilaian yang ditugaskan untuk pembimbing dosen. Silakan hubungi admin untuk setting aspek penilaian.');
            redirect('Pkl_mahasiswa_dosen');
        }
        
        // Untuk setiap aspek, ambil item penilaiannya
        foreach ($aspek_list as &$aspek) {
            $aspek->items = $this->Pkl_item_penilaian_model->get_by_aspek($aspek->id, true);
        }
        unset($aspek);

        // Cek apakah sudah ada penilaian
        $penilaian = $this->Pkl_penilaian_model->get_by_mahasiswa_penilai(
            $pengajuan->id_mahasiswa, 
            $tahun_aktif->id, 
            $id_dosen, 
            'pembimbing_dosen'
        );

        // Ambil detail penilaian jika sudah ada
        $penilaian_details = array();
        if ($penilaian) {
            $details = $this->Pkl_penilaian_detail_model->get_by_penilaian($penilaian->id);
            foreach ($details as $detail) {
                $penilaian_details[$detail->id_item_penilaian] = $detail->nilai;
            }
        }

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

                // Set nilai P yang tidak diinput pembimbing dosen menjadi 0
                $nilai_p1 = isset($nilai_p['P1']) ? $nilai_p['P1'] : 0;
                $nilai_p2 = isset($nilai_p['P2']) ? $nilai_p['P2'] : 0;
                $nilai_p3 = isset($nilai_p['P3']) ? $nilai_p['P3'] : 0;
                $nilai_p4 = isset($nilai_p['P4']) ? $nilai_p['P4'] : 0;
                $nilai_p5 = isset($nilai_p['P5']) ? $nilai_p['P5'] : 0;
                $nilai_p6 = isset($nilai_p['P6']) ? $nilai_p['P6'] : 0;

                // Hitung nilai akhir
                $nilai_akhir = hitung_nilai_akhir_pkl($nilai_p1, $nilai_p2, $nilai_p3, $nilai_p4, $nilai_p5, $nilai_p6);
                $nilai_huruf = konversi_nilai_huruf_pkl($nilai_akhir);

                // Simpan atau update penilaian
                $penilaian_data = array(
                    'id_mahasiswa' => $pengajuan->id_mahasiswa,
                    'id_tahun_akademik' => $tahun_aktif->id,
                    'id_penilai' => $id_dosen,
                    'tipe_penilai' => 'pembimbing_dosen',
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
                    $this->Pkl_penilaian_detail_model->delete_by_penilaian($penilaian_id);
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

                // Update pengajuan PKL - set layak sidang
                $this->Pkl_pengajuan_model->update($pengajuan_id, array(
                    'is_layak_sidang' => 1
                ));

                send_in_app_notification_by_ref(
                    'mahasiswa',
                    (int) $pengajuan->id_mahasiswa,
                    'Dinyatakan Layak Sidang PKL',
                    'Anda dinyatakan layak sidang oleh dosen pembimbing. Silakan lanjut pendaftaran sidang PKL.',
                    site_url('Pendaftaran_sidang_pkl')
                );
                send_in_app_notification_by_role(
                    'sekprodi',
                    'Mahasiswa Layak Sidang PKL',
                    'Terdapat mahasiswa yang sudah dinyatakan layak sidang oleh dosen pembimbing.',
                    site_url('Pkl_verifikasi_berkas_sidang')
                );

                $this->session->set_flashdata('success', 'Mahasiswa dinyatakan layak sidang dan nilai berhasil disimpan.');
                redirect('Pkl_mahasiswa_dosen');
            }
        }

        $data = array(
            'title' => 'Layak Sidang & Input Nilai',
            'page_title' => 'Layak Sidang & Input Nilai',
            'breadcrumb' => array(
                array('label' => 'PKL', 'url' => '#'),
                array('label' => 'Daftar Mahasiswa', 'url' => site_url('Pkl_mahasiswa_dosen')),
                array('label' => 'Layak Sidang', 'active' => true)
            ),
            'sidebar_menu' => generate_sidebar_menu('dosen'),
            'pengajuan' => $pengajuan,
            'aspek_list' => $aspek_list,
            'penilaian' => $penilaian,
            'penilaian_details' => $penilaian_details,
            'tahun_aktif' => $tahun_aktif
        );

        $data['content'] = $this->load->view('dosen/pkl_layak_sidang_form', $data, true);
        $this->load->view('layouts/main', $data);
    }

    /**
     * Detail penilaian untuk mahasiswa bimbingan tertentu
     */
    public function detail_penilaian($pengajuan_id) {
        $id_dosen = $this->session->userdata('ref_id');
        if (!$id_dosen) {
            show_404();
        }

        $tahun_aktif = $this->Tahun_akademik_model->get_aktif();
        if (!$tahun_aktif) {
            $this->session->set_flashdata('error', 'Tidak ada tahun akademik aktif.');
            redirect('Pkl_mahasiswa_dosen');
        }

        // Ambil pengajuan PKL
        $pengajuan = $this->Pkl_pengajuan_model->get_by_id($pengajuan_id);
        if (!$pengajuan) {
            $this->session->set_flashdata('error', 'Data pengajuan tidak ditemukan.');
            redirect('Pkl_mahasiswa_dosen');
        }

        // Pastikan dosen ini adalah pembimbing mahasiswa ini
        if ($pengajuan->id_pembimbing != $id_dosen) {
            $this->session->set_flashdata('error', 'Anda tidak memiliki akses untuk melihat penilaian mahasiswa ini.');
            redirect('Pkl_mahasiswa_dosen');
        }

        // Ambil semua penilaian untuk mahasiswa ini
        $penilaian_list = $this->Pkl_penilaian_model->get_by_mahasiswa($pengajuan->id_mahasiswa, $tahun_aktif->id);
        
        // Untuk setiap penilaian, ambil detail per item
        foreach ($penilaian_list as &$penilaian) {
            $penilaian->details = $this->Pkl_penilaian_detail_model->get_by_penilaian($penilaian->id);
            
            // Ambil info penilai
            if ($penilaian->tipe_penilai === 'pembimbing_dosen' || $penilaian->tipe_penilai === 'penguji') {
                $this->load->model('Dosen_model');
                $dosen = $this->Dosen_model->get_by_id($penilaian->id_penilai);
                $penilaian->penilai_nama = $dosen ? $dosen->nama_dosen : '-';
            } else {
                $penilaian->penilai_nama = ucfirst(str_replace('_', ' ', $penilaian->tipe_penilai));
            }
        }
        unset($penilaian);

        // Ambil nilai rekap
        $this->load->model('Pkl_nilai_rekap_model');
        $nilai_rekap = $this->Pkl_nilai_rekap_model->get_by_mahasiswa($pengajuan->id_mahasiswa, $tahun_aktif->id);

        // Ambil semua aspek dan item untuk display
        $aspek_list = $this->Pkl_aspek_penilaian_model->get_all(true);
        foreach ($aspek_list as &$aspek) {
            $aspek->items = $this->Pkl_item_penilaian_model->get_by_aspek($aspek->id, true);
        }
        unset($aspek);

        $data = array(
            'title' => 'Detail Aktivitas Penilaian PKL',
            'page_title' => 'Detail Aktivitas Penilaian PKL',
            'breadcrumb' => array(
                array('label' => 'PKL', 'url' => '#'),
                array('label' => 'Daftar Mahasiswa', 'url' => site_url('Pkl_mahasiswa_dosen')),
                array('label' => 'Detail Penilaian', 'active' => true)
            ),
            'sidebar_menu' => generate_sidebar_menu('dosen'),
            'pengajuan' => $pengajuan,
            'penilaian_list' => $penilaian_list,
            'nilai_rekap' => $nilai_rekap,
            'aspek_list' => $aspek_list,
            'tahun_aktif' => $tahun_aktif
        );

        $data['content'] = $this->load->view('dosen/pkl_aktivitas_penilaian_detail', $data, true);
        $this->load->view('layouts/main', $data);
    }

    /**
     * Logbook dan Absensi Mahasiswa Bimbingan
     * Menampilkan daftar mahasiswa bimbingan dan detail logbook & absensi
     */
    public function monitoring($pengajuan_id = null) {
        $id_dosen = $this->session->userdata('ref_id');
        if (!$id_dosen) {
            show_404();
        }

        $tahun_aktif = $this->Tahun_akademik_model->get_aktif();
        if (!$tahun_aktif) {
            $this->session->set_flashdata('error', 'Tidak ada tahun akademik aktif.');
            redirect('Dashboard');
        }

        // Ambil semua tempat PKL yang sudah di-approve sekprodi dimana dosen ini adalah pembimbing
        // Join dengan pkl_pengajuan untuk mendapatkan data lengkap
        // Gunakan INNER JOIN untuk memastikan hanya data yang memiliki pengajuan dengan pembimbing
        $this->db->select('pt.*, m.nim, m.nama_mahasiswa, m.kelas, ta.tahun_akademik, ta.semester,
                          pp.id as id_pengajuan, pp.id_pembimbing, pp.judul_pkl, pp.status as status_pengajuan, pp.status_acc_judul,
                          d.nama_dosen as pembimbing_nama, d.nidn as pembimbing_nidn');
        $this->db->from('pkl_tempat pt');
        $this->db->join('mahasiswa m', 'm.id = pt.id_mahasiswa', 'inner');
        $this->db->join('tahun_akademik ta', 'ta.id = pt.id_tahun_akademik', 'inner');
        $this->db->join('pkl_pengajuan pp', 'pp.id_mahasiswa = pt.id_mahasiswa AND pp.id_tahun_akademik = pt.id_tahun_akademik', 'inner');
        $this->db->join('dosen d', 'd.id = pp.id_pembimbing', 'inner');
        $this->db->where('pt.id_tahun_akademik', $tahun_aktif->id);
        $this->db->where('pt.status_acc_tempat', 'disetujui');
        $this->db->where('pp.id_pembimbing', $id_dosen);
        $this->db->order_by('m.kelas', 'ASC');
        $this->db->order_by('m.nim', 'ASC');
        $tempat_list = $this->db->get()->result();
        
        // Konversi ke format pengajuan untuk kompatibilitas
        $mahasiswa_list = array();
        foreach ($tempat_list as $tempat) {
            $pengajuan = new stdClass();
            $pengajuan->id = $tempat->id_pengajuan ? $tempat->id_pengajuan : null;
            $pengajuan->id_mahasiswa = $tempat->id_mahasiswa;
            $pengajuan->id_tahun_akademik = $tempat->id_tahun_akademik;
            $pengajuan->id_pembimbing = $tempat->id_pembimbing;
            $pengajuan->judul_pkl = $tempat->judul_pkl;
            $pengajuan->status = $tempat->status_pengajuan;
            $pengajuan->status_acc_judul = $tempat->status_acc_judul;
            $pengajuan->nim = $tempat->nim;
            $pengajuan->nama_mahasiswa = $tempat->nama_mahasiswa;
            $pengajuan->kelas = $tempat->kelas;
            $pengajuan->tahun_akademik = $tempat->tahun_akademik;
            $pengajuan->semester = $tempat->semester;
            $pengajuan->pembimbing_nama = $tempat->pembimbing_nama;
            $pengajuan->pembimbing_nidn = $tempat->pembimbing_nidn;
            $mahasiswa_list[] = $pengajuan;
        }

        // Jika ada pengajuan_id, tampilkan detail monitoring untuk mahasiswa tersebut
        $selected_pengajuan = null;
        $tempat_pkl = null;
        $dosen_pembimbing = null;
        $logbook_list = array();
        $absensi_list = array();
        $statistik_logbook = null;
        $statistik_absensi = null;

        if ($pengajuan_id) {
            // Coba ambil pengajuan berdasarkan id
            $selected_pengajuan = $this->Pkl_pengajuan_model->get_by_id($pengajuan_id);
            
            // Jika tidak ditemukan, mungkin parameter adalah id_mahasiswa
            if (!$selected_pengajuan) {
                // Coba ambil berdasarkan id_mahasiswa
                $selected_pengajuan = $this->Pkl_pengajuan_model->get_by_mahasiswa($pengajuan_id, $tahun_aktif->id);
            }
            
            // Jika masih tidak ditemukan, coba ambil dari tempat PKL
            if (!$selected_pengajuan) {
                // Ambil tempat PKL berdasarkan id_mahasiswa
                $tempat_pkl_check = $this->Pkl_tempat_model->get_by_mahasiswa($pengajuan_id, $tahun_aktif->id);
                if ($tempat_pkl_check && $tempat_pkl_check->status_acc_tempat === 'disetujui') {
                    // Ambil pengajuan berdasarkan id_mahasiswa
                    $selected_pengajuan = $this->Pkl_pengajuan_model->get_by_mahasiswa($tempat_pkl_check->id_mahasiswa, $tahun_aktif->id);
                    
                    // Jika pengajuan tidak ada, buat object dummy dari tempat
                    if (!$selected_pengajuan) {
                        $this->load->model('Mahasiswa_model');
                        $mahasiswa = $this->Mahasiswa_model->get_by_id($tempat_pkl_check->id_mahasiswa);
                        $selected_pengajuan = new stdClass();
                        $selected_pengajuan->id = null;
                        $selected_pengajuan->id_mahasiswa = $tempat_pkl_check->id_mahasiswa;
                        $selected_pengajuan->id_tahun_akademik = $tahun_aktif->id;
                        $selected_pengajuan->id_pembimbing = null;
                        $selected_pengajuan->judul_pkl = 'Menunggu Pengajuan Judul';
                        $selected_pengajuan->status = 'pending';
                        $selected_pengajuan->status_acc_judul = 'pending';
                        $selected_pengajuan->nim = $mahasiswa ? $mahasiswa->nim : '';
                        $selected_pengajuan->nama_mahasiswa = $mahasiswa ? $mahasiswa->nama_mahasiswa : '';
                        $selected_pengajuan->kelas = $mahasiswa ? $mahasiswa->kelas : '';
                    }
                }
            }
            
            if ($selected_pengajuan) {
                // Cek apakah dosen ini adalah pembimbing
                $tempat_pkl_check = $this->Pkl_tempat_model->get_by_mahasiswa($selected_pengajuan->id_mahasiswa, $tahun_aktif->id);
                
                // Validasi: tempat sudah di-approve dan pembimbing adalah dosen ini
                if ($tempat_pkl_check && $tempat_pkl_check->status_acc_tempat === 'disetujui' && 
                    $selected_pengajuan->id_pembimbing == $id_dosen) {
                    
                    // Ambil data tempat PKL untuk ditampilkan
                    $tempat_pkl = $tempat_pkl_check;
                    
                // Ambil logbook mahasiswa langsung menggunakan id_mahasiswa
                $logbook_list = $this->Pkl_logbook_model->get_by_mahasiswa($selected_pengajuan->id_mahasiswa, $tahun_aktif->id);
                $statistik_logbook = $this->Pkl_logbook_model->get_statistik($selected_pengajuan->id_mahasiswa, $tahun_aktif->id);
                
                // Load helper GPS untuk menghitung jarak
                $this->load->helper('gps');
                
                // Hitung jarak untuk setiap logbook yang memiliki koordinat
                if ($tempat_pkl && $tempat_pkl->latitude && $tempat_pkl->longitude) {
                    foreach ($logbook_list as $logbook) {
                        if ($logbook->latitude_logbook && $logbook->longitude_logbook) {
                            $logbook->jarak_dari_tempat = calculate_distance_gps(
                                $tempat_pkl->latitude,
                                $tempat_pkl->longitude,
                                $logbook->latitude_logbook,
                                $logbook->longitude_logbook
                            );
                        } else {
                            $logbook->jarak_dari_tempat = null;
                        }
                    }
                }
                
                // Ambil absensi mahasiswa langsung menggunakan id_mahasiswa
                $absensi_list = $this->Pkl_absensi_model->get_by_mahasiswa($selected_pengajuan->id_mahasiswa);
                
                $statistik_absensi = $this->Pkl_absensi_model->get_statistik($selected_pengajuan->id_mahasiswa);
                
                // Ambil informasi dosen pembimbing akademik jika ada
                $dosen_pembimbing = null;
                if ($selected_pengajuan->id_pembimbing) {
                    $this->load->model('Dosen_model');
                    $dosen_pembimbing = $this->Dosen_model->get_by_id($selected_pengajuan->id_pembimbing);
                }
                } else {
                    $this->session->set_flashdata('error', 'Anda tidak memiliki akses untuk melihat data mahasiswa ini.');
                    redirect('Pkl_mahasiswa_dosen/monitoring');
                }
            } else {
                $this->session->set_flashdata('error', 'Data mahasiswa PKL tidak ditemukan.');
                redirect('Pkl_mahasiswa_dosen/monitoring');
            }
        }

        $data = array(
            'title' => 'Logbook & Absensi PKL',
            'page_title' => 'Logbook & Absensi PKL',
            'breadcrumb' => array(
                array('label' => 'PKL', 'url' => '#'),
                array('label' => 'Logbook & Absensi', 'active' => true)
            ),
            'sidebar_menu' => generate_sidebar_menu('dosen'),
            'mahasiswa_list' => $mahasiswa_list,
            'selected_pengajuan' => $selected_pengajuan,
            'tempat_pkl' => $tempat_pkl,
            'dosen_pembimbing' => $dosen_pembimbing,
            'logbook_list' => $logbook_list,
            'absensi_list' => $absensi_list,
            'statistik_logbook' => $statistik_logbook,
            'statistik_absensi' => $statistik_absensi,
            'tahun_aktif' => $tahun_aktif
        );

        $data['content'] = $this->load->view('dosen/pkl/monitoring', $data, true);
        $this->load->view('layouts/main', $data);
    }

}

