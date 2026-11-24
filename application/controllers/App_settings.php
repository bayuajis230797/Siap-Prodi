<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * App_settings Controller
 * Mengelola setting aplikasi (logo, nama kampus, nama prodi, kop surat)
 */
class App_settings extends MY_Controller {

    public function __construct() {
        parent::__construct();
        $this->check_role('admin');
        $this->load->model('App_settings_model');
        $this->load->library('form_validation');
        $this->load->library('upload');
    }

    public function index() {
        $settings = $this->App_settings_model->get_settings();

        $data = array(
            'title' => 'Manajemen Aplikasi',
            'page_title' => 'Manajemen Aplikasi',
            'breadcrumb' => array(array('label' => 'Manajemen Aplikasi', 'active' => true)),
            'sidebar_menu' => generate_sidebar_menu('admin'),
            'settings' => $settings
        );

        $data['content'] = $this->load->view('admin/app_settings', $data, true);
        $this->load->view('layouts/main', $data);
    }

    public function update() {
        if ($this->input->method() !== 'post') {
            show_404();
        }

        $this->form_validation->set_rules('nama_kampus', 'Nama Kampus', 'required|trim|min_length[3]');
        $this->form_validation->set_rules('nama_prodi', 'Nama Program Studi', 'required|trim|min_length[3]');

        if ($this->form_validation->run() === FALSE) {
            $this->session->set_flashdata('error', validation_errors());
            redirect('App_settings');
        }

        $nama_kampus = $this->input->post('nama_kampus');
        $nama_prodi = $this->input->post('nama_prodi');

        $data = array(
            'nama_kampus' => trim($nama_kampus),
            'nama_prodi' => trim($nama_prodi)
        );

        // Upload logo jika ada
        if (!empty($_FILES['logo_kampus']['name'])) {
            $config['upload_path'] = './uploads/logo/';
            $config['allowed_types'] = 'jpg|jpeg|png|gif';
            $config['max_size'] = 2048; // 2MB
            $config['encrypt_name'] = TRUE;

            if (!is_dir($config['upload_path'])) {
                mkdir($config['upload_path'], 0755, true);
            }

            $this->upload->initialize($config);
            if ($this->upload->do_upload('logo_kampus')) {
                $upload_data = $this->upload->data();
                // Simpan path dengan forward slash untuk konsistensi
                $data['logo_kampus'] = 'uploads/logo/' . $upload_data['file_name'];
                
                // Hapus logo lama jika ada
                $settings = $this->App_settings_model->get_settings();
                if ($settings && $settings->logo_kampus) {
                    $old_logo_path = str_replace('\\', '/', $settings->logo_kampus);
                    $old_full_path = FCPATH . str_replace('/', DIRECTORY_SEPARATOR, $old_logo_path);
                    if (file_exists($old_full_path)) {
                        @unlink($old_full_path);
                    }
                }
            } else {
                $this->session->set_flashdata('error', 'Gagal upload logo: ' . $this->upload->display_errors('', ''));
                redirect('App_settings');
            }
        }

        // Upload kop surat jika ada
        if (!empty($_FILES['kop_surat']['name'])) {
            $config['upload_path'] = './uploads/kop_surat/';
            $config['allowed_types'] = 'jpg|jpeg';
            $config['max_size'] = 5120; // 5MB
            $config['overwrite'] = TRUE; // Overwrite file jika sudah ada
            $config['file_name'] = 'kop_surat.jpg'; // Nama file tetap dengan ekstensi

            if (!is_dir($config['upload_path'])) {
                mkdir($config['upload_path'], 0755, true);
            }

            $this->upload->initialize($config);
            if ($this->upload->do_upload('kop_surat')) {
                $upload_data = $this->upload->data();
                
                // Pastikan file sudah ada dan path benar
                $new_filename = 'kop_surat.jpg';
                $new_path = $config['upload_path'] . $new_filename;
                
                // Jika file terupload dengan nama berbeda, rename
                if (file_exists($upload_data['full_path']) && basename($upload_data['full_path']) !== $new_filename) {
                    @rename($upload_data['full_path'], $new_path);
                }
                
                // Simpan path dengan forward slash
                $data['kop_surat'] = 'uploads/kop_surat/' . $new_filename;
                
                // Hapus kop surat lama jika ada (dengan nama berbeda)
                $settings = $this->App_settings_model->get_settings();
                if ($settings && $settings->kop_surat) {
                    $old_kop_path = str_replace('\\', '/', $settings->kop_surat);
                    $old_full_path = FCPATH . str_replace('/', DIRECTORY_SEPARATOR, $old_kop_path);
                    if (file_exists($old_full_path) && basename($old_full_path) !== $new_filename) {
                        @unlink($old_full_path);
                    }
                }
            } else {
                $this->session->set_flashdata('error', 'Gagal upload kop surat: ' . $this->upload->display_errors('', ''));
                redirect('App_settings');
            }
        }

        if ($this->App_settings_model->upsert($data)) {
            $this->session->set_flashdata('success', 'Setting aplikasi berhasil diperbarui.');
        } else {
            $this->session->set_flashdata('error', 'Gagal memperbarui setting aplikasi.');
        }

        redirect('App_settings');
    }

