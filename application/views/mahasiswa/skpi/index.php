<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header"><h3 class="card-title">Pengajuan SKPI</h3></div>
            <div class="card-body">
                <?php if ($this->session->flashdata('success')): ?><div class="alert alert-success"><?php echo $this->session->flashdata('success'); ?></div><?php endif; ?>
                <?php if ($this->session->flashdata('error')): ?><div class="alert alert-danger"><?php echo $this->session->flashdata('error'); ?></div><?php endif; ?>

                <?php if (empty($periode_list)): ?>
                    <div class="alert alert-info">Anda belum terdaftar pada periode SKPI manapun.</div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th>Periode</th>
                                    <th>Deadline</th>
                                    <th>Status Periode</th>
                                    <th>Pengumpulan</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($periode_list as $p): ?>
                                    <tr>
                                        <td><strong><?php echo htmlspecialchars($p->nama_periode); ?></strong></td>
                                        <td><?php echo date('d/m/Y', strtotime($p->deadline_pengumpulan)); ?></td>
                                        <td><span class="badge badge-<?php echo $p->status === 'aktif' ? 'success' : 'secondary'; ?>"><?php echo ucfirst($p->status); ?></span></td>
                                        <td>
                                            <?php if (!empty($p->deadline_lewat)): ?>
                                                <span class="badge badge-danger">Deadline lewat</span>
                                            <?php elseif (!empty($p->upload_allowed)): ?>
                                                <span class="badge badge-success">Buka</span>
                                            <?php else: ?>
                                                <span class="badge badge-secondary">Tutup</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <a href="<?php echo site_url('Skpi_mahasiswa/upload/' . $p->id); ?>" class="btn btn-sm btn-primary">
                                                <i class="fas fa-upload"></i> Kelola Berkas
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
