<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Edit Grade Setting - <?php echo strtoupper($grade->tipe); ?> (<?php echo $grade->grade; ?>)</h3>
            </div>
            <div class="card-body">
                <?php if (validation_errors()): ?>
                    <div class="alert alert-danger alert-dismissible">
                        <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
                        <?php echo validation_errors(); ?>
                    </div>
                <?php endif; ?>

                <form method="post" action="<?php echo site_url('Setting_grade/edit_grade/' . $grade->id); ?>">
                    <div class="form-group">
                        <label>Tipe</label>
                        <input type="text" class="form-control" value="<?php echo strtoupper($grade->tipe); ?>" disabled>
                    </div>

                    <div class="form-group">
                        <label for="grade">Grade <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="grade" name="grade" 
                               value="<?php echo set_value('grade', $grade->grade); ?>" 
                               maxlength="10" required>
                        <small class="form-text text-muted">Grade bisa berupa: A, B, C, D, E, AB, -A, A-, B+, dll (maks 10 karakter)</small>
                        <?php echo form_error('grade', '<small class="text-danger">', '</small>'); ?>
                    </div>
                    <input type="hidden" name="tipe" value="<?php echo $grade->tipe; ?>">
                    <input type="hidden" name="id" value="<?php echo $grade->id; ?>">

                    <div class="form-group">
                        <label for="nilai_min">Nilai Minimum <span class="text-danger">*</span></label>
                        <input type="number" step="0.01" class="form-control" id="nilai_min" name="nilai_min" 
                               value="<?php echo set_value('nilai_min', $grade->nilai_min); ?>" required>
                        <small class="form-text text-muted">Nilai minimum untuk grade ini</small>
                    </div>

                    <div class="form-group">
                        <label for="nilai_max">Nilai Maksimum</label>
                        <input type="number" step="0.01" class="form-control" id="nilai_max" name="nilai_max" 
                               value="<?php echo set_value('nilai_max', $grade->nilai_max); ?>">
                        <small class="form-text text-muted">Kosongkan untuk grade tertinggi (A). Nilai maksimum akan dianggap tak terhingga.</small>
                    </div>

                    <div class="form-group">
                        <label for="keterangan">Keterangan</label>
                        <input type="text" class="form-control" id="keterangan" name="keterangan" 
                               value="<?php echo set_value('keterangan', $grade->keterangan); ?>" 
                               placeholder="Contoh: Sangat Baik, Baik, Cukup, dll">
                    </div>

                    <div class="form-group">
                        <div class="form-check">
                            <input type="checkbox" class="form-check-input" id="is_aktif" name="is_aktif" value="1" 
                                   <?php echo ($grade->is_aktif == 1) ? 'checked' : ''; ?>>
                            <label class="form-check-label" for="is_aktif">Aktif</label>
                        </div>
                    </div>

                    <div class="form-group">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> Simpan
                        </button>
                        <a href="<?php echo site_url('Setting_grade?tipe=' . $grade->tipe); ?>" class="btn btn-secondary">
                            <i class="fas fa-times"></i> Batal
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

