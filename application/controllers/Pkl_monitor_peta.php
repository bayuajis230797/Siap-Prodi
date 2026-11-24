<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Pkl_monitor_peta Controller
 * Menampilkan peta Google Maps dengan lokasi tempat PKL semua mahasiswa
 */
class Pkl_monitor_peta extends MY_Controller {

    public function __construct() {
        parent::__construct();
        $this->check_role(array('sekprodi', 'kaprodi'));
        $this->load->model('Pkl_tempat_model');
        $this->load->model('Pkl_absensi_model');
        $this->load->model('Tahun_akademik_model');
    }

    /**
     * Index - Tampilkan peta Google Maps dengan semua lokasi PKL
     */
    public function index() {
        $tahun_aktif = $this->Tahun_akademik_model->get_aktif();
        if (!$tahun_aktif) {
            $this->session->set_flashdata('error', 'Tidak ada tahun akademik aktif.');
            redirect('Dashboard');
        }

        // Ambil semua tempat PKL yang sudah disetujui dan memiliki koordinat
        $this->db->select('pt.*, m.nim, m.nama_mahasiswa, m.kelas, ta.tahun_akademik, ta.semester,
                          pp.id as id_pengajuan, pp.judul_pkl,
                          d.nama_dosen as pembimbing_nama');
        $this->db->from('pkl_tempat pt');
        $this->db->join('mahasiswa m', 'm.id = pt.id_mahasiswa', 'inner');
        $this->db->join('tahun_akademik ta', 'ta.id = pt.id_tahun_akademik', 'inner');
        $this->db->join('pkl_pengajuan pp', 'pp.id_mahasiswa = pt.id_mahasiswa AND pp.id_tahun_akademik = pt.id_tahun_akademik', 'left');
        $this->db->join('dosen d', 'd.id = pp.id_pembimbing', 'left');
        $this->db->where('pt.id_tahun_akademik', $tahun_aktif->id);
        $this->db->where('pt.status_acc_tempat', 'disetujui');
        $this->db->where('pt.latitude IS NOT NULL');
        $this->db->where('pt.longitude IS NOT NULL');
        $this->db->order_by('m.kelas', 'ASC');
        $this->db->order_by('m.nim', 'ASC');
        $tempat_list = $this->db->get()->result();

        $anomali_map = $this->build_anomali_map($tempat_list, $tahun_aktif->id);

        // Format data untuk JavaScript
        $locations = array();
        $total_anomali_luar_radius = 0;
        $total_anomali_device = 0;
        foreach ($tempat_list as $tempat) {
            $anomali = isset($anomali_map[$tempat->id_mahasiswa]) ? $anomali_map[$tempat->id_mahasiswa] : null;
            $anomali_luar = (int) ($anomali['total_luar_radius'] ?? 0);
            $anomali_device = (int) ($anomali['total_device_berbeda'] ?? 0);
            $has_anomali = ($anomali_luar > 0 || $anomali_device > 0);
            $total_anomali_luar_radius += $anomali_luar;
            $total_anomali_device += $anomali_device;

            $anomali_lat = isset($anomali['anomali_latitude']) ? $anomali['anomali_latitude'] : null;
            $anomali_lng = isset($anomali['anomali_longitude']) ? $anomali['anomali_longitude'] : null;

            $locations[] = array(
                'id' => $tempat->id,
                'nama_instansi' => $tempat->nama_instansi,
                'alamat_instansi' => $tempat->alamat_instansi,
                'latitude' => floatval($tempat->latitude),
                'longitude' => floatval($tempat->longitude),
                'nim' => $tempat->nim,
                'nama_mahasiswa' => $tempat->nama_mahasiswa,
                'kelas' => $tempat->kelas,
                'judul_pkl' => $tempat->judul_pkl ?: '-',
                'pembimbing_nama' => $tempat->pembimbing_nama ?: '-',
                'nama_pembimbing_lapangan' => $tempat->nama_pembimbing_lapangan ?: '-',
                'no_hp_pembimbing_lapangan' => $tempat->no_hp_pembimbing_lapangan ?: '-',
                'has_anomali' => $has_anomali,
                'total_luar_radius' => $anomali_luar,
                'total_device_berbeda' => $anomali_device,
                'last_anomali_at' => $anomali['last_anomali_at'] ?? null,
                'anomali_latitude' => $anomali_lat !== null ? floatval($anomali_lat) : null,
                'anomali_longitude' => $anomali_lng !== null ? floatval($anomali_lng) : null
            );
        }

        $data = array(
            'title' => 'Monitor PKL - Peta Lokasi',
            'page_title' => 'Monitor PKL - Peta Lokasi',
            'breadcrumb' => array(
                array('label' => 'PKL', 'url' => '#'),
                array('label' => 'Monitor PKL', 'active' => true)
            ),
            'sidebar_menu' => generate_sidebar_menu(get_user_role()),
            'locations' => $locations,
            'tahun_aktif' => $tahun_aktif,
            'total_anomali_luar_radius' => $total_anomali_luar_radius,
            'total_anomali_device' => $total_anomali_device
        );

        $data['content'] = $this->load->view('sekprodi/pkl/monitor_peta', $data, true);
        $this->load->view('layouts/main', $data);
    }

    /**
     * Map agregat anomali absensi per mahasiswa untuk overlay peta.
     *
     * @param array $tempat_list
     * @param int $id_tahun_akademik
     * @return array
     */
    private function build_anomali_map($tempat_list, $id_tahun_akademik) {
        if (empty($tempat_list)) {
            return array();
        }

        $id_mahasiswa_list = array();
        foreach ($tempat_list as $tempat) {
            $id_mahasiswa_list[] = (int) $tempat->id_mahasiswa;
        }
        $id_mahasiswa_list = array_values(array_unique($id_mahasiswa_list));
        if (empty($id_mahasiswa_list)) {
            return array();
        }

        // Agregat jumlah anomali per mahasiswa.
        $this->db->select('pa.id_mahasiswa,
                          SUM(CASE WHEN pa.status_radius = "di_luar_radius" THEN 1 ELSE 0 END) as total_luar_radius,
                          SUM(CASE WHEN pa.is_device_changed = 1 THEN 1 ELSE 0 END) as total_device_berbeda,
                          MAX(CASE WHEN (pa.status_radius = "di_luar_radius" OR pa.is_device_changed = 1) THEN pa.waktu_absen END) as last_anomali_at');
        $this->db->from('pkl_absensi_harian pa');
        $this->db->where('pa.id_tahun_akademik', $id_tahun_akademik);
        $this->db->where_in('pa.id_mahasiswa', $id_mahasiswa_list);
        $this->db->group_by('pa.id_mahasiswa');
        $agg_rows = $this->db->get()->result();

        $map = array();
        foreach ($agg_rows as $row) {
            $map[(int) $row->id_mahasiswa] = array(
                'total_luar_radius' => (int) $row->total_luar_radius,
                'total_device_berbeda' => (int) $row->total_device_berbeda,
                'last_anomali_at' => $row->last_anomali_at
            );
        }

        // Ambil titik absensi anomali terbaru per mahasiswa (untuk marker overlay).
        $this->db->select('pa.id_mahasiswa, pa.latitude_absen, pa.longitude_absen, pa.waktu_absen');
        $this->db->from('pkl_absensi_harian pa');
        $this->db->where('pa.id_tahun_akademik', $id_tahun_akademik);
        $this->db->where_in('pa.id_mahasiswa', $id_mahasiswa_list);
        $this->db->where('(pa.status_radius = "di_luar_radius" OR pa.is_device_changed = 1)', null, false);
        $this->db->order_by('pa.id_mahasiswa', 'ASC');
        $this->db->order_by('pa.waktu_absen', 'DESC');
        $anomali_rows = $this->db->get()->result();

        $seen = array();
        foreach ($anomali_rows as $row) {
            $id_mhs = (int) $row->id_mahasiswa;
            if (isset($seen[$id_mhs])) {
                continue;
            }
            $seen[$id_mhs] = true;
            if (!isset($map[$id_mhs])) {
                $map[$id_mhs] = array(
                    'total_luar_radius' => 0,
                    'total_device_berbeda' => 0,
                    'last_anomali_at' => $row->waktu_absen
                );
            }
            $map[$id_mhs]['anomali_latitude'] = $row->latitude_absen;
            $map[$id_mhs]['anomali_longitude'] = $row->longitude_absen;
        }

        return $map;
    }
}

