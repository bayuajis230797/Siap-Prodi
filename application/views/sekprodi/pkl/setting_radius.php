<div class="row">
    <div class="col-md-12">
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

        <?php if ($this->session->flashdata('info')): ?>
            <div class="alert alert-info alert-dismissible fade show">
                <i class="fas fa-info-circle"></i> <?php echo $this->session->flashdata('info'); ?>
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
        <?php endif; ?>
    </div>
</div>

<div class="row">
    <div class="col-md-8">
        <div class="card card-primary card-outline">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-cog"></i> Pengaturan Radius Absensi PKL
                </h3>
            </div>
            <div class="card-body">
                <div class="alert alert-info">
                    <i class="fas fa-info-circle"></i> 
                    <strong>Informasi:</strong> Setting radius ini akan digunakan untuk validasi absensi harian PKL. 
                    Mahasiswa yang absen di luar radius akan tetap bisa absen, namun statusnya akan ditandai "di luar radius".
                </div>

                <?php if ($tahun_aktif): ?>
                    <div class="card bg-light mb-3">
                        <div class="card-body">
                            <h5 class="card-title">Tahun Akademik Aktif</h5>
                            <p class="mb-0">
                                <strong><?php echo htmlspecialchars($tahun_aktif->tahun_akademik); ?></strong> - 
                                Semester <?php echo $tahun_aktif->semester; ?>
                            </p>
                        </div>
                    </div>

                    <?php if ($setting_aktif): ?>
                        <div class="card bg-success text-white mb-3">
                            <div class="card-body">
                                <h5 class="card-title">Setting Radius Aktif</h5>
                                <p class="mb-0">
                                    <strong>Radius: <?php echo $setting_aktif->radius_meter; ?> meter</strong>
                                </p>
                                <small>Dibuat: <?php echo date('d/m/Y H:i', strtotime($setting_aktif->created_at)); ?></small>
                            </div>
                        </div>

                        <div class="text-center">
                            <a href="<?php echo site_url('Pkl_setting_radius/edit/' . $setting_aktif->id); ?>" class="btn btn-warning">
                                <i class="fas fa-edit"></i> Edit Setting Radius
                            </a>
                        </div>
                    <?php else: ?>
                        <div class="alert alert-warning">
                            <i class="fas fa-exclamation-triangle"></i> 
                            Belum ada setting radius untuk tahun akademik aktif. 
                            <a href="<?php echo site_url('Pkl_setting_radius/create'); ?>" class="alert-link">Buat setting radius sekarang</a>
                        </div>
                    <?php endif; ?>
                <?php else: ?>
                    <div class="alert alert-danger">
                        <i class="fas fa-exclamation-circle"></i> Tidak ada tahun akademik aktif.
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <div class="col-md-4">
        <div class="card card-info card-outline">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-info-circle"></i> Informasi</h3>
            </div>
            <div class="card-body">
                <p><strong>Radius Absensi:</strong></p>
                <p class="text-muted">
                    Radius maksimal (dalam meter) yang diizinkan untuk absensi PKL. 
                    Jika mahasiswa absen di luar radius ini, absensi tetap tersimpan namun statusnya "di luar radius".
                </p>
                <p><strong>Rekomendasi:</strong></p>
                <ul class="text-muted">
                    <li>Minimal: 10 meter</li>
                    <li>Maksimal: 1000 meter</li>
                    <li>Default: 50 meter</li>
                </ul>
            </div>
        </div>
    </div>
</div>

<!-- Daftar Setting Radius -->
<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-list"></i> Daftar Setting Radius</h3>
                <div class="card-tools">
                    <a href="<?php echo site_url('Pkl_setting_radius/create'); ?>" class="btn btn-primary btn-sm">
                        <i class="fas fa-plus"></i> Tambah Setting Baru
                    </a>
                </div>
            </div>
            <div class="card-body table-responsive p-0">
                <?php if (empty($settings)): ?>
                    <div class="alert alert-info m-3">
                        <i class="fas fa-info-circle"></i> Belum ada setting radius.
                    </div>
                <?php else: ?>
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Tahun Akademik</th>
                                <th>Radius (Meter)</th>
                                <th>Dibuat Oleh</th>
                                <th>Tanggal Dibuat</th>
                                <th>Terakhir Diupdate</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($settings as $setting): ?>
                                <tr>
                                    <td>
                                        <?php if ($setting->tahun_akademik): ?>
                                            <strong><?php echo htmlspecialchars($setting->tahun_akademik); ?></strong>
                                            <?php if ($setting->semester): ?>
                                                - Semester <?php echo $setting->semester; ?>
                                            <?php endif; ?>
                                        <?php else: ?>
                                            <span class="text-muted">Global</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <span class="badge badge-primary"><?php echo $setting->radius_meter; ?> m</span>
                                    </td>
                                    <td><?php echo htmlspecialchars($setting->created_by_username); ?></td>
                                    <td><?php echo date('d/m/Y H:i', strtotime($setting->created_at)); ?></td>
                                    <td><?php echo date('d/m/Y H:i', strtotime($setting->updated_at)); ?></td>
                                    <td>
                                        <a href="<?php echo site_url('Pkl_setting_radius/edit/' . $setting->id); ?>" 
                                           class="btn btn-sm btn-warning" title="Edit">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <a href="<?php echo site_url('Pkl_setting_radius/delete/' . $setting->id); ?>" 
                                           class="btn btn-sm btn-danger" 
                                           onclick="return confirm('Apakah Anda yakin ingin menghapus setting radius ini?')" 
                                           title="Hapus">
                                            <i class="fas fa-trash"></i>
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

