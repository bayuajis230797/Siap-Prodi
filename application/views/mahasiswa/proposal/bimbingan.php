<div class="row">
    <div class="col-12">
        <div class="card card-primary">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-comments"></i> Bimbingan Proposal TA</h3>
                <div class="card-tools">
                    <a href="<?php echo site_url('Bimbingan_proposal_mahasiswa/bimbingan_create'); ?>" class="btn btn-primary btn-sm">
                        <i class="fas fa-plus"></i> Buat Topik Baru
                    </a>
                    <?php if (!empty($bimbingan_list)): ?>
                        <a href="<?php echo site_url('Bimbingan_proposal_mahasiswa/cetak_histori_bimbingan'); ?>" class="btn btn-success btn-sm" target="_blank">
                            <i class="fas fa-file-pdf"></i> Cetak Histori (PDF)
                        </a>
                    <?php endif; ?>
                </div>
            </div>
            <div class="card-body">
                <?php if ($this->session->flashdata('success')): ?>
                    <div class="alert alert-success alert-dismissible">
                        <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
                        <?php echo $this->session->flashdata('success'); ?>
                    </div>
                <?php endif; ?>
                <?php if ($this->session->flashdata('error')): ?>
                    <div class="alert alert-danger alert-dismissible">
                        <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
                        <?php echo $this->session->flashdata('error'); ?>
                    </div>
                <?php endif; ?>

                <div class="alert alert-info">
                    <i class="fas fa-user-tie"></i>
                    <strong>Pembimbing:</strong> <?php echo htmlspecialchars($pengajuan->pembimbing_nama ?? '-'); ?>
                    &nbsp;|&nbsp; <strong>Judul:</strong> <?php echo htmlspecialchars(get_judul_proposal_terbaru($pengajuan->id_mahasiswa, $pengajuan->id_tahun_akademik) ?: '-'); ?>
                </div>

                <?php if ($proposal && $proposal->status === 'layak_sidang'): ?>
                    <div class="alert alert-success">
                        <i class="fas fa-check-circle"></i>
                        <strong>Anda dinyatakan layak sidang.</strong>
                        Silakan lanjut ke pendaftaran sidang.
                    </div>
                <?php endif; ?>

                <div class="mb-3">
                    <a href="<?php echo site_url('Bimbingan_proposal_mahasiswa/bimbingan'); ?>" class="btn btn-sm btn-<?php echo !$status_filter ? 'primary' : 'outline-primary'; ?>">Semua</a>
                    <a href="<?php echo site_url('Bimbingan_proposal_mahasiswa/bimbingan?status=open'); ?>" class="btn btn-sm btn-<?php echo $status_filter === 'open' ? 'primary' : 'outline-primary'; ?>">Terbuka</a>
                    <a href="<?php echo site_url('Bimbingan_proposal_mahasiswa/bimbingan?status=closed'); ?>" class="btn btn-sm btn-<?php echo $status_filter === 'closed' ? 'primary' : 'outline-primary'; ?>">Ditutup</a>
                </div>

                <?php if (empty($bimbingan_list)): ?>
                    <div class="alert alert-warning">
                        <i class="fas fa-info-circle"></i> Belum ada topik bimbingan.
                        <a href="<?php echo site_url('Bimbingan_proposal_mahasiswa/bimbingan_create'); ?>" class="alert-link">Buat topik pertama</a>.
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped table-hover">
                            <thead>
                                <tr>
                                    <th width="5%">No</th>
                                    <th>Topik</th>
                                    <th width="22%" class="text-center">Status</th>
                                    <th width="10%" class="text-center">Topik</th>
                                    <th width="17%">Aktivitas Terakhir</th>
                                    <th width="10%" class="text-center">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php $no = 1; foreach ($bimbingan_list as $b): ?>
                                    <tr>
                                        <td><?php echo $no++; ?></td>
                                        <td><strong><?php echo htmlspecialchars($b->judul_topik); ?></strong></td>
                                        <td class="text-center"><span class="badge badge-<?php echo $b->workflow_status_class; ?>"><?php echo htmlspecialchars($b->workflow_status_label); ?></span></td>
                                        <td class="text-center"><span class="badge badge-<?php echo $b->status === 'open' ? 'success' : 'secondary'; ?>"><?php echo $b->status === 'open' ? 'Terbuka' : 'Ditutup'; ?></span></td>
                                        <td><?php echo date('d/m/Y H:i', strtotime($b->last_activity)); ?></td>
                                        <td class="text-center">
                                            <a href="<?php echo site_url('Bimbingan_proposal_mahasiswa/bimbingan_detail/' . encode_id($b->id)); ?>" class="btn btn-sm btn-info" title="Detail">
                                                <i class="fas fa-eye"></i>
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
