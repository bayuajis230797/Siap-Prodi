<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-tachometer-alt"></i> Dashboard Dosen
                </h3>
            </div>
            <div class="card-body">
                <p class="lead mb-1">Selamat datang, <?php echo $user_data['nama']; ?>!</p>
                <?php if (!empty($tahun_aktif)): ?>
                    <p class="text-muted mb-4">
                        Tahun Akademik Aktif: <strong><?php echo $tahun_aktif->tahun_akademik . ' ' . ucfirst($tahun_aktif->semester); ?></strong>
                    </p>
                <?php else: ?>
                    <div class="alert alert-warning">
                        <i class="fas fa-exclamation-triangle"></i> Belum ada tahun akademik aktif. Statistik ditampilkan tanpa filter tahun.
                    </div>
                <?php endif; ?>

                <div class="row">
                    <div class="col-md-4">
                        <div class="small-box bg-gradient-primary">
                            <div class="inner">
                                <h3><?php echo number_format($total_sks_mengajar, 0); ?></h3>
                                <p>Total SKS Diajarkan</p>
                                <small>Berdasarkan jadwal aktif</small>
                            </div>
                            <div class="icon">
                                <i class="fas fa-book-open"></i>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="small-box bg-gradient-success">
                            <div class="inner">
                                <h3><?php echo number_format($total_mahasiswa_bimbingan, 0); ?></h3>
                                <p>Mahasiswa Bimbingan</p>
                                <small>Skripsi bimbingan aktif</small>
                            </div>
                            <div class="icon">
                                <i class="fas fa-users"></i>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="small-box bg-gradient-warning">
                            <div class="inner">
                                <h3><?php echo number_format($total_mahasiswa_uji, 0); ?></h3>
                                <p>Mahasiswa Ujian</p>
                                <small>Sebagai penguji skripsi</small>
                            </div>
                            <div class="icon">
                                <i class="fas fa-chalkboard-teacher"></i>
                            </div>
                        </div>
                    </div>
                </div>

                <hr>
                <p class="mb-2">Sebagai <strong>Dosen</strong>, Anda dapat:</p>
                <ul class="mb-0">
                    <li>Memantau bimbingan skripsi yang sedang berjalan</li>
                    <li>Mengakses jadwal ujian PKL/Skripsi yang Anda ikuti</li>
                    <li>Mengelola penilaian bimbingan dan sidang</li>
                </ul>
            </div>
        </div>

        <?php if (isset($notifikasi_list) && !empty($notifikasi_list)): ?>
            <div class="card mt-3">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-bell"></i> Notifikasi
                        <?php if (isset($notifikasi_unread_count) && $notifikasi_unread_count > 0): ?>
                            <span class="badge badge-danger ml-2"><?php echo $notifikasi_unread_count; ?> Baru</span>
                        <?php endif; ?>
                    </h3>
                </div>
                <div class="card-body p-0">
                    <ul class="list-group list-group-flush">
                        <?php foreach ($notifikasi_list as $notif): ?>
                            <li class="list-group-item <?php echo !$notif->is_read ? 'bg-light' : ''; ?>">
                                <div class="d-flex justify-content-between align-items-start">
                                    <div class="flex-grow-1">
                                        <h6 class="mb-1 <?php echo !$notif->is_read ? 'font-weight-bold' : ''; ?>">
                                            <?php if (!$notif->is_read): ?>
                                                <span class="badge badge-primary mr-2">Baru</span>
                                            <?php endif; ?>
                                            <?php echo htmlspecialchars($notif->judul); ?>
                                        </h6>
                                        <p class="mb-1 text-muted"><?php echo htmlspecialchars($notif->pesan); ?></p>
                                        <small class="text-muted">
                                            <i class="far fa-clock"></i> <?php echo date('d/m/Y H:i', strtotime($notif->created_at)); ?>
                                        </small>
                                    </div>
                                    <div class="ml-3">
                                        <?php if ($notif->link): ?>
                                            <a href="<?php echo $notif->link; ?>" class="btn btn-sm btn-primary">
                                                <i class="fas fa-eye"></i> Lihat
                                            </a>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </div>
                <?php if (isset($notifikasi_unread_count) && $notifikasi_unread_count > 0): ?>
                    <div class="card-footer">
                        <a href="<?php echo site_url('Notifikasi/mark_all_read'); ?>" class="btn btn-sm btn-secondary">
                            <i class="fas fa-check-double"></i> Tandai Semua Sudah Dibaca
                        </a>
                    </div>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </div>
</div>
