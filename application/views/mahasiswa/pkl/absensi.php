<div class="row">
    <div class="col-md-12">
        <?php if ($this->session->flashdata('success')): ?>
            <div class="alert alert-success alert-dismissible fade show">
                <i class="fas fa-check-circle"></i> <?php echo $this->session->flashdata('success'); ?>
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
        <?php endif; ?>

        <?php if ($this->session->flashdata('error')): ?>
            <div class="alert alert-danger alert-dismissible fade show">
                <i class="fas fa-exclamation-circle"></i> <?php echo $this->session->flashdata('error'); ?>
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
        <?php endif; ?>
    </div>
</div>

<div class="row">
    <!-- Card Absensi Hari Ini -->
    <div class="col-md-8">
        <div class="card card-primary card-outline">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-map-marker-alt"></i> Absensi Harian PKL
                </h3>
            </div>
            <div class="card-body">
                <div class="alert alert-info">
                    <i class="fas fa-info-circle"></i> 
                    <strong>Informasi:</strong> Sistem absensi menggunakan GPS untuk memvalidasi lokasi Anda. 
                    Pastikan Anda mengizinkan akses lokasi di browser.
                </div>

                <!-- Info Tempat PKL -->
                <div class="card bg-light mb-3">
                    <div class="card-body">
                        <h5 class="card-title"><i class="fas fa-building"></i> Tempat PKL</h5>
                        <p class="mb-1"><strong><?php echo htmlspecialchars($tempat_pkl->nama_instansi); ?></strong></p>
                        <p class="mb-0 text-muted"><?php echo htmlspecialchars($tempat_pkl->alamat_instansi); ?></p>
                        <p class="mb-0"><small>Radius absensi: <strong><?php echo $radius_setting; ?> meter</strong></small></p>
                    </div>
                </div>

                <!-- Status Absensi Hari Ini -->
                <div class="row mb-3">
                    <div class="col-md-6">
                        <div class="card <?php echo $sudah_berangkat ? 'bg-success' : 'bg-secondary'; ?> text-white">
                            <div class="card-body text-center">
                                <h5><i class="fas fa-sign-in-alt"></i> Berangkat</h5>
                                <?php if ($sudah_berangkat): ?>
                                    <?php 
                                    $absen_berangkat = null;
                                    foreach ($absensi_hari_ini as $absen) {
                                        if ($absen->tipe_absensi === 'berangkat') {
                                            $absen_berangkat = $absen;
                                            break;
                                        }
                                    }
                                    ?>
                                    <p class="mb-0">Sudah Absen</p>
                                    <small><?php echo date('H:i', strtotime($absen_berangkat->waktu_absen)); ?></small>
                                    <?php if ($absen_berangkat->status_radius === 'di_luar_radius'): ?>
                                        <br><span class="badge badge-warning">Di Luar Radius</span>
                                    <?php else: ?>
                                        <br><span class="badge badge-light">Dalam Radius</span>
                                    <?php endif; ?>
                                <?php else: ?>
                                    <p class="mb-0">Belum Absen</p>
                                    <button type="button" class="btn btn-light btn-sm mt-2" onclick="absen('berangkat')">
                                        <i class="fas fa-check"></i> Absen Berangkat
                                    </button>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="card <?php echo $sudah_pulang ? 'bg-success' : ($sudah_berangkat ? 'bg-warning' : 'bg-secondary'); ?> text-white">
                            <div class="card-body text-center">
                                <h5><i class="fas fa-sign-out-alt"></i> Pulang</h5>
                                <?php if ($sudah_pulang): ?>
                                    <?php 
                                    $absen_pulang = null;
                                    foreach ($absensi_hari_ini as $absen) {
                                        if ($absen->tipe_absensi === 'pulang') {
                                            $absen_pulang = $absen;
                                            break;
                                        }
                                    }
                                    ?>
                                    <p class="mb-0">Sudah Absen</p>
                                    <small><?php echo date('H:i', strtotime($absen_pulang->waktu_absen)); ?></small>
                                    <?php if ($absen_pulang->status_radius === 'di_luar_radius'): ?>
                                        <br><span class="badge badge-warning">Di Luar Radius</span>
                                    <?php else: ?>
                                        <br><span class="badge badge-light">Dalam Radius</span>
                                    <?php endif; ?>
                                <?php elseif ($sudah_berangkat): ?>
                                    <p class="mb-0">Belum Absen</p>
                                    <button type="button" class="btn btn-light btn-sm mt-2" onclick="absen('pulang')">
                                        <i class="fas fa-check"></i> Absen Pulang
                                    </button>
                                <?php else: ?>
                                    <p class="mb-0">Belum Bisa Absen</p>
                                    <small>Absen berangkat terlebih dahulu</small>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Peta Lokasi Saat Ini -->
                <div class="form-group">
                    <label>Lokasi GPS Saat Ini</label>
                    <div id="map-absensi" style="width: 100%; height: 300px; border: 1px solid #ddd; border-radius: 4px;"></div>
                    <small class="text-muted">Peta menampilkan lokasi GPS Anda saat ini</small>
                </div>

                <!-- Loading Indicator -->
                <div id="loading-indicator" class="text-center" style="display: none;">
                    <i class="fas fa-spinner fa-spin fa-2x"></i>
                    <p>Mengambil lokasi GPS...</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Card Statistik -->
    <div class="col-md-4">
        <div class="card card-info card-outline">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-chart-bar"></i> Statistik Kehadiran</h3>
            </div>
            <div class="card-body">
                <?php if ($statistik): ?>
                    <div class="row">
                        <div class="col-12 mb-3">
                            <div class="info-box">
                                <span class="info-box-icon bg-info"><i class="fas fa-calendar-day"></i></span>
                                <div class="info-box-content">
                                    <span class="info-box-text">Total Hari</span>
                                    <span class="info-box-number"><?php echo (int) ($statistik->total_hari ?? 0); ?></span>
                                </div>
                            </div>
                        </div>
                        <div class="col-12 mb-3">
                            <div class="info-box">
                                <span class="info-box-icon bg-success"><i class="fas fa-check"></i></span>
                                <div class="info-box-content">
                                    <span class="info-box-text">Berangkat</span>
                                    <span class="info-box-number"><?php echo (int) ($statistik->total_berangkat ?? 0); ?></span>
                                </div>
                            </div>
                        </div>
                        <div class="col-12 mb-3">
                            <div class="info-box">
                                <span class="info-box-icon bg-success"><i class="fas fa-check-circle"></i></span>
                                <div class="info-box-content">
                                    <span class="info-box-text">Pulang</span>
                                    <span class="info-box-number"><?php echo (int) ($statistik->total_pulang ?? 0); ?></span>
                                </div>
                            </div>
                        </div>
                        <div class="col-12 mb-3">
                            <div class="info-box">
                                <span class="info-box-icon bg-primary"><i class="fas fa-map-marker-alt"></i></span>
                                <div class="info-box-content">
                                    <span class="info-box-text">Dalam Radius</span>
                                    <span class="info-box-number"><?php echo (int) ($statistik->dalam_radius ?? 0); ?></span>
                                </div>
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="info-box">
                                <span class="info-box-icon bg-danger"><i class="fas fa-exclamation-triangle"></i></span>
                                <div class="info-box-content">
                                    <span class="info-box-text">Di Luar Radius</span>
                                    <span class="info-box-number"><?php echo (int) ($statistik->di_luar_radius ?? 0); ?></span>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php else: ?>
                    <p class="text-muted">Belum ada data statistik</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- History Absensi -->
