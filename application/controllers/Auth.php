<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Auth Controller
 * Menangani login dan logout
 */
class Auth extends CI_Controller {

    const MAX_LOGIN_ATTEMPTS = 5;
    const LOGIN_LOCK_SECONDS = 600; // 10 menit
    const ALLOWED_MOODS = '😀,😄,🙂,😐,😴,🤒,😡,🥳,😰,🤔';

    public function __construct() {
        parent::__construct();
        $this->load->model('User_model');
        $this->load->helper('url');
        $this->load->library('form_validation');
    }

    /**
     * Halaman login
     */
    public function index() {
        // Jika sudah login, redirect ke halaman utama
        if ($this->session->userdata('user_id')) {
            redirect('Halaman_utama');
        }
        
        $this->login();
    }

    /**
     * Proses login
     */
    public function login() {
        // Jika sudah login, redirect ke halaman utama
        if ($this->session->userdata('user_id')) {
            redirect('Halaman_utama');
        }

        // Cek apakah ada parameter logout success
        if ($this->input->get('logout') == 'success') {
            $this->session->set_flashdata('success', 'Anda telah berhasil logout.');
        }

        // Pesan jika sesi invalid (dari MY_Controller)
        if ($this->input->get('session') === 'invalid') {
            $this->session->set_flashdata('error', 'Sesi tidak valid. Silakan login ulang.');
        }

        if ($this->input->method() === 'post') {
            $mood_raw = (string)$this->input->post('mood', true);
            $mood = $this->sanitize_mood($mood_raw);

            // Anti brute-force: cek lock sebelum validasi DB
            if ($this->is_login_locked()) {
                $remaining_minutes = $this->get_login_lock_remaining_minutes();
                $this->session->set_flashdata('error', 'Terlalu banyak percobaan gagal. Silakan coba lagi dalam ' . $remaining_minutes . ' menit.');

                // Catat event locked untuk dipantau admin
                if ($this->db->table_exists('user_activity_log')) {
                    $username_locked = $this->normalize_username((string)$this->input->post('username', true));
                    $payload = array(
                        'user_id' => null,
                        'activity_type' => 'login_locked',
                        'description' => $this->build_login_activity_description('Login diblokir sementara', $username_locked, $mood, (int)$this->session->userdata('login_attempts')),
                        'ip_address' => $this->input->ip_address(),
                        'user_agent' => substr($this->input->user_agent(), 0, 500)
                    );

                    $prev_debug = $this->db->db_debug;
                    $this->db->db_debug = false;
                    $ok = $this->db->insert('user_activity_log', $payload);
                    $this->db->db_debug = $prev_debug;
                    if (!$ok) {
                        $db_err = $this->db->error();
                        log_message('error', 'Gagal insert user_activity_log login_locked: ' . ($db_err['message'] ?? 'unknown') . ' payload=' . json_encode($payload));
                    }
                }

                $this->load->view('auth/login');
                return;
            }

            // Validasi form
            $this->form_validation->set_rules('username', 'Username', 'required|trim');
            $this->form_validation->set_rules('password', 'Password', 'required|trim');
            // Mood opsional: jika diisi harus termasuk whitelist emoji
            $this->form_validation->set_rules('mood', 'Mood', 'trim|callback_valid_mood');

            if ($this->form_validation->run() == FALSE) {
                $this->load->view('auth/login');
            } else {
                // Normalize ulang setelah validation
                $mood = $this->sanitize_mood((string)$this->input->post('mood', true));
                $username = $this->normalize_username((string)$this->input->post('username', true));
                // Jangan lakukan XSS clean pada password (bisa mengubah karakter dan membuat verifikasi hash gagal).
                $password = (string)$this->input->post('password', false);

                // Cek login
                $user = $this->User_model->check_login($username, $password);

                if ($user) {
                    // Session fixation protection: ganti session ID setelah login sukses
                    $this->session->sess_regenerate(TRUE);

                    // Reset brute-force counter
                    $this->reset_login_attempts();

                    // Set session data
                    $session_data = array(
                        'user_id' => $user->id,
                        'username' => $user->username,
                        'role' => $user->nama_role,
                        'role_id' => $user->role_id,
                        'ref_id' => $user->ref_id
                    );

                    // Ambil data tambahan berdasarkan role
                    if ($user->nama_role === 'dosen' && $user->ref_id) {
                        $dosen = $this->User_model->get_dosen_by_ref($user->ref_id);
                        if ($dosen) {
                            $session_data['nama'] = $dosen->nama_dosen;
                            $session_data['nidn'] = $dosen->nidn;
                        }
                    } elseif ($user->nama_role === 'mahasiswa' && $user->ref_id) {
                        $mahasiswa = $this->User_model->get_mahasiswa_by_ref($user->ref_id);
                        if ($mahasiswa) {
                            $session_data['nama'] = $mahasiswa->nama_mahasiswa;
                            $session_data['nim'] = $mahasiswa->nim;
                        }
                    } elseif ($user->nama_role === 'tendik' && $user->ref_id) {
                        $tendik = $this->User_model->get_tendik_by_ref($user->ref_id);
                        if ($tendik) {
                            $session_data['nama'] = $tendik->nama_tendik;
                            $session_data['nipy'] = $tendik->nipy;
                        }
                    } else {
                        // Untuk admin, kaprodi, sekprodi
                        $session_data['nama'] = $user->username;
                    }

                    $this->session->set_userdata($session_data);

                    // Sekprodi: baseline badge — antrean lama tidak dianggap "belum dilihat"
                    if (strtolower((string) $user->nama_role) === 'sekprodi') {
                        $this->load->helper('sekprodi_badge');
                        if (function_exists('sekprodi_badge_seed_baseline')) {
                            sekprodi_badge_seed_baseline(true);
                        }
                    }
                    
                    // Update last_login
                    $this->User_model->update_last_login($user->id);

                    // Log aktivitas login
                    if ($this->db->table_exists('user_activity_log')) {
                        $this->load->model('User_activity_log_model');
                        $login_log_id = $this->User_activity_log_model->create(array(
                            'user_id' => $user->id,
                            'activity_type' => 'login',
                            'description' => $this->build_login_activity_description('Login berhasil', $user->username, $mood, 0),
                            'ip_address' => $this->input->ip_address(),
                            'user_agent' => substr($this->input->user_agent(), 0, 500)
                        ));
                        if ($login_log_id) {
                            $this->session->set_userdata('activity_login_log_id', $login_log_id);
                        }
                    }

                    // Set flashdata success untuk login berhasil
                    $this->session->set_flashdata('success', 'Login berhasil! Selamat datang, ' . ($session_data['nama'] ?? $session_data['username']) . '.');

                    // Redirect ke halaman utama untuk semua role
                    redirect('Halaman_utama');
                } else {
                    $this->register_failed_login_attempt($username, $mood);
                    $this->load->view('auth/login');
                }
            }
        } else {
            $this->load->view('auth/login');
        }
    }

