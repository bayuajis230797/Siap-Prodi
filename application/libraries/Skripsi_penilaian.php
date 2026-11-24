<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Skripsi_penilaian {

    protected $CI;

    public function __construct() {
        $this->CI =& get_instance();
        $this->CI->load->model('Skripsi_model');
        $this->CI->load->model('Skripsi_nilai_bimbingan_model');
        $this->CI->load->model('Skripsi_nilai_sidang_model');
    }

    public function sync_bimbingan($id_mahasiswa_skripsi) {
        $records = $this->CI->Skripsi_nilai_bimbingan_model->get_by_skripsi($id_mahasiswa_skripsi);

        $nilai_p1 = null;
        $nilai_p2 = null;

        foreach ($records as $record) {
            if ((int)$record->tipe_pembimbing === 1) {
                $nilai_p1 = (float)$record->nilai_rata;
            } elseif ((int)$record->tipe_pembimbing === 2) {
                $nilai_p2 = (float)$record->nilai_rata;
            }
        }

        $update = array(
            'nilai_bimbingan_p1' => $nilai_p1,
            'nilai_bimbingan_p2' => $nilai_p2
        );

        $values = array_filter(array($nilai_p1, $nilai_p2), function($value) {
            return $value !== null;
        });

        if (!empty($values)) {
            $update['nilai_bimbingan_final'] = round(array_sum($values) / count($values), 2);
        } else {
            $update['nilai_bimbingan_final'] = null;
        }

        $this->CI->Skripsi_model->update($id_mahasiswa_skripsi, $update);
        $this->sync_final($id_mahasiswa_skripsi);
    }

    public function sync_sidang($id_mahasiswa_skripsi) {
        $avg = $this->CI->Skripsi_nilai_sidang_model->get_average_by_skripsi($id_mahasiswa_skripsi);
        $this->CI->Skripsi_model->update($id_mahasiswa_skripsi, array(
            'nilai_sidang' => $avg
        ));
        $this->sync_final($id_mahasiswa_skripsi);
    }

    private function sync_final($id_mahasiswa_skripsi) {
        $skripsi = $this->CI->Skripsi_model->get_by_id($id_mahasiswa_skripsi);
        if (!$skripsi) {
            return;
        }

        $nilai_skripsi = null;
        $nilai_huruf = null;

        if ($skripsi->nilai_bimbingan_final !== null && $skripsi->nilai_sidang !== null) {
            // Gunakan bobot dari database
            $this->CI->load->model('Bobot_nilai_setting_model');
            $nilai_skripsi = $this->CI->Bobot_nilai_setting_model->hitung_nilai_akhir_skripsi(
                $skripsi->nilai_bimbingan_final, 
                $skripsi->nilai_sidang
            );
            
            // Gunakan grade setting dari database
            $this->CI->load->model('Grade_setting_model');
            $nilai_huruf = $this->CI->Grade_setting_model->konversi_nilai_ke_grade($nilai_skripsi, 'skripsi');
        }

        $this->CI->Skripsi_model->update($id_mahasiswa_skripsi, array(
            'nilai_skripsi_final' => $nilai_skripsi,
            'nilai_huruf' => $nilai_huruf
        ));
    }
}













