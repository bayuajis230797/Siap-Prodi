<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Google_oauth_setting_model extends CI_Model {

    protected $table = 'google_oauth_settings';

    public function get_settings() {
        $this->db->where('is_aktif', 1);
        $this->db->order_by('id', 'ASC');
        $this->db->limit(1);
        $row = $this->db->get($this->table)->row();
        if ($row) {
            return $row;
        }

        $this->db->insert($this->table, array(
            'client_id' => '',
            'client_secret_enc' => null,
            'redirect_uri' => site_url('Profile/oauth_callback'),
            'is_aktif' => 1
        ));

        return $this->db->get_where($this->table, array('id' => $this->db->insert_id()))->row();
    }

    public function save($data) {
        $settings = $this->get_settings();
        if ($settings && $settings->id) {
            $this->db->where('id', (int) $settings->id);
            return $this->db->update($this->table, $data);
        }
        return $this->db->insert($this->table, $data);
    }

    public function is_configured() {
        $settings = $this->get_settings();
        return $settings
            && trim((string) $settings->client_id) !== ''
            && trim((string) $settings->client_secret_enc) !== ''
            && trim((string) $settings->redirect_uri) !== '';
    }
}
