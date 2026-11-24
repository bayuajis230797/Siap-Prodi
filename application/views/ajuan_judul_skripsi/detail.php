<div class="row">
    <div class="col-12">
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
                                <td><?php echo htmlspecialchars($pengajuan->nim); ?></td>
                            </tr>
                            <tr>
                                <th>Nama Mahasiswa</th>
                                <td><?php echo htmlspecialchars($pengajuan->nama_mahasiswa); ?></td>
                            </tr>
                            <tr>
                                <th>Kelas</th>
                                <td><?php echo htmlspecialchars($pengajuan->kelas); ?></td>
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
                                    $status_acc = $current_status ?? 'pending';
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
                                    <span class="badge badge-<?php echo $status_class; ?>">
                                        <?php echo $status_text; ?>
                                    </span>
                                </td>
                            </tr>
                            <tr>
                                <th>Sebagai</th>
                                <td>Pembimbing <?php echo $tipe_pembimbing; ?></td>
                            </tr>
                        </table>
                    </div>
                </div>

                <hr>

                <h5>Judul Skripsi</h5>
                <div class="form-group">
                    <label>Judul yang Disetujui Sekprodi</label>
                    <p class="form-control-plaintext"><strong><?php echo htmlspecialchars($pengajuan->judul_disetujui ?: $pengajuan->judul_pengajuan); ?></strong></p>
                </div>

                <?php if ($pengajuan->deskripsi): ?>
                    <div class="form-group">
                        <label>Deskripsi</label>
                        <p class="form-control-plaintext"><?php echo nl2br(htmlspecialchars($pengajuan->deskripsi)); ?></p>
                    </div>
                <?php endif; ?>

                <?php 
                $catatan_revisi = $pengajuan->$catatan_field;
                if ($catatan_revisi): 
                ?>
                    <div class="alert alert-warning">
                        <h5>Catatan Revisi Sebelumnya</h5>
                        <p><?php echo nl2br(htmlspecialchars($catatan_revisi)); ?></p>
                    </div>
                <?php endif; ?>

                <?php if ($current_status == 'pending'): ?>
                    <hr>
                    <h5>Review Judul</h5>
                    
                    <!-- Form ACC -->
                    <div class="card card-success mb-3">
                        <div class="card-header">
                            <h3 class="card-title">Setujui Judul</h3>
                        </div>
                        <div class="card-body">
                            <p>Anda dapat menyetujui judul skripsi ini. Jika kedua pembimbing sudah menyetujui, maka mahasiswa akan otomatis masuk ke menu Bimbingan Skripsi.</p>
                            <a href="<?php echo site_url('Ajuan_judul_skripsi/acc/' . $pengajuan->id); ?>" 
                               class="btn btn-success" 
                               onclick="return confirm('Setujui judul skripsi ini?')">
                                <i class="fas fa-check"></i> Setujui Judul
                            </a>
                        </div>
                    </div>

                    <!-- Form Revisi -->
                    <?php echo form_open('Ajuan_judul_skripsi/revisi/' . $pengajuan->id, array('method' => 'post')); ?>
                    <div class="card card-warning">
                        <div class="card-header">
                            <h3 class="card-title">Minta Revisi</h3>
                        </div>
                        <div class="card-body">
                            <div class="form-group">
                                <label for="catatan_revisi">Catatan Revisi <span class="text-danger">*</span></label>
                                <textarea name="catatan_revisi" id="catatan_revisi" class="form-control" rows="5" required></textarea>
                                <small class="form-text text-muted">Berikan catatan revisi untuk mahasiswa. Mahasiswa akan diminta untuk mengupload bukti perubahan judul yang sudah ditandatangani pembimbing.</small>
                            </div>

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
                        <?php if ($current_status == 'acc'): ?>
                            <br>Judul telah disetujui. Jika kedua pembimbing sudah menyetujui, mahasiswa akan otomatis masuk ke menu Bimbingan Skripsi.
                        <?php elseif ($current_status == 'revisi'): ?>
                            <br>Anda telah meminta revisi. Mahasiswa akan diminta untuk mengupload bukti perubahan judul yang sudah ditandatangani pembimbing.
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

