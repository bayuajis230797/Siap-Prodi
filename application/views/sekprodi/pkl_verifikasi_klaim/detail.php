<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Detail Verifikasi Klaim Nilai PKL</h3>
                <div class="card-tools">
                    <a href="<?php echo site_url('Pkl_verifikasi_klaim'); ?>" class="btn btn-secondary btn-sm">
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
                                <td><strong><?php echo $klaim->nim; ?></strong></td>
                            </tr>
                            <tr>
                                <th>Nama Mahasiswa</th>
                                <td><?php echo $klaim->nama_mahasiswa; ?></td>
                            </tr>
                            <tr>
                                <th>Kelas</th>
                                <td><span class="badge badge-info"><?php echo $klaim->kelas; ?></span></td>
                            </tr>
                            <tr>
                                <th>Judul PKL</th>
                                <td><?php echo $klaim->judul_pkl ?: '-'; ?></td>
                            </tr>
                        </table>
                    </div>
                    <div class="col-md-6">
                        <h5 class="mb-3"><i class="fas fa-info-circle"></i> Status Klaim</h5>
                        <table class="table table-sm">
                            <tr>
                                <th width="40%">Status</th>
                                <td>
                                    <?php
                                    $status_class = 'secondary';
                                    switch ($klaim->status) {
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
                                    <span class="badge badge-<?php echo $status_class; ?>"><?php echo ucfirst($klaim->status); ?></span>
                                </td>
                            </tr>
                            <tr>
                                <th>Tanggal Pengajuan</th>
                                <td><?php echo date('d/m/Y H:i', strtotime($klaim->created_at)); ?></td>
                            </tr>
                            <?php if ($klaim->catatan_sekprodi): ?>
                                <tr>
                                    <th>Catatan</th>
                                    <td><?php echo nl2br(htmlspecialchars($klaim->catatan_sekprodi)); ?></td>
                                </tr>
                            <?php endif; ?>
                        </table>
                    </div>
                </div>

                <!-- Berkas yang Diupload -->
                <?php if (!empty($berkas_uploaded)): ?>
                    <div class="row mb-4">
                        <div class="col-12">
                            <h5 class="mb-3"><i class="fas fa-file-alt"></i> Berkas yang Diupload</h5>
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
                                                        <a href="<?php echo site_url('Pkl_verifikasi_klaim/download_berkas/' . $berkas->id); ?>" class="btn btn-sm btn-primary" title="Download">
                                                            <i class="fas fa-download"></i>
                                                        </a>
                                                    <?php endif; ?>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>

                <!-- Penilaian dari Semua Penilai -->
                <?php if (!empty($penilaian_list)): ?>
                    <div class="row mb-4">
                        <div class="col-12">
                            <h5 class="mb-3"><i class="fas fa-chart-line"></i> Penilaian dari Semua Penilai</h5>
                            <div class="table-responsive">
                                <table class="table table-bordered">
                                    <thead>
                                        <tr>
                                            <th rowspan="2" class="align-middle">Penilai</th>
                                            <th colspan="6" class="text-center">Nilai Aspek</th>
                                            <th rowspan="2" class="align-middle">Nilai Akhir</th>
                                        </tr>
                                        <tr>
                                            <th class="text-center">P1</th>
                                            <th class="text-center">P2</th>
                                            <th class="text-center">P3</th>
                                            <th class="text-center">P4</th>
                                            <th class="text-center">P5</th>
                                            <th class="text-center">P6</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php 
                                        $this->load->helper('pkl');
                                        foreach ($penilaian_list as $penilaian): 
                                            $nilai_akhir_penilai = hitung_nilai_akhir_pkl(
                                                $penilaian->nilai_p1, 
                                                $penilaian->nilai_p2, 
                                                $penilaian->nilai_p3, 
                                                $penilaian->nilai_p4, 
                                                $penilaian->nilai_p5, 
                                                $penilaian->nilai_p6
                                            );
                                            $label_penilai = ucfirst(str_replace('_', ' ', $penilaian->tipe_penilai));
                                        ?>
                                            <tr>
                                                <td><strong><?php echo $label_penilai; ?></strong></td>
                                                <td class="text-center"><?php echo number_format($penilaian->nilai_p1, 2); ?></td>
                                                <td class="text-center"><?php echo number_format($penilaian->nilai_p2, 2); ?></td>
                                                <td class="text-center"><?php echo number_format($penilaian->nilai_p3, 2); ?></td>
                                                <td class="text-center"><?php echo number_format($penilaian->nilai_p4, 2); ?></td>
                                                <td class="text-center"><?php echo number_format($penilaian->nilai_p5, 2); ?></td>
                                                <td class="text-center"><?php echo number_format($penilaian->nilai_p6, 2); ?></td>
                                                <td class="text-center"><strong><?php echo number_format($nilai_akhir_penilai, 2); ?></strong></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>

                <!-- Nilai Rekap -->
                <?php if ($nilai_rekap): ?>
                    <div class="row mb-4">
                        <div class="col-12">
                            <h5 class="mb-3"><i class="fas fa-calculator"></i> Rekapitulasi Nilai Final</h5>
                            <div class="alert alert-success">
                                <div class="row">
                                    <div class="col-md-3">
                                        <p class="mb-1"><strong>Nilai Akhir</strong></p>
                                        <h3><?php echo number_format($nilai_rekap->nilai_akhir, 2); ?></h3>
                                    </div>
                                    <div class="col-md-3">
                                        <p class="mb-1"><strong>Nilai Huruf (Grade)</strong></p>
                                        <?php
                                        $this->load->helper('pkl');
                                        $grade = $nilai_rekap->nilai_huruf;
                                        if (!$grade && $nilai_rekap->nilai_akhir > 0) {
                                            $grade = konversi_nilai_huruf_pkl($nilai_rekap->nilai_akhir);
                                        }
                                        $grade_class = 'primary';
                                        if ($grade) {
                                            switch (strtoupper($grade)) {
                                                case 'A':
                                                    $grade_class = 'success';
                                                    break;
                                                case 'B':
                                                    $grade_class = 'info';
                                                    break;
                                                case 'C':
                                                    $grade_class = 'warning';
                                                    break;
                                                case 'D':
                                                case 'E':
                                                    $grade_class = 'danger';
                                                    break;
                                            }
                                        }
                                        ?>
                                        <?php if ($grade): ?>
                                            <h3><span class="badge badge-<?php echo $grade_class; ?> badge-lg" style="font-size: 1.5em; padding: 10px 20px;"><?php echo htmlspecialchars($grade); ?></span></h3>
                                        <?php else: ?>
                                            <h3><span class="text-muted">-</span></h3>
                                        <?php endif; ?>
                                    </div>
                                    <div class="col-md-6">
                                        <p class="mb-1"><strong>Rincian Nilai per Penilai:</strong></p>
                                        <small>
                                            Pembimbing Dosen: <?php echo number_format($nilai_rekap->nilai_pembimbing_dosen, 2); ?><br>
                                            Pembimbing Lapangan: <?php echo number_format($nilai_rekap->nilai_pembimbing_lapangan, 2); ?><br>
                                            Sekprodi: <?php echo number_format($nilai_rekap->nilai_sekprodi, 2); ?><br>
                                            Penguji: <?php echo number_format($nilai_rekap->nilai_penguji, 2); ?>
                                        </small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php else: ?>
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle"></i> Nilai final akan dihitung setelah klaim disetujui.
                    </div>
                <?php endif; ?>

                <!-- Form Approve/Reject -->
                <?php if ($klaim->status === 'menunggu'): ?>
                    <div class="row">
                        <div class="col-12">
                            <div class="card card-outline card-success">
                                <div class="card-header">
                                    <h5 class="card-title"><i class="fas fa-check-circle"></i> Setujui Klaim Nilai & Finalisasi Nilai</h5>
                                </div>
                                <div class="card-body">
                                    <?php echo form_open('Pkl_verifikasi_klaim/approve/' . $klaim->id); ?>
                                        <div class="form-group">
                                            <label for="catatan_sekprodi">Catatan</label>
                                            <textarea name="catatan_sekprodi" id="catatan_sekprodi" class="form-control" rows="3" placeholder="Catatan (opsional)"></textarea>
                                        </div>
                                        <div class="alert alert-warning">
                                            <i class="fas fa-exclamation-triangle"></i> 
                                            <strong>Perhatian:</strong> Dengan menyetujui klaim nilai, sistem akan menghitung nilai final dari semua penilaian yang ada (mengambil nilai maksimal untuk setiap aspek P1-P6).
                                        </div>
                                        <div class="form-group">
                                            <button type="submit" class="btn btn-success" onclick="return confirm('Apakah Anda yakin ingin menyetujui klaim nilai dan memfinalisasi nilai?');">
                                                <i class="fas fa-check"></i> Setujui & Finalisasi Nilai
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
                                    <h5 class="card-title"><i class="fas fa-times-circle"></i> Tolak Klaim Nilai</h5>
                                </div>
                                <div class="card-body">
                                    <?php echo form_open('Pkl_verifikasi_klaim/reject/' . $klaim->id); ?>
                                        <div class="form-group">
                                            <label for="catatan_sekprodi_reject">Alasan Penolakan <span class="text-danger">*</span></label>
                                            <textarea name="catatan_sekprodi" id="catatan_sekprodi_reject" class="form-control" rows="3" required placeholder="Sebutkan alasan penolakan klaim nilai"></textarea>
                                        </div>
                                        <div class="form-group">
                                            <button type="submit" class="btn btn-danger" onclick="return confirm('Apakah Anda yakin ingin menolak klaim nilai ini?');">
                                                <i class="fas fa-times"></i> Tolak Klaim Nilai
                                            </button>
                                        </div>
                                    <?php echo form_close(); ?>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php elseif ($klaim->status === 'disetujui' && $nilai_rekap): ?>
                    <div class="alert alert-success">
                        <h5><i class="fas fa-check-circle"></i> Klaim Nilai Telah Disetujui</h5>
                        <p class="mb-0">Nilai final telah dihitung dan difinalisasi.</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>





