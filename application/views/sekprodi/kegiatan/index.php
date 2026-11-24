<div class="row">
    <div class="col-12">
        <div class="card card-primary card-outline">
            <div class="card-header">
                <h3 class="card-title">Daftar Kegiatan</h3>
                <div class="card-tools">
                    <a href="<?php echo site_url('Kegiatan/create'); ?>" class="btn btn-primary btn-sm">
                        <i class="fas fa-plus"></i> Tambah Kegiatan Baru
                    </a>
                </div>
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

                <!-- Table -->
                <div class="table-responsive">
                    <table class="table table-bordered table-striped table-hover">
                        <thead>
                            <tr>
                                <th width="5%">No</th>
                                <th width="25%">Nama Kegiatan</th>
                                <th width="15%">Tanggal & Waktu</th>
                                <th width="15%">Lokasi</th>
                                <th width="15%">Jenis</th>
                                <th width="15%">Status</th>
                                <th width="10%">Absensi</th>
                                <th width="20%">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($kegiatan_list)): ?>
                                <tr>
                                    <td colspan="8" class="text-center">Tidak ada data kegiatan.</td>
                                </tr>
                            <?php else: ?>
                                <?php $no = 1; ?>
                                <?php foreach ($kegiatan_list as $kegiatan): ?>
                                    <tr>
                                        <td><?php echo $no++; ?></td>
                                        <td>
                                            <strong><?php echo htmlspecialchars($kegiatan->nama_kegiatan); ?></strong>
                                        </td>
                                        <td>
                                            <i class="fas fa-calendar"></i> <?php echo date('d/m/Y', strtotime($kegiatan->tanggal_kegiatan)); ?><br>
                                            <i class="fas fa-clock"></i> <?php echo date('H:i', strtotime($kegiatan->jam_mulai)); ?> WIB
                                            <?php if ($kegiatan->jam_selesai): ?>
                                                - <?php echo date('H:i', strtotime($kegiatan->jam_selesai)); ?> WIB
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php echo $kegiatan->lokasi ? htmlspecialchars($kegiatan->lokasi) : '-'; ?>
                                        </td>
                                        <td>
                                            <span class="badge badge-info">
                                                <?php echo $kegiatan->jenis_kegiatan == 'akademik' ? 'Akademik' : 'Non-Akademik'; ?>
                                            </span>
                                        </td>
                                        <td>
                                            <?php 
                                            $status_labels = array(
                                                'draft' => array('label' => 'Draft', 'class' => 'secondary'),
                                                'aktif' => array('label' => 'Aktif', 'class' => 'success'),
                                                'selesai' => array('label' => 'Selesai', 'class' => 'info'),
                                                'dibatalkan' => array('label' => 'Dibatalkan', 'class' => 'danger')
                                            );
                                            $status = $status_labels[$kegiatan->status] ?? array('label' => $kegiatan->status, 'class' => 'secondary');
                                            ?>
                                            <span class="badge badge-<?php echo $status['class']; ?>">
                                                <?php echo $status['label']; ?>
                                            </span>
                                        </td>
                                        <td>
                                            <?php if (isset($kegiatan->statistik)): ?>
                                                <span class="badge badge-success">Hadir: <?php echo $kegiatan->statistik->hadir; ?></span><br>
                                                <span class="badge badge-warning">Terlambat: <?php echo $kegiatan->statistik->terlambat; ?></span><br>
                                                <span class="badge badge-danger">Tidak Hadir: <?php echo $kegiatan->statistik->tidak_hadir; ?></span>
                                            <?php else: ?>
                                                <span class="text-muted">-</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <div class="btn-group btn-group-sm">
                                                <a href="<?php echo site_url('Kegiatan/detail/' . $kegiatan->id); ?>" 
                                                   class="btn btn-info" title="Detail">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                                <a href="<?php echo site_url('Kegiatan/laporan/' . $kegiatan->id); ?>" 
                                                   class="btn btn-success" title="Laporan">
                                                    <i class="fas fa-file-alt"></i>
                                                </a>
                                                <a href="<?php echo site_url('Kegiatan/edit/' . $kegiatan->id); ?>" 
                                                   class="btn btn-warning" title="Edit">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                <a href="<?php echo site_url('Kegiatan/delete/' . $kegiatan->id); ?>" 
                                                   class="btn btn-danger" title="Hapus"
                                                   onclick="return confirm('Apakah Anda yakin ingin menghapus kegiatan ini? Semua data peserta dan absensi akan ikut terhapus.');">
                                                    <i class="fas fa-trash"></i>
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <?php if (!empty($pagination)): ?>
                    <div class="mt-3">
                        <?php echo $pagination; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

