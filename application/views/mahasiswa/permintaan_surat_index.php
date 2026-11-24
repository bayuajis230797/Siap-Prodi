<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Permintaan Surat</h3>
                <div class="card-tools">
                    <a href="<?php echo site_url('Permintaan_surat/create'); ?>" class="btn btn-primary btn-sm">
                        <i class="fas fa-plus"></i> Ajukan Permintaan Surat
                    </a>
                </div>
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

                <?php if (empty($permintaan)): ?>
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle"></i> Belum ada permintaan surat. Silakan ajukan permintaan surat baru.
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped table-hover">
                            <thead>
                                <tr>
                                    <th width="5%">No</th>
                                    <th width="18%">Jenis Surat</th>
                                    <th width="25%">Keperluan</th>
                                    <th width="12%">Tipe Surat</th>
                                    <th width="12%" class="text-center">Status</th>
                                    <th width="13%">Tanggal Pengajuan</th>
                                    <th width="15%" class="text-center">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php $no = 1; foreach ($permintaan as $p): ?>
                                    <tr>
                                        <td><?php echo $no++; ?></td>
                                        <td><?php echo htmlspecialchars($p->jenis_surat); ?></td>
                                        <td>
                                            <?php 
                                            $keperluan = htmlspecialchars($p->keperluan);
                                            echo strlen($keperluan) > 50 ? substr($keperluan, 0, 50) . '...' : $keperluan;
                                            ?>
                                        </td>
                                        <td>
                                            <?php
                                            $tipe_label = '';
                                            $tipe_badge = 'secondary';
                                            if (isset($p->tipe_surat)) {
                                                if ($p->tipe_surat == 'soft_copy') {
                                                    $tipe_label = 'Soft Copy';
                                                    $tipe_badge = 'info';
                                                } elseif ($p->tipe_surat == 'hard_copy') {
                                                    $tipe_label = 'Hard Copy';
                                                    $tipe_badge = 'warning';
                                                } else {
                                                    $tipe_label = '-';
                                                }
                                            } else {
                                                $tipe_label = '-';
                                            }
                                            ?>
                                            <span class="badge badge-<?php echo $tipe_badge; ?>"><?php echo $tipe_label; ?></span>
                                        </td>
                                        <td class="text-center">
                                            <?php
                                            $status_class = 'secondary';
                                            $status_text = ucfirst($p->status);
                                            switch ($p->status) {
                                                case 'pending':
                                                    $status_class = 'warning';
                                                    break;
                                                case 'diproses':
                                                    $status_class = 'info';
                                                    break;
                                                case 'selesai':
                                                    $status_class = 'success';
                                                    break;
                                                case 'ditolak':
                                                    $status_class = 'danger';
                                                    break;
                                            }
                                            ?>
                                            <span class="badge badge-<?php echo $status_class; ?>"><?php echo $status_text; ?></span>
                                        </td>
                                        <td><?php echo date('d/m/Y H:i', strtotime($p->created_at)); ?></td>
                                        <td class="text-center">
                                            <a href="<?php echo site_url('Permintaan_surat/detail/' . $p->id); ?>" 
                                               class="btn btn-sm btn-info" 
                                               title="Detail">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <?php if ($p->status == 'selesai' && $p->file_surat): ?>
                                                <a href="<?php echo site_url('Permintaan_surat/download/' . $p->id); ?>" 
                                                   class="btn btn-sm btn-success" 
                                                   title="Download Surat">
                                                    <i class="fas fa-download"></i>
                                                </a>
                                            <?php endif; ?>
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
                                    Menampilkan <?php echo count($permintaan); ?> dari <?php echo isset($total_rows) ? $total_rows : count($permintaan); ?> data
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

