<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Berkas Pendaftaran Sidang</h3>
                <div class="card-tools">
                    <a href="<?php echo site_url('Berkas_sidang/create'); ?>" class="btn btn-primary btn-sm">
                        <i class="fas fa-plus"></i> Tambah Berkas
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

                <div class="table-responsive">
                    <table class="table table-bordered table-striped table-hover">
                        <thead>
                            <tr>
                                <th width="5%">No</th>
                                <th>Nama Berkas</th>
                                <th>Deskripsi</th>
                                <th width="10%" class="text-center">Wajib</th>
                                <th width="8%" class="text-center">Urutan</th>
                                <th width="8%" class="text-center">Status</th>
                                <th width="20%" class="text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($berkas_list)): ?>
                                <tr>
                                    <td colspan="7" class="text-center text-muted">
                                        <i class="fas fa-inbox"></i> Belum ada berkas yang ditentukan.
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php $no = 1; foreach ($berkas_list as $berkas): ?>
                                    <tr>
                                        <td><?php echo $no++; ?></td>
                                        <td><strong><?php echo $berkas->nama_berkas; ?></strong></td>
                                        <td><?php echo $berkas->deskripsi ?: '-'; ?></td>
                                        <td class="text-center">
                                            <?php if ($berkas->wajib): ?>
                                                <span class="badge badge-danger">Wajib</span>
                                            <?php else: ?>
                                                <span class="badge badge-secondary">Opsional</span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="text-center"><?php echo $berkas->urutan; ?></td>
                                        <td class="text-center">
                                            <?php if ($berkas->aktif): ?>
                                                <span class="badge badge-success">Aktif</span>
                                            <?php else: ?>
                                                <span class="badge badge-secondary">Nonaktif</span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="text-center">
                                            <a href="<?php echo site_url('Berkas_sidang/edit/' . $berkas->id); ?>" class="btn btn-sm btn-warning" title="Edit">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <a href="<?php echo site_url('Berkas_sidang/delete/' . $berkas->id); ?>" class="btn btn-sm btn-danger" title="Hapus" onclick="return confirm('Apakah Anda yakin ingin menghapus berkas ini?');">
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












