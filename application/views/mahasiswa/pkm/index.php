<?php $this->load->helper('pkm'); ?>
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">PKM — Pekan Kreativitas Mahasiswa</h3>
            </div>
            <div class="card-body">
                <?php if ($this->session->flashdata('success')): ?><div class="alert alert-success"><?php echo $this->session->flashdata('success'); ?></div><?php endif; ?>
                <?php if ($this->session->flashdata('error')): ?><div class="alert alert-danger"><?php echo $this->session->flashdata('error'); ?></div><?php endif; ?>

                <?php if (empty($periode_list)): ?>
                    <div class="alert alert-info">Belum ada periode PKM yang aktif.</div>
                <?php else: ?>
                    <h5 class="mb-3"><i class="fas fa-calendar-check text-success"></i> Periode Aktif</h5>
                    <div class="mb-4">
                        <?php foreach ($periode_list as $p):
                            $deadline_iso = date('Y-m-d', strtotime($p->deadline_pengajuan)) . 'T23:59:59';
                            $pengajuan_anggota = null;
                            foreach ($pengajuan_tim as $pg) {
                                if ((int) $pg->id_pkm_periode === (int) $p->id && $pg->peran === 'anggota') {
                                    $pengajuan_anggota = $pg;
                                    break;
                                }
                            }
                        ?>
                            <div class="card border-success shadow-sm mb-3 pkm-periode-aktif" style="border-width:2px;background:linear-gradient(135deg,#f0fff4 0%,#ffffff 100%);">
                                <div class="card-body py-3">
                                    <div class="d-flex flex-wrap justify-content-between align-items-start">
                                        <div class="mb-2 mb-md-0">
                                            <span class="badge badge-success mb-2"><i class="fas fa-circle blink-dot"></i> Periode Aktif</span>
                                            <h5 class="mb-1 text-success font-weight-bold"><?php echo htmlspecialchars($p->nama_periode); ?></h5>
                                            <p class="text-muted small mb-2">Deadline pengajuan: <?php echo date('d/m/Y', strtotime($p->deadline_pengajuan)); ?></p>
                                            <div class="pkm-countdown p-2 rounded" data-deadline="<?php echo htmlspecialchars($deadline_iso); ?>" style="background:rgba(40,167,69,.12);border:1px dashed #28a745;display:inline-block;">
                                                <small class="text-success d-block mb-1"><i class="fas fa-hourglass-half"></i> Sisa waktu pengajuan</small>
                                                <span class="countdown-text font-weight-bold text-success" style="font-size:1.05rem;letter-spacing:.5px;">—</span>
                                            </div>
                                        </div>
                                        <div class="text-right">
                                            <?php if (!$p->pengajuan_ketua && !$pengajuan_anggota): ?>
                                            <a href="<?php echo site_url('Pkm_mahasiswa/ajukan/' . pkm_encoded_id($p->id)); ?>" class="btn btn-success btn-sm">
                                                <i class="fas fa-paper-plane"></i> Ajukan Judul PKM
                                            </a>
                                            <?php elseif ($p->pengajuan_ketua && $p->pengajuan_ketua->status_verifikasi_sekprodi === 'ditolak'): ?>
                                            <a href="<?php echo site_url('Pkm_mahasiswa/ajukan/' . pkm_encoded_id($p->id)); ?>" class="btn btn-success btn-sm">
                                                <i class="fas fa-paper-plane"></i> Ajukan Ulang
                                            </a>
                                            <?php elseif ($p->pengajuan_ketua): ?>
                                            <a href="<?php echo site_url('Pkm_mahasiswa/detail/' . pkm_encoded_id($p->pengajuan_ketua->id)); ?>" class="btn btn-outline-success btn-sm"><i class="fas fa-eye"></i> Lihat Pengajuan</a>
                                            <?php elseif ($pengajuan_anggota): ?>
                                            <a href="<?php echo site_url('Pkm_mahasiswa/detail/' . pkm_encoded_id($pengajuan_anggota->id)); ?>" class="btn btn-outline-info btn-sm"><i class="fas fa-users"></i> Lihat Tim PKM</a>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>

                <h5>Tim PKM Saya</h5>
                <?php if (empty($pengajuan_tim)): ?>
                    <p class="text-muted">Belum terdaftar pada tim PKM manapun.</p>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-bordered table-sm">
                            <thead>
                                <tr>
                                    <th>Periode</th>
                                    <th>Peran</th>
                                    <th>Jenis</th>
                                    <th>Judul</th>
                                    <th>Ketua</th>
                                    <th>Sekprodi</th>
                                    <th>Judul (Dosen)</th>
                                    <th>Sisa Waktu Submit Proposal Final</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($pengajuan_tim as $pg): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($pg->nama_periode); ?></td>
                                        <td>
                                            <span class="badge badge-<?php echo $pg->peran === 'ketua' ? 'primary' : 'info'; ?>">
                                                <?php echo $pg->peran === 'ketua' ? 'Ketua' : 'Anggota'; ?>
                                            </span>
                                        </td>
                                        <td><?php echo htmlspecialchars($pg->nama_jenis); ?></td>
                                        <td><?php echo htmlspecialchars($pg->judul_disetujui ?: $pg->judul_pkm); ?></td>
                                        <td><?php echo htmlspecialchars($pg->ketua_nama); ?></td>
                                        <td><span class="badge badge-<?php echo pkm_status_verifikasi_badge($pg->status_verifikasi_sekprodi); ?>"><?php echo ucfirst($pg->status_verifikasi_sekprodi); ?></span></td>
                                        <td><span class="badge badge-<?php echo pkm_status_acc_badge($pg->status_acc_judul); ?>"><?php echo ucfirst($pg->status_acc_judul); ?></span></td>
                                        <td>
                                            <?php if (!empty($pg->tanggal_selesai)): ?>
                                                <?php if (!pkm_proposal_final_submit_allowed($pg)): ?>
                                                    <span class="text-danger small">Deadline lewat</span>
                                                <?php else: ?>
                                                    <div class="pkm-countdown-proposal" data-deadline="<?php echo htmlspecialchars(pkm_proposal_final_deadline_iso($pg->tanggal_selesai)); ?>">
                                                        <span class="countdown-text small font-weight-bold text-success">—</span>
                                                    </div>
                                                    <small class="text-muted d-block">s/d <?php echo date('d/m/Y', strtotime($pg->tanggal_selesai)); ?></small>
                                                <?php endif; ?>
                                            <?php else: ?>
                                                <span class="text-muted">-</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <a href="<?php echo site_url('Pkm_mahasiswa/detail/' . pkm_encoded_id($pg->id)); ?>" class="btn btn-xs btn-info">Detail</a>
                                            <?php if (pkm_bimbingan_aktif($pg)): ?>
                                                <a href="<?php echo site_url('Bimbingan_pkm_mahasiswa'); ?>" class="btn btn-xs btn-success">Bimbingan</a>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<style>