    public function delete_logo() {
        if ($this->input->method() !== 'post') {
            show_404();
        }

        $settings = $this->App_settings_model->get_settings();
        if ($settings && $settings->logo_kampus) {
            $logo_path = str_replace('\\', '/', $settings->logo_kampus);
            $full_path = FCPATH . str_replace('/', DIRECTORY_SEPARATOR, $logo_path);
            if (file_exists($full_path)) {
                @unlink($full_path);
            }
            
            $this->App_settings_model->update($settings->id, array('logo_kampus' => null));
            $this->session->set_flashdata('success', 'Logo berhasil dihapus.');
        } else {
            $this->session->set_flashdata('error', 'Logo tidak ditemukan.');
        }

        redirect('App_settings');
    }

    public function delete_kop_surat() {
        if ($this->input->method() !== 'post') {
            show_404();
        }

        $settings = $this->App_settings_model->get_settings();
        if ($settings && $settings->kop_surat) {
            $kop_path = str_replace('\\', '/', $settings->kop_surat);
            $full_path = FCPATH . str_replace('/', DIRECTORY_SEPARATOR, $kop_path);
            if (file_exists($full_path)) {
                @unlink($full_path);
            }
            
            $this->App_settings_model->update($settings->id, array('kop_surat' => null));
            $this->session->set_flashdata('success', 'Kop surat berhasil dihapus.');
        } else {
            $this->session->set_flashdata('error', 'Kop surat tidak ditemukan.');
        }

        redirect('App_settings');
    }

    public function view_kop_surat() {
        $settings = $this->App_settings_model->get_settings();
        
        if (!$settings || !$settings->kop_surat) {
            show_404();
        }
        
        $kop_path = str_replace('\\', '/', $settings->kop_surat);
        $full_path = FCPATH . str_replace('/', DIRECTORY_SEPARATOR, $kop_path);
        
        // Cek path alternatif
        if (!file_exists($full_path)) {
            $alt_path = FCPATH . 'uploads' . DIRECTORY_SEPARATOR . 'kop_surat' . DIRECTORY_SEPARATOR . 'kop_surat.jpg';
            if (file_exists($alt_path)) {
                $full_path = $alt_path;
            } else {
                show_404();
            }
        }
        
        // Set header untuk gambar
        $this->output->set_content_type('image/jpeg');
        $this->output->set_header('Content-Disposition: inline; filename="kop_surat.jpg"');
        $this->output->set_header('Cache-Control: public, max-age=3600');
        
        // Output file
        readfile($full_path);
    }

