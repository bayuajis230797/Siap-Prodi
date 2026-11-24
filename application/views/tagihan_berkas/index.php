<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Tagihan Berkas Saya</h3>
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

                <div class="table-responsive">
                    <table class="table table-bordered table-striped table-hover">
                        <thead>
                            <tr>
                                <th width="5%">No</th>
                                <th>Nama Tagihan</th>
                                <th>Jenis</th>
                                <th>Deadline</th>
                                <th>Status</th>
                                <th width="15%">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($tagihan_list)): ?>
                                <tr>
                                    <td colspan="6" class="text-center text-muted">Tidak ada tagihan berkas.</td>
                                </tr>
                            <?php else: ?>
                                <?php $no = 1; foreach ($tagihan_list as $tagihan): ?>
                                    <tr>
                                        <td><?php echo $no++; ?></td>
                                        <td><strong><?php echo htmlspecialchars($tagihan->nama_tagihan); ?></strong></td>
                                        <td>
                                            <?php if ($tagihan->is_hardcopy == 1): ?>
                                                <span class="badge badge-warning">Hardcopy</span>
                                            <?php else: ?>
                                                <span class="badge badge-info">Softfile</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php if ($tagihan->deadline): ?>
                                                <?php 
                                                    $deadline = strtotime($tagihan->deadline);
                                                    $today = strtotime('today');
                                                    $class = ($deadline < $today) ? 'text-danger' : (($deadline == $today) ? 'text-warning' : '');
                                                ?>
                                                <span class="<?php echo $class; ?>">
                                                    <?php echo date('d/m/Y', $deadline); ?>
                                                </span>
                                            <?php else: ?>
                                                <span class="text-muted">-</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php if ($tagihan->is_hardcopy == 1): ?>
                                                <?php if ($tagihan->detail): ?>
                                                    <span class="badge badge-<?php echo $tagihan->detail->status_hardcopy == 'sudah' ? 'success' : 'warning'; ?>">
                                                        <?php echo $tagihan->detail->status_hardcopy == 'sudah' ? 'Sudah Dikumpulkan' : 'Belum Dikumpulkan'; ?>
                                                    </span>
                                                <?php else: ?>
                                                    <span class="badge badge-warning">Belum Dikumpulkan</span>
                                                <?php endif; ?>
                                            <?php else: ?>
                                                <?php if ($tagihan->detail && $tagihan->detail->status == 'sudah'): ?>
                                                    <span class="badge badge-success">Sudah Upload</span>
                                                <?php else: ?>
                                                    <span class="badge badge-warning">Belum Upload</span>
                                                <?php endif; ?>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <a href="<?php echo site_url('Tagihan_berkas/detail/' . $tagihan->id); ?>" 
                                               class="btn btn-info btn-sm" title="Detail">
                                                <i class="fas fa-eye"></i> Detail
                                            </a>
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

