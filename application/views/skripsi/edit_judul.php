<div class="row">
    <div class="col-12 col-md-8">
        <div class="card card-primary">
            <div class="card-header">
                <h3 class="card-title">Ubah Judul Skripsi</h3>
            </div>
            <div class="card-body">
                <?php if ($this->session->flashdata('error')): ?>
                    <div class="alert alert-danger"><?php echo $this->session->flashdata('error'); ?></div>
                <?php endif; ?>

                <?php echo form_open('Skripsi/edit_judul/' . $skripsi->id); ?>
                    <div class="form-group">
                        <label>Mahasiswa</label>
                        <input type="text" class="form-control" value="<?php echo $skripsi->nama_mahasiswa . ' (' . $skripsi->nim . ')'; ?>" disabled>
                    </div>
                    <div class="form-group">
                        <label>Judul Saat Ini</label>
                        <textarea class="form-control" rows="2" disabled><?php echo $skripsi->judul_skripsi; ?></textarea>
                    </div>
                    <div class="form-group">
                        <label for="judul_skripsi">Judul Baru <span class="text-danger">*</span></label>
                        <textarea name="judul_skripsi" id="judul_skripsi" class="form-control" rows="2" required><?php echo set_value('judul_skripsi', $skripsi->judul_skripsi); ?></textarea>
                    </div>
                    <div class="form-group">
                        <label for="catatan">Catatan Sekprodi (opsional)</label>
                        <textarea name="catatan" id="catatan" class="form-control" rows="2" placeholder="Alasan perubahan judul atau catatan untuk mahasiswa/pembimbing"></textarea>
                    </div>
                    <div class="form-group">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> Simpan Perubahan
                        </button>
                        <a href="<?php echo site_url('Skripsi'); ?>" class="btn btn-secondary">
                            <i class="fas fa-arrow-left"></i> Kembali
                        </a>
                    </div>
                <?php echo form_close(); ?>
            </div>
        </div>
    </div>
</div>













