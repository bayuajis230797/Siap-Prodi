<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Mapping Mata Kuliah - Dosen</h3>
                <div class="card-tools">
                    <a href="<?php echo site_url('Mapping_mk/export_excel'); ?>" class="btn btn-info btn-sm">
                        <i class="fas fa-download"></i> Download Excel
                    </a>
                    <a href="<?php echo site_url('Mapping_mk/import_excel'); ?>" class="btn btn-success btn-sm">
                        <i class="fas fa-file-excel"></i> Import Excel
                    </a>
                    <a href="<?php echo site_url('Mapping_mk/create'); ?>" class="btn btn-primary btn-sm">
                        <i class="fas fa-plus"></i> Tambah Mapping
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

                <?php if (isset($tahun_aktif) && $tahun_aktif): ?>
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle"></i> 
                        <strong>Tahun Akademik Aktif:</strong> <?php echo $tahun_aktif->tahun_akademik . ' ' . ucfirst($tahun_aktif->semester); ?>
                        <br><small>Mapping yang ditampilkan adalah untuk mata kuliah aktif pada tahun akademik ini.</small>
                    </div>
                <?php else: ?>
                    <div class="alert alert-warning">
                        <i class="fas fa-exclamation-triangle"></i> 
                        <strong>Perhatian:</strong> Belum ada tahun akademik yang aktif. Aktifkan tahun akademik terlebih dahulu.
                    </div>
                <?php endif; ?>
                
                <!-- Search Box dan Show Entries -->
                <div class="row mb-3">
                    <div class="col-md-4">
                        <div class="input-group">
                            <div class="input-group-prepend">
                                <span class="input-group-text"><i class="fas fa-search"></i></span>
                            </div>
                            <input type="text" id="searchMapping" class="form-control" placeholder="Cari Kode MK, Nama MK, Dosen...">
                        </div>
                    </div>
                    <div class="col-md-4 offset-md-4">
                        <form method="get" action="<?php echo site_url('Mapping_mk/index'); ?>" id="formPerPageMapping" class="form-inline justify-content-end">
                            <label for="per_page_mapping" class="mr-2">Tampilkan:</label>
                            <select name="per_page" id="per_page_mapping" class="form-control form-control-sm" onchange="document.getElementById('formPerPageMapping').submit();">
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
                    <table class="table table-bordered table-striped table-hover" id="tableMapping">
                        <thead>
                            <tr>
                                <th width="5%">No</th>
                                <th width="12%">Kode MK</th>
                                <th>Nama Mata Kuliah</th>
                                <th width="8%" class="text-center">SKS</th>
                                <th width="10%" class="text-center">Semester</th>
                                <th width="8%" class="text-center">Kelas</th>
                                <th width="15%">Kurikulum</th>
                                <th>Dosen Pengampu</th>
                                <th width="12%" class="text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($mapping)): ?>
                                <tr>
                                    <td colspan="9" class="text-center text-muted">
                                        <i class="fas fa-inbox"></i> Belum ada mapping dosen dengan mata kuliah.
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php 
                                $no = 1; 
                                $page = ($this->uri->segment(3)) ? $this->uri->segment(3) : 1;
                                $no = ($page - 1) * 10 + 1;
                                foreach ($mapping as $m): 
                                ?>
                                    <tr>
                                        <td><?php echo $no++; ?></td>
                                        <td><strong><?php echo $m->kode_mk; ?></strong></td>
                                        <td><?php echo $m->nama_mk; ?></td>
                                        <td class="text-center">
                                            <span class="badge badge-info"><?php echo $m->sks; ?> SKS</span>
                                        </td>
                                        <td class="text-center">
                                            <span class="badge badge-primary">Semester <?php echo $m->semester_aktif; ?></span>
                                        </td>
                                        <td class="text-center">
                                            <?php if (isset($m->kelas) && !empty($m->kelas)): ?>
                                                <span class="badge badge-info"><?php echo $m->kelas; ?></span>
                                            <?php else: ?>
                                                <span class="badge badge-secondary">-</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <small>
                                                <strong><?php echo $m->nama_kurikulum; ?></strong><br>
                                                <span class="text-muted">Tahun <?php echo $m->tahun_berlaku; ?></span>
                                            </small>
                                        </td>
                                        <td>
                                            <strong><?php echo $m->nama_dosen; ?></strong><br>
                                            <small class="text-muted">NIDN: <?php echo $m->nidn; ?></small>
                                        </td>
                                        <td class="text-center">
                                            <a href="<?php echo site_url('Mapping_mk/edit/' . $m->id); ?>" class="btn btn-warning btn-sm">
                                                <i class="fas fa-edit"></i> Edit
                                            </a>
                                            <a href="<?php echo site_url('Mapping_mk/delete/' . $m->id); ?>" 
                                               class="btn btn-danger btn-sm" 
                                               onclick="return confirm('Hapus mapping ini?')">
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
                                Menampilkan <?php echo count($mapping); ?> dari <?php echo $total_rows; ?> data
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
    const searchInput = document.getElementById('searchMapping');
    if (searchInput) {
        const table = document.getElementById('tableMapping');
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

