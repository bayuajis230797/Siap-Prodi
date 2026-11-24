<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * MY_Controller
 * Base Controller dengan autentikasi otomatis dan role checking
 */
class MY_Controller extends CI_Controller {

    public function __construct() {
        parent::__construct();
        
        // Set timezone ke WIB (Waktu Indonesia Barat)
        date_default_timezone_set('Asia/Jakarta');
        
        // Load helper
        $this->load->helper('auth');
        $this->load->helper('menu');
        $this->load->helper('notifikasi');
        
        // Cek apakah sudah login (kecuali untuk Auth controller)
        $controller = $this->router->class;
        if ($controller !== 'Auth' && $controller !== 'Welcome') {
            if (!$this->session->userdata('user_id')) {
                $this->session->set_flashdata('error', 'Anda harus login terlebih dahulu.');
                redirect('Auth/login');
            }

            // Pastikan user pada session benar-benar ada di database (mencegah sesi dipalsukan)
            if (!$this->ensure_session_user_valid()) {
                // Jangan set_flashdata setelah sess_destroy (tidak akan tersimpan).
                $this->session->sess_destroy();
                redirect('Auth/login?session=invalid');
            }

            $this->terapkan_guard_mode_lihat($controller);
            $this->prepare_global_tahun_akademik_context();
            $this->prepare_global_notification_context();
            $this->prepare_global_chat_context();
            $this->ack_sekprodi_menu_badges($controller);

            $this->log_user_activity_detail();
        }
    }

    /**
     * Sekprodi: saat membuka halaman antrean, badge angka menu di-ack (hilang sampai ada item baru).
     */
    protected function ack_sekprodi_menu_badges($controller) {
        $role = strtolower((string) $this->session->userdata('role'));
        if ($role !== 'sekprodi') {
            return;
        }
        if (!function_exists('sekprodi_badge_ack_from_controller')) {
            $this->load->helper('sekprodi_badge');
        }
        // Pastikan baseline ada (sesi lama sebelum fitur badge)
        if (function_exists('sekprodi_badge_seed_baseline')) {
            sekprodi_badge_seed_baseline(false);
        }
        sekprodi_badge_ack_from_controller($controller, $this->router->method);
    }

    /**
     * Mode lihat tahun akademik = HANYA BACA: tolak permintaan yang mengubah data
     * (aturan di library Mode_lihat). Tidak berefek bila sesi tidak sedang dalam mode lihat.
     */
    protected function terapkan_guard_mode_lihat($controller) {
        if (!$this->db->table_exists('tahun_akademik')) {
            return;
        }
        $this->load->model('Tahun_akademik_model');
        $lihat = $this->Tahun_akademik_model->get_tahun_lihat();
        if (!$lihat) {
            return;
        }
        $this->load->library('Mode_lihat');
        $alasan = Mode_lihat::evaluasi($this->input->method(), $controller, $this->router->method);
        if ($alasan === null) {
            return;
        }

        $pesan = Mode_lihat::pesan($lihat);
        if ($this->input->is_ajax_request()) {
            $this->output->set_status_header(403)
                ->set_content_type('application/json')
                ->set_output(json_encode(array('success' => false, 'status' => 'error', 'message' => $pesan)))
                ->_display();
            exit;
        }
        $this->session->set_flashdata('error', $pesan);
        $ref = (string) $this->input->server('HTTP_REFERER');
        redirect(($ref !== '' && strpos($ref, base_url()) === 0) ? $ref : 'Halaman_utama');
    }

    /**
     * Siapkan konteks tahun akademik untuk layout (pilihan di navbar + banner mode lihat).
     */
    protected function prepare_global_tahun_akademik_context() {
        $vars = array('global_ta_list' => array(), 'global_ta_resmi' => null, 'global_ta_lihat' => null);
        if ($this->db->table_exists('tahun_akademik')) {
            $this->load->model('Tahun_akademik_model');
            $vars['global_ta_list'] = $this->Tahun_akademik_model->get_all();
            $vars['global_ta_resmi'] = $this->Tahun_akademik_model->get_aktif_resmi();
            $vars['global_ta_lihat'] = $this->Tahun_akademik_model->get_tahun_lihat();
        }
        $this->load->vars($vars);
    }

