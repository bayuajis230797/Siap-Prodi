<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-calendar-alt"></i> Jadwal Mengajar
                </h3>
                <div class="card-tools">
                    <?php if ($tahun_aktif): ?>
                        <span class="badge badge-info">
                            <?php echo $tahun_aktif->tahun_akademik . ' ' . ucfirst($tahun_aktif->semester); ?>
                        </span>
                    <?php else: ?>
                        <span class="badge badge-warning">Tahun Akademik Belum Aktif</span>
                    <?php endif; ?>
                </div>
            </div>
            <div class="card-body">
                <?php if (!$tahun_aktif): ?>
                    <div class="alert alert-warning">
                        <i class="fas fa-exclamation-triangle"></i> 
                        <strong>Peringatan:</strong> Belum ada tahun akademik yang aktif. Jadwal mengajar tidak dapat ditampilkan.
                    </div>
                <?php else: ?>
                    <!-- Filter Hari -->
                    <div class="row mb-3">
                        <div class="col-md-12">
                            <div class="btn-group" role="group">
                                <a href="<?php echo site_url('Jadwal_mengajar'); ?>" 
                                   class="btn btn-sm <?php echo !$hari_filter ? 'btn-primary' : 'btn-outline-primary'; ?>">
                                    <i class="fas fa-list"></i> Semua Hari
                                </a>
                                <?php foreach ($hari_list as $hari): ?>
                                    <a href="<?php echo site_url('Jadwal_mengajar?hari=' . urlencode($hari)); ?>" 
                                       class="btn btn-sm <?php echo $hari_filter == $hari ? 'btn-primary' : 'btn-outline-primary'; ?>">
                                        <?php echo $hari; ?>
                                    </a>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>

                    <?php if (empty($jadwal_list)): ?>
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle"></i> 
                            Belum ada jadwal mengajar untuk periode ini.
                        </div>
                    <?php else: ?>
                        <?php if ($hari_filter): ?>
                            <!-- Tampilkan jadwal untuk hari tertentu -->
                            <div class="table-responsive">
                                <table class="table table-bordered table-striped table-hover">
                                    <thead>
                                        <tr>
                                            <th width="5%">No</th>
                                            <th width="15%">Kode MK</th>
                                            <th>Mata Kuliah</th>
                                            <th width="8%">SKS</th>
                                            <th width="12%">Kelas</th>
                                            <th width="12%">Jam</th>
                                            <th width="15%">Ruangan</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php $no = 1; foreach ($jadwal_list as $jadwal): ?>
                                            <tr>
                                                <td><?php echo $no++; ?></td>
                                                <td><strong><?php echo $jadwal->kode_mk; ?></strong></td>
                                                <td><?php echo $jadwal->nama_mk; ?></td>
                                                <td class="text-center">
                                                    <span class="badge badge-info"><?php echo $jadwal->sks; ?></span>
                                                </td>
                                                <td class="text-center">
                                                    <span class="badge badge-secondary"><?php echo $jadwal->kelas; ?></span>
                                                </td>
                                                <td>
                                                    <i class="fas fa-clock"></i> 
                                                    <?php echo date('H:i', strtotime($jadwal->jam_mulai)); ?> - 
                                                    <?php echo date('H:i', strtotime($jadwal->jam_selesai)); ?>
                                                </td>
                                                <td>
                                                    <i class="fas fa-door-open"></i> 
                                                    <?php echo $jadwal->nama_ruang ? $jadwal->nama_ruang : '-'; ?>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php else: ?>
                            <!-- Tampilkan jadwal per hari -->
                            <div class="row">
                                <?php foreach ($hari_list as $hari): ?>
                                    <?php if (!empty($jadwal_by_hari[$hari])): ?>
                                        <div class="col-md-6 mb-4">
                                            <div class="card card-primary card-outline">
                                                <div class="card-header">
                                                    <h3 class="card-title">
                                                        <i class="fas fa-calendar-day"></i> <?php echo $hari; ?>
                                                    </h3>
                                                    <div class="card-tools">
                                                        <span class="badge badge-primary">
                                                            <?php echo count($jadwal_by_hari[$hari]); ?> Jadwal
                                                        </span>
                                                    </div>
                                                </div>
                                                <div class="card-body p-0">
                                                    <table class="table table-sm table-hover mb-0">
                                                        <thead>
                                                            <tr>
                                                                <th width="15%">Jam</th>
                                                                <th>Mata Kuliah</th>
                                                                <th width="10%">Kelas</th>
                                                                <th width="15%">Ruangan</th>
                                                            </tr>
                                                        </thead>
                                                        <tbody>
                                                            <?php foreach ($jadwal_by_hari[$hari] as $jadwal): ?>
                                                                <tr>
                                                                    <td>
                                                                        <small>
                                                                            <i class="fas fa-clock text-primary"></i><br>
                                                                            <?php echo date('H:i', strtotime($jadwal->jam_mulai)); ?><br>
                                                                            <strong><?php echo date('H:i', strtotime($jadwal->jam_selesai)); ?></strong>
                                                                        </small>
                                                                    </td>
                                                                    <td>
                                                                        <strong><?php echo $jadwal->kode_mk; ?></strong><br>
                                                                        <small><?php echo $jadwal->nama_mk; ?></small><br>
                                                                        <span class="badge badge-info badge-sm"><?php echo $jadwal->sks; ?> SKS</span>
                                                                    </td>
                                                                    <td class="text-center">
                                                                        <span class="badge badge-secondary"><?php echo $jadwal->kelas; ?></span>
                                                                    </td>
                                                                    <td>
                                                                        <i class="fas fa-door-open"></i><br>
                                                                        <small><?php echo $jadwal->nama_ruang ? $jadwal->nama_ruang : '-'; ?></small>
                                                                    </td>
                                                                </tr>
                                                            <?php endforeach; ?>
                                                        </tbody>
                                                    </table>
                                                </div>
                                            </div>
                                        </div>
                                    <?php endif; ?>
                                <?php endforeach; ?>
                            </div>

                            <?php 
                            // Cek apakah ada hari yang tidak ada jadwal
                            $has_empty_days = false;
                            foreach ($hari_list as $hari) {
                                if (empty($jadwal_by_hari[$hari])) {
                                    $has_empty_days = true;
                                    break;
                                }
                            }
                            ?>

                            <?php if ($has_empty_days): ?>
                                <div class="alert alert-info mt-3">
                                    <i class="fas fa-info-circle"></i> 
                                    Hari yang tidak memiliki jadwal tidak ditampilkan.
                                </div>
                            <?php endif; ?>
                        <?php endif; ?>

                        <!-- Summary -->
                        <div class="row mt-3">
                            <div class="col-md-12">
                                <div class="card card-info">
                                    <div class="card-header">
                                        <h3 class="card-title">
                                            <i class="fas fa-chart-bar"></i> Ringkasan
                                        </h3>
                                    </div>
                                    <div class="card-body">
                                        <div class="row">
                                            <div class="col-md-3">
                                                <div class="info-box">
                                                    <span class="info-box-icon bg-info">
                                                        <i class="fas fa-book"></i>
                                                    </span>
                                                    <div class="info-box-content">
                                                        <span class="info-box-text">Total Mata Kuliah</span>
                                                        <span class="info-box-number">
                                                            <?php 
                                                            $unique_mk = array();
                                                            foreach ($jadwal_list as $j) {
                                                                if (!in_array($j->kode_mk, $unique_mk)) {
                                                                    $unique_mk[] = $j->kode_mk;
                                                                }
                                                            }
                                                            echo count($unique_mk);
                                                            ?>
                                                        </span>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md-3">
                                                <div class="info-box">
                                                    <span class="info-box-icon bg-success">
                                                        <i class="fas fa-calendar-check"></i>
                                                    </span>
                                                    <div class="info-box-content">
                                                        <span class="info-box-text">Total Jadwal</span>
                                                        <span class="info-box-number"><?php echo count($jadwal_list); ?></span>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md-3">
                                                <div class="info-box">
                                                    <span class="info-box-icon bg-warning">
                                                        <i class="fas fa-users"></i>
                                                    </span>
                                                    <div class="info-box-content">
                                                        <span class="info-box-text">Total Kelas</span>
                                                        <span class="info-box-number">
                                                            <?php 
                                                            $unique_kelas = array();
                                                            foreach ($jadwal_list as $j) {
                                                                if (!in_array($j->kelas, $unique_kelas)) {
                                                                    $unique_kelas[] = $j->kelas;
                                                                }
                                                            }
                                                            echo count($unique_kelas);
                                                            ?>
                                                        </span>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md-3">
                                                <div class="info-box">
                                                    <span class="info-box-icon bg-primary">
                                                        <i class="fas fa-clock"></i>
                                                    </span>
                                                    <div class="info-box-content">
                                                        <span class="info-box-text">Total SKS</span>
                                                        <span class="info-box-number">
                                                            <?php 
                                                            $total_sks = 0;
                                                            foreach ($jadwal_list as $j) {
                                                                $total_sks += $j->sks;
                                                            }
                                                            echo $total_sks;
                                                            ?>
                                                        </span>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<style>
.badge-sm {
    font-size: 0.75rem;
    padding: 0.25rem 0.5rem;
}
</style>
