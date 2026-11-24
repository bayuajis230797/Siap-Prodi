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

        <div class="card">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-comments"></i> Detail Bimbingan: <?php echo htmlspecialchars($bimbingan->judul_topik); ?>
                </h3>
                <div class="card-tools">
                    <a href="<?php echo site_url('Bimbingan_skripsi_mahasiswa/bimbingan'); ?>" class="btn btn-secondary btn-sm">
                        <i class="fas fa-arrow-left"></i> Kembali
                    </a>
                    <a href="<?php echo site_url('Bimbingan_skripsi_mahasiswa/cetak_histori_bimbingan/' . encode_id($bimbingan->id)); ?>" 
                       class="btn btn-success btn-sm" target="_blank">
                        <i class="fas fa-file-pdf"></i> Cetak PDF
                    </a>
                </div>
            </div>
            <div class="card-body">
                <div class="row mb-4">
                    <div class="col-md-6">
                        <h5 class="mb-3"><i class="fas fa-info-circle"></i> Informasi Topik</h5>
                        <table class="table table-sm table-bordered">
                            <tr>
                                <th width="40%">Judul Topik</th>
                                <td><strong><?php echo htmlspecialchars($bimbingan->judul_topik); ?></strong></td>
                            </tr>
                            <tr>
                                <th>Status</th>
                                <td>
                                    <span class="badge badge-<?php echo $bimbingan->status === 'open' ? 'success' : 'secondary'; ?>">
                                        <?php echo ucfirst($bimbingan->status); ?>
                                    </span>
                                </td>
                            </tr>
                            <tr>
                                <th>Pembimbing</th>
                                <td>
                                    <?php if ($bimbingan->tipe_pembimbing == 1): ?>
                                        <span class="badge badge-info">Pembimbing 1</span>
                                    <?php else: ?>
                                        <span class="badge badge-warning">Pembimbing 2</span>
                                    <?php endif; ?>
                                    <?php if (isset($bimbingan->nama_dosen)): ?>
                                        <br><?php echo htmlspecialchars($bimbingan->nama_dosen); ?>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <tr>
                                <th>Last Activity</th>
                                <td><?php echo date('d/m/Y H:i', strtotime($bimbingan->last_activity)); ?></td>
                            </tr>
                        </table>
                    </div>
                    <div class="col-md-6">
                        <h5 class="mb-3"><i class="fas fa-chart-bar"></i> Statistik</h5>
                        <table class="table table-sm table-bordered">
                            <tr>
                                <th width="40%">Total Log</th>
                                <td><?php echo isset($statistik) && $statistik ? $statistik->total_log : 0; ?></td>
                            </tr>
                            <tr>
                                <th>Total ACC</th>
                                <td><span class="badge badge-success"><?php echo isset($statistik) && $statistik ? $statistik->total_acc : 0; ?></span></td>
                            </tr>
                            <tr>
                                <th>Total Revisi</th>
                                <td><span class="badge badge-warning"><?php echo isset($statistik) && $statistik ? $statistik->total_revisi : 0; ?></span></td>
                            </tr>
                        </table>
                    </div>
                </div>

                <!-- Form Kirim Revisi (jika ada revisi dari dosen) -->
                <?php if ($can_send_revisi): ?>
                    <div class="card card-warning mb-4">
                        <div class="card-header">
                            <h5 class="card-title mb-0">
                                <i class="fas fa-edit"></i> Kirim Revisi Laporan
                            </h5>
                        </div>
                        <div class="card-body">
                            <?php 
                            $this->load->helper('google_drive');
                            echo form_open_multipart('Bimbingan_skripsi_mahasiswa/bimbingan_kirim/' . encode_id($bimbingan->id)); 
                            ?>
                                <div class="form-group">
                                    <label for="file_laporan">Berkas Laporan Revisi <span class="text-danger">*</span></label>
                                    <input type="file" name="file_laporan" id="file_laporan" class="form-control-file"
                                           accept=".pdf,.doc,.docx,.zip,.rar" required>
                                    <?php echo form_error('file_laporan', '<small class="text-danger">', '</small>'); ?>
                                    <small class="form-text text-muted">
                                        Diunggah ke Google Drive institusi. PDF/DOC/ZIP tanpa batas form.
                                    </small>
                                </div>

                                <div class="form-group">
                                    <label for="pesan">Pesan Tambahan (Opsional)</label>
                                    <textarea name="pesan" id="pesan" class="form-control" rows="3" 
                                              placeholder="Tambahkan pesan atau catatan untuk pembimbing..."></textarea>
                                </div>

                                <div class="form-group">
                                    <button type="submit" class="btn btn-warning">
                                        <i class="fas fa-paper-plane"></i> Kirim Revisi
                                    </button>
                                </div>
                            <?php echo form_close(); ?>
                        </div>
                    </div>
                <?php endif; ?>

                <!-- Timeline Log Bimbingan -->
                <h5 class="mb-3"><i class="fas fa-history"></i> Timeline Bimbingan</h5>
                <?php if (empty($logs)): ?>
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle"></i> Belum ada log bimbingan.
                    </div>
                <?php else: ?>
                    <div class="timeline">
                        <?php foreach ($logs as $log): ?>
                            <div class="card mb-3 <?php echo $log->role_pengguna === 'dosen' ? 'border-left-success' : 'border-left-primary'; ?>">
                                <div class="card-header">
                                    <strong>
                                        <?php if ($log->role_pengguna === 'mahasiswa'): ?>
                                            <i class="fas fa-user text-primary"></i> Saya (Mahasiswa)
                                        <?php else: ?>
                                            <i class="fas fa-user-tie text-success"></i> <?php echo htmlspecialchars($bimbingan->nama_dosen ?: 'Pembimbing'); ?>
                                        <?php endif; ?>
                                    </strong>
                                    <small class="float-right text-muted">
                                        <?php echo date('d/m/Y H:i', strtotime($log->created_at)); ?>
                                    </small>
                                </div>
                                <div class="card-body">
                                    <?php if (!empty($log->pesan)): ?>
                                        <p class="mb-2"><?php echo nl2br(htmlspecialchars($log->pesan)); ?></p>
                                    <?php endif; ?>
                                    
                                    <?php if ($log->file_path): ?>
                                        <div class="alert alert-info mb-2">
                                            <i class="fas fa-link"></i> 
                                            <strong>Link Google Drive:</strong>
                                            <?php 
                                            $this->load->helper('google_drive');
                                            echo google_drive_link_html($log->file_path, htmlspecialchars($log->file_name ?: 'Buka File'));
                                            ?>
                                        </div>
                                    <?php endif; ?>
                                    
                                    <?php if ($log->status_revisi): ?>
                                        <div class="alert alert-<?php echo $log->status_revisi === 'acc' ? 'success' : 'warning'; ?> mb-0">
                                            <strong>
                                                <i class="fas fa-<?php echo $log->status_revisi === 'acc' ? 'check-circle' : 'exclamation-triangle'; ?>"></i>
                                                Status: <?php echo strtoupper($log->status_revisi); ?>
                                            </strong>
                                            <?php if ($log->keterangan_revisi): ?>
                                                <br><small><?php echo nl2br(htmlspecialchars($log->keterangan_revisi)); ?></small>
                                            <?php endif; ?>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

