<!-- Halaman Utama -->
<?php 
$role = $this->session->userdata('role');
$total_mahasiswa_aktif = isset($total_mahasiswa_aktif) ? $total_mahasiswa_aktif : 0;
$statistik_kelas = isset($statistik_kelas) ? $statistik_kelas : array();
$total_dosen_aktif = isset($total_dosen_aktif) ? $total_dosen_aktif : 0;
$total_tendik_aktif = isset($total_tendik_aktif) ? $total_tendik_aktif : 0;
$total_bimbingan_skripsi = isset($total_bimbingan_skripsi) ? $total_bimbingan_skripsi : 0;
$total_bimbingan_pkl = isset($total_bimbingan_pkl) ? $total_bimbingan_pkl : 0;
$total_bimbingan_pkm = isset($total_bimbingan_pkm) ? $total_bimbingan_pkm : 0;
$is_registered_pkl = isset($is_registered_pkl) ? $is_registered_pkl : false;
$is_registered_skripsi = isset($is_registered_skripsi) ? $is_registered_skripsi : false;
$is_registered_pkm = isset($is_registered_pkm) ? $is_registered_pkm : false;
$pkm_pengajuan = isset($pkm_pengajuan) ? $pkm_pengajuan : null;
$pkm_peran = isset($pkm_peran) ? $pkm_peran : null;
$pkm_anggota_list = isset($pkm_anggota_list) ? $pkm_anggota_list : array();
?>

<?php
$siap_nama_tampil = $this->session->userdata('nama') ?: $this->session->userdata('username');
$chart_login = isset($chart_login_7hari) && is_array($chart_login_7hari) ? $chart_login_7hari : array('labels' => array(), 'values' => array());
$total_percakapan = isset($total_percakapan) ? (int) $total_percakapan : 0;
$total_pesan_chat = isset($total_pesan_chat) ? (int) $total_pesan_chat : 0;
$login_hari_ini = isset($login_hari_ini) ? (int) $login_hari_ini : 0;
?>
<?php if ($role == 'admin'): ?>
    <div class="siap-welcome">
        <h2>Selamat datang, <?php echo htmlspecialchars($siap_nama_tampil); ?>!</h2>
        <p>Panel administrasi SI-HARKAT — pantau data akademik, aktivitas, dan pesan.</p>
    </div>
    <div class="row">
        <div class="col-xl-2 col-lg-3 col-md-4 col-sm-6">
            <div class="small-box bg-info">
                <div class="inner"><h3><?php echo number_format($total_mahasiswa_aktif); ?></h3><p>Mahasiswa Aktif</p></div>
                <div class="icon"><i class="fas fa-users"></i></div>
            </div>
        </div>
        <div class="col-xl-2 col-lg-3 col-md-4 col-sm-6">
            <div class="small-box bg-primary">
                <div class="inner"><h3><?php echo number_format($total_dosen_aktif); ?></h3><p>Dosen Aktif</p></div>
                <div class="icon"><i class="fas fa-chalkboard-teacher"></i></div>
            </div>
        </div>
        <div class="col-xl-2 col-lg-3 col-md-4 col-sm-6">
            <div class="small-box bg-success">
                <div class="inner"><h3><?php echo number_format($total_tendik_aktif); ?></h3><p>Tendik Aktif</p></div>
                <div class="icon"><i class="fas fa-user-tie"></i></div>
            </div>
        </div>
        <div class="col-xl-2 col-lg-3 col-md-4 col-sm-6">
            <div class="small-box bg-warning">
                <div class="inner"><h3><?php echo number_format($login_hari_ini); ?></h3><p>Login Hari Ini</p></div>
                <div class="icon"><i class="fas fa-sign-in-alt"></i></div>
            </div>
        </div>
        <div class="col-xl-2 col-lg-3 col-md-4 col-sm-6">
            <div class="small-box bg-teal">
                <div class="inner"><h3><?php echo number_format($total_percakapan); ?></h3><p>Percakapan Chat</p></div>
                <div class="icon"><i class="fas fa-comments"></i></div>
            </div>
        </div>
        <div class="col-xl-2 col-lg-3 col-md-4 col-sm-6">
            <div class="small-box bg-danger">
                <div class="inner"><h3><?php echo number_format($total_pesan_chat); ?></h3><p>Total Pesan</p></div>
                <div class="icon"><i class="fas fa-envelope"></i></div>
            </div>
        </div>
        <?php if (!empty($statistik_kelas) && is_array($statistik_kelas)): ?>
            <?php $colorClasses = array('bg-purple', 'bg-orange', 'bg-secondary', 'bg-info', 'bg-success'); $idx = 0; ?>
            <?php foreach ($statistik_kelas as $stat): ?>
                <div class="col-xl-2 col-lg-3 col-md-4 col-sm-6">
                    <div class="small-box <?php echo $colorClasses[$idx++ % count($colorClasses)]; ?>">
                        <div class="inner"><h3><?php echo number_format($stat->jumlah); ?></h3><p>Kelas <?php echo htmlspecialchars($stat->kelas ?: '-'); ?></p></div>
                        <div class="icon"><i class="fas fa-user-graduate"></i></div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

    <div class="row">
        <div class="col-lg-7">
            <div class="card">
                <div class="card-header"><h3 class="card-title"><i class="fas fa-chart-area mr-1"></i> Login 7 Hari Terakhir</h3></div>
                <div class="card-body"><canvas id="chartAdminLogin7" style="min-height:280px;height:280px;"></canvas></div>
            </div>
        </div>
        <div class="col-lg-5">
            <div class="card">
                <div class="card-header"><h3 class="card-title"><i class="fas fa-chart-pie mr-1"></i> Mahasiswa per Kelas</h3></div>
                <div class="card-body"><canvas id="chartAdminKelas" style="min-height:280px;height:280px;"></canvas></div>
            </div>
        </div>
    </div>
    <script>
    (function(){
        var loginData = <?php echo json_encode($chart_login); ?>;
        var kelasData = <?php echo json_encode(isset($statistik_kelas) ? $statistik_kelas : array()); ?>;
        function boot(){
            if (typeof Chart === 'undefined') { setTimeout(boot, 150); return; }
            var c1 = document.getElementById('chartAdminLogin7');
            if (c1) {
                new Chart(c1.getContext('2d'), {
                    type: 'line',
                    data: {
                        labels: loginData.labels || [],
                        datasets: [{
                            label: 'Login',
                            data: loginData.values || [],
                            fill: true,
                            tension: 0.35,
                            borderColor: '#0d6efd',
                            backgroundColor: 'rgba(13,110,253,.15)',
                            pointBackgroundColor: '#0d6efd',
                            pointRadius: 4
                        }]
                    },
                    options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { display: false } }, scales: { y: { beginAtZero: true, ticks: { precision: 0 } } } }
                });
            }
            var c2 = document.getElementById('chartAdminKelas');
            if (c2) {
                var labels = [], vals = [], colors = ['#0d6efd','#6610f2','#198754','#0dcaf0','#ffc107','#dc3545','#fd7e14','#6f42c1','#20c997','#6c757d'];
                (kelasData || []).forEach(function(r,i){ labels.push(r.kelas||'-'); vals.push(parseInt(r.jumlah||0,10)); });
                if (!labels.length) { labels=['Belum ada data']; vals=[0]; }
                new Chart(c2.getContext('2d'), {
                    type: 'doughnut',
                    data: { labels: labels, datasets: [{ data: vals, backgroundColor: labels.map(function(_,i){return colors[i%colors.length];}), borderWidth: 0 }] },
                    options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { position: 'bottom' } } }
                });
            }
        }
        window.addEventListener('load', function(){ setTimeout(boot, 200); });
    })();
    </script>

