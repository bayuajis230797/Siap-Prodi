<?php
/**
 * Kartu Proposal TA di halaman utama mahasiswa.
 * Variabel: $progress (calculate_proposal_progress), $judul, $jadwal (objek jadwal terdekat atau null), $jadwal_timestamp
 */
$this->load->helper('app');
?>
<div class="row">
    <div class="col-12">
        <div class="card card-outline card-primary mb-3">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-file-signature"></i> Proposal TA</h3>
                <div class="card-tools"><a href="<?php echo site_url('Proposal_pengajuan'); ?>" class="btn btn-primary btn-xs">Buka</a></div>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-4 text-center mb-3">
                        <?php $pc = (float) $progress['percentage']; $off = 565.48 - ($pc / 100) * 565.48; ?>
                        <div class="progress-circle-wrapper">
                            <div class="progress-circle">
                                <svg class="progress-circle-svg" viewBox="0 0 200 200">
                                    <circle class="progress-circle-bg" cx="100" cy="100" r="90"></circle>
                                    <circle class="progress-circle-progress" cx="100" cy="100" r="90" style="stroke:#007bff;stroke-dashoffset:<?php echo round($off, 2); ?>"></circle>
                                </svg>
                                <div class="progress-circle-content">
                                    <span class="progress-circle-percent"><?php echo $pc; ?>%</span>
                                    <span class="progress-circle-text"><?php echo (int) $progress['current_step']; ?>/<?php echo (int) $progress['total_steps']; ?></span>
                                </div>
                            </div>
                        </div>
                        <?php if (!empty($judul)): ?><p class="mt-3 mb-0 small"><strong>Judul:</strong> <?php echo htmlspecialchars($judul); ?></p><?php endif; ?>
                    </div>
                    <div class="col-md-4">
                        <h6 class="text-center mb-2">Tahapan Proposal TA</h6>
                        <div class="progress-steps">
                            <?php foreach ($progress['steps'] as $s): ?>
                                <div class="progress-step-item <?php echo $s['completed'] ? 'completed' : ''; ?>">
                                    <div class="step-indicator"><i class="<?php echo $s['completed'] ? 'fas fa-check-circle text-success' : 'far fa-circle text-muted'; ?>"></i></div>
                                    <div class="step-label"><?php echo htmlspecialchars($s['name']); ?></div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    <?php if (!empty($jadwal)): ?>
                        <div class="col-md-4">
                            <h6 class="text-muted"><i class="fas fa-clock"></i> Countdown Sidang Proposal TA</h6>
                            <?php $this->load->view('proposal_partials/countdown_card', array(
                                'cd_id' => 'dash-proposal',
                                'cd_timestamp' => (int) $jadwal_timestamp,
                                'cd_judul' => '',
                                'cd_info' => hari_dari_tanggal($jadwal->tanggal_ujian) . ', ' . date('d/m/Y', strtotime($jadwal->tanggal_ujian)) . ' ' . date('H:i', strtotime($jadwal->jam_mulai)) . ' - ' . ($jadwal->ruangan ?: '-')
                            )); ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>
<?php if (!empty($jadwal)) { $this->load->view('proposal_partials/countdown_script'); } ?>
