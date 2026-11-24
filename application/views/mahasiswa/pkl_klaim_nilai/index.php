<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Klaim Nilai PKL</h3>
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

                <?php if ($nilai_rekap && $klaim && $klaim->status === 'disetujui'): ?>
                    <?php
                    $this->load->helper('pkl');
                    $grade = $nilai_rekap->nilai_huruf;
                    if (!$grade && $nilai_rekap->nilai_akhir > 0) {
                        $grade = konversi_nilai_huruf_pkl($nilai_rekap->nilai_akhir);
                    }
                    $grade_class = 'primary';
                    if ($grade) {
                        switch (strtoupper($grade)) {
                            case 'A':
                                $grade_class = 'success';
                                break;
                            case 'B':
                                $grade_class = 'info';
                                break;
                            case 'C':
                                $grade_class = 'warning';
                                break;
                            case 'D':
                            case 'E':
                                $grade_class = 'danger';
                                break;
                        }
                    }
                    ?>
                    <div class="alert alert-success">
                        <h5><i class="fas fa-check-circle"></i> Nilai Final PKL</h5>
                        <p class="mb-1"><strong>Nilai Akhir:</strong> <?php echo number_format($nilai_rekap->nilai_akhir, 2, ',', '.'); ?></p>
                        <p class="mb-0"><strong>Nilai Huruf (Grade):</strong> 
                            <?php if ($grade): ?>
                                <span class="badge badge-<?php echo $grade_class; ?> badge-lg" style="font-size: 1.2em; padding: 8px 15px;"><?php echo htmlspecialchars($grade); ?></span>
                            <?php else: ?>
                                <span class="text-muted">-</span>
                            <?php endif; ?>
                        </p>
                    </div>
                <?php endif; ?>

                <?php if ($klaim): ?>
                    <div class="alert alert-info">
                        <h5><i class="fas fa-info-circle"></i> Status Klaim Nilai</h5>
                        <p class="mb-0">
                            <strong>Status:</strong> 
                            <?php
                            $status_class = 'secondary';
                            $status_text = ucfirst($klaim->status);
                            switch ($klaim->status) {
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
                        <?php if ($klaim->catatan_sekprodi): ?>
                            <p class="mb-0 mt-2"><strong>Catatan Sekprodi:</strong> <?php echo nl2br(htmlspecialchars($klaim->catatan_sekprodi)); ?></p>
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
                                        <th>Tanggal Upload</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($berkas_uploaded as $berkas): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($berkas->nama_berkas); ?></td>
                                            <td>
                                                <?php 
                                                // Cek apakah file_path adalah link Google Drive
                                                $is_google_drive_link = (strpos($berkas->file_path, 'http') === 0);
                                                if ($is_google_drive_link): 
                                                ?>
                                                    <i class="fab fa-google-drive text-primary"></i> 
                                                    <a href="<?php echo htmlspecialchars($berkas->file_path); ?>" target="_blank" class="text-primary">
                                                        <strong><?php echo htmlspecialchars($berkas->file_name); ?></strong>
                                                    </a>
                                                <?php else: ?>
                                                    <i class="fas fa-file-alt"></i> <?php echo htmlspecialchars($berkas->file_name); ?>
                                                <?php endif; ?>
                                                <?php if ($berkas->wajib): ?>
                                                    <span class="badge badge-danger ml-2">Wajib</span>
                                                <?php endif; ?>
                                            </td>
                                            <td><?php echo date('d/m/Y H:i', strtotime($berkas->uploaded_at)); ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>

                    <?php if ($klaim->status === 'ditolak'): ?>
                        <div class="alert alert-warning">
                            <p>Klaim nilai Anda ditolak. Silakan perbaiki berkas dan ajukan kembali.</p>
                        </div>
                    <?php endif; ?>
                <?php endif; ?>

                <?php if (!$klaim || $klaim->status === 'ditolak'): ?>
                    <?php if (empty($boleh_klaim)): ?>
                        <div class="alert alert-warning">
                            <i class="fas fa-hourglass-half"></i>
                            <strong>Menunggu penguji.</strong>
                            <?php echo htmlspecialchars(isset($pasca_status['message']) ? $pasca_status['message'] : 'Menunggu penguji menyatakan layak klaim nilai.'); ?>
                            <br>
                            <a href="<?php echo site_url('Bimbingan_pasca_pkl_mahasiswa'); ?>" class="btn btn-sm btn-outline-primary mt-2">
                                Buka Bimbingan Pasca Sidang
                            </a>
                        </div>
                    <?php endif; ?>
                    <?php echo form_open_multipart('Pkl_klaim_nilai/submit'); ?>
                        <fieldset <?php echo empty($boleh_klaim) ? 'disabled' : ''; ?>>
                        <h5 class="mb-3"><i class="fab fa-google-drive"></i> Upload Berkas Klaim Nilai</h5>
                        <p class="text-muted mb-4">Silakan masukkan link Google Drive untuk semua berkas yang wajib untuk klaim nilai PKL.</p>
                        
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
                                        if ($bu->id_berkas_klaim_nilai == $berkas->id) {
                                            $berkas_uploaded_item = $bu;
                                            break;
                                        }
                                    }
                                }
                                ?>
                                <div class="form-group">
                                    <label for="file_berkas_<?php echo $berkas->id; ?>">
                                        <?php echo htmlspecialchars($berkas->nama_berkas); ?>
                                        <?php if ($berkas->wajib && !$berkas_uploaded_item): ?>
                                            <span class="text-danger">*</span>
                                        <?php endif; ?>
                                    </label>
                                    <?php if ($berkas->deskripsi): ?>
                                        <small class="form-text text-muted mb-2"><?php echo htmlspecialchars($berkas->deskripsi); ?></small>
                                    <?php endif; ?>
                                    <?php if ($berkas_uploaded_item && strpos($berkas_uploaded_item->file_path, 'http') === 0): ?>
                                        <div class="alert alert-info mb-2">
                                            <?php $this->load->helper('google_drive'); ?>
                                            <?php echo google_drive_link_html($berkas_uploaded_item->file_path, htmlspecialchars($berkas_uploaded_item->file_name)); ?>
                                        </div>
                                    <?php endif; ?>
                                    <input type="file" name="file_berkas_<?php echo $berkas->id; ?>" id="file_berkas_<?php echo $berkas->id; ?>"
                                           class="form-control-file" <?php echo ($berkas->wajib && !$berkas_uploaded_item) ? 'required' : ''; ?>>
                                </div>
                            <?php endforeach; ?>
                            
                            <div class="form-group">
                                <button type="submit" class="btn btn-primary" <?php echo empty($boleh_klaim) ? 'disabled' : ''; ?>>
                                    <i class="fas fa-paper-plane"></i> <?php echo $klaim ? 'Update Klaim Nilai' : 'Kirim Klaim Nilai'; ?>
                                </button>
                                <?php if (empty($boleh_klaim)): ?>
                                    <small class="text-muted d-block mt-2">Tombol nonaktif: menunggu penguji menyatakan layak klaim nilai.</small>
                                <?php endif; ?>
                            </div>
                        <?php endif; ?>
                        </fieldset>
                    <?php echo form_close(); ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>





