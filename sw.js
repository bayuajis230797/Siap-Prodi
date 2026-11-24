/*
 * Service worker SI-HARKAT (PWA)
 *
 * Prinsip (data akademik bersifat dinamis dan memakai sesi/CSRF, jadi TIDAK di-cache):
 *  - Hanya menangani permintaan GET.
 *  - Navigasi halaman: jaringan dulu; bila offline tampilkan offline.html. HTML tidak pernah disimpan.
 *  - Aset statis satu asal (css, js, gambar, font): cache dulu lalu perbarui di latar (stale-while-revalidate).
 *  - Tidak menyentuh: /api/, unggahan (/uploads/), login/logout, dan permintaan lintas asal.
 * Naikkan VERSI bila aset berubah agar cache lama dibersihkan.
 */
var VERSI = 'siap-prodi-v4';
var CACHE_STATIS = VERSI + '-statis';
var HALAMAN_OFFLINE = 'offline.html';
var PRECACHE = [HALAMAN_OFFLINE, 'pwa/icon-192.png', 'pwa/icon-512.png'];
var EKSTENSI_STATIS = /\.(?:css|js|png|jpg|jpeg|gif|svg|webp|ico|woff2?|ttf)$/i;
var LEWATI = /\/(?:api|uploads|Auth)(?:\/|$)/i;

self.addEventListener('install', function (event) {
    event.waitUntil(
        caches.open(CACHE_STATIS).then(function (cache) { return cache.addAll(PRECACHE); }).then(function () { return self.skipWaiting(); })
    );
});

self.addEventListener('activate', function (event) {
    event.waitUntil(
        caches.keys().then(function (nama) {
            return Promise.all(nama.filter(function (n) { return n.indexOf('siap-prodi-') === 0 && n.indexOf(VERSI) !== 0; })
                .map(function (n) { return caches.delete(n); }));
        }).then(function () { return self.clients.claim(); })
    );
});

self.addEventListener('fetch', function (event) {
    var req = event.request;
    if (req.method !== 'GET') { return; }

    var url = new URL(req.url);
    if (url.origin !== self.location.origin) { return; }
    if (LEWATI.test(url.pathname)) { return; }

    // Navigasi halaman: jaringan dulu, offline => halaman offline
    if (req.mode === 'navigate') {
        event.respondWith(
            fetch(req).catch(function () { return caches.match(HALAMAN_OFFLINE); })
        );
        return;
    }

    // Aset statis: stale-while-revalidate
    if (EKSTENSI_STATIS.test(url.pathname)) {
        event.respondWith(
            caches.open(CACHE_STATIS).then(function (cache) {
                return cache.match(req).then(function (cached) {
                    var jaringan = fetch(req).then(function (res) {
                        if (res && res.status === 200 && res.type === 'basic') { cache.put(req, res.clone()); }
                        return res;
                    }).catch(function () { return cached; });
                    return cached || jaringan;
                });
            })
        );
    }
});
