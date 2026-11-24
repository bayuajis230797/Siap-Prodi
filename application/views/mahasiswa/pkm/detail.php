<?php $this->load->helper(array('pkm', 'google_drive')); ?>
<div class="row mb-3">
    <div class="col-12">
        <a href="<?php echo site_url('Pkm_mahasiswa'); ?>" class="btn btn-secondary btn-sm"><i class="fas fa-arrow-left"></i> Kembali</a>
        <?php if ($bimbingan_aktif): ?>
            <a href="<?php echo site_url('Bimbingan_pkm_mahasiswa'); ?>" class="btn btn-success btn-sm">Bimbingan PKM</a>
        <?php endif; ?>
        <?php if (!empty($is_ketua) && $pengajuan->status_acc_judul === 'revisi' && empty($has_pending_perubahan)): ?>
            <a href="<?php echo site_url('Pkm_perubahan_judul/ajukan/' . pkm_encoded_id($pengajuan->id)); ?>" class="btn btn-warning btn-sm">Ajukan Perubahan Judul</a>
        <?php endif; ?>
    </div>
</div>

<?php if ($this->session->flashdata('success')): ?><div class="alert alert-success"><?php echo $this->session->flashdata('success'); ?></div><?php endif; ?>
<?php if ($this->session->flashdata('error')): ?><div class="alert alert-danger"><?php echo $this->session->flashdata('error'); ?></div><?php endif; ?>

<div class="row">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header"><h3 class="card-title">Status Pengajuan<?php echo empty($is_ketua) ? ' Tim' : ''; ?></h3></div>
            <div class="card-body">
                <?php if (empty($is_ketua)): ?>
                    <div class="alert alert-info py-2"><i class="fas fa-info-circle"></i> Anda terdaftar sebagai <strong>anggota tim</strong> PKM ini.</div>
                <?php endif; ?>
                <table class="table table-sm">
                    <tr><th>Periode</th><td><?php echo htmlspecialchars($pengajuan->nama_periode); ?></td></tr>
                    <tr><th>Jenis</th><td><?php echo htmlspecialchars($pengajuan->nama_jenis); ?></td></tr>
                    <tr><th>Judul</th><td><strong><?php echo htmlspecialchars($pengajuan->judul_pkm); ?></strong>
                        <?php if ($pengajuan->judul_disetujui): ?><br><small class="text-success">Disetujui: <?php echo htmlspecialchars($pengajuan->judul_disetujui); ?></small><?php endif; ?>
                    </td></tr>
                    <tr><th>Verifikasi Sekprodi</th><td><span class="badge badge-<?php echo pkm_status_verifikasi_badge($pengajuan->status_verifikasi_sekprodi); ?>"><?php echo ucfirst($pengajuan->status_verifikasi_sekprodi); ?></span></td></tr>
                    <tr><th>ACC Judul (Dosen)</th><td><span class="badge badge-<?php echo pkm_status_acc_badge($pengajuan->status_acc_judul); ?>"><?php echo ucfirst($pengajuan->status_acc_judul); ?></span>
                        <?php if ($pengajuan->catatan_pembimbing): ?><br><small><?php echo nl2br(htmlspecialchars($pengajuan->catatan_pembimbing)); ?></small><?php endif; ?>
                    </td></tr>
                    <tr><th>Pembimbing</th><td><?php echo htmlspecialchars($pengajuan->pembimbing_final_nama ?? $pengajuan->pembimbing_request_nama ?? '-'); ?></td></tr>
                    <tr><th>Proposal Awal</th><td><?php echo $pengajuan->link_proposal_awal ? pkm_google_drive_link_html($pengajuan->link_proposal_awal, 'Buka Proposal', 'proposal_awal', $pengajuan->id) : '-'; ?></td></tr>
                    <tr><th>Proposal Final</th><td>
                        <?php if (!pkm_proposal_final_aktif($pengajuan)): ?>
                            <span class="text-muted">Menunggu finalisasi dosen</span>
                        <?php else: ?>
                            <span class="badge badge-<?php echo pkm_status_acc_badge($pengajuan->status_acc_proposal); ?>"><?php echo pkm_status_verifikasi_proposal_final_label($pengajuan->status_acc_proposal); ?></span>
                        <?php endif; ?>
                    </td></tr>
                </table>
                <?php if ($pengajuan->catatan_sekprodi): ?>
                    <div class="alert alert-warning"><strong>Catatan Sekprodi:</strong> <?php echo nl2br(htmlspecialchars($pengajuan->catatan_sekprodi)); ?></div>
                <?php endif; ?>

                <h5 class="mt-3">Anggota Tim</h5>
                <table class="table table-sm table-bordered">
                    <thead><tr><th>#</th><th>NIM</th><th>Nama</th><th>Prodi</th></tr></thead>
                    <tbody>
                        <tr class="table-primary"><td>K</td><td><?php echo htmlspecialchars($pengajuan->ketua_nim); ?></td><td><?php echo htmlspecialchars($pengajuan->ketua_nama); ?> <em>(Ketua)</em></td><td>S1 Sistem Informasi</td></tr>
                        <?php foreach ($anggota as $a): ?>
                            <tr>
                                <td><?php echo (int) $a->urutan; ?></td>
                                <td><?php echo htmlspecialchars($a->tipe === 'internal' ? ($a->nim ?? '-') : ($a->nim_manual ?? '-')); ?></td>
                                <td><?php echo htmlspecialchars($a->tipe === 'internal' ? ($a->nama_mahasiswa ?? '-') : ($a->nama_manual ?? '-')); ?></td>
                                <td><?php echo htmlspecialchars($a->tipe === 'internal' ? pkm_prodi_default() : ($a->prodi_asal ?? '-')); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>

                <?php if (!empty($is_ketua) && $pengajuan->status_verifikasi_sekprodi === 'ditolak'): ?>
                    <a href="<?php echo site_url('Pkm_mahasiswa/ajukan/' . pkm_encoded_id($pengajuan->id_pkm_periode)); ?>" class="btn btn-primary">Ajukan Ulang</a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
