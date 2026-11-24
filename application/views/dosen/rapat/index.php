<div class="row">
    <div class="col-12">
        <div class="card card-primary card-outline">
            <div class="card-header">
                <h3 class="card-title">Daftar Rapat yang Saya Ikuti</h3>
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
                                <th width="30%">Judul Rapat</th>
                                <th width="15%">Tanggal & Waktu</th>
                                <th width="15%">Tempat</th>
                                <th width="15%">Status Kehadiran Saya</th>
                                <th width="20%">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($rapat_list)): ?>
                                <tr>
                                    <td colspan="6" class="text-center">Anda belum terdaftar sebagai peserta rapat.</td>
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
                                            <?php if (isset($rapat->status_kehadiran_saya)): ?>
                                                <?php 
                                                $status_labels = array(
                                                    'hadir' => array('label' => 'Hadir', 'class' => 'success'),
                                                    'tidak_hadir' => array('label' => 'Tidak Hadir', 'class' => 'danger'),
                                                    'izin' => array('label' => 'Izin', 'class' => 'warning')
                                                );
                                                $status = $status_labels[$rapat->status_kehadiran_saya] ?? array('label' => $rapat->status_kehadiran_saya, 'class' => 'secondary');
                                                ?>
                                                <span class="badge badge-<?php echo $status['class']; ?>">
                                                    <?php echo $status['label']; ?>
                                                </span>
                                            <?php else: ?>
                                                <span class="badge badge-secondary">Belum Dikonfirmasi</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <a href="<?php echo site_url('Rapat/dosen_detail/' . $rapat->id); ?>" 
                                               class="btn btn-info btn-sm" title="Lihat Detail">
                                                <i class="fas fa-eye"></i> Detail
                                            </a>
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






