<div class="row">
    <!-- Info Rapat -->
    <div class="col-md-12">
        <div class="card card-primary card-outline">
            <div class="card-header">
                <h3 class="card-title">Detail Rapat</h3>
                <div class="card-tools">
                    <a href="<?php echo site_url('Rapat/dosen'); ?>" class="btn btn-secondary btn-sm">
                        <i class="fas fa-arrow-left"></i> Kembali
                    </a>
                </div>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-8">
                        <h4><?php echo htmlspecialchars($rapat->judul_rapat); ?></h4>
                        <table class="table table-sm">
                            <tr>
                                <th width="150">Tanggal</th>
                                <td><?php echo date('d F Y', strtotime($rapat->tanggal_rapat)); ?></td>
                            </tr>
                            <tr>
                                <th>Waktu</th>
                                <td>
                                    <?php echo date('H:i', strtotime($rapat->jam_mulai)); ?>
                                    <?php if ($rapat->jam_selesai): ?>
                                        - <?php echo date('H:i', strtotime($rapat->jam_selesai)); ?>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <tr>
                                <th>Tempat</th>
                                <td><?php echo $rapat->tempat ? htmlspecialchars($rapat->tempat) : '-'; ?></td>
                            </tr>
                            <tr>
                                <th>Status Kehadiran Saya</th>
                                <td>
                                    <?php if (isset($rapat->status_kehadiran_saya)): ?>
                                        <?php 
                                        $status_labels = array(
                                            'hadir' => array('label' => 'Hadir', 'class' => 'success'),
                                            'tidak_hadir' => array('label' => 'Tidak Hadir', 'class' => 'danger'),
                                            'izin' => array('label' => 'Izin', 'class' => 'warning')
                                        );
                                        $status = $status_labels[$rapat->status_kehadiran_saya] ?? array('label' => $rapat->status_kehadiran_saya, 'class' => 'secondary');
                                        ?>
                                        <span class="badge badge-<?php echo $status['class']; ?> badge-lg">
                                            <?php echo $status['label']; ?>
                                        </span>
                                        <?php if (isset($rapat->keterangan_saya) && !empty($rapat->keterangan_saya)): ?>
                                            <br><small class="text-muted"><?php echo htmlspecialchars($rapat->keterangan_saya); ?></small>
                                        <?php endif; ?>
                                    <?php else: ?>
                                        <span class="badge badge-secondary">Belum Dikonfirmasi</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php if ($rapat->notulen): ?>
                            <tr>
                                <th>Notulen</th>
                                <td>
                                    <div class="border p-3 bg-light" style="max-height: 300px; overflow-y: auto;">
                                        <?php echo nl2br(htmlspecialchars($rapat->notulen)); ?>
                                    </div>
                                </td>
                            </tr>
                            <?php endif; ?>
                        </table>
                    </div>
                    <div class="col-md-4">
                        <h5>Statistik Kehadiran</h5>
                        <?php if (isset($rapat->statistik)): ?>
                            <div class="info-box mb-3">
                                <span class="info-box-icon bg-success"><i class="fas fa-check"></i></span>
                                <div class="info-box-content">
                                    <span class="info-box-text">Hadir</span>
                                    <span class="info-box-number"><?php echo $rapat->statistik['hadir']; ?></span>
                                </div>
                            </div>
                            <div class="info-box mb-3">
                                <span class="info-box-icon bg-danger"><i class="fas fa-times"></i></span>
                                <div class="info-box-content">
                                    <span class="info-box-text">Tidak Hadir</span>
                                    <span class="info-box-number"><?php echo $rapat->statistik['tidak_hadir']; ?></span>
                                </div>
                            </div>
                            <?php if ($rapat->statistik['izin'] > 0): ?>
                            <div class="info-box mb-3">
                                <span class="info-box-icon bg-warning"><i class="fas fa-exclamation"></i></span>
                                <div class="info-box-content">
                                    <span class="info-box-text">Izin</span>
                                    <span class="info-box-number"><?php echo $rapat->statistik['izin']; ?></span>
                                </div>
                            </div>
                            <?php endif; ?>
                            <div class="info-box">
                                <span class="info-box-icon bg-info"><i class="fas fa-users"></i></span>
                                <div class="info-box-content">
                                    <span class="info-box-text">Total Peserta</span>
                                    <span class="info-box-number"><?php echo $rapat->statistik['total']; ?></span>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Daftar Peserta -->
    <div class="col-md-12">
        <div class="card card-info card-outline">
            <div class="card-header">
                <h3 class="card-title">Daftar Peserta Rapat</h3>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered table-striped">
                        <thead>
                            <tr>
                                <th width="5%">No</th>
                                <th width="30%">Nama Peserta</th>
                                <th width="15%">Role</th>
                                <th width="20%">Status Kehadiran</th>
                                <th width="30%">Keterangan</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($rapat->peserta)): ?>
                                <tr>
                                    <td colspan="5" class="text-center">Belum ada peserta.</td>
                                </tr>
                            <?php else: ?>
                                <?php $no = 1; ?>
                                <?php foreach ($rapat->peserta as $peserta): ?>
                                    <tr>
                                        <td><?php echo $no++; ?></td>
                                        <td><?php echo htmlspecialchars($peserta->nama_peserta); ?></td>
                                        <td>
                                            <span class="badge badge-info">
                                                <?php 
                                                $role_labels = array(
                                                    'dosen' => 'Dosen',
                                                    'tendik' => 'Tendik',
                                                    'eksternal' => 'Eksternal',
                                                    'lainnya' => 'Lainnya'
                                                );
                                                echo isset($role_labels[$peserta->role_peserta]) ? $role_labels[$peserta->role_peserta] : $peserta->role_peserta;
                                                ?>
                                            </span>
                                        </td>
                                        <td>
                                            <?php 
                                            $status_labels = array(
                                                'hadir' => array('label' => 'Hadir', 'class' => 'success'),
                                                'tidak_hadir' => array('label' => 'Tidak Hadir', 'class' => 'danger'),
                                                'izin' => array('label' => 'Izin', 'class' => 'warning')
                                            );
                                            $status = $status_labels[$peserta->status_kehadiran] ?? array('label' => $peserta->status_kehadiran, 'class' => 'secondary');
                                            ?>
                                            <span class="badge badge-<?php echo $status['class']; ?>">
                                                <?php echo $status['label']; ?>
                                            </span>
                                        </td>
                                        <td>
                                            <?php echo $peserta->keterangan ? htmlspecialchars($peserta->keterangan) : '-'; ?>
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

    <!-- Dokumentasi -->
    <?php if (!empty($rapat->dokumentasi)): ?>
    <div class="col-md-12">
        <div class="card card-success card-outline">
            <div class="card-header">
                <h3 class="card-title">Dokumentasi Rapat</h3>
            </div>
            <div class="card-body">
                <div class="row" id="galeri_dokumentasi">
                    <?php foreach ($rapat->dokumentasi as $dok): ?>
                        <div class="col-md-3 mb-3">
                            <div class="card">
                                <?php if (strpos($dok->file_type, 'image') !== false): ?>
                                    <img src="<?php echo base_url($dok->file_path); ?>" class="card-img-top" 
                                         style="height: 200px; object-fit: cover; cursor: pointer;" 
                                         alt="<?php echo htmlspecialchars($dok->file_name); ?>"
                                         onclick="window.open('<?php echo base_url($dok->file_path); ?>', '_blank')">
                                <?php else: ?>
                                    <div class="card-img-top d-flex align-items-center justify-content-center" 
                                         style="height: 200px; background: #f0f0f0; cursor: pointer;"
                                         onclick="window.open('<?php echo base_url($dok->file_path); ?>', '_blank')">
                                        <i class="fas fa-file-pdf fa-3x text-danger"></i>
                                    </div>
                                <?php endif; ?>
                                <div class="card-body p-2">
                                    <p class="card-text small mb-1">
                                        <strong><?php echo htmlspecialchars($dok->file_name); ?></strong>
                                    </p>
                                    <?php if ($dok->keterangan): ?>
                                        <p class="card-text small text-muted mb-1">
                                            <?php echo htmlspecialchars($dok->keterangan); ?>
                                        </p>
                                    <?php endif; ?>
                                    <div class="btn-group btn-group-sm w-100">
                                        <a href="<?php echo base_url($dok->file_path); ?>" 
                                           target="_blank" class="btn btn-info">
                                            <i class="fas fa-eye"></i> Lihat
                                        </a>
                                        <a href="<?php echo base_url($dok->file_path); ?>" 
                                           download class="btn btn-success">
                                            <i class="fas fa-download"></i> Download
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>
</div>






