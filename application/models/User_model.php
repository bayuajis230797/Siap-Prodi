<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * User Model
 * Model untuk mengelola data user dan autentikasi
 */
class User_model extends CI_Model {

    protected $users_password_max_length = null;

    public function __construct() {
        parent::__construct();
        $this->load->database();
    }

    /**
     * Cek login user
     * @param string $username Username
     * @param string $password Password (plain text)
     * @return object|false User object jika berhasil, false jika gagal
     */
    public function check_login($username, $password) {
        $user = $this->get_user_by_username($username);
        if (!$user) {
            return false;
        }

        $stored = (string)($user->password ?? '');
        if ($stored === '') {
            return false;
        }

        // Legacy support: MD5 (32 hex) -> auto-upgrade to password_hash() after successful login
        if ($this->is_legacy_md5_hash($stored)) {
            if (hash_equals($stored, md5((string)$password))) {
                $this->upgrade_password_hash($user->id, (string)$password);
                return $user;
            }
            return false;
        }

        // Modern: password_hash()
        if (password_verify((string)$password, $stored)) {
            if (password_needs_rehash($stored, PASSWORD_DEFAULT)) {
                $this->upgrade_password_hash($user->id, (string)$password);
            }
            return $user;
        }

        return false;
    }

    /**
     * Deteksi password legacy MD5: 32 karakter hex.
     */
    protected function is_legacy_md5_hash($value) {
        if (!is_string($value) || strlen($value) !== 32) {
            return false;
        }
        return (bool)preg_match('/^[a-f0-9]{32}$/i', $value);
    }

    /**
     * Upgrade password user ke password_hash().
     * Tidak mengubah struktur DB (memakai kolom password yang sama).
     */
    protected function upgrade_password_hash($user_id, $plain_password) {
        $hash = password_hash($plain_password, PASSWORD_DEFAULT);
        if (!$hash) {
            return false;
        }
        if (!$this->can_store_password_hash($hash)) {
            log_message('error', 'Tidak bisa upgrade password ke password_hash(): kolom users.password terlalu pendek. user_id=' . (int)$user_id . ' hash_len=' . strlen($hash) . ' max_len=' . (int)$this->get_users_password_max_length());
            return false;
        }
        $this->db->where('id', (int)$user_id);
        $this->db->update('users', array('password' => $hash));
        return $this->db->affected_rows() > 0;
    }

    /**
     * Ambil max_length kolom users.password (cache per request).
     * Return null jika metadata tidak tersedia (anggap cukup).
     */
    protected function get_users_password_max_length() {
        if ($this->users_password_max_length !== null) {
            return $this->users_password_max_length;
        }

        $this->users_password_max_length = null;
        try {
            $fields = $this->db->field_data('users');
            if (is_array($fields)) {
                foreach ($fields as $f) {
                    if (isset($f->name) && $f->name === 'password') {
                        $len = isset($f->max_length) ? (int)$f->max_length : null;
                        $this->users_password_max_length = $len ?: null;
                        break;
                    }
                }
            }
        } catch (Exception $e) {
            $this->users_password_max_length = null;
        }

        return $this->users_password_max_length;
    }

    protected function can_store_password_hash($hash) {
        $max = $this->get_users_password_max_length();
        if ($max === null) {
            return true;
        }
        return strlen((string)$hash) <= $max;
    }

    /**
     * Get user by ID
     * @param int $user_id User ID
     * @return object|false User object jika ditemukan, false jika tidak
     */
    public function get_user_by_id($user_id) {
        $this->db->select('u.*, r.nama_role');
        $this->db->from('users u');
        $this->db->join('roles r', 'r.id = u.role_id', 'left');
        $this->db->where('u.id', $user_id);
        
        $query = $this->db->get();
        return $query->row();
    }

