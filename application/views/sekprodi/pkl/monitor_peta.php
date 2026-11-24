<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-map-marked-alt"></i> Monitor PKL - Peta Lokasi Tempat PKL
                </h3>
                <div class="card-tools">
                    <span class="badge badge-info"><?php echo count($locations); ?> Lokasi</span>
                    <span class="badge badge-danger ml-1"><?php echo (int) ($total_anomali_luar_radius ?? 0); ?> Luar Radius</span>
                    <span class="badge badge-warning ml-1"><?php echo (int) ($total_anomali_device ?? 0); ?> Device Berbeda</span>
                </div>
            </div>
            <div class="card-body">
                <?php if (empty($locations)): ?>
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle"></i> Belum ada tempat PKL yang memiliki koordinat GPS.
                    </div>
                <?php else: ?>
                    <div class="alert alert-light border mb-3">
                        <strong><i class="fas fa-map-pin"></i> Legenda Marker:</strong>
                        <span class="badge badge-success ml-2">Normal</span>
                        <span class="badge badge-danger ml-2">Luar Radius</span>
                        <span class="badge badge-warning ml-2">Device Berbeda</span>
                        <span class="badge badge-purple ml-2" style="background:#6f42c1;color:#fff;">Keduanya</span>
                        <small class="text-muted d-block mt-1">Lingkaran merah/oranye/ungu menunjukkan titik absensi anomali terbaru mahasiswa.</small>
                    </div>
                    <div class="mb-3">
                        <div class="btn-group btn-group-sm" role="group" aria-label="Filter Marker Peta">
                            <button type="button" class="btn btn-primary js-map-filter active" data-filter="all">
                                Semua
                            </button>
                            <button type="button" class="btn btn-outline-danger js-map-filter" data-filter="anomali">
                                Hanya Anomali
                            </button>
                            <button type="button" class="btn btn-outline-success js-map-filter" data-filter="normal">
                                Hanya Normal
                            </button>
                        </div>
                    </div>
                    <div id="map" style="width: 100%; height: 600px; border-radius: 5px;"></div>
                    
                    <div class="mt-3">
                        <h5><i class="fas fa-list"></i> Daftar Lokasi PKL</h5>
                        <div class="table-responsive">
                            <table class="table table-sm table-bordered">
                                <thead>
                                    <tr>
                                        <th>No</th>
                                        <th>NIM</th>
                                        <th>Nama Mahasiswa</th>
                                        <th>Kelas</th>
                                        <th>Nama Instansi</th>
                                        <th>Alamat</th>
                                        <th>Koordinat</th>
                                        <th>Anomali</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php $no = 1; foreach ($locations as $idx => $loc): ?>
                                        <tr id="lokasi-row-<?php echo (int) $idx; ?>" data-has-anomali="<?php echo !empty($loc['has_anomali']) ? '1' : '0'; ?>">
                                            <td><?php echo $no++; ?></td>
                                            <td><?php echo htmlspecialchars($loc['nim']); ?></td>
                                            <td><?php echo htmlspecialchars($loc['nama_mahasiswa']); ?></td>
                                            <td><?php echo htmlspecialchars($loc['kelas']); ?></td>
                                            <td><?php echo htmlspecialchars($loc['nama_instansi']); ?></td>
                                            <td><?php echo htmlspecialchars($loc['alamat_instansi'] ?: '-'); ?></td>
                                            <td>
                                                <small>
                                                    <?php echo number_format($loc['latitude'], 6); ?>, 
                                                    <?php echo number_format($loc['longitude'], 6); ?>
                                                </small>
                                            </td>
                                            <td>
                                                <?php if (!empty($loc['has_anomali'])): ?>
                                                    <?php if (!empty($loc['total_luar_radius'])): ?>
                                                        <span class="badge badge-danger"><?php echo (int) $loc['total_luar_radius']; ?> luar radius</span>
                                                    <?php endif; ?>
                                                    <?php if (!empty($loc['total_device_berbeda'])): ?>
                                                        <span class="badge badge-warning"><?php echo (int) $loc['total_device_berbeda']; ?> device berbeda</span>
                                                    <?php endif; ?>
                                                <?php else: ?>
                                                    <span class="badge badge-success">Normal</span>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php if (!empty($locations)): ?>
<!-- Leaflet CSS -->
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin=""/>
<!-- Leaflet JS -->
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>

