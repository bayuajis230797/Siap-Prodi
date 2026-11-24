<div class="row">
    <div class="col-12">
        <div class="card card-primary card-outline">
            <div class="card-header"><h3 class="card-title">Scan QR Tanda Terima</h3></div>
            <div class="card-body">
                <div class="alert alert-info">
                    <i class="fas fa-info-circle"></i> Arahkan kamera ke QR Code pada label barang yang Anda ambil.
                </div>
                <div class="text-center">
                    <video id="video" width="100%" style="max-width:400px" autoplay playsinline></video>
                    <canvas id="canvas" style="display:none"></canvas>
                    <div class="mt-2">
                        <button type="button" class="btn btn-primary" id="btn-start">Aktifkan Kamera</button>
                        <button type="button" class="btn btn-secondary" id="btn-stop" style="display:none">Stop</button>
                    </div>
                    <div id="status-container" class="mt-3 text-left" style="max-width:400px;margin:0 auto"></div>
                </div>
            </div>
        </div>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/jsqr@1.4.0/dist/jsQR.min.js"></script>
<script>
(function() {
    var processUrl = <?php echo json_encode($process_url); ?>;
    var video = document.getElementById('video');
    var canvas = document.getElementById('canvas');
    var ctx = canvas.getContext('2d');
    var stream = null, scanning = false, interval = null, lastToken = '';

    function showStatus(ok, msg) {
        document.getElementById('status-container').innerHTML =
            '<div class="alert alert-' + (ok ? 'success' : 'danger') + '">' + msg + '</div>';
    }

    function extractToken(qrData) {
        qrData = (qrData || '').trim();
        if (qrData.indexOf('Tanda_terima_dosen/scan/') >= 0) {
            return qrData.split('Tanda_terima_dosen/scan/')[1].split(/[?#]/)[0].trim();
        }
        if (qrData.indexOf('Tanda_terima_ambil/scan/') >= 0) {
            return qrData.split('Tanda_terima_ambil/scan/')[1].split(/[?#]/)[0].trim();
        }
        if (qrData.indexOf('TT_') >= 0) {
            var m = qrData.match(/(TT_[A-Za-z0-9_]+)/);
            if (m) return m[1];
        }
        return qrData;
    }

    function syncCsrfFromCookie() {
        var cookieName = 'csrf_siapprodi_cookie';
        var parts = document.cookie ? document.cookie.split(';') : [];
        for (var i = 0; i < parts.length; i++) {
            var part = parts[i].trim();
            if (part.indexOf(cookieName + '=') === 0) {
                var newHash = decodeURIComponent(part.substring(cookieName.length + 1));
                if (newHash) {
                    var meta = document.querySelector('meta[name=csrf-token]');
                    if (meta) meta.setAttribute('content', newHash);
                }
                break;
            }
        }
    }

    function appendCsrf(fd) {
        syncCsrfFromCookie();
        var nameEl = document.querySelector('meta[name=csrf-token-name]');
        var hashEl = document.querySelector('meta[name=csrf-token]');
        if (nameEl && hashEl && nameEl.content && hashEl.content) {
            fd.append(nameEl.content, hashEl.content);
        }
    }

    function submit(token) {
        if (!token || token.length < 8) {
            showStatus(false, 'QR Code tidak valid.');
            return;
        }
        if (lastToken === token) return;
        lastToken = token;
        var fd = new FormData();
        fd.append('token', token);
        appendCsrf(fd);
        fetch(processUrl, { method: 'POST', body: fd, credentials: 'same-origin', headers: { 'X-Requested-With': 'XMLHttpRequest' } })
            .then(function(r) {
                return r.text().then(function(t) {
                    syncCsrfFromCookie();
                    var d;
                    try { d = JSON.parse(t); } catch (e) {
                        throw new Error(r.status === 403 ? 'Sesi keamanan kedaluwarsa. Muat ulang halaman lalu scan lagi.' : 'Respons server tidak valid.');
                    }
                    if (!r.ok && !d.message) throw new Error('HTTP ' + r.status);
                    return d;
                });
            })
            .then(function(d) {
                showStatus(!!d.success, d.message || 'Selesai');
                if (d.success) setTimeout(function() { lastToken = ''; }, 3000);
                else lastToken = '';
            })
            .catch(function(err) {
                showStatus(false, (err && err.message) ? err.message : 'Gagal menghubungi server.');
                lastToken = '';
            });
    }

    document.getElementById('btn-start').onclick = function() {
        navigator.mediaDevices.getUserMedia({ video: { facingMode: 'environment' } })
            .then(function(s) {
                stream = s; video.srcObject = s; video.play();
                document.getElementById('btn-start').style.display = 'none';
                document.getElementById('btn-stop').style.display = 'inline-block';
                scanning = true;
                interval = setInterval(function() {
                    if (!scanning || video.readyState !== video.HAVE_ENOUGH_DATA) return;
                    canvas.width = video.videoWidth; canvas.height = video.videoHeight;
                    ctx.drawImage(video, 0, 0);
                    var code = jsQR(ctx.getImageData(0,0,canvas.width,canvas.height).data, canvas.width, canvas.height);
                    if (code) {
                        scanning = false; clearInterval(interval);
                        if (stream) stream.getTracks().forEach(function(t){t.stop();});
                        document.getElementById('btn-start').style.display = 'inline-block';
                        document.getElementById('btn-stop').style.display = 'none';
                        submit(extractToken(code.data));
                    }
                }, 200);
            })
            .catch(function(e) { alert('Kamera: ' + e.message); });
    };

    document.getElementById('btn-stop').onclick = function() {
        scanning = false;
        if (interval) clearInterval(interval);
        if (stream) stream.getTracks().forEach(function(t){t.stop();});
        document.getElementById('btn-start').style.display = 'inline-block';
        document.getElementById('btn-stop').style.display = 'none';
    };
})();
</script>
