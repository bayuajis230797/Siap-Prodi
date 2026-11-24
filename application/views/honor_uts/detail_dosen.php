<div class="row">
    <div class="col-12">
        <div class="card card-primary card-outline">
            <div class="card-header">
                <h3 class="card-title">Detail Honor UTS - <?php echo htmlspecialchars($dosen->nama_dosen); ?></h3>
                <div class="card-tools">
                    <a href="<?php echo site_url('Honor_uts'); ?>" class="btn btn-secondary btn-sm">
                        <i class="fas fa-arrow-left"></i> Kembali
                    </a>
                </div>
            </div>
            <div class="card-body">
                <?php if (isset($tahun_aktif) && $tahun_aktif): ?>
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle"></i> 
                        <strong>Tahun Akademik:</strong> <?php echo $tahun_aktif->tahun_akademik . ' ' . ucfirst($tahun_aktif->semester); ?>
                    </div>
                <?php endif; ?>

                <!-- Info Dosen -->
                <div class="card mb-3">
                    <div class="card-header bg-info">
                        <h5 class="mb-0"><i class="fas fa-user"></i> Informasi Dosen</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <p><strong>NIDN:</strong> <?php echo htmlspecialchars($dosen->nidn); ?></p>
                                <p><strong>Nama:</strong> <?php echo htmlspecialchars($dosen->nama_dosen); ?></p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Rincian Pembuatan Soal -->
                <div class="card mb-3">
                    <div class="card-header bg-success">
                        <h5 class="mb-0"><i class="fas fa-file-alt"></i> Rincian Pembuatan Soal</h5>
                    </div>
                    <div class="card-body">
                        <?php if (empty($detail['pembuatan_soal'])): ?>
                            <p class="text-muted">Tidak ada data pembuatan soal.</p>
                        <?php else: ?>
                            <div class="table-responsive">
                                <table class="table table-bordered table-striped table-hover">
                                    <thead>
                                        <tr>
                                            <th width="5%">No</th>
                                            <th width="10%">Tanggal</th>
                                            <th width="10%">Kode MK</th>
                                            <th width="20%">Nama Mata Kuliah</th>
                                            <th width="10%">Kelas</th>
                                            <th width="10%">Ruangan</th>
                                            <th width="8%">Besaran</th>
                                            <th width="7%">Total Honor</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php $no = 1; ?>
                                        <?php foreach ($detail['pembuatan_soal'] as $item): ?>
                                            <tr>
                                                <td><?php echo $no++; ?></td>
                                                <td><?php echo date('d/m/Y', strtotime($item->tanggal)); ?></td>
                                                <td><?php echo htmlspecialchars($item->kode_mk ?? '-'); ?></td>
                                                <td><?php echo htmlspecialchars($item->nama_mk ?? '-'); ?></td>
                                                <td class="text-center"><?php echo htmlspecialchars($item->kelas ?? '-'); ?></td>
                                                <td><?php echo htmlspecialchars($item->ruangan ?? '-'); ?></td>
                                                <td class="text-right">Rp. <?php echo number_format($item->besaran_per_item ?? 0, 0, ',', '.'); ?></td>
                                                <td class="text-right"><strong>Rp. <?php echo number_format($item->total_honor, 0, ',', '.'); ?></strong></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                    <tfoot>
                                        <tr class="bg-light">
                                            <th colspan="7" class="text-right">Subtotal:</th>
                                            <th class="text-right"><strong>Rp. <?php echo number_format($total_pembuatan_soal, 0, ',', '.'); ?></strong></th>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Rincian Koreksi -->
                <div class="card mb-3">
                    <div class="card-header bg-warning">
                        <h5 class="mb-0"><i class="fas fa-check-circle"></i> Rincian Koreksi</h5>
                    </div>
                    <div class="card-body">
                        <?php if (empty($detail['koreksi'])): ?>
                            <p class="text-muted">Tidak ada data koreksi.</p>
                        <?php else: ?>
                            <div class="table-responsive">
                                <table class="table table-bordered table-striped table-hover">
                                    <thead>
                                        <tr>
                                            <th width="5%">No</th>
                                            <th width="10%">Tanggal</th>
                                            <th width="10%">Kode MK</th>
                                            <th width="20%">Nama Mata Kuliah</th>
                                            <th width="10%">Kelas</th>
                                            <th width="10%">Ruangan</th>
                                            <th width="10%">Jml Mahasiswa</th>
                                            <th width="8%">Besaran</th>
                                            <th width="7%">Total Honor</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php $no = 1; ?>
                                        <?php foreach ($detail['koreksi'] as $item): ?>
                                            <tr>
                                                <td><?php echo $no++; ?></td>
                                                <td><?php echo date('d/m/Y', strtotime($item->tanggal)); ?></td>
                                                <td><?php echo htmlspecialchars($item->kode_mk ?? '-'); ?></td>
                                                <td><?php echo htmlspecialchars($item->nama_mk ?? '-'); ?></td>
                                                <td class="text-center"><?php echo htmlspecialchars($item->kelas ?? '-'); ?></td>
                                                <td><?php echo htmlspecialchars($item->ruangan ?? '-'); ?></td>
                                                <td class="text-center"><?php echo isset($item->total_mahasiswa) ? $item->total_mahasiswa : ($item->jumlah_item ?? 0); ?></td>
                                                <td class="text-right">Rp. <?php echo number_format($item->besaran_per_item ?? 0, 0, ',', '.'); ?></td>
                                                <td class="text-right"><strong>Rp. <?php echo number_format(isset($item->total_honor_koreksi) ? $item->total_honor_koreksi : $item->total_honor, 0, ',', '.'); ?></strong></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                    <tfoot>
                                        <tr class="bg-light">
                                            <th colspan="8" class="text-right">Subtotal:</th>
                                            <th class="text-right"><strong>Rp. <?php echo number_format($total_koreksi, 0, ',', '.'); ?></strong></th>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Rincian Pengawas -->
                <div class="card mb-3">
                    <div class="card-header bg-primary">
                        <h5 class="mb-0"><i class="fas fa-user-shield"></i> Rincian Pengawas</h5>
                    </div>
                    <div class="card-body">
                        <?php if (empty($detail['pengawas'])): ?>
                            <p class="text-muted">Tidak ada data pengawas.</p>
                        <?php else: ?>
                            <div class="table-responsive">
                                <table class="table table-bordered table-striped table-hover">
                                    <thead>
                                        <tr>
                                            <th width="5%">No</th>
                                            <th width="10%">Tanggal</th>
                                            <th width="8%">Sesi</th>
                                            <th width="12%">Jam</th>
                                            <th width="10%">Kode MK</th>
                                            <th width="20%">Nama Mata Kuliah</th>
                                            <th width="10%">Kelas</th>
                                            <th width="10%">Ruangan</th>
                                            <th width="8%">Besaran</th>
                                            <th width="7%">Total Honor</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php $no = 1; ?>
                                        <?php foreach ($detail['pengawas'] as $item): ?>
                                            <tr>
                                                <td><?php echo $no++; ?></td>
                                                <td><?php echo date('d/m/Y', strtotime($item->tanggal)); ?></td>
                                                <td class="text-center"><?php echo htmlspecialchars($item->sesi ?? '-'); ?></td>
                                                <td class="text-center"><?php echo date('H:i', strtotime($item->jam_mulai)) . ' - ' . date('H:i', strtotime($item->jam_selesai)); ?></td>
                                                <td><?php echo htmlspecialchars($item->kode_mk ?? '-'); ?></td>
                                                <td><?php echo htmlspecialchars($item->nama_mk ?? '-'); ?></td>
                                                <td class="text-center"><?php echo htmlspecialchars($item->kelas ?? '-'); ?></td>
                                                <td><?php echo htmlspecialchars($item->ruangan ?? '-'); ?></td>
                                                <td class="text-right">Rp. <?php echo number_format($item->besaran_per_item ?? 0, 0, ',', '.'); ?></td>
                                                <td class="text-right"><strong>Rp. <?php echo number_format($item->total_honor, 0, ',', '.'); ?></strong></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                    <tfoot>
                                        <tr class="bg-light">
                                            <th colspan="9" class="text-right">Subtotal:</th>
                                            <th class="text-right"><strong>Rp. <?php echo number_format($total_pengawas, 0, ',', '.'); ?></strong></th>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Total Honor -->
                <div class="card mb-3">
                    <div class="card-header bg-success">
                        <h5 class="mb-0"><i class="fas fa-calculator"></i> Total Honor</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <table class="table table-bordered">
                                    <tr>
                                        <th>Pembuatan Soal</th>
                                        <td class="text-right"><strong>Rp. <?php echo number_format($total_pembuatan_soal, 0, ',', '.'); ?></strong></td>
                                    </tr>
                                    <tr>
                                        <th>Koreksi</th>
                                        <td class="text-right"><strong>Rp. <?php echo number_format($total_koreksi, 0, ',', '.'); ?></strong></td>
                                    </tr>
                                    <tr>
                                        <th>Pengawas</th>
                                        <td class="text-right"><strong>Rp. <?php echo number_format($total_pengawas, 0, ',', '.'); ?></strong></td>
                                    </tr>
                                    <tr class="bg-light">
                                        <th><strong>TOTAL HONOR</strong></th>
                                        <td class="text-right"><strong class="text-success">Rp. <?php echo number_format($total_pembuatan_soal + $total_koreksi + $total_pengawas, 0, ',', '.'); ?></strong></td>
                                    </tr>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>