<style>
/* Custom styling untuk tooltip */
.custom-tooltip {
    background-color: rgba(255, 255, 255, 0.95) !important;
    border: 2px solid #007bff !important;
    border-radius: 5px !important;
    box-shadow: 0 2px 8px rgba(0,0,0,0.3) !important;
    font-size: 12px !important;
}
.custom-tooltip:before {
    border-top-color: #007bff !important;
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Data lokasi dari PHP
    var locations = <?php echo json_encode($locations); ?>;
    
    if (locations.length === 0) {
        return;
    }
    
    // Tentukan center peta (gunakan lokasi pertama atau rata-rata)
    var centerLat = locations[0].latitude;
    var centerLng = locations[0].longitude;
    
    // Initialize Leaflet map
    var map = L.map('map').setView([centerLat, centerLng], 10);
    
    // Add OpenStreetMap tile layer
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '© OpenStreetMap contributors',
        maxZoom: 19
    }).addTo(map);
    
    // Array untuk menyimpan semua marker
    var markerRecords = [];
    var bounds = L.latLngBounds();
    
    // Buat marker untuk setiap lokasi
    locations.forEach(function(location, index) {
        var lat = location.latitude;
        var lng = location.longitude;
        
        var hasLuarRadius = Number(location.total_luar_radius || 0) > 0;
        var hasDeviceIssue = Number(location.total_device_berbeda || 0) > 0;
        var markerColor = '#28a745'; // normal
        if (hasLuarRadius && hasDeviceIssue) {
            markerColor = '#6f42c1'; // keduanya
        } else if (hasLuarRadius) {
            markerColor = '#dc3545'; // luar radius
        } else if (hasDeviceIssue) {
            markerColor = '#fd7e14'; // device berbeda
        }

        // Buat custom icon dengan nomor
        var customIcon = L.divIcon({
            className: 'custom-marker',
            html: '<div style="background-color: ' + markerColor + '; color: white; width: 30px; height: 30px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: bold; border: 2px solid white; box-shadow: 0 2px 4px rgba(0,0,0,0.3);">' + (index + 1) + '</div>',
            iconSize: [30, 30],
            iconAnchor: [15, 15],
            popupAnchor: [0, -15]
        });
        
        // Buat marker
        var marker = L.marker([lat, lng], {
            icon: customIcon,
            title: location.nama_instansi
        }).addTo(map);
        
        // Buat tooltip content (untuk hover)
        var tooltipContent = '<div style="text-align: left; padding: 5px; min-width: 200px;">' +
            '<strong style="color: #007bff; font-size: 13px;"><i class="fas fa-building"></i> ' + 
            escapeHtml(location.nama_instansi) + '</strong><br>' +
            '<div style="margin-top: 5px; font-size: 12px;">' +
            '<strong>Mahasiswa:</strong> ' + escapeHtml(location.nama_mahasiswa) + '<br>' +
            '<strong>Pembimbing:</strong> ' + escapeHtml(location.pembimbing_nama || '-') +
            '</div>' +
            '</div>';
        
        // Bind tooltip untuk hover
        marker.bindTooltip(tooltipContent, {
            permanent: false,
            direction: 'top',
            className: 'custom-tooltip'
        });
        
        // Buat popup content (untuk click)
        var popupContent = '<div style="min-width: 250px; padding: 5px;">' +
            '<h6 style="margin: 0 0 10px 0; color: #007bff;"><i class="fas fa-building"></i> ' + 
            escapeHtml(location.nama_instansi) + '</h6>' +
            '<p style="margin: 5px 0;"><strong>Mahasiswa:</strong><br>' +
            escapeHtml(location.nim) + ' - ' + escapeHtml(location.nama_mahasiswa) + 
            ' (' + escapeHtml(location.kelas) + ')</p>' +
            '<p style="margin: 5px 0;"><strong>Alamat:</strong><br>' + 
            escapeHtml(location.alamat_instansi || '-') + '</p>' +
            '<p style="margin: 5px 0;"><strong>Judul PKL:</strong><br>' + 
            escapeHtml(location.judul_pkl) + '</p>' +
            '<p style="margin: 5px 0;"><strong>Pembimbing:</strong><br>' +
            'Akademik: ' + escapeHtml(location.pembimbing_nama || '-') + '<br>' +
            'Lapangan: ' + escapeHtml(location.nama_pembimbing_lapangan || '-') + 
            (location.no_hp_pembimbing_lapangan ? ' (' + escapeHtml(location.no_hp_pembimbing_lapangan) + ')' : '') +
            '</p>' +
            '<p style="margin: 5px 0; font-size: 11px; color: #666;"><strong>Koordinat:</strong> ' +
            parseFloat(location.latitude).toFixed(6) + ', ' + parseFloat(location.longitude).toFixed(6) + '</p>' +
            '<p style="margin: 5px 0;"><strong>Anomali:</strong><br>' +
            (hasLuarRadius ? '<span style="color:#dc3545;">Luar radius: ' + Number(location.total_luar_radius || 0) + '</span><br>' : '') +
            (hasDeviceIssue ? '<span style="color:#fd7e14;">Device berbeda: ' + Number(location.total_device_berbeda || 0) + '</span><br>' : '') +
            ((!hasLuarRadius && !hasDeviceIssue) ? '<span style="color:#28a745;">Tidak ada anomali utama</span>' : '') +
            '</p>' +
            '</div>';
        
        marker.bindPopup(popupContent);
        
        // Tambahkan ke bounds
        bounds.extend([lat, lng]);
        
        var anomalyMarkerRef = null;

        // Overlay marker khusus titik anomali terbaru.
        if ((hasLuarRadius || hasDeviceIssue) &&
            location.anomali_latitude !== null &&
            location.anomali_longitude !== null) {
            var anomalyColor = hasLuarRadius && hasDeviceIssue ? '#6f42c1' : (hasLuarRadius ? '#dc3545' : '#fd7e14');
            var anomalyLat = parseFloat(location.anomali_latitude);
            var anomalyLng = parseFloat(location.anomali_longitude);
            if (!isNaN(anomalyLat) && !isNaN(anomalyLng)) {
                var anomalyMarker = L.circleMarker([anomalyLat, anomalyLng], {
                    radius: 8,
                    color: anomalyColor,
                    weight: 2,
                    fillColor: anomalyColor,
                    fillOpacity: 0.35
                }).addTo(map);

                anomalyMarker.bindPopup(
                    '<div style="min-width:220px;">' +
                    '<strong>Anomali Absensi Terbaru</strong><br>' +
                    escapeHtml(location.nama_mahasiswa) + ' (' + escapeHtml(location.nim) + ')<br>' +
                    (location.last_anomali_at ? ('<small>' + escapeHtml(location.last_anomali_at) + '</small><br>') : '') +
                    (hasLuarRadius ? ('<span style="color:#dc3545;">Luar radius: ' + Number(location.total_luar_radius || 0) + '</span><br>') : '') +
                    (hasDeviceIssue ? ('<span style="color:#fd7e14;">Device berbeda: ' + Number(location.total_device_berbeda || 0) + '</span><br>') : '') +
                    '</div>'
                );

                anomalyMarkerRef = anomalyMarker;
                bounds.extend([anomalyLat, anomalyLng]);
            }
        }

        markerRecords.push({
            primaryMarker: marker,
            anomalyMarker: anomalyMarkerRef,
            hasAnomali: (hasLuarRadius || hasDeviceIssue),
            tableRowId: 'lokasi-row-' + index
        });
    });
    
    // Fit bounds untuk menampilkan semua marker
    if (markerRecords.length > 0) {
        map.fitBounds(bounds, {
            padding: [50, 50],
            maxZoom: 15
        });
    }

    function applyMapFilter(filterType) {
        markerRecords.forEach(function(record) {
            var isAnomali = record.hasAnomali === true;
            var shouldShow = (filterType === 'all') ||
                (filterType === 'anomali' && isAnomali) ||
                (filterType === 'normal' && !isAnomali);

            var hasPrimary = map.hasLayer(record.primaryMarker);
            if (shouldShow && !hasPrimary) {
                record.primaryMarker.addTo(map);
            } else if (!shouldShow && hasPrimary) {
                map.removeLayer(record.primaryMarker);
            }

            if (record.anomalyMarker) {
                var hasAnomalyLayer = map.hasLayer(record.anomalyMarker);
                if (shouldShow && isAnomali && !hasAnomalyLayer) {
                    record.anomalyMarker.addTo(map);
                } else if ((!shouldShow || !isAnomali) && hasAnomalyLayer) {
                    map.removeLayer(record.anomalyMarker);
                }
            }

            var rowEl = document.getElementById(record.tableRowId);
            if (rowEl) {
                rowEl.style.display = shouldShow ? '' : 'none';
            }
        });
    }

    document.querySelectorAll('.js-map-filter').forEach(function(btn) {
        btn.addEventListener('click', function() {
            var targetFilter = this.getAttribute('data-filter') || 'all';
            document.querySelectorAll('.js-map-filter').forEach(function(otherBtn) {
                otherBtn.classList.remove('active', 'btn-primary', 'btn-danger', 'btn-success');
                otherBtn.classList.add('btn-outline-primary');
                if (otherBtn.getAttribute('data-filter') === 'anomali') {
                    otherBtn.classList.remove('btn-outline-primary');
                    otherBtn.classList.add('btn-outline-danger');
                } else if (otherBtn.getAttribute('data-filter') === 'normal') {
                    otherBtn.classList.remove('btn-outline-primary');
                    otherBtn.classList.add('btn-outline-success');
                }
            });

            this.classList.remove('btn-outline-primary', 'btn-outline-danger', 'btn-outline-success');
            if (targetFilter === 'anomali') {
                this.classList.add('btn-danger');
            } else if (targetFilter === 'normal') {
                this.classList.add('btn-success');
            } else {
                this.classList.add('btn-primary');
            }
            this.classList.add('active');

            applyMapFilter(targetFilter);
        });
    });
});

// Helper function untuk escape HTML
function escapeHtml(text) {
    if (!text) return '';
    var map = {
        '&': '&amp;',
        '<': '&lt;',
        '>': '&gt;',
        '"': '&quot;',
        "'": '&#039;'
    };
    return text.toString().replace(/[&<>"']/g, function(m) { return map[m]; });
}
</script>
<?php endif; ?>

