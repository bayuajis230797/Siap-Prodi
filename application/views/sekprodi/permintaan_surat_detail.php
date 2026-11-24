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

                <!-- Info Mahasiswa -->
                <div class="card card-info mb-3">
                    <div class="card-header">
                        <h5 class="card-title mb-0">
                            <i class="fas fa-user"></i> Data Mahasiswa
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <table class="table table-borderless">
                                    <tr>
                                        <th width="40%">NIM:</th>
                                        <td><strong><?php echo htmlspecialchars($permintaan->nim); ?></strong></td>
                                    </tr>
                                    <tr>
                                        <th>Nama:</th>
                                        <td><strong><?php echo htmlspecialchars($permintaan->nama_mahasiswa); ?></strong></td>
                                    </tr>
                                    <tr>
                                        <th>Kelas:</th>
                                        <td><?php echo htmlspecialchars($permintaan->kelas); ?></td>
                                    </tr>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Detail Permintaan -->
                <div class="card mb-3">
                    <div class="card-header">
                        <h5 class="card-title mb-0">
                            <i class="fas fa-file-alt"></i> Detail Permintaan
                        </h5>
                    </div>
                    <div class="card-body">
                        <table class="table table-borderless">
                            <tr>
                                <th width="25%">Jenis Surat:</th>
                                <td><strong><?php echo htmlspecialchars($permintaan->jenis_surat); ?></strong></td>
                            </tr>
                            <tr>
                                <th>Untuk Siapa:</th>
                                <td><strong><?php echo isset($permintaan->untuk_siapa) && $permintaan->untuk_siapa ? htmlspecialchars($permintaan->untuk_siapa) : '-'; ?></strong></td>
                            </tr>
                            <tr>
                                <th>Keperluan:</th>
                                <td><?php echo nl2br(htmlspecialchars($permintaan->keperluan)); ?></td>
                            </tr>
                            
                            <!-- Informasi Skripsi (jika jenis surat = Surat Permintaan Data Skripsi) -->
                            <?php if ($permintaan->jenis_surat == 'Surat Permintaan Data Skripsi'): ?>
                                <?php if (isset($permintaan->judul_skripsi) && $permintaan->judul_skripsi): ?>
                                    <tr>
                                        <th>Judul Skripsi:</th>
                                        <td><?php echo htmlspecialchars($permintaan->judul_skripsi); ?></td>
                                    </tr>
                                <?php endif; ?>
                                <?php if (isset($permintaan->lokasi_penelitian) && $permintaan->lokasi_penelitian): ?>
                                    <tr>
                                        <th>Lokasi Penelitian:</th>
                                        <td><?php echo nl2br(htmlspecialchars($permintaan->lokasi_penelitian)); ?></td>
                                    </tr>
                                <?php endif; ?>
                                <?php 
                                $metode_list = array();
                                if (isset($permintaan->pengambilan_data) && $permintaan->pengambilan_data == 1) {
                                    $metode_list[] = 'Pengambilan Data';
                                }
                                if (isset($permintaan->observasi) && $permintaan->observasi == 1) {
                                    $metode_list[] = 'Observasi';
                                }
                                if (isset($permintaan->wawancara) && $permintaan->wawancara == 1) {
                                    $metode_list[] = 'Wawancara';
                                }
                                if (!empty($metode_list)):
                                ?>
                                    <tr>
                                        <th>Metode Pengumpulan Data:</th>
                                        <td>
                                            <?php foreach ($metode_list as $metode): ?>
                                                <span class="badge badge-primary mr-1"><?php echo htmlspecialchars($metode); ?></span>
                                            <?php endforeach; ?>
                                        </td>
                                    </tr>
                                <?php endif; ?>
                            <?php endif; ?>
                            
                            <!-- Informasi PKL (jika jenis surat = Surat Izin Tempat PKL) -->
                            <?php if ($permintaan->jenis_surat == 'Surat Izin Tempat PKL'): ?>
                                <?php if (isset($permintaan->nama_tempat_pkl) && $permintaan->nama_tempat_pkl): ?>
                                    <tr>
                                        <th>Nama Tempat PKL:</th>
                                        <td><?php echo htmlspecialchars($permintaan->nama_tempat_pkl); ?></td>
                                    </tr>
                                <?php endif; ?>
                                <?php if (isset($permintaan->alamat_pkl) && $permintaan->alamat_pkl): ?>
                                    <tr>
                                        <th>Alamat Tempat PKL:</th>
                                        <td><?php echo nl2br(htmlspecialchars($permintaan->alamat_pkl)); ?></td>
                                    </tr>
                                <?php endif; ?>
                            <?php endif; ?>
                            
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
                            
                            <!-- Template yang diupload mahasiswa -->
                            <?php if (isset($permintaan->file_template) && $permintaan->file_template): ?>
                                <tr>
                                    <th>Template Surat:</th>
                                    <td>
                                        <a href="<?php echo base_url($permintaan->file_template); ?>" target="_blank" class="btn btn-sm btn-info">
                                            <i class="fas fa-download"></i> 
                                            <?php echo isset($permintaan->nama_file_template) && $permintaan->nama_file_template ? htmlspecialchars($permintaan->nama_file_template) : 'Download Template'; ?>
                                        </a>
                                        <small class="text-muted ml-2">Template yang diupload mahasiswa</small>
                                    </td>
                                </tr>
                            <?php endif; ?>
                            
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
                </div>

                <!-- Form Proses Permintaan -->
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">
                            <i class="fas fa-cog"></i> Proses Permintaan
                        </h5>
                    </div>
                    <div class="card-body">
                        <form method="post" action="<?php echo site_url('Permintaan_surat_sekprodi/proses/' . $permintaan->id); ?>" enctype="multipart/form-data">
                            <div class="form-group">
                                <label for="status">Status <span class="text-danger">*</span></label>
                                <select name="status" id="status" class="form-control" required>
                                    <option value="pending" <?php echo ($permintaan->status == 'pending') ? 'selected' : ''; ?>>Pending</option>
                                    <option value="diproses" <?php echo ($permintaan->status == 'diproses') ? 'selected' : ''; ?>>Diproses</option>
                                    <option value="selesai" <?php echo ($permintaan->status == 'selesai') ? 'selected' : ''; ?>>Selesai</option>
                                    <option value="ditolak" <?php echo ($permintaan->status == 'ditolak') ? 'selected' : ''; ?>>Ditolak</option>
                                </select>
                            </div>

                            <div class="form-group" id="file-upload-group" style="display: none;">
                                <label for="file_surat">File Surat <span class="text-danger" id="file-required">*</span></label>
                                <input type="file" name="file_surat" id="file_surat" class="form-control-file" accept=".pdf,.doc,.docx">
                                <small class="form-text text-muted" id="file-help-text">
                                    Format: PDF, DOC, DOCX (Maks. 5MB).
                                    <span id="file-requirement-text">Wajib diupload jika status Selesai.</span>
                                </small>
                                <?php if ($permintaan->file_surat): ?>
                                    <div class="mt-2">
                                        <small class="text-muted">File saat ini: 
                                            <a href="<?php echo site_url('Permintaan_surat_sekprodi/download/' . $permintaan->id); ?>" target="_blank">
                                                <i class="fas fa-file"></i> <?php echo htmlspecialchars($permintaan->nama_file_surat); ?>
                                            </a>
                                        </small>
                                        <br>
                                        <small class="text-info">Jika ingin mengganti file, pilih file baru di atas.</small>
                                    </div>
                                <?php endif; ?>
                            </div>

                            <div class="form-group">
                                <label for="catatan">Catatan</label>
                                <textarea name="catatan" id="catatan" class="form-control" rows="3" 
                                          placeholder="Catatan untuk mahasiswa (opsional)"><?php echo htmlspecialchars($permintaan->catatan_sekprodi); ?></textarea>
                            </div>

                            <div class="form-group">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save"></i> Simpan
                                </button>
                                <a href="<?php echo site_url('Permintaan_surat_sekprodi'); ?>" class="btn btn-secondary">
                                    <i class="fas fa-arrow-left"></i> Kembali
                                </a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const statusSelect = document.getElementById('status');
    const fileUploadGroup = document.getElementById('file-upload-group');
    const fileInput = document.getElementById('file_surat');
    const fileRequired = document.getElementById('file-required');
    const hasExistingFile = <?php echo $permintaan->file_surat ? 'true' : 'false'; ?>;
    
    const tipeSurat = '<?php echo isset($permintaan->tipe_surat) ? $permintaan->tipe_surat : ""; ?>';
    const fileRequirementText = document.getElementById('file-requirement-text');
    
    function toggleFileUpload() {
        if (statusSelect.value === 'selesai') {
            fileUploadGroup.style.display = 'block';
            
            // Tentukan apakah file wajib berdasarkan tipe surat
            const isSoftCopy = tipeSurat === 'soft_copy';
            const isRequired = !hasExistingFile && isSoftCopy;
            
            if (isRequired) {
                fileInput.setAttribute('required', 'required');
                if (fileRequired) fileRequired.style.display = 'inline';
                if (fileRequirementText) {
                    fileRequirementText.textContent = 'Wajib diupload untuk Soft Copy (untuk didownload mahasiswa).';
                }
            } else {
                fileInput.removeAttribute('required');
                if (fileRequired) fileRequired.style.display = 'none';
                if (fileRequirementText) {
                    if (tipeSurat === 'hard_copy') {
                        fileRequirementText.textContent = 'Opsional (hanya untuk arsip digital). Surat sudah dicetak fisik.';
                    } else {
                        fileRequirementText.textContent = 'Opsional. Jika sudah ada file, tidak perlu upload ulang.';
                    }
                }
            }
        } else {
            fileUploadGroup.style.display = 'none';
            fileInput.removeAttribute('required');
        }
    }
    
    // Check on load
    toggleFileUpload();
    
    // Check on change
    statusSelect.addEventListener('change', toggleFileUpload);
});
</script>

