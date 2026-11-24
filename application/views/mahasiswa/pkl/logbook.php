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
    <!-- Form Input Logbook -->
    <div class="col-md-8">
        <div class="card card-primary card-outline">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-book"></i> Input Logbook Harian
                </h3>
            </div>
            <div class="card-body">
                <?php echo form_open('Pkl/logbook', array('id' => 'formLogbook')); ?>
                
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="tanggal">Tanggal <span class="text-danger">*</span></label>
                            <input type="date" name="tanggal" id="tanggal" class="form-control" 
                                   value="<?php echo set_value('tanggal', date('Y-m-d')); ?>" 
                                   max="<?php echo date('Y-m-d'); ?>" required>
                            <?php echo form_error('tanggal', '<small class="text-danger">', '</small>'); ?>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="jam_mulai">Jam Mulai <span class="text-danger">*</span></label>
                            <input type="time" name="jam_mulai" id="jam_mulai" class="form-control" 
                                   value="<?php echo set_value('jam_mulai'); ?>" required>
                            <?php echo form_error('jam_mulai', '<small class="text-danger">', '</small>'); ?>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="jam_selesai">Jam Selesai <span class="text-danger">*</span></label>
                            <input type="time" name="jam_selesai" id="jam_selesai" class="form-control" 
                                   value="<?php echo set_value('jam_selesai'); ?>" required>
                            <?php echo form_error('jam_selesai', '<small class="text-danger">', '</small>'); ?>
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <label for="aktivitas">Aktivitas Harian <span class="text-danger">*</span></label>
                    <textarea name="aktivitas" id="aktivitas" class="form-control" rows="5" 
                              placeholder="Jelaskan aktivitas yang dilakukan selama PKL hari ini..." required><?php echo set_value('aktivitas'); ?></textarea>
                    <small class="text-muted">Minimal 10 karakter. Jelaskan secara detail aktivitas yang dilakukan.</small>
                    <?php echo form_error('aktivitas', '<small class="text-danger">', '</small>'); ?>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="latitude">Latitude (Koordinat GPS) <small class="text-muted">(Opsional)</small></label>
                            <input type="number" step="any" name="latitude" id="latitude" class="form-control" 
                                   value="<?php echo set_value('latitude'); ?>" 
                                   placeholder="-6.938820950051878" readonly>
                            <small class="text-muted">Koordinat GPS saat input logbook</small>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="longitude">Longitude (Koordinat GPS) <small class="text-muted">(Opsional)</small></label>
                            <input type="number" step="any" name="longitude" id="longitude" class="form-control" 
                                   value="<?php echo set_value('longitude'); ?>" 
                                   placeholder="109.15060538276545" readonly>
                            <small class="text-muted">Koordinat GPS saat input logbook</small>
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <button type="button" class="btn btn-info btn-sm" onclick="getCurrentLocation()">
                        <i class="fas fa-map-marker-alt"></i> Ambil Lokasi Saat Ini (Opsional)
                    </button>
                    <small class="text-muted ml-2">Gunakan untuk mencatat koordinat saat input logbook</small>
                </div>

                <div class="form-group">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Simpan Logbook
                    </button>
                    <a href="<?php echo site_url('Pkl'); ?>" class="btn btn-secondary">
                        <i class="fas fa-times"></i> Batal
                    </a>
                </div>

                <?php echo form_close(); ?>
            </div>
        </div>
    </div>

    <!-- Statistik Logbook -->
    <div class="col-md-4">
        <div class="card card-info card-outline">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-chart-bar"></i> Statistik Logbook</h3>
            </div>
            <div class="card-body">
                <?php if ($statistik): ?>
                    <div class="info-box mb-3">
                        <span class="info-box-icon bg-info"><i class="fas fa-book"></i></span>
                        <div class="info-box-content">
                            <span class="info-box-text">Total Logbook</span>
                            <span class="info-box-number"><?php echo $statistik->total; ?></span>
                        </div>
                    </div>
                    <div class="info-box mb-3">
                        <span class="info-box-icon bg-success"><i class="fas fa-map-marker-alt"></i></span>
                        <div class="info-box-content">
                            <span class="info-box-text">Dengan Koordinat</span>
                            <span class="info-box-number"><?php echo $statistik->dengan_koordinat; ?></span>
                        </div>
                    </div>
                    <div class="info-box">
                        <span class="info-box-icon bg-warning"><i class="fas fa-exclamation-triangle"></i></span>
                        <div class="info-box-content">
                            <span class="info-box-text">Tanpa Koordinat</span>
                            <span class="info-box-number"><?php echo $statistik->tanpa_koordinat; ?></span>
                        </div>
                    </div>
                <?php else: ?>
                    <p class="text-muted">Belum ada data statistik</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Daftar Logbook -->
