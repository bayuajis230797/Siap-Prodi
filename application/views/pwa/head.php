<?php
/**
 * Tag PWA untuk <head> (dipakai layout utama dan halaman login).
 * Memakai path relatif-situs agar tetap benar di localhost/sub-folder maupun di domain hosting.
 */
$pwa_path = rtrim((string) parse_url(base_url(), PHP_URL_PATH), '/') . '/';
?>
<link rel="manifest" href="<?php echo $pwa_path; ?>manifest.webmanifest">
<meta name="theme-color" content="#5c1228">
<meta name="mobile-web-app-capable" content="yes">
<meta name="apple-mobile-web-app-capable" content="yes">
<meta name="apple-mobile-web-app-title" content="SI-HARKAT">
<meta name="apple-mobile-web-app-status-bar-style" content="default">
<link rel="apple-touch-icon" href="<?php echo $pwa_path; ?>pwa/icon-180.png">
<link rel="icon" type="image/png" sizes="48x48" href="<?php echo $pwa_path; ?>pwa/icon-48.png">
