<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/*
| -------------------------------------------------------------------------
| URI ROUTING
| -------------------------------------------------------------------------
| This file lets you re-map URI requests to specific controller functions.
|
| Typically there is a one-to-one relationship between a URL string
| and its corresponding controller class/method. The segments in a
| URL normally follow this pattern:
|
|	example.com/class/method/id/
|
| In some instances, however, you may want to remap this relationship
| so that a different class/function is called than the one
| corresponding to the URL.
|
| Please see the user guide for complete details:
|
|	https://codeigniter.com/userguide3/general/routing.html
|
| -------------------------------------------------------------------------
| RESERVED ROUTES
| -------------------------------------------------------------------------
|
| There are three reserved routes:
|
|	$route['default_controller'] = 'Auth';
|
| This route indicates which controller class should be loaded if the
| URI contains no data. In the above example, the "welcome" class
| would be loaded.
|
|	$route['404_override'] = 'errors/page_missing';
|
| This route will tell the Router which controller/method to use if those
| provided in the URL cannot be matched to a valid route.
|
|	$route['translate_uri_dashes'] = FALSE;
|
| This is not exactly a route, but allows you to automatically route
| controller and method names that contain dashes. '-' isn't a valid
| class or method name character, so it requires translation.
| When you set this option to TRUE, it will replace ALL dashes in the
| controller and method URI segments.
|
| Examples:	my-controller/index	-> my_controller/index
|		my-controller/my-method	-> my_controller/my_method
*/
$route['default_controller'] = 'Auth';
$route['404_override'] = '';
$route['translate_uri_dashes'] = FALSE;

/*
| API mobile (v1) — URL contoh: index.php/api/v1/auth/login
*/
$route['api/v1/auth/login'] = 'api/auth_api/login';
$route['api/v1/app/branding'] = 'api/app_api/branding';
$route['api/v1/mahasiswa/me'] = 'api/mahasiswa_api/me';
$route['api/v1/mahasiswa/menu'] = 'api/mahasiswa_api/menu';
$route['api/v1/mahasiswa/dashboard'] = 'api/mahasiswa_api/dashboard';
$route['api/v1/mahasiswa/beranda'] = 'api/mahasiswa_api/beranda';
$route['api/v1/mahasiswa/scan_absensi'] = 'api/mahasiswa_api/scan_absensi';
$route['api/v1/mahasiswa/pkl_logbook'] = 'api/mahasiswa_api/pkl_logbook';
$route['api/v1/mahasiswa/pkl_absensi'] = 'api/mahasiswa_api/pkl_absensi';
$route['api/v1/mahasiswa/pkl_bimbingan_nilai'] = 'api/mahasiswa_api/pkl_bimbingan_nilai';
$route['api/v1/mahasiswa/pkl_bimbingan_log/(:num)'] = 'api/mahasiswa_api/pkl_bimbingan_log/$1';
$route['api/v1/mahasiswa/skripsi_bimbingan_log/(:num)'] = 'api/mahasiswa_api/skripsi_bimbingan_log/$1';
$route['api/v1/mahasiswa/skripsi_ringkas'] = 'api/mahasiswa_api/skripsi_ringkas';
$route['api/v1/mahasiswa/jadwal_kuliah'] = 'api/mahasiswa_api/jadwal_kuliah';
$route['api/v1/mahasiswa/jadwal_uts'] = 'api/mahasiswa_api/jadwal_uts';
$route['api/v1/mahasiswa/jadwal_uas'] = 'api/mahasiswa_api/jadwal_uas';
$route['api/v1/mahasiswa/kegiatan'] = 'api/mahasiswa_api/kegiatan';
$route['api/v1/mahasiswa/ujian_jadwal'] = 'api/mahasiswa_api/ujian_jadwal';
$route['api/v1/mahasiswa/berkas'] = 'api/mahasiswa_api/berkas';
$route['api/v1/mahasiswa/permintaan_surat'] = 'api/mahasiswa_api/permintaan_surat';
$route['api/v1/mahasiswa/tagihan'] = 'api/mahasiswa_api/tagihan';
$route['api/v1/mahasiswa/jadwal_sidang_pkl'] = 'api/mahasiswa_api/jadwal_sidang_pkl';
$route['api/v1/mahasiswa/pkl_portal'] = 'api/mahasiswa_api/pkl_portal';
$route['api/v1/mahasiswa/notifikasi'] = 'api/mahasiswa_api/notifikasi';
$route['api/v1/mahasiswa/notifikasi_read'] = 'api/mahasiswa_api/notifikasi_read';
$route['api/v1/mahasiswa/notifikasi_read_all'] = 'api/mahasiswa_api/notifikasi_read_all';
$route['api/v1/mahasiswa/skripsi_portal'] = 'api/mahasiswa_api/skripsi_portal';
$route['api/v1/mahasiswa/change_password'] = 'api/mahasiswa_api/change_password';
$route['api/v1/absensi/scan'] = 'api/absensi_api/scan';
/** Alias (beberapa server / path hanya cocok dengan pola mahasiswa). */
$route['api/v1/mahasiswa/absensi_scan'] = 'api/absensi_api/scan';
$route['api/v1/mahasiswa/tanda_terima_scan'] = 'api/tanda_terima_api/scan';
