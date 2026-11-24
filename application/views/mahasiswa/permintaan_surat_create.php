<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Ajukan Permintaan Surat</h3>
            </div>
            <div class="card-body">
                <?php if ($this->session->flashdata('error')): ?>
                    <div class="alert alert-danger alert-dismissible fade show">
                        <i class="fas fa-exclamation-circle"></i> <?php echo $this->session->flashdata('error'); ?>
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                <?php endif; ?>

                <?php echo form_open_multipart('Permintaan_surat/create'); ?>
                    <div class="form-group">
                        <label for="jenis_surat">Jenis Surat <span class="text-danger">*</span></label>
                        <select name="jenis_surat" id="jenis_surat" class="form-control" required>
                            <option value="">-- Pilih Jenis Surat --</option>
                            <option value="Surat Keterangan Aktif" <?php echo set_value('jenis_surat') == 'Surat Keterangan Aktif' ? 'selected' : ''; ?>>Surat Keterangan Aktif</option>
                            <option value="Surat Pengantar" <?php echo set_value('jenis_surat') == 'Surat Pengantar' ? 'selected' : ''; ?>>Surat Pengantar</option>
                            <option value="Surat Keterangan Lulus" <?php echo set_value('jenis_surat') == 'Surat Keterangan Lulus' ? 'selected' : ''; ?>>Surat Keterangan Lulus</option>
                            <option value="Surat Keterangan Beasiswa" <?php echo set_value('jenis_surat') == 'Surat Keterangan Beasiswa' ? 'selected' : ''; ?>>Surat Keterangan Beasiswa</option>
                            <option value="Surat Rekomendasi" <?php echo set_value('jenis_surat') == 'Surat Rekomendasi' ? 'selected' : ''; ?>>Surat Rekomendasi</option>
                            <option value="Surat Permintaan Data Skripsi" <?php echo set_value('jenis_surat') == 'Surat Permintaan Data Skripsi' ? 'selected' : ''; ?>>Surat Permintaan Data Skripsi</option>
                            <option value="Surat Izin Tempat PKL" <?php echo set_value('jenis_surat') == 'Surat Izin Tempat PKL' ? 'selected' : ''; ?>>Surat Izin Tempat PKL</option>
                            <option value="Lainnya" <?php echo set_value('jenis_surat') == 'Lainnya' ? 'selected' : ''; ?>>Lainnya</option>
                        </select>
                        <?php echo form_error('jenis_surat', '<small class="text-danger">', '</small>'); ?>
                    </div>

                    <div class="form-group">
                        <label for="untuk_siapa">Untuk Siapa Surat Ini <span class="text-danger">*</span></label>
                        <input type="text" name="untuk_siapa" id="untuk_siapa" class="form-control" 
                               placeholder="Contoh: PT. ABC, Dinas Kesehatan, Universitas XYZ, dll" 
                               value="<?php echo set_value('untuk_siapa'); ?>" required>
                        <?php echo form_error('untuk_siapa', '<small class="text-danger">', '</small>'); ?>
                        <small class="form-text text-muted">Masukkan nama instansi, organisasi, atau pihak yang akan menerima surat ini.</small>
                    </div>

                    <!-- Form Dinamis untuk Surat Permintaan Data Skripsi -->
                    <div id="form-skripsi" style="display: none;">
                        <div class="card card-info mb-3">
                            <div class="card-header">
                                <h5 class="card-title mb-0">Informasi Skripsi</h5>
                            </div>
                            <div class="card-body">
                                <div class="form-group">
                                    <label for="judul_skripsi">Judul Skripsi <span class="text-danger">*</span></label>
                                    <input type="text" name="judul_skripsi" id="judul_skripsi" class="form-control" 
                                           placeholder="Masukkan judul skripsi Anda" 
                                           value="<?php echo set_value('judul_skripsi'); ?>">
                                    <?php echo form_error('judul_skripsi', '<small class="text-danger">', '</small>'); ?>
                                </div>
                                <div class="form-group">
                                    <label for="lokasi_penelitian">Lokasi Penelitian <span class="text-danger">*</span></label>
                                    <textarea name="lokasi_penelitian" id="lokasi_penelitian" class="form-control" rows="3" 
                                              placeholder="Masukkan lokasi penelitian (alamat lengkap)"><?php echo set_value('lokasi_penelitian'); ?></textarea>
                                    <?php echo form_error('lokasi_penelitian', '<small class="text-danger">', '</small>'); ?>
                                    <small class="form-text text-muted">Masukkan alamat lengkap lokasi penelitian Anda.</small>
                                </div>
                                <div class="form-group">
                                    <label>Metode Pengumpulan Data <span class="text-danger">*</span></label>
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="pengambilan_data" id="pengambilan_data" value="1" 
                                               <?php echo set_checkbox('pengambilan_data', '1'); ?>>
                                        <label class="form-check-label" for="pengambilan_data">
                                            Pengambilan Data
                                        </label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="observasi" id="observasi" value="1" 
                                               <?php echo set_checkbox('observasi', '1'); ?>>
                                        <label class="form-check-label" for="observasi">
                                            Observasi
                                        </label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="wawancara" id="wawancara" value="1" 
                                               <?php echo set_checkbox('wawancara', '1'); ?>>
                                        <label class="form-check-label" for="wawancara">
                                            Wawancara
                                        </label>
                                    </div>
                                    <small class="form-text text-muted">Pilih minimal satu metode pengumpulan data yang akan digunakan.</small>
                                    <div id="error-metode" class="text-danger" style="display: none;">
                                        <small>Pilih minimal satu metode pengumpulan data.</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Form Dinamis untuk Surat Izin Tempat PKL -->
                    <div id="form-pkl" style="display: none;">
                        <div class="card card-warning mb-3">
                            <div class="card-header">
                                <h5 class="card-title mb-0">Informasi Tempat PKL</h5>
                            </div>
                            <div class="card-body">
                                <div class="form-group">
                                    <label for="nama_tempat_pkl">Nama Tempat PKL <span class="text-danger">*</span></label>
                                    <input type="text" name="nama_tempat_pkl" id="nama_tempat_pkl" class="form-control" 
                                           placeholder="Contoh: PT. ABC, Dinas Kesehatan, dll" 
                                           value="<?php echo set_value('nama_tempat_pkl'); ?>">
                                    <?php echo form_error('nama_tempat_pkl', '<small class="text-danger">', '</small>'); ?>
                                </div>
                                <div class="form-group">
                                    <label for="alamat_pkl">Alamat Lengkap Tempat PKL <span class="text-danger">*</span></label>
                                    <textarea name="alamat_pkl" id="alamat_pkl" class="form-control" rows="4" 
                                              placeholder="Masukkan alamat lengkap tempat PKL (jalan, kelurahan, kecamatan, kota, kode pos)"><?php echo set_value('alamat_pkl'); ?></textarea>
                                    <?php echo form_error('alamat_pkl', '<small class="text-danger">', '</small>'); ?>
                                    <small class="form-text text-muted">Masukkan alamat lengkap tempat PKL Anda.</small>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="keperluan">Keperluan Surat <span class="text-danger">*</span></label>
                        <textarea name="keperluan" id="keperluan" class="form-control" rows="5" 
                                  placeholder="Jelaskan keperluan surat yang diminta (minimal 10 karakter)" required><?php echo set_value('keperluan'); ?></textarea>
                        <?php echo form_error('keperluan', '<small class="text-danger">', '</small>'); ?>
                        <small class="form-text text-muted">Minimal 10 karakter. Jelaskan secara detail keperluan surat yang diminta.</small>
                    </div>

                    <div class="form-group">
                        <label for="tipe_surat">Tipe Surat <span class="text-danger">*</span></label>
                        <select name="tipe_surat" id="tipe_surat" class="form-control" required>
                            <option value="">-- Pilih Tipe Surat --</option>
                            <option value="soft_copy" <?php echo set_value('tipe_surat') == 'soft_copy' ? 'selected' : ''; ?>>Soft Copy (File Digital)</option>
                            <option value="hard_copy" <?php echo set_value('tipe_surat') == 'hard_copy' ? 'selected' : ''; ?>>Hard Copy (Cetak Fisik)</option>
                        </select>
                        <?php echo form_error('tipe_surat', '<small class="text-danger">', '</small>'); ?>
                        <small class="form-text text-muted">Pilih apakah surat yang diminta dalam bentuk Soft Copy (file digital) atau Hard Copy (cetak fisik).</small>
                    </div>

                    <div class="form-group">
                        <label for="file_template">Upload Template Surat (Opsional)</label>
                        <div class="custom-file">
                            <input type="file" class="custom-file-input" id="file_template" name="file_template" 
                                   accept=".pdf,.doc,.docx,.jpg,.jpeg,.png">
                            <label class="custom-file-label" for="file_template">Pilih file template...</label>
                        </div>
                        <small class="form-text text-muted">
                            Jika Anda memiliki template surat sendiri, silakan upload di sini. Format yang diizinkan: PDF, DOC, DOCX, JPG, JPEG, PNG (Maks. 5MB)
                        </small>
                        <?php if (isset($_FILES['file_template']) && $_FILES['file_template']['error'] != UPLOAD_ERR_NO_FILE): ?>
                            <?php if ($_FILES['file_template']['error'] != 0): ?>
                                <small class="text-danger">Error upload: <?php echo $_FILES['file_template']['error']; ?></small>
                            <?php endif; ?>
                        <?php endif; ?>
                    </div>

                    <div class="form-group">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-paper-plane"></i> Ajukan Permintaan
                        </button>
                        <a href="<?php echo site_url('Permintaan_surat'); ?>" class="btn btn-secondary">
                            <i class="fas fa-times"></i> Batal
                        </a>
                    </div>
                <?php echo form_close(); ?>

