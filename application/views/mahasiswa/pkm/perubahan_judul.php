<?php $this->load->helper('pkm'); ?>
<div class="card card-primary">
    <div class="card-header"><h3 class="card-title">Ajukan Perubahan Judul PKM</h3></div>
    <form method="post" enctype="multipart/form-data">
        <div class="card-body">
            <p>Judul saat ini: <strong><?php echo htmlspecialchars($pengajuan->judul_pkm); ?></strong></p>
            <div class="form-group">
                <label>Judul Baru <span class="text-danger">*</span></label>
                <input type="text" name="judul_baru" class="form-control" required maxlength="500">
            </div>
            <div class="form-group">
                <label>Alasan</label>
                <textarea name="alasan" class="form-control" rows="3"></textarea>
            </div>
            <div class="form-group">
                <label>Bukti (opsional)</label>
                <input type="file" name="file_bukti" class="form-control-file" accept=".pdf,.doc,.docx,.jpg,.jpeg,.png">
                <small class="text-muted">Diunggah ke Google Drive institusi. Referensi disimpan terenkripsi.</small>
            </div>
        </div>
        <div class="card-footer">
            <button type="submit" class="btn btn-primary">Kirim ke Dosen</button>
            <a href="<?php echo site_url('Pkm_mahasiswa/detail/' . pkm_encoded_id($pengajuan->id)); ?>" class="btn btn-secondary">Batal</a>
        </div>
    </form>
</div>
