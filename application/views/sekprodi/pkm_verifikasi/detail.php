<?php $this->load->helper(array('pkm', 'google_drive')); ?>
<div class="row mb-3">
    <div class="col-12">
        <a href="<?php echo site_url('Pkm_verifikasi'); ?>" class="btn btn-secondary btn-sm"><i class="fas fa-arrow-left"></i> Kembali</a>
    </div>
</div>

<?php if ($this->session->flashdata('success')): ?><div class="alert alert-success"><?php echo $this->session->flashdata('success'); ?></div><?php endif; ?>
<?php if ($this->session->flashdata('error')): ?><div class="alert alert-danger"><?php echo $this->session->flashdata('error'); ?></div><?php endif; ?>

<div class="row">
    <div class="col-md-7">
        <div class="card">
            <div class="card-header"><h3 class="card-title">Data Pengajuan</h3></div>
            <div class="card-body">
                <table class="table table-sm">
                    <tr><th>Periode</th><td><?php echo htmlspecialchars($pengajuan->nama_periode); ?></td></tr>
                    <tr><th>Ketua Tim</th><td><code><?php echo htmlspecialchars($pengajuan->ketua_nim); ?></code> — <?php echo htmlspecialchars($pengajuan->ketua_nama); ?> (<?php echo htmlspecialchars($pengajuan->ketua_kelas); ?>)</td></tr>
                    <tr><th>Jenis Lomba</th><td><?php echo htmlspecialchars($pengajuan->nama_jenis); ?></td></tr>
                    <tr><th>Judul PKM</th><td><strong><?php echo htmlspecialchars($pengajuan->judul_pkm); ?></strong></td></tr>
                    <tr><th>Pembimbing Request</th><td><?php echo htmlspecialchars($pengajuan->pembimbing_request_nama ?? '-'); ?></td></tr>
                    <tr><th>Proposal Awal</th><td><?php echo $pengajuan->link_proposal_awal ? pkm_google_drive_link_html($pengajuan->link_proposal_awal, 'Buka Proposal', 'proposal_awal', $pengajuan->id) : '-'; ?></td></tr>
                    <tr><th>Status Sekprodi</th><td><span class="badge badge-<?php echo pkm_status_verifikasi_badge($pengajuan->status_verifikasi_sekprodi); ?>"><?php echo ucfirst($pengajuan->status_verifikasi_sekprodi); ?></span></td></tr>
                    <tr><th>Status Judul (Dosen)</th><td><span class="badge badge-<?php echo pkm_status_acc_badge($pengajuan->status_acc_judul); ?>"><?php echo ucfirst($pengajuan->status_acc_judul); ?></span></td></tr>
                </table>

                <h5 class="mt-3">Anggota Tim (<?php echo count($anggota); ?> / min <?php echo (int) $pengajuan->min_anggota; ?> – max <?php echo (int) $pengajuan->max_anggota; ?>)</h5>
                <table class="table table-sm table-bordered">
                    <thead><tr><th>#</th><th>NIM</th><th>Nama</th><th>Prodi</th><th>Tipe</th></tr></thead>
                    <tbody>
                        <?php if (empty($anggota)): ?>
                            <tr><td colspan="5" class="text-muted text-center">Belum ada anggota.</td></tr>
                        <?php else: foreach ($anggota as $a): ?>
                            <tr>
                                <td><?php echo (int) $a->urutan; ?></td>
                                <td><code><?php echo htmlspecialchars($a->tipe === 'internal' ? ($a->nim ?? '-') : ($a->nim_manual ?? '-')); ?></code></td>
                                <td><?php echo htmlspecialchars($a->tipe === 'internal' ? ($a->nama_mahasiswa ?? '-') : ($a->nama_manual ?? '-')); ?></td>
                                <td><?php echo htmlspecialchars($a->prodi_asal); ?></td>
                                <td><?php echo htmlspecialchars(pkm_anggota_tipe_label($a->tipe)); ?></td>
                            </tr>
                        <?php endforeach; endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <div class="col-md-5">
        <?php if ($pengajuan->status_verifikasi_sekprodi === 'pending'): ?>
        <div class="card card-outline card-primary">
            <div class="card-header"><h3 class="card-title">Verifikasi Sekprodi</h3></div>
            <form method="post" action="<?php echo site_url('Pkm_verifikasi/proses/' . $pengajuan->id); ?>">
                <div class="card-body">
                    <div class="form-group">
                        <label>Dosen Pembimbing Final <span class="text-danger">*</span></label>
                        <select name="id_pembimbing_final" class="form-control" required>
                            <option value="">— Pilih dosen —</option>
                            <?php foreach ($dosen_list as $d): ?>
                                <option value="<?php echo (int) $d->id; ?>" <?php echo ((int) ($pengajuan->id_pembimbing_request ?? 0) === (int) $d->id) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($d->nama_dosen); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Catatan Sekprodi</label>
                        <textarea name="catatan_sekprodi" class="form-control" rows="3"></textarea>
                    </div>
                </div>
                <div class="card-footer">
                    <button type="submit" name="action" value="setujui" class="btn btn-success">Setujui & Plot Pembimbing</button>
                    <button type="submit" name="action" value="tolak" class="btn btn-danger" onclick="return confirm('Tolak pengajuan ini?');">Tolak</button>
                </div>
            </form>
        </div>
        <?php else: ?>
        <div class="card">
            <div class="card-body">
                <p><strong>Pembimbing Final:</strong> <?php echo htmlspecialchars($pengajuan->pembimbing_final_nama ?? '-'); ?></p>
                <?php if ($pengajuan->catatan_sekprodi): ?>
                    <p><strong>Catatan:</strong> <?php echo nl2br(htmlspecialchars($pengajuan->catatan_sekprodi)); ?></p>
                <?php endif; ?>
            </div>
        </div>
        <?php endif; ?>
    </div>
</div>
