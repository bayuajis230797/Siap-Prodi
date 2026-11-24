<div class="row">
    <!-- Info Kegiatan -->
    <div class="col-md-8">
        <div class="card card-primary card-outline">
            <div class="card-header">
                <h3 class="card-title">Detail Kegiatan</h3>
                <div class="card-tools">
                    <a href="<?php echo site_url('Kegiatan/edit/' . $kegiatan->id); ?>" class="btn btn-warning btn-sm">
                        <i class="fas fa-edit"></i> Edit
                    </a>
                    <a href="<?php echo site_url('Kegiatan'); ?>" class="btn btn-secondary btn-sm">
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
                    <?php if ($kegiatan->latitude && $kegiatan->longitude): ?>
                    <tr>
                        <th>Koordinat GPS</th>
                        <td>
                            Lat: <?php echo $kegiatan->latitude; ?>, 
                            Lon: <?php echo $kegiatan->longitude; ?><br>
                            <small class="text-muted">Radius: <?php echo $kegiatan->radius_meter; ?> meter</small>
                        </td>
                    </tr>
                    <?php endif; ?>
                    <tr>
                        <th>Jenis</th>
                        <td>
                            <span class="badge badge-info">
                                <?php echo $kegiatan->jenis_kegiatan == 'akademik' ? 'Akademik' : 'Non-Akademik'; ?>
                            </span>
                        </td>
                    </tr>
                    <tr>
                        <th>Status</th>
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
                            <div class="d-flex align-items-center flex-wrap">
                                <span class="badge badge-<?php echo $status['class']; ?> badge-lg mr-3 mb-2">
                                    Status Saat Ini: <?php echo $status['label']; ?>
                                </span>
                                <div class="d-flex align-items-center">
                                    <label for="select_status" class="mr-2 mb-0">Ubah Status:</label>
                                    <select class="form-control form-control-sm mr-2" id="select_status" style="width: 150px;">
                                        <option value="draft" <?php echo $kegiatan->status == 'draft' ? 'selected' : ''; ?>>Draft</option>
                                        <option value="aktif" <?php echo $kegiatan->status == 'aktif' ? 'selected' : ''; ?>>Aktif</option>
                                        <option value="selesai" <?php echo $kegiatan->status == 'selesai' ? 'selected' : ''; ?>>Selesai</option>
                                        <option value="dibatalkan" <?php echo $kegiatan->status == 'dibatalkan' ? 'selected' : ''; ?>>Dibatalkan</option>
                                    </select>
                                    <button type="button" class="btn btn-primary btn-sm" id="btn-simpan-status" data-id="<?php echo $kegiatan->id; ?>">
                                        <i class="fas fa-save"></i> Simpan
                                    </button>
                                </div>
                            </div>
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

    <!-- QR Code -->
    <div class="col-md-4">
        <div class="card card-success card-outline">
            <div class="card-header">
                <h3 class="card-title">QR Code Absensi</h3>
                <div class="card-tools">
                    <button type="button" class="btn btn-sm btn-warning" onclick="resetQRCode()" title="Reset QR Code">
                        <i class="fas fa-sync"></i> Reset
                    </button>
                </div>
            </div>
            <div class="card-body text-center">
                <?php if ($kegiatan->qr_code): ?>
                    <?php $this->load->helper('qrcode'); ?>
                    <img src="<?php echo qr_code_serve_url_kegiatan($kegiatan->id); ?>" 
                         alt="QR Code" 
                         class="img-fluid" 
                         style="max-width: 100%;">
                    <p class="mt-2">
                        <small class="text-muted">Scan QR Code ini untuk absensi (tampil saat kegiatan aktif)</small>
                    </p>
                    <a href="<?php echo qr_code_serve_url_kegiatan($kegiatan->id); ?>" 
                       download="qr_kegiatan_<?php echo (int) $kegiatan->id; ?>.png"
                       class="btn btn-sm btn-primary mt-2">
                        <i class="fas fa-download"></i> Download QR Code
                    </a>
                <?php else: ?>
                    <p class="text-muted">QR Code belum dibuat.</p>
                    <button type="button" class="btn btn-primary" onclick="resetQRCode()">
                        <i class="fas fa-qrcode"></i> Generate QR Code
                    </button>
                <?php endif; ?>
            </div>
        </div>

        <!-- Statistik -->
        <?php if (isset($kegiatan->statistik)): ?>
        <div class="card card-info card-outline mt-3">
            <div class="card-header">
                <h3 class="card-title">Statistik Absensi</h3>
            </div>
            <div class="card-body">
                <div class="info-box mb-3">
                    <span class="info-box-icon bg-success"><i class="fas fa-check"></i></span>
                    <div class="info-box-content">
                        <span class="info-box-text">Hadir</span>
                        <span class="info-box-number"><?php echo $kegiatan->statistik->hadir; ?></span>
                    </div>
                </div>
                <div class="info-box mb-3">
                    <span class="info-box-icon bg-warning"><i class="fas fa-clock"></i></span>
                    <div class="info-box-content">
                        <span class="info-box-text">Terlambat</span>
                        <span class="info-box-number"><?php echo $kegiatan->statistik->terlambat; ?></span>
                    </div>
                </div>
                <div class="info-box mb-3">
                    <span class="info-box-icon bg-danger"><i class="fas fa-times"></i></span>
                    <div class="info-box-content">
                        <span class="info-box-text">Tidak Hadir</span>
                        <span class="info-box-number"><?php echo $kegiatan->statistik->tidak_hadir; ?></span>
                    </div>
                </div>
                <div class="info-box">
                    <span class="info-box-icon bg-info"><i class="fas fa-users"></i></span>
                    <div class="info-box-content">
                        <span class="info-box-text">Total Peserta</span>
                        <span class="info-box-number"><?php echo $kegiatan->statistik->total_peserta; ?></span>
                    </div>
                </div>
            </div>
        </div>
        <?php endif; ?>
    </div>