    /**
     * Get user by username
     * @param string $username Username
     * @return object|false User object jika ditemukan, false jika tidak
     */
    public function get_user_by_username($username) {
        $this->db->select('u.*, r.nama_role');
        $this->db->from('users u');
        $this->db->join('roles r', 'r.id = u.role_id', 'left');
        $this->db->where('u.username', $username);
        
        $query = $this->db->get();
        return $query->row();
    }

    /**
     * Get dosen by ref_id
     * @param int $ref_id Reference ID (dosen id)
     * @return object|false Dosen object jika ditemukan, false jika tidak
     */
    public function get_dosen_by_ref($ref_id) {
        $this->db->where('id', $ref_id);
        $query = $this->db->get('dosen');
        return $query->row();
    }

    /**
     * Get mahasiswa by ref_id
     * @param int $ref_id Reference ID (mahasiswa id)
     * @return object|false Mahasiswa object jika ditemukan, false jika tidak
     */
    public function get_mahasiswa_by_ref($ref_id) {
        $this->db->where('id', $ref_id);
        $query = $this->db->get('mahasiswa');
        return $query->row();
    }

    /**
     * Get tendik by ref_id
     * @param int $ref_id Reference ID (tendik id)
     * @return object|false Tendik object jika ditemukan, false jika tidak
     */
    public function get_tendik_by_ref($ref_id) {
        $this->db->where('id', $ref_id);
        $query = $this->db->get('tendik');
        return $query->row();
    }

    /**
     * Create new user
     * @param array $data User data (username, password, role_id, ref_id)
     * @return int|false User ID jika berhasil, false jika gagal
     */
    public function create($data) {
        // Untuk password baru: selalu gunakan password_hash().
        // Tetap kompatibel jika ada kode lama yang mengirim MD5 (32 hex) atau sudah hashed ($2y$...).
        if (isset($data['password'])) {
            $pwd = (string)$data['password'];
            if ($pwd !== '' && strpos($pwd, '$') !== 0 && !$this->is_legacy_md5_hash($pwd)) {
                $hash = password_hash($pwd, PASSWORD_DEFAULT);
                if ($hash && $this->can_store_password_hash($hash)) {
                    $data['password'] = $hash;
                } else {
                    log_message('error', 'Tidak bisa menyimpan password_hash() saat create user: kolom users.password terlalu pendek. username=' . ($data['username'] ?? '-') . ' hash_len=' . (int)strlen((string)$hash) . ' max_len=' . (int)$this->get_users_password_max_length());
                    // Fallback: simpan MD5 agar sistem tetap bisa jalan tanpa ubah DB.
                    $data['password'] = md5($pwd);
                }
            }
        }
        
        $this->db->insert('users', $data);
        return $this->db->insert_id();
    }

    /**
     * Update user
     * @param int $user_id User ID
     * @param array $data Data to update
     * @return bool True jika berhasil, false jika gagal
     */
    public function update($user_id, $data) {
        // Untuk password baru: selalu gunakan password_hash().
        // Tetap kompatibel jika ada kode lama yang mengirim MD5 (32 hex) atau sudah hashed ($2y$...).
        if (isset($data['password'])) {
            $pwd = (string)$data['password'];
            if ($pwd !== '' && strpos($pwd, '$') !== 0 && !$this->is_legacy_md5_hash($pwd)) {
                $hash = password_hash($pwd, PASSWORD_DEFAULT);
                if ($hash && $this->can_store_password_hash($hash)) {
                    $data['password'] = $hash;
                } else {
                    log_message('error', 'Tidak bisa menyimpan password_hash() saat update user: kolom users.password terlalu pendek. user_id=' . (int)$user_id . ' hash_len=' . (int)strlen((string)$hash) . ' max_len=' . (int)$this->get_users_password_max_length());
                    // Fallback: simpan MD5 agar sistem tetap bisa jalan tanpa ubah DB.
                    $data['password'] = md5($pwd);
                }
            }
        }
        
        $this->db->where('id', $user_id);
        $this->db->update('users', $data);
        return $this->db->affected_rows() > 0;
    }

