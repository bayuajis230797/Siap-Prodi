<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Daftar Item Penilaian PKL</h3>
                <div class="card-tools">
                    <a href="<?php echo site_url('Pkl_item_penilaian/create'); ?>" class="btn btn-primary btn-sm">
                        <i class="fas fa-plus"></i> Tambah Item Penilaian
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
                <form method="get" action="<?php echo site_url('Pkl_item_penilaian/index'); ?>" class="mb-3">
                    <div class="row">
                        <div class="col-md-3">
                            <select name="id_aspek_penilaian" class="form-control">
                                <option value="">Semua Aspek</option>
                                <?php foreach ($aspek_list as $aspek): ?>
                                    <option value="<?php echo $aspek->id; ?>" 
                                            <?php echo ($id_aspek_filter == $aspek->id) ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($aspek->kode_aspek . ' - ' . $aspek->nama_aspek); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <select name="is_aktif" class="form-control">
                                <option value="">Semua Status</option>
                                <option value="1" <?php echo ($is_aktif_filter === '1') ? 'selected' : ''; ?>>Aktif</option>
                                <option value="0" <?php echo ($is_aktif_filter === '0') ? 'selected' : ''; ?>>Tidak Aktif</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <button type="submit" class="btn btn-info">
                                <i class="fas fa-filter"></i> Filter
                            </button>
                            <a href="<?php echo site_url('Pkl_item_penilaian/index'); ?>" class="btn btn-secondary">
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
                                <th width="12%">Aspek</th>
                                <th>Pertanyaan</th>
                                <th width="10%">Bobot (%)</th>
                                <th width="8%">Urutan</th>
                                <th width="8%">Status</th>
                                <th width="20%">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($item_list)): ?>
                                <tr>
                                    <td colspan="7" class="text-center">Tidak ada data item penilaian.</td>
                                </tr>
                            <?php else: ?>
                                <?php $no = 1; foreach ($item_list as $item): ?>
                                    <tr>
                                        <td><?php echo $no++; ?></td>
                                        <td>
                                            <strong class="text-primary"><?php echo htmlspecialchars($item->kode_aspek); ?></strong>
                                            <br>
                                            <small class="text-muted"><?php echo htmlspecialchars($item->nama_aspek); ?></small>
                                        </td>
                                        <td><?php echo nl2br(htmlspecialchars($item->pertanyaan)); ?></td>
                                        <td><?php echo number_format($item->bobot, 2); ?>%</td>
                                        <td><?php echo $item->urutan; ?></td>
                                        <td>
                                            <?php if ($item->is_aktif): ?>
                                                <span class="badge badge-success">Aktif</span>
                                            <?php else: ?>
                                                <span class="badge badge-secondary">Tidak Aktif</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <a href="<?php echo site_url('Pkl_item_penilaian/edit/' . $item->id); ?>" 
                                               class="btn btn-warning btn-sm" 
                                               title="Edit">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <a href="<?php echo site_url('Pkl_item_penilaian/toggle_aktif/' . $item->id); ?>" 
                                               class="btn btn-<?php echo $item->is_aktif ? 'secondary' : 'success'; ?> btn-sm" 
                                               title="<?php echo $item->is_aktif ? 'Nonaktifkan' : 'Aktifkan'; ?>"
                                               onclick="return confirm('<?php echo $item->is_aktif ? 'Nonaktifkan' : 'Aktifkan'; ?> item penilaian ini?')">
                                                <i class="fas fa-<?php echo $item->is_aktif ? 'times' : 'check'; ?>"></i>
                                            </a>
                                            <a href="<?php echo site_url('Pkl_item_penilaian/delete/' . $item->id); ?>" 
                                               class="btn btn-danger btn-sm" 
                                               title="Hapus"
                                               onclick="return confirm('Hapus item penilaian ini? Pastikan item belum digunakan dalam penilaian.')">
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





