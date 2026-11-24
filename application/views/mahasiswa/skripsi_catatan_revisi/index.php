<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-comments"></i> Catatan Revisi Skripsi
                </h3>
            </div>
            <div class="card-body">
                <?php if ($this->session->flashdata('error')): ?>
                    <div class="alert alert-danger alert-dismissible fade show">
                        <i class="fas fa-exclamation-circle"></i> <?php echo $this->session->flashdata('error'); ?>
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                <?php endif; ?>

                <?php if ($tahun_aktif): ?>
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle"></i>
                        <strong>Tahun Akademik:</strong> <?php echo $tahun_aktif->tahun_akademik . ' ' . ucfirst($tahun_aktif->semester); ?>
                    </div>
                <?php endif; ?>

                <?php if (empty($jadwal_list)): ?>
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle"></i>
                        Belum ada jadwal sidang Skripsi yang tersedia.
                    </div>
                <?php else: ?>
                    <?php foreach ($jadwal_list as $jadwal): ?>
                        <?php 
                        $nilai_sidang_list = isset($catatan_revisi[$jadwal->id]['nilai_sidang_list']) 
                            ? $catatan_revisi[$jadwal->id]['nilai_sidang_list'] 
                            : array();
                        
                        // Cek apakah ada catatan revisi pasca sidang
                        $ada_catatan = false;
                        foreach ($nilai_sidang_list as $nilai_sidang) {
                            if (!empty($nilai_sidang->catatan_revisi)) {
                                $ada_catatan = true;
                                break;
                            }
                        }
                        ?>
                        <div class="card card-outline card-primary mb-3">
                            <div class="card-header">
                                <h5 class="card-title mb-0">
                                    <i class="fas fa-calendar-check"></i> 
                                    Jadwal Sidang Skripsi - <?php echo date('d/m/Y', strtotime($jadwal->tanggal_ujian)); ?>
                                </h5>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-6">
                                        <table class="table table-borderless table-sm">
                                            <tr>
                                                <th width="40%">Tanggal:</th>
                                                <td>
                                                    <strong><?php echo date('d/m/Y', strtotime($jadwal->tanggal_ujian)); ?></strong>
                                                    <br>
                                                    <small class="text-muted">
                                                        <?php 
                                                        $hari_inggris = date('l', strtotime($jadwal->tanggal_ujian));
                                                        $hari_indonesia = array(
                                                            'Monday' => 'Senin',
                                                            'Tuesday' => 'Selasa',
                                                            'Wednesday' => 'Rabu',
                                                            'Thursday' => 'Kamis',
                                                            'Friday' => 'Jumat',
                                                            'Saturday' => 'Sabtu',
                                                            'Sunday' => 'Minggu'
                                                        );
                                                        echo isset($hari_indonesia[$hari_inggris]) ? $hari_indonesia[$hari_inggris] : $hari_inggris;
                                                        ?>
                                                    </small>
                                                </td>
                                            </tr>
                                            <tr>
                                                <th>Jam:</th>
                                                <td><strong><?php echo isset($jadwal->jam_mulai) ? date('H:i', strtotime($jadwal->jam_mulai)) : '-'; ?> - <?php echo isset($jadwal->jam_selesai) ? date('H:i', strtotime($jadwal->jam_selesai)) : '-'; ?></strong></td>
                                            </tr>
                                            <tr>
                                                <th>Ruangan:</th>
                                                <td><strong><?php echo isset($jadwal->ruangan) ? $jadwal->ruangan : '-'; ?></strong></td>
                                            </tr>
                                            <tr>
                                                <th>Status:</th>
                                                <td>
                                                    <?php
                                                    $status_class = 'secondary';
                                                    $status_text = ucfirst($jadwal->status ?? 'terjadwal');
                                                    switch ($jadwal->status ?? 'terjadwal') {
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
                                        <table class="table table-borderless table-sm">
                                            <tr>
                                                <th width="40%">Ketua Penguji:</th>
                                                <td>
                                                    <strong><?php echo isset($jadwal->ketua_penguji) ? $jadwal->ketua_penguji : '-'; ?></strong>
                                                </td>
                                            </tr>
                                            <tr>
                                                <th>Penguji 1:</th>
                                                <td>
                                                    <strong><?php echo isset($jadwal->anggota_penguji_1) ? $jadwal->anggota_penguji_1 : '-'; ?></strong>
                                                </td>
                                            </tr>
                                            <tr>
                                                <th>Penguji 2:</th>
                                                <td>
                                                    <strong><?php echo isset($jadwal->anggota_penguji_2) ? $jadwal->anggota_penguji_2 : '-'; ?></strong>
                                                </td>
                                            </tr>
                                        </table>
                                    </div>
                                </div>

                                <?php if ($ada_catatan): ?>
                                    <div class="mt-3">
                                        <h6 class="font-weight-bold mb-2">
                                            <i class="fas fa-comments text-warning"></i> Catatan Revisi Pasca Sidang dari Penguji:
                                        </h6>
                                        <?php foreach ($nilai_sidang_list as $nilai_sidang): ?>
                                            <?php if (!empty($nilai_sidang->catatan_revisi)): ?>
                                                <div class="card card-outline card-warning mb-2">
                                                    <div class="card-header py-2">
                                                        <h6 class="mb-0">
                                                            <i class="fas fa-user-tie"></i> 
                                                            <?php
                                                            $tipe_label = '';
                                                            $peran = isset($nilai_sidang->peran) ? $nilai_sidang->peran : '';
                                                            switch ($peran) {
                                                                case 'ketua':
                                                                    $tipe_label = 'Ketua Penguji';
                                                                    break;
                                                                case 'anggota_1':
                                                                    $tipe_label = 'Anggota Penguji 1';
                                                                    break;
                                                                case 'anggota_2':
                                                                    $tipe_label = 'Anggota Penguji 2';
                                                                    break;
                                                                default:
                                                                    $tipe_label = 'Penguji';
                                                            }
                                                            ?>
                                                            <?php echo $tipe_label; ?>: <?php echo isset($nilai_sidang->penguji_nama) ? htmlspecialchars($nilai_sidang->penguji_nama) : 'Tidak diketahui'; ?>
                                                        </h6>
                                                    </div>
                                                    <div class="card-body py-2">
                                                        <p class="mb-0"><?php echo nl2br(htmlspecialchars($nilai_sidang->catatan_revisi)); ?></p>
                                                        <?php if (isset($nilai_sidang->created_at) && $nilai_sidang->created_at): ?>
                                                            <small class="text-muted">
                                                                <i class="fas fa-calendar"></i> 
                                                                Tanggal: <?php echo date('d/m/Y H:i', strtotime($nilai_sidang->created_at)); ?>
                                                            </small>
                                                        <?php endif; ?>
                                                    </div>
                                                </div>
                                            <?php endif; ?>
                                        <?php endforeach; ?>
                                    </div>
                                <?php else: ?>
                                    <div class="alert alert-info mt-3">
                                        <i class="fas fa-info-circle"></i> Belum ada catatan revisi pasca sidang dari dosen penguji.
                                    </div>
                                <?php endif; ?>

                                <div class="mt-3">
                                    <a href="<?php echo site_url('Skripsi_catatan_revisi/detail/' . $jadwal->id); ?>" class="btn btn-sm btn-primary">
                                        <i class="fas fa-eye"></i> Lihat Detail
                                    </a>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>


