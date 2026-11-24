<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-comments"></i> Bimbingan Skripsi
                </h3>
            </div>
            <div class="card-body">
                <?php if ($this->session->flashdata('success')): ?>
                    <div class="alert alert-success alert-dismissible">
                        <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
                        <?php echo $this->session->flashdata('success'); ?>
                    </div>
                <?php endif; ?>

                <?php if ($this->session->flashdata('error')): ?>
                    <div class="alert alert-danger alert-dismissible">
                        <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
                        <?php echo $this->session->flashdata('error'); ?>
                    </div>
                <?php endif; ?>

                <?php if ($tahun_aktif): ?>
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle"></i>
                        <strong>Tahun Akademik:</strong> <?php echo $tahun_aktif->tahun_akademik . ' ' . ucfirst($tahun_aktif->semester); ?>
                    </div>
                <?php endif; ?>

                <!-- Form Mulai Bimbingan -->
                <div class="card card-primary card-outline mb-4">
                    <div class="card-header">
                        <h3 class="card-title">
                            <i class="fas fa-plus-circle"></i> Mulai Bimbingan Baru
                        </h3>
                    </div>
                    <div class="card-body">
                        <?php echo form_open('Bimbingan_skripsi_mahasiswa/create_topik', array('method' => 'post')); ?>
                        <div class="form-group">
                            <label for="id_pembimbing">Kepada <span class="text-danger">*</span></label>
                            <select name="id_pembimbing" id="id_pembimbing" class="form-control" required>
                                <option value="">-- Pilih Pembimbing --</option>
                                <?php foreach ($pembimbing_list as $pembimbing): ?>
                                    <option value="<?php echo $pembimbing['id']; ?>" 
                                            <?php echo $pembimbing['has_pending'] ? 'disabled' : ''; ?>>
                                        <?php echo htmlspecialchars($pembimbing['nama']); ?> 
                                        (Pembimbing <?php echo $pembimbing['tipe']; ?>)
                                        <?php if ($pembimbing['has_pending']): ?>
                                            - Masih ada topik yang belum di-ACC
                                        <?php endif; ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <small class="form-text text-muted">
                                Pilih pembimbing yang akan direview topik bimbingannya.
                            </small>
                        </div>

                        <div class="form-group">
                            <label for="judul_topik">Judul/Topik Bimbingan <span class="text-danger">*</span></label>
                            <select name="judul_topik" id="judul_topik" class="form-control" required>
                                <option value="">-- Pilih topik bimbingan --</option>
                                <?php foreach (skripsi_topik_bimbingan_pilihan() as $topik_opt): ?>
                                    <option value="<?php echo htmlspecialchars($topik_opt); ?>"><?php echo htmlspecialchars($topik_opt); ?></option>
                                <?php endforeach; ?>
                            </select>
                            <small class="form-text text-muted">
                                Pilih salah satu tahap laporan yang akan dibimbing.
                            </small>
                        </div>

                        <div class="form-group">
                            <label for="pesan_pembuka">Pesan Pembuka <span class="text-danger">*</span></label>
                            <textarea name="pesan_pembuka" id="pesan_pembuka" class="form-control" rows="4" 
                                      placeholder="Tuliskan permasalahan atau pertanyaan bimbingan" required></textarea>
                            <small class="form-text text-muted">
                                Jelaskan permasalahan atau pertanyaan yang ingin didiskusikan dengan pembimbing.
                            </small>
                        </div>

                        <div class="form-group">
                            <button type="submit" class="btn btn-success">
                                <i class="fas fa-comments"></i> Mulai Bimbingan
                            </button>
                        </div>
                        <?php echo form_close(); ?>
                    </div>
                </div>

                <!-- Daftar Bimbingan -->
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">
                            <i class="fas fa-list"></i> Daftar Bimbingan
                        </h3>
                        <div class="card-tools">
                            <div class="btn-group btn-group-sm">
                                <a href="<?php echo site_url('Bimbingan_skripsi_mahasiswa'); ?>" 
                                   class="btn <?php echo empty($status_filter) ? 'btn-primary' : 'btn-secondary'; ?>">Semua</a>
                                <a href="<?php echo site_url('Bimbingan_skripsi_mahasiswa?status=open'); ?>" 
                                   class="btn <?php echo (isset($status_filter) && $status_filter === 'open') ? 'btn-primary' : 'btn-secondary'; ?>">Open</a>
                                <a href="<?php echo site_url('Bimbingan_skripsi_mahasiswa?status=closed'); ?>" 
                                   class="btn <?php echo (isset($status_filter) && $status_filter === 'closed') ? 'btn-primary' : 'btn-secondary'; ?>">Closed</a>
                            </div>
                        </div>
                    </div>
                    <div class="card-body">
                        <?php if (empty($bimbingan_list)): ?>
                            <div class="alert alert-info">
                                <i class="fas fa-info-circle"></i>
                                Belum ada bimbingan.
                            </div>
                        <?php else: ?>
                            <div class="table-responsive">
                                <table class="table table-bordered table-striped">
                                    <thead>
                                        <tr>
                                            <th width="5%">#</th>
                                            <th>Judul/Topik Bimbingan</th>
                                            <th>Pembimbing</th>
                                            <th width="9%" class="text-center">Status</th>
                                            <th width="16%" class="text-center">Status Proses</th>
                                            <th width="12%" class="text-center">Aktivitas Terakhir</th>
                                            <th width="10%" class="text-center">Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php 
                                        $no = 1;
                                        foreach ($bimbingan_list as $bimbingan): 
                                        ?>
                                            <tr>
                                                <td><?php echo $no++; ?></td>
                                                <td>
                                                    <strong><?php echo htmlspecialchars($bimbingan->judul_topik); ?></strong>
                                                    <?php if (isset($bimbingan->status_acc) && $bimbingan->status_acc): ?>
                                                        <br><small class="badge badge-success">Topik sudah di-ACC</small>
                                                    <?php else: ?>
                                                        <br><small class="badge badge-warning">Menunggu ACC pembimbing</small>
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <?php echo htmlspecialchars($bimbingan->nama_dosen); ?><br>
                                                    <small class="text-muted">Pembimbing <?php echo $bimbingan->tipe_pembimbing; ?></small>
                                                </td>
                                                <td class="text-center">
                                                    <span class="badge badge-<?php echo ($bimbingan->status == 'open') ? 'success' : 'secondary'; ?>">
                                                        <?php echo ucfirst($bimbingan->status); ?>
                                                    </span>
                                                </td>
                                                <td class="text-center">
                                                    <span class="badge badge-<?php echo htmlspecialchars($bimbingan->workflow_status_class ?? 'secondary'); ?>">
                                                        <?php echo htmlspecialchars($bimbingan->workflow_status_label ?? '-'); ?>
                                                    </span>
                                                </td>
                                                <td class="text-center">
                                                    <?php echo $bimbingan->last_activity ? date('d/m/Y H:i', strtotime($bimbingan->last_activity)) : '-'; ?>
                                                </td>
                                                <td class="text-center">
                                                    <a href="<?php echo site_url('Bimbingan_skripsi_mahasiswa/detail/' . encode_id($bimbingan->id)); ?>" 
                                                       class="btn btn-sm btn-primary" title="Lihat Detail">
                                                        <i class="fas fa-eye"></i> Detail
                                                    </a>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
