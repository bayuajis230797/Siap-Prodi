<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-edit"></i> Review Perubahan Judul Skripsi
                </h3>
                <div class="card-tools">
                    <?php if ($tahun_aktif): ?>
                        <span class="badge badge-info">
                            TA Aktif: <?php echo $tahun_aktif->tahun_akademik . ' ' . ucfirst($tahun_aktif->semester); ?>
                        </span>
                    <?php else: ?>
                        <span class="badge badge-warning">Belum ada tahun aktif</span>
                    <?php endif; ?>
                </div>
            </div>
            <div class="card-body">
                <?php if (empty($pengajuan_list)): ?>
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle"></i>
                        Belum ada pengajuan perubahan judul yang perlu divalidasi.
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped">
                            <thead>
                                <tr>
                                    <th width="5%">No</th>
                                    <th width="15%">NIM</th>
                                    <th width="20%">Nama Mahasiswa</th>
                                    <th width="10%">Kelas</th>
                                    <th width="25%">Judul Revisi</th>
                                    <th width="10%">File Bukti</th>
                                    <th width="15%">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php $no = 1; foreach ($pengajuan_list as $pengajuan): ?>
                                    <tr>
                                        <td><?php echo $no++; ?></td>
                                        <td><?php echo htmlspecialchars($pengajuan->nim); ?></td>
                                        <td><?php echo htmlspecialchars($pengajuan->nama_mahasiswa); ?></td>
                                        <td><span class="badge badge-info"><?php echo htmlspecialchars($pengajuan->kelas); ?></span></td>
                                        <td>
                                            <?php if (!empty($pengajuan->judul_revisi)): ?>
                                                <strong><?php echo htmlspecialchars($pengajuan->judul_revisi); ?></strong>
                                            <?php else: ?>
                                                <span class="text-muted">-</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php if (!empty($pengajuan->file_persetujuan_judul)): ?>
                                                <a href="<?php echo site_url('Skripsi_pengajuan/download_persetujuan/' . $pengajuan->id); ?>" 
                                                   class="btn btn-sm btn-info" target="_blank">
                                                    <i class="fas fa-download"></i> Download
                                                </a>
                                            <?php else: ?>
                                                <span class="text-muted">-</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <a href="<?php echo site_url('Skripsi_pengajuan/detail/' . $pengajuan->id); ?>" 
                                               class="btn btn-sm btn-primary">
                                                <i class="fas fa-eye"></i> Detail
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

