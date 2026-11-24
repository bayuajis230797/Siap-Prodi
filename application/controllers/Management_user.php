<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Management User Controller
 * Mengelola user account (hanya untuk Admin)
 */
class Management_user extends MY_Controller {

    public function __construct() {
        parent::__construct();
        $this->check_role('admin');
        $this->load->model('User_model');
        $this->load->model('Dosen_model');
        $this->load->model('Mahasiswa_model');
        $this->load->model('Tendik_model');
    }

    /**
     * List semua user dengan pagination
     */
    public function index() {
        $this->load->library('pagination');
        
        // Ambil parameter per_page dari GET, default 10
        $per_page = $this->input->get('per_page') ? (int)$this->input->get('per_page') : 10;
        $per_page = in_array($per_page, [10, 25, 50, 100]) ? $per_page : 10;
        
        // Pagination config
        $config['base_url'] = site_url('Management_user/index');
        $config['total_rows'] = $this->User_model->count_all();
        $config['per_page'] = $per_page;
        $config['uri_segment'] = 3;
        
        // Tambahkan per_page ke query string
        if ($per_page != 10) {
            $config['suffix'] = '?per_page=' . $per_page;
            $config['first_url'] = site_url('Management_user/index?per_page=' . $per_page);
        }
        $config['use_page_numbers'] = TRUE;
        $config['first_link'] = '&laquo; Awal';
        $config['last_link'] = 'Akhir &raquo;';
        $config['next_link'] = '&gt;';
        $config['prev_link'] = '&lt;';
        $config['full_tag_open'] = '<ul class="pagination pagination-sm m-0 float-right">';
        $config['full_tag_close'] = '</ul>';
        $config['first_tag_open'] = '<li class="page-item">';
        $config['first_tag_close'] = '</li>';
        $config['last_tag_open'] = '<li class="page-item">';
        $config['last_tag_close'] = '</li>';
        $config['next_tag_open'] = '<li class="page-item">';
        $config['next_tag_close'] = '</li>';
        $config['prev_tag_open'] = '<li class="page-item">';
        $config['prev_tag_close'] = '</li>';
        $config['cur_tag_open'] = '<li class="page-item active"><a class="page-link" href="#">';
        $config['cur_tag_close'] = '</a></li>';
        $config['num_tag_open'] = '<li class="page-item">';
        $config['num_tag_close'] = '</li>';
        $config['attributes'] = array('class' => 'page-link');
        
        $this->pagination->initialize($config);
        
        $page = ($this->uri->segment(3)) ? $this->uri->segment(3) : 1;
        $offset = ($page - 1) * $config['per_page'];
        
        $users = $this->User_model->get_all($config['per_page'], $offset);
        
        // Ambil daftar user yang sedang login (ada session aktif)
        $active_user_ids = $this->User_model->get_active_session_user_ids();
        
        // Ambil data referensi untuk setiap user dan cek status aktif
        foreach ($users as $user) {
            // Cek apakah user sedang login (ada di session aktif)
            $user->is_online = in_array($user->id, $active_user_ids);
            
            if ($user->ref_id) {
                $user->ref_data = $this->User_model->get_ref_data($user->nama_role, $user->ref_id);
                
                // Untuk mahasiswa, cek status aktif di sistem
                if ($user->nama_role == 'mahasiswa' && $user->ref_data) {
                    $user->is_mahasiswa_aktif = ($user->ref_data->status == 'aktif');
                } else {
                    $user->is_mahasiswa_aktif = false;
                }
            } else {
                $user->ref_data = null;
                $user->is_mahasiswa_aktif = false;
            }
        }
        
        $data['users'] = $users;
        $data['pagination'] = $this->pagination->create_links();
        $data['total_rows'] = $config['total_rows'];
        $data['per_page'] = $per_page;
        
        $data['title'] = 'Management User';
        $data['page_title'] = 'Management User';
        $data['breadcrumb'] = array(
            array('label' => 'Management User', 'active' => true)
        );
        $data['sidebar_menu'] = generate_sidebar_menu('admin');
        $data['content'] = $this->load->view('management_user/index', $data, true);
        $this->load->view('layouts/main', $data);
    }

