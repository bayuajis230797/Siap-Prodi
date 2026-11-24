<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header"><h3 class="card-title">Verifikasi Klaim Nilai Proposal TA</h3></div>
            <div class="card-body">
                <?php if ($this->session->flashdata('success')): ?><div class="alert alert-success alert-dismissible fade show"><i class="fas fa-check-circle"></i> <?php echo $this->session->flashdata('success'); ?>
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button></div><?php endif; ?>
                <?php if ($this->session->flashdata('error')): ?><div class="alert alert-danger alert-dismissible fade show"><i class="fas fa-exclamation-circle"></i> <?php echo $this->session->flashdata('error'); ?>
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button></div><?php endif; ?>
                <?php if ($tahun_aktif): ?><div class="alert alert-info"><i class="fas fa-info-circle"></i> <strong>Tahun Akademik:</strong> <?php echo htmlspecialchars($tahun_aktif->tahun_akademik . ' ' . ucfirst($tahun_aktif->semester)); ?></div><?php endif; ?>

                <form method="get" action="<?php echo site_url('Proposal_verifikasi_klaim/index'); ?>" class="form-inline mb-3">
                    <select name="status" class="form-control form-control-sm mr-2">
                        <option value="">Semua Status</option>
                        <?php foreach (array('menunggu' => 'Menunggu', 'disetujui' => 'Disetujui', 'ditolak' => 'Ditolak') as $k => $v): ?>
                            <option value="<?php echo $k; ?>" <?php echo $status_filter === $k ? 'selected' : ''; ?>><?php echo $v; ?></option>
                        <?php endforeach; ?>
                    </select>
                    <button type="submit" class="btn btn-primary btn-sm mr-1"><i class="fas fa-filter"></i> Filter</button>
                    <a href="<?php echo site_url('Proposal_verifikasi_klaim'); ?>" class="btn btn-secondary btn-sm"><i class="fas fa-times"></i> Reset</a>
                </form>

                <div class="table-responsive">
                    <table class="table table-bordered table-striped table-hover">
                        <thead>
                            <tr><th width="4%">No</th><th width="10%">NIM</th><th width="20%">Nama</th><th width="8%">Kelas</th><th>Judul</th>
                                <th width="9%" class="text-center">Nilai Akhir</th><th width="11%" class="text-center">Status</th><th width="8%" class="text-center">Aksi</th></tr>
                        </thead>
                        <tbody>
                            <?php if (empty($klaim_list)): ?>
                                <tr><td colspan="8" class="text-center text-muted">Belum ada klaim nilai.</td></tr>
                            <?php else: ?>
                                <?php $no = (max(1, (int) $this->uri->segment(3)) - 1) * $per_page + 1; foreach ($klaim_list as $k): ?>
                                    <tr>
                                        <td><?php echo $no++; ?></td>
                                        <td><strong><?php echo htmlspecialchars($k->nim ?? '-'); ?></strong></td>
                                        <td><?php echo htmlspecialchars($k->nama_mahasiswa ?? '-'); ?></td>
                                        <td><?php echo htmlspecialchars($k->kelas ?? '-'); ?></td>
                                        <td><small><?php echo htmlspecialchars($k->judul_proposal ?? '-'); ?></small></td>
                                        <td class="text-center"><?php echo $k->nilai_akhir !== null ? number_format((float) $k->nilai_akhir, 2, ',', '.') . ' (' . htmlspecialchars((string) $k->grade) . ')' : '-'; ?></td>
                                        <td class="text-center"><?php echo proposal_status_badge($k->status); ?></td>
                                        <td class="text-center"><a href="<?php echo site_url('Proposal_verifikasi_klaim/detail/' . $k->id); ?>" class="btn btn-sm btn-info"><i class="fas fa-eye"></i></a></td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
                <?php if (!empty($pagination)): ?>
                    <div class="row mt-3"><div class="col-md-6"><p class="text-muted">Menampilkan <?php echo count($klaim_list); ?> dari <?php echo $total_rows; ?> data</p></div>
                        <div class="col-md-6"><?php echo $pagination; ?></div></div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
