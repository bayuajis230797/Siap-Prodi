<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Detail Permintaan Surat</h3>
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

                <div class="row">
                    <div class="col-md-6">
                        <table class="table table-borderless">
                            <tr>
                                <th width="40%">Jenis Surat:</th>
                                <td><strong><?php echo htmlspecialchars($permintaan->jenis_surat); ?></strong></td>
                            </tr>
                            <tr>
                                <th>Keperluan:</th>
                                <td><?php echo nl2br(htmlspecialchars($permintaan->keperluan)); ?></td>
                            </tr>
                            <tr>
                                <th>Tipe Surat:</th>
                                <td>
                                    <?php
                                    $tipe_label = '';
                                    $tipe_badge = 'secondary';
                                    if (isset($permintaan->tipe_surat)) {
                                        if ($permintaan->tipe_surat == 'soft_copy') {
                                            $tipe_label = 'Soft Copy (File Digital)';
                                            $tipe_badge = 'info';
                                        } elseif ($permintaan->tipe_surat == 'hard_copy') {
                                            $tipe_label = 'Hard Copy (Cetak Fisik)';
                                            $tipe_badge = 'warning';
                                        } else {
                                            $tipe_label = '-';
                                        }
                                    } else {
                                        $tipe_label = '-';
                                    }
                                    ?>
                                    <span class="badge badge-<?php echo $tipe_badge; ?> badge-lg"><?php echo $tipe_label; ?></span>
                                </td>
                            </tr>
                            <tr>
                                <th>Status:</th>
                                <td>
                                    <?php
                                    $status_class = 'secondary';
                                    $status_text = ucfirst($permintaan->status);
                                    switch ($permintaan->status) {
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
                                    <span class="badge badge-<?php echo $status_class; ?> badge-lg"><?php echo $status_text; ?></span>
                                </td>
                            </tr>
                            <tr>
                                <th>Tanggal Pengajuan:</th>
                                <td><?php echo date('d/m/Y H:i', strtotime($permintaan->created_at)); ?></td>
                            </tr>
                            <?php if ($permintaan->updated_at != $permintaan->created_at): ?>
                                <tr>
                                    <th>Terakhir Diupdate:</th>
                                    <td><?php echo date('d/m/Y H:i', strtotime($permintaan->updated_at)); ?></td>
                                </tr>
                            <?php endif; ?>
                        </table>
                    </div>
                    <div class="col-md-6">
                        <?php if ($permintaan->catatan_sekprodi): ?>
                            <div class="card card-info">
                                <div class="card-header">
                                    <h5 class="card-title mb-0">
                                        <i class="fas fa-comment"></i> Catatan dari Sekprodi
                                    </h5>
                                </div>
                                <div class="card-body">
                                    <p class="mb-0"><?php echo nl2br(htmlspecialchars($permintaan->catatan_sekprodi)); ?></p>
                                </div>
                            </div>
                        <?php endif; ?>

                        <?php if ($permintaan->status == 'selesai' && $permintaan->file_surat): ?>
                            <div class="card card-success">
                                <div class="card-header">
                                    <h5 class="card-title mb-0">
                                        <i class="fas fa-file"></i> File Surat
                                    </h5>
                                </div>
                                <div class="card-body">
                                    <p><strong>Nama File:</strong> <?php echo htmlspecialchars($permintaan->nama_file_surat); ?></p>
                                    <?php 
                                    $tipe_surat = isset($permintaan->tipe_surat) ? $permintaan->tipe_surat : 'soft_copy';
                                    if ($tipe_surat == 'soft_copy'): 
                                    ?>
                                        <p class="text-muted"><small>File digital surat Anda. Silakan download.</small></p>
                                    <?php else: ?>
                                        <p class="text-muted"><small>File arsip digital. Surat fisik sudah dicetak. Download file ini sebagai backup digital jika diperlukan.</small></p>
                                    <?php endif; ?>
                                    <a href="<?php echo site_url('Permintaan_surat/download/' . $permintaan->id); ?>" 
                                       class="btn btn-success btn-block">
                                        <i class="fas fa-download"></i> Download Surat
                                    </a>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="mt-3">
                    <a href="<?php echo site_url('Permintaan_surat'); ?>" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i> Kembali
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>










