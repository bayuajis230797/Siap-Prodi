<div class="row">
    <div class="col-md-8">
        <div class="card card-primary card-outline">
            <div class="card-header">
                <h3 class="card-title">Detail Jadwal <?php echo $jenis_ujian; ?></h3>
                <div class="card-tools">
                    <a href="<?php echo site_url('Absensi_ujian/jadwal_saya'); ?>" class="btn btn-secondary btn-sm">
                        <i class="fas fa-arrow-left"></i> Kembali
                    </a>
                </div>
            </div>
            <div class="card-body">
                <h4><?php echo htmlspecialchars($jadwal->nama_mk); ?></h4>
                <table class="table table-sm">
                    <tr>
                        <th width="150">Tanggal</th>
                        <td><?php echo date('d F Y', strtotime($jadwal->tanggal)); ?></td>
                    </tr>
                    <tr>
                        <th>Waktu (WIB)</th>
                        <td>
                            <?php echo date('H:i', strtotime($jadwal->jam_mulai)); ?> WIB
                            <?php if ($jadwal->jam_selesai): ?>
                                - <?php echo date('H:i', strtotime($jadwal->jam_selesai)); ?> WIB
                            <?php endif; ?>
                        </td>
                    </tr>
                    <tr>
                        <th>Ruangan</th>
                        <td><?php echo $jadwal->nama_ruang ? htmlspecialchars($jadwal->nama_ruang) : '-'; ?></td>
                    </tr>
                    <tr>
                        <th>Sesi</th>
                        <td>
                            <span class="badge badge-info">
                                Sesi <?php echo $jadwal->sesi ?? '-'; ?>
                            </span>
                        </td>
                    </tr>
                    <tr>
                        <th>Pengawas</th>
                        <td>
                            <?php if (!empty($pengawas)): ?>
                                <?php foreach ($pengawas as $p): ?>
                                    <div>
                                        <?php echo htmlspecialchars(isset($p->nama_dosen) ? $p->nama_dosen : (isset($p->nama_tendik) ? $p->nama_tendik : '-')); ?>
                                        <?php if (isset($p->nidn) || isset($p->nipy)): ?>
                                            <br><small class="text-muted"><?php echo isset($p->nidn) ? $p->nidn : (isset($p->nipy) ? $p->nipy : ''); ?></small>
                                        <?php endif; ?>
                                    </div>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <span class="text-muted">-</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <tr>
                        <th>Status Absensi</th>
                        <td>
                            <?php if ($kehadiran): ?>
                                <?php 
                                $status_labels = array(
                                    'hadir' => array('label' => 'Hadir', 'class' => 'success'),
                                    'tidak_hadir' => array('label' => 'Tidak Hadir', 'class' => 'danger')
                                );
                                $status = $status_labels[$kehadiran->status_kehadiran] ?? array('label' => $kehadiran->status_kehadiran, 'class' => 'secondary');
                                ?>
                                <span class="badge badge-<?php echo $status['class']; ?> badge-lg">
                                    <?php echo $status['label']; ?>
                                </span>
                                <?php if ($kehadiran->created_at): ?>
                                    <br><small class="text-muted">
                                        Waktu absen: <?php echo date('d/m/Y H:i', strtotime($kehadiran->created_at)); ?>
                                    </small>
                                <?php endif; ?>
                            <?php else: ?>
                                <span class="badge badge-secondary">Belum Absen</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                </table>
            </div>
        </div>
    </div>
    
    <div class="col-md-4">
        <div class="card card-success card-outline">
            <div class="card-header">
                <h3 class="card-title">QR Code Absensi</h3>
            </div>
            <div class="card-body text-center">
                <p class="text-muted mb-2">Scan QR Code yang ditampilkan pengawas di ruang ujian menggunakan tombol di bawah.</p>
                <?php if (empty($can_absen)): ?>
                    <div class="alert alert-warning small mb-0">
                        Barcode absensi untuk jadwal ini belum tersedia. Hubungi sekprodi/pengawas.
                    </div>
                <?php elseif (!$kehadiran || $kehadiran->status_kehadiran !== 'hadir'): ?>
                    <a href="<?php echo site_url('Absensi_ujian/scan/' . urlencode($jadwal->qr_code_token)); ?>" class="btn btn-success btn-block">
                        <i class="fas fa-qrcode"></i> Buka Scanner Absensi
                    </a>
                <?php else: ?>
                    <span class="badge badge-success">Sudah absen</span>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