<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-history"></i> Riwayat Absensi</h3>
                <div class="card-tools">
                    <?php if (!empty($history_absensi)): ?>
                        <a href="<?php echo site_url('Pkl_absensi/cetak'); ?>" class="btn btn-sm btn-primary" target="_blank">
                            <i class="fas fa-print"></i> Cetak PDF
                        </a>
                    <?php endif; ?>
                </div>
            </div>
            <div class="card-body table-responsive p-0">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Tanggal</th>
                            <th>Berangkat</th>
                            <th>Pulang</th>
                            <th>Status</th>
                            <th>Jarak</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        $tanggal_list = array();
                        foreach ($history_absensi as $absen) {
                            if (!isset($tanggal_list[$absen->tanggal])) {
                                $tanggal_list[$absen->tanggal] = array('berangkat' => null, 'pulang' => null);
                            }
                            $tanggal_list[$absen->tanggal][$absen->tipe_absensi] = $absen;
                        }
                        krsort($tanggal_list);
                        
                        if (empty($tanggal_list)):
                        ?>
                            <tr>
                                <td colspan="5" class="text-center text-muted">Belum ada data absensi</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($tanggal_list as $tanggal => $absensi): ?>
                                <tr>
                                    <td><?php echo date('d/m/Y', strtotime($tanggal)); ?></td>
                                    <td>
                                        <?php if ($absensi['berangkat']): ?>
                                            <span class="badge badge-success"><?php echo date('H:i', strtotime($absensi['berangkat']->waktu_absen)); ?></span>
                                            <?php if ($absensi['berangkat']->status_radius === 'di_luar_radius'): ?>
                                                <span class="badge badge-danger">Luar</span>
                                            <?php endif; ?>
                                        <?php else: ?>
                                            <span class="badge badge-secondary">-</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if ($absensi['pulang']): ?>
                                            <span class="badge badge-success"><?php echo date('H:i', strtotime($absensi['pulang']->waktu_absen)); ?></span>
                                            <?php if ($absensi['pulang']->status_radius === 'di_luar_radius'): ?>
                                                <span class="badge badge-danger">Luar</span>
                                            <?php endif; ?>
                                        <?php else: ?>
                                            <span class="badge badge-secondary">-</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php 
                                        $berangkat_ok = $absensi['berangkat'] && $absensi['berangkat']->status_radius === 'dalam_radius';
                                        $pulang_ok = $absensi['pulang'] && $absensi['pulang']->status_radius === 'dalam_radius';
                                        if ($berangkat_ok && $pulang_ok):
                                        ?>
                                            <span class="badge badge-success">Lengkap</span>
                                        <?php elseif ($absensi['berangkat'] || $absensi['pulang']): ?>
                                            <span class="badge badge-warning">Tidak Lengkap</span>
                                        <?php else: ?>
                                            <span class="badge badge-danger">Tidak Hadir</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php 
                                        if ($absensi['berangkat']):
                                            echo number_format($absensi['berangkat']->jarak_meter, 1) . ' m';
                                        elseif ($absensi['pulang']):
                                            echo number_format($absensi['pulang']->jarak_meter, 1) . ' m';
                                        else:
                                            echo '-';
                                        endif;
                                        ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Leaflet CSS -->
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin=""/>
<!-- Leaflet JS -->
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>

