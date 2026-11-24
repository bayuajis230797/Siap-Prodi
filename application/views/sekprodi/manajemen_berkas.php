<div class="row">
    <div class="col-12">
        <div class="card card-primary card-outline">
            <div class="card-header">
                <h3 class="card-title">Manajemen Berkas</h3>
                <div class="card-tools">
                    <a href="<?php echo site_url('Manajemen_berkas/create'); ?>" class="btn btn-primary btn-sm">
                        <i class="fas fa-plus"></i> Upload Berkas
                    </a>
                </div>
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

                <!-- Filter -->
                <div class="row mb-3">
                    <div class="col-md-4">
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
                    <div class="col-md-4">
                        <form method="get" action="<?php echo site_url('Manajemen_berkas'); ?>">
                            <?php if ($kategori_filter): ?>
                                <input type="hidden" name="kategori" value="<?php echo htmlspecialchars($kategori_filter); ?>">
                            <?php endif; ?>
                            <div class="input-group">
                                <select name="is_active" class="form-control">
                                    <option value="">-- Semua Status --</option>
                                    <option value="1" <?php echo ($is_active_filter === '1') ? 'selected' : ''; ?>>Aktif</option>
                                    <option value="0" <?php echo ($is_active_filter === '0') ? 'selected' : ''; ?>>Tidak Aktif</option>
                                </select>
                                <div class="input-group-append">
                                    <button type="submit" class="btn btn-info">
                                        <i class="fas fa-filter"></i> Filter
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                    <div class="col-md-4 text-right">
                        <a href="<?php echo site_url('Manajemen_berkas'); ?>" class="btn btn-secondary">
                            <i class="fas fa-redo"></i> Reset Filter
                        </a>
                    </div>
                </div>

                <!-- Search Box dan Show Entries -->
                <div class="row mb-3">
                    <div class="col-md-4">
                        <div class="input-group">
                            <div class="input-group-prepend">
                                <span class="input-group-text"><i class="fas fa-search"></i></span>
                            </div>
                            <input type="text" id="searchBerkas" class="form-control" placeholder="Cari Judul, Kategori...">
                        </div>
                    </div>
                    <div class="col-md-4 offset-md-4">
                        <form method="get" action="<?php echo site_url('Manajemen_berkas'); ?>" id="formPerPageBerkas" class="form-inline justify-content-end">
                            <?php if ($kategori_filter): ?>
                                <input type="hidden" name="kategori" value="<?php echo htmlspecialchars($kategori_filter); ?>">
                            <?php endif; ?>
                            <?php if ($is_active_filter !== null): ?>
                                <input type="hidden" name="is_active" value="<?php echo (int)$is_active_filter; ?>">
                            <?php endif; ?>
                            <label for="per_page_berkas" class="mr-2">Tampilkan:</label>
                            <select name="per_page" id="per_page_berkas" class="form-control form-control-sm" onchange="document.getElementById('formPerPageBerkas').submit();">
                                <option value="10" <?php echo (isset($per_page) && $per_page == 10) ? 'selected' : ''; ?>>10</option>
                                <option value="25" <?php echo (isset($per_page) && $per_page == 25) ? 'selected' : ''; ?>>25</option>
                                <option value="50" <?php echo (isset($per_page) && $per_page == 50) ? 'selected' : ''; ?>>50</option>
                                <option value="100" <?php echo (isset($per_page) && $per_page == 100) ? 'selected' : ''; ?>>100</option>
                            </select>
                            <span class="ml-2">data</span>
                        </form>
                    </div>
                </div>

                <!-- Table -->
                <div class="table-responsive">
                    <table class="table table-bordered table-striped table-hover" id="tableBerkas">
                        <thead>
                            <tr>
                                <th width="5%">No</th>
                                <th width="30%">Judul</th>
                                <th width="15%">Kategori</th>
                                <th width="25%">File</th>
                                <th width="10%">Status</th>
                                <th width="15%">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($berkas_list)): ?>
                                <tr>
                                    <td colspan="6" class="text-center">Tidak ada data berkas.</td>
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
                                            <?php if ($berkas->is_active == 1): ?>
                                                <span class="badge badge-success">Aktif</span>
                                            <?php else: ?>
                                                <span class="badge badge-secondary">Tidak Aktif</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <div class="btn-group btn-group-sm">
                                                <?php if (!empty($berkas->file_path) && strpos($berkas->file_path, 'http') === 0): ?>
                                                    <a href="<?php echo htmlspecialchars($berkas->file_path); ?>" 
                                                       target="_blank" 
                                                       class="btn btn-info" title="Buka Link">
                                                        <i class="fas fa-external-link-alt"></i>
                                                    </a>
                                                <?php else: ?>
                                                    <a href="<?php echo site_url('Manajemen_berkas/download/' . $berkas->id); ?>" 
                                                       class="btn btn-info" title="Download">
                                                        <i class="fas fa-download"></i>
                                                    </a>
                                                <?php endif; ?>
                                                <a href="<?php echo site_url('Manajemen_berkas/edit/' . $berkas->id); ?>" 
                                                   class="btn btn-warning" title="Edit">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                <a href="<?php echo site_url('Manajemen_berkas/toggle_active/' . $berkas->id); ?>" 
                                                   class="btn btn-<?php echo $berkas->is_active == 1 ? 'secondary' : 'success'; ?>" 
                                                   title="<?php echo $berkas->is_active == 1 ? 'Nonaktifkan' : 'Aktifkan'; ?>"
                                                   onclick="return confirm('Apakah Anda yakin ingin mengubah status berkas ini?');">
                                                    <i class="fas fa-<?php echo $berkas->is_active == 1 ? 'eye-slash' : 'eye'; ?>"></i>
                                                </a>
                                                <a href="<?php echo site_url('Manajemen_berkas/delete/' . $berkas->id); ?>" 
                                                   class="btn btn-danger" title="Hapus"
                                                   onclick="return confirm('Apakah Anda yakin ingin menghapus berkas ini?');">
                                                    <i class="fas fa-trash"></i>
                                                </a>
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

<script>
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('searchBerkas');
    if (searchInput) {
        const table = document.getElementById('tableBerkas');
        const tbody = table.querySelector('tbody');
        const rows = tbody.querySelectorAll('tr');
        
        searchInput.addEventListener('keyup', function() {
            const searchText = this.value.toLowerCase();
            
            rows.forEach(function(row) {
                const text = row.textContent.toLowerCase();
                if (text.includes(searchText)) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            });
        });
    }
});
</script>


