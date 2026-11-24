<div class="row">
    <div class="col-md-12">
        <?php if ($this->session->flashdata('error')): ?>
            <div class="alert alert-danger alert-dismissible fade show">
                <i class="fas fa-exclamation-circle"></i> <?php echo $this->session->flashdata('error'); ?>
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
        <?php endif; ?>

        <?php if ($this->session->flashdata('success')): ?>
            <div class="alert alert-success alert-dismissible fade show">
                <i class="fas fa-check-circle"></i> <?php echo $this->session->flashdata('success'); ?>
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
        <?php endif; ?>

        <?php if (!empty($perubahan_list) && count($perubahan_list) > 0): ?>
            <!-- Tampilkan Tabel Riwayat Perubahan Judul -->
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Riwayat Perubahan Judul Skripsi</h3>
                    <div class="card-tools">
                        <a href="<?php echo site_url('Skripsi_pengajuan'); ?>" class="btn btn-secondary btn-sm">
                            <i class="fas fa-arrow-left"></i> Kembali
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <?php if (isset($judul_terbaru) && !empty($judul_terbaru)): ?>
                        <div class="alert alert-success">
                            <h6><i class="fas fa-check-circle"></i> Judul Terbaru yang Disetujui</h6>
                            <p class="mb-0"><strong><?php echo htmlspecialchars($judul_terbaru); ?></strong></p>
                        </div>
                    <?php endif; ?>

                    <div class="table-responsive">
                        <table class="table table-bordered table-striped table-hover">
                            <thead>
                                <tr>
                                    <th style="width: 5%;">No</th>
                                    <th style="width: 25%;">Judul Lama</th>
                                    <th style="width: 25%;">Judul Baru</th>
                                    <th style="width: 10%;">Status</th>
                                    <th style="width: 15%;">Tanggal Pengajuan</th>
                                    <th style="width: 10%;">File TTD</th>
                                    <th style="width: 10%;">Catatan</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php 
                                $no = 1;
                                foreach ($perubahan_list as $perubahan): 
                                    // Tentukan status badge
                                    $status_badge = '';
                                    switch($perubahan->status_approve_sekprodi) {
                                        case 'acc':
                                            $status_badge = '<span class="badge badge-success">Disetujui</span>';
                                            break;
                                        case 'ditolak':
                                            $status_badge = '<span class="badge badge-danger">Ditolak</span>';
                                            break;
                                        case 'pending':
                                        default:
                                            $status_badge = '<span class="badge badge-warning">Menunggu Validasi</span>';
                                            break;
                                    }
                                ?>
                                    <tr>
                                        <td><?php echo $no++; ?></td>
                                        <td>
                                            <small><?php echo htmlspecialchars($perubahan->judul_lama ?: '-'); ?></small>
                                        </td>
                                        <td>
                                            <strong><?php echo htmlspecialchars($perubahan->judul_baru); ?></strong>
                                        </td>
                                        <td><?php echo $status_badge; ?></td>
                                        <td>
                                            <small><?php echo date('d/m/Y H:i', strtotime($perubahan->created_at)); ?></small>
                                        </td>
                                        <td>
                                            <?php if (!empty($perubahan->file_ttd_persetujuan)): ?>
                                                <?php 
                                                $this->load->helper('google_drive');
                                                echo google_drive_link_html($perubahan->file_ttd_persetujuan, 'Buka');
                                                ?>
                                            <?php else: ?>
                                                <span class="text-muted">-</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php if (!empty($perubahan->catatan_sekprodi)): ?>
                                                <div class="small text-dark" style="white-space: pre-line;">
                                                    <?php echo nl2br(htmlspecialchars($perubahan->catatan_sekprodi), false); ?>
                                                </div>
                                            <?php else: ?>
                                                <span class="text-muted">-</span>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>

                    <?php if (!empty($perubahan_pending) && isset($perubahan) && $perubahan): ?>
                        <div class="mt-4">
                            <hr>
                            <h5>Edit Pengajuan yang Masih Pending</h5>
                            <?php echo form_open_multipart('Skripsi_perubahan_judul'); ?>
                                <div class="form-group">
                                    <label for="judul_baru">Judul Baru <span class="text-danger">*</span></label>
                                    <textarea name="judul_baru" id="judul_baru" class="form-control" rows="3" required><?php echo set_value('judul_baru', $perubahan->judul_baru); ?></textarea>
                                    <?php echo form_error('judul_baru', '<small class="text-danger">', '</small>'); ?>
                                </div>
                                <div class="form-group">
                                    <label for="file_ttd_persetujuan">Berkas Bukti Perubahan Judul (TTD Pembimbing) <span class="text-danger">*</span></label>
                                    <input type="file" name="file_ttd_persetujuan" id="file_ttd_persetujuan" class="form-control-file"
                                           accept=".pdf,.doc,.docx,.jpg,.jpeg,.png" required>
                                    <?php echo form_error('file_ttd_persetujuan', '<small class="text-danger">', '</small>'); ?>
                                    <small class="form-text text-muted">
                                        Diunggah ke Google Drive institusi. PDF berisi bukti TTD pembimbing, tanpa batas form.
                                    </small>
                                    <?php if (!empty($perubahan->file_ttd_persetujuan)): ?>
                                        <div class="alert alert-info mt-2">
                                            <i class="fas fa-link"></i> Link saat ini: 
                                            <?php 
                                            $this->load->helper('google_drive');
                                            echo google_drive_link_html($perubahan->file_ttd_persetujuan, 'Buka Berkas');
                                            ?>
                                        </div>
                                    <?php endif; ?>
                                </div>
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save"></i> Update Pengajuan
                                </button>
                            <?php echo form_close(); ?>
                        </div>
                    <?php elseif (!empty($perubahan_ditolak)): ?>
                        <div class="mt-4">
                            <hr>
                            <h5>Ajukan Ulang Perubahan Judul</h5>
                            <div class="alert alert-warning">
                                <h6><i class="fas fa-exclamation-triangle"></i> Pengajuan Terakhir Ditolak Sekprodi</h6>
                                <p class="mb-1"><strong>Catatan Sekprodi:</strong></p>
                                <p class="mb-0" style="white-space: pre-line;">
                                    <?php echo nl2br(htmlspecialchars($perubahan_ditolak->catatan_sekprodi ?: 'Tidak ada catatan.'), false); ?>
                                </p>
                            </div>
                            <?php echo form_open_multipart('Skripsi_perubahan_judul'); ?>
                                <div class="form-group">
                                    <label for="judul_baru">Judul Baru <span class="text-danger">*</span></label>
                                    <textarea name="judul_baru" id="judul_baru" class="form-control" rows="3" required><?php echo set_value('judul_baru', $perubahan_ditolak->judul_baru); ?></textarea>
                                    <?php echo form_error('judul_baru', '<small class="text-danger">', '</small>'); ?>
                                </div>
                                <div class="form-group">
                                    <label for="file_ttd_persetujuan">Berkas Bukti Perubahan Judul (TTD Pembimbing) <span class="text-danger">*</span></label>
                                    <input type="file" name="file_ttd_persetujuan" id="file_ttd_persetujuan" class="form-control-file"
                                           accept=".pdf,.doc,.docx,.jpg,.jpeg,.png" required>
                                    <?php echo form_error('file_ttd_persetujuan', '<small class="text-danger">', '</small>'); ?>
                                </div>
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-paper-plane"></i> Ajukan Ulang ke Sekprodi
                                </button>
                            <?php echo form_close(); ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        <?php else: ?>
            <!-- Tampilkan Form Pengajuan Baru -->
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Perubahan Judul Skripsi</h3>
                    <div class="card-tools">
                        <a href="<?php echo site_url('Skripsi_pengajuan'); ?>" class="btn btn-secondary btn-sm">
                            <i class="fas fa-arrow-left"></i> Kembali
                        </a>
                    </div>
                </div>
                <?php echo form_open_multipart('Skripsi_perubahan_judul'); ?>
                <div class="card-body">
                    <?php if (validation_errors()): ?>
                        <div class="alert alert-danger">
                            <?php echo validation_errors(); ?>
                        </div>
                    <?php endif; ?>

                    <div class="alert alert-warning">
                        <h6><i class="fas fa-exclamation-triangle"></i> Catatan Penting</h6>
                        <p class="mb-0">Judul Skripsi Anda memerlukan revisi dari pembimbing. Silakan ajukan judul baru yang sudah ditandatangani oleh pembimbing.</p>
                    </div>

                    <!-- Judul Lama -->
                    <div class="form-group">
                        <label>Judul Lama</label>
                        <div class="alert alert-secondary">
                            <?php 
                            // Prioritas: judul_disetujui > judul_pengajuan
                            $judul_lama = (isset($pengajuan->judul_disetujui) && !empty($pengajuan->judul_disetujui)) 
                                ? $pengajuan->judul_disetujui 
                                : (isset($pengajuan->judul_pengajuan) && !empty($pengajuan->judul_pengajuan)
                                    ? $pengajuan->judul_pengajuan
                                    : '-');
                            ?>
                            <p class="mb-0"><strong><?php echo htmlspecialchars($judul_lama); ?></strong></p>
                        </div>
                    </div>

                    <?php if (isset($pengajuan->catatan_revisi_p1) || isset($pengajuan->catatan_revisi_p2)): ?>
                        <div class="alert alert-info">
                            <h6><i class="fas fa-comment"></i> Catatan Revisi dari Pembimbing</h6>
                            <?php if (isset($pengajuan->catatan_revisi_p1) && $pengajuan->catatan_revisi_p1): ?>
                                <p class="mb-1"><strong>Pembimbing 1:</strong></p>
                                <p class="mb-2"><?php echo nl2br(htmlspecialchars($pengajuan->catatan_revisi_p1)); ?></p>
                            <?php endif; ?>
                            <?php if (isset($pengajuan->catatan_revisi_p2) && $pengajuan->catatan_revisi_p2): ?>
                                <p class="mb-1"><strong>Pembimbing 2:</strong></p>
                                <p class="mb-0"><?php echo nl2br(htmlspecialchars($pengajuan->catatan_revisi_p2)); ?></p>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>

                    <!-- Judul Baru -->
                    <div class="form-group">
                        <label for="judul_baru">Judul Baru <span class="text-danger">*</span></label>
                        <textarea name="judul_baru" id="judul_baru" class="form-control" rows="3" required><?php echo set_value('judul_baru', isset($perubahan_form->judul_baru) ? $perubahan_form->judul_baru : ''); ?></textarea>
                        <?php echo form_error('judul_baru', '<small class="text-danger">', '</small>'); ?>
                        <small class="form-text text-muted">Masukkan judul Skripsi yang baru (minimal 10 karakter)</small>
                    </div>

                    <div class="form-group">
                        <label for="file_ttd_persetujuan">Berkas Bukti Perubahan Judul (TTD Pembimbing) <span class="text-danger">*</span></label>
                        <input type="file" name="file_ttd_persetujuan" id="file_ttd_persetujuan" class="form-control-file"
                               accept=".pdf,.doc,.docx,.jpg,.jpeg,.png" required>
                        <?php echo form_error('file_ttd_persetujuan', '<small class="text-danger">', '</small>'); ?>
                        <small class="form-text text-muted">
                            Diunggah ke Google Drive institusi. PDF berisi bukti TTD pembimbing, tanpa batas form.
                        </small>
                        <?php if (isset($perubahan_form->file_ttd_persetujuan) && !empty($perubahan_form->file_ttd_persetujuan)): ?>
                            <div class="alert alert-info mt-2">
                                <i class="fas fa-link"></i> Link saat ini: 
                                <?php 
                                $this->load->helper('google_drive');
                                echo google_drive_link_html($perubahan_form->file_ttd_persetujuan, 'Buka Berkas');
                                ?>
                            </div>
                        <?php endif; ?>
                    </div>

                    <div class="alert alert-info">
                        <i class="fas fa-info-circle"></i> 
                        <strong>Informasi:</strong> Setelah mengajukan perubahan judul, permintaan akan dikirim ke Sekprodi untuk validasi. 
                        Jika disetujui, judul Skripsi akan otomatis diperbarui.
                    </div>
                </div>
                <div class="card-footer">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-paper-plane"></i> Ajukan Perubahan Judul
                    </button>
                    <a href="<?php echo site_url('Skripsi_pengajuan'); ?>" class="btn btn-secondary">
                        <i class="fas fa-times"></i> Kembali
                    </a>
                </div>
                <?php echo form_close(); ?>
            </div>
        <?php endif; ?>
    </div>
</div>

<script>
    // Initialize tooltips
    $(function () {
        $('[data-toggle="tooltip"]').tooltip();
    });
</script>