    /**
     * Reset password user ke default (sama dengan username).
     * @param int $user_id User ID
     * @return bool
     */
    public function reset_password_to_username($user_id) {
        $user = $this->get_user_by_id($user_id);
        if (!$user || empty($user->username)) {
            return false;
        }
        return $this->update($user_id, array('password' => $user->username));
    }

    /**
     * Delete user
     * @param int $user_id User ID
     * @return bool True jika berhasil, false jika gagal
     */
    public function delete($user_id) {
        $this->db->where('id', $user_id);
        $this->db->delete('users');
        return $this->db->affected_rows() > 0;
    }

    /**
     * Update last login time
     * @param int $user_id User ID
     * @return bool True jika berhasil, false jika gagal
     */
    public function update_last_login($user_id) {
        // Set timezone ke WIB (Asia/Jakarta)
        date_default_timezone_set('Asia/Jakarta');
        $this->db->where('id', $user_id);
        $this->db->update('users', array('last_login' => date('Y-m-d H:i:s')));
        return $this->db->affected_rows() > 0;
    }

    /**
     * Update last logout time
     * @param int $user_id User ID
     * @return bool True jika berhasil, false jika gagal
     */
    public function update_last_logout($user_id) {
        // Set timezone ke WIB (Asia/Jakarta)
        date_default_timezone_set('Asia/Jakarta');
        $this->db->where('id', $user_id);
        $this->db->update('users', array('last_logout' => date('Y-m-d H:i:s')));
        return $this->db->affected_rows() > 0;
    }

    /**
     * Get active session user IDs (users yang sedang login)
     * @return array Array of user IDs yang sedang login
     */
    public function get_active_session_user_ids() {
        $active_user_ids = array();
        
        // Baca semua session files
        // CodeIgniter session files biasanya di application/sessions/ atau system/sessions/
        $possible_paths = array(
            APPPATH . 'sessions/',
            BASEPATH . '../application/sessions/',
            sys_get_temp_dir() . '/'
        );
        
        $session_path = $this->config->item('sess_save_path');
        if (!empty($session_path) && is_dir($session_path)) {
            array_unshift($possible_paths, rtrim($session_path, '/\\') . '/');
        }
        
        $session_expiration = $this->config->item('sess_expiration') ?: 1800;
        $current_time = time();
        
        foreach ($possible_paths as $path) {
            if (is_dir($path)) {
                $session_files = glob($path . 'ci_session*');
                if ($session_files) {
                    foreach ($session_files as $file) {
                        if (!is_file($file)) continue;
                        
                        // Cek waktu modifikasi file (jika terlalu lama, skip)
                        $file_time = filemtime($file);
                        if (($current_time - $file_time) > $session_expiration) {
                            continue; // Session sudah expired
                        }
                        
                        $session_data = @file_get_contents($file);
                        if ($session_data) {
                            // Parse session data untuk mendapatkan user_id
                            // Format CodeIgniter: user_id";s:1:"1" atau user_id";i:1
                            // Coba beberapa pola
                            if (preg_match('/user_id["\'];s:\d+:["\'](\d+)["\']/', $session_data, $matches) ||
                                preg_match('/user_id["\'];i:(\d+)/', $session_data, $matches) ||
                                preg_match('/"user_id":(\d+)/', $session_data, $matches)) {
                                $user_id = (int)$matches[1];
                                if ($user_id > 0) {
                                    $active_user_ids[] = $user_id;
                                }
                            }
                        }
                    }
                    break; // Jika sudah menemukan session files, tidak perlu cek path lain
                }
            }
        }
        
        // Fallback: jika tidak ada session files yang ditemukan, gunakan logika last_login > last_logout
        if (empty($active_user_ids)) {
            $this->db->select('id, last_login');
            $this->db->from('users');
            $this->db->where('(last_login > last_logout OR last_logout IS NULL)', null, false);
            $this->db->where('last_login IS NOT NULL', null, false);
            $query = $this->db->get();
            $result = $query->result();
            foreach ($result as $row) {
                // Cek apakah last_login masih dalam waktu session expiration
                if ($row->last_login) {
                    $last_login_time = strtotime($row->last_login);
                    if (($current_time - $last_login_time) <= $session_expiration) {
                        $active_user_ids[] = $row->id;
                    }
                }
            }
        }
        
        return array_unique($active_user_ids);
    }

