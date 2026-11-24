<div class="row">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title"><?php echo $tempat_pkl ? 'Edit' : 'Input'; ?> Data Tempat PKL</h3>
            </div>
            <?php echo form_open('Pkl/input_tempat', array('method' => 'post')); ?>
            <div class="card-body">
                <?php if (validation_errors()): ?>
                    <div class="alert alert-danger">
                        <?php echo validation_errors(); ?>
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

                <?php if ($pembimbing_sudah_ditetapkan): ?>
                    <div class="alert alert-warning">
                        <i class="fas fa-exclamation-triangle"></i>
                        <strong>Perhatian:</strong> Tempat PKL sudah disetujui dan pembimbing sudah ditentukan. Anda tidak dapat mengubah data tempat PKL.
                    </div>
                <?php endif; ?>

                <div class="form-group">
                    <label for="nama_instansi">Nama Instansi <span class="text-danger">*</span></label>
                    <input type="text" name="nama_instansi" id="nama_instansi" class="form-control" 
                           value="<?php echo set_value('nama_instansi', $tempat_pkl ? $tempat_pkl->nama_instansi : ''); ?>" 
                           <?php echo $pembimbing_sudah_ditetapkan ? 'readonly' : 'required'; ?>>
                    <?php echo form_error('nama_instansi', '<small class="text-danger">', '</small>'); ?>
                </div>

                <div class="form-group">
                    <label for="alamat_instansi">Alamat Instansi <span class="text-danger">*</span></label>
                    <textarea name="alamat_instansi" id="alamat_instansi" class="form-control" rows="3" <?php echo $pembimbing_sudah_ditetapkan ? 'readonly' : 'required'; ?>><?php echo set_value('alamat_instansi', $tempat_pkl ? $tempat_pkl->alamat_instansi : ''); ?></textarea>
                    <?php echo form_error('alamat_instansi', '<small class="text-danger">', '</small>'); ?>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="tanggal_mulai">Tanggal Mulai PKL <span class="text-danger">*</span></label>
                            <input type="date" name="tanggal_mulai" id="tanggal_mulai" class="form-control" 
                                   value="<?php echo set_value('tanggal_mulai', $tempat_pkl && $tempat_pkl->tanggal_mulai ? date('Y-m-d', strtotime($tempat_pkl->tanggal_mulai)) : ''); ?>" 
                                   <?php echo $pembimbing_sudah_ditetapkan ? 'readonly' : 'required'; ?>>
                            <?php echo form_error('tanggal_mulai', '<small class="text-danger">', '</small>'); ?>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="tanggal_selesai">Tanggal Selesai PKL <span class="text-danger">*</span></label>
                            <input type="date" name="tanggal_selesai" id="tanggal_selesai" class="form-control" 
                                   value="<?php echo set_value('tanggal_selesai', $tempat_pkl && $tempat_pkl->tanggal_selesai ? date('Y-m-d', strtotime($tempat_pkl->tanggal_selesai)) : ''); ?>" 
                                   <?php echo $pembimbing_sudah_ditetapkan ? 'readonly' : 'required'; ?>>
                            <?php echo form_error('tanggal_selesai', '<small class="text-danger">', '</small>'); ?>
                        </div>
                    </div>
                </div>

                <hr>
                <h5 class="mb-3"><i class="fas fa-user-tie"></i> Data Pembimbing Lapangan (Opsional)</h5>

                <div class="form-group">
                    <label for="nama_pembimbing_lapangan">Nama Pembimbing Lapangan</label>
                    <input type="text" name="nama_pembimbing_lapangan" id="nama_pembimbing_lapangan" class="form-control" 
                           value="<?php echo set_value('nama_pembimbing_lapangan', $tempat_pkl ? $tempat_pkl->nama_pembimbing_lapangan : ''); ?>"
                           <?php echo $pembimbing_sudah_ditetapkan ? 'readonly' : ''; ?>>
                </div>

                <div class="form-group">
                    <label for="jabatan_pembimbing_lapangan">Jabatan Pembimbing Lapangan</label>
                    <input type="text" name="jabatan_pembimbing_lapangan" id="jabatan_pembimbing_lapangan" class="form-control" 
                           value="<?php echo set_value('jabatan_pembimbing_lapangan', $tempat_pkl ? $tempat_pkl->jabatan_pembimbing_lapangan : ''); ?>"
                           <?php echo $pembimbing_sudah_ditetapkan ? 'readonly' : ''; ?>>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="no_hp_pembimbing_lapangan">No. HP Pembimbing Lapangan</label>
                            <input type="text" name="no_hp_pembimbing_lapangan" id="no_hp_pembimbing_lapangan" class="form-control" 
                                   value="<?php echo set_value('no_hp_pembimbing_lapangan', $tempat_pkl ? $tempat_pkl->no_hp_pembimbing_lapangan : ''); ?>"
                                   <?php echo $pembimbing_sudah_ditetapkan ? 'readonly' : ''; ?>>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="email_pembimbing_lapangan">Email Pembimbing Lapangan</label>
                            <input type="email" name="email_pembimbing_lapangan" id="email_pembimbing_lapangan" class="form-control" 
                                   value="<?php echo set_value('email_pembimbing_lapangan', $tempat_pkl ? $tempat_pkl->email_pembimbing_lapangan : ''); ?>"
                                   <?php echo $pembimbing_sudah_ditetapkan ? 'readonly' : ''; ?>>
                        </div>
                    </div>
                </div>

                <hr>
                <h5 class="mb-3"><i class="fas fa-map-marker-alt"></i> Koordinat GPS Tempat PKL <span class="text-danger">*</span></h5>
                <div class="alert alert-info">
                    <i class="fas fa-info-circle"></i> 
                    <strong>Penting:</strong> Koordinat GPS diperlukan untuk sistem absensi harian. 
                    Anda dapat mengambil koordinat dengan dua cara:
                    <ul class="mb-0 mt-2">
                        <li>Klik tombol "Ambil Lokasi Saat Ini" jika Anda sedang berada di tempat PKL</li>
                        <li>Atau salin koordinat dari Google Maps (klik kanan pada lokasi → pilih koordinat)</li>
                    </ul>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="latitude">Latitude (Koordinat GPS) <span class="text-danger">*</span></label>
                            <input type="number" step="any" name="latitude" id="latitude" class="form-control" 
                                   value="<?php echo set_value('latitude', $tempat_pkl ? $tempat_pkl->latitude : ''); ?>" 
                                   placeholder="-6.938820950051878" 
                                   <?php echo $pembimbing_sudah_ditetapkan ? 'readonly' : 'required'; ?>>
                            <small class="text-muted">Contoh: -6.938820950051878</small>
                            <?php echo form_error('latitude', '<small class="text-danger">', '</small>'); ?>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="longitude">Longitude (Koordinat GPS) <span class="text-danger">*</span></label>
                            <input type="number" step="any" name="longitude" id="longitude" class="form-control" 
                                   value="<?php echo set_value('longitude', $tempat_pkl ? $tempat_pkl->longitude : ''); ?>" 
                                   placeholder="109.15060538276545" 
                                   <?php echo $pembimbing_sudah_ditetapkan ? 'readonly' : 'required'; ?>>
                            <small class="text-muted">Contoh: 109.15060538276545</small>
                            <?php echo form_error('longitude', '<small class="text-danger">', '</small>'); ?>
                        </div>
                    </div>
                </div>

                <?php if (!$pembimbing_sudah_ditetapkan): ?>
                <div class="row">
                    <div class="col-md-12">
                        <div class="form-group">
                            <button type="button" class="btn btn-info" onclick="getCurrentLocation()">
                                <i class="fas fa-map-marker-alt"></i> Ambil Lokasi Saat Ini
                            </button>
                            <small class="text-muted ml-2">Gunakan tombol ini jika Anda sedang berada di tempat PKL</small>
                        </div>
                    </div>
                </div>
                <?php endif; ?>

                <!-- Peta Google Maps untuk Preview Lokasi -->
                <div class="row">
                    <div class="col-md-12">
                        <div class="form-group">
                            <label>Preview Lokasi di Peta</label>
                            <div id="map-preview" style="width: 100%; height: 400px; border: 1px solid #ddd; border-radius: 4px;"></div>
                            <small class="text-muted">Peta akan menampilkan lokasi yang diambil. Pastikan lokasi akurat sebelum menyimpan. Anda bisa menggeser marker untuk menyesuaikan posisi.</small>
                        </div>
                    </div>
                </div>
            </div>
            <div class="card-footer">
                <?php if (!$pembimbing_sudah_ditetapkan): ?>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Simpan
                    </button>
                <?php endif; ?>
                <a href="<?php echo site_url('Pkl'); ?>" class="btn btn-secondary">
                    <i class="fas fa-times"></i> Batal
                </a>
            </div>
            <?php echo form_close(); ?>
        </div>
    </div>
