<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Permintaan Surat Mahasiswa</h3>
                <div class="card-tools">
                    <a href="<?php echo site_url('Permintaan_surat_sekprodi/export_excel'); ?>" class="btn btn-success btn-sm">
                        <i class="fas fa-file-excel"></i> Export Excel
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

                <!-- Filter Status -->
                <div class="card mb-3">
                    <div class="card-body">
                        <form method="get" action="<?php echo site_url('Permintaan_surat_sekprodi/index'); ?>" class="form-inline">
                            <div class="form-group mr-2">
                                <label for="status" class="mr-2">Filter Status:</label>
                                <select name="status" id="status" class="form-control form-control-sm">
                                    <option value="">-- Semua Status --</option>
                                    <option value="pending" <?php echo ($filter_status == 'pending') ? 'selected' : ''; ?>>Pending</option>
                                    <option value="diproses" <?php echo ($filter_status == 'diproses') ? 'selected' : ''; ?>>Diproses</option>
                                    <option value="selesai" <?php echo ($filter_status == 'selesai') ? 'selected' : ''; ?>>Selesai</option>
                                    <option value="ditolak" <?php echo ($filter_status == 'ditolak') ? 'selected' : ''; ?>>Ditolak</option>
                                </select>
                            </div>
                            <button type="submit" class="btn btn-primary btn-sm mr-2">
                                <i class="fas fa-filter"></i> Filter
                            </button>
                            <?php if ($filter_status): ?>
                                <a href="<?php echo site_url('Permintaan_surat_sekprodi/index'); ?>" class="btn btn-secondary btn-sm">
                                    <i class="fas fa-times"></i> Reset
                                </a>
                            <?php endif; ?>
                        </form>
                    </div>
                </div>

                <?php if (empty($permintaan)): ?>
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle"></i> Belum ada permintaan surat.
                    </div>
                <?php else: ?>
                <!-- Search Box dan Show Entries -->
                <div class="row mb-3">
                    <div class="col-md-4">
                        <div class="input-group">
                            <div class="input-group-prepend">
                                <span class="input-group-text"><i class="fas fa-search"></i></span>
                            </div>
                            <input type="text" id="searchPermintaan" class="form-control" placeholder="Cari NIM, Nama, Jenis Surat...">
                        </div>
                    </div>
                    <div class="col-md-4 offset-md-4">
                        <form method="get" action="<?php echo site_url('Permintaan_surat_sekprodi/index'); ?>" id="formPerPagePermintaan" class="form-inline justify-content-end">
                            <?php if ($filter_status): ?>
                                <input type="hidden" name="status" value="<?php echo htmlspecialchars($filter_status); ?>">
                            <?php endif; ?>
                            <label for="per_page_permintaan" class="mr-2">Tampilkan:</label>
                            <select name="per_page" id="per_page_permintaan" class="form-control form-control-sm" onchange="document.getElementById('formPerPagePermintaan').submit();">
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
                        <table class="table table-bordered table-striped table-hover" id="tablePermintaan">
                            <thead>
                                <tr>
                                    <th width="5%">No</th>
                                    <th width="10%">NIM</th>
                                    <th width="15%">Nama Mahasiswa</th>
                                    <th width="13%">Jenis Surat</th>
                                    <th width="17%">Keperluan</th>
                                    <th width="10%">Tipe Surat</th>
                                    <th width="10%" class="text-center">Status</th>
                                    <th width="10%">Tanggal</th>
                                    <th width="10%" class="text-center">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php $no = 1; foreach ($permintaan as $p): ?>
                                    <tr>
                                        <td><?php echo $no++; ?></td>
                                        <td><?php echo htmlspecialchars($p->nim); ?></td>
                                        <td><?php echo htmlspecialchars($p->nama_mahasiswa); ?></td>
                                        <td><?php echo htmlspecialchars($p->jenis_surat); ?></td>
                                        <td>
                                            <?php 
                                            $keperluan = htmlspecialchars($p->keperluan);
                                            echo strlen($keperluan) > 40 ? substr($keperluan, 0, 40) . '...' : $keperluan;
                                            ?>
                                        </td>
                                        <td>
                                            <?php
                                            $tipe_label = '';
                                            $tipe_badge = 'secondary';
                                            if (isset($p->tipe_surat)) {
                                                if ($p->tipe_surat == 'soft_copy') {
                                                    $tipe_label = 'Soft Copy';
                                                    $tipe_badge = 'info';
                                                } elseif ($p->tipe_surat == 'hard_copy') {
                                                    $tipe_label = 'Hard Copy';
                                                    $tipe_badge = 'warning';
                                                } else {
                                                    $tipe_label = '-';
                                                }
                                            } else {
                                                $tipe_label = '-';
                                            }
                                            ?>
                                            <span class="badge badge-<?php echo $tipe_badge; ?>"><?php echo $tipe_label; ?></span>
                                        </td>
                                        <td class="text-center">
                                            <?php
                                            $status_class = 'secondary';
                                            $status_text = ucfirst($p->status);
                                            switch ($p->status) {
                                                case 'pending':
                                                    $status_class = 'warning';
                                                    break;
                                                case 'diproses':
                                                    $status_class = 'info';
                                                    break;
                                                case 'selesai':
                                                    $status_class = 'success';
                                                    break;
                                                case 'ditolak':
                                                    $status_class = 'danger';
                                                    break;
                                            }
                                            ?>
                                            <span class="badge badge-<?php echo $status_class; ?>"><?php echo $status_text; ?></span>
                                        </td>
                                        <td><?php echo date('d/m/Y', strtotime($p->created_at)); ?></td>
                                        <td class="text-center">
                                            <a href="<?php echo site_url('Permintaan_surat_sekprodi/detail/' . $p->id); ?>" 
                                               class="btn btn-sm btn-primary" 
                                               title="Detail & Proses">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    
                    <!-- Pagination -->
                    <?php if (isset($pagination) && !empty($pagination)): ?>
                        <div class="row mt-3">
                            <div class="col-md-6">
                                <p class="text-muted">
                                    Menampilkan <?php echo count($permintaan); ?> dari <?php echo isset($total_rows) ? $total_rows : count($permintaan); ?> data
                                </p>
                            </div>
                            <div class="col-md-6">
                                <div class="text-right">
                                    <?php echo $pagination; ?>
                                </div>
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
    const searchInput = document.getElementById('searchPermintaan');
    if (searchInput) {
        const table = document.getElementById('tablePermintaan');
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

