<?php
// Pastikan semua variabel terdefinisi
$mahasiswa = isset($mahasiswa) ? $mahasiswa : null;
$skripsi = isset($skripsi) ? $skripsi : null;
$pengajuan = isset($pengajuan) ? $pengajuan : null;
$jadwal_skripsi = isset($jadwal_skripsi) ? $jadwal_skripsi : null;
$tahun_aktif = isset($tahun_aktif) ? $tahun_aktif : null;
?>
<div class="row">
    <!-- Biodata Mahasiswa -->
    <div class="col-md-4">
        <div class="card card-primary card-outline">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-user"></i> Biodata Mahasiswa
                </h3>
            </div>
            <div class="card-body">
                <?php if ($mahasiswa): ?>
                    <table class="table table-sm table-borderless">
                        <tr>
                            <th width="40%">NIM</th>
                            <td><strong><?php echo htmlspecialchars($mahasiswa->nim); ?></strong></td>
                        </tr>
                        <tr>
                            <th>Nama</th>
                            <td><?php echo htmlspecialchars($mahasiswa->nama_mahasiswa); ?></td>
                        </tr>
                        <tr>
                            <th>Kelas</th>
                            <td>
                                <?php if ($mahasiswa->kelas): ?>
                                    <span class="badge badge-info"><?php echo htmlspecialchars($mahasiswa->kelas); ?></span>
                                <?php else: ?>
                                    <span class="text-muted">-</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <tr>
                            <th>Semester</th>
                            <td>
                                <?php if ($mahasiswa->semester_aktif): ?>
                                    <span class="badge badge-secondary"><?php echo $mahasiswa->semester_aktif; ?></span>
                                <?php else: ?>
                                    <span class="text-muted">-</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <tr>
                            <th>Angkatan</th>
                            <td><?php echo $mahasiswa->angkatan ?: '-'; ?></td>
                        </tr>
                        <tr>
                            <th>Status</th>
                            <td>
                                <?php
                                $status_class = 'secondary';
                                switch ($mahasiswa->status) {
                                    case 'aktif':
                                        $status_class = 'success';
                                        break;
                                    case 'cuti':
                                        $status_class = 'warning';
                                        break;
                                    case 'lulus':
                                        $status_class = 'info';
                                        break;
                                    case 'nonaktif':
                                        $status_class = 'danger';
                                        break;
                                }
                                ?>
                                <span class="badge badge-<?php echo $status_class; ?>">
                                    <?php echo ucfirst($mahasiswa->status); ?>
                                </span>
                            </td>
                        </tr>
                        <?php if ($skripsi): ?>
                        <tr>
                            <th>Judul Skripsi</th>
                            <td>
                                <?php if ($skripsi->judul_skripsi): ?>
                                    <small><?php echo htmlspecialchars($skripsi->judul_skripsi); ?></small>
                                <?php else: ?>
                                    <span class="text-muted">-</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <tr>
                            <th>Pembimbing 1</th>
                            <td>
                                <?php if ($skripsi->nama_pembimbing_1): ?>
                                    <strong><?php echo htmlspecialchars($skripsi->nama_pembimbing_1); ?></strong>
                                <?php else: ?>
                                    <span class="text-muted">Belum ditetapkan</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <tr>
                            <th>Pembimbing 2</th>
                            <td>
                                <?php if ($skripsi->nama_pembimbing_2): ?>
                                    <strong><?php echo htmlspecialchars($skripsi->nama_pembimbing_2); ?></strong>
                                <?php else: ?>
                                    <span class="text-muted">Belum ditetapkan</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endif; ?>
                    </table>
                <?php else: ?>
                    <p class="text-muted">Data mahasiswa tidak ditemukan.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <!-- Alur Skripsi -->
    <div class="col-md-8">
        <?php if ($skripsi): ?>
            <div class="card card-success card-outline">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-graduation-cap"></i> Alur Skripsi
                    </h3>
                </div>
                <div class="card-body">
                    <div class="timeline">
                        <!-- Step 1: Terdaftar -->
                        <div class="time-label">
                            <span class="badge badge-primary">1. Terdaftar</span>
                        </div>
                        <div>
                            <i class="fas fa-check-circle bg-blue"></i>
                            <div class="timeline-item">
                                <span class="time"><i class="fas fa-clock"></i> Terdaftar</span>
                                <h3 class="timeline-header">Registrasi Skripsi</h3>
                                <div class="timeline-body">
                                    Anda telah terdaftar untuk skripsi oleh Sekprodi.
                                </div>
                            </div>
                        </div>
                        
                        <!-- Step 2: Pengajuan Judul -->
                        <div class="time-label">
                            <span class="badge badge-<?php echo $pengajuan ? 'success' : 'secondary'; ?>">2. Pengajuan Judul</span>
                        </div>
                        <div>
                            <i class="fas fa-<?php echo $pengajuan ? 'check' : 'clock'; ?>-circle bg-<?php echo $pengajuan ? 'green' : 'gray'; ?>"></i>
                            <div class="timeline-item">
                                <span class="time">
                                    <?php if ($pengajuan): ?>
                                        <i class="fas fa-check"></i> <?php echo $pengajuan->status == 'disetujui' ? 'Disetujui' : ucfirst($pengajuan->status); ?>
                                    <?php else: ?>
                                        <i class="fas fa-clock"></i> Belum
                                    <?php endif; ?>
                                </span>
                                <h3 class="timeline-header">Pengajuan Judul Skripsi</h3>
                                <div class="timeline-body">
                                    <?php if ($pengajuan): ?>
                                        <p><strong>Judul:</strong> <?php echo htmlspecialchars($pengajuan->judul_pengajuan); ?></p>
                                        <p><strong>Status:</strong> 
                                            <span class="badge badge-<?php echo $pengajuan->status == 'disetujui' ? 'success' : ($pengajuan->status == 'ditolak' ? 'danger' : 'warning'); ?>">
                                                <?php echo ucfirst($pengajuan->status); ?>
                                            </span>
                                        </p>
                                    <?php else: ?>
                                        <p>Silakan ajukan judul skripsi Anda.</p>
                                        <a href="<?php echo site_url('Skripsi_pengajuan'); ?>" class="btn btn-sm btn-primary">
                                            <i class="fas fa-plus"></i> Ajukan Judul
                                        </a>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Step 3: Review Pembimbing -->
                        <?php if ($pengajuan && $pengajuan->status == 'disetujui'): ?>
                            <?php 
                            $status_p1 = isset($pengajuan->status_acc_judul_p1) ? $pengajuan->status_acc_judul_p1 : null;
                            $status_p2 = isset($pengajuan->status_acc_judul_p2) ? $pengajuan->status_acc_judul_p2 : null;
                            $both_acc = ($status_p1 == 'acc' && $status_p2 == 'acc');
                            ?>
                            <div class="time-label">
                                <span class="badge badge-<?php echo $both_acc ? 'success' : 'warning'; ?>">
                                    3. Review Pembimbing
                                </span>
                            </div>
                            <div>
                                <i class="fas fa-<?php echo $both_acc ? 'check' : 'clock'; ?>-circle bg-<?php echo $both_acc ? 'green' : 'yellow'; ?>"></i>
                                <div class="timeline-item">
                                    <span class="time">
                                        <?php if ($both_acc): ?>
                                            <i class="fas fa-check"></i> ACC
                                        <?php else: ?>
                                            <i class="fas fa-clock"></i> Menunggu
                                        <?php endif; ?>
                                    </span>
                                    <h3 class="timeline-header">Review Judul oleh Pembimbing</h3>
                                    <div class="timeline-body">
                                        <p><strong>Pembimbing 1:</strong> 
                                            <?php if ($status_p1 == 'acc'): ?>
                                                <span class="badge badge-success">ACC</span>
                                            <?php elseif ($status_p1 == 'revisi'): ?>
                                                <span class="badge badge-warning">Revisi</span>
                                            <?php else: ?>
                                                <span class="badge badge-secondary">Menunggu</span>
                                            <?php endif; ?>
                                        </p>
                                        <p><strong>Pembimbing 2:</strong> 
                                            <?php if ($status_p2 == 'acc'): ?>
                                                <span class="badge badge-success">ACC</span>
                                            <?php elseif ($status_p2 == 'revisi'): ?>
                                                <span class="badge badge-warning">Revisi</span>
                                            <?php else: ?>
                                                <span class="badge badge-secondary">Menunggu</span>
                                            <?php endif; ?>
                                        </p>
                                    </div>
                                </div>
                            </div>
                        <?php endif; ?>
                        
                        <!-- Step 4: Bimbingan -->
                        <?php 
                        $status_p1 = isset($pengajuan->status_acc_judul_p1) ? $pengajuan->status_acc_judul_p1 : null;
                        $status_p2 = isset($pengajuan->status_acc_judul_p2) ? $pengajuan->status_acc_judul_p2 : null;
                        if ($pengajuan && $pengajuan->status == 'disetujui' && $status_p1 == 'acc' && $status_p2 == 'acc'): 
                        ?>
                            <div class="time-label">
                                <span class="badge badge-<?php echo ($skripsi->status == 'layak_sidang') ? 'success' : 'info'; ?>">4. Bimbingan</span>
                            </div>
                            <div>
                                <i class="fas fa-<?php echo ($skripsi->status == 'layak_sidang') ? 'check' : 'clock'; ?>-circle bg-<?php echo ($skripsi->status == 'layak_sidang') ? 'green' : 'blue'; ?>"></i>
                                <div class="timeline-item">
                                    <span class="time">
                                        <?php if ($skripsi->status == 'layak_sidang'): ?>
                                            <i class="fas fa-check"></i> Layak Sidang
                                        <?php else: ?>
                                            <i class="fas fa-clock"></i> Sedang Bimbingan
                                        <?php endif; ?>
                                    </span>
                                    <h3 class="timeline-header">Bimbingan Skripsi</h3>
                                    <div class="timeline-body">
                                        <p><strong>Status:</strong> 
                                            <span class="badge badge-<?php echo $skripsi->status == 'layak_sidang' ? 'success' : 'info'; ?>">
                                                <?php echo ucfirst(str_replace('_', ' ', $skripsi->status)); ?>
                                            </span>
                                        </p>
                                        <?php if ($skripsi->acc_pembimbing_1 && $skripsi->acc_pembimbing_2): ?>
                                            <p class="text-success"><i class="fas fa-check-circle"></i> Kedua pembimbing sudah ACC layak sidang</p>
                                        <?php else: ?>
                                            <p class="text-warning"><i class="fas fa-clock"></i> Menunggu ACC layak sidang dari pembimbing</p>
                                        <?php endif; ?>
                                        <a href="<?php echo site_url('Bimbingan_skripsi_mahasiswa'); ?>" class="btn btn-sm btn-info">
                                            <i class="fas fa-comments"></i> Lihat Bimbingan
                                        </a>
                                    </div>
                                </div>
                            </div>
                        <?php endif; ?>
                        
                        <!-- Step 5: Pendaftaran Sidang -->
                        <?php if ($skripsi && $skripsi->status == 'layak_sidang'): ?>
                            <div class="time-label">
                                <span class="badge badge-info">5. Pendaftaran Sidang</span>
                            </div>
                            <div>
                                <i class="fas fa-clock-circle bg-blue"></i>
                                <div class="timeline-item">
                                    <span class="time"><i class="fas fa-clock"></i> Menunggu</span>
                                    <h3 class="timeline-header">Pendaftaran Sidang</h3>
                                    <div class="timeline-body">
                                        <p>Silakan daftarkan diri untuk sidang skripsi.</p>
                                        <a href="<?php echo site_url('Pendaftaran_sidang_mahasiswa'); ?>" class="btn btn-sm btn-primary">
                                            <i class="fas fa-paper-plane"></i> Daftar Sidang
                                        </a>
                                    </div>
                                </div>
                            </div>
                        <?php endif; ?>
                        
                        <!-- Step 6: Jadwal Sidang -->
                        <?php if ($jadwal_skripsi): ?>
                            <div class="time-label">
                                <span class="badge badge-success">6. Sidang</span>
                            </div>
                            <div>
                                <i class="fas fa-check-circle bg-green"></i>
                                <div class="timeline-item">
                                    <span class="time">
                                        <i class="fas fa-calendar"></i> <?php echo date('d/m/Y', strtotime($jadwal_skripsi->tanggal_ujian)); ?>
                                    </span>
                                    <h3 class="timeline-header">Jadwal Sidang</h3>
                                    <div class="timeline-body">
                                        <p><strong>Tanggal:</strong> <?php echo date('d F Y', strtotime($jadwal_skripsi->tanggal_ujian)); ?></p>
                                        <p><strong>Jam:</strong> <?php echo date('H:i', strtotime($jadwal_skripsi->jam_mulai)) . ' - ' . date('H:i', strtotime($jadwal_skripsi->jam_selesai)); ?></p>
                                        <p><strong>Ruangan:</strong> <?php echo htmlspecialchars($jadwal_skripsi->ruangan ?: '-'); ?></p>
                                        <a href="<?php echo site_url('Jadwal_skripsi_mahasiswa'); ?>" class="btn btn-sm btn-success">
                                            <i class="fas fa-eye"></i> Lihat Detail
                                        </a>
                                    </div>
                                </div>
                            </div>
                        <?php endif; ?>
                        
                        <div>
                            <i class="fas fa-clock bg-gray"></i>
                        </div>
                    </div>
                </div>
            </div>
        <?php else: ?>
            <div class="card card-outline card-secondary">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-info-circle"></i> Informasi
                    </h3>
                </div>
                <div class="card-body">
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle"></i>
                        <strong>Anda belum terdaftar untuk skripsi.</strong><br>
                        Silakan hubungi Sekprodi untuk registrasi skripsi.
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Struktur Kurikulum -->
<?php if (isset($kurikulum_info) && $kurikulum_info && !empty($struktur_kurikulum)): ?>
<div class="row mt-4">
    <div class="col-12">
        <div class="card card-outline card-primary">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-book"></i> Struktur Kurikulum
                    <span class="badge badge-<?php echo $kurikulum_info->is_aktif ? 'success' : 'secondary'; ?> ml-2">
                        <?php echo $kurikulum_info->is_aktif ? 'Aktif' : 'Tidak Aktif'; ?>
                    </span>
                </h3>
                <div class="card-tools">
                    <small class="text-muted">
                        <strong><?php echo htmlspecialchars($kurikulum_info->nama_kurikulum); ?></strong>
                        (Tahun <?php echo $kurikulum_info->tahun_berlaku; ?>)
                    </small>
                </div>
            </div>
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

