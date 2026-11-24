<div class="row">
    <div class="col-md-4">
        <div class="card card-outline card-info">
            <div class="card-header"><h3 class="card-title">Status Penguji</h3></div>
            <div class="card-body">
                <p class="small text-muted"><?php echo htmlspecialchars($bimbingan->judul_topik); ?></p>
                <ul class="list-unstyled mb-2">
                    <?php foreach ($penguji_list as $p): ?>
                        <li class="mb-1"><?php echo htmlspecialchars($p->nama_dosen); ?> <small>(<?php echo $p->peran === 'penguji_1' ? 'Penguji 1' : 'Penguji 2'; ?>)</small>
                            <?php echo !empty($p->layak_klaim) ? '<span class="badge badge-success">Layak klaim</span>' : '<span class="badge badge-secondary">Belum</span>'; ?></li>
                    <?php endforeach; ?>
                </ul>
                <?php if ($all_layak): ?>
                    <div class="alert alert-success mb-0 py-2 small">Kedua penguji sudah menyatakan layak. <a href="<?php echo site_url('Proposal_klaim_nilai'); ?>" class="alert-link">Ajukan klaim nilai</a>.</div>
                <?php endif; ?>
                <?php if ($bimbingan->status !== 'open'): ?>
                    <div class="alert alert-secondary mt-2 mb-0 py-2 small"><i class="fas fa-lock"></i> Bimbingan ditutup.</div>
                <?php endif; ?>
            </div>
        </div>
        <a href="<?php echo site_url('Bimbingan_pasca_proposal_mahasiswa'); ?>" class="btn btn-secondary btn-sm"><i class="fas fa-arrow-left"></i> Kembali</a>
    </div>
    <div class="col-md-8">
        <?php if ($this->session->flashdata('success')): ?><div class="alert alert-success"><?php echo $this->session->flashdata('success'); ?></div><?php endif; ?>
        <?php if ($this->session->flashdata('error')): ?><div class="alert alert-danger"><?php echo $this->session->flashdata('error'); ?></div><?php endif; ?>

        <?php $this->load->view('proposal_partials/timeline_pasca', array('logs' => $logs)); ?>

        <?php if ($bimbingan->status === 'open'): ?>
            <div class="card card-outline card-primary">
                <div class="card-header"><h3 class="card-title">Kirim Pesan / Revisi</h3></div>
                <?php echo form_open_multipart('Bimbingan_pasca_proposal_mahasiswa/kirim/' . $encoded_id); ?>
                <div class="card-body">
                    <div class="form-group"><label>Pesan</label><textarea name="pesan" class="form-control" rows="3"></textarea></div>
                    <div class="form-group mb-0"><label>Berkas revisi (opsional)</label>
                        <input type="file" name="file_google_drive" class="form-control-file" accept=".pdf,.doc,.docx,.zip,.rar,.jpg,.jpeg,.png">
                        <small class="form-text text-muted">Diunggah ke Google Drive institusi.</small></div>
                </div>
                <div class="card-footer text-right"><button type="submit" class="btn btn-primary"><i class="fas fa-paper-plane"></i> Kirim</button></div>
                <?php echo form_close(); ?>
            </div>
        <?php endif; ?>
    </div>
</div>
