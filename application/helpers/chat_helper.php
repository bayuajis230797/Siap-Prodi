<?php
defined('BASEPATH') OR exit('No direct script access allowed');

if (!function_exists('chat_get_settings')) {
    function chat_get_settings() {
        $CI =& get_instance();
        $CI->load->model('Chat_settings_model');
        return $CI->Chat_settings_model->get_settings();
    }
}

if (!function_exists('chat_display_name')) {
    function chat_display_name($user_id) {
        $CI =& get_instance();
        $CI->load->model('User_model');
        $user = $CI->User_model->get_user_by_id((int) $user_id);
        if (!$user) {
            return 'Pengguna #' . (int) $user_id;
        }

        $role = strtolower((string) ($user->nama_role ?? ''));
        $ref_id = (int) ($user->ref_id ?? 0);

        if ($role === 'mahasiswa' && $ref_id > 0) {
            $m = $CI->db->get_where('mahasiswa', array('id' => $ref_id))->row();
            if ($m) {
                return $m->nama_mahasiswa . ' (' . $m->nim . ')';
            }
        }
        if ($role === 'dosen' && $ref_id > 0) {
            $d = $CI->db->get_where('dosen', array('id' => $ref_id))->row();
            if ($d) {
                return $d->nama_dosen . ' (Dosen)';
            }
        }

        return $user->username . ' (' . ucfirst($role) . ')';
    }
}

if (!function_exists('chat_is_pembimbing_of')) {
    function chat_is_pembimbing_of($id_dosen, $id_mahasiswa) {
        $id_dosen = (int) $id_dosen;
        $id_mahasiswa = (int) $id_mahasiswa;
        if ($id_dosen <= 0 || $id_mahasiswa <= 0) {
            return false;
        }

        $CI =& get_instance();
        $CI->load->model('Tahun_akademik_model');
        $tahun = $CI->Tahun_akademik_model->get_aktif();
        $id_tahun = $tahun ? (int) $tahun->id : 0;

        $CI->db->where('id_mahasiswa', $id_mahasiswa);
        $CI->db->where('id_pembimbing', $id_dosen);
        if ($id_tahun > 0) {
            $CI->db->where('id_tahun_akademik', $id_tahun);
        }
        if ($CI->db->count_all_results('pkl_pengajuan') > 0) {
            return true;
        }

        $CI->db->where('id_mahasiswa', $id_mahasiswa);
        $CI->db->group_start();
        $CI->db->where('id_pembimbing_1', $id_dosen);
        $CI->db->or_where('id_pembimbing_2', $id_dosen);
        $CI->db->group_end();
        if ($CI->db->count_all_results('mahasiswa_skripsi') > 0) {
            return true;
        }

        $CI->db->where('id_mahasiswa', $id_mahasiswa);
        $CI->db->where('id_pembimbing_final', $id_dosen);
        if ($CI->db->count_all_results('pkm_pengajuan') > 0) {
            return true;
        }

        return false;
    }
}

