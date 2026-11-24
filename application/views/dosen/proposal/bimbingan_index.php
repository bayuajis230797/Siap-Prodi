<div class="row">
    <div class="col-12">
        <div class="card card-primary">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-comments"></i> Bimbingan Proposal TA</h3>
            </div>
            <div class="card-body">
                <?php if ($this->session->flashdata('success')): ?>
                    <div class="alert alert-success alert-dismissible"><button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button><?php echo $this->session->flashdata('success'); ?></div>
                <?php endif; ?>
                <?php if ($this->session->flashdata('error')): ?>
                    <div class="alert alert-danger alert-dismissible"><button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button><?php echo $this->session->flashdata('error'); ?></div>
                <?php endif; ?>

                <div class="mb-3">
                    <a href="<?php echo site_url('Bimbingan_proposal'); ?>" class="btn btn-sm btn-<?php echo !$status_filter ? 'primary' : 'outline-primary'; ?>">Semua</a>
                    <a href="<?php echo site_url('Bimbingan_proposal?status=open'); ?>" class="btn btn-sm btn-<?php echo $status_filter === 'open' ? 'primary' : 'outline-primary'; ?>">Terbuka</a>
                    <a href="<?php echo site_url('Bimbingan_proposal?status=closed'); ?>" class="btn btn-sm btn-<?php echo $status_filter === 'closed' ? 'primary' : 'outline-primary'; ?>">Ditutup</a>
                </div>

                <div class="table-responsive">
                    <table class="table table-bordered table-striped table-hover">
                        <thead>
                            <tr>
                                <th width="5%">No</th>
                                <th width="12%">NIM</th>
                                <th width="20%">Mahasiswa</th>
                                <th>Topik</th>
                                <th width="20%" class="text-center">Status</th>
                                <th width="15%">Aktivitas Terakhir</th>
                                <th width="8%" class="text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($bimbingan_list)): ?>
                                <tr><td colspan="7" class="text-center text-muted">Belum ada topik bimbingan.</td></tr>
                            <?php else: ?>
                                <?php $no = 1; foreach ($bimbingan_list as $b): ?>
                                    <tr>
                                        <td><?php echo $no++; ?></td>
                                        <td><strong><?php echo htmlspecialchars($b->nim ?? '-'); ?></strong></td>
                                        <td><?php echo htmlspecialchars($b->nama_mahasiswa ?? '-'); ?></td>
                                        <td><?php echo htmlspecialchars($b->judul_topik); ?></td>
                                        <td class="text-center"><span class="badge badge-<?php echo $b->workflow_status_class; ?>"><?php echo htmlspecialchars($b->workflow_status_label); ?></span></td>
                                        <td><?php echo date('d/m/Y H:i', strtotime($b->last_activity)); ?></td>
                                        <td class="text-center">
                                            <a href="<?php echo site_url('Bimbingan_proposal/detail/' . $b->id); ?>" class="btn btn-sm btn-info" title="Review"><i class="fas fa-eye"></i></a>
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
