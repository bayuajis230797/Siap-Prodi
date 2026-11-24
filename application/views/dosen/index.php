<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Daftar Dosen</h3>
                <div class="card-tools">
                    <?php if (get_user_role() !== 'kaprodi'): ?>
                    <a href="<?php echo site_url('Dosen/create'); ?>" class="btn btn-primary btn-sm">
                        <i class="fas fa-plus"></i> Tambah Dosen
                    </a>
                    <a href="<?php echo site_url('Dosen/import'); ?>" class="btn btn-success btn-sm">
                        <i class="fas fa-file-excel"></i> Import Excel
                    </a>
                    <a href="<?php echo site_url('Dosen/download_template'); ?>" class="btn btn-info btn-sm">
                        <i class="fas fa-download"></i> Download Template
                    </a>
                    <?php endif; ?>
                </div>
            </div>
            <div class="card-body">
                <div class="alert alert-info">
                    <i class="fas fa-info-circle"></i> 
                    <strong>Informasi:</strong> 
                    <ul class="mb-0 mt-2">
                        <li>Username dan password default untuk login dosen adalah <strong>NIDN</strong></li>
                        <li>User account akan otomatis dibuat saat menambahkan dosen baru</li>
                    </ul>
                </div>
                
                <!-- Search Box dan Show Entries -->
                <div class="row mb-3">
                    <div class="col-md-4">
                        <div class="input-group">
                            <div class="input-group-prepend">
                                <span class="input-group-text"><i class="fas fa-search"></i></span>
                            </div>
                            <input type="text" id="searchDosen" class="form-control" placeholder="Cari NIDN, Nama, Email...">
                        </div>
                    </div>
                    <div class="col-md-4 offset-md-4">
                        <form method="get" action="<?php echo site_url('Dosen/index'); ?>" id="formPerPageDosen" class="form-inline justify-content-end">
                            <label for="per_page_dosen" class="mr-2">Tampilkan:</label>
                            <select name="per_page" id="per_page_dosen" class="form-control form-control-sm" onchange="document.getElementById('formPerPageDosen').submit();">
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
                    <table class="table table-bordered table-striped table-hover" id="tableDosen">
                        <thead>
                            <tr>
                                <th width="5%">No</th>
                                <th width="12%">NIDN</th>
                                <th>Nama Dosen</th>
                                <th width="15%">Email</th>
                                <th width="18%">Jabatan Fungsional</th>
                                <th width="10%" class="text-center">Status</th>
                                <th width="12%" class="text-center">Pembimbing Skripsi</th>
                                <th width="15%" class="text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($dosen)): ?>
                                <tr>
                                    <td colspan="8" class="text-center text-muted">
                                        <i class="fas fa-inbox"></i> Belum ada data dosen.
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php $no = 1; foreach ($dosen as $d): ?>
                                    <tr>
                                        <td><?php echo $no++; ?></td>
                                        <td><strong><?php echo $d->nidn; ?></strong></td>
                                        <td><?php echo $d->nama_dosen; ?></td>
                                        <td><?php echo $d->email ? $d->email : '-'; ?></td>
                                        <td>
                                            <span class="badge badge-info">
                                                <?php echo $d->jabatan_fungsional ? $d->jabatan_fungsional : 'Asisten Ahli'; ?>
                                            </span>
                                        </td>
                                        <td class="text-center">
                                            <?php
                                            $badge_class = $d->status == 'aktif' ? 'success' : 'secondary';
                                            ?>
                                            <span class="badge badge-<?php echo $badge_class; ?>">
                                                <?php echo ucfirst($d->status); ?>
                                            </span>
                                        </td>
                                        <td class="text-center">
                                            <?php
                                            $is_pembimbing = isset($d->is_pembimbing_skripsi) && $d->is_pembimbing_skripsi == 1;
                                            $badge_class_pembimbing = $is_pembimbing ? 'success' : 'secondary';
                                            $text_pembimbing = $is_pembimbing ? 'Ya' : 'Tidak';
                                            ?>
                                            <span class="badge badge-<?php echo $badge_class_pembimbing; ?>">
                                                <?php echo $text_pembimbing; ?>
                                            </span>
                                        </td>
                                        <td class="text-center">
                                            <?php if (get_user_role() !== 'kaprodi'): ?>
                                            <a href="<?php echo site_url('Dosen/edit/' . $d->id); ?>" class="btn btn-warning btn-sm">
                                                <i class="fas fa-edit"></i> Edit
                                            </a>
                                            <a href="<?php echo site_url('Dosen/delete/' . $d->id); ?>" 
                                               class="btn btn-danger btn-sm" 
                                               onclick="return confirm('Hapus dosen ini? User account juga akan dihapus.')">
                                                <i class="fas fa-trash"></i> Hapus
                                            </a>
                                            <?php else: ?>
                                            <span class="text-muted">—</span>
                                            <?php endif; ?>
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
                                Menampilkan <?php echo count($dosen); ?> dari <?php echo isset($total_rows) ? $total_rows : count($dosen); ?> data
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
    const searchInput = document.getElementById('searchDosen');
    const table = document.getElementById('tableDosen');
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






