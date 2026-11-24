<?php
$this->load->helper('google_drive');
$boleh_kirim = ($syarat_pesan === null) && (!$klaim || $klaim->status === 'ditolak');
$disetujui = ($klaim && $klaim->status === 'disetujui');
?>
<div class="row">
    <div class="col-12">
        <div class="card card-primary">
            <div class="card-header"><h3 class="card-title"><i class="fas fa-award"></i> Klaim Nilai Proposal TA</h3></div>
            <div class="card-body">
                <?php if ($this->session->flashdata('success')): ?>
                    <div class="alert alert-success alert-dismissible fade show"><i class="fas fa-check-circle"></i> <?php echo $this->session->flashdata('success'); ?>
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button></div>
                <?php endif; ?>
                <?php if ($this->session->flashdata('error')): ?>
                    <div class="alert alert-danger alert-dismissible fade show"><i class="fas fa-exclamation-circle"></i> <?php echo $this->session->flashdata('error'); ?>
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button></div>
                <?php endif; ?>

                <?php if ($disetujui && $nilai_akhir !== null): ?>
                    <div class="alert alert-success">
                        <h5 class="mb-1"><i class="fas fa-check-circle"></i> Nilai Akhir Proposal TA</h5>
                        <p class="mb-0" style="font-size:1.4rem"><strong><?php echo format_nilai_proposal($nilai_akhir); ?></strong>
                            &nbsp;(Grade <strong><?php echo htmlspecialchars((string) $grade); ?></strong>)</p>
                    </div>
                <?php endif; ?>

                <?php if (!$nilai_tampil): ?>
                    <div class="alert alert-info"><i class="fas fa-lock"></i> Nilai (bimbingan, sidang, dan akhir) akan ditampilkan setelah klaim nilai Anda disetujui sekprodi.</div>
                <?php else: ?>
                <div class="card card-outline card-info mb-4">
                    <div class="card-header"><h5 class="card-title mb-0"><i class="fas fa-chart-line"></i> Informasi Nilai</h5></div>
                    <div class="card-body">
                        <div class="row text-center">
                            <div class="col-md-3"><p class="mb-0 text-muted">Nilai Bimbingan (<?php echo (float) $bobot_bimbingan; ?>%)</p>
                                <h4><?php echo $nilai_bimbingan !== null ? format_nilai_proposal($nilai_bimbingan) : '-'; ?></h4></div>
                            <div class="col-md-3"><p class="mb-0 text-muted">Nilai Sidang (<?php echo (float) $bobot_sidang; ?>%)</p>
                                <h4><?php echo $nilai_sidang !== null ? format_nilai_proposal($nilai_sidang) : '-'; ?></h4></div>
                            <div class="col-md-3"><p class="mb-0 text-muted">Nilai Akhir<?php echo $disetujui ? '' : ' (sementara)'; ?></p>
                                <h4><?php echo $nilai_akhir !== null ? format_nilai_proposal($nilai_akhir) : '-'; ?></h4></div>
                            <div class="col-md-3"><p class="mb-0 text-muted">Grade</p>
                                <h4><?php echo $grade ? htmlspecialchars($grade) : '-'; ?></h4></div>
                        </div>
                        <small class="text-muted">Nilai sidang = rata-rata nilai Penguji 1 dan Penguji 2. Nilai akhir resmi ditetapkan sekprodi setelah klaim diverifikasi.</small>
                    </div>
                </div>
                <?php endif; ?>

                <?php if ($syarat_pesan !== null && !$klaim): ?>
                    <div class="alert alert-warning"><i class="fas fa-lock"></i> <?php echo htmlspecialchars($syarat_pesan); ?>
                        <?php if ($jadwal->status === 'selesai'): ?><a href="<?php echo site_url('Bimbingan_pasca_proposal_mahasiswa'); ?>" class="alert-link">Buka bimbingan pasca sidang</a>.<?php endif; ?></div>
                <?php endif; ?>

                <?php if ($klaim): ?>
                    <div class="alert alert-secondary">
                        <h5><i class="fas fa-info-circle"></i> Status Klaim</h5>
                        <p class="mb-0"><?php echo proposal_status_badge($klaim->status); ?></p>
                        <?php if (!empty($klaim->catatan_sekprodi)): ?><p class="mb-0 mt-2"><strong>Catatan Sekprodi:</strong> <?php echo nl2br(htmlspecialchars($klaim->catatan_sekprodi)); ?></p><?php endif; ?>
                        <?php if ($klaim->status === 'ditolak'): ?><p class="mb-0 mt-2 text-danger">Klaim ditolak. Perbaiki berkas lalu kirim kembali di bawah.</p><?php endif; ?>
                    </div>
                    <?php $this->load->view('proposal_partials/berkas_table', array('berkas_uploaded' => $berkas_uploaded)); ?>
                <?php endif; ?>

                <?php if ($boleh_kirim): ?>
                    <?php echo form_open_multipart('Proposal_klaim_nilai/submit'); ?>
                        <h5 class="mb-3"><i class="fas fa-upload"></i> Unggah Berkas Klaim</h5>
                        <?php if (empty($berkas_list)): ?>
                            <div class="alert alert-warning">Belum ada berkas klaim yang ditentukan. Hubungi Sekprodi atau Admin.</div>
                        <?php else: ?>
                            <?php foreach ($berkas_list as $berkas): ?>
                                <?php $sudah = null; foreach ($berkas_uploaded as $bu) { if ((int) $bu->id_berkas_klaim_nilai === (int) $berkas->id) { $sudah = $bu; break; } } ?>
                                <div class="form-group">
                                    <label for="file_berkas_<?php echo $berkas->id; ?>"><?php echo htmlspecialchars($berkas->nama_berkas); ?>
                                        <?php if (!empty($berkas->wajib)): ?><span class="text-danger">*</span><?php endif; ?></label>
                                    <?php if (!empty($berkas->deskripsi)): ?><small class="form-text text-muted mb-1"><?php echo htmlspecialchars($berkas->deskripsi); ?></small><?php endif; ?>
                                    <?php if ($sudah): ?>
                                        <?php $this->load->helper('google_drive'); ?>
                                        <div class="alert alert-info py-1 mb-2 small"><?php echo google_drive_link_html($sudah->file_path, 'Berkas saat ini'); ?></div>
                                    <?php endif; ?>
                                    <input type="file" name="file_berkas_<?php echo $berkas->id; ?>" id="file_berkas_<?php echo $berkas->id; ?>" class="form-control-file"
                                           accept=".pdf,.doc,.docx,.zip,.rar,.jpg,.jpeg,.png"
                                           <?php echo (!empty($berkas->wajib) && !$sudah) ? 'required' : ''; ?>>
                                </div>
                            <?php endforeach; ?>
                            <button type="submit" class="btn btn-primary"><i class="fas fa-paper-plane"></i> <?php echo $klaim ? 'Kirim Ulang Klaim' : 'Kirim Klaim Nilai'; ?></button>
                        <?php endif; ?>
                    <?php echo form_close(); ?>
                <?php endif; ?>

                <?php if (!empty($grade_list)): ?>
                    <details class="mt-4"><summary class="text-muted">Tabel grade</summary>
                        <table class="table table-sm table-bordered mt-2" style="max-width:420px">
                            <thead><tr><th>Grade</th><th>Rentang Nilai</th></tr></thead>
                            <tbody><?php foreach ($grade_list as $g): ?>
                                <tr><td><?php echo htmlspecialchars($g->grade); ?></td><td><?php echo (float) $g->nilai_min; ?> - <?php echo $g->nilai_max !== null ? (float) $g->nilai_max : '100'; ?></td></tr>
                            <?php endforeach; ?></tbody>
                        </table>
                    </details>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