<script>
let map;
let currentMarker;
let tempatMarker;
let currentLat = null;
let currentLng = null;

// Initialize map
function initMap() {
    // Default location (Tegal)
    const defaultLat = -6.8667;
    const defaultLng = 109.1333;
    
    // Initialize Leaflet map
    map = L.map('map-absensi').setView([defaultLat, defaultLng], 15);

    // Add OpenStreetMap tile layer
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '© OpenStreetMap contributors',
        maxZoom: 19
    }).addTo(map);

    // Add marker untuk tempat PKL
    const tempatLat = <?php echo $tempat_pkl->latitude; ?>;
    const tempatLng = <?php echo $tempat_pkl->longitude; ?>;
    const radius = <?php echo $radius_setting; ?>;
    
    tempatMarker = L.marker([tempatLat, tempatLng], {
        title: 'Tempat PKL'
    }).addTo(map);
    
    tempatMarker.bindPopup('<strong>Tempat PKL</strong><br><?php echo htmlspecialchars($tempat_pkl->nama_instansi); ?>').openPopup();
    
    // Add circle untuk radius
    L.circle([tempatLat, tempatLng], {
        color: '#FF0000',
        fillColor: '#FF0000',
        fillOpacity: 0.2,
        radius: radius
    }).addTo(map);

    // Get current location
    getCurrentLocation();
}

