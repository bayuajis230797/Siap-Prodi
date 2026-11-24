<div class="row">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Import Excel - Mapping Mata Kuliah - Dosen</h3>
                <div class="card-tools">
                    <a href="<?php echo site_url('Mapping_mk'); ?>" class="btn btn-secondary btn-sm">
                        <i class="fas fa-arrow-left"></i> Kembali
                    </a>
                </div>
            </div>
            <div class="card-body">
                <?php if (isset($tahun_aktif) && $tahun_aktif): ?>
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle"></i> 
                        <strong>Tahun Akademik Aktif:</strong> <?php echo $tahun_aktif->tahun_akademik . ' ' . ucfirst($tahun_aktif->semester); ?>
                    </div>
                <?php endif; ?>

                <div class="alert alert-info">
                    <i class="fas fa-info-circle"></i> 
                    <strong>Format Excel:</strong> File Excel harus memiliki kolom: <strong>kode_mk (atau id_mata_kuliah_aktif), nidn (atau id_dosen)</strong> (baris pertama adalah header).
                    <br><strong>Format 1:</strong> kode_mk, nidn
                    <br><strong>Format 2:</strong> id_mata_kuliah_aktif, id_dosen
                </div>

                <div class="alert alert-warning">
                    <i class="fas fa-exclamation-triangle"></i> 
                    <strong>Catatan:</strong> Format file yang didukung: .xlsx, .xls, atau .csv. Maksimal ukuran file 5MB.
                    <br><small>Untuk kemudahan, gunakan format CSV (bisa diexport dari Excel).</small>
                </div>

                <?php echo form_open_multipart('Mapping_mk/import_excel'); ?>
                <div class="form-group">
                    <label for="file_excel">Pilih File Excel <span class="text-danger">*</span></label>
                    <input type="file" name="file_excel" id="file_excel" class="form-control-file" accept=".xlsx,.xls,.csv" required>
                    <small class="form-text text-muted">Format: .xlsx, .xls, atau .csv (maksimal 5MB)</small>
                </div>

                <div class="form-group">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-upload"></i> Upload dan Import
                    </button>
                    <a href="<?php echo site_url('Mapping_mk'); ?>" class="btn btn-secondary">
                        <i class="fas fa-times"></i> Batal
                    </a>
                </div>
                <?php echo form_close(); ?>

                <hr>

                <h5>Contoh Format Excel:</h5>
                <div class="table-responsive">
                    <table class="table table-bordered table-sm">
                        <thead>
                            <tr>
                                <th>kode_mk</th>
                                <th>nidn</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>SIF2001</td>
                                <td>1234567890</td>
                            </tr>
                            <tr>
                                <td>SIF3002</td>
                                <td>1234567890</td>
                            </tr>
                            <tr>
                                <td>SIF3003</td>
                                <td>0987654321</td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div class="alert alert-secondary mt-3">
                    <small>
                        <i class="fas fa-info-circle"></i> 
                        <strong>Catatan:</strong>
                        <ul class="mb-0 mt-2">
                            <li>Gunakan kode_mk yang sesuai dengan mata kuliah aktif di tahun akademik ini</li>
                            <li>Gunakan NIDN dosen yang valid</li>
                            <li>Mapping yang sudah ada akan dilewati</li>
                        </ul>
                    </small>
                </div>

                <hr>

                <h5>Download Referensi Data:</h5>
                <div class="row">
                    <div class="col-md-4">
                        <div class="card">
                            <div class="card-body text-center">
                                <h6 class="card-title">Template CSV</h6>
                                <p class="card-text"><small>Template untuk import mapping</small></p>
                                <a href="<?php echo site_url('Mapping_mk/download_template'); ?>" class="btn btn-info btn-sm">
                                    <i class="fas fa-download"></i> Download Template
                                </a>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card">
                            <div class="card-body text-center">
                                <h6 class="card-title">Daftar Dosen</h6>
                                <p class="card-text"><small>Daftar dosen beserta NIDN</small></p>
                                <a href="<?php echo site_url('Mapping_mk/download_daftar_dosen'); ?>" class="btn btn-success btn-sm">
                                    <i class="fas fa-download"></i> Download Daftar Dosen
                                </a>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card">
                            <div class="card-body text-center">
                                <h6 class="card-title">Daftar Mata Kuliah Aktif</h6>
                                <p class="card-text"><small>Daftar kode mata kuliah yang statusnya aktif</small></p>
                                <a href="<?php echo site_url('Mapping_mk/download_daftar_mk_aktif'); ?>" class="btn btn-primary btn-sm">
                                    <i class="fas fa-download"></i> Download Daftar MK
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="alert alert-info mt-3">
                    <i class="fas fa-info-circle"></i> 
                    <strong>Tips:</strong> Download daftar dosen dan daftar mata kuliah aktif untuk referensi saat mengisi file import. 
                    Pastikan kode_mk dan nidn yang digunakan sesuai dengan data yang ada.
                </div>
            </div>
        </div>
    </div>
</div>

