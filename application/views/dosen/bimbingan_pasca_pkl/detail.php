<?php $this->load->helper('google_drive'); ?>
<div class="row">
    <div class="col-md-4">
        <div class="card card-outline card-info">
            <div class="card-header"><h3 class="card-title">Mahasiswa</h3></div>
            <div class="card-body">
                <p class="mb-1"><strong><?php echo htmlspecialchars($bimbingan->nama_mahasiswa); ?></strong></p>
                <p class="mb-1"><?php echo htmlspecialchars($bimbingan->nim); ?> · <?php echo htmlspecialchars($bimbingan->kelas ?: '-'); ?></p>
                <p class="mb-2"><strong>Layak klaim:</strong>
                    <?php echo !empty($bimbingan->layak_klaim) ? '<span class="badge badge-success">Ya</span>' : '<span class="badge badge-warning">Belum</span>'; ?>
                </p>
                <?php if (empty($bimbingan->layak_klaim)): ?>
                    <?php echo form_open('Bimbingan_pasca_pkl/layak_klaim/' . $bimbingan->id); ?>
                        <button type="submit" class="btn btn-success btn-block" onclick="return confirm('Nyatakan mahasiswa layak klaim nilai?')">
                            <i class="fas fa-check"></i> Layak Klaim Nilai
                        </button>
                    <?php echo form_close(); ?>
                <?php endif; ?>
            </div>
        </div>
        <a href="<?php echo site_url('Bimbingan_pasca_pkl'); ?>" class="btn btn-secondary btn-sm"><i class="fas fa-arrow-left"></i> Kembali</a>
    </div>
    <div class="col-md-8">
        <?php if ($this->session->flashdata('success')): ?><div class="alert alert-success"><?php echo $this->session->flashdata('success'); ?></div><?php endif; ?>
        <?php if ($this->session->flashdata('error')): ?><div class="alert alert-danger"><?php echo $this->session->flashdata('error'); ?></div><?php endif; ?>
        <div class="card">
            <div class="card-header"><h3 class="card-title">Riwayat Diskusi</h3></div>
            <div class="card-body" style="max-height:420px;overflow:auto">
                <?php if (empty($logs)): ?>
                    <p class="text-muted">Belum ada pesan.</p>
                <?php else: foreach ($logs as $log): ?>
                    <div class="border rounded p-2 mb-2 <?php echo $log->role_pengguna === 'dosen' ? 'bg-light' : ''; ?>">
                        <div class="d-flex justify-content-between">
                            <strong><?php echo $log->role_pengguna === 'dosen' ? 'Anda (Penguji)' : 'Mahasiswa'; ?></strong>
                            <small class="text-muted"><?php echo date('d/m/Y H:i', strtotime($log->created_at)); ?></small>
                        </div>
                        <?php if (!empty($log->status_revisi)): ?>
                            <span class="badge badge-<?php echo $log->status_revisi==='acc'?'success':'warning'; ?>"><?php echo strtoupper($log->status_revisi); ?></span>
                        <?php endif; ?>
                        <?php if (!empty($log->pesan)): ?><p class="mb-1 mt-1"><?php echo nl2br(htmlspecialchars($log->pesan)); ?></p><?php endif; ?>
                        <?php if (!empty($log->keterangan_revisi)): ?><p class="mb-1 text-danger small"><strong>Revisi:</strong> <?php echo nl2br(htmlspecialchars($log->keterangan_revisi)); ?></p><?php endif; ?>
                        <?php if (!empty($log->file_path)): ?><div><?php echo google_drive_link_html($log->file_path, $log->file_name ?: 'Buka file'); ?></div><?php endif; ?>
                    </div>
                <?php endforeach; endif; ?>
            </div>
        </div>
        <?php if ($bimbingan->status === 'open'): ?>
        <div class="card card-outline card-primary">
            <div class="card-header"><h3 class="card-title">Balas / ACC / Revisi</h3></div>
            <?php echo form_open_multipart('Bimbingan_pasca_pkl/kirim_pesan/' . $bimbingan->id); ?>
            <div class="card-body">
                <div class="form-group">
                    <label>Status</label>
                    <select name="status_revisi" class="form-control" required>
                        <option value="revisi">Revisi</option>
                        <option value="acc">ACC</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Keterangan Revisi</label>
                    <textarea name="keterangan_revisi" class="form-control" rows="3"></textarea>
                </div>
                <div class="form-group">
                    <label>Pesan (opsional)</label>
                    <textarea name="pesan" class="form-control" rows="2"></textarea>
                </div>
                <div class="form-group mb-0">
                    <label>Berkas (opsional)</label>
                    <input type="file" name="file_berkas" class="form-control-file" accept=".pdf,.doc,.docx,.zip,.rar,.jpg,.jpeg,.png">
                </div>
            </div>
            <div class="card-footer text-right">
                <button type="submit" class="btn btn-primary"><i class="fas fa-paper-plane"></i> Kirim</button>
            </div>
            <?php echo form_close(); ?>
        </div>
        <?php endif; ?>
    </div>
</div>
