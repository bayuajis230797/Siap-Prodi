<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Aktivitas User Controller
 * Menampilkan log aktivitas user (login, logout) - untuk Admin
 */
class Aktivitas_user extends MY_Controller {

    public function __construct() {
        parent::__construct();
        $this->check_role('admin');
        $this->load->model('User_model');
        if ($this->db->table_exists('user_activity_log')) {
            $this->load->model('User_activity_log_model');
        }
        if ($this->db->table_exists('user_activity_detail')) {
            $this->load->model('User_activity_detail_model');
        }
    }

    /**
     * Daftar aktivitas user
     */
    public function index() {
        $filters = array(
            'user_id' => $this->input->get('user_id'),
            'activity_type' => $this->input->get('activity_type'),
            'date_from' => $this->input->get('date_from'),
            'date_to' => $this->input->get('date_to'),
            'role' => $this->input->get('role'),
            'search' => $this->input->get('search')
        );

        $per_page = $this->input->get('per_page') ? (int)$this->input->get('per_page') : 25;
        $per_page = in_array($per_page, [10, 25, 50, 100]) ? $per_page : 25;
        $page = ($this->uri->segment(3)) ? (int)$this->uri->segment(3) : 1;
        $offset = ($page - 1) * $per_page;

        $activity_list = array();
        $total_rows = 0;
        $roles = array();
        $users_for_filter = array();
        $table_exists = $this->db->table_exists('user_activity_log');
        $detail_table_exists = $this->db->table_exists('user_activity_detail');

        if ($table_exists && $detail_table_exists) {
            $total_rows = $this->User_activity_log_model->count_all($filters);
            $activity_list = $this->User_activity_log_model->get_all($filters, $per_page, $offset);
            $roles = $this->User_activity_log_model->get_roles_for_filter();
            $users_for_filter = $this->User_model->get_all(500, 0);
        }

        // Pagination
        $this->load->library('pagination');
        $query_string = http_build_query(array_filter(array_merge($filters, array('per_page' => $per_page))));
        $config['base_url'] = site_url('Aktivitas_user/index');
        $config['total_rows'] = $total_rows;
        $config['per_page'] = $per_page;
        $config['uri_segment'] = 3;
        $config['suffix'] = $query_string ? '?' . $query_string : '';
        $config['first_url'] = site_url('Aktivitas_user/index') . ($query_string ? '?' . $query_string : '');
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

        $data = array(
            'page_title' => 'Aktivitas User',
            'breadcrumb' => array(
                array('label' => 'Halaman Utama', 'url' => site_url('Halaman_utama')),
                array('label' => 'Aktivitas User', 'active' => true)
            ),
            'sidebar_menu' => generate_sidebar_menu('admin'),
            'activity_list' => $activity_list,
            'filters' => $filters,
            'roles' => $roles,
            'users_for_filter' => $users_for_filter,
            'table_exists' => $table_exists,
            'detail_table_exists' => $detail_table_exists,
            'pagination' => $this->pagination->create_links(),
            'per_page' => $per_page,
            'total_rows' => $total_rows,
            'page' => $page
        );

        $data['content'] = $this->load->view('admin/aktivitas_user/index', $data, true);
        $this->load->view('layouts/main', $data);
    }

    /**
     * Detail aktivitas user dalam satu sesi login.
     */
    public function detail($login_log_id) {
        if (!$this->db->table_exists('user_activity_log') || !$this->db->table_exists('user_activity_detail')) {
            $this->session->set_flashdata('error', 'Tabel aktivitas detail belum tersedia.');
            redirect('Aktivitas_user');
        }

        $login_log = $this->User_activity_log_model->get_by_id($login_log_id);
        if (!$login_log || $login_log->activity_type !== 'login') {
            $this->session->set_flashdata('error', 'Data sesi login tidak ditemukan.');
            redirect('Aktivitas_user');
        }

        $activity_detail_list = $this->User_activity_detail_model->get_by_login_log_id($login_log_id);

        $data = array(
            'page_title' => 'Detail Aktivitas User',
            'breadcrumb' => array(
                array('label' => 'Halaman Utama', 'url' => site_url('Halaman_utama')),
                array('label' => 'Aktivitas User', 'url' => site_url('Aktivitas_user')),
                array('label' => 'Detail Aktivitas', 'active' => true)
            ),
            'sidebar_menu' => generate_sidebar_menu('admin'),
            'login_log' => $login_log,
            'activity_detail_list' => $activity_detail_list
        );

        $data['content'] = $this->load->view('admin/aktivitas_user/detail', $data, true);
        $this->load->view('layouts/main', $data);
    }

