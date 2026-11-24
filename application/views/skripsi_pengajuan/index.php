<div class="row">
    <div class="col-12">
        <div class="card card-primary">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-file-alt"></i> Pengajuan Judul Skripsi
                </h3>
            </div>
            <div class="card-body">
                <?php if ($this->session->flashdata('success')): ?>
                    <div class="alert alert-success alert-dismissible">
                        <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
                        <?php echo $this->session->flashdata('success'); ?>
                    </div>
                <?php endif; ?>

                <?php if ($this->session->flashdata('error')): ?>
                    <div class="alert alert-danger alert-dismissible">
                        <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
                        <?php echo $this->session->flashdata('error'); ?>
                    </div>
                <?php endif; ?>

                <?php if ($tahun_aktif): ?>
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle"></i>
                        <strong>Tahun Akademik:</strong> <?php echo $tahun_aktif->tahun_akademik . ' ' . ucfirst($tahun_aktif->semester); ?>
                    </div>
                <?php endif; ?>

                <?php if ($pengajuan && $pengajuan->status != 'ditolak'): ?>
                    <div class="card card-outline card-info mb-3">
                        <div class="card-header">
                            <h5 class="card-title mb-0"><i class="fas fa-info-circle"></i> Status Pengajuan</h5>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <p><strong>Judul Diajukan:</strong><br>
                                    <?php 
                                    // Judul Diajukan = judul yang masih diajukan (belum approve sekprodi).
                                    $judul_tampil = !empty($judul_diajukan) ? $judul_diajukan : $pengajuan->judul_pengajuan;
                                    echo htmlspecialchars($judul_tampil); 
                                    ?></p>
                                    
                                    <p><strong>Status Pengajuan:</strong> 
                            <?php
                            $status_class = 'secondary';
                            switch ($pengajuan->status) {
                                case 'pending':
                                    $status_class = 'warning';
                                    break;
                                case 'disetujui':
                                    $status_class = 'success';
                                    break;
                                case 'ditolak':
                                    $status_class = 'danger';
                                    break;
                            }
                            ?>
                            <span class="badge badge-<?php echo $status_class; ?>">
                                <?php echo ucfirst($pengajuan->status); ?>
                            </span>
                        </p>
                                    
                        <?php if (!empty($judul_disetujui_terbaru)): ?>
                                        <p><strong>Judul Disetujui (Terbaru):</strong><br>
                                        <span class="text-success"><strong><?php echo htmlspecialchars($judul_disetujui_terbaru); ?></strong></span></p>
                                    <?php endif; ?>
                                    
                                    <?php if ($pengajuan->catatan_sekprodi): ?>
                                        <p><strong>Catatan Sekprodi:</strong><br>
                                        <?php echo nl2br(htmlspecialchars($pengajuan->catatan_sekprodi)); ?></p>
                                    <?php endif; ?>
                                </div>
                                
                                <div class="col-md-6">
                                    <h6 class="font-weight-bold mb-3">Plot Pembimbing & Status ACC:</h6>
                                    
                                    <?php
                                    // Ambil pembimbing dari pengajuan atau dari mahasiswa_skripsi
                                    $pembimbing_1_nama = null;
                                    $pembimbing_2_nama = null;
                                    $pembimbing_1_nidn = null;
                                    $pembimbing_2_nidn = null;
                                    
                                    // Cek dari pengajuan (pembimbing final)
                                    if (!empty($pengajuan->pembimbing_1_nama)) {
                                        $pembimbing_1_nama = $pengajuan->pembimbing_1_nama;
                                        $pembimbing_1_nidn = isset($pengajuan->pembimbing_1_nidn) ? $pengajuan->pembimbing_1_nidn : null;
                                    } elseif (!empty($skripsi->nama_pembimbing_1)) {
                                        $pembimbing_1_nama = $skripsi->nama_pembimbing_1;
                                    }
                                    
                                    if (!empty($pengajuan->pembimbing_2_nama)) {
                                        $pembimbing_2_nama = $pengajuan->pembimbing_2_nama;
                                        $pembimbing_2_nidn = isset($pengajuan->pembimbing_2_nidn) ? $pengajuan->pembimbing_2_nidn : null;
                                    } elseif (!empty($skripsi->nama_pembimbing_2)) {
                                        $pembimbing_2_nama = $skripsi->nama_pembimbing_2;
                                    }
                                    ?>
                                    
                                    <?php if ($pembimbing_1_nama): ?>
                                    <div class="mb-3">
                                        <p class="mb-1">
                                            <strong>Pembimbing 1:</strong> 
                                            <?php echo htmlspecialchars($pembimbing_1_nama); ?>
                                            <?php if ($pembimbing_1_nidn): ?>
                                                <small class="text-muted">(NIDN: <?php echo htmlspecialchars($pembimbing_1_nidn); ?>)</small>
                                            <?php endif; ?>
                                        </p>
                                        <p class="mb-0">
                                            <?php
                                            $status_p1 = isset($pengajuan->status_acc_judul_p1) ? $pengajuan->status_acc_judul_p1 : 'pending';
                                            $p1_class = 'secondary';
                                            $p1_text = 'Menunggu';
                                            
                                            switch ($status_p1) {
                                                case 'acc':
                                                    $p1_class = 'success';
                                                    $p1_text = 'Disetujui (ACC)';
                                                    break;
                                                case 'revisi':
                                                    $p1_class = 'warning';
                                                    $p1_text = 'Perlu Revisi';
                                                    break;
                                                case 'pending':
                                                default:
                                                    $p1_class = 'secondary';
                                                    $p1_text = 'Menunggu Review';
                                                    break;
                                            }
                                            ?>
                                            <span class="badge badge-<?php echo $p1_class; ?>"><?php echo $p1_text; ?></span>
                                        </p>
                                        <?php if ($status_p1 == 'revisi' && !empty($pengajuan->catatan_revisi_p1)): ?>
                                            <small class="text-muted d-block mt-1">
                                                <strong>Catatan:</strong> <?php echo nl2br(htmlspecialchars($pengajuan->catatan_revisi_p1)); ?>
                                            </small>
                                        <?php endif; ?>
                                    </div>
                                    <?php else: ?>
                                    <div class="mb-3">
                                        <p class="mb-1"><strong>Pembimbing 1:</strong> <span class="text-muted">Belum diplot</span></p>
                                    </div>
                                    <?php endif; ?>
                                    
                                    <?php if ($pembimbing_2_nama): ?>
                                    <div class="mb-3">
                                        <p class="mb-1">
                                            <strong>Pembimbing 2:</strong> 
                                            <?php echo htmlspecialchars($pembimbing_2_nama); ?>
                                            <?php if ($pembimbing_2_nidn): ?>
                                                <small class="text-muted">(NIDN: <?php echo htmlspecialchars($pembimbing_2_nidn); ?>)</small>
                                            <?php endif; ?>
                                        </p>
                                        <p class="mb-0">
                                            <?php
                                            $status_p2 = isset($pengajuan->status_acc_judul_p2) ? $pengajuan->status_acc_judul_p2 : 'pending';
                                            $p2_class = 'secondary';
                                            $p2_text = 'Menunggu';
                                            
                                            switch ($status_p2) {
                                                case 'acc':
                                                    $p2_class = 'success';
                                                    $p2_text = 'Disetujui (ACC)';
                                                    break;
                                                case 'revisi':
                                                    $p2_class = 'warning';
                                                    $p2_text = 'Perlu Revisi';
                                                    break;
                                                case 'pending':
                                                default:
                                                    $p2_class = 'secondary';
                                                    $p2_text = 'Menunggu Review';
                                                    break;
                                            }
                                            ?>
                                            <span class="badge badge-<?php echo $p2_class; ?>"><?php echo $p2_text; ?></span>
                                        </p>
                                        <?php if ($status_p2 == 'revisi' && !empty($pengajuan->catatan_revisi_p2)): ?>
                                            <small class="text-muted d-block mt-1">
                                                <strong>Catatan:</strong> <?php echo nl2br(htmlspecialchars($pengajuan->catatan_revisi_p2)); ?>
                                            </small>
                                        <?php endif; ?>
                                    </div>
                                    <?php else: ?>
                                    <div class="mb-3">
                                        <p class="mb-1"><strong>Pembimbing 2:</strong> <span class="text-muted">Belum diplot</span></p>
                                    </div>
                                    <?php endif; ?>
                                    
                                    <?php if ($pengajuan->status == 'disetujui'): ?>
                                    
                                    <?php
                                    // Cek apakah kedua pembimbing sudah ACC
                                    $status_p1 = isset($pengajuan->status_acc_judul_p1) ? $pengajuan->status_acc_judul_p1 : 'pending';
                                    $status_p2 = isset($pengajuan->status_acc_judul_p2) ? $pengajuan->status_acc_judul_p2 : 'pending';
                                    $both_acc = ($status_p1 == 'acc' && $status_p2 == 'acc');
                                    ?>
                                    
                                    <?php if ($both_acc): ?>
                                        <div class="alert alert-success mt-3">
                                            <i class="fas fa-check-circle"></i> 
                                            <strong>Selamat!</strong> Kedua pembimbing telah menyetujui judul Anda.
                                        </div>
                                    <?php elseif ($status_p1 == 'revisi' || $status_p2 == 'revisi'): ?>
                                        <div class="alert alert-warning mt-3">
                                            <i class="fas fa-exclamation-triangle"></i> 
                                            <strong>Perhatian!</strong> Ada catatan revisi dari pembimbing. 
                                            Silakan ajukan judul revisi yang sudah disetujui pembimbing atau upload berkas persetujuan judul.
                                        </div>
                                        
                                        <?php
                                        $status_approve_revisi = isset($pengajuan->status_approve_revisi) ? $pengajuan->status_approve_revisi : 'pending';
                                        ?>
                                        
                                        <?php if ($status_approve_revisi == 'pending'): ?>
                                            <!-- Form Pengajuan Judul Revisi dan Upload File -->
                                            <?php if (empty($pengajuan->judul_revisi) && empty($pengajuan->file_persetujuan_judul)): ?>
                                                <div class="card card-outline card-warning mt-3">
                                                    <div class="card-header">
                                                        <h6 class="card-title mb-0"><i class="fas fa-edit"></i> Ajukan Perubahan Judul dan Upload Bukti ACC</h6>
                                                    </div>
                                                    <div class="card-body">
                                                        <?php echo form_open_multipart('Skripsi_pengajuan/submit_judul_revisi_dan_file'); ?>
                                                        <div class="form-group">
                                                            <label for="judul_revisi">Judul Revisi <span class="text-danger">*</span></label>
                                                            <input type="text" name="judul_revisi" id="judul_revisi" class="form-control" 
                                                                   value="<?php echo set_value('judul_revisi'); ?>" required>
                                                            <small class="form-text text-muted">Masukkan judul revisi yang sudah disetujui oleh pembimbing.</small>
                                                        </div>
                                                        <div class="form-group">
                                                            <label for="file_persetujuan_judul">Berkas Bukti ACC Pembimbing <span class="text-danger">*</span></label>
                                                            <input type="file" name="file_persetujuan_judul" id="file_persetujuan_judul" class="form-control-file"
                                                                   accept=".pdf,.doc,.docx,.jpg,.jpeg,.png" required>
                                                            <small class="form-text text-muted">
                                                                Diunggah ke Google Drive institusi. Berkas persetujuan judul yang sudah ditandatangani pembimbing 1 dan 2. PDF/DOC/JPG tanpa batas form.
                                                            </small>
                                                        </div>
                                                        <button type="submit" class="btn btn-warning btn-block">
                                                            <i class="fas fa-paper-plane"></i> Kirim Pengajuan Perubahan Judul
                                                        </button>
                                                        <?php echo form_close(); ?>
                                                    </div>
                                                </div>
                                            <?php else: ?>
                                                <?php if (!empty($pengajuan->judul_revisi)): ?>
                                                    <div class="alert alert-info mt-3">
                                                        <i class="fas fa-file-alt"></i> 
                                                        <strong>Judul Revisi yang Diajukan:</strong> <?php echo htmlspecialchars($pengajuan->judul_revisi); ?>
                                                    </div>
                                                <?php endif; ?>
                                                
                                                <?php if (!empty($pengajuan->file_persetujuan_judul)): ?>
                                                    <div class="alert alert-info mt-3">
                                                        <i class="fas fa-link"></i> 
                                                        <strong>Link Google Drive sudah diupload:</strong> 
                                                        <?php 
                                                        $this->load->helper('google_drive');
                                                        echo google_drive_link_html($pengajuan->file_persetujuan_judul, 'Buka Berkas');
                                                        ?>
                                                    </div>
                                                <?php endif; ?>
                                                
                                                <div class="alert alert-warning mt-3">
                                                    <i class="fas fa-clock"></i> 
                                                    <strong>Status:</strong> Menunggu validasi dari sekprodi.
                                                </div>
                                            <?php endif; ?>
                                        <?php elseif ($status_approve_revisi == 'disetujui'): ?>
                                            <div class="alert alert-success mt-3">
                                                <i class="fas fa-check-circle"></i> 
                                                <strong>Judul revisi disetujui oleh sekprodi.</strong>
                                                <?php if ($pengajuan->judul_revisi): ?>
                                                    <br><strong>Judul yang berlaku sekarang:</strong> <?php echo htmlspecialchars($pengajuan->judul_revisi); ?>
                        <?php endif; ?>
                                                <br>Bimbingan skripsi sekarang aktif.
                                            </div>
                                        <?php elseif ($status_approve_revisi == 'ditolak'): ?>
                                            <div class="alert alert-danger mt-3">
                                                <i class="fas fa-times-circle"></i> 
                                                <strong>Judul revisi ditolak oleh sekprodi.</strong>
                        <?php if ($pengajuan->catatan_sekprodi): ?>
                                                    <br><strong>Catatan:</strong> <?php echo htmlspecialchars($pengajuan->catatan_sekprodi); ?>
                                                <?php endif; ?>
                                                <br><small>Silakan ajukan judul revisi baru atau upload ulang berkas yang benar.</small>
                                            </div>
                                        <?php endif; ?>
                                    <?php else: ?>
                                        <div class="alert alert-info mt-3">
                                            <i class="fas fa-clock"></i> 
                                            Menunggu persetujuan dari pembimbing 1 dan pembimbing 2.
                                        </div>
                                    <?php endif; ?>
                                </div>
                        <?php endif; ?>
                            </div>
                        </div>
                    </div>
                <?php else: ?>
                    <?php echo form_open_multipart('Skripsi_pengajuan/submit', array('method' => 'post')); ?>
                    <div class="form-group">
                        <label for="judul_pengajuan">Judul Skripsi <span class="text-danger">*</span></label>
                        <input type="text" name="judul_pengajuan" id="judul_pengajuan" class="form-control" 
                               value="<?php echo set_value('judul_pengajuan'); ?>" required>
                        <small class="form-text text-muted">Masukkan judul skripsi yang akan diajukan.</small>
                    </div>

                    <div class="form-group">
                        <label for="deskripsi">Deskripsi</label>
                        <textarea name="deskripsi" id="deskripsi" class="form-control" rows="5"><?php echo set_value('deskripsi'); ?></textarea>
                        <small class="form-text text-muted">Jelaskan secara singkat tentang skripsi yang akan dikerjakan.</small>
                    </div>

                    <div class="form-group">
                        <label for="alasan">Alasan Memilih Judul</label>
                        <textarea name="alasan" id="alasan" class="form-control" rows="3"><?php echo set_value('alasan'); ?></textarea>
                        <small class="form-text text-muted">Jelaskan alasan memilih judul ini (opsional).</small>
                    </div>

                    <div class="form-group">
                        <label for="file_proposal">Berkas Proposal (opsional)</label>
                        <input type="file" name="file_proposal" id="file_proposal" class="form-control-file"
                               accept=".pdf,.doc,.docx,.zip,.rar">
                        <small class="form-text text-muted">
                            File disimpan ke Google Drive institusi (dikelola admin). Tidak ada batas ukuran di form (mengikuti batas server PHP).
                        </small>
                    </div>

                    <div class="card-footer">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-paper-plane"></i> Kirim Pengajuan
                        </button>
                    </div>
                    <?php echo form_close(); ?>
                <?php endif; ?>
                
            </div>
        </div>
    </div>
</div>



