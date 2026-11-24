<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Detail Validasi Perubahan Judul Proposal TA</h3>
                <div class="card-tools">
                    <a href="<?php echo site_url('Proposal_perubahan_judul_sekprodi'); ?>" class="btn btn-secondary btn-sm">
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
                                <td><strong><?php echo htmlspecialchars($pengajuan->nim ?? '-'); ?></strong></td>
                            </tr>
                            <tr>
                                <th>Nama Mahasiswa</th>
                                <td><?php echo htmlspecialchars($pengajuan->nama_mahasiswa ?? '-'); ?></td>
                            </tr>
                            <tr>
                                <th>Kelas</th>
                                <td><span class="badge badge-info"><?php echo htmlspecialchars($pengajuan->kelas ?? '-'); ?></span></td>
                            </tr>
                        </table>
                    </div>
                    <div class="col-md-6">
                        <h5 class="mb-3"><i class="fas fa-file-alt"></i> Status Pengajuan</h5>
                        <table class="table table-sm table-bordered">
                            <tr>
                                <th width="40%">Status Perubahan Judul</th>
                                <td>
                                    <?php
                                    $status_value = $perubahan->status_approve_sekprodi ?? 'pending';
                                    $status_class = 'warning';
                                    $status_text = 'Pending';
                                    if ($status_value == 'acc' || $status_value == 'disetujui') {
                                        $status_class = 'success';
                                        $status_text = 'Disetujui';
                                    } elseif ($status_value == 'ditolak') {
                                        $status_class = 'danger';
                                        $status_text = 'Ditolak';
                                    }
                                    ?>
                                    <span class="badge badge-<?php echo $status_class; ?>"><?php echo $status_text; ?></span>
                                </td>
                            </tr>
                            <tr>
                                <th>Tanggal Pengajuan</th>
                                <td><?php echo isset($perubahan->created_at) ? date('d/m/Y H:i', strtotime($perubahan->created_at)) : '-'; ?></td>
                            </tr>
                        </table>
                    </div>
                </div>

                <!-- Perbandingan Judul -->
                <div class="row mb-4">
                    <div class="col-md-6">
                        <h5 class="mb-3"><i class="fas fa-book"></i> Judul Lama</h5>
                        <div class="alert alert-secondary">
                            <p class="mb-0"><strong><?php echo htmlspecialchars($perubahan->judul_lama ?? $pengajuan->judul_pengajuan ?? '-'); ?></strong></p>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <h5 class="mb-3"><i class="fas fa-book-open"></i> Judul Baru</h5>
                        <div class="alert alert-info">
                            <p class="mb-0"><strong><?php echo htmlspecialchars($perubahan->judul_baru ?? '-'); ?></strong></p>
                        </div>
                    </div>
                </div>

                <!-- Link Google Drive Bukti Perubahan Judul -->
                <?php if (!empty($perubahan->file_ttd_persetujuan)): ?>
                    <div class="row mb-4">
                        <div class="col-12">
                            <h5 class="mb-3"><i class="fas fa-link"></i> Link Google Drive Bukti Perubahan Judul (TTD Persetujuan)</h5>
                            <div class="alert alert-info">
                                <p class="mb-2">
                                    <i class="fas fa-link text-primary"></i> 
                                    <strong>Link Google Drive:</strong>
                                </p>
                                <?php 
                                $this->load->helper('google_drive');
                                echo google_drive_link_html($perubahan->file_ttd_persetujuan, 'Buka Berkas');
                                ?>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>

                <!-- Form Approve/Reject -->
                <?php if (($perubahan->status_approve_sekprodi ?? 'pending') == 'pending'): ?>
                    <div class="card card-primary">
                        <div class="card-header">
                            <h5 class="card-title mb-0"><i class="fas fa-check-circle"></i> Proses Validasi Perubahan Judul Proposal TA</h5>
                        </div>
                        <div class="card-body">
                            <?php echo form_open('Proposal_perubahan_judul_sekprodi/detail/' . $perubahan->id); ?>
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

                                <div id="catatan_section" style="display: none;">
                                    <div class="form-group">
                                        <label for="catatan_sekprodi">Catatan</label>
                                        <textarea name="catatan_sekprodi" id="catatan_sekprodi" class="form-control" rows="3"></textarea>
                                        <small class="form-text text-muted">Catatan untuk mahasiswa (opsional)</small>
                                    </div>
                                </div>

                                <div class="alert alert-warning">
                                    <i class="fas fa-exclamation-triangle"></i>
                                    <strong>Perhatian:</strong> Jika disetujui, judul Proposal TA akan otomatis diperbarui menjadi judul baru.
                                </div>

                                <div class="form-group mt-3">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-save"></i> Proses
                                    </button>
                                    <a href="<?php echo site_url('Proposal_perubahan_judul_sekprodi'); ?>" class="btn btn-secondary">
                                        <i class="fas fa-times"></i> Batal
                                    </a>
                                </div>
                            <?php echo form_close(); ?>
                        </div>
                    </div>
                <?php else: ?>
                    <div class="alert alert-<?php echo ($perubahan->status_approve_sekprodi ?? '') == 'acc' ? 'success' : 'danger'; ?>">
                        <h6>
                            <i class="fas fa-<?php echo ($perubahan->status_approve_sekprodi ?? '') == 'acc' ? 'check-circle' : 'times-circle'; ?>"></i>
                            Perubahan Judul Telah <?php echo ($perubahan->status_approve_sekprodi ?? '') == 'acc' ? 'Disetujui' : 'Ditolak'; ?>
                        </h6>
                        <?php if (!empty($perubahan->catatan_sekprodi)): ?>
                            <p class="mb-0"><strong>Catatan:</strong> <?php echo nl2br(htmlspecialchars($perubahan->catatan_sekprodi)); ?></p>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const actionApprove = document.getElementById('action_approve');
    const actionReject = document.getElementById('action_reject');
    const catatanSection = document.getElementById('catatan_section');
    
    function toggleCatatan() {
        if (actionApprove && actionReject && catatanSection) {
            if (actionReject.checked) {
                catatanSection.style.display = 'block';
            } else {
                catatanSection.style.display = 'none';
            }
        }
    }
    
    if (actionReject) {
        actionReject.addEventListener('change', toggleCatatan);
    }
    if (actionApprove) {
        actionApprove.addEventListener('change', toggleCatatan);
    }
});
</script>

