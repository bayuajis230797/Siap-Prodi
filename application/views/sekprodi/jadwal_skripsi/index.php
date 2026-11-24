<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Jadwal Sidang Skripsi</h3>
                <div class="card-tools">
                    <a href="<?php echo site_url('Jadwal_skripsi_sekprodi/export_excel' . (isset($status_filter) && $status_filter ? '?status=' . urlencode($status_filter) : '')); ?>" class="btn btn-success btn-sm">
                        <i class="fas fa-file-excel"></i> Download Excel
                    </a>
                    <?php if (get_user_role() !== 'kaprodi'): ?>
                    <a href="<?php echo site_url('Jadwal_skripsi_sekprodi/create'); ?>" class="btn btn-primary btn-sm">
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
                        <form method="get" action="<?php echo site_url('Jadwal_skripsi_sekprodi/index'); ?>">
                            <div class="input-group">
                                <select name="status" class="form-control">
                                    <option value="">-- Semua Status --</option>
                                    <option value="terjadwal" <?php echo (isset($status_filter) && $status_filter == 'terjadwal') ? 'selected' : ''; ?>>Terjadwal</option>
                                    <option value="selesai" <?php echo (isset($status_filter) && $status_filter == 'selesai') ? 'selected' : ''; ?>>Selesai</option>
                                    <option value="batal" <?php echo (isset($status_filter) && $status_filter == 'batal') ? 'selected' : ''; ?>>Batal</option>
                                </select>
                                <div class="input-group-append">
                                    <button type="submit" class="btn btn-info">
                                        <i class="fas fa-filter"></i> Filter
                                    </button>
                                    <?php if (isset($status_filter) && $status_filter): ?>
                                        <a href="<?php echo site_url('Jadwal_skripsi_sekprodi'); ?>" class="btn btn-secondary">
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
                                <th>Ketua Penguji</th>
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
                                    <td colspan="11" class="text-center text-muted">
                                        <i class="fas fa-inbox"></i> Belum ada jadwal sidang Skripsi.
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
                                        <td><strong><?php echo isset($jadwal->nim) ? $jadwal->nim : '-'; ?></strong></td>
                                        <td><?php echo isset($jadwal->nama_mahasiswa) ? $jadwal->nama_mahasiswa : '-'; ?></td>
                                        <td class="text-center">
                                            <span class="badge badge-info"><?php echo isset($jadwal->kelas) ? $jadwal->kelas : '-'; ?></span>
                                        </td>
                                        <td><?php echo isset($jadwal->ketua_penguji) ? $jadwal->ketua_penguji : '-'; ?></td>
                                        <td class="text-center"><?php echo isset($jadwal->tanggal_ujian) ? date('d/m/Y', strtotime($jadwal->tanggal_ujian)) : '-'; ?></td>
                                        <td class="text-center"><?php echo hari_dari_tanggal(isset($jadwal->tanggal_ujian) ? $jadwal->tanggal_ujian : null); ?></td>
                                        <td class="text-center">
                                            <?php 
                                            if (isset($jadwal->jam_mulai) && isset($jadwal->jam_selesai)) {
                                                echo date('H:i', strtotime($jadwal->jam_mulai)) . ' - ' . date('H:i', strtotime($jadwal->jam_selesai));
                                            } else {
                                                echo '-';
                                            }
                                            ?>
                                        </td>
                                        <td><?php echo isset($jadwal->ruangan) ? $jadwal->ruangan : '-'; ?></td>
                                        <td class="text-center">
                                            <?php
                                            $status_class = 'secondary';
                                            $status = isset($jadwal->status) ? $jadwal->status : 'terjadwal';
                                            switch ($status) {
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
                                            <span class="badge badge-<?php echo $status_class; ?>"><?php echo ucfirst($status); ?></span>
                                        </td>
                                        <td class="text-center">
                                            <a href="<?php echo site_url('Jadwal_skripsi_sekprodi/view/' . $jadwal->id); ?>" class="btn btn-sm btn-info" title="View">
                                                <i class="fas fa-eye"></i> View
                                            </a>
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
                    <small class="text-muted">Total: <strong><?php echo isset($total_rows) ? $total_rows : count($jadwal_list ?? array()); ?></strong> jadwal</small>
                </div>
            </div>
        </div>
    </div>
</div>


