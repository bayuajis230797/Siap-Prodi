<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Jadwal UAS Controller
 * Menampilkan jadwal UAS untuk Sekprodi/Admin
 */
class Jadwal_uas extends MY_Controller {

    private $hari_map = array(
        'minggu' => 0,
        'senin' => 1,
        'selasa' => 2,
        'rabu' => 3,
        'kamis' => 4,
        'jumat' => 5,
        'sabtu' => 6
    );

    public function __construct() {
        parent::__construct();
        // Izinkan sekprodi, admin, dosen, tendik, kaprodi (baca)
        $this->check_role(array('sekprodi', 'admin', 'dosen', 'tendik', 'kaprodi'));
        $this->load->model('Jadwal_uas_model');
        $this->load->model('Tahun_akademik_model');
        $this->load->model('Jadwal_kuliah_model');
        $this->load->model('Mahasiswa_model');
        $this->load->model('Tendik_model');
        $this->load->model('Dosen_model');
        $this->load->library('form_validation');
    }

    /**
     * Halaman daftar jadwal UAS
     */
    public function index() {
        $this->load->library('pagination');

        $tahun_aktif = $this->Tahun_akademik_model->get_aktif();
        $id_tahun_akademik = $tahun_aktif ? $tahun_aktif->id : null;

        $filter_tanggal = $this->input->get('tanggal');
        $filter_kelas = $this->input->get('kelas');
        $per_page = $this->input->get('per_page') ? (int)$this->input->get('per_page') : 10;
        $per_page = in_array($per_page, array(10, 25, 50, 100)) ? $per_page : 10;

        $config['base_url'] = site_url('Jadwal_uas/index');
        $config['total_rows'] = $id_tahun_akademik ? $this->Jadwal_uas_model->count_all($id_tahun_akademik, $filter_tanggal, $filter_kelas) : 0;
        $config['per_page'] = $per_page;
        $config['uri_segment'] = 3;
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

        $query_params = array();
        if (!empty($filter_tanggal)) {
            $query_params['tanggal'] = $filter_tanggal;
        }
        if (!empty($filter_kelas)) {
            $query_params['kelas'] = $filter_kelas;
        }
        if ($per_page != 10) {
            $query_params['per_page'] = $per_page;
        }
        if (!empty($query_params)) {
            $query_string = http_build_query($query_params);
            $config['suffix'] = '?' . $query_string;
            $config['first_url'] = site_url('Jadwal_uas/index?' . $query_string);
        }

        $this->pagination->initialize($config);

        $page = ($this->uri->segment(3)) ? (int)$this->uri->segment(3) : 1;
        $page = $page < 1 ? 1 : $page;
        $offset = ($page - 1) * $config['per_page'];

        $jadwal_list = array();
        $kelas_options = array();
        $bentrok_map = array();

        if ($id_tahun_akademik) {
            $jadwal_list = $this->Jadwal_uas_model->get_all($id_tahun_akademik, $config['per_page'], $offset, $filter_tanggal, $filter_kelas);
            $all_jadwal = $this->Jadwal_uas_model->get_all($id_tahun_akademik, 10000, 0);
            $bentrok_map = jadwal_ujian_build_pengawas_bentrok_map($all_jadwal);

            $kelas_records = $this->Jadwal_uas_model->get_kelas_with_mapping($id_tahun_akademik);
            if (!empty($kelas_records)) {
                foreach ($kelas_records as $row) {
                    if (!empty($row->kelas)) {
                        $kelas_options[] = $row->kelas;
                    }
                }
            }
        }

        $data = array(
            'title' => 'Jadwal UAS',
            'page_title' => 'Jadwal Ujian Akhir Semester',
            'breadcrumb' => array(
                array('label' => 'Jadwal', 'url' => site_url('Halaman_utama')),
                array('label' => 'UAS', 'active' => true)
            ),
            'sidebar_menu' => generate_sidebar_menu(get_user_role()),
            'tahun_aktif' => $tahun_aktif,
            'jadwal_list' => $jadwal_list,
            'kelas_options' => $kelas_options,
            'filter_tanggal' => $filter_tanggal,
            'filter_kelas' => $filter_kelas,
            'per_page' => $per_page,
            'pagination' => $this->pagination->create_links(),
            'total_rows' => $config['total_rows'],
            'bentrok_map' => $bentrok_map,
        );

        $data['content'] = $this->load->view('jadwal_uas/index', $data, true);
        $this->load->view('layouts/main', $data);
    }

