<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Pendaftaran Sidang Skripsi</h3>
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

                <?php if ($tahun_aktif): ?>
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle"></i>
                        <strong>Tahun Akademik:</strong> <?php echo $tahun_aktif->tahun_akademik . ' ' . ucfirst($tahun_aktif->semester); ?>
                    </div>
                <?php endif; ?>

                <?php if ($pendaftaran): ?>
                    <div class="alert alert-info">
                        <h5><i class="fas fa-info-circle"></i> Status Pendaftaran</h5>
                        <p class="mb-0">
                            <strong>Status:</strong> 
                            <?php
                            $status_class = 'secondary';
                            $status_text = ucfirst($pendaftaran->status);
                            switch ($pendaftaran->status) {
                                case 'menunggu':
                                    $status_class = 'warning';
                                    break;
                                case 'disetujui':
                                    $status_class = 'success';
                                    break;
                                case 'ditolak':
                                    $status_class = 'danger';
                                    break;
                                case 'selesai':
                                    $status_class = 'info';
                                    break;
                            }
                            ?>
                            <span class="badge badge-<?php echo $status_class; ?>"><?php echo $status_text; ?></span>
                        </p>
                        <?php if ($pendaftaran->catatan_sekprodi): ?>
                            <p class="mb-0 mt-2"><strong>Catatan Sekprodi:</strong> <?php echo nl2br(htmlspecialchars($pendaftaran->catatan_sekprodi)); ?></p>
                        <?php endif; ?>
                    </div>

                    <?php if (!empty($berkas_uploaded)): ?>
                        <h5 class="mb-3"><i class="fas fa-file-alt"></i> Berkas yang Sudah Diupload</h5>
                        <div class="table-responsive mb-4">
                            <table class="table table-bordered">
                                <thead>
                                    <tr>
                                        <th>Nama Berkas</th>
                                        <th>File</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($berkas_uploaded as $berkas): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($berkas->nama_berkas); ?></td>
                                            <td>
                                                <?php if (!empty($berkas->file_path)): ?>
                                                    <?php if (strpos($berkas->file_path, 'http') === 0): ?>
                                                        <!-- Link Google Drive -->
                                                        <a href="<?php echo htmlspecialchars($berkas->file_path); ?>" target="_blank" class="text-primary">
                                                            <i class="fas fa-external-link-alt"></i> <strong><?php echo htmlspecialchars($berkas->file_name); ?></strong>
                                                        </a>
                                                    <?php else: ?>
                                                        <!-- File lokal (backward compatibility) -->
                                                        <a href="<?php echo base_url($berkas->file_path); ?>" target="_blank" class="text-info">
                                                            <i class="fas fa-file-alt"></i> <?php echo htmlspecialchars($berkas->file_name); ?>
                                                        </a>
                                                    <?php endif; ?>
                                                <?php else: ?>
                                                    <span class="text-muted">-</span>
                                                <?php endif; ?>
                                                <?php if ($berkas->wajib): ?>
                                                    <span class="badge badge-danger ml-2">Wajib</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <?php
                                                $status_class = 'secondary';
                                                switch ($berkas->status) {
                                                    case 'approved':
                                                        $status_class = 'success';
                                                        break;
                                                    case 'rejected':
                                                        $status_class = 'danger';
                                                        break;
                                                    default:
                                                        $status_class = 'warning';
                                                }
                                                ?>
                                                <span class="badge badge-<?php echo $status_class; ?>">
                                                    <?php echo ucfirst($berkas->status); ?>
                                                </span>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>

                    <?php if ($pendaftaran->status === 'ditolak'): ?>
                        <div class="alert alert-warning">
                            <p>Pendaftaran Anda ditolak. Silakan perbaiki berkas dan ajukan kembali.</p>
                        </div>
                    <?php endif; ?>
                <?php endif; ?>

                <?php if (!$pendaftaran || $pendaftaran->status === 'ditolak'): ?>
                    <?php echo form_open_multipart('Pendaftaran_sidang_mahasiswa/submit'); ?>
                        <h5 class="mb-3"><i class="fas fa-link"></i> Link Berkas Pendaftaran Sidang</h5>
                        <p class="text-muted mb-4">Silakan upload berkas ke Google Drive terlebih dahulu, lalu masukkan link-nya di sini. Semua berkas wajib harus diisi.</p>
                        
                        <?php if (empty($berkas_list)): ?>
                            <div class="alert alert-warning">
                                <i class="fas fa-exclamation-triangle"></i> Belum ada berkas yang ditentukan. Silakan hubungi Sekprodi.
                            </div>
                        <?php else: ?>
                            <?php foreach ($berkas_list as $berkas): ?>
                                <?php
                                $berkas_uploaded_item = null;
                                if (!empty($berkas_uploaded)) {
                                    foreach ($berkas_uploaded as $bu) {
                                        if ($bu->id_berkas_sidang == $berkas->id) {
                                            $berkas_uploaded_item = $bu;
                                            break;
                                        }
                                    }
                                }
                                ?>
                                <div class="form-group">
                                    <label for="file_berkas_<?php echo $berkas->id; ?>">
                                        <?php echo htmlspecialchars($berkas->nama_berkas); ?>
                                        <?php if (isset($berkas->wajib) && $berkas->wajib): ?>
                                            <span class="text-danger">*</span>
                                        <?php endif; ?>
                                    </label>
                                    <?php if ($berkas->deskripsi): ?>
                                        <small class="form-text text-muted mb-2 d-block"><?php echo htmlspecialchars($berkas->deskripsi); ?></small>
                                    <?php endif; ?>
                                    <?php if ($berkas_uploaded_item): ?>
                                        <div class="alert alert-info mb-2">
                                            <i class="fas fa-check-circle"></i> Link terisi: 
                                            <?php if (strpos($berkas_uploaded_item->file_path, 'http') === 0): ?>
                                                <a href="<?php echo htmlspecialchars($berkas_uploaded_item->file_path); ?>" target="_blank" class="text-primary">
                                                    <strong><?php echo htmlspecialchars($berkas_uploaded_item->file_name); ?></strong>
                                                </a>
                                            <?php else: ?>
                                                <strong><?php echo htmlspecialchars($berkas_uploaded_item->file_name); ?></strong>
                                            <?php endif; ?>
                                            <br><small>Masukkan link baru untuk mengganti link yang sudah ada.</small>
                                        </div>
                                    <?php endif; ?>
                                    <input type="file" name="file_berkas_<?php echo $berkas->id; ?>" id="file_berkas_<?php echo $berkas->id; ?>"
                                           class="form-control-file" accept=".pdf,.doc,.docx,.zip,.rar,.jpg,.jpeg,.png"
                                           <?php echo (isset($berkas->wajib) && $berkas->wajib && !$berkas_uploaded_item) ? 'required' : ''; ?>>
                                    <small class="form-text text-muted">
                                        <i class="fas fa-info-circle"></i> Diunggah ke Google Drive institusi. Kosongkan jika tidak mengganti berkas yang sudah ada.
                                        <br>File disimpan ke Google Drive institusi (dikelola admin).
                                    </small>
                                </div>
                            <?php endforeach; ?>
                            
                            <div class="form-group">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-paper-plane"></i> <?php echo $pendaftaran ? 'Update Pendaftaran' : 'Kirim Pendaftaran'; ?>
                                </button>
                            </div>
                        <?php endif; ?>
                    <?php echo form_close(); ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

