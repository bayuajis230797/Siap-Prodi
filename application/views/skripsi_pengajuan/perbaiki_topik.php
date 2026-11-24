<div class="card">
    <div class="card-header">
        <h3 class="card-title">Perbaiki Topik Bimbingan</h3>
    </div>
    <div class="card-body">
        <?php if ($this->session->flashdata('error')): ?>
            <div class="alert alert-danger"><?php echo $this->session->flashdata('error'); ?></div>
        <?php endif; ?>
        <?php if ($this->session->flashdata('success')): ?>
            <div class="alert alert-success"><?php echo $this->session->flashdata('success'); ?></div>
        <?php endif; ?>

        <div class="alert alert-warning">
            <i class="fas fa-exclamation-triangle"></i>
            <strong>Topik yang Direvisi:</strong> <?php echo htmlspecialchars($log_topik->pesan); ?>
            <?php if ($log_revisi_dosen && $log_revisi_dosen->keterangan_revisi): ?>
                <br><strong>Keterangan Revisi:</strong> <?php echo htmlspecialchars($log_revisi_dosen->keterangan_revisi); ?>
            <?php endif; ?>
        </div>

        <?php echo form_open_multipart('Skripsi_pengajuan/perbaiki_topik/' . $log_topik->id); ?>
            <div class="form-group">
                <label for="judul_topik">Judul/Topik Bimbingan yang Diperbaiki <span class="text-danger">*</span></label>
                <select name="judul_topik" id="judul_topik" class="form-control" required>
                    <option value="">-- Pilih topik bimbingan --</option>
                    <?php
                    $pesan_lama = isset($log_topik->pesan) ? trim((string) $log_topik->pesan) : '';
                    foreach (skripsi_topik_bimbingan_pilihan() as $topik_opt):
                    ?>
                        <option value="<?php echo htmlspecialchars($topik_opt); ?>" <?php echo set_select('judul_topik', $topik_opt, ($pesan_lama !== '' && $pesan_lama === $topik_opt)); ?>>
                            <?php echo htmlspecialchars($topik_opt); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <small class="form-text text-muted">
                    Pilih tahap laporan yang diperbaiki sesuai arahan pembimbing.
                </small>
            </div>

            <div class="form-group">
                <label for="file_bimbingan">File Perbaikan (Opsional)</label>
                <input type="file" name="file_bimbingan" id="file_bimbingan" class="form-control-file" 
                       accept=".pdf,.doc,.docx,.jpg,.jpeg,.png">
                <small class="form-text text-muted">
                    Upload file berkas skripsi yang sudah diperbaiki jika diperlukan (PDF, DOC, DOCX, JPG, PNG - maks. 10MB).
                </small>
            </div>

            <div class="form-group">
                <button type="submit" class="btn btn-warning">
                    <i class="fas fa-check"></i> Kirim Perbaikan
                </button>
                <a href="<?php echo site_url('Skripsi_pengajuan'); ?>" class="btn btn-secondary">
                    <i class="fas fa-times"></i> Batal
                </a>
            </div>
        <?php echo form_close(); ?>
    </div>
</div>