    /**
     * Siapkan notifikasi in-app agar tersedia di layout global.
     */
    protected function prepare_global_notification_context() {
        $user_id = (int) $this->session->userdata('user_id');
        $role = (string) $this->session->userdata('role');
        if ($user_id <= 0 || $role === '') {
            $this->load->vars(array(
                'global_notifikasi_unread_count' => 0,
                'global_notifikasi_items' => array()
            ));
            return;
        }

        $this->load->model('Notifikasi_model');
        $items = $this->Notifikasi_model->get_by_user($user_id, $role, 5, 0);
        $unread_count = (int) $this->Notifikasi_model->count_unread($user_id, $role);

        $this->load->vars(array(
            'global_notifikasi_unread_count' => $unread_count,
            'global_notifikasi_items' => $items
        ));
    }

    /**
     * Siapkan konteks chat global (badge unread & pop-up prioritas).
     */
    protected function prepare_global_chat_context() {
        $user_id = (int) $this->session->userdata('user_id');
        $role = (string) $this->session->userdata('role');

        if ($user_id <= 0 || !$this->db->table_exists('chat_messages')) {
            $this->load->vars(array(
                'global_chat_unread_count' => 0,
                'global_chat_priority_messages' => array(),
            ));
            return;
        }

        $this->load->model('Chat_model');
        $this->load->helper('chat');

        $unread = (int) $this->Chat_model->count_unread($user_id);
        $priority = ($role === 'mahasiswa')
            ? $this->Chat_model->get_unread_priority_messages($user_id, 5)
            : array();

        $this->load->vars(array(
            'global_chat_unread_count' => $unread,
            'global_chat_priority_messages' => $priority,
        ));
    }

    /**
     * Cek apakah user memiliki role yang diizinkan
     * @param string|array $allowed_roles Role atau array of roles yang diizinkan
     * @param string $redirect_url URL untuk redirect jika tidak memiliki akses
     */
    protected function check_role($allowed_roles, $redirect_url = 'Profile') {
        if (!$this->session->userdata('user_id')) {
            $this->session->set_flashdata('error', 'Anda harus login terlebih dahulu.');
            redirect('Auth/login');
        }

        // Validasi kembali ke database untuk menghindari role/session dipalsukan
        if (!$this->ensure_session_user_valid()) {
            $this->session->sess_destroy();
            redirect('Auth/login?session=invalid');
        }

        $user_role = $this->session->userdata('role');
        
        if (is_array($allowed_roles)) {
            if (!in_array($user_role, $allowed_roles)) {
                $this->session->set_flashdata('error', 'Anda tidak memiliki akses ke halaman ini.');
                redirect($redirect_url);
            }
        } else {
            if ($user_role !== $allowed_roles) {
                $this->session->set_flashdata('error', 'Anda tidak memiliki akses ke halaman ini.');
                redirect($redirect_url);
            }
        }
    }

    /**
     * Apakah user saat ini berperan sebagai Kaprodi (akses baca saja untuk modul tertentu).
     */
    protected function is_kaprodi() {
        return $this->session->userdata('role') === 'kaprodi';
    }

    /**
     * Blokir Kaprodi dari aksi yang mengubah data; redirect dengan pesan.
     *
     * @param string $redirect_url Default Halaman_utama
     */
    protected function assert_not_kaprodi($redirect_url = 'Halaman_utama') {
        if ($this->is_kaprodi()) {
            $this->session->set_flashdata('error', 'Akun Kaprodi hanya dapat melihat data (tidak dapat mengubah).');
            redirect($redirect_url);
        }
    }

    /**
     * Untuk endpoint AJAX/JSON: tolak Kaprodi tanpa redirect HTML.
     */
    protected function json_deny_kaprodi() {
        if ($this->is_kaprodi()) {
            while (ob_get_level()) {
                ob_end_clean();
            }
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode(array('success' => false, 'message' => 'Akun Kaprodi hanya dapat melihat data.'));
            exit;
        }
    }