// Get current GPS location
function getCurrentLocation() {
    if (!navigator.geolocation) {
        alert('Browser Anda tidak mendukung Geolocation API.');
        return;
    }

    document.getElementById('loading-indicator').style.display = 'block';

    navigator.geolocation.getCurrentPosition(
        function(position) {
            currentLat = position.coords.latitude;
            currentLng = position.coords.longitude;
            
            // Update map
            map.setView([currentLat, currentLng], 17);
            
            // Remove old marker
            if (currentMarker) {
                map.removeLayer(currentMarker);
            }
            
            // Add current location marker
            currentMarker = L.marker([currentLat, currentLng], {
                title: 'Lokasi Saat Ini'
            }).addTo(map);
            
            currentMarker.bindPopup('<strong>Lokasi Saat Ini</strong><br>Lat: ' + currentLat.toFixed(8) + '<br>Lng: ' + currentLng.toFixed(8)).openPopup();
            
            document.getElementById('loading-indicator').style.display = 'none';
        },
        function(error) {
            let errorMsg = 'Gagal mengambil lokasi GPS. ';
            switch(error.code) {
                case error.PERMISSION_DENIED:
                    errorMsg += 'Akses lokasi ditolak. Izinkan akses lokasi di pengaturan browser.';
                    break;
                case error.POSITION_UNAVAILABLE:
                    errorMsg += 'Informasi lokasi tidak tersedia.';
                    break;
                case error.TIMEOUT:
                    errorMsg += 'Waktu permintaan lokasi habis.';
                    break;
                default:
                    errorMsg += 'Terjadi kesalahan yang tidak diketahui.';
                    break;
            }
            alert(errorMsg);
            document.getElementById('loading-indicator').style.display = 'none';
        },
        {
            enableHighAccuracy: true,
            timeout: 10000,
            maximumAge: 0
        }
    );
}

// Device Fingerprinting Functions
function collectDeviceFingerprint() {
    const fingerprint = {
        userAgent: navigator.userAgent,
        screenWidth: screen.width,
        screenHeight: screen.height,
        screenColorDepth: screen.colorDepth,
        timezone: Intl.DateTimeFormat().resolvedOptions().timeZone,
        language: navigator.language,
        languages: navigator.languages ? navigator.languages.join(',') : navigator.language,
        platform: navigator.platform,
        hardwareConcurrency: navigator.hardwareConcurrency || 'unknown',
        deviceMemory: navigator.deviceMemory || 'unknown',
        cookieEnabled: navigator.cookieEnabled,
        doNotTrack: navigator.doNotTrack || 'unknown',
        maxTouchPoints: navigator.maxTouchPoints || 0
    };

    // Canvas fingerprint (simple version)
    try {
        const canvas = document.createElement('canvas');
        const ctx = canvas.getContext('2d');
        canvas.width = 200;
        canvas.height = 50;
        ctx.textBaseline = 'top';
        ctx.font = '14px Arial';
        ctx.fillStyle = '#f60';
        ctx.fillRect(125, 1, 62, 20);
        ctx.fillStyle = '#069';
        ctx.fillText('Device fingerprint', 2, 15);
        ctx.fillStyle = 'rgba(102, 204, 0, 0.7)';
        ctx.fillText('Device fingerprint', 4, 17);
        fingerprint.canvasHash = canvas.toDataURL().substring(0, 100);
    } catch (e) {
        fingerprint.canvasHash = 'not_supported';
    }

    // WebGL fingerprint
    try {
        const gl = document.createElement('canvas').getContext('webgl') || 
                   document.createElement('canvas').getContext('experimental-webgl');
        if (gl) {
            const debugInfo = gl.getExtension('WEBGL_debug_renderer_info');
            if (debugInfo) {
                fingerprint.webglVendor = gl.getParameter(debugInfo.UNMASKED_VENDOR_WEBGL);
                fingerprint.webglRenderer = gl.getParameter(debugInfo.UNMASKED_RENDERER_WEBGL);
            }
        }
    } catch (e) {
        fingerprint.webglVendor = 'not_supported';
        fingerprint.webglRenderer = 'not_supported';
    }

    return fingerprint;
}

