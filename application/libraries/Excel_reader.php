<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Excel Reader Library
 * Library sederhana untuk membaca file Excel (menggunakan SimpleXLSX atau alternatif)
 */
class Excel_reader {

    public function __construct() {
        // Load SimpleXLSX jika tersedia, atau gunakan alternatif
        // Untuk CI3, kita akan gunakan pendekatan sederhana dengan PHPExcel atau PhpSpreadsheet
    }

    /**
     * Baca file Excel dan import ke mapping MK
     * @param string $file_path Path ke file Excel
     * @param int $id_kurikulum ID kurikulum
     * @return array ['success' => bool, 'message' => string]
     */
    public function read_excel($file_path, $id_kurikulum) {
        $CI =& get_instance();
        $CI->load->model('Kurikulum_model');
        $CI->load->model('Mata_kuliah_model');

        // Cek ekstensi file
        $ext = strtolower(pathinfo($file_path, PATHINFO_EXTENSION));
        
        if ($ext == 'xlsx') {
            return $this->read_xlsx($file_path, $id_kurikulum);
        } elseif ($ext == 'xls') {
            return $this->read_xls($file_path, $id_kurikulum);
        } elseif ($ext == 'csv') {
            return $this->read_csv($file_path, $id_kurikulum);
        } else {
            return array(
                'success' => false,
                'message' => 'Format file tidak didukung. Gunakan .xlsx, .xls, atau .csv'
            );
        }
    }

    /**
     * Baca file CSV (paling sederhana, tidak perlu library)
     */
    private function read_csv($file_path, $id_kurikulum) {
        $rows = array();
        
        if (($handle = fopen($file_path, "r")) !== FALSE) {
            while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
                $rows[] = $data;
            }
            fclose($handle);
        }

