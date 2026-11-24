<div class="row">
    <div class="col-md-4">
        <div class="card card-outline card-info">
            <div class="card-header">
                <h3 class="card-title">Informasi Bimbingan</h3>
            </div>
            <div class="card-body">
                <p class="mb-1"><strong>Pembimbing:</strong><br><?php echo htmlspecialchars($bimbingan->nama_dosen); ?> (<?php echo htmlspecialchars($bimbingan->nidn); ?>)</p>
                <p class="mb-1"><strong>Sebagai:</strong> Pembimbing <?php echo $bimbingan->tipe_pembimbing; ?></p>
                <p class="mb-1"><strong>Topik:</strong><br><?php echo htmlspecialchars($bimbingan->judul_topik); ?></p>
                <p class="mb-1"><strong>Status:</strong>
                    <span class="badge badge-<?php echo ($bimbingan->status === 'open') ? 'success' : 'secondary'; ?>">
                        <?php echo ucfirst($bimbingan->status); ?>
                    </span>
                </p>
                <p class="mb-0"><strong>Aktivitas Terakhir:</strong><br>
                    <?php echo $bimbingan->last_activity ? date('d/m/Y H:i', strtotime($bimbingan->last_activity)) : '-'; ?>
                </p>
            </div>
        </div>
        <a href="<?php echo site_url('Bimbingan_skripsi_mahasiswa'); ?>" class="btn btn-secondary btn-block">
            <i class="fas fa-arrow-left"></i> Kembali
        </a>
    </div>
    <div class="col-md-8">
        <div class="card card-outline card-primary">
            <div class="card-header">
                <h3 class="card-title">Diskusi Bimbingan</h3>
            </div>
            <div class="card-body" style="max-height: 600px; overflow-y: auto;">
                <?php if (empty($logs)): ?>
                    <div class="alert alert-info mb-0">Belum ada diskusi.</div>
                <?php else: ?>
                    <?php foreach ($logs as $log): ?>
                        <div class="direct-chat-msg <?php echo ($log->role_pengguna == 'mahasiswa') ? 'right' : ''; ?> mb-3">
                            <div class="direct-chat-infos clearfix">
                                <span class="direct-chat-name <?php echo ($log->role_pengguna == 'mahasiswa') ? 'float-right' : 'float-left'; ?>">
                                    <?php echo ($log->role_pengguna == 'mahasiswa') ? 'Anda' : 'Pembimbing'; ?>
                                </span>
                                <span class="direct-chat-timestamp <?php echo ($log->role_pengguna == 'mahasiswa') ? 'float-left' : 'float-right'; ?>">
                                    <?php echo date('d/m/Y H:i', strtotime($log->created_at)); ?>
                                </span>
                            </div>
                            <div class="direct-chat-text <?php echo ($log->role_pengguna == 'mahasiswa') ? 'bg-primary' : 'bg-light'; ?>">
                                <?php echo nl2br(htmlspecialchars($log->pesan)); ?>
                                
                                <?php if (!empty($log->file_path)): ?>
                                    <br><br>
                                    <a href="<?php echo base_url($log->file_path); ?>" target="_blank" class="btn btn-sm btn-info">
                                        <i class="fas fa-download"></i> Download File: <?php echo htmlspecialchars($log->file_name); ?>
                                    </a>
                                <?php endif; ?>

                                <?php if ($log->role_pengguna == 'dosen' && !empty($log->status_revisi)): ?>
                                    <br><br>
                                    <div class="alert alert-<?php echo ($log->status_revisi == 'acc') ? 'success' : 'warning'; ?> mb-0">
                                        <strong>Status:</strong> 
                                        <span class="badge badge-<?php echo ($log->status_revisi == 'acc') ? 'success' : 'warning'; ?>">
                                            <?php echo strtoupper($log->status_revisi); ?>
                                        </span>
                                        <?php if (!empty($log->keterangan_revisi)): ?>
                                            <br><strong>Keterangan:</strong> <?php echo nl2br(htmlspecialchars($log->keterangan_revisi)); ?>
                                        <?php endif; ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
            <?php if ($bimbingan->status == 'open'): ?>
                <div class="card-footer">
                    <?php echo form_open_multipart('Bimbingan_skripsi_mahasiswa/kirim_pesan/' . $bimbingan->id, array('method' => 'post')); ?>
                    <div class="input-group">
                        <textarea name="pesan" class="form-control" rows="3" placeholder="Tulis pesan..." required></textarea>
                    </div>
                    <div class="input-group mt-2">
                        <div class="custom-file">
                            <input type="file" name="file" class="custom-file-input" id="fileInput">
                            <label class="custom-file-label" for="fileInput">Pilih file (PDF/DOC/DOCX/JPG/PNG)</label>
                        </div>
                        <div class="input-group-append">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-paper-plane"></i> Kirim
                            </button>
                        </div>
                    </div>
                    <?php echo form_close(); ?>
                </div>
            <?php else: ?>
                <div class="card-footer">
                    <div class="alert alert-warning mb-0">
                        <i class="fas fa-lock"></i> Bimbingan ini sudah ditutup.
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const fileInput = document.getElementById('fileInput');
    if (fileInput) {
        fileInput.addEventListener('change', function(e) {
            const fileName = e.target.files[0] ? e.target.files[0].name : 'Pilih file (PDF/DOC/DOCX/JPG/PNG)';
            const label = this.nextElementSibling;
            if (label) {
                label.textContent = fileName;
            }
        });
    }
});
</script>



