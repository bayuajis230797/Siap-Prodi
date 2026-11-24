<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Excel Library
 * Library untuk import/export Excel
 * 
 * Catatan: Library ini memerlukan PhpSpreadsheet atau PHPExcel
 * Install via Composer: composer require phpoffice/phpspreadsheet
 */
class Excel {

    protected $CI;

    public function __construct() {
        $this->CI =& get_instance();
    }

    /**
     * Baca data mahasiswa dari file Excel
     * Format: NIM | Nama | Angkatan | ID Kurikulum | Status
     */
    public function read_mahasiswa($file_path) {
        $result = array(
            'success' => false,
            'message' => '',
            'success_count' => 0,
            'error_count' => 0,
            'errors' => array()
        );

        try {
            // Cek apakah PhpSpreadsheet tersedia
            if (!class_exists('PhpOffice\PhpSpreadsheet\Spreadsheet')) {
                // Fallback: gunakan metode sederhana dengan CSV
                return $this->read_mahasiswa_csv($file_path);
            }

            // Load PhpSpreadsheet
            require_once APPPATH . 'vendor/autoload.php';
            
            $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($file_path);
            $worksheet = $spreadsheet->getActiveSheet();
            $rows = $worksheet->toArray();

            // Skip header row (row 1)
            array_shift($rows);

            $this->CI->load->model('Mahasiswa_model');
            $this->CI->load->model('Kurikulum_model');
            $this->CI->load->model('Tahun_akademik_model');

            foreach ($rows as $row_num => $row) {
                // Skip baris kosong
                if (empty($row[0])) continue;

                $nim = trim($row[0]);
                $nama = trim($row[1]);
                $angkatan = intval($row[2]);
                $id_kurikulum = intval($row[3]);
                $status = isset($row[4]) ? trim(strtolower($row[4])) : 'aktif';

                // Validasi
                if (empty($nim) || empty($nama) || empty($angkatan) || empty($id_kurikulum)) {
                    $result['error_count']++;
                    $result['errors'][] = "Baris " . ($row_num + 2) . ": Data tidak lengkap";
                    continue;
                }

                // Cek NIM sudah ada
                if ($this->CI->Mahasiswa_model->get_by_nim($nim)) {
                    $result['error_count']++;
                    $result['errors'][] = "Baris " . ($row_num + 2) . ": NIM $nim sudah terdaftar";
                    continue;
                }

                // Cek kurikulum ada
                $kurikulum = $this->CI->Kurikulum_model->get_by_id($id_kurikulum);
                if (!$kurikulum) {
                    $result['error_count']++;
                    $result['errors'][] = "Baris " . ($row_num + 2) . ": Kurikulum ID $id_kurikulum tidak ditemukan";
                    continue;
                }

                // Hitung semester aktif
                $this->CI->load->library('Kenaikan_kelas');
                $attr = $this->CI->kenaikan_kelas->atribut_baru($angkatan);
                $semester_aktif = $attr['semester_aktif'];

                // Insert mahasiswa
                $data = array(
                    'nim' => $nim,
                    'nama_mahasiswa' => $nama,
                    'angkatan' => $angkatan,
                    'semester_aktif' => $semester_aktif,
                    'id_kurikulum' => $id_kurikulum,
                    'status' => $status
                );
                if (isset($attr['semester_riil'])) {
                    $data['semester_riil'] = $attr['semester_riil'];
                }

                $mahasiswa_id = $this->CI->Mahasiswa_model->create($data);

                if ($mahasiswa_id) {
                    // Buat user account
                    $this->create_user_account($nim, $mahasiswa_id);
                    $result['success_count']++;
                } else {
                    $result['error_count']++;
                    $result['errors'][] = "Baris " . ($row_num + 2) . ": Gagal menyimpan data";
                }
            }

            $result['success'] = true;
            $result['message'] = 'Import selesai';

        } catch (Exception $e) {
            $result['message'] = 'Error: ' . $e->getMessage();
        }

        return $result;
    }

