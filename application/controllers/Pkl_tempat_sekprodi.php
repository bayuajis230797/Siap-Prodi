<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Pkl_tempat_sekprodi Controller
 * Mengelola pengajuan tempat PKL untuk Sekprodi (approve/reject dan tentukan pembimbing)
 */
class Pkl_tempat_sekprodi extends MY_Controller {

    public function __construct() {
        parent::__construct();
        $this->check_role(array('admin', 'sekprodi', 'kaprodi'));
        $this->load->model('Pkl_tempat_model');
        $this->load->model('Pkl_pengajuan_model');
        $this->load->model('Dosen_model');
        $this->load->model('Tahun_akademik_model');
        $this->load->model('Mahasiswa_model');
        $this->load->library('pagination');
        $this->load->library('form_validation');
        $this->load->helper('notifikasi');
    }

    /**
     * Daftar pengajuan tempat PKL
     */
    public function index() {
        $tahun_aktif = $this->Tahun_akademik_model->get_aktif();
        $id_tahun_akademik = $tahun_aktif ? $tahun_aktif->id : null;

        // Filter status
        $filter_status = $this->input->get('status');
        $per_page = $this->input->get('per_page') ? (int)$this->input->get('per_page') : 10;
        $per_page = in_array($per_page, [10, 25, 50, 100]) ? $per_page : 10;

        // Pagination config
        $config['base_url'] = site_url('Pkl_tempat_sekprodi/index');
        $config['total_rows'] = $this->Pkl_tempat_model->count_all($id_tahun_akademik, 'selesai', $filter_status);
        $config['per_page'] = $per_page;
        $config['uri_segment'] = 3;
        
        // Build query string
        $query_params = array_filter(array(
            'per_page' => ($per_page != 10 ? $per_page : null),
            'status' => $filter_status
        ));
        $query_string = http_build_query($query_params);
        if ($query_string) {
            $config['suffix'] = '?' . $query_string;
            $config['first_url'] = site_url('Pkl_tempat_sekprodi/index?' . $query_string);
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
        $config['cur_tag_open'] = '<li class="page-item active"><span class="page-link">';
        $config['cur_tag_close'] = '</span></li>';
        $config['num_tag_open'] = '<li class="page-item">';
        $config['num_tag_close'] = '</li>';
        $config['attributes'] = array('class' => 'page-link');

        $this->pagination->initialize($config);
        $page = ($this->uri->segment(3)) ? $this->uri->segment(3) : 1;
        $offset = ($page - 1) * $per_page;

        $tempat_list = $this->Pkl_tempat_model->get_all($id_tahun_akademik, 'selesai', $filter_status, $per_page, $offset);

        $data = array(
            'title' => 'Pengajuan Tempat PKL',
            'page_title' => 'Pengajuan Tempat PKL',
            'breadcrumb' => array(
                array('label' => 'PKL', 'url' => '#'),
                array('label' => 'Pengajuan Tempat PKL', 'active' => true)
            ),
            'sidebar_menu' => generate_sidebar_menu(get_user_role()),
            'tempat_list' => $tempat_list,
            'tahun_aktif' => $tahun_aktif,
            'filter_status' => $filter_status,
            'pagination' => $this->pagination->create_links(),
            'per_page' => $per_page
        );

        $data['content'] = $this->load->view('sekprodi/pkl_tempat_index', $data, true);
        $this->load->view('layouts/main', $data);
    }

    /**
     * Detail pengajuan tempat PKL
     */
    public function detail($id) {
        $tempat = $this->Pkl_tempat_model->get_by_id($id);
        
        if (!$tempat) {
            $this->session->set_flashdata('error', 'Data pengajuan tempat PKL tidak ditemukan.');
            redirect('Pkl_tempat_sekprodi');
        }

        // Ambil data pembimbing jika sudah ditentukan
        $pengajuan = $this->Pkl_pengajuan_model->get_by_mahasiswa($tempat->id_mahasiswa, $tempat->id_tahun_akademik);
        
        // Ambil daftar dosen pembimbing PKL
        // Ambil semua dosen aktif untuk ditampilkan di dropdown
        // Jika ingin membatasi, bisa menggunakan kolom is_pembimbing_pkl nanti
        $dosen_pembimbing = $this->Dosen_model->get_all_active();
        
        // Hitung progress PKL mahasiswa
        $this->load->helper('pkl');
        $tahun_aktif = $this->Tahun_akademik_model->get_by_id($tempat->id_tahun_akademik);
        $pkl_progress = calculate_pkl_progress($tempat->id_mahasiswa, $tahun_aktif);

        $data = array(
            'title' => 'Detail Pengajuan Tempat PKL',
            'page_title' => 'Detail Pengajuan Tempat PKL',
            'breadcrumb' => array(
                array('label' => 'PKL', 'url' => '#'),
                array('label' => 'Pengajuan Tempat PKL', 'url' => site_url('Pkl_tempat_sekprodi')),
                array('label' => 'Detail', 'active' => true)
            ),
            'sidebar_menu' => generate_sidebar_menu(get_user_role()),
            'tempat' => $tempat,
            'pengajuan' => $pengajuan,
            'dosen_pembimbing' => $dosen_pembimbing,
            'pkl_progress' => $pkl_progress
        );

        $data['content'] = $this->load->view('sekprodi/pkl_tempat_detail', $data, true);
        $this->load->view('layouts/main', $data);
    }

    /**
     * Proses approve/reject tempat PKL
     */
    public function proses($id) {
        $this->assert_not_kaprodi('Pkl_tempat_sekprodi');
        $tempat = $this->Pkl_tempat_model->get_by_id($id);
        
        if (!$tempat) {
            $this->session->set_flashdata('error', 'Data pengajuan tempat PKL tidak ditemukan.');
            redirect('Pkl_tempat_sekprodi');
        }

        if ($this->input->method() === 'post') {
            $status_acc = $this->input->post('status_acc_tempat');
            $catatan = $this->input->post('catatan_penolakan');
            $id_pembimbing = $this->input->post('id_pembimbing');

            $this->form_validation->set_rules('status_acc_tempat', 'Status', 'required|in_list[disetujui,ditolak]');
            
            if ($status_acc == 'disetujui') {
                $this->form_validation->set_rules('id_pembimbing', 'Pembimbing', 'required|integer|greater_than[0]', 
                    array('required' => 'Pembimbing wajib dipilih jika status disetujui.', 
                          'greater_than' => 'Pilih pembimbing yang valid.'));
            }

            if ($this->form_validation->run() == FALSE) {
                redirect('Pkl_tempat_sekprodi/detail/' . $id);
            }

            // Update status tempat
            $data_tempat = array(
                'status_acc_tempat' => $status_acc
            );

            if ($status_acc == 'ditolak' && $catatan) {
                $data_tempat['catatan_penolakan'] = $catatan;
            } else {
                $data_tempat['catatan_penolakan'] = null;
            }

            if ($this->Pkl_tempat_model->update($id, $data_tempat)) {
                // Jika disetujui, buat atau update pengajuan PKL dengan pembimbing
                if ($status_acc == 'disetujui' && $id_pembimbing) {
                    $dosen_pembimbing = $this->Dosen_model->get_by_id($id_pembimbing);
                    $nama_pembimbing = $dosen_pembimbing ? $dosen_pembimbing->nama_dosen : 'Dosen pembimbing';

                    // Cek apakah sudah ada pengajuan
                    $pengajuan_existing = $this->Pkl_pengajuan_model->get_by_mahasiswa($tempat->id_mahasiswa, $tempat->id_tahun_akademik);
                    
                    if ($pengajuan_existing) {
                        // Update pembimbing
                        $this->Pkl_pengajuan_model->update($pengajuan_existing->id, array(
                            'id_pembimbing' => $id_pembimbing
                        ));
                    } else {
                        // Buat pengajuan baru dengan placeholder judul
                        $data_pengajuan = array(
                            'id_mahasiswa' => $tempat->id_mahasiswa,
                            'id_tahun_akademik' => $tempat->id_tahun_akademik,
                            'id_pembimbing' => $id_pembimbing,
                            'judul_pkl' => 'Menunggu Pengajuan Judul',
                            'status' => 'pending',
                            'status_acc_judul' => 'pending'
                        );
                        $this->Pkl_pengajuan_model->create($data_pengajuan);
                    }

                    send_in_app_notification_by_ref(
                        'mahasiswa',
                        (int) $tempat->id_mahasiswa,
                        'Tempat PKL Disetujui',
                        'Tempat PKL Anda disetujui. Pembimbing ditetapkan: ' . $nama_pembimbing . '. Silakan lanjutkan pengajuan judul PKL.',
                        site_url('Pkl')
                    );
                } elseif ($status_acc == 'ditolak') {
                    $pesan = 'Pengajuan tempat PKL ditolak. Silakan perbaiki data tempat.';
                    if (!empty($catatan)) {
                        $pesan .= ' Catatan: ' . trim((string) $catatan);
                    }
                    send_in_app_notification_by_ref(
                        'mahasiswa',
                        (int) $tempat->id_mahasiswa,
                        'Tempat PKL Ditolak',
                        $pesan,
                        site_url('Pkl/input_tempat')
                    );
                }

                $this->session->set_flashdata('success', 'Pengajuan tempat PKL berhasil diproses.');
            } else {
                $this->session->set_flashdata('error', 'Gagal memproses pengajuan tempat PKL.');
            }
        }

        redirect('Pkl_tempat_sekprodi/detail/' . $id);
    }

    /**
     * Ubah dosen pembimbing untuk pengajuan tempat PKL yang sudah disetujui.
     */
    public function update_pembimbing($id) {
        $this->assert_not_kaprodi('Pkl_tempat_sekprodi');
        $tempat = $this->Pkl_tempat_model->get_by_id($id);
        if (!$tempat) {
            $this->session->set_flashdata('error', 'Data pengajuan tempat PKL tidak ditemukan.');
            redirect('Pkl_tempat_sekprodi');
        }

        if ($this->input->method() !== 'post') {
            redirect('Pkl_tempat_sekprodi/detail/' . $id);
        }

        if (($tempat->status_acc_tempat ?? '') !== 'disetujui') {
            $this->session->set_flashdata('error', 'Dosen pembimbing hanya dapat diubah untuk pengajuan yang sudah disetujui.');
            redirect('Pkl_tempat_sekprodi/detail/' . $id);
        }

        $this->form_validation->set_rules(
            'id_pembimbing',
            'Dosen Pembimbing',
            'required|integer|greater_than[0]',
            array(
                'required' => 'Dosen pembimbing wajib dipilih.',
                'greater_than' => 'Pilih dosen pembimbing yang valid.'
            )
        );

        if ($this->form_validation->run() === FALSE) {
            $this->session->set_flashdata('error', validation_errors());
            redirect('Pkl_tempat_sekprodi/detail/' . $id);
        }

        $id_pembimbing = (int) $this->input->post('id_pembimbing');
        $dosen_pembimbing = $this->Dosen_model->get_by_id($id_pembimbing);
        if (!$dosen_pembimbing || (($dosen_pembimbing->status ?? '') !== 'aktif')) {
            $this->session->set_flashdata('error', 'Dosen pembimbing tidak valid atau tidak aktif.');
            redirect('Pkl_tempat_sekprodi/detail/' . $id);
        }

        $pengajuan = $this->Pkl_pengajuan_model->get_by_mahasiswa($tempat->id_mahasiswa, $tempat->id_tahun_akademik);
        if ($pengajuan) {
            $this->Pkl_pengajuan_model->update($pengajuan->id, array('id_pembimbing' => $id_pembimbing));
        } else {
            $this->Pkl_pengajuan_model->create(array(
                'id_mahasiswa' => $tempat->id_mahasiswa,
                'id_tahun_akademik' => $tempat->id_tahun_akademik,
                'id_pembimbing' => $id_pembimbing,
                'judul_pkl' => Pkl_pengajuan_model::PLACEHOLDER_JUDUL,
                'status' => 'pending',
                'status_acc_judul' => 'pending'
            ));
        }

        send_in_app_notification_by_ref(
            'mahasiswa',
            (int) $tempat->id_mahasiswa,
            'Perubahan Dosen Pembimbing PKL',
            'Dosen pembimbing PKL Anda diperbarui menjadi: ' . $dosen_pembimbing->nama_dosen . '.',
            site_url('Pkl')
        );

        $this->session->set_flashdata('success', 'Dosen pembimbing berhasil diperbarui.');
        redirect('Pkl_tempat_sekprodi/detail/' . $id);
    }

    /**
     * Export Excel - Data Detail Pengajuan Tempat PKL
     */
    public function export_excel() {
        $tahun_aktif = $this->Tahun_akademik_model->get_aktif();
        $id_tahun_akademik = $tahun_aktif ? $tahun_aktif->id : null;
        
        $filter_status = $this->input->get('status');
        
        // Ambil semua data tanpa pagination
        $tempat_list = $this->Pkl_tempat_model->get_all($id_tahun_akademik, 'selesai', $filter_status, null, null);
        
        // Siapkan data untuk export
        $data_export = array();
        foreach ($tempat_list as $tempat) {
            // Ambil data pembimbing jika sudah ditentukan
            $pengajuan = $this->Pkl_pengajuan_model->get_by_mahasiswa($tempat->id_mahasiswa, $tempat->id_tahun_akademik);
            $dosen_pembimbing = null;
            if ($pengajuan && $pengajuan->id_pembimbing) {
                $dosen_pembimbing = $this->Dosen_model->get_by_id($pengajuan->id_pembimbing);
            }
            
            $data_export[] = array(
                'nim' => $tempat->nim,
                'nama_mahasiswa' => $tempat->nama_mahasiswa,
                'kelas' => $tempat->kelas ?: '-',
                'tahun_akademik' => $tempat->tahun_akademik . ' ' . ucfirst($tempat->semester),
                'nama_instansi' => $tempat->nama_instansi,
                'alamat_instansi' => $tempat->alamat_instansi ?: '-',
                'tanggal_mulai' => $tempat->tanggal_mulai ? date('d/m/Y', strtotime($tempat->tanggal_mulai)) : '-',
                'tanggal_selesai' => $tempat->tanggal_selesai ? date('d/m/Y', strtotime($tempat->tanggal_selesai)) : '-',
                'latitude' => $tempat->latitude ? number_format($tempat->latitude, 8) : '-',
                'longitude' => $tempat->longitude ? number_format($tempat->longitude, 8) : '-',
                'status' => ucfirst($tempat->status_acc_tempat ?: 'Pending'),
                'nama_pembimbing_lapangan' => $tempat->nama_pembimbing_lapangan ?: '-',
                'jabatan_pembimbing_lapangan' => $tempat->jabatan_pembimbing_lapangan ?: '-',
                'no_hp_pembimbing_lapangan' => $tempat->no_hp_pembimbing_lapangan ?: '-',
                'email_pembimbing_lapangan' => $tempat->email_pembimbing_lapangan ?: '-',
                'dosen_pembimbing' => $dosen_pembimbing ? $dosen_pembimbing->nama_dosen : '-',
                'catatan_penolakan' => $tempat->catatan_penolakan ?: '-'
            );
        }

        // Cek apakah PhpSpreadsheet tersedia
        if (class_exists('PhpOffice\PhpSpreadsheet\Spreadsheet')) {
            require_once APPPATH . 'vendor/autoload.php';
            
            $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet();
            
            // Set title
            $sheet->setTitle('Data Pengajuan Tempat PKL');
            
            // Header
            $sheet->setCellValue('A1', 'No');
            $sheet->setCellValue('B1', 'NIM');
            $sheet->setCellValue('C1', 'Nama Mahasiswa');
            $sheet->setCellValue('D1', 'Kelas');
            $sheet->setCellValue('E1', 'Tahun Akademik');
            $sheet->setCellValue('F1', 'Nama Instansi');
            $sheet->setCellValue('G1', 'Alamat Instansi');
            $sheet->setCellValue('H1', 'Tanggal Mulai');
            $sheet->setCellValue('I1', 'Tanggal Selesai');
            $sheet->setCellValue('J1', 'Latitude');
            $sheet->setCellValue('K1', 'Longitude');
            $sheet->setCellValue('L1', 'Status');
            $sheet->setCellValue('M1', 'Nama Pembimbing Lapangan');
            $sheet->setCellValue('N1', 'Jabatan Pembimbing Lapangan');
            $sheet->setCellValue('O1', 'No HP Pembimbing Lapangan');
            $sheet->setCellValue('P1', 'Email Pembimbing Lapangan');
            $sheet->setCellValue('Q1', 'Dosen Pembimbing');
            $sheet->setCellValue('R1', 'Catatan Penolakan');
            
            // Style header
            $sheet->getStyle('A1:R1')->getFont()->setBold(true);
            $sheet->getStyle('A1:R1')->getFill()
                ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                ->getStartColor()->setARGB('FF4472C4');
            $sheet->getStyle('A1:R1')->getFont()->getColor()->setARGB('FFFFFFFF');
            $sheet->getStyle('A1:R1')->getAlignment()
                ->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER)
                ->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER);
            
            // Data
            $row = 2;
            $no = 1;
            foreach ($data_export as $data) {
                $sheet->setCellValue('A' . $row, $no++);
                $sheet->setCellValue('B' . $row, $data['nim']);
                $sheet->setCellValue('C' . $row, $data['nama_mahasiswa']);
                $sheet->setCellValue('D' . $row, $data['kelas']);
                $sheet->setCellValue('E' . $row, $data['tahun_akademik']);
                $sheet->setCellValue('F' . $row, $data['nama_instansi']);
                $sheet->setCellValue('G' . $row, $data['alamat_instansi']);
                $sheet->setCellValue('H' . $row, $data['tanggal_mulai']);
                $sheet->setCellValue('I' . $row, $data['tanggal_selesai']);
                $sheet->setCellValue('J' . $row, $data['latitude']);
                $sheet->setCellValue('K' . $row, $data['longitude']);
                $sheet->setCellValue('L' . $row, $data['status']);
                $sheet->setCellValue('M' . $row, $data['nama_pembimbing_lapangan']);
                $sheet->setCellValue('N' . $row, $data['jabatan_pembimbing_lapangan']);
                $sheet->setCellValue('O' . $row, $data['no_hp_pembimbing_lapangan']);
                $sheet->setCellValue('P' . $row, $data['email_pembimbing_lapangan']);
                $sheet->setCellValue('Q' . $row, $data['dosen_pembimbing']);
                $sheet->setCellValue('R' . $row, $data['catatan_penolakan']);
                $row++;
            }
            
            // Auto size columns
            foreach (range('A', 'R') as $col) {
                $sheet->getColumnDimension($col)->setAutoSize(true);
            }
            
            // Set borders
            $styleArray = [
                'borders' => [
                    'allBorders' => [
                        'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                    ],
                ],
            ];
            $sheet->getStyle('A1:R' . ($row - 1))->applyFromArray($styleArray);
            
            // Set filename
            $filename = 'Data_Pengajuan_Tempat_PKL_' . date('Ymd_His') . '.xlsx';
            
            // Download
            header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
            header('Content-Disposition: attachment;filename="' . $filename . '"');
            header('Cache-Control: max-age=0');
            
            $writer = \PhpOffice\PhpSpreadsheet\IOFactory::createWriter($spreadsheet, 'Xlsx');
            $writer->save('php://output');
            exit;
        } else {
            // Fallback: download CSV
            $filename = 'Data_Pengajuan_Tempat_PKL_' . date('Ymd_His') . '.csv';
            header('Content-Type: text/csv; charset=utf-8');
            header('Content-Disposition: attachment;filename="' . $filename . '"');
            header('Cache-Control: max-age=0');
            
            $output = fopen('php://output', 'w');
            
            // BOM untuk UTF-8 (agar Excel membaca dengan benar)
            fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));
            
