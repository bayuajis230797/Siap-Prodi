<div class="row">
    <div class="col-12">
        <div class="card card-primary card-outline">
            <div class="card-header">
                <h3 class="card-title">Tambah Kegiatan Baru</h3>
            </div>
            <div class="card-body">
                <?php echo form_open('Kegiatan/store', array('id' => 'formKegiatan')); ?>
                
                <div class="row">
                    <div class="col-md-12">
                        <div class="form-group">
                            <label for="nama_kegiatan">Nama Kegiatan <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="nama_kegiatan" name="nama_kegiatan" 
                                   value="<?php echo set_value('nama_kegiatan'); ?>" required>
                            <?php echo form_error('nama_kegiatan', '<small class="text-danger">', '</small>'); ?>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-12">
                        <div class="form-group">
                            <label for="deskripsi">Deskripsi</label>
                            <textarea class="form-control" id="deskripsi" name="deskripsi" rows="3" 
                                      placeholder="Deskripsi kegiatan..."><?php echo set_value('deskripsi'); ?></textarea>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="tanggal_kegiatan">Tanggal Kegiatan <span class="text-danger">*</span></label>
                            <input type="date" class="form-control" id="tanggal_kegiatan" name="tanggal_kegiatan" 
                                   value="<?php echo set_value('tanggal_kegiatan'); ?>" required>
                            <?php echo form_error('tanggal_kegiatan', '<small class="text-danger">', '</small>'); ?>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="jam_mulai">Jam Mulai (WIB) <span class="text-danger">*</span></label>
                            <input type="time" class="form-control" id="jam_mulai" name="jam_mulai" 
                                   value="<?php echo set_value('jam_mulai'); ?>" required>
                            <small class="text-muted">Format 24 jam (contoh: 08:00, 14:30)</small>
                            <?php echo form_error('jam_mulai', '<small class="text-danger">', '</small>'); ?>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="jam_selesai">Jam Selesai (WIB)</label>
                            <input type="time" class="form-control" id="jam_selesai" name="jam_selesai" 
                                   value="<?php echo set_value('jam_selesai'); ?>">
                            <small class="text-muted">Format 24 jam (contoh: 10:00, 16:30)</small>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="jenis_kegiatan">Jenis Kegiatan <span class="text-danger">*</span></label>
                            <select class="form-control" id="jenis_kegiatan" name="jenis_kegiatan" required>
                                <option value="akademik" <?php echo set_value('jenis_kegiatan') == 'akademik' ? 'selected' : ''; ?>>Akademik</option>
                                <option value="non_akademik" <?php echo set_value('jenis_kegiatan') == 'non_akademik' ? 'selected' : ''; ?>>Non-Akademik</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="kuota_peserta">Kuota Peserta</label>
                            <input type="number" class="form-control" id="kuota_peserta" name="kuota_peserta" 
                                   value="<?php echo set_value('kuota_peserta'); ?>" min="1" placeholder="Opsional">
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-12">
                        <div class="form-group">
                            <label for="lokasi">Lokasi</label>
                            <input type="text" class="form-control" id="lokasi" name="lokasi" 
                                   value="<?php echo set_value('lokasi'); ?>" placeholder="Nama lokasi kegiatan">
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="latitude">Latitude (Koordinat GPS)</label>
                            <input type="number" step="any" class="form-control" id="latitude" name="latitude" 
                                   value="<?php echo set_value('latitude'); ?>" placeholder="-7.12345678">
                            <small class="text-muted">Koordinat GPS untuk validasi lokasi absensi. Dapat diisi manual atau menggunakan tombol "Ambil Lokasi Saat Ini"</small>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="longitude">Longitude (Koordinat GPS)</label>
                            <input type="number" step="any" class="form-control" id="longitude" name="longitude" 
                                   value="<?php echo set_value('longitude'); ?>" placeholder="110.12345678">
                            <small class="text-muted">Koordinat GPS untuk validasi lokasi absensi. Dapat diisi manual atau menggunakan tombol "Ambil Lokasi Saat Ini"</small>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="radius_meter">Radius Maksimal (meter)</label>
                            <input type="number" class="form-control" id="radius_meter" name="radius_meter" 
                                   value="<?php echo set_value('radius_meter', 20); ?>" min="1" required>
                            <small class="text-muted">Jarak maksimal dari titik kegiatan untuk absensi (default: 20 meter)</small>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>&nbsp;</label>
                            <button type="button" class="btn btn-info btn-block" onclick="getCurrentLocation()">
                                <i class="fas fa-map-marker-alt"></i> Ambil Lokasi Saat Ini
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Peta Google Maps untuk Preview Lokasi -->
                <div class="row">
                    <div class="col-md-12">
                        <div class="form-group">
                            <label>Preview Lokasi di Peta</label>
                            <div id="map-preview" style="width: 100%; height: 400px; border: 1px solid #ddd; border-radius: 4px;"></div>
                            <small class="text-muted">Peta akan menampilkan lokasi yang diambil. Pastikan lokasi akurat sebelum menyimpan.</small>
                        </div>
                    </div>
                </div>

                <hr>
                <h5>Filter Kelas (Opsional)</h5>
                <div class="row">
                    <div class="col-md-12">
                        <div class="form-group" style="max-height: 200px; overflow-y: auto; border: 1px solid #ddd; padding: 10px; border-radius: 4px;">
                            <?php if (!empty($kelas_list)): ?>
                                <?php foreach ($kelas_list as $kelas): ?>
                                    <div class="form-check">
                                        <input class="form-check-input kelas-checkbox" type="checkbox" name="kelas[]" 
                                               value="<?php echo htmlspecialchars($kelas->kelas); ?>" 
                                               id="kelas_<?php echo htmlspecialchars($kelas->kelas); ?>"
                                               onchange="loadMahasiswaByKelas()">
                                        <label class="form-check-label" for="kelas_<?php echo htmlspecialchars($kelas->kelas); ?>">
                                            <?php echo htmlspecialchars($kelas->kelas); ?> 
                                            <small class="text-muted">(<?php echo $kelas->jumlah_mahasiswa; ?> mahasiswa)</small>
                                        </label>
                                    </div>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <p class="text-muted">Tidak ada data kelas.</p>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <hr>
                <h5>Pilih Mahasiswa Peserta</h5>
                <div class="row">
                    <div class="col-md-12">
                        <div class="form-group" id="mahasiswa-container" style="max-height: 300px; overflow-y: auto; border: 1px solid #ddd; padding: 10px; border-radius: 4px;">
                            <p class="text-muted">Pilih kelas terlebih dahulu atau pilih mahasiswa secara manual.</p>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-12">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> Simpan Kegiatan
                        </button>
                        <a href="<?php echo site_url('Kegiatan'); ?>" class="btn btn-secondary">
                            <i class="fas fa-times"></i> Batal
                        </a>
                    </div>
                </div>

                <?php echo form_close(); ?>
            </div>
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
let circle;

