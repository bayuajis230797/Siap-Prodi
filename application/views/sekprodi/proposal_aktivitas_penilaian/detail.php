<?php
$f = function ($v) { return ($v === null || $v === '') ? '-' : number_format((float) $v, 2, ',', '.'); };
$tabel = function ($r) use ($f) {
    if (!$r) { return '<p class="text-muted">Belum ada penilaian.</p>'; }
    $h = '<table class="table table-sm table-bordered mb-1"><thead><tr><th>Butir</th><th width="14%" class="text-center">Bobot</th><th width="16%" class="text-center">Nilai</th></tr></thead><tbody>';
    foreach ($r->baris as $b) {
        $h .= '<tr><td>' . htmlspecialchars($b->pertanyaan) . '</td><td class="text-center">' . (float) $b->bobot . '%</td><td class="text-center">' . $f($b->nilai) . '</td></tr>';
    }
    return $h . '<tr class="table-light"><th colspan="2" class="text-right">Total</th><th class="text-center">' . $f($r->total) . '</th></tr></tbody></table>';
};
?>
<div class="row">
    <div class="col-12">
        <div class="card card-outline card-info">
            <div class="card-header">
                <h3 class="card-title">Detail Aktivitas: <?php echo htmlspecialchars($p->nama_mahasiswa ?? '-'); ?> (<?php echo htmlspecialchars($p->nim ?? '-'); ?>)</h3>
                <div class="card-tools"><a href="<?php echo site_url('Proposal_aktivitas_penilaian'); ?>" class="btn btn-secondary btn-sm"><i class="fas fa-arrow-left"></i> Kembali</a></div>
            </div>
            <div class="card-body">
                <p><strong>Judul:</strong> <?php echo htmlspecialchars($judul ?: '-'); ?> &nbsp;|&nbsp; <strong>Status:</strong> <?php echo htmlspecialchars(str_replace('_', ' ', $p->status)); ?></p>

                <h5>Progres (<?php echo (int) $progress['current_step']; ?>/<?php echo (int) $progress['total_steps']; ?> - <?php echo $progress['percentage']; ?>%)</h5>
                <div class="progress mb-2"><div class="progress-bar bg-success" style="width: <?php echo (float) $progress['percentage']; ?>%"></div></div>
                <p class="mb-4">
                    <?php foreach ($progress['steps'] as $s): ?>
                        <span class="badge badge-<?php echo $s['completed'] ? 'success' : 'secondary'; ?> mr-1 mb-1"><?php echo htmlspecialchars($s['name']); ?></span>
                    <?php endforeach; ?>
                </p>

                <div class="row">
                    <div class="col-md-4"><h5>Nilai Bimbingan (Pembimbing)</h5><?php echo $tabel($rincian['bimbingan']); ?></div>
                    <?php foreach (array('penguji_1' => 'Penguji 1', 'penguji_2' => 'Penguji 2') as $k => $label): ?>
                        <div class="col-md-4">
                            <h5>Nilai Sidang - <?php echo $label; ?><?php echo $rincian[$k] ? ' <small class="text-muted">(' . htmlspecialchars((string) $rincian[$k]->penguji) . ')</small>' : ''; ?></h5>
                            <?php echo $tabel($rincian[$k]); ?>
                            <?php if ($rincian[$k] && !empty($rincian[$k]->catatan_revisi)): ?>
                                <p class="small"><strong>Catatan revisi:</strong> <?php echo nl2br(htmlspecialchars($rincian[$k]->catatan_revisi)); ?></p>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                </div>

                <?php if ($rekap): ?>
                    <div class="alert alert-success mt-3 mb-0"><strong>Nilai Akhir (final):</strong> <?php echo $f($rekap->nilai_akhir); ?> (<?php echo htmlspecialchars((string) $rekap->grade); ?>)
                        &mdash; bimbingan <?php echo $f($rekap->nilai_bimbingan); ?>, sidang <?php echo $f($rekap->nilai_sidang_rata); ?></div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
