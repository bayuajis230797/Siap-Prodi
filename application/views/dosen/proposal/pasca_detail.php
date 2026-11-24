<div class="row">
    <div class="col-md-4">
        <div class="card card-outline card-info">
            <div class="card-header"><h3 class="card-title">Mahasiswa</h3></div>
            <div class="card-body">
                <p class="mb-1"><strong><?php echo htmlspecialchars($bimbingan->nama_mahasiswa ?? '-'); ?></strong></p>
                <p class="mb-1"><?php echo htmlspecialchars($bimbingan->nim ?? '-'); ?> &middot; <?php echo htmlspecialchars($bimbingan->kelas ?: '-'); ?></p>
                <p class="mb-0 small text-muted"><?php echo htmlspecialchars($bimbingan->judul_proposal ?? $bimbingan->judul_topik); ?></p>
                <hr>
                <p class="mb-1"><strong>Peran Anda:</strong> <span class="badge badge-info"><?php echo $penguji_me->peran === 'penguji_1' ? 'Penguji 1' : 'Penguji 2'; ?></span></p>
                <p class="mb-2"><strong>Layak klaim Anda:</strong> <?php echo !empty($penguji_me->layak_klaim) ? '<span class="badge badge-success">Ya</span>' : '<span class="badge badge-warning">Belum</span>'; ?></p>
                <h6>Status Penguji</h6>
                <ul class="list-unstyled mb-2">
                    <?php foreach ($penguji_list as $p): ?>
                        <li class="mb-1"><small><?php echo htmlspecialchars($p->nama_dosen); ?> (<?php echo $p->peran === 'penguji_1' ? 'Penguji 1' : 'Penguji 2'; ?>)</small>
                            <?php echo !empty($p->layak_klaim) ? '<span class="badge badge-success">Layak</span>' : '<span class="badge badge-secondary">Belum</span>'; ?></li>
                    <?php endforeach; ?>
                </ul>
                <?php if ($bimbingan->status !== 'open'): ?>
                    <div class="alert alert-secondary mb-0 py-2 small"><i class="fas fa-lock"></i> Bimbingan pasca sidang ditutup.</div>
                <?php elseif (empty($penguji_me->layak_klaim)): ?>
                    <?php if (!$sudah_menilai): ?>
                        <div class="alert alert-warning py-2 small mb-0">Isi penilaian sidang Anda dahulu sebelum menyatakan layak klaim.</div>
                    <?php else: ?>
                        <?php echo form_open('Bimbingan_pasca_proposal/layak_klaim/' . $bimbingan->id); ?>
                            <button type="submit" class="btn btn-success btn-block" onclick="return confirm('Nyatakan mahasiswa layak klaim nilai dari sisi Anda?')"><i class="fas fa-check"></i> Layak Klaim Nilai</button>
                        <?php echo form_close(); ?>
                    <?php endif; ?>
                <?php elseif (!empty($all_layak)): ?>
                    <div class="alert alert-success mb-0 py-2 small">Kedua penguji sudah layak klaim.</div>
                <?php endif; ?>
            </div>
        </div>
        <a href="<?php echo site_url('Bimbingan_pasca_proposal'); ?>" class="btn btn-secondary btn-sm"><i class="fas fa-arrow-left"></i> Kembali</a>
    </div>
    <div class="col-md-8">
        <?php if ($this->session->flashdata('success')): ?><div class="alert alert-success"><?php echo $this->session->flashdata('success'); ?></div><?php endif; ?>
        <?php if ($this->session->flashdata('error')): ?><div class="alert alert-danger"><?php echo $this->session->flashdata('error'); ?></div><?php endif; ?>

        <?php $this->load->view('proposal_partials/timeline_pasca', array('logs' => $logs)); ?>

        <?php if ($bimbingan->status === 'open'): ?>
            <div class="card card-outline card-primary">
                <div class="card-header"><h3 class="card-title">Balas / ACC / Revisi</h3></div>
                <?php echo form_open_multipart('Bimbingan_pasca_proposal/kirim_pesan/' . $bimbingan->id); ?>
                <div class="card-body">
                    <div class="form-group">
                        <label>Status</label>
                        <select name="status_revisi" class="form-control" required>
                            <option value="revisi">Revisi</option>
                            <option value="acc">ACC</option>
                        </select>
                    </div>
                    <div class="form-group"><label>Keterangan Revisi</label>
                        <textarea name="keterangan_revisi" class="form-control" rows="3" placeholder="Wajib diisi jika status Revisi"></textarea></div>
                    <div class="form-group"><label>Pesan (opsional)</label><textarea name="pesan" class="form-control" rows="2"></textarea></div>
                    <div class="form-group mb-0"><label>Berkas (opsional)</label>
                        <input type="file" name="file_google_drive" class="form-control-file" accept=".pdf,.doc,.docx,.zip,.rar,.jpg,.jpeg,.png">
                        <small class="form-text text-muted">Diunggah ke Google Drive institusi.</small></div>
                </div>
                <div class="card-footer text-right"><button type="submit" class="btn btn-primary"><i class="fas fa-paper-plane"></i> Kirim</button></div>
                <?php echo form_close(); ?>
            </div>
        <?php endif; ?>
    </div>
</div>
