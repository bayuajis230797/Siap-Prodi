<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Google Drive OAuth (tanpa SDK eksternal).
 * Mengelola authorize URL, tukar code, refresh token, dan enkripsi token.
 */
class Google_drive_oauth {

    const AUTH_URL = 'https://accounts.google.com/o/oauth2/v2/auth';
    const TOKEN_URL = 'https://oauth2.googleapis.com/token';
    const USERINFO_URL = 'https://www.googleapis.com/oauth2/v2/userinfo';
    const SCOPES = 'https://www.googleapis.com/auth/drive.file https://www.googleapis.com/auth/userinfo.email';

    /** @var CI_Controller */
    protected $CI;

    public function __construct() {
        $this->CI =& get_instance();
        $this->CI->load->model('Google_oauth_setting_model');
        $this->CI->load->model('User_google_drive_model');
    }

    public function get_settings() {
        return $this->CI->Google_oauth_setting_model->get_settings();
    }

    public function is_configured() {
        return $this->CI->Google_oauth_setting_model->is_configured();
    }

    public function get_client_secret_plain($settings = null) {
        if ($settings === null) {
            $settings = $this->get_settings();
        }
        if (!$settings || empty($settings->client_secret_enc)) {
            return '';
        }
        $plain = $this->decrypt($settings->client_secret_enc);
        return $plain !== false ? $plain : '';
    }

    /**
     * Redirect URI efektif = host yang sedang dipakai (localhost ATAU hosting).
     * Harus terdaftar identik di Google Cloud Console → Authorized redirect URIs.
     */
    public function effective_redirect_uri() {
        return site_url('Profile/oauth_callback');
    }

    public function build_auth_url($user_id, $state_extra = array()) {
        $settings = $this->get_settings();
        if (!$this->is_configured()) {
            return false;
        }

        $redirect_uri = $this->effective_redirect_uri();
        // Simpan agar callback/token exchange memakai URI yang sama persis
        $this->CI->session->set_userdata('gdrive_oauth_redirect_uri', $redirect_uri);

        $state = $this->encode_state(array_merge(array(
            'uid' => (int) $user_id,
            'ts' => time(),
            'nonce' => bin2hex(random_bytes(8))
        ), $state_extra));

        $this->CI->session->set_userdata('gdrive_oauth_state', $state);

        $params = array(
            'client_id' => $settings->client_id,
            'redirect_uri' => $redirect_uri,
            'response_type' => 'code',
            'scope' => self::SCOPES,
            'access_type' => 'offline',
            'prompt' => 'consent',
            'include_granted_scopes' => 'true',
            'state' => $state
        );

        return self::AUTH_URL . '?' . http_build_query($params);
    }

    public function handle_callback($code, $state) {
        $expected = $this->CI->session->userdata('gdrive_oauth_state');
        if (!$expected || !hash_equals((string) $expected, (string) $state)) {
            return array('ok' => false, 'message' => 'State OAuth tidak valid. Silakan coba Hubungkan lagi.');
        }

        $payload = $this->decode_state($state);
        if (!$payload || empty($payload['uid'])) {
            return array('ok' => false, 'message' => 'State OAuth rusak.');
        }

        $user_id = (int) $payload['uid'];
        $session_uid = (int) $this->CI->session->userdata('user_id');
        if ($session_uid !== $user_id) {
            return array('ok' => false, 'message' => 'Sesi user tidak cocok dengan OAuth state.');
        }

        $token = $this->exchange_code($code);
        if (!$token || empty($token['access_token'])) {
            return array('ok' => false, 'message' => 'Gagal menukar kode OAuth: ' . (isset($token['error_description']) ? $token['error_description'] : 'unknown'));
        }

        $email = $this->fetch_email($token['access_token']);
        $expires_at = null;
        if (!empty($token['expires_in'])) {
            $expires_at = date('Y-m-d H:i:s', time() + (int) $token['expires_in'] - 60);
        }

        $existing = $this->CI->User_google_drive_model->get_by_user($user_id);
        $data = array(
            'google_email' => $email ?: null,
            'access_token_enc' => $this->encrypt($token['access_token']),
            'token_expires_at' => $expires_at,
            'scopes' => isset($token['scope']) ? $token['scope'] : self::SCOPES,
            'connected_at' => date('Y-m-d H:i:s'),
            'revoked_at' => null
        );

        if (!empty($token['refresh_token'])) {
            $data['refresh_token_enc'] = $this->encrypt($token['refresh_token']);
        } elseif ($existing && !empty($existing->refresh_token_enc)) {
            // Google kadang tidak kirim refresh_token ulang; pertahankan yang lama.
            $data['refresh_token_enc'] = $existing->refresh_token_enc;
        } else {
            return array('ok' => false, 'message' => 'Google tidak mengirim refresh token. Cabut akses aplikasi di akun Google lalu Hubungkan ulang.');
        }

        $this->CI->User_google_drive_model->upsert($user_id, $data);
        $this->CI->session->unset_userdata('gdrive_oauth_state');

        return array('ok' => true, 'message' => 'Google Drive terhubung' . ($email ? ' (' . $email . ')' : '') . '.', 'email' => $email);
    }

