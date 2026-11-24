<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Setting Grade & Bobot Nilai Akhir</h3>
            </div>
            <div class="card-body">
                <?php if ($this->session->flashdata('success')): ?>
                    <div class="alert alert-success alert-dismissible">
                        <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
                        <?php echo $this->session->flashdata('success'); ?>
                    </div>
                <?php endif; ?>
                
                <?php if ($this->session->flashdata('error')): ?>
                    <div class="alert alert-danger alert-dismissible">
                        <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
                        <?php echo $this->session->flashdata('error'); ?>
                    </div>
                <?php endif; ?>

                <!-- Tab Navigation -->
                <ul class="nav nav-tabs" id="settingTabs" role="tablist">
                    <li class="nav-item">
                        <a class="nav-link <?php echo ($tipe === 'pkl' || !$tipe) ? 'active' : ''; ?>" id="pkl-tab" data-toggle="tab" href="#pkl" role="tab" aria-controls="pkl" aria-selected="<?php echo ($tipe === 'pkl' || !$tipe) ? 'true' : 'false'; ?>">
                            <i class="fas fa-briefcase"></i> PKL
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?php echo ($tipe === 'skripsi') ? 'active' : ''; ?>" id="skripsi-tab" data-toggle="tab" href="#skripsi" role="tab" aria-controls="skripsi" aria-selected="<?php echo ($tipe === 'skripsi') ? 'true' : 'false'; ?>">
                            <i class="fas fa-graduation-cap"></i> Skripsi
                        </a>
                    </li>
                </ul>

                <!-- Tab Content -->
                <div class="tab-content" id="settingTabsContent">
                    <!-- PKL Tab -->
                    <div class="tab-pane fade <?php echo ($tipe === 'pkl' || !$tipe) ? 'show active' : ''; ?>" id="pkl" role="tabpanel" aria-labelledby="pkl-tab">
                        <div class="row mt-3">
                            <!-- Grade Setting PKL - Kiri -->
                            <div class="col-md-6">
                                <div class="card card-primary">
                                    <div class="card-header">
                                        <h3 class="card-title">Setting Grade PKL</h3>
                                        <div class="card-tools">
                                            <a href="<?php echo site_url('Setting_grade/create_grade?tipe=pkl'); ?>" class="btn btn-primary btn-sm">
                                                <i class="fas fa-plus"></i> Tambah Grade
                                            </a>
                                        </div>
                                    </div>
                                    <div class="card-body">
                                        <p class="text-muted">Atur range nilai untuk setiap grade (A, B, C, D, E, AB, -A, A-, dll)</p>
                                        <div class="table-responsive">
                                            <table class="table table-bordered table-sm">
                                                <thead>
                                                    <tr>
                                                        <th>Grade</th>
                                                        <th>Nilai Min</th>
                                                        <th>Nilai Max</th>
                                                        <th>Keterangan</th>
                                                        <th>Status</th>
                                                        <th>Aksi</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?php if (empty($grade_pkl)): ?>
                                                        <tr>
                                                            <td colspan="6" class="text-center">Tidak ada data grade.</td>
                                                        </tr>
                                                    <?php else: ?>
                                                        <?php foreach ($grade_pkl as $grade): ?>
                                                            <tr>
                                                                <td><strong><?php echo $grade->grade; ?></strong></td>
                                                                <td><?php echo number_format($grade->nilai_min, 2); ?></td>
                                                                <td><?php echo $grade->nilai_max ? number_format($grade->nilai_max, 2) : '∞'; ?></td>
                                                                <td><?php echo $grade->keterangan; ?></td>
                                                                <td>
                                                                    <?php if ($grade->is_aktif): ?>
                                                                        <span class="badge badge-success">Aktif</span>
                                                                    <?php else: ?>
                                                                        <span class="badge badge-secondary">Tidak Aktif</span>
                                                                    <?php endif; ?>
                                                                </td>
                                                                <td>
                                                                    <a href="<?php echo site_url('Setting_grade/edit_grade/' . $grade->id); ?>" class="btn btn-sm btn-warning" title="Edit">
                                                                        <i class="fas fa-edit"></i>
                                                                    </a>
                                                                    <a href="<?php echo site_url('Setting_grade/delete_grade/' . $grade->id); ?>" 
                                                                       class="btn btn-sm btn-danger" 
                                                                       title="Hapus"
                                                                       onclick="return confirm('Yakin ingin menghapus grade <?php echo $grade->grade; ?>?');">
                                                                        <i class="fas fa-trash"></i>
                                                                    </a>
                                                                </td>
                                                            </tr>
                                                        <?php endforeach; ?>
                                                    <?php endif; ?>
                                                </tbody>
                                            </table>
                                        </div>
                                        <div class="alert alert-info mt-3">
                                            <strong>Catatan:</strong> Nilai akhir PKL = (P1 + P2 + P3 + P4 + P5 + P6) / 6
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Bobot Setting PKL - Kanan -->
                            <div class="col-md-6">
                                <div class="card card-info">
                                    <div class="card-header">
                                        <h3 class="card-title">Setting Bobot Nilai Akhir PKL</h3>
                                    </div>
                                    <div class="card-body">
                                        <p class="text-muted">Atur bobot perhitungan nilai akhir PKL</p>
                                        <div class="table-responsive">
                                            <table class="table table-bordered table-sm">
                                                <thead>
                                                    <tr>
                                                        <th>Komponen</th>
                                                        <th>Bobot (%)</th>
                                                        <th>Keterangan</th>
                                                        <th>Status</th>
                                                        <th>Aksi</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?php if (empty($bobot_pkl)): ?>
                                                        <tr>
                                                            <td colspan="5" class="text-center">Tidak ada data bobot.</td>
                                                        </tr>
                                                    <?php else: ?>
                                                        <?php foreach ($bobot_pkl as $bobot): ?>
                                                            <tr>
                                                                <td><strong><?php echo ucfirst(str_replace('_', ' ', $bobot->komponen)); ?></strong></td>
                                                                <td><?php echo number_format($bobot->bobot, 2); ?>%</td>
                                                                <td><?php echo $bobot->keterangan; ?></td>
                                                                <td>
                                                                    <?php if ($bobot->is_aktif): ?>
                                                                        <span class="badge badge-success">Aktif</span>
                                                                    <?php else: ?>
                                                                        <span class="badge badge-secondary">Tidak Aktif</span>
                                                                    <?php endif; ?>
                                                                </td>
                                                                <td>
                                                                    <a href="<?php echo site_url('Setting_grade/edit_bobot/' . $bobot->id); ?>" class="btn btn-sm btn-warning">
                                                                        <i class="fas fa-edit"></i> Edit
                                                                    </a>
                                                                </td>
                                                            </tr>
                                                        <?php endforeach; ?>
                                                    <?php endif; ?>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Skripsi Tab -->
                    <div class="tab-pane fade <?php echo ($tipe === 'skripsi') ? 'show active' : ''; ?>" id="skripsi" role="tabpanel" aria-labelledby="skripsi-tab">
                        <div class="row mt-3">
                            <!-- Grade Setting Skripsi - Full Width di Atas (Vertikal Layout) -->
                            <div class="col-12">
                                <div class="card card-primary">
                                    <div class="card-header">
                                        <h3 class="card-title">Setting Grade Skripsi</h3>
                                        <div class="card-tools">
                                            <a href="<?php echo site_url('Setting_grade/create_grade?tipe=skripsi'); ?>" class="btn btn-primary btn-sm">
                                                <i class="fas fa-plus"></i> Tambah Grade
                                            </a>
                                        </div>
                                    </div>
                                    <div class="card-body">
                                        <p class="text-muted">Atur range nilai untuk setiap grade (A, B, C, D, E, AB, -A, A-, dll)</p>
                                        <div class="table-responsive">
                                            <table class="table table-bordered table-sm">
                                                <thead>
                                                    <tr>
                                                        <th>Grade</th>
                                                        <th>Nilai Min</th>
                                                        <th>Nilai Max</th>
                                                        <th>Keterangan</th>
                                                        <th>Status</th>
                                                        <th>Aksi</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?php if (empty($grade_skripsi)): ?>
                                                        <tr>
                                                            <td colspan="6" class="text-center">Tidak ada data grade.</td>
                                                        </tr>
                                                    <?php else: ?>
                                                        <?php foreach ($grade_skripsi as $grade): ?>
                                                            <tr>
                                                                <td><strong><?php echo $grade->grade; ?></strong></td>
                                                                <td><?php echo number_format($grade->nilai_min, 2); ?></td>
                                                                <td><?php echo $grade->nilai_max ? number_format($grade->nilai_max, 2) : '∞'; ?></td>
                                                                <td><?php echo $grade->keterangan; ?></td>
                                                                <td>
                                                                    <?php if ($grade->is_aktif): ?>
                                                                        <span class="badge badge-success">Aktif</span>
                                                                    <?php else: ?>
                                                                        <span class="badge badge-secondary">Tidak Aktif</span>
                                                                    <?php endif; ?>
                                                                </td>
                                                                <td>
                                                                    <a href="<?php echo site_url('Setting_grade/edit_grade/' . $grade->id); ?>" class="btn btn-sm btn-warning" title="Edit">
                                                                        <i class="fas fa-edit"></i>
                                                                    </a>
                                                                    <a href="<?php echo site_url('Setting_grade/delete_grade/' . $grade->id); ?>" 
                                                                       class="btn btn-sm btn-danger" 
                                                                       title="Hapus"
                                                                       onclick="return confirm('Yakin ingin menghapus grade <?php echo $grade->grade; ?>?');">
                                                                        <i class="fas fa-trash"></i>
                                                                    </a>
                                                                </td>
                                                            </tr>
                                                        <?php endforeach; ?>
                                                    <?php endif; ?>
                                                </tbody>
                                            </table>
                                        </div>
                                        <div class="alert alert-info mt-3">
                                            <strong>Catatan:</strong> Nilai akhir Skripsi = (Bobot Bimbingan × Nilai Bimbingan) + (Bobot Sidang × Nilai Sidang)
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Bobot Setting Skripsi - Full Width di Bawah -->
                            <div class="col-12 mt-3">
                                <div class="card card-info">
                                    <div class="card-header">
                                        <h3 class="card-title">Setting Bobot Nilai Akhir Skripsi</h3>
                                    </div>
                                    <div class="card-body">
                                        <p class="text-muted">Atur bobot perhitungan nilai akhir Skripsi</p>
                                        <div class="table-responsive">
                                            <table class="table table-bordered table-sm">
                                                <thead>
                                                    <tr>
                                                        <th>Komponen</th>
                                                        <th>Bobot (%)</th>
                                                        <th>Keterangan</th>
                                                        <th>Status</th>
                                                        <th>Aksi</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?php if (empty($bobot_skripsi)): ?>
                                                        <tr>
                                                            <td colspan="5" class="text-center">Tidak ada data bobot.</td>
                                                        </tr>
                                                    <?php else: ?>
                                                        <?php 
                                                        $total_bobot = 0;
                                                        foreach ($bobot_skripsi as $bobot): 
                                                            $total_bobot += $bobot->bobot;
                                                        ?>
                                                            <tr>
                                                                <td><strong><?php echo ucfirst(str_replace('_', ' ', $bobot->komponen)); ?></strong></td>
                                                                <td><?php echo number_format($bobot->bobot, 2); ?>%</td>
                                                                <td><?php echo $bobot->keterangan; ?></td>
                                                                <td>
                                                                    <?php if ($bobot->is_aktif): ?>
                                                                        <span class="badge badge-success">Aktif</span>
                                                                    <?php else: ?>
                                                                        <span class="badge badge-secondary">Tidak Aktif</span>
                                                                    <?php endif; ?>
                                                                </td>
                                                                <td>
                                                                    <a href="<?php echo site_url('Setting_grade/edit_bobot/' . $bobot->id); ?>" class="btn btn-sm btn-warning">
                                                                        <i class="fas fa-edit"></i> Edit
                                                                    </a>
                                                                </td>
                                                            </tr>
                                                        <?php endforeach; ?>
                                                        <tr class="table-info">
                                                            <td><strong>Total</strong></td>
                                                            <td><strong><?php echo number_format($total_bobot, 2); ?>%</strong></td>
                                                            <td colspan="3">
                                                                <?php if ($total_bobot != 100): ?>
                                                                    <span class="badge badge-warning">Total harus 100%</span>
                                                                <?php else: ?>
                                                                    <span class="badge badge-success">Total: 100%</span>
                                                                <?php endif; ?>
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
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    // Handle tab switching dengan jQuery (untuk Bootstrap 4/AdminLTE)
    $(document).ready(function() {
        // Handle click pada tab link - gunakan Bootstrap tab API
        $('#settingTabs a[data-toggle="tab"]').on('click', function(e) {
            var target = $(this).attr('href');
            if (target && target.startsWith('#')) {
                var targetId = target.substring(1);
                
                // Update URL parameter tanpa reload
                var newUrl = window.location.pathname + '?tipe=' + targetId;
                if (window.history && window.history.pushState) {
                    window.history.pushState({}, '', newUrl);
                }
            }
        });
        
        // Handle URL parameter saat halaman dimuat
        var urlParams = new URLSearchParams(window.location.search);
        var tipe = urlParams.get('tipe');
        
        if (tipe === 'skripsi') {
            // Switch ke tab Skripsi menggunakan Bootstrap tab API
            setTimeout(function() {
                $('#skripsi-tab').tab('show');
            }, 100);
        } else {
            // Default ke PKL
            setTimeout(function() {
                $('#pkl-tab').tab('show');
            }, 100);
        }
    });
</script>
