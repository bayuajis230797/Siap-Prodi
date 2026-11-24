<div class="row">
    <div class="col-12">
        <div class="card card-primary">
            <div class="card-header">
                <h3 class="card-title">Tambah Ruang Kelas</h3>
            </div>
            <?php echo form_open('Ruang_kelas/create', array('method' => 'post')); ?>
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
                            <label for="kode_ruang">Kode Ruang <span class="text-danger">*</span></label>
                            <input type="text" name="kode_ruang" id="kode_ruang" class="form-control" 
                                   value="<?php echo set_value('kode_ruang'); ?>" 
                                   placeholder="R001" required>
                            <small class="form-text text-muted">Kode ruang harus unik</small>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="nama_ruang">Nama Ruang <span class="text-danger">*</span></label>
                            <input type="text" name="nama_ruang" id="nama_ruang" class="form-control" 
                                   value="<?php echo set_value('nama_ruang'); ?>" 
                                   placeholder="Ruang Kelas 1" required>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="kapasitas">Kapasitas</label>
                            <input type="number" name="kapasitas" id="kapasitas" class="form-control" 
                                   value="<?php echo set_value('kapasitas'); ?>" 
                                   placeholder="40" min="0">
                            <small class="form-text text-muted">Jumlah maksimal orang</small>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="jenis_ruang">Jenis Ruang <span class="text-danger">*</span></label>
                            <select name="jenis_ruang" id="jenis_ruang" class="form-control" required>
                                <option value="Kelas" <?php echo set_select('jenis_ruang', 'Kelas', true); ?>>Kelas</option>
                                <option value="Laboratorium" <?php echo set_select('jenis_ruang', 'Laboratorium'); ?>>Laboratorium</option>
                                <option value="Aula" <?php echo set_select('jenis_ruang', 'Aula'); ?>>Aula</option>
                                <option value="Ruang Ujian" <?php echo set_select('jenis_ruang', 'Ruang Ujian'); ?>>Ruang Ujian</option>
                                <option value="Lainnya" <?php echo set_select('jenis_ruang', 'Lainnya'); ?>>Lainnya</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="status">Status <span class="text-danger">*</span></label>
                            <select name="status" id="status" class="form-control" required>
                                <option value="aktif" <?php echo set_select('status', 'aktif', true); ?>>Aktif</option>
                                <option value="nonaktif" <?php echo set_select('status', 'nonaktif'); ?>>Nonaktif</option>
                                <option value="maintenance" <?php echo set_select('status', 'maintenance'); ?>>Maintenance</option>
                            </select>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-12">
                        <div class="form-group">
                            <label for="lokasi">Lokasi</label>
                            <input type="text" name="lokasi" id="lokasi" class="form-control" 
                                   value="<?php echo set_value('lokasi'); ?>" 
                                   placeholder="Gedung A Lantai 1">
                            <small class="form-text text-muted">Contoh: Gedung A Lantai 1</small>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-12">
                        <div class="form-group">
                            <label for="fasilitas">Fasilitas</label>
                            <textarea name="fasilitas" id="fasilitas" class="form-control" rows="3" 
                                      placeholder="AC, Proyektor, Papan Tulis, dll"><?php echo set_value('fasilitas'); ?></textarea>
                            <small class="form-text text-muted">Daftar fasilitas yang tersedia di ruang ini</small>
                        </div>
                    </div>
                </div>
            </div>
            <div class="card-footer">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i> Simpan
                </button>
                <a href="<?php echo site_url('Ruang_kelas'); ?>" class="btn btn-secondary">
                    <i class="fas fa-times"></i> Batal
                </a>
            </div>
            <?php echo form_close(); ?>
        </div>
    </div>
</div>
















