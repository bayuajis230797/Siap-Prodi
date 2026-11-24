<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Pengajuan Judul PKL</h3>
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

                <!-- Tabel Pengajuan Judul - Selalu tampilkan di bagian atas jika ada pengajuan -->
                <?php if (!empty($pengajuan_list)): ?>
                    <div class="mb-4">
                        <h5 class="mb-3"><i class="fas fa-list"></i> Daftar Pengajuan Judul PKL</h5>
                        <div class="table-responsive">
                            <table class="table table-bordered table-striped table-hover">
                                <thead>
                                    <tr>
                                        <th width="5%">No</th>
                                        <th>Judul PKL</th>
                                        <th width="12%" class="text-center">Status</th>
                                        <th width="15%" class="text-center">Tanggal</th>
                                        <th width="20%" class="text-center">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php $no = 1; foreach ($pengajuan_list as $p): ?>
                                        <tr>
                                            <td><?php echo $no++; ?></td>
                                            <td><?php echo htmlspecialchars($p->judul_pkl); ?></td>
                                            <td class="text-center">
                                                <?php
                                                $status_class = 'warning';
                                                $status_text = 'Pending';
                                                if ($p->status_acc_judul === 'acc') {
                                                    $status_class = 'success';
                                                    $status_text = 'Disetujui';
                                                } elseif ($p->status_acc_judul === 'revisi') {
                                                    $status_class = 'danger';
                                                    $status_text = 'Revisi';
                                                }
                                                ?>
                                                <span class="badge badge-<?php echo $status_class; ?>"><?php echo $status_text; ?></span>
                                            </td>
                                            <td class="text-center"><?php echo date('d/m/Y', strtotime($p->created_at)); ?></td>
                                            <td class="text-center">
                                                <a href="<?php echo site_url('Pkl/detail_pengajuan/' . $p->id); ?>" class="btn btn-info btn-sm">
                                                    <i class="fas fa-eye"></i> Detail
                                                </a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                <?php endif; ?>

                <!-- Form Pengajuan Judul Baru - Hanya tampil jika belum ada pengajuan yang sudah di-submit -->
                <?php if ($can_submit && empty($pengajuan_list)): ?>
                    <div class="card card-primary">
                        <div class="card-header">
                            <h5 class="card-title mb-0"><i class="fas fa-plus"></i> Ajukan Judul PKL Baru</h5>
                        </div>
                        <div class="card-body">
                            <?php echo form_open_multipart('Pkl/submit'); ?>
                                <div class="form-group">
                                    <label for="judul_pkl">Judul PKL <span class="text-danger">*</span></label>
                                    <textarea name="judul_pkl" id="judul_pkl" class="form-control" rows="3" required><?php echo set_value('judul_pkl', $pengajuan && $pengajuan->judul_pkl != 'Menunggu Pengajuan Judul' ? $pengajuan->judul_pkl : ''); ?></textarea>
                                    <?php echo form_error('judul_pkl', '<small class="text-danger">', '</small>'); ?>
                                    <small class="form-text text-muted">Masukkan judul PKL yang ingin diajukan (minimal 10 karakter)</small>
                                </div>

                                <div class="form-group">
                                    <label for="temuan_masalah">Temuan Masalah di Tempat PKL <span class="text-danger">*</span></label>
                                    <textarea name="temuan_masalah" id="temuan_masalah" class="form-control" rows="4" required placeholder="Jelaskan temuan atau permasalahan yang diamati di tempat PKL..."><?php echo set_value('temuan_masalah', isset($pengajuan->temuan_masalah) ? $pengajuan->temuan_masalah : ''); ?></textarea>
                                    <?php echo form_error('temuan_masalah', '<small class="text-danger">', '</small>'); ?>
                                    <small class="form-text text-muted">Uraikan masalah/temuan di lapangan yang menjadi dasar pengajuan judul (minimal 20 karakter)</small>
                                </div>

                                <div class="form-group">
                                    <label for="deskripsi_pengajuan">Deskripsi (Latar Belakang Pengajuan Judul) <span class="text-danger">*</span></label>
                                    <textarea name="deskripsi_pengajuan" id="deskripsi_pengajuan" class="form-control" rows="5" required placeholder="Jelaskan latar belakang, alasan, dan relevansi judul yang diajukan..."><?php echo set_value('deskripsi_pengajuan', isset($pengajuan->deskripsi_pengajuan) ? $pengajuan->deskripsi_pengajuan : ''); ?></textarea>
                                    <?php echo form_error('deskripsi_pengajuan', '<small class="text-danger">', '</small>'); ?>
                                    <small class="form-text text-muted">Latar belakang pengajuan judul untuk direview dosen pembimbing (minimal 30 karakter)</small>
                                </div>

                                <div class="form-group">
                                    <label for="file_proposal">File Proposal (Opsional)</label>
                                    <?php $this->load->helper('google_drive'); ?>
                                    <input type="file" name="file_proposal" id="file_proposal" class="form-control-file"
                                           accept=".pdf,.doc,.docx,.zip,.rar">
                                    <?php if ($pengajuan && $pengajuan->file_proposal && is_valid_google_drive_link($pengajuan->file_proposal)): ?>
                                        <div class="alert alert-info mt-2">
                                            <i class="fas fa-check-circle"></i> Proposal saat ini:
                                            <?php echo google_drive_link_html($pengajuan->file_proposal, 'Buka Proposal'); ?>
                                        </div>
                                    <?php endif; ?>
                                    <small class="form-text text-muted">Diunggah ke Google Drive institusi (hubungkan di Profil). Kosongkan jika tidak mengganti file.</small>
                                </div>

                                <div class="form-group mt-3">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-paper-plane"></i> Kirim Pengajuan
                                    </button>
                                    <a href="<?php echo site_url('Pkl'); ?>" class="btn btn-secondary">
                                        <i class="fas fa-times"></i> Batal
                                    </a>
                                </div>
                            <?php echo form_close(); ?>
                        </div>
                    </div>
                <?php elseif (!empty($pengajuan_list)): ?>
                    <!-- Jika sudah ada pengajuan, tampilkan pesan info di bawah tabel -->
                    <?php if ($pengajuan && $pengajuan->status_acc_judul === 'revisi'): ?>
                        <div class="alert alert-warning">
                            <i class="fas fa-exclamation-triangle"></i> 
                            Judul PKL Anda memerlukan revisi. Silakan ajukan perubahan judul.
                            <a href="<?php echo site_url('Pkl/perubahan_judul'); ?>" class="btn btn-warning btn-sm ml-2">
                                <i class="fas fa-edit"></i> Ajukan Perubahan Judul
                            </a>
                        </div>
                    <?php endif; ?>
                <?php else: ?>
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle"></i> 
                        Tidak dapat mengajukan judul baru saat ini.
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>



