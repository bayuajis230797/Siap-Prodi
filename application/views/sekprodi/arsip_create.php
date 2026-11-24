<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Tambah Arsip Berkas</h3>
            </div>
            <div class="card-body">
                <?php if ($this->session->flashdata('error')): ?>
                    <div class="alert alert-danger alert-dismissible fade show">
                        <i class="fas fa-exclamation-circle"></i> <?php echo $this->session->flashdata('error'); ?>
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                <?php endif; ?>

                <?php echo form_open_multipart('Arsip/create'); ?>
                    <div class="form-group">
                        <label for="jenis">Jenis Berkas <span class="text-danger">*</span></label>
                        <select name="jenis" id="jenis" class="form-control" required onchange="toggleJenisFields()">
                            <option value="">-- Pilih Jenis --</option>
                            <option value="masuk" <?php echo set_value('jenis') == 'masuk' ? 'selected' : ''; ?>>Berkas Masuk</option>
                            <option value="keluar" <?php echo set_value('jenis') == 'keluar' ? 'selected' : ''; ?>>Berkas Keluar</option>
                        </select>
                        <?php echo form_error('jenis', '<small class="text-danger">', '</small>'); ?>
                    </div>

                    <div class="form-group" id="pengirim_group" style="display: none;">
                        <label for="pengirim">Pengirim <span class="text-danger">*</span></label>
                        <input type="text" name="pengirim" id="pengirim" class="form-control" 
                               value="<?php echo set_value('pengirim'); ?>" 
                               placeholder="Nama pengirim berkas">
                        <?php echo form_error('pengirim', '<small class="text-danger">', '</small>'); ?>
                    </div>

                    <div class="form-group" id="penerima_group" style="display: none;">
                        <label for="penerima">Penerima <span class="text-danger">*</span></label>
                        <input type="text" name="penerima" id="penerima" class="form-control" 
                               value="<?php echo set_value('penerima'); ?>" 
                               placeholder="Nama penerima berkas">
                        <?php echo form_error('penerima', '<small class="text-danger">', '</small>'); ?>
                    </div>

                    <div class="form-group">
                        <label for="nomor_surat">Nomor Surat</label>
                        <input type="text" name="nomor_surat" id="nomor_surat" class="form-control" 
                               value="<?php echo set_value('nomor_surat'); ?>" 
                               placeholder="Nomor surat (opsional)">
                        <?php echo form_error('nomor_surat', '<small class="text-danger">', '</small>'); ?>
                    </div>

                    <div class="form-group">
                        <label for="tanggal_surat">Tanggal Surat <span class="text-danger">*</span></label>
                        <input type="date" name="tanggal_surat" id="tanggal_surat" class="form-control" 
                               value="<?php echo set_value('tanggal_surat'); ?>" required>
                        <?php echo form_error('tanggal_surat', '<small class="text-danger">', '</small>'); ?>
                    </div>

                    <div class="form-group">
                        <label for="perihal">Perihal <span class="text-danger">*</span></label>
                        <textarea name="perihal" id="perihal" class="form-control" rows="3" 
                                  placeholder="Perihal surat" required><?php echo set_value('perihal'); ?></textarea>
                        <?php echo form_error('perihal', '<small class="text-danger">', '</small>'); ?>
                    </div>

                    <div class="form-group">
                        <label for="file_berkas">Berkas <span class="text-danger">*</span></label>
                        <input type="file" name="file_berkas" id="file_berkas" class="form-control-file" required>
                        <small class="form-text text-muted">Diunggah ke Google Drive sekprodi (hubungkan Drive di Profil). Folder: SIAP/Arsip</small>
                    </div>

                    <div class="form-group">
                        <label for="nama_file">Nama File (Opsional)</label>
                        <input type="text" name="nama_file" id="nama_file" class="form-control" 
                               value="<?php echo set_value('nama_file'); ?>" 
                               placeholder="Contoh: Surat_Masuk_001.pdf">
                        <small class="form-text text-muted">Nama file untuk referensi (opsional)</small>
                    </div>

                    <div class="form-group">
                        <label for="keterangan">Keterangan</label>
                        <textarea name="keterangan" id="keterangan" class="form-control" rows="2" 
                                  placeholder="Keterangan tambahan (opsional)"><?php echo set_value('keterangan'); ?></textarea>
                        <?php echo form_error('keterangan', '<small class="text-danger">', '</small>'); ?>
                    </div>

                    <div class="form-group">
                        <a href="<?php echo site_url('Arsip'); ?>" class="btn btn-secondary">
                            <i class="fas fa-times"></i> Batal
                        </a>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> Simpan
                        </button>
                    </div>
                <?php echo form_close(); ?>
            </div>
        </div>
    </div>
</div>

<script>
function toggleJenisFields() {
    const jenis = document.getElementById('jenis').value;
    const pengirimGroup = document.getElementById('pengirim_group');
    const penerimaGroup = document.getElementById('penerima_group');
    const pengirim = document.getElementById('pengirim');
    const penerima = document.getElementById('penerima');
    
    if (jenis === 'masuk') {
        pengirimGroup.style.display = 'block';
        penerimaGroup.style.display = 'none';
        pengirim.required = true;
        penerima.required = false;
        penerima.value = '';
    } else if (jenis === 'keluar') {
        pengirimGroup.style.display = 'none';
        penerimaGroup.style.display = 'block';
        pengirim.required = false;
        penerima.required = true;
        pengirim.value = '';
    } else {
        pengirimGroup.style.display = 'none';
        penerimaGroup.style.display = 'none';
        pengirim.required = false;
        penerima.required = false;
    }
}

// Initialize on page load
document.addEventListener('DOMContentLoaded', function() {
    toggleJenisFields();
});
</script>










