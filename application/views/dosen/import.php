<div class="row">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Import Excel - Dosen</h3>
                <div class="card-tools">
                    <a href="<?php echo site_url('Dosen'); ?>" class="btn btn-secondary btn-sm">
                        <i class="fas fa-arrow-left"></i> Kembali
                    </a>
                </div>
            </div>
            <div class="card-body">
                <div class="alert alert-info">
                    <i class="fas fa-info-circle"></i> 
                    <strong>Format Excel:</strong> File Excel harus memiliki kolom: <strong>nidn, nama_dosen, email, jabatan_fungsional, status</strong> (baris pertama adalah header).
                    <br><strong>jabatan_fungsional:</strong> Asisten Ahli, Lektor, Lektor Kepala, atau Guru Besar (Profesor).
                    <br><strong>status:</strong> aktif atau nonaktif (opsional, default: aktif).
                </div>

                <div class="alert alert-warning">
                    <i class="fas fa-exclamation-triangle"></i> 
                    <strong>Catatan:</strong> Format file yang didukung: .xlsx, .xls, atau .csv. Maksimal ukuran file 5MB.
                    <br><small>Untuk kemudahan, gunakan format CSV (bisa diexport dari Excel).</small>
                </div>

                <?php echo form_open_multipart('Dosen/import'); ?>
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
                    <a href="<?php echo site_url('Dosen'); ?>" class="btn btn-secondary">
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
                        <a href="<?php echo site_url('Dosen/download_template'); ?>" class="btn btn-success">
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
                                    <th>nidn</th>
                                    <th>nama_dosen</th>
                                    <th>email</th>
                                    <th>jabatan_fungsional</th>
                                    <th>status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>0123456789</td>
                                    <td>Dr. Ahmad Hidayat, M.Kom</td>
                                    <td>ahmad.hidayat@example.com</td>
                                    <td>Lektor</td>
                                    <td>aktif</td>
                                </tr>
                                <tr>
                                    <td>0987654321</td>
                                    <td>Prof. Dr. Siti Nurhaliza, M.T</td>
                                    <td>siti.nurhaliza@example.com</td>
                                    <td>Guru Besar (Profesor)</td>
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
















