<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-history"></i> Aktivitas User
                </h3>
                <div class="card-tools">
                    <?php
                    $export_qs = http_build_query(array_filter(array(
                        'user_id' => $filters['user_id'] ?? '',
                        'activity_type' => $filters['activity_type'] ?? '',
                        'date_from' => $filters['date_from'] ?? '',
                        'date_to' => $filters['date_to'] ?? '',
                        'role' => $filters['role'] ?? '',
                        'search' => $filters['search'] ?? '',
                    )));
                    ?>
                    <a href="<?php echo site_url('Aktivitas_user/export' . ($export_qs ? '?' . $export_qs : '')); ?>" class="btn btn-success btn-sm">
                        <i class="fas fa-file-csv"></i> Ekspor CSV
                    </a>
                    <button type="button" class="btn btn-danger btn-sm" data-toggle="modal" data-target="#modalFormatAktivitas" data-bs-toggle="modal" data-bs-target="#modalFormatAktivitas">
                        <i class="fas fa-eraser"></i> Format Aktivitas
                    </button>
                    <a href="<?php echo site_url('Aktivitas_user'); ?>" class="btn btn-secondary btn-sm">
                        <i class="fas fa-sync-alt"></i> Reset Filter
                    </a>
                </div>
            </div>
            <div class="card-body">
                <?php if (!$table_exists || !$detail_table_exists): ?>
                    <div class="alert alert-warning">
                        <i class="fas fa-exclamation-triangle"></i>
                        <strong>Tabel aktivitas user belum lengkap.</strong>
                        Jalankan migration
                        <code>database/migrations/2026_02_user_activity_log.sql</code>
                        dan
                        <code>database/migrations/2026_02_user_activity_detail.sql</code>
                        untuk mengaktifkan fitur ini.
                        Setelah migration dijalankan, aktivitas login, logout, dan detail aksi user akan tercatat.
                    </div>
                <?php else: ?>
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle"></i>
                        Halaman ini menampilkan riwayat aktivitas login dan logout setiap user.
                    </div>

                    <!-- Filter -->
                    <form method="get" action="<?php echo site_url('Aktivitas_user/index'); ?>" class="mb-4">
                        <div class="row">
                            <div class="col-md-2">
                                <label>Nama User</label>
                                <select name="user_id" class="form-control form-control-sm">
                                    <option value="">-- Semua Nama User --</option>
                                    <?php foreach ($users_for_filter as $u): ?>
                                        <option value="<?php echo $u->id; ?>" <?php echo (isset($filters['user_id']) && $filters['user_id'] == $u->id) ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($u->nama_user ?: $u->username); ?> (<?php echo htmlspecialchars($u->nama_role); ?>)
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label>Role</label>
                                <select name="role" class="form-control form-control-sm">
                                    <option value="">-- Semua Role --</option>
                                    <?php foreach ($roles as $r): ?>
                                        <option value="<?php echo htmlspecialchars($r); ?>" <?php echo (isset($filters['role']) && $filters['role'] == $r) ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars(ucfirst($r)); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label>Tipe</label>
                                <select name="activity_type" class="form-control form-control-sm">
                                    <option value="">-- Semua --</option>
                                    <option value="login" <?php echo (isset($filters['activity_type']) && $filters['activity_type'] == 'login') ? 'selected' : ''; ?>>Login</option>
                                    <option value="login_failed" <?php echo (isset($filters['activity_type']) && $filters['activity_type'] == 'login_failed') ? 'selected' : ''; ?>>Login Gagal</option>
                                    <option value="login_locked" <?php echo (isset($filters['activity_type']) && $filters['activity_type'] == 'login_locked') ? 'selected' : ''; ?>>Login Locked</option>
                                    <option value="logout" <?php echo (isset($filters['activity_type']) && $filters['activity_type'] == 'logout') ? 'selected' : ''; ?>>Logout</option>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label>Tanggal Mulai</label>
                                <input type="date" name="date_from" class="form-control form-control-sm" value="<?php echo htmlspecialchars($filters['date_from'] ?? ''); ?>">
                            </div>
                            <div class="col-md-2">
                                <label>Tanggal Selesai</label>
                                <input type="date" name="date_to" class="form-control form-control-sm" value="<?php echo htmlspecialchars($filters['date_to'] ?? ''); ?>">
                            </div>
                            <div class="col-md-2">
                                <label>&nbsp;</label>
                                <div class="d-flex">
                                    <button type="submit" class="btn btn-info btn-sm mr-1">
                                        <i class="fas fa-filter"></i> Filter
                                    </button>
                                    <input type="hidden" name="per_page" value="<?php echo $per_page; ?>">
                                </div>
                            </div>
                        </div>
                        <div class="row mt-2">
                            <div class="col-md-4">
                                <input type="text" name="search" class="form-control form-control-sm" placeholder="Cari nama user, username, atau deskripsi..." value="<?php echo htmlspecialchars($filters['search'] ?? ''); ?>">
                            </div>
                        </div>
                    </form>

                    <!-- Tabel Aktivitas -->
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped table-hover">
                            <thead>
                                <tr>
                                    <th width="5%">No</th>
                                    <th width="12%">Waktu</th>
                                    <th width="18%">Nama User</th>
                                    <th width="12%">Role</th>
                                    <th width="6%" class="text-center">Mood</th>
                                    <th width="10%">Tipe</th>
                                    <th>Deskripsi</th>
                                    <th width="12%">IP Address</th>
                                    <th width="8%" class="text-center">View</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($activity_list)): ?>
                                    <tr>
                                        <td colspan="9" class="text-center text-muted">
                                            <i class="fas fa-inbox"></i> Belum ada data aktivitas.
                                        </td>
                                    </tr>
                                <?php else: ?>
                                    <?php 
                                    $page_num = isset($page) ? (int)$page : 1;
                                    $no = $page_num > 1 ? ($page_num - 1) * $per_page + 1 : 1;
                                    foreach ($activity_list as $a): 
                                    ?>
                                        <tr>
                                            <td><?php echo $no++; ?></td>
                                            <td><?php echo date('d/m/Y H:i:s', strtotime($a->created_at)); ?></td>
                                            <td>
                                                <?php
                                                $display_name = $a->nama_user ?? $a->username ?? '-';
                                                // Untuk login gagal, username bisa tidak ada di tabel users (user_id=0),
                                                // jadi ambil dari description.
                                                if (($a->activity_type ?? '') === 'login_failed') {
                                                    $display_name = '-';
                                                    if (!empty($a->description) && preg_match('/username:\s*(.+)$/i', $a->description, $m)) {
                                                        $display_name = trim($m[1]);
                                                    }
                                                }
                                                ?>
                                                <strong><?php echo htmlspecialchars($display_name); ?></strong>
                                                <?php if (!empty($a->username) && ($a->nama_user ?? '') !== $a->username): ?>
                                                    <br><small class="text-muted">@<?php echo htmlspecialchars($a->username); ?></small>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <span class="badge badge-primary"><?php echo htmlspecialchars(ucfirst($a->nama_role ?? '-')); ?></span>
                                            </td>
                                            <td class="text-center">
                                                <?php
                                                $mood = '';
                                                if (!empty($a->description) && preg_match('/\bmood=([^\|\s]+)/u', $a->description, $mm)) {
                                                    $mood = trim($mm[1]);
                                                }
                                                ?>
                                                <span style="font-size: 18px;" title="<?php echo $mood ? 'Mood dipilih saat login' : 'Tidak ada mood'; ?>">
                                                    <?php echo $mood ? htmlspecialchars($mood) : '-'; ?>
                                                </span>
                                            </td>
                                            <td>
                                                <?php if ($a->activity_type === 'login'): ?>
                                                    <span class="badge badge-success">Login</span>
                                                <?php elseif ($a->activity_type === 'login_failed'): ?>
                                                    <span class="badge badge-danger">Login Gagal</span>
                                                <?php elseif ($a->activity_type === 'login_locked'): ?>
                                                    <span class="badge badge-warning">Locked</span>
                                                <?php else: ?>
                                                    <span class="badge badge-secondary">Logout</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <?php
                                                $desc = $a->description ?? '-';
                                                $attempts_badge = '';
                                                if (!empty($desc) && preg_match('/\battempts=(\d+)\/(\d+)/i', $desc, $am)) {
                                                    $cur = (int)$am[1];
                                                    $max = (int)$am[2];
                                                    if ($max > 0) {
                                                        if ($cur >= $max) {
                                                            $attempts_badge = '<span class="badge badge-dark ml-1" title="Mencapai batas percobaan">Suspicious ' . $cur . '/' . $max . '</span>';
                                                        } else {
                                                            $attempts_badge = '<span class="badge badge-light ml-1" title="Jumlah percobaan gagal">' . $cur . '/' . $max . '</span>';
                                                        }
                                                    }
                                                }
                                                ?>
                                                <?php echo htmlspecialchars($desc); ?> <?php echo $attempts_badge; ?>
                                            </td>
                                            <td>
                                                <?php $ip = trim((string)($a->ip_address ?? '')); ?>
                                                <small class="text-muted"><?php echo $ip !== '' ? htmlspecialchars($ip) : '-'; ?></small>
                                            </td>
                                            <td class="text-center">
                                                <?php if ($a->activity_type === 'login'): ?>
                                                    <a href="<?php echo site_url('Aktivitas_user/detail/' . $a->id); ?>"
                                                       class="btn btn-sm btn-info"
                                                       title="Lihat detail aktivitas sesi login">
                                                        <i class="fas fa-eye"></i>
                                                        <?php if (isset($a->detail_count) && $a->detail_count > 0): ?>
                                                            <span class="badge badge-light ml-1"><?php echo (int) $a->detail_count; ?></span>
                                                        <?php endif; ?>
                                                    </a>
                                                <?php else: ?>
                                                    <span class="text-muted">-</span>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    <div class="row mt-3">
                        <div class="col-md-6">
                            <p class="text-muted small mb-0">
                                Menampilkan <?php echo count($activity_list); ?> dari <?php echo $total_rows; ?> data
                            </p>
                        </div>
                        <div class="col-md-6">
                            <form method="get" action="<?php echo site_url('Aktivitas_user/index'); ?>" class="float-right">
                                <?php foreach (array_filter($filters) as $k => $v): ?>
                                    <input type="hidden" name="<?php echo htmlspecialchars($k); ?>" value="<?php echo htmlspecialchars($v); ?>">
                                <?php endforeach; ?>
                                <label class="mr-2">Tampilkan:</label>
                                <select name="per_page" class="form-control form-control-sm d-inline-block w-auto" onchange="this.form.submit()">
                                    <option value="10" <?php echo $per_page == 10 ? 'selected' : ''; ?>>10</option>
                                    <option value="25" <?php echo $per_page == 25 ? 'selected' : ''; ?>>25</option>
                                    <option value="50" <?php echo $per_page == 50 ? 'selected' : ''; ?>>50</option>
                                    <option value="100" <?php echo $per_page == 100 ? 'selected' : ''; ?>>100</option>
                                </select>
                            </form>
                        </div>
                    </div>
                    <?php if (!empty($pagination)): ?>
                        <div class="mt-2"><?php echo $pagination; ?></div>
                    <?php endif; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php if (!empty($table_exists)): ?>
