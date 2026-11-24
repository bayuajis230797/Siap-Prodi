<div class="row">
    <div class="col-12">
        <div class="card card-primary card-outline">
            <div class="card-header">
                <h3 class="card-title">Kegiatan Saya</h3>
                <div class="card-tools">
                    <a href="<?php echo site_url('Absensi/scan'); ?>" class="btn btn-success btn-sm">
                        <i class="fas fa-qrcode"></i> Scan QR Code
                    </a>
                </div>
            </div>
            <div class="card-body">
                <?php if ($this->session->flashdata('success')): ?>
                    <div class="alert alert-success alert-dismissible">
                        <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
                        <?php echo $this->session->flashdata('success'); ?>
                    </div>
                <?php endif; ?>

                <!-- Table -->
                <div class="table-responsive">
                    <table class="table table-bordered table-striped table-hover">
                        <thead>
                            <tr>
                                <th width="5%">No</th>
                                <th width="30%">Nama Kegiatan</th>
                                <th width="15%">Tanggal & Waktu</th>
                                <th width="15%">Lokasi</th>
                                <th width="15%">Status Absensi</th>
                                <th width="20%">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($kegiatan_list)): ?>
                                <tr>
                                    <td colspan="6" class="text-center">Anda belum terdaftar dalam kegiatan apapun.</td>
                                </tr>
                            <?php else: ?>
                                <?php $no = 1; ?>
                                <?php foreach ($kegiatan_list as $kegiatan): ?>
                                    <tr>
                                        <td><?php echo $no++; ?></td>
                                        <td>
                                            <strong><?php echo htmlspecialchars($kegiatan->nama_kegiatan); ?></strong>
                                            <br><small class="text-muted">
                                                <?php echo $kegiatan->jenis_kegiatan == 'akademik' ? 'Akademik' : 'Non-Akademik'; ?>
                                            </small>
                                        </td>
                                        <td>
                                            <i class="fas fa-calendar"></i> <?php echo date('d/m/Y', strtotime($kegiatan->tanggal_kegiatan)); ?><br>
                                            <i class="fas fa-clock"></i> <?php echo date('H:i', strtotime($kegiatan->jam_mulai)); ?> WIB
                                            <?php if ($kegiatan->jam_selesai): ?>
                                                - <?php echo date('H:i', strtotime($kegiatan->jam_selesai)); ?> WIB
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php echo $kegiatan->lokasi ? htmlspecialchars($kegiatan->lokasi) : '-'; ?>
                                        </td>
                                        <td>
                                            <?php if ($kegiatan->status_absen): ?>
                                                <?php 
                                                $status_labels = array(
                                                    'hadir' => array('label' => 'Hadir', 'class' => 'success'),
                                                    'terlambat' => array('label' => 'Terlambat', 'class' => 'warning'),
                                                    'tidak_hadir' => array('label' => 'Tidak Hadir', 'class' => 'danger')
                                                );
                                                $status = $status_labels[$kegiatan->status_absen] ?? array('label' => $kegiatan->status_absen, 'class' => 'secondary');
                                                ?>
                                                <span class="badge badge-<?php echo $status['class']; ?> badge-lg">
                                                    <?php echo $status['label']; ?>
                                                </span>
                                                <?php if ($kegiatan->waktu_absen): ?>
                                                    <br><small class="text-muted">
                                                        <?php echo date('d/m/Y H:i', strtotime($kegiatan->waktu_absen)); ?>
                                                    </small>
                                                <?php endif; ?>
                                            <?php else: ?>
                                                <span class="badge badge-secondary">Belum Absen</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <div class="btn-group btn-group-sm">
                                                <a href="<?php echo site_url('Absensi/detail/' . $kegiatan->qr_code_token); ?>" 
                                                   class="btn btn-info" title="Detail">
                                                    <i class="fas fa-eye"></i> Detail
                                                </a>
                                                <?php if ($kegiatan->status == 'aktif' && !$kegiatan->status_absen): ?>
                                                    <a href="<?php echo site_url('Absensi/scan'); ?>" 
                                                       class="btn btn-success" title="Scan QR Code">
                                                        <i class="fas fa-qrcode"></i> Absen
                                                    </a>
                                                <?php endif; ?>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <?php if (!empty($pagination)): ?>
                    <div class="mt-3">
                        <?php echo $pagination; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

