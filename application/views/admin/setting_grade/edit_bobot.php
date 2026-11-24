<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Edit Bobot Nilai - <?php echo strtoupper($bobot->tipe); ?> (<?php echo ucfirst(str_replace('_', ' ', $bobot->komponen)); ?>)</h3>
            </div>
            <div class="card-body">
                <?php if (validation_errors()): ?>
                    <div class="alert alert-danger alert-dismissible">
                        <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
                        <?php echo validation_errors(); ?>
                    </div>
                <?php endif; ?>

                <form method="post" action="<?php echo site_url(($bobot->tipe === 'pkl' ? 'Setting_bobot_pkl' : 'Setting_bobot_skripsi') . '/edit_bobot/' . $bobot->id); ?>">
                    <div class="form-group">
                        <label>Tipe</label>
                        <input type="text" class="form-control" value="<?php echo strtoupper($bobot->tipe); ?>" disabled>
                    </div>

                    <div class="form-group">
                        <label>Komponen</label>
                        <input type="text" class="form-control" value="<?php echo ucfirst(str_replace('_', ' ', $bobot->komponen)); ?>" disabled>
                    </div>

                    <div class="form-group">
                        <label for="bobot">Bobot (%) <span class="text-danger">*</span></label>
                        <input type="number" step="0.01" min="0" max="100" class="form-control" id="bobot" name="bobot" 
                               value="<?php echo set_value('bobot', $bobot->bobot); ?>" required>
                        <small class="form-text text-muted">Bobot dalam persen (0-100). Pastikan total bobot semua komponen = 100%</small>
                    </div>

                    <div class="form-group">
                        <label for="keterangan">Keterangan</label>
                        <textarea class="form-control" id="keterangan" name="keterangan" rows="3"><?php echo set_value('keterangan', $bobot->keterangan); ?></textarea>
                    </div>

                    <div class="form-group">
                        <div class="form-check">
                            <input type="checkbox" class="form-check-input" id="is_aktif" name="is_aktif" value="1" 
                                   <?php echo ($bobot->is_aktif == 1) ? 'checked' : ''; ?>>
                            <label class="form-check-label" for="is_aktif">Aktif</label>
                        </div>
                    </div>

                    <?php if ($bobot->tipe === 'skripsi'): ?>
                        <div class="alert alert-warning">
                            <strong>Peringatan:</strong> Pastikan total bobot semua komponen (bimbingan + sidang) = 100%
                        </div>
                    <?php endif; ?>

                    <div class="form-group">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> Simpan
                        </button>
                        <a href="<?php echo site_url(($bobot->tipe === 'pkl' ? 'Setting_bobot_pkl' : 'Setting_bobot_skripsi')); ?>" class="btn btn-secondary">
                            <i class="fas fa-times"></i> Batal
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

