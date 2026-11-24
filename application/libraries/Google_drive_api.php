<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Google Drive API helpers (folder, upload, share) via REST + curl.
 * Upload memakai resumable upload agar file besar (>20MB) aman.
 */
class Google_drive_api {

    const FILES_URL = 'https://www.googleapis.com/drive/v3/files';
    const UPLOAD_URL = 'https://www.googleapis.com/upload/drive/v3/files';
    const CHUNK_SIZE = 2621440; // 2.5MB (kelipatan 256KB)

    /** @var CI_Controller */
    protected $CI;

    public function __construct() {
        $this->CI =& get_instance();
        $this->CI->load->library('Google_drive_oauth');
        $this->CI->load->model('User_google_drive_model');
    }

    /**
     * Pastikan path folder SIAP/... ada di Drive; return folder_id terakhir.
     *
     * @param int $user_id
     * @param string[] $path_parts e.g. ['SIAP','Proposal','bimbingan']
     * @return string|false folder id
     */
    public function ensure_folder_path($user_id, array $path_parts) {
        $token = $this->CI->google_drive_oauth->get_valid_access_token($user_id);
        if (!$token) {
            return false;
        }

        $parent = 'root';
        $row = $this->CI->User_google_drive_model->get_by_user($user_id);

        foreach ($path_parts as $i => $name) {
            $name = trim($name);
            if ($name === '') {
                continue;
            }

            if ($i === 0 && strtoupper($name) === 'SIAP' && $row && !empty($row->root_folder_id)) {
                $parent = $row->root_folder_id;
                continue;
            }

            $existing = $this->find_child_folder($token, $parent, $name);
            if ($existing) {
                $parent = $existing;
            } else {
                $created = $this->create_folder($token, $name, $parent);
                if (!$created) {
                    return false;
                }
                $parent = $created;
            }

            if ($i === 0 && strtoupper($name) === 'SIAP') {
                $this->CI->User_google_drive_model->upsert($user_id, array('root_folder_id' => $parent));
                $row = $this->CI->User_google_drive_model->get_by_user($user_id);
            }
        }

        return $parent;
    }

    /**
     * Upload file lokal ke Drive (resumable).
     *
     * @return array|false {id, name, webViewLink, webContentLink, mimeType}
     */
    public function upload_file($user_id, $local_path, $filename, $folder_id, $mime = null) {
        $token = $this->CI->google_drive_oauth->get_valid_access_token($user_id);
        if (!$token || !is_file($local_path)) {
            return false;
        }

        if ($mime === null) {
            $mime = function_exists('mime_content_type') ? mime_content_type($local_path) : 'application/octet-stream';
            if (!$mime) {
                $mime = 'application/octet-stream';
            }
        }

        $filesize = filesize($local_path);
        if ($filesize === false) {
            return false;
        }

        // File kecil: multipart sederhana; besar: resumable
        if ($filesize <= 5 * 1024 * 1024) {
            return $this->upload_multipart($token, $local_path, $filename, $folder_id, $mime);
        }

        return $this->upload_resumable($token, $local_path, $filename, $folder_id, $mime, $filesize);
    }

    protected function upload_multipart($token, $local_path, $filename, $folder_id, $mime) {
        $metadata = array(
            'name' => $filename,
            'parents' => array($folder_id)
        );

        $boundary = '----siapBoundary' . bin2hex(random_bytes(8));
        $body = "--{$boundary}\r\n";
        $body .= "Content-Type: application/json; charset=UTF-8\r\n\r\n";
        $body .= json_encode($metadata) . "\r\n";
        $body .= "--{$boundary}\r\n";
        $body .= "Content-Type: {$mime}\r\n\r\n";
        $body .= file_get_contents($local_path) . "\r\n";
        $body .= "--{$boundary}--";

        $ch = curl_init(self::UPLOAD_URL . '?uploadType=multipart&fields=id,name,webViewLink,webContentLink,mimeType');
        curl_setopt_array($ch, array(
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => $body,
            CURLOPT_HTTPHEADER => array(
                'Authorization: Bearer ' . $token,
                'Content-Type: multipart/related; boundary=' . $boundary,
                'Content-Length: ' . strlen($body)
            ),
            CURLOPT_TIMEOUT => 300
        ));
        $raw = curl_exec($ch);
        $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $err = curl_error($ch);
        curl_close($ch);

        if ($raw === false || $code >= 400) {
            log_message('error', 'Google_drive_api multipart failed HTTP ' . $code . ' ' . $err . ' body=' . substr((string) $raw, 0, 500));
            return false;
        }

        $json = json_decode($raw, true);
        return is_array($json) && !empty($json['id']) ? $json : false;
    }

