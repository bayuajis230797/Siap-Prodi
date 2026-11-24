<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Plot Pembimbing Proposal TA</h3>
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

                <!-- Filter -->
                <div class="row mb-3">
                    <div class="col-md-4">
                        <form method="get" action="<?php echo site_url('Proposal_plot_pembimbing/index'); ?>" id="formFilterKelas">
                            <div class="input-group">
                                <select name="kelas" class="form-control" onchange="document.getElementById('formFilterKelas').submit();">
                                    <option value="">-- Semua Kelas --</option>
                                    <?php foreach ($kelas_list as $kelas): ?>
                                        <option value="<?php echo htmlspecialchars($kelas); ?>" 
                                                <?php echo ($kelas_filter === $kelas) ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($kelas); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                                <?php if ($kelas_filter): ?>
                                    <div class="input-group-append">
                                        <a href="<?php echo site_url('Proposal_plot_pembimbing'); ?>" class="btn btn-secondary">
                                            <i class="fas fa-times"></i> Reset
                                        </a>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </form>
                    </div>
                    <div class="col-md-4 offset-md-4">
                        <form method="get" action="<?php echo site_url('Proposal_plot_pembimbing/index'); ?>" id="formPerPage" class="form-inline justify-content-end">
                            <?php if ($kelas_filter): ?>
                                <input type="hidden" name="kelas" value="<?php echo htmlspecialchars($kelas_filter); ?>">
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

                <div class="table-responsive">
                    <table class="table table-bordered table-striped table-hover">
                        <thead>
                            <tr>
                                <th width="5%">No</th>
                                <th width="12%">NIM</th>
                                <th>Nama Mahasiswa</th>
                                <th width="10%" class="text-center">Kelas</th>
                                <th>Judul Pengajuan</th>
                                <th width="25%">Pembimbing</th>
                                <th width="12%" class="text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($pengajuan_list)): ?>
                                <tr>
                                    <td colspan="7" class="text-center">Tidak ada pengajuan judul proposal.</td>
                                </tr>
                            <?php else: ?>
                                <?php 
                                $no = 1;
                                $start_no = ($this->uri->segment(3) ? ($this->uri->segment(3) - 1) : 0) * $per_page + 1;
                                foreach ($pengajuan_list as $pengajuan): 
                                ?>
                                    <tr>
                                        <td><?php echo $start_no++; ?></td>
                                        <td><strong><?php echo htmlspecialchars($pengajuan->nim ?? '-'); ?></strong></td>
                                        <td><?php echo htmlspecialchars($pengajuan->nama_mahasiswa ?? '-'); ?></td>
                                        <td class="text-center">
                                            <span class="badge badge-info"><?php echo htmlspecialchars($pengajuan->kelas ?? '-'); ?></span>
                                        </td>
                                        <td>
                                            <small><?php echo htmlspecialchars($pengajuan->judul_pengajuan ?? '-'); ?></small>
                                        </td>
                                        <td>
                                            <?php $nama_pembimbing = !empty($pengajuan->pembimbing_nama) ? $pengajuan->pembimbing_nama : ($pengajuan->pembimbing_nama_efektif ?? null); ?>
                                            <?php if (!empty($nama_pembimbing)): ?>
                                                <strong><?php echo htmlspecialchars($nama_pembimbing); ?></strong>
                                            <?php else: ?>
                                                <span class="text-muted"><i>Belum diplot</i></span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="text-center">
                                            <?php
                                            // Cek apakah pembimbing sudah diplot
                                            $sudah_diplot = !empty($nama_pembimbing);
                                            ?>
                                            <a href="<?php echo site_url('Proposal_plot_pembimbing/detail/' . $pengajuan->id); ?>" 
                                               class="btn btn-<?php echo $sudah_diplot ? 'info' : 'primary'; ?> btn-sm" 
                                               title="<?php echo $sudah_diplot ? 'Edit Plot Pembimbing' : 'Plot Pembimbing'; ?>">
                                                <i class="fas fa-<?php echo $sudah_diplot ? 'edit' : 'user-plus'; ?>"></i> 
                                                <?php echo $sudah_diplot ? 'Edit' : 'Plot'; ?>
                                            </a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>

                <?php if (!empty($pagination)): ?>
                    <div class="row mt-3">
                        <div class="col-md-6">
                            <p class="text-muted">Menampilkan <?php echo count($pengajuan_list); ?> dari <?php echo $total_rows; ?> data</p>
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

