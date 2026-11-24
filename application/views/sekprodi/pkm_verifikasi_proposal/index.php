<?php $this->load->helper('pkm'); ?>
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header"><h3 class="card-title">Verifikasi Proposal Final PKM</h3></div>
            <div class="card-body">
                <?php if ($this->session->flashdata('success')): ?><div class="alert alert-success"><?php echo $this->session->flashdata('success'); ?></div><?php endif; ?>
                <?php if ($this->session->flashdata('error')): ?><div class="alert alert-danger"><?php echo $this->session->flashdata('error'); ?></div><?php endif; ?>

                <form method="get" class="form-inline mb-3">
                    <label class="mr-2">Status:</label>
                    <select name="status" class="form-control mr-2">
                        <option value="pending" <?php echo $status_filter === 'pending' ? 'selected' : ''; ?>>Menunggu Verifikasi</option>
                        <option value="acc" <?php echo $status_filter === 'acc' ? 'selected' : ''; ?>>Disetujui</option>
                        <option value="revisi" <?php echo $status_filter === 'revisi' ? 'selected' : ''; ?>>Perlu Perbaikan</option>
                        <option value="all" <?php echo $status_filter === 'all' ? 'selected' : ''; ?>>Semua</option>
                    </select>
                    <button type="submit" class="btn btn-info btn-sm mr-3">Filter</button>
                    <a href="<?php echo site_url('Pkm_verifikasi_proposal/export_mahasiswa_pkm'); ?>" class="btn btn-success btn-sm mr-1"><i class="fas fa-file-csv"></i> Export Mahasiswa PKM</a>
                    <a href="<?php echo site_url('Pkm_verifikasi_proposal/export_mahasiswa_non_pkm'); ?>" class="btn btn-outline-secondary btn-sm"><i class="fas fa-file-csv"></i> Export Mahasiswa Non PKM</a>
                </form>
                <p class="text-muted small mb-3">Export berdasarkan periode PKM aktif. <strong>Mahasiswa PKM</strong> = ketua/anggota tim yang sudah submit Proposal Final. <strong>Non PKM</strong> = ketua/anggota terdaftar yang belum submit Proposal Final.</p>

                <div class="table-responsive">
                    <table class="table table-bordered table-striped table-sm">
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>Ketua</th>
                                <th>Jenis PKM</th>
                                <th>Judul</th>
                                <th>Status</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($pengajuan_list)): ?>
                                <tr><td colspan="6" class="text-center text-muted">Tidak ada proposal final.</td></tr>
                            <?php else: $no = 1; foreach ($pengajuan_list as $row): ?>
                                <tr>
                                    <td><?php echo $no++; ?></td>
                                    <td><code><?php echo htmlspecialchars($row->ketua_nim); ?></code><br><?php echo htmlspecialchars($row->ketua_nama); ?></td>
                                    <td><?php echo htmlspecialchars($row->nama_jenis); ?></td>
                                    <td><?php echo htmlspecialchars($row->judul_disetujui ?: $row->judul_pkm); ?></td>
                                    <td><span class="badge badge-<?php echo pkm_status_acc_badge($row->status_acc_proposal); ?>"><?php echo pkm_status_verifikasi_proposal_final_label($row->status_acc_proposal); ?></span></td>
                                    <td><a href="<?php echo site_url('Pkm_verifikasi_proposal/detail/' . $row->id); ?>" class="btn btn-sm btn-primary">Detail</a></td>
                                </tr>
                            <?php endforeach; endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
