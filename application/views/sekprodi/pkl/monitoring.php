<div class="row">
    <div class="col-12">
        <?php if ($this->session->flashdata('error')): ?>
            <div class="alert alert-danger alert-dismissible fade show">
                <i class="fas fa-exclamation-circle"></i> <?php echo $this->session->flashdata('error'); ?>
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
        <?php endif; ?>
    </div>
</div>

<div class="row">
    <div class="col-12">
            <div class="card card-primary card-outline">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-chart-line"></i> Logbook & Absensi PKL
                </h3>
                <div class="card-tools">
                    <button type="button" class="btn btn-tool" data-card-widget="collapse">
                        <i class="fas fa-minus"></i>
                    </button>
                </div>
            </div>
            <div class="card-body">
                <?php if ($tahun_aktif): ?>
                    <div class="alert alert-info mb-3">
                        <i class="fas fa-info-circle"></i> 
                        <strong>Tahun Akademik Aktif:</strong> <?php echo $tahun_aktif->tahun_akademik . ' ' . ucfirst($tahun_aktif->semester); ?>
                    </div>
                <?php endif; ?>

                <?php if (isset($risk_summary)): ?>
                    <div class="row mb-3">
                        <div class="col-md-4">
                            <div class="small-box bg-danger">
                                <div class="inner">
                                    <h3><?php echo (int) ($risk_summary['high'] ?? 0); ?></h3>
                                    <p>Risiko Tinggi</p>
                                </div>
                                <div class="icon"><i class="fas fa-exclamation-triangle"></i></div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="small-box bg-warning">
                                <div class="inner">
                                    <h3><?php echo (int) ($risk_summary['medium'] ?? 0); ?></h3>
                                    <p>Risiko Sedang</p>
                                </div>
                                <div class="icon"><i class="fas fa-exclamation-circle"></i></div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="small-box bg-success">
                                <div class="inner">
                                    <h3><?php echo (int) ($risk_summary['low'] ?? 0); ?></h3>
                                    <p>Risiko Rendah</p>
                                </div>
                                <div class="icon"><i class="fas fa-check-circle"></i></div>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>

                <!-- Filter -->
                <div class="row mb-3">
                    <div class="col-md-4">
                        <form method="get" action="<?php echo site_url('Pkl_monitoring_sekprodi/index'); ?>">
                            <div class="input-group">
                                <select name="kelas" class="form-control">
                                    <option value="">-- Semua Kelas --</option>
                                    <?php foreach ($kelas_list as $kelas): ?>
                                        <option value="<?php echo htmlspecialchars($kelas->kelas); ?>" 
                                                <?php echo ($filter_kelas == $kelas->kelas) ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($kelas->kelas); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                                <div class="input-group-append">
                                    <button type="submit" class="btn btn-info">
                                        <i class="fas fa-filter"></i> Filter
                                    </button>
                                    <?php if ($filter_kelas): ?>
                                        <a href="<?php echo site_url('Pkl_monitoring_sekprodi'); ?>" class="btn btn-secondary">
                                            <i class="fas fa-times"></i> Reset
                                        </a>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </form>
                    </div>
                    <div class="col-md-4 offset-md-4">
                        <form method="get" action="<?php echo site_url('Pkl_monitoring_sekprodi/index'); ?>" id="formPerPage" class="form-inline justify-content-end">
                            <?php if ($filter_kelas): ?>
                                <input type="hidden" name="kelas" value="<?php echo htmlspecialchars($filter_kelas); ?>">
                            <?php endif; ?>
                            <label for="per_page_select" class="mr-2">Tampilkan:</label>
                            <select name="per_page" id="per_page_select" class="form-control form-control-sm" onchange="document.getElementById('formPerPage').submit();">
                                <option value="10" <?php echo ($per_page == 10) ? 'selected' : ''; ?>>10</option>
                                <option value="25" <?php echo ($per_page == 25) ? 'selected' : ''; ?>>25</option>
                                <option value="50" <?php echo ($per_page == 50) ? 'selected' : ''; ?>>50</option>
                                <option value="100" <?php echo ($per_page == 100) ? 'selected' : ''; ?>>100</option>
                            </select>
                            <span class="ml-2">data</span>
                        </form>
                    </div>
                </div>

                <!-- Daftar Mahasiswa -->
                <div class="table-responsive">
                    <table class="table table-bordered table-striped table-hover">
                        <thead>
                            <tr>
                                <th width="5%">No</th>
                                <th width="12%">NIM</th>
                                <th>Nama Mahasiswa</th>
                                <th width="10%" class="text-center">Kelas</th>
                                <th width="15%">Judul PKL</th>
                                <th width="12%" class="text-center">Logbook</th>
                                <th width="12%" class="text-center">Absensi</th>
                                <th width="16%">Tahap Workflow</th>
                                <th width="18%">Risiko Monitoring</th>
                                <th width="15%" class="text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($mahasiswa_list)): ?>
                                <tr>
                                    <td colspan="10" class="text-center">Tidak ada data mahasiswa PKL.</td>
                                </tr>
                            <?php else: ?>
                                <?php 
                                $no = 1;
                                $start_no = ($this->uri->segment(3) ? ($this->uri->segment(3) - 1) : 0) * $per_page + 1;
                                foreach ($mahasiswa_list as $mhs): 
                                ?>
                                    <tr>
                                        <td><?php echo $start_no++; ?></td>
                                        <td><strong><?php echo htmlspecialchars($mhs->nim); ?></strong></td>
                                        <td><?php echo htmlspecialchars($mhs->nama_mahasiswa); ?></td>
                                        <td class="text-center">
                                            <span class="badge badge-info"><?php echo htmlspecialchars($mhs->kelas ?: '-'); ?></span>
                                        </td>
                                        <td>
                                            <div style="max-width: 200px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;" 
                                                 title="<?php echo htmlspecialchars($mhs->judul_pkl); ?>">
                                                <?php echo htmlspecialchars($mhs->judul_pkl); ?>
                                            </div>
                                        </td>
                                        <td class="text-center">
                                            <?php if ($mhs->statistik_logbook && $mhs->statistik_logbook->total > 0): ?>
                                                <span class="badge badge-success">
                                                    <i class="fas fa-book"></i> <?php echo $mhs->statistik_logbook->total; ?>
                                                </span>
                                            <?php else: ?>
                                                <span class="badge badge-secondary">0</span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="text-center">
                                            <?php if ($mhs->statistik_absensi && $mhs->statistik_absensi->total_hari > 0): ?>
                                                <span class="badge badge-success">
                                                    <i class="fas fa-calendar-check"></i> <?php echo $mhs->statistik_absensi->total_hari; ?> hari
                                                </span>
                                            <?php else: ?>
                                                <span class="badge badge-secondary">0</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php $wf = $mhs->workflow_state ?? null; ?>
                                            <?php if ($wf): ?>
                                                <span class="badge badge-<?php echo htmlspecialchars($wf['class'] ?? 'secondary'); ?>">
                                                    <?php echo htmlspecialchars($wf['label'] ?? '-'); ?>
                                                </span>
                                                <?php if (!empty($wf['progress'])): ?>
                                                    <small class="text-muted d-block mt-1">
                                                        <?php echo (int) ($wf['progress']['current_step'] ?? 0); ?>/<?php echo (int) ($wf['progress']['total_steps'] ?? 10); ?>
                                                        (<?php echo (float) ($wf['progress']['percentage'] ?? 0); ?>%)
                                                    </small>
                                                <?php endif; ?>
                                            <?php else: ?>
                                                <small class="text-muted">-</small>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php $risk = $mhs->risk_indicator ?? null; ?>
                                            <?php if ($risk): ?>
                                                <span class="badge badge-<?php echo htmlspecialchars($risk['class']); ?>">
                                                    Risiko <?php echo htmlspecialchars($risk['label']); ?>
                                                </span>
                                                <?php if (!empty($risk['issues'])): ?>
                                                    <ul class="mb-0 pl-3 mt-1">
                                                        <?php foreach (array_slice($risk['issues'], 0, 2) as $issue): ?>
                                                            <li><small><?php echo htmlspecialchars($issue); ?></small></li>
                                                        <?php endforeach; ?>
                                                    </ul>
                                                <?php else: ?>
                                                    <small class="text-muted d-block mt-1">Tidak ada indikator risiko utama.</small>
                                                <?php endif; ?>
                                            <?php else: ?>
                                                <small class="text-muted">-</small>
                                            <?php endif; ?>
                                        </td>
                                        <td class="text-center">
                                            <a href="<?php echo site_url('Pkl_monitoring_sekprodi/detail/' . ($mhs->id ? $mhs->id : $mhs->id_mahasiswa)); ?>" 
                                               class="btn btn-info btn-sm" 
                                               title="Lihat Detail">
                                                <i class="fas fa-eye"></i> View
                                            </a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <?php if (isset($pagination) && !empty($pagination)): ?>
                    <div class="row mt-3">
                        <div class="col-md-6">
                            <p class="text-muted">
                                Menampilkan <?php echo count($mahasiswa_list); ?> dari <?php echo $total_rows; ?> mahasiswa
                            </p>
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

