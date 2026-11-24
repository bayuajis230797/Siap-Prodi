<div class="row">
    <div class="col-md-8">
        <div class="card card-primary">
            <div class="card-header">
                <h3 class="card-title">Upload Berkas</h3>
            </div>
            <?php echo form_open_multipart('Manajemen_berkas/create'); ?>
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
                           value="<?php echo set_value('judul'); ?>" 
                           placeholder="Contoh: Template Persetujuan Judul Skripsi" required>
                    <small class="form-text text-muted">Masukkan judul atau nama berkas</small>
                </div>

                <div class="form-group">
                    <label for="kategori">Kategori (Opsional)</label>
                    <input type="text" name="kategori" id="kategori" class="form-control" 
                           value="<?php echo set_value('kategori'); ?>" 
                           placeholder="Contoh: Template, Dokumen, Formulir">
                    <small class="form-text text-muted">Kategori untuk memudahkan pengelompokan berkas</small>
                </div>

                <div class="form-group">
                    <label for="deskripsi">Deskripsi (Opsional)</label>
                    <textarea name="deskripsi" id="deskripsi" rows="3" class="form-control" 
                              placeholder="Masukkan deskripsi berkas jika diperlukan"><?php echo set_value('deskripsi'); ?></textarea>
                </div>

                <div class="form-group">
                    <label for="file_berkas">Berkas <span class="text-danger">*</span></label>
                    <input type="file" name="file_berkas" id="file_berkas" class="form-control-file" required>
                    <small class="form-text text-muted">Diunggah ke Google Drive sekprodi. Folder: SIAP/Manajemen_Berkas</small>
                </div>

                <div class="form-group">
                    <label for="nama_file">Nama File (Opsional)</label>
                    <input type="text" name="nama_file" id="nama_file" class="form-control" 
                           value="<?php echo set_value('nama_file'); ?>" 
                           placeholder="Contoh: Template_Persetujuan_Judul_Skripsi.pdf">
                    <small class="form-text text-muted">Nama file untuk referensi (opsional)</small>
                </div>
            </div>
            <div class="card-footer">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i> Simpan Berkas
                </button>
                <a href="<?php echo site_url('Manajemen_berkas'); ?>" class="btn btn-secondary">
                    <i class="fas fa-times"></i> Batal
                </a>
            </div>
            <?php echo form_close(); ?>
        </div>
    </div>
</div>











