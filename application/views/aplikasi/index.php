<div class="row justify-content-center">
    <div class="col-lg-7 col-md-9">
        <div class="card card-outline card-primary">
            <div class="card-header"><h3 class="card-title"><i class="fab fa-android"></i> Pasang Aplikasi SI-HARKAT (PWA)</h3></div>
            <div class="card-body">
                <p class="text-muted">Pasang SI-HARKAT di layar utama ponsel Android atau komputer Anda. Tampil layar penuh seperti aplikasi, tanpa mengunduh berkas, dan selalu memakai versi terbaru.</p>

                <div id="pwa-status-terpasang" class="alert alert-success" style="display:none;">
                    <i class="fas fa-check-circle"></i> Aplikasi sudah terpasang dan sedang dibuka sebagai aplikasi.
                </div>

                <div id="pwa-blok-pasang" style="display:none;">
                    <button type="button" id="pwa-tombol-pasang" class="btn btn-primary btn-lg btn-block"><i class="fas fa-download"></i> Pasang Aplikasi</button>
                    <small class="text-muted d-block mt-2">Tombol ini aktif bila peramban Anda mendukung pemasangan langsung.</small>
                </div>

                <div id="pwa-petunjuk-android" style="display:none;">
                    <h6>Android (Chrome)</h6>
                    <ol class="pl-3">
                        <li>Buka situs ini di <strong>Chrome</strong>.</li>
                        <li>Ketuk menu <strong>&#8942;</strong> di kanan atas.</li>
                        <li>Pilih <strong>Instal aplikasi</strong> atau <strong>Tambahkan ke layar utama</strong>.</li>
                    </ol>
                </div>

                <div id="pwa-petunjuk-ios" style="display:none;">
                    <h6>iPhone / iPad (Safari)</h6>
                    <ol class="pl-3">
                        <li>Buka situs ini di <strong>Safari</strong>.</li>
                        <li>Ketuk ikon <strong>Bagikan</strong>.</li>
                        <li>Pilih <strong>Tambah ke Layar Utama</strong>.</li>
                    </ol>
                </div>

                <div id="pwa-petunjuk-komputer" style="display:none;">
                    <h6>Komputer (Chrome / Edge)</h6>
                    <p class="mb-0">Klik ikon <strong>Instal</strong> di ujung kanan kolom alamat, atau menu <strong>&#8942; &rarr; Simpan dan bagikan &rarr; Instal halaman sebagai aplikasi</strong>.</p>
                </div>

                <div id="pwa-peringatan-https" class="alert alert-warning mt-3" style="display:none;">
                    <i class="fas fa-lock"></i> Pemasangan aplikasi hanya tersedia bila situs dibuka lewat <strong>HTTPS</strong>.
                </div>
            </div>
        </div>
    </div>
</div>

<script>
(function () {
    function el(id) { return document.getElementById(id); }
    function tampil(id, ya) { var e = el(id); if (e) { e.style.display = ya ? '' : 'none'; } }

    var ua = navigator.userAgent || '';
    var ios = /iPad|iPhone|iPod/.test(ua) || (navigator.platform === 'MacIntel' && navigator.maxTouchPoints > 1);
    var android = /Android/i.test(ua);
    var standalone = (window.matchMedia && window.matchMedia('(display-mode: standalone)').matches) || window.navigator.standalone === true;
    var aman = location.protocol === 'https:' || location.hostname === 'localhost' || location.hostname === '127.0.0.1';

    function perbarui() {
        if (standalone) {
            tampil('pwa-status-terpasang', true); tampil('pwa-blok-pasang', false);
            tampil('pwa-petunjuk-android', false); tampil('pwa-petunjuk-ios', false); tampil('pwa-petunjuk-komputer', false);
            return;
        }
        var bisaLangsung = !!window.__pwaPrompt;
        tampil('pwa-blok-pasang', bisaLangsung);
        tampil('pwa-petunjuk-ios', ios && !bisaLangsung);
        tampil('pwa-petunjuk-android', android && !ios && !bisaLangsung);
        tampil('pwa-petunjuk-komputer', !android && !ios && !bisaLangsung);
        tampil('pwa-peringatan-https', !aman);
    }

    var tombol = el('pwa-tombol-pasang');
    if (tombol) {
        tombol.addEventListener('click', function () {
            var p = window.__pwaPrompt;
            if (!p) { return; }
            p.prompt();
            p.userChoice.then(function () { window.__pwaPrompt = null; perbarui(); });
        });
    }
    document.addEventListener('pwa:siap-pasang', perbarui);
    document.addEventListener('pwa:terpasang', function () { standalone = true; perbarui(); });
    perbarui();
})();
</script>
