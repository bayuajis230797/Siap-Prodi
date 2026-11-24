<div class="row">
    <div class="col-12">
        <div class="card card-primary">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-calendar-alt"></i> Detail Jadwal Sidang Skripsi
                </h3>
                <div class="card-tools">
                    <a href="<?php echo site_url('Jadwal_skripsi_sekprodi'); ?>" class="btn btn-secondary btn-sm">
                        <i class="fas fa-arrow-left"></i> Kembali
                    </a>
                </div>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <h5>Informasi Mahasiswa</h5>
                        <table class="table table-bordered">
                            <tr>
                                <th width="40%">NIM</th>
                                <td><?php echo htmlspecialchars($jadwal->nim ?: '-'); ?></td>
                            </tr>
                            <tr>
                                <th>Nama</th>
                                <td><?php echo htmlspecialchars($jadwal->nama_mahasiswa ?: '-'); ?></td>
                            </tr>
                            <tr>
                                <th>Kelas</th>
                                <td><?php echo htmlspecialchars($jadwal->kelas ?: '-'); ?></td>
                            </tr>
                            <tr>
                                <th>Judul Skripsi</th>
                                <td><?php echo htmlspecialchars($jadwal->judul_skripsi ?: '-'); ?></td>
                            </tr>
                        </table>
                    </div>
                    <div class="col-md-6">
                        <h5>Informasi Jadwal</h5>
                        <table class="table table-bordered">
                            <tr>
                                <th width="40%">Tanggal</th>
                                <td><?php echo isset($jadwal->tanggal_ujian) ? date('d/m/Y', strtotime($jadwal->tanggal_ujian)) : '-'; ?></td>
                            </tr>
                            <tr>
                                <th>Jam</th>
                                <td>
                                    <?php 
                                    if (isset($jadwal->jam_mulai) && isset($jadwal->jam_selesai)) {
                                        echo date('H:i', strtotime($jadwal->jam_mulai)) . ' - ' . date('H:i', strtotime($jadwal->jam_selesai));
                                    } else {
                                        echo '-';
                                    }
                                    ?>
                                </td>
                            </tr>
                            <tr>
                                <th>Ruangan</th>
                                <td><?php echo htmlspecialchars($jadwal->ruangan ?: '-'); ?></td>
                            </tr>
                            <tr>
                                <th>Status</th>
                                <td>
                                    <?php
                                    $status_class = 'secondary';
                                    $status = isset($jadwal->status) ? $jadwal->status : 'terjadwal';
                                    switch ($status) {
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
                                    <span class="badge badge-<?php echo $status_class; ?>">
                                        <?php echo ucfirst($status); ?>
                                    </span>
                                </td>
                            </tr>
                        </table>
                    </div>
                </div>

                <hr>

                <div class="row">
                    <div class="col-12">
                        <h5>Tim Penguji</h5>
                        <table class="table table-bordered">
                            <tr>
                                <th width="20%">Ketua Penguji</th>
                                <td>
                                    <?php if (isset($jadwal->ketua_penguji) && $jadwal->ketua_penguji): ?>
                                        <?php echo htmlspecialchars($jadwal->ketua_penguji); ?><br>
                                        <small class="text-muted">NIDN: <?php echo htmlspecialchars($jadwal->ketua_penguji_nidn ?: '-'); ?></small>
                                    <?php else: ?>
                                        <span class="text-muted">-</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <tr>
                                <th>Anggota Penguji 1</th>
                                <td>
                                    <?php if (isset($jadwal->anggota_penguji_1) && $jadwal->anggota_penguji_1): ?>
                                        <?php echo htmlspecialchars($jadwal->anggota_penguji_1); ?><br>
                                        <small class="text-muted">NIDN: <?php echo htmlspecialchars($jadwal->anggota_penguji_1_nidn ?: '-'); ?></small>
                                    <?php else: ?>
                                        <span class="text-muted">-</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <tr>
                                <th>Anggota Penguji 2</th>
                                <td>
                                    <?php if (isset($jadwal->anggota_penguji_2) && $jadwal->anggota_penguji_2): ?>
                                        <?php echo htmlspecialchars($jadwal->anggota_penguji_2); ?><br>
                                        <small class="text-muted">NIDN: <?php echo htmlspecialchars($jadwal->anggota_penguji_2_nidn ?: '-'); ?></small>
                                    <?php else: ?>
                                        <span class="text-muted">-</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        </table>
                    </div>
                </div>

                <?php if (isset($jadwal->catatan) && $jadwal->catatan): ?>
                    <hr>
                    <h5>Catatan</h5>
                    <p><?php echo nl2br(htmlspecialchars($jadwal->catatan)); ?></p>
                <?php endif; ?>

                <?php if ((isset($jadwal->nilai_skripsi_final) && $jadwal->nilai_skripsi_final) || (isset($jadwal->nilai_huruf) && $jadwal->nilai_huruf)): ?>
                    <hr>
                    <h5>Hasil Penilaian</h5>
                    <div class="row">
                        <div class="col-md-3">
                            <p class="mb-1"><strong>Nilai Bimbingan</strong></p>
                            <h4><?php echo isset($jadwal->nilai_bimbingan_final) && $jadwal->nilai_bimbingan_final !== null ? number_format($jadwal->nilai_bimbingan_final, 2) : '-'; ?></h4>
                        </div>
                        <div class="col-md-3">
                            <p class="mb-1"><strong>Nilai Sidang</strong></p>
                            <h4><?php echo isset($jadwal->nilai_sidang) && $jadwal->nilai_sidang !== null ? number_format($jadwal->nilai_sidang, 2) : '-'; ?></h4>
                        </div>
                        <div class="col-md-3">
                            <p class="mb-1"><strong>Nilai Akhir</strong></p>
                            <h4 class="text-primary">
                                <?php echo isset($jadwal->nilai_skripsi_final) && $jadwal->nilai_skripsi_final !== null ? number_format($jadwal->nilai_skripsi_final, 2) : '-'; ?>
                            </h4>
                        </div>
                        <div class="col-md-3">
                            <p class="mb-1"><strong>Grade</strong></p>
                            <h4>
                                <?php if (isset($jadwal->nilai_huruf) && $jadwal->nilai_huruf): ?>
                                    <span class="badge badge-success badge-lg"><?php echo htmlspecialchars($jadwal->nilai_huruf); ?></span>
                                <?php else: ?>
                                    <span class="text-muted">-</span>
                                <?php endif; ?>
                            </h4>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>




