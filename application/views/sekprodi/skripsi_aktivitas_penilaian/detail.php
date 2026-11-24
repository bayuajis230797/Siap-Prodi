<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Detail Aktivitas Penilaian Skripsi</h3>
                <div class="card-tools">
                    <a href="<?php echo site_url('Skripsi_aktivitas_penilaian'); ?>" class="btn btn-secondary btn-sm">
                        <i class="fas fa-arrow-left"></i> Kembali
                    </a>
                </div>
            </div>
            <div class="card-body">
                <?php 
                $this->load->helper('skripsi');
                
                // Informasi Mahasiswa
                ?>
                <div class="row mb-4">
                    <div class="col-md-6">
                        <h5 class="mb-3"><i class="fas fa-user"></i> Informasi Mahasiswa</h5>
                        <table class="table table-sm">
                            <tr>
                                <th width="40%">NIM</th>
                                <td><strong><?php echo isset($skripsi->nim) ? $skripsi->nim : '-'; ?></strong></td>
                            </tr>
                            <tr>
                                <th>Nama Mahasiswa</th>
                                <td><?php echo isset($skripsi->nama_mahasiswa) ? $skripsi->nama_mahasiswa : '-'; ?></td>
                            </tr>
                            <tr>
                                <th>Kelas</th>
                                <td><span class="badge badge-info"><?php echo isset($skripsi->kelas) ? $skripsi->kelas : '-'; ?></span></td>
                            </tr>
                            <tr>
                                <th>Judul Skripsi</th>
                                <td>
                                    <?php 
                                    $judul = isset($judul_skripsi_terbaru) && !empty($judul_skripsi_terbaru) 
                                        ? $judul_skripsi_terbaru 
                                        : (isset($skripsi->judul_skripsi) && !empty($skripsi->judul_skripsi) 
                                            ? $skripsi->judul_skripsi 
                                            : '-');
                                    echo htmlspecialchars($judul);
                                    ?>
                                </td>
                            </tr>
                        </table>
                    </div>
                    <div class="col-md-6">
                        <h5 class="mb-3"><i class="fas fa-chart-line"></i> Nilai Rekap</h5>
                        <table class="table table-sm">
                            <tr>
                                <th width="40%">Nilai Bimbingan (P1)</th>
                                <td>
                                    <?php 
                                    $nilai_p1_display = null;
                                    if (isset($nilai_rekap) && isset($nilai_rekap->nilai_bimbingan) && $nilai_rekap->nilai_bimbingan !== null) {
                                        $nilai_p1_display = $nilai_rekap->nilai_bimbingan;
                                    } elseif (isset($nilai_bimbingan_rata) && $nilai_bimbingan_rata !== null) {
                                        $nilai_p1_display = $nilai_bimbingan_rata;
                                    }
                                    ?>
                                    <?php if ($nilai_p1_display !== null): ?>
                                        <strong><?php echo number_format($nilai_p1_display, 2); ?></strong>
                                    <?php else: ?>
                                        <span class="text-muted">-</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <tr>
                                <th>Nilai Sidang (P2)</th>
                                <td>
                                    <?php 
                                    $nilai_p2_display = null;
                                    if (isset($nilai_rekap) && isset($nilai_rekap->nilai_sidang) && $nilai_rekap->nilai_sidang !== null) {
                                        $nilai_p2_display = $nilai_rekap->nilai_sidang;
                                    } elseif (isset($nilai_sidang_rata) && $nilai_sidang_rata !== null) {
                                        $nilai_p2_display = $nilai_sidang_rata;
                                    }
                                    ?>
                                    <?php if ($nilai_p2_display !== null): ?>
                                        <strong><?php echo number_format($nilai_p2_display, 2); ?></strong>
                                    <?php else: ?>
                                        <span class="text-muted">-</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <tr>
                                <th>Nilai Akhir</th>
                                <td>
                                    <?php 
                                    $nilai_akhir_display = null;
                                    if (isset($nilai_rekap) && isset($nilai_rekap->nilai_akhir) && $nilai_rekap->nilai_akhir !== null) {
                                        $nilai_akhir_display = $nilai_rekap->nilai_akhir;
                                    } elseif (isset($nilai_akhir) && $nilai_akhir !== null) {
                                        $nilai_akhir_display = $nilai_akhir;
                                    }
                                    ?>
                                    <?php if ($nilai_akhir_display !== null): ?>
                                        <strong><?php echo number_format($nilai_akhir_display, 2); ?></strong>
                                    <?php else: ?>
                                        <span class="text-muted">-</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <tr>
                                <th>Nilai Huruf</th>
                                <td>
                                    <?php 
                                    $nilai_huruf_display = null;
                                    // Re-konversi dari nilai akhir menggunakan helper function yang mengambil dari setting database
                                    if (isset($nilai_akhir_display) && $nilai_akhir_display !== null) {
                                        $this->load->helper('skripsi');
                                        $nilai_huruf_display = konversi_nilai_huruf_skripsi($nilai_akhir_display);
                                    } elseif (isset($nilai_rekap) && isset($nilai_rekap->nilai_akhir) && $nilai_rekap->nilai_akhir !== null) {
                                        $this->load->helper('skripsi');
                                        $nilai_huruf_display = konversi_nilai_huruf_skripsi($nilai_rekap->nilai_akhir);
                                    } elseif (isset($nilai_akhir) && $nilai_akhir !== null) {
                                        $this->load->helper('skripsi');
                                        $nilai_huruf_display = konversi_nilai_huruf_skripsi($nilai_akhir);
                                    } elseif (isset($grade) && $grade !== null && !empty($grade)) {
                                        // Fallback: gunakan grade yang sudah ada jika nilai akhir tidak tersedia
                                        $nilai_huruf_display = $grade;
                                    }
                                    ?>
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
                                        <span class="badge badge-<?php echo $grade_class; ?> badge-lg" style="font-size: 1.2em; padding: 8px 16px;">
                                            <?php echo htmlspecialchars($nilai_huruf_display); ?>
                                        </span>
                                    <?php else: ?>
                                        <span class="text-muted">-</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        </table>
                    </div>
                </div>

                <!-- Rekap Hasil Penilaian -->
                <div class="row mb-4">
                    <div class="col-12">
                        <div class="card card-outline card-primary">
                            <div class="card-header">
                                <h5 class="card-title"><i class="fas fa-calculator"></i> Rekap Hasil Penilaian</h5>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-bordered table-sm">
                                        <thead class="bg-light">
                                            <tr>
                                                <th width="30%" class="text-center">Komponen</th>
                                                <th width="70%" class="text-center">Nilai</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <!-- Nilai Bimbingan -->
                                            <tr>
                                                <td class="bg-light"><strong>Nilai Pembimbing 1</strong></td>
                                                <td class="text-center">
                                                    <?php if (isset($nilai_p1) && $nilai_p1 !== null): ?>
                                                        <strong><?php echo number_format($nilai_p1, 2); ?></strong>
                                                    <?php else: ?>
                                                        <span class="text-muted">-</span>
                                                    <?php endif; ?>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td class="bg-light"><strong>Nilai Pembimbing 2</strong></td>
                                                <td class="text-center">
                                                    <?php if (isset($nilai_p2) && $nilai_p2 !== null): ?>
                                                        <strong><?php echo number_format($nilai_p2, 2); ?></strong>
                                                    <?php else: ?>
                                                        <span class="text-muted">-</span>
                                                    <?php endif; ?>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td class="bg-light"><strong>Rata-rata Nilai Bimbingan</strong><br><small class="text-muted">(P1 + P2) / 2</small></td>
                                                <td class="text-center">
                                                    <?php if (isset($nilai_bimbingan_rata) && $nilai_bimbingan_rata !== null): ?>
                                                        <strong><?php echo number_format($nilai_bimbingan_rata, 2); ?></strong>
                                                    <?php else: ?>
                                                        <span class="text-muted">-</span>
                                                    <?php endif; ?>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td class="bg-light"><strong>40% dari Nilai Bimbingan</strong><br><small class="text-muted">(Rata-rata × 0.4)</small></td>
                                                <td class="text-center">
                                                    <?php if (isset($nilai_bimbingan_40) && $nilai_bimbingan_40 !== null): ?>
                                                        <strong class="text-primary"><?php echo number_format($nilai_bimbingan_40, 2); ?></strong>
                                                    <?php else: ?>
                                                        <span class="text-muted">-</span>
                                                    <?php endif; ?>
                                                </td>
                                            </tr>
                                            
                                            <!-- Separator -->
                                            <tr>
                                                <td colspan="2" class="bg-secondary text-white text-center">
                                                    <strong>NILAI SIDANG (P2)</strong>
                                                </td>
                                            </tr>
                                            
                                            <!-- Nilai Penguji -->
                                            <?php if (!empty($nilai_penguji_list)): ?>
                                                <?php foreach ($nilai_penguji_list as $np): ?>
                                                    <?php
                                                    $peran_label = '';
                                                    switch ($np['peran']) {
                                                        case 'ketua':
                                                            $peran_label = 'Ketua Penguji';
                                                            break;
                                                        case 'anggota_1':
                                                            $peran_label = 'Anggota Penguji 1';
                                                            break;
                                                        case 'anggota_2':
                                                            $peran_label = 'Anggota Penguji 2';
                                                            break;
                                                        default:
                                                            $peran_label = 'Penguji';
                                                    }
                                                    ?>
                                                    <tr>
                                                        <td class="bg-light"><strong><?php echo htmlspecialchars($peran_label); ?></strong><br><small class="text-muted"><?php echo htmlspecialchars($np['nama']); ?></small></td>
                                                        <td class="text-center">
                                                            <?php if (isset($np['nilai']) && $np['nilai'] !== null && $np['nilai'] > 0): ?>
                                                                <strong><?php echo number_format($np['nilai'], 2); ?></strong>
                                                            <?php else: ?>
                                                                <span class="text-muted">-</span>
                                                            <?php endif; ?>
                                                        </td>
                                                    </tr>
                                                <?php endforeach; ?>
                                                
                                                <tr>
                                                    <td class="bg-light"><strong>Rata-rata Nilai Sidang</strong><br><small class="text-muted">(Semua Penguji yang sudah menginput) / <?php 
                                                    $count_terinput = 0;
                                                    foreach ($nilai_penguji_list as $np) {
                                                        if (isset($np['nilai']) && $np['nilai'] !== null && $np['nilai'] > 0) {
                                                            $count_terinput++;
                                                        }
                                                    }
                                                    echo $count_terinput > 0 ? $count_terinput : count($nilai_penguji_list);
                                                    ?></small></td>
                                                    <td class="text-center">
                                                        <?php if (isset($nilai_sidang_rata) && $nilai_sidang_rata !== null): ?>
                                                            <strong><?php echo number_format($nilai_sidang_rata, 2); ?></strong>
                                                        <?php else: ?>
                                                            <span class="text-muted">-</span>
                                                        <?php endif; ?>
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td class="bg-light"><strong>60% dari Nilai Sidang</strong><br><small class="text-muted">(Rata-rata × 0.6)</small></td>
                                                    <td class="text-center">
                                                        <?php if (isset($nilai_sidang_60) && $nilai_sidang_60 !== null): ?>
                                                            <strong class="text-primary"><?php echo number_format($nilai_sidang_60, 2); ?></strong>
                                                        <?php else: ?>
                                                            <span class="text-muted">-</span>
                                                        <?php endif; ?>
                                                    </td>
                                                </tr>
                                            <?php else: ?>
                                                <tr>
                                                    <td class="bg-light"><strong>Nilai Penguji</strong></td>
                                                    <td class="text-center">
                                                        <span class="text-muted">Belum ada nilai sidang</span>
                                                    </td>
                                                </tr>
                                            <?php endif; ?>
                                            
                                            <!-- Separator -->
                                            <tr>
                                                <td colspan="2" class="bg-success text-white text-center">
                                                    <strong>NILAI AKHIR</strong>
                                                </td>
                                            </tr>
                                            
                                            <!-- Nilai Akhir -->
                                            <tr>
                                                <td class="bg-light"><strong>Nilai Akhir Skripsi</strong><br><small class="text-muted">(40% Bimbingan + 60% Sidang)</small></td>
                                                <td class="text-center">
                                                    <?php if (isset($nilai_akhir) && $nilai_akhir !== null): ?>
                                                        <strong class="text-success" style="font-size: 1.2em;"><?php echo number_format($nilai_akhir, 2); ?></strong>
                                                    <?php else: ?>
                                                        <span class="text-muted">-</span>
                                                    <?php endif; ?>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td class="bg-light"><strong>Grade (Nilai Huruf)</strong></td>
                                                <td class="text-center">
                                                    <?php if (isset($grade) && $grade !== null): ?>
                                                        <?php
                                                        $grade_class = 'secondary';
                                                        switch ($grade) {
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
                                                        <span class="badge badge-<?php echo $grade_class; ?> badge-lg" style="font-size: 1.5em; padding: 10px 20px;">
                                                            <?php echo htmlspecialchars($grade); ?>
                                                        </span>
                                                    <?php else: ?>
                                                        <span class="text-muted">-</span>
                                                    <?php endif; ?>
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Nilai Bimbingan dari Pembimbing -->
                <?php if (!empty($nilai_bimbingan_list)): ?>
                    <div class="row mb-4">
                        <div class="col-12">
                            <h5 class="mb-3"><i class="fas fa-users"></i> Nilai Bimbingan (P1) dari Pembimbing</h5>
                            <div class="table-responsive">
                                <table class="table table-bordered">
                                    <thead>
                                        <tr>
                                            <th>Pembimbing</th>
                                            <th class="text-center">Nilai Bimbingan (P1)</th>
                                            <th class="text-center">Tanggal Input</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($nilai_bimbingan_list as $nilai_bimbingan): ?>
                                            <tr>
                                                <td>
                                                    <strong><?php echo isset($nilai_bimbingan->pembimbing_nama) ? htmlspecialchars($nilai_bimbingan->pembimbing_nama) : '-'; ?></strong>
                                                    <br><small class="text-muted">
                                                        <?php 
                                                        $tipe_label = '';
                                                        if (isset($nilai_bimbingan->tipe_pembimbing)) {
                                                            $tipe_label = ($nilai_bimbingan->tipe_pembimbing == 1) ? 'Pembimbing 1' : 'Pembimbing 2';
                                                        }
                                                        echo htmlspecialchars($tipe_label);
                                                        ?>
                                                    </small>
                                                </td>
                                                <td class="text-center">
                                                    <?php if (isset($nilai_bimbingan->nilai_rata) && $nilai_bimbingan->nilai_rata !== null && $nilai_bimbingan->nilai_rata > 0): ?>
                                                        <strong><?php echo number_format($nilai_bimbingan->nilai_rata, 2); ?></strong>
                                                    <?php else: ?>
                                                        <span class="text-muted text-italic">Belum diinput</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td class="text-center">
                                                    <?php if (isset($nilai_bimbingan->created_at) && !empty($nilai_bimbingan->created_at)): ?>
                                                        <?php echo date('d/m/Y H:i', strtotime($nilai_bimbingan->created_at)); ?>
                                                    <?php else: ?>
                                                        <span class="text-muted">-</span>
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

                <!-- Nilai Sidang dari Penguji -->
                <?php if (!empty($nilai_sidang_list)): ?>
                    <div class="row mb-4">
                        <div class="col-12">
                            <h5 class="mb-3"><i class="fas fa-users"></i> Nilai Sidang (P2) dari Penguji</h5>
                            <div class="table-responsive">
                                <table class="table table-bordered">
                                    <thead>
                                        <tr>
                                            <th>Penguji</th>
                                            <th class="text-center">Peran</th>
                                            <th class="text-center">Nilai Sidang (P2)</th>
                                            <th class="text-center">Tanggal Input</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($nilai_sidang_list as $nilai_sidang): ?>
                                            <tr>
                                                <td>
                                                    <strong><?php echo isset($nilai_sidang->penguji_nama) ? htmlspecialchars($nilai_sidang->penguji_nama) : '-'; ?></strong>
                                                </td>
                                                <td class="text-center">
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
                                                    ?>
                                                    <span class="badge badge-primary"><?php echo $tipe_label; ?></span>
                                                </td>
                                                <td class="text-center">
                                                    <?php if (isset($nilai_sidang->nilai_total) && $nilai_sidang->nilai_total !== null && $nilai_sidang->nilai_total > 0): ?>
                                                        <strong><?php echo number_format($nilai_sidang->nilai_total, 2); ?></strong>
                                                    <?php else: ?>
                                                        <span class="text-muted text-italic">Belum diinput</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td class="text-center">
                                                    <?php if (isset($nilai_sidang->created_at) && !empty($nilai_sidang->created_at)): ?>
                                                        <?php echo date('d/m/Y H:i', strtotime($nilai_sidang->created_at)); ?>
                                                    <?php else: ?>
                                                        <span class="text-muted">-</span>
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

                <!-- Detail Penilaian per Item - P1 (Bimbingan) -->
                <?php if (!empty($item_p1) && !empty($nilai_bimbingan_list)): ?>
                    <div class="row mb-4">
                        <div class="col-12">
                            <h5 class="mb-3"><i class="fas fa-list"></i> Detail Penilaian Bimbingan (P1) per Item</h5>
                            
                            <?php foreach ($nilai_bimbingan_list as $nilai_bimbingan): ?>
                                <div class="card mb-3">
                                    <div class="card-header bg-light">
                                        <h5 class="mb-0">
                                            <strong><?php echo isset($nilai_bimbingan->pembimbing_nama) ? htmlspecialchars($nilai_bimbingan->pembimbing_nama) : '-'; ?></strong> 
                                            <?php 
                                            $tipe_label = '';
                                            if (isset($nilai_bimbingan->tipe_pembimbing)) {
                                                $tipe_label = ($nilai_bimbingan->tipe_pembimbing == 1) ? '(Pembimbing 1)' : '(Pembimbing 2)';
                                            }
                                            echo $tipe_label;
                                            ?>
                                        </h5>
                                    </div>
                                    <div class="card-body">
                                        <?php
                                        $details = isset($nilai_bimbingan->details) ? $nilai_bimbingan->details : array();
                                        ?>
                                        <?php if (empty($details)): ?>
                                            <p class="text-muted mb-0">Belum ada detail penilaian.</p>
                                        <?php else: ?>
                                            <div class="table-responsive">
                                                <table class="table table-sm table-bordered">
                                                    <thead>
                                                        <tr>
                                                            <th width="5%">No</th>
                                                            <th>Item Penilaian</th>
                                                            <th width="10%" class="text-center">Bobot</th>
                                                            <th width="15%" class="text-center">Nilai</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        <?php 
                                                        $no_item = 1;
                                                        foreach ($item_p1 as $item): 
                                                            $nilai_item = '-';
                                                            foreach ($details as $detail) {
                                                                if ($detail->id_item_penilaian == $item->id) {
                                                                    $nilai_item = $detail->nilai;
                                                                    break;
                                                                }
                                                            }
                                                        ?>
                                                            <tr>
                                                                <td><?php echo $no_item++; ?></td>
                                                                <td><strong><?php echo htmlspecialchars($item->pertanyaan); ?></strong></td>
                                                                <td class="text-center"><span class="badge badge-secondary"><?php echo isset($item->bobot) ? $item->bobot : 0; ?>%</span></td>
                                                                <td class="text-center"><?php echo $nilai_item; ?></td>
                                                            </tr>
                                                        <?php endforeach; ?>
                                                    </tbody>
                                                </table>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endif; ?>

                <!-- Detail Penilaian per Item - P2 (Sidang) -->
                <?php if (!empty($item_p2) && !empty($nilai_sidang_list)): ?>
                    <div class="row mb-4">
                        <div class="col-12">
                            <h5 class="mb-3"><i class="fas fa-list"></i> Detail Penilaian Sidang (P2) per Item</h5>
                            
                            <?php foreach ($nilai_sidang_list as $nilai_sidang): ?>
                                <div class="card mb-3">
                                    <div class="card-header bg-light">
                                        <h5 class="mb-0">
                                            <strong><?php echo isset($nilai_sidang->penguji_nama) ? htmlspecialchars($nilai_sidang->penguji_nama) : '-'; ?></strong>
                                            <?php
                                            $tipe_label = '';
                                            $peran = isset($nilai_sidang->peran) ? $nilai_sidang->peran : '';
                                            switch ($peran) {
                                                case 'ketua':
                                                    $tipe_label = '(Ketua Penguji)';
                                                    break;
                                                case 'anggota_1':
                                                    $tipe_label = '(Anggota Penguji 1)';
                                                    break;
                                                case 'anggota_2':
                                                    $tipe_label = '(Anggota Penguji 2)';
                                                    break;
                                                default:
                                                    $tipe_label = '(Penguji)';
                                            }
                                            echo $tipe_label;
                                            ?>
                                        </h5>
                                    </div>
                                    <div class="card-body">
                                        <?php
                                        $details = isset($nilai_sidang->details) ? $nilai_sidang->details : array();
                                        ?>
                                        <?php if (empty($details)): ?>
                                            <p class="text-muted mb-0">Belum ada detail penilaian.</p>
                                        <?php else: ?>
                                            <div class="table-responsive">
                                                <table class="table table-sm table-bordered">
                                                    <thead>
                                                        <tr>
                                                            <th width="5%">No</th>
                                                            <th>Item Penilaian</th>
                                                            <th width="10%" class="text-center">Bobot</th>
                                                            <th width="15%" class="text-center">Nilai</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        <?php 
                                                        $no_item = 1;
                                                        foreach ($item_p2 as $item): 
                                                            $nilai_item = '-';
                                                            foreach ($details as $detail) {
                                                                if ($detail->id_item_penilaian == $item->id) {
                                                                    $nilai_item = $detail->nilai;
                                                                    break;
                                                                }
                                                            }
                                                        ?>
                                                            <tr>
                                                                <td><?php echo $no_item++; ?></td>
                                                                <td><strong><?php echo htmlspecialchars($item->pertanyaan); ?></strong></td>
                                                                <td class="text-center"><span class="badge badge-secondary"><?php echo isset($item->bobot) ? $item->bobot : 0; ?>%</span></td>
                                                                <td class="text-center"><?php echo $nilai_item; ?></td>
                                                            </tr>
                                                        <?php endforeach; ?>
                                                    </tbody>
                                                </table>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endif; ?>

                <!-- Catatan Revisi dari Penguji -->
                <?php 
                $catatan_revisi_list = array();
                foreach ($nilai_sidang_list ?? array() as $nilai_sidang) {
                    if (!empty($nilai_sidang->catatan)) {
                        $catatan_revisi_list[] = $nilai_sidang;
                    }
                }
                ?>
                <?php if (!empty($catatan_revisi_list)): ?>
                    <div class="row mb-4">
                        <div class="col-12">
                            <div class="card card-outline card-warning">
                                <div class="card-header">
                                    <h5 class="card-title"><i class="fas fa-comment-dots"></i> Catatan Revisi Pasca Sidang</h5>
                                </div>
                                <div class="card-body">
                                    <?php foreach ($catatan_revisi_list as $catatan_revisi): ?>
                                        <div class="mb-3 p-3 bg-light border rounded">
                                            <p class="mb-1"><strong>Dari:</strong> <?php echo isset($catatan_revisi->penguji_nama) ? $catatan_revisi->penguji_nama : '-'; ?></p>
                                            <p class="mb-1"><strong>Peran:</strong> 
                                                <?php
                                                $tipe_label = '';
                                                $peran = isset($catatan_revisi->peran) ? $catatan_revisi->peran : '';
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
                                            </p>
                                            <p class="mb-1"><strong>Tanggal:</strong> <?php echo isset($catatan_revisi->tanggal_input) ? date('d/m/Y', strtotime($catatan_revisi->tanggal_input)) : '-'; ?></p>
                                            <div class="mt-2">
                                                <?php echo nl2br(htmlspecialchars($catatan_revisi->catatan)); ?>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>


