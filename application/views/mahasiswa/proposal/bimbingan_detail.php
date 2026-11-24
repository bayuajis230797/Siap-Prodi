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
                    <a href="<?php echo site_url('Bimbingan_proposal_mahasiswa/bimbingan'); ?>" class="btn btn-secondary btn-sm"><i class="fas fa-arrow-left"></i> Kembali</a>
                    <a href="<?php echo site_url('Bimbingan_proposal_mahasiswa/cetak_histori_bimbingan/' . encode_id($bimbingan->id)); ?>" class="btn btn-success btn-sm" target="_blank">
                        <i class="fas fa-file-pdf"></i> Cetak PDF
                    </a>
                </div>
            </div>
            <div class="card-body">
                <div class="row mb-4">
                    <div class="col-md-6">
                        <table class="table table-sm table-bordered">
                            <tr><th width="40%">Topik</th><td><strong><?php echo htmlspecialchars($bimbingan->judul_topik); ?></strong></td></tr>
                            <tr><th>Status Alur</th><td><span class="badge badge-<?php echo $workflow['class']; ?>"><?php echo htmlspecialchars($workflow['label']); ?></span></td></tr>
                            <tr><th>Pembimbing</th><td><?php echo htmlspecialchars($bimbingan->nama_dosen ?? '-'); ?></td></tr>
                            <tr><th>Aktivitas Terakhir</th><td><?php echo date('d/m/Y H:i', strtotime($bimbingan->last_activity)); ?></td></tr>
                        </table>
                    </div>
                    <div class="col-md-6">
                        <table class="table table-sm table-bordered">
                            <tr><th width="40%">Total Log</th><td><?php echo $statistik ? (int) $statistik->total_log : 0; ?></td></tr>
                            <tr><th>Total ACC</th><td><span class="badge badge-success"><?php echo $statistik ? (int) $statistik->total_acc : 0; ?></span></td></tr>
                            <tr><th>Total Revisi</th><td><span class="badge badge-warning"><?php echo $statistik ? (int) $statistik->total_revisi : 0; ?></span></td></tr>
                        </table>
                    </div>
                </div>

                <?php if ($bimbingan->status === 'closed'): ?>
                    <div class="alert alert-secondary"><i class="fas fa-lock"></i> Topik ini ditutup oleh pembimbing.</div>
                <?php endif; ?>

                <?php if ($can_send_revisi): ?>
                    <div class="card card-warning mb-4">
                        <div class="card-header"><h5 class="card-title mb-0"><i class="fas fa-edit"></i> Kirim Revisi Laporan</h5></div>
                        <div class="card-body">
                            <?php echo form_open_multipart('Bimbingan_proposal_mahasiswa/bimbingan_kirim/' . encode_id($bimbingan->id)); ?>
                                <div class="form-group">
                                    <label for="file_laporan">Berkas Laporan Revisi <span class="text-danger">*</span></label>
                                    <input type="file" name="file_laporan" id="file_laporan" class="form-control-file"
                                           accept=".pdf,.doc,.docx,.zip,.rar" required>
                                    <small class="form-text text-muted">Diunggah ke Google Drive institusi. PDF/DOC/DOCX/ZIP tanpa batas form.</small>
                                </div>
                                <div class="form-group">
                                    <label for="pesan">Pesan Tambahan (opsional)</label>
                                    <textarea name="pesan" id="pesan" class="form-control" rows="3"></textarea>
                                </div>
                                <button type="submit" class="btn btn-warning"><i class="fas fa-paper-plane"></i> Kirim Revisi</button>
                            <?php echo form_close(); ?>
                        </div>
                    </div>
                <?php endif; ?>

                <?php
                $sudut = 'mahasiswa';
                $nama_mahasiswa = '';
                $nama_dosen = $bimbingan->nama_dosen ?? '';
                $this->load->view('proposal_partials/timeline_bimbingan', compact('logs', 'sudut', 'nama_mahasiswa', 'nama_dosen'));
                ?>
            </div>
        </div>
    </div>
</div>
