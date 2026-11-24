<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Edit Berkas Pendaftaran Sidang</h3>
                <div class="card-tools">
                    <a href="<?php echo site_url('Berkas_sidang'); ?>" class="btn btn-secondary btn-sm">
                        <i class="fas fa-arrow-left"></i> Kembali
                    </a>
                </div>
            </div>
            <div class="card-body">
                <?php echo form_open('Berkas_sidang/edit/' . $berkas->id); ?>
                    <div class="form-group">
                        <label for="nama_berkas">Nama Berkas <span class="text-danger">*</span></label>
                        <input type="text" name="nama_berkas" id="nama_berkas" class="form-control" value="<?php echo set_value('nama_berkas', $berkas->nama_berkas); ?>" required>
                    </div>
                    <div class="form-group">
                        <label for="deskripsi">Deskripsi</label>
                        <textarea name="deskripsi" id="deskripsi" class="form-control" rows="3"><?php echo set_value('deskripsi', $berkas->deskripsi); ?></textarea>
                    </div>
                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="wajib">Wajib</label>
                                <select name="wajib" id="wajib" class="form-control">
                                    <option value="1" <?php echo set_select('wajib', '1', $berkas->wajib == 1); ?>>Ya</option>
                                    <option value="0" <?php echo set_select('wajib', '0', $berkas->wajib == 0); ?>>Tidak</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="urutan">Urutan <span class="text-danger">*</span></label>
                                <input type="number" name="urutan" id="urutan" class="form-control" value="<?php echo set_value('urutan', $berkas->urutan); ?>" required>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="aktif">Status</label>
                                <select name="aktif" id="aktif" class="form-control">
                                    <option value="1" <?php echo set_select('aktif', '1', $berkas->aktif == 1); ?>>Aktif</option>
                                    <option value="0" <?php echo set_select('aktif', '0', $berkas->aktif == 0); ?>>Nonaktif</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> Simpan
                        </button>
                        <a href="<?php echo site_url('Berkas_sidang'); ?>" class="btn btn-secondary">Batal</a>
                    </div>
                <?php echo form_close(); ?>
            </div>
        </div>
    </div>
</div>












