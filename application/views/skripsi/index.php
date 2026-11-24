<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Skripsi</h3>
                <div class="card-tools">
                    <a href="<?php echo site_url('Skripsi/create'); ?>" class="btn btn-primary btn-sm">
                        <i class="fas fa-plus"></i> Tambah Mahasiswa Skripsi
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
                    </div>
                <?php else: ?>
                    <div class="alert alert-warning">
                        <i class="fas fa-exclamation-triangle"></i> 
                        <strong>Perhatian:</strong> Belum ada tahun akademik yang aktif. Aktifkan tahun akademik terlebih dahulu.
                    </div>
                <?php endif; ?>

                <!-- Filter Kelas -->
                <div class="row mb-3">
                    <div class="col-md-4">
                        <form method="get" action="<?php echo site_url('Skripsi/index'); ?>">
                            <div class="input-group">
                                <select name="kelas" class="form-control">
                                    <option value="">-- Semua Kelas --</option>
                                    <?php if (!empty($kelas_list)): ?>
                                        <?php foreach ($kelas_list as $k): ?>
                                            <option value="<?php echo $k->kelas; ?>" <?php echo ($kelas_filter == $k->kelas) ? 'selected' : ''; ?>>
                                                <?php echo $k->kelas; ?>
                                            </option>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </select>
                                <div class="input-group-append">
                                    <button type="submit" class="btn btn-info">
                                        <i class="fas fa-filter"></i> Filter
                                    </button>
                                    <?php if ($kelas_filter): ?>
                                        <a href="<?php echo site_url('Skripsi'); ?>" class="btn btn-secondary">
                                            <i class="fas fa-times"></i> Reset
                                        </a>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
                
                <!-- Search Box dan Show Entries -->
                <div class="row mb-3">
                    <div class="col-md-4">
                        <div class="input-group">
                            <div class="input-group-prepend">
                                <span class="input-group-text"><i class="fas fa-search"></i></span>
                            </div>
                            <input type="text" id="searchSkripsi" class="form-control" placeholder="Cari NIM, Nama, Kelas...">
                        </div>
                    </div>
                    <div class="col-md-4 offset-md-4">
                        <form method="get" action="<?php echo site_url('Skripsi/index'); ?>" id="formPerPageSkripsi" class="form-inline justify-content-end">
                            <?php if ($kelas_filter): ?>
                                <input type="hidden" name="kelas" value="<?php echo htmlspecialchars($kelas_filter); ?>">
                            <?php endif; ?>
                            <label for="per_page_skripsi" class="mr-2">Tampilkan:</label>
                            <select name="per_page" id="per_page_skripsi" class="form-control form-control-sm" onchange="document.getElementById('formPerPageSkripsi').submit();">
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
                    <table class="table table-bordered table-striped table-hover" id="tableSkripsi">
                        <thead>
                            <tr>
                                <th width="5%">No</th>
                                <th width="12%">NIM</th>
                                <th>Nama Mahasiswa</th>
                                <th width="8%" class="text-center">Kelas</th>
                                <th width="12%" class="text-center">Status</th>
                                <th width="20%">Pembimbing 1</th>
                                <th width="20%">Pembimbing 2</th>
                                <th width="10%" class="text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($skripsi_list)): ?>
                                <tr>
                                    <td colspan="8" class="text-center text-muted">
                                        <i class="fas fa-inbox"></i> Belum ada mahasiswa yang terdaftar Skripsi.
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php 
                                $no = 1; 
                                $page = ($this->uri->segment(3)) ? $this->uri->segment(3) : 1;
                                $no = ($page - 1) * 10 + 1;
                                foreach ($skripsi_list as $skripsi): 
                                ?>
                                    <tr>
                                        <td><?php echo $no++; ?></td>
                                        <td><strong><?php echo $skripsi->nim; ?></strong></td>
                                        <td><?php echo $skripsi->nama_mahasiswa; ?></td>
                                        <td class="text-center">
                                            <span class="badge badge-info"><?php echo $skripsi->kelas; ?></span>
                                        </td>
                                        <td class="text-center">
                                            <?php
                                            $status_class = 'secondary';
                                            $status_text = ucfirst(str_replace('_', ' ', $skripsi->status));
                                            switch ($skripsi->status) {
                                                case 'terdaftar':
                                                    $status_class = 'info';
                                                    break;
                                                case 'sedang_skripsi':
                                                    $status_class = 'primary';
                                                    break;
                                                case 'layak_sidang':
                                                    $status_class = 'warning';
                                                    break;
                                                case 'selesai_skripsi':
                                                    $status_class = 'success';
                                                    break;
                                                case 'batal':
                                                    $status_class = 'danger';
                                                    break;
                                            }
                                            ?>
                                            <span class="badge badge-<?php echo $status_class; ?>"><?php echo $status_text; ?></span>
                                        </td>
                                        <td>
                                            <?php if (!empty($skripsi->pembimbing_1)): ?>
                                                <strong><?php echo $skripsi->pembimbing_1; ?></strong><br>
                                                <small class="text-muted"><?php echo $skripsi->pembimbing_1_nidn ?: '-'; ?></small>
                                            <?php else: ?>
                                                <span class="text-muted">Belum ditetapkan</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php if (!empty($skripsi->pembimbing_2)): ?>
                                                <strong><?php echo $skripsi->pembimbing_2; ?></strong><br>
                                                <small class="text-muted"><?php echo $skripsi->pembimbing_2_nidn ?: '-'; ?></small>
                                            <?php else: ?>
                                                <span class="text-muted">Belum ditetapkan</span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="text-center">
                                            <a href="<?php echo site_url('Skripsi/update_status/' . $skripsi->id); ?>" class="btn btn-sm btn-warning" title="Update Status">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <a href="<?php echo site_url('Skripsi/edit_judul/' . $skripsi->id); ?>" class="btn btn-sm btn-secondary" title="Ubah Judul">
                                                <i class="fas fa-pen"></i>
                                            </a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>

                <?php if (isset($pagination)): ?>
                    <div class="row mt-3">
                        <div class="col-md-6">
                            <p class="text-muted">Menampilkan <?php echo $total_rows; ?> data</p>
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
    const searchInput = document.getElementById('searchSkripsi');
    if (searchInput) {
        const table = document.getElementById('tableSkripsi');
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

