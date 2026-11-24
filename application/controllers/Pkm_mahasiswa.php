<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Pkm_mahasiswa extends MY_Controller {

    public function __construct() {
        parent::__construct();
        $this->check_role('mahasiswa');
        $this->load->model('Pkm_periode_model');
        $this->load->model('Pkm_jenis_lomba_model');
        $this->load->model('Pkm_pengajuan_model');
        $this->load->model('Pkm_anggota_model');
        $this->load->model('Pkm_perubahan_judul_model');
        $this->load->model('Dosen_model');
        $this->load->helper(array('menu', 'pkm', 'google_drive', 'id_encode'));
        $this->load->library('form_validation');
    }

    public function index() {
        $id_mahasiswa = (int) $this->session->userdata('ref_id');
        $periode_list = $this->Pkm_periode_model->get_active_periode();
        $pengajuan_tim = $this->Pkm_pengajuan_model->get_by_mahasiswa($id_mahasiswa);
        foreach ($pengajuan_tim as $pg) {
            $pg->anggota = $this->Pkm_anggota_model->get_by_pengajuan($pg->id);
        }

        foreach ($periode_list as $p) {
            $p->pengajuan_ketua = null;
            foreach ($pengajuan_tim as $pg) {
                if ((int) $pg->id_pkm_periode === (int) $p->id && $pg->peran === 'ketua') {
                    $p->pengajuan_ketua = $pg;
                    break;
                }
            }
        }

        $data = array(
            'title' => 'PKM',
            'page_title' => 'Pekan Kreativitas Mahasiswa',
            'breadcrumb' => array(array('label' => 'PKM', 'active' => true)),
            'sidebar_menu' => generate_sidebar_menu('mahasiswa'),
            'periode_list' => $periode_list,
            'pengajuan_tim' => $pengajuan_tim,
        );
        $data['content'] = $this->load->view('mahasiswa/pkm/index', $data, true);
        $this->load->view('layouts/main', $data);
    }

    public function ajukan($id_periode = null) {
        $id_mahasiswa = (int) $this->session->userdata('ref_id');
        if ($id_periode === null || $id_periode === '') {
            $id_periode = $this->input->get('id_periode');
        }
        $id_periode = pkm_resolve_id($id_periode);
        if ($id_periode <= 0) {
            $this->session->set_flashdata('error', 'Periode PKM tidak valid.');
            redirect('Pkm_mahasiswa');
        }
        $seg = $this->uri->segment(3);
        if ($seg !== null && $seg !== '' && ctype_digit((string) $seg)) {
            redirect('Pkm_mahasiswa/ajukan/' . pkm_encoded_id($id_periode));
        }
        $periode = $this->Pkm_periode_model->get_by_id($id_periode);
        if (!$periode || !$this->Pkm_periode_model->is_pengajuan_allowed($periode)) {
            $this->session->set_flashdata('error', 'Periode PKM tidak aktif atau deadline sudah lewat.');
            redirect('Pkm_mahasiswa');
        }

        $existing = $this->Pkm_pengajuan_model->get_by_ketua_periode($id_mahasiswa, $id_periode);
        if ($existing && $existing->status_verifikasi_sekprodi !== 'ditolak') {
            $this->session->set_flashdata('error', 'Anda sudah mengajukan PKM pada periode ini.');
            redirect('Pkm_mahasiswa/detail/' . pkm_encoded_id($existing->id));
        }

        if ($this->Pkm_anggota_model->count_anggota_per_periode($id_mahasiswa, $id_periode) >= 1) {
            $this->session->set_flashdata('error', 'Anda sudah terdaftar sebagai anggota tim PKM lain pada periode ini. Tidak dapat mengajuan sebagai ketua.');
            redirect('Pkm_mahasiswa');
        }

        if ($this->Pkm_anggota_model->count_ketua_per_periode($id_mahasiswa, $id_periode, $existing ? $existing->id : null) >= 1) {
            $this->session->set_flashdata('error', 'Anda hanya boleh menjadi ketua PKM 1 kali per periode.');
            redirect('Pkm_mahasiswa');
        }

        $jenis_list = $this->Pkm_jenis_lomba_model->get_by_periode($id_periode, true);
        $dosen_list = $this->Dosen_model->get_all_active();

        $data = array(
            'title' => 'Ajukan PKM',
            'page_title' => 'Pengajuan Judul PKM',
            'breadcrumb' => array(
                array('label' => 'PKM', 'url' => site_url('Pkm_mahasiswa')),
                array('label' => 'Ajukan', 'active' => true),
            ),
            'sidebar_menu' => generate_sidebar_menu('mahasiswa'),
            'periode' => $periode,
            'jenis_list' => $jenis_list,
            'dosen_list' => $dosen_list,
            'pengajuan' => $existing,
        );
        $data['content'] = $this->load->view('mahasiswa/pkm/form_ajuan', $data, true);
        $this->load->view('layouts/main', $data);
    }

    public function simpan() {
        if ($this->input->method() !== 'post') {
            show_404();
        }
        $id_mahasiswa = (int) $this->session->userdata('ref_id');
        $id_periode = pkm_resolve_id($this->input->post('id_pkm_periode'));
        if ($id_periode <= 0) {
            $this->session->set_flashdata('error', 'Periode tidak valid.');
            redirect('Pkm_mahasiswa');
        }
        $periode = $this->Pkm_periode_model->get_by_id($id_periode);
        if (!$periode || !$this->Pkm_periode_model->is_pengajuan_allowed($periode)) {
            $this->session->set_flashdata('error', 'Periode tidak valid.');
            redirect('Pkm_mahasiswa');
        }

        $id_jenis = (int) $this->input->post('id_pkm_jenis');
        $jenis = $this->Pkm_jenis_lomba_model->get_by_id($id_jenis);
        if (!$jenis || (int) $jenis->id_pkm_periode !== $id_periode) {
            $this->session->set_flashdata('error', 'Jenis lomba tidak valid.');
            redirect('Pkm_mahasiswa/ajukan/' . pkm_encoded_id($id_periode));
        }

        $existing = $this->Pkm_pengajuan_model->get_by_ketua_periode($id_mahasiswa, $id_periode);
        if ($existing && $existing->status_verifikasi_sekprodi !== 'ditolak') {
            $this->session->set_flashdata('error', 'Pengajuan sudah ada.');
            redirect('Pkm_mahasiswa/detail/' . pkm_encoded_id($existing->id));
        }

        if ($this->Pkm_anggota_model->count_anggota_per_periode($id_mahasiswa, $id_periode) >= 1) {
            $this->session->set_flashdata('error', 'Anda sudah terdaftar sebagai anggota tim PKM lain pada periode ini.');
            redirect('Pkm_mahasiswa/ajukan/' . pkm_encoded_id($id_periode));
        }

        if ($this->Pkm_anggota_model->count_ketua_per_periode($id_mahasiswa, $id_periode, $existing ? $existing->id : null) >= 1) {
            $this->session->set_flashdata('error', 'Anda hanya boleh menjadi ketua PKM 1 kali per periode.');
            redirect('Pkm_mahasiswa/ajukan/' . pkm_encoded_id($id_periode));
        }

        $judul = trim($this->input->post('judul_pkm'));
        $id_pembimbing = (int) $this->input->post('id_pembimbing_request');
        if ($judul === '' || $id_pembimbing <= 0) {
            $this->session->set_flashdata('error', 'Judul dan dosen pembimbing wajib diisi.');
            redirect('Pkm_mahasiswa/ajukan/' . pkm_encoded_id($id_periode));
        }

        $this->load->helper(array('google_drive', 'pkm'));
        $link_proposal = ($existing && !empty($existing->link_proposal_awal)) ? $existing->link_proposal_awal : null;
        if (!empty($_FILES['file_proposal_awal']['name'])) {
            require_google_drive_or_redirect();
            $this->load->library('Upload_to_drive');
            $share_emails = array();
            $email = $this->upload_to_drive->get_drive_email_by_role_ref('dosen', $id_pembimbing);
            if ($email) {
                $share_emails[] = $email;
            }
            $upload = $this->upload_to_drive->handle_upload('file_proposal_awal', $this->upload_to_drive->default_options(
                array('SIAP', 'PKM', 'proposal_awal'),
                $share_emails
            ));
            if (empty($upload['ok'])) {
                $this->session->set_flashdata('error', isset($upload['message']) ? $upload['message'] : 'Gagal upload proposal.');
                redirect('Pkm_mahasiswa/ajukan/' . pkm_encoded_id($id_periode));
            }
            $link_proposal = pkm_encrypt_link($upload['webViewLink']);
        }

        $exclude_pengajuan = ($existing && $existing->status_verifikasi_sekprodi === 'ditolak') ? (int) $existing->id : null;
        $anggota_rows = $this->_parse_anggota_post($id_mahasiswa, $id_periode, $jenis, $exclude_pengajuan);
        if ($anggota_rows === false) {
            redirect('Pkm_mahasiswa/ajukan/' . pkm_encoded_id($id_periode));
        }

        if ($existing && $existing->status_verifikasi_sekprodi === 'ditolak') {
            $id_pengajuan = $existing->id;
            $this->Pkm_pengajuan_model->update($id_pengajuan, array(
                'id_pkm_jenis' => $id_jenis,
                'judul_pkm' => $judul,
                'id_pembimbing_request' => $id_pembimbing,
                'link_proposal_awal' => $link_proposal ?: null,
                'status_verifikasi_sekprodi' => 'pending',
                'status_acc_judul' => 'pending',
                'status_acc_proposal' => 'pending',
                'catatan_sekprodi' => null,
                'id_pembimbing_final' => null,
            ));
        } else {
            $id_pengajuan = $this->Pkm_pengajuan_model->create(array(
                'id_pkm_periode' => $id_periode,
                'id_pkm_jenis' => $id_jenis,
                'id_ketua' => $id_mahasiswa,
                'judul_pkm' => $judul,
                'id_pembimbing_request' => $id_pembimbing,
                'link_proposal_awal' => $link_proposal ?: null,
                'status_verifikasi_sekprodi' => 'pending',
            ));
        }

        if (!$id_pengajuan) {
            $this->session->set_flashdata('error', 'Gagal menyimpan pengajuan.');
            redirect('Pkm_mahasiswa/ajukan/' . pkm_encoded_id($id_periode));
        }

        $this->Pkm_anggota_model->sync_anggota($id_pengajuan, $anggota_rows);
        $this->load->helper(array('notifikasi', 'sekprodi_badge'));
        notify_sekprodi(
            'Ajuan PKM Baru',
            'Ada pengajuan PKM baru yang menunggu verifikasi.',
            site_url('Pkm_verifikasi'),
            'pkm_verifikasi'
        );
        $this->session->set_flashdata('success', 'Pengajuan PKM berhasil dikirim. Menunggu verifikasi sekprodi.');
        redirect('Pkm_mahasiswa/detail/' . pkm_encoded_id($id_pengajuan));
    }

    public function detail($id) {
        $id = pkm_resolve_id($id);
        if ($id <= 0) {
            show_404();
        }
        $seg = $this->uri->segment(3);
        if ($seg !== null && $seg !== '' && ctype_digit((string) $seg)) {
            redirect('Pkm_mahasiswa/detail/' . pkm_encoded_id($id));
        }
        $id_mahasiswa = (int) $this->session->userdata('ref_id');
        $pengajuan = $this->Pkm_pengajuan_model->mahasiswa_akses_pengajuan($id_mahasiswa, $id);
        if (!$pengajuan) {
            show_404();
        }
        $is_ketua = ($pengajuan->peran === 'ketua');
        $anggota = $this->Pkm_anggota_model->get_by_pengajuan($id);
        $perubahan = $is_ketua ? $this->Pkm_perubahan_judul_model->get_by_pengajuan($id) : array();

        $data = array(
            'title' => 'Detail PKM',
            'page_title' => 'Detail Pengajuan PKM',
            'breadcrumb' => array(
                array('label' => 'PKM', 'url' => site_url('Pkm_mahasiswa')),
                array('label' => 'Detail', 'active' => true),
            ),
            'sidebar_menu' => generate_sidebar_menu('mahasiswa'),
            'pengajuan' => $pengajuan,
            'anggota' => $anggota,
            'perubahan_list' => $perubahan,
            'bimbingan_aktif' => pkm_bimbingan_aktif($pengajuan),
            'has_pending_perubahan' => $is_ketua ? $this->Pkm_perubahan_judul_model->has_pending($id) : false,
            'is_ketua' => $is_ketua,
        );
        $data['content'] = $this->load->view('mahasiswa/pkm/detail', $data, true);
        $this->load->view('layouts/main', $data);
    }

    public function cari_mahasiswa() {
        $this->load->helper('id_encode');
        $q = trim($this->input->get('q'));
        $id_periode = (int) $this->input->get('id_periode');
        if (strlen($q) < 2) {
            echo json_encode(array());
            return;
        }
        $this->db->select('id, nim, nama_mahasiswa, kelas');
        $this->db->from('mahasiswa');
        $this->db->where('status', 'aktif');
        $this->db->group_start();
        $this->db->like('nim', $q);
        $this->db->or_like('nama_mahasiswa', $q);
        $this->db->group_end();
        $this->db->limit(20);
        $rows = $this->db->get()->result();
        $out = array();
        foreach ($rows as $r) {
            $mid = (int) $r->id;
            if ($id_periode > 0) {
                if ($this->Pkm_anggota_model->count_ketua_per_periode($mid, $id_periode) >= 1) {
                    continue;
                }
                if ($this->Pkm_anggota_model->count_anggota_per_periode($mid, $id_periode) >= 1) {
                    continue;
                }
            }
            $out[] = array(
                'token' => encode_id($mid),
                'nim' => $r->nim,
                'nama' => $r->nama_mahasiswa,
                'kelas' => $r->kelas,
                'label' => $r->nim . ' — ' . $r->nama_mahasiswa . ' (' . $r->kelas . ')',
            );
            if (count($out) >= 15) {
                break;
            }
        }
        header('Content-Type: application/json');
        echo json_encode($out);
    }

    private function _parse_anggota_post($id_ketua, $id_periode, $jenis, $exclude_pengajuan_id = null) {
        $this->load->helper('pkm');
        $this->load->helper('id_encode');
        $tipes = $this->input->post('anggota_tipe');
        $id_mhs = $this->input->post('anggota_id_mahasiswa');
        $nama_manual = $this->input->post('anggota_nama_manual');
        $nim_manual = $this->input->post('anggota_nim_manual');
        $prodi_manual = $this->input->post('anggota_prodi_asal');

        if (!is_array($tipes)) {
            $this->session->set_flashdata('error', 'Tambahkan minimal ' . (int) $jenis->min_anggota . ' anggota tim.');
            return false;
        }

        $rows = array();
        $urutan = 1;
        $seen_mhs = array();

        foreach ($tipes as $i => $tipe) {
            $i = (string) $i;
            $tipe = $tipe === 'external' ? 'external' : 'internal';
            if ($tipe === 'internal') {
                $raw = isset($id_mhs[$i]) ? trim($id_mhs[$i]) : '';
                $mid = decode_id($raw);
                if ($mid === false) {
                    $mid = (int) $raw;
                }
                if ($mid <= 0) {
                    $this->session->set_flashdata('error', 'Pilih mahasiswa dari daftar untuk anggota prodi S1 Sistem Informasi.');
                    return false;
                }
                if ($mid === $id_ketua) {
                    $this->session->set_flashdata('error', 'Ketua tidak boleh didaftarkan sebagai anggota.');
                    return false;
                }
                if (in_array($mid, $seen_mhs, true)) {
                    $this->session->set_flashdata('error', 'Mahasiswa duplikat dalam tim.');
                    return false;
                }
                if ($this->Pkm_anggota_model->count_ketua_per_periode($mid, $id_periode, $exclude_pengajuan_id) >= 1) {
                    $this->session->set_flashdata('error', 'Mahasiswa tersebut sudah menjadi ketua tim PKM lain pada periode ini.');
                    return false;
                }
                if ($this->Pkm_anggota_model->count_anggota_per_periode($mid, $id_periode, $exclude_pengajuan_id) >= 1) {
                    $this->session->set_flashdata('error', 'Mahasiswa tersebut sudah menjadi anggota tim PKM lain pada periode ini.');
                    return false;
                }
                $seen_mhs[] = $mid;
                $rows[] = array(
                    'urutan' => $urutan++,
                    'tipe' => 'internal',
                    'id_mahasiswa' => $mid,
                    'nama_manual' => null,
                    'nim_manual' => null,
                    'prodi_asal' => pkm_prodi_default(),
                );
            } else {
                $nama = isset($nama_manual[$i]) ? trim($nama_manual[$i]) : '';
                $nim = isset($nim_manual[$i]) ? trim($nim_manual[$i]) : '';
                $prodi = isset($prodi_manual[$i]) ? trim($prodi_manual[$i]) : '';
                if ($nama === '' || $nim === '' || $prodi === '') {
                    $this->session->set_flashdata('error', 'Anggota manual: nama, NIM, dan prodi asal wajib diisi.');
                    return false;
                }
                $rows[] = array(
                    'urutan' => $urutan++,
                    'tipe' => 'external',
                    'id_mahasiswa' => null,
                    'nama_manual' => $nama,
                    'nim_manual' => $nim,
                    'prodi_asal' => $prodi,
                );
            }
        }

        $count = count($rows);
        if ($count < (int) $jenis->min_anggota || $count > (int) $jenis->max_anggota) {
            $this->session->set_flashdata('error', 'Jumlah anggota harus ' . (int) $jenis->min_anggota . '–' . (int) $jenis->max_anggota . ' (saat ini: ' . $count . ').');
            return false;
        }
        return $rows;
    }
}
