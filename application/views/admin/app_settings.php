<div class="row">
    <div class="col-md-8">
        <div class="card card-primary card-outline">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-cog"></i> Manajemen Aplikasi
                </h3>
            </div>
            <div class="card-body">
                <?php if ($this->session->flashdata('success')): ?>
                    <div class="alert alert-success alert-dismissible">
                        <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
                        <?php echo $this->session->flashdata('success'); ?>
                    </div>
                <?php endif; ?>
                
                <?php if ($this->session->flashdata('error')): ?>
                    <div class="alert alert-danger alert-dismissible">
                        <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
                        <?php echo $this->session->flashdata('error'); ?>
                    </div>
                <?php endif; ?>

                <?php echo form_open_multipart('App_settings/update'); ?>
                    <div class="form-group">
                        <label for="nama_kampus">Nama Kampus <span class="text-danger">*</span></label>
                        <input type="text" name="nama_kampus" id="nama_kampus" class="form-control" 
                               value="<?php echo htmlspecialchars($settings->nama_kampus); ?>" required>
                        <small class="form-text text-muted">Nama lengkap kampus/universitas</small>
                    </div>

                    <div class="form-group">
                        <label for="nama_prodi">Nama Program Studi <span class="text-danger">*</span></label>
                        <input type="text" name="nama_prodi" id="nama_prodi" class="form-control" 
                               value="<?php echo htmlspecialchars($settings->nama_prodi); ?>" required>
                        <small class="form-text text-muted">Nama lengkap program studi</small>
                    </div>

                    <div class="form-group">
                        <label for="logo_kampus">Logo Kampus</label>
                        <div class="mb-2">
                            <?php 
                            $logo_path = null;
                            $file_exists = false;
                            $full_path = null;
                            
                            if ($settings->logo_kampus) {
                                // Normalisasi path - ganti backslash dengan forward slash
                                $logo_path = str_replace('\\', '/', trim($settings->logo_kampus));
                                
                                // Jika path tidak dimulai dengan uploads/, tambahkan
                                if (strpos($logo_path, 'uploads/') !== 0) {
                                    $logo_path = 'uploads/logo/' . basename($logo_path);
                                }
                                
                                // Cek file exists dengan berbagai kemungkinan path
                                $full_path = FCPATH . str_replace('/', DIRECTORY_SEPARATOR, $logo_path);
                                if (file_exists($full_path)) {
                                    $file_exists = true;
                                } else {
                                    // Coba path alternatif
                                    $alt_paths = array(
                                        FCPATH . 'uploads' . DIRECTORY_SEPARATOR . 'logo' . DIRECTORY_SEPARATOR . basename($logo_path),
                                        FCPATH . str_replace('uploads/logo/', 'uploads\\logo\\', $logo_path),
                                        FCPATH . $settings->logo_kampus
                                    );
                                    
                                    foreach ($alt_paths as $alt_path) {
                                        if (file_exists($alt_path)) {
                                            $logo_path = 'uploads/logo/' . basename($alt_path);
                                            $file_exists = true;
                                            $full_path = $alt_path;
                                            break;
                                        }
                                    }
                                }
                            }
                            ?>
                            <?php if ($logo_path && $file_exists): ?>
                                <div class="mb-2">
                                    <?php 
                                    // Gunakan helper untuk mendapatkan URL logo yang benar
                                    $logo_url = get_logo_kampus();
                                    if (!$logo_url && isset($full_path) && file_exists($full_path)) {
                                        // Jika helper gagal tapi file exists, gunakan path yang sudah ditemukan
                                        $relative_path_for_url = str_replace('\\', '/', $logo_path);
                                        $relative_path_for_url = ltrim($relative_path_for_url, '/');
                                        $logo_url = base_url($relative_path_for_url);
                                    }
                                    $error_id = 'logo-error-' . uniqid();
                                    ?>
                                    <?php if ($logo_url): ?>
                                        <img src="<?php echo htmlspecialchars($logo_url); ?>" 
                                             alt="Logo Kampus" 
                                             class="img-thumbnail" 
                                             style="max-height: 150px; max-width: 300px; display: block; border: 1px solid #ddd;"
                                             onerror="this.onerror=null; this.style.display='none'; document.getElementById('<?php echo $error_id; ?>').style.display='block';">
                                        <div id="<?php echo $error_id; ?>" style="display: none; padding: 10px; border: 1px solid #f0ad4e; background: #fff3cd; border-radius: 4px; margin-top: 10px;">
                                            <i class="fas fa-exclamation-triangle text-warning"></i> 
                                            <strong>Gagal memuat logo</strong><br>
                                            <small>URL: <?php echo htmlspecialchars($logo_url); ?></small><br>
                                            <?php if (isset($full_path)): ?>
                                            <small>Path lengkap: <?php echo htmlspecialchars($full_path); ?></small><br>
                                            <small>File exists: <?php echo file_exists($full_path) ? 'Ya' : 'Tidak'; ?></small>
                                            <?php endif; ?>
                                        </div>
                                    <?php else: ?>
                                        <div class="alert alert-warning">
                                            <i class="fas fa-exclamation-triangle"></i> 
                                            <strong>Logo tidak dapat dimuat</strong><br>
                                            <small>File ditemukan namun URL tidak dapat dibuat. Silakan upload ulang logo.</small>
                                        </div>
                                    <?php endif; ?>
                                </div>
                                <small class="text-muted">Logo saat ini</small>
                                <br>
                                <button type="button" class="btn btn-sm btn-danger mt-2" onclick="deleteLogo()">
                                    <i class="fas fa-trash"></i> Hapus Logo
                                </button>
                            <?php else: ?>
                                <div class="alert alert-info">
                                    <i class="fas fa-image fa-3x text-muted mb-2"></i>
                                    <p class="mb-0"><strong>Belum ada logo yang diupload</strong></p>
                                    <?php if ($settings->logo_kampus): ?>
                                        <hr>
                                        <small class="text-muted">
                                            <strong>Path di database:</strong> <?php echo htmlspecialchars($settings->logo_kampus); ?><br>
                                            <?php if (isset($full_path)): ?>
                                                <strong>Path lengkap:</strong> <?php echo htmlspecialchars($full_path); ?><br>
                                            <?php endif; ?>
                                            <strong>Status:</strong> <span class="text-danger">File tidak ditemukan</span>
                                        </small>
                                    <?php endif; ?>
                                </div>
                            <?php endif; ?>
                        </div>
                        <input type="file" name="logo_kampus" id="logo_kampus" class="form-control-file" accept="image/*">
                        <small class="form-text text-muted">Format: JPG, PNG, GIF. Maksimal 2MB. Ukuran disarankan: 300x150px</small>
                    </div>

                    <div class="form-group">
                        <label for="kop_surat">Kop Surat untuk PDF <span class="text-muted">(Opsional)</span></label>
                        <div class="mb-2">
                            <?php 
                            $kop_path = null;
                            $kop_file_exists = false;
                            $kop_full_path = null;
                            
                            if ($settings->kop_surat) {
                                // Normalisasi path - ganti backslash dengan forward slash
                                $kop_path = str_replace('\\', '/', trim($settings->kop_surat));
                                
                                // Jika path tidak dimulai dengan uploads/, tambahkan
                                if (strpos($kop_path, 'uploads/') !== 0) {
                                    $kop_path = 'uploads/kop_surat/' . basename($kop_path);
                                }
                                
                                // Cek file exists dengan berbagai kemungkinan path
                                $kop_full_path = FCPATH . str_replace('/', DIRECTORY_SEPARATOR, $kop_path);
                                if (file_exists($kop_full_path)) {
                                    $kop_file_exists = true;
                                } else {
                                    // Coba path alternatif
                                    $alt_paths = array(
                                        FCPATH . 'uploads' . DIRECTORY_SEPARATOR . 'kop_surat' . DIRECTORY_SEPARATOR . 'kop_surat.jpg',
                                        FCPATH . 'uploads' . DIRECTORY_SEPARATOR . 'kop_surat' . DIRECTORY_SEPARATOR . basename($kop_path),
                                        FCPATH . str_replace('uploads/kop_surat/', 'uploads\\kop_surat\\', $kop_path),
                                        FCPATH . $settings->kop_surat
                                    );
                                    
                                    foreach ($alt_paths as $alt_path) {
                                        if (file_exists($alt_path)) {
                                            $kop_path = 'uploads/kop_surat/' . basename($alt_path);
                                            $kop_file_exists = true;
                                            $kop_full_path = $alt_path;
                                            break;
                                        }
                                    }
                                }
                            } else {
                                // Cek apakah ada file default kop_surat.jpg
                                $default_path = FCPATH . 'uploads' . DIRECTORY_SEPARATOR . 'kop_surat' . DIRECTORY_SEPARATOR . 'kop_surat.jpg';
                                if (file_exists($default_path)) {
                                    $kop_path = 'uploads/kop_surat/kop_surat.jpg';
                                    $kop_file_exists = true;
                                    $kop_full_path = $default_path;
                                }
                            }
                            ?>
                            <?php if ($kop_path && $kop_file_exists): ?>
                                <div class="mb-2">
                                    <?php 
                                    // Gunakan controller method untuk serve file (lebih aman)
                                    $kop_url = site_url('App_settings/view_kop_surat');
                                    // Fallback ke base_url jika diperlukan
                                    $kop_url_fallback = base_url($kop_path);
                                    ?>
                                    <img src="<?php echo $kop_url; ?>" 
                                         alt="Kop Surat" 
                                         class="img-thumbnail" 
                                         style="max-width: 100%; max-height: 400px; display: block; border: 1px solid #ddd;"
                                         onerror="this.onerror=null; this.src='<?php echo $kop_url_fallback; ?>'; if(this.complete && this.naturalWidth === 0) { this.style.display='none'; document.getElementById('kop-error').style.display='block'; }">
                                    <div id="kop-error" style="display: none; padding: 10px; border: 1px solid #f0ad4e; background: #fff3cd; border-radius: 4px;">
                                        <i class="fas fa-exclamation-triangle text-warning"></i> 
                                        <strong>Gagal memuat kop surat</strong><br>
                                        <small>URL Controller: <?php echo htmlspecialchars($kop_url); ?></small><br>
                                        <small>URL Fallback: <?php echo htmlspecialchars($kop_url_fallback); ?></small><br>
                                        <small>Path: <?php echo htmlspecialchars($kop_path); ?></small>
                                    </div>
                                </div>
                                <small class="text-muted">Kop surat saat ini</small>
                                <br>
                                <button type="button" class="btn btn-sm btn-danger mt-2" onclick="deleteKopSurat()">
                                    <i class="fas fa-trash"></i> Hapus Kop Surat
                                </button>
                            <?php else: ?>
                                <div class="alert alert-info">
                                    <i class="fas fa-file-image fa-3x text-muted mb-2"></i>
                                    <p class="mb-0"><strong>Belum ada kop surat yang diupload</strong></p>
                                    <?php if ($settings->kop_surat): ?>
                                        <hr>
                                        <small class="text-muted">
                                            <strong>Path di database:</strong> <?php echo htmlspecialchars($settings->kop_surat); ?><br>
                                            <?php if (isset($kop_full_path)): ?>
                                                <strong>Path lengkap:</strong> <?php echo htmlspecialchars($kop_full_path); ?><br>
                                            <?php endif; ?>
                                            <strong>Status:</strong> <span class="text-danger">File tidak ditemukan</span>
                                        </small>
                                    <?php endif; ?>
                                </div>
                            <?php endif; ?>
                        </div>
                        <input type="file" name="kop_surat" id="kop_surat" class="form-control-file" accept="image/jpeg,image/jpg">
                        <small class="form-text text-muted">
                            Format: JPG/JPEG saja. Maksimal 5MB. 
                            Ukuran disarankan: Lebar 21cm (A4) dengan tinggi sesuai kebutuhan. 
                            Kop surat ini akan digunakan di semua cetak PDF.
                        </small>
                    </div>

                    <div class="form-group">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> Simpan Setting
                        </button>
                    </div>
                <?php echo form_close(); ?>
            </div>
        </div>
        
        <!-- Backup Database Card -->
        <div class="card card-warning card-outline mt-3">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-database"></i> Backup Database
                </h3>
            </div>
            <div class="card-body">
                <p class="text-muted">Lakukan backup database untuk menjaga keamanan data. File backup akan didownload secara otomatis.</p>
                <div class="alert alert-info">
                    <i class="fas fa-info-circle"></i>
                    <strong>Catatan:</strong> Backup mencakup semua tabel dan data dalam database. File backup berformat SQL dan dapat di-restore melalui phpMyAdmin atau command line MySQL.
                </div>
                <a href="<?php echo site_url('App_settings/backup_database'); ?>" class="btn btn-warning" onclick="return confirm('Apakah Anda yakin ingin melakukan backup database? Proses ini mungkin memakan waktu beberapa saat.');">
                    <i class="fas fa-download"></i> Download Backup Database
                </a>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card card-info card-outline">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-info-circle"></i> Informasi
                </h3>
            </div>
            <div class="card-body">
                <p><strong>Petunjuk:</strong></p>
                <ul>
                    <li>Nama kampus dan nama prodi akan digunakan di seluruh aplikasi</li>
                    <li>Logo kampus akan ditampilkan di header aplikasi dan kop surat</li>
                    <li>Kop header adalah HTML yang akan digunakan saat cetak PDF</li>
                    <li>Variabel yang bisa digunakan di kop header:
                        <ul>
                            <li><code>{NAMA_KAMPUS}</code> - Nama kampus</li>
                            <li><code>{NAMA_PRODI}</code> - Nama program studi</li>
                            <li><code>{LOGO}</code> - Tag img untuk logo</li>
                        </ul>
                    </li>
                </ul>
                <hr>
                <p><strong>Fitur Editor:</strong></p>
                <ul>
                    <li><strong>Font Size:</strong> Pilih ukuran font dari dropdown</li>
                    <li><strong>Font Family:</strong> Pilih jenis font dari dropdown</li>
                    <li><strong>Bold (B):</strong> Teks tebal</li>
                    <li><strong>Italic (I):</strong> Teks miring</li>
                    <li><strong>Underline (U):</strong> Teks bergaris bawah</li>
                    <li><strong>Enter:</strong> Tekan Enter untuk baris baru</li>
                </ul>
                <hr>
                <p><strong>Contoh Kop Header:</strong></p>
                <div class="bg-light p-2 small">
                    <p class="mb-1">Gunakan toolbar untuk memformat:</p>
                    <ul class="mb-0 small">
                        <li>Pilih font size dan jenis font</li>
                        <li>Bold untuk nama kampus</li>
                        <li>Italic untuk nama prodi</li>
                        <li>Enter untuk baris baru</li>
                    </ul>
                    <p class="mt-2 mb-0"><strong>Variabel:</strong></p>
                    <ul class="mb-0 small">
                        <li><code>{NAMA_KAMPUS}</code> - Nama kampus</li>
                        <li><code>{NAMA_PRODI}</code> - Nama program studi</li>
                        <li><code>{LOGO}</code> - Logo kampus</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function deleteLogo() {
    if (confirm('Apakah Anda yakin ingin menghapus logo?')) {
        var form = document.createElement('form');
        form.method = 'POST';
        form.action = '<?php echo site_url('App_settings/delete_logo'); ?>';
        document.body.appendChild(form);
        form.submit();
    }
}

function deleteKopSurat() {
    if (confirm('Apakah Anda yakin ingin menghapus kop surat?')) {
        var form = document.createElement('form');
        form.method = 'POST';
        form.action = '<?php echo site_url('App_settings/delete_kop_surat'); ?>';
        document.body.appendChild(form);
        form.submit();
    }
}
</script>

