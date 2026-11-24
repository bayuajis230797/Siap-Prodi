<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header"><h3 class="card-title">Verifikasi Pengajuan PKM</h3></div>
            <div class="card-body">
                <?php if ($this->session->flashdata('success')): ?><div class="alert alert-success"><?php echo $this->session->flashdata('success'); ?></div><?php endif; ?>
                <?php if ($this->session->flashdata('error')): ?><div class="alert alert-danger"><?php echo $this->session->flashdata('error'); ?></div><?php endif; ?>

                <?php if (empty($periode_aktif_list)): ?>
                    <div class="alert alert-warning">
                        <i class="fas fa-exclamation-triangle"></i> Tidak ada periode PKM yang aktif saat ini.
                    </div>
                <?php else: ?>
                    <div class="alert alert-info py-2">
                        <i class="fas fa-calendar-check"></i>
                        Periode aktif:
                        <?php foreach ($periode_aktif_list as $i => $p): ?>
                            <?php if ($i > 0) echo ', '; ?>
                            <strong><?php echo htmlspecialchars($p->nama_periode); ?></strong>
                        <?php endforeach; ?>
                    </div>

                    <form method="get" class="form-inline mb-3">
                        <label class="mr-2">Status:</label>
                        <select name="status" class="form-control mr-2">
                            <option value="pending" <?php echo $status_filter === 'pending' ? 'selected' : ''; ?>>Pending</option>
                            <option value="disetujui" <?php echo $status_filter === 'disetujui' ? 'selected' : ''; ?>>Disetujui</option>
                            <option value="ditolak" <?php echo $status_filter === 'ditolak' ? 'selected' : ''; ?>>Ditolak</option>
                            <option value="all" <?php echo $status_filter === 'all' ? 'selected' : ''; ?>>Semua</option>
                        </select>
                        <button type="submit" class="btn btn-info btn-sm mr-3">Filter</button>
                        <a href="<?php echo site_url('Pkm_verifikasi/export_mahasiswa_pkm'); ?>" class="btn btn-success btn-sm mr-1"><i class="fas fa-file-csv"></i> Export Mahasiswa PKM</a>
                        <a href="<?php echo site_url('Pkm_verifikasi/export_mahasiswa_non_pkm'); ?>" class="btn btn-outline-secondary btn-sm"><i class="fas fa-file-csv"></i> Export Mahasiswa Non PKM</a>
                    </form>
                    <p class="text-muted small mb-3">Export berdasarkan periode PKM aktif. <strong>Mahasiswa PKM</strong> = terdaftar sebagai ketua/anggota. <strong>Non PKM</strong> = mahasiswa aktif yang belum terdaftar.</p>

                    <div class="table-responsive">
                        <table class="table table-bordered table-striped table-sm">
                            <thead>
                                <tr>
                                    <th>No</th>
                                    <th>Ketua</th>
                                    <th>Jenis</th>
                                    <th>Judul</th>
                                    <th>Anggota</th>
                                    <th>Pembimbing Request</th>
                                    <th>Status</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($pengajuan_list)): ?>
                                    <tr><td colspan="8" class="text-center text-muted">Tidak ada pengajuan pada periode aktif.</td></tr>
                                <?php else: $no = 1; foreach ($pengajuan_list as $row): ?>
                                    <tr>
                                        <td><?php echo $no++; ?></td>
                                        <td><code><?php echo htmlspecialchars($row->ketua_nim); ?></code><br><?php echo htmlspecialchars($row->ketua_nama); ?></td>
                                        <td><?php echo htmlspecialchars($row->nama_jenis); ?></td>
                                        <td><?php echo htmlspecialchars($row->judul_pkm); ?></td>
                                        <td><?php echo (int) $row->jumlah_anggota; ?> / <?php echo (int) $row->min_anggota; ?>–<?php echo (int) $row->max_anggota; ?></td>
                                        <td><?php echo htmlspecialchars($row->pembimbing_request_nama ?? '-'); ?></td>
                                        <td><span class="badge badge-<?php echo pkm_status_verifikasi_badge($row->status_verifikasi_sekprodi); ?>"><?php echo ucfirst($row->status_verifikasi_sekprodi); ?></span></td>
                                        <td><a href="<?php echo site_url('Pkm_verifikasi/detail/' . $row->id); ?>" class="btn btn-sm btn-primary">Detail</a></td>
                                    </tr>
                                <?php endforeach; endif; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
