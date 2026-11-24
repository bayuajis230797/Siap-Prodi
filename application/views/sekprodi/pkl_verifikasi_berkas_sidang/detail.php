<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Detail Verifikasi Berkas Sidang PKL</h3>
                <div class="card-tools">
                    <a href="<?php echo site_url('Pkl_verifikasi_berkas_sidang'); ?>" class="btn btn-secondary btn-sm">
                        <i class="fas fa-arrow-left"></i> Kembali
                    </a>
                </div>
            </div>
            <div class="card-body">
                <!-- Informasi Mahasiswa -->
                <div class="row mb-4">
                    <div class="col-md-6">
                        <h5 class="mb-3"><i class="fas fa-user"></i> Informasi Mahasiswa</h5>
                        <table class="table table-sm">
                            <tr>
                                <th width="40%">NIM</th>
                                <td><strong><?php echo $pendaftaran->nim; ?></strong></td>
                            </tr>
                            <tr>
                                <th>Nama Mahasiswa</th>
                                <td><?php echo $pendaftaran->nama_mahasiswa; ?></td>
                            </tr>
                            <tr>
                                <th>Kelas</th>
                                <td><span class="badge badge-info"><?php echo $pendaftaran->kelas; ?></span></td>
                            </tr>
                            <tr>
                                <th>Judul PKL</th>
                                <td><?php echo $pendaftaran->judul_pkl ?: '-'; ?></td>
                            </tr>
                            <tr>
                                <th>Dosen Pembimbing</th>
                                <td>
                                    <?php if ($dosen_pembimbing): ?>
                                        <strong><?php echo htmlspecialchars($dosen_pembimbing->nama_dosen); ?></strong>
                                        <?php if ($dosen_pembimbing->nidn): ?>
                                            <br><small class="text-muted">NIDN: <?php echo htmlspecialchars($dosen_pembimbing->nidn); ?></small>
                                        <?php endif; ?>
                                    <?php else: ?>
                                        <span class="text-muted">-</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        </table>
                    </div>
                    <div class="col-md-6">
                        <h5 class="mb-3"><i class="fas fa-info-circle"></i> Status Pendaftaran</h5>
                        <table class="table table-sm">
                            <tr>
                                <th width="40%">Status</th>
                                <td>
                                    <?php
                                    $status_class = 'secondary';
                                    switch ($pendaftaran->status) {
                                        case 'menunggu':
                                            $status_class = 'warning';
                                            break;
                                        case 'disetujui':
                                            $status_class = 'success';
                                            break;
                                        case 'ditolak':
                                            $status_class = 'danger';
                                            break;
                                        case 'selesai':
                                            $status_class = 'info';
                                            break;
                                    }
                                    ?>
                                    <span class="badge badge-<?php echo $status_class; ?>"><?php echo ucfirst($pendaftaran->status); ?></span>
                                </td>
                            </tr>
                            <tr>
                                <th>Tanggal Pengajuan</th>
                                <td><?php echo date('d/m/Y H:i', strtotime($pendaftaran->created_at)); ?></td>
                            </tr>
                            <?php if ($pendaftaran->catatan_sekprodi): ?>
                                <tr>
                                    <th>Catatan</th>
                                    <td><?php echo nl2br(htmlspecialchars($pendaftaran->catatan_sekprodi)); ?></td>
                                </tr>
                            <?php endif; ?>
                        </table>
                    </div>
                </div>

                <!-- Berkas yang Diupload -->
                <div class="row mb-4">
                    <div class="col-12">
                        <h5 class="mb-3"><i class="fas fa-file-alt"></i> Berkas yang Diupload</h5>
                        <?php if (empty($berkas_uploaded)): ?>
                            <div class="alert alert-warning">
                                <i class="fas fa-exclamation-triangle"></i> Belum ada berkas yang diupload.
                            </div>
                        <?php else: ?>
                            <div class="table-responsive">
                                <table class="table table-bordered">
                                    <thead>
                                        <tr>
                                            <th width="5%">No</th>
                                            <th>Nama Berkas</th>
                                            <th>File</th>
                                            <th width="13%" class="text-center">Validasi Link</th>
                                            <th width="15%">Tanggal Upload</th>
                                            <th width="10%" class="text-center">Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php $no = 1; foreach ($berkas_uploaded as $berkas): ?>
                                            <tr>
                                                <td><?php echo $no++; ?></td>
                                                <td>
                                                    <?php echo htmlspecialchars($berkas->nama_berkas); ?>
                                                    <?php if ($berkas->wajib): ?>
                                                        <span class="badge badge-danger ml-2">Wajib</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <?php 
                                                    // Cek apakah file_path adalah link Google Drive
                                                    $is_google_drive_link = (strpos($berkas->file_path, 'http') === 0);
                                                    if ($is_google_drive_link): 
                                                    ?>
                                                        <i class="fab fa-google-drive text-primary"></i> 
                                                        <a href="<?php echo htmlspecialchars($berkas->file_path); ?>" target="_blank" class="text-primary">
                                                            <strong><?php echo htmlspecialchars($berkas->file_name); ?></strong>
                                                        </a>
                                                        <br><small class="text-muted"><?php echo htmlspecialchars($berkas->file_path); ?></small>
                                                    <?php else: ?>
                                                        <i class="fas fa-file-alt"></i> <?php echo htmlspecialchars($berkas->file_name); ?>
                                                    <?php endif; ?>
                                                </td>
                                                <td class="text-center">
                                                    <?php if ($is_google_drive_link): ?>
                                                        <?php if (is_valid_google_drive_link($berkas->file_path)): ?>
                                                            <span class="badge badge-success">Valid</span>
                                                        <?php else: ?>
                                                            <span class="badge badge-danger">Tidak Valid</span>
                                                        <?php endif; ?>
                                                    <?php else: ?>
                                                        <span class="badge badge-secondary">Legacy File</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td><?php echo date('d/m/Y H:i', strtotime($berkas->uploaded_at)); ?></td>
                                                <td class="text-center">
                                                    <?php if ($is_google_drive_link): ?>
                                                        <a href="<?php echo htmlspecialchars($berkas->file_path); ?>" target="_blank" class="btn btn-sm btn-success" title="Buka Link Google Drive">
                                                            <i class="fab fa-google-drive"></i> Buka
                                                        </a>
                                                    <?php else: ?>
                                                        <a href="<?php echo site_url('Pkl_verifikasi_berkas_sidang/download_berkas/' . $berkas->id); ?>" class="btn btn-sm btn-primary" title="Download">
                                                            <i class="fas fa-download"></i>
                                                        </a>
                                                    <?php endif; ?>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Form Approve/Reject -->
                <?php if ($pendaftaran->status === 'menunggu'): ?>
                    <div class="row">
                        <div class="col-12">
                            <div class="card card-outline card-success">
                                <div class="card-header">
                                    <h5 class="card-title"><i class="fas fa-check-circle"></i> Setujui Pendaftaran & Buat Jadwal Sidang</h5>
                                </div>
                                <div class="card-body">
                                    <?php echo form_open('Pkl_verifikasi_berkas_sidang/approve/' . $pendaftaran->id); ?>
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label for="id_penguji">Dosen Penguji <span class="text-danger">*</span></label>
                                                    <select name="id_penguji" id="id_penguji" class="form-control" required>
                                                        <option value="">-- Pilih Dosen Penguji --</option>
                                                        <?php foreach ($dosen_list as $dosen): ?>
                                                            <?php if ($pengajuan && $dosen->id != $pengajuan->id_pembimbing): ?>
                                                                <option value="<?php echo $dosen->id; ?>"><?php echo $dosen->nama_dosen . ' (' . $dosen->nidn . ')'; ?></option>
                                                            <?php elseif (!$pengajuan): ?>
                                                                <option value="<?php echo $dosen->id; ?>"><?php echo $dosen->nama_dosen . ' (' . $dosen->nidn . ')'; ?></option>
                                                            <?php endif; ?>
                                                        <?php endforeach; ?>
                                                    </select>
                                                    <small class="form-text text-muted">Pembimbing tidak boleh menjadi Penguji</small>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label for="ruangan">Ruangan <span class="text-danger">*</span></label>
                                                    <input type="text" name="ruangan" id="ruangan" class="form-control" required>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-md-4">
                                                <div class="form-group">
                                                    <label for="tanggal">Tanggal Sidang <span class="text-danger">*</span></label>
                                                    <input type="date" name="tanggal" id="tanggal" class="form-control" required>
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <div class="form-group">
                                                    <label for="jam_mulai">Jam Mulai <span class="text-danger">*</span></label>
                                                    <input type="time" name="jam_mulai" id="jam_mulai" class="form-control" required>
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <div class="form-group">
                                                    <label for="jam_selesai">Jam Selesai <span class="text-danger">*</span></label>
                                                    <input type="time" name="jam_selesai" id="jam_selesai" class="form-control" required>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="form-group">
                                            <label for="catatan_sekprodi">Catatan</label>
                                            <textarea name="catatan_sekprodi" id="catatan_sekprodi" class="form-control" rows="3"></textarea>
                                        </div>
                                        <div class="form-group">
                                            <button type="submit" class="btn btn-success">
                                                <i class="fas fa-check"></i> Setujui & Buat Jadwal
                                            </button>
                                        </div>
                                    <?php echo form_close(); ?>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row mt-3">
                        <div class="col-12">
                            <div class="card card-outline card-danger">
                                <div class="card-header">
                                    <h5 class="card-title"><i class="fas fa-times-circle"></i> Tolak Pendaftaran</h5>
                                </div>
                                <div class="card-body">
                                    <?php echo form_open('Pkl_verifikasi_berkas_sidang/reject/' . $pendaftaran->id); ?>
                                        <div class="form-group">
                                            <label for="catatan_sekprodi_reject">Alasan Penolakan <span class="text-danger">*</span></label>
                                            <textarea name="catatan_sekprodi" id="catatan_sekprodi_reject" class="form-control" rows="3" required></textarea>
                                        </div>
                                        <div class="form-group">
                                            <button type="submit" class="btn btn-danger" onclick="return confirm('Apakah Anda yakin ingin menolak pendaftaran ini?');">
                                                <i class="fas fa-times"></i> Tolak Pendaftaran
                                            </button>
                                        </div>
                                    <?php echo form_close(); ?>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php elseif ($pendaftaran->status === 'disetujui' && $jadwal && is_object($jadwal)): ?>
                    <div class="alert alert-success">
                        <h5><i class="fas fa-check-circle"></i> Jadwal Sidang</h5>
                        <p class="mb-1"><strong>Tanggal:</strong> <?php echo $jadwal->tanggal ? date('d/m/Y', strtotime($jadwal->tanggal)) : '-'; ?></p>
                        <p class="mb-1"><strong>Jam:</strong> <?php echo ($jadwal->jam_mulai && $jadwal->jam_selesai) ? date('H:i', strtotime($jadwal->jam_mulai)) . ' - ' . date('H:i', strtotime($jadwal->jam_selesai)) : '-'; ?></p>
                        <p class="mb-1"><strong>Ruangan:</strong> <?php echo isset($jadwal->ruangan) ? htmlspecialchars($jadwal->ruangan) : '-'; ?></p>
                        <p class="mb-0"><strong>Penguji:</strong> <?php echo isset($jadwal->penguji_nama) ? htmlspecialchars($jadwal->penguji_nama) : '-'; ?></p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

