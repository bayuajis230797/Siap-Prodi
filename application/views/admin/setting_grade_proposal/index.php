<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Setting Grade Proposal TA</h3>
                <div class="card-tools">
                    <a href="<?php echo site_url('Setting_grade_proposal/create_grade'); ?>" class="btn btn-primary btn-sm">
                        <i class="fas fa-plus"></i> Tambah Grade
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

                <p class="text-muted">Atur range nilai untuk setiap grade (A, B, C, D, E, AB, -A, A-, dll)</p>
                <div class="table-responsive">
                    <table class="table table-bordered table-sm">
                        <thead>
                            <tr>
                                <th>Grade</th>
                                <th>Nilai Min</th>
                                <th>Nilai Max</th>
                                <th>Keterangan</th>
                                <th>Status</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($grade_proposal)): ?>
                                <tr>
                                    <td colspan="6" class="text-center">Tidak ada data grade.</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($grade_proposal as $grade): ?>
                                    <tr>
                                        <td><strong><?php echo $grade->grade; ?></strong></td>
                                        <td><?php echo number_format($grade->nilai_min, 2); ?></td>
                                        <td><?php echo $grade->nilai_max ? number_format($grade->nilai_max, 2) : '∞'; ?></td>
                                        <td><?php echo $grade->keterangan; ?></td>
                                        <td>
                                            <?php if ($grade->is_aktif): ?>
                                                <span class="badge badge-success">Aktif</span>
                                            <?php else: ?>
                                                <span class="badge badge-secondary">Tidak Aktif</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <a href="<?php echo site_url('Setting_grade_proposal/edit_grade/' . $grade->id); ?>" class="btn btn-sm btn-warning" title="Edit">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <?php echo form_open('Setting_grade_proposal/delete_grade/' . $grade->id, array('class' => 'd-inline', 'onsubmit' => "return confirm('Hapus data ini?');")); ?><button type="submit" class="btn btn-danger btn-sm" title="Hapus"><i class="fas fa-trash"></i></button><?php echo form_close(); ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
                <div class="alert alert-info mt-3">
                    <strong>Catatan:</strong> Nilai akhir Proposal TA = (Bobot Bimbingan × Nilai Bimbingan) + (Bobot Sidang × Nilai Sidang)
                </div>
            </div>
        </div>
    </div>
</div>