    /**
     * Form generate jadwal UAS otomatis + handler
     */
    public function generate_otomatis() {
        $this->assert_not_kaprodi('Jadwal_uas');
        $tahun_aktif = $this->Tahun_akademik_model->get_aktif();
        $id_tahun_akademik = $tahun_aktif ? $tahun_aktif->id : null;
        $kelas_list = $id_tahun_akademik ? $this->Jadwal_kuliah_model->get_kelas_with_mapping($id_tahun_akademik) : array();

        if ($this->input->method() === 'post') {
            $kelas = trim($this->input->post('kelas'));
            $tanggal_uas = $this->input->post('tanggal_uas');
            $jumlah_sesi = (int)$this->input->post('jumlah_sesi');
            $overwrite = $this->input->post('overwrite') ? true : false;

            if (!$id_tahun_akademik) {
                $this->session->set_flashdata('error', 'Tidak ada tahun akademik aktif.');
                redirect('Jadwal_uas/generate_otomatis');
            }

            if (empty($kelas) || empty($tanggal_uas) || !in_array($jumlah_sesi, array(1, 2))) {
                $this->session->set_flashdata('error', 'Lengkapi kelas, tanggal UAS, dan jumlah sesi.');
                redirect('Jadwal_uas/generate_otomatis');
            }

            $jadwal_kuliah = $this->Jadwal_kuliah_model->get_by_kelas($kelas, $id_tahun_akademik);
            if (empty($jadwal_kuliah)) {
                $this->session->set_flashdata('error', 'Tidak ditemukan jadwal kuliah untuk kelas tersebut.');
                redirect('Jadwal_uas/generate_otomatis');
            }

            if ($jumlah_sesi === 2) {
                $mahasiswa = $this->Mahasiswa_model->get_by_kelas($kelas, 'aktif');

                if (empty($mahasiswa)) {
                    $result = $this->generate_jadwal_uas_entries($kelas, $tanggal_uas, $jumlah_sesi, $overwrite, array(), $jadwal_kuliah, $id_tahun_akademik);
                    $this->handle_generation_result($result, 'UAS');
                    redirect('Jadwal_uas');
                }

                $view_data = array(
                    'kelas' => $kelas,
                    'tanggal_uas' => $tanggal_uas,
                    'jumlah_sesi' => $jumlah_sesi,
                    'overwrite' => $overwrite,
                    'jadwal_kuliah' => $jadwal_kuliah,
                    'mahasiswa' => $mahasiswa
                );

                $layout_data = array(
                    'title' => 'Assign Sesi UAS',
                    'page_title' => 'Assign Mahasiswa ke Sesi UAS',
                    'breadcrumb' => array(
                        array('label' => 'Jadwal UAS', 'url' => site_url('Jadwal_uas')),
                        array('label' => 'Generate Otomatis', 'url' => site_url('Jadwal_uas/generate_otomatis')),
                        array('label' => 'Assign Sesi', 'active' => true)
                    ),
                    'sidebar_menu' => generate_sidebar_menu(get_user_role()),
                    'content' => $this->load->view('jadwal_uas/assign_sesi', $view_data, true)
                );

                $this->load->view('layouts/main', $layout_data);
                return;
            }

            $result = $this->generate_jadwal_uas_entries($kelas, $tanggal_uas, $jumlah_sesi, $overwrite, array(), $jadwal_kuliah, $id_tahun_akademik);
            $this->handle_generation_result($result, 'UAS');
            redirect('Jadwal_uas');
        }

        $data = array(
            'title' => 'Generate Jadwal UAS',
            'page_title' => 'Generate Jadwal UAS Otomatis',
            'breadcrumb' => array(
                array('label' => 'Jadwal UAS', 'url' => site_url('Jadwal_uas')),
                array('label' => 'Generate Otomatis', 'active' => true)
            ),
            'sidebar_menu' => generate_sidebar_menu(get_user_role()),
            'tahun_aktif' => $tahun_aktif,
            'kelas_list' => $kelas_list
        );

        $data['content'] = $this->load->view('jadwal_uas/generate_otomatis', $data, true);
        $this->load->view('layouts/main', $data);
    }

