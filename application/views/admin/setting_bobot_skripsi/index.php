<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Setting Bobot Nilai Akhir Skripsi</h3>
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

                <p class="text-muted">Atur bobot perhitungan nilai akhir Skripsi</p>
                <div class="table-responsive">
                    <table class="table table-bordered table-sm">
                        <thead>
                            <tr>
                                <th>Komponen</th>
                                <th>Bobot (%)</th>
                                <th>Keterangan</th>
                                <th>Status</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($bobot_skripsi)): ?>
                                <tr>
                                    <td colspan="5" class="text-center">Tidak ada data bobot.</td>
                                </tr>
                            <?php else: ?>
                                <?php 
                                $total_bobot = 0;
                                foreach ($bobot_skripsi as $bobot): 
                                    $total_bobot += $bobot->bobot;
                                ?>
                                    <tr>
                                        <td><strong><?php echo ucfirst(str_replace('_', ' ', $bobot->komponen)); ?></strong></td>
                                        <td><?php echo number_format($bobot->bobot, 2); ?>%</td>
                                        <td><?php echo $bobot->keterangan; ?></td>
                                        <td>
                                            <?php if ($bobot->is_aktif): ?>
                                                <span class="badge badge-success">Aktif</span>
                                            <?php else: ?>
                                                <span class="badge badge-secondary">Tidak Aktif</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <a href="<?php echo site_url('Setting_bobot_skripsi/edit_bobot/' . $bobot->id); ?>" class="btn btn-sm btn-warning">
                                                <i class="fas fa-edit"></i> Edit
                                            </a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                                <tr class="table-info">
                                    <td><strong>Total</strong></td>
                                    <td><strong><?php echo number_format($total_bobot, 2); ?>%</strong></td>
                                    <td colspan="3">
                                        <?php if ($total_bobot != 100): ?>
                                            <span class="badge badge-warning">Total harus 100%</span>
                                        <?php else: ?>
                                            <span class="badge badge-success">Total: 100%</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
                <div class="alert alert-info mt-3">
                    <strong>Catatan:</strong> Nilai akhir Skripsi = (Bobot Bimbingan × Nilai Bimbingan) + (Bobot Sidang × Nilai Sidang)
                </div>
            </div>
        </div>
    </div>
</div>