<!-- Menu Navigasi Cepat -->
<div class="row mt-4">
    <div class="col-12">
        <div class="card card-outline card-info">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-bars"></i> Menu Navigasi
                </h3>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-3 col-sm-6 mb-3">
                        <a href="<?php echo site_url('Jadwal'); ?>" class="btn btn-block btn-primary btn-lg">
                            <i class="fas fa-calendar-alt"></i><br>
                            <span>Jadwal</span>
                        </a>
                    </div>
                    <?php if ($skripsi): ?>
                    <div class="col-md-3 col-sm-6 mb-3">
                        <a href="<?php echo site_url('Skripsi_pengajuan'); ?>" class="btn btn-block btn-success btn-lg">
                            <i class="fas fa-file-alt"></i><br>
                            <span>Pengajuan Judul</span>
                        </a>
                    </div>
                    <div class="col-md-3 col-sm-6 mb-3">
                        <a href="<?php echo site_url('Bimbingan_skripsi_mahasiswa'); ?>" class="btn btn-block btn-warning btn-lg">
                            <i class="fas fa-comments"></i><br>
                            <span>Bimbingan Skripsi</span>
                        </a>
                    </div>
                    <?php endif; ?>
                    <div class="col-md-3 col-sm-6 mb-3">
                        <a href="<?php echo site_url('Manajemen_berkas'); ?>" class="btn btn-block btn-info btn-lg">
                            <i class="fas fa-folder"></i><br>
                            <span>Manajemen Berkas</span>
                        </a>
                    </div>
                    <div class="col-md-3 col-sm-6 mb-3">
                        <a href="<?php echo site_url('Permintaan_surat'); ?>" class="btn btn-block btn-secondary btn-lg">
                            <i class="fas fa-file-alt"></i><br>
                            <span>Permintaan Surat</span>
                        </a>
                    </div>
                    <div class="col-md-3 col-sm-6 mb-3">
                        <a href="<?php echo site_url('Profile'); ?>" class="btn btn-block btn-dark btn-lg">
                            <i class="fas fa-user"></i><br>
                            <span>Profile</span>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.timeline {
    position: relative;
    padding: 0;
    list-style: none;
}
.timeline:before {
    content: '';
    position: absolute;
    top: 0;
    bottom: 0;
    left: 40px;
    width: 2px;
    margin-left: -1.5px;
    background-color: #dee2e6;
}
.timeline > div {
    position: relative;
    margin-bottom: 20px;
}
.timeline > div:before,
.timeline > div:after {
    content: "";
    display: table;
}
.timeline > div:after {
    clear: both;
}
.timeline > div > .timeline-item {
    position: relative;
    background: #fff;
    border-radius: 0.25rem;
    padding: 10px 20px;
    margin-left: 60px;
    margin-right: 15px;
    box-shadow: 0 0 1px rgba(0,0,0,.125), 0 1px 3px rgba(0,0,0,.2);
}
.timeline > div > i {
    position: absolute;
    left: 15px;
    top: 0;
    width: 50px;
    height: 50px;
    border-radius: 50%;
    text-align: center;
    line-height: 50px;
    color: #fff;
    font-size: 1.2em;
}
.timeline .time-label {
    position: relative;
    padding: 10px 0;
    margin-left: 60px;
}
.timeline .time-label > span {
    padding: 5px 10px;
    font-size: 0.9em;
}
</style>