    protected function upload_resumable($token, $local_path, $filename, $folder_id, $mime, $filesize) {
        $metadata = json_encode(array(
            'name' => $filename,
            'parents' => array($folder_id)
        ));

        $init = curl_init(self::UPLOAD_URL . '?uploadType=resumable');
        curl_setopt_array($init, array(
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HEADER => true,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => $metadata,
            CURLOPT_HTTPHEADER => array(
                'Authorization: Bearer ' . $token,
                'Content-Type: application/json; charset=UTF-8',
                'X-Upload-Content-Type: ' . $mime,
                'X-Upload-Content-Length: ' . $filesize
            ),
            CURLOPT_TIMEOUT => 60
        ));
        $header_raw = curl_exec($init);
        $code = curl_getinfo($init, CURLINFO_HTTP_CODE);
        curl_close($init);

        if ($header_raw === false || $code >= 400) {
            log_message('error', 'Google_drive_api resumable init failed HTTP ' . $code . ' ' . substr((string) $header_raw, 0, 400));
            return false;
        }

        if (!preg_match('/^Location:\s*(.+)$/mi', $header_raw, $m)) {
            log_message('error', 'Google_drive_api resumable missing Location header');
            return false;
        }
        $session_url = trim($m[1]);

        $fp = fopen($local_path, 'rb');
        if (!$fp) {
            return false;
        }

        $offset = 0;
        $final_body = null;
        while ($offset < $filesize) {
            $chunk = fread($fp, self::CHUNK_SIZE);
            if ($chunk === false) {
                fclose($fp);
                return false;
            }
            $chunk_len = strlen($chunk);
            $end = $offset + $chunk_len - 1;

            $ch = curl_init($session_url);
            curl_setopt_array($ch, array(
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_CUSTOMREQUEST => 'PUT',
                CURLOPT_POSTFIELDS => $chunk,
                CURLOPT_HTTPHEADER => array(
                    'Content-Length: ' . $chunk_len,
                    'Content-Range: bytes ' . $offset . '-' . $end . '/' . $filesize
                ),
                CURLOPT_TIMEOUT => 300
            ));
            $resp = curl_exec($ch);
            $http = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);

            // 308 = Resume Incomplete
            if ($http == 308) {
                $offset += $chunk_len;
                continue;
            }
            if ($http >= 200 && $http < 300) {
                $final_body = $resp;
                break;
            }

            log_message('error', 'Google_drive_api resumable chunk failed HTTP ' . $http . ' ' . substr((string) $resp, 0, 400));
            fclose($fp);
            return false;
        }
        fclose($fp);

        if ($final_body === null) {
            return false;
        }

        // Ambil metadata lengkap
        $json = json_decode($final_body, true);
        if (!is_array($json) || empty($json['id'])) {
            return false;
        }

        return $this->get_file_meta($token, $json['id']);
    }

    protected function get_file_meta($token, $file_id) {
        $url = self::FILES_URL . '/' . rawurlencode($file_id) . '?fields=id,name,webViewLink,webContentLink,mimeType';
        $res = $this->http_get($url, $token);
        return (!empty($res['id'])) ? $res : false;
    }

    public function share_with_email($user_id, $file_id, $email, $role = 'reader') {
        $token = $this->CI->google_drive_oauth->get_valid_access_token($user_id);
        if (!$token || !$file_id || !$email) {
            return false;
        }

        $payload = json_encode(array(
            'type' => 'user',
            'role' => $role,
            'emailAddress' => $email
        ));

        $url = self::FILES_URL . '/' . rawurlencode($file_id) . '/permissions?sendNotificationEmail=false';
        $ch = curl_init($url);
        curl_setopt_array($ch, array(
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => $payload,
            CURLOPT_HTTPHEADER => array(
                'Authorization: Bearer ' . $token,
                'Content-Type: application/json'
            ),
            CURLOPT_TIMEOUT => 30
        ));
        $raw = curl_exec($ch);
        $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($code >= 400) {
            log_message('error', 'Google_drive_api share failed HTTP ' . $code . ' ' . substr((string) $raw, 0, 400));
            return false;
        }
        return true;
    }

    public function share_anyone_with_link($user_id, $file_id) {
        $token = $this->CI->google_drive_oauth->get_valid_access_token($user_id);
        if (!$token || !$file_id) {
            return false;
        }

        $payload = json_encode(array(
            'type' => 'anyone',
            'role' => 'reader'
        ));

        $url = self::FILES_URL . '/' . rawurlencode($file_id) . '/permissions';
        $ch = curl_init($url);
        curl_setopt_array($ch, array(
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => $payload,
            CURLOPT_HTTPHEADER => array(
                'Authorization: Bearer ' . $token,
                'Content-Type: application/json'
            ),
            CURLOPT_TIMEOUT => 30
        ));
        $raw = curl_exec($ch);
        $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        return $code < 400;
    }

    protected function find_child_folder($token, $parent_id, $name) {
        $q = sprintf(
            "mimeType = 'application/vnd.google-apps.folder' and name = '%s' and '%s' in parents and trashed = false",
            $this->escape_query($name),
            $this->escape_query($parent_id)
        );
        $url = self::FILES_URL . '?q=' . rawurlencode($q) . '&fields=files(id,name)&pageSize=1';
        $res = $this->http_get($url, $token);
        if (!empty($res['files'][0]['id'])) {
            return $res['files'][0]['id'];
        }
        return false;
    }

    protected function create_folder($token, $name, $parent_id) {
        $payload = json_encode(array(
            'name' => $name,
            'mimeType' => 'application/vnd.google-apps.folder',
            'parents' => array($parent_id)
        ));
        $url = self::FILES_URL . '?fields=id,name';
        $ch = curl_init($url);
        curl_setopt_array($ch, array(
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => $payload,
            CURLOPT_HTTPHEADER => array(
                'Authorization: Bearer ' . $token,
                'Content-Type: application/json'
            ),
            CURLOPT_TIMEOUT => 30
        ));
        $raw = curl_exec($ch);
        $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        $json = json_decode($raw, true);
        if ($code >= 400 || empty($json['id'])) {
            log_message('error', 'create_folder failed: ' . substr((string) $raw, 0, 400));
            return false;
        }
        return $json['id'];
    }

    protected function http_get($url, $token) {
        $ch = curl_init($url);
        curl_setopt_array($ch, array(
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => array('Authorization: Bearer ' . $token),
            CURLOPT_TIMEOUT => 30
        ));
        $raw = curl_exec($ch);
        curl_close($ch);
        $json = json_decode($raw, true);
        return is_array($json) ? $json : array();
    }

    protected function escape_query($value) {
        return str_replace(array("\\", "'"), array("\\\\", "\\'"), $value);
    }
}
