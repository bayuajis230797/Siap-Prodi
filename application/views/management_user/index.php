<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Daftar User</h3>
                <div class="card-tools">
                    <a href="<?php echo site_url('Management_user/create'); ?>" class="btn btn-primary btn-sm">
                        <i class="fas fa-plus"></i> Tambah User
                    </a>
                </div>
            </div>
            <div class="card-body">
                <div class="alert alert-info">
                    <i class="fas fa-info-circle"></i> 
                    <strong>Informasi:</strong> 
                    <ul class="mb-0 mt-2">
                        <li>User account untuk Dosen, Mahasiswa, dan Tendik akan otomatis dibuat saat menambahkan data di masing-masing modul</li>
                        <li>Management User ini digunakan untuk mengelola user account secara manual, terutama untuk Admin, Kaprodi, dan Sekprodi</li>
                        <li>Reset password akan mengembalikan password user menjadi sama dengan username</li>
                        <li>Anda tidak dapat menghapus akun sendiri</li>
                    </ul>
                </div>
                
                <!-- Search Box dan Show Entries -->
                <div class="row mb-3">
                    <div class="col-md-4">
                        <div class="input-group">
                            <div class="input-group-prepend">
                                <span class="input-group-text"><i class="fas fa-search"></i></span>
                            </div>
                            <input type="text" id="searchUser" class="form-control" placeholder="Cari Username, Role, Referensi...">
                        </div>
                    </div>
                    <div class="col-md-4 offset-md-4">
                        <form method="get" action="<?php echo site_url('Management_user/index'); ?>" id="formPerPageUser" class="form-inline justify-content-end">
                            <label for="per_page_user" class="mr-2">Tampilkan:</label>
                            <select name="per_page" id="per_page_user" class="form-control form-control-sm" onchange="document.getElementById('formPerPageUser').submit();">
                                <option value="10" <?php echo (isset($per_page) && $per_page == 10) ? 'selected' : ''; ?>>10</option>
                                <option value="25" <?php echo (isset($per_page) && $per_page == 25) ? 'selected' : ''; ?>>25</option>
                                <option value="50" <?php echo (isset($per_page) && $per_page == 50) ? 'selected' : ''; ?>>50</option>
                                <option value="100" <?php echo (isset($per_page) && $per_page == 100) ? 'selected' : ''; ?>>100</option>
                            </select>
                            <span class="ml-2">data</span>
                        </form>
                    </div>
                </div>
                
                <div class="table-responsive">
                    <table class="table table-bordered table-striped table-hover" id="tableUser">
                        <thead>
                            <tr>
                                <th width="5%">No</th>
                                <th width="15%">Username</th>
                                <th width="15%">Role</th>
                                <th>Referensi</th>
                                <th width="15%" class="text-center">Aktivitas</th>
                                <th width="12%" class="text-center">Pindah Login</th>
                                <th width="18%" class="text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($users)): ?>
                                <tr>
                                    <td colspan="7" class="text-center text-muted">
                                        <i class="fas fa-inbox"></i> Belum ada data user.
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php 
                                $no = 1; 
                                $page = ($this->uri->segment(3)) ? $this->uri->segment(3) : 1;
                                $no = ($page - 1) * 10 + 1;
                                foreach ($users as $u): 
                                ?>
                                    <tr>
                                        <td><?php echo $no++; ?></td>
                                        <td><strong><?php echo $u->username; ?></strong></td>
                                        <td>
                                            <span class="badge badge-primary">
                                                <?php echo strtoupper($u->nama_role); ?>
                                            </span>
                                        </td>
                                        <td>
                                            <?php if ($u->ref_id && $u->ref_data): ?>
                                                <?php
                                                $ref_text = '';
                                                switch ($u->nama_role) {
                                                    case 'dosen':
                                                        $ref_text = $u->ref_data->nidn . ' - ' . $u->ref_data->nama_dosen;
                                                        break;
                                                    case 'mahasiswa':
                                                        $ref_text = $u->ref_data->nim . ' - ' . $u->ref_data->nama_mahasiswa;
                                                        break;
                                                    case 'tendik':
                                                        $ref_text = $u->ref_data->nipy . ' - ' . $u->ref_data->nama_tendik;
                                                        break;
                                                    default:
                                                        $ref_text = 'ID: ' . $u->ref_id;
                                                }
                                                ?>
                                                <small class="text-muted"><?php echo $ref_text; ?></small>
                                            <?php else: ?>
                                                <span class="text-muted">-</span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="text-center">
                                            <?php 
                                            // Jika user sedang login (ada di session aktif)
                                            if (isset($u->is_online) && $u->is_online): 
                                                // Jika mahasiswa, tampilkan info mahasiswa
                                                if ($u->nama_role == 'mahasiswa' && $u->ref_data): 
                                            ?>
                                                <span class="badge badge-success mb-1">
                                                    <i class="fas fa-circle"></i> Sedang Login
                                                </span>
                                                <br>
                                                <small class="text-muted" style="font-size: 0.85em;">
                                                    <?php echo htmlspecialchars($u->ref_data->nim ?? '-'); ?><br>
                                                    <?php echo htmlspecialchars($u->ref_data->nama_mahasiswa ?? '-'); ?>
                                                </small>
                                            <?php 
                                                else: 
                                                    // User lain yang sedang login
                                            ?>
                                                <span class="badge badge-success mb-1">
                                                    <i class="fas fa-circle"></i> Sedang Login
                                                </span>
                                            <?php 
                                                endif;
                                            else: 
                                                // User tidak sedang login, tampilkan last logout
                                            ?>
                                                <small class="text-muted">
                                                    <i class="fas fa-clock"></i> Last Logout<br>
                                                    <?php 
                                                    // Tampilkan last_logout jika ada, jika tidak tampilkan last_login
                                                    if (isset($u->last_logout) && $u->last_logout): 
                                                        echo date('d/m/Y H:i', strtotime($u->last_logout)); 
                                                    elseif (isset($u->last_login) && $u->last_login): 
                                                        echo date('d/m/Y H:i', strtotime($u->last_login)); 
                                                    else: 
                                                        echo '-';
                                                    endif;
                                                    ?>
                                                </small>
                                            <?php endif; ?>
                                        </td>
                                        <td class="text-center">
                                            <?php if ($u->id != $this->session->userdata('user_id')): ?>
                                                <form action="<?php echo site_url('Management_user/pindah_login/' . $u->id); ?>" method="post" style="display:inline;">
                                                    <button type="submit" class="btn btn-primary btn-sm" onclick="return confirm('Pindah login ke user ini?');">
                                                        <i class="fas fa-sign-in-alt"></i> Pindah
                                                    </button>
                                                </form>
                                            <?php else: ?>
                                                <button class="btn btn-secondary btn-sm" disabled>
                                                    <i class="fas fa-sign-in-alt"></i> Pindah
                                                </button>
                                            <?php endif; ?>
                                        </td>
                                        <td class="text-center">
                                            <form action="<?php echo site_url('Management_user/reset_password/' . $u->id); ?>" method="post" style="display:inline;">
                                                <button type="submit" class="btn btn-warning btn-sm" title="Reset Password" onclick="return confirm('Reset password user <?php echo htmlspecialchars($u->username, ENT_QUOTES); ?>?\nPassword akan kembali sama dengan username.');">
                                                    <i class="fas fa-key"></i>
                                                </button>
                                            </form>
                                            <?php if ($u->id != $this->session->userdata('user_id')): ?>
                                                <a href="<?php echo site_url('Management_user/delete/' . $u->id); ?>" 
                                                   class="btn btn-danger btn-sm" 
                                                   title="Hapus"
                                                   onclick="return confirm('Hapus user ini?')">
                                                    <i class="fas fa-trash"></i>
                                                </a>
                                            <?php else: ?>
                                                <button class="btn btn-danger btn-sm" disabled title="Tidak dapat menghapus akun sendiri">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
                
                <?php if (isset($pagination) && !empty($pagination)): ?>
                    <div class="row mt-3">
                        <div class="col-md-6">
                            <p class="text-muted">
                                Menampilkan <?php echo count($users); ?> dari <?php echo $total_rows; ?> data
                            </p>
                        </div>
                        <div class="col-md-6">
                            <?php echo $pagination; ?>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('searchUser');
    const table = document.getElementById('tableUser');
    const tbody = table.querySelector('tbody');
    const rows = tbody.querySelectorAll('tr');
    
    searchInput.addEventListener('keyup', function() {
        const searchText = this.value.toLowerCase();
        
        rows.forEach(function(row) {
            const text = row.textContent.toLowerCase();
            if (text.includes(searchText)) {
                row.style.display = '';
            } else {
                row.style.display = 'none';
            }
        });
    });
});
</script>







