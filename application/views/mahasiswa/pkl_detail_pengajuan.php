<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Detail Pengajuan Judul PKL</h3>
                <div class="card-tools">
                    <a href="<?php echo site_url('Pkl/submit'); ?>" class="btn btn-secondary btn-sm">
                        <i class="fas fa-arrow-left"></i> Kembali
                    </a>
                </div>
            </div>
            <div class="card-body">
                <!-- Informasi Mahasiswa -->
                <div class="row mb-4">
                    <div class="col-md-6">
                        <h5 class="mb-3"><i class="fas fa-user"></i> Informasi Mahasiswa</h5>
                        <table class="table table-sm table-bordered">
                            <tr>
                                <th width="40%">NIM</th>
                                <td><strong><?php echo htmlspecialchars($mahasiswa->nim); ?></strong></td>
                            </tr>
                            <tr>
                                <th>Nama</th>
                                <td><?php echo htmlspecialchars($mahasiswa->nama_mahasiswa); ?></td>
                            </tr>
                            <tr>
                                <th>Kelas</th>
                                <td><span class="badge badge-info"><?php echo htmlspecialchars($mahasiswa->kelas); ?></span></td>
                            </tr>
                        </table>
                    </div>
                    <div class="col-md-6">
                        <h5 class="mb-3"><i class="fas fa-info-circle"></i> Status Pengajuan</h5>
                        <table class="table table-sm table-bordered">
                            <tr>
                                <th width="40%">Status</th>
                                <td>
                                    <?php
                                    $status_class = 'warning';
                                    $status_text = 'Pending';
                                    if ($pengajuan->status_acc_judul === 'acc') {
                                        $status_class = 'success';
                                        $status_text = 'Disetujui';
                                    } elseif ($pengajuan->status_acc_judul === 'revisi') {
                                        $status_class = 'danger';
                                        $status_text = 'Revisi';
                                    }
                                    ?>
                                    <span class="badge badge-<?php echo $status_class; ?>"><?php echo $status_text; ?></span>
                                </td>
                            </tr>
                            <tr>
                                <th>Tanggal Pengajuan</th>
                                <td><?php echo date('d/m/Y H:i', strtotime($pengajuan->created_at)); ?></td>
                            </tr>
                            <?php if ($pengajuan->pembimbing_nama): ?>
                                <tr>
                                    <th>Dosen Pembimbing</th>
                                    <td><?php echo htmlspecialchars($pengajuan->pembimbing_nama); ?></td>
                                </tr>
                            <?php endif; ?>
                        </table>
                    </div>
                </div>

                <!-- Judul PKL -->
                <div class="row mb-4">
                    <div class="col-12">
                        <h5 class="mb-3"><i class="fas fa-book"></i> Judul PKL</h5>
                        <div class="alert alert-info">
                            <p class="mb-0"><strong><?php echo htmlspecialchars($pengajuan->judul_pkl); ?></strong></p>
                        </div>
                    </div>
                </div>

                <?php if (!empty($pengajuan->temuan_masalah)): ?>
                    <div class="row mb-4">
                        <div class="col-12">
                            <h5 class="mb-3"><i class="fas fa-search"></i> Temuan Masalah di Tempat PKL</h5>
                            <div class="border rounded p-3 bg-light"><?php echo nl2br(htmlspecialchars($pengajuan->temuan_masalah)); ?></div>
                        </div>
                    </div>
                <?php endif; ?>

                <?php if (!empty($pengajuan->deskripsi_pengajuan)): ?>
                    <div class="row mb-4">
                        <div class="col-12">
                            <h5 class="mb-3"><i class="fas fa-align-left"></i> Deskripsi (Latar Belakang)</h5>
                            <div class="border rounded p-3 bg-light"><?php echo nl2br(htmlspecialchars($pengajuan->deskripsi_pengajuan)); ?></div>
                        </div>
                    </div>
                <?php endif; ?>

                <!-- File Proposal -->
                <?php if ($pengajuan->file_proposal): ?>
                    <div class="row mb-4">
                        <div class="col-12">
                            <h5 class="mb-3"><i class="fas fa-link"></i> Link Google Drive Proposal</h5>
                            <div class="alert alert-info">
                                <?php 
                                $this->load->helper('google_drive');
                                echo google_drive_link_html($pengajuan->file_proposal, 'Buka Proposal di Google Drive');
                                ?>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>

                <!-- Catatan Revisi -->
                <?php if ($pengajuan->catatan_revisi): ?>
                    <div class="alert alert-warning">
                        <h6><i class="fas fa-exclamation-triangle"></i> Catatan Revisi dari Pembimbing</h6>
                        <p class="mb-0"><?php echo nl2br(htmlspecialchars($pengajuan->catatan_revisi)); ?></p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