    /**
     * Proses assign sesi (jumlah sesi = 2)
     */
    public function process_assign_sesi() {
        $this->assert_not_kaprodi('Jadwal_uas');
        if ($this->input->method() !== 'post') {
            show_404();
        }

        $kelas = $this->input->post('kelas');
        $jumlah_sesi = (int)$this->input->post('jumlah_sesi');
        $tanggal_uas = $this->input->post('tanggal_uas');
        $overwrite = $this->input->post('overwrite') ? true : false;
        $mahasiswa_sesi = $this->input->post('mahasiswa_sesi');

        $tahun_aktif = $this->Tahun_akademik_model->get_aktif();
        $id_tahun_akademik = $tahun_aktif ? $tahun_aktif->id : null;

        if (!$id_tahun_akademik || $jumlah_sesi !== 2 || empty($kelas) || empty($tanggal_uas) || empty($mahasiswa_sesi) || !is_array($mahasiswa_sesi)) {
            $this->session->set_flashdata('error', 'Data assign sesi tidak lengkap.');
            redirect('Jadwal_uas/generate_otomatis');
        }

        $jadwal_kuliah = $this->Jadwal_kuliah_model->get_by_kelas($kelas, $id_tahun_akademik);
        if (empty($jadwal_kuliah)) {
            $this->session->set_flashdata('error', 'Tidak ditemukan jadwal kuliah untuk kelas tersebut.');
            redirect('Jadwal_uas/generate_otomatis');
        }

        $result = $this->generate_jadwal_uas_entries($kelas, $tanggal_uas, $jumlah_sesi, $overwrite, $mahasiswa_sesi, $jadwal_kuliah, $id_tahun_akademik);
        $this->handle_generation_result($result, 'UAS');
        redirect('Jadwal_uas');
    }

    /**
     * Tampilkan feedback generate
     */
    private function handle_generation_result($result, $label) {
        if ($result['success']) {
            $this->session->set_flashdata('success', sprintf('%s jadwal %s berhasil digenerate.', $result['count'], $label));
        } else {
            $this->session->set_flashdata('error', $result['message'] ?: 'Gagal generate jadwal.');
        }
    }

    /**
     * Generate data jadwal UAS sesuai konfigurasi
     */
    private function generate_jadwal_uas_entries($kelas, $tanggal_ref, $jumlah_sesi, $overwrite, $mahasiswa_sesi_map, $jadwal_kuliah, $id_tahun_akademik) {
        $this->db->trans_start();

        if ($overwrite) {
            $this->cleanup_existing_jadwal('jadwal_uas', 'id_jadwal_uas', $kelas, $id_tahun_akademik);
        }

        $mahasiswa = $this->Mahasiswa_model->get_by_kelas($kelas, 'aktif');
        $student_ids = array_map(function($m) { return $m->id; }, $mahasiswa);

        $mahasiswa_per_sesi = $this->prepare_mahasiswa_sesi($student_ids, $jumlah_sesi, $mahasiswa_sesi_map);
        $tendik_pool = $this->Tendik_model->get_all_active();
        $tendik_count = count($tendik_pool);
        $tendik_index = 0;

        $created = 0;

        foreach ($jadwal_kuliah as $jk) {
            $tanggal_actual = $this->get_tanggal_by_hari($tanggal_ref, $jk->hari);
            $session_times = $this->calculate_session_times($jk->jam_mulai, $jk->jam_selesai, $jumlah_sesi);

            for ($sesi = 1; $sesi <= $jumlah_sesi; $sesi++) {
                $time_slot = $session_times[$sesi];

                $data_jadwal = array(
                    'kode_jadwal' => $this->generate_kode('UAS', $tanggal_actual, $time_slot['mulai'], $kelas, $sesi, $jk->id_mapping_dosen_mk),
                    'id_mapping_dosen_mk' => $jk->id_mapping_dosen_mk,
                    'kelas' => $kelas,
                    'sesi' => $sesi,
                    'tanggal' => $tanggal_actual,
                    'jam_mulai' => $time_slot['mulai'],
                    'jam_selesai' => $time_slot['selesai'],
                    'id_ruang_kelas' => $jk->id_ruang_kelas,
                    'id_tendik' => null,
                    'id_tahun_akademik' => $id_tahun_akademik,
                    'keterangan' => 'Generated automatically from jadwal kuliah'
                );

                if ($sesi === 2 && $tendik_count > 0) {
                    $tendik = $tendik_pool[$tendik_index % $tendik_count];
                    $data_jadwal['id_tendik'] = $tendik->id;
                    $tendik_index++;
                }

                $jadwal_id = $this->Jadwal_uas_model->create($data_jadwal);
                if (!$jadwal_id) {
                    $this->db->trans_rollback();
                    return array('success' => false, 'message' => 'Gagal menyimpan jadwal UAS.');
                }

                if ($sesi === 1 && !empty($jk->id_dosen)) {
                    $this->Jadwal_uas_model->set_pengawas_single($jadwal_id, 'dosen', $jk->id_dosen);
                } elseif ($sesi === 2 && $data_jadwal['id_tendik']) {
                    $this->Jadwal_uas_model->set_pengawas_single($jadwal_id, 'tendik', $data_jadwal['id_tendik']);
                }

                $assigned_students = isset($mahasiswa_per_sesi[$sesi]) ? $mahasiswa_per_sesi[$sesi] : array();
                $this->assign_mahasiswa_to_jadwal('jadwal_uas_mahasiswa_sesi', 'id_jadwal_uas', $jadwal_id, $assigned_students, $sesi);

                $created++;
            }
        }

        $this->db->trans_complete();

        if ($this->db->trans_status() === false) {
            return array('success' => false, 'message' => 'Transaksi gagal disimpan.');
        }

        return array('success' => true, 'count' => $created);
    }

