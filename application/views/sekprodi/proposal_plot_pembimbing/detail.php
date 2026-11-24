<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Plot Pembimbing Proposal TA</h3>
                <div class="card-tools">
                    <a href="<?php echo site_url('Proposal_plot_pembimbing'); ?>" class="btn btn-secondary btn-sm">
                        <i class="fas fa-arrow-left"></i> Kembali
                    </a>
                </div>
            </div>
            <div class="card-body">
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

                <div class="row mb-4">
                    <div class="col-md-6">
                        <h5 class="mb-3"><i class="fas fa-user"></i> Informasi Mahasiswa</h5>
                        <table class="table table-sm table-bordered">
                            <tr><th width="40%">NIM</th><td><strong><?php echo htmlspecialchars($pengajuan->nim ?? '-'); ?></strong></td></tr>
                            <tr><th>Nama Mahasiswa</th><td><?php echo htmlspecialchars($pengajuan->nama_mahasiswa ?? '-'); ?></td></tr>
                            <tr><th>Kelas</th><td><span class="badge badge-info"><?php echo htmlspecialchars($pengajuan->kelas ?? '-'); ?></span></td></tr>
                            <tr><th>Status Pengajuan</th><td><?php echo proposal_status_badge($pengajuan->status); ?></td></tr>
                        </table>
                    </div>
                    <div class="col-md-6">
                        <h5 class="mb-3"><i class="fas fa-book"></i> Judul</h5>
                        <div class="alert alert-info">
                            <p class="mb-0"><strong><?php echo htmlspecialchars($pengajuan->judul_disetujui ?: ($pengajuan->judul_pengajuan ?? '-')); ?></strong></p>
                        </div>
                    </div>
                </div>

                <div class="card card-primary">
                    <div class="card-header">
                        <h5 class="card-title mb-0"><i class="fas fa-user-plus"></i> Pembimbing</h5>
                    </div>
                    <div class="card-body">
                        <?php if (empty($pembimbing_list)): ?>
                            <div class="alert alert-warning mb-0">
                                <i class="fas fa-exclamation-triangle"></i>
                                Belum ada dosen yang diizinkan menjadi pembimbing Proposal TA. Minta Admin mengaturnya di menu
                                <strong>Proposal TA &rarr; Dosen Pembimbing Proposal TA</strong>.
                            </div>
                        <?php else: ?>
                            <?php echo form_open('Proposal_plot_pembimbing/proses/' . $pengajuan->id); ?>
                                <div class="form-group">
                                    <label for="id_pembimbing">Pembimbing <span class="text-danger">*</span></label>
                                    <select name="id_pembimbing" id="id_pembimbing" class="form-control" required>
                                        <option value="">Pilih Pembimbing</option>
                                        <?php foreach ($pembimbing_list as $dosen): ?>
                                            <option value="<?php echo $dosen->id; ?>"
                                                    <?php echo (!empty($pengajuan->id_pembimbing_final) && $pengajuan->id_pembimbing_final == $dosen->id) ? 'selected' : ''; ?>>
                                                <?php echo htmlspecialchars($dosen->nama_dosen . ' - ' . $dosen->nidn); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                    <?php echo form_error('id_pembimbing', '<small class="text-danger">', '</small>'); ?>
                                    <small class="form-text text-muted">Proposal TA hanya memiliki satu pembimbing.</small>
                                </div>

                                <div class="alert alert-info">
                                    <i class="fas fa-info-circle"></i>
                                    Mengganti pembimbing mengembalikan status ACC judul menjadi <em>menunggu</em>.
                                    Pembimbing tidak dapat diganti setelah bimbingan dimulai.
                                </div>

                                <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Simpan Plot Pembimbing</button>
                                <a href="<?php echo site_url('Proposal_plot_pembimbing'); ?>" class="btn btn-secondary"><i class="fas fa-times"></i> Batal</a>
                            <?php echo form_close(); ?>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
