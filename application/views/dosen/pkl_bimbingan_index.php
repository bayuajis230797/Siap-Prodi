<div class="row">
    <div class="col-12">
        <?php if ($this->session->flashdata('success')): ?>
            <div class="alert alert-success alert-dismissible fade show">
                <i class="fas fa-check-circle"></i> <?php echo $this->session->flashdata('success'); ?>
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
        <?php endif; ?>

        <?php if ($this->session->flashdata('error')): ?>
            <div class="alert alert-danger alert-dismissible fade show">
                <i class="fas fa-exclamation-circle"></i> <?php echo $this->session->flashdata('error'); ?>
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
        <?php endif; ?>

        <div class="card">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-comments"></i> Bimbingan PKL
                </h3>
                <div class="card-tools">
                    <div class="btn-group">
                        <a href="<?php echo site_url('Bimbingan_pkl'); ?>" 
                           class="btn btn-sm <?php echo !$status_filter ? 'btn-primary' : 'btn-secondary'; ?>">
                            Semua
                        </a>
                        <a href="<?php echo site_url('Bimbingan_pkl?status=open'); ?>" 
                           class="btn btn-sm <?php echo $status_filter === 'open' ? 'btn-primary' : 'btn-secondary'; ?>">
                            Open
                        </a>
                        <a href="<?php echo site_url('Bimbingan_pkl?status=closed'); ?>" 
                           class="btn btn-sm <?php echo $status_filter === 'closed' ? 'btn-primary' : 'btn-secondary'; ?>">
                            Closed
                        </a>
                    </div>
                </div>
            </div>
            <div class="card-body">
                <?php if (empty($bimbingan_list)): ?>
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle"></i> Belum ada bimbingan PKL.
                    </div>
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
                                    <th width="16%" class="text-center">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php $no = 1; foreach ($bimbingan_list as $bimbingan): ?>
                                    <tr>
                                        <td><?php echo $no++; ?></td>
                                        <td>
                                            <strong><?php echo htmlspecialchars($bimbingan->nama_mahasiswa); ?></strong>
                                            <br><small class="text-muted">NIM: <?php echo htmlspecialchars($bimbingan->nim); ?></small>
                                            <?php if ($bimbingan->kelas): ?>
                                                <br><small class="text-muted">Kelas: <?php echo htmlspecialchars($bimbingan->kelas); ?></small>
                                            <?php endif; ?>
                                        </td>
                                        <td><?php echo htmlspecialchars($bimbingan->judul_topik); ?></td>
                                        <td class="text-center">
                                            <?php if (($bimbingan->status ?? '') === 'open'): ?>
                                                <span class="badge badge-success">Open</span>
                                            <?php else: ?>
                                                <span class="badge badge-secondary">Closed</span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="text-center">
                                            <span class="badge badge-<?php echo htmlspecialchars($bimbingan->workflow_status_class ?? 'secondary'); ?>">
                                                <?php echo htmlspecialchars($bimbingan->workflow_status_label ?? 'Status Tidak Diketahui'); ?>
                                            </span>
                                        </td>
                                        <td class="text-center">
                                            <?php echo date('d/m/Y H:i', strtotime($bimbingan->last_activity)); ?>
                                        </td>
                                        <td class="text-center">
                                            <a href="<?php echo site_url('Bimbingan_pkl/detail/' . $bimbingan->id); ?>" 
                                               class="btn btn-info btn-sm">
                                                <i class="fas fa-eye"></i> Review
                                            </a>
                                            <?php echo form_open('Bimbingan_pkl/hapus/' . $bimbingan->id, array(
                                                'method' => 'post',
                                                'style' => 'display:inline-block;',
                                                'onsubmit' => "return confirm('Hapus data bimbingan ini? Tindakan tidak dapat dibatalkan.');"
                                            )); ?>
                                                <button type="submit" class="btn btn-danger btn-sm" title="Hapus">
                                                    <i class="fas fa-trash"></i> Hapus
                                                </button>
                                            <?php echo form_close(); ?>
                                        </td>
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

