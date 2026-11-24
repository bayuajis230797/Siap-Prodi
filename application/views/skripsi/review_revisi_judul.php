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
                <h3 class="card-title">Review Perubahan Judul Skripsi</h3>
                <div class="card-tools">
                    <a href="<?php echo site_url('Skripsi/approve_revisi_judul'); ?>" class="btn btn-secondary btn-sm">
                        <i class="fas fa-arrow-left"></i> Kembali
                    </a>
                </div>
            </div>
            <div class="card-body">
                <!-- Informasi Mahasiswa -->
                <div class="row mb-4">
                    <div class="col-md-6">
                        <h5 class="mb-3"><i class="fas fa-user"></i> Informasi Mahasiswa</h5>
                        <table class="table table-sm">
                            <tr>
                                <th width="40%">NIM</th>
                                <td><strong><?php echo $pengajuan->nim; ?></strong></td>
                            </tr>
                            <tr>
                                <th>Nama Mahasiswa</th>
                                <td><?php echo $pengajuan->nama_mahasiswa; ?></td>
                            </tr>
                            <tr>
                                <th>Kelas</th>
                                <td><span class="badge badge-info"><?php echo $pengajuan->kelas; ?></span></td>
                            </tr>
                            <tr>
                                <th>Pembimbing 1</th>
                                <td><?php echo $pengajuan->pembimbing_1_final_nama; ?></td>
                            </tr>
                            <tr>
                                <th>Pembimbing 2</th>
                                <td><?php echo $pengajuan->pembimbing_2_final_nama; ?></td>
                            </tr>
                        </table>
                    </div>
                    <div class="col-md-6">
                        <h5 class="mb-3"><i class="fas fa-book"></i> Informasi Judul</h5>
                        <table class="table table-sm">
                            <tr>
                                <th width="40%">Judul Awal</th>
                                <td><?php echo $pengajuan->judul_pengajuan; ?></td>
                            </tr>
                            <tr>
                                <th>Judul Disetujui</th>
                                <td><?php echo $pengajuan->judul_disetujui ?: '<span class="text-muted">-</span>'; ?></td>
                            </tr>
                            <tr>
                                <th>Status ACC Pembimbing</th>
                                <td>
                                    <?php
                                    $status_p1 = $pengajuan->status_acc_judul_p1 ?? 'pending';
                                    $status_p2 = $pengajuan->status_acc_judul_p2 ?? 'pending';
                                    ?>
                                    P1: <span class="badge badge-<?php echo $status_p1 === 'revisi' ? 'danger' : ($status_p1 === 'acc' ? 'success' : 'warning'); ?>">
                                        <?php echo strtoupper($status_p1); ?>
                                    </span><br>
                                    P2: <span class="badge badge-<?php echo $status_p2 === 'revisi' ? 'danger' : ($status_p2 === 'acc' ? 'success' : 'warning'); ?>">
                                        <?php echo strtoupper($status_p2); ?>
                                    </span>
                                </td>
                            </tr>
                        </table>
                    </div>
                </div>

                <!-- Catatan Revisi dari Pembimbing -->
                <?php if ($status_p1 === 'revisi' && !empty($pengajuan->catatan_revisi_p1)): ?>
                    <div class="card card-outline card-danger mb-3">
                        <div class="card-header">
                            <h6 class="mb-0">Catatan Revisi dari Pembimbing 1</h6>
                        </div>
                        <div class="card-body">
                            <p class="mb-0"><?php echo nl2br(htmlspecialchars($pengajuan->catatan_revisi_p1)); ?></p>
                        </div>
                    </div>
                <?php endif; ?>

                <?php if ($status_p2 === 'revisi' && !empty($pengajuan->catatan_revisi_p2)): ?>
                    <div class="card card-outline card-danger mb-3">
                        <div class="card-header">
                            <h6 class="mb-0">Catatan Revisi dari Pembimbing 2</h6>
                        </div>
                        <div class="card-body">
                            <p class="mb-0"><?php echo nl2br(htmlspecialchars($pengajuan->catatan_revisi_p2)); ?></p>
                        </div>
                    </div>
                <?php endif; ?>

                <!-- File Persetujuan atau Judul Revisi -->
                <div class="card card-outline card-info mb-3">
                    <div class="card-header">
                        <h6 class="mb-0">Dokumen yang Diajukan Mahasiswa</h6>
                    </div>
                    <div class="card-body">
                        <?php if (!empty($pengajuan->file_persetujuan_judul)): ?>
                            <div class="mb-3">
                                <strong>File Lembar Persetujuan:</strong><br>
                                <a href="<?php echo site_url('Skripsi/download_persetujuan_judul/' . $pengajuan->id); ?>" target="_blank" class="btn btn-info mt-2">
                                    <i class="fas fa-download"></i> Download File Persetujuan
                                </a>
                            </div>
                        <?php endif; ?>
                        
                        <?php if (!empty($pengajuan->judul_revisi)): ?>
                            <div>
                                <strong>Judul Revisi yang Diajukan:</strong>
                                <div class="alert alert-light mt-2">
                                    <strong><?php echo $pengajuan->judul_revisi; ?></strong>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Form Approve/Reject -->
                <?php echo form_open('Skripsi/review_revisi_judul/' . $pengajuan->id); ?>
                    <div class="row">
                        <div class="col-md-6">
                            <button type="submit" name="action" value="approve" class="btn btn-success btn-block btn-lg" onclick="return confirm('Apakah Anda yakin ingin menyetujui perubahan judul ini?');">
                                <i class="fas fa-check-circle"></i> Setujui Perubahan Judul
                            </button>
                            <small class="form-text text-muted text-center d-block mt-2">
                                Klik tombol ini untuk menyetujui perubahan judul.
                            </small>
                        </div>
                        <div class="col-md-6">
                            <button type="button" class="btn btn-danger btn-block btn-lg" data-bs-toggle="modal" data-bs-target="#modalTolak">
                                <i class="fas fa-times-circle"></i> Tolak Perubahan
                            </button>
                            <small class="form-text text-muted text-center d-block mt-2">
                                Klik tombol ini jika perubahan judul ditolak.
                            </small>
                        </div>
                    </div>
                <?php echo form_close(); ?>
            </div>
        </div>
    </div>
</div>

<!-- Modal Tolak -->
<div class="modal fade" id="modalTolak" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Tolak Perubahan Judul</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <?php echo form_open('Skripsi/review_revisi_judul/' . $pengajuan->id); ?>
                <div class="modal-body">
                    <div class="form-group">
                        <label for="catatan_tolak">Alasan Penolakan <span class="text-danger">*</span></label>
                        <textarea name="catatan_tolak" id="catatan_tolak" rows="5" class="form-control" required placeholder="Masukkan alasan penolakan perubahan judul..."><?php echo set_value('catatan_tolak'); ?></textarea>
                        <small class="form-text text-muted">Berikan alasan yang jelas mengapa perubahan judul ditolak.</small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" name="action" value="reject" class="btn btn-danger">
                        <i class="fas fa-times-circle"></i> Tolak Perubahan
                    </button>
                </div>
            <?php echo form_close(); ?>
        </div>
    </div>
</div>

