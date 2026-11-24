<div class="row">
    <div class="col-12">
        <div class="card card-primary card-outline">
            <div class="card-header">
                <h3 class="card-title">Jadwal Ujian Saya</h3>
                <div class="card-tools">
                    <a href="<?php echo site_url('Absensi_ujian/scan'); ?>" class="btn btn-success btn-sm">
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
                <?php if ($this->session->flashdata('info')): ?>
                    <div class="alert alert-info alert-dismissible">
                        <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
                        <?php echo $this->session->flashdata('info'); ?>
                    </div>
                <?php endif; ?>
                <?php if ($this->session->flashdata('error')): ?>
                    <div class="alert alert-danger alert-dismissible">
                        <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
                        <?php echo $this->session->flashdata('error'); ?>
                    </div>
                <?php endif; ?>

                <div class="nav-tabs-custom">
                    <ul class="nav nav-tabs">
                        <li class="nav-item">
                            <a class="nav-link active" data-toggle="tab" href="#tab-uts">
                                <i class="fas fa-clipboard-list"></i> Jadwal UTS
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" data-toggle="tab" href="#tab-uas">
                                <i class="fas fa-file-alt"></i> Jadwal UAS
                            </a>
                        </li>
                    </ul>
                    <div class="tab-content">
                        <!-- Tab UTS -->
                        <div class="tab-pane active" id="tab-uts">
                            <?php if (empty($jadwal_uts)): ?>
                                <div class="alert alert-info">
                                    <i class="fas fa-info-circle"></i> Belum ada jadwal UTS untuk kelas Anda.
                                </div>
                            <?php else: ?>
                                <div class="table-responsive">
                                    <table class="table table-bordered table-striped table-hover">
                                        <thead>
                                            <tr>
                                                <th width="5%">No</th>
                                                <th width="20%">Mata Kuliah</th>
                                                <th width="12%">Tanggal</th>
                                                <th width="15%">Jam</th>
                                                <th width="10%" class="text-center">Sesi</th>
                                                <th width="12%">Ruangan</th>
                                                <th width="15%">Status Absensi</th>
                                                <th width="11%" class="text-center">Aksi</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php $no = 1; foreach ($jadwal_uts as $jadwal): ?>
                                                <tr>
                                                    <td><?php echo $no++; ?></td>
                                                    <td>
                                                        <strong><?php echo htmlspecialchars($jadwal->nama_mk); ?></strong>
                                                    </td>
                                                    <td>
                                                        <?php echo date('d/m/Y', strtotime($jadwal->tanggal)); ?>
                                                    </td>
                                                    <td>
                                                        <?php echo date('H:i', strtotime($jadwal->jam_mulai)); ?> - 
                                                        <?php echo date('H:i', strtotime($jadwal->jam_selesai)); ?> WIB
                                                    </td>
                                                    <td class="text-center">
                                                        <span class="badge badge-info"><?php echo $jadwal->sesi; ?></span>
                                                    </td>
                                                    <td><?php echo $jadwal->nama_ruang ? htmlspecialchars($jadwal->nama_ruang) : '-'; ?></td>
                                                    <td>
                                                        <?php 
                                                        // Default status adalah "tidak_hadir" jika belum ada
                                                        $status_absen = $jadwal->status_absen ? $jadwal->status_absen : 'tidak_hadir';
                                                        $status_labels = array(
                                                            'hadir' => array('label' => 'Hadir', 'class' => 'success'),
                                                            'tidak_hadir' => array('label' => 'Tidak Hadir', 'class' => 'danger')
                                                        );
                                                        $status = $status_labels[$status_absen] ?? array('label' => $status_absen, 'class' => 'secondary');
                                                        ?>
                                                        <span class="badge badge-<?php echo $status['class']; ?> badge-lg">
                                                            <?php echo $status['label']; ?>
                                                        </span>
                                                        <?php if ($status_absen == 'hadir' && $jadwal->waktu_absen): ?>
                                                            <br><small class="text-muted">
                                                                <?php echo date('d/m/Y H:i', strtotime($jadwal->waktu_absen)); ?>
                                                            </small>
                                                        <?php endif; ?>
                                                    </td>
                                                    <td>
                                                        <div class="btn-group btn-group-sm">
                                                            <a href="<?php echo site_url('Absensi_ujian/detail/' . $jadwal->qr_code_token); ?>" 
                                                               class="btn btn-info" title="Detail">
                                                                <i class="fas fa-eye"></i> Detail
                                                            </a>
                                                            <?php 
                                                            $status_absen = $jadwal->status_absen ? $jadwal->status_absen : 'tidak_hadir';
                                                            if ($status_absen == 'tidak_hadir' && !empty($jadwal->qr_code_token) && !empty($jadwal->can_absen)): ?>
                                                                <a href="<?php echo site_url('Absensi_ujian/scan/' . $jadwal->qr_code_token); ?>" 
                                                                   class="btn btn-success" title="Scan QR Code">
                                                                    <i class="fas fa-qrcode"></i> Absen
                                                                </a>
                                                            <?php endif; ?>
                                                        </div>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            <?php endif; ?>
                        </div>

                        <!-- Tab UAS -->
                        <div class="tab-pane" id="tab-uas">
                            <?php if (empty($jadwal_uas)): ?>
                                <div class="alert alert-info">
                                    <i class="fas fa-info-circle"></i> Belum ada jadwal UAS untuk kelas Anda.
                                </div>
                            <?php else: ?>
                                <div class="table-responsive">
                                    <table class="table table-bordered table-striped table-hover">
                                        <thead>
                                            <tr>
                                                <th width="5%">No</th>
                                                <th width="20%">Mata Kuliah</th>
                                                <th width="12%">Tanggal</th>
                                                <th width="15%">Jam</th>
                                                <th width="10%" class="text-center">Sesi</th>
                                                <th width="12%">Ruangan</th>
                                                <th width="15%">Status Absensi</th>
                                                <th width="11%" class="text-center">Aksi</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php $no = 1; foreach ($jadwal_uas as $jadwal): ?>
                                                <tr>
                                                    <td><?php echo $no++; ?></td>
                                                    <td>
                                                        <strong><?php echo htmlspecialchars($jadwal->nama_mk); ?></strong>
                                                    </td>
                                                    <td>
                                                        <?php echo date('d/m/Y', strtotime($jadwal->tanggal)); ?>
                                                    </td>
                                                    <td>
                                                        <?php echo date('H:i', strtotime($jadwal->jam_mulai)); ?> - 
                                                        <?php echo date('H:i', strtotime($jadwal->jam_selesai)); ?> WIB
                                                    </td>
                                                    <td class="text-center">
                                                        <span class="badge badge-info"><?php echo $jadwal->sesi; ?></span>
                                                    </td>
                                                    <td><?php echo $jadwal->nama_ruang ? htmlspecialchars($jadwal->nama_ruang) : '-'; ?></td>
                                                    <td>
                                                        <?php 
                                                        // Default status adalah "tidak_hadir" jika belum ada
                                                        $status_absen = $jadwal->status_absen ? $jadwal->status_absen : 'tidak_hadir';
                                                        $status_labels = array(
                                                            'hadir' => array('label' => 'Hadir', 'class' => 'success'),
                                                            'tidak_hadir' => array('label' => 'Tidak Hadir', 'class' => 'danger')
                                                        );
                                                        $status = $status_labels[$status_absen] ?? array('label' => $status_absen, 'class' => 'secondary');
                                                        ?>
                                                        <span class="badge badge-<?php echo $status['class']; ?> badge-lg">
                                                            <?php echo $status['label']; ?>
                                                        </span>
                                                        <?php if ($status_absen == 'hadir' && $jadwal->waktu_absen): ?>
                                                            <br><small class="text-muted">
                                                                <?php echo date('d/m/Y H:i', strtotime($jadwal->waktu_absen)); ?>
                                                            </small>
                                                        <?php endif; ?>
                                                    </td>
                                                    <td>
                                                        <div class="btn-group btn-group-sm">
                                                            <a href="<?php echo site_url('Absensi_ujian/detail/' . $jadwal->qr_code_token); ?>" 
                                                               class="btn btn-info" title="Detail">
                                                                <i class="fas fa-eye"></i> Detail
                                                            </a>
                                                            <?php 
                                                            $status_absen = $jadwal->status_absen ? $jadwal->status_absen : 'tidak_hadir';
                                                            if ($status_absen == 'tidak_hadir' && !empty($jadwal->qr_code_token) && !empty($jadwal->can_absen)): ?>
                                                                <a href="<?php echo site_url('Absensi_ujian/scan/' . $jadwal->qr_code_token); ?>" 
                                                                   class="btn btn-success" title="Scan QR Code">
                                                                    <i class="fas fa-qrcode"></i> Absen
                                                                </a>
                                                            <?php endif; ?>
                                                        </div>
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
            </div>
        </div>
    </div>
</div>

