<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Jadwal Sidang PKL</h3>
                <div class="card-tools">
                    <a href="<?php echo site_url('Jadwal_pkl_sekprodi/export_excel'); ?>" class="btn btn-success btn-sm mr-2">
                        <i class="fas fa-file-excel"></i> Export Excel
                    </a>
                    <?php if (get_user_role() !== 'kaprodi'): ?>
                    <a href="<?php echo site_url('Jadwal_pkl_sekprodi/create'); ?>" class="btn btn-primary btn-sm">
                        <i class="fas fa-plus"></i> Tambah Jadwal
                    </a>
                    <?php endif; ?>
                </div>
            </div>
            <div class="card-body">
                <?php if ($this->session->flashdata('success')): ?>
                    <div class="alert alert-success alert-dismissible fade show">
                        <i class="fas fa-check-circle"></i> <?php echo $this->session->flashdata('success'); ?>
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                <?php endif; ?>

                <?php if ($this->session->flashdata('error')): ?>
                    <div class="alert alert-danger alert-dismissible fade show">
                        <i class="fas fa-exclamation-circle"></i> <?php echo $this->session->flashdata('error'); ?>
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                <?php endif; ?>

                <!-- Filter -->
                <div class="row mb-3">
                    <div class="col-md-6">
                        <form method="get" action="<?php echo site_url('Jadwal_pkl_sekprodi/index'); ?>">
                            <div class="input-group">
                                <select name="status" class="form-control">
                                    <option value="">-- Semua Status --</option>
                                    <option value="terjadwal" <?php echo ($status_filter == 'terjadwal') ? 'selected' : ''; ?>>Terjadwal</option>
                                    <option value="selesai" <?php echo ($status_filter == 'selesai') ? 'selected' : ''; ?>>Selesai</option>
                                    <option value="batal" <?php echo ($status_filter == 'batal') ? 'selected' : ''; ?>>Batal</option>
                                </select>
                                <div class="input-group-append">
                                    <button type="submit" class="btn btn-info">
                                        <i class="fas fa-filter"></i> Filter
                                    </button>
                                    <?php if ($status_filter): ?>
                                        <a href="<?php echo site_url('Jadwal_pkl_sekprodi'); ?>" class="btn btn-secondary">
                                            <i class="fas fa-times"></i> Reset
                                        </a>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
                
                <div class="table-responsive">
                    <table class="table table-bordered table-striped table-hover">
                        <thead>
                            <tr>
                                <th width="5%">No</th>
                                <th width="12%">NIM</th>
                                <th>Nama Mahasiswa</th>
                                <th width="8%" class="text-center">Kelas</th>
                                <th>Pembimbing</th>
                                <th>Penguji</th>
                                <th width="10%" class="text-center">Tanggal</th>
                                <th width="8%" class="text-center">Hari</th>
                                <th width="10%" class="text-center">Jam</th>
                                <th>Ruangan</th>
                                <th width="10%" class="text-center">Status</th>
                                <th width="12%" class="text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($jadwal_list)): ?>
                                <tr>
                                    <td colspan="12" class="text-center text-muted">
                                        <i class="fas fa-inbox"></i> Belum ada jadwal sidang PKL.
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php 
                                $no = 1; 
                                $page = ($this->uri->segment(3)) ? $this->uri->segment(3) : 1;
                                $no = ($page - 1) * 10 + 1;
                                foreach ($jadwal_list as $jadwal): 
                                ?>
                                    <tr>
                                        <td><?php echo $no++; ?></td>
                                        <td><strong><?php echo $jadwal->nim; ?></strong></td>
                                        <td><?php echo $jadwal->nama_mahasiswa; ?></td>
                                        <td class="text-center">
                                            <span class="badge badge-info"><?php echo $jadwal->kelas; ?></span>
                                        </td>
                                        <td><?php echo $jadwal->pembimbing_nama ?: '-'; ?></td>
                                        <td><?php echo $jadwal->penguji_nama ?: '-'; ?></td>
                                        <td class="text-center"><?php echo date('d/m/Y', strtotime($jadwal->tanggal)); ?></td>
                                        <td class="text-center"><?php echo hari_dari_tanggal($jadwal->tanggal); ?></td>
                                        <td class="text-center"><?php echo date('H:i', strtotime($jadwal->jam_mulai)) . ' - ' . date('H:i', strtotime($jadwal->jam_selesai)); ?></td>
                                        <td><?php echo $jadwal->ruangan; ?></td>
                                        <td class="text-center">
                                            <?php
                                            $status_class = 'secondary';
                                            switch ($jadwal->status) {
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
                                            <span class="badge badge-<?php echo $status_class; ?>"><?php echo ucfirst($jadwal->status); ?></span>
                                        </td>
                                        <td class="text-center">
                                            <?php if (get_user_role() !== 'kaprodi'): ?>
                                            <a href="<?php echo site_url('Jadwal_pkl_sekprodi/edit/' . $jadwal->id); ?>" class="btn btn-sm btn-warning" title="Edit">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <?php else: ?>
                                            <span class="text-muted">—</span>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>

                <?php if (!empty($pagination)): ?>
                    <div class="mt-3">
                        <?php echo $pagination; ?>
                    </div>
                <?php endif; ?>

                <div class="mt-2">
                    <small class="text-muted">Total: <strong><?php echo $total_rows; ?></strong> jadwal</small>
                </div>
            </div>
        </div>
    </div>
</div>





