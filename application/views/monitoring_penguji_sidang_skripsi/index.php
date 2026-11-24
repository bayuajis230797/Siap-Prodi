<?php
$data_penguji = isset($data_penguji) && is_array($data_penguji) ? $data_penguji : array();
$dosen_list = isset($dosen_list) && is_array($dosen_list) ? $dosen_list : array();
$filter_dosen = isset($filter_dosen) ? $filter_dosen : '';
$filter_tanggal_mulai = isset($filter_tanggal_mulai) ? $filter_tanggal_mulai : '';
$filter_tanggal_selesai = isset($filter_tanggal_selesai) ? $filter_tanggal_selesai : '';
?>

<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-user-check me-1"></i> Monitoring Penguji Sidang Skripsi
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
                        <div class="form-group col-md-3">
                            <label for="filter_dosen">Penguji</label>
                            <select name="dosen" id="filter_dosen" class="form-control">
                                <option value="">-- Semua Penguji --</option>
                                <?php foreach ($dosen_list as $dosen): ?>
                                    <option value="<?php echo $dosen->id; ?>" <?php echo ($filter_dosen == $dosen->id) ? 'selected' : ''; ?>>
                                        <?php echo $dosen->nidn . ' - ' . $dosen->nama_dosen; ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="form-group col-md-2">
                            <label for="filter_tanggal_mulai">Tanggal Mulai</label>
                            <input type="date" name="tanggal_mulai" id="filter_tanggal_mulai" class="form-control" value="<?php echo htmlspecialchars($filter_tanggal_mulai); ?>">
                        </div>
                        <div class="form-group col-md-2">
                            <label for="filter_tanggal_selesai">Tanggal Selesai</label>
                            <input type="date" name="tanggal_selesai" id="filter_tanggal_selesai" class="form-control" value="<?php echo htmlspecialchars($filter_tanggal_selesai); ?>">
                        </div>
                        <div class="form-group col-md-3 d-flex align-items-end">
                            <button type="submit" class="btn btn-primary mr-2">
                                <i class="fas fa-filter"></i> Terapkan Filter
                            </button>
                            <a href="<?php echo site_url('Monitoring_penguji_sidang_skripsi'); ?>" class="btn btn-secondary">
                                <i class="fas fa-undo"></i> Reset
                            </a>
                        </div>
                    </div>
                </form>

                <div class="d-flex justify-content-between align-items-center mb-2">
                    <strong>Total Data: <?php echo number_format(count($data_penguji)); ?></strong>
                </div>

                <style>
                    /* Baris bentrok: background merah agar terlihat jelas */
                    #table-monitoring tbody tr.bentrok-jadwal,
                    #table-monitoring tbody tr.bentrok-jadwal:hover {
                        background-color: #f8d7da !important;
                    }
                    #table-monitoring tbody tr.bentrok-jadwal td {
                        background-color: transparent !important;
                    }
                </style>
                <?php if (empty($data_penguji)): ?>
                    <div class="alert alert-warning">
                        <i class="fas fa-info-circle"></i>
                        Tidak ada data jadwal sidang Skripsi ditemukan.
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped table-hover" id="table-monitoring">
                            <thead>
                                <tr>
                                    <th>No</th>
                                    <th>Penguji</th>
                                    <th>Role</th>
                                    <th>Tanggal</th>
                                    <th>Jam</th>
                                    <th>Ruangan</th>
                                    <th>Mahasiswa</th>
                                    <th>Kelas</th>
                                    <th>Pembimbing 1</th>
                                    <th>Pembimbing 2</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php 
                                $no = 1;
                                foreach ($data_penguji as $row): 
                                ?>
                                    <tr class="<?php echo isset($row['bentrok']) && $row['bentrok'] ? 'table-danger bentrok-jadwal' : ''; ?>" 
                                        <?php if (isset($row['bentrok']) && $row['bentrok']): ?>
                                            title="⚠️ BENTROK: Dosen penguji ini memiliki jadwal sidang bentrok (jam pelaksanaan bersamaan)"
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
                                        <td>
                                            <?php
                                            $role_badge = 'badge-secondary';
                                            if ($row['role'] == 'Ketua Penguji') {
                                                $role_badge = 'badge-primary';
                                            } elseif ($row['role'] == 'Anggota Penguji 1') {
                                                $role_badge = 'badge-info';
                                            } elseif ($row['role'] == 'Anggota Penguji 2') {
                                                $role_badge = 'badge-success';
                                            }
                                            ?>
                                            <span class="badge <?php echo $role_badge; ?>">
                                                <?php echo htmlspecialchars($row['role']); ?>
                                            </span>
                                        </td>
                                        <td><?php echo htmlspecialchars($row['hari_tanggal'] ?? ''); ?></td>
                                        <td>
                                            <?php 
                                            echo date('H:i', strtotime($row['jam_mulai'])) . ' - ' . date('H:i', strtotime($row['jam_selesai'])); 
                                            ?>
                                        </td>
                                        <td><?php echo htmlspecialchars($row['ruangan']); ?></td>
                                        <td><?php echo htmlspecialchars($row['mahasiswa']); ?></td>
                                        <td><?php echo htmlspecialchars($row['kelas']); ?></td>
                                        <td><?php echo htmlspecialchars($row['pembimbing_1'] ?? '-'); ?></td>
                                        <td><?php echo htmlspecialchars($row['pembimbing_2'] ?? '-'); ?></td>
                                        <td>
                                            <?php
                                            $status_badge = 'badge-secondary';
                                            if ($row['status'] == 'terjadwal') {
                                                $status_badge = 'badge-info';
                                            } elseif ($row['status'] == 'selesai') {
                                                $status_badge = 'badge-success';
                                            } elseif ($row['status'] == 'batal') {
                                                $status_badge = 'badge-danger';
                                            }
                                            ?>
                                            <span class="badge <?php echo $status_badge; ?>">
                                                <?php echo ucfirst($row['status']); ?>
                                            </span>
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
            'order': [[1, 'asc'], [3, 'asc'], [4, 'asc']] // Sort by penguji, tanggal, jam
        });
    }
});
</script>

