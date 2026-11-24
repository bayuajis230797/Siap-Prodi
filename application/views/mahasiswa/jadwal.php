<div class="row">
    <div class="col-12">
        <div class="card card-primary card-outline">
            <?php if (!isset($active_tab)): ?>
            <div class="card-header p-2">
                <ul class="nav nav-pills" id="tabJadwal" role="tablist">
                    <li class="nav-item">
                        <a class="nav-link active" id="tab-kuliah-link" href="#tab-kuliah" role="tab" style="cursor: pointer;">
                            <i class="fas fa-book"></i> Jadwal Kuliah
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" id="tab-uts-link" href="#tab-uts" role="tab" style="cursor: pointer;">
                            <i class="fas fa-clipboard-list"></i> Jadwal UTS
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" id="tab-uas-link" href="#tab-uas" role="tab" style="cursor: pointer;">
                            <i class="fas fa-file-alt"></i> Jadwal UAS
                        </a>
                    </li>
                    <?php if (isset($is_mahasiswa_pkl) && $is_mahasiswa_pkl): ?>
                    <li class="nav-item">
                        <a class="nav-link" id="tab-pkl-link" href="#tab-pkl" role="tab" style="cursor: pointer;">
                            <i class="fas fa-briefcase"></i> Jadwal PKL
                        </a>
                    </li>
                    <?php endif; ?>
                </ul>
            </div>
            <?php endif; ?>
            <div class="card-body">
                <?php if (!$tahun_aktif): ?>
                    <div class="alert alert-warning">
                        <i class="fas fa-exclamation-triangle"></i> 
                        <strong>Perhatian:</strong> Belum ada tahun akademik yang aktif.
                    </div>
                <?php elseif (!$kelas): ?>
                    <div class="alert alert-warning">
                        <i class="fas fa-exclamation-triangle"></i> 
                        <strong>Perhatian:</strong> Data kelas Anda belum terdaftar. Silakan hubungi admin.
                    </div>
                <?php else: ?>
                    <div class="alert alert-info mb-3">
                        <i class="fas fa-info-circle"></i> 
                        <strong>Tahun Akademik:</strong> <?php echo $tahun_aktif->tahun_akademik . ' ' . ucfirst($tahun_aktif->semester); ?> | 
                        <strong>Kelas:</strong> <?php echo $kelas; ?>
                    </div>

                    <div class="tab-content" id="tabJadwalContent">
                        <!-- Tab Jadwal Kuliah -->
                        <div class="tab-pane fade <?php echo (!isset($active_tab) || $active_tab == 'kuliah') ? 'show active' : ''; ?>" id="tab-kuliah" role="tabpanel" <?php echo (isset($active_tab) && $active_tab != 'kuliah') ? 'style="display:none;"' : ''; ?>>
                            <?php if (empty($jadwal_kuliah)): ?>
                                <div class="alert alert-info">
                                    <i class="fas fa-info-circle"></i> Belum ada jadwal kuliah untuk kelas Anda.
                                </div>
                            <?php else: ?>
                                <div class="table-responsive">
                                    <table class="table table-bordered table-striped table-hover">
                                        <thead class="thead-light">
                                            <tr>
                                                <th width="10%">Hari</th>
                                                <th width="15%">Jam</th>
                                                <th width="12%">Kode MK</th>
                                                <th>Mata Kuliah</th>
                                                <th width="8%" class="text-center">SKS</th>
                                                <th width="20%">Dosen</th>
                                                <th width="15%">Ruangan</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php 
                                            $hari_indonesia = array(
                                                'senin' => 'Senin',
                                                'selasa' => 'Selasa',
                                                'rabu' => 'Rabu',
                                                'kamis' => 'Kamis',
                                                'jumat' => 'Jumat',
                                                'sabtu' => 'Sabtu',
                                                'minggu' => 'Minggu'
                                            );
                                            foreach ($jadwal_by_hari as $hari => $jadwal_hari): 
                                                if (!empty($jadwal_hari)):
                                                    foreach ($jadwal_hari as $index => $jk):
                                                        $rowspan = ($index === 0) ? count($jadwal_hari) : 0;
                                            ?>
                                                <tr>
                                                    <?php if ($index === 0): ?>
                                                        <td rowspan="<?php echo count($jadwal_hari); ?>" class="align-middle text-center">
                                                            <strong><?php echo isset($hari_indonesia[$hari]) ? $hari_indonesia[$hari] : ucfirst($hari); ?></strong>
                                                        </td>
                                                    <?php endif; ?>
                                                    <td>
                                                        <?php echo date('H:i', strtotime($jk->jam_mulai)); ?> - 
                                                        <?php echo date('H:i', strtotime($jk->jam_selesai)); ?>
                                                    </td>
                                                    <td><strong><?php echo $jk->kode_mk; ?></strong></td>
                                                    <td><?php echo $jk->nama_mk; ?></td>
                                                    <td class="text-center"><?php echo $jk->sks; ?></td>
                                                    <td>
                                                        <?php echo $jk->nama_dosen; ?><br>
                                                        <small class="text-muted"><?php echo $jk->nidn ?: '-'; ?></small>
                                                    </td>
                                                    <td>
                                                        <?php echo $jk->nama_ruang ?: '-'; ?><br>
                                                        <small class="text-muted"><?php echo $jk->kode_ruang ?: '-'; ?></small>
                                                    </td>
                                                </tr>
                                            <?php 
                                                    endforeach;
                                                endif;
                                            endforeach; 
                                            ?>
                                        </tbody>
                                    </table>
                                </div>
                            <?php endif; ?>
                        </div>

                        <!-- Tab Jadwal UTS -->
                        <div class="tab-pane fade <?php echo (isset($active_tab) && $active_tab == 'uts') ? 'show active' : ''; ?>" id="tab-uts" role="tabpanel" <?php echo (isset($active_tab) && $active_tab != 'uts') ? 'style="display:none;"' : ''; ?>>
                            <?php if (empty($jadwal_uts)): ?>
                                <div class="alert alert-info">
                                    <i class="fas fa-info-circle"></i> Belum ada jadwal UTS untuk kelas Anda.
                                </div>
                            <?php else: ?>
                                <div class="mb-3">
                                    <a href="<?php echo site_url('Absensi_ujian/scan'); ?>" class="btn btn-success btn-sm">
                                        <i class="fas fa-qrcode"></i> Scan QR Code
                                    </a>
                                </div>
                                <div class="table-responsive">
                                    <table class="table table-bordered table-striped table-hover">
                                        <thead class="thead-light">
                                            <tr>
                                                <th width="5%">#</th>
                                                <th width="12%">Tanggal</th>
                                                <th width="15%">Jam</th>
                                                <th>Mata Kuliah</th>
                                                <th width="15%">Ruangan</th>
                                                <th width="15%">Pengawas</th>
                                                <th width="8%" class="text-center">Sesi</th>
                                                <th width="12%" class="text-center">Status Absensi</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php 
                                            $no = 1; 
                                            foreach ($jadwal_uts as $jut): 
                                                $kehadiran = isset($jut->kehadiran) ? $jut->kehadiran : null;
                                            ?>
                                                <tr>
                                                    <td><?php echo $no++; ?></td>
                                                    <td>
                                                        <strong><?php echo date('d/m/Y', strtotime($jut->tanggal)); ?></strong><br>
                                                        <?php 
                                                        $hari_inggris = date('l', strtotime($jut->tanggal));
                                                        $hari_indonesia_uts = array(
                                                            'Monday' => 'Senin',
                                                            'Tuesday' => 'Selasa',
                                                            'Wednesday' => 'Rabu',
                                                            'Thursday' => 'Kamis',
                                                            'Friday' => 'Jumat',
                                                            'Saturday' => 'Sabtu',
                                                            'Sunday' => 'Minggu'
                                                        );
                                                        ?>
                                                        <small class="text-muted"><?php echo isset($hari_indonesia_uts[$hari_inggris]) ? $hari_indonesia_uts[$hari_inggris] : $hari_inggris; ?></small>
                                                    </td>
                                                    <td>
                                                        <?php echo date('H:i', strtotime($jut->jam_mulai)); ?> - 
                                                        <?php echo date('H:i', strtotime($jut->jam_selesai)); ?> WIB
                                                    </td>
                                                    <td><?php echo htmlspecialchars($jut->nama_mk); ?></td>
                                                    <td>
                                                        <?php echo $jut->nama_ruang ?: '-'; ?><br>
                                                        <small class="text-muted"><?php echo $jut->kode_ruang ?: '-'; ?></small>
                                                    </td>
                                                    <td>
                                                        <?php if (!empty($jut->pengawas)): ?>
                                                            <?php foreach ($jut->pengawas as $pengawas): ?>
                                                                <?php 
                                                                $nama_pengawas = !empty($pengawas->nama_dosen) ? $pengawas->nama_dosen : $pengawas->nama_tendik;
                                                                $nidn_nipy = !empty($pengawas->nidn) ? $pengawas->nidn : $pengawas->nipy;
                                                                ?>
                                                                <div>
                                                                    <?php echo $nama_pengawas; ?><br>
                                                                    <small class="text-muted"><?php echo $nidn_nipy ?: '-'; ?></small>
                                                                </div>
                                                            <?php endforeach; ?>
                                                        <?php else: ?>
                                                            <span class="text-muted">-</span>
                                                        <?php endif; ?>
                                                    </td>
                                                    <td class="text-center">
                                                        <?php if (!empty($jut->sesi)): ?>
                                                            <span class="badge badge-info">Sesi <?php echo $jut->sesi; ?></span>
                                                        <?php else: ?>
                                                            <span class="text-muted">-</span>
                                                        <?php endif; ?>
                                                    </td>
                                                    <td class="text-center">
                                                        <?php if ($kehadiran): ?>
                                                            <?php 
                                                            $status_class = ($kehadiran->status_kehadiran == 'hadir') ? 'success' : 'danger';
                                                            $status_label = ($kehadiran->status_kehadiran == 'hadir') ? 'Hadir' : 'Tidak Hadir';
                                                            ?>
                                                            <span class="badge badge-<?php echo $status_class; ?>">
                                                                <?php echo $status_label; ?>
                                                            </span>
                                                        <?php else: ?>
                                                            <span class="badge badge-danger">Tidak Hadir</span>
                                                        <?php endif; ?>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            <?php endif; ?>
                        </div>

                        <!-- Tab Jadwal UAS -->
                        <div class="tab-pane fade <?php echo (isset($active_tab) && $active_tab == 'uas') ? 'show active' : ''; ?>" id="tab-uas" role="tabpanel" <?php echo (isset($active_tab) && $active_tab != 'uas') ? 'style="display:none;"' : ''; ?>>
                            <?php if (empty($jadwal_uas)): ?>
                                <div class="alert alert-info">
                                    <i class="fas fa-info-circle"></i> Belum ada jadwal UAS untuk kelas Anda.
                                </div>
                            <?php else: ?>
                                <div class="mb-3">
                                    <a href="<?php echo site_url('Absensi_ujian/scan'); ?>" class="btn btn-success btn-sm">
                                        <i class="fas fa-qrcode"></i> Scan QR Code
                                    </a>
                                </div>
                                <div class="table-responsive">
                                    <table class="table table-bordered table-striped table-hover">
                                        <thead class="thead-light">
                                            <tr>
                                                <th width="5%">#</th>
                                                <th width="12%">Tanggal</th>
                                                <th width="15%">Jam</th>
                                                <th>Mata Kuliah</th>
                                                <th width="15%">Ruangan</th>
                                                <th width="15%">Pengawas</th>
                                                <th width="8%" class="text-center">Sesi</th>
                                                <th width="12%" class="text-center">Status Absensi</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php 
                                            $no = 1; 
                                            foreach ($jadwal_uas as $jua): 
                                                $kehadiran = isset($jua->kehadiran) ? $jua->kehadiran : null;
                                            ?>
                                                <tr>
                                                    <td><?php echo $no++; ?></td>
                                                    <td>
                                                        <strong><?php echo date('d/m/Y', strtotime($jua->tanggal)); ?></strong><br>
                                                        <?php 
                                                        $hari_inggris = date('l', strtotime($jua->tanggal));
                                                        $hari_indonesia_uas = array(
                                                            'Monday' => 'Senin',
                                                            'Tuesday' => 'Selasa',
                                                            'Wednesday' => 'Rabu',
                                                            'Thursday' => 'Kamis',
                                                            'Friday' => 'Jumat',
                                                            'Saturday' => 'Sabtu',
                                                            'Sunday' => 'Minggu'
                                                        );
                                                        ?>
                                                        <small class="text-muted"><?php echo isset($hari_indonesia_uas[$hari_inggris]) ? $hari_indonesia_uas[$hari_inggris] : $hari_inggris; ?></small>
                                                    </td>
                                                    <td>
                                                        <?php echo date('H:i', strtotime($jua->jam_mulai)); ?> - 
                                                        <?php echo date('H:i', strtotime($jua->jam_selesai)); ?> WIB
                                                    </td>
                                                    <td><?php echo htmlspecialchars($jua->nama_mk); ?></td>
                                                    <td>
                                                        <?php echo $jua->nama_ruang ?: '-'; ?><br>
                                                        <small class="text-muted"><?php echo $jua->kode_ruang ?: '-'; ?></small>
                                                    </td>
                                                    <td>
                                                        <?php if (!empty($jua->pengawas)): ?>
                                                            <?php foreach ($jua->pengawas as $pengawas): ?>
                                                                <?php 
                                                                $nama_pengawas = !empty($pengawas->nama_dosen) ? $pengawas->nama_dosen : $pengawas->nama_tendik;
                                                                $nidn_nipy = !empty($pengawas->nidn) ? $pengawas->nidn : $pengawas->nipy;
                                                                ?>
                                                                <div>
                                                                    <?php echo $nama_pengawas; ?><br>
                                                                    <small class="text-muted"><?php echo $nidn_nipy ?: '-'; ?></small>
                                                                </div>
                                                            <?php endforeach; ?>
                                                        <?php else: ?>
                                                            <span class="text-muted">-</span>
                                                        <?php endif; ?>
                                                    </td>
                                                    <td class="text-center">
                                                        <?php if (!empty($jua->sesi)): ?>
                                                            <span class="badge badge-info">Sesi <?php echo $jua->sesi; ?></span>
                                                        <?php else: ?>
                                                            <span class="text-muted">-</span>
                                                        <?php endif; ?>
                                                    </td>
                                                    <td class="text-center">
                                                        <?php if ($kehadiran): ?>
                                                            <?php 
                                                            $status_class = ($kehadiran->status_kehadiran == 'hadir') ? 'success' : 'danger';
                                                            $status_label = ($kehadiran->status_kehadiran == 'hadir') ? 'Hadir' : 'Tidak Hadir';
                                                            ?>
                                                            <span class="badge badge-<?php echo $status_class; ?>">
                                                                <?php echo $status_label; ?>
                                                            </span>
                                                        <?php else: ?>
                                                            <span class="badge badge-danger">Tidak Hadir</span>
                                                        <?php endif; ?>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            <?php endif; ?>
                        </div>

                        <!-- Tab Jadwal PKL -->
                        <?php if (isset($is_mahasiswa_pkl) && $is_mahasiswa_pkl): ?>
                        <div class="tab-pane fade" id="tab-pkl" role="tabpanel">
                            <?php if (empty($jadwal_pkl)): ?>
                                <div class="alert alert-info">
                                    <i class="fas fa-info-circle"></i> Belum ada jadwal ujian PKL untuk Anda.
                                </div>
                            <?php else: ?>
                                <?php foreach ($jadwal_pkl as $jp): ?>
                                    <div class="card card-outline card-warning mb-3">
                                        <div class="card-header">
                                            <h5 class="card-title mb-0">
                                                <i class="fas fa-briefcase"></i> Ujian PKL
                                            </h5>
                                        </div>
                                        <div class="card-body">
                                            <div class="row">
                                                <div class="col-md-6">
                                                    <table class="table table-borderless">
                                                        <tr>
                                                            <th width="40%">Tanggal Ujian:</th>
                                                            <td>
                                                                <strong><?php echo date('d/m/Y', strtotime($jp->tanggal)); ?></strong><br>
                                                                <?php 
                                                                $hari_inggris = date('l', strtotime($jp->tanggal));
                                                                $hari_indonesia_pkl = array(
                                                                    'Monday' => 'Senin',
                                                                    'Tuesday' => 'Selasa',
                                                                    'Wednesday' => 'Rabu',
                                                                    'Thursday' => 'Kamis',
                                                                    'Friday' => 'Jumat',
                                                                    'Saturday' => 'Sabtu',
                                                                    'Sunday' => 'Minggu'
                                                                );
                                                                ?>
                                                                <small class="text-muted"><?php echo isset($hari_indonesia_pkl[$hari_inggris]) ? $hari_indonesia_pkl[$hari_inggris] : $hari_inggris; ?></small>
                                                            </td>
                                                        </tr>
                                                        <tr>
                                                            <th>Jam:</th>
                                                            <td>
                                                                <strong><?php echo date('H:i', strtotime($jp->jam_mulai)); ?> - <?php echo date('H:i', strtotime($jp->jam_selesai)); ?></strong>
                                                            </td>
                                                        </tr>
                                                        <tr>
                                                            <th>Ruangan:</th>
                                                            <td><strong><?php echo $jp->ruangan ?: '-'; ?></strong></td>
                                                        </tr>
                                                        <tr>
                                                            <th>Status:</th>
                                                            <td>
                                                                <?php
                                                                $status_class = 'secondary';
                                                                $status_text = ucfirst($jp->status);
                                                                switch ($jp->status) {
                                                                    case 'terjadwal':
                                                                        $status_class = 'info';
                                                                        break;
                                                                    case 'selesai':
                                                                        $status_class = 'success';
                                                                        break;
                                                                    case 'batal':
                                                                        $status_class = 'danger';
                                                                        break;
                                                                }
                                                                ?>
                                                                <span class="badge badge-<?php echo $status_class; ?>"><?php echo $status_text; ?></span>
                                                            </td>
                                                        </tr>
                                                    </table>
                                                </div>
                                                <div class="col-md-6">
                                                    <h6 class="font-weight-bold mb-3">Penguji:</h6>
                                                    <table class="table table-borderless">
                                                        <tr>
                                                            <th width="35%">Penguji:</th>
                                                            <td>
                                                                <strong><?php echo isset($jp->penguji_nama) ? $jp->penguji_nama : '-'; ?></strong><br>
                                                                <small class="text-muted"><?php echo isset($jp->penguji_nidn) ? $jp->penguji_nidn : '-'; ?></small>
                                                            </td>
                                                        </tr>
                                                    </table>
                                                </div>
                                            </div>
                                            <?php if (!empty($jp->catatan)): ?>
                                                <div class="mt-3">
                                                    <h6 class="font-weight-bold">Catatan:</h6>
                                                    <p class="mb-0"><?php echo nl2br(htmlspecialchars($jp->catatan)); ?></p>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    // Fungsi untuk switch tab secara manual
    function switchTab(targetId) {
        // Remove active dari semua tab link dan content
        $('#tabJadwal .nav-link').removeClass('active');
        $('#tabJadwalContent .tab-pane').removeClass('show active');
        
        // Add active ke tab yang dipilih
        var tabLink = $('#tabJadwal a[href="#' + targetId + '"]');
        var tabContent = $('#' + targetId);
        
        if (tabLink.length && tabContent.length) {
            tabLink.addClass('active');
            tabContent.addClass('show active');
            
            // Update URL hash
            if (history.pushState) {
                history.pushState(null, null, '#' + targetId);
            } else {
                window.location.hash = targetId;
            }
        }
    }
    
    // Handle click pada tab link - gunakan event delegation
    $(document).on('click', '#tabJadwal a[href^="#tab-"]', function (e) {
        e.preventDefault();
        e.stopPropagation();
        
        var target = $(this).attr('href');
        if (target && target.startsWith('#')) {
            var targetId = target.substring(1);
            switchTab(targetId);
        }
        return false;
    });
    
    // Handle hash URL saat halaman dimuat
    setTimeout(function() {
        var hash = window.location.hash;
        if (hash) {
            var targetId = hash.substring(1);
            var tabLink = $('#tabJadwal a[href="#' + targetId + '"]');
            if (tabLink.length) {
                switchTab(targetId);
            }
        }
    }, 100);
    
    // Handle back/forward browser button
    $(window).on('hashchange', function() {
        var hash = window.location.hash;
        if (hash) {
            var targetId = hash.substring(1);
            var tabLink = $('#tabJadwal a[href="#' + targetId + '"]');
            if (tabLink.length) {
                switchTab(targetId);
            }
        }
    });
});
</script>
