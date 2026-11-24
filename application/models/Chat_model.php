<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Chat_model extends CI_Model {

    public function get_conversation($id) {
        $this->db->where('id', (int) $id);
        return $this->db->get('chat_conversations')->row();
    }

    public function is_participant($id_conversation, $id_user) {
        $this->db->where('id_conversation', (int) $id_conversation);
        $this->db->where('id_user', (int) $id_user);
        return $this->db->count_all_results('chat_conversation_participants') > 0;
    }

    public function get_participants($id_conversation) {
        $this->db->select('p.*, u.username, u.ref_id, r.nama_role as role');
        $this->db->from('chat_conversation_participants p');
        $this->db->join('users u', 'u.id = p.id_user', 'inner');
        $this->db->join('roles r', 'r.id = u.role_id', 'left');
        $this->db->where('p.id_conversation', (int) $id_conversation);
        return $this->db->get()->result();
    }

    public function find_direct_conversation($user_a, $user_b) {
        $user_a = (int) $user_a;
        $user_b = (int) $user_b;
        if ($user_a === $user_b) {
            return null;
        }

        $sql = "SELECT c.id FROM chat_conversations c
            INNER JOIN chat_conversation_participants p1 ON p1.id_conversation = c.id AND p1.id_user = ?
            INNER JOIN chat_conversation_participants p2 ON p2.id_conversation = c.id AND p2.id_user = ?
            WHERE c.type = 'direct'
            LIMIT 1";
        $row = $this->db->query($sql, array($user_a, $user_b))->row();
        return $row ? $this->get_conversation($row->id) : null;
    }

    public function create_conversation($type, $created_by_user_id, $subject = null) {
        $this->db->insert('chat_conversations', array(
            'type' => $type,
            'subject' => $subject,
            'created_by_user_id' => (int) $created_by_user_id,
        ));
        return (int) $this->db->insert_id();
    }

    public function add_participant($id_conversation, $id_user, $role) {
        $exists = $this->is_participant($id_conversation, $id_user);
        if ($exists) {
            return true;
        }
        return (bool) $this->db->insert('chat_conversation_participants', array(
            'id_conversation' => (int) $id_conversation,
            'id_user' => (int) $id_user,
            'role' => $role,
        ));
    }

    public function get_conversations_for_user($id_user, $limit = 10, $offset = 0) {
        $id_user = (int) $id_user;
        $limit = (int) $limit;
        $offset = (int) $offset;
        $sql = "SELECT c.*,
            (SELECT body FROM chat_messages m WHERE m.id_conversation = c.id ORDER BY m.id DESC LIMIT 1) AS last_message,
            (SELECT created_at FROM chat_messages m WHERE m.id_conversation = c.id ORDER BY m.id DESC LIMIT 1) AS last_message_at,
            (SELECT COUNT(*) FROM chat_message_receipts r
                INNER JOIN chat_messages m ON m.id = r.id_message
                WHERE m.id_conversation = c.id AND r.id_user = ? AND r.is_read = 0 AND m.sender_user_id != ?) AS unread_count
            FROM chat_conversations c
            INNER JOIN chat_conversation_participants p ON p.id_conversation = c.id
            WHERE p.id_user = ?
            ORDER BY COALESCE(
                (SELECT MAX(m2.created_at) FROM chat_messages m2 WHERE m2.id_conversation = c.id),
                c.created_at
            ) DESC
            LIMIT ?, ?";
        return $this->db->query($sql, array($id_user, $id_user, $id_user, $offset, $limit))->result();
    }

    public function count_conversations_for_user($id_user) {
        $id_user = (int) $id_user;
        $sql = "SELECT COUNT(DISTINCT c.id) AS cnt
            FROM chat_conversations c
            INNER JOIN chat_conversation_participants p ON p.id_conversation = c.id
            WHERE p.id_user = ?";
        $row = $this->db->query($sql, array($id_user))->row();
        return $row ? (int) $row->cnt : 0;
    }

    public function count_unread($id_user) {
        $id_user = (int) $id_user;
        $sql = "SELECT COUNT(*) AS cnt FROM chat_message_receipts r
            INNER JOIN chat_messages m ON m.id = r.id_message
            INNER JOIN chat_conversation_participants p ON p.id_conversation = m.id_conversation AND p.id_user = r.id_user
            WHERE r.id_user = ? AND r.is_read = 0 AND m.sender_user_id != ?";
        $row = $this->db->query($sql, array($id_user, $id_user))->row();
        return $row ? (int) $row->cnt : 0;
    }

    public function get_messages($id_conversation, $limit = 100) {
        $this->db->select('m.*, u.username, r.nama_role as sender_role, u.ref_id as sender_ref_id');
        $this->db->from('chat_messages m');
        $this->db->join('users u', 'u.id = m.sender_user_id', 'left');
        $this->db->join('roles r', 'r.id = u.role_id', 'left');
        $this->db->where('m.id_conversation', (int) $id_conversation);
        $this->db->order_by('m.id', 'ASC');
        $this->db->limit((int) $limit);
        return $this->db->get()->result();
    }

    public function get_message($id) {
        $this->db->where('id', (int) $id);
        return $this->db->get('chat_messages')->row();
    }

    public function send_message($id_conversation, $sender_user_id, $body, $is_priority = 0, $recipient_user_ids = array()) {
        $this->db->insert('chat_messages', array(
            'id_conversation' => (int) $id_conversation,
            'sender_user_id' => (int) $sender_user_id,
            'body' => $body,
            'is_priority' => $is_priority ? 1 : 0,
        ));
        $message_id = (int) $this->db->insert_id();
        if ($message_id <= 0) {
            return false;
        }

        if (empty($recipient_user_ids)) {
            $participants = $this->get_participants($id_conversation);
            foreach ($participants as $p) {
                if ((int) $p->id_user !== (int) $sender_user_id) {
                    $recipient_user_ids[] = (int) $p->id_user;
                }
            }
        }

        foreach ($recipient_user_ids as $uid) {
            $uid = (int) $uid;
            if ($uid <= 0 || $uid === (int) $sender_user_id) {
                continue;
            }
            $this->db->insert('chat_message_receipts', array(
                'id_message' => $message_id,
                'id_user' => $uid,
                'is_read' => 0,
            ));
        }

        return $message_id;
    }

    public function mark_conversation_read($id_conversation, $id_user) {
        $id_conversation = (int) $id_conversation;
        $id_user = (int) $id_user;
        $now = date('Y-m-d H:i:s');

        $sql = "UPDATE chat_message_receipts r
            INNER JOIN chat_messages m ON m.id = r.id_message
            SET r.is_read = 1, r.read_at = ?
            WHERE m.id_conversation = ? AND r.id_user = ? AND r.is_read = 0";
        $this->db->query($sql, array($now, $id_conversation, $id_user));

        $last = $this->db->select('id')->from('chat_messages')
            ->where('id_conversation', $id_conversation)
            ->order_by('id', 'DESC')->limit(1)->get()->row();

        $this->db->where('id_conversation', $id_conversation);
        $this->db->where('id_user', $id_user);
        $this->db->update('chat_conversation_participants', array(
            'last_read_at' => $now,
            'last_read_message_id' => $last ? (int) $last->id : null,
        ));

        return true;
    }

    public function mark_message_read($id_message, $id_user) {
        $id_message = (int) $id_message;
        $id_user = (int) $id_user;
        $msg = $this->get_message($id_message);
        if (!$msg || !$this->is_participant($msg->id_conversation, $id_user)) {
            return false;
        }

        $now = date('Y-m-d H:i:s');
        $this->db->where('id_message', $id_message);
        $this->db->where('id_user', $id_user);
        $this->db->update('chat_message_receipts', array(
            'is_read' => 1,
            'read_at' => $now,
        ));

        $this->db->where('id_conversation', $msg->id_conversation);
        $this->db->where('id_user', $id_user);
        $this->db->update('chat_conversation_participants', array(
            'last_read_at' => $now,
            'last_read_message_id' => $id_message,
        ));

        return true;
    }

    public function get_unread_priority_messages($id_user, $limit = 5) {
        $id_user = (int) $id_user;
        $sql = "SELECT m.*, c.subject, c.type, u.username AS sender_username, r.nama_role AS sender_role, u.ref_id AS sender_ref_id
            FROM chat_message_receipts rc
            INNER JOIN chat_messages m ON m.id = rc.id_message
            INNER JOIN chat_conversations c ON c.id = m.id_conversation
            INNER JOIN users u ON u.id = m.sender_user_id
            LEFT JOIN roles r ON r.id = u.role_id
            WHERE rc.id_user = ? AND rc.is_read = 0 AND m.is_priority = 1 AND m.sender_user_id != ?
            ORDER BY m.created_at ASC
            LIMIT ?";
        return $this->db->query($sql, array($id_user, $id_user, (int) $limit))->result();
    }

    public function get_message_read_stats($id_message) {
        $this->db->where('id_message', (int) $id_message);
        $total = $this->db->count_all_results('chat_message_receipts');

        $this->db->where('id_message', (int) $id_message);
        $this->db->where('is_read', 1);
        $read = $this->db->count_all_results('chat_message_receipts');

        return array('total' => $total, 'read' => $read);
    }

    public function get_receipts_for_message($id_message) {
        $this->db->select('r.*, u.username, ro.nama_role, u.ref_id');
        $this->db->from('chat_message_receipts r');
        $this->db->join('users u', 'u.id = r.id_user', 'left');
        $this->db->join('roles ro', 'ro.id = u.role_id', 'left');
        $this->db->where('r.id_message', (int) $id_message);
        return $this->db->get()->result();
    }

    public function get_read_status_for_sender($id_message, $recipient_user_id) {
        $this->db->where('id_message', (int) $id_message);
        $this->db->where('id_user', (int) $recipient_user_id);
        return $this->db->get('chat_message_receipts')->row();
    }

    /**
     * Semua percakapan (untuk pantau admin) — tidak terbatas peserta.
     */
    public function get_all_conversations($limit = 40, $offset = 0, $q = '') {
        $limit = (int) $limit;
        $offset = (int) $offset;
        $params = array();
        $where = '1=1';
        if ($q !== '') {
            $where .= ' AND (
                c.subject LIKE ? OR
                EXISTS (
                    SELECT 1 FROM chat_messages m0
                    WHERE m0.id_conversation = c.id AND m0.body LIKE ?
                ) OR
                EXISTS (
                    SELECT 1 FROM chat_conversation_participants p0
                    INNER JOIN users u0 ON u0.id = p0.id_user
                    WHERE p0.id_conversation = c.id AND u0.username LIKE ?
                )
            )';
            $like = '%' . $q . '%';
            $params[] = $like;
            $params[] = $like;
            $params[] = $like;
        }
        $params[] = $offset;
        $params[] = $limit;
        $sql = "SELECT c.*,
            (SELECT body FROM chat_messages m WHERE m.id_conversation = c.id ORDER BY m.id DESC LIMIT 1) AS last_message,
            (SELECT created_at FROM chat_messages m WHERE m.id_conversation = c.id ORDER BY m.id DESC LIMIT 1) AS last_message_at,
            0 AS unread_count,
            (SELECT COUNT(*) FROM chat_messages mx WHERE mx.id_conversation = c.id) AS message_count,
            (SELECT COUNT(*) FROM chat_conversation_participants px WHERE px.id_conversation = c.id) AS participant_count
            FROM chat_conversations c
            WHERE $where
            ORDER BY COALESCE(
                (SELECT MAX(m2.created_at) FROM chat_messages m2 WHERE m2.id_conversation = c.id),
                c.created_at
            ) DESC
            LIMIT ?, ?";
        return $this->db->query($sql, $params)->result();
    }

    public function count_all_conversations($q = '') {
        $params = array();
        $where = '1=1';
        if ($q !== '') {
            $where .= ' AND (
                c.subject LIKE ? OR
                EXISTS (
                    SELECT 1 FROM chat_messages m0
                    WHERE m0.id_conversation = c.id AND m0.body LIKE ?
                ) OR
                EXISTS (
                    SELECT 1 FROM chat_conversation_participants p0
                    INNER JOIN users u0 ON u0.id = p0.id_user
                    WHERE p0.id_conversation = c.id AND u0.username LIKE ?
                )
            )';
            $like = '%' . $q . '%';
            $params[] = $like;
            $params[] = $like;
            $params[] = $like;
        }
        $sql = "SELECT COUNT(*) AS cnt FROM chat_conversations c WHERE $where";
        $row = $this->db->query($sql, $params)->row();
        return $row ? (int) $row->cnt : 0;
    }
}
