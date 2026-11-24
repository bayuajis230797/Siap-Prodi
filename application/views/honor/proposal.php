<?php $rp = function ($n) { return 'Rp ' . number_format((float) $n, 0, ',', '.'); }; ?>
<div class="row">
    <div class="col-12">
        <div class="card card-primary card-outline">
            <div class="card-header">
                <h3 class="card-title">Honor Proposal TA</h3>
                <div class="card-tools">
                    <a href="<?php echo site_url('Honor_proposal/export_csv'); ?>" class="btn btn-info btn-sm"><i class="fas fa-file-excel"></i> Unduh CSV</a>
                </div>
            </div>
            <div class="card-body">
                <?php if ($this->session->flashdata('error')): ?><div class="alert alert-danger"><?php echo $this->session->flashdata('error'); ?></div><?php endif; ?>
                <?php if ($tahun_aktif): ?>
                    <div class="alert alert-info"><i class="fas fa-info-circle"></i> <strong>Tahun Akademik:</strong> <?php echo htmlspecialchars($tahun_aktif->tahun_akademik . ' ' . ucfirst($tahun_aktif->semester)); ?></div>
                <?php else: ?>
                    <div class="alert alert-warning"><i class="fas fa-exclamation-triangle"></i> Belum ada tahun akademik aktif.</div>
                <?php endif; ?>

                <div class="card mb-3">
                    <div class="card-header bg-info"><h5 class="mb-0"><i class="fas fa-info-circle"></i> Besaran Honor</h5></div>
                    <div class="card-body">
                        <ul class="mb-1">
                            <li>Pembimbing: <?php echo $rp($besaran['pembimbing']); ?> per mahasiswa</li>
                            <li>Penguji 1: <?php echo $rp($besaran['penguji_1']); ?> per sidang</li>
                            <li>Penguji 2: <?php echo $rp($besaran['penguji_2']); ?> per sidang</li>
                        </ul>
                        <small class="text-muted">Diatur di <a href="<?php echo site_url('Honor_setup'); ?>">Pengaturan Honor</a>. Jadwal berstatus batal tidak dihitung.</small>
                        <?php if ($besaran['pembimbing'] == 0 && $besaran['penguji_1'] == 0 && $besaran['penguji_2'] == 0): ?>
                            <div class="alert alert-warning mt-2 mb-0">Besaran honor Proposal TA masih 0. Isi dulu di Pengaturan Honor.</div>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="table table-bordered table-striped table-hover">
                        <thead>
                            <tr>
                                <th rowspan="2" class="align-middle" width="4%">No</th><th rowspan="2" class="align-middle">Dosen</th>
                                <th colspan="2" class="text-center">Pembimbing</th><th colspan="2" class="text-center">Penguji 1</th><th colspan="2" class="text-center">Penguji 2</th>
                                <th rowspan="2" class="align-middle text-right" width="13%">Total</th><th rowspan="2" class="align-middle" width="7%"></th>
                            </tr>
                            <tr>
                                <th class="text-center" width="6%">Jml</th><th class="text-right" width="11%">Honor</th>
                                <th class="text-center" width="6%">Jml</th><th class="text-right" width="11%">Honor</th>
                                <th class="text-center" width="6%">Jml</th><th class="text-right" width="11%">Honor</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($rekap)): ?>
                                <tr><td colspan="9" class="text-center text-muted">Belum ada data bimbingan atau sidang.</td></tr>
                            <?php else: ?>
                                <?php $no = 1; foreach ($rekap as $r): ?>
                                    <tr>
                                        <td><?php echo $no++; ?></td>
                                        <td><strong><?php echo htmlspecialchars($r->nama_dosen); ?></strong><br><small class="text-muted"><?php echo htmlspecialchars((string) $r->nidn); ?></small></td>
                                        <td class="text-center"><?php echo $r->jumlah_bimbingan; ?></td><td class="text-right"><?php echo $rp($r->honor_pembimbing); ?></td>
                                        <td class="text-center"><?php echo $r->jumlah_penguji_1; ?></td><td class="text-right"><?php echo $rp($r->honor_penguji_1); ?></td>
                                        <td class="text-center"><?php echo $r->jumlah_penguji_2; ?></td><td class="text-right"><?php echo $rp($r->honor_penguji_2); ?></td>
                                        <td class="text-right"><strong><?php echo $rp($r->total_honor); ?></strong></td>
                                        <td class="text-center"><a href="<?php echo site_url('Honor_proposal/detail/' . $r->id_dosen); ?>" class="btn btn-sm btn-info" title="Rincian"><i class="fas fa-eye"></i></a></td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                        <?php if (!empty($rekap)): ?>
                            <tfoot><tr class="table-success"><th colspan="7" class="text-right">Total Honor Proposal TA</th><th class="text-right"><?php echo $rp($total); ?></th><th></th></tr></tfoot>
                        <?php endif; ?>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