.pkm-periode-aktif .blink-dot { animation: pkm-pulse 1.5s ease-in-out infinite; font-size: .5rem; vertical-align: middle; }
@keyframes pkm-pulse { 0%,100% { opacity: 1; } 50% { opacity: .35; } }
</style>
<script>
(function() {
    function pad(n) { return n < 10 ? '0' + n : n; }
    function updateCountdowns() {
        document.querySelectorAll('.pkm-countdown, .pkm-countdown-proposal').forEach(function(box) {
            var el = box.querySelector('.countdown-text');
            if (!el) return;
            var deadline = new Date(box.getAttribute('data-deadline')).getTime();
            var diff = deadline - Date.now();
            if (isNaN(deadline) || diff <= 0) {
                el.innerHTML = '<span class="text-danger">Deadline telah lewat</span>';
                box.style.background = 'rgba(220,53,69,.08)';
                box.style.borderColor = '#dc3545';
                return;
            }
            var days = Math.floor(diff / 86400000);
            var hours = Math.floor((diff % 86400000) / 3600000);
            var mins = Math.floor((diff % 3600000) / 60000);
            var secs = Math.floor((diff % 60000) / 1000);
            el.innerHTML = days + ' <small>hari</small> ' + pad(hours) + ' <small>jam</small> ' + pad(mins) + ' <small>menit</small> ' + pad(secs) + ' <small>detik</small>';
        });
    }
    updateCountdowns();
    setInterval(updateCountdowns, 1000);
})();
</script>
