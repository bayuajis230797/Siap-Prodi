<?php $this->load->helper('google_drive'); ?>
<div class="row">
    <div class="col-12">
        <?php if ($this->session->flashdata('success')): ?>
            <div class="alert alert-success alert-dismissible fade show">
                <i class="fas fa-check-circle"></i> <?php echo $this->session->flashdata('success'); ?>
                <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
            </div>
        <?php endif; ?>
        <?php if ($this->session->flashdata('error')): ?>
            <div class="alert alert-danger alert-dismissible fade show">
                <i class="fas fa-exclamation-circle"></i> <?php echo $this->session->flashdata('error'); ?>
                <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
            </div>
        <?php endif; ?>

        <div class="card card-primary">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-clipboard-check"></i> Detail Ajuan Judul Proposal TA</h3>
                <div class="card-tools">
                    <a href="<?php echo site_url('Ajuan_judul_proposal'); ?>" class="btn btn-secondary btn-sm"><i class="fas fa-arrow-left"></i> Kembali</a>
                </div>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <h5>Data Mahasiswa</h5>
                        <table class="table table-bordered">
                            <tr><th width="40%">NIM</th><td><?php echo htmlspecialchars($pengajuan->nim ?? ''); ?></td></tr>
                            <tr><th>Nama Mahasiswa</th><td><?php echo htmlspecialchars($pengajuan->nama_mahasiswa ?? ''); ?></td></tr>
                            <tr><th>Kelas</th><td><?php echo htmlspecialchars($pengajuan->kelas ?? ''); ?></td></tr>
                        </table>
                    </div>
                    <div class="col-md-6">
                        <h5>Status Review</h5>
                        <table class="table table-bordered">
                            <tr><th width="40%">Status ACC Anda</th><td><?php echo proposal_status_badge($status_acc); ?></td></tr>
                            <?php if ($status_acc === 'revisi' && !empty($pengajuan->catatan_revisi)): ?>
                                <tr><th>Catatan Revisi Anda</th><td><?php echo nl2br(htmlspecialchars($pengajuan->catatan_revisi)); ?></td></tr>
                            <?php endif; ?>
                        </table>
                    </div>
                </div>

                <div class="card card-outline card-info">
                    <div class="card-header"><h5 class="card-title mb-0">Judul dan Uraian</h5></div>
                    <div class="card-body">
                        <p><strong>Judul Disetujui Sekprodi:</strong><br>
                            <span class="text-success"><strong><?php echo htmlspecialchars($pengajuan->judul_disetujui ?: $pengajuan->judul_pengajuan); ?></strong></span></p>
                        <?php if ($pengajuan->judul_disetujui && $pengajuan->judul_disetujui !== $pengajuan->judul_pengajuan): ?>
                            <p><strong>Judul Awal Diajukan:</strong><br><?php echo htmlspecialchars($pengajuan->judul_pengajuan); ?></p>
                        <?php endif; ?>
                        <?php if (!empty($pengajuan->deskripsi)): ?>
                            <p><strong>Deskripsi:</strong><br><?php echo nl2br(htmlspecialchars($pengajuan->deskripsi)); ?></p>
                        <?php endif; ?>
                        <?php if (!empty($pengajuan->alasan)): ?>
                            <p><strong>Alasan Memilih Judul:</strong><br><?php echo nl2br(htmlspecialchars($pengajuan->alasan)); ?></p>
                        <?php endif; ?>
                        <?php if (!empty($pengajuan->file_proposal)): ?>
                            <p class="mb-0"><strong>Berkas Proposal:</strong> <?php echo google_drive_link_html($pengajuan->file_proposal, 'Buka Berkas'); ?></p>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="card card-outline card-secondary">
                    <div class="card-header"><h5 class="card-title mb-0"><i class="fas fa-pen"></i> Edit Judul</h5></div>
                    <?php echo form_open('Ajuan_judul_proposal/edit_judul/' . $pengajuan->id); ?>
                    <div class="card-body">
                        <div class="form-group mb-0">
                            <label for="judul_disetujui">Judul Proposal TA <span class="text-danger">*</span></label>
                            <input type="text" name="judul_disetujui" id="judul_disetujui" class="form-control" maxlength="255" minlength="10"
                                   value="<?php echo htmlspecialchars($pengajuan->judul_disetujui ?: $pengajuan->judul_pengajuan); ?>" required>
                            <small class="form-text text-muted">Judul ini menggantikan judul yang berlaku dan langsung dipakai di seluruh modul Proposal TA. Minimal 10 karakter.</small>
                        </div>
                    </div>
                    <div class="card-footer">
                        <button type="submit" class="btn btn-secondary" onclick="return confirm('Simpan perubahan judul?');"><i class="fas fa-save"></i> Simpan Judul</button>
                    </div>
                    <?php echo form_close(); ?>
                </div>
                <?php if ($status_acc !== 'acc'): ?>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="card card-success">
                                <div class="card-header"><h5 class="card-title mb-0"><i class="fas fa-check"></i> Setujui (ACC) Judul</h5></div>
                                <?php echo form_open('Ajuan_judul_proposal/acc/' . $pengajuan->id); ?>
                                <div class="card-body">
                                    Setelah di-ACC, mahasiswa dapat memulai bimbingan Proposal TA dengan Anda.
                                </div>
                                <div class="card-footer">
                                    <button type="submit" class="btn btn-success" onclick="return confirm('ACC judul proposal ini?');">
                                        <i class="fas fa-check"></i> ACC Judul
                                    </button>
                                </div>
                                <?php echo form_close(); ?>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="card card-warning">
                                <div class="card-header"><h5 class="card-title mb-0"><i class="fas fa-edit"></i> Minta Revisi</h5></div>
                                <?php echo form_open('Ajuan_judul_proposal/revisi/' . $pengajuan->id); ?>
                                <div class="card-body">
                                    <div class="form-group mb-0">
                                        <label for="catatan_revisi">Catatan Revisi <span class="text-danger">*</span></label>
                                        <textarea name="catatan_revisi" id="catatan_revisi" class="form-control" rows="4" required></textarea>
                                        <small class="form-text text-muted">Mahasiswa akan mengajukan judul perbaikan lewat menu Perubahan Judul, lalu divalidasi sekprodi.</small>
                                    </div>
                                </div>
                                <div class="card-footer">
                                    <button type="submit" class="btn btn-warning"><i class="fas fa-paper-plane"></i> Kirim Revisi</button>
                                </div>
                                <?php echo form_close(); ?>
                            </div>
                        </div>
                    </div>
                <?php else: ?>
                    <div class="alert alert-success mb-0">
                        <i class="fas fa-check-circle"></i> Judul sudah Anda setujui. Mahasiswa dapat melakukan bimbingan.
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
