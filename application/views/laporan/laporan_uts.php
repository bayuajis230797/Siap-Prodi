<?php
$jadwal_list = isset($jadwal_list) && is_array($jadwal_list) ? $jadwal_list : array();
$kelas_list = isset($kelas_list) && is_array($kelas_list) ? $kelas_list : array();
$filter_tanggal = isset($filter_tanggal) ? $filter_tanggal : '';
$filter_kelas = isset($filter_kelas) ? $filter_kelas : '';
$filter_kode_mk = isset($filter_kode_mk) ? $filter_kode_mk : '';
$tahun_aktif = isset($tahun_aktif) ? $tahun_aktif : null;
?>

<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-file-alt me-1"></i> Laporan Lengkap UTS
                </h3>
                <div class="card-tools">
                    <a href="<?php echo site_url('Laporan/export_mahasiswa_tidak_hadir_uts'); ?>" 
                       class="btn btn-success btn-sm">
                        <i class="fas fa-file-excel"></i> Download Excel - Daftar Mahasiswa Tidak Hadir
                    </a>
                </div>
            </div>
            <div class="card-body">
                <?php if ($tahun_aktif): ?>
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle"></i>
                        <strong>Tahun Akademik:</strong>
                        <?php echo $tahun_aktif->tahun_akademik . ' ' . ucfirst($tahun_aktif->semester); ?>
                    </div>
                <?php endif; ?>

                <!-- Filter -->
                <form method="get" action="<?php echo site_url('Laporan/laporan_uts'); ?>" class="mb-4">
                    <div class="row">
                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="filter_tanggal">Tanggal Ujian</label>
                                <input type="date" name="tanggal" id="filter_tanggal" class="form-control"
                                       value="<?php echo htmlspecialchars($filter_tanggal); ?>">
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="filter_kelas">Kelas</label>
                                <select name="kelas" id="filter_kelas" class="form-control">
                                    <option value="">-- Semua Kelas --</option>
                                    <?php foreach ($kelas_list as $kelas): ?>
                                        <option value="<?php echo $kelas->kelas; ?>" <?php echo ($filter_kelas === $kelas->kelas) ? 'selected' : ''; ?>>
                                            <?php echo $kelas->kelas; ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="filter_kode_mk">Kode Mata Kuliah</label>
                                <input type="text" name="kode_mk" id="filter_kode_mk" class="form-control"
                                       value="<?php echo htmlspecialchars($filter_kode_mk); ?>" placeholder="Contoh: SIF3001">
                            </div>
                        </div>
                        <div class="col-md-3 d-flex align-items-end">
                            <button type="submit" class="btn btn-primary mr-2">
                                <i class="fas fa-filter"></i> Filter
                            </button>
                            <a href="<?php echo site_url('Laporan/laporan_uts'); ?>" class="btn btn-secondary">
                                <i class="fas fa-undo"></i> Reset
                            </a>
                        </div>
                    </div>
                </form>

                <!-- Daftar Jadwal -->
                <?php if (!empty($jadwal_list)): ?>
                    <div class="mb-3">
                        <strong>Total Jadwal: <?php echo count($jadwal_list); ?></strong>
                    </div>

                    <?php foreach ($jadwal_list as $jadwal): ?>
                        <div class="card card-outline card-primary mb-3">
                            <div class="card-header">
                                <h5 class="card-title mb-0">
                                    <i class="fas fa-calendar-check"></i>
                                    <?php echo $jadwal->kode_mk; ?> - <?php echo $jadwal->nama_mk; ?>
                                    <span class="badge badge-info ml-2"><?php echo $jadwal->kelas; ?></span>
                                    <?php if ($jadwal->sesi == 2): ?>
                                        <span class="badge badge-warning ml-1">Sesi 2</span>
                                    <?php else: ?>
                                        <span class="badge badge-success ml-1">Sesi 1</span>
                                    <?php endif; ?>
                                </h5>
                            </div>
                            <div class="card-body">
                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <table class="table table-sm table-borderless">
                                            <tr>
                                                <th width="40%">Tanggal</th>
                                                <td><?php echo $jadwal->tanggal ? date('d/m/Y', strtotime($jadwal->tanggal)) : '-'; ?></td>
                                            </tr>
                                            <tr>
                                                <th>Jam</th>
                                                <td>
                                                    <?php 
                                                    if ($jadwal->jam_mulai && $jadwal->jam_selesai) {
                                                        echo date('H:i', strtotime($jadwal->jam_mulai)) . ' - ' . date('H:i', strtotime($jadwal->jam_selesai));
                                                    } else {
                                                        echo '-';
                                                    }
                                                    ?>
                                                </td>
                                            </tr>
                                            <tr>
                                                <th>Ruang</th>
                                                <td><?php echo htmlspecialchars($jadwal->nama_ruang ?? ($jadwal->kode_ruang ?? '-')); ?></td>
                                            </tr>
                                            <tr>
                                                <th>SKS</th>
                                                <td><?php echo $jadwal->sks ?? '-'; ?></td>
                                            </tr>
                                        </table>
                                    </div>
                                    <div class="col-md-6">
                                        <table class="table table-sm table-borderless">
                                            <tr>
                                                <th width="40%">Dosen Pengampu</th>
                                                <td><?php echo htmlspecialchars($jadwal->nama_dosen ?? '-'); ?> (<?php echo $jadwal->nidn ?? '-'; ?>)</td>
                                            </tr>
                                            <tr>
                                                <th>Pengawas</th>
                                                <td>
                                                    <?php if (!empty($jadwal->pengawas)): ?>
                                                        <?php 
                                                        $pengawas_names = array();
                                                        foreach ($jadwal->pengawas as $p) {
                                                            if ($p->id_dosen_full) {
                                                                $pengawas_names[] = $p->nama_dosen . ' (Dosen)';
                                                            } elseif ($p->id_tendik_full) {
                                                                $pengawas_names[] = $p->nama_tendik . ' (Tendik)';
                                                            }
                                                        }
                                                        echo implode(', ', $pengawas_names);
                                                        ?>
                                                    <?php else: ?>
                                                        <span class="text-muted">Belum ada pengawas</span>
                                                    <?php endif; ?>
                                                </td>
                                            </tr>
                                        </table>
                                    </div>
                                </div>

                                <!-- Statistik Kehadiran -->
                                <?php if ($jadwal->total_mahasiswa > 0): ?>
                                    <div class="row mb-3">
                                        <div class="col-12">
                                            <h6><i class="fas fa-users"></i> Statistik Kehadiran</h6>
                                            <div class="row">
                                                <div class="col-md-3">
                                                    <div class="info-box">
                                                        <span class="info-box-icon bg-success"><i class="fas fa-check"></i></span>
                                                        <div class="info-box-content">
                                                            <span class="info-box-text">Hadir</span>
                                                            <span class="info-box-number"><?php echo $jadwal->hadir; ?></span>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-md-3">
                                                    <div class="info-box">
                                                        <span class="info-box-icon bg-danger"><i class="fas fa-times"></i></span>
                                                        <div class="info-box-content">
                                                            <span class="info-box-text">Tidak Hadir</span>
                                                            <span class="info-box-number"><?php echo $jadwal->tidak_hadir; ?></span>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-md-3">
                                                    <div class="info-box">
                                                        <span class="info-box-icon bg-warning"><i class="fas fa-envelope"></i></span>
                                                        <div class="info-box-content">
                                                            <span class="info-box-text">Izin</span>
                                                            <span class="info-box-number"><?php echo $jadwal->izin; ?></span>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-md-3">
                                                    <div class="info-box">
                                                        <span class="info-box-icon bg-info"><i class="fas fa-thermometer-half"></i></span>
                                                        <div class="info-box-content">
                                                            <span class="info-box-text">Sakit</span>
                                                            <span class="info-box-number"><?php echo $jadwal->sakit; ?></span>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Daftar Kehadiran Mahasiswa -->
                                    <div class="table-responsive">
                                        <h6><i class="fas fa-list"></i> Daftar Kehadiran Mahasiswa</h6>
                                        <table class="table table-sm table-bordered table-striped">
                                            <thead>
                                                <tr>
                                                    <th width="5%">No</th>
                                                    <th width="15%">NIM</th>
                                                    <th width="30%">Nama Mahasiswa</th>
                                                    <th width="15%">Status</th>
                                                    <th>Keterangan</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php if (!empty($jadwal->kehadiran)): ?>
                                                    <?php $no = 1; foreach ($jadwal->kehadiran as $k): ?>
                                                        <tr>
                                                            <td><?php echo $no++; ?></td>
                                                            <td><?php echo $k->nim; ?></td>
                                                            <td><?php echo $k->nama_mahasiswa; ?></td>
                                                            <td>
                                                                <?php
                                                                $badge_class = '';
                                                                $status_text = '';
                                                                switch ($k->status_kehadiran) {
                                                                    case 'hadir':
                                                                        $badge_class = 'success';
                                                                        $status_text = 'Hadir';
                                                                        break;
                                                                    case 'tidak_hadir':
                                                                        $badge_class = 'danger';
                                                                        $status_text = 'Tidak Hadir';
                                                                        break;
                                                                    case 'izin':
                                                                        $badge_class = 'warning';
                                                                        $status_text = 'Izin';
                                                                        break;
                                                                    case 'sakit':
                                                                        $badge_class = 'info';
                                                                        $status_text = 'Sakit';
                                                                        break;
                                                                }
                                                                ?>
                                                                <span class="badge badge-<?php echo $badge_class; ?>"><?php echo $status_text; ?></span>
                                                            </td>
                                                            <td><?php echo htmlspecialchars($k->keterangan ?? '-'); ?></td>
                                                        </tr>
                                                    <?php endforeach; ?>
                                                <?php else: ?>
                                                    <tr>
                                                        <td colspan="5" class="text-center text-muted">Belum ada data kehadiran</td>
                                                    </tr>
                                                <?php endif; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                <?php else: ?>
                                    <div class="alert alert-warning">
                                        <i class="fas fa-exclamation-triangle"></i> Belum ada data kehadiran mahasiswa untuk jadwal ini.
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle"></i> Tidak ada data jadwal UTS yang ditemukan.
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

