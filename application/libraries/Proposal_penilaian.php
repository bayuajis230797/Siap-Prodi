<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Proposal_penilaian
 * Sinkronisasi nilai bimbingan (1 pembimbing), nilai sidang (2 penguji) dan nilai akhir
 * ke tabel mahasiswa_proposal.
 */
class Proposal_penilaian {

    protected $CI;

    public function __construct() {
        $this->CI =& get_instance();
        $this->CI->load->model('Proposal_model');
        $this->CI->load->model('Proposal_nilai_bimbingan_model');
        $this->CI->load->model('Proposal_nilai_sidang_model');
    }

    public function sync_bimbingan($id_mahasiswa_proposal) {
        $record = $this->CI->Proposal_nilai_bimbingan_model->get_row_by_proposal($id_mahasiswa_proposal);

        $this->CI->Proposal_model->update($id_mahasiswa_proposal, array(
            'nilai_bimbingan' => $record ? (float) $record->nilai_rata : null
        ));
        $this->sync_final($id_mahasiswa_proposal);
    }

    public function sync_sidang($id_mahasiswa_proposal) {
        $avg = $this->CI->Proposal_nilai_sidang_model->get_average_by_proposal($id_mahasiswa_proposal);
        $this->CI->Proposal_model->update($id_mahasiswa_proposal, array(
            'nilai_sidang' => $avg !== null ? round($avg, 2) : null
        ));
        $this->sync_final($id_mahasiswa_proposal);
    }

    private function sync_final($id_mahasiswa_proposal) {
        $proposal = $this->CI->Proposal_model->get_by_id($id_mahasiswa_proposal);
        if (!$proposal) {
            return;
        }

        $nilai_final = null;
        $nilai_huruf = null;

        if ($proposal->nilai_bimbingan !== null && $proposal->nilai_sidang !== null) {
            $this->CI->load->model('Bobot_nilai_setting_model');
            $nilai_final = $this->CI->Bobot_nilai_setting_model->hitung_nilai_akhir_proposal(
                $proposal->nilai_bimbingan,
                $proposal->nilai_sidang
            );

            $this->CI->load->model('Grade_setting_model');
            $nilai_huruf = $this->CI->Grade_setting_model->konversi_nilai_ke_grade($nilai_final, 'proposal');
        }

        $this->CI->Proposal_model->update($id_mahasiswa_proposal, array(
            'nilai_proposal_final' => $nilai_final,
            'nilai_huruf' => $nilai_huruf
        ));
    }
}
