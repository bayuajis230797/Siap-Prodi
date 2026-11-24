<div class="row">
    <div class="col-12">
        <?php if ($this->session->flashdata('success')): ?>
            <div class="alert alert-success alert-dismissible fade show">
                <i class="fas fa-check-circle"></i> <?php echo $this->session->flashdata('success'); ?>
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
        <?php endif; ?>

        <?php if ($this->session->flashdata('error')): ?>
            <div class="alert alert-danger alert-dismissible fade show">
                <i class="fas fa-exclamation-circle"></i> <?php echo $this->session->flashdata('error'); ?>
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
        <?php endif; ?>

        <div class="card">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-plus"></i> Buat Topik Bimbingan Baru
                </h3>
            </div>
            <div class="card-body">
                <?php echo form_open_multipart('Bimbingan_skripsi_mahasiswa/bimbingan_create'); ?>
                    <div class="form-group">
                        <label for="id_pembimbing">Pilih Pembimbing <span class="text-danger">*</span></label>
                        <select name="id_pembimbing" id="id_pembimbing" class="form-control" required>
                            <option value="">-- Pilih Pembimbing --</option>
                            <?php if ($pembimbing_1): ?>
                                <option value="<?php echo $pembimbing_1->id; ?>">
                                    Pembimbing 1: <?php echo htmlspecialchars($pembimbing_1->nama_dosen); ?>
                                </option>
                            <?php endif; ?>
                            <?php if ($pembimbing_2): ?>
                                <option value="<?php echo $pembimbing_2->id; ?>">
                                    Pembimbing 2: <?php echo htmlspecialchars($pembimbing_2->nama_dosen); ?>
                                </option>
                            <?php endif; ?>
                        </select>
                        <?php echo form_error('id_pembimbing', '<small class="text-danger">', '</small>'); ?>
                        <small class="form-text text-muted">Pilih pembimbing yang akan mereview topik bimbingan ini.</small>
                    </div>

                    <div class="form-group">
                        <label for="judul_topik">Judul/Topik Bimbingan <span class="text-danger">*</span></label>
                        <select name="judul_topik" id="judul_topik" class="form-control" required>
                            <option value="">-- Pilih topik bimbingan --</option>
                            <?php foreach (skripsi_topik_bimbingan_pilihan() as $topik_opt): ?>
                                <option value="<?php echo htmlspecialchars($topik_opt); ?>" <?php echo set_select('judul_topik', $topik_opt); ?>>
                                    <?php echo htmlspecialchars($topik_opt); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <?php echo form_error('judul_topik', '<small class="text-danger">', '</small>'); ?>
                        <small class="form-text text-muted">Topik disamakan untuk semua mahasiswa.</small>
                    </div>

                    <div class="form-group">
                        <label for="file_laporan">Berkas Laporan <span class="text-danger">*</span></label>
                        <input type="file" name="file_laporan" id="file_laporan" class="form-control-file"
                               accept=".pdf,.doc,.docx,.zip,.rar" required>
                        <?php echo form_error('file_laporan', '<small class="text-danger">', '</small>'); ?>
                        <small class="form-text text-muted">
                            Diunggah ke Google Drive institusi. PDF/DOC/ZIP tanpa batas form.
                        </small>
                    </div>

                    <div class="form-group">
                        <label for="pesan">Pesan Tambahan (Opsional)</label>
                        <textarea name="pesan" id="pesan" class="form-control" rows="3" 
                                  placeholder="Tambahkan pesan atau catatan untuk pembimbing..."><?php echo set_value('pesan'); ?></textarea>
                        <?php echo form_error('pesan', '<small class="text-danger">', '</small>'); ?>
                    </div>

                    <div class="form-group mt-3">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-plus"></i> Mulai Bimbingan
                        </button>
                        <a href="<?php echo site_url('Bimbingan_skripsi_mahasiswa/bimbingan'); ?>" class="btn btn-secondary">
                            <i class="fas fa-times"></i> Batal
                        </a>
                    </div>
                <?php echo form_close(); ?>
            </div>
        </div>
    </div>
</div>

