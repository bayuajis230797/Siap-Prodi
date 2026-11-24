<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Tambah Grade Setting - <?php echo strtoupper($tipe); ?></h3>
            </div>
            <div class="card-body">
                <?php if (validation_errors()): ?>
                    <div class="alert alert-danger alert-dismissible">
                        <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
                        <?php echo validation_errors(); ?>
                    </div>
                <?php endif; ?>

                <form method="post" action="<?php echo site_url('Setting_grade/create_grade?tipe=' . $tipe); ?>">
                    <input type="hidden" name="tipe" value="<?php echo $tipe; ?>">

                    <div class="form-group">
                        <label for="tipe">Tipe</label>
                        <input type="text" class="form-control" value="<?php echo strtoupper($tipe); ?>" disabled>
                        <small class="form-text text-muted">Tipe: PKL atau Skripsi</small>
                    </div>

                    <div class="form-group">
                        <label for="grade">Grade <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="grade" name="grade" 
                               value="<?php echo set_value('grade'); ?>" 
                               placeholder="Contoh: A, B, AB, -A, A-, B+, dll" 
                               maxlength="10" required>
                        <small class="form-text text-muted">Grade bisa berupa: A, B, C, D, E, AB, -A, A-, B+, dll (maks 10 karakter)</small>
                        <?php echo form_error('grade', '<small class="text-danger">', '</small>'); ?>
                    </div>

                    <div class="form-group">
                        <label for="nilai_min">Nilai Minimum <span class="text-danger">*</span></label>
                        <input type="number" step="0.01" class="form-control" id="nilai_min" name="nilai_min" 
                               value="<?php echo set_value('nilai_min'); ?>" required>
                        <small class="form-text text-muted">Nilai minimum untuk grade ini</small>
                        <?php echo form_error('nilai_min', '<small class="text-danger">', '</small>'); ?>
                    </div>

                    <div class="form-group">
                        <label for="nilai_max">Nilai Maksimum</label>
                        <input type="number" step="0.01" class="form-control" id="nilai_max" name="nilai_max" 
                               value="<?php echo set_value('nilai_max'); ?>">
                        <small class="form-text text-muted">Kosongkan untuk grade tertinggi. Nilai maksimum akan dianggap tak terhingga.</small>
                        <?php echo form_error('nilai_max', '<small class="text-danger">', '</small>'); ?>
                    </div>

                    <div class="form-group">
                        <label for="keterangan">Keterangan</label>
                        <input type="text" class="form-control" id="keterangan" name="keterangan" 
                               value="<?php echo set_value('keterangan'); ?>" 
                               placeholder="Contoh: Sangat Baik, Baik, Cukup, dll">
                        <small class="form-text text-muted">Keterangan untuk grade ini</small>
                    </div>

                    <div class="form-group">
                        <div class="form-check">
                            <input type="checkbox" class="form-check-input" id="is_aktif" name="is_aktif" value="1" 
                                   <?php echo set_checkbox('is_aktif', '1', true); ?>>
                            <label class="form-check-label" for="is_aktif">Aktif</label>
                        </div>
                        <small class="form-text text-muted">Centang untuk mengaktifkan grade ini</small>
                    </div>

                    <div class="alert alert-info">
                        <strong>Catatan:</strong>
                        <ul class="mb-0">
                            <li>Grade harus unik per tipe (PKL/Skripsi)</li>
                            <li>Range nilai tidak boleh overlap dengan grade lain</li>
                            <li>Urutkan dari nilai tertinggi ke terendah</li>
                        </ul>
                    </div>

                    <div class="form-group">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> Simpan
                        </button>
                        <a href="<?php echo site_url('Setting_grade?tipe=' . $tipe); ?>" class="btn btn-secondary">
                            <i class="fas fa-times"></i> Batal
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>







