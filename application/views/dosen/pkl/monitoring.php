<div class="row">
    <div class="col-md-12">
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
    <!-- Daftar Mahasiswa Bimbingan -->
    <div class="col-md-4">
        <div class="card card-primary card-outline">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-users"></i> Mahasiswa Bimbingan</h3>
            </div>
            <div class="card-body p-0">
                <?php if (empty($mahasiswa_list)): ?>
                    <div class="alert alert-info m-3">
                        <i class="fas fa-info-circle"></i> Belum ada mahasiswa bimbingan PKL.
                    </div>
                <?php else: ?>
                    <div class="p-2">
                        <small class="text-muted">
                            <i class="fas fa-info-circle"></i> 
                            Klik nama mahasiswa untuk melihat logbook dan absensi
                        </small>
                    </div>
                    <ul class="nav nav-pills flex-column">
                        <?php foreach ($mahasiswa_list as $mhs): ?>
                            <li class="nav-item">
                                <a href="<?php echo site_url('Pkl_mahasiswa_dosen/monitoring/' . ($mhs->id ? $mhs->id : $mhs->id_mahasiswa)); ?>" 
                                   class="nav-link <?php echo ($selected_pengajuan && (($selected_pengajuan->id && $selected_pengajuan->id == $mhs->id) || ($selected_pengajuan->id_mahasiswa == $mhs->id_mahasiswa))) ? 'active' : ''; ?>">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <i class="fas fa-user-graduate"></i> 
                                            <strong><?php echo htmlspecialchars($mhs->nama_mahasiswa); ?></strong><br>
                                            <small class="text-muted"><?php echo htmlspecialchars($mhs->nim); ?></small>
                                        </div>
                                        <?php if ($selected_pengajuan && (($selected_pengajuan->id && $selected_pengajuan->id == $mhs->id) || ($selected_pengajuan->id_mahasiswa == $mhs->id_mahasiswa))): ?>
                                            <i class="fas fa-chevron-right"></i>
                                        <?php endif; ?>
                                    </div>
                                </a>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Detail Logbook & Absensi -->
    <div class="col-md-8">
        <?php if ($selected_pengajuan): ?>
            <!-- Info Mahasiswa -->
            <div class="card card-info card-outline mb-3">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-user-graduate"></i> 
                        <?php echo htmlspecialchars($selected_pengajuan->nama_mahasiswa); ?>
                        <a href="<?php echo site_url('Pkl_mahasiswa_dosen/monitoring'); ?>" class="btn btn-sm btn-secondary float-right">
                            <i class="fas fa-arrow-left"></i> Kembali ke Daftar
                        </a>
                    </h3>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <p><strong>NIM:</strong> <?php echo htmlspecialchars($selected_pengajuan->nim); ?></p>
                            <p><strong>Judul PKL:</strong> <?php echo htmlspecialchars($selected_pengajuan->judul_pkl); ?></p>
                            <p><strong>Kelas:</strong> <?php echo htmlspecialchars($selected_pengajuan->kelas ?? '-'); ?></p>
                            <p><strong>Tempat PKL:</strong> <?php echo htmlspecialchars(isset($tempat_pkl) && $tempat_pkl ? $tempat_pkl->nama_instansi : ($selected_pengajuan->nama_instansi ?? '-')); ?></p>
                        </div>
                        <div class="col-md-6">
                            <p><strong>Dosen Pembimbing Akademik:</strong><br>
                                <?php if (isset($dosen_pembimbing) && $dosen_pembimbing): ?>
                                    <?php echo htmlspecialchars($dosen_pembimbing->nama_dosen); ?>
                                <?php else: ?>
                                    <span class="text-muted">-</span>
                                <?php endif; ?>
                            </p>
                            <p><strong>Dosen Pembimbing di Tempat PKL:</strong><br>
                                <?php if (isset($tempat_pkl) && $tempat_pkl && $tempat_pkl->nama_pembimbing_lapangan): ?>
                                    <?php echo htmlspecialchars($tempat_pkl->nama_pembimbing_lapangan); ?>
                                    <?php if ($tempat_pkl->no_hp_pembimbing_lapangan): ?>
                                        <br><small class="text-muted"><i class="fas fa-phone"></i> <?php echo htmlspecialchars($tempat_pkl->no_hp_pembimbing_lapangan); ?></small>
                                    <?php endif; ?>
                                <?php else: ?>
                                    <span class="text-muted">-</span>
                                <?php endif; ?>
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Statistik -->
            <div class="row mb-3">
                <div class="col-md-6">
                    <div class="card bg-info text-white">
                        <div class="card-body">
                            <h5><i class="fas fa-book"></i> Logbook</h5>
                            <?php if ($statistik_logbook): ?>
                                <p class="mb-0">Total: <strong><?php echo $statistik_logbook->total; ?></strong></p>
                                <small>Dengan koordinat: <?php echo $statistik_logbook->dengan_koordinat; ?></small>
                            <?php else: ?>
                                <p class="mb-0">Belum ada data</p>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="card bg-success text-white">
                        <div class="card-body">
                            <h5><i class="fas fa-calendar-check"></i> Absensi</h5>
                            <?php if ($statistik_absensi): ?>
                                <p class="mb-0">Total Hari: <strong><?php echo $statistik_absensi->total_hari; ?></strong></p>
                                <small>Berangkat: <?php echo $statistik_absensi->total_berangkat; ?> | Pulang: <?php echo $statistik_absensi->total_pulang; ?></small>
                            <?php else: ?>
                                <p class="mb-0">Belum ada data</p>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Logbook & Absensi -->
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-book"></i> Logbook & Absensi
                    </h3>
                </div>
                <div class="card-body">
                    <!-- Logbook -->
                    <div class="mb-4">
                        <h6><i class="fas fa-book"></i> Daftar Logbook</h6>
                        <p class="text-muted mb-3">Logbook yang diinputkan oleh <?php echo htmlspecialchars($selected_pengajuan->nama_mahasiswa); ?></p>
                        <?php if (empty($logbook_list)): ?>
                            <div class="alert alert-info">
                                <i class="fas fa-info-circle"></i> Belum ada logbook yang diinput oleh mahasiswa.
                            </div>
                        <?php else: ?>
                            <div class="table-responsive">
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
                                                    <div style="max-width: 300px;">
                                                        <?php echo htmlspecialchars($logbook->aktivitas); ?>
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
                            </div>
                        <?php endif; ?>
                    </div>

                    <hr class="my-4">

                    <!-- Absensi -->
                    <div>
                        <h6><i class="fas fa-calendar-check"></i> Daftar Absensi</h6>
                        <p class="text-muted mb-3">Absensi harian yang dilakukan oleh <?php echo htmlspecialchars($selected_pengajuan->nama_mahasiswa); ?></p>
                        <?php if (empty($absensi_list) || !is_array($absensi_list) || count($absensi_list) == 0): ?>
                            <div class="alert alert-info">
                                <i class="fas fa-info-circle"></i> Belum ada data absensi.
                            </div>
                        <?php else: ?>
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th>Tanggal</th>
                                            <th>Tipe</th>
                                            <th>Waktu</th>
                                            <th>Status</th>
                                            <th>Jarak</th>
                                            <th>Device</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php 
                                        // Load helper
                                        $this->load->helper('device_fingerprint');
                                        
                                        $tanggal_list = array();
                                        foreach ($absensi_list as $absen) {
                                            if (!isset($tanggal_list[$absen->tanggal])) {
                                                $tanggal_list[$absen->tanggal] = array('berangkat' => null, 'pulang' => null);
                                            }
                                            $tanggal_list[$absen->tanggal][$absen->tipe_absensi] = $absen;
                                        }
                                        
                                        foreach ($tanggal_list as $tanggal => $absensi):
                                            // Check device changed untuk berangkat dan pulang
                                            $berangkat_device_changed = $absensi['berangkat'] && isset($absensi['berangkat']->is_device_changed) && $absensi['berangkat']->is_device_changed == 1;
                                            $pulang_device_changed = $absensi['pulang'] && isset($absensi['pulang']->is_device_changed) && $absensi['pulang']->is_device_changed == 1;
                                            $any_device_changed = $berangkat_device_changed || $pulang_device_changed;
                                            
                                            // Parse device info
                                            $berangkat_device_info = $absensi['berangkat'] ? parse_device_info($absensi['berangkat']->device_info) : null;
                                            $pulang_device_info = $absensi['pulang'] ? parse_device_info($absensi['pulang']->device_info) : null;
                                        ?>
                                            <tr class="<?php echo $any_device_changed ? 'table-warning' : ''; ?>">
                                                <td><?php echo date('d/m/Y', strtotime($tanggal)); ?></td>
                                                <td>
                                                    <?php if ($absensi['berangkat']): ?>
                                                        <span class="badge badge-primary">Berangkat</span>
                                                    <?php endif; ?>
                                                    <?php if ($absensi['pulang']): ?>
                                                        <span class="badge badge-success">Pulang</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <?php if ($absensi['berangkat']): ?>
                                                        <?php echo date('H:i', strtotime($absensi['berangkat']->waktu_absen)); ?>
                                                    <?php endif; ?>
                                                    <?php if ($absensi['pulang']): ?>
                                                        <?php if ($absensi['berangkat']): ?> / <?php endif; ?>
                                                        <?php echo date('H:i', strtotime($absensi['pulang']->waktu_absen)); ?>
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
                                                        <?php if ($absensi['berangkat'] && $absensi['berangkat']->status_radius === 'di_luar_radius'): ?>
                                                            <span class="badge badge-danger">Berangkat: Luar</span>
                                                        <?php endif; ?>
                                                        <?php if ($absensi['pulang'] && $absensi['pulang']->status_radius === 'di_luar_radius'): ?>
                                                            <span class="badge badge-danger">Pulang: Luar</span>
                                                        <?php endif; ?>
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
                                                <td>
                                                    <?php if ($any_device_changed): ?>
                                                        <button type="button" class="btn btn-sm btn-warning" data-toggle="modal" data-target="#deviceModal<?php echo str_replace('-', '', $tanggal); ?>">
                                                            <i class="fas fa-exclamation-triangle"></i> Device Berbeda
                                                        </button>
                                                        
                                                        <!-- Modal Device Info -->
                                                        <div class="modal fade" id="deviceModal<?php echo str_replace('-', '', $tanggal); ?>" tabindex="-1" role="dialog">
                                                            <div class="modal-dialog modal-lg" role="document">
                                                                <div class="modal-content">
                                                                    <div class="modal-header bg-warning">
                                                                        <h5 class="modal-title">
                                                                            <i class="fas fa-mobile-alt"></i> Informasi Device - <?php echo date('d/m/Y', strtotime($tanggal)); ?>
                                                                        </h5>
                                                                        <button type="button" class="close" data-dismiss="modal">
                                                                            <span>&times;</span>
                                                                        </button>
                                                                    </div>
                                                                    <div class="modal-body">
                                                                        <?php if ($berangkat_device_changed && $berangkat_device_info): ?>
                                                                            <h6 class="text-primary"><i class="fas fa-sign-in-alt"></i> Absensi Berangkat</h6>
                                                                            <div class="table-responsive mb-3">
                                                                                <table class="table table-sm table-bordered">
                                                                                    <tr>
                                                                                        <th width="30%">Browser</th>
                                                                                        <td><?php echo htmlspecialchars($berangkat_device_info['browser']); ?></td>
                                                                                    </tr>
                                                                                    <tr>
                                                                                        <th>OS</th>
                                                                                        <td><?php echo htmlspecialchars($berangkat_device_info['os']); ?></td>
                                                                                    </tr>
                                                                                    <tr>
                                                                                        <th>Screen Resolution</th>
                                                                                        <td><?php echo htmlspecialchars($berangkat_device_info['screen']); ?></td>
                                                                                    </tr>
                                                                                    <tr>
                                                                                        <th>Timezone</th>
                                                                                        <td><?php echo htmlspecialchars($berangkat_device_info['timezone']); ?></td>
                                                                                    </tr>
                                                                                    <tr>
                                                                                        <th>Platform</th>
                                                                                        <td><?php echo htmlspecialchars($berangkat_device_info['platform']); ?></td>
                                                                                    </tr>
                                                                                    <tr>
                                                                                        <th>Language</th>
                                                                                        <td><?php echo htmlspecialchars($berangkat_device_info['language']); ?></td>
                                                                                    </tr>
                                                                                </table>
                                                                            </div>
                                                                        <?php endif; ?>
                                                                        
                                                                        <?php if ($pulang_device_changed && $pulang_device_info): ?>
                                                                            <h6 class="text-success"><i class="fas fa-sign-out-alt"></i> Absensi Pulang</h6>
                                                                            <div class="table-responsive">
                                                                                <table class="table table-sm table-bordered">
                                                                                    <tr>
                                                                                        <th width="30%">Browser</th>
                                                                                        <td><?php echo htmlspecialchars($pulang_device_info['browser']); ?></td>
                                                                                    </tr>
                                                                                    <tr>
                                                                                        <th>OS</th>
                                                                                        <td><?php echo htmlspecialchars($pulang_device_info['os']); ?></td>
                                                                                    </tr>
                                                                                    <tr>
                                                                                        <th>Screen Resolution</th>
                                                                                        <td><?php echo htmlspecialchars($pulang_device_info['screen']); ?></td>
                                                                                    </tr>
                                                                                    <tr>
                                                                                        <th>Timezone</th>
                                                                                        <td><?php echo htmlspecialchars($pulang_device_info['timezone']); ?></td>
                                                                                    </tr>
                                                                                    <tr>
                                                                                        <th>Platform</th>
                                                                                        <td><?php echo htmlspecialchars($pulang_device_info['platform']); ?></td>
                                                                                    </tr>
                                                                                    <tr>
                                                                                        <th>Language</th>
                                                                                        <td><?php echo htmlspecialchars($pulang_device_info['language']); ?></td>
                                                                                    </tr>
                                                                                </table>
                                                                            </div>
                                                                        <?php endif; ?>
                                                                        
                                                                        <div class="alert alert-warning mt-3">
                                                                            <i class="fas fa-exclamation-triangle"></i>
                                                                            <strong>Peringatan:</strong> Device yang digunakan berbeda dari device yang digunakan saat absensi pertama. 
                                                                            Kemungkinan mahasiswa menggunakan HP orang lain untuk absensi.
                                                                        </div>
                                                                    </div>
                                                                    <div class="modal-footer">
                                                                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Tutup</button>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    <?php else: ?>
                                                        <span class="badge badge-success">
                                                            <i class="fas fa-check"></i> Device Sama
                                                        </span>
                                                    <?php endif; ?>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        <?php else: ?>
            <div class="card">
                <div class="card-body text-center">
                    <i class="fas fa-info-circle fa-3x text-muted mb-3"></i>
                    <h5 class="text-muted">Pilih Mahasiswa Bimbingan</h5>
                    <p class="text-muted">Klik nama mahasiswa di daftar untuk melihat logbook dan absensi mereka.</p>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

<script>
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

