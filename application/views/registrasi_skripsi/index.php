<div class="row">
    <div class="col-12">
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

        <div class="card card-primary card-outline">
            <div class="card-header">
                <h3 class="card-title">Daftar Mahasiswa Skripsi</h3>
                <div class="card-tools">
                    <a href="<?php echo site_url('Registrasi_skripsi/create'); ?>" class="btn btn-primary btn-sm">
                        <i class="fas fa-plus"></i> Tambah Mahasiswa Skripsi
                    </a>
                </div>
            </div>
            <div class="card-body">
                <?php if ($tahun_aktif): ?>
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle"></i>
                        <strong>Tahun Akademik:</strong> <?php echo $tahun_aktif->tahun_akademik . ' ' . ucfirst($tahun_aktif->semester); ?>
                    </div>
                <?php else: ?>
                    <div class="alert alert-warning">
                        <i class="fas fa-exclamation-triangle"></i>
                        Tidak ada tahun akademik aktif. Silakan aktifkan tahun akademik terlebih dahulu.
                    </div>
                <?php endif; ?>

                <!-- Filter Kelas, Search Box dan Show Entries -->
                <div class="row mb-3">
                    <div class="col-md-3">
                        <form method="get" action="<?php echo site_url('Registrasi_skripsi/index'); ?>" id="formFilterKelas" class="form-inline">
                            <?php if (isset($per_page) && $per_page != 10): ?>
                                <input type="hidden" name="per_page" value="<?php echo $per_page; ?>">
                            <?php endif; ?>
                            <label for="filter_kelas" class="mr-2">Filter Kelas:</label>
                            <select name="kelas" id="filter_kelas" class="form-control form-control-sm" onchange="document.getElementById('formFilterKelas').submit();">
                                <option value="">-- Semua Kelas --</option>
                                <?php foreach ($kelas_list as $kelas): ?>
                                    <option value="<?php echo htmlspecialchars($kelas); ?>" 
                                            <?php echo ($kelas_filter === $kelas) ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($kelas); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </form>
                    </div>
                    <div class="col-md-4">
                        <div class="input-group">
                            <div class="input-group-prepend">
                                <span class="input-group-text"><i class="fas fa-search"></i></span>
                            </div>
                            <input type="text" id="searchRegistrasiSkripsi" class="form-control" placeholder="Cari NIM, Nama, Kelas...">
                        </div>
                    </div>
                    <div class="col-md-3 offset-md-2">
                        <form method="get" action="<?php echo site_url('Registrasi_skripsi/index'); ?>" id="formPerPageRegistrasiSkripsi" class="form-inline justify-content-end">
                            <?php if ($kelas_filter): ?>
                                <input type="hidden" name="kelas" value="<?php echo htmlspecialchars($kelas_filter); ?>">
                            <?php endif; ?>
                            <label for="per_page_registrasi_skripsi" class="mr-2">Tampilkan:</label>
                            <select name="per_page" id="per_page_registrasi_skripsi" class="form-control form-control-sm" onchange="document.getElementById('formPerPageRegistrasiSkripsi').submit();">
                                <option value="10" <?php echo (isset($per_page) && $per_page == 10) ? 'selected' : ''; ?>>10</option>
                                <option value="25" <?php echo (isset($per_page) && $per_page == 25) ? 'selected' : ''; ?>>25</option>
                                <option value="50" <?php echo (isset($per_page) && $per_page == 50) ? 'selected' : ''; ?>>50</option>
                                <option value="100" <?php echo (isset($per_page) && $per_page == 100) ? 'selected' : ''; ?>>100</option>
                            </select>
                        </form>
                    </div>
                </div>

                <?php if (empty($skripsi_list)): ?>
                    <div class="alert alert-warning">
                        <i class="fas fa-exclamation-triangle"></i>
                        Belum ada mahasiswa yang terdaftar Skripsi.
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped" id="tableRegistrasiSkripsi">
                            <thead>
                                <tr>
                                    <th width="5%">#</th>
                                    <th width="10%">NIM</th>
                                    <th width="20%">Nama Mahasiswa</th>
                                    <th width="10%">Kelas</th>
                                    <th width="12%" class="text-center">Status</th>
                                    <th width="18%">Pembimbing 1</th>
                                    <th width="18%">Pembimbing 2</th>
                                    <th width="7%" class="text-center">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php 
                                $no = 1;
                                $page = ($this->uri->segment(3)) ? $this->uri->segment(3) : 1;
                                $no = ($page - 1) * $per_page + 1;
                                foreach ($skripsi_list as $skripsi): 
                                ?>
                                    <tr>
                                        <td><?php echo $no++; ?></td>
                                        <td><strong><?php echo htmlspecialchars($skripsi->nim); ?></strong></td>
                                        <td><?php echo htmlspecialchars($skripsi->nama_mahasiswa); ?></td>
                                        <td><?php echo htmlspecialchars($skripsi->kelas); ?></td>
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
                                                <strong><?php echo htmlspecialchars($skripsi->pembimbing_1); ?></strong><br>
                                                <small class="text-muted"><?php echo htmlspecialchars($skripsi->pembimbing_1_nidn ?: '-'); ?></small>
                                            <?php else: ?>
                                                <span class="text-muted">Belum ditetapkan</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php if (!empty($skripsi->pembimbing_2)): ?>
                                                <strong><?php echo htmlspecialchars($skripsi->pembimbing_2); ?></strong><br>
                                                <small class="text-muted"><?php echo htmlspecialchars($skripsi->pembimbing_2_nidn ?: '-'); ?></small>
                                            <?php else: ?>
                                                <span class="text-muted">Belum ditetapkan</span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="text-center">
                                            <a href="<?php echo site_url('Registrasi_skripsi/delete/' . $skripsi->id); ?>" 
                                               class="btn btn-sm btn-danger" 
                                               title="Hapus"
                                               onclick="return confirm('Apakah Anda yakin ingin menghapus mahasiswa ini dari registrasi skripsi?');">
                                                <i class="fas fa-trash"></i>
                                            </a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>

                    <?php if (!empty($pagination)): ?>
                        <div class="row mt-3">
                            <div class="col-md-6">
                                <p class="text-muted">Menampilkan <?php echo count($skripsi_list); ?> dari <?php echo $total_rows; ?> data</p>
                            </div>
                            <div class="col-md-6">
                                <?php echo $pagination; ?>
                            </div>
                        </div>
                    <?php endif; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('searchRegistrasiSkripsi');
    if (searchInput) {
        const table = document.getElementById('tableRegistrasiSkripsi');
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

