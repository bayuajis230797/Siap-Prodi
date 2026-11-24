<div class="row">
    <div class="col-md-8">
        <div class="card card-primary card-outline">
            <div class="card-header">
                <h3 class="card-title">Detail Kegiatan</h3>
                <div class="card-tools">
                    <a href="<?php echo site_url('Absensi/kegiatan_saya'); ?>" class="btn btn-secondary btn-sm">
                        <i class="fas fa-arrow-left"></i> Kembali
                    </a>
                </div>
            </div>
            <div class="card-body">
                <h4><?php echo htmlspecialchars($kegiatan->nama_kegiatan); ?></h4>
                <table class="table table-sm">
                    <tr>
                        <th width="150">Tanggal</th>
                        <td><?php echo date('d F Y', strtotime($kegiatan->tanggal_kegiatan)); ?></td>
                    </tr>
                    <tr>
                        <th>Waktu (WIB)</th>
                        <td>
                            <?php echo date('H:i', strtotime($kegiatan->jam_mulai)); ?> WIB
                            <?php if ($kegiatan->jam_selesai): ?>
                                - <?php echo date('H:i', strtotime($kegiatan->jam_selesai)); ?> WIB
                            <?php endif; ?>
                        </td>
                    </tr>
                    <tr>
                        <th>Lokasi</th>
                        <td><?php echo $kegiatan->lokasi ? htmlspecialchars($kegiatan->lokasi) : '-'; ?></td>
                    </tr>
                    <tr>
                        <th>Jenis</th>
                        <td>
                            <span class="badge badge-info">
                                <?php echo $kegiatan->jenis_kegiatan == 'akademik' ? 'Akademik' : 'Non-Akademik'; ?>
                            </span>
                        </td>
                    </tr>
                    <tr>
                        <th>Status Kegiatan</th>
                        <td>
                            <?php 
                            $status_labels = array(
                                'draft' => array('label' => 'Draft', 'class' => 'secondary'),
                                'aktif' => array('label' => 'Aktif', 'class' => 'success'),
                                'selesai' => array('label' => 'Selesai', 'class' => 'info'),
                                'dibatalkan' => array('label' => 'Dibatalkan', 'class' => 'danger')
                            );
                            $status = $status_labels[$kegiatan->status] ?? array('label' => $kegiatan->status, 'class' => 'secondary');
                            ?>
                            <span class="badge badge-<?php echo $status['class']; ?>">
                                <?php echo $status['label']; ?>
                            </span>
                        </td>
                    </tr>
                    <?php if ($kegiatan->deskripsi): ?>
                    <tr>
                        <th>Deskripsi</th>
                        <td><?php echo nl2br(htmlspecialchars($kegiatan->deskripsi)); ?></td>
                    </tr>
                    <?php endif; ?>
                </table>
            </div>
        </div>
    </div>

    <div class="col-md-4">
        <!-- Status Absensi -->
        <div class="card card-info card-outline">
            <div class="card-header">
                <h3 class="card-title">Status Absensi Saya</h3>
            </div>
            <div class="card-body">
                <?php if ($absensi): ?>
                    <div class="alert alert-success">
                        <h5>
                            <i class="fas fa-check-circle"></i> 
                            <?php echo $absensi->status == 'hadir' ? 'Hadir' : 'Terlambat'; ?>
                        </h5>
                        <p class="mb-1"><strong>Waktu Absen:</strong><br>
                            <?php echo date('d F Y H:i:s', strtotime($absensi->waktu_absen)); ?>
                        </p>
                        <?php if ($absensi->jarak_meter): ?>
                            <p class="mb-1"><strong>Jarak:</strong> <?php echo number_format($absensi->jarak_meter, 2); ?> meter</p>
                        <?php endif; ?>
                    </div>
                <?php else: ?>
                    <div class="alert alert-warning">
                        <h5><i class="fas fa-exclamation-triangle"></i> Belum Absen</h5>
                        <?php if ($kegiatan->status == 'aktif'): ?>
                            <p>Silakan scan QR Code untuk melakukan absensi.</p>
                            <a href="<?php echo site_url('Absensi/scan'); ?>" class="btn btn-success btn-block">
                                <i class="fas fa-qrcode"></i> Scan QR Code
                            </a>
                            <?php if ($kegiatan->qr_code_token): ?>
                                <hr>
                                <p class="mb-2"><strong>Token QR Code (untuk input manual):</strong></p>
                                <div class="input-group">
                                    <input type="text" class="form-control form-control-sm" id="token-display" 
                                           value="<?php echo htmlspecialchars($kegiatan->qr_code_token); ?>" 
                                           readonly>
                                    <div class="input-group-append">
                                        <button class="btn btn-sm btn-outline-secondary" type="button" onclick="copyToken()">
                                            <i class="fas fa-copy"></i> Copy
                                        </button>
                                    </div>
                                </div>
                                <small class="text-muted">Gunakan token ini jika QR Code tidak bisa di-scan</small>
                            <?php endif; ?>
                        <?php else: ?>
                            <p>Kegiatan belum aktif atau sudah selesai.</p>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<script>
function copyToken() {
    const tokenInput = document.getElementById('token-display');
    tokenInput.select();
    tokenInput.setSelectionRange(0, 99999); // For mobile devices
    
    try {
        document.execCommand('copy');
        alert('Token berhasil di-copy! Silakan paste di form input token manual.');
    } catch (err) {
        // Fallback untuk browser modern
        if (navigator.clipboard && navigator.clipboard.writeText) {
            navigator.clipboard.writeText(tokenInput.value).then(function() {
                alert('Token berhasil di-copy! Silakan paste di form input token manual.');
            }, function() {
                alert('Gagal menyalin token. Silakan copy manual dengan Ctrl+C.');
            });
        } else {
            alert('Gagal menyalin token. Silakan copy manual dengan Ctrl+C.');
        }
    }
}
</script>

