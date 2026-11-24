<div class="row">
    <div class="col-md-8">
        <div class="card card-primary">
            <div class="card-header">
                <h3 class="card-title">Edit Berkas</h3>
            </div>
            <?php echo form_open_multipart('Manajemen_berkas/edit/' . $berkas->id); ?>
            <div class="card-body">
                <?php if (validation_errors()): ?>
                    <div class="alert alert-danger">
                        <?php echo validation_errors(); ?>
                    </div>
                <?php endif; ?>

                <?php if ($this->session->flashdata('error')): ?>
                    <div class="alert alert-danger">
                        <?php echo $this->session->flashdata('error'); ?>
                    </div>
                <?php endif; ?>

                <div class="form-group">
                    <label for="judul">Judul Berkas <span class="text-danger">*</span></label>
                    <input type="text" name="judul" id="judul" class="form-control" 
                           value="<?php echo set_value('judul', $berkas->judul); ?>" required>
                </div>

                <div class="form-group">
                    <label for="kategori">Kategori (Opsional)</label>
                    <input type="text" name="kategori" id="kategori" class="form-control" 
                           value="<?php echo set_value('kategori', $berkas->kategori); ?>">
                </div>

                <div class="form-group">
                    <label for="deskripsi">Deskripsi (Opsional)</label>
                    <textarea name="deskripsi" id="deskripsi" rows="3" class="form-control"><?php echo set_value('deskripsi', $berkas->deskripsi); ?></textarea>
                </div>

                <div class="form-group">
                    <label>Link Saat Ini</label>
                    <?php if (!empty($berkas->file_path)): ?>
                        <div class="alert alert-info">
                            <i class="fas fa-link"></i> 
                            <a href="<?php echo htmlspecialchars($berkas->file_path); ?>" target="_blank" class="text-primary">
                                <strong>Buka Link</strong>
                            </a>
                            <br>
                            <small class="text-muted"><?php echo htmlspecialchars($berkas->file_path); ?></small>
                        </div>
                    <?php else: ?>
                        <div class="alert alert-warning">
                            <i class="fas fa-exclamation-triangle"></i> Belum ada link
                        </div>
                    <?php endif; ?>
                </div>

                <div class="form-group">
                    <label for="file_berkas">Ganti Berkas (opsional)</label>
                    <?php $this->load->helper('google_drive'); ?>
                    <?php if (!empty($berkas->file_path) && strpos($berkas->file_path, 'http') === 0): ?>
                        <p class="small mb-2"><?php echo google_drive_link_html($berkas->file_path, htmlspecialchars($berkas->file_name ?: 'Buka berkas')); ?></p>
                    <?php endif; ?>
                    <input type="file" name="file_berkas" id="file_berkas" class="form-control-file">
                    <small class="form-text text-muted">Kosongkan jika tidak mengganti file.</small>
                </div>

                <div class="form-group">
                    <label for="nama_file">Nama File (Opsional)</label>
                    <input type="text" name="nama_file" id="nama_file" class="form-control" 
                           value="<?php echo set_value('nama_file', $berkas->file_name); ?>" 
                           placeholder="Contoh: Template_Persetujuan_Judul_Skripsi.pdf">
                    <small class="form-text text-muted">Nama file untuk referensi (opsional)</small>
                </div>

                <div class="form-group">
                    <div class="form-check">
                        <input type="checkbox" name="is_active" id="is_active" class="form-check-input" 
                               value="1" <?php echo ($berkas->is_active == 1) ? 'checked' : ''; ?>>
                        <label class="form-check-label" for="is_active">
                            Aktif (Berkas dapat dilihat oleh mahasiswa)
                        </label>
                    </div>
                </div>
            </div>
            <div class="card-footer">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i> Simpan Perubahan
                </button>
                <a href="<?php echo site_url('Manajemen_berkas'); ?>" class="btn btn-secondary">
                    <i class="fas fa-times"></i> Batal
                </a>
            </div>
            <?php echo form_close(); ?>
        </div>
    </div>
</div>











