<div class="row">
    <div class="col-12">
        <div class="card card-primary">
            <div class="card-header">
                <h3 class="card-title">Tambah Tenaga Kependidikan</h3>
            </div>
            <?php echo form_open('Tendik/create', array('method' => 'post')); ?>
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
                            <label for="nipy">NIPY <span class="text-danger">*</span></label>
                            <input type="text" name="nipy" id="nipy" class="form-control" 
                                   value="<?php echo set_value('nipy'); ?>" 
                                   placeholder="T001" required>
                            <small class="form-text text-muted">NIPY akan digunakan sebagai username dan password default untuk login</small>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="nama_tendik">Nama Tenaga Kependidikan <span class="text-danger">*</span></label>
                            <input type="text" name="nama_tendik" id="nama_tendik" class="form-control" 
                                   value="<?php echo set_value('nama_tendik'); ?>" 
                                   placeholder="Nama Lengkap" required>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="email">Email</label>
                            <input type="email" name="email" id="email" class="form-control" 
                                   value="<?php echo set_value('email'); ?>" 
                                   placeholder="tendik@example.com">
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="jabatan">Jabatan</label>
                            <input type="text" name="jabatan" id="jabatan" class="form-control" 
                                   value="<?php echo set_value('jabatan'); ?>" 
                                   placeholder="Administrator, Staff, dll">
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="status">Status <span class="text-danger">*</span></label>
                            <select name="status" id="status" class="form-control" required>
                                <option value="aktif" <?php echo set_select('status', 'aktif', true); ?>>Aktif</option>
                                <option value="nonaktif" <?php echo set_select('status', 'nonaktif'); ?>>Nonaktif</option>
                            </select>
                        </div>
                    </div>
                </div>

                <div class="alert alert-info">
                    <i class="fas fa-info-circle"></i> 
                    <strong>Catatan:</strong> User account akan otomatis dibuat dengan username dan password sama dengan NIPY.
                </div>
            </div>
            <div class="card-footer">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i> Simpan
                </button>
                <a href="<?php echo site_url('Tendik'); ?>" class="btn btn-secondary">
                    <i class="fas fa-times"></i> Batal
                </a>
            </div>
            <?php echo form_close(); ?>
        </div>
    </div>
</div>
















