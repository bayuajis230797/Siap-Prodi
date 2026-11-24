<?php $this->load->helper(array('pkm', 'id_encode')); ?>
<div class="row">
    <div class="col-12">
        <?php if ($this->session->flashdata('success')): ?>
            <div class="alert alert-success alert-dismissible fade show"><?php echo $this->session->flashdata('success'); ?><button type="button" class="close" data-dismiss="alert"><span>&times;</span></button></div>
        <?php endif; ?>
        <?php if ($this->session->flashdata('error')): ?>
            <div class="alert alert-danger alert-dismissible fade show"><?php echo $this->session->flashdata('error'); ?><button type="button" class="close" data-dismiss="alert"><span>&times;</span></button></div>
        <?php endif; ?>

        <div class="card">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-comments"></i> Bimbingan PKM</h3>
                <div class="card-tools">
                    <?php if (!empty($is_ketua)): ?>
                        <?php if (empty($has_pending_topic)): ?>
                            <a href="<?php echo site_url('Bimbingan_pkm_mahasiswa/bimbingan_create'); ?>" class="btn btn-primary btn-sm"><i class="fas fa-plus"></i> Buat Topik Baru</a>
                        <?php else: ?>
                            <span class="badge badge-warning mr-2">Selesaikan topik pending dulu</span>
                        <?php endif; ?>
                    <?php else: ?>
                        <span class="badge badge-info"><i class="fas fa-eye"></i> Mode Pantau (Anggota)</span>
                    <?php endif; ?>
                </div>
            </div>
            <div class="card-body">
                <?php if (empty($is_ketua)): ?>
                    <div class="alert alert-info mb-3">
                        <i class="fas fa-info-circle"></i> Anda terdaftar sebagai <strong>anggota tim</strong>.
                        Halaman ini hanya untuk memantau proses bimbingan tim. Hanya ketua tim yang dapat menambah topik dan mengirim revisi.
                    </div>
                <?php endif; ?>
                <div class="mb-3">
                    <div class="btn-group" role="group">
                        <a href="<?php echo site_url('Bimbingan_pkm_mahasiswa/bimbingan'); ?>" class="btn btn-sm <?php echo empty($status_filter) ? 'btn-primary' : 'btn-secondary'; ?>">Semua</a>
                        <a href="<?php echo site_url('Bimbingan_pkm_mahasiswa/bimbingan?status=open'); ?>" class="btn btn-sm <?php echo $status_filter === 'open' ? 'btn-primary' : 'btn-secondary'; ?>">Open</a>
                        <a href="<?php echo site_url('Bimbingan_pkm_mahasiswa/bimbingan?status=closed'); ?>" class="btn btn-sm <?php echo $status_filter === 'closed' ? 'btn-primary' : 'btn-secondary'; ?>">Closed</a>
                    </div>
                </div>

                <?php if (empty($bimbingan_list)): ?>
                    <div class="alert alert-info">
                        Belum ada topik bimbingan.
                        <?php if (!empty($is_ketua) && empty($has_pending_topic)): ?>
                            <a href="<?php echo site_url('Bimbingan_pkm_mahasiswa/bimbingan_create'); ?>" class="alert-link">Buat topik bimbingan baru</a>
                        <?php endif; ?>
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped table-hover">
                            <thead>
                                <tr>
                                    <th width="5%">No</th>
                                    <th>Judul Topik</th>
                                    <th width="10%" class="text-center">Status</th>
                                    <th width="18%" class="text-center">Status Proses</th>
                                    <th width="12%" class="text-center">Last Activity</th>
                                    <th width="14%" class="text-center">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php $no = 1; foreach ($bimbingan_list as $b): ?>
                                    <tr>
                                        <td><?php echo $no++; ?></td>
                                        <td><strong><?php echo htmlspecialchars($b->judul_topik); ?></strong></td>
                                        <td class="text-center"><span class="badge badge-<?php echo ($b->status ?? '') === 'open' ? 'success' : 'secondary'; ?>"><?php echo ucfirst($b->status); ?></span></td>
                                        <td class="text-center"><span class="badge badge-<?php echo htmlspecialchars($b->workflow_status_class ?? 'secondary'); ?>"><?php echo htmlspecialchars($b->workflow_status_label ?? '-'); ?></span></td>
                                        <td class="text-center"><?php echo date('d/m/Y H:i', strtotime($b->last_activity)); ?></td>
                                        <td class="text-center">
                                            <a href="<?php echo site_url('Bimbingan_pkm_mahasiswa/bimbingan_detail/' . encode_id($b->id)); ?>" class="btn btn-info btn-sm"><i class="fas fa-eye"></i> Detail</a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <?php if (pkm_proposal_final_aktif($pengajuan)): ?>
        <div class="card card-success">
            <div class="card-header"><h3 class="card-title"><i class="fas fa-file-alt"></i> Proposal Final PKM</h3></div>
            <div class="card-body">
                <?php if (!empty($pengajuan->tanggal_selesai)): ?>
                    <p class="mb-2">
                        <small class="text-muted">Batas submit: <strong><?php echo date('d/m/Y', strtotime($pengajuan->tanggal_selesai)); ?></strong></small>
                        <?php if (empty($proposal_final_submit_allowed)): ?>
                            <span class="badge badge-danger ml-1">Waktu habis</span>
                        <?php else: ?>
                            <div class="pkm-countdown-proposal p-2 rounded mt-2" data-deadline="<?php echo htmlspecialchars(pkm_proposal_final_deadline_iso($pengajuan->tanggal_selesai)); ?>" style="background:rgba(40,167,69,.12);border:1px dashed #28a745;display:inline-block;">
                                <small class="text-success d-block mb-1"><i class="fas fa-hourglass-half"></i> Sisa waktu submit Proposal Final PKM</small>
                                <span class="countdown-text font-weight-bold text-success">—</span>
                            </div>
                        <?php endif; ?>
                    </p>
                <?php endif; ?>
                <p>Status: <span class="badge badge-<?php echo pkm_status_acc_badge($pengajuan->status_acc_proposal); ?>"><?php echo pkm_status_verifikasi_proposal_final_label($pengajuan->status_acc_proposal); ?></span></p>
                <?php if ($pengajuan->link_proposal_final): ?>
                    <p><?php echo pkm_google_drive_link_html($pengajuan->link_proposal_final, 'Proposal Final Terkirim', 'proposal_final', $pengajuan->id); ?></p>
                <?php endif; ?>
                <?php if ($pengajuan->catatan_verifikasi_proposal_final): ?>
                    <div class="alert alert-info"><strong>Catatan Sekprodi:</strong> <?php echo nl2br(htmlspecialchars($pengajuan->catatan_verifikasi_proposal_final)); ?></div>
                <?php endif; ?>
                <?php if (!empty($is_ketua) && $pengajuan->status_acc_proposal !== 'acc' && !empty($proposal_final_submit_allowed)): ?>
                    <?php echo form_open_multipart('Bimbingan_pkm_mahasiswa/kirim_proposal_final'); ?>
                        <div class="form-group">
                            <label>Proposal Final <span class="text-danger">*</span></label>
                            <input type="file" name="file_proposal_final" class="form-control-file" accept=".pdf,.doc,.docx,.zip,.rar" required>
                            <small class="text-muted">Diunggah ke Google Drive institusi, lalu dikirim ke sekprodi untuk verifikasi.</small>
                        </div>
                        <button type="submit" class="btn btn-success"><i class="fas fa-paper-plane"></i> Kirim ke Sekprodi</button>
                    <?php echo form_close(); ?>
                <?php elseif (!empty($is_ketua) && empty($proposal_final_submit_allowed)): ?>
                    <div class="alert alert-danger mb-0"><i class="fas fa-clock"></i> Batas waktu submit Proposal Final PKM telah berakhir.</div>
                <?php elseif (empty($is_ketua)): ?>
                    <p class="text-muted mb-0"><small>Hanya ketua tim yang dapat mengirim proposal final.</small></p>
                <?php endif; ?>
            </div>
        </div>
        <?php endif; ?>
    </div>
</div>
<script>
(function() {
    function pad(n) { return n < 10 ? '0' + n : n; }
    function updateCountdowns() {
        document.querySelectorAll('.pkm-countdown-proposal').forEach(function(box) {
            var el = box.querySelector('.countdown-text');
            if (!el) return;
            var deadline = new Date(box.getAttribute('data-deadline')).getTime();
            var diff = deadline - Date.now();
            if (isNaN(deadline) || diff <= 0) {
                el.innerHTML = '<span class="text-danger">Deadline telah lewat</span>';
                box.style.background = 'rgba(220,53,69,.08)';
                box.style.borderColor = '#dc3545';
                return;
            }
            var days = Math.floor(diff / 86400000);
            var hours = Math.floor((diff % 86400000) / 3600000);
            var mins = Math.floor((diff % 3600000) / 60000);
            var secs = Math.floor((diff % 60000) / 1000);
            el.innerHTML = days + ' <small>hari</small> ' + pad(hours) + ' <small>jam</small> ' + pad(mins) + ' <small>menit</small> ' + pad(secs) + ' <small>detik</small>';
        });
    }
    updateCountdowns();
    setInterval(updateCountdowns, 1000);
})();
</script>
