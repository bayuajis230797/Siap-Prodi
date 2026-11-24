<div class="row">
    <div class="col-12">
        <?php if ($this->session->flashdata('success')): ?>
            <div class="alert alert-success alert-dismissible fade show">
                <i class="fas fa-check-circle"></i> <?php echo $this->session->flashdata('success'); ?>
                <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
            </div>
        <?php endif; ?>
        <?php if ($this->session->flashdata('error')): ?>
            <div class="alert alert-danger alert-dismissible fade show">
                <i class="fas fa-exclamation-circle"></i> <?php echo $this->session->flashdata('error'); ?>
                <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
            </div>
        <?php endif; ?>

        <div class="card card-primary">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-comments"></i> <?php echo htmlspecialchars($bimbingan->judul_topik); ?></h3>
                <div class="card-tools">
                    <a href="<?php echo site_url('Bimbingan_proposal'); ?>" class="btn btn-secondary btn-sm"><i class="fas fa-arrow-left"></i> Kembali</a>
                </div>
            </div>
            <div class="card-body">
                <div class="row mb-4">
                    <div class="col-md-6">
                        <table class="table table-sm table-bordered">
                            <tr><th width="40%">Mahasiswa</th><td><strong><?php echo htmlspecialchars($bimbingan->nama_mahasiswa ?? '-'); ?></strong> (<?php echo htmlspecialchars($bimbingan->nim ?? '-'); ?>)</td></tr>
                            <tr><th>Kelas</th><td><?php echo htmlspecialchars($bimbingan->kelas ?? '-'); ?></td></tr>
                            <tr><th>Topik</th><td><?php echo htmlspecialchars($bimbingan->judul_topik); ?></td></tr>
                            <tr><th>Status Alur</th><td><span class="badge badge-<?php echo $workflow['class']; ?>"><?php echo htmlspecialchars($workflow['label']); ?></span></td></tr>
                            <tr><th>Aktivitas Terakhir</th><td><?php echo date('d/m/Y H:i', strtotime($bimbingan->last_activity)); ?></td></tr>
                        </table>
                    </div>
                    <div class="col-md-6">
                        <div class="card card-outline card-success mb-0">
                            <div class="card-header"><h5 class="card-title mb-0"><i class="fas fa-graduation-cap"></i> Nilai dan Layak Sidang</h5></div>
                            <div class="card-body">
                                <?php if ($proposal && $proposal->status === 'layak_sidang'): ?>
                                    <p class="mb-1"><span class="badge badge-success">Layak Sidang</span></p>
                                    <p class="mb-2">Nilai bimbingan: <strong><?php echo $nilai_bimbingan ? format_nilai_proposal($nilai_bimbingan->nilai_rata) : '-'; ?></strong></p>
                                <?php else: ?>
                                    <p class="mb-2 text-muted">Belum dinyatakan layak sidang.</p>
                                <?php endif; ?>
                                <?php if ($proposal): ?>
                                    <a href="<?php echo site_url('Proposal_mahasiswa_dosen/siap_sidang/' . $proposal->id); ?>" class="btn btn-success btn-sm">
                                        <i class="fas fa-clipboard-check"></i> <?php echo $nilai_bimbingan ? 'Ubah Nilai / Layak Sidang' : 'Input Nilai / Layak Sidang'; ?>
                                    </a>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>

                <?php if ($bimbingan->status === 'open'): ?>
                    <?php if ($workflow['key'] === 'waiting_review'): ?>
                        <div class="card card-primary mb-4">
                            <div class="card-header"><h5 class="card-title mb-0"><i class="fas fa-clipboard-check"></i> Review Bimbingan</h5></div>
                            <div class="card-body">
                                <?php echo form_open_multipart('Bimbingan_proposal/kirim_pesan/' . $bimbingan->id); ?>
                                    <div class="form-group">
                                        <label for="status_revisi">Status Review <span class="text-danger">*</span></label>
                                        <select name="status_revisi" id="status_revisi" class="form-control" required>
                                            <option value="">-- Pilih Status --</option>
                                            <option value="acc">ACC (Disetujui)</option>
                                            <option value="revisi">REVISI (Perlu Perbaikan)</option>
                                        </select>
                                    </div>
                                    <div class="form-group" id="keterangan_group" style="display:none;">
                                        <label for="keterangan_revisi">Keterangan Revisi <span class="text-danger">*</span></label>
                                        <textarea name="keterangan_revisi" id="keterangan_revisi" class="form-control" rows="5" placeholder="Bagian yang perlu direvisi..."></textarea>
                                    </div>
                                    <div class="form-group">
                                        <label for="file_google_drive">Berkas (opsional)</label>
                                        <input type="file" name="file_google_drive" id="file_google_drive" class="form-control-file" accept=".pdf,.doc,.docx,.zip,.rar,.jpg,.jpeg,.png">
                                        <small class="form-text text-muted">Diunggah ke Google Drive institusi.</small>
                                    </div>
                                    <div class="form-group">
                                        <label for="pesan">Pesan Tambahan (opsional)</label>
                                        <textarea name="pesan" id="pesan" class="form-control" rows="3"></textarea>
                                    </div>
                                    <button type="submit" class="btn btn-primary"><i class="fas fa-paper-plane"></i> Kirim Review</button>
                                <?php echo form_close(); ?>
                            </div>
                        </div>
                    <?php elseif ($workflow['key'] === 'need_revision'): ?>
                        <div class="alert alert-warning"><i class="fas fa-hourglass-half"></i> Menunggu mahasiswa mengirim revisi.</div>
                    <?php else: ?>
                        <div class="alert alert-success"><i class="fas fa-check-circle"></i> Topik ini sudah Anda ACC.</div>
                    <?php endif; ?>
                <?php else: ?>
                    <div class="alert alert-secondary"><i class="fas fa-lock"></i> Topik ini ditutup.</div>
                <?php endif; ?>

                <?php
                $sudut = 'dosen';
                $nama_mahasiswa = $bimbingan->nama_mahasiswa ?? '';
                $nama_dosen = '';
                $this->load->view('proposal_partials/timeline_bimbingan', compact('logs', 'sudut', 'nama_mahasiswa', 'nama_dosen'));
                ?>

                <div class="mt-4">
                    <?php if ($bimbingan->status === 'open'): ?>
                        <?php echo form_open('Bimbingan_proposal/tutup/' . $bimbingan->id, array('class' => 'd-inline', 'onsubmit' => "return confirm('Tutup bimbingan ini?');")); ?>
                            <button type="submit" class="btn btn-secondary"><i class="fas fa-lock"></i> Tutup Bimbingan</button>
                        <?php echo form_close(); ?>
                    <?php else: ?>
                        <?php echo form_open('Bimbingan_proposal/buka/' . $bimbingan->id, array('class' => 'd-inline', 'onsubmit' => "return confirm('Buka kembali bimbingan ini?');")); ?>
                            <button type="submit" class="btn btn-primary"><i class="fas fa-unlock"></i> Buka Kembali Bimbingan</button>
                        <?php echo form_close(); ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    var sel = document.getElementById('status_revisi');
    var grp = document.getElementById('keterangan_group');
    var ket = document.getElementById('keterangan_revisi');
    if (sel && grp) {
        sel.addEventListener('change', function () {
            var rev = this.value === 'revisi';
            grp.style.display = rev ? 'block' : 'none';
            if (rev) { ket.setAttribute('required', 'required'); } else { ket.removeAttribute('required'); }
        });
    }
});
</script>