    /**
     * Backup Database
     */
    public function backup_database() {
        // Ambil konfigurasi database
        $this->load->database();
        $db_config = $this->db->database;
        $db_host = $this->db->hostname;
        $db_user = $this->db->username;
        $db_pass = $this->db->password;
        
        // Nama file backup
        $filename = 'backup_' . $db_config . '_' . date('Y-m-d_H-i-s') . '.sql';
        
        // Path untuk menyimpan backup (opsional, bisa langsung download)
        $backup_path = FCPATH . 'backups' . DIRECTORY_SEPARATOR;
        if (!is_dir($backup_path)) {
            mkdir($backup_path, 0755, true);
        }
        
        // Cek apakah mysqldump tersedia (untuk Windows dengan XAMPP)
        $mysqldump_path = '';
        $possible_paths = array(
            'C:\\xampp\\mysql\\bin\\mysqldump.exe',
            'C:\\wamp64\\bin\\mysql\\mysql8.0.xx\\bin\\mysqldump.exe',
            'mysqldump' // Jika ada di PATH
        );
        
        $use_mysqldump = false;
        foreach ($possible_paths as $path) {
            if (file_exists($path) || $path === 'mysqldump') {
                $mysqldump_path = $path;
                // Test jika mysqldump bisa dijalankan
                $test_cmd = ($path === 'mysqldump') ? 'mysqldump --version' : '"' . $path . '" --version';
                @exec($test_cmd . ' 2>&1', $output, $return_var);
                if ($return_var === 0) {
                    $use_mysqldump = true;
                    break;
                }
            }
        }
        
        if ($use_mysqldump && $mysqldump_path !== 'mysqldump') {
            // Menggunakan mysqldump (lebih cepat untuk database besar)
            $backup_file = $backup_path . $filename;
            $command = '"' . $mysqldump_path . '"';
            $command .= ' -h ' . escapeshellarg($db_host);
            $command .= ' -u ' . escapeshellarg($db_user);
            if (!empty($db_pass)) {
                $command .= ' -p' . escapeshellarg($db_pass);
            }
            $command .= ' ' . escapeshellarg($db_config);
            $command .= ' > "' . $backup_file . '" 2>&1';
            
            @exec($command, $output, $return_var);
            
            if ($return_var === 0 && file_exists($backup_file) && filesize($backup_file) > 0) {
                // Download file
                header('Content-Type: application/octet-stream');
                header('Content-Disposition: attachment; filename="' . $filename . '"');
                header('Content-Length: ' . filesize($backup_file));
                readfile($backup_file);
                
                // Hapus file setelah download (opsional)
                @unlink($backup_file);
                exit;
            } else {
                // Fallback ke method PHP jika mysqldump gagal
                $this->backup_database_php($db_config, $db_host, $db_user, $db_pass, $filename);
            }
        } else {
            // Menggunakan PHP (export langsung dari database)
            $this->backup_database_php($db_config, $db_host, $db_user, $db_pass, $filename);
        }
    }

    /**
     * Backup database menggunakan PHP (tanpa mysqldump)
     */
    private function backup_database_php($db_name, $db_host, $db_user, $db_pass, $filename) {
        // Buat koneksi langsung ke MySQL
        $conn = @mysqli_connect($db_host, $db_user, $db_pass, $db_name);
        
        if (!$conn) {
            $this->session->set_flashdata('error', 'Gagal terhubung ke database. Error: ' . mysqli_connect_error());
            redirect('App_settings');
            return;
        }
        
        // Set charset
        mysqli_set_charset($conn, 'utf8');
        
        // Mulai backup
        $output = "-- Database Backup\n";
        $output .= "-- Generated: " . date('Y-m-d H:i:s') . "\n";
        $output .= "-- Database: " . $db_name . "\n\n";
        $output .= "SET SQL_MODE = \"NO_AUTO_VALUE_ON_ZERO\";\n";
        $output .= "SET AUTOCOMMIT = 0;\n";
        $output .= "START TRANSACTION;\n";
        $output .= "SET time_zone = \"+00:00\";\n\n";
        
        // Ambil semua tabel
        $tables = array();
        $result = mysqli_query($conn, "SHOW TABLES");
        while ($row = mysqli_fetch_row($result)) {
            $tables[] = $row[0];
        }
        
        // Backup setiap tabel
        foreach ($tables as $table) {
            $output .= "\n-- --------------------------------------------------------\n";
            $output .= "-- Table structure for table `{$table}`\n";
            $output .= "-- --------------------------------------------------------\n\n";
            
            // Drop table
            $output .= "DROP TABLE IF EXISTS `{$table}`;\n";
            
            // Create table
            $result = mysqli_query($conn, "SHOW CREATE TABLE `{$table}`");
            $row = mysqli_fetch_row($result);
            $output .= $row[1] . ";\n\n";
            
            // Insert data
            $result = mysqli_query($conn, "SELECT * FROM `{$table}`");
            if (mysqli_num_rows($result) > 0) {
                $output .= "-- Dumping data for table `{$table}`\n\n";
                
                while ($data = mysqli_fetch_assoc($result)) {
                    $output .= "INSERT INTO `{$table}` VALUES(";
                    $values = array();
                    foreach ($data as $value) {
                        if ($value === null) {
                            $values[] = "NULL";
                        } else {
                            $values[] = "'" . mysqli_real_escape_string($conn, $value) . "'";
                        }
                    }
                    $output .= implode(',', $values) . ");\n";
                }
                $output .= "\n";
            }
        }
        
        $output .= "COMMIT;\n";
        
        mysqli_close($conn);
        
        // Download file
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Content-Length: ' . strlen($output));
        echo $output;
        exit;
    }
}

