<div class="row">
    <div class="col-12">
        <div class="card card-primary">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-file-edit"></i> Detail Ajuan Perubahan Judul
                </h3>
                <div class="card-tools">
                    <a href="<?php echo site_url('Ajuan_perubahan_judul'); ?>" class="btn btn-secondary btn-sm">
                        <i class="fas fa-arrow-left"></i> Kembali
                    </a>
                </div>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <h5>Data Mahasiswa</h5>
                        <table class="table table-bordered">
                            <tr>
                                <th width="40%">NIM</th>
                                <td><?php echo htmlspecialchars($pengajuan->nim); ?></td>
                            </tr>
                            <tr>
                                <th>Nama Mahasiswa</th>
                                <td><?php echo htmlspecialchars($pengajuan->nama_mahasiswa); ?></td>
                            </tr>
                            <tr>
                                <th>Kelas</th>
                                <td><?php echo htmlspecialchars($pengajuan->kelas); ?></td>
                            </tr>
                        </table>
                    </div>
                    <div class="col-md-6">
                        <h5>Pembimbing</h5>
                        <table class="table table-bordered">
                            <tr>
                                <th width="40%">Pembimbing 1</th>
                                <td><?php echo htmlspecialchars($pengajuan->pembimbing_1_final_nama ?? '-'); ?></td>
                            </tr>
                            <tr>
                                <th>Pembimbing 2</th>
                                <td><?php echo htmlspecialchars($pengajuan->pembimbing_2_final_nama ?? '-'); ?></td>
                            </tr>
                        </table>
                    </div>
                </div>

                <hr>

                <h5>Judul Skripsi</h5>
                <div class="form-group">
                    <label>Judul Lama (yang sudah disetujui)</label>
                    <p class="form-control-plaintext"><strong><?php echo htmlspecialchars($pengajuan->judul_disetujui ?: $pengajuan->judul_pengajuan); ?></strong></p>
                </div>

                <?php if ($pengajuan->judul_revisi): ?>
                    <div class="form-group">
                        <label>Judul Revisi</label>
                        <p class="form-control-plaintext"><strong class="text-primary"><?php echo htmlspecialchars($pengajuan->judul_revisi); ?></strong></p>
                    </div>
                <?php endif; ?>

                <?php 
                $status_p1 = isset($pengajuan->status_acc_judul_p1) ? $pengajuan->status_acc_judul_p1 : 'pending';
                $status_p2 = isset($pengajuan->status_acc_judul_p2) ? $pengajuan->status_acc_judul_p2 : 'pending';
                
                if ($status_p1 == 'revisi' && !empty($pengajuan->catatan_revisi_p1)): 
                ?>
                    <div class="alert alert-warning">
                        <h5>Catatan Revisi dari Pembimbing 1</h5>
                        <p><?php echo nl2br(htmlspecialchars($pengajuan->catatan_revisi_p1)); ?></p>
                    </div>
                <?php endif; ?>

                <?php if ($status_p2 == 'revisi' && !empty($pengajuan->catatan_revisi_p2)): 
                ?>
                    <div class="alert alert-warning">
                        <h5>Catatan Revisi dari Pembimbing 2</h5>
                        <p><?php echo nl2br(htmlspecialchars($pengajuan->catatan_revisi_p2)); ?></p>
                    </div>
                <?php endif; ?>

                <?php if ($pengajuan->file_persetujuan_judul): ?>
                    <div class="form-group">
                        <label>File Bukti Perubahan Judul</label>
                        <div>
                            <a href="<?php echo site_url('Ajuan_perubahan_judul/download_file/' . $pengajuan->id); ?>" 
                               target="_blank" 
                               class="btn btn-sm btn-info">
                                <i class="fas fa-file-pdf"></i> Lihat File
                            </a>
                        </div>
                        <small class="form-text text-muted">File bukti perubahan judul yang sudah ditandatangani pembimbing.</small>
                    </div>
                <?php endif; ?>

                <?php if ($pengajuan->status_approve_revisi == 'pending'): ?>
                    <hr>
                    <h5>Review Perubahan Judul</h5>
                    
                    <!-- Form ACC -->
                    <div class="card card-success mb-3">
                        <div class="card-header">
                            <h3 class="card-title">Setujui Perubahan Judul</h3>
                        </div>
                        <div class="card-body">
                            <p>Jika Anda menyetujui perubahan judul ini, maka:</p>
                            <ul>
                                <li>Judul skripsi akan diupdate dengan judul revisi</li>
                                <li>Status pembimbing yang meminta revisi akan otomatis menjadi ACC</li>
                                <li>Mahasiswa dan pembimbing akan otomatis masuk ke menu Bimbingan Skripsi</li>
                            </ul>
                            <a href="<?php echo site_url('Ajuan_perubahan_judul/acc/' . $pengajuan->id); ?>" 
                               class="btn btn-success" 
                               onclick="return confirm('Setujui perubahan judul ini?')">
                                <i class="fas fa-check"></i> Setujui Perubahan Judul
                            </a>
                        </div>
                    </div>

                    <!-- Form Tolak -->
                    <?php echo form_open('Ajuan_perubahan_judul/tolak/' . $pengajuan->id, array('method' => 'post')); ?>
                    <div class="card card-danger">
                        <div class="card-header">
                            <h3 class="card-title">Tolak Perubahan Judul</h3>
                        </div>
                        <div class="card-body">
                            <div class="form-group">
                                <label for="alasan_penolakan">Alasan Penolakan <span class="text-danger">*</span></label>
                                <textarea name="alasan_penolakan" id="alasan_penolakan" class="form-control" rows="5" required></textarea>
                                <small class="form-text text-muted">Berikan alasan mengapa perubahan judul ditolak.</small>
                            </div>

                            <button type="submit" class="btn btn-danger">
                                <i class="fas fa-times"></i> Tolak Perubahan Judul
                            </button>
                        </div>
                    </div>
                    <?php echo form_close(); ?>
                <?php else: ?>
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle"></i>
                        <?php if ($pengajuan->status_approve_revisi == 'disetujui'): ?>
                            Perubahan judul telah disetujui pada <?php echo date('d/m/Y H:i', strtotime($pengajuan->approved_at)); ?>.
                        <?php elseif ($pengajuan->status_approve_revisi == 'ditolak'): ?>
                            Perubahan judul telah ditolak.
                            <?php if ($pengajuan->catatan_sekprodi): ?>
                                <br><strong>Alasan:</strong> <?php echo nl2br(htmlspecialchars($pengajuan->catatan_sekprodi)); ?>
                            <?php endif; ?>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

