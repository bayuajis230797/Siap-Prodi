<div class="row">
    <div class="col-12">
        <div class="card card-primary card-outline">
            <div class="card-header">
                <h3 class="card-title">Honor Skripsi</h3>
                <div class="card-tools">
                    <a href="<?php echo site_url('Honor_skripsi/export_excel'); ?>" 
                       class="btn btn-info btn-sm">
                        <i class="fas fa-file-excel"></i> Download Excel
                    </a>
                </div>
            </div>
            <div class="card-body">
                <?php if ($this->session->flashdata('success')): ?>
                    <div class="alert alert-success alert-dismissible">
                        <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
                        <?php echo $this->session->flashdata('success'); ?>
                    </div>
                <?php endif; ?>

                <?php if ($this->session->flashdata('warning')): ?>
                    <div class="alert alert-warning alert-dismissible">
                        <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
                        <?php echo $this->session->flashdata('warning'); ?>
                    </div>
                <?php endif; ?>

                <?php if ($this->session->flashdata('error')): ?>
                    <div class="alert alert-danger alert-dismissible">
                        <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
                        <?php echo $this->session->flashdata('error'); ?>
                    </div>
                <?php endif; ?>

                <?php if (isset($tahun_aktif) && $tahun_aktif): ?>
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle"></i> 
                        <strong>Tahun Akademik:</strong> <?php echo $tahun_aktif->tahun_akademik . ' ' . ucfirst($tahun_aktif->semester); ?>
                    </div>
                <?php else: ?>
                    <div class="alert alert-warning">
                        <i class="fas fa-exclamation-triangle"></i> 
                        <strong>Perhatian:</strong> Belum ada tahun akademik yang aktif.
                    </div>
                <?php endif; ?>

                <!-- Info Besaran Honor -->
                <div class="card mb-3">
                    <div class="card-header bg-info">
                        <h5 class="mb-0"><i class="fas fa-info-circle"></i> Besaran Honor</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <h6>Pembimbing:</h6>
                                <ul>
                                    <li>Pembimbing 1: Rp <?php echo number_format($honor_p1, 0, ',', '.'); ?> per mahasiswa</li>
                                    <li>Pembimbing 2: Rp <?php echo number_format($honor_p2, 0, ',', '.'); ?> per mahasiswa</li>
                                </ul>
                            </div>
                            <div class="col-md-6">
                                <h6>Penguji:</h6>
                                <ul>
                                    <li>Ketua Penguji: Rp <?php echo number_format($honor_ketua, 0, ',', '.'); ?> per ujian</li>
                                    <li>Anggota Penguji 1: Rp <?php echo number_format($honor_anggota_1, 0, ',', '.'); ?> per ujian</li>
                                    <li>Anggota Penguji 2: Rp <?php echo number_format($honor_anggota_2, 0, ',', '.'); ?> per ujian</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Summary Total -->
                <?php 
                $total_honor_pembimbing_all = 0;
                $total_honor_penguji_all = 0;
                $grand_total_honor = 0;
                
                if (!empty($honor_all)) {
                    foreach ($honor_all as $item) {
                        $total_honor_pembimbing_all += isset($item['total_honor_pembimbing']) ? $item['total_honor_pembimbing'] : 0;
                        $total_honor_penguji_all += isset($item['total_honor_penguji']) ? $item['total_honor_penguji'] : 0;
                        $grand_total_honor += isset($item['total_honor']) ? $item['total_honor'] : 0;
                    }
                }
                ?>
                
                <?php if (!empty($honor_all)): ?>
                    <div class="row mb-3">
                        <div class="col-md-4">
                            <div class="info-box">
                                <span class="info-box-icon bg-info elevation-1"><i class="fas fa-user-tie"></i></span>
                                <div class="info-box-content">
                                    <span class="info-box-text">Total Honor Pembimbing</span>
                                    <span class="info-box-number">Rp <?php echo number_format($total_honor_pembimbing_all, 0, ',', '.'); ?></span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="info-box">
                                <span class="info-box-icon bg-warning elevation-1"><i class="fas fa-gavel"></i></span>
                                <div class="info-box-content">
                                    <span class="info-box-text">Total Honor Penguji</span>
                                    <span class="info-box-number">Rp <?php echo number_format($total_honor_penguji_all, 0, ',', '.'); ?></span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="info-box">
                                <span class="info-box-icon bg-success elevation-1"><i class="fas fa-money-bill-wave"></i></span>
                                <div class="info-box-content">
                                    <span class="info-box-text">Total Honor Keseluruhan</span>
                                    <span class="info-box-number">Rp <?php echo number_format($grand_total_honor, 0, ',', '.'); ?></span>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>

                <!-- Table Honor -->
                <div class="table-responsive">
                    <table class="table table-bordered table-striped table-hover" id="table-honor-skripsi">
                        <thead>
                            <tr>
                                <th rowspan="2" width="3%" class="text-center">No</th>
                                <th rowspan="2" width="10%">NIDN</th>
                                <th rowspan="2" width="20%">Nama Dosen</th>
                                <th colspan="3" class="text-center">Pembimbing</th>
                                <th colspan="4" class="text-center">Penguji</th>
                                <th rowspan="2" width="12%" class="text-center">Total Honor</th>
                            </tr>
                            <tr>
                                <th width="6%" class="text-center">P1</th>
                                <th width="6%" class="text-center">P2</th>
                                <th width="10%" class="text-center">Subtotal</th>
                                <th width="6%" class="text-center">Ketua</th>
                                <th width="6%" class="text-center">Anggota 1</th>
                                <th width="6%" class="text-center">Anggota 2</th>
                                <th width="10%" class="text-center">Subtotal</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($honor_all)): ?>
                                <tr>
                                    <td colspan="11" class="text-center">Tidak ada data honor Skripsi.</td>
                                </tr>
                            <?php else: ?>
                                <?php $no = 1; ?>
                                <?php foreach ($honor_all as $item): ?>
                                    <tr>
                                        <td class="text-center"><?php echo $no++; ?></td>
                                        <td><strong><?php echo htmlspecialchars(isset($item['nidn']) ? $item['nidn'] : '-'); ?></strong></td>
                                        <td><?php echo htmlspecialchars(isset($item['nama_dosen']) ? $item['nama_dosen'] : '-'); ?></td>
                                        <td class="text-center">
                                            <?php if (isset($item['jumlah_pembimbing_1']) && $item['jumlah_pembimbing_1'] > 0): ?>
                                                <?php echo $item['jumlah_pembimbing_1']; ?><br>
                                                <small class="text-muted">(Rp <?php echo number_format(isset($item['honor_p1']) ? $item['honor_p1'] : 0, 0, ',', '.'); ?>)</small>
                                            <?php else: ?>
                                                <span class="text-muted">-</span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="text-center">
                                            <?php if (isset($item['jumlah_pembimbing_2']) && $item['jumlah_pembimbing_2'] > 0): ?>
                                                <?php echo $item['jumlah_pembimbing_2']; ?><br>
                                                <small class="text-muted">(Rp <?php echo number_format(isset($item['honor_p2']) ? $item['honor_p2'] : 0, 0, ',', '.'); ?>)</small>
                                            <?php else: ?>
                                                <span class="text-muted">-</span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="text-center">
                                            <strong>Rp <?php echo number_format(isset($item['total_honor_pembimbing']) ? $item['total_honor_pembimbing'] : 0, 0, ',', '.'); ?></strong>
                                        </td>
                                        <td class="text-center">
                                            <?php if (isset($item['jumlah_ketua']) && $item['jumlah_ketua'] > 0): ?>
                                                <?php echo $item['jumlah_ketua']; ?><br>
                                                <small class="text-muted">(Rp <?php echo number_format(isset($item['honor_ketua']) ? $item['honor_ketua'] : 0, 0, ',', '.'); ?>)</small>
                                            <?php else: ?>
                                                <span class="text-muted">-</span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="text-center">
                                            <?php if (isset($item['jumlah_anggota_1']) && $item['jumlah_anggota_1'] > 0): ?>
                                                <?php echo $item['jumlah_anggota_1']; ?><br>
                                                <small class="text-muted">(Rp <?php echo number_format(isset($item['honor_anggota_1']) ? $item['honor_anggota_1'] : 0, 0, ',', '.'); ?>)</small>
                                            <?php else: ?>
                                                <span class="text-muted">-</span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="text-center">
                                            <?php if (isset($item['jumlah_anggota_2']) && $item['jumlah_anggota_2'] > 0): ?>
                                                <?php echo $item['jumlah_anggota_2']; ?><br>
                                                <small class="text-muted">(Rp <?php echo number_format(isset($item['honor_anggota_2']) ? $item['honor_anggota_2'] : 0, 0, ',', '.'); ?>)</small>
                                            <?php else: ?>
                                                <span class="text-muted">-</span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="text-center">
                                            <strong>Rp <?php echo number_format(isset($item['total_honor_penguji']) ? $item['total_honor_penguji'] : 0, 0, ',', '.'); ?></strong>
                                        </td>
                                        <td class="text-center">
                                            <strong class="text-success">Rp <?php echo number_format(isset($item['total_honor']) ? $item['total_honor'] : 0, 0, ',', '.'); ?></strong>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                                <!-- Total Row -->
                                <?php
                                $total_p1 = 0;
                                $total_p2 = 0;
                                $total_ketua = 0;
                                $total_anggota_1 = 0;
                                $total_anggota_2 = 0;
                                
                                foreach ($honor_all as $item) {
                                    $total_p1 += isset($item['jumlah_pembimbing_1']) ? $item['jumlah_pembimbing_1'] : 0;
                                    $total_p2 += isset($item['jumlah_pembimbing_2']) ? $item['jumlah_pembimbing_2'] : 0;
                                    $total_ketua += isset($item['jumlah_ketua']) ? $item['jumlah_ketua'] : 0;
                                    $total_anggota_1 += isset($item['jumlah_anggota_1']) ? $item['jumlah_anggota_1'] : 0;
                                    $total_anggota_2 += isset($item['jumlah_anggota_2']) ? $item['jumlah_anggota_2'] : 0;
                                }
                                ?>
                                <tr class="bg-light">
                                    <td colspan="3" class="text-right"><strong>TOTAL</strong></td>
                                    <td class="text-center"><strong><?php echo $total_p1; ?></strong></td>
                                    <td class="text-center"><strong><?php echo $total_p2; ?></strong></td>
                                    <td class="text-center">
                                        <strong>Rp <?php echo number_format($total_honor_pembimbing_all, 0, ',', '.'); ?></strong>
                                    </td>
                                    <td class="text-center"><strong><?php echo $total_ketua; ?></strong></td>
                                    <td class="text-center"><strong><?php echo $total_anggota_1; ?></strong></td>
                                    <td class="text-center"><strong><?php echo $total_anggota_2; ?></strong></td>
                                    <td class="text-center">
                                        <strong>Rp <?php echo number_format($total_honor_penguji_all, 0, ',', '.'); ?></strong>
                                    </td>
                                    <td class="text-center">
                                        <strong class="text-success">Rp <?php echo number_format($grand_total_honor, 0, ',', '.'); ?></strong>
                                    </td>
                                </tr>
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
            $('#table-honor-skripsi').DataTable({
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
                'order': [[2, 'asc']], // Sort by nama dosen
                'scrollX': true
            });
        }
    });
</script>




