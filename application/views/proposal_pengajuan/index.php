<?php
$status_acc = $pengajuan ? ($pengajuan->status_acc_judul ?? 'pending') : null;
?>
<div class="row">
    <div class="col-12">
        <div class="card card-primary">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-file-alt"></i> Pengajuan Judul Proposal TA</h3>
            </div>
            <div class="card-body">
                <?php if ($this->session->flashdata('success')): ?>
                    <div class="alert alert-success alert-dismissible">
                        <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
                        <?php echo $this->session->flashdata('success'); ?>
                    </div>
                <?php endif; ?>
                <?php if ($this->session->flashdata('error')): ?>
                    <div class="alert alert-danger alert-dismissible">
                        <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
                        <?php echo $this->session->flashdata('error'); ?>
                    </div>
                <?php endif; ?>

                <?php if ($tahun_aktif): ?>
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle"></i>
                        <strong>Tahun Akademik:</strong> <?php echo htmlspecialchars($tahun_aktif->tahun_akademik . ' ' . ucfirst($tahun_aktif->semester)); ?>
                    </div>
                <?php endif; ?>

                <?php if ($pengajuan && $pengajuan->status !== 'ditolak'): ?>
                    <div class="card card-outline card-info mb-3">
                        <div class="card-header">
                            <h5 class="card-title mb-0"><i class="fas fa-info-circle"></i> Status Pengajuan</h5>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <p><strong>Judul Diajukan:</strong><br>
                                        <?php echo htmlspecialchars($pengajuan->judul_pengajuan); ?></p>

                                    <p><strong>Status Pengajuan:</strong>
                                        <?php echo proposal_status_badge($pengajuan->status); ?></p>

                                    <?php if (!empty($judul_terbaru) && $pengajuan->status === 'disetujui'): ?>
                                        <p><strong>Judul Berlaku Saat Ini:</strong><br>
                                            <span class="text-success"><strong><?php echo htmlspecialchars($judul_terbaru); ?></strong></span></p>
                                    <?php endif; ?>

                                    <?php if (!empty($pengajuan->catatan_sekprodi)): ?>
                                        <p><strong>Catatan Sekprodi:</strong><br>
                                            <?php echo nl2br(htmlspecialchars($pengajuan->catatan_sekprodi)); ?></p>
                                    <?php endif; ?>

                                    <?php if (!empty($pengajuan->file_proposal)): ?>
                                        <?php $this->load->helper('google_drive'); ?>
                                        <p><strong>Berkas Proposal:</strong>
                                            <?php echo google_drive_link_html($pengajuan->file_proposal, 'Buka Berkas'); ?></p>
                                    <?php endif; ?>
                                </div>

                                <div class="col-md-6">
                                    <h6 class="font-weight-bold mb-3">Pembimbing &amp; Status ACC Judul</h6>

                                    <?php if (!empty($pengajuan->pembimbing_nama)): ?>
                                        <p class="mb-1">
                                            <strong>Pembimbing:</strong>
                                            <?php echo htmlspecialchars($pengajuan->pembimbing_nama); ?>
                                            <?php if (!empty($pengajuan->pembimbing_nidn)): ?>
                                                <small class="text-muted">(NIDN: <?php echo htmlspecialchars($pengajuan->pembimbing_nidn); ?>)</small>
                                            <?php endif; ?>
                                        </p>
                                        <p class="mb-3">
                                            <?php if ($status_acc === 'acc'): ?>
                                                <span class="badge badge-success">Disetujui (ACC)</span>
                                            <?php elseif ($status_acc === 'revisi'): ?>
                                                <span class="badge badge-info">Perlu Revisi</span>
                                            <?php else: ?>
                                                <span class="badge badge-secondary">Menunggu Review Pembimbing</span>
                                            <?php endif; ?>
                                        </p>
                                    <?php else: ?>
                                        <p class="text-muted">Pembimbing belum ditetapkan sekprodi.</p>
                                    <?php endif; ?>

                                    <?php if ($pengajuan->status === 'pending'): ?>
                                        <div class="alert alert-warning">
                                            <i class="fas fa-clock"></i> Menunggu review sekprodi.
                                        </div>
                                    <?php elseif ($status_acc === 'acc'): ?>
                                        <div class="alert alert-success">
                                            <i class="fas fa-check-circle"></i>
                                            <strong>Judul disetujui pembimbing.</strong> Bimbingan Proposal TA sudah dapat dimulai.
                                            <a href="<?php echo site_url('Bimbingan_proposal_mahasiswa/bimbingan'); ?>" class="alert-link">Buka menu Bimbingan</a>.
                                        </div>
                                    <?php elseif ($status_acc === 'revisi'): ?>
                                        <div class="alert alert-warning">
                                            <i class="fas fa-exclamation-triangle"></i>
                                            <strong>Pembimbing meminta revisi judul.</strong>
                                            <?php if (!empty($pengajuan->catatan_revisi)): ?>
                                                <br><strong>Catatan:</strong> <?php echo nl2br(htmlspecialchars($pengajuan->catatan_revisi)); ?>
                                            <?php endif; ?>
                                            <hr class="my-2">
                                            <?php if (!empty($perubahan_pending)): ?>
                                                Perubahan judul Anda sedang menunggu validasi sekprodi.
                                            <?php else: ?>
                                                Ajukan judul perbaikan lewat menu
                                                <a href="<?php echo site_url('Proposal_perubahan_judul'); ?>" class="alert-link">Perubahan Judul</a>.
                                            <?php endif; ?>
                                        </div>
                                    <?php else: ?>
                                        <div class="alert alert-info">
                                            <i class="fas fa-clock"></i> Menunggu ACC judul dari pembimbing.
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php else: ?>
                    <?php if ($pengajuan && $pengajuan->status === 'ditolak'): ?>
                        <div class="alert alert-danger">
                            <i class="fas fa-times-circle"></i>
                            <strong>Pengajuan sebelumnya ditolak.</strong>
                            <?php if (!empty($pengajuan->catatan_sekprodi)): ?>
                                <br><strong>Alasan:</strong> <?php echo nl2br(htmlspecialchars($pengajuan->catatan_sekprodi)); ?>
                            <?php endif; ?>
                            <br>Silakan ajukan judul baru di bawah ini.
                        </div>
                    <?php endif; ?>

                    <?php echo form_open_multipart('Proposal_pengajuan/submit', array('method' => 'post')); ?>
                        <div class="form-group">
                            <label for="judul_pengajuan">Judul Proposal TA <span class="text-danger">*</span></label>
                            <input type="text" name="judul_pengajuan" id="judul_pengajuan" class="form-control"
                                   value="<?php echo set_value('judul_pengajuan'); ?>" maxlength="255" required>
                            <small class="form-text text-muted">Minimal 10 karakter.</small>
                        </div>

                        <div class="form-group">
                            <label for="deskripsi">Deskripsi</label>
                            <textarea name="deskripsi" id="deskripsi" class="form-control" rows="5"><?php echo set_value('deskripsi'); ?></textarea>
                            <small class="form-text text-muted">Jelaskan secara singkat tentang proposal yang akan dikerjakan.</small>
                        </div>

                        <div class="form-group">
                            <label for="alasan">Alasan Memilih Judul</label>
                            <textarea name="alasan" id="alasan" class="form-control" rows="3"><?php echo set_value('alasan'); ?></textarea>
                            <small class="form-text text-muted">Opsional.</small>
                        </div>

                        <div class="form-group">
                            <label for="file_proposal">Berkas Proposal (opsional)</label>
                            <input type="file" name="file_proposal" id="file_proposal" class="form-control-file"
                                   accept=".pdf,.doc,.docx,.zip,.rar">
                            <small class="form-text text-muted">
                                File disimpan ke Google Drive institusi (dikelola admin). Format: PDF/DOC/DOCX/ZIP dan lainnya.
                            </small>
                        </div>

                        <div class="card-footer px-0">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-paper-plane"></i> Kirim Pengajuan
                            </button>
                        </div>
                    <?php echo form_close(); ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
