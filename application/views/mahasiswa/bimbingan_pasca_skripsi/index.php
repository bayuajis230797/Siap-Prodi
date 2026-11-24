<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header"><h3 class="card-title">Bimbingan Pasca Sidang Skripsi</h3></div>
            <div class="card-body">
                <?php $this->load->helper('id_encode'); ?>
                <?php if ($this->session->flashdata('success')): ?><div class="alert alert-success"><?php echo $this->session->flashdata('success'); ?></div><?php endif; ?>
                <?php if ($this->session->flashdata('error')): ?><div class="alert alert-danger"><?php echo $this->session->flashdata('error'); ?></div><?php endif; ?>
                <p class="text-muted small">Kirim revisi pasca sidang ke penguji (ketua &amp; anggota). Klaim nilai aktif setelah semua penguji menyatakan layak.</p>
                <div class="table-responsive">
                    <table class="table table-bordered table-striped table-sm">
                        <thead><tr><th>No</th><th>Topik</th><th>Status</th><th>Aktivitas</th><th></th></tr></thead>
                        <tbody>
                            <?php if (empty($list)): ?>
                                <tr><td colspan="5" class="text-center text-muted">Belum ada bimbingan pasca sidang. Pastikan jadwal sidang sudah ditetapkan.</td></tr>
                            <?php else: $no=1; foreach ($list as $row): ?>
                                <tr>
                                    <td><?php echo $no++; ?></td>
                                    <td><?php echo htmlspecialchars($row->judul_topik); ?></td>
                                    <td><span class="badge badge-<?php echo $row->status==='open'?'success':'secondary'; ?>"><?php echo ucfirst($row->status); ?></span></td>
                                    <td><?php echo $row->last_activity ? date('d/m/Y H:i', strtotime($row->last_activity)) : '-'; ?></td>
                                    <td><a class="btn btn-sm btn-primary" href="<?php echo site_url('Bimbingan_pasca_skripsi_mahasiswa/detail/' . encode_id($row->id)); ?>">Buka</a></td>
                                </tr>
                            <?php endforeach; endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
