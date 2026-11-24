<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Jadwal Kuliah</h3>
                <div class="card-tools">
                    <?php if (in_array(get_user_role(), array('sekprodi', 'kaprodi'), true)): ?>
                        <?php if (!empty($filter_kelas)): ?>
                            <!-- Jika ada filter kelas, tampilkan tombol download langsung untuk kelas tersebut -->
                            <a href="<?php echo site_url('Jadwal_kuliah/download_per_kelas?kelas=' . urlencode($filter_kelas)); ?>" class="btn btn-success btn-sm mr-2">
                                <i class="fas fa-file-excel"></i> Download Kelas <?php echo htmlspecialchars($filter_kelas); ?>
                            </a>
                        <?php else: ?>
                            <!-- Jika tidak ada filter kelas, tampilkan dropdown -->
                            <div class="btn-group mr-2">
                                <button type="button" class="btn btn-success btn-sm dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                    <i class="fas fa-file-excel"></i> Download Per Kelas
                                </button>
                                <div class="dropdown-menu dropdown-menu-right" style="max-height: 300px; overflow-y: auto;">
                                    <?php if (isset($kelas_list) && !empty($kelas_list)): ?>
                                        <?php foreach ($kelas_list as $k): ?>
                                            <a class="dropdown-item" href="<?php echo site_url('Jadwal_kuliah/download_per_kelas?kelas=' . urlencode($k->kelas)); ?>">
                                                <i class="fas fa-users"></i> Kelas <?php echo htmlspecialchars($k->kelas); ?>
                                            </a>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <a class="dropdown-item text-muted" href="#">
                                            <i class="fas fa-info-circle"></i> Belum ada kelas
                                        </a>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endif; ?>
                    <?php endif; ?>
                    <?php if (get_user_role() !== 'kaprodi'): ?>
                    <a href="<?php echo site_url('Jadwal_kuliah/generate_pemadatan'); ?>" class="btn btn-warning btn-sm">
                        <i class="fas fa-compress"></i> Generate Jadwal Pemadatan
                    </a>
                    <a href="<?php echo site_url('Jadwal_kuliah/generate_otomatis'); ?>" class="btn btn-success btn-sm">
                        <i class="fas fa-magic"></i> Generate Jadwal Otomatis
                    </a>
                    <a href="<?php echo site_url('Jadwal_kuliah/create_pemadatan'); ?>" class="btn btn-warning btn-sm">
                        <i class="fas fa-plus-circle"></i> Tambah Jadwal Pemadatan
                    </a>
                    <a href="<?php echo site_url('Jadwal_kuliah/create'); ?>" class="btn btn-primary btn-sm">
                        <i class="fas fa-plus"></i> Tambah Jadwal
                    </a>
                    <?php endif; ?>
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
                        <br><small>Jadwal yang ditampilkan adalah untuk tahun akademik ini.</small>
                    </div>
                <?php else: ?>
                    <div class="alert alert-warning">
                        <i class="fas fa-exclamation-triangle"></i> 
                        <strong>Perhatian:</strong> Belum ada tahun akademik yang aktif. Aktifkan tahun akademik terlebih dahulu.
                    </div>
                <?php endif; ?>
                
                <?php if (in_array(get_user_role(), array('sekprodi', 'kaprodi'), true)): ?>
                    <!-- Filter Kelas -->
                    <form method="get" action="<?php echo site_url('Jadwal_kuliah/index'); ?>" class="mb-4">
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="filter_kelas">Filter Kelas</label>
                                    <select name="kelas" id="filter_kelas" class="form-control">
                                        <option value="">-- Semua Kelas --</option>
                                        <?php if (isset($kelas_list) && !empty($kelas_list)): ?>
                                            <?php foreach ($kelas_list as $k): ?>
                                                <option value="<?php echo htmlspecialchars($k->kelas); ?>" <?php echo (isset($filter_kelas) && $filter_kelas === $k->kelas) ? 'selected' : ''; ?>>
                                                    <?php echo htmlspecialchars($k->kelas); ?>
                                                </option>
                                            <?php endforeach; ?>
                                        <?php endif; ?>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="per_page_jadwal_kuliah">Tampilkan</label>
                                    <select name="per_page" id="per_page_jadwal_kuliah" class="form-control">
                                        <option value="10" <?php echo (isset($per_page) && $per_page == 10) ? 'selected' : ''; ?>>10 data</option>
                                        <option value="25" <?php echo (isset($per_page) && $per_page == 25) ? 'selected' : ''; ?>>25 data</option>
                                        <option value="50" <?php echo (isset($per_page) && $per_page == 50) ? 'selected' : ''; ?>>50 data</option>
                                        <option value="100" <?php echo (isset($per_page) && $per_page == 100) ? 'selected' : ''; ?>>100 data</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>&nbsp;</label>
                                    <div>
                                        <button type="submit" class="btn btn-primary">
                                            <i class="fas fa-filter"></i> Terapkan Filter
                                        </button>
                                        <a href="<?php echo site_url('Jadwal_kuliah'); ?>" class="btn btn-secondary">
                                            <i class="fas fa-undo"></i> Reset
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </form>
                <?php endif; ?>
                
                <!-- Search Box dan Show Entries -->
                <div class="row mb-3">
                    <div class="col-md-4">
                        <div class="input-group">
                            <div class="input-group-prepend">
                                <span class="input-group-text"><i class="fas fa-search"></i></span>
                            </div>
                            <input type="text" id="searchJadwalKuliah" class="form-control" placeholder="Cari Kode MK, Mata Kuliah, Dosen...">
                        </div>
                    </div>
                    <?php if (!in_array(get_user_role(), array('sekprodi', 'kaprodi'), true)): ?>
                        <div class="col-md-4 offset-md-4">
                            <form method="get" action="<?php echo site_url('Jadwal_kuliah/index'); ?>" id="formPerPageJadwalKuliah" class="form-inline justify-content-end">
                                <label for="per_page_jadwal_kuliah" class="mr-2">Tampilkan:</label>
                                <select name="per_page" id="per_page_jadwal_kuliah" class="form-control form-control-sm" onchange="document.getElementById('formPerPageJadwalKuliah').submit();">
                                    <option value="10" <?php echo (isset($per_page) && $per_page == 10) ? 'selected' : ''; ?>>10</option>
                                    <option value="25" <?php echo (isset($per_page) && $per_page == 25) ? 'selected' : ''; ?>>25</option>
                                    <option value="50" <?php echo (isset($per_page) && $per_page == 50) ? 'selected' : ''; ?>>50</option>
                                    <option value="100" <?php echo (isset($per_page) && $per_page == 100) ? 'selected' : ''; ?>>100</option>
                                </select>
                                <span class="ml-2">data</span>
                            </form>
                        </div>
                    <?php endif; ?>
                </div>
                
                <div class="table-responsive">
                    <table class="table table-bordered table-striped table-hover" id="tableJadwalKuliah">
                        <thead>
                            <tr>
                                <th width="5%">No</th>
                                <th width="10%">Kode MK</th>
                                <th>Mata Kuliah</th>
                                <th width="8%" class="text-center">SKS</th>
                                <th width="8%" class="text-center">Kelas</th>
                                <th width="10%">Hari</th>
                                <th width="10%">Jam</th>
                                <th width="12%">Ruang</th>
                                <th>Dosen</th>
                                <?php if (get_user_role() !== 'kaprodi'): ?>
                                <th width="12%" class="text-center">Aksi</th>
                                <?php endif; ?>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($jadwal)): ?>
                                <tr>
                                    <td colspan="<?php echo get_user_role() === 'kaprodi' ? '9' : '10'; ?>" class="text-center text-muted">
                                        <i class="fas fa-inbox"></i> Belum ada jadwal kuliah.
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php 
                                $no = 1; 
                                $page = ($this->uri->segment(3)) ? $this->uri->segment(3) : 1;
                                $no = ($page - 1) * 20 + 1;
                                foreach ($jadwal as $j): 
                                ?>
                                    <tr>
                                        <td><?php echo $no++; ?></td>
                                        <td><strong><?php echo $j->kode_mk; ?></strong></td>
                                        <td><?php echo $j->nama_mk; ?></td>
                                        <td class="text-center">
                                            <span class="badge badge-info"><?php echo $j->sks; ?> SKS</span>
                                        </td>
                                        <td class="text-center">
                                            <?php if (isset($j->kelas) && !empty($j->kelas)): ?>
                                                <span class="badge badge-primary"><?php echo $j->kelas; ?></span>
                                            <?php else: ?>
                                                <span class="badge badge-secondary">-</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <span class="badge badge-success"><?php echo ucfirst($j->hari); ?></span>
                                        </td>
                                        <td>
                                            <small>
                                                <strong><?php echo date('H:i', strtotime($j->jam_mulai)); ?></strong> - 
                                                <strong><?php echo date('H:i', strtotime($j->jam_selesai)); ?></strong>
                                            </small>
                                        </td>
                                        <td>
                                            <?php if (isset($j->nama_ruang) && !empty($j->nama_ruang)): ?>
                                                <small>
                                                    <strong><?php echo $j->kode_ruang; ?></strong><br>
                                                    <?php echo $j->nama_ruang; ?>
                                                </small>
                                            <?php else: ?>
                                                <span class="text-muted">-</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <small>
                                                <strong><?php echo $j->nama_dosen; ?></strong><br>
                                                <span class="text-muted">NIDN: <?php echo $j->nidn; ?></span>
                                            </small>
                                        </td>
                                        <?php if (get_user_role() !== 'kaprodi'): ?>
                                        <td class="text-center">
                                            <a href="<?php echo site_url('Jadwal_kuliah/edit/' . $j->id); ?>" class="btn btn-warning btn-sm" title="Edit">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <a href="<?php echo site_url('Jadwal_kuliah/delete/' . $j->id); ?>" 
                                               class="btn btn-danger btn-sm" 
                                               title="Hapus"
                                               onclick="return confirm('Hapus jadwal ini?')">
                                                <i class="fas fa-trash"></i>
                                            </a>
                                        </td>
                                        <?php endif; ?>
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
                                Menampilkan <?php echo count($jadwal); ?> dari <?php echo $total_rows; ?> data
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
    const searchInput = document.getElementById('searchJadwalKuliah');
    if (searchInput) {
        const table = document.getElementById('tableJadwalKuliah');
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