<?php elseif ($role == 'sekprodi' || $role == 'kaprodi'): ?>
    <?php
    $total_skripsi_ta = isset($total_skripsi_ta) ? (int) $total_skripsi_ta : 0;
    $total_pkl_ta = isset($total_pkl_ta) ? (int) $total_pkl_ta : 0;
    $total_proposal_ta = isset($total_proposal_ta) ? (int) $total_proposal_ta : 0;
    ?>
    <div class="siap-welcome">
        <h2>Selamat datang, <?php echo htmlspecialchars($siap_nama_tampil); ?>!</h2>
        <p>Ringkasan akademik untuk peran <?php echo htmlspecialchars(ucfirst($role)); ?>.</p>
    </div>
    <div class="row">
        <div class="col-xl-2 col-lg-3 col-md-4 col-sm-6">
            <div class="small-box bg-info">
                <div class="inner"><h3><?php echo number_format($total_mahasiswa_aktif); ?></h3><p>Mahasiswa Aktif</p></div>
                <div class="icon"><i class="fas fa-users"></i></div>
            </div>
        </div>
        <div class="col-xl-2 col-lg-3 col-md-4 col-sm-6">
            <div class="small-box bg-secondary">
                <div class="inner"><h3><?php echo number_format($total_proposal_ta); ?></h3><p>Proposal TA</p></div>
                <div class="icon"><i class="fas fa-file-alt"></i></div>
            </div>
        </div>
        <div class="col-xl-2 col-lg-3 col-md-4 col-sm-6">
            <div class="small-box bg-primary">
                <div class="inner"><h3><?php echo number_format($total_skripsi_ta); ?></h3><p>Skripsi (TA)</p></div>
                <div class="icon"><i class="fas fa-graduation-cap"></i></div>
            </div>
        </div>
        <div class="col-xl-2 col-lg-3 col-md-4 col-sm-6">
            <div class="small-box bg-success">
                <div class="inner"><h3><?php echo number_format($total_pkl_ta); ?></h3><p>PKL (TA)</p></div>
                <div class="icon"><i class="fas fa-briefcase"></i></div>
            </div>
        </div>
        <?php if (!empty($statistik_kelas) && is_array($statistik_kelas)): ?>
            <?php $colorClasses = array('bg-warning', 'bg-danger', 'bg-teal', 'bg-purple', 'bg-orange'); $idx = 0; ?>
            <?php foreach ($statistik_kelas as $stat): ?>
                <div class="col-xl-2 col-lg-3 col-md-4 col-sm-6">
                    <div class="small-box <?php echo $colorClasses[$idx++ % count($colorClasses)]; ?>">
                        <div class="inner"><h3><?php echo number_format($stat->jumlah); ?></h3><p>Kelas <?php echo htmlspecialchars($stat->kelas ?: '-'); ?></p></div>
                        <div class="icon"><i class="fas fa-user-graduate"></i></div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

    <div class="row">
        <div class="col-lg-7">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-chart-bar mr-1"></i> Grafik Jumlah Mahasiswa per Kelas</h3>
                </div>
                <div class="card-body">
                    <canvas id="chartMahasiswaPerKelas" style="min-height:300px;height:300px;"></canvas>
                </div>
            </div>
        </div>
        <div class="col-lg-5">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-chart-pie mr-1"></i> Proporsi Kelas</h3>
                </div>
                <div class="card-body">
                    <canvas id="chartSekprodiDonut" style="min-height:300px;height:300px;"></canvas>
                </div>
            </div>
        </div>
    </div>

    <script>
    // Data untuk chart
    var statistikKelasData = <?php echo json_encode(isset($statistik_kelas) && is_array($statistik_kelas) ? $statistik_kelas : array()); ?>;
    
    // Fungsi untuk inisialisasi chart
    function initChartMahasiswaPerKelas() {
        // Cek Chart.js
        if (typeof Chart === 'undefined') {
            console.warn('Chart.js belum ter-load, menunggu...');
            setTimeout(initChartMahasiswaPerKelas, 200);
            return;
        }
        
        var canvas = document.getElementById('chartMahasiswaPerKelas');
        if (!canvas) {
            console.error('Canvas chartMahasiswaPerKelas tidak ditemukan');
            return;
        }
        
        try {
            var labels = [];
            var data = [];
            var colors = [
                'rgba(54, 162, 235, 0.8)',
                'rgba(75, 192, 192, 0.8)',
                'rgba(255, 206, 86, 0.8)',
                'rgba(255, 99, 132, 0.8)',
                'rgba(153, 102, 255, 0.8)',
                'rgba(255, 159, 64, 0.8)',
                'rgba(199, 199, 199, 0.8)',
                'rgba(83, 102, 255, 0.8)',
                'rgba(255, 99, 255, 0.8)',
                'rgba(99, 255, 132, 0.8)'
            ];
            
            if (statistikKelasData && statistikKelasData.length > 0) {
                statistikKelasData.forEach(function(item, idx) {
                    labels.push(item.kelas || '-');
                    data.push(parseInt(item.jumlah || 0));
                });
            } else {
                labels = ['Belum ada data'];
                data = [0];
            }
            
            var backgroundColors = labels.map(function(_, i) {
                return colors[i % colors.length];
            });
            
            var borderColors = backgroundColors.map(function(color) {
                return color.replace('0.8', '1');
            });
            
            var ctx = canvas.getContext('2d');
            var chart = new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: labels,
                    datasets: [{
                        label: 'Jumlah Mahasiswa',
                        data: data,
                        backgroundColor: backgroundColors,
                        borderColor: borderColors,
                        borderWidth: 2
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { display: false },
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    return 'Jumlah: ' + context.parsed.y + ' mahasiswa';
                                }
                            }
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: { 
                                stepSize: 1,
                                precision: 0
                            },
                            title: { 
                                display: true, 
                                text: 'Jumlah Mahasiswa' 
                            }
                        },
                        x: {
                            title: { 
                                display: true, 
                                text: 'Kelas' 
                            },
                            ticks: { 
                                maxRotation: 45, 
                                minRotation: 0 
                            }
                        }
                    }
                }
            });
            
            var donut = document.getElementById('chartSekprodiDonut');
            if (donut) {
                new Chart(donut.getContext('2d'), {
                    type: 'doughnut',
                    data: {
                        labels: labels,
                        datasets: [{ data: data, backgroundColor: backgroundColors, borderWidth: 0 }]
                    },
                    options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { position: 'bottom' } } }
                });
            }
        } catch(e) {
            console.error('Error membuat chart:', e);
            var cardBody = canvas.closest('.card-body');
            if (cardBody) {
                cardBody.innerHTML = '<div class="alert alert-danger">Gagal memuat grafik: ' + e.message + '</div>';
            }
        }
    }
    
    if (window.addEventListener) {
        window.addEventListener('load', function() { setTimeout(initChartMahasiswaPerKelas, 300); });
    } else {
        window.attachEvent('onload', function() { setTimeout(initChartMahasiswaPerKelas, 300); });
    }
    </script>

