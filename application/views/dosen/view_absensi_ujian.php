<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-users"></i> Status Absensi Mahasiswa - <?php echo $jenis_ujian; ?>
                </h3>
                <div class="card-tools">
                    <a href="<?php echo site_url('Jadwal_pengawas/' . strtolower($jenis_ujian)); ?>" class="btn btn-secondary btn-sm">
                        <i class="fas fa-arrow-left"></i> Kembali
                    </a>
                </div>
            </div>
            <div class="card-body">
                <!-- Info Jadwal -->
                <div class="row mb-4">
                    <div class="col-md-6">
                        <table class="table table-sm table-borderless">
                            <tr>
                                <th width="40%">Mata Kuliah</th>
                                <td><?php echo htmlspecialchars($jadwal->kode_mk ?? '-'); ?> - <?php echo htmlspecialchars($jadwal->nama_mk ?? '-'); ?></td>
                            </tr>
                            <tr>
                                <th>Kelas</th>
                                <td><?php echo htmlspecialchars($jadwal->kelas ?? '-'); ?></td>
                            </tr>
                            <tr>
                                <th>Tanggal</th>
                                <td><?php echo $jadwal->tanggal ? date('d/m/Y', strtotime($jadwal->tanggal)) : '-'; ?></td>
                            </tr>
                            <tr>
                                <th>Jam</th>
                                <td>
                                    <?php 
                                    if ($jadwal->jam_mulai && $jadwal->jam_selesai) {
                                        echo date('H:i', strtotime($jadwal->jam_mulai)) . ' - ' . date('H:i', strtotime($jadwal->jam_selesai)) . ' WIB';
                                    } else {
                                        echo '-';
                                    }
                                    ?>
                                </td>
                            </tr>
                        </table>
                    </div>
                    <div class="col-md-6">
                        <table class="table table-sm table-borderless">
                            <tr>
                                <th width="40%">Ruang</th>
                                <td><?php echo htmlspecialchars($jadwal->nama_ruang ?? ($jadwal->kode_ruang ?? '-')); ?></td>
                            </tr>
                            <tr>
                                <th>Sesi</th>
                                <td>
                                    <?php if (isset($jadwal->sesi) && $jadwal->sesi == 2): ?>
                                        <span class="badge badge-warning">Sesi 2</span>
                                    <?php else: ?>
                                        <span class="badge badge-info">Sesi 1</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <tr>
                                <th>Dosen Pengampu</th>
                                <td><?php echo htmlspecialchars($jadwal->nama_dosen ?? '-'); ?></td>
                            </tr>
                        </table>
                    </div>
                </div>

                <!-- Statistik -->
                <div class="row mb-4">
                    <div class="col-md-4">
                        <div class="info-box">
                            <span class="info-box-icon bg-info"><i class="fas fa-users"></i></span>
                            <div class="info-box-content">
                                <span class="info-box-text">Total Mahasiswa</span>
                                <span class="info-box-number"><?php echo $total_mahasiswa; ?></span>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="info-box">
                            <span class="info-box-icon bg-success"><i class="fas fa-check"></i></span>
                            <div class="info-box-content">
                                <span class="info-box-text">Hadir</span>
                                <span class="info-box-number"><?php echo $hadir; ?></span>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="info-box">
                            <span class="info-box-icon bg-danger"><i class="fas fa-times"></i></span>
                            <div class="info-box-content">
                                <span class="info-box-text">Tidak Hadir</span>
                                <span class="info-box-number"><?php echo $tidak_hadir; ?></span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Tabel Daftar Mahasiswa -->
                <div class="table-responsive">
                    <table class="table table-bordered table-striped table-hover">
                        <thead>
                            <tr>
                                <th width="5%">No</th>
                                <th width="15%">NIM</th>
                                <th>Nama Mahasiswa</th>
                                <th width="15%" class="text-center">Status</th>
                                <th width="20%" class="text-center">Waktu Absen</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($mahasiswa_list)): ?>
                                <?php $no = 1; foreach ($mahasiswa_list as $mhs): ?>
                                    <tr>
                                        <td><?php echo $no++; ?></td>
                                        <td><?php echo htmlspecialchars($mhs->nim); ?></td>
                                        <td><?php echo htmlspecialchars($mhs->nama_mahasiswa); ?></td>
                                        <td class="text-center">
                                            <?php if ($mhs->status_kehadiran == 'hadir'): ?>
                                                <span class="badge badge-success">Hadir</span>
                                            <?php else: ?>
                                                <span class="badge badge-danger">Tidak Hadir</span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="text-center">
                                            <?php if ($mhs->status_kehadiran == 'hadir' && $mhs->waktu_absen): ?>
                                                <?php echo date('d/m/Y H:i:s', strtotime($mhs->waktu_absen)); ?>
                                            <?php elseif ($mhs->status_kehadiran == 'hadir' && $mhs->created_at): ?>
                                                <?php echo date('d/m/Y H:i:s', strtotime($mhs->created_at)); ?>
                                            <?php else: ?>
                                                <span class="text-muted">-</span>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="5" class="text-center">
                                        <div class="alert alert-info">
                                            <i class="fas fa-info-circle"></i> Belum ada mahasiswa terdaftar untuk jadwal ini.
                                        </div>
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

