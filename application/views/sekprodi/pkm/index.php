<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Periode PKM</h3>
                <div class="card-tools">
                    <a href="<?php echo site_url('Pkm_verifikasi'); ?>" class="btn btn-info btn-sm mr-1"><i class="fas fa-check-double"></i> Verifikasi</a>
                    <a href="<?php echo site_url('Pkm/create'); ?>" class="btn btn-primary btn-sm"><i class="fas fa-plus"></i> Tambah Periode</a>
                </div>
            </div>
            <div class="card-body">
                <?php if ($this->session->flashdata('success')): ?><div class="alert alert-success"><?php echo $this->session->flashdata('success'); ?></div><?php endif; ?>
                <?php if ($this->session->flashdata('error')): ?><div class="alert alert-danger"><?php echo $this->session->flashdata('error'); ?></div><?php endif; ?>

                <form method="get" class="mb-3">
                    <select name="status" class="form-control d-inline-block" style="width:auto">
                        <option value="">Semua status</option>
                        <option value="draft" <?php echo ($filter_status === 'draft') ? 'selected' : ''; ?>>Draft</option>
                        <option value="aktif" <?php echo ($filter_status === 'aktif') ? 'selected' : ''; ?>>Aktif</option>
                        <option value="selesai" <?php echo ($filter_status === 'selesai') ? 'selected' : ''; ?>>Selesai</option>
                        <option value="dibatalkan" <?php echo ($filter_status === 'dibatalkan') ? 'selected' : ''; ?>>Dibatalkan</option>
                    </select>
                    <button type="submit" class="btn btn-info btn-sm">Filter</button>
                </form>

                <div class="table-responsive">
                    <table class="table table-bordered table-striped">
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>Nama Periode</th>
                                <th>Deadline Pengajuan</th>
                                <th>Jenis Lomba</th>
                                <th>Pengajuan</th>
                                <th>Status</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($list)): ?>
                                <tr><td colspan="7" class="text-center text-muted">Belum ada periode PKM.</td></tr>
                            <?php else: $no = 1; foreach ($list as $row): ?>
                                <tr>
                                    <td><?php echo $no++; ?></td>
                                    <td><strong><?php echo htmlspecialchars($row->nama_periode); ?></strong></td>
                                    <td>
                                        <?php echo date('d/m/Y', strtotime($row->deadline_pengajuan)); ?>
                                        <?php if (!empty($row->deadline_lewat)): ?><span class="badge badge-danger">Lewat</span><?php endif; ?>
                                    </td>
                                    <td><?php echo (int) $row->jumlah_jenis; ?></td>
                                    <td><?php echo (int) $row->jumlah_pengajuan; ?></td>
                                    <td><span class="badge badge-<?php echo $row->status === 'aktif' ? 'success' : ($row->status === 'draft' ? 'secondary' : 'info'); ?>"><?php echo ucfirst($row->status); ?></span></td>
                                    <td>
                                        <a href="<?php echo site_url('Pkm/detail/' . $row->id); ?>" class="btn btn-sm btn-primary">Detail</a>
                                        <a href="<?php echo site_url('Pkm/edit/' . $row->id); ?>" class="btn btn-sm btn-warning">Edit</a>
                                    </td>
                                </tr>
                            <?php endforeach; endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