<script>
document.addEventListener('DOMContentLoaded', function() {
    var jenisSurat = document.getElementById('jenis_surat');
    var formSkripsi = document.getElementById('form-skripsi');
    var formPkl = document.getElementById('form-pkl');
    var judulSkripsi = document.getElementById('judul_skripsi');
    var lokasiPenelitian = document.getElementById('lokasi_penelitian');
    var namaTempatPkl = document.getElementById('nama_tempat_pkl');
    var alamatPkl = document.getElementById('alamat_pkl');
    
    // Custom file input label
    var fileTemplate = document.getElementById('file_template');
    if (fileTemplate) {
        fileTemplate.addEventListener('change', function(e) {
            var fileName = e.target.files[0] ? e.target.files[0].name : 'Pilih file template...';
            var label = e.target.nextElementSibling;
            if (label) {
                label.textContent = fileName;
            }
        });
    }
    
    function toggleForms() {
        var selectedJenis = jenisSurat.value;
        
        // Reset form fields
        if (formSkripsi) {
            formSkripsi.style.display = 'none';
            if (judulSkripsi) judulSkripsi.removeAttribute('required');
            if (lokasiPenelitian) lokasiPenelitian.removeAttribute('required');
        }
        
        if (formPkl) {
            formPkl.style.display = 'none';
            if (namaTempatPkl) namaTempatPkl.removeAttribute('required');
            if (alamatPkl) alamatPkl.removeAttribute('required');
        }
        
        // Tampilkan form sesuai jenis surat
        if (selectedJenis === 'Surat Permintaan Data Skripsi') {
            if (formSkripsi) {
                formSkripsi.style.display = 'block';
                if (judulSkripsi) judulSkripsi.setAttribute('required', 'required');
                if (lokasiPenelitian) lokasiPenelitian.setAttribute('required', 'required');
            }
        } else if (selectedJenis === 'Surat Izin Tempat PKL') {
            if (formPkl) {
                formPkl.style.display = 'block';
                if (namaTempatPkl) namaTempatPkl.setAttribute('required', 'required');
                if (alamatPkl) alamatPkl.setAttribute('required', 'required');
            }
        }
    }
    
    // Validasi checkbox metode pengumpulan data sebelum submit
    var form = document.querySelector('form');
    if (form) {
        form.addEventListener('submit', function(e) {
            var jenisSuratValue = jenisSurat.value;
            if (jenisSuratValue === 'Surat Permintaan Data Skripsi') {
                var pengambilanData = document.getElementById('pengambilan_data');
                var observasi = document.getElementById('observasi');
                var wawancara = document.getElementById('wawancara');
                var errorMetode = document.getElementById('error-metode');
                
                var checked = (pengambilanData && pengambilanData.checked) || 
                             (observasi && observasi.checked) || 
                             (wawancara && wawancara.checked);
                
                if (!checked) {
                    e.preventDefault();
                    if (errorMetode) {
                        errorMetode.style.display = 'block';
                    }
                    return false;
                } else {
                    if (errorMetode) {
                        errorMetode.style.display = 'none';
                    }
                }
            }
        });
    }
    
    // Event listener untuk perubahan jenis surat
    if (jenisSurat) {
        jenisSurat.addEventListener('change', toggleForms);
        // Trigger sekali saat load untuk set form yang sudah dipilih
        toggleForms();
    }
});
</script>
            </div>
        </div>
    </div>
</div>