<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-list"></i> Daftar Logbook</h3>
                <div class="card-tools">
                    <?php if (!empty($logbook_list)): ?>
                        <a href="<?php echo site_url('Pkl/cetak_logbook'); ?>" class="btn btn-sm btn-primary" target="_blank">
                            <i class="fas fa-print"></i> Cetak PDF
                        </a>
                    <?php endif; ?>
                </div>
            </div>
            <div class="card-body table-responsive p-0">
                <?php if (empty($logbook_list)): ?>
                    <div class="alert alert-info m-3">
                        <i class="fas fa-info-circle"></i> Belum ada logbook yang diinput.
                    </div>
                <?php else: ?>
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Tanggal</th>
                                <th>Jam</th>
                                <th>Aktivitas</th>
                                <th>Koordinat</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($logbook_list as $logbook): ?>
                                <tr>
                                    <td><?php echo date('d/m/Y', strtotime($logbook->tanggal)); ?></td>
                                    <td>
                                        <?php echo date('H:i', strtotime($logbook->jam_mulai)); ?> - 
                                        <?php echo date('H:i', strtotime($logbook->jam_selesai)); ?>
                                    </td>
                                    <td>
                                        <div style="max-width: 300px; overflow: hidden; text-overflow: ellipsis;">
                                            <?php echo htmlspecialchars(substr($logbook->aktivitas, 0, 100)); ?>
                                            <?php if (strlen($logbook->aktivitas) > 100): ?>...<?php endif; ?>
                                        </div>
                                    </td>
                                    <td>
                                        <?php if ($logbook->latitude_logbook && $logbook->longitude_logbook): ?>
                                            <?php
                                            $lat = number_format($logbook->latitude_logbook, 8);
                                            $lng = number_format($logbook->longitude_logbook, 8);
                                            $jarak = isset($logbook->jarak_dari_tempat) && $logbook->jarak_dari_tempat !== null 
                                                ? number_format($logbook->jarak_dari_tempat, 2) 
                                                : null;
                                            ?>
                                            <span class="badge badge-success tooltip-koordinat" 
                                                  data-toggle="tooltip" 
                                                  data-placement="top" 
                                                  data-html="true"
                                                  data-lat="<?php echo htmlspecialchars($lat); ?>"
                                                  data-lng="<?php echo htmlspecialchars($lng); ?>"
                                                  data-jarak="<?php echo $jarak !== null ? htmlspecialchars($jarak) : ''; ?>"
                                                  style="cursor: help;">
                                                <i class="fas fa-map-marker-alt"></i> Ada
                                            </span>
                                        <?php else: ?>
                                            <span class="badge badge-secondary">-</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<script>
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
            const accuracy = position.coords.accuracy;
            
            document.getElementById('latitude').value = lat.toFixed(8);
            document.getElementById('longitude').value = lng.toFixed(8);
            
            btn.innerHTML = originalHtml;
            btn.disabled = false;
            
            alert('Lokasi GPS berhasil diambil!\nAkurasi: ±' + Math.round(accuracy) + ' meter');
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

// Validasi jam selesai harus setelah jam mulai
document.getElementById('formLogbook').addEventListener('submit', function(e) {
    const jamMulai = document.getElementById('jam_mulai').value;
    const jamSelesai = document.getElementById('jam_selesai').value;
    
    if (jamMulai && jamSelesai && jamSelesai <= jamMulai) {
        e.preventDefault();
        alert('Jam selesai harus setelah jam mulai!');
        return false;
    }
});

// Initialize tooltips dengan HTML content
$(document).ready(function() {
    $('.tooltip-koordinat').each(function() {
        var $this = $(this);
        var lat = $this.data('lat');
        var lng = $this.data('lng');
        var jarak = $this.data('jarak');
        
        var tooltipContent = '<div style="text-align: left; min-width: 200px;">';
        tooltipContent += '<strong>Koordinat:</strong><br>';
        tooltipContent += 'Lat: ' + lat + '<br>';
        tooltipContent += 'Lng: ' + lng;
        if (jarak && jarak !== '') {
            tooltipContent += '<br><br><strong>Jarak dari Tempat PKL:</strong><br>';
            tooltipContent += jarak + ' meter';
        }
        tooltipContent += '</div>';
        
        $this.attr('title', tooltipContent);
        $this.tooltip({
            html: true,
            placement: 'top',
            trigger: 'hover',
            sanitize: false
        });
    });
});
</script>