    /**
     * Validasi bahwa user_id di session benar-benar ada di database.
     * Jika ada, sync role/username/ref_id dari database ke session.
     */
    protected function ensure_session_user_valid() {
        $user_id = $this->session->userdata('user_id');
        if (!$user_id) {
            return false;
        }

        $this->load->model('User_model');
        $user = $this->User_model->get_user_by_id($user_id);
        if (!$user) {
            return false;
        }

        // Sync data minimal dari DB agar tidak bisa dipalsukan via session
        $this->session->set_userdata(array(
            'role' => $user->nama_role,
            'role_id' => $user->role_id,
            'username' => $user->username,
            'ref_id' => $user->ref_id
        ));

        return true;
    }

    /**
     * Simpan detail aktivitas user selama sesi login.
     * Jika login_log_id di sesi sudah terhapus (mis. setelah Format Aktivitas), buat ulang agar FK aman.
     */
    protected function log_user_activity_detail() {
        if (!$this->db->table_exists('user_activity_detail') || !$this->db->table_exists('user_activity_log')) {
            return;
        }

        $user_id = $this->session->userdata('user_id');
        if (!$user_id) {
            return;
        }

        $login_log_id = (int) $this->session->userdata('activity_login_log_id');
        if ($login_log_id > 0) {
            $exists = $this->db->select('id')->from('user_activity_log')->where('id', $login_log_id)->limit(1)->get()->row();
            if (!$exists) {
                $login_log_id = 0;
                $this->session->unset_userdata('activity_login_log_id');
            }
        }

        if ($login_log_id <= 0) {
            $login_log_id = $this->ensure_activity_login_log($user_id);
            if ($login_log_id <= 0) {
                return;
            }
        }

        $controller = $this->router->class;
        $method = $this->router->method;
        $request_method = strtoupper($this->input->method(TRUE));
        $url = current_url();
        $query_string = isset($_SERVER['QUERY_STRING']) && $_SERVER['QUERY_STRING'] !== '' ? '?' . $_SERVER['QUERY_STRING'] : '';

        $this->load->model('User_activity_detail_model');
        $this->User_activity_detail_model->create(array(
            'login_log_id' => $login_log_id,
            'user_id' => $user_id,
            'controller' => $controller,
            'method' => $method,
            'url' => substr($url . $query_string, 0, 255),
            'activity_label' => $this->build_activity_label($controller, $method, $request_method),
            'request_method' => $request_method,
            'ip_address' => $this->input->ip_address()
        ));
    }

    /**
     * Pastikan sesi punya baris login di user_activity_log (dipakai setelah format/hapus log).
     */
    protected function ensure_activity_login_log($user_id) {
        $user_id = (int) $user_id;
        if ($user_id <= 0 || !$this->db->table_exists('user_activity_log')) {
            return 0;
        }
        $this->load->model('User_activity_log_model');
        $login_log_id = (int) $this->User_activity_log_model->create(array(
            'user_id' => $user_id,
            'activity_type' => 'login',
            'description' => 'Sesi dilanjutkan (log login dibuat ulang)',
            'ip_address' => $this->input->ip_address(),
            'user_agent' => substr((string) $this->input->user_agent(), 0, 500),
        ));
        if ($login_log_id > 0) {
            $this->session->set_userdata('activity_login_log_id', $login_log_id);
        }
        return $login_log_id;
    }

    /**
     * Generate label aktivitas yang lebih mudah dibaca admin.
     */
    protected function build_activity_label($controller, $method, $request_method) {
        $controller_label = trim(str_replace('_', ' ', $controller));
        $method_label = trim(str_replace('_', ' ', $method));

        if ($method === 'index') {
            return ($request_method === 'POST' ? 'Memproses' : 'Melihat') . ' halaman ' . $controller_label;
        }

        if ($request_method === 'POST') {
            return 'Menjalankan aksi ' . $method_label . ' pada ' . $controller_label;
        }

        return 'Mengakses ' . $controller_label . ' / ' . $method_label;
    }
}