</div>

<!-- Kelas & Peserta -->
<div class="row mt-3">
    <div class="col-md-12">
        <div class="card card-info card-outline">
            <div class="card-header">
                <h3 class="card-title">Kelas & Peserta</h3>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <h5>Kelas yang Terdaftar</h5>
                        <?php if (!empty($kegiatan->kelas)): ?>
                            <ul>
                                <?php foreach ($kegiatan->kelas as $kelas): ?>
                                    <li><?php echo htmlspecialchars($kelas->nama_kelas); ?></li>
                                <?php endforeach; ?>
                            </ul>
                        <?php else: ?>
                            <p class="text-muted">Tidak ada kelas yang terdaftar.</p>
                        <?php endif; ?>
                    </div>
                    <div class="col-md-6">
                        <h5>Total Peserta</h5>
                        <p class="h4"><?php echo count($kegiatan->peserta); ?> Mahasiswa</p>
                        <a href="<?php echo site_url('Kegiatan/laporan/' . $kegiatan->id); ?>" class="btn btn-success btn-sm">
                            <i class="fas fa-file-alt"></i> Lihat Laporan
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
(function() {
    'use strict';
    
    // Tunggu sampai jQuery ready
    function initKegiatanDetail() {
        // Cek jQuery
        if (typeof jQuery === 'undefined') {
            console.log('Menunggu jQuery...');
            setTimeout(initKegiatanDetail, 100);
            return;
        }
        
        var $ = jQuery;
        
        // Tunggu sampai document ready
        $(document).ready(function() {
            console.log('Kegiatan Detail Script Loaded');
            
            // Function reset QR Code
            window.resetQRCode = function() {
                if (!confirm('Apakah Anda yakin ingin reset QR Code? QR Code lama tidak akan bisa digunakan lagi.')) {
                    return;
                }
                
                $.ajax({
                    url: '<?php echo site_url("Kegiatan/reset_qrcode/' . $kegiatan->id . '"); ?>',
                    method: 'POST',
                    dataType: 'json',
                    success: function(response) {
                        if (response.success) {
                            alert(response.message);
                            location.reload();
                        } else {
                            alert(response.message || 'Gagal reset QR Code.');
                        }
                    },
                    error: function() {
                        alert('Terjadi kesalahan saat reset QR Code.');
                    }
                });
            };
            
            // Event handler untuk tombol simpan status
            $(document).on('click', '#btn-simpan-status', function(e) {
                e.preventDefault();
                e.stopPropagation();
                
                console.log('Button simpan status clicked');
                
                var btn = $(this);
                var select = $('#select_status');
                var id_kegiatan = btn.data('id');
                var status_baru = select.val();
                var status_lama = '<?php echo $kegiatan->status; ?>';
                
                console.log('ID Kegiatan:', id_kegiatan);
                console.log('Status Lama:', status_lama);
                console.log('Status Baru:', status_baru);
                
                // Cek apakah status berubah
                if (status_baru == status_lama) {
                    alert('Status belum berubah. Pilih status yang berbeda.');
                    return;
                }
                
                var status_labels = {
                    'draft': 'Draft',
                    'aktif': 'Aktif',
                    'selesai': 'Selesai',
                    'dibatalkan': 'Dibatalkan'
                };
                
                if (!confirm('Apakah Anda yakin ingin mengubah status dari "' + status_labels[status_lama] + '" menjadi "' + status_labels[status_baru] + '"?')) {
                    // Reset select ke status lama jika cancel
                    select.val(status_lama);
                    return;
                }
                
                // Disable button dan select, show loading
                var originalHtml = btn.html();
                btn.prop('disabled', true);
                select.prop('disabled', true);
                btn.html('<i class="fas fa-spinner fa-spin"></i> Menyimpan...');
                
                $.ajax({
                    url: '<?php echo site_url("Kegiatan/ubah_status"); ?>',
                    method: 'POST',
                    data: {
                        id_kegiatan: id_kegiatan,
                        status: status_baru
                    },
                    dataType: 'json',
                    success: function(response) {
                        console.log('AJAX Success:', response);
                        if (response.success) {
                            alert(response.message);
                            location.reload();
                        } else {
                            alert(response.message || 'Gagal mengubah status.');
                            btn.prop('disabled', false);
                            select.prop('disabled', false);
                            btn.html(originalHtml);
                            select.val(status_lama);
                        }
                    },
                    error: function(xhr, status, error) {
                        console.error('AJAX Error:', status, error);
                        console.error('Response:', xhr.responseText);
                        console.error('Status Code:', xhr.status);
                        
                        var errorMsg = 'Terjadi kesalahan saat mengubah status.';
                        if (xhr.status === 404) {
                            errorMsg = 'URL tidak ditemukan. Pastikan controller method ada.';
                        } else if (xhr.status === 500) {
                            errorMsg = 'Terjadi kesalahan di server.';
                        } else if (xhr.responseText) {
                            try {
                                var errorResponse = JSON.parse(xhr.responseText);
                                if (errorResponse.message) {
                                    errorMsg = errorResponse.message;
                                }
                            } catch(e) {
                                errorMsg = 'Error: ' + xhr.responseText.substring(0, 100);
                            }
                        }
                        
                        alert(errorMsg);
                        btn.prop('disabled', false);
                        select.prop('disabled', false);
                        btn.html(originalHtml);
                        select.val(status_lama);
                    }
                });
            });
            
            // Debug: cek apakah element ada
            console.log('Button found:', $('#btn-simpan-status').length);
            console.log('Select found:', $('#select_status').length);
        });
    }
    
    // Mulai inisialisasi
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initKegiatanDetail);
    } else {
        initKegiatanDetail();
    }
})();
</script>