<?php elseif ($role == 'dosen'): ?>
    <?php
    $total_bimbingan_skripsi = isset($total_bimbingan_skripsi) ? $total_bimbingan_skripsi : 0;
    $total_bimbingan_proposal = isset($total_bimbingan_proposal) ? $total_bimbingan_proposal : 0;
    $total_bimbingan_pkl = isset($total_bimbingan_pkl) ? $total_bimbingan_pkl : 0;
    $total_bimbingan_pkm = isset($total_bimbingan_pkm) ? $total_bimbingan_pkm : 0;
    $total_penguji_pkl = isset($total_penguji_pkl) ? $total_penguji_pkl : 0;
    $total_penguji_skripsi = isset($total_penguji_skripsi) ? $total_penguji_skripsi : 0;
    $total_pengawasan_uts = isset($total_pengawasan_uts) ? $total_pengawasan_uts : 0;
    $total_pengawasan_uas = isset($total_pengawasan_uas) ? $total_pengawasan_uas : 0;
    $total_sks = isset($total_sks) ? $total_sks : 0;
    $ta_label = (!empty($tahun_aktif) && !empty($tahun_aktif->tahun_akademik))
        ? $tahun_aktif->tahun_akademik . (!empty($tahun_aktif->semester) ? ' (' . $tahun_aktif->semester . ')' : '')
        : 'Tahun akademik aktif';
    ?>
    <div class="siap-welcome">
        <h2>Selamat datang, <?php echo htmlspecialchars($siap_nama_tampil); ?>!</h2>
        <p>Statistik untuk: <strong><?php echo htmlspecialchars($ta_label); ?></strong></p>
    </div>
    <div class="row">
        <div class="col-lg-3 col-md-4 col-sm-6 mb-3">
            <a href="<?php echo site_url('Bimbingan_proposal'); ?>" class="text-decoration-none" style="color:inherit;">
                <div class="small-box bg-secondary">
                    <div class="inner">
                        <h3><?php echo number_format($total_bimbingan_proposal); ?></h3>
                        <p>Bimbingan Proposal TA</p>
                    </div>
                    <div class="icon"><i class="fas fa-file-alt"></i></div>
                </div>
            </a>
        </div>
        <div class="col-lg-3 col-md-4 col-sm-6 mb-3">
            <a href="<?php echo site_url('Bimbingan_skripsi'); ?>" class="text-decoration-none" style="color:inherit;">
                <div class="small-box bg-info">
                    <div class="inner">
                        <h3><?php echo number_format($total_bimbingan_skripsi); ?></h3>
                        <p>Bimbingan Skripsi</p>
                    </div>
                    <div class="icon"><i class="fas fa-graduation-cap"></i></div>
                </div>
            </a>
        </div>
        <div class="col-lg-3 col-md-4 col-sm-6 mb-3">
            <a href="<?php echo site_url('Bimbingan_pkl'); ?>" class="text-decoration-none" style="color:inherit;">
                <div class="small-box bg-success">
                    <div class="inner">
                        <h3><?php echo number_format($total_bimbingan_pkl); ?></h3>
                        <p>Bimbingan PKL</p>
                    </div>
                    <div class="icon"><i class="fas fa-briefcase"></i></div>
                </div>
            </a>
        </div>
        <div class="col-lg-3 col-md-4 col-sm-6 mb-3">
            <a href="<?php echo site_url('Bimbingan_pkm'); ?>" class="text-decoration-none" style="color:inherit;">
                <div class="small-box bg-warning">
                    <div class="inner">
                        <h3><?php echo number_format($total_bimbingan_pkm); ?></h3>
                        <p>Bimbingan PKM</p>
                    </div>
                    <div class="icon"><i class="fas fa-lightbulb"></i></div>
                </div>
            </a>
        </div>
        <div class="col-lg-3 col-md-4 col-sm-6 mb-3">
            <div class="small-box bg-orange">
                <div class="inner">
                    <h3><?php echo number_format($total_penguji_skripsi); ?></h3>
                    <p>Penguji Skripsi</p>
                </div>
                <div class="icon"><i class="fas fa-user-check"></i></div>
            </div>
        </div>
        <div class="col-lg-3 col-md-4 col-sm-6 mb-3">
            <div class="small-box bg-danger">
                <div class="inner">
                    <h3><?php echo number_format($total_penguji_pkl); ?></h3>
                    <p>Penguji PKL</p>
                </div>
                <div class="icon"><i class="fas fa-user-tie"></i></div>
            </div>
        </div>
        <div class="col-lg-3 col-md-4 col-sm-6 mb-3">
            <div class="small-box bg-primary">
                <div class="inner">
                    <h3><?php echo number_format($total_pengawasan_uts); ?></h3>
                    <p>Pengawasan UTS</p>
                </div>
                <div class="icon"><i class="fas fa-clipboard-check"></i></div>
            </div>
        </div>
        <div class="col-lg-3 col-md-4 col-sm-6 mb-3">
            <div class="small-box bg-teal">
                <div class="inner">
                    <h3><?php echo number_format($total_pengawasan_uas); ?></h3>
                    <p>Pengawasan UAS</p>
                </div>
                <div class="icon"><i class="fas fa-clipboard-list"></i></div>
            </div>
        </div>
        <div class="col-lg-3 col-md-4 col-sm-6 mb-3">
            <div class="small-box bg-purple">
                <div class="inner">
                    <h3><?php echo number_format($total_sks); ?></h3>
                    <p>Total SKS</p>
                </div>
                <div class="icon"><i class="fas fa-book"></i></div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-7">
            <div class="card">
                <div class="card-header"><h3 class="card-title"><i class="fas fa-chart-bar mr-1"></i> Beban Kerja Dosen</h3></div>
                <div class="card-body"><canvas id="chartDosenBar" style="min-height:280px;height:280px;"></canvas></div>
            </div>
        </div>
        <div class="col-lg-5">
            <div class="card">
                <div class="card-header"><h3 class="card-title"><i class="fas fa-chart-pie mr-1"></i> Komposisi Bimbingan</h3></div>
                <div class="card-body"><canvas id="chartDosenDonut" style="min-height:280px;height:280px;"></canvas></div>
            </div>
        </div>
    </div>
    <script>
    (function(){
        var barLabels = ['Proposal','Skripsi','PKL','PKM','Penguji Skripsi','Penguji PKL','UTS','UAS','SKS'];
        var barValues = [
            <?php echo (int) $total_bimbingan_proposal; ?>,
            <?php echo (int) $total_bimbingan_skripsi; ?>,
            <?php echo (int) $total_bimbingan_pkl; ?>,
            <?php echo (int) $total_bimbingan_pkm; ?>,
            <?php echo (int) $total_penguji_skripsi; ?>,
            <?php echo (int) $total_penguji_pkl; ?>,
            <?php echo (int) $total_pengawasan_uts; ?>,
            <?php echo (int) $total_pengawasan_uas; ?>,
            <?php echo (int) $total_sks; ?>
        ];
        var donutLabels = ['Proposal','Skripsi','PKL','PKM'];
        var donutValues = [
            <?php echo (int) $total_bimbingan_proposal; ?>,
            <?php echo (int) $total_bimbingan_skripsi; ?>,
            <?php echo (int) $total_bimbingan_pkl; ?>,
            <?php echo (int) $total_bimbingan_pkm; ?>
        ];
        function boot(){
            if (typeof Chart === 'undefined') { setTimeout(boot, 150); return; }
            var b = document.getElementById('chartDosenBar');
            if (b) {
                new Chart(b.getContext('2d'), {
                    type: 'bar',
                    data: {
                        labels: barLabels,
                        datasets: [{
                            data: barValues,
                            backgroundColor: ['#6c757d','#0d6efd','#198754','#ffc107','#fd7e14','#dc3545','#6610f2','#0dcaf0','#6f42c1'],
                            borderWidth: 0,
                            borderRadius: 6
                        }]
                    },
                    options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { display: false } }, scales: { y: { beginAtZero: true, ticks: { precision: 0 } } } }
                });
            }
            var d = document.getElementById('chartDosenDonut');
            if (d) {
                new Chart(d.getContext('2d'), {
                    type: 'doughnut',
                    data: {
                        labels: donutLabels,
                        datasets: [{ data: donutValues, backgroundColor: ['#6c757d','#0d6efd','#198754','#ffc107'], borderWidth: 0 }]
                    },
                    options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { position: 'bottom' } } }
                });
            }
        }
        window.addEventListener('load', function(){ setTimeout(boot, 200); });
    })();
    </script>

