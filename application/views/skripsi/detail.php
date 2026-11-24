<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Detail Skripsi</h3>
                <div class="card-tools">
                    <a href="<?php echo site_url('Skripsi'); ?>" class="btn btn-secondary btn-sm">
                        <i class="fas fa-arrow-left"></i> Kembali
                    </a>
                </div>
            </div>
            <div class="card-body">
                <!-- Informasi Mahasiswa -->
                <div class="row mb-4">
                    <div class="col-md-6">
                        <h5 class="mb-3"><i class="fas fa-user"></i> Informasi Mahasiswa</h5>
                        <table class="table table-sm">
                            <tr>
                                <th width="40%">NIM</th>
                                <td><strong><?php echo $skripsi->nim; ?></strong></td>
                            </tr>
                            <tr>
                                <th>Nama Mahasiswa</th>
                                <td><?php echo $skripsi->nama_mahasiswa; ?></td>
                            </tr>
                            <tr>
                                <th>Kelas</th>
                                <td><span class="badge badge-info"><?php echo $skripsi->kelas; ?></span></td>
                            </tr>
                            <tr>
                                <th>Status</th>
                                <td>
                                    <?php
                                    $status_class = 'secondary';
                                    $status_text = ucfirst(str_replace('_', ' ', $skripsi->status));
                                    switch ($skripsi->status) {
                                        case 'terdaftar':
                                            $status_class = 'info';
                                            break;
                                        case 'sedang_skripsi':
                                            $status_class = 'primary';
                                            break;
                                        case 'layak_sidang':
                                            $status_class = 'warning';
                                            break;
                                        case 'selesai_skripsi':
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
                        <h5 class="mb-3"><i class="fas fa-user-tie"></i> Pembimbing</h5>
                        <table class="table table-sm">
                            <tr>
                                <th width="40%">Pembimbing 1</th>
                                <td>
                                    <?php if (!empty($skripsi->pembimbing_1)): ?>
                                        <strong><?php echo $skripsi->pembimbing_1; ?></strong><br>
                                        <small class="text-muted">NIDN: <?php echo $skripsi->pembimbing_1_nidn ?: '-'; ?></small>
                                    <?php else: ?>
                                        <span class="text-muted">Belum ditetapkan</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <tr>
                                <th>Pembimbing 2</th>
                                <td>
                                    <?php if (!empty($skripsi->pembimbing_2)): ?>
                                        <strong><?php echo $skripsi->pembimbing_2; ?></strong><br>
                                        <small class="text-muted">NIDN: <?php echo $skripsi->pembimbing_2_nidn ?: '-'; ?></small>
                                    <?php else: ?>
                                        <span class="text-muted">Belum ditetapkan</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        </table>
                    </div>
                </div>

                <!-- Informasi Skripsi -->
                <div class="row mb-4">
                    <div class="col-12">
                        <h5 class="mb-3"><i class="fas fa-book"></i> Informasi Skripsi</h5>
                        <table class="table table-sm table-bordered">
                            <tr>
                                <th width="20%">Judul Skripsi</th>
                                <td><?php echo $skripsi->judul_skripsi ? $skripsi->judul_skripsi : '<span class="text-muted">-</span>'; ?></td>
                            </tr>
                            <tr>
                                <th>Tanggal Mulai</th>
                                <td><?php echo $skripsi->tanggal_mulai ? date('d/m/Y', strtotime($skripsi->tanggal_mulai)) : '<span class="text-muted">-</span>'; ?></td>
                            </tr>
                            <tr>
                                <th>Tanggal Selesai</th>
                                <td><?php echo $skripsi->tanggal_selesai ? date('d/m/Y', strtotime($skripsi->tanggal_selesai)) : '<span class="text-muted">-</span>'; ?></td>
                            </tr>
                        </table>
                    </div>
                </div>

                <!-- Nilai -->
                <div class="row mb-4">
                    <div class="col-12">
                        <h5 class="mb-3"><i class="fas fa-chart-line"></i> Nilai</h5>
                        <table class="table table-sm table-bordered">
                            <thead class="bg-light">
                                <tr>
                                    <th width="25%">Aspek</th>
                                    <th width="25%" class="text-center">Nilai</th>
                                    <th width="25%" class="text-center">Nilai Bimbingan</th>
                                    <th width="25%" class="text-center">Nilai Sidang</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td><strong>Nilai Bimbingan</strong></td>
                                    <td class="text-center">
                                        <?php echo $skripsi->nilai_bimbingan_final !== null ? '<strong>' . number_format($skripsi->nilai_bimbingan_final, 2) . '</strong>' : '<span class="text-muted">-</span>'; ?>
                                    </td>
                                    <td class="text-center">
                                        P1: <?php echo $skripsi->nilai_bimbingan_p1 !== null ? number_format($skripsi->nilai_bimbingan_p1, 2) : '<span class="text-muted">-</span>'; ?><br>
                                        P2: <?php echo $skripsi->nilai_bimbingan_p2 !== null ? number_format($skripsi->nilai_bimbingan_p2, 2) : '<span class="text-muted">-</span>'; ?>
                                    </td>
                                    <td class="text-center">-</td>
                                </tr>
                                <tr>
                                    <td><strong>Nilai Sidang</strong></td>
                                    <td class="text-center">
                                        <?php echo $skripsi->nilai_sidang !== null ? '<strong>' . number_format($skripsi->nilai_sidang, 2) . '</strong>' : '<span class="text-muted">-</span>'; ?>
                                    </td>
                                    <td class="text-center">-</td>
                                    <td class="text-center">
                                        <?php 
                                        // Tampilkan nilai dari masing-masing penguji
                                        $nilai_penguji_display = array();
                                        if (!empty($jadwal_skripsi)) {
                                            foreach ($jadwal_skripsi as $jadwal) {
                                                if (isset($nilai_sidang_penguji[$jadwal->id])) {
                                                    $nilai = $nilai_sidang_penguji[$jadwal->id];
                                                    if ($nilai['ketua'] && $nilai['ketua']->nilai_total !== null) {
                                                        $nilai_penguji_display[] = 'Ketua: ' . number_format($nilai['ketua']->nilai_total, 2);
                                                    }
                                                    if ($nilai['anggota_1'] && $nilai['anggota_1']->nilai_total !== null) {
                                                        $nilai_penguji_display[] = 'Anggota 1: ' . number_format($nilai['anggota_1']->nilai_total, 2);
                                                    }
                                                    if ($nilai['anggota_2'] && $nilai['anggota_2']->nilai_total !== null) {
                                                        $nilai_penguji_display[] = 'Anggota 2: ' . number_format($nilai['anggota_2']->nilai_total, 2);
                                                    }
                                                }
                                            }
                                        }
                                        if (!empty($nilai_penguji_display)) {
                                            echo implode('<br>', $nilai_penguji_display);
                                        } else {
                                            echo '<span class="text-muted">-</span>';
                                        }
                                        ?>
                                    </td>
                                </tr>
                                <tr class="table-primary">
                                    <td><strong>Nilai Akhir Skripsi</strong></td>
                                    <td class="text-center">
                                        <strong><?php echo $skripsi->nilai_skripsi_final !== null ? number_format($skripsi->nilai_skripsi_final, 2) : '<span class="text-muted">-</span>'; ?></strong>
                                    </td>
                                    <td class="text-center">
                                        <?php echo $skripsi->nilai_bimbingan_final !== null ? number_format($skripsi->nilai_bimbingan_final * 0.4, 2) . ' (40%)' : '<span class="text-muted">-</span>'; ?>
                                    </td>
                                    <td class="text-center">
                                        <?php echo $skripsi->nilai_sidang !== null ? number_format($skripsi->nilai_sidang * 0.6, 2) . ' (60%)' : '<span class="text-muted">-</span>'; ?>
                                    </td>
                                </tr>
                                <tr>
                                    <td><strong>Grade</strong></td>
                                    <td class="text-center" colspan="3">
                                        <?php if ($skripsi->nilai_huruf): ?>
                                            <span class="badge badge-success" style="font-size: 1.2em; padding: 8px 15px;"><?php echo $skripsi->nilai_huruf; ?></span>
                                        <?php else: ?>
                                            <span class="text-muted">-</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Rincian Nilai Penguji -->
                <?php if (!empty($jadwal_skripsi) && !empty($nilai_sidang_penguji)): ?>
                <div class="row mb-4">
                    <div class="col-12">
                        <h5 class="mb-3"><i class="fas fa-chart-bar"></i> Rincian Nilai Masing-Masing Penguji</h5>
                        <?php foreach ($jadwal_skripsi as $jadwal): ?>
                            <?php if (isset($nilai_sidang_penguji[$jadwal->id])): ?>
                                <?php $nilai = $nilai_sidang_penguji[$jadwal->id]; ?>
                                <div class="card card-outline card-info mb-3">
                                    <div class="card-header">
                                        <h6 class="mb-0">
                                            <i class="fas fa-calendar-alt"></i> 
                                            Jadwal: <?php echo date('d/m/Y', strtotime($jadwal->tanggal_ujian)); ?> 
                                            <?php echo date('H:i', strtotime($jadwal->jam_mulai)) . ' - ' . date('H:i', strtotime($jadwal->jam_selesai)); ?>
                                        </h6>
                                    </div>
                                    <div class="card-body">
                                        <div class="table-responsive">
                                            <table class="table table-bordered table-sm">
                                                <thead class="bg-light">
                                                    <tr>
                                                        <th width="25%">Penguji</th>
                                                        <th width="15%" class="text-center">Nilai Total</th>
                                                        <th width="20%" class="text-center">N1</th>
                                                        <th width="20%" class="text-center">N2</th>
                                                        <th width="20%" class="text-center">N3</th>
                                                        <th width="20%" class="text-center">N4</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?php if ($nilai['ketua'] && $nilai['ketua']->nilai_total !== null): ?>
                                                        <tr>
                                                            <td>
                                                                <strong>Ketua Penguji</strong><br>
                                                                <small><?php echo $jadwal->ketua_penguji; ?> (<?php echo $jadwal->ketua_penguji_nidn; ?>)</small>
                                                            </td>
                                                            <td class="text-center"><strong><?php echo number_format($nilai['ketua']->nilai_total, 2); ?></strong></td>
                                                            <td class="text-center"><?php echo number_format($nilai['ketua']->n1, 2); ?></td>
                                                            <td class="text-center"><?php echo number_format($nilai['ketua']->n2, 2); ?></td>
                                                            <td class="text-center"><?php echo number_format($nilai['ketua']->n3, 2); ?></td>
                                                            <td class="text-center"><?php echo number_format($nilai['ketua']->n4, 2); ?></td>
                                                        </tr>
                                                    <?php endif; ?>
                                                    
                                                    <?php if ($nilai['anggota_1'] && $nilai['anggota_1']->nilai_total !== null): ?>
                                                        <tr>
                                                            <td>
                                                                <strong>Anggota Penguji 1</strong><br>
                                                                <small><?php echo $jadwal->anggota_penguji_1; ?> (<?php echo $jadwal->anggota_penguji_1_nidn; ?>)</small>
                                                            </td>
                                                            <td class="text-center"><strong><?php echo number_format($nilai['anggota_1']->nilai_total, 2); ?></strong></td>
                                                            <td class="text-center"><?php echo number_format($nilai['anggota_1']->n1, 2); ?></td>
                                                            <td class="text-center"><?php echo number_format($nilai['anggota_1']->n2, 2); ?></td>
                                                            <td class="text-center"><?php echo number_format($nilai['anggota_1']->n3, 2); ?></td>
                                                            <td class="text-center"><?php echo number_format($nilai['anggota_1']->n4, 2); ?></td>
                                                        </tr>
                                                    <?php endif; ?>
                                                    
                                                    <?php if ($nilai['anggota_2'] && $nilai['anggota_2']->nilai_total !== null): ?>
                                                        <tr>
                                                            <td>
                                                                <strong>Anggota Penguji 2</strong><br>
                                                                <small><?php echo $jadwal->anggota_penguji_2; ?> (<?php echo $jadwal->anggota_penguji_2_nidn; ?>)</small>
                                                            </td>
                                                            <td class="text-center"><strong><?php echo number_format($nilai['anggota_2']->nilai_total, 2); ?></strong></td>
                                                            <td class="text-center"><?php echo number_format($nilai['anggota_2']->n1, 2); ?></td>
                                                            <td class="text-center"><?php echo number_format($nilai['anggota_2']->n2, 2); ?></td>
                                                            <td class="text-center"><?php echo number_format($nilai['anggota_2']->n3, 2); ?></td>
                                                            <td class="text-center"><?php echo number_format($nilai['anggota_2']->n4, 2); ?></td>
                                                        </tr>
                                                    <?php endif; ?>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php endif; ?>

                <!-- Jadwal Ujian -->
                <?php if (!empty($jadwal_skripsi)): ?>
                <div class="row mb-4">
                    <div class="col-12">
                        <h5 class="mb-3"><i class="fas fa-calendar-alt"></i> Jadwal Ujian</h5>
                        <?php foreach ($jadwal_skripsi as $jadwal): ?>
                        <div class="card card-outline card-info mb-3">
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-6">
                                        <p class="mb-1"><strong>Tanggal:</strong> <?php echo date('d/m/Y', strtotime($jadwal->tanggal_ujian)); ?></p>
                                        <p class="mb-1"><strong>Jam:</strong> <?php echo date('H:i', strtotime($jadwal->jam_mulai)) . ' - ' . date('H:i', strtotime($jadwal->jam_selesai)); ?></p>
                                        <p class="mb-1"><strong>Ruangan:</strong> <?php echo $jadwal->ruangan ?: '-'; ?></p>
                                    </div>
                                    <div class="col-md-6">
                                        <p class="mb-1"><strong>Ketua Penguji:</strong> <?php echo $jadwal->ketua_penguji; ?></p>
                                        <p class="mb-1"><strong>Anggota Penguji 1:</strong> <?php echo $jadwal->anggota_penguji_1; ?></p>
                                        <p class="mb-1"><strong>Anggota Penguji 2:</strong> <?php echo $jadwal->anggota_penguji_2; ?></p>
                                        <p class="mb-0">
                                            <strong>Status:</strong> 
                                            <span class="badge badge-<?php echo $jadwal->status === 'selesai' ? 'success' : ($jadwal->status === 'batal' ? 'danger' : 'info'); ?>">
                                                <?php echo ucfirst($jadwal->status); ?>
                                            </span>
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php endif; ?>

                <!-- Histori Bimbingan -->
                <div class="row">
                    <div class="col-12">
                        <h5 class="mb-3"><i class="fas fa-history"></i> Histori Bimbingan</h5>
                        <?php if (empty($histori_bimbingan)): ?>
                            <div class="alert alert-info">
                                <i class="fas fa-info-circle"></i> Belum ada histori bimbingan.
                            </div>
                        <?php else: ?>
                            <?php foreach ($histori_bimbingan as $histori): ?>
                                <div class="card card-outline card-secondary mb-3">
                                    <div class="card-header">
                                        <h6 class="mb-0">
                                            <i class="fas fa-user-tie"></i> 
                                            Pembimbing <?php echo $histori['bimbingan']->tipe_pembimbing; ?>: 
                                            <strong><?php echo $histori['bimbingan']->nama_dosen; ?></strong>
                                            <span class="badge badge-<?php echo $histori['bimbingan']->status === 'open' ? 'success' : 'secondary'; ?> float-right">
                                                <?php echo ucfirst($histori['bimbingan']->status); ?>
                                            </span>
                                        </h6>
                                    </div>
                                    <div class="card-body" style="max-height: 400px; overflow-y: auto;">
                                        <?php if (empty($histori['logs'])): ?>
                                            <p class="text-muted mb-0">Belum ada diskusi.</p>
                                        <?php else: ?>
                                            <?php foreach ($histori['logs'] as $log): ?>
                                                <?php $is_mahasiswa = ($log->role_pengguna === 'mahasiswa'); ?>
                                                <div class="media mb-3 <?php echo $is_mahasiswa ? 'bg-light p-2 rounded' : ''; ?>">
                                                    <div class="media-body">
                                                        <h6 class="mt-0 mb-1">
                                                            <?php echo $is_mahasiswa ? '<i class="fas fa-user-graduate"></i> Mahasiswa' : '<i class="fas fa-user-tie"></i> Dosen'; ?>
                                                            <small class="text-muted float-right"><?php echo date('d/m/Y H:i', strtotime($log->created_at)); ?></small>
                                                        </h6>
                                                        <p class="mb-0"><?php echo nl2br(htmlspecialchars($log->pesan)); ?></p>
                                                        <?php if ($is_mahasiswa && !empty($log->file_path)): ?>
                                                            <div class="mt-2 p-2 bg-white rounded border">
                                                                <div class="d-flex align-items-center">
                                                                    <i class="fas fa-file-alt fa-2x text-primary mr-2"></i>
                                                                    <div class="flex-grow-1">
                                                                        <strong><?php echo htmlspecialchars($log->file_name); ?></strong><br>
                                                                        <small class="text-muted">File berkas skripsi</small>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        <?php endif; ?>
                                                        <?php if (!$is_mahasiswa && !empty($log->status_revisi)): ?>
                                                            <div class="mt-2 p-2 rounded border <?php echo $log->status_revisi === 'acc' ? 'bg-success' : 'bg-warning'; ?>">
                                                                <div class="d-flex align-items-center mb-1">
                                                                    <i class="fas fa-<?php echo $log->status_revisi === 'acc' ? 'check-circle' : 'exclamation-triangle'; ?> mr-2 <?php echo $log->status_revisi === 'acc' ? 'text-white' : ''; ?>"></i>
                                                                    <strong class="<?php echo $log->status_revisi === 'acc' ? 'text-white' : ''; ?>">Status: <?php echo strtoupper($log->status_revisi); ?></strong>
                                                                </div>
                                                                <?php if (!empty($log->keterangan_revisi)): ?>
                                                                    <div class="mt-1 small <?php echo $log->status_revisi === 'acc' ? 'text-white' : ''; ?>">
                                                                        <strong>Keterangan:</strong><br>
                                                                        <?php echo nl2br(htmlspecialchars($log->keterangan_revisi)); ?>
                                                                    </div>
                                                                <?php endif; ?>
                                                            </div>
                                                        <?php endif; ?>
                                                    </div>
                                                </div>
                                            <?php endforeach; ?>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>