    /**
     * Fungsi utilitas bersama (reuse dari Jadwal UTS)
     */
    private function cleanup_existing_jadwal($table, $fk_column, $kelas, $id_tahun_akademik) {
        $this->db->select('id');
        $this->db->from($table);
        $this->db->where('kelas', $kelas);
        $this->db->where('id_tahun_akademik', $id_tahun_akademik);
        $ids = $this->db->get()->result();

        if (empty($ids)) {
            return;
        }

        $id_list = array_map(function($row) { return $row->id; }, $ids);

        if ($table === 'jadwal_uts') {
            $child_tables = array('jadwal_uts_pengawas','jadwal_uts_mahasiswa_sesi','jadwal_uts_kehadiran','jadwal_uts_berita_acara','honor_uts');
        } elseif ($table === 'jadwal_uas') {
            $child_tables = array('jadwal_uas_pengawas','jadwal_uas_mahasiswa_sesi','jadwal_uas_kehadiran','jadwal_uas_berita_acara','honor_uas');
        } else {
            $child_tables = array();
        }

        foreach ($child_tables as $child) {
            $column = $child === 'honor_uts' || $child === 'honor_uas' ? $fk_column : $fk_column;
            $this->db->where_in($column, $id_list)->delete($child);
        }

        $this->db->where_in('id', $id_list)->delete($table);
    }

    private function prepare_mahasiswa_sesi($student_ids, $jumlah_sesi, $input_map) {
        $result = array();
        if (empty($student_ids)) {
            return $result;
        }

        if ($jumlah_sesi === 1) {
            $result[1] = $student_ids;
            return $result;
        }

        $assigned = array();
        foreach ($student_ids as $sid) {
            if (isset($input_map[$sid]) && in_array((int)$input_map[$sid], array(1, 2))) {
                $assigned[(int)$input_map[$sid]][] = $sid;
            }
        }

        $existing = array_merge(isset($assigned[1]) ? $assigned[1] : array(), isset($assigned[2]) ? $assigned[2] : array());
        $missing = array_diff($student_ids, $existing);
        if (!empty($missing)) {
            $half = ceil(count($student_ids) / 2);
            foreach ($missing as $sid) {
                if (count(isset($assigned[1]) ? $assigned[1] : array()) < $half) {
                    $assigned[1][] = $sid;
                } else {
                    $assigned[2][] = $sid;
                }
            }
        }

        $result[1] = isset($assigned[1]) ? $assigned[1] : array();
        $result[2] = isset($assigned[2]) ? $assigned[2] : array();

        return $result;
    }

    private function assign_mahasiswa_to_jadwal($table, $fk_column, $jadwal_id, $student_ids, $sesi) {
        if (empty($student_ids)) {
            return;
        }

        $batch = array();
        foreach ($student_ids as $sid) {
            $batch[] = array(
                $fk_column => $jadwal_id,
                'id_mahasiswa' => $sid,
                'sesi' => $sesi
            );
        }

        if (!empty($batch)) {
            $this->db->insert_batch($table, $batch);
        }
        
        // Buat record kehadiran dengan status "tidak_hadir" untuk semua mahasiswa
        $table_kehadiran = ($table == 'jadwal_uas_mahasiswa_sesi') ? 'jadwal_uas_kehadiran' : 'jadwal_uts_kehadiran';
        $id_jadwal_field = ($table == 'jadwal_uas_mahasiswa_sesi') ? 'id_jadwal_uas' : 'id_jadwal_uts';
        
        $batch_kehadiran = array();
        foreach ($student_ids as $sid) {
            // Cek apakah sudah ada record kehadiran
            $this->db->where($id_jadwal_field, $jadwal_id);
            $this->db->where('id_mahasiswa', $sid);
            $existing = $this->db->get($table_kehadiran)->row();
            
            if (!$existing) {
                $batch_kehadiran[] = array(
                    $id_jadwal_field => $jadwal_id,
                    'id_mahasiswa' => $sid,
                    'status_kehadiran' => 'tidak_hadir',
                    'created_at' => date('Y-m-d H:i:s')
                );
            }
        }
        
        if (!empty($batch_kehadiran)) {
            $this->db->insert_batch($table_kehadiran, $batch_kehadiran);
        }
    }

