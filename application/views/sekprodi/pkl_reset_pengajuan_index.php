<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Reset Pengajuan Judul PKL</h3>
            </div>
            <div class="card-body">
                <?php if ($this->session->flashdata('success')): ?>
                    <div class="alert alert-success alert-dismissible fade show">
                        <i class="fas fa-check-circle"></i> <?php echo $this->session->flashdata('success'); ?>
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                    </div>
                <?php endif; ?>

                <?php if ($this->session->flashdata('error')): ?>
                    <div class="alert alert-danger alert-dismissible fade show">
                        <i class="fas fa-exclamation-circle"></i> <?php echo $this->session->flashdata('error'); ?>
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                    </div>
                <?php endif; ?>

                <?php if ($tahun_aktif): ?>
                    <div class="alert alert-info mb-3">
                        <i class="fas fa-info-circle"></i>
                        <strong>Tahun Akademik Aktif:</strong> <?php echo $tahun_aktif->tahun_akademik . ' ' . ucfirst($tahun_aktif->semester); ?>
                    </div>
                <?php endif; ?>

                <div class="alert alert-warning">
                    <i class="fas fa-exclamation-triangle"></i>
                    Fitur ini mengembalikan pengajuan judul ke kondisi awal agar mahasiswa dapat mengajukan ulang
                    (misalnya setelah penambahan field baru). Hanya pengajuan dengan status <strong>Pending</strong> atau <strong>Revisi</strong> yang dapat di-reset.
                    Judul yang sudah <strong>Disetujui (ACC)</strong> pembimbing tidak ditampilkan di sini.
                </div>

                <div class="table-responsive">
                    <table class="table table-bordered table-striped table-hover">
                        <thead>
                            <tr>
                                <th width="5%">No</th>
                                <th width="12%">NIM</th>
                                <th>Nama Mahasiswa</th>
                                <th width="8%" class="text-center">Kelas</th>
                                <th>Judul PKL</th>
                                <th width="12%" class="text-center">Status Review</th>
                                <th width="10%" class="text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($pengajuan_list)): ?>
                                <tr>
                                    <td colspan="7" class="text-center text-muted">Tidak ada pengajuan judul yang dapat di-reset.</td>
                                </tr>
                            <?php else: ?>
                                <?php $no = 1; foreach ($pengajuan_list as $p): ?>
                                    <?php
                                    $status_acc = $p->status_acc_judul ?? 'pending';
                                    $badge_class = 'warning';
                                    $badge_text = 'Pending';
                                    if ($status_acc === 'revisi') {
                                        $badge_class = 'danger';
                                        $badge_text = 'Revisi';
                                    }
                                    ?>
                                    <tr>
                                        <td><?php echo $no++; ?></td>
                                        <td><strong><?php echo htmlspecialchars($p->nim); ?></strong></td>
                                        <td><?php echo htmlspecialchars($p->nama_mahasiswa); ?></td>
                                        <td class="text-center"><span class="badge badge-info"><?php echo htmlspecialchars($p->kelas); ?></span></td>
                                        <td><?php echo htmlspecialchars($p->judul_pkl); ?></td>
                                        <td class="text-center"><span class="badge badge-<?php echo $badge_class; ?>"><?php echo $badge_text; ?></span></td>
                                        <td class="text-center">
                                            <a href="<?php echo site_url('Pkl_reset_pengajuan_sekprodi/reset/' . $p->id); ?>"
                                               class="btn btn-warning btn-sm"
                                               onclick="return confirm('Reset pengajuan judul PKL mahasiswa <?php echo htmlspecialchars($p->nama_mahasiswa, ENT_QUOTES); ?>?\n\nMahasiswa harus mengajukan judul ulang dari awal.');">
                                                <i class="fas fa-undo"></i> Reset
                                            </a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
