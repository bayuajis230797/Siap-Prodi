<div class="row">
    <div class="col-12">
        <div class="card card-primary">
            <div class="card-header">
                <h3 class="card-title">Tambah Mahasiswa</h3>
            </div>
            <?php echo form_open('Mahasiswa/create', array('method' => 'post')); ?>
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

                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="nim">NIM <span class="text-danger">*</span></label>
                            <input type="text" name="nim" id="nim" class="form-control" 
                                   value="<?php echo set_value('nim'); ?>" 
                                   placeholder="202001001" required>
                            <small class="form-text text-muted">NIM akan digunakan sebagai username dan password default untuk login</small>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="nama_mahasiswa">Nama Mahasiswa <span class="text-danger">*</span></label>
                            <input type="text" name="nama_mahasiswa" id="nama_mahasiswa" class="form-control" 
                                   value="<?php echo set_value('nama_mahasiswa'); ?>" 
                                   placeholder="Nama Lengkap" required>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="angkatan">Angkatan <span class="text-danger">*</span></label>
                            <input type="number" name="angkatan" id="angkatan" class="form-control" 
                                   value="<?php echo set_value('angkatan', date('Y')); ?>" 
                                   min="2000" max="<?php echo date('Y') + 1; ?>" required>
                            <small class="form-text text-muted">Tahun masuk mahasiswa</small>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="kelas">Kelas</label>
                            <input type="text" name="kelas" id="kelas" class="form-control" 
                                   value="<?php echo set_value('kelas'); ?>" 
                                   placeholder="1A, 1B, 1G (otomatis jika kosong)" 
                                   pattern="[1-8][A-Z]" maxlength="2">
                            <small class="form-text text-muted">Format: {semester}{huruf} (contoh: 1A, 1B, 1G untuk karyawan). Kosongkan untuk generate otomatis.</small>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="id_kurikulum">Kurikulum <span class="text-danger">*</span></label>
                            <select name="id_kurikulum" id="id_kurikulum" class="form-control" required>
                                <option value="">-- Pilih Kurikulum --</option>
                                <?php foreach ($kurikulum as $k): ?>
                                    <option value="<?php echo $k->id; ?>" <?php echo set_select('id_kurikulum', $k->id); ?>>
                                        <?php echo $k->nama_kurikulum . ' (' . $k->tahun_berlaku . ')'; ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <small class="form-text text-muted">Kurikulum yang digunakan mahasiswa</small>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="status">Status <span class="text-danger">*</span></label>
                            <select name="status" id="status" class="form-control" required>
                                <option value="aktif" <?php echo set_select('status', 'aktif', true); ?>>Aktif</option>
                                <option value="cuti" <?php echo set_select('status', 'cuti'); ?>>Cuti</option>
                                <option value="lulus" <?php echo set_select('status', 'lulus'); ?>>Lulus</option>
                                <option value="nonaktif" <?php echo set_select('status', 'nonaktif'); ?>>Nonaktif</option>
                            </select>
                        </div>
                    </div>
                </div>

                <div class="alert alert-info">
                    <i class="fas fa-info-circle"></i> 
                    <strong>Catatan:</strong>
                    <ul class="mb-0 mt-2">
                        <li>Semester aktif akan otomatis dihitung berdasarkan angkatan dan tahun akademik aktif</li>
                        <li>Kelas akan otomatis di-generate jika dikosongkan (format: {semester}{huruf}, contoh: 1A, 1B, 1G untuk karyawan)</li>
                        <li>User account akan otomatis dibuat dengan username dan password sama dengan NIM</li>
                        <li>Ketika tahun akademik berubah, semester dan kelas akan otomatis mengikuti (1A -> 2A, 1B -> 2B, dll)</li>
                    </ul>
                </div>
            </div>
            <div class="card-footer">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i> Simpan
                </button>
                <a href="<?php echo site_url('Mahasiswa'); ?>" class="btn btn-secondary">
                    <i class="fas fa-times"></i> Batal
                </a>
            </div>
            <?php echo form_close(); ?>
        </div>
    </div>
</div>

