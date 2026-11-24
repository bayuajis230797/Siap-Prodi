<?php $menunggu = ($klaim->status === 'menunggu'); ?>
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Detail Klaim Nilai Proposal TA</h3>
                <div class="card-tools"><a href="<?php echo site_url('Proposal_verifikasi_klaim'); ?>" class="btn btn-secondary btn-sm"><i class="fas fa-arrow-left"></i> Kembali</a></div>
            </div>
            <div class="card-body">
                <?php if ($this->session->flashdata('success')): ?><div class="alert alert-success"><?php echo $this->session->flashdata('success'); ?></div><?php endif; ?>
                <?php if ($this->session->flashdata('error')): ?><div class="alert alert-danger"><?php echo $this->session->flashdata('error'); ?></div><?php endif; ?>

                <div class="row mb-3">
                    <div class="col-md-6">
                        <table class="table table-sm table-bordered">
                            <tr><th width="35%">NIM</th><td><strong><?php echo htmlspecialchars($klaim->nim ?? '-'); ?></strong></td></tr>
                            <tr><th>Nama</th><td><?php echo htmlspecialchars($klaim->nama_mahasiswa ?? '-'); ?></td></tr>
                            <tr><th>Kelas</th><td><?php echo htmlspecialchars($klaim->kelas ?? '-'); ?></td></tr>
                            <tr><th>Judul</th><td><?php echo htmlspecialchars($judul ?: '-'); ?></td></tr>
                            <tr><th>Status Klaim</th><td><?php echo proposal_status_badge($klaim->status); ?></td></tr>
                            <?php if (!empty($klaim->catatan_sekprodi)): ?><tr><th>Catatan</th><td><?php echo nl2br(htmlspecialchars($klaim->catatan_sekprodi)); ?></td></tr><?php endif; ?>
                        </table>
                    </div>
                    <div class="col-md-6">
                        <table class="table table-sm table-bordered">
                            <tr><th width="45%">Nilai Bimbingan (Pembimbing)</th><td><?php echo $nilai_bimbingan ? format_nilai_proposal($nilai_bimbingan->nilai_rata) : '<span class="text-danger">Belum ada</span>'; ?></td></tr>
                            <?php foreach ($nilai_sidang as $n): ?>
                                <tr><th><?php echo $n->peran === 'penguji_1' ? 'Nilai Penguji 1' : 'Nilai Penguji 2'; ?> <small class="text-muted">(<?php echo htmlspecialchars($n->penguji_nama ?? '-'); ?>)</small></th>
                                    <td><?php echo format_nilai_proposal($n->nilai_total); ?></td></tr>
                            <?php endforeach; ?>
                            <?php if (count($nilai_sidang) < 2): ?><tr><td colspan="2" class="text-danger">Nilai sidang kedua penguji belum lengkap.</td></tr><?php endif; ?>
                            <tr><th>Layak klaim penguji</th><td><?php echo (int) $pasca_status['layak']; ?> dari <?php echo (int) $pasca_status['total']; ?></td></tr>
                            <?php if ($rekap): ?>
                                <tr class="table-success"><th>Nilai Akhir</th><td><strong><?php echo format_nilai_proposal($rekap->nilai_akhir); ?></strong> (<?php echo htmlspecialchars((string) $rekap->grade); ?>)</td></tr>
                            <?php endif; ?>
                        </table>
                    </div>
                </div>

                <h5 class="mb-2"><i class="fas fa-file-alt"></i> Berkas Klaim</h5>
                <?php if (empty($berkas_uploaded)): ?><div class="alert alert-warning">Belum ada berkas.</div>
                <?php else: $this->load->view('proposal_partials/berkas_table', array('berkas_uploaded' => $berkas_uploaded)); endif; ?>

                <?php if ($menunggu): ?>
                    <div class="row">
                        <div class="col-md-7">
                            <div class="card card-success">
                                <div class="card-header"><h5 class="card-title mb-0"><i class="fas fa-check"></i> Setujui dan Tetapkan Nilai Akhir</h5></div>
                                <?php echo form_open('Proposal_verifikasi_klaim/approve/' . $klaim->id); ?>
                                <div class="card-body">
                                    <div class="alert alert-warning mb-3"><i class="fas fa-exclamation-triangle"></i>
                                        Nilai akhir dihitung dari bobot bimbingan dan sidang pada Setting Bobot Proposal TA, lalu dikonversi ke grade sesuai Setting Grade.</div>
                                    <div class="form-group mb-0"><label for="catatan_sekprodi">Catatan (opsional)</label>
                                        <textarea name="catatan_sekprodi" id="catatan_sekprodi" class="form-control" rows="2"></textarea></div>
                                </div>
                                <div class="card-footer"><button type="submit" class="btn btn-success" onclick="return confirm('Setujui klaim dan finalisasi nilai?');"><i class="fas fa-check"></i> Setujui dan Finalisasi</button></div>
                                <?php echo form_close(); ?>
                            </div>
                        </div>
                        <div class="col-md-5">
                            <div class="card card-danger">
                                <div class="card-header"><h5 class="card-title mb-0"><i class="fas fa-times"></i> Tolak Klaim</h5></div>
                                <?php echo form_open('Proposal_verifikasi_klaim/reject/' . $klaim->id); ?>
                                <div class="card-body"><div class="form-group mb-0"><label for="alasan">Alasan <span class="text-danger">*</span></label>
                                    <textarea name="catatan_sekprodi" id="alasan" class="form-control" rows="4" required></textarea></div></div>
                                <div class="card-footer"><button type="submit" class="btn btn-danger" onclick="return confirm('Tolak klaim ini?');"><i class="fas fa-times"></i> Tolak</button></div>
                                <?php echo form_close(); ?>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
