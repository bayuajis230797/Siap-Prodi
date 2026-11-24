<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Registrasi PKL</h3>
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

                <?php if (!empty($workflow_state)): ?>
                    <div class="card card-outline card-secondary mb-3">
                        <div class="card-header">
                            <h5 class="card-title mb-0"><i class="fas fa-route"></i> Workflow PKL Saat Ini</h5>
                        </div>
                        <div class="card-body">
                            <span class="badge badge-<?php echo htmlspecialchars($workflow_state['class'] ?? 'secondary'); ?>">
                                <?php echo htmlspecialchars($workflow_state['label'] ?? 'Belum Ada Data'); ?>
                            </span>
                            <p class="text-muted mb-2 mt-2"><?php echo htmlspecialchars($workflow_state['description'] ?? '-'); ?></p>
                            <?php if (!empty($workflow_state['progress'])): ?>
                                <small class="text-muted">
                                    Progress: <?php echo (int) ($workflow_state['progress']['current_step'] ?? 0); ?>/<?php echo (int) ($workflow_state['progress']['total_steps'] ?? 10); ?>
                                    (<?php echo (float) ($workflow_state['progress']['percentage'] ?? 0); ?>%)
                                </small>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endif; ?>

                <?php if (!$tempat_pkl): ?>
                    <!-- Belum Input Tempat PKL -->
                    <div class="alert alert-info">
                        <h5><i class="fas fa-info-circle"></i> Selamat Datang di Sistem PKL</h5>
                        <p class="mb-0">Silakan input data tempat PKL terlebih dahulu sebelum melanjutkan ke tahap selanjutnya.</p>
                    </div>
                    <a href="<?php echo site_url('Pkl/input_tempat'); ?>" class="btn btn-primary">
                        <i class="fas fa-plus"></i> Input Data Tempat PKL
                    </a>
                <?php else: ?>
                    <!-- Status Tempat PKL -->
                    <div class="card card-outline card-info mb-3">
                        <div class="card-header">
                            <h5 class="card-title mb-0"><i class="fas fa-building"></i> Data Tempat PKL</h5>
                        </div>
                        <div class="card-body">
                            <table class="table table-sm">
                                <tr>
                                    <th width="30%">Nama Instansi</th>
                                    <td><?php echo htmlspecialchars($tempat_pkl->nama_instansi); ?></td>
                                </tr>
                                <tr>
                                    <th>Alamat Instansi</th>
                                    <td><?php echo nl2br(htmlspecialchars($tempat_pkl->alamat_instansi ?: '-')); ?></td>
                                </tr>
                                <?php if (($tempat_pkl->status_acc_tempat ?? '') === 'disetujui'): ?>
                                    <tr>
                                        <th>Dosen Pembimbing</th>
                                        <td>
                                            <?php if (!empty($pengajuan) && !empty($pengajuan->id_pembimbing) && !empty($pengajuan->pembimbing_nama)): ?>
                                                <strong><?php echo htmlspecialchars($pengajuan->pembimbing_nama); ?></strong>
                                                <?php if (!empty($pengajuan->pembimbing_nidn)): ?>
                                                    <br><small class="text-muted">NIDN: <?php echo htmlspecialchars($pengajuan->pembimbing_nidn); ?></small>
                                                <?php endif; ?>
                                            <?php else: ?>
                                                <span class="text-muted">Belum ditetapkan oleh Sekprodi.</span>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endif; ?>
                                <tr>
                                    <th>Status Tempat</th>
                                    <td>
                                        <?php
                                        $status_class = 'warning';
                                        $status_text = 'Pending';
                                        if ($tempat_pkl->status_acc_tempat === 'disetujui') {
                                            $status_class = 'success';
                                            $status_text = 'Disetujui';
                                        } elseif ($tempat_pkl->status_acc_tempat === 'ditolak') {
                                            $status_class = 'danger';
                                            $status_text = 'Ditolak';
                                        }
                                        ?>
                                        <span class="badge badge-<?php echo $status_class; ?>"><?php echo $status_text; ?></span>
                                        <?php if ($tempat_pkl->status === 'draft'): ?>
                                            <a href="<?php echo site_url('Pkl/input_tempat'); ?>" class="btn btn-sm btn-warning ml-2">
                                                <i class="fas fa-edit"></i> Edit
                                            </a>
                                            <a href="<?php echo site_url('Pkl/selesai_tempat'); ?>" class="btn btn-sm btn-primary ml-2" 
                                               onclick="return confirm('Kirim data tempat PKL ke Sekprodi untuk persetujuan?')">
                                                <i class="fas fa-paper-plane"></i> Kirim ke Sekprodi
                                            </a>
                                        <?php else: ?>
                                            <a href="<?php echo site_url('Pkl/input_tempat'); ?>" class="btn btn-sm btn-info ml-2">
                                                <i class="fas fa-eye"></i> Lihat Detail
                                            </a>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            </table>
                        </div>
                    </div>

                    <!-- Status Pengajuan Judul -->
                    <?php if ($pembimbing_sudah_ditetapkan): ?>
                        <div class="card card-outline card-primary mb-3">
                            <div class="card-header">
                                <h5 class="card-title mb-0"><i class="fas fa-file-alt"></i> Pengajuan Judul PKL</h5>
                            </div>
                            <div class="card-body">
                                <?php if ($pengajuan): ?>
                                    <table class="table table-sm">
                                        <tr>
                                            <th width="30%">Judul PKL</th>
                                            <td><?php echo htmlspecialchars($pengajuan->judul_pkl); ?></td>
                                        </tr>
                                        <tr>
                                            <th>Status Persetujuan</th>
                                            <td>
                                                <?php
                                                $status_class = 'warning';
                                                $status_text = 'Pending';
                                                if ($pengajuan->status_acc_judul === 'acc') {
                                                    $status_class = 'success';
                                                    $status_text = 'Disetujui';
                                                } elseif ($pengajuan->status_acc_judul === 'revisi') {
                                                    $status_class = 'danger';
                                                    $status_text = 'Revisi';
                                                }
                                                ?>
                                                <span class="badge badge-<?php echo $status_class; ?>"><?php echo $status_text; ?></span>
                                            </td>
                                        </tr>
                                        <?php if ($pengajuan->catatan_revisi): ?>
                                            <tr>
                                                <th>Catatan Revisi</th>
                                                <td><?php echo nl2br(htmlspecialchars($pengajuan->catatan_revisi)); ?></td>
                                            </tr>
                                        <?php endif; ?>
                                    </table>
                                    <a href="<?php echo site_url('Pkl/submit'); ?>" class="btn btn-primary btn-sm">
                                        <i class="fas fa-eye"></i> Lihat Detail Pengajuan
                                    </a>
                                <?php else: ?>
                                    <p class="mb-2">Belum ada pengajuan judul PKL.</p>
                                    <a href="<?php echo site_url('Pkl/submit'); ?>" class="btn btn-primary btn-sm">
                                        <i class="fas fa-plus"></i> Ajukan Judul PKL
                                    </a>
                                <?php endif; ?>
                            </div>
                        </div>

                        <!-- Status Layak Sidang -->
                        <?php if ($is_layak_sidang): ?>
                            <div class="alert alert-success">
                                <h5><i class="fas fa-check-circle"></i> Selamat!</h5>
                                <p class="mb-2">Anda sudah dinyatakan <strong>Layak Sidang</strong> oleh dosen pembimbing.</p>
                                <a href="<?php echo site_url('Pendaftaran_sidang_pkl'); ?>" class="btn btn-success">
                                    <i class="fas fa-calendar-check"></i> Daftar Sidang PKL
                                </a>
                            </div>
                        <?php endif; ?>
                    <?php else: ?>
                        <div class="alert alert-warning">
                            <i class="fas fa-clock"></i> Menunggu penunjukkan dosen pembimbing dari Sekprodi.
                        </div>
                    <?php endif; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

