<div class="row">
    <div class="col-12">
        <?php if ($this->session->flashdata('success')): ?><div class="alert alert-success alert-dismissible fade show"><?php echo $this->session->flashdata('success'); ?><button type="button" class="close" data-dismiss="alert"><span>&times;</span></button></div><?php endif; ?>
        <?php if ($this->session->flashdata('error')): ?><div class="alert alert-danger alert-dismissible fade show"><?php echo $this->session->flashdata('error'); ?><button type="button" class="close" data-dismiss="alert"><span>&times;</span></button></div><?php endif; ?>

        <div class="card">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-comments"></i> Bimbingan PKM</h3>
                <div class="card-tools">
                    <div class="btn-group">
                        <a href="<?php echo site_url('Bimbingan_pkm'); ?>" class="btn btn-sm <?php echo !$status_filter ? 'btn-primary' : 'btn-secondary'; ?>">Semua</a>
                        <a href="<?php echo site_url('Bimbingan_pkm?status=open'); ?>" class="btn btn-sm <?php echo $status_filter === 'open' ? 'btn-primary' : 'btn-secondary'; ?>">Open</a>
                        <a href="<?php echo site_url('Bimbingan_pkm?status=closed'); ?>" class="btn btn-sm <?php echo $status_filter === 'closed' ? 'btn-primary' : 'btn-secondary'; ?>">Closed</a>
                    </div>
                </div>
            </div>
            <div class="card-body">
                <?php if (empty($bimbingan_list)): ?>
                    <div class="alert alert-info">Belum ada bimbingan PKM.</div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped table-hover">
                            <thead>
                                <tr>
                                    <th width="5%">No</th>
                                    <th>Mahasiswa</th>
                                    <th>Topik</th>
                                    <th width="10%" class="text-center">Status</th>
                                    <th width="16%" class="text-center">Status Proses</th>
                                    <th width="12%" class="text-center">Last Activity</th>
                                    <th width="12%" class="text-center">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php $no = 1; foreach ($bimbingan_list as $b): ?>
                                    <tr>
                                        <td><?php echo $no++; ?></td>
                                        <td><strong><?php echo htmlspecialchars($b->nama_mahasiswa); ?></strong><br><small class="text-muted">NIM: <?php echo htmlspecialchars($b->nim); ?></small></td>
                                        <td><?php echo htmlspecialchars($b->judul_topik); ?></td>
                                        <td class="text-center"><span class="badge badge-<?php echo ($b->status ?? '') === 'open' ? 'success' : 'secondary'; ?>"><?php echo ucfirst($b->status); ?></span></td>
                                        <td class="text-center"><span class="badge badge-<?php echo htmlspecialchars($b->workflow_status_class ?? 'secondary'); ?>"><?php echo htmlspecialchars($b->workflow_status_label ?? '-'); ?></span></td>
                                        <td class="text-center"><?php echo date('d/m/Y H:i', strtotime($b->last_activity)); ?></td>
                                        <td class="text-center"><a href="<?php echo site_url('Bimbingan_pkm/detail/' . $b->id); ?>" class="btn btn-info btn-sm"><i class="fas fa-eye"></i> Review</a></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
