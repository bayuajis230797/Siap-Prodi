<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Detail Kelas <?php echo $kelas; ?></h3>
                <div class="card-tools">
                    <a href="<?php echo site_url('Kelas'); ?>" class="btn btn-secondary btn-sm">
                        <i class="fas fa-arrow-left"></i> Kembali
                    </a>
                </div>
            </div>
            <div class="card-body">
                <?php if (isset($tahun_aktif) && $tahun_aktif): ?>
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle"></i> 
                        <strong>Tahun Akademik Aktif:</strong> <?php echo $tahun_aktif->tahun_akademik . ' ' . ucfirst($tahun_aktif->semester); ?>
                    </div>
                <?php endif; ?>

                <!-- Statistik -->
                <div class="row mb-3">
                    <div class="col-md-3">
                        <div class="info-box">
                            <span class="info-box-icon bg-info"><i class="fas fa-users"></i></span>
                            <div class="info-box-content">
                                <span class="info-box-text">Jumlah Mahasiswa</span>
                                <span class="info-box-number"><?php echo $jumlah_mahasiswa; ?></span>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="info-box">
                            <span class="info-box-icon bg-success"><i class="fas fa-book"></i></span>
                            <div class="info-box-content">
                                <span class="info-box-text">Jumlah Mata Kuliah</span>
                                <span class="info-box-number"><?php echo $jumlah_mata_kuliah; ?></span>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="info-box">
                            <span class="info-box-icon bg-warning"><i class="fas fa-graduation-cap"></i></span>
                            <div class="info-box-content">
                                <span class="info-box-text">Kurikulum</span>
                                <span class="info-box-number"><?php echo count($kurikulum); ?></span>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="info-box">
                            <span class="info-box-icon bg-primary"><i class="fas fa-calendar"></i></span>
                            <div class="info-box-content">
                                <span class="info-box-text">Semester</span>
                                <span class="info-box-number"><?php echo intval(substr($kelas, 0, 1)); ?></span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Tab Navigation -->
                <ul class="nav nav-tabs" id="kelasTabs" role="tablist">
                    <li class="nav-item">
                        <a class="nav-link active" id="mahasiswa-tab" data-bs-toggle="tab" href="#mahasiswa" role="tab" aria-controls="mahasiswa" aria-selected="true">
                            <i class="fas fa-users"></i> Mahasiswa (<?php echo $jumlah_mahasiswa; ?>)
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" id="matakuliah-tab" data-bs-toggle="tab" href="#matakuliah" role="tab" aria-controls="matakuliah" aria-selected="false">
                            <i class="fas fa-book"></i> Mata Kuliah (<?php echo $jumlah_mata_kuliah; ?>)
                        </a>
                    </li>
                </ul>

                <!-- Tab Content -->
                <div class="tab-content" id="kelasTabsContent">
                    <!-- Tab Mahasiswa -->
                    <div class="tab-pane fade show active" id="mahasiswa" role="tabpanel">
                        <div class="card-body">
                            <h5 class="mb-3">Daftar Mahasiswa Kelas <?php echo $kelas; ?></h5>
                            
                            <?php if (empty($mahasiswa)): ?>
                                <div class="alert alert-warning">
                                    <i class="fas fa-exclamation-triangle"></i> Tidak ada mahasiswa di kelas ini.
                                </div>
                            <?php else: ?>
                                <div class="table-responsive">
                                    <table class="table table-bordered table-striped">
                                        <thead>
                                            <tr>
                                                <th width="5%">No</th>
                                                <th width="12%">NIM</th>
                                                <th>Nama Mahasiswa</th>
                                                <th width="10%">Angkatan</th>
                                                <th width="15%">Kurikulum</th>
                                                <th width="10%" class="text-center">Status</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php $no = 1; foreach ($mahasiswa as $m): ?>
                                                <tr>
                                                    <td><?php echo $no++; ?></td>
                                                    <td><strong><?php echo $m->nim; ?></strong></td>
                                                    <td><?php echo $m->nama_mahasiswa; ?></td>
                                                    <td class="text-center">
                                                        <span class="badge badge-secondary"><?php echo $m->angkatan; ?></span>
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
                                        </tbody>
                                    </table>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Tab Mata Kuliah -->
                    <div class="tab-pane fade" id="matakuliah" role="tabpanel">
                        <div class="card-body">
                            <h5 class="mb-3">Mata Kuliah Kelas <?php echo $kelas; ?> (Semester <?php echo intval(substr($kelas, 0, 1)); ?>)</h5>
                            
                            <?php 
                            // Debug: Cek data mata kuliah
                            // var_dump($mata_kuliah); 
                            // var_dump($mata_kuliah_by_kurikulum); 
                            ?>
                            
                            <?php if (empty($mata_kuliah)): ?>
                                <div class="alert alert-warning">
                                    <i class="fas fa-exclamation-triangle"></i> 
                                    Tidak ada mata kuliah untuk kelas ini. Pastikan:
                                    <ul class="mb-0 mt-2">
                                        <li>Mahasiswa di kelas ini sudah memiliki kurikulum</li>
                                        <li>Kurikulum sudah diaktifkan</li>
                                        <li>Mata kuliah untuk semester <?php echo intval(substr($kelas, 0, 1)); ?> sudah di-input di menu Kurikulum > Mapping MK</li>
                                    </ul>
                                </div>
                            <?php else: ?>
                                <?php if (!empty($kurikulum) && count($kurikulum) > 1): ?>
                                    <div class="alert alert-info">
                                        <i class="fas fa-info-circle"></i> 
                                        <strong>Catatan:</strong> Kelas ini menggunakan <?php echo count($kurikulum); ?> kurikulum berbeda. Mata kuliah dikelompokkan berdasarkan kurikulum.
                                    </div>
                                <?php endif; ?>

                                <?php foreach ($mata_kuliah_by_kurikulum as $kurikulum_name => $mk_list): ?>
                                    <div class="card card-outline card-primary mb-3">
                                        <div class="card-header">
                                            <h3 class="card-title">
                                                <i class="fas fa-book"></i> <?php echo $kurikulum_name; ?>
                                                <span class="badge badge-info ml-2"><?php echo count($mk_list); ?> Mata Kuliah</span>
                                            </h3>
                                        </div>
                                        <div class="card-body">
                                            <div class="table-responsive">
                                                <table class="table table-bordered table-striped">
                                                    <thead>
                                                        <tr>
                                                            <th width="5%">No</th>
                                                            <th width="12%">Kode MK</th>
                                                            <th>Nama Mata Kuliah</th>
                                                            <th width="8%" class="text-center">SKS</th>
                                                            <th width="10%" class="text-center">Semester</th>
                                                            <th width="10%" class="text-center">Jenis</th>
                                                            <th width="10%" class="text-center">Status Aktif</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        <?php $no = 1; foreach ($mk_list as $mk): ?>
                                                            <tr>
                                                                <td><?php echo $no++; ?></td>
                                                                <td><strong><?php echo $mk->kode_mk; ?></strong></td>
                                                                <td><?php echo $mk->nama_mk; ?></td>
                                                                <td class="text-center">
                                                                    <span class="badge badge-info"><?php echo $mk->sks; ?> SKS</span>
                                                                </td>
                                                                <td class="text-center">
                                                                    <span class="badge badge-primary">Semester <?php echo $mk->semester; ?></span>
                                                                </td>
                                                                <td class="text-center">
                                                                    <?php if (isset($mk->jenis) && !empty($mk->jenis)): ?>
                                                                        <span class="badge badge-info"><?php echo ucfirst($mk->jenis); ?></span>
                                                                    <?php else: ?>
                                                                        <span class="badge badge-secondary">-</span>
                                                                    <?php endif; ?>
                                                                </td>
                                                                <td class="text-center">
                                                                    <?php if (isset($mk->is_aktif) && $mk->is_aktif): ?>
                                                                        <span class="badge badge-success">
                                                                            <i class="fas fa-check"></i> Aktif
                                                                        </span>
                                                                    <?php else: ?>
                                                                        <span class="badge badge-warning">
                                                                            <i class="fas fa-times"></i> Belum Aktif
                                                                        </span>
                                                                    <?php endif; ?>
                                                                </td>
                                                            </tr>
                                                        <?php endforeach; ?>
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Handle hash URL untuk tab navigation (Bootstrap 5)
document.addEventListener('DOMContentLoaded', function() {
    // Fungsi untuk show tab berdasarkan hash
    function showTabFromHash() {
        var hash = window.location.hash.substring(1); // Remove #
        if (hash) {
            var tabLink = document.getElementById(hash + '-tab');
            if (tabLink) {
                // Gunakan Bootstrap 5 Tab API
                var tab = new bootstrap.Tab(tabLink);
                tab.show();
            }
        }
    }
    
    // Cek jika ada hash di URL saat halaman dimuat
    if (window.location.hash) {
        // Delay sedikit untuk memastikan Bootstrap sudah ter-load
        setTimeout(function() {
            showTabFromHash();
        }, 100);
    }
    
    // Handle shown event untuk update hash (setelah tab berhasil di-show)
    var tabLinks = document.querySelectorAll('a[data-bs-toggle="tab"]');
    tabLinks.forEach(function(tabLink) {
        tabLink.addEventListener('shown.bs.tab', function (e) {
            var target = e.target.getAttribute("href");
            if (target && target.startsWith('#')) {
                // Update hash tanpa trigger reload
                if (history.pushState) {
                    history.pushState(null, null, target);
                } else {
                    window.location.hash = target.substring(1);
                }
            }
        });
    });
    
    // Handle back/forward browser button
    window.addEventListener('hashchange', function() {
        showTabFromHash();
    });
});
</script>

