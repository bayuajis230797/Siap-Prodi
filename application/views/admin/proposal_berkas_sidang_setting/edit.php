<div class="row">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Edit Berkas Sidang Proposal TA</h3>
            </div>
            <?php echo form_open('Proposal_berkas_sidang_setting/edit/' . $berkas->id); ?>
            <div class="card-body">
                <?php if ($this->session->flashdata('error')): ?>
                    <div class="alert alert-danger alert-dismissible">
                        <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
                        <?php echo $this->session->flashdata('error'); ?>
                    </div>
                <?php endif; ?>

                <div class="form-group">
                    <label for="nama_berkas">Nama Berkas <span class="text-danger">*</span></label>
                    <input type="text" name="nama_berkas" id="nama_berkas" class="form-control" 
                           value="<?php echo set_value('nama_berkas', $berkas->nama_berkas); ?>" 
                           placeholder="Contoh: Surat Keterangan Proposal TA" required>
                    <?php echo form_error('nama_berkas', '<small class="text-danger">', '</small>'); ?>
                    <small class="form-text text-muted">Nama berkas yang harus diupload mahasiswa</small>
                </div>

                <div class="form-group">
                    <label for="deskripsi">Deskripsi</label>
                    <textarea name="deskripsi" id="deskripsi" class="form-control" rows="3"><?php echo set_value('deskripsi', $berkas->deskripsi); ?></textarea>
                    <?php echo form_error('deskripsi', '<small class="text-danger">', '</small>'); ?>
                    <small class="form-text text-muted">Deskripsi atau keterangan tentang berkas ini</small>
                </div>

                <div class="form-group">
                    <div class="form-check">
                        <input type="checkbox" name="wajib" id="wajib" class="form-check-input" 
                               value="1" <?php echo set_checkbox('wajib', '1', $berkas->wajib == 1); ?>>
                        <label class="form-check-label" for="wajib">
                            Wajib
                        </label>
                    </div>
                    <small class="form-text text-muted">Centang jika berkas ini wajib diupload oleh mahasiswa</small>
                </div>

                <div class="form-group">
                    <label for="urutan">Urutan <span class="text-danger">*</span></label>
                    <input type="number" name="urutan" id="urutan" class="form-control" 
                           value="<?php echo set_value('urutan', $berkas->urutan); ?>" 
                           min="1" required>
                    <?php echo form_error('urutan', '<small class="text-danger">', '</small>'); ?>
                    <small class="form-text text-muted">Urutan tampil berkas (1, 2, 3, ...)</small>
                </div>

                <div class="form-group">
                    <div class="form-check">
                        <input type="checkbox" name="aktif" id="aktif" class="form-check-input" 
                               value="1" <?php echo set_checkbox('aktif', '1', $berkas->aktif == 1); ?>>
                        <label class="form-check-label" for="aktif">
                            Aktif
                        </label>
                    </div>
                    <small class="form-text text-muted">Centang untuk mengaktifkan berkas sidang</small>
                </div>
            </div>
            <div class="card-footer">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i> Simpan
                </button>
                <a href="<?php echo site_url('Proposal_berkas_sidang_setting'); ?>" class="btn btn-secondary">
                    <i class="fas fa-times"></i> Batal
                </a>
            </div>
            <?php echo form_close(); ?>
        </div>
    </div>
</div>