</div>

<!-- Leaflet CSS -->
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin=""/>
<!-- Leaflet JS -->
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>
<style>
#map-preview { z-index: 0; }
</style>
<script>
let map;
let marker;

// Initialize map
function initMap() {
    // Default location (Tegal)
    const defaultLat = -6.8667;
    const defaultLng = 109.1333;
    
    // Get existing coordinates or use default
    const existingLat = parseFloat(document.getElementById('latitude').value) || defaultLat;
    const existingLng = parseFloat(document.getElementById('longitude').value) || defaultLng;
    
    // Initialize Leaflet map
    map = L.map('map-preview').setView([existingLat, existingLng], existingLat !== defaultLat ? 17 : 13);

    // Add OpenStreetMap tile layer
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '© OpenStreetMap contributors',
        maxZoom: 19
    }).addTo(map);

    // Update map saat koordinat berubah
    updateMapFromCoordinates();
    
    // Sync coordinates when manually input
    document.getElementById('latitude').addEventListener('input', syncCoordinates);
    document.getElementById('longitude').addEventListener('input', syncCoordinates);
}

// Sync coordinates between latitude and longitude fields
function syncCoordinates() {
    const lat = parseFloat(document.getElementById('latitude').value);
    const lng = parseFloat(document.getElementById('longitude').value);
    
    if (!isNaN(lat) && !isNaN(lng)) {
        updateMapFromCoordinates();
    }
}

