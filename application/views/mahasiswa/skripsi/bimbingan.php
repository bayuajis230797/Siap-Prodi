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
                    <i class="fas fa-comments"></i> Bimbingan Skripsi
                </h3>
                <div class="card-tools">
                    <a href="<?php echo site_url('Bimbingan_skripsi_mahasiswa/bimbingan_create'); ?>" class="btn btn-primary btn-sm">
                        <i class="fas fa-plus"></i> Buat Topik Baru
                    </a>
                    <?php if (!empty($bimbingan_list)): ?>
                        <a href="<?php echo site_url('Bimbingan_skripsi_mahasiswa/cetak_histori_bimbingan'); ?>" class="btn btn-success btn-sm" target="_blank">
                            <i class="fas fa-file-pdf"></i> Cetak Histori PDF
                        </a>
                    <?php endif; ?>
                </div>
            </div>
            <div class="card-body">
                <!-- Filter Pembimbing -->
                <?php if ($pembimbing_1 || $pembimbing_2): ?>
                    <div class="mb-3">
                        <div class="btn-group mr-2 mb-2" role="group" aria-label="Filter status topik">
                            <a href="<?php echo site_url('Bimbingan_skripsi_mahasiswa/bimbingan' . ($filter_pembimbing ? '?pembimbing=' . urlencode($filter_pembimbing) : '')); ?>" 
                               class="btn btn-sm <?php echo empty($status_filter) ? 'btn-primary' : 'btn-secondary'; ?>">
                                Semua
                            </a>
                            <a href="<?php echo site_url('Bimbingan_skripsi_mahasiswa/bimbingan?status=open' . ($filter_pembimbing ? '&pembimbing=' . urlencode($filter_pembimbing) : '')); ?>" 
                               class="btn btn-sm <?php echo ($status_filter === 'open') ? 'btn-primary' : 'btn-secondary'; ?>">
                                Open
                            </a>
                            <a href="<?php echo site_url('Bimbingan_skripsi_mahasiswa/bimbingan?status=closed' . ($filter_pembimbing ? '&pembimbing=' . urlencode($filter_pembimbing) : '')); ?>" 
                               class="btn btn-sm <?php echo ($status_filter === 'closed') ? 'btn-primary' : 'btn-secondary'; ?>">
                                Closed
                            </a>
                        </div>
                        <div class="btn-group mb-2" role="group">
                            <a href="<?php echo site_url('Bimbingan_skripsi_mahasiswa/bimbingan' . ($status_filter ? '?status=' . urlencode($status_filter) : '')); ?>" 
                               class="btn btn-sm <?php echo !$filter_pembimbing ? 'btn-primary' : 'btn-secondary'; ?>">
                                Semua Pembimbing
                            </a>
                            <?php if ($pembimbing_1): ?>
                                <a href="<?php echo site_url('Bimbingan_skripsi_mahasiswa/bimbingan?pembimbing=1' . ($status_filter ? '&status=' . urlencode($status_filter) : '')); ?>" 
                                   class="btn btn-sm <?php echo $filter_pembimbing == '1' ? 'btn-primary' : 'btn-secondary'; ?>">
                                    Pembimbing 1: <?php echo htmlspecialchars($pembimbing_1->nama_dosen); ?>
                                </a>
                            <?php endif; ?>
                            <?php if ($pembimbing_2): ?>
                                <a href="<?php echo site_url('Bimbingan_skripsi_mahasiswa/bimbingan?pembimbing=2' . ($status_filter ? '&status=' . urlencode($status_filter) : '')); ?>" 
                                   class="btn btn-sm <?php echo $filter_pembimbing == '2' ? 'btn-primary' : 'btn-secondary'; ?>">
                                    Pembimbing 2: <?php echo htmlspecialchars($pembimbing_2->nama_dosen); ?>
                                </a>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endif; ?>

                <?php if (empty($bimbingan_list)): ?>
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle"></i> Belum ada topik bimbingan. 
                        <a href="<?php echo site_url('Bimbingan_skripsi_mahasiswa/bimbingan_create'); ?>" class="alert-link">Buat topik bimbingan baru</a> untuk memulai bimbingan laporan skripsi.
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped table-hover">
                            <thead>
                                <tr>
                                    <th width="5%">No</th>
                                    <th>Judul Topik</th>
                                    <th width="14%" class="text-center">Pembimbing</th>
                                    <th width="9%" class="text-center">Status</th>
                                    <th width="16%" class="text-center">Status Proses</th>
                                    <th width="12%" class="text-center">Last Activity</th>
                                    <th width="14%" class="text-center">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php $no = 1; foreach ($bimbingan_list as $bimbingan): ?>
                                    <tr>
                                        <td><?php echo $no++; ?></td>
                                        <td>
                                            <strong><?php echo htmlspecialchars($bimbingan->judul_topik); ?></strong>
                                        </td>
                                        <td class="text-center">
                                            <?php if ($bimbingan->tipe_pembimbing == 1): ?>
                                                <span class="badge badge-info">Pembimbing 1</span>
                                                <?php if (isset($bimbingan->nama_dosen)): ?>
                                                    <br><small class="text-muted"><?php echo htmlspecialchars($bimbingan->nama_dosen); ?></small>
                                                <?php endif; ?>
                                            <?php else: ?>
                                                <span class="badge badge-warning">Pembimbing 2</span>
                                                <?php if (isset($bimbingan->nama_dosen)): ?>
                                                    <br><small class="text-muted"><?php echo htmlspecialchars($bimbingan->nama_dosen); ?></small>
                                                <?php endif; ?>
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
                                                <?php echo htmlspecialchars($bimbingan->workflow_status_label ?? '-'); ?>
                                            </span>
                                        </td>
                                        <td class="text-center">
                                            <?php echo date('d/m/Y H:i', strtotime($bimbingan->last_activity)); ?>
                                        </td>
                                        <td class="text-center">
                                            <a href="<?php echo site_url('Bimbingan_skripsi_mahasiswa/bimbingan_detail/' . encode_id($bimbingan->id)); ?>" 
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