    /**
     * Form tambah user
     */
    public function create() {
        if ($this->input->method() === 'post') {
            $this->form_validation->set_rules('username', 'Username', 'required|trim|is_unique[users.username]');
            $this->form_validation->set_rules('password', 'Password', 'required|trim|min_length[6]');
            $this->form_validation->set_rules('password_confirm', 'Konfirmasi Password', 'required|matches[password]');
            $this->form_validation->set_rules('role_id', 'Role', 'required|trim');
            $this->form_validation->set_rules('ref_id', 'Referensi ID', 'trim');

            if ($this->form_validation->run() == FALSE) {
                $data['roles'] = $this->User_model->get_all_roles();
                $data['title'] = 'Tambah User';
                $data['page_title'] = 'Tambah User';
                $data['breadcrumb'] = array(
                    array('label' => 'Management User', 'url' => site_url('Management_user')),
                    array('label' => 'Tambah', 'active' => true)
                );
                $data['sidebar_menu'] = generate_sidebar_menu('admin');
                $data['content'] = $this->load->view('management_user/create', $data, true);
                $this->load->view('layouts/main', $data);
            } else {
                $data_user = array(
                    'username' => $this->input->post('username'),
                    'password' => $this->input->post('password'),
                    'role_id' => $this->input->post('role_id'),
                    'ref_id' => $this->input->post('ref_id') ? $this->input->post('ref_id') : null
                );

                // Insert user
                $user_id = $this->User_model->create($data_user);
                
                if ($user_id) {
                    $this->session->set_flashdata('success', 'User berhasil ditambahkan.');
                    redirect('Management_user');
                } else {
                    $this->session->set_flashdata('error', 'Gagal menambahkan user.');
                    redirect('Management_user/create');
                }
            }
        } else {
            $data['roles'] = $this->User_model->get_all_roles();
            $data['title'] = 'Tambah User';
            $data['page_title'] = 'Tambah User';
            $data['breadcrumb'] = array(
                array('label' => 'Management User', 'url' => site_url('Management_user')),
                array('label' => 'Tambah', 'active' => true)
            );
            $data['sidebar_menu'] = generate_sidebar_menu('admin');
            $data['content'] = $this->load->view('management_user/create', $data, true);
            $this->load->view('layouts/main', $data);
        }
    }

