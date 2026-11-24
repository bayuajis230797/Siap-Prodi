<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Detail Pendaftaran Sidang</h3>
                <div class="card-tools">
                    <a href="<?php echo site_url('Pendaftaran_sidang'); ?>" class="btn btn-secondary btn-sm">
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
                                <th>Judul Skripsi</th>
                                <td><?php echo $pendaftaran->judul_skripsi ?: '-'; ?></td>
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
                                            <th width="15%">Tanggal Upload</th>
                                            <th width="10%" class="text-center">Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php $no = 1; foreach ($berkas_uploaded as $berkas): ?>
                                            <tr>
                                                <td><?php echo $no++; ?></td>
                                                <td>
                                                    <?php echo $berkas->nama_berkas; ?>
                                                    <?php if ($berkas->wajib): ?>
                                                        <span class="badge badge-danger ml-2">Wajib</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <i class="fas fa-file-alt"></i> <?php echo htmlspecialchars($berkas->file_name); ?>
                                                </td>
                                                <td><?php echo date('d/m/Y H:i', strtotime($berkas->uploaded_at)); ?></td>
                                                <td class="text-center">
                                                    <a href="<?php echo site_url('Pendaftaran_sidang/download_berkas/' . $berkas->id); ?>" class="btn btn-sm btn-primary" title="Download">
                                                        <i class="fas fa-download"></i>
                                                    </a>
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
                                    <?php echo form_open('Pendaftaran_sidang/approve/' . $pendaftaran->id); ?>
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label for="id_ketua_penguji">Ketua Penguji <span class="text-danger">*</span></label>
                                                    <select name="id_ketua_penguji" id="id_ketua_penguji" class="form-control" required>
                                                        <option value="">-- Pilih Ketua Penguji --</option>
                                                        <?php foreach ($dosen_list as $dosen): ?>
                                                            <?php if ($dosen->id != $skripsi->id_pembimbing_1 && $dosen->id != $skripsi->id_pembimbing_2): ?>
                                                                <option value="<?php echo $dosen->id; ?>"><?php echo $dosen->nama_dosen . ' (' . $dosen->nidn . ')'; ?></option>
                                                            <?php endif; ?>
                                                        <?php endforeach; ?>
                                                    </select>
                                                    <small class="form-text text-muted">Pembimbing 1 tidak boleh menjadi Ketua Penguji</small>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label for="id_anggota_penguji_1">Anggota Penguji 1 <span class="text-danger">*</span></label>
                                                    <select name="id_anggota_penguji_1" id="id_anggota_penguji_1" class="form-control" required>
                                                        <option value="">-- Pilih Anggota Penguji 1 --</option>
                                                        <?php foreach ($dosen_list as $dosen): ?>
                                                            <?php if ($dosen->id != $skripsi->id_pembimbing_1 && $dosen->id != $skripsi->id_pembimbing_2): ?>
                                                                <option value="<?php echo $dosen->id; ?>"><?php echo $dosen->nama_dosen . ' (' . $dosen->nidn . ')'; ?></option>
                                                            <?php endif; ?>
                                                        <?php endforeach; ?>
                                                    </select>
                                                    <small class="form-text text-muted">Pembimbing 1 tidak boleh menjadi Anggota Penguji 1</small>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label>Anggota Penguji 2</label>
                                                    <input type="text" class="form-control" value="<?php echo $skripsi->pembimbing_2 . ' (' . $skripsi->pembimbing_2_nidn . ')'; ?>" readonly>
                                                    <small class="form-text text-muted">Otomatis: Pembimbing 2</small>
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
                                                    <label for="tanggal_ujian">Tanggal Ujian <span class="text-danger">*</span></label>
                                                    <input type="date" name="tanggal_ujian" id="tanggal_ujian" class="form-control" required>
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
                                    <?php echo form_open('Pendaftaran_sidang/reject/' . $pendaftaran->id); ?>
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
                <?php elseif ($pendaftaran->status === 'disetujui' && !empty($jadwal)): ?>
                    <div class="alert alert-success">
                        <h5><i class="fas fa-check-circle"></i> Jadwal Sidang</h5>
                        <p class="mb-1"><strong>Tanggal:</strong> <?php echo date('d/m/Y', strtotime($jadwal->tanggal_ujian)); ?></p>
                        <p class="mb-1"><strong>Jam:</strong> <?php echo date('H:i', strtotime($jadwal->jam_mulai)) . ' - ' . date('H:i', strtotime($jadwal->jam_selesai)); ?></p>
                        <p class="mb-1"><strong>Ruangan:</strong> <?php echo $jadwal->ruangan; ?></p>
                        <p class="mb-0"><strong>Status:</strong> <span class="badge badge-info"><?php echo ucfirst($jadwal->status); ?></span></p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

