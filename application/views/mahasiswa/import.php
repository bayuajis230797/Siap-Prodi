<div class="row">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Import Excel - Mahasiswa</h3>
                <div class="card-tools">
                    <a href="<?php echo site_url('Mahasiswa'); ?>" class="btn btn-secondary btn-sm">
                        <i class="fas fa-arrow-left"></i> Kembali
                    </a>
                </div>
            </div>
            <div class="card-body">
                <div class="alert alert-info">
                    <i class="fas fa-info-circle"></i> 
                    <strong>Format Excel:</strong> File Excel harus memiliki kolom: <strong>nim, nama_mahasiswa, angkatan, id_kurikulum, kelas, status</strong> (baris pertama adalah header).
                    <br><strong>kelas:</strong> Opsional. Format {semester}{huruf} seperti 1A, 1B, 1G (untuk karyawan). Jika dikosongkan, akan otomatis di-generate.
                    <br><strong>status:</strong> aktif, cuti, lulus, atau nonaktif (opsional, default: aktif).
                </div>

                <div class="alert alert-warning">
                    <i class="fas fa-exclamation-triangle"></i> 
                    <strong>Catatan:</strong> Format file yang didukung: .xlsx, .xls, atau .csv. Maksimal ukuran file 5MB.
                    <br><small>Untuk kemudahan, gunakan format CSV (bisa diexport dari Excel).</small>
                </div>

                <?php echo form_open_multipart('Mahasiswa/import'); ?>
                <div class="form-group">
                    <label for="file_excel">Pilih File Excel <span class="text-danger">*</span></label>
                    <div class="input-group">
                        <div class="custom-file">
                            <input type="file" name="file_excel" id="file_excel" class="custom-file-input" 
                                   accept=".xlsx,.xls,.csv" required>
                            <label class="custom-file-label" for="file_excel">Pilih file...</label>
                        </div>
                    </div>
                    <small class="form-text text-muted">Format: .xlsx, .xls, atau .csv</small>
                </div>

                <div class="form-group">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-upload"></i> Upload & Import
                    </button>
                    <a href="<?php echo site_url('Mahasiswa'); ?>" class="btn btn-secondary">
                        <i class="fas fa-times"></i> Batal
                    </a>
                </div>
                <?php echo form_close(); ?>

                <hr>

                <div class="card card-info">
                    <div class="card-header">
                        <h4 class="card-title"><i class="fas fa-download"></i> Download Template Excel</h4>
                    </div>
                    <div class="card-body">
                        <p>Download template Excel untuk memudahkan input data:</p>
                        <a href="<?php echo site_url('Mahasiswa/download_template'); ?>" class="btn btn-success">
                            <i class="fas fa-file-excel"></i> Download Template
                        </a>
                    </div>
                </div>

                <!-- Daftar ID Kurikulum Aktif -->
                <?php if (!empty($kurikulum)): ?>
                    <div class="card card-info card-outline mt-3">
                        <div class="card-header">
                            <h4 class="card-title">
                                <i class="fas fa-list"></i> Daftar ID Kurikulum Aktif
                            </h4>
                        </div>
                        <div class="card-body">
                            <p class="text-muted mb-2">Gunakan ID berikut untuk kolom "id_kurikulum" di Excel:</p>
                            <div class="table-responsive">
                                <table class="table table-sm table-bordered">
                                    <thead>
                                        <tr>
                                            <th width="10%">ID</th>
                                            <th>Nama Kurikulum</th>
                                            <th width="15%">Tahun Berlaku</th>
                                            <th width="10%" class="text-center">Status</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($kurikulum as $k): ?>
                                            <tr>
                                                <td><strong class="text-primary"><?php echo $k->id; ?></strong></td>
                                                <td><?php echo $k->nama_kurikulum; ?></td>
                                                <td><?php echo $k->tahun_berlaku; ?></td>
                                                <td class="text-center">
                                                    <span class="badge badge-success">Aktif</span>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                <?php else: ?>
                    <div class="alert alert-danger mt-3">
                        <i class="fas fa-exclamation-circle"></i> 
                        <strong>Peringatan:</strong> Tidak ada kurikulum aktif. Silakan aktifkan kurikulum terlebih dahulu di menu <a href="<?php echo site_url('Kurikulum'); ?>"><strong>Kurikulum</strong></a>.
                    </div>
                <?php endif; ?>

                <div class="card mt-3">
                    <div class="card-header">
                        <h4 class="card-title"><i class="fas fa-table"></i> Contoh Format Excel</h4>
                    </div>
                    <div class="card-body">
                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th>nim</th>
                                    <th>nama_mahasiswa</th>
                                    <th>angkatan</th>
                                    <th>id_kurikulum</th>
                                    <th>kelas</th>
                                    <th>status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>202001001</td>
                                    <td>Budi Santoso</td>
                                    <td>2020</td>
                                    <td>1</td>
                                    <td>1A</td>
                                    <td>aktif</td>
                                </tr>
                                <tr>
                                    <td>202001002</td>
                                    <td>Siti Aisyah</td>
                                    <td>2020</td>
                                    <td>1</td>
                                    <td>1B</td>
                                    <td>aktif</td>
                                </tr>
                                <tr>
                                    <td>202101001</td>
                                    <td>Andi Pratama</td>
                                    <td>2021</td>
                                    <td>2</td>
                                    <td>1A</td>
                                    <td>aktif</td>
                                </tr>
                                <tr>
                                    <td>202001003</td>
                                    <td>Mahasiswa Karyawan</td>
                                    <td>2020</td>
                                    <td>1</td>
                                    <td>1G</td>
                                    <td>aktif</td>
                                </tr>
                            </tbody>
                        </table>
                        <div class="alert alert-warning mt-2 mb-0">
                            <small>
                                <i class="fas fa-info-circle"></i> 
                                <strong>Catatan Kelas:</strong> 
                                <ul class="mb-0 mt-1">
                                    <li>Format kelas: {semester}{huruf} (contoh: 1A, 1B, 1C, 1G)</li>
                                    <li>G = untuk kelas karyawan</li>
                                    <li>Jika dikosongkan, kelas akan otomatis di-generate berdasarkan semester aktif</li>
                                    <li>Kelas akan otomatis update saat tahun akademik berubah (1A → 2A, 1B → 2B, dll)</li>
                                </ul>
                            </small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Update label file input
document.getElementById('file_excel').addEventListener('change', function(e) {
    var fileName = e.target.files[0].name;
    var label = e.target.nextElementSibling;
    label.textContent = fileName;
});
</script>
