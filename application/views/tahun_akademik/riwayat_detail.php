<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Batch #<?php echo (int) $batch->id; ?>:
                    <?php echo $batch->dari_ta ? htmlspecialchars($batch->dari_ta . ' ' . ucfirst($batch->dari_sem)) : '-'; ?>
                    &rarr; <?php echo $batch->ke_ta ? htmlspecialchars($batch->ke_ta . ' ' . ucfirst($batch->ke_sem)) : '-'; ?>
                    (<?php echo htmlspecialchars($batch->arah); ?>, <?php echo (int) $batch->jumlah_berubah; ?> mahasiswa, <?php echo htmlspecialchars($batch->status); ?>)</h3>
                <div class="card-tools"><a href="<?php echo site_url('Tahun_akademik/riwayat'); ?>" class="btn btn-secondary btn-sm"><i class="fas fa-arrow-left"></i> Kembali</a></div>
            </div>
            <div class="card-body table-responsive p-0">
                <table class="table table-sm table-striped mb-0">
                    <thead>
                        <tr><th>NIM</th><th>Nama</th><th>Angkatan</th><th>Kelas</th><th>Semester Aktif</th><th>Semester Riil</th><th>Status</th><th>Keterangan</th></tr>
                    </thead>
                    <tbody>
                    <?php if (empty($detail)): ?>
                        <tr><td colspan="8" class="text-center text-muted">Tidak ada detail.</td></tr>
                    <?php else: foreach ($detail as $d): ?>
                        <tr>
                            <td><?php echo htmlspecialchars((string) $d->nim); ?></td>
                            <td><?php echo htmlspecialchars((string) $d->nama_mahasiswa); ?></td>
                            <td><?php echo (int) $d->angkatan; ?></td>
                            <td><?php echo htmlspecialchars((string) $d->kelas_lama ?: '-'); ?> &rarr; <strong><?php echo htmlspecialchars((string) $d->kelas_baru); ?></strong></td>
                            <td><?php echo (int) $d->semester_aktif_lama; ?> &rarr; <?php echo (int) $d->semester_aktif_baru; ?></td>
                            <td><?php echo $d->semester_riil_lama === null ? '-' : (int) $d->semester_riil_lama; ?> &rarr; <?php echo (int) $d->semester_riil_baru; ?></td>
                            <td><?php echo htmlspecialchars((string) $d->status_lama); ?> &rarr; <?php echo htmlspecialchars((string) $d->status_baru); ?></td>
                            <td><?php echo htmlspecialchars((string) $d->keterangan); ?></td>
                        </tr>
                    <?php endforeach; endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