    /**
     * Form edit user
     */
    public function edit($id) {
        $user = $this->User_model->get_user_by_id($id);
        
        if (!$user) {
            $this->session->set_flashdata('error', 'User tidak ditemukan.');
            redirect('Management_user');
        }

        if ($this->input->method() === 'post') {
            // Validasi username unik (kecuali untuk user yang sedang diedit)
            $username_unique = '';
            if ($this->input->post('username') != $user->username) {
                $username_unique = '|is_unique[users.username]';
            }

            $this->form_validation->set_rules('username', 'Username', 'required|trim' . $username_unique);
            $this->form_validation->set_rules('password', 'Password', 'trim|min_length[6]');
            $this->form_validation->set_rules('password_confirm', 'Konfirmasi Password', 'trim|matches[password]');
            $this->form_validation->set_rules('role_id', 'Role', 'required|trim');
            $this->form_validation->set_rules('ref_id', 'Referensi ID', 'trim');

            if ($this->form_validation->run() == FALSE) {
                $data['user'] = $user;
                $data['roles'] = $this->User_model->get_all_roles();
                $data['title'] = 'Edit User';
                $data['page_title'] = 'Edit User';
                $data['breadcrumb'] = array(
                    array('label' => 'Management User', 'url' => site_url('Management_user')),
                    array('label' => 'Edit', 'active' => true)
                );
                $data['sidebar_menu'] = generate_sidebar_menu('admin');
                $data['content'] = $this->load->view('management_user/edit', $data, true);
                $this->load->view('layouts/main', $data);
            } else {
                $data_user = array(
                    'username' => $this->input->post('username'),
                    'role_id' => $this->input->post('role_id'),
                    'ref_id' => $this->input->post('ref_id') ? $this->input->post('ref_id') : null
                );

                // Update password hanya jika diisi
                if ($this->input->post('password')) {
                    $data_user['password'] = $this->input->post('password');
                }

                // Update user
                if ($this->User_model->update($id, $data_user)) {
                    $this->session->set_flashdata('success', 'User berhasil diupdate.');
                    redirect('Management_user');
                } else {
                    $this->session->set_flashdata('error', 'Gagal mengupdate user.');
                    redirect('Management_user/edit/' . $id);
                }
            }
        } else {
            // Ambil data referensi jika ada
            if ($user->ref_id) {
                $user->ref_data = $this->User_model->get_ref_data($user->nama_role, $user->ref_id);
            } else {
                $user->ref_data = null;
            }

            $data['user'] = $user;
            $data['roles'] = $this->User_model->get_all_roles();
            $data['title'] = 'Edit User';
            $data['page_title'] = 'Edit User';
            $data['breadcrumb'] = array(
                array('label' => 'Management User', 'url' => site_url('Management_user')),
                array('label' => 'Edit', 'active' => true)
            );
            $data['sidebar_menu'] = generate_sidebar_menu('admin');
            $data['content'] = $this->load->view('management_user/edit', $data, true);
            $this->load->view('layouts/main', $data);
        }
    }

    /**
     * Reset password user ke default (sama dengan username)
     */
    public function reset_password($id) {
        if ($this->input->method() !== 'post') {
            show_404();
        }

        $user = $this->User_model->get_user_by_id($id);

        if (!$user) {
            $this->session->set_flashdata('error', 'User tidak ditemukan.');
            redirect('Management_user');
        }

        if ($this->User_model->reset_password_to_username($id)) {
            $this->session->set_flashdata('success', 'Password user "' . $user->username . '" berhasil direset. Password baru sama dengan username.');
        } else {
            $this->session->set_flashdata('error', 'Gagal mereset password user.');
        }

        redirect('Management_user');
    }

    /**
     * Hapus user
     */
    public function delete($id) {
        // Jangan izinkan hapus user sendiri
        if ($id == $this->session->userdata('user_id')) {
            $this->session->set_flashdata('error', 'Anda tidak dapat menghapus akun sendiri.');
            redirect('Management_user');
        }

        $user = $this->User_model->get_user_by_id($id);
        
        if (!$user) {
            $this->session->set_flashdata('error', 'User tidak ditemukan.');
            redirect('Management_user');
        }

        if ($this->User_model->delete($id)) {
            $this->session->set_flashdata('success', 'User berhasil dihapus.');
        } else {
            $this->session->set_flashdata('error', 'Gagal menghapus user.');
        }
        
        redirect('Management_user');
    }

    /**
     * AJAX: Ambil daftar referensi berdasarkan role
     */
    public function get_ref_list() {
        $role_id = $this->input->post('role_id');
        
        if (!$role_id) {
            echo json_encode(array('success' => false, 'message' => 'Role ID tidak valid.'));
            return;
        }

        // Ambil nama role
        $this->load->model('User_model');
        $roles = $this->User_model->get_all_roles();
        $role_name = '';
        foreach ($roles as $role) {
            if ($role->id == $role_id) {
                $role_name = $role->nama_role;
                break;
            }
        }

        $ref_list = array();
        
        switch ($role_name) {
            case 'dosen':
                $dosen_list = $this->Dosen_model->get_all(1000, 0); // Ambil semua
                foreach ($dosen_list as $dosen) {
                    $ref_list[] = array(
                        'id' => $dosen->id,
                        'text' => $dosen->nidn . ' - ' . $dosen->nama_dosen
                    );
                }
                break;
            case 'mahasiswa':
                $mahasiswa_list = $this->Mahasiswa_model->get_all(1000, 0); // Ambil semua
                foreach ($mahasiswa_list as $mahasiswa) {
                    $ref_list[] = array(
                        'id' => $mahasiswa->id,
                        'text' => $mahasiswa->nim . ' - ' . $mahasiswa->nama_mahasiswa
                    );
                }
                break;
            case 'tendik':
                $tendik_list = $this->Tendik_model->get_all(1000, 0); // Ambil semua
                foreach ($tendik_list as $tendik) {
                    $ref_list[] = array(
                        'id' => $tendik->id,
                        'text' => $tendik->nipy . ' - ' . $tendik->nama_tendik
                    );
                }
                break;
        }

        echo json_encode(array('success' => true, 'data' => $ref_list));
    }

