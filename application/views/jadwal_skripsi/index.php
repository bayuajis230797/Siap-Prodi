<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Jadwal Ujian Skripsi</h3>
                <div class="card-tools">
                    <a href="<?php echo site_url('Jadwal_skripsi/export_excel' . ($status_filter ? '?status=' . urlencode($status_filter) : '')); ?>" class="btn btn-success btn-sm mr-2">
                        <i class="fas fa-file-excel"></i> Export Excel
                    </a>
                    <a href="<?php echo site_url('Jadwal_skripsi/create'); ?>" class="btn btn-primary btn-sm">
                        <i class="fas fa-plus"></i> Tambah Jadwal
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
                        <strong>Perhatian:</strong> Belum ada tahun akademik yang aktif.
                    </div>
                <?php endif; ?>

                <!-- Filter Status -->
                <div class="row mb-3">
                    <div class="col-md-4">
                        <form method="get" action="<?php echo site_url('Jadwal_skripsi/index'); ?>">
                            <div class="input-group">
                                <select name="status" class="form-control">
                                    <option value="">-- Semua Status --</option>
                                    <option value="terjadwal" <?php echo ($status_filter == 'terjadwal') ? 'selected' : ''; ?>>Terjadwal</option>
                                    <option value="selesai" <?php echo ($status_filter == 'selesai') ? 'selected' : ''; ?>>Selesai</option>
                                    <option value="batal" <?php echo ($status_filter == 'batal') ? 'selected' : ''; ?>>Batal</option>
                                </select>
                                <div class="input-group-append">
                                    <button type="submit" class="btn btn-info">
                                        <i class="fas fa-filter"></i> Filter
                                    </button>
                                    <?php if ($status_filter): ?>
                                        <a href="<?php echo site_url('Jadwal_skripsi'); ?>" class="btn btn-secondary">
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
                            <input type="text" id="searchJadwalSkripsi" class="form-control" placeholder="Cari NIM, Nama, Ruangan...">
                        </div>
                    </div>
                    <div class="col-md-4 offset-md-4">
                        <form method="get" action="<?php echo site_url('Jadwal_skripsi/index'); ?>" id="formPerPageJadwalSkripsi" class="form-inline justify-content-end">
                            <?php if ($status_filter): ?>
                                <input type="hidden" name="status" value="<?php echo htmlspecialchars($status_filter); ?>">
                            <?php endif; ?>
                            <label for="per_page_jadwal_skripsi" class="mr-2">Tampilkan:</label>
                            <select name="per_page" id="per_page_jadwal_skripsi" class="form-control form-control-sm" onchange="document.getElementById('formPerPageJadwalSkripsi').submit();">
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
                    <table class="table table-bordered table-striped table-hover" id="tableJadwalSkripsi">
                        <thead>
                            <tr>
                                <th width="5%">No</th>
                                <th width="10%">NIM</th>
                                <th>Nama Mahasiswa</th>
                                <th width="8%" class="text-center">Kelas</th>
                                <th width="12%">Tanggal Ujian</th>
                                <th width="10%">Jam</th>
                                <th width="10%">Ruangan</th>
                                <th width="15%">Ketua Penguji</th>
                                <th width="15%">Anggota Penguji 1</th>
                                <th width="15%">Anggota Penguji 2</th>
                                <th width="8%" class="text-center">Status</th>
                                <th width="8%" class="text-center">Nilai Bimb.</th>
                                <th width="8%" class="text-center">Nilai Sidang</th>
                                <th width="8%" class="text-center">Nilai Akhir</th>
                                <th width="6%" class="text-center">Grade</th>
                                <th width="16%" class="text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($jadwal_list)): ?>
                                <tr>
                                    <td colspan="12" class="text-center text-muted">
                                        <i class="fas fa-inbox"></i> Belum ada jadwal ujian skripsi.
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php 
                                $no = 1; 
                                $page = ($this->uri->segment(3)) ? $this->uri->segment(3) : 1;
                                $no = ($page - 1) * 10 + 1;
                                foreach ($jadwal_list as $jadwal): 
                                ?>
                                    <tr>
                                        <td><?php echo $no++; ?></td>
                                        <td><strong><?php echo $jadwal->nim; ?></strong></td>
                                        <td><?php echo $jadwal->nama_mahasiswa; ?></td>
                                        <td class="text-center">
                                            <span class="badge badge-info"><?php echo $jadwal->kelas; ?></span>
                                        </td>
                                        <td><?php echo date('d/m/Y', strtotime($jadwal->tanggal_ujian)); ?></td>
                                        <td><?php echo date('H:i', strtotime($jadwal->jam_mulai)) . ' - ' . date('H:i', strtotime($jadwal->jam_selesai)); ?></td>
                                        <td><?php echo $jadwal->ruangan ?: '-'; ?></td>
                                        <td>
                                            <?php echo $jadwal->ketua_penguji; ?><br>
                                            <small class="text-muted"><?php echo $jadwal->ketua_penguji_nidn ?: '-'; ?></small>
                                        </td>
                                        <td>
                                            <?php echo $jadwal->anggota_penguji_1; ?><br>
                                            <small class="text-muted"><?php echo $jadwal->anggota_penguji_1_nidn ?: '-'; ?></small>
                                        </td>
                                        <td>
                                            <?php echo $jadwal->anggota_penguji_2; ?><br>
                                            <small class="text-muted"><?php echo $jadwal->anggota_penguji_2_nidn ?: '-'; ?></small>
                                        </td>
                                        <td class="text-center">
                                            <?php
                                            $status_class = 'secondary';
                                            $status_text = ucfirst($jadwal->status);
                                            switch ($jadwal->status) {
                                                case 'terjadwal':
                                                    $status_class = 'info';
                                                    break;
                                                case 'selesai':
                                                    $status_class = 'success';
                                                    break;
                                                case 'batal':
                                                    $status_class = 'danger';
                                                    break;
                                            }
                                            ?>
                                            <span class="badge badge-<?php echo $status_class; ?>"><?php echo $status_text; ?></span>
                                        </td>
                                        <td class="text-center align-middle">
                                            <?php echo $jadwal->nilai_bimbingan_final !== null ? number_format($jadwal->nilai_bimbingan_final, 2) : '<span class="text-muted">-</span>'; ?>
                                        </td>
                                        <td class="text-center align-middle">
                                            <?php echo $jadwal->nilai_sidang !== null ? number_format($jadwal->nilai_sidang, 2) : '<span class="text-muted">-</span>'; ?>
                                        </td>
                                        <td class="text-center align-middle">
                                            <?php echo $jadwal->nilai_skripsi_final !== null ? '<strong>' . number_format($jadwal->nilai_skripsi_final, 2) . '</strong>' : '<span class="text-muted">-</span>'; ?>
                                        </td>
                                        <td class="text-center align-middle">
                                            <?php echo $jadwal->nilai_huruf ? '<span class="badge badge-success">' . $jadwal->nilai_huruf . '</span>' : '<span class="text-muted">-</span>'; ?>
                                        </td>
                                        <td class="text-center">
                                            <a href="<?php echo site_url('Jadwal_skripsi/penilaian/' . $jadwal->id); ?>" class="btn btn-sm btn-success" title="Input Penilaian">
                                                <i class="fas fa-clipboard-check"></i>
                                            </a>
                                            <a href="<?php echo site_url('Jadwal_skripsi/edit/' . $jadwal->id); ?>" class="btn btn-sm btn-warning" title="Edit">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <a href="<?php echo site_url('Jadwal_skripsi/delete/' . $jadwal->id); ?>" class="btn btn-sm btn-danger" title="Hapus" onclick="return confirm('Apakah Anda yakin ingin menghapus jadwal ini?');">
                                                <i class="fas fa-trash"></i>
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
    const searchInput = document.getElementById('searchJadwalSkripsi');
    if (searchInput) {
        const table = document.getElementById('tableJadwalSkripsi');
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

