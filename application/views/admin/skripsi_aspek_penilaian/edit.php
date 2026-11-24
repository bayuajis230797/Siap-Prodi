<div class="row">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Edit Aspek Penilaian Skripsi</h3>
            </div>
            <?php echo form_open('Skripsi_aspek_penilaian/edit/' . $aspek->id); ?>
            <div class="card-body">
                <?php if ($this->session->flashdata('error')): ?>
                    <div class="alert alert-danger alert-dismissible">
                        <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
                        <?php echo $this->session->flashdata('error'); ?>
                    </div>
                <?php endif; ?>

                <div class="form-group">
                    <label for="kode_aspek">Kode Aspek <span class="text-danger">*</span></label>
                    <input type="text" name="kode_aspek" id="kode_aspek" class="form-control" 
                           value="<?php echo set_value('kode_aspek', $aspek->kode_aspek); ?>" 
                           placeholder="Contoh: P1, P2" required>
                    <?php echo form_error('kode_aspek', '<small class="text-danger">', '</small>'); ?>
                    <small class="form-text text-muted">Kode unik untuk aspek penilaian (mis: P1 untuk Nilai Bimbingan, P2 untuk Nilai Sidang)</small>
                </div>

                <div class="form-group">
                    <label for="nama_aspek">Nama Aspek <span class="text-danger">*</span></label>
                    <input type="text" name="nama_aspek" id="nama_aspek" class="form-control" 
                           value="<?php echo set_value('nama_aspek', $aspek->nama_aspek); ?>" required>
                    <?php echo form_error('nama_aspek', '<small class="text-danger">', '</small>'); ?>
                    <small class="form-text text-muted">Nama lengkap aspek penilaian</small>
                </div>

                <div class="form-group">
                    <label for="bobot">Bobot (%)</label>
                    <input type="number" name="bobot" id="bobot" class="form-control" 
                           value="<?php echo set_value('bobot', $aspek->bobot); ?>" 
                           step="0.01" min="0" max="100">
                    <?php echo form_error('bobot', '<small class="text-danger">', '</small>'); ?>
                    <small class="form-text text-muted">Bobot aspek penilaian (0-100)</small>
                </div>

                <div class="form-group">
                    <label for="ditunjukkan_untuk">Ditunjukkan Untuk <span class="text-danger">*</span></label>
                    <select name="ditunjukkan_untuk" id="ditunjukkan_untuk" class="form-control" required>
                        <option value="">Pilih Penilai</option>
                        <option value="pembimbing" <?php echo set_select('ditunjukkan_untuk', 'pembimbing', $aspek->ditunjukkan_untuk === 'pembimbing'); ?>>
                            Pembimbing
                        </option>
                        <option value="penguji" <?php echo set_select('ditunjukkan_untuk', 'penguji', $aspek->ditunjukkan_untuk === 'penguji'); ?>>
                            Penguji
                        </option>
                    </select>
                    <?php echo form_error('ditunjukkan_untuk', '<small class="text-danger">', '</small>'); ?>
                    <small class="form-text text-muted">Siapa yang akan menilai aspek ini</small>
                </div>

                <div class="form-group">
                    <label for="urutan">Urutan <span class="text-danger">*</span></label>
                    <input type="number" name="urutan" id="urutan" class="form-control" 
                           value="<?php echo set_value('urutan', $aspek->urutan); ?>" 
                           min="1" required>
                    <?php echo form_error('urutan', '<small class="text-danger">', '</small>'); ?>
                    <small class="form-text text-muted">Urutan tampil (1, 2, ...)</small>
                </div>

                <div class="form-group">
                    <div class="form-check">
                        <input type="checkbox" name="is_aktif" id="is_aktif" class="form-check-input" 
                               value="1" <?php echo set_checkbox('is_aktif', '1', $aspek->is_aktif == 1); ?>>
                        <label class="form-check-label" for="is_aktif">
                            Aktif
                        </label>
                    </div>
                    <small class="form-text text-muted">Centang untuk mengaktifkan aspek penilaian</small>
                </div>
            </div>
            <div class="card-footer">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i> Simpan
                </button>
                <a href="<?php echo site_url('Skripsi_aspek_penilaian'); ?>" class="btn btn-secondary">
                    <i class="fas fa-times"></i> Batal
                </a>
            </div>
            <?php echo form_close(); ?>
        </div>
    </div>
</div>

