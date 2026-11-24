<?php $this->load->helper(array('google_drive', 'id_encode')); ?>
<div class="row">
    <div class="col-md-4">
        <div class="card card-outline card-info">
            <div class="card-header"><h3 class="card-title">Status Penguji</h3></div>
            <div class="card-body">
                <p class="mb-2"><strong><?php echo htmlspecialchars($bimbingan->judul_topik); ?></strong></p>
                <?php if (!empty($all_layak)): ?>
                    <div class="alert alert-success py-2">Semua penguji sudah layak klaim. Silakan ke menu Klaim Nilai.</div>
                <?php else: ?>
                    <div class="alert alert-warning py-2">Menunggu penguji menyatakan layak klaim nilai.</div>
                <?php endif; ?>
                <ul class="list-unstyled mb-0">
                    <?php foreach ($penguji_list as $p): ?>
                        <li class="mb-1">
                            <small><?php echo htmlspecialchars($p->nama_dosen); ?> (<?php echo $p->peran; ?>)</small>
                            <?php echo !empty($p->layak_klaim) ? '<span class="badge badge-success">Layak</span>' : '<span class="badge badge-secondary">Belum</span>'; ?>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>
        </div>
        <a href="<?php echo site_url('Bimbingan_pasca_skripsi_mahasiswa'); ?>" class="btn btn-secondary btn-sm"><i class="fas fa-arrow-left"></i> Kembali</a>
    </div>
    <div class="col-md-8">
        <?php if ($this->session->flashdata('success')): ?><div class="alert alert-success"><?php echo $this->session->flashdata('success'); ?></div><?php endif; ?>
        <?php if ($this->session->flashdata('error')): ?><div class="alert alert-danger"><?php echo $this->session->flashdata('error'); ?></div><?php endif; ?>
        <div class="card">
            <div class="card-header"><h3 class="card-title">Riwayat Diskusi</h3></div>
            <div class="card-body" style="max-height:420px;overflow:auto">
                <?php if (empty($logs)): ?>
                    <p class="text-muted">Belum ada pesan. Kirim file revisi Anda di bawah.</p>
                <?php else: foreach ($logs as $log): ?>
                    <div class="border rounded p-2 mb-2 <?php echo $log->role_pengguna === 'mahasiswa' ? 'bg-light' : ''; ?>">
                        <div class="d-flex justify-content-between">
                            <strong><?php echo $log->role_pengguna === 'dosen' ? ('Penguji' . (!empty($log->nama_dosen) ? ': '.htmlspecialchars($log->nama_dosen) : '')) : 'Anda'; ?></strong>
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
        <?php if (!empty($can_reply)): ?>
        <div class="card card-outline card-primary">
            <div class="card-header"><h3 class="card-title">Kirim Revisi / Pesan</h3></div>
            <?php echo form_open_multipart('Bimbingan_pasca_skripsi_mahasiswa/kirim/' . $encoded_id); ?>
            <div class="card-body">
                <div class="form-group">
                    <label>Pesan</label>
                    <textarea name="pesan" class="form-control" rows="3" placeholder="Jelaskan perbaikan yang sudah dilakukan"></textarea>
                </div>
                <div class="form-group mb-0">
                    <label>Berkas (opsional)</label>
                    <input type="file" name="file_google_drive" class="form-control-file" accept=".pdf,.doc,.docx,.zip,.rar,.jpg,.jpeg,.png">
                    <small class="form-text text-muted">Diunggah ke Google Drive institusi.</small>
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
