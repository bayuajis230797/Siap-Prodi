<div class="card">
    <div class="card-header">
        <h3 class="card-title">
            <i class="fas fa-envelope-open-text"></i> Daftar Pengajuan Skripsi Mahasiswa
        </h3>
        <div class="card-tools">
            <?php if ($tahun_aktif): ?>
                <span class="badge badge-info">
                    TA Aktif: <?php echo $tahun_aktif->tahun_akademik . ' ' . ucfirst($tahun_aktif->semester); ?>
                </span>
            <?php else: ?>
                <span class="badge badge-warning">Belum ada tahun aktif</span>
            <?php endif; ?>
        </div>
    </div>
    <div class="card-body">
        <form method="get" class="mb-3">
            <div class="form-row">
                <div class="col-md-3">
                    <label for="status_filter">Status Pengajuan</label>
                    <select name="status" id="status_filter" class="form-control">
                        <option value="">-- Semua Status --</option>
                        <option value="pending" <?php echo ($status_filter === 'pending') ? 'selected' : ''; ?>>Pending</option>
                        <option value="disetujui" <?php echo ($status_filter === 'disetujui') ? 'selected' : ''; ?>>Disetujui</option>
                        <option value="ditolak" <?php echo ($status_filter === 'ditolak') ? 'selected' : ''; ?>>Ditolak</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label for="kelas_filter">Kelas</label>
                    <input type="text" name="kelas" id="kelas_filter" class="form-control" value="<?php echo htmlspecialchars($kelas_filter); ?>" placeholder="Contoh: 7A">
                </div>
                <div class="col-md-3 align-self-end">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-filter"></i> Terapkan
                    </button>
                    <a href="<?php echo site_url('Skripsi/pengajuan'); ?>" class="btn btn-secondary">
                        <i class="fas fa-sync"></i> Reset
                    </a>
                </div>
            </div>
        </form>

        <!-- Search Box dan Show Entries -->
        <div class="row mb-3">
            <div class="col-md-4">
                <div class="input-group">
                    <div class="input-group-prepend">
                        <span class="input-group-text"><i class="fas fa-search"></i></span>
                    </div>
                    <input type="text" id="searchPengajuan" class="form-control" placeholder="Cari NIM, Nama, Judul...">
                </div>
            </div>
            <div class="col-md-4 offset-md-4">
                <form method="get" action="<?php echo site_url('Skripsi/pengajuan'); ?>" id="formPerPagePengajuan" class="form-inline justify-content-end">
                    <?php if ($status_filter): ?>
                        <input type="hidden" name="status" value="<?php echo htmlspecialchars($status_filter); ?>">
                    <?php endif; ?>
                    <?php if ($kelas_filter): ?>
                        <input type="hidden" name="kelas" value="<?php echo htmlspecialchars($kelas_filter); ?>">
                    <?php endif; ?>
                    <label for="per_page_pengajuan" class="mr-2">Tampilkan:</label>
                    <select name="per_page" id="per_page_pengajuan" class="form-control form-control-sm" onchange="document.getElementById('formPerPagePengajuan').submit();">
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
            <table class="table table-bordered table-striped table-hover" id="tablePengajuan">
                <thead>
                    <tr>
                        <th width="5%">No</th>
                        <th width="15%">Mahasiswa</th>
                        <th width="8%">Kelas</th>
                        <th>Judul Pengajuan</th>
                        <th width="18%">Pembimbing yang Ditentukan</th>
                        <th width="12%" class="text-center">Status</th>
                        <th width="12%" class="text-center">Tanggal</th>
                        <th width="10%" class="text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($pengajuan_list)): ?>
                        <tr>
                            <td colspan="8" class="text-center text-muted">
                                <i class="fas fa-inbox"></i> Belum ada pengajuan yang ditemukan.
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php 
                            $no = ($this->uri->segment(3)) ? (($this->uri->segment(3) - 1) * 10) + 1 : 1;
                            $status_badge = array('pending' => 'warning', 'disetujui' => 'success', 'ditolak' => 'danger');
                        ?>
                        <?php foreach ($pengajuan_list as $item): ?>
                            <tr>
                                <td><?php echo $no++; ?></td>
                                <td>
                                    <strong><?php echo $item->nama_mahasiswa; ?></strong><br>
                                    <small class="text-muted"><?php echo $item->nim; ?></small>
                                </td>
                                <td class="text-center">
                                    <span class="badge badge-info"><?php echo $item->kelas ?: '-'; ?></span>
                                </td>
                                <td>
                                    <strong><?php echo $item->judul_pengajuan; ?></strong>
                                    <?php if (!empty($item->file_proposal)): ?>
                                        <br><small>
                                            <a href="<?php echo site_url('Skripsi/download_proposal/' . $item->id); ?>" target="_blank" class="text-info">
                                                <i class="fas fa-file-pdf"></i> Download Proposal
                                            </a>
                                        </small>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <small>Pembimbing 1:</small><br>
                                    <strong><?php echo $item->pembimbing_1_final_nama ?? '<span class="text-muted">Belum ditentukan</span>'; ?></strong><br>
                                    <small>Pembimbing 2:</small><br>
                                    <strong><?php echo $item->pembimbing_2_final_nama ?? '<span class="text-muted">Belum ditentukan</span>'; ?></strong>
                                </td>
                                <td class="text-center">
                                    <span class="badge badge-<?php echo $status_badge[$item->status] ?? 'secondary'; ?>">
                                        <?php echo ucfirst($item->status); ?>
                                    </span>
                                </td>
                                <td class="text-center">
                                    <?php echo date('d/m/Y H:i', strtotime($item->created_at)); ?>
                                </td>
                                <td class="text-center">
                                    <a href="<?php echo site_url('Skripsi/review/' . $item->id); ?>" class="btn btn-sm btn-primary" title="Detail">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="<?php echo site_url('Skripsi/delete_pengajuan/' . $item->id); ?>" 
                                       class="btn btn-sm btn-danger" 
                                       title="Hapus"
                                       onclick="return confirm('Hapus pengajuan skripsi ini?')">
                                        <i class="fas fa-trash"></i>
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <div class="row mt-3">
            <div class="col-md-6">
                <p class="text-muted mb-0">Total data: <?php echo $total_rows; ?></p>
            </div>
            <div class="col-md-6">
                <?php echo $pagination; ?>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('searchPengajuan');
    if (searchInput) {
        const table = document.getElementById('tablePengajuan');
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

