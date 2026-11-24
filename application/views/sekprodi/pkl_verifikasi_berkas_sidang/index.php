<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Verifikasi Berkas Sidang PKL</h3>
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

                <?php if ($tahun_aktif): ?>
                    <div class="alert alert-info mb-3">
                        <i class="fas fa-info-circle"></i> 
                        <strong>Tahun Akademik Aktif:</strong> <?php echo $tahun_aktif->tahun_akademik . ' ' . ucfirst($tahun_aktif->semester); ?>
                    </div>
                <?php endif; ?>

                <div class="row mb-3">
                    <div class="col-md-3 col-sm-6">
                        <div class="small-box bg-warning">
                            <div class="inner">
                                <h3><?php echo (int) ($verification_summary['pending_total'] ?? 0); ?></h3>
                                <p>Menunggu Verifikasi</p>
                            </div>
                            <div class="icon"><i class="fas fa-hourglass-half"></i></div>
                        </div>
                    </div>
                    <div class="col-md-3 col-sm-6">
                        <div class="small-box bg-danger">
                            <div class="inner">
                                <h3><?php echo (int) ($verification_summary['pending_overdue'] ?? 0); ?></h3>
                                <p>Menunggu >= 3 Hari</p>
                            </div>
                            <div class="icon"><i class="fas fa-exclamation-triangle"></i></div>
                        </div>
                    </div>
                    <div class="col-md-3 col-sm-6">
                        <div class="small-box bg-maroon">
                            <div class="inner">
                                <h3><?php echo (int) ($verification_summary['invalid_link_total'] ?? 0); ?></h3>
                                <p>Indikasi Link Tidak Valid</p>
                            </div>
                            <div class="icon"><i class="fas fa-unlink"></i></div>
                        </div>
                    </div>
                    <div class="col-md-3 col-sm-6">
                        <div class="small-box bg-info">
                            <div class="inner">
                                <h3><?php echo (int) ($verification_summary['missing_required_total'] ?? 0); ?></h3>
                                <p>Berkas Wajib Kurang</p>
                            </div>
                            <div class="icon"><i class="fas fa-file-excel"></i></div>
                        </div>
                    </div>
                </div>

                <!-- Filter + pencarian + tampilkan -->
                <div class="row mb-3">
                    <div class="col-md-3">
                        <form method="get" action="<?php echo site_url('Pkl_verifikasi_berkas_sidang/index'); ?>">
                            <?php if (!empty($kelas_filter)): ?><input type="hidden" name="kelas" value="<?php echo htmlspecialchars($kelas_filter); ?>"><?php endif; ?>
                            <?php if (!empty($q_filter)): ?><input type="hidden" name="q" value="<?php echo htmlspecialchars($q_filter); ?>"><?php endif; ?>
                            <?php if (isset($per_page) && (int) $per_page !== 10): ?><input type="hidden" name="per_page" value="<?php echo (int) $per_page; ?>"><?php endif; ?>
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
                                </div>
                            </div>
                        </form>
                    </div>
                    <div class="col-md-3">
                        <form method="get" action="<?php echo site_url('Pkl_verifikasi_berkas_sidang/index'); ?>">
                            <?php if (!empty($status_filter)): ?><input type="hidden" name="status" value="<?php echo htmlspecialchars($status_filter); ?>"><?php endif; ?>
                            <?php if (!empty($q_filter)): ?><input type="hidden" name="q" value="<?php echo htmlspecialchars($q_filter); ?>"><?php endif; ?>
                            <?php if (isset($per_page) && (int) $per_page !== 10): ?><input type="hidden" name="per_page" value="<?php echo (int) $per_page; ?>"><?php endif; ?>
                            <div class="input-group">
                                <select name="kelas" class="form-control">
                                    <option value="">-- Semua Kelas --</option>
                                    <?php if (!empty($kelas_list)): ?>
                                        <?php foreach ($kelas_list as $kelas): ?>
                                            <option value="<?php echo htmlspecialchars($kelas->kelas); ?>" <?php echo ($kelas_filter == $kelas->kelas) ? 'selected' : ''; ?>>
                                                <?php echo htmlspecialchars($kelas->kelas); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </select>
                                <div class="input-group-append">
                                    <button type="submit" class="btn btn-info">
                                        <i class="fas fa-filter"></i> Filter
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                    <div class="col-md-4">
                        <form method="get" action="<?php echo site_url('Pkl_verifikasi_berkas_sidang/index'); ?>">
                            <?php if (!empty($status_filter)): ?><input type="hidden" name="status" value="<?php echo htmlspecialchars($status_filter); ?>"><?php endif; ?>
                            <?php if (!empty($kelas_filter)): ?><input type="hidden" name="kelas" value="<?php echo htmlspecialchars($kelas_filter); ?>"><?php endif; ?>
                            <?php if (isset($per_page) && (int) $per_page !== 10): ?><input type="hidden" name="per_page" value="<?php echo (int) $per_page; ?>"><?php endif; ?>
                            <div class="input-group">
                                <input type="text" name="q" class="form-control" placeholder="Cari NIM / nama / judul PKL..." value="<?php echo htmlspecialchars(isset($q_filter) ? $q_filter : ''); ?>">
                                <div class="input-group-append">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-search"></i> Cari
                                    </button>
                                    <?php if (!empty($status_filter) || !empty($kelas_filter) || !empty($q_filter)): ?>
                                        <a href="<?php echo site_url('Pkl_verifikasi_berkas_sidang'); ?>" class="btn btn-secondary">
                                            <i class="fas fa-times"></i> Reset
                                        </a>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </form>
                    </div>
                    <div class="col-md-2">
                        <form method="get" action="<?php echo site_url('Pkl_verifikasi_berkas_sidang/index'); ?>" id="formPerPageVerifikasiBerkasPkl" class="form-inline justify-content-md-end">
                            <?php if (!empty($status_filter)): ?><input type="hidden" name="status" value="<?php echo htmlspecialchars($status_filter); ?>"><?php endif; ?>
                            <?php if (!empty($kelas_filter)): ?><input type="hidden" name="kelas" value="<?php echo htmlspecialchars($kelas_filter); ?>"><?php endif; ?>
                            <?php if (!empty($q_filter)): ?><input type="hidden" name="q" value="<?php echo htmlspecialchars($q_filter); ?>"><?php endif; ?>
                            <label for="per_page_verifikasi_berkas_pkl" class="mr-2 mb-0">Tampilkan:</label>
                            <select name="per_page" id="per_page_verifikasi_berkas_pkl" class="form-control form-control-sm" onchange="document.getElementById('formPerPageVerifikasiBerkasPkl').submit();">
                                <option value="10" <?php echo (isset($per_page) && (int) $per_page === 10) ? 'selected' : ''; ?>>10</option>
                                <option value="25" <?php echo (isset($per_page) && (int) $per_page === 25) ? 'selected' : ''; ?>>25</option>
                                <option value="50" <?php echo (isset($per_page) && (int) $per_page === 50) ? 'selected' : ''; ?>>50</option>
                                <option value="100" <?php echo (isset($per_page) && (int) $per_page === 100) ? 'selected' : ''; ?>>100</option>
                            </select>
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
                                <th>Judul PKL</th>
                                <th width="16%" class="text-center">Prioritas</th>
                                <th width="12%" class="text-center">Status</th>
                                <th width="15%" class="text-center">Tanggal Pengajuan</th>
                                <th width="15%" class="text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($pendaftaran_list)): ?>
                                <tr>
                                    <td colspan="9" class="text-center text-muted">
                                        <i class="fas fa-inbox"></i> Belum ada pendaftaran sidang PKL.
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php 
                                $page = ($this->uri->segment(3)) ? (int) $this->uri->segment(3) : 1;
                                $no = ($page - 1) * (isset($per_page) ? (int) $per_page : 10) + 1;
                                foreach ($pendaftaran_list as $pendaftaran): 
                                ?>
                                    <tr>
                                        <td><?php echo $no++; ?></td>
                                        <td><strong><?php echo $pendaftaran->nim; ?></strong></td>
                                        <td><?php echo $pendaftaran->nama_mahasiswa; ?></td>
                                        <td class="text-center">
                                            <span class="badge badge-info"><?php echo $pendaftaran->kelas; ?></span>
                                        </td>
                                        <td><?php echo $pendaftaran->judul_pkl ?: '-'; ?></td>
                                        <td class="text-center">
                                            <?php $flags = $pendaftaran->verifikasi_flags ?? array(); ?>
                                            <span class="badge badge-<?php echo htmlspecialchars($flags['priority_class'] ?? 'secondary'); ?>">
                                                <?php echo htmlspecialchars($flags['priority_label'] ?? 'Rendah'); ?>
                                            </span>
                                            <?php if (!empty($flags['is_pending']) && !empty($flags['wait_days'])): ?>
                                                <br><small class="text-muted"><?php echo (int) $flags['wait_days']; ?> hari</small>
                                            <?php endif; ?>
                                            <?php if (!empty($flags['invalid_link_count'])): ?>
                                                <br><small class="text-danger">Link invalid: <?php echo (int) $flags['invalid_link_count']; ?></small>
                                            <?php endif; ?>
                                            <?php if (!empty($flags['missing_required_count'])): ?>
                                                <br><small class="text-danger">Kurang wajib: <?php echo (int) $flags['missing_required_count']; ?></small>
                                            <?php endif; ?>
                                        </td>
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
                                        <td class="text-center"><?php echo date('d/m/Y H:i', strtotime($pendaftaran->created_at)); ?></td>
                                        <td class="text-center">
                                            <a href="<?php echo site_url('Pkl_verifikasi_berkas_sidang/detail/' . $pendaftaran->id); ?>" class="btn btn-sm btn-info" title="Detail">
                                                <i class="fas fa-eye"></i> Detail
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
                    <small class="text-muted">Total: <strong><?php echo $total_rows; ?></strong> pendaftaran</small>
                </div>
            </div>
        </div>
    </div>
</div>





