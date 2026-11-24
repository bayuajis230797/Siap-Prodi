<?php $this->load->helper('pkm'); ?>
<div class="card">
    <div class="card-header"><h3 class="card-title">Perubahan Judul PKM — Pending</h3></div>
    <div class="card-body">
        <?php if ($this->session->flashdata('success')): ?><div class="alert alert-success"><?php echo $this->session->flashdata('success'); ?></div><?php endif; ?>
        <?php if (empty($list)): ?>
            <p class="text-muted">Tidak ada pengajuan perubahan judul.</p>
        <?php else: foreach ($list as $row): ?>
            <div class="card card-outline card-warning mb-3">
                <div class="card-body">
                    <p><strong><?php echo htmlspecialchars($row->nama_mahasiswa); ?></strong> (<?php echo htmlspecialchars($row->nim); ?>)</p>
                    <p>Judul lama: <?php echo htmlspecialchars($row->judul_pkm); ?></p>
                    <p>Judul baru: <strong><?php echo htmlspecialchars($row->judul_baru); ?></strong></p>
                    <?php if ($row->alasan): ?><p>Alasan: <?php echo nl2br(htmlspecialchars($row->alasan)); ?></p><?php endif; ?>
                    <?php if ($row->link_bukti): ?><p>Bukti: <?php echo pkm_google_drive_link_html($row->link_bukti, 'Buka bukti', 'bukti_judul', $row->id); ?></p><?php endif; ?>
                    <form method="post" action="<?php echo site_url('Pkm_perubahan_judul_dosen/proses/' . $row->id); ?>" class="mt-2">
                        <textarea name="catatan_dosen" class="form-control form-control-sm mb-2" placeholder="Catatan (opsional)"></textarea>
                        <button type="submit" name="action" value="acc" class="btn btn-success btn-sm">Setujui</button>
                        <button type="submit" name="action" value="tolak" class="btn btn-danger btn-sm">Tolak</button>
                    </form>
                </div>
            </div>
        <?php endforeach; endif; ?>
    </div>
</div>
