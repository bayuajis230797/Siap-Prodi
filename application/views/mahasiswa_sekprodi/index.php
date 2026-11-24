<?php
$mahasiswa = isset($mahasiswa) && is_array($mahasiswa) ? $mahasiswa : array();
$daftar_kelas = isset($daftar_kelas) && is_array($daftar_kelas) ? $daftar_kelas : array();
$daftar_angkatan = isset($daftar_angkatan) && is_array($daftar_angkatan) ? $daftar_angkatan : array();
$filter_kelas = isset($filter_kelas) ? $filter_kelas : '';
$filter_status = isset($filter_status) ? $filter_status : 'aktif';
$filter_angkatan = isset($filter_angkatan) ? $filter_angkatan : '';
$per_page = isset($per_page) ? $per_page : 25;
$total_rows = isset($total_rows) ? $total_rows : 0;
?>

<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Daftar Mahasiswa</h3>
                <div class="card-tools">
                    <?php if (!empty($filter_kelas)): ?>
                        <!-- Jika ada filter kelas, tampilkan tombol download langsung untuk kelas tersebut -->
                        <a href="<?php echo site_url('Mahasiswa_sekprodi/download_per_kelas?kelas=' . urlencode($filter_kelas)); ?>" class="btn btn-success btn-sm">
                            <i class="fas fa-file-excel"></i> Download Kelas <?php echo htmlspecialchars($filter_kelas); ?>
                        </a>
                    <?php else: ?>
                        <!-- Jika tidak ada filter kelas, tampilkan dropdown -->
                        <div class="btn-group">
                            <button type="button" class="btn btn-success btn-sm dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                <i class="fas fa-file-excel"></i> Download Per Kelas
                            </button>
                            <div class="dropdown-menu dropdown-menu-right" style="max-height: 300px; overflow-y: auto;">
                                <?php if (empty($daftar_kelas)): ?>
                                    <a class="dropdown-item text-muted" href="#">
                                        <i class="fas fa-info-circle"></i> Belum ada kelas
                                    </a>
                                <?php else: ?>
                                    <?php foreach ($daftar_kelas as $kelas_item): ?>
                                        <a class="dropdown-item" href="<?php echo site_url('Mahasiswa_sekprodi/download_per_kelas?kelas=' . urlencode($kelas_item->kelas)); ?>">
                                            <i class="fas fa-users"></i> Kelas <?php echo htmlspecialchars($kelas_item->kelas); ?>
                                        </a>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
            <div class="card-body">
                <!-- Filter -->
                <form method="get" action="<?php echo site_url('Mahasiswa_sekprodi/index'); ?>" class="mb-4">
                    <div class="row">
                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="filter_kelas">Kelas</label>
                                <select name="kelas" id="filter_kelas" class="form-control">
                                    <option value="">-- Semua Kelas --</option>
                                    <?php foreach ($daftar_kelas as $kelas_item): ?>
                                        <option value="<?php echo htmlspecialchars($kelas_item->kelas); ?>" <?php echo ($filter_kelas === $kelas_item->kelas) ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($kelas_item->kelas); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="filter_status">Status</label>
                                <select name="status" id="filter_status" class="form-control">
                                    <option value="aktif" <?php echo ($filter_status === 'aktif') ? 'selected' : ''; ?>>Aktif</option>
                                    <option value="lulus" <?php echo ($filter_status === 'lulus') ? 'selected' : ''; ?>>Lulus</option>
                                    <option value="cuti" <?php echo ($filter_status === 'cuti') ? 'selected' : ''; ?>>Cuti</option>
                                    <option value="nonaktif" <?php echo ($filter_status === 'nonaktif') ? 'selected' : ''; ?>>Nonaktif</option>
                                    <option value="">Semua Status</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="filter_angkatan">Angkatan</label>
                                <select name="angkatan" id="filter_angkatan" class="form-control">
                                    <option value="">-- Semua Angkatan --</option>
                                    <?php foreach ($daftar_angkatan as $angkatan_item): ?>
                                        <option value="<?php echo $angkatan_item->angkatan; ?>" <?php echo ($filter_angkatan == $angkatan_item->angkatan) ? 'selected' : ''; ?>>
                                            <?php echo $angkatan_item->angkatan; ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="per_page">Tampilkan</label>
                                <select name="per_page" id="per_page" class="form-control">
                                    <option value="10" <?php echo ($per_page == 10) ? 'selected' : ''; ?>>10 data</option>
                                    <option value="25" <?php echo ($per_page == 25) ? 'selected' : ''; ?>>25 data</option>
                                    <option value="50" <?php echo ($per_page == 50) ? 'selected' : ''; ?>>50 data</option>
                                    <option value="100" <?php echo ($per_page == 100) ? 'selected' : ''; ?>>100 data</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-12">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-filter"></i> Terapkan Filter
                            </button>
                            <a href="<?php echo site_url('Mahasiswa_sekprodi'); ?>" class="btn btn-secondary">
                                <i class="fas fa-undo"></i> Reset
                            </a>
                        </div>
                    </div>
                </form>

                <div class="d-flex justify-content-between align-items-center mb-2">
                    <strong>Total Data: <?php echo number_format($total_rows); ?></strong>
                </div>

                <div class="table-responsive">
                    <table class="table table-bordered table-striped table-hover">
                        <thead>
                            <tr>
                                <th width="5%">No</th>
                                <th width="12%">NIM</th>
                                <th>Nama Mahasiswa</th>
                                <th width="8%" class="text-center">Angkatan</th>
                                <th width="8%" class="text-center">Kelas</th>
                                <th width="10%" class="text-center">Semester Aktif</th>
                                <th width="15%">Kurikulum</th>
                                <th width="10%" class="text-center">Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($mahasiswa)): ?>
                                <tr>
                                    <td colspan="8" class="text-center text-muted">
                                        <i class="fas fa-inbox"></i> Belum ada data mahasiswa.
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php $no = ($this->uri->segment(3) ? ($this->uri->segment(3) - 1) * $per_page : 0) + 1; foreach ($mahasiswa as $m): ?>
                                    <tr>
                                        <td><?php echo $no++; ?></td>
                                        <td><strong><?php echo $m->nim; ?></strong></td>
                                        <td><?php echo $m->nama_mahasiswa; ?></td>
                                        <td class="text-center">
                                            <span class="badge badge-secondary"><?php echo $m->angkatan; ?></span>
                                        </td>
                                        <td class="text-center">
                                            <?php if ($m->kelas): ?>
                                                <span class="badge badge-info"><?php echo $m->kelas; ?></span>
                                            <?php else: ?>
                                                <span class="text-muted">-</span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="text-center">
                                            <span class="badge badge-primary">Semester <?php echo $m->semester_aktif; ?></span>
                                        </td>
                                        <td>
                                            <?php if ($m->nama_kurikulum): ?>
                                                <small>
                                                    <strong><?php echo $m->nama_kurikulum; ?></strong><br>
                                                    <span class="text-muted">Tahun <?php echo $m->tahun_berlaku; ?></span>
                                                </small>
                                            <?php else: ?>
                                                <span class="text-muted">-</span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="text-center">
                                            <?php
                                            $badge_class = 'secondary';
                                            if ($m->status == 'aktif') $badge_class = 'success';
                                            elseif ($m->status == 'lulus') $badge_class = 'info';
                                            elseif ($m->status == 'cuti') $badge_class = 'warning';
                                            ?>
                                            <span class="badge badge-<?php echo $badge_class; ?>">
                                                <?php echo ucfirst($m->status); ?>
                                            </span>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>

                <?php if (isset($pagination)): ?>
                    <div class="mt-3">
                        <?php echo $pagination; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

