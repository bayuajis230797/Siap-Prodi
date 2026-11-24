<div class="row">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Perubahan Judul PKL</h3>
                <div class="card-tools">
                    <a href="<?php echo site_url('Pkl/submit'); ?>" class="btn btn-secondary btn-sm">
                        <i class="fas fa-arrow-left"></i> Kembali
                    </a>
                </div>
            </div>
            <?php echo form_open_multipart('Pkl/perubahan_judul'); ?>
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

                <div class="alert alert-warning">
                    <h6><i class="fas fa-exclamation-triangle"></i> Catatan Penting</h6>
                    <p class="mb-0">Judul PKL Anda memerlukan revisi dari pembimbing. Silakan ajukan judul baru yang sudah ditandatangani oleh pembimbing.</p>
                </div>

                <!-- Judul Lama -->
                <div class="form-group">
                    <label>Judul Lama</label>
                    <div class="alert alert-secondary">
                        <p class="mb-0"><strong><?php echo htmlspecialchars($pengajuan->judul_pkl); ?></strong></p>
                    </div>
                </div>

                <?php if ($pengajuan->catatan_revisi): ?>
                    <div class="alert alert-info">
                        <h6><i class="fas fa-comment"></i> Catatan Revisi dari Pembimbing</h6>
                        <p class="mb-0"><?php echo nl2br(htmlspecialchars($pengajuan->catatan_revisi)); ?></p>
                    </div>
                <?php endif; ?>

                <!-- Judul Baru -->
                <div class="form-group">
                    <label for="judul_revisi">Judul Baru <span class="text-danger">*</span></label>
                    <textarea name="judul_revisi" id="judul_revisi" class="form-control" rows="3" required><?php echo set_value('judul_revisi', $judul_revisi); ?></textarea>
                    <?php echo form_error('judul_revisi', '<small class="text-danger">', '</small>'); ?>
                    <small class="form-text text-muted">Masukkan judul PKL yang baru (minimal 10 karakter)</small>
                </div>

                <!-- File Bukti TTD Pembimbing -->
                <div class="form-group">
                    <label for="file_approve_revisi">Bukti Perubahan Judul (TTD Pembimbing) <span class="text-danger">*</span></label>
                    <input type="file" name="file_approve_revisi" id="file_approve_revisi" class="form-control-file" accept=".pdf" required>
                    <?php echo form_error('file_approve_revisi', '<small class="text-danger">', '</small>'); ?>
                    <small class="form-text text-muted">PDF bukti TTD pembimbing. Diunggah ke Google Drive (maks. 5MB).</small>
                    <?php if ($pengajuan->file_approve_revisi): ?>
                        <?php $this->load->helper('google_drive'); ?>
                        <div class="alert alert-info mt-2">
                            <i class="fas fa-file-pdf"></i> Bukti saat ini:
                            <?php if (strpos($pengajuan->file_approve_revisi, 'http') === 0): ?>
                                <?php echo google_drive_link_html($pengajuan->file_approve_revisi, 'Buka Berkas'); ?>
                            <?php else: ?>
                                <a href="<?php echo site_url('Pkl/download_approve_revisi/' . $pengajuan->id); ?>" class="btn btn-sm btn-info ml-2"><i class="fas fa-download"></i> Download</a>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>
                </div>

                <div class="alert alert-info">
                    <i class="fas fa-info-circle"></i> 
                    <strong>Informasi:</strong> Setelah mengajukan perubahan judul, permintaan akan dikirim ke Sekprodi untuk validasi. 
                    Jika disetujui, judul PKL akan otomatis diperbarui.
                </div>
            </div>
            <div class="card-footer">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-paper-plane"></i> Ajukan Perubahan Judul
                </button>
                <a href="<?php echo site_url('Pkl/submit'); ?>" class="btn btn-secondary">
                    <i class="fas fa-times"></i> Batal
                </a>
            </div>
            <?php echo form_close(); ?>
        </div>
    </div>
</div>