// Update peta berdasarkan koordinat di form
function updateMapFromCoordinates() {
    const lat = parseFloat(document.getElementById('latitude').value);
    const lng = parseFloat(document.getElementById('longitude').value);

    if (!isNaN(lat) && !isNaN(lng)) {
        // Update map center
        map.setView([lat, lng], 17);

        // Hapus marker lama jika ada
        if (marker) {
            map.removeLayer(marker);
        }

        // Buat marker baru (dapat di-drag)
        marker = L.marker([lat, lng], {
            draggable: true,
            title: 'Lokasi Tempat PKL'
        }).addTo(map);

        // Popup untuk menampilkan koordinat
        const popupContent = `
            <div style="padding: 5px;">
                <strong>Lokasi Tempat PKL</strong><br>
                Latitude: ${lat.toFixed(8)}<br>
                Longitude: ${lng.toFixed(8)}
            </div>
        `;
        marker.bindPopup(popupContent).openPopup();

        // Event listener saat marker di-drag
        marker.on('dragend', function(event) {
            const position = marker.getLatLng();
            const newLat = position.lat;
            const newLng = position.lng;
            
            document.getElementById('latitude').value = newLat.toFixed(8);
            document.getElementById('longitude').value = newLng.toFixed(8);
            
            // Update popup
            const updatedPopup = `
                <div style="padding: 5px;">
                    <strong>Lokasi Tempat PKL</strong><br>
                    Latitude: ${newLat.toFixed(8)}<br>
                    Longitude: ${newLng.toFixed(8)}
                </div>
            `;
            marker.setPopupContent(updatedPopup).openPopup();
        });

        // Event listener saat marker diklik
        marker.on('click', function() {
            marker.openPopup();
        });
    }
}

// Ambil lokasi GPS saat ini
function getCurrentLocation() {
    if (!navigator.geolocation) {
        alert('Browser Anda tidak mendukung Geolocation API.');
        return;
    }

    const btn = event.target.closest('button');
    const originalHtml = btn.innerHTML;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Mengambil lokasi...';
    btn.disabled = true;

    navigator.geolocation.getCurrentPosition(
        function(position) {
            const lat = position.coords.latitude;
            const lng = position.coords.longitude;
            const accuracy = position.coords.accuracy; // Akurasi dalam meter
            
            document.getElementById('latitude').value = lat.toFixed(8);
            document.getElementById('longitude').value = lng.toFixed(8);
            
            // Update peta
            updateMapFromCoordinates();
            
            btn.innerHTML = originalHtml;
            btn.disabled = false;
            
            alert('Lokasi GPS berhasil diambil!\nAkurasi: ±' + Math.round(accuracy) + ' meter\n\nPeriksa peta di bawah untuk memastikan lokasi akurat. Anda bisa menggeser marker untuk menyesuaikan posisi.');
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
            console.error('Geolocation error:', error);
            btn.innerHTML = originalHtml;
            btn.disabled = false;
        },
        {
            enableHighAccuracy: true,
            timeout: 10000,
            maximumAge: 0
        }
    );
}

// Initialize map when page loads
document.addEventListener('DOMContentLoaded', function() {
    initMap();
});
</script>

