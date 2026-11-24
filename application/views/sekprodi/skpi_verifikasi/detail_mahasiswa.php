<?php
$this->load->helper('google_drive');
function skpi_status_badge($s) {
    $map = array(
        'pending' => 'warning',
        'disetujui' => 'success',
        'ditolak' => 'danger',
        'ditolak_permanen' => 'dark',
    );
    $label = array(
        'pending' => 'Menunggu',
        'disetujui' => 'Disetujui',
        'ditolak' => 'Ditolak (bisa perbaiki)',
        'ditolak_permanen' => 'Ditolak permanen',
    );
    return '<span class="badge badge-' . ($map[$s] ?? 'secondary') . '">' . ($label[$s] ?? $s) . '</span>';
}
?>
<div class="row mb-2">
    <div class="col-12">
        <a href="<?php echo site_url('Skpi_verifikasi?id_periode=' . $periode->id); ?>" class="btn btn-secondary btn-sm"><i class="fas fa-arrow-left"></i> Kembali</a>
    </div>
</div>
<?php if ($this->session->flashdata('success')): ?><div class="alert alert-success"><?php echo $this->session->flashdata('success'); ?></div><?php endif; ?>
<?php if ($this->session->flashdata('error')): ?><div class="alert alert-danger"><?php echo $this->session->flashdata('error'); ?></div><?php endif; ?>

<div class="card mb-3">
    <div class="card-body">
        <h5><?php echo htmlspecialchars($mhs->nama_mahasiswa); ?> — <code><?php echo htmlspecialchars($mhs->nim); ?></code> — <?php echo htmlspecialchars($mhs->kelas); ?></h5>
        <p class="text-muted mb-0">Periode: <?php echo htmlspecialchars($periode->nama_periode); ?></p>
    </div>
</div>

<?php if (empty($grouped)): ?>
    <div class="alert alert-info">Belum ada pengajuan berkas dari mahasiswa ini.</div>
<?php else: ?>
    <?php foreach ($grouped as $nama_jenis => $items): ?>
        <div class="card mb-3">
            <div class="card-header"><strong><?php echo htmlspecialchars($nama_jenis); ?></strong></div>
            <div class="card-body">
                <?php foreach ($items as $u): ?>
                    <div class="border rounded p-3 mb-3">
                        <div class="d-flex justify-content-between flex-wrap">
                            <div><?php echo skpi_status_badge($u->status); ?> <small class="text-muted"><?php echo date('d/m/Y H:i', strtotime($u->created_at)); ?></small></div>
                        </div>
                        <?php if (!empty($u->nama_sertifikat)): ?>
                            <p class="mb-1 mt-2"><strong>Nama Sertifikat:</strong> <?php echo htmlspecialchars($u->nama_sertifikat); ?></p>
                        <?php endif; ?>
                        <p class="mb-1"><strong>Deskripsi:</strong> <?php echo nl2br(htmlspecialchars($u->deskripsi_berkas)); ?></p>
                        <p class="mb-2"><?php echo google_drive_link_html($u->link_drive, 'Buka Berkas'); ?></p>
                        <?php if ($u->catatan_sekprodi): ?>
                            <p class="text-danger small mb-2"><strong>Catatan sekprodi:</strong> <?php echo nl2br(htmlspecialchars($u->catatan_sekprodi)); ?></p>
                        <?php endif; ?>
                        <?php if ($u->status === 'pending'): ?>
                            <a href="<?php echo site_url('Skpi_verifikasi/setujui/' . $u->id); ?>" class="btn btn-sm btn-success" onclick="return confirm('Setujui pengajuan ini?');"><i class="fas fa-check"></i> Setujui</a>
                            <button type="button" class="btn btn-sm btn-warning" data-bs-toggle="modal" data-bs-target="#modalTolak<?php echo $u->id; ?>"><i class="fas fa-times"></i> Tolak</button>
                            <div class="modal fade" id="modalTolak<?php echo $u->id; ?>" tabindex="-1" aria-labelledby="modalTolakLabel<?php echo $u->id; ?>" aria-hidden="true">
                                <div class="modal-dialog">
                                    <form method="post" action="<?php echo site_url('Skpi_verifikasi/tolak/' . $u->id); ?>">
                                        <div class="modal-content">
                                            <div class="modal-header"><h5 class="modal-title" id="modalTolakLabel<?php echo $u->id; ?>">Tolak Pengajuan</h5><button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button></div>
                                            <div class="modal-body">
                                                <div class="form-group">
                                                    <label>Catatan untuk mahasiswa <span class="text-danger">*</span></label>
                                                    <textarea name="catatan_sekprodi" class="form-control" rows="3" required></textarea>
                                                </div>
                                                <div class="form-group">
                                                    <label>Jenis penolakan</label>
                                                    <div class="form-check">
                                                        <input class="form-check-input" type="radio" name="jenis_tolak" id="rev<?php echo $u->id; ?>" value="revisi" checked>
                                                        <label class="form-check-label" for="rev<?php echo $u->id; ?>">Ditolak — mahasiswa boleh memperbaiki</label>
                                                    </div>
                                                    <div class="form-check">
                                                        <input class="form-check-input" type="radio" name="jenis_tolak" id="perm<?php echo $u->id; ?>" value="permanen">
                                                        <label class="form-check-label" for="perm<?php echo $u->id; ?>">Ditolak permanen — tidak bisa diperbaiki</label>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                                                <button type="submit" class="btn btn-danger">Tolak</button>
                                            </div>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    <?php endforeach; ?>
<?php endif; ?>
