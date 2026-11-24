<div class="row">
    <div class="col-12">
        <div class="card card-primary">
            <div class="card-header">
                <h3 class="card-title">Tambah Dosen</h3>
            </div>
            <?php echo form_open('Dosen/create', array('method' => 'post')); ?>
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
                            <label for="nidn">NIDN <span class="text-danger">*</span></label>
                            <input type="text" name="nidn" id="nidn" class="form-control" 
                                   value="<?php echo set_value('nidn'); ?>" 
                                   placeholder="0123456789" required>
                            <small class="form-text text-muted">NIDN akan digunakan sebagai username dan password default untuk login</small>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="nama_dosen">Nama Dosen <span class="text-danger">*</span></label>
                            <input type="text" name="nama_dosen" id="nama_dosen" class="form-control" 
                                   value="<?php echo set_value('nama_dosen'); ?>" 
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
                                   placeholder="dosen@example.com">
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="jabatan_fungsional">Jabatan Fungsional <span class="text-danger">*</span></label>
                            <select name="jabatan_fungsional" id="jabatan_fungsional" class="form-control" required>
                                <option value="Asisten Ahli" <?php echo set_select('jabatan_fungsional', 'Asisten Ahli', true); ?>>Asisten Ahli</option>
                                <option value="Lektor" <?php echo set_select('jabatan_fungsional', 'Lektor'); ?>>Lektor</option>
                                <option value="Lektor Kepala" <?php echo set_select('jabatan_fungsional', 'Lektor Kepala'); ?>>Lektor Kepala</option>
                                <option value="Guru Besar (Profesor)" <?php echo set_select('jabatan_fungsional', 'Guru Besar (Profesor)'); ?>>Guru Besar (Profesor)</option>
                            </select>
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

                <div class="row">
                    <div class="col-md-12">
                        <div class="form-group">
                            <div class="form-check">
                                <input type="checkbox" name="is_pembimbing_skripsi" id="is_pembimbing_skripsi" value="1" 
                                       class="form-check-input" <?php echo set_checkbox('is_pembimbing_skripsi', '1'); ?>>
                                <label class="form-check-label" for="is_pembimbing_skripsi">
                                    <strong>Dapat Menjadi Pembimbing Skripsi</strong>
                                </label>
                            </div>
                            <small class="form-text text-muted">Centang jika dosen ini dapat dipilih sebagai pembimbing skripsi oleh mahasiswa</small>
                        </div>
                    </div>
                </div>

                <div class="alert alert-info">
                    <i class="fas fa-info-circle"></i> 
                    <strong>Catatan:</strong> User account akan otomatis dibuat dengan username dan password sama dengan NIDN.
                </div>
            </div>
            <div class="card-footer">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i> Simpan
                </button>
                <a href="<?php echo site_url('Dosen'); ?>" class="btn btn-secondary">
                    <i class="fas fa-times"></i> Batal
                </a>
            </div>
            <?php echo form_close(); ?>
        </div>
    </div>
</div>






