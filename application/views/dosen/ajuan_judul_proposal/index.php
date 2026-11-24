<div class="row">
    <div class="col-12">
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

        <div class="card card-primary">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-clipboard-list"></i> Ajuan Judul Proposal TA Mahasiswa</h3>
            </div>
            <div class="card-body">
                <?php if ($tahun_aktif): ?>
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle"></i>
                        <strong>Tahun Akademik:</strong> <?php echo htmlspecialchars($tahun_aktif->tahun_akademik . ' ' . ucfirst($tahun_aktif->semester)); ?>
                    </div>
                <?php endif; ?>

                <form method="get" action="<?php echo site_url('Ajuan_judul_proposal/index'); ?>" class="form-inline mb-3">
                    <label for="status" class="mr-2">Status ACC</label>
                    <select name="status" id="status" class="form-control form-control-sm mr-2">
                        <option value="">Semua</option>
                        <option value="pending" <?php echo ($filter_status === 'pending') ? 'selected' : ''; ?>>Menunggu</option>
                        <option value="acc" <?php echo ($filter_status === 'acc') ? 'selected' : ''; ?>>ACC</option>
                        <option value="revisi" <?php echo ($filter_status === 'revisi') ? 'selected' : ''; ?>>Perlu Revisi</option>
                    </select>
                    <button type="submit" class="btn btn-primary btn-sm mr-1"><i class="fas fa-filter"></i> Filter</button>
                    <a href="<?php echo site_url('Ajuan_judul_proposal'); ?>" class="btn btn-secondary btn-sm"><i class="fas fa-times"></i> Reset</a>
                </form>

                <div class="table-responsive">
                    <table class="table table-bordered table-striped table-hover">
                        <thead>
                            <tr>
                                <th width="5%">No</th>
                                <th width="12%">NIM</th>
                                <th width="20%">Nama Mahasiswa</th>
                                <th width="8%">Kelas</th>
                                <th>Judul</th>
                                <th width="12%" class="text-center">Status ACC</th>
                                <th width="10%" class="text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($pengajuan_list)): ?>
                                <tr><td colspan="7" class="text-center text-muted">Belum ada ajuan judul untuk Anda.</td></tr>
                            <?php else: ?>
                                <?php $no = (max(1, (int) $this->uri->segment(3)) - 1) * $per_page + 1; ?>
                                <?php foreach ($pengajuan_list as $p): ?>
                                    <tr>
                                        <td><?php echo $no++; ?></td>
                                        <td><strong><?php echo htmlspecialchars($p->nim ?? '-'); ?></strong></td>
                                        <td><?php echo htmlspecialchars($p->nama_mahasiswa ?? '-'); ?></td>
                                        <td><span class="badge badge-info"><?php echo htmlspecialchars($p->kelas ?? '-'); ?></span></td>
                                        <td><small><?php echo htmlspecialchars($p->judul_disetujui ?: $p->judul_pengajuan); ?></small></td>
                                        <td class="text-center"><?php echo proposal_status_badge($p->status_acc); ?></td>
                                        <td class="text-center">
                                            <a href="<?php echo site_url('Ajuan_judul_proposal/detail/' . $p->id); ?>" class="btn btn-sm btn-info" title="Review">
                                                <i class="fas fa-eye"></i> Review
                                            </a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>

                <?php if (!empty($pagination)): ?>
                    <div class="row mt-3">
                        <div class="col-md-6"><p class="text-muted">Menampilkan <?php echo count($pengajuan_list); ?> dari <?php echo $total_rows; ?> data</p></div>
                        <div class="col-md-6"><?php echo $pagination; ?></div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
