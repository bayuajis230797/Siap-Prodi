<div class="row">
    <div class="col-12">
        <div class="card card-primary">
            <div class="card-header">
                <h3 class="card-title">Edit Mapping Mata Kuliah - Dosen</h3>
            </div>
            <?php echo form_open('Mapping_mk/edit/' . $mapping->id, array('method' => 'post')); ?>
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

                <?php if (isset($tahun_aktif) && $tahun_aktif): ?>
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle"></i> 
                        <strong>Tahun Akademik Aktif:</strong> <?php echo $tahun_aktif->tahun_akademik . ' ' . ucfirst($tahun_aktif->semester); ?>
                    </div>
                <?php endif; ?>

                <div class="row">
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="id_mata_kuliah_aktif">Mata Kuliah Aktif <span class="text-danger">*</span></label>
                            <select name="id_mata_kuliah_aktif" id="id_mata_kuliah_aktif" class="form-control" required>
                                <option value="">-- Pilih Mata Kuliah --</option>
                                <?php if (!empty($mata_kuliah_aktif)): ?>
                                    <?php foreach ($mata_kuliah_aktif as $mk): ?>
                                        <option value="<?php echo $mk->id; ?>" 
                                                <?php echo set_select('id_mata_kuliah_aktif', $mk->id, ($mk->id == $mapping->id_mata_kuliah_aktif)); ?>>
                                            <?php 
                                            $semester_display = isset($mk->semester) ? $mk->semester : (isset($mk->semester_aktif) ? $mk->semester_aktif : '-');
                                            echo $mk->kode_mk . ' - ' . $mk->nama_mk . ' (' . $mk->sks . ' SKS) - Semester ' . $semester_display . ' - ' . $mk->nama_kurikulum; 
                                            ?>
                                        </option>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </select>
                            <small class="form-text text-muted">Pilih mata kuliah aktif yang akan diampu oleh dosen</small>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="id_dosen">Dosen Pengampu <span class="text-danger">*</span></label>
                            <select name="id_dosen" id="id_dosen" class="form-control" required>
                                <option value="">-- Pilih Dosen --</option>
                                <?php if (!empty($dosen)): ?>
                                    <?php foreach ($dosen as $d): ?>
                                        <?php if ($d->status == 'aktif'): ?>
                                            <option value="<?php echo $d->id; ?>" 
                                                    <?php echo set_select('id_dosen', $d->id, ($d->id == $mapping->id_dosen)); ?>>
                                                <?php echo $d->nidn . ' - ' . $d->nama_dosen; ?>
                                            </option>
                                        <?php endif; ?>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </select>
                            <small class="form-text text-muted">Pilih dosen yang akan mengampu mata kuliah</small>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="kelas">Kelas</label>
                            <select name="kelas" id="kelas" class="form-control">
                                <option value="">-- Pilih Kelas (Opsional) --</option>
                                <?php if (!empty($kelas_list)): ?>
                                    <?php foreach ($kelas_list as $k): ?>
                                        <option value="<?php echo $k->kelas; ?>" 
                                                <?php echo set_select('kelas', $k->kelas, (isset($mapping->kelas) && $mapping->kelas == $k->kelas)); ?>>
                                            <?php echo $k->kelas; ?>
                                        </option>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </select>
                            <small class="form-text text-muted">Pilih kelas jika mapping spesifik per kelas (kosongkan jika untuk semua kelas)</small>
                        </div>
                    </div>
                </div>

                <div class="alert alert-info">
                    <i class="fas fa-info-circle"></i> 
                    <strong>Mapping Saat Ini:</strong>
                    <ul class="mb-0 mt-2">
                        <li>Mata Kuliah: <strong><?php echo $mapping->kode_mk . ' - ' . $mapping->nama_mk; ?></strong></li>
                        <li>Dosen: <strong><?php echo $mapping->nama_dosen . ' (NIDN: ' . $mapping->nidn . ')'; ?></strong></li>
                        <li>Kelas: <strong><?php echo (isset($mapping->kelas) && !empty($mapping->kelas)) ? $mapping->kelas : 'Semua Kelas'; ?></strong></li>
                    </ul>
                </div>
            </div>
            <div class="card-footer">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i> Simpan Perubahan
                </button>
                <a href="<?php echo site_url('Mapping_mk'); ?>" class="btn btn-secondary">
                    <i class="fas fa-times"></i> Batal
                </a>
            </div>
            <?php echo form_close(); ?>
        </div>
    </div>
</div>




