<?php $this->load->helper('pkm'); ?>
<div class="row">
    <div class="col-lg-8">
        <?php if ($this->session->flashdata('success')): ?><div class="alert alert-success alert-dismissible fade show"><?php echo $this->session->flashdata('success'); ?><button type="button" class="close" data-dismiss="alert"><span>&times;</span></button></div><?php endif; ?>
        <?php if ($this->session->flashdata('error')): ?><div class="alert alert-danger alert-dismissible fade show"><?php echo $this->session->flashdata('error'); ?><button type="button" class="close" data-dismiss="alert"><span>&times;</span></button></div><?php endif; ?>

        <div class="card">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-comments"></i> Detail Bimbingan PKM</h3>
                <div class="card-tools"><a href="<?php echo site_url('Bimbingan_pkm'); ?>" class="btn btn-secondary btn-sm"><i class="fas fa-arrow-left"></i> Kembali</a></div>
            </div>
            <div class="card-body">
                <div class="row mb-4">
                    <div class="col-md-6">
                        <table class="table table-sm table-bordered">
                            <tr><th width="40%">NIM</th><td><?php echo htmlspecialchars($bimbingan->nim); ?></td></tr>
                            <tr><th>Nama</th><td><?php echo htmlspecialchars($bimbingan->nama_mahasiswa); ?></td></tr>
                            <tr><th>Kelas</th><td><?php echo htmlspecialchars($bimbingan->kelas ?: '-'); ?></td></tr>
                        </table>
                    </div>
                    <div class="col-md-6">
                        <table class="table table-sm table-bordered">
                            <tr><th width="40%">Topik</th><td><strong><?php echo htmlspecialchars($bimbingan->judul_topik); ?></strong></td></tr>
                            <tr><th>Status Proses</th><td><span class="badge badge-<?php echo htmlspecialchars($workflow_status['class'] ?? 'secondary'); ?>"><?php echo htmlspecialchars($workflow_status['label'] ?? '-'); ?></span></td></tr>
                            <tr><th>Last Activity</th><td><?php echo date('d/m/Y H:i', strtotime($bimbingan->last_activity)); ?></td></tr>
                        </table>
                    </div>
                </div>

                <?php if ($bimbingan->status === 'open'): ?>
                    <div class="card card-primary mb-4">
                        <div class="card-header"><h5 class="mb-0"><i class="fas fa-clipboard-check"></i> Review Bimbingan</h5></div>
                        <div class="card-body">
                            <?php echo form_open_multipart('Bimbingan_pkm/kirim_pesan/' . $bimbingan->id); ?>
                                <div class="form-group">
                                    <label for="status_revisi">Status Review <span class="text-danger">*</span></label>
                                    <select name="status_revisi" id="status_revisi" class="form-control" required>
                                        <option value="">-- Pilih Status --</option>
                                        <option value="acc">ACC (Disetujui)</option>
                                        <option value="revisi">REVISI (Perlu Perbaikan)</option>
                                    </select>
                                </div>
                                <div class="form-group" id="keterangan_group" style="display:none;">
                                    <label for="keterangan_revisi">Keterangan Revisi <span class="text-danger">*</span></label>
                                    <textarea name="keterangan_revisi" id="keterangan_revisi" class="form-control" rows="4"></textarea>
                                </div>
                                <div class="form-group">
                                    <label>Berkas (Opsional)</label>
                                    <input type="file" name="file_berkas" class="form-control-file" accept=".pdf,.doc,.docx,.zip,.rar,.jpg,.jpeg,.png">
                                </div>
                                <div class="form-group">
                                    <label>Pesan (Opsional)</label>
                                    <textarea name="pesan" class="form-control" rows="2"></textarea>
                                </div>
                                <button type="submit" class="btn btn-primary"><i class="fas fa-paper-plane"></i> Kirim Review</button>
                            <?php echo form_close(); ?>
                        </div>
                    </div>
                <?php endif; ?>

                <h5 class="mb-3"><i class="fas fa-history"></i> Timeline Bimbingan</h5>
                <?php if (empty($logs)): ?>
                    <div class="alert alert-info">Belum ada log bimbingan.</div>
                <?php else: foreach ($logs as $log): ?>
                    <div class="card mb-3 <?php echo $log->role_pengguna === 'dosen' ? 'border-left-success' : 'border-left-primary'; ?>" style="border-left-width:4px;">
                        <div class="card-header">
                            <strong><?php echo $log->role_pengguna === 'mahasiswa' ? htmlspecialchars($bimbingan->nama_mahasiswa) : 'Anda (Pembimbing)'; ?></strong>
                            <small class="float-right text-muted"><?php echo date('d/m/Y H:i', strtotime($log->created_at)); ?></small>
                        </div>
                        <div class="card-body">
                            <?php if (!empty($log->pesan)): ?><p class="mb-2"><?php echo nl2br(htmlspecialchars($log->pesan)); ?></p><?php endif; ?>
                            <?php if ($log->file_path): ?><div class="alert alert-info mb-2"><?php echo pkm_google_drive_link_html($log->file_path, 'Buka File', 'log', $log->id); ?></div><?php endif; ?>
                            <?php if ($log->status_revisi): ?>
                                <div class="alert alert-<?php echo $log->status_revisi === 'acc' ? 'success' : 'warning'; ?> mb-0">
                                    <strong>Status: <?php echo strtoupper($log->status_revisi); ?></strong>
                                    <?php if ($log->keterangan_revisi): ?><br><small><?php echo nl2br(htmlspecialchars($log->keterangan_revisi)); ?></small><?php endif; ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; endif; ?>

                <div class="mt-3">
                    <?php if ($bimbingan->status === 'open'): ?>
                        <a href="<?php echo site_url('Bimbingan_pkm/tutup/' . $bimbingan->id); ?>" class="btn btn-secondary" onclick="return confirm('Tutup topik bimbingan ini?');"><i class="fas fa-lock"></i> Tutup Topik</a>
                    <?php else: ?>
                        <a href="<?php echo site_url('Bimbingan_pkm/buka/' . $bimbingan->id); ?>" class="btn btn-primary" onclick="return confirm('Buka kembali topik ini?');"><i class="fas fa-unlock"></i> Buka Kembali</a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="card card-success">
            <div class="card-header"><h3 class="card-title"><i class="fas fa-check-double"></i> Finalisasi Proposal PKM</h3></div>
            <div class="card-body">
                <p class="small text-muted">Setelah bimbingan selesai, finalisasi proposal agar mahasiswa dapat mengirim proposal final ke sekprodi.</p>
                <?php if (!empty($pengajuan->proposal_final_aktif)): ?>
                    <div class="alert alert-success mb-2"><i class="fas fa-check"></i> Proposal sudah difinalisasi.</div>
                    <p class="mb-1">Status kirim sekprodi: <strong><?php echo pkm_status_verifikasi_proposal_final_label($pengajuan->status_acc_proposal); ?></strong></p>
                    <?php if ($pengajuan->link_proposal_final): ?>
                        <p><?php echo pkm_google_drive_link_html($pengajuan->link_proposal_final, 'Proposal Final', 'proposal_final', $pengajuan->id); ?></p>
                    <?php endif; ?>
                <?php else: ?>
                    <?php echo form_open('Bimbingan_pkm/finalisasi_proposal/' . $pengajuan->id); ?>
                        <input type="hidden" name="id_bimbingan" value="<?php echo (int) $bimbingan->id; ?>">
                        <button type="submit" class="btn btn-success btn-block" onclick="return confirm('Finalisasi proposal PKM? Mahasiswa dapat mengirim proposal final ke sekprodi.');">
                            <i class="fas fa-check-double"></i> Finalisasi Proposal
                        </button>
                    <?php echo form_close(); ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
<script>
document.addEventListener('DOMContentLoaded', function() {
    var statusSelect = document.getElementById('status_revisi');
    var keteranganGroup = document.getElementById('keterangan_group');
    var keteranganInput = document.getElementById('keterangan_revisi');
    if (statusSelect && keteranganGroup) {
        statusSelect.addEventListener('change', function() {
            if (this.value === 'revisi') {
                keteranganGroup.style.display = 'block';
                keteranganInput.setAttribute('required', 'required');
            } else {
                keteranganGroup.style.display = 'none';
                keteranganInput.removeAttribute('required');
            }
        });
    }
});
</script>