        return $this->process_excel_data($rows, $id_kurikulum);
    }

    /**
     * Baca file XLSX menggunakan SimpleXLSX atau alternatif
     */
    private function read_xlsx($file_path, $id_kurikulum) {
        $CI =& get_instance();
        $CI->load->model('Kurikulum_model');
        $CI->load->model('Mata_kuliah_model');

        // Coba gunakan PhpSpreadsheet jika tersedia via Composer
        if (file_exists(APPPATH . '../vendor/autoload.php')) {
            require_once APPPATH . '../vendor/autoload.php';
            return $this->read_with_phpspreadsheet($file_path, $id_kurikulum);
        }

        // Alternatif: Gunakan SimpleXLSX
        if (file_exists(APPPATH . 'third_party/SimpleXLSX.php')) {
            require_once APPPATH . 'third_party/SimpleXLSX.php';
            return $this->read_with_simplexlsx($file_path, $id_kurikulum);
        }

        // Fallback: Baca menggunakan ZIP (untuk XLSX)
        return $this->read_xlsx_simple($file_path, $id_kurikulum);
    }

    /**
     * Baca XLSX menggunakan PhpSpreadsheet
     */
    private function read_with_phpspreadsheet($file_path, $id_kurikulum) {
        $CI =& get_instance();
        
        try {
            $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($file_path);
            $worksheet = $spreadsheet->getActiveSheet();
            $rows = $worksheet->toArray();

            return $this->process_excel_data($rows, $id_kurikulum);
        } catch (Exception $e) {
            return array(
                'success' => false,
                'message' => 'Error membaca file: ' . $e->getMessage()
            );
        }
    }

    /**
     * Baca XLSX menggunakan SimpleXLSX
     */
    private function read_with_simplexlsx($file_path, $id_kurikulum) {
        $CI =& get_instance();
        
        if ($xlsx = SimpleXLSX::parse($file_path)) {
            $rows = $xlsx->rows();
            return $this->process_excel_data($rows, $id_kurikulum);
        } else {
            return array(
                'success' => false,
                'message' => 'Gagal membaca file Excel: ' . SimpleXLSX::parseError()
            );
        }
    }

    /**
     * Baca XLSX sederhana (fallback)
     */
    private function read_xlsx_simple($file_path, $id_kurikulum) {
        // Untuk XLSX, kita perlu library eksternal
        // Sementara return error dan minta install library
        return array(
            'success' => false,
            'message' => 'Library Excel tidak ditemukan. Silakan install PhpSpreadsheet via Composer atau SimpleXLSX di folder third_party.'
        );
    }

    /**
     * Baca file XLS (Excel 97-2003)
     */
    private function read_xls($file_path, $id_kurikulum) {
        // XLS lebih kompleks, gunakan library PHPExcel atau konversi ke XLSX dulu
        return array(
            'success' => false,
            'message' => 'Format .xls belum didukung. Silakan konversi ke .xlsx terlebih dahulu.'
        );
    }

    /**
     * Proses data dari Excel
     */
    private function process_excel_data($rows, $id_kurikulum) {
        $CI =& get_instance();
        $CI->load->model('Kurikulum_model');
        $CI->load->model('Mata_kuliah_model');

        if (empty($rows) || count($rows) < 2) {
            return array(
                'success' => false,
                'message' => 'File Excel kosong atau tidak memiliki data.'
            );
        }

        // Baris pertama adalah header
        $header = array_map('strtolower', array_map('trim', $rows[0]));
        
        // Cek kolom yang diperlukan
        $required_columns = array('kode_mk', 'nama_mk', 'sks', 'semester', 'is_wajib');
        $missing_columns = array();
        foreach ($required_columns as $col) {
            if (!in_array($col, $header)) {
                $missing_columns[] = $col;
            }
        }

        if (!empty($missing_columns)) {
            return array(
                'success' => false,
                'message' => 'Kolom yang diperlukan tidak ditemukan: ' . implode(', ', $missing_columns)
            );
        }

        // Get index kolom
        $col_kode = array_search('kode_mk', $header);
        $col_nama = array_search('nama_mk', $header);
        $col_sks = array_search('sks', $header);
        $col_semester = array_search('semester', $header);
        $col_wajib = array_search('is_wajib', $header);

        $success_count = 0;
        $error_count = 0;
        $errors = array();

        // Proses setiap baris data (skip header)
        for ($i = 1; $i < count($rows); $i++) {
            $row = $rows[$i];
            
            // Skip baris kosong
            if (empty($row[$col_kode]) && empty($row[$col_nama])) {
                continue;
            }

            $kode_mk = strtoupper(trim($row[$col_kode]));
            $nama_mk = trim($row[$col_nama]);
            $sks = intval($row[$col_sks]);
            $semester = intval($row[$col_semester]);
            $is_wajib = intval($row[$col_wajib]);

            // Validasi
            if (empty($kode_mk) || empty($nama_mk) || $sks < 1 || $semester < 1 || $semester > 8) {
                $error_count++;
                $errors[] = "Baris " . ($i + 1) . ": Data tidak valid";
                continue;
            }

            // Cek atau buat mata kuliah
            $mk = $CI->Mata_kuliah_model->get_by_kode($kode_mk);
            if (!$mk) {
                $mk_data = array(
                    'kode_mk' => $kode_mk,
                    'nama_mk' => $nama_mk,
                    'sks' => $sks
                );
                $id_mata_kuliah = $CI->Mata_kuliah_model->create($mk_data);
            } else {
                $id_mata_kuliah = $mk->id;
                // Update jika berbeda
                if ($mk->nama_mk != $nama_mk || $mk->sks != $sks) {
                    $CI->Mata_kuliah_model->update($mk->id, array(
                        'nama_mk' => $nama_mk,
                        'sks' => $sks
                    ));
                }
            }

            // Tambah ke mapping
            if ($CI->Kurikulum_model->add_mapping_mk($id_kurikulum, $id_mata_kuliah, $semester, $is_wajib)) {
                $success_count++;
            } else {
                $error_count++;
                $errors[] = "Baris " . ($i + 1) . ": " . $kode_mk . " sudah ada di semester " . $semester;
            }
        }

        $message = "Import selesai. Berhasil: {$success_count}, Gagal: {$error_count}";
        if (!empty($errors) && $error_count > 0) {
            $message .= "<br>Detail error: " . implode(", ", array_slice($errors, 0, 5));
            if (count($errors) > 5) {
                $message .= " dan " . (count($errors) - 5) . " error lainnya.";
            }
        }

        return array(
            'success' => $success_count > 0,
            'message' => $message
        );
    }

    /**
     * Baca file Excel dan import ke mahasiswa
     * @param string $file_path Path ke file Excel
     * @return array ['success' => bool, 'message' => string, 'success_count' => int, 'error_count' => int]
     */
    public function read_excel_mahasiswa($file_path) {
        $CI =& get_instance();
        $CI->load->model('Mahasiswa_model');
        $CI->load->model('Kurikulum_model');
        $CI->load->model('Tahun_akademik_model');

        // Cek ekstensi file
        $ext = strtolower(pathinfo($file_path, PATHINFO_EXTENSION));
        
        if ($ext == 'xlsx') {
            return $this->read_xlsx_mahasiswa($file_path);
        } elseif ($ext == 'xls') {
            return $this->read_xls_mahasiswa($file_path);
        } elseif ($ext == 'csv') {
            return $this->read_csv_mahasiswa($file_path);
        } else {
            return array(
                'success' => false,
                'message' => 'Format file tidak didukung. Gunakan .xlsx, .xls, atau .csv',
                'success_count' => 0,
                'error_count' => 0
            );
        }
    }

    /**
     * Baca file CSV untuk mahasiswa
     */
    private function read_csv_mahasiswa($file_path) {
        $rows = array();
        
        if (($handle = fopen($file_path, "r")) !== FALSE) {
            while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
                $rows[] = $data;
            }
            fclose($handle);
        }

        return $this->process_mahasiswa_data($rows);
    }

    /**
     * Baca file XLSX untuk mahasiswa
     */
    private function read_xlsx_mahasiswa($file_path) {
        $CI =& get_instance();
        
        // Coba gunakan PhpSpreadsheet jika tersedia
        if (file_exists(APPPATH . '../vendor/autoload.php')) {
            require_once APPPATH . '../vendor/autoload.php';
            try {
                $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($file_path);
                $worksheet = $spreadsheet->getActiveSheet();
                $rows = $worksheet->toArray();
                return $this->process_mahasiswa_data($rows);
            } catch (Exception $e) {
                return array(
                    'success' => false,
                    'message' => 'Error membaca file: ' . $e->getMessage(),
                    'success_count' => 0,
                    'error_count' => 0
                );
            }
        }

        // Alternatif: Gunakan SimpleXLSX
        if (file_exists(APPPATH . 'third_party/SimpleXLSX.php')) {
            require_once APPPATH . 'third_party/SimpleXLSX.php';
            if ($xlsx = SimpleXLSX::parse($file_path)) {
                $rows = $xlsx->rows();
                return $this->process_mahasiswa_data($rows);
            } else {
                return array(
                    'success' => false,
                    'message' => 'Gagal membaca file Excel: ' . SimpleXLSX::parseError(),
                    'success_count' => 0,
                    'error_count' => 0
                );
            }
        }

        return array(
            'success' => false,
            'message' => 'Library Excel tidak ditemukan. Silakan install PhpSpreadsheet via Composer atau gunakan format CSV.',
            'success_count' => 0,
            'error_count' => 0
        );
    }

    /**
     * Baca file XLS untuk mahasiswa
     */
    private function read_xls_mahasiswa($file_path) {
        return array(
            'success' => false,
            'message' => 'Format .xls belum didukung. Silakan konversi ke .xlsx atau .csv terlebih dahulu.',
            'success_count' => 0,
            'error_count' => 0
        );
    }

    /**
     * Proses data mahasiswa dari Excel
     */
    private function process_mahasiswa_data($rows) {
        $CI =& get_instance();
        $CI->load->model('Mahasiswa_model');
        $CI->load->model('Kurikulum_model');
        $CI->load->model('Tahun_akademik_model');

        if (empty($rows) || count($rows) < 2) {
            return array(
                'success' => false,
                'message' => 'File Excel kosong atau tidak memiliki data.',
                'success_count' => 0,
                'error_count' => 0
            );
        }

        // Baris pertama adalah header
        $header = array_map('strtolower', array_map('trim', $rows[0]));
        
        // Cek kolom yang diperlukan
        $required_columns = array('nim', 'nama_mahasiswa', 'angkatan', 'id_kurikulum');
        $missing_columns = array();
        foreach ($required_columns as $col) {
            if (!in_array($col, $header)) {
                $missing_columns[] = $col;
            }
        }

        if (!empty($missing_columns)) {
            return array(
                'success' => false,
                'message' => 'Kolom yang diperlukan tidak ditemukan: ' . implode(', ', $missing_columns),
                'success_count' => 0,
                'error_count' => 0
            );
        }

        // Get index kolom
        $col_nim = array_search('nim', $header);
        $col_nama = array_search('nama_mahasiswa', $header);
        $col_angkatan = array_search('angkatan', $header);
        $col_kurikulum = array_search('id_kurikulum', $header);
        $col_kelas = array_search('kelas', $header) !== false ? array_search('kelas', $header) : null;
        $col_status = array_search('status', $header) !== false ? array_search('status', $header) : null;

        $success_count = 0;
        $error_count = 0;
        $errors = array();

        // Proses setiap baris data (skip header)
        for ($i = 1; $i < count($rows); $i++) {
            $row = $rows[$i];
            
            // Skip baris kosong
            if (empty($row[$col_nim]) && empty($row[$col_nama])) {
                continue;
            }

            $nim = trim($row[$col_nim]);
            $nama = trim($row[$col_nama]);
            $angkatan = intval($row[$col_angkatan]);
            $id_kurikulum = intval($row[$col_kurikulum]);
            $kelas = $col_kelas !== null ? trim($row[$col_kelas]) : '';
            $status = $col_status !== null ? trim(strtolower($row[$col_status])) : 'aktif';

            // Validasi
            if (empty($nim) || empty($nama) || empty($angkatan) || empty($id_kurikulum)) {
                $error_count++;
                $errors[] = "Baris " . ($i + 1) . ": Data tidak lengkap";
                continue;
            }

            // Cek NIM sudah ada
            if ($CI->Mahasiswa_model->get_by_nim($nim)) {
                $error_count++;
                $errors[] = "Baris " . ($i + 1) . ": NIM $nim sudah terdaftar";
                continue;
            }

            // Cek kurikulum ada
            $kurikulum = $CI->Kurikulum_model->get_by_id($id_kurikulum);
            if (!$kurikulum) {
                $error_count++;
                $errors[] = "Baris " . ($i + 1) . ": Kurikulum ID $id_kurikulum tidak ditemukan";
                continue;
            }

            // Hitung semester aktif
            $CI->load->library('Kenaikan_kelas');
            $attr = $CI->kenaikan_kelas->atribut_baru($angkatan);
            $semester_aktif = $attr['semester_aktif'];

            // Generate kelas jika kosong
            if (empty($kelas)) {
                // Deteksi apakah karyawan (NIM mengandung 'K' atau nama mengandung 'karyawan')
                $is_karyawan = (stripos($nim, 'K') !== false || stripos($nama, 'karyawan') !== false);
                $kelas = $CI->Mahasiswa_model->generate_kelas($semester_aktif, $is_karyawan);
            } else {
                // Validasi format kelas (harus {semester}{huruf})
                $kelas = strtoupper(trim($kelas));
                if (!preg_match('/^[1-8][A-Z]$/', $kelas)) {
                    $error_count++;
                    $errors[] = "Baris " . ($i + 1) . ": Format kelas tidak valid (harus seperti 1A, 1B, 1G)";
                    continue;
                }
            }

            // Insert mahasiswa
            $data = array(
                'nim' => $nim,
                'nama_mahasiswa' => $nama,
                'angkatan' => $angkatan,
                'semester_aktif' => $semester_aktif,
                'kelas' => $kelas,
                'id_kurikulum' => $id_kurikulum,
                'status' => $status
            );
            if (isset($attr['semester_riil'])) {
                $data['semester_riil'] = $attr['semester_riil'];
                $data['rombel'] = Kenaikan_kelas::rombel_dari_kelas($kelas);
            }

            $mahasiswa_id = $CI->Mahasiswa_model->create($data);

            if ($mahasiswa_id) {
                // Buat user account
                $this->create_user_account_mahasiswa($nim, $mahasiswa_id);
                $success_count++;
            } else {
                $error_count++;
                $errors[] = "Baris " . ($i + 1) . ": Gagal menyimpan data";
            }
        }

        $message = "Import selesai. Berhasil: {$success_count}, Gagal: {$error_count}";
        if (!empty($errors) && $error_count > 0) {
            $message .= "<br>Detail error: " . implode(", ", array_slice($errors, 0, 5));
            if (count($errors) > 5) {
                $message .= " dan " . (count($errors) - 5) . " error lainnya.";
            }
        }

        return array(
            'success' => $success_count > 0,
            'message' => $message,
            'success_count' => $success_count,
            'error_count' => $error_count
        );
    }

    /**
     * Hitung semester aktif untuk mahasiswa
     */
    private function calculate_semester_aktif_mahasiswa($angkatan) {
        $CI =& get_instance();
        $CI->load->library('Kenaikan_kelas');
        $a = $CI->kenaikan_kelas->atribut_baru($angkatan);
        return $a['semester_aktif'];
    }

    /**
     * Buat user account untuk mahasiswa
     */
    private function create_user_account_mahasiswa($nim, $mahasiswa_id) {
        $CI =& get_instance();
        
        // Ambil role_id untuk mahasiswa
        $CI->db->where('nama_role', 'mahasiswa');
        $role = $CI->db->get('roles')->row();
        
        if (!$role) {
            return false;
        }
        
        // Cek apakah user sudah ada
        $CI->db->where('username', $nim);
        $existing = $CI->db->get('users')->row();
        
        if ($existing) {
            // Update ref_id jika user sudah ada
            $CI->db->where('id', $existing->id);
            $CI->db->update('users', array('ref_id' => $mahasiswa_id));
            return true;
        }
        
        // Insert user baru
        $data = array(
            'username' => $nim,
            'password' => md5($nim),
            'role_id' => $role->id,
            'ref_id' => $mahasiswa_id
        );
        
        $CI->db->insert('users', $data);
        return $CI->db->insert_id();
    }

    /**
     * Baca file Excel dan import ke dosen
     */
    public function read_excel_dosen($file_path) {
        $CI =& get_instance();
        $CI->load->model('Dosen_model');

        // Cek ekstensi file
        $ext = strtolower(pathinfo($file_path, PATHINFO_EXTENSION));
        
        if ($ext == 'xlsx') {
            return $this->read_xlsx_dosen($file_path);
        } elseif ($ext == 'xls') {
            return $this->read_xls_dosen($file_path);
        } elseif ($ext == 'csv') {
            return $this->read_csv_dosen($file_path);
        } else {
            return array(
                'success' => false,
                'message' => 'Format file tidak didukung. Gunakan .xlsx, .xls, atau .csv',
                'success_count' => 0,
                'error_count' => 0
            );
        }
    }

    /**
     * Baca file CSV untuk dosen
     */
    private function read_csv_dosen($file_path) {
        $rows = array();
        
        if (($handle = fopen($file_path, "r")) !== FALSE) {
            while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
                $rows[] = $data;
            }
            fclose($handle);
        }

        return $this->process_dosen_data($rows);
    }

    /**
     * Baca file XLSX untuk dosen
     */
    private function read_xlsx_dosen($file_path) {
        $CI =& get_instance();
        
        // Coba gunakan PhpSpreadsheet jika tersedia
        if (file_exists(APPPATH . '../vendor/autoload.php')) {
            require_once APPPATH . '../vendor/autoload.php';
            try {
                $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($file_path);
                $worksheet = $spreadsheet->getActiveSheet();
                $rows = $worksheet->toArray();
                return $this->process_dosen_data($rows);
            } catch (Exception $e) {
                return array(
                    'success' => false,
                    'message' => 'Error membaca file: ' . $e->getMessage(),
                    'success_count' => 0,
                    'error_count' => 0
                );
            }
        }

        // Alternatif: Gunakan SimpleXLSX
        if (file_exists(APPPATH . 'third_party/SimpleXLSX.php')) {
            require_once APPPATH . 'third_party/SimpleXLSX.php';
            if ($xlsx = SimpleXLSX::parse($file_path)) {
                $rows = $xlsx->rows();
                return $this->process_dosen_data($rows);
            } else {
                return array(
                    'success' => false,
                    'message' => 'Gagal membaca file Excel: ' . SimpleXLSX::parseError(),
                    'success_count' => 0,
                    'error_count' => 0
                );
            }
        }

        return array(
            'success' => false,
            'message' => 'Library Excel tidak ditemukan. Silakan install PhpSpreadsheet via Composer atau gunakan format CSV.',
            'success_count' => 0,
            'error_count' => 0
        );
    }

    /**
     * Baca file XLS untuk dosen
     */
    private function read_xls_dosen($file_path) {
        return array(
            'success' => false,
            'message' => 'Format .xls belum didukung. Silakan konversi ke .xlsx atau .csv terlebih dahulu.',
            'success_count' => 0,
            'error_count' => 0
        );
    }

    /**
     * Proses data dosen dari Excel
     */
    private function process_dosen_data($rows) {
        $CI =& get_instance();
        $CI->load->model('Dosen_model');

        if (empty($rows) || count($rows) < 2) {
            return array(
                'success' => false,
                'message' => 'File Excel kosong atau tidak memiliki data.',
                'success_count' => 0,
                'error_count' => 0
            );
        }

        // Baris pertama adalah header
        $header = array_map('strtolower', array_map('trim', $rows[0]));
        
        // Cek kolom yang diperlukan
        $required_columns = array('nidn', 'nama_dosen');
        $missing_columns = array();
        foreach ($required_columns as $col) {
            if (!in_array($col, $header)) {
                $missing_columns[] = $col;
            }
        }

        if (!empty($missing_columns)) {
            return array(
                'success' => false,
                'message' => 'Kolom yang diperlukan tidak ditemukan: ' . implode(', ', $missing_columns),
                'success_count' => 0,
                'error_count' => 0
            );
        }

        // Get index kolom
        $col_nidn = array_search('nidn', $header);
        $col_nama = array_search('nama_dosen', $header);
        $col_email = array_search('email', $header) !== false ? array_search('email', $header) : null;
        $col_jabatan = array_search('jabatan_fungsional', $header) !== false ? array_search('jabatan_fungsional', $header) : null;
        $col_status = array_search('status', $header) !== false ? array_search('status', $header) : null;

        $success_count = 0;
        $error_count = 0;
        $errors = array();

        // Proses setiap baris data (skip header)
        for ($i = 1; $i < count($rows); $i++) {
            $row = $rows[$i];
            
            // Skip baris kosong
            if (empty($row[$col_nidn]) && empty($row[$col_nama])) {
                continue;
            }

            $nidn = trim($row[$col_nidn]);
            $nama = trim($row[$col_nama]);
            $email = $col_email !== null ? trim($row[$col_email]) : '';
            $jabatan = $col_jabatan !== null ? trim($row[$col_jabatan]) : 'Asisten Ahli';
            $status = $col_status !== null ? trim(strtolower($row[$col_status])) : 'aktif';

            // Validasi jabatan fungsional
            $jabatan_valid = array('Asisten Ahli', 'Lektor', 'Lektor Kepala', 'Guru Besar (Profesor)');
            if (!in_array($jabatan, $jabatan_valid)) {
                $jabatan = 'Asisten Ahli';
            }

            // Validasi
            if (empty($nidn) || empty($nama)) {
                $error_count++;
                $errors[] = "Baris " . ($i + 1) . ": Data tidak lengkap";
                continue;
            }

            // Cek NIDN sudah ada
            if ($CI->Dosen_model->get_by_nidn($nidn)) {
                $error_count++;
                $errors[] = "Baris " . ($i + 1) . ": NIDN $nidn sudah terdaftar";
                continue;
            }

            // Insert dosen
            $data = array(
                'nidn' => $nidn,
                'nama_dosen' => $nama,
                'email' => $email,
                'jabatan_fungsional' => $jabatan,
                'status' => $status
            );

            $dosen_id = $CI->Dosen_model->create($data);

            if ($dosen_id) {
                // Buat user account
                $this->create_user_account_dosen($nidn, $dosen_id);
                $success_count++;
            } else {
                $error_count++;
                $errors[] = "Baris " . ($i + 1) . ": Gagal menyimpan data";
            }
        }

        $message = "Import selesai. Berhasil: {$success_count}, Gagal: {$error_count}";
        if (!empty($errors) && $error_count > 0) {
            $message .= "<br>Detail error: " . implode(", ", array_slice($errors, 0, 5));
            if (count($errors) > 5) {
                $message .= " dan " . (count($errors) - 5) . " error lainnya.";
            }
        }

        return array(
            'success' => $success_count > 0,
            'message' => $message,
            'success_count' => $success_count,
            'error_count' => $error_count
        );
    }

    /**
     * Buat user account untuk dosen
     */
    private function create_user_account_dosen($nidn, $dosen_id) {
        $CI =& get_instance();
        
        // Ambil role_id untuk dosen
        $CI->db->where('nama_role', 'dosen');
        $role = $CI->db->get('roles')->row();
        
        if (!$role) {
            return false;
        }
        
        // Cek apakah user sudah ada
        $CI->db->where('username', $nidn);
        $existing = $CI->db->get('users')->row();
        
        if ($existing) {
            // Update ref_id jika user sudah ada
            $CI->db->where('id', $existing->id);
            $CI->db->update('users', array('ref_id' => $dosen_id));
            return true;
        }
        
        // Insert user baru
        $data = array(
            'username' => $nidn,
            'password' => md5($nidn),
            'role_id' => $role->id,
            'ref_id' => $dosen_id
        );
        
        $CI->db->insert('users', $data);
        return $CI->db->insert_id();
    }

    /**
     * Baca file Excel dan import ke tendik
     */
    public function read_excel_tendik($file_path) {
        $CI =& get_instance();
        $CI->load->model('Tendik_model');

        // Cek ekstensi file
        $ext = strtolower(pathinfo($file_path, PATHINFO_EXTENSION));
        
        if ($ext == 'xlsx') {
            return $this->read_xlsx_tendik($file_path);
        } elseif ($ext == 'xls') {
            return $this->read_xls_tendik($file_path);
        } elseif ($ext == 'csv') {
            return $this->read_csv_tendik($file_path);
        } else {
            return array(
                'success' => false,
                'message' => 'Format file tidak didukung. Gunakan .xlsx, .xls, atau .csv',
                'success_count' => 0,
                'error_count' => 0
            );
        }
    }

    /**
     * Baca file CSV untuk tendik
     */
    private function read_csv_tendik($file_path) {
        $rows = array();
        
        if (($handle = fopen($file_path, "r")) !== FALSE) {
            while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
                $rows[] = $data;
            }
            fclose($handle);
        }

        return $this->process_tendik_data($rows);
    }

    /**
     * Baca file XLSX untuk tendik
     */
    private function read_xlsx_tendik($file_path) {
        $CI =& get_instance();
        
        // Coba gunakan PhpSpreadsheet jika tersedia
        if (file_exists(APPPATH . '../vendor/autoload.php')) {
            require_once APPPATH . '../vendor/autoload.php';
            try {
                $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($file_path);
                $worksheet = $spreadsheet->getActiveSheet();
                $rows = $worksheet->toArray();
                return $this->process_tendik_data($rows);
            } catch (Exception $e) {
                return array(
                    'success' => false,
                    'message' => 'Error membaca file: ' . $e->getMessage(),
                    'success_count' => 0,
                    'error_count' => 0
                );
            }
        }

        // Alternatif: Gunakan SimpleXLSX
        if (file_exists(APPPATH . 'third_party/SimpleXLSX.php')) {
            require_once APPPATH . 'third_party/SimpleXLSX.php';
            if ($xlsx = SimpleXLSX::parse($file_path)) {
                $rows = $xlsx->rows();
                return $this->process_tendik_data($rows);
            } else {
                return array(
                    'success' => false,
                    'message' => 'Gagal membaca file Excel: ' . SimpleXLSX::parseError(),
                    'success_count' => 0,
                    'error_count' => 0
                );
            }
        }

        return array(
            'success' => false,
            'message' => 'Library Excel tidak ditemukan. Silakan install PhpSpreadsheet via Composer atau gunakan format CSV.',
            'success_count' => 0,
            'error_count' => 0
        );
    }

    /**
     * Baca file XLS untuk tendik
     */
    private function read_xls_tendik($file_path) {
        return array(
            'success' => false,
            'message' => 'Format .xls belum didukung. Silakan konversi ke .xlsx atau .csv terlebih dahulu.',
            'success_count' => 0,
            'error_count' => 0
        );
    }

    /**
     * Proses data tendik dari Excel
     */
    private function process_tendik_data($rows) {
        $CI =& get_instance();
        $CI->load->model('Tendik_model');

        if (empty($rows) || count($rows) < 2) {
            return array(
                'success' => false,
                'message' => 'File Excel kosong atau tidak memiliki data.',
                'success_count' => 0,
                'error_count' => 0
            );
        }

        // Baris pertama adalah header
        $header = array_map('strtolower', array_map('trim', $rows[0]));
        
        // Cek kolom yang diperlukan
        $required_columns = array('nipy', 'nama_tendik');
        $missing_columns = array();
        foreach ($required_columns as $col) {
            if (!in_array($col, $header)) {
                $missing_columns[] = $col;
            }
        }

        if (!empty($missing_columns)) {
            return array(
                'success' => false,
                'message' => 'Kolom yang diperlukan tidak ditemukan: ' . implode(', ', $missing_columns),
                'success_count' => 0,
                'error_count' => 0
            );
        }

        // Get index kolom
        $col_nipy = array_search('nipy', $header);
        $col_nama = array_search('nama_tendik', $header);
        $col_email = array_search('email', $header) !== false ? array_search('email', $header) : null;
        $col_jabatan = array_search('jabatan', $header) !== false ? array_search('jabatan', $header) : null;
        $col_status = array_search('status', $header) !== false ? array_search('status', $header) : null;

        $success_count = 0;
        $error_count = 0;
        $errors = array();

        // Proses setiap baris data (skip header)
        for ($i = 1; $i < count($rows); $i++) {
            $row = $rows[$i];
            
            // Skip baris kosong
            if (empty($row[$col_nipy]) && empty($row[$col_nama])) {
                continue;
            }

            $nipy = trim($row[$col_nipy]);
            $nama = trim($row[$col_nama]);
            $email = $col_email !== null ? trim($row[$col_email]) : '';
            $jabatan = $col_jabatan !== null ? trim($row[$col_jabatan]) : '';
            $status = $col_status !== null ? trim(strtolower($row[$col_status])) : 'aktif';

            // Validasi
            if (empty($nipy) || empty($nama)) {
                $error_count++;
                $errors[] = "Baris " . ($i + 1) . ": Data tidak lengkap";
                continue;
            }

            // Cek NIPY sudah ada
            if ($CI->Tendik_model->get_by_nipy($nipy)) {
                $error_count++;
                $errors[] = "Baris " . ($i + 1) . ": NIPY $nipy sudah terdaftar";
                continue;
            }

            // Insert tendik
            $data = array(
                'nipy' => $nipy,
                'nama_tendik' => $nama,
                'email' => $email,
                'jabatan' => $jabatan,
                'status' => $status
            );

            $tendik_id = $CI->Tendik_model->create($data);

            if ($tendik_id) {
                // Buat user account
                $this->create_user_account_tendik($nipy, $tendik_id);
                $success_count++;
            } else {
                $error_count++;
                $errors[] = "Baris " . ($i + 1) . ": Gagal menyimpan data";
            }
        }

        $message = "Import selesai. Berhasil: {$success_count}, Gagal: {$error_count}";
        if (!empty($errors) && $error_count > 0) {
            $message .= "<br>Detail error: " . implode(", ", array_slice($errors, 0, 5));
            if (count($errors) > 5) {
                $message .= " dan " . (count($errors) - 5) . " error lainnya.";
            }
        }

        return array(
            'success' => $success_count > 0,
            'message' => $message,
            'success_count' => $success_count,
            'error_count' => $error_count
        );
    }

    /**
     * Buat user account untuk tendik
     */
    private function create_user_account_tendik($nipy, $tendik_id) {
        $CI =& get_instance();
        
        // Ambil role_id untuk tendik (gunakan sekprodi sebagai default)
        $CI->db->where('nama_role', 'sekprodi');
        $role = $CI->db->get('roles')->row();
        
        if (!$role) {
            return false;
        }
        
        // Cek apakah user sudah ada
        $CI->db->where('username', $nipy);
        $existing = $CI->db->get('users')->row();
        
        if ($existing) {
            // Update ref_id jika user sudah ada
            $CI->db->where('id', $existing->id);
            $CI->db->update('users', array('ref_id' => $tendik_id));
            return true;
        }
        
        // Insert user baru
        $data = array(
            'username' => $nipy,
            'password' => md5($nipy),
            'role_id' => $role->id,
            'ref_id' => $tendik_id
        );
        
        $CI->db->insert('users', $data);
        return $CI->db->insert_id();
    }

    /**
     * Baca file Excel dan import ke ruang kelas
     * @param string $file_path Path ke file Excel
     * @return array ['success' => bool, 'message' => string, 'success_count' => int, 'error_count' => int]
     */
    public function read_excel_ruang_kelas($file_path) {
        $CI =& get_instance();
        $CI->load->model('Ruang_kelas_model');

        // Cek ekstensi file
        $ext = strtolower(pathinfo($file_path, PATHINFO_EXTENSION));
        
        if ($ext == 'xlsx') {
            return $this->read_xlsx_ruang_kelas($file_path);
        } elseif ($ext == 'xls') {
            return $this->read_xls_ruang_kelas($file_path);
        } elseif ($ext == 'csv') {
            return $this->read_csv_ruang_kelas($file_path);
        } else {
            return array(
                'success' => false,
                'message' => 'Format file tidak didukung. Gunakan .xlsx, .xls, atau .csv',
                'success_count' => 0,
                'error_count' => 0
            );
        }
    }

    /**
     * Baca file CSV untuk ruang kelas
     */
    private function read_csv_ruang_kelas($file_path) {
        $rows = array();
        
        if (($handle = fopen($file_path, "r")) !== FALSE) {
            while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
                $rows[] = $data;
            }
            fclose($handle);
        }

        return $this->process_ruang_kelas_data($rows);
    }

    /**
     * Baca file XLSX untuk ruang kelas
     */
    private function read_xlsx_ruang_kelas($file_path) {
        $CI =& get_instance();
        
        // Coba gunakan PhpSpreadsheet jika tersedia
        if (file_exists(APPPATH . '../vendor/autoload.php')) {
            require_once APPPATH . '../vendor/autoload.php';
            try {
                $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($file_path);
                $worksheet = $spreadsheet->getActiveSheet();
                $rows = $worksheet->toArray();
                return $this->process_ruang_kelas_data($rows);
            } catch (Exception $e) {
                return array(
                    'success' => false,
                    'message' => 'Error membaca file: ' . $e->getMessage(),
                    'success_count' => 0,
                    'error_count' => 0
                );
            }
        }

        // Alternatif: Gunakan SimpleXLSX
        if (file_exists(APPPATH . 'third_party/SimpleXLSX.php')) {
            require_once APPPATH . 'third_party/SimpleXLSX.php';
            if ($xlsx = SimpleXLSX::parse($file_path)) {
                $rows = $xlsx->rows();
                return $this->process_ruang_kelas_data($rows);
            } else {
                return array(
                    'success' => false,
                    'message' => 'Gagal membaca file Excel: ' . SimpleXLSX::parseError(),
                    'success_count' => 0,
                    'error_count' => 0
                );
            }
        }

        return array(
            'success' => false,
            'message' => 'Library Excel tidak ditemukan. Silakan install PhpSpreadsheet via Composer atau gunakan format CSV.',
            'success_count' => 0,
            'error_count' => 0
        );
    }

    /**
     * Baca file XLS untuk ruang kelas
     */
    private function read_xls_ruang_kelas($file_path) {
        return array(
            'success' => false,
            'message' => 'Format .xls belum didukung. Silakan konversi ke .xlsx atau .csv terlebih dahulu.',
            'success_count' => 0,
            'error_count' => 0
        );
    }

    /**
     * Proses data ruang kelas dari Excel
     */
    private function process_ruang_kelas_data($rows) {
        $CI =& get_instance();
        $CI->load->model('Ruang_kelas_model');

        if (empty($rows) || count($rows) < 2) {
            return array(
                'success' => false,
                'message' => 'File Excel kosong atau tidak memiliki data.',
                'success_count' => 0,
                'error_count' => 0
            );
        }

        // Baris pertama adalah header
        $header = array_map('strtolower', array_map('trim', $rows[0]));
        
        // Cek kolom yang diperlukan
        $required_columns = array('kode_ruang', 'nama_ruang');
        $missing_columns = array();
        foreach ($required_columns as $col) {
            if (!in_array($col, $header)) {
                $missing_columns[] = $col;
            }
        }

        if (!empty($missing_columns)) {
            return array(
                'success' => false,
                'message' => 'Kolom yang diperlukan tidak ditemukan: ' . implode(', ', $missing_columns),
                'success_count' => 0,
                'error_count' => 0
            );
        }

        // Get index kolom
        $col_kode = array_search('kode_ruang', $header);
        $col_nama = array_search('nama_ruang', $header);
        $col_kapasitas = array_search('kapasitas', $header) !== false ? array_search('kapasitas', $header) : null;
        $col_jenis = array_search('jenis_ruang', $header) !== false ? array_search('jenis_ruang', $header) : null;
        $col_lokasi = array_search('lokasi', $header) !== false ? array_search('lokasi', $header) : null;
        $col_fasilitas = array_search('fasilitas', $header) !== false ? array_search('fasilitas', $header) : null;
        $col_status = array_search('status', $header) !== false ? array_search('status', $header) : null;

        $success_count = 0;
        $error_count = 0;
        $errors = array();

        // Proses setiap baris data (skip header)
        for ($i = 1; $i < count($rows); $i++) {
            $row = $rows[$i];
            
            // Skip baris kosong
            if (empty($row[$col_kode]) && empty($row[$col_nama])) {
                continue;
            }

            $kode_ruang = trim($row[$col_kode]);
            $nama_ruang = trim($row[$col_nama]);
            $kapasitas = $col_kapasitas !== null && !empty($row[$col_kapasitas]) ? intval($row[$col_kapasitas]) : 0;
            $jenis_ruang = $col_jenis !== null ? trim($row[$col_jenis]) : 'Kelas';
            $lokasi = $col_lokasi !== null ? trim($row[$col_lokasi]) : '';
            $fasilitas = $col_fasilitas !== null ? trim($row[$col_fasilitas]) : '';
            $status = $col_status !== null ? trim(strtolower($row[$col_status])) : 'aktif';

            // Validasi jenis ruang
            $jenis_valid = array('Kelas', 'Laboratorium', 'Aula', 'Ruang Ujian', 'Lainnya');
            if (!in_array($jenis_ruang, $jenis_valid)) {
                $jenis_ruang = 'Kelas';
            }

            // Validasi status
            $status_valid = array('aktif', 'nonaktif', 'maintenance');
            if (!in_array($status, $status_valid)) {
                $status = 'aktif';
            }

            // Validasi
            if (empty($kode_ruang) || empty($nama_ruang)) {
                $error_count++;
                $errors[] = "Baris " . ($i + 1) . ": Data tidak lengkap";
                continue;
            }

            // Cek kode ruang sudah ada
            if ($CI->Ruang_kelas_model->get_by_kode($kode_ruang)) {
                $error_count++;
                $errors[] = "Baris " . ($i + 1) . ": Kode ruang $kode_ruang sudah terdaftar";
                continue;
            }

            // Insert ruang kelas
            $data = array(
                'kode_ruang' => $kode_ruang,
                'nama_ruang' => $nama_ruang,
                'kapasitas' => $kapasitas,
                'jenis_ruang' => $jenis_ruang,
                'lokasi' => $lokasi,
                'fasilitas' => $fasilitas,
                'status' => $status
            );

            $ruang_id = $CI->Ruang_kelas_model->create($data);

            if ($ruang_id) {
                $success_count++;
            } else {
                $error_count++;
                $errors[] = "Baris " . ($i + 1) . ": Gagal menyimpan data";
            }
        }

        $message = "Import selesai. Berhasil: {$success_count}, Gagal: {$error_count}";
        if (!empty($errors) && $error_count > 0) {
            $message .= "<br>Detail error: " . implode(", ", array_slice($errors, 0, 5));
            if (count($errors) > 5) {
                $message .= " dan " . (count($errors) - 5) . " error lainnya.";
            }
        }

        return array(
            'success' => $success_count > 0,
            'message' => $message,
            'success_count' => $success_count,
            'error_count' => $error_count
        );
    }

    /**
     * Baca file Excel dan import ke mapping dosen-MK
     * @param string $file_path Path ke file Excel
     * @param int $id_tahun_akademik ID tahun akademik
     * @return array ['success' => bool, 'message' => string]
     */
    public function read_excel_mapping_dosen($file_path, $id_tahun_akademik) {
        $CI =& get_instance();
        $CI->load->model('Mapping_dosen_mk_model');
        $CI->load->model('Dosen_model');
        $CI->load->model('Mata_kuliah_aktif_model');

        // Cek ekstensi file
        $ext = strtolower(pathinfo($file_path, PATHINFO_EXTENSION));
        
        if ($ext == 'xlsx') {
            return $this->read_xlsx_mapping_dosen($file_path, $id_tahun_akademik);
        } elseif ($ext == 'xls') {
            return $this->read_xls_mapping_dosen($file_path, $id_tahun_akademik);
        } elseif ($ext == 'csv') {
            return $this->read_csv_mapping_dosen($file_path, $id_tahun_akademik);
        } else {
            return array(
                'success' => false,
                'message' => 'Format file tidak didukung. Gunakan .xlsx, .xls, atau .csv'
            );
        }
    }

    /**
     * Baca file CSV untuk mapping dosen-MK
     */
    private function read_csv_mapping_dosen($file_path, $id_tahun_akademik) {
        $rows = array();
        
        if (($handle = fopen($file_path, "r")) !== FALSE) {
            while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
                $rows[] = $data;
            }
            fclose($handle);
        }

        return $this->process_mapping_dosen_data($rows, $id_tahun_akademik);
    }

    /**
     * Baca file XLSX untuk mapping dosen-MK
     */
    private function read_xlsx_mapping_dosen($file_path, $id_tahun_akademik) {
        // Coba gunakan PhpSpreadsheet jika tersedia
        if (class_exists('\PhpOffice\PhpSpreadsheet\IOFactory')) {
            try {
                $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($file_path);
                $worksheet = $spreadsheet->getActiveSheet();
                $rows = $worksheet->toArray();
                return $this->process_mapping_dosen_data($rows, $id_tahun_akademik);
            } catch (Exception $e) {
                return array(
                    'success' => false,
                    'message' => 'Error membaca file: ' . $e->getMessage()
                );
            }
        } else {
            // Fallback ke CSV
            return $this->read_csv_mapping_dosen($file_path, $id_tahun_akademik);
        }
    }

    /**
     * Baca file XLS untuk mapping dosen-MK
     */
    private function read_xls_mapping_dosen($file_path, $id_tahun_akademik) {
        // Fallback ke CSV
        return $this->read_csv_mapping_dosen($file_path, $id_tahun_akademik);
    }

    /**
     * Proses data mapping dosen-MK dari Excel
     */
    private function process_mapping_dosen_data($rows, $id_tahun_akademik) {
        $CI =& get_instance();
        $CI->load->model('Mapping_dosen_mk_model');
        $CI->load->model('Dosen_model');
        $CI->load->model('Mata_kuliah_aktif_model');

        if (empty($rows) || count($rows) < 2) {
            return array(
                'success' => false,
                'message' => 'File Excel kosong atau tidak memiliki data.'
            );
        }

        // Baris pertama adalah header
        $header = array_map('strtolower', array_map('trim', $rows[0]));
        
        // Cek kolom yang diperlukan (bisa kode_mk atau id_mata_kuliah_aktif, dan nidn atau id_dosen)
        $has_kode_mk = in_array('kode_mk', $header);
        $has_id_mk = in_array('id_mata_kuliah_aktif', $header);
        $has_nidn = in_array('nidn', $header);
        $has_id_dosen = in_array('id_dosen', $header);

        if ((!$has_kode_mk && !$has_id_mk) || (!$has_nidn && !$has_id_dosen)) {
            return array(
                'success' => false,
                'message' => 'Kolom yang diperlukan tidak ditemukan. Gunakan: (kode_mk atau id_mata_kuliah_aktif) dan (nidn atau id_dosen)'
            );
        }

        // Get index kolom
        $col_kode_mk = $has_kode_mk ? array_search('kode_mk', $header) : null;
        $col_id_mk = $has_id_mk ? array_search('id_mata_kuliah_aktif', $header) : null;
        $col_nidn = $has_nidn ? array_search('nidn', $header) : null;
        $col_id_dosen = $has_id_dosen ? array_search('id_dosen', $header) : null;

        $success_count = 0;
        $error_count = 0;
        $errors = array();

        // Proses setiap baris data (skip header)
        for ($i = 1; $i < count($rows); $i++) {
            $row = $rows[$i];
            
            // Skip baris kosong
            if (empty($row[$col_kode_mk ?? $col_id_mk]) && empty($row[$col_nidn ?? $col_id_dosen])) {
                continue;
            }

            // Ambil id_mata_kuliah_aktif
            $id_mata_kuliah_aktif = null;
            if ($col_id_mk !== null && !empty($row[$col_id_mk])) {
                $id_mata_kuliah_aktif = intval($row[$col_id_mk]);
            } elseif ($col_kode_mk !== null && !empty($row[$col_kode_mk])) {
                // Cari berdasarkan kode_mk
                $kode_mk = strtoupper(trim($row[$col_kode_mk]));
                $CI->db->select('mka.id');
                $CI->db->from('mata_kuliah_aktif mka');
                $CI->db->join('kurikulum_detail kd', 'kd.id = mka.id_kurikulum_detail', 'left');
                $CI->db->join('mata_kuliah mk', 'mk.id = kd.id_mata_kuliah', 'left');
                $CI->db->where('mka.id_tahun_akademik', $id_tahun_akademik);
                $CI->db->where('mk.kode_mk', $kode_mk);
                $mk_aktif = $CI->db->get()->row();
                if ($mk_aktif) {
                    $id_mata_kuliah_aktif = $mk_aktif->id;
                }
            }

            // Ambil id_dosen
            $id_dosen = null;
            if ($col_id_dosen !== null && !empty($row[$col_id_dosen])) {
                $id_dosen = intval($row[$col_id_dosen]);
            } elseif ($col_nidn !== null && !empty($row[$col_nidn])) {
                // Cari berdasarkan NIDN
                $nidn = trim($row[$col_nidn]);
                $dosen = $CI->Dosen_model->get_by_nidn($nidn);
                if ($dosen) {
                    $id_dosen = $dosen->id;
                }
            }

            // Validasi
            if (empty($id_mata_kuliah_aktif) || empty($id_dosen)) {
                $error_count++;
                $errors[] = "Baris " . ($i + 1) . ": Data tidak valid atau tidak ditemukan";
                continue;
            }

            // Cek apakah mapping sudah ada
            if ($CI->Mapping_dosen_mk_model->mapping_exists($id_mata_kuliah_aktif, $id_dosen)) {
                $error_count++;
                $errors[] = "Baris " . ($i + 1) . ": Mapping sudah ada";
                continue;
            }

            // Insert mapping
            $data_mapping = array(
                'id_mata_kuliah_aktif' => $id_mata_kuliah_aktif,
                'id_dosen' => $id_dosen
            );

            if ($CI->Mapping_dosen_mk_model->create($data_mapping)) {
                $success_count++;
            } else {
                $error_count++;
                $errors[] = "Baris " . ($i + 1) . ": Gagal menyimpan";
            }
        }

        $message = "Import selesai. Berhasil: {$success_count}, Gagal: {$error_count}";
        if (!empty($errors) && $error_count > 0) {
            $message .= "<br>Detail error: " . implode(", ", array_slice($errors, 0, 5));
            if (count($errors) > 5) {
                $message .= " dan " . (count($errors) - 5) . " error lainnya.";
            }
        }

        return array(
            'success' => $success_count > 0,
            'message' => $message
        );
    }
}

