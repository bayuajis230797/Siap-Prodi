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
                <h3 class="card-title">Ajuan Judul Skripsi</h3>
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

                <!-- Filter Status, Kelas, Search Box dan Show Entries -->
                <div class="row mb-3">
                    <div class="col-md-3">
                        <form method="get" action="<?php echo site_url('Judul_skripsi/index'); ?>" id="formFilterStatus" class="form-inline">
                            <?php if (isset($per_page) && $per_page != 10): ?>
                                <input type="hidden" name="per_page" value="<?php echo $per_page; ?>">
                            <?php endif; ?>
                            <?php if ($kelas_filter): ?>
                                <input type="hidden" name="kelas" value="<?php echo htmlspecialchars($kelas_filter); ?>">
                            <?php endif; ?>
                            <label for="filter_status" class="mr-2">Status:</label>
                            <select name="status" id="filter_status" class="form-control form-control-sm" onchange="document.getElementById('formFilterStatus').submit();">
                                <option value="">-- Semua Status --</option>
                                <option value="pending" <?php echo ($status_filter === 'pending') ? 'selected' : ''; ?>>Pending</option>
                                <option value="disetujui" <?php echo ($status_filter === 'disetujui') ? 'selected' : ''; ?>>Disetujui</option>
                                <option value="ditolak" <?php echo ($status_filter === 'ditolak') ? 'selected' : ''; ?>>Ditolak</option>
                            </select>
                        </form>
                    </div>
                    <div class="col-md-3">
                        <form method="get" action="<?php echo site_url('Judul_skripsi/index'); ?>" id="formFilterKelas" class="form-inline">
                            <?php if (isset($per_page) && $per_page != 10): ?>
                                <input type="hidden" name="per_page" value="<?php echo $per_page; ?>">
                            <?php endif; ?>
                            <?php if ($status_filter): ?>
                                <input type="hidden" name="status" value="<?php echo htmlspecialchars($status_filter); ?>">
                            <?php endif; ?>
                            <label for="filter_kelas" class="mr-2">Kelas:</label>
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
                    <div class="col-md-3">
                        <div class="input-group">
                            <div class="input-group-prepend">
                                <span class="input-group-text"><i class="fas fa-search"></i></span>
                            </div>
                            <input type="text" id="searchJudulSkripsi" class="form-control" placeholder="Cari NIM, Nama, Judul...">
                        </div>
                    </div>
                    <div class="col-md-3">
                        <form method="get" action="<?php echo site_url('Judul_skripsi/index'); ?>" id="formPerPageJudulSkripsi" class="form-inline justify-content-end">
                            <?php if ($kelas_filter): ?>
                                <input type="hidden" name="kelas" value="<?php echo htmlspecialchars($kelas_filter); ?>">
                            <?php endif; ?>
                            <?php if ($status_filter): ?>
                                <input type="hidden" name="status" value="<?php echo htmlspecialchars($status_filter); ?>">
                            <?php endif; ?>
                            <label for="per_page_judul_skripsi" class="mr-2">Tampilkan:</label>
                            <select name="per_page" id="per_page_judul_skripsi" class="form-control form-control-sm" onchange="document.getElementById('formPerPageJudulSkripsi').submit();">
                                <option value="10" <?php echo (isset($per_page) && $per_page == 10) ? 'selected' : ''; ?>>10</option>
                                <option value="25" <?php echo (isset($per_page) && $per_page == 25) ? 'selected' : ''; ?>>25</option>
                                <option value="50" <?php echo (isset($per_page) && $per_page == 50) ? 'selected' : ''; ?>>50</option>
                                <option value="100" <?php echo (isset($per_page) && $per_page == 100) ? 'selected' : ''; ?>>100</option>
                            </select>
                        </form>
                    </div>
                </div>

                <?php if (empty($pengajuan_list)): ?>
                    <div class="alert alert-warning">
                        <i class="fas fa-exclamation-triangle"></i>
                        Belum ada pengajuan judul skripsi.
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped" id="tableJudulSkripsi">
                            <thead>
                                <tr>
                                    <th width="5%">#</th>
                                    <th width="10%">NIM</th>
                                    <th width="15%">Nama Mahasiswa</th>
                                    <th width="8%">Kelas</th>
                                    <th width="25%">Judul Pengajuan</th>
                                    <th width="10%" class="text-center">Status</th>
                                    <th width="12%">Pembimbing 1</th>
                                    <th width="12%">Pembimbing 2</th>
                                    <th width="3%" class="text-center">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php 
                                $no = 1;
                                $page = ($this->uri->segment(3)) ? $this->uri->segment(3) : 1;
                                $no = ($page - 1) * $per_page + 1;
                                foreach ($pengajuan_list as $p): 
                                ?>
                                    <tr>
                                        <td><?php echo $no++; ?></td>
                                        <td><strong><?php echo htmlspecialchars($p->nim); ?></strong></td>
                                        <td><?php echo htmlspecialchars($p->nama_mahasiswa); ?></td>
                                        <td><?php echo htmlspecialchars($p->kelas); ?></td>
                                        <td>
                                            <strong><?php echo htmlspecialchars($p->judul_pengajuan); ?></strong>
                                            <?php if (!empty($p->judul_disetujui) && $p->judul_disetujui != $p->judul_pengajuan): ?>
                                                <br><small class="text-success">Judul Disetujui: <?php echo htmlspecialchars($p->judul_disetujui); ?></small>
                                            <?php endif; ?>
                                        </td>
                                        <td class="text-center">
                                            <?php
                                            $status_class = 'secondary';
                                            switch ($p->status) {
                                                case 'pending':
                                                    $status_class = 'warning';
                                                    break;
                                                case 'disetujui':
                                                    $status_class = 'success';
                                                    break;
                                                case 'ditolak':
                                                    $status_class = 'danger';
                                                    break;
                                            }
                                            ?>
                                            <span class="badge badge-<?php echo $status_class; ?>">
                                                <?php echo ucfirst($p->status); ?>
                                            </span>
                                        </td>
                                        <td>
                                            <?php if (!empty($p->pembimbing_1_final_nama)): ?>
                                                <strong><?php echo htmlspecialchars($p->pembimbing_1_final_nama); ?></strong>
                                            <?php elseif (!empty($p->pembimbing_1_request_nama)): ?>
                                                <span class="text-muted"><?php echo htmlspecialchars($p->pembimbing_1_request_nama); ?></span>
                                                <br><small class="text-info">(Diminta)</small>
                                            <?php else: ?>
                                                <span class="text-muted">-</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php if (!empty($p->pembimbing_2_final_nama)): ?>
                                                <strong><?php echo htmlspecialchars($p->pembimbing_2_final_nama); ?></strong>
                                            <?php elseif (!empty($p->pembimbing_2_request_nama)): ?>
                                                <span class="text-muted"><?php echo htmlspecialchars($p->pembimbing_2_request_nama); ?></span>
                                                <br><small class="text-info">(Diminta)</small>
                                            <?php else: ?>
                                                <span class="text-muted">-</span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="text-center">
                                            <div class="btn-group btn-group-sm" role="group">
                                                <a href="<?php echo site_url('Judul_skripsi/detail/' . $p->id); ?>" 
                                                   class="btn btn-primary" 
                                                   title="Detail & Setting">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                <a href="<?php echo site_url('Judul_skripsi/delete/' . $p->id); ?>" 
                                                   class="btn btn-danger" 
                                                   title="Hapus"
                                                   onclick="return confirm('Apakah Anda yakin ingin menghapus pengajuan judul skripsi ini?');">
                                                    <i class="fas fa-trash"></i>
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>

                    <?php if (!empty($pagination)): ?>
                        <div class="row mt-3">
                            <div class="col-md-6">
                                <p class="text-muted">Menampilkan <?php echo count($pengajuan_list); ?> dari <?php echo $total_rows; ?> data</p>
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
    const searchInput = document.getElementById('searchJudulSkripsi');
    if (searchInput) {
        const table = document.getElementById('tableJudulSkripsi');
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

