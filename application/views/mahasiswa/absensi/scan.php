<div class="row">
    <div class="col-12">
        <div class="card card-primary card-outline">
            <div class="card-header">
                <h3 class="card-title">Scan QR Code Absensi</h3>
            </div>
            <div class="card-body">
                <div class="alert alert-info">
                    <i class="fas fa-info-circle"></i> 
                    <strong>Petunjuk:</strong> Izinkan akses lokasi GPS pada browser Anda, lalu scan QR Code yang ditampilkan di kegiatan.
                </div>

                <!-- Camera untuk scan QR Code -->
                <div class="row">
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="card-title">Scan QR Code</h5>
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
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-title">Atau Input Token Manual</h5>
                        </div>
                        <div class="card-body">
                            <div class="form-group">
                                <label>Token QR Code</label>
                                <input type="text" class="form-control" id="token-input" placeholder="Masukkan token QR Code">
                            </div>
                            <button type="button" class="btn btn-success btn-block" onclick="processToken()">
                                <i class="fas fa-check"></i> Proses Absensi
                            </button>
                        </div>
                    </div>

                        <!-- Status -->
                        <div id="status-container" class="mt-3"></div>
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

function processToken() {
    const token = document.getElementById('token-input').value.trim();
    if (!token) {
        alert('Masukkan token QR Code terlebih dahulu.');
        return;
    }
    // Langsung proses dengan token saja
    processAbsensi(token);
}

function processAbsensi(qrData) {
    // Extract token dari URL
    let token = null;
    
    // Cek jika QR Code berisi URL lengkap
    if (qrData.includes('/Absensi/scan/')) {
        // Extract token dari URL: http://domain.com/Absensi/scan/TOKEN
        let parts = qrData.split('/Absensi/scan/');
        if (parts.length > 1) {
            token = parts[1].split('?')[0].split('#')[0].trim(); // Ambil token, hilangkan query string dan hash
        }
    } else if (qrData.includes('Absensi/scan/')) {
        // URL tanpa protocol: domain.com/Absensi/scan/TOKEN
        let parts = qrData.split('Absensi/scan/');
        if (parts.length > 1) {
            token = parts[1].split('?')[0].split('#')[0].trim();
        }
    } else {
        // Jika langsung token
        token = qrData.trim();
    }
    
    if (!token || token.length < 10) {
        showStatus('error', 'Token QR Code tidak valid. Data: ' + qrData.substring(0, 50));
        return;
    }
    
    // Ambil lokasi GPS
    if (navigator.geolocation) {
        navigator.geolocation.getCurrentPosition(
            function(position) {
                submitAbsensi(token, position.coords.latitude, position.coords.longitude);
            },
            function(error) {
                // Jika GPS gagal, coba absensi tanpa GPS (opsional)
                if (confirm('Gagal mengambil lokasi GPS. Lanjutkan absensi tanpa validasi GPS?')) {
                    submitAbsensi(token, null, null);
                }
            },
            { timeout: 10000, enableHighAccuracy: true }
        );
    } else {
        // Browser tidak support geolocation
        if (confirm('Browser Anda tidak mendukung GPS. Lanjutkan absensi tanpa validasi GPS?')) {
            submitAbsensi(token, null, null);
        }
    }
}

function submitAbsensi(token, latitude, longitude) {
    showStatus('loading', 'Memproses absensi...');
    
    $.ajax({
        url: '<?php echo site_url("Absensi/process_scan/"); ?>' + token,
        method: 'POST',
        data: {
            latitude: latitude,
            longitude: longitude
        },
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                showStatus('success', response.message);
                setTimeout(function() {
                    window.location.href = '<?php echo site_url("Absensi/kegiatan_saya"); ?>';
                }, 2000);
            } else {
                showStatus('error', response.message);
            }
        },
        error: function() {
            showStatus('error', 'Terjadi kesalahan saat memproses absensi.');
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

