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
                    <i class="fas fa-comments"></i> Detail Bimbingan PKL
                </h3>
                <div class="card-tools">
                    <a href="<?php echo site_url('Bimbingan_pkl'); ?>" class="btn btn-secondary btn-sm">
                        <i class="fas fa-arrow-left"></i> Kembali
                    </a>
                </div>
            </div>
            <div class="card-body">
                <div class="row mb-4">
                    <div class="col-md-6">
                        <h5 class="mb-3"><i class="fas fa-user-graduate"></i> Data Mahasiswa</h5>
                        <table class="table table-sm table-bordered">
                            <tr>
                                <th width="40%">NIM</th>
                                <td><?php echo htmlspecialchars($bimbingan->nim); ?></td>
                            </tr>
                            <tr>
                                <th>Nama</th>
                                <td><?php echo htmlspecialchars($bimbingan->nama_mahasiswa); ?></td>
                            </tr>
                            <tr>
                                <th>Kelas</th>
                                <td><?php echo htmlspecialchars($bimbingan->kelas ?: '-'); ?></td>
                            </tr>
                        </table>
                    </div>
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
                                    <span class="badge badge-<?php echo htmlspecialchars($workflow_status['class'] ?? 'secondary'); ?>">
                                        <?php echo htmlspecialchars($workflow_status['label'] ?? ucfirst($bimbingan->status)); ?>
                                    </span>
                                    <br><small class="text-muted">Topik: <?php echo ucfirst($bimbingan->status); ?></small>
                                </td>
                            </tr>
                            <tr>
                                <th>Last Activity</th>
                                <td><?php echo date('d/m/Y H:i', strtotime($bimbingan->last_activity)); ?></td>
                            </tr>
                        </table>
                    </div>
                </div>

                <?php if (!empty($pengajuan) && (int) ($pengajuan->is_layak_sidang ?? 0) === 1): ?>
                    <div class="alert alert-success mb-4">
                        <i class="fas fa-check-circle"></i>
                        Mahasiswa ini sudah dinyatakan <strong>layak sidang</strong>.
                    </div>
                <?php elseif (!empty($pengajuan) && $bimbingan->status === 'open'): ?>
                    <div class="card card-outline card-success mb-4">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h5 class="mb-1"><i class="fas fa-clipboard-check"></i> Kelayakan Sidang PKL</h5>
                                    <small class="text-muted">Lanjutkan proses penilaian dan nyatakan layak sidang dari satu tombol.</small>
                                </div>
                                <?php if (!empty($can_acc_layak_sidang)): ?>
                                    <a href="<?php echo site_url('Pkl_mahasiswa_dosen/layak_sidang/' . $pengajuan->id); ?>" class="btn btn-success">
                                        <i class="fas fa-check"></i> Input Nilai & Layak Sidang
                                    </a>
                                <?php else: ?>
                                    <button type="button" class="btn btn-secondary" disabled>
                                        <i class="fas fa-lock"></i> Sudah Diproses
                                    </button>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>

                <!-- Form Review -->
                <?php if ($bimbingan->status === 'open'): ?>
                    <div class="card card-primary mb-4">
                        <div class="card-header">
                            <h5 class="card-title mb-0">
                                <i class="fas fa-clipboard-check"></i> Review Bimbingan
                            </h5>
                        </div>
                        <div class="card-body">
                            <?php 
                            $this->load->helper('google_drive');
                            echo form_open_multipart('Bimbingan_pkl/kirim_pesan/' . $bimbingan->id); 
                            ?>
                                <div class="form-group">
                                    <label for="status_revisi">Status Review <span class="text-danger">*</span></label>
                                    <select name="status_revisi" id="status_revisi" class="form-control" required>
                                        <option value="">-- Pilih Status --</option>
                                        <option value="acc">ACC (Disetujui)</option>
                                        <option value="revisi">REVISI (Perlu Perbaikan)</option>
                                    </select>
                                    <?php echo form_error('status_revisi', '<small class="text-danger">', '</small>'); ?>
                                </div>

                                <div class="form-group" id="keterangan_group" style="display: none;">
                                    <label for="keterangan_revisi">Keterangan Revisi <span class="text-danger">*</span></label>
                                    <textarea name="keterangan_revisi" id="keterangan_revisi" class="form-control" rows="5" 
                                              placeholder="Masukkan keterangan revisi untuk mahasiswa..."></textarea>
                                    <?php echo form_error('keterangan_revisi', '<small class="text-danger">', '</small>'); ?>
                                    <small class="form-text text-muted">Berikan keterangan detail tentang bagian yang perlu direvisi.</small>
                                </div>

                                <div class="form-group">
                                    <label for="file_berkas">Berkas (Opsional)</label>
                                    <input type="file" name="file_berkas" id="file_berkas" class="form-control-file"
                                           accept=".pdf,.doc,.docx,.zip,.rar,.jpg,.jpeg,.png">
                                    <small class="form-text text-muted">Diunggah ke Google Drive institusi (template/contoh revisi, dll.).</small>
                                </div>

                                <div class="form-group">
                                    <label for="pesan">Pesan Tambahan (Opsional)</label>
                                    <textarea name="pesan" id="pesan" class="form-control" rows="3" 
                                              placeholder="Tambahkan pesan atau catatan untuk mahasiswa..."></textarea>
                                </div>

                                <div class="form-group">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-paper-plane"></i> Kirim Review
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
                                            <i class="fas fa-user text-primary"></i> <?php echo htmlspecialchars($bimbingan->nama_mahasiswa); ?>
                                        <?php else: ?>
                                            <i class="fas fa-user-tie text-success"></i> Anda (Pembimbing)
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

                <!-- Tombol Aksi -->
                <?php if ($bimbingan->status === 'open'): ?>
                    <div class="mt-4">
                        <a href="<?php echo site_url('Bimbingan_pkl/tutup/' . $bimbingan->id); ?>" 
                           class="btn btn-secondary"
                           onclick="return confirm('Tutup bimbingan ini?');">
                            <i class="fas fa-lock"></i> Tutup Bimbingan
                        </a>
                    </div>
                <?php else: ?>
                    <div class="mt-4">
                        <a href="<?php echo site_url('Bimbingan_pkl/buka/' . $bimbingan->id); ?>" 
                           class="btn btn-primary"
                           onclick="return confirm('Buka kembali bimbingan ini?');">
                            <i class="fas fa-unlock"></i> Buka Kembali Bimbingan
                        </a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const statusSelect = document.getElementById('status_revisi');
    const keteranganGroup = document.getElementById('keterangan_group');
    const keteranganInput = document.getElementById('keterangan_revisi');
    
    if (statusSelect && keteranganGroup) {
        statusSelect.addEventListener('change', function() {
            if (this.value === 'revisi') {
                keteranganGroup.style.display = 'block';
                keteranganInput.setAttribute('required', 'required');
            } else {
                keteranganGroup.style.display = 'none';
                keteranganInput.removeAttribute('required');
            }
        });
    }
});
</script>