    /**
     * Get all users
     * @param int|null $limit Limit records
     * @param int|null $offset Offset records
     * @return array Array of user objects
     */
    public function get_all($limit = null, $offset = null) {
        $this->db->select("
            u.*,
            r.nama_role,
            CASE
                WHEN r.nama_role = 'dosen' THEN COALESCE(d.nama_dosen, u.username)
                WHEN r.nama_role = 'mahasiswa' THEN COALESCE(m.nama_mahasiswa, u.username)
                WHEN r.nama_role = 'tendik' THEN COALESCE(t.nama_tendik, u.username)
                ELSE u.username
            END AS nama_user
        ");
        $this->db->from('users u');
        $this->db->join('roles r', 'r.id = u.role_id', 'left');
        $this->db->join('dosen d', "d.id = u.ref_id AND r.nama_role = 'dosen'", 'left', false);
        $this->db->join('mahasiswa m', "m.id = u.ref_id AND r.nama_role = 'mahasiswa'", 'left', false);
        $this->db->join('tendik t', "t.id = u.ref_id AND r.nama_role = 'tendik'", 'left', false);
        $this->db->order_by('u.id', 'DESC');
        
        if ($limit !== null) {
            $this->db->limit($limit, $offset);
        }
        
        $query = $this->db->get();
        return $query->result();
    }

    /**
     * Count all users
     * @return int Total users
     */
    public function count_all() {
        $this->db->from('users');
        return $this->db->count_all_results();
    }

    /**
     * Get reference data berdasarkan role dan ref_id
     * @param string $role Role name (dosen, mahasiswa, tendik, admin, sekprodi, kaprodi)
     * @param int $ref_id Reference ID
     * @return object|false|null Reference data object jika ditemukan, false jika tidak, null jika ref_id kosong/tidak relevan
     */
    public function get_ref_data($role, $ref_id) {
        if (!$ref_id) {
            return null;
        }

        switch (strtolower($role)) {
            case 'dosen':
                return $this->get_dosen_by_ref($ref_id);
            
            case 'mahasiswa':
                return $this->get_mahasiswa_by_ref($ref_id);
            
            case 'tendik':
                return $this->get_tendik_by_ref($ref_id);
            
            case 'admin':
            case 'sekprodi':
            case 'kaprodi':
                // Untuk role admin, sekprodi, kaprodi biasanya tidak ada ref_id
                // Tapi jika ada, bisa diambil dari tabel yang sesuai
                // Untuk sekarang return null karena biasanya tidak ada ref_id
                return null;
            
            default:
                return null;
        }
    }

    /**
     * Get all roles
     * @return array Array of role objects
     */
    public function get_all_roles() {
        $this->db->order_by('nama_role', 'ASC');
        $query = $this->db->get('roles');
        return $query->result();
    }

    /**
     * Get user by ref_id and role
     * @param int $ref_id Reference ID (dosen/mahasiswa/tendik id)
     * @param string $role Role name (dosen, mahasiswa, tendik)
     * @return object|false|null User object jika ditemukan, false jika tidak, null jika ref_id kosong
     */
    public function get_by_ref_id($ref_id, $role) {
        if (!$ref_id) {
            return null;
        }
        
        $this->db->select('u.*, r.nama_role');
        $this->db->from('users u');
        $this->db->join('roles r', 'r.id = u.role_id', 'left');
        $this->db->where('u.ref_id', $ref_id);
        $this->db->where('LOWER(r.nama_role)', strtolower($role));
        $query = $this->db->get();
        return $query->row();
    }
}



