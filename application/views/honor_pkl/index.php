<div class="row">
    <div class="col-12">
        <div class="card card-primary card-outline">
            <div class="card-header">
                <h3 class="card-title">Honor PKL</h3>
                <div class="card-tools">
                    <a href="<?php echo site_url('Honor_pkl/export_excel'); ?>" 
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
                        <ul>
                            <li>Pembimbing PKL: Rp <?php echo number_format($honor_pembimbing, 0, ',', '.'); ?> per mahasiswa</li>
                            <li>Penguji PKL: Rp <?php echo number_format($honor_penguji, 0, ',', '.'); ?> per ujian</li>
                        </ul>
                    </div>
                </div>

                <!-- Summary Total -->
                <?php if (!empty($rekap_dosen)): ?>
                    <div class="row mb-3">
                        <div class="col-md-4">
                            <div class="info-box">
                                <span class="info-box-icon bg-info elevation-1"><i class="fas fa-users"></i></span>
                                <div class="info-box-content">
                                    <span class="info-box-text">Total Honor Pembimbing</span>
                                    <span class="info-box-number">Rp <?php echo number_format($total_pembimbing, 0, ',', '.'); ?></span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="info-box">
                                <span class="info-box-icon bg-warning elevation-1"><i class="fas fa-gavel"></i></span>
                                <div class="info-box-content">
                                    <span class="info-box-text">Total Honor Penguji</span>
                                    <span class="info-box-number">Rp <?php echo number_format($total_penguji, 0, ',', '.'); ?></span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="info-box">
                                <span class="info-box-icon bg-success elevation-1"><i class="fas fa-money-bill-wave"></i></span>
                                <div class="info-box-content">
                                    <span class="info-box-text">Total Honor Keseluruhan</span>
                                    <span class="info-box-number">Rp <?php echo number_format($total_all, 0, ',', '.'); ?></span>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>

                <!-- Table Honor -->
                <div class="table-responsive">
                    <table class="table table-bordered table-striped table-hover" id="table-honor-pkl">
                        <thead>
                            <tr>
                                <th width="5%">No</th>
                                <th width="12%">NIDN</th>
                                <th width="25%">Nama Dosen</th>
                                <th width="12%" class="text-center">Jumlah Bimbingan</th>
                                <th width="15%" class="text-center">Honor Pembimbing</th>
                                <th width="12%" class="text-center">Jumlah Ujian</th>
                                <th width="15%" class="text-center">Honor Penguji</th>
                                <th width="15%" class="text-center">Total Honor</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($rekap_dosen)): ?>
                                <tr>
                                    <td colspan="8" class="text-center">Tidak ada data honor PKL untuk tahun akademik aktif.</td>
                                </tr>
                            <?php else: ?>
                                <?php $no = 1; ?>
                                <?php foreach ($rekap_dosen as $rekap): ?>
                                    <tr>
                                        <td><?php echo $no++; ?></td>
                                        <td><strong><?php echo htmlspecialchars($rekap->nidn); ?></strong></td>
                                        <td><?php echo htmlspecialchars($rekap->nama_dosen); ?></td>
                                        <td class="text-center">
                                            <?php if ($rekap->jumlah_mahasiswa_bimbingan > 0): ?>
                                                <span class="badge badge-info"><?php echo $rekap->jumlah_mahasiswa_bimbingan; ?> mahasiswa</span>
                                            <?php else: ?>
                                                <span class="text-muted">-</span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="text-center">
                                            <?php if ($rekap->total_honor_pembimbing > 0): ?>
                                                <strong>Rp <?php echo number_format($rekap->total_honor_pembimbing, 0, ',', '.'); ?></strong>
                                            <?php else: ?>
                                                <span class="text-muted">-</span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="text-center">
                                            <?php if ($rekap->jumlah_ujian > 0): ?>
                                                <span class="badge badge-warning"><?php echo $rekap->jumlah_ujian; ?> ujian</span>
                                            <?php else: ?>
                                                <span class="text-muted">-</span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="text-center">
                                            <?php if ($rekap->total_honor_penguji > 0): ?>
                                                <strong>Rp <?php echo number_format($rekap->total_honor_penguji, 0, ',', '.'); ?></strong>
                                            <?php else: ?>
                                                <span class="text-muted">-</span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="text-center">
                                            <strong class="text-success">Rp <?php echo number_format($rekap->total_honor_all, 0, ',', '.'); ?></strong>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                                <!-- Total Row -->
                                <?php
                                $total_jumlah_bimbingan = 0;
                                $total_jumlah_ujian = 0;
                                foreach ($rekap_dosen as $rekap) {
                                    $total_jumlah_bimbingan += $rekap->jumlah_mahasiswa_bimbingan ?: 0;
                                    $total_jumlah_ujian += $rekap->jumlah_ujian ?: 0;
                                }
                                ?>
                                <tr class="bg-light">
                                    <td colspan="3" class="text-right"><strong>TOTAL</strong></td>
                                    <td class="text-center">
                                        <strong><?php echo $total_jumlah_bimbingan; ?></strong>
                                    </td>
                                    <td class="text-center">
                                        <strong>Rp <?php echo number_format($total_pembimbing, 0, ',', '.'); ?></strong>
                                    </td>
                                    <td class="text-center">
                                        <strong><?php echo $total_jumlah_ujian; ?></strong>
                                    </td>
                                    <td class="text-center">
                                        <strong>Rp <?php echo number_format($total_penguji, 0, ',', '.'); ?></strong>
                                    </td>
                                    <td class="text-center">
                                        <strong class="text-success">Rp <?php echo number_format($total_all, 0, ',', '.'); ?></strong>
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
            $('#table-honor-pkl').DataTable({
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
                'order': [[2, 'asc']] // Sort by nama dosen
            });
        }
    });
</script>
