<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Bimbingan PKL</h3>
                <div class="card-tools">
                    <a href="<?php echo site_url('Pkl'); ?>" class="btn btn-secondary btn-sm">
                        <i class="fas fa-arrow-left"></i> Kembali
                    </a>
                </div>
            </div>
            <div class="card-body">
                <?php if (!$bimbingan): ?>
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle"></i> Bimbingan PKL belum aktif. Bimbingan akan aktif setelah judul PKL disetujui.
                    </div>
                <?php else: ?>
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <h5 class="mb-3"><i class="fas fa-book"></i> Judul Topik</h5>
                            <div class="alert alert-info">
                                <p class="mb-0"><strong><?php echo htmlspecialchars($bimbingan->judul_topik); ?></strong></p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <h5 class="mb-3"><i class="fas fa-info-circle"></i> Status Bimbingan</h5>
                            <table class="table table-sm table-bordered">
                                <tr>
                                    <th width="40%">Status</th>
                                    <td>
                                        <span class="badge badge-<?php echo $bimbingan->status === 'open' ? 'success' : 'secondary'; ?>">
                                            <?php echo ucfirst($bimbingan->status); ?>
                                        </span>
                                    </td>
                                </tr>
                                <tr>
                                    <th>Terakhir Aktivitas</th>
                                    <td><?php echo $bimbingan->last_activity ? date('d/m/Y H:i', strtotime($bimbingan->last_activity)) : '-'; ?></td>
                                </tr>
                            </table>
                        </div>
                    </div>

                    <!-- Form Kirim Pesan -->
                    <div class="card card-primary mb-4">
                        <div class="card-header">
                            <h5 class="card-title mb-0"><i class="fas fa-comment"></i> Kirim Pesan ke Pembimbing</h5>
                        </div>
                        <div class="card-body">
                            <?php echo form_open_multipart('Pkl/kirim_pesan/' . encode_id($bimbingan->id)); ?>
                                <div class="form-group">
                                    <label for="pesan">Pesan <span class="text-danger">*</span></label>
                                    <textarea name="pesan" id="pesan" class="form-control" rows="4" required></textarea>
                                    <small class="form-text text-muted">Tulis pesan atau pertanyaan untuk pembimbing</small>
                                </div>

                                <div class="form-group">
                                    <label for="file_berkas">Berkas (Opsional)</label>
                                    <input type="file" name="file_berkas" id="file_berkas" class="form-control-file" accept=".pdf,.doc,.docx,.zip,.rar,.jpg,.jpeg,.png">
                                    <small class="form-text text-muted">Diunggah ke Google Drive institusi. tanpa batas form.</small>
                                </div>

                                <div class="form-group">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-paper-plane"></i> Kirim Pesan
                                    </button>
                                </div>
                            <?php echo form_close(); ?>
                        </div>
                    </div>

                    <!-- Log Diskusi -->
                    <?php if (!empty($logs)): ?>
                        <h5 class="mb-3"><i class="fas fa-comments"></i> Riwayat Diskusi</h5>
                        <div class="timeline">
                            <?php foreach ($logs as $log): ?>
                                <div class="card mb-3">
                                    <div class="card-header">
                                        <strong>
                                            <?php if ($log->role_pengguna === 'mahasiswa'): ?>
                                                <i class="fas fa-user text-primary"></i> Anda
                                            <?php else: ?>
                                                <i class="fas fa-user-tie text-success"></i> Pembimbing
                                            <?php endif; ?>
                                        </strong>
                                        <small class="float-right text-muted">
                                            <?php echo date('d/m/Y H:i', strtotime($log->created_at)); ?>
                                        </small>
                                    </div>
                                    <div class="card-body">
                                        <p class="mb-2"><?php echo nl2br(htmlspecialchars($log->pesan)); ?></p>
                                        
                                        <?php if ($log->file_path): ?>
                                            <div class="alert alert-info mb-2">
                                                <i class="fas fa-file"></i> 
                                                <strong>File:</strong> <?php echo htmlspecialchars($log->file_name); ?>
                                                <a href="<?php echo site_url('Pkl/download_file/' . $log->id); ?>" class="btn btn-sm btn-info ml-2">
                                                    <i class="fas fa-download"></i> Download
                                                </a>
                                            </div>
                                        <?php endif; ?>

                                        <?php if ($log->status_revisi === 'perlu_revisi'): ?>
                                            <div class="alert alert-warning mb-0">
                                                <i class="fas fa-exclamation-triangle"></i> 
                                                <strong>Perlu Revisi</strong>
                                                <?php if ($log->keterangan_revisi): ?>
                                                    <br><?php echo nl2br(htmlspecialchars($log->keterangan_revisi)); ?>
                                                <?php endif; ?>
                                            </div>
                                        <?php elseif ($log->status_revisi === 'sudah_benar'): ?>
                                            <div class="alert alert-success mb-0">
                                                <i class="fas fa-check-circle"></i> 
                                                <strong>Sudah Benar</strong>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle"></i> Belum ada diskusi. Silakan kirim pesan untuk memulai diskusi dengan pembimbing.
                        </div>
                    <?php endif; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>





