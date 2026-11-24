<div class="row">
    <div class="col-12">
        <div class="card card-primary">
            <div class="card-header">
                <h3 class="card-title">Tambah User</h3>
            </div>
            <?php echo form_open('Management_user/create', array('method' => 'post', 'id' => 'form-create-user')); ?>
            <div class="card-body">
                <?php if (validation_errors()): ?>
                    <div class="alert alert-danger">
                        <?php echo validation_errors(); ?>
                    </div>
                <?php endif; ?>

                <?php if ($this->session->flashdata('error')): ?>
                    <div class="alert alert-danger">
                        <?php echo $this->session->flashdata('error'); ?>
                    </div>
                <?php endif; ?>

                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="username">Username <span class="text-danger">*</span></label>
                            <input type="text" name="username" id="username" class="form-control" 
                                   value="<?php echo set_value('username'); ?>" 
                                   placeholder="Username" required>
                            <small class="form-text text-muted">Username harus unik</small>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="role_id">Role <span class="text-danger">*</span></label>
                            <select name="role_id" id="role_id" class="form-control" required>
                                <option value="">-- Pilih Role --</option>
                                <?php foreach ($roles as $role): ?>
                                    <option value="<?php echo $role->id; ?>" <?php echo set_select('role_id', $role->id); ?>>
                                        <?php echo strtoupper($role->nama_role); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="password">Password <span class="text-danger">*</span></label>
                            <input type="password" name="password" id="password" class="form-control" 
                                   placeholder="Minimal 6 karakter" required minlength="6">
                            <small class="form-text text-muted">Password minimal 6 karakter</small>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="password_confirm">Konfirmasi Password <span class="text-danger">*</span></label>
                            <input type="password" name="password_confirm" id="password_confirm" class="form-control" 
                                   placeholder="Ulangi password" required minlength="6">
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-12">
                        <div class="form-group">
                            <label for="ref_id">Referensi ID (Opsional)</label>
                            <select name="ref_id" id="ref_id" class="form-control">
                                <option value="">-- Pilih Referensi (Opsional) --</option>
                            </select>
                            <small class="form-text text-muted">
                                Pilih referensi jika user terkait dengan data Dosen, Mahasiswa, atau Tendik. 
                                Kosongkan untuk Admin, Kaprodi, atau Sekprodi.
                            </small>
                        </div>
                    </div>
                </div>

                <div class="alert alert-info">
                    <i class="fas fa-info-circle"></i> 
                    <strong>Catatan:</strong> 
                    <ul class="mb-0 mt-2">
                        <li>User account untuk Dosen, Mahasiswa, dan Tendik biasanya dibuat otomatis saat menambahkan data di modul masing-masing</li>
                        <li>Gunakan form ini untuk membuat user account manual, terutama untuk Admin, Kaprodi, dan Sekprodi</li>
                        <li>Jika memilih role Dosen, Mahasiswa, atau Tendik, pilih referensi yang sesuai</li>
                    </ul>
                </div>
            </div>
            <div class="card-footer">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i> Simpan
                </button>
                <a href="<?php echo site_url('Management_user'); ?>" class="btn btn-secondary">
                    <i class="fas fa-times"></i> Batal
                </a>
            </div>
            <?php echo form_close(); ?>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const roleSelect = document.getElementById('role_id');
    const refSelect = document.getElementById('ref_id');
    
    roleSelect.addEventListener('change', function() {
        const roleId = this.value;
        refSelect.innerHTML = '<option value="">-- Memuat data...</option>';
        refSelect.disabled = true;
        
        if (!roleId) {
            refSelect.innerHTML = '<option value="">-- Pilih Referensi (Opsional) --</option>';
            refSelect.disabled = false;
            return;
        }
        
        // AJAX request untuk mengambil daftar referensi
        fetch('<?php echo site_url("Management_user/get_ref_list"); ?>', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: 'role_id=' + encodeURIComponent(roleId)
        })
        .then(response => response.json())
        .then(data => {
            refSelect.innerHTML = '<option value="">-- Pilih Referensi (Opsional) --</option>';
            
            if (data.success && data.data && data.data.length > 0) {
                data.data.forEach(function(item) {
                    const option = document.createElement('option');
                    option.value = item.id;
                    option.textContent = item.text;
                    refSelect.appendChild(option);
                });
            } else {
                const option = document.createElement('option');
                option.value = '';
                option.textContent = '-- Tidak ada data referensi --';
                refSelect.appendChild(option);
            }
            
            refSelect.disabled = false;
        })
        .catch(error => {
            console.error('Error:', error);
            refSelect.innerHTML = '<option value="">-- Error memuat data --</option>';
            refSelect.disabled = false;
        });
    });
    
    // Validasi form
    document.getElementById('form-create-user').addEventListener('submit', function(e) {
        const password = document.getElementById('password').value;
        const passwordConfirm = document.getElementById('password_confirm').value;
        
        if (password !== passwordConfirm) {
            e.preventDefault();
            alert('Password dan Konfirmasi Password tidak sama!');
            return false;
        }
    });
});
</script>
















