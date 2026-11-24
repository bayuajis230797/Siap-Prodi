<?php
$this->load->helper('google_drive');
function mhs_skpi_badge($s) {
    $map = array('pending' => 'warning', 'disetujui' => 'success', 'ditolak' => 'danger', 'ditolak_permanen' => 'dark');
    $label = array('pending' => 'Menunggu verifikasi', 'disetujui' => 'Disetujui', 'ditolak' => 'Ditolak — perbaiki', 'ditolak_permanen' => 'Ditolak permanen');
    return '<span class="badge badge-' . ($map[$s] ?? 'secondary') . '">' . ($label[$s] ?? $s) . '</span>';
}
?>
<div class="row mb-2">
    <div class="col-12">
        <a href="<?php echo site_url('Skpi_mahasiswa'); ?>" class="btn btn-secondary btn-sm"><i class="fas fa-arrow-left"></i> Kembali</a>
    </div>
</div>
<?php if ($this->session->flashdata('success')): ?><div class="alert alert-success"><?php echo $this->session->flashdata('success'); ?></div><?php endif; ?>
<?php if ($this->session->flashdata('error')): ?><div class="alert alert-danger"><?php echo $this->session->flashdata('error'); ?></div><?php endif; ?>

<?php if ($deadline_lewat): ?>
    <div class="alert alert-danger"><i class="fas fa-clock"></i> <strong>Deadline telah berakhir</strong> (<?php echo date('d/m/Y', strtotime($periode->deadline_pengumpulan)); ?>). Pengajuan baru dan perbaikan tidak dapat dilakukan.</div>
<?php elseif ($upload_allowed): ?>
    <div class="alert alert-info"><i class="fas fa-info-circle"></i> Batas pengumpulan: <strong><?php echo date('d/m/Y', strtotime($periode->deadline_pengumpulan)); ?></strong>. Anda dapat menambah lebih dari satu sertifikat per jenis berkas.</div>
<?php else: ?>
    <div class="alert alert-warning">Periode ini belum aktif untuk pengumpulan berkas.</div>
<?php endif; ?>

<?php if (empty($jenis_list)): ?>
    <div class="alert alert-warning">Sekprodi belum mengatur jenis berkas untuk periode ini.</div>
