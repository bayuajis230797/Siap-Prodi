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
                    <i class="fas fa-clipboard-check"></i> Detail Ajuan Judul PKL
                </h3>
                <div class="card-tools">
                    <a href="<?php echo site_url('Ajuan_judul_pkl'); ?>" class="btn btn-secondary btn-sm">
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
                                    $status_acc = $pengajuan->status_acc_judul ?? 'pending';
                                    $status_class = 'warning';
                                    $status_text = 'MENUNGGU';
                                    switch ($status_acc) {
                                        case 'acc':
                                            $status_class = 'success';
                                            $status_text = 'DISETUJUI';
                                            break;
                                        case 'revisi':
                                            $status_class = 'danger';
                                            $status_text = 'REVISI';
                                            break;
                                    }
                                    ?>
                                    <span class="badge badge-<?php echo $status_class; ?> badge-lg">
                                        <?php echo $status_text; ?>
                                    </span>
                                </td>
                            </tr>
                        </table>
                    </div>
                </div>

                <hr>

                <?php if ($tempat_pkl): ?>
                    <h5>Data Tempat PKL</h5>
                    <div class="mb-3">
                        <table class="table table-bordered">
                            <tr>
                                <th width="25%">Nama Instansi</th>
                                <td><?php echo htmlspecialchars($tempat_pkl->nama_instansi); ?></td>
                            </tr>
                            <tr>
                                <th>Alamat Instansi</th>
                                <td><?php echo nl2br(htmlspecialchars($tempat_pkl->alamat_instansi)); ?></td>
                            </tr>
                            <?php if ($tempat_pkl->nama_pembimbing_lapangan): ?>
                                <tr>
                                    <th>Pembimbing Lapangan</th>
                                    <td>
                                        <?php echo htmlspecialchars($tempat_pkl->nama_pembimbing_lapangan); ?>
                                        <?php if ($tempat_pkl->jabatan_pembimbing_lapangan): ?>
                                            <br><small class="text-muted"><?php echo htmlspecialchars($tempat_pkl->jabatan_pembimbing_lapangan); ?></small>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </table>
                    </div>
                    <hr>
                <?php endif; ?>

                <h5>Judul PKL</h5>
                <div class="form-group">
                    <label>Judul yang Diajukan</label>
                    <p class="form-control-plaintext" style="font-size: 1.1em;"><strong><?php echo htmlspecialchars($pengajuan->judul_pkl); ?></strong></p>
                </div>

                <div class="card card-outline card-info mb-3">
                    <div class="card-header">
                        <h3 class="card-title"><i class="fas fa-edit"></i> Ubah Judul PKL</h3>
                    </div>
                    <div class="card-body">
                        <?php echo form_open('Ajuan_judul_pkl/ubah_judul/' . $pengajuan->id); ?>
                        <div class="form-group">
                            <label for="judul_pkl">Judul Baru <span class="text-danger">*</span></label>
                            <textarea name="judul_pkl" id="judul_pkl" class="form-control" rows="3" required minlength="10"><?php echo htmlspecialchars($pengajuan->judul_pkl); ?></textarea>
                            <small class="form-text text-muted">Pembimbing dapat memperbaiki atau menyesuaikan judul PKL mahasiswa.</small>
                        </div>
                        <button type="submit" class="btn btn-info" onclick="return confirm('Simpan perubahan judul PKL?');">
                            <i class="fas fa-save"></i> Simpan Perubahan Judul
                        </button>
                        <?php echo form_close(); ?>
                    </div>
                </div>

                <?php if (!empty($pengajuan->temuan_masalah)): ?>
                    <div class="form-group">
                        <label>Temuan Masalah di Tempat PKL</label>
                        <div class="border rounded p-3 bg-light"><?php echo nl2br(htmlspecialchars($pengajuan->temuan_masalah)); ?></div>
                    </div>
                <?php endif; ?>

                <?php if (!empty($pengajuan->deskripsi_pengajuan)): ?>
                    <div class="form-group">
                        <label>Deskripsi (Latar Belakang Pengajuan Judul)</label>
                        <div class="border rounded p-3 bg-light"><?php echo nl2br(htmlspecialchars($pengajuan->deskripsi_pengajuan)); ?></div>
                    </div>
                <?php endif; ?>

                <?php if ($pengajuan->file_proposal): ?>
                    <div class="form-group">
                        <label>Link Google Drive Proposal</label>
                        <p>
                            <?php 
                            $this->load->helper('google_drive');
                            echo google_drive_link_html($pengajuan->file_proposal, 'Buka Proposal di Google Drive');
                            ?>
                        </p>
                    </div>
                <?php endif; ?>

                <?php if ($pengajuan->catatan_revisi): ?>
                    <div class="alert alert-warning">
                        <h5>Catatan Revisi Sebelumnya</h5>
                        <p><?php echo nl2br(htmlspecialchars($pengajuan->catatan_revisi)); ?></p>
                    </div>
                <?php endif; ?>

                <?php if ($pengajuan->status_acc_judul == 'pending'): ?>
                    <hr>
                    <h5>Review Judul</h5>
                    
                    <!-- Form ACC -->
                    <div class="card card-success mb-3">
                        <div class="card-header">
                            <h3 class="card-title">Setujui Judul</h3>
                        </div>
                        <div class="card-body">
                            <p>Anda dapat menyetujui judul PKL ini. Setelah disetujui, mahasiswa dapat melanjutkan ke proses bimbingan PKL.</p>
                            <a href="<?php echo site_url('Ajuan_judul_pkl/acc/' . $pengajuan->id); ?>" 
                               class="btn btn-success" 
                               onclick="return confirm('Setujui judul PKL ini?')">
                                <i class="fas fa-check"></i> Setujui Judul
                            </a>
                        </div>
                    </div>

                    <!-- Form Revisi -->
                    <?php echo form_open('Ajuan_judul_pkl/revisi/' . $pengajuan->id, array('method' => 'post')); ?>
                    <div class="card card-warning">
                        <div class="card-header">
                            <h3 class="card-title">Minta Revisi</h3>
                        </div>
                        <div class="card-body">
                            <div class="form-group">
                                <label for="catatan_revisi">Catatan Revisi <span class="text-danger">*</span></label>
                                <textarea name="catatan_revisi" id="catatan_revisi" class="form-control" rows="5" required placeholder="Masukkan catatan revisi untuk mahasiswa..."></textarea>
                                <small class="form-text text-muted">Berikan catatan revisi untuk mahasiswa. Mahasiswa akan diminta untuk mengupload bukti perubahan judul yang sudah ditandatangani pembimbing jika diperlukan.</small>
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
                        <?php if ($pengajuan->status_acc_judul == 'acc'): ?>
                            Judul PKL telah disetujui. Mahasiswa dapat melanjutkan ke proses bimbingan PKL.
                        <?php elseif ($pengajuan->status_acc_judul == 'revisi'): ?>
                            Anda telah meminta revisi. Mahasiswa akan diminta untuk mengupload bukti perubahan judul yang sudah ditandatangani pembimbing jika diperlukan.
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>


