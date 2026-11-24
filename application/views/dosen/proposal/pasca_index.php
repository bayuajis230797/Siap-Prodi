<div class="row">
    <div class="col-12">
        <div class="card card-primary">
            <div class="card-header"><h3 class="card-title"><i class="fas fa-comments"></i> Bimbingan Pasca Sidang Proposal TA</h3></div>
            <div class="card-body">
                <?php if ($this->session->flashdata('success')): ?><div class="alert alert-success"><?php echo $this->session->flashdata('success'); ?></div><?php endif; ?>
                <?php if ($this->session->flashdata('error')): ?><div class="alert alert-danger"><?php echo $this->session->flashdata('error'); ?></div><?php endif; ?>
                <div class="alert alert-info"><i class="fas fa-info-circle"></i> Thread dibuat otomatis setelah penguji pertama menyimpan nilai sidang.</div>
                <div class="table-responsive">
                    <table class="table table-bordered table-striped table-hover">
                        <thead>
                            <tr><th width="4%">No</th><th width="10%">NIM</th><th width="20%">Mahasiswa</th><th width="13%">Tanggal Sidang</th>
                                <th width="10%" class="text-center">Peran</th><th width="12%" class="text-center">Layak Klaim Saya</th><th width="8%" class="text-center">Status</th><th width="8%" class="text-center">Aksi</th></tr>
                        </thead>
                        <tbody>
                            <?php if (empty($list)): ?>
                                <tr><td colspan="8" class="text-center text-muted">Belum ada bimbingan pasca sidang.</td></tr>
                            <?php else: ?>
                                <?php $no = 1; foreach ($list as $b): ?>
                                    <tr>
                                        <td><?php echo $no++; ?></td>
                                        <td><strong><?php echo htmlspecialchars($b->nim ?? '-'); ?></strong></td>
                                        <td><?php echo htmlspecialchars($b->nama_mahasiswa ?? '-'); ?></td>
                                        <td><?php echo $b->tanggal_ujian ? date('d/m/Y', strtotime($b->tanggal_ujian)) : '-'; ?></td>
                                        <td class="text-center"><span class="badge badge-info"><?php echo $b->peran === 'penguji_1' ? 'Penguji 1' : 'Penguji 2'; ?></span></td>
                                        <td class="text-center"><?php echo !empty($b->layak_klaim) ? '<span class="badge badge-success">Ya</span>' : '<span class="badge badge-warning">Belum</span>'; ?></td>
                                        <td class="text-center"><span class="badge badge-<?php echo $b->status === 'open' ? 'success' : 'secondary'; ?>"><?php echo $b->status === 'open' ? 'Terbuka' : 'Ditutup'; ?></span></td>
                                        <td class="text-center"><a href="<?php echo site_url('Bimbingan_pasca_proposal/detail/' . $b->id); ?>" class="btn btn-sm btn-info"><i class="fas fa-eye"></i></a></td>
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
