<div class="row">
    <div class="col-md-8">
        <div class="card card-primary">
            <div class="card-header"><h3 class="card-title"><?php echo $periode ? 'Edit' : 'Tambah'; ?> Periode PKM</h3></div>
            <form method="post" action="<?php echo $periode ? site_url('Pkm/update/' . $periode->id) : site_url('Pkm/store'); ?>">
                <div class="card-body">
                    <div class="form-group">
                        <label>Nama Periode <span class="text-danger">*</span></label>
                        <input type="text" name="nama_periode" class="form-control" required value="<?php echo $periode ? htmlspecialchars($periode->nama_periode) : ''; ?>" placeholder="Contoh: PKM 2025/2026 Ganjil">
                    </div>
                    <div class="form-group">
                        <label>Deskripsi</label>
                        <textarea name="deskripsi" class="form-control" rows="2"><?php echo $periode ? htmlspecialchars($periode->deskripsi ?? '') : ''; ?></textarea>
                    </div>
                    <div class="form-group">
                        <label>Deadline Pengajuan <span class="text-danger">*</span></label>
                        <input type="date" name="deadline_pengajuan" class="form-control" required value="<?php echo $periode ? htmlspecialchars($periode->deadline_pengajuan) : ''; ?>">
                        <small class="text-muted">Mahasiswa tidak dapat mengajukan setelah tanggal ini.</small>
                    </div>
                    <div class="form-row">
                        <div class="form-group col-md-6">
                            <label>Tanggal Mulai</label>
                            <input type="date" name="tanggal_mulai" class="form-control" value="<?php echo ($periode && $periode->tanggal_mulai) ? htmlspecialchars($periode->tanggal_mulai) : ''; ?>">
                        </div>
                        <div class="form-group col-md-6">
                            <label>Tanggal Selesai <span class="text-danger">*</span></label>
                            <input type="date" name="tanggal_selesai" class="form-control" required value="<?php echo ($periode && $periode->tanggal_selesai) ? htmlspecialchars($periode->tanggal_selesai) : ''; ?>">
                            <small class="text-muted">Batas akhir mahasiswa (ketua tim) submit Proposal Final PKM.</small>
                        </div>
                    </div>
                    <?php if (!empty($tahun_aktif)): ?>
                        <p class="text-muted small">Tahun akademik aktif: <?php echo htmlspecialchars($tahun_aktif->tahun_akademik . ' ' . ucfirst($tahun_aktif->semester)); ?></p>
                    <?php endif; ?>
                </div>
                <div class="card-footer">
                    <button type="submit" class="btn btn-primary">Simpan</button>
                    <a href="<?php echo site_url('Pkm'); ?>" class="btn btn-secondary">Batal</a>
                </div>
            </form>
        </div>
    </div>
</div>
