<!-- Dashboard Sekprodi -->
<?php 
// Pastikan variabel selalu ada
$total_mahasiswa_aktif = isset($total_mahasiswa_aktif) ? $total_mahasiswa_aktif : 0;
$statistik_kelas = isset($statistik_kelas) ? $statistik_kelas : array();
$statistik_angkatan = isset($statistik_angkatan) ? $statistik_angkatan : array();
?>
<div class="row">
    <!-- Statistik Total -->
    <div class="col-lg-3 col-6">
        <div class="small-box bg-info">
            <div class="inner">
                <h3><?php echo number_format($total_mahasiswa_aktif); ?></h3>
                <p>Total Mahasiswa Aktif</p>
            </div>
            <div class="icon">
                <i class="fas fa-users"></i>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <!-- Statistik Mahasiswa per Kelas -->
    <div class="col-md-6">
        <div class="card">
            <div class="card-header border-0">
                <h3 class="card-title">
                    <i class="fas fa-chart-bar mr-1"></i>
                    Statistik Mahasiswa per Kelas
                </h3>
            </div>
            <div class="card-body">
                <div class="chart-container" style="position: relative; height: 400px;">
                    <canvas id="chartKelas"></canvas>
                </div>
                <?php if (!empty($statistik_kelas) && is_array($statistik_kelas)): ?>
                    <div class="table-responsive mt-3">
                        <table class="table table-sm table-bordered">
                            <thead>
                                <tr>
                                    <th>Kelas</th>
                                    <th class="text-center">Jumlah</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($statistik_kelas as $stat): ?>
                                    <tr>
                                        <td><strong><?php echo $stat->kelas ? $stat->kelas : '-'; ?></strong></td>
                                        <td class="text-center">
                                            <span class="badge badge-primary"><?php echo $stat->jumlah; ?></span>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <p class="text-muted text-center mt-3">Belum ada data mahasiswa per kelas.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Statistik Mahasiswa per Angkatan -->
    <div class="col-md-6">
        <div class="card">
            <div class="card-header border-0">
                <h3 class="card-title">
                    <i class="fas fa-chart-bar mr-1"></i>
                    Statistik Mahasiswa per Angkatan
                </h3>
            </div>
            <div class="card-body">
                <div class="chart-container" style="position: relative; height: 300px;">
                    <canvas id="chartAngkatan"></canvas>
                </div>
                <?php if (!empty($statistik_angkatan) && is_array($statistik_angkatan)): ?>
                    <div class="table-responsive mt-3">
                        <table class="table table-sm table-bordered">
                            <thead>
                                <tr>
                                    <th>Angkatan</th>
                                    <th class="text-center">Jumlah</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($statistik_angkatan as $stat): ?>
                                    <tr>
                                        <td><strong><?php echo $stat->angkatan; ?></strong></td>
                                        <td class="text-center">
                                            <span class="badge badge-success"><?php echo $stat->jumlah; ?></span>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <p class="text-muted text-center mt-3">Belum ada data mahasiswa per angkatan.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    try {
        // Cek apakah Chart.js sudah ter-load
        if (typeof Chart === 'undefined') {
            console.error('Chart.js tidak ter-load!');
            // Tampilkan pesan error di halaman
            $('#chartKelas').closest('.card-body').append('<div class="alert alert-warning">Chart.js tidak ter-load. Silakan refresh halaman.</div>');
            return;
        }
        
        // Chart Mahasiswa per Kelas
        const canvasKelas = document.getElementById('chartKelas');
        if (canvasKelas) {
        const ctxKelas = canvasKelas.getContext('2d');
        const statistikKelas = <?php echo json_encode(isset($statistik_kelas) ? $statistik_kelas : array()); ?>;
        
        const labelsKelas = statistikKelas.length > 0 
            ? statistikKelas.map(function(s) { return s.kelas ? s.kelas : '-'; })
            : ['Belum ada data'];
        const dataKelas = statistikKelas.length > 0
            ? statistikKelas.map(function(s) { return parseInt(s.jumlah); })
            : [0];
        
        // Plugin custom untuk menampilkan label di atas bar
        const labelPlugin = {
            id: 'labelPlugin',
            afterDraw: function(chart) {
                const ctx = chart.ctx;
                ctx.save();
                ctx.font = 'bold 13px Arial';
                ctx.fillStyle = '#333';
                ctx.textAlign = 'center';
                ctx.textBaseline = 'bottom';
                
                chart.data.datasets.forEach((dataset, i) => {
                    const meta = chart.getDatasetMeta(i);
                    meta.data.forEach((bar, index) => {
                        const data = dataset.data[index];
                        if (data > 0) {
                            ctx.fillText(data, bar.x, bar.y - 8);
                        }
                    });
                });
                ctx.restore();
            }
        };
        
        const chartKelasInstance = new Chart(ctxKelas, {
            type: 'bar',
            plugins: [labelPlugin],
            data: {
                labels: labelsKelas,
                datasets: [{
                    label: 'Jumlah Mahasiswa',
                    data: dataKelas,
                    backgroundColor: [
                        'rgba(54, 162, 235, 0.8)',
                        'rgba(255, 99, 132, 0.8)',
                        'rgba(255, 206, 86, 0.8)',
                        'rgba(75, 192, 192, 0.8)',
                        'rgba(153, 102, 255, 0.8)',
                        'rgba(255, 159, 64, 0.8)',
                        'rgba(199, 199, 199, 0.8)',
                        'rgba(83, 102, 255, 0.8)',
                        'rgba(255, 99, 255, 0.8)',
                        'rgba(99, 255, 132, 0.8)'
                    ],
                    borderColor: [
                        'rgba(54, 162, 235, 1)',
                        'rgba(255, 99, 132, 1)',
                        'rgba(255, 206, 86, 1)',
                        'rgba(75, 192, 192, 1)',
                        'rgba(153, 102, 255, 1)',
                        'rgba(255, 159, 64, 1)',
                        'rgba(199, 199, 199, 1)',
                        'rgba(83, 102, 255, 1)',
                        'rgba(255, 99, 255, 1)',
                        'rgba(99, 255, 132, 1)'
                    ],
                    borderWidth: 2
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
                        }
                    }
                },
                animation: {
                    duration: 1000
                }
            }
        });
    }

    // Chart Mahasiswa per Angkatan
    const canvasAngkatan = document.getElementById('chartAngkatan');
    if (canvasAngkatan) {
        const ctxAngkatan = canvasAngkatan.getContext('2d');
        const statistikAngkatan = <?php echo json_encode(isset($statistik_angkatan) ? $statistik_angkatan : array()); ?>;
        
        const labelsAngkatan = statistikAngkatan.length > 0
            ? statistikAngkatan.map(function(s) { return s.angkatan.toString(); })
            : ['Belum ada data'];
        const dataAngkatan = statistikAngkatan.length > 0
            ? statistikAngkatan.map(function(s) { return parseInt(s.jumlah); })
            : [0];
        
        new Chart(ctxAngkatan, {
            type: 'bar',
            data: {
                labels: labelsAngkatan,
                datasets: [{
                    label: 'Jumlah Mahasiswa',
                    data: dataAngkatan,
                    backgroundColor: [
                        'rgba(75, 192, 192, 0.8)',
                        'rgba(54, 162, 235, 0.8)',
                        'rgba(255, 206, 86, 0.8)',
                        'rgba(255, 99, 132, 0.8)',
                        'rgba(153, 102, 255, 0.8)',
                        'rgba(255, 159, 64, 0.8)',
                        'rgba(199, 199, 199, 0.8)',
                        'rgba(83, 102, 255, 0.8)',
                        'rgba(255, 99, 255, 0.8)',
                        'rgba(99, 255, 132, 0.8)'
                    ],
                    borderColor: [
                        'rgba(75, 192, 192, 1)',
                        'rgba(54, 162, 235, 1)',
                        'rgba(255, 206, 86, 1)',
                        'rgba(255, 99, 132, 1)',
                        'rgba(153, 102, 255, 1)',
                        'rgba(255, 159, 64, 1)',
                        'rgba(199, 199, 199, 1)',
                        'rgba(83, 102, 255, 1)',
                        'rgba(255, 99, 255, 1)',
                        'rgba(99, 255, 132, 1)'
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
    } catch (error) {
        console.error('Error loading charts:', error);
        // Tampilkan pesan error
        if ($('#chartKelas').length) {
            $('#chartKelas').closest('.card-body').append('<div class="alert alert-danger">Error loading chart: ' + error.message + '</div>');
        }
    }
});
</script>
