<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Detail Tagihan Berkas</h3>
                <div class="card-tools">
                    <a href="<?php echo site_url('Tagihan_berkas'); ?>" class="btn btn-secondary btn-sm">
                        <i class="fas fa-arrow-left"></i> Kembali
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

                <!-- Info Tagihan -->
                <div class="card card-info mb-3">
                    <div class="card-header">
                        <h5 class="card-title mb-0">Informasi Tagihan</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <table class="table table-borderless">
                                    <tr>
                                        <th width="40%">Nama Tagihan:</th>
                                        <td><strong><?php echo htmlspecialchars($tagihan->nama_tagihan); ?></strong></td>
                                    </tr>
                                    <tr>
                                        <th>Jenis:</th>
                                        <td>
                                            <?php if ($tagihan->is_hardcopy == 1): ?>
                                                <span class="badge badge-warning">Hardcopy</span>
                                                <small class="text-muted d-block">Silakan kumpulkan berkas fisik ke sekprodi</small>
                                            <?php else: ?>
                                                <span class="badge badge-info">Softfile</span>
                                                <small class="text-muted d-block">Upload file melalui form di bawah</small>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                    <tr>
                                        <th>Deadline:</th>
                                        <td>
                                            <?php if ($tagihan->deadline): ?>
                                                <?php 
                                                    $deadline = strtotime($tagihan->deadline);
                                                    $today = strtotime('today');
                                                    $class = ($deadline < $today) ? 'text-danger' : (($deadline == $today) ? 'text-warning' : '');
                                                ?>
                                                <span class="<?php echo $class; ?>">
                                                    <strong><?php echo date('d/m/Y', $deadline); ?></strong>
                                                    <?php if ($deadline < $today): ?>
                                                        <span class="badge badge-danger ml-2">Terlambat</span>
                                                    <?php elseif ($deadline == $today): ?>
                                                        <span class="badge badge-warning ml-2">Hari Ini</span>
                                                    <?php endif; ?>
                                                </span>
                                            <?php else: ?>
                                                <span class="text-muted">-</span>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                </table>
                            </div>
                            <div class="col-md-6">
                                <table class="table table-borderless">
                                    <tr>
                                        <th width="40%">Status:</th>
                                        <td>
                                            <?php if ($tagihan->is_hardcopy == 1): ?>
                                                <?php if ($detail && $detail->status_hardcopy == 'sudah'): ?>
                                                    <span class="badge badge-success">Sudah Dikumpulkan</span>
                                                <?php else: ?>
                                                    <span class="badge badge-warning">Belum Dikumpulkan</span>
                                                <?php endif; ?>
                                            <?php else: ?>
                                                <?php if ($detail && $detail->status == 'sudah'): ?>
                                                    <span class="badge badge-success">Sudah Upload</span>
                                                <?php else: ?>
                                                    <span class="badge badge-warning">Belum Upload</span>
                                                <?php endif; ?>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                    <?php if ($detail && $detail->uploaded_at): ?>
                                        <tr>
                                            <th>Tanggal Upload:</th>
                                            <td><?php echo date('d/m/Y H:i', strtotime($detail->uploaded_at)); ?></td>
                                        </tr>
                                    <?php endif; ?>
                                    <?php if ($tagihan->deskripsi): ?>
                                        <tr>
                                            <th>Deskripsi:</th>
                                            <td><?php echo nl2br(htmlspecialchars($tagihan->deskripsi)); ?></td>
                                        </tr>
                                    <?php endif; ?>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Upload Form untuk Softfile -->
                <?php if ($tagihan->is_hardcopy == 0): ?>
                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-title mb-0">
                                <i class="fas fa-upload"></i> Upload File
                            </h5>
                        </div>
                        <div class="card-body">
                            <?php if ($detail && $detail->file_name): ?>
                                <div class="alert alert-info">
                                    <strong>File yang sudah diupload:</strong><br>
                                    <?php
                                    $this->load->helper('google_drive');
                                    $is_drive = !empty($detail->file_path) && (is_valid_google_drive_link($detail->file_path) || preg_match('#^https?://#i', $detail->file_path));
                                    ?>
                                    <a href="<?php echo site_url('Tagihan_berkas/download/' . $tagihan->id); ?>"
                                       class="btn btn-sm btn-info mt-2" <?php echo $is_drive ? 'target="_blank" rel="noopener"' : ''; ?>>
                                        <i class="fas fa-<?php echo $is_drive ? 'external-link-alt' : 'download'; ?>"></i>
                                        <?php echo $is_drive ? 'Buka di Google Drive' : 'Download'; ?>:
                                        <?php echo htmlspecialchars($detail->file_name); ?>
                                    </a>
                                    <a href="<?php echo site_url('Tagihan_berkas/delete_file/' . $tagihan->id); ?>" 
                                       class="btn btn-sm btn-danger mt-2"
                                       onclick="return confirm('Yakin ingin menghapus file ini? Anda bisa upload file baru setelah menghapus.');">
                                        <i class="fas fa-trash"></i> Hapus File
                                    </a>
                                    <br><small class="text-muted">Upload file baru untuk mengganti file yang sudah ada.</small>
                                </div>
                            <?php endif; ?>

                            <?php echo form_open_multipart('Tagihan_berkas/upload/' . $tagihan->id); ?>
                                <div class="form-group">
                                    <label for="file_berkas">File Berkas <span class="text-danger">*</span></label>
                                    <div class="custom-file">
                                        <input type="file" 
                                               name="file_berkas" 
                                               id="file_berkas" 
                                               class="custom-file-input" 
                                               required 
                                               accept=".pdf,.doc,.docx,.xls,.xlsx,.jpg,.jpeg,.png">
                                        <label class="custom-file-label" for="file_berkas">Pilih file (PDF, DOC, DOCX, XLS, XLSX, JPG, PNG - Max 10MB)</label>
                                    </div>
                                    <small class="form-text text-muted">
                                        Format yang diizinkan: PDF, DOC, DOCX, XLS, XLSX, JPG, PNG. Maksimal 10MB.
                                    </small>
                                </div>

                                <div class="form-group">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-upload"></i> Upload File
                                    </button>
                                </div>
                            <?php echo form_close(); ?>
                        </div>
                    </div>
                <?php else: ?>
                    <!-- Info untuk Hardcopy -->
                    <div class="alert alert-warning">
                        <h5><i class="fas fa-info-circle"></i> Tagihan Hardcopy</h5>
                        <p class="mb-0">
                            Tagihan ini adalah <strong>hardcopy</strong>. Silakan kumpulkan berkas fisik Anda ke sekretariat prodi.
                            Status pengumpulan akan ditandai oleh sekprodi setelah Anda mengumpulkan berkas.
                        </p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<script>
// Update custom file label
$('.custom-file-input').on('change', function() {
    var fileName = $(this).val().split('\\').pop();
    $(this).next('.custom-file-label').html(fileName);
});
</script>

