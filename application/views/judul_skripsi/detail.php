<div class="row">
    <div class="col-12">
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

        <div class="card card-primary">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-edit"></i> Detail & Setting Judul Skripsi
                </h3>
                <div class="card-tools">
                    <a href="<?php echo site_url('Judul_skripsi'); ?>" class="btn btn-secondary btn-sm">
                        <i class="fas fa-arrow-left"></i> Kembali
                    </a>
                </div>
            </div>
            <?php echo form_open('Judul_skripsi/detail/' . $pengajuan->id, array('method' => 'post')); ?>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <h5>Data Mahasiswa</h5>
                        <table class="table table-bordered">
                            <tr>
                                <th width="40%">NIM</th>
                                <td><strong><?php echo htmlspecialchars($pengajuan->nim); ?></strong></td>
                            </tr>
                            <tr>
                                <th>Nama Mahasiswa</th>
                                <td><?php echo htmlspecialchars($pengajuan->nama_mahasiswa); ?></td>
                            </tr>
                            <tr>
                                <th>Kelas</th>
                                <td><?php echo htmlspecialchars($pengajuan->kelas); ?></td>
                            </tr>
                        </table>
                    </div>
                    <div class="col-md-6">
                        <h5>Status Pengajuan</h5>
                        <table class="table table-bordered">
                            <tr>
                                <th width="40%">Status</th>
                                <td>
                                    <?php
                                    $status_class = 'secondary';
                                    switch ($pengajuan->status) {
                                        case 'pending':
                                            $status_class = 'warning';
                                            break;
                                        case 'disetujui':
                                            $status_class = 'success';
                                            break;
                                        case 'ditolak':
                                            $status_class = 'danger';
                                            break;
                                    }
                                    ?>
                                    <span class="badge badge-<?php echo $status_class; ?>">
                                        <?php echo ucfirst($pengajuan->status); ?>
                                    </span>
                                </td>
                            </tr>
                            <tr>
                                <th>Tanggal Pengajuan</th>
                                <td><?php echo date('d/m/Y H:i', strtotime($pengajuan->created_at)); ?></td>
                            </tr>
                        </table>
                    </div>
                </div>

                <hr>

                <h5>Judul dan Deskripsi</h5>
                <div class="form-group">
                    <label>Judul yang Diajukan</label>
                    <p class="form-control-plaintext border p-2 bg-light">
                        <?php echo htmlspecialchars($pengajuan->judul_pengajuan); ?>
                    </p>
                </div>

                <?php if ($pengajuan->deskripsi): ?>
                    <div class="form-group">
                        <label>Deskripsi</label>
                        <p class="form-control-plaintext border p-2 bg-light">
                            <?php echo nl2br(htmlspecialchars($pengajuan->deskripsi)); ?>
                        </p>
                    </div>
                <?php endif; ?>

                <?php if ($pengajuan->alasan): ?>
                    <div class="form-group">
                        <label>Alasan Memilih Judul</label>
                        <p class="form-control-plaintext border p-2 bg-light">
                            <?php echo nl2br(htmlspecialchars($pengajuan->alasan)); ?>
                        </p>
                    </div>
                <?php endif; ?>

                <?php if ($pengajuan->file_proposal): ?>
                    <div class="form-group">
                        <label>File Proposal</label>
                        <p>
                            <a href="<?php echo base_url($pengajuan->file_proposal); ?>" target="_blank" class="btn btn-sm btn-info">
                                <i class="fas fa-download"></i> Download Proposal
                            </a>
                        </p>
                    </div>
                <?php endif; ?>

                <hr>

                <h5>Review & Setting Pembimbing</h5>
                
                <div class="form-group">
                    <label for="status">Status Pengajuan <span class="text-danger">*</span></label>
                    <select name="status" id="status" class="form-control" required>
                        <option value="pending" <?php echo ($pengajuan->status == 'pending') ? 'selected' : ''; ?>>Pending</option>
                        <option value="disetujui" <?php echo ($pengajuan->status == 'disetujui') ? 'selected' : ''; ?>>Disetujui</option>
                        <option value="ditolak" <?php echo ($pengajuan->status == 'ditolak') ? 'selected' : ''; ?>>Ditolak</option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="judul_disetujui">Judul yang Disetujui</label>
                    <input type="text" name="judul_disetujui" id="judul_disetujui" class="form-control" 
                           value="<?php echo htmlspecialchars($pengajuan->judul_disetujui ?: $pengajuan->judul_pengajuan); ?>" 
                           placeholder="Kosongkan jika menggunakan judul yang diajukan">
                    <small class="form-text text-muted">Jika kosong, akan menggunakan judul yang diajukan mahasiswa.</small>
                </div>

                <div class="form-group">
                    <label for="id_pembimbing_1_final">Pembimbing 1</label>
                    <select name="id_pembimbing_1_final" id="id_pembimbing_1_final" class="form-control">
                        <option value="">-- Pilih Pembimbing 1 --</option>
                        <?php foreach ($dosen_pembimbing_1 as $dosen): ?>
                            <option value="<?php echo $dosen->id; ?>" 
                                    <?php echo ($pengajuan->id_pembimbing_1_final == $dosen->id || (!$pengajuan->id_pembimbing_1_final && $pengajuan->id_pembimbing_1_request == $dosen->id)) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($dosen->nama_dosen . ' (' . $dosen->nidn . ')'); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <small class="form-text text-muted">Jika kosong, akan menggunakan pembimbing yang diminta mahasiswa (jika ada).</small>
                </div>

                <div class="form-group">
                    <label for="id_pembimbing_2_final">Pembimbing 2</label>
                    <select name="id_pembimbing_2_final" id="id_pembimbing_2_final" class="form-control">
                        <option value="">-- Pilih Pembimbing 2 --</option>
                        <?php foreach ($dosen_pembimbing_2 as $dosen): ?>
                            <option value="<?php echo $dosen->id; ?>" 
                                    <?php echo ($pengajuan->id_pembimbing_2_final == $dosen->id || (!$pengajuan->id_pembimbing_2_final && $pengajuan->id_pembimbing_2_request == $dosen->id)) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($dosen->nama_dosen . ' (' . $dosen->nidn . ')'); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <small class="form-text text-muted">Jika kosong, akan menggunakan pembimbing yang diminta mahasiswa (jika ada).</small>
                </div>

                <div class="form-group">
                    <label for="catatan_sekprodi">Catatan</label>
                    <textarea name="catatan_sekprodi" id="catatan_sekprodi" class="form-control" rows="3"><?php echo htmlspecialchars($pengajuan->catatan_sekprodi); ?></textarea>
                    <small class="form-text text-muted">Catatan untuk mahasiswa (opsional).</small>
                </div>

                <?php if ($pengajuan->status == 'disetujui'): ?>
                    <div class="alert alert-info">
                        <h6><i class="fas fa-info-circle"></i> Informasi Pembimbing yang Sudah Ditetapkan</h6>
                        <div class="row">
                            <div class="col-md-6">
                                <strong>Pembimbing 1:</strong> 
                                <?php echo $pengajuan->pembimbing_1_final_nama ? htmlspecialchars($pengajuan->pembimbing_1_final_nama . ' (' . $pengajuan->pembimbing_1_final_nidn . ')') : '-'; ?>
                            </div>
                            <div class="col-md-6">
                                <strong>Pembimbing 2:</strong> 
                                <?php echo $pengajuan->pembimbing_2_final_nama ? htmlspecialchars($pengajuan->pembimbing_2_final_nama . ' (' . $pengajuan->pembimbing_2_final_nidn . ')') : '-'; ?>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
            <div class="card-footer">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i> Simpan Perubahan
                </button>
                <a href="<?php echo site_url('Judul_skripsi'); ?>" class="btn btn-secondary">
                    <i class="fas fa-times"></i> Batal
                </a>
            </div>
            <?php echo form_close(); ?>
        </div>
    </div>
</div>

