<div class="row">
    <div class="col-12">
        <?php if ($this->session->flashdata('success')): ?>
            <div class="alert alert-success alert-dismissible fade show">
                <i class="fas fa-check-circle"></i> <?php echo $this->session->flashdata('success'); ?>
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
        <?php endif; ?>

        <?php if ($this->session->flashdata('error')): ?>
            <div class="alert alert-danger alert-dismissible fade show">
                <i class="fas fa-exclamation-circle"></i> <?php echo $this->session->flashdata('error'); ?>
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
        <?php endif; ?>

        <div class="card card-primary">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-clipboard-check"></i> Detail Ajuan Judul Skripsi
                </h3>
                <div class="card-tools">
                    <a href="<?php echo site_url('Ajuan_judul_skripsi'); ?>" class="btn btn-secondary btn-sm">
                        <i class="fas fa-arrow-left"></i> Kembali
                    </a>
                </div>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <h5>Data Mahasiswa</h5>
                        <table class="table table-bordered">
                            <tr>
                                <th width="40%">NIM</th>
                                <td><?php echo htmlspecialchars($pengajuan->nim ?? ''); ?></td>
                            </tr>
                            <tr>
                                <th>Nama Mahasiswa</th>
                                <td><?php echo htmlspecialchars($pengajuan->nama_mahasiswa ?? ''); ?></td>
                            </tr>
                            <tr>
                                <th>Kelas</th>
                                <td><?php echo htmlspecialchars($pengajuan->kelas ?? ''); ?></td>
                            </tr>
                        </table>
                    </div>
                    <div class="col-md-6">
                        <h5>Status Review</h5>
                        <table class="table table-bordered">
                            <tr>
                                <th width="40%">Status ACC</th>
                                <td>
                                    <?php
                                    $status_acc = isset($status_acc) ? $status_acc : 'pending';
                                    $status_class = 'secondary';
                                    $status_text = 'Pending';
                                    switch ($status_acc) {
                                        case 'acc':
                                            $status_class = 'success';
                                            $status_text = 'ACC';
                                            break;
                                        case 'revisi':
                                            $status_class = 'warning';
                                            $status_text = 'Revisi';
                                            break;
                                    }
                                    ?>
                                    <span class="badge badge-<?php echo $status_class; ?> badge-lg">
                                        <?php echo $status_text; ?>
                                    </span>
                                </td>
                            </tr>
                            <tr>
                                <th>Sebagai</th>
                                <td>
                                    <span class="badge badge-info">
                                        <?php echo isset($tipe_pembimbing) && $tipe_pembimbing == 'pembimbing_1' ? 'Pembimbing 1' : 'Pembimbing 2'; ?>
                                    </span>
                                </td>
                            </tr>
                        </table>
                    </div>
                </div>

                <hr>

                <h5>Judul Skripsi</h5>
                <div class="form-group">
                    <label>Judul Pengajuan</label>
                    <p class="form-control-plaintext"><strong><?php echo htmlspecialchars($pengajuan->judul_pengajuan ?? ''); ?></strong></p>
                </div>

                <?php if (!empty($pengajuan->judul_disetujui)): ?>
                    <div class="form-group">
                        <label>Judul yang Disetujui Sekprodi</label>
                        <p class="form-control-plaintext"><strong><?php echo htmlspecialchars($pengajuan->judul_disetujui); ?></strong></p>
                    </div>
                <?php endif; ?>

                <?php if (!empty($pengajuan->deskripsi)): ?>
                    <div class="form-group">
                        <label>Deskripsi</label>
                        <p class="form-control-plaintext"><?php echo nl2br(htmlspecialchars($pengajuan->deskripsi)); ?></p>
                    </div>
                <?php endif; ?>

                <?php if (isset($tipe_pembimbing) && $tipe_pembimbing == 'pembimbing_1'): ?>
                    <div class="card card-outline card-info mb-3">
                        <div class="card-header">
                            <h3 class="card-title"><i class="fas fa-edit"></i> Ubah Judul Skripsi (Pembimbing 1)</h3>
                        </div>
                        <div class="card-body">
                            <?php echo form_open('Ajuan_judul_skripsi/ubah_judul_mahasiswa/' . $pengajuan->id); ?>
                            <div class="form-group">
                                <label for="judul_disetujui">Judul Skripsi <span class="text-danger">*</span></label>
                                <textarea name="judul_disetujui" id="judul_disetujui" class="form-control" rows="3" required minlength="10"><?php echo htmlspecialchars(($pengajuan->judul_disetujui ?? $pengajuan->judul_pengajuan) ?? ''); ?></textarea>
                                <small class="form-text text-muted">Pembimbing 1 dapat mengubah judul meskipun status ACC. Perubahan disinkronkan ke data skripsi & bimbingan.</small>
                            </div>
                            <button type="submit" class="btn btn-info" onclick="return confirm('Simpan perubahan judul skripsi mahasiswa?');">
                                <i class="fas fa-save"></i> Simpan Perubahan Judul
                            </button>
                            <?php echo form_close(); ?>
                        </div>
                    </div>
                <?php endif; ?>

                <?php 
                $catatan_field = ($tipe_pembimbing == 'pembimbing_1') ? 'catatan_revisi_p1' : 'catatan_revisi_p2';
                $catatan_revisi = isset($pengajuan->$catatan_field) ? $pengajuan->$catatan_field : null;
                if ($catatan_revisi): 
                ?>
                    <div class="alert alert-warning">
                        <h5>Catatan Revisi Sebelumnya</h5>
                        <p><?php echo nl2br(htmlspecialchars($catatan_revisi)); ?></p>
                    </div>
                <?php endif; ?>

                <?php if (!empty($pengajuan->file_proposal)): ?>
                    <div class="form-group">
                        <label>Link Google Drive Proposal</label>
                        <p>
                            <?php 
                            $this->load->helper('google_drive');
                            echo google_drive_link_html($pengajuan->file_proposal, 'Buka Proposal');
                            ?>
                        </p>
                    </div>
                <?php endif; ?>

                <?php if ($status_acc == 'pending'): ?>
                    <hr>
                    <h5>Review Judul</h5>
                    
                    <?php echo form_open('Ajuan_judul_skripsi/detail/' . $pengajuan->id, array('method' => 'post')); ?>
                    
                    <!-- Form ACC -->
                    <div class="card card-success mb-3">
                        <div class="card-header">
                            <h3 class="card-title">Setujui Judul</h3>
                        </div>
                        <div class="card-body">
                            <p>Anda dapat menyetujui judul skripsi ini. Jika kedua pembimbing sudah menyetujui, maka mahasiswa dapat melanjutkan ke tahap berikutnya.</p>
                            <div class="form-group">
                                <label for="catatan_acc">Catatan (Opsional)</label>
                                <textarea name="catatan" id="catatan_acc" class="form-control" rows="3" placeholder="Tambahkan catatan jika diperlukan..."></textarea>
                            </div>
                            <input type="hidden" name="action" value="acc">
                            <button type="submit" class="btn btn-success" onclick="return confirm('Setujui judul skripsi ini?')">
                                <i class="fas fa-check"></i> Setujui Judul
                            </button>
                        </div>
                    </div>
                    
                    <?php echo form_close(); ?>

                    <!-- Form Revisi -->
                    <?php echo form_open('Ajuan_judul_skripsi/detail/' . $pengajuan->id, array('method' => 'post')); ?>
                    <div class="card card-warning">
                        <div class="card-header">
                            <h3 class="card-title">Minta Revisi</h3>
                        </div>
                        <div class="card-body">
                            <div class="form-group">
                                <label for="catatan_revisi">Catatan Revisi <span class="text-danger">*</span></label>
                                <textarea name="catatan" id="catatan_revisi" class="form-control" rows="5" required></textarea>
                                <small class="form-text text-muted">Berikan catatan revisi untuk mahasiswa. Mahasiswa akan diminta untuk mengupload bukti perubahan judul yang sudah ditandatangani pembimbing.</small>
                            </div>

                            <input type="hidden" name="action" value="revisi">
                            <button type="submit" class="btn btn-warning">
                                <i class="fas fa-edit"></i> Kirim Catatan Revisi
                            </button>
                        </div>
                    </div>
                    <?php echo form_close(); ?>
                <?php else: ?>
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle"></i>
                        Anda sudah memberikan review untuk judul ini.
                        <?php if ($status_acc == 'acc'): ?>
                            <br>Judul telah disetujui. Jika kedua pembimbing sudah menyetujui, mahasiswa dapat melanjutkan ke tahap berikutnya.
                        <?php elseif ($status_acc == 'revisi'): ?>
                            <br>Anda telah meminta revisi. Mahasiswa akan diminta untuk mengupload bukti perubahan judul yang sudah ditandatangani pembimbing.
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>




