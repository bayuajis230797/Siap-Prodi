<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Setting Berkas Sidang Skripsi</h3>
                <div class="card-tools">
                    <a href="<?php echo site_url('Skripsi_berkas_sidang_setting/create'); ?>" class="btn btn-primary btn-sm">
                        <i class="fas fa-plus"></i> Tambah Berkas Sidang
                    </a>
                </div>
            </div>
            <div class="card-body">
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

                <!-- Filter -->
                <form method="get" action="<?php echo site_url('Skripsi_berkas_sidang_setting/index'); ?>" class="mb-3">
                    <div class="row">
                        <div class="col-md-3">
                            <select name="aktif" class="form-control">
                                <option value="">Semua Status</option>
                                <option value="1" <?php echo ($aktif_filter === '1') ? 'selected' : ''; ?>>Aktif</option>
                                <option value="0" <?php echo ($aktif_filter === '0') ? 'selected' : ''; ?>>Tidak Aktif</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <button type="submit" class="btn btn-info">
                                <i class="fas fa-filter"></i> Filter
                            </button>
                            <a href="<?php echo site_url('Skripsi_berkas_sidang_setting/index'); ?>" class="btn btn-secondary">
                                <i class="fas fa-times"></i> Reset
                            </a>
                        </div>
                    </div>
                </form>
                
                <div class="table-responsive">
                    <table class="table table-bordered table-striped">
                        <thead>
                            <tr>
                                <th width="5%">No</th>
                                <th>Nama Berkas</th>
                                <th>Deskripsi</th>
                                <th width="10%">Wajib</th>
                                <th width="8%">Urutan</th>
                                <th width="8%">Status</th>
                                <th width="20%">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($berkas_list)): ?>
                                <tr>
                                    <td colspan="7" class="text-center">Tidak ada data berkas sidang.</td>
                                </tr>
                            <?php else: ?>
                                <?php $no = 1; foreach ($berkas_list as $berkas): ?>
                                    <tr>
                                        <td><?php echo $no++; ?></td>
                                        <td><strong><?php echo htmlspecialchars($berkas->nama_berkas); ?></strong></td>
                                        <td><?php echo nl2br(htmlspecialchars($berkas->deskripsi ?: '-')); ?></td>
                                        <td>
                                            <?php if ($berkas->wajib): ?>
                                                <span class="badge badge-danger">Wajib</span>
                                            <?php else: ?>
                                                <span class="badge badge-info">Opsional</span>
                                            <?php endif; ?>
                                        </td>
                                        <td><?php echo $berkas->urutan; ?></td>
                                        <td>
                                            <?php if ($berkas->aktif): ?>
                                                <span class="badge badge-success">Aktif</span>
                                            <?php else: ?>
                                                <span class="badge badge-secondary">Tidak Aktif</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <a href="<?php echo site_url('Skripsi_berkas_sidang_setting/edit/' . $berkas->id); ?>" 
                                               class="btn btn-warning btn-sm" 
                                               title="Edit">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <a href="<?php echo site_url('Skripsi_berkas_sidang_setting/toggle_aktif/' . $berkas->id); ?>" 
                                               class="btn btn-<?php echo $berkas->aktif ? 'secondary' : 'success'; ?> btn-sm" 
                                               title="<?php echo $berkas->aktif ? 'Nonaktifkan' : 'Aktifkan'; ?>"
                                               onclick="return confirm('<?php echo $berkas->aktif ? 'Nonaktifkan' : 'Aktifkan'; ?> berkas sidang ini?')">
                                                <i class="fas fa-<?php echo $berkas->aktif ? 'times' : 'check'; ?>"></i>
                                            </a>
                                            <a href="<?php echo site_url('Skripsi_berkas_sidang_setting/delete/' . $berkas->id); ?>" 
                                               class="btn btn-danger btn-sm" 
                                               title="Hapus"
                                               onclick="return confirm('Hapus berkas sidang ini? Pastikan berkas belum digunakan dalam pendaftaran sidang.')">
                                                <i class="fas fa-trash"></i>
                                            </a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

