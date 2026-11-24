<?php $this->load->helper('google_drive'); ?>
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Detail Pengajuan Judul Proposal TA</h3>
                <div class="card-tools">
                    <a href="<?php echo site_url('Proposal_pengajuan/review'); ?>" class="btn btn-secondary btn-sm">
                        <i class="fas fa-arrow-left"></i> Kembali
                    </a>
                </div>
            </div>
            <div class="card-body">
                <?php if ($this->session->flashdata('success')): ?>
                    <div class="alert alert-success alert-dismissible">
                        <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
                        <?php echo $this->session->flashdata('success'); ?>
                    </div>
                <?php endif; ?>
                <?php if ($this->session->flashdata('error')): ?>
                    <div class="alert alert-danger alert-dismissible">
                        <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
                        <?php echo $this->session->flashdata('error'); ?>
                    </div>
                <?php endif; ?>

                <div class="row">
                    <div class="col-md-6">
                        <h5 class="mb-3"><i class="fas fa-user"></i> Mahasiswa</h5>
                        <table class="table table-sm table-bordered">
                            <tr><th width="35%">NIM</th><td><strong><?php echo htmlspecialchars($pengajuan->nim ?? '-'); ?></strong></td></tr>
                            <tr><th>Nama</th><td><?php echo htmlspecialchars($pengajuan->nama_mahasiswa ?? '-'); ?></td></tr>
                            <tr><th>Kelas</th><td><span class="badge badge-info"><?php echo htmlspecialchars($pengajuan->kelas ?? '-'); ?></span></td></tr>
                            <tr><th>Tahun Akademik</th><td><?php echo htmlspecialchars(trim(($pengajuan->tahun_akademik ?? '-') . ' ' . ucfirst($pengajuan->semester ?? ''))); ?></td></tr>
                            <tr><th>Diajukan</th><td><?php echo !empty($pengajuan->created_at) ? date('d/m/Y H:i', strtotime($pengajuan->created_at)) : '-'; ?></td></tr>
                        </table>
                    </div>
                    <div class="col-md-6">
                        <h5 class="mb-3"><i class="fas fa-info-circle"></i> Status</h5>
                        <table class="table table-sm table-bordered">
                            <tr><th width="35%">Status Pengajuan</th><td><?php echo proposal_status_badge($pengajuan->status); ?></td></tr>
                            <tr>
                                <th>Pembimbing</th>
                                <td>
                                    <?php if (!empty($pengajuan->pembimbing_nama)): ?>
                                        <?php echo htmlspecialchars($pengajuan->pembimbing_nama); ?>
                                        <small class="text-muted">(<?php echo htmlspecialchars($pengajuan->pembimbing_nidn ?? '-'); ?>)</small>
                                    <?php else: ?>
                                        <span class="text-muted">Belum ditetapkan</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <tr><th>ACC Judul Pembimbing</th><td><?php echo proposal_status_badge($pengajuan->status_acc_judul ?? 'pending'); ?></td></tr>
                            <?php if (!empty($pengajuan->catatan_revisi)): ?>
                                <tr><th>Catatan Revisi</th><td><?php echo nl2br(htmlspecialchars($pengajuan->catatan_revisi)); ?></td></tr>
                            <?php endif; ?>
                            <?php if (!empty($pengajuan->catatan_sekprodi)): ?>
                                <tr><th>Catatan Sekprodi</th><td><?php echo nl2br(htmlspecialchars($pengajuan->catatan_sekprodi)); ?></td></tr>
                            <?php endif; ?>
                        </table>
                    </div>
                </div>

                <div class="card card-outline card-info">
                    <div class="card-header"><h5 class="card-title mb-0"><i class="fas fa-book"></i> Judul dan Uraian</h5></div>
                    <div class="card-body">
                        <p><strong>Judul Diajukan:</strong><br><?php echo htmlspecialchars($pengajuan->judul_pengajuan); ?></p>
                        <?php if (!empty($pengajuan->judul_disetujui)): ?>
                            <p><strong>Judul Disetujui:</strong><br><span class="text-success"><strong><?php echo htmlspecialchars($pengajuan->judul_disetujui); ?></strong></span></p>
                        <?php endif; ?>
                        <?php if (!empty($pengajuan->deskripsi)): ?>
                            <p><strong>Deskripsi:</strong><br><?php echo nl2br(htmlspecialchars($pengajuan->deskripsi)); ?></p>
                        <?php endif; ?>
                        <?php if (!empty($pengajuan->alasan)): ?>
                            <p><strong>Alasan Memilih Judul:</strong><br><?php echo nl2br(htmlspecialchars($pengajuan->alasan)); ?></p>
                        <?php endif; ?>
                        <?php if (!empty($pengajuan->file_proposal)): ?>
                            <p class="mb-0"><strong>Berkas Proposal:</strong>
                                <?php echo google_drive_link_html($pengajuan->file_proposal, 'Buka Berkas'); ?></p>
                        <?php endif; ?>
                    </div>
                </div>

                <?php if ($pengajuan->status === 'pending'): ?>
                    <div class="row">
                        <div class="col-md-7">
                            <div class="card card-success">
                                <div class="card-header"><h5 class="card-title mb-0"><i class="fas fa-check"></i> Setujui dan Tetapkan Pembimbing</h5></div>
                                <?php echo form_open('Proposal_pengajuan/approve/' . $pengajuan->id); ?>
                                <div class="card-body">
                                    <div class="form-group">
                                        <label for="judul_disetujui">Judul Disetujui <span class="text-danger">*</span></label>
                                        <input type="text" name="judul_disetujui" id="judul_disetujui" class="form-control" maxlength="255"
                                               value="<?php echo set_value('judul_disetujui', $pengajuan->judul_pengajuan); ?>" required>
                                    </div>
                                    <div class="form-group">
                                        <label for="id_pembimbing">Pembimbing <span class="text-danger">*</span></label>
                                        <?php if (empty($pembimbing_list)): ?>
                                            <div class="alert alert-warning mb-0">
                                                Belum ada dosen yang diizinkan menjadi pembimbing Proposal TA. Minta Admin mengaturnya
                                                di <strong>Proposal TA &rarr; Dosen Pembimbing Proposal TA</strong>.
                                            </div>
                                        <?php else: ?>
                                            <select name="id_pembimbing" id="id_pembimbing" class="form-control" required>
                                                <option value="">Pilih Pembimbing</option>
                                                <?php foreach ($pembimbing_list as $d): ?>
                                                    <option value="<?php echo $d->id; ?>" <?php echo set_select('id_pembimbing', $d->id, (!empty($pengajuan->id_pembimbing_final) && $pengajuan->id_pembimbing_final == $d->id)); ?>>
                                                        <?php echo htmlspecialchars($d->nama_dosen . ' - ' . $d->nidn); ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                            <small class="form-text text-muted">Hanya satu pembimbing untuk Proposal TA.</small>
                                        <?php endif; ?>
                                    </div>
                                    <div class="form-group mb-0">
                                        <label for="catatan_sekprodi">Catatan (opsional)</label>
                                        <textarea name="catatan_sekprodi" id="catatan_sekprodi" class="form-control" rows="2"></textarea>
                                    </div>
                                </div>
                                <div class="card-footer">
                                    <button type="submit" class="btn btn-success" <?php echo empty($pembimbing_list) ? 'disabled' : ''; ?>
                                            onclick="return confirm('Setujui pengajuan ini dan tetapkan pembimbing?');">
                                        <i class="fas fa-check"></i> Setujui
                                    </button>
                                </div>
                                <?php echo form_close(); ?>
                            </div>
                        </div>
                        <div class="col-md-5">
                            <div class="card card-danger">
                                <div class="card-header"><h5 class="card-title mb-0"><i class="fas fa-times"></i> Tolak Pengajuan</h5></div>
                                <?php echo form_open('Proposal_pengajuan/reject/' . $pengajuan->id); ?>
                                <div class="card-body">
                                    <div class="form-group mb-0">
                                        <label for="alasan_tolak">Alasan Penolakan <span class="text-danger">*</span></label>
                                        <textarea name="catatan_sekprodi" id="alasan_tolak" class="form-control" rows="4" required></textarea>
                                    </div>
                                </div>
                                <div class="card-footer">
                                    <button type="submit" class="btn btn-danger" onclick="return confirm('Tolak pengajuan ini?');">
                                        <i class="fas fa-times"></i> Tolak
                                    </button>
                                </div>
                                <?php echo form_close(); ?>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
