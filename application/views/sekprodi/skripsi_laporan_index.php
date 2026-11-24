<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-file-alt"></i> Laporan Skripsi
                </h3>
                <div class="card-tools">
                    <a href="<?php echo site_url('Skripsi_laporan/export_rekap_nilai'); ?>" class="btn btn-primary btn-sm mr-2">
                        <i class="fas fa-table"></i> Rekap Nilai
                    </a>
                    <a href="<?php echo site_url('Skripsi_laporan/export_excel'); ?>" class="btn btn-success btn-sm">
                        <i class="fas fa-file-excel"></i> Download Excel
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

                <?php if ($this->session->flashdata('warning')): ?>
                    <div class="alert alert-warning alert-dismissible fade show">
                        <i class="fas fa-exclamation-triangle"></i> <?php echo $this->session->flashdata('warning'); ?>
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                <?php endif; ?>

                <?php if (isset($tahun_aktif) && $tahun_aktif): ?>
                    <div class="alert alert-info mb-3">
                        <i class="fas fa-info-circle"></i> 
                        <strong>Tahun Akademik Aktif:</strong> <?php echo $tahun_aktif->tahun_akademik . ' ' . ucfirst($tahun_aktif->semester); ?>
                    </div>
                <?php endif; ?>

                <div class="table-responsive">
                    <table class="table table-bordered table-striped table-hover" id="table-laporan">
                        <thead>
                            <tr>
                                <th width="3%" class="text-center">No</th>
                                <th width="8%">NIM</th>
                                <th width="12%">Nama Mahasiswa</th>
                                <th width="6%" class="text-center">Kelas</th>
                                <th width="20%">Judul Skripsi</th>
                                <th width="10%">Pembimbing 1</th>
                                <th width="10%">Pembimbing 2</th>
                                <th width="10%">Ketua Penguji</th>
                                <th width="10%">Anggota Penguji 1</th>
                                <th width="10%">Anggota Penguji 2</th>
                                <th width="8%" class="text-center">Tanggal Sidang</th>
                                <th width="8%" class="text-center">Jam</th>
                                <th width="8%" class="text-center">Ruangan</th>
                                <th width="6%" class="text-center">Nilai Bimbingan</th>
                                <th width="6%" class="text-center">Nilai Sidang</th>
                                <th width="6%" class="text-center">Nilai Akhir</th>
                                <th width="5%" class="text-center">Nilai Huruf</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($laporan_list)): ?>
                                <tr>
                                    <td colspan="17" class="text-center">Tidak ada data laporan Skripsi.</td>
                                </tr>
                            <?php else: ?>
                                <?php $no = 1; foreach ($laporan_list as $laporan): ?>
                                    <tr>
                                        <td class="text-center"><?php echo $no++; ?></td>
                                        <td><strong><?php echo htmlspecialchars($laporan->nim ?: '-'); ?></strong></td>
                                        <td><?php echo htmlspecialchars($laporan->nama_mahasiswa ?: '-'); ?></td>
                                        <td class="text-center">
                                            <span class="badge badge-info"><?php echo htmlspecialchars($laporan->kelas ?: '-'); ?></span>
                                        </td>
                                        <td><?php echo htmlspecialchars($laporan->judul_skripsi ?: '-'); ?></td>
                                        <td><?php echo htmlspecialchars($laporan->pembimbing_1_nama ?: '-'); ?></td>
                                        <td><?php echo htmlspecialchars($laporan->pembimbing_2_nama ?: '-'); ?></td>
                                        <td><?php echo htmlspecialchars($laporan->ketua_penguji ?: '-'); ?></td>
                                        <td><?php echo htmlspecialchars($laporan->anggota_penguji_1 ?: '-'); ?></td>
                                        <td><?php echo htmlspecialchars($laporan->anggota_penguji_2 ?: '-'); ?></td>
                                        <td class="text-center">
                                            <?php if ($laporan->tanggal_sidang): ?>
                                                <?php echo date('d/m/Y', strtotime($laporan->tanggal_sidang)); ?>
                                            <?php else: ?>
                                                <span class="text-muted">-</span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="text-center">
                                            <?php if (isset($laporan->jam_sidang) && $laporan->jam_sidang): ?>
                                                <?php echo htmlspecialchars($laporan->jam_sidang); ?>
                                            <?php else: ?>
                                                <span class="text-muted">-</span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="text-center">
                                            <?php if (isset($laporan->ruangan) && $laporan->ruangan): ?>
                                                <?php echo htmlspecialchars($laporan->ruangan); ?>
                                            <?php else: ?>
                                                <span class="text-muted">-</span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="text-center">
                                            <?php
                                            $nilai_bimbingan = isset($laporan->nilai_bimbingan_final) ? $laporan->nilai_bimbingan_final : null;
                                            if ($nilai_bimbingan !== null && $nilai_bimbingan !== '' && (is_numeric($nilai_bimbingan) || is_float($nilai_bimbingan))):
                                            ?>
                                                <strong><?php echo number_format((float)$nilai_bimbingan, 2); ?></strong>
                                            <?php else: ?>
                                                <span class="text-muted">-</span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="text-center">
                                            <?php
                                            $nilai_sidang = isset($laporan->nilai_sidang) ? $laporan->nilai_sidang : null;
                                            if ($nilai_sidang !== null && $nilai_sidang !== '' && (is_numeric($nilai_sidang) || is_float($nilai_sidang))):
                                            ?>
                                                <strong><?php echo number_format((float)$nilai_sidang, 2); ?></strong>
                                            <?php else: ?>
                                                <span class="text-muted">-</span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="text-center">
                                            <?php
                                            $nilai_akhir = isset($laporan->nilai_skripsi_final) ? $laporan->nilai_skripsi_final : null;
                                            if ($nilai_akhir !== null && $nilai_akhir !== '' && (is_numeric($nilai_akhir) || is_float($nilai_akhir))):
                                            ?>
                                                <strong class="text-primary"><?php echo number_format((float)$nilai_akhir, 2); ?></strong>
                                            <?php else: ?>
                                                <span class="text-muted">-</span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="text-center">
                                            <?php if (isset($laporan->nilai_huruf) && $laporan->nilai_huruf !== null && trim($laporan->nilai_huruf) !== ''): 
                                                $nilai_huruf = trim($laporan->nilai_huruf);
                                                $grade_upper = strtoupper($nilai_huruf);
                                                $badge_class = 'secondary';
                                                if (strpos($grade_upper, 'A') === 0) $badge_class = 'success';
                                                elseif (strpos($grade_upper, 'B') === 0) $badge_class = 'info';
                                                elseif (strpos($grade_upper, 'C') === 0) $badge_class = 'warning';
                                                elseif (strpos($grade_upper, 'D') === 0 || strpos($grade_upper, 'E') === 0) $badge_class = 'danger';
                                            ?>
                                                <span class="badge badge-<?php echo $badge_class; ?> badge-lg"><?php echo htmlspecialchars($nilai_huruf); ?></span>
                                            <?php else: ?>
                                                <span class="text-muted">-</span>
                                            <?php endif; ?>
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

<script>
    $(document).ready(function() {
        // Initialize DataTable if available
        if ($.fn.DataTable) {
            $('#table-laporan').DataTable({
                'paging': true,
                'lengthChange': true,
                'searching': true,
                'ordering': true,
                'info': true,
                'autoWidth': false,
                'responsive': true,
                'pageLength': 25,
                'language': {
                    'url': '//cdn.datatables.net/plug-ins/1.10.24/i18n/Indonesian.json'
                },
                'order': [[3, 'asc'], [1, 'asc']], // Sort by kelas, then NIM
                'scrollX': true
            });
        }
    });
</script>

