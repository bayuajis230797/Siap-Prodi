<div class="row">
    <div class="col-12">
        <div class="card card-primary card-outline">
            <div class="card-header">
                <h3 class="card-title">Scan QR Code Absensi Ujian</h3>
            </div>
            <div class="card-body">
                <div class="alert alert-info">
                    <i class="fas fa-info-circle"></i> 
                    <strong>Petunjuk:</strong> Scan QR Code dari daftar hadir UTS/UAS yang telah dicetak untuk melakukan absensi.
                </div>

                <!-- Camera untuk scan QR Code -->
                <div class="row">
                    <div class="col-md-8 offset-md-2">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="card-title">Scan QR Code dari Daftar Hadir</h5>
                            </div>
                            <div class="card-body text-center">
                                <div id="camera-container" style="position: relative; width: 100%; max-width: 500px; margin: 0 auto;">
                                    <video id="video" width="100%" autoplay playsinline></video>
                                    <canvas id="canvas" style="display: none;"></canvas>
                                </div>
                                <div class="mt-3">
                                    <button type="button" class="btn btn-primary" id="btn-start-camera" onclick="startCamera()">
                                        <i class="fas fa-camera"></i> Aktifkan Kamera
                                    </button>
                                    <button type="button" class="btn btn-secondary" id="btn-stop-camera" onclick="stopCamera()" style="display: none;">
                                        <i class="fas fa-stop"></i> Matikan Kamera
                                    </button>
                                </div>
                                
                                <!-- Status -->
                                <div id="status-container" class="mt-3"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/jsqr@1.4.0/dist/jsQR.min.js"></script>
<script>
let video = null;
let canvas = null;
let context = null;
let scanning = false;
let scanInterval = null;

function startCamera() {
    navigator.mediaDevices.getUserMedia({ 
        video: { 
            facingMode: 'environment' // Gunakan kamera belakang
        } 
    })
    .then(function(stream) {
        video = document.getElementById('video');
        canvas = document.getElementById('canvas');
        context = canvas.getContext('2d');
        
        video.srcObject = stream;
        video.setAttribute('playsinline', true);
        video.play();
        
        document.getElementById('btn-start-camera').style.display = 'none';
        document.getElementById('btn-stop-camera').style.display = 'inline-block';
        
        scanning = true;
        scanQRCode();
    })
    .catch(function(err) {
        alert('Gagal mengakses kamera: ' + err.message);
        console.error('Camera error:', err);
    });
}

function stopCamera() {
    if (video && video.srcObject) {
        video.srcObject.getTracks().forEach(track => track.stop());
        video.srcObject = null;
    }
    
    scanning = false;
    if (scanInterval) {
        clearInterval(scanInterval);
    }
    
    document.getElementById('btn-start-camera').style.display = 'inline-block';
    document.getElementById('btn-stop-camera').style.display = 'none';
}

function scanQRCode() {
    if (!scanning) return;
    
    scanInterval = setInterval(function() {
        if (video.readyState === video.HAVE_ENOUGH_DATA) {
            canvas.height = video.videoHeight;
            canvas.width = video.videoWidth;
            context.drawImage(video, 0, 0, canvas.width, canvas.height);
            
            const imageData = context.getImageData(0, 0, canvas.width, canvas.height);
            const code = jsQR(imageData.data, imageData.width, imageData.height);
            
            if (code) {
                // QR Code terdeteksi
                stopCamera();
                processAbsensi(code.data);
            }
        }
    }, 100);
}


function processAbsensi(qrData) {
    let token = null;
    const raw = (qrData || '').trim();

    if (/^(UTS|UAS)_[A-Fa-f0-9]+$/i.test(raw)) {
        token = raw;
    } else if (raw.includes('/Absensi_ujian/scan/')) {
        let parts = raw.split('/Absensi_ujian/scan/');
        if (parts.length > 1) {
            token = parts[1].split('?')[0].split('#')[0].trim();
        }
    } else if (raw.includes('Absensi_ujian/scan/')) {
        let parts = raw.split('Absensi_ujian/scan/');
        if (parts.length > 1) {
            token = parts[1].split('?')[0].split('#')[0].trim();
        }
    } else {
        const match = raw.match(/(UTS|UAS)_[A-Fa-f0-9]+/i);
        token = match ? match[0] : raw;
    }

    token = decodeURIComponent(token || '').trim();

    if (!token || token.length < 10) {
        showStatus('error', 'Token QR Code tidak valid.');
        return;
    }

    submitAbsensi(token);
}

function submitAbsensi(token) {
    showStatus('loading', 'Memproses absensi...');

    $.ajax({
        url: '<?php echo site_url("Absensi_ujian/process_scan"); ?>',
        method: 'POST',
        data: { token: token },
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                showStatus('success', response.message);
                setTimeout(function() {
                    if (response.redirect_url) {
                        window.location.href = response.redirect_url;
                    } else {
                        var jenisUjian = response.jenis_ujian ? response.jenis_ujian.toLowerCase() : 'uts';
                        window.location.href = '<?php echo site_url("Jadwal/jadwal_"); ?>' + jenisUjian;
                    }
                }, 2000);
            } else {
                showStatus('error', response.message || 'Gagal memproses absensi.');
            }
        },
        error: function(xhr) {
            var msg = 'Terjadi kesalahan saat memproses absensi.';
            if (xhr.responseJSON && xhr.responseJSON.message) {
                msg = xhr.responseJSON.message;
            } else if (xhr.responseText) {
                try {
                    var parsed = JSON.parse(xhr.responseText);
                    if (parsed.message) {
                        msg = parsed.message;
                    }
                } catch (e) {}
            }
            showStatus('error', msg);
        }
    });
}

function showStatus(type, message) {
    const container = document.getElementById('status-container');
    let alertClass = 'alert-info';
    let icon = 'fa-info-circle';
    
    if (type === 'success') {
        alertClass = 'alert-success';
        icon = 'fa-check-circle';
    } else if (type === 'error') {
        alertClass = 'alert-danger';
        icon = 'fa-exclamation-circle';
    } else if (type === 'loading') {
        alertClass = 'alert-warning';
        icon = 'fa-spinner fa-spin';
    }
    
    container.innerHTML = '<div class="alert ' + alertClass + ' alert-dismissible">' +
        '<i class="fas ' + icon + '"></i> ' + message +
        '<button type="button" class="close" data-dismiss="alert">&times;</button>' +
        '</div>';
}
</script>

