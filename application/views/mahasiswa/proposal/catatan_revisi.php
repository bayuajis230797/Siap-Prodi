<div class="row">
    <div class="col-12">
        <div class="card card-primary">
            <div class="card-header"><h3 class="card-title"><i class="fas fa-calendar-check"></i> Jadwal Sidang dan Catatan Revisi</h3></div>
            <div class="card-body">
                <?php if ($this->session->flashdata('error')): ?><div class="alert alert-danger"><?php echo $this->session->flashdata('error'); ?></div><?php endif; ?>

                <?php if (empty($jadwal_list)): ?>
                    <div class="alert alert-warning mb-0"><i class="fas fa-info-circle"></i> Jadwal sidang belum ditetapkan. Daftar lewat menu Pendaftaran Jadwal Sidang.</div>
                <?php else: ?>
                    <?php foreach ($jadwal_list as $j): ?>
                        <?php $b = $blok[$j->id]; ?>
                        <?php $this->load->view('proposal_partials/jadwal_info', array('jadwal_list' => array($j))); ?>

                        <div class="card card-outline card-warning mb-4">
                            <div class="card-header"><h5 class="card-title mb-0"><i class="fas fa-edit"></i> Catatan Revisi Penguji</h5></div>
                            <div class="card-body">
                                <?php if (empty($b['nilai_list'])): ?>
                                    <p class="text-muted mb-0">Belum ada penilaian dari penguji.</p>
                                <?php else: ?>
                                    <?php foreach ($b['nilai_list'] as $n): ?>
                                        <div class="border rounded p-2 mb-2">
                                            <strong><?php echo htmlspecialchars($n->penguji_nama ?? '-'); ?></strong>
                                            <span class="badge badge-info ml-1"><?php echo $n->peran === 'penguji_1' ? 'Penguji 1' : 'Penguji 2'; ?></span>
                                            <?php if ($sudah_klaim): ?>
                                                <span class="badge badge-success ml-1">Nilai: <?php echo number_format((float) $n->nilai_total, 2, ',', '.'); ?></span>
                                            <?php endif; ?>
                                            <p class="mb-0 mt-1"><?php echo !empty($n->catatan_revisi) ? nl2br(htmlspecialchars($n->catatan_revisi)) : '<span class="text-muted">Tidak ada catatan revisi.</span>'; ?></p>
                                        </div>
                                    <?php endforeach; ?>
                                    <p class="small text-muted mb-2"><i class="fas fa-lock"></i> Nilai ditampilkan setelah klaim nilai Anda disetujui sekprodi.</p>
                                    <a href="<?php echo site_url('Bimbingan_pasca_proposal_mahasiswa'); ?>" class="btn btn-outline-primary btn-sm"><i class="fas fa-comments"></i> Bimbingan Pasca Sidang</a>
                                <?php endif; ?>
                            </div>
                        </div>

                        <?php if ($sudah_klaim && $b['rekap']): ?>
                            <div class="alert alert-success">
                                <strong>Nilai Akhir:</strong> <?php echo number_format((float) $b['rekap']->nilai_akhir, 2, ',', '.'); ?>
                                (Grade <?php echo htmlspecialchars((string) $b['rekap']->grade); ?>)
                            </div>
                        <?php endif; ?>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
