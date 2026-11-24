<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-eye"></i> Detail Aktivitas User
                </h3>
                <div class="card-tools">
                    <a href="<?php echo site_url('Aktivitas_user'); ?>" class="btn btn-secondary btn-sm">
                        <i class="fas fa-arrow-left"></i> Kembali
                    </a>
                </div>
            </div>
            <div class="card-body">
                <div class="row mb-3">
                    <div class="col-md-6">
                        <table class="table table-sm table-bordered">
                            <tr>
                                <th width="35%">Nama User</th>
                                <td>
                                    <strong><?php echo htmlspecialchars($login_log->nama_user ?? $login_log->username ?? '-'); ?></strong>
                                    <?php if (!empty($login_log->username) && ($login_log->nama_user ?? '') !== $login_log->username): ?>
                                        <br><small class="text-muted">@<?php echo htmlspecialchars($login_log->username); ?></small>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <tr>
                                <th>Role</th>
                                <td><?php echo htmlspecialchars(ucfirst($login_log->nama_role ?? '-')); ?></td>
                            </tr>
                            <tr>
                                <th>Login</th>
                                <td><?php echo date('d/m/Y H:i:s', strtotime($login_log->created_at)); ?></td>
                            </tr>
                            <tr>
                                <th>IP Address</th>
                                <?php $ip = trim((string)($login_log->ip_address ?? '')); ?>
                                <td><?php echo $ip !== '' ? htmlspecialchars($ip) : '-'; ?></td>
                            </tr>
                        </table>
                    </div>
                    <div class="col-md-6">
                        <div class="alert alert-info mb-0">
                            <i class="fas fa-info-circle"></i>
                            Halaman ini menampilkan daftar aktivitas user selama sesi login yang dipilih, seperti melihat halaman, membuka menu, atau menjalankan aksi form.
                        </div>
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="table table-bordered table-striped table-hover">
                        <thead>
                            <tr>
                                <th width="5%">No</th>
                                <th width="14%">Waktu</th>
                                <th width="12%">Metode</th>
                                <th width="18%">Controller</th>
                                <th width="18%">Method</th>
                                <th>Aktivitas</th>
                                <th width="20%">URL</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($activity_detail_list)): ?>
                                <tr>
                                    <td colspan="7" class="text-center text-muted">
                                        <i class="fas fa-inbox"></i> Belum ada detail aktivitas pada sesi ini.
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php $no = 1; foreach ($activity_detail_list as $detail): ?>
                                    <tr>
                                        <td><?php echo $no++; ?></td>
                                        <td><?php echo date('d/m/Y H:i:s', strtotime($detail->created_at)); ?></td>
                                        <td>
                                            <span class="badge badge-<?php echo strtoupper($detail->request_method) === 'POST' ? 'warning' : 'info'; ?>">
                                                <?php echo htmlspecialchars(strtoupper($detail->request_method ?: '-')); ?>
                                            </span>
                                        </td>
                                        <td><?php echo htmlspecialchars($detail->controller ?: '-'); ?></td>
                                        <td><?php echo htmlspecialchars($detail->method ?: '-'); ?></td>
                                        <td><?php echo htmlspecialchars($detail->activity_label ?: '-'); ?></td>
                                        <td><small class="text-muted"><?php echo htmlspecialchars($detail->url ?: '-'); ?></small></td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