    public function disconnect($user_id) {
        return $this->CI->User_google_drive_model->revoke($user_id);
    }

    /**
     * Ambil access token valid (auto-refresh jika perlu).
     * @return string|false
     */
    public function get_valid_access_token($user_id) {
        $row = $this->CI->User_google_drive_model->get_by_user($user_id);
        if (!$row || !empty($row->revoked_at) || empty($row->refresh_token_enc)) {
            return false;
        }

        $access = $this->decrypt($row->access_token_enc);
        $expires_ok = !empty($row->token_expires_at) && strtotime($row->token_expires_at) > time();
        if ($access && $expires_ok) {
            return $access;
        }

        $refresh = $this->decrypt($row->refresh_token_enc);
        if (!$refresh) {
            return false;
        }

        $token = $this->refresh_access_token($refresh);
        if (!$token || empty($token['access_token'])) {
            return false;
        }

        $expires_at = null;
        if (!empty($token['expires_in'])) {
            $expires_at = date('Y-m-d H:i:s', time() + (int) $token['expires_in'] - 60);
        }

        $update = array(
            'access_token_enc' => $this->encrypt($token['access_token']),
            'token_expires_at' => $expires_at
        );
        if (!empty($token['refresh_token'])) {
            $update['refresh_token_enc'] = $this->encrypt($token['refresh_token']);
        }
        $this->CI->User_google_drive_model->upsert($user_id, $update);

        return $token['access_token'];
    }

    protected function exchange_code($code) {
        $settings = $this->get_settings();
        $redirect_uri = $this->CI->session->userdata('gdrive_oauth_redirect_uri');
        if (!$redirect_uri) {
            $redirect_uri = $this->effective_redirect_uri();
        }
        return $this->http_post(self::TOKEN_URL, array(
            'code' => $code,
            'client_id' => $settings->client_id,
            'client_secret' => $this->get_client_secret_plain($settings),
            'redirect_uri' => $redirect_uri,
            'grant_type' => 'authorization_code'
        ));
    }

    protected function refresh_access_token($refresh_token) {
        $settings = $this->get_settings();
        return $this->http_post(self::TOKEN_URL, array(
            'client_id' => $settings->client_id,
            'client_secret' => $this->get_client_secret_plain($settings),
            'refresh_token' => $refresh_token,
            'grant_type' => 'refresh_token'
        ));
    }

    protected function fetch_email($access_token) {
        $res = $this->http_get(self::USERINFO_URL, $access_token);
        return (!empty($res['email'])) ? $res['email'] : null;
    }

    protected function http_post($url, array $fields) {
        $ch = curl_init($url);
        curl_setopt_array($ch, array(
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => http_build_query($fields),
            CURLOPT_HTTPHEADER => array('Content-Type: application/x-www-form-urlencoded'),
            CURLOPT_TIMEOUT => 30
        ));
        $body = curl_exec($ch);
        $err = curl_error($ch);
        curl_close($ch);
        if ($body === false) {
            log_message('error', 'Google_drive_oauth POST error: ' . $err);
            return array('error' => 'curl', 'error_description' => $err);
        }
        $json = json_decode($body, true);
        return is_array($json) ? $json : array('error' => 'invalid_json', 'error_description' => $body);
    }

    protected function http_get($url, $access_token) {
        $ch = curl_init($url);
        curl_setopt_array($ch, array(
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => array('Authorization: Bearer ' . $access_token),
            CURLOPT_TIMEOUT => 30
        ));
        $body = curl_exec($ch);
        curl_close($ch);
        $json = json_decode($body, true);
        return is_array($json) ? $json : array();
    }

    public function encrypt($plain) {
        $key = $this->encryption_key();
        $iv = random_bytes(16);
        $cipher = openssl_encrypt((string) $plain, 'AES-256-CBC', $key, OPENSSL_RAW_DATA, $iv);
        if ($cipher === false) {
            return null;
        }
        return base64_encode($iv . $cipher);
    }

    public function decrypt($payload) {
        if ($payload === null || $payload === '') {
            return false;
        }
        $raw = base64_decode($payload, true);
        if ($raw === false || strlen($raw) < 17) {
            return false;
        }
        $iv = substr($raw, 0, 16);
        $cipher = substr($raw, 16);
        $plain = openssl_decrypt($cipher, 'AES-256-CBC', $this->encryption_key(), OPENSSL_RAW_DATA, $iv);
        return $plain === false ? false : $plain;
    }

    protected function encryption_key() {
        $key = (string) $this->CI->config->item('encryption_key');
        if ($key === '') {
            $key = 'siap-prodi-gdrive-fallback-key';
        }
        return hash('sha256', $key, true);
    }

    protected function encode_state(array $data) {
        return rtrim(strtr(base64_encode(json_encode($data)), '+/', '-_'), '=');
    }

    protected function decode_state($state) {
        $b64 = strtr((string) $state, '-_', '+/');
        $pad = strlen($b64) % 4;
        if ($pad) {
            $b64 .= str_repeat('=', 4 - $pad);
        }
        $json = base64_decode($b64, true);
        if ($json === false) {
            return null;
        }
        $data = json_decode($json, true);
        return is_array($data) ? $data : null;
    }
}
