<div class="row">
    <div class="col-12">
        <div class="card card-primary">
            <div class="card-header">
                <h3 class="card-title">Edit Mahasiswa</h3>
            </div>
            <?php echo form_open('Mahasiswa/edit/' . $mahasiswa->id, array('method' => 'post')); ?>
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
                                   value="<?php echo set_value('nim', $mahasiswa->nim); ?>" 
                                   placeholder="202001001" required>
                            <small class="form-text text-muted">Jika NIM diubah, username dan password user account juga akan diupdate</small>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="nama_mahasiswa">Nama Mahasiswa <span class="text-danger">*</span></label>
                            <input type="text" name="nama_mahasiswa" id="nama_mahasiswa" class="form-control" 
                                   value="<?php echo set_value('nama_mahasiswa', $mahasiswa->nama_mahasiswa); ?>" 
                                   placeholder="Nama Lengkap" required>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="angkatan">Angkatan <span class="text-danger">*</span></label>
                            <input type="number" name="angkatan" id="angkatan" class="form-control" 
                                   value="<?php echo set_value('angkatan', $mahasiswa->angkatan); ?>" 
                                   min="2000" max="<?php echo date('Y') + 1; ?>" required>
                            <small class="form-text text-muted">Jika angkatan diubah, semester aktif akan dihitung ulang</small>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="kelas">Kelas</label>
                            <input type="text" name="kelas" id="kelas" class="form-control" 
                                   value="<?php echo set_value('kelas', $mahasiswa->kelas); ?>" 
                                   placeholder="1A, 1B, 1G" 
                                   pattern="[1-8][A-Z]" maxlength="2">
                            <small class="form-text text-muted">Format: {semester}{huruf}. Kosongkan untuk update otomatis saat semester berubah.</small>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="id_kurikulum">Kurikulum <span class="text-danger">*</span></label>
                            <select name="id_kurikulum" id="id_kurikulum" class="form-control" required>
                                <option value="">-- Pilih Kurikulum --</option>
                                <?php foreach ($kurikulum as $k): ?>
                                    <option value="<?php echo $k->id; ?>" 
                                            <?php echo set_select('id_kurikulum', $k->id, ($k->id == $mahasiswa->id_kurikulum)); ?>>
                                        <?php echo $k->nama_kurikulum . ' (' . $k->tahun_berlaku . ')'; ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="status">Status <span class="text-danger">*</span></label>
                            <select name="status" id="status" class="form-control" required>
                                <option value="aktif" <?php echo set_select('status', 'aktif', ($mahasiswa->status == 'aktif')); ?>>Aktif</option>
                                <option value="cuti" <?php echo set_select('status', 'cuti', ($mahasiswa->status == 'cuti')); ?>>Cuti</option>
                                <option value="lulus" <?php echo set_select('status', 'lulus', ($mahasiswa->status == 'lulus')); ?>>Lulus</option>
                                <option value="nonaktif" <?php echo set_select('status', 'nonaktif', ($mahasiswa->status == 'nonaktif')); ?>>Nonaktif</option>
                            </select>
                        </div>
                    </div>
                </div>

                <div class="alert alert-info">
                    <i class="fas fa-info-circle"></i> 
                    <strong>Informasi:</strong>
                    <ul class="mb-0 mt-2">
                        <li>Semester Aktif Saat Ini: <strong>Semester <?php echo $mahasiswa->semester_aktif; ?></strong></li>
                        <?php if ($mahasiswa->kelas): ?>
                            <li>Kelas Saat Ini: <strong><?php echo $mahasiswa->kelas; ?></strong></li>
                        <?php endif; ?>
                        <li>Jika kelas dikosongkan dan semester berubah, kelas akan otomatis diupdate (contoh: 1A -> 2A)</li>
                    </ul>
                </div>
            </div>
            <div class="card-footer">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i> Update
                </button>
                <a href="<?php echo site_url('Mahasiswa'); ?>" class="btn btn-secondary">
                    <i class="fas fa-times"></i> Batal
                </a>
            </div>
            <?php echo form_close(); ?>
        </div>
    </div>
</div>

