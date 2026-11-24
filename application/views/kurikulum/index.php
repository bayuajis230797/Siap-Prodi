<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Daftar Kurikulum</h3>
                <div class="card-tools">
                    <?php if (get_user_role() !== 'kaprodi'): ?>
                    <a href="<?php echo site_url('Kurikulum/create'); ?>" class="btn btn-primary btn-sm">
                        <i class="fas fa-plus"></i> Tambah Kurikulum
                    </a>
                    <?php endif; ?>
                </div>
            </div>
            <div class="card-body">
                <div class="alert alert-info">
                    <i class="fas fa-info-circle"></i> 
                    <strong>Informasi:</strong> Setiap kurikulum dapat diaktifkan secara bersamaan karena setiap angkatan menggunakan kurikulum yang berbeda. 
                    Untuk mengatur mapping mata kuliah (kode_mk, nama_mk, sks, semester, is_wajib), klik tombol <strong>Mapping MK</strong>.
                </div>
                
                <!-- Search Box dan Show Entries -->
                <div class="row mb-3">
                    <div class="col-md-4">
                        <div class="input-group">
                            <div class="input-group-prepend">
                                <span class="input-group-text"><i class="fas fa-search"></i></span>
                            </div>
                            <input type="text" id="searchKurikulum" class="form-control" placeholder="Cari Nama Kurikulum, Tahun...">
                        </div>
                    </div>
                    <div class="col-md-4 offset-md-4">
                        <form method="get" action="<?php echo site_url('Kurikulum/index'); ?>" id="formPerPageKurikulum" class="form-inline justify-content-end">
                            <label for="per_page_kurikulum" class="mr-2">Tampilkan:</label>
                            <select name="per_page" id="per_page_kurikulum" class="form-control form-control-sm" onchange="document.getElementById('formPerPageKurikulum').submit();">
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
                    <table class="table table-bordered table-striped" id="tableKurikulum">
                        <thead>
                            <tr>
                                <th width="5%">No</th>
                                <th width="10%">ID</th>
                                <th>Nama Kurikulum</th>
                                <th width="12%">Tahun Berlaku</th>
                                <th width="10%">Status</th>
                                <th width="25%">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($kurikulum)): ?>
                                <tr>
                                    <td colspan="6" class="text-center">Tidak ada data kurikulum.</td>
                                </tr>
                            <?php else: ?>
                                <?php $no = 1; foreach ($kurikulum as $k): ?>
                                    <tr>
                                        <td><?php echo $no++; ?></td>
                                        <td><strong class="text-primary"><?php echo $k->id; ?></strong></td>
                                        <td><?php echo $k->nama_kurikulum; ?></td>
                                        <td><?php echo $k->tahun_berlaku; ?></td>
                                        <td>
                                            <?php if ($k->is_aktif): ?>
                                                <span class="badge badge-success">Aktif</span>
                                            <?php else: ?>
                                                <span class="badge badge-secondary">Tidak Aktif</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <a href="<?php echo site_url('Kurikulum/mapping_mk/' . $k->id); ?>" class="btn btn-info btn-sm">
                                                <i class="fas fa-map"></i> <?php echo get_user_role() === 'kaprodi' ? 'Lihat Struktur MK' : 'Mapping MK'; ?>
                                            </a>
                                            <a href="<?php echo site_url('Kurikulum/download/' . $k->id); ?>" class="btn btn-success btn-sm" title="Download Kurikulum">
                                                <i class="fas fa-download"></i> Download
                                            </a>
                                            <?php if (get_user_role() !== 'kaprodi'): ?>
                                            <a href="<?php echo site_url('Kurikulum/toggle_aktif/' . $k->id); ?>" 
                                               class="btn btn-<?php echo $k->is_aktif ? 'warning' : 'success'; ?> btn-sm" 
                                               onclick="return confirm('<?php echo $k->is_aktif ? 'Nonaktifkan' : 'Aktifkan'; ?> kurikulum ini?')">
                                                <i class="fas fa-<?php echo $k->is_aktif ? 'times' : 'check'; ?>"></i> 
                                                <?php echo $k->is_aktif ? 'Nonaktifkan' : 'Aktifkan'; ?>
                                            </a>
                                            <a href="<?php echo site_url('Kurikulum/delete/' . $k->id); ?>" class="btn btn-danger btn-sm" onclick="return confirm('Hapus kurikulum ini?')">
                                                <i class="fas fa-trash"></i> Hapus
                                            </a>
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
                                Menampilkan <?php echo count($kurikulum); ?> dari <?php echo isset($total_rows) ? $total_rows : count($kurikulum); ?> data
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
    const searchInput = document.getElementById('searchKurikulum');
    const table = document.getElementById('tableKurikulum');
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

