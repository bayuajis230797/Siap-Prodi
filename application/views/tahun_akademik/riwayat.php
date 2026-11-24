<div class="row">
    <div class="col-12">
        <?php if ($this->session->flashdata('success')): ?>
            <div class="alert alert-success alert-dismissible fade show"><i class="fas fa-check-circle"></i> <?php echo $this->session->flashdata('success'); ?>
                <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button></div>
        <?php endif; ?>
        <?php if ($this->session->flashdata('error')): ?>
            <div class="alert alert-danger alert-dismissible fade show"><i class="fas fa-exclamation-circle"></i> <?php echo $this->session->flashdata('error'); ?>
                <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button></div>
        <?php endif; ?>

        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Riwayat Kenaikan Kelas</h3>
                <div class="card-tools"><a href="<?php echo site_url('Tahun_akademik'); ?>" class="btn btn-secondary btn-sm"><i class="fas fa-arrow-left"></i> Kembali</a></div>
            </div>
            <div class="card-body table-responsive p-0">
                <table class="table table-striped mb-0">
                    <thead>
                        <tr><th>#</th><th>Waktu</th><th>Dari</th><th>Ke</th><th>Arah</th><th class="text-right">Mahasiswa Berubah</th><th>Oleh</th><th>Status</th><th>Aksi</th></tr>
                    </thead>
                    <tbody>
                    <?php if (empty($batch_list)): ?>
                        <tr><td colspan="9" class="text-center text-muted">Belum ada riwayat kenaikan kelas.</td></tr>
                    <?php else: foreach ($batch_list as $b): ?>
                        <tr>
                            <td><?php echo (int) $b->id; ?></td>
                            <td><?php echo date('d/m/Y H:i', strtotime($b->dibuat_pada)); ?></td>
                            <td><?php echo $b->dari_ta ? htmlspecialchars($b->dari_ta . ' ' . ucfirst($b->dari_sem)) : '-'; ?></td>
                            <td><?php echo $b->ke_ta ? htmlspecialchars($b->ke_ta . ' ' . ucfirst($b->ke_sem)) : '-'; ?></td>
                            <td><span class="badge badge-<?php echo $b->arah === 'mundur' ? 'warning' : 'info'; ?>"><?php echo htmlspecialchars($b->arah); ?></span></td>
                            <td class="text-right"><?php echo (int) $b->jumlah_berubah; ?></td>
                            <td><?php echo htmlspecialchars((string) $b->dibuat_oleh ?: '-'); ?></td>
                            <td>
                                <?php if ($b->status === 'diterapkan'): ?><span class="badge badge-success">Diterapkan</span>
                                <?php else: ?><span class="badge badge-secondary">Dipulihkan<?php echo $b->dipulihkan_pada ? ' ' . date('d/m/Y H:i', strtotime($b->dipulihkan_pada)) : ''; ?></span><?php endif; ?>
                            </td>
                            <td>
                                <a href="<?php echo site_url('Tahun_akademik/riwayat_detail/' . $b->id); ?>" class="btn btn-info btn-sm"><i class="fas fa-eye"></i> Detail</a>
                                <?php if ((int) $b->id === (int) $batch_terakhir): ?>
                                    <?php echo form_open('Tahun_akademik/pulihkan/' . $b->id, array('class' => 'd-inline', 'onsubmit' => "return confirm('Pulihkan batch ini? Kelas, semester, dan status mahasiswa dikembalikan ke kondisi sebelumnya, dan tahun akademik aktif dikembalikan ke tahun akademik sebelum batch.');")); ?>
                                        <button type="submit" class="btn btn-warning btn-sm"><i class="fas fa-undo"></i> Pulihkan</button>
                                    <?php echo form_close(); ?>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; endif; ?>
                    </tbody>
                </table>
            </div>
            <div class="card-footer text-muted small">Hanya batch terakhir yang berstatus Diterapkan yang dapat dipulihkan. Baris yang sudah diubah manual sesudah batch dilewati agar tidak tertimpa.</div>
        </div>
    </div>
</div>
