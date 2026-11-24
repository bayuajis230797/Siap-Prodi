<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-calendar-check"></i> Detail Jadwal Sidang PKL
                </h3>
                <div class="card-tools">
                    <a href="<?php echo site_url('Pkl_revisi_mahasiswa'); ?>" class="btn btn-sm btn-secondary">
                        <i class="fas fa-arrow-left"></i> Kembali
                    </a>
                </div>
            </div>
            <div class="card-body">
                <?php if ($this->session->flashdata('error')): ?>
                    <div class="alert alert-danger alert-dismissible fade show">
                        <i class="fas fa-exclamation-circle"></i> <?php echo $this->session->flashdata('error'); ?>
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                <?php endif; ?>

                <?php if ($pengajuan): ?>
                    <div class="alert alert-info">
                        <strong>Judul PKL:</strong> <?php echo htmlspecialchars($pengajuan->judul_pkl); ?>
                    </div>
                <?php endif; ?>

                <!-- Informasi Jadwal -->
                <div class="card card-outline card-primary mb-3">
                    <div class="card-header">
                        <h5 class="card-title mb-0">
                            <i class="fas fa-calendar-check"></i> Informasi Jadwal Sidang
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <table class="table table-borderless">
                                    <tr>
                                        <th width="40%">Tanggal:</th>
                                        <td>
                                            <strong><?php echo date('d/m/Y', strtotime($jadwal->tanggal)); ?></strong>
                                            <br>
                                            <small class="text-muted">
                                                <?php 
                                                $hari_inggris = date('l', strtotime($jadwal->tanggal));
                                                $hari_indonesia = array(
                                                    'Monday' => 'Senin',
                                                    'Tuesday' => 'Selasa',
                                                    'Wednesday' => 'Rabu',
                                                    'Thursday' => 'Kamis',
                                                    'Friday' => 'Jumat',
                                                    'Saturday' => 'Sabtu',
                                                    'Sunday' => 'Minggu'
                                                );
                                                echo isset($hari_indonesia[$hari_inggris]) ? $hari_indonesia[$hari_inggris] : $hari_inggris;
                                                ?>
                                            </small>
                                        </td>
                                    </tr>
                                    <tr>
                                        <th>Jam:</th>
                                        <td>
                                            <strong><?php echo date('H:i', strtotime($jadwal->jam_mulai)); ?> - <?php echo date('H:i', strtotime($jadwal->jam_selesai)); ?></strong>
                                        </td>
                                    </tr>
                                    <tr>
                                        <th>Ruangan:</th>
                                        <td><strong><?php echo $jadwal->ruangan ?: '-'; ?></strong></td>
                                    </tr>
                                    <tr>
                                        <th>Status:</th>
                                        <td>
                                            <?php
                                            $status_class = 'secondary';
                                            $status_text = ucfirst($jadwal->status);
                                            switch ($jadwal->status) {
                                                case 'terjadwal':
                                                    $status_class = 'info';
                                                    break;
                                                case 'selesai':
                                                    $status_class = 'success';
                                                    break;
                                                case 'batal':
                                                    $status_class = 'danger';
                                                    break;
                                            }
                                            ?>
                                            <span class="badge badge-<?php echo $status_class; ?>"><?php echo $status_text; ?></span>
                                        </td>
                                    </tr>
                                </table>
                            </div>
                            <div class="col-md-6">
                                <table class="table table-borderless">
                                    <tr>
                                        <th width="40%">Pembimbing:</th>
                                        <td>
                                            <strong><?php echo $jadwal->pembimbing_nama ?: '-'; ?></strong>
                                            <?php if ($jadwal->pembimbing_nidn): ?>
                                                <br><small class="text-muted">NIDN: <?php echo $jadwal->pembimbing_nidn; ?></small>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                    <tr>
                                        <th>Penguji:</th>
                                        <td>
                                            <strong><?php echo $jadwal->penguji_nama ?: '-'; ?></strong>
                                            <?php if ($jadwal->penguji_nidn): ?>
                                                <br><small class="text-muted">NIDN: <?php echo $jadwal->penguji_nidn; ?></small>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Catatan Revisi dari Penguji -->
                <?php if (!empty($penilaian_list)): ?>
                    <?php 
                    $ada_catatan = false;
                    foreach ($penilaian_list as $penilaian) {
                        if (!empty($penilaian->catatan)) {
                            $ada_catatan = true;
                            break;
                        }
                    }
                    ?>
                    <?php if ($ada_catatan): ?>
                        <div class="card card-outline card-warning">
                            <div class="card-header">
                                <h5 class="card-title mb-0">
                                    <i class="fas fa-comments"></i> Catatan Revisi dari Dosen Penguji
                                </h5>
                            </div>
                            <div class="card-body">
                                <?php foreach ($penilaian_list as $penilaian): ?>
                                    <?php if (!empty($penilaian->catatan)): ?>
                                        <div class="card card-outline card-warning mb-3">
                                            <div class="card-header">
                                                <h6 class="mb-0">
                                                    <i class="fas fa-user-tie"></i> 
                                                    Dosen Penguji: <?php echo $penilaian->penilai_nama ?: 'Tidak diketahui'; ?>
                                                    <?php if ($penilaian->penilai_nidn): ?>
                                                        <small class="text-muted">(NIDN: <?php echo $penilaian->penilai_nidn; ?>)</small>
                                                    <?php endif; ?>
                                                </h6>
                                            </div>
                                            <div class="card-body">
                                                <div class="mb-2">
                                                    <strong>Catatan Revisi:</strong>
                                                </div>
                                                <div class="alert alert-warning mb-0">
                                                    <p class="mb-0" style="white-space: pre-wrap;"><?php echo htmlspecialchars($penilaian->catatan); ?></p>
                                                </div>
                                                <?php if ($penilaian->tanggal_penilaian): ?>
                                                    <small class="text-muted mt-2 d-block">
                                                        <i class="fas fa-calendar"></i> 
                                                        Tanggal Penilaian: <?php echo date('d/m/Y', strtotime($penilaian->tanggal_penilaian)); ?>
                                                    </small>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    <?php endif; ?>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    <?php else: ?>
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle"></i> Belum ada catatan revisi dari dosen penguji.
                        </div>
                    <?php endif; ?>
                <?php else: ?>
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle"></i> Belum ada penilaian dari dosen penguji.
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

