<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Aktivitas Penilaian Skripsi</h3>
                <div class="card-tools">
                    <a href="<?php echo site_url('Skripsi_aktivitas_penilaian/export_excel'); ?>" class="btn btn-success btn-sm">
                        <i class="fas fa-file-excel"></i> Export Excel
                    </a>
                </div>
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

                <div class="alert alert-info mb-3">
                    <i class="fas fa-info-circle"></i> 
                    Halaman ini menampilkan rinci semua aktivitas penilaian Skripsi dari Pembimbing dan Penguji.
                </div>

                <div class="table-responsive">
                    <table class="table table-bordered table-striped table-hover">
                        <thead>
                            <tr>
                                <th width="5%">No</th>
                                <th width="12%">NIM</th>
                                <th>Nama Mahasiswa</th>
                                <th width="8%" class="text-center">Kelas</th>
                                <th>Judul Skripsi</th>
                                <th width="15%" class="text-center">Nilai Bimbingan (P1)</th>
                                <th width="15%" class="text-center">Nilai Sidang (P2)</th>
                                <th width="15%" class="text-center">Nilai Akhir</th>
                                <th width="10%" class="text-center">Nilai Huruf</th>
                                <th width="12%" class="text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($mahasiswa_penilaian)): ?>
                                <tr>
                                    <td colspan="10" class="text-center text-muted">
                                        <i class="fas fa-inbox"></i> Belum ada aktivitas penilaian Skripsi.
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php 
                                $this->load->helper('skripsi');
                                $no = 1;
                                foreach ($mahasiswa_penilaian as $item): 
                                    $skripsi = $item['skripsi'];
                                    $nilai_bimbingan_final = isset($item['nilai_bimbingan_final']) ? $item['nilai_bimbingan_final'] : null;
                                    $nilai_sidang_final = isset($item['nilai_sidang_final']) ? $item['nilai_sidang_final'] : null;
                                    $nilai_akhir = isset($item['nilai_akhir']) ? $item['nilai_akhir'] : null;
                                    
                                    // Re-konversi grade dari nilai akhir menggunakan helper function yang mengambil dari setting database
                                    $nilai_huruf = null;
                                    if ($nilai_akhir !== null && $nilai_akhir > 0) {
                                        $nilai_huruf = konversi_nilai_huruf_skripsi($nilai_akhir);
                                    } elseif (isset($item['nilai_huruf']) && !empty($item['nilai_huruf'])) {
                                        // Fallback: gunakan grade yang sudah ada jika nilai akhir tidak tersedia
                                        $nilai_huruf = $item['nilai_huruf'];
                                    }
                                ?>
                                    <tr>
                                        <td><?php echo $no++; ?></td>
                                        <td><strong><?php echo isset($skripsi->nim) ? htmlspecialchars($skripsi->nim) : '-'; ?></strong></td>
                                        <td><?php echo isset($skripsi->nama_mahasiswa) ? htmlspecialchars($skripsi->nama_mahasiswa) : '-'; ?></td>
                                        <td class="text-center">
                                            <span class="badge badge-info"><?php echo isset($skripsi->kelas) ? htmlspecialchars($skripsi->kelas) : '-'; ?></span>
                                        </td>
                                        <td>
                                            <?php 
                                            $judul = isset($item['judul_terbaru']) && !empty($item['judul_terbaru']) 
                                                ? $item['judul_terbaru'] 
                                                : (isset($skripsi->judul_skripsi) && !empty($skripsi->judul_skripsi) 
                                                    ? $skripsi->judul_skripsi 
                                                    : '-');
                                            echo htmlspecialchars($judul);
                                            ?>
                                        </td>
                                        <td class="text-center">
                                            <?php if ($nilai_bimbingan_final !== null && $nilai_bimbingan_final > 0): ?>
                                                <strong><?php echo number_format($nilai_bimbingan_final, 2); ?></strong>
                                            <?php else: ?>
                                                <span class="text-muted">-</span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="text-center">
                                            <?php if ($nilai_sidang_final !== null && $nilai_sidang_final > 0): ?>
                                                <strong><?php echo number_format($nilai_sidang_final, 2); ?></strong>
                                            <?php else: ?>
                                                <span class="text-muted">-</span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="text-center">
                                            <?php if ($nilai_akhir !== null && $nilai_akhir > 0): ?>
                                                <strong><?php echo number_format($nilai_akhir, 2); ?></strong>
                                            <?php else: ?>
                                                <span class="text-muted">-</span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="text-center">
                                            <?php if ($nilai_huruf !== null && !empty($nilai_huruf)): ?>
                                                <?php
                                                $grade_class = 'secondary';
                                                $grade_upper = strtoupper($nilai_huruf);
                                                // Support untuk grade seperti A, A-, B+, B, B-, C, D, E
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
                                                <span class="badge badge-<?php echo $grade_class; ?> badge-lg"><?php echo htmlspecialchars($nilai_huruf); ?></span>
                                            <?php else: ?>
                                                <span class="text-muted">-</span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="text-center">
                                            <a href="<?php echo site_url('Skripsi_aktivitas_penilaian/detail/' . $skripsi->id); ?>" class="btn btn-sm btn-info" title="Detail">
                                                <i class="fas fa-eye"></i> Detail
                                            </a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>


