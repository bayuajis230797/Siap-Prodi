<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-calendar-alt"></i> Jadwal Sidang Skripsi
                </h3>
            </div>
            <div class="card-body">
                <?php if ($this->session->flashdata('success')): ?>
                    <div class="alert alert-success alert-dismissible">
                        <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
                        <?php echo $this->session->flashdata('success'); ?>
                    </div>
                <?php endif; ?>

                <?php if ($this->session->flashdata('error')): ?>
                    <div class="alert alert-danger alert-dismissible">
                        <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
                        <?php echo $this->session->flashdata('error'); ?>
                    </div>
                <?php endif; ?>

                <?php if ($tahun_aktif): ?>
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle"></i>
                        <strong>Tahun Akademik:</strong> <?php echo $tahun_aktif->tahun_akademik . ' ' . ucfirst($tahun_aktif->semester); ?>
                    </div>
                <?php endif; ?>

                <?php if ($skripsi && ($skripsi->nilai_skripsi_final || $skripsi->nilai_huruf)): ?>
                    <div class="card card-success">
                        <div class="card-header">
                            <h3 class="card-title">
                                <i class="fas fa-trophy"></i> Hasil Akhir Skripsi
                            </h3>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-3">
                                    <p class="mb-1"><strong>Nilai Bimbingan</strong></p>
                                    <h4><?php echo $skripsi->nilai_bimbingan_final !== null ? number_format($skripsi->nilai_bimbingan_final, 2) : '-'; ?></h4>
                                </div>
                                <div class="col-md-3">
                                    <p class="mb-1"><strong>Nilai Sidang</strong></p>
                                    <h4><?php echo $skripsi->nilai_sidang !== null ? number_format($skripsi->nilai_sidang, 2) : '-'; ?></h4>
                                </div>
                                <div class="col-md-3">
                                    <p class="mb-1"><strong>Nilai Akhir</strong></p>
                                    <h4 class="text-primary">
                                        <?php echo $skripsi->nilai_skripsi_final !== null ? number_format($skripsi->nilai_skripsi_final, 2) : '-'; ?>
                                    </h4>
                                </div>
                                <div class="col-md-3">
                                    <p class="mb-1"><strong>Grade</strong></p>
                                    <h4>
                                        <?php if ($skripsi->nilai_huruf): ?>
                                            <span class="badge badge-success badge-lg"><?php echo $skripsi->nilai_huruf; ?></span>
                                        <?php else: ?>
                                            <span class="text-muted">-</span>
                                        <?php endif; ?>
                                    </h4>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>

                <?php if (empty($jadwal_list)): ?>
                    <div class="alert alert-warning">
                        <i class="fas fa-exclamation-triangle"></i>
                        Belum ada jadwal sidang yang ditetapkan.
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped">
                            <thead>
                                <tr>
                                    <th width="5%">No</th>
                                    <th width="12%">Tanggal</th>
                                    <th width="10%">Jam</th>
                                    <th width="10%">Ruangan</th>
                                    <th>Ketua Penguji</th>
                                    <th>Anggota Penguji 1</th>
                                    <th>Anggota Penguji 2</th>
                                    <th width="10%" class="text-center">Status</th>
                                    <th width="10%" class="text-center">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php 
                                $no = 1;
                                foreach ($jadwal_list as $jadwal): 
                                ?>
                                    <tr>
                                        <td><?php echo $no++; ?></td>
                                        <td><?php echo date('d/m/Y', strtotime($jadwal->tanggal_ujian)); ?></td>
                                        <td><?php echo date('H:i', strtotime($jadwal->jam_mulai)) . ' - ' . date('H:i', strtotime($jadwal->jam_selesai)); ?></td>
                                        <td><?php echo htmlspecialchars($jadwal->ruangan ?: '-'); ?></td>
                                        <td>
                                            <?php if ($jadwal->ketua_penguji): ?>
                                                <?php echo htmlspecialchars($jadwal->ketua_penguji); ?><br>
                                                <small class="text-muted"><?php echo htmlspecialchars($jadwal->ketua_penguji_nidn); ?></small>
                                            <?php else: ?>
                                                <span class="text-muted">-</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php if ($jadwal->anggota_penguji_1): ?>
                                                <?php echo htmlspecialchars($jadwal->anggota_penguji_1); ?><br>
                                                <small class="text-muted"><?php echo htmlspecialchars($jadwal->anggota_penguji_1_nidn); ?></small>
                                            <?php else: ?>
                                                <span class="text-muted">-</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php if ($jadwal->anggota_penguji_2): ?>
                                                <?php echo htmlspecialchars($jadwal->anggota_penguji_2); ?><br>
                                                <small class="text-muted"><?php echo htmlspecialchars($jadwal->anggota_penguji_2_nidn); ?></small>
                                            <?php else: ?>
                                                <span class="text-muted">-</span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="text-center">
                                            <?php
                                            $status_class = 'secondary';
                                            switch ($jadwal->status) {
                                                case 'terjadwal':
                                                    $status_class = 'info';
                                                    break;
                                                case 'selesai':
                                                    $status_class = 'success';
                                                    break;
                                            }
                                            ?>
                                            <span class="badge badge-<?php echo $status_class; ?>">
                                                <?php echo ucfirst($jadwal->status); ?>
                                            </span>
                                        </td>
                                        <td class="text-center">
                                            <a href="<?php echo site_url('Jadwal_skripsi_mahasiswa/detail/' . $jadwal->id); ?>" 
                                               class="btn btn-sm btn-info" title="Detail">
                                                <i class="fas fa-eye"></i>
                                            </a>
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



