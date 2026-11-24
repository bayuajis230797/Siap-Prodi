<div class="row">
    <div class="col-md-8 offset-md-2">
        <div class="card card-primary">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-plus"></i> Buat Topik Bimbingan Proposal TA</h3>
            </div>
            <?php echo form_open_multipart('Bimbingan_proposal_mahasiswa/bimbingan_create'); ?>
            <div class="card-body">
                <?php if ($this->session->flashdata('error')): ?>
                    <div class="alert alert-danger"><?php echo $this->session->flashdata('error'); ?></div>
                <?php endif; ?>
                <?php if (validation_errors()): ?>
                    <div class="alert alert-danger"><?php echo validation_errors(); ?></div>
                <?php endif; ?>

                <div class="form-group">
                    <label>Kepada</label>
                    <input type="text" class="form-control" disabled
                           value="Pembimbing: <?php echo htmlspecialchars($pembimbing ? $pembimbing->nama_dosen : '-'); ?>">
                </div>

                <div class="form-group">
                    <label for="judul_topik">Topik Bimbingan <span class="text-danger">*</span></label>
                    <select name="judul_topik" id="judul_topik" class="form-control" required>
                        <option value="">-- Pilih Topik --</option>
                        <?php foreach (proposal_topik_bimbingan_pilihan() as $opt): ?>
                            <option value="<?php echo htmlspecialchars($opt); ?>" <?php echo set_select('judul_topik', $opt); ?>><?php echo htmlspecialchars($opt); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label for="file_laporan">Berkas Laporan <span class="text-danger">*</span></label>
                    <input type="file" name="file_laporan" id="file_laporan" class="form-control-file"
                           accept=".pdf,.doc,.docx,.zip,.rar" required>
                    <small class="form-text text-muted">Disimpan ke Google Drive institusi (dikelola admin).</small>
                </div>

                <div class="form-group">
                    <label for="pesan">Pesan Pembuka (opsional)</label>
                    <textarea name="pesan" id="pesan" class="form-control" rows="4" placeholder="Pesan untuk pembimbing..."><?php echo set_value('pesan'); ?></textarea>
                </div>
            </div>
            <div class="card-footer">
                <button type="submit" class="btn btn-primary"><i class="fas fa-paper-plane"></i> Kirim</button>
                <a href="<?php echo site_url('Bimbingan_proposal_mahasiswa/bimbingan'); ?>" class="btn btn-secondary">Batal</a>
            </div>
            <?php echo form_close(); ?>
        </div>
    </div>
</div>
