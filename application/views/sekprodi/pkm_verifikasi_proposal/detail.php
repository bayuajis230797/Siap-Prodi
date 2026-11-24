<?php $this->load->helper('pkm'); ?>
<div class="row mb-3"><div class="col-12"><a href="<?php echo site_url('Pkm_verifikasi_proposal'); ?>" class="btn btn-secondary btn-sm"><i class="fas fa-arrow-left"></i> Kembali</a></div></div>
<div class="card">
    <div class="card-header"><h3 class="card-title">Verifikasi Proposal Final — <?php echo htmlspecialchars($pengajuan->ketua_nama); ?></h3></div>
    <div class="card-body">
        <table class="table table-bordered table-sm">
            <tr><th width="25%">Ketua</th><td><?php echo htmlspecialchars($pengajuan->ketua_nama); ?> (<?php echo htmlspecialchars($pengajuan->ketua_nim); ?>)</td></tr>
            <tr><th>Jenis PKM</th><td><?php echo htmlspecialchars($pengajuan->nama_jenis); ?></td></tr>
            <tr><th>Judul</th><td><?php echo htmlspecialchars($pengajuan->judul_disetujui ?: $pengajuan->judul_pkm); ?></td></tr>
            <tr><th>Pembimbing</th><td><?php echo htmlspecialchars($pengajuan->pembimbing_final_nama ?? '-'); ?></td></tr>
            <tr><th>Proposal Final</th><td><?php echo pkm_google_drive_link_html($pengajuan->link_proposal_final, 'Buka Proposal Final', 'proposal_final', $pengajuan->id); ?></td></tr>
            <tr><th>Status</th><td><span class="badge badge-<?php echo pkm_status_acc_badge($pengajuan->status_acc_proposal); ?>"><?php echo pkm_status_verifikasi_proposal_final_label($pengajuan->status_acc_proposal); ?></span></td></tr>
        </table>

        <?php if ($pengajuan->status_acc_proposal === 'pending'): ?>
            <?php echo form_open('Pkm_verifikasi_proposal/proses/' . $pengajuan->id); ?>
                <div class="form-group">
                    <label>Catatan Verifikasi</label>
                    <textarea name="catatan_verifikasi_proposal_final" class="form-control" rows="3"></textarea>
                </div>
                <button type="submit" name="action" value="setujui" class="btn btn-success"><i class="fas fa-check"></i> Setujui</button>
                <button type="submit" name="action" value="tolak" class="btn btn-warning" onclick="return confirm('Tolak proposal final? Mahasiswa dapat kirim ulang.');"><i class="fas fa-times"></i> Tolak / Minta Perbaikan</button>
            <?php echo form_close(); ?>
        <?php endif; ?>
    </div>
</div>
