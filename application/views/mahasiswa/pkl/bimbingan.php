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
                    <a href="<?php echo site_url('Pkl/bimbingan_create'); ?>" class="btn btn-primary btn-sm">
                        <i class="fas fa-plus"></i> Buat Topik Baru
                    </a>
                    <?php if (!empty($bimbingan_list)): ?>
                        <a href="<?php echo site_url('Pkl/cetak_histori_bimbingan'); ?>" class="btn btn-success btn-sm" target="_blank">
                            <i class="fas fa-file-pdf"></i> Cetak Histori PDF
                        </a>
                    <?php endif; ?>
                </div>
            </div>
            <div class="card-body">
                <?php if (empty($bimbingan_list)): ?>
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle"></i> Belum ada topik bimbingan. 
                        <a href="<?php echo site_url('Pkl/bimbingan_create'); ?>" class="alert-link">Buat topik bimbingan baru</a> untuk memulai bimbingan laporan PKL.
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped table-hover">
                            <thead>
                                <tr>
                                    <th width="5%">No</th>
                                    <th>Judul Topik</th>
                                    <th width="10%" class="text-center">Status</th>
                                    <th width="18%" class="text-center">Status Proses</th>
                                    <th width="12%" class="text-center">Last Activity</th>
                                    <th width="18%" class="text-center">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php $no = 1; foreach ($bimbingan_list as $bimbingan): ?>
                                    <tr>
                                        <td><?php echo $no++; ?></td>
                                        <td>
                                            <strong><?php echo htmlspecialchars($bimbingan->judul_topik); ?></strong>
                                            <?php if (isset($bimbingan->nama_dosen) && $bimbingan->nama_dosen): ?>
                                                <br><small class="text-muted">
                                                    <i class="fas fa-user-tie"></i> <?php echo htmlspecialchars($bimbingan->nama_dosen); ?>
                                                </small>
                                            <?php endif; ?>
                                        </td>
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
                                            <a href="<?php echo site_url('Pkl/bimbingan_detail/' . encode_id($bimbingan->id)); ?>" 
                                               class="btn btn-info btn-sm">
                                                <i class="fas fa-eye"></i> Detail
                                            </a>
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

