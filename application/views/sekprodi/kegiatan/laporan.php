<div class="row">
    <div class="col-12">
        <div class="card card-primary card-outline">
            <div class="card-header">
                <h3 class="card-title">Laporan Absensi Kegiatan</h3>
                <div class="card-tools">
                    <a href="<?php echo site_url('Kegiatan/export_excel/' . $kegiatan->id); ?>" class="btn btn-success btn-sm">
                        <i class="fas fa-file-excel"></i> Export Excel
                    </a>
                    <a href="<?php echo site_url('Kegiatan/cetak_kehadiran/' . $kegiatan->id); ?>" class="btn btn-primary btn-sm" target="_blank">
                        <i class="fas fa-print"></i> Cetak Kehadiran
                    </a>
                    <a href="<?php echo site_url('Kegiatan/detail/' . $kegiatan->id); ?>" class="btn btn-secondary btn-sm">
                        <i class="fas fa-arrow-left"></i> Kembali
                    </a>
                </div>
            </div>
            <div class="card-body">
                <h4><?php echo htmlspecialchars($kegiatan->nama_kegiatan); ?></h4>
                <p class="text-muted">
                    <i class="fas fa-calendar"></i> <?php echo date('d F Y', strtotime($kegiatan->tanggal_kegiatan)); ?> | 
                    <i class="fas fa-clock"></i> <?php echo date('H:i', strtotime($kegiatan->jam_mulai)); ?> WIB
                    <?php if ($kegiatan->jam_selesai): ?>
                        - <?php echo date('H:i', strtotime($kegiatan->jam_selesai)); ?> WIB
                    <?php endif; ?>
                </p>

                <!-- Statistik -->
                <?php if (isset($kegiatan->statistik)): ?>
                <div class="row mb-3">
                    <div class="col-md-3">
                        <div class="info-box">
                            <span class="info-box-icon bg-success"><i class="fas fa-check"></i></span>
                            <div class="info-box-content">
                                <span class="info-box-text">Hadir</span>
                                <span class="info-box-number"><?php echo $kegiatan->statistik->hadir; ?></span>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="info-box">
                            <span class="info-box-icon bg-warning"><i class="fas fa-clock"></i></span>
                            <div class="info-box-content">
                                <span class="info-box-text">Terlambat</span>
                                <span class="info-box-number"><?php echo $kegiatan->statistik->terlambat; ?></span>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="info-box">
                            <span class="info-box-icon bg-danger"><i class="fas fa-times"></i></span>
                            <div class="info-box-content">
                                <span class="info-box-text">Tidak Hadir</span>
                                <span class="info-box-number"><?php echo $kegiatan->statistik->tidak_hadir; ?></span>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
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

                <!-- Table Absensi -->
                <div class="table-responsive">
                    <table class="table table-bordered table-striped">
                        <thead>
                            <tr>
                                <th width="5%">No</th>
                                <th width="15%">NIM</th>
                                <th width="25%">Nama Mahasiswa</th>
                                <th width="10%">Kelas</th>
                                <th width="15%">Waktu Absen</th>
                                <th width="10%">Jarak</th>
                                <th width="10%">Status</th>
                                <th width="10%">Keterangan</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($kegiatan->peserta)): ?>
                                <tr>
                                    <td colspan="8" class="text-center">Tidak ada peserta.</td>
                                </tr>
                            <?php else: ?>
                                <?php $no = 1; ?>
                                <?php foreach ($kegiatan->peserta as $peserta): ?>
                                    <tr>
                                        <td><?php echo $no++; ?></td>
                                        <td><?php echo htmlspecialchars($peserta->nim); ?></td>
                                        <td><?php echo htmlspecialchars($peserta->nama_mahasiswa); ?></td>
                                        <td><?php echo htmlspecialchars($peserta->kelas); ?></td>
                                        <td>
                                            <?php if (isset($peserta->absensi) && $peserta->absensi): ?>
                                                <?php echo date('d/m/Y H:i', strtotime($peserta->absensi->waktu_absen)); ?>
                                            <?php else: ?>
                                                <span class="text-muted">-</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php if (isset($peserta->absensi) && $peserta->absensi && $peserta->absensi->jarak_meter): ?>
                                                <?php echo number_format($peserta->absensi->jarak_meter, 2); ?> m
                                            <?php else: ?>
                                                <span class="text-muted">-</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php 
                                            $status_labels = array(
                                                'hadir' => array('label' => 'Hadir', 'class' => 'success'),
                                                'terlambat' => array('label' => 'Terlambat', 'class' => 'warning'),
                                                'tidak_hadir' => array('label' => 'Tidak Hadir', 'class' => 'danger')
                                            );
                                            $status = $status_labels[$peserta->status_absen] ?? array('label' => 'Tidak Hadir', 'class' => 'danger');
                                            ?>
                                            <span class="badge badge-<?php echo $status['class']; ?>">
                                                <?php echo $status['label']; ?>
                                            </span>
                                        </td>
                                        <td>
                                            <?php if (isset($peserta->absensi) && $peserta->absensi && $peserta->absensi->keterangan): ?>
                                                <?php echo htmlspecialchars($peserta->absensi->keterangan); ?>
                                            <?php else: ?>
                                                <span class="text-muted">-</span>
                                            <?php endif; ?>
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
</div>

