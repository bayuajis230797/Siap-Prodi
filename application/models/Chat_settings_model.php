<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Chat_settings_model extends CI_Model {

    public function get_settings() {
        $this->db->where('is_aktif', 1);
        $this->db->order_by('id', 'DESC');
        $this->db->limit(1);
        $row = $this->db->get('chat_settings')->row();

        if (!$row) {
            return (object) array(
                'enable_dosen_to_mahasiswa' => 1,
                'enable_sekprodi_to_mahasiswa' => 1,
                'enable_admin_to_mahasiswa' => 1,
                'enable_mahasiswa_to_dosen' => 1,
                'enable_mahasiswa_to_mahasiswa' => 1,
                'mhs_dosen_rule' => 'pembimbing_only',
                'mhs_mhs_rule' => 'same_class',
            );
        }

        return $row;
    }

    public function upsert($data) {
        $settings = $this->get_settings();
        if (!empty($settings->id)) {
            $this->db->where('id', (int) $settings->id);
            $this->db->update('chat_settings', $data);
            return $this->db->affected_rows() >= 0;
        }

        $data['is_aktif'] = 1;
        $this->db->insert('chat_settings', $data);
        return (bool) $this->db->insert_id();
    }
}
