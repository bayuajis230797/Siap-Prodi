<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-clipboard-check"></i> Ajuan Judul PKL
                </h3>
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

                <?php if ($tahun_aktif): ?>
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle"></i>
                        <strong>Tahun Akademik:</strong> <?php echo $tahun_aktif->tahun_akademik . ' ' . ucfirst($tahun_aktif->semester); ?>
                    </div>
                <?php endif; ?>

                <!-- Filter Status -->
                <div class="mb-3">
                    <form method="get" action="<?php echo site_url('Ajuan_judul_pkl/index'); ?>" class="form-inline">
                        <div class="form-group mr-2">
                            <label for="status" class="mr-2">Filter Status:</label>
                            <select name="status" id="status" class="form-control form-control-sm">
                                <option value="">-- Semua Status --</option>
                                <option value="pending" <?php echo ($filter_status == 'pending') ? 'selected' : ''; ?>>Pending</option>
                                <option value="acc" <?php echo ($filter_status == 'acc') ? 'selected' : ''; ?>>Disetujui</option>
                                <option value="revisi" <?php echo ($filter_status == 'revisi') ? 'selected' : ''; ?>>Revisi</option>
                            </select>
                        </div>
                        <button type="submit" class="btn btn-primary btn-sm mr-2">
                            <i class="fas fa-filter"></i> Filter
                        </button>
                        <?php if ($filter_status): ?>
                            <a href="<?php echo site_url('Ajuan_judul_pkl/index'); ?>" class="btn btn-secondary btn-sm">
                                <i class="fas fa-times"></i> Reset
                            </a>
                        <?php endif; ?>
                    </form>
                </div>

                <?php if (empty($pengajuan_list)): ?>
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle"></i>
                        Tidak ada ajuan judul PKL yang perlu direview.
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped table-hover">
                            <thead>
                                <tr>
                                    <th width="5%">No</th>
                                    <th width="10%">NIM</th>
                                    <th width="15%">Nama Mahasiswa</th>
                                    <th width="8%">Kelas</th>
                                    <th>Judul PKL</th>
                                    <th width="12%" class="text-center">Status ACC</th>
                                    <th width="15%" class="text-center">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php 
                                $no = 1;
                                foreach ($pengajuan_list as $pengajuan): 
                                    $status_acc = $pengajuan->status_acc_judul ?? 'pending';
                                ?>
                                    <tr>
                                        <td><?php echo $no++; ?></td>
                                        <td><strong><?php echo htmlspecialchars($pengajuan->nim); ?></strong></td>
                                        <td><?php echo htmlspecialchars($pengajuan->nama_mahasiswa); ?></td>
                                        <td><?php echo htmlspecialchars($pengajuan->kelas); ?></td>
                                        <td><?php echo htmlspecialchars($pengajuan->judul_pkl); ?></td>
                                        <td class="text-center">
                                            <?php
                                            $status_class = 'warning';
                                            $status_text = 'MENUNGGU';
                                            switch ($status_acc) {
                                                case 'acc':
                                                    $status_class = 'success';
                                                    $status_text = 'DISETUJUI';
                                                    break;
                                                case 'revisi':
                                                    $status_class = 'danger';
                                                    $status_text = 'REVISI';
                                                    break;
                                            }
                                            ?>
                                            <span class="badge badge-<?php echo $status_class; ?>">
                                                <?php echo $status_text; ?>
                                            </span>
                                        </td>
                                        <td class="text-center">
                                            <a href="<?php echo site_url('Ajuan_judul_pkl/detail/' . $pengajuan->id); ?>" 
                                               class="btn btn-sm btn-info" title="Review">
                                                <i class="fas fa-eye"></i> Review
                                            </a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    
                    <!-- Pagination -->
                    <?php if (isset($pagination) && !empty($pagination)): ?>
                        <div class="row mt-3">
                            <div class="col-md-6">
                                <p class="text-muted">
                                    Menampilkan <?php echo count($pengajuan_list); ?> dari <?php echo isset($config['total_rows']) ? $config['total_rows'] : count($pengajuan_list); ?> data
                                </p>
                            </div>
                            <div class="col-md-6">
                                <div class="text-right">
                                    <?php echo $pagination; ?>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>






