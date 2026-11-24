<div class="row">
    <div class="col-12">
        <div class="card card-primary card-outline">
            <div class="card-header">
                <h3 class="card-title">Manajemen Berkas</h3>
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

                <div class="alert alert-info">
                    <i class="fas fa-info-circle"></i> 
                    Berikut adalah berkas-berkas yang diupload oleh Sekprodi. Anda dapat melihat atau mengunduh berkas yang tersedia.
                </div>

                <!-- Filter -->
                <div class="row mb-3">
                    <div class="col-md-6">
                        <form method="get" action="<?php echo site_url('Manajemen_berkas'); ?>">
                            <div class="input-group">
                                <select name="kategori" class="form-control">
                                    <option value="">-- Semua Kategori --</option>
                                    <?php if (!empty($kategori_list)): ?>
                                        <?php foreach ($kategori_list as $kat): ?>
                                            <option value="<?php echo htmlspecialchars($kat->kategori); ?>" 
                                                    <?php echo ($kategori_filter == $kat->kategori) ? 'selected' : ''; ?>>
                                                <?php echo htmlspecialchars($kat->kategori); ?>
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
                    <div class="col-md-6 text-right">
                        <a href="<?php echo site_url('Manajemen_berkas'); ?>" class="btn btn-secondary">
                            <i class="fas fa-redo"></i> Reset Filter
                        </a>
                    </div>
                </div>

                <!-- Table -->
                <div class="table-responsive">
                    <table class="table table-bordered table-striped table-hover">
                        <thead>
                            <tr>
                                <th width="5%">No</th>
                                <th width="35%">Judul</th>
                                <th width="15%">Kategori</th>
                                <th width="30%">File</th>
                                <th width="15%">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($berkas_list)): ?>
                                <tr>
                                    <td colspan="5" class="text-center">Tidak ada berkas yang tersedia.</td>
                                </tr>
                            <?php else: ?>
                                <?php $no = 1; ?>
                                <?php foreach ($berkas_list as $berkas): ?>
                                    <tr>
                                        <td><?php echo $no++; ?></td>
                                        <td>
                                            <strong><?php echo htmlspecialchars($berkas->judul); ?></strong>
                                            <?php if (!empty($berkas->deskripsi)): ?>
                                                <br><small class="text-muted"><?php echo nl2br(htmlspecialchars($berkas->deskripsi)); ?></small>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php if ($berkas->kategori): ?>
                                                <span class="badge badge-info"><?php echo htmlspecialchars($berkas->kategori); ?></span>
                                            <?php else: ?>
                                                <span class="text-muted">-</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php if (!empty($berkas->file_path)): ?>
                                                <?php if (strpos($berkas->file_path, 'http') === 0): ?>
                                                    <!-- Link Google Drive -->
                                                    <a href="<?php echo htmlspecialchars($berkas->file_path); ?>" target="_blank" class="text-primary">
                                                        <i class="fas fa-external-link-alt"></i> 
                                                        <strong><?php echo htmlspecialchars($berkas->file_name); ?></strong>
                                                    </a>
                                                    <br><small class="text-muted">Link Google Drive</small>
                                                <?php else: ?>
                                                    <!-- File lokal (backward compatibility) -->
                                                    <i class="fas fa-file"></i> 
                                                    <?php echo htmlspecialchars($berkas->file_name); ?>
                                                <?php endif; ?>
                                            <?php else: ?>
                                                <span class="text-muted">-</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <div class="btn-group btn-group-sm">
                                                <?php if (!empty($berkas->file_path) && strpos($berkas->file_path, 'http') === 0): ?>
                                                    <!-- Link Google Drive -->
                                                    <a href="<?php echo htmlspecialchars($berkas->file_path); ?>" 
                                                       target="_blank" 
                                                       class="btn btn-primary" title="Buka Link">
                                                        <i class="fas fa-external-link-alt"></i> Buka
                                                    </a>
                                                <?php else: ?>
                                                    <!-- File lokal (backward compatibility) -->
                                                    <?php 
                                                    $file_ext = strtolower(pathinfo($berkas->file_path, PATHINFO_EXTENSION));
                                                    if ($file_ext === 'pdf'): 
                                                    ?>
                                                        <a href="<?php echo site_url('Manajemen_berkas/view/' . $berkas->id); ?>" 
                                                           class="btn btn-primary" title="Lihat" target="_blank">
                                                            <i class="fas fa-eye"></i> Lihat
                                                        </a>
                                                    <?php endif; ?>
                                                    <a href="<?php echo site_url('Manajemen_berkas/download/' . $berkas->id); ?>" 
                                                       class="btn btn-success" title="Download">
                                                        <i class="fas fa-download"></i> Download
                                                    </a>
                                                <?php endif; ?>
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











