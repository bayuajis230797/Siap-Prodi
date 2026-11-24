<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Tanda Terima Barang</h3>
                <div class="card-tools">
                    <a href="<?php echo site_url('Tanda_terima_dosen/scan'); ?>" class="btn btn-primary btn-sm">
                        <i class="fas fa-qrcode"></i> Scan QR
                    </a>
                </div>
            </div>
            <div class="card-body">
                <?php if (empty($distribusi)): ?>
                    <p class="text-muted text-center">Anda belum terdaftar pada distribusi barang.</p>
                <?php else: ?>
                    <?php foreach ($distribusi as $d): ?>
                        <div class="card card-outline card-secondary mb-3">
                            <div class="card-header"><strong><?php echo htmlspecialchars($d->nama_distribusi); ?></strong></div>
                            <div class="card-body p-0">
                                <table class="table table-sm mb-0">
                                    <?php foreach ($d->items as $it): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($it->nama_barang); ?></td>
                                            <td class="text-center">
                                                <?php if (!empty($it->waktu_terima)): ?>
                                                    <span class="badge badge-success">Sudah — <?php echo date('d/m/Y H:i', strtotime($it->waktu_terima)); ?></span>
                                                <?php else: ?>
                                                    <span class="badge badge-warning">Belum</span>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </table>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