// Initialize map
function initMap() {
    // Default location (Jakarta)
    const defaultLat = -6.2088;
    const defaultLng = 106.8456;
    
    // Initialize Leaflet map
    map = L.map('map-preview').setView([defaultLat, defaultLng], 15);

    // Add OpenStreetMap tile layer
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '© OpenStreetMap contributors',
        maxZoom: 19
    }).addTo(map);

    // Update map saat koordinat berubah
    updateMapFromCoordinates();
}

// Update peta berdasarkan koordinat di form
function updateMapFromCoordinates() {
    const lat = parseFloat(document.getElementById('latitude').value);
    const lng = parseFloat(document.getElementById('longitude').value);
    const radius = parseFloat(document.getElementById('radius_meter').value) || 20;

    if (!isNaN(lat) && !isNaN(lng)) {
        // Update map center
        map.setView([lat, lng], 17);

        // Hapus marker dan circle lama jika ada
        if (marker) {
            map.removeLayer(marker);
        }
        if (circle) {
            map.removeLayer(circle);
        }

        // Buat marker baru (dapat di-drag)
        marker = L.marker([lat, lng], {
            draggable: true,
            title: 'Lokasi Kegiatan'
        }).addTo(map);

        // Buat circle untuk radius (dalam meter)
        circle = L.circle([lat, lng], {
            color: '#FF0000',
            fillColor: '#FF0000',
            fillOpacity: 0.35,
            radius: radius
        }).addTo(map);

        // Popup untuk menampilkan koordinat
        const popupContent = `
            <div style="padding: 5px;">
                <strong>Lokasi Kegiatan</strong><br>
                Latitude: ${lat.toFixed(8)}<br>
                Longitude: ${lng.toFixed(8)}<br>
                Radius: ${radius} meter
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
            
            // Update circle center
            circle.setLatLng([newLat, newLng]);
            
            // Update popup
            const updatedPopup = `
                <div style="padding: 5px;">
                    <strong>Lokasi Kegiatan</strong><br>
                    Latitude: ${newLat.toFixed(8)}<br>
                    Longitude: ${newLng.toFixed(8)}<br>
                    Radius: ${radius} meter
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
    if (navigator.geolocation) {
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
    } else {
        alert('Browser Anda tidak mendukung Geolocation API.');
    }
}

// Load mahasiswa berdasarkan kelas yang dipilih
function fetchMahasiswaByKelas(kelas) {
    return fetch('<?php echo site_url("Kegiatan/get_mahasiswa_by_kelas"); ?>?kelas=' + encodeURIComponent(kelas), {
        headers: { 'X-Requested-With': 'XMLHttpRequest' }
    })
        .then(function(response) {
            if (!response.ok) {
                throw new Error('HTTP ' + response.status);
            }
            return response.json();
        })
        .then(function(data) {
            if (data.success) {
                return data.mahasiswa || data.data || [];
            }
            throw new Error(data.message || 'Gagal memuat data mahasiswa.');
        });
}

function loadMahasiswaByKelas() {
    const kelasSelected = [];
    document.querySelectorAll('.kelas-checkbox:checked').forEach(function(checkbox) {
        kelasSelected.push(checkbox.value);
    });
    
    const container = document.getElementById('mahasiswa-container');
    if (!container) {
        console.error('Container mahasiswa-container tidak ditemukan');
        return;
    }
    
    if (kelasSelected.length === 0) {
        container.innerHTML = '<p class="text-muted">Pilih kelas terlebih dahulu.</p>';
        return;
    }
    
    container.innerHTML = '<p class="text-muted"><i class="fas fa-spinner fa-spin"></i> Memuat data mahasiswa...</p>';
    
    Promise.all(kelasSelected.map(fetchMahasiswaByKelas))
        .then(function(results) {
            const allMahasiswa = [];
            results.forEach(function(list) {
                list.forEach(function(mhs) {
                    if (!allMahasiswa.find(function(m) { return m.id == mhs.id; })) {
                        allMahasiswa.push(mhs);
                    }
                });
            });
            renderMahasiswaList(allMahasiswa);
        })
        .catch(function(error) {
            console.error('Error loading mahasiswa:', error);
            container.innerHTML = '<p class="text-danger">Gagal memuat data mahasiswa. Silakan refresh halaman dan coba lagi.</p>';
        });
}

// Render daftar mahasiswa
function renderMahasiswaList(mahasiswaList) {
    const container = document.getElementById('mahasiswa-container');
    
    if (!container) {
        console.error('Container mahasiswa-container tidak ditemukan');
        return;
    }
    
    if (mahasiswaList.length === 0) {
        container.innerHTML = '<p class="text-muted">Tidak ada mahasiswa di kelas yang dipilih.</p>';
        return;
    }
    
    let html = '<div class="form-check mb-2">';
    html += '<input class="form-check-input" type="checkbox" id="select_all_mahasiswa" onchange="toggleAllMahasiswa(this.checked)">';
    html += '<label class="form-check-label font-weight-bold" for="select_all_mahasiswa">Pilih Semua</label>';
    html += '</div>';
    html += '<hr>';
    
    mahasiswaList.forEach(function(mhs) {
        html += '<div class="form-check">';
        html += '<input class="form-check-input mahasiswa-checkbox" type="checkbox" name="peserta[]" ';
        html += 'value="' + escapeHtml(mhs.id) + '" id="mhs_' + escapeHtml(mhs.id) + '">';
        html += '<label class="form-check-label" for="mhs_' + escapeHtml(mhs.id) + '">';
        html += escapeHtml(mhs.nama_mahasiswa) + ' <small class="text-muted">(' + escapeHtml(mhs.nim) + ' - ' + escapeHtml(mhs.kelas) + ')</small>';
        html += '</label>';
        html += '</div>';
    });
    
    container.innerHTML = html;
}

// Toggle semua mahasiswa
function toggleAllMahasiswa(checked) {
    document.querySelectorAll('.mahasiswa-checkbox').forEach(function(checkbox) {
        checkbox.checked = checked;
    });
}

// Helper function untuk escape HTML
function escapeHtml(text) {
    if (!text) return '';
    const map = {
        '&': '&amp;',
        '<': '&lt;',
        '>': '&gt;',
        '"': '&quot;',
        "'": '&#039;'
    };
    return String(text).replace(/[&<>"']/g, function(m) { return map[m]; });
}

// Update circle radius saat radius_meter berubah
document.addEventListener('DOMContentLoaded', function() {
    const radiusInput = document.getElementById('radius_meter');
    if (radiusInput) {
        radiusInput.addEventListener('change', function() {
            const radius = parseFloat(this.value) || 20;
            if (circle) {
                circle.setRadius(radius);
                // Update popup jika marker ada
                if (marker) {
                    const lat = parseFloat(document.getElementById('latitude').value);
                    const lng = parseFloat(document.getElementById('longitude').value);
                    if (!isNaN(lat) && !isNaN(lng)) {
                        const popupContent = `
                            <div style="padding: 5px;">
                                <strong>Lokasi Kegiatan</strong><br>
                                Latitude: ${lat.toFixed(8)}<br>
                                Longitude: ${lng.toFixed(8)}<br>
                                Radius: ${radius} meter
                            </div>
                        `;
                        marker.setPopupContent(popupContent);
                    }
                }
            }
            // Jika sudah ada koordinat, update map
            if (marker) {
                updateMapFromCoordinates();
            }
        });
    }
    
    // Event listener untuk update peta saat koordinat diinput manual
    const latitudeInput = document.getElementById('latitude');
    const longitudeInput = document.getElementById('longitude');
    
    if (latitudeInput) {
        // Gunakan debounce untuk menghindari update terlalu sering saat user mengetik
        let latitudeTimeout;
        latitudeInput.addEventListener('input', function() {
            clearTimeout(latitudeTimeout);
            latitudeTimeout = setTimeout(function() {
                updateMapFromCoordinates();
            }, 500); // Update setelah 500ms user berhenti mengetik
        });
    }
    
    if (longitudeInput) {
        // Gunakan debounce untuk menghindari update terlalu sering saat user mengetik
        let longitudeTimeout;
        longitudeInput.addEventListener('input', function() {
            clearTimeout(longitudeTimeout);
            longitudeTimeout = setTimeout(function() {
                updateMapFromCoordinates();
            }, 500); // Update setelah 500ms user berhenti mengetik
        });
    }
    
    // Initialize map saat halaman load
    initMap();
});
</script>