<div class="modal fade" id="modalFormatAktivitas" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <?php echo form_open('Aktivitas_user/format'); ?>
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title"><i class="fas fa-eraser"></i> Format Aktivitas</h5>
                <button type="button" class="close text-white" data-dismiss="modal" data-bs-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="alert alert-warning mb-3">
                    <strong>Perhatian:</strong> Format akan menghapus log aktivitas secara permanen (termasuk detail sesi).
                </div>
                <div class="form-group">
                    <label>Mode</label>
                    <select name="mode" id="formatMode" class="form-control" required>
                        <option value="filtered">Format sesuai filter di bawah</option>
                        <option value="all">Format SEMUA aktivitas</option>
                    </select>
                </div>
                <div id="formatFilters">
                    <div class="form-row">
                        <div class="form-group col-md-6">
                            <label>Dari tanggal</label>
                            <input type="date" name="date_from" class="form-control" value="<?php echo htmlspecialchars($filters['date_from'] ?? ''); ?>">
                        </div>
                        <div class="form-group col-md-6">
                            <label>Sampai tanggal</label>
                            <input type="date" name="date_to" class="form-control" value="<?php echo htmlspecialchars($filters['date_to'] ?? ''); ?>">
                        </div>
                    </div>
                    <div class="form-group">
                        <label>Tipe aktivitas</label>
                        <select name="activity_type" class="form-control">
                            <option value="">Semua tipe</option>
                            <option value="login">Login</option>
                            <option value="logout">Logout</option>
                            <option value="login_failed">Login Gagal</option>
                            <option value="login_locked">Login Locked</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>User (opsional)</label>
                        <select name="user_id" class="form-control">
                            <option value="">Semua user</option>
                            <?php if (!empty($users_for_filter)): foreach ($users_for_filter as $u): ?>
                                <option value="<?php echo (int) $u->id; ?>">
                                    <?php echo htmlspecialchars(($u->nama_user ?: $u->username) . ' (' . $u->nama_role . ')'); ?>
                                </option>
                            <?php endforeach; endif; ?>
                        </select>
                    </div>
                </div>
                <div class="form-group mb-0">
                    <label>Ketik <code>FORMAT</code> untuk konfirmasi</label>
                    <input type="text" name="confirm" class="form-control" placeholder="FORMAT" autocomplete="off" required>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal" data-bs-dismiss="modal">Batal</button>
                <button type="submit" class="btn btn-danger"><i class="fas fa-eraser"></i> Format Sekarang</button>
            </div>
            <?php echo form_close(); ?>
        </div>
    </div>
</div>
<script>
(function () {
    var mode = document.getElementById('formatMode');
    var box = document.getElementById('formatFilters');
    if (!mode || !box) return;
    function sync() { box.style.display = mode.value === 'all' ? 'none' : ''; }
    mode.addEventListener('change', sync);
    sync();
})();
</script>
<?php endif; ?>
