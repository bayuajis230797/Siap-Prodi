<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Notifikasi extends MY_Controller {

    public function __construct() {
        parent::__construct();
        $this->load->model('Notifikasi_model');
    }

    public function mark_read($id) {
        $user_id = $this->session->userdata('user_id');
        $role = $this->session->userdata('role');
        
        if (!$user_id) {
            show_404();
        }
        
        $notifikasi = $this->Notifikasi_model->get_by_id($id);
        if (!$notifikasi || $notifikasi->id_user != $user_id || $notifikasi->role != $role) {
            show_404();
        }
        
        $this->Notifikasi_model->mark_as_read($id);
        $this->session->set_flashdata('success', 'Notifikasi ditandai sudah dibaca.');
        
        if ($notifikasi->link) {
            redirect($notifikasi->link);
        } else {
            redirect('Dashboard');
        }
    }

    public function mark_all_read() {
        $user_id = $this->session->userdata('user_id');
        $role = $this->session->userdata('role');
        
        if (!$user_id) {
            show_404();
        }
        
        $this->Notifikasi_model->mark_all_as_read($user_id, $role);
        $this->session->set_flashdata('success', 'Semua notifikasi ditandai sudah dibaca.');
        redirect('Dashboard');
    }

    /**
     * Polling notifikasi baru (JSON) — dipakai popup di semua role.
     * GET after_id = id terakhir yang sudah diketahui browser.
     */
    public function poll() {
        $user_id = $this->session->userdata('user_id');
        $role = $this->session->userdata('role');
        if (!$user_id || !$role) {
            $this->output->set_status_header(401)->set_content_type('application/json')
                ->set_output(json_encode(array('success' => false)));
            return;
        }

        $after_id = (int) $this->input->get('after_id');
        $items = $this->Notifikasi_model->get_unread_after($user_id, $role, $after_id, 8);
        $unread = (int) $this->Notifikasi_model->count_unread($user_id, $role);
        $max_id = $this->Notifikasi_model->get_max_id($user_id, $role);

        $out = array();
        foreach ($items as $n) {
            $out[] = array(
                'id' => (int) $n->id,
                'judul' => $n->judul,
                'pesan' => $n->pesan,
                'link' => $n->link ? site_url('Notifikasi/mark_read/' . (int) $n->id) : site_url('Notifikasi/mark_read/' . (int) $n->id),
                'created_at' => $n->created_at,
            );
        }

        $this->output->set_content_type('application/json')->set_output(json_encode(array(
            'success' => true,
            'unread' => $unread,
            'max_id' => $max_id,
            'items' => $out,
        )));
    }
}



