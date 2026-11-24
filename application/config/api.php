<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/*
| JWT untuk aplikasi mobile (mahasiswa). Set environment SIAPPRODI_JWT_SECRET di server produksi.
*/
$config['jwt_secret'] = getenv('SIAPPRODI_JWT_SECRET') ?: 'ganti-secret-ini-di-server-produksi-min-32-karakter!!';
$config['jwt_ttl_seconds'] = 60 * 60 * 24 * 7; // 7 hari

$config['login_max_attempts'] = 5;
$config['login_lock_seconds'] = 600;
