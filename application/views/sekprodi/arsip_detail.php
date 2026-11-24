<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Detail Arsip Berkas</h3>
                <div class="card-tools">
                    <a href="<?php echo site_url('Arsip'); ?>" class="btn btn-secondary btn-sm">
                        <i class="fas fa-arrow-left"></i> Kembali
                    </a>
                </div>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-8">
                        <table class="table table-bordered">
                            <tr>
                                <th width="30%">Jenis Berkas</th>
                                <td>
                                    <?php if ($arsip->jenis === 'masuk'): ?>
                                        <span class="badge badge-success">Berkas Masuk</span>
                                    <?php else: ?>
                                        <span class="badge badge-primary">Berkas Keluar</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php if ($arsip->nomor_surat): ?>
                            <tr>
                                <th>Nomor Surat</th>
                                <td><?php echo htmlspecialchars($arsip->nomor_surat); ?></td>
                            </tr>
                            <?php endif; ?>
                            <tr>
                                <th>Tanggal Surat</th>
                                <td><?php echo date('d F Y', strtotime($arsip->tanggal_surat)); ?></td>
                            </tr>
                            <?php if ($arsip->jenis === 'masuk'): ?>
                            <tr>
                                <th>Pengirim</th>
                                <td><?php echo htmlspecialchars($arsip->pengirim); ?></td>
                            </tr>
                            <?php else: ?>
                            <tr>
                                <th>Penerima</th>
                                <td><?php echo htmlspecialchars($arsip->penerima); ?></td>
                            </tr>
                            <?php endif; ?>
                            <tr>
                                <th>Perihal</th>
                                <td><?php echo nl2br(htmlspecialchars($arsip->perihal)); ?></td>
                            </tr>
                            <?php if ($arsip->keterangan): ?>
                            <tr>
                                <th>Keterangan</th>
                                <td><?php echo nl2br(htmlspecialchars($arsip->keterangan)); ?></td>
                            </tr>
                            <?php endif; ?>
                            <tr>
                                <th>Link Berkas</th>
                                <td>
                                    <?php if (!empty($arsip->file_path)): ?>
                                        <?php if (strpos($arsip->file_path, 'http') === 0): ?>
                                            <!-- Link Google Drive -->
                                            <div class="alert alert-info mb-0">
                                                <i class="fas fa-link"></i> 
                                                <a href="<?php echo htmlspecialchars($arsip->file_path); ?>" target="_blank" class="text-primary">
                                                    <strong>Buka Link Google Drive</strong>
                                                </a>
                                                <br>
                                                <small class="text-muted"><?php echo htmlspecialchars($arsip->file_path); ?></small>
                                                <?php if (!empty($arsip->file_name)): ?>
                                                    <br><small><strong>Nama File:</strong> <?php echo htmlspecialchars($arsip->file_name); ?></small>
                                                <?php endif; ?>
                                            </div>
                                        <?php else: ?>
                                            <!-- File lokal (backward compatibility) -->
                                            <div class="alert alert-info mb-0">
                                                <i class="fas fa-file"></i> <strong><?php echo htmlspecialchars($arsip->file_name); ?></strong><br>
                                                <small>
                                                    Format: <?php echo $arsip->file_type; ?><br>
                                                    Ukuran: <?php echo number_format($arsip->file_size / 1024, 2); ?> KB
                                                </small>
                                            </div>
                                        <?php endif; ?>
                                    <?php else: ?>
                                        <div class="alert alert-warning mb-0">
                                            <i class="fas fa-exclamation-triangle"></i> Belum ada link berkas
                                        </div>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <tr>
                                <th>Diinput Oleh</th>
                                <td><?php echo htmlspecialchars($arsip->created_by_username); ?></td>
                            </tr>
                            <tr>
                                <th>Tanggal Input</th>
                                <td><?php echo date('d F Y H:i', strtotime($arsip->created_at)); ?></td>
                            </tr>
                        </table>
                    </div>
                    <div class="col-md-4">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="card-title mb-0">Aksi</h5>
                            </div>
                            <div class="card-body">
                                <?php if (!empty($arsip->file_path) && strpos($arsip->file_path, 'http') === 0): ?>
                                    <a href="<?php echo htmlspecialchars($arsip->file_path); ?>" target="_blank" class="btn btn-success btn-block mb-2">
                                        <i class="fas fa-external-link-alt"></i> Buka Link
                                    </a>
                                <?php else: ?>
                                    <a href="<?php echo site_url('Arsip/download/' . $arsip->id); ?>" class="btn btn-success btn-block mb-2">
                                        <i class="fas fa-download"></i> Download File
                                    </a>
                                <?php endif; ?>
                                <?php if (get_user_role() !== 'kaprodi'): ?>
                                <a href="<?php echo site_url('Arsip/edit/' . $arsip->id); ?>" class="btn btn-warning btn-block mb-2">
                                    <i class="fas fa-edit"></i> Edit Arsip
                                </a>
                                <a href="<?php echo site_url('Arsip/delete/' . $arsip->id); ?>" 
                                   class="btn btn-danger btn-block" 
                                   onclick="return confirm('Hapus arsip ini?')">
                                    <i class="fas fa-trash"></i> Hapus Arsip
                                </a>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>










