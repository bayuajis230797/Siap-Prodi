<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class User_google_drive_model extends CI_Model {

    protected $table = 'user_google_drive';

    public function get_by_user($user_id) {
        return $this->db->get_where($this->table, array('user_id' => (int) $user_id))->row();
    }

    public function is_connected($user_id) {
        $row = $this->get_by_user($user_id);
        return $row
            && empty($row->revoked_at)
            && !empty($row->refresh_token_enc);
    }

    public function upsert($user_id, array $data) {
        $existing = $this->get_by_user($user_id);
        $data['updated_at'] = date('Y-m-d H:i:s');

        if ($existing) {
            $this->db->where('user_id', (int) $user_id);
            return $this->db->update($this->table, $data);
        }

        $data['user_id'] = (int) $user_id;
        return $this->db->insert($this->table, $data);
    }

    public function revoke($user_id) {
        return $this->upsert($user_id, array(
            'access_token_enc' => null,
            'refresh_token_enc' => null,
            'token_expires_at' => null,
            'revoked_at' => date('Y-m-d H:i:s')
        ));
    }
}
