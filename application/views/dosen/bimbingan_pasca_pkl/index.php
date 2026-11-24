<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header"><h3 class="card-title">Bimbingan Pasca Sidang PKL (Penguji)</h3></div>
            <div class="card-body">
                <?php if ($this->session->flashdata('success')): ?><div class="alert alert-success"><?php echo $this->session->flashdata('success'); ?></div><?php endif; ?>
                <?php if ($this->session->flashdata('error')): ?><div class="alert alert-danger"><?php echo $this->session->flashdata('error'); ?></div><?php endif; ?>
                <div class="table-responsive">
                    <table class="table table-bordered table-striped table-sm">
                        <thead><tr><th>No</th><th>NIM</th><th>Nama</th><th>Kelas</th><th>Layak Klaim</th><th>Aktivitas</th><th></th></tr></thead>
                        <tbody>
                            <?php if (empty($list)): ?>
                                <tr><td colspan="7" class="text-center text-muted">Belum ada jadwal di mana Anda menjadi penguji.</td></tr>
                            <?php else: $no=1; foreach ($list as $row): ?>
                                <tr>
                                    <td><?php echo $no++; ?></td>
                                    <td><?php echo htmlspecialchars($row->nim); ?></td>
                                    <td><?php echo htmlspecialchars($row->nama_mahasiswa); ?></td>
                                    <td><?php echo htmlspecialchars($row->kelas ?: '-'); ?></td>
                                    <td><?php echo !empty($row->layak_klaim) ? '<span class="badge badge-success">Ya</span>' : '<span class="badge badge-secondary">Belum</span>'; ?></td>
                                    <td><?php echo $row->last_activity ? date('d/m/Y H:i', strtotime($row->last_activity)) : '-'; ?></td>
                                    <td><a class="btn btn-sm btn-primary" href="<?php echo site_url('Bimbingan_pasca_pkl/detail/' . $row->id); ?>">Buka</a></td>
                                </tr>
                            <?php endforeach; endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
