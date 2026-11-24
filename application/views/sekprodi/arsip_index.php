<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Arsip Berkas Masuk & Keluar</h3>
                <div class="card-tools">
                    <?php if (get_user_role() !== 'kaprodi'): ?>
                    <a href="<?php echo site_url('Arsip/create'); ?>" class="btn btn-primary btn-sm">
                        <i class="fas fa-plus"></i> Tambah Arsip
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

                <!-- Filter -->
                <div class="card mb-3">
                    <div class="card-body">
                        <form method="get" action="<?php echo site_url('Arsip/index'); ?>" class="form-inline">
                            <div class="form-group mr-2">
                                <label for="jenis" class="mr-2">Jenis:</label>
                                <select name="jenis" id="jenis" class="form-control form-control-sm">
                                    <option value="">-- Semua Jenis --</option>
                                    <option value="masuk" <?php echo ($jenis_filter == 'masuk') ? 'selected' : ''; ?>>Berkas Masuk</option>
                                    <option value="keluar" <?php echo ($jenis_filter == 'keluar') ? 'selected' : ''; ?>>Berkas Keluar</option>
                                </select>
                            </div>
                            <div class="form-group mr-2">
                                <label for="tanggal_dari" class="mr-2">Dari Tanggal:</label>
                                <input type="date" name="tanggal_dari" id="tanggal_dari" 
                                       class="form-control form-control-sm" 
                                       value="<?php echo $tanggal_dari ? $tanggal_dari : ''; ?>">
                            </div>
                            <div class="form-group mr-2">
                                <label for="tanggal_sampai" class="mr-2">Sampai Tanggal:</label>
                                <input type="date" name="tanggal_sampai" id="tanggal_sampai" 
                                       class="form-control form-control-sm" 
                                       value="<?php echo $tanggal_sampai ? $tanggal_sampai : ''; ?>">
                            </div>
                            <button type="submit" class="btn btn-primary btn-sm mr-2">
                                <i class="fas fa-filter"></i> Filter
                            </button>
                            <?php if ($jenis_filter || $tanggal_dari || $tanggal_sampai): ?>
                                <a href="<?php echo site_url('Arsip'); ?>" class="btn btn-secondary btn-sm">
                                    <i class="fas fa-times"></i> Reset
                                </a>
                            <?php endif; ?>
                        </form>
                    </div>
                </div>
                <!-- Show Entries -->
                <div class="row mb-3">
                    <div class="col-md-4 offset-md-8">
                        <form method="get" action="<?php echo site_url('Arsip/index'); ?>" id="formPerPageArsip" class="form-inline justify-content-end">
                            <?php if ($jenis_filter): ?>
                                <input type="hidden" name="jenis" value="<?php echo htmlspecialchars($jenis_filter); ?>">
                            <?php endif; ?>
                            <?php if ($tanggal_dari): ?>
                                <input type="hidden" name="tanggal_dari" value="<?php echo htmlspecialchars($tanggal_dari); ?>">
                            <?php endif; ?>
                            <?php if ($tanggal_sampai): ?>
                                <input type="hidden" name="tanggal_sampai" value="<?php echo htmlspecialchars($tanggal_sampai); ?>">
                            <?php endif; ?>
                            <label for="per_page_arsip" class="mr-2">Tampilkan:</label>
                            <select name="per_page" id="per_page_arsip" class="form-control form-control-sm" onchange="document.getElementById('formPerPageArsip').submit();">
                                <option value="10" <?php echo (isset($per_page) && $per_page == 10) ? 'selected' : ''; ?>>10</option>
                                <option value="25" <?php echo (isset($per_page) && $per_page == 25) ? 'selected' : ''; ?>>25</option>
                                <option value="50" <?php echo (isset($per_page) && $per_page == 50) ? 'selected' : ''; ?>>50</option>
                                <option value="100" <?php echo (isset($per_page) && $per_page == 100) ? 'selected' : ''; ?>>100</option>
                            </select>
                            <span class="ml-2">data</span>
                        </form>
                    </div>
                </div>

                <!-- Search Box -->
                <div class="row mb-3">
                    <div class="col-md-4">
                        <div class="input-group">
                            <div class="input-group-prepend">
                                <span class="input-group-text"><i class="fas fa-search"></i></span>
                            </div>
                            <input type="text" id="searchArsip" class="form-control" placeholder="Cari Nomor Surat, Perihal, Pengirim/Penerima...">
                        </div>
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="table table-bordered table-striped table-hover" id="tableArsip">
                        <thead>
                            <tr>
                                <th width="5%">No</th>
                                <th width="10%">Jenis</th>
                                <th width="12%">Nomor Surat</th>
                                <th width="10%">Tanggal Surat</th>
                                <th width="15%">Pengirim/Penerima</th>
                                <th>Perihal</th>
                                <th width="12%">File</th>
                                <th width="12%" class="text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($arsip_list)): ?>
                                <tr>
                                    <td colspan="8" class="text-center text-muted">
                                        <i class="fas fa-inbox"></i> Belum ada arsip berkas.
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php 
                                $page = ($this->uri->segment(3)) ? $this->uri->segment(3) : 1;
                                $no = ($page - 1) * (isset($per_page) ? $per_page : 10) + 1;
                                foreach ($arsip_list as $arsip): 
                                ?>
                                    <tr>
                                        <td><?php echo $no++; ?></td>
                                        <td>
                                            <?php if ($arsip->jenis === 'masuk'): ?>
                                                <span class="badge badge-success">Masuk</span>
                                            <?php else: ?>
                                                <span class="badge badge-primary">Keluar</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php echo $arsip->nomor_surat ? htmlspecialchars($arsip->nomor_surat) : '<span class="text-muted">-</span>'; ?>
                                        </td>
                                        <td><?php echo date('d/m/Y', strtotime($arsip->tanggal_surat)); ?></td>
                                        <td>
                                            <?php if ($arsip->jenis === 'masuk'): ?>
                                                <small><strong>Dari:</strong><br><?php echo htmlspecialchars($arsip->pengirim); ?></small>
                                            <?php else: ?>
                                                <small><strong>Kepada:</strong><br><?php echo htmlspecialchars($arsip->penerima); ?></small>
                                            <?php endif; ?>
                                        </td>
                                        <td><?php echo htmlspecialchars($arsip->perihal); ?></td>
                                        <td>
                                            <?php if (!empty($arsip->file_path)): ?>
                                                <?php if (strpos($arsip->file_path, 'http') === 0): ?>
                                                    <!-- Link Google Drive -->
                                                    <small>
                                                        <a href="<?php echo htmlspecialchars($arsip->file_path); ?>" target="_blank" class="text-primary">
                                                            <i class="fas fa-external-link-alt"></i> <strong><?php echo htmlspecialchars($arsip->file_name); ?></strong>
                                                        </a>
                                                        <br><span class="text-muted">Link Google Drive</span>
                                                    </small>
                                                <?php else: ?>
                                                    <!-- File lokal (backward compatibility) -->
                                                    <small>
                                                        <i class="fas fa-file"></i> <?php echo htmlspecialchars($arsip->file_name); ?><br>
                                                        <span class="text-muted"><?php echo $arsip->file_type; ?> (<?php echo number_format($arsip->file_size / 1024, 2); ?> KB)</span>
                                                    </small>
                                                <?php endif; ?>
                                            <?php else: ?>
                                                <span class="text-muted">-</span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="text-center">
                                            <a href="<?php echo site_url('Arsip/detail/' . $arsip->id); ?>" class="btn btn-info btn-sm" title="Detail">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <?php if (!empty($arsip->file_path) && strpos($arsip->file_path, 'http') === 0): ?>
                                                <a href="<?php echo htmlspecialchars($arsip->file_path); ?>" target="_blank" class="btn btn-success btn-sm" title="Buka Link">
                                                    <i class="fas fa-external-link-alt"></i>
                                                </a>
                                            <?php else: ?>
                                                <a href="<?php echo site_url('Arsip/download/' . $arsip->id); ?>" class="btn btn-success btn-sm" title="Download">
                                                    <i class="fas fa-download"></i>
                                                </a>
                                            <?php endif; ?>
                                            <?php if (get_user_role() !== 'kaprodi'): ?>
                                            <a href="<?php echo site_url('Arsip/edit/' . $arsip->id); ?>" class="btn btn-warning btn-sm" title="Edit">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <a href="<?php echo site_url('Arsip/delete/' . $arsip->id); ?>" 
                                               class="btn btn-danger btn-sm" 
                                               title="Hapus"
                                               onclick="return confirm('Hapus arsip ini?')">
                                                <i class="fas fa-trash"></i>
                                            </a>
                                            <?php endif; ?>
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
                                Menampilkan <?php echo count($arsip_list); ?> dari <?php echo $total_rows; ?> data
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
    const searchInput = document.getElementById('searchArsip');
    if (searchInput) {
        const table = document.getElementById('tableArsip');
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

