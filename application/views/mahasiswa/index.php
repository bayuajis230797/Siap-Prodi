<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Daftar Mahasiswa</h3>
                <div class="card-tools">
                    <a href="<?php echo site_url('Mahasiswa/create'); ?>" class="btn btn-primary btn-sm">
                        <i class="fas fa-plus"></i> Tambah Mahasiswa
                    </a>
                    <a href="<?php echo site_url('Mahasiswa/import'); ?>" class="btn btn-success btn-sm">
                        <i class="fas fa-file-excel"></i> Import Excel
                    </a>
                    <a href="<?php echo site_url('Mahasiswa/download_template'); ?>" class="btn btn-info btn-sm">
                        <i class="fas fa-download"></i> Download Template
                    </a>
                    <div class="btn-group ml-2">
                        <button type="button" class="btn btn-warning btn-sm dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                            <i class="fas fa-file-excel"></i> Download Per Kelas
                        </button>
                        <div class="dropdown-menu dropdown-menu-right" style="max-height: 300px; overflow-y: auto;">
                            <?php if (empty($daftar_kelas)): ?>
                                <a class="dropdown-item text-muted" href="#">
                                    <i class="fas fa-info-circle"></i> Belum ada kelas
                                </a>
                            <?php else: ?>
                                <?php foreach ($daftar_kelas as $kelas_item): ?>
                                    <a class="dropdown-item" href="<?php echo site_url('Mahasiswa/download_per_kelas?kelas=' . urlencode($kelas_item->kelas)); ?>">
                                        <i class="fas fa-users"></i> Kelas <?php echo htmlspecialchars($kelas_item->kelas); ?> 
                                        <span class="badge badge-secondary float-right"><?php echo $kelas_item->jumlah; ?> mahasiswa</span>
                                    </a>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
            <div class="card-body">
                <div class="alert alert-info">
                    <i class="fas fa-info-circle"></i> 
                    <strong>Informasi:</strong> 
                    <ul class="mb-0 mt-2">
                        <li>Username dan password default untuk login mahasiswa adalah <strong>NIM</strong></li>
                        <li>Semester aktif akan otomatis dihitung berdasarkan angkatan dan tahun akademik aktif</li>
                        <li>User account akan otomatis dibuat saat menambahkan mahasiswa baru</li>
                    </ul>
                </div>
                
                <!-- Search Box dan Show Entries -->
                <div class="row mb-3">
                    <div class="col-md-4">
                        <div class="input-group">
                            <div class="input-group-prepend">
                                <span class="input-group-text"><i class="fas fa-search"></i></span>
                            </div>
                            <input type="text" id="searchMahasiswa" class="form-control" placeholder="Cari NIM, Nama, Kelas...">
                        </div>
                    </div>
                    <div class="col-md-4 offset-md-4">
                        <form method="get" action="<?php echo site_url('Mahasiswa/index'); ?>" id="formPerPageMahasiswa" class="form-inline justify-content-end">
                            <label for="per_page_mahasiswa" class="mr-2">Tampilkan:</label>
                            <select name="per_page" id="per_page_mahasiswa" class="form-control form-control-sm" onchange="document.getElementById('formPerPageMahasiswa').submit();">
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
                    <table class="table table-bordered table-striped table-hover" id="tableMahasiswa">
                        <thead>
                            <tr>
                                <th width="5%">No</th>
                                <th width="12%">NIM</th>
                                <th>Nama Mahasiswa</th>
                                <th width="8%" class="text-center">Angkatan</th>
                                <th width="8%" class="text-center">Kelas</th>
                                <th width="10%" class="text-center">Semester Aktif</th>
                                <th width="15%">Kurikulum</th>
                                <th width="10%" class="text-center">Status</th>
                                <th width="15%" class="text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($mahasiswa)): ?>
                                <tr>
                                    <td colspan="9" class="text-center text-muted">
                                        <i class="fas fa-inbox"></i> Belum ada data mahasiswa.
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php $no = 1; foreach ($mahasiswa as $m): ?>
                                    <tr>
                                        <td><?php echo $no++; ?></td>
                                        <td><strong><?php echo $m->nim; ?></strong></td>
                                        <td><?php echo $m->nama_mahasiswa; ?></td>
                                        <td class="text-center">
                                            <span class="badge badge-secondary"><?php echo $m->angkatan; ?></span>
                                        </td>
                                        <td class="text-center">
                                            <?php if ($m->kelas): ?>
                                                <span class="badge badge-info"><?php echo $m->kelas; ?></span>
                                            <?php else: ?>
                                                <span class="text-muted">-</span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="text-center">
                                            <span class="badge badge-primary">Semester <?php echo $m->semester_aktif; ?></span>
                                        </td>
                                        <td>
                                            <?php if ($m->nama_kurikulum): ?>
                                                <small>
                                                    <strong><?php echo $m->nama_kurikulum; ?></strong><br>
                                                    <span class="text-muted">Tahun <?php echo $m->tahun_berlaku; ?></span>
                                                </small>
                                            <?php else: ?>
                                                <span class="text-muted">-</span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="text-center">
                                            <?php
                                            $badge_class = 'secondary';
                                            if ($m->status == 'aktif') $badge_class = 'success';
                                            elseif ($m->status == 'lulus') $badge_class = 'info';
                                            elseif ($m->status == 'cuti') $badge_class = 'warning';
                                            ?>
                                            <span class="badge badge-<?php echo $badge_class; ?>">
                                                <?php echo ucfirst($m->status); ?>
                                            </span>
                                        </td>
                                        <td class="text-center">
                                            <a href="<?php echo site_url('Mahasiswa/edit/' . $m->id); ?>" class="btn btn-warning btn-sm">
                                                <i class="fas fa-edit"></i> Edit
                                            </a>
                                            <a href="<?php echo site_url('Mahasiswa/delete/' . $m->id); ?>" 
                                               class="btn btn-danger btn-sm" 
                                               onclick="return confirm('Hapus mahasiswa ini? User account juga akan dihapus.')">
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
                                Menampilkan <?php echo count($mahasiswa); ?> dari <?php echo isset($total_rows) ? $total_rows : count($mahasiswa); ?> data
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
    const searchInput = document.getElementById('searchMahasiswa');
    const table = document.getElementById('tableMahasiswa');
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