    private function get_tanggal_by_hari($tanggal_ref, $hari_target) {
        $base = strtotime($tanggal_ref);
        $base_day = date('w', $base);
        $target_key = strtolower($hari_target);
        $target_day = isset($this->hari_map[$target_key]) ? $this->hari_map[$target_key] : $base_day;
        $diff = $target_day - $base_day;
        if ($diff < 0) {
            $diff += 7;
        }

        return date('Y-m-d', strtotime("+{$diff} days", $base));
    }

    private function calculate_session_times($jam_mulai, $jam_selesai, $jumlah_sesi) {
        $start = strtotime($jam_mulai);
        $end = strtotime($jam_selesai);
        $duration = max(0, $end - $start);

        if ($jumlah_sesi === 1 || $duration === 0) {
            $half = $duration > 0 ? floor($duration / 2) : 0;
            $half = $half > 0 ? $half : ($duration > 0 ? $duration : 3600);
            $custom_end = $start + $half;
            return array(
                1 => array(
                    'mulai' => date('H:i:s', $start),
                    'selesai' => date('H:i:s', $custom_end)
                )
            );
        }

        $half = floor($duration / 2);
        $first_end = $start + $half;

        return array(
            1 => array(
                'mulai' => date('H:i:s', $start),
                'selesai' => date('H:i:s', $first_end)
            ),
            2 => array(
                'mulai' => date('H:i:s', $first_end),
                'selesai' => date('H:i:s', $end)
            )
        );
    }

    private function generate_kode($prefix, $tanggal, $jam_mulai, $kelas, $sesi, $mapping_id) {
        return sprintf(
            '%s-AUTO-%s-%s-%s-S%s-%s',
            strtoupper($prefix),
            date('Ymd', strtotime($tanggal)),
            date('His', strtotime($jam_mulai)),
            strtoupper($kelas),
            $sesi,
            $mapping_id
        );
    }

