<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-clock"></i> Countdown Sidang Skripsi
                    <small class="text-muted ml-2">Tampilan realtime</small>
                </h3>
                <div class="card-tools">
                    <a href="<?php echo site_url('Jadwal_skripsi_sekprodi'); ?>" class="btn btn-secondary btn-sm">
                        <i class="fas fa-calendar-alt"></i> Jadwal Sidang
                    </a>
                </div>
            </div>
            <div class="card-body">
                <!-- Filter Tanggal -->
                <form method="get" action="<?php echo site_url('Countdown_sidang_skripsi'); ?>" class="mb-4">
                    <div class="row">
                        <div class="col-md-3">
                            <label>Tanggal Mulai</label>
                            <input type="date" name="tanggal_mulai" class="form-control" value="<?php echo htmlspecialchars($filter_tanggal_mulai ?? ''); ?>">
                        </div>
                        <div class="col-md-3">
                            <label>Tanggal Selesai</label>
                            <input type="date" name="tanggal_selesai" class="form-control" value="<?php echo htmlspecialchars($filter_tanggal_selesai ?? ''); ?>">
                        </div>
                        <div class="col-md-3 d-flex align-items-end">
                            <button type="submit" class="btn btn-info mr-2">
                                <i class="fas fa-filter"></i> Filter
                            </button>
                            <a href="<?php echo site_url('Countdown_sidang_skripsi'); ?>" class="btn btn-secondary">
                                <i class="fas fa-times"></i> Reset
                            </a>
                        </div>
                    </div>
                </form>

                <?php if (empty($jadwal_list)): ?>
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle"></i> Belum ada jadwal sidang skripsi dengan tanggal dan jam yang terisi.
                    </div>
                <?php else: ?>
                    <div class="row" id="countdown-grid">
                        <?php foreach ($jadwal_list as $idx => $j): ?>
                            <?php
                            $tgl = isset($j->tanggal_ujian) ? $j->tanggal_ujian : (isset($j->tanggal) ? $j->tanggal : '');
                            $jam = isset($j->jam_mulai) ? $j->jam_mulai : '';
                            $ruang = isset($j->ruangan) ? $j->ruangan : '';
                            $ts = $j->jadwal_timestamp;
                            $id_prefix = 'countdown-' . $j->id . '-';
                            ?>
                            <div class="col-lg-4 col-md-6 mb-4">
                                <div class="card card-outline card-success h-100">
                                    <div class="card-header py-2">
                                        <h5 class="card-title mb-0">
                                            <i class="fas fa-user-graduate"></i>
                                            <?php echo htmlspecialchars($j->nama_mahasiswa ?? '-'); ?>
                                        </h5>
                                    </div>
                                    <div class="card-body py-3">
                                        <p class="mb-2 small">
                                            <i class="fas fa-calendar"></i>
                                            <?php echo $tgl ? date('d/m/Y', strtotime($tgl)) : '-'; ?>
                                            <?php echo $jam ? date('H:i', strtotime($jam)) : ''; ?>
                                            <?php echo $ruang ? ' - ' . htmlspecialchars($ruang) : ''; ?>
                                        </p>
                                        <?php if ($j->judul_skripsi): ?>
                                            <p class="mb-2 small text-muted" title="<?php echo htmlspecialchars($j->judul_skripsi); ?>">
                                                <i class="fas fa-book"></i> <?php echo strlen($j->judul_skripsi) > 50 ? substr($j->judul_skripsi, 0, 50) . '...' : $j->judul_skripsi; ?>
                                            </p>
                                        <?php endif; ?>
                                        <div class="row justify-content-center mt-2 countdown-row" data-timestamp="<?php echo $ts; ?>" data-id="<?php echo $j->id; ?>">
                                            <div class="col-3 px-1">
                                                <div class="countdown-box-hari p-1 text-center">
                                                    <div class="countdown-value small" id="<?php echo $id_prefix; ?>hari">--</div>
                                                    <div class="countdown-label" style="font-size:0.65rem;">Hari</div>
                                                </div>
                                            </div>
                                            <div class="col-3 px-1">
                                                <div class="countdown-box-jam p-1 text-center">
                                                    <div class="countdown-value small" id="<?php echo $id_prefix; ?>jam">--</div>
                                                    <div class="countdown-label" style="font-size:0.65rem;">Jam</div>
                                                </div>
                                            </div>
                                            <div class="col-3 px-1">
                                                <div class="countdown-box-menit p-1 text-center">
                                                    <div class="countdown-value small" id="<?php echo $id_prefix; ?>menit">--</div>
                                                    <div class="countdown-label" style="font-size:0.65rem;">Menit</div>
                                                </div>
                                            </div>
                                            <div class="col-3 px-1">
                                                <div class="countdown-box-detik p-1 text-center">
                                                    <div class="countdown-value small" id="<?php echo $id_prefix; ?>detik">--</div>
                                                    <div class="countdown-label" style="font-size:0.65rem;">Detik</div>
                                                </div>
                                            </div>
                                        </div>
                                        <p class="text-muted mb-0 mt-2 small text-center countdown-status" id="<?php echo $id_prefix; ?>status"></p>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<style>
