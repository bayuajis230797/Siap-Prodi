<div class="row">
    <div class="col-md-8">
        <div class="card card-primary card-outline">
            <div class="card-header">
                <h3 class="card-title">
                    <?php echo isset($setting) ? 'Edit' : 'Tambah'; ?> Setting Radius Absensi PKL
                </h3>
            </div>
            <div class="card-body">
                <?php echo form_open(isset($setting) ? 'Pkl_setting_radius/update/' . $setting->id : 'Pkl_setting_radius/store', array('id' => 'formSetting')); ?>
                
                <?php if (validation_errors()): ?>
                    <div class="alert alert-danger">
                        <?php echo validation_errors(); ?>
                    </div>
                <?php endif; ?>

                <div class="form-group">
                    <label for="id_tahun_akademik">Tahun Akademik <span class="text-danger">*</span></label>
                    <?php if (isset($setting)): ?>
                        <input type="text" class="form-control" value="<?php echo htmlspecialchars($setting->tahun_akademik . ' - Semester ' . $setting->semester); ?>" readonly>
                        <input type="hidden" name="id_tahun_akademik" value="<?php echo $setting->id_tahun_akademik; ?>">
                    <?php else: ?>
                        <select name="id_tahun_akademik" id="id_tahun_akademik" class="form-control" required>
                            <option value="">-- Pilih Tahun Akademik --</option>
                            <?php if ($tahun_aktif): ?>
                                <option value="<?php echo $tahun_aktif->id; ?>" selected>
                                    <?php echo htmlspecialchars($tahun_aktif->tahun_akademik); ?> - Semester <?php echo $tahun_aktif->semester; ?> (Aktif)
                                </option>
                            <?php endif; ?>
                        </select>
                        <small class="text-muted">Pilih tahun akademik untuk setting radius ini</small>
                    <?php endif; ?>
                    <?php echo form_error('id_tahun_akademik', '<small class="text-danger">', '</small>'); ?>
                </div>

                <div class="form-group">
                    <label for="radius_meter">Radius (Meter) <span class="text-danger">*</span></label>
                    <input type="number" name="radius_meter" id="radius_meter" class="form-control" 
                           value="<?php echo set_value('radius_meter', isset($setting) ? $setting->radius_meter : 50); ?>" 
                           min="10" max="1000" required>
                    <small class="text-muted">Radius maksimal untuk absensi (10-1000 meter). Default: 50 meter</small>
                    <?php echo form_error('radius_meter', '<small class="text-danger">', '</small>'); ?>
                </div>

                <div class="alert alert-info">
                    <i class="fas fa-info-circle"></i> 
                    <strong>Catatan:</strong> Setting radius ini akan digunakan untuk validasi absensi harian PKL. 
                    Mahasiswa yang absen di luar radius akan tetap bisa absen, namun statusnya akan ditandai "di luar radius".
                </div>

                <div class="form-group">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Simpan
                    </button>
                    <a href="<?php echo site_url('Pkl_setting_radius'); ?>" class="btn btn-secondary">
                        <i class="fas fa-times"></i> Batal
                    </a>
                </div>

                <?php echo form_close(); ?>
            </div>
        </div>
    </div>

    <div class="col-md-4">
        <div class="card card-info card-outline">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-info-circle"></i> Informasi</h3>
            </div>
            <div class="card-body">
                <p><strong>Radius Absensi:</strong></p>
                <p class="text-muted">
                    Radius maksimal (dalam meter) yang diizinkan untuk absensi PKL. 
                    Sistem akan menghitung jarak dari koordinat tempat PKL ke koordinat saat mahasiswa absen.
                </p>
                <p><strong>Rekomendasi:</strong></p>
                <ul class="text-muted">
                    <li>Minimal: 10 meter</li>
                    <li>Maksimal: 1000 meter</li>
                    <li>Default: 50 meter</li>
                </ul>
                <p><strong>Status Absensi:</strong></p>
                <ul class="text-muted">
                    <li><span class="badge badge-success">Dalam Radius</span> - Absen dalam batas radius</li>
                    <li><span class="badge badge-danger">Di Luar Radius</span> - Absen di luar batas radius</li>
                </ul>
            </div>
        </div>
    </div>
</div>