    protected function normalize_username($username) {
        $username = trim($username);
        // Hindari whitespace aneh agar konsisten (tanpa mengubah case)
        $username = preg_replace('/\s+/', ' ', $username);
        return $username;
    }

    /**
     * Anti brute-force: cek apakah login sedang diblokir sementara (berbasis session).
     */
    protected function is_login_locked() {
        $lock_until = (int)$this->session->userdata('login_lock_until');
        return ($lock_until > time());
    }

    protected function get_login_lock_remaining_minutes() {
        $lock_until = (int)$this->session->userdata('login_lock_until');
        $remaining = $lock_until - time();
        if ($remaining <= 0) {
            return 0;
        }
        return (int)ceil($remaining / 60);
    }

    protected function reset_login_attempts() {
        $this->session->unset_userdata('login_attempts');
        $this->session->unset_userdata('login_lock_until');
    }

    protected function register_failed_login_attempt($username, $mood) {
        $attempts = (int)$this->session->userdata('login_attempts');
        $attempts++;
        $this->session->set_userdata('login_attempts', $attempts);

        $ip = $this->input->ip_address();
        // CI3 default log levels: error/debug/info. "warning" akan memicu Undefined array key di core/Log.php.
        log_message('error', 'Login gagal. username=' . $username . ' ip=' . $ip . ' attempts=' . $attempts);

        // Bonus: simpan ke tabel aktivitas (agar bisa dipantau admin), tanpa mengubah struktur DB.
        // Pakai user_id=0 supaya tetap bisa masuk jika kolom user_id NOT NULL.
        if ($this->db->table_exists('user_activity_log')) {
            $this->load->model('User_activity_log_model');

            // Jika username ada di tabel users, pakai ID tsb agar lolos FK.
            // Jika tidak ada, set NULL (butuh skema user_activity_log.user_id mengizinkan NULL).
            $user_id = null;
            $user = $this->User_model->get_user_by_username($username);
            if ($user && isset($user->id)) {
                $user_id = (int)$user->id;
            }

            $payload = array(
                'user_id' => $user_id,
                'activity_type' => 'login_failed',
                'description' => $this->build_login_activity_description('Login gagal', $username, $mood, $attempts),
                'ip_address' => $ip,
                'user_agent' => substr($this->input->user_agent(), 0, 500)
            );

            // Jangan biarkan DB error mematikan request (khusus untuk login_failed).
            $prev_debug = $this->db->db_debug;
            $this->db->db_debug = false;
            $ok = $this->db->insert('user_activity_log', $payload);
            $this->db->db_debug = $prev_debug;

            if (!$ok) {
                $db_err = $this->db->error();
                log_message('error', 'Gagal insert user_activity_log login_failed: ' . ($db_err['message'] ?? 'unknown') . ' payload=' . json_encode($payload));
            }
        }

        if ($attempts >= self::MAX_LOGIN_ATTEMPTS) {
            $lock_until = time() + self::LOGIN_LOCK_SECONDS;
            $this->session->set_userdata('login_lock_until', $lock_until);
            $this->session->set_flashdata('error', 'Terlalu banyak percobaan gagal. Login diblokir selama 10 menit.');
        } else {
            $remaining = self::MAX_LOGIN_ATTEMPTS - $attempts;
            $this->session->set_flashdata('error', 'Password salah. Percobaan gagal: ' . $attempts . ' dari ' . self::MAX_LOGIN_ATTEMPTS . '.');
        }
    }

