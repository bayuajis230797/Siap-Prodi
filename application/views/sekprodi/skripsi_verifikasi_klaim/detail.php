<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Detail Verifikasi Klaim Nilai Skripsi</h3>
                <div class="card-tools">
                    <a href="<?php echo site_url('Skripsi_verifikasi_klaim'); ?>" class="btn btn-secondary btn-sm">
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
                                <td><strong><?php echo isset($klaim->nim) ? $klaim->nim : '-'; ?></strong></td>
                            </tr>
                            <tr>
                                <th>Nama Mahasiswa</th>
                                <td><?php echo isset($klaim->nama_mahasiswa) ? $klaim->nama_mahasiswa : '-'; ?></td>
                            </tr>
                            <tr>
                                <th>Kelas</th>
                                <td><span class="badge badge-info"><?php echo isset($klaim->kelas) ? $klaim->kelas : '-'; ?></span></td>
                            </tr>
                            <tr>
                                <th>Judul Skripsi</th>
                                <td><?php echo isset($klaim->judul_skripsi) ? $klaim->judul_skripsi : '-'; ?></td>
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
                                    $status = isset($klaim->status) ? $klaim->status : 'menunggu';
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
                                <td><?php echo isset($klaim->created_at) ? date('d/m/Y H:i', strtotime($klaim->created_at)) : '-'; ?></td>
                            </tr>
                            <?php if (isset($klaim->catatan_sekprodi) && $klaim->catatan_sekprodi): ?>
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
                        </div>
                    </div>
                <?php endif; ?>

                <!-- Penilaian Bimbingan (P1) -->
                <?php if (!empty($nilai_bimbingan_list)): ?>
                    <div class="row mb-4">
                        <div class="col-12">
                            <h5 class="mb-3"><i class="fas fa-chart-line"></i> Nilai Bimbingan (P1) dari Pembimbing</h5>
                            <div class="table-responsive">
                                <table class="table table-bordered">
                                    <thead>
                                        <tr>
                                            <th>Pembimbing</th>
                                            <th class="text-center">Nilai Bimbingan (P1)</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($nilai_bimbingan_list as $nilai_bimbingan): ?>
                                            <tr>
                                                <td>
                                                    <strong><?php echo isset($nilai_bimbingan->pembimbing_nama) ? $nilai_bimbingan->pembimbing_nama : '-'; ?></strong>
                                                    <br><small class="text-muted">
                                                        <?php
                                                        $tipe_label = '';
                                                        if (isset($nilai_bimbingan->tipe_pembimbing)) {
                                                            $tipe_label = ($nilai_bimbingan->tipe_pembimbing == 1) ? 'Pembimbing 1' : 'Pembimbing 2';
                                                        }
                                                        echo $tipe_label;
                                                        ?>
                                                    </small>
                                                </td>
                                                <td class="text-center">
                                                    <strong><?php echo isset($nilai_bimbingan->nilai_rata) && $nilai_bimbingan->nilai_rata > 0 ? number_format($nilai_bimbingan->nilai_rata, 2) : '-'; ?></strong>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>

                <!-- Penilaian Sidang (P2) -->
                <?php if (!empty($nilai_sidang_list)): ?>
                    <div class="row mb-4">
                        <div class="col-12">
                            <h5 class="mb-3"><i class="fas fa-chart-line"></i> Nilai Sidang (P2) dari Penguji</h5>
                            <div class="table-responsive">
                                <table class="table table-bordered">
                                    <thead>
                                        <tr>
                                            <th>Penguji</th>
                                            <th class="text-center">Nilai Sidang (P2)</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($nilai_sidang_list as $nilai_sidang): ?>
                                            <tr>
                                                <td>
                                                    <strong><?php echo isset($nilai_sidang->penguji_nama) ? $nilai_sidang->penguji_nama : '-'; ?></strong>
                                                    <br><small class="text-muted">
                                                        <?php
                                                        $tipe_label = '';
                                                        $peran = isset($nilai_sidang->peran) ? $nilai_sidang->peran : '';
                                                        switch ($peran) {
                                                            case 'ketua':
                                                                $tipe_label = 'Ketua Penguji';
                                                                break;
                                                            case 'anggota_1':
                                                                $tipe_label = 'Anggota Penguji 1';
                                                                break;
                                                            case 'anggota_2':
                                                                $tipe_label = 'Anggota Penguji 2';
                                                                break;
                                                            default:
                                                                $tipe_label = 'Penguji';
                                                        }
                                                        echo $tipe_label;
                                                        ?>
                                                    </small>
                                                </td>
                                                <td class="text-center">
                                                    <strong><?php echo isset($nilai_sidang->nilai_total) && $nilai_sidang->nilai_total > 0 ? number_format($nilai_sidang->nilai_total, 2) : '-'; ?></strong>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>

                <!-- Nilai Rekap -->
                <?php 
                // Hitung nilai bimbingan rata-rata jika belum ada di rekap
                $nilai_bimbingan_rata_display = null;
                if (isset($nilai_rekap) && isset($nilai_rekap->nilai_bimbingan_rata) && $nilai_rekap->nilai_bimbingan_rata !== null) {
                    $nilai_bimbingan_rata_display = $nilai_rekap->nilai_bimbingan_rata;
                } elseif (!empty($nilai_bimbingan_list)) {
                    $total_nilai = 0;
                    $count = 0;
                    foreach ($nilai_bimbingan_list as $nb) {
                        if (isset($nb->nilai_rata) && $nb->nilai_rata > 0) {
                            $total_nilai += $nb->nilai_rata;
                            $count++;
                        }
                    }
                    if ($count > 0) {
                        $nilai_bimbingan_rata_display = $total_nilai / $count;
                    }
                }
                
                // Hitung nilai sidang rata-rata jika belum ada di rekap
                $nilai_sidang_rata_display = null;
                if (isset($nilai_rekap) && isset($nilai_rekap->nilai_sidang_rata) && $nilai_rekap->nilai_sidang_rata !== null) {
                    $nilai_sidang_rata_display = $nilai_rekap->nilai_sidang_rata;
                } elseif (!empty($nilai_sidang_list)) {
                    $total_nilai = 0;
                    $count = 0;
                    foreach ($nilai_sidang_list as $ns) {
                        if (isset($ns->nilai_total) && $ns->nilai_total > 0) {
                            $total_nilai += $ns->nilai_total;
                            $count++;
                        }
                    }
                    if ($count > 0) {
                        $nilai_sidang_rata_display = $total_nilai / $count;
                    }
                }
                
                // Hitung nilai akhir dan grade - selalu re-konversi dari nilai akhir menggunakan helper function
                $nilai_akhir_display = null;
                $nilai_huruf_display = null;
                $this->load->helper('skripsi');
                
                if (isset($nilai_rekap) && isset($nilai_rekap->nilai_akhir) && $nilai_rekap->nilai_akhir !== null) {
                    $nilai_akhir_display = $nilai_rekap->nilai_akhir;
                    // Re-konversi dari nilai akhir menggunakan helper function yang mengambil dari setting database
                    $nilai_huruf_display = konversi_nilai_huruf_skripsi($nilai_akhir_display);
                } elseif ($nilai_bimbingan_rata_display !== null && $nilai_sidang_rata_display !== null) {
                    $nilai_akhir_display = hitung_nilai_akhir_skripsi($nilai_bimbingan_rata_display, $nilai_sidang_rata_display);
                    // Konversi dari nilai akhir menggunakan helper function yang mengambil dari setting database
                    $nilai_huruf_display = konversi_nilai_huruf_skripsi($nilai_akhir_display);
                }
                ?>
                <div class="row mb-4">
                    <div class="col-12">
                        <h5 class="mb-3"><i class="fas fa-calculator"></i> Rekapitulasi Nilai Final</h5>
                        <div class="alert alert-success">
                            <div class="row">
                                <div class="col-md-3">
                                    <p class="mb-1"><strong>Nilai Bimbingan (P1)</strong></p>
                                    <?php if ($nilai_bimbingan_rata_display !== null): ?>
                                        <h4><?php echo number_format($nilai_bimbingan_rata_display, 2); ?></h4>
                                    <?php else: ?>
                                        <h4 class="text-muted">-</h4>
                                    <?php endif; ?>
                                </div>
                                <div class="col-md-3">
                                    <p class="mb-1"><strong>Nilai Sidang (P2)</strong></p>
                                    <?php if ($nilai_sidang_rata_display !== null): ?>
                                        <h4><?php echo number_format($nilai_sidang_rata_display, 2); ?></h4>
                                    <?php else: ?>
                                        <h4 class="text-muted">-</h4>
                                    <?php endif; ?>
                                </div>
                                <div class="col-md-3">
                                    <p class="mb-1"><strong>Nilai Akhir</strong></p>
                                    <?php if ($nilai_akhir_display !== null): ?>
                                        <h3><?php echo number_format($nilai_akhir_display, 2); ?></h3>
                                    <?php else: ?>
                                        <h3 class="text-muted">-</h3>
                                    <?php endif; ?>
                                </div>
                                <div class="col-md-3">
                                    <p class="mb-1"><strong>Nilai Huruf</strong></p>
                                    <?php if ($nilai_huruf_display !== null && !empty($nilai_huruf_display)): ?>
                                        <?php
                                        $grade_class = 'secondary';
                                        switch (strtoupper($nilai_huruf_display)) {
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
                                        ?>
                                        <h3><span class="badge badge-<?php echo $grade_class; ?> badge-lg" style="font-size: 1.5em; padding: 10px 20px;"><?php echo htmlspecialchars($nilai_huruf_display); ?></span></h3>
                                    <?php else: ?>
                                        <h3 class="text-muted">-</h3>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <div class="row mt-3">
                                <div class="col-12">
                                    <small class="text-muted">
                                        <strong>Rumus:</strong> Nilai Akhir = (40% × Nilai Bimbingan) + (60% × Nilai Sidang)
                                    </small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Form Approve/Reject -->
                <?php if (isset($klaim->status) && $klaim->status === 'menunggu'): ?>
                    <div class="row">
                        <div class="col-12">
                            <div class="card card-outline card-success">
                                <div class="card-header">
                                    <h5 class="card-title"><i class="fas fa-check-circle"></i> Setujui Klaim Nilai & Finalisasi Nilai</h5>
                                </div>
                                <div class="card-body">
                                    <?php echo form_open('Skripsi_verifikasi_klaim/approve/' . $klaim->id); ?>
                                        <div class="form-group">
                                            <label for="catatan_sekprodi">Catatan</label>
                                            <textarea name="catatan_sekprodi" id="catatan_sekprodi" class="form-control" rows="3" placeholder="Catatan (opsional)"></textarea>
                                        </div>
                                        <div class="alert alert-warning">
                                            <i class="fas fa-exclamation-triangle"></i> 
                                            <strong>Perhatian:</strong> Dengan menyetujui klaim nilai, sistem akan menghitung nilai final berdasarkan rumus: Nilai Akhir = (40% × Nilai Bimbingan) + (60% × Nilai Sidang).
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
                                    <?php echo form_open('Skripsi_verifikasi_klaim/reject/' . $klaim->id); ?>
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
                <?php elseif (isset($klaim->status) && $klaim->status === 'disetujui' && $nilai_rekap): ?>
                    <div class="alert alert-success">
                        <h5><i class="fas fa-check-circle"></i> Klaim Nilai Telah Disetujui</h5>
                        <p class="mb-0">Nilai final telah dihitung dan difinalisasi.</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>





