<div class="row">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Import Excel - Ruang Kelas</h3>
                <div class="card-tools">
                    <a href="<?php echo site_url('Ruang_kelas'); ?>" class="btn btn-secondary btn-sm">
                        <i class="fas fa-arrow-left"></i> Kembali
                    </a>
                </div>
            </div>
            <div class="card-body">
                <div class="alert alert-info">
                    <i class="fas fa-info-circle"></i> 
                    <strong>Format Excel:</strong> File Excel harus memiliki kolom: <strong>kode_ruang, nama_ruang, kapasitas, jenis_ruang, lokasi, fasilitas, status</strong> (baris pertama adalah header).
                    <br><strong>jenis_ruang:</strong> Kelas, Laboratorium, Aula, Ruang Ujian, atau Lainnya (opsional, default: Kelas).
                    <br><strong>status:</strong> aktif, nonaktif, atau maintenance (opsional, default: aktif).
                    <br><strong>kapasitas:</strong> Angka (opsional, default: 0).
                </div>

                <div class="alert alert-warning">
                    <i class="fas fa-exclamation-triangle"></i> 
                    <strong>Catatan:</strong> Format file yang didukung: .xlsx, .xls, atau .csv. Maksimal ukuran file 5MB.
                    <br><small>Untuk kemudahan, gunakan format CSV (bisa diexport dari Excel).</small>
                </div>

                <?php echo form_open_multipart('Ruang_kelas/import'); ?>
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
                    <a href="<?php echo site_url('Ruang_kelas'); ?>" class="btn btn-secondary">
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
                        <a href="<?php echo site_url('Ruang_kelas/download_template'); ?>" class="btn btn-success">
                            <i class="fas fa-file-excel"></i> Download Template
                        </a>
                    </div>
                </div>

                <div class="card mt-3">
                    <div class="card-header">
                        <h4 class="card-title"><i class="fas fa-table"></i> Contoh Format Excel</h4>
                    </div>
                    <div class="card-body">
                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th>kode_ruang</th>
                                    <th>nama_ruang</th>
                                    <th>kapasitas</th>
                                    <th>jenis_ruang</th>
                                    <th>lokasi</th>
                                    <th>fasilitas</th>
                                    <th>status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>R001</td>
                                    <td>Ruang Kelas 1</td>
                                    <td>40</td>
                                    <td>Kelas</td>
                                    <td>Gedung A Lantai 1</td>
                                    <td>AC, Proyektor, Papan Tulis</td>
                                    <td>aktif</td>
                                </tr>
                                <tr>
                                    <td>LAB001</td>
                                    <td>Laboratorium Komputer 1</td>
                                    <td>30</td>
                                    <td>Laboratorium</td>
                                    <td>Gedung B Lantai 1</td>
                                    <td>AC, Komputer, Proyektor</td>
                                    <td>aktif</td>
                                </tr>
                                <tr>
                                    <td>AULA001</td>
                                    <td>Aula Utama</td>
                                    <td>200</td>
                                    <td>Aula</td>
                                    <td>Gedung C Lantai 1</td>
                                    <td>AC, Sound System, Proyektor</td>
                                    <td>aktif</td>
                                </tr>
                            </tbody>
                        </table>
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