function generateFingerprintHash(fingerprint) {
    // Create a string from all fingerprint data
    const fingerprintString = JSON.stringify(fingerprint);
    
    // Simple hash function (SHA-256 would be better but requires crypto API)
    // For now, we'll use a combination that creates a unique hash
    let hash = 0;
    for (let i = 0; i < fingerprintString.length; i++) {
        const char = fingerprintString.charCodeAt(i);
        hash = ((hash << 5) - hash) + char;
        hash = hash & hash; // Convert to 32bit integer
    }
    
    // Convert to positive hex string
    return Math.abs(hash).toString(16) + fingerprintString.length.toString(16);
}

// Absen function
function absen(tipe) {
    if (!currentLat || !currentLng) {
        Swal.fire({
            icon: 'warning',
            title: 'Peringatan!',
            html: 'Lokasi GPS belum didapatkan. Silakan tunggu sebentar atau refresh halaman.',
            confirmButtonText: 'OK',
            confirmButtonColor: '#ffc107'
        });
        return;
    }

    // Get button reference before SweetAlert
    const btn = event.target.closest('button');
    const originalHtml = btn.innerHTML;

    Swal.fire({
        icon: 'question',
        title: 'Konfirmasi Absensi',
        html: 'Apakah Anda yakin ingin melakukan absensi ' + tipe + '?',
        showCancelButton: true,
        confirmButtonText: 'Ya, Absen',
        cancelButtonText: 'Batal',
        confirmButtonColor: '#28a745',
        cancelButtonColor: '#6c757d'
    }).then((result) => {
        if (!result.isConfirmed) {
            return;
        }
        
        // Continue with absensi process
        doAbsensi(tipe, btn, originalHtml);
    });
}

function doAbsensi(tipe, btn, originalHtml) {
    // Show loading
    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Memproses...';
    btn.disabled = true;

    // Collect device fingerprint
    const deviceFingerprint = collectDeviceFingerprint();
    const fingerprintHash = generateFingerprintHash(deviceFingerprint);

    // Send AJAX request
    $.ajax({
        url: '<?php echo site_url("Pkl_absensi/store"); ?>',
        type: 'POST',
        data: {
            tipe_absensi: tipe,
            latitude: currentLat,
            longitude: currentLng,
            tanggal: '<?php echo date("Y-m-d"); ?>',
            keterangan: '',
            device_fingerprint: fingerprintHash,
            device_info: JSON.stringify(deviceFingerprint)
        },
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                Swal.fire({
                    icon: 'success',
                    title: 'Berhasil!',
                    html: response.message,
                    confirmButtonText: 'OK',
                    confirmButtonColor: '#28a745',
                    timer: 3000,
                    timerProgressBar: true,
                    allowOutsideClick: false
                }).then(function() {
                    location.reload();
                });
            } else {
                Swal.fire({
                    icon: 'error',
                    title: 'Error!',
                    html: response.message,
                    confirmButtonText: 'OK',
                    confirmButtonColor: '#dc3545'
                });
                if (btn && originalHtml) {
                    btn.innerHTML = originalHtml;
                    btn.disabled = false;
                }
            }
        },
        error: function() {
            Swal.fire({
                icon: 'error',
                title: 'Error!',
                html: 'Terjadi kesalahan saat menyimpan absensi.',
                confirmButtonText: 'OK',
                confirmButtonColor: '#dc3545'
            });
            if (btn && originalHtml) {
                btn.innerHTML = originalHtml;
                btn.disabled = false;
            }
        }
    });
}

// Initialize map when page loads
document.addEventListener('DOMContentLoaded', function() {
    initMap();
    
    // Refresh location every 30 seconds
    setInterval(getCurrentLocation, 30000);
});
</script>

