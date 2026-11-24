<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Pintu tunggal upload berkas ke Google Drive ADMIN (satu akun institusi).
 *
 * Semua role mengunggah ke Drive yang dihubungkan admin.
 * Admin dapat Putuskan / Hubungkan ulang jika kapasitas penuh.
 */
class Upload_to_drive {

    /** @var CI_Controller */
    protected $CI;

    public function __construct() {
        $this->CI =& get_instance();
        $this->CI->load->library('Google_drive_oauth');
        $this->CI->load->library('Google_drive_api');
        $this->CI->load->library('upload');
        $this->CI->load->helper('google_drive');
    }

    /**
     * @return array{ok:bool,message?:string,file_id?:string,webViewLink?:string,file_name?:string,mimeType?:string,drive_owner_user_id?:int}
     */
    public function handle_upload($field_name, array $options = array()) {
        // Selalu pakai Drive admin (abaikan owner_user_id dari pemanggil)
        $owner_id = $this->find_connected_admin_user_id();
        $folder_parts = $this->normalize_folder_parts(
            !empty($options['folder_parts']) ? $options['folder_parts'] : array('SIAP', 'Lainnya')
        );
        $share_emails = !empty($options['share_emails']) ? (array) $options['share_emails'] : array();
        $share_anyone = array_key_exists('share_anyone', $options) ? (bool) $options['share_anyone'] : true;
        $allowed = isset($options['allowed_types']) ? $options['allowed_types'] : '*';

        if (!$owner_id) {
            return array('ok' => false, 'message' => 'Google Drive institusi belum terhubung. Minta admin menghubungkan akun di menu Profil.');
        }

        if (!$this->CI->google_drive_oauth->is_configured()) {
            return array('ok' => false, 'message' => 'Integrasi Google Drive belum dikonfigurasi admin (Client ID/Secret).');
        }

        if (empty($_FILES[$field_name]['name'])) {
            return array('ok' => false, 'message' => 'File belum dipilih.');
        }

        $tmp_dir = FCPATH . 'uploads' . DIRECTORY_SEPARATOR . 'tmp_drive' . DIRECTORY_SEPARATOR;
        if (!is_dir($tmp_dir)) {
            @mkdir($tmp_dir, 0755, true);
        }

        // max_size 0 = tanpa batas di CI Upload (batas praktis = PHP upload_max_filesize)
        $config = array(
            'upload_path' => $tmp_dir,
            'allowed_types' => $allowed,
            'max_size' => 0,
            'encrypt_name' => TRUE
        );
        $this->CI->upload->initialize($config);

        if (!$this->CI->upload->do_upload($field_name)) {
            return array('ok' => false, 'message' => strip_tags($this->CI->upload->display_errors('', '')));
        }

        $up = $this->CI->upload->data();
        $local = $up['full_path'];
        $original = $up['orig_name'];
        $mime = $up['file_type'];

        // Nama file di Drive: mudah dicari manual
        $drive_filename = $this->build_drive_filename($original, isset($options['file_label']) ? $options['file_label'] : null);

        $folder_id = $this->CI->google_drive_api->ensure_folder_path($owner_id, $folder_parts);
        if (!$folder_id) {
            @unlink($local);
            return array('ok' => false, 'message' => 'Gagal menyiapkan folder di Google Drive admin.');
        }

        $drive_file = $this->CI->google_drive_api->upload_file($owner_id, $local, $drive_filename, $folder_id, $mime);
        @unlink($local);

        if (!$drive_file) {
            return array('ok' => false, 'message' => 'Gagal mengunggah file ke Google Drive admin. Cek kapasitas Drive atau Hubungkan ulang akun.');
        }

        // Link publik agar seluruh role bisa membuka tanpa koneksi Drive pribadi
        $this->CI->google_drive_api->share_anyone_with_link($owner_id, $drive_file['id']);

        foreach ($share_emails as $email) {
            $email = trim((string) $email);
            if ($email !== '' && filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $this->CI->google_drive_api->share_with_email($owner_id, $drive_file['id'], $email);
            }
        }

        $link = !empty($drive_file['webViewLink'])
            ? $drive_file['webViewLink']
            : ('https://drive.google.com/file/d/' . $drive_file['id'] . '/view');

        return array(
            'ok' => true,
            'file_id' => $drive_file['id'],
            'webViewLink' => normalize_google_drive_link($link),
            'file_name' => $original,
            'drive_file_name' => $drive_filename,
            'mimeType' => isset($drive_file['mimeType']) ? $drive_file['mimeType'] : $mime,
            'drive_owner_user_id' => $owner_id
        );
    }

