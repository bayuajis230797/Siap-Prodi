<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Detail Perubahan Judul PKL</h3>
                <div class="card-tools">
                    <a href="<?php echo site_url('Pkl_perubahan_judul_sekprodi'); ?>" class="btn btn-secondary btn-sm">
                        <i class="fas fa-arrow-left"></i> Kembali
                    </a>
                </div>
            </div>
            <div class="card-body">
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

                <!-- Informasi Mahasiswa -->
                <div class="row mb-4">
                    <div class="col-md-6">
                        <h5 class="mb-3"><i class="fas fa-user"></i> Informasi Mahasiswa</h5>
                        <table class="table table-sm table-bordered">
                            <tr>
                                <th width="40%">NIM</th>
                                <td><strong><?php echo htmlspecialchars($pengajuan->nim ?: '-'); ?></strong></td>
                            </tr>
                            <tr>
                                <th>Nama Mahasiswa</th>
                                <td><?php echo htmlspecialchars($pengajuan->nama_mahasiswa ?: '-'); ?></td>
                            </tr>
                            <tr>
                                <th>Kelas</th>
                                <td><span class="badge badge-info"><?php echo htmlspecialchars($pengajuan->kelas ?: '-'); ?></span></td>
                            </tr>
                            <?php if (isset($pengajuan->pembimbing_nama) && $pengajuan->pembimbing_nama): ?>
                                <tr>
                                    <th>Dosen Pembimbing</th>
                                    <td><?php echo htmlspecialchars($pengajuan->pembimbing_nama); ?></td>
                                </tr>
                            <?php endif; ?>
                        </table>
                    </div>
                    <div class="col-md-6">
                        <h5 class="mb-3"><i class="fas fa-file-alt"></i> Status Pengajuan</h5>
                        <table class="table table-sm table-bordered">
                            <tr>
                                <th width="40%">Status Perubahan Judul</th>
                                <td>
                                    <?php
                                    $status_class = 'warning';
                                    $status_text = 'Pending';
                                    if ($pengajuan->status_approve_revisi == 'disetujui') {
                                        $status_class = 'success';
                                        $status_text = 'Disetujui';
                                    } elseif ($pengajuan->status_approve_revisi == 'ditolak') {
                                        $status_class = 'danger';
                                        $status_text = 'Ditolak';
                                    }
                                    ?>
                                    <span class="badge badge-<?php echo $status_class; ?>"><?php echo $status_text; ?></span>
                                </td>
                            </tr>
                            <tr>
                                <th>Tanggal Pengajuan</th>
                                <td><?php echo date('d/m/Y H:i', strtotime($pengajuan->created_at)); ?></td>
                            </tr>
                        </table>
                    </div>
                </div>

                <!-- Perbandingan Judul -->
                <div class="row mb-4">
                    <div class="col-md-6">
                        <h5 class="mb-3"><i class="fas fa-book"></i> Judul Lama</h5>
                        <div class="alert alert-secondary">
                            <?php
                            $st_d = isset($pengajuan->status_approve_revisi) ? (string) $pengajuan->status_approve_revisi : '';
                            if ($st_d === 'disetujui' && trim((string) ($pengajuan->judul_revisi ?? '')) === '') {
                                echo '<p class="mb-0 text-muted">— <small>(Judul sebelum perubahan tidak disimpan setelah pengesahan; yang berlaku adalah judul di samping.)</small></p>';
                            } else {
                                echo '<p class="mb-0"><strong>' . htmlspecialchars($pengajuan->judul_pkl ?: '-') . '</strong></p>';
                            }
                            ?>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <h5 class="mb-3"><i class="fas fa-book-open"></i> Judul Baru</h5>
                        <div class="alert alert-info">
                            <?php
                            $judul_revisi_detail = isset($pengajuan->judul_revisi) ? trim((string) $pengajuan->judul_revisi) : '';
                            if ($judul_revisi_detail !== '') {
                                echo '<p class="mb-0"><strong>' . htmlspecialchars($judul_revisi_detail) . '</strong></p>';
                            } elseif ($st_d === 'disetujui' && !empty($pengajuan->judul_pkl)) {
                                echo '<p class="mb-0"><strong>' . htmlspecialchars($pengajuan->judul_pkl) . '</strong></p>';
                                echo '<p class="mb-0 mt-2"><small class="text-success">Judul berlaku setelah disetujui Sekprodi.</small></p>';
                            } elseif (!empty($pengajuan->file_approve_revisi)) {
                                $nama_berkas_detail = basename(str_replace('\\', '/', (string) $pengajuan->file_approve_revisi));
                                echo '<p class="mb-2 text-muted">Kolom judul revisi kosong di database; judul biasanya tercantum di berkas PDF berikut.</p>';
                                echo '<p class="mb-0"><i class="fas fa-file-pdf text-danger"></i> <strong>' . htmlspecialchars($nama_berkas_detail) . '</strong></p>';
                            } else {
                                echo '<p class="mb-0 text-muted">—</p>';
                            }
                            ?>
                        </div>
                    </div>
                </div>

                <!-- File Bukti Perubahan Judul -->
                <?php if ($pengajuan->file_approve_revisi): ?>
                    <div class="row mb-4">
                        <div class="col-12">
                            <h5 class="mb-3"><i class="fas fa-file-pdf"></i> Bukti Perubahan Judul (TTD Pembimbing)</h5>
                            <div class="alert alert-info">
                                <p class="mb-2">
                                    <i class="fas fa-file-pdf text-danger"></i> 
                                    <strong>File:</strong> <?php echo basename($pengajuan->file_approve_revisi); ?>
                                </p>
                                <a href="<?php echo site_url('Pkl_perubahan_judul_sekprodi/download_approve_revisi/' . $pengajuan->id); ?>" 
                                   class="btn btn-primary btn-sm">
                                    <i class="fas fa-download"></i> Download File
                                </a>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>

                <!-- Form Approve/Reject -->
                <?php if ($pengajuan->status_approve_revisi == 'pending' || !$pengajuan->status_approve_revisi): ?>
                    <div class="card card-primary">
                        <div class="card-header">
                            <h5 class="card-title mb-0"><i class="fas fa-check-circle"></i> Proses Perubahan Judul PKL</h5>
                        </div>
                        <div class="card-body">
                            <?php echo form_open('Pkl_perubahan_judul_sekprodi/detail/' . $pengajuan->id); ?>
                                <div class="form-group">
                                    <label>Aksi <span class="text-danger">*</span></label>
                                    <div>
                                        <div class="form-check form-check-inline">
                                            <input type="radio" name="action" id="action_approve" value="approve" class="form-check-input" required>
                                            <label class="form-check-label" for="action_approve">
                                                <i class="fas fa-check text-success"></i> Setujui Perubahan Judul
                                            </label>
                                        </div>
                                        <div class="form-check form-check-inline">
                                            <input type="radio" name="action" id="action_reject" value="reject" class="form-check-input" required>
                                            <label class="form-check-label" for="action_reject">
                                                <i class="fas fa-times text-danger"></i> Tolak Perubahan Judul
                                            </label>
                                        </div>
                                    </div>
                                </div>

                                <div class="alert alert-warning">
                                    <i class="fas fa-exclamation-triangle"></i>
                                    <strong>Perhatian:</strong> Jika disetujui, judul PKL akan otomatis diperbarui dan bimbingan PKL akan aktif.
                                </div>

                                <div class="form-group mt-3">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-save"></i> Proses
                                    </button>
                                    <a href="<?php echo site_url('Pkl_perubahan_judul_sekprodi'); ?>" class="btn btn-secondary">
                                        <i class="fas fa-times"></i> Batal
                                    </a>
                                </div>
                            <?php echo form_close(); ?>
                        </div>
                    </div>
                <?php else: ?>
                    <div class="alert alert-<?php echo $pengajuan->status_approve_revisi == 'disetujui' ? 'success' : 'danger'; ?>">
                        <h6>
                            <i class="fas fa-<?php echo $pengajuan->status_approve_revisi == 'disetujui' ? 'check-circle' : 'times-circle'; ?>"></i>
                            Perubahan Judul Telah <?php echo $pengajuan->status_approve_revisi == 'disetujui' ? 'Disetujui' : 'Ditolak'; ?>
                        </h6>
                        <?php if ($pengajuan->status_approve_revisi == 'disetujui'): ?>
                            <p class="mb-0">Judul PKL telah diperbarui menjadi: <strong><?php echo htmlspecialchars($pengajuan->judul_pkl); ?></strong></p>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>