    protected function sanitize_mood($mood) {
        $mood = trim($mood);
        if ($mood === '') {
            return '';
        }
        $allowed = array_map('trim', explode(',', self::ALLOWED_MOODS));
        return in_array($mood, $allowed, true) ? $mood : '';
    }

    public function valid_mood($mood) {
        $raw = trim((string)$mood);
        if ($raw === '') {
            return true; // opsional
        }
        $normalized = $this->sanitize_mood($raw);
        if ($normalized === '') {
            $this->form_validation->set_message('valid_mood', 'Mood tidak valid.');
            return false;
        }
        return true;
    }

    protected function build_login_activity_description($label, $username, $mood, $attempts) {
        $parts = array();
        $parts[] = $label;
        if ($username !== '') {
            $parts[] = 'username=' . $username;
        }
        if ($mood !== '') {
            $parts[] = 'mood=' . $mood;
        }
        if ((int)$attempts > 0) {
            $parts[] = 'attempts=' . (int)$attempts . '/' . self::MAX_LOGIN_ATTEMPTS;
        }
        return implode(' | ', $parts);
    }

    /**
     * Logout
     */
    public function logout() {
        // Update last_logout dan log aktivitas sebelum destroy session
        $user_id = $this->session->userdata('user_id');
        if ($user_id) {
            $this->load->model('User_model');
            $this->User_model->update_last_logout($user_id);
            // Log aktivitas logout
            if ($this->db->table_exists('user_activity_log')) {
                $this->load->model('User_activity_log_model');
                $this->User_activity_log_model->create(array(
                    'user_id' => $user_id,
                    'activity_type' => 'logout',
                    'description' => 'Logout',
                    'ip_address' => $this->input->ip_address(),
                    'user_agent' => substr($this->input->user_agent(), 0, 500)
                ));
            }
        }

        $this->session->unset_userdata('activity_login_log_id');
        
        // Destroy session
        $this->session->sess_destroy();
        
        // Redirect ke halaman login dengan parameter logout=success
        redirect('Auth/login?logout=success');
    }
}

