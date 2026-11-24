<?php $this->load->helper('pkm'); ?>
<div class="card">
    <div class="card-header"><h3 class="card-title">Ajuan Judul PKM</h3></div>
    <div class="card-body">
        <?php if ($this->session->flashdata('success')): ?><div class="alert alert-success"><?php echo $this->session->flashdata('success'); ?></div><?php endif; ?>
        <form method="get" class="mb-3">
            <select name="status" class="form-control d-inline-block" style="width:auto">
                <option value="">Semua status ACC</option>
                <option value="pending" <?php echo $filter_status === 'pending' ? 'selected' : ''; ?>>Pending</option>
                <option value="acc" <?php echo $filter_status === 'acc' ? 'selected' : ''; ?>>ACC</option>
                <option value="revisi" <?php echo $filter_status === 'revisi' ? 'selected' : ''; ?>>Revisi</option>
            </select>
            <button type="submit" class="btn btn-sm btn-info">Filter</button>
        </form>
        <table class="table table-bordered table-sm">
            <thead><tr><th>Ketua</th><th>Jenis</th><th>Judul</th><th>Periode</th><th>Status</th><th></th></tr></thead>
            <tbody>
                <?php if (empty($pengajuan_list)): ?>
                    <tr><td colspan="6" class="text-center text-muted">Tidak ada ajuan.</td></tr>
                <?php else: foreach ($pengajuan_list as $p): ?>
                    <tr>
                        <td><code><?php echo htmlspecialchars($p->ketua_nim); ?></code><br><?php echo htmlspecialchars($p->ketua_nama); ?></td>
                        <td><?php echo htmlspecialchars($p->nama_jenis); ?></td>
                        <td><?php echo htmlspecialchars($p->judul_pkm); ?></td>
                        <td><?php echo htmlspecialchars($p->nama_periode); ?></td>
                        <td><span class="badge badge-<?php echo pkm_status_acc_badge($p->status_acc_judul); ?>"><?php echo ucfirst($p->status_acc_judul); ?></span></td>
                        <td><a href="<?php echo site_url('Ajuan_judul_pkm/detail/' . $p->id); ?>" class="btn btn-xs btn-primary">Review</a></td>
                    </tr>
                <?php endforeach; endif; ?>
            </tbody>
        </table>
    </div>
</div>
