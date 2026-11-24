<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header"><h3 class="card-title">Bimbingan Pasca Sidang Skripsi (Penguji)</h3></div>
            <div class="card-body">
                <?php if ($this->session->flashdata('success')): ?><div class="alert alert-success"><?php echo $this->session->flashdata('success'); ?></div><?php endif; ?>
                <?php if ($this->session->flashdata('error')): ?><div class="alert alert-danger"><?php echo $this->session->flashdata('error'); ?></div><?php endif; ?>
                <p class="text-muted small">Thread bersama dengan penguji lain. Mahasiswa dapat klaim nilai setelah <strong>semua</strong> penguji klik Layak Klaim Nilai.</p>
                <div class="table-responsive">
                    <table class="table table-bordered table-striped table-sm">
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>NIM</th>
                                <th>Nama</th>
                                <th>Kelas</th>
                                <th>Peran Anda</th>
                                <th>Layak Klaim</th>
                                <th>Aktivitas</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($list)): ?>
                                <tr><td colspan="8" class="text-center text-muted">Belum ada jadwal sidang di mana Anda menjadi penguji.</td></tr>
                            <?php else: $no=1; foreach ($list as $row): ?>
                                <tr>
                                    <td><?php echo $no++; ?></td>
                                    <td><?php echo htmlspecialchars($row->nim); ?></td>
                                    <td><?php echo htmlspecialchars($row->nama_mahasiswa); ?></td>
                                    <td><?php echo htmlspecialchars($row->kelas ?: '-'); ?></td>
                                    <td><span class="badge badge-info"><?php echo htmlspecialchars($row->peran); ?></span></td>
                                    <td><?php echo !empty($row->layak_klaim) ? '<span class="badge badge-success">Ya</span>' : '<span class="badge badge-secondary">Belum</span>'; ?></td>
                                    <td><?php echo $row->last_activity ? date('d/m/Y H:i', strtotime($row->last_activity)) : '-'; ?></td>
                                    <td><a class="btn btn-sm btn-primary" href="<?php echo site_url('Bimbingan_pasca_skripsi/detail/' . $row->id); ?>">Buka</a></td>
                                </tr>
                            <?php endforeach; endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
