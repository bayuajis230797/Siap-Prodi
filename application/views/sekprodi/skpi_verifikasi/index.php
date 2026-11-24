<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Verifikasi Pengajuan SKPI</h3>
                <div class="card-tools">
                    <a href="<?php echo site_url('Skpi'); ?>" class="btn btn-secondary btn-sm">Kelola Periode</a>
                </div>
            </div>
            <div class="card-body">
                <?php if ($this->session->flashdata('success')): ?><div class="alert alert-success"><?php echo $this->session->flashdata('success'); ?></div><?php endif; ?>
                <?php if ($this->session->flashdata('error')): ?><div class="alert alert-danger"><?php echo $this->session->flashdata('error'); ?></div><?php endif; ?>

                <form method="get" class="mb-3">
                    <div class="form-row">
                        <div class="col-md-4">
                            <label>Periode SKPI</label>
                            <select name="id_periode" class="form-control" required onchange="this.form.submit()">
                                <option value="">— Pilih periode —</option>
                                <?php foreach ($periode_list as $p): ?>
                                    <option value="<?php echo (int) $p->id; ?>" <?php echo ($id_periode == $p->id) ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($p->nama_periode); ?> (<?php echo ucfirst($p->status); ?>)
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <?php if ($id_periode > 0): ?>
                        <div class="col-md-3">
                            <label>Filter Kelas</label>
                            <input type="text" name="kelas" class="form-control" value="<?php echo htmlspecialchars($kelas_filter ?? ''); ?>" placeholder="Contoh: TI-3A">
                        </div>
                        <div class="col-md-2 d-flex align-items-end">
                            <button type="submit" class="btn btn-info">Filter</button>
                        </div>
                        <div class="col-md-3 d-flex align-items-end">
                            <a href="<?php echo site_url('Skpi_verifikasi/export_csv?id_periode=' . $id_periode . ($kelas_filter ? '&kelas=' . urlencode($kelas_filter) : '')); ?>" class="btn btn-success">
                                <i class="fas fa-file-csv"></i> Export CSV
                            </a>
                        </div>
                        <?php endif; ?>
                    </div>
                </form>

                <?php if ($id_periode > 0 && $periode): ?>
                    <p class="text-muted">Deadline: <strong><?php echo date('d/m/Y', strtotime($periode->deadline_pengumpulan)); ?></strong></p>
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped">
                            <thead>
                                <tr>
                                    <th>No</th>
                                    <th>NIM</th>
                                    <th>Nama</th>
                                    <th>Kelas</th>
                                    <th>Total Upload</th>
                                    <th>Pending</th>
                                    <th>Disetujui</th>
                                    <th>Ditolak</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($peserta_list)): ?>
                                    <tr><td colspan="9" class="text-center text-muted">Tidak ada peserta.</td></tr>
                                <?php else: $no = 1; foreach ($peserta_list as $p): ?>
                                    <tr>
                                        <td><?php echo $no++; ?></td>
                                        <td><code><?php echo htmlspecialchars($p->nim); ?></code></td>
                                        <td><?php echo htmlspecialchars($p->nama_mahasiswa); ?></td>
                                        <td><?php echo htmlspecialchars($p->kelas); ?></td>
                                        <td><?php echo (int) $p->total_upload; ?></td>
                                        <td><?php echo (int) $p->pending; ?></td>
                                        <td><?php echo (int) $p->disetujui; ?></td>
                                        <td><?php echo (int) $p->ditolak; ?></td>
                                        <td>
                                            <a href="<?php echo site_url('Skpi_verifikasi/detail_mahasiswa/' . $id_periode . '/' . $p->id_mahasiswa); ?>" class="btn btn-sm btn-primary">Verifikasi</a>
                                        </td>
                                    </tr>
                                <?php endforeach; endif; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <p class="text-muted">Pilih periode SKPI untuk melihat daftar mahasiswa.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
