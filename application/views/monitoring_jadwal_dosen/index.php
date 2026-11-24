<?php
$data_dosen = isset($data_dosen) && is_array($data_dosen) ? $data_dosen : array();
$dosen_list = isset($dosen_list) && is_array($dosen_list) ? $dosen_list : array();
$filter_dosen = isset($filter_dosen) ? $filter_dosen : '';
$filter_jenis = isset($filter_jenis) ? $filter_jenis : 'all';
?>

<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-chalkboard-teacher me-1"></i> Monitoring Jadwal Mengajar Dosen
                </h3>
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

                <?php if (!empty($tahun_aktif)): ?>
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle"></i>
                        <strong>Tahun Akademik Aktif:</strong>
                        <?php echo $tahun_aktif->tahun_akademik . ' ' . ucfirst($tahun_aktif->semester); ?>
                    </div>
                <?php else: ?>
                    <div class="alert alert-warning">
                        <i class="fas fa-exclamation-triangle"></i>
                        Belum ada tahun akademik yang aktif. Aktifkan terlebih dahulu untuk melihat data monitoring.
                    </div>
                <?php endif; ?>

                <form method="get" class="mb-4">
                    <div class="form-row">
                        <div class="form-group col-md-4">
                            <label for="filter_dosen">Dosen</label>
                            <select name="dosen" id="filter_dosen" class="form-control">
                                <option value="">-- Semua Dosen --</option>
                                <?php foreach ($dosen_list as $dosen): ?>
                                    <option value="<?php echo $dosen->id; ?>" <?php echo ($filter_dosen == $dosen->id) ? 'selected' : ''; ?>>
                                        <?php echo $dosen->nidn . ' - ' . $dosen->nama_dosen; ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="form-group col-md-4">
                            <label for="filter_jenis">Jenis Jadwal</label>
                            <select name="jenis" id="filter_jenis" class="form-control">
                                <option value="all" <?php echo ($filter_jenis == 'all') ? 'selected' : ''; ?>>Semua Jenis</option>
                                <option value="kuliah" <?php echo ($filter_jenis == 'kuliah') ? 'selected' : ''; ?>>Jadwal Kuliah</option>
                                <option value="uts" <?php echo ($filter_jenis == 'uts') ? 'selected' : ''; ?>>Jadwal UTS</option>
                                <option value="uas" <?php echo ($filter_jenis == 'uas') ? 'selected' : ''; ?>>Jadwal UAS</option>
                            </select>
                        </div>
                        <div class="form-group col-md-4 d-flex align-items-end">
                            <button type="submit" class="btn btn-primary mr-2">
                                <i class="fas fa-filter"></i> Terapkan Filter
                            </button>
                            <a href="<?php echo site_url('Monitoring_jadwal_dosen'); ?>" class="btn btn-secondary">
                                <i class="fas fa-undo"></i> Reset
                            </a>
                        </div>
                    </div>
                </form>

                <div class="d-flex justify-content-between align-items-center mb-2">
                    <strong>Total Data: <?php echo number_format(count($data_dosen)); ?></strong>
                    <?php if (!empty($data_dosen)): ?>
                        <a href="<?php echo site_url('Monitoring_jadwal_dosen/export_excel') . '?' . http_build_query($_GET); ?>" 
                           class="btn btn-success btn-sm">
                            <i class="fas fa-file-excel"></i> Export Excel
                        </a>
                    <?php endif; ?>
                </div>

                <?php if (empty($data_dosen)): ?>
                    <div class="alert alert-warning">
                        <i class="fas fa-info-circle"></i>
                        Tidak ada data jadwal dosen ditemukan.
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped table-hover" id="table-monitoring">
                            <thead>
                                <tr>
                                    <th>No</th>
                                    <th>Dosen</th>
                                    <th>NIDN</th>
                                    <th>Jenis Jadwal</th>
                                    <th>Hari/Tanggal</th>
                                    <th>Jam</th>
                                    <th>Kelas</th>
                                    <th>Mata Kuliah</th>
                                    <th>Ruangan</th>
                                    <th>Sesi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php 
                                $no = 1;
                                foreach ($data_dosen as $row): 
                                    $badge_class = '';
                                    if ($row['jenis'] == 'Kuliah') {
                                        $badge_class = 'badge-primary';
                                    } elseif ($row['jenis'] == 'UTS') {
                                        $badge_class = 'badge-warning';
                                    } elseif ($row['jenis'] == 'UAS') {
                                        $badge_class = 'badge-danger';
                                    }
                                ?>
                                    <tr class="<?php echo isset($row['bentrok']) && $row['bentrok'] ? 'table-danger' : ''; ?>" 
                                        <?php if (isset($row['bentrok']) && $row['bentrok']): ?>
                                            title="⚠️ BENTROK: Dosen ini memiliki jadwal bentrok pada waktu yang sama"
                                        <?php endif; ?>>
                                        <td><?php echo $no++; ?></td>
                                        <td>
                                            <strong><?php echo htmlspecialchars($row['dosen']); ?></strong>
                                            <?php if (isset($row['bentrok']) && $row['bentrok']): ?>
                                                <span class="badge badge-danger ml-1" title="Bentrok jadwal">
                                                    <i class="fas fa-exclamation-triangle"></i> BENTROK
                                                </span>
                                            <?php endif; ?>
                                        </td>
                                        <td><?php echo htmlspecialchars($row['nidn']); ?></td>
                                        <td>
                                            <span class="badge <?php echo $badge_class; ?>">
                                                <?php echo htmlspecialchars($row['jenis']); ?>
                                            </span>
                                        </td>
                                        <td><?php echo htmlspecialchars($row['hari_tanggal']); ?></td>
                                        <td>
                                            <?php 
                                            echo date('H:i', strtotime($row['jam_mulai'])) . ' - ' . date('H:i', strtotime($row['jam_selesai'])); 
                                            ?>
                                        </td>
                                        <td><?php echo htmlspecialchars($row['kelas']); ?></td>
                                        <td><?php echo htmlspecialchars($row['mata_kuliah']); ?></td>
                                        <td><?php echo htmlspecialchars($row['ruangan']); ?></td>
                                        <td>
                                            <?php 
                                            if ($row['sesi']) {
                                                echo 'Sesi ' . $row['sesi'];
                                            } else {
                                                echo '-';
                                            }
                                            ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    // Initialize DataTable if available
    if ($.fn.DataTable) {
        $('#table-monitoring').DataTable({
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
            'order': [[1, 'asc'], [4, 'asc'], [5, 'asc']] // Sort by dosen, hari/tanggal, jam
        });
    }
});
</script>