    /**
     * Format (hapus) log aktivitas — admin only.
     * POST: mode=all|filtered, date_from, date_to, activity_type, user_id, confirm=FORMAT
     */
    public function format() {
        if ($this->input->method() !== 'post') {
            show_404();
        }
        if (!$this->db->table_exists('user_activity_log')) {
            $this->session->set_flashdata('error', 'Tabel aktivitas tidak tersedia.');
            redirect('Aktivitas_user');
        }

        $confirm = strtoupper(trim((string) $this->input->post('confirm')));
        if ($confirm !== 'FORMAT') {
            $this->session->set_flashdata('error', 'Ketik FORMAT untuk mengonfirmasi penghapusan.');
            redirect('Aktivitas_user');
        }

        $mode = $this->input->post('mode') === 'all' ? 'all' : 'filtered';
        $filters = array();
        if ($mode === 'filtered') {
            $filters = array(
                'user_id' => $this->input->post('user_id'),
                'activity_type' => $this->input->post('activity_type'),
                'date_from' => $this->input->post('date_from'),
                'date_to' => $this->input->post('date_to'),
            );
            $filters = array_filter($filters, function ($v) {
                return $v !== null && $v !== '';
            });
            if (empty($filters)) {
                $this->session->set_flashdata('error', 'Untuk format terfilter, isi minimal satu filter (tanggal / tipe / user).');
                redirect('Aktivitas_user');
            }
        }

        $current_login_log_id = (int) $this->session->userdata('activity_login_log_id');
        $deleted = $this->User_activity_log_model->format_clear($filters);

        // Jika log login sesi ini ikut terhapus, buat ulang agar pencatatan detail tidak gagal FK
        if ($current_login_log_id > 0) {
            $still = $this->db->select('id')->from('user_activity_log')->where('id', $current_login_log_id)->limit(1)->get()->row();
            if (!$still) {
                $this->session->unset_userdata('activity_login_log_id');
                $this->ensure_activity_login_log((int) $this->session->userdata('user_id'));
            }
        }

        $this->session->set_flashdata('success', 'Aktivitas berhasil diformat. ' . number_format($deleted) . ' baris log dihapus.');
        redirect('Aktivitas_user');
    }

    /**
     * Ekspor CSV aktivitas (filter GET sama seperti index).
     */
    public function export() {
        if (!$this->db->table_exists('user_activity_log')) {
            $this->session->set_flashdata('error', 'Tabel aktivitas tidak tersedia.');
            redirect('Aktivitas_user');
        }

        $filters = array(
            'user_id' => $this->input->get('user_id'),
            'activity_type' => $this->input->get('activity_type'),
            'date_from' => $this->input->get('date_from'),
            'date_to' => $this->input->get('date_to'),
            'role' => $this->input->get('role'),
            'search' => $this->input->get('search'),
        );

        $rows = $this->User_activity_log_model->get_all($filters, 10000, 0);
        $filename = 'aktivitas_user_' . date('Ymd_His') . '.csv';

        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        $out = fopen('php://output', 'w');
        fprintf($out, chr(0xEF) . chr(0xBB) . chr(0xBF));
        fputcsv($out, array('Waktu', 'User', 'Role', 'Tipe', 'Deskripsi', 'IP', 'Detail Count'));
        foreach ($rows as $r) {
            fputcsv($out, array(
                $r->created_at,
                $r->nama_user ?: $r->username,
                $r->nama_role,
                $r->activity_type,
                $r->description,
                $r->ip_address,
                isset($r->detail_count) ? $r->detail_count : 0,
            ));
        }
        fclose($out);
        exit;
    }
}
