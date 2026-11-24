<div class="row">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Tambah Tahun Akademik Baru</h3>
            </div>
            <?php echo form_open('Tahun_akademik/create'); ?>
            <div class="card-body">
                <div class="form-group">
                    <label for="tahun_akademik">Tahun Akademik <span class="text-danger">*</span></label>
                    <input type="text" name="tahun_akademik" id="tahun_akademik" class="form-control" 
                           placeholder="Contoh: 2024/2025" value="<?php echo set_value('tahun_akademik'); ?>" required>
                    <?php echo form_error('tahun_akademik', '<small class="text-danger">', '</small>'); ?>
                </div>

                <div class="form-group">
                    <label for="semester">Semester <span class="text-danger">*</span></label>
                    <select name="semester" id="semester" class="form-control" required>
                        <option value="">-- Pilih Semester --</option>
                        <option value="ganjil" <?php echo set_select('semester', 'ganjil'); ?>>Ganjil</option>
                        <option value="genap" <?php echo set_select('semester', 'genap'); ?>>Genap</option>
                    </select>
                    <?php echo form_error('semester', '<small class="text-danger">', '</small>'); ?>
                </div>

                <div class="form-group">
                    <label for="tgl_mulai">Tanggal Mulai <span class="text-danger">*</span></label>
                    <input type="date" name="tgl_mulai" id="tgl_mulai" class="form-control" 
                           value="<?php echo set_value('tgl_mulai'); ?>" required>
                    <?php echo form_error('tgl_mulai', '<small class="text-danger">', '</small>'); ?>
                </div>

                <div class="form-group">
                    <label for="tgl_selesai">Tanggal Selesai <span class="text-danger">*</span></label>
                    <input type="date" name="tgl_selesai" id="tgl_selesai" class="form-control" 
                           value="<?php echo set_value('tgl_selesai'); ?>" required>
                    <?php echo form_error('tgl_selesai', '<small class="text-danger">', '</small>'); ?>
                </div>
            </div>
            <div class="card-footer">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i> Simpan
                </button>
                <a href="<?php echo site_url('Tahun_akademik'); ?>" class="btn btn-secondary">
                    <i class="fas fa-times"></i> Batal
                </a>
            </div>
            <?php echo form_close(); ?>
        </div>
    </div>
</div>
















