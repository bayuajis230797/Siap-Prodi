<div class="row">
    <!-- Statistik Card -->
    <div class="col-lg-3 col-6">
        <div class="small-box bg-info">
            <div class="inner">
                <h3><?php echo $total_mahasiswa; ?></h3>
                <p>Total Mahasiswa</p>
            </div>
            <div class="icon">
                <i class="fas fa-user-graduate"></i>
            </div>
            <a href="<?php echo site_url('Mahasiswa'); ?>" class="small-box-footer">
                More info <i class="fas fa-arrow-circle-right"></i>
            </a>
        </div>
    </div>

    <div class="col-lg-3 col-6">
        <div class="small-box bg-success">
            <div class="inner">
                <h3><?php echo $total_dosen; ?></h3>
                <p>Total Dosen</p>
            </div>
            <div class="icon">
                <i class="fas fa-chalkboard-teacher"></i>
            </div>
            <a href="<?php echo site_url('Dosen'); ?>" class="small-box-footer">
                More info <i class="fas fa-arrow-circle-right"></i>
            </a>
        </div>
    </div>

    <div class="col-lg-3 col-6">
        <div class="small-box bg-warning">
            <div class="inner">
                <h3><?php echo isset($total_tendik) ? $total_tendik : 0; ?></h3>
                <p>Total Tendik</p>
            </div>
            <div class="icon">
                <i class="fas fa-users"></i>
            </div>
            <a href="<?php echo site_url('Tendik'); ?>" class="small-box-footer">
                More info <i class="fas fa-arrow-circle-right"></i>
            </a>
        </div>
    </div>

    <div class="col-lg-3 col-6">
        <div class="small-box bg-danger">
            <div class="inner">
                <h3><?php echo $tahun_aktif ? $tahun_aktif->tahun_akademik . ' ' . ucfirst($tahun_aktif->semester) : '-'; ?></h3>
                <p>Tahun Akademik Aktif</p>
            </div>
            <div class="icon">
                <i class="fas fa-calendar-alt"></i>
            </div>
            <a href="<?php echo site_url('Tahun_akademik'); ?>" class="small-box-footer">
                More info <i class="fas fa-arrow-circle-right"></i>
            </a>
        </div>
    </div>
</div>