    /**
     * Admin pindah login sebagai user tertentu (impersonate).
     */
    public function pindah_login($id) {
        if ($this->input->method() !== 'post') {
            show_404();
        }

        $target_id = (int) $id;
        $admin_user_id = (int) $this->session->userdata('user_id');

        if ($target_id <= 0) {
            $this->session->set_flashdata('error', 'User tujuan tidak valid.');
            redirect('Management_user');
        }

        if ($target_id === $admin_user_id) {
            $this->session->set_flashdata('error', 'Tidak bisa pindah login ke akun sendiri.');
            redirect('Management_user');
        }

        $target_user = $this->User_model->get_user_by_id($target_id);
        if (!$target_user) {
            $this->session->set_flashdata('error', 'User tujuan tidak ditemukan.');
            redirect('Management_user');
        }

        // Simpan jejak admin asal untuk audit / restore manual di masa depan.
        $impersonator = array(
            'user_id' => $admin_user_id,
            'username' => $this->session->userdata('username'),
            'role' => $this->session->userdata('role'),
            'role_id' => $this->session->userdata('role_id'),
            'ref_id' => $this->session->userdata('ref_id'),
            'nama' => $this->session->userdata('nama'),
        );

        // Session fixation protection
        $this->session->sess_regenerate(TRUE);

        $session_data = array(
            'user_id' => (int) $target_user->id,
            'username' => $target_user->username,
            'role' => $target_user->nama_role,
            'role_id' => (int) $target_user->role_id,
            'ref_id' => $target_user->ref_id,
            'is_impersonating' => true,
            'impersonator' => $impersonator,
        );

        // Ambil data profil sesuai role target (sama konsep Auth::login)
        if ($target_user->nama_role === 'dosen' && $target_user->ref_id) {
            $dosen = $this->User_model->get_dosen_by_ref($target_user->ref_id);
            $session_data['nama'] = $dosen ? $dosen->nama_dosen : $target_user->username;
            if ($dosen && isset($dosen->nidn)) {
                $session_data['nidn'] = $dosen->nidn;
            }
        } elseif ($target_user->nama_role === 'mahasiswa' && $target_user->ref_id) {
            $mahasiswa = $this->User_model->get_mahasiswa_by_ref($target_user->ref_id);
            $session_data['nama'] = $mahasiswa ? $mahasiswa->nama_mahasiswa : $target_user->username;
            if ($mahasiswa && isset($mahasiswa->nim)) {
                $session_data['nim'] = $mahasiswa->nim;
            }
        } elseif ($target_user->nama_role === 'tendik' && $target_user->ref_id) {
            $tendik = $this->User_model->get_tendik_by_ref($target_user->ref_id);
            $session_data['nama'] = $tendik ? $tendik->nama_tendik : $target_user->username;
            if ($tendik && isset($tendik->nipy)) {
                $session_data['nipy'] = $tendik->nipy;
            }
        } else {
            $session_data['nama'] = $target_user->username;
        }

        $this->session->set_userdata($session_data);
        $this->User_model->update_last_login((int) $target_user->id);

        $this->session->set_flashdata('success', 'Pindah login berhasil ke user: ' . $target_user->username . '.');
        redirect('Halaman_utama');
    }
}







