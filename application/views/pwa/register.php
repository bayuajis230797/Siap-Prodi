<?php
/**
 * Pendaftaran service worker + penangkap prompt instal PWA (sebelum </body>).
 * Event kustom untuk halaman Aplikasi Android: 'pwa:siap-pasang' dan 'pwa:terpasang'.
 */
$pwa_path = rtrim((string) parse_url(base_url(), PHP_URL_PATH), '/') . '/';
?>
<script>
(function () {
    var basePath = <?php echo json_encode($pwa_path); ?>;

    window.addEventListener('beforeinstallprompt', function (e) {
        e.preventDefault();
        window.__pwaPrompt = e;
        document.dispatchEvent(new CustomEvent('pwa:siap-pasang'));
    });
    window.addEventListener('appinstalled', function () {
        window.__pwaPrompt = null;
        document.dispatchEvent(new CustomEvent('pwa:terpasang'));
    });

    // Service worker hanya berjalan di HTTPS (atau localhost)
    if ('serviceWorker' in navigator && (location.protocol === 'https:' || location.hostname === 'localhost' || location.hostname === '127.0.0.1')) {
        window.addEventListener('load', function () {
            navigator.serviceWorker.register(basePath + 'sw.js', { scope: basePath }).catch(function () { /* diabaikan: aplikasi tetap berjalan tanpa PWA */ });
        });
    }
})();
</script>