    /**
     * Fallback: Baca dari CSV (jika Excel library tidak tersedia)
     */
    private function read_mahasiswa_csv($file_path) {
        $result = array(
            'success' => false,
            'message' => 'Library PhpSpreadsheet tidak tersedia. Silakan install via Composer: composer require phpoffice/phpspreadsheet',
            'success_count' => 0,
            'error_count' => 0,
            'errors' => array()
        );

        return $result;
    }

    /**
     * Download template Excel untuk import mahasiswa
     */
    public function download_mahasiswa_template() {
        // Buat array data untuk template
        $data = array(
            array('NIM', 'Nama Mahasiswa', 'Angkatan', 'ID Kurikulum', 'Status'),
            array('202001001', 'Contoh Mahasiswa 1', '2020', '1', 'aktif'),
            array('202001002', 'Contoh Mahasiswa 2', '2020', '1', 'aktif'),
        );

        // Cek apakah PhpSpreadsheet tersedia
        if (class_exists('PhpOffice\PhpSpreadsheet\Spreadsheet')) {
            require_once APPPATH . 'vendor/autoload.php';
            
            $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet();
            
            // Set header
            $sheet->setCellValue('A1', 'NIM');
            $sheet->setCellValue('B1', 'Nama Mahasiswa');
            $sheet->setCellValue('C1', 'Angkatan');
            $sheet->setCellValue('D1', 'ID Kurikulum');
            $sheet->setCellValue('E1', 'Status');
            
            // Set contoh data
            $sheet->setCellValue('A2', '202001001');
            $sheet->setCellValue('B2', 'Contoh Mahasiswa 1');
            $sheet->setCellValue('C2', '2020');
            $sheet->setCellValue('D2', '1');
            $sheet->setCellValue('E2', 'aktif');
            
            // Style header
            $sheet->getStyle('A1:E1')->getFont()->setBold(true);
            $sheet->getStyle('A1:E1')->getFill()
                ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                ->getStartColor()->setARGB('FFE0E0E0');
            
            // Auto size columns
            foreach (range('A', 'E') as $col) {
                $sheet->getColumnDimension($col)->setAutoSize(true);
            }
            
            // Download
            header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
            header('Content-Disposition: attachment;filename="template_import_mahasiswa.xlsx"');
            header('Cache-Control: max-age=0');
            
            $writer = \PhpOffice\PhpSpreadsheet\IOFactory::createWriter($spreadsheet, 'Xlsx');
            $writer->save('php://output');
            exit;
        } else {
            // Fallback: download CSV
            header('Content-Type: text/csv');
            header('Content-Disposition: attachment;filename="template_import_mahasiswa.csv"');
            
            $output = fopen('php://output', 'w');
            fputcsv($output, array('NIM', 'Nama Mahasiswa', 'Angkatan', 'ID Kurikulum', 'Status'));
            fputcsv($output, array('202001001', 'Contoh Mahasiswa 1', '2020', '1', 'aktif'));
            fclose($output);
            exit;
        }
    }

    /**
     * Hitung semester aktif
     */
    private function calculate_semester_aktif($angkatan) {
        $this->CI->load->library('Kenaikan_kelas');
        $a = $this->CI->kenaikan_kelas->atribut_baru($angkatan);
        return $a['semester_aktif'];
    }

    /**
     * Buat user account
     */
    private function create_user_account($nim, $mahasiswa_id) {
        $this->CI->db->where('nama_role', 'mahasiswa');
        $role = $this->CI->db->get('roles')->row();
        
        if (!$role) {
            return false;
        }
        
        $this->CI->db->where('username', $nim);
        $existing = $this->CI->db->get('users')->row();
        
        if ($existing) {
            $this->CI->db->where('id', $existing->id);
            $this->CI->db->update('users', array('ref_id' => $mahasiswa_id));
            return true;
        }
        
        $data = array(
            'username' => $nim,
            'password' => md5($nim),
            'role_id' => $role->id,
            'ref_id' => $mahasiswa_id
        );
        
        $this->CI->db->insert('users', $data);
        return $this->CI->db->insert_id();
    }
}
















