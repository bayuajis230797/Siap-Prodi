<div class="row">
    <!-- Statistik Pengawasan -->
    <div class="col-lg-3 col-md-4 col-sm-6 mb-3">
        <div class="small-box bg-info">
            <div class="inner">
                <h3><?php echo number_format($total_pengawasan ?? 0); ?></h3>
                <p>Total Pengawasan</p>
            </div>
            <div class="icon">
                <i class="fas fa-clipboard-check"></i>
            </div>
        </div>
    </div>
    
    <div class="col-lg-3 col-md-4 col-sm-6 mb-3">
        <div class="small-box bg-primary">
            <div class="inner">
                <h3><?php echo number_format($total_pengawasan_uts ?? 0); ?></h3>
                <p>Pengawasan UTS</p>
            </div>
            <div class="icon">
                <i class="fas fa-clipboard-list"></i>
            </div>
            <a href="<?php echo site_url('Jadwal_pengawas/uts'); ?>" class="small-box-footer">
                Lihat Detail <i class="fas fa-arrow-circle-right"></i>
            </a>
        </div>
    </div>
    
    <div class="col-lg-3 col-md-4 col-sm-6 mb-3">
        <div class="small-box bg-success">
            <div class="inner">
                <h3><?php echo number_format($total_pengawasan_uas ?? 0); ?></h3>
                <p>Pengawasan UAS</p>
            </div>
            <div class="icon">
                <i class="fas fa-clipboard-list"></i>
            </div>
            <a href="<?php echo site_url('Jadwal_pengawas/uas'); ?>" class="small-box-footer">
                Lihat Detail <i class="fas fa-arrow-circle-right"></i>
            </a>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-tachometer-alt"></i> Dashboard Tendik
                </h3>
            </div>
            <div class="card-body">
                <div class="alert alert-info">
                    <h5><i class="fas fa-info-circle"></i> Selamat Datang, <?php echo htmlspecialchars($user_data['nama'] ?? 'Tendik'); ?>!</h5>
                    <p>Anda dapat mengakses menu <strong>Jadwal Pengawas UTS</strong> dan <strong>Jadwal Pengawas UAS</strong> untuk mengisi berita acara dan kehadiran mahasiswa.</p>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="card card-primary">
                            <div class="card-header">
                                <h3 class="card-title">
                                    <i class="fas fa-clipboard-list"></i> Jadwal Pengawas UTS
                                </h3>
                            </div>
                            <div class="card-body">
                                <p>Lihat dan kelola jadwal UTS dimana Anda menjadi pengawas.</p>
                                <a href="<?php echo site_url('Jadwal_pengawas/uts'); ?>" class="btn btn-primary">
                                    <i class="fas fa-arrow-right"></i> Buka Jadwal Pengawas UTS
                                </a>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="card card-success">
                            <div class="card-header">
                                <h3 class="card-title">
                                    <i class="fas fa-clipboard-list"></i> Jadwal Pengawas UAS
                                </h3>
                            </div>
                            <div class="card-body">
                                <p>Lihat dan kelola jadwal UAS dimana Anda menjadi pengawas.</p>
                                <a href="<?php echo site_url('Jadwal_pengawas/uas'); ?>" class="btn btn-success">
                                    <i class="fas fa-arrow-right"></i> Buka Jadwal Pengawas UAS
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>










