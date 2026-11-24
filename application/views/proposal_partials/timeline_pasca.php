<?php
/** Riwayat diskusi pasca sidang. Variabel: $logs */
$this->load->helper('google_drive');
?>
<div class="card">
    <div class="card-header"><h3 class="card-title">Riwayat Diskusi</h3></div>
    <div class="card-body" style="max-height:420px;overflow:auto">
        <?php if (empty($logs)): ?>
            <p class="text-muted mb-0">Belum ada pesan.</p>
        <?php else: foreach ($logs as $log): ?>
            <div class="border rounded p-2 mb-2 <?php echo $log->role_pengguna === 'dosen' ? 'bg-light' : ''; ?>">
                <div class="d-flex justify-content-between">
                    <strong><?php echo $log->role_pengguna === 'dosen' ? ('Penguji' . (!empty($log->nama_dosen) ? ': ' . htmlspecialchars($log->nama_dosen) : '')) : 'Mahasiswa'; ?></strong>
                    <small class="text-muted"><?php echo date('d/m/Y H:i', strtotime($log->created_at)); ?></small>
                </div>
                <?php if (!empty($log->status_revisi)): ?>
                    <span class="badge badge-<?php echo $log->status_revisi === 'acc' ? 'success' : 'warning'; ?>"><?php echo strtoupper($log->status_revisi); ?></span>
                <?php endif; ?>
                <?php if (!empty($log->pesan)): ?><p class="mb-1 mt-1"><?php echo nl2br(htmlspecialchars($log->pesan)); ?></p><?php endif; ?>
                <?php if (!empty($log->keterangan_revisi)): ?><p class="mb-1 text-danger small"><strong>Revisi:</strong> <?php echo nl2br(htmlspecialchars($log->keterangan_revisi)); ?></p><?php endif; ?>
                <?php if (!empty($log->file_path)): ?><div><?php echo google_drive_link_html($log->file_path, htmlspecialchars($log->file_name ?: 'Buka file')); ?></div><?php endif; ?>
            </div>
        <?php endforeach; endif; ?>
    </div>
</div>
