<div class="row">
    <div class="col-12">
        <div class="card card-primary">
            <div class="card-header">
                <h3 class="card-title">Edit User</h3>
            </div>
            <?php echo form_open('Management_user/edit/' . $user->id, array('method' => 'post', 'id' => 'form-edit-user')); ?>
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
                                   value="<?php echo set_value('username', $user->username); ?>" 
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
                                    <option value="<?php echo $role->id; ?>" 
                                            <?php echo set_select('role_id', $role->id, ($role->id == $user->role_id)); ?>>
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
                            <label for="password">Password Baru (Kosongkan jika tidak diubah)</label>
                            <input type="password" name="password" id="password" class="form-control" 
                                   placeholder="Minimal 6 karakter" minlength="6">
                            <small class="form-text text-muted">Kosongkan jika tidak ingin mengubah password</small>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="password_confirm">Konfirmasi Password Baru</label>
                            <input type="password" name="password_confirm" id="password_confirm" class="form-control" 
                                   placeholder="Ulangi password baru" minlength="6">
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

                <?php if ($user->ref_data): ?>
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle"></i> 
                        <strong>Referensi Saat Ini:</strong> 
                        <?php
                        $ref_text = '';
                        switch ($user->nama_role) {
                            case 'dosen':
                                $ref_text = $user->ref_data->nidn . ' - ' . $user->ref_data->nama_dosen;
                                break;
                            case 'mahasiswa':
                                $ref_text = $user->ref_data->nim . ' - ' . $user->ref_data->nama_mahasiswa;
                                break;
                            case 'tendik':
                                $ref_text = $user->ref_data->nipy . ' - ' . $user->ref_data->nama_tendik;
                                break;
                            default:
                                $ref_text = 'ID: ' . $user->ref_id;
                        }
                        echo $ref_text;
                        ?>
                    </div>
                <?php endif; ?>

                <div class="alert alert-warning">
                    <i class="fas fa-exclamation-triangle"></i> 
                    <strong>Peringatan:</strong> 
                    <ul class="mb-0 mt-2">
                        <li>Mengubah role atau referensi dapat mempengaruhi akses user ke sistem</li>
                        <li>Pastikan referensi yang dipilih sesuai dengan role yang dipilih</li>
                    </ul>
                </div>
            </div>
            <div class="card-footer">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i> Simpan Perubahan
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
    const currentRoleId = roleSelect.value;
    const currentRefId = <?php echo $user->ref_id ? $user->ref_id : 'null'; ?>;
    
    // Load referensi saat halaman dimuat jika role sudah dipilih
    if (currentRoleId) {
        loadRefList(currentRoleId, currentRefId);
    }
    
    roleSelect.addEventListener('change', function() {
        const roleId = this.value;
        loadRefList(roleId, null);
    });
    
    function loadRefList(roleId, selectedRefId) {
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
                    if (selectedRefId && item.id == selectedRefId) {
                        option.selected = true;
                    }
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
    }
    
    // Validasi form
    document.getElementById('form-edit-user').addEventListener('submit', function(e) {
        const password = document.getElementById('password').value;
        const passwordConfirm = document.getElementById('password_confirm').value;
        
        if (password && password !== passwordConfirm) {
            e.preventDefault();
            alert('Password dan Konfirmasi Password tidak sama!');
            return false;
        }
        
        if (password && password.length < 6) {
            e.preventDefault();
            alert('Password minimal 6 karakter!');
            return false;
        }
    });
});
</script>
















