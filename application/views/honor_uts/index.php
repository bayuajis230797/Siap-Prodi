<div class="row">
    <div class="col-12">
        <div class="card card-primary card-outline">
            <div class="card-header">
                <h3 class="card-title">Honor UTS</h3>
                <div class="card-tools">
                    <?php if (get_user_role() !== 'kaprodi'): ?>
                    <a href="<?php echo site_url('Honor_uts/generate'); ?>" 
                       class="btn btn-success btn-sm" 
                       onclick="return confirm('Generate honor UTS akan menghapus honor lama untuk tahun akademik aktif. Lanjutkan?')">
                        <i class="fas fa-sync"></i> Generate Honor UTS
                    </a>
                    <?php endif; ?>
                    <a href="<?php echo site_url('Honor_uts/export_excel'); ?>" 
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
                            <li>Pembuatan Soal: Rp <?php echo number_format($honor_soal, 0, ',', '.'); ?> per kelas</li>
                            <li>Koreksi: Rp <?php echo number_format($honor_koreksi, 0, ',', '.'); ?> per mahasiswa</li>
                            <li>Pengawas: Rp <?php echo number_format($honor_pengawas, 0, ',', '.'); ?> per jadwal</li>
                        </ul>
                    </div>
                </div>

                <!-- Table Honor -->
                <div class="table-responsive">
                    <table class="table table-bordered table-striped table-hover">
                        <thead>
                            <tr>
                                <th rowspan="2" width="5%">No</th>
                                <th rowspan="2" width="15%">NIDN / NIPY</th>
                                <th rowspan="2" width="20%">Nama</th>
                                <th colspan="2" class="text-center">Pembuatan Soal</th>
                                <th colspan="2" class="text-center">Koreksi</th>
                                <th colspan="2" class="text-center">Pengawas</th>
                                <th rowspan="2" width="12%" class="text-center">Total Honor</th>
                            </tr>
                            <tr>
                                <th width="10%" class="text-center">Jumlah (Kelas)</th>
                                <th width="10%" class="text-center">Subtotal</th>
                                <th width="10%" class="text-center">Jumlah (Mahasiswa)</th>
                                <th width="10%" class="text-center">Subtotal</th>
                                <th width="10%" class="text-center">Jumlah (Sesi)</th>
                                <th width="10%" class="text-center">Subtotal</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($honor_all)): ?>
                                <tr>
                                    <td colspan="11" class="text-center">Tidak ada data honor UTS.</td>
                                </tr>
                            <?php else: ?>
                                <?php $no = 1; ?>
                                <?php foreach ($honor_all as $item): ?>
                                    <tr>
                                        <td><?php echo $no++; ?></td>
                                        <td><?php echo htmlspecialchars($item['tipe'] == 'dosen' ? ($item['nidn'] ?? '-') : ($item['nipy'] ?? '-')); ?></td>
                                        <td>
                                            <?php if ($item['tipe'] == 'dosen'): ?>
                                                <a href="<?php echo site_url('Honor_uts/detail_dosen/' . $item['id_dosen']); ?>">
                                                    <?php echo htmlspecialchars($item['nama_dosen'] ?? ''); ?>
                                                </a>
                                            <?php else: ?>
                                                <a href="<?php echo site_url('Honor_uts/detail_tendik/' . $item['id_tendik']); ?>">
                                                    <?php echo htmlspecialchars($item['nama_tendik'] ?? ''); ?>
                                                </a>
                                            <?php endif; ?>
                                        </td>
                                        <td class="text-center">
                                            <?php echo $item['jumlah_soal']; ?><br>
                                            <small class="text-muted">(Rp <?php echo number_format($item['honor_soal_total'], 0, ',', '.'); ?>)</small>
                                        </td>
                                        <td class="text-center">
                                            <strong>Rp <?php echo number_format($item['honor_soal_total'], 0, ',', '.'); ?></strong>
                                        </td>
                                        <td class="text-center">
                                            <?php echo $item['jumlah_koreksi']; ?><br>
                                            <small class="text-muted">(Rp <?php echo number_format($item['honor_koreksi_total'], 0, ',', '.'); ?>)</small>
                                        </td>
                                        <td class="text-center">
                                            <strong>Rp <?php echo number_format($item['honor_koreksi_total'], 0, ',', '.'); ?></strong>
                                        </td>
                                        <td class="text-center">
                                            <?php echo $item['jumlah_pengawas']; ?><br>
                                            <small class="text-muted">(Rp <?php echo number_format($item['honor_pengawas_total'], 0, ',', '.'); ?>)</small>
                                        </td>
                                        <td class="text-center">
                                            <strong>Rp <?php echo number_format($item['honor_pengawas_total'], 0, ',', '.'); ?></strong>
                                        </td>
                                        <td class="text-center">
                                            <strong class="text-success">Rp <?php echo number_format($item['total_honor'], 0, ',', '.'); ?></strong>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                        <tfoot>
                            <?php if (!empty($honor_all)): ?>
                                <tr class="bg-light">
                                    <th colspan="3" class="text-right">TOTAL:</th>
                                    <th class="text-center">
                                        <?php 
                                        $total_soal = 0;
                                        $total_honor_soal = 0;
                                        $total_koreksi = 0;
                                        $total_honor_koreksi = 0;
                                        $total_pengawas = 0;
                                        $total_honor_pengawas = 0;
                                        $grand_total = 0;
                                        foreach ($honor_all as $item) {
                                            $total_soal += $item['jumlah_soal'];
                                            $total_honor_soal += $item['honor_soal_total'];
                                            $total_koreksi += $item['jumlah_koreksi'];
                                            $total_honor_koreksi += $item['honor_koreksi_total'];
                                            $total_pengawas += $item['jumlah_pengawas'];
                                            $total_honor_pengawas += $item['honor_pengawas_total'];
                                            $grand_total += $item['total_honor'];
                                        }
                                        echo $total_soal;
                                        ?>
                                    </th>
                                    <th class="text-center">
                                        <strong>Rp <?php echo number_format($total_honor_soal, 0, ',', '.'); ?></strong>
                                    </th>
                                    <th class="text-center">
                                        <?php echo $total_koreksi; ?>
                                    </th>
                                    <th class="text-center">
                                        <strong>Rp <?php echo number_format($total_honor_koreksi, 0, ',', '.'); ?></strong>
                                    </th>
                                    <th class="text-center">
                                        <?php echo $total_pengawas; ?>
                                    </th>
                                    <th class="text-center">
                                        <strong>Rp <?php echo number_format($total_honor_pengawas, 0, ',', '.'); ?></strong>
                                    </th>
                                    <th class="text-center">
                                        <strong class="text-success">Rp <?php echo number_format($grand_total, 0, ',', '.'); ?></strong>
                                    </th>
                                </tr>
                            <?php endif; ?>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>