<!-- Charts Row -->
<div class="row">
    <!-- Chart: Distribusi Mahasiswa per Semester -->
    <div class="col-lg-6">
        <div class="card">
            <div class="card-header border-0">
                <h3 class="card-title">
                    <i class="fas fa-chart-bar mr-1"></i>
                    Distribusi Mahasiswa per Semester
                </h3>
            </div>
            <div class="card-body">
                <canvas id="chartSemester" style="min-height: 250px; height: 250px; max-height: 250px; max-width: 100%;"></canvas>
            </div>
        </div>
    </div>

    <!-- Chart: Statistik Dosen -->
    <div class="col-lg-6">
        <div class="card">
            <div class="card-header border-0">
                <h3 class="card-title">
                    <i class="fas fa-chart-pie mr-1"></i>
                    Statistik Dosen
                </h3>
            </div>
            <div class="card-body">
                <canvas id="chartDosen" style="min-height: 250px; height: 250px; max-height: 250px; max-width: 100%;"></canvas>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <!-- Chart: Distribusi Mahasiswa per Kelas (Top 10) -->
    <div class="col-lg-6">
        <div class="card">
            <div class="card-header border-0">
                <h3 class="card-title">
                    <i class="fas fa-chart-bar mr-1"></i>
                    Distribusi Mahasiswa per Kelas (Top 10)
                </h3>
            </div>
            <div class="card-body">
                <canvas id="chartKelas" style="min-height: 250px; height: 250px; max-height: 250px; max-width: 100%;"></canvas>
            </div>
        </div>
    </div>

    <!-- Chart: Trend Mahasiswa per Angkatan -->
    <div class="col-lg-6">
        <div class="card">
            <div class="card-header border-0">
                <h3 class="card-title">
                    <i class="fas fa-chart-line mr-1"></i>
                    Trend Mahasiswa per Angkatan (5 Tahun Terakhir)
                </h3>
            </div>
            <div class="card-body">
                <canvas id="chartAngkatan" style="min-height: 250px; height: 250px; max-height: 250px; max-width: 100%;"></canvas>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-tachometer-alt"></i> Dashboard Admin Sistem
                </h3>
            </div>
            <div class="card-body">
                <p class="lead">Selamat datang di SIAP PRODI - Sistem Informasi Administrasi Prodi</p>
                <p>Sebagai <strong>Admin Sistem</strong>, Anda memiliki akses penuh untuk mengelola:</p>
                <ul>
                    <li>Master Data (Dosen, Tendik, Mahasiswa, Mata Kuliah Aktif, Kurikulum, Tahun Akademik, Ruang Kelas)</li>
                    <li>System (Management User)</li>
                    <li>Laporan</li>
                </ul>
                <div class="alert alert-info">
                    <i class="fas fa-info-circle"></i> 
                    <strong>Catatan:</strong> Menu Akademik, Honor, dan RKAT dapat diakses oleh Sekretaris Prodi.
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Tunggu sampai semua library dan DOM ready
(function() {
    function initCharts() {
        // Pastikan Chart.js sudah ter-load
        if (typeof Chart === 'undefined') {
            console.error('Chart.js tidak ter-load!');
            setTimeout(initCharts, 100);
            return;
        }
        
        // Pastikan canvas element ada
        const canvasSemester = document.getElementById('chartSemester');
        if (!canvasSemester) {
            console.error('Canvas chartSemester tidak ditemukan!');
            setTimeout(initCharts, 100);
            return;
        }
        
        console.log('Memulai inisialisasi chart...');
        
        try {
            // Chart: Distribusi Mahasiswa per Semester
            const ctxSemester = canvasSemester.getContext('2d');
            const chartSemester = new Chart(ctxSemester, {
                type: 'bar',
                data: {
                    labels: ['Semester 1', 'Semester 2', 'Semester 3', 'Semester 4', 'Semester 5', 'Semester 6', 'Semester 7', 'Semester 8'],
                    datasets: [{
                        label: 'Jumlah Mahasiswa',
                        data: [
                            <?php echo isset($statistik_semester[1]) ? $statistik_semester[1] : 0; ?>,
                            <?php echo isset($statistik_semester[2]) ? $statistik_semester[2] : 0; ?>,
                            <?php echo isset($statistik_semester[3]) ? $statistik_semester[3] : 0; ?>,
                            <?php echo isset($statistik_semester[4]) ? $statistik_semester[4] : 0; ?>,
                            <?php echo isset($statistik_semester[5]) ? $statistik_semester[5] : 0; ?>,
                            <?php echo isset($statistik_semester[6]) ? $statistik_semester[6] : 0; ?>,
                            <?php echo isset($statistik_semester[7]) ? $statistik_semester[7] : 0; ?>,
                            <?php echo isset($statistik_semester[8]) ? $statistik_semester[8] : 0; ?>
                        ],
                        backgroundColor: [
                            'rgba(54, 162, 235, 0.8)',
                            'rgba(75, 192, 192, 0.8)',
                            'rgba(255, 206, 86, 0.8)',
                            'rgba(153, 102, 255, 0.8)',
                            'rgba(255, 159, 64, 0.8)',
                            'rgba(199, 199, 199, 0.8)',
                            'rgba(83, 102, 255, 0.8)',
                            'rgba(255, 99, 132, 0.8)'
                        ],
                        borderColor: [
                            'rgba(54, 162, 235, 1)',
                            'rgba(75, 192, 192, 1)',
                            'rgba(255, 206, 86, 1)',
                            'rgba(153, 102, 255, 1)',
                            'rgba(255, 159, 64, 1)',
                            'rgba(199, 199, 199, 1)',
                            'rgba(83, 102, 255, 1)',
                            'rgba(255, 99, 132, 1)'
                        ],
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: false
                        },
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
                                stepSize: 1
                            }
                        }
                    }
                }
            });

            // Chart: Statistik Dosen (Pie Chart)
            const canvasDosen = document.getElementById('chartDosen');
            if (!canvasDosen) {
                console.error('Canvas chartDosen tidak ditemukan!');
            } else {
                const ctxDosen = canvasDosen.getContext('2d');
                const chartDosen = new Chart(ctxDosen, {
                    type: 'pie',
                    data: {
                        labels: ['Dosen Aktif', 'Pembimbing Skripsi', 'Pembimbing PKL'],
                        datasets: [{
                            data: [
                                <?php echo isset($dosen_aktif) ? $dosen_aktif : 0; ?>,
                                <?php echo isset($dosen_pembimbing_skripsi) ? $dosen_pembimbing_skripsi : 0; ?>,
                                <?php echo isset($dosen_pembimbing_pkl) ? $dosen_pembimbing_pkl : 0; ?>
                            ],
                            backgroundColor: [
                                'rgba(54, 162, 235, 0.8)',
                                'rgba(75, 192, 192, 0.8)',
                                'rgba(255, 206, 86, 0.8)'
                            ],
                            borderColor: [
                                'rgba(54, 162, 235, 1)',
                                'rgba(75, 192, 192, 1)',
                                'rgba(255, 206, 86, 1)'
                            ],
                            borderWidth: 2
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                position: 'bottom'
                            },
                            tooltip: {
                                callbacks: {
                                    label: function(context) {
                                        let label = context.label || '';
                                        if (label) {
                                            label += ': ';
                                        }
                                        label += context.parsed + ' dosen';
                                        return label;
                                    }
                                }
                            }
                        }
                    }
                });
            }

            // Chart: Distribusi Mahasiswa per Kelas (Top 10)
            const canvasKelas = document.getElementById('chartKelas');
            if (!canvasKelas) {
                console.error('Canvas chartKelas tidak ditemukan!');
            } else {
                const ctxKelas = canvasKelas.getContext('2d');
                const chartKelas = new Chart(ctxKelas, {
                    type: 'bar',
                    data: {
                        labels: [
                            <?php 
                            if (isset($statistik_kelas) && !empty($statistik_kelas)) {
                                $labels = array();
                                foreach ($statistik_kelas as $sk) {
                                    $labels[] = "'" . $sk->kelas . "'";
                                }
                                echo implode(', ', $labels);
                            } else {
                                echo "'Tidak ada data'";
                            }
                            ?>
                        ],
                        datasets: [{
                            label: 'Jumlah Mahasiswa',
                            data: [
                                <?php 
                                if (isset($statistik_kelas) && !empty($statistik_kelas)) {
                                    $data = array();
                                    foreach ($statistik_kelas as $sk) {
                                        $data[] = $sk->jumlah;
                                    }
                                    echo implode(', ', $data);
                                } else {
                                    echo "0";
                                }
                                ?>
                            ],
                            backgroundColor: 'rgba(75, 192, 192, 0.8)',
                            borderColor: 'rgba(75, 192, 192, 1)',
                            borderWidth: 1
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                display: false
                            },
                            tooltip: {
                                callbacks: {
                                    label: function(context) {
                                        return 'Kelas ' + context.label + ': ' + context.parsed.y + ' mahasiswa';
                                    }
                                }
                            }
                        },
                        scales: {
                            y: {
                                beginAtZero: true,
                                ticks: {
                                    stepSize: 1
                                }
                            }
                        }
                    }
                });
            }

            // Chart: Trend Mahasiswa per Angkatan (Line Chart)
            const canvasAngkatan = document.getElementById('chartAngkatan');
            if (!canvasAngkatan) {
                console.error('Canvas chartAngkatan tidak ditemukan!');
            } else {
                const ctxAngkatan = canvasAngkatan.getContext('2d');
                const chartAngkatan = new Chart(ctxAngkatan, {
                    type: 'line',
                    data: {
                        labels: [
                            <?php 
                            if (isset($statistik_angkatan) && !empty($statistik_angkatan)) {
                                $labels = array();
                                krsort($statistik_angkatan); // Sort descending
                                foreach ($statistik_angkatan as $tahun => $jumlah) {
                                    $labels[] = "'" . $tahun . "'";
                                }
                                echo implode(', ', $labels);
                            } else {
                                echo "'Tidak ada data'";
                            }
                            ?>
                        ],
                        datasets: [{
                            label: 'Jumlah Mahasiswa',
                            data: [
                                <?php 
                                if (isset($statistik_angkatan) && !empty($statistik_angkatan)) {
                                    $data = array();
                                    krsort($statistik_angkatan); // Sort descending
                                    foreach ($statistik_angkatan as $tahun => $jumlah) {
                                        $data[] = $jumlah;
                                    }
                                    echo implode(', ', $data);
                                } else {
                                    echo "0";
                                }
                                ?>
                            ],
                            backgroundColor: 'rgba(153, 102, 255, 0.2)',
                            borderColor: 'rgba(153, 102, 255, 1)',
                            borderWidth: 2,
                            fill: true,
                            tension: 0.4
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                display: false
                            },
                            tooltip: {
                                callbacks: {
                                    label: function(context) {
                                        return 'Angkatan ' + context.label + ': ' + context.parsed.y + ' mahasiswa';
                                    }
                                }
                            }
                        },
                        scales: {
                            y: {
                                beginAtZero: true,
                                ticks: {
                                    stepSize: 1
                                }
                            }
                        }
                    }
                });
            }
            
            console.log('Chart berhasil diinisialisasi!');
        } catch (error) {
            console.error('Error saat membuat chart:', error);
        }
    }
    
    // Jalankan saat DOM ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initCharts);
    } else {
        // DOM sudah ready, jalankan langsung atau tunggu sedikit
        setTimeout(initCharts, 100);
    }
})();
</script>

