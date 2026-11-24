<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-user-cog"></i> Profil</h3>
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

                <div class="card card-info mb-3">
                    <div class="card-header">
                        <h5 class="card-title mb-0"><i class="fas fa-user"></i> Informasi Akun</h5>
                    </div>
                    <div class="card-body">
                        <table class="table table-borderless mb-0">
                            <tr>
                                <th width="30%">Username:</th>
                                <td><strong><?php echo htmlspecialchars($user->username); ?></strong></td>
                            </tr>
                            <tr>
                                <th>Role:</th>
                                <td><span class="badge badge-primary"><?php echo ucfirst($user->nama_role); ?></span></td>
                            </tr>
                        </table>
                    </div>
                </div>

                <?php if (!empty($is_admin)): ?>
                <div class="card card-outline <?php echo !empty($drive_connected) ? 'card-success' : 'card-warning'; ?> mb-3">
                    <div class="card-header">
                        <h5 class="card-title mb-0"><i class="fab fa-google-drive"></i> Google Drive Institusi</h5>
                    </div>
                    <div class="card-body">
                        <?php if (empty($oauth_configured)): ?>
                            <div class="alert alert-warning mb-0">
                                Belum dikonfigurasi.
                                <a href="<?php echo site_url('Google_oauth_settings'); ?>">Isi Client ID &amp; Secret di sini</a>.
                            </div>
                        <?php elseif (!empty($drive_connected)): ?>
                            <p class="mb-2">
                                <span class="badge badge-success">Terhubung</span>
                                <?php if (!empty($drive->google_email)): ?>
                                    — <strong><?php echo htmlspecialchars($drive->google_email); ?></strong>
                                <?php endif; ?>
                            </p>
                            <?php if (!empty($drive->connected_at)): ?>
                                <p class="text-muted small mb-3">Terhubung sejak: <?php echo htmlspecialchars($drive->connected_at); ?></p>
                            <?php endif; ?>
                            <p class="small text-muted">
                                Seluruh unggahan dari semua role (mahasiswa, dosen, sekprodi, dll.) disimpan ke akun Drive ini
                                dengan struktur folder <code>SIAP/Modul/...</code>.
                                Jika kapasitas penuh: <strong>Putuskan</strong> lalu <strong>Hubungkan</strong> akun Google lain.
                            </p>
                            <?php echo form_open('Profile/disconnect_drive'); ?>
                                <button type="submit" class="btn btn-outline-danger"
                                        onclick="return confirm('Putuskan Drive institusi? Upload berkas di seluruh sistem akan berhenti sampai Anda Hubungkan akun lagi.');">
                                    <i class="fas fa-unlink"></i> Putuskan
                                </button>
                                <a href="<?php echo site_url('Profile/connect_drive'); ?>" class="btn btn-outline-primary">
                                    <i class="fas fa-sync"></i> Hubungkan akun lain
                                </a>
                            <?php echo form_close(); ?>
                        <?php else: ?>
                            <p class="mb-3">
                                <span class="badge badge-secondary">Belum terhubung</span>
                            </p>
                            <p class="small text-muted mb-3">
                                Hubungkan satu akun Google Drive institusi. Semua file sistem akan masuk ke akun ini.
                            </p>
                            <a href="<?php echo site_url('Profile/connect_drive'); ?>" class="btn btn-primary">
                                <i class="fab fa-google"></i> Hubungkan Google Drive
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
                <?php endif; ?>

                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0"><i class="fas fa-key"></i> Ubah Password</h5>
                    </div>
                    <div class="card-body">
                        <form method="post" action="<?php echo site_url('Profile'); ?>">
                            <input type="hidden" name="<?php echo $this->security->get_csrf_token_name(); ?>"
                                   value="<?php echo $this->security->get_csrf_hash(); ?>">
                            <div class="form-group">
                                <label for="password_lama">Password Lama <span class="text-danger">*</span></label>
                                <input type="password" name="password_lama" id="password_lama" class="form-control"
                                       placeholder="Masukkan password lama untuk verifikasi" required>
                                <?php echo form_error('password_lama', '<small class="text-danger">', '</small>'); ?>
                            </div>

                            <div class="form-group">
                                <label for="password_baru">Password Baru <span class="text-danger">*</span></label>
                                <input type="password" name="password_baru" id="password_baru" class="form-control"
                                       placeholder="Masukkan password baru" required minlength="5">
                                <?php echo form_error('password_baru', '<small class="text-danger">', '</small>'); ?>
                                <small class="form-text text-muted">Minimal 5 karakter.</small>
                            </div>

                            <div class="form-group">
                                <label for="password_baru_confirm">Konfirmasi Password Baru <span class="text-danger">*</span></label>
                                <input type="password" name="password_baru_confirm" id="password_baru_confirm" class="form-control"
                                       placeholder="Ulangi password baru" required minlength="5">
                                <?php echo form_error('password_baru_confirm', '<small class="text-danger">', '</small>'); ?>
                            </div>

                            <div class="form-group mb-0">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save"></i> Simpan Password
                                </button>
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
    const passwordBaru = document.getElementById('password_baru');
    const passwordBaruConfirm = document.getElementById('password_baru_confirm');
    function validatePasswordMatch() {
        if (passwordBaru.value && passwordBaruConfirm.value) {
            if (passwordBaru.value !== passwordBaruConfirm.value) {
                passwordBaruConfirm.setCustomValidity('Password tidak sama dengan password baru');
            } else {
                passwordBaruConfirm.setCustomValidity('');
            }
        } else {
            passwordBaruConfirm.setCustomValidity('');
        }
    }
    passwordBaru.addEventListener('input', validatePasswordMatch);
    passwordBaruConfirm.addEventListener('input', validatePasswordMatch);
});
</script>