.countdown-box-hari { background: linear-gradient(135deg, #007bff 0%, #0056b3 100%); color: #fff; border-radius: 6px; }
.countdown-box-jam { background: linear-gradient(135deg, #17a2b8 0%, #117a8b 100%); color: #fff; border-radius: 6px; }
.countdown-box-menit { background: linear-gradient(135deg, #ffc107 0%, #e0a800 100%); color: #212529; border-radius: 6px; }
.countdown-box-detik { background: linear-gradient(135deg, #dc3545 0%, #c82333 100%); color: #fff; border-radius: 6px; }
.countdown-box-hari .countdown-label, .countdown-box-jam .countdown-label, .countdown-box-detik .countdown-label { color: rgba(255,255,255,0.9) !important; }
.countdown-box-menit .countdown-label { color: rgba(33,37,41,0.8) !important; }
.countdown-value { font-weight: bold; font-size: 1rem; }
.countdown-box-menit .countdown-value { color: #212529; }
</style>

<script>
(function() {
    function updateCountdown(el) {
        var ts = parseInt(el.getAttribute('data-timestamp'), 10) * 1000;
        var id = el.getAttribute('data-id');
        var prefix = 'countdown-' + id + '-';
        var now = Date.now();
        var diff = ts - now;

        var hariEl = document.getElementById(prefix + 'hari');
        var jamEl = document.getElementById(prefix + 'jam');
        var menitEl = document.getElementById(prefix + 'menit');
        var detikEl = document.getElementById(prefix + 'detik');
        var statusEl = document.getElementById(prefix + 'status');

        if (!hariEl || !jamEl || !menitEl || !detikEl) return;

        if (diff <= 0) {
            hariEl.textContent = '0';
            jamEl.textContent = '0';
            menitEl.textContent = '0';
            detikEl.textContent = '0';
            if (statusEl) statusEl.textContent = 'Sidang telah dimulai atau selesai.';
            return;
        }

        var days = Math.floor(diff / (1000 * 60 * 60 * 24));
        var hours = Math.floor((diff % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
        var minutes = Math.floor((diff % (1000 * 60 * 60)) / (1000 * 60));
        var seconds = Math.floor((diff % (1000 * 60)) / 1000);

        hariEl.textContent = days;
        jamEl.textContent = String(hours).padStart(2, '0');
        menitEl.textContent = String(minutes).padStart(2, '0');
        detikEl.textContent = String(seconds).padStart(2, '0');
        if (statusEl) statusEl.textContent = '';
    }

    function runAll() {
        var rows = document.querySelectorAll('.countdown-row');
        rows.forEach(function(el) {
            updateCountdown(el);
        });
    }

    runAll();
    setInterval(runAll, 1000);
})();
</script>