    public function berita_acara($id) {
        $role = $this->session->userdata('role');
        $jadwal = $this->Jadwal_uas_model->get_by_id($id);
        if (!$jadwal) {
            $this->session->set_flashdata('error', 'Jadwal tidak ditemukan.');
            if ($role == 'dosen' || $role == 'tendik') {
                redirect('Jadwal_pengawas/uas');
            } else {
                redirect('Jadwal_uas');
            }
        }

        // Jika user adalah dosen atau tendik, pastikan mereka adalah pengawas untuk jadwal ini
        $role = $this->session->userdata('role');
        if ($role == 'dosen') {
            $id_dosen = $this->session->userdata('ref_id');
            $this->db->where('id_jadwal_uas', $id);
            $this->db->where('id_dosen', $id_dosen);
            $is_pengawas = $this->db->get('jadwal_uas_pengawas')->num_rows() > 0;
            
            if (!$is_pengawas) {
                $this->session->set_flashdata('error', 'Anda tidak memiliki akses ke halaman ini.');
                redirect('Jadwal_pengawas/uas');
            }
        } elseif ($role == 'tendik') {
            $id_tendik = $this->session->userdata('ref_id');
            $this->db->where('id_jadwal_uas', $id);
            $this->db->where('id_tendik', $id_tendik);
            $is_pengawas = $this->db->get('jadwal_uas_pengawas')->num_rows() > 0;
            
            if (!$is_pengawas) {
                $this->session->set_flashdata('error', 'Anda tidak memiliki akses ke halaman ini.');
                redirect('Jadwal_pengawas/uas');
            }
        }

        $mahasiswa = $this->get_mahasiswa_for_uas($id, $jadwal->kelas, $jadwal->id_tahun_akademik);
        $kehadiran_map = $this->get_kehadiran_map_uas($id);
        $berita = $this->db->where('id_jadwal_uas', $id)->get('jadwal_uas_berita_acara')->row();

        if ($this->input->method() === 'post') {
            $this->assert_not_kaprodi('Jadwal_uas');
            $this->form_validation->set_rules('berita_acara', 'Berita Acara', 'required');
            if ($this->form_validation->run() === FALSE) {
                $this->session->set_flashdata('error', validation_errors());
                redirect('Jadwal_uas/berita_acara/' . $id);
            }

            $kehadiran_input = $this->input->post('kehadiran');
            $keterangan_input = $this->input->post('keterangan_kehadiran');
            $berita_text = $this->input->post('berita_acara');
            $catatan_text = $this->input->post('catatan');

            $this->db->trans_start();

            $data_ba = array(
                'berita_acara' => $berita_text,
                'catatan' => $catatan_text,
                'created_by' => get_user_id()
            );

            if ($berita) {
                $this->db->where('id', $berita->id)->update('jadwal_uas_berita_acara', $data_ba);
            } else {
                $data_ba['id_jadwal_uas'] = $id;
                $this->db->insert('jadwal_uas_berita_acara', $data_ba);
            }

            $this->db->where('id_jadwal_uas', $id)->delete('jadwal_uas_kehadiran');

            if (!empty($mahasiswa) && !empty($kehadiran_input)) {
                foreach ($mahasiswa as $mhs) {
                    if (!isset($kehadiran_input[$mhs->id])) {
                        continue;
                    }
                    $status = $kehadiran_input[$mhs->id];
                    $ket = isset($keterangan_input[$mhs->id]) ? $keterangan_input[$mhs->id] : null;
                    $this->db->insert('jadwal_uas_kehadiran', array(
                        'id_jadwal_uas' => $id,
                        'id_mahasiswa' => $mhs->id,
                        'status_kehadiran' => $status,
                        'keterangan' => $ket
                    ));
                }
            }

            $this->db->trans_complete();

            if ($this->db->trans_status()) {
                $this->session->set_flashdata('success', 'Berita acara & kehadiran tersimpan.');
            } else {
                $this->session->set_flashdata('error', 'Gagal menyimpan berita acara.');
            }

            // Redirect sesuai role
            $role = $this->session->userdata('role');
            if ($role == 'dosen' || $role == 'tendik') {
                redirect('Jadwal_pengawas/uas');
            } else {
                redirect('Jadwal_uas');
            }
        }

        $role = $this->session->userdata('role');
        $back_url = ($role == 'dosen' || $role == 'tendik') ? site_url('Jadwal_pengawas/uas') : site_url('Jadwal_uas');
        
        $view_data = array(
            'title' => 'Berita Acara UAS',
            'page_title' => 'Berita Acara & Kehadiran UAS',
            'breadcrumb' => array(
                array('label' => 'Jadwal UAS', 'url' => $back_url),
                array('label' => 'Berita Acara', 'active' => true)
            ),
            'sidebar_menu' => generate_sidebar_menu(get_user_role()),
            'jadwal' => $jadwal,
            'mahasiswa' => $mahasiswa,
            'kehadiran' => $kehadiran_map,
            'berita_acara' => $berita,
            'jenis_label' => 'UAS',
            'action_url' => site_url('Jadwal_uas/berita_acara/' . $id),
            'back_url' => $back_url
        );

        $view_data['content'] = $this->load->view('jadwal/berita_acara_form', $view_data, true);
        $this->load->view('layouts/main', $view_data);
    }