<?php else: ?>
    <?php foreach ($jenis_list as $jenis):
        $entri = isset($uploads_by_jenis[$jenis->id]) ? $uploads_by_jenis[$jenis->id] : array();
    ?>
        <div class="card mb-3">
            <div class="card-header bg-light">
                <strong><?php echo htmlspecialchars($jenis->nama_berkas); ?></strong>
                <?php if ($jenis->petunjuk): ?><br><small class="text-muted"><?php echo htmlspecialchars($jenis->petunjuk); ?></small><?php endif; ?>
            </div>
            <div class="card-body">
                <?php if (!empty($entri)): ?>
                    <?php foreach ($entri as $u): ?>
                        <div class="border rounded p-3 mb-2">
                            <?php echo mhs_skpi_badge($u->status); ?>
                            <?php if (!empty($u->nama_sertifikat)): ?>
                                <p class="mt-2 mb-1"><strong>Nama Sertifikat:</strong> <?php echo htmlspecialchars($u->nama_sertifikat); ?></p>
                            <?php endif; ?>
                            <p class="mb-1"><strong>Deskripsi:</strong> <?php echo nl2br(htmlspecialchars($u->deskripsi_berkas)); ?></p>
                            <p class="mb-2"><?php echo google_drive_link_html($u->link_drive, 'Buka Berkas'); ?></p>
                            <?php if ($u->catatan_sekprodi): ?>
                                <p class="text-danger small"><strong>Catatan sekprodi:</strong> <?php echo nl2br(htmlspecialchars($u->catatan_sekprodi)); ?></p>
                            <?php endif; ?>
                            <?php if ($upload_allowed && $u->status === 'ditolak'): ?>
                                <button type="button" class="btn btn-sm btn-warning" data-bs-toggle="collapse" data-bs-target="#edit<?php echo $u->id; ?>" aria-expanded="false" aria-controls="edit<?php echo $u->id; ?>">Perbaiki</button>
                                <div class="collapse mt-2" id="edit<?php echo $u->id; ?>">
                                    <form method="post" action="<?php echo site_url('Skpi_mahasiswa/update_entri/' . $u->id); ?>" enctype="multipart/form-data">
                                        <div class="form-group">
                                            <label>Nama Sertifikat <span class="text-danger">*</span></label>
                                            <input type="text" name="nama_sertifikat" class="form-control" required maxlength="255" value="<?php echo htmlspecialchars($u->nama_sertifikat ?? ''); ?>" placeholder="Contoh: Sertifikat TOEFL">
                                        </div>
                                        <div class="form-group">
                                            <label>Ganti Berkas (opsional)</label>
                                            <input type="file" name="file_berkas" class="form-control-file" accept=".pdf,.doc,.docx,.jpg,.jpeg,.png">
                                            <small class="text-muted">Kosongkan jika tidak mengganti file.</small>
                                        </div>
                                        <div class="form-group">
                                            <label>Deskripsi berkas</label>
                                            <textarea name="deskripsi_berkas" class="form-control" rows="2" required><?php echo htmlspecialchars($u->deskripsi_berkas); ?></textarea>
                                        </div>
                                        <button type="submit" class="btn btn-primary btn-sm">Kirim ulang</button>
                                    </form>
                                </div>
                            <?php endif; ?>
                            <?php if ($upload_allowed && in_array($u->status, array('pending', 'ditolak'), true)): ?>
                                <a href="<?php echo site_url('Skpi_mahasiswa/hapus_entri/' . $u->id); ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('Hapus entri ini?');">Hapus</a>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p class="text-muted small">Belum ada berkas untuk jenis ini.</p>
                <?php endif; ?>

                <?php if ($upload_allowed): ?>
                    <button type="button" class="btn btn-sm btn-success mt-2" data-bs-toggle="collapse" data-bs-target="#tambah<?php echo $jenis->id; ?>" aria-expanded="false" aria-controls="tambah<?php echo $jenis->id; ?>">
                        <i class="fas fa-plus"></i> Tambah sertifikat / berkas
                    </button>
                    <div class="collapse mt-2" id="tambah<?php echo $jenis->id; ?>">
                        <form method="post" action="<?php echo site_url('Skpi_mahasiswa/simpan_entri/' . $periode->id); ?>" enctype="multipart/form-data">
                            <input type="hidden" name="id_skpi_jenis_berkas" value="<?php echo (int) $jenis->id; ?>">
                            <div class="form-group">
                                <label>Nama Sertifikat <span class="text-danger">*</span></label>
                                <input type="text" name="nama_sertifikat" class="form-control" required maxlength="255" placeholder="Contoh: Sertifikat Kompetensi Web Design">
                            </div>
                            <div class="form-group">
                                <label>Berkas <span class="text-danger">*</span></label>
                                <input type="file" name="file_berkas" class="form-control-file" accept=".pdf,.doc,.docx,.jpg,.jpeg,.png" required>
                                <small class="text-muted">Diunggah ke Google Drive institusi (hubungkan di Profil). Folder: SIAP/SKPI/berkas</small>
                            </div>
                            <div class="form-group">
                                <label>Deskripsi berkas <span class="text-danger">*</span></label>
                                <textarea name="deskripsi_berkas" class="form-control" rows="2" required placeholder="Contoh: Sertifikat TOEFL skor 500, tahun 2025"></textarea>
                            </div>
                            <button type="submit" class="btn btn-primary btn-sm"><i class="fas fa-paper-plane"></i> Ajukan</button>
                        </form>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    <?php endforeach; ?>
<?php endif; ?>
