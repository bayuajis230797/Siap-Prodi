<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Pengaturan Dosen Pembimbing Skripsi</h3>
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

                <div class="alert alert-info">
                    <i class="fas fa-info-circle"></i>
                    <strong>Petunjuk:</strong> Centang dosen yang diizinkan menjadi Pembimbing 1 dan/atau Pembimbing 2.
                    Tidak semua dosen yang mengajar otomatis bisa menjadi pembimbing skripsi.
                </div>

                <?php echo form_open('Skripsi_pembimbing', array('method' => 'post')); ?>
                <div class="table-responsive">
                    <table class="table table-bordered table-striped table-hover">
                        <thead>
                            <tr>
                                <th width="5%" class="text-center">#</th>
                                <th width="20%">NIDN</th>
                                <th>Nama Dosen</th>
                                <th width="15%" class="text-center">Aktif</th>
                                <th width="15%" class="text-center">Pembimbing 1</th>
                                <th width="15%" class="text-center">Pembimbing 2</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($dosen_list)): ?>
                                <tr>
                                    <td colspan="6" class="text-center text-muted">
                                        <i class="fas fa-inbox"></i> Belum ada data dosen.
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php $no = 1; foreach ($dosen_list as $dosen): 
                                    $perm = isset($permissions[$dosen->id]) ? $permissions[$dosen->id] : null;
                                    $is_active = $perm ? ($perm->is_active == 1) : true;
                                    $allow_p1 = $perm ? ($perm->allowed_pembimbing_1 == 1) : false;
                                    $allow_p2 = $perm ? ($perm->allowed_pembimbing_2 == 1) : false;
                                ?>
                                    <tr>
                                        <td class="text-center"><?php echo $no++; ?></td>
                                        <td><strong><?php echo $dosen->nidn; ?></strong></td>
                                        <td><?php echo $dosen->nama_dosen; ?></td>
                                        <td class="text-center">
                                            <input type="checkbox" name="is_active[]" value="<?php echo $dosen->id; ?>" <?php echo $is_active ? 'checked' : ''; ?>>
                                        </td>
                                        <td class="text-center">
                                            <input type="checkbox" name="pembimbing_1[]" value="<?php echo $dosen->id; ?>" <?php echo ($allow_p1 && $is_active) ? 'checked' : ''; ?>>
                                        </td>
                                        <td class="text-center">
                                            <input type="checkbox" name="pembimbing_2[]" value="<?php echo $dosen->id; ?>" <?php echo ($allow_p2 && $is_active) ? 'checked' : ''; ?>>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>

                <div class="mt-3">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Simpan Pengaturan
                    </button>
                    <a href="<?php echo site_url('Skripsi'); ?>" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i> Kembali
                    </a>
                </div>
                <?php echo form_close(); ?>
            </div>
        </div>
    </div>
</div>













