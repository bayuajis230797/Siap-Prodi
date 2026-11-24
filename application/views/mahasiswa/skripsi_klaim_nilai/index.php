<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Klaim Nilai Skripsi</h3>
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

                <?php if ($nilai_rekap && $klaim && isset($klaim->status) && $klaim->status === 'disetujui'): ?>
                    <?php
                    // Re-konversi dari nilai akhir menggunakan helper function yang mengambil dari setting database
                    $this->load->helper('skripsi');
                    $grade = null;
                    // Prioritas: selalu re-konversi dari nilai akhir menggunakan helper function
                    if (isset($nilai_akhir_skripsi) && $nilai_akhir_skripsi !== null) {
                        $grade = konversi_nilai_huruf_skripsi($nilai_akhir_skripsi);
                    } elseif (isset($nilai_rekap->nilai_akhir) && $nilai_rekap->nilai_akhir !== null) {
                        $grade = konversi_nilai_huruf_skripsi($nilai_rekap->nilai_akhir);
                    } elseif (isset($nilai_rekap->grade) && !empty($nilai_rekap->grade)) {
                        // Fallback: gunakan grade yang sudah ada jika nilai akhir tidak tersedia
                        $grade = $nilai_rekap->grade;
                    } elseif (isset($nilai_rekap->nilai_huruf) && !empty($nilai_rekap->nilai_huruf)) {
                        // Fallback: gunakan nilai_huruf yang sudah ada jika nilai akhir tidak tersedia
                        $grade = $nilai_rekap->nilai_huruf;
                    }
                    ?>
                    
                    <!-- Informasi Nilai -->
                    <?php if ($nilai_bimbingan_final || $nilai_sidang_final || $nilai_akhir_skripsi): ?>
                        <div class="card card-outline card-info mb-4">
                            <div class="card-header">
                                <h5 class="card-title mb-0"><i class="fas fa-chart-line"></i> Informasi Nilai</h5>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-4">
                                        <div class="info-box">
                                            <span class="info-box-icon bg-info elevation-1"><i class="fas fa-user-tie"></i></span>
                                            <div class="info-box-content">
                                                <span class="info-box-text">Nilai Akhir Pembimbing</span>
                                                <span class="info-box-number">
                                                    <?php if ($nilai_bimbingan_final): ?>
                                                        <?php echo number_format($nilai_bimbingan_final, 2); ?>
                                                    <?php else: ?>
                                                        <span class="text-muted">-</span>
                                                    <?php endif; ?>
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="info-box">
                                            <span class="info-box-icon bg-warning elevation-1"><i class="fas fa-users"></i></span>
                                            <div class="info-box-content">
                                                <span class="info-box-text">Nilai Akhir Penguji</span>
                                                <span class="info-box-number">
                                                    <?php if ($nilai_sidang_final): ?>
                                                        <?php echo number_format($nilai_sidang_final, 2); ?>
                                                    <?php else: ?>
                                                        <span class="text-muted">-</span>
                                                    <?php endif; ?>
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="info-box">
                                            <span class="info-box-icon bg-success elevation-1"><i class="fas fa-trophy"></i></span>
                                            <div class="info-box-content">
                                                <span class="info-box-text">Nilai Akhir Skripsi</span>
                                                <span class="info-box-number">
                                                    <?php if ($nilai_akhir_skripsi): ?>
                                                        <strong><?php echo number_format($nilai_akhir_skripsi, 2); ?></strong>
                                                    <?php else: ?>
                                                        <span class="text-muted">-</span>
                                                    <?php endif; ?>
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- Keterangan Perhitungan Nilai Akhir -->
                                <?php if ($nilai_bimbingan_final && $nilai_sidang_final): ?>
                                    <div class="mt-4 pt-3 border-top">
                                        <h6 class="mb-3"><i class="fas fa-calculator"></i> Keterangan Perhitungan Nilai Akhir</h6>
                                        <div class="alert alert-light">
                                            <p class="mb-2"><strong>Rumus Perhitungan:</strong></p>
                                            <p class="mb-1">
                                                <strong>Nilai Akhir Skripsi</strong> = 
                                                (<?php echo $bobot_bimbingan; ?>% × Nilai Akhir Pembimbing) + 
                                                (<?php echo $bobot_sidang; ?>% × Nilai Akhir Penguji)
                                            </p>
                                            <?php if ($nilai_akhir_skripsi): ?>
                                                <p class="mb-0 mt-2">
                                                    <strong>Contoh Perhitungan:</strong><br>
                                                    Nilai Akhir Skripsi = 
                                                    (<?php echo $bobot_bimbingan; ?>% × <?php echo number_format($nilai_bimbingan_final, 2); ?>) + 
                                                    (<?php echo $bobot_sidang; ?>% × <?php echo number_format($nilai_sidang_final, 2); ?>)<br>
                                                    = (<?php echo number_format(($bobot_bimbingan / 100) * $nilai_bimbingan_final, 2); ?>) + 
                                                    (<?php echo number_format(($bobot_sidang / 100) * $nilai_sidang_final, 2); ?>)<br>
                                                    = <strong><?php echo number_format($nilai_akhir_skripsi, 2); ?></strong>
                                                </p>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                <?php endif; ?>
                                
                                <!-- Daftar Grade Huruf -->
                                <?php if (!empty($grade_list)): ?>
                                    <div class="mt-4 pt-3 border-top">
                                        <h6 class="mb-3"><i class="fas fa-list"></i> Daftar Grade Huruf Lengkap</h6>
                                        <div class="table-responsive">
                                            <table class="table table-sm table-bordered table-striped">
                                                <thead class="thead-light">
                                                    <tr>
                                                        <th width="15%" class="text-center">No</th>
                                                        <th width="20%" class="text-center">Grade</th>
                                                        <th width="25%" class="text-center">Range Nilai</th>
                                                        <th width="40%">Keterangan</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?php 
                                                    $no = 1;
                                                    foreach ($grade_list as $grade_item): 
                                                        $grade_item_class = 'secondary';
                                                        $grade_upper = strtoupper($grade_item->grade);
                                                        if (strpos($grade_upper, 'A') === 0 && $grade_upper !== 'A-') {
                                                            $grade_item_class = 'success';
                                                        } elseif (strpos($grade_upper, 'B') === 0) {
                                                            $grade_item_class = 'info';
                                                        } elseif (strpos($grade_upper, 'C') === 0) {
                                                            $grade_item_class = 'warning';
                                                        } elseif (strpos($grade_upper, 'D') === 0 || strpos($grade_upper, 'E') === 0) {
                                                            $grade_item_class = 'danger';
                                                        }
                                                        
                                                        $range_text = number_format($grade_item->nilai_min, 2);
                                                        if ($grade_item->nilai_max !== null) {
                                                            $range_text .= ' - ' . number_format($grade_item->nilai_max, 2);
                                                        } else {
                                                            $range_text .= ' - 100.00';
                                                        }
                                                    ?>
                                                        <tr>
                                                            <td class="text-center">
                                                                <strong><?php echo $no++; ?></strong>
                                                            </td>
                                                            <td class="text-center">
                                                                <span class="badge badge-<?php echo $grade_item_class; ?> badge-lg" style="font-size: 18px; padding: 10px 20px; min-width: 60px; display: inline-block;">
                                                                    <?php echo htmlspecialchars($grade_item->grade); ?>
                                                                </span>
                                                            </td>
                                                            <td class="text-center">
                                                                <strong style="font-size: 14px;"><?php echo $range_text; ?></strong>
                                                            </td>
                                                            <td>
                                                                <span style="font-size: 14px;"><?php echo htmlspecialchars($grade_item->keterangan ?: '-'); ?></span>
                                                            </td>
                                                        </tr>
                                                    <?php endforeach; ?>
                                                </tbody>
                                            </table>
                                        </div>
                                        <div class="mt-2">
                                            <small class="text-muted">
                                                <i class="fas fa-info-circle"></i> 
                                                Total: <strong><?php echo count($grade_list); ?></strong> grade huruf untuk Skripsi
                                            </small>
                                        </div>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endif; ?>
                    
                    <!-- Nilai Final Skripsi -->
                    <?php if ($grade): ?>
                        <?php
                        // Tentukan warna badge berdasarkan grade
                        $grade_class = 'secondary';
                        $grade_upper = strtoupper($grade);
                        if (strpos($grade_upper, 'A') === 0 && $grade_upper !== 'A-') {
                            $grade_class = 'success';
                        } elseif (strpos($grade_upper, 'B') === 0) {
                            $grade_class = 'info';
                        } elseif (strpos($grade_upper, 'C') === 0) {
                            $grade_class = 'warning';
                        } elseif (strpos($grade_upper, 'D') === 0 || strpos($grade_upper, 'E') === 0) {
                            $grade_class = 'danger';
                        }
                        ?>
                        <div class="alert alert-success text-center py-5">
                            <h5 class="mb-4"><i class="fas fa-check-circle"></i> Nilai Final Skripsi</h5>
                            <div class="mb-0" style="font-size: 150px; line-height: 1;">
                                <span class="badge badge-<?php echo $grade_class; ?> badge-lg" style="font-size: 150px; padding: 30px 60px; border: 5px solid rgba(255,255,255,0.5);">
                                    <?php echo htmlspecialchars($grade); ?>
                                </span>
                            </div>
                        </div>
                    <?php endif; ?>
                <?php endif; ?>

                <?php if ($klaim): ?>
                    <div class="alert alert-info">
                        <h5><i class="fas fa-info-circle"></i> Status Klaim Nilai</h5>
                        <p class="mb-0">
                            <strong>Status:</strong> 
                            <?php
                            $status_class = 'secondary';
                            $status_text = ucfirst($klaim->status);
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
                            <span class="badge badge-<?php echo $status_class; ?>"><?php echo $status_text; ?></span>
                        </p>
                        <?php if (isset($klaim->catatan_sekprodi) && $klaim->catatan_sekprodi): ?>
                            <p class="mb-0 mt-2"><strong>Catatan Sekprodi:</strong> <?php echo nl2br(htmlspecialchars($klaim->catatan_sekprodi)); ?></p>
                        <?php endif; ?>
                    </div>

                    <?php if (!empty($berkas_uploaded)): ?>
                        <h5 class="mb-3"><i class="fas fa-file-alt"></i> Berkas yang Sudah Diupload</h5>
                        <div class="table-responsive mb-4">
                            <table class="table table-bordered">
                                <thead>
                                    <tr>
                                        <th>Nama Berkas</th>
                                        <th>File</th>
                                        <th>Tanggal Upload</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($berkas_uploaded as $berkas): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($berkas->nama_berkas); ?></td>
                                            <td>
                                                <?php 
                                                $this->load->helper('google_drive');
                                                echo google_drive_link_html($berkas->file_path, htmlspecialchars($berkas->file_name));
                                                ?>
                                                <?php if (isset($berkas->wajib) && $berkas->wajib): ?>
                                                    <span class="badge badge-danger ml-2">Wajib</span>
                                                <?php endif; ?>
                                            </td>
                                            <td><?php echo isset($berkas->uploaded_at) ? date('d/m/Y H:i', strtotime($berkas->uploaded_at)) : '-'; ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>

                    <?php if (isset($klaim->status) && $klaim->status === 'ditolak'): ?>
                        <div class="alert alert-warning">
                            <p>Klaim nilai Anda ditolak. Silakan perbaiki berkas dan ajukan kembali.</p>
                        </div>
                    <?php endif; ?>
                <?php endif; ?>

                <?php if (!$klaim || (isset($klaim->status) && $klaim->status === 'ditolak')): ?>
                    <?php if (empty($boleh_klaim)): ?>
                        <div class="alert alert-warning">
                            <i class="fas fa-hourglass-half"></i>
                            <strong>Menunggu penguji.</strong>
                            <?php echo htmlspecialchars(isset($pasca_status['message']) ? $pasca_status['message'] : 'Menunggu semua penguji menyatakan layak klaim nilai.'); ?>
                            <?php if (!empty($pasca_status['total'])): ?>
                                (<?php echo (int) $pasca_status['layak']; ?>/<?php echo (int) $pasca_status['total']; ?>)
                            <?php endif; ?>
                            <br>
                            <a href="<?php echo site_url('Bimbingan_pasca_skripsi_mahasiswa'); ?>" class="btn btn-sm btn-outline-primary mt-2">
                                Buka Bimbingan Pasca Sidang
                            </a>
                        </div>
                    <?php endif; ?>
                    <?php echo form_open_multipart('Skripsi_klaim_nilai/submit'); ?>
                        <fieldset <?php echo empty($boleh_klaim) ? 'disabled' : ''; ?>>
                        <h5 class="mb-3"><i class="fas fa-upload"></i> Upload Berkas Klaim Nilai</h5>
                        <p class="text-muted mb-4">Silakan upload semua berkas yang wajib untuk klaim nilai Skripsi.</p>
                        
                        <?php if (empty($berkas_list)): ?>
                            <div class="alert alert-warning">
                                <i class="fas fa-exclamation-triangle"></i> Belum ada berkas yang ditentukan. Silakan hubungi Sekprodi.
                            </div>
                        <?php else: ?>
                            <?php foreach ($berkas_list as $berkas): ?>
                                <?php
                                $berkas_uploaded_item = null;
                                if (!empty($berkas_uploaded)) {
                                    foreach ($berkas_uploaded as $bu) {
                                        if (isset($bu->id_berkas_klaim_nilai) && $bu->id_berkas_klaim_nilai == $berkas->id) {
                                            $berkas_uploaded_item = $bu;
                                            break;
                                        }
                                    }
                                }
                                ?>
                                <div class="form-group">
                                    <label for="berkas_<?php echo $berkas->id; ?>">
                                        <?php echo htmlspecialchars($berkas->nama_berkas); ?>
                                        <?php if (isset($berkas->wajib) && $berkas->wajib): ?>
                                            <span class="text-danger">*</span>
                                        <?php endif; ?>
                                    </label>
                                    <?php if (isset($berkas->deskripsi) && $berkas->deskripsi): ?>
                                        <small class="form-text text-muted mb-2"><?php echo htmlspecialchars($berkas->deskripsi); ?></small>
                                    <?php endif; ?>
                                    <?php if ($berkas_uploaded_item): ?>
                                        <div class="alert alert-info mb-2">
                                            <i class="fas fa-check-circle"></i> Link sudah diupload: 
                                            <?php 
                                            $this->load->helper('google_drive');
                                            echo google_drive_link_html($berkas_uploaded_item->file_path, 'Buka Berkas');
                                            ?>
                                        </div>
                                    <?php endif; ?>
                                    <input type="file" name="file_berkas_<?php echo $berkas->id; ?>" id="file_berkas_<?php echo $berkas->id; ?>"
                                           class="form-control-file" accept=".pdf,.doc,.docx,.zip,.rar,.jpg,.jpeg,.png"
                                           <?php echo (isset($berkas->wajib) && $berkas->wajib && !$berkas_uploaded_item) ? 'required' : ''; ?>>
                                    <small class="form-text text-muted">
                                        Diunggah ke Google Drive institusi. Kosongkan jika tidak mengganti berkas yang sudah ada.
                                    </small>
                                </div>
                            <?php endforeach; ?>
                            
                            <div class="form-group">
                                <button type="submit" class="btn btn-primary" <?php echo empty($boleh_klaim) ? 'disabled' : ''; ?>>
                                    <i class="fas fa-paper-plane"></i> <?php echo $klaim ? 'Update Klaim Nilai' : 'Kirim Klaim Nilai'; ?>
                                </button>
                                <?php if (empty($boleh_klaim)): ?>
                                    <small class="text-muted d-block mt-2">Tombol nonaktif: menunggu penguji menyatakan layak klaim nilai.</small>
                                <?php endif; ?>
                            </div>
                        <?php endif; ?>
                        </fieldset>
                    <?php echo form_close(); ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>


