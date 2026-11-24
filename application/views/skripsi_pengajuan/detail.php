<div class="row">
    <div class="col-12">
        <div class="card card-primary">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-eye"></i> Detail Pengajuan Judul Skripsi
                </h3>
                <div class="card-tools">
                    <a href="<?php echo site_url('Skripsi_pengajuan/review'); ?>" class="btn btn-secondary btn-sm">
                        <i class="fas fa-arrow-left"></i> Kembali
                    </a>
                </div>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <h5>Data Mahasiswa</h5>
                        <table class="table table-bordered">
                            <tr>
                                <th width="40%">NIM</th>
                                <td><?php echo htmlspecialchars($pengajuan->nim); ?></td>
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
                    <label>Judul Pengajuan</label>
                    <p class="form-control-plaintext">
                        <?php 
                        // Tampilkan judul_disetujui jika sudah ada (judul terbaru), jika belum tampilkan judul_pengajuan
                        $judul_tampil = !empty($pengajuan->judul_disetujui) ? $pengajuan->judul_disetujui : $pengajuan->judul_pengajuan;
                        echo htmlspecialchars($judul_tampil); 
                        ?>
                        <?php if (!empty($pengajuan->judul_disetujui) && $pengajuan->judul_disetujui != $pengajuan->judul_pengajuan): ?>
                            <br><small class="text-muted">(Judul awal: <?php echo htmlspecialchars($pengajuan->judul_pengajuan); ?>)</small>
                        <?php endif; ?>
                    </p>
                </div>

                <?php if ($pengajuan->deskripsi): ?>
                    <div class="form-group">
                        <label>Deskripsi</label>
                        <p class="form-control-plaintext"><?php echo nl2br(htmlspecialchars($pengajuan->deskripsi)); ?></p>
                    </div>
                <?php endif; ?>

                <?php if ($pengajuan->alasan): ?>
                    <div class="form-group">
                        <label>Alasan Memilih Judul</label>
                        <p class="form-control-plaintext"><?php echo nl2br(htmlspecialchars($pengajuan->alasan)); ?></p>
                    </div>
                <?php endif; ?>

                <?php if ($pengajuan->file_proposal): ?>
                    <div class="form-group">
                        <label>Link Google Drive Proposal</label>
                        <p>
                            <?php 
                            $this->load->helper('google_drive');
                            echo google_drive_link_html($pengajuan->file_proposal, 'Buka Proposal');
                            ?>
                        </p>
                    </div>
                <?php endif; ?>

                <hr>

                <h5>Pembimbing yang Diminta</h5>
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Pembimbing 1</label>
                            <p class="form-control-plaintext">
                                <?php echo $pengajuan->pembimbing_1_request_nama ? htmlspecialchars($pengajuan->pembimbing_1_request_nama . ' (' . $pengajuan->pembimbing_1_request_nidn . ')') : '<span class="text-muted">Tidak ada permintaan</span>'; ?>
                            </p>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Pembimbing 2</label>
                            <p class="form-control-plaintext">
                                <?php echo $pengajuan->pembimbing_2_request_nama ? htmlspecialchars($pengajuan->pembimbing_2_request_nama . ' (' . $pengajuan->pembimbing_2_request_nidn . ')') : '<span class="text-muted">Tidak ada permintaan</span>'; ?>
                            </p>
                        </div>
                    </div>
                </div>

                <?php if ($pengajuan->status == 'pending'): ?>
                    <hr>
                    <h5>Review Pengajuan</h5>
                    
                    <!-- Form Approve -->
                    <?php echo form_open('Skripsi_pengajuan/approve/' . $pengajuan->id, array('method' => 'post')); ?>
                    <div class="card card-success">
                        <div class="card-header">
                            <h3 class="card-title">Setujui Pengajuan</h3>
                        </div>
                        <div class="card-body">
                            <div class="form-group">
                                <label for="judul_disetujui">Judul yang Disetujui <span class="text-danger">*</span></label>
                                <input type="text" name="judul_disetujui" id="judul_disetujui" class="form-control" 
                                       value="<?php echo htmlspecialchars($pengajuan->judul_pengajuan); ?>" required>
                            </div>

                            <div class="form-group">
                                <label for="id_pembimbing_1_final">Pembimbing 1 <span class="text-danger">*</span></label>
                                <select name="id_pembimbing_1_final" id="id_pembimbing_1_final" class="form-control" required>
                                    <option value="">-- Pilih Pembimbing 1 --</option>
                                    <?php foreach ($pembimbing_1_list as $dosen): ?>
                                        <option value="<?php echo $dosen->id; ?>" 
                                                <?php echo ($pengajuan->id_pembimbing_1_request == $dosen->id) ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($dosen->nama_dosen . ' (' . $dosen->nidn . ')'); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <div class="form-group">
                                <label for="id_pembimbing_2_final">Pembimbing 2 <small class="text-muted">(opsional)</small></label>
                                <select name="id_pembimbing_2_final" id="id_pembimbing_2_final" class="form-control">
                                    <option value="">-- Tanpa Pembimbing 2 --</option>
                                    <?php foreach ($pembimbing_2_list as $dosen): ?>
                                        <option value="<?php echo $dosen->id; ?>" 
                                                <?php echo ($pengajuan->id_pembimbing_2_request == $dosen->id) ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($dosen->nama_dosen . ' (' . $dosen->nidn . ')'); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <div class="form-group">
                                <label for="catatan_sekprodi_approve">Catatan</label>
                                <textarea name="catatan_sekprodi" id="catatan_sekprodi_approve" class="form-control" rows="3"></textarea>
                            </div>

                            <button type="submit" class="btn btn-success">
                                <i class="fas fa-check"></i> Setujui Pengajuan
                            </button>
                        </div>
                    </div>
                    <?php echo form_close(); ?>

                    <!-- Form Reject -->
                    <?php echo form_open('Skripsi_pengajuan/reject/' . $pengajuan->id, array('method' => 'post')); ?>
                    <div class="card card-danger">
                        <div class="card-header">
                            <h3 class="card-title">Tolak Pengajuan</h3>
                        </div>
                        <div class="card-body">
                            <div class="form-group">
                                <label for="catatan_sekprodi_reject">Alasan Penolakan <span class="text-danger">*</span></label>
                                <textarea name="catatan_sekprodi" id="catatan_sekprodi_reject" class="form-control" rows="3" required></textarea>
                            </div>

                            <button type="submit" class="btn btn-danger">
                                <i class="fas fa-times"></i> Tolak Pengajuan
                            </button>
                        </div>
                    </div>
                    <?php echo form_close(); ?>
                <?php else: ?>
                    <?php if ($pengajuan->judul_disetujui): ?>
                        <hr id="edit-judul">
                        <h5>Judul yang Disetujui</h5>
                        <div class="card card-outline card-info">
                            <div class="card-body">
                                <!-- Display Mode -->
                                <div id="displayJudul" class="d-flex justify-content-between align-items-center">
                                    <p class="form-control-plaintext mb-0"><strong><?php echo htmlspecialchars($pengajuan->judul_disetujui); ?></strong></p>
                                    <a href="javascript:void(0);" id="linkEditJudul" title="Edit Judul" style="color: #ffc107; font-size: 18px;">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                </div>
                                
                                <!-- Edit Mode -->
                                <div id="editJudul" style="display: none;">
                                    <?php echo form_open('Skripsi_pengajuan/edit_judul/' . $pengajuan->id, array('method' => 'post', 'id' => 'formEditJudul')); ?>
                                    <div class="form-group">
                                        <label for="judul_disetujui_edit">Judul Baru <span class="text-danger">*</span></label>
                                        <input type="text" name="judul_disetujui" id="judul_disetujui_edit" class="form-control" 
                                               value="<?php echo htmlspecialchars($pengajuan->judul_disetujui); ?>" required minlength="10">
                                        <small class="form-text text-muted">Judul ini akan menggantikan judul yang sudah disetujui sebelumnya. Minimal 10 karakter.</small>
                                    </div>
                                    <div class="form-group">
                                        <label for="catatan_edit_judul">Catatan Perubahan (Opsional)</label>
                                        <textarea name="catatan_edit_judul" id="catatan_edit_judul" class="form-control" rows="3" 
                                                  placeholder="Masukkan catatan perubahan judul jika diperlukan..."></textarea>
                                    </div>
                                    <div class="form-group mb-0">
                                        <button type="submit" class="btn btn-warning">
                                            <i class="fas fa-save"></i> Simpan Perubahan
                                        </button>
                                        <a href="javascript:void(0);" class="btn btn-secondary" id="linkBatalEdit">
                                            <i class="fas fa-times"></i> Batal
                                        </a>
                                    </div>
                                    <?php echo form_close(); ?>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>

                    <?php if ($pengajuan->pembimbing_1_final_nama): ?>
                        <hr>
                        <h5>Pembimbing yang Ditetapkan</h5>
                        <div class="row">
                            <div class="col-md-6">
                                <p><strong>Pembimbing 1:</strong> <?php echo htmlspecialchars($pengajuan->pembimbing_1_final_nama . ' (' . $pengajuan->pembimbing_1_final_nidn . ')'); ?></p>
                            </div>
                            <div class="col-md-6">
                                <p><strong>Pembimbing 2:</strong> <?php echo htmlspecialchars($pengajuan->pembimbing_2_final_nama . ' (' . $pengajuan->pembimbing_2_final_nidn . ')'); ?></p>
                            </div>
                        </div>
                    <?php endif; ?>

                    <?php if ($pengajuan->catatan_sekprodi): ?>
                        <hr>
                        <h5>Catatan Sekprodi</h5>
                        <p class="form-control-plaintext"><?php echo nl2br(htmlspecialchars($pengajuan->catatan_sekprodi)); ?></p>
                    <?php endif; ?>
                    
                    <!-- Judul Revisi -->
                    <?php if (!empty($pengajuan->judul_revisi)): ?>
                        <hr>
                        <h5>Judul Revisi yang Diajukan</h5>
                        <div class="card card-outline card-info">
                            <div class="card-body">
                                <p><strong>Judul Revisi:</strong> <?php echo htmlspecialchars($pengajuan->judul_revisi); ?></p>
                                <?php
                                $status_approve_revisi = isset($pengajuan->status_approve_revisi) ? $pengajuan->status_approve_revisi : 'pending';
                                $status_p1 = isset($pengajuan->status_acc_judul_p1) ? $pengajuan->status_acc_judul_p1 : 'pending';
                                $status_p2 = isset($pengajuan->status_acc_judul_p2) ? $pengajuan->status_acc_judul_p2 : 'pending';
                                ?>
                                <p><strong>Status ACC Pembimbing:</strong><br>
                                    P1: <span class="badge badge-<?php echo $status_p1 === 'acc' ? 'success' : ($status_p1 === 'revisi' ? 'danger' : 'warning'); ?>"><?php echo strtoupper($status_p1); ?></span>
                                    P2: <span class="badge badge-<?php echo $status_p2 === 'acc' ? 'success' : ($status_p2 === 'revisi' ? 'danger' : 'warning'); ?>"><?php echo strtoupper($status_p2); ?></span>
                                </p>
                                <p><strong>Status Validasi Sekprodi:</strong> 
                                    <?php
                                    $status_class = 'warning';
                                    $status_text = 'Menunggu';
                                    switch ($status_approve_revisi) {
                                        case 'disetujui':
                                            $status_class = 'success';
                                            $status_text = 'Disetujui';
                                            break;
                                        case 'ditolak':
                                            $status_class = 'danger';
                                            $status_text = 'Ditolak';
                                            break;
                                        default:
                                            $status_class = 'warning';
                                            $status_text = 'Menunggu Validasi';
                                    }
                                    ?>
                                    <span class="badge badge-<?php echo $status_class; ?>"><?php echo $status_text; ?></span>
                                </p>
                                
                                <?php if ($status_approve_revisi == 'pending'): ?>
                                    <div class="row mt-3">
                                        <div class="col-md-6">
                                            <?php echo form_open('Skripsi_pengajuan/approve_persetujuan/' . $pengajuan->id, array('method' => 'post')); ?>
                                            <input type="hidden" name="action" value="approve">
                                            <button type="submit" class="btn btn-success btn-block" onclick="return confirm('Apakah Anda yakin ingin menyetujui judul revisi ini? Judul yang berlaku akan diubah menjadi judul revisi.');">
                                                <i class="fas fa-check"></i> Setujui Judul Revisi
                                            </button>
                                            <?php echo form_close(); ?>
                                        </div>
                                        <div class="col-md-6">
                                            <button type="button" class="btn btn-danger btn-block" data-toggle="modal" data-target="#modalRejectJudulRevisi">
                                                <i class="fas fa-times"></i> Tolak Judul Revisi
                                            </button>
                                        </div>
                                    </div>
                                <?php elseif ($status_approve_revisi == 'disetujui'): ?>
                                    <div class="alert alert-success mt-2">
                                        <strong>Judul revisi telah disetujui.</strong> Judul yang berlaku sekarang adalah judul revisi.
                                    </div>
                                <?php elseif ($status_approve_revisi == 'ditolak' && $pengajuan->catatan_sekprodi): ?>
                                    <div class="alert alert-danger mt-2">
                                        <strong>Alasan Penolakan:</strong><br>
                                        <?php echo nl2br(htmlspecialchars($pengajuan->catatan_sekprodi)); ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endif; ?>
                    
                    <!-- Berkas Persetujuan Judul -->
                    <?php if (!empty($pengajuan->file_persetujuan_judul)): ?>
                        <hr>
                        <h5>Berkas Persetujuan Judul</h5>
                        <div class="card card-outline card-warning">
                            <div class="card-body">
                                <p><strong>Link Google Drive:</strong> 
                                    <?php 
                                    $this->load->helper('google_drive');
                                    echo google_drive_link_html($pengajuan->file_persetujuan_judul, 'Buka Berkas');
                                    ?>
                                </p>
                                <?php
                                $status_approve_revisi = isset($pengajuan->status_approve_revisi) ? $pengajuan->status_approve_revisi : 'pending';
                                ?>
                                <p><strong>Status Validasi:</strong> 
                                    <?php
                                    $status_class = 'warning';
                                    $status_text = 'Menunggu';
                                    switch ($status_approve_revisi) {
                                        case 'disetujui':
                                            $status_class = 'success';
                                            $status_text = 'Disetujui';
                                            break;
                                        case 'ditolak':
                                            $status_class = 'danger';
                                            $status_text = 'Ditolak';
                                            break;
                                        default:
                                            $status_class = 'warning';
                                            $status_text = 'Menunggu Validasi';
                                    }
                                    ?>
                                    <span class="badge badge-<?php echo $status_class; ?>"><?php echo $status_text; ?></span>
                                </p>
                                
                                <?php if ($status_approve_revisi == 'pending'): ?>
                                    <div class="row mt-3">
                                        <div class="col-md-6">
                                            <?php echo form_open('Skripsi_pengajuan/approve_persetujuan/' . $pengajuan->id, array('method' => 'post')); ?>
                                            <input type="hidden" name="action" value="approve">
                                            <button type="submit" class="btn btn-success btn-block" onclick="return confirm('Apakah Anda yakin ingin menyetujui berkas persetujuan judul ini?');">
                                                <i class="fas fa-check"></i> Setujui Berkas
                                            </button>
                                            <?php echo form_close(); ?>
                                        </div>
                                        <div class="col-md-6">
                                            <button type="button" class="btn btn-danger btn-block" data-toggle="modal" data-target="#modalRejectPersetujuan">
                                                <i class="fas fa-times"></i> Tolak Berkas
                                            </button>
                                        </div>
                                    </div>
                                <?php elseif ($status_approve_revisi == 'ditolak' && $pengajuan->catatan_sekprodi): ?>
                                    <div class="alert alert-danger mt-2">
                                        <strong>Alasan Penolakan:</strong><br>
                                        <?php echo nl2br(htmlspecialchars($pengajuan->catatan_sekprodi)); ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endif; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Modal Reject Judul Revisi -->
