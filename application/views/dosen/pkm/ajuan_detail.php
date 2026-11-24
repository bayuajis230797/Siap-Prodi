<?php $this->load->helper(array('pkm', 'google_drive')); ?>
<div class="row mb-3"><div class="col-12"><a href="<?php echo site_url('Ajuan_judul_pkm'); ?>" class="btn btn-secondary btn-sm">Kembali</a></div></div>
<?php if ($this->session->flashdata('success')): ?><div class="alert alert-success"><?php echo $this->session->flashdata('success'); ?></div><?php endif; ?>
<?php if ($this->session->flashdata('error')): ?><div class="alert alert-danger"><?php echo $this->session->flashdata('error'); ?></div><?php endif; ?>

<div class="row">
    <div class="col-md-7">
        <div class="card">
            <div class="card-body">
                <table class="table table-sm">
                    <tr><th>Ketua</th><td><?php echo htmlspecialchars($pengajuan->ketua_nim . ' — ' . $pengajuan->ketua_nama); ?></td></tr>
                    <tr><th>Jenis</th><td><?php echo htmlspecialchars($pengajuan->nama_jenis); ?></td></tr>
                    <tr><th>Judul</th><td><strong><?php echo htmlspecialchars($pengajuan->judul_pkm); ?></strong></td></tr>
                    <tr><th>Proposal</th><td><?php echo $pengajuan->link_proposal_awal ? pkm_google_drive_link_html($pengajuan->link_proposal_awal, 'Buka Proposal', 'proposal_awal', $pengajuan->id) : '-'; ?></td></tr>
                    <tr><th>Status</th><td><span class="badge badge-<?php echo pkm_status_acc_badge($pengajuan->status_acc_judul); ?>"><?php echo ucfirst($pengajuan->status_acc_judul); ?></span></td></tr>
                </table>
                <h5>Anggota</h5>
                <ul>
                    <?php foreach ($anggota as $a): ?>
                        <li><?php echo htmlspecialchars($a->tipe === 'internal' ? ($a->nama_mahasiswa ?? '-') : ($a->nama_manual ?? '-')); ?> — <?php echo htmlspecialchars($a->prodi_asal); ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        </div>
    </div>
    <?php if ($pengajuan->status_acc_judul === 'pending'): ?>
    <div class="col-md-5">
        <div class="card card-primary">
            <div class="card-header">Review Judul</div>
            <form method="post" action="<?php echo site_url('Ajuan_judul_pkm/proses/' . $pengajuan->id); ?>">
                <div class="card-body">
                    <div class="form-group">
                        <label>Catatan</label>
                        <textarea name="catatan_pembimbing" class="form-control" rows="3"></textarea>
                    </div>
                </div>
                <div class="card-footer">
                    <button type="submit" name="action" value="acc" class="btn btn-success">ACC Judul</button>
                    <button type="submit" name="action" value="revisi" class="btn btn-warning">Minta Revisi</button>
                </div>
            </form>
        </div>
    </div>
    <?php endif; ?>
</div>
