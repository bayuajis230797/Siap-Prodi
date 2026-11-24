<?php $status_badge = array('draft' => 'secondary', 'aktif' => 'success', 'selesai' => 'info', 'dibatalkan' => 'danger'); ?>
<div class="row mb-3">
    <div class="col-12">
        <a href="<?php echo site_url('Pkm'); ?>" class="btn btn-secondary btn-sm"><i class="fas fa-arrow-left"></i> Kembali</a>
        <a href="<?php echo site_url('Pkm/edit/' . $periode->id); ?>" class="btn btn-warning btn-sm">Edit Periode</a>
        <a href="<?php echo site_url('Pkm_verifikasi?id_periode=' . $periode->id); ?>" class="btn btn-info btn-sm">Verifikasi Pengajuan</a>
    </div>
</div>

<?php if ($this->session->flashdata('success')): ?><div class="alert alert-success"><?php echo $this->session->flashdata('success'); ?></div><?php endif; ?>
<?php if ($this->session->flashdata('error')): ?><div class="alert alert-danger"><?php echo $this->session->flashdata('error'); ?></div><?php endif; ?>

<div class="row">
    <div class="col-md-6">
        <div class="card">
            <div class="card-header"><h3 class="card-title">Informasi Periode</h3></div>
            <div class="card-body">
                <table class="table table-sm">
                    <tr><th>Nama</th><td><strong><?php echo htmlspecialchars($periode->nama_periode); ?></strong></td></tr>
                    <tr><th>Deadline Pengajuan</th><td>
                        <?php echo date('d/m/Y', strtotime($periode->deadline_pengajuan)); ?>
                        <?php if ($deadline_lewat): ?><span class="badge badge-danger ml-1">Sudah lewat</span><?php endif; ?>
                    </td></tr>
                    <tr><th>Tanggal Selesai (Batas Submit Proposal Final)</th><td>
                        <?php echo $periode->tanggal_selesai ? date('d/m/Y', strtotime($periode->tanggal_selesai)) : '-'; ?>
                        <?php if (!empty($periode->tanggal_selesai) && $this->Pkm_periode_model->is_proposal_final_deadline_lewat($periode)): ?>
                            <span class="badge badge-danger ml-1">Sudah lewat</span>
                        <?php endif; ?>
                    </td></tr>
                    <tr><th>Status</th><td>
                        <span class="badge badge-<?php echo $status_badge[$periode->status] ?? 'secondary'; ?>"><?php echo ucfirst($periode->status); ?></span>
                        <select id="sel-status-pkm" class="form-control form-control-sm d-inline-block ml-2" style="width:auto">
                            <option value="draft" <?php echo $periode->status === 'draft' ? 'selected' : ''; ?>>Draft</option>
                            <option value="aktif" <?php echo $periode->status === 'aktif' ? 'selected' : ''; ?>>Aktif</option>
                            <option value="selesai" <?php echo $periode->status === 'selesai' ? 'selected' : ''; ?>>Selesai</option>
                            <option value="dibatalkan" <?php echo $periode->status === 'dibatalkan' ? 'selected' : ''; ?>>Dibatalkan</option>
                        </select>
                        <button type="button" class="btn btn-sm btn-outline-primary" id="btn-simpan-status">Simpan status</button>
                    </td></tr>
                    <tr><th>Jenis lomba</th><td><?php echo count($jenis_list); ?> item</td></tr>
                </table>
                <?php if ($periode->deskripsi): ?><p class="text-muted small"><?php echo nl2br(htmlspecialchars($periode->deskripsi)); ?></p><?php endif; ?>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card card-outline card-success">
            <div class="card-header"><h3 class="card-title">Jenis Lomba PKM</h3></div>
            <div class="card-body">
                <form method="post" action="<?php echo site_url('Pkm/tambah_jenis/' . $periode->id); ?>" class="mb-3 border-bottom pb-3">
                    <div class="form-group mb-2">
                        <input type="text" name="nama_jenis" class="form-control form-control-sm" placeholder="Nama jenis lomba *" required>
                    </div>
                    <div class="form-group mb-2">
                        <input type="text" name="deskripsi" class="form-control form-control-sm" placeholder="Deskripsi (opsional)">
                    </div>
                    <div class="form-row mb-2">
                        <div class="col-4">
                            <input type="number" name="min_anggota" class="form-control form-control-sm" placeholder="Min anggota" value="1" min="0" title="Min anggota (selain ketua)">
                        </div>
                        <div class="col-4">
                            <input type="number" name="max_anggota" class="form-control form-control-sm" placeholder="Max anggota" value="4" min="0" title="Max anggota (selain ketua)">
                        </div>
                        <div class="col-2">
                            <input type="number" name="urutan" class="form-control form-control-sm" placeholder="Urut" value="0">
                        </div>
                        <div class="col-2">
                            <button type="submit" class="btn btn-success btn-sm btn-block"><i class="fas fa-plus"></i></button>
                        </div>
                    </div>
                    <small class="text-muted">Min/max = jumlah anggota tim selain ketua.</small>
                </form>
                <?php if (empty($jenis_list)): ?>
                    <p class="text-muted">Belum ada jenis lomba. Contoh: PKM-K, PKM-PM, PKM-T, PKM-AI.</p>
                <?php else: ?>
                    <table class="table table-sm">
                        <thead><tr><th>Urut</th><th>Jenis</th><th>Anggota</th><th></th></tr></thead>
                        <tbody>
                            <?php foreach ($jenis_list as $j): ?>
                                <tr>
                                    <td><?php echo (int) $j->urutan; ?></td>
                                    <td>
                                        <strong><?php echo htmlspecialchars($j->nama_jenis); ?></strong>
                                        <?php if ($j->deskripsi): ?><br><small class="text-muted"><?php echo htmlspecialchars($j->deskripsi); ?></small><?php endif; ?>
                                    </td>
                                    <td><?php echo (int) $j->min_anggota; ?>–<?php echo (int) $j->max_anggota; ?></td>
                                    <td>
                                        <a href="<?php echo site_url('Pkm/hapus_jenis/' . $j->id); ?>" class="btn btn-xs btn-danger" onclick="return confirm('Hapus jenis lomba ini?');"><i class="fas fa-trash"></i></a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<script>
document.getElementById('btn-simpan-status').addEventListener('click', function() {
    var status = document.getElementById('sel-status-pkm').value;
    fetch('<?php echo site_url('Pkm/update_status/' . $periode->id); ?>', {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded', 'X-Requested-With': 'XMLHttpRequest'},
        body: 'status=' + encodeURIComponent(status) + '&<?php echo $this->security->get_csrf_token_name(); ?>=' + encodeURIComponent('<?php echo $this->security->get_csrf_hash(); ?>')
    }).then(function(r) { return r.json(); }).then(function(data) {
        alert(data.message);
        if (data.success) location.reload();
    });
});
</script>
