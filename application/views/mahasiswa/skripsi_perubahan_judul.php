<div class="row">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Perubahan Judul Skripsi</h3>
                <div class="card-tools">
                    <a href="<?php echo site_url('Skripsi_pengajuan'); ?>" class="btn btn-secondary btn-sm">
                        <i class="fas fa-arrow-left"></i> Kembali
                    </a>
                </div>
            </div>
            <?php echo form_open_multipart('Skripsi_perubahan_judul'); ?>
            <div class="card-body">
                <?php if (validation_errors()): ?>
                    <div class="alert alert-danger">
                        <?php echo validation_errors(); ?>
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

                <?php if ($this->session->flashdata('success')): ?>
                    <div class="alert alert-success alert-dismissible fade show">
                        <i class="fas fa-check-circle"></i> <?php echo $this->session->flashdata('success'); ?>
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                <?php endif; ?>

                <div class="alert alert-warning">
                    <h6><i class="fas fa-exclamation-triangle"></i> Catatan Penting</h6>
                    <p class="mb-0">Judul Skripsi Anda memerlukan revisi dari pembimbing. Silakan ajukan judul baru yang sudah ditandatangani oleh pembimbing.</p>
                </div>

                <!-- Judul Lama -->
                <div class="form-group">
                    <label>Judul Lama</label>
                    <div class="alert alert-secondary">
                        <p class="mb-0"><strong><?php echo isset($pengajuan->judul_skripsi) ? htmlspecialchars($pengajuan->judul_skripsi) : '-'; ?></strong></p>
                    </div>
                </div>

                <?php if (isset($pengajuan->catatan_revisi_p1) || isset($pengajuan->catatan_revisi_p2)): ?>
                    <div class="alert alert-info">
                        <h6><i class="fas fa-comment"></i> Catatan Revisi dari Pembimbing</h6>
                        <?php if (isset($pengajuan->catatan_revisi_p1) && $pengajuan->catatan_revisi_p1): ?>
                            <p class="mb-1"><strong>Pembimbing 1:</strong></p>
                            <p class="mb-2"><?php echo nl2br(htmlspecialchars($pengajuan->catatan_revisi_p1)); ?></p>
                        <?php endif; ?>
                        <?php if (isset($pengajuan->catatan_revisi_p2) && $pengajuan->catatan_revisi_p2): ?>
                            <p class="mb-1"><strong>Pembimbing 2:</strong></p>
                            <p class="mb-0"><?php echo nl2br(htmlspecialchars($pengajuan->catatan_revisi_p2)); ?></p>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>

                <!-- Judul Baru -->
                <div class="form-group">
                    <label for="judul_revisi">Judul Baru <span class="text-danger">*</span></label>
                    <textarea name="judul_revisi" id="judul_revisi" class="form-control" rows="3" required><?php echo set_value('judul_revisi', isset($pengajuan->judul_revisi) ? $pengajuan->judul_revisi : ''); ?></textarea>
                    <?php echo form_error('judul_revisi', '<small class="text-danger">', '</small>'); ?>
                    <small class="form-text text-muted">Masukkan judul Skripsi yang baru (minimal 10 karakter)</small>
                </div>

                <!-- Link Google Drive Bukti TTD Pembimbing -->
                <div class="form-group">
                    <label for="file_ttd_persetujuan">Berkas Bukti Perubahan Judul (TTD Pembimbing) <span class="text-danger">*</span></label>
                    <input type="file" name="file_ttd_persetujuan" id="file_ttd_persetujuan" class="form-control-file"
                           accept=".pdf,.doc,.docx,.jpg,.jpeg,.png" required>
                    <?php echo form_error('file_ttd_persetujuan', '<small class="text-danger">', '</small>'); ?>
                    <small class="form-text text-muted">
                        Masukkan link Google Drive untuk file PDF yang berisi bukti perubahan judul yang sudah ditandatangani oleh pembimbing. 
                        Pastikan file sudah di-share dengan akses "Anyone with the link can view".
                    </small>
                    <?php if (isset($perubahan->file_ttd_persetujuan) && !empty($perubahan->file_ttd_persetujuan)): ?>
                        <div class="alert alert-info mt-2">
                            <i class="fas fa-link"></i> Link saat ini: 
                            <?php 
                            $this->load->helper('google_drive');
                            echo google_drive_link_html($perubahan->file_ttd_persetujuan, 'Buka Berkas');
                            ?>
                        </div>
                    <?php endif; ?>
                </div>

                <div class="alert alert-info">
                    <i class="fas fa-info-circle"></i> 
                    <strong>Informasi:</strong> Setelah mengajukan perubahan judul, permintaan akan dikirim ke Sekprodi untuk validasi. 
                    Jika disetujui, judul Skripsi akan otomatis diperbarui.
                </div>
            </div>
            <div class="card-footer">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-paper-plane"></i> Ajukan Perubahan Judul
                </button>
                <a href="<?php echo site_url('Skripsi_pengajuan'); ?>" class="btn btn-secondary">
                    <i class="fas fa-times"></i> Batal
                </a>
            </div>
            <?php echo form_close(); ?>
        </div>
    </div>
</div>





