<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Daftar Mahasiswa PKL</h3>
                <div class="card-tools">
                    <a href="<?php echo site_url('Pkl_register/create'); ?>" class="btn btn-primary btn-sm">
                        <i class="fas fa-plus"></i> Tambah Mahasiswa PKL
                    </a>
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

                <?php if ($tahun_aktif): ?>
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle"></i> Tahun Akademik Aktif: 
                        <strong><?php echo htmlspecialchars($tahun_aktif->tahun_akademik . ' - ' . ucfirst($tahun_aktif->semester)); ?></strong>
                    </div>
                <?php else: ?>
                    <div class="alert alert-warning">
                        <i class="fas fa-exclamation-triangle"></i> Tidak ada tahun akademik aktif. Silakan aktifkan tahun akademik terlebih dahulu.
                    </div>
                <?php endif; ?>

                <!-- Filter -->
                <form method="get" action="<?php echo site_url('Pkl_register/index'); ?>" class="mb-3">
                    <div class="row">
                        <div class="col-md-3">
                            <select name="kelas" class="form-control">
                                <option value="">Semua Kelas</option>
                                <?php foreach ($kelas_list as $kelas): ?>
                                    <option value="<?php echo htmlspecialchars($kelas); ?>" 
                                            <?php echo ($kelas_filter === $kelas) ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($kelas); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <button type="submit" class="btn btn-info">
                                <i class="fas fa-filter"></i> Filter
                            </button>
                            <a href="<?php echo site_url('Pkl_register/index'); ?>" class="btn btn-secondary">
                                <i class="fas fa-times"></i> Reset
                            </a>
                        </div>
                    </div>
                </form>

                <!-- Show Entries -->
                <div class="row mb-3">
                    <div class="col-md-4 offset-md-8">
                        <form method="get" action="<?php echo site_url('Pkl_register/index'); ?>" id="formPerPage" class="form-inline justify-content-end">
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
                    <table class="table table-bordered table-striped">
                        <thead>
                            <tr>
                                <th width="5%">No</th>
                                <th width="12%">NIM</th>
                                <th>Nama</th>
                                <th width="10%">Kelas</th>
                                <th width="15%">Tahun Akademik</th>
                                <th width="15%">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($mahasiswa_pkl_list)): ?>
                                <tr>
                                    <td colspan="6" class="text-center">Tidak ada data mahasiswa PKL.</td>
                                </tr>
                            <?php else: ?>
                                <?php 
                                $no = 1;
                                $start_no = ($this->uri->segment(3) ? ($this->uri->segment(3) - 1) : 0) * $per_page + 1;
                                foreach ($mahasiswa_pkl_list as $mhs): 
                                ?>
                                    <tr>
                                        <td><?php echo $start_no++; ?></td>
                                        <td><strong><?php echo htmlspecialchars($mhs->nim); ?></strong></td>
                                        <td><?php echo htmlspecialchars($mhs->nama_mahasiswa); ?></td>
                                        <td><?php echo htmlspecialchars($mhs->kelas ?: '-'); ?></td>
                                        <td>
                                            <?php if (isset($mhs->tahun_akademik)): ?>
                                                <?php echo htmlspecialchars($mhs->tahun_akademik); ?>
                                            <?php else: ?>
                                                <?php echo htmlspecialchars($tahun_aktif->tahun_akademik); ?>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <a href="<?php echo site_url('Pkl_register/delete/' . $mhs->id); ?>" 
                                               class="btn btn-danger btn-sm" 
                                               onclick="return confirm('Hapus mahasiswa ini dari daftar PKL?')"
                                               title="Hapus">
                                                <i class="fas fa-trash"></i> Hapus
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
                                Total: <?php echo isset($total_rows) ? $total_rows : count($mahasiswa_pkl_list); ?> mahasiswa
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