if (!function_exists('chat_can_send')) {
    function chat_can_send($sender_user_id, $sender_role, $recipient_user_id, $recipient_role) {
        $settings = chat_get_settings();
        $sender_role = strtolower(trim((string) $sender_role));
        $recipient_role = strtolower(trim((string) $recipient_role));

        $CI =& get_instance();
        $CI->load->model('User_model');

        $sender = $CI->User_model->get_user_by_id((int) $sender_user_id);
        $recipient = $CI->User_model->get_user_by_id((int) $recipient_user_id);
        if (!$sender || !$recipient) {
            return array('allowed' => false, 'message' => 'Pengguna tidak ditemukan.');
        }

        if ((int) $sender_user_id === (int) $recipient_user_id) {
            return array('allowed' => false, 'message' => 'Tidak dapat mengirim pesan ke diri sendiri.');
        }

        $staff_roles = array('sekprodi', 'kaprodi', 'admin');

        if ($sender_role === 'dosen' && $recipient_role === 'mahasiswa') {
            if (empty($settings->enable_dosen_to_mahasiswa)) {
                return array('allowed' => false, 'message' => 'Saluran dosen ke mahasiswa dinonaktifkan admin.');
            }
            return array('allowed' => true, 'message' => '');
        }

        if (in_array($sender_role, $staff_roles, true) && $recipient_role === 'mahasiswa') {
            if ($sender_role === 'admin' && empty($settings->enable_admin_to_mahasiswa)) {
                return array('allowed' => false, 'message' => 'Saluran admin ke mahasiswa dinonaktifkan.');
            }
            if (in_array($sender_role, array('sekprodi', 'kaprodi'), true) && empty($settings->enable_sekprodi_to_mahasiswa)) {
                return array('allowed' => false, 'message' => 'Saluran sekprodi ke mahasiswa dinonaktifkan.');
            }
            return array('allowed' => true, 'message' => '');
        }

        if ($sender_role === 'mahasiswa' && $recipient_role === 'dosen') {
            if (empty($settings->enable_mahasiswa_to_dosen)) {
                return array('allowed' => false, 'message' => 'Mahasiswa ke dosen dinonaktifkan admin.');
            }
            if ($settings->mhs_dosen_rule === 'pembimbing_only') {
                $id_mhs = (int) $sender->ref_id;
                $id_dosen = (int) $recipient->ref_id;
                if (!chat_is_pembimbing_of($id_dosen, $id_mhs)) {
                    return array('allowed' => false, 'message' => 'Anda hanya dapat menghubungi dosen pembimbing.');
                }
            }
            return array('allowed' => true, 'message' => '');
        }

        if ($sender_role === 'mahasiswa' && $recipient_role === 'mahasiswa') {
            if (empty($settings->enable_mahasiswa_to_mahasiswa)) {
                return array('allowed' => false, 'message' => 'Chat antar mahasiswa dinonaktifkan admin.');
            }
            if ($settings->mhs_mhs_rule === 'same_class') {
                $m1 = $CI->db->get_where('mahasiswa', array('id' => (int) $sender->ref_id))->row();
                $m2 = $CI->db->get_where('mahasiswa', array('id' => (int) $recipient->ref_id))->row();
                if (!$m1 || !$m2 || trim((string) $m1->kelas) === '' || $m1->kelas !== $m2->kelas) {
                    return array('allowed' => false, 'message' => 'Chat antar mahasiswa hanya diperbolehkan sesama kelas.');
                }
            }
            return array('allowed' => true, 'message' => '');
        }

        if ($recipient_role === 'mahasiswa') {
            return array('allowed' => false, 'message' => 'Saluran pengiriman ke mahasiswa tidak diizinkan untuk role Anda.');
        }

        return array('allowed' => false, 'message' => 'Kombinasi pengirim/penerima tidak diizinkan.');
    }
}

if (!function_exists('chat_conversation_title')) {
    function chat_conversation_title($conversation, $current_user_id) {
        $CI =& get_instance();
        $CI->load->model('Chat_model');

        if ($conversation->type === 'broadcast' && !empty($conversation->subject)) {
            return $conversation->subject;
        }

        $participants = $CI->Chat_model->get_participants($conversation->id);
        $is_participant = false;
        foreach ($participants as $p) {
            if ((int) $p->id_user === (int) $current_user_id) {
                $is_participant = true;
                break;
            }
        }

        $names = array();
        foreach ($participants as $p) {
            // Admin pantau (bukan peserta): tampilkan semua nama
            if ($is_participant && (int) $p->id_user === (int) $current_user_id) {
                continue;
            }
            $role_label = !empty($p->role) ? ' · ' . ucfirst($p->role) : '';
            $names[] = chat_display_name($p->id_user) . ($is_participant ? '' : $role_label);
        }

        if (empty($names)) {
            return 'Percakapan #' . (int) $conversation->id;
        }

        return implode(' ↔ ', $names);
    }
}

if (!function_exists('chat_sidebar_menu_item')) {
    function chat_sidebar_menu_item() {
        $current_url = current_url();
        $CI =& get_instance();
        $count = 0;
        if ($CI->db->table_exists('chat_messages') && $CI->session->userdata('user_id')) {
            $CI->load->model('Chat_model');
            $count = (int) $CI->Chat_model->count_unread((int) $CI->session->userdata('user_id'));
        }
        $badge = $count > 0 ? ' <span class="badge badge-danger right">' . $count . '</span>' : '';
        $active = (strpos($current_url, 'Chat') !== false && strpos($current_url, 'Chat_settings') === false) ? 'active' : '';
        return '<li class="nav-item"><a href="' . site_url('Chat') . '" class="nav-link ' . $active . '"><i class="nav-icon fas fa-comments"></i><p>Pesan' . $badge . '</p></a></li>';
    }
}
