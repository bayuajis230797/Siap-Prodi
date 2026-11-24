<div class="row">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Detail Tahun Akademik</h3>
                <div class="card-tools">
                    <a href="<?php echo site_url('Tahun_akademik'); ?>" class="btn btn-secondary btn-sm">
                        <i class="fas fa-arrow-left"></i> Kembali
                    </a>
                </div>
            </div>
            <div class="card-body">
                <table class="table table-bordered">
                    <tr>
                        <th width="30%">Tahun Akademik</th>
                        <td><?php echo $tahun_akademik->tahun_akademik; ?></td>
                    </tr>
                    <tr>
                        <th>Semester</th>
                        <td><?php echo ucfirst($tahun_akademik->semester); ?></td>
                    </tr>
                    <tr>
                        <th>Tanggal Mulai</th>
                        <td><?php echo $tahun_akademik->tgl_mulai ? date('d F Y', strtotime($tahun_akademik->tgl_mulai)) : '-'; ?></td>
                    </tr>
                    <tr>
                        <th>Tanggal Selesai</th>
                        <td><?php echo $tahun_akademik->tgl_selesai ? date('d F Y', strtotime($tahun_akademik->tgl_selesai)) : '-'; ?></td>
                    </tr>
                    <tr>
                        <th>Status</th>
                        <td>
                            <?php if ($tahun_akademik->is_aktif): ?>
                                <span class="badge badge-success">Aktif</span>
                            <?php else: ?>
                                <span class="badge badge-secondary">Tidak Aktif</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                </table>
            </div>
        </div>
    </div>

    <div class="col-md-4">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Statistik Mahasiswa per Semester</h3>
            </div>
            <div class="card-body">
                <?php if ($tahun_akademik->is_aktif): ?>
                    <table class="table table-sm">
                        <thead>
                            <tr>
                                <th>Semester</th>
                                <th class="text-right">Jumlah</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php for ($sem = 1; $sem <= 8; $sem++): ?>
                                <tr>
                                    <td>Semester <?php echo $sem; ?></td>
                                    <td class="text-right">
                                        <span class="badge badge-info"><?php echo $statistik_semester[$sem]; ?></span>
                                    </td>
                                </tr>
                            <?php endfor; ?>
                        </tbody>
                    </table>
                <?php else: ?>
                    <p class="text-muted">Statistik hanya tersedia untuk tahun akademik yang aktif.</p>
                <?php endif; ?>
            </div>
        </div>

        <?php if ($tahun_akademik->is_aktif && !empty($kurikulum_mk_aktif)): ?>
            <div class="card mt-3">
                <div class="card-header">
                    <h3 class="card-title">Mata Kuliah Aktif per Kurikulum</h3>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-sm table-bordered">
                            <thead>
                                <tr>
                                    <th>Kurikulum</th>
                                    <th>Tahun Berlaku</th>
                                    <th class="text-center">Semester Aktif</th>
                                    <th class="text-center">Jumlah MK Aktif</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($kurikulum_mk_aktif as $item): ?>
                                    <tr>
                                        <td><?php echo $item['kurikulum']->nama_kurikulum; ?></td>
                                        <td><?php echo $item['kurikulum']->tahun_berlaku; ?></td>
                                        <td class="text-center">
                                            <span class="badge badge-primary">Semester <?php echo $item['semester_aktif']; ?></span>
                                        </td>
                                        <td class="text-center">
                                            <span class="badge badge-success"><?php echo $item['jumlah_mk']; ?> MK</span>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

