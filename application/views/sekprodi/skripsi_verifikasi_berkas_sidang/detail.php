<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Detail Verifikasi Berkas Sidang Skripsi</h3>
                <div class="card-tools">
                    <a href="<?php echo site_url('Skripsi_verifikasi_berkas_sidang'); ?>" class="btn btn-secondary btn-sm">
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
                                <td><strong><?php echo isset($pendaftaran->nim) ? $pendaftaran->nim : '-'; ?></strong></td>
                            </tr>
                            <tr>
                                <th>Nama Mahasiswa</th>
                                <td><?php echo isset($pendaftaran->nama_mahasiswa) ? $pendaftaran->nama_mahasiswa : '-'; ?></td>
                            </tr>
                            <tr>
                                <th>Kelas</th>
                                <td><span class="badge badge-info"><?php echo isset($pendaftaran->kelas) ? $pendaftaran->kelas : '-'; ?></span></td>
                            </tr>
                            <tr>
                                <th>Judul Skripsi</th>
                                <td>
                                    <?php 
                                    $judul = '-';
                                    // Prioritas: judul_skripsi_terbaru > judul_skripsi dari mahasiswa_skripsi
                                    if (isset($judul_skripsi_terbaru) && !empty($judul_skripsi_terbaru)) {
                                        $judul = $judul_skripsi_terbaru;
                                    } elseif (isset($skripsi) && isset($skripsi->judul_skripsi) && !empty($skripsi->judul_skripsi)) {
                                        $judul = $skripsi->judul_skripsi;
                                    }
                                    echo htmlspecialchars($judul);
                                    ?>
                                </td>
                            </tr>
                            <tr>
                                <th>Pembimbing 1</th>
                                <td>
                                    <?php 
                                    if (isset($skripsi) && isset($skripsi->nama_pembimbing_1) && !empty($skripsi->nama_pembimbing_1)) {
                                        echo htmlspecialchars($skripsi->nama_pembimbing_1);
                                    } else {
                                        echo '-';
                                    }
                                    ?>
                                </td>
                            </tr>
                            <tr>
                                <th>Pembimbing 2</th>
                                <td>
                                    <?php 
                                    if (isset($skripsi) && isset($skripsi->nama_pembimbing_2) && !empty($skripsi->nama_pembimbing_2)) {
                                        echo htmlspecialchars($skripsi->nama_pembimbing_2);
                                    } else {
                                        echo '-';
                                    }
                                    ?>
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
                                    $status = isset($pendaftaran->status) ? $pendaftaran->status : 'menunggu';
                                    switch ($status) {
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
                                    <span class="badge badge-<?php echo $status_class; ?>"><?php echo ucfirst($status); ?></span>
                                </td>
                            </tr>
                            <tr>
                                <th>Tanggal Pengajuan</th>
                                <td><?php echo isset($pendaftaran->created_at) ? date('d/m/Y H:i', strtotime($pendaftaran->created_at)) : '-'; ?></td>
                            </tr>
                            <?php if (isset($pendaftaran->catatan_sekprodi) && $pendaftaran->catatan_sekprodi): ?>
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
                                                    <?php echo htmlspecialchars($berkas->nama_berkas); ?>
                                                    <?php if (isset($berkas->wajib) && $berkas->wajib): ?>
                                                        <span class="badge badge-danger ml-2">Wajib</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <?php 
                                                    $this->load->helper('google_drive');
                                                    echo google_drive_link_html($berkas->file_path, htmlspecialchars($berkas->file_name));
                                                    ?>
                                                </td>
                                                <td><?php echo isset($berkas->uploaded_at) ? date('d/m/Y H:i', strtotime($berkas->uploaded_at)) : '-'; ?></td>
                                                <td class="text-center">
                                                    <?php 
                                                    $this->load->helper('google_drive');
                                                    echo google_drive_link_html($berkas->file_path, 'Buka');
                                                    ?>
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
                <?php if (isset($pendaftaran->status) && $pendaftaran->status === 'menunggu'): ?>
                    <div class="row">
                        <div class="col-12">
                            <div class="card card-outline card-success">
                                <div class="card-header">
                                    <h5 class="card-title"><i class="fas fa-check-circle"></i> 
                                        <?php if (isset($is_tahap_jadwal) && $is_tahap_jadwal): ?>
                                            Setujui Pendaftaran & Tetapkan Jadwal
                                        <?php else: ?>
                                            Setujui Pendaftaran & Tetapkan Penguji
                                        <?php endif; ?>
                                    </h5>
                                </div>
                                <div class="card-body">
                                    <?php 
                                    $bisa_approve = true;
                                    if (!isset($is_tahap_jadwal) || !$is_tahap_jadwal) {
                                        if (!isset($jadwal) || !$jadwal) $bisa_approve = false;
                                    }
                                    if ($bisa_approve): 
                                    echo form_open('Skripsi_verifikasi_berkas_sidang/approve/' . $pendaftaran->id); ?>
                                        <?php if (isset($is_tahap_jadwal) && $is_tahap_jadwal): ?>
                                        <!-- TAHAP JADWAL: Hanya ruangan, tanggal, jam -->
                                        <div class="alert alert-info mb-3">
                                            <i class="fas fa-info-circle"></i>
                                            <strong>Tahap Jadwal:</strong> Tetapkan tanggal, jam, dan ruangan sidang. Tim penguji akan ditetapkan pada tahap verifikasi.
                                        </div>
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label for="ruangan">Ruangan <span class="text-danger">*</span></label>
                                                    <input type="text" name="ruangan" id="ruangan" class="form-control" required>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label for="tanggal_ujian">Tanggal Sidang <span class="text-danger">*</span></label>
                                                    <input type="date" name="tanggal_ujian" id="tanggal_ujian" class="form-control" required>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label for="jam_mulai">Jam Mulai <span class="text-danger">*</span></label>
                                                    <input type="time" name="jam_mulai" id="jam_mulai" class="form-control" required>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label for="jam_selesai">Jam Selesai <span class="text-danger">*</span></label>
                                                    <input type="time" name="jam_selesai" id="jam_selesai" class="form-control" required>
                                                </div>
                                            </div>
                                        </div>
                                        <?php else: ?>
                                        <!-- TAHAP VERIFIKASI: Hanya Ketua Penguji, Anggota 1, Anggota 2 -->
                                        <?php if (!isset($jadwal) || !$jadwal): ?>
                                        <div class="alert alert-danger mb-3">
                                            <i class="fas fa-exclamation-triangle"></i>
                                            <strong>Jadwal sidang belum ditetapkan.</strong> Mahasiswa harus menyelesaikan pendaftaran tahap jadwal terlebih dahulu. Silakan approve pendaftaran tahap jadwal untuk menetapkan tanggal, jam, dan ruangan sidang.
                                        </div>
                                        <?php else: ?>
                                        <div class="alert alert-info mb-3">
                                            <i class="fas fa-info-circle"></i>
                                            <strong>Tahap Verifikasi:</strong> Jadwal sidang (tanggal, jam, ruangan) sudah ditetapkan pada tahap jadwal. Tetapkan tim penguji.
                                            <div class="mt-2">
                                                <strong>Jadwal saat ini:</strong> <?php echo isset($jadwal->tanggal_ujian) ? date('d/m/Y', strtotime($jadwal->tanggal_ujian)) : ''; ?> 
                                                <?php echo isset($jadwal->jam_mulai) && isset($jadwal->jam_selesai) ? date('H:i', strtotime($jadwal->jam_mulai)) . '-' . date('H:i', strtotime($jadwal->jam_selesai)) : ''; ?>
                                                <?php echo isset($jadwal->ruangan) ? ' - ' . htmlspecialchars($jadwal->ruangan) : ''; ?>
                                            </div>
                                        </div>
                                        <div class="alert alert-warning mb-3">
                                            <strong>Rekomendasi Penguji:</strong>
                                            <ul class="mb-0 mt-2">
                                                <li>Pembimbing 2 otomatis direkomendasikan sebagai <strong>Anggota Penguji 2</strong> (masih bisa diganti)</li>
                                                <li>Pembimbing 1 <strong>tidak boleh</strong> menjadi penguji</li>
                                            </ul>
                                        </div>
                                        <div class="row">
                                            <div class="col-md-4">
                                                <div class="form-group">
                                                    <label for="id_ketua_penguji">Ketua Penguji <span class="text-danger">*</span></label>
                                                    <select name="id_ketua_penguji" id="id_ketua_penguji" class="form-control" required>
                                                        <option value="">-- Pilih Ketua Penguji --</option>
                                                        <?php foreach ($dosen_list as $dosen): ?>
                                                            <?php 
                                                            $is_pembimbing_1 = (isset($skripsi) && $skripsi->id_pembimbing_1 == $dosen->id);
                                                            $is_pembimbing_2 = (isset($skripsi) && $skripsi->id_pembimbing_2 == $dosen->id);
                                                            $boleh_pilih = (!$is_pembimbing_1 && !$is_pembimbing_2);
                                                            ?>
                                                            <?php if ($boleh_pilih): ?>
                                                                <option value="<?php echo $dosen->id; ?>"><?php echo $dosen->nama_dosen . ' (' . $dosen->nidn . ')'; ?></option>
                                                            <?php endif; ?>
                                                        <?php endforeach; ?>
                                                    </select>
                                                    <small class="form-text text-muted">Pembimbing 1 dan 2 tidak boleh jadi Ketua Penguji</small>
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <div class="form-group">
                                                    <label for="id_anggota_penguji_1">Anggota Penguji 1 <span class="text-danger">*</span></label>
                                                    <select name="id_anggota_penguji_1" id="id_anggota_penguji_1" class="form-control" required>
                                                        <option value="">-- Pilih Anggota Penguji 1 --</option>
                                                        <?php foreach ($dosen_list as $dosen): ?>
                                                            <?php 
                                                            $is_pembimbing_1 = (isset($skripsi) && $skripsi->id_pembimbing_1 == $dosen->id);
                                                            $is_pembimbing_2 = (isset($skripsi) && $skripsi->id_pembimbing_2 == $dosen->id);
                                                            $boleh_pilih = (!$is_pembimbing_1 && !$is_pembimbing_2);
                                                            ?>
                                                            <?php if ($boleh_pilih): ?>
                                                                <option value="<?php echo $dosen->id; ?>"><?php echo $dosen->nama_dosen . ' (' . $dosen->nidn . ')'; ?></option>
                                                            <?php endif; ?>
                                                        <?php endforeach; ?>
                                                    </select>
                                                    <small class="form-text text-muted">Pembimbing 1 dan 2 tidak boleh jadi Anggota Penguji 1</small>
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <div class="form-group">
                                                    <label for="id_anggota_penguji_2">Anggota Penguji 2 <span class="text-danger">*</span></label>
                                                    <select name="id_anggota_penguji_2" id="id_anggota_penguji_2" class="form-control" required>
                                                        <option value="">-- Pilih Anggota Penguji 2 --</option>
                                                        <?php foreach ($dosen_list as $dosen): ?>
                                                            <?php 
                                                            $is_pembimbing_1 = (isset($skripsi) && $skripsi->id_pembimbing_1 == $dosen->id);
                                                            $is_pembimbing_2 = (isset($skripsi) && $skripsi->id_pembimbing_2 == $dosen->id);
                                                            $boleh_pilih = (!$is_pembimbing_1);
                                                            $selected = ($is_pembimbing_2 && isset($rekomendasi_penguji_2) && $rekomendasi_penguji_2 == $dosen->id) ? 'selected' : '';
                                                            ?>
                                                            <?php if ($boleh_pilih): ?>
                                                                <option value="<?php echo $dosen->id; ?>" <?php echo $selected; ?>>
                                                                    <?php echo $dosen->nama_dosen . ' (' . $dosen->nidn . ')'; ?>
                                                                    <?php if ($is_pembimbing_2): ?> <strong>(Rekomendasi)</strong><?php endif; ?>
                                                                </option>
                                                            <?php endif; ?>
                                                        <?php endforeach; ?>
                                                    </select>
                                                    <small class="form-text text-muted">Pembimbing 2 direkomendasikan, Pembimbing 1 tidak boleh</small>
                                                </div>
                                            </div>
                                        </div>
                                        <?php endif; ?>
                                        <?php endif; ?>
                                        <div class="form-group">
                                            <label for="catatan_sekprodi">Catatan</label>
                                            <textarea name="catatan_sekprodi" id="catatan_sekprodi" class="form-control" rows="3"></textarea>
                                        </div>
                                        <div class="form-group">
                                            <button type="submit" class="btn btn-success">
                                                <i class="fas fa-check"></i> 
                                                <?php if (isset($is_tahap_jadwal) && $is_tahap_jadwal): ?>
                                                    Setujui & Tetapkan Jadwal
                                                <?php else: ?>
                                                    Setujui & Tetapkan Penguji
                                                <?php endif; ?>
                                            </button>
                                        </div>
                                    <?php echo form_close(); ?>
                                    <?php endif; ?>
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
                                    <?php echo form_open('Skripsi_verifikasi_berkas_sidang/reject/' . $pendaftaran->id); ?>
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
                <?php elseif (isset($pendaftaran->status) && $pendaftaran->status === 'disetujui' && isset($jadwal) && is_object($jadwal)): ?>
                    <div class="row mb-4">
                        <div class="col-12">
                            <div class="card card-outline card-success">
                                <div class="card-header">
                                    <h5 class="card-title"><i class="fas fa-calendar-check"></i> Jadwal Sidang</h5>
                                </div>
                                <div class="card-body">
                                    <div class="table-responsive">
                                        <table class="table table-bordered">
                                            <tbody>
                                                <tr>
                                                    <th width="25%" class="bg-light">Tanggal</th>
                                                    <td><?php echo isset($jadwal->tanggal_ujian) ? date('d/m/Y', strtotime($jadwal->tanggal_ujian)) : '-'; ?></td>
                                                </tr>
                                                <tr>
                                                    <th class="bg-light">Jam</th>
                                                    <td>
                                                        <?php 
                                                        if (isset($jadwal->jam_mulai) && isset($jadwal->jam_selesai)) {
                                                            echo date('H:i', strtotime($jadwal->jam_mulai)) . ' - ' . date('H:i', strtotime($jadwal->jam_selesai));
                                                        } else {
                                                            echo '-';
                                                        }
                                                        ?>
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <th class="bg-light">Ruangan</th>
                                                    <td><?php echo isset($jadwal->ruangan) ? htmlspecialchars($jadwal->ruangan) : '-'; ?></td>
                                                </tr>
                                                <tr>
                                                    <th class="bg-light">Ketua Penguji</th>
                                                    <td>
                                                        <?php 
                                                        $kp = $jadwal->ketua_penguji ?? $jadwal->ketua_penguji_nama ?? null;
                                                        if ($kp) {
                                                            echo htmlspecialchars($kp);
                                                            if (isset($jadwal->ketua_penguji_nidn) && !empty($jadwal->ketua_penguji_nidn)) {
                                                                echo ' <span class="text-muted">(' . htmlspecialchars($jadwal->ketua_penguji_nidn) . ')</span>';
                                                            }
                                                        } else {
                                                            echo '-';
                                                        }
                                                        ?>
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <th class="bg-light">Anggota Penguji 1</th>
                                                    <td>
                                                        <?php 
                                                        $ap1 = $jadwal->anggota_penguji_1 ?? $jadwal->penguji_1_nama ?? null;
                                                        if ($ap1) {
                                                            echo htmlspecialchars($ap1);
                                                            $nidn1 = $jadwal->anggota_penguji_1_nidn ?? $jadwal->penguji_1_nidn ?? null;
                                                            if ($nidn1) echo ' <span class="text-muted">(' . htmlspecialchars($nidn1) . ')</span>';
                                                        } else {
                                                            echo '-';
                                                        }
                                                        ?>
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <th class="bg-light">Anggota Penguji 2</th>
                                                    <td>
                                                        <?php 
                                                        $ap2 = $jadwal->anggota_penguji_2 ?? $jadwal->penguji_2_nama ?? null;
                                                        if ($ap2) {
                                                            echo htmlspecialchars($ap2);
                                                            $nidn2 = $jadwal->anggota_penguji_2_nidn ?? $jadwal->penguji_2_nidn ?? null;
                                                            if ($nidn2) echo ' <span class="text-muted">(' . htmlspecialchars($nidn2) . ')</span>';
                                                        } else {
                                                            echo '-';
                                                        }
                                                        ?>
                                                    </td>
                                                </tr>
                                                <?php if (isset($jadwal->catatan) && !empty($jadwal->catatan)): ?>
                                                    <tr>
                                                        <th class="bg-light">Catatan</th>
                                                        <td><?php echo nl2br(htmlspecialchars($jadwal->catatan)); ?></td>
                                                    </tr>
                                                <?php endif; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Form Edit (sesuai tahap) -->
                    <?php if (isset($can_edit_jadwal) && $can_edit_jadwal): ?>
                    <div class="row mt-3">
                        <div class="col-12">
                            <div class="card card-outline card-primary">
                                <div class="card-header">
                                    <h5 class="card-title"><i class="fas fa-edit"></i> 
                                        <?php if (isset($is_tahap_jadwal) && $is_tahap_jadwal): ?>
                                            Edit Jadwal (Ruangan, Tanggal, Jam)
                                        <?php else: ?>
                                            Edit Penguji
                                        <?php endif; ?>
                                    </h5>
                                </div>
                                <div class="card-body">
                                    <?php echo form_open('Skripsi_verifikasi_berkas_sidang/update_jadwal/' . $jadwal->id . '?pendaftaran_id=' . $pendaftaran->id); ?>
                                    <input type="hidden" name="edit_tahap" value="<?php echo isset($pendaftaran_tahap) ? htmlspecialchars($pendaftaran_tahap) : 'verifikasi'; ?>">
                                        <?php if (isset($is_tahap_jadwal) && $is_tahap_jadwal): ?>
                                        <!-- Edit Tahap Jadwal: hanya ruangan, tanggal, jam -->
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label for="edit_ruangan">Ruangan <span class="text-danger">*</span></label>
                                                    <input type="text" name="ruangan" id="edit_ruangan" class="form-control" value="<?php echo isset($jadwal->ruangan) ? htmlspecialchars($jadwal->ruangan) : ''; ?>" required>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label for="edit_tanggal_ujian">Tanggal Sidang <span class="text-danger">*</span></label>
                                                    <input type="date" name="tanggal_ujian" id="edit_tanggal_ujian" class="form-control" value="<?php echo isset($jadwal->tanggal_ujian) ? date('Y-m-d', strtotime($jadwal->tanggal_ujian)) : ''; ?>" required>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label for="edit_jam_mulai">Jam Mulai <span class="text-danger">*</span></label>
                                                    <input type="time" name="jam_mulai" id="edit_jam_mulai" class="form-control" value="<?php echo isset($jadwal->jam_mulai) ? date('H:i', strtotime($jadwal->jam_mulai)) : ''; ?>" required>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label for="edit_jam_selesai">Jam Selesai <span class="text-danger">*</span></label>
                                                    <input type="time" name="jam_selesai" id="edit_jam_selesai" class="form-control" value="<?php echo isset($jadwal->jam_selesai) ? date('H:i', strtotime($jadwal->jam_selesai)) : ''; ?>" required>
                                                </div>
                                            </div>
                                        </div>
                                        <?php else: ?>
                                        <!-- Edit Tahap Verifikasi: hanya penguji -->
                                        <div class="alert alert-info mb-3">
                                            <i class="fas fa-info-circle"></i>
                                            <strong>Rekomendasi Penguji:</strong> Pembimbing 2 direkomendasikan sebagai Anggota Penguji 2. Pembimbing 1 tidak boleh menjadi penguji.
                                        </div>
                                        <div class="row">
                                            <div class="col-md-4">
                                                <div class="form-group">
                                                    <label for="edit_id_ketua_penguji">Ketua Penguji <span class="text-danger">*</span></label>
                                                    <select name="id_ketua_penguji" id="edit_id_ketua_penguji" class="form-control" required>
                                                        <option value="">-- Pilih Ketua Penguji --</option>
                                                        <?php foreach ($dosen_list as $dosen): ?>
                                                            <?php 
                                                            $is_pembimbing_1 = (isset($skripsi) && $skripsi->id_pembimbing_1 == $dosen->id);
                                                            $is_pembimbing_2 = (isset($skripsi) && $skripsi->id_pembimbing_2 == $dosen->id);
                                                            $boleh_pilih = (!$is_pembimbing_1 && !$is_pembimbing_2);
                                                            $selected = (isset($jadwal->id_ketua_penguji) && $jadwal->id_ketua_penguji == $dosen->id) ? 'selected' : '';
                                                            ?>
                                                            <?php if ($boleh_pilih): ?>
                                                                <option value="<?php echo $dosen->id; ?>" <?php echo $selected; ?>><?php echo $dosen->nama_dosen . ' (' . $dosen->nidn . ')'; ?></option>
                                                            <?php endif; ?>
                                                        <?php endforeach; ?>
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <div class="form-group">
                                                    <label for="edit_id_anggota_penguji_1">Anggota Penguji 1 <span class="text-danger">*</span></label>
                                                    <select name="id_anggota_penguji_1" id="edit_id_anggota_penguji_1" class="form-control" required>
                                                        <option value="">-- Pilih Anggota Penguji 1 --</option>
                                                        <?php foreach ($dosen_list as $dosen): ?>
                                                            <?php 
                                                            $is_pembimbing_1 = (isset($skripsi) && $skripsi->id_pembimbing_1 == $dosen->id);
                                                            $is_pembimbing_2 = (isset($skripsi) && $skripsi->id_pembimbing_2 == $dosen->id);
                                                            $boleh_pilih = (!$is_pembimbing_1 && !$is_pembimbing_2);
                                                            $selected = (isset($jadwal->id_anggota_penguji_1) && $jadwal->id_anggota_penguji_1 == $dosen->id) ? 'selected' : '';
                                                            ?>
                                                            <?php if ($boleh_pilih): ?>
                                                                <option value="<?php echo $dosen->id; ?>" <?php echo $selected; ?>><?php echo $dosen->nama_dosen . ' (' . $dosen->nidn . ')'; ?></option>
                                                            <?php endif; ?>
                                                        <?php endforeach; ?>
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <div class="form-group">
                                                    <label for="edit_id_anggota_penguji_2">Anggota Penguji 2 <span class="text-danger">*</span></label>
                                                    <select name="id_anggota_penguji_2" id="edit_id_anggota_penguji_2" class="form-control" required>
                                                        <option value="">-- Pilih Anggota Penguji 2 --</option>
                                                        <?php foreach ($dosen_list as $dosen): ?>
                                                            <?php 
                                                            $is_pembimbing_1 = (isset($skripsi) && $skripsi->id_pembimbing_1 == $dosen->id);
                                                            $is_pembimbing_2 = (isset($skripsi) && $skripsi->id_pembimbing_2 == $dosen->id);
                                                            $boleh_pilih = (!$is_pembimbing_1);
                                                            $selected = (isset($jadwal->id_anggota_penguji_2) && $jadwal->id_anggota_penguji_2 == $dosen->id) ? 'selected' : '';
                                                            ?>
                                                            <?php if ($boleh_pilih): ?>
                                                                <option value="<?php echo $dosen->id; ?>" <?php echo $selected; ?>>
                                                                    <?php echo $dosen->nama_dosen . ' (' . $dosen->nidn . ')'; ?>
                                                                    <?php if ($is_pembimbing_2): ?> <strong>(Rekomendasi)</strong><?php endif; ?>
                                                                </option>
                                                            <?php endif; ?>
                                                        <?php endforeach; ?>
                                                    </select>
                                                </div>
                                            </div>
                                        </div>
                                        <?php endif; ?>
                                        <div class="form-group">
                                            <label for="edit_catatan">Catatan</label>
                                            <textarea name="catatan" id="edit_catatan" class="form-control" rows="3"><?php echo isset($jadwal->catatan) ? htmlspecialchars($jadwal->catatan) : ''; ?></textarea>
                                        </div>
                                        <div class="form-group">
                                            <button type="submit" class="btn btn-primary">
                                                <i class="fas fa-save"></i> 
                                                <?php if (isset($is_tahap_jadwal) && $is_tahap_jadwal): ?>
                                                    Update Jadwal
                                                <?php else: ?>
                                                    Update Penguji
                                                <?php endif; ?>
                                            </button>
                                        </div>
                                    <?php echo form_close(); ?>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php else: ?>
                    <div class="row mt-3">
                        <div class="col-12">
                            <div class="alert alert-warning">
                                <i class="fas fa-info-circle"></i> 
                                <strong>Form edit tidak tersedia:</strong> Jadwal sidang sudah selesai dan semua penguji sudah memberikan nilai. 
                                Data jadwal tidak dapat diubah lagi.
                            </div>
                        </div>
                    </div>
                    <?php endif; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>


