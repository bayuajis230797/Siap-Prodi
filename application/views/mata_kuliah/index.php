<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Mata Kuliah Aktif</h3>
            </div>
            <div class="card-body">
                <?php if (isset($tahun_aktif) && $tahun_aktif): ?>
                    <div class="alert alert-success">
                        <i class="fas fa-check-circle"></i> 
                        <strong>Tahun Akademik Aktif:</strong> <?php echo $tahun_aktif->tahun_akademik . ' ' . ucfirst($tahun_aktif->semester); ?>
                        <br><small>Mata kuliah yang ditampilkan adalah mata kuliah aktif berdasarkan tahun akademik ini dari <strong>kurikulum yang statusnya aktif</strong> (di menu Kurikulum > Mapping MK dengan Status Aktif).</small>
                    </div>
                <?php else: ?>
                    <div class="alert alert-warning">
                        <i class="fas fa-exclamation-triangle"></i> 
                        <strong>Perhatian:</strong> Belum ada tahun akademik yang aktif. Aktifkan tahun akademik terlebih dahulu untuk melihat mata kuliah aktif.
                    </div>
                <?php endif; ?>
                
                <!-- Search Box dan Show Entries -->
                <div class="row mb-3">
                    <div class="col-md-4">
                        <div class="input-group">
                            <div class="input-group-prepend">
                                <span class="input-group-text"><i class="fas fa-search"></i></span>
                            </div>
                            <input type="text" id="searchMataKuliah" class="form-control" placeholder="Cari Kode MK, Nama MK...">
                        </div>
                    </div>
                    <div class="col-md-4 offset-md-4">
                        <form method="get" action="<?php echo site_url('Mata_kuliah/index'); ?>" id="formPerPageMataKuliah" class="form-inline justify-content-end">
                            <label for="per_page_mata_kuliah" class="mr-2">Tampilkan:</label>
                            <select name="per_page" id="per_page_mata_kuliah" class="form-control form-control-sm" onchange="document.getElementById('formPerPageMataKuliah').submit();">
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
                    <table class="table table-bordered table-striped table-hover" id="tableMataKuliah">
                        <thead>
                            <tr>
                                <th width="5%">No</th>
                                <th width="12%">Kode MK</th>
                                <th>Nama Mata Kuliah</th>
                                <th width="8%" class="text-center">SKS</th>
                                <th width="10%" class="text-center">Semester</th>
                                <th width="20%">Kurikulum</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($mata_kuliah)): ?>
                                <tr>
                                    <td colspan="6" class="text-center text-muted">
                                        <i class="fas fa-inbox"></i> 
                                        <?php if (isset($tahun_aktif) && $tahun_aktif): ?>
                                            Belum ada mata kuliah aktif untuk tahun akademik ini.
                                        <?php else: ?>
                                            Belum ada tahun akademik aktif.
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php $no = 1; foreach ($mata_kuliah as $mk): ?>
                                    <tr>
                                        <td><?php echo $no++; ?></td>
                                        <td><strong><?php echo $mk->kode_mk; ?></strong></td>
                                        <td><?php echo $mk->nama_mk; ?></td>
                                        <td class="text-center">
                                            <span class="badge badge-info"><?php echo $mk->sks; ?> SKS</span>
                                        </td>
                                        <td class="text-center">
                                            <span class="badge badge-primary">Semester <?php echo $mk->semester; ?></span>
                                        </td>
                                        <td>
                                            <small>
                                                <strong><?php echo $mk->nama_kurikulum; ?></strong><br>
                                                <span class="text-muted">Tahun <?php echo $mk->tahun_berlaku; ?></span>
                                            </small>
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
                                Menampilkan <?php echo count($mata_kuliah); ?> dari <?php echo isset($total_rows) ? $total_rows : count($mata_kuliah); ?> data
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
    const searchInput = document.getElementById('searchMataKuliah');
    const table = document.getElementById('tableMataKuliah');
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