    /**
     * Upload opsional: jika tidak ada file, return skipped.
     */
    public function handle_optional_upload($field_name, array $options = array()) {
        if (empty($_FILES[$field_name]['name'])) {
            return array('ok' => true, 'skipped' => true, 'webViewLink' => null, 'file_name' => null);
        }
        return $this->handle_upload($field_name, $options);
    }

    public function get_drive_email($user_id) {
        $this->CI->load->model('User_google_drive_model');
        $row = $this->CI->User_google_drive_model->get_by_user($user_id);
        return ($row && empty($row->revoked_at) && !empty($row->google_email)) ? $row->google_email : null;
    }

    public function find_user_id_by_role_ref($role_name, $ref_id) {
        $row = $this->CI->db->select('u.id')
            ->from('users u')
            ->join('roles r', 'r.id = u.role_id')
            ->where('r.nama_role', $role_name)
            ->where('u.ref_id', (int) $ref_id)
            ->limit(1)
            ->get()->row();
        return $row ? (int) $row->id : null;
    }

    public function get_drive_email_by_role_ref($role_name, $ref_id) {
        // Legacy no-op helper: share utama via anyone-with-link
        $uid = $this->find_user_id_by_role_ref($role_name, $ref_id);
        return $uid ? $this->get_drive_email($uid) : null;
    }

    /**
     * User admin yang Drive-nya terhubung (prioritas terbaru).
     */
    public function find_connected_admin_user_id() {
        $sql = "SELECT u.id
                FROM users u
                JOIN roles r ON r.id = u.role_id
                JOIN user_google_drive g ON g.user_id = u.id
                WHERE r.nama_role = 'admin'
                  AND g.revoked_at IS NULL
                  AND g.refresh_token_enc IS NOT NULL
                  AND g.refresh_token_enc <> ''
                ORDER BY g.connected_at DESC
                LIMIT 1";
        $row = $this->CI->db->query($sql)->row();
        return $row ? (int) $row->id : null;
    }

    /**
     * Alias lama — sekarang mengarah ke Drive admin.
     */
    public function find_connected_sekprodi_user_id() {
        return $this->find_connected_admin_user_id();
    }

    /**
     * Default options folder akademik (owner selalu admin).
     */
    public function default_options($folder_parts, $share_emails = array(), $owner_user_id = null) {
        return array(
            'folder_parts' => $this->normalize_folder_parts($folder_parts),
            'share_emails' => array_values(array_filter((array) $share_emails)),
            'share_anyone' => true,
            'allowed_types' => '*',
            'max_size_kb' => 0
        );
    }

    /**
     * Rapikan path folder: SIAP / Modul / Sub / ...
     */
    public function normalize_folder_parts(array $parts) {
        $clean = array();
        foreach ($parts as $p) {
            $p = $this->sanitize_folder_name($p);
            if ($p !== '') {
                $clean[] = $p;
            }
        }
        if (empty($clean)) {
            $clean = array('SIAP', 'Lainnya');
        }
        if (strtoupper($clean[0]) !== 'SIAP') {
            array_unshift($clean, 'SIAP');
        } else {
            $clean[0] = 'SIAP';
        }
        return $clean;
    }

    public function sanitize_folder_name($name) {
        $name = trim((string) $name);
        $name = preg_replace('/[\\\\\/\:\*\?\"\<\>\|\r\n\t]+/u', '-', $name);
        $name = preg_replace('/\s+/u', ' ', $name);
        $name = trim($name, " .-_");
        if ($name === '') {
            return '';
        }
        if (function_exists('mb_substr')) {
            return mb_substr($name, 0, 80);
        }
        return substr($name, 0, 80);
    }

    protected function build_drive_filename($original, $label = null) {
        $username = (string) $this->CI->session->userdata('username');
        $role = (string) $this->CI->session->userdata('role');
        $ext = pathinfo($original, PATHINFO_EXTENSION);
        $base = pathinfo($original, PATHINFO_FILENAME);
        $base = $this->sanitize_folder_name($base);
        if ($base === '') {
            $base = 'berkas';
        }
        $prefix = date('Ymd_His');
        $who = $this->sanitize_folder_name(($role ? $role . '_' : '') . ($username ?: 'user'));
        $extra = $label ? ('_' . $this->sanitize_folder_name($label)) : '';
        $name = $prefix . '_' . $who . $extra . '_' . $base;
        if ($ext) {
            $name .= '.' . strtolower($ext);
        }
        return $name;
    }
}
