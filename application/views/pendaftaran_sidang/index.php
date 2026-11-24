<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Pengajuan Pendaftaran Sidang</h3>
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

                <?php if (isset($tahun_aktif) && $tahun_aktif): ?>
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle"></i> 
                        <strong>Tahun Akademik Aktif:</strong> <?php echo $tahun_aktif->tahun_akademik . ' ' . ucfirst($tahun_aktif->semester); ?>
                    </div>
                <?php endif; ?>

                <!-- Filter -->
                <div class="row mb-3">
                    <div class="col-md-6">
                        <form method="get" action="<?php echo site_url('Pendaftaran_sidang/index'); ?>">
                            <div class="input-group">
                                <select name="status" class="form-control">
                                    <option value="">-- Semua Status --</option>
                                    <option value="menunggu" <?php echo ($status_filter == 'menunggu') ? 'selected' : ''; ?>>Menunggu</option>
                                    <option value="disetujui" <?php echo ($status_filter == 'disetujui') ? 'selected' : ''; ?>>Disetujui</option>
                                    <option value="ditolak" <?php echo ($status_filter == 'ditolak') ? 'selected' : ''; ?>>Ditolak</option>
                                    <option value="selesai" <?php echo ($status_filter == 'selesai') ? 'selected' : ''; ?>>Selesai</option>
                                </select>
                                <div class="input-group-append">
                                    <button type="submit" class="btn btn-info">
                                        <i class="fas fa-filter"></i> Filter
                                    </button>
                                    <?php if ($status_filter): ?>
                                        <a href="<?php echo site_url('Pendaftaran_sidang'); ?>" class="btn btn-secondary">
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
                                <th>Judul Skripsi</th>
                                <th width="12%" class="text-center">Status</th>
                                <th width="15%" class="text-center">Tanggal Pengajuan</th>
                                <th width="15%" class="text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($pendaftaran_list)): ?>
                                <tr>
                                    <td colspan="8" class="text-center text-muted">
                                        <i class="fas fa-inbox"></i> Belum ada pengajuan pendaftaran sidang.
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php 
                                $no = 1; 
                                $page = ($this->uri->segment(3)) ? $this->uri->segment(3) : 1;
                                $no = ($page - 1) * 10 + 1;
                                foreach ($pendaftaran_list as $pendaftaran): 
                                ?>
                                    <tr>
                                        <td><?php echo $no++; ?></td>
                                        <td><strong><?php echo $pendaftaran->nim; ?></strong></td>
                                        <td><?php echo $pendaftaran->nama_mahasiswa; ?></td>
                                        <td class="text-center">
                                            <span class="badge badge-info"><?php echo $pendaftaran->kelas; ?></span>
                                        </td>
                                        <td><?php echo $pendaftaran->judul_skripsi ?: '-'; ?></td>
                                        <td class="text-center">
                                            <?php
                                            $status_class = 'secondary';
                                            switch ($pendaftaran->status) {
                                                case 'menunggu':
                                                    $status_class = 'warning';
                                                    break;
                                                case 'disetujui':
                                                    $status_class = 'success';
                                                    break;
                                                case 'ditolak':
                                                    $status_class = 'danger';
                                                    break;
                                                case 'selesai':
                                                    $status_class = 'info';
                                                    break;
                                            }
                                            ?>
                                            <span class="badge badge-<?php echo $status_class; ?>"><?php echo ucfirst($pendaftaran->status); ?></span>
                                        </td>
                                        <td class="text-center">
                                            <?php echo date('d/m/Y H:i', strtotime($pendaftaran->created_at)); ?>
                                        </td>
                                        <td class="text-center">
                                            <a href="<?php echo site_url('Pendaftaran_sidang/detail/' . $pendaftaran->id); ?>" class="btn btn-sm btn-info" title="Lihat Detail">
                                                <i class="fas fa-eye"></i> Detail
                                            </a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>

                <?php if (isset($pagination)): ?>
                    <div class="row mt-3">
                        <div class="col-md-6">
                            <p class="text-muted">Menampilkan <?php echo $total_rows; ?> data</p>
                        </div>
                        <div class="col-md-6">
                            <?php echo $pagination; ?>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>












