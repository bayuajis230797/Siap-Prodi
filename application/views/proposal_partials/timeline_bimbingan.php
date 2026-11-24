<?php
/**
 * Timeline log bimbingan (dipakai mahasiswa dan dosen).
 * Variabel: $logs, $sudut ('mahasiswa'|'dosen'), $nama_mahasiswa, $nama_dosen
 */
$this->load->helper('google_drive');
?>
<h5 class="mb-3"><i class="fas fa-history"></i> Timeline Bimbingan</h5>
<?php if (empty($logs)): ?>
    <div class="alert alert-info"><i class="fas fa-info-circle"></i> Belum ada log bimbingan.</div>
<?php else: ?>
    <div class="timeline">
        <?php foreach ($logs as $log): ?>
            <div class="card mb-3 <?php echo $log->role_pengguna === 'dosen' ? 'border-left-success' : 'border-left-primary'; ?>">
                <div class="card-header">
                    <strong>
                        <?php if ($log->role_pengguna === 'mahasiswa'): ?>
                            <i class="fas fa-user text-primary"></i>
                            <?php echo $sudut === 'mahasiswa' ? 'Saya (Mahasiswa)' : htmlspecialchars($nama_mahasiswa); ?>
                        <?php else: ?>
                            <i class="fas fa-user-tie text-success"></i>
                            <?php echo $sudut === 'dosen' ? 'Anda (Pembimbing)' : htmlspecialchars($nama_dosen ?: 'Pembimbing'); ?>
                        <?php endif; ?>
                    </strong>
                    <small class="float-right text-muted"><?php echo date('d/m/Y H:i', strtotime($log->created_at)); ?></small>
                </div>
                <div class="card-body">
                    <?php if (!empty($log->pesan)): ?>
                        <p class="mb-2"><?php echo nl2br(htmlspecialchars($log->pesan)); ?></p>
                    <?php endif; ?>

                    <?php if (!empty($log->file_path)): ?>
                        <div class="alert alert-info mb-2">
                            <i class="fas fa-link"></i> <strong>Link Google Drive:</strong>
                            <?php echo google_drive_link_html($log->file_path, htmlspecialchars($log->file_name ?: 'Buka File')); ?>
                        </div>
                    <?php endif; ?>

                    <?php if (!empty($log->status_revisi)): ?>
                        <div class="alert alert-<?php echo $log->status_revisi === 'acc' ? 'success' : 'warning'; ?> mb-0">
                            <strong>
                                <i class="fas fa-<?php echo $log->status_revisi === 'acc' ? 'check-circle' : 'exclamation-triangle'; ?>"></i>
                                Status: <?php echo strtoupper($log->status_revisi); ?>
                            </strong>
                            <?php if (!empty($log->keterangan_revisi)): ?>
                                <br><small><?php echo nl2br(htmlspecialchars($log->keterangan_revisi)); ?></small>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>
