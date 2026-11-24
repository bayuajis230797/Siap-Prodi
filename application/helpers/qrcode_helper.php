<?php
defined('BASEPATH') OR exit('No direct script access allowed');

if (!function_exists('qr_code_storage_dir')) {
    function qr_code_storage_dir() {
        $dir = APPPATH . 'storage' . DIRECTORY_SEPARATOR . 'qrcode' . DIRECTORY_SEPARATOR;
        if (!is_dir($dir)) {
            mkdir($dir, 0750, true);
        }
        return $dir;
    }
}

if (!function_exists('qr_code_resolve_full_path')) {
    /**
     * Resolve path relatif DB ke path absolut di server.
     */
    function qr_code_resolve_full_path($relative_path) {
        if (empty($relative_path)) {
            return null;
        }
        $relative_path = str_replace('\\', '/', trim($relative_path));

        if (strpos($relative_path, 'private/qrcode/') === 0) {
            $file = substr($relative_path, strlen('private/qrcode/'));
            $full = qr_code_storage_dir() . str_replace('/', DIRECTORY_SEPARATOR, $file);
            return file_exists($full) ? $full : null;
        }

        if (strpos($relative_path, 'uploads/qrcode/') === 0) {
            $full = FCPATH . str_replace('/', DIRECTORY_SEPARATOR, $relative_path);
            return file_exists($full) ? $full : null;
        }

        return null;
    }
}

if (!function_exists('qr_code_serve_url_jadwal_uts')) {
    function qr_code_serve_url_jadwal_uts($id) {
        return site_url('File_asset/jadwal_uts_qr/' . (int) $id);
    }
}

if (!function_exists('qr_code_serve_url_jadwal_uas')) {
    function qr_code_serve_url_jadwal_uas($id) {
        return site_url('File_asset/jadwal_uas_qr/' . (int) $id);
    }
}

if (!function_exists('qr_code_serve_url_kegiatan')) {
    function qr_code_serve_url_kegiatan($id) {
        return site_url('File_asset/kegiatan_qr/' . (int) $id);
    }
}

if (!function_exists('qr_code_serve_url_tanda_terima_item')) {
    function qr_code_serve_url_tanda_terima_item($id) {
        return site_url('File_asset/tanda_terima_qr/' . (int) $id);
    }
}

if (!function_exists('qr_code_payload_for_ujian')) {
    /**
     * Isi QR absensi ujian — token mentah agar tetap valid lintas domain.
     */
    function qr_code_payload_for_ujian($token) {
        return trim((string) $token);
    }
}

if (!function_exists('qr_code_data_uri')) {
    function qr_code_data_uri($relative_path) {
        $full = qr_code_resolve_full_path($relative_path);
        if (!$full || !is_readable($full)) {
            return '';
        }
        return 'data:image/png;base64,' . base64_encode(file_get_contents($full));
    }
}

if (!function_exists('qr_code_embed_jadwal_uts')) {
    function qr_code_embed_jadwal_uts($jadwal_or_id) {
        $CI =& get_instance();
        $CI->load->model('Jadwal_uts_model');

        $jadwal = is_object($jadwal_or_id)
            ? $jadwal_or_id
            : $CI->Jadwal_uts_model->get_by_id((int) $jadwal_or_id);

        if (!$jadwal) {
            return '';
        }

        if (empty($jadwal->qr_code) || !qr_code_resolve_full_path($jadwal->qr_code)) {
            if (!empty($jadwal->qr_code_token)) {
                $CI->Jadwal_uts_model->ensure_qr_code_file($jadwal->id);
            } else {
                $CI->Jadwal_uts_model->reset_qr_code($jadwal->id);
            }
            $jadwal = $CI->Jadwal_uts_model->get_by_id($jadwal->id);
        }

        return qr_code_data_uri($jadwal->qr_code ?? '');
    }
}

if (!function_exists('qr_code_embed_jadwal_uas')) {
    function qr_code_embed_jadwal_uas($jadwal_or_id) {
        $CI =& get_instance();
        $CI->load->model('Jadwal_uas_model');

        $jadwal = is_object($jadwal_or_id)
            ? $jadwal_or_id
            : $CI->Jadwal_uas_model->get_by_id((int) $jadwal_or_id);

        if (!$jadwal) {
            return '';
        }

        if (empty($jadwal->qr_code) || !qr_code_resolve_full_path($jadwal->qr_code)) {
            if (!empty($jadwal->qr_code_token)) {
                $CI->Jadwal_uas_model->ensure_qr_code_file($jadwal->id);
            } else {
                $CI->Jadwal_uas_model->reset_qr_code($jadwal->id);
            }
            $jadwal = $CI->Jadwal_uas_model->get_by_id($jadwal->id);
        }

        return qr_code_data_uri($jadwal->qr_code ?? '');
    }
}

if (!function_exists('generate_qr_code')) {
    function generate_qr_code($data, $filename = null, $size = 300) {
        $upload_path = qr_code_storage_dir();

        if (!$filename) {
            $filename = 'qrcode_' . time() . '_' . uniqid();
        }

        $file_path = $upload_path . $filename . '.png';
        $relative_path = 'private/qrcode/' . $filename . '.png';

        if (class_exists('SimpleSoftwareIO\QrCode\Facades\QrCode')) {
            \SimpleSoftwareIO\QrCode\Facades\QrCode::format('png')
                ->size($size)
                ->generate($data, $file_path);
        } elseif (class_exists('Endroid\QrCode\QrCode')) {
            $qrCode = new \Endroid\QrCode\QrCode($data);
            $qrCode->setSize($size);
            $writer = new \Endroid\QrCode\Writer\PngWriter();
            file_put_contents($file_path, $writer->write($qrCode)->getString());
        } elseif (file_exists(APPPATH . 'third_party/phpqrcode/qrlib.php')) {
            require_once APPPATH . 'third_party/phpqrcode/qrlib.php';
            QRcode::png($data, $file_path, QR_ECLEVEL_L, 10, 2);
        } else {
            $qr_url = 'https://api.qrserver.com/v1/create-qr-code/?size=' . $size . 'x' . $size . '&data=' . urlencode($data);
            $qr_image = file_get_contents($qr_url);
            file_put_contents($file_path, $qr_image);
        }

        return $relative_path;
    }
}

if (!function_exists('delete_qr_code')) {
    function delete_qr_code($file_path) {
        $full_path = qr_code_resolve_full_path($file_path);
        if ($full_path && file_exists($full_path)) {
            return @unlink($full_path);
        }
        return false;
    }
}
