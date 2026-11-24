<div class="row">
    <div class="col-md-8">
        <div class="card card-primary card-outline">
            <div class="card-header">
                <h3 class="card-title"><i class="fab fa-google-drive"></i> Pengaturan Google Drive OAuth</h3>
            </div>
            <div class="card-body">
                <?php if ($this->session->flashdata('success')): ?>
                    <div class="alert alert-success alert-dismissible">
                        <button type="button" class="close" data-dismiss="alert">&times;</button>
                        <?php echo $this->session->flashdata('success'); ?>
                    </div>
                <?php endif; ?>
                <?php if ($this->session->flashdata('error')): ?>
                    <div class="alert alert-danger alert-dismissible">
                        <button type="button" class="close" data-dismiss="alert">&times;</button>
                        <?php echo $this->session->flashdata('error'); ?>
                    </div>
                <?php endif; ?>

                <div class="mb-3">
                    <?php if (!empty($is_configured)): ?>
                        <span class="badge badge-success">OAuth sudah dikonfigurasi</span>
                    <?php else: ?>
                        <span class="badge badge-warning">Belum lengkap — isi Client ID &amp; Secret</span>
                    <?php endif; ?>
                </div>

                <?php echo form_open('Google_oauth_settings/save'); ?>
                    <div class="form-group">
                        <label for="client_id">Client ID <span class="text-danger">*</span></label>
                        <input type="text" name="client_id" id="client_id" class="form-control"
                               value="<?php echo htmlspecialchars($settings->client_id); ?>" required
                               placeholder="xxxx.apps.googleusercontent.com">
                    </div>

                    <div class="form-group">
                        <label for="client_secret">Client Secret <?php echo empty($has_secret) ? '<span class="text-danger">*</span>' : ''; ?></label>
                        <input type="password" name="client_secret" id="client_secret" class="form-control"
                               placeholder="<?php echo !empty($has_secret) ? 'Kosongkan jika tidak ingin mengubah secret' : 'Masukkan Client Secret'; ?>"
                               autocomplete="new-password">
                        <?php if (!empty($has_secret)): ?>
                            <small class="form-text text-muted">Secret tersimpan (terenkripsi). Isi ulang hanya jika ingin mengganti.</small>
                        <?php endif; ?>
                    </div>

                    <div class="form-group">
                        <label for="redirect_uri">Redirect URI (referensi)</label>
                        <input type="url" name="redirect_uri" id="redirect_uri" class="form-control"
                               value="<?php echo htmlspecialchars($suggested_redirect); ?>" readonly>
                        <small class="form-text text-muted">
                            Sistem memakai URI host saat ini secara otomatis.<br>
                            <strong>Wajib daftarkan keduanya</strong> di Google Cloud → Clients → Authorized redirect URIs:
                        </small>
                        <ul class="small mb-0 mt-1">
                            <li><code>http://localhost/siap_prodi/index.php/Profile/oauth_callback</code> (lokal)</li>
                            <li><code>https://siapprodi.siharkatnegeri.com/index.php/Profile/oauth_callback</code> (hosting)</li>
                        </ul>
                    </div>

                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Simpan
                    </button>
                    <a href="<?php echo site_url('App_settings'); ?>" class="btn btn-secondary">Kembali</a>
                <?php echo form_close(); ?>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card card-outline card-info">
            <div class="card-header"><h3 class="card-title">Petunjuk singkat</h3></div>
            <div class="card-body">
                <ol class="pl-3 mb-0">
                    <li>Buka Google Cloud Console → APIs &amp; Services → Credentials.</li>
                    <li>Buat OAuth Client ID (Web application).</li>
                    <li>Tambahkan Redirect URI di atas.</li>
                    <li>Aktifkan <strong>Google Drive API</strong>.</li>
                    <li>Paste Client ID &amp; Secret ke form ini.</li>
                    <li>Hanya <strong>admin</strong> yang Hubungkan Drive di menu <strong>Profil</strong> (satu akun institusi untuk seluruh sistem).</li>
                    <li>Jika kapasitas penuh: admin Putuskan lalu Hubungkan akun Google lain.</li>
                </ol>
                <hr>
                <p class="mb-0 small text-muted">
                    Scope: <code>drive.file</code> + <code>userinfo.email</code>. Folder otomatis: <code>SIAP/Modul/...</code>.
                </p>
            </div>
        </div>
    </div>
</div>
