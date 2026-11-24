<div class="row">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Tambah Mata Kuliah Baru</h3>
            </div>
            <?php echo form_open('Mata_kuliah/create'); ?>
            <div class="card-body">
                <div class="form-group">
                    <label for="kode_mk">Kode Mata Kuliah <span class="text-danger">*</span></label>
                    <input type="text" name="kode_mk" id="kode_mk" class="form-control" 
                           placeholder="Contoh: MK001" value="<?php echo set_value('kode_mk'); ?>" 
                           required style="text-transform: uppercase;">
                    <small class="form-text text-muted">Kode harus unik dan tidak boleh duplikat.</small>
                    <?php echo form_error('kode_mk', '<div class="text-danger mt-1">', '</div>'); ?>
                </div>

                <div class="form-group">
                    <label for="nama_mk">Nama Mata Kuliah <span class="text-danger">*</span></label>
                    <input type="text" name="nama_mk" id="nama_mk" class="form-control" 
                           placeholder="Contoh: Pemrograman Dasar" value="<?php echo set_value('nama_mk'); ?>" required>
                    <?php echo form_error('nama_mk', '<div class="text-danger mt-1">', '</div>'); ?>
                </div>

                <div class="form-group">
                    <label for="sks">SKS (Satuan Kredit Semester) <span class="text-danger">*</span></label>
                    <input type="number" name="sks" id="sks" class="form-control" 
                           placeholder="Contoh: 3" value="<?php echo set_value('sks'); ?>" 
                           min="1" max="6" required>
                    <small class="form-text text-muted">Biasanya 1-6 SKS.</small>
                    <?php echo form_error('sks', '<div class="text-danger mt-1">', '</div>'); ?>
                </div>
            </div>
            <div class="card-footer">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i> Simpan
                </button>
                <a href="<?php echo site_url('Mata_kuliah'); ?>" class="btn btn-secondary">
                    <i class="fas fa-times"></i> Batal
                </a>
            </div>
            <?php echo form_close(); ?>
        </div>
    </div>
</div>

<script>
document.getElementById('kode_mk').addEventListener('input', function(e) {
    this.value = this.value.toUpperCase();
});
</script>
















