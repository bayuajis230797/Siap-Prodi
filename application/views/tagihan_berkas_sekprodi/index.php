<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Tagihan Berkas</h3>
                <div class="card-tools">
                    <a href="<?php echo site_url('Tagihan_berkas_sekprodi/create'); ?>" class="btn btn-primary btn-sm">
                        <i class="fas fa-plus"></i> Buat Tagihan Baru
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

                <!-- Filter Kelas -->
                <div class="card mb-3">
                    <div class="card-body">
                        <form method="get" action="<?php echo site_url('Tagihan_berkas_sekprodi/index'); ?>" class="form-inline">
                            <div class="form-group mr-2">
                                <label for="kelas" class="mr-2">Filter Kelas:</label>
                                <select name="kelas" id="kelas" class="form-control form-control-sm">
                                    <option value="">-- Semua Kelas --</option>
                                    <?php foreach ($kelas_list as $kelas): ?>
                                        <option value="<?php echo htmlspecialchars($kelas->kelas); ?>" 
                                                <?php echo ($kelas_filter == $kelas->kelas) ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($kelas->kelas); ?> (<?php echo $kelas->jumlah_mahasiswa; ?> mahasiswa)
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <button type="submit" class="btn btn-primary btn-sm mr-2">
                                <i class="fas fa-filter"></i> Filter
                            </button>
                            <?php if ($kelas_filter): ?>
                                <a href="<?php echo site_url('Tagihan_berkas_sekprodi'); ?>" class="btn btn-secondary btn-sm">
                                    <i class="fas fa-times"></i> Reset
                                </a>
                            <?php endif; ?>
                        </form>
                    </div>
                </div>

                <!-- Tabel Tagihan -->
                <div class="table-responsive">
                    <table class="table table-bordered table-striped table-hover">
                        <thead>
                            <tr>
                                <th width="5%">No</th>
                                <th>Nama Tagihan</th>
                                <th>Jenis</th>
                                <th>Kelas</th>
                                <th>Deadline</th>
                                <th>Status</th>
                                <th>Dibuat Oleh</th>
                                <th>Tanggal Dibuat</th>
                                <th width="15%">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($tagihan_list)): ?>
                                <tr>
                                    <td colspan="9" class="text-center text-muted">Tidak ada data tagihan.</td>
                                </tr>
                            <?php else: ?>
                                <?php $no = 1; foreach ($tagihan_list as $tagihan): ?>
                                    <tr>
                                        <td><?php echo $no++; ?></td>
                                        <td><strong><?php echo htmlspecialchars($tagihan->nama_tagihan); ?></strong></td>
                                        <td>
                                            <?php if ($tagihan->is_hardcopy == 1): ?>
                                                <span class="badge badge-warning">Hardcopy</span>
                                            <?php else: ?>
                                                <span class="badge badge-info">Softfile</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php echo $tagihan->kelas_filter ? htmlspecialchars($tagihan->kelas_filter) : '<span class="text-muted">Semua Kelas</span>'; ?>
                                        </td>
                                        <td>
                                            <?php echo $tagihan->deadline ? date('d/m/Y', strtotime($tagihan->deadline)) : '<span class="text-muted">-</span>'; ?>
                                        </td>
                                        <td>
                                            <?php if ($tagihan->is_aktif == 1): ?>
                                                <span class="badge badge-success">Aktif</span>
                                            <?php else: ?>
                                                <span class="badge badge-secondary">Tidak Aktif</span>
                                            <?php endif; ?>
                                        </td>
                                        <td><?php echo htmlspecialchars($tagihan->created_by_name); ?></td>
                                        <td><?php echo date('d/m/Y H:i', strtotime($tagihan->created_at)); ?></td>
                                        <td>
                                            <a href="<?php echo site_url('Tagihan_berkas_sekprodi/detail/' . $tagihan->id); ?>" 
                                               class="btn btn-info btn-sm" title="Detail">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <a href="<?php echo site_url('Tagihan_berkas_sekprodi/edit/' . $tagihan->id); ?>" 
                                               class="btn btn-warning btn-sm" title="Edit">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <a href="<?php echo site_url('Tagihan_berkas_sekprodi/delete/' . $tagihan->id); ?>" 
                                               class="btn btn-danger btn-sm" 
                                               onclick="return confirm('Yakin ingin menghapus tagihan ini?');" 
                                               title="Hapus">
                                                <i class="fas fa-trash"></i>
                                            </a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

