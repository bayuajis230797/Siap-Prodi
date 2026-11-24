<div class="row">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Tambah Kurikulum Baru</h3>
            </div>
            <?php echo form_open('Kurikulum/create'); ?>
            <div class="card-body">
                <div class="form-group">
                    <label for="nama_kurikulum">Nama Kurikulum <span class="text-danger">*</span></label>
                    <input type="text" name="nama_kurikulum" id="nama_kurikulum" class="form-control" 
                           value="<?php echo set_value('nama_kurikulum'); ?>" required>
                    <?php echo form_error('nama_kurikulum', '<small class="text-danger">', '</small>'); ?>
                </div>

                <div class="form-group">
                    <label for="tahun_berlaku">Tahun Berlaku <span class="text-danger">*</span></label>
                    <input type="number" name="tahun_berlaku" id="tahun_berlaku" class="form-control" 
                           value="<?php echo set_value('tahun_berlaku', date('Y')); ?>" min="2000" max="2100" required>
                    <?php echo form_error('tahun_berlaku', '<small class="text-danger">', '</small>'); ?>
                </div>
            </div>
            <div class="card-footer">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i> Simpan
                </button>
                <a href="<?php echo site_url('Kurikulum'); ?>" class="btn btn-secondary">
                    <i class="fas fa-times"></i> Batal
                </a>
            </div>
            <?php echo form_close(); ?>
        </div>
    </div>
</div>
















