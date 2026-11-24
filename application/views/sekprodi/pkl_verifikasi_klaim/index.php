<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Verifikasi Klaim Nilai PKL</h3>
            </div>
            <div class="card-body">
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

                <div class="row mb-3">
                    <div class="col-md-3 col-sm-6">
                        <div class="small-box bg-warning">
                            <div class="inner">
                                <h3><?php echo (int) ($verification_summary['pending_total'] ?? 0); ?></h3>
                                <p>Menunggu Verifikasi</p>
                            </div>
                            <div class="icon"><i class="fas fa-hourglass-half"></i></div>
                        </div>
                    </div>
                    <div class="col-md-3 col-sm-6">
                        <div class="small-box bg-danger">
                            <div class="inner">
                                <h3><?php echo (int) ($verification_summary['pending_overdue'] ?? 0); ?></h3>
                                <p>Menunggu >= 3 Hari</p>
                            </div>
                            <div class="icon"><i class="fas fa-exclamation-triangle"></i></div>
                        </div>
                    </div>
                    <div class="col-md-3 col-sm-6">
                        <div class="small-box bg-maroon">
                            <div class="inner">
                                <h3><?php echo (int) ($verification_summary['invalid_link_total'] ?? 0); ?></h3>
                                <p>Indikasi Link Tidak Valid</p>
                            </div>
                            <div class="icon"><i class="fas fa-unlink"></i></div>
                        </div>
                    </div>
                    <div class="col-md-3 col-sm-6">
                        <div class="small-box bg-info">
                            <div class="inner">
                                <h3><?php echo (int) ($verification_summary['missing_required_total'] ?? 0); ?></h3>
                                <p>Berkas Wajib Kurang</p>
                            </div>
                            <div class="icon"><i class="fas fa-file-excel"></i></div>
                        </div>
                    </div>
                </div>

                <!-- Filter -->
                <div class="row mb-3">
                    <div class="col-md-6">
                        <form method="get" action="<?php echo site_url('Pkl_verifikasi_klaim/index'); ?>">
                            <div class="input-group">
                                <select name="status" class="form-control">
                                    <option value="">-- Semua Status --</option>
                                    <option value="menunggu" <?php echo ($status_filter == 'menunggu') ? 'selected' : ''; ?>>Menunggu</option>
                                    <option value="disetujui" <?php echo ($status_filter == 'disetujui') ? 'selected' : ''; ?>>Disetujui</option>
                                    <option value="ditolak" <?php echo ($status_filter == 'ditolak') ? 'selected' : ''; ?>>Ditolak</option>
                                    <option value="selesai" <?php echo ($status_filter == 'selesai') ? 'selected' : ''; ?>>Selesai</option>
                                </select>
                                <div class="input-group-append">
                                    <button type="submit" class="btn btn-info">
                                        <i class="fas fa-filter"></i> Filter
                                    </button>
                                    <?php if ($status_filter): ?>
                                        <a href="<?php echo site_url('Pkl_verifikasi_klaim'); ?>" class="btn btn-secondary">
                                            <i class="fas fa-times"></i> Reset
                                        </a>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
                
                <div class="table-responsive">
                    <table class="table table-bordered table-striped table-hover">
                        <thead>
                            <tr>
                                <th width="5%">No</th>
                                <th width="12%">NIM</th>
                                <th>Nama Mahasiswa</th>
                                <th width="8%" class="text-center">Kelas</th>
                                <th width="16%" class="text-center">Prioritas</th>
                                <th width="12%" class="text-center">Status</th>
                                <th width="15%" class="text-center">Tanggal Pengajuan</th>
                                <th width="15%" class="text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($klaim_list)): ?>
                                <tr>
                                    <td colspan="8" class="text-center text-muted">
                                        <i class="fas fa-inbox"></i> Belum ada klaim nilai PKL.
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php 
                                $no = 1; 
                                $page = ($this->uri->segment(3)) ? $this->uri->segment(3) : 1;
                                $no = ($page - 1) * 10 + 1;
                                foreach ($klaim_list as $klaim): 
                                ?>
                                    <tr>
                                        <td><?php echo $no++; ?></td>
                                        <td><strong><?php echo $klaim->nim; ?></strong></td>
                                        <td><?php echo $klaim->nama_mahasiswa; ?></td>
                                        <td class="text-center">
                                            <span class="badge badge-info"><?php echo $klaim->kelas; ?></span>
                                        </td>
                                        <td class="text-center">
                                            <?php $flags = $klaim->verifikasi_flags ?? array(); ?>
                                            <span class="badge badge-<?php echo htmlspecialchars($flags['priority_class'] ?? 'secondary'); ?>">
                                                <?php echo htmlspecialchars($flags['priority_label'] ?? 'Rendah'); ?>
                                            </span>
                                            <?php if (!empty($flags['is_pending']) && !empty($flags['wait_days'])): ?>
                                                <br><small class="text-muted"><?php echo (int) $flags['wait_days']; ?> hari</small>
                                            <?php endif; ?>
                                            <?php if (!empty($flags['invalid_link_count'])): ?>
                                                <br><small class="text-danger">Link invalid: <?php echo (int) $flags['invalid_link_count']; ?></small>
                                            <?php endif; ?>
                                            <?php if (!empty($flags['missing_required_count'])): ?>
                                                <br><small class="text-danger">Kurang wajib: <?php echo (int) $flags['missing_required_count']; ?></small>
                                            <?php endif; ?>
                                        </td>
                                        <td class="text-center">
                                            <?php
                                            $status_class = 'secondary';
                                            switch ($klaim->status) {
                                                case 'menunggu':
                                                    $status_class = 'warning';
                                                    break;
                                                case 'disetujui':
                                                    $status_class = 'success';
                                                    break;
                                                case 'ditolak':
                                                    $status_class = 'danger';
                                                    break;
                                                case 'selesai':
                                                    $status_class = 'info';
                                                    break;
                                            }
                                            ?>
                                            <span class="badge badge-<?php echo $status_class; ?>"><?php echo ucfirst($klaim->status); ?></span>
                                        </td>
                                        <td class="text-center"><?php echo date('d/m/Y H:i', strtotime($klaim->created_at)); ?></td>
                                        <td class="text-center">
                                            <a href="<?php echo site_url('Pkl_verifikasi_klaim/detail/' . $klaim->id); ?>" class="btn btn-sm btn-info" title="Detail">
                                                <i class="fas fa-eye"></i> Detail
                                            </a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>

                <?php if (!empty($pagination)): ?>
                    <div class="mt-3">
                        <?php echo $pagination; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>





