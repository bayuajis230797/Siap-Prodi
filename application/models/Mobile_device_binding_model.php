<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Satu fingerprint perangkat hanya boleh terikat satu akun mahasiswa (login mobile).
 */
class Mobile_device_binding_model extends CI_Model {

    public function __construct() {
        parent::__construct();
        $this->load->database();
    }

    public function ensure_table() {
        if ($this->db->table_exists('mobile_app_device_binding')) {
            return;
        }
        $sql = "CREATE TABLE IF NOT EXISTS `mobile_app_device_binding` (
            `device_fingerprint` varchar(191) NOT NULL,
            `id_mahasiswa` int(11) NOT NULL,
            `updated_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (`device_fingerprint`),
            KEY `idx_id_mahasiswa` (`id_mahasiswa`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci";
        try {
            $this->db->query($sql);
        } catch (Exception $e) {
            log_message('error', 'Mobile_device_binding_model::ensure_table: ' . $e->getMessage());
        }
    }

    /**
     * @param int $id_mahasiswa
     * @param string $fingerprint
     * @return array{ok:bool,message?:string}
     */
    public function assert_binding_or_bind($id_mahasiswa, $fingerprint) {
        $id_mahasiswa = (int) $id_mahasiswa;
        $fingerprint = trim((string) $fingerprint);
        if ($fingerprint === '' || strlen($fingerprint) < 6) {
            return array(
                'ok' => false,
                'message' => 'Identitas perangkat tidak valid. Perbarui aplikasi lalu coba login lagi.',
            );
        }
        $this->ensure_table();
        if (!$this->db->table_exists('mobile_app_device_binding')) {
            return array(
                'ok' => false,
                'message' => 'Tabel pengikatan perangkat belum tersedia di server. Hubungi administrator (jalankan migrasi mobile_app_device_binding).',
            );
        }
        $row = $this->db->where('device_fingerprint', $fingerprint)->get('mobile_app_device_binding')->row();
        if ($row && (int) $row->id_mahasiswa !== $id_mahasiswa) {
            return array(
                'ok' => false,
                'message' => 'Perangkat ini sudah digunakan akun mahasiswa lain. Satu perangkat hanya untuk satu akun. Hubungi administrator bila perlu reset.',
            );
        }
        $this->db->where('id_mahasiswa', $id_mahasiswa)->delete('mobile_app_device_binding');
        $this->db->insert('mobile_app_device_binding', array(
            'device_fingerprint' => $fingerprint,
            'id_mahasiswa' => $id_mahasiswa,
            'updated_at' => date('Y-m-d H:i:s'),
        ));
        return array('ok' => true);
    }
}
