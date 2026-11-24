<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Chat extends MY_Controller {

    private $allowed_roles = array('mahasiswa', 'dosen', 'sekprodi', 'admin', 'kaprodi');

    public function __construct() {
        parent::__construct();
        $this->check_role($this->allowed_roles);
        $this->load->model('Chat_model');
        $this->load->model('User_model');
        $this->load->helper('chat');
        $this->load->library('form_validation');
    }

    private function current_user_id() {
        return (int) $this->session->userdata('user_id');
    }

    private function current_role() {
        return strtolower((string) $this->session->userdata('role'));
    }

    public function index() {
        $user_id = $this->current_user_id();
        $role = $this->current_role();
        $is_admin_monitor = ($role === 'admin');
        $q = trim((string) $this->input->get('q'));

        $this->load->library('pagination');
        $config['base_url'] = site_url('Chat');
        $config['per_page'] = $is_admin_monitor ? 30 : 10;
        $config['uri_segment'] = 2;
        $config['reuse_query_string'] = true;

        if ($is_admin_monitor) {
            $config['total_rows'] = $this->Chat_model->count_all_conversations($q);
        } else {
            $config['total_rows'] = $this->Chat_model->count_conversations_for_user($user_id);
        }
        $this->pagination->initialize($config);

        $page = $this->uri->segment(2) ? (int) $this->uri->segment(2) : 0;
        if ($is_admin_monitor) {
            $conversations = $this->Chat_model->get_all_conversations($config['per_page'], $page, $q);
        } else {
            $conversations = $this->Chat_model->get_conversations_for_user($user_id, $config['per_page'], $page);
        }

        $data = array(
            'title' => $is_admin_monitor ? 'Pantau Pesan' : 'Pesan',
            'page_title' => $is_admin_monitor ? 'Pantau Pesan (Semua Role)' : 'Pesan',
            'breadcrumb' => array(array('label' => 'Pesan', 'active' => true)),
            'sidebar_menu' => generate_sidebar_menu($role),
            'conversations' => $conversations,
            'user_id' => $user_id,
            'pagination' => $this->pagination->create_links(),
            'is_admin_monitor' => $is_admin_monitor,
            'chat_search_q' => $q,
        );
        $data['content'] = $this->load->view('chat/index', $data, true);
        $this->load->view('layouts/main', $data);
    }

    public function compose() {
        $role = $this->current_role();

        $data = array(
            'title' => 'Tulis Pesan',
            'page_title' => 'Tulis Pesan Baru',
            'breadcrumb' => array(
                array('label' => 'Pesan', 'url' => site_url('Chat')),
                array('label' => 'Tulis Pesan', 'active' => true),
            ),
            'sidebar_menu' => generate_sidebar_menu($role),
            'role' => $role,
        );
        $data['content'] = $this->load->view('chat/compose', $data, true);
        $this->load->view('layouts/main', $data);
    }

    public function search_users() {
        $q = trim((string) $this->input->get('q'));
        if (strlen($q) < 2) {
            $this->output->set_content_type('application/json')->set_output(json_encode(array('success' => true, 'data' => array())));
            return;
        }

        $sender_id = $this->current_user_id();
        $sender_role = $this->current_role();
        $results = array();
        $seen_ids = array();

        $add_result = function($user_id, $label, $role) use (&$results, &$seen_ids, $sender_id, $sender_role) {
            $user_id = (int) $user_id;
            if ($user_id <= 0 || isset($seen_ids[$user_id])) {
                return;
            }
            $role = strtolower((string) $role);
            $check = chat_can_send($sender_id, $sender_role, $user_id, $role);
            if (!$check['allowed']) {
                return;
            }
            $seen_ids[$user_id] = true;
            $results[] = array('id' => $user_id, 'label' => $label, 'role' => $role);
        };

        $staff_to_mhs = in_array($sender_role, array('dosen', 'sekprodi', 'admin', 'kaprodi'), true);

        if ($sender_role === 'mahasiswa' || $staff_to_mhs) {
            $this->db->select('u.id, m.nim, m.nama_mahasiswa, m.kelas');
            $this->db->from('mahasiswa m');
            $this->db->join('users u', 'u.ref_id = m.id', 'inner');
            $this->db->join('roles r', 'r.id = u.role_id AND LOWER(r.nama_role) = \'mahasiswa\'', 'inner');
            $this->db->where('u.id !=', $sender_id);
            $this->db->group_start();
            $this->db->like('m.nim', $q);
            $this->db->or_like('m.nama_mahasiswa', $q);
            $this->db->or_like('u.username', $q);
            $this->db->group_end();
            $this->db->limit(30);
            foreach ($this->db->get()->result() as $m) {
                $kelas_label = $m->kelas ? ' - ' . $m->kelas : '';
                $add_result($m->id, $m->nama_mahasiswa . ' (' . $m->nim . $kelas_label . ')', 'mahasiswa');
            }
        }

        if ($sender_role === 'mahasiswa') {
            $this->db->select('u.id, d.nama_dosen, d.nidn, u.username');
            $this->db->from('dosen d');
            $this->db->join('users u', 'u.ref_id = d.id', 'inner');
            $this->db->join('roles r', 'r.id = u.role_id AND LOWER(r.nama_role) = \'dosen\'', 'inner');
            $this->db->where('u.id !=', $sender_id);
            $this->db->group_start();
            $this->db->like('d.nama_dosen', $q);
            $this->db->or_like('d.nidn', $q);
            $this->db->or_like('u.username', $q);
            $this->db->group_end();
            $this->db->limit(20);
            foreach ($this->db->get()->result() as $d) {
                $label = $d->nama_dosen . ' (Dosen' . ($d->nidn ? ' - ' . $d->nidn : '') . ')';
                $add_result($d->id, $label, 'dosen');
            }
        }

        $this->output->set_content_type('application/json')->set_output(json_encode(array(
            'success' => true,
            'data' => array_slice($results, 0, 20),
        )));
    }

    public function send() {
        if ($this->input->method() !== 'post') {
            show_404();
        }

        $sender_id = $this->current_user_id();
        $sender_role = $this->current_role();
        $mode = $this->input->post('mode') ?: 'direct';

        if ($mode === 'broadcast') {
            $this->session->set_flashdata('error', 'Fitur siaran ke kelas tidak tersedia.');
            redirect('Chat/compose');
        }

        $this->form_validation->set_rules('body', 'Isi pesan', 'required|trim|min_length[1]');
        $this->form_validation->set_rules('recipient_user_id', 'Penerima', 'required|integer');

        if ($this->form_validation->run() === FALSE) {
            $this->session->set_flashdata('error', validation_errors());
            redirect('Chat/compose');
        }

        $body = trim($this->input->post('body'));
        $is_priority = $this->input->post('is_priority') ? 1 : 0;
        if ($sender_role === 'mahasiswa') {
            $is_priority = 0;
        }

        $recipient_id = (int) $this->input->post('recipient_user_id');
        $recipient = $this->User_model->get_user_by_id($recipient_id);
        if (!$recipient) {
            $this->session->set_flashdata('error', 'Penerima tidak ditemukan.');
            redirect('Chat/compose');
        }

        $check = chat_can_send($sender_id, $sender_role, $recipient_id, strtolower($recipient->nama_role));
        if (!$check['allowed']) {
            $this->session->set_flashdata('error', $check['message']);
            redirect('Chat/compose');
        }

        $conv = $this->Chat_model->find_direct_conversation($sender_id, $recipient_id);
        if (!$conv) {
            $conv_id = $this->Chat_model->create_conversation('direct', $sender_id);
            $this->Chat_model->add_participant($conv_id, $sender_id, $sender_role);
            $this->Chat_model->add_participant($conv_id, $recipient_id, strtolower($recipient->nama_role));
        } else {
            $conv_id = (int) $conv->id;
        }

        $msg_id = $this->Chat_model->send_message($conv_id, $sender_id, $body, $is_priority);
        if ($msg_id) {
            $this->load->helper('notifikasi');
            $judul = $is_priority ? 'Pesan Penting' : 'Pesan Baru';
            send_in_app_notification_by_user($recipient_id, strtolower($recipient->nama_role), $judul, mb_substr($body, 0, 120), site_url('Chat/thread/' . $conv_id), 'chat');
            $this->session->set_flashdata('success', 'Pesan berhasil dikirim.');
            redirect('Chat/thread/' . $conv_id);
        }

        $this->session->set_flashdata('error', 'Gagal mengirim pesan.');
        redirect('Chat/compose');
    }

    public function thread($id) {
        $user_id = $this->current_user_id();
        $role = $this->current_role();
        $conv = $this->Chat_model->get_conversation($id);
        $is_participant = $conv && $this->Chat_model->is_participant($id, $user_id);
        $is_admin_monitor = ($role === 'admin' && $conv && !$is_participant);

        if (!$conv || (!$is_participant && $role !== 'admin')) {
            $this->session->set_flashdata('error', 'Percakapan tidak ditemukan.');
            redirect('Chat');
        }

        $messages = $this->Chat_model->get_messages($id);
        if ($is_participant) {
            $this->Chat_model->mark_conversation_read($id, $user_id);
        }

        $read_map = array();
        foreach ($messages as $msg) {
            if ((int) $msg->sender_user_id === $user_id && $conv->type === 'direct') {
                $parts = $this->Chat_model->get_participants($id);
                foreach ($parts as $p) {
                    if ((int) $p->id_user !== $user_id) {
                        $read_map[$msg->id] = $this->Chat_model->get_read_status_for_sender($msg->id, (int) $p->id_user);
                    }
                }
            }
        }

        $side_list = ($role === 'admin')
            ? $this->Chat_model->get_all_conversations(40, 0)
            : $this->Chat_model->get_conversations_for_user($user_id, 40, 0);

        $data = array(
            'title' => chat_conversation_title($conv, $user_id),
            'page_title' => chat_conversation_title($conv, $user_id),
            'breadcrumb' => array(
                array('label' => 'Pesan', 'url' => site_url('Chat')),
                array('label' => 'Percakapan', 'active' => true),
            ),
            'sidebar_menu' => generate_sidebar_menu($role),
            'conversation' => $conv,
            'messages' => $messages,
            'user_id' => $user_id,
            'read_map' => $read_map,
            'conversations' => $side_list,
            'is_admin_monitor' => $is_admin_monitor,
            'can_reply' => $is_participant,
        );
        $data['content'] = $this->load->view('chat/thread', $data, true);
        $this->load->view('layouts/main', $data);
    }

    public function reply($id) {
        if ($this->input->method() !== 'post') {
            show_404();
        }

        $user_id = $this->current_user_id();
        $conv = $this->Chat_model->get_conversation($id);
        if (!$conv || !$this->Chat_model->is_participant($id, $user_id)) {
            $this->session->set_flashdata('error', 'Percakapan tidak ditemukan.');
            redirect('Chat');
        }

        $body = trim((string) $this->input->post('body'));
        if ($body === '') {
            $this->session->set_flashdata('error', 'Isi pesan tidak boleh kosong.');
            redirect('Chat/thread/' . $id);
        }

        $is_priority = 0;
        if ($this->current_role() !== 'mahasiswa' && $this->input->post('is_priority')) {
            $is_priority = 1;
        }

        $msg_id = $this->Chat_model->send_message($id, $user_id, $body, $is_priority);
        if ($msg_id) {
            $this->load->helper('notifikasi');
            $parts = $this->Chat_model->get_participants($id);
            foreach ($parts as $p) {
                if ((int) $p->id_user === $user_id) {
                    continue;
                }
                send_in_app_notification_by_user((int) $p->id_user, strtolower($p->role), 'Balasan Pesan', mb_substr($body, 0, 120), site_url('Chat/thread/' . $id), 'chat');
            }
            $this->session->set_flashdata('success', 'Balasan terkirim.');
        } else {
            $this->session->set_flashdata('error', 'Gagal mengirim balasan.');
        }

        redirect('Chat/thread/' . $id);
    }

    public function broadcast($id) {
        $user_id = $this->current_user_id();
        $role = $this->current_role();
        $conv = $this->Chat_model->get_conversation($id);

        if (!$conv || $conv->type !== 'broadcast') {
            $this->session->set_flashdata('error', 'Siaran tidak ditemukan.');
            redirect('Chat');
        }

        if (!$this->Chat_model->is_participant($id, $user_id) && (int) $conv->created_by_user_id !== $user_id) {
            if (!in_array($role, array('sekprodi', 'admin', 'kaprodi'), true)) {
                $this->session->set_flashdata('error', 'Akses ditolak.');
                redirect('Chat');
            }
        }

        $messages = $this->Chat_model->get_messages($id, 1);
        $first_message = !empty($messages) ? $messages[0] : null;
        $receipts = $first_message ? $this->Chat_model->get_receipts_for_message($first_message->id) : array();

        $data = array(
            'title' => 'Detail Siaran',
            'page_title' => $conv->subject ?: 'Detail Siaran',
            'breadcrumb' => array(
                array('label' => 'Pesan', 'url' => site_url('Chat')),
                array('label' => 'Detail Siaran', 'active' => true),
            ),
            'sidebar_menu' => generate_sidebar_menu($role),
            'conversation' => $conv,
            'first_message' => $first_message,
            'receipts' => $receipts,
        );
        $data['content'] = $this->load->view('chat/broadcast_detail', $data, true);
        $this->load->view('layouts/main', $data);
    }

    public function mark_read($message_id) {
        $user_id = $this->current_user_id();
        $msg = $this->Chat_model->get_message($message_id);
        if (!$msg) {
            if ($this->input->is_ajax_request()) {
                $this->output->set_content_type('application/json')->set_output(json_encode(array('success' => false)));
                return;
            }
            redirect('Chat');
        }

        $this->Chat_model->mark_message_read($message_id, $user_id);

        if ($this->input->is_ajax_request()) {
            $this->output->set_content_type('application/json')->set_output(json_encode(array(
                'success' => true,
                'thread_url' => site_url('Chat/thread/' . $msg->id_conversation),
            )));
            return;
        }

        redirect('Chat/thread/' . $msg->id_conversation);
    }

    public function unread_count() {
        $count = $this->Chat_model->count_unread($this->current_user_id());
        $this->output->set_content_type('application/json')->set_output(json_encode(array(
            'success' => true,
            'count' => $count,
        )));
    }

    public function priority_pending() {
        $items = $this->Chat_model->get_unread_priority_messages($this->current_user_id(), 10);
        $data = array();
        foreach ($items as $item) {
            $data[] = array(
                'id' => (int) $item->id,
                'body' => $item->body,
                'sender' => chat_display_name($item->sender_user_id),
                'created_at' => date('d/m/Y H:i', strtotime($item->created_at)),
                'thread_url' => site_url('Chat/thread/' . $item->id_conversation),
            );
        }
        $this->output->set_content_type('application/json')->set_output(json_encode(array(
            'success' => true,
            'items' => $data,
        )));
    }
}
