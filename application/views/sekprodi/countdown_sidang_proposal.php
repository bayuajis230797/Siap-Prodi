<?php $this->load->helper('app'); ?>
<div class="row">
    <div class="col-12">
        <div class="card card-primary">
            <div class="card-header"><h3 class="card-title"><i class="fas fa-hourglass-half"></i> Countdown Sidang Proposal TA</h3></div>
            <div class="card-body">
                <?php if ($tahun_aktif): ?>
                    <div class="alert alert-info"><i class="fas fa-info-circle"></i> <strong>Tahun Akademik:</strong> <?php echo htmlspecialchars($tahun_aktif->tahun_akademik . ' ' . ucfirst($tahun_aktif->semester)); ?></div>
                <?php endif; ?>

                <form method="get" action="<?php echo site_url('Countdown_sidang_proposal'); ?>" class="form-inline mb-3">
                    <label class="mr-2" for="tanggal_mulai">Dari</label>
                    <input type="date" id="tanggal_mulai" name="tanggal_mulai" class="form-control form-control-sm mr-3" value="<?php echo htmlspecialchars((string) $filter_tanggal_mulai); ?>">
                    <label class="mr-2" for="tanggal_selesai">Sampai</label>
                    <input type="date" id="tanggal_selesai" name="tanggal_selesai" class="form-control form-control-sm mr-3" value="<?php echo htmlspecialchars((string) $filter_tanggal_selesai); ?>">
                    <div class="form-check mr-3"><input type="checkbox" class="form-check-input" id="selesai" name="selesai" value="1" <?php echo $tampil_selesai ? 'checked' : ''; ?>>
                        <label class="form-check-label" for="selesai">Tampilkan yang sudah selesai</label></div>
                    <button type="submit" class="btn btn-primary btn-sm mr-1"><i class="fas fa-filter"></i> Filter</button>
                    <a href="<?php echo site_url('Countdown_sidang_proposal'); ?>" class="btn btn-secondary btn-sm"><i class="fas fa-times"></i> Reset</a>
                </form>

                <?php if (empty($jadwal_list)): ?>
                    <div class="alert alert-info"><i class="fas fa-info-circle"></i> Tidak ada jadwal sidang Proposal TA yang akan datang.</div>
                <?php else: ?>
                    <div class="row">
                        <?php foreach ($jadwal_list as $j): ?>
                            <div class="col-lg-4 col-md-6 mb-4">
                                <div class="card card-outline card-success h-100">
                                    <div class="card-header py-2"><h5 class="card-title mb-0"><i class="fas fa-user-graduate"></i> <?php echo htmlspecialchars($j->nama_mahasiswa ?? '-'); ?></h5></div>
                                    <div class="card-body py-3">
                                        <?php $this->load->view('proposal_partials/countdown_card', array(
                                            'cd_id' => 'cd-' . $j->id,
                                            'cd_timestamp' => $j->jadwal_timestamp,
                                            'cd_judul' => (string) ($j->judul_proposal ?? ''),
                                            'cd_info' => hari_dari_tanggal($j->tanggal_ujian) . ', ' . date('d/m/Y', strtotime($j->tanggal_ujian)) . ' ' . date('H:i', strtotime($j->jam_mulai)) . ' - ' . ($j->ruangan ?: '-')
                                        )); ?>
                                        <p class="mb-0 mt-2 small">Penguji: <?php echo htmlspecialchars(($j->penguji_1 ?: '-') . ' / ' . ($j->penguji_2 ?: '-')); ?></p>
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
<?php $this->load->view('proposal_partials/countdown_script'); ?>
