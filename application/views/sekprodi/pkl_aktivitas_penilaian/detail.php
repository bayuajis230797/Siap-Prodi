<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Detail Aktivitas Penilaian PKL</h3>
                <div class="card-tools">
                    <a href="<?php echo site_url('Pkl_aktivitas_penilaian'); ?>" class="btn btn-secondary btn-sm">
                        <i class="fas fa-arrow-left"></i> Kembali
                    </a>
                </div>
            </div>
            <div class="card-body">
                <?php 
                $this->load->helper('pkl');
                
                // Informasi Mahasiswa
                ?>
                <div class="row mb-4">
                    <div class="col-md-6">
                        <h5 class="mb-3"><i class="fas fa-user"></i> Informasi Mahasiswa</h5>
                        <table class="table table-sm">
                            <tr>
                                <th width="40%">NIM</th>
                                <td><strong><?php echo $pengajuan->nim; ?></strong></td>
                            </tr>
                            <tr>
                                <th>Nama Mahasiswa</th>
                                <td><?php echo $pengajuan->nama_mahasiswa; ?></td>
                            </tr>
                            <tr>
                                <th>Kelas</th>
                                <td><span class="badge badge-info"><?php echo $pengajuan->kelas; ?></span></td>
                            </tr>
                            <tr>
                                <th>Judul PKL</th>
                                <td><?php echo $pengajuan->judul_pkl ?: '-'; ?></td>
                            </tr>
                        </table>
                    </div>
                    <div class="col-md-6">
                        <h5 class="mb-3"><i class="fas fa-chart-line"></i> Nilai Rekap</h5>
                        <?php if ($nilai_rekap): ?>
                            <table class="table table-sm">
                                <tr>
                                    <th width="40%">Nilai Akhir</th>
                                    <td><strong><?php echo number_format($nilai_rekap->nilai_akhir, 2); ?></strong></td>
                                </tr>
                                <tr>
                                    <th>Nilai Huruf (Grade)</th>
                                    <td>
                                        <?php if ($nilai_rekap->nilai_huruf): ?>
                                            <?php
                                            $grade = $nilai_rekap->nilai_huruf;
                                            $grade_class = 'secondary';
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
                                            ?>
                                            <span class="badge badge-<?php echo $grade_class; ?> badge-lg" style="font-size: 1.2em; padding: 8px 15px;"><?php echo htmlspecialchars($grade); ?></span>
                                        <?php else: ?>
                                            <?php
                                            // Hitung grade jika belum ada
                                            if ($nilai_rekap->nilai_akhir > 0) {
                                                $grade = konversi_nilai_huruf_pkl($nilai_rekap->nilai_akhir);
                                                if ($grade) {
                                                    $grade_class = 'secondary';
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
                                                    echo '<span class="badge badge-' . $grade_class . ' badge-lg" style="font-size: 1.2em; padding: 8px 15px;">' . htmlspecialchars($grade) . '</span>';
                                                } else {
                                                    echo '<span class="text-muted">-</span>';
                                                }
                                            } else {
                                                echo '<span class="text-muted">-</span>';
                                            }
                                            ?>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                                <tr>
                                    <th>Pembimbing Dosen</th>
                                    <td><?php echo number_format($nilai_rekap->nilai_pembimbing_dosen, 2); ?></td>
                                </tr>
                                <tr>
                                    <th>Pembimbing Lapangan</th>
                                    <td><?php echo number_format($nilai_rekap->nilai_pembimbing_lapangan, 2); ?></td>
                                </tr>
                                <tr>
                                    <th>Sekprodi</th>
                                    <td><?php echo number_format($nilai_rekap->nilai_sekprodi, 2); ?></td>
                                </tr>
                                <tr>
                                    <th>Penguji</th>
                                    <td><?php echo number_format($nilai_rekap->nilai_penguji, 2); ?></td>
                                </tr>
                            </table>
                        <?php else: ?>
                            <p class="text-muted">Nilai rekap belum tersedia.</p>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Ringkasan Penilaian per Penilai -->
                <?php if (!empty($penilaian_list)): ?>
                    <div class="row mb-4">
                        <div class="col-12">
                            <h5 class="mb-3"><i class="fas fa-users"></i> Ringkasan Penilaian per Penilai</h5>
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
                                        <?php foreach ($penilaian_list as $penilaian): 
                                            $nilai_akhir_penilai = hitung_nilai_akhir_pkl(
                                                $penilaian->nilai_p1, 
                                                $penilaian->nilai_p2, 
                                                $penilaian->nilai_p3, 
                                                $penilaian->nilai_p4, 
                                                $penilaian->nilai_p5, 
                                                $penilaian->nilai_p6
                                            );
                                        ?>
                                            <tr>
                                                <td>
                                                    <strong><?php echo $penilaian->penilai_nama; ?></strong>
                                                    <br><small class="text-muted"><?php echo date('d/m/Y', strtotime($penilaian->tanggal_penilaian)); ?></small>
                                                </td>
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

                <!-- Detail Penilaian per Item -->
                <?php if (!empty($penilaian_list) && !empty($aspek_list)): ?>
                    <div class="row mb-4">
                        <div class="col-12">
                            <h5 class="mb-3"><i class="fas fa-list"></i> Detail Penilaian per Item</h5>
                            
                            <?php foreach ($aspek_list as $aspek): ?>
                                <div class="card mb-3">
                                    <div class="card-header bg-light">
                                        <h5 class="mb-0">
                                            <strong><?php echo $aspek->kode_aspek; ?>:</strong> <?php echo $aspek->nama_aspek; ?>
                                        </h5>
                                    </div>
                                    <div class="card-body">
                                        <?php if (empty($aspek->items)): ?>
                                            <p class="text-muted mb-0">Belum ada item penilaian untuk aspek ini.</p>
                                        <?php else: ?>
                                            <div class="table-responsive">
                                                <table class="table table-sm table-bordered">
                                                    <thead>
                                                        <tr>
                                                            <th width="5%">No</th>
                                                            <th>Item Penilaian</th>
                                                            <th width="10%" class="text-center">Bobot</th>
                                                            <?php foreach ($penilaian_list as $penilaian): ?>
                                                                <th width="15%" class="text-center">
                                                                    <?php echo $penilaian->penilai_nama; ?>
                                                                    <br><small class="text-muted"><?php echo ucfirst(str_replace('_', ' ', $penilaian->tipe_penilai)); ?></small>
                                                                </th>
                                                            <?php endforeach; ?>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        <?php 
                                                        $no_item = 1;
                                                        foreach ($aspek->items as $item): 
                                                            // Cari detail penilaian untuk item ini
                                                            $detail_per_penilai = array();
                                                            foreach ($penilaian_list as $penilaian) {
                                                                foreach ($penilaian->details as $detail) {
                                                                    if ($detail->id_item_penilaian == $item->id && $detail->id_penilaian == $penilaian->id) {
                                                                        $detail_per_penilai[$penilaian->id] = $detail->nilai;
                                                                        break;
                                                                    }
                                                                }
                                                            }
                                                        ?>
                                                            <tr>
                                                                <td><?php echo $no_item++; ?></td>
                                                                <td>
                                                                    <strong><?php echo $item->pertanyaan; ?></strong>
                                                                </td>
                                                                <td class="text-center">
                                                                    <span class="badge badge-secondary"><?php echo $item->bobot; ?>%</span>
                                                                </td>
                                                                <?php foreach ($penilaian_list as $penilaian): ?>
                                                                    <td class="text-center">
                                                                        <?php if (isset($detail_per_penilai[$penilaian->id])): ?>
                                                                            <?php echo number_format($detail_per_penilai[$penilaian->id], 2); ?>
                                                                        <?php else: ?>
                                                                            <span class="text-muted">-</span>
                                                                        <?php endif; ?>
                                                                    </td>
                                                                <?php endforeach; ?>
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
                $catatan_revisi = null;
                foreach ($penilaian_list as $penilaian) {
                    if ($penilaian->tipe_penilai === 'penguji' && !empty($penilaian->catatan)) {
                        $catatan_revisi = $penilaian;
                        break;
                    }
                }
                ?>
                <?php if ($catatan_revisi): ?>
                    <div class="row mb-4">
                        <div class="col-12">
                            <div class="card card-outline card-warning">
                                <div class="card-header">
                                    <h5 class="card-title"><i class="fas fa-comment-dots"></i> Catatan Revisi Pasca Sidang</h5>
                                </div>
                                <div class="card-body">
                                    <p><strong>Dari:</strong> <?php echo $catatan_revisi->penilai_nama; ?> (Penguji)</p>
                                    <p><strong>Tanggal:</strong> <?php echo date('d/m/Y', strtotime($catatan_revisi->tanggal_penilaian)); ?></p>
                                    <div class="bg-light p-3 border rounded">
                                        <?php echo nl2br(htmlspecialchars($catatan_revisi->catatan)); ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