    public function ganti_pengawas($id) {
        $this->assert_not_kaprodi('Jadwal_uas');
        $jadwal = $this->Jadwal_uas_model->get_by_id($id);
        if (!$jadwal) {
            $this->session->set_flashdata('error', 'Jadwal tidak ditemukan.');
            redirect('Jadwal_uas');
        }

        $is_sesi_1 = ((int)$jadwal->sesi === 1);

        if ($this->input->method() === 'post') {
            $pengawas_type = $this->input->post('pengawas_type');
            $pengawas_id = $this->input->post('pengawas_id');

            if (!$pengawas_type || !$pengawas_id) {
                $this->session->set_flashdata('error', 'Pilih tipe pengawas dan pengawas yang akan ditugaskan.');
            } else {
                if ($this->Jadwal_uas_model->set_pengawas_single($id, $pengawas_type, $pengawas_id)) {
                    $tipe_text = ($pengawas_type == 'dosen') ? 'Dosen' : 'Tendik';
                    $this->session->set_flashdata('success', 'Pengawas ' . $tipe_text . ' berhasil diperbarui.');
                    redirect('Jadwal_uas');
                } else {
                    $this->session->set_flashdata('error', 'Gagal memperbarui pengawas.');
                }
            }
        }

        // Ambil semua dosen dan tendik untuk opsi pilihan
        $dosen_list = $this->Dosen_model->get_all_active();
        $tendik_list = $this->Tendik_model->get_all_active();

        // Tentukan pengawas saat ini
        $pengawas_sekarang_type = '';
        $pengawas_sekarang_id = null;
        
        if (!empty($jadwal->pengawas)) {
            $current = $jadwal->pengawas[0];
            if (isset($current->id_dosen_full) && !empty($current->id_dosen_full)) {
                $pengawas_sekarang_type = 'dosen';
                $pengawas_sekarang_id = $current->id_dosen_full;
            } elseif (isset($current->id_tendik_full) && !empty($current->id_tendik_full)) {
                $pengawas_sekarang_type = 'tendik';
                $pengawas_sekarang_id = $current->id_tendik_full;
            }
        }

        $view_data = array(
            'title' => 'Ganti Pengawas UAS',
            'page_title' => 'Ganti Pengawas Jadwal UAS',
            'breadcrumb' => array(
                array('label' => 'Jadwal UAS', 'url' => site_url('Jadwal_uas')),
                array('label' => 'Ganti Pengawas', 'active' => true)
            ),
            'sidebar_menu' => generate_sidebar_menu(get_user_role()),
            'jadwal' => $jadwal,
            'is_sesi_1' => $is_sesi_1,
            'dosen_list' => $dosen_list,
            'tendik_list' => $tendik_list,
            'action_url' => site_url('Jadwal_uas/ganti_pengawas/' . $id),
            'jenis' => 'UAS',
            'selected_id' => $pengawas_sekarang_id,
            'pengawas_sekarang_type' => $pengawas_sekarang_type,
            'pengawas_sekarang_id' => $pengawas_sekarang_id
        );

        $view_data['content'] = $this->load->view('jadwal/ganti_pengawas', $view_data, true);
        $this->load->view('layouts/main', $view_data);
    }

    private function get_mahasiswa_for_uas($id_jadwal, $kelas, $id_tahun_akademik) {
        $this->db->select('m.id, m.nim, m.nama_mahasiswa');
        $this->db->from('jadwal_uas_mahasiswa_sesi jms');
        $this->db->join('mahasiswa m', 'm.id = jms.id_mahasiswa', 'left');
        $this->db->where('jms.id_jadwal_uas', $id_jadwal);
        $this->db->order_by('m.nama_mahasiswa', 'ASC');
        $result = $this->db->get()->result();

        if (empty($result)) {
            $result = $this->Mahasiswa_model->get_by_kelas($kelas, 'aktif');
        }

        return $result;
    }

    private function get_kehadiran_map_uas($id_jadwal) {
        $records = $this->db->where('id_jadwal_uas', $id_jadwal)->get('jadwal_uas_kehadiran')->result();
        $map = array();
        foreach ($records as $row) {
            $map[$row->id_mahasiswa] = $row;
        }
        return $map;
    }

    /**
     * Cetak Daftar Hadir UAS
     */
    public function cetak_daftar_hadir($id) {
        $this->check_role(array('sekprodi', 'admin', 'dosen', 'tendik', 'kaprodi'));
        
        $jadwal = $this->Jadwal_uas_model->get_by_id($id);
        if (!$jadwal) {
            $this->session->set_flashdata('error', 'Jadwal tidak ditemukan.');
            redirect('Jadwal_uas');
        }
        
        // Ambil mahasiswa berdasarkan sesi (filter per sesi, bukan per kelas)
        $tahun_aktif = $this->Tahun_akademik_model->get_aktif();
        $id_tahun_akademik = $tahun_aktif ? $tahun_aktif->id : null;
        $mahasiswa_list = $this->get_mahasiswa_for_uas($id, $jadwal->kelas, $id_tahun_akademik);
        
        // Ambil data kehadiran
        $kehadiran_map = $this->get_kehadiran_map_uas($id);
        
        // Ambil pengawas
        $pengawas = $this->Jadwal_uas_model->get_pengawas($id);
        
        // Load library PDF
        $this->load->library('Pdf');
        $this->load->helper('app');
        
        $data = array(
            'jadwal' => $jadwal,
            'mahasiswa_list' => $mahasiswa_list,
            'kehadiran_map' => $kehadiran_map,
            'pengawas' => $pengawas,
            'jenis_ujian' => 'UAS'
        );
        
        // Generate HTML untuk PDF
        $html = $this->load->view('jadwal_uas/cetak_daftar_hadir', $data, true);
        
        // Load HTML ke PDF library
        $this->pdf->load_html($html);
        $this->pdf->set_paper('A4', 'portrait');
        
        // Generate filename (termasuk kelas dan sesi)
        $filename = 'Daftar_Hadir_UAS_' . $jadwal->kode_mk . '_' . $jadwal->kelas . '_S' . $jadwal->sesi . '_' . date('Ymd', strtotime($jadwal->tanggal)) . '.pdf';
        
        // Stream PDF
        $this->pdf->stream($filename);
    }

