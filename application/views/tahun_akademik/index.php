<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Daftar Tahun Akademik</h3>
                <div class="card-tools">
                    <a href="<?php echo site_url('Tahun_akademik/riwayat'); ?>" class="btn btn-outline-secondary btn-sm">
                        <i class="fas fa-history"></i> Riwayat Kenaikan Kelas
                    </a>
                    <a href="<?php echo site_url('Tahun_akademik/create'); ?>" class="btn btn-primary btn-sm">
                        <i class="fas fa-plus"></i> Tambah Tahun Akademik
                    </a>
                </div>
            </div>
            <div class="card-body">
                <!-- Search Box dan Show Entries -->
                <div class="row mb-3">
                    <div class="col-md-4">
                        <div class="input-group">
                            <div class="input-group-prepend">
                                <span class="input-group-text"><i class="fas fa-search"></i></span>
                            </div>
                            <input type="text" id="searchTahunAkademik" class="form-control" placeholder="Cari Tahun Akademik, Semester...">
                        </div>
                    </div>
                    <div class="col-md-4 offset-md-4">
                        <form method="get" action="<?php echo site_url('Tahun_akademik/index'); ?>" id="formPerPageTahunAkademik" class="form-inline justify-content-end">
                            <label for="per_page_tahun_akademik" class="mr-2">Tampilkan:</label>
                            <select name="per_page" id="per_page_tahun_akademik" class="form-control form-control-sm" onchange="document.getElementById('formPerPageTahunAkademik').submit();">
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
                    <table class="table table-bordered table-striped" id="tableTahunAkademik">
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>Tahun Akademik</th>
                                <th>Semester</th>
                                <th>Tanggal Mulai</th>
                                <th>Tanggal Selesai</th>
                                <th>Status</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($tahun_akademik)): ?>
                                <tr>
                                    <td colspan="7" class="text-center">Tidak ada data tahun akademik.</td>
                                </tr>
                            <?php else: ?>
                                <?php $no = 1; foreach ($tahun_akademik as $ta): ?>
                                    <tr>
                                        <td><?php echo $no++; ?></td>
                                        <td><?php echo $ta->tahun_akademik; ?></td>
                                        <td><?php echo ucfirst($ta->semester); ?></td>
                                        <td><?php echo $ta->tgl_mulai ? date('d/m/Y', strtotime($ta->tgl_mulai)) : '-'; ?></td>
                                        <td><?php echo $ta->tgl_selesai ? date('d/m/Y', strtotime($ta->tgl_selesai)) : '-'; ?></td>
                                        <td>
                                            <?php if ($ta->is_aktif): ?>
                                                <span class="badge badge-success">Aktif</span>
                                            <?php else: ?>
                                                <span class="badge badge-secondary">Tidak Aktif</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <a href="<?php echo site_url('Tahun_akademik/detail/' . $ta->id); ?>" class="btn btn-info btn-sm">
                                                <i class="fas fa-eye"></i> Detail
                                            </a>
                                            <?php if (!$ta->is_aktif): ?>
                                                <a href="<?php echo site_url('Tahun_akademik/pratinjau/' . $ta->id); ?>" 
                                                   class="btn btn-success btn-sm" 
                                                   title="Lihat pratinjau dampak sebelum mengaktifkan">
                                                    <i class="fas fa-check"></i> Aktifkan
                                                </a>
                                            <?php endif; ?>
                                            <?php if (!$ta->is_aktif): ?>
                                                <a href="<?php echo site_url('Tahun_akademik/delete/' . $ta->id); ?>" 
                                                   class="btn btn-danger btn-sm" 
                                                   onclick="return confirm('Hapus tahun akademik ini?')">
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
                                Menampilkan <?php echo count($tahun_akademik); ?> dari <?php echo isset($total_rows) ? $total_rows : count($tahun_akademik); ?> data
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

<div class="alert alert-warning">
    <i class="fas fa-exclamation-triangle"></i> 
    <strong>Perhatian:</strong> Ketika mengaktifkan tahun akademik baru, sistem akan:
    <ul class="mb-0 mt-2">
        <li>Menaikkan semester untuk semua mahasiswa aktif yang semester aktifnya kurang dari 8</li>
        <li>Menentukan mata kuliah aktif untuk setiap kurikulum berdasarkan semester yang dihitung dari tahun akademik</li>
    </ul>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('searchTahunAkademik');
    const table = document.getElementById('tableTahunAkademik');
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

