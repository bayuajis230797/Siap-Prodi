<div class="row">
    <div class="col-12">
        <div class="card card-primary card-outline">
            <div class="card-header">
                <h3 class="card-title">Daftar Rapat</h3>
                <div class="card-tools">
                    <a href="<?php echo site_url('Rapat/create'); ?>" class="btn btn-primary btn-sm">
                        <i class="fas fa-plus"></i> Tambah Rapat Baru
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
                                <th width="25%">Judul Rapat</th>
                                <th width="15%">Tanggal & Waktu</th>
                                <th width="15%">Tempat</th>
                                <th width="15%">Kehadiran</th>
                                <th width="25%">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($rapat_list)): ?>
                                <tr>
                                    <td colspan="6" class="text-center">Tidak ada data rapat.</td>
                                </tr>
                            <?php else: ?>
                                <?php $no = 1; ?>
                                <?php foreach ($rapat_list as $rapat): ?>
                                    <tr>
                                        <td><?php echo $no++; ?></td>
                                        <td>
                                            <strong><?php echo htmlspecialchars($rapat->judul_rapat); ?></strong>
                                        </td>
                                        <td>
                                            <i class="fas fa-calendar"></i> <?php echo date('d/m/Y', strtotime($rapat->tanggal_rapat)); ?><br>
                                            <i class="fas fa-clock"></i> <?php echo date('H:i', strtotime($rapat->jam_mulai)); ?>
                                            <?php if ($rapat->jam_selesai): ?>
                                                - <?php echo date('H:i', strtotime($rapat->jam_selesai)); ?>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php echo $rapat->tempat ? htmlspecialchars($rapat->tempat) : '-'; ?>
                                        </td>
                                        <td>
                                            <?php if (isset($rapat->statistik)): ?>
                                                <span class="badge badge-success">Hadir: <?php echo $rapat->statistik['hadir']; ?></span>
                                                <span class="badge badge-danger">Tidak Hadir: <?php echo $rapat->statistik['tidak_hadir']; ?></span>
                                                <?php if ($rapat->statistik['izin'] > 0): ?>
                                                    <span class="badge badge-warning">Izin: <?php echo $rapat->statistik['izin']; ?></span>
                                                <?php endif; ?>
                                            <?php else: ?>
                                                <span class="text-muted">-</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <div class="btn-group btn-group-sm">
                                                <a href="<?php echo site_url('Rapat/detail/' . $rapat->id); ?>" 
                                                   class="btn btn-info" title="Detail">
                                                    <i class="fas fa-eye"></i> Detail
                                                </a>
                                                <a href="<?php echo site_url('Rapat/cetak_daftar_hadir/' . $rapat->id); ?>" 
                                                   class="btn btn-success" title="Cetak Daftar Hadir" target="_blank">
                                                    <i class="fas fa-print"></i> Cetak
                                                </a>
                                                <a href="<?php echo site_url('Rapat/edit/' . $rapat->id); ?>" 
                                                   class="btn btn-warning" title="Edit">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                <a href="<?php echo site_url('Rapat/delete/' . $rapat->id); ?>" 
                                                   class="btn btn-danger" title="Hapus"
                                                   onclick="return confirm('Apakah Anda yakin ingin menghapus rapat ini? Semua data peserta dan dokumentasi akan ikut terhapus.');">
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