<?php if (!empty($pengajuan->judul_revisi)): ?>
<div class="modal fade" id="modalRejectJudulRevisi" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Tolak Judul Revisi</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <?php echo form_open('Skripsi_pengajuan/approve_persetujuan/' . $pengajuan->id, array('method' => 'post')); ?>
            <div class="modal-body">
                <input type="hidden" name="action" value="reject">
                <div class="form-group">
                    <label for="catatan_reject_judul">Alasan Penolakan <span class="text-danger">*</span></label>
                    <textarea name="catatan" id="catatan_reject_judul" class="form-control" rows="4" required placeholder="Masukkan alasan penolakan judul revisi..."></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                <button type="submit" class="btn btn-danger">Tolak Judul Revisi</button>
            </div>
            <?php echo form_close(); ?>
        </div>
    </div>
</div>
<?php endif; ?>

<script>
$(document).ready(function() {
    // Toggle edit mode dengan hyperlink
    $('#linkEditJudul').on('click', function(e) {
        e.preventDefault();
        $('#displayJudul').hide();
        $('#editJudul').show();
        setTimeout(function() {
            $('#judul_disetujui_edit').focus();
        }, 100);
    });
    
    // Toggle back to display mode dengan hyperlink
    $('#linkBatalEdit').on('click', function(e) {
        e.preventDefault();
        $('#editJudul').hide();
        $('#displayJudul').show();
        // Reset form values
        var judulAwal = <?php echo json_encode($pengajuan->judul_disetujui); ?>;
        $('#judul_disetujui_edit').val(judulAwal);
        $('#catatan_edit_judul').val('');
    });
    
    // Handle form submission dengan konfirmasi
    $('#formEditJudul').on('submit', function(e) {
        var judulBaru = $('#judul_disetujui_edit').val().trim();
        
        if (judulBaru.length < 10) {
            alert('Judul baru minimal harus 10 karakter!');
            e.preventDefault();
            return false;
        }
        
        if (!confirm('Apakah Anda yakin ingin mengubah judul ini?\n\nJudul baru: ' + judulBaru + '\n\nPerubahan akan mempengaruhi judul di bimbingan skripsi dan data terkait.')) {
            e.preventDefault();
            return false;
        }
    });
});
</script>

<!-- Modal Reject Persetujuan -->
<?php if (!empty($pengajuan->file_persetujuan_judul)): ?>
<div class="modal fade" id="modalRejectPersetujuan" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Tolak Berkas Persetujuan Judul</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <?php echo form_open('Skripsi_pengajuan/approve_persetujuan/' . $pengajuan->id, array('method' => 'post')); ?>
            <div class="modal-body">
                <input type="hidden" name="action" value="reject">
                <div class="form-group">
                    <label for="catatan_reject">Alasan Penolakan <span class="text-danger">*</span></label>
                    <textarea name="catatan" id="catatan_reject" class="form-control" rows="4" required placeholder="Masukkan alasan penolakan berkas persetujuan judul..."></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                <button type="submit" class="btn btn-danger">
                    <i class="fas fa-times"></i> Tolak Berkas
                </button>
            </div>
            <?php echo form_close(); ?>
        </div>
    </div>
</div>
<?php endif; ?>



