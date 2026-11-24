<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Daftar Tenaga Kependidikan</h3>
                <div class="card-tools">
                    <a href="<?php echo site_url('Tendik/create'); ?>" class="btn btn-primary btn-sm">
                        <i class="fas fa-plus"></i> Tambah Tendik
                    </a>
                    <a href="<?php echo site_url('Tendik/import'); ?>" class="btn btn-success btn-sm">
                        <i class="fas fa-file-excel"></i> Import Excel
                    </a>
                    <a href="<?php echo site_url('Tendik/download_template'); ?>" class="btn btn-info btn-sm">
                        <i class="fas fa-download"></i> Download Template
                    </a>
                </div>
            </div>
            <div class="card-body">
                <div class="alert alert-info">
                    <i class="fas fa-info-circle"></i> 
                    <strong>Informasi:</strong> 
                    <ul class="mb-0 mt-2">
                        <li>Username dan password default untuk login tendik adalah <strong>NIPY</strong></li>
                        <li>User account akan otomatis dibuat saat menambahkan tendik baru</li>
                    </ul>
                </div>
                
                <!-- Search Box dan Show Entries -->
                <div class="row mb-3">
                    <div class="col-md-4">
                        <div class="input-group">
                            <div class="input-group-prepend">
                                <span class="input-group-text"><i class="fas fa-search"></i></span>
                            </div>
                            <input type="text" id="searchTendik" class="form-control" placeholder="Cari NIPY, Nama, Email...">
                        </div>
                    </div>
                    <div class="col-md-4 offset-md-4">
                        <form method="get" action="<?php echo site_url('Tendik/index'); ?>" id="formPerPageTendik" class="form-inline justify-content-end">
                            <label for="per_page_tendik" class="mr-2">Tampilkan:</label>
                            <select name="per_page" id="per_page_tendik" class="form-control form-control-sm" onchange="document.getElementById('formPerPageTendik').submit();">
                                <option value="10" <?php echo (isset($per_page) && $per_page == 10) ? 'selected' : ''; ?>>10</option>
                                <option value="25" <?php echo (isset($per_page) && $per_page == 25) ? 'selected' : ''; ?>>25</option>
                                <option value="50" <?php echo (isset($per_page) && $per_page == 50) ? 'selected' : ''; ?>>50</option>
                                <option value="100" <?php echo (isset($per_page) && $per_page == 100) ? 'selected' : ''; ?>>100</option>
                            </select>
                            <span class="ml-2">data</span>
                        </form>
                    </div>
                </div>
                
                <div class="table-responsive">
                    <table class="table table-bordered table-striped table-hover" id="tableTendik">
                        <thead>
                            <tr>
                                <th width="5%">No</th>
                                <th width="12%">NIPY</th>
                                <th>Nama Tendik</th>
                                <th width="15%">Email</th>
                                <th width="18%">Jabatan</th>
                                <th width="10%" class="text-center">Status</th>
                                <th width="15%" class="text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($tendik)): ?>
                                <tr>
                                    <td colspan="7" class="text-center text-muted">
                                        <i class="fas fa-inbox"></i> Belum ada data tenaga kependidikan.
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php $no = 1; foreach ($tendik as $t): ?>
                                    <tr>
                                        <td><?php echo $no++; ?></td>
                                        <td><strong><?php echo $t->nipy; ?></strong></td>
                                        <td><?php echo $t->nama_tendik; ?></td>
                                        <td><?php echo $t->email ? $t->email : '-'; ?></td>
                                        <td><?php echo $t->jabatan ? $t->jabatan : '-'; ?></td>
                                        <td class="text-center">
                                            <?php
                                            $badge_class = $t->status == 'aktif' ? 'success' : 'secondary';
                                            ?>
                                            <span class="badge badge-<?php echo $badge_class; ?>">
                                                <?php echo ucfirst($t->status); ?>
                                            </span>
                                        </td>
                                        <td class="text-center">
                                            <a href="<?php echo site_url('Tendik/edit/' . $t->id); ?>" class="btn btn-warning btn-sm">
                                                <i class="fas fa-edit"></i> Edit
                                            </a>
                                            <a href="<?php echo site_url('Tendik/delete/' . $t->id); ?>" 
                                               class="btn btn-danger btn-sm" 
                                               onclick="return confirm('Hapus tenaga kependidikan ini? User account juga akan dihapus.')">
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
                                Menampilkan <?php echo count($tendik); ?> dari <?php echo isset($total_rows) ? $total_rows : count($tendik); ?> data
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

<script>
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('searchTendik');
    const table = document.getElementById('tableTendik');
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
});
</script>