    /**
     * Reset QR Code untuk jadwal UAS
     */
    public function reset_qrcode($id) {
        $this->check_role(array('sekprodi', 'admin'));
        
        $result = $this->Jadwal_uas_model->reset_qr_code($id);
        
        if ($result) {
            $this->session->set_flashdata('success', 'QR Code berhasil di-reset.');
        } else {
            $this->session->set_flashdata('error', 'Gagal reset QR Code.');
        }
        
        redirect('Jadwal_uas');
    }

    /**
     * Form edit jadwal UAS
     */
    public function edit($id) {
        $this->check_role(array('sekprodi', 'admin'));
        
        $jadwal = $this->Jadwal_uas_model->get_by_id($id);
        if (!$jadwal) {
            $this->session->set_flashdata('error', 'Jadwal tidak ditemukan.');
            redirect('Jadwal_uas');
        }

        // Load model ruang kelas
        $this->load->model('Ruang_kelas_model');
        $ruang_list = $this->Ruang_kelas_model->get_all_active();

        if ($this->input->method() === 'post') {
            $this->form_validation->set_rules('tanggal', 'Tanggal', 'required');
            $this->form_validation->set_rules('jam_mulai', 'Jam Mulai', 'required');
            $this->form_validation->set_rules('jam_selesai', 'Jam Selesai', 'required');
            $this->form_validation->set_rules('id_ruang_kelas', 'Ruang Kelas', 'required');

            if ($this->form_validation->run() === FALSE) {
                $this->session->set_flashdata('error', validation_errors());
            } else {
                $data_update = array(
                    'tanggal' => $this->input->post('tanggal'),
                    'jam_mulai' => $this->input->post('jam_mulai'),
                    'jam_selesai' => $this->input->post('jam_selesai'),
                    'id_ruang_kelas' => $this->input->post('id_ruang_kelas'),
                    'keterangan' => $this->input->post('keterangan')
                );

                if ($this->Jadwal_uas_model->update($id, $data_update)) {
                    $this->session->set_flashdata('success', 'Jadwal UAS berhasil diperbarui.');
                    redirect('Jadwal_uas');
                } else {
                    $this->session->set_flashdata('error', 'Gagal memperbarui jadwal UAS.');
                }
            }
        }

        $data = array(
            'title' => 'Edit Jadwal UAS',
            'page_title' => 'Edit Jadwal Ujian Akhir Semester',
            'breadcrumb' => array(
                array('label' => 'Jadwal UAS', 'url' => site_url('Jadwal_uas')),
                array('label' => 'Edit', 'active' => true)
            ),
            'sidebar_menu' => generate_sidebar_menu(get_user_role()),
            'jadwal' => $jadwal,
            'ruang_list' => $ruang_list
        );

        $data['content'] = $this->load->view('jadwal_uas/edit', $data, true);
        $this->load->view('layouts/main', $data);
    }

    /**
     * Hapus jadwal UAS
     */
    public function delete($id) {
        $this->check_role(array('sekprodi', 'admin'));
        
        $jadwal = $this->Jadwal_uas_model->get_by_id($id);
        
        if (!$jadwal) {
            $this->session->set_flashdata('error', 'Jadwal tidak ditemukan.');
            redirect('Jadwal_uas');
        }

        // Hapus data terkait terlebih dahulu
        $this->db->where('id_jadwal_uas', $id)->delete('jadwal_uas_pengawas');
        $this->db->where('id_jadwal_uas', $id)->delete('jadwal_uas_mahasiswa_sesi');
        $this->db->where('id_jadwal_uas', $id)->delete('jadwal_uas_kehadiran');
        $this->db->where('id_jadwal_uas', $id)->delete('jadwal_uas_berita_acara');
        $this->db->where('id_jadwal_uas', $id)->delete('honor_uas');

        // Hapus jadwal utama
        if ($this->Jadwal_uas_model->delete($id)) {
            $this->session->set_flashdata('success', 'Jadwal UAS berhasil dihapus.');
        } else {
            $this->session->set_flashdata('error', 'Gagal menghapus jadwal UAS.');
        }
        
        redirect('Jadwal_uas');
    }

}