            // Header
            fputcsv($output, array(
                'No', 'NIM', 'Nama Mahasiswa', 'Kelas', 'Tahun Akademik', 'Nama Instansi', 
                'Alamat Instansi', 'Tanggal Mulai', 'Tanggal Selesai', 'Latitude', 'Longitude', 
                'Status', 'Nama Pembimbing Lapangan', 'Jabatan Pembimbing Lapangan', 
                'No HP Pembimbing Lapangan', 'Email Pembimbing Lapangan', 'Dosen Pembimbing', 
                'Catatan Penolakan'
            ));
            
            // Data
            $no = 1;
            foreach ($data_export as $data) {
                fputcsv($output, array(
                    $no++,
                    $data['nim'],
                    $data['nama_mahasiswa'],
                    $data['kelas'],
                    $data['tahun_akademik'],
                    $data['nama_instansi'],
                    $data['alamat_instansi'],
                    $data['tanggal_mulai'],
                    $data['tanggal_selesai'],
                    $data['latitude'],
                    $data['longitude'],
                    $data['status'],
                    $data['nama_pembimbing_lapangan'],
                    $data['jabatan_pembimbing_lapangan'],
                    $data['no_hp_pembimbing_lapangan'],
                    $data['email_pembimbing_lapangan'],
                    $data['dosen_pembimbing'],
                    $data['catatan_penolakan']
                ));
            }
            
            fclose($output);
            exit;
        }
    }
}

