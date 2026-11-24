<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Daftar Ruang Kelas</h3>
                <div class="card-tools">
                    <a href="<?php echo site_url('Ruang_kelas/import'); ?>" class="btn btn-success btn-sm">
                        <i class="fas fa-file-excel"></i> Import Excel
                    </a>
                    <a href="<?php echo site_url('Ruang_kelas/create'); ?>" class="btn btn-primary btn-sm">
                        <i class="fas fa-plus"></i> Tambah Ruang Kelas
                    </a>
                </div>
            </div>
            <div class="card-body">
                <div class="alert alert-info">
                    <i class="fas fa-info-circle"></i> 
                    <strong>Informasi:</strong> 
                    <ul class="mb-0 mt-2">
                        <li>Ruang kelas ini akan digunakan untuk setting jadwal kuliah, UTS, UAS, dan ujian PKL/Skripsi</li>
                        <li>Pastikan kode ruang unik untuk setiap ruang</li>
                    </ul>
                </div>
                
                <!-- Search Box dan Show Entries -->
                <div class="row mb-3">
                    <div class="col-md-4">
                        <div class="input-group">
                            <div class="input-group-prepend">
                                <span class="input-group-text"><i class="fas fa-search"></i></span>
                            </div>
                            <input type="text" id="searchRuangKelas" class="form-control" placeholder="Cari Kode Ruang, Nama Ruang...">
                        </div>
                    </div>
                    <div class="col-md-4 offset-md-4">
                        <form method="get" action="<?php echo site_url('Ruang_kelas/index'); ?>" id="formPerPageRuangKelas" class="form-inline justify-content-end">
                            <label for="per_page_ruang_kelas" class="mr-2">Tampilkan:</label>
                            <select name="per_page" id="per_page_ruang_kelas" class="form-control form-control-sm" onchange="document.getElementById('formPerPageRuangKelas').submit();">
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
                    <table class="table table-bordered table-striped table-hover" id="tableRuangKelas">
                        <thead>
                            <tr>
                                <th width="5%">No</th>
                                <th width="12%">Kode Ruang</th>
                                <th>Nama Ruang</th>
                                <th width="10%" class="text-center">Kapasitas</th>
                                <th width="15%">Jenis Ruang</th>
                                <th width="15%">Lokasi</th>
                                <th width="10%" class="text-center">Status</th>
                                <th width="15%" class="text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($ruang_kelas)): ?>
                                <tr>
                                    <td colspan="8" class="text-center text-muted">
                                        <i class="fas fa-inbox"></i> Belum ada data ruang kelas.
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php 
                                $no = 1; 
                                $page = ($this->uri->segment(3)) ? $this->uri->segment(3) : 1;
                                $no = ($page - 1) * 10 + 1;
                                foreach ($ruang_kelas as $r): 
                                ?>
                                    <tr>
                                        <td><?php echo $no++; ?></td>
                                        <td><strong><?php echo $r->kode_ruang; ?></strong></td>
                                        <td><?php echo $r->nama_ruang; ?></td>
                                        <td class="text-center"><?php echo $r->kapasitas; ?> orang</td>
                                        <td>
                                            <span class="badge badge-info">
                                                <?php echo $r->jenis_ruang; ?>
                                            </span>
                                        </td>
                                        <td><?php echo $r->lokasi ? $r->lokasi : '-'; ?></td>
                                        <td class="text-center">
                                            <?php
                                            $badge_class = 'success';
                                            if ($r->status == 'nonaktif') {
                                                $badge_class = 'secondary';
                                            } elseif ($r->status == 'maintenance') {
                                                $badge_class = 'warning';
                                            }
                                            ?>
                                            <span class="badge badge-<?php echo $badge_class; ?>">
                                                <?php echo ucfirst($r->status); ?>
                                            </span>
                                        </td>
                                        <td class="text-center">
                                            <a href="<?php echo site_url('Ruang_kelas/edit/' . $r->id); ?>" class="btn btn-warning btn-sm">
                                                <i class="fas fa-edit"></i> Edit
                                            </a>
                                            <a href="<?php echo site_url('Ruang_kelas/delete/' . $r->id); ?>" 
                                               class="btn btn-danger btn-sm" 
                                               onclick="return confirm('Hapus ruang kelas ini?')">
                                                <i class="fas fa-trash"></i> Hapus
                                            </a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
                
                <?php if (isset($pagination) && !empty($pagination)): ?>
                    <div class="row mt-3">
                        <div class="col-md-6">
                            <p class="text-muted">
                                Menampilkan <?php echo count($ruang_kelas); ?> dari <?php echo $total_rows; ?> data
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
    const searchInput = document.getElementById('searchRuangKelas');
    const table = document.getElementById('tableRuangKelas');
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




