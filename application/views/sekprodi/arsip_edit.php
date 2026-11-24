<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Edit Arsip Berkas</h3>
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

                <?php echo form_open_multipart('Arsip/edit/' . $arsip->id); ?>
                    <div class="form-group">
                        <label for="jenis">Jenis Berkas <span class="text-danger">*</span></label>
                        <select name="jenis" id="jenis" class="form-control" required onchange="toggleJenisFields()">
                            <option value="masuk" <?php echo ($arsip->jenis == 'masuk') ? 'selected' : ''; ?>>Berkas Masuk</option>
                            <option value="keluar" <?php echo ($arsip->jenis == 'keluar') ? 'selected' : ''; ?>>Berkas Keluar</option>
                        </select>
                        <?php echo form_error('jenis', '<small class="text-danger">', '</small>'); ?>
                    </div>

                    <div class="form-group" id="pengirim_group" style="display: <?php echo $arsip->jenis == 'masuk' ? 'block' : 'none'; ?>;">
                        <label for="pengirim">Pengirim <span class="text-danger">*</span></label>
                        <input type="text" name="pengirim" id="pengirim" class="form-control" 
                               value="<?php echo set_value('pengirim', $arsip->pengirim); ?>" 
                               placeholder="Nama pengirim berkas">
                        <?php echo form_error('pengirim', '<small class="text-danger">', '</small>'); ?>
                    </div>

                    <div class="form-group" id="penerima_group" style="display: <?php echo $arsip->jenis == 'keluar' ? 'block' : 'none'; ?>;">
                        <label for="penerima">Penerima <span class="text-danger">*</span></label>
                        <input type="text" name="penerima" id="penerima" class="form-control" 
                               value="<?php echo set_value('penerima', $arsip->penerima); ?>" 
                               placeholder="Nama penerima berkas">
                        <?php echo form_error('penerima', '<small class="text-danger">', '</small>'); ?>
                    </div>

                    <div class="form-group">
                        <label for="nomor_surat">Nomor Surat</label>
                        <input type="text" name="nomor_surat" id="nomor_surat" class="form-control" 
                               value="<?php echo set_value('nomor_surat', $arsip->nomor_surat); ?>" 
                               placeholder="Nomor surat (opsional)">
                        <?php echo form_error('nomor_surat', '<small class="text-danger">', '</small>'); ?>
                    </div>

                    <div class="form-group">
                        <label for="tanggal_surat">Tanggal Surat <span class="text-danger">*</span></label>
                        <input type="date" name="tanggal_surat" id="tanggal_surat" class="form-control" 
                               value="<?php echo set_value('tanggal_surat', $arsip->tanggal_surat); ?>" required>
                        <?php echo form_error('tanggal_surat', '<small class="text-danger">', '</small>'); ?>
                    </div>

                    <div class="form-group">
                        <label for="perihal">Perihal <span class="text-danger">*</span></label>
                        <textarea name="perihal" id="perihal" class="form-control" rows="3" 
                                  placeholder="Perihal surat" required><?php echo set_value('perihal', $arsip->perihal); ?></textarea>
                        <?php echo form_error('perihal', '<small class="text-danger">', '</small>'); ?>
                    </div>

                    <div class="form-group">
                        <label>Link Saat Ini</label>
                        <?php if (!empty($arsip->file_path)): ?>
                            <div class="alert alert-info">
                                <i class="fas fa-link"></i> 
                                <a href="<?php echo htmlspecialchars($arsip->file_path); ?>" target="_blank" class="text-primary">
                                    <strong>Buka Link</strong>
                                </a>
                                <br>
                                <small class="text-muted"><?php echo htmlspecialchars($arsip->file_path); ?></small>
                            </div>
                        <?php else: ?>
                            <div class="alert alert-warning">
                                <i class="fas fa-exclamation-triangle"></i> Belum ada link
                            </div>
                        <?php endif; ?>
                    </div>

                    <div class="form-group">
                        <label for="file_berkas">Ganti Berkas (opsional)</label>
                        <?php $this->load->helper('google_drive'); ?>
                        <?php if (!empty($arsip->file_path) && strpos($arsip->file_path, 'http') === 0): ?>
                            <p class="small mb-2"><?php echo google_drive_link_html($arsip->file_path, htmlspecialchars($arsip->file_name ?: 'Buka berkas')); ?></p>
                        <?php endif; ?>
                        <input type="file" name="file_berkas" id="file_berkas" class="form-control-file">
                        <small class="form-text text-muted">Kosongkan jika tidak mengganti file.</small>
                    </div>

                    <div class="form-group">
                        <label for="nama_file">Nama File (Opsional)</label>
                        <input type="text" name="nama_file" id="nama_file" class="form-control" 
                               value="<?php echo set_value('nama_file', $arsip->file_name); ?>" 
                               placeholder="Contoh: Surat_Masuk_001.pdf">
                        <small class="form-text text-muted">Nama file untuk referensi (opsional)</small>
                    </div>

                    <div class="form-group">
                        <label for="keterangan">Keterangan</label>
                        <textarea name="keterangan" id="keterangan" class="form-control" rows="2" 
                                  placeholder="Keterangan tambahan (opsional)"><?php echo set_value('keterangan', $arsip->keterangan); ?></textarea>
                        <?php echo form_error('keterangan', '<small class="text-danger">', '</small>'); ?>
                    </div>

                    <div class="form-group">
                        <a href="<?php echo site_url('Arsip'); ?>" class="btn btn-secondary">
                            <i class="fas fa-times"></i> Batal
                        </a>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> Simpan Perubahan
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
        if (penerima.value) penerima.value = '';
    } else if (jenis === 'keluar') {
        pengirimGroup.style.display = 'none';
        penerimaGroup.style.display = 'block';
        pengirim.required = false;
        penerima.required = true;
        if (pengirim.value) pengirim.value = '';
    }
}

// Initialize on page load
document.addEventListener('DOMContentLoaded', function() {
    toggleJenisFields();
});
</script>