<?php elseif ($role == 'mahasiswa'): ?>
    <!-- Halaman Utama Mahasiswa -->
    
    <?php if (!empty($proposal_card)) { $this->load->view('proposal_partials/dashboard_card', $proposal_card); } ?>

    <!-- Countdown Sidang Skripsi -->
    <?php if (isset($is_registered_skripsi) && $is_registered_skripsi && isset($jadwal_sidang) && $jadwal_sidang && isset($jadwal_sidang_timestamp)): ?>
    <div class="row mb-4">
        <div class="col-12">
            <div class="card card-outline card-success">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-clock"></i> Countdown Sidang Skripsi
                        <?php 
                        $tgl = isset($jadwal_sidang->tanggal_ujian) ? $jadwal_sidang->tanggal_ujian : (isset($jadwal_sidang->tanggal) ? $jadwal_sidang->tanggal : '');
                        $jam = isset($jadwal_sidang->jam_mulai) ? $jadwal_sidang->jam_mulai : '';
                        $ruang = isset($jadwal_sidang->ruangan) ? $jadwal_sidang->ruangan : '';
                        ?>
                        <small class="text-muted ml-2"><?php echo $tgl ? date('d/m/Y', strtotime($tgl)) : ''; ?> <?php echo $jam ? date('H:i', strtotime($jam)) : ''; ?><?php echo $ruang ? ' - ' . htmlspecialchars($ruang) : ''; ?></small>
                    </h3>
                </div>
                <div class="card-body text-center">
                    <style>
                        .countdown-box-hari { background: linear-gradient(135deg, #007bff 0%, #0056b3 100%); color: #fff; border: none; border-radius: 8px; padding: 12px; box-shadow: 0 2px 8px rgba(0,123,255,0.3); }
                        .countdown-box-jam { background: linear-gradient(135deg, #17a2b8 0%, #117a8b 100%); color: #fff; border: none; border-radius: 8px; padding: 12px; box-shadow: 0 2px 8px rgba(23,162,184,0.3); }
                        .countdown-box-menit { background: linear-gradient(135deg, #ffc107 0%, #e0a800 100%); color: #212529; border: none; border-radius: 8px; padding: 12px; box-shadow: 0 2px 8px rgba(255,193,7,0.3); }
                        .countdown-box-detik { background: linear-gradient(135deg, #dc3545 0%, #c82333 100%); color: #fff; border: none; border-radius: 8px; padding: 12px; box-shadow: 0 2px 8px rgba(220,53,69,0.3); }
                        .countdown-box-hari .countdown-label, .countdown-box-jam .countdown-label, .countdown-box-detik .countdown-label { color: rgba(255,255,255,0.9) !important; }
                        .countdown-box-menit .countdown-label { color: rgba(33,37,41,0.8) !important; }
                        .countdown-box-hari .countdown-value, .countdown-box-jam .countdown-value, .countdown-box-detik .countdown-value { font-weight: bold; font-size: 1.5rem; }
                        .countdown-box-menit .countdown-value { font-weight: bold; font-size: 1.5rem; color: #212529; }
                    </style>
                    <div class="row justify-content-center" id="countdown-sidang">
                        <div class="col-3 col-md-2 mb-2">
                            <div class="countdown-box-hari p-2">
                                <div class="countdown-value" id="countdown-hari">--</div>
                                <div class="countdown-label small">Hari</div>
                            </div>
                        </div>
                        <div class="col-3 col-md-2 mb-2">
                            <div class="countdown-box-jam p-2">
                                <div class="countdown-value" id="countdown-jam">--</div>
                                <div class="countdown-label small">Jam</div>
                            </div>
                        </div>
                        <div class="col-3 col-md-2 mb-2">
                            <div class="countdown-box-menit p-2">
                                <div class="countdown-value" id="countdown-menit">--</div>
                                <div class="countdown-label small">Menit</div>
                            </div>
                        </div>
                        <div class="col-3 col-md-2 mb-2">
                            <div class="countdown-box-detik p-2">
                                <div class="countdown-value" id="countdown-detik">--</div>
                                <div class="countdown-label small">Detik</div>
                            </div>
                        </div>
                    </div>
                    <p class="text-muted mb-0 mt-2 small" id="countdown-status"></p>
                </div>
            </div>
        </div>
    </div>
    <script>
    (function() {
        var targetTime = <?php echo $jadwal_sidang_timestamp; ?> * 1000;
        function updateCountdown() {
            var now = Date.now();
            var diff = targetTime - now;
            if (diff <= 0) {
                document.getElementById('countdown-hari').textContent = '0';
                document.getElementById('countdown-jam').textContent = '0';
                document.getElementById('countdown-menit').textContent = '0';
                document.getElementById('countdown-detik').textContent = '0';
                var status = document.getElementById('countdown-status');
                if (status) status.textContent = 'Sidang telah dimulai atau selesai.';
                return;
            }
            var days = Math.floor(diff / (1000 * 60 * 60 * 24));
            var hours = Math.floor((diff % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
            var minutes = Math.floor((diff % (1000 * 60 * 60)) / (1000 * 60));
            var seconds = Math.floor((diff % (1000 * 60)) / 1000);
            document.getElementById('countdown-hari').textContent = days;
            document.getElementById('countdown-jam').textContent = String(hours).padStart(2, '0');
            document.getElementById('countdown-menit').textContent = String(minutes).padStart(2, '0');
            document.getElementById('countdown-detik').textContent = String(seconds).padStart(2, '0');
        }
        updateCountdown();
        setInterval(updateCountdown, 1000);
    })();
    </script>
    <?php endif; ?>
    
    <!-- Selamat Datang dan Progress -->
    <div class="row mb-4">
        <!-- Selamat Datang -->
        <div class="<?php echo ($is_registered_pkl || $is_registered_skripsi) ? 'col-lg-8 col-md-7' : 'col-12'; ?> mb-3">
            <div class="card card-primary card-outline">
                <div class="card-body">
                    <h2 class="mb-2">
                        <i class="fas fa-user-graduate text-primary"></i> 
                        Selamat Datang, <?php echo isset($mahasiswa) && $mahasiswa ? htmlspecialchars($mahasiswa->nama_mahasiswa) : 'Mahasiswa'; ?>!
                    </h2>
                    <p class="text-muted mb-2">
                        <i class="fas fa-info-circle"></i> 
                        NIM: <strong><?php echo isset($mahasiswa) && $mahasiswa ? htmlspecialchars($mahasiswa->nim) : '-'; ?></strong>
                        <?php if (isset($mahasiswa) && $mahasiswa && $mahasiswa->kelas): ?>
                            | Kelas: <strong><?php echo htmlspecialchars($mahasiswa->kelas); ?></strong>
                        <?php endif; ?>
                    </p>
                    <p class="text-muted mb-0">
                        <i class="fas fa-calendar-alt"></i> 
                        Tahun Akademik: <strong><?php echo isset($tahun_aktif) && $tahun_aktif ? htmlspecialchars($tahun_aktif->tahun_akademik) : '-'; ?></strong>
                    </p>
                </div>
            </div>
        </div>
        
        <!-- Progress PKL dan Skripsi (di samping welcome) -->
        <?php if ($is_registered_pkl || $is_registered_skripsi): ?>
        <div class="col-lg-4 col-md-5 mb-3">
            <?php if (isset($is_registered_pkl) && $is_registered_pkl && isset($pkl_progress)): ?>
            <div class="card card-outline card-info mb-3">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-briefcase"></i> Progress PKL
                    </h3>
                </div>
                <div class="card-body">
                    <!-- Progress Circle -->
                    <div class="text-center mb-3">
                        <div class="progress-circle-wrapper">
                            <div class="progress-circle" data-percent="<?php echo $pkl_progress['percentage']; ?>" data-color="#17a2b8">
                                <svg class="progress-circle-svg" viewBox="0 0 200 200">
                                    <circle class="progress-circle-bg" cx="100" cy="100" r="90"></circle>
                                    <circle class="progress-circle-progress" cx="100" cy="100" r="90" 
                                            data-percent="<?php echo $pkl_progress['percentage']; ?>"></circle>
                                </svg>
                                <div class="progress-circle-content">
                                    <span class="progress-circle-percent"><?php echo $pkl_progress['percentage']; ?>%</span>
                                    <span class="progress-circle-text"><?php echo $pkl_progress['current_step']; ?>/<?php echo $pkl_progress['total_steps']; ?></span>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Detail Tahapan -->
                    <div class="mt-3">
                        <h5 class="mb-3 text-center">Tahapan PKL</h5>
                        <div class="progress-steps">
                            <?php foreach ($pkl_progress['steps'] as $index => $step): ?>
                                <div class="progress-step-item <?php echo $step['completed'] ? 'completed' : ''; ?>">
                                    <div class="step-indicator">
                                        <?php if ($step['completed']): ?>
                                            <i class="fas fa-check-circle text-success"></i>
                                        <?php else: ?>
                                            <i class="far fa-circle text-muted"></i>
                                        <?php endif; ?>
                                    </div>
                                    <div class="step-label"><?php echo $step['name']; ?></div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            </div>
            <?php endif; ?>
            
            <?php if (isset($is_registered_skripsi) && $is_registered_skripsi && isset($skripsi_progress)): ?>
            <div class="card card-outline card-success">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-graduation-cap"></i> Progress Skripsi
                    </h3>
                </div>
                <div class="card-body">
                    <!-- Progress Circle -->
                    <div class="text-center mb-3">
                        <div class="progress-circle-wrapper">
                            <div class="progress-circle" data-percent="<?php echo $skripsi_progress['percentage']; ?>" data-color="#28a745">
                                <svg class="progress-circle-svg" viewBox="0 0 200 200">
                                    <circle class="progress-circle-bg" cx="100" cy="100" r="90"></circle>
                                    <circle class="progress-circle-progress" cx="100" cy="100" r="90" 
                                            data-percent="<?php echo $skripsi_progress['percentage']; ?>"></circle>
                                </svg>
                                <div class="progress-circle-content">
                                    <span class="progress-circle-percent"><?php echo $skripsi_progress['percentage']; ?>%</span>
                                    <span class="progress-circle-text"><?php echo $skripsi_progress['current_step']; ?>/<?php echo $skripsi_progress['total_steps']; ?></span>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Detail Tahapan -->
                    <div class="mt-3">
                        <h5 class="mb-3 text-center">Tahapan Skripsi</h5>
                        <div class="progress-steps">
                            <?php foreach ($skripsi_progress['steps'] as $index => $step): ?>
                                <div class="progress-step-item <?php echo $step['completed'] ? 'completed' : ''; ?>">
                                    <div class="step-indicator">
                                        <?php if ($step['completed']): ?>
                                            <i class="fas fa-check-circle text-success"></i>
                                        <?php else: ?>
                                            <i class="far fa-circle text-muted"></i>
                                        <?php endif; ?>
                                    </div>
                                    <div class="step-label"><?php echo $step['name']; ?></div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            </div>
            <?php endif; ?>
        </div>
        <?php endif; ?>
    </div>

    <?php if ($is_registered_pkm && $pkm_pengajuan): ?>
    <?php $this->load->helper('pkm'); ?>
    <div class="row mb-4">
        <div class="col-12">
            <div class="card card-outline card-warning">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-lightbulb"></i> PKM — Periode Aktif</h3>
                    <div class="card-tools">
                        <a href="<?php echo site_url('Pkm_mahasiswa'); ?>" class="btn btn-sm btn-outline-primary">Detail PKM</a>
                        <?php if (pkm_bimbingan_aktif($pkm_pengajuan)): ?>
                            <a href="<?php echo site_url('Bimbingan_pkm_mahasiswa/bimbingan'); ?>" class="btn btn-sm btn-primary">Bimbingan PKM</a>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive mb-3">
                        <table class="table table-bordered table-sm mb-0">
                            <tbody>
                                <tr>
                                    <th width="18%">Peran Anda</th>
                                    <td>
                                        <span class="badge badge-<?php echo $pkm_peran === 'ketua' ? 'primary' : 'info'; ?>">
                                            <?php echo $pkm_peran === 'ketua' ? 'Ketua Tim' : 'Anggota Tim'; ?>
                                        </span>
                                    </td>
                                </tr>
                                <tr>
                                    <th>Jenis PKM</th>
                                    <td><?php echo htmlspecialchars($pkm_pengajuan->nama_jenis); ?></td>
                                </tr>
                                <tr>
                                    <th>Periode</th>
                                    <td><?php echo htmlspecialchars($pkm_pengajuan->nama_periode); ?></td>
                                </tr>
                                <tr>
                                    <th>Dosen Pembimbing</th>
                                    <td><?php echo htmlspecialchars($pkm_pengajuan->pembimbing_final_nama ?: $pkm_pengajuan->pembimbing_request_nama ?: '-'); ?></td>
                                </tr>
                                <tr>
                                    <th>Judul</th>
                                    <td><?php echo htmlspecialchars($pkm_pengajuan->judul_disetujui ?: $pkm_pengajuan->judul_pkm); ?></td>
                                </tr>
                                <tr>
                                    <th>Status Judul</th>
                                    <td><span class="badge badge-<?php echo pkm_status_acc_badge($pkm_pengajuan->status_acc_judul); ?>"><?php echo ucfirst($pkm_pengajuan->status_acc_judul); ?></span></td>
                                </tr>
                                <?php if (!empty($pkm_pengajuan->tanggal_selesai)): ?>
                                <tr>
                                    <th>Batas Submit Proposal Final</th>
                                    <td>
                                        <?php echo date('d/m/Y', strtotime($pkm_pengajuan->tanggal_selesai)); ?>
                                        <?php if (!pkm_proposal_final_submit_allowed($pkm_pengajuan)): ?>
                                            <span class="badge badge-danger ml-1">Waktu habis</span>
                                        <?php else: ?>
                                            <div class="pkm-countdown-proposal p-2 rounded mt-2" data-deadline="<?php echo htmlspecialchars(pkm_proposal_final_deadline_iso($pkm_pengajuan->tanggal_selesai)); ?>" style="background:rgba(40,167,69,.12);border:1px dashed #28a745;display:inline-block;">
                                                <small class="text-success d-block mb-1"><i class="fas fa-hourglass-half"></i> Sisa waktu submit Proposal Final PKM</small>
                                                <span class="countdown-text font-weight-bold text-success">—</span>
                                            </div>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>

                    <h6 class="mb-2"><i class="fas fa-users"></i> Anggota Tim</h6>
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped table-sm mb-0">
                            <thead>
                                <tr>
                                    <th width="8%">#</th>
                                    <th width="18%">NIM</th>
                                    <th>Nama</th>
                                    <th width="22%">Prodi</th>
                                    <th width="14%">Peran</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr class="table-primary">
                                    <td>K</td>
                                    <td><code><?php echo htmlspecialchars($pkm_pengajuan->ketua_nim); ?></code></td>
                                    <td><?php echo htmlspecialchars($pkm_pengajuan->ketua_nama); ?></td>
                                    <td>S1 Sistem Informasi</td>
                                    <td><span class="badge badge-primary">Ketua</span></td>
                                </tr>
                                <?php foreach ($pkm_anggota_list as $a): ?>
                                    <tr>
                                        <td><?php echo (int) $a->urutan; ?></td>
                                        <td><code><?php echo htmlspecialchars($a->tipe === 'internal' ? ($a->nim ?? '-') : ($a->nim_manual ?? '-')); ?></code></td>
                                        <td><?php echo htmlspecialchars($a->tipe === 'internal' ? ($a->nama_mahasiswa ?? '-') : ($a->nama_manual ?? '-')); ?></td>
                                        <td><?php echo htmlspecialchars($a->tipe === 'internal' ? pkm_prodi_default() : ($a->prodi_asal ?? '-')); ?></td>
                                        <td><span class="badge badge-secondary"><?php echo pkm_anggota_tipe_label($a->tipe); ?></span></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>
    <?php if ($is_registered_pkm && $pkm_pengajuan && !empty($pkm_pengajuan->tanggal_selesai) && pkm_proposal_final_submit_allowed($pkm_pengajuan)): ?>
    <script>
    (function() {
        function pad(n) { return n < 10 ? '0' + n : n; }
        function updatePkmProposalCountdowns() {
            document.querySelectorAll('.pkm-countdown-proposal').forEach(function(box) {
                var el = box.querySelector('.countdown-text');
                if (!el) return;
                var deadline = new Date(box.getAttribute('data-deadline')).getTime();
                var diff = deadline - Date.now();
                if (isNaN(deadline) || diff <= 0) {
                    el.innerHTML = '<span class="text-danger">Deadline telah lewat</span>';
                    return;
                }
                var days = Math.floor(diff / 86400000);
                var hours = Math.floor((diff % 86400000) / 3600000);
                var mins = Math.floor((diff % 3600000) / 60000);
                var secs = Math.floor((diff % 60000) / 1000);
                el.innerHTML = days + ' <small>hari</small> ' + pad(hours) + ' <small>jam</small> ' + pad(mins) + ' <small>menit</small> ' + pad(secs) + ' <small>detik</small>';
            });
        }
        updatePkmProposalCountdowns();
        setInterval(updatePkmProposalCountdowns, 1000);
    })();
    </script>
    <?php endif; ?>
    
    <style>
    .progress-circle-wrapper {
        display: flex;
        justify-content: center;
    }
    
    .progress-circle {
        position: relative;
        width: 150px;
        height: 150px;
        margin: 0 auto;
    }
    
    .progress-circle-svg {
        width: 100%;
        height: 100%;
        transform: rotate(-90deg);
    }
    
    .progress-circle-bg {
        fill: none;
        stroke: #e0e0e0;
        stroke-width: 12;
    }
    
    .progress-circle-progress {
        fill: none;
        stroke-width: 12;
        stroke-linecap: round;
        stroke-dasharray: 565.48;
        stroke-dashoffset: 565.48;
        transition: stroke-dashoffset 1s ease-in-out;
    }
    
    .progress-circle-content {
        position: absolute;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        text-align: center;
    }
    
    .progress-circle-percent {
        display: block;
        font-size: 24px;
        font-weight: bold;
        color: #333;
    }
    
    .progress-circle-text {
        display: block;
        font-size: 12px;
        color: #666;
        margin-top: 3px;
    }
    
    .progress-steps {
        text-align: left;
        max-height: 400px;
        overflow-y: auto;
        padding: 5px;
    }
    
    .progress-step-item {
        display: flex;
        align-items: center;
        padding: 8px 0;
        border-bottom: 1px solid #f0f0f0;
    }
    
    .progress-step-item:last-child {
        border-bottom: none;
    }
    
    .progress-step-item.completed .step-label {
        color: #28a745;
        font-weight: 500;
    }
    
    .step-indicator {
        width: 30px;
        text-align: center;
        margin-right: 10px;
    }
    
    .step-indicator i {
        font-size: 18px;
    }
    
    .step-label {
        flex: 1;
        font-size: 14px;
        color: #666;
    }
    </style>
    
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // Animate PKL progress circle
        <?php if (isset($is_registered_pkl) && $is_registered_pkl && isset($pkl_progress)): ?>
        var pklCircles = document.querySelectorAll('.card-info .progress-circle-progress');
        pklCircles.forEach(function(pklCircle) {
            var pklPercent = <?php echo $pkl_progress['percentage']; ?>;
            var pklColor = '#17a2b8';
            var circumference = 2 * Math.PI * 90;
            var offset = circumference - (pklPercent / 100) * circumference;
            pklCircle.style.strokeDashoffset = offset;
            pklCircle.style.stroke = pklColor;
        });
        <?php endif; ?>
        
        // Animate Skripsi progress circle
        <?php if (isset($is_registered_skripsi) && $is_registered_skripsi && isset($skripsi_progress)): ?>
        var skripsiCircles = document.querySelectorAll('.card-success .progress-circle-progress');
        skripsiCircles.forEach(function(skripsiCircle) {
            var skripsiPercent = <?php echo $skripsi_progress['percentage']; ?>;
            var skripsiColor = '#28a745';
            var circumference = 2 * Math.PI * 90;
            var offset = circumference - (skripsiPercent / 100) * circumference;
            skripsiCircle.style.strokeDashoffset = offset;
            skripsiCircle.style.stroke = skripsiColor;
        });
        <?php endif; ?>
    });
    </script>

    <!-- Struktur Kurikulum -->
    <?php if (isset($kurikulum_info) && $kurikulum_info && !empty($struktur_kurikulum)): ?>
    <div class="row mt-4">
        <div class="col-12">
            <div class="card card-outline card-primary">
                <div class="card-header" style="cursor: pointer;" id="kurikulumHeader" onclick="toggleKurikulum()">
                    <h3 class="card-title">
                        <i class="fas fa-book"></i> Struktur Kurikulum
                        <span class="badge badge-<?php echo $kurikulum_info->is_aktif ? 'success' : 'secondary'; ?> ml-2">
                            <?php echo $kurikulum_info->is_aktif ? 'Aktif' : 'Tidak Aktif'; ?>
                        </span>
                    </h3>
                    <div class="card-tools">
                        <small class="text-muted mr-3">
                            <strong><?php echo htmlspecialchars($kurikulum_info->nama_kurikulum); ?></strong>
                            (Tahun <?php echo $kurikulum_info->tahun_berlaku; ?>)
                        </small>
                        <i class="fas fa-chevron-up" id="kurikulumChevron"></i>
                    </div>
                </div>
                <div class="collapse" id="strukturKurikulumCollapse" style="display: block;">
                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-12">
                            <div class="alert alert-info">
                                <i class="fas fa-info-circle"></i>
                                <strong>Keterangan:</strong>
                                <span class="badge badge-success ml-2"><i class="fas fa-check"></i> Sudah Diambil</span>
                                <span class="badge badge-warning ml-2"><i class="fas fa-clock"></i> Sedang Diambil</span>
                                <span class="badge badge-secondary ml-2"><i class="fas fa-calendar"></i> Akan Diambil</span>
                            </div>
                        </div>
                    </div>
                    
                    <?php 
                    ksort($struktur_kurikulum); // Urutkan berdasarkan semester
                    $semester_aktif_mhs = isset($mahasiswa->semester_aktif) ? intval($mahasiswa->semester_aktif) : 1;
                    ?>
                    
                    <div class="row">
                        <?php foreach ($struktur_kurikulum as $semester => $mata_kuliah_list): ?>
                            <?php 
                            $semester_int = intval($semester);
                            $is_semester_aktif = ($semester_int == $semester_aktif_mhs);
                            $card_class = $is_semester_aktif ? 'card-warning' : ($semester_int < $semester_aktif_mhs ? 'card-success' : 'card-secondary');
                            ?>
                            <div class="col-md-6 mb-3">
                                <div class="card <?php echo $card_class; ?> card-outline">
                                    <div class="card-header">
                                        <h5 class="card-title mb-0">
                                            <i class="fas fa-graduation-cap"></i> Semester <?php echo $semester; ?>
                                            <?php if ($is_semester_aktif): ?>
                                                <span class="badge badge-warning ml-2">Semester Aktif</span>
                                            <?php endif; ?>
                                        </h5>
                                    </div>
                                    <div class="card-body p-2">
                                        <div class="table-responsive">
                                            <table class="table table-sm table-bordered mb-0">
                                                <thead>
                                                    <tr>
                                                        <th width="30%">Kode</th>
                                                        <th width="50%">Mata Kuliah</th>
                                                        <th width="10%" class="text-center">SKS</th>
                                                        <th width="10%" class="text-center">Status</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?php 
                                                    $total_sks_semester = 0;
                                                    foreach ($mata_kuliah_list as $mk): 
                                                        $total_sks_semester += intval($mk->sks);
                                                        
                                                        // Tentukan badge status
                                                        $status_badge = '';
                                                        if ($mk->status_mk == 'sudah_diambil') {
                                                            $status_badge = '<span class="badge badge-success" title="Sudah Diambil"><i class="fas fa-check"></i></span>';
                                                        } elseif ($mk->status_mk == 'sedang_diambil') {
                                                            $status_badge = '<span class="badge badge-warning" title="Sedang Diambil"><i class="fas fa-clock"></i></span>';
                                                        } else {
                                                            $status_badge = '<span class="badge badge-secondary" title="Akan Diambil"><i class="fas fa-calendar"></i></span>';
                                                        }
                                                    ?>
                                                        <tr>
                                                            <td><small><?php echo htmlspecialchars($mk->kode_mk); ?></small></td>
                                                            <td>
                                                                <small><?php echo htmlspecialchars($mk->nama_mk); ?></small>
                                                            </td>
                                                            <td class="text-center"><small><?php echo $mk->sks; ?></small></td>
                                                            <td class="text-center">
                                                                <?php echo $status_badge; ?>
                                                            </td>
                                                        </tr>
                                                    <?php endforeach; ?>
                                                </tbody>
                                                <tfoot>
                                                    <tr>
                                                        <td colspan="2" class="text-right"><strong>Total SKS:</strong></td>
                                                        <td class="text-center"><strong><?php echo $total_sks_semester; ?></strong></td>
                                                        <td></td>
                                                    </tr>
                                                </tfoot>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                </div>
            </div>
        </div>
    </div>
    
    <script>
    function toggleKurikulum() {
        var collapse = document.getElementById('strukturKurikulumCollapse');
        var chevron = document.getElementById('kurikulumChevron');
        
        if (collapse.style.display === 'none' || collapse.style.display === '') {
            collapse.style.display = 'block';
            chevron.classList.remove('fa-chevron-down');
            chevron.classList.add('fa-chevron-up');
        } else {
            collapse.style.display = 'none';
            chevron.classList.remove('fa-chevron-up');
            chevron.classList.add('fa-chevron-down');
        }
    }
    </script>
    <?php elseif (isset($mahasiswa) && $mahasiswa && !$mahasiswa->id_kurikulum): ?>
    <div class="row mt-4">
        <div class="col-12">
            <div class="card card-outline card-warning">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-exclamation-triangle"></i> Struktur Kurikulum
                    </h3>
                </div>
                <div class="card-body">
                    <div class="alert alert-warning">
                        <i class="fas fa-info-circle"></i>
                        Anda belum memiliki kurikulum yang ditetapkan. Silakan hubungi admin atau sekprodi untuk menetapkan kurikulum.
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>
    <!-- End of mahasiswa role block -->

<?php else: ?>
    <div class="alert alert-info">
        <h5>Selamat Datang di Halaman Utama</h5>
        <p>Selamat datang di Sistem Informasi Administrasi Prodi.</p>
    </div>
<?php endif; ?>